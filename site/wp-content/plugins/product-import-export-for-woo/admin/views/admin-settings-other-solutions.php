<?php
/**
 * "Other Solutions" admin tab — sidebar categories + card grid layout.
 *
 * Ported from wt-woocommerce-related-products' "You May Also Need" template.
 * Class prefix renamed wt-crp-os-* → wt-piew-os-* to keep both plugins collision-free.
 *
 * @package Product_Import_Export_For_Woo
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template file; variables are template-scoped, not plugin globals.
defined( 'WPINC' ) || die;

$wt_piew_img_base = esc_url( WT_P_IEW_PLUGIN_URL . 'assets/images/other_solutions' );

$wt_piew_categories = array(
	'ecommerce-promotions' => array(
		'label'      => __( 'E-commerce Promotions', 'product-import-export-for-woo' ),
		'subtitle'   => __( 'Create and run successful promotional campaigns with the best marketing tools for WooCommerce', 'product-import-export-for-woo' ),
		'icon'       => 'sidebar-ecommerce-promotions.svg',
		'hero'       => null,
		'plugins'    => array(
			array(
				'type'     => 'standard',
				'name'     => __( 'Smart Coupons for WooCommerce', 'product-import-export-for-woo' ),
				'icon'     => 'smart-coupons-plugin.png',
				'rating'   => '4.9',
				'features' => array(
					__( 'Advanced BOGO Coupons', 'product-import-export-for-woo' ),
					__( 'Offer store credits', 'product-import-export-for-woo' ),
					__( 'Create attractive gift cards', 'product-import-export-for-woo' ),
					__( 'Give away product coupons', 'product-import-export-for-woo' ),
					__( 'Coupons based on past purchases', 'product-import-export-for-woo' ),
					__( 'Restrict coupons by country', 'product-import-export-for-woo' ),
					__( 'Create and offer sign-up discount coupons', 'product-import-export-for-woo' ),
					__( 'Cart abandonment coupons', 'product-import-export-for-woo' ),
					__( 'Customizable countdown sales banner', 'product-import-export-for-woo' ),
					__( 'Bulk generate coupons', 'product-import-export-for-woo' ),
					__( 'Import and export coupons', 'product-import-export-for-woo' ),
					__( 'Coupon embeds', 'product-import-export-for-woo' ),
					__( 'Allow coupon combinations', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=smart_coupons',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'URL Coupons for WooCommerce', 'product-import-export-for-woo' ),
				'icon'     => 'url-coupons-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Generate custom coupon URLs', 'product-import-export-for-woo' ),
					__( 'Set up a redirect page', 'product-import-export-for-woo' ),
					__( 'Automatically add products', 'product-import-export-for-woo' ),
					__( 'Create QR code coupons', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=URL_Coupons',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Product Recommendations', 'product-import-export-for-woo' ),
				'icon'     => 'product-recommendation-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Automatically generate suggestions based on order history', 'product-import-export-for-woo' ),
					__( 'Display recommended products on the product pages', 'product-import-export-for-woo' ),
					__( 'Quick setup page to add & edit recommendations', 'product-import-export-for-woo' ),
					__( 'Multiple product recommendation layouts', 'product-import-export-for-woo' ),
					__( 'Set up discounts on the recommended product bundle', 'product-import-export-for-woo' ),
					__( 'Manually create a bought-together list', 'product-import-export-for-woo' ),
					__( 'Use upsells, cross-sells, & related products as frequently bought products', 'product-import-export-for-woo' ),
					__( 'Customize the title, button, and label texts', 'product-import-export-for-woo' ),
					__( 'Customize the display of the recommended products', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-product-recommendations/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Product_Recommendations',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Coupon Generator', 'product-import-export-for-woo' ),
				'icon'     => 'coupon-generator-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Bulk generate WooCommerce coupons', 'product-import-export-for-woo' ),
					__( 'Bulk export WooCommerce coupons to CSV', 'product-import-export-for-woo' ),
					__( 'Add usage restrictions to coupons', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-coupon-generator/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Coupon_Generator',
			),
			array(
				'type'        => 'standard-with-image',
				'name'        => __( 'WooCommerce Gift Cards', 'product-import-export-for-woo' ),
				'icon'        => 'gift-card-plugin.png',
				'rating'      => 'stars',
				'features'    => array(
					__( 'Create unlimited gift cards', 'product-import-export-for-woo' ),
					__( 'Email gift cards to customers', 'product-import-export-for-woo' ),
					__( 'Provide refunds to store credit', 'product-import-export-for-woo' ),
					__( '20+ predefined gift card templates', 'product-import-export-for-woo' ),
					__( 'Category wise template listing', 'product-import-export-for-woo' ),
					__( 'Add custom templates for gift cards', 'product-import-export-for-woo' ),
					__( 'Generate gift cards based on order status', 'product-import-export-for-woo' ),
					__( 'Manage user credit balance', 'product-import-export-for-woo' ),
					__( 'Fixed and custom gift card amounts', 'product-import-export-for-woo' ),
					__( 'Add usage restrictions for gift cards', 'product-import-export-for-woo' ),
				),
				'url'         => 'https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=WooCommerce_Gift_Cards',
				'image_src'   => 'woocommerce-giftcard-hero.svg',
				'card_class'  => 'wt-piew-os-card--gift-cards',
				'plugin_file' => 'wt-woocommerce-gift-cards/wt-woocommerce-gift-cards.php',
			),
		),
		'standalone' => array(
			'name'        => __( 'ECommerce Marketing Automation App', 'product-import-export-for-woo' ),
			'icon'        => 'ema-app-plugin.png',
			'desc'        => __( 'Create signup forms, popups, and automated email campaigns with pre-built workflow templates to capture leads, recover abandoned carts, and grow sales.', 'product-import-export-for-woo' ),
			'screenshot'  => 'ema-screenshot.svg',
			'url'         => 'https://www.webtoffee.com/product/ecommerce-marketing-automation/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=EMA',
			'plugin_file' => 'ecommerce-marketing-automation/ecommerce-marketing-automation.php',
		),
		'bundle'     => array(
			'tag_emoji'    => '📣',
			'tag_color'    => 'yellow',
			'tag'          => __( 'Promotion Bundle', 'product-import-export-for-woo' ),
			'title'        => __( 'WooCommerce Promotion Bundle', 'product-import-export-for-woo' ),
			'url'          => 'https://www.webtoffee.com/woocommerce-promotions/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Promotion_Bundle',
			'desc'         => __( 'Make powerful promotional campaigns with our WooCommerce promotion bundle. Create coupon promotions, set up gift cards, and implement popular product recommendation strategies.', 'product-import-export-for-woo' ),
			'pills'        => array(
				__( 'Smart Coupons', 'product-import-export-for-woo' ),
				__( 'Product recommendation', 'product-import-export-for-woo' ),
				__( 'Gift cards', 'product-import-export-for-woo' ),
			),
			'price_orig'   => '$277',
			'price_sale'   => '$194',
			'savings'      => __( 'Save up to 30% off', 'product-import-export-for-woo' ),
			'illustration' => 'woocommerce-promotion-bundle-hero.svg',
		),
	),
	'privacy-compliance'   => array(
		'label'      => __( 'Privacy Compliance', 'product-import-export-for-woo' ),
		'subtitle'   => __( 'Ensure compliance with major cookie laws, including, GDPR, CCPA, LGPD, CNIL, and more', 'product-import-export-for-woo' ),
		'icon'       => 'sidebar-privacy-compliance.svg',
		'hero'       => array(
			'name'        => __( 'GDPR Cookie Consent Plugin (CCPA Ready)', 'product-import-export-for-woo' ),
			'icon'        => 'gdpr-plugin.png',
			'rating'      => 'stars',
			'image'       => 'cookie-consent.svg',
			'desc'        => __( 'This Google-certified CMP lets you create a customizable cookie banner, manage user consent, and ensure global privacy compliance with automatic script blocking.', 'product-import-export-for-woo' ),
			'features'    => array(),
			'url'         => 'https://www.webtoffee.com/product/gdpr-cookie-consent/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=GDPR',
			'plugin_file' => 'webtoffee-cookie-consent/webtoffee-cookie-consent.php',
		),
		'plugins'    => array(
			array(
				'type'        => 'standard-with-image',
				'name'        => __( 'EU Order Withdrawal Button Plugin for WooCommerce', 'product-import-export-for-woo' ),
				'icon'        => 'eu-withdrawal-plugin-icon.svg',
				'rating'      => 'stars',
				'features'    => array(
					__( 'Add "Request Withdrawal" button to WooCommerce', 'product-import-export-for-woo' ),
					__( 'Supports guest withdrawal option', 'product-import-export-for-woo' ),
					__( 'Two-step confirmation to prevent errors', 'product-import-export-for-woo' ),
					__( 'Full or partial order withdrawal support', 'product-import-export-for-woo' ),
					__( 'Dedicated admin dashboard for all requests', 'product-import-export-for-woo' ),
					__( 'Send email confirmation to customers', 'product-import-export-for-woo' ),
				),
				'url'         => 'https://www.webtoffee.com/product/eu-withdrawal-button/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=EU_Withdarawal_Button',
				'image_src'   => 'eu-withdrawal-hero.svg',
				'card_class'  => 'wt-piew-os-card--full-width',
				'plugin_file' => 'wt-eu-withdrawal-button/wt-eu-withdrawal-button.php',
			),
		),
		'standalone' => null,
		'bundle'     => null,
	),
	'data-import-export'   => array(
		'label'      => __( 'Data Import & Export', 'product-import-export-for-woo' ),
		'subtitle'   => __( 'The best-in-class import, export, and migration solutions for your WooCommerce data', 'product-import-export-for-woo' ),
		'icon'       => 'sidebar-data-import-export.svg',
		'hero'       => null,

		// Product Import Export is intentionally omitted here — this is the free
		// version of that product; we don't cross-sell ourselves on our own page.
		'plugins'    => array(
			array(
				'type'        => 'standard',
				'name'        => __( 'Order, Coupon, Subscription Export Import', 'product-import-export-for-woo' ),
				'icon'        => 'order-ie-plugin.png',
				'rating'      => '4.6',
				'features'    => array(
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'product-import-export-for-woo' ),
					__( 'Schedule automated import & export', 'product-import-export-for-woo' ),
					__( 'Email customers on order status change', 'product-import-export-for-woo' ),
					__( 'Create users on order import', 'product-import-export-for-woo' ),
					__( 'Filter export by products, order status, email, date, etc', 'product-import-export-for-woo' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'product-import-export-for-woo' ),
					__( 'Export to FTP/SFTP', 'product-import-export-for-woo' ),
					__( 'Advanced filters and customizations for import & export', 'product-import-export-for-woo' ),
					__( 'Add & update data while importing', 'product-import-export-for-woo' ),
					__( 'Maintains action history and debug logs', 'product-import-export-for-woo' ),
					__( 'Compatible with major 3rd-party plugins', 'product-import-export-for-woo' ),
				),
				'url'         => 'https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Order_Import_Export',
				'plugin_file' => 'order-import-export-for-woocommerce/order-import-export-for-woocommerce.php',
			),
			array(
				'type'        => 'standard',
				'name'        => __( 'User Import Export Plugin', 'product-import-export-for-woo' ),
				'icon'        => 'user-ie-plugin.png',
				'rating'      => '5.0',
				'features'    => array(
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'product-import-export-for-woo' ),
					__( 'Schedule automated import and export', 'product-import-export-for-woo' ),
					__( 'Customize and send emails to new users on import', 'product-import-export-for-woo' ),
					__( 'Retain user passwords on import/export', 'product-import-export-for-woo' ),
					__( 'Export and import custom fields and third-party plugin fields', 'product-import-export-for-woo' ),
					__( 'Filter by user role, email, date, etc', 'product-import-export-for-woo' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'product-import-export-for-woo' ),
					__( 'Export to FTP/SFTP', 'product-import-export-for-woo' ),
					__( 'Advanced filters and customizations for import & export', 'product-import-export-for-woo' ),
					__( 'Add & update data while importing', 'product-import-export-for-woo' ),
					__( 'Maintains action history and debug logs', 'product-import-export-for-woo' ),
					__( 'Compatible with major 3rd-party plugins', 'product-import-export-for-woo' ),
				),
				'url'         => 'https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=User_Import_Export',
				'plugin_file' => 'users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php',
			),
			array(
				'type'        => 'standard',
				'name'        => __( 'Product Feed & Sync Manager for WooCommerce', 'product-import-export-for-woo' ),
				'icon'        => 'product-feed-sync.png',
				'rating'      => '4.9',
				'features'    => array(
					__( 'Generate optimized product feeds for 20+ sales channels', 'product-import-export-for-woo' ),
					__( 'Map WooCommerce product details and categories', 'product-import-export-for-woo' ),
					__( 'Create feeds for all Google shopping platforms', 'product-import-export-for-woo' ),
					__( 'Sync WooCommerce products with Facebook Catalog', 'product-import-export-for-woo' ),
					__( 'Tailor your product feed with filters', 'product-import-export-for-woo' ),
					__( 'Track and manage feed updates', 'product-import-export-for-woo' ),
					__( 'Keep your product feeds up-to-date', 'product-import-export-for-woo' ),
				),
				'url'         => 'https://www.webtoffee.com/product/woocommerce-product-feed/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=WooCommerce_Product_Feed',
				'plugin_file' => 'webtoffee-product-feed/webtoffee-product-feed.php',
			),
			array(
				'type'       => 'standard-with-image',
				'name'       => __( 'Import Export Suite for WooCommerce', 'product-import-export-for-woo' ),
				'icon'       => 'ie-suite-plugin.png',
				'rating'     => 'stars',
				'features'   => array(
					__( 'Import/export Products, Orders, Subscriptions, Coupons, Customers, WordPress Users, Categories & Tags, Reviews', 'product-import-export-for-woo' ),
					__( 'Supports Excel, XML, CSV, and TSV file formats', 'product-import-export-for-woo' ),
					__( 'Schedule automated import & export', 'product-import-export-for-woo' ),
					__( 'Import from URL, Google Sheets, FTP/SFTP', 'product-import-export-for-woo' ),
					__( 'Export to FTP/SFTP', 'product-import-export-for-woo' ),
					__( 'Import & export custom fields and values', 'product-import-export-for-woo' ),
					__( 'Advanced filters and customizations for import & export', 'product-import-export-for-woo' ),
					__( 'Add and update data while importing', 'product-import-export-for-woo' ),
					__( 'Maintains action history and debug logs', 'product-import-export-for-woo' ),
					__( 'Compatible with major 3rd-party plugins', 'product-import-export-for-woo' ),
				),
				'url'        => 'https://www.webtoffee.com/product/woocommerce-import-export-suite/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Import_Export_Suite',
				'image_src'  => 'data-io-illustration.svg',
				'card_class' => 'wt-piew-os-card--ie-suite',
			),
		),
		'standalone' => null,
		'bundle'     => null,
	),
	'accounting-invoicing' => array(
		'label'      => __( 'Accounting & Invoicing', 'product-import-export-for-woo' ),
		'subtitle'   => __( 'Automatically generate professional WooCommerce invoices and documents for all your orders', 'product-import-export-for-woo' ),
		'icon'       => 'sidebar-accounting-invoicing.svg',
		'hero'       => array(
			'name'        => __( 'PDF Invoices, Packing Slips, & Credit Notes', 'product-import-export-for-woo' ),
			'icon'        => 'pdf-invoices-plugin.png',
			'rating'      => 'stars',
			'pdf_cluster' => true,
			'desc'        => __( 'Automatically generate, customize, and manage professional WooCommerce PDF invoices, packing slips, and credit notes with advanced automation and tax compliance features.', 'product-import-export-for-woo' ),
			'features'    => array(),
			'url'         => 'https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=PDF_invoice',
		),
		'plugins'    => array(
			array(
				'type'     => 'standard',
				'name'     => __( 'Shipping Labels, Dispatch Labels, & Delivery Notes', 'product-import-export-for-woo' ),
				'icon'     => 'shipping-labels-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Create delivery notes, shipping & dispatch labels', 'product-import-export-for-woo' ),
					__( 'Enable customers to print the documents from order emails', 'product-import-export-for-woo' ),
					__( 'Customize shipping label size', 'product-import-export-for-woo' ),
					__( 'Add multiple shipping labels on one page', 'product-import-export-for-woo' ),
					__( 'Show product variation data', 'product-import-export-for-woo' ),
					__( 'Add extra product & order data fields', 'product-import-export-for-woo' ),
					__( 'Pre-built layouts & customizable templates', 'product-import-export-for-woo' ),
					__( 'Group products by \'Category\'', 'product-import-export-for-woo' ),
					__( 'Sort products based on Name or SKU', 'product-import-export-for-woo' ),
					__( 'Multilingual support', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-shipping-labels-delivery-notes/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Shipping_Label',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Picklists plugin', 'product-import-export-for-woo' ),
				'icon'     => 'picklists-plugin.png',
				'rating'   => '4.0',
				'features' => array(
					__( 'Bulk print picklists from the admin order page', 'product-import-export-for-woo' ),
					__( 'Automatically email picklists based on order status', 'product-import-export-for-woo' ),
					__( 'Create or customize picklist templates', 'product-import-export-for-woo' ),
					__( 'Show product variation data', 'product-import-export-for-woo' ),
					__( 'Group products in picklist by order/category', 'product-import-export-for-woo' ),
					__( 'Add product meta fields & attributes', 'product-import-export-for-woo' ),
					__( 'Exclude virtual products from picklists', 'product-import-export-for-woo' ),
					__( 'Multilingual support', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-picklist/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Picklist',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'Customizer for WooCommerce PDF Invoices', 'product-import-export-for-woo' ),
				'icon'     => 'pdf-customizer-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Drag-and-drop easy customization', 'product-import-export-for-woo' ),
					__( 'Advanced visual and code editor', 'product-import-export-for-woo' ),
					__( 'Easy invoice layout customization', 'product-import-export-for-woo' ),
					__( 'Customize individual elements using block editors', 'product-import-export-for-woo' ),
					__( 'View live preview of customization', 'product-import-export-for-woo' ),
					__( 'Change color, text, background, border & more', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/customizer-for-woocommerce-pdf-invoice/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=PDF_Customizer',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'WooCommerce Address Labels plugin', 'product-import-export-for-woo' ),
				'icon'     => 'address-labels-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Generate \'Shipping Address\', \'Billing Address\', \'From Address\', and \'Return Address\' labels', 'product-import-export-for-woo' ),
					__( 'Customize label sizes', 'product-import-export-for-woo' ),
					__( 'Bulk print address labels', 'product-import-export-for-woo' ),
					__( 'Offers built-in label templates', 'product-import-export-for-woo' ),
					__( 'Change address label layout', 'product-import-export-for-woo' ),
					__( 'Multilingual support', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-address-label/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Address_Label',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'Proforma Invoice', 'product-import-export-for-woo' ),
				'icon'     => 'proforma-invoice-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Create proforma invoices automatically', 'product-import-export-for-woo' ),
					__( 'Pre-built proforma invoice layouts', 'product-import-export-for-woo' ),
					__( 'Easy invoice layout customization', 'product-import-export-for-woo' ),
					__( 'Attach proforma invoice PDF to order emails', 'product-import-export-for-woo' ),
					__( 'Allow customers to print invoices', 'product-import-export-for-woo' ),
					__( 'Set custom proforma invoice number', 'product-import-export-for-woo' ),
					__( 'Add additional product & order data fields', 'product-import-export-for-woo' ),
					__( 'Attach special notes with proforma invoices', 'product-import-export-for-woo' ),
					__( 'Attach transport & sales terms', 'product-import-export-for-woo' ),
					__( 'Multilingual support', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/woocommerce-proforma-invoice/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Proforma_Invoice',
			),
			array(
				'type'     => 'standard',
				'name'     => __( 'QR Code Add-on for WooCommerce PDF Invoices', 'product-import-export-for-woo' ),
				'icon'     => 'qr-code-plugin.png',
				'rating'   => '5.0',
				'features' => array(
					__( 'Assign QR codes to all generated invoices', 'product-import-export-for-woo' ),
					__( 'Create QR code that reads order or invoice number', 'product-import-export-for-woo' ),
					__( 'Add custom data to invoices', 'product-import-export-for-woo' ),
					__( 'Compatible with WooCommerce PDF Invoice, Packing Slip & Credit Note (Premium)', 'product-import-export-for-woo' ),
					__( 'Compatible with WooCommerce PDF Invoices, Packing Slips, Delivery Notes, and Shipping Labels (Free)', 'product-import-export-for-woo' ),
				),
				'url'      => 'https://www.webtoffee.com/product/qr-code-addon-for-woocommerce-pdf-invoices/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=QR_Code',
			),
			array(
				'type'        => 'standard',
				'name'        => __( 'WooCommerce Request a Quote', 'product-import-export-for-woo' ),
				'icon'        => 'request-quote-plugin.png',
				'rating'      => '5.0',
				'features'    => array(
					__( 'Add quote button to the product & shop pages', 'product-import-export-for-woo' ),
					__( 'Enable quotation request for selected products', 'product-import-export-for-woo' ),
					__( 'Automatically send quotes to users', 'product-import-export-for-woo' ),
					__( 'Disable guest users from asking for quote', 'product-import-export-for-woo' ),
					__( 'Hide prices and \'add to cart\' button', 'product-import-export-for-woo' ),
					__( 'Automatic email alerts for admin & users', 'product-import-export-for-woo' ),
					__( 'Easy button and form customization', 'product-import-export-for-woo' ),
					__( 'Set quote expiry period', 'product-import-export-for-woo' ),
					__( 'Limit spams with reCAPTCHA', 'product-import-export-for-woo' ),
				),
				'url'         => 'https://www.webtoffee.com/product/woocommerce-request-a-quote/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Request_Quote',
				'plugin_file' => 'wt-woo-request-quote/wt-woo-request-quote.php',
			),
			array(
				'type'       => 'standard-with-image',
				'name'       => __( 'Sequential Order Numbers', 'product-import-export-for-woo' ),
				'icon'       => 'sequential-orders-plugin.png',
				'rating'     => 'stars',
				'features'   => array(
					__( 'Auto reset sequence per month/year etc', 'product-import-export-for-woo' ),
					__( 'Add a custom suffix for order numbers', 'product-import-export-for-woo' ),
					__( 'Date suffix in order numbers', 'product-import-export-for-woo' ),
					__( 'Custom sequence for free orders', 'product-import-export-for-woo' ),
					__( 'Increment sequence in custom series', 'product-import-export-for-woo' ),
					__( 'More order number templates', 'product-import-export-for-woo' ),
				),
				'url'        => 'https://www.webtoffee.com/product/woocommerce-sequential-order-numbers/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Sequential_Order_Numbers',
				'image_src'  => 'seq-orders-illustration.png',
				'card_class' => 'wt-piew-os-card--seq-orders',
			),
		),
		'standalone' => null,
		'bundle'     => array(
			'tag_emoji'    => '📄',
			'tag_color'    => 'green',
			'tag'          => __( 'Invoice Bundle', 'product-import-export-for-woo' ),
			'title'        => __( 'All in one Invoice bundle', 'product-import-export-for-woo' ),
			'url'          => 'https://www.webtoffee.com/pdf-invoices-packing-slips-suite-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin_product_import_export&utm_campaign=Invoice_bundle',
			'desc'         => __( 'A complete suite of invoices and shipping documents bundle to create and print PDF invoices, packing slips, shipping and delivery documents in WooCommerce.', 'product-import-export-for-woo' ),
			'pills'        => array(
				__( 'Invoice', 'product-import-export-for-woo' ),
				__( 'Packing Slip', 'product-import-export-for-woo' ),
				__( 'Address Labels', 'product-import-export-for-woo' ),
				__( 'Dispatch Labels', 'product-import-export-for-woo' ),
				__( 'Shipping Labels', 'product-import-export-for-woo' ),
				__( 'Delivery Notes', 'product-import-export-for-woo' ),
				__( 'Picklists', 'product-import-export-for-woo' ),
				__( 'Proforma Invoice', 'product-import-export-for-woo' ),
			),
			'price_orig'   => '$279',
			'price_sale'   => '$179',
			'savings'      => __( 'Save up to 30% off', 'product-import-export-for-woo' ),
			'illustration' => 'invoice-bundle.png',
		),
	),
);

/*
 * This is the Product Feed plugin — Data Import & Export is the most relevant
 * category for our audience, so it leads the sidebar. Any category listed here
 * but missing from the array is silently skipped; any category present in the
 * array but not listed here is appended at the end.
 */
