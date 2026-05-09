<?php
/**
 * Központi SEO réteg:
 * - Meta leírás, canonical, OG, Twitter
 * - JSON-LD schema (WebSite, Organization, Breadcrumb, ItemList, Product/Auction)
 * - XML sitemap index + rész-sitemapok
 * - robots.txt + noindex vezérlés kereső/404 oldalakra
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_SEO {

    const SITEMAP_PER_PAGE = 100;
    const RM_FOCUS_META_KEY = 'rank_math_focus_keyword';
    const RM_FOCUS_SEED_OPTION = 'va_rankmath_focus_seed_v1';

    public static function init(): void {
        add_action( 'save_post', [ __CLASS__, 'maybe_seed_rankmath_focus_keyword_on_save' ], 20, 3 );

        if ( is_admin() ) {
            add_action( 'admin_init', [ __CLASS__, 'seed_missing_rankmath_focus_keywords' ] );
            return;
        }

        add_action( 'init', [ __CLASS__, 'register_sitemap_routes' ] );
        add_filter( 'query_vars', [ __CLASS__, 'register_query_vars' ] );
        add_action( 'template_redirect', [ __CLASS__, 'maybe_render_sitemap' ], 0 );
        add_action( 'template_redirect', [ __CLASS__, 'start_social_meta_buffer' ], 1 );

        add_action( 'wp_head', [ __CLASS__, 'render_head_meta' ], 1 );
        add_action( 'wp_head', [ __CLASS__, 'render_schema' ], 90 );

        add_filter( 'wp_robots', [ __CLASS__, 'filter_wp_robots' ] );
        // Teljes robots.txt kontroll: priority 1-en futunk, mielőtt bárki más (WP core, Rank Math) outputolna.
        add_action( 'do_robots', [ __CLASS__, 'output_robots_txt' ], 1 );
        add_filter( 'document_title_parts', [ __CLASS__, 'filter_document_title_parts' ] );

        // Rank Math felülírhatja az OG/Twitter title-t, ezért közvetlenül ide is bekötjük.
        add_filter( 'rank_math/opengraph/facebook/title', [ __CLASS__, 'rank_math_social_title' ] );
        add_filter( 'rank_math/opengraph/twitter/title', [ __CLASS__, 'rank_math_social_title' ] );
        add_filter( 'rank_math/opengraph/facebook/description', [ __CLASS__, 'rank_math_social_description' ] );
        add_filter( 'rank_math/opengraph/twitter/description', [ __CLASS__, 'rank_math_social_description' ] );
        add_filter( 'rank_math/frontend/title', [ __CLASS__, 'rank_math_social_title' ] );
        add_filter( 'rank_math/frontend/description', [ __CLASS__, 'rank_math_social_description' ] );
    }

    private static function rankmath_focus_target_post_types(): array {
        return [ 'post', 'page', 'va_listing', 'va_auction' ];
    }

    private static function build_focus_keyword_from_post( WP_Post $post ): string {
        $title = trim( wp_strip_all_tags( (string) $post->post_title ) );
        if ( $title === '' ) {
            return '';
        }

        // Rank Math a vesszovel tagolt listat var, de mar egyetlen kulcsszo is eleg.
        return mb_substr( $title, 0, 191 );
    }

    public static function maybe_seed_rankmath_focus_keyword_on_save( int $post_id, WP_Post $post, bool $update ): void {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( ! in_array( $post->post_type, self::rankmath_focus_target_post_types(), true ) ) {
            return;
        }

        $existing = trim( (string) get_post_meta( $post_id, self::RM_FOCUS_META_KEY, true ) );
        if ( $existing !== '' ) {
            return;
        }

        $keyword = self::build_focus_keyword_from_post( $post );
        if ( $keyword !== '' ) {
            update_post_meta( $post_id, self::RM_FOCUS_META_KEY, $keyword );
        }
    }

    public static function seed_missing_rankmath_focus_keywords(): void {
        if ( get_option( self::RM_FOCUS_SEED_OPTION ) ) {
            return;
        }

        $post_types = self::rankmath_focus_target_post_types();
        $paged = 1;
        $processed = 0;

        do {
            $q = new WP_Query([
                'post_type'      => $post_types,
                'post_status'    => [ 'publish', 'future', 'draft', 'pending', 'private' ],
                'fields'         => 'ids',
                'posts_per_page' => 100,
                'paged'          => $paged,
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            ]);

            if ( empty( $q->posts ) ) {
                break;
            }

            foreach ( $q->posts as $post_id ) {
                $post_id = (int) $post_id;
                $existing = trim( (string) get_post_meta( $post_id, self::RM_FOCUS_META_KEY, true ) );
                if ( $existing !== '' ) {
                    continue;
                }

                $post = get_post( $post_id );
                if ( ! $post instanceof WP_Post ) {
                    continue;
                }

                $keyword = self::build_focus_keyword_from_post( $post );
                if ( $keyword !== '' ) {
                    update_post_meta( $post_id, self::RM_FOCUS_META_KEY, $keyword );
                    $processed++;
                }
            }

            $count = count( $q->posts );
            wp_reset_postdata();
            $paged++;
        } while ( $count === 100 );

        update_option( self::RM_FOCUS_SEED_OPTION, [
            'done_at'   => gmdate( 'c' ),
            'processed' => $processed,
        ], false );
    }

    public static function rank_math_social_title( $title ): string {
        return self::sanitize_seo_copy( self::social_title( is_string( $title ) ? $title : wp_get_document_title() ) );
    }

    public static function rank_math_social_description( $description ): string {
        if ( is_singular( 'va_listing' ) ) {
            return self::sanitize_seo_copy( self::social_description() );
        }
        $desc = is_string( $description ) ? trim( wp_strip_all_tags( $description ) ) : '';
        if ( $desc === '' || preg_match( '/^\[[^\]]+\]$/', $desc ) ) {
            $desc = self::meta_description();
        }
        return self::sanitize_seo_copy( $desc );
    }

    public static function filter_document_title_parts( array $parts ): array {
        if ( self::is_listing_search_landing() ) {
            $landing = self::get_search_landing_context();
            $parts['title'] = $landing['title'];
            unset( $parts['tagline'] );
            return $parts;
        }

        if ( is_front_page() ) {
            $parts['title'] = 'Eladó autók és motorok | Weingartner Autó';
            unset( $parts['tagline'] );
            return $parts;
        }

        if ( is_singular( 'va_listing' ) ) {
            $parts['title'] = self::listing_browser_title( get_queried_object_id() );
            unset( $parts['tagline'] );
            return $parts;
        }

        if ( is_post_type_archive( 'va_listing' ) ) {
            $parts['title'] = 'Eladó autók és motorok';
            return $parts;
        }

        if ( is_tax( 'va_category' ) ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term ) {
                $parts['title'] = 'Eladó ' . $term->name;
            }
        }

        return $parts;
    }

    private static function sanitize_seo_copy( string $text ): string {
        $clean = trim( wp_strip_all_tags( $text ) );
        if ( $clean === '' ) {
            return $clean;
        }

        $replacements = [
            'VadászApró' => 'Weingartner Autó',
            'Vadaszapro' => 'Weingartner Auto',
            'Vadászati'  => 'Autós',
            'vadászati'  => 'autós',
            'Vadász'     => 'Autós',
            'vadász'     => 'autós',
            'Fegyver'    => 'Jármű',
            'fegyver'    => 'jármű',
        ];

        return strtr( $clean, $replacements );
    }

    public static function start_social_meta_buffer(): void {
        if ( is_admin() || is_feed() || is_trackback() ) return;
        if ( ! is_singular( 'va_listing' ) ) return;

        ob_start( [ __CLASS__, 'rewrite_social_meta_buffer' ] );
    }

    public static function rewrite_social_meta_buffer( string $html ): string {
        if ( $html === '' || ! is_singular( 'va_listing' ) ) {
            return $html;
        }

        $social_title = self::social_title( wp_get_document_title() );
        $social_desc  = self::social_description();

        $replacements = [
            '/(<meta[^>]+property=["\'])og:title(["\'][^>]+content=["\'])(.*?)(["\'][^>]*>)/i' => '$1og:title$2' . esc_attr( $social_title ) . '$4',
            '/(<meta[^>]+name=["\'])twitter:title(["\'][^>]+content=["\'])(.*?)(["\'][^>]*>)/i' => '$1twitter:title$2' . esc_attr( $social_title ) . '$4',
            '/(<meta[^>]+property=["\'])og:description(["\'][^>]+content=["\'])(.*?)(["\'][^>]*>)/i' => '$1og:description$2' . esc_attr( $social_desc ) . '$4',
            '/(<meta[^>]+name=["\'])twitter:description(["\'][^>]+content=["\'])(.*?)(["\'][^>]*>)/i' => '$1twitter:description$2' . esc_attr( $social_desc ) . '$4',
        ];

        foreach ( $replacements as $pattern => $replacement ) {
            $updated = preg_replace( $pattern, $replacement, $html, 1 );
            if ( is_string( $updated ) ) {
                $html = $updated;
            }
        }

        return $html;
    }

    public static function register_sitemap_routes(): void {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?va_sitemap=index', 'top' );
        add_rewrite_rule( '^sitemap-tax-([a-z0-9_-]+)-([0-9]+)\.xml$', 'index.php?va_sitemap=tax&va_sitemap_type=$matches[1]&va_sitemap_page=$matches[2]', 'top' );
        add_rewrite_rule( '^sitemap-landing-([0-9]+)\.xml$', 'index.php?va_sitemap=landing&va_sitemap_page=$matches[1]', 'top' );
        add_rewrite_rule( '^sitemap-([a-z0-9_-]+)-([0-9]+)\.xml$', 'index.php?va_sitemap=post&va_sitemap_type=$matches[1]&va_sitemap_page=$matches[2]', 'top' );
    }

    public static function register_query_vars( array $vars ): array {
        $vars[] = 'va_sitemap';
        $vars[] = 'va_sitemap_type';
        $vars[] = 'va_sitemap_page';
        return $vars;
    }

    private static function listing_search_page_url(): string {
        $page = get_page_by_path( 'va-hirdetes-kereses' );
        return $page ? (string) get_permalink( $page ) : home_url( '/va-hirdetes-kereses/' );
    }

    private static function requested_brand(): string {
        $brand = sanitize_text_field( wp_unslash( $_GET['brand'] ?? '' ) );
        return trim( $brand );
    }

    private static function requested_model(): string {
        $model = sanitize_text_field( wp_unslash( $_GET['model'] ?? '' ) );
        return trim( $model );
    }

    private static function is_listing_search_landing(): bool {
        return function_exists( 'va_is_page' )
            && va_is_page( 'va-hirdetes-kereses' )
            && ( self::requested_brand() !== '' || self::requested_model() !== '' );
    }

    private static function normalize_search_key( string $value ): string {
        $value = trim( $value );
        if ( $value === '' ) {
            return '';
        }
        return function_exists( 'mb_strtolower' )
            ? (string) mb_strtolower( $value, 'UTF-8' )
            : strtolower( $value );
    }

    private static function brand_seo_variants(): array {
        return [
            'bmw' => [
                'seo_heading' => 'Eladó BMW hirdetések és ár-összehasonlítás',
                'seo_text'    => 'A BMW landing oldalon egy helyen látod a legfrissebb ajánlatokat, így gyorsabban tudsz dönteni ár, évjárat, futásteljesítmény és felszereltség alapján.',
                'seo_points'  => [
                    'Friss BMW hirdetések országosan, szűrhető listában.',
                    'Gyors összevetés ár, évjárat és kilométer alapján.',
                    'Közvetlen továbblépés BMW modellekre egy kattintással.',
                ],
            ],
            'mercedes-benz' => [
                'seo_heading' => 'Eladó Mercedes-Benz modellek részletes adatokkal',
                'seo_text'    => 'A Mercedes-Benz ajánlatoknál kiemelten látható a teljesítmény, felszereltség és futásteljesítmény, így a prémium modellek összehasonlítása gyorsabb és tisztább.',
                'seo_points'  => [
                    'Aktuális Mercedes-Benz készlet folyamatosan frissítve.',
                    'Ár és évjárat alapú gyors szűrés a keresőben.',
                    'Kapcsolódó modellekre vezető belső linkek egy helyen.',
                ],
            ],
            'audi' => [
                'seo_heading' => 'Eladó Audi hirdetések ár és évjárat szerint',
                'seo_text'    => 'Az Audi landing oldal célja, hogy gyorsan lehessen szűkíteni a találatokat modell, állapot, ár és futásteljesítmény alapján, így kevesebb kattintásból megvan a releváns kör.',
                'seo_points'  => [
                    'Friss Audi ajánlatok egyetlen céloldalon.',
                    'Modellek közti gyors átjárás landing linkekkel.',
                    'Részletes járműadatok és összehasonlítható árak.',
                ],
            ],
            'volkswagen' => [
                'seo_heading' => 'Eladó Volkswagen hirdetések, gyors modellszűréssel',
                'seo_text'    => 'A Volkswagen kínálat modellszinten böngészhető, így egyszerűbb megtalálni a jó ár-érték arányú ajánlatokat akár városi, akár családi autót keresel.',
                'seo_points'  => [
                    'Volkswagen hirdetések modell és üzemanyag szerint szűrve.',
                    'Ár és futásteljesítmény szerinti gyors összehasonlítás.',
                    'Kapcsolódó Volkswagen modellek elérése egy helyről.',
                ],
            ],
            'toyota' => [
                'seo_heading' => 'Eladó Toyota hirdetések, kiemelten hibrid opciókkal',
                'seo_text'    => 'A Toyota landing oldalon a gazdaságos és hibrid modellek gyorsan szűrhetők, így könnyebb megtalálni a fenntarthatóbb és alacsony fenntartású ajánlatokat.',
                'seo_points'  => [
                    'Friss Toyota ajánlatok országos lefedettséggel.',
                    'Hibrid és benzines modellek gyors szűrése.',
                    'Ár, évjárat és futásteljesítmény alapú összevetés.',
                ],
            ],
        ];
    }

    private static function model_seo_variants(): array {
        return [
            'bmw|x5' => [
                'seo_heading' => 'Eladó BMW X5 hirdetések, prémium SUV kínálat',
                'seo_text'    => 'A BMW X5 oldalon egy helyen látod a prémium SUV ajánlatokat, így gyorsan összehasonlítható az ár, évjárat, futásteljesítmény és felszereltség.',
                'seo_points'  => [
                    'BMW X5 ajánlatok részletes műszaki adatokkal.',
                    'Gyors ár-összehasonlítás és állapot szerinti szűrés.',
                    'Kapcsolódó BMW modellek azonnali eléréssel.',
                ],
            ],
            'volkswagen|golf' => [
                'seo_heading' => 'Eladó Volkswagen Golf hirdetések, valós piaci árakkal',
                'seo_text'    => 'A Volkswagen Golf keresőoldal segít gyorsan átlátni a kínálatot, hogy melyik évjárat és felszereltség adja a legjobb ár-érték arányt.',
                'seo_points'  => [
                    'Volkswagen Golf hirdetések folyamatosan frissülve.',
                    'Évjárat és futásteljesítmény szerinti gyors összevetés.',
                    'Modellek közötti belső átjárás egy kattintással.',
                ],
            ],
            'audi|a4' => [
                'seo_heading' => 'Eladó Audi A4 hirdetések, összehasonlítható ajánlatokkal',
                'seo_text'    => 'Az Audi A4 oldalon tisztán összehasonlíthatóak a különböző évjáratok és motorváltozatok, így gyorsabban kiválasztható a megfelelő autó.',
                'seo_points'  => [
                    'Audi A4 ajánlatok részletes adatokkal és fotókkal.',
                    'Gyors szűrés ár, futásteljesítmény és üzemanyag szerint.',
                    'Kapcsolódó Audi modellek közvetlen linkekkel.',
                ],
            ],
            'toyota|corolla' => [
                'seo_heading' => 'Eladó Toyota Corolla hirdetések, megbízható modellek egy helyen',
                'seo_text'    => 'A Toyota Corolla ajánlatok külön céloldalon jelennek meg, így egyszerűbb kiválasztani a jó állapotú, jól árazott példányokat.',
                'seo_points'  => [
                    'Toyota Corolla hirdetések országos kínálatból.',
                    'Ár és évjárat szerinti áttekinthető szűrés.',
                    'Kapcsolódó Toyota modellek gyors elérése.',
                ],
            ],
        ];
    }

    public static function get_search_landing_context(): array {
        $brand = self::requested_brand();
        $model = self::requested_model();

        $context = [
            'brand'       => $brand,
            'model'       => $model,
            'title'       => 'Eladó autók és motorok',
            'intro'       => 'Böngészd a friss autó- és motorhirdetéseket részletes szűrőkkel, aktuális árakkal és járműadatokkal.',
            'description' => 'Eladó használt autók és motorok a Weingartner Autónál. Márka, modell, ár, évjárat és futásteljesítmény szerint szűrhető ajánlatok egy helyen.',
            'seo_heading' => 'Autó- és motorhirdetések országosan',
            'seo_text'    => 'A keresőoldal úgy lett felépítve, hogy márka, modell, ár, évjárat és állapot szerint is gyorsan lehessen szűrni. Így nem csak több hirdetést látsz, hanem könnyebben megtalálod a valóban releváns ajánlatokat.',
            'seo_points'  => [
                'Friss autó- és motorhirdetések egyetlen keresőoldalon.',
                'Részletes szűrés márka, modell, ár, kilométer és üzemanyag szerint.',
                'Gyors átjárás a kapcsolódó márka- és modelloldalak között.',
            ],
        ];

        if ( $brand !== '' && $model !== '' ) {
            $context['title'] = 'Eladó ' . $brand . ' ' . $model;
            $context['intro'] = 'Aktuális ' . $brand . ' ' . $model . ' ajánlatok részletes adatokkal, árakkal és szűrőzhető találatokkal.';
            $context['description'] = 'Eladó ' . $brand . ' ' . $model . ' ajánlatok a Weingartner Autónál. Ár, évjárat, kilométer és felszereltség szerint böngészhető készlet egy helyen.';
            $context['seo_heading'] = $brand . ' ' . $model . ' hirdetések, árak és járműadatok';
            $context['seo_text'] = 'Ez az oldal a ' . $brand . ' ' . $model . ' ajánlatokat gyűjti össze egy helyre, hogy gyorsan össze lehessen vetni az árakat, évjáratokat, futásteljesítményt és a fontosabb műszaki adatokat.';
            $context['seo_points'] = [
                'Összegyűjtött ' . $brand . ' ' . $model . ' hirdetések egy helyen.',
                'Gyors összehasonlítás ár, évjárat és kilométer alapján.',
                'Kapcsolódó keresések ugyanazon márkán belül további modellekre.',
            ];

            $model_variants = self::model_seo_variants();
            $model_key = self::normalize_search_key( $brand ) . '|' . self::normalize_search_key( $model );
            if ( isset( $model_variants[ $model_key ] ) ) {
                $context = array_merge( $context, $model_variants[ $model_key ] );
            }
        } elseif ( $brand !== '' ) {
            $context['title'] = 'Eladó ' . $brand;
            $context['intro'] = 'Aktuális ' . $brand . ' ajánlatok részletes adatokkal, árakkal és szűrőzhető találatokkal.';
            $context['description'] = 'Eladó ' . $brand . ' autók és motorok a Weingartner Autónál. Friss készlet, részletes adatok és aktuális ajánlatok egy helyen.';
            $context['seo_heading'] = $brand . ' hirdetések és kapcsolódó modellek';
            $context['seo_text'] = 'A ' . $brand . ' landing oldal célja, hogy egy helyre gyűjtse a márkához tartozó friss hirdetéseket és modelleket. Így könnyebb továbbmenni a legkeresettebb típusokra és gyorsabban szűkíteni a találatokat.';
            $context['seo_points'] = [
                'Eladó ' . $brand . ' ajánlatok folyamatosan frissülő listában.',
                'Gyors továbblépés a leggyakoribb modellekre.',
                'Szűrhető találatok ár, évjárat és futásteljesítmény szerint.',
            ];

            $brand_variants = self::brand_seo_variants();
            $brand_key = self::normalize_search_key( $brand );
            if ( isset( $brand_variants[ $brand_key ] ) ) {
                $context = array_merge( $context, $brand_variants[ $brand_key ] );
            }
        }

        return $context;
    }

    private static function landing_url( string $brand = '', string $model = '' ): string {
        $args = [];
        if ( $brand !== '' ) {
            $args['brand'] = $brand;
        }
        if ( $model !== '' ) {
            $args['model'] = $model;
        }
        return $args ? add_query_arg( $args, self::listing_search_page_url() ) : self::listing_search_page_url();
    }

    private static function landing_rows(): array {
        global $wpdb;

        $posts = $wpdb->posts;
        $pm_brand = $wpdb->postmeta;
        $pm_model = $wpdb->postmeta;

        $rows = [];

        $brand_rows = $wpdb->get_results(
            "SELECT pb.meta_value AS brand, COUNT(DISTINCT p.ID) AS cnt
             FROM {$posts} p
             INNER JOIN {$pm_brand} pb ON pb.post_id = p.ID AND pb.meta_key = 'va_brand'
             WHERE p.post_type = 'va_listing' AND p.post_status = 'publish' AND pb.meta_value <> ''
             GROUP BY pb.meta_value
             HAVING cnt > 0
             ORDER BY cnt DESC, pb.meta_value ASC",
            ARRAY_A
        );

        if ( is_array( $brand_rows ) ) {
            foreach ( $brand_rows as $row ) {
                $brand = trim( (string) ( $row['brand'] ?? '' ) );
                if ( $brand === '' ) {
                    continue;
                }
                $rows[] = [
                    'url' => self::landing_url( $brand ),
                    'lastmod' => gmdate( 'c' ),
                ];
            }
        }

        $model_rows = $wpdb->get_results(
            "SELECT pb.meta_value AS brand, pm.meta_value AS model, COUNT(DISTINCT p.ID) AS cnt
             FROM {$posts} p
             INNER JOIN {$pm_brand} pb ON pb.post_id = p.ID AND pb.meta_key = 'va_brand'
             INNER JOIN {$pm_model} pm ON pm.post_id = p.ID AND pm.meta_key = 'va_model'
             WHERE p.post_type = 'va_listing' AND p.post_status = 'publish' AND pb.meta_value <> '' AND pm.meta_value <> ''
             GROUP BY pb.meta_value, pm.meta_value
             HAVING cnt > 0
             ORDER BY cnt DESC, pb.meta_value ASC, pm.meta_value ASC",
            ARRAY_A
        );

        if ( is_array( $model_rows ) ) {
            foreach ( $model_rows as $row ) {
                $brand = trim( (string) ( $row['brand'] ?? '' ) );
                $model = trim( (string) ( $row['model'] ?? '' ) );
                if ( $brand === '' || $model === '' ) {
                    continue;
                }
                $rows[] = [
                    'url' => self::landing_url( $brand, $model ),
                    'lastmod' => gmdate( 'c' ),
                ];
            }
        }

        return $rows;
    }

    private static function landing_page_count(): int {
        $rows = self::landing_rows();
        if ( empty( $rows ) ) {
            return 0;
        }
        return (int) ceil( count( $rows ) / self::SITEMAP_PER_PAGE );
    }

    private static function sitemap_index_url_for_landing( int $page ): string {
        return home_url( '/sitemap-landing-' . $page . '.xml' );
    }

    public static function maybe_render_sitemap(): void {
        $mode = (string) get_query_var( 'va_sitemap' );
        if ( $mode === '' ) return;

        if ( $mode === 'index' ) {
            self::render_sitemap_index();
        }

        $type = sanitize_key( (string) get_query_var( 'va_sitemap_type' ) );
        $page = max( 1, (int) get_query_var( 'va_sitemap_page' ) );

        if ( $mode === 'post' && $type !== '' ) {
            self::render_posttype_sitemap( $type, $page );
        }

        if ( $mode === 'tax' && $type !== '' ) {
            self::render_taxonomy_sitemap( $type, $page );
        }

        if ( $mode === 'landing' ) {
            self::render_landing_sitemap( $page );
        }

        status_header( 404 );
        nocache_headers();
        exit;
    }

    private static function xml_header(): void {
        status_header( 200 );
        header( 'Content-Type: application/xml; charset=UTF-8' );
        nocache_headers();
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    }

    private static function public_post_types(): array {
        $types = get_post_types( [ 'public' => true ], 'names' );
        $types = array_values( array_filter( $types, static function( $pt ) {
            return $pt !== 'attachment';
        } ) );

        $priority = [ 'va_listing', 'va_auction', 'page', 'post' ];
        usort( $types, static function( $a, $b ) use ( $priority ) {
            $pa = array_search( $a, $priority, true );
            $pb = array_search( $b, $priority, true );
            $pa = $pa === false ? 99 : $pa;
            $pb = $pb === false ? 99 : $pb;
            return $pa <=> $pb;
        } );

        return $types;
    }

    private static function public_taxonomies(): array {
        return array_values( get_taxonomies( [ 'public' => true ], 'names' ) );
    }

    private static function post_type_page_count( string $post_type ): int {
        $counts = wp_count_posts( $post_type );
        $published = isset( $counts->publish ) ? (int) $counts->publish : 0;
        if ( $published <= 0 ) return 0;
        return (int) ceil( $published / self::SITEMAP_PER_PAGE );
    }

    private static function taxonomy_page_count( string $taxonomy ): int {
        $count = (int) wp_count_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
        ] );
        if ( $count <= 0 ) return 0;
        return (int) ceil( $count / self::SITEMAP_PER_PAGE );
    }

    private static function sitemap_index_url_for_post_type( string $post_type, int $page ): string {
        return home_url( '/sitemap-' . rawurlencode( $post_type ) . '-' . $page . '.xml' );
    }

    private static function sitemap_index_url_for_taxonomy( string $taxonomy, int $page ): string {
        return home_url( '/sitemap-tax-' . rawurlencode( $taxonomy ) . '-' . $page . '.xml' );
    }

    private static function lastmod_for_post_type( string $post_type ): string {
        global $wpdb;
        $val = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_modified_gmt FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status = 'publish'
             ORDER BY post_modified_gmt DESC LIMIT 1",
            $post_type
        ) );

        if ( ! $val ) return gmdate( 'c' );
        return gmdate( 'c', strtotime( (string) $val . ' UTC' ) );
    }

    public static function render_sitemap_index(): void {
        self::xml_header();
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ( self::public_post_types() as $pt ) {
            $pages = self::post_type_page_count( $pt );
            if ( $pages < 1 ) continue;
            $lastmod = self::lastmod_for_post_type( $pt );
            for ( $i = 1; $i <= $pages; $i++ ) {
                echo '<sitemap>';
                echo '<loc>' . esc_url( self::sitemap_index_url_for_post_type( $pt, $i ) ) . '</loc>';
                echo '<lastmod>' . esc_html( $lastmod ) . '</lastmod>';
                echo '</sitemap>';
            }
        }

        foreach ( self::public_taxonomies() as $tax ) {
            $pages = self::taxonomy_page_count( $tax );
            if ( $pages < 1 ) continue;
            $lastmod = gmdate( 'c' );
            for ( $i = 1; $i <= $pages; $i++ ) {
                echo '<sitemap>';
                echo '<loc>' . esc_url( self::sitemap_index_url_for_taxonomy( $tax, $i ) ) . '</loc>';
                echo '<lastmod>' . esc_html( $lastmod ) . '</lastmod>';
                echo '</sitemap>';
            }
        }

        $landing_pages = self::landing_page_count();
        if ( $landing_pages > 0 ) {
            $lastmod = gmdate( 'c' );
            for ( $i = 1; $i <= $landing_pages; $i++ ) {
                echo '<sitemap>';
                echo '<loc>' . esc_url( self::sitemap_index_url_for_landing( $i ) ) . '</loc>';
                echo '<lastmod>' . esc_html( $lastmod ) . '</lastmod>';
                echo '</sitemap>';
            }
        }

        echo '</sitemapindex>';
        exit;
    }

    public static function render_posttype_sitemap( string $post_type, int $page ): void {
        if ( ! post_type_exists( $post_type ) ) {
            status_header( 404 );
            exit;
        }

        $q = new WP_Query( [
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => self::SITEMAP_PER_PAGE,
            'paged'                  => $page,
            'orderby'                => 'modified',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ] );

        self::xml_header();
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ( $q->posts as $post ) {
            $loc = get_permalink( $post );
            if ( ! $loc ) continue;
            $mod_gmt = get_post_modified_time( 'U', true, $post );
            echo '<url>';
            echo '<loc>' . esc_url( $loc ) . '</loc>';
            if ( $mod_gmt ) {
                echo '<lastmod>' . esc_html( gmdate( 'c', (int) $mod_gmt ) ) . '</lastmod>';
            }
            echo '</url>';
        }
        echo '</urlset>';
        wp_reset_postdata();
        exit;
    }

    public static function render_taxonomy_sitemap( string $taxonomy, int $page ): void {
        if ( ! taxonomy_exists( $taxonomy ) ) {
            status_header( 404 );
            exit;
        }

        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
            'number'     => self::SITEMAP_PER_PAGE,
            'offset'     => ( $page - 1 ) * self::SITEMAP_PER_PAGE,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ] );

        self::xml_header();
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $url = get_term_link( $term );
                if ( is_wp_error( $url ) ) continue;
                echo '<url>';
                echo '<loc>' . esc_url( $url ) . '</loc>';
                echo '<lastmod>' . esc_html( gmdate( 'c' ) ) . '</lastmod>';
                echo '</url>';
            }
        }
        echo '</urlset>';
        exit;
    }

    public static function render_landing_sitemap( int $page ): void {
        $rows = self::landing_rows();
        $chunks = array_chunk( $rows, self::SITEMAP_PER_PAGE );
        $selected = $chunks[ $page - 1 ] ?? [];

        self::xml_header();
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ( $selected as $row ) {
            echo '<url>';
            echo '<loc>' . esc_url( (string) $row['url'] ) . '</loc>';
            echo '<lastmod>' . esc_html( (string) $row['lastmod'] ) . '</lastmod>';
            echo '</url>';
        }
        echo '</urlset>';
        exit;
    }

    public static function filter_wp_robots( array $robots ): array {
        if ( is_search() || is_404() ) {
            $robots['noindex'] = true;
            $robots['nofollow'] = true;
        }
        return $robots;
    }

    public static function output_robots_txt(): void {
        // Átvesszük az összes do_robots hook-ot (WP core + Rank Math),
        // így csak a mi sitemap.xml-ünk szerepel.
        remove_all_actions( 'do_robots' );

        header( 'Content-Type: text/plain; charset=utf-8' );
        $sitemap = esc_url( home_url( '/sitemap.xml' ) );
        echo "User-agent: *\n";
        echo "Disallow: /wp-admin/\n";
        echo "Allow: /wp-admin/admin-ajax.php\n";
        echo "\n";
        echo "Sitemap: {$sitemap}\n";
        exit;
    }

    private static function should_render_meta(): bool {
        if ( is_admin() ) return false;
        if ( is_feed() ) return false;
        if ( is_trackback() ) return false;
        return true;
    }

    private static function current_canonical(): string {
        if ( self::is_listing_search_landing() ) {
            return self::landing_url( self::requested_brand(), self::requested_model() );
        }

        if ( is_singular() ) {
            $url = get_permalink();
            return is_string( $url ) ? $url : home_url( '/' );
        }

        if ( is_home() ) {
            $page_for_posts = (int) get_option( 'page_for_posts' );
            if ( $page_for_posts > 0 ) {
                $url = get_permalink( $page_for_posts );
                if ( is_string( $url ) && $url !== '' ) return $url;
            }
        }

        if ( is_post_type_archive() || is_tax() || is_category() || is_tag() || is_author() || is_date() ) {
            $url = get_pagenum_link( 1 );
            return is_string( $url ) ? $url : home_url( '/' );
        }

        return home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) );
    }

    private static function fallback_image_url(): string {
        $logo_id = (int) get_theme_mod( 'custom_logo' );
        if ( $logo_id > 0 ) {
            $logo = wp_get_attachment_image_url( $logo_id, 'full' );
            if ( $logo ) return $logo;
        }

        if ( has_site_icon() ) {
            $icon = get_site_icon_url( 512 );
            if ( $icon ) return $icon;
        }

        return '';
    }

    private static function meta_image_url(): string {
        if ( is_singular() && has_post_thumbnail() ) {
            $img = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
            if ( $img ) return $img;
        }
        return self::fallback_image_url();
    }

    private static function meta_description(): string {
        if ( self::is_listing_search_landing() ) {
            $landing = self::get_search_landing_context();
            return $landing['description'];
        }

        if ( is_front_page() ) {
            return 'Eladó használt autók és motorok - Weingartner Autó. Friss hirdetések, márka és modell alapú keresés, részletes járműadatok és valós piaci árak egy helyen.';
        }

        if ( is_singular( 'va_listing' ) ) {
            $id = get_queried_object_id();
            return self::listing_meta_description( $id );
        }

        if ( is_singular( 'va_auction' ) ) {
            $id = get_queried_object_id();
            $title = get_the_title( $id );
            $end = (int) get_post_meta( $id, 'va_auction_end', true );
            $end_txt = $end > 0 ? date_i18n( 'Y.m.d H:i', $end ) : 'hamarosan';
            return wp_strip_all_tags( $title . ' - Online aukció. Lejárat: ' . $end_txt . '.' );
        }

        if ( is_singular() ) {
            if ( has_excerpt() ) {
                $excerpt = get_the_excerpt();
            } else {
                $raw = (string) get_post_field( 'post_content', get_queried_object_id() );
                $raw = strip_shortcodes( $raw );
                $excerpt = wp_trim_words( wp_strip_all_tags( $raw ), 28, '...' );
            }
            return wp_strip_all_tags( $excerpt );
        }

        if ( is_post_type_archive( 'va_listing' ) ) {
            $count = wp_count_posts( 'va_listing' );
            $published = isset( $count->publish ) ? (int) $count->publish : 0;
            return 'Eladó autók és motorok a Weingartner Autónál. ' . number_format( $published, 0, ',', ' ' ) . ' hirdetés részletes adatokkal és friss kínálattal.';
        }

        if ( is_post_type_archive( 'va_auction' ) ) {
            return 'Aktuális jármű aukciók valós licittel, folyamatosan frissülő kínálattal és részletes adatokkal.';
        }

        if ( is_tax() || is_category() || is_tag() ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term ) {
                $d = term_description( $term );
                if ( is_string( $d ) && trim( wp_strip_all_tags( $d ) ) !== '' ) {
                    return wp_trim_words( wp_strip_all_tags( $d ), 28, '...' );
                }
                return 'Eladó ' . $term->name . ' ajánlatok a Weingartner Autónál, részletes adatokkal és aktuális készlettel.';
            }
        }

        return 'Weingartner Autó - autó és motor hirdetések egy helyen.';
    }

    private static function listing_social_title( int $post_id ): string {
        $title = self::listing_base_title( $post_id );
        $price_raw = get_post_meta( $post_id, 'va_price', true );
        $price_type = (string) get_post_meta( $post_id, 'va_price_type', true );
        $sale_raw = get_post_meta( $post_id, 'va_sale_price', true );
        $sale_end_raw = (string) get_post_meta( $post_id, 'va_sale_price_end', true );

        $base_price_txt = '';
        if ( is_numeric( $price_raw ) && $price_type !== 'ask' ) {
            $base_price_txt = number_format( (float) $price_raw, 0, ',', ' ' ) . ' Ft';
        }

        $sale_active = false;
        if ( is_numeric( $sale_raw ) && (float) $sale_raw > 0 ) {
            $sale_active = true;
            if ( $sale_end_raw !== '' ) {
                $sale_end_ts = strtotime( $sale_end_raw . ' 23:59:59' );
                if ( $sale_end_ts && $sale_end_ts < current_time( 'timestamp' ) ) {
                    $sale_active = false;
                }
            }
        }

        if ( $sale_active ) {
            $sale_price_txt = number_format( (float) $sale_raw, 0, ',', ' ' ) . ' Ft';
            if ( $base_price_txt !== '' ) {
                return $title . ' | Eladó | Akciós ár: ' . $sale_price_txt . ' (eredeti: ' . $base_price_txt . ')';
            }
            return $title . ' | Eladó | Akciós ár: ' . $sale_price_txt;
        }

        if ( $base_price_txt !== '' ) {
            return $title . ' | Eladó | Ár: ' . $base_price_txt;
        }

        return $title . ' | Eladó';
    }

    private static function listing_social_description( int $post_id ): string {
        return self::listing_meta_description( $post_id );
    }

    private static function listing_browser_title( int $post_id ): string {
        $exact_title = wp_strip_all_tags( (string) get_the_title( $post_id ) );
        if ( $exact_title !== '' ) {
            return $exact_title . ' | Weingartner Autó';
        }
        return self::listing_base_title( $post_id ) . ' | Weingartner Autó';
    }

    private static function listing_base_title( int $post_id ): string {
        $title = wp_strip_all_tags( (string) get_the_title( $post_id ) );
        $brand = trim( (string) get_post_meta( $post_id, 'va_brand', true ) );
        $model = trim( (string) get_post_meta( $post_id, 'va_model', true ) );
        $year  = trim( (string) get_post_meta( $post_id, 'va_year', true ) );
        $body  = trim( (string) get_post_meta( $post_id, 'va_body_type', true ) );

        $parts = [];
        if ( $brand !== '' ) $parts[] = strtoupper( $brand );
        if ( $model !== '' ) $parts[] = $model;
        if ( $body !== '' ) $parts[] = $body;

        $base = trim( implode( ' ', $parts ) );
        if ( $base === '' ) {
            $base = $title;
        }

        if ( $year !== '' ) {
            $base .= ' (' . $year . ')';
        }

        return trim( preg_replace( '/\s+/', ' ', $base ) ?: $base );
    }

    private static function listing_meta_description( int $post_id ): string {
        $price_raw     = get_post_meta( $post_id, 'va_price', true );
        $price_type    = (string) get_post_meta( $post_id, 'va_price_type', true );
        $exact_title   = wp_strip_all_tags( (string) get_the_title( $post_id ) );
        $brand         = trim( (string) get_post_meta( $post_id, 'va_brand', true ) );
        $model         = trim( (string) get_post_meta( $post_id, 'va_model', true ) );
        $year          = trim( (string) get_post_meta( $post_id, 'va_year', true ) );
        $mileage       = trim( (string) get_post_meta( $post_id, 'va_mileage', true ) );
        $fuel_type     = trim( (string) get_post_meta( $post_id, 'va_fuel_type', true ) );
        $transmission  = trim( (string) get_post_meta( $post_id, 'va_transmission', true ) );
        $location      = trim( (string) get_post_meta( $post_id, 'va_location', true ) );
        $county        = wp_get_post_terms( $post_id, 'va_county', [ 'fields' => 'names' ] );
        $county_txt    = ! empty( $county[0] ) ? (string) $county[0] : '';
        $summary       = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 18, '...' );

        $name = trim( implode( ' ', array_filter( [ $brand, $model ] ) ) );
        if ( $name === '' ) {
            $name = wp_strip_all_tags( (string) get_the_title( $post_id ) );
        }

        $parts = [];
        if ( $exact_title !== '' ) {
            $parts[] = $exact_title . ' - eladó jármű.';
        } else {
            $parts[] = 'Eladó ' . $name . '.';
        }

        if ( $year !== '' ) {
            $parts[] = 'Évjárat: ' . $year . '.';
        }
        if ( is_numeric( $price_raw ) && $price_type !== 'ask' ) {
            $parts[] = 'Ár: ' . number_format( (float) $price_raw, 0, ',', ' ' ) . ' Ft.';
        }
        if ( $mileage !== '' && is_numeric( $mileage ) ) {
            $parts[] = 'Kilométer: ' . number_format( (float) $mileage, 0, ',', ' ' ) . ' km.';
        }
        if ( $fuel_type !== '' ) {
            $parts[] = 'Üzemanyag: ' . $fuel_type . '.';
        }
        if ( $transmission !== '' ) {
            $parts[] = 'Váltó: ' . $transmission . '.';
        }
        if ( $location !== '' || $county_txt !== '' ) {
            $parts[] = 'Elérhető: ' . trim( implode( ', ', array_filter( [ $location, $county_txt ] ) ) ) . '.';
        }
        if ( $summary !== '' ) {
            $parts[] = $summary;
        }

        return self::sanitize_seo_copy( trim( implode( ' ', $parts ) ) );
    }

    private static function social_title( string $default_title ): string {
        if ( self::is_listing_search_landing() ) {
            $landing = self::get_search_landing_context();
            return $landing['title'] . ' | Weingartner Autó';
        }

        if ( is_front_page() ) {
            return 'Eladó autók és motorok | Weingartner Autó';
        }

        if ( is_singular( 'va_listing' ) ) {
            $id = get_queried_object_id();
            if ( $id > 0 ) {
                return self::listing_social_title( $id );
            }
        }

        return self::sanitize_seo_copy( $default_title );
    }

    private static function social_description(): string {
        if ( self::is_listing_search_landing() || is_front_page() ) {
            return self::meta_description();
        }

        if ( is_singular( 'va_listing' ) ) {
            $id = get_queried_object_id();
            if ( $id > 0 ) {
                return self::listing_social_description( $id );
            }
        }

        return self::sanitize_seo_copy( self::meta_description() );
    }

    public static function render_head_meta(): void {
        if ( ! self::should_render_meta() ) return;

        $title = wp_get_document_title();
        $social_title = self::social_title( $title );
        $desc  = self::meta_description();
        $social_desc = self::social_description();
        $url   = self::current_canonical();
        $img   = self::meta_image_url();
        $site  = get_bloginfo( 'name' );
        $robots = ( is_search() || is_404() ) ? 'noindex, nofollow, max-image-preview:large' : 'index, follow, max-image-preview:large';

        echo "\n";
        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:locale" content="hu_HU">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr( is_singular() ? 'article' : 'website' ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $social_title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $social_desc ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
        if ( $img !== '' ) {
            echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
        }
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $social_title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $social_desc ) . '">' . "\n";
        if ( $img !== '' ) {
            echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
        }

        echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="hu-HU">' . "\n";
        echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="x-default">' . "\n";
    }

    private static function breadcrumb_graph(): array {
        $items = [];
        $pos = 1;
        $items[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => 'Főoldal',
            'item' => home_url( '/' ),
        ];

        if ( is_singular( 'va_listing' ) ) {
            $archive = get_post_type_archive_link( 'va_listing' );
            if ( $archive ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Hirdetések', 'item' => $archive ];
            }
            $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title(), 'item' => get_permalink() ];
        } elseif ( is_singular( 'va_auction' ) ) {
            $archive = get_post_type_archive_link( 'va_auction' );
            if ( $archive ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Aukciók', 'item' => $archive ];
            }
            $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title(), 'item' => get_permalink() ];
        } elseif ( is_tax() || is_category() || is_tag() ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term ) {
                $term_link = get_term_link( $term );
                if ( ! is_wp_error( $term_link ) ) {
                    $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => $term->name, 'item' => $term_link ];
                }
            }
        } elseif ( is_singular() ) {
            $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title(), 'item' => get_permalink() ];
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    private static function itemlist_graph_for_frontpage(): ?array {
        if ( ! is_front_page() ) return null;

        $q = new WP_Query( [
            'post_type'              => 'va_listing',
            'post_status'            => 'publish',
            'posts_per_page'         => 3,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ] );

        if ( empty( $q->posts ) ) return null;

        $elements = [];
        $i = 1;
        foreach ( $q->posts as $p ) {
            $thumb = get_the_post_thumbnail_url( $p->ID, 'medium' );
            $price = get_post_meta( $p->ID, 'va_price', true );
            $elem = [
                '@type'    => 'ListItem',
                'position' => $i++,
                'url'      => get_permalink( $p ),
                'name'     => get_the_title( $p ),
            ];
            if ( $thumb ) {
                $elem['image'] = $thumb;
            }
            if ( is_numeric( $price ) && $price > 0 ) {
                $elem['offers'] = [
                    '@type'         => 'Offer',
                    'priceCurrency' => 'HUF',
                    'price'         => (float) $price,
                    'availability'  => 'https://schema.org/InStock',
                ];
            }
            $elements[] = $elem;
        }
        wp_reset_postdata();

        return [
            '@type'           => 'ItemList',
            'name'            => 'Legfrissebb autó hirdetések',
            'description'     => 'Weingartner Autó legújabb eladó autói',
            'url'             => home_url( '/' ),
            'itemListElement' => $elements,
        ];
    }

    private static function itemlist_graph_for_archive(): ?array {
        if ( ! is_post_type_archive( 'va_listing' ) && ! is_tax( 'va_category' ) ) return null;

        $q = new WP_Query( [
            'post_type'              => 'va_listing',
            'post_status'            => 'publish',
            'posts_per_page'         => 10,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ] );

        if ( empty( $q->posts ) ) return null;

        $elements = [];
        $i = 1;
        foreach ( $q->posts as $p ) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $i++,
                'url' => get_permalink( $p ),
                'name' => get_the_title( $p ),
            ];
        }
        wp_reset_postdata();

        return [
            '@type' => 'ItemList',
            'name' => 'Friss hirdetések',
            'itemListElement' => $elements,
        ];
    }

    private static function product_graph_for_listing(): ?array {
        if ( ! is_singular( 'va_listing' ) ) return null;

        $id = get_queried_object_id();
        $price = get_post_meta( $id, 'va_price', true );
        $price_type = (string) get_post_meta( $id, 'va_price_type', true );
        $image = get_the_post_thumbnail_url( $id, 'full' );
        $brand = trim( (string) get_post_meta( $id, 'va_brand', true ) );
        $model = trim( (string) get_post_meta( $id, 'va_model', true ) );
        $year = trim( (string) get_post_meta( $id, 'va_year', true ) );
        $mileage = trim( (string) get_post_meta( $id, 'va_mileage', true ) );
        $fuel_type = trim( (string) get_post_meta( $id, 'va_fuel_type', true ) );
        $transmission = trim( (string) get_post_meta( $id, 'va_transmission', true ) );
        $body_type = trim( (string) get_post_meta( $id, 'va_body_type', true ) );
        $color = trim( (string) get_post_meta( $id, 'va_color', true ) );

        $graph = [
            '@type'         => 'Car',
            'name'          => self::listing_base_title( $id ),
            'url'           => get_permalink( $id ),
            'description'   => self::listing_meta_description( $id ),
            'itemCondition' => 'https://schema.org/UsedCondition',
            'seller'        => [
                '@type' => 'AutoDealer',
                'name'  => get_bloginfo( 'name' ),
                'url'   => home_url( '/' ),
            ],
        ];

        if ( $image ) {
            $graph['image'] = [ $image ];
        }

        if ( $brand !== '' ) {
            $graph['brand'] = [ '@type' => 'Brand', 'name' => $brand ];
        }
        if ( $model !== '' ) {
            $graph['model'] = $model;
        }
        if ( $year !== '' ) {
            $graph['releaseDate'] = $year;
        }
        if ( $mileage !== '' && is_numeric( $mileage ) ) {
            $graph['mileageFromOdometer'] = [
                '@type'    => 'QuantitativeValue',
                'value'    => (float) $mileage,
                'unitCode' => 'KMT',
            ];
        }
        if ( $fuel_type !== '' ) {
            $graph['fuelType'] = $fuel_type;
        }
        if ( $transmission !== '' ) {
            $graph['vehicleTransmission'] = $transmission;
        }
        if ( $body_type !== '' ) {
            $graph['bodyType'] = $body_type;
        }
        if ( $color !== '' ) {
            $graph['color'] = $color;
        }

        $engine_size = trim( (string) get_post_meta( $id, 'va_engine_size', true ) );
        if ( $engine_size !== '' && is_numeric( $engine_size ) ) {
            $graph['vehicleEngine'] = [
                '@type'              => 'EngineSpecification',
                'engineDisplacement' => [
                    '@type'    => 'QuantitativeValue',
                    'value'    => (float) $engine_size,
                    'unitCode' => 'CMQ',
                ],
            ];
        }

        $doors = trim( (string) get_post_meta( $id, 'va_doors', true ) );
        if ( $doors !== '' && is_numeric( $doors ) ) {
            $graph['numberOfDoors'] = (int) $doors;
        }

        $drive = trim( (string) get_post_meta( $id, 'va_drive', true ) );
        if ( $drive !== '' ) {
            $graph['driveWheelConfiguration'] = $drive;
        }

        $vin = trim( (string) get_post_meta( $id, 'va_vin', true ) );
        if ( $vin !== '' ) {
            $graph['vehicleIdentificationNumber'] = $vin;
        }

        if ( is_numeric( $price ) && $price_type !== 'ask' ) {
            $graph['offers'] = [
                '@type'         => 'Offer',
                'priceCurrency' => 'HUF',
                'price'         => (float) $price,
                'availability'  => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/UsedCondition',
                'url'           => get_permalink( $id ),
                'seller'        => [
                    '@type' => 'AutoDealer',
                    'name'  => get_bloginfo( 'name' ),
                    'url'   => home_url( '/' ),
                ],
            ];
        }

        return $graph;
    }

    private static function auction_graph(): ?array {
        if ( ! is_singular( 'va_auction' ) ) return null;

        $id = get_queried_object_id();
        $start = (float) get_post_meta( $id, 'va_auction_start_price', true );
        $endts = (int) get_post_meta( $id, 'va_auction_end', true );
        $img = get_the_post_thumbnail_url( $id, 'full' );

        $g = [
            '@type' => 'Auction',
            'name' => get_the_title( $id ),
            'url' => get_permalink( $id ),
            'description' => wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ), 40, '...' ),
        ];

        if ( $img ) {
            $g['image'] = [ $img ];
        }

        if ( $start > 0 ) {
            $offer = [
                '@type' => 'Offer',
                'priceCurrency' => 'HUF',
                'price' => $start,
                'url' => get_permalink( $id ),
            ];
            if ( $endts > 0 ) {
                $offer['priceValidUntil'] = gmdate( 'c', $endts );
            }
            $g['offers'] = $offer;
        }

        return $g;
    }

    public static function render_schema(): void {
        if ( ! self::should_render_meta() ) return;

        $site_name = get_bloginfo( 'name' );
        $home = home_url( '/' );
        $logo = self::fallback_image_url();

        $graph = [];

        $website = [
            '@type' => 'WebSite',
            '@id' => $home . '#website',
            'url' => $home,
            'name' => $site_name,
            'inLanguage' => 'hu-HU',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
        $graph[] = $website;

        $org = [
            '@type' => 'Organization',
            '@id' => $home . '#organization',
            'name' => $site_name,
            'url' => $home,
        ];
        if ( $logo !== '' ) {
            $org['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
        }
        $graph[] = $org;

        $graph[] = self::breadcrumb_graph();

        $frontpage_list = self::itemlist_graph_for_frontpage();
        if ( $frontpage_list ) $graph[] = $frontpage_list;

        $itemlist = self::itemlist_graph_for_archive();
        if ( $itemlist ) $graph[] = $itemlist;

        $product = self::product_graph_for_listing();
        if ( $product ) $graph[] = $product;

        $auction = self::auction_graph();
        if ( $auction ) $graph[] = $auction;

        $data = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        echo "\n";
        echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }
}
