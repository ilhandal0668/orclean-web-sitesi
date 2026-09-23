<?php
/**
 * Smart Coupons Analytics Banner
 *
 * @since 2.5.7
 *
 * @package  Product_Import_Export_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Wbte_Smart_Coupons_Analytics_Banner' ) ) {

    class Wbte_Smart_Coupons_Analytics_Banner {

        public $module_id               = '';
        public static $module_id_static = '';
        public $module_base             = 'wbte_smart_coupons_analytics_banner';

        /**
         * The single instance of the class
         *
         * @var self
         */
        private static $instance = null;

        /**
         * The dismiss option name in WP Options table
         *
         * @var string
         */
        private $analytics_page_dismiss_option = 'wbte_smart_coupons_analytics_dismiss_2026';

        /**
         * Constructor
         * @since 2.5.7
         */
        public function __construct() {
            $this->module_id        = $this->module_base;
            self::$module_id_static = $this->module_id;

            if ( ! in_array( 'wt-smart-coupon-pro/wt-smart-coupon-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
                add_action('admin_footer', array($this, 'sc_analytics_inject_script'));
                add_action('wp_ajax_wbte_smart_coupons_analytics_dismiss_2026', array($this, 'wbte_smart_coupons_analytics_dismiss_2026_banner'));
            }
        }

        /**
         * Ensures only one instance is loaded or can be loaded.
         *
         * @since 2.5.7
         * @return self
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Enqueue banner styles
         *
         * @since 2.5.7
         */
        public function enqueue_styles() {
            if ( ! $this->sc_analytics_should_display_banner() ) {
                return;
            }

            wp_enqueue_style('wt-p-iew-smart-coupons-analytics-banner',plugin_dir_url(__FILE__) . 'assets/css/wbte-smart-coupons-analytics-banner.css',array(),WT_P_IEW_VERSION);
            wp_enqueue_script('wt-p-iew-smart-coupons-analytics-banner',plugin_dir_url(__FILE__) . 'assets/js/wbte-smart-coupons-analytics-banner.js',array('jquery'),WT_P_IEW_VERSION,true);

            wp_localize_script('wt-p-iew-smart-coupons-analytics-banner', 'wbte_smart_coupons_analytics_banner_params', array(
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => wp_create_nonce('wbte_smart_coupons_analytics_banner_nonce'),
            ));
        }

        /**
         * Check if we should display the banner
         *
         * @since 2.5.7
         * @return boolean
         */
        private function sc_analytics_should_display_banner() {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
            // Enqueue on any wc-admin screen; the JS decides when the Revenue tab is active
            // (WC Analytics is a SPA — the current tab can change without a new PHP request).
            if ( ! $screen || 'woocommerce_page_wc-admin' !== $screen->id ) {
                return false;
            }

            return ! get_option( $this->analytics_page_dismiss_option ) && ! defined( 'WBTE_SMART_COUPONS_ANALYTICS_BANNER' );
        }

        /**
         * Ajax handler to dismiss the Smart Coupons analytics banner
         *
         * @since 2.5.7
         */
        public function wbte_smart_coupons_analytics_dismiss_2026_banner() {
            check_ajax_referer( 'wbte_smart_coupons_analytics_banner_nonce', 'nonce' );
            update_option( $this->analytics_page_dismiss_option, true );
            wp_send_json_success();
        }

        /**
         * Inject analytics script in admin footer
         *
         * @since 2.5.7
         */
        public function sc_analytics_inject_script() {
            if ( ! $this->sc_analytics_should_display_banner() ) {
                return;
            }

            ob_start();

            define( 'WBTE_SMART_COUPONS_ANALYTICS_BANNER', true );

            $sale_link = 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_analytics_revenue_tab&utm_medium=smart_coupons_free&utm_campaign=smart_coupons' ;

            ?>

                <div class="wbte_sc_analytics_banner">
                    <div class="wbte_sc_box">
                        <div class="wbte_sc_text">
                            <div class="wbte_sc_header">
                                <img src="<?php echo esc_url( WT_P_IEW_PLUGIN_URL . 'admin/banner/assets/images/idea_bulb_orange.svg' ); ?>" alt="">
                                <span class="wbte_sc_title"><?php esc_html_e( 'Did you know?', 'product-import-export-for-woo' ); ?></span>
                            </div>
                            <div class="wbte_sc_body">
                                <?php
                                echo wp_kses_post(
                                    sprintf(
                                        // translators: %1$s = <strong>BOGO offers</strong>, %2$s = <strong>giveaways</strong>, %3$s = <strong>store credits</strong>. Highlighted phrases must remain wrapped so they render bold in the banner.
                                        __( 'WebToffee Smart Coupons lets you create %1$s, %2$s, and %3$s to help grow your store\'s revenue.', 'product-import-export-for-woo' ),
                                        '<strong>' . esc_html__( 'BOGO offers', 'product-import-export-for-woo' ) . '</strong>',
                                        '<strong>' . esc_html__( 'giveaways', 'product-import-export-for-woo' ) . '</strong>',
                                        '<strong>' . esc_html__( 'store credits', 'product-import-export-for-woo' ) . '</strong>'
                                    )
                                );
                                ?>
                            </div>
                        </div>
                        <div class="wbte_sc_actions">
                            <a href="<?php echo esc_url( $sale_link ); ?>" class="btn-primary" target="_blank"><?php esc_html_e( 'Get Plugin Now', 'product-import-export-for-woo' ); ?></a>
                            <button type="button" class="notice-dismiss wbte_smart_coupons_analytics_dismiss_2026">
                                <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'product-import-export-for-woo' ); ?></span>
                            </button>
                        </div>
                    </div>
                </div>

            <?php
            $output = ob_get_clean();

            if ( empty( trim( $output ) ) ) {
                return;
            }
            ?>
            <script type="text/javascript">
                (function() {
                    'use strict';

                    var bannerHTML = <?php echo wp_json_encode( wp_kses_post( $output ) ); ?>;
                    var bannerSelector = '.wbte_sc_analytics_banner';
                    var revenuePath = '/analytics/revenue';
                    // WC Analytics is a SPA — the tab can change via history.pushState without a
                    // new page load. If we key off $_GET['path'] server-side, the banner only
                    // shows when the user lands on Revenue directly (or refreshes). We render
                    // the script on every wc-admin screen and let this IIFE decide when to
                    // inject/remove based on the live URL.
                    var sessionDismissed = false;

                    function getCurrentPath() {
                        try {
                            return new URL( window.location.href ).searchParams.get( 'path' ) || '';
                        } catch ( e ) {
                            return '';
                        }
                    }

                    function shouldShow() {
                        return ! sessionDismissed && getCurrentPath() === revenuePath;
                    }

                    function injectBanner() {
                        if ( document.querySelector( bannerSelector ) ) {
                            return; // already present
                        }
                        var header = document.querySelector( '.woocommerce-layout__header' );
                        if ( ! header || ! header.parentNode ) {
                            return; // WC layout not ready yet
                        }
                        var wrapper = document.createElement( 'div' );
                        wrapper.innerHTML = bannerHTML;
                        var node = wrapper.firstElementChild || wrapper;
                        header.parentNode.insertBefore( node, header.nextSibling );
                    }

                    function removeBanner() {
                        var existing = document.querySelector( bannerSelector );
                        if ( existing && existing.parentNode ) {
                            existing.parentNode.removeChild( existing );
                        }
                    }

                    function update() {
                        if ( shouldShow() ) {
                            injectBanner();
                        } else {
                            removeBanner();
                        }
                    }

                    // Dismiss: existing dismiss handler fires AJAX; also mark the session so
                    // we don't re-inject when the user navigates away and back.
                    document.addEventListener( 'click', function( e ) {
                        var target = e.target;
                        if ( target && target.closest && target.closest( '.wbte_smart_coupons_analytics_dismiss_2026' ) ) {
                            sessionDismissed = true;
                            removeBanner();
                        }
                    }, true );

                    // Initial run — give React a moment to paint the layout header
                    function initialUpdate( attempts ) {
                        attempts = attempts || 0;
                        if ( document.querySelector( '.woocommerce-layout__header' ) ) {
                            update();
                        } else if ( attempts < 20 ) {
                            setTimeout( function() { initialUpdate( attempts + 1 ); }, 250 );
                        }
                    }
                    if ( document.readyState === 'loading' ) {
                        document.addEventListener( 'DOMContentLoaded', function() { initialUpdate(); } );
                    } else {
                        initialUpdate();
                    }

                    // React to SPA navigation
                    window.addEventListener( 'popstate', function() { setTimeout( update, 50 ); } );

                    var origPushState = window.history.pushState;
                    window.history.pushState = function() {
                        var ret = origPushState.apply( this, arguments );
                        setTimeout( update, 50 );
                        return ret;
                    };

                    var origReplaceState = window.history.replaceState;
                    window.history.replaceState = function() {
                        var ret = origReplaceState.apply( this, arguments );
                        setTimeout( update, 50 );
                        return ret;
                    };
                })();
            </script>
            <?php
        }
    }

    /**
     * Initialize the Smart Coupons analytics banner
     *
     * @since 2.5.7
     */
    add_action('admin_init', array('Wbte_Smart_Coupons_Analytics_Banner', 'get_instance'));

}
