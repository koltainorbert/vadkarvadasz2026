<?php
/**
 * Plugin Name: VadászApró Core
 * Plugin URI:  https://vadaszapro.net
 * Description: Vadászati apróhirdetési rendszer – hirdetés, aukció, felhasználói fiók, reklámzónák.
 * Version:     1.0.1
 * Author:      SDH
 * Text Domain: vadaszapro
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VA_VERSION',        '1.1.5' );
define( 'VA_REWRITE_VER',   '1.0.9' );   // Növeld meg ha CPT/tax slug változik!
define( 'VA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VA_TEXT_DOMAIN', 'vadaszapro' );

// GitHub auto-update – állítsd be a saját repo-dat:
// Formátum: 'github-felhasznalonev/repo-neve'
// Privát repo esetén: define( 'VA_GITHUB_TOKEN', 'ghp_...' );
if ( ! defined( 'VA_GITHUB_REPO' ) )  define( 'VA_GITHUB_REPO',  '' );
if ( ! defined( 'VA_GITHUB_TOKEN' ) ) define( 'VA_GITHUB_TOKEN', '' );

/* ── Autoload includes ────────────────────────────── */
require_once VA_PLUGIN_DIR . 'includes/class-post-types.php';
require_once VA_PLUGIN_DIR . 'includes/class-vehicle-catalog.php';
require_once VA_PLUGIN_DIR . 'includes/class-taxonomy.php';
require_once VA_PLUGIN_DIR . 'includes/class-meta-fields.php';
require_once VA_PLUGIN_DIR . 'includes/class-mailer.php';
require_once VA_PLUGIN_DIR . 'includes/class-user-system.php';
require_once VA_PLUGIN_DIR . 'includes/class-user-roles.php';
require_once VA_PLUGIN_DIR . 'includes/class-auctions.php';
require_once VA_PLUGIN_DIR . 'includes/class-ad-zones.php';
require_once VA_PLUGIN_DIR . 'includes/class-ajax.php';
require_once VA_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once VA_PLUGIN_DIR . 'includes/class-updater.php';
require_once VA_PLUGIN_DIR . 'includes/class-page-renderer.php';
require_once VA_PLUGIN_DIR . 'includes/class-seo.php';
require_once VA_PLUGIN_DIR . 'includes/helpers.php';

require_once VA_PLUGIN_DIR . 'admin/class-form-builder.php'; // frontend is használja (VA_Form_Builder::get_fields)
require_once VA_PLUGIN_DIR . 'admin/class-settings-page.php'; // frontend is kell (wp_head CSS: pill + kártya stílusok)
require_once VA_PLUGIN_DIR . 'admin/class-admin.php'; // admin_bar_menu hookhoz frontenden is kell

if ( is_admin() ) {
    require_once VA_PLUGIN_DIR . 'admin/class-page-builder.php';
    require_once VA_PLUGIN_DIR . 'admin/class-dashboard.php';
    require_once VA_PLUGIN_DIR . 'admin/class-listing-edit.php';
    require_once VA_PLUGIN_DIR . 'admin/class-listing-columns.php';
}

/* ── Boot ────────────────────────────────────────── */
add_action( 'plugins_loaded', function () {
    VA_Post_Types::init();
    VA_Taxonomy::init();
    VA_Meta_Fields::init();
    VA_User_System::init();
    VA_User_Roles::init();
    VA_Auctions::init();
    VA_Ad_Zones::init();
    VA_Ajax::init();
    VA_Shortcodes::init();
    VA_Updater::init();
    VA_Page_Renderer::init();
    VA_SEO::init();

    // Settings Page init-je frontend-en is kell (wp_head CSS hookak: pill + kártya stílusok)
    VA_Settings_Page::init();

    if ( is_admin() ) {
        VA_Page_Builder::init();
        VA_Admin::init();
        VA_Listing_Columns::init();
        VA_Listing_Edit::init();
        VA_Form_Builder::init();
    } else {
        // Admin bar menü frontenden is kell
        add_action( 'admin_bar_menu', [ VA_Admin::class, 'register_admin_bar' ], 90 );
    }
});

