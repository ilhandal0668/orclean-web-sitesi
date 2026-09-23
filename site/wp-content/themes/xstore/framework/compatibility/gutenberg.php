<?php
/**
 * Gutenberg actions/filters
 *
 * @package    gutenberg.php
 * @since      8.3.4
 * @author     Stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

$wp_is_block_theme = wp_is_block_theme();

add_action('init', function () {
	if ( get_theme_mod( 'disable_block_css', 0 ) ) {
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
	}
});

if ( $wp_is_block_theme ) {
    add_filter('etheme_is_fse_theme', '__return_true');
    $disable_theme_features = array(
        'etheme_grid_list_switcher_enabled',
        'etheme_products_per_page_select_enabled',
        'etheme_shop_top_toolbar_enabled',
    );
    foreach ($disable_theme_features as $disable_theme_feature) {
        add_filter($disable_theme_feature, '__return_false');
    }

    add_filter('etheme_results_count_enabled', '__return_true');

    add_action('init', 'etheme_fse_blocks_customization');

    // enable gutenberg for woocommerce
    add_filter( 'use_block_editor_for_post_type', 'etheme_activate_gutenberg_product', 10, 2 );

    // enable taxonomy fields for woocommerce with gutenberg on
    add_filter( 'woocommerce_taxonomy_args_product_cat', 'etheme_enable_taxonomy_rest' );
    add_filter( 'woocommerce_taxonomy_args_product_tag', 'etheme_enable_taxonomy_rest' );

}

function etheme_activate_gutenberg_product( $can_edit, $post_type ) {
    if ( $post_type == 'product' ) {
        $can_edit = true;
    }
    return $can_edit;
}

function etheme_enable_taxonomy_rest( $args ) {
    $args['show_in_rest'] = true;
    return $args;
}

function etheme_fse_blocks_customization() {
    register_block_style(
        'core/button',
        [
            'name'  => 'et-fse-dark',
            'label' => __( 'Dark', 'xstore' ),
        ]
    );
    register_block_style(
        'core/button',
        [
            'name'  => 'et-fse-light',
            'label' => __( 'Light', 'xstore' ),
        ]
    );
    register_block_style(
        'core/button',
        [
            'name'  => 'et-fse-active',
            'label' => __( 'Active', 'xstore' ),
        ]
    );
    register_block_style(
        'core/button',
        [
            'name'  => 'et-fse-bordered',
            'label' => __( 'Bordered', 'xstore' ),
        ]
    );
    register_block_style(
        'woocommerce/product-button',
        [
            'name'  => 'et-fse-dark',
            'label' => __( 'Dark', 'xstore' ),
        ]
    );
}

if ( $wp_is_block_theme ) {
    add_filter('etheme_should_register_style', function ($value, $script_data) {
        if ( isset($script_data['gutenberg']) ) {
            return $value;
        }
        return false;
    }, 10, 2);

    add_action( 'wp_enqueue_scripts', 'etheme_fse_assets', 50);
}

if ( !function_exists('etheme_fse_assets') ) {
    function etheme_fse_assets() {
//        etheme_enqueue_style( 'banner' );
        etheme_enqueue_style( 'banners-global' );

        wp_enqueue_script('etheme_waypoints');

        $data = "
            // mostly for gutenberg
            etTheme.et_counter_trigger = function (_this) {
                var num = jQuery(_this).text();
                let speed = jQuery(_this).data('speed') ?? 2000;
                let start_counter = 0;

                var decimal = 0;
                if (num.indexOf(".") > 0) { // if number is Decimal
                    decimal = num.toString().split(".")[1].length;
                }
                if ( decimal <= 0 && num.toString().length > 1) {
                    start_counter = 10;
                }  
                
                if ( decimal <= 0 && num.toString().length > 2) {
                    start_counter = 100;
                }  

                jQuery(_this).prop('counter',start_counter).animate({
                    counter: jQuery(_this).text()
                }, {
                    duration: speed,
                    easing: 'swing',
                    step: function (now) {
                        jQuery(_this).text(parseFloat(now).toFixed(decimal));
                    }
                });

                jQuery(_this).data('done', 'yes');
            };
        
            etTheme.autoinit.et_counter = etTheme.et_counter = function () {
                jQuery('.xstore-counter').each(function () {
                    etTheme.waypoint(jQuery(this), () => etTheme.et_counter_trigger(this));
                });
            };
        ";

        wp_add_inline_script(apply_filters('etheme_elementor_force_localize_global_assets', 'etheme',  'general'), $data);
    }
}

//add_action( 'admin_enqueue_scripts', 'etheme_fse_load_admin_styles', 150 );
if ( $wp_is_block_theme ) {
    add_action( 'enqueue_block_assets', 'etheme_fse_load_admin_styles', 30 );
}

if ( !function_exists('etheme_fse_load_admin_styles') ) {
    function etheme_fse_load_admin_styles() {
        if ( apply_filters( 'etheme_fse_disable_gutenberg_backend_css', false ) || ! is_admin() ) {
            return;
        }

        $dir_uri = get_template_directory_uri();
        $icons_type = ( etheme_get_option('bold_icons', 0) ) ? 'bold' : 'light';
        wp_register_style( 'xstore-icons-font', false );
        wp_enqueue_style( 'xstore-icons-font' );
        wp_add_inline_style( 'xstore-icons-font',
            "@font-face {
		  font-family: 'xstore-icons';
		  src:
		    url('".$dir_uri."/fonts/xstore-icons-".$icons_type.".ttf') format('truetype'),
		    url('".$dir_uri."/fonts/xstore-icons-".$icons_type.".woff2') format('woff2'),
		    url('".$dir_uri."/fonts/xstore-icons-".$icons_type.".woff') format('woff'),
		    url('".$dir_uri."/fonts/xstore-icons-".$icons_type.".svg#xstore-icons') format('svg');
		  font-weight: normal;
		  font-style: normal;
		  font-display: swap;
		}"
        );
        wp_enqueue_style( 'xstore-icons-font-style', $dir_uri . '/css/xstore-icons.css' );

        wp_enqueue_style('etheme-gutenberg-style', $dir_uri . '/css/gutenberg' . ETHEME_MIN_CSS.'.css');

        wp_enqueue_style('etheme_fse-editor-css', ETHEME_CODE_CSS.'gutenberg-editor.css');
    }
}
