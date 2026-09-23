<?php
namespace ETC\App\Controllers\Elementor\General;

use ETC\App\Classes\Elementor;

/**
 * Product filters widget.
 *
 * @since      4.0.8
 * @package    ETC
 * @subpackage ETC/Controllers/Elementor/General
 */

class Product_Filters extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @since 4.0.8
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'etheme_product_filters';
    }

    /**
     * Get widget title.
     *
     * @since 4.0.8
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'Product Filters', 'xstore-core' );
    }

    /**
     * Get widget icon.
     *
     * @since 4.0.8
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eight_theme-elementor-icon et-elementor-product-filter';
    }

    /**
     * Get widget keywords.
     *
     * @since 4.0.8
     * @access public
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return [ 'product', 'filter', 'attributes', 'categories', 'price', 'select', 'woocommerce' ];
    }

    /**
     * Get widget categories.
     *
     * @since 4.0.8
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'eight_theme_general' ];
    }

    /**
     * Get widget dependency.
     *
     * @since 4.0.8
     * @access public
     *
     * @return array Widget dependency.
     */

    public function get_script_depends() {
        return apply_filters('etheme_elementor_widget_script_depends', ['etheme_product_filters'], true);
    }

    public function get_style_depends() {
        return apply_filters('etheme_elementor_widget_style_depends', [ 'etheme-elementor-product-filters' ], true);
    }

    /**
     * Help link.
     *
     * @since 4.1.5
     *
     * @return string
     */
    public function get_custom_help_url() {
        return etheme_documentation_url('122-elementor-live-copy-option', false);
    }

    /**
     * Register the widget controls.
     *
     * @since 4.0.8
     * @access protected
     */
    protected function register_controls() {
        if ( !class_exists('WooCommerce') ) {
            $this->start_controls_section(
                'section_general',
                [
                    'label' => esc_html__( 'General', 'xstore-core' ),
                ]
            );
            $this->add_control(
                'required_plugin_info',
                [
                    'type'            => \Elementor\Controls_Manager::RAW_HTML,
                    'raw' => sprintf( __( 'Please, install <a href="%s" target="_blank">WooCommerce</a> plugin to use this widget', 'xstore-core' ), admin_url('plugin-install.php?s=woocommerce&tab=search&type=term') ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                ]
            );
            $this->end_controls_section();
        }
        else {
            $this->start_controls_section(
                'section_general',
                [
                    'label' => esc_html__( 'General', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'type',
                [
                    'label'   => esc_html__( 'Type', 'xstore-core' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => array(
                        'separated' => esc_html__('Separated', 'xstore-core'),
                        'inline' => esc_html__('Inline', 'xstore-core')
                    ),
                    'default' => 'separated',
                ]
            );

            $this->add_control(
                'content_position',
                [
                    'label'   => esc_html__( 'Content Position', 'xstore-core' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => array(
                        'top' => esc_html__('Top', 'xstore-core'),
                        '' => esc_html__('Bottom', 'xstore-core')
                    ),
                    'default' => '',
                    'prefix_class'          => 'etheme-product-filters-content-',
                ]
            );

            $repeater = new \Elementor\Repeater();

            $filtered_taxonomies = $this->product_taxonomies_to_filter();

            $repeater_options = array(
                'attributes' => esc_html__( 'Attributes', 'xstore-core' ),
                'price'      => esc_html__( 'Price', 'xstore-core' ),
                'rating'     => esc_html__( 'Rating', 'xstore-core' ),
                'orderby'    => esc_html__( 'Order By', 'xstore-core' ),
                'on_sale'    => esc_html__( 'On Sale', 'xstore-core' ),
            );

            $repeater_options = $filtered_taxonomies + $repeater_options;

            $repeater->add_control(
                'filter_title',
                [
                    'label'     => esc_html__( 'Title', 'xstore-core' ),
                    'type'      => \Elementor\Controls_Manager::TEXT,
                    'default'   => esc_html__('Filter','xstore-core')
                ]
            );

            $repeater->add_control(
                'filter_type',
                [
                    'label'   => esc_html__( 'Filter type', 'xstore-core' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => $repeater_options,
                    'default' => array_key_exists('product_cat', $repeater_options) ? 'product_cat' : $repeater_options[array_key_first($repeater_options)],
                ]
            );

            foreach ($filtered_taxonomies as $taxonomy_key => $taxonomy_title) {
                $taxonomy = get_taxonomy($taxonomy_key);

                if ( $taxonomy->hierarchical ) {
                    $repeater->add_control(
                        $taxonomy_key . '_hierarchical',
                        [
                            'label'     => esc_html__( 'Show Hierarchy', 'xstore-core' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'condition' => [
                                'filter_type' => $taxonomy_key,
                            ],
                        ]
                    );
                }

                $repeater->add_control(
                    $taxonomy_key.'_hide_empty',
                    [
                        'label'        => esc_html__( 'Hide Empty', 'xstore-core' ),
                        'type'         => \Elementor\Controls_Manager::SWITCHER,
                        'condition'    => [
                            'filter_type' => $taxonomy_key,
                        ],
                    ]
                );

                $repeater->add_control(
                    $taxonomy_key.'_show_count',
                    [
                        'label'        => esc_html__( 'Show Count', 'xstore-core' ),
                        'type'         => \Elementor\Controls_Manager::SWITCHER,
                        'condition'    => [
                            'filter_type' => $taxonomy_key,
                        ],
                    ]
                );
            }

            $repeater->add_control(
                'attribute',
                [
                    'label'     => esc_html__( 'Attribute', 'xstore-core' ),
                    'type'      => \Elementor\Controls_Manager::SELECT,
                    'options'   => $this->get_attributes(),
                    'default'   => '',
                    'condition' => [
                        'filter_type' => 'attributes',
                    ],
                ]
            );

            $repeater->add_control(
                'rating_type',
                [
                    'label'     => esc_html__( 'Display Type', 'xstore-core' ),
                    'type'      => \Elementor\Controls_Manager::SELECT,
                    'options'               => [
                        'default'    => __( 'Default', 'xstore-core' ),
                        'advanced'        => __( 'Advanced', 'xstore-core' ),
                    ],
                    'default'   => 'default',
                    'condition' => [
                        'filter_type' => 'rating',
                    ],
                ]
            );

            $repeater->add_control(
                'price_type',
                [
                    'label'     => esc_html__( 'Display Type', 'xstore-core' ),
                    'type'      => \Elementor\Controls_Manager::SELECT,
                    'options'               => [
                        'slider'   => esc_html__( 'Slider', 'xstore-core' ),
                        'ranges'  => esc_html__( 'Ranges', 'xstore-core' )
                    ],
                    'default'   => 'slider',
                    'condition' => [
                        'filter_type' => 'price',
                    ],
                ]
            );

            $repeater->add_control(
                'price_ranges',
                [
                    'label' => __( 'Ranges', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'description' => sprintf(__( 'Each range on a line, separate by the "-" symbol. Do not include the currency symbol. Example: %s', 'xstore-core' ), 'https://prnt.sc/g3WzTnI0IhXY'),
                    'rows' => 5,
                    'default' => implode("\n", array('100-200', '200-300', '300-400')),
                    'placeholder' => implode("\n", array('100-200', '200-300', '300-400')),
                    'condition' => [
                        'filter_type' => 'price',
                        'price_type' => 'ranges'
                    ],
                ]
            );

            $repeater->add_control(
                'attribute_query_type',
                [
                    'label'     => esc_html__( 'Query Type', 'xstore-core' ),
                    'type'      => \Elementor\Controls_Manager::SELECT,
                    'default'   => 'and',
                    'options'   => array(
                        'or'  => esc_html__( 'OR', 'xstore-core' ),
                        'and' => esc_html__( 'AND', 'xstore-core' ),
                    ),
                    'condition' => [
                        'filter_type' => 'attributes',
                    ],
                ]
            );

            $repeater->add_control(
                'items_limit',
                [
                    'label' 		=>	__( 'Limit', 'xstore-core' ),
                    'type' 			=>	\Elementor\Controls_Manager::NUMBER,
                    'default'	 	=>	'',
                    'min' 	=> '1',
                    'max' 	=> '',
                    'condition' => [
                        'filter_type' => ['attributes', 'rating'],
                    ],
                ]
            );

            $repeater->add_control(
                'required_field',
                [
                    'label'        => esc_html__( 'Required field', 'xstore-core' ),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'section_items',
                [
                    'label' => esc_html__( 'Items', 'xstore-core' ),
                ]
            );

            //	Repeater
            $this->add_control(
                'items',
                [
                    'type'        => \Elementor\Controls_Manager::REPEATER,
                    'title_field' => '{{{ filter_title }}}',
                    'fields'      => $repeater->get_controls(),
                    'default'     => [
                        [
                            'filter_type' => 'product_cat',
                            'filter_title' => esc_html__('Categories', 'xstore-core')
                        ],
                        [
                            'filter_type' => 'rating',
                            'filter_title' => esc_html__('Rating filter', 'xstore-core')
                        ],
                        [
                            'filter_type' => 'price',
                            'filter_title' => esc_html__('Price filter', 'xstore-core')
                        ],
                    ],
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'button_section',
                [
                    'label' => __( 'Button', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'button_text',
                [
                    'label' => __( 'Button Text', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Search', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'button_selected_icon',
                [
                    'label' => esc_html__( 'Icon', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::ICONS,
                    'fa4compatibility' => 'button_icon',
                    'skin' => 'inline',
                    'label_block' => false,
                ]
            );

            $this->add_control(
                'button_icon_align',
                [
                    'label' => esc_html__( 'Icon Position', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'left',
                    'options' => [
                        'left' => esc_html__( 'Before', 'xstore-core' ),
                        'right' => esc_html__( 'After', 'xstore-core' ),
                    ],
                    'condition' => [
                        'button_selected_icon[value]!' => '',
                        'button_text!' => '',
                    ],
                ]
            );

            $this->add_control(
                'button_icon_indent',
                [
                    'label' => esc_html__( 'Icon Spacing', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'max' => 50,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'button_text!' => '',
                        'button_selected_icon[value]!' => '',
                    ],
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'section_style_general',
                [
                    'label' => esc_html__( 'General', 'xstore-core' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_responsive_control(
                'cols_gap',
                [
                    'label' => __( 'Spacing To Button', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 100,
                            'step' => 1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--cols-gap: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name'     => 'items_wrapper_background',
                    'types'    => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .etheme-product-filters-items',
                    'condition' => [
                        'type' => 'inline'
                    ]
                ]
            );

            $this->add_control(
                'separator_items_wrapper_background_style',
                [
                    'type' => \Elementor\Controls_Manager::DIVIDER,
                    'condition' => [
                        'type' => 'inline'
                    ]
                ]
            );

            $this->add_control(
                'items_wrapper_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-items' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'type' => 'inline'
                    ],
                    'default' => [
                        'top' => 30,
                        'left' => 30,
                        'right' => 30,
                        'bottom' => 30,
                        'unit' => 'px'
                    ]
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name'      => 'items_wrapper_border',
                    'label'     => esc_html__( 'Border', 'xstore-core' ),
                    'selector'  => '{{WRAPPER}} .etheme-product-filters-items',
                    'condition' => [
                        'type' => 'inline'
                    ],
                    'fields_options' => [
                        'border' => [
                            'default' => 'solid',
                        ],
                        'width' => [
                            'default' => [
                                'top' => 1,
                                'left' => 1,
                                'right' => 1,
                                'bottom' => 1
                            ],
                        ],
                        'color' => [
                            'default' => '#e1e1e1',
                        ]
                    ],
                ]
            );

            $this->add_control(
                'delimiter_style_heading',
                [
                    'label' => __( 'Delimiter', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                    'condition' => [
                        'type' => 'inline'
                    ]
                ]
            );

            $this->add_control(
                'delimiter_width',
                [
                    'label' => __( 'Width', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 5,
                            'step' => 1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--delimiter-width: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'type' => 'inline'
                    ]
                ]
            );

            $this->add_control(
                'delimiter_style',
                [
                    'label' => __( 'Style', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'solid' => __( 'Solid', 'xstore-core' ),
                        'double' => __( 'Double', 'xstore-core' ),
                        'dotted' => __( 'Dotted', 'xstore-core' ),
                        'dashed' => __( 'Dashed', 'xstore-core' ),
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--delimiter-style: {{VALUE}};',
                    ],
                    'condition' => [
                        'type' => 'inline'
                    ]
                ]
            );

            $this->add_control(
                'delimiter_color',
                [
                    'label' => __( 'Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}}' => '--delimiter-color: {{VALUE}}',
                    ],
                    'condition' => [
                        'type' => 'inline'
                    ]
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'item_style_section',
                [
                    'label' => esc_html__( 'Items', 'xstore-core' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'item_spacing',
                [
                    'label' => __( 'Spacing', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 100,
                            'step' => 1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => '--inner-cols-gap: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'item_typography',
                    'selector' => '{{WRAPPER}} .etheme-product-filters-item-title',
                ]
            );

            $this->start_controls_tabs( 'item_tabs' );

            $this->start_controls_tab(
                'item_tab_normal',
                [
                    'label' => __( 'Normal', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'item_color',
                [
                    'label' => esc_html__( 'Text Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-item-title' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name' => 'item_background',
                    'label' => esc_html__( 'Background', 'xstore-core' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .etheme-product-filters-item-title',
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'item_tab_active',
                [
                    'label' => __( 'Active', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'item_color_active',
                [
                    'label' => esc_html__( 'Text Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .opened .etheme-product-filters-item-title' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name' => 'item_background_active',
                    'label' => esc_html__( 'Background', 'xstore-core' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .opened .etheme-product-filters-item-title',
                ]
            );

            $this->add_control(
                'item_border_color_active',
                [
                    'label' => esc_html__( 'Border Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'condition' => [
                        'type!' => 'inline',
                        'item_border_border!' => '',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .opened .etheme-product-filters-item-title' => 'border-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'item_border',
                    'selector' => '{{WRAPPER}} .etheme-product-filters-item-title',
                    'separator' => 'before',
                    'condition' => [
                        'type!' => 'inline',
                    ],
                    'fields_options' => [
                        'border' => [
                            'default' => 'solid',
                        ],
                        'width' => [
                            'default' => [
                                'top' => 1,
                                'left' => 1,
                                'right' => 1,
                                'bottom' => 1
                            ],
                        ],
                        'color' => [
                            'default' => '#e1e1e1',
                        ]
                    ],
                ]
            );

            $this->add_responsive_control(
                'item_padding',
                [
                    'label' => esc_html__( 'Padding', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-item-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .etheme-product-filters-item-title:not(:last-child):after' => 'top: {{TOP}}{{UNIT}}; bottom: {{BOTTOM}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'item_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-item-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'type!' => 'inline'
                    ],
                    'default' => [
                        'top' => 3,
                        'left' => 3,
                        'right' => 3,
                        'bottom' => 3,
                        'unit' => 'px'
                    ]
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'item_content_style_section',
                [
                    'label' => esc_html__( 'Item Content', 'xstore-core' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'item_content_color',
                [
                    'label' => esc_html__( 'Text Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-item-content' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name' => 'item_content_background',
                    'label' => esc_html__( 'Background', 'xstore-core' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .etheme-product-filters-item-content',
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'item_content_border',
                    'selector' => '{{WRAPPER}} .etheme-product-filters-item-content',
                    'separator' => 'before',
                    'fields_options' => [
                        'border' => [
                            'default' => 'solid',
                        ],
                        'width' => [
                            'default' => [
                                'top' => 1,
                                'left' => 1,
                                'right' => 1,
                                'bottom' => 1
                            ],
                        ],
                        'color' => [
                            'default' => '#e1e1e1',
                        ]
                    ],
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'section_button_style',
                [
                    'label' => esc_html__( 'Button', 'xstore-core' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'button_typography',
                    'selector' => '{{WRAPPER}} .etheme-product-filters-button .elementor-button',
                ]
            );

            $this->start_controls_tabs( 'tabs_button_style' );

            $this->start_controls_tab(
                'tab_button_normal',
                [
                    'label' => esc_html__( 'Normal', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'button_text_color',
                [
                    'label' => esc_html__( 'Text Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#fff',
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name' => 'button_background',
                    'label' => esc_html__( 'Background', 'xstore-core' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .etheme-product-filters-button .elementor-button',
                    'fields_options' => [
                        'background' => [
                            'default' => 'classic',
                        ],
                        'color' => [
                            'default' => '#000000',
                        ],
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'tab_button_hover',
                [
                    'label' => esc_html__( 'Hover', 'xstore-core' ),
                ]
            );

            $this->add_control(
                'button_hover_color',
                [
                    'label' => esc_html__( 'Text Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-button:hover, {{WRAPPER}} .etheme-product-filters-button .elementor-button:focus' => 'color: {{VALUE}};',
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-button:hover svg, {{WRAPPER}} .etheme-product-filters-button .elementor-button:focus svg' => 'fill: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name' => 'button_background_hover',
                    'label' => esc_html__( 'Background', 'xstore-core' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .etheme-product-filters-button .elementor-button:hover, {{WRAPPER}} .etheme-product-filters-button .elementor-button:focus',
                    'fields_options' => [
                        'background' => [
                            'default' => 'classic',
                        ],
                        'color' => [
                            'default' => '#3f3f3f'
                        ]
                    ],
                ]
            );

            $this->add_control(
                'button_hover_border_color',
                [
                    'label' => esc_html__( 'Border Color', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'condition' => [
                        'button_border_border!' => '',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-button:hover, {{WRAPPER}} .etheme-product-filters-button .elementor-button:focus' => 'border-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'button_border',
                    'selector' => '{{WRAPPER}} .etheme-product-filters-button .elementor-button',
                    'separator' => 'before',
                    'fields_options' => [
                        'border' => [
                            'default' => 'solid',
                        ],
                        'width' => [
                            'default' => [
                                'top' => 0,
                                'left' => 0,
                                'right' => 0,
                                'bottom' => 0
                            ]
                        ],
                    ],
                ]
            );

            $this->add_control(
                'button_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'button_padding',
                [
                    'label' => esc_html__( 'Padding', 'xstore-core' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .etheme-product-filters-button .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' => 'before',
                ]
            );

            $this->end_controls_section();
        }
    }

    /**
     * Render product filters widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 4.0.8
     * @access protected
     */
    protected function render() {

        if ( !class_exists('WooCommerce') ) {
            echo '<div class="elementor-panel-alert elementor-panel-alert-warning">'.
                esc_html__('Install WooCommerce Plugin to use this widget', 'xstore-core') .
                '</div>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $edit_mode = Elementor::is_editor_or_preview_mode();
        $migrated = isset( $settings['__fa4_migrated']['button_selected_icon'] );
        $is_new = empty( $settings['button_icon'] ) && \Elementor\Icons_Manager::is_migration_allowed();

        global $wp;

        $form_action = wc_get_page_permalink( 'shop' );

        if ( get_query_var('et_is-woocommerce-archive', false) && apply_filters( 'xstore_filters_form_action_without_cat_widget', false ) ) {
            if ( '' === get_option( 'permalink_structure' ) ) {
                $form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
            } else {
                $form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
            }
        }

        $this->add_render_attribute(
            [
                'wrapper' => [
                    'class'  => [
                        'etheme-product-filters',
                    ],
                    'action' => [
                        $form_action,
                    ],
                    'data-origin-action' => [
                        $form_action,
                    ],
                    'method' => [
                        'GET',
                    ],
                    'data-type' => [
                        $settings['type']
                    ]
                ],
            ]
        );

        if ( get_query_var('et_is-woocommerce-archive', false) ) {
            $this->add_render_attribute( 'wrapper', 'class', 'with-ajax' );
        }

        ?>
        <form <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
            <div class="etheme-product-filters-items">
                <?php
                foreach ( $settings['items'] as $index => $item ) :

                    switch ($item['filter_type']) {
                        case 'attributes':
                            $this->attributes_filter_template( $item );
                            break;
                        case 'price':
                            $this->price_filter_template( $item, $edit_mode );
                            break;
                        case 'orderby':
                            $this->orderby_filter_template( $item );
                            break;
                        case 'rating':
                            $this->rating_filter_template( $item, $form_action );
                            break;
                        case 'on_sale':
                            $this->on_sale_filter_template( $item, $form_action );
                            break;
                        default:
                            if ( in_array($item['filter_type'], array_keys($this->product_taxonomies_to_filter())) ) {
                                $this->taxonomy_filter_template( $item );
                            }
                            break;
                    }

                endforeach;
                ?>
            </div>

            <?php

            $this->add_render_attribute( [
                'button-wrapper' => [
                    'class' => [
                        'etheme-product-filters-button',
                    ]
                ],
                'button' => [
                    'class' => [
                        'elementor-button'
                    ]
                ],
                'button-icon-align' => [
                    'class' => [
                        'elementor-button-icon',
                        'elementor-align-icon-' . $settings['button_icon_align'],
                    ],
                ],
                'content-wrapper' => [
                    'class' => 'elementor-button-content-wrapper',
                ],
                'text' => [
                    'class' => 'elementor-button-text',
                ],
            ] );
            // $this->add_render_attribute( 'button', 'class', 'elementor-button' );
            $this->add_render_attribute( 'button', 'role', 'button' );
            $this->add_render_attribute( 'button', 'type', 'submit' );

            ?>

            <div <?php $this->print_render_attribute_string( 'button-wrapper' ); ?>>
                <button <?php $this->print_render_attribute_string( 'button' ); ?>>
                    <span <?php $this->print_render_attribute_string( 'content-wrapper' ); ?>>
                        <?php if ( ! empty( $settings['button_icon'] ) || ! empty( $settings['button_selected_icon']['value'] ) ) : ?>
                            <span <?php $this->print_render_attribute_string( 'button-icon-align' ); ?>>
                            <?php if ( $is_new || $migrated ) :
                                \Elementor\Icons_Manager::render_icon( $settings['button_selected_icon'], [ 'aria-hidden' => 'true' ] );
                            else : ?>
                                <i class="<?php echo esc_attr( $settings['button_icon'] ); ?>" aria-hidden="true"></i>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <span <?php $this->print_render_attribute_string( 'text' ); ?>>
                            <?php echo $settings['button_text'] ?? esc_html__( 'Search', 'xstore-core' ); ?>
                        </span>
                    </span>
                </button>
            </div>

        </form>
        <?php

    }

    /**
     * Render Rating filter item.
     *
     * @param $settings
     * @param $link
     * @return void
     *
     * @since 4.0.8
     *
     */
    public function rating_filter_template($settings, $link) {

        $rating_filter = array();
        $html = '';
        for ( $rating = 5; $rating >= 1; $rating-- ) {

            $link_ratings = implode( ',', array_merge( $rating_filter, array( $rating ) ) );
            $class       = in_array( $rating, $rating_filter, true ) ? 'wc-layered-nav-rating chosen' : 'wc-layered-nav-rating';
            $link        = apply_filters( 'woocommerce_rating_filter_link', $link_ratings ? add_query_arg( 'rating_filter', $link_ratings, $link ) : remove_query_arg( 'rating_filter' ) );
            $rating_html = wc_get_star_rating_html( $rating );
            $count_html  = '';
            $label = $settings['rating_type'] == 'advanced' ? ('<em>' . ($rating >= 5 ? $rating : sprintf(esc_html__('%s & Up', 'xstore-core'), $rating) ) . '</em>') : '';
            $html .= sprintf( '<li class="%s"><a class="filter-item" href="%s" data-value="%s"><span class="star-rating">%s</span> %s %s</a></li>', esc_attr( $class ), esc_url( $link ), $rating, $rating_html, $label, $count_html ); // WPCS: XSS ok.
        }

        $ratings_args = array(
            'name' => 'rating_filter',
        );

        if ( !!$settings['items_limit'] )
            $ratings_args['attr'] = array('data-limit="'.$settings['items_limit'].'"');

        $this->render_item_content(
            $settings,
            $html,
            $ratings_args
        );

    }

    /**
     * Render Price filter item.
     *
     * @param $settings
     * @return void
     *
     * @since 4.0.8
     *
     */
    public function price_filter_template( $settings, $edit_mode ) {
        $range_type = $settings['price_type'] == 'ranges';
        if ( !$range_type )
            $this->get_price_scripts();

        $prices = $this->get_filtered_price();

        $step = max( apply_filters( 'woocommerce_price_filter_widget_step', 10 ), 1 );

        $min = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $prices->min_price ) );
        $max = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $prices->max_price ) );

        if ( $min === $max ) {
            return;
        }

        if ( ( get_query_var('et_is-woocommerce-archive', false) ) && ! WC()->query->get_main_query()->post_count ) {
            return;
        }

        $min_price = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : $min;
        $max_price = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : $max;

        $extra_atts = array();

        $html = '<div class="'.($range_type ? 'widget_price_filter' : 'price_slider_wrapper').'">';

        if ( $edit_mode ) {
            $html .= __('Price filter is not available in edit mode', 'xstore-core');
        }
        else {
            if ( $range_type ) {
                $price_ranges = $this->get_range_price($settings);
                $filter_name = 'price';
                $range_args            = array(
                    'name'        			=> $filter_name,
                    'current'     			=> array(),
                    'options'     			=> $price_ranges,
                    'multiple'    			=> 0,
                    'show_counts' 			=> 0,
                    'display_type' 			=> 'range',
                    'source' 	   			=> 'price',
                    'button_text' => esc_html__('Apply', 'xstore-core')
                );
                $range_args['current']['min'] = $min_price;
                $range_args['current']['max'] = $max_price;
                ob_start();
                $this->render_range_price($range_args);
                $html .= ob_get_clean();
                $extra_atts['attr'] = array('data-limit="1"');
            }
            else {
                $extra_atts['result_input'] = false;
                $html .=
                    '<div class="etheme_price_slider" style="display:none;"></div>' .
                    '<div class="price_slider_amount" data-step="' . esc_attr($step) . '">' .
                    '<input type="text" id="min_price" name="min_price" value="' . esc_attr($min_price) . '" data-min="' . esc_attr($min) . '" placeholder="' . esc_attr__('Min price', 'xstore-core') . '" />' .
                    '<input type="text" id="max_price" name="max_price" value="' . esc_attr($max_price) . '" data-max="' . esc_attr($max) . '" placeholder="' . esc_attr__('Max price', 'xstore-core') . '" />' .
                    '<div class="price_label" style="display:none;">' .
                    esc_html__('Price:', 'xstore-core') . '<span class="from"></span> &mdash; <span class="to"></span>' .
                    '</div>' .
                    wc_query_string_form_fields(null, array('min_price', 'max_price', 'sale_status', 'rating_filter', 'orderby', 'paged'), '', true) .
                    '<div class="clear"></div>' .
                    '</div>';
            }
        }

        $html .= '</div>';

        $this->render_item_content($settings, $html, array_merge($extra_atts, array('html_tag' => 'div')));
    }

    /**
     * Render Order By filter item.
     *
     * @param $settings
     * @return void
     *
     * @since 4.0.8
     *
     */
    public function orderby_filter_template($settings) {
        $options = apply_filters(
            'woocommerce_catalog_orderby',
            array(
                'menu_order' => __( 'Default sorting', 'xstore-core' ),
                'popularity' => __( 'Sort by popularity', 'xstore-core' ),
                'rating'     => __( 'Sort by average rating', 'xstore-core' ),
                'date'       => __( 'Sort by latest', 'xstore-core' ),
                'price'      => __( 'Sort by price: low to high', 'xstore-core' ),
                'price-desc' => __( 'Sort by price: high to low', 'xstore-core' ),
            )
        );

        $html = '';
        foreach ( $options as $key => $value ) {
            $html .=
                '<li>' .
                '<span class="filter-item" data-value="' . esc_attr( $key ) . '">' .
                esc_html( $value ) .
                '</span>' .
                '</li>';
        }

        $this->render_item_content($settings, $html, array('attr' => array('data-limit="1"')));
    }

    /**
     * Render Attributes filter item.
     *
     * @param $settings
     * @return void
     *
     * @since 4.0.8
     *
     */
    public function attributes_filter_template( $settings ) {

        if ( !$settings['attribute'] ) {
            return;
        }

        $html_tag = 'ul';

        global $wpdb;
        $taxonomy           = wc_attribute_taxonomy_name( $settings['attribute'] );

        // ! Set get_terms args
        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            )
        );

        if ( is_wp_error( $terms ) || ! count( $terms ) ) {
            return;
        }

        $query_type = isset( $settings['attribute_query_type'] ) ? $settings['attribute_query_type'] : 'and';
        $term_counts = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
        $current_filter = $this->get_current_taxonomy_filter( $taxonomy );
        $filter_by_counts = is_product_category() || is_tax( 'brand' ) || is_product_tag() || is_search() || $this->has_active_filters();
        $has_other_active_filters = $this->has_active_filters_except_taxonomy( $taxonomy );

        $origin_attr = substr( $taxonomy, 3 );
        $attribute_type = get_query_var('et_swatch_tax-'.$origin_attr, false);
        if ( !$attribute_type ) {
            $attr = $wpdb->get_row( $wpdb->prepare( "SELECT attribute_type FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s", $origin_attr ) );
            if ($attr && $attr->attribute_type) {
                $attribute_type = $attr->attribute_type;
                set_query_var('et_swatch_tax-' . $origin_attr, $attribute_type);
            }
        }

        $html = '';
        $visible_terms = 0;

        // if swatches
        if ( get_query_var( 'et_is-swatches', false ) ||
            ( get_theme_mod( 'enable_swatch', 1 ) && class_exists( 'St_Woo_Swatches_Base' ) )
        ) {

            $html_tag = 'div';

//			if ( function_exists('etheme_enqueue_style') )
//                etheme_enqueue_style( "swatches-style");

            $class = 'st-swatch-size-large';
            $subtype      = '';

            $sw_shape = get_theme_mod('swatch_shape', 'default');
            $sw_custom_shape = $sw_shape != 'default' ? $sw_shape : false;

            if ( strpos( $attribute_type, '-sq') !== false ) {
                $et_attribute_type = str_replace( '-sq', '', $attribute_type );
                if ( !$sw_custom_shape || $sw_custom_shape == 'square' ) {
                    $class .= ' st-swatch-shape-square';
                    $subtype      = 'subtype-square';
                }
                else if ( $sw_custom_shape == 'circle' ) {
                    $class .= ' st-swatch-shape-circle';
                }
            } else {
                $et_attribute_type = $attribute_type;
                if ( !$sw_custom_shape || $sw_custom_shape == 'circle' ) {
                    $class .= ' st-swatch-shape-circle';
                }
            }

            $html .= '<ul class="st-swatch-preview st-color-swatch '. esc_attr( $class ). '">';

            foreach( $terms as $taxonomy ) {
                if ( !is_object($taxonomy) ) continue;
                $decoded_slug = urldecode( $taxonomy->slug );
                $is_current_term = in_array( $decoded_slug, $current_filter, true );

                if ( $filter_by_counts && ! array_key_exists( $taxonomy->term_id, $term_counts ) && ( ! $is_current_term || $has_other_active_filters ) ) {
                    continue;
                }

                $visible_terms++;
                $metadata    = get_term_meta( $taxonomy->term_id, '', true );
                $data_tooltip = $taxonomy->name;
                $data_slug = $taxonomy->slug;
                $link_class = 'filter-item';
                $validator_hidden_text = '<span class="screen-reader-text hidden">'.$data_tooltip.'</span>';
                $li_class = $is_current_term ? ' selected chosen' : '';
                // ! Generate html
                switch ( $et_attribute_type ) {
                    case 'st-color-swatch':
                        $value = ( isset( $metadata['st-color-swatch'] ) && isset( $metadata['st-color-swatch'][0] ) ) ? $metadata['st-color-swatch'][0] : '#fff';
                        $html .= '<li class="type-color ' . $subtype . $li_class . '"  data-tooltip="'.$data_tooltip.'">
                        <a class="'.$link_class.'" data-value="'.$data_slug.'">
                            <span class="st-custom-attribute" style="'. esc_attr( $this->generate_gradient_color_css($value) ) .'">
                                '.$validator_hidden_text.'
                            </span>
                        </a></li>';
                        break;

                    case 'st-image-swatch':
                        $value = ( isset( $metadata['st-image-swatch'] ) && isset( $metadata['st-image-swatch'][0] ) ) ? $metadata['st-image-swatch'][0] : false;
                        $image = ( $value ) ? wp_get_attachment_image( $value, apply_filters('sten_wc_filter_image_swatch_size', 'thumbnail') ) : wc_placeholder_img();
                        $html .=
                            '<li class="type-image ' . $subtype . $li_class . '"  data-tooltip="'.$data_tooltip.'">
                            <a class="'.$link_class.'" data-value="'.$data_slug.'">
                                <span class="st-custom-attribute">'
                            . $image . $validator_hidden_text.
                            '</span>
                            </a>
                        </li>';
                        break;

                    case 'st-label-swatch':
                        $value = ( isset( $metadata['st-label-swatch'] ) && $metadata['st-label-swatch'][0] ) ? $metadata['st-label-swatch'][0] : false;

                        if ( ! $value ) {
                            $value = $taxonomy->name;
                        }

                        $html .= '<li class="type-label ' . $subtype . $li_class . '"><a class="'.$link_class.'" data-value="'.$data_slug.'"><span class="st-custom-attribute">' . $value . '</span></a></li>';
                        break;

                    default:
                        $html .= '<li class="type-select ' . $li_class . '"><a class="'.$link_class.'" data-value="'.$data_slug.'"><span class="st-custom-attribute">' . $taxonomy->name . '</span></a></li>';
                        break;
                }
            }

            $html .= '</ul>';

        }

        else {
            foreach( $terms as $taxonomy ) {
                $decoded_slug = urldecode( $taxonomy->slug );
                $is_current_term = in_array( $decoded_slug, $current_filter, true );

                if ( $filter_by_counts && ! array_key_exists( $taxonomy->term_id, $term_counts ) && ( ! $is_current_term || $has_other_active_filters ) ) {
                    continue;
                }

                $visible_terms++;
                $li_class = $is_current_term ? ' class="selected chosen"' : '';
                $html .=
                    '<li'.$li_class.'>'.
                    '<span class="filter-item" data-value="'.$taxonomy->slug.'">'.
                    $taxonomy->name .
                    '</span>'.
                    '</li>';
            }
        }

        if ( ! $visible_terms ) {
            return;
        }

        $attr_args = array(
            'name' => 'filter_'.$settings['attribute'],
            'html_tag' => $html_tag,
            'query_type_name' => 'query_type_'.$settings['attribute'],
            'query_type_value' => $settings['attribute_query_type'],
        );

        if ( !!$settings['items_limit'] )
            $attr_args['attr'] = array('data-limit="'.$settings['items_limit'].'"');

        $this->render_item_content(
            $settings,
            $html,
            $attr_args
        );
    }

    /**
     * Render Taxonomy filter item.
     *
     * @param $settings
     * @return void
     *
     * @since 4.0.8
     *
     */
    public function taxonomy_filter_template( $settings ) {
        global $wp_query;

        $list_args = [
            'taxonomy'           => $settings['filter_type'],
            'hide_empty'         => !!$settings[$settings['filter_type'].'_hide_empty'],
            'title_li'           => false,
            'show_count' => !!$settings[$settings['filter_type'].'_show_count'],
            'use_desc_for_title' => false,
            'echo'               => false,
        ];

        $filter_settings = apply_filters( 'et_taxonomy_filter_template_settings', array(
            'brand_depend_category' => false
        ) );

        switch ($settings['filter_type']) {
            case 'product_tag':
                $list_args['show_option_none'] = __('No product tags', 'xstore-core');
                break;
            case 'brand':
                $list_args['show_option_none'] = __('No brands', 'xstore-core');
                break;
            default:
                break;
        }

        if ( isset($settings[$settings['filter_type'].'_hierarchical']) ) {
            $list_args['hierarchical'] = !!$settings[$settings['filter_type'].'_hierarchical'];
        }

        $cat_ancestors = [];

        $list_args['current_category_ancestors'] = $cat_ancestors;

        if (
            $filter_settings['brand_depend_category']
            && $settings['filter_type'] === 'brand'
            && is_tax('product_cat')
        ) {
            $cat = get_queried_object();
            if ( $cat && ! is_wp_error($cat) && ! empty($cat->term_id) ) {

                $product_ids = get_posts([
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    'fields'         => 'ids',
                    'posts_per_page' => -1,
                    'no_found_rows'  => true,
                    'tax_query'      => [
                        [
                            'taxonomy'         => 'product_cat',
                            'field'            => 'term_id',
                            'terms'            => (int) $cat->term_id,
                            'include_children' => true,
                        ]
                    ],
                ]);

                $brand_ids = [];
                if ( ! empty($product_ids) ) {
                    $brand_ids = wp_get_object_terms($product_ids, 'brand', [
                        'fields' => 'ids',
                    ]);
                    $brand_ids = array_values(array_unique(array_map('intval', (array)$brand_ids)));
                }

                if ( ! empty($brand_ids) ) {
                    $list_args['include'] = $brand_ids;
                } else {
                    $list_args['include'] = [0]; 
                }
            }
        }

        $taxonomy_terms = get_terms(
            array(
                'taxonomy'   => $settings['filter_type'],
                'hide_empty' => !!$settings[$settings['filter_type'].'_hide_empty'],
            )
        );

        if ( ! is_wp_error( $taxonomy_terms ) && count( $taxonomy_terms ) ) {
            $term_counts = $this->get_filtered_term_product_counts( wp_list_pluck( $taxonomy_terms, 'term_id' ), $settings['filter_type'], 'or' );
            $current_filter = $this->get_current_taxonomy_filter( $settings['filter_type'] );
            $filter_by_counts = is_product_category() || is_tax( 'brand' ) || is_product_tag() || is_search() || $this->has_active_filters();
            $has_other_active_filters = $this->has_active_filters_except_taxonomy( $settings['filter_type'] );

            if ( $filter_by_counts ) {
                $include_terms = array();

                foreach ( $taxonomy_terms as $term ) {
                    $is_current_term = in_array( urldecode( $term->slug ), $current_filter, true );

                    if ( array_key_exists( $term->term_id, $term_counts ) || ( $is_current_term && ! $has_other_active_filters ) ) {
                        $include_terms[] = (int) $term->term_id;
                    }
                }

                if ( isset( $list_args['include'] ) ) {
                    $include_terms = array_values( array_intersect( wp_parse_id_list( $list_args['include'] ), $include_terms ) );
                }

                $list_args['include'] = count( $include_terms ) ? $include_terms : array( 0 );
            }
        }


        $extra = array(
            'attr' => array(
                'data-limit="1"'
            )
        );

        // product_cat || product_tag
        if ( get_query_var( $settings['filter_type'] ) ) {
            $term = get_term_by('slug', get_query_var( $settings['filter_type'] ), $settings['filter_type']);
            $extra['is_active'] = true;
            $extra['active']['value'] = get_query_var( $settings['filter_type'] );
            $extra['active']['label'] = $term->name;
        }

        add_filter( 'category_list_link_attributes', array($this, 'filter_wp_list_categories'), 10, 2 );

        $this->render_item_content($settings,  wp_list_categories( $list_args ), $extra);

        remove_filter( 'category_list_link_attributes', array($this, 'filter_wp_list_categories'), 10, 2 );

    }

    public function on_sale_filter_template( $settings, $link ) {
        $html = '<li>';
        $base_link   = esc_url_raw( remove_query_arg( array( 'on_sale', 'sale_status' ), $link ) );
        $is_sale_active = $this->is_sale_filter_active();
        $toggle_link = ! $is_sale_active ? add_query_arg( 'on_sale', '1', $base_link ) : $base_link;
        $label = esc_html__('Display sale products only', 'xstore-core');

        $html .= '<a href="'. esc_url( $toggle_link ) .'" data-value="1" class="filter-item">'.$label.'</a>';
        $html .= '</li>';

        $this->render_item_content(
            $settings,
            $html,
            array(
                'name' => 'sale_status',
                'is_active' => $is_sale_active,
                'active' => array(
                    'value' => '1',
                    'label' => $label,
                ),
            )
        );
    }

    /**
     * Get Price scripts.
     *
     * @param $settings
     * @param $link
     * @return void
     *
     * @since 4.0.8
     *
     */
    protected function get_price_scripts() {
        wp_localize_script(
            'wc-price-slider',
            'woocommerce_price_slider_params',
            [
                'currency_format_num_decimals' => 0,
                'currency_format_symbol'       => get_woocommerce_currency_symbol(),
                'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
                'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
                'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
            ]
        );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'wc-jquery-ui-touchpunch' );
        wp_enqueue_script( 'wc-accounting' );
        wp_enqueue_script( 'wc-price-slider' );
    }

    /**
     * Get filtered min price for current products.
     *
     * @return int
     *
     * @since 4.0.8
     *
     */
    protected function get_filtered_price() {
        global $wpdb;

        $args       = ( function_exists( 'WC' ) && WC()->query && is_object( WC()->query->get_main_query() ) ) ? WC()->query->get_main_query()->query_vars : array();
        $tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
        $meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

        if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
            $tax_query[] = array(
                'taxonomy' => $args['taxonomy'],
                'terms'    => array( $args['term'] ),
                'field'    => 'slug',
            );
        }

        $tax_query = $this->add_active_filters_to_tax_query( $tax_query );

        foreach ( $meta_query + $tax_query as $key => $query ) {
            if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
                unset( $meta_query[ $key ] );
            }
        }

        $meta_query = new \WP_Meta_Query( $meta_query );
        $tax_query  = new \WP_Tax_Query( $tax_query );
        $meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        $tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
        $sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
        $sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
        $sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
			AND {$wpdb->posts}.post_status = 'publish'
			AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
			AND price_meta.meta_value > '' ";
        $sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

        $product_ids = $this->get_filtered_product_ids();
        if ( is_array( $product_ids ) ) {
            if ( ! count( $product_ids ) ) {
                return (object) array(
                    'min_price' => null,
                    'max_price' => null,
                );
            }

            $sql .= " AND {$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")";
        }

        $search = \WC_Query::get_main_search_query_sql();
        if ( $search ) {
            $sql .= ' AND ' . $search;
        }

        $sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );
        return $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
    }

    protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type = 'and' ) {
        global $wpdb;

        if ( ! count( $term_ids ) ) {
            return array();
        }

        $tax_query  = \WC_Query::get_main_tax_query();
        $meta_query = \WC_Query::get_main_meta_query();
        $tax_query  = $this->add_active_filters_to_tax_query( $tax_query );

        if ( 'or' === $query_type || $this->has_current_taxonomy_filter( $taxonomy ) ) {
            foreach ( $tax_query as $key => $query ) {
                if ( is_array( $query ) && isset( $query['taxonomy'] ) && $taxonomy === $query['taxonomy'] ) {
                    unset( $tax_query[ $key ] );
                }
            }
        }

        $meta_query     = new \WP_Meta_Query( $meta_query );
        $tax_query      = new \WP_Tax_Query( $tax_query );
        $meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        $tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
        $term_ids_sql   = '(' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

        $query           = array();
        $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) AS term_count, terms.term_id AS term_count_id";
        $query['from']   = "FROM {$wpdb->posts}";
        $query['join']   = "
            INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
            INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
            INNER JOIN {$wpdb->terms} AS terms USING( term_id )
            " . $tax_query_sql['join'] . $meta_query_sql['join'];
        $query['where'] = "
            WHERE {$wpdb->posts}.post_type IN ( 'product' )
            AND {$wpdb->posts}.post_status = 'publish'
            {$tax_query_sql['where']} {$meta_query_sql['where']}
            AND terms.term_id IN $term_ids_sql";

        $product_ids = $this->get_filtered_product_ids();
        if ( is_array( $product_ids ) ) {
            if ( ! count( $product_ids ) ) {
                return array();
            }

            $query['where'] .= " AND {$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")";
        }

        $search = \WC_Query::get_main_search_query_sql();
        if ( $search ) {
            $query['where'] .= ' AND ' . $search;
        }

        $query['group_by'] = 'GROUP BY terms.term_id';
        $query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
        $query_sql         = implode( ' ', $query );
        $query_hash        = md5( $query_sql );
        $cache             = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );

        if ( true === $cache ) {
            $cached_counts = (array) get_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ) );
        } else {
            $cached_counts = array();
        }

        if ( ! isset( $cached_counts[ $query_hash ] ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $results                      = $wpdb->get_results( $query_sql, ARRAY_A );
            $cached_counts[ $query_hash ] = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );

            if ( true === $cache ) {
                set_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
            }
        }

        return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
    }

    protected function has_current_taxonomy_filter( $taxonomy ) {
        return count( $this->get_current_taxonomy_filter( $taxonomy ) ) > 0;
    }

    protected function get_current_taxonomy_filter( $taxonomy ) {
        $filter_name = sanitize_title( str_replace( 'pa_', '', urldecode( $taxonomy ) ) );
        $filter_keys = array_unique(
            array_filter(
                array(
                    'filter_' . $filter_name,
                    $taxonomy,
                    'product_cat' === $taxonomy ? 'filter_cat' : '',
                    'brand' === $taxonomy ? 'filter_brand' : '',
                )
            )
        );

        $current_filter = array();

        foreach ( $filter_keys as $filter_key ) {
            if ( isset( $_GET[ $filter_key ] ) && '' !== $_GET[ $filter_key ] ) {
                $current_filter = array_merge( $current_filter, $this->sanitize_filter_slugs( $_GET[ $filter_key ] ) );
            }
        }

        $query_var = get_query_var( $taxonomy );
        if ( $query_var ) {
            $current_filter = array_merge( $current_filter, $this->sanitize_filter_slugs( $query_var ) );
        }

        return array_values( array_unique( $current_filter ) );
    }

    protected function has_active_filters() {
        foreach ( $_GET as $query_arg_key => $query_arg_value ) {
            if ( empty( $query_arg_value ) ) {
                continue;
            }

            if (
                strpos( $query_arg_key, 'filter_' ) === 0
                || in_array( $query_arg_key, array( 'sale_status', 'on_sale', 'min_price', 'max_price', 'rating_filter' ), true )
                || $this->get_taxonomy_from_query_arg( $query_arg_key )
            ) {
                return true;
            }
        }

        return $this->is_sale_filter_active();
    }

    protected function has_active_filters_except_taxonomy( $taxonomy ) {
        $filter_name = sanitize_title( str_replace( 'pa_', '', urldecode( $taxonomy ) ) );
        $ignored_keys = array_unique(
            array_filter(
                array(
                    $taxonomy,
                    'filter_' . $filter_name,
                    'query_type_' . $filter_name,
                    'product_cat' === $taxonomy ? 'filter_cat' : '',
                    'brand' === $taxonomy ? 'filter_brand' : '',
                )
            )
        );

        foreach ( $_GET as $query_arg_key => $query_arg_value ) {
            if ( empty( $query_arg_value ) || in_array( $query_arg_key, $ignored_keys, true ) ) {
                continue;
            }

            $sale_orderby_values = array( 'sale', 'onsale', 'on_sale', 'on-sale', 'sale_products', 'sale-products', 'sale_status' );
            $is_sale_query_arg = ( 'orderby' === $query_arg_key && in_array( wc_clean( wp_unslash( $query_arg_value ) ), $sale_orderby_values, true ) )
                || ( 'stock_status' === $query_arg_key && in_array( 'onsale', array_map( 'sanitize_title', explode( ',', str_replace( '_', '', wc_clean( wp_unslash( $query_arg_value ) ) ) ) ), true ) );

            if (
                strpos( $query_arg_key, 'filter_' ) === 0
                || in_array( $query_arg_key, array( 'sale_status', 'on_sale', 'min_price', 'max_price', 'rating_filter' ), true )
                || $is_sale_query_arg
                || $this->get_taxonomy_from_query_arg( $query_arg_key )
            ) {
                return true;
            }
        }

        return is_product_category() || is_product_tag() || is_search() || ( is_tax( 'brand' ) && 'brand' !== $taxonomy );
    }

    protected function add_active_filters_to_tax_query( $tax_query ) {
        $existing_taxonomies = array();

        foreach ( $tax_query as $query ) {
            if ( is_array( $query ) && isset( $query['taxonomy'] ) ) {
                $existing_taxonomies[] = $query['taxonomy'];
            }
        }

        foreach ( $_GET as $query_arg_key => $query_arg_value ) {
            if ( '' === $query_arg_value ) {
                continue;
            }

            $filter_taxonomy = $this->get_taxonomy_from_query_arg( $query_arg_key );

            if ( ! $filter_taxonomy || in_array( $filter_taxonomy, $existing_taxonomies, true ) ) {
                continue;
            }

            $terms = $this->sanitize_filter_slugs( $query_arg_value );

            if ( empty( $terms ) ) {
                continue;
            }

            $query_type_key = 'query_type_' . sanitize_title( str_replace( 'pa_', '', $filter_taxonomy ) );
            $query_type     = isset( $_GET[ $query_type_key ] ) ? wc_clean( wp_unslash( $_GET[ $query_type_key ] ) ) : 'and';
            $operator       = in_array( $filter_taxonomy, array( 'brand', 'product_cat', 'product_tag' ), true ) ? 'IN' : ( 'and' === strtolower( $query_type ) ? 'AND' : 'IN' );

            $tax_query[] = array(
                'taxonomy' => $filter_taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => $operator,
            );

            $existing_taxonomies[] = $filter_taxonomy;
        }

        return $tax_query;
    }

    protected function get_taxonomy_from_query_arg( $query_arg_key ) {
        if ( strpos( $query_arg_key, 'filter_' ) === 0 ) {
            $filter_name = sanitize_title( substr( $query_arg_key, 7 ) );

            if ( 'cat' === $filter_name ) {
                return 'product_cat';
            }

            if ( 'brand' === $filter_name && taxonomy_exists( 'brand' ) ) {
                return 'brand';
            }

            if ( taxonomy_exists( 'pa_' . $filter_name ) ) {
                return 'pa_' . $filter_name;
            }

            return taxonomy_exists( $filter_name ) ? $filter_name : false;
        }

        return taxonomy_exists( $query_arg_key ) ? $query_arg_key : false;
    }

    protected function sanitize_filter_slugs( $value ) {
        $value = wc_clean( wp_unslash( $value ) );
        $value = is_array( $value ) ? $value : explode( ',', $value );

        return array_filter( array_map( 'sanitize_title', $value ) );
    }

    protected function get_filtered_product_ids() {
        $sale_product_ids  = $this->get_sale_product_ids();
        $query_product_ids = false;

        if ( function_exists( 'WC' ) && WC()->query && is_object( WC()->query->get_main_query() ) ) {
            $post__in = WC()->query->get_main_query()->get( 'post__in' );

            if ( ! empty( $post__in ) ) {
                $query_product_ids = wp_parse_id_list( $post__in );
            }
        }

        if ( is_array( $sale_product_ids ) && is_array( $query_product_ids ) ) {
            return array_values( array_intersect( $sale_product_ids, $query_product_ids ) );
        }

        if ( is_array( $sale_product_ids ) ) {
            return $sale_product_ids;
        }

        if ( is_array( $query_product_ids ) ) {
            return $query_product_ids;
        }

        return false;
    }

    protected function get_sale_product_ids( $force = false ) {
        if ( ! $force && ! $this->is_sale_filter_active() ) {
            return false;
        }

        static $product_ids_on_sale = null;

        if ( null === $product_ids_on_sale ) {
            $product_ids_on_sale = array();
            $loaded_from_data_store = false;

            if ( class_exists( 'WC_Data_Store' ) ) {
                $data_store = \WC_Data_Store::load( 'product' );

                if ( is_callable( array( $data_store, 'get_on_sale_products' ) ) ) {
                    $on_sale_products = $data_store->get_on_sale_products();
                    $product_ids_on_sale = wp_parse_id_list(
                        array_merge(
                            wp_list_pluck( $on_sale_products, 'id' ),
                            array_diff( wp_list_pluck( $on_sale_products, 'parent_id' ), array( 0 ) )
                        )
                    );
                    $loaded_from_data_store = true;
                }
            }

            if ( ! $loaded_from_data_store ) {
                $product_ids_on_sale = array_map( 'absint', wc_get_product_ids_on_sale() );
            }
        }

        return $product_ids_on_sale;
    }

    protected function is_sale_filter_active() {
        $sale_status = isset( $_GET['sale_status'] ) ? wc_clean( wp_unslash( $_GET['sale_status'] ) ) : '';
        $on_sale     = isset( $_GET['on_sale'] ) ? wc_clean( wp_unslash( $_GET['on_sale'] ) ) : '';
        $stock_status = isset( $_GET['stock_status'] ) ? wc_clean( wp_unslash( $_GET['stock_status'] ) ) : '';
        $orderby     = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';
        $sale_orderby_values = array( 'sale', 'onsale', 'on_sale', 'on-sale', 'sale_products', 'sale-products', 'sale_status' );

        return ! empty( $sale_status )
            || ! empty( $on_sale )
            || in_array( $orderby, $sale_orderby_values, true )
            || in_array( 'onsale', array_map( 'sanitize_title', explode( ',', str_replace( '_', '', $stock_status ) ) ), true );
    }

    /**
     * Add class for wp list categories via filter.
     *
     * @param $atts
     * @param $category
     * @return mixed
     *
     * @since 4.0.8
     *
     */
    public function filter_wp_list_categories( $atts, $category ) {
        if ( $category->slug )
            $atts['data-value'] = $category->slug;

        $atts['data-name'] = $category->name;

        $atts['class'] = isset($atts['class']) ? isset($atts['class']). ' filter-item' : 'filter-item';

        return $atts;
    }

    /**
     * Render Item Content.
     *
     * @param       $settings
     * @param       $html
     * @param array $extra
     * @return void
     *
     * @since 4.0.8
     *
     */
    public function render_item_content($settings, $html, $extra = array()) {
        $extra = shortcode_atts(array(
            'name' => $settings['filter_type'],
            'required' => $settings['required_field'],
            'html_tag' => 'ul',
            'result_input' => true,
            'attr' => array(),
            'query_type_name' => false,
            'query_type_value' => false,
            'is_active' => false,
            'active' => array(
                'value' => '',
                'label' => ''
            )
        ), $extra);

        // if (condition) {
        //     // code...
        // }

        // filter_type

        // var_dump($settings);

        if ( $extra['required'] )
            $extra['attr'][] = 'data-required="yes"';

        ?>
    <div class="etheme-product-filters-item" <?php echo implode(' ', $extra['attr']); ?>>
        <?php if ( $extra['result_input']) :
            // tweak for setting empty input name for price to prevent sending param in url but keep input for JS code
            ?>
            <input type="hidden" class="result-input" name="<?php echo $settings['filter_type'] != 'price' ? $extra['name'] : ''; ?>" value="<?php echo $extra['is_active'] ? $extra['active']['value'] : ''; ?>">
        <?php endif; ?>
        <?php if ( $extra['query_type_name'] && $extra['query_type_value'] ) : ?>
            <input type="hidden" name="<?php echo $extra['query_type_name']; ?>" value="<?php echo $extra['query_type_value']; ?>">
        <?php endif; ?>
        <div class="etheme-product-filters-item-title">
                    <span class="title-text">
                        <?php echo esc_html( $settings['filter_title'] ) . ($extra['required'] ? ' <span class="required">*</span> ' : ''); ?>
                    </span>

            <span class="etheme-product-filters-quick-results">
                    <?php if ( $extra['is_active'] ) : ?>
                        <span data-q-value="<?php echo esc_attr($extra['active']['value']); ?>">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width=".75em" height=".75em" viewBox="0 0 24 24">
                                <path d="M13.056 12l10.728-10.704c0.144-0.144 0.216-0.336 0.216-0.552 0-0.192-0.072-0.384-0.216-0.528-0.144-0.12-0.336-0.216-0.528-0.216 0 0 0 0 0 0-0.192 0-0.408 0.072-0.528 0.216l-10.728 10.728-10.704-10.728c-0.288-0.288-0.768-0.288-1.056 0-0.168 0.144-0.24 0.336-0.24 0.528 0 0.216 0.072 0.408 0.216 0.552l10.728 10.704-10.728 10.704c-0.144 0.144-0.216 0.336-0.216 0.552s0.072 0.384 0.216 0.528c0.288 0.288 0.768 0.288 1.056 0l10.728-10.728 10.704 10.704c0.144 0.144 0.336 0.216 0.528 0.216s0.384-0.072 0.528-0.216c0.144-0.144 0.216-0.336 0.216-0.528s-0.072-0.384-0.216-0.528l-10.704-10.704z"></path>
                            </svg><?php echo $extra['active']['label'] ?></span>
                    <?php endif; ?>
                <?php if ( $settings['filter_type'] == 'price' && $settings['price_type'] != 'ranges' ) { ?>
                    <span style="display:none;" data-q-value="price">
                            <span class="from"></span> &mdash; <span class="to"></span>
                        </span>
                <?php } ?>
                </span>
        </div>

        <<?php echo $extra['html_tag']; ?> class="etheme-product-filters-item-content" style="display: none">
        <?php echo $html; ?>
        </<?php echo $extra['html_tag']; ?>>
        </div>
        <?php
    }

    /**
     * Return filtered product taxonomies for filters.
     *
     * @since 4.0.8
     *
     * @return mixed
     */
    public function product_taxonomies_to_filter() {
        return apply_filters('etheme_product_filters_taxonomies', array(
            'product_cat' => esc_html__('Categories', 'xstore-core'),
            'product_tag' => esc_html__('Product tag', 'xstore-core'),
        ) );
    }

    /**
     * Get product attributes.
     *
     * @since 4.0.8
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_attributes() {
        $output = [
            '' => esc_html__( 'Select', 'xstore-core' ),
        ];

        $taxonomies = wc_get_attribute_taxonomies();

        if ( $taxonomies ) {
            foreach ( $taxonomies as $tax ) {
                $output[ $tax->attribute_name ] = $tax->attribute_name;
            }
        }

        return $output;
    }

    protected function get_range_price($filter) {
        $options = array();
        // Use the default price slider widget.
        if ( empty( $filter['price_ranges'] ) ) {
            return $options;
        }

        $ranges = explode( "\n", $filter['price_ranges'] );

        foreach ( $ranges as $range ) {
            $range       = trim( $range );
            $prices      = explode( '-', $range );
            $price_range = array( 'min' => '', 'max' => '' );
            $name        = array();

            if ( count( $prices ) > 1 ) {
                $price_range['min'] = preg_match( '/\d+\.?\d+/', current( $prices ), $match ) ? floatval( $match[0] ) : 0;
                $price_range['max'] = preg_match( '/\d+\.?\d+/', end( $prices ), $match ) ? floatval( $match[0] ) : 0;
                reset( $prices );
                $name['min'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['min'] ) . '</span>', current( $prices ) );
                $name['max'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['max'] ) . '</span>', end( $prices ) );
            } elseif ( substr( $range, 0, 1 ) === '<' ) {
                $price_range['max'] = preg_match( '/\d+\.?\d+/', end( $prices ), $match ) ? floatval( $match[0] ) : 0;
                $name['max'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['max'] ) . '</span>', ltrim( end( $prices ), '< ' ) );
            } else {
                $price_range['min'] = preg_match( '/\d+\.?\d+/', current( $prices ), $match ) ? floatval( $match[0] ) : 0;
                $name['min'] = preg_replace( '/\d+\.?\d+/', '<span class="price">' . wc_price( $price_range['min'] ) . '</span>', current( $prices ) );
            }

            $options[] = array(
                'name'  => implode( ' - ', $name ),
                'count' => 0,
                'range' => $price_range,
                'level' => 0,
            );
        }

        return $options;
    }

    public function render_range_price($args) {
        $args = wp_parse_args( $args, array(
            'name'        => '',
            'current'     => array(),
            'options'     => array(),
            'attribute'   => '',
            'multiple'    => false,
            'show_counts' => false,
        ) );

        if ( empty( $args['options'] ) ) {
            return;
        }

        $current_page_url = home_url();
        $base_link          = remove_query_arg(array('min_price', 'max_price'), $current_page_url);
        echo '<ul class="prices-list">';
        foreach ( $args['options'] as $option ) {
            printf(
                '<li class="price-list-item %s"><a class="filter-item" href="%s" data-value="%s">%s</a>%s</li>',
                $args['current']['min'] == $option['range']['min'] && $args['current']['max'] == $option['range']['max'] ? 'chosen' : '',
                add_query_arg(array('min_price' => $option['range']['min'], 'max_price' => $option['range']['max']), $base_link),
                esc_attr( implode('-', $option['range']) ),
                $option['name'],
                $args['show_counts'] ? '<span class="products-filter__count counter">' . $option['count'] . '</span>' : ''
            );
        }
        echo '</ul>';

        echo '<div class="price-filter-box">';

        echo '<span class="filter-item filter-item-ghost hidden" data-value="">'.esc_html__('Custom price value', 'xstore-core').'</span>';

        printf(
            '<input type="number" name="min_%s" min="0" value="%s" placeholder="%s">',
            esc_attr( $args['name'] ),
            esc_attr( $args['current']['min'] ),
            esc_html__( 'Min', 'xstore-core' )
        );

        echo '<span class="line"></span>';

        printf(
            '<input type="number" name="max_%s" min="0" value="%s" placeholder="%s">',
            esc_attr( $args['name'] ),
            esc_attr( $args['current']['max'] ),
            esc_html__( 'Max', 'xstore-core' )
        );

        echo '<button type="submit" value="' . esc_attr__( 'Apply', 'xstore-core' ) . '" data-base-url="'.$base_link.'" class="btn btn-black medium">' . $args['button_text'] . '</button>';

        echo '</div>';
    }

    /**
     * Generate color style
     */
    public function generate_gradient_color_css($color) {
        $style = '';
        if (is_serialized($color)){
            $color = unserialize($color);
            $gradient_direction = get_theme_mod('swatch_multicolor_design', 'right');
            if ( in_array($gradient_direction, array('diagonal_1', 'diagonal_2'))) {
                $gradient_direction = str_replace(array('diagonal_1', 'diagonal_2'), array('bottom right', 'bottom left'), $gradient_direction);
            }
            $style .= 'background: linear-gradient( to ';
            $style .= $gradient_direction . ',';
            $percent = 100/count($color);

            foreach($color as $color_key => $color_value){
                $style .= $color_value . ' ' . $percent .'% '. ( $percent+$percent*$color_key ) . '%';
                if ($color_key != count($color)-1){
                    $style .= ',';
                }
            }

            $style .= ');';
        } else {
            $style .= 'background-color:' . $color . ';';
        }
        return $style;
    }
}
