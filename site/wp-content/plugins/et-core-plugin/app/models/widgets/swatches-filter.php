<?php
namespace ETC\App\Models\Widgets;

use ETC\App\Models\WC_Widget;

/**
 * Swatches Filter Widget.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Models/Widgets
 */
if( ! class_exists( 'WC_Widget' ) ) return;

class Swatches_Filter extends WC_Widget {

	protected function get_brand_taxonomies() {
		return array_values( array_filter( array(
			taxonomy_exists( 'brand' ) ? 'brand' : '',
			taxonomy_exists( 'product_brand' ) ? 'product_brand' : '',
		) ) );
	}

	protected function is_brand_taxonomy( $taxonomy ) {
		return in_array( $taxonomy, $this->get_brand_taxonomies(), true );
	}

	protected function is_product_brand_archive() {
		$brand_taxonomies = $this->get_brand_taxonomies();

		return ! empty( $brand_taxonomies ) && is_tax( $brand_taxonomies );
	}

	protected function get_filter_taxonomy( $filter_name ) {
		if ( 'cat' === $filter_name ) {
			return 'product_cat';
		}

		if ( $this->is_brand_taxonomy( $filter_name ) ) {
			return $filter_name;
		}

		if ( taxonomy_exists( 'pa_' . $filter_name ) ) {
			return 'pa_' . $filter_name;
		}

		if ( taxonomy_exists( $filter_name ) ) {
			return $filter_name;
		}

		return false;
	}

	protected function get_tax_query_operator( $taxonomy, $query_type ) {
		return ( $this->is_brand_taxonomy( $taxonomy ) || 'product_cat' === $taxonomy ) ? 'IN' : ( 'and' === strtolower( $query_type ) ? 'AND' : 'IN' );
	}

	public function __construct() {
		// ! Get the taxonomies
		$attribute_array      = array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( ! empty( $attribute_taxonomies ) ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$attribute_array[ $tax->attribute_name ] = $tax->attribute_name;
			}
		}

		$this->widget_cssclass    = 'etheme_swatches_filter';
		$this->widget_description = esc_html__( 'Widget to filtering products by swatches attributes', 'xstore-core' );
		$this->widget_id          = 'etheme_swatches_filter';
		$this->widget_name        = '8theme - &nbsp;&nbsp;' . esc_html__( 'Swatches filter', 'xstore-core' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => esc_html__( 'Filter by', 'xstore-core' ),
				'label' => esc_html__( 'Title', 'xstore-core' ),
			),
			'attribute' => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => esc_html__( 'Attribute', 'xstore-core' ),
				'options' => $attribute_array,
			),
			'query_type' => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => esc_html__( 'Query type', 'xstore-core' ),
				'options' => array(
					'and' => esc_html__( 'AND', 'xstore-core' ),
					'or'  => esc_html__( 'OR', 'xstore-core' ),
					'one_select' => esc_html__( 'One select', 'xstore-core' ),
				),
			),
			'search' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__( 'Show search', 'xstore-core' )
			),
			'count' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__( 'Show product counts', 'xstore-core' )
			),
			'ajax' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__( 'Use ajax preload for this widget', 'xstore-core' )
			),
		);
		parent::__construct();
	}

	public function widget( $args, $instance ) {
		if (parent::admin_widget_preview(esc_html__('Swatches filter', 'xstore-core')) !== false) return;
		if ( xstore_notice() ) return;
		if (is_admin()){
			return;
		}

		$check_empty_tax    = apply_filters('et_swatch_filter_check_empty_tax', false);
		$search             = isset( $instance['search'] ) ? $instance['search'] : $this->settings['search']['std'];
		$ajax               = isset( $instance['ajax'] ) ? $instance['ajax'] : $this->settings['ajax']['std'];
		$show_count         = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];

		$unique = $instance["attribute"] . '-' . $instance["query_type"];

		if (apply_filters('et_ajax_widgets', $ajax)){
			$instance['selector'] = '.etheme_swatches_filter.' . $unique;
			echo et_ajax_element_holder( 'Swatches_Filter', $instance, '', '', 'widget_filter', $args );
			return;
		}

		if ( ! is_shop() && ! is_product_taxonomy() ) return;

		global $wpdb;
		// ! Set main variables
		$html               = '';
