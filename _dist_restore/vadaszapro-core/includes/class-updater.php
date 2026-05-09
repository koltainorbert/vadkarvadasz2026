<?php
/**
 * VA_Updater – automatikus frissítés GitHub Releases alapján.
 *
 * Hogyan működik:
 *  1. WP 12 óránként ellenőrzi a plugin frissítéseket (transient: update_plugins).
 *  2. Ez az osztály belehorgol a `pre_set_site_transient_update_plugins` filterbe,
 *     lekéri a GitHub legújabb Release tag-jét, és ha nagyobb a jelenlegi
 *     VA_VERSION-nél, megjelenik a szokásos WP "Frissítés" gomb.
 *  3. A `plugins_api` filterbe is belehorgol, hogy a részletes popup megjelenjen.
 *  4. A zip-et közvetlenül a GitHub Release asset URL-jéről tölti le.
 *
 * GitHub repo: settings → Releases → minden Release-hez csatolj egy zip-et
 * (pl. vadaszapro-core-1.0.2.zip) VAGY használd a GitHub auto-generate zip-jét.
 *
 * Beállítás (vadaszapro-core.php-ban):
 *   define( 'VA_GITHUB_REPO', 'tulajdonos/vadaszapro-core' );
 *   // opcionális privát repo token:
 *   define( 'VA_GITHUB_TOKEN', '' );
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Updater {

    private static string $repo  = VA_GITHUB_REPO ?? '';
    private static string $token = VA_GITHUB_TOKEN ?? '';
    private static string $slug  = 'vadaszapro-core/vadaszapro-core.php';

    public static function init(): void {
        if ( empty( self::$repo ) ) return;

        add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'check_update'   ] );
        add_filter( 'plugins_api',                           [ __CLASS__, 'plugin_info'    ], 10, 3 );
        add_filter( 'upgrader_source_selection',             [ __CLASS__, 'fix_folder_name'], 10, 4 );
    }

    /* ── GitHub API lekérés ─────────────────────────── */
    private static function get_latest_release(): ?object {
        $cache_key = 'va_github_release_' . md5( self::$repo );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) return $cached;

        $url  = 'https://api.github.com/repos/' . self::$repo . '/releases/latest';
        $args = [
            'timeout'    => 10,
            'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            'headers'    => [],
        ];
        if ( ! empty( self::$token ) ) {
            $args['headers']['Authorization'] = 'token ' . self::$token;
        }

        $response = wp_remote_get( $url, $args );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return null;
        }

        $body    = json_decode( wp_remote_retrieve_body( $response ) );
        $release = $body ?: null;

        if ( $release ) {
            set_transient( $cache_key, $release, 6 * HOUR_IN_SECONDS );
        }
        return $release;
    }

    /* ── Frissítés ellenőrzés ───────────────────────── */
    public static function check_update( object $transient ): object {
        if ( empty( $transient->checked ) ) return $transient;

        $release = self::get_latest_release();
        if ( ! $release ) return $transient;

        // tag_name pl. "v1.0.2" vagy "1.0.2"
        $latest_version = ltrim( $release->tag_name ?? '', 'v' );
        $current        = VA_VERSION;

        if ( version_compare( $latest_version, $current, '>' ) ) {
            // Zip URL: első asset VAGY GitHub auto-zip
            $zip_url = '';
            if ( ! empty( $release->assets ) ) {
                foreach ( $release->assets as $asset ) {
                    if ( str_ends_with( $asset->name ?? '', '.zip' ) ) {
                        $zip_url = $asset->browser_download_url;
                        break;
                    }
                }
            }
            if ( ! $zip_url ) {
                // Fallback: GitHub auto-generated zip
                $zip_url = 'https://github.com/' . self::$repo . '/archive/refs/tags/' . $release->tag_name . '.zip';
            }

            $transient->response[ self::$slug ] = (object) [
                'id'          => self::$slug,
                'slug'        => 'vadaszapro-core',
                'plugin'      => self::$slug,
                'new_version' => $latest_version,
                'url'         => 'https://github.com/' . self::$repo,
                'package'     => $zip_url,
                'icons'       => [],
                'banners'     => [],
                'tested'      => get_bloginfo( 'version' ),
                'requires_php'=> '7.4',
                'compatibility'=> new stdClass(),
            ];
        } else {
            // Nincs frissítés – no_update-be is rögzíteni kell
            $transient->no_update[ self::$slug ] = (object) [
                'id'          => self::$slug,
                'slug'        => 'vadaszapro-core',
                'plugin'      => self::$slug,
                'new_version' => $current,
                'url'         => 'https://github.com/' . self::$repo,
                'package'     => '',
            ];
        }

        return $transient;
    }

    /* ── Plugin info popup (Részletek gomb) ─────────── */
    public static function plugin_info( mixed $result, string $action, object $args ): mixed {
        if ( $action !== 'plugin_information' ) return $result;
        if ( ( $args->slug ?? '' ) !== 'vadaszapro-core' ) return $result;

        $release = self::get_latest_release();
        if ( ! $release ) return $result;

        $latest = ltrim( $release->tag_name ?? '', 'v' );
        $body   = wp_kses_post( $release->body ?? '' );

        return (object) [
            'name'          => 'VadászApró Core',
            'slug'          => 'vadaszapro-core',
            'version'       => $latest,
            'author'        => 'SDH',
            'homepage'      => 'https://github.com/' . self::$repo,
            'requires'      => '6.0',
            'tested'        => get_bloginfo( 'version' ),
            'requires_php'  => '7.4',
            'last_updated'  => $release->published_at ?? '',
            'sections'      => [
                'description' => '<p>VadászApró vadászati hirdetési rendszer WordPress plugin.</p>',
                'changelog'   => nl2br( $body ) ?: '<p>Lásd a GitHub Release oldalát.</p>',
            ],
            'download_link' => '',
        ];
    }

    /* ── Mappa átnevezés (GitHub zip-nél fontos) ─────── */
    public static function fix_folder_name( string $source, string $remote_source, object $upgrader, array $hook_extra ): string {
        global $wp_filesystem;

        if ( ( $hook_extra['plugin'] ?? '' ) !== self::$slug ) return $source;

        // GitHub archive zip-ből: "repo-tagname/" → "vadaszapro-core/"
        $expected   = $remote_source . 'vadaszapro-core/';
        if ( $wp_filesystem->is_dir( $expected ) ) return $source; // már jó

        // Keresés: mi van a remote_source-ban?
        $files = $wp_filesystem->dirlist( $remote_source );
        if ( ! $files ) return $source;

        $first = key( $files );
        $src   = trailingslashit( $remote_source . $first );
        if ( $wp_filesystem->is_dir( $src ) && $src !== $expected ) {
            $wp_filesystem->move( $src, $expected );
            return $expected;
        }

        return $source;
    }
}
