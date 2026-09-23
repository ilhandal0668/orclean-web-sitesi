<?php
/**
 * The Control class.
 *
 * @package KirkiAccessibleColorpicker
 * @since 1.0
 */

/**
 * The main control class.
 *
 * @since 1.0
 */
class Kirki_Box_Shadow_Control extends WP_Customize_Control {

	/**
	 * The control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'kirki-box-shadow';

	/**
	 * Used to automatically generate all CSS output.
	 *
	 * @access public
	 * @var array
	 */
	public $output = [];

	/**
	 * Data type
	 *
	 * @access public
	 * @var string
	 */
	public $option_type = 'theme_mod';

	/**
	 * Option name (if using options).
	 *
	 * @access public
	 * @var string
	 */
	public $option_name = false;

	/**
	 * The kirki_config we're using for this control
	 *
	 * @access public
	 * @var string
	 */
	public $kirki_config = 'global';

	/**
	 * Whitelisting the "required" argument.
	 *
	 * @since 3.0.17
	 * @access public
	 * @var array
	 */
	public $required = [];

	/**
	 * Whitelisting the "css_vars" argument.
	 *
	 * @since 3.0.28
	 * @access public
	 * @var string
	 */
	public $css_vars = '';

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
	 */
	public function enqueue() {

		// Enqueue the script and style.
		wp_enqueue_script( 'kirki_box_shadow_control', apply_filters( 'kirki_box_shadow_control_url', plugins_url( __FILE__ ) ) . '/script.js', [ 'jquery', 'customize-base', 'customize-controls' ], '1.0.2', false );
		wp_enqueue_style( 'kirki_box_shadow_control', apply_filters( 'kirki_box_shadow_control_url', plugins_url( __FILE__ ) ) . '/styles.css', [], '1.0.2' );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @see WP_Customize_Control::to_json()
	 */
	public function to_json() {

		// Get the basics from the parent class.
		parent::to_json();

		// Default value.
		$this->json['default'] = $this->setting->default;
		if ( isset( $this->default ) ) {
			$this->json['default'] = $this->default;
		}

		// Required.
		$this->json['required'] = $this->required;

		// Output.
		$this->json['output'] = $this->output;

		// Value.
		$this->json['value'] = $this->value();

		// Choices.
		$this->json['choices'] = $this->choices;

		// The link.
		$this->json['link'] = $this->get_link();

		// The ID.
		$this->json['id'] = $this->id;

		// The kirki-config.
		$this->json['kirkiConfig'] = $this->kirki_config;

		// The option-type.
		$this->json['kirkiOptionType'] = $this->option_type;

		// The option-name.
		$this->json['kirkiOptionName'] = $this->option_name;

		// The CSS-Variables.
		$this->json['css-var'] = $this->css_vars;
	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see WP_Customize_Control::print_template()
	 *
	 * @access protected
	 */
	protected function content_template() {
		?>
		<!-- Label. -->
		<# if ( data.label ) { #>
			<label><span class="customize-control-title">{{{ data.label }}}</span></label>
		<# } #>
		<!-- Description. -->
		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>

		<div class="kirki-input-container kirki-box-shadow-field">
			<label for="{{ data.id }}-preset"><?php esc_html_e( 'Preset', 'xstore-core' ); ?></label>
			<select id="{{ data.id }}-preset" class="shadow-preset" data-context="preset">
				<option value="custom"><?php esc_html_e( 'Custom', 'xstore-core' ); ?></option>
				<option value="none"><?php esc_html_e( 'None', 'xstore-core' ); ?></option>
				<option value="soft"><?php esc_html_e( 'Soft', 'xstore-core' ); ?></option>
				<option value="medium"><?php esc_html_e( 'Medium', 'xstore-core' ); ?></option>
				<option value="strong"><?php esc_html_e( 'Strong', 'xstore-core' ); ?></option>
			</select>
		</div>

		<div class="kirki-input-container kirki-box-shadow-grid">
			<div class="kirki-box-shadow-field">
				<label for="{{ data.id }}-horizontal-length"><?php esc_html_e( 'X offset', 'xstore-core' ); ?></label>
				<div class="kirki-box-shadow-number">
					<input class="shadow-part horizontal-length" type="number" step="1" id="{{ data.id }}-horizontal-length" data-context="horizontalLength"/>
					<span>px</span>
				</div>
			</div>
			<div class="kirki-box-shadow-field">
				<label for="{{ data.id }}-vertical-length"><?php esc_html_e( 'Y offset', 'xstore-core' ); ?></label>
				<div class="kirki-box-shadow-number">
					<input class="shadow-part vertical-length" type="number" step="1" id="{{ data.id }}-vertical-length" data-context="verticalLength"/>
					<span>px</span>
				</div>
			</div>
			<div class="kirki-box-shadow-field">
				<label for="{{ data.id }}-blur-radius"><?php esc_html_e( 'Blur', 'xstore-core' ); ?></label>
				<div class="kirki-box-shadow-number">
					<input class="shadow-part blur-radius" type="number" min="0" step="1" id="{{ data.id }}-blur-radius" data-context="blurRadius"/>
					<span>px</span>
				</div>
			</div>
			<div class="kirki-box-shadow-field">
				<label for="{{ data.id }}-spread-radius"><?php esc_html_e( 'Spread', 'xstore-core' ); ?></label>
				<div class="kirki-box-shadow-number">
					<input class="shadow-part spread-radius" type="number" step="1" id="{{ data.id }}-spread-radius" data-context="spreadRadius"/>
					<span>px</span>
				</div>
			</div>
		</div>

		<div class="kirki-input-container kirki-box-shadow-switch">
			<label for="{{ data.id }}-inset">
				<input id="{{ data.id }}-inset" class="inset" type="checkbox" data-context="inset"/>
				<span><?php esc_html_e( 'Inset shadow', 'xstore-core' ); ?></span>
			</label>
		</div>

		<div class="kirki-input-container kirki-box-shadow-field">
			<label for="{{ data.id }}-color"><?php esc_html_e( 'Color', 'xstore-core' ); ?></label>
			<input id="{{ data.id }}-color" type="text" data-default-color="rgba(0,0,0,0.15)" value="" class="kirki-color-control" data-id="{{ data.id }}" data-alpha="true"/>
		</div>

		<input class="hidden-value" type="hidden" {{{ data.link }}}/>

		<?php
	}

	/**
	 * Adding an empty function here prevents PHP errors from to_json() in the parent class.
	 *
	 * @access protected
	 * @since 1.0
	 * @return void
	 */
	protected function render_content() {}
}
