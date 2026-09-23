<?php
namespace ETC\App\Controllers\Shortcodes;

use ETC\App\Controllers\Shortcodes;

/**
 * Button shortcode.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Controllers/Shortcodes
 */
class Button extends Shortcodes {

	function hooks(){}

	function btn_shortcode($atts){

		if ( xstore_notice() )
			return;

		$atts = shortcode_atts( 
			array(
				'title' => 'Button',
				'url' => '#',
				'icon' => '',
				'size' => '',
				'rel' => '',
				'style' => '',
				'et_class' => '',
				'type' => '',
				'target' => ''
		), $atts );
	
		$icon_class = self::sanitize_shortcode_classes( 'et-icon et-' . $atts['icon'] );
		$icon = ( $atts['icon'] != '' ) ? '<i class="' . esc_attr( $icon_class ) . '" style="vertical-align: middle;"></i>' : '';

		if ( $atts['style'] != '') 
			$atts['et_class'] .= ' ' . $atts['style'];

		if ( $atts['type'] != '') 
			$atts['et_class'] .= ' ' . $atts['type'];

		if ( $atts['size'] != '') 
			$atts['et_class'] .= ' ' . $atts['size'];

		$a_attr = array(
			'target="' . esc_attr( $atts['target'] ) . '"',
			'class="' . esc_attr( trim( 'btn ' . self::sanitize_shortcode_classes( $atts['et_class'] ) ) ) . '"',
			'href="' . esc_url( $atts['url'] ) . '"'
		);
		
		if ( $atts['rel'] ) {
			$a_attr[] = 'rel="' . esc_attr( $atts['rel'] ) . '"';
		}


		return '<a ' . implode(' ', $a_attr) . '><span>' . $icon . ' ' . esc_html( $atts['title'] ) . '</span></a>';
	}

}
