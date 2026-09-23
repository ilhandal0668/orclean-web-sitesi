<?php
namespace ETC\App\Controllers\Admin;

use ETC\App\Controllers\Admin\Base_Controller;
use ETC\App\Controllers\Customizer;

/**
 * Remove controller.
 *
 * @since
 * @package    ETC
 * @subpackage ETC/Controller
 */
class Remove extends Base_Controller {

	// ! Declare default variables
	private $allow_types = array(
		'page',
		'product',
		'post',
		'testimonials',
		'project',
		'etheme_portfolio',
		'staticblocks',
        'etheme_slides',
		'wpcf7_contact_form',
		'mc4wp-form',
		'attachment',
		'options',
		'widgets',
		'widget_areas',
		'menu',
		'elementor_templates-footer',
		'elementor_templates-header',
		'elementor_templates-product-archive',
		'elementor_templates-product',
		'elementor_templates-archive',
		'elementor_templates-single-post',
		'elementor_xstore-builders',
		'etheme_mega_menus',
		'elementor_templates-section',
		'etheme_terms',
		'vc_grid_item',
		'gutenberg_html_templates',
		'gutenberg_templates',
	);
	private $imported_data = array();
	private $type = '';
	private $option = 'et_imported_data';
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
		add_action('wp_ajax_etheme_remove_ajax', array($this, 'remove_data'));
	}

	public function remove_data(){

		check_ajax_referer('etheme_remove_content-nonce', 'security');

		if (!isset($_POST['type']) || empty($_POST['type'])) wp_send_json_error(array('msg'=>'ERROR: remove error 1'));

		if (!in_array($_POST['type'], $this->allow_types)) wp_send_json_error(array('msg'=>'ERROR: remove error 2'));

		$this->imported_data = get_option($this->option, array());
		$this->type = $_POST['type'];

        switch ($this->type) {
            case 'attachment':
                $this->remove_attachment();
                break;
            case 'revslider':
                $this->remove_revslider();
                break;
            case 'options':
                $this->remove_options();
                break;
            case 'menu':
                $this->remove_menu();
                break;
            case 'widgets':
                $this->remove_widget();
                 break;
            case 'widget_areas':
                $this->remove_widget_areas();
                break;
            case 'etheme_portfolio':
            case 'project':
                $this->remove_project();
                break;
	        case 'elementor_xstore-builders':
		        $this->remove_builders();
		        break;
            case 'etheme_terms':
                $this->remove_terms();
                break;
            case 'gutenberg_html_templates':
                $this->remove_gutenberg_html_templates();
                wp_send_json_success(array());
                break;
            case 'gutenberg_templates':
                $this->remove_gutenberg_imported_templates();
                wp_send_json_success(array());
                break;
            default:
                $stylesheet_theme_json = get_stylesheet_directory() . '/theme.json';
                $template_theme_json = get_template_directory() . '/theme.json';
                if ( file_exists( $stylesheet_theme_json ) || file_exists( $template_theme_json ) ) {
					$this->remove_post(false);
                    $this->remove_gutenberg_imports();
                } else {
					$this->remove_post(true);
				}
                break;
        }

		wp_send_json_error(array('msg' => 'ERROR: remove error 4!'));
	}


	private function remove_builders(){
		$return = array();
		foreach (array('elementor_templates-footer',
			'elementor_templates-header',
			'elementor_templates-product-archive',
			'elementor_templates-product', 'elementor_templates-section', 'elementor_templates-archive', 'elementor_templates-single-post', 'elementor_templates-single') as $value){
			if (isset($this->imported_data[$value])){
				$posts = $this->imported_data[$value];
				foreach ($posts as $kay => $post){
					if (is_wp_error(wp_delete_post($post, true))){
						$return['not-removed'][$value][] = 'ERROR: Can not remove '. $value . ' ' .$post. ' !';
					} else {
						unset($this->imported_data[$value][$kay]);
					}
				}
				update_option($this->option, $this->imported_data);
			}
		}

		wp_send_json_success($return);
	}

	private function remove_post($send_response = true){
		$return = array();
		if (isset($this->imported_data[$this->type])){
			$posts = $this->imported_data[$this->type];
			foreach ($posts as $kay => $post){
				if (is_wp_error(wp_delete_post($post, true))){
					$return['not-removed'][$this->type][] = 'ERROR: Can not remove '. $this->type . ' ' .$post. ' !';
				} else {
					unset($this->imported_data[$this->type][$kay]);
				}
			}
			update_option($this->option, $this->imported_data);
			if ( $send_response ) {
				wp_send_json_success($return);
			}
		}
		if ( $send_response ) {
			wp_send_json_success($return);
		}
		//wp_send_json_error(array('msg' => 'ERROR: thea no ' .$this->type. ' imported!'));
		return $return;
	}
	private function remove_project(){
		$return = array();
		if (isset($this->imported_data['etheme_portfolio'])){
			$posts = $this->imported_data['etheme_portfolio'];
			foreach ($posts as $kay => $post){
				if (is_wp_error(wp_delete_post($post, true))){
					$return['not-removed']['etheme_portfolio'][] = 'ERROR: Can not remove '. $this->type . ' ' .$post. ' !';
				} else {
					unset($this->imported_data['etheme_portfolio'][$kay]);
				}
			}
			update_option($this->option, $this->imported_data);
			wp_send_json_success($return);
		}
		wp_send_json_success($return);
	}
	private function remove_attachment(){
		$return = array();
		if (isset($this->imported_data[$this->type])){
			$posts = $this->imported_data[$this->type];
			foreach ($posts as $kay => $post){
				if (is_wp_error(wp_delete_attachment($post, true))){
					$return['not-removed'][$this->type][] = 'ERROR: Can not remove '. $this->type . ' ' .$post. ' !';
				} else {
					unset($this->imported_data[$this->type][$kay]);
				}
			}
			update_option($this->option, $this->imported_data);
			wp_send_json_success($return);
		}
		wp_send_json_success($return);
		//wp_send_json_error(array('msg' => 'ERROR: thea no image imported!'));
	}
	private function remove_options(){
		// remove theme options
		$theme = get_option( 'stylesheet' );
		delete_option('theme_mods_'.$theme);

		delete_option('et_multiple_headers');
		delete_option('et_multiple_single_product');
		delete_option('etheme_single_product_builder');
		delete_option('etheme_disable_customizer_header_builder');
		get_option('versions_imported');
		// regenerete css
		$Customizer = Customizer::get_instance( 'ETC\App\Models\Customizer' );
		$Customizer->customizer_style('kirki-styles');

		$Etheme_Customize_header_Builder = new \Etheme_Customize_header_Builder();
		$Etheme_Customize_header_Builder->generate_header_builder_style('all');

		$Etheme_Customize_header_Builder = new \Etheme_Customize_header_Builder();
		$Etheme_Customize_header_Builder->generate_single_product_style('all');

		wp_send_json_success(array());
	}
	private function remove_menu(){
		$menus = $this->imported_data[$this->type];
		foreach ($menus as $kay => $menu){
			wp_delete_nav_menu($menu);
			unset($this->imported_data[$this->type][$kay]);
		}

		wp_delete_nav_menu('currencies');
		wp_delete_nav_menu('languages');

		update_option($this->option, $this->imported_data);
		wp_send_json_success(array());
	}
	private function remove_widget(){
		$widgets = $this->imported_data[$this->type];
		$active_widgets = get_option( 'sidebars_widgets' );
		foreach ($widgets as $kay => $widget){
			delete_option(  'widget_' . $widget );
			unset($this->imported_data[$this->type][$kay]);
		}
		update_option($this->option, $this->imported_data);

		$active_widgets = get_option( 'sidebars_widgets' );
		foreach ($active_widgets as $area => $widgets_2){
			if (is_array($widgets_2)){
				foreach ($widgets_2 as $key => $widget_2){
					if (in_array($widget_2, $this->imported_data['widgets-ids'])){
						unset($active_widgets[$area][$key]);
					}
				}
			}
		}
		$this->imported_data['widgets-ids']=array();
		$this->imported_data['multiples']=array();
		update_option( 'sidebars_widgets', $active_widgets );
		update_option($this->option, $this->imported_data);

		wp_send_json_success(array());
	}
	private function remove_widget_areas(){
		delete_option(  'etheme_custom_sidebars' );
		$this->imported_data['widget_areas']=array();

		update_option($this->option, $this->imported_data);
		wp_send_json_success(array());
	}

	private function remove_terms(){
		$return = array();
		foreach (array('brand', 'product_cat') as $value){
			if (isset($this->imported_data[$value])){
				foreach ($this->imported_data[$value] as $kay => $term){
					if (is_wp_error(wp_delete_term($term, $value))){
						$return['not-removed'][$value][] = 'ERROR: Can not remove '. $value . ' ' .$term. ' !';
					} else {
						unset($this->imported_data[$value][$kay]);
					}
				}
				update_option($this->option, $this->imported_data);
			}
		}

		wp_send_json_success($return);
	}

	private function remove_revslider(){
	}

	private function remove_gutenberg_imports() {
		$return = array();

		// $this->remove_imported_posts_by_key('gutenberg_patterns');
		// $this->remove_imported_posts_by_key('gutenberg_template_parts');
		// $this->remove_imported_posts_by_key('gutenberg_styles');
		// $this->remove_imported_posts_by_key('gutenberg_navigation');
		$this->remove_all_gutenberg_navigation_posts();
		$this->remove_gutenberg_site_logo();
		delete_option('etheme_current_version');

		$this->imported_data = get_option( $this->option, array() );
		if ( ! is_array( $this->imported_data ) ) {
			$this->imported_data = array();
		}

		if ( isset( $this->imported_data['gutenberg_html_templates'] ) ) {
			unset( $this->imported_data['gutenberg_html_templates'] );
		}
		if ( isset( $this->imported_data['testimonials'] ) ) {
			unset( $this->imported_data['testimonials'] );
		}

		update_option($this->option, $this->imported_data);
		wp_send_json_success($return);
	}

	private function remove_gutenberg_imported_templates() {
		$return = array();
		$this->remove_imported_posts_by_key('gutenberg_patterns');
		$this->remove_imported_posts_by_key('gutenberg_template_parts');
		$this->remove_imported_posts_by_key('gutenberg_styles');
		$this->remove_imported_posts_by_key('gutenberg_navigation');
		$this->remove_all_gutenberg_navigation_posts();
		$this->remove_imported_posts_by_key('gutenberg_templates');

		return $return;
	}

	private function remove_gutenberg_site_logo() {
		$return = array();
		$logo_id = get_theme_mod('custom_logo');
		if ( $logo_id ) {
			set_theme_mod('custom_logo', 0);
			$return['removed']['gutenberg_site_logo'][] = $logo_id;
		}

		if ( isset($this->imported_data['gutenberg_site_logo']) ) {
			unset($this->imported_data['gutenberg_site_logo']);
			update_option($this->option, $this->imported_data);
		}

		return $return;
	}

	private function remove_gutenberg_html_templates() {
		$return = array();
		$imported = get_option($this->option, array());
		if ( ! is_array( $imported ) ) {
			$imported = array();
		}

		$theme_dir = get_stylesheet_directory();
		$parent_dir = get_template_directory();
		$target_root = $parent_dir ? $parent_dir : $theme_dir;
		$target_dir = $target_root . '/templates';
		$source_dir = $target_root . '/templates-gutenberg';

		$fallback_files = array();
		if ( empty( $imported['gutenberg_html_templates'] ) && is_dir( $source_dir ) ) {
			$source_files = glob( $source_dir . '/*.html' );
			if ( ! empty( $source_files ) ) {
				foreach ( $source_files as $file ) {
					$fallback_files[] = basename( $file );
				}
			}
		}

		$files_to_remove = ! empty( $imported['gutenberg_html_templates'] ) ? $imported['gutenberg_html_templates'] : $fallback_files;
		if ( ! empty( $files_to_remove ) ) {
			foreach ( $files_to_remove as $filename ) {
				$path = $target_dir . '/' . basename( $filename );
				if ( file_exists( $path ) ) {
					if ( @unlink( $path ) ) {
						$return['removed']['gutenberg_html_templates'][] = $filename;
					} else {
						$return['not-removed']['gutenberg_html_templates'][] = 'ERROR: Can not remove ' . $filename . ' !';
					}
				}
			}
			unset( $imported['gutenberg_html_templates'] );
		}

		$should_remove_theme_json = isset( $imported['gutenberg_theme_json'] );
		if ( ! $should_remove_theme_json && is_dir( $source_dir ) && file_exists( $source_dir . '/theme.json' ) ) {
			$should_remove_theme_json = true;
		}

		if ( $should_remove_theme_json ) {
			$theme_json_target = $target_root . '/theme.json';
			if ( file_exists( $theme_json_target ) ) {
				if ( @unlink( $theme_json_target ) ) {
					$return['removed']['gutenberg_theme_json'][] = 'theme.json';
				} else {
					$return['not-removed']['gutenberg_theme_json'][] = 'ERROR: Can not remove theme.json !';
				}
			}
			unset( $imported['gutenberg_theme_json'] );
		}

		update_option( $this->option, $imported );

		return $return;
	}

	private function remove_all_gutenberg_navigation_posts() {
		$return = array();

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
			return $return;
		}

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}
			if ( is_wp_error( wp_delete_post( $post->ID, true ) ) ) {
				$return['not-removed']['gutenberg_navigation'][] = 'ERROR: Can not remove wp_navigation ' . $post->ID . ' !';
			} else {
				$return['removed']['gutenberg_navigation'][] = $post->ID;
			}
		}

		if ( isset( $this->imported_data['gutenberg_navigation'] ) ) {
			unset( $this->imported_data['gutenberg_navigation'] );
			update_option( $this->option, $this->imported_data );
		}

		return $return;
	}

	private function remove_imported_posts_by_key( $key ) {
		$return = array();
		if (isset($this->imported_data[$key])){
			$posts = $this->imported_data[$key];
			foreach ($posts as $kay => $post){
				if (is_wp_error(wp_delete_post($post, true))){
					$return['not-removed'][$key][] = 'ERROR: Can not remove '. $key . ' ' .$post. ' !';
				} else {
					$return['removed'][$key][] = $post;
					unset($this->imported_data[$key][$kay]);
				}
			}
			update_option($this->option, $this->imported_data);
		}

		return $return;
	}
}