//        $_chosen_attributes = \WC_Query::get_layered_nav_chosen_attributes();
		$taxonomy           = isset( $instance['attribute'] ) ? wc_attribute_taxonomy_name( $instance['attribute'] ) : $this->settings['attribute']['std'];
		$query_type         = isset( $instance['query_type'] ) ? $instance['query_type'] : $this->settings['query_type']['std'];

		$is_one_select = ($query_type == 'one_select') ? true : false;

		if ($is_one_select) {
			$query_type = 'or';
		}

//	    $orderby            = wc_attribute_orderby( $taxonomy );

		// ! Set get_terms args
		$terms = get_terms( $taxonomy );

		// ! Set class
		$class = '';
		$class .= 'st-swatch-size-large';

		// ! Get the taxonomies attribute
		$origin_attr = substr( $taxonomy, 3 );
		$attribute_type = get_query_var('et_swatch_tax-'.$origin_attr, false);
		if ( !$attribute_type ) {
			$attr = $wpdb->get_row( $wpdb->prepare( "SELECT attribute_type FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s", $origin_attr ) );
			if ($attr && $attr->attribute_type) {
				$attribute_type = $attr->attribute_type;
				set_query_var('et_swatch_tax-' . $origin_attr, $attribute_type);
			}
		}

		$subtype      = '';
		$sw_shape = get_theme_mod('swatch_shape', 'default');
		$sw_custom_shape = $sw_shape != 'default' ? $sw_shape : false;

		$subtype = apply_filters('et_'.$attribute_type.'_swatch_filter_subtype', $subtype);
		$sw_custom_shape = apply_filters('et_'.$attribute_type.'_swatch_filter_shape', $sw_custom_shape);

		if ( strpos( $attribute_type, '-sq') !== false ) {
			$et_attribute_type = str_replace( '-sq', '', $attribute_type );
			if ( !$sw_custom_shape || $sw_custom_shape == 'square' ) {
				$class .= ' st-swatch-shape-square';
				$subtype      = 'subtype-square';
			}
			else if ( $sw_custom_shape == 'circle' ) {
				$class .= ' st-swatch-shape-circle';
			}
		} else {
			$et_attribute_type = $attribute_type;
			if ( !$sw_custom_shape || $sw_custom_shape == 'circle' ) {
				$class .= ' st-swatch-shape-circle';
			}
		}

		// ! Get current filter
		$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', urldecode($taxonomy) ) );
		$current_filter = isset( $_GET[ urldecode($filter_name) ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ urldecode($filter_name) ] ) ) ) : array();
		$is_tax_or_search = false;
		$has_active_filters = false;

		foreach ( $_GET as $query_arg_key => $query_arg_value ) {
			$sale_orderby_values = array( 'sale', 'onsale', 'on_sale', 'on-sale', 'sale_products', 'sale-products', 'sale_status' );
			$is_sale_query_arg = ( 'orderby' === $query_arg_key && in_array( wc_clean( wp_unslash( $query_arg_value ) ), $sale_orderby_values, true ) )
				|| ( 'stock_status' === $query_arg_key && in_array( 'onsale', array_map( 'sanitize_title', explode( ',', str_replace( '_', '', wc_clean( wp_unslash( $query_arg_value ) ) ) ) ), true ) );

			if (
				(
					strpos( $query_arg_key, 'filter_' ) === 0
					|| 'sale_status' === $query_arg_key
					|| 'on_sale' === $query_arg_key
					|| $is_sale_query_arg
				)
				&& ! empty( $query_arg_value )
			) {
				$has_active_filters = true;
				break;
			}
		}

		if ( ! is_rtl() ) {
			$current_filter = array_map( 'sanitize_title', $current_filter );
		}

		$term_counts  = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );

		if ( is_product_category() || $this->is_product_brand_archive() || is_product_tag() || is_search() || $has_active_filters ) {
			$is_tax_or_search = true;

			if ( ! count( $term_counts ) && ! $has_active_filters ) {
				return;
			}
		}

		$current_page_url = false;

		foreach( $terms as $taxonomy ) {

			if (! is_object($taxonomy) || ! isset($taxonomy->term_id)){
				continue;
			}

			$decoded_slug = urldecode($taxonomy->slug);

			if ( $is_tax_or_search && count( $term_counts ) ) {
				if ( ! array_key_exists( $taxonomy->term_id, $term_counts) && ! in_array( $decoded_slug, $current_filter, true ) ) {
					continue;
				}
			}

			$count = '';



			$all_filters = $current_filter;
			$metadata    = get_term_meta( $taxonomy->term_id, '', true );
			$current_page_url = $current_page_url ? $current_page_url : $this->get_current_page_url();
			$link        = remove_query_arg( urldecode($filter_name), $current_page_url );

			$data_tooltip = $taxonomy->name;
			$li_class  = '';

			if ($show_count){
				$count .=  '<span class="count">';
				$count .=  isset( $term_counts[ $taxonomy->term_id ] ) ? $term_counts[ $taxonomy->term_id ] : 0 ;
				$count .=  '</span>';
				$li_class .= 'with-count';
			}

			// ! Generate link
			if ( ! in_array( $decoded_slug, $current_filter, true ) ) {
				$all_filters[] = $decoded_slug;
			} else {
				$key = array_search( $decoded_slug, $all_filters );
				unset( $all_filters[$key] );
				$li_class .= ' selected';
			}

			if ( ! empty( $all_filters ) ) {
				asort( $all_filters );

				if ($is_one_select && count($all_filters) && count($current_filter)) {

					if (
						end($all_filters) == $current_filter[0]
						&& isset($all_filters[1])
					) {
						$one_select_attr = $all_filters[1];
					} else {
						$one_select_attr = end($all_filters);
					}

					$link = add_query_arg( $filter_name, $one_select_attr, $link );
				} else {
					$link = add_query_arg( $filter_name, implode( ',', $all_filters ), $link );
				}

				$decoded_taxonomy = sanitize_title( str_replace( 'pa_', '', urldecode($taxonomy->taxonomy) ) );
				if ( 'or' === $query_type && ! strpos($link, 'query_type_' . $decoded_taxonomy) && ! ( 1 === count( $all_filters ) ) ) {
					$link = add_query_arg( 'query_type_' . $decoded_taxonomy, 'or', $link );
				}
				$link = str_replace( '%2C', ',', $link );
			}

			if ($check_empty_tax && !$this->check_woocommerce_url_for_products($link)) {
				continue;
			}

			if ( $is_tax_or_search && ! count( $term_counts ) && ! in_array( $decoded_slug, $current_filter, true ) && ! $this->check_woocommerce_url_for_products($link) ) {
				continue;
			}

			// ! Generate html
			switch ( $et_attribute_type ) {
				case 'st-color-swatch':
					$value = ( isset( $metadata['st-color-swatch'] ) && isset( $metadata['st-color-swatch'][0] ) ) ? $metadata['st-color-swatch'][0] : '#fff';
					$html .= '<li class="type-color ' . $subtype . $li_class . '"  data-tooltip="'.$data_tooltip.'">
                    <a rel="nofollow noopener" href="' . $link . '">
	                    <span class="st-custom-attribute" style="'. esc_attr( $this->generate_gradient_color_css($value) ) .'">
	                        <span class="screen-reader-text hidden">'.$data_tooltip.'</span>
	                    </span>
                    </a></li>';
					break;

				case 'st-image-swatch':
					$value = ( isset( $metadata['st-image-swatch'] ) && isset( $metadata['st-image-swatch'][0] ) ) ? $metadata['st-image-swatch'][0] : false;
					$image = ( $value ) ? wp_get_attachment_image( $value, apply_filters('sten_wc_filter_image_swatch_size', 'thumbnail') ) : wc_placeholder_img();
					$html .= '<li class="type-image ' . $subtype . $li_class . '"  data-tooltip="'.$data_tooltip.'">
                    <a rel="nofollow noopener" href="' . $link . '">
						<span class="st-custom-attribute">'
					         . $image .
					         '<span class="screen-reader-text hidden">'.$data_tooltip.'</span>' .
					         '</span>
					</a>
					</li>';
					break;

				case 'st-label-swatch':
					$value = ( isset( $metadata['st-label-swatch'] ) && $metadata['st-label-swatch'][0] ) ? $metadata['st-label-swatch'][0] : false;

					if ( ! $value ) {
						$value = $taxonomy->name;
					}

					$html .= '<li class="type-label ' . $subtype . $li_class . '"><a rel="nofollow noopener" href="' . $link . '"><span class="st-custom-attribute">' . $value . '</span></a></li>';
					break;

				default:
					$html .= '<li class="type-select ' . $li_class . '"><a rel="nofollow noopener" href="' . $link . '"><span class="st-custom-attribute">' . $taxonomy->name . '</span>'.$count.'</a></li>';
					break;
			}
		}

		if ( $html == '' ) return;

		$out = '';
		$out .= (isset($args['before_widget'])) ? str_replace('class="', ' class="type-'.$et_attribute_type.' '.$unique . ' ', $args['before_widget']) : '';
		$out .= parent::etheme_widget_title( $args, $instance );
		if ( $search ) {
			$out .= parent::render_widget_local_search_form(esc_html__('Search product attribute', 'xstore-core'));
		}
		$out .= '<ul class="st-swatch-preview st-color-swatch ' . esc_attr( $class ) . '">';
		$out .= $html;
		$out .= '</ul>';
		$out .= (isset($args['after_widget'])) ? $args['after_widget'] : '';

		echo $out;