$wt_piew_category_order = array(
	'data-import-export',
	'ecommerce-promotions',
	'privacy-compliance',
	'accounting-invoicing',
);
$wt_piew_categories     = array_replace( array_fill_keys( $wt_piew_category_order, null ), $wt_piew_categories );
$wt_piew_categories     = array_filter(
	$wt_piew_categories,
	static function ( $wt_piew_c ) {
		return null !== $wt_piew_c;
	}
);

/*
 * Hide categories whose entire content is empty — i.e. no hero, no bundle, no
 * visible standalone (either missing or its plugin is active), and every plugin
 * card in the grid has its plugin_file set AND that plugin is active. Both the
 * sidebar link AND the panel body are skipped for such categories.
 */
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$wt_piew_categories = array_filter(
	$wt_piew_categories,
	static function ( $wt_piew_c ) {
		if ( ! empty( $wt_piew_c['hero'] ) ) {
			$wt_piew_hf = isset( $wt_piew_c['hero']['plugin_file'] ) ? $wt_piew_c['hero']['plugin_file'] : '';
			if ( '' === $wt_piew_hf || ! is_plugin_active( $wt_piew_hf ) ) {
				return true;
			}
		}
		if ( ! empty( $wt_piew_c['bundle'] ) ) {
			return true;
		}
		if ( ! empty( $wt_piew_c['standalone'] ) ) {
			$wt_piew_sf = isset( $wt_piew_c['standalone']['plugin_file'] ) ? $wt_piew_c['standalone']['plugin_file'] : '';
			if ( '' === $wt_piew_sf || ! is_plugin_active( $wt_piew_sf ) ) {
				return true;
			}
		}
		if ( ! empty( $wt_piew_c['plugins'] ) ) {
			foreach ( $wt_piew_c['plugins'] as $wt_piew_p ) {
				if ( empty( $wt_piew_p['plugin_file'] ) || ! is_plugin_active( $wt_piew_p['plugin_file'] ) ) {
					return true;
				}
			}
		}
		return false;
	}
);
?>
<?php if ( empty( $wt_piew_categories ) ) : ?>
	<div class="wt-iew-tab-content" data-id="<?php echo esc_attr( $target_id ); ?>">
		<div class="wt-piew-os-page">
			<div class="wt-piew-os-header">
				<h1 class="wt-piew-os-page-title"><?php esc_html_e( 'You\'re all set!', 'product-import-export-for-woo' ); ?></h1>
				<p class="wt-piew-os-page-subtitle"><?php esc_html_e( 'All recommended plugins are already active on your store.', 'product-import-export-for-woo' ); ?></p>
			</div>
		</div>
	</div>
	<?php return; ?>
