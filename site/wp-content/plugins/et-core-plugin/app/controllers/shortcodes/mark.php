<?php
namespace ETC\App\Controllers\Shortcodes;

use ETC\App\Controllers\Shortcodes;

/**
 * Mark shortcode.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Controllers/Shortcodes
 */
class Mark extends Shortcodes {

    function hooks() {}

    function mark_shortcode( $atts, $content = null ) {
	    if ( xstore_notice() )
		    return;
        $atts = shortcode_atts( array(
           'style' => '',
           'color' => '',
        ), $atts );
	
        if ( function_exists('etheme_enqueue_style') )
	        etheme_enqueue_style('mark-text');

        $style = '';

        if ( ! empty( $atts['color'] ) ) {
            $style_value = self::sanitize_shortcode_style(
                ( ( $atts['style'] == 'paragraph' ) ? '' : 'background-' ) . 'color:' . $atts['color'] . ';'
            );

            if ( '' !== $style_value ) {
                $style = 'style="' . esc_attr( $style_value ) . '"';
            }
        }
       
        return sprintf(
            '<span class="%1$s" %2$s>%3$s</span>',
            esc_attr( trim( 'mark-text ' . self::sanitize_shortcode_classes( $atts['style'] ) ) ),
            $style,
            do_shortcode( shortcode_unautop( $content ) )
        );
    }
}
