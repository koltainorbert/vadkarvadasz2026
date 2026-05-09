<?php
/**
 * Reklámzóna kezelés: 6 pozíció, HTML-alapú, backend beállítható
 * Pozíciók: header_top, header_bottom, sidebar_left, sidebar_right, content_top, footer_top
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Ad_Zones {

    const ZONES = [
        'header_top'      => 'Fejléc felett (970×90 / teljes szélességű)',
        'header_bottom'   => 'Fejléc alatt / navigáció alatt (970×90)',
        'sidebar_left'    => 'Bal oldalsáv (300×250 / 160×600)',
        'sidebar_right'   => 'Jobb oldalsáv (300×250 / 160×600)',
        'content_top'     => 'Tartalom felett (728×90)',
        'footer_top'      => 'Lábléc felett (970×90)',
    ];

    public static function init() {
        // Semmit nem kell itt regisztrálni – a shortcode-okat a VA_Shortcodes kezeli
    }

    /**
     * Visszaadja a zóna HTML tartalmát (opcionálisan wrapper div-vel)
     */
    public static function render( string $zone, bool $echo = true ): string {
        if ( ! array_key_exists( $zone, self::ZONES ) ) return '';

        $html = get_option( 'va_ad_zone_' . $zone, '' );

        // Ha üres és a placeholder megjelenítés be van kapcsolva
        if ( empty( $html ) && get_option( 'va_ad_show_placeholder', '0' ) === '1' ) {
            $label = self::ZONES[ $zone ];
            $html  = '<div class="va-ad-placeholder" style="background:#111;border:1px dashed #ff0000;padding:10px;color:#ff0000;text-align:center;font-size:12px;">' . esc_html( $label ) . ' – reklámzóna (üres)</div>';
        }

        if ( empty( $html ) ) return '';

        // KIZÁRÓLAG az admin által beállított HTML-t rendereljük (megbízott tartalom)
        $output = '<div class="va-ad-zone va-ad-zone--' . esc_attr( $zone ) . '">' . $html . '</div>';

        if ( $echo ) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        return $output;
    }

    /**
     * Összes zóna adatait visszaadja (a settings page-hez)
     */
    public static function get_all(): array {
        $result = [];
        foreach ( self::ZONES as $key => $label ) {
            $result[ $key ] = [
                'label' => $label,
                'html'  => get_option( 'va_ad_zone_' . $key, '' ),
            ];
        }
        return $result;
    }
}
