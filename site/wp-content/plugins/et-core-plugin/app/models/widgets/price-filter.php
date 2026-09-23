<?php
namespace ETC\App\Models\Widgets;

use ETC\App\Models\WC_Widget;

/**
 * Price Filter Widget and related functions.
 *
 * Generates a range slider to filter products by price
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Models/Widgets
 */
if( ! class_exists( 'WC_Widget' ) ) return;
class Price_Filter extends WC_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_price_filter etheme-price-filter';
		$this->widget_description = esc_html__( 'Display a slider to filter products in your store by price.', 'xstore-core' );
		$this->widget_id          = 'et_price_filter';
		$this->widget_name        =  '8theme - &nbsp;&nbsp;' . esc_html__( 'Filter Products by Price', 'xstore-core' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => esc_html__( 'Filter by price', 'xstore-core' ),
				'label' => esc_html__( 'Title', 'xstore-core' ),
			),
            'display_type' => array(
                'type'    => 'select',
                'std'     => 'slider',
                'label'   => esc_html__( 'Display type', 'xstore-core' ),
                'options' => array(
                    'slider'   => esc_html__( 'Slider', 'xstore-core' ),
                    'ranges'  => esc_html__( 'Ranges', 'xstore-core' )
                )
            ),
            'ranges' => array(
                'type' => 'textarea',
                'label' => __( 'Ranges (for Display type "Ranges")', 'xstore-core' ),
                'std'     => '',
                'desc' => sprintf(__( 'Each range on a line, separate by the "-" symbol. Do not include the currency symbol. Example: %s', 'xstore-core' ), 'https://prnt.sc/g3WzTnI0IhXY'),
                'condition' => array(
                    'display_type' => 'ranges',
                ),
            ),
            'ajax' => array(
                'type'  => 'checkbox',
                'std'   => 0,
                'label' => esc_html__( 'Use ajax preload for this widget (will work only if "Ajax Product Filters" option is "on")', 'xstore-core' ),
            ),
		);
		parent::__construct();
	}
	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args     Arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		if (parent::admin_widget_preview(esc_html__('Filter Products by Price', 'xstore-core')) !== false) return;
		if ( xstore_notice() ) return;

		if ( get_query_var('et_is-woocommerce-archive', false) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), '0.4.2' );
			wp_register_script( 'wc-jquery-ui-touchpunch', WC()->plugin_url() . '/assets/js/jquery-ui-touch-punch/jquery-ui-touch-punch' . $suffix . '.js', array( 'jquery-ui-slider' ), WC_VERSION, true );
			wp_register_script( 'wc-price-slider', WC()->plugin_url() . '/assets/js/frontend/price-slider' . $suffix . '.js', array(
				'jquery-ui-slider',
				'wc-jquery-ui-touchpunch',
				'accounting'
			), WC_VERSION, true );
			wp_localize_script(
				'wc-price-slider', 'woocommerce_price_slider_params', array(
					'currency_format_num_decimals' => 0,
					'currency_format_symbol'       => get_woocommerce_currency_symbol(),
					'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
					'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
					'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array(
						'%s',
						'%v'
					), get_woocommerce_price_format() ) ),
				)
			);
			wp_enqueue_script( 'wc-price-slider' );
		}

        $display_type = isset( $instance['display_type'] ) ? $instance['display_type'] : $this->settings['display_type']['std'];
        $is_range_type = $display_type == 'ranges';
		$ajax  = isset( $instance['ajax'] ) ? $instance['ajax'] : $this->settings['ajax']['std'];

		if (apply_filters('et_ajax_widgets', $ajax) && etheme_get_option( 'ajax_product_filter', 0 )){
			$instance['selector'] = '.etheme-price-filter';
			echo et_ajax_element_holder( 'Price_Filter', $instance, 'price_filter', '', 'widget_filter', $args );
			return;
		}
		global $wp;
		if ( !get_query_var('et_is-woocommerce-archive', false) ) {
			return;
		}

		// comment it because of topic https://www.8theme.com/topic/unable-to-remove-applied-filter/#post-430663
//		if (
//			! is_object(WC()->query->get_main_query())
//			|| ! WC()->query->get_main_query()->post_count
//		) {
//			return;
//		}

		wp_enqueue_script( 'wc-price-slider' );
		// Find min and max price in current result set.
		$prices = $this->get_filtered_price();

		// Try to use wc default function
		if (is_null($prices)){
			$prices = $this->get_filtered_price_default();
		}

		$min    = (!is_null($prices->min_price)) ? floor( $prices->min_price ) : 0;
