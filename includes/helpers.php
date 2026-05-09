<?php
/**
 * Segédfüggvények – az egész plugin használja
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Aukció funkció globális kapcsoló ─────────────────── */
function va_auctions_enabled(): bool {
    return get_option( 'va_enable_auctions', '1' ) === '1';
}

/* ── Megtekintés szám (determinisztikus alap + valós) ─── */
function va_client_ip(): string {
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR']  ?? '',
        $_SERVER['HTTP_CLIENT_IP']        ?? '',
        $_SERVER['REMOTE_ADDR']           ?? '',
    ];

    foreach ( $candidates as $candidate ) {
        $candidate = sanitize_text_field( wp_unslash( (string) $candidate ) );
        if ( $candidate === '' ) {
            continue;
        }

        if ( strpos( $candidate, ',' ) !== false ) {
            $parts = array_map( 'trim', explode( ',', $candidate ) );
            $candidate = (string) ( $parts[0] ?? '' );
        }

        if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
            return $candidate;
        }
    }

    return '0.0.0.0';
}

function va_views_floor_key( int $post_id ): string {
    $ip = va_client_ip();
    $hash = substr( md5( $ip . '|' . wp_salt( 'auth' ) . '|' . $post_id ), 0, 16 );
    return 'va_vwf_' . $post_id . '_' . $hash;
}

function va_apply_views_floor( int $post_id, int $display_views ): int {
    $key = va_views_floor_key( $post_id );
    $stored = (int) get_transient( $key );
    $final  = max( $display_views, $stored );

    // 100 napig megtartjuk az adott IP altal latott max erteket.
    if ( $final !== $stored ) {
        set_transient( $key, $final, DAY_IN_SECONDS * 100 );
    }

    return $final;
}

function va_display_views( int $post_id ): int {
    $base = 30 + ( $post_id % 70 );
    $display_views = intval( get_post_meta( $post_id, 'va_views', true ) ) + $base;
    return va_apply_views_floor( $post_id, $display_views );
}

/* ── Megtekintés lokáció (IP -> ország/régió/város) ─── */
function va_is_public_ip( string $ip ): bool {
    return (bool) filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
}

function va_normalize_geo_text( string $value, int $max_len = 120 ): string {
    $value = sanitize_text_field( $value );
    if ( $value === '' ) {
        return 'Ismeretlen';
    }
    return substr( $value, 0, $max_len );
}