//        echo '
//            <div class="sidebar-widget etheme_swatches_filter '.$unique.'">
//                ' . parent::etheme_widget_title($args, $instance) . '
//                <ul class="st-swatch-preview st-color-swatch ' . esc_attr( $class ) . '">
//                    ' . $html . '
//                </ul>
//            </div>
//        ';

	}

	protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
		global $wpdb;
		$tax_query  = \WC_Query::get_main_tax_query();
		$meta_query = \WC_Query::get_main_meta_query();

		$tax_query = $this->add_active_filters_to_tax_query( $tax_query );

		if ( 'or' === $query_type ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && $taxonomy === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}
		$meta_query     = new \WP_Meta_Query( $meta_query );
		$tax_query      = new \WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$term_ids_sql   = '(' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

		if (strlen($term_ids_sql) < 3) {
			return array();
		}

		// Generate query.
		$query           = array();
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) AS term_count, terms.term_id AS term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'
			{$tax_query_sql['where']} {$meta_query_sql['where']}
			AND terms.term_id IN $term_ids_sql";

		$product_ids = $this->get_filtered_product_ids();
		if ( is_array( $product_ids ) ) {
			if ( ! count( $product_ids ) ) {
				return array();
			}
			$query['where'] .= " AND {$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")";
		}

		$search = \WC_Query::get_main_search_query_sql();
		if ( $search ) {
			$query['where'] .= ' AND ' . $search;
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
		$query_sql         = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $query_sql );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );
		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ) );
		} else {
			$cached_counts = array();
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results                      = $wpdb->get_results( $query_sql, ARRAY_A );
			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
			}
		}
		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}

	protected function add_active_filters_to_tax_query( $tax_query ) {
		$existing_taxonomies = array();

		foreach ( $tax_query as $query ) {
			if ( is_array( $query ) && isset( $query['taxonomy'] ) ) {
				$existing_taxonomies[] = $query['taxonomy'];
			}
		}

		foreach ( $_GET as $query_arg_key => $query_arg_value ) {
			if ( strpos( $query_arg_key, 'filter_' ) !== 0 || '' === $query_arg_value ) {
				continue;
			}

			$filter_name = sanitize_title( substr( $query_arg_key, 7 ) );

			$filter_taxonomy = $this->get_filter_taxonomy( $filter_name );

			if ( ! $filter_taxonomy ) {
				continue;
			}

			if ( in_array( $filter_taxonomy, $existing_taxonomies, true ) ) {
				continue;
			}

			$terms = array_filter( array_map( 'sanitize_title', explode( ',', wc_clean( wp_unslash( $query_arg_value ) ) ) ) );

			if ( empty( $terms ) ) {
				continue;
			}

			$query_type_key = 'query_type_' . sanitize_title( str_replace( 'pa_', '', $filter_taxonomy ) );
			$query_type = isset( $_GET[ $query_type_key ] ) ? wc_clean( wp_unslash( $_GET[ $query_type_key ] ) ) : 'and';
			$operator = $this->get_tax_query_operator( $filter_taxonomy, $query_type );

			$tax_query[] = array(
				'taxonomy' => $filter_taxonomy,
				'field'    => 'slug',
				'terms'    => $terms,
				'operator' => $operator,
			);

			$existing_taxonomies[] = $filter_taxonomy;
		}

		return $tax_query;
	}

	protected function get_filtered_product_ids() {
		$sale_product_ids = $this->get_sale_product_ids();
		$query_product_ids = false;

		if ( function_exists( 'WC' ) && WC()->query && is_object( WC()->query->get_main_query() ) ) {
			$post__in = WC()->query->get_main_query()->get( 'post__in' );

			if ( ! empty( $post__in ) ) {
				$query_product_ids = wp_parse_id_list( $post__in );
			}
		}

		if ( is_array( $sale_product_ids ) && is_array( $query_product_ids ) ) {
			return array_values( array_intersect( $sale_product_ids, $query_product_ids ) );
		}

		if ( is_array( $sale_product_ids ) ) {
			return $sale_product_ids;
		}

		if ( is_array( $query_product_ids ) ) {
			return $query_product_ids;
		}

		return false;
	}

	protected function get_sale_product_ids( $force = false ) {
		$sale_status = isset( $_GET['sale_status'] ) ? wc_clean( wp_unslash( $_GET['sale_status'] ) ) : '';
		$on_sale = isset( $_GET['on_sale'] ) ? wc_clean( wp_unslash( $_GET['on_sale'] ) ) : '';
		$stock_status = isset( $_GET['stock_status'] ) ? wc_clean( wp_unslash( $_GET['stock_status'] ) ) : '';
		$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';
		$sale_orderby_values = array( 'sale', 'onsale', 'on_sale', 'on-sale', 'sale_products', 'sale-products', 'sale_status' );
		$is_sale_request = $sale_status || $on_sale || in_array( $orderby, $sale_orderby_values, true ) || in_array( 'onsale', array_map( 'sanitize_title', explode( ',', str_replace( '_', '', $stock_status ) ) ), true );

		if ( ! $force && ! $is_sale_request ) {
			return false;
		}

		static $product_ids_on_sale = null;

		if ( null === $product_ids_on_sale ) {
			$product_ids_on_sale = array();
			$loaded_from_data_store = false;

			if ( class_exists( 'WC_Data_Store' ) ) {
				$data_store = \WC_Data_Store::load( 'product' );
				if ( is_callable( array( $data_store, 'get_on_sale_products' ) ) ) {
					$on_sale_products = $data_store->get_on_sale_products();
					$product_ids_on_sale = wp_parse_id_list( array_merge(
						wp_list_pluck( $on_sale_products, 'id' ),
						array_diff( wp_list_pluck( $on_sale_products, 'parent_id' ), array( 0 ) )
					) );
					$loaded_from_data_store = true;
				}
			}

			if ( ! $loaded_from_data_store ) {
				$product_ids_on_sale = array_map( 'absint', wc_get_product_ids_on_sale() );
			}
		}

		return $product_ids_on_sale;
	}

	/**
	 * Generate color style
	 */
	public function generate_gradient_color_css($color) {
		$style = '';

		if (is_serialized($color) || is_array($color)){
			if (!is_array($color)) {
				$color = unserialize($color);
			}

			$gradient_direction = get_theme_mod('swatch_multicolor_design', 'right');
			if ( in_array($gradient_direction, array('diagonal_1', 'diagonal_2'))) {
				$gradient_direction = str_replace(array('diagonal_1', 'diagonal_2'), array('bottom right', 'bottom left'), $gradient_direction);
			}
			$style .= 'background: linear-gradient( to ';
			$style .= $gradient_direction . ',';
			$percent = 100/count($color);

			foreach($color as $color_key => $color_value){
				$style .= $color_value . ' ' . $percent .'% '. ( $percent+$percent*$color_key ) . '%';
				if ($color_key != count($color)-1){
					$style .= ',';
				}
			}

			$style .= ');';
		} else {
			$style .= 'background-color:' . $color . ';';
		}

		return $style;
	}

	public function check_woocommerce_url_for_products($url) {
		$query_string = wp_parse_url($url, PHP_URL_QUERY);
		parse_str((string) $query_string, $request_args);

		$tax_query = array(
			'relation' => 'AND',
		);
		$existing_taxonomies = array();

		if ( is_product_taxonomy() ) {
			$queried_object = get_queried_object();

			if ( $queried_object instanceof \WP_Term && taxonomy_exists( $queried_object->taxonomy ) ) {
				$tax_query[] = array(
					'taxonomy' => $queried_object->taxonomy,
					'field'    => 'slug',
					'terms'    => array( $queried_object->slug ),
					'operator' => 'IN',
				);

				$existing_taxonomies[] = $queried_object->taxonomy;
			}
		}

		foreach ($request_args as $key => $value) {
			if (strpos($key, 'filter_') !== 0 || '' === $value) {
				continue;
			}

			$filter_name = sanitize_title(str_replace('filter_', '', $key));
			$taxonomy = $this->get_filter_taxonomy( $filter_name );

			if ( ! $taxonomy ) {
				continue;
			}

			if ( in_array( $taxonomy, $existing_taxonomies, true ) ) {
				continue;
			}

			$terms = array_filter(array_map('sanitize_title', explode(',', wc_clean(wp_unslash($value)))));

			if (empty($terms)) {
				continue;
			}

			$query_type_key = 'query_type_' . sanitize_title(str_replace('pa_', '', $taxonomy));
			$query_type = isset($request_args[$query_type_key]) ? wc_clean(wp_unslash($request_args[$query_type_key])) : 'and';
			$operator = $this->get_tax_query_operator( $taxonomy, $query_type );

			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $terms,
				'operator' => $operator,
			);

			$existing_taxonomies[] = $taxonomy;
		}

		$query_args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'fields'              => 'ids',
		);

		if ( ( isset($request_args['sale_status']) && $request_args['sale_status'] ) || ( isset($request_args['on_sale']) && $request_args['on_sale'] ) ) {
			$sale_product_ids = $this->get_sale_product_ids( true );
			$query_args['post__in'] = count($sale_product_ids) ? $sale_product_ids : array(0);
		}

		if (count($tax_query) > 1) {
			$query_args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query($query_args);

		return $query->have_posts();
	}
}
