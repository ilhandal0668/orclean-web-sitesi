<?php
namespace ETC\App\Controllers\Shortcodes;

use ETC\App\Controllers\Shortcodes;

/**
 * QRCode shortcode.
 *
 * @since      1.4.4
 * @package    ETC
 * @subpackage ETC/Controllers/Shortcodes
 */
class QRCode extends Shortcodes {

    function hooks() {}

    function qrcode_shortcode( $atts, $content = null ) {
        if ( xstore_notice() )
            return;

        $atts = shortcode_atts(array(
            'size' => '128',
            'self_link' => 0,
            'title' => 'QR Code',
            'lightbox' => 0,
            'class' => ''
        ), $atts);

        return $this->etheme_qr_code($content,$atts['title'],$atts['size'],$atts['class'],$atts['self_link'],$atts['lightbox']);
    }

    function etheme_qr_code($text='QR Code', $title = 'QR Code', $size = 128, $class = '', $self_link = false, $lightbox = false ) {
        if( $self_link ) {
            $text = get_permalink();
            if ( ! $text ) {
                $text = home_url( $_SERVER['REQUEST_URI'] );
            }
        }

        $size = (int) $size;
        $image = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&ecc=H&data=' . rawurlencode( $text );

        if( $lightbox )
            $output = '<a href="' . esc_url( $image ) . '" rel="lightbox" class="qr-lighbox ' . esc_attr( $class ) . '"><img src="' . esc_url( $image ) . '" width="' . $size . '" height="' . $size . '" alt="' . esc_attr( $title ) . '" /></a>';
        else
            $output = '<img src="' . esc_url( $image ) . '" width="' . $size . '" height="' . $size . '" alt="' . esc_attr( $title ) . '" class="qr-image ' . esc_attr( $class ) . '" />';

        return $output;
    }

}