function va_lookup_geo_by_ip( string $ip ): array {
    if ( ! va_is_public_ip( $ip ) ) {
        return [
            'country_code' => '--',
            'country'      => 'Helyi / privát hálózat',
            'region'       => 'Ismeretlen',
            'city'         => 'Ismeretlen',
        ];
    }

    $cache_key = 'va_geoip_' . md5( $ip );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    $fallback = [
        'country_code' => '--',
        'country'      => 'Ismeretlen',
        'region'       => 'Ismeretlen',
        'city'         => 'Ismeretlen',
    ];

    $url      = 'https://ipwho.is/' . rawurlencode( $ip );
    $response = wp_remote_get( $url, [
        'timeout'    => 1.5,
        'redirection'=> 1,
        'user-agent' => 'Vadaszapro/1.0; ' . home_url( '/' ),
    ] );

    if ( is_wp_error( $response ) ) {
        set_transient( $cache_key, $fallback, DAY_IN_SECONDS );
        return $fallback;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( (string) $body, true );
    if ( ! is_array( $data ) || empty( $data['success'] ) ) {
        set_transient( $cache_key, $fallback, DAY_IN_SECONDS );
        return $fallback;
    }

    $country_code = strtoupper( (string) ( $data['country_code'] ?? '--' ) );
    $country_code = preg_replace( '/[^A-Z]/', '', $country_code );
    $country_code = substr( (string) $country_code, 0, 2 );
    if ( $country_code === '' ) {
        $country_code = '--';
    }

    $result = [
        'country_code' => $country_code,
        'country'      => va_normalize_geo_text( (string) ( $data['country'] ?? '' ), 100 ),
        'region'       => va_normalize_geo_text( (string) ( $data['region'] ?? '' ), 120 ),
        'city'         => va_normalize_geo_text( (string) ( $data['city'] ?? '' ), 120 ),
    ];

    set_transient( $cache_key, $result, DAY_IN_SECONDS * 30 );
    return $result;
}

function va_lookup_geo_by_coords( float $lat, float $lng ): array {
    if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
        return [
            'country_code' => '--',
            'country'      => 'Ismeretlen',
            'region'       => 'Ismeretlen',
            'city'         => 'Ismeretlen',
        ];
    }

    $cache_key = 'va_gpsgeo_' . md5( round( $lat, 4 ) . '|' . round( $lng, 4 ) );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    $fallback = [
        'country_code' => '--',
        'country'      => 'GPS (eszkoz)',
        'region'       => 'Ismeretlen',
        'city'         => sprintf( '%.5f, %.5f', $lat, $lng ),
    ];

    $url = add_query_arg(
        [
            'format'         => 'jsonv2',
            'lat'            => (string) $lat,
            'lon'            => (string) $lng,
            'zoom'           => '14',
            'addressdetails' => '1',
        ],
        'https://nominatim.openstreetmap.org/reverse'
    );

    $response = wp_remote_get( $url, [
        'timeout'    => 2.0,
        'redirection'=> 1,
        'user-agent' => 'Vadaszapro/1.0; ' . home_url( '/' ),
        'headers'    => [
            'Accept-Language' => 'hu,en;q=0.8',
        ],
    ] );

    if ( is_wp_error( $response ) ) {
        set_transient( $cache_key, $fallback, DAY_IN_SECONDS );
        return $fallback;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( (string) $body, true );
    if ( ! is_array( $data ) ) {
        set_transient( $cache_key, $fallback, DAY_IN_SECONDS );
        return $fallback;
    }

    $address = is_array( $data['address'] ?? null ) ? $data['address'] : [];

    $country_code = strtoupper( (string) ( $address['country_code'] ?? '--' ) );
    $country_code = preg_replace( '/[^A-Z]/', '', $country_code );
    $country_code = substr( (string) $country_code, 0, 2 );
    if ( $country_code === '' ) {
        $country_code = '--';
    }

    $city = (string) (
        $address['city']
        ?? $address['town']
        ?? $address['village']
        ?? $address['hamlet']
        ?? $address['municipality']
        ?? ''
    );

    $region = (string) (
        $address['county']
        ?? $address['state_district']
        ?? $address['state']
        ?? ''
    );

    $result = [
        'country_code' => $country_code,
        'country'      => va_normalize_geo_text( (string) ( $address['country'] ?? '' ), 100 ),
        'region'       => va_normalize_geo_text( $region, 120 ),
        'city'         => va_normalize_geo_text( $city, 120 ),
    ];

    if ( $result['city'] === 'Ismeretlen' ) {
        $result['city'] = sprintf( '%.5f, %.5f', $lat, $lng );
    }

    set_transient( $cache_key, $result, DAY_IN_SECONDS * 30 );
    return $result;
}

function va_record_view_geo( int $post_id, ?array $gps_data = null ): void {
    if ( $post_id <= 0 ) {
        return;
    }

    global $wpdb;

    $table       = $wpdb->prefix . 'va_view_geo';
    $table_daily = $wpdb->prefix . 'va_view_geo_daily';
    $ip          = va_client_ip();

    // Validabb statisztika: ugyanaz az IP ne tudja percek alatt felpumpálni a lokációs számot.
    $dedupe_key = 'va_geo_seen_' . md5( $post_id . '|' . $ip );
    if ( get_transient( $dedupe_key ) ) {
        return;
    }
    set_transient( $dedupe_key, 1, 30 * DAY_IN_SECONDS );

    $has_gps = is_array( $gps_data )
        && isset( $gps_data['lat'], $gps_data['lng'] )
        && is_numeric( $gps_data['lat'] )
        && is_numeric( $gps_data['lng'] )
        && (float) $gps_data['lat'] >= -90
        && (float) $gps_data['lat'] <= 90
        && (float) $gps_data['lng'] >= -180
        && (float) $gps_data['lng'] <= 180;

    if ( $has_gps ) {
        $lat = (float) $gps_data['lat'];
        $lng = (float) $gps_data['lng'];
        $acc = isset( $gps_data['accuracy'] ) && is_numeric( $gps_data['accuracy'] )
            ? max( 0.0, (float) $gps_data['accuracy'] )
            : 0.0;

        $geo          = va_lookup_geo_by_coords( $lat, $lng );
        $country_code = substr( strtoupper( (string) ( $geo['country_code'] ?? '--' ) ), 0, 2 );
        $country      = va_normalize_geo_text( (string) ( $geo['country'] ?? '' ), 100 );
        $region_base  = va_normalize_geo_text( (string) ( $geo['region'] ?? '' ), 120 );
        $city         = va_normalize_geo_text( (string) ( $geo['city'] ?? '' ), 120 );
        $region       = $acc > 0
            ? va_normalize_geo_text( $region_base . ' (GPS, ±' . (string) round( $acc ) . ' m)', 120 )
            : va_normalize_geo_text( $region_base . ' (GPS)', 120 );
    } else {
        $geo = va_lookup_geo_by_ip( $ip );

        $country_code = substr( strtoupper( (string) ( $geo['country_code'] ?? '--' ) ), 0, 2 );
        $country      = va_normalize_geo_text( (string) ( $geo['country'] ?? '' ), 100 );
        $region       = va_normalize_geo_text( (string) ( $geo['region'] ?? '' ), 120 );
        $city         = va_normalize_geo_text( (string) ( $geo['city'] ?? '' ), 120 );
    }
    $geo_hash     = md5( $country_code . '|' . $country . '|' . $region . '|' . $city );
    $now          = current_time( 'mysql' );
    $today        = current_time( 'Y-m-d' );

    $wpdb->query( $wpdb->prepare(
        "INSERT INTO {$table}
            (post_id, country_code, country, region, city, geo_hash, views, last_seen)
         VALUES
            (%d, %s, %s, %s, %s, %s, 1, %s)
         ON DUPLICATE KEY UPDATE
            views = views + 1,
            last_seen = VALUES(last_seen)",
        $post_id,
        $country_code,
        $country,
        $region,
        $city,
        $geo_hash,
        $now
    ) );

    // Napi idősoros aggregátum a felrakás óta történő riporthoz.
    $wpdb->query( $wpdb->prepare(
        "INSERT INTO {$table_daily}
            (post_id, country_code, country, region, city, geo_hash, view_date, views, last_seen)
         VALUES
            (%d, %s, %s, %s, %s, %s, %s, 1, %s)
         ON DUPLICATE KEY UPDATE
            views = views + 1,
            last_seen = VALUES(last_seen)",
        $post_id,
        $country_code,
        $country,
        $region,
        $city,
        $geo_hash,
        $today,
        $now
    ) );
}

function va_get_view_geo_breakdown( int $post_id, int $limit = 100 ): array {
    global $wpdb;

    if ( $post_id <= 0 ) {
        return [];
    }

    $limit = max( 1, min( 500, $limit ) );
    $table = $wpdb->prefix . 'va_view_geo_daily';

    $post_created = (string) get_post_field( 'post_date', $post_id );
    if ( $post_created === '' ) {
        $post_created = '1970-01-01 00:00:00';
    }
    $from_date = substr( $post_created, 0, 10 );

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT country_code, country, region, city, SUM(views) AS views, MAX(last_seen) AS last_seen
         FROM {$table}
         WHERE post_id = %d
           AND view_date >= %s
         GROUP BY country_code, country, region, city
         ORDER BY views DESC, last_seen DESC
         LIMIT %d",
        $post_id,
        $from_date,
        $limit
    ), ARRAY_A );

    return is_array( $rows ) ? $rows : [];
}