//		$max    = ceil( $prices->max_price );
		$max    = (!is_null($prices->max_price)) ? floor( $prices->max_price ) : 0;

		if ( $min === $max ) {
			return;
		}
		$this->widget_start( $args, $instance );
		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}
		$min_price = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : apply_filters( 'woocommerce_price_filter_widget_min_amount', $min ); // WPCS: input var ok, CSRF ok.
		$max_price = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : apply_filters( 'woocommerce_price_filter_widget_max_amount', $max ); // WPCS: input var ok, CSRF ok.

		$ajax_filters = function_exists('etheme_get_option') && etheme_get_option('ajax_product_filter', 0);
		$class = ( ! isset( $_GET['min_price'] ) && ! isset( $_GET['max_price'] ) && $ajax_filters) ? 'invisible' : '';
		$button_text = $ajax_filters ? esc_html__( 'Reset', 'xstore-core' ) : esc_html__( 'Filter', 'xstore-core' );
        if ( $is_range_type )
            $button_text = esc_html__('Apply', 'xstore-core');
		if ( apply_filters('etheme_elementor_theme_builder', false) ) {
            $button_text = '<span class="button-text">'.$button_text.'</span>';
            if ( isset($instance['icon'])) {
                if ( $instance['icon_position'] == 'left' )
                    $button_text = $instance['icon'] . $button_text;
                else
                    $button_text = $button_text . $instance['icon'];
            }
        }

        if ( $is_range_type ) {
            $current_filters = $this->get_current_filters();
            $price_ranges = $this->get_range_price($instance);
            $filter_name = 'price';
            $range_args            = array(
                'name'        			=> $filter_name,
                'current'     			=> array(),
                'options'     			=> $price_ranges,
                'multiple'    			=> 0,
                'show_counts' 			=> 0,
                'display_type' 			=> 'range',
                'source' 	   			=> 'price',
                'button_text' => $button_text
            );
            $range_args['current']['min'] = isset( $current_filters[ 'min_' . $filter_name ] ) ? $current_filters[ 'min_' . $filter_name ] : '';
            $range_args['current']['max'] = isset( $current_filters[ 'max_' . $filter_name ] ) ? $current_filters[ 'max_' . $filter_name ] : '';
            echo '<form method="get" action="' . esc_url( $form_action ) . '">';
            $this->render_range_price($range_args);
            echo '</form>';
        }
        else {
            echo '<form method="get" action="' . esc_url($form_action) . '">
			<div class="price_slider_wrapper">
				<div class="price_slider" style="display:none;"></div>
				<div class="price_slider_amount" data-step="1">
					<label class="screen-reader-text hidden" for="min_price">' . esc_html__('Min price', 'xstore-core') . '</label>
					<label class="screen-reader-text hidden" for="max_price">' . esc_html__('Max price', 'xstore-core') . '</label>
					<input type="text" id="min_price" name="min_price" value="' . esc_attr($min_price) . '" data-min="' . esc_attr(apply_filters('woocommerce_price_filter_widget_min_amount', $min)) . '" placeholder="' . esc_attr__('Min price', 'xstore-core') . '" />
					<input type="text" id="max_price" name="max_price" value="' . esc_attr($max_price) . '" data-max="' . esc_attr(apply_filters('woocommerce_price_filter_widget_max_amount', $max)) . '" placeholder="' . esc_attr__('Max price', 'xstore-core') . '" />
					<button type="submit" class="button et-reset-price ' . $class . '">' . $button_text . '</button>
					<div class="price_label" style="display:none;">
						' . esc_html__('Price:', 'xstore-core') . ' <span class="from"></span> &mdash; <span class="to"></span>
					</div>
					' . wc_query_string_form_fields(null, array('min_price', 'max_price'), '', true) . '
					<div class="clear"></div>
				</div>
			</div>
		</form>'; // WPCS: XSS ok.
        }
		$this->widget_end( $args );
	}

    protected function get_range_price($filter) {
        $options = array();
        // Use the default price slider widget.
        if ( empty( $filter['ranges'] ) ) {
            return $options;
        }

        $ranges = explode( "\n", $filter['ranges'] );

        foreach ( $ranges as $range ) {
            $range       = trim( $range );
            $prices      = explode( '-', $range );
            $price_range = array( 'min' => '', 'max' => '' );
            $name        = array();

            if ( count( $prices ) > 1 ) {
                $price_range['min'] = preg_match( '/\d+\.?\d+/', current( $prices ), $match ) ? floatval( $match[0] ) : 0;
                $price_range['max'] = preg_match( '/\d+\.?\d+/', end( $prices ), $match ) ? floatval( $match[0] ) : 0;
                reset( $prices );
                $name['min'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['min'] ) . '</span>', current( $prices ) );
                $name['max'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['max'] ) . '</span>', end( $prices ) );
            } elseif ( substr( $range, 0, 1 ) === '<' ) {
                $price_range['max'] = preg_match( '/\d+\.?\d+/', end( $prices ), $match ) ? floatval( $match[0] ) : 0;
                $name['max'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['max'] ) . '</span>', ltrim( end( $prices ), '< ' ) );
            } else {
                $price_range['min'] = preg_match( '/\d+\.?\d+/', current( $prices ), $match ) ? floatval( $match[0] ) : 0;
                $name['min'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['min'] ) . '</span>', current( $prices ) );
            }

            $options[] = array(
                'name'  => implode( ' - ', $name ),
                'count' => 0,
                'range' => $price_range,
                'level' => 0,
            );
        }

        return $options;
    }

    public function render_range_price($args) {
        $args = wp_parse_args( $args, array(
            'name'        => '',
            'current'     => array(),
            'options'     => array(),
            'attribute'   => '',
            'multiple'    => false,
            'show_counts' => false,
        ) );

        if ( empty( $args['options'] ) ) {
            return;
        }

        $current_page_url = apply_filters('etheme_elementor_edit_mode', false) ? home_url() : $this->get_current_page_url();
        $base_link          = remove_query_arg(array('min_price', 'max_price'), $current_page_url);
        echo '<ul class="prices-list">';
	    foreach ( $args['options'] as $option ) {
		    $is_chosen = $args['current']['min'] == $option['range']['min'] && $args['current']['max'] == $option['range']['max'];
		    printf(
			    '<li class="price-list-item %s"><a rel="nofollow noopener" href="%s">%s</a>%s</li>',
			    $is_chosen ? 'chosen' : '',
			    $is_chosen ? $base_link : add_query_arg(array('min_price' => $option['range']['min'], 'max_price' => $option['range']['max']), $base_link),
			    $option['name'],
			    $args['show_counts'] ? '<span class="products-filter__count counter">' . $option['count'] . '</span>' : ''
		    );
	    }
        echo '</ul>';

        echo '<div class="price-filter-box">';

        printf(
            '<input type="number" name="min_%s" min="0" value="%s" placeholder="%s">',
            esc_attr( $args['name'] ),
            esc_attr( $args['current']['min'] ),
            esc_html__( 'Min', 'xstore-core' )
        );

        echo '<span class="line"></span>';

        printf(
            '<input type="number" name="max_%s" min="0" value="%s" placeholder="%s">',
            esc_attr( $args['name'] ),
            esc_attr( $args['current']['max'] ),
            esc_html__( 'Max', 'xstore-core' )
        );

        echo '<button type="submit" value="' . esc_attr__( 'Apply', 'xstore-core' ) . '" data-base-url="'.$base_link.'" class="btn btn-black medium">' . $args['button_text'] . '</button>';

        echo '</div>';
    }
	/**
	 * Get filtered min price for current products.
	 *
	 * @return int
	 */
	protected function get_filtered_price() {
		global $wpdb;
		$args       = wc()->query->get_main_query()->query_vars;
		$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
		if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			$tax_query[] = array(
				'taxonomy' => $args['taxonomy'],
				'terms'    => array( $args['term'] ),
				'field'    => 'slug',
			);
		}
		$tax_query = $this->add_active_filters_to_tax_query( $tax_query );
		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}
		$meta_query = new \WP_Meta_Query( $meta_query );
		$tax_query  = new \WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
		$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
			AND {$wpdb->posts}.post_status = 'publish'
			AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
			AND price_meta.meta_value > '' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];
		$product_ids = $this->get_filtered_product_ids();
		if ( is_array( $product_ids ) ) {
			if ( ! count( $product_ids ) ) {
				return (object) array(
					'min_price' => null,
					'max_price' => null,
				);
			}
			$sql .= " AND {$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")";
		}
		$search = \WC_Query::get_main_search_query_sql();
		if ( $search ) {
			$sql .= ' AND ' . $search;
		}
		$sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );
		return $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Get filtered min price for current products.
	 * WC default function
	 * @return int
	 */
	protected function get_filtered_price_default() {
		global $wpdb;

		$args       = WC()->query->get_main_query()->query_vars;
		$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			$tax_query[] = WC()->query->get_main_tax_query();
		}
		$tax_query = $this->add_active_filters_to_tax_query( $tax_query );

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$meta_query = new \WP_Meta_Query( $meta_query );
		$tax_query  = new \WP_Tax_Query( $tax_query );
		$search     = \WC_Query::get_main_search_query_sql();

		$meta_query_sql   = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql    = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$search_query_sql = $search ? ' AND ' . $search : '';
		$product_ids = $this->get_filtered_product_ids();
		$sale_query_sql = '';
		if ( is_array( $product_ids ) ) {
			if ( ! count( $product_ids ) ) {
				return (object) array(
					'min_price' => null,
					'max_price' => null,
				);
			}
			$sale_query_sql = " AND {$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")";
		}

		$sql = "
			SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
			FROM {$wpdb->wc_product_meta_lookup}
			WHERE product_id IN (
				SELECT ID FROM {$wpdb->posts}
				" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
				WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
				AND {$wpdb->posts}.post_status = 'publish'
				" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . $sale_query_sql . '
			)';

		$sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );

		return $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
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

    /**
     * Get current filter from the query string.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_current_filters() {
        // Cache the list of current filters in a property.
        if ( isset( $this->current_filters ) ) {
            return $this->current_filters;
        }

        $request = $_GET;
        $current_filters = array();

        if ( get_search_query() ) {
            $current_filters['s'] = get_search_query();

            if ( isset( $request['s'] ) ) {
                unset( $request['s'] );
            }
        }

        if ( isset( $request['paged'] ) ) {
            unset( $request['paged'] );
        }

        if ( isset( $request['filter'] ) ) {
            unset( $request['filter'] );
        }

        // Add chosen attributes to the list of current filter.
        if ( $_chosen_attributes = \WC_Query::get_layered_nav_chosen_attributes() ) {
            foreach ( $_chosen_attributes as $name => $data ) {
                $taxonomy_slug = wc_attribute_taxonomy_slug( $name );
                $filter_name   = 'filter_' . $taxonomy_slug;

                if ( ! empty( $data['terms'] ) ) {
                    // We use pretty slug name instead of encoded version of WC.
                    $terms = array_map( 'urldecode', $data['terms'] );

                    // Should we stop joining array? This value is used as array in most situation (except for hidden_filters).
                    $current_filters[ $filter_name ] = implode( ',', $terms );
                }

                if ( isset( $request[ $filter_name ] ) ) {
                    unset( $request[ $filter_name ] );
                }

                if ( 'or' == $data['query_type'] ) {
                    $query_type                     = 'query_type_' . $taxonomy_slug;
                    $current_filters[ $query_type ] = 'or';

                    if ( isset( $request[ $query_type ] ) ) {
                        unset( $request[ $query_type ] );
                    }
                }
            }
        }

        // Add taxonomy terms to the list of current filter.
        // This step is required because of the filter url is always the shop url.
        if ( is_product_taxonomy() ) {
            $taxonomy = get_queried_object()->taxonomy;
            $term     = get_query_var( $taxonomy );

            if ( taxonomy_is_product_attribute( $taxonomy ) ) {
                $taxonomy_slug = wc_attribute_taxonomy_slug( $taxonomy );
                $filter_name   = 'filter_' . $taxonomy_slug;

                if ( ! isset( $current_filters[ $filter_name ] ) ) {
                    $current_filters[ $filter_name ] = $term;
                }
            } elseif ( ! isset( $current_filters[ $taxonomy ] ) ) {
                $current_filters[ $taxonomy ] = $term;
            }
        }

        foreach ( $request as $name => $value ) {
            $current_filters[ $name ] = $value;
        }

        $this->current_filters = $current_filters;

        return $this->current_filters;
    }
}
