<?php
namespace ETC\App\Models\Widgets;

use ETC\App\Models\WC_Widget;

/**
 * Brands filter.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Models/Admin
 */
if( ! class_exists( 'WC_Widget' ) ) return;
class Brands_Filter extends WC_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->widget_cssclass    = 'etheme_widget_brands_filter etheme_widget_brands';
        $this->widget_description = esc_html__( 'Widget to filtering products by brands', 'xstore-core' );
        $this->widget_id          = 'etheme_brands_filter';
        $this->widget_name        = '8theme - &nbsp;&nbsp;' . esc_html__( 'Filter Products by Brands', 'xstore-core' );
        $this->settings           = array(
            'title' => array(
                'type'  => 'text',
                'std'   => esc_html__( 'Filter by brand', 'xstore-core' ),
                'label' => esc_html__( 'Title', 'xstore-core' ),
            ),
            'display_type' => array(
                'type'    => 'select',
                'std'     => 'list',
                'label'   => esc_html__( 'Display type', 'xstore-core' ),
                'options' => array(
                    'list'     => esc_html__( 'List', 'xstore-core' ),
                    'dropdown' => esc_html__( 'Dropdown', 'xstore-core' ),
                    'select2' => esc_html__( 'Dropdown Advanced', 'xstore-core' ),
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
            )
        );

        parent::__construct();
    }

    /**
     * Output widget.
     *
     * @see WP_Widget
     *
     * @param array $args Arguments.
     * @param array $instance Instance.
     */
    public function widget( $args, $instance ) {
	    if ( xstore_notice() ) return;

        if ( ! is_shop() && ! is_product_taxonomy() ) {
            return;
        }

//        $_chosen_attributes = \WC_Query::get_layered_nav_chosen_attributes();
        $count              = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];
        $search             = isset( $instance['search'] ) ? $instance['search'] : $this->settings['search']['std'];
        $taxonomy           = 'brand';
        $query_type         = isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';
        $display_type       = isset( $instance['display_type'] ) ? $instance['display_type'] : $this->settings['display_type']['std'];
        $ajax               = isset( $instance['ajax'] ) ? $instance['ajax'] : $this->settings['ajax']['std'];

	    if ( in_array($display_type, array('dropdown', 'select2')) ) {
		    if ($display_type == 'select2'){
			    wp_enqueue_script( 'selectWoo' );
			    wp_enqueue_style( 'select2' );
                wp_add_inline_script(apply_filters('etheme_elementor_force_localize_global_assets', 'etheme',  'general'), "
		            jQuery(document).ready(function ($) { jQuery( '.etheme_widget_brands_filter .dropdown_product_brand' ).select2();
		            jQuery(document).on('et_ajax_content_loaded et_ajax_element_loaded', function() {
				        jQuery( '.etheme_widget_brands_filter .dropdown_product_brand' ).select2();
				    }) });
		        " );
		    }

		    if (! etheme_get_option( 'ajax_product_filter', 0 )){
                wp_add_inline_script(apply_filters('etheme_elementor_force_localize_global_assets', 'etheme',  'general'), "
                    jQuery(document).ready(function ($) { jQuery( '.dropdown_product_brand' ).change( function() {
                        var url = jQuery(this).find( 'option:selected' ).data( 'url' );
                        if ( url != '' ) location.href = url;
                    }) });
                " );
		    }
	    }

	    if (apply_filters('et_ajax_widgets', $ajax)){
		    $extra = ($display_type == 'select2') ? 'select2' : '';
            $instance['selector'] = '.etheme_widget_brands_filter';
            echo et_ajax_element_holder( 'Brands_Filter', $instance, '', '', 'widget_filter', $args );
            return;
        }

        $hide_empty = get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes';

        $terms = get_terms(
            array(
                'taxonomy' => 'brand',
                'hide_empty' => $hide_empty,
                'operator'         => 'IN',
                'include_children' => false,
            )
        );

        if ( is_wp_error( $terms ) || 0 === count( $terms ) ) {
            return;
        }


        $class = '';
        $shop_url = '';

        $items = '';
	    $cached_counts = array();
	    $write_cache = false;

	    $is_cache_enabled = apply_filters( 'etheme_widget_product_brands_cache', true);
        $is_category_check = apply_filters( 'etheme_is_category_check', false);

	    if ( $is_cache_enabled ){
		    $cached_counts = (array) get_transient( 'etheme_product_brands_filter_counts' );
	    }

	    // Next one write random string to $cached_counts in any case
	    // $cached_counts = (array) (apply_filters( 'etheme_widget_product_brands_cache', true)) ? get_transient( 'etheme_product_brands_filter_counts' ) : array();

        $widget_class = '';
        if( count( $terms ) > 0 ) {

            $is_product_cat = false;
            if ( is_tax( 'brand' ) ) {
                $widget_class = 'on_brand ';
                $shop_url = 'data-shop-url="' . get_permalink( wc_get_page_id( 'shop' ) ) . '"';
            }
            elseif ( is_tax( 'product_cat' ) ) {
                $is_product_cat = true;
            }

            $term_counts  = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
            $current_filter = isset( $_GET['filter_brand'] ) ? explode( ',', wc_clean( wp_unslash( $_GET['filter_brand'] ) ) ) : array();
            $current_filter = array_map( 'sanitize_title', $current_filter );
            $has_active_filters = $this->has_active_filters();
            $filter_by_counts = $is_category_check || is_product_category() || is_tax( 'brand' ) || is_product_tag() || is_search() || $has_active_filters;
            $current_page_url = false;
            foreach ( $terms as $brand ) {

                $class = 'cat-item';
                $stock = null;
                $is_current_brand = in_array( $brand->slug, $current_filter, true );
                $has_term_count = array_key_exists( $brand->term_id, $term_counts );

                // temp disable this check
                // if ( ! array_key_exists( $brand->term_id, $term_counts) ) {
                //     continue;
                // }

                if (
                    $filter_by_counts
                    && ! $has_term_count
                    && ( ! $is_current_brand || $this->has_active_filters_except_brand() )
                ) {
                    continue;


                }

	            if ( $filter_by_counts && $has_term_count ) {
		            $stock = $term_counts[ $brand->term_id ];
	            }
	            elseif ( ! $cached_counts || !is_array($cached_counts) || count($cached_counts) < 2 || !isset($cached_counts[$brand->term_id]) ) {
		            $write_cache = true;

		            if ($is_cache_enabled) {
			            if ( $hide_empty ) {
				            $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand' );
				            $cached_counts[$brand->term_id] = $stock;
			            } else {
				            $cached_counts[$brand->term_id] = absint($brand->count);
			            }
		            } else {
			            if ( $hide_empty && $is_product_cat ) {
				            global $wp_query;
				            $cat    = $wp_query->get_queried_object();
				            $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand', $cat->slug );
				            $cached_counts[$brand->term_id] = $stock;
			            } elseif ( $hide_empty ) {
				            $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand' );
				            $cached_counts[$brand->term_id] = $stock;
			            } elseif ( $is_product_cat ) {
				            global $wp_query;
				            $cat    = $wp_query->get_queried_object();
				            $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand', $cat->slug, false );
				            $cached_counts[$brand->term_id] = $stock;
			            } else {
				            $cached_counts[$brand->term_id] = absint($brand->count);
			            }
		            }

//		            if ( $hide_empty && $is_product_cat ) {
//			            $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand', $brand->slug );
//			            $cached_counts[$brand->term_id] = $stock;
//		            } elseif ( $hide_empty ) {
			            // $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand' );
//			            $cached_counts[$brand->term_id] = $stock;
//		            } elseif ( $is_product_cat ) {
//			            $stock = parent::etheme_stock_taxonomy( $brand->term_id, 'brand', $brand->slug, false );
//			            $cached_counts[$brand->term_id] = $stock;
//		            }
//		            else {
//			            $cached_counts[$brand->term_id] = absint($brand->count);
//		            }

	            }

                 // var_dump($stock);

	            // Do it if new brand added during the cache.
                if ( ! isset( $stock ) ) {
                    $stock = isset($cached_counts[$brand->term_id]) ? $cached_counts[$brand->term_id] : $brand->count;
                }

	            if (!$filter_by_counts && !$stock) {
		            $stock = $brand->count;
	            }

                // var_dump($stock);

                if ((!$stock || $stock =='') && ! $is_current_brand){
                    continue;
                }

//                $thumbnail_id = absint(get_term_meta($brand->term_id, 'thumbnail_id', true));
                $current_page_url = $current_page_url ? $current_page_url : $this->get_current_page_url();
                $link = remove_query_arg( 'filter_brand', $current_page_url );

                $all_filters = $current_filter;

                if ( ! $is_current_brand ) {
                    $all_filters[] = $brand->slug;
                } else {
                    $key = array_search( $brand->slug, $all_filters );
                    unset( $all_filters[$key] );
                    $class .= ' current-item';
                }

                if ( ! empty($all_filters) ) {
                    $link = add_query_arg( 'filter_brand', implode( ',', $all_filters ), $link );
                }

                if ($is_category_check && ! $is_current_brand && !$this->check_woocommerce_url_for_products($link)) {
                    continue;
                }

                // Render widget items
                if ( in_array($display_type, array('dropdown', 'select2')) ) {
                    $link = remove_query_arg( 'filter_brand', $current_page_url );
                    $link = add_query_arg( 'filter_brand', $brand->slug, $link );

                    $selected = ( is_tax( 'brand' , $brand->term_id ) || $is_current_brand ) ? ' selected' : '' ;
                    $items .= '<option class="level-0" value="' . esc_html( $brand->name ) . '" data-url="' . $link . '"' . $selected . '>' . esc_html( $brand->name .( $count == 1 ? ' ('. $stock . ')' : '' ) ) . '</option>';
                } else {
                    $items .= '<li class="' . $class . '">';
                    $items .= '<a rel="nofollow noopener" href="' . $link . '">';
                    $items .= esc_html( $brand->name );
                    if ( $count == 1 ) {
                        $items .= apply_filters( 'etheme_brands_widget_count', '<span class="count">(' . esc_html( $stock ) . ')</span>', $stock, $brand );
                    }
                    $items .= '</a>';
                    $items .= '</li>';
                }
            }
        }

	    if ($cached_counts && $write_cache && $is_cache_enabled) {
		    set_transient( 'etheme_product_brands_filter_counts', $cached_counts, DAY_IN_SECONDS );
	    }

        // Render widget
        $out = '';
        $out .= (isset($args['before_widget'])) ? str_replace('class="', $shop_url . ' class="'.$widget_class, $args['before_widget']) : '';
//        $out .= '<div class="sidebar-widget etheme_widget_brands_filter etheme_widget_brands ' . $class . '" ' . $shop_url . '>';
        $out .= parent::etheme_widget_title($args, $instance);
        if ( $search && $display_type == 'list' ) {
            $out .= parent::render_widget_local_search_form(esc_html__('Find a brand', 'xstore-core'));
        }
        if ( in_array($display_type, array('dropdown', 'select2')) ) {
            $out .= '<select name="product_brand" class="dropdown_product_brand">';
                $out .= '<option value="" selected="selected" data-url="">'.esc_html__('Select a brand', 'xstore-core').'</option>';
                $out .= $items;
            $out .= '</select>';
        } else {

            $out .= '<ul>';

                $out .= $items;

            $out .= '</ul>';
        }
        $out .= (isset($args['after_widget'])) ? $args['after_widget'] : '';
//        $out .= '</div>';

        echo $out;
    }

	    protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
	        global $wpdb;
	        $tax_query  = \WC_Query::get_main_tax_query();
	        $meta_query = \WC_Query::get_main_meta_query();
	        $has_current_brand_filter = isset( $_GET['filter_brand'] ) && '' !== wc_clean( wp_unslash( $_GET['filter_brand'] ) );

	        $tax_query = $this->add_active_filters_to_tax_query( $tax_query );

	        if ( 'or' === $query_type || $has_current_brand_filter ) {
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
        // Generate query.
        $query           = array();
        $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
        $query['from']   = "FROM {$wpdb->posts}";
        $query['join']   = "
            INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
            INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
            INNER JOIN {$wpdb->terms} AS terms USING( term_id )
            " . $tax_query_sql['join'] . $meta_query_sql['join'];
        $query['where'] = "
            WHERE {$wpdb->posts}.post_type IN ( 'product' )
            AND {$wpdb->posts}.post_status = 'publish'"
            . $tax_query_sql['where'] . $meta_query_sql['where'] .
            ' AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';
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
        $query             = implode( ' ', $query );
        // We have a query - let's see if cached results of this query already exist.
        $query_hash    = md5( $query );
        // Maybe store a transient of the count values.
        $cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );
//        $cache = false;
        if ( true === $cache ) {
            $cached_counts = (array) get_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ) );
        } else {
            $cached_counts = array();
        }
        if ( ! isset( $cached_counts[ $query_hash ] ) ) {
            $results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
            $counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
            $cached_counts[ $query_hash ] = $counts;
            if ( true === $cache ) {
                set_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
            }
	        }
	        return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	    }

	    protected function has_active_filters() {
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
				    return true;
			    }
		    }

		    return false;
	    }

	    protected function has_active_filters_except_brand() {
		    foreach ( $_GET as $query_arg_key => $query_arg_value ) {
			    if ( empty( $query_arg_value ) || in_array( $query_arg_key, array( 'filter_brand', 'query_type_brand' ), true ) ) {
				    continue;
			    }

			    $sale_orderby_values = array( 'sale', 'onsale', 'on_sale', 'on-sale', 'sale_products', 'sale-products', 'sale_status' );
			    $is_sale_query_arg = ( 'orderby' === $query_arg_key && in_array( wc_clean( wp_unslash( $query_arg_value ) ), $sale_orderby_values, true ) )
				    || ( 'stock_status' === $query_arg_key && in_array( 'onsale', array_map( 'sanitize_title', explode( ',', str_replace( '_', '', wc_clean( wp_unslash( $query_arg_value ) ) ) ) ), true ) );

			    if (
				    strpos( $query_arg_key, 'filter_' ) === 0
				    || 'sale_status' === $query_arg_key
				    || 'on_sale' === $query_arg_key
				    || $is_sale_query_arg
			    ) {
				    return true;
			    }
		    }

		    return is_product_category() || is_product_tag() || is_search();
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

			    if ( 'cat' === $filter_name ) {
				    $filter_taxonomy = 'product_cat';
			    } elseif ( 'brand' === $filter_name && taxonomy_exists( 'brand' ) ) {
				    $filter_taxonomy = 'brand';
			    } elseif ( taxonomy_exists( 'pa_' . $filter_name ) ) {
				    $filter_taxonomy = 'pa_' . $filter_name;
			    } elseif ( taxonomy_exists( $filter_name ) ) {
				    $filter_taxonomy = $filter_name;
			    } else {
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
			    $operator = in_array( $filter_taxonomy, array( 'brand', 'product_cat' ), true ) ? 'IN' : ( 'and' === strtolower( $query_type ) ? 'AND' : 'IN' );

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

	    public function check_woocommerce_url_for_products($url) {
	        $query_string = parse_url($url, PHP_URL_QUERY);
        parse_str($query_string, $request_args);

        $tax_query = [
            'relation' => 'AND',
        ];

        foreach ($request_args as $key => $value) {
	        if (strpos($key, 'filter_') !== 0 || '' === $value) {
		        continue;
	        }

	        $filter_name = sanitize_title(str_replace('filter_', '', $key));

	        if ('cat' === $filter_name) {
		        $taxonomy = 'product_cat';
	        } elseif ('brand' === $filter_name && taxonomy_exists('brand')) {
		        $taxonomy = 'brand';
	        } elseif (taxonomy_exists('pa_' . $filter_name)) {
		        $taxonomy = 'pa_' . $filter_name;
	        } elseif (taxonomy_exists($filter_name)) {
		        $taxonomy = $filter_name;
	        } else {
		        continue;
	        }

	        $terms = array_filter(array_map('sanitize_title', explode(',', wc_clean(wp_unslash($value)))));

	        if (empty($terms)) {
		        continue;
	        }

	        $query_type_key = 'query_type_' . sanitize_title(str_replace('pa_', '', $taxonomy));
	        $query_type = isset($request_args[$query_type_key]) ? wc_clean(wp_unslash($request_args[$query_type_key])) : 'and';
	        $operator = in_array($taxonomy, array('brand', 'product_cat'), true) ? 'IN' : ('and' === strtolower($query_type) ? 'AND' : 'IN');

	        $tax_query[] = [
		        'taxonomy' => $taxonomy,
		        'field'    => 'slug',
		        'terms'    => $terms,
		        'operator' => $operator,
	        ];
        }

        $args = [
	        'post_type'           => 'product',
	        'post_status'         => 'publish',
	        'posts_per_page'      => 1,
	        'no_found_rows'       => true,
	        'ignore_sticky_posts' => true,
	        'fields'              => 'ids',
        ];

        if ( ( isset($request_args['sale_status']) && $request_args['sale_status'] ) || ( isset($request_args['on_sale']) && $request_args['on_sale'] ) ) {
	        $sale_product_ids = $this->get_sale_product_ids( true );
	        $args['post__in'] = count($sale_product_ids) ? $sale_product_ids : array(0);
        }

        if ( count($tax_query) > 1 ) {
	        $args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query($args);

        return $query->have_posts();
    }
}