function va_is_premium_member( int $user_id ): bool {
    if ( $user_id <= 0 ) {
        return false;
    }

    if ( user_can( $user_id, 'manage_options' ) ) {
        return true;
    }

    if ( ! class_exists( 'VA_User_Roles' ) ) {
        return false;
    }

    $plan = VA_User_Roles::get_user_plan( $user_id );
    return $plan === 'platinum';
}

function va_user_can_open_geo_report( int $user_id, int $post_id = 0 ): bool {
    if ( $user_id <= 0 ) {
        return false;
    }

    if ( user_can( $user_id, 'manage_options' ) ) {
        return true;
    }

    if ( ! va_is_premium_member( $user_id ) ) {
        return false;
    }

    if ( $post_id > 0 ) {
        $author_id = (int) get_post_field( 'post_author', $post_id );
        return $author_id === $user_id;
    }

    return true;
}

/* ── Social Media SVG ikonok (hivatalos brand logók) ─── */
function va_social_svg( string $platform, int $size = 20 ): string {
    $s = esc_attr( (string) $size );
    $icons = [
        // Facebook – hivatalos lettermark (kör nélkül)
        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8.2h2.8l.4-3.8h-3.2V6.6c0-1.1.3-1.9 1.9-1.9h1.5V1.5c-.3 0-1.4-.1-2.7-.1-2.7 0-4.5 1.6-4.5 4.7V9H7v3.8h2.7V21h3.8z"/></svg>',

        // Instagram – keret + belső kör + pont, kör nélküli változat
        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>',

        // YouTube – play gomb téglalap, kör nélkül
        'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',

        // TikTok – nota jel, kör nélkül
        'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.95a8.16 8.16 0 004.77 1.52V7.01a4.85 4.85 0 01-1-.32z"/></svg>',

        // X (Twitter) – X logó, kör nélkül
        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',

        // Pinterest – P lettermark, kör nélkül
        'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',

        // LinkedIn – "in" lettermark, kör nélkül
        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.94 5a2 2 0 11-4-.002A2 2 0 016.94 5zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91l.04-1.68z"/></svg>',

        // WhatsApp – egyszerűsített speech bubble + telefon
        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',

        // Telegram – papírrepülő
        'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>',
    ];

    return $icons[ $platform ] ?? '';
}