<?php endif; ?>
<?php
$wt_piew_first_category = array_key_first( $wt_piew_categories );
$wt_piew_first_cat      = $wt_piew_categories[ $wt_piew_first_category ];
?>
<div class="wt-iew-tab-content" data-id="<?php echo esc_attr( $target_id ); ?>">
	<div class="wt-piew-os-page">

		<div class="wt-piew-os-header">
			<h1 class="wt-piew-os-page-title" id="wt-piew-os-cat-title"><?php echo esc_html( $wt_piew_first_cat['label'] ); ?></h1>
			<p class="wt-piew-os-page-subtitle" id="wt-piew-os-cat-subtitle"><?php echo esc_html( $wt_piew_first_cat['subtitle'] ); ?></p>
		</div>

		<div class="wt-piew-os-layout">

			<?php /* ---- Sidebar ---- */ ?>
			<div class="wt-piew-os-sidebar">
				<ul class="wt-piew-os-sidebar-nav">
					<?php foreach ( $wt_piew_categories as $wt_piew_cat_id => $wt_piew_cat ) : ?>
						<li>
							<a href="#"
								class="wt-piew-os-cat-link<?php echo ( $wt_piew_cat_id === $wt_piew_first_category ) ? ' active' : ''; ?>"
								data-category="<?php echo esc_attr( $wt_piew_cat_id ); ?>">
								<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
								<img class="wt-piew-os-cat-icon"
									src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_cat['icon'] ); ?>"
									alt="<?php echo esc_attr( $wt_piew_cat['label'] ); ?>">
								<?php echo esc_html( $wt_piew_cat['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="wt-piew-os-trust-badges">
					<div class="wt-piew-os-trust-badge">
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						<img src="<?php echo esc_url( $wt_piew_img_base . '/thirty-day-guarantee.png' ); ?>"
							alt="<?php esc_attr_e( '30 Day Money Back Guarantee', 'product-import-export-for-woo' ); ?>">
						<span><?php esc_html_e( '30 Day No Risk Money Back Guarantee', 'product-import-export-for-woo' ); ?></span>
					</div>
					<div class="wt-piew-os-trust-badge">
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						<img src="<?php echo esc_url( $wt_piew_img_base . '/satisfaction-badge.png' ); ?>"
							alt="<?php esc_attr_e( '99% Satisfaction Rating', 'product-import-export-for-woo' ); ?>">
						<span><?php esc_html_e( 'Fast Support with 99% Satisfaction Rating', 'product-import-export-for-woo' ); ?></span>
					</div>
				</div>
			</div>

			<?php /* ---- Main content ---- */ ?>
			<div class="wt-piew-os-main">

				<?php foreach ( $wt_piew_categories as $wt_piew_cat_id => $wt_piew_cat ) : ?>
					<div id="wt-piew-os-panel-<?php echo esc_attr( $wt_piew_cat_id ); ?>"
						class="wt-piew-os-category-panel<?php echo ( $wt_piew_cat_id === $wt_piew_first_category ) ? ' active' : ''; ?>"
						data-title="<?php echo esc_attr( $wt_piew_cat['label'] ); ?>"
						data-subtitle="<?php echo esc_attr( $wt_piew_cat['subtitle'] ); ?>">

						<?php /* -- Hero card -- */ ?>
						<?php
						if ( ! empty( $wt_piew_cat['hero'] ) ) :
							$wt_piew_hero              = $wt_piew_cat['hero'];
							$wt_piew_hero_plugin_file  = isset( $wt_piew_hero['plugin_file'] ) ? $wt_piew_hero['plugin_file'] : '';
							$wt_piew_hero_is_active    = $wt_piew_hero_plugin_file && is_plugin_active( $wt_piew_hero_plugin_file );
							$wt_piew_hero_is_installed = $wt_piew_hero_plugin_file && file_exists( WP_PLUGIN_DIR . '/' . $wt_piew_hero_plugin_file );

							if ( ! $wt_piew_hero_is_active ) :
								?>
							<div class="wt-piew-os-hero-card">
								<div class="wt-piew-os-hero-left">
									<div class="wt-piew-os-hero-title-row">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wt-piew-os-hero-icon"
											src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_hero['icon'] ); ?>"
											alt="<?php echo esc_attr( $wt_piew_hero['name'] ); ?>">
										<div class="wt-piew-os-hero-title-block">
											<h3 class="wt-piew-os-hero-name"><?php echo esc_html( $wt_piew_hero['name'] ); ?></h3>
											<div class="wt-piew-os-hero-stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'product-import-export-for-woo' ); ?>">
												<?php for ( $i = 0; $i < 5; $i++ ) : ?>
													<span class="wt-piew-os-star">&#9733;</span>
												<?php endfor; ?>
											</div>
										</div>
									</div>
									<div class="wt-piew-os-hero-divider"></div>
									<p class="wt-piew-os-hero-desc"><?php echo esc_html( $wt_piew_hero['desc'] ); ?></p>
									<?php if ( $wt_piew_hero_is_installed && current_user_can( 'activate_plugins' ) ) : ?>
										<?php
										$wt_piew_hero_activate_url = wp_nonce_url(
											self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $wt_piew_hero_plugin_file ) ),
											'activate-plugin_' . $wt_piew_hero_plugin_file
										);
										?>
										<a href="<?php echo esc_url( $wt_piew_hero_activate_url ); ?>"
											class="wt-piew-os-btn-premium wt-piew-os-btn-premium--block">
											<?php esc_html_e( 'Activate', 'product-import-export-for-woo' ); ?>
										</a>
									<?php else : ?>
										<a href="<?php echo esc_url( $wt_piew_hero['url'] ); ?>"
											target="_blank"
											rel="noopener noreferrer"
											class="wt-piew-os-btn-premium wt-piew-os-btn-premium--block">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Get premium', 'product-import-export-for-woo' ); ?>
										</a>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $wt_piew_hero['pdf_cluster'] ) ) : ?>
									<div class="wt-piew-os-hero-right wt-piew-os-hero-right--pdf-cluster">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wt-piew-os-pdf wt-piew-os-pdf--left"
											src="<?php echo esc_url( $wt_piew_img_base . '/pdf-invoice-left.svg' ); ?>"
											alt="">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wt-piew-os-pdf wt-piew-os-pdf--center"
											src="<?php echo esc_url( $wt_piew_img_base . '/pdf-invoice-center.svg' ); ?>"
											alt="">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wt-piew-os-pdf wt-piew-os-pdf--right"
											src="<?php echo esc_url( $wt_piew_img_base . '/pdf-invoice-right.svg' ); ?>"
											alt="">
									</div>
								<?php elseif ( ! empty( $wt_piew_hero['image'] ) ) : ?>
									<div class="wt-piew-os-hero-right">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_hero['image'] ); ?>"
											alt="<?php echo esc_attr( $wt_piew_hero['name'] ); ?>">
									</div>
								<?php endif; ?>
							</div>
								<?php
							endif;
						endif;
						?>

						<?php /* -- Plugin card grid -- */ ?>
						<?php if ( ! empty( $wt_piew_cat['plugins'] ) ) : ?>
							<?php
							// Filter out plugins that are already active — the card is only useful when the plugin is missing or inactive.
							// is_plugin_active() is guaranteed available here — required at the top of the file.
							$wt_piew_visible_plugins = array_values(
								array_filter(
									$wt_piew_cat['plugins'],
									static function ( $wt_piew_p ) {
										if ( empty( $wt_piew_p['plugin_file'] ) ) {
											return true;
										}
										return ! is_plugin_active( $wt_piew_p['plugin_file'] );
									}
								)
							);
							$wt_piew_chunks          = array_chunk( $wt_piew_visible_plugins, 3 );
							foreach ( $wt_piew_chunks as $wt_piew_row ) :
								?>
								<div class="wt-piew-os-card-grid">
									<?php foreach ( $wt_piew_row as $wt_piew_plugin ) : ?>

										<?php if ( 'image' === $wt_piew_plugin['type'] ) : ?>

											<div class="wt-piew-os-card-image">
												<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
												<img src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_plugin['src'] ); ?>"
													alt="">
											</div>

											<?php
										else :
											$wt_piew_with_image = ( 'standard-with-image' === $wt_piew_plugin['type'] && ! empty( $wt_piew_plugin['image_src'] ) );
											$wt_piew_card_class = 'wt-piew-os-card';
											if ( $wt_piew_with_image ) {
												$wt_piew_card_class .= ' wt-piew-os-card--with-image';
											}
											if ( ! empty( $wt_piew_plugin['card_class'] ) ) {
												$wt_piew_card_class .= ' ' . sanitize_html_class( $wt_piew_plugin['card_class'] );
											}
											?>

											<div class="<?php echo esc_attr( $wt_piew_card_class ); ?>">
												<div class="wt-piew-os-card-body">
													<?php if ( $wt_piew_with_image ) : ?>
														<div class="wt-piew-os-card-header wt-piew-os-card-header--stacked">
															<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
															<img class="wt-piew-os-card-icon"
																src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_plugin['icon'] ); ?>"
																alt="<?php echo esc_attr( $wt_piew_plugin['name'] ); ?>">
															<div class="wt-piew-os-card-title-block">
																<span class="wt-piew-os-card-name"><?php echo esc_html( $wt_piew_plugin['name'] ); ?></span>
																<?php if ( 'stars' === $wt_piew_plugin['rating'] ) : ?>
																	<span class="wt-piew-os-card-rating wt-piew-os-card-rating--stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'product-import-export-for-woo' ); ?>">
																		<?php for ( $i = 0; $i < 5; $i++ ) : ?>
																			<span class="wt-piew-os-star">&#9733;</span>
																		<?php endfor; ?>
																	</span>
																<?php else : ?>
																	<span class="wt-piew-os-card-rating">
																		<?php echo esc_html( $wt_piew_plugin['rating'] ); ?>
																		<span class="wt-piew-os-star">&#9733;</span>
																	</span>
																<?php endif; ?>
															</div>
														</div>
													<?php else : ?>
														<div class="wt-piew-os-card-header">
															<div class="wt-piew-os-card-icon-name">
																<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
																<img class="wt-piew-os-card-icon"
																	src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_plugin['icon'] ); ?>"
																	alt="<?php echo esc_attr( $wt_piew_plugin['name'] ); ?>">
																<span class="wt-piew-os-card-name"><?php echo esc_html( $wt_piew_plugin['name'] ); ?></span>
															</div>
															<?php if ( 'stars' === $wt_piew_plugin['rating'] ) : ?>
																<span class="wt-piew-os-card-rating wt-piew-os-card-rating--stars">
																	<span class="wt-piew-os-star">&#9733;</span>
																	<span class="wt-piew-os-star">&#9733;</span>
																	<span class="wt-piew-os-star">&#9733;</span>
																	<span class="wt-piew-os-star">&#9733;</span>
																	<span class="wt-piew-os-star">&#9733;</span>
																</span>
															<?php else : ?>
																<span class="wt-piew-os-card-rating">
																	<?php echo esc_html( $wt_piew_plugin['rating'] ); ?>
																	<span class="wt-piew-os-star">&#9733;</span>
																</span>
															<?php endif; ?>
														</div>
													<?php endif; ?>
													<ul class="wt-piew-os-card-features<?php echo ( count( $wt_piew_plugin['features'] ) > 3 ) ? ' wt-piew-os-card-features--collapsible' : ''; ?>">
														<?php foreach ( $wt_piew_plugin['features'] as $wt_piew_feature ) : ?>
															<li>
																<span class="dashicons dashicons-yes-alt"></span>
																<?php echo esc_html( $wt_piew_feature ); ?>
															</li>
														<?php endforeach; ?>
													</ul>
													<?php if ( count( $wt_piew_plugin['features'] ) > 3 ) : ?>
														<div class="wt-piew-os-show-more-less">
															<a href="#" class="wt-piew-os-show-more"><?php esc_html_e( 'Show More', 'product-import-export-for-woo' ); ?></a>
															<a href="#" class="wt-piew-os-show-less"><?php esc_html_e( 'Show Less', 'product-import-export-for-woo' ); ?></a>
														</div>
													<?php endif; ?>
													<?php
													$wt_piew_plugin_file      = ! empty( $wt_piew_plugin['plugin_file'] ) ? $wt_piew_plugin['plugin_file'] : '';
													$wt_piew_plugin_installed = $wt_piew_plugin_file && file_exists( WP_PLUGIN_DIR . '/' . $wt_piew_plugin_file );
													if ( $wt_piew_plugin_installed && current_user_can( 'activate_plugins' ) ) :
														$wt_piew_activate_url = wp_nonce_url(
															self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $wt_piew_plugin_file ) ),
															'activate-plugin_' . $wt_piew_plugin_file
														);
														?>
														<a href="<?php echo esc_url( $wt_piew_activate_url ); ?>"
															class="wt-piew-os-btn-premium">
															<?php esc_html_e( 'Activate', 'product-import-export-for-woo' ); ?>
														</a>
													<?php else : ?>
														<a href="<?php echo esc_url( $wt_piew_plugin['url'] ); ?>"
															target="_blank"
															rel="noopener noreferrer"
															class="wt-piew-os-btn-premium">
															<span class="dashicons dashicons-star-filled"></span>
															<?php esc_html_e( 'Get premium', 'product-import-export-for-woo' ); ?>
														</a>
													<?php endif; ?>
												</div>
												<?php if ( $wt_piew_with_image ) : ?>
													<div class="wt-piew-os-card-image-side">
														<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
														<img src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_plugin['image_src'] ); ?>"
															alt="">
													</div>
												<?php endif; ?>
											</div>

										<?php endif; ?>

									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php /* -- Bundle section (renders BEFORE the standalone, per Figma order) -- */ ?>
						<?php
						if ( ! empty( $wt_piew_cat['bundle'] ) ) :
							$wt_piew_bundle    = $wt_piew_cat['bundle'];
							$wt_piew_tag_color = ! empty( $wt_piew_bundle['tag_color'] ) ? $wt_piew_bundle['tag_color'] : 'green';
							?>
							<div class="wt-piew-os-bundle">
								<div class="wt-piew-os-bundle-content">
									<span class="wt-piew-os-bundle-tag wt-piew-os-bundle-tag--<?php echo esc_attr( $wt_piew_tag_color ); ?>">
										<?php if ( ! empty( $wt_piew_bundle['tag_emoji'] ) ) : ?>
											<span class="wt-piew-os-bundle-tag-emoji"><?php echo esc_html( $wt_piew_bundle['tag_emoji'] ); ?></span>
										<?php endif; ?>
										<?php echo esc_html( $wt_piew_bundle['tag'] ); ?>
									</span>
									<div class="wt-piew-os-bundle-title">
										<a href="<?php echo esc_url( $wt_piew_bundle['url'] ); ?>"
											target="_blank"
											rel="noopener noreferrer">
											<?php echo esc_html( $wt_piew_bundle['title'] ); ?>
										</a>
										<span class="dashicons dashicons-external"></span>
									</div>
									<p class="wt-piew-os-bundle-desc"><?php echo esc_html( $wt_piew_bundle['desc'] ); ?></p>
									<div class="wt-piew-os-bundle-pills">
										<?php foreach ( $wt_piew_bundle['pills'] as $wt_piew_pill ) : ?>
											<span class="wt-piew-os-bundle-pill">
												<span class="dashicons dashicons-yes-alt"></span>
												<?php echo esc_html( $wt_piew_pill ); ?>
											</span>
										<?php endforeach; ?>
									</div>
									<p class="wt-piew-os-bundle-pricing">
										<?php
										printf(
											wp_kses(
												/* translators: 1: strikethrough original price, 2: bold sale price, 3: green savings text */
												__( 'Total: <s>%1$s</s> <strong>%2$s</strong> <span class="wt-piew-os-savings">(%3$s)</span>', 'product-import-export-for-woo' ),
												array(
													's'    => array(),
													'strong' => array(),
													'span' => array( 'class' => array() ),
												)
											),
											esc_html( $wt_piew_bundle['price_orig'] ),
											esc_html( $wt_piew_bundle['price_sale'] ),
											esc_html( $wt_piew_bundle['savings'] )
										);
										?>
									</p>
									<a href="<?php echo esc_url( $wt_piew_bundle['url'] ); ?>"
										target="_blank"
										rel="noopener noreferrer"
										class="wt-piew-os-btn-bundle">
										<?php esc_html_e( 'View Bundle', 'product-import-export-for-woo' ); ?>
										<span class="dashicons dashicons-external"></span>
									</a>
								</div>
								<?php if ( ! empty( $wt_piew_bundle['illustration'] ) ) : ?>
									<div class="wt-piew-os-bundle-illustration">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_bundle['illustration'] ); ?>"
											alt="<?php echo esc_attr( $wt_piew_bundle['title'] ); ?>">
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php /* -- Standalone card (e.g. EMA App) — renders AFTER the bundle, per Figma order -- */ ?>
						<?php
						if ( ! empty( $wt_piew_cat['standalone'] ) ) :
							$wt_piew_solo = $wt_piew_cat['standalone'];

							/*
							 * Tri-state install/active check:
							 *   active         → hide banner
							 *   installed only → show "Activate" button (nonce-protected activate URL)
							 *   not installed  → show default "Try Now" button
							 *
							 * is_plugin_active() is guaranteed available here — required at the top of the file.
							 */
							$wt_piew_solo_plugin_file  = isset( $wt_piew_solo['plugin_file'] ) ? $wt_piew_solo['plugin_file'] : '';
							$wt_piew_solo_is_active    = $wt_piew_solo_plugin_file && is_plugin_active( $wt_piew_solo_plugin_file );
							$wt_piew_solo_is_installed = $wt_piew_solo_plugin_file && file_exists( WP_PLUGIN_DIR . '/' . $wt_piew_solo_plugin_file );

							if ( ! $wt_piew_solo_is_active ) :
								?>
							<div class="wt-piew-os-standalone">
								<div class="wt-piew-os-standalone-content">
									<div class="wt-piew-os-standalone-header">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img class="wt-piew-os-standalone-icon"
											src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_solo['icon'] ); ?>"
											alt="<?php echo esc_attr( $wt_piew_solo['name'] ); ?>">
										<h3 class="wt-piew-os-standalone-name"><?php echo esc_html( $wt_piew_solo['name'] ); ?></h3>
									</div>
									<p class="wt-piew-os-standalone-desc"><?php echo esc_html( $wt_piew_solo['desc'] ); ?></p>
									<?php if ( $wt_piew_solo_is_installed && current_user_can( 'activate_plugins' ) ) : ?>
										<?php
										$wt_piew_solo_activate_url = wp_nonce_url(
											self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $wt_piew_solo_plugin_file ) ),
											'activate-plugin_' . $wt_piew_solo_plugin_file
										);
										?>
										<a href="<?php echo esc_url( $wt_piew_solo_activate_url ); ?>"
											class="wt-piew-os-btn-premium wt-piew-os-btn-premium--block">
											<?php esc_html_e( 'Activate', 'product-import-export-for-woo' ); ?>
										</a>
									<?php else : ?>
										<a href="<?php echo esc_url( $wt_piew_solo['url'] ); ?>"
											target="_blank"
											rel="noopener noreferrer"
											class="wt-piew-os-btn-premium wt-piew-os-btn-premium--block">
											<?php esc_html_e( 'Try Now', 'product-import-export-for-woo' ); ?>
										</a>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $wt_piew_solo['screenshot'] ) ) : ?>
									<div class="wt-piew-os-standalone-screenshot">
										<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										<img src="<?php echo esc_url( $wt_piew_img_base . '/' . $wt_piew_solo['screenshot'] ); ?>"
											alt="<?php echo esc_attr( $wt_piew_solo['name'] ); ?>">
									</div>
								<?php endif; ?>
							</div>
								<?php
							endif;
						endif;
						?>

					</div>
				<?php endforeach; ?>

			</div>
		</div>
	</div>
</div>
