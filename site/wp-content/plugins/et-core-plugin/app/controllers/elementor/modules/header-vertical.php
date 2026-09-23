<?php
/**
 * Vertical Header feature for Elementor header containers.
 *
 * @package XStoreCore
 */

namespace ETC\App\Controllers\Elementor\Modules;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use ETC\App\Classes\Elementor;

class Header_Vertical {

	public function __construct() {
		add_action( 'elementor/element/container/section_effects/before_section_start', array( $this, 'register_controls' ) );
		add_action( 'elementor/frontend/before_render', array( $this, 'before_render' ) );
	}

	public function register_controls( $element ) {
		if (
			Plugin::$instance->editor->is_edit_mode()
			&& ! ( Plugin::$instance->documents->get_current() instanceof \ElementorPro\Modules\ThemeBuilder\Documents\Header )
		) {
			return;
		}

		$breakpoints = Elementor::get_breakpoints_list();

		$element->start_controls_section(
			'_section_etheme_header_vertical',
			array(
				'label' => sprintf( __( '%s Vertical Header', 'xstore-core' ), apply_filters( 'etheme_theme_label', 'XSTORE' ) ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'etheme_header_vertical_description',
			array(
				'raw'             => esc_html__( 'Enable this option on the outermost header container. Padding, margin, border and box shadow remain available in the native Style and Advanced tabs. Sticky and overlap modes are ignored while the vertical header is active.', 'xstore-core' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'type'            => Controls_Manager::RAW_HTML,
			)
		);

		$element->add_control(
			'etheme_header_vertical',
			array(
				'label'              => esc_html__( 'Enable Vertical Header', 'xstore-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'return_value'       => 'yes',
				'frontend_available' => true,
				'render_type'        => 'template',
			)
		);

		$element->add_control(
			'etheme_header_vertical_position',
			array(
				'label'              => esc_html__( 'Position', 'xstore-core' ),
				'type'               => Controls_Manager::CHOOSE,
				'default'            => 'left',
				'options'            => array(
					'left'  => array(
						'title' => esc_html__( 'Left', 'xstore-core' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'xstore-core' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => array(
					'etheme_header_vertical' => 'yes',
				),
			)
		);

		$element->add_responsive_control(
			'etheme_header_vertical_width',
			array(
				'label'       => esc_html__( 'Header Width', 'xstore-core' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'vw' ),
				'range'       => array(
					'px' => array(
						'min' => 90,
						'max' => 600,
					),
					'vw' => array(
						'min' => 5,
						'max' => 50,
					),
				),
				'default'     => array(
					'size' => 300,
					'unit' => 'px',
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--etheme-elementor-header-vertical-width: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'etheme_header_vertical' => 'yes',
				),
			)
		);

		$element->add_control(
			'etheme_header_vertical_content_layout',
			array(
				'label'              => esc_html__( 'Content Layout', 'xstore-core' ),
				'description'        => esc_html__( 'Stack converts horizontal header containers, widgets and navigation menus into a vertical layout. Preserve keeps the Elementor structure unchanged for manually designed vertical headers.', 'xstore-core' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'stack',
				'options'            => array(
					'stack'    => esc_html__( 'Stack vertically', 'xstore-core' ),
					'preserve' => esc_html__( 'Preserve existing layout', 'xstore-core' ),
				),
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => array(
					'etheme_header_vertical' => 'yes',
				),
			)
		);

		$element->add_control(
			'etheme_header_vertical_on',
			array(
				'label'              => esc_html__( 'Vertical Header On Devices', 'xstore-core' ),
				'type'               => Controls_Manager::SELECT2,
				'multiple'           => true,
				'label_block'        => true,
				'default'            => array_key_exists( 'desktop', $breakpoints ) ? array( 'desktop' ) : array(),
				'options'            => $breakpoints,
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => array(
					'etheme_header_vertical' => 'yes',
				),
			)
		);

		$element->add_control(
			'etheme_header_vertical_content_offset',
			array(
				'label'              => esc_html__( 'Offset Page Content', 'xstore-core' ),
				'description'        => esc_html__( 'Reserves space for the vertical header using its actual width and horizontal margins.', 'xstore-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'return_value'       => 'yes',
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => array(
					'etheme_header_vertical' => 'yes',
				),
			)
		);

		$element->end_controls_section();
	}

	public function before_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( empty( $settings['etheme_header_vertical'] ) ) {
			return;
		}

		$position = isset( $settings['etheme_header_vertical_position'] ) && 'right' === $settings['etheme_header_vertical_position'] ? 'right' : 'left';

		$element->add_render_attribute(
			'_wrapper',
			array(
				'class' => array(
					'etheme-elementor-header-vertical',
					'etheme-elementor-header-vertical-position-' . $position,
				),
			)
		);

		if ( apply_filters( 'etheme_should_enqueue_style', true ) ) {
			wp_enqueue_style( 'etheme-elementor-header-sticky' );
		}
		if ( apply_filters( 'etheme_should_enqueue_script', true ) ) {
			wp_enqueue_script( 'etheme_elementor_header_sticky' );
		}
	}
}
