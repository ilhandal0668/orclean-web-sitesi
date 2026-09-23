<?php
namespace ETC\App\Controllers\Admin;

use ETC\App\Controllers\Admin\Base_Controller;
use ETC\App\Controllers\Customizer;

/**
 * Import controller.
 *
 * @since      1.4.5
 * @package    ETC
 * @subpackage ETC/Controller
 */
class Import extends Base_Controller {

	// ! Declare default variables
	private $import_url = '';
	private $widgets_counter = 0;
	public  $engine = 'wpb';
	public  $version = '';
	private $active_widgets = '';
	public  $versions = array();
	private $last_import_error = array();

	// ! Main construct/ setup variables
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Add import init actions.
	 *
	 * Require files/add ajax actions callback.
	 *
	 * @since   1.1.0
	 * @version 1.1.2
	 */
	public function init() {
		if( ! defined( 'ETHEME_THEME_SLUG' ) ) return;
		$this->import_url  = apply_filters('etheme_protocol_url', 'https://www.8theme.com/import/xstore-demos/');

		if (defined('ETHEME_BASE_URL')) {
			$this->import_url  = apply_filters('etheme_protocol_url', ETHEME_BASE_URL . 'import/xstore-demos/');
		}
		$this->versions    = ( function_exists( 'etheme_get_demo_versions' ) ) ? etheme_get_demo_versions() : array();
		add_action('wp_ajax_etheme_import_ajax', array($this, 'import_data'));

		set_theme_mod( 'testimonials_type', 1 );
	}

	private function reset_import_error() {
		$this->last_import_error = array();
	}

	private function set_import_error( $message, $details = '', $code = 'import_error' ) {
		if ( ! empty( $this->last_import_error ) ) {
			return;
		}

		$this->last_import_error = array(
			'code'    => $code,
			'message' => $message,
			'details' => $details,
		);
	}

	private function get_import_response( $output = '' ) {
		$response = array(
			'status'  => 'installed',
			'message' => '',
			'details' => '',
		);

		if ( ! empty( $this->last_import_error ) ) {
			$response['status'] = 'error';
			$response['message'] = $this->last_import_error['message'];
			$response['details'] = $this->last_import_error['details'];
		} elseif ( ! empty( $output ) ) {
			$response['details'] = $output;
		}

		return $response;
	}

