<?php
/**
 * VA_Mailer – HTML e-mail küldő helper
 * Minden wp_mail hívást ezen keresztül kell indítani.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Mailer {

    /**
     * HTML email küldése branded template-tel.
     *
     * @param string $to       Címzett email
     * @param string $subject  Tárgy
     * @param string $heading  Nagy cím az emailben (pl. "Nyertél az aukción!")
     * @param string $body_html Tartalom HTML (bekezdések, linkek, stb.)
     * @param array  $button   Opcionális CTA gomb: ['label' => '...', 'url' => '...']
     */
    public static function send( string $to, string $subject, string $heading, string $body_html, array $button = [] ): bool {
        $html = self::build( $heading, $body_html, $button );

        add_filter( 'wp_mail_content_type', [ __CLASS__, 'set_html_content_type' ] );
        add_filter( 'wp_mail_from',         [ __CLASS__, 'set_from_email' ] );
        add_filter( 'wp_mail_from_name',    [ __CLASS__, 'set_from_name'  ] );

        $result = wp_mail( $to, $subject, $html );

        remove_filter( 'wp_mail_content_type', [ __CLASS__, 'set_html_content_type' ] );
        remove_filter( 'wp_mail_from',         [ __CLASS__, 'set_from_email' ] );
        remove_filter( 'wp_mail_from_name',    [ __CLASS__, 'set_from_name'  ] );

        return $result;
    }

    public static function set_html_content_type(): string { return 'text/html'; }
    public static function set_from_email( string $email ): string {
        $opt = get_option( 'va_contact_email', '' );
        return $opt ?: $email;
    }
    public static function set_from_name( string $name ): string {
        return get_option( 'va_site_name', 'VadászApró' );
    }

    /* ─── HTML template builder ─────────────────────────── */
    private static function build( string $heading, string $body_html, array $button ): string {
        $site_name  = esc_html( get_option( 'va_site_name', 'VadászApró' ) );
        $logo_url   = esc_url( get_option( 'va_header_logo_url', '' ) );
        $site_url   = esc_url( home_url( '/' ) );
        $year       = date( 'Y' );

        $logo_block = $logo_url
            ? '<img src="' . $logo_url . '" alt="' . $site_name . '" height="40" style="height:40px;max-width:180px;display:block;">'
            : '<span style="font-size:24px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">' . $site_name . '</span>';

        $btn_block = '';
        if ( ! empty( $button['label'] ) && ! empty( $button['url'] ) ) {
            $btn_block = '
            <div style="text-align:center;margin:28px 0 8px;">
                <a href="' . esc_url( $button['url'] ) . '"
                   style="display:inline-block;background:#cc0000;color:#ffffff;text-decoration:none;
                          font-size:15px;font-weight:700;padding:14px 32px;border-radius:8px;
                          letter-spacing:0.3px;">' . esc_html( $button['label'] ) . '</a>
            </div>';
        }

        return '<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . esc_html( $heading ) . '</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:system-ui,-apple-system,Segoe UI,sans-serif;">

  <!-- Wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#0a0a0a 0%,#1a1a1a 100%);
                     border-radius:12px 12px 0 0;padding:24px 32px;text-align:center;">
            <a href="' . $site_url . '" style="text-decoration:none;">' . $logo_block . '</a>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="background:#ffffff;padding:36px 40px 28px;border-left:1px solid #e5e5e5;border-right:1px solid #e5e5e5;">
            <h1 style="margin:0 0 20px;font-size:22px;font-weight:800;color:#0a0a0a;line-height:1.3;">'
                . esc_html( $heading ) . '</h1>
            <div style="font-size:15px;line-height:1.7;color:#374151;">' . $body_html . '</div>'
            . $btn_block . '
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#0a0a0a;border-radius:0 0 12px 12px;padding:20px 32px;text-align:center;">
            <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.45);">
              © ' . $year . ' <a href="' . $site_url . '" style="color:rgba(255,255,255,0.6);text-decoration:none;">'
              . $site_name . '</a> &bull; Ezt az emailt automatikusan küldte a rendszer.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>

</body>
</html>';
    }
}
