<?php
/**
 * Main class for Cross Promotion Banners.
 *
 * @version 1.0.0
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( version_compare( WBTE_PIEW_CROSS_PROMO_BANNER_VERSION, get_option( 'wbfte_promotion_banner_version', WBTE_PIEW_CROSS_PROMO_BANNER_VERSION ), '==' ) ) {

	/**
	 * Banner 4 — WC Settings → Products tab Premium-Product CTA.
	 * Loaded outside the Wbte_Cross_Promotion_Banners class_exists gate so that
	 * the product-specific banner still loads when another basic plugin (which
	 * has its own coordinator definition) is also active and loads alphabetically
	 * earlier — otherwise the class_exists guard would skip this require entirely.
	 * The banner file itself has class_exists guards to prevent double load.
	 *
	 * @since 2.6.4
	 */
	require_once plugin_dir_path( __FILE__ ) . 'class-wbte-iew-wc-settings-products-banner.php';

}

if ( version_compare( WBTE_PIEW_CROSS_PROMO_BANNER_VERSION, get_option( 'wbfte_promotion_banner_version', WBTE_PIEW_CROSS_PROMO_BANNER_VERSION ), '==' ) && ! class_exists( 'Wbte_Cross_Promotion_Banners' ) ) {

	class Wbte_Cross_Promotion_Banners {

		public function __construct() {

			/**
			 * Class includes helper functions for pklist invoice cta banner
			 */
			if ( ! get_option( 'wt_hide_invoice_cta_banner' ) ) {
				require_once plugin_dir_path(__FILE__) . 'class-wt-pklist-cta-banner.php';
			}

			/**
			 * Class includes helper functions for smart coupon cta banner
			 */
			if ( ! get_option( 'wt_hide_smart_coupon_cta_banner' ) ) {
				require_once plugin_dir_path(__FILE__) . 'class-wt-smart-coupon-cta-banner.php';
			}

			/**
			 * Class includes helper functions for pklist invoice cta banner
			 */
			if ( ! get_option( 'wt_hide_product_ie_cta_banner' ) ) {
				require_once plugin_dir_path(__FILE__) . 'class-wt-p-iew-cta-banner.php';
			}

			/**
			 * Banner 1 — History tab common Suite CTA.
			 * Same file shipped in all three basic plugins; the define guard
			 * ensures only the first-loaded copy executes when multiple basic
			 * plugins are active simultaneously.
			 *
			 * @since 2.6.4
			 */
			if ( ! defined( 'WT_IEW_HISTORY_BANNER_LOADED' ) ) {
				define( 'WT_IEW_HISTORY_BANNER_LOADED', true );
				require_once plugin_dir_path( __FILE__ ) . 'class-wbte-iew-history-banner.php';
			}

			/**
			 * Banner 2 — Analytics → Products tab (Premium / Suite rotation).
			 * Shared across the three basic plugins; define guard prevents duplicate load.
			 *
			 * @since 2.6.4
			 */
			if ( ! defined( 'WT_IEW_ANALYTICS_PRODUCTS_BANNER_LOADED' ) ) {
				define( 'WT_IEW_ANALYTICS_PRODUCTS_BANNER_LOADED', true );
				require_once plugin_dir_path( __FILE__ ) . 'class-wbte-iew-analytics-products-banner.php';
			}

			/**
			 * Banner 3 — Analytics → Orders tab (Premium / Suite rotation).
			 * Shared across the three basic plugins; define guard prevents duplicate load.
			 *
			 * @since 2.6.4
			 */
			if ( ! defined( 'WT_IEW_ANALYTICS_ORDERS_BANNER_LOADED' ) ) {
				define( 'WT_IEW_ANALYTICS_ORDERS_BANNER_LOADED', true );
				require_once plugin_dir_path( __FILE__ ) . 'class-wbte-iew-analytics-orders-banner.php';
			}

			// Banner 4 is loaded at file-top, outside this class_exists gate (see above).

		}

		public static function get_banner_version() {
			return WBTE_PIEW_CROSS_PROMO_BANNER_VERSION;
		}
	}

	new Wbte_Cross_Promotion_Banners();
}