/* ── Auto rewrite flush – verziókövetéssel ──────────
 * Ha VA_REWRITE_VER változik (pl. új CPT slug),
 * a WP automatikusan újragenerálja a rewrite táblákat.
 * Emberi beavatkozás nem kell.
────────────────────────────────────────────────── */
add_action( 'init', function () {
    // Rewrite flush: VA_REWRITE_VER változásakor VAGY plugin verzió változásakor (pl. FTP deploy után)
    $needs_flush = get_option( 'va_rewrite_ver' ) !== VA_REWRITE_VER
                || get_option( 'va_plugin_ver' )  !== VA_VERSION;
    if ( $needs_flush ) {
        flush_rewrite_rules( false );
        update_option( 'va_rewrite_ver', VA_REWRITE_VER );
        update_option( 'va_plugin_ver',  VA_VERSION );
    }
}, 999 );

// Hiányzó alapoldalak létrehozása futás közben (reaktiválás nélkül)
add_action( 'init', function () {
    if ( get_option( 'va_pages_created_v2' ) ) return;
    va_create_default_pages();
    update_option( 'va_pages_created_v2', '1', false );
}, 1 );

// Factory defaults biztos betöltése futás közben is (új telepítésnél)
add_action( 'init', function () {
    if ( ! get_option( 'va_factory_defaults_loaded' ) ) {
        va_load_factory_defaults();
    }
}, 2 );

// Lokációs megtekintés tábla automatikus létrehozása/frissítése
add_action( 'init', function () {
    $schema_ver = '1.1.0';
    if ( get_option( 'va_view_geo_table_ver' ) === $schema_ver ) {
        return;
    }

    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( va_get_view_geo_table_sql( $wpdb->get_charset_collate() ) );
    dbDelta( va_get_view_geo_daily_table_sql( $wpdb->get_charset_collate() ) );
    update_option( 'va_view_geo_table_ver', $schema_ver, false );
}, 4 );

// Demo minta hirdetesek betoltese uj telepiteskor
add_action( 'init', function () {
    if ( ! get_option( 'va_demo_listings_seeded' ) ) {
        va_seed_demo_listings();
    }
}, 30 );

/* ── Vadász Naptár – virtuális oldal (WP admin nélkül) ──────────────
 * A /vadasz-naptar/ URL betölti a theme page-vadasz-naptar.php-t
 * automatikusan, adatbázis bejegyzés nélkül.
──────────────────────────────────────────────────────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^vadasz-naptar/?$', 'index.php?va_virtual_page=vadasz-naptar', 'top' );
} );
add_filter( 'query_vars', function ( $vars ) {
    $vars[] = 'va_virtual_page';
    return $vars;
} );
add_filter( 'template_include', function ( $template ) {
    if ( get_query_var( 'va_virtual_page' ) !== 'vadasz-naptar' ) {
        return $template;
    }
    $t = locate_template( 'page-vadasz-naptar.php' );
    return $t ?: $template;
} );

/* ── Activation / Deactivation ───────────────────── */
register_activation_hook( __FILE__,   'va_activate'   );
register_deactivation_hook( __FILE__, 'va_deactivate' );

function va_get_view_geo_table_sql( string $charset ): string {
    global $wpdb;
    return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_view_geo (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id      BIGINT UNSIGNED NOT NULL,
        country_code CHAR(2)         NOT NULL DEFAULT '--',
        country      VARCHAR(100)    NOT NULL DEFAULT '',
        region       VARCHAR(120)    NOT NULL DEFAULT '',
        city         VARCHAR(120)    NOT NULL DEFAULT '',
        geo_hash     CHAR(32)        NOT NULL,
        views        BIGINT UNSIGNED NOT NULL DEFAULT 0,
        last_seen    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY post_geo (post_id, geo_hash),
        KEY post_id (post_id),
        KEY country_code (country_code),
        KEY views (views)
    ) $charset;";
}

