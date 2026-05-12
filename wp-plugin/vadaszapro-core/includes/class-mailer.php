<?php
/**
 * VA_Mailer – HTML e-mail küldő helper
 * Minden wp_mail hívást ezen keresztül kell indítani.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Mailer {

  const BRAND_NAME = 'Vadkár Vadász';
  const SUPPORT_EMAIL = 'vadkarvadasz@gmail.com';

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
      $opt = sanitize_email( (string) get_option( 'va_email_contact_email', self::SUPPORT_EMAIL ) );
      return $opt !== '' ? $opt : self::SUPPORT_EMAIL;
    }
    public static function set_from_name( string $name ): string {
      $opt = trim( (string) get_option( 'va_email_brand_name', self::BRAND_NAME ) );
      return $opt !== '' ? $opt : self::BRAND_NAME;
    }

    /* ─── HTML template builder ─────────────────────────── */
    private static function build( string $heading, string $body_html, array $button ): string {
        $site_name_raw = trim( (string) get_option( 'va_email_brand_name', self::BRAND_NAME ) );
        if ( $site_name_raw === '' ) {
          $site_name_raw = self::BRAND_NAME;
        }
        $site_name  = esc_html( $site_name_raw );

        $logo_source = (string) get_option( 'va_email_logo_url', '' );
        if ( $logo_source === '' ) {
          $logo_source = (string) get_option( 'va_header_logo_url', '' );
        }
        $logo_url   = esc_url( $logo_source );
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

  private static function get_tpl( string $key, string $default ): string {
    $val = trim( (string) get_option( $key, '' ) );
    return $val !== '' ? $val : $default;
  }

  private static function tpl_replace( string $template, array $tokens ): string {
    $map = [];
    foreach ( $tokens as $k => $v ) {
      $map[ '{' . $k . '}' ] = (string) $v;
    }
    return strtr( $template, $map );
  }

  private static function text_to_html( string $text ): string {
    $parts = preg_split( '/\R{2,}/', trim( $text ) );
    if ( ! is_array( $parts ) || empty( $parts ) ) {
      return '';
    }
    $out = '';
    foreach ( $parts as $p ) {
      $safe = nl2br( esc_html( trim( (string) $p ) ) );
      if ( $safe !== '' ) {
        $out .= '<p>' . $safe . '</p>';
      }
    }
    return $out;
  }

    public static function send_plan_upgrade_notice( int $user_id, array $data = [] ): bool {
      $user = get_userdata( $user_id );
      if ( ! $user ) {
        return false;
      }

      $prev_plan = sanitize_key( (string) ( $data['prev_plan_slug'] ?? '' ) );
      $new_plan  = sanitize_key( (string) ( $data['new_plan_slug'] ?? '' ) );
      $carryover_days = absint( $data['carryover_days'] ?? 0 );
      $carryover_value = absint( $data['carryover_value_ft'] ?? 0 );
      $expires_at = absint( $data['new_expires_at'] ?? get_user_meta( $user_id, 'va_plan_expires_at', true ) );

      $prev_label = $prev_plan !== '' ? ucfirst( $prev_plan ) : 'Korábbi csomag';
      $new_label  = $new_plan !== '' ? ucfirst( $new_plan ) : 'Új csomag';
      if ( class_exists( 'VA_User_Roles' ) ) {
        if ( $prev_plan !== '' ) {
          $prev_cfg = VA_User_Roles::get_plan_config( $prev_plan, $user_id );
          $prev_candidate = trim( (string) ( $prev_cfg['label'] ?? '' ) );
          if ( $prev_candidate !== '' ) {
            $prev_label = $prev_candidate;
          }
        }
        if ( $new_plan !== '' ) {
          $new_cfg = VA_User_Roles::get_plan_config( $new_plan, $user_id );
          $new_candidate = trim( (string) ( $new_cfg['label'] ?? '' ) );
          if ( $new_candidate !== '' ) {
            $new_label = $new_candidate;
          }
        }
      }
      if ( $prev_plan === 'custom' ) {
        $prev_label = 'Céges előfizetés';
      }
      if ( $new_plan === 'custom' ) {
        $new_label = 'Céges előfizetés';
      }

      $expires_text = $expires_at > 0 ? wp_date( 'Y.m.d H:i', $expires_at ) : 'nincs beállítva';
      $carryover_line = $carryover_days > 0
        ? 'Maradék kompenzáció: +' . $carryover_days . ' nap' . ( $carryover_value > 0 ? ' (' . number_format( $carryover_value, 0, ',', ' ' ) . ' Ft érték)' : '' )
        : 'Maradék kompenzáció: nem volt jóváírható maradék érték';

      $subject = 'Sikeres csomagváltás - maradék érték jóváírva';
      $heading = 'Sikeres csomagváltás';
      $body = self::text_to_html(
        "Kedves {$user->display_name}!\n\n"
        . "A csomagváltásod sikeres volt.\n"
        . "Korábbi csomag: {$prev_label}\n"
        . "Új csomag: {$new_label}\n"
        . $carryover_line . "\n"
        . "Új lejárat: {$expires_text}\n\n"
        . "Fontos: a maradék érték nem veszett el, automatikusan hozzáíródott az új csomagodhoz."
      );

      return self::send( (string) $user->user_email, $subject, $heading, $body, [
        'label' => 'Csomagjaim megtekintése',
        'url'   => home_url( '/va-kredit-vasarlas/' ),
      ] );
    }

    /* ─── Csomag lejárati figyelmeztetés a felhasználónak ───────── */
    public static function send_plan_expiry_warning( int $user_id, int $days_left ): bool {
        $user = get_userdata( $user_id );
        if ( ! $user ) return false;

        $plan       = (string) get_user_meta( $user_id, 'va_plan', true );
        $plan_cfg   = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_config( $plan, $user_id ) : [];
        $plan_label = $plan_cfg['label'] ?? $plan;
        $expires_at = (int) get_user_meta( $user_id, 'va_plan_expires_at', true );
        $date_str   = $expires_at > 0 ? wp_date( 'Y.m.d H:i', $expires_at ) : '–';

        $variant = $days_left <= 1 ? '1' : ( $days_left <= 7 ? '7' : '30' );
        $subject_tpl = self::get_tpl( 'va_email_plan_expiry_subject_' . $variant, 'A csomagod {days_left} nap múlva lejár – {site_name}' );
        $heading_tpl = self::get_tpl( 'va_email_plan_expiry_heading_' . $variant, 'A csomagod {days_left} nap múlva lejár' );
        $body_tpl    = self::get_tpl( 'va_email_plan_expiry_body_' . $variant, "Kedves {name}!\n\nA {plan_label} csomagod hamarosan lejár.\nLejárat időpontja: {expires_at}" );
        $btn_lbl_tpl = self::get_tpl( 'va_email_plan_expiry_btn_label_' . $variant, 'Csomag vásárlás' );
        $btn_url_tpl = self::get_tpl( 'va_email_plan_expiry_btn_url_' . $variant, home_url( '/csomagok/' ) );

        $tokens = [
          'name'          => (string) $user->display_name,
          'user_email'    => (string) $user->user_email,
          'plan_label'    => (string) $plan_label,
          'plan_slug'     => (string) $plan,
          'days_left'     => (string) $days_left,
          'expires_at'    => (string) $date_str,
          'site_name'     => (string) get_option( 'va_email_brand_name', self::BRAND_NAME ),
          'support_email' => (string) get_option( 'va_email_contact_email', self::SUPPORT_EMAIL ),
          'edit_url'      => (string) get_edit_user_link( $user_id ),
        ];

        $subject = self::tpl_replace( $subject_tpl, $tokens );
        $heading = self::tpl_replace( $heading_tpl, $tokens );
        $body    = self::text_to_html( self::tpl_replace( $body_tpl, $tokens ) );
        $btn_lbl = self::tpl_replace( $btn_lbl_tpl, $tokens );
        $btn_url = self::tpl_replace( $btn_url_tpl, $tokens );

        return self::send( $user->user_email, $subject, $heading, $body, [
          'label' => $btn_lbl,
          'url'   => $btn_url,
        ] );
    }

      /* ─── Felhasználó értesítése: csomag lejárt ────────────────── */
      public static function send_plan_expired_user( int $user_id ): bool {
        $user = get_userdata( $user_id );
        if ( ! $user ) return false;

        $plan       = (string) get_user_meta( $user_id, 'va_plan', true );
        $plan_cfg   = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_config( $plan, $user_id ) : [];
        $plan_label = $plan_cfg['label'] ?? $plan;
        $expires_at = (int) get_user_meta( $user_id, 'va_plan_expires_at', true );
        $date_str   = $expires_at > 0 ? wp_date( 'Y.m.d H:i', $expires_at ) : '–';

        $tokens = [
          'name'          => (string) $user->display_name,
          'user_email'    => (string) $user->user_email,
          'plan_label'    => (string) $plan_label,
          'plan_slug'     => (string) $plan,
          'days_left'     => '0',
          'expires_at'    => (string) $date_str,
          'site_name'     => (string) get_option( 'va_email_brand_name', self::BRAND_NAME ),
          'support_email' => (string) get_option( 'va_email_contact_email', self::SUPPORT_EMAIL ),
          'edit_url'      => (string) get_edit_user_link( $user_id ),
        ];

        $subject = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_user_subject', 'Csomagod lejárt – visszakerültél Alap csomagra ({site_name})' ), $tokens );
        $heading = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_user_heading', 'A csomagod lejárt' ), $tokens );
        $body    = self::text_to_html( self::tpl_replace( self::get_tpl( 'va_email_plan_expired_user_body', "Kedves {name}!\n\nA csomagod lejárt, visszakerültél Alap csomagra." ), $tokens ) );
        $btn_lbl = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_user_btn_label', 'Csomag vásárlása' ), $tokens );
        $btn_url = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_user_btn_url', home_url( '/csomagok/' ) ), $tokens );

        return self::send( $user->user_email, $subject, $heading, $body, [
          'label' => $btn_lbl,
          'url'   => $btn_url,
        ] );
      }

    /* ─── Admin értesítés lejárt csomagról ──────────────────────── */
    public static function send_plan_expired_admin( int $user_id ): bool {
        $admin_email = (string) get_option( 'admin_email', '' );
        if ( ! is_email( $admin_email ) ) return false;

        $user       = get_userdata( $user_id );
        $name       = $user ? (string) $user->display_name : "#{$user_id}";
        $user_email = $user ? (string) $user->user_email   : '–';
        $plan       = (string) get_user_meta( $user_id, 'va_plan', true );
        $plan_cfg   = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_config( $plan, $user_id ) : [];
        $plan_label = (string) ( $plan_cfg['label'] ?? $plan );
        $edit_url   = (string) get_edit_user_link( $user_id );

        $tokens = [
          'name'          => $name,
          'user_email'    => $user_email,
          'plan_label'    => $plan_label,
          'plan_slug'     => $plan,
          'days_left'     => '0',
          'expires_at'    => '',
          'site_name'     => (string) get_option( 'va_email_brand_name', self::BRAND_NAME ),
          'support_email' => (string) get_option( 'va_email_contact_email', self::SUPPORT_EMAIL ),
          'edit_url'      => $edit_url,
        ];

        $subject = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_admin_subject', 'Lejárt csomag – {name}' ), $tokens );
        $heading = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_admin_heading', 'Felhasználói csomag lejárt' ), $tokens );
        $body    = self::text_to_html( self::tpl_replace( self::get_tpl( 'va_email_plan_expired_admin_body', "A következő felhasználó csomagja lejárt.\n\nFelhasználó: {name}\nE-mail: {user_email}\nVolt csomag: {plan_label}" ), $tokens ) );
        $btn_lbl = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_admin_btn_label', 'Felhasználó szerkesztése' ), $tokens );
        $btn_url = self::tpl_replace( self::get_tpl( 'va_email_plan_expired_admin_btn_url', '' ), $tokens );
        if ( trim( $btn_url ) === '' ) {
          $btn_url = $edit_url;
        }

        return self::send( $admin_email, $subject, $heading, $body, [
          'label' => $btn_lbl,
          'url'   => $btn_url,
        ] );
    }
}