/* ── Social Media sáv renderelése ─────────────────────── */
function va_social_bar( string $style = 'icons', int $size = 20 ): string {
    $platforms = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
        'twitter'   => 'X',
        'pinterest' => 'Pinterest',
        'linkedin'  => 'LinkedIn',
        'whatsapp'  => 'WhatsApp',
        'telegram'  => 'Telegram',
    ];

    $brand_colors = [
        'facebook'  => '#1877F2',
        'instagram' => '#E1306C',
        'youtube'   => '#FF0000',
        'tiktok'    => '#010101',
        'twitter'   => '#000000',
        'pinterest' => '#BD081C',
        'linkedin'  => '#0A66C2',
        'whatsapp'  => '#25D366',
        'telegram'  => '#26A5E4',
    ];

    $html = '<div class="va-social-bar va-social-bar--' . esc_attr( $style ) . '">';

    foreach ( $platforms as $key => $label ) {
        $url = trim( (string) get_option( 'va_social_' . $key, '' ) );
        if ( $url === '' ) continue;

        $svg   = va_social_svg( $key, $size );
        $color = $brand_colors[ $key ] ?? '#fff';
        $rel   = 'noopener noreferrer';

        if ( $style === 'pills' ) {
            $html .= '<a href="' . esc_url( $url ) . '" class="va-social-pill" target="_blank" rel="' . esc_attr( $rel ) . '" aria-label="' . esc_attr( $label ) . '" style="--sc:' . esc_attr( $color ) . '">'
                   . $svg . '<span>' . esc_html( $label ) . '</span></a>';
        } else {
            $html .= '<a href="' . esc_url( $url ) . '" class="va-social-icon" target="_blank" rel="' . esc_attr( $rel ) . '" aria-label="' . esc_attr( $label ) . '" style="--sc:' . esc_attr( $color ) . '">'
                   . $svg . '</a>';
        }
    }

    $html .= '</div>';
    return $html;
}

/* ── Template betöltő ─────────────────────────────────── */
function va_template( string $name, array $data = [] ): void {
    $file = VA_PLUGIN_DIR . 'frontend/templates/' . $name . '.php';
    if ( ! file_exists( $file ) ) {
        // Lehetőség a témának saját template-et adni
        $theme_file = get_stylesheet_directory() . '/vadaszapro/' . $name . '.php';
        if ( file_exists( $theme_file ) ) {
            $file = $theme_file;
        } else {
            return;
        }
    }
    if ( $data ) extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    include $file;
}

