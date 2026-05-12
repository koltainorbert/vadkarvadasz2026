<?php
/**
 * Felhasználói Tervek (Plans) + Hirdetés Kiemelés (Boost) rendszer
 *
 * Tervek:
 *  basic    – Ingyenes, 1 aktív hirdetés egyszerre,          kiemelés 7 naponta
 *  silver   – Fizetős havi,  max 5 hirdetés/hónap,           kiemelés 5 naponta
 *  gold     – Fizetős éves, max 10 hirdetés/hónap,           kiemelés 3 naponta
 *  platinum – Egyedi feltételek, admin állítja a limiteket,  kiemelés 3 naponta (vagy custom)
 *
 * Storage:
 *  wp_usermeta  va_plan                       → plan slug
 *  wp_usermeta  va_plan_listing_limit         → platinum custom havi limit
 *  wp_usermeta  va_plan_boost_cooldown        → platinum custom cooldown (nap)
 *  wp_usermeta  va_plan_note                  → admin megjegyzés (platinum)
 *  wp_postmeta  va_boost_time                 → utolsó boost Unix timestamp
 *  wp_postmeta  va_boost_user_{ID}_last       → per-user per-post utolsó boost
 *  wp_postmeta  va_new_pill_time              → "Új" pill kezdő Unix timestamp
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_User_Roles {

    /** Runtime cache a plans config-hoz (wp_options overlay) */
    private static ?array $_cfg_cache = null;

    /* ── Plan definíciók (alap értékek / fallback) ───────────── */
    const PLANS = [
        'basic'    => [
            'label'         => 'Basic',
            'color'         => '#888888',
            'bg'            => 'rgba(136,136,136,.15)',
            'icon'          => '🥉',
            'monthly_limit' => 1,       // max aktív hirdetés (basis=active)
            'boost_cooldown'=> 7,       // napok
            'basis'         => 'active',// 'active' = összes aktív | 'monthly' = havi
            'description'   => 'Ingyenes alap csomag – 1 aktív hirdetés',
        ],
        'silver'   => [
            'label'         => 'Silver',
            'color'         => '#c0c0c0',
            'bg'            => 'rgba(192,192,192,.15)',
            'icon'          => '🥈',
            'monthly_limit' => 5,
            'boost_cooldown'=> 5,
            'basis'         => 'monthly',
            'description'   => 'Havi előfizetés – 5 hirdetés/hó',
        ],
        'gold'     => [
            'label'         => 'Gold',
            'color'         => '#ffd700',
            'bg'            => 'rgba(255,215,0,.15)',
            'icon'          => '🥇',
            'monthly_limit' => 10,
            'boost_cooldown'=> 3,
            'basis'         => 'monthly',
            'description'   => 'Éves előfizetés – 10 hirdetés/hó',
        ],
        'platinum' => [
            'label'         => 'Platinum',
            'color'         => '#e2c6ff',
            'bg'            => 'rgba(226,198,255,.15)',
            'icon'          => '💎',
            'monthly_limit' => 20,      // felülírja va_plan_listing_limit
            'boost_cooldown'=> 3,       // felülírja va_plan_boost_cooldown
            'basis'         => 'monthly',
            'description'   => 'Egyedi feltételek – admin határozza meg',
            'seller_label'  => '',      // egyedi feladó rang címke (pl. Kereskedő)
        ],
        'custom'   => [
            'label'         => 'Egyedi',
            'color'         => '#ff2b2b',
            'bg'            => 'rgba(255,43,43,.14)',
            'icon'          => '🏷️',
            'monthly_limit' => 20,      // felülírja va_plan_listing_limit
            'boost_cooldown'=> 3,       // felülírja va_plan_boost_cooldown
            'basis'         => 'monthly',
            'description'   => 'Ügyfélspecifikus kategória – minden paraméter egyedileg állítható',
            'seller_label'  => '',      // egyedi feladó rang címke (pl. Céges partner)
        ],
    ];

    /* ── Boot ───────────────────────────────────────────────── */
    public static function init(): void {
        // Admin AJAX: admin állítja a tervet
        add_action( 'wp_ajax_va_admin_set_user_plan',  [ __CLASS__, 'ajax_admin_set_plan'      ] );
        // Admin AJAX: plan beállítások mentése
        add_action( 'wp_ajax_va_admin_save_plan_cfg',  [ __CLASS__, 'ajax_save_plan_settings'  ] );

        // Frontend AJAX: felhasználó boostol egy hirdetést
        add_action( 'wp_ajax_va_boost_listing', [ __CLASS__, 'ajax_boost_listing' ] );
        // Frontend AJAX: felhasználó "Új" pillt kapcsol egy hirdetésen
        add_action( 'wp_ajax_va_toggle_new_pill', [ __CLASS__, 'ajax_toggle_new_pill' ] );

        // Új hirdetésnél automatikusan induljon az "Új" pill 7 napos ablaka
        add_action( 'save_post_va_listing', [ __CLASS__, 'ensure_new_pill_on_create' ], 10, 3 );

        // Boost sorrendezés a va_listing archívum/taxonómia oldalain
        add_filter( 'posts_clauses', [ __CLASS__, 'filter_posts_clauses' ], 10, 2 );

        // Automatikus limit-érvényesítés bejelentkezett usereknél (naponta egyszer/user)
        add_action( 'wp', [ __CLASS__, 'maybe_enforce_current_user_limits' ] );

        // Leállított (plan limit miatt private) hirdetések takarítása.
        add_action( 'va_cleanup_plan_suspended_listings', [ __CLASS__, 'cleanup_plan_suspended_listings' ] );
        if ( ! wp_next_scheduled( 'va_cleanup_plan_suspended_listings' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'va_cleanup_plan_suspended_listings' );
        }

        // Ha bármi (admin, webhook, WC) frissíti a va_plan metát → azonnal enforce
        // updated_user_meta (nem update_user_meta!) hogy a DB-mentés UTÁN fusson, friss értékkel.
        add_action( 'updated_user_meta', [ __CLASS__, 'on_plan_meta_updated' ], 10, 4 );
        add_action( 'added_user_meta',   [ __CLASS__, 'on_plan_meta_updated' ], 10, 4 );

        // Ha kredit jóváírás történik → felfüggesztett hirdetések visszaállítása
        // updated_user_meta: a hook DB-mentés UTÁN tüzel, get_user_meta() már az új értéket adja.
        add_action( 'updated_user_meta', [ __CLASS__, 'on_credits_meta_updated' ], 10, 4 );
    }

    /**
     * Ha a va_plan user meta változik, azonnal enforce-olja a limitet.
     * Ez elkapja az admin-mentést, webhookokat, WC-integrációt egyaránt.
     */
    public static function on_plan_meta_updated( int $meta_id, int $user_id, string $meta_key, $meta_value ): void {
        if ( $meta_key !== 'va_plan' ) return;
        // Cache flush hogy az új plan érvényes legyen
        self::flush_plan_cache();
        delete_transient( 'va_enforce_ok_' . $user_id );
        self::enforce_plan_limits( $user_id );
    }

    /**
     * Ha a va_listing_credits user meta nő, enforce-olja a limitet (felold visszafelfüggesztetteket).
     */
    public static function on_credits_meta_updated( int $meta_id, int $user_id, string $meta_key, $meta_value ): void {
        if ( $meta_key !== 'va_listing_credits' ) return;
        delete_transient( 'va_enforce_ok_' . $user_id );
        self::enforce_plan_limits( $user_id );
    }

    /**
     * Automatikus limit-érvényesítés bejelentkezett usereknél (naponta egyszer/user).
     */
    public static function maybe_enforce_current_user_limits(): void {
        if ( ! is_user_logged_in() || is_admin() ) return;
        $uid = get_current_user_id();
        $key = 'va_enforce_ok_' . $uid;
        if ( get_transient( $key ) ) return;
        self::enforce_plan_limits( $uid );
        set_transient( $key, 1, DAY_IN_SECONDS );
    }

    /* ══ Plan config – options overlay ════════════════════════ */

    /**
     * Teljes plans config DB-ből (wp_options), merged a PLANS const alapértékeivel.
     * @return array<string,array>
     */
    public static function get_all_plan_configs(): array {
        if ( self::$_cfg_cache !== null ) return self::$_cfg_cache;

        $saved = get_option( 'va_plans_config', [] );
        if ( ! is_array( $saved ) ) $saved = [];

        $merged = [];
        foreach ( self::PLANS as $slug => $defaults ) {
            $override        = isset( $saved[ $slug ] ) && is_array( $saved[ $slug ] ) ? $saved[ $slug ] : [];
            $merged[ $slug ] = array_merge( $defaults, $override );
            // Típus kényszer
            // Ha a mentett monthly_limit 0, de a default >0, a default marad (0 = "nem állítottam be")
            $saved_limit = isset( $override['monthly_limit'] ) ? (int) $override['monthly_limit'] : -1;
            $merged[ $slug ]['monthly_limit'] = ( $saved_limit > 0 ) ? $saved_limit : (int) $defaults['monthly_limit'];
            $merged[ $slug ]['boost_cooldown'] = (int)  $merged[ $slug ]['boost_cooldown'];
            $merged[ $slug ]['basis']          = in_array( $merged[ $slug ]['basis'], [ 'active', 'monthly' ], true )
                ? $merged[ $slug ]['basis'] : $defaults['basis'];
        }

        // Globális boost beállítások
        $global_defaults = [
            'boost_badge_window' => 7,
            'boost_badge_text'   => '⚡ Előre téve',
            'boost_enabled'      => true,
        ];
        $global_saved    = isset( $saved['_global'] ) && is_array( $saved['_global'] ) ? $saved['_global'] : [];
        $merged['_global'] = array_merge( $global_defaults, $global_saved );

        self::$_cfg_cache = $merged;
        return $merged;
    }

    /** Plan config invalidálása (mentés után hívandó) */
    public static function flush_plan_cache(): void {
        self::$_cfg_cache = null;
    }

    private static function is_admin_user( int $user_id ): bool {
        return user_can( $user_id, 'administrator' );
    }

    public static function get_plan_rank( string $plan ): int {
        $order = [
            'basic'    => 0,
            'silver'   => 1,
            'gold'     => 2,
            'platinum' => 3,
            'custom'   => 4,
        ];
        return $order[ sanitize_key( $plan ) ] ?? 0;
    }

    private static function parse_cooldown_from_feature_row( string $row ): int {
        if ( preg_match( '/kiemel(?:e|é)s[^0-9]{0,24}(\d+)\s*nap(?:onta)?/iu', $row, $m ) ) {
            return max( 1, (int) ( $m[1] ?? 0 ) );
        }
        return 0;
    }

    public static function get_card_cooldown_for_plan( string $plan ): int {
        $plan = sanitize_key( $plan );
        if ( $plan === '' ) {
            return 0;
        }

        $count = max( 1, min( 8, (int) get_option( 'va_pc_count', 4 ) ) );
        for ( $n = 1; $n <= $count; $n++ ) {
            if ( get_option( "va_pc_{$n}_enabled", '1' ) !== '1' ) {
                continue;
            }
            $card_plan = sanitize_key( (string) get_option( "va_pc_{$n}_plan_slug", '' ) );
            if ( $card_plan !== $plan ) {
                continue;
            }

            $days = absint( get_option( "va_pc_{$n}_boost_cooldown", 0 ) );
            if ( $days > 0 ) {
                return $days;
            }

            // Visszafelé kompatibilitás: régi feature-sorokból kinyerés.
            $features = get_option( "va_pc_{$n}_features", [] );
            if ( is_array( $features ) ) {
                foreach ( $features as $feature ) {
                    $row = trim( (string) $feature );
                    if ( $row === '' ) {
                        continue;
                    }
                    $legacy_days = self::parse_cooldown_from_feature_row( $row );
                    if ( $legacy_days > 0 ) {
                        return $legacy_days;
                    }
                }
            }
        }

        return 0;
    }

    public static function get_user_plan( int $user_id ): string {
        if ( self::is_admin_user( $user_id ) ) {
            return 'platinum';
        }

        $plan = (string) get_user_meta( $user_id, 'va_plan', true );
        $all  = self::get_all_plan_configs();
        $plan_slug = ( isset( $all[ $plan ] ) && $plan !== '_global' ) ? $plan : 'basic';

        // Nem basic plan esetén lejárat után visszaesik basic-re.
        if ( $plan_slug !== 'basic' ) {
            $expires_at = (int) get_user_meta( $user_id, 'va_plan_expires_at', true );

            // Legacy user migracio: ha nincs lejárat, kapjon alapértelmezett ciklust.
            if ( $expires_at <= 0 ) {
                $duration_days = 365; // 1 éves lejárat minden csomagra
                $expires_at = time() + ( $duration_days * DAY_IN_SECONDS );
                update_user_meta( $user_id, 'va_plan_expires_at', $expires_at );
            }

            if ( $expires_at > 0 && $expires_at < time() ) {
                return 'basic';
            }
        }

        // _global key nem plan slug
        return $plan_slug;
    }

    /**
     * Plan konfiguráció – DB override-dal, user-specifikus kiemelési érték támogatással.
     * @return array{label:string,color:string,bg:string,icon:string,monthly_limit:int,boost_cooldown:int,basis:string,description:string}
     */
    public static function get_plan_config( string $plan, int $user_id = 0 ): array {
        $all = self::get_all_plan_configs();
        $cfg = ( isset( $all[ $plan ] ) && $plan !== '_global' ) ? $all[ $plan ] : $all['basic'];

        // Alap mindig a kártya: ha van kártyához kötött kiemelési idő, ez a default.
        $card_cd = self::get_card_cooldown_for_plan( $plan );
        if ( $card_cd > 0 ) {
            $cfg['boost_cooldown'] = $card_cd;
        }

        if ( self::is_admin_user( $user_id ) ) {
            $cfg['monthly_limit'] = 0;
            $cfg['boost_cooldown'] = 0;
            return $cfg;
        }

        if ( $user_id > 0 ) {
            // A kiemelési újratöltés mindig a csomag (árkártya) cooldownja szerint működik.
            // Havi limit override továbbra is csak platinum/custom csomagnál értelmezett.
            if ( in_array( $plan, [ 'platinum', 'custom' ], true ) ) {
                $custom_limit = (int) get_user_meta( $user_id, 'va_plan_listing_limit', true );
                if ( $custom_limit > 0 ) {
                    $cfg['monthly_limit'] = $custom_limit;
                }
            }
        }
        return $cfg;
    }

    /* ══ Hirdetésszám-ellenőrzők ════════════════════════════════ */

    /** Aktuális hónapban feladott aktív/pending/draft hirdetések száma */
    public static function get_monthly_listing_count( int $user_id ): int {
        global $wpdb;
        $start = gmdate( 'Y-m-01 00:00:00' );
        $end   = gmdate( 'Y-m-t 23:59:59' );
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type   = %s
               AND post_author = %d
               AND post_status IN ('publish','pending','draft','future','private')
               AND post_date  >= %s
               AND post_date  <= %s",
            'va_listing', $user_id, $start, $end
        ) );
    }

    /** Összes aktív hirdetés (minden státuszban, minden hónapban) – basic limit */
    public static function get_active_listing_count( int $user_id ): int {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type   = %s
               AND post_author = %d
               AND post_status IN ('publish','pending','draft','future','private')",
            'va_listing', $user_id
        ) );
    }

    /**
     * Feladhat-e új hirdetést a felhasználó?
     * @return array{can:bool, reason:string, used:int, limit:int}
     */
    public static function can_post_listing( int $user_id ): array {
        $plan = self::get_user_plan( $user_id );
        $cfg  = self::get_plan_config( $plan, $user_id );

        // Korlátlan (0 vagy -1)
        if ( $cfg['monthly_limit'] <= 0 ) {
            return [ 'can' => true, 'reason' => '', 'used' => 0, 'limit' => 0, 'remaining' => -1 ];
        }

        // Plusz kreditek (egyszer használatos feladási kreditek)
        $purchased_credits = (int) get_user_meta( $user_id, 'va_listing_credits', true );
        $limit = $cfg['monthly_limit'] + $purchased_credits;

        $used  = ( $cfg['basis'] === 'active' )
            ? self::get_active_listing_count( $user_id )
            : self::get_monthly_listing_count( $user_id );
        $remaining = max( 0, $limit - $used );

        if ( $used < $limit ) {
            return [ 'can' => true, 'reason' => '', 'used' => $used, 'limit' => $limit, 'remaining' => $remaining ];
        }

        $label = $cfg['label'];
        if ( $cfg['basis'] === 'active' ) {
            $reason = "{$label} csomaggal egyszerre legfeljebb {$limit} aktív hirdetésed lehet. Töröl egy meglévőt, vagy frissítsd csomagodat!";
        } else {
            $reason = "{$label} csomaggal ebben a hónapban legfeljebb {$limit} hirdetést adhatsz fel. A hónap végén újra indul a keret.";
        }

        return [ 'can' => false, 'reason' => $reason, 'used' => $used, 'limit' => $limit, 'remaining' => 0 ];
    }

    /* ══ Boost logika ═══════════════════════════════════════════ */

    /** Hány másodperc van még a következő boostig? 0 = azonnal boostolhat. */
    public static function boost_seconds_remaining( int $user_id, int $post_id ): int {
        $plan    = self::get_user_plan( $user_id );
        $cfg     = self::get_plan_config( $plan, $user_id );
        $cd_secs = $cfg['boost_cooldown'] * DAY_IN_SECONDS;

        $last_boost = (int) get_post_meta( $post_id, 'va_boost_user_' . $user_id . '_last', true );
        if ( $last_boost === 0 ) return 0;

        $elapsed = time() - $last_boost;
        return ( $elapsed >= $cd_secs ) ? 0 : ( $cd_secs - $elapsed );
    }

    /** @return array{can:bool, seconds_remaining:int, cooldown_days:int} */
    public static function can_boost( int $user_id, int $post_id ): array {
        $plan = self::get_user_plan( $user_id );
        $cfg  = self::get_plan_config( $plan, $user_id );
        $rem  = self::boost_seconds_remaining( $user_id, $post_id );
        return [
            'can'               => $rem === 0,
            'seconds_remaining' => $rem,
            'cooldown_days'     => $cfg['boost_cooldown'],
        ];
    }

    /**
     * Boost elvégzése. True ha sikeres.
     */
    public static function do_boost( int $user_id, int $post_id ): bool {
        $check = self::can_boost( $user_id, $post_id );
        if ( ! $check['can'] ) return false;

        $now = time();
        update_post_meta( $post_id, 'va_boost_time', $now );
        update_post_meta( $post_id, 'va_boost_user_' . $user_id . '_last', $now );

        // Keresési cache törlése, hogy az új sorrend azonnal megjelenjen
        if ( class_exists( 'VA_Ajax' ) ) {
            VA_Ajax::flush_filter_cache();
        }

        return true;
    }

    /**
     * Boost (pill) levétele.
     */
    public static function do_unboost( int $post_id ): bool {
        delete_post_meta( $post_id, 'va_boost_time' );

        if ( class_exists( 'VA_Ajax' ) ) {
            VA_Ajax::flush_filter_cache();
        }

        return true;
    }

    /**
     * Új hirdetésnél automatikusan beállítja az "Új" pill induló idejét.
     */
    public static function ensure_new_pill_on_create( int $post_id, \WP_Post $post, bool $update ): void {
        if ( wp_is_post_revision( $post_id ) ) return;
        if ( $post->post_type !== 'va_listing' ) return;
        if ( $update ) return;

        $existing = (int) get_post_meta( $post_id, 'va_new_pill_time', true );
        if ( $existing <= 0 ) {
            update_post_meta( $post_id, 'va_new_pill_time', time() );
        }
    }

    /**
     * "Új" pill aktív-e (fixen 1 napos ablak).
     */
    public static function is_new_pill( int $post_id, int $window_days = 1 ): bool {
        $ts = (int) get_post_meta( $post_id, 'va_new_pill_time', true );
        if ( $ts <= 0 ) return false;
        return ( time() - $ts ) < ( max( 1, $window_days ) * DAY_IN_SECONDS );
    }

    /**
     * Hirdetés boostednak számít-e (kategória badge megjelenítéshez)?
     * Az ablak mérete a globális konfigból jön (alapba 14 nap).
     */
    public static function is_boosted( int $post_id, int $window_days = 0 ): bool {
        if ( $window_days <= 0 ) {
            $cfg = self::get_all_plan_configs();
            $window_days = (int) ( $cfg['_global']['boost_badge_window'] ?? 7 );
        }
        $bt = (int) get_post_meta( $post_id, 'va_boost_time', true );
        if ( $bt <= 0 ) return false;
        return ( time() - $bt ) < ( $window_days * DAY_IN_SECONDS );
    }

    /* ══ Query szűrő: boost sorrendezés ════════════════════════ */

    public static function filter_posts_clauses( array $clauses, \WP_Query $query ): array {
        global $wpdb;

        if ( is_admin() ) return $clauses;
        if ( ! $query->is_main_query() ) return $clauses;

        // Csak va_listing típusnál, VAGY va_category/va_county/va_condition taxonómia archívumnál, VAGY va_listing CPT archívumnál
        $pt     = $query->get( 'post_type' );
        $pt_arr = is_array( $pt ) ? $pt : ( $pt ? [ $pt ] : [] );
        $is_va_listing_query = in_array( 'va_listing', $pt_arr, true )
            || $query->is_tax( [ 'va_category', 'va_county', 'va_condition' ] )
            || $query->is_post_type_archive( 'va_listing' );

        if ( ! $is_va_listing_query ) {
            return $clauses;
        }

        // Ne írjuk felül ha valaki explicit nem-dátum szerinti sorrendezést kért
        $orderby = $query->get( 'orderby' );
        if ( $orderby && ! in_array( $orderby, [ '', 'date', 'post_date', 'none' ], true ) ) {
            return $clauses;
        }

        $alias = 'va_bst_pm';

        // Csak egyszer adjuk hozzá a JOIN-t
        if ( strpos( $clauses['join'], $alias ) !== false ) {
            return $clauses;
        }

        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS {$alias}
            ON ( {$alias}.post_id = {$wpdb->posts}.ID
                 AND {$alias}.meta_key = 'va_boost_time' )";

        // Ablakidő a DB-ből (boost_badge_window nap) – az ablakon kívüliek nem számítanak boostnak
        $global_cfg   = self::get_all_plan_configs()['_global'] ?? [];
        $window_days  = (int) ( $global_cfg['boost_badge_window'] ?? 7 );
        $boost_cutoff = time() - $window_days * DAY_IN_SECONDS;

        // Boostnál előre ugrik, de a később feladott hirdetések idővel megelőzhetik.
        $clauses['orderby'] = "CASE WHEN CAST( {$alias}.meta_value AS UNSIGNED ) > {$boost_cutoff} THEN GREATEST(CAST( {$alias}.meta_value AS UNSIGNED ), UNIX_TIMESTAMP({$wpdb->posts}.post_date)) ELSE UNIX_TIMESTAMP({$wpdb->posts}.post_date) END DESC";

        return $clauses;
    }

    /* ══ AJAX: Plan beállítások mentése (admin) ═══════════════ */

    public static function ajax_save_plan_settings(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }
        check_ajax_referer( 'va_admin_plan_cfg', 'nonce' );

        $raw   = isset( $_POST['plans'] ) ? wp_unslash( (string) $_POST['plans'] ) : '{}';
        $input = json_decode( $raw, true );
        if ( ! is_array( $input ) ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen adatformátum.' ] );
        }

        $current       = get_option( 'va_plans_config', [] );
        if ( ! is_array( $current ) ) $current = [];

        $allowed_slugs = array_keys( self::PLANS );

        foreach ( $allowed_slugs as $slug ) {
            if ( ! isset( $input[ $slug ] ) || ! is_array( $input[ $slug ] ) ) continue;
            $d = $input[ $slug ];

            $current[ $slug ] = [
                'label'          => sanitize_text_field( $d['label']         ?? '' ),
                'icon'           => sanitize_text_field( $d['icon']          ?? '' ),
                'color'          => sanitize_hex_color( $d['color']          ?? '' ) ?? self::PLANS[ $slug ]['color'],
                'bg'             => sanitize_text_field( $d['bg']            ?? '' ),
                'monthly_limit'  => max( 0, (int) ( $d['monthly_limit']     ?? 0 ) ),
                'boost_cooldown' => max( 1, (int) ( $d['boost_cooldown']    ?? 1 ) ),
                'basis'          => in_array( $d['basis'] ?? '', [ 'active', 'monthly' ], true ) ? $d['basis'] : self::PLANS[ $slug ]['basis'],
                'description'    => sanitize_textarea_field( $d['description']   ?? '' ),
                'price_monthly'  => sanitize_text_field( $d['price_monthly']     ?? '' ),
                'price_yearly'   => sanitize_text_field( $d['price_yearly']      ?? '' ),
                'badge_text'     => sanitize_text_field( $d['badge_text']        ?? '' ),
                'seller_label'   => in_array( $slug, [ 'platinum', 'custom' ], true ) ? sanitize_text_field( $d['seller_label'] ?? '' ) : '',
            ];
        }

        if ( isset( $input['_global'] ) && is_array( $input['_global'] ) ) {
            $g = $input['_global'];
            $current['_global'] = [
                'boost_badge_window' => max( 1, (int) ( $g['boost_badge_window'] ?? 7 ) ),
                'boost_badge_text'   => sanitize_text_field( $g['boost_badge_text']  ?? '⚡ Előre téve' ),
                'boost_enabled'      => ! empty( $g['boost_enabled'] ),
            ];
        }

        update_option( 'va_plans_config', $current );
        self::flush_plan_cache();

        wp_send_json_success( [ 'message' => 'Csomag beállítások sikeresen mentve!' ] );
    }

    /* ══ AJAX: Admin állítja a tervet ══════════════════════════ */

    /**
     * Csomaghoz illesztés: ha a felhasználónak több aktív hirdetése van mint a limit,
     * a legrégebbieket felfüggeszti (private), NEM törli.
     * A felfüggesztett hirdetéseken va_suspended_by_plan=1 meta van,
     * hogy visszaállítható legyen ha újra megfelelő csomagot vesz.
     *
     * @return int Felfüggesztett hirdetések száma
     */
    public static function enforce_plan_limits( int $user_id ): int {
        $plan = self::get_user_plan( $user_id );
        $cfg  = self::get_plan_config( $plan, $user_id );

        // Korlátlan plan → nincs mit felfüggeszteni
        if ( $cfg['monthly_limit'] <= 0 ) {
            return 0;
        }

        // Plan limit + megvásárolt kredit = összes engedélyezett aktív hirdetés
        $purchased_credits = (int) get_user_meta( $user_id, 'va_listing_credits', true );
        $limit = $cfg['monthly_limit'] + $purchased_credits;

        // MINDEN hirdetés (aktiv és felfüggesztett) – legrégebbtől legújabbig
        // Az ASC sorrend biztosítja hogy a legrégebbi ("igazi") hirdetések maradnak aktiv.
        global $wpdb;
        $posts = $wpdb->get_results( $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_status
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'va_suspended_by_plan'
             WHERE p.post_type = 'va_listing'
               AND p.post_author = %d
               AND (
                 p.post_status IN ('publish','pending')
                 OR (p.post_status = 'private' AND pm.meta_value = '1')
               )
             ORDER BY p.post_date ASC
             LIMIT 200",
            $user_id
        ) );

        $preferred_id = (int) get_user_meta( $user_id, 'va_primary_listing_id', true );
        if ( $preferred_id > 0 && ! empty( $posts ) ) {
            foreach ( $posts as $idx => $item ) {
                if ( (int) $item->ID === $preferred_id ) {
                    $preferred_item = $item;
                    unset( $posts[ $idx ] );
                    array_unshift( $posts, $preferred_item );
                    break;
                }
            }
        }

        $suspended = 0;
        foreach ( $posts as $i => $post ) {
            $is_plan_suspended = get_post_meta( $post->ID, 'va_suspended_by_plan', true ) === '1';
            if ( $i < $limit ) {
                // Belül a limitben – ha korábban felfüggesztettük, visszaállítjuk
                if ( $is_plan_suspended ) {
                    wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'publish' ] );
                    delete_post_meta( $post->ID, 'va_suspended_by_plan' );
                    delete_post_meta( $post->ID, 'va_suspended_by_plan_at' );
                }
            } else {
                // Limit felett → felfüggesztés
                if ( $post->post_status !== 'private' ) {
                    wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'private' ] );
                    update_post_meta( $post->ID, 'va_suspended_by_plan', '1' );
                    update_post_meta( $post->ID, 'va_suspended_by_plan_at', current_time( 'timestamp' ) );
                    $suspended++;
                } elseif ( $is_plan_suspended && (int) get_post_meta( $post->ID, 'va_suspended_by_plan_at', true ) <= 0 ) {
                    // Régi rekordoknál a visszaszámlálóhoz pótoljuk a hiányzó időbélyeget.
                    update_post_meta( $post->ID, 'va_suspended_by_plan_at', current_time( 'timestamp' ) );
                }
            }
        }

        return $suspended;
    }

    /**
     * Plan limit miatt leállított hirdetések takarítása.
     * Lépések: figyelmeztetés (30/7/1) → kuka → végleges törlés.
     */
    public static function cleanup_plan_suspended_listings(): void {
        $retention_days = max( 1, absint( get_option( 'va_plan_suspended_retention_days', 90 ) ) );
        $trash_grace_days = max( 1, absint( get_option( 'va_plan_suspended_trash_grace_days', 7 ) ) );
        $now_ts           = current_time( 'timestamp' );
        $warning_points   = [ 30, 7, 1 ];
        $mail_queue       = [];

        $private_q = new WP_Query([
            'post_type'           => 'va_listing',
            'post_status'         => 'private',
            'fields'              => 'ids',
            'posts_per_page'      => 100,
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'meta_query'          => [
                [
                    'key'     => 'va_suspended_by_plan',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
        ]);

        foreach ( $private_q->posts as $post_id ) {
            $post_id = (int) $post_id;
            if ( $post_id <= 0 ) {
                continue;
            }

            $author_id    = (int) get_post_field( 'post_author', $post_id );
            $suspended_at = (int) get_post_meta( $post_id, 'va_suspended_by_plan_at', true );
            if ( $suspended_at <= 0 ) {
                $suspended_at = $now_ts;
                update_post_meta( $post_id, 'va_suspended_by_plan_at', $suspended_at );
            }

            $delete_at = $suspended_at + ( $retention_days * DAY_IN_SECONDS );
            $seconds_left = max( 0, $delete_at - $now_ts );
            $days_left = (int) ceil( $seconds_left / DAY_IN_SECONDS );

            foreach ( $warning_points as $point ) {
                $warn_key = 'va_plan_suspend_warn_' . $point;
                if ( $days_left > $point || (int) get_post_meta( $post_id, $warn_key, true ) > 0 ) {
                    continue;
                }

                if ( ! isset( $mail_queue[ $author_id ][ $point ] ) ) {
                    $mail_queue[ $author_id ][ $point ] = [
                        'count'       => 0,
                        'earliest_ts' => $delete_at,
                        'post_ids'    => [],
                    ];
                }
                $mail_queue[ $author_id ][ $point ]['count']++;
                $mail_queue[ $author_id ][ $point ]['post_ids'][] = $post_id;
                $mail_queue[ $author_id ][ $point ]['earliest_ts'] = min( $mail_queue[ $author_id ][ $point ]['earliest_ts'], $delete_at );
            }

            if ( $now_ts >= $delete_at ) {
                wp_trash_post( $post_id );
                update_post_meta( $post_id, 'va_suspended_by_plan_trashed_at', $now_ts );
            }
        }

        foreach ( $mail_queue as $user_id => $items ) {
            foreach ( $items as $days => $payload ) {
                if ( ! self::send_plan_suspended_warning_email( (int) $user_id, (int) $days, (int) $payload['count'], (int) $payload['earliest_ts'] ) ) {
                    continue;
                }
                // E-mail küldési napló mentése
                $email_log = get_user_meta( (int) $user_id, 'va_email_send_log', true );
                if ( ! is_array( $email_log ) ) {
                    $email_log = [];
                }
                $email_log[] = [
                    'type'         => 'plan_warning',
                    'sent_at'      => $now_ts,
                    'days_warning' => (int) $days,
                    'post_count'   => (int) $payload['count'],
                    'post_ids'     => $payload['post_ids'],
                    'earliest_delete_ts' => (int) $payload['earliest_ts'],
                ];
                if ( count( $email_log ) > 100 ) {
                    $email_log = array_slice( $email_log, -100 );
                }
                update_user_meta( (int) $user_id, 'va_email_send_log', $email_log );

                foreach ( $payload['post_ids'] as $post_id ) {
                    update_post_meta( (int) $post_id, 'va_plan_suspend_warn_' . (int) $days, $now_ts );
                }
            }
        }

        $trash_cutoff = $now_ts - ( $trash_grace_days * DAY_IN_SECONDS );
        $trash_q = new WP_Query([
            'post_type'           => 'va_listing',
            'post_status'         => 'trash',
            'fields'              => 'ids',
            'posts_per_page'      => 100,
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'meta_query'          => [
                [
                    'key'     => 'va_suspended_by_plan',
                    'value'   => '1',
                    'compare' => '=',
                ],
                [
                    'key'     => 'va_suspended_by_plan_trashed_at',
                    'value'   => $trash_cutoff,
                    'type'    => 'NUMERIC',
                    'compare' => '<=',
                ],
            ],
        ]);

        foreach ( $trash_q->posts as $post_id ) {
            $post_id = (int) $post_id;
            if ( $post_id <= 0 ) {
                continue;
            }
            if ( class_exists( 'VA_User_System' ) && method_exists( 'VA_User_System', 'delete_listing_with_images' ) ) {
                VA_User_System::delete_listing_with_images( $post_id );
            } else {
                wp_delete_post( $post_id, true );
            }
        }
    }

    private static function send_plan_suspended_warning_email( int $user_id, int $days_left, int $count, int $earliest_delete_ts ): bool {
        $user = get_userdata( $user_id );
        if ( ! $user || empty( $user->user_email ) ) {
            return false;
        }

        $site_name = trim( (string) get_option( 'va_email_brand_name', 'Vadkár Vadász' ) );
        if ( $site_name === '' ) {
            $site_name = 'Vadkár Vadász';
        }
        $support_email = sanitize_email( (string) get_option( 'va_email_contact_email', 'vadkarvadasz@gmail.com' ) );
        if ( $support_email === '' ) {
            $support_email = 'vadkarvadasz@gmail.com';
        }
        $buy_page  = get_page_by_path( 'va-kredit-vasarlas' );
        $buy_url   = $buy_page ? get_permalink( $buy_page ) : home_url( '/va-kredit-vasarlas/' );

        $subject_default = '{count} hirdetésed {days} nap múlva törlődhet';
        $body_default    = "Kedves {name},\n\nTájékoztatunk, hogy az alábbi, csomaglimit miatt szüneteltetett hirdetéseid {delete_at}-ig törlésre kerülnek, amennyiben addig nem frissíted előfizetésedet.\n\nMeghosszabbításhoz: {buy_url}\n\nKapcsolat: {support_email}\n\nÜdvözlettel,\n{site_name}";

        $subject_by_days = [
            30 => trim( (string) get_option( 'va_email_warning_subject_30', '{count} hirdetésed 30 nap múlva törlődhet' ) ),
            7  => trim( (string) get_option( 'va_email_warning_subject_7', '7 nap múlva törlünk {count} hirdetést' ) ),
            1  => trim( (string) get_option( 'va_email_warning_subject_1', '⚠️ Holnap törlünk {count} hirdetést' ) ),
        ];
        $body_by_days = [
            30 => (string) get_option( 'va_email_warning_body_30', "Kedves {name},\n\nTájékoztatunk, hogy az alábbi, csomaglimit miatt szüneteltetett hirdetéseid {delete_at}-ig törlésre kerülnek, amennyiben addig nem frissíted előfizetésedet.\n\nMég bőven van idő, de érdemes időben gondoskodni a megújításról.\n\nMeghosszabbításhoz: {buy_url}\n\nKapcsolat: {support_email}\n\nÜdvözlettel,\n{site_name}" ),
            7  => (string) get_option( 'va_email_warning_body_7', "Kedves {name},\n\nEmlékeztetünk, hogy 7 napon belül (legkorábban {delete_at}) törlődnek az alábbi, csomaglimit miatt szüneteltetett hirdetéseid.\n\nA hirdetések visszaállításához frissítsd előfizetésedet.\n\nMeghosszabbításhoz: {buy_url}\n\nKapcsolat: {support_email}\n\nÜdvözlettel,\n{site_name}" ),
            1  => (string) get_option( 'va_email_warning_body_1', "Kedves {name},\n\nEz az utolsó figyelmeztetés. Holnap ({delete_at}) véglegesen törlődnek az alábbi, csomaglimit miatt inaktív hirdetéseid.\n\nA törlés csak csomagfrissítéssel előzhető meg.\n\nMeghosszabbításhoz: {buy_url}\n\nKapcsolat: {support_email}\n\nÜdvözlettel,\n{site_name}" ),
        ];

        $subject_tpl = $subject_by_days[ $days_left ] ?? '';
        $body_tpl    = $body_by_days[ $days_left ] ?? '';

        if ( $subject_tpl === '' ) {
            $subject_tpl = trim( (string) get_option( 'va_email_warning_subject', $subject_default ) );
        }
        if ( $subject_tpl === '' ) {
            $subject_tpl = $subject_default;
        }

        if ( trim( $body_tpl ) === '' ) {
            $body_tpl = (string) get_option( 'va_email_warning_body', $body_default );
        }
        if ( trim( $body_tpl ) === '' ) {
            $body_tpl = $body_default;
        }

        $replace_map = [
            '{name}'          => (string) $user->display_name,
            '{count}'         => (string) $count,
            '{days}'          => (string) $days_left,
            '{delete_at}'     => date_i18n( 'Y.m.d H:i', $earliest_delete_ts ),
            '{buy_url}'       => (string) $buy_url,
            '{support_email}' => (string) $support_email,
            '{site_name}'     => (string) $site_name,
        ];

        $subject = strtr( $subject_tpl, $replace_map );
        $message = strtr( $body_tpl, $replace_map );

        return wp_mail( $user->user_email, $subject, $message );
    }

    public static function ajax_admin_set_plan(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        check_ajax_referer( 'va_admin_user_plan', 'nonce' );

        $target_uid        = absint( $_POST['user_id'] ?? 0 );
        $plan              = sanitize_key( $_POST['plan'] ?? 'basic' );
        $custom_lim           = absint( $_POST['custom_limit'] ?? 0 );
        $custom_cd            = absint( $_POST['custom_boost_cooldown'] ?? 0 );
        $custom_credits_add   = absint( $_POST['custom_credits_add'] ?? 0 );
        $custom_credits_revoke= absint( $_POST['custom_credits_revoke'] ?? 0 );
        // Visszafele kompatibilitas a regi +/- mezovel.
        if ( $custom_credits_add === 0 && $custom_credits_revoke === 0 && isset( $_POST['custom_credits'] ) ) {
            $legacy_delta = (int) wp_unslash( (string) $_POST['custom_credits'] );
            if ( $legacy_delta >= 0 ) {
                $custom_credits_add = $legacy_delta;
            } else {
                $custom_credits_revoke = abs( $legacy_delta );
            }
        }
        $custom_credits_delta = $custom_credits_add - $custom_credits_revoke;
        $custom_expires_at = sanitize_text_field( wp_unslash( (string) ( $_POST['custom_expires_at'] ?? '' ) ) );
        $plan_note         = sanitize_textarea_field( wp_unslash( (string) ( $_POST['plan_note'] ?? '' ) ) );
        $admin_note        = sanitize_textarea_field( wp_unslash( (string) ( $_POST['admin_note'] ?? '' ) ) );
        $seller_label      = sanitize_text_field( wp_unslash( (string) ( $_POST['plan_seller_label'] ?? '' ) ) );

        $all_plans = self::get_all_plan_configs();
        if ( ! $target_uid || ! isset( $all_plans[ $plan ] ) || $plan === '_global' ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen adat.' ] );
        }

        // Ne engedjen admin fiókot módosítani
        $target = get_userdata( $target_uid );
        if ( ! $target ) {
            wp_send_json_error( [ 'message' => 'Felhasználó nem található.' ] );
        }
        if ( in_array( 'administrator', (array) $target->roles, true ) ) {
            wp_send_json_error( [ 'message' => 'Adminisztrátor jogköre nem módosítható.' ] );
        }

        $old_credits = absint( get_user_meta( $target_uid, 'va_listing_credits', true ) );
        $new_credits = max( 0, $old_credits + $custom_credits_delta );

        update_user_meta( $target_uid, 'va_plan', $plan );
        update_user_meta( $target_uid, 'va_listing_credits', $new_credits );
        if ( $admin_note !== '' ) {
            update_user_meta( $target_uid, 'va_admin_note', $admin_note );
        } else {
            delete_user_meta( $target_uid, 'va_admin_note' );
        }

        if ( $new_credits !== $old_credits ) {
            $gift_history = get_user_meta( $target_uid, 'va_gift_credit_history', true );
            $gift_history = is_array( $gift_history ) ? $gift_history : [];
            $current_admin = wp_get_current_user();
            $gift_history[] = [
                'saved_at'      => time(),
                'admin_id'      => get_current_user_id(),
                'admin_name'    => $current_admin && ! empty( $current_admin->display_name ) ? $current_admin->display_name : ( $current_admin && ! empty( $current_admin->user_login ) ? $current_admin->user_login : '' ),
                'prev_credits'  => $old_credits,
                'new_credits'   => $new_credits,
                'delta_credits' => $new_credits - $old_credits,
                'note'          => $admin_note,
            ];
            update_user_meta( $target_uid, 'va_gift_credit_history', array_slice( $gift_history, -50 ) );
        }

        // Admin által beállított lejárat (csak ha van érték és nem basic)
        if ( $plan !== 'basic' ) {
            if ( $plan !== 'custom' ) {
                // Egyéb csomagoknál globális default ha nincs lejárat
                $existing_exp = (int) get_user_meta( $target_uid, 'va_plan_expires_at', true );
                if ( $existing_exp <= 0 ) {
                    $dur = 365; // 1 éves lejárat minden csomagra
                    update_user_meta( $target_uid, 'va_plan_expires_at', time() + ( $dur * DAY_IN_SECONDS ) );
                }
            } else {
                // Egyedi csomagnál is a naptár a mérvadó; ha nincs dátum, maradjon az alap 365 napos információ.
                $existing_exp = (int) get_user_meta( $target_uid, 'va_plan_expires_at', true );
                if ( $existing_exp <= 0 ) {
                    update_user_meta( $target_uid, 'va_plan_expires_at', time() + ( 365 * DAY_IN_SECONDS ) );
                }
            }
            // Flagek törlése ha admin megújítja a csomagot
            foreach ( [ 1, 7, 30 ] as $_t ) {
                delete_user_meta( $target_uid, 'va_plan_expiry_warned_' . $_t );
            }
            delete_user_meta( $target_uid, 'va_plan_expired_admin_notified' );
            delete_user_meta( $target_uid, 'va_plan_expired_user_notified' );

            // Ha admin konkrét lejárat dátumot adott meg, az felülírja a fenti számítást.
            if ( $custom_expires_at !== '' ) {
                $exp_ts = strtotime( $custom_expires_at );
                if ( $exp_ts && $exp_ts > 0 ) {
                    update_user_meta( $target_uid, 'va_plan_expires_at', (int) $exp_ts );
                }
            }
        }

        // Kiemelési újratöltés felülírás minden csomagnál menthető.
        if ( $custom_cd > 0 ) {
            update_user_meta( $target_uid, 'va_plan_boost_cooldown', $custom_cd );
        }

        if ( in_array( $plan, [ 'platinum', 'custom' ], true ) ) {
            if ( $custom_lim > 0 ) {
                update_user_meta( $target_uid, 'va_plan_listing_limit', $custom_lim );
            }
            if ( $plan_note !== '' ) {
                update_user_meta( $target_uid, 'va_plan_note', $plan_note );
            } else {
                delete_user_meta( $target_uid, 'va_plan_note' );
            }
            update_user_meta( $target_uid, 'va_seller_label', $seller_label );
        }

        // Csomagváltás utáni limit érvényesítés (felesleges hirdetések felfüggesztése)
        $suspended = self::enforce_plan_limits( $target_uid );

        $cfg = self::get_plan_config( $plan, $target_uid );
        wp_send_json_success( [
            'message'   => 'Ajándék hirdetés jóváírva / beállítva!',
            'plan'      => $plan,
            'label'     => $cfg['label'],
            'icon'      => $cfg['icon'],
            'color'     => $cfg['color'],
            'credits'   => $new_credits,
            'suspended' => $suspended,
        ] );
    }

    /* ══ AJAX: Felhasználó boostol ══════════════════════════════ */

    public static function ajax_boost_listing(): void {
        check_ajax_referer( 'va_user_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $user_id = get_current_user_id();
        $post_id = absint( $_POST['post_id'] ?? 0 );

        if ( ! $post_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés azonosító.' ] );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' ) {
            wp_send_json_error( [ 'message' => 'Hirdetés nem található.' ] );
        }

        // Csak a saját hirdetést boostolhatja
        if ( (int) $post->post_author !== $user_id ) {
            wp_send_json_error( [ 'message' => 'Csak saját hirdetést emelhetsz ki.' ] );
        }

        $mode = sanitize_key( (string) ( $_POST['mode'] ?? 'toggle' ) );
        if ( ! in_array( $mode, [ 'toggle', 'boost', 'remove' ], true ) ) {
            $mode = 'toggle';
        }

        $is_boosted_now = self::is_boosted( $post_id );
        $plan           = self::get_user_plan( $user_id );
        $is_admin       = self::is_admin_user( $user_id );

        if ( ! $is_admin && $plan === 'basic' ) {
            wp_send_json_error( [ 'message' => 'A kiemelés Basic csomagban nem elérhető. Vásároljon nagyobb előfizetést.' ] );
        }

        // Pill levétele: platinum és admin bármikor leveheti
        if ( $mode === 'remove' || ( $mode === 'toggle' && $is_boosted_now ) ) {
            if ( ! $is_admin && $plan === 'basic' ) {
                wp_send_json_error( [ 'message' => 'A kiemelés Basic csomagban nem elérhető. Vásároljon nagyobb előfizetést.' ] );
            }

            self::do_unboost( $post_id );

            wp_send_json_success( [
                'message' => 'Kiemelés levéve.',
                'removed' => true,
            ] );
        }

        $check = self::can_boost( $user_id, $post_id );

        if ( ! $check['can'] ) {
            $hours = (int) ceil( $check['seconds_remaining'] / 3600 );
            $days  = round( $check['seconds_remaining'] / DAY_IN_SECONDS, 1 );
            $msg   = $hours >= 24
                ? "Még {$days} nap múlva emelheted ki ezt a hirdetést."
                : "Még {$hours} óra múlva emelheted ki ezt a hirdetést.";
            wp_send_json_error( [
                'message'           => $msg,
                'seconds_remaining' => $check['seconds_remaining'],
                'cooldown_days'     => $check['cooldown_days'],
            ] );
        }

        self::do_boost( $user_id, $post_id );

        wp_send_json_success( [
            'message' => '✅ Hirdetés kiemelve! Az adott kategóriában az élre kerültél.',
            'removed' => false,
        ] );
    }

    /* ══ AJAX: Felhasználó "Új" pillt kapcsol ═════════════════ */

    public static function ajax_toggle_new_pill(): void {
        check_ajax_referer( 'va_user_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $user_id = get_current_user_id();
        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés azonosító.' ] );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' ) {
            wp_send_json_error( [ 'message' => 'Hirdetés nem található.' ] );
        }

        if ( (int) $post->post_author !== $user_id ) {
            wp_send_json_error( [ 'message' => 'Csak saját hirdetésen kapcsolhatsz "Új" pillt.' ] );
        }

        $mode   = sanitize_key( (string) ( $_POST['mode'] ?? 'toggle' ) );
        $active = self::is_new_pill( $post_id );
        $plan   = self::get_user_plan( $user_id );
        $is_admin = self::is_admin_user( $user_id );

        if ( ! $is_admin && $plan !== 'custom' ) {
            wp_send_json_error( [ 'message' => 'Az "Új" pill kapcsolasa csak Uzleti csomagban erheto el. Vasaroljon nagyobb elofizetest.' ] );
        }

        if ( $mode === 'off' || ( $mode === 'toggle' && $active ) ) {
            delete_post_meta( $post_id, 'va_new_pill_time' );
            wp_send_json_success( [
                'message' => '"Új" pill kikapcsolva.',
                'active'  => false,
            ] );
        }

        update_post_meta( $post_id, 'va_new_pill_time', time() );
        wp_send_json_success( [
            'message' => '"Új" pill bekapcsolva (1 nap).',
            'active'  => true,
        ] );
    }
}