function va_get_view_geo_daily_table_sql( string $charset ): string {
    global $wpdb;
    return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_view_geo_daily (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id      BIGINT UNSIGNED NOT NULL,
        country_code CHAR(2)         NOT NULL DEFAULT '--',
        country      VARCHAR(100)    NOT NULL DEFAULT '',
        region       VARCHAR(120)    NOT NULL DEFAULT '',
        city         VARCHAR(120)    NOT NULL DEFAULT '',
        geo_hash     CHAR(32)        NOT NULL,
        view_date    DATE            NOT NULL,
        views        BIGINT UNSIGNED NOT NULL DEFAULT 0,
        last_seen    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY post_geo_date (post_id, geo_hash, view_date),
        KEY post_date (post_id, view_date),
        KEY country_code (country_code),
        KEY views (views)
    ) $charset;";
}

function va_activate() {
    VA_Post_Types::init();
    VA_Taxonomy::init();
    flush_rewrite_rules();
    update_option( 'va_rewrite_ver', VA_REWRITE_VER );

    // Régi hourly cron törlése, új 5 perces ütemezés
    $old = wp_next_scheduled( 'va_close_expired_auctions' );
    if ( $old ) wp_unschedule_event( $old, 'va_close_expired_auctions' );
    if ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : get_option( 'va_enable_auctions', '1' ) === '1' ) {
        wp_schedule_event( time(), 'va_every_5min', 'va_close_expired_auctions' );
    }

    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    // Licitek táblája
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_bids (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        auction_id  BIGINT UNSIGNED NOT NULL,
        user_id     BIGINT UNSIGNED NOT NULL,
        amount      DECIMAL(12,2)    NOT NULL,
        created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY auction_id (auction_id),
        KEY user_id (user_id)
    ) $charset;";

    // Hirdetésfigyelő (értesítések) tábla
    $sql2 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_watchlist (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT UNSIGNED NOT NULL,
        post_id     BIGINT UNSIGNED NOT NULL,
        created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset;";

    // Hirdetés meta gyorstábla – kereshetőség és szűrés index nélküli meta_query helyett
    // 2-3M hirdetésnél ez a tábla teszi lehetővé a gyors ár/lokáció/kategória szűrést.
    $sql3 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_listing_meta (
        post_id     BIGINT UNSIGNED NOT NULL,
        price       DECIMAL(14,2)   DEFAULT NULL,
        price_type  VARCHAR(20)     DEFAULT 'fixed',
        county_id   BIGINT UNSIGNED DEFAULT NULL,
        category_id BIGINT UNSIGNED DEFAULT NULL,
        condition_id BIGINT UNSIGNED DEFAULT NULL,
        location    VARCHAR(100)    DEFAULT NULL,
        expires     DATE            DEFAULT NULL,
        featured    TINYINT(1)      NOT NULL DEFAULT 0,
        views       BIGINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (post_id),
        KEY price (price),
        KEY county_id (county_id),
        KEY category_id (category_id),
        KEY featured (featured),
        KEY expires (expires),
        KEY cat_price (category_id, price)
    ) $charset;";

    // Megtekintési lokáció aggregátum (ország/régió/város)
    $sql4 = va_get_view_geo_table_sql( $charset );
    $sql5 = va_get_view_geo_daily_table_sql( $charset );

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    dbDelta( $sql2 );
    dbDelta( $sql3 );
    dbDelta( $sql4 );
    dbDelta( $sql5 );
    update_option( 'va_view_geo_table_ver', '1.1.0', false );

    // Alapértelmezett oldalak létrehozása ha nem léteznek
    va_create_default_pages();

    // Factory defaults betöltése ha ez friss telepítés
    va_load_factory_defaults();
}