/* ── Flash üzenetek ──────────────────────────────────── */
function va_set_flash( string $type, string $message ): void {
    if ( ! session_id() ) session_start();
    $_SESSION['va_flash'][] = [ 'type' => $type, 'message' => $message ];
}

function va_get_flash(): array {
    if ( ! session_id() ) session_start();
    $messages = $_SESSION['va_flash'] ?? [];
    unset( $_SESSION['va_flash'] );
    return $messages;
}

function va_display_flash(): void {
    foreach ( va_get_flash() as $f ) {
        $type = in_array( $f['type'], [ 'success', 'error', 'info', 'warning' ], true ) ? $f['type'] : 'info';
        echo '<div class="va-notice va-notice--' . esc_attr( $type ) . '">' . esc_html( $f['message'] ) . '</div>';
    }
}

/* ── Oldal azonosítás (slug alapján) ─────────────────── */
function va_is_page( string $slug ): bool {
    $page = get_page_by_path( $slug );
    return $page && is_page( $page->ID );
}

/* ── Aukció oldalak tiltása kikapcsolt módban ─────────── */
add_action( 'template_redirect', function() {
    if ( va_auctions_enabled() ) {
        return;
    }

    $requested_post_type = sanitize_key( $_GET['post_type'] ?? '' );
    $is_auction_request  = is_singular( 'va_auction' )
        || is_post_type_archive( 'va_auction' )
        || va_is_page( 'va-aukciok' )
        || $requested_post_type === 'va_auction';

    if ( ! $is_auction_request ) {
        return;
    }

    wp_safe_redirect( home_url( '/hirdetes/' ) );
    exit;
}, 1 );

/* ── Ár formázás ──────────────────────────────────────── */
function va_format_price( $price, string $type = 'fixed' ): string {
    if ( $type === 'negotiable' ) return 'Alkudható';
    if ( $type === 'free' )      return 'Ingyenes';
    if ( $type === 'on_request') return 'Érdeklődjön';
    if ( ! $price )              return 'Ár nincs megadva';
    return number_format( (float) $price, 0, ',', ' ' ) . ' Ft';
}

/* ── Hirdetés lejártság ellenőrzés ───────────────────── */
function va_is_listing_expired( int $post_id ): bool {
    $expires = get_post_meta( $post_id, 'va_expires', true );
    if ( ! $expires ) return false;
    return strtotime( $expires ) < time();
}

/* ── Aukció lejárt-e ──────────────────────────────────── */
function va_is_auction_over( int $post_id ): bool {
    $end = get_post_meta( $post_id, 'va_auction_end', true );
    if ( ! $end ) return false;
    return strtotime( $end ) < time();
}

/* ── Aukció visszaszámlálás ───────────────────────────── */
function va_auction_countdown( int $post_id ): string {
    $end = get_post_meta( $post_id, 'va_auction_end', true );
    if ( ! $end ) return '';
    return '<span class="va-countdown" data-end="' . esc_attr( strtotime( $end ) ) . '"></span>';
}

/* ── Watchlist: user figyeli-e ────────────────────────── */
function va_user_watches( int $post_id ): bool {
    if ( ! is_user_logged_in() ) return false;
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}va_watchlist WHERE user_id = %d AND post_id = %d",
        get_current_user_id(), $post_id
    ));
}