	/**
	 * Import data router.
	 *
	 * Manage what data must be imported.
	 *
	 * @since   1.1.0
	 * @version 1.1.4
	 */
	public function import_data(){
		check_ajax_referer('etheme_import-demo', 'security');

		if (!current_user_can( 'manage_options' )){
			wp_send_error('Unauthorized access!');
		}

		$versions_imported = get_option('versions_imported');
		if( empty( $versions_imported ) ) $versions_imported = array();

		if( !empty($_POST['version']) ) {
			$this->version = $_POST['version'];
		}

		if ( isset($_POST['engine']) && ! empty($_POST['engine']) ) {
			$this->engine = $_POST['engine'];
		}

		$this->reset_import_error();
		ob_start();

		if( empty( $this->versions[ $this->version ] ) ){
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Invalid demo version requested.',
					'details' => 'The selected demo version was not found in the available versions list.',
				)
			);
		}

		$to_import = etheme_get_demo_import_data( $this->version, $this->engine );

		$to_import = $to_import['to_import'];

		// $to_import = $this->versions[ $this->version ]['to_import'];

		do_action('et_before_data_import');


		if (
			isset( $to_import['single_product_builder'] )
			&& $to_import['single_product_builder']
			&& (
				$this->engine == 'wpb'
				|| ! isset($to_import['elementor_headers'])
			)
		) {
			update_option( 'etheme_single_product_builder', true );
		}
		// deactivate Customizer Header builder in case demo uses Elementor built header
		if (
			isset( $to_import['elementor_headers'] )
			&& $to_import['elementor_headers']
			&& in_array( $this->engine, array('elementor', 'elementor_builders'))
		) {
			update_option( 'etheme_disable_customizer_header_builder', true );
			// deactivate Elementor WooCommerce mini cart template
			update_option( 'elementor_use_mini_cart_template', 'no' );
		}

		// Global settings
		$global_settings = $this->get_global_data('global_settings');

		if(
			is_array($global_settings)
			&& isset($global_settings['disabled'])
			&& in_array($_POST['type'], $global_settings['disabled']) 
		){

			wp_send_json(array(
				'status'  => 'installed',
				'message' => '',
				'details' => '',
			));

			// wp_send_json(
			// 	array(
			// 		'status'  => 'error',
			// 		'message' => 'This import step is disabled for the selected demo.',
			// 		'details' => sprintf( 'The import type "%s" is disabled by global settings.', sanitize_text_field( wp_unslash( $_POST['type'] ) ) ),
			// 	)
			// );
		}

		try {
			switch ($_POST['type']) {
			case 'xml':

				if ( $_POST['install']['value'] != 'et_all' ) {
					$this->import_xml_file($_POST['install']['value']);
				}

				if ( $_POST['install']['value'] == 'products' ) {
					if ( isset($to_import['brands']) && $to_import['brands'] == true ){
						$this->update_terms('brand');
					}
					if ( isset($to_import['product_cats']) && $to_import['product_cats'] == true ){
						$this->update_terms('product_cat');
					}

					$this->update_on_sale_products();
				}

				if (
					$_POST['install']['value'] == 'pages'
					&& isset($to_import['content-presets'])
					&& $to_import['content-presets']
					&& $this->engine == 'wpb'
				){
					$remote_data = $this->get_remote_data('content-presets');
					if ( $remote_data && is_array($remote_data) ) {
						update_option('mpc_presets_mpc_navigation', json_encode( $remote_data ));
					}
				}

				if ( $_POST['install']['value'] == 'pages' ) {
					$this->default_woocommerce_pages();
				}

				break;
			case 'options':

				if ( in_array($this->version, array('niche-market', 'eco-scooter') ) ) {
					update_option('etheme_single_product_builder', true);
				}

				if( ! empty( $to_import['options'] ) ) {
					$this->update_options();
					$this->clear_frontend_cache();
				}
				break;

			case 'slider':
				if( ! empty( $to_import['slider'] ) ) {
					for( $i = 0; $i < $to_import['slider']; $i++ ) {
						$this->import_slider( $i );
					}
				}
				break;

			case 'home_page':
				if( ! empty( $to_import['home_page'] ) ) {
					$this->update_home_page();
				}
				break;

			case 'widgets':
				if( ! empty( $to_import['widgets'] ) ) {
					$this->update_widgets();
				}
				break;

			case 'menu':
				$xml_result = $this->import_xml_file($_POST['install']['value']);
				$this->update_menus();
				break;

			case 'fonts':
				$this->import_custom_fonts();
				break;

			case 'variation_taxonomy':
				$this->import_variation_taxonomy('variation_taxonomy');
				break;

			case 'variations_trems':
				$this->import_variations_trems('variations_trems');
				break;

			case 'variation_products':
				$this->import_variation_products(3);
				break;

			case 'et_multiple_headers':
				$this->import_multiple_conditions('et_multiple_headers', 'et_multiple_headers');
				$this->update_menus(true);
				break;

			case 'et_multiple_conditions':
				$this->import_multiple_conditions('et_multiple_conditions', 'et_multiple_conditions');
				break;

			case 'et_multiple_single_product':
				$this->import_multiple_conditions('et_multiple_single_product', 'et_multiple_single_product');
				break;

			case 'et_multiple_single_product_conditions':
				$this->import_multiple_conditions('et_multiple_single_product_conditions', 'et_multiple_single_product_conditions');
				break;

			case 'elementor_globals':
				$this->import_elementor_globals();
				break;

			case 'elementor_sections':
				$this->import_elementor_sections('elementor_sections');
				break;

			case 'elementor_single_products':
				$this->import_elementor_sections('elementor_single_products');
				break;

			case 'elementor_archives':
				$this->import_elementor_sections('elementor_archives');
				break;

			case 'elementor_footers':
				$this->import_elementor_sections('elementor_footers');
				break;

			case 'elementor_headers':
				$this->import_elementor_sections('elementor_headers');
				break;

			case 'elementor_post_archive':
				$this->import_elementor_sections('elementor_post_archive');
				break;

			case 'elementor_post':
				$this->import_elementor_sections('elementor_post');
				break;
			case 'gutenberg_patterns':
				$this->import_gutenberg_patterns();
				break;
			case 'gutenberg_template_parts':
				$this->import_gutenberg_template_parts();
				break;
			case 'gutenberg_styles':
				$this->import_gutenberg_styles();
				break;
			case 'gutenberg_navigation':
				$this->import_gutenberg_navigation();
				break;
			case 'gutenberg_templates':
				$this->import_gutenberg_templates();
				break;
			case 'gutenberg_site_logo':
				$this->import_gutenberg_site_logo();
				break;

			case 'imported':
				$versions_imported[] = $this->version;
				update_option('versions_imported', $versions_imported);
				break;
			case 'default_woocommerce_pages':
				$this->default_woocommerce_pages();
				break;
			case 'version_info':
				if ( $this->engine === 'gutenberg' ) {
					$this->import_gutenberg_patterns();
					$this->import_gutenberg_template_parts();
					$this->import_gutenberg_styles();
					$this->import_gutenberg_navigation();
					$this->import_gutenberg_templates();
					$this->import_gutenberg_site_logo();
					$this->sync_gutenberg_html_templates();
					$this->reinit_fse_theme_state();
					if ( ! empty( $to_import['options'] ) ) {
						$this->update_options();
						$this->clear_frontend_cache();
					}
				}

				if (in_array( $this->engine, array('elementor', 'elementor_builders'))){
					$this->disable_elementor_atomic_editor();
				}

				$this->get_version_info();
				break;
			case 'init_builders':
				$this->init_builders();
				break;
			case 'sales_boosters':
				$this->sales_boosters();
				break;
			default:
				break;
			}
		} catch ( \Throwable $e ) {
			$this->set_import_error(
				'Unexpected error during import.',
				$e->getMessage(),
				'import_exception'
			);
		}

		do_action('et_after_data_import');

		$output = trim( ob_get_clean() );
		wp_send_json( $this->get_import_response( $output ) );
	}

	/**
	 * Import slider.
	 *
	 * Import revolution slider.
	 *
	 * @since   1.1.1
	 * @version 1.1.2
	 *
	 * @param integer $i sliders count
	 * @return bool result of import
	 */
	public function import_slider( $i = 0 ) {

		$zip_file = ( $i > 0 ) ? 'slider' . $i: 'slider' ;

		$slider_url = $this->generate_remote_url($zip_file);
		try {
			$zip_file = download_url( $slider_url );
		} catch( Exception $e ) {
			return false;
		}

		if(!class_exists('RevSlider')) return false;

		$revapi = new \RevSlider();

		ob_start();

		$slider_result = $revapi->importSliderFromPost(true, true, $zip_file);

		ob_end_clean();

		return $slider_result;
	}

	/**
	 * Import xml files.
	 *
	 * Use WordPress importer to do it.
	 *
	 * @since   1.1.0
	 * @version 1.1.1
	 *
	 * @param string $file file name with extension
	 * @return bool|object true on success|Wp error object
	 */
	public function import_xml_file($file) {

		ini_set( 'max_execution_time', 900 );

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS' , true );
		}

		include ET_CORE_DIR . 'packages/wordpress-importer/wordpress-importer.php';

		$result = false;

		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		$importerError = false;

		//check if wp_importer, the base importer class is available, otherwise include it
		if ( !class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require_once($class_wp_importer);
			else
				$importerError = true;
		}

		if($importerError !== false) {
			$this->set_import_error(
				'The auto import script could not be loaded.',
				'Please use the WordPress importer manually or verify that the importer classes are available on the server.',
				'importer_not_loaded'
			);
			return false;
		}

		if(class_exists('WP_Importer')) {

			// Ensure testimonials post type exists during import.
			if ( $file === 'testimonials' && ! post_type_exists( 'testimonials' ) ) {
				set_theme_mod( 'testimonials_type', 1 );

				if ( class_exists( '\ETC\App\Controllers\Post_Types' ) ) {
					$controller = \ETC\App\Controllers\Post_Types::get_instance(
						'\ETC\App\Models\Post_Types',
						'\ETC\Views\Post_Types',
						'post-type'
					);
					if ( method_exists( $controller, 'create_custom_post_types' ) ) {
						$controller->create_custom_post_types();
					}
					if ( method_exists( $controller, 'create_taxonomies' ) ) {
						$controller->create_taxonomies();
					}
				}
			}

			$map_testimonial_post_type = null;
			if ( $file === 'testimonials' ) {
				$map_testimonial_post_type = function ( $post ) {
					if ( isset( $post['post_type'] ) && $post['post_type'] === 'testimonial' ) {
						$post['post_type'] = 'testimonials';
					}
					return $post;
				};
				add_filter( 'wp_import_post_data_raw', $map_testimonial_post_type, 10, 1 );
			}

			// Enable svg support
			add_filter( 'upload_mimes', [ $this, 'add_svg_support' ] );

			$uploads = wp_get_upload_dir();

			$version_data = $this->get_remote_data($file, false);

			$tmpxml = '';

			if( $version_data ) {
				$tmpxml = $uploads['basedir']. '/xstore-tmp-data.xml';
				if ( false === file_put_contents( $tmpxml, $version_data ) ) {
					$this->set_import_error(
						'Failed to write the temporary import file.',
						'WordPress could not write xstore-tmp-data.xml into the uploads directory. Check filesystem write permissions.',
						'filesystem_write_error'
					);
					return false;
				}
			} else {
				$this->set_import_error(
					'Failed to download import data from the demo server.',
					'The XML import file could not be fetched from the remote demo source.',
					'remote_data_unavailable'
				);
				return false;
			}

			try {

				ob_start();

				if ( $this->engine !== 'gutenberg' ) {
					add_filter( 'intermediate_image_sizes', function($sizes){return array();} );
				}

				$file_url = $tmpxml;

				$importer = new \WP_Import();

				$importer->fetch_attachments = true;

				$importer->import($file_url);

				$result = ob_get_clean();

			} catch (Exception $e) {
				$result = false;
				$this->set_import_error(
					'Error while importing XML content.',
					$e->getMessage(),
					'xml_import_failed'
				);
			}

			// Enable svg support
			remove_filter( 'upload_mimes', [ $this, 'add_svg_support' ] );
			if ( $map_testimonial_post_type ) {
				remove_filter( 'wp_import_post_data_raw', $map_testimonial_post_type, 10 );
			}

			if ( $this->engine === 'gutenberg' ) {
				$this->regenerate_missing_attachment_sizes();
			}
		}
		return $result;
	}

	/**
	 * Update menus.
	 *
	 * Force update menus in theme options.
	 *
	 * @since   1.1.0
	 * @version 1.1.1
	 * @param $multiple boolean : is multiple menus or not
	 */
	public function update_menus($multiple = false){
		$menus = array();
		$menus['main_menu_term'] = wp_get_nav_menu_object( 'Main menu' );
		$menus['main_menu_2_term'] = wp_get_nav_menu_object( 'Secondary menu' );
		$menus['secondary_menu_term'] = wp_get_nav_menu_object( 'All departments' );

		if ( wp_get_nav_menu_object( 'Mobile menu' ) ) {
			$menus['mobile_menu_term'] = wp_get_nav_menu_object( 'Mobile menu' );
		} else {
			$menus['mobile_menu_term'] = $menus['main_menu_term'];
		}

		if ( wp_get_nav_menu_object( 'Vertical menu' ) ){
			$menus['header_vertical_menu_term'] = wp_get_nav_menu_object( 'Vertical menu' );
		} else {
			$menus['header_vertical_menu_term'] = $menus['main_menu_term'];
		}

		if ($multiple){
			$multiple_headers = get_option('et_multiple_headers');
			if ($multiple_headers){
				$multiple_headers = json_decode($multiple_headers, true);
				if (is_array($multiple_headers) && count($multiple_headers)){
					foreach ( $multiple_headers as $key => $value ) {
						foreach ($menus as $k => $v) {
							if ( isset($multiple_headers[$key]['options'][$k]) && isset( $v->term_id ) ) {
								$multiple_headers[$key]['options'][$k] = strval($v->term_id);
							}
						}
					}
					update_option( 'et_multiple_headers', json_encode($multiple_headers) );
				}
			}
		} else {
			$imported = array();
			foreach ($menus as $key => $value) {
				if ( isset( $value->term_id ) ) {
					set_theme_mod( $key, strval($value->term_id) );
					$imported[$key] = strval($value->term_id);
					if (function_exists('wc_get_page_id')){
						$this->change_menu_item_url($value->term_id, 'shop', get_permalink( wc_get_page_id( 'shop' ) ));
						$this->change_menu_item_url($value->term_id, 'shop', get_permalink( wc_get_page_id( 'Shop' ) ));
					}
				}
			}
			$this->log_imported_data('menu', $imported);
		}
	}


	public function change_menu_item_url($menu_id, $menu_title, $new_url){
		$menu_items = wp_get_nav_menu_items($menu_id);
		foreach ($menu_items as $menu_item) {
			if ($menu_item->title == $menu_title) {
				$menu_item->url = $new_url;
				wp_update_nav_menu_item($menu_id, $menu_item->ID, (array) $menu_item);
				break;
			}
		}
	}

	public function log_imported_data($type, $data){
		$et_imported_data = get_option('et_imported_data', array());
		if (is_array($data)){
			$et_imported_data[$type] = $data;
		} else {
			$et_imported_data[$type][] = $data;
		}
		update_option('et_imported_data', $et_imported_data);
	}

	/**
	 * Update widgets.
	 *
	 * Create custom widget areas/ Create widgets.
	 *
	 * @since   1.1.0
	 * @version 1.1.1
	 */
	private function update_widgets() {
		$widgets = $this->get_remote_data('widgets');

		// We don't want to undo user changes, so we look for changes first.
		$this->active_widgets = get_option( 'sidebars_widgets' );

		$this->widgets_counter = 1;

		if( ! empty( $widgets['custom-sidebars'] ) ) {
			foreach ($widgets['custom-sidebars'] as $customsidebar) {
				etheme_add_sidebar( $customsidebar );
				$this->log_imported_data('widget_areas', $customsidebar);
			}
		}

		foreach ($widgets['sidebar-widgets'] as $area => $params) {
			if ( ! empty ( $this->active_widgets[$area] ) && $params['flush'] ) {
				$this->flush_widget_area($area);
			} else if(! empty ( $this->active_widgets[$area] ) && ! $params['flush'] ) {
				continue;
			}
			foreach ($params['widgets'] as $widget => $args) {
				$this->add_widget($area, $args['widget'], $args['args']);
			}
		}
		// Now save the $active_widgets array.
		update_option( 'sidebars_widgets', $this->active_widgets );
	}

	/**
	 * Add widget.
	 *
	 * Create widgets.
	 *
	 * @since   1.1.0
	 * @version 1.2.1
	 *
	 * @param integer $sidebar widget area id
	 * @param integer $widget  widget id
	 * @param array   $options widget options
	 */
		private function add_widget( $sidebar, $widget, $options = array() ) {
			$this->active_widgets[ $sidebar ][] = $widget . '-' . $this->widgets_counter;
			$widget_content = get_option( 'widget_' . $widget );

			if ( ! is_array( $widget_content ) ) {
				$widget_content = array();
			}

			if(! is_array($options)){
				$options = array();
			}
		
		$widget_content[ $this->widgets_counter ] = $options;
		update_option(  'widget_' . $widget, $widget_content );
		$this->log_imported_data('widgets-ids', $widget . '-' . $this->widgets_counter);
		$this->log_imported_data('widgets', 'widget_' . $widget);
		$this->widgets_counter++;
	}

	/**
	 * Flush widget area.
	 *
	 * Flush widget area in area.
	 *
	 * @since   1.1.0
	 * @version 1.1.0
	 *
	 * @param integer $area widget area id
	 */
	private function flush_widget_area( $area ) {
		unset($this->active_widgets[ $area ]);
	}

	/**
	 * Update home page.
	 *
	 * Update show_on_front/page_on_front/page_for_posts.
	 *
	 * @since   1.1.0
	 * @version 1.1.0
	 */
	public function update_home_page() {
		$blog_id = $this->get_page_by_title('Blog');
		$home_page = $this->get_page_by_title('Home ' . str_replace('home-', '', $this->version));

		if($home_page){
			$pageid = $home_page->ID;
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $pageid );
		}
		if($blog_id){
			update_option( 'page_for_posts', $blog_id->ID );
		}
	}

	/**
	 * Update options.
	 *
	 * Update theme options by using set_theme_mod.
	 *
	 * @since   1.1.0
	 * @version 1.1.1
	 */
	public function update_options() {
		$new_options = $this->get_remote_data('options', false);

		if( $new_options ) {
			$new_options = @unserialize( $new_options );

			if ( isset($new_options['mods']) && is_array($new_options['mods'])) {

				unset($new_options['mods']['0']);
				unset($new_options['mods']['nav_menu_locations']);
				unset($new_options['mods']['custom_css_post_id']);

				foreach ( $new_options['mods'] as $key => $val ) {

					if ( $key == 'footer_border_width' ) {
						$val = intval($val);
					}

					// Save the mod.
					set_theme_mod( $key, $val );
				}

//				set_theme_mod( 'load_wc_cart_fragments', true );
				set_theme_mod( 'images_loading_type_et-desktop', 'default' );
			}
			update_option( 'elementor_global_image_lightbox', '' );
		}
	}

	/**
	 * Import custom fonts.
	 *
	 * Load font file and update it in wp_options.
	 *
	 * @since   1.5.1
	 * @version 1.0.1
	 */
	public function import_custom_fonts(){
		$fonts = get_option( 'etheme-fonts', false );

		if ( ! is_array( $fonts ) ) {
			$fonts = array();
		}

		$new_fonts = $this->get_remote_data('fonts');

		if (!is_array($new_fonts) || !count($new_fonts)){
			return false;
		}

		foreach ($new_fonts as $key => $value) {
			$id  = array_search($value['id'], array_column($fonts, 'id'));

			if ( $id !== false ) {
				continue;
			}

			// Get remote font
			$file         = $value['file'];
			$file['time'] = current_time( 'mysql' );
			$remote_file  = $file['url'];

			$response = wp_remote_get( 
				$remote_file, 
				array(
					'sslverify' => false, 
					'timeout'   => 20,   
				) 
			);

			if ( is_wp_error( $response ) ) {
				error_log( 'Font file error: ' . $response->get_error_message() );
				return;
			}

			$content = wp_remote_retrieve_body( $response );
			$uploads = wp_get_upload_dir();

			// Setup right font folder
			$time         = current_time( 'mysql' );
			$y            = substr( $time, 0, 4 );
			$m            = substr( $time, 5, 2 );
			$subdir       = "/$y/$m";

			$fonts_uploads = array(
				'path'   => $uploads['basedir'] . '/custom-fonts' . $subdir,
				'url'    => $uploads['baseurl'] . '/custom-fonts' . $subdir ,
				'subdir' => '/custom-fonts' . $subdir,
			);

			// Create custom fonts folder
			$is_dir = is_dir( $fonts_uploads['path'] );
			if ( ! $is_dir ) {
				$resoult = wp_mkdir_p( $fonts_uploads['path'] );
				if ( ! $resoult ){
					return esc_html__( 'Can not create custom fonts folder', 'xstore-core' );
				}
			}

			// Put remote file content into the folder/ reset file url
			if ( ! file_exists( $fonts_uploads['path'] . '/' . $file['name'] ) ) {
				$file['name'] = str_replace( ' ', '-', $file['name'] );
				file_put_contents( $fonts_uploads['path'] . '/' . $file['name'], $content );
			}
			$file['url'] = $fonts_uploads['url'] . '/' . $file['name'];

			$value['file'] = $file;
			$fonts[] = $value;
		}
		update_option( 'etheme-fonts', $fonts );
		return false;
	}

	/**
	 * Update brands.
	 *
	 * Update theme brands.
	 *
	 * @since   1.5.2
	 * @version 1.0.1
	 *
	 * @param  string $terms terms to import
	 */
	public function update_terms($terms) {
		$remote_data = $this->get_remote_data($terms);
		if (! $remote_data ) {
			return;
		}

		$imported = array();

		foreach ($remote_data as $key => $value) {
			$term = get_term_by('slug', $value['term']['slug'], $terms);
			if ( $term ) {
				$id = $term->term_id;

				$imported[] = $id;

				// Update brand parent
				if ($value['term']['parent']) {

					$parent = array_filter($remote_data,function($var) use ($value){
						return( $var['term']['term_id'] == $value['term']['parent'] );
					});

						if ( $parent ) {
							$parent = end($parent);
							if ( $parent['term'] && $parent['term']['term_id'] ) {
								$parent = get_term_by('slug', $parent['term']['slug'], $terms);
								if ( $parent && ! is_wp_error( $parent ) ) {
									wp_update_term( $id, $terms, array(
										'parent' => $parent->term_id,
									));
								}
							}
						}
					}

				// Update brand thumbnail_id

				if (
					$value['meta']
					&& isset($value['meta']['thumbnail_id'])
					&& $value['meta']['thumbnail_id']
					&& isset($value['meta']['thumbnail_id'][0])
					&& $value['meta']['thumbnail_id'][0]
				) {
					update_term_meta( $id, 'thumbnail_id', $value['meta']['thumbnail_id'][0] );
				}

				// Update brand description
				if ($value['term']['description']) {
					wp_update_term( $id, $terms, array(
						'description' => $value['term']['description'],
					));
				}

				if ( in_array($terms, array('category', 'product_cat', 'product_tag') ) ) {
					if (isset($value['meta']['_et_second_description']) && isset($value['meta']['_et_second_description'][0])) {
						update_term_meta( $id, '_et_second_description', $value['meta']['_et_second_description'][0] );
					}

					if ($value['meta'] && isset($value['meta']['_et_page_heading_id']) && isset($value['meta']['_et_page_heading_id'][0] ) ) {
						update_term_meta( $id, '_et_page_heading_id', $value['meta']['_et_page_heading_id'][0] );
					}

					if (
						$value['meta'] &&
						isset($value['meta']['_et_page_heading']) &&
						isset($value['meta']['_et_page_heading'][0]) &&
						isset($value['meta']['_et_page_heading_id']) &&
						isset($value['meta']['_et_page_heading_id'][0] ) ) {
						update_term_meta( $id, '_et_page_heading', wp_get_attachment_url($value['meta']['_et_page_heading_id'][0]) ) ;
					}
				}
			}
		}

		$this->log_imported_data($terms, $imported);
	}

	/**
	 * import variation taxonomy.
	 *
	 * create taxonomy for product attributes.
	 *
	 * @since   1.5.3
	 * @version 1.0.2
	 *
	 * @param  string $terms terms to import
	 */
	public function import_variation_taxonomy($terms){
		$remote_data = $this->get_remote_data($terms);

		if ($remote_data) {
			foreach ($remote_data as $taxonomie) {
				$args = array(
					'id'      => '',
					'name'    => $taxonomie['taxonomie']['attribute_name'],
					'label'   => $taxonomie['taxonomie']['attribute_label'],
					'type'    => $taxonomie['taxonomie']['attribute_type'],
					'orderby' => $taxonomie['taxonomie']['attribute_orderby'],
					'public'  => false
				);
				wc_create_attribute( $args );
			}
		}
	}

	/**
	 * import variation trems.
	 *
	 * create trems for product attributes.
	 *
	 * @since   1.5.3
	 * @version 1.0.4
	 *
	 * @param  string $terms terms to import
	 * @log
	 * Fixed wp_error
	 * 1.0.3
	 * Fixed import of multicolor attributes
	 * Fixed import of variations attributes
	 */
	public function import_variations_trems($terms){
		$remote_data = $this->get_remote_data($terms);

		if ($remote_data) {
			foreach ($remote_data as $taxonomie) {
				if ( isset( $taxonomie['taxonomie'] ) && isset( $taxonomie['taxonomie']['terms'] ) ) {
					foreach ( $taxonomie['taxonomie']['terms'] as $term ) {
						$args = array(
							'description' => $term['term']['description'],
							'parent'      => 0,
							'slug'        => $term['term']['slug'],
						);

						$insert_data = wp_insert_term( $term['term']['name'], 'pa_' . $taxonomie['taxonomie']['attribute_name'], $args );

						foreach ( $term['meta'] as $key => $value ) {
							$swatches = array(
								'st-color-swatch-sq',
								'st-color-swatch',
								'st-label-swatch-sq',
								'st-label-swatch',
								'st-image-swatch-sq',
								'st-image-swatch'
							);
							if (
								in_array( $key, $swatches )
								&& ! is_wp_error( $value[0] )
								&& ! empty($value[0])
							) {
								$term_value = $value[0];

								if (@unserialize($term_value)){
									$term_value = unserialize($term_value);
								}
									if (!is_wp_error($insert_data)){
										update_term_meta( $insert_data['term_id'], $key, $term_value );
									} else {
										$created_term = get_term_by('slug', $term['term']['slug'], 'pa_' . $taxonomie['taxonomie']['attribute_name']);
										if ( $created_term && ! is_wp_error( $created_term ) ) {
											update_term_meta( $created_term->term_id, $key, $term_value );
										}
									}
								}
						}
					}
				}
			}
		}
	}

	/**
	 * import products variations.
	 *
	 * create variations for products.
	 *
	 * @since   1.5.3
	 * @version 1.0.2
	 *
	 * @param  integer $count number of products to import
	 */
	public function import_variation_products($count){
		$remote_data = $this->get_remote_data('variation_products');

		if ($remote_data) {
			$_i = 0;
			foreach ($remote_data as $key => $value) {
				if ( $_i == $count ) {
					return;
				}
				$_i++;

				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'post_name__in'  => array($key),
					'fields'         => 'ids'
				);
				$q  = get_posts( $args );
				$id = $q[0];

				foreach ($value as $variation) {
					$attributes = array();

					foreach ($variation['attributes'] as $attribute_key => $attribute) {
						$attributes[str_replace( 'attribute_pa_', '', $attribute_key )] = $attribute;
					}

					$variation_data =  array(
						'attributes' => $attributes,
						// 'sku'           => $variation['sku'],
						'regular_price' => $variation['display_regular_price'],
						'sale_price'    => $variation['display_price'],
						'stock_qty'     => $variation['max_qty'],
						// 'stock' => $variation['is_in_stock'],
						'image_id' => $variation['image_id'],

					);
					$this->create_product_variation( $id, $variation_data );
				}
			}
		}
	}

	/**
	 * import products variations.
	 *
	 * create variations for products.
	 *
	 * @since   1.5.3
	 * @version 1.0.2
	 * @log 1.0.1
	 * Fix non object slug
	 * 1.0.2
	 * Fix $product not a product
	 */
	public function create_product_variation( $product_id, $variation_data ){
		// Get the Variable product object (parent)
		$product = wc_get_product($product_id);

		if (!$product) return;

		$variation_post = array(
			'post_title'  => $product->get_title(),
			'post_name'   => 'product-'.$product_id.'-variation',
			'post_status' => 'publish',
			'post_parent' => $product_id,
			'post_type'   => 'product_variation',
			'guid'        => $product->get_permalink()
		);

		$variation_id = wp_insert_post( $variation_post );
		$variation    = new \WC_Product_Variation( $variation_id );

		foreach ($variation_data['attributes'] as $attribute => $term_name ){
			$taxonomy = 'pa_'.$attribute;

			if( ! taxonomy_exists( $taxonomy ) ) {
				register_taxonomy(
					$taxonomy,
					'product_variation',
					array(
						'hierarchical' => false,
						'label'        => ucfirst( $attribute ),
						'query_var'    => true,
						'rewrite'      => array( 'slug' => sanitize_title($attribute) ),
					)
				);
			}

			if( ! term_exists( $term_name, $taxonomy ) ) {
				wp_insert_term( $term_name, $taxonomy );
			}

			$term_slug       = get_term_by('name', $term_name, $taxonomy );

			if (is_object($term_slug) && $term_slug->slug){
				$term_slug = $term_slug->slug;
				$post_term_names = wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );
				if( ! in_array( $term_name, $post_term_names ) ){
					wp_set_post_terms( $product_id, $term_name, $taxonomy, true );
				}
				update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
			}
		}

		if( ! empty( $variation_data['sku'] ) ){
			$variation->set_sku( $variation_data['sku'] );
		}

		if( empty( $variation_data['sale_price'] ) ){
			$variation->set_price( $variation_data['regular_price'] );
		} else {
			$variation->set_price( $variation_data['sale_price'] );
			$variation->set_sale_price( $variation_data['sale_price'] );
		}
		$variation->set_regular_price( $variation_data['regular_price'] );

		if( ! empty($variation_data['stock_qty']) ){
			$variation->set_stock_quantity( $variation_data['stock_qty'] );
			$variation->set_manage_stock(true);
			$variation->set_stock_status('');
		} else {
			$variation->set_manage_stock(false);
		}

		if ( ! empty( $variation_data['image_id'] ) ) {
			$variation->set_image_id($variation_data['image_id']);
		}

		$variation->set_weight('');
		$variation->save();
	}

	/**
	 * import products variations.
	 *
	 * create variations for products.
	 *
	 * @since   2.2.1
	 * @version 1.0.1
	 *
	 * @param  string $file   name of file with extension
	 * @param  string $option option name
	 */
	public function import_multiple_conditions($option, $file){
		$local = json_decode( get_option( $option ), true );
		if ( ! is_array($local) ) {
			$local = array();
		}

		$remote_data = $this->get_remote_data($file);

		if( is_null($remote_data) ) return;

		foreach ($remote_data as $key => $value) {
			if ( ! isset( $local[$key] ) ) {
				$local[$key] = $value;
			}
		}
		update_option( $option, json_encode($local) );
//		$this->log_imported_data('multiples', $option);
	}

	/**
	 * Import sales boosters
	 *
	 * @since   9.2.9
	 * @version 1.0.0
	 *
	 */
	public function sales_boosters(){
		$remote_data = $this->get_remote_data('sales_boosters');

		if ($remote_data) {
			if (isset($remote_data['xstore_sales_booster_settings'])){
				update_option('xstore_sales_booster_settings', $remote_data['xstore_sales_booster_settings']);
			}
			if (isset($remote_data['groups'])){
				foreach ($remote_data['groups'] as $key => $value) {
					if ($value){
						update_option( $key, $value );
					}
				}
			}
		}
	}

	/**
	 * import elementor globals.
	 *
	 * @since   2.3.0
	 * @version 1.0.1
	 *
	 */
	public function import_elementor_globals(){
		$remote_data = $this->get_remote_data('elementor_globals');

		if ($remote_data) {
			foreach ($remote_data as $key => $value) {
				update_option( $key, $value );
			}
		}

		if (class_exists('CSS_Manager')) {
			$CSS_Manager = new CSS_Manager();
			$CSS_Manager->save_settings();
		}
	}

	public function import_elementor_sections($type) {

		if (!in_array( $this->engine, array('elementor', 'elementor_builders'))){
			return true;
		}

		ini_set( 'max_execution_time', 900 );

		$data = $this->get_remote_data($type);

		// Enable svg support
		add_filter( 'upload_mimes', [ $this, 'add_svg_support' ] );

		switch ($type) {
			case 'elementor_footers':
				$type = 'footer';
				break;
			case 'elementor_headers':
				$type = 'header';
				break;
			case 'elementor_archives':
				$type = 'product-archive';
				break;
			case 'elementor_single_products':
				$type = 'product';
				break;
			case 'elementor_post_archive':
				$type = 'archive';
				break;
			case 'elementor_post':
				$type = 'single';
				break;
			default:
				$type = 'section';
				break;
		}

		foreach ($data as $saved_template) {
			$document = \Elementor\Plugin::$instance->documents->create(
				$type,
				[
					'post_title'  => $saved_template['title'],
					'post_status' => 'publish',
					'post_type'   => 'elementor_library',
				]
			);

			if ( is_wp_error( $document ) ) {
				continue;
			}

			$template_data = [
				'elements' => $this->make_content_unique( $saved_template['content'] ),
				'settings' => $saved_template['settings'],
			];

			$document->save( $template_data );

			$template_id = $document->get_main_id();

			if ( ! $template_id || ! is_numeric($template_id) ) {
				continue;
			}

			// setup the conditions
			if ( isset($saved_template['conditions']) && is_array($saved_template['conditions']) ){
				update_post_meta($template_id, '_elementor_conditions', $saved_template['conditions']);

				$elementor_condition_kay = 'elementor_pro_theme_builder_conditions';
				$conditions = get_option($elementor_condition_kay);

				if (!is_array($conditions)){
					$conditions = array();
				}

				$condition_type = $type;

				if ($type == 'product-archive' || $type == 'archive'){
					$condition_type = 'archive';
				} elseif ($type == 'product' || $type == 'single'){
					$condition_type = 'single';
				}

				if (isset($conditions[$condition_type])){
					$conditions[$condition_type][$template_id] =$saved_template['conditions'];
				} else {
					$conditions[$condition_type] = array(
						$template_id => $saved_template['conditions']
					);
				}
				update_option( $elementor_condition_kay, $conditions );
			}

			$this->log_imported_data('elementor_templates-' . $type, $template_id);

			/**
			 * After template library save.
			 *
			 * Fires after Elementor template library was saved.
			 *
			 * @param int   $template_id   The ID of the template.
			 * @param array $template_data The template data.
			 *
			 * @since 1.0.1
			 *
			 */
			do_action( 'elementor/template-library/after_save_template', $template_id, $template_data );

			/**
			 * After template library update.
			 *
			 * Fires after Elementor template library was updated.
			 *
			 * @param int   $template_id   The ID of the template.
			 * @param array $template_data The template data.
			 *
			 * @since 1.0.1
			 *
			 */
			do_action( 'elementor/template-library/after_update_template', $template_id, $template_data );

		}


		remove_filter( 'upload_mimes', [ $this, 'add_svg_support' ] );
	}

	/**
	 * Import Gutenberg patterns/reusable blocks.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	public function import_gutenberg_patterns() {
		$data = $this->get_remote_data( 'gutenberg_patterns' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$post_type = isset( $item['type'] ) ? $item['type'] : 'wp_block';
			if ( ! in_array( $post_type, array( 'wp_block', 'wp_pattern' ), true ) ) {
				$post_type = 'wp_block';
			}

			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			$post_name = isset( $item['slug'] ) ? sanitize_title( $item['slug'] ) : '';
			$existing  = $post_name ? get_page_by_path( $post_name, OBJECT, $post_type ) : null;

			$postarr = array(
				'post_type'         => $post_type,
				'post_status'       => isset( $item['status'] ) ? $item['status'] : 'publish',
				'post_title'        => isset( $item['title'] ) ? $item['title'] : '',
				'post_name'         => $post_name,
				'post_content'      => isset( $item['content'] ) ? $item['content'] : '',
				'post_excerpt'      => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
				'post_date'         => isset( $item['date'] ) ? $item['date'] : '',
				'post_date_gmt'     => isset( $item['date_gmt'] ) ? $item['date_gmt'] : '',
				'post_modified'     => isset( $item['modified'] ) ? $item['modified'] : '',
				'post_modified_gmt' => isset( $item['modified_gmt'] ) ? $item['modified_gmt'] : '',
			);

			$postarr = wp_slash( $postarr );

			if ( $existing && isset( $existing->ID ) ) {
				$postarr['ID'] = $existing->ID;
				$post_id = wp_update_post( $postarr, true );
			} else {
				if ( isset( $item['id'] ) ) {
					$import_id = (int) $item['id'];
					if ( $import_id > 0 ) {
						$maybe_existing = get_post( $import_id );
						if ( ! $maybe_existing ) {
							$postarr['import_id'] = $import_id;
						}
					}
				}
				$post_id = wp_insert_post( $postarr, true );
			}

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			if ( isset( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_values ) {
					if ( '' === $meta_key ) {
						continue;
					}

					delete_post_meta( $post_id, $meta_key );

					if ( is_array( $meta_values ) ) {
						foreach ( $meta_values as $meta_value ) {
							add_post_meta( $post_id, $meta_key, $meta_value );
						}
					} else {
						update_post_meta( $post_id, $meta_key, $meta_values );
					}
				}
			}

			$this->log_imported_data( 'gutenberg_patterns', $post_id );
		}

		return true;
	}

	/**
	 * Import Gutenberg template parts.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	public function import_gutenberg_template_parts() {
		$data = $this->get_remote_data( 'gutenberg_template_parts' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$this->ensure_gutenberg_template_part_support();

		if ( ! post_type_exists( 'wp_template_part' ) ) {
			return false;
		}

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$post_name = isset( $item['slug'] ) ? wp_unslash( $item['slug'] ) : '';
			$post_name = is_string( $post_name ) ? $post_name : '';
			$theme_slug = '';

			if ( '' !== $post_name && false !== strpos( $post_name, '//' ) ) {
				$parts = explode( '//', $post_name, 2 );
				$theme_slug = isset( $parts[0] ) ? sanitize_title( $parts[0] ) : '';
				$post_name  = isset( $parts[1] ) ? $parts[1] : '';
			}

			$existing = null;
			if ( '' !== $post_name ) {
				$existing = get_page_by_path( $post_name, OBJECT, 'wp_template_part' );
				if ( ! $existing ) {
					$by_slug = get_posts(
						array(
							'post_type'      => 'wp_template_part',
							'name'           => sanitize_title( $post_name ),
							'posts_per_page' => 1,
							'no_found_rows'  => true,
						)
					);
					if ( ! empty( $by_slug ) ) {
						$existing = $by_slug[0];
					}
				}
			}

			$post_name = sanitize_title( $post_name );

			$postarr = array(
				'post_type'         => 'wp_template_part',
				'post_status'       => isset( $item['status'] ) ? $item['status'] : 'publish',
				'post_title'        => isset( $item['title'] ) ? $item['title'] : '',
				'post_name'         => $post_name,
				'post_content'      => isset( $item['content'] ) ? $item['content'] : '',
				'post_excerpt'      => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
				'post_date'         => isset( $item['date'] ) ? $item['date'] : '',
				'post_date_gmt'     => isset( $item['date_gmt'] ) ? $item['date_gmt'] : '',
				'post_modified'     => isset( $item['modified'] ) ? $item['modified'] : '',
				'post_modified_gmt' => isset( $item['modified_gmt'] ) ? $item['modified_gmt'] : '',
			);

			$postarr = wp_slash( $postarr );

			if ( $existing && isset( $existing->ID ) ) {
				$postarr['ID'] = $existing->ID;
				$post_id = wp_update_post( $postarr, true );
			} else {
				$post_id = wp_insert_post( $postarr, true );
			}

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			if ( isset( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_values ) {
					if ( '' === $meta_key ) {
						continue;
					}

					delete_post_meta( $post_id, $meta_key );

					if ( is_array( $meta_values ) ) {
						foreach ( $meta_values as $meta_value ) {
							add_post_meta( $post_id, $meta_key, $meta_value );
						}
					} else {
						update_post_meta( $post_id, $meta_key, $meta_values );
					}
				}
			}

			if ( isset( $item['terms'] ) && is_array( $item['terms'] ) ) {
				foreach ( $item['terms'] as $taxonomy => $term_slugs ) {
					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}
					if ( ! is_array( $term_slugs ) ) {
						$term_slugs = array( $term_slugs );
					}
					$term_slugs = array_filter( array_map( 'sanitize_title', $term_slugs ) );
					if ( ! empty( $term_slugs ) ) {
						wp_set_object_terms( $post_id, $term_slugs, $taxonomy, false );
					}
				}
			}

			if ( taxonomy_exists( 'wp_theme' ) ) {
				$theme_term = $theme_slug ? $theme_slug : get_stylesheet();
				wp_set_object_terms( $post_id, array( $theme_term ), 'wp_theme', false );
			}

			if ( taxonomy_exists( 'wp_template_part_area' ) ) {
				$area = '';
				if ( isset( $item['area'] ) ) {
					$area = is_string( $item['area'] ) ? $item['area'] : '';
				}
				if ( '' === $area && isset( $item['meta']['area'][0] ) ) {
					$area = is_string( $item['meta']['area'][0] ) ? $item['meta']['area'][0] : '';
				}
				if ( '' === $area && isset( $item['meta']['wp_template_part_area'][0] ) ) {
					$area = is_string( $item['meta']['wp_template_part_area'][0] ) ? $item['meta']['wp_template_part_area'][0] : '';
				}
				$area = sanitize_title( $area );
				if ( '' !== $area ) {
					wp_set_object_terms( $post_id, array( $area ), 'wp_template_part_area', false );
				}
			}

			$this->log_imported_data( 'gutenberg_template_parts', $post_id );
		}

		return true;
	}

	/**
	 * Import Gutenberg global styles.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	public function import_gutenberg_styles() {
		$data = $this->get_remote_data( 'gutenberg_styles' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$this->ensure_gutenberg_global_styles_support();

		if ( ! post_type_exists( 'wp_global_styles' ) ) {
			return false;
		}

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$stylesheet = get_stylesheet();
			$post_name  = 'wp-global-styles-' . urlencode( $stylesheet );

			$existing = get_page_by_path( $post_name, OBJECT, 'wp_global_styles' );
			if ( ! $existing && taxonomy_exists( 'wp_theme' ) ) {
				$by_theme = get_posts(
					array(
						'post_type'      => 'wp_global_styles',
						'posts_per_page' => 1,
						'no_found_rows'  => true,
						'tax_query'      => array(
							array(
								'taxonomy' => 'wp_theme',
								'field'    => 'name',
								'terms'    => $stylesheet,
							),
						),
					)
				);
				if ( ! empty( $by_theme ) ) {
					$existing = $by_theme[0];
				}
			}

			$content = isset( $item['content'] ) ? $item['content'] : '';
			if ( is_array( $content ) ) {
				$content = wp_json_encode( $content, JSON_UNESCAPED_UNICODE );
			} elseif ( is_string( $content ) ) {
				$decoded = json_decode( $content, true );
				if ( is_array( $decoded ) ) {
					$decoded['isGlobalStylesUserThemeJSON'] = true;
					if ( ! isset( $decoded['version'] ) ) {
						$decoded['version'] = class_exists( 'WP_Theme_JSON' ) ? \WP_Theme_JSON::LATEST_SCHEMA : 2;
					}
					$content = wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE );
				}
			}

			if ( '' === $content ) {
				$content = '{"version": ' . ( class_exists( 'WP_Theme_JSON' ) ? \WP_Theme_JSON::LATEST_SCHEMA : 2 ) . ', "isGlobalStylesUserThemeJSON": true }';
			}

			$postarr = array(
				'post_type'         => 'wp_global_styles',
				'post_status'       => isset( $item['status'] ) ? $item['status'] : 'publish',
				'post_title'        => isset( $item['title'] ) ? $item['title'] : '',
				'post_name'         => $post_name,
				'post_content'      => $content,
				'post_excerpt'      => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
				'post_date'         => isset( $item['date'] ) ? $item['date'] : '',
				'post_date_gmt'     => isset( $item['date_gmt'] ) ? $item['date_gmt'] : '',
				'post_modified'     => isset( $item['modified'] ) ? $item['modified'] : '',
				'post_modified_gmt' => isset( $item['modified_gmt'] ) ? $item['modified_gmt'] : '',
			);

			$postarr = wp_slash( $postarr );

			if ( $existing && isset( $existing->ID ) ) {
				$postarr['ID'] = $existing->ID;
				$post_id = wp_update_post( $postarr, true );
			} else {
				$post_id = wp_insert_post( $postarr, true );
			}

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			if ( isset( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_values ) {
					if ( '' === $meta_key ) {
						continue;
					}

					delete_post_meta( $post_id, $meta_key );

					if ( is_array( $meta_values ) ) {
						foreach ( $meta_values as $meta_value ) {
							add_post_meta( $post_id, $meta_key, $meta_value );
						}
					} else {
						update_post_meta( $post_id, $meta_key, $meta_values );
					}
				}
			}

			if ( taxonomy_exists( 'wp_theme' ) ) {
				wp_set_object_terms( $post_id, array( $stylesheet ), 'wp_theme', false );
			}

			$this->log_imported_data( 'gutenberg_styles', $post_id );
		}

		if ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			\WP_Theme_JSON_Resolver::clean_cached_data();
		}
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}

		return true;
	}

	/**
	 * Import Gutenberg navigation posts.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	public function import_gutenberg_navigation() {
		$data = $this->get_remote_data( 'gutenberg_navigation' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$this->ensure_gutenberg_navigation_support();

		if ( ! post_type_exists( 'wp_navigation' ) ) {
			return false;
		}

		$id_map = array();
		$nav_by_slug = array();
		$default_nav_id = 0;

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$post_name = isset( $item['slug'] ) ? wp_unslash( $item['slug'] ) : '';
			$post_name = is_string( $post_name ) ? $post_name : '';
			$post_name = sanitize_title( $post_name );

			$existing = null;
			if ( '' !== $post_name ) {
				$existing = get_page_by_path( $post_name, OBJECT, 'wp_navigation' );
				if ( ! $existing ) {
					$by_slug = get_posts(
						array(
							'post_type'      => 'wp_navigation',
							'name'           => $post_name,
							'posts_per_page' => 1,
							'no_found_rows'  => true,
						)
					);
					if ( ! empty( $by_slug ) ) {
						$existing = $by_slug[0];
					}
				}
			}

			$content = isset( $item['content'] ) ? $item['content'] : '';
			if ( is_string( $content ) && '' !== $content ) {
				$blocks = parse_blocks( $content );
				$changed = false;
				$blocks = $this->map_navigation_link_targets( $blocks, $changed );
				if ( $changed ) {
					$content = serialize_blocks( $blocks );
				}
			}

			$postarr = array(
				'post_type'         => 'wp_navigation',
				'post_status'       => isset( $item['status'] ) ? $item['status'] : 'publish',
				'post_title'        => isset( $item['title'] ) ? $item['title'] : '',
				'post_name'         => $post_name,
				'post_content'      => $content,
				'post_excerpt'      => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
				'post_date'         => isset( $item['date'] ) ? $item['date'] : '',
				'post_date_gmt'     => isset( $item['date_gmt'] ) ? $item['date_gmt'] : '',
				'post_modified'     => isset( $item['modified'] ) ? $item['modified'] : '',
				'post_modified_gmt' => isset( $item['modified_gmt'] ) ? $item['modified_gmt'] : '',
			);

			$postarr = wp_slash( $postarr );

			if ( $existing && isset( $existing->ID ) ) {
				$postarr['ID'] = $existing->ID;
				$post_id = wp_update_post( $postarr, true );
			} else {
				$post_id = wp_insert_post( $postarr, true );
			}

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			if ( '' !== $post_name ) {
				$nav_by_slug[ $post_name ] = (int) $post_id;
				if ( 0 === $default_nav_id && 'menu' === $post_name ) {
					$default_nav_id = (int) $post_id;
				}
			}

			if ( isset( $item['id'] ) ) {
				$old_id = (int) $item['id'];
				if ( $old_id > 0 ) {
					$id_map[ $old_id ] = (int) $post_id;
				}
			}

			if ( isset( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_values ) {
					if ( '' === $meta_key ) {
						continue;
					}

					delete_post_meta( $post_id, $meta_key );

					if ( is_array( $meta_values ) ) {
						foreach ( $meta_values as $meta_value ) {
							add_post_meta( $post_id, $meta_key, $meta_value );
						}
					} else {
						update_post_meta( $post_id, $meta_key, $meta_values );
					}
				}
			}

			if ( isset( $item['terms'] ) && is_array( $item['terms'] ) ) {
				foreach ( $item['terms'] as $taxonomy => $term_slugs ) {
					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}
					if ( ! is_array( $term_slugs ) ) {
						$term_slugs = array( $term_slugs );
					}
					$term_slugs = array_filter( array_map( 'sanitize_title', $term_slugs ) );
					if ( ! empty( $term_slugs ) ) {
						wp_set_object_terms( $post_id, $term_slugs, $taxonomy, false );
					}
				}
			}

			$this->log_imported_data( 'gutenberg_navigation', $post_id );
		}

		if ( ! empty( $id_map ) || $default_nav_id ) {
			$this->update_gutenberg_navigation_references( $id_map, $default_nav_id, $nav_by_slug );
			$preferred_nav_id = isset( $nav_by_slug['menu'] ) ? (int) $nav_by_slug['menu'] : $default_nav_id;
			$this->update_gutenberg_navigation_header_template_parts( $preferred_nav_id );
		}

		$this->force_sync_gutenberg_navigation_frontend( $default_nav_id );

		return true;
	}

	/**
	 * Import Gutenberg templates.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	public function import_gutenberg_templates() {
		$data = $this->get_remote_data( 'gutenberg_templates' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$this->ensure_gutenberg_templates_support();

		if ( ! post_type_exists( 'wp_template' ) ) {
			return false;
		}

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$original_slug = isset( $item['slug'] ) ? wp_unslash( $item['slug'] ) : '';
			$original_slug = is_string( $original_slug ) ? $original_slug : '';
			$theme_slug = '';
			$template_slug = $original_slug;

			if ( '' !== $original_slug && false !== strpos( $original_slug, '//' ) ) {
				$parts = explode( '//', $original_slug, 2 );
				$theme_slug = isset( $parts[0] ) ? sanitize_title( $parts[0] ) : '';
				$template_slug  = isset( $parts[1] ) ? $parts[1] : '';
			}

			$template_slug = is_string( $template_slug ) ? $template_slug : '';
			$post_name = sanitize_title( $template_slug );

			$content = isset( $item['content'] ) ? $item['content'] : '';
			if ( is_string( $content ) && '' !== $content ) {
				$blocks = parse_blocks( $content );
				$changed = false;
				$blocks = $this->normalize_template_part_blocks( $blocks, $changed );
				if ( $changed ) {
					$content = serialize_blocks( $blocks );
				}
			}

			$postarr = array(
				'post_type'         => 'wp_template',
				'post_status'       => isset( $item['status'] ) ? $item['status'] : 'publish',
				'post_title'        => isset( $item['title'] ) ? $item['title'] : '',
				'post_name'         => $post_name,
				'post_content'      => $content,
				'post_excerpt'      => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
				'post_date'         => isset( $item['date'] ) ? $item['date'] : '',
				'post_date_gmt'     => isset( $item['date_gmt'] ) ? $item['date_gmt'] : '',
				'post_modified'     => isset( $item['modified'] ) ? $item['modified'] : '',
				'post_modified_gmt' => isset( $item['modified_gmt'] ) ? $item['modified_gmt'] : '',
			);

			$postarr = wp_slash( $postarr );

			$existing = null;
			if ( '' !== $post_name ) {
				$existing = get_page_by_path( $post_name, OBJECT, 'wp_template' );
				if ( ! $existing ) {
					$by_slug = get_posts(
						array(
							'post_type'      => 'wp_template',
							'name'           => $post_name,
							'posts_per_page' => 1,
							'no_found_rows'  => true,
						)
					);
					if ( ! empty( $by_slug ) ) {
						$existing = $by_slug[0];
					}
				}
			}

			if ( $existing && isset( $existing->ID ) ) {
				$postarr['ID'] = $existing->ID;
				$post_id = wp_update_post( $postarr, true );
			} else {
				$post_id = wp_insert_post( $postarr, true );
			}

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			if ( isset( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_values ) {
					if ( '' === $meta_key ) {
						continue;
					}

					delete_post_meta( $post_id, $meta_key );

					if ( is_array( $meta_values ) ) {
						foreach ( $meta_values as $meta_value ) {
							add_post_meta( $post_id, $meta_key, $meta_value );
						}
					} else {
						update_post_meta( $post_id, $meta_key, $meta_values );
					}
				}
			}

			if ( taxonomy_exists( 'wp_theme' ) ) {
				$theme_term = $theme_slug ? $theme_slug : get_stylesheet();
				wp_set_object_terms( $post_id, array( $theme_term ), 'wp_theme', false );
			}

			// Force overwrite template by slug for current theme.
			if ( taxonomy_exists( 'wp_theme' ) && $post_name ) {
				$theme_term = $theme_slug ? $theme_slug : get_stylesheet();
				$by_theme_slug = get_posts(
					array(
						'post_type'      => 'wp_template',
						'posts_per_page' => -1,
						'no_found_rows'  => true,
						'name'           => $post_name,
						'tax_query'      => array(
							array(
								'taxonomy' => 'wp_theme',
								'field'    => 'name',
								'terms'    => $theme_term,
							),
						),
					)
				);
				foreach ( $by_theme_slug as $template_post ) {
					if ( $template_post instanceof \WP_Post && $template_post->ID !== $post_id ) {
						wp_update_post(
							array(
								'ID'           => $template_post->ID,
								'post_content' => $content,
								'post_title'   => isset( $item['title'] ) ? $item['title'] : $template_post->post_title,
								'post_status'  => isset( $item['status'] ) ? $item['status'] : $template_post->post_status,
								'post_name'    => $post_name,
							)
						);
					}
				}
			}

			$this->log_imported_data( 'gutenberg_templates', $post_id );
		}

		$this->force_sync_gutenberg_navigation_frontend( 0 );

		return true;
	}

	/**
	 * Import Gutenberg site logo.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	public function import_gutenberg_site_logo() {
		$data = $this->get_remote_data( 'gutenberg_site_logo' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$filename = isset( $data['filename'] ) ? basename( $data['filename'] ) : '';
		$url = isset( $data['url'] ) ? $data['url'] : '';

		if ( ! $filename && $url ) {
			$filename = basename( wp_parse_url( $url, PHP_URL_PATH ) );
		}

		$attachment_id = $url ? $this->sideload_site_logo( $url, $filename ) : 0;

		if ( $attachment_id ) {
			set_theme_mod( 'custom_logo', $attachment_id );
			return true;
		}

		return false;
	}

	/**
	 * Normalize core/template-part blocks to current theme.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function normalize_template_part_blocks( array $blocks, bool &$changed ) : array {
		$stylesheet = get_stylesheet();

		foreach ( $blocks as $index => $block ) {
			if ( isset( $block['blockName'] ) && 'core/template-part' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
				$slug  = isset( $attrs['slug'] ) ? $attrs['slug'] : '';
				$theme = isset( $attrs['theme'] ) ? $attrs['theme'] : '';

				if ( $theme !== $stylesheet ) {
					$blocks[ $index ]['attrs']['theme'] = $stylesheet;
					$changed = true;
				}

				if ( $slug ) {
					$this->ensure_template_part_for_theme( $slug );
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->normalize_template_part_blocks(
					$block['innerBlocks'],
					$changed
				);
			}
		}

		return $blocks;
	}

	/**
	 * Ensure template part with slug is assigned to current theme.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function ensure_template_part_for_theme( string $slug ) {
		if ( ! post_type_exists( 'wp_template_part' ) ) {
			return;
		}

		$slug = sanitize_title( $slug );
		if ( '' === $slug ) {
			return;
		}

		$post = get_page_by_path( $slug, OBJECT, 'wp_template_part' );
		if ( ! $post || ! isset( $post->ID ) ) {
			return;
		}

		if ( taxonomy_exists( 'wp_theme' ) ) {
			wp_set_object_terms( $post->ID, array( get_stylesheet() ), 'wp_theme', false );
		}
	}

	/**
	 * Map navigation-link targets to local post IDs/URLs.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function map_navigation_link_targets( array $blocks, bool &$changed ) : array {
		foreach ( $blocks as $index => $block ) {
			if ( isset( $block['blockName'] ) && 'core/navigation-link' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
				$kind  = isset( $attrs['kind'] ) ? $attrs['kind'] : '';
				$type  = isset( $attrs['type'] ) ? $attrs['type'] : '';

				if ( 'post-type' === $kind && $type ) {
					$post_id = $this->resolve_navigation_target_id( $attrs );
					if ( $post_id ) {
						$blocks[ $index ]['attrs']['id']  = $post_id;
						$blocks[ $index ]['attrs']['url'] = get_permalink( $post_id );
						$changed = true;
					} elseif ( isset( $attrs['url'] ) && is_string( $attrs['url'] ) && '' !== $attrs['url'] ) {
						$home_url = $this->resolve_navigation_home_url( $attrs );
						if ( $home_url ) {
							$blocks[ $index ]['attrs']['url'] = $home_url;
							if ( isset( $blocks[ $index ]['attrs']['id'] ) ) {
								unset( $blocks[ $index ]['attrs']['id'] );
							}
							$changed = true;
						} else {
						$normalized_url = $this->normalize_demo_link_url( $attrs['url'] );
						if ( $normalized_url && $normalized_url !== $attrs['url'] ) {
							$blocks[ $index ]['attrs']['url'] = $normalized_url;
							if ( isset( $blocks[ $index ]['attrs']['id'] ) ) {
								unset( $blocks[ $index ]['attrs']['id'] );
							}
							$changed = true;
						}
						}
					}
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->map_navigation_link_targets(
					$block['innerBlocks'],
					$changed
				);
			}
		}

		return $blocks;
	}

	/**
	 * Resolve navigation link target to local post ID.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function resolve_navigation_target_id( array $attrs ) {
		$type  = isset( $attrs['type'] ) ? $attrs['type'] : '';
		$label = isset( $attrs['label'] ) ? $attrs['label'] : '';
		$url   = isset( $attrs['url'] ) ? $attrs['url'] : '';

		if ( $url ) {
			$path = wp_parse_url( $url, PHP_URL_PATH );
			if ( $path ) {
				$path = trim( $path, '/' );
				if ( '' !== $path ) {
					$post = get_page_by_path( $path, OBJECT, $type );
					if ( $post && isset( $post->ID ) ) {
						return (int) $post->ID;
					}
				}
			}
		}

		if ( $label ) {
			$post = get_page_by_title( wp_strip_all_tags( $label ), OBJECT, $type );
			if ( $post && isset( $post->ID ) ) {
				return (int) $post->ID;
			}
		}

		return 0;
	}

	/**
	 * Force home link for "Home" navigation item when no local match.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function resolve_navigation_home_url( array $attrs ) : string {
		$label = isset( $attrs['label'] ) ? $attrs['label'] : '';
		$type  = isset( $attrs['type'] ) ? $attrs['type'] : '';
		if ( 'page' !== $type ) {
			return '';
		}

		$normalized = strtolower( trim( wp_strip_all_tags( $label ) ) );
		if ( in_array( $normalized, array( 'home', 'homepage', 'home page' ), true ) ) {
			return home_url( '/' );
		}

		if ( in_array( $normalized, array( 'blog', 'news' ), true ) ) {
			$blog_id = (int) get_option( 'page_for_posts' );
			if ( $blog_id ) {
				return get_permalink( $blog_id );
			}
		}

		return '';
	}

	/**
	 * Replace demo-site URL with local site URL (same path).
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function normalize_demo_link_url( string $url ) : string {
		$home = home_url( '/' );
		$home_host = wp_parse_url( $home, PHP_URL_HOST );
		$url_host = wp_parse_url( $url, PHP_URL_HOST );
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( ! $url_host || ! $home_host || $url_host === $home_host || ! $path ) {
			return $url;
		}

		$query = wp_parse_url( $url, PHP_URL_QUERY );
		$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
		$normalized = home_url( $path );
		if ( $query ) {
			$normalized .= '?' . $query;
		}
		if ( $fragment ) {
			$normalized .= '#' . $fragment;
		}

		return $normalized;
	}

	/**
	 * Download and attach a site logo if it does not exist locally.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function sideload_site_logo( string $url, string $filename = '' ) : int {
		if ( ! function_exists( 'download_url' ) || ! function_exists( 'media_handle_sideload' ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			return 0;
		}

		$file = array(
			'name'     => $filename ? $filename : basename( wp_parse_url( $url, PHP_URL_PATH ) ),
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file, 0 );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			return 0;
		}

		return (int) $attachment_id;
	}

	/**
	 * Update navigation refs inside template parts/templates to match imported IDs.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function update_gutenberg_navigation_references( array $id_map, int $default_nav_id = 0, array $nav_by_slug = array() ) {
		$posts = get_posts(
			array(
				'post_type'      => array( 'wp_template_part', 'wp_template' ),
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'suppress_filters' => true,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$blocks = parse_blocks( $post->post_content );
			$changed = false;
			$blocks = $this->map_navigation_block_refs( $blocks, $id_map, $default_nav_id, $nav_by_slug, $changed );

			if ( $changed ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => serialize_blocks( $blocks ),
					)
				);
			}
		}
	}

	/**
	 * Recursively map navigation block refs.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function map_navigation_block_refs( array $blocks, array $id_map, int $default_nav_id, array $nav_by_slug, bool &$changed ) : array {
		foreach ( $blocks as $index => $block ) {
			if ( isset( $block['blockName'] ) && 'core/navigation' === $block['blockName'] ) {
				if ( isset( $block['attrs']['ref'] ) ) {
					$old_id = (int) $block['attrs']['ref'];
					if ( $old_id && isset( $id_map[ $old_id ] ) ) {
						$blocks[ $index ]['attrs']['ref'] = $id_map[ $old_id ];
						$changed = true;
					} elseif ( $old_id && ! $this->is_valid_navigation_post( $old_id ) && $default_nav_id ) {
						$blocks[ $index ]['attrs']['ref'] = $default_nav_id;
						$changed = true;
					}
				} elseif ( $default_nav_id ) {
					$blocks[ $index ]['attrs']['ref'] = $default_nav_id;
					$changed = true;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->map_navigation_block_refs(
					$block['innerBlocks'],
					$id_map,
					$default_nav_id,
					$nav_by_slug,
					$changed
				);
			}
		}

		return $blocks;
	}

	/**
	 * Force navigation refs inside header template parts to default nav.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function update_gutenberg_navigation_header_template_parts( int $default_nav_id ) {
		if ( ! $default_nav_id ) {
			return;
		}

		$header_updated = false;
		$posts = get_posts(
			array(
				'post_type'      => 'wp_template_part',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'suppress_filters' => true,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$area = get_post_meta( $post->ID, 'wp_template_part_area', true );
			$is_header = 'header' === $area
				|| false !== stripos( $post->post_name, 'header' )
				|| false !== stripos( $post->post_title, 'header' );

			if ( ! $is_header ) {
				$raw = $post->post_content;
				$has_nav = is_string( $raw ) && false !== strpos( $raw, 'wp:navigation' );
				$has_brand = is_string( $raw ) && ( false !== strpos( $raw, 'wp:site-logo' ) || false !== strpos( $raw, 'wp:site-title' ) );
				if ( $has_nav && $has_brand ) {
					$is_header = true;
				}
			}

			if ( ! $is_header ) {
				continue;
			}

			$blocks = parse_blocks( $post->post_content );
			if ( empty( $blocks ) ) {
				continue;
			}

			$changed = false;
			$blocks = $this->force_navigation_block_ref_default( $blocks, $default_nav_id, $changed );

			if ( $changed ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => serialize_blocks( $blocks ),
					)
				);
			}
			$header_updated = true;
		}

		if ( $header_updated ) {
			return;
		}

		$theme_dir = get_stylesheet_directory();
		$header_file = $theme_dir . '/parts/header.html';
		if ( ! is_readable( $header_file ) ) {
			return;
		}

		$content = file_get_contents( $header_file );
		if ( false === $content ) {
			return;
		}

		$blocks = parse_blocks( $content );
		if ( empty( $blocks ) ) {
			return;
		}

		$changed = false;
		$blocks = $this->force_navigation_block_ref_default( $blocks, $default_nav_id, $changed );
		if ( ! $changed ) {
			return;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'wp_template_part',
				'post_status'  => 'publish',
				'post_title'   => 'Header',
				'post_name'    => 'header',
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, 'wp_template_part_area', 'header' );

		if ( taxonomy_exists( 'wp_theme' ) ) {
			$theme_slug = get_stylesheet();
			if ( $theme_slug ) {
				wp_set_object_terms( $post_id, array( $theme_slug ), 'wp_theme', false );
			}
		}
	}

	/**
	 * Force core/navigation refs to the default nav id.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function force_navigation_block_ref_default( array $blocks, int $default_nav_id, bool &$changed ) : array {
		foreach ( $blocks as $index => $block ) {
			if ( isset( $block['blockName'] ) && 'core/navigation' === $block['blockName'] ) {
				$old_id = isset( $block['attrs']['ref'] ) ? (int) $block['attrs']['ref'] : 0;
				if ( $old_id !== $default_nav_id ) {
					$blocks[ $index ]['attrs']['ref'] = $default_nav_id;
					$changed = true;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->force_navigation_block_ref_default(
					$block['innerBlocks'],
					$default_nav_id,
					$changed
				);
			}
		}

		return $blocks;
	}

	/**
	 * Check if navigation post exists.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function is_valid_navigation_post( int $post_id ) : bool {
		$post = get_post( $post_id );
		return $post instanceof \WP_Post && 'wp_navigation' === $post->post_type;
	}

	/**
	 * Rebuild navigation links after pages are imported.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function resync_gutenberg_navigation_links() {
		if ( ! post_type_exists( 'wp_navigation' ) ) {
			return;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'wp_navigation',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'suppress_filters' => true,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		$default_nav_id = 0;
		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}
			if ( 0 === $default_nav_id && 'menu' === $post->post_name ) {
				$default_nav_id = (int) $post->ID;
			}

			$blocks = parse_blocks( $post->post_content );
			$changed = false;
			$blocks = $this->map_navigation_link_targets( $blocks, $changed );
			if ( $changed ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => serialize_blocks( $blocks ),
					)
				);
			}
		}

		if ( $default_nav_id ) {
			$this->update_gutenberg_navigation_references( array(), $default_nav_id, array() );
			$this->update_gutenberg_navigation_header_template_parts( $default_nav_id );
		}
	}

	/**
	 * Force-sync navigation refs and links for frontend rendering.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function force_sync_gutenberg_navigation_frontend( int $preferred_nav_id = 0 ) {
		if ( ! post_type_exists( 'wp_navigation' ) ) {
			return;
		}

		$nav_posts = get_posts(
			array(
				'post_type'      => 'wp_navigation',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'suppress_filters' => true,
			)
		);

		if ( empty( $nav_posts ) ) {
			return;
		}

		$default_nav_id = $preferred_nav_id;
		foreach ( $nav_posts as $nav_post ) {
			if ( ! $nav_post instanceof \WP_Post ) {
				continue;
			}
			if ( 0 === $default_nav_id && 'menu' === $nav_post->post_name ) {
				$default_nav_id = (int) $nav_post->ID;
			}

			$blocks = parse_blocks( $nav_post->post_content );
			$changed = false;
			$blocks = $this->map_navigation_link_targets( $blocks, $changed );
			if ( $changed ) {
				wp_update_post(
					array(
						'ID'           => $nav_post->ID,
						'post_content' => serialize_blocks( $blocks ),
					)
				);
			}
		}

		if ( $default_nav_id ) {
			$this->update_gutenberg_navigation_references( array(), $default_nav_id, array() );
		}
	}

	private function sync_gutenberg_html_templates() {
		$theme_dir = get_stylesheet_directory();
		$parent_dir = get_template_directory();
		$source_dir = $theme_dir . '/templates-gutenberg';
		if ( ! is_dir( $source_dir ) && $parent_dir && $parent_dir !== $theme_dir ) {
			$parent_source = $parent_dir . '/templates-gutenberg';
			if ( is_dir( $parent_source ) ) {
				$source_dir = $parent_source;
			}
		}
		$target_root = $parent_dir ? $parent_dir : $theme_dir;
		$target_dir = $target_root . '/templates';

		if ( ! is_dir( $source_dir ) ) {
			return;
		}

		if ( ! is_dir( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		$imported = get_option( 'et_imported_data', array() );
		if ( ! is_array( $imported ) ) {
			$imported = array();
		}

		if ( ! isset( $imported['gutenberg_html_templates'] ) || ! is_array( $imported['gutenberg_html_templates'] ) ) {
			$imported['gutenberg_html_templates'] = array();
		}

		$files = glob( $source_dir . '/*.html' );
		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$filename = basename( $file );
				$target = $target_dir . '/' . $filename;

				if ( ! file_exists( $target ) || md5_file( $file ) !== md5_file( $target ) ) {
					copy( $file, $target );
				}

				if ( ! in_array( $filename, $imported['gutenberg_html_templates'], true ) ) {
					$imported['gutenberg_html_templates'][] = $filename;
				}
			}
		}

		$theme_json = $source_dir . '/theme.json';
		if ( ! file_exists( $theme_json ) && $parent_dir && $parent_dir !== $theme_dir ) {
			$parent_theme_json = $parent_dir . '/templates-gutenberg/theme.json';
			if ( file_exists( $parent_theme_json ) ) {
				$theme_json = $parent_theme_json;
			}
		}
		if ( file_exists( $theme_json ) ) {
			$theme_json_target = $target_root . '/theme.json';
			if ( ! file_exists( $theme_json_target ) || md5_file( $theme_json ) !== md5_file( $theme_json_target ) ) {
				copy( $theme_json, $theme_json_target );
			}
			$imported['gutenberg_theme_json'] = 'theme.json';
		}

		update_option( 'et_imported_data', $imported );
	}

	/**
	 * Reinitialize FSE-related state after templates/theme.json import.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function reinit_fse_theme_state() {
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}
		if ( function_exists( 'wp_clean_themes_cache' ) ) {
			wp_clean_themes_cache( true );
		}
		if ( function_exists( 'wp_clean_plugins_cache' ) ) {
			wp_clean_plugins_cache( true );
		}
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		add_filter( 'etheme_is_fse_theme', '__return_true', 1000 );
	}

	/**
	 * Ensure Gutenberg template part post type/taxonomies exist for import.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function ensure_gutenberg_template_part_support() {
		if ( ! post_type_exists( 'wp_template_part' ) ) {
			register_post_type(
				'wp_template_part',
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'supports'     => array( 'title', 'editor', 'excerpt', 'revisions' ),
					'rewrite'      => false,
					'query_var'    => false,
				)
			);
		}

		if ( ! taxonomy_exists( 'wp_theme' ) ) {
			register_taxonomy(
				'wp_theme',
				array( 'wp_template_part' ),
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'rewrite'      => false,
				)
			);
		}

		if ( ! taxonomy_exists( 'wp_template_part_area' ) ) {
			register_taxonomy(
				'wp_template_part_area',
				array( 'wp_template_part' ),
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'rewrite'      => false,
				)
			);
		}
	}

	/**
	 * Ensure Gutenberg global styles post type/taxonomy exists for import.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function ensure_gutenberg_global_styles_support() {
		if ( ! post_type_exists( 'wp_global_styles' ) ) {
			register_post_type(
				'wp_global_styles',
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'supports'     => array( 'title', 'editor', 'revisions' ),
					'rewrite'      => false,
					'query_var'    => false,
				)
			);
		}

		if ( ! taxonomy_exists( 'wp_theme' ) ) {
			register_taxonomy(
				'wp_theme',
				array( 'wp_global_styles' ),
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'rewrite'      => false,
				)
			);
		}
	}

	/**
	 * Ensure Gutenberg navigation post type exists for import.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function ensure_gutenberg_navigation_support() {
		if ( ! post_type_exists( 'wp_navigation' ) ) {
			register_post_type(
				'wp_navigation',
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'supports'     => array( 'title', 'editor', 'excerpt', 'revisions' ),
					'rewrite'      => false,
					'query_var'    => false,
				)
			);
		}
	}

	/**
	 * Ensure Gutenberg templates post type/taxonomy exists for import.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function ensure_gutenberg_templates_support() {
		if ( ! post_type_exists( 'wp_template' ) ) {
			register_post_type(
				'wp_template',
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'supports'     => array( 'title', 'editor', 'excerpt', 'revisions' ),
					'rewrite'      => false,
					'query_var'    => false,
				)
			);
		}

		if ( ! taxonomy_exists( 'wp_theme' ) ) {
			register_taxonomy(
				'wp_theme',
				array( 'wp_template' ),
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => true,
					'rewrite'      => false,
				)
			);
		}
	}

	/**
	 * Reset Gutenberg-related caches/assets.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function reset_gutenberg_assets() {
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}

		if ( function_exists( 'wp_clean_plugins_cache' ) ) {
			wp_clean_plugins_cache( true );
		}

		if ( function_exists( 'wp_clean_themes_cache' ) ) {
			wp_clean_themes_cache( true );
		}

		if ( function_exists( 'wp_clean_update_cache' ) ) {
			wp_clean_update_cache();
		}

		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}

	public static function add_svg_support( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Generates random id before inserting content.
	 *
	 * @param $content
	 * @return mixed
	 *
	 * @since 4.1
	 *
	 */
	protected function make_content_unique( $content ) {
		return \Elementor\Plugin::instance()->db->iterate_data( $content, function ( $element ) {
			$element['id'] = \Elementor\Utils::generate_random_string();

			return $element;
		} );
	}

	/**
	 * Install default WooCommerce pages.
	 *
	 * @since   7.1.1
	 * @version 1.0.0
	 *
	 */
	public function default_woocommerce_pages(){
		$shop_page = $this->get_page_by_title_or_slug(
			array( 'Shop ' . $this->version, 'Shop', 'shop' ),
			array( 'shop' )
		);
		$cart_page = $this->get_page_by_title_or_slug(
			array( 'Cart ' . $this->version, 'Cart', 'cart' ),
			array( 'cart' )
		);
		$checkout_page = $this->get_page_by_title_or_slug(
			array( 'Checkout ' . $this->version, 'Checkout', 'checkout' ),
			array( 'checkout' )
		);
		$my_account = $this->get_page_by_title_or_slug(
			array( 'my account', 'My account', 'My Account' ),
			array( 'my-account', 'my-account-2' )
		);

		if ($shop_page){
			update_option( 'woocommerce_shop_page_id', $shop_page->ID );
		}
		if ($cart_page){
			update_option( 'woocommerce_cart_page_id', $cart_page->ID );
		}
		if ($checkout_page){
			update_option( 'woocommerce_checkout_page_id', $checkout_page->ID);
		}
		if ($my_account){
			update_option( 'woocommerce_myaccount_page_id',$my_account->ID );
		}

		$this->update_on_sale_products();

//		update_option('et_theme_builder_wc_install_done', 'yes');
	}

	/**
	 * Get version info.
	 *
	 * @since   7.1.0
	 * @version 1.0.3
	 *
	 */
	public function get_version_info(){

		$Customizer = Customizer::get_instance( 'ETC\App\Models\Customizer' );
		$Customizer->customizer_style('kirki-styles');

		$Etheme_Customize_header_Builder = new \Etheme_Customize_header_Builder();
		$Etheme_Customize_header_Builder->generate_header_builder_style('all');

		$Etheme_Customize_header_Builder = new \Etheme_Customize_header_Builder();
		$Etheme_Customize_header_Builder->generate_single_product_style('all');

		$data = wp_remote_get($this->generate_url());
		$code = wp_remote_retrieve_response_code($data);

		if( ! is_wp_error( $data ) && $code == '200' ) {
			$data = wp_remote_retrieve_body($data);
			$verified = json_decode($data,true);
			$verified['domain'] = $this->get_domain();
			update_option('etheme_current_version',$data, 'no');
			wp_send_json($verified);
		} else {
			$data = false;
		}
		return true;
	}

	/**
	 * Generate url
	 *
	 * @since   3.2.4
	 * @version 1.0.0
	 *
	 * @return string url
	 */
	public function generate_url(){
		$errors = isset($_POST['errors']) ? $_POST['errors'] : null;
		$base = $this->import_url . '1/versions/';
		return add_query_arg(
			array(
				'etheme_version_info' => $this->version,
				'etheme_engine' => $this->engine,
				'errors' => json_encode($errors),
				'system' => json_encode($this->get_system()),
				'pkg' => json_encode(
					array(
						'theme'=> ETHEME_THEME_VERSION,
						'plugin' => ET_CORE_VERSION,
						'code' => $this->get_code(),
						'domain' => $this->get_domain()
					)
				),
			),
			$base );
	}

	/**
	 * Get domain
	 *
	 * @since   3.2.5
	 * @version 1.0.0
	 *
	 * @return string domain
	 */
	public function get_domain() {
		$domain = get_option('siteurl'); //or home
		$domain = str_replace('http://', '', $domain);
		$domain = str_replace('https://', '', $domain);
		$domain = str_replace('www', '', $domain); //add the . after the www if you don't want it
		return urlencode($domain);
	}

	/**
	 * Get system status
	 *
	 * @since   3.2.4
	 * @version 1.0.0
	 *
	 * @return array system status
	 */
	public function get_system(){
		$system = '';
		if ( class_exists('Etheme_System_Requirements') ) {
			$system = new \Etheme_System_Requirements();
			$system = $system->get_system(true);
		} elseif( defined('ETHEME_CODE') && is_user_logged_in() && current_user_can('administrator') ) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'system-requirements.php') );

			$system = new \Etheme_System_Requirements();
			$system = $system->get_system(true);
		}
		$system['wp_uploads'] = wp_is_writable( $system['wp_uploads']['basedir'] );
		unset($system['curl_version']);
		return $system;
	}

	/**
	 * Get code
	 *
	 * @since   3.2.4
	 * @version 1.0.0
	 *
	 * @return string code
	 */
	public function get_code(){
		$activated_data = get_option( 'etheme_activated_data' );
		$activated_data = ( isset( $activated_data['purchase'] ) && ! empty( $activated_data['purchase'] ) ) ? $activated_data['purchase'] : '';
		return $activated_data;
	}

	/**
	 * Generate remote url.
	 *
	 * @since   2.3.2
	 * @version 1.1.0
	 *
	 * @param  string $file name of file with extension
	 * @return string       path to file
	 */
	public function generate_remote_url($file){
		if (
			$this->engine == 'elementor'
			&& in_array('elementor_builders', $this->versions[ $this->version ]['engine'])
		){
			$this->engine = 'elementor_builders';
		}
		return $this->import_url . $this->version . '/' . $this->engine . '/' . $this->get_file_name($file) . '?code=' . $this->get_code();
	}

	/**
	 * Get remote data.
	 *
	 * @since   2.3.2
	 * @version 1.0.2
	 *
	 * @param  string     $file name of file with extension
	 * @param  bool       $json use or not json_decode
	 * @return array|bool $data array with data|false if fail
	 */
	public function get_remote_data($file, $json=true){
		add_filter( 'http_request_args', 'et_increase_http_request_timeout', 10, 2 );
		$url = $this->generate_remote_url($file);
		$data = wp_remote_get($url);
		$code = wp_remote_retrieve_response_code($data);

		// write_log([
		// 	'url' => $this->generate_remote_url($file),
		// 	'file' => $file,
		// 	'code' => $code,
		// 	//'data' => $data,
		// ]);

		if( ! is_wp_error( $data ) && $code == '200' ) {
			$data = wp_remote_retrieve_body($data);
			if ($json){
				$data = json_decode($data, true);
			}
		} else {
			if ( is_wp_error( $data ) ) {
				$this->set_import_error(
					'Failed to connect to the demo server.',
					$data->get_error_message(),
					'remote_connection_error'
				);
			} else {
				if($code == 404){
					wp_send_json(array(
						'status'  => 'installed',
						'message' => '',
						'details' => '',
					));
				}
				$this->set_import_error(
					'The demo server returned an unexpected response.',
					sprintf( 'Request to %s returned HTTP %s.', esc_url_raw( $url ), $code ? $code : 'unknown' ),
					'remote_http_error'
				);
			}
			$data = false;
		}
		remove_filter( 'http_request_args', 'et_increase_http_request_timeout', 10, 2 );
		return $data;
	}

	/**
	 * Get file name.
	 *
	 * @since   2.3.2
	 * @version 1.0.1
	 *
	 * @param  string      $file name of import type
	 * @return string|bool file name with extension|false if no file
	 */
	public function get_file_name($file){
		$files = array(
			'fonts'=> 'fonts.json',
			'brand'=> 'brands.json',
			'product_cat'=> 'product_cats.json',
			'variation_taxonomy'=> 'product_variation_terms.json',
			'variations_trems'=> 'product_variation_terms.json',
			'variation_products'=> 'data_product_variations.json',
			'et_multiple_conditions'=> 'multiple_header_conditions.json',
			'et_multiple_headers'=> 'multiple_header_templates.json',
			'et_multiple_single_product_conditions'=> 'multiple_single_product_conditions.json',
			'et_multiple_single_product'=> 'multiple_single_product_templates.json',
			'elementor_globals'=> 'elementor_defaults.json',
			'elementor_sections'=> 'elementor_sections.json',
			'elementor_single_products'=> 'elementor_single_products.json',
			'elementor_archives'=> 'elementor_archives.json',
			'elementor_footers'=> 'elementor_footers.json',
			'elementor_headers' => 'elementor_headers.json',
			'elementor_post_archive' => 'elementor_post_archives.json',
			'elementor_post' => 'elementor_posts.json',
			'mega-menus' => 'mega-menus.xml',
			'contact-forms'=> 'contact-forms.xml',
			'coupons'=> 'coupons.xml',
			'grid-builder'=> 'grid-builder.xml',
			'mailchimp'=> 'mailchimp.xml',
			'media'=> 'media.xml',
			'menu'=> 'menu.xml',
			'orders'=> 'orders.xml',
			'pages'=> 'pages.xml',
			'posts'=> 'posts.xml',
			'products'=> 'products.xml',
			'projects'=> 'projects.xml',
			'refunds'=> 'refunds.xml',
			'smart-product'=> 'smart-product.xml',
			'static-blocks'=> 'static-blocks.xml',
			'testimonials'=> 'testimonials.xml',
			'variations'=> 'variations.xml',
			'vc-templates'=> 'vc-templates.xml',
			'widgets'=> 'widgets.json',
			'options'=> 'options.dat',
			'slider'=> 'slider.zip',
			'slider1'=> 'slider1.zip',
			'slider2'=> 'slider2.zip',
			'slider3'=> 'slider3.zip',
			'slider4'=> 'slider4.zip',
			'slider5'=> 'slider5.zip',
			'slider6'=> 'slider6.zip',
			'etheme_slides' => 'slides.xml',
			'content-presets'=> 'content-presets.json',
			'sales_boosters' => 'sales_boosters.json',
			'elementor_secure_widgets' =>  'widgets.json',
			'global_settings' => 'global_settings.json',
			'gutenberg_patterns' => 'gutenberg-patterns.json',
			'gutenberg_template_parts' => 'gutenberg-template-parts.json',
			'gutenberg_styles' => 'gutenberg-styles.json',
			'gutenberg_navigation' => 'gutenberg-navigation.json',
			'gutenberg_templates' => 'gutenberg-templates.json',
			'gutenberg_site_logo' => 'gutenberg-site-logo.json',
		);
		return ( isset($files[$file]) ) ? $files[$file] : false;
	}

	/**
	 * Init builders for custom post types.
	 *
	 * @since   4.3.5
	 * @version 1.0.1
	 * 1.0.1
	 * - ADDED:
	 * -- elementor_clear_cache
	 * -- deactivate_unused_elementor_widgets
	 *
	 * @return string|bool file name with extension|false if no file
	 */
	public function init_builders(){
		if ( $this->engine === 'gutenberg' ) {
			$this->reset_gutenberg_assets();
			$this->resync_gutenberg_navigation_links();
			$this->sync_gutenberg_html_templates();
		}

		if (defined( 'ELEMENTOR_VERSION' )){
			$elementor_cpt_support = get_option( 'elementor_cpt_support' );
			if (!is_array($elementor_cpt_support)){
				$elementor_cpt_support = array('post', 'page', 'staticblocks', 'etheme_slides', 'testimonials', 'etheme_portfolio', 'product', 'etheme_mega_menus');
			} else {
				$elementor_cpt_support[] = 'staticblocks';
				$elementor_cpt_support[] = 'etheme_slides';
				$elementor_cpt_support[] = 'etheme_mega_menus';
				$elementor_cpt_support[] = 'testimonials';
				$elementor_cpt_support[] = 'etheme_portfolio';
				$elementor_cpt_support[] = 'product';
			}
			update_option('elementor_cpt_support', $elementor_cpt_support);
			// Activate All widgets
			update_option( 'elementor_disabled_elements', array() );
			do_action( 'elementor/element_manager/save_disabled_elements' );

			$this->elementor_clear_cache();
			$ignore_widgets = (isset($this->versions[$this->version ][ 'ignore_widgets'])) ? $this->versions[$this->version ][ 'ignore_widgets'] : false;

			if (!$ignore_widgets){
				$this->deactivate_unused_elementor_widgets();
			}
		}

		if (defined( 'WPB_VC_VERSION' )){
			if (!function_exists('get_editable_roles')) {
				require_once(ABSPATH . '/wp-admin/includes/user.php');
			}

			if (!class_exists('Vc_Roles')){
				require_once vc_path_dir( 'SETTINGS_DIR', 'class-vc-roles.php' );
			}
			$roles = new \Vc_Roles();
			$roles->save(
				array(
					'administrator' => json_encode(array(
						"post_types"      => [
							"_state"            => "custom",
							"post"              => "1",
							"page"              => "1",
							"e-landing-page"    => "0",
							"elementor_library" => "0",
							"staticblocks"      => "1",
							"testimonials"      => "1",
							"etheme_portfolio"  => "1",
							"product"           => "1"
						],
						"backend_editor"  => [
							"_state"             => "1",
							"disabled_ce_editor" => "0"
						],
						"frontend_editor" => [
							"_state" => "1"
						],
						"unfiltered_html" => [
							"_state" => "1"
						],
						"post_settings"   => [
							"_state" => "1"
						],
						"templates"       => [
							"_state" => "1"
						],
						"shortcodes"      => [
							"_state" => "1"
						],
						"grid_builder"    => [
							"_state" => "1"
						],
						"presets"         => [
							"_state" => "1"
						],
						"dragndrop"       => [
							"_state" => "1"
						]
					)),
					'editor' => json_encode(array(
						"post_types"      => [
							"_state"            => "custom",
							"post"              => "1",
							"page"              => "1",
							"e-landing-page"    => "0",
							"elementor_library" => "0",
							"staticblocks"      => "1",
							"testimonials"      => "1",
							"etheme_portfolio"  => "1",
							"product"           => "1"
						],
						"backend_editor"  => [
							"_state"             => "1",
							"disabled_ce_editor" => "0"
						],
						"frontend_editor" => [
							"_state" => "1"
						],
						"unfiltered_html" => [
							"_state" => "1"
						],
						"post_settings"   => [
							"_state" => "1"
						],
						"templates"       => [
							"_state" => "1"
						],
						"shortcodes"      => [
							"_state" => "1"
						],
						"grid_builder"    => [
							"_state" => "1"
						],
						"presets"         => [
							"_state" => "1"
						],
						"dragndrop"       => [
							"_state" => "1"
						]
					))
				)
			);
		}
		return true;
	}

	/**
	 * Get page object by page title
	 *
	 * @since   5.1.9
	 * @version 1.0.0
	 *
	 *
	 * @return object|null post object if post find or null if it not
	 */
	public function get_page_by_title($page_title){
		global $wpdb;

		$sql = $wpdb->prepare(
			"
			SELECT ID
			FROM $wpdb->posts
			WHERE post_title = %s
			AND post_type = 'page'
		",
			$page_title
		);

		$page = $wpdb->get_var( $sql );

		if ( $page ) {
			return get_post( $page, OBJECT );
		}

		return null;
	}

	private function get_page_by_title_or_slug( array $titles, array $slugs = array() ) {
		foreach ( $titles as $title ) {
			if ( ! is_string( $title ) || '' === trim( $title ) ) {
				continue;
			}
			$page = $this->get_page_by_title( $title );
			if ( $page ) {
				return $page;
			}
		}

		foreach ( $slugs as $slug ) {
			if ( ! is_string( $slug ) || '' === trim( $slug ) ) {
				continue;
			}
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $page ) {
				return $page;
			}
		}

		return null;
	}

	/**
	 * Update On Sale products
	 *
	 * @since   5.3.0
	 * @version 1.0.0
	 *
	 */
	public function update_on_sale_products(){
		$isd = et_wc_get_product_ids_on_sale();
		if ($isd && is_array($isd)){
			delete_transient('wc_products_onsale');
			set_transient( 'wc_products_onsale', $isd, DAY_IN_SECONDS * 30 );
		}
	}

	/**
	 * Clear elementor page cache
	 *
	 * @since   5.5.0
	 * @version 1.0.0
	 *
	 */
	public function elementor_clear_cache() {
		if (defined( 'ELEMENTOR_VERSION' )){
			\Elementor\Plugin::$instance->files_manager->clear_cache();
			// \Elementor\Plugin::$instance->assets_manager->clear_cache();
			// \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend(true);
			// delete_option('_elementor_css_files');
			$this->clear_frontend_cache();
		}
	}

	/**
	 * Clear frontend page cache
	 *
	 * @since   5.5.0
	 * @version 1.0.0
	 *
	 */
	private function clear_frontend_cache(){
		wp_remote_get( get_home_url() );
	}

	/**
	 * Regenerate missing attachment sizes after Gutenberg import.
	 *
	 * @since   9.3.0
	 * @version 1.0.0
	 */
	private function regenerate_missing_attachment_sizes() {
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_wp_attachment_metadata',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( empty( $attachments ) ) {
			return;
		}

		foreach ( $attachments as $attachment_id ) {
			$file = get_attached_file( $attachment_id );
			if ( ! $file || ! file_exists( $file ) ) {
				continue;
			}
			$metadata = wp_generate_attachment_metadata( $attachment_id, $file );
			if ( $metadata ) {
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}
		}
	}

	/**
	 * Force scan elementor usage if recalc_usage - stop working
	 *
	 * @since   5.5.0
	 * @version 1.0.0
	 *
	 */
	public function force_scan_elementor_usage() {
		if ( ! class_exists('\Elementor\Modules\Usage\Module') ) return;
		$usage_module = \Elementor\Modules\Usage\Module::instance();
		$usage_module->recalc_usage();

		$widgets_usage = [];

		foreach ( $usage_module->get_formatted_usage('raw') as $data ) {
			foreach ( $data['elements'] as $element => $count ) {
				if ( ! isset( $widgets_usage[ $element ] ) ) {
					$widgets_usage[ $element ] = 0;
				}
				$widgets_usage[ $element ] += $count;
			}
		}
	}

	/**
	 * Scan for all elementor widgets
	 *
	 * @since   5.5.0
	 * @version 1.0.0
	 *
	 */
	public function scan_elementor_widgets($elements, &$used) {
		foreach ($elements as $element) {
			if (isset($element['elType']) && $element['elType'] === 'widget' && isset($element['widgetType'])) {
				$used[] = $element['widgetType'];
			}
			if (!empty($element['elements'])) {
				$this->scan_elementor_widgets($element['elements'], $used);
			}
		}
	}

	/**
	 * Deactivate unused widgets for elementor
	 *
	 * @since   5.5.0
	 * @version 1.0.0
	 *
	 */
	public function deactivate_unused_elementor_widgets() {
		$used_widgets  = [];
		$all_widgets_k = [];
		$to_remove 	   = [];

		// Global protected widgets
		$global_protected = $this->get_global_data('elementor_secure_widgets');

		// Elementor protected widgets if deactivate - fatal error
		$protected = array(
			'common',
			'common-base',
			'common-optimized',
			// 'inner-section',
			'global',
			'template'
		);

		// Get widgets from posts, pages, etc...
		$posts = get_posts([
			'post_type' => [
				'page',
				'post',
				'staticblocks',
				'etheme_slides',
				'etheme_mega_menus',
				'elementor_library',
				'product',
				'testimonials',
				'etheme_portfolio'
			],
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields' => 'ids',
			'meta_key' => '_elementor_data',
		]);

		foreach ($posts as $post_id) {
			$data = get_post_meta($post_id, '_elementor_data', true);
			$elements = json_decode($data, true);

			// Scan elements
			if (is_array($elements)) {
				$this->scan_elementor_widgets($elements, $used_widgets);
			}
		}

		// Get used widgets, only after Scan
		$used_widgets = array_unique($used_widgets);
		// Get all widgets
		$all_widgets = \Elementor\Plugin::$instance->widgets_manager->get_widget_types();
		$usage_module = \Elementor\Modules\Usage\Module::instance();

		foreach ( $all_widgets as $kay => $_widget ) {
			$all_widgets_k[] = $kay;
		}

		// Setup widgets to remove
		foreach ($all_widgets_k as $key => $value) {
			if (
				! in_array($value, $used_widgets)
				&& ! in_array($value, $protected)
				&& ! in_array($value, $global_protected)
			) {
				$to_remove[] = $value;
			}
		}

		if (count($to_remove )) {
			// Remove unused widgets and recalc_usage
			update_option( 'elementor_disabled_elements', $to_remove );
			do_action( 'elementor/element_manager/save_disabled_elements' );
			$usage_module->recalc_usage();
			// Uncomit if recalc_usage - stop working
			// $this->et_force_scan_elementor_usage();
		}
	}

	/**
	 * Get global import data
	 *
	 * @since   5.5.0
	 * @version 1.0.0
	 *
	 */
	private function get_global_data($data){
		$url = $this->import_url . 'globals/' . $this->get_file_name($data) . '?code=' . $this->get_code();
		$remote_data = array();

		$response = wp_remote_get($url, [
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
				'Accept' => 'application/json',
			],
			'timeout' => 20,
		]);

		if ( ! is_wp_error($response) ) {
			$body = wp_remote_retrieve_body($response);
			$remote_data = json_decode($body, true);
		}
		return $remote_data;
	}

	/**
	 * Disable elementor atomic edithor
	 *
	 * @since   5.7.8
	 * @version 1.0.0
	 *
	 */
	private function disable_elementor_atomic_editor() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		if (
			! apply_filters(
				'etheme_disable_elementor_atomic_elements_on_import',
				true,
				$this->version,
				$this->engine
			)
		) {
			return;
		}

		$experiments = \Elementor\Plugin::$instance->experiments;
		$features    = array(
			'e_opt_in_v4',
			'e_atomic_elements',
			'e_classes',
			'e_variables',
			'e_components',
		);

		foreach ( $features as $feature ) {
			update_option(
				$experiments->get_feature_option_key( $feature ),
				\Elementor\Core\Experiments\Manager::STATE_INACTIVE
			);
		}
	}
}
