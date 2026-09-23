<?php

use function Avifinfo\read;

class ET_Setup_Wizard {

	private static $instance;

	public $wizard_data = array(
		'max_steps' => '',
		'current_step' => '',
		'version' => '',
		'engine' => '',
		'passed_steps' => ''
	);

	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action('admin_menu', [$this, 'add_menu'], 25);
		add_action('admin_init', function () {
			if (isset($_GET['page']) && $_GET['page'] === 'xstore-setup') {
				$this->maybe_handle_step_redirects();
				$this->render_wizard();
				exit;
			}
		});
	}

	public function add_menu() {
		// if(!get_option('etheme_current_version')){
			add_submenu_page(
				'et-panel-welcome',
				esc_html__( 'Setup Wizard', 'xstore' ),
				esc_html__( 'Setup Wizard', 'xstore' ),
				'manage_options',
				'xstore-setup',
				[$this, 'render_wizard'],
			);
		// }
	}

	public function get_controls_url($step = 'welcome'){
		return admin_url('admin.php?page=xstore-setup&step=' . $step);
	}

	public function render_wizard() {
		$wizard_template_path = get_template_directory() . '/framework/setup-wizard/templates/';

		include $wizard_template_path . 'header.php';
		include $wizard_template_path . 'content.php';
		include $wizard_template_path . 'footer.php';
	}

	private function maybe_handle_step_redirects() {
		$step = isset($_GET['step']) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : 'welcome';
		$is_activated = etheme_is_activated();

		if ( $step === 'basic-updated' ) {
			if ( isset( $_POST['site_title'] ) ) {
				update_option( 'blogname', esc_html( wp_unslash( $_POST['site_title'] ) ) );
			}

			if ( isset( $_POST['site_tagline'] ) ) {
				update_option( 'blogdescription', esc_html( wp_unslash( $_POST['site_tagline'] ) ) );
			}

			wp_safe_redirect( self::get_controls_url( $is_activated ? 'child-theme' : 'register' ) );
			exit;
		}

		if ( $step === 'language-setuped' ) {
			$language = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';

			if ( $language ) {
				ETC_Languages::setup_translation( $language );
				update_option( 'etheme_builtin_language', true );
			}

			wp_safe_redirect( self::get_controls_url( 'basic' ) );
			exit;
		}

		if ( $step === 'engine' ) {
			$versions = etheme_get_demo_versions();
			$version_key = isset( $_GET['version'] ) ? sanitize_key( wp_unslash( $_GET['version'] ) ) : '';
			$engines = ( isset( $versions[ $version_key ]['engine'] ) && is_array( $versions[ $version_key ]['engine'] ) )
				? $versions[ $version_key ]['engine']
				: array( 'wpb' );
			$default_engine = in_array( 'elementor', $engines, true ) ? 'elementor' : $engines[0];

			if ( count( $engines ) <= 1 ) {
				wp_safe_redirect( self::get_controls_url( 'plugins&engine=' . $default_engine . '&version=' . $version_key ) );
				exit;
			}
		}

		if ( $step === 'plugins' ) {
			$plugins_class_file = get_template_directory() . '/framework/panel/classes/plugins.php';
			if ( file_exists( $plugins_class_file ) ) {
				require_once $plugins_class_file;
			}

			$version_key = isset( $_GET['version'] ) ? sanitize_key( wp_unslash( $_GET['version'] ) ) : '';
			$requested_engine = isset( $_GET['engine'] ) ? sanitize_key( wp_unslash( $_GET['engine'] ) ) : '';
			$versions = etheme_get_demo_versions();
			$engines = ( isset( $versions[ $version_key ]['engine'] ) && is_array( $versions[ $version_key ]['engine'] ) )
				? $versions[ $version_key ]['engine']
				: array( 'wpb' );
			$engine = in_array( $requested_engine, array( 'wpb', 'elementor', 'gutenberg' ), true )
				? $requested_engine
				: ( in_array( 'elementor', $engines, true ) ? 'elementor' : $engines[0] );

			if ( class_exists( 'Plugins' ) ) {
				$plugins_instance = new Plugins();
				$plugins = $plugins_instance->get_popup_plugin_list( $version_key, $engine );

				if ( ! count( $plugins ) ) {
					wp_safe_redirect( self::get_controls_url( 'install&engine=' . $engine . '&version=' . $version_key ) );
					exit;
				}
			}
		}
	}

	public function is_critical_requirements(){
		$system = class_exists('Etheme_System_Requirements') ? Etheme_System_Requirements::get_instance() : new Etheme_System_Requirements();
		$system->system_test(true);
		$system->result();
		return $system->is_critical_requirements();
	}
	

	// public function is_critical

	public function is_installed_demo(){
		$is_remove = false;
		$et_imported_data = get_option('et_imported_data', array());

		if (count($et_imported_data)){
			foreach ($et_imported_data as $type){
				if (count($type)){
					$is_remove = true;
				}
			}
		}
		return $is_remove;
	}

	public static function calculate_progress($current_step = 'welcome', $prev = false) {
		$steps = ['welcome'];
		$steps[] = 'basic';

		if (!etheme_is_activated()) {
			$steps[] = 'register';
		}
		else {
			$steps[] = 'registered';
		}

		$steps[] = 'child-theme';
		$steps[] = 'child-theme-created';
		$steps[] = 'demos';
		$steps[] = 'engine';
		$steps[] = 'plugins';
		$steps[] = 'install';
		$steps[] = 'final';

		$total_steps = count($steps);
		$current_index = array_search($current_step, $steps);

		if ($current_index === false) {
			return 0;
		}

		// If $prev is true, subtract 1 step (but never go below 0)
		$effective_index = max(0, $current_index - ($prev ? 1 : 0));

		return round((($effective_index + 1) / $total_steps) * 100);
	}

	public static function get_tooltip($text = '', $lg_size = false) {
		?>
		<span class="mtips<?php if ($lg_size) { ?> mtips-lg<?php } ?> text-left helping">
			<svg width="1em" height="1em" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 0C4.70996 0 0 4.70996 0 10.5C0 16.29 4.70996 21 10.5 21C16.29 21 21 16.29 21 10.5C21 4.70996 16.29 0 10.5 0ZM10.5 1.75C15.3433 1.75 19.25 5.65674 19.25 10.5C19.25 15.3433 15.3433 19.25 10.5 19.25C5.65674 19.25 1.75 15.3433 1.75 10.5C1.75 5.65674 5.65674 1.75 10.5 1.75ZM10.5 5.25C8.57568 5.25 7 6.82568 7 8.75H8.75C8.75 7.77246 9.52246 7 10.5 7C11.4775 7 12.25 7.77246 12.25 8.75C12.25 9.41992 11.8193 10.0146 11.1836 10.2266L10.8281 10.3359C10.1138 10.5718 9.625 11.2554 9.625 12.0039V13.125H11.375V12.0039L11.7305 11.8945C13.0771 11.4468 14 10.1685 14 8.75C14 6.82568 12.4243 5.25 10.5 5.25ZM9.625 14V15.75H11.375V14H9.625Z" fill="currentColor"/></svg>
			<?php echo '<span class="mt-mes">'.$text.'</span>'; ?>
		</span>
		<?php
	}

	public function get_steps_count(){
		
	}

	public function texts($text, $type = false) {
		$texts = array(
			'next' => __('Next!', 'xstore'),
			'skip' => __('Skip this step', 'xstore'),
		);

		if(isset($texts[$text])){
			return $texts[$text];
		} else {
			return 'No text found';
		}
	}

	public function elementor_experiments_container() {
		if(! class_exists( '\Elementor\Plugin' ) ) return 'no_elenemtor';
			
		$manager = \Elementor\Plugin::$instance->experiments;

		$ref = new ReflectionClass( $manager );
		$prop = $ref->getProperty( 'features' );
		$prop->setAccessible( true );
		$features = $prop->getValue( $manager );

		$out = [];

		foreach ( $features as $name => $data ) {
			if($data['name'] == 'container'){
				$state = $data['state'];
				if($state == 'inactive' || ($state == 'default' && $data['default'] == 'inactive')){
					return false;
				} else {
					return true;
				}
			}
		}
	}
}

add_action('after_setup_theme', function() {
	if (is_admin()) {
		ET_Setup_Wizard::get_instance();
	}
});
