<?php

namespace ETC\App\Controllers\Elementor\Theme_Builder\WooCommerce\Single_Product;

/**
 * Product price widget.
 *
 * @since      5.2
 * @package    ETC
 * @subpackage ETC/Controllers/Elementor
 */
class Product_Price extends \ElementorPro\Modules\Woocommerce\Widgets\Product_Price
{

    /**
     * Get widget name.
     *
     * @return string Widget name.
     * @since 5.2
     * @access public
     *
     */
    public function get_name()
    {
        return 'woocommerce-product-etheme_price';
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     * @since 5.2
     * @access public
     *
     */
    public function get_icon()
    {
        return 'eight_theme-elementor-icon et-xstore-coreduct-price et-xstore-coreduct-widget-icon-only';
    }

    /**
     * Help link.
     *
     * @return string
     * @since 5.2
     *
     */
    public function get_custom_help_url()
    {
        return etheme_documentation_url('122-elementor-live-copy-option', false);
    }

    /**
     * Register widget controls.
     *
     * @since 5.2
     * @access protected
     */
    protected function register_controls()
    {
        parent::register_controls();

        $this->start_injection([
                'type' => 'section',
                'at' => 'end',
                'of' => 'section_price_style',
        ]);

        $this->add_control(
                'sale_price_badge_block',
                [
                        'label' => esc_html__('Sales Badge', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::SWITCHER,
                        'description' => esc_html__('Works only on on-sale products', 'xstore-core'),
                        'separator' => 'before',
                ]
        );

        $this->add_control(
                'sale_price_badge_block_position',
                [
                        'label' => __('Sale Badge Position', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'default' => 'after_inline',
                        'options' => [
                                'before' => __('Before', 'xstore-core'),
                                'before_inline' => __('Before (inline)', 'xstore-core'),
                                'after' => __('After', 'xstore-core'),
                                'after_inline' => __('After (inline)', 'xstore-core'),
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ],
                ]
        );

        $this->add_control(
                'sale_price_badge_title',
                [
                        'label' => __('Title', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'description' => esc_html__('Text that will be shown: {percentage} will be replaced by calculated count between Regular and Sale prices values', 'xstore-core'),
                        'default' => esc_html__('{percentage} Off Today!', 'xstore-core'),
                        'dynamic' => [
                                'active' => true,
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ],
                ]
        );

        $this->add_control(
                'selected_icon',
                [
                        'label' => __('Icon', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::ICONS,
                        'skin' => 'inline',
                        'fa4compatibility' => 'icon',
                        'label_block' => false,
                        'default' => [
                                'value' => 'et-icon et-coupon',
                                'library' => 'xstore-icons',
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ],
                ]
        );

        $this->add_control(
                'icon_align',
                [
                        'label' => __('Icon Position', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'default' => 'left',
                        'options' => [
                                'left' => __('Before', 'xstore-core'),
                                'right' => __('After', 'xstore-core'),
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => '',
                                'selected_icon[value]!' => '',
                        ],
                ]
        );

        $this->add_control(
                'sale_price_icon_indent',
                [
                        'label' => esc_html__( 'Icon Spacing', 'xstore-core' ),
                        'type' => \Elementor\Controls_Manager::SLIDER,
                        'range' => [
                                'px' => [
                                        'max' => 50,
                                ],
                        ],
                        'default' => [
                                'size' => 12
                        ],
                        'selectors' => [
                                '{{WRAPPER}} .etheme-price-sale-badge' => 'gap: {{SIZE}}{{UNIT}};',
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => '',
                                'sale_price_badge_title!' => '',
                                'selected_icon[value]!' => '',
                        ],
                ]
        );

        $this->add_control(
                'sale_price_badge_color',
                [
                        'label' => esc_html__('Color', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::COLOR,
                        'separator' => 'before',
                        'default' => '#fff',
                        'selectors' => [
                                '{{WRAPPER}} .etheme-price-sale-badge' => 'color: {{VALUE}};',
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ],
                ]
        );

        $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                        'name' => 'sale_price_badge_background',
                        'types' => ['classic', 'gradient'],
                        'fields_options' => [
                                'background' => [
                                        'default' => 'classic',
                                ],
                                'color' => [
                                        'default' => '#000000',
                                ],
                        ],
                        'selector' => '{{WRAPPER}} .etheme-price-sale-badge',
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ]
                ]
        );

        $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                        'name' => 'sale_price_badge_typography',
                        'selector' => '{{WRAPPER}} .etheme-price-sale-badge',
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ],
                ]
        );

        $this->add_responsive_control(
                'sale_price_badge_spacing',
                [
                        'label' => esc_html__('Spacing', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::SLIDER,
                        'size_units' => ['px', 'em', 'rem', 'custom'],
                        'range' => [
                                'px' => [
                                        'max' => 100,
                                ],
                                'em' => [
                                        'max' => 10,
                                ],
                                'rem' => [
                                        'max' => 10,
                                ],
                        ],
                        'selectors' => [
                                '{{WRAPPER}} .etheme-price-wrapper' => '--badge-spacing: {{SIZE}}{{UNIT}}',
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ],
                ]
        );

        $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                        'name' => 'sale_price_badge_border',
                        'selector' => '{{WRAPPER}} .etheme-price-sale-badge',
                        'separator' => 'before',
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ]
                ]
        );

