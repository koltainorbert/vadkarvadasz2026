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

        // Ha bármi (admin, webhook, WC) frissíti a va_plan metát → azonnal enforce
        add_action( 'update_user_meta', [ __CLASS__, 'on_plan_meta_updated' ], 10, 4 );
        add_action( 'added_user_meta',  [ __CLASS__, 'on_plan_meta_updated' ], 10, 4 );

        // Ha kredit jóváírás történik → felfüggesztett hirdetések visszaállítása
        add_action( 'update_user_meta', [ __CLASS__, 'on_credits_meta_updated' ], 10, 4 );
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

    public static function get_user_plan( int $user_id ): string {
        if ( self::is_admin_user( $user_id ) ) {
            return 'platinum';
        }

        $plan = (string) get_user_meta( $user_id, 'va_plan', true );
        $all  = self::get_all_plan_configs();
        // _global key nem plan slug
        return ( isset( $all[ $plan ] ) && $plan !== '_global' ) ? $plan : 'basic';
    }

    /**
     * Plan konfiguráció – DB override-dal, platinum esetén user-specifikus értékekkel.
     * @return array{label:string,color:string,bg:string,icon:string,monthly_limit:int,boost_cooldown:int,basis:string,description:string}
     */
    public static function get_plan_config( string $plan, int $user_id = 0 ): array {
        $all = self::get_all_plan_configs();
        $cfg = ( isset( $all[ $plan ] ) && $plan !== '_global' ) ? $all[ $plan ] : $all['basic'];

        if ( self::is_admin_user( $user_id ) ) {
            $cfg['monthly_limit'] = 0;
            $cfg['boost_cooldown'] = 0;
            return $cfg;
        }

        if ( $plan === 'platinum' && $user_id > 0 ) {
            $custom_limit = (int) get_user_meta( $user_id, 'va_plan_listing_limit', true );
            $custom_cd    = (int) get_user_meta( $user_id, 'va_plan_boost_cooldown', true );
            if ( $custom_limit > 0 ) $cfg['monthly_limit']  = $custom_limit;
            if ( $custom_cd    > 0 ) $cfg['boost_cooldown'] = $custom_cd;
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
            return [ 'can' => true, 'reason' => '', 'used' => 0, 'limit' => 0 ];
        }

        $limit = $cfg['monthly_limit'];
        $used  = ( $cfg['basis'] === 'active' )
            ? self::get_active_listing_count( $user_id )
            : self::get_monthly_listing_count( $user_id );

        if ( $used < $limit ) {
            return [ 'can' => true, 'reason' => '', 'used' => $used, 'limit' => $limit ];
        }

        $label = $cfg['label'];
        if ( $cfg['basis'] === 'active' ) {
            $reason = "{$label} csomaggal egyszerre legfeljebb {$limit} aktív hirdetésed lehet. Töröl egy meglévőt, vagy frissítsd csomagodat!";
        } else {
            $reason = "{$label} csomaggal havonta legfeljebb {$limit} hirdetést adhatsz fel. A hónap végén újra indul a keret.";
        }

        return [ 'can' => false, 'reason' => $reason, 'used' => $used, 'limit' => $limit ];
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
     * "Új" pill aktív-e (fixen 7 napos ablak).
     */
    public static function is_new_pill( int $post_id, int $window_days = 7 ): bool {
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

        // Boosted (az ablakon belül) hirdetések először, aztán feladás dátuma
        $clauses['orderby'] = "CASE WHEN CAST( {$alias}.meta_value AS UNSIGNED ) > {$boost_cutoff} THEN 1 ELSE 0 END DESC, {$wpdb->posts}.post_date DESC";

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
                'seller_label'   => $slug === 'platinum' ? sanitize_text_field( $d['seller_label'] ?? '' ) : '',
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

        $suspended = 0;
        foreach ( $posts as $i => $post ) {
            if ( $i < $limit ) {
                // Belül a limitben – ha korábban felfüggesztettük, visszaállítjuk
                if ( get_post_meta( $post->ID, 'va_suspended_by_plan', true ) === '1' ) {
                    wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'publish' ] );
                    delete_post_meta( $post->ID, 'va_suspended_by_plan' );
                }
            } else {
                // Limit felett → felfüggesztés
                if ( $post->post_status !== 'private' ) {
                    wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'private' ] );
                    update_post_meta( $post->ID, 'va_suspended_by_plan', '1' );
                    $suspended++;
                }
            }
        }

        return $suspended;
    }

    public static function ajax_admin_set_plan(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        check_ajax_referer( 'va_admin_user_plan', 'nonce' );

        $target_uid  = absint( $_POST['user_id'] ?? 0 );
        $plan        = sanitize_key( $_POST['plan'] ?? 'basic' );
        $custom_lim  = absint( $_POST['custom_limit'] ?? 0 );
        $custom_cd   = absint( $_POST['custom_boost_cooldown'] ?? 0 );
        $plan_note   = sanitize_textarea_field( wp_unslash( (string) ( $_POST['plan_note'] ?? '' ) ) );

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

        update_user_meta( $target_uid, 'va_plan', $plan );

        if ( $plan === 'platinum' ) {
            if ( $custom_lim > 0 ) {
                update_user_meta( $target_uid, 'va_plan_listing_limit', $custom_lim );
            }
            if ( $custom_cd > 0 ) {
                update_user_meta( $target_uid, 'va_plan_boost_cooldown', $custom_cd );
            }
            if ( $plan_note !== '' ) {
                update_user_meta( $target_uid, 'va_plan_note', $plan_note );
            }
        }

        // Csomagváltás utáni limit érvényesítés (felesleges hirdetések felfüggesztése)
        $suspended = self::enforce_plan_limits( $target_uid );

        $cfg = self::get_plan_config( $plan, $target_uid );
        wp_send_json_success( [
            'message'   => 'Terv sikeresen frissítve!',
            'plan'      => $plan,
            'label'     => $cfg['label'],
            'icon'      => $cfg['icon'],
            'color'     => $cfg['color'],
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

        // Pill levétele: platinum és admin bármikor leveheti
        if ( $mode === 'remove' || ( $mode === 'toggle' && $is_boosted_now ) ) {
            if ( ! $is_admin && $plan !== 'platinum' ) {
                wp_send_json_error( [ 'message' => 'A pill levételéhez legalább Platinum csomag szükséges.' ] );
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

        if ( $mode === 'off' || ( $mode === 'toggle' && $active ) ) {
            delete_post_meta( $post_id, 'va_new_pill_time' );
            wp_send_json_success( [
                'message' => '"Új" pill kikapcsolva.',
                'active'  => false,
            ] );
        }

        update_post_meta( $post_id, 'va_new_pill_time', time() );
        wp_send_json_success( [
            'message' => '"Új" pill bekapcsolva (7 nap).',
            'active'  => true,
        ] );
    }
}
