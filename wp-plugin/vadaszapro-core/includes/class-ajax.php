<?php
/**
 * AJAX kezelők: hirdetés feladás, watchlist, megtekintés számláló
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Ajax {

    public static function init() {
        // Hirdetés feladás
        add_action( 'wp_ajax_va_submit_listing',  [ __CLASS__, 'submit_listing' ] );
        // Hirdetés szerkesztés (frontend)
        add_action( 'wp_ajax_va_update_listing',  [ __CLASS__, 'update_listing' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_listing_payment_callback' ] );

        // Kredit csomag vásárlás
        add_action( 'wp_ajax_va_buy_credits',        [ __CLASS__, 'buy_credits' ] );
        add_action( 'template_redirect',             [ __CLASS__, 'handle_credit_payment_callback' ] );

        // Watchlist
        add_action( 'wp_ajax_va_toggle_watchlist', [ __CLASS__, 'toggle_watchlist' ] );

        // Megtekintés számláló (nem bejelentkezett is)
        add_action( 'wp_ajax_va_increment_views',        [ __CLASS__, 'increment_views' ] );
        add_action( 'wp_ajax_nopriv_va_increment_views', [ __CLASS__, 'increment_views' ] );
        add_action( 'wp_ajax_va_get_view_geo_report',    [ __CLASS__, 'get_view_geo_report' ] );

        // Szűrő AJAX
        add_action( 'wp_ajax_va_filter_listings',        [ __CLASS__, 'filter_listings' ] );
        add_action( 'wp_ajax_nopriv_va_filter_listings', [ __CLASS__, 'filter_listings' ] );

        // Cache invalidáció – hirdetés mentésekor a szűrő cache törlődik
        add_action( 'save_post_va_listing', [ __CLASS__, 'flush_filter_cache' ] );
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            add_action( 'save_post_va_auction', [ __CLASS__, 'flush_filter_cache' ] );
        }

        // Base64 kép feltöltése médiatárba
        add_action( 'wp_ajax_va_upload_editor_image', [ __CLASS__, 'upload_editor_image' ] );

        // Hirdetés törlésekor editor képek törlése
        add_action( 'before_delete_post', [ __CLASS__, 'delete_editor_images_on_listing_delete' ] );

        // Élő keresés
        add_action( 'wp_ajax_va_live_search',        [ __CLASS__, 'live_search' ] );
        add_action( 'wp_ajax_nopriv_va_live_search', [ __CLASS__, 'live_search' ] );

        // Felhasználói hirdetés-kezelés (dashboard)
        add_action( 'wp_ajax_va_refresh_listing', [ __CLASS__, 'refresh_listing' ] );
        add_action( 'wp_ajax_va_bulk_listings',   [ __CLASS__, 'bulk_listings' ] );
        add_action( 'wp_ajax_va_set_sale_price',  [ __CLASS__, 'set_sale_price' ] );
    }

    /* ── Rate limiting helper ──────────────────────────── */
    /**
     * IP-alapú rate limiting transient-tel.
     * @param string $action  Egyedi azonosító (pl. 'live_search')
     * @param int    $limit   Max kérés száma az időablakon belül
     * @param int    $window  Időablak másodpercben
     */
    private static function is_rate_limited( string $action, int $limit = 30, int $window = 60 ): bool {
        $ip  = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) );
        $key = 'va_rl_' . $action . '_' . md5( $ip );

        $count = (int) get_transient( $key );
        if ( $count >= $limit ) {
            return true;
        }
        if ( $count === 0 ) {
            set_transient( $key, 1, $window );
        } else {
            // Növelés – maradék TTL megőrzése nem lehetséges transient-tel, de
            // a window újraindul csak ha a transient lejár. Ez elfogadható.
            set_transient( $key, $count + 1, $window );
        }
        return false;
    }

    private static function count_uploaded_images( array $files ): int {
        if ( empty( $files ) || empty( $files['name'] ) ) {
            return 0;
        }

        $names = is_array( $files['name'] ) ? $files['name'] : [ $files['name'] ];
        $count = 0;
        foreach ( $names as $name ) {
            if ( is_string( $name ) && trim( $name ) !== '' ) {
                $count++;
            }
        }
        return $count;
    }

    /* ── Hirdetés szerkesztés (frontend) ──────────────── */
    public static function update_listing() {
        check_ajax_referer( 'va_update_listing', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés.' ] );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' || (int) $post->post_author !== get_current_user_id() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság ehhez a hirdetéshez.' ] );
        }

        $title       = sanitize_text_field( wp_unslash( $_POST['title']       ?? '' ) );
        $description = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
        $price       = floatval( $_POST['price'] ?? 0 );
        $price_type  = sanitize_key( $_POST['price_type'] ?? 'fixed' );
        $phone       = sanitize_text_field( wp_unslash( $_POST['phone']    ?? '' ) );
        $location    = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
        if ( $location === '' ) {
            $location = 'Veszprém Gyulafirátót';
        }
        $brand       = sanitize_text_field( wp_unslash( $_POST['brand']    ?? '' ) );
        $model       = sanitize_text_field( wp_unslash( $_POST['model']    ?? '' ) );
        $caliber     = sanitize_text_field( wp_unslash( $_POST['caliber']  ?? '' ) );
        $fuel_type   = sanitize_key( (string) ( $_POST['fuel_type'] ?? '' ) );
        $transmission = sanitize_key( (string) ( $_POST['transmission'] ?? '' ) );
        $mileage     = intval( $_POST['mileage'] ?? 0 );
        $year        = intval( $_POST['year'] ?? 0 );
        $license_req = ! empty( $_POST['license_req'] ) ? '1' : '0';
        $category    = intval( $_POST['category'] ?? 0 );
        $county      = intval( $_POST['county']   ?? 0 );
        $condition   = intval( $_POST['condition'] ?? 0 );

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'A cím kötelező.' ] );
        }

        $keep_raw = sanitize_text_field( wp_unslash( $_POST['keep_images'] ?? '' ) );
        $keep_ids = array_filter( array_map( 'absint', explode( ',', $keep_raw ) ) );
        $new_image_count = self::count_uploaded_images( (array) ( $_FILES['listing_images'] ?? [] ) );

        $quality = function_exists( 'va_validate_listing_quality_input' )
            ? va_validate_listing_quality_input( [
                'title'        => $title,
                'description'  => $description,
                'image_count'  => count( $keep_ids ) + $new_image_count,
                'price'        => $price,
                'price_type'   => $price_type,
                'year'         => $year,
                'mileage'      => $mileage,
                'brand'        => $brand,
                'model'        => $model,
                'fuel_type'    => $fuel_type,
                'transmission' => $transmission,
                'location'     => $location,
            ] )
            : [ 'ok' => true, 'message' => '' ];

        if ( empty( $quality['ok'] ) ) {
            wp_send_json_error( [ 'message' => (string) ( $quality['message'] ?? 'A hirdetés nem felel meg a közzétételi minimumoknak.' ) ] );
        }

        wp_update_post( [
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_content' => $description,
        ] );

        $metas = [
            'va_price'       => $price,
            'va_price_type'  => $price_type,
            'va_phone'       => $phone,
            'va_location'    => $location,
            'va_email_show'  => '1',
            'va_brand'       => $brand,
            'va_model'       => $model,
            'va_caliber'     => $caliber,
            'va_year'        => $year,
            'va_license_req' => $license_req,
        ];

        // Típus-specifikus extra mezők mentése
        $type_extra_text = [ 'va_color', 'va_first_reg', 'va_tech_inspect',
                             'va_upholstery_1', 'va_upholstery_2', 'va_internal_id', 'va_vin', 'va_second_phone',
                             'va_summer_tire_front', 'va_summer_tire_rear', 'va_winter_tire_front', 'va_winter_tire_rear' ];
        $type_extra_num  = [ 'va_mileage', 'va_performance_kw', 'va_engine_size', 'va_owners', 'va_keys', 'va_own_weight',
                             'va_gross_weight', 'va_passengers', 'va_trunk_liters',
                             'va_area_m2', 'va_rooms', 'va_floor', 'va_total_floors', 'va_lot_size', 'va_building_year' ];
        $type_extra_key  = [ 'va_fuel_type', 'va_transmission', 'va_body_type', 'va_doors', 'va_parking', 'va_furnished', 'va_heating',
                             'va_drive', 'va_vehicle_condition', 'va_doc_type', 'va_doc_validity', 'va_ac_type', 'va_eco_class',
                             'va_cylinder_layout', 'va_vehicle_type', 'va_roof_type' ];
        $type_extra_bool = [ 'va_previous_damage', 'va_service_book', 'va_balcony', 'va_color_metallic', 'va_range_gearbox' ];
        foreach ( $type_extra_text as $k ) {
            if ( isset( $_POST[ str_replace( 'va_', '', $k ) ] ) ) {
                $metas[ $k ] = sanitize_text_field( wp_unslash( $_POST[ str_replace( 'va_', '', $k ) ] ) );
            }
        }
        foreach ( $type_extra_num as $k ) {
            $short = str_replace( 'va_', '', $k );
            if ( isset( $_POST[ $short ] ) ) {
                $metas[ $k ] = intval( $_POST[ $short ] );
            }
        }
        foreach ( $type_extra_key as $k ) {
            $short = str_replace( 'va_', '', $k );
            if ( isset( $_POST[ $short ] ) ) {
                $metas[ $k ] = sanitize_key( $_POST[ $short ] );
            }
        }
        foreach ( $type_extra_bool as $k ) {
            $short = str_replace( 'va_', '', $k );
            $metas[ $k ] = ! empty( $_POST[ $short ] ) ? '1' : '0';
        }

        foreach ( $metas as $k => $v ) {
            update_post_meta( $post_id, $k, $v );
        }

        // va_extras – JSON tömb
        if ( isset( $_POST['extras'] ) ) {
            $raw_extras   = is_array( $_POST['extras'] ) ? (array) $_POST['extras'] : [];
            $valid_extras = array_keys( class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_options() : [] );
            $clean_extras = array_values( array_intersect( array_map( 'sanitize_key', $raw_extras ), $valid_extras ) );
            update_post_meta( $post_id, 'va_extras', wp_json_encode( $clean_extras ) );
        }

        if ( $category ) wp_set_post_terms( $post_id, [ $category ], 'va_category' );
        if ( $county   ) wp_set_post_terms( $post_id, [ $county ],   'va_county'   );
        if ( $condition) wp_set_post_terms( $post_id, [ $condition ], 'va_condition' );

        // Megtartandó meglévő képek
        // Töröljük azokat a galériában lévő képeket amiket nem tartanak meg
        $old_gallery_str = get_post_meta( $post_id, 'va_gallery_ids', true );
        $old_gallery = array_filter( array_map( 'absint', explode( ',', (string) $old_gallery_str ) ) );
        foreach ( $old_gallery as $old_id ) {
            if ( $old_id && ! in_array( $old_id, $keep_ids, true ) ) {
                wp_delete_attachment( $old_id, true );
            }
        }

        // Borítókép / megtartandó galériakép beállítása
        $feat_existing = absint( $_POST['featured_existing_id'] ?? 0 );

        // Új képek feltöltése
        $img_errors = [];
        if ( ! empty( $_FILES['listing_images'] ) && ! empty( $_FILES['listing_images']['name'][0] ) ) {
            $featured_idx = isset( $_POST['featured_image_index'] ) ? intval( $_POST['featured_image_index'] ) : 0;
            $img_errors = self::handle_images( $post_id, $_FILES['listing_images'], max( 0, $featured_idx ) );

            // handle_images beírta az új képeket va_gallery_ids-be; hozzáfűzzük a keep_ids-t elé
            $new_gallery_str = get_post_meta( $post_id, 'va_gallery_ids', true );
            $new_ids = array_filter( array_map( 'absint', explode( ',', (string) $new_gallery_str ) ) );
            $final = array_merge( $keep_ids, $new_ids );
            update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $final ) );

            // Ha meglévő kép a borítókép
            if ( $feat_existing && in_array( $feat_existing, $keep_ids, true ) ) {
                set_post_thumbnail( $post_id, $feat_existing );
            }
        } else {
            // Nincs új kép – csak keep_ids marad
            update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $keep_ids ) );
            if ( $feat_existing && in_array( $feat_existing, $keep_ids, true ) ) {
                set_post_thumbnail( $post_id, $feat_existing );
            } elseif ( ! empty( $keep_ids ) ) {
                set_post_thumbnail( $post_id, $keep_ids[0] );
            }
        }

        // listing_meta szinkronizálás
        if ( function_exists( 'va_sync_listing_meta' ) ) {
            va_sync_listing_meta( $post_id );
        }

        wp_send_json_success( [
            'message'   => 'Hirdetés sikeresen frissítve!',
            'post_id'   => $post_id,
            'permalink' => get_permalink( $post_id ),
        ] );
    }

    /* ── Hirdetés feladás ──────────────────────────────── */
    public static function submit_listing() {
        check_ajax_referer( 'va_submit_listing', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $free_limit = max( 0, absint( get_option( 'va_free_listings_limit', 1 ) ) );
        $paid_price = max( 0, absint( get_option( 'va_listing_price_after_free', 1990 ) ) );
        $payment_url = trim( (string) get_option( 'va_listing_payment_url', '' ) );

        $title       = sanitize_text_field( wp_unslash( $_POST['title']       ?? '' ) );
        $description = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
        $price       = floatval( $_POST['price'] ?? 0 );
        $price_type  = sanitize_key( $_POST['price_type'] ?? 'fixed' );
        $phone       = sanitize_text_field( wp_unslash( $_POST['phone']  ?? '' ) );
        $location    = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
        if ( $location === '' ) {
            $location = 'Veszprém Gyulafirátót';
        }
        $brand       = sanitize_text_field( wp_unslash( $_POST['brand']  ?? '' ) );
        $model       = sanitize_text_field( wp_unslash( $_POST['model']  ?? '' ) );
        $caliber     = sanitize_text_field( wp_unslash( $_POST['caliber'] ?? '' ) );
        $fuel_type   = sanitize_key( (string) ( $_POST['fuel_type'] ?? '' ) );
        $transmission = sanitize_key( (string) ( $_POST['transmission'] ?? '' ) );
        $mileage     = intval( $_POST['mileage'] ?? 0 );
        $year        = intval( $_POST['year'] ?? 0 );
        $license_req = ! empty( $_POST['license_req'] ) ? '1' : '0';
        $category    = intval( $_POST['category'] ?? 0 );
        $county      = intval( $_POST['county']   ?? 0 );
        $condition   = intval( $_POST['condition'] ?? 0 );

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'A cím kötelező.' ] );
        }

        $new_image_count = self::count_uploaded_images( (array) ( $_FILES['listing_images'] ?? [] ) );
        $quality = function_exists( 'va_validate_listing_quality_input' )
            ? va_validate_listing_quality_input( [
                'title'        => $title,
                'description'  => $description,
                'image_count'  => $new_image_count,
                'price'        => $price,
                'price_type'   => $price_type,
                'year'         => $year,
                'mileage'      => $mileage,
                'brand'        => $brand,
                'model'        => $model,
                'fuel_type'    => $fuel_type,
                'transmission' => $transmission,
                'location'     => $location,
            ] )
            : [ 'ok' => true, 'message' => '' ];

        if ( empty( $quality['ok'] ) ) {
            wp_send_json_error( [ 'message' => (string) ( $quality['message'] ?? 'A hirdetés nem felel meg a közzétételi minimumoknak.' ) ] );
        }

        // Plan-alapú limit ellenőrzés (VA_User_Roles rendszer)
        $plan_check    = VA_User_Roles::can_post_listing( $user_id );
        $is_free_allowed = true; // plan rendszer engedélyez → nincs kredit levonás

        if ( ! $plan_check['can'] ) {
            wp_send_json_error([
                'message'      => $plan_check['reason'],
                'need_upgrade' => true,
            ]);
        }

        // WP beállítástól függ: auto-publish vagy pending review
        $final_status = get_option( 'va_auto_publish_listings', '0' ) === '1' ? 'publish' : 'pending';
        $status = $final_status;

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $description,
            'post_status'  => $status,
            'post_type'    => 'va_listing',
            'post_author'  => $user_id,
        ], true );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
        }

        // Meta mentés
        $metas = [
            'va_price'       => $price,
            'va_price_type'  => $price_type,
            'va_phone'       => $phone,
            'va_location'    => $location,
            'va_email_show'  => '1',
            'va_brand'       => $brand,
            'va_model'       => $model,
            'va_caliber'     => $caliber,
            'va_year'        => $year,
            'va_license_req' => $license_req,
            'va_views'       => 0,
        ];

        // Típus-specifikus extra mezők mentése (submit)
        $type_extra_text = [ 'va_color', 'va_first_reg', 'va_tech_inspect',
                             'va_upholstery_1', 'va_upholstery_2', 'va_internal_id', 'va_vin', 'va_second_phone',
                             'va_summer_tire_front', 'va_summer_tire_rear', 'va_winter_tire_front', 'va_winter_tire_rear' ];
        $type_extra_num  = [ 'va_mileage', 'va_performance_kw', 'va_engine_size', 'va_owners', 'va_keys', 'va_own_weight',
                             'va_gross_weight', 'va_passengers', 'va_trunk_liters',
                             'va_area_m2', 'va_rooms', 'va_floor', 'va_total_floors', 'va_lot_size', 'va_building_year' ];
        $type_extra_key  = [ 'va_fuel_type', 'va_transmission', 'va_body_type', 'va_doors', 'va_parking', 'va_furnished', 'va_heating',
                             'va_drive', 'va_vehicle_condition', 'va_doc_type', 'va_doc_validity', 'va_ac_type', 'va_eco_class',
                             'va_cylinder_layout', 'va_vehicle_type', 'va_roof_type' ];
        $type_extra_bool = [ 'va_previous_damage', 'va_service_book', 'va_balcony', 'va_color_metallic', 'va_range_gearbox' ];
        foreach ( $type_extra_text as $k ) {
            $short = str_replace( 'va_', '', $k );
            if ( isset( $_POST[ $short ] ) ) $metas[ $k ] = sanitize_text_field( wp_unslash( $_POST[ $short ] ) );
        }
        foreach ( $type_extra_num as $k ) {
            $short = str_replace( 'va_', '', $k );
            if ( isset( $_POST[ $short ] ) ) $metas[ $k ] = intval( $_POST[ $short ] );
        }
        foreach ( $type_extra_key as $k ) {
            $short = str_replace( 'va_', '', $k );
            if ( isset( $_POST[ $short ] ) ) $metas[ $k ] = sanitize_key( $_POST[ $short ] );
        }
        foreach ( $type_extra_bool as $k ) {
            $short = str_replace( 'va_', '', $k );
            $metas[ $k ] = ! empty( $_POST[ $short ] ) ? '1' : '0';
        }
        foreach ( $metas as $k => $v ) {
            update_post_meta( $post_id, $k, $v );
        }

        // va_extras – JSON tömb
        if ( isset( $_POST['extras'] ) ) {
            $raw_extras   = is_array( $_POST['extras'] ) ? (array) $_POST['extras'] : [];
            $valid_extras = array_keys( class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_options() : [] );
            $clean_extras = array_values( array_intersect( array_map( 'sanitize_key', $raw_extras ), $valid_extras ) );
            update_post_meta( $post_id, 'va_extras', wp_json_encode( $clean_extras ) );
        }

        // Taxonómiák
        if ( $category ) wp_set_post_terms( $post_id, [ $category ], 'va_category' );
        if ( $county   ) wp_set_post_terms( $post_id, [ $county ],   'va_county'   );
        if ( $condition) wp_set_post_terms( $post_id, [ $condition ], 'va_condition' );

        // Képfeltöltés kezelése
        $img_errors = [];
        if ( ! empty( $_FILES['listing_images'] ) ) {
            $featured_idx = isset( $_POST['featured_image_index'] ) ? absint( (string) $_POST['featured_image_index'] ) : 0;
            $img_errors = self::handle_images( $post_id, $_FILES['listing_images'], $featured_idx );
        }

        // Ha nem ingyenes: kredit levonás
        if ( ! $is_free_allowed ) {
            $credits = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );
            update_user_meta( $user_id, 'va_listing_credits', max( 0, $credits - 1 ) );
        }

        $msg = $status === 'publish'
            ? 'Hirdetés sikeresen feladva!'
            : 'Hirdetés mentve – jóváhagyásra vár.';

        wp_send_json_success([
            'message'    => $msg,
            'post_id'    => $post_id,
            'permalink'  => get_permalink( $post_id ),
            'img_errors' => $img_errors,
        ]);
    }

    public static function handle_listing_payment_callback(): void {
        $payment_state = isset( $_GET['va_payment'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_payment'] ) ) : '';
        if ( $payment_state === '' ) {
            return;
        }

        $token = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
        if ( $token === '' || ! is_user_logged_in() ) {
            return;
        }

        $posts = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => [ 'draft', 'pending', 'publish' ],
            'author'         => get_current_user_id(),
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'   => 'va_payment_token',
                    'value' => $token,
                ],
            ],
        ]);

        if ( empty( $posts ) ) {
            va_set_flash( 'error', 'A fizetési tranzakció nem található.' );
            self::redirect_submit_page();
        }

        $post = $posts[0];
        $post_id = (int) $post->ID;

        if ( $payment_state === 'cancel' ) {
            va_set_flash( 'warning', 'A fizetés megszakadt. A hirdetés vázlatban maradt.' );
            self::redirect_submit_page();
        }

        if ( $payment_state !== 'success' ) {
            return;
        }

        $already_paid = get_post_meta( $post_id, 'va_payment_status', true ) === 'paid';
        if ( ! $already_paid ) {
            $final_status = get_option( 'va_auto_publish_listings', '0' ) === '1' ? 'publish' : 'pending';
            wp_update_post([
                'ID'          => $post_id,
                'post_status' => $final_status,
            ]);

            update_post_meta( $post_id, 'va_payment_status', 'paid' );
            update_post_meta( $post_id, 'va_payment_paid_at', current_time( 'mysql' ) );

            $invoice_no = self::generate_invoice( $post_id );
            $msg = 'Sikeres fizetés. A hirdetés aktiválva.';
            if ( $invoice_no !== '' ) {
                $msg .= ' Számla: ' . $invoice_no;
            }
            va_set_flash( 'success', $msg );
        } else {
            va_set_flash( 'info', 'A fizetés már feldolgozásra került.' );
        }

        self::redirect_submit_page();
    }

    /* ── Kredit csomag vásárlás ────────────────────────── */
    public static function buy_credits(): void {
        check_ajax_referer( 'va_buy_credits', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $return_to = isset( $_POST['return_to'] ) ? sanitize_key( (string) wp_unslash( $_POST['return_to'] ) ) : 'buy';
        if ( ! in_array( $return_to, [ 'buy', 'submit' ], true ) ) {
            $return_to = 'buy';
        }

        $qty = absint( $_POST['qty'] ?? 0 );
        if ( $qty < 1 ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen mennyiség.' ] );
        }

        $packages = self::get_credit_packages();
        // Legolcsóbb egységár-logika: a legmagasabb darabszámú csomag ami <= $qty
        $unit_price = (int) get_option( 'va_listing_price_after_free', 1990 );
        $total      = $unit_price * $qty;

        // Keresünk matching csomagot
        foreach ( array_reverse( $packages ) as $pkg ) {
            if ( $qty >= $pkg['qty'] ) {
                $unit_price = $pkg['unit_price'];
                $total      = $pkg['total'];
                break;
            }
        }

        $payment_url = trim( (string) get_option( 'va_listing_payment_url', '' ) );

        if ( $payment_url === '' ) {
            wp_send_json_error( [ 'message' => 'Fizetési szolgáltató nincs beállítva. Kérjük, lépjen kapcsolatba az adminisztrátorral.' ] );
        }

        $token  = wp_generate_password( 32, false, false );
        $user_id = get_current_user_id();

        // Token elmentése átmeneti adatban
        set_transient( 'va_credit_token_' . $token, [
            'user_id'    => $user_id,
            'qty'        => $qty,
            'amount'     => $total,
            'return_to'  => $return_to,
            'created_at' => time(),
        ], 3600 );

        $return_url = $return_to === 'submit'
            ? self::get_submit_page_url()
            : self::get_buy_credits_page_url();

        $success_url = add_query_arg([
            'va_credit_payment' => 'success',
            'token'             => rawurlencode( $token ),
        ], $return_url );
        $cancel_url = add_query_arg([
            'va_credit_payment' => 'cancel',
            'token'             => rawurlencode( $token ),
        ], $return_url );

        $checkout_url = add_query_arg([
            'intent'      => 'credit_purchase',
            'qty'         => $qty,
            'amount'      => $total,
            'token'       => $token,
            'success_url' => rawurlencode( $success_url ),
            'cancel_url'  => rawurlencode( $cancel_url ),
        ], $payment_url );

        wp_send_json_success( [
            'checkout_url' => esc_url_raw( $checkout_url ),
            'total'        => $total,
            'qty'          => $qty,
        ] );
    }

    /* ── Kredit fizetés callback ───────────────────────── */
    public static function handle_credit_payment_callback(): void {
        $state = isset( $_GET['va_credit_payment'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_credit_payment'] ) ) : '';
        if ( $state === '' ) return;

        $token = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
        if ( $token === '' || ! is_user_logged_in() ) return;

        $data = get_transient( 'va_credit_token_' . $token );
        if ( ! $data || (int) $data['user_id'] !== get_current_user_id() ) {
            va_set_flash( 'error', 'A fizetési session érvénytelen vagy lejárt.' );
            self::redirect_buy_credits_page();
            return;
        }

        $return_to = ( isset( $data['return_to'] ) && $data['return_to'] === 'submit' ) ? 'submit' : 'buy';

        if ( $state === 'cancel' ) {
            delete_transient( 'va_credit_token_' . $token );
            va_set_flash( 'warning', 'A fizetés megszakadt.' );
            if ( $return_to === 'submit' ) {
                self::redirect_submit_page();
            }
            self::redirect_buy_credits_page();
            return;
        }

        if ( $state === 'success' ) {
            $qty     = absint( $data['qty'] );
            $user_id = (int) $data['user_id'];
            $current = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );
            update_user_meta( $user_id, 'va_listing_credits', $current + $qty );
            delete_transient( 'va_credit_token_' . $token );

            // Felfüggesztett hirdetések visszakapcsolása ha most van elég keret
            if ( class_exists( 'VA_User_Roles' ) ) {
                delete_transient( 'va_enforce_ok_' . $user_id );
                VA_User_Roles::enforce_plan_limits( $user_id );
            }

            va_set_flash( 'success', $qty . ' hirdetési kredit jóváírva! Most már feladhatod a hirdetésedet.' );
            if ( $return_to === 'submit' ) {
                self::redirect_submit_page();
            }
            self::redirect_buy_credits_page();
        }
    }

    /* ── Kredit csomagok definíciója ───────────────────── */
    public static function get_credit_packages(): array {
        $base = (int) get_option( 'va_listing_price_after_free', 1990 );

        $default_qtys   = [ 1 => 1, 2 => 3, 3 => 5, 4 => 10 ];
        $default_labels = [ 1 => 'Basic', 2 => 'Silver', 3 => 'Gold', 4 => 'Platinum' ];
        $default_prices = [ 1 => 0, 2 => (int) round( $base * .9 ), 3 => (int) round( $base * .8 ), 4 => (int) round( $base * .7 ) ];
        $default_badges = [ 1 => '', 2 => '–10%', 3 => '–20%', 4 => '–30%' ];

        $packages = [];
        for ( $n = 1; $n <= 4; $n++ ) {
            $enabled = get_option( "va_pc_{$n}_enabled", '1' ) === '1';
            if ( ! $enabled ) continue;

            $qty   = max( 1, (int) get_option( "va_pc_{$n}_qty",   $default_qtys[$n] ) );
            $price = (int) get_option( "va_pc_{$n}_price", $default_prices[$n] );
            // Fallback: ha az ár 0 és nem ingyenes kártya, alapár
            $free  = get_option( "va_pc_{$n}_free", $n === 1 ? '1' : '0' ) === '1';
            if ( ! $free && $price <= 0 ) $price = $base;
            $total = $qty * $price;
            $label = (string) get_option( "va_pc_{$n}_label", $default_labels[$n] );
            $badge = (string) get_option( "va_pc_{$n}_badge", $default_badges[$n] );

            $packages[] = [
                'qty'        => $qty,
                'label'      => $qty . ' kredit',
                'unit_price' => $price,
                'total'      => $total,
                'badge'      => $badge,
            ];
        }

        usort( $packages, static fn( $a, $b ) => $a['qty'] <=> $b['qty'] );

        if ( empty( $packages ) ) {
            return [
                [ 'qty' => 1,  'label' => '1 hirdetés',   'unit_price' => $base,               'total' => $base,                      'badge' => '' ],
                [ 'qty' => 3,  'label' => '3 hirdetés',   'unit_price' => (int) round($base*.9),'total' => (int) round($base*3*.9),   'badge' => '–10%' ],
                [ 'qty' => 5,  'label' => '5 hirdetés',   'unit_price' => (int) round($base*.8),'total' => (int) round($base*5*.8),   'badge' => '–20%' ],
                [ 'qty' => 10, 'label' => '10 hirdetés',  'unit_price' => (int) round($base*.7),'total' => (int) round($base*10*.7),  'badge' => '–30%' ],
            ];
        }

        return $packages;
    }

    private static function generate_invoice( int $post_id ): string {
        $prefix_raw = (string) get_option( 'va_invoice_prefix', 'VA' );
        $prefix = strtoupper( preg_replace( '/[^A-Z0-9\-]/', '', remove_accents( $prefix_raw ) ) );
        if ( $prefix === '' ) {
            $prefix = 'VA';
        }

        $next = max( 1, absint( get_option( 'va_invoice_next_number', 1 ) ) );
        update_option( 'va_invoice_next_number', $next + 1 );

        $invoice_no = $prefix . '-' . date( 'Y' ) . '-' . str_pad( (string) $next, 6, '0', STR_PAD_LEFT );
        $amount = (int) get_post_meta( $post_id, 'va_payment_amount', true );
        $post = get_post( $post_id );

        $billing_company = (string) get_option( 'va_billing_company_name', 'Vadaszapro Kft.' );
        $billing_address = (string) get_option( 'va_billing_company_address', 'Magyarorszag' );
        $billing_tax     = (string) get_option( 'va_billing_tax_number', '' );
        $billing_email   = (string) get_option( 'va_billing_email', (string) get_option( 'admin_email', '' ) );
        $billing_phone   = (string) get_option( 'va_billing_phone', '' );
        $footer_note     = (string) get_option( 'va_invoice_footer_note', 'Koszonjuk a vasarlast!' );

        update_post_meta( $post_id, 'va_invoice_no', $invoice_no );
        update_post_meta( $post_id, 'va_invoice_amount', $amount );
        update_post_meta( $post_id, 'va_invoice_generated_at', current_time( 'mysql' ) );
        update_post_meta( $post_id, 'va_invoice_company_name', $billing_company );
        update_post_meta( $post_id, 'va_invoice_company_address', $billing_address );
        update_post_meta( $post_id, 'va_invoice_tax_number', $billing_tax );

        $upload = wp_upload_dir();
        if ( empty( $upload['error'] ) ) {
            $dir = trailingslashit( $upload['basedir'] ) . 'va-invoices';
            if ( ! wp_mkdir_p( $dir ) ) {
                return $invoice_no;
            }

            $filename = sanitize_file_name( strtolower( $invoice_no ) . '.pdf' );
            $path = trailingslashit( $dir ) . $filename;
            $url  = trailingslashit( $upload['baseurl'] ) . 'va-invoices/' . $filename;

            $lines = [
                'Vadaszapro - Szamla',
                'Szamlaszam: ' . $invoice_no,
                'Datum: ' . current_time( 'Y-m-d H:i:s' ),
                'Kiallito: ' . $billing_company,
                'Cim: ' . $billing_address,
                'Adoszam: ' . $billing_tax,
                'Email: ' . $billing_email,
                'Telefon: ' . $billing_phone,
                'Hirdetes ID: ' . $post_id,
                'Hirdetes cim: ' . ( $post ? $post->post_title : '' ),
                'Tetel: Hirdetes feladas dij',
                'Osszeg: ' . number_format( $amount, 0, ',', ' ' ) . ' Ft',
                'Megjegyzes: ' . $footer_note,
            ];

            $pdf = self::build_simple_invoice_pdf( $lines );
            if ( $pdf !== '' ) {
                file_put_contents( $path, $pdf );
            }
            update_post_meta( $post_id, 'va_invoice_url', esc_url_raw( $url ) );
        }

        return $invoice_no;
    }

    private static function build_simple_invoice_pdf( array $lines ): string {
        $safe_lines = [];
        foreach ( $lines as $line ) {
            $line = sanitize_text_field( (string) $line );
            $line = remove_accents( $line );
            $safe_lines[] = self::escape_pdf_text( $line );
        }

        $stream = "BT\n/F1 16 Tf\n50 790 Td\n(" . ( $safe_lines[0] ?? 'Szamla' ) . ") Tj\n";
        $stream .= "/F1 11 Tf\n0 -28 Td\n";

        for ( $i = 1; $i < count( $safe_lines ); $i++ ) {
            $stream .= "(" . $safe_lines[ $i ] . ") Tj\n0 -18 Td\n";
        }
        $stream .= "ET";

        $len = strlen( $stream );

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length {$len} >>\nstream\n{$stream}\nendstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [ 0 ];

        foreach ( $objects as $obj ) {
            $offsets[] = strlen( $pdf );
            $pdf .= $obj;
        }

        $xref_pos = strlen( $pdf );
        $count = count( $offsets );
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";

        for ( $i = 1; $i < $count; $i++ ) {
            $pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref_pos}\n%%EOF";

        return $pdf;
    }

    private static function escape_pdf_text( string $text ): string {
        $text = str_replace( "\\", "\\\\", $text );
        $text = str_replace( "(", "\\(", $text );
        $text = str_replace( ")", "\\)", $text );
        return $text;
    }

    private static function get_submit_page_url(): string {
        $submit_page = get_page_by_path( 'va-hirdetes-feladas' );
        return $submit_page ? get_permalink( $submit_page ) : home_url( '/va-hirdetes-feladas/' );
    }

    private static function get_buy_credits_page_url(): string {
        $buy_page = get_page_by_path( 'va-kredit-vasarlas' );
        return $buy_page ? get_permalink( $buy_page ) : home_url( '/va-kredit-vasarlas/' );
    }

    private static function redirect_submit_page(): void {
        $url = self::get_submit_page_url();
        wp_safe_redirect( $url );
        exit;
    }

    private static function redirect_buy_credits_page(): void {
        $url = self::get_buy_credits_page_url();
        wp_safe_redirect( $url );
        exit;
    }

    /* ── Kép tömörítés + átméretezés ─────────────────────
     * Átméretezi ha szélesebb a max értéknél, és beállítja a JPEG minőséget.
     * A fájlt felülírja helyben (az attachment URL nem változik).
     * ─────────────────────────────────────────────────────── */
    private static function compress_image( string $file_path ): void {
        $quality   = (int) get_option( 'va_img_quality',   82 );
        $max_width = (int) get_option( 'va_img_max_width', 1920 );
        if ( $quality  < 10 )  $quality  = 10;
        if ( $quality  > 100 ) $quality  = 100;
        if ( $max_width < 400 ) $max_width = 400;

        $editor = wp_get_image_editor( $file_path );
        if ( is_wp_error( $editor ) ) return;

        $size = $editor->get_size();
        if ( ! empty( $size['width'] ) && $size['width'] > $max_width ) {
            $editor->resize( $max_width, null, false );
        }

        $editor->set_quality( $quality );
        $editor->save( $file_path ); // helyben felülírja
    }

    /* ── Képfeltöltés ──────────────────────────────────── */
    private static function handle_images( $post_id, $files, int $featured_idx = 0 ): array {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Grant upload_files cap via filter (no DB write, reverted after)
        $cap_filter = static function ( $allcaps ) {
            $allcaps['upload_files'] = true;
            return $allcaps;
        };
        add_filter( 'user_has_cap', $cap_filter );

        // Felhasználónkénti könyvtár: /va-users/{author_id}/listings/{post_id}/
        $listing_author_id = (int) get_post_field( 'post_author', $post_id );
        if ( ! $listing_author_id ) $listing_author_id = get_current_user_id();
        $va_listing_dir_filter = static function( $dirs ) use ( $listing_author_id, $post_id ) {
            $dirs['subdir'] = '/va-users/' . $listing_author_id . '/listings/' . $post_id;
            $dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
            $dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
            return $dirs;
        };
        add_filter( 'upload_dir', $va_listing_dir_filter );

        $max_images     = intval( get_option( 'va_max_images_per_listing', 10 ) );
        $allowed_types  = [ 'image/jpeg', 'image/png', 'image/webp' ];
        $count          = 0;
        $attachment_ids = [];
        $errors         = [];

        // Normalizálás (több fájl esetén)
        $file_count = is_array( $files['name'] ) ? count( $files['name'] ) : 1;

        for ( $i = 0; $i < $file_count && $count < $max_images; $i++ ) {
            if ( is_array( $files['name'] ) ) {
                $single = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
            } else {
                $single = $files;
            }

            if ( (int) $single['error'] !== UPLOAD_ERR_OK ) {
                $errors[] = 'PHP upload error ' . $single['error'] . ' for ' . $single['name'];
                continue;
            }
            if ( ! in_array( $single['type'], $allowed_types, true ) ) {
                $errors[] = 'Invalid type ' . $single['type'];
                continue;
            }

            $_FILES['va_upload'] = $single;
            $attachment_id = media_handle_upload( 'va_upload', $post_id );

            if ( is_wp_error( $attachment_id ) ) {
                $errors[] = $attachment_id->get_error_message();
            } else {
                // Tömörítés + átméretezés
                $file_path = get_attached_file( $attachment_id );
                if ( $file_path ) {
                    self::compress_image( $file_path );
                    // Thumbnail-ek újragenerálása a módosított fájlból
                    wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );
                }
                $attachment_ids[] = $attachment_id;
                $count++;
            }
        }

        remove_filter( 'upload_dir', $va_listing_dir_filter );
        remove_filter( 'user_has_cap', $cap_filter );

        // Főkép beállítása a kiválasztott index alapján (vagy az első ha invalid)
        if ( ! empty( $attachment_ids ) ) {
            $feat = isset( $attachment_ids[ $featured_idx ] ) ? $attachment_ids[ $featured_idx ] : $attachment_ids[0];
            set_post_thumbnail( $post_id, $feat );
            update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $attachment_ids ) );
        }

        return $errors;
    }

    /* ── Watchlist (kedvencek) ─────────────────────────── */
    public static function toggle_watchlist() {
        check_ajax_referer( 'va_user_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Be kell jelentkezni.' ] );
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = intval( $_POST['post_id'] ?? 0 );

        if ( ! $post_id ) wp_send_json_error();

        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}va_watchlist WHERE user_id = %d AND post_id = %d",
            $user_id, $post_id
        ));

        if ( $exists ) {
            $wpdb->delete( $wpdb->prefix . 'va_watchlist', [ 'user_id' => $user_id, 'post_id' => $post_id ], [ '%d', '%d' ] );
            wp_send_json_success( [ 'action' => 'removed', 'message' => 'Eltávolítva a kedvencekből.' ] );
        } else {
            $wpdb->insert( $wpdb->prefix . 'va_watchlist', [ 'user_id' => $user_id, 'post_id' => $post_id ], [ '%d', '%d' ] );
            wp_send_json_success( [ 'action' => 'added', 'message' => 'Hozzáadva a kedvencekhez.' ] );
        }
    }

    /* ── Megtekintés számláló ──────────────────────────── */
    public static function increment_views() {
        check_ajax_referer( 'va_user_nonce', 'nonce' );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) wp_send_json_error();

        // Csak va_listing és va_auction post típusra engedett
        $allowed_types = [ 'va_listing', 'va_auction' ];
        if ( ! in_array( get_post_type( $post_id ), $allowed_types, true ) ) {
            wp_send_json_error();
        }

        $views = intval( get_post_meta( $post_id, 'va_views', true ) ?: 0 );
        update_post_meta( $post_id, 'va_views', $views + 1 );

        $gps_payload = null;
        if ( isset( $_POST['gps_lat'], $_POST['gps_lng'] ) ) {
            $gps_lat = floatval( wp_unslash( $_POST['gps_lat'] ) );
            $gps_lng = floatval( wp_unslash( $_POST['gps_lng'] ) );
            $gps_acc = isset( $_POST['gps_accuracy'] ) ? floatval( wp_unslash( $_POST['gps_accuracy'] ) ) : 0.0;

            if ( $gps_lat >= -90 && $gps_lat <= 90 && $gps_lng >= -180 && $gps_lng <= 180 ) {
                $gps_payload = [
                    'lat'      => $gps_lat,
                    'lng'      => $gps_lng,
                    'accuracy' => max( 0.0, $gps_acc ),
                ];
            }
        }

        if ( function_exists( 'va_record_view_geo' ) ) {
            va_record_view_geo( $post_id, $gps_payload );
        }
        $display_views = function_exists( 'va_display_views' ) ? va_display_views( $post_id ) : ( $views + 1 );
        wp_send_json_success( [
            'views'         => $views + 1,
            'display_views' => $display_views,
        ] );
    }

    public static function get_view_geo_report(): void {
        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( $post_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés.' ] );
        }

        $user_id = get_current_user_id();
        if ( $user_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ], 403 );
        }

        $can_geo = function_exists( 'va_user_can_open_geo_report' )
            ? va_user_can_open_geo_report( $user_id, $post_id )
            : current_user_can( 'manage_options' );

        if ( ! $can_geo ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ], 403 );
        }

        if ( current_user_can( 'manage_options' ) && ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ], 403 );
        }

        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if ( ! wp_verify_nonce( $nonce, 'va_view_geo_report_' . $post_id ) ) {
            wp_send_json_error( [ 'message' => 'Lejárt vagy érvénytelen token.' ], 403 );
        }

        $rows = function_exists( 'va_get_view_geo_breakdown' )
            ? va_get_view_geo_breakdown( $post_id, 300 )
            : [];
        $from_datetime = (string) get_post_field( 'post_date', $post_id );

        $total_views = 0;
        foreach ( $rows as $row ) {
            $total_views += (int) ( $row['views'] ?? 0 );
        }

        wp_send_json_success( [
            'post_id'      => $post_id,
            'post_title'   => get_the_title( $post_id ),
            'rows'         => $rows,
            'total_views'  => $total_views,
            'from_datetime'=> $from_datetime,
            'generated_at' => current_time( 'mysql' ),
        ] );
    }

    /* ── Hirdetések szűrő AJAX ─────────────────────────── */
    /* Skálázható megoldás: wp_va_listing_meta indexelt custom táblát használ
     * meta_query / EAV helyett – 3M hirdetésnél is gyors marad.
     * Transient cache: 5 perc, automatikusan törlődik új hirdetésnél.     */
    public static function filter_listings() {
        if ( self::is_rate_limited( 'filter_listings', 60, 60 ) ) {
            wp_send_json_error( [ 'message' => 'Túl sok kérés. Kérjük várjon egy percet.' ], 429 );
        }

        global $wpdb;

        $paged     = max( 1, intval( $_POST['paged']     ?? 1 ) );
        $category  = intval( $_POST['category']  ?? 0 );
        $county    = intval( $_POST['county']    ?? 0 );
        $condition = intval( $_POST['condition'] ?? 0 );
        $min_price = floatval( $_POST['min_price'] ?? 0 );
        $max_price = floatval( $_POST['max_price'] ?? 0 );
        $keyword   = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
        $sort      = sanitize_key( $_POST['sort'] ?? 'date' );

        // Jármű specifikus részletes szűrők
        $brand             = sanitize_text_field( wp_unslash( $_POST['brand'] ?? '' ) );
        $model             = sanitize_text_field( wp_unslash( $_POST['model'] ?? '' ) );
        $body_type         = sanitize_key( $_POST['body_type'] ?? '' );
        $fuel_type         = sanitize_key( $_POST['fuel_type'] ?? '' );
        $year_min          = max( 0, intval( $_POST['year_min'] ?? 0 ) );
        $year_max          = max( 0, intval( $_POST['year_max'] ?? 0 ) );
        $mileage_min       = max( 0, intval( $_POST['mileage_min'] ?? 0 ) );
        $mileage_max       = max( 0, intval( $_POST['mileage_max'] ?? 0 ) );
        $engine_min        = max( 0, intval( $_POST['engine_min'] ?? 0 ) );
        $engine_max        = max( 0, intval( $_POST['engine_max'] ?? 0 ) );
        $vehicle_condition = sanitize_key( $_POST['vehicle_condition'] ?? '' );
        $doors             = sanitize_key( $_POST['doors'] ?? '' );
        $passengers        = max( 0, intval( $_POST['passengers'] ?? 0 ) );
        $opt_automatic     = ! empty( $_POST['opt_automatic'] );
        $opt_awd           = ! empty( $_POST['opt_awd'] );
        $opt_service_book  = ! empty( $_POST['opt_service_book'] );
        $extra_keys_raw    = isset( $_POST['extras'] ) ? (array) $_POST['extras'] : [];
        $valid_extra_keys  = class_exists( 'VA_Vehicle_Catalog' ) ? array_keys( VA_Vehicle_Catalog::get_extras_options() ) : [];
        $extras            = array_values( array_intersect( array_map( 'sanitize_key', $extra_keys_raw ), $valid_extra_keys ) );
        $allowed_post_types = [ 'va_listing' ];
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            $allowed_post_types[] = 'va_auction';
        }

        $post_type = in_array( sanitize_key( $_POST['post_type'] ?? '' ), $allowed_post_types, true )
                     ? sanitize_key( $_POST['post_type'] )
                     : 'va_listing';
        $posted_per_page = intval( $_POST['per_page'] ?? 0 );
        $per_page = in_array( $posted_per_page, [ 25, 50, 100 ], true )
            ? $posted_per_page
            : intval( get_option( 'va_listings_per_page', 20 ) );
        $offset   = ( $paged - 1 ) * $per_page;

        // ── Transient cache kulcs ─────────────────────────
        $cache_key = 'va_fl_' . md5( serialize( compact(
            'paged','category','county','condition','min_price','max_price','keyword','sort','post_type','per_page',
            'brand','model','body_type','fuel_type','year_min','year_max','mileage_min','mileage_max',
            'engine_min','engine_max','vehicle_condition','doors','passengers','opt_automatic','opt_awd','opt_service_book','extras'
        ) ) );
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) {
            wp_send_json_success( $cached );
        }

        $lm    = $wpdb->prefix . 'va_listing_meta';
        $posts = $wpdb->posts;

        // ── WHERE feltételek összerakása ──────────────────
        $where  = [ "p.post_type = %s", "p.post_status = 'publish'" ];
        $params = [ $post_type ];

        if ( $category  ) { $where[] = 'lm.category_id  = %d'; $params[] = $category;  }
        if ( $county    ) { $where[] = 'lm.county_id    = %d'; $params[] = $county;    }
        if ( $condition ) { $where[] = 'lm.condition_id = %d'; $params[] = $condition; }
        if ( $min_price > 1 ) { $where[] = 'lm.price >= %f'; $params[] = $min_price; }
        if ( $max_price > 0 ) { $where[] = 'lm.price <= %f'; $params[] = $max_price; }

        if ( $keyword !== '' ) {
            $like     = '%' . $wpdb->esc_like( $keyword ) . '%';
            $where[]  = 'p.post_title LIKE %s';
            $params[] = $like;
        }

        $meta_joins = [];

        if ( $brand !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_brand ON (pm_brand.post_id = p.ID AND pm_brand.meta_key = 'va_brand')";
            $where[] = 'pm_brand.meta_value = %s';
            $params[] = $brand;
        }

        if ( $model !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_model ON (pm_model.post_id = p.ID AND pm_model.meta_key = 'va_model')";
            $where[] = 'pm_model.meta_value = %s';
            $params[] = $model;
        }

        if ( $body_type !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_body ON (pm_body.post_id = p.ID AND pm_body.meta_key = 'va_body_type')";
            $where[] = 'pm_body.meta_value = %s';
            $params[] = $body_type;
        }

        if ( $fuel_type !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_fuel ON (pm_fuel.post_id = p.ID AND pm_fuel.meta_key = 'va_fuel_type')";
            $where[] = 'pm_fuel.meta_value = %s';
            $params[] = $fuel_type;
        }

        if ( $year_min > 0 || $year_max > 0 ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_year ON (pm_year.post_id = p.ID AND pm_year.meta_key = 'va_year')";
            if ( $year_min > 0 ) { $where[] = 'CAST(pm_year.meta_value AS UNSIGNED) >= %d'; $params[] = $year_min; }
            if ( $year_max > 0 ) { $where[] = 'CAST(pm_year.meta_value AS UNSIGNED) <= %d'; $params[] = $year_max; }
        }

        if ( $mileage_min > 0 || $mileage_max > 0 ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_mileage ON (pm_mileage.post_id = p.ID AND pm_mileage.meta_key = 'va_mileage')";
            if ( $mileage_min > 0 ) { $where[] = 'CAST(pm_mileage.meta_value AS UNSIGNED) >= %d'; $params[] = $mileage_min; }
            if ( $mileage_max > 0 ) { $where[] = 'CAST(pm_mileage.meta_value AS UNSIGNED) <= %d'; $params[] = $mileage_max; }
        }

        if ( $engine_min > 0 || $engine_max > 0 ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_engine ON (pm_engine.post_id = p.ID AND pm_engine.meta_key = 'va_engine_size')";
            if ( $engine_min > 0 ) { $where[] = 'CAST(pm_engine.meta_value AS UNSIGNED) >= %d'; $params[] = $engine_min; }
            if ( $engine_max > 0 ) { $where[] = 'CAST(pm_engine.meta_value AS UNSIGNED) <= %d'; $params[] = $engine_max; }
        }

        if ( $vehicle_condition !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_vcond ON (pm_vcond.post_id = p.ID AND pm_vcond.meta_key = 'va_vehicle_condition')";
            $where[] = 'pm_vcond.meta_value = %s';
            $params[] = $vehicle_condition;
        }

        if ( $doors !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_doors ON (pm_doors.post_id = p.ID AND pm_doors.meta_key = 'va_doors')";
            if ( $doors === '6' ) {
                $where[] = 'CAST(pm_doors.meta_value AS UNSIGNED) >= 6';
            } else {
                $where[] = 'pm_doors.meta_value = %s';
                $params[] = $doors;
            }
        }

        if ( $passengers > 0 ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_pass ON (pm_pass.post_id = p.ID AND pm_pass.meta_key = 'va_passengers')";
            $where[] = 'CAST(pm_pass.meta_value AS UNSIGNED) = %d';
            $params[] = $passengers;
        }

        if ( $opt_automatic ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_trans ON (pm_trans.post_id = p.ID AND pm_trans.meta_key = 'va_transmission')";
            $where[] = "(pm_trans.meta_value = 'automatic' OR pm_trans.meta_value = 'semi_auto' OR pm_trans.meta_value = 'cvt')";
        }

        if ( $opt_awd ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_drive ON (pm_drive.post_id = p.ID AND pm_drive.meta_key = 'va_drive')";
            $where[] = "(pm_drive.meta_value = 'osszkerek_kapc' OR pm_drive.meta_value = 'osszkerek_allando')";
        }

        if ( $opt_service_book ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_srv ON (pm_srv.post_id = p.ID AND pm_srv.meta_key = 'va_service_book')";
            $where[] = "pm_srv.meta_value = '1'";
        }

        if ( ! empty( $extras ) ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_extras ON (pm_extras.post_id = p.ID AND pm_extras.meta_key = 'va_extras')";
            foreach ( $extras as $ex ) {
                $where[] = 'pm_extras.meta_value LIKE %s';
                $params[] = '%"' . $wpdb->esc_like( $ex ) . '"%';
            }
        }

        $where_sql = 'WHERE ' . implode( ' AND ', $where );
        $meta_join_sql = implode( "\n", array_values( array_unique( $meta_joins ) ) );

        // ── Boost rendezés konfig ─────────────────────────
        $boost_join         = '';
        $boost_order_prefix = '';
        if ( class_exists( 'VA_User_Roles' ) ) {
            $global_cfg = VA_User_Roles::get_all_plan_configs()['_global'] ?? [];
            if ( ! empty( $global_cfg['boost_enabled'] ) ) {
                $window_days        = (int) ( $global_cfg['boost_badge_window'] ?? 7 );
                $boost_cutoff       = time() - $window_days * DAY_IN_SECONDS;
                $boost_join         = "LEFT JOIN {$wpdb->postmeta} AS va_bst
                                        ON ( va_bst.post_id = p.ID AND va_bst.meta_key = 'va_boost_time' )";
                $boost_order_prefix = "CASE WHEN CAST( va_bst.meta_value AS UNSIGNED ) > {$boost_cutoff} THEN 1 ELSE 0 END DESC, ";
            }
        }

        // ── Rendezés ─────────────────────────────────────
        $is_price_sort = in_array( $sort, [ 'price_asc', 'price_desc' ], true );
        $order_prefix  = $is_price_sort ? '' : $boost_order_prefix;

        $order_sql = $order_prefix . match ( $sort ) {
            // Ár rendezésnél legyen tiszta ár szerinti sorrend
            'price_asc'  => 'lm.price ASC,  p.post_date DESC',
            'price_desc' => 'lm.price DESC, p.post_date DESC',
            'views'      => 'lm.featured DESC, lm.views DESC, p.post_date DESC',
            default      => 'lm.featured DESC, p.post_date DESC',
        };

        // ── Összesített szám (lapozáshoz) ─────────────────
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$posts} p
             LEFT JOIN {$lm} lm ON lm.post_id = p.ID
             {$meta_join_sql}
             {$boost_join}
             {$where_sql}",
            ...$params
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // ── ID lista – csak az aktuális lap ──────────────
        $id_sql = $wpdb->prepare(
            "SELECT p.ID FROM {$posts} p
             LEFT JOIN {$lm} lm ON lm.post_id = p.ID
             {$meta_join_sql}
             {$boost_join}
             {$where_sql}
             ORDER BY {$order_sql}
             LIMIT %d OFFSET %d",
            ...array_merge( $params, [ $per_page, $offset ] )
        );
        $ids = $wpdb->get_col( $id_sql );

        // ── WP_Query az ID listára – csak rendereléshez ──
        ob_start();
        if ( ! empty( $ids ) ) {
            $query = new WP_Query([
                'post_type'           => $post_type,
                'post_status'         => 'publish',
                'post__in'            => array_map( 'intval', $ids ),
                'orderby'             => 'post__in',
                'posts_per_page'      => $per_page,
                'no_found_rows'       => true,   // nem kell count – már megvan
                'ignore_sticky_posts' => true,
            ]);
            while ( $query->have_posts() ) {
                $query->the_post();
                va_template( 'listing/card', [ 'post' => get_post() ] );
            }
            wp_reset_postdata();
        } else {
            echo '<p class="va-no-results">Nincs találat a keresési feltételekre.</p>';
        }
        $html = ob_get_clean();

        $result = [
            'html'         => $html,
            'total'        => $total,
            'max_pages'    => $per_page > 0 ? (int) ceil( $total / $per_page ) : 1,
            'current_page' => $paged,
        ];

        // Cache 5 percre – törlődik ha új hirdetés/módosítás (save_post hook)
        set_transient( $cache_key, $result, 5 * MINUTE_IN_SECONDS );

        wp_send_json_success( $result );
    }

    /* ── Filter cache törlése ──────────────────────────── */
    public static function flush_filter_cache(): void {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_va_fl_%' OR option_name LIKE '_transient_timeout_va_fl_%'" );
    }

    /* ── Élő keresés (header dropdown) ─────────────────── */
    public static function live_search() {
        if ( self::is_rate_limited( 'live_search', 60, 60 ) ) {
            wp_send_json_error( [ 'message' => 'Túl sok kérés. Kérjük várjon egy percet.' ], 429 );
        }

        $q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
        if ( strlen( $q ) < 2 ) {
            wp_send_json_success( [] );
        }

        $results = [];

        // Kategória találatok
        $cats = get_terms([
            'taxonomy'   => 'va_category',
            'hide_empty' => false,
            'search'     => $q,
            'number'     => 3,
        ]);
        if ( ! is_wp_error( $cats ) ) {
            $search_page = get_page_by_path( 'va-hirdetes-kereses' );
            $search_url  = $search_page ? get_permalink( $search_page ) : home_url( '/va-hirdetes-kereses/' );
            foreach ( $cats as $cat ) {
                $results[] = [
                    'id'    => $cat->term_id,
                    'title' => $cat->name,
                    'url'   => add_query_arg( 'cat', $cat->term_id, $search_url ),
                    'price' => $cat->count . ' hirdetés',
                    'thumb' => '',
                    'type'  => 'category',
                ];
            }
        }

        // Hirdetés (+ opcionálisan aukció) találatok
        $search_post_types = [ 'va_listing' ];
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            $search_post_types[] = 'va_auction';
        }

        $query = new WP_Query([
            'post_type'      => $search_post_types,
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            'no_found_rows'  => true,
            's'              => $q,
        ]);

        foreach ( $query->posts as $post ) {
            $price     = get_post_meta( $post->ID, 'va_price', true );
            $thumb_id  = get_post_thumbnail_id( $post->ID );
            $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
            $results[] = [
                'id'    => $post->ID,
                'title' => get_the_title( $post ),
                'url'   => get_permalink( $post ),
                'price' => $price ? number_format( (float) $price, 0, ',', ' ' ) . ' Ft' : '',
                'thumb' => $thumb_url,
                'type'  => $post->post_type,
            ];
        }

        wp_reset_postdata();

        // Felhasználó találatok – csak bejelentkezett felhasználóknak
        if ( is_user_logged_in() ) {
            $users = get_users([
                'search'         => '*' . $q . '*',
                'search_columns' => [ 'user_login', 'display_name' ],
                'number'         => 3,
                'fields'         => [ 'ID', 'display_name', 'user_login' ],
            ]);
            $search_page_for_user = get_page_by_path( 'va-hirdetes-kereses' );
            $search_url_for_user  = $search_page_for_user ? get_permalink( $search_page_for_user ) : home_url( '/va-hirdetes-kereses/' );
            foreach ( $users as $u ) {
                $avatar  = get_avatar_url( $u->ID, [ 'size' => 80 ] );
                $results[] = [
                    'id'    => $u->ID,
                    'title' => $u->display_name,
                    'url'   => add_query_arg( 'author_id', $u->ID, $search_url_for_user ),
                    'price' => '@' . $u->user_login,
                    'thumb' => $avatar,
                    'type'  => 'user',
                ];
            }
        }

        wp_send_json_success( $results );
    }

    /* ── Base64 kép feltöltése médiatárba ───────────────────── */
    public static function upload_editor_image(): void {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }
        check_ajax_referer( 'va_upload_editor_image', 'nonce' );

        $user_id  = get_current_user_id();
        $post_id  = absint( $_POST['post_id'] ?? 0 );

        // Max 2 editor kép / hirdetés / felhasználó limitálása szerver oldalon
        if ( $post_id > 0 ) {
            $existing = get_posts( [
                'post_type'      => 'attachment',
                'post_parent'    => $post_id,
                'post_status'    => 'inherit',
                'posts_per_page' => -1,
                'no_found_rows'  => true,
                'meta_key'       => '_va_editor_img',
                'meta_value'     => '1',
                'author'         => $user_id,
                'fields'         => 'ids',
            ] );
            if ( count( $existing ) >= 2 ) {
                wp_send_json_error( [ 'message' => 'Maximum 2 kép engedélyezett a leírásban.' ] );
            }
        }

        $data_url = wp_unslash( $_POST['data_url'] ?? '' );
        if ( ! preg_match( '/^data:(image\/(jpeg|png|webp|gif));base64,(.+)$/s', $data_url, $m ) ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen képadat.' ] );
        }

        $mime      = $m[1];
        $ext_map   = [ 'jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif' ];
        $ext       = $ext_map[ $m[2] ] ?? 'jpg';
        $img_data  = base64_decode( $m[3] );

        if ( ! $img_data || strlen( $img_data ) > 10 * 1024 * 1024 ) {
            wp_send_json_error( [ 'message' => 'Túl nagy kép (max 10 MB).' ] );
        }

        // User-specifikus mappa: /va-users/{user_id}/listings/{post_id}/editor/
        // (ha post_id=0: /va-users/{user_id}/editor/)
        $va_editor_dir_filter = static function( $dirs ) use ( $user_id, $post_id ) {
            if ( $post_id > 0 ) {
                $dirs['subdir'] = '/va-users/' . $user_id . '/listings/' . $post_id . '/editor';
            } else {
                $dirs['subdir'] = '/va-users/' . $user_id . '/editor';
            }
            $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
            $dirs['url']  = $dirs['baseurl'] . $dirs['subdir'];
            return $dirs;
        };
        add_filter( 'upload_dir', $va_editor_dir_filter );

        $upload = wp_upload_bits( 'editor-img-' . time() . '.' . $ext, null, $img_data );

        remove_filter( 'upload_dir', $va_editor_dir_filter );

        if ( $upload['error'] ) {
            wp_send_json_error( [ 'message' => $upload['error'] ] );
        }

        // Tömörítés + átméretezés
        self::compress_image( $upload['file'] );

        $attachment_id = wp_insert_attachment( [
            'post_mime_type' => $mime,
            'post_title'     => sanitize_file_name( basename( $upload['file'] ) ),
            'post_status'    => 'inherit',
            'post_author'    => $user_id,
            'post_parent'    => $post_id > 0 ? $post_id : 0,
        ], $upload['file'] );

        // Jelöljük meg hogy editor kép → törléskor tudjuk tisztítani
        update_post_meta( $attachment_id, '_va_editor_img', '1' );
        update_post_meta( $attachment_id, '_va_editor_img_owner', $user_id );

        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );

        wp_send_json_success( [ 'url' => $upload['url'], 'attachment_id' => $attachment_id ] );
    }

    /* ── Hirdetés törlésekor editor képek törlése ───────────── */
    public static function delete_editor_images_on_listing_delete( int $post_id ): void {
        if ( get_post_type( $post_id ) !== 'va_listing' ) return;

        // Editor képek törlése (_va_editor_img meta alapján)
        $editor_imgs = get_posts( [
            'post_type'      => 'attachment',
            'post_parent'    => $post_id,
            'post_status'    => 'inherit',
            'posts_per_page' => 100,
            'no_found_rows'  => true,
            'meta_key'       => '_va_editor_img',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ] );
        foreach ( $editor_imgs as $att_id ) {
            wp_delete_attachment( $att_id, true );
        }

        // Galéria képek törlése (va_gallery_ids meta alapján)
        $gallery_str = get_post_meta( $post_id, 'va_gallery_ids', true );
        if ( $gallery_str ) {
            $gallery_ids = array_filter( array_map( 'intval', explode( ',', $gallery_str ) ) );
            foreach ( $gallery_ids as $att_id ) {
                wp_delete_attachment( $att_id, true );
            }
        }
        // Kiemelt kép törlése
        $thumb_id = (int) get_post_thumbnail_id( $post_id );
        if ( $thumb_id ) {
            wp_delete_attachment( $thumb_id, true );
        }
    }

    /* ── Hirdetés frissítése (lista tetejére tol) ───────────── */
    public static function refresh_listing(): void {
        check_ajax_referer( 'va_user_nonce', 'nonce' );
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }
        $post_id = absint( $_POST['post_id'] ?? 0 );
        $post    = get_post( $post_id );
        if ( ! $post || (int) $post->post_author !== $user_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés.' ] );
        }
        $now = current_time( 'mysql' );
        wp_update_post( [
            'ID'                => $post_id,
            'post_date'         => $now,
            'post_date_gmt'     => get_gmt_from_date( $now ),
            'post_modified'     => $now,
            'post_modified_gmt' => get_gmt_from_date( $now ),
        ] );
        update_post_meta( $post_id, 'va_active_since', current_time( 'timestamp' ) );
        wp_send_json_success( [ 'message' => 'Hirdetés frissítve.' ] );
    }

    /* ── Tömeges hirdetés-kezelés ───────────────────────────── */
    public static function bulk_listings(): void {
        check_ajax_referer( 'va_user_nonce', 'nonce' );
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }
        $action      = sanitize_key( $_POST['bulk_action'] ?? '' );
        $raw_ids     = $_POST['listing_ids'] ?? [];
        if ( ! is_array( $raw_ids ) || empty( $raw_ids ) ) {
            wp_send_json_error( [ 'message' => 'Nincs kijelölt hirdetés.' ] );
        }
        $listing_ids = array_map( 'absint', $raw_ids );
        $new_price   = isset( $_POST['new_price'] ) ? floatval( $_POST['new_price'] ) : null;
        $updated     = 0;

        foreach ( $listing_ids as $pid ) {
            $post = get_post( $pid );
            if ( ! $post || (int) $post->post_author !== $user_id ) {
                continue;
            }
            if ( $action === 'delete' ) {
                wp_delete_post( $pid, true );
            } elseif ( $action === 'suspend' ) {
                wp_update_post( [ 'ID' => $pid, 'post_status' => 'private' ] );
                update_post_meta( $pid, 'va_is_suspended', '1' );
                update_post_meta( $pid, 'va_suspended_at', current_time( 'timestamp' ) );
            } elseif ( $action === 'activate' ) {
                wp_update_post( [ 'ID' => $pid, 'post_status' => 'publish' ] );
                delete_post_meta( $pid, 'va_is_suspended' );
                delete_post_meta( $pid, 'va_suspended_at' );
            } elseif ( $action === 'price_change' && $new_price !== null && $new_price >= 0 ) {
                update_post_meta( $pid, 'va_price', $new_price );
                if ( function_exists( 'va_sync_listing_meta' ) ) {
                    va_sync_listing_meta( $pid );
                }
            } else {
                continue;
            }
            $updated++;
        }
        wp_send_json_success( [ 'updated' => $updated, 'action' => $action ] );
    }

    /* ── Akciós ár beállítása ────────────────────────────────── */
    public static function set_sale_price(): void {
        check_ajax_referer( 'va_user_nonce', 'nonce' );
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }
        $post_id    = absint( $_POST['post_id'] ?? 0 );
        $post       = get_post( $post_id );
        if ( ! $post || (int) $post->post_author !== $user_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés.' ] );
        }
        $normal_price = isset( $_POST['normal_price'] ) ? floatval( $_POST['normal_price'] ) : null;
        if ( $normal_price !== null && $normal_price >= 0 ) {
            update_post_meta( $post_id, 'va_price', $normal_price );
        }
        $sale_price = floatval( $_POST['sale_price'] ?? 0 );
        $sale_end   = sanitize_text_field( $_POST['sale_end'] ?? '' );
        if ( $sale_price > 0 ) {
            update_post_meta( $post_id, 'va_sale_price', $sale_price );
            if ( $sale_end ) {
                update_post_meta( $post_id, 'va_sale_price_end', $sale_end );
            } else {
                delete_post_meta( $post_id, 'va_sale_price_end' );
            }
        } else {
            delete_post_meta( $post_id, 'va_sale_price' );
            delete_post_meta( $post_id, 'va_sale_price_end' );
        }
        if ( function_exists( 'va_sync_listing_meta' ) ) {
            va_sync_listing_meta( $post_id );
        }
        wp_send_json_success( [ 'message' => 'Akciós ár mentve.' ] );
    }
}