        $this->add_control(
                'sale_price_badge_border_radius',
                [
                        'label' => __('Border Radius', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::DIMENSIONS,
                        'size_units' => ['px'],
                        'default' => [
                                'unit' => 'px',
                                'top' => '3',
                                'right' => '3',
                                'bottom' => '3',
                                'left' => '3',
                        ],
                        'selectors' => [
                                '{{WRAPPER}} .etheme-price-sale-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ]
                ]
        );

        $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                        'name' => 'sale_price_badge_box_shadow',
                        'selector' => '{{WRAPPER}} .etheme-price-sale-badge',
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ]
                ]
        );

        $this->add_responsive_control(
                'sale_price_badge_padding',
                [
                        'label' => __('Padding', 'xstore-core'),
                        'type' => \Elementor\Controls_Manager::DIMENSIONS,
                        'size_units' => ['px', 'em', '%'],
                        'default' => [
                                'unit' => 'px',
                                'top' => '3',
                                'right' => '12',
                                'bottom' => '3',
                                'left' => '12',
                        ],
                        'selectors' => [
                                '{{WRAPPER}} .etheme-price-sale-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                        'separator' => 'before',
                        'condition' => [
                                'sale_price_badge_block!' => ''
                        ]
                ]
        );

        $this->end_injection();

        $this->remove_control('wc_style_warning');
    }

    protected function render()
    {
        global $product;
        $product = method_exists($this, 'get_product') ? $this->get_product() : wc_get_product();

        if (!$product) {
            return;
        }
        $on_sale = $product->is_on_sale();

        $settings = $this->get_settings_for_display();
        $show_sale_price_badge_block = !!$settings['sale_price_badge_block'];
        if ( $on_sale && $show_sale_price_badge_block ) {
            echo '<div class="etheme-price-wrapper flex-inline flex-wrap">';
        }
        if ($on_sale && $show_sale_price_badge_block && in_array($settings['sale_price_badge_block_position'], array('before', 'before_inline'))) {
            self::render_sale_price_badge_block($product, $settings);
        }

        parent::render();

        if ($on_sale && $show_sale_price_badge_block && in_array($settings['sale_price_badge_block_position'], array('after', 'after_inline'))) {
            self::render_sale_price_badge_block($product, $settings);
        }
        if ( $on_sale && $show_sale_price_badge_block ) {
            echo '</div>';
        }
    }

    protected function render_sale_price_badge_block($product, $settings)
    {
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        $percentage_price = round((($regular_price - $sale_price) / $regular_price) * 100);
        $title = $settings['sale_price_badge_title'];
        ?>
        <span class="etheme-price-sale-badge<?php echo in_array($settings['sale_price_badge_block_position'], array('before_inline', 'after_inline')) ? ' etheme-price-sale-badge-inline' : ''; ?> flex-inline align-items-center">
            <?php if ($settings['icon_align'] == 'left')
                $this->render_icon($settings);
            ?>
            <span class="nowrap"><?php echo str_replace('{percentage}', $percentage_price . '%', $title); ?></span>
            <?php if ($settings['icon_align'] == 'right')
                $this->render_icon($settings);
            ?>
        </span>
        <?php
    }

    protected function render_icon($settings)
    {
        if (!empty($settings['icon']) || !empty($settings['selected_icon']['value'])) :
            \Elementor\Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']);
        endif;
    }
}