function va_load_factory_defaults(): void {
    if ( get_option( 'va_factory_defaults_loaded' ) ) {
        return; // Már be volt töltve korábban
    }

    $json_file = plugin_dir_path( __FILE__ ) . 'includes/factory-defaults.json';
    if ( ! file_exists( $json_file ) ) {
        return;
    }

    $content = file_get_contents( $json_file );
    if ( ! $content ) {
        return;
    }

    // BOM eltávolítás
    $content = ltrim( $content, "\xEF\xBB\xBF" );
    $data    = json_decode( $content, true );

    if ( ! is_array( $data ) || ! isset( $data['options'] ) || ! is_array( $data['options'] ) ) {
        return;
    }

    // Csak azokat az opciókat importáljuk amelyek még NINCSENEK beállítva
    foreach ( $data['options'] as $key => $value ) {
        if ( ! is_string( $key ) || strpos( $key, 'va_' ) !== 0 ) {
            continue;
        }
        if ( get_option( $key ) === false ) {
            add_option( $key, $value, '', 'no' );
        }
    }

    // Taxonómiák importálása (kategóriák, megyék, állapotok) ha üresek
    if ( isset( $data['taxonomies'] ) && is_array( $data['taxonomies'] ) ) {
        $by_tax = [];
        foreach ( $data['taxonomies'] as $term ) {
            $by_tax[ $term['taxonomy'] ][] = $term;
        }
        foreach ( $by_tax as $tax => $terms ) {
            $existing = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false, 'fields' => 'count' ] );
            if ( ! is_wp_error( $existing ) && (int) $existing === 0 ) {
                $slug_to_id = [];
                foreach ( $terms as $term ) {
                    $parent_id = 0;
                    if ( ! empty( $term['parent_slug'] ) && isset( $slug_to_id[ $term['parent_slug'] ] ) ) {
                        $parent_id = $slug_to_id[ $term['parent_slug'] ];
                    }
                    $result = wp_insert_term( $term['name'], $tax, [
                        'slug'        => $term['slug'],
                        'description' => $term['description'] ?? '',
                        'parent'      => $parent_id,
                    ] );
                    if ( ! is_wp_error( $result ) ) {
                        $tid = (int) $result['term_id'];
                        $slug_to_id[ $term['slug'] ] = $tid;
                        if ( ! empty( $term['meta'] ) && is_array( $term['meta'] ) ) {
                            foreach ( $term['meta'] as $mkey => $mval ) {
                                update_term_meta( $tid, $mkey, $mval );
                            }
                        }
                    }
                }
            }
        }
    }

    update_option( 'va_factory_defaults_loaded', '1' );
}

function va_deactivate() {
    flush_rewrite_rules();
}

