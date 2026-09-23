<?php
/**
 * WooCommerce → Settings → Products tab cross-promotion banner.
 *
 * Single-variant Premium-Product CTA at the top
 * of the WC Settings → Products tab. Basic product plugin only — no define
 * guard needed since this banner ships to one plugin.
 *
 * @package Product_Import_Export_For_Woo
 * @since   2.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wbte_Iew_Wc_Settings_Products_Banner' ) ) {

	/**
	 * Banner 4 — WC Settings → Products tab (Premium-Product CTA).
	 */
	class Wbte_Iew_Wc_Settings_Products_Banner {

		const DISMISS_OPTION = 'wt_iew_hide_wc_settings_products_premium_cta_dismissed_at';
		const AJAX_ACTION    = 'wt_iew_dismiss_wc_settings_products_banner';
		const NONCE_ACTION   = 'wt_iew_wc_settings_products_banner';

		const PLUGIN_PRODUCT_PREMIUM = 'wt-import-export-for-woo-product/wt-import-export-for-woo-product.php';
		const PLUGIN_IES             = 'import-export-suite-for-woocommerce/import-export-suite-for-woocommerce.php';

		/**
		 * Hook everything up.
		 */
		public function __construct() {
			add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_dismiss_ajax' ) );

			if ( $this->is_suppressed() ) {
				return;
			}

			add_action( 'admin_notices', array( $this, 'render_banner' ) );
		}

		/**
		 * Is the given plugin active on the site?
		 *
		 * @param string $plugin_file Plugin folder/file path.
		 * @return bool
		 */
		private function is_plugin_active( $plugin_file ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			return is_plugin_active( $plugin_file );
		}

		/**
		 * Suppression: hide when Product Premium or IES is active, or once dismissed.
		 *
		 * @return bool
		 */
		private function is_suppressed() {
			if ( $this->is_plugin_active( self::PLUGIN_PRODUCT_PREMIUM ) || $this->is_plugin_active( self::PLUGIN_IES ) ) {
				return true;
			}
			$dismissed_at = absint( get_option( self::DISMISS_OPTION, 0 ) );
			return $dismissed_at > 0;
		}

		/**
		 * Current admin screen check — WC Settings → Products tab.
		 *
		 * @return bool
		 */
		private function is_wc_settings_products_screen() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen || 'woocommerce_page_wc-settings' !== $screen->id ) {
				return false;
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection.
			$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			return 'products' === $tab;
		}

		/**
		 * Render the banner HTML on the WC Settings → Products page.
		 */
		public function render_banner() {
			if ( ! $this->is_wc_settings_products_screen() ) {
				return;
			}

			$accent      = '#007CBA';
			$button_bg   = '#2270B1';
			$ajax_url    = admin_url( 'admin-ajax.php' );
			$nonce       = wp_create_nonce( self::NONCE_ACTION );
			$cta_url     = 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_product_import&utm_medium=woo_settings_products&utm_campaign=Product_import_Export';
			$dismiss_btn = esc_attr__( 'Dismiss', 'product-import-export-for-woo' );

			$title = __( 'Take WooCommerce imports/exports further with Premium', 'product-import-export-for-woo' );
			$body  = __( 'Handle complex product types, automate imports/exports, and manage large catalogs with advanced filters and broader file support.', 'product-import-export-for-woo' );
			$cta   = __( 'Upgrade Now', 'product-import-export-for-woo' );

			?>
			<div id="wt-iew-wc-settings-products-cta-banner" class="notice" style="position: relative; padding: 20px 40px 25px 28px; background-color: #fff; border-left: 6px solid <?php echo esc_attr( $accent ); ?>; border-radius: 3px; margin: 15px 0;">
				<button type="button" class="wt-iew-wc-settings-products-dismiss" aria-label="<?php echo esc_attr( $dismiss_btn ); ?>" style="position: absolute; top: 14px; right: 18px; border: none; background: none; color: #6E6E6E; cursor: pointer; font-size: 20px; line-height: 1; padding: 0;">&times;</button>
				<h2 style="margin: 0 0 12px; color: #000; font-weight: 500; font-size: 17px; line-height: 21px;"><?php echo esc_html( $title ); ?></h2>
				<p style="margin: 0 0 16px; font-size: 14px; color: #434343; line-height: 21px; max-width: 900px;"><?php echo esc_html( $body ); ?></p>
				<a href="<?php echo esc_url( $cta_url ); ?>" target="_blank" rel="noopener noreferrer" style="display: inline-block; background: <?php echo esc_attr( $button_bg ); ?>; border: 0; color: #fff; padding: 7px 24px; border-radius: 3px; text-decoration: none; font-size: 13px; font-weight: 500; line-height: 18px; min-width: 135px; text-align: center; box-shadow: none;"><?php echo esc_html( $cta ); ?></a>
			</div>

			<script type="text/javascript">
				( function ( $ ) {
					$( document ).ready( function () {
						$( '.wt-iew-wc-settings-products-dismiss' ).on( 'click', function ( e ) {
							e.preventDefault();
							var banner = $( '#wt-iew-wc-settings-products-cta-banner' );
							$.ajax( {
								url:  '<?php echo esc_url( $ajax_url ); ?>',
								type: 'POST',
								data: {
									action:      '<?php echo esc_js( self::AJAX_ACTION ); ?>',
									option_name: '<?php echo esc_js( self::DISMISS_OPTION ); ?>',
									nonce:       '<?php echo esc_js( $nonce ); ?>'
								},
								success: function () {
									banner.fadeOut( 200 );
								}
							} );
						} );
					} );
				} )( jQuery );
			</script>
			<?php
		}

		/**
		 * AJAX dismiss handler — nonce + explicit allowlist.
		 */
		public function handle_dismiss_ajax() {
			check_ajax_referer( self::NONCE_ACTION, 'nonce' );

			if ( ! isset( $_POST['option_name'] ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Missing option name.', 'product-import-export-for-woo' ) ),
					400
				);
			}

			$option_name = sanitize_text_field( wp_unslash( $_POST['option_name'] ) );

			$allowed_options = array( self::DISMISS_OPTION );

			if ( ! in_array( $option_name, $allowed_options, true ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Invalid option.', 'product-import-export-for-woo' ) ),
					400
				);
			}

			update_option( $option_name, time() );
			wp_send_json_success();
		}
	}

	new Wbte_Iew_Wc_Settings_Products_Banner();
}
