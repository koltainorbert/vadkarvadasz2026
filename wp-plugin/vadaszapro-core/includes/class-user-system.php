<?php
/**
 * Felhasználói rendszer: regisztráció, bejelentkezés, fiók, frontend form-ok
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_User_System {

    private static function ensure_roles(): void {
        if ( ! get_role( 'va_maganszemely' ) ) {
            add_role( 'va_maganszemely', 'Magánszemély', [ 'read' => true ] );
        }
        if ( ! get_role( 'va_ceg' ) ) {
            add_role( 'va_ceg', 'Cég', [ 'read' => true ] );
        }
    }

    private static function is_login_enabled(): bool {
        return get_option( 'va_enable_login', '1' ) === '1';
    }

    private static function is_register_enabled(): bool {
        return get_option( 'va_enable_register', '1' ) === '1';
    }

    private static function get_login_page_url( array $args = [] ): string {
        $page = get_page_by_path( 'va-bejelentkezes' );
        $url  = $page ? get_permalink( $page ) : home_url();
        if ( ! empty( $args ) ) {
            $url = add_query_arg( $args, $url );
        }
        return $url;
    }

    public static function init() {
        self::ensure_roles();

        add_action( 'init',                [ __CLASS__, 'handle_forms' ] );
        add_action( 'wp_enqueue_scripts',  [ __CLASS__, 'enqueue' ] );
        add_action( 'admin_init',          [ __CLASS__, 'restrict_wp_admin_for_non_admins' ] );
        add_filter( 'login_url',           [ __CLASS__, 'custom_login_url' ], 10, 3 );
        add_action( 'transition_post_status', [ __CLASS__, 'notify_listing_published' ], 10, 3 );
        add_filter( 'register_url',        [ __CLASS__, 'custom_register_url' ] );
        add_filter( 'lostpassword_url',    [ __CLASS__, 'custom_lostpassword_url' ], 10, 2 );
        add_filter( 'show_admin_bar',      [ __CLASS__, 'filter_admin_bar_visibility' ] );
        add_filter( 'logout_redirect',     [ __CLASS__, 'logout_redirect' ], 10, 1 );
        add_action( 'login_form_lostpassword', [ __CLASS__, 'redirect_wp_lostpassword' ] );
        add_action( 'login_form_retrievepassword', [ __CLASS__, 'redirect_wp_lostpassword' ] );
        add_action( 'login_form_rp',       [ __CLASS__, 'redirect_wp_resetpass' ] );
        add_action( 'login_form_resetpass',[ __CLASS__, 'redirect_wp_resetpass' ] );
    }

    private static function async_get( string $url ): void {
        if ( $url === '' ) return;

        wp_remote_get( $url, [
            'timeout'  => 1,
            'blocking' => false,
            'headers'  => [
                'User-Agent' => 'VA-Discovery-Bot/1.0',
            ],
        ] );
    }

    private static function trigger_listing_discovery_boost( int $post_id ): void {
        if ( $post_id < 1 ) return;

        $permalink = get_permalink( $post_id );
        if ( ! $permalink ) return;

        $urls = [
            home_url( '/' ),
            home_url( '/hirdetes/' ),
            home_url( '/sitemap.xml' ),
            home_url( '/sitemap-va_listing-1.xml' ),
            $permalink,
        ];

        foreach ( array_unique( $urls ) as $url ) {
            self::async_get( $url );
        }

        // Bing sitemap ping
        $bing_ping = add_query_arg( 'sitemap', rawurlencode( home_url( '/sitemap.xml' ) ), 'https://www.bing.com/ping' );
        self::async_get( $bing_ping );

        // IndexNow – azonnali indexelés Bing + Yandex + egyéb résztvevőknél
        self::indexnow_submit( $permalink );

        // Google Search Console Indexing API (ha be van állítva a service account)
        self::gsc_indexing_submit( $permalink );
    }

    private static function indexnow_submit( string $url ): void {
        $key      = 'b873c945f60ea2d1bc78a3f254901e6d';
        $key_url  = home_url( '/' . $key . '.txt' );
        $endpoint = 'https://api.indexnow.org/indexnow';

        $payload = wp_json_encode( [
            'host'    => wp_parse_url( home_url(), PHP_URL_HOST ),
            'key'     => $key,
            'keyLocation' => $key_url,
            'urlList' => [ $url ],
        ] );

        wp_remote_post( $endpoint, [
            'timeout'     => 5,
            'blocking'    => false,
            'headers'     => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'body'        => $payload,
        ] );
    }

    private static function gsc_indexing_submit( string $url ): void {
        $sa_json = (string) get_option( 'va_gsc_service_account', '' );
        if ( $sa_json === '' ) return;

        $sa = json_decode( $sa_json, true );
        if ( ! is_array( $sa ) || empty( $sa['private_key'] ) || empty( $sa['client_email'] ) ) return;
        if ( ! function_exists( 'openssl_sign' ) ) return;

        // JWT összeállítása
        $now    = time();
        $header = rtrim( strtr( base64_encode( (string) wp_json_encode( [ 'alg' => 'RS256', 'typ' => 'JWT' ] ) ), '+/', '-_' ), '=' );
        $claims = rtrim( strtr( base64_encode( (string) wp_json_encode( [
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ] ) ), '+/', '-_' ), '=' );

        $sig_input = $header . '.' . $claims;
        $signature = '';
        openssl_sign( $sig_input, $signature, $sa['private_key'], 'SHA256' );
        $jwt = $sig_input . '.' . rtrim( strtr( base64_encode( $signature ), '+/', '-_' ), '=' );

        // Access token lekérése
        $token_resp = wp_remote_post( 'https://oauth2.googleapis.com/token', [
            'timeout'  => 10,
            'blocking' => true,
            'body'     => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
        ] );

        if ( is_wp_error( $token_resp ) ) return;

        $token_data = json_decode( wp_remote_retrieve_body( $token_resp ), true );
        if ( empty( $token_data['access_token'] ) ) return;

        // URL beküldése a Google Indexing API-nak
        wp_remote_post( 'https://indexing.googleapis.com/v3/urlNotifications:publish', [
            'timeout'  => 5,
            'blocking' => false,
            'headers'  => [
                'Authorization' => 'Bearer ' . $token_data['access_token'],
                'Content-Type'  => 'application/json',
            ],
            'body'     => (string) wp_json_encode( [ 'url' => $url, 'type' => 'URL_UPDATED' ] ),
        ] );
    }

    public static function gsc_indexing_test( string $url = '' ): array {
        $test_url = trim( $url );
        if ( $test_url === '' ) {
            $test_url = home_url( '/' );
        }

        $sa_json = (string) get_option( 'va_gsc_service_account', '' );
        if ( $sa_json === '' ) {
            return [
                'ok'      => false,
                'message' => 'Nincs Service Account JSON megadva.',
                'status'  => 0,
            ];
        }

        $sa = json_decode( $sa_json, true );
        if ( ! is_array( $sa ) || empty( $sa['private_key'] ) || empty( $sa['client_email'] ) ) {
            return [
                'ok'      => false,
                'message' => 'A Service Account JSON hibás vagy hiányos (private_key / client_email).',
                'status'  => 0,
            ];
        }

        if ( ! function_exists( 'openssl_sign' ) ) {
            return [
                'ok'      => false,
                'message' => 'OpenSSL PHP extension nem elérhető a szerveren.',
                'status'  => 0,
            ];
        }

        $now    = time();
        $header = rtrim( strtr( base64_encode( (string) wp_json_encode( [ 'alg' => 'RS256', 'typ' => 'JWT' ] ) ), '+/', '-_' ), '=' );
        $claims = rtrim( strtr( base64_encode( (string) wp_json_encode( [
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ] ) ), '+/', '-_' ), '=' );

        $sig_input = $header . '.' . $claims;
        $signature = '';
        openssl_sign( $sig_input, $signature, $sa['private_key'], 'SHA256' );
        $jwt = $sig_input . '.' . rtrim( strtr( base64_encode( $signature ), '+/', '-_' ), '=' );

        $token_resp = wp_remote_post( 'https://oauth2.googleapis.com/token', [
            'timeout'  => 12,
            'blocking' => true,
            'body'     => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
        ] );

        if ( is_wp_error( $token_resp ) ) {
            return [
                'ok'      => false,
                'message' => 'Token kérés hiba: ' . $token_resp->get_error_message(),
                'status'  => 0,
            ];
        }

        $token_code = (int) wp_remote_retrieve_response_code( $token_resp );
        $token_data = json_decode( wp_remote_retrieve_body( $token_resp ), true );
        if ( $token_code < 200 || $token_code >= 300 || empty( $token_data['access_token'] ) ) {
            $detail = '';
            if ( is_array( $token_data ) && ! empty( $token_data['error_description'] ) ) {
                $detail = (string) $token_data['error_description'];
            } elseif ( is_array( $token_data ) && ! empty( $token_data['error'] ) ) {
                $detail = is_array( $token_data['error'] ) ? wp_json_encode( $token_data['error'] ) : (string) $token_data['error'];
            }

            return [
                'ok'      => false,
                'message' => 'Google token kérés sikertelen (HTTP ' . $token_code . '). ' . $detail,
                'status'  => $token_code,
            ];
        }

        $submit_resp = wp_remote_post( 'https://indexing.googleapis.com/v3/urlNotifications:publish', [
            'timeout'  => 12,
            'blocking' => true,
            'headers'  => [
                'Authorization' => 'Bearer ' . $token_data['access_token'],
                'Content-Type'  => 'application/json',
            ],
            'body'     => (string) wp_json_encode( [ 'url' => $test_url, 'type' => 'URL_UPDATED' ] ),
        ] );

        if ( is_wp_error( $submit_resp ) ) {
            return [
                'ok'      => false,
                'message' => 'Indexing API hívás hiba: ' . $submit_resp->get_error_message(),
                'status'  => 0,
            ];
        }

        $submit_code = (int) wp_remote_retrieve_response_code( $submit_resp );
        if ( $submit_code >= 200 && $submit_code < 300 ) {
            return [
                'ok'      => true,
                'message' => 'Sikeres kapcsolat: URL beküldve (' . esc_url_raw( $test_url ) . ').',
                'status'  => $submit_code,
            ];
        }

        $submit_data = json_decode( wp_remote_retrieve_body( $submit_resp ), true );
        $detail      = '';
        if ( is_array( $submit_data ) && ! empty( $submit_data['error'] ) ) {
            $detail = is_array( $submit_data['error'] ) ? wp_json_encode( $submit_data['error'] ) : (string) $submit_data['error'];
        }

        return [
            'ok'      => false,
            'message' => 'Indexing API válasz hiba (HTTP ' . $submit_code . '). ' . $detail,
            'status'  => $submit_code,
        ];
    }

    /* ── URL átirányítások ─────────────────────────────── */
    public static function custom_login_url( $url, $redirect, $force_reauth ) {
        // Admin redirect esetén mindig az eredeti wp-login.php maradjon
        if ( $force_reauth || strpos( $redirect, admin_url(), 0 ) === 0 || strpos( $redirect, '/wp-admin', 0 ) !== false ) {
            return $url;
        }
        if ( ! self::is_login_enabled() ) {
            return home_url();
        }
        $page = get_page_by_path( 'va-bejelentkezes' );
        return $page ? get_permalink( $page ) : $url;
    }

    public static function custom_register_url( $url ) {
        if ( ! self::is_register_enabled() ) {
            return home_url();
        }
        $page = get_page_by_path( 'va-regisztracio' );
        return $page ? get_permalink( $page ) : $url;
    }

    public static function custom_lostpassword_url( $url, $redirect ) {
        $args = [ 'action' => 'lostpassword' ];
        if ( ! empty( $redirect ) ) {
            $args['redirect_to'] = $redirect;
        }
        return self::get_login_page_url( $args );
    }

    public static function redirect_wp_lostpassword() {
        wp_safe_redirect( self::get_login_page_url( [ 'action' => 'lostpassword' ] ) );
        exit;
    }

    public static function redirect_wp_resetpass() {
        $key   = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['key'] ) ) : '';
        $login = isset( $_REQUEST['login'] ) ? sanitize_user( wp_unslash( (string) $_REQUEST['login'] ) ) : '';

        $args = [ 'action' => 'resetpass' ];
        if ( $key !== '' ) {
            $args['key'] = $key;
        }
        if ( $login !== '' ) {
            $args['login'] = $login;
        }

        wp_safe_redirect( self::get_login_page_url( $args ) );
        exit;
    }

    public static function logout_redirect( $url ) {
        return home_url();
    }

    public static function filter_admin_bar_visibility( $show ) {
        if ( current_user_can( 'manage_options' ) ) {
            return $show;
        }
        return false;
    }

    public static function restrict_wp_admin_for_non_admins(): void {
        if ( ! is_user_logged_in() ) {
            return;
        }
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        wp_safe_redirect( home_url() );
        exit;
    }

    /* ── Enqueue ───────────────────────────────────────── */
    public static function enqueue() {
        wp_enqueue_style(  'va-user', VA_PLUGIN_URL . 'frontend/css/user.css', [], VA_VERSION );
        wp_enqueue_script( 'va-user', VA_PLUGIN_URL . 'frontend/js/user.js',  [ 'jquery' ], VA_VERSION, true );
        wp_localize_script( 'va-user', 'VA_User', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'va_user_nonce' ),
        ]);
    }

    /* ── Form kezelés ──────────────────────────────────── */
    public static function handle_forms() {
        if ( isset( $_POST['va_action'] ) ) {
            $action = sanitize_key( $_POST['va_action'] );
            if ( $action === 'register' ) self::process_register();
            if ( $action === 'login'    ) self::process_login();
            if ( $action === 'lostpassword' ) self::process_lostpassword();
            if ( $action === 'resetpass'    ) self::process_resetpass();
            if ( $action === 'logout'   ) self::process_logout();
            if ( $action === 'profile'  ) self::process_profile();
            if ( $action === 'profile_avatar' ) self::process_profile_avatar();
            if ( $action === 'profile_label'  ) self::process_profile_label();
            if ( $action === 'delete_listing'   ) self::process_delete_listing();
            if ( $action === 'delete_profile'   ) self::process_delete_profile();
            if ( $action === 'suspend_listing'  ) self::process_suspend_listing();
        }
    }

    private static function process_lostpassword() {
        if ( ! isset( $_POST['va_lostpass_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_lostpass_nonce'] ) ), 'va_lostpass' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_login = sanitize_text_field( wp_unslash( $_POST['lost_user_login'] ?? '' ) );
        if ( $user_login === '' ) {
            va_set_flash( 'error', 'Add meg a felhasználónevedet vagy e-mail címedet.' );
            return;
        }

        $res = retrieve_password( $user_login );
        if ( is_wp_error( $res ) ) {
            $msg = $res->get_error_message();
            va_set_flash( 'error', $msg !== '' ? $msg : 'Nem sikerült jelszó-visszaállító e-mailt küldeni.' );
            return;
        }

        va_set_flash( 'success', 'Küldtünk egy e-mailt a jelszó visszaállításához.' );
        wp_safe_redirect( self::get_login_page_url( [ 'action' => 'lostpassword' ] ) );
        exit;
    }

    private static function process_resetpass() {
        if ( ! isset( $_POST['va_resetpass_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_resetpass_nonce'] ) ), 'va_resetpass' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $key   = sanitize_text_field( wp_unslash( $_POST['rp_key'] ?? '' ) );
        $login = sanitize_user( wp_unslash( $_POST['rp_login'] ?? '' ) );
        $pass1 = (string) wp_unslash( $_POST['rp_pass1'] ?? '' );
        $pass2 = (string) wp_unslash( $_POST['rp_pass2'] ?? '' );

        if ( $key === '' || $login === '' ) {
            va_set_flash( 'error', 'Hiányzó visszaállítási adatok.' );
            return;
        }

        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error( $user ) ) {
            va_set_flash( 'error', 'A jelszó-visszaállító link lejárt vagy érvénytelen.' );
            return;
        }

        if ( $pass1 === '' || $pass2 === '' ) {
            va_set_flash( 'error', 'Add meg az új jelszót mindkét mezőben.' );
            return;
        }

        if ( $pass1 !== $pass2 ) {
            va_set_flash( 'error', 'A két új jelszó nem egyezik.' );
            return;
        }

        if ( strlen( $pass1 ) < 8 ) {
            va_set_flash( 'error', 'Az új jelszó legalább 8 karakter legyen.' );
            return;
        }

        reset_password( $user, $pass1 );
        va_set_flash( 'success', 'Jelszó sikeresen módosítva. Most már bejelentkezhetsz.' );
        wp_safe_redirect( self::get_login_page_url() );
        exit;
    }

    /* ── Regisztráció ──────────────────────────────────── */
    private static function process_register() {
        if ( ! self::is_register_enabled() ) {
            va_set_flash( 'error', 'A regisztráció jelenleg ki van kapcsolva.' );
            return;
        }

        if ( ! isset( $_POST['va_register_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_register_nonce'] ) ), 'va_register' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $username  = sanitize_user( wp_unslash( $_POST['reg_username'] ?? '' ) );
        $email     = sanitize_email( wp_unslash( $_POST['reg_email']    ?? '' ) );
        $pass      = wp_unslash( $_POST['reg_password'] ?? '' );
        $pass2     = wp_unslash( $_POST['reg_password2'] ?? '' );
        $firstname = sanitize_text_field( wp_unslash( $_POST['reg_firstname'] ?? '' ) );
        $lastname  = sanitize_text_field( wp_unslash( $_POST['reg_lastname']  ?? '' ) );
        $phone     = sanitize_text_field( wp_unslash( $_POST['reg_phone']     ?? '' ) );
        $account_type = sanitize_key( wp_unslash( $_POST['reg_account_type'] ?? 'private' ) );
        $company_name = sanitize_text_field( wp_unslash( $_POST['reg_company_name'] ?? '' ) );
        $company_tax  = sanitize_text_field( wp_unslash( $_POST['reg_company_tax'] ?? '' ) );
        $company_seat = sanitize_text_field( wp_unslash( $_POST['reg_company_seat'] ?? '' ) );

        if ( ! in_array( $account_type, [ 'private', 'company' ], true ) ) {
            $account_type = 'private';
        }

        if ( empty( $username ) || empty( $email ) || empty( $pass ) ) {
            va_set_flash( 'error', 'Kérjük töltse ki a kötelező mezőket.' );
            return;
        }

        if ( empty( $_POST['terms_accept'] ) ) {
            va_set_flash( 'error', 'Az ÁSZF elfogadása kötelező.' );
            return;
        }

        if ( $account_type === 'company' ) {
            if ( $company_name === '' || $company_tax === '' || $company_seat === '' ) {
                va_set_flash( 'error', 'Céges regisztrációnál a cégnév, adószám és székhely kitöltése kötelező.' );
                return;
            }

            if ( strlen( preg_replace( '/[^0-9]/', '', $company_tax ) ) < 8 ) {
                va_set_flash( 'error', 'Kérjük érvényes adószámot adjon meg.' );
                return;
            }
        }

        if ( $pass !== $pass2 ) {
            va_set_flash( 'error', 'A két jelszó nem egyezik.' );
            return;
        }

        if ( strlen( $pass ) < 8 ) {
            va_set_flash( 'error', 'A jelszónak legalább 8 karakter hosszúnak kell lennie.' );
            return;
        }

        if ( username_exists( $username ) ) {
            va_set_flash( 'error', 'Ez a felhasználónév már foglalt.' );
            return;
        }

        if ( email_exists( $email ) ) {
            va_set_flash( 'error', 'Ez az e-mail cím már regisztrált.' );
            return;
        }

        $user_id = wp_create_user( $username, $pass, $email );
        if ( is_wp_error( $user_id ) ) {
            va_set_flash( 'error', $user_id->get_error_message() );
            return;
        }

        $assigned_role = ( $account_type === 'company' ) ? 'va_ceg' : 'va_maganszemely';
        if ( ! get_role( $assigned_role ) ) {
            $assigned_role = 'subscriber';
        }

        wp_update_user([
            'ID'         => $user_id,
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'role'       => $assigned_role,
        ]);
        update_user_meta( $user_id, 'va_phone', $phone );
        update_user_meta( $user_id, 'va_account_type', $account_type );

        if ( $account_type === 'company' ) {
            update_user_meta( $user_id, 'va_company_name', $company_name );
            update_user_meta( $user_id, 'va_company_tax', $company_tax );
            update_user_meta( $user_id, 'va_company_seat', $company_seat );
        } else {
            delete_user_meta( $user_id, 'va_company_name' );
            delete_user_meta( $user_id, 'va_company_tax' );
            delete_user_meta( $user_id, 'va_company_seat' );
        }

        // Regisztrációs email
        if ( get_option( 'va_email_reg_enabled', '1' ) === '1' && class_exists( 'VA_Mailer' ) ) {
            $user_obj  = get_userdata( $user_id );
            $site_name = get_option( 'va_site_name', get_bloginfo( 'name' ) );
            $vars = [
                '{name}'      => esc_html( $user_obj->display_name ?: $firstname . ' ' . $lastname ),
                '{username}'  => esc_html( $username ),
                '{site_name}' => esc_html( $site_name ),
            ];
            $btn_lbl = strtr( get_option( 'va_email_reg_btn', 'Fiókja megtekintése' ), $vars );
            $btn_url = get_permalink( get_page_by_path( 'va-fiok' ) ) ?: home_url( '/' );
            VA_Mailer::send(
                $email,
                strtr( get_option( 'va_email_reg_subject', 'Üdvözlünk a {site_name} oldalon!' ), $vars ),
                strtr( get_option( 'va_email_reg_heading', 'Sikeres regisztráció!' ), $vars ),
                strtr( get_option( 'va_email_reg_body', '' ), $vars ),
                $btn_lbl ? [ 'label' => $btn_lbl, 'url' => $btn_url ] : []
            );
        }

        // Automatikus bejelentkezés regisztráció után
        wp_set_auth_cookie( $user_id );

        va_set_flash( 'success', 'Sikeres regisztráció! Üdvözöljük!' );

        $dashboard = get_page_by_path( 'va-fiok' );
        wp_safe_redirect( $dashboard ? get_permalink( $dashboard ) : home_url() );
        exit;
    }

    /* ── Bejelentkezés ─────────────────────────────────── */
    private static function process_login() {
        if ( ! self::is_login_enabled() ) {
            va_set_flash( 'error', 'A bejelentkezés jelenleg ki van kapcsolva.' );
            return;
        }

        if ( ! isset( $_POST['va_login_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_login_nonce'] ) ), 'va_login' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $creds = [
            'user_login'    => sanitize_user( wp_unslash( $_POST['login_username'] ?? '' ) ),
            'user_password' => wp_unslash( $_POST['login_password'] ?? '' ),
            'remember'      => ! empty( $_POST['login_remember'] ),
        ];

        $user = wp_signon( $creds, is_ssl() );
        if ( is_wp_error( $user ) ) {
            va_set_flash( 'error', 'Hibás felhasználónév vagy jelszó.' );
            return;
        }

        $redirect = wp_validate_redirect(
            wp_unslash( $_POST['redirect_to'] ?? '' ),
            home_url()
        );
        wp_safe_redirect( $redirect );
        exit;
    }

    private static function process_logout() {
        wp_logout();
        wp_safe_redirect( home_url() );
        exit;
    }

    /* ── Profil frissítés ──────────────────────────────── */
    private static function process_profile() {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_profile_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_profile_nonce'] ) ), 'va_profile' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id   = get_current_user_id();
        $firstname = sanitize_text_field( wp_unslash( $_POST['profile_firstname'] ?? '' ) );
        $lastname  = sanitize_text_field( wp_unslash( $_POST['profile_lastname']  ?? '' ) );
        $phone     = sanitize_text_field( wp_unslash( $_POST['profile_phone']     ?? '' ) );
        $bio       = sanitize_textarea_field( wp_unslash( $_POST['profile_bio']   ?? '' ) );

        // Platinum: egyedi rang címke
        $user_plan = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user_id ) : 'basic';
        if ( $user_plan === 'platinum' ) {
            $seller_label = sanitize_text_field( wp_unslash( $_POST['profile_seller_label'] ?? '' ) );
            update_user_meta( $user_id, 'va_seller_label', $seller_label );
        }

        wp_update_user([
            'ID'          => $user_id,
            'first_name'  => $firstname,
            'last_name'   => $lastname,
            'description' => $bio,
        ]);
        update_user_meta( $user_id, 'va_phone', $phone );

        // Profilkép törlés
        if ( ! empty( $_POST['profile_avatar_remove'] ) ) {
            delete_user_meta( $user_id, 'va_profile_avatar_id' );
        }

        // Profilkép feltöltés
        if ( ! empty( $_FILES['profile_avatar']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            // Felhasználónkénti könyvtár: /va-users/{user_id}/avatar/
            $va_avatar_dir_filter = static function( $dirs ) use ( $user_id ) {
                $dirs['subdir'] = '/va-users/' . $user_id . '/avatar';
                $dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
                $dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
                return $dirs;
            };
            add_filter( 'upload_dir', $va_avatar_dir_filter );

            $uploaded = wp_handle_upload( $_FILES['profile_avatar'], [
                'test_form' => false,
                'mimes'     => [
                    'jpg|jpeg' => 'image/jpeg',
                    'png'      => 'image/png',
                    'webp'     => 'image/webp',
                ],
            ] );

            if ( ! empty( $uploaded['error'] ) ) {
                remove_filter( 'upload_dir', $va_avatar_dir_filter );
                va_set_flash( 'error', 'A profilkép feltöltése sikertelen: ' . sanitize_text_field( $uploaded['error'] ) );
                wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
                exit;
            }

            remove_filter( 'upload_dir', $va_avatar_dir_filter );

            $file_path = $uploaded['file'] ?? '';
            $file_url  = $uploaded['url'] ?? '';
            if ( $file_path && $file_url ) {
                $filetype = wp_check_filetype( wp_basename( $file_path ), null );
                $attach_id = wp_insert_attachment( [
                    'guid'           => $file_url,
                    'post_mime_type' => $filetype['type'] ?? 'image/jpeg',
                    'post_title'     => sanitize_file_name( pathinfo( $file_path, PATHINFO_FILENAME ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                    'post_author'    => $user_id,
                ], $file_path );

                if ( ! is_wp_error( $attach_id ) && $attach_id > 0 ) {
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    update_user_meta( $user_id, 'va_profile_avatar_id', (int) $attach_id );
                }
            }
        }

        // Jelszócsere ha megadva
        $new_pass  = wp_unslash( $_POST['profile_newpass']  ?? '' );
        $new_pass2 = wp_unslash( $_POST['profile_newpass2'] ?? '' );
        if ( ! empty( $new_pass ) ) {
            if ( $new_pass !== $new_pass2 ) {
                va_set_flash( 'error', 'A két jelszó nem egyezik.' );
                return;
            }
            if ( strlen( $new_pass ) < 8 ) {
                va_set_flash( 'error', 'A jelszónak legalább 8 karakter.' );
                return;
            }
            wp_set_password( $new_pass, $user_id );
            wp_set_auth_cookie( $user_id );
        }

        va_set_flash( 'success', 'Profil sikeresen frissítve.' );
        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }

    /* ── Platinum: gyors címke frissítés ───────────────── */
    private static function process_profile_avatar() {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_profile_avatar_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_profile_avatar_nonce'] ) ), 'va_profile_avatar' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id = get_current_user_id();

        if ( ! empty( $_POST['profile_avatar_remove'] ) ) {
            delete_user_meta( $user_id, 'va_profile_avatar_id' );
        }

        if ( ! empty( $_FILES['profile_avatar']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            // Felhasználónkénti könyvtár: /va-users/{user_id}/avatar/
            $va_avatar_dir_filter = static function( $dirs ) use ( $user_id ) {
                $dirs['subdir'] = '/va-users/' . $user_id . '/avatar';
                $dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
                $dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
                return $dirs;
            };
            add_filter( 'upload_dir', $va_avatar_dir_filter );

            $uploaded = wp_handle_upload( $_FILES['profile_avatar'], [
                'test_form' => false,
                'mimes'     => [
                    'jpg|jpeg' => 'image/jpeg',
                    'png'      => 'image/png',
                    'webp'     => 'image/webp',
                ],
            ] );

            remove_filter( 'upload_dir', $va_avatar_dir_filter );

            if ( ! empty( $uploaded['error'] ) ) {
                va_set_flash( 'error', 'A profilkép feltöltése sikertelen: ' . sanitize_text_field( $uploaded['error'] ) );
                wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
                exit;
            }

            $file_path = $uploaded['file'] ?? '';
            $file_url  = $uploaded['url'] ?? '';
            if ( $file_path && $file_url ) {
                $filetype = wp_check_filetype( wp_basename( $file_path ), null );
                $attach_id = wp_insert_attachment( [
                    'guid'           => $file_url,
                    'post_mime_type' => $filetype['type'] ?? 'image/jpeg',
                    'post_title'     => sanitize_file_name( pathinfo( $file_path, PATHINFO_FILENAME ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                    'post_author'    => $user_id,
                ], $file_path );

                if ( ! is_wp_error( $attach_id ) && $attach_id > 0 ) {
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    update_user_meta( $user_id, 'va_profile_avatar_id', (int) $attach_id );
                }
            }
        }

        va_set_flash( 'success', 'Profilkép frissítve.' );
        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }

    /* ── Platinum: gyors címke frissítés ───────────────── */
    private static function process_profile_label() {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_profile_label_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_profile_label_nonce'] ) ), 'va_profile_label' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id   = get_current_user_id();
        $user_plan = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user_id ) : 'basic';
        if ( $user_plan !== 'platinum' ) {
            va_set_flash( 'error', 'Ez a funkció csak Platinum csomagban érhető el.' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        $seller_label = sanitize_text_field( wp_unslash( $_POST['profile_seller_label'] ?? '' ) );
        update_user_meta( $user_id, 'va_seller_label', $seller_label );

        va_set_flash( 'success', 'Rang címke frissítve.' );
        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }

    /* ── Hirdetés törlés (saját) ───────────────────────── */
    private static function process_delete_listing(): void {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_delete_listing_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_delete_listing_nonce'] ) ), 'va_delete_listing' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id = get_current_user_id();
        $post_id = (int) ( $_POST['listing_id'] ?? 0 );
        if ( ! $post_id ) return;

        $post = get_post( $post_id );
        if ( ! $post || (int) $post->post_author !== $user_id || $post->post_type !== 'va_listing' ) {
            va_set_flash( 'error', 'Nincs jogosultságod ehhez a hirdetéshez.' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        // Hirdetés törlés email
        if ( get_option( 'va_email_del_listing_enabled', '1' ) === '1' && class_exists( 'VA_Mailer' ) ) {
            $u    = get_userdata( $user_id );
            $site = get_option( 'va_site_name', get_bloginfo( 'name' ) );
            $vars = [
                '{name}'      => esc_html( $u->display_name ),
                '{title}'     => esc_html( $post->post_title ),
                '{site_name}' => esc_html( $site ),
            ];
            $btn_lbl = strtr( get_option( 'va_email_del_listing_btn', '' ), $vars );
            VA_Mailer::send(
                $u->user_email,
                strtr( get_option( 'va_email_del_listing_subject', 'Hirdetésed törölve – {title}' ), $vars ),
                strtr( get_option( 'va_email_del_listing_heading', 'Hirdetésed törölve lett' ), $vars ),
                strtr( get_option( 'va_email_del_listing_body', '' ), $vars ),
                $btn_lbl ? [ 'label' => $btn_lbl, 'url' => home_url( '/' ) ] : []
            );
        }

        self::delete_listing_with_images( $post_id );

        va_set_flash( 'success', 'A hirdetés sikeresen törölve.' );
        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }

    /* ── Helper: hirdetés + képek törlése ─────────────── */
    public static function delete_listing_with_images( int $post_id ): void {
        global $wpdb;

        // Leírásban lévő editor képek törlése (URL alapján – post_parent=0 esetén is)
        $post = get_post( $post_id );
        if ( $post && $post->post_content ) {
            preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*/i', $post->post_content, $img_matches );
            foreach ( $img_matches[1] as $img_url ) {
                // Csak saját domain-re mutatói URL-eket töröljük (ne külső képeket)
                if ( strpos( $img_url, home_url() ) === false ) continue;
                $att_id = attachment_url_to_postid( $img_url );
                if ( $att_id ) {
                    wp_delete_attachment( $att_id, true );
                }
            }
        }

        // Csatolt képek törlése (post_parent alapján)
        $attached = get_posts([
            'post_type'      => 'attachment',
            'post_parent'    => $post_id,
            'posts_per_page' => 100,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        foreach ( $attached as $att_id ) {
            wp_delete_attachment( (int) $att_id, true );
        }

        // Galéria képek törlése (va_gallery_ids meta alapján – esetleg nincs post_parent)
        $gallery_str = get_post_meta( $post_id, 'va_gallery_ids', true );
        if ( $gallery_str ) {
            foreach ( array_filter( array_map( 'intval', explode( ',', $gallery_str ) ) ) as $att_id ) {
                wp_delete_attachment( $att_id, true );
            }
        }

        // Custom táblák takarítás
        $wpdb->delete( $wpdb->prefix . 'va_listing_meta', [ 'post_id'    => $post_id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'va_bids',         [ 'auction_id' => $post_id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'va_watchlist',    [ 'post_id'    => $post_id ], [ '%d' ] );

        wp_delete_post( $post_id, true );
    }

    /* ── Teljes profil törlése ─────────────────────────── */
    private static function process_delete_profile(): void {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_delete_profile_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_delete_profile_nonce'] ) ), 'va_delete_profile' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $confirm = sanitize_text_field( wp_unslash( $_POST['confirm_delete'] ?? '' ) );
        if ( $confirm !== 'TORLESEM' ) {
            va_set_flash( 'error', 'A törlés megerősítéséhez írd be: TORLESEM' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        $user_id = get_current_user_id();

        // Admin nem törölheti magát így
        if ( user_can( $user_id, 'manage_options' ) ) {
            va_set_flash( 'error', 'Admin fiók nem törölhető ezen az úton.' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        global $wpdb;

        // Összes saját hirdetés törlése (max 100-as batch)
        $paged = 1;
        do {
            $ids = get_posts([
                'post_type'      => 'va_listing',
                'author'         => $user_id,
                'posts_per_page' => 100,
                'paged'          => $paged,
                'post_status'    => 'any',
                'fields'         => 'ids',
                'no_found_rows'  => false,
            ]);
            foreach ( $ids as $pid ) {
                self::delete_listing_with_images( (int) $pid );
            }
            $paged++;
        } while ( count( $ids ) === 100 );

        // Profilkép törlése
        $avatar_id = (int) get_user_meta( $user_id, 'va_profile_avatar_id', true );
        if ( $avatar_id ) {
            wp_delete_attachment( $avatar_id, true );
        }

        // Licitek és kedvencek törlése
        $wpdb->delete( $wpdb->prefix . 'va_bids',      [ 'user_id' => $user_id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'va_watchlist', [ 'user_id' => $user_id ], [ '%d' ] );

        // Fiók törlés email (logout előtt, user még létezik)
        if ( get_option( 'va_email_del_account_enabled', '1' ) === '1' && class_exists( 'VA_Mailer' ) ) {
            $u    = get_userdata( $user_id );
            $site = get_option( 'va_site_name', get_bloginfo( 'name' ) );
            $vars = [
                '{name}'      => esc_html( $u->display_name ),
                '{username}'  => esc_html( $u->user_login ),
                '{site_name}' => esc_html( $site ),
            ];
            $btn_lbl = strtr( get_option( 'va_email_del_account_btn', '' ), $vars );
            VA_Mailer::send(
                $u->user_email,
                strtr( get_option( 'va_email_del_account_subject', 'Fiókod törölve – {site_name}' ), $vars ),
                strtr( get_option( 'va_email_del_account_heading', 'Fiókod törölve lett' ), $vars ),
                strtr( get_option( 'va_email_del_account_body', '' ), $vars ),
                $btn_lbl ? [ 'label' => $btn_lbl, 'url' => home_url( '/' ) ] : []
            );
        }

        // Kijelentkezés, majd felhasználó törlése
        wp_logout();
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user( $user_id );

        wp_safe_redirect( add_query_arg( 'va_deleted', '1', home_url( '/' ) ) );
        exit;
    }

    /* ── Email: hirdetés megjelent (admin jóváhagyáskor) ────── */
    public static function notify_listing_published( string $new_status, string $old_status, \WP_Post $post ): void {
        if ( $new_status !== 'publish' || $old_status === 'publish' ) return;
        if ( $post->post_type !== 'va_listing' ) return;

        self::trigger_listing_discovery_boost( (int) $post->ID );

        if ( get_option( 'va_email_listing_enabled', '1' ) !== '1' ) return;
        if ( ! class_exists( 'VA_Mailer' ) ) return;

        $user = get_userdata( (int) $post->post_author );
        if ( ! $user ) return;

        $site = get_option( 'va_site_name', get_bloginfo( 'name' ) );
        $vars = [
            '{name}'      => esc_html( $user->display_name ),
            '{title}'     => esc_html( $post->post_title ),
            '{site_name}' => esc_html( $site ),
        ];
        $btn_lbl = strtr( get_option( 'va_email_listing_btn', 'Hirdetés megtekintése' ), $vars );
        VA_Mailer::send(
            $user->user_email,
            strtr( get_option( 'va_email_listing_subject', 'Hirdetésed megjelent – {title}' ), $vars ),
            strtr( get_option( 'va_email_listing_heading', 'Hirdetésed él!' ), $vars ),
            strtr( get_option( 'va_email_listing_body', '' ), $vars ),
            $btn_lbl ? [ 'label' => $btn_lbl, 'url' => get_permalink( $post->ID ) ] : []
        );
    }

    /* ── Hirdetés felfüggesztés / újraindítás (Gold, Platinum) ── */
    private static function process_suspend_listing(): void {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_suspend_listing_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_suspend_listing_nonce'] ) ), 'va_suspend_listing' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id = get_current_user_id();
        $post_id = (int) ( $_POST['listing_id'] ?? 0 );
        if ( ! $post_id ) return;

        // Csak gold / platinum jogosult
        $plan = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user_id ) : 'basic';
        if ( ! in_array( $plan, [ 'gold', 'platinum' ], true ) ) {
            va_set_flash( 'error', 'Ez a funkció Gold és Platinum csomagoknál érhető el.' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        $post = get_post( $post_id );
        if ( ! $post || (int) $post->post_author !== $user_id || $post->post_type !== 'va_listing' ) {
            va_set_flash( 'error', 'Nincs jogosultságod ehhez a hirdetéshez.' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        $is_suspended = get_post_meta( $post_id, 'va_is_suspended', true ) === '1';

        if ( $is_suspended ) {
            // Újraindítás
            wp_update_post( [ 'ID' => $post_id, 'post_status' => 'publish' ] );
            delete_post_meta( $post_id, 'va_is_suspended' );
            delete_post_meta( $post_id, 'va_suspended_at' );
            // active_since újraindul a jelenlegi időtől
            update_post_meta( $post_id, 'va_active_since', current_time( 'timestamp' ) );
            va_set_flash( 'success', 'A hirdetés újra aktív.' );
        } else {
            // Felfüggesztés – csak publish-ból
            if ( $post->post_status !== 'publish' ) {
                va_set_flash( 'error', 'Csak aktív hirdetést lehet felfüggeszteni.' );
                wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
                exit;
            }
            wp_update_post( [ 'ID' => $post_id, 'post_status' => 'private' ] );
            update_post_meta( $post_id, 'va_is_suspended', '1' );
            update_post_meta( $post_id, 'va_suspended_at', current_time( 'timestamp' ) );
            va_set_flash( 'success', 'A hirdetés felfüggesztve – bármikor újraindíthatod.' );
        }

        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }
}