function va_seed_demo_listings(): void {
    if ( get_option( 'va_demo_listings_seeded' ) ) {
        return;
    }

    $has_listings = get_posts( [
        'post_type'      => 'va_listing',
        'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ] );

    if ( ! empty( $has_listings ) ) {
        update_option( 'va_demo_listings_seeded', 'skipped_existing', false );
        return;
    }

    $admins = get_users( [
        'role'   => 'administrator',
        'number' => 1,
        'fields' => 'ID',
    ] );
    $author_id = ! empty( $admins ) ? (int) $admins[0] : 1;

    $samples = [
        [
            'title'         => 'BMW 320d M Sport - megkimelt allapot',
            'price'         => 8990000,
            'location'      => 'Budapest',
            'brand'         => 'BMW',
            'model'         => '320d',
            'year'          => '2020',
            'fuel'          => 'Dizel',
            'mileage'       => '128000',
            'category_slug' => 'csaladi-auto',
            'county_name'   => 'Budapest',
            'condition'     => 'Használt – Kiváló',
            'image'         => 'assets/demo/demo-auto-1.svg',
            'description'   => 'Rendszeresen szervizelt, azonnal hasznalhato allapotban. Gazdag felszereltseg, igenyes belso ter.',
        ],
        [
            'title'         => 'Suzuki Vitara 1.4 BoosterJet GL+ 4x4',
            'price'         => 6790000,
            'location'      => 'Győr',
            'brand'         => 'SUZUKI',
            'model'         => 'Vitara',
            'year'          => '2019',
            'fuel'          => 'Benzin',
            'mileage'       => '99000',
            'category_slug' => 'terepjaro',
            'county_name'   => 'Győr-Moson-Sopron',
            'condition'     => 'Használt – Jó',
            'image'         => 'assets/demo/demo-auto-2.svg',
            'description'   => 'Magyarorszagi, ellenorizheto eloeletu auto. Osszkerek meghajtas, megbizhato csaladi valasztas.',
        ],
        [
            'title'         => 'Volkswagen Polo 1.0 TSI Comfortline',
            'price'         => 4290000,
            'location'      => 'Debrecen',
            'brand'         => 'VOLKSWAGEN',
            'model'         => 'Polo',
            'year'          => '2018',
            'fuel'          => 'Benzin',
            'mileage'       => '142000',
            'category_slug' => 'kisauto',
            'county_name'   => 'Hajdú-Bihar',
            'condition'     => 'Használt – Jó',
            'image'         => 'assets/demo/demo-auto-3.svg',
            'description'   => 'Varosi es orszaguti hasznalatra is idealis, alacsony fogyasztasu modell, rendezett papirokkal.',
        ],
    ];

    $created = 0;

    foreach ( $samples as $sample ) {
        $post_id = wp_insert_post( [
            'post_type'    => 'va_listing',
            'post_status'  => 'publish',
            'post_title'   => $sample['title'],
            'post_content' => $sample['description'],
            'post_author'  => $author_id,
        ], true );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }

        update_post_meta( $post_id, 'va_price', (string) $sample['price'] );
        update_post_meta( $post_id, 'va_price_type', 'fixed' );
        update_post_meta( $post_id, 'va_location', (string) $sample['location'] );
        update_post_meta( $post_id, 'va_brand', (string) $sample['brand'] );
        update_post_meta( $post_id, 'va_model', (string) $sample['model'] );
        update_post_meta( $post_id, 'va_year', (string) $sample['year'] );
        update_post_meta( $post_id, 'va_fuel', (string) $sample['fuel'] );
        update_post_meta( $post_id, 'va_mileage', (string) $sample['mileage'] );
        update_post_meta( $post_id, 'va_demo_image', (string) $sample['image'] );

        $category = get_term_by( 'slug', (string) $sample['category_slug'], 'va_category' );
        if ( $category && ! is_wp_error( $category ) ) {
            wp_set_object_terms( $post_id, [ (int) $category->term_id ], 'va_category', false );
        }

        $county = get_term_by( 'name', (string) $sample['county_name'], 'va_county' );
        if ( $county && ! is_wp_error( $county ) ) {
            wp_set_object_terms( $post_id, [ (int) $county->term_id ], 'va_county', false );
        }

        $condition = get_term_by( 'name', (string) $sample['condition'], 'va_condition' );
        if ( $condition && ! is_wp_error( $condition ) ) {
            wp_set_object_terms( $post_id, [ (int) $condition->term_id ], 'va_condition', false );
        }

        if ( function_exists( 'va_sync_listing_meta' ) ) {
            va_sync_listing_meta( (int) $post_id );
        }

        $created++;
    }

    if ( $created > 0 ) {
        update_option( 'va_demo_listings_seeded', '1', false );
    }
}

function va_create_default_pages() {
    $pages = [
        'va-hirdetes-feladas'  => [ 'title' => 'Hirdetés feladása',   'content' => '[va_submit_listing]' ],
        'va-bejelentkezes'     => [ 'title' => 'Bejelentkezés',        'content' => '[va_login_form]' ],
        'va-regisztracio'      => [ 'title' => 'Regisztráció',         'content' => '[va_register_form]' ],
        'va-fiok'              => [ 'title' => 'Fiókom',               'content' => '[va_user_dashboard]' ],
        'va-hirdetes-kereses'  => [ 'title' => 'Hirdetések keresése',  'content' => '[va_listing_search]' ],
        'va-kredit-vasarlas'   => [ 'title' => 'Vásárlás',             'content' => '[va_buy_credits]' ],
    ];

    if ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : get_option( 'va_enable_auctions', '1' ) === '1' ) {
        $pages['va-aukciok'] = [ 'title' => 'Aukciók', 'content' => '[va_auction_list]' ];
    }

    foreach ( $pages as $slug => $data ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post([
                'post_title'   => $data['title'],
                'post_name'    => $slug,
                'post_content' => $data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
        }
    }
}