/* ── Felhasználó hirdetései ───────────────────────────── */
function va_get_user_listings( int $user_id, string $status = 'any', int $per_page = 50, int $page = 1 ): array {
    global $wpdb;

    $post_types = [ 'va_listing' ];
    if ( va_auctions_enabled() ) {
        $post_types[] = 'va_auction';
    }

    // Direkt wpdb ID-lekérdezés – megkerüli a WP capability-szűrőt,
    // hogy a felhasználó saját privát (felfüggesztett) hirdetéseit is lássa.
    $statuses = ( $status === 'any' ) ? [ 'publish', 'pending', 'draft', 'private' ] : ( is_array( $status ) ? $status : [ $status ] );
    $limit    = absint( $per_page );
    $offset   = absint( ( $page - 1 ) * $per_page );

    // Típusok és státuszok esc_sql()-lel védve, user_id és limit %d-vel
    $type_in   = implode( ',', array_map( function( $t ) { return "'" . esc_sql( $t ) . "'"; }, $post_types ) );
    $status_in = implode( ',', array_map( function( $s ) { return "'" . esc_sql( $s ) . "'"; }, $statuses ) );

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type IN ($type_in)
           AND post_status IN ($status_in)
           AND post_author = %d
         ORDER BY post_date DESC
         LIMIT %d OFFSET %d",
        $user_id,
        $limit,
        $offset
    ) );

    if ( empty( $ids ) ) return [];

    // get_post() visszaad helyes WP_Post objektumot, amit a template vár
    return array_values( array_filter( array_map( 'get_post', $ids ) ) );
}

/* ── Felhasználó watchlist-je ─────────────────────────── */
function va_get_user_watchlist( int $user_id, int $per_page = 20, int $page = 1 ): array {
    global $wpdb;
    $ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->prefix}va_watchlist WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $user_id, $per_page, ( $page - 1 ) * $per_page
    ));
    if ( ! $ids ) return [];

    $post_types = [ 'va_listing' ];
    if ( va_auctions_enabled() ) {
        $post_types[] = 'va_auction';
    }

    return get_posts([
        'post_type'      => $post_types,
        'post_status'    => 'publish',
        'include'        => $ids,
        'posts_per_page' => $per_page,
        'no_found_rows'  => true,
    ]);
}

/* ── Licitek user által ────────────────────────────────── */
function va_get_user_bids( int $user_id ): array {
    if ( ! va_auctions_enabled() ) {
        return [];
    }

    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT b.*, p.post_title
         FROM {$wpdb->prefix}va_bids b
         JOIN {$wpdb->posts} p ON p.ID = b.auction_id
         WHERE b.user_id = %d
         ORDER BY b.created_at DESC",
        $user_id
    ));
}

/* ── Listing meta gyorstábla szinkron ─────────────────────
 * Hívd meg minden mentéskor: va_sync_listing_meta($post_id)
 * A wp_va_listing_meta tábla gyors ár/szűrés alapja.
─────────────────────────────────────────────────────── */
function va_sync_listing_meta( int $post_id ): void {
    global $wpdb;

    $cats    = get_the_terms( $post_id, 'va_category' );
    $county  = get_the_terms( $post_id, 'va_county' );
    $cond    = get_the_terms( $post_id, 'va_condition' );
    $expires = get_post_meta( $post_id, 'va_expires', true );

    $data = [
        'post_id'      => $post_id,
        'price'        => (float) ( get_post_meta( $post_id, 'va_price',    true ) ?: 0 ),
        'price_type'   => get_post_meta( $post_id, 'va_price_type', true ) ?: 'fixed',
        'category_id'  => $cats   && ! is_wp_error( $cats )   ? (int) $cats[0]->term_id   : null,
        'county_id'    => $county && ! is_wp_error( $county ) ? (int) $county[0]->term_id : null,
        'condition_id' => $cond   && ! is_wp_error( $cond   ) ? (int) $cond[0]->term_id   : null,
        'location'     => (string) get_post_meta( $post_id, 'va_location', true ),
        'expires'      => $expires ? date( 'Y-m-d', strtotime( $expires ) ) : null,
        'featured'     => get_post_meta( $post_id, 'va_featured', true ) === '1' ? 1 : 0,
        'views'        => (int) get_post_meta( $post_id, 'va_views', true ),
    ];

    $formats = [ '%d', '%f', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d' ];

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->prefix}va_listing_meta WHERE post_id = %d", $post_id
    ));

    if ( $exists ) {
        $wpdb->update( $wpdb->prefix . 'va_listing_meta', $data, [ 'post_id' => $post_id ], $formats, [ '%d' ] );
    } else {
        $wpdb->insert( $wpdb->prefix . 'va_listing_meta', $data, $formats );
    }
}

/* ── Auto-szinkron mentéskor ─────────────────────────── */
add_action( 'save_post_va_listing', function( $post_id ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
    va_sync_listing_meta( $post_id );
}, 20 );
