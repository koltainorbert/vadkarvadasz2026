<?php
/**
 * AJAX kezelők: hirdetés feladás, watchlist, megtekintés számláló
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Ajax {

    private static function normalize_learning_value( string $value ): string {
        $value = trim( wp_strip_all_tags( $value ) );
        if ( $value === '' ) {
            return '';
        }

        $value = preg_replace( '/\s+/u', ' ', $value );
        $norm  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );

        if ( function_exists( 'remove_accents' ) ) {
            $norm = remove_accents( $norm );
        }

        $norm = preg_replace( '/[^\p{L}\p{N}\s\.\-\/]+/u', '', $norm );
        $norm = preg_replace( '/\s+/u', ' ', $norm );
        return trim( (string) $norm );
    }

    private static function get_learning_category_slug( int $category_id ): string {
        if ( $category_id <= 0 ) {
            return '';
        }

        $term = get_term( $category_id, 'va_category' );
        if ( ! $term || is_wp_error( $term ) ) {
            return '';
        }

        return sanitize_title( (string) $term->slug );
    }

    private static function learning_table_exists(): bool {
        static $exists = null;
        if ( $exists !== null ) {
            return $exists;
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'va_learning_terms';
        $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
        return $exists;
    }

    private static function save_learning_term( string $term_type, string $term_value, string $category_slug = '' ): void {
        $term_type    = sanitize_key( $term_type );
        $term_value   = trim( sanitize_text_field( $term_value ) );
        $category_slug = sanitize_title( $category_slug );

        if ( $term_value === '' || strlen( $term_value ) < 2 || strlen( $term_value ) > 190 ) {
            return;
        }

        if ( ! in_array( $term_type, [ 'brand', 'model', 'caliber', 'location', 'street' ], true ) ) {
            return;
        }

        if ( ! self::learning_table_exists() ) {
            return;
        }

        $term_norm = self::normalize_learning_value( $term_value );
        if ( $term_norm === '' ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'va_learning_terms';

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table} (term_type, term_value, term_value_norm, category_slug, usage_count, created_at, updated_at)
                 VALUES (%s, %s, %s, %s, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    term_value = VALUES(term_value),
                    usage_count = usage_count + 1,
                    updated_at = NOW()",
                $term_type,
                $term_value,
                $term_norm,
                $category_slug
            )
        );
    }

    private static function learn_listing_terms( int $category_id, array $values ): void {
        $category_slug = self::get_learning_category_slug( $category_id );

        self::save_learning_term( 'title', (string) ( $values['title'] ?? '' ), '' );
        self::save_learning_term( 'brand', (string) ( $values['brand'] ?? '' ), $category_slug );
        self::save_learning_term( 'model', (string) ( $values['model'] ?? '' ), $category_slug );
        self::save_learning_term( 'caliber', (string) ( $values['caliber'] ?? '' ), $category_slug );
        self::save_learning_term( 'location', (string) ( $values['location'] ?? '' ), '' );
        self::save_learning_term( 'street', (string) ( $values['street'] ?? '' ), '' );
    }

    public static function get_learned_terms_map( string $term_type, int $limit = 500 ): array {
        $term_type = sanitize_key( $term_type );
        if ( ! in_array( $term_type, [ 'brand', 'model', 'caliber', 'location', 'street' ], true ) ) {
            return [ '__global' => [] ];
        }

        if ( ! self::learning_table_exists() ) {
            return [ '__global' => [] ];
        }

        $limit = max( 50, min( 2000, absint( $limit ) ) );

        global $wpdb;
        $table = $wpdb->prefix . 'va_learning_terms';
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT term_value, category_slug
                 FROM {$table}
                 WHERE term_type = %s
                 ORDER BY usage_count DESC, updated_at DESC
                 LIMIT %d",
                $term_type,
                $limit
            ),
            ARRAY_A
        );

        if ( ! is_array( $rows ) || empty( $rows ) ) {
            return [ '__global' => [] ];
        }

        $map = [ '__global' => [] ];
        $seen = [];

        foreach ( $rows as $row ) {
            $value = trim( (string) ( $row['term_value'] ?? '' ) );
            if ( $value === '' ) {
                continue;
            }

            $slug = sanitize_title( (string) ( $row['category_slug'] ?? '' ) );
            $bucket = $slug !== '' ? $slug : '__global';
            if ( ! isset( $map[ $bucket ] ) ) {
                $map[ $bucket ] = [];
            }
            if ( ! isset( $seen[ $bucket ] ) ) {
                $seen[ $bucket ] = [];
            }

            $norm = self::normalize_learning_value( $value );
            if ( $norm === '' || isset( $seen[ $bucket ][ $norm ] ) ) {
                continue;
            }

            $seen[ $bucket ][ $norm ] = true;
            $map[ $bucket ][] = $value;
        }

        return $map;
    }

    private static function get_category_required_rules(): array {
        return [
            'golyos-puska'      => [ 'label' => 'Golyós lőfegyver', 'required' => [ 'brand', 'model', 'rifle_type', 'rifle_caliber', 'rifle_barrel_length_cm', 'rifle_condition', 'rifle_optic_compatibility' ] ],
            'soretes-puska'     => [ 'label' => 'Sörétes lőfegyver', 'required' => [ 'brand', 'caliber' ] ],
            'vegyescsovu-puska' => [ 'label' => 'Vegyescsövű lőfegyver', 'required' => [ 'brand', 'model', 'mixed_type', 'mixed_barrel_count', 'mixed_rifle_caliber', 'mixed_shotgun_caliber', 'mixed_condition' ] ],
            'maroklofegyver'    => [ 'label' => 'Maroklőfegyver', 'required' => [ 'brand', 'model', 'caliber', 'marok_type', 'marok_condition', 'marok_action_type', 'marok_magazine_capacity', 'marok_license_required', 'marok_legal_category', 'marok_cip_marking', 'marok_transfer_license_only' ] ],
            'hatastalanitott'   => [ 'label' => 'Hatástalanított', 'required' => [ 'brand', 'model', 'hatastalanitott_weapon_type', 'hatastalanitott_is_deactivated', 'hatastalanitott_deactivation_type', 'hatastalanitott_certificate', 'hatastalanitott_mkh_cip', 'hatastalanitott_condition' ] ],
            'egyeb-fegyverek'   => [ 'label' => 'Egyéb fegyverek', 'required' => [ 'brand', 'other_weapon_kind' ] ],
            'loszer-tolteny'    => [ 'label' => 'Lőszer-Töltény', 'required' => [ 'brand', 'caliber', 'loszer_category', 'loszer_condition' ] ],
            'tavcsovek'         => [ 'label' => 'Távcsövek', 'required' => [ 'brand', 'model', 'optic_type', 'optic_objective' ] ],
            'ejjellato-tavcso'  => [ 'label' => 'Éjjellátó távcső', 'required' => [ 'brand', 'model', 'optic_zoom' ] ],
            'hokamerak'         => [ 'label' => 'Hőkamerák', 'required' => [ 'brand', 'model', 'optic_zoom', 'thermal_device_type', 'thermal_sensor_resolution', 'thermal_netd', 'thermal_refresh_hz' ] ],
            'vadkamera'         => [ 'label' => 'Vadkamera', 'required' => [ 'brand', 'model', 'vadcamera_camera_type', 'vadcamera_connectivity', 'vadcamera_trigger_speed', 'vadcamera_ir_type', 'vadcamera_night_range_m', 'vadcamera_video_resolution', 'vadcamera_ip_rating', 'vadcamera_sim_slot' ] ],
            'vadaszlampa'       => [ 'label' => 'Vadászlámpa', 'required' => [ 'brand', 'model' ] ],
            'fegyverlampa'      => [ 'label' => 'Fegyverlámpa', 'required' => [ 'brand', 'model' ] ],
            'vadaszkutya'       => [ 'label' => 'Vadászkutya', 'required' => [ 'dog_working_type', 'dog_breed', 'dog_gender', 'dog_birth_date', 'dog_hunting_specialization', 'dog_training_level' ] ],
            'vadasz-ruhazat'    => [ 'label' => 'Vadász ruházat', 'required' => [ 'brand', 'clothing_type', 'season', 'hunting_usage', 'waterproof_level', 'noise_level', 'material_tech', 'camo_pattern', 'clothing_size' ] ],
            'egyeb-ruhazat'     => [ 'label' => 'Egyéb ruházat', 'required' => [ 'brand', 'clothing_type', 'clothing_condition', 'clothing_gender', 'clothing_size' ] ],
            'cipo-bakancs'      => [ 'label' => 'Cipő, bakancs', 'required' => [ 'brand', 'shoe_main_type', 'shoe_eu_size', 'shoe_condition', 'shoe_boot_height' ] ],
            'csere'             => [ 'label' => 'Csere', 'required' => [ 'exchange_type', 'exchange_offer_category', 'exchange_brand', 'exchange_condition', 'exchange_estimated_value', 'exchange_extra_payment_direction', 'exchange_county' ] ],
            'konyv-folyoirat'   => [ 'label' => 'Könyv és folyóirat', 'required' => [ 'publication_type', 'publication_topics', 'publication_title', 'publication_author', 'publication_year', 'publication_language', 'publication_condition', 'publication_collector_value', 'publication_complete_volume' ] ],
            'disztargyak'       => [ 'label' => 'Dísztárgyak', 'required' => [ 'decor_type', 'decor_material', 'decor_style', 'decor_motif_tags', 'decor_production_type', 'decor_size_class', 'decor_condition' ] ],
            'disztargy'         => [ 'label' => 'Dísztárgyak', 'required' => [ 'decor_type', 'decor_material', 'decor_style', 'decor_motif_tags', 'decor_production_type', 'decor_size_class', 'decor_condition' ] ],
            'allas'             => [ 'label' => 'Állás', 'required' => [ 'job_location', 'job_type' ] ],
            'allas-hirdetes'    => [ 'label' => 'Állás', 'required' => [ 'job_location', 'job_type' ] ],
            'szolgaltatas'      => [ 'label' => 'Szolgáltatás', 'required' => [ 'service_type', 'service_provider_type', 'service_country', 'service_county', 'service_town', 'service_area', 'service_pricing_type' ] ],
            'szallas'           => [ 'label' => 'Szállás / Ingatlan', 'required' => [ 'accommodation_type', 'accommodation_county_text', 'accommodation_land_size_sqm', 'accommodation_building_size_sqm', 'accommodation_utilities', 'accommodation_accessibility', 'accommodation_hunting_features', 'accommodation_property_condition' ] ],
            'ingatlan'          => [ 'label' => 'Szállás / Ingatlan', 'required' => [ 'accommodation_type', 'accommodation_county_text', 'accommodation_land_size_sqm', 'accommodation_building_size_sqm', 'accommodation_utilities', 'accommodation_accessibility', 'accommodation_hunting_features', 'accommodation_property_condition' ] ],
            'ingatlan-szallas'  => [ 'label' => 'Szállás / Ingatlan', 'required' => [ 'accommodation_type', 'accommodation_county_text', 'accommodation_land_size_sqm', 'accommodation_building_size_sqm', 'accommodation_utilities', 'accommodation_accessibility', 'accommodation_hunting_features', 'accommodation_property_condition' ] ],
            'vadaszati-lehetoseg' => [ 'label' => 'Vadászati lehetőség', 'required' => [ 'hunt_type', 'hunt_game_species', 'hunt_country', 'hunt_county', 'hunt_price_type', 'hunt_price_min', 'hunt_methods', 'hunt_has_accommodation', 'hunt_guide_type', 'hunt_difficulty_level', 'hunt_season_start', 'hunt_season_end' ] ],
            'vadkarelharitas'   => [ 'label' => 'Vadkárelhárítás', 'required' => [ 'hunt_damage_main_type', 'hunt_game_species', 'hunt_country', 'hunt_county', 'hunt_land_type', 'hunt_response_time', 'hunt_availability_mode', 'hunt_pricing_model', 'hunt_contract_type', 'hunt_methods' ] ],
            'vadaszati-hagyatek'=> [ 'label' => 'Vadászati hagyaték', 'required' => [ 'estate_main_type' ] ],
            'egyeb'             => [ 'label' => 'Egyéb kategória', 'required' => [ 'other_category' ] ],
            'vadasz-felszereles'=> [ 'label' => 'Vadász felszerelés', 'required' => [ 'brand', 'equipment_type', 'equipment_condition', 'equipment_hunting_usage' ] ],
            'sportlovo-felsz'   => [ 'label' => 'Sportlövő felszerelés', 'required' => [ 'gear_type', 'shooting_discipline', 'firearm_compatibility', 'sport_size', 'modular', 'competition_level', 'sport_condition' ] ],
            'taskak'            => [ 'label' => 'Táskák', 'required' => [ 'bag_type', 'volume_l', 'bag_material', 'bag_weather_resistance', 'bag_condition' ] ],
            'taska'             => [ 'label' => 'Táskák', 'required' => [ 'bag_type', 'volume_l', 'bag_material', 'bag_weather_resistance', 'bag_condition' ] ],
            'hatizsakok'        => [ 'label' => 'Táskák', 'required' => [ 'bag_type', 'volume_l', 'bag_material', 'bag_weather_resistance', 'bag_condition' ] ],
            'fegyvertokok'      => [ 'label' => 'Táskák', 'required' => [ 'bag_type', 'volume_l', 'bag_material', 'bag_weather_resistance', 'bag_condition' ] ],
            'kurtok-sipok'      => [ 'label' => 'Kürtök és sípok', 'required' => [ 'call_type', 'call_target_species', 'call_operation_mode', 'call_material_type', 'call_volume_level', 'call_style' ] ],
            'kesek'                => [ 'label' => 'Kések', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
            'vadaszkes-vadasztor'  => [ 'label' => 'Vadászkés, vadásztőr', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
            'taktikai-kes-taktikai-tor' => [ 'label' => 'Taktikai kés', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
            'konyhakes'            => [ 'label' => 'Konyhakés', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
            'svajci-bicska'        => [ 'label' => 'Svájci bicska', 'required' => [ 'knife_type', 'knife_condition', 'knife_steel_type' ] ],
            'trofea-aletet'        => [ 'label' => 'Trófeaalátét', 'required' => [ 'trophy_type', 'trophy_material_type', 'trophy_style', 'trophy_mounting_type', 'trophy_attachment_type', 'trophy_height_cm', 'trophy_width_cm', 'trophy_thickness_cm', 'trophy_compatible_species' ] ],
        ];
    }

    private static function collect_call_fields_from_request(): array {
        $call_type = sanitize_key( wp_unslash( $_POST['call_type'] ?? '' ) );
        $call_target_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['call_target_species'] ?? [] ) ) ) );
        $call_sound_type = sanitize_key( wp_unslash( $_POST['call_sound_type'] ?? '' ) );
        $call_material_type = sanitize_key( wp_unslash( $_POST['call_material_type'] ?? '' ) );
        $call_operation_mode = sanitize_key( wp_unslash( $_POST['call_operation_mode'] ?? '' ) );
        $call_electronic_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['call_electronic_features'] ?? [] ) ) ) );
        $call_volume_level = sanitize_key( wp_unslash( $_POST['call_volume_level'] ?? '' ) );
        $call_weather_resistance = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['call_weather_resistance'] ?? [] ) ) ) );
        $call_size_class = sanitize_key( wp_unslash( $_POST['call_size_class'] ?? '' ) );
        $call_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['call_usage'] ?? [] ) ) ) );
        $call_sound_generation = sanitize_key( wp_unslash( $_POST['call_sound_generation'] ?? '' ) );
        $call_style = sanitize_key( wp_unslash( $_POST['call_style'] ?? '' ) );
        $call_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['call_accessories'] ?? [] ) ) ) );
        $call_condition = sanitize_key( wp_unslash( $_POST['call_condition'] ?? '' ) );
        $call_sample_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['call_sample_count'] ?? '' ) );
        $call_range_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['call_range_m'] ?? '' ) );
        $call_runtime_hours = sanitize_text_field( wp_unslash( $_POST['call_runtime_hours'] ?? '' ) );
        $call_maker_name = sanitize_text_field( wp_unslash( $_POST['call_maker_name'] ?? '' ) );
        $call_unique_piece = sanitize_key( wp_unslash( $_POST['call_unique_piece'] ?? '' ) );
        $call_competition_standard = sanitize_text_field( wp_unslash( $_POST['call_competition_standard'] ?? '' ) );
        $call_tuning = sanitize_text_field( wp_unslash( $_POST['call_tuning'] ?? '' ) );
        $call_sound_sample_url = esc_url_raw( wp_unslash( $_POST['call_sound_sample_url'] ?? '' ) );
        $call_filter_is_electronic = ( in_array( $call_operation_mode, [ 'elektronikus', 'digitalis-hangmintas' ], true ) || $call_type === 'elektronikus-hivo' ) ? '1' : '0';
        $call_filter_waterproof = strpos( ',' . $call_weather_resistance . ',', ',vizallo,' ) !== false ? '1' : '0';

        return [
            'call_type' => $call_type,
            'call_target_species' => $call_target_species,
            'call_sound_type' => $call_sound_type,
            'call_material_type' => $call_material_type,
            'call_operation_mode' => $call_operation_mode,
            'call_electronic_features' => $call_electronic_features,
            'call_volume_level' => $call_volume_level,
            'call_weather_resistance' => $call_weather_resistance,
            'call_size_class' => $call_size_class,
            'call_usage' => $call_usage,
            'call_sound_generation' => $call_sound_generation,
            'call_style' => $call_style,
            'call_accessories' => $call_accessories,
            'call_condition' => $call_condition,
            'call_sample_count' => $call_sample_count,
            'call_range_m' => $call_range_m,
            'call_runtime_hours' => $call_runtime_hours,
            'call_maker_name' => $call_maker_name,
            'call_unique_piece' => $call_unique_piece,
            'call_competition_standard' => $call_competition_standard,
            'call_tuning' => $call_tuning,
            'call_sound_sample_url' => $call_sound_sample_url,
            'call_filter_type' => $call_type,
            'call_filter_target_species' => $call_target_species,
            'call_filter_operation_mode' => $call_operation_mode,
            'call_filter_material_type' => $call_material_type,
            'call_filter_volume_level' => $call_volume_level,
            'call_filter_style' => $call_style,
            'call_filter_waterproof' => $call_filter_waterproof,
            'call_filter_is_electronic' => $call_filter_is_electronic,
        ];
    }

    private static function validate_category_required_fields( int $category_id, array $values ): string {
        if ( $category_id <= 0 ) {
            return '';
        }

        $term = get_term( $category_id, 'va_category' );
        if ( ! $term || is_wp_error( $term ) ) {
            return '';
        }

        $slug = sanitize_title( (string) $term->slug );
        $rules = self::get_category_required_rules();
        if ( isset( $rules[ $slug ] ) ) {
            $rule = $rules[ $slug ];
        } elseif ( preg_match( '/taska|taskak|hatizsak|fegyvertok|puskatok|pisztolytaska|range-bag|molle/', $slug ) ) {
            $rule = [
                'label' => 'Táskák',
                'required' => [ 'bag_type', 'volume_l', 'bag_material', 'bag_weather_resistance', 'bag_condition' ],
            ];
        } else {
            return '';
        }

        $required = is_array( $rule['required'] ?? null ) ? $rule['required'] : [];
        if ( empty( $required ) ) {
            return '';
        }

        $values = array_map(
            static fn( $v ) => is_scalar( $v ) ? trim( (string) $v ) : '',
            $values
        );

        $labels = [
            'brand'   => 'Márka / gyártó',
            'model'   => 'Modell / típus',
            'caliber' => 'Kaliber',
            'rifle_type' => 'Típus',
            'rifle_caliber' => 'Golyós kaliber',
            'rifle_barrel_length_cm' => 'Csőhossz (cm)',
            'rifle_condition' => 'Állapot',
            'rifle_optic_compatibility' => 'Optika kompatibilitás',
            'mixed_type' => 'Vegyescsövű típus',
            'mixed_barrel_count' => 'Csövek száma',
            'mixed_rifle_caliber' => 'Golyós kaliber',
            'mixed_shotgun_caliber' => 'Sörétes kaliber',
            'mixed_condition' => 'Állapot',
            'other_weapon_kind' => 'Egyéb fegyverek kategória',
            'hatastalanitott_weapon_type' => 'Fegyver típusa',
            'hatastalanitott_is_deactivated' => 'Hatástalanított?',
            'hatastalanitott_deactivation_type' => 'Hatástalanítás típusa',
            'hatastalanitott_certificate' => 'Tanúsítvány van?',
            'hatastalanitott_mkh_cip' => 'MKH / CIP jelölés?',
            'hatastalanitott_condition' => 'Állapot',
            'loszer_category' => 'Kategória',
            'loszer_condition' => 'Állapot',
            'optic_type' => 'Távcső típusa',
            'optic_zoom' => 'Nagyítás',
            'optic_objective' => 'Objektív átmérő (mm)',
            'optic_magnification_min' => 'Nagyítás minimum',
            'optic_magnification_max' => 'Nagyítás maximum',
            'optic_magnification_fixed' => 'Fix nagyítás',
            'optic_tube_diameter' => 'Csőátmérő',
            'optic_exit_pupil' => 'Kilépő pupilla',
            'optic_field_of_view' => 'Látómező',
            'optic_eye_relief' => 'Eye relief',
            'optic_reticle_type' => 'Reticle típus',
            'optic_reticle_plane' => 'Reticle sík',
            'optic_illumination' => 'Megvilágítás',
            'optic_illumination_color' => 'Megvilágítás színe',
            'optic_click_value' => 'Klikk érték',
            'optic_parallax_adjustment' => 'Parallax állítás',
            'optic_turret_type' => 'Torony típus',
            'optic_body_material' => 'Test anyag',
            'optic_coating_type' => 'Bevonat típusa',
            'optic_waterproof_flags' => 'Vízállóság',
            'optic_prism_system' => 'Prizma rendszer',
            'optic_glass_type' => 'Üvegtípus',
            'optic_dot_size' => 'Pontméret',
            'optic_rail_compatibility' => 'Sín kompatibilitás',
            'optic_rangefinder_max_m' => 'Távmérő max',
            'optic_ballistic_calc' => 'Ballisztikai kalkulátor',
            'optic_spektiv_zoom_range' => 'Spektív zoom tartomány',
            'optic_eyepiece_type' => 'Okulár típus',
            'optic_weapon_compatibility' => 'Fegyver kompatibilitás',
            'scope_accessories_flags' => 'Tartozék jelölők',
            'optic_mount_type' => 'Szerelék típusa',
            'optic_shipping_methods' => 'Szállítás módja',
            'optic_recommended_use' => 'Ajánlott felhasználás',
            'optic_moderation_flags' => 'Moderációs jelzők',
            'thermal_device_type' => 'Hőkamera típus',
            'thermal_sensor_resolution' => 'Szenzor felbontás',
            'thermal_netd' => 'NETD érzékenység',
            'thermal_refresh_hz' => 'Frissítési frekvencia',
            'dog_age_months' => 'Kutya életkor (hónap)',
            'dog_working_type' => 'Vadászkutya típus',
            'dog_breed' => 'Kutya fajtája',
            'dog_gender' => 'Neme',
            'dog_birth_date' => 'Születési dátum',
            'dog_color' => 'Színe',
            'dog_purebred' => 'Fajtatisztaság',
            'dog_hunting_specialization' => 'Vadászati specializáció',
            'dog_training_level' => 'Kiképzés szintje',
            'job_location' => 'Munkavégzés települése',
            'job_type' => 'Foglalkoztatás típusa',
            'clothing_type' => 'Ruhatípus',
            'clothing_condition' => 'Állapot',
            'clothing_gender' => 'Nem',
            'clothing_size' => 'Ruhaméret',
            'season' => 'Időjárás / szezon',
            'hunting_usage' => 'Vadászati használat',
            'waterproof_level' => 'Vízállósági szint',
            'noise_level' => 'Hangtulajdonság',
            'material_tech' => 'Anyagtechnológia',
            'camo_pattern' => 'Szín / minta',
            'trophy_type' => 'Trófeaalátét típusa',
            'trophy_maker' => 'Készítő / márka',
            'trophy_custom_made' => 'Kézzel készített',
            'trophy_condition' => 'Állapot',
            'trophy_manufacture_year' => 'Gyártási év',
            'trophy_height_cm' => 'Magasság (cm)',
            'trophy_width_cm' => 'Szélesség (cm)',
            'trophy_thickness_cm' => 'Vastagság (cm)',
            'trophy_compatible_species' => 'Trófea kompatibilitás',
            'trophy_species' => 'Vadfaj',
            'trophy_mount_type' => 'Trófeaalátét típus',
            'trophy_mounting_type' => 'Rögzítés típusa',
            'trophy_attachment_type' => 'Trófea rögzítés',
            'trophy_style' => 'Forma / stílus',
            'trophy_fang_mount' => 'Agyarfoglalat kivitel',
            'trophy_material_type' => 'Alapanyag',
            'trophy_material' => 'Anyag',
            'trophy_surface_finish' => 'Felületkezelés',
            'trophy_finish' => 'Felületkezelés',
            'trophy_decoration_flags' => 'Díszítés',
            'trophy_weight_kg' => 'Súly (kg)',
            'trophy_usage_context' => 'Felhasználás',
            'trophy_accessories' => 'Tartozékok',
            'trophy_price_negotiable' => 'Ár: alku',
            'trophy_custom_order' => 'Egyedi rendelés',
            'trophy_shipping_methods' => 'Szállítás',
            'trophy_fragile_handling' => 'Törékeny kezelés',
            'trophy_required_media' => 'Kötelező képek',
            'trophy_lead_time' => 'Elkészítési idő',
            'trophy_orderable_sizes' => 'Rendelhető méret',
            'trophy_custom_engraving' => 'Egyedi gravírozás',
            'trophy_house_style' => 'Vadászház stílus illesztés',
            'trophy_moderation_flags' => 'Moderációs jelölők',
            'accommodation_type' => 'Szállás típusa',
            'accommodation_capacity' => 'Férőhely (fő)',
            'accommodation_hunting_nearby' => 'Vadászterület közelében?',
            'accommodation_hunt_available' => 'Szervezett vadászat elérhető?',
            'accommodation_county_text' => 'Megye',
            'accommodation_land_size_sqm' => 'Telek méret (m²)',
            'accommodation_building_size_sqm' => 'Lakóterület (m²)',
            'accommodation_utilities' => 'Közművek',
            'accommodation_accessibility' => 'Megközelíthetőség',
            'accommodation_hunting_features' => 'Vadászati kapcsolódás',
            'accommodation_property_condition' => 'Ingatlan állapot',
            'hunt_slot_from_hour' => 'Idősáv kezdete (óra)',
            'hunt_slot_to_hour' => 'Idősáv vége (óra)',
            'hunt_capacity' => 'Létszám / fő',
            'hunt_species_list' => 'Vadászható fajok listája',
            'hunt_lease_type' => 'Lesbérleti / hozzáférési forma',
            'hunt_type' => 'Vadászat típusa',
            'hunt_game_species' => 'Vadfaj',
            'hunt_country' => 'Ország',
            'hunt_county' => 'Megye',
            'hunt_area_name' => 'Vadászterület neve',
            'hunt_exact_area' => 'Pontos terület',
            'hunt_gps_lat' => 'GPS lat',
            'hunt_gps_lng' => 'GPS lng',
            'hunt_terrain_types' => 'Terület jellemzők',
            'hunt_season_start' => 'Kezdő dátum',
            'hunt_season_end' => 'Záró dátum',
            'hunt_time_type' => 'Időpont típus',
            'hunt_price_type' => 'Ár típusa',
            'hunt_price_min' => 'Ár minimum',
            'hunt_price_max' => 'Ár maximum',
            'hunt_price_includes' => 'Ár tartalmazza',
            'hunt_guide_type' => 'Kíséret típusa',
            'hunt_methods' => 'Vadászati módszer',
            'hunt_onsite_services' => 'Helyszíni szolgáltatások',
            'hunt_hunting_license_required' => 'Vadászjegy szükséges',
            'hunt_permit_required' => 'Engedély szükséges',
            'hunt_foreigners_allowed' => 'Külföldiek vadászhatnak',
            'hunt_min_age' => 'Minimum életkor',
            'hunt_safety_features' => 'Biztonság',
            'hunt_conditions' => 'Körülmények',
            'hunt_difficulty_level' => 'Nehézségi szint',
            'hunt_expected_trophy_quality' => 'Várható trófea minőség',
            'hunt_trophy_weight_age' => 'Súly / korosztály',
            'hunt_required_media' => 'Kötelező képek',
            'hunt_video_types' => 'Videó',
            'hunt_moderation_flags' => 'Moderációs jelölők',
            'hunt_damage_main_type' => 'Vadkár elhárítás fő típusa',
            'hunt_settlement' => 'Település',
            'hunt_land_type' => 'Terület típusa',
            'hunt_response_time' => 'Reakcióidő',
            'hunt_availability_mode' => 'Elérhetőség',
            'hunt_executor_type' => 'Végrehajtó',
            'hunt_executor_capacity' => 'Kapacitás',
            'hunt_tools' => 'Eszközök',
            'hunt_weapon_use_required' => 'Fegyverhasználat szükséges',
            'hunt_land_manager_permit' => 'Területgazdálkodói engedély',
            'hunt_hunting_company_coop' => 'Együttműködés vadásztársasággal',
            'hunt_liability_insurance' => 'Felelősségbiztosítás',
            'hunt_damage_nature' => 'Kár jellege',
            'hunt_pricing_model' => 'Árazás modell',
            'hunt_travel_fee' => 'Kiszállás díja',
            'hunt_contract_type' => 'Szerződés típusa',
            'hunt_site_conditions' => 'Helyszíni feltételek',
            'hunt_trophy_category' => 'Várható trófea kategória',
            'hunt_trophy_weight_limit' => 'Súlyhatár',
            'hunt_travel_assist' => 'Utazás segítés',
            'hunt_interpreter' => 'Tolmács',
            'hunt_group_size' => 'Csoport méret',
            'hunt_shot_limit' => 'Lövésszám limit',
            'hunt_real_time_slots' => 'Szabad helyek száma',
            'hunt_bookable_dates' => 'Foglalható időpontok',
            'vadcamera_camera_type' => 'Kamera típusa',
            'vadcamera_connectivity' => 'Adatkapcsolat',
            'vadcamera_trigger_speed' => 'Trigger idő',
            'vadcamera_ir_type' => 'Infra típusa',
            'vadcamera_night_range_m' => 'Éjszakai hatótáv (m)',
            'vadcamera_video_resolution' => 'Videó felbontás',
            'vadcamera_ip_rating' => 'IP védelem',
            'vadcamera_sim_slot' => 'SIM kártyás?',
            'shoe_main_type' => 'Típus',
            'shoe_eu_size' => 'EU méret',
            'shoe_condition' => 'Állapot',
            'shoe_boot_height' => 'Szármagasság',
            'estate_main_type' => 'Hagyaték fő típusa',
            'knife_type' => 'Kés típusa',
            'knife_condition' => 'Állapot',
            'knife_blade_length_mm' => 'Pengehossz (mm)',
            'knife_steel_type' => 'Acél',
            'service_type' => 'Szolgáltatás típusa',
            'service_provider_type' => 'Szolgáltató típusa',
            'service_provider_name' => 'Szolgáltató neve',
            'service_country' => 'Ország',
            'service_county' => 'Megye',
            'service_town' => 'Település',
            'service_area' => 'Kiszállási terület',
            'service_gps_lat' => 'GPS szélesség',
            'service_gps_lng' => 'GPS hosszúság',
            'service_pricing_type' => 'Ár típusa',
            'service_min_price' => 'Minimum ár',
            'service_travel_fee' => 'Kiszállási díj',
            'service_schedule' => 'Elérhetőség',
            'service_available_weekend' => 'Hétvégi elérhetőség',
            'service_available_24_7' => '0-24 elérhető',
            'service_seasonal' => 'Szezonális',
            'service_hunting_specialization' => 'Vadászati specializáció',
            'service_hunting_species' => 'Vadfajok',
            'service_weapon_categories' => 'Fegyver / optika',
            'service_equipment' => 'Felszereltség',
            'service_permits' => 'Engedélyek',
            'service_reference_rating' => 'Ügyfél értékelés',
            'service_completed_jobs' => 'Elvégzett munkák',
            'service_reference_notes' => 'Referencia megjegyzés',
            'service_media_required' => 'Média kötelező elemek',
            'service_media_video' => 'Videó',
            'service_verified_provider' => 'Ellenőrzött szolgáltató',
            'service_document_verified' => 'Dokumentum ellenőrzés',
            'service_phone_verified' => 'Telefonszám ellenőrzés',
            'service_business_verified' => 'Vállalkozás ellenőrzés',
            'service_sos_service' => 'SOS szolgáltatás',
            'service_urgent_repair' => 'Sürgős javítás',
            'service_full_prep' => 'Teljes preparálás',
            'service_night_work' => 'Éjszakai vállalás',
            'service_caliber_support' => 'Vállalt kaliberek',
            'service_moderation_flags' => 'Moderációs jelzők',
            'marok_type' => 'Maroklőfegyver típusa',
            'marok_condition' => 'Állapot',
            'marok_action_type' => 'Működési rendszer',
            'marok_magazine_capacity' => 'Tárkapacitás',
            'marok_license_required' => 'Engedélyköteles',
            'marok_legal_category' => 'Kategória',
            'marok_cip_marking' => 'MKH / CIP jelölés',
            'marok_transfer_license_only' => 'Csak engedéllyel átvehető',
            'exchange_type' => 'Csere típusa',
            'exchange_offer_category' => 'Fő kategória (mit kínál?)',
            'exchange_brand' => 'Márka',
            'exchange_condition' => 'Állapot',
            'exchange_estimated_value' => 'Becsült érték (Ft)',
            'exchange_extra_payment_direction' => 'Ráfizetés',
            'exchange_county' => 'Megye',
            'publication_type' => 'Típus',
            'publication_topics' => 'Témakör',
            'publication_title' => 'Cím',
            'publication_author' => 'Szerző',
            'publication_year' => 'Kiadás éve',
            'publication_language' => 'Nyelv',
            'publication_condition' => 'Állapot',
            'publication_collector_value' => 'Gyűjtői érték',
            'publication_complete_volume' => 'Komplett évfolyam',
            'other_category' => 'Egyéb kategória neve',
            'equipment_type' => 'Felszerelés típusa',
            'equipment_condition' => 'Állapot',
            'equipment_hunting_usage' => 'Felhasználás',
            'gear_type' => 'Felszerelés típusa',
            'shooting_discipline' => 'Sportág',
            'firearm_compatibility' => 'Kompatibilitás',
            'sport_size' => 'Méret',
            'modular' => 'Moduláris',
            'competition_level' => 'Versenyhasználat',
            'sport_condition' => 'Állapot',
            'bag_type' => 'Táska típusa',
            'volume_l' => 'Űrtartalom',
            'bag_material' => 'Anyag',
            'bag_weather_resistance' => 'Időjárásállóság',
            'bag_condition' => 'Állapot',
            'call_type' => 'Kürt / síp típusa',
            'call_target_species' => 'Cél vadfaj',
            'call_operation_mode' => 'Működés',
            'call_material_type' => 'Anyag',
            'call_volume_level' => 'Hangerő',
            'call_style' => 'Stílus',
            'decor_type' => 'Dísztárgy típusa',
            'decor_material' => 'Anyag',
            'decor_style' => 'Stílus',
            'decor_motif_tags' => 'Motívum',
            'decor_production_type' => 'Készítés',
            'decor_size_class' => 'Méret kategória',
            'decor_condition' => 'Állapot',
        ];

        $missing = [];
        foreach ( $required as $field ) {
            if ( empty( $values[ $field ] ?? '' ) ) {
                $missing[] = $labels[ $field ] ?? $field;
            }
        }

        if ( empty( $missing ) ) {
            return '';
        }

        $cat_label = (string) ( $rule['label'] ?? $term->name );
        return sprintf(
            '%s kategóriában kötelező: %s.',
            $cat_label,
            implode( ', ', $missing )
        );
    }

    private static function detect_estate_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'elo fegyver',
            'élő fegyver',
            'lofegyver engedely nelkul',
            'lőfegyver engedély nélkül',
            'mukodo automata',
            'működő automata',
            'sorozatlovo',
            'sorozatlövő',
            'katonai keszlet',
            'katonai készlet',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_vadcamera_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'rejtett megfigyelo',
            'rejtett megfigyelő',
            'illegalis lehallgato',
            'illegális lehallgató',
            'titkos kamera',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_shoe_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'hamis marka',
            'hamis márka',
            'replika',
            'fake',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_knife_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'automata kes',
            'automata kés',
            'rugoskes',
            'rugóskés',
            'illegalis penge',
            'illegális penge',
            'rejtett penge',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_service_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'illegalis fegyverjavitas',
            'illegális fegyverjavítás',
            'engedely nelkuli szolgaltatas',
            'engedély nélküli szolgáltatás',
            'tiltott vadaszati tevekenyseg',
            'tiltott vadászati tevékenység',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_trophy_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'vedett fafaj illegalis kereskedelem',
            'védett fafaj illegális kereskedelem',
            'hamis kezmuves eredet',
            'hamis kézműves eredet',
            'trofea manipulacio',
            'trófea manipuláció',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_hunt_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'engedely nelkuli vadaszat',
            'engedély nélküli vadászat',
            'vedett faj',
            'védett faj',
            'illegalis terulet',
            'illegális terület',
            'hamis allomany',
            'hamis állomány',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function map_response_time_to_hours( string $response_time ): int {
        switch ( $response_time ) {
            case 'azonnal-0-2':
                return 2;
            case '24-oran-belul':
                return 24;
            case '48-oran-belul':
                return 48;
            case 'szezonalis-jelenlet':
                return 168;
            case 'folyamatos-jelenlet':
                return 1;
            default:
                return 0;
        }
    }

    private static function detect_marok_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'automata',
            'sorozatlovo',
            'sorozatlövő',
            'konverzio',
            'konverzió',
            'hangtompito',
            'hangtompító',
            'illegalis atalakitas',
            'illegális átalakítás',
            'engedely nelkul',
            'engedély nélkül',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function detect_mixed_moderation_hits( string $title, string $description, string $notes = '' ): array {
        $haystack = mb_strtolower( trim( $title . "\n" . wp_strip_all_tags( $description ) . "\n" . $notes ) );
        if ( $haystack === '' ) {
            return [];
        }

        $needles = [
            'illegalis atalakitas',
            'illegális átalakítás',
            'automata fegyver jelleg',
            'automata',
            'tiltott csokonverzio',
            'tiltott csőkonverzió',
            'engedely nelkul',
            'engedély nélkül',
        ];

        $hits = [];
        foreach ( $needles as $needle ) {
            if ( mb_strpos( $haystack, $needle ) !== false ) {
                $hits[] = $needle;
            }
        }

        return array_values( array_unique( $hits ) );
    }

    private static function is_vehicle_category( int $category_id ): bool {
        if ( get_option( 'va_site_type', 'vadaszat' ) === 'jarmu' ) {
            return true;
        }
        if ( $category_id <= 0 ) {
            return false;
        }

        $term = get_term( $category_id, 'va_category' );
        if ( ! $term || is_wp_error( $term ) ) {
            return false;
        }

        $slug = sanitize_title( (string) $term->slug );
        $name = sanitize_text_field( wp_strip_all_tags( (string) $term->name ) );

        if ( preg_match( '/(jarmu|gepjarmu|auto|autok|kocsi|motor|teherauto|busz|utanfuto|munkagep)/u', $slug ) ) {
            return true;
        }
        if ( preg_match( '/(jármű|gepjármű|autó|auto|kocsi|motor|teherautó|busz|utánfutó|utanfuto|munkagép|munkagep)/ui', $name ) ) {
            return true;
        }

        return false;
    }

    private static function validate_vehicle_required_fields( int $category_id, array $values ): string {
        if ( ! self::is_vehicle_category( $category_id ) ) {
            return '';
        }

        $labels = [
            'brand'             => 'Márka / gyártó',
            'model'             => 'Modell / típus',
            'year'              => 'Gyártási év',
            'mileage'           => 'Futásteljesítmény (km)',
            'fuel_type'         => 'Üzemanyag',
            'transmission'      => 'Sebességváltó',
            'drive'             => 'Hajtás',
            'vehicle_condition' => 'Jármű állapota',
            'performance_kw'    => 'Teljesítmény (kW)',
            'engine_size'       => 'Hengerűrtartalom (cm³)',
        ];

        $required = array_keys( $labels );
        $missing = [];

        foreach ( $required as $field ) {
            $raw = $values[ $field ] ?? '';
            $val = is_scalar( $raw ) ? trim( (string) $raw ) : '';
            if ( $val === '' || $val === '0' ) {
                $missing[] = $labels[ $field ];
            }
        }

        if ( empty( $missing ) ) {
            return '';
        }

        return 'Jármű hirdetéshez kötelező: ' . implode( ', ', $missing ) . '.';
    }

    public static function init() {
        // Hirdetés feladás
        add_action( 'wp_ajax_va_submit_listing',  [ __CLASS__, 'submit_listing' ] );
        // Hirdetés szerkesztés (frontend)
        add_action( 'wp_ajax_va_update_listing',  [ __CLASS__, 'update_listing' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_listing_payment_callback' ] );

        // Kredit csomag vásárlás
        add_action( 'wp_ajax_va_buy_credits',        [ __CLASS__, 'buy_credits' ] );
        add_action( 'template_redirect',             [ __CLASS__, 'handle_buy_credits_start' ] );
        add_action( 'template_redirect',             [ __CLASS__, 'handle_credit_payment_callback' ] );
        add_action( 'init',                          [ __CLASS__, 'handle_stripe_webhook' ] );

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

        // Cím autocomplete
        add_action( 'wp_ajax_va_address_suggest',        [ __CLASS__, 'address_suggest' ] );
        add_action( 'wp_ajax_nopriv_va_address_suggest', [ __CLASS__, 'address_suggest' ] );

        // Felhasználói hirdetés-kezelés (dashboard)
        add_action( 'wp_ajax_va_refresh_listing', [ __CLASS__, 'refresh_listing' ] );
        add_action( 'wp_ajax_va_bulk_listings',   [ __CLASS__, 'bulk_listings' ] );
        add_action( 'wp_ajax_va_set_sale_price',  [ __CLASS__, 'set_sale_price' ] );

        // Időjárás proxy (CORS-biztos Open-Meteo hívás)
        add_action( 'wp_ajax_va_weather_proxy',        [ __CLASS__, 'weather_proxy' ] );
        add_action( 'wp_ajax_nopriv_va_weather_proxy', [ __CLASS__, 'weather_proxy' ] );
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

    public static function weather_proxy(): void {
        if ( self::is_rate_limited( 'weather_proxy', 120, 60 ) ) {
            status_header( 429 );
            wp_send_json_error( [ 'message' => 'Túl sok kérés, próbáld újra később.' ], 429 );
        }

        $lat  = isset( $_GET['lat'] ) ? (float) wp_unslash( $_GET['lat'] ) : 0.0;
        $lon  = isset( $_GET['lon'] ) ? (float) wp_unslash( $_GET['lon'] ) : 0.0;
        $mode = isset( $_GET['mode'] ) ? sanitize_key( wp_unslash( $_GET['mode'] ) ) : 'weather';

        if ( $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180 ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen koordináták.' ], 400 );
        }

        if ( ! in_array( $mode, [ 'weather', 'radar' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen lekérdezési mód.' ], 400 );
        }

        $base = 'https://api.open-meteo.com/v1/forecast';
        if ( $mode === 'radar' ) {
            $query = [
                'latitude'        => $lat,
                'longitude'       => $lon,
                'current'         => 'temperature_2m,relative_humidity_2m,precipitation,weather_code,wind_speed_10m,pressure_msl,cloud_cover',
                'hourly'          => 'temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,pressure_msl,cloud_cover,soil_moisture_0_to_1cm',
                'daily'           => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max',
                'past_days'       => 2,
                'forecast_days'   => 7,
                'timezone'        => 'auto',
                'wind_speed_unit' => 'kmh',
            ];
        } else {
            $query = [
                'latitude'        => $lat,
                'longitude'       => $lon,
                'current'         => 'temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,weather_code,wind_speed_10m,wind_direction_10m',
                'daily'           => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max',
                'forecast_days'   => 8,
                'timezone'        => 'auto',
                'wind_speed_unit' => 'kmh',
            ];
        }

        $url  = add_query_arg( $query, $base );
        $resp = wp_remote_get( $url, [
            'timeout'    => 15,
            'redirection'=> 2,
            'sslverify'  => true,
            'user-agent' => 'VA-Weather-Proxy/1.0',
        ] );

        if ( is_wp_error( $resp ) ) {
            wp_send_json( self::build_weather_fallback( $lat, $lon, $mode ) );
        }

        $code = (int) wp_remote_retrieve_response_code( $resp );
        $body = (string) wp_remote_retrieve_body( $resp );
        if ( $code < 200 || $code >= 300 || $body === '' ) {
            wp_send_json( self::build_weather_fallback( $lat, $lon, $mode ) );
        }

        $json = json_decode( $body, true );
        if ( ! is_array( $json ) ) {
            wp_send_json( self::build_weather_fallback( $lat, $lon, $mode ) );
        }

        wp_send_json( $json );
    }

    private static function build_weather_fallback( float $lat, float $lon, string $mode ): array {
        $now_ts  = current_time( 'timestamp' );
        $seed    = abs( (int) round( ( $lat + 90 ) * 1000 + ( $lon + 180 ) * 1000 ) );
        $base    = 14 + ( $seed % 7 );

        $daily_days = $mode === 'weather' ? 8 : 7;
        $daily = [
            'time'                        => [],
            'weather_code'                => [],
            'temperature_2m_max'          => [],
            'temperature_2m_min'          => [],
            'precipitation_probability_max'=> [],
            'precipitation_sum'           => [],
            'wind_speed_10m_max'          => [],
        ];

        for ( $d = 0; $d < $daily_days; $d++ ) {
            $ts = strtotime( '+' . $d . ' day', $now_ts );
            $daily['time'][] = wp_date( 'Y-m-d', $ts );

            $temp_max = $base + (int) round( sin( ( $d + 1 ) * 0.8 ) * 4 ) + ( $seed % 3 );
            $temp_min = $temp_max - ( 6 + ( $d % 2 ) );
            $rain_mm  = max( 0, ( ( $seed + $d * 11 ) % 10 ) - 3 ) * 0.7;
            $rain_pr  = min( 95, max( 10, 25 + (int) round( $rain_mm * 18 ) + ( $d % 4 ) * 6 ) );
            $wind_max = 10 + ( ( $seed + $d * 7 ) % 26 );

            $daily['temperature_2m_max'][] = $temp_max;
            $daily['temperature_2m_min'][] = $temp_min;
            $daily['precipitation_sum'][]  = round( $rain_mm, 1 );
            $daily['precipitation_probability_max'][] = $rain_pr;
            $daily['wind_speed_10m_max'][] = $wind_max;
            $daily['weather_code'][] = $rain_mm > 1.2 ? 61 : ( $rain_mm > 0.2 ? 3 : 1 );
        }

        if ( $mode === 'weather' ) {
            return [
                'va_fallback'   => true,
                'current'       => [
                    'temperature_2m'         => $daily['temperature_2m_max'][0] - 2,
                    'apparent_temperature'   => $daily['temperature_2m_max'][0] - 3,
                    'relative_humidity_2m'   => 58 + ( $seed % 26 ),
                    'precipitation'          => $daily['precipitation_sum'][0] > 0 ? 0.2 : 0,
                    'weather_code'           => $daily['weather_code'][0],
                    'wind_speed_10m'         => max( 4, $daily['wind_speed_10m_max'][0] - 6 ),
                    'wind_direction_10m'     => ( $seed * 17 ) % 360,
                ],
                'current_units' => [ 'wind_speed_10m' => 'km/h' ],
                'daily'         => $daily,
                'daily_units'   => [ 'wind_speed_10m_max' => 'km/h' ],
            ];
        }

        $hourly = [
            'time'                 => [],
            'temperature_2m'       => [],
            'relative_humidity_2m' => [],
            'precipitation'        => [],
            'wind_speed_10m'       => [],
            'pressure_msl'         => [],
            'cloud_cover'          => [],
            'soil_moisture_0_to_1cm' => [],
        ];

        $start_ts = strtotime( '-2 day', strtotime( wp_date( 'Y-m-d 00:00:00', $now_ts ) ) );
        for ( $h = 0; $h < 216; $h++ ) {
            $ts = $start_ts + ( $h * HOUR_IN_SECONDS );
            $hourly['time'][] = wp_date( 'Y-m-d\\TH:00', $ts );
            $hourly['temperature_2m'][] = ( $base - 2 ) + round( sin( $h / 5 ) * 4, 1 );
            $hourly['relative_humidity_2m'][] = 55 + ( ( $h + $seed ) % 36 );
            $hourly['precipitation'][] = ( ( $h + $seed ) % 19 === 0 ) ? 0.8 : 0.0;
            $hourly['wind_speed_10m'][] = 6 + ( ( $h + $seed ) % 24 );
            $hourly['pressure_msl'][] = 1008 + round( sin( $h / 9 ) * 7, 1 );
            $hourly['cloud_cover'][] = min( 100, max( 8, 35 + ( ( $h * 7 + $seed ) % 62 ) ) );
            $hourly['soil_moisture_0_to_1cm'][] = round( 0.12 + ( ( ( $h + $seed ) % 10 ) * 0.01 ), 2 );
        }

        return [
            'va_fallback' => true,
            'current'     => [
                'temperature_2m'       => $daily['temperature_2m_max'][0] - 2,
                'relative_humidity_2m' => 58 + ( $seed % 26 ),
                'precipitation'        => $daily['precipitation_sum'][0] > 0 ? 0.2 : 0,
                'weather_code'         => $daily['weather_code'][0],
                'wind_speed_10m'       => max( 4, $daily['wind_speed_10m_max'][0] - 6 ),
                'pressure_msl'         => 1014,
                'cloud_cover'          => 45 + ( $seed % 30 ),
            ],
            'hourly'      => $hourly,
            'daily'       => [
                'time'               => $daily['time'],
                'weather_code'       => $daily['weather_code'],
                'temperature_2m_max' => $daily['temperature_2m_max'],
                'temperature_2m_min' => $daily['temperature_2m_min'],
                'precipitation_sum'  => $daily['precipitation_sum'],
                'wind_speed_10m_max' => $daily['wind_speed_10m_max'],
            ],
        ];
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
        $postal_code = sanitize_text_field( wp_unslash( $_POST['postal_code'] ?? '' ) );
        $street      = sanitize_text_field( wp_unslash( $_POST['street'] ?? '' ) );
        $postal_code = preg_replace( '/\D+/', '', $postal_code );
        $other_category = sanitize_text_field( wp_unslash( $_POST['other_category'] ?? '' ) );
        $brand       = sanitize_text_field( wp_unslash( $_POST['brand']    ?? '' ) );
        $model       = sanitize_text_field( wp_unslash( $_POST['model']    ?? '' ) );
        $caliber     = sanitize_text_field( wp_unslash( $_POST['caliber']  ?? '' ) );
        $year        = intval( $_POST['year'] ?? 0 );
        $license_req = ! empty( $_POST['license_req'] ) ? '1' : '0';
        $optic_type  = sanitize_key( wp_unslash( $_POST['optic_type'] ?? '' ) );
        $optic_zoom  = sanitize_text_field( wp_unslash( $_POST['optic_zoom'] ?? '' ) );
        $optic_objective = sanitize_text_field( wp_unslash( $_POST['optic_objective'] ?? '' ) );
        $optic_magnification_min = sanitize_text_field( wp_unslash( $_POST['optic_magnification_min'] ?? '' ) );
        $optic_magnification_max = sanitize_text_field( wp_unslash( $_POST['optic_magnification_max'] ?? '' ) );
        $optic_magnification_fixed = sanitize_text_field( wp_unslash( $_POST['optic_magnification_fixed'] ?? '' ) );
        $optic_tube_diameter = sanitize_text_field( wp_unslash( $_POST['optic_tube_diameter'] ?? '' ) );
        $optic_exit_pupil = sanitize_text_field( wp_unslash( $_POST['optic_exit_pupil'] ?? '' ) );
        $optic_field_of_view = sanitize_text_field( wp_unslash( $_POST['optic_field_of_view'] ?? '' ) );
        $optic_eye_relief = sanitize_text_field( wp_unslash( $_POST['optic_eye_relief'] ?? '' ) );
        $optic_reticle_type = sanitize_key( wp_unslash( $_POST['optic_reticle_type'] ?? '' ) );
        $optic_reticle_plane = sanitize_key( wp_unslash( $_POST['optic_reticle_plane'] ?? '' ) );
        $optic_illumination = sanitize_key( wp_unslash( $_POST['optic_illumination'] ?? '' ) );
        $optic_illumination_color = sanitize_text_field( wp_unslash( $_POST['optic_illumination_color'] ?? '' ) );
        $optic_click_value = sanitize_key( wp_unslash( $_POST['optic_click_value'] ?? '' ) );
        $optic_parallax_adjustment = sanitize_key( wp_unslash( $_POST['optic_parallax_adjustment'] ?? '' ) );
        $optic_turret_type = sanitize_key( wp_unslash( $_POST['optic_turret_type'] ?? '' ) );
        $optic_body_material = sanitize_key( wp_unslash( $_POST['optic_body_material'] ?? '' ) );
        $optic_coating_type = sanitize_key( wp_unslash( $_POST['optic_coating_type'] ?? '' ) );
        $optic_waterproof_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['optic_waterproof_flags'] ?? [] ) ) ) );
        $optic_prism_system = sanitize_key( wp_unslash( $_POST['optic_prism_system'] ?? '' ) );
        $optic_glass_type = sanitize_key( wp_unslash( $_POST['optic_glass_type'] ?? '' ) );
        $optic_dot_size = sanitize_text_field( wp_unslash( $_POST['optic_dot_size'] ?? '' ) );
        $optic_rail_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['optic_rail_compatibility'] ?? [] ) ) ) );
        $optic_rangefinder_max_m = sanitize_text_field( wp_unslash( $_POST['optic_rangefinder_max_m'] ?? '' ) );
        $optic_ballistic_calc = sanitize_key( wp_unslash( $_POST['optic_ballistic_calc'] ?? '' ) );
        $optic_spektiv_zoom_range = sanitize_key( wp_unslash( $_POST['optic_spektiv_zoom_range'] ?? '' ) );
        $optic_eyepiece_type = sanitize_key( wp_unslash( $_POST['optic_eyepiece_type'] ?? '' ) );
        $optic_weapon_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['optic_weapon_compatibility'] ?? [] ) ) ) );
        $scope_accessories_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['scope_accessories_flags'] ?? [] ) ) ) );
        $optic_mount_type = sanitize_key( wp_unslash( $_POST['optic_mount_type'] ?? '' ) );
        $optic_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['optic_shipping_methods'] ?? [] ) ) ) );
        $optic_recommended_use = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['optic_recommended_use'] ?? [] ) ) ) );
        $optic_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['optic_moderation_flags'] ?? [] ) ) ) );
        $thermal_device_type = sanitize_key( wp_unslash( $_POST['thermal_device_type'] ?? '' ) );
        $thermal_condition = sanitize_key( wp_unslash( $_POST['thermal_condition'] ?? '' ) );
        $thermal_warranty = sanitize_key( wp_unslash( $_POST['thermal_warranty'] ?? '' ) );
        $thermal_manufacture_year = sanitize_text_field( wp_unslash( $_POST['thermal_manufacture_year'] ?? '' ) );
        $thermal_sensor_resolution = sanitize_key( wp_unslash( $_POST['thermal_sensor_resolution'] ?? '' ) );
        $thermal_pixel_pitch = sanitize_key( wp_unslash( $_POST['thermal_pixel_pitch'] ?? '' ) );
        $thermal_netd = sanitize_key( wp_unslash( $_POST['thermal_netd'] ?? '' ) );
        $thermal_refresh_hz = sanitize_key( wp_unslash( $_POST['thermal_refresh_hz'] ?? '' ) );
        $thermal_base_magnification = sanitize_text_field( wp_unslash( $_POST['thermal_base_magnification'] ?? '' ) );
        $thermal_digital_zoom = sanitize_key( wp_unslash( $_POST['thermal_digital_zoom'] ?? '' ) );
        $thermal_lens_size = sanitize_key( wp_unslash( $_POST['thermal_lens_size'] ?? '' ) );
        $thermal_fov_degree = sanitize_text_field( wp_unslash( $_POST['thermal_fov_degree'] ?? '' ) );
        $thermal_fov_m100 = sanitize_text_field( wp_unslash( $_POST['thermal_fov_m100'] ?? '' ) );
        $thermal_detect_human_m = sanitize_text_field( wp_unslash( $_POST['thermal_detect_human_m'] ?? '' ) );
        $thermal_detect_game_m = sanitize_text_field( wp_unslash( $_POST['thermal_detect_game_m'] ?? '' ) );
        $thermal_detect_vehicle_m = sanitize_text_field( wp_unslash( $_POST['thermal_detect_vehicle_m'] ?? '' ) );
        $thermal_display_type = sanitize_key( wp_unslash( $_POST['thermal_display_type'] ?? '' ) );
        $thermal_display_resolution = sanitize_text_field( wp_unslash( $_POST['thermal_display_resolution'] ?? '' ) );
        $thermal_palettes = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_palettes'] ?? [] ) ) ) );
        $thermal_photo = sanitize_key( wp_unslash( $_POST['thermal_photo'] ?? '' ) );
        $thermal_video = sanitize_key( wp_unslash( $_POST['thermal_video'] ?? '' ) );
        $thermal_audio = sanitize_key( wp_unslash( $_POST['thermal_audio'] ?? '' ) );
        $thermal_internal_memory_gb = sanitize_text_field( wp_unslash( $_POST['thermal_internal_memory_gb'] ?? '' ) );
        $thermal_sd_support = sanitize_key( wp_unslash( $_POST['thermal_sd_support'] ?? '' ) );
        $thermal_connectivity = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_connectivity'] ?? [] ) ) ) );
        $thermal_mobile_app = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_mobile_app'] ?? [] ) ) ) );
        $thermal_extra_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_extra_features'] ?? [] ) ) ) );
        $thermal_battery_type = sanitize_key( wp_unslash( $_POST['thermal_battery_type'] ?? '' ) );
        $thermal_runtime_hours = sanitize_text_field( wp_unslash( $_POST['thermal_runtime_hours'] ?? '' ) );
        $thermal_powerbank_compatible = sanitize_key( wp_unslash( $_POST['thermal_powerbank_compatible'] ?? '' ) );
        $thermal_ip_rating = sanitize_key( wp_unslash( $_POST['thermal_ip_rating'] ?? '' ) );
        $thermal_recoil_resistance = sanitize_key( wp_unslash( $_POST['thermal_recoil_resistance'] ?? '' ) );
        $thermal_weapon_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_weapon_compatibility'] ?? [] ) ) ) );
        $thermal_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_accessories'] ?? [] ) ) ) );
        $dog_age_months = sanitize_text_field( wp_unslash( $_POST['dog_age_months'] ?? '' ) );
        $dog_working_type = sanitize_key( wp_unslash( $_POST['dog_working_type'] ?? '' ) );
        $dog_breed  = sanitize_text_field( wp_unslash( $_POST['dog_breed'] ?? '' ) );
        $dog_name = sanitize_text_field( wp_unslash( $_POST['dog_name'] ?? '' ) );
        $dog_gender = sanitize_key( wp_unslash( $_POST['dog_gender'] ?? '' ) );
        $dog_breeder_name = sanitize_text_field( wp_unslash( $_POST['dog_breeder_name'] ?? '' ) );
        $dog_kennel_name = sanitize_text_field( wp_unslash( $_POST['dog_kennel_name'] ?? '' ) );
        $dog_pedigree_status = sanitize_key( wp_unslash( $_POST['dog_pedigree_status'] ?? '' ) );
        $dog_origin_country = sanitize_text_field( wp_unslash( $_POST['dog_origin_country'] ?? '' ) );
        $dog_bloodline_type = sanitize_key( wp_unslash( $_POST['dog_bloodline_type'] ?? '' ) );
        $dog_documents = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_documents'] ?? [] ) ) ) );
        $dog_birth_date = sanitize_text_field( wp_unslash( $_POST['dog_birth_date'] ?? '' ) );
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dog_birth_date ) ) {
            $dog_birth_date = '';
        }
        $dog_color  = sanitize_text_field( wp_unslash( $_POST['dog_color'] ?? '' ) );
        $dog_appearance_type = sanitize_key( wp_unslash( $_POST['dog_appearance_type'] ?? '' ) );
        $dog_purebred = sanitize_key( wp_unslash( $_POST['dog_purebred'] ?? '' ) );
        $dog_training_skills = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_training_skills'] ?? [] ) ) ) );
        $dog_training_level = sanitize_key( wp_unslash( $_POST['dog_training_level'] ?? '' ) );
        $dog_exam_level = sanitize_key( wp_unslash( $_POST['dog_exam_level'] ?? '' ) );
        $dog_work_exam_type = sanitize_key( wp_unslash( $_POST['dog_work_exam_type'] ?? '' ) );
        $dog_hunting_specialization = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_hunting_specialization'] ?? [] ) ) ) );
        $dog_work_style = sanitize_key( wp_unslash( $_POST['dog_work_style'] ?? '' ) );
        $dog_search_radius_m = sanitize_text_field( wp_unslash( $_POST['dog_search_radius_m'] ?? '' ) );
        $dog_endurance_level = sanitize_key( wp_unslash( $_POST['dog_endurance_level'] ?? '' ) );
        $dog_aggression_level = sanitize_key( wp_unslash( $_POST['dog_aggression_level'] ?? '' ) );
        $dog_apport_stability = sanitize_key( wp_unslash( $_POST['dog_apport_stability'] ?? '' ) );
        $dog_water_work_ability = sanitize_key( wp_unslash( $_POST['dog_water_work_ability'] ?? '' ) );
        $dog_water_work = sanitize_key( wp_unslash( $_POST['dog_water_work'] ?? '' ) );
        $dog_water_work_level = sanitize_key( wp_unslash( $_POST['dog_water_work_level'] ?? '' ) );
        $dog_cold_water_tolerance = sanitize_key( wp_unslash( $_POST['dog_cold_water_tolerance'] ?? '' ) );
        $dog_temperament = sanitize_key( wp_unslash( $_POST['dog_temperament'] ?? '' ) );
        $dog_environment = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_environment'] ?? [] ) ) ) );
        $dog_allergic = sanitize_key( wp_unslash( $_POST['dog_allergic'] ?? '' ) );
        $dog_special_diet = sanitize_text_field( wp_unslash( $_POST['dog_special_diet'] ?? '' ) );
        $dog_known_diseases = sanitize_textarea_field( wp_unslash( $_POST['dog_known_diseases'] ?? '' ) );
        $dog_hip_dysplasia_screening = sanitize_key( wp_unslash( $_POST['dog_hip_dysplasia_screening'] ?? '' ) );
        $dog_height_cm = sanitize_text_field( wp_unslash( $_POST['dog_height_cm'] ?? '' ) );
        $dog_weight_kg = sanitize_text_field( wp_unslash( $_POST['dog_weight_kg'] ?? '' ) );
        $dog_country = sanitize_text_field( wp_unslash( $_POST['dog_country'] ?? '' ) );
        $dog_county = sanitize_text_field( wp_unslash( $_POST['dog_county'] ?? '' ) );
        $dog_city = sanitize_text_field( wp_unslash( $_POST['dog_city'] ?? '' ) );
        $dog_origin_qualification = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_origin_qualification'] ?? [] ) ) ) );
        $dog_litter_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['dog_litter_count'] ?? '' ) );
        $dog_available_from = sanitize_text_field( wp_unslash( $_POST['dog_available_from'] ?? '' ) );
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dog_available_from ) ) {
            $dog_available_from = '';
        }
        $dog_tracking_distance_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['dog_tracking_distance_m'] ?? '' ) );
        $dog_tracking_experience = sanitize_text_field( wp_unslash( $_POST['dog_tracking_experience'] ?? '' ) );
        $dog_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_moderation_flags'] ?? [] ) ) ) );
        $dog_work_video_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_work_video_types'] ?? [] ) ) ) );
        $dog_work_video_url = esc_url_raw( wp_unslash( $_POST['dog_work_video_url'] ?? '' ) );
        if ( $dog_birth_date !== '' ) {
            try {
                $dog_birth = new DateTimeImmutable( $dog_birth_date );
                $dog_today = new DateTimeImmutable( current_time( 'Y-m-d' ) );
                if ( $dog_birth <= $dog_today ) {
                    $dog_diff = $dog_birth->diff( $dog_today );
                    $dog_age_months = (string) ( ( $dog_diff->y * 12 ) + $dog_diff->m );
                }
            } catch ( Exception $e ) {
            }
        }
        $dog_review_hits = array_values( array_filter( array_map( 'trim', explode( ',', $dog_moderation_flags ) ) ) );
        $dog_filter_pedigree = $dog_pedigree_status === 'igen' ? '1' : '0';
        $dog_filter_has_work_exam = ( $dog_work_exam_type !== '' || strpos( ',' . $dog_documents . ',', ',munkavizsga-papir,' ) !== false ) ? '1' : '0';
        $dog_filter_water_work = ( $dog_water_work === 'igen' || in_array( $dog_water_work_level, [ 'eros', 'kozepes' ], true ) ) ? '1' : '0';
        $job_location = sanitize_text_field( wp_unslash( $_POST['job_location'] ?? '' ) );
        $job_type   = sanitize_text_field( wp_unslash( $_POST['job_type'] ?? '' ) );
        $service_type = sanitize_key( wp_unslash( $_POST['service_type'] ?? '' ) );
        $service_provider_type = sanitize_key( wp_unslash( $_POST['service_provider_type'] ?? '' ) );
        $service_provider_name = sanitize_text_field( wp_unslash( $_POST['service_provider_name'] ?? '' ) );
        $service_country = sanitize_text_field( wp_unslash( $_POST['service_country'] ?? '' ) );
        $service_county = absint( wp_unslash( $_POST['service_county'] ?? 0 ) );
        $service_town = sanitize_text_field( wp_unslash( $_POST['service_town'] ?? '' ) );
        $service_area = sanitize_key( wp_unslash( $_POST['service_area'] ?? '' ) );
        $service_gps_lat = sanitize_text_field( wp_unslash( $_POST['service_gps_lat'] ?? '' ) );
        $service_gps_lng = sanitize_text_field( wp_unslash( $_POST['service_gps_lng'] ?? '' ) );
        $service_pricing_type = sanitize_key( wp_unslash( $_POST['service_pricing_type'] ?? '' ) );
        $service_min_price = sanitize_text_field( wp_unslash( $_POST['service_min_price'] ?? '' ) );
        $service_travel_fee = sanitize_text_field( wp_unslash( $_POST['service_travel_fee'] ?? '' ) );
        $service_schedule = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_schedule'] ?? [] ) ) ) );
        $service_available_weekend = sanitize_key( wp_unslash( $_POST['service_available_weekend'] ?? '' ) );
        $service_available_24_7 = sanitize_key( wp_unslash( $_POST['service_available_24_7'] ?? '' ) );
        $service_seasonal = sanitize_key( wp_unslash( $_POST['service_seasonal'] ?? '' ) );
        $service_hunting_specialization = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_hunting_specialization'] ?? [] ) ) ) );
        $service_hunting_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_hunting_species'] ?? [] ) ) ) );
        $service_weapon_categories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_weapon_categories'] ?? [] ) ) ) );
        $service_equipment = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_equipment'] ?? [] ) ) ) );
        $service_permits = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_permits'] ?? [] ) ) ) );
        $service_reference_rating = sanitize_text_field( wp_unslash( $_POST['service_reference_rating'] ?? '' ) );
        $service_completed_jobs = sanitize_text_field( wp_unslash( $_POST['service_completed_jobs'] ?? '' ) );
        $service_reference_notes = sanitize_textarea_field( wp_unslash( $_POST['service_reference_notes'] ?? '' ) );
        $service_media_required = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_media_required'] ?? [] ) ) ) );
        $service_media_video = sanitize_key( wp_unslash( $_POST['service_media_video'] ?? '' ) );
        $service_verified_provider = sanitize_key( wp_unslash( $_POST['service_verified_provider'] ?? '' ) );
        $service_document_verified = sanitize_key( wp_unslash( $_POST['service_document_verified'] ?? '' ) );
        $service_phone_verified = sanitize_key( wp_unslash( $_POST['service_phone_verified'] ?? '' ) );
        $service_business_verified = sanitize_key( wp_unslash( $_POST['service_business_verified'] ?? '' ) );
        $service_sos_service = sanitize_key( wp_unslash( $_POST['service_sos_service'] ?? '' ) );
        $service_urgent_repair = sanitize_key( wp_unslash( $_POST['service_urgent_repair'] ?? '' ) );
        $service_full_prep = sanitize_key( wp_unslash( $_POST['service_full_prep'] ?? '' ) );
        $service_night_work = sanitize_key( wp_unslash( $_POST['service_night_work'] ?? '' ) );
        $service_hound_breed = sanitize_text_field( wp_unslash( $_POST['service_hound_breed'] ?? '' ) );
        $service_caliber_support = sanitize_text_field( wp_unslash( $_POST['service_caliber_support'] ?? '' ) );
        $service_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_moderation_flags'] ?? [] ) ) ) );
        $clothing_type = sanitize_key( wp_unslash( $_POST['clothing_type'] ?? '' ) );
        $clothing_condition = sanitize_key( wp_unslash( $_POST['clothing_condition'] ?? '' ) );
        $clothing_gender = sanitize_key( wp_unslash( $_POST['clothing_gender'] ?? '' ) );
        $clothing_size_system = sanitize_key( wp_unslash( $_POST['clothing_size_system'] ?? '' ) );
        $clothing_size = sanitize_text_field( wp_unslash( $_POST['clothing_size'] ?? '' ) );
        $clothing_accessory_size = sanitize_text_field( wp_unslash( $_POST['clothing_accessory_size'] ?? '' ) );
        $clothing_glove_size = sanitize_text_field( wp_unslash( $_POST['clothing_glove_size'] ?? '' ) );
        $clothing_hat_size = sanitize_key( wp_unslash( $_POST['clothing_hat_size'] ?? '' ) );
        $clothing_hat_cm = sanitize_text_field( wp_unslash( $_POST['clothing_hat_cm'] ?? '' ) );
        $clothing_belt_length_cm = sanitize_text_field( wp_unslash( $_POST['clothing_belt_length_cm'] ?? '' ) );
        $clothing_colors = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_colors'] ?? [] ) ) ) );
        $clothing_materials = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_materials'] ?? [] ) ) ) );
        $clothing_protections = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_protections'] ?? [] ) ) ) );
        $clothing_season = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_season'] ?? [] ) ) ) );
        $clothing_use_cases = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_use_cases'] ?? [] ) ) ) );
        $clothing_special_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_special_features'] ?? [] ) ) ) );
        $clothing_glove_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_glove_features'] ?? [] ) ) ) );
        $clothing_headwear_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_headwear_features'] ?? [] ) ) ) );
        $clothing_poncho_water_column = sanitize_text_field( wp_unslash( $_POST['clothing_poncho_water_column'] ?? '' ) );
        $clothing_poncho_ultralight = sanitize_key( wp_unslash( $_POST['clothing_poncho_ultralight'] ?? '' ) );
        $clothing_poncho_backpack_compatible = sanitize_key( wp_unslash( $_POST['clothing_poncho_backpack_compatible'] ?? '' ) );
        $clothing_ghillie_type = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_ghillie_type'] ?? [] ) ) ) );
        $clothing_ghillie_pattern = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_ghillie_pattern'] ?? [] ) ) ) );
        $clothing_filter_thermo = ( strpos( ',' . $clothing_protections . ',', ',thermo,' ) !== false ) ? '1' : '0';
        $clothing_filter_waterproof = ( strpos( ',' . $clothing_protections . ',', ',vizallo,' ) !== false ) ? '1' : '0';
        $clothing_filter_camo = ( strpos( ',' . $clothing_colors . ',', ',terepmintas,' ) !== false || strpos( ',' . $clothing_colors . ',', ',realtree,' ) !== false || strpos( ',' . $clothing_colors . ',', ',multicam,' ) !== false ) ? '1' : '0';
        $clothing_filter_silent = ( strpos( ',' . $clothing_special_features . ',', ',hangtalan,' ) !== false ) ? '1' : '0';
        $season = sanitize_key( wp_unslash( $_POST['season'] ?? '' ) );
        $hunting_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunting_usage'] ?? [] ) ) ) );
        $noise_level = sanitize_key( wp_unslash( $_POST['noise_level'] ?? '' ) );
        $material_tech = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['material_tech'] ?? [] ) ) ) );
        $waterproof_level = sanitize_key( wp_unslash( $_POST['waterproof_level'] ?? '' ) );
        $camo_pattern = sanitize_key( wp_unslash( $_POST['camo_pattern'] ?? '' ) );
        $silence_index = sanitize_key( wp_unslash( $_POST['silence_index'] ?? '' ) );
        $clothing_weather_protection = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_weather_protection'] ?? [] ) ) ) );
        $clothing_functional_details = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_functional_details'] ?? [] ) ) ) );
        $clothing_fit = sanitize_key( wp_unslash( $_POST['clothing_fit'] ?? '' ) );
        $clothing_size_fit = sanitize_key( wp_unslash( $_POST['clothing_size_fit'] ?? '' ) );
        $clothing_hunter_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_hunter_features'] ?? [] ) ) ) );
        $clothing_gear_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_gear_compatibility'] ?? [] ) ) ) );
        $clothing_durability = sanitize_key( wp_unslash( $_POST['clothing_durability'] ?? '' ) );
        $clothing_layering = sanitize_key( wp_unslash( $_POST['clothing_layering'] ?? '' ) );
        $clothing_membrane_type = sanitize_key( wp_unslash( $_POST['clothing_membrane_type'] ?? '' ) );
        $clothing_ir_reduction = sanitize_key( wp_unslash( $_POST['clothing_ir_reduction'] ?? '' ) );
        $clothing_filter_hunting_usage = $hunting_usage;
        $clothing_filter_noise_level = $noise_level;
        $clothing_filter_material_tech = $material_tech;
        $clothing_filter_camo_pattern = $camo_pattern;
        $clothing_filter_season_mode = $season;
        $shoe_size   = sanitize_text_field( wp_unslash( $_POST['shoe_size'] ?? '' ) );
        $shoe_main_type = sanitize_key( wp_unslash( $_POST['shoe_main_type'] ?? '' ) );
        $shoe_condition = sanitize_key( wp_unslash( $_POST['shoe_condition'] ?? '' ) );
        $shoe_gender = sanitize_key( wp_unslash( $_POST['shoe_gender'] ?? '' ) );
        $shoe_eu_size = sanitize_text_field( wp_unslash( $_POST['shoe_eu_size'] ?? '' ) );
        $shoe_uk_size = sanitize_text_field( wp_unslash( $_POST['shoe_uk_size'] ?? '' ) );
        $shoe_us_size = sanitize_text_field( wp_unslash( $_POST['shoe_us_size'] ?? '' ) );
        $shoe_boot_height = sanitize_key( wp_unslash( $_POST['shoe_boot_height'] ?? '' ) );
        $shoe_outer_material = sanitize_key( wp_unslash( $_POST['shoe_outer_material'] ?? '' ) );
        $shoe_lining = sanitize_key( wp_unslash( $_POST['shoe_lining'] ?? '' ) );
        $shoe_sole_material = sanitize_key( wp_unslash( $_POST['shoe_sole_material'] ?? '' ) );
        $shoe_protections = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_protections'] ?? [] ) ) ) );
        $shoe_season = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_season'] ?? [] ) ) ) );
        $shoe_terrain = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_terrain'] ?? [] ) ) ) );
        $shoe_use_cases = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_use_cases'] ?? [] ) ) ) );
        $shoe_sole_pattern = sanitize_key( wp_unslash( $_POST['shoe_sole_pattern'] ?? '' ) );
        $shoe_stiffness = sanitize_key( wp_unslash( $_POST['shoe_stiffness'] ?? '' ) );
        $shoe_weight_single_g = sanitize_text_field( wp_unslash( $_POST['shoe_weight_single_g'] ?? '' ) );
        $shoe_weight_pair_g = sanitize_text_field( wp_unslash( $_POST['shoe_weight_pair_g'] ?? '' ) );
        $shoe_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_accessories'] ?? [] ) ) ) );
        $shoe_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_shipping_methods'] ?? [] ) ) ) );
        $shoe_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_required_media'] ?? [] ) ) ) );
        $shoe_rubber_shaft_cm = sanitize_text_field( wp_unslash( $_POST['shoe_rubber_shaft_cm'] ?? '' ) );
        $shoe_rubber_neoprene_lining = sanitize_key( wp_unslash( $_POST['shoe_rubber_neoprene_lining'] ?? '' ) );
        $shoe_tactical_steel_toe = sanitize_key( wp_unslash( $_POST['shoe_tactical_steel_toe'] ?? '' ) );
        $shoe_tactical_quick_lace = sanitize_key( wp_unslash( $_POST['shoe_tactical_quick_lace'] ?? '' ) );
        $shoe_winter_comfort_temp_c = sanitize_text_field( wp_unslash( $_POST['shoe_winter_comfort_temp_c'] ?? '' ) );
        $shoe_foot_type = sanitize_key( wp_unslash( $_POST['shoe_foot_type'] ?? '' ) );
        $shoe_sole_wear_percent = sanitize_text_field( wp_unslash( $_POST['shoe_sole_wear_percent'] ?? '' ) );
        $shoe_inner_wear = sanitize_text_field( wp_unslash( $_POST['shoe_inner_wear'] ?? '' ) );
        $shoe_waterproof_retained = sanitize_key( wp_unslash( $_POST['shoe_waterproof_retained'] ?? '' ) );
        $shoe_resoled = sanitize_key( wp_unslash( $_POST['shoe_resoled'] ?? '' ) );
        $shoe_notes = sanitize_textarea_field( wp_unslash( $_POST['shoe_notes'] ?? '' ) );
        if ( $shoe_eu_size === '' ) {
            $shoe_eu_size = $shoe_size;
        }
        if ( $shoe_size === '' ) {
            $shoe_size = $shoe_eu_size;
        }
        $shoe_filter_waterproof = ( strpos( ',' . $shoe_protections . ',', ',vizallo,' ) !== false ) ? '1' : '0';
        $shoe_filter_goretex = ( $shoe_lining === 'gore-tex' ) ? '1' : '0';
        $shoe_filter_winter = ( strpos( ',' . $shoe_season . ',', ',teli,' ) !== false || $shoe_main_type === 'teli-csizma' || $shoe_main_type === 'hotaposo' ) ? '1' : '0';
        $shoe_filter_high_ankle = ( $shoe_boot_height === 'magas' ) ? '1' : '0';
        $knife_type  = sanitize_key( wp_unslash( $_POST['knife_type'] ?? '' ) );
        $knife_blade_length = sanitize_text_field( wp_unslash( $_POST['knife_blade_length'] ?? '' ) );
        $knife_condition = sanitize_key( wp_unslash( $_POST['knife_condition'] ?? '' ) );
        $knife_manufacture_year = sanitize_text_field( wp_unslash( $_POST['knife_manufacture_year'] ?? '' ) );
        $knife_total_length_cm = sanitize_text_field( wp_unslash( $_POST['knife_total_length_cm'] ?? '' ) );
        $knife_blade_length_mm = sanitize_text_field( wp_unslash( $_POST['knife_blade_length_mm'] ?? '' ) );
        $knife_blade_thickness_mm = sanitize_text_field( wp_unslash( $_POST['knife_blade_thickness_mm'] ?? '' ) );
        $knife_weight_g = sanitize_text_field( wp_unslash( $_POST['knife_weight_g'] ?? '' ) );
        $knife_steel_type = sanitize_key( wp_unslash( $_POST['knife_steel_type'] ?? '' ) );
        $knife_blade_shape = sanitize_key( wp_unslash( $_POST['knife_blade_shape'] ?? '' ) );
        $knife_blade_edge = sanitize_key( wp_unslash( $_POST['knife_blade_edge'] ?? '' ) );
        $knife_finish = sanitize_key( wp_unslash( $_POST['knife_finish'] ?? '' ) );
        $knife_construction = sanitize_key( wp_unslash( $_POST['knife_construction'] ?? '' ) );
        $knife_locking_type = sanitize_key( wp_unslash( $_POST['knife_locking_type'] ?? '' ) );
        $knife_one_hand_opening = sanitize_key( wp_unslash( $_POST['knife_one_hand_opening'] ?? '' ) );
        $knife_handle_material = sanitize_key( wp_unslash( $_POST['knife_handle_material'] ?? '' ) );
        $knife_handle_color = sanitize_text_field( wp_unslash( $_POST['knife_handle_color'] ?? '' ) );
        $knife_has_sheath = sanitize_key( wp_unslash( $_POST['knife_has_sheath'] ?? '' ) );
        $knife_sheath_type = sanitize_key( wp_unslash( $_POST['knife_sheath_type'] ?? '' ) );
        $knife_belt_carry = sanitize_key( wp_unslash( $_POST['knife_belt_carry'] ?? '' ) );
        $knife_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_usage'] ?? [] ) ) ) );
        $knife_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_features'] ?? [] ) ) ) );
        $knife_age_restriction = sanitize_key( wp_unslash( $_POST['knife_age_restriction'] ?? '' ) );
        $knife_restricted = sanitize_key( wp_unslash( $_POST['knife_restricted'] ?? '' ) );
        $knife_public_carry = sanitize_key( wp_unslash( $_POST['knife_public_carry'] ?? '' ) );
        $knife_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_accessories'] ?? [] ) ) ) );
        $knife_price_deal = sanitize_key( wp_unslash( $_POST['knife_price_deal'] ?? '' ) );
        $knife_price_exchange = sanitize_key( wp_unslash( $_POST['knife_price_exchange'] ?? '' ) );
        $knife_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_shipping_methods'] ?? [] ) ) ) );
        $knife_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_required_media'] ?? [] ) ) ) );
        $knife_sharpening_state = sanitize_key( wp_unslash( $_POST['knife_sharpening_state'] ?? '' ) );
        $knife_collectible_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_collectible_flags'] ?? [] ) ) ) );
        $knife_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_moderation_flags'] ?? [] ) ) ) );
        $knife_axe_head_weight_g = sanitize_text_field( wp_unslash( $_POST['knife_axe_head_weight_g'] ?? '' ) );
        $knife_axe_handle_material = sanitize_text_field( wp_unslash( $_POST['knife_axe_handle_material'] ?? '' ) );
        $knife_multitool_function_count = sanitize_text_field( wp_unslash( $_POST['knife_multitool_function_count'] ?? '' ) );
        if ( $knife_blade_length_mm === '' && $knife_blade_length !== '' && is_numeric( $knife_blade_length ) ) {
            $knife_blade_length_mm = (string) ( (int) round( (float) $knife_blade_length * 10 ) );
        }
        if ( $knife_blade_length === '' && $knife_blade_length_mm !== '' && is_numeric( $knife_blade_length_mm ) ) {
            $knife_blade_length = (string) round( (float) $knife_blade_length_mm / 10, 1 );
        }
        $knife_filter_type = in_array( $knife_type, [ 'bicska', 'zsebkes' ], true ) ? 'bicska' : 'fix';
        $knife_filter_full_tang = ( $knife_construction === 'full-tang' ) ? '1' : '0';
        $knife_filter_bushcraft = ( $knife_type === 'bushcraft-kes' || strpos( ',' . $knife_usage . ',', ',bushcraft,' ) !== false ) ? '1' : '0';
        $knife_filter_vadasz = ( $knife_type === 'vadaszkes' || strpos( ',' . $knife_usage . ',', ',vadaszat,' ) !== false ) ? '1' : '0';
        $mixed_type = sanitize_key( wp_unslash( $_POST['mixed_type'] ?? '' ) );
        $mixed_barrel_count = sanitize_text_field( wp_unslash( $_POST['mixed_barrel_count'] ?? '' ) );
        $mixed_has_rifle = sanitize_key( wp_unslash( $_POST['mixed_has_rifle'] ?? '' ) );
        $mixed_rifle_caliber = sanitize_text_field( wp_unslash( $_POST['mixed_rifle_caliber'] ?? '' ) );
        $mixed_has_shotgun = sanitize_key( wp_unslash( $_POST['mixed_has_shotgun'] ?? '' ) );
        $mixed_shotgun_caliber = sanitize_text_field( wp_unslash( $_POST['mixed_shotgun_caliber'] ?? '' ) );
        $mixed_trigger_type = sanitize_key( wp_unslash( $_POST['mixed_trigger_type'] ?? '' ) );
        $mixed_action_system = sanitize_key( wp_unslash( $_POST['mixed_action_system'] ?? '' ) );
        $mixed_lock_type = sanitize_key( wp_unslash( $_POST['mixed_lock_type'] ?? '' ) );
        $mixed_sight_type = sanitize_key( wp_unslash( $_POST['mixed_sight_type'] ?? '' ) );
        $mixed_optic_rail = sanitize_key( wp_unslash( $_POST['mixed_optic_rail'] ?? '' ) );
        $mixed_optic_compatible = sanitize_key( wp_unslash( $_POST['mixed_optic_compatible'] ?? '' ) );
        $mixed_barrel_length_cm = sanitize_text_field( wp_unslash( $_POST['mixed_barrel_length_cm'] ?? '' ) );
        $mixed_barrel_material = sanitize_key( wp_unslash( $_POST['mixed_barrel_material'] ?? '' ) );
        $mixed_barrel_regulated = sanitize_key( wp_unslash( $_POST['mixed_barrel_regulated'] ?? '' ) );
        $mixed_material = sanitize_key( wp_unslash( $_POST['mixed_material'] ?? '' ) );
        $mixed_stock_material = sanitize_key( wp_unslash( $_POST['mixed_stock_material'] ?? '' ) );
        $mixed_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['mixed_usage'] ?? [] ) ) ) );
        $mixed_weight_kg = sanitize_text_field( wp_unslash( $_POST['mixed_weight_kg'] ?? '' ) );
        $mixed_total_length_cm = sanitize_text_field( wp_unslash( $_POST['mixed_total_length_cm'] ?? '' ) );
        $mixed_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['mixed_accessories'] ?? [] ) ) ) );
        $mixed_rifle_moa = sanitize_text_field( wp_unslash( $_POST['mixed_rifle_moa'] ?? '' ) );
        $mixed_shot_pattern = sanitize_text_field( wp_unslash( $_POST['mixed_shot_pattern'] ?? '' ) );
        $mixed_zero_distance_m = sanitize_text_field( wp_unslash( $_POST['mixed_zero_distance_m'] ?? '' ) );
        $mixed_safety_type = sanitize_key( wp_unslash( $_POST['mixed_safety_type'] ?? '' ) );
        $mixed_barrel_layout = sanitize_key( wp_unslash( $_POST['mixed_barrel_layout'] ?? '' ) );
        $mixed_barrel_internal = sanitize_text_field( wp_unslash( $_POST['mixed_barrel_internal'] ?? '' ) );
        $mixed_engraving = sanitize_text_field( wp_unslash( $_POST['mixed_engraving'] ?? '' ) );
        $mixed_factory_marking = sanitize_text_field( wp_unslash( $_POST['mixed_factory_marking'] ?? '' ) );
        $mixed_drilling_barrel1_caliber = sanitize_text_field( wp_unslash( $_POST['mixed_drilling_barrel1_caliber'] ?? '' ) );
        $mixed_drilling_top_barrel_role = sanitize_text_field( wp_unslash( $_POST['mixed_drilling_top_barrel_role'] ?? '' ) );
        $mixed_optic_mount_type = sanitize_text_field( wp_unslash( $_POST['mixed_optic_mount_type'] ?? '' ) );
        $mixed_mountain_weight_optimized = sanitize_key( wp_unslash( $_POST['mixed_mountain_weight_optimized'] ?? '' ) );
        $mixed_universal_label = sanitize_key( wp_unslash( $_POST['mixed_universal_label'] ?? '' ) );
        $mixed_condition = sanitize_key( wp_unslash( $_POST['mixed_condition'] ?? '' ) );
        $mixed_filter_optic = $mixed_optic_compatible === 'igen' ? '1' : '0';
        $mixed_filter_universal = $mixed_universal_label === 'igen' ? '1' : '0';
        $rifle_type = sanitize_key( wp_unslash( $_POST['rifle_type'] ?? '' ) );
        $rifle_caliber = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_caliber'] ?? [] ) ) ) );
        $rifle_barrel_length_cm = sanitize_text_field( wp_unslash( $_POST['rifle_barrel_length_cm'] ?? '' ) );
        $rifle_barrel_material = sanitize_key( wp_unslash( $_POST['rifle_barrel_material'] ?? '' ) );
        $rifle_rifling_type = sanitize_key( wp_unslash( $_POST['rifle_rifling_type'] ?? '' ) );
        $rifle_barrel_condition = sanitize_key( wp_unslash( $_POST['rifle_barrel_condition'] ?? '' ) );
        $rifle_accuracy_moa = sanitize_text_field( wp_unslash( $_POST['rifle_accuracy_moa'] ?? '' ) );
        $rifle_zero_distance_m = sanitize_text_field( wp_unslash( $_POST['rifle_zero_distance_m'] ?? '' ) );
        $rifle_factory_group = sanitize_text_field( wp_unslash( $_POST['rifle_factory_group'] ?? '' ) );
        $rifle_action_type = sanitize_key( wp_unslash( $_POST['rifle_action_type'] ?? '' ) );
        $rifle_trigger_type = sanitize_key( wp_unslash( $_POST['rifle_trigger_type'] ?? '' ) );
        $rifle_safety_type = sanitize_key( wp_unslash( $_POST['rifle_safety_type'] ?? '' ) );
        $rifle_stock_material = sanitize_key( wp_unslash( $_POST['rifle_stock_material'] ?? '' ) );
        $rifle_optic_rail_type = sanitize_key( wp_unslash( $_POST['rifle_optic_rail_type'] ?? '' ) );
        $rifle_optic_compatibility = sanitize_key( wp_unslash( $_POST['rifle_optic_compatibility'] ?? '' ) );
        $rifle_zero_return = sanitize_key( wp_unslash( $_POST['rifle_zero_return'] ?? '' ) );
        $rifle_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_accessories'] ?? [] ) ) ) );
        $rifle_weight_kg = sanitize_text_field( wp_unslash( $_POST['rifle_weight_kg'] ?? '' ) );
        $rifle_total_length_cm = sanitize_text_field( wp_unslash( $_POST['rifle_total_length_cm'] ?? '' ) );
        $rifle_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_usage'] ?? [] ) ) ) );
        $rifle_special_build = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_special_build'] ?? [] ) ) ) );
        $rifle_barrel_mount_type = sanitize_key( wp_unslash( $_POST['rifle_barrel_mount_type'] ?? '' ) );
        $rifle_impact_safe = sanitize_key( wp_unslash( $_POST['rifle_impact_safe'] ?? '' ) );
        $rifle_closed_system = sanitize_key( wp_unslash( $_POST['rifle_closed_system'] ?? '' ) );
        $rifle_effective_range_m = sanitize_text_field( wp_unslash( $_POST['rifle_effective_range_m'] ?? '' ) );
        $rifle_stability_level = sanitize_key( wp_unslash( $_POST['rifle_stability_level'] ?? '' ) );
        $rifle_recoil_level = sanitize_key( wp_unslash( $_POST['rifle_recoil_level'] ?? '' ) );
        $rifle_temperament = sanitize_key( wp_unslash( $_POST['rifle_temperament'] ?? '' ) );
        $rifle_condition = sanitize_key( wp_unslash( $_POST['rifle_condition'] ?? '' ) );
        $rifle_mountain_weight_optimized = sanitize_key( wp_unslash( $_POST['rifle_mountain_weight_optimized'] ?? '' ) );
        $rifle_magazine_capacity = sanitize_text_field( wp_unslash( $_POST['rifle_magazine_capacity'] ?? '' ) );
        $rifle_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_moderation_flags'] ?? [] ) ) ) );
        $rifle_hunter_profile_game = sanitize_text_field( wp_unslash( $_POST['rifle_hunter_profile_game'] ?? '' ) );
        $rifle_hunter_profile_optic = sanitize_text_field( wp_unslash( $_POST['rifle_hunter_profile_optic'] ?? '' ) );
        $rifle_hunter_profile_distance = sanitize_text_field( wp_unslash( $_POST['rifle_hunter_profile_distance'] ?? '' ) );
        $rifle_caliber_primary = '';
        if ( $rifle_caliber !== '' ) {
            $rifle_calibers = array_filter( array_map( 'trim', explode( ',', $rifle_caliber ) ) );
            $rifle_caliber_primary = (string) ( $rifle_calibers[0] ?? '' );
        }
        $rifle_filter_optic = $rifle_optic_compatibility === 'igen' ? '1' : '0';
        if ( $caliber === '' && $rifle_caliber_primary !== '' ) {
            $caliber = $rifle_caliber_primary;
        }
        $marok_type = sanitize_key( wp_unslash( $_POST['marok_type'] ?? '' ) );
        $marok_condition = sanitize_key( wp_unslash( $_POST['marok_condition'] ?? '' ) );
        $marok_action_type = sanitize_key( wp_unslash( $_POST['marok_action_type'] ?? '' ) );
        $marok_manufacture_year = sanitize_text_field( wp_unslash( $_POST['marok_manufacture_year'] ?? '' ) );
        $marok_origin_country = sanitize_text_field( wp_unslash( $_POST['marok_origin_country'] ?? '' ) );
        $marok_magazine_capacity = sanitize_text_field( wp_unslash( $_POST['marok_magazine_capacity'] ?? '' ) );
        $marok_cylinder_capacity = sanitize_text_field( wp_unslash( $_POST['marok_cylinder_capacity'] ?? '' ) );
        $marok_revolver_action = sanitize_key( wp_unslash( $_POST['marok_revolver_action'] ?? '' ) );
        $marok_barrel_length_mm = sanitize_text_field( wp_unslash( $_POST['marok_barrel_length_mm'] ?? '' ) );
        $marok_total_length_mm = sanitize_text_field( wp_unslash( $_POST['marok_total_length_mm'] ?? '' ) );
        $marok_weight_g = sanitize_text_field( wp_unslash( $_POST['marok_weight_g'] ?? '' ) );
        $marok_frame_material = sanitize_key( wp_unslash( $_POST['marok_frame_material'] ?? '' ) );
        $marok_slide_material = sanitize_key( wp_unslash( $_POST['marok_slide_material'] ?? '' ) );
        $marok_sight_type = sanitize_key( wp_unslash( $_POST['marok_sight_type'] ?? '' ) );
        $marok_optic_ready = sanitize_key( wp_unslash( $_POST['marok_optic_ready'] ?? '' ) );
        $marok_optic_mount = sanitize_key( wp_unslash( $_POST['marok_optic_mount'] ?? '' ) );
        $marok_red_dot_compatibility = sanitize_text_field( wp_unslash( $_POST['marok_red_dot_compatibility'] ?? '' ) );
        $marok_threaded_barrel = sanitize_key( wp_unslash( $_POST['marok_threaded_barrel'] ?? '' ) );
        $marok_rail_type = sanitize_key( wp_unslash( $_POST['marok_rail_type'] ?? '' ) );
        $marok_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_accessories'] ?? [] ) ) ) );
        $marok_license_required = sanitize_key( wp_unslash( $_POST['marok_license_required'] ?? '' ) );
        $marok_legal_category = sanitize_key( wp_unslash( $_POST['marok_legal_category'] ?? '' ) );
        $marok_technical_exam_valid = sanitize_key( wp_unslash( $_POST['marok_technical_exam_valid'] ?? '' ) );
        $marok_cip_marking = sanitize_key( wp_unslash( $_POST['marok_cip_marking'] ?? '' ) );
        $marok_transfer_license_only = sanitize_key( wp_unslash( $_POST['marok_transfer_license_only'] ?? '' ) );
        $marok_personal_pickup = sanitize_key( wp_unslash( $_POST['marok_personal_pickup'] ?? '' ) );
        $marok_serial_masked = sanitize_text_field( wp_unslash( $_POST['marok_serial_masked'] ?? '' ) );
        $marok_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_usage'] ?? [] ) ) ) );
        $marok_price_deal = sanitize_key( wp_unslash( $_POST['marok_price_deal'] ?? '' ) );
        $marok_price_exchange = sanitize_key( wp_unslash( $_POST['marok_price_exchange'] ?? '' ) );
        $marok_transfer_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_transfer_methods'] ?? [] ) ) ) );
        $marok_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_required_media'] ?? [] ) ) ) );
        $marok_recommended_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_recommended_media'] ?? [] ) ) ) );
        $marok_round_count = sanitize_key( wp_unslash( $_POST['marok_round_count'] ?? '' ) );
        $marok_trigger_tuning = sanitize_key( wp_unslash( $_POST['marok_trigger_tuning'] ?? '' ) );
        $marok_sport_categories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_sport_categories'] ?? [] ) ) ) );
        $marok_ipsc_ready = sanitize_key( wp_unslash( $_POST['marok_ipsc_ready'] ?? '' ) );
        $marok_race_trigger = sanitize_key( wp_unslash( $_POST['marok_race_trigger'] ?? '' ) );
        $marok_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_moderation_flags'] ?? [] ) ) ) );
        $marok_filter_optic_ready = ( $marok_optic_ready !== '' && $marok_optic_ready !== 'nincs' ) ? '1' : '0';
        $marok_filter_threaded_barrel = ( $marok_threaded_barrel === 'igen' ) ? '1' : '0';
        $marok_filter_magazine_capacity = $marok_magazine_capacity !== '' ? $marok_magazine_capacity : $marok_cylinder_capacity;
        $trophy_species = sanitize_key( wp_unslash( $_POST['trophy_species'] ?? '' ) );
        $trophy_type = sanitize_key( wp_unslash( $_POST['trophy_type'] ?? '' ) );
        $trophy_maker = sanitize_text_field( wp_unslash( $_POST['trophy_maker'] ?? '' ) );
        $trophy_custom_made = sanitize_key( wp_unslash( $_POST['trophy_custom_made'] ?? '' ) );
        $trophy_condition = sanitize_key( wp_unslash( $_POST['trophy_condition'] ?? '' ) );
        $trophy_manufacture_year = sanitize_text_field( wp_unslash( $_POST['trophy_manufacture_year'] ?? '' ) );
        $trophy_height_cm = sanitize_text_field( wp_unslash( $_POST['trophy_height_cm'] ?? '' ) );
        $trophy_width_cm = sanitize_text_field( wp_unslash( $_POST['trophy_width_cm'] ?? '' ) );
        $trophy_thickness_cm = sanitize_text_field( wp_unslash( $_POST['trophy_thickness_cm'] ?? '' ) );
        $trophy_compatible_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_compatible_species'] ?? [] ) ) ) );
        $trophy_mount_type = sanitize_key( wp_unslash( $_POST['trophy_mount_type'] ?? '' ) );
        if ( $trophy_type !== '' ) {
            $trophy_mount_type = $trophy_type;
        }
        $trophy_mounting_type = sanitize_key( wp_unslash( $_POST['trophy_mounting_type'] ?? '' ) );
        $trophy_attachment_type = sanitize_key( wp_unslash( $_POST['trophy_attachment_type'] ?? '' ) );
        $trophy_style = sanitize_key( wp_unslash( $_POST['trophy_style'] ?? '' ) );
        $trophy_fang_mount = sanitize_key( wp_unslash( $_POST['trophy_fang_mount'] ?? '' ) );
        $trophy_material_type = sanitize_key( wp_unslash( $_POST['trophy_material_type'] ?? '' ) );
        $trophy_material = sanitize_key( wp_unslash( $_POST['trophy_material'] ?? '' ) );
        if ( $trophy_material_type !== '' ) {
            $trophy_material = $trophy_material_type;
        }
        $trophy_surface_finish = sanitize_key( wp_unslash( $_POST['trophy_surface_finish'] ?? '' ) );
        $trophy_finish = sanitize_key( wp_unslash( $_POST['trophy_finish'] ?? '' ) );
        if ( $trophy_surface_finish !== '' ) {
            $trophy_finish = $trophy_surface_finish;
        }
        $trophy_decoration_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_decoration_flags'] ?? [] ) ) ) );
        $trophy_weight_kg = sanitize_text_field( wp_unslash( $_POST['trophy_weight_kg'] ?? '' ) );
        $trophy_usage_context = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_usage_context'] ?? [] ) ) ) );
        $trophy_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_accessories'] ?? [] ) ) ) );
        $trophy_price_negotiable = sanitize_key( wp_unslash( $_POST['trophy_price_negotiable'] ?? '' ) );
        $trophy_custom_order = sanitize_key( wp_unslash( $_POST['trophy_custom_order'] ?? '' ) );
        $trophy_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_shipping_methods'] ?? [] ) ) ) );
        $trophy_fragile_handling = sanitize_key( wp_unslash( $_POST['trophy_fragile_handling'] ?? '' ) );
        $trophy_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_required_media'] ?? [] ) ) ) );
        $trophy_lead_time = sanitize_text_field( wp_unslash( $_POST['trophy_lead_time'] ?? '' ) );
        $trophy_orderable_sizes = sanitize_text_field( wp_unslash( $_POST['trophy_orderable_sizes'] ?? '' ) );
        $trophy_custom_engraving = sanitize_key( wp_unslash( $_POST['trophy_custom_engraving'] ?? '' ) );
        $trophy_house_style = sanitize_key( wp_unslash( $_POST['trophy_house_style'] ?? '' ) );
        $trophy_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_moderation_flags'] ?? [] ) ) ) );
        if ( $trophy_species === '' && $trophy_compatible_species !== '' ) {
            $trophy_species_list = array_filter( array_map( 'trim', explode( ',', $trophy_compatible_species ) ) );
            $trophy_species = $trophy_species_list[0] ?? '';
        }
        $accommodation_type = sanitize_key( wp_unslash( $_POST['accommodation_type'] ?? '' ) );
        $accommodation_capacity = sanitize_text_field( wp_unslash( $_POST['accommodation_capacity'] ?? '' ) );
        $accommodation_slot_mode = sanitize_key( wp_unslash( $_POST['accommodation_slot_mode'] ?? '' ) );
        $accommodation_from_hour = sanitize_text_field( wp_unslash( $_POST['accommodation_from_hour'] ?? '' ) );
        $accommodation_to_hour = sanitize_text_field( wp_unslash( $_POST['accommodation_to_hour'] ?? '' ) );
        $accommodation_checkin_from = sanitize_text_field( wp_unslash( $_POST['accommodation_checkin_from'] ?? '' ) );
        $accommodation_checkin_to = sanitize_text_field( wp_unslash( $_POST['accommodation_checkin_to'] ?? '' ) );
        $accommodation_checkout_from = sanitize_text_field( wp_unslash( $_POST['accommodation_checkout_from'] ?? '' ) );
        $accommodation_checkout_to = sanitize_text_field( wp_unslash( $_POST['accommodation_checkout_to'] ?? '' ) );
        $accommodation_meal_type = sanitize_key( wp_unslash( $_POST['accommodation_meal_type'] ?? '' ) );
        $accommodation_parking = sanitize_key( wp_unslash( $_POST['accommodation_parking'] ?? '' ) );
        $accommodation_settlement = sanitize_text_field( wp_unslash( $_POST['accommodation_settlement'] ?? '' ) );
        $accommodation_county_text = sanitize_text_field( wp_unslash( $_POST['accommodation_county_text'] ?? '' ) );
        $accommodation_country = sanitize_text_field( wp_unslash( $_POST['accommodation_country'] ?? '' ) );
        $accommodation_exact_address = sanitize_text_field( wp_unslash( $_POST['accommodation_exact_address'] ?? '' ) );
        $accommodation_adult_capacity = sanitize_text_field( wp_unslash( $_POST['accommodation_adult_capacity'] ?? '' ) );
        $accommodation_room_count = sanitize_text_field( wp_unslash( $_POST['accommodation_room_count'] ?? '' ) );
        $accommodation_bed_count = sanitize_text_field( wp_unslash( $_POST['accommodation_bed_count'] ?? '' ) );
        $accommodation_extra_bed = sanitize_key( wp_unslash( $_POST['accommodation_extra_bed'] ?? '' ) );
        $accommodation_hunting_nearby = sanitize_key( wp_unslash( $_POST['accommodation_hunting_nearby'] ?? '' ) );
        $accommodation_hunt_available = sanitize_key( wp_unslash( $_POST['accommodation_hunt_available'] ?? '' ) );
        $accommodation_trophy_handling = sanitize_key( wp_unslash( $_POST['accommodation_trophy_handling'] ?? '' ) );
        $accommodation_cold_room = sanitize_key( wp_unslash( $_POST['accommodation_cold_room'] ?? '' ) );
        $accommodation_gun_storage = sanitize_key( wp_unslash( $_POST['accommodation_gun_storage'] ?? '' ) );
        $accommodation_game_processing = sanitize_key( wp_unslash( $_POST['accommodation_game_processing'] ?? '' ) );
        $accommodation_offroad_parking = sanitize_key( wp_unslash( $_POST['accommodation_offroad_parking'] ?? '' ) );
        $accommodation_dog_allowed = sanitize_key( wp_unslash( $_POST['accommodation_dog_allowed'] ?? '' ) );
        $accommodation_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_features'] ?? [] ) ) ) );
        $accommodation_min_nights = sanitize_text_field( wp_unslash( $_POST['accommodation_min_nights'] ?? '' ) );
        $accommodation_bookable_period = sanitize_text_field( wp_unslash( $_POST['accommodation_bookable_period'] ?? '' ) );
        $accommodation_instant_book = sanitize_key( wp_unslash( $_POST['accommodation_instant_book'] ?? '' ) );
        $accommodation_nearby_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_nearby_species'] ?? [] ) ) ) );
        $accommodation_programs = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_programs'] ?? [] ) ) ) );
        $accommodation_environment = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_environment'] ?? [] ) ) ) );
        $accommodation_land_size_sqm = sanitize_text_field( wp_unslash( $_POST['accommodation_land_size_sqm'] ?? '' ) );
        $accommodation_land_size_hectare = sanitize_text_field( wp_unslash( $_POST['accommodation_land_size_hectare'] ?? '' ) );
        $accommodation_building_size_sqm = sanitize_text_field( wp_unslash( $_POST['accommodation_building_size_sqm'] ?? '' ) );
        $accommodation_levels_count = sanitize_text_field( wp_unslash( $_POST['accommodation_levels_count'] ?? '' ) );
        $accommodation_build_year = sanitize_text_field( wp_unslash( $_POST['accommodation_build_year'] ?? '' ) );
        $accommodation_property_condition = sanitize_key( wp_unslash( $_POST['accommodation_property_condition'] ?? '' ) );
        $accommodation_construction_type = sanitize_key( wp_unslash( $_POST['accommodation_construction_type'] ?? '' ) );
        $accommodation_heating_type = sanitize_key( wp_unslash( $_POST['accommodation_heating_type'] ?? '' ) );
        $accommodation_utilities = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_utilities'] ?? [] ) ) ) );
        $accommodation_accessibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_accessibility'] ?? [] ) ) ) );
        $accommodation_hunting_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_hunting_features'] ?? [] ) ) ) );
        $accommodation_extras = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_extras'] ?? [] ) ) ) );
        $accommodation_network_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_network_features'] ?? [] ) ) ) );
        $accommodation_use_type = sanitize_key( wp_unslash( $_POST['accommodation_use_type'] ?? '' ) );
        $accommodation_terrain_type = sanitize_key( wp_unslash( $_POST['accommodation_terrain_type'] ?? '' ) );
        $accommodation_animal_housing = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_animal_housing'] ?? [] ) ) ) );
        $accommodation_fafaj = sanitize_text_field( wp_unslash( $_POST['accommodation_fafaj'] ?? '' ) );
        $accommodation_erdo_kora = sanitize_text_field( wp_unslash( $_POST['accommodation_erdo_kora'] ?? '' ) );
        $accommodation_vadaszhaz_trofeaszoba = sanitize_key( wp_unslash( $_POST['accommodation_vadaszhaz_trofeaszoba'] ?? '' ) );
        $accommodation_vadaszhaz_fegyvertarolo = sanitize_key( wp_unslash( $_POST['accommodation_vadaszhaz_fegyvertarolo'] ?? '' ) );
        $accommodation_tanya_gazdasagi_epuletek = sanitize_text_field( wp_unslash( $_POST['accommodation_tanya_gazdasagi_epuletek'] ?? '' ) );
        $accommodation_hunting_potential_wild_movement = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_wild_movement'] ?? '' ) );
        $accommodation_hunting_potential_isolation = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_isolation'] ?? '' ) );
        $accommodation_hunting_potential_forest_proximity = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_forest_proximity'] ?? '' ) );
        $accommodation_hunting_potential_infrastructure = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_infrastructure'] ?? '' ) );
        $accommodation_filter_property_type = $accommodation_type;
        $accommodation_filter_county = sanitize_title( $accommodation_county_text );
        $accommodation_filter_land_size_sqm = $accommodation_land_size_sqm;
        $accommodation_filter_building_size_sqm = $accommodation_building_size_sqm;
        $accommodation_filter_utilities = $accommodation_utilities;
        $accommodation_filter_accessibility = $accommodation_accessibility;
        $accommodation_filter_hunting_features = $accommodation_hunting_features;
        $accommodation_filter_condition = $accommodation_property_condition;
        $hunt_slot_from_hour = sanitize_text_field( wp_unslash( $_POST['hunt_slot_from_hour'] ?? '' ) );
        $hunt_slot_to_hour = sanitize_text_field( wp_unslash( $_POST['hunt_slot_to_hour'] ?? '' ) );
        $hunt_capacity = sanitize_text_field( wp_unslash( $_POST['hunt_capacity'] ?? '' ) );
        $hunt_species_list = sanitize_text_field( wp_unslash( $_POST['hunt_species_list'] ?? '' ) );
        $hunt_lease_type = sanitize_key( wp_unslash( $_POST['hunt_lease_type'] ?? '' ) );
        $hunt_has_guide = sanitize_key( wp_unslash( $_POST['hunt_has_guide'] ?? '' ) );
        $hunt_has_tracking = sanitize_key( wp_unslash( $_POST['hunt_has_tracking'] ?? '' ) );
        $hunt_has_trophy_prep = sanitize_key( wp_unslash( $_POST['hunt_has_trophy_prep'] ?? '' ) );
        $hunt_has_trophy_judging = sanitize_key( wp_unslash( $_POST['hunt_has_trophy_judging'] ?? '' ) );
        $hunt_has_offroad = sanitize_key( wp_unslash( $_POST['hunt_has_offroad'] ?? '' ) );
        $hunt_has_accommodation = sanitize_key( wp_unslash( $_POST['hunt_has_accommodation'] ?? '' ) );
        $hunt_can_buy_game = sanitize_key( wp_unslash( $_POST['hunt_can_buy_game'] ?? '' ) );
        $hunt_type = sanitize_key( wp_unslash( $_POST['hunt_type'] ?? '' ) );
        $hunt_game_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_game_species'] ?? [] ) ) ) );
        $hunt_country = sanitize_text_field( wp_unslash( $_POST['hunt_country'] ?? '' ) );
        $hunt_county = sanitize_text_field( wp_unslash( $_POST['hunt_county'] ?? '' ) );
        $hunt_area_name = sanitize_text_field( wp_unslash( $_POST['hunt_area_name'] ?? '' ) );
        $hunt_exact_area = sanitize_text_field( wp_unslash( $_POST['hunt_exact_area'] ?? '' ) );
        $hunt_gps_lat = sanitize_text_field( wp_unslash( $_POST['hunt_gps_lat'] ?? '' ) );
        $hunt_gps_lng = sanitize_text_field( wp_unslash( $_POST['hunt_gps_lng'] ?? '' ) );
        $hunt_terrain_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_terrain_types'] ?? [] ) ) ) );
        $hunt_season_start = sanitize_text_field( wp_unslash( $_POST['hunt_season_start'] ?? '' ) );
        $hunt_season_end = sanitize_text_field( wp_unslash( $_POST['hunt_season_end'] ?? '' ) );
        $hunt_time_type = sanitize_key( wp_unslash( $_POST['hunt_time_type'] ?? '' ) );
        $hunt_price_type = sanitize_key( wp_unslash( $_POST['hunt_price_type'] ?? '' ) );
        $hunt_price_min = sanitize_text_field( wp_unslash( $_POST['hunt_price_min'] ?? '' ) );
        $hunt_price_max = sanitize_text_field( wp_unslash( $_POST['hunt_price_max'] ?? '' ) );
        $hunt_price_includes = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_price_includes'] ?? [] ) ) ) );
        $hunt_guide_type = sanitize_key( wp_unslash( $_POST['hunt_guide_type'] ?? '' ) );
        $hunt_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_methods'] ?? [] ) ) ) );
        $hunt_onsite_services = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_onsite_services'] ?? [] ) ) ) );
        $hunt_hunting_license_required = sanitize_key( wp_unslash( $_POST['hunt_hunting_license_required'] ?? '' ) );
        $hunt_permit_required = sanitize_key( wp_unslash( $_POST['hunt_permit_required'] ?? '' ) );
        $hunt_foreigners_allowed = sanitize_key( wp_unslash( $_POST['hunt_foreigners_allowed'] ?? '' ) );
        $hunt_min_age = sanitize_text_field( wp_unslash( $_POST['hunt_min_age'] ?? '' ) );
        $hunt_safety_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_safety_features'] ?? [] ) ) ) );
        $hunt_conditions = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_conditions'] ?? [] ) ) ) );
        $hunt_difficulty_level = sanitize_key( wp_unslash( $_POST['hunt_difficulty_level'] ?? '' ) );
        $hunt_expected_trophy_quality = sanitize_key( wp_unslash( $_POST['hunt_expected_trophy_quality'] ?? '' ) );
        $hunt_trophy_weight_age = sanitize_text_field( wp_unslash( $_POST['hunt_trophy_weight_age'] ?? '' ) );
        $hunt_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_required_media'] ?? [] ) ) ) );
        $hunt_video_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_video_types'] ?? [] ) ) ) );
        $hunt_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_moderation_flags'] ?? [] ) ) ) );
        $hunt_trophy_category = sanitize_text_field( wp_unslash( $_POST['hunt_trophy_category'] ?? '' ) );
        $hunt_trophy_weight_limit = sanitize_text_field( wp_unslash( $_POST['hunt_trophy_weight_limit'] ?? '' ) );
        $hunt_travel_assist = sanitize_key( wp_unslash( $_POST['hunt_travel_assist'] ?? '' ) );
        $hunt_interpreter = sanitize_key( wp_unslash( $_POST['hunt_interpreter'] ?? '' ) );
        $hunt_group_size = sanitize_text_field( wp_unslash( $_POST['hunt_group_size'] ?? '' ) );
        $hunt_shot_limit = sanitize_text_field( wp_unslash( $_POST['hunt_shot_limit'] ?? '' ) );
        $hunt_real_time_slots = sanitize_text_field( wp_unslash( $_POST['hunt_real_time_slots'] ?? '' ) );
        $hunt_bookable_dates = sanitize_text_field( wp_unslash( $_POST['hunt_bookable_dates'] ?? '' ) );
        $hunt_damage_main_type = sanitize_key( wp_unslash( $_POST['hunt_damage_main_type'] ?? '' ) );
        $hunt_settlement = sanitize_text_field( wp_unslash( $_POST['hunt_settlement'] ?? '' ) );
        $hunt_land_type = sanitize_key( wp_unslash( $_POST['hunt_land_type'] ?? '' ) );
        $hunt_response_time = sanitize_key( wp_unslash( $_POST['hunt_response_time'] ?? '' ) );
        $hunt_availability_mode = sanitize_key( wp_unslash( $_POST['hunt_availability_mode'] ?? '' ) );
        $hunt_executor_type = sanitize_key( wp_unslash( $_POST['hunt_executor_type'] ?? '' ) );
        $hunt_executor_capacity = sanitize_key( wp_unslash( $_POST['hunt_executor_capacity'] ?? '' ) );
        $hunt_tools = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_tools'] ?? [] ) ) ) );
        $hunt_weapon_use_required = sanitize_key( wp_unslash( $_POST['hunt_weapon_use_required'] ?? '' ) );
        $hunt_land_manager_permit = sanitize_key( wp_unslash( $_POST['hunt_land_manager_permit'] ?? '' ) );
        $hunt_hunting_company_coop = sanitize_key( wp_unslash( $_POST['hunt_hunting_company_coop'] ?? '' ) );
        $hunt_liability_insurance = sanitize_key( wp_unslash( $_POST['hunt_liability_insurance'] ?? '' ) );
        $hunt_damage_nature = sanitize_key( wp_unslash( $_POST['hunt_damage_nature'] ?? '' ) );
        $hunt_pricing_model = sanitize_key( wp_unslash( $_POST['hunt_pricing_model'] ?? '' ) );
        $hunt_travel_fee = sanitize_text_field( wp_unslash( $_POST['hunt_travel_fee'] ?? '' ) );
        $hunt_contract_type = sanitize_key( wp_unslash( $_POST['hunt_contract_type'] ?? '' ) );
        $hunt_site_conditions = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_site_conditions'] ?? [] ) ) ) );
        $hunt_reference_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_reference_media'] ?? [] ) ) ) );
        $hunt_agri_hectare_size = sanitize_text_field( wp_unslash( $_POST['hunt_agri_hectare_size'] ?? '' ) );
        $hunt_agri_culture_type = sanitize_text_field( wp_unslash( $_POST['hunt_agri_culture_type'] ?? '' ) );
        $hunt_urgent_immediate_start = sanitize_key( wp_unslash( $_POST['hunt_urgent_immediate_start'] ?? '' ) );
        $hunt_urgent_duty_phone = sanitize_text_field( wp_unslash( $_POST['hunt_urgent_duty_phone'] ?? '' ) );
        $hunt_long_term_yearly_discount = sanitize_text_field( wp_unslash( $_POST['hunt_long_term_yearly_discount'] ?? '' ) );
        $hunt_package_auto_seasonal = sanitize_key( wp_unslash( $_POST['hunt_package_auto_seasonal'] ?? '' ) );
        $hunt_package_monthly_model = sanitize_key( wp_unslash( $_POST['hunt_package_monthly_model'] ?? '' ) );
        $hunt_package_monitoring_intervention = sanitize_key( wp_unslash( $_POST['hunt_package_monitoring_intervention'] ?? '' ) );
        if ( $hunt_species_list === '' && $hunt_game_species !== '' ) {
            $hunt_species_list = $hunt_game_species;
        }
        if ( $hunt_has_guide === '' && $hunt_guide_type !== '' ) {
            $hunt_has_guide = $hunt_guide_type === 'nincs' ? 'nem' : 'igen';
        }
        if ( $hunt_price_type === '' && $hunt_pricing_model !== '' ) {
            $hunt_price_type = $hunt_pricing_model;
        }
        $hunt_response_time_hours = self::map_response_time_to_hours( $hunt_response_time );
        $hunt_availability_24_7 = $hunt_availability_mode === '0-24' ? '1' : '0';
        $hunt_method_types = $hunt_methods;
        $exchange_target = sanitize_text_field( wp_unslash( $_POST['exchange_target'] ?? '' ) );
        $exchange_type = sanitize_key( wp_unslash( $_POST['exchange_type'] ?? '' ) );
        $exchange_offer_category = sanitize_key( wp_unslash( $_POST['exchange_offer_category'] ?? '' ) );
        $exchange_item_name = sanitize_text_field( wp_unslash( $_POST['exchange_item_name'] ?? '' ) );
        $exchange_brand = sanitize_text_field( wp_unslash( $_POST['exchange_brand'] ?? '' ) );
        $exchange_model = sanitize_text_field( wp_unslash( $_POST['exchange_model'] ?? '' ) );
        $exchange_condition = sanitize_key( wp_unslash( $_POST['exchange_condition'] ?? '' ) );
        $exchange_short_desc = sanitize_textarea_field( wp_unslash( $_POST['exchange_short_desc'] ?? '' ) );
        $exchange_description = sanitize_textarea_field( wp_unslash( $_POST['exchange_description'] ?? '' ) );
        $exchange_defects = sanitize_textarea_field( wp_unslash( $_POST['exchange_defects'] ?? '' ) );
        $exchange_wear_signs = sanitize_textarea_field( wp_unslash( $_POST['exchange_wear_signs'] ?? '' ) );
        $exchange_estimated_value = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['exchange_estimated_value'] ?? '' ) );
        $exchange_extra_payment_direction = sanitize_key( wp_unslash( $_POST['exchange_extra_payment_direction'] ?? '' ) );
        $exchange_extra_payment_amount = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['exchange_extra_payment_amount'] ?? '' ) );
        $exchange_wanted_categories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['exchange_wanted_categories'] ?? [] ) ) ) );
        $exchange_country = sanitize_text_field( wp_unslash( $_POST['exchange_country'] ?? '' ) );
        $exchange_county = sanitize_text_field( wp_unslash( $_POST['exchange_county'] ?? '' ) );
        $exchange_city = sanitize_text_field( wp_unslash( $_POST['exchange_city'] ?? '' ) );
        $exchange_method = sanitize_key( wp_unslash( $_POST['exchange_method'] ?? '' ) );
        $exchange_legal_notes = sanitize_textarea_field( wp_unslash( $_POST['exchange_legal_notes'] ?? '' ) );
        $exchange_age_18 = sanitize_key( wp_unslash( $_POST['exchange_age_18'] ?? '' ) );
        $exchange_required_images = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['exchange_required_images'] ?? [] ) ) ) );
        $exchange_video_url = esc_url_raw( wp_unslash( $_POST['exchange_video_url'] ?? '' ) );
        $exchange_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['exchange_moderation_flags'] ?? [] ) ) ) );
        $exchange_partner_requirements = sanitize_textarea_field( wp_unslash( $_POST['exchange_partner_requirements'] ?? '' ) );
        $exchange_firearm_caliber = sanitize_text_field( wp_unslash( $_POST['exchange_firearm_caliber'] ?? '' ) );
        $exchange_firearm_license = sanitize_key( wp_unslash( $_POST['exchange_firearm_license'] ?? '' ) );
        $exchange_optic_zoom = sanitize_text_field( wp_unslash( $_POST['exchange_optic_zoom'] ?? '' ) );
        $exchange_optic_objective = sanitize_text_field( wp_unslash( $_POST['exchange_optic_objective'] ?? '' ) );
        $exchange_knife_steel = sanitize_text_field( wp_unslash( $_POST['exchange_knife_steel'] ?? '' ) );
        $publication_type = sanitize_key( wp_unslash( $_POST['publication_type'] ?? '' ) );
        $publication_topics = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_topics'] ?? [] ) ) ) );
        $publication_title = sanitize_text_field( wp_unslash( $_POST['publication_title'] ?? '' ) );
        $publication_subtitle = sanitize_text_field( wp_unslash( $_POST['publication_subtitle'] ?? '' ) );
        $publication_author = sanitize_text_field( wp_unslash( $_POST['publication_author'] ?? '' ) );
        $publication_publisher = sanitize_text_field( wp_unslash( $_POST['publication_publisher'] ?? '' ) );
        $publication_year = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['publication_year'] ?? '' ) );
        $publication_language = sanitize_key( wp_unslash( $_POST['publication_language'] ?? '' ) );
        $publication_edition_type = sanitize_key( wp_unslash( $_POST['publication_edition_type'] ?? '' ) );
        $publication_periodical_name = sanitize_text_field( wp_unslash( $_POST['publication_periodical_name'] ?? '' ) );
        $publication_volume = sanitize_text_field( wp_unslash( $_POST['publication_volume'] ?? '' ) );
        $publication_issue = sanitize_text_field( wp_unslash( $_POST['publication_issue'] ?? '' ) );
        $publication_complete_volume = sanitize_key( wp_unslash( $_POST['publication_complete_volume'] ?? '' ) );
        $publication_page_count = sanitize_text_field( wp_unslash( $_POST['publication_page_count'] ?? '' ) );
        $publication_cover_type = sanitize_key( wp_unslash( $_POST['publication_cover_type'] ?? '' ) );
        $publication_size = sanitize_text_field( wp_unslash( $_POST['publication_size'] ?? '' ) );
        $publication_condition = sanitize_key( wp_unslash( $_POST['publication_condition'] ?? '' ) );
        $publication_defects = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_defects'] ?? [] ) ) ) );
        $publication_content_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_content_flags'] ?? [] ) ) ) );
        $publication_collector_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_collector_flags'] ?? [] ) ) ) );
        $publication_collector_value = sanitize_key( wp_unslash( $_POST['publication_collector_value'] ?? '' ) );
        $publication_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_usage'] ?? [] ) ) ) );
        $publication_condition_details = sanitize_textarea_field( wp_unslash( $_POST['publication_condition_details'] ?? '' ) );
        $publication_authenticity = sanitize_key( wp_unslash( $_POST['publication_authenticity'] ?? '' ) );
        $publication_signed_copy = sanitize_key( wp_unslash( $_POST['publication_signed_copy'] ?? '' ) );
        $publication_signed_to = sanitize_text_field( wp_unslash( $_POST['publication_signed_to'] ?? '' ) );
        $publication_signature_type = sanitize_key( wp_unslash( $_POST['publication_signature_type'] ?? '' ) );
        $publication_isbn = sanitize_text_field( wp_unslash( $_POST['publication_isbn'] ?? '' ) );
        $publication_digital_format = sanitize_key( wp_unslash( $_POST['publication_digital_format'] ?? '' ) );
        $publication_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_moderation_flags'] ?? [] ) ) ) );
        $publication_notes = sanitize_textarea_field( wp_unslash( $_POST['publication_notes'] ?? '' ) );
        if ( $publication_signed_copy === '' && $publication_edition_type === 'dedikalt' ) {
            $publication_signed_copy = 'igen';
        }
        $publication_filter_collector_item = ( in_array( $publication_collector_value, [ 'magas', 'kiemelt' ], true ) || strpos( ',' . $publication_collector_flags . ',', ',ritka-kiadas,' ) !== false || strpos( ',' . $publication_collector_flags . ',', ',muzealis,' ) !== false || in_array( $publication_edition_type, [ 'gyujtoi', 'limitalt', 'antikvar' ], true ) ) ? '1' : '0';
        $publication_filter_complete_volume = $publication_complete_volume === 'igen' ? '1' : '0';
        $publication_review_hits = array_values( array_filter( array_map( 'trim', explode( ',', $publication_moderation_flags ) ) ) );
        $equipment_type = sanitize_key( wp_unslash( $_POST['equipment_type'] ?? '' ) );
        $equipment_condition = sanitize_key( wp_unslash( $_POST['equipment_condition'] ?? '' ) );
        $equipment_hunting_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_hunting_usage'] ?? [] ) ) ) );
        $equipment_material_type = sanitize_key( wp_unslash( $_POST['equipment_material_type'] ?? '' ) );
        $equipment_color_pattern = sanitize_key( wp_unslash( $_POST['equipment_color_pattern'] ?? '' ) );
        $equipment_length_cm = sanitize_text_field( wp_unslash( $_POST['equipment_length_cm'] ?? '' ) );
        $equipment_width_cm = sanitize_text_field( wp_unslash( $_POST['equipment_width_cm'] ?? '' ) );
        $equipment_height_cm = sanitize_text_field( wp_unslash( $_POST['equipment_height_cm'] ?? '' ) );
        $equipment_weight_g = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_weight_g'] ?? '' ) );
        $equipment_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_features'] ?? [] ) ) ) );
        $equipment_light_lumen = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_light_lumen'] ?? '' ) );
        $equipment_light_range_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_light_range_m'] ?? '' ) );
        $equipment_light_battery_type = sanitize_text_field( wp_unslash( $_POST['equipment_light_battery_type'] ?? '' ) );
        $equipment_light_usb_charge = sanitize_key( wp_unslash( $_POST['equipment_light_usb_charge'] ?? '' ) );
        $equipment_light_color_modes = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_light_color_modes'] ?? [] ) ) ) );
        $equipment_light_color_temperature = sanitize_text_field( wp_unslash( $_POST['equipment_light_color_temperature'] ?? '' ) );
        $equipment_support_height_range_cm = sanitize_text_field( wp_unslash( $_POST['equipment_support_height_range_cm'] ?? '' ) );
        $equipment_support_legs = sanitize_text_field( wp_unslash( $_POST['equipment_support_legs'] ?? '' ) );
        $equipment_support_head_swivel = sanitize_key( wp_unslash( $_POST['equipment_support_head_swivel'] ?? '' ) );
        $equipment_support_material = sanitize_key( wp_unslash( $_POST['equipment_support_material'] ?? '' ) );
        $equipment_call_type = sanitize_key( wp_unslash( $_POST['equipment_call_type'] ?? '' ) );
        $equipment_call_species = sanitize_text_field( wp_unslash( $_POST['equipment_call_species'] ?? '' ) );
        $equipment_call_volume = sanitize_text_field( wp_unslash( $_POST['equipment_call_volume'] ?? '' ) );
        $equipment_trap_type = sanitize_key( wp_unslash( $_POST['equipment_trap_type'] ?? '' ) );
        $equipment_trap_size = sanitize_text_field( wp_unslash( $_POST['equipment_trap_size'] ?? '' ) );
        $equipment_cleaning_caliber_compatibility = sanitize_text_field( wp_unslash( $_POST['equipment_cleaning_caliber_compatibility'] ?? '' ) );
        $equipment_cleaning_full_set = sanitize_key( wp_unslash( $_POST['equipment_cleaning_full_set'] ?? '' ) );
        $equipment_cleaning_oil = sanitize_key( wp_unslash( $_POST['equipment_cleaning_oil'] ?? '' ) );
        $equipment_cleaning_bronze_brush = sanitize_key( wp_unslash( $_POST['equipment_cleaning_bronze_brush'] ?? '' ) );
        $equipment_seat_foldable = sanitize_key( wp_unslash( $_POST['equipment_seat_foldable'] ?? '' ) );
        $equipment_seat_backrest = sanitize_key( wp_unslash( $_POST['equipment_seat_backrest'] ?? '' ) );
        $equipment_seat_swivel = sanitize_key( wp_unslash( $_POST['equipment_seat_swivel'] ?? '' ) );
        $equipment_seat_capacity_kg = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_seat_capacity_kg'] ?? '' ) );
        $equipment_electronic_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_electronic_features'] ?? [] ) ) ) );
        $equipment_runtime_hours = sanitize_text_field( wp_unslash( $_POST['equipment_runtime_hours'] ?? '' ) );
        $equipment_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_accessories'] ?? [] ) ) ) );
        $equipment_silence_level = sanitize_key( wp_unslash( $_POST['equipment_silence_level'] ?? '' ) );
        $equipment_weapon_compatibility = sanitize_text_field( wp_unslash( $_POST['equipment_weapon_compatibility'] ?? '' ) );
        $equipment_caliber_compatibility = sanitize_text_field( wp_unslash( $_POST['equipment_caliber_compatibility'] ?? '' ) );
        $equipment_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_moderation_flags'] ?? [] ) ) ) );
        $equipment_notes = sanitize_textarea_field( wp_unslash( $_POST['equipment_notes'] ?? '' ) );
        $other_equipment_type = sanitize_key( wp_unslash( $_POST['other_equipment_type'] ?? '' ) );
        $other_equipment_function = sanitize_text_field( wp_unslash( $_POST['other_equipment_function'] ?? '' ) );
        $other_equipment_material_note = sanitize_text_field( wp_unslash( $_POST['other_equipment_material_note'] ?? '' ) );
        $other_equipment_compatibility = sanitize_text_field( wp_unslash( $_POST['other_equipment_compatibility'] ?? '' ) );
        $other_hunting_relevance = sanitize_key( wp_unslash( $_POST['other_hunting_relevance'] ?? '' ) );
        $other_uncertain_classification = sanitize_key( wp_unslash( $_POST['other_uncertain_classification'] ?? '' ) );
        $other_auto_suggested_category = sanitize_key( wp_unslash( $_POST['other_auto_suggested_category'] ?? '' ) );
        $other_auto_suggested_reason = sanitize_text_field( wp_unslash( $_POST['other_auto_suggested_reason'] ?? '' ) );
        $light_type = sanitize_key( wp_unslash( $_POST['light_type'] ?? '' ) );
        $lumens = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['lumens'] ?? '' ) );
        $light_levels_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['light_levels_count'] ?? '' ) );
        $light_turbo_mode = sanitize_key( wp_unslash( $_POST['light_turbo_mode'] ?? '' ) );
        $light_eco_mode = sanitize_key( wp_unslash( $_POST['light_eco_mode'] ?? '' ) );
        $beam_distance_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['beam_distance_m'] ?? '' ) );
        $beam_distance_band = sanitize_key( wp_unslash( $_POST['beam_distance_band'] ?? '' ) );
        $light_color = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_color'] ?? [] ) ) ) );
        $battery_type = sanitize_key( wp_unslash( $_POST['battery_type'] ?? '' ) );
        $light_usb_rechargeable = sanitize_key( wp_unslash( $_POST['light_usb_rechargeable'] ?? '' ) );
        $light_powerbank_compatible = sanitize_key( wp_unslash( $_POST['light_powerbank_compatible'] ?? '' ) );
        $runtime_hours = sanitize_text_field( wp_unslash( $_POST['runtime_hours'] ?? '' ) );
        $runtime_eco_hours = sanitize_text_field( wp_unslash( $_POST['runtime_eco_hours'] ?? '' ) );
        $runtime_turbo_hours = sanitize_text_field( wp_unslash( $_POST['runtime_turbo_hours'] ?? '' ) );
        $light_material = sanitize_key( wp_unslash( $_POST['light_material'] ?? '' ) );
        $light_ip_rating = sanitize_key( wp_unslash( $_POST['light_ip_rating'] ?? '' ) );
        $light_resistance = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_resistance'] ?? [] ) ) ) );
        $mount_type = sanitize_key( wp_unslash( $_POST['mount_type'] ?? '' ) );
        $light_mount_systems = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_mount_systems'] ?? [] ) ) ) );
        $hunting_light_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunting_light_usage'] ?? [] ) ) ) );
        $light_functions = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_functions'] ?? [] ) ) ) );
        $light_optical_properties = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_optical_properties'] ?? [] ) ) ) );
        $light_weapon_type_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_weapon_type_compatibility'] ?? [] ) ) ) );
        $light_optics_compatible = sanitize_key( wp_unslash( $_POST['light_optics_compatible'] ?? '' ) );
        $light_ir_compatible = sanitize_key( wp_unslash( $_POST['light_ir_compatible'] ?? '' ) );
        $light_weight_g = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['light_weight_g'] ?? '' ) );
        $light_length_mm = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['light_length_mm'] ?? '' ) );
        $light_size_category = sanitize_key( wp_unslash( $_POST['light_size_category'] ?? '' ) );
        $recoil_resistance = sanitize_key( wp_unslash( $_POST['recoil_resistance'] ?? '' ) );
        $rail_compatibility = sanitize_key( wp_unslash( $_POST['rail_compatibility'] ?? '' ) );
        $ir_wavelength_nm = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['ir_wavelength_nm'] ?? '' ) );
        $head_tiltable = sanitize_key( wp_unslash( $_POST['head_tiltable'] ?? '' ) );
        $headband_system = sanitize_text_field( wp_unslash( $_POST['headband_system'] ?? '' ) );
        $target_identification_index = sanitize_text_field( wp_unslash( $_POST['target_identification_index'] ?? '' ) );
        if ( $equipment_light_lumen === '' && $lumens !== '' ) {
            $equipment_light_lumen = $lumens;
        }
        if ( $equipment_light_range_m === '' && $beam_distance_m !== '' ) {
            $equipment_light_range_m = $beam_distance_m;
        }
        if ( $equipment_light_battery_type === '' && $battery_type !== '' ) {
            $equipment_light_battery_type = $battery_type;
        }
        if ( $equipment_light_usb_charge === '' && $light_usb_rechargeable !== '' ) {
            $equipment_light_usb_charge = $light_usb_rechargeable;
        }
        if ( $equipment_light_color_modes === '' && $light_color !== '' ) {
            $equipment_light_color_modes = $light_color;
        }
        if ( $equipment_runtime_hours === '' && $runtime_hours !== '' ) {
            $equipment_runtime_hours = $runtime_hours;
        }
        $equipment_review_hits = array_values( array_filter( array_map( 'trim', explode( ',', $equipment_moderation_flags ) ) ) );
        $equipment_filter_waterproof = ( strpos( ',' . $equipment_features . ',', ',vizallo,' ) !== false || $light_ip_rating !== '' ) ? '1' : '0';
        $equipment_filter_silent = ( strpos( ',' . $equipment_features . ',', ',hangtalan,' ) !== false || $equipment_silence_level === 'hangtalan' ) ? '1' : '0';
        $equipment_filter_foldable = ( strpos( ',' . $equipment_features . ',', ',osszecsukhato,' ) !== false || $equipment_seat_foldable === 'igen' ) ? '1' : '0';
        $equipment_filter_light_type = $light_type;
        $equipment_filter_light_color = $light_color;
        $equipment_filter_lumens = $lumens;
        $equipment_filter_beam_distance_m = $beam_distance_m;
        $equipment_filter_mount_type = $mount_type;
        $equipment_filter_battery_type = $battery_type;
        $equipment_filter_ip_rating = $light_ip_rating;
        $equipment_filter_recoil_resistance = $recoil_resistance;
        $gear_type = sanitize_key( wp_unslash( $_POST['gear_type'] ?? '' ) );
        $shooting_discipline = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shooting_discipline'] ?? [] ) ) ) );
        $sport_material = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_material'] ?? [] ) ) ) );
        $firearm_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['firearm_compatibility'] ?? [] ) ) ) );
        $sport_size = sanitize_key( wp_unslash( $_POST['sport_size'] ?? '' ) );
        $sport_color = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_color'] ?? [] ) ) ) );
        $sport_ergonomics = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_ergonomics'] ?? [] ) ) ) );
        $modular = sanitize_key( wp_unslash( $_POST['modular'] ?? '' ) );
        $handedness = sanitize_key( wp_unslash( $_POST['handedness'] ?? '' ) );
        $sport_hearing_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_hearing_features'] ?? [] ) ) ) );
        $sport_nrr = sanitize_text_field( wp_unslash( $_POST['sport_nrr'] ?? '' ) );
        $sport_eyewear_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_eyewear_features'] ?? [] ) ) ) );
        $sport_precision_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_precision_features'] ?? [] ) ) ) );
        $sport_clothing_reinforcement = sanitize_text_field( wp_unslash( $_POST['sport_clothing_reinforcement'] ?? '' ) );
        $sport_belt_compatibility = sanitize_text_field( wp_unslash( $_POST['sport_belt_compatibility'] ?? '' ) );
        $sport_speed_setup = sanitize_text_field( wp_unslash( $_POST['sport_speed_setup'] ?? '' ) );
        $sport_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_accessories'] ?? [] ) ) ) );
        $sport_env_resistance = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sport_env_resistance'] ?? [] ) ) ) );
        $sport_condition = sanitize_key( wp_unslash( $_POST['sport_condition'] ?? '' ) );
        $competition_level = sanitize_key( wp_unslash( $_POST['competition_level'] ?? '' ) );
        $sport_filter_discipline = $shooting_discipline;
        $sport_filter_compatibility = $firearm_compatibility;
        $sport_filter_modular = $modular === 'igen' ? '1' : '0';
        $bag_type = sanitize_key( wp_unslash( $_POST['bag_type'] ?? '' ) );
        $volume_l = sanitize_key( wp_unslash( $_POST['volume_l'] ?? '' ) );
        $bag_size = sanitize_key( wp_unslash( $_POST['bag_size'] ?? '' ) );
        $bag_material = sanitize_key( wp_unslash( $_POST['bag_material'] ?? '' ) );
        $bag_weather_resistance = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_weather_resistance'] ?? [] ) ) ) );
        $bag_color_pattern = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_color_pattern'] ?? [] ) ) ) );
        $bag_camo_pattern = sanitize_key( wp_unslash( $_POST['bag_camo_pattern'] ?? '' ) );
        $bag_modular = sanitize_key( wp_unslash( $_POST['bag_modular'] ?? '' ) );
        $bag_design_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_design_features'] ?? [] ) ) ) );
        $bag_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_usage'] ?? [] ) ) ) );
        $bag_firearm_compatible = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_firearm_compatible'] ?? [] ) ) ) );
        $bag_max_firearm_length_cm = sanitize_text_field( wp_unslash( $_POST['bag_max_firearm_length_cm'] ?? '' ) );
        $bag_inner_padding = sanitize_key( wp_unslash( $_POST['bag_inner_padding'] ?? '' ) );
        $bag_lockable = sanitize_key( wp_unslash( $_POST['bag_lockable'] ?? '' ) );
        $bag_hydration_system = sanitize_key( wp_unslash( $_POST['bag_hydration_system'] ?? '' ) );
        $bag_touring_compatible = sanitize_key( wp_unslash( $_POST['bag_touring_compatible'] ?? '' ) );
        $bag_mag_holder_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_mag_holder_count'] ?? '' ) );
        $bag_ammo_compartment_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_ammo_compartment_count'] ?? '' ) );
        $bag_main_compartments = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_main_compartments'] ?? '' ) );
        $bag_outer_pockets = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_outer_pockets'] ?? '' ) );
        $bag_internal_organizer = sanitize_key( wp_unslash( $_POST['bag_internal_organizer'] ?? '' ) );
        $bag_ammo_holder = sanitize_key( wp_unslash( $_POST['bag_ammo_holder'] ?? '' ) );
        $bag_document_compartment = sanitize_key( wp_unslash( $_POST['bag_document_compartment'] ?? '' ) );
        $bag_carrying_system = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_carrying_system'] ?? [] ) ) ) );
        $bag_max_load_kg = sanitize_text_field( wp_unslash( $_POST['bag_max_load_kg'] ?? '' ) );
        $bag_comfort = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_comfort'] ?? [] ) ) ) );
        $bag_closure_type = sanitize_key( wp_unslash( $_POST['bag_closure_type'] ?? '' ) );
        $bag_condition = sanitize_key( wp_unslash( $_POST['bag_condition'] ?? '' ) );
        $bag_packability_index = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_packability_index'] ?? '' ) );
        $bag_filter_waterproof = ( strpos( ',' . $bag_weather_resistance . ',', ',vizallo,' ) !== false || strpos( ',' . $bag_weather_resistance . ',', ',vizlepergeto,' ) !== false ) ? '1' : '0';
        $bag_filter_molle = strpos( ',' . $bag_design_features . ',', ',molle-kompatibilis,' ) !== false ? '1' : '0';
        $bag_filter_modular = ( $bag_modular === 'igen' || strpos( ',' . $bag_design_features . ',', ',modularis,' ) !== false ) ? '1' : '0';
        $bag_filter_firearm_compatible = $bag_firearm_compatible;
        $bag_filter_color = $bag_color_pattern;
        $bag_filter_material = $bag_material;
        $call_fields = self::collect_call_fields_from_request();
        $decor_type = sanitize_key( wp_unslash( $_POST['decor_type'] ?? '' ) );
        $decor_material = sanitize_key( wp_unslash( $_POST['decor_material'] ?? '' ) );
        $decor_wood_type = sanitize_text_field( wp_unslash( $_POST['decor_wood_type'] ?? '' ) );
        $decor_metal_alloy = sanitize_text_field( wp_unslash( $_POST['decor_metal_alloy'] ?? '' ) );
        $decor_style = sanitize_key( wp_unslash( $_POST['decor_style'] ?? '' ) );
        $decor_motif_tags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['decor_motif_tags'] ?? [] ) ) ) );
        $decor_finish = sanitize_key( wp_unslash( $_POST['decor_finish'] ?? '' ) );
        $decor_detailing = sanitize_key( wp_unslash( $_POST['decor_detailing'] ?? '' ) );
        $decor_era = sanitize_key( wp_unslash( $_POST['decor_era'] ?? '' ) );
        $decor_production_type = sanitize_key( wp_unslash( $_POST['decor_production_type'] ?? '' ) );
        $decor_function = sanitize_key( wp_unslash( $_POST['decor_function'] ?? '' ) );
        $decor_thematic_group = sanitize_key( wp_unslash( $_POST['decor_thematic_group'] ?? '' ) );
        $decor_special_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['decor_special_features'] ?? [] ) ) ) );
        $decor_mounting = sanitize_key( wp_unslash( $_POST['decor_mounting'] ?? '' ) );
        $decor_size_class = sanitize_key( wp_unslash( $_POST['decor_size_class'] ?? '' ) );
        $decor_condition = sanitize_key( wp_unslash( $_POST['decor_condition'] ?? '' ) );
        $decor_replica_type = sanitize_text_field( wp_unslash( $_POST['decor_replica_type'] ?? '' ) );
        $decor_authenticity_level = sanitize_key( wp_unslash( $_POST['decor_authenticity_level'] ?? '' ) );
        $decor_collectible_flag = sanitize_key( wp_unslash( $_POST['decor_collectible_flag'] ?? '' ) );
        $decor_filter_collectible = $decor_collectible_flag === 'igen' ? '1' : '0';
        $vadcamera_camera_type = sanitize_key( wp_unslash( $_POST['vadcamera_camera_type'] ?? '' ) );
        $vadcamera_condition = sanitize_key( wp_unslash( $_POST['vadcamera_condition'] ?? '' ) );
        $vadcamera_warranty = sanitize_key( wp_unslash( $_POST['vadcamera_warranty'] ?? '' ) );
        $vadcamera_manufacture_year = sanitize_text_field( wp_unslash( $_POST['vadcamera_manufacture_year'] ?? '' ) );
        $vadcamera_photo_resolution = sanitize_key( wp_unslash( $_POST['vadcamera_photo_resolution'] ?? '' ) );
        $vadcamera_video_resolution = sanitize_key( wp_unslash( $_POST['vadcamera_video_resolution'] ?? '' ) );
        $vadcamera_video_fps = sanitize_key( wp_unslash( $_POST['vadcamera_video_fps'] ?? '' ) );
        $vadcamera_video_audio = sanitize_key( wp_unslash( $_POST['vadcamera_video_audio'] ?? '' ) );
        $vadcamera_ir_type = sanitize_key( wp_unslash( $_POST['vadcamera_ir_type'] ?? '' ) );
        $vadcamera_night_range_m = sanitize_text_field( wp_unslash( $_POST['vadcamera_night_range_m'] ?? '' ) );
        $vadcamera_ir_led_count = sanitize_text_field( wp_unslash( $_POST['vadcamera_ir_led_count'] ?? '' ) );
        $vadcamera_trigger_speed = sanitize_key( wp_unslash( $_POST['vadcamera_trigger_speed'] ?? '' ) );
        $vadcamera_pir_distance_m = sanitize_text_field( wp_unslash( $_POST['vadcamera_pir_distance_m'] ?? '' ) );
        $vadcamera_view_angle_deg = sanitize_text_field( wp_unslash( $_POST['vadcamera_view_angle_deg'] ?? '' ) );
        $vadcamera_connectivity = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_connectivity'] ?? [] ) ) ) );
        $vadcamera_sim_slot = sanitize_key( wp_unslash( $_POST['vadcamera_sim_slot'] ?? '' ) );
        $vadcamera_mobile_app = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_mobile_app'] ?? [] ) ) ) );
        $vadcamera_sd_max_gb = sanitize_text_field( wp_unslash( $_POST['vadcamera_sd_max_gb'] ?? '' ) );
        $vadcamera_internal_memory = sanitize_key( wp_unslash( $_POST['vadcamera_internal_memory'] ?? '' ) );
        $vadcamera_power_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_power_types'] ?? [] ) ) ) );
        $vadcamera_runtime = sanitize_text_field( wp_unslash( $_POST['vadcamera_runtime'] ?? '' ) );
        $vadcamera_ip_rating = sanitize_key( wp_unslash( $_POST['vadcamera_ip_rating'] ?? '' ) );
        $vadcamera_temp_range = sanitize_text_field( wp_unslash( $_POST['vadcamera_temp_range'] ?? '' ) );
        $vadcamera_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_accessories'] ?? [] ) ) ) );
        $vadcamera_use_cases = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_use_cases'] ?? [] ) ) ) );
        $vadcamera_audio_record = sanitize_key( wp_unslash( $_POST['vadcamera_audio_record'] ?? '' ) );
        $vadcamera_public_area_use = sanitize_key( wp_unslash( $_POST['vadcamera_public_area_use'] ?? '' ) );
        $vadcamera_mounting = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_mounting'] ?? [] ) ) ) );
        $vadcamera_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_shipping_methods'] ?? [] ) ) ) );
        $vadcamera_media_required = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_media_required'] ?? [] ) ) ) );
        $vadcamera_sample_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_sample_media'] ?? [] ) ) ) );
        $vadcamera_4g_carrier = sanitize_text_field( wp_unslash( $_POST['vadcamera_4g_carrier'] ?? '' ) );
        $vadcamera_4g_app_support = sanitize_text_field( wp_unslash( $_POST['vadcamera_4g_app_support'] ?? '' ) );
        $vadcamera_solar_panel_power = sanitize_text_field( wp_unslash( $_POST['vadcamera_solar_panel_power'] ?? '' ) );
        $vadcamera_notes = sanitize_textarea_field( wp_unslash( $_POST['vadcamera_notes'] ?? '' ) );
        $vadcamera_filter_connectivity_4g = ( strpos( ',' . $vadcamera_connectivity . ',', ',4g,' ) !== false || strpos( ',' . $vadcamera_connectivity . ',', ',lte,' ) !== false ) ? '1' : '0';
        $vadcamera_filter_connectivity_wifi = ( strpos( ',' . $vadcamera_connectivity . ',', ',wifi,' ) !== false ) ? '1' : '0';
        $vadcamera_filter_is_solar = ( strpos( ',' . $vadcamera_power_types . ',', ',napelem,' ) !== false || $vadcamera_camera_type === 'napelemes' ) ? '1' : '0';
        $vadcamera_filter_is_sim = ( $vadcamera_sim_slot === 'igen' ) ? '1' : '0';
        $estate_main_type = sanitize_key( wp_unslash( $_POST['estate_main_type'] ?? '' ) );
        $estate_sale_mode = sanitize_key( wp_unslash( $_POST['estate_sale_mode'] ?? '' ) );
        $estate_price_total = sanitize_text_field( wp_unslash( $_POST['estate_price_total'] ?? '' ) );
        $estate_price_guide = sanitize_text_field( wp_unslash( $_POST['estate_price_guide'] ?? '' ) );
        $estate_price_per_item = sanitize_text_field( wp_unslash( $_POST['estate_price_per_item'] ?? '' ) );
        $estate_estimated_value = sanitize_text_field( wp_unslash( $_POST['estate_estimated_value'] ?? '' ) );
        $estate_negotiable = sanitize_key( wp_unslash( $_POST['estate_negotiable'] ?? '' ) );
        $estate_firearms_count = sanitize_text_field( wp_unslash( $_POST['estate_firearms_count'] ?? '' ) );
        $estate_firearms_type = sanitize_key( wp_unslash( $_POST['estate_firearms_type'] ?? '' ) );
        $estate_firearms_deactivated = sanitize_key( wp_unslash( $_POST['estate_firearms_deactivated'] ?? '' ) );
        $estate_firearms_licensed = sanitize_key( wp_unslash( $_POST['estate_firearms_licensed'] ?? '' ) );
        $estate_optics_count = sanitize_text_field( wp_unslash( $_POST['estate_optics_count'] ?? '' ) );
        $estate_clothing_count = sanitize_text_field( wp_unslash( $_POST['estate_clothing_count'] ?? '' ) );
        $estate_trophy_count = sanitize_text_field( wp_unslash( $_POST['estate_trophy_count'] ?? '' ) );
        $estate_docs_count = sanitize_text_field( wp_unslash( $_POST['estate_docs_count'] ?? '' ) );
        $estate_equipment_count = sanitize_text_field( wp_unslash( $_POST['estate_equipment_count'] ?? '' ) );
        $estate_condition = sanitize_key( wp_unslash( $_POST['estate_condition'] ?? '' ) );
        $estate_origin = sanitize_key( wp_unslash( $_POST['estate_origin'] ?? '' ) );
        $estate_legal_has_licensed_items = sanitize_key( wp_unslash( $_POST['estate_legal_has_licensed_items'] ?? '' ) );
        $estate_legal_deactivated = sanitize_key( wp_unslash( $_POST['estate_legal_deactivated'] ?? '' ) );
        $estate_legal_has_papers = sanitize_key( wp_unslash( $_POST['estate_legal_has_papers'] ?? '' ) );
        $estate_legal_bundle_only = sanitize_key( wp_unslash( $_POST['estate_legal_bundle_only'] ?? '' ) );
        $estate_transfer_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_transfer_methods'] ?? [] ) ) ) );

        $estate_optic_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_optic_items'] ?? [] ) ),
            array( 'celtavcso', 'binokular', 'spektiv', 'reddot' )
        ) );
        if ( empty( $estate_optic_items ) ) {
            if ( ! empty( $_POST['estate_optic_celtavcso'] ) ) { $estate_optic_items[] = 'celtavcso'; }
            if ( ! empty( $_POST['estate_optic_binokular'] ) ) { $estate_optic_items[] = 'binokular'; }
            if ( ! empty( $_POST['estate_optic_spektiv'] ) ) { $estate_optic_items[] = 'spektiv'; }
            if ( ! empty( $_POST['estate_optic_reddot'] ) ) { $estate_optic_items[] = 'reddot'; }
        }

        $estate_clothing_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_clothing_items'] ?? [] ) ),
            array( 'kabat', 'nadrag', 'csizma', 'esoruha' )
        ) );
        if ( empty( $estate_clothing_items ) ) {
            if ( ! empty( $_POST['estate_clothing_kabat'] ) ) { $estate_clothing_items[] = 'kabat'; }
            if ( ! empty( $_POST['estate_clothing_nadrag'] ) ) { $estate_clothing_items[] = 'nadrag'; }
            if ( ! empty( $_POST['estate_clothing_csizma'] ) ) { $estate_clothing_items[] = 'csizma'; }
            if ( ! empty( $_POST['estate_clothing_esoruha'] ) ) { $estate_clothing_items[] = 'esoruha'; }
        }

        $estate_trophy_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_trophy_items'] ?? [] ) ),
            array( 'agancs', 'koponya', 'preparatum' )
        ) );
        if ( empty( $estate_trophy_items ) ) {
            if ( ! empty( $_POST['estate_trophy_agancs'] ) ) { $estate_trophy_items[] = 'agancs'; }
            if ( ! empty( $_POST['estate_trophy_koponya'] ) ) { $estate_trophy_items[] = 'koponya'; }
            if ( ! empty( $_POST['estate_trophy_preparatum'] ) ) { $estate_trophy_items[] = 'preparatum'; }
        }

        $estate_docs_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_docs_items'] ?? [] ) ),
            array( 'engedelyek', 'vadasznaplo', 'konyvek', 'tanusitvanyok' )
        ) );
        if ( empty( $estate_docs_items ) ) {
            if ( ! empty( $_POST['estate_docs_engedelyek'] ) ) { $estate_docs_items[] = 'engedelyek'; }
            if ( ! empty( $_POST['estate_docs_vadasznaplo'] ) ) { $estate_docs_items[] = 'vadasznaplo'; }
            if ( ! empty( $_POST['estate_docs_konyvek'] ) ) { $estate_docs_items[] = 'konyvek'; }
            if ( ! empty( $_POST['estate_docs_tanusitvanyok'] ) ) { $estate_docs_items[] = 'tanusitvanyok'; }
        }

        $estate_equipment_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_equipment_items'] ?? [] ) ),
            array( 'kes', 'hatizsak', 'lampa', 'tavcso_tartozek', 'fegyverszef' )
        ) );
        if ( empty( $estate_equipment_items ) ) {
            if ( ! empty( $_POST['estate_equipment_kes'] ) ) { $estate_equipment_items[] = 'kes'; }
            if ( ! empty( $_POST['estate_equipment_hatizsak'] ) ) { $estate_equipment_items[] = 'hatizsak'; }
            if ( ! empty( $_POST['estate_equipment_lampa'] ) ) { $estate_equipment_items[] = 'lampa'; }
            if ( ! empty( $_POST['estate_equipment_tavcso_tartozek'] ) ) { $estate_equipment_items[] = 'tavcso_tartozek'; }
            if ( ! empty( $_POST['estate_equipment_fegyverszef'] ) ) { $estate_equipment_items[] = 'fegyverszef'; }
        }

        $estate_media_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_media_items'] ?? [] ) ),
            array( 'full', 'details', 'documents', 'firearms' )
        ) );
        if ( empty( $estate_media_items ) ) {
            if ( ! empty( $_POST['estate_media_full'] ) ) { $estate_media_items[] = 'full'; }
            if ( ! empty( $_POST['estate_media_details'] ) ) { $estate_media_items[] = 'details'; }
            if ( ! empty( $_POST['estate_media_documents'] ) ) { $estate_media_items[] = 'documents'; }
            if ( ! empty( $_POST['estate_media_firearms'] ) ) { $estate_media_items[] = 'firearms'; }
        }

        $estate_media_full = in_array( 'full', $estate_media_items, true ) ? '1' : '0';
        $estate_media_details = in_array( 'details', $estate_media_items, true ) ? '1' : '0';
        $estate_media_documents = in_array( 'documents', $estate_media_items, true ) ? '1' : '0';
        $estate_media_firearms = in_array( 'firearms', $estate_media_items, true ) ? '1' : '0';
        $estate_optic_celtavcso = in_array( 'celtavcso', $estate_optic_items, true ) ? '1' : '0';
        $estate_optic_binokular = in_array( 'binokular', $estate_optic_items, true ) ? '1' : '0';
        $estate_optic_spektiv = in_array( 'spektiv', $estate_optic_items, true ) ? '1' : '0';
        $estate_optic_reddot = in_array( 'reddot', $estate_optic_items, true ) ? '1' : '0';
        $estate_clothing_kabat = in_array( 'kabat', $estate_clothing_items, true ) ? '1' : '0';
        $estate_clothing_nadrag = in_array( 'nadrag', $estate_clothing_items, true ) ? '1' : '0';
        $estate_clothing_csizma = in_array( 'csizma', $estate_clothing_items, true ) ? '1' : '0';
        $estate_clothing_esoruha = in_array( 'esoruha', $estate_clothing_items, true ) ? '1' : '0';
        $estate_trophy_agancs = in_array( 'agancs', $estate_trophy_items, true ) ? '1' : '0';
        $estate_trophy_koponya = in_array( 'koponya', $estate_trophy_items, true ) ? '1' : '0';
        $estate_trophy_preparatum = in_array( 'preparatum', $estate_trophy_items, true ) ? '1' : '0';
        $estate_docs_engedelyek = in_array( 'engedelyek', $estate_docs_items, true ) ? '1' : '0';
        $estate_docs_vadasznaplo = in_array( 'vadasznaplo', $estate_docs_items, true ) ? '1' : '0';
        $estate_docs_konyvek = in_array( 'konyvek', $estate_docs_items, true ) ? '1' : '0';
        $estate_docs_tanusitvanyok = in_array( 'tanusitvanyok', $estate_docs_items, true ) ? '1' : '0';
        $estate_equipment_kes = in_array( 'kes', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_hatizsak = in_array( 'hatizsak', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_lampa = in_array( 'lampa', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_tavcso_tartozek = in_array( 'tavcso_tartozek', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_fegyverszef = in_array( 'fegyverszef', $estate_equipment_items, true ) ? '1' : '0';
        $estate_notes = sanitize_textarea_field( wp_unslash( $_POST['estate_notes'] ?? '' ) );
        $estate_filter_has_firearms = ( $estate_firearms_count !== '' || $estate_firearms_type !== '' || $estate_firearms_licensed === 'igen' ) ? '1' : '0';
        $estate_filter_has_optics = ( $estate_optics_count !== '' || $estate_optic_celtavcso === '1' || $estate_optic_binokular === '1' || $estate_optic_spektiv === '1' || $estate_optic_reddot === '1' ) ? '1' : '0';
        $estate_filter_scope = in_array( $estate_sale_mode, [ 'egyben', 'darabonkent', 'reszletekben' ], true ) ? $estate_sale_mode : '';
        $bow_type = sanitize_key( wp_unslash( $_POST['bow_type'] ?? '' ) );
        $bow_handedness = sanitize_key( wp_unslash( $_POST['bow_handedness'] ?? '' ) );
        $bow_draw_weight = sanitize_text_field( wp_unslash( $_POST['bow_draw_weight'] ?? '' ) );
        $bow_draw_length = sanitize_text_field( wp_unslash( $_POST['bow_draw_length'] ?? '' ) );
        $bow_axle_length = sanitize_text_field( wp_unslash( $_POST['bow_axle_length'] ?? '' ) );
        $bow_item_condition = sanitize_key( wp_unslash( $_POST['bow_item_condition'] ?? '' ) );
        $bow_condition_notes = sanitize_textarea_field( wp_unslash( $_POST['bow_condition_notes'] ?? '' ) );
        $bow_string_replaced = sanitize_key( wp_unslash( $_POST['bow_string_replaced'] ?? '' ) );
        $bow_owned_since = sanitize_text_field( wp_unslash( $_POST['bow_owned_since'] ?? '' ) );
        $bow_usage = sanitize_textarea_field( wp_unslash( $_POST['bow_usage'] ?? '' ) );
        $bow_accessories = sanitize_textarea_field( wp_unslash( $_POST['bow_accessories'] ?? '' ) );
        $bow_cams_cables = sanitize_textarea_field( wp_unslash( $_POST['bow_cams_cables'] ?? '' ) );
        $bow_speed_fps = sanitize_text_field( wp_unslash( $_POST['bow_speed_fps'] ?? '' ) );
        $bow_serial = sanitize_text_field( wp_unslash( $_POST['bow_serial'] ?? '' ) );
        $bow_material = sanitize_text_field( wp_unslash( $_POST['bow_material'] ?? '' ) );
        $other_weapon_kind = sanitize_key( wp_unslash( $_POST['other_weapon_kind'] ?? '' ) );
        $other_weapon_general_notes = sanitize_textarea_field( wp_unslash( $_POST['other_weapon_general_notes'] ?? '' ) );
        $other_weapon_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['other_weapon_accessories'] ?? [] ) ) ) );
        $other_weapon_accessories_other = sanitize_text_field( wp_unslash( $_POST['other_weapon_accessories_other'] ?? '' ) );
        $other_weapon_18plus = sanitize_key( wp_unslash( $_POST['other_weapon_18plus'] ?? '' ) );
        $other_weapon_permit_required = sanitize_key( wp_unslash( $_POST['other_weapon_permit_required'] ?? '' ) );
        $other_weapon_personal_pickup = sanitize_key( wp_unslash( $_POST['other_weapon_personal_pickup'] ?? '' ) );
        $other_weapon_origin_papers = sanitize_key( wp_unslash( $_POST['other_weapon_origin_papers'] ?? '' ) );
        $other_weapon_serial_number = sanitize_text_field( wp_unslash( $_POST['other_weapon_serial_number'] ?? '' ) );
        $other_weapon_bow_draw_weight_lbs = sanitize_text_field( wp_unslash( $_POST['other_weapon_bow_draw_weight_lbs'] ?? '' ) );
        $other_weapon_bow_axle_to_axle = sanitize_text_field( wp_unslash( $_POST['other_weapon_bow_axle_to_axle'] ?? '' ) );
        $other_weapon_bow_handness = sanitize_key( wp_unslash( $_POST['other_weapon_bow_handness'] ?? '' ) );
        $other_weapon_bow_style = sanitize_key( wp_unslash( $_POST['other_weapon_bow_style'] ?? '' ) );
        $other_weapon_bow_arrow_rest = sanitize_key( wp_unslash( $_POST['other_weapon_bow_arrow_rest'] ?? '' ) );
        $other_weapon_bow_sight = sanitize_key( wp_unslash( $_POST['other_weapon_bow_sight'] ?? '' ) );
        $other_weapon_crossbow_draw_weight_lbs = sanitize_text_field( wp_unslash( $_POST['other_weapon_crossbow_draw_weight_lbs'] ?? '' ) );
        $other_weapon_crossbow_fps = sanitize_text_field( wp_unslash( $_POST['other_weapon_crossbow_fps'] ?? '' ) );
        $other_weapon_crossbow_accessories = sanitize_textarea_field( wp_unslash( $_POST['other_weapon_crossbow_accessories'] ?? '' ) );
        $other_weapon_airsoft_operation = sanitize_key( wp_unslash( $_POST['other_weapon_airsoft_operation'] ?? '' ) );
        $other_weapon_airsoft_energy_joule = sanitize_text_field( wp_unslash( $_POST['other_weapon_airsoft_energy_joule'] ?? '' ) );
        $other_weapon_airsoft_fps = sanitize_text_field( wp_unslash( $_POST['other_weapon_airsoft_fps'] ?? '' ) );
        $other_weapon_airsoft_magazine_type = sanitize_key( wp_unslash( $_POST['other_weapon_airsoft_magazine_type'] ?? '' ) );
        $other_weapon_airgun_caliber = sanitize_text_field( wp_unslash( $_POST['other_weapon_airgun_caliber'] ?? '' ) );
        $other_weapon_airgun_muzzle_energy = sanitize_text_field( wp_unslash( $_POST['other_weapon_airgun_muzzle_energy'] ?? '' ) );
        $other_weapon_airgun_power_source = sanitize_key( wp_unslash( $_POST['other_weapon_airgun_power_source'] ?? '' ) );
        $other_weapon_gas_alarm_caliber = sanitize_text_field( wp_unslash( $_POST['other_weapon_gas_alarm_caliber'] ?? '' ) );
        $other_weapon_gas_alarm_self_defense_license = sanitize_key( wp_unslash( $_POST['other_weapon_gas_alarm_self_defense_license'] ?? '' ) );
        $other_weapon_gas_alarm_carry_license = sanitize_key( wp_unslash( $_POST['other_weapon_gas_alarm_carry_license'] ?? '' ) );
        $other_weapon_knife_blade_length = sanitize_text_field( wp_unslash( $_POST['other_weapon_knife_blade_length'] ?? '' ) );
        $other_weapon_knife_steel_type = sanitize_text_field( wp_unslash( $_POST['other_weapon_knife_steel_type'] ?? '' ) );
        $other_weapon_knife_handle_material = sanitize_text_field( wp_unslash( $_POST['other_weapon_knife_handle_material'] ?? '' ) );
        $other_weapon_knife_sheath = sanitize_key( wp_unslash( $_POST['other_weapon_knife_sheath'] ?? '' ) );
        $other_weapon_knife_accessories = sanitize_textarea_field( wp_unslash( $_POST['other_weapon_knife_accessories'] ?? '' ) );
        $bow_accessories_flags_raw = isset( $_POST['bow_accessories_flags'] ) ? (array) wp_unslash( $_POST['bow_accessories_flags'] ) : array();
        $bow_accessories_flags = array_map( 'sanitize_key', $bow_accessories_flags_raw );
        $bow_accessories_allowed = array( 'bow_acc_sight', 'bow_acc_release', 'bow_acc_stabilizer', 'bow_acc_arrows', 'bow_acc_case', 'bow_acc_quiver', 'bow_acc_target' );
        $bow_accessories_flags = array_values( array_intersect( $bow_accessories_flags, $bow_accessories_allowed ) );
        $bow_acc_sight = ( in_array( 'bow_acc_sight', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_sight'] ) ) ? '1' : '0';
        $bow_acc_release = ( in_array( 'bow_acc_release', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_release'] ) ) ? '1' : '0';
        $bow_acc_stabilizer = ( in_array( 'bow_acc_stabilizer', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_stabilizer'] ) ) ? '1' : '0';
        $bow_acc_arrows = ( in_array( 'bow_acc_arrows', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_arrows'] ) ) ? '1' : '0';
        $bow_acc_case = ( in_array( 'bow_acc_case', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_case'] ) ) ? '1' : '0';
        $bow_acc_quiver = ( in_array( 'bow_acc_quiver', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_quiver'] ) ) ? '1' : '0';
        $bow_acc_target = ( in_array( 'bow_acc_target', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_target'] ) ) ? '1' : '0';
        $feed_type = sanitize_key( wp_unslash( $_POST['feed_type'] ?? '' ) );
        $feed_recommended_game = sanitize_textarea_field( wp_unslash( $_POST['feed_recommended_game'] ?? '' ) );
        $feed_recommended_game_flags_raw = isset( $_POST['feed_recommended_game_flags'] ) ? (array) wp_unslash( $_POST['feed_recommended_game_flags'] ) : array();
        $feed_recommended_game_flags = array_map( 'sanitize_key', $feed_recommended_game_flags_raw );
        $feed_recommended_game_allowed = array( 'feed_game_boar', 'feed_game_roe', 'feed_game_red_deer', 'feed_game_pheasant', 'feed_game_other' );
        $feed_recommended_game_flags = array_values( array_intersect( $feed_recommended_game_flags, $feed_recommended_game_allowed ) );
        $feed_game_boar = ( in_array( 'feed_game_boar', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_boar'] ) ) ? '1' : '0';
        $feed_game_roe = ( in_array( 'feed_game_roe', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_roe'] ) ) ? '1' : '0';
        $feed_game_red_deer = ( in_array( 'feed_game_red_deer', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_red_deer'] ) ) ? '1' : '0';
        $feed_game_pheasant = ( in_array( 'feed_game_pheasant', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_pheasant'] ) ) ? '1' : '0';
        $feed_game_other = ( in_array( 'feed_game_other', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_other'] ) ) ? '1' : '0';
        $feed_game_other_text = sanitize_text_field( wp_unslash( $_POST['feed_game_other_text'] ?? '' ) );
        $feed_ingredients = sanitize_textarea_field( wp_unslash( $_POST['feed_ingredients'] ?? '' ) );
        $feed_moisture = sanitize_text_field( wp_unslash( $_POST['feed_moisture'] ?? '' ) );
        $feed_has_additives = sanitize_key( wp_unslash( $_POST['feed_has_additives'] ?? '' ) );
        $feed_packaging_type = sanitize_key( wp_unslash( $_POST['feed_packaging_type'] ?? '' ) );
        $feed_bag_weight = sanitize_text_field( wp_unslash( $_POST['feed_bag_weight'] ?? '' ) );
        $feed_total_quantity = sanitize_text_field( wp_unslash( $_POST['feed_total_quantity'] ?? '' ) );
        $feed_pallet_quantity = sanitize_text_field( wp_unslash( $_POST['feed_pallet_quantity'] ?? '' ) );
        $feed_harvest_freshness = sanitize_key( wp_unslash( $_POST['feed_harvest_freshness'] ?? '' ) );
        $feed_stored_dry = sanitize_key( wp_unslash( $_POST['feed_stored_dry'] ?? '' ) );
        $feed_mold_free = sanitize_key( wp_unslash( $_POST['feed_mold_free'] ?? '' ) );
        $feed_bags_undamaged = sanitize_key( wp_unslash( $_POST['feed_bags_undamaged'] ?? '' ) );
        $feed_price_per_unit = sanitize_text_field( wp_unslash( $_POST['feed_price_per_unit'] ?? '' ) );
        $feed_min_order = sanitize_text_field( wp_unslash( $_POST['feed_min_order'] ?? '' ) );
        $feed_delivery_available = sanitize_key( wp_unslash( $_POST['feed_delivery_available'] ?? '' ) );
        $feed_loading_available = sanitize_key( wp_unslash( $_POST['feed_loading_available'] ?? '' ) );
        $hatastalanitott_weapon_type = sanitize_key( wp_unslash( $_POST['hatastalanitott_weapon_type'] ?? '' ) );
        $hatastalanitott_origin_country = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_origin_country'] ?? '' ) );
        $hatastalanitott_is_deactivated = sanitize_key( wp_unslash( $_POST['hatastalanitott_is_deactivated'] ?? '' ) );
        $hatastalanitott_deactivation_type = sanitize_key( wp_unslash( $_POST['hatastalanitott_deactivation_type'] ?? '' ) );
        $hatastalanitott_certificate = sanitize_key( wp_unslash( $_POST['hatastalanitott_certificate'] ?? '' ) );
        $hatastalanitott_mkh_cip = sanitize_key( wp_unslash( $_POST['hatastalanitott_mkh_cip'] ?? '' ) );
        $hatastalanitott_deactivation_year = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_deactivation_year'] ?? '' ) );
        $hatastalanitott_deactivation_org = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_deactivation_org'] ?? '' ) );
        $hatastalanitott_condition = sanitize_key( wp_unslash( $_POST['hatastalanitott_condition'] ?? '' ) );
        $hatastalanitott_serial_admin = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_serial_admin'] ?? '' ) );
        $hatastalanitott_working_parts = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hatastalanitott_working_parts'] ?? [] ) ) ) );
        $hatastalanitott_original_finish = sanitize_key( wp_unslash( $_POST['hatastalanitott_original_finish'] ?? '' ) );
        $hatastalanitott_original_parts_ratio = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_original_parts_ratio'] ?? '' ) );
        $hatastalanitott_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hatastalanitott_accessories'] ?? [] ) ) ) );
        $loszer_category = sanitize_key( wp_unslash( $_POST['loszer_category'] ?? '' ) );
        $loszer_caliber_preset = sanitize_key( wp_unslash( $_POST['loszer_caliber_preset'] ?? '' ) );
        $loszer_caliber_custom = sanitize_text_field( wp_unslash( $_POST['loszer_caliber_custom'] ?? '' ) );
        $loszer_manufacturer_preset = sanitize_key( wp_unslash( $_POST['loszer_manufacturer_preset'] ?? '' ) );
        $loszer_manufacturer_custom = sanitize_text_field( wp_unslash( $_POST['loszer_manufacturer_custom'] ?? '' ) );
        $loszer_projectile_type = sanitize_key( wp_unslash( $_POST['loszer_projectile_type'] ?? '' ) );
        $loszer_projectile_weight = sanitize_text_field( wp_unslash( $_POST['loszer_projectile_weight'] ?? '' ) );
        $loszer_projectile_weight_unit = sanitize_key( wp_unslash( $_POST['loszer_projectile_weight_unit'] ?? '' ) );
        $loszer_piece_count = sanitize_text_field( wp_unslash( $_POST['loszer_piece_count'] ?? '' ) );
        $loszer_piece_unit = sanitize_key( wp_unslash( $_POST['loszer_piece_unit'] ?? '' ) );
        $loszer_lot_year = sanitize_text_field( wp_unslash( $_POST['loszer_lot_year'] ?? '' ) );
        $loszer_condition = sanitize_key( wp_unslash( $_POST['loszer_condition'] ?? '' ) );
        $loszer_component_case_condition = sanitize_key( wp_unslash( $_POST['loszer_component_case_condition'] ?? '' ) );
        $loszer_powder_type = sanitize_text_field( wp_unslash( $_POST['loszer_powder_type'] ?? '' ) );
        $loszer_powder_sealed = sanitize_key( wp_unslash( $_POST['loszer_powder_sealed'] ?? '' ) );
        $loszer_powder_weight = sanitize_text_field( wp_unslash( $_POST['loszer_powder_weight'] ?? '' ) );
        $loszer_primer_type = sanitize_key( wp_unslash( $_POST['loszer_primer_type'] ?? '' ) );
        $category    = intval( $_POST['category'] ?? 0 );
        $county      = intval( $_POST['county']   ?? 0 );
        $condition   = intval( $_POST['condition'] ?? 0 );

        $category_term = get_term( $category, 'va_category' );
        $category_slug = ( $category_term instanceof \WP_Term && ! is_wp_error( $category_term ) ) ? sanitize_title( (string) $category_term->slug ) : '';
        if ( $category_slug === 'vadasz-felszereles' && $equipment_type === '' ) {
            $equipment_type = 'egyeb';
        }

        if ( $equipment_type === 'egyeb' ) {
            $suggestion_source = strtolower( trim( implode( ' ', [
                (string) $title,
                wp_strip_all_tags( (string) $description ),
                (string) $equipment_notes,
                (string) $other_equipment_function,
                (string) $other_equipment_material_note,
                (string) $other_equipment_compatibility,
                (string) $equipment_weapon_compatibility,
                (string) $light_type,
            ] ) ) );

            if ( $other_uncertain_classification === 'hangtompito-gyanus' || preg_match( '/hangtompito|suppressor|silencer/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'hangtompito';
                $other_auto_suggested_reason = 'Mintafelismeres: hangtompito kulcsszavak';
            } elseif ( preg_match( '/lampa|feny|ir\s|infra|lumen|beam/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'lampa';
                $other_auto_suggested_reason = 'Mintafelismeres: lampa/feny kulcsszavak';
            } elseif ( preg_match( '/taska|tok|hatizsak|molle/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'taska';
                $other_auto_suggested_reason = 'Mintafelismeres: taska/tok kulcsszavak';
            } elseif ( preg_match( '/ruhazat|kabat|nadrag|melleny|bakancs|cipo/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'ruhazat';
                $other_auto_suggested_reason = 'Mintafelismeres: ruhazat kulcsszavak';
            } elseif ( preg_match( '/fegyver|puska|soretes|golyos|marok/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'fegyver';
                $other_auto_suggested_reason = 'Mintafelismeres: fegyver kulcsszavak';
            }
        }

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'A cím kötelező.' ] );
        }

        if ( $location === '' && $postal_code === '' ) {
            wp_send_json_error( [ 'message' => 'Adja meg a várost vagy az irányítószámot.' ] );
        }

        $rule_error = self::validate_category_required_fields( $category, [
            'brand' => $brand,
            'model' => $model,
            'caliber' => $caliber,
            'mixed_type' => $mixed_type,
            'mixed_barrel_count' => $mixed_barrel_count,
            'mixed_rifle_caliber' => $mixed_rifle_caliber,
            'mixed_shotgun_caliber' => $mixed_shotgun_caliber,
            'mixed_condition' => $mixed_condition,
            'rifle_type' => $rifle_type,
            'rifle_caliber' => $rifle_caliber,
            'rifle_barrel_length_cm' => $rifle_barrel_length_cm,
            'rifle_condition' => $rifle_condition,
            'rifle_optic_compatibility' => $rifle_optic_compatibility,
            'other_weapon_kind' => $other_weapon_kind,
            'hatastalanitott_weapon_type' => $hatastalanitott_weapon_type,
            'hatastalanitott_is_deactivated' => $hatastalanitott_is_deactivated,
            'hatastalanitott_deactivation_type' => $hatastalanitott_deactivation_type,
            'hatastalanitott_certificate' => $hatastalanitott_certificate,
            'hatastalanitott_mkh_cip' => $hatastalanitott_mkh_cip,
            'hatastalanitott_condition' => $hatastalanitott_condition,
            'loszer_category' => $loszer_category,
            'loszer_condition' => $loszer_condition,
            'optic_type' => $optic_type,
            'optic_zoom' => $optic_zoom,
            'optic_objective' => $optic_objective,
            'thermal_device_type' => $thermal_device_type,
            'thermal_sensor_resolution' => $thermal_sensor_resolution,
            'thermal_netd' => $thermal_netd,
            'thermal_refresh_hz' => $thermal_refresh_hz,
            'dog_age_months' => $dog_age_months,
            'dog_working_type' => $dog_working_type,
            'dog_breed' => $dog_breed,
            'dog_gender' => $dog_gender,
            'dog_birth_date' => $dog_birth_date,
            'dog_hunting_specialization' => $dog_hunting_specialization,
            'dog_training_level' => $dog_training_level,
            'job_location' => $job_location !== '' ? $job_location : $location,
            'job_type' => $job_type,
            'service_type' => $service_type,
            'service_provider_type' => $service_provider_type,
            'service_country' => $service_country,
            'service_county' => $service_county,
            'service_town' => $service_town,
            'service_area' => $service_area,
            'service_pricing_type' => $service_pricing_type,
            'service_available_weekend' => $service_available_weekend,
            'service_available_24_7' => $service_available_24_7,
            'service_seasonal' => $service_seasonal,
            'service_verified_provider' => $service_verified_provider,
            'service_document_verified' => $service_document_verified,
            'service_phone_verified' => $service_phone_verified,
            'service_business_verified' => $service_business_verified,
            'service_sos_service' => $service_sos_service,
            'service_urgent_repair' => $service_urgent_repair,
            'service_full_prep' => $service_full_prep,
            'service_night_work' => $service_night_work,
            'clothing_type' => $clothing_type,
            'clothing_condition' => $clothing_condition,
            'clothing_gender' => $clothing_gender,
            'clothing_size' => $clothing_size,
            'season' => $season,
            'hunting_usage' => $hunting_usage,
            'waterproof_level' => $waterproof_level,
            'noise_level' => $noise_level,
            'material_tech' => $material_tech,
            'camo_pattern' => $camo_pattern,
            'knife_type' => $knife_type,
            'knife_condition' => $knife_condition,
            'knife_blade_length_mm' => $knife_blade_length_mm,
            'knife_steel_type' => $knife_steel_type,
            'knife_blade_length' => $knife_blade_length,
            'marok_type' => sanitize_key( wp_unslash( $_POST['marok_type'] ?? '' ) ),
            'marok_condition' => sanitize_key( wp_unslash( $_POST['marok_condition'] ?? '' ) ),
            'marok_action_type' => sanitize_key( wp_unslash( $_POST['marok_action_type'] ?? '' ) ),
            'marok_magazine_capacity' => sanitize_text_field( wp_unslash( $_POST['marok_magazine_capacity'] ?? '' ) ),
            'marok_license_required' => sanitize_key( wp_unslash( $_POST['marok_license_required'] ?? '' ) ),
            'marok_legal_category' => sanitize_key( wp_unslash( $_POST['marok_legal_category'] ?? '' ) ),
            'marok_cip_marking' => sanitize_key( wp_unslash( $_POST['marok_cip_marking'] ?? '' ) ),
            'marok_transfer_license_only' => sanitize_key( wp_unslash( $_POST['marok_transfer_license_only'] ?? '' ) ),
            'trophy_species' => $trophy_species,
            'trophy_type' => $trophy_type,
            'trophy_maker' => $trophy_maker,
            'trophy_custom_made' => $trophy_custom_made,
            'trophy_condition' => $trophy_condition,
            'trophy_manufacture_year' => $trophy_manufacture_year,
            'trophy_height_cm' => $trophy_height_cm,
            'trophy_width_cm' => $trophy_width_cm,
            'trophy_thickness_cm' => $trophy_thickness_cm,
            'trophy_compatible_species' => $trophy_compatible_species,
            'trophy_mount_type' => $trophy_mount_type,
            'trophy_mounting_type' => $trophy_mounting_type,
            'trophy_attachment_type' => $trophy_attachment_type,
            'trophy_style' => $trophy_style,
            'trophy_fang_mount' => $trophy_fang_mount,
            'trophy_material_type' => $trophy_material_type,
            'trophy_material' => $trophy_material,
            'trophy_surface_finish' => $trophy_surface_finish,
            'trophy_finish' => $trophy_finish,
            'trophy_weight_kg' => $trophy_weight_kg,
            'trophy_price_negotiable' => $trophy_price_negotiable,
            'trophy_custom_order' => $trophy_custom_order,
            'trophy_fragile_handling' => $trophy_fragile_handling,
            'trophy_house_style' => $trophy_house_style,
            'accommodation_type' => $accommodation_type,
            'accommodation_capacity' => $accommodation_capacity,
            'accommodation_hunting_nearby' => $accommodation_hunting_nearby,
            'accommodation_hunt_available' => $accommodation_hunt_available,
            'accommodation_county_text' => $accommodation_county_text,
            'accommodation_land_size_sqm' => $accommodation_land_size_sqm,
            'accommodation_building_size_sqm' => $accommodation_building_size_sqm,
            'accommodation_utilities' => $accommodation_utilities,
            'accommodation_accessibility' => $accommodation_accessibility,
            'accommodation_hunting_features' => $accommodation_hunting_features,
            'accommodation_property_condition' => $accommodation_property_condition,
            'hunt_slot_from_hour' => $hunt_slot_from_hour,
            'hunt_slot_to_hour' => $hunt_slot_to_hour,
            'hunt_capacity' => $hunt_capacity,
            'hunt_species_list' => $hunt_species_list,
            'hunt_lease_type' => $hunt_lease_type,
            'hunt_type' => $hunt_type,
            'hunt_game_species' => $hunt_game_species,
            'hunt_country' => $hunt_country,
            'hunt_county' => $hunt_county,
            'hunt_price_type' => $hunt_price_type,
            'hunt_price_min' => $hunt_price_min,
            'hunt_methods' => $hunt_methods,
            'hunt_has_accommodation' => $hunt_has_accommodation,
            'hunt_guide_type' => $hunt_guide_type,
            'hunt_difficulty_level' => $hunt_difficulty_level,
            'hunt_season_start' => $hunt_season_start,
            'hunt_season_end' => $hunt_season_end,
            'hunt_damage_main_type' => $hunt_damage_main_type,
            'hunt_settlement' => $hunt_settlement,
            'hunt_land_type' => $hunt_land_type,
            'hunt_response_time' => $hunt_response_time,
            'hunt_availability_mode' => $hunt_availability_mode,
            'hunt_executor_type' => $hunt_executor_type,
            'hunt_executor_capacity' => $hunt_executor_capacity,
            'hunt_tools' => $hunt_tools,
            'hunt_weapon_use_required' => $hunt_weapon_use_required,
            'hunt_land_manager_permit' => $hunt_land_manager_permit,
            'hunt_hunting_company_coop' => $hunt_hunting_company_coop,
            'hunt_liability_insurance' => $hunt_liability_insurance,
            'hunt_damage_nature' => $hunt_damage_nature,
            'hunt_pricing_model' => $hunt_pricing_model,
            'hunt_travel_fee' => $hunt_travel_fee,
            'hunt_contract_type' => $hunt_contract_type,
            'hunt_site_conditions' => $hunt_site_conditions,
            'vadcamera_camera_type' => $vadcamera_camera_type,
            'vadcamera_connectivity' => $vadcamera_connectivity,
            'vadcamera_trigger_speed' => $vadcamera_trigger_speed,
            'vadcamera_ir_type' => $vadcamera_ir_type,
            'vadcamera_night_range_m' => $vadcamera_night_range_m,
            'vadcamera_video_resolution' => $vadcamera_video_resolution,
            'vadcamera_ip_rating' => $vadcamera_ip_rating,
            'vadcamera_sim_slot' => $vadcamera_sim_slot,
            'shoe_main_type' => $shoe_main_type,
            'shoe_eu_size' => $shoe_eu_size,
            'shoe_condition' => $shoe_condition,
            'shoe_boot_height' => $shoe_boot_height,
            'exchange_type' => $exchange_type,
            'exchange_offer_category' => $exchange_offer_category,
            'exchange_brand' => $exchange_brand,
            'exchange_condition' => $exchange_condition,
            'exchange_estimated_value' => $exchange_estimated_value,
            'exchange_extra_payment_direction' => $exchange_extra_payment_direction,
            'exchange_county' => $exchange_county,
            'publication_type' => $publication_type,
            'publication_topics' => $publication_topics,
            'publication_title' => $publication_title,
            'publication_author' => $publication_author,
            'publication_year' => $publication_year,
            'publication_language' => $publication_language,
            'publication_condition' => $publication_condition,
            'publication_collector_value' => $publication_collector_value,
            'publication_complete_volume' => $publication_complete_volume,
            'equipment_type' => $equipment_type,
            'equipment_condition' => $equipment_condition,
            'equipment_hunting_usage' => $equipment_hunting_usage,
            'bag_type' => $bag_type,
            'volume_l' => $volume_l,
            'bag_material' => $bag_material,
            'bag_weather_resistance' => $bag_weather_resistance,
            'bag_condition' => $bag_condition,
            'gear_type' => $gear_type,
            'shooting_discipline' => $shooting_discipline,
            'firearm_compatibility' => $firearm_compatibility,
            'sport_size' => $sport_size,
            'modular' => $modular,
            'competition_level' => $competition_level,
            'sport_condition' => $sport_condition,
            'call_type' => $call_fields['call_type'],
            'call_target_species' => $call_fields['call_target_species'],
            'call_operation_mode' => $call_fields['call_operation_mode'],
            'call_material_type' => $call_fields['call_material_type'],
            'call_volume_level' => $call_fields['call_volume_level'],
            'call_style' => $call_fields['call_style'],
            'decor_type' => $decor_type,
            'decor_material' => $decor_material,
            'decor_style' => $decor_style,
            'decor_motif_tags' => $decor_motif_tags,
            'decor_production_type' => $decor_production_type,
            'decor_size_class' => $decor_size_class,
            'decor_condition' => $decor_condition,
            'estate_main_type' => $estate_main_type,
        ] );
        if ( $rule_error !== '' ) {
            wp_send_json_error( [ 'message' => $rule_error ] );
        }

        $vehicle_error = self::validate_vehicle_required_fields( $category, [
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'mileage' => sanitize_text_field( wp_unslash( $_POST['mileage'] ?? '' ) ),
            'fuel_type' => sanitize_key( wp_unslash( $_POST['fuel_type'] ?? '' ) ),
            'transmission' => sanitize_key( wp_unslash( $_POST['transmission'] ?? '' ) ),
            'drive' => sanitize_key( wp_unslash( $_POST['drive'] ?? '' ) ),
            'vehicle_condition' => sanitize_key( wp_unslash( $_POST['vehicle_condition'] ?? '' ) ),
            'performance_kw' => sanitize_text_field( wp_unslash( $_POST['performance_kw'] ?? '' ) ),
            'engine_size' => sanitize_text_field( wp_unslash( $_POST['engine_size'] ?? '' ) ),
        ] );
        if ( $vehicle_error !== '' ) {
            wp_send_json_error( [ 'message' => $vehicle_error ] );
        }

        $estate_term = $category > 0 ? get_term( $category, 'va_category' ) : null;
        $is_estate_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'vadaszati-hagyatek';
        $estate_moderation_hits = $is_estate_category ? self::detect_estate_moderation_hits( $title, $description, $estate_notes ) : [];
        $is_vadcamera_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'vadkamera';
        $vadcamera_moderation_hits = $is_vadcamera_category ? self::detect_vadcamera_moderation_hits( $title, $description, $vadcamera_notes ) : [];
        $is_shoe_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'cipo-bakancs';
        $shoe_moderation_hits = $is_shoe_category ? self::detect_shoe_moderation_hits( $title, $description, $shoe_notes ) : [];
        $is_knife_category = $estate_term && ! is_wp_error( $estate_term ) && in_array( sanitize_title( (string) $estate_term->slug ), [ 'kesek', 'vadaszkes-vadasztor', 'taktikai-kes-taktikai-tor', 'konyhakes', 'svajci-bicska' ], true );
        $knife_moderation_hits = $is_knife_category ? self::detect_knife_moderation_hits( $title, $description, $knife_moderation_flags ) : [];
        $is_marok_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'maroklofegyver';
        $marok_moderation_hits = $is_marok_category ? self::detect_marok_moderation_hits( $title, $description, $marok_moderation_flags ) : [];
        $is_mixed_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'vegyescsovu-puska';
        $mixed_moderation_hits = $is_mixed_category ? self::detect_mixed_moderation_hits( $title, $description ) : [];
        $is_rifle_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'golyos-puska';
        $rifle_moderation_hits = $is_rifle_category && $rifle_moderation_flags !== '' ? array_filter( array_map( 'trim', explode( ',', $rifle_moderation_flags ) ) ) : [];
        $is_service_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'szolgaltatas';
        $service_moderation_hits = $is_service_category ? self::detect_service_moderation_hits( $title, $description, $service_reference_notes . "\n" . $service_moderation_flags ) : [];
        $is_trophy_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'trofea-aletet';
        $trophy_moderation_hits = $is_trophy_category ? self::detect_trophy_moderation_hits( $title, $description, $trophy_moderation_flags . "\n" . $trophy_maker ) : [];
        $is_hunt_category = $estate_term && ! is_wp_error( $estate_term ) && in_array( sanitize_title( (string) $estate_term->slug ), [ 'vadaszati-lehetoseg', 'vadkarelharitas' ], true );
        $hunt_moderation_hits = $is_hunt_category ? self::detect_hunt_moderation_hits( $title, $description, $hunt_moderation_flags . "\n" . $hunt_area_name . "\n" . $hunt_game_species ) : [];

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
            'va_postal_code' => $postal_code,
            'va_street'      => $street,
            'va_other_category' => $other_category,
            'va_email_show'  => '1',
            'va_brand'       => $brand,
            'va_model'       => $model,
            'va_caliber'     => $caliber,
            'va_year'        => $year,
            'va_license_req' => $license_req,
            'va_mixed_type' => $mixed_type,
            'va_mixed_barrel_count' => $mixed_barrel_count,
            'va_mixed_has_rifle' => $mixed_has_rifle,
            'va_mixed_rifle_caliber' => $mixed_rifle_caliber,
            'va_mixed_has_shotgun' => $mixed_has_shotgun,
            'va_mixed_shotgun_caliber' => $mixed_shotgun_caliber,
            'va_mixed_trigger_type' => $mixed_trigger_type,
            'va_mixed_action_system' => $mixed_action_system,
            'va_mixed_lock_type' => $mixed_lock_type,
            'va_mixed_sight_type' => $mixed_sight_type,
            'va_mixed_optic_rail' => $mixed_optic_rail,
            'va_mixed_optic_compatible' => $mixed_optic_compatible,
            'va_mixed_barrel_length_cm' => $mixed_barrel_length_cm,
            'va_mixed_barrel_material' => $mixed_barrel_material,
            'va_mixed_barrel_regulated' => $mixed_barrel_regulated,
            'va_mixed_material' => $mixed_material,
            'va_mixed_stock_material' => $mixed_stock_material,
            'va_mixed_usage' => $mixed_usage,
            'va_mixed_weight_kg' => $mixed_weight_kg,
            'va_mixed_total_length_cm' => $mixed_total_length_cm,
            'va_mixed_accessories' => $mixed_accessories,
            'va_mixed_rifle_moa' => $mixed_rifle_moa,
            'va_mixed_shot_pattern' => $mixed_shot_pattern,
            'va_mixed_zero_distance_m' => $mixed_zero_distance_m,
            'va_mixed_safety_type' => $mixed_safety_type,
            'va_mixed_barrel_layout' => $mixed_barrel_layout,
            'va_mixed_barrel_internal' => $mixed_barrel_internal,
            'va_mixed_engraving' => $mixed_engraving,
            'va_mixed_factory_marking' => $mixed_factory_marking,
            'va_mixed_drilling_barrel1_caliber' => $mixed_drilling_barrel1_caliber,
            'va_mixed_drilling_top_barrel_role' => $mixed_drilling_top_barrel_role,
            'va_mixed_optic_mount_type' => $mixed_optic_mount_type,
            'va_mixed_mountain_weight_optimized' => $mixed_mountain_weight_optimized,
            'va_mixed_universal_label' => $mixed_universal_label,
            'va_mixed_condition' => $mixed_condition,
            'va_mixed_filter_type' => $mixed_type,
            'va_mixed_filter_rifle_caliber' => $mixed_rifle_caliber,
            'va_mixed_filter_shotgun_caliber' => $mixed_shotgun_caliber,
            'va_mixed_filter_barrel_count' => $mixed_barrel_count,
            'va_mixed_filter_brand' => sanitize_title( $brand ),
            'va_mixed_filter_condition' => $mixed_condition,
            'va_mixed_filter_optic' => $mixed_filter_optic,
            'va_mixed_filter_universal' => $mixed_filter_universal,
            'va_mixed_needs_review' => ! empty( $mixed_moderation_hits ) ? '1' : '0',
            'va_mixed_review_hits' => implode( ',', $mixed_moderation_hits ),
            'va_rifle_type' => $rifle_type,
            'va_rifle_caliber' => $rifle_caliber,
            'va_rifle_barrel_length_cm' => $rifle_barrel_length_cm,
            'va_rifle_barrel_material' => $rifle_barrel_material,
            'va_rifle_rifling_type' => $rifle_rifling_type,
            'va_rifle_barrel_condition' => $rifle_barrel_condition,
            'va_rifle_accuracy_moa' => $rifle_accuracy_moa,
            'va_rifle_zero_distance_m' => $rifle_zero_distance_m,
            'va_rifle_factory_group' => $rifle_factory_group,
            'va_rifle_action_type' => $rifle_action_type,
            'va_rifle_trigger_type' => $rifle_trigger_type,
            'va_rifle_safety_type' => $rifle_safety_type,
            'va_rifle_stock_material' => $rifle_stock_material,
            'va_rifle_optic_rail_type' => $rifle_optic_rail_type,
            'va_rifle_optic_compatibility' => $rifle_optic_compatibility,
            'va_rifle_zero_return' => $rifle_zero_return,
            'va_rifle_accessories' => $rifle_accessories,
            'va_rifle_weight_kg' => $rifle_weight_kg,
            'va_rifle_total_length_cm' => $rifle_total_length_cm,
            'va_rifle_usage' => $rifle_usage,
            'va_rifle_special_build' => $rifle_special_build,
            'va_rifle_barrel_mount_type' => $rifle_barrel_mount_type,
            'va_rifle_impact_safe' => $rifle_impact_safe,
            'va_rifle_closed_system' => $rifle_closed_system,
            'va_rifle_effective_range_m' => $rifle_effective_range_m,
            'va_rifle_stability_level' => $rifle_stability_level,
            'va_rifle_recoil_level' => $rifle_recoil_level,
            'va_rifle_temperament' => $rifle_temperament,
            'va_rifle_condition' => $rifle_condition,
            'va_rifle_mountain_weight_optimized' => $rifle_mountain_weight_optimized,
            'va_rifle_magazine_capacity' => $rifle_magazine_capacity,
            'va_rifle_moderation_flags' => $rifle_moderation_flags,
            'va_rifle_hunter_profile_game' => $rifle_hunter_profile_game,
            'va_rifle_hunter_profile_optic' => $rifle_hunter_profile_optic,
            'va_rifle_hunter_profile_distance' => $rifle_hunter_profile_distance,
            'va_rifle_filter_type' => $rifle_type,
            'va_rifle_filter_caliber' => $rifle_caliber_primary,
            'va_rifle_filter_brand' => sanitize_title( $brand ),
            'va_rifle_filter_barrel_length_cm' => $rifle_barrel_length_cm,
            'va_rifle_filter_condition' => $rifle_condition,
            'va_rifle_filter_optic' => $rifle_filter_optic,
            'va_rifle_filter_action_type' => $rifle_action_type,
            'va_rifle_needs_review' => ! empty( $rifle_moderation_hits ) ? '1' : '0',
            'va_rifle_review_hits' => implode( ',', $rifle_moderation_hits ),
            'va_marok_type' => $marok_type,
            'va_marok_condition' => $marok_condition,
            'va_marok_manufacture_year' => $marok_manufacture_year,
            'va_marok_origin_country' => $marok_origin_country,
            'va_marok_action_type' => $marok_action_type,
            'va_marok_magazine_capacity' => $marok_magazine_capacity,
            'va_marok_cylinder_capacity' => $marok_cylinder_capacity,
            'va_marok_revolver_action' => $marok_revolver_action,
            'va_marok_barrel_length_mm' => $marok_barrel_length_mm,
            'va_marok_total_length_mm' => $marok_total_length_mm,
            'va_marok_weight_g' => $marok_weight_g,
            'va_marok_frame_material' => $marok_frame_material,
            'va_marok_slide_material' => $marok_slide_material,
            'va_marok_sight_type' => $marok_sight_type,
            'va_marok_optic_ready' => $marok_optic_ready,
            'va_marok_optic_mount' => $marok_optic_mount,
            'va_marok_red_dot_compatibility' => $marok_red_dot_compatibility,
            'va_marok_threaded_barrel' => $marok_threaded_barrel,
            'va_marok_rail_type' => $marok_rail_type,
            'va_marok_accessories' => $marok_accessories,
            'va_marok_license_required' => $marok_license_required,
            'va_marok_legal_category' => $marok_legal_category,
            'va_marok_technical_exam_valid' => $marok_technical_exam_valid,
            'va_marok_cip_marking' => $marok_cip_marking,
            'va_marok_transfer_license_only' => $marok_transfer_license_only,
            'va_marok_personal_pickup' => $marok_personal_pickup,
            'va_marok_serial_masked' => $marok_serial_masked,
            'va_marok_usage' => $marok_usage,
            'va_marok_price_deal' => $marok_price_deal,
            'va_marok_price_exchange' => $marok_price_exchange,
            'va_marok_transfer_methods' => $marok_transfer_methods,
            'va_marok_required_media' => $marok_required_media,
            'va_marok_recommended_media' => $marok_recommended_media,
            'va_marok_round_count' => $marok_round_count,
            'va_marok_trigger_tuning' => $marok_trigger_tuning,
            'va_marok_sport_categories' => $marok_sport_categories,
            'va_marok_ipsc_ready' => $marok_ipsc_ready,
            'va_marok_race_trigger' => $marok_race_trigger,
            'va_marok_moderation_flags' => $marok_moderation_flags,
            'va_marok_filter_caliber' => $caliber,
            'va_marok_filter_barrel_length_mm' => $marok_barrel_length_mm,
            'va_marok_filter_magazine_capacity' => $marok_filter_magazine_capacity,
            'va_marok_filter_action_type' => $marok_action_type,
            'va_marok_filter_optic_ready' => $marok_filter_optic_ready,
            'va_marok_filter_threaded_barrel' => $marok_filter_threaded_barrel,
            'va_marok_filter_frame_material' => $marok_frame_material,
            'va_marok_needs_review' => ! empty( $marok_moderation_hits ) ? '1' : '0',
            'va_marok_review_hits' => implode( ',', $marok_moderation_hits ),
            'va_optic_type'  => $optic_type,
            'va_optic_zoom'  => $optic_zoom,
            'va_optic_objective' => $optic_objective,
            'va_optic_magnification_min' => $optic_magnification_min,
            'va_optic_magnification_max' => $optic_magnification_max,
            'va_optic_magnification_fixed' => $optic_magnification_fixed,
            'va_optic_tube_diameter' => $optic_tube_diameter,
            'va_optic_exit_pupil' => $optic_exit_pupil,
            'va_optic_field_of_view' => $optic_field_of_view,
            'va_optic_eye_relief' => $optic_eye_relief,
            'va_optic_reticle_type' => $optic_reticle_type,
            'va_optic_reticle_plane' => $optic_reticle_plane,
            'va_optic_illumination' => $optic_illumination,
            'va_optic_illumination_color' => $optic_illumination_color,
            'va_optic_click_value' => $optic_click_value,
            'va_optic_parallax_adjustment' => $optic_parallax_adjustment,
            'va_optic_turret_type' => $optic_turret_type,
            'va_optic_body_material' => $optic_body_material,
            'va_optic_coating_type' => $optic_coating_type,
            'va_optic_waterproof_flags' => $optic_waterproof_flags,
            'va_optic_prism_system' => $optic_prism_system,
            'va_optic_glass_type' => $optic_glass_type,
            'va_optic_dot_size' => $optic_dot_size,
            'va_optic_rail_compatibility' => $optic_rail_compatibility,
            'va_optic_rangefinder_max_m' => $optic_rangefinder_max_m,
            'va_optic_ballistic_calc' => $optic_ballistic_calc,
            'va_optic_spektiv_zoom_range' => $optic_spektiv_zoom_range,
            'va_optic_eyepiece_type' => $optic_eyepiece_type,
            'va_optic_weapon_compatibility' => $optic_weapon_compatibility,
            'va_scope_accessories_flags' => $scope_accessories_flags,
            'va_optic_mount_type' => $optic_mount_type,
            'va_optic_shipping_methods' => $optic_shipping_methods,
            'va_optic_recommended_use' => $optic_recommended_use,
            'va_optic_moderation_flags' => $optic_moderation_flags,
            'va_thermal_device_type' => $thermal_device_type,
            'va_thermal_condition' => $thermal_condition,
            'va_thermal_warranty' => $thermal_warranty,
            'va_thermal_manufacture_year' => $thermal_manufacture_year,
            'va_thermal_sensor_resolution' => $thermal_sensor_resolution,
            'va_thermal_pixel_pitch' => $thermal_pixel_pitch,
            'va_thermal_netd' => $thermal_netd,
            'va_thermal_refresh_hz' => $thermal_refresh_hz,
            'va_thermal_base_magnification' => $thermal_base_magnification,
            'va_thermal_digital_zoom' => $thermal_digital_zoom,
            'va_thermal_lens_size' => $thermal_lens_size,
            'va_thermal_fov_degree' => $thermal_fov_degree,
            'va_thermal_fov_m100' => $thermal_fov_m100,
            'va_thermal_detect_human_m' => $thermal_detect_human_m,
            'va_thermal_detect_game_m' => $thermal_detect_game_m,
            'va_thermal_detect_vehicle_m' => $thermal_detect_vehicle_m,
            'va_thermal_display_type' => $thermal_display_type,
            'va_thermal_display_resolution' => $thermal_display_resolution,
            'va_thermal_palettes' => $thermal_palettes,
            'va_thermal_photo' => $thermal_photo,
            'va_thermal_video' => $thermal_video,
            'va_thermal_audio' => $thermal_audio,
            'va_thermal_internal_memory_gb' => $thermal_internal_memory_gb,
            'va_thermal_sd_support' => $thermal_sd_support,
            'va_thermal_connectivity' => $thermal_connectivity,
            'va_thermal_mobile_app' => $thermal_mobile_app,
            'va_thermal_extra_features' => $thermal_extra_features,
            'va_thermal_battery_type' => $thermal_battery_type,
            'va_thermal_runtime_hours' => $thermal_runtime_hours,
            'va_thermal_powerbank_compatible' => $thermal_powerbank_compatible,
            'va_thermal_ip_rating' => $thermal_ip_rating,
            'va_thermal_recoil_resistance' => $thermal_recoil_resistance,
            'va_thermal_weapon_compatibility' => $thermal_weapon_compatibility,
            'va_thermal_accessories' => $thermal_accessories,
            'va_dog_age_months' => $dog_age_months,
            'va_dog_working_type' => $dog_working_type,
            'va_dog_breed'   => $dog_breed,
            'va_dog_name' => $dog_name,
            'va_dog_gender'  => $dog_gender,
            'va_dog_breeder_name' => $dog_breeder_name,
            'va_dog_kennel_name' => $dog_kennel_name,
            'va_dog_pedigree_status' => $dog_pedigree_status,
            'va_dog_origin_country' => $dog_origin_country,
            'va_dog_bloodline_type' => $dog_bloodline_type,
            'va_dog_documents' => $dog_documents,
            'va_dog_birth_date' => $dog_birth_date,
            'va_dog_color'   => $dog_color,
            'va_dog_appearance_type' => $dog_appearance_type,
            'va_dog_purebred'=> $dog_purebred,
            'va_dog_training_skills' => $dog_training_skills,
            'va_dog_training_level' => $dog_training_level,
            'va_dog_exam_level' => $dog_exam_level,
            'va_dog_work_exam_type' => $dog_work_exam_type,
            'va_dog_hunting_specialization' => $dog_hunting_specialization,
            'va_dog_work_style' => $dog_work_style,
            'va_dog_search_radius_m' => $dog_search_radius_m,
            'va_dog_endurance_level' => $dog_endurance_level,
            'va_dog_aggression_level' => $dog_aggression_level,
            'va_dog_apport_stability' => $dog_apport_stability,
            'va_dog_water_work_ability' => $dog_water_work_ability,
            'va_dog_water_work' => $dog_water_work,
            'va_dog_water_work_level' => $dog_water_work_level,
            'va_dog_cold_water_tolerance' => $dog_cold_water_tolerance,
            'va_dog_temperament' => $dog_temperament,
            'va_dog_environment' => $dog_environment,
            'va_dog_allergic' => $dog_allergic,
            'va_dog_special_diet' => $dog_special_diet,
            'va_dog_known_diseases' => $dog_known_diseases,
            'va_dog_hip_dysplasia_screening' => $dog_hip_dysplasia_screening,
            'va_dog_height_cm' => $dog_height_cm,
            'va_dog_weight_kg' => $dog_weight_kg,
            'va_dog_country' => $dog_country,
            'va_dog_county' => $dog_county,
            'va_dog_city' => $dog_city,
            'va_dog_origin_qualification' => $dog_origin_qualification,
            'va_dog_litter_count' => $dog_litter_count,
            'va_dog_available_from' => $dog_available_from,
            'va_dog_tracking_distance_m' => $dog_tracking_distance_m,
            'va_dog_tracking_experience' => $dog_tracking_experience,
            'va_dog_moderation_flags' => $dog_moderation_flags,
            'va_dog_work_video_types' => $dog_work_video_types,
            'va_dog_work_video_url' => $dog_work_video_url,
            'va_dog_filter_breed' => $dog_breed,
            'va_dog_filter_specialization' => $dog_hunting_specialization,
            'va_dog_filter_training_level' => $dog_training_level,
            'va_dog_filter_working_type' => $dog_working_type,
            'va_dog_filter_pedigree' => $dog_filter_pedigree,
            'va_dog_filter_bloodline' => $dog_bloodline_type,
            'va_dog_filter_age_months' => $dog_age_months,
            'va_dog_filter_has_work_exam' => $dog_filter_has_work_exam,
            'va_dog_filter_water_work' => $dog_filter_water_work,
            'va_dog_review_hits' => implode( ',', $dog_review_hits ),
            'va_job_location' => $job_location,
            'va_job_type'   => $job_type,
            'va_service_type' => $service_type,
            'va_service_provider_type' => $service_provider_type,
            'va_service_provider_name' => $service_provider_name,
            'va_service_country' => $service_country,
            'va_service_county' => $service_county,
            'va_service_town' => $service_town,
            'va_service_area' => $service_area,
            'va_service_gps_lat' => $service_gps_lat,
            'va_service_gps_lng' => $service_gps_lng,
            'va_service_pricing_type' => $service_pricing_type,
            'va_service_min_price' => $service_min_price,
            'va_service_travel_fee' => $service_travel_fee,
            'va_service_schedule' => $service_schedule,
            'va_service_available_weekend' => $service_available_weekend,
            'va_service_available_24_7' => $service_available_24_7,
            'va_service_seasonal' => $service_seasonal,
            'va_service_hunting_specialization' => $service_hunting_specialization,
            'va_service_hunting_species' => $service_hunting_species,
            'va_service_weapon_categories' => $service_weapon_categories,
            'va_service_equipment' => $service_equipment,
            'va_service_permits' => $service_permits,
            'va_service_reference_rating' => $service_reference_rating,
            'va_service_completed_jobs' => $service_completed_jobs,
            'va_service_reference_notes' => $service_reference_notes,
            'va_service_media_required' => $service_media_required,
            'va_service_media_video' => $service_media_video,
            'va_service_verified_provider' => $service_verified_provider,
            'va_service_document_verified' => $service_document_verified,
            'va_service_phone_verified' => $service_phone_verified,
            'va_service_business_verified' => $service_business_verified,
            'va_service_sos_service' => $service_sos_service,
            'va_service_urgent_repair' => $service_urgent_repair,
            'va_service_full_prep' => $service_full_prep,
            'va_service_night_work' => $service_night_work,
            'va_service_hound_breed' => $service_hound_breed,
            'va_service_caliber_support' => $service_caliber_support,
            'va_service_moderation_flags' => $service_moderation_flags,
            'va_service_needs_review' => ! empty( $service_moderation_hits ) ? '1' : '0',
            'va_service_review_hits' => implode( ',', $service_moderation_hits ),
            'va_service_filter_type' => $service_type,
            'va_service_filter_area' => $service_area,
            'va_service_filter_pricing_type' => $service_pricing_type,
            'va_service_filter_available_weekend' => $service_available_weekend,
            'va_service_filter_verified_provider' => $service_verified_provider,
            'va_service_filter_hunting_specialization' => $service_hunting_specialization,
            'va_service_filter_sos_service' => $service_sos_service,
            'va_clothing_type' => $clothing_type,
            'va_clothing_condition' => $clothing_condition,
            'va_clothing_gender' => $clothing_gender,
            'va_clothing_size_system' => $clothing_size_system,
            'va_clothing_size' => $clothing_size,
            'va_clothing_accessory_size' => $clothing_accessory_size,
            'va_clothing_glove_size' => $clothing_glove_size,
            'va_clothing_hat_size' => $clothing_hat_size,
            'va_clothing_hat_cm' => $clothing_hat_cm,
            'va_clothing_belt_length_cm' => $clothing_belt_length_cm,
            'va_clothing_colors' => $clothing_colors,
            'va_clothing_materials' => $clothing_materials,
            'va_clothing_protections' => $clothing_protections,
            'va_clothing_season' => $clothing_season,
            'va_clothing_use_cases' => $clothing_use_cases,
            'va_clothing_special_features' => $clothing_special_features,
            'va_clothing_glove_features' => $clothing_glove_features,
            'va_clothing_headwear_features' => $clothing_headwear_features,
            'va_clothing_poncho_water_column' => $clothing_poncho_water_column,
            'va_clothing_poncho_ultralight' => $clothing_poncho_ultralight,
            'va_clothing_poncho_backpack_compatible' => $clothing_poncho_backpack_compatible,
            'va_clothing_ghillie_type' => $clothing_ghillie_type,
            'va_clothing_ghillie_pattern' => $clothing_ghillie_pattern,
            'va_season' => $season,
            'va_hunting_usage' => $hunting_usage,
            'va_noise_level' => $noise_level,
            'va_material_tech' => $material_tech,
            'va_waterproof_level' => $waterproof_level,
            'va_camo_pattern' => $camo_pattern,
            'va_silence_index' => $silence_index,
            'va_clothing_weather_protection' => $clothing_weather_protection,
            'va_clothing_functional_details' => $clothing_functional_details,
            'va_clothing_fit' => $clothing_fit,
            'va_clothing_size_fit' => $clothing_size_fit,
            'va_clothing_hunter_features' => $clothing_hunter_features,
            'va_clothing_gear_compatibility' => $clothing_gear_compatibility,
            'va_clothing_durability' => $clothing_durability,
            'va_clothing_layering' => $clothing_layering,
            'va_clothing_membrane_type' => $clothing_membrane_type,
            'va_clothing_ir_reduction' => $clothing_ir_reduction,
            'va_clothing_filter_type' => $clothing_type,
            'va_clothing_filter_size' => $clothing_size,
            'va_clothing_filter_thermo' => $clothing_filter_thermo,
            'va_clothing_filter_waterproof' => $clothing_filter_waterproof,
            'va_clothing_filter_camo' => $clothing_filter_camo,
            'va_clothing_filter_silent' => $clothing_filter_silent,
            'va_clothing_filter_season' => $clothing_season,
            'va_clothing_filter_brand' => $brand,
            'va_clothing_filter_hunting_usage' => $clothing_filter_hunting_usage,
            'va_clothing_filter_noise_level' => $clothing_filter_noise_level,
            'va_clothing_filter_material_tech' => $clothing_filter_material_tech,
            'va_clothing_filter_camo_pattern' => $clothing_filter_camo_pattern,
            'va_clothing_filter_season_mode' => $clothing_filter_season_mode,
            'va_knife_type' => $knife_type,
            'va_knife_condition' => $knife_condition,
            'va_knife_manufacture_year' => $knife_manufacture_year,
            'va_knife_total_length_cm' => $knife_total_length_cm,
            'va_knife_blade_length_mm' => $knife_blade_length_mm,
            'va_knife_blade_thickness_mm' => $knife_blade_thickness_mm,
            'va_knife_weight_g' => $knife_weight_g,
            'va_knife_steel_type' => $knife_steel_type,
            'va_knife_blade_shape' => $knife_blade_shape,
            'va_knife_blade_edge' => $knife_blade_edge,
            'va_knife_finish' => $knife_finish,
            'va_knife_construction' => $knife_construction,
            'va_knife_locking_type' => $knife_locking_type,
            'va_knife_one_hand_opening' => $knife_one_hand_opening,
            'va_knife_handle_material' => $knife_handle_material,
            'va_knife_handle_color' => $knife_handle_color,
            'va_knife_has_sheath' => $knife_has_sheath,
            'va_knife_sheath_type' => $knife_sheath_type,
            'va_knife_belt_carry' => $knife_belt_carry,
            'va_knife_usage' => $knife_usage,
            'va_knife_features' => $knife_features,
            'va_knife_age_restriction' => $knife_age_restriction,
            'va_knife_restricted' => $knife_restricted,
            'va_knife_public_carry' => $knife_public_carry,
            'va_knife_accessories' => $knife_accessories,
            'va_knife_price_deal' => $knife_price_deal,
            'va_knife_price_exchange' => $knife_price_exchange,
            'va_knife_shipping_methods' => $knife_shipping_methods,
            'va_knife_required_media' => $knife_required_media,
            'va_knife_sharpening_state' => $knife_sharpening_state,
            'va_knife_collectible_flags' => $knife_collectible_flags,
            'va_knife_moderation_flags' => $knife_moderation_flags,
            'va_knife_axe_head_weight_g' => $knife_axe_head_weight_g,
            'va_knife_axe_handle_material' => $knife_axe_handle_material,
            'va_knife_multitool_function_count' => $knife_multitool_function_count,
            'va_knife_filter_type' => $knife_filter_type,
            'va_knife_filter_blade_length_mm' => $knife_blade_length_mm,
            'va_knife_filter_steel_type' => $knife_steel_type,
            'va_knife_filter_locking_type' => $knife_locking_type,
            'va_knife_filter_handle_material' => $knife_handle_material,
            'va_knife_filter_blade_shape' => $knife_blade_shape,
            'va_knife_filter_full_tang' => $knife_filter_full_tang,
            'va_knife_filter_bushcraft' => $knife_filter_bushcraft,
            'va_knife_filter_vadasz' => $knife_filter_vadasz,
            'va_knife_filter_condition' => $knife_condition,
            'va_knife_needs_review' => ! empty( $knife_moderation_hits ) ? '1' : '0',
            'va_knife_review_hits' => implode( ',', $knife_moderation_hits ),
            'va_knife_blade_length' => $knife_blade_length,
            'va_trophy_species' => $trophy_species,
            'va_trophy_type' => $trophy_type,
            'va_trophy_maker' => $trophy_maker,
            'va_trophy_custom_made' => $trophy_custom_made,
            'va_trophy_condition' => $trophy_condition,
            'va_trophy_manufacture_year' => $trophy_manufacture_year,
            'va_trophy_height_cm' => $trophy_height_cm,
            'va_trophy_width_cm' => $trophy_width_cm,
            'va_trophy_thickness_cm' => $trophy_thickness_cm,
            'va_trophy_compatible_species' => $trophy_compatible_species,
            'va_trophy_mount_type' => $trophy_mount_type,
            'va_trophy_mounting_type' => $trophy_mounting_type,
            'va_trophy_attachment_type' => $trophy_attachment_type,
            'va_trophy_style' => $trophy_style,
            'va_trophy_fang_mount' => $trophy_fang_mount,
            'va_trophy_material_type' => $trophy_material_type,
            'va_trophy_material' => $trophy_material,
            'va_trophy_surface_finish' => $trophy_surface_finish,
            'va_trophy_finish' => $trophy_finish,
            'va_trophy_decoration_flags' => $trophy_decoration_flags,
            'va_trophy_weight_kg' => $trophy_weight_kg,
            'va_trophy_usage_context' => $trophy_usage_context,
            'va_trophy_accessories' => $trophy_accessories,
            'va_trophy_price_negotiable' => $trophy_price_negotiable,
            'va_trophy_custom_order' => $trophy_custom_order,
            'va_trophy_shipping_methods' => $trophy_shipping_methods,
            'va_trophy_fragile_handling' => $trophy_fragile_handling,
            'va_trophy_required_media' => $trophy_required_media,
            'va_trophy_lead_time' => $trophy_lead_time,
            'va_trophy_orderable_sizes' => $trophy_orderable_sizes,
            'va_trophy_custom_engraving' => $trophy_custom_engraving,
            'va_trophy_house_style' => $trophy_house_style,
            'va_trophy_moderation_flags' => $trophy_moderation_flags,
            'va_trophy_needs_review' => ! empty( $trophy_moderation_hits ) ? '1' : '0',
            'va_trophy_review_hits' => implode( ',', $trophy_moderation_hits ),
            'va_trophy_filter_species' => $trophy_compatible_species,
            'va_trophy_filter_material_type' => $trophy_material_type,
            'va_trophy_filter_height_cm' => $trophy_height_cm,
            'va_trophy_filter_width_cm' => $trophy_width_cm,
            'va_trophy_filter_thickness_cm' => $trophy_thickness_cm,
            'va_trophy_filter_style' => $trophy_style,
            'va_trophy_filter_wall_mount' => ( $trophy_mounting_type === 'falra-szerelheto' || $trophy_attachment_type === 'falra-szerelheto' ) ? '1' : '0',
            'va_trophy_filter_handmade' => $trophy_custom_made === 'igen' ? '1' : '0',
            'va_accommodation_type' => $accommodation_type,
            'va_accommodation_capacity' => $accommodation_capacity,
            'va_accommodation_slot_mode' => $accommodation_slot_mode,
            'va_accommodation_from_hour' => $accommodation_from_hour,
            'va_accommodation_to_hour' => $accommodation_to_hour,
            'va_accommodation_checkin_from' => $accommodation_checkin_from,
            'va_accommodation_checkin_to' => $accommodation_checkin_to,
            'va_accommodation_checkout_from' => $accommodation_checkout_from,
            'va_accommodation_checkout_to' => $accommodation_checkout_to,
            'va_accommodation_meal_type' => $accommodation_meal_type,
            'va_accommodation_parking' => $accommodation_parking,
            'va_accommodation_settlement' => $accommodation_settlement,
            'va_accommodation_county_text' => $accommodation_county_text,
            'va_accommodation_country' => $accommodation_country,
            'va_accommodation_exact_address' => $accommodation_exact_address,
            'va_accommodation_adult_capacity' => $accommodation_adult_capacity,
            'va_accommodation_room_count' => $accommodation_room_count,
            'va_accommodation_bed_count' => $accommodation_bed_count,
            'va_accommodation_extra_bed' => $accommodation_extra_bed,
            'va_accommodation_hunting_nearby' => $accommodation_hunting_nearby,
            'va_accommodation_hunt_available' => $accommodation_hunt_available,
            'va_accommodation_trophy_handling' => $accommodation_trophy_handling,
            'va_accommodation_cold_room' => $accommodation_cold_room,
            'va_accommodation_gun_storage' => $accommodation_gun_storage,
            'va_accommodation_game_processing' => $accommodation_game_processing,
            'va_accommodation_offroad_parking' => $accommodation_offroad_parking,
            'va_accommodation_dog_allowed' => $accommodation_dog_allowed,
            'va_accommodation_features' => $accommodation_features,
            'va_accommodation_min_nights' => $accommodation_min_nights,
            'va_accommodation_bookable_period' => $accommodation_bookable_period,
            'va_accommodation_instant_book' => $accommodation_instant_book,
            'va_accommodation_nearby_species' => $accommodation_nearby_species,
            'va_accommodation_programs' => $accommodation_programs,
            'va_accommodation_environment' => $accommodation_environment,
            'va_accommodation_land_size_sqm' => $accommodation_land_size_sqm,
            'va_accommodation_land_size_hectare' => $accommodation_land_size_hectare,
            'va_accommodation_building_size_sqm' => $accommodation_building_size_sqm,
            'va_accommodation_levels_count' => $accommodation_levels_count,
            'va_accommodation_build_year' => $accommodation_build_year,
            'va_accommodation_property_condition' => $accommodation_property_condition,
            'va_accommodation_construction_type' => $accommodation_construction_type,
            'va_accommodation_heating_type' => $accommodation_heating_type,
            'va_accommodation_utilities' => $accommodation_utilities,
            'va_accommodation_accessibility' => $accommodation_accessibility,
            'va_accommodation_hunting_features' => $accommodation_hunting_features,
            'va_accommodation_extras' => $accommodation_extras,
            'va_accommodation_network_features' => $accommodation_network_features,
            'va_accommodation_use_type' => $accommodation_use_type,
            'va_accommodation_terrain_type' => $accommodation_terrain_type,
            'va_accommodation_animal_housing' => $accommodation_animal_housing,
            'va_accommodation_fafaj' => $accommodation_fafaj,
            'va_accommodation_erdo_kora' => $accommodation_erdo_kora,
            'va_accommodation_vadaszhaz_trofeaszoba' => $accommodation_vadaszhaz_trofeaszoba,
            'va_accommodation_vadaszhaz_fegyvertarolo' => $accommodation_vadaszhaz_fegyvertarolo,
            'va_accommodation_tanya_gazdasagi_epuletek' => $accommodation_tanya_gazdasagi_epuletek,
            'va_accommodation_hunting_potential_wild_movement' => $accommodation_hunting_potential_wild_movement,
            'va_accommodation_hunting_potential_isolation' => $accommodation_hunting_potential_isolation,
            'va_accommodation_hunting_potential_forest_proximity' => $accommodation_hunting_potential_forest_proximity,
            'va_accommodation_hunting_potential_infrastructure' => $accommodation_hunting_potential_infrastructure,
            'va_accommodation_filter_property_type' => $accommodation_filter_property_type,
            'va_accommodation_filter_county' => $accommodation_filter_county,
            'va_accommodation_filter_land_size_sqm' => $accommodation_filter_land_size_sqm,
            'va_accommodation_filter_building_size_sqm' => $accommodation_filter_building_size_sqm,
            'va_accommodation_filter_utilities' => $accommodation_filter_utilities,
            'va_accommodation_filter_accessibility' => $accommodation_filter_accessibility,
            'va_accommodation_filter_hunting_features' => $accommodation_filter_hunting_features,
            'va_accommodation_filter_condition' => $accommodation_filter_condition,
            'va_hunt_slot_from_hour' => $hunt_slot_from_hour,
            'va_hunt_slot_to_hour' => $hunt_slot_to_hour,
            'va_hunt_capacity' => $hunt_capacity,
            'va_hunt_species_list' => $hunt_species_list,
            'va_hunt_lease_type' => $hunt_lease_type,
            'va_hunt_has_guide' => $hunt_has_guide,
            'va_hunt_has_tracking' => $hunt_has_tracking,
            'va_hunt_has_trophy_prep' => $hunt_has_trophy_prep,
            'va_hunt_has_trophy_judging' => $hunt_has_trophy_judging,
            'va_hunt_has_offroad' => $hunt_has_offroad,
            'va_hunt_has_accommodation' => $hunt_has_accommodation,
            'va_hunt_can_buy_game' => $hunt_can_buy_game,
            'va_hunt_type' => $hunt_type,
            'va_hunt_game_species' => $hunt_game_species,
            'va_hunt_country' => $hunt_country,
            'va_hunt_county' => $hunt_county,
            'va_hunt_area_name' => $hunt_area_name,
            'va_hunt_exact_area' => $hunt_exact_area,
            'va_hunt_gps_lat' => $hunt_gps_lat,
            'va_hunt_gps_lng' => $hunt_gps_lng,
            'va_hunt_terrain_types' => $hunt_terrain_types,
            'va_hunt_season_start' => $hunt_season_start,
            'va_hunt_season_end' => $hunt_season_end,
            'va_hunt_time_type' => $hunt_time_type,
            'va_hunt_price_type' => $hunt_price_type,
            'va_hunt_price_min' => $hunt_price_min,
            'va_hunt_price_max' => $hunt_price_max,
            'va_hunt_price_includes' => $hunt_price_includes,
            'va_hunt_guide_type' => $hunt_guide_type,
            'va_hunt_methods' => $hunt_methods,
            'va_hunt_onsite_services' => $hunt_onsite_services,
            'va_hunt_hunting_license_required' => $hunt_hunting_license_required,
            'va_hunt_permit_required' => $hunt_permit_required,
            'va_hunt_foreigners_allowed' => $hunt_foreigners_allowed,
            'va_hunt_min_age' => $hunt_min_age,
            'va_hunt_safety_features' => $hunt_safety_features,
            'va_hunt_conditions' => $hunt_conditions,
            'va_hunt_difficulty_level' => $hunt_difficulty_level,
            'va_hunt_expected_trophy_quality' => $hunt_expected_trophy_quality,
            'va_hunt_trophy_weight_age' => $hunt_trophy_weight_age,
            'va_hunt_required_media' => $hunt_required_media,
            'va_hunt_video_types' => $hunt_video_types,
            'va_hunt_moderation_flags' => $hunt_moderation_flags,
            'va_hunt_trophy_category' => $hunt_trophy_category,
            'va_hunt_trophy_weight_limit' => $hunt_trophy_weight_limit,
            'va_hunt_travel_assist' => $hunt_travel_assist,
            'va_hunt_interpreter' => $hunt_interpreter,
            'va_hunt_group_size' => $hunt_group_size,
            'va_hunt_shot_limit' => $hunt_shot_limit,
            'va_hunt_real_time_slots' => $hunt_real_time_slots,
            'va_hunt_bookable_dates' => $hunt_bookable_dates,
            'va_hunt_damage_main_type' => $hunt_damage_main_type,
            'va_hunt_settlement' => $hunt_settlement,
            'va_hunt_land_type' => $hunt_land_type,
            'va_hunt_response_time' => $hunt_response_time,
            'va_hunt_availability_mode' => $hunt_availability_mode,
            'va_hunt_executor_type' => $hunt_executor_type,
            'va_hunt_executor_capacity' => $hunt_executor_capacity,
            'va_hunt_tools' => $hunt_tools,
            'va_hunt_weapon_use_required' => $hunt_weapon_use_required,
            'va_hunt_land_manager_permit' => $hunt_land_manager_permit,
            'va_hunt_hunting_company_coop' => $hunt_hunting_company_coop,
            'va_hunt_liability_insurance' => $hunt_liability_insurance,
            'va_hunt_damage_nature' => $hunt_damage_nature,
            'va_hunt_pricing_model' => $hunt_pricing_model,
            'va_hunt_travel_fee' => $hunt_travel_fee,
            'va_hunt_contract_type' => $hunt_contract_type,
            'va_hunt_site_conditions' => $hunt_site_conditions,
            'va_hunt_reference_media' => $hunt_reference_media,
            'va_hunt_agri_hectare_size' => $hunt_agri_hectare_size,
            'va_hunt_agri_culture_type' => $hunt_agri_culture_type,
            'va_hunt_urgent_immediate_start' => $hunt_urgent_immediate_start,
            'va_hunt_urgent_duty_phone' => $hunt_urgent_duty_phone,
            'va_hunt_long_term_yearly_discount' => $hunt_long_term_yearly_discount,
            'va_hunt_package_auto_seasonal' => $hunt_package_auto_seasonal,
            'va_hunt_package_monthly_model' => $hunt_package_monthly_model,
            'va_hunt_package_monitoring_intervention' => $hunt_package_monitoring_intervention,
            'va_hunt_needs_review' => ! empty( $hunt_moderation_hits ) ? '1' : '0',
            'va_hunt_review_hits' => implode( ',', $hunt_moderation_hits ),
            'va_hunt_filter_game_species' => $hunt_game_species,
            'va_hunt_filter_country' => $hunt_country,
            'va_hunt_filter_county' => $hunt_county,
            'va_hunt_filter_price_min' => $hunt_price_min,
            'va_hunt_filter_price_max' => $hunt_price_max,
            'va_hunt_filter_methods' => $hunt_methods,
            'va_hunt_filter_method_types' => $hunt_method_types,
            'va_hunt_filter_land_type' => $hunt_land_type,
            'va_hunt_filter_response_time_hours' => (string) $hunt_response_time_hours,
            'va_hunt_filter_contract_type' => $hunt_contract_type,
            'va_hunt_filter_pricing_model' => $hunt_pricing_model,
            'va_hunt_filter_availability_24_7' => $hunt_availability_24_7,
            'va_hunt_filter_has_accommodation' => $hunt_has_accommodation === 'igen' ? '1' : '0',
            'va_hunt_filter_has_guide' => $hunt_guide_type === 'nincs' ? '0' : '1',
            'va_hunt_filter_difficulty_level' => $hunt_difficulty_level,
            'va_hunt_filter_season_start' => $hunt_season_start,
            'va_hunt_filter_season_end' => $hunt_season_end,
            'va_exchange_target' => $exchange_target,
            'va_exchange_type' => $exchange_type,
            'va_exchange_offer_category' => $exchange_offer_category,
            'va_exchange_item_name' => $exchange_item_name,
            'va_exchange_brand' => $exchange_brand,
            'va_exchange_model' => $exchange_model,
            'va_exchange_condition' => $exchange_condition,
            'va_exchange_short_desc' => $exchange_short_desc,
            'va_exchange_description' => $exchange_description,
            'va_exchange_defects' => $exchange_defects,
            'va_exchange_wear_signs' => $exchange_wear_signs,
            'va_exchange_estimated_value' => $exchange_estimated_value,
            'va_exchange_extra_payment_direction' => $exchange_extra_payment_direction,
            'va_exchange_extra_payment_amount' => $exchange_extra_payment_amount,
            'va_exchange_wanted_categories' => $exchange_wanted_categories,
            'va_exchange_country' => $exchange_country,
            'va_exchange_county' => $exchange_county,
            'va_exchange_city' => $exchange_city,
            'va_exchange_method' => $exchange_method,
            'va_exchange_legal_notes' => $exchange_legal_notes,
            'va_exchange_age_18' => $exchange_age_18,
            'va_exchange_required_images' => $exchange_required_images,
            'va_exchange_video_url' => $exchange_video_url,
            'va_exchange_moderation_flags' => $exchange_moderation_flags,
            'va_exchange_partner_requirements' => $exchange_partner_requirements,
            'va_exchange_firearm_caliber' => $exchange_firearm_caliber,
            'va_exchange_firearm_license' => $exchange_firearm_license,
            'va_exchange_optic_zoom' => $exchange_optic_zoom,
            'va_exchange_optic_objective' => $exchange_optic_objective,
            'va_exchange_knife_steel' => $exchange_knife_steel,
            'va_exchange_filter_offer_category' => $exchange_offer_category,
            'va_exchange_filter_wanted_categories' => $exchange_wanted_categories,
            'va_exchange_filter_estimated_value' => $exchange_estimated_value,
            'va_exchange_filter_exchange_type' => $exchange_type,
            'va_exchange_filter_extra_payment_direction' => $exchange_extra_payment_direction,
            'va_exchange_filter_extra_payment_amount' => $exchange_extra_payment_amount,
            'va_exchange_filter_condition' => $exchange_condition,
            'va_exchange_filter_county' => $exchange_county,
            'va_exchange_filter_brand' => sanitize_title( $exchange_brand ),
            'va_publication_type' => $publication_type,
            'va_publication_topics' => $publication_topics,
            'va_publication_title' => $publication_title,
            'va_publication_subtitle' => $publication_subtitle,
            'va_publication_author' => $publication_author,
            'va_publication_publisher' => $publication_publisher,
            'va_publication_year' => $publication_year,
            'va_publication_language' => $publication_language,
            'va_publication_edition_type' => $publication_edition_type,
            'va_publication_periodical_name' => $publication_periodical_name,
            'va_publication_volume' => $publication_volume,
            'va_publication_issue' => $publication_issue,
            'va_publication_complete_volume' => $publication_complete_volume,
            'va_publication_page_count' => $publication_page_count,
            'va_publication_cover_type' => $publication_cover_type,
            'va_publication_size' => $publication_size,
            'va_publication_condition' => $publication_condition,
            'va_publication_defects' => $publication_defects,
            'va_publication_content_flags' => $publication_content_flags,
            'va_publication_collector_flags' => $publication_collector_flags,
            'va_publication_collector_value' => $publication_collector_value,
            'va_publication_usage' => $publication_usage,
            'va_publication_condition_details' => $publication_condition_details,
            'va_publication_authenticity' => $publication_authenticity,
            'va_publication_signed_copy' => $publication_signed_copy,
            'va_publication_signed_to' => $publication_signed_to,
            'va_publication_signature_type' => $publication_signature_type,
            'va_publication_isbn' => $publication_isbn,
            'va_publication_digital_format' => $publication_digital_format,
            'va_publication_moderation_flags' => $publication_moderation_flags,
            'va_publication_notes' => $publication_notes,
            'va_publication_filter_type' => $publication_type,
            'va_publication_filter_topics' => $publication_topics,
            'va_publication_filter_author' => sanitize_title( $publication_author ),
            'va_publication_filter_year' => $publication_year,
            'va_publication_filter_language' => $publication_language,
            'va_publication_filter_condition' => $publication_condition,
            'va_publication_filter_collector_item' => $publication_filter_collector_item,
            'va_publication_filter_complete_volume' => $publication_filter_complete_volume,
            'va_publication_needs_review' => ! empty( $publication_review_hits ) ? '1' : '0',
            'va_publication_review_hits' => implode( ',', $publication_review_hits ),
            'va_equipment_type' => $equipment_type,
            'va_equipment_condition' => $equipment_condition,
            'va_equipment_hunting_usage' => $equipment_hunting_usage,
            'va_equipment_material_type' => $equipment_material_type,
            'va_equipment_color_pattern' => $equipment_color_pattern,
            'va_equipment_length_cm' => $equipment_length_cm,
            'va_equipment_width_cm' => $equipment_width_cm,
            'va_equipment_height_cm' => $equipment_height_cm,
            'va_equipment_weight_g' => $equipment_weight_g,
            'va_equipment_features' => $equipment_features,
            'va_equipment_light_lumen' => $equipment_light_lumen,
            'va_equipment_light_range_m' => $equipment_light_range_m,
            'va_equipment_light_battery_type' => $equipment_light_battery_type,
            'va_equipment_light_usb_charge' => $equipment_light_usb_charge,
            'va_equipment_light_color_modes' => $equipment_light_color_modes,
            'va_equipment_light_color_temperature' => $equipment_light_color_temperature,
            'va_light_type' => $light_type,
            'va_lumens' => $lumens,
            'va_light_levels_count' => $light_levels_count,
            'va_light_turbo_mode' => $light_turbo_mode,
            'va_light_eco_mode' => $light_eco_mode,
            'va_beam_distance_m' => $beam_distance_m,
            'va_beam_distance_band' => $beam_distance_band,
            'va_light_color' => $light_color,
            'va_battery_type' => $battery_type,
            'va_light_usb_rechargeable' => $light_usb_rechargeable,
            'va_light_powerbank_compatible' => $light_powerbank_compatible,
            'va_runtime_hours' => $runtime_hours,
            'va_runtime_eco_hours' => $runtime_eco_hours,
            'va_runtime_turbo_hours' => $runtime_turbo_hours,
            'va_light_material' => $light_material,
            'va_light_ip_rating' => $light_ip_rating,
            'va_light_resistance' => $light_resistance,
            'va_mount_type' => $mount_type,
            'va_light_mount_systems' => $light_mount_systems,
            'va_hunting_light_usage' => $hunting_light_usage,
            'va_light_functions' => $light_functions,
            'va_light_optical_properties' => $light_optical_properties,
            'va_light_weapon_type_compatibility' => $light_weapon_type_compatibility,
            'va_light_optics_compatible' => $light_optics_compatible,
            'va_light_ir_compatible' => $light_ir_compatible,
            'va_light_weight_g' => $light_weight_g,
            'va_light_length_mm' => $light_length_mm,
            'va_light_size_category' => $light_size_category,
            'va_recoil_resistance' => $recoil_resistance,
            'va_rail_compatibility' => $rail_compatibility,
            'va_ir_wavelength_nm' => $ir_wavelength_nm,
            'va_head_tiltable' => $head_tiltable,
            'va_headband_system' => $headband_system,
            'va_target_identification_index' => $target_identification_index,
            'va_equipment_support_height_range_cm' => $equipment_support_height_range_cm,
            'va_equipment_support_legs' => $equipment_support_legs,
            'va_equipment_support_head_swivel' => $equipment_support_head_swivel,
            'va_equipment_support_material' => $equipment_support_material,
            'va_equipment_call_type' => $equipment_call_type,
            'va_equipment_call_species' => $equipment_call_species,
            'va_equipment_call_volume' => $equipment_call_volume,
            'va_equipment_trap_type' => $equipment_trap_type,
            'va_equipment_trap_size' => $equipment_trap_size,
            'va_equipment_cleaning_caliber_compatibility' => $equipment_cleaning_caliber_compatibility,
            'va_equipment_cleaning_full_set' => $equipment_cleaning_full_set,
            'va_equipment_cleaning_oil' => $equipment_cleaning_oil,
            'va_equipment_cleaning_bronze_brush' => $equipment_cleaning_bronze_brush,
            'va_equipment_seat_foldable' => $equipment_seat_foldable,
            'va_equipment_seat_backrest' => $equipment_seat_backrest,
            'va_equipment_seat_swivel' => $equipment_seat_swivel,
            'va_equipment_seat_capacity_kg' => $equipment_seat_capacity_kg,
            'va_equipment_electronic_features' => $equipment_electronic_features,
            'va_equipment_runtime_hours' => $equipment_runtime_hours,
            'va_equipment_accessories' => $equipment_accessories,
            'va_equipment_silence_level' => $equipment_silence_level,
            'va_equipment_weapon_compatibility' => $equipment_weapon_compatibility,
            'va_equipment_caliber_compatibility' => $equipment_caliber_compatibility,
            'va_equipment_moderation_flags' => $equipment_moderation_flags,
            'va_equipment_notes' => $equipment_notes,
            'va_other_equipment_type' => $other_equipment_type,
            'va_other_equipment_function' => $other_equipment_function,
            'va_other_equipment_material_note' => $other_equipment_material_note,
            'va_other_equipment_compatibility' => $other_equipment_compatibility,
            'va_other_hunting_relevance' => $other_hunting_relevance,
            'va_other_uncertain_classification' => $other_uncertain_classification,
            'va_other_auto_suggested_category' => $other_auto_suggested_category,
            'va_other_auto_suggested_reason' => $other_auto_suggested_reason,
            'va_equipment_filter_type' => $equipment_type,
            'va_equipment_filter_usage' => $equipment_hunting_usage,
            'va_equipment_filter_condition' => $equipment_condition,
            'va_equipment_filter_brand' => sanitize_title( $brand ),
            'va_equipment_filter_waterproof' => $equipment_filter_waterproof,
            'va_equipment_filter_silent' => $equipment_filter_silent,
            'va_equipment_filter_foldable' => $equipment_filter_foldable,
            'va_equipment_filter_light_type' => $equipment_filter_light_type,
            'va_equipment_filter_light_color' => $equipment_filter_light_color,
            'va_equipment_filter_lumens' => $equipment_filter_lumens,
            'va_equipment_filter_beam_distance_m' => $equipment_filter_beam_distance_m,
            'va_equipment_filter_mount_type' => $equipment_filter_mount_type,
            'va_equipment_filter_battery_type' => $equipment_filter_battery_type,
            'va_equipment_filter_ip_rating' => $equipment_filter_ip_rating,
            'va_equipment_filter_recoil_resistance' => $equipment_filter_recoil_resistance,
            'va_equipment_filter_other_type' => $other_equipment_type,
            'va_equipment_filter_other_function' => sanitize_title( $other_equipment_function ),
            'va_equipment_filter_other_material' => sanitize_title( $other_equipment_material_note ),
            'va_equipment_filter_other_compatibility' => sanitize_title( $other_equipment_compatibility ),
            'va_equipment_filter_other_relevance' => $other_hunting_relevance,
            'va_equipment_filter_other_suggested_category' => $other_auto_suggested_category,
            'va_equipment_needs_review' => ! empty( $equipment_review_hits ) ? '1' : '0',
            'va_equipment_review_hits' => implode( ',', $equipment_review_hits ),
            'va_bag_type' => $bag_type,
            'va_volume_l' => $volume_l,
            'va_bag_size' => $bag_size,
            'va_bag_material' => $bag_material,
            'va_bag_weather_resistance' => $bag_weather_resistance,
            'va_bag_color_pattern' => $bag_color_pattern,
            'va_bag_camo_pattern' => $bag_camo_pattern,
            'va_bag_modular' => $bag_modular,
            'va_bag_design_features' => $bag_design_features,
            'va_bag_usage' => $bag_usage,
            'va_bag_firearm_compatible' => $bag_firearm_compatible,
            'va_bag_max_firearm_length_cm' => $bag_max_firearm_length_cm,
            'va_bag_inner_padding' => $bag_inner_padding,
            'va_bag_lockable' => $bag_lockable,
            'va_bag_hydration_system' => $bag_hydration_system,
            'va_bag_touring_compatible' => $bag_touring_compatible,
            'va_bag_mag_holder_count' => $bag_mag_holder_count,
            'va_bag_ammo_compartment_count' => $bag_ammo_compartment_count,
            'va_bag_main_compartments' => $bag_main_compartments,
            'va_bag_outer_pockets' => $bag_outer_pockets,
            'va_bag_internal_organizer' => $bag_internal_organizer,
            'va_bag_ammo_holder' => $bag_ammo_holder,
            'va_bag_document_compartment' => $bag_document_compartment,
            'va_bag_carrying_system' => $bag_carrying_system,
            'va_bag_max_load_kg' => $bag_max_load_kg,
            'va_bag_comfort' => $bag_comfort,
            'va_bag_closure_type' => $bag_closure_type,
            'va_bag_condition' => $bag_condition,
            'va_bag_packability_index' => $bag_packability_index,
            'va_bag_filter_type' => $bag_type,
            'va_bag_filter_volume_l' => $volume_l,
            'va_bag_filter_waterproof' => $bag_filter_waterproof,
            'va_bag_filter_molle' => $bag_filter_molle,
            'va_bag_filter_firearm_compatible' => $bag_filter_firearm_compatible,
            'va_bag_filter_color_pattern' => $bag_filter_color,
            'va_bag_filter_material' => $bag_filter_material,
            'va_bag_filter_condition' => $bag_condition,
            'va_bag_filter_modular' => $bag_filter_modular,
            'va_gear_type' => $gear_type,
            'va_shooting_discipline' => $shooting_discipline,
            'va_sport_material' => $sport_material,
            'va_firearm_compatibility' => $firearm_compatibility,
            'va_sport_size' => $sport_size,
            'va_sport_color' => $sport_color,
            'va_sport_ergonomics' => $sport_ergonomics,
            'va_modular' => $modular,
            'va_handedness' => $handedness,
            'va_sport_hearing_features' => $sport_hearing_features,
            'va_sport_nrr' => $sport_nrr,
            'va_sport_eyewear_features' => $sport_eyewear_features,
            'va_sport_precision_features' => $sport_precision_features,
            'va_sport_clothing_reinforcement' => $sport_clothing_reinforcement,
            'va_sport_belt_compatibility' => $sport_belt_compatibility,
            'va_sport_speed_setup' => $sport_speed_setup,
            'va_sport_accessories' => $sport_accessories,
            'va_sport_env_resistance' => $sport_env_resistance,
            'va_sport_condition' => $sport_condition,
            'va_competition_level' => $competition_level,
            'va_sport_filter_gear_type' => $gear_type,
            'va_sport_filter_discipline' => $sport_filter_discipline,
            'va_sport_filter_compatibility' => $sport_filter_compatibility,
            'va_sport_filter_size' => $sport_size,
            'va_sport_filter_modular' => $sport_filter_modular,
            'va_sport_filter_competition_level' => $competition_level,
            'va_sport_filter_condition' => $sport_condition,
            'va_call_type' => $call_fields['call_type'],
            'va_call_target_species' => $call_fields['call_target_species'],
            'va_call_sound_type' => $call_fields['call_sound_type'],
            'va_call_material_type' => $call_fields['call_material_type'],
            'va_call_operation_mode' => $call_fields['call_operation_mode'],
            'va_call_electronic_features' => $call_fields['call_electronic_features'],
            'va_call_volume_level' => $call_fields['call_volume_level'],
            'va_call_weather_resistance' => $call_fields['call_weather_resistance'],
            'va_call_size_class' => $call_fields['call_size_class'],
            'va_call_usage' => $call_fields['call_usage'],
            'va_call_sound_generation' => $call_fields['call_sound_generation'],
            'va_call_style' => $call_fields['call_style'],
            'va_call_accessories' => $call_fields['call_accessories'],
            'va_call_condition' => $call_fields['call_condition'],
            'va_call_sample_count' => $call_fields['call_sample_count'],
            'va_call_range_m' => $call_fields['call_range_m'],
            'va_call_runtime_hours' => $call_fields['call_runtime_hours'],
            'va_call_maker_name' => $call_fields['call_maker_name'],
            'va_call_unique_piece' => $call_fields['call_unique_piece'],
            'va_call_competition_standard' => $call_fields['call_competition_standard'],
            'va_call_tuning' => $call_fields['call_tuning'],
            'va_call_sound_sample_url' => $call_fields['call_sound_sample_url'],
            'va_call_filter_type' => $call_fields['call_filter_type'],
            'va_call_filter_target_species' => $call_fields['call_filter_target_species'],
            'va_call_filter_operation_mode' => $call_fields['call_filter_operation_mode'],
            'va_call_filter_material_type' => $call_fields['call_filter_material_type'],
            'va_call_filter_volume_level' => $call_fields['call_filter_volume_level'],
            'va_call_filter_style' => $call_fields['call_filter_style'],
            'va_call_filter_waterproof' => $call_fields['call_filter_waterproof'],
            'va_call_filter_is_electronic' => $call_fields['call_filter_is_electronic'],
            'va_decor_type' => $decor_type,
            'va_decor_material' => $decor_material,
            'va_decor_wood_type' => $decor_wood_type,
            'va_decor_metal_alloy' => $decor_metal_alloy,
            'va_decor_style' => $decor_style,
            'va_decor_motif_tags' => $decor_motif_tags,
            'va_decor_finish' => $decor_finish,
            'va_decor_detailing' => $decor_detailing,
            'va_decor_era' => $decor_era,
            'va_decor_production_type' => $decor_production_type,
            'va_decor_function' => $decor_function,
            'va_decor_thematic_group' => $decor_thematic_group,
            'va_decor_special_features' => $decor_special_features,
            'va_decor_mounting' => $decor_mounting,
            'va_decor_size_class' => $decor_size_class,
            'va_decor_condition' => $decor_condition,
            'va_decor_replica_type' => $decor_replica_type,
            'va_decor_authenticity_level' => $decor_authenticity_level,
            'va_decor_collectible_flag' => $decor_collectible_flag,
            'va_decor_filter_type' => $decor_type,
            'va_decor_filter_material' => $decor_material,
            'va_decor_filter_style' => $decor_style,
            'va_decor_filter_motif' => $decor_motif_tags,
            'va_decor_filter_production' => $decor_production_type,
            'va_decor_filter_size' => $decor_size_class,
            'va_decor_filter_condition' => $decor_condition,
            'va_decor_filter_collectible_flag' => $decor_filter_collectible,
            'va_shoe_size' => $shoe_size,
            'va_shoe_main_type' => $shoe_main_type,
            'va_shoe_condition' => $shoe_condition,
            'va_shoe_gender' => $shoe_gender,
            'va_shoe_eu_size' => $shoe_eu_size,
            'va_shoe_uk_size' => $shoe_uk_size,
            'va_shoe_us_size' => $shoe_us_size,
            'va_shoe_boot_height' => $shoe_boot_height,
            'va_shoe_outer_material' => $shoe_outer_material,
            'va_shoe_lining' => $shoe_lining,
            'va_shoe_sole_material' => $shoe_sole_material,
            'va_shoe_protections' => $shoe_protections,
            'va_shoe_season' => $shoe_season,
            'va_shoe_terrain' => $shoe_terrain,
            'va_shoe_use_cases' => $shoe_use_cases,
            'va_shoe_sole_pattern' => $shoe_sole_pattern,
            'va_shoe_stiffness' => $shoe_stiffness,
            'va_shoe_weight_single_g' => $shoe_weight_single_g,
            'va_shoe_weight_pair_g' => $shoe_weight_pair_g,
            'va_shoe_accessories' => $shoe_accessories,
            'va_shoe_shipping_methods' => $shoe_shipping_methods,
            'va_shoe_required_media' => $shoe_required_media,
            'va_shoe_rubber_shaft_cm' => $shoe_rubber_shaft_cm,
            'va_shoe_rubber_neoprene_lining' => $shoe_rubber_neoprene_lining,
            'va_shoe_tactical_steel_toe' => $shoe_tactical_steel_toe,
            'va_shoe_tactical_quick_lace' => $shoe_tactical_quick_lace,
            'va_shoe_winter_comfort_temp_c' => $shoe_winter_comfort_temp_c,
            'va_shoe_foot_type' => $shoe_foot_type,
            'va_shoe_sole_wear_percent' => $shoe_sole_wear_percent,
            'va_shoe_inner_wear' => $shoe_inner_wear,
            'va_shoe_waterproof_retained' => $shoe_waterproof_retained,
            'va_shoe_resoled' => $shoe_resoled,
            'va_shoe_notes' => $shoe_notes,
            'va_shoe_filter_eu_size' => $shoe_eu_size,
            'va_shoe_filter_waterproof' => $shoe_filter_waterproof,
            'va_shoe_filter_membrane_type' => $shoe_lining,
            'va_shoe_filter_goretex' => $shoe_filter_goretex,
            'va_shoe_filter_boot_height' => $shoe_boot_height,
            'va_shoe_filter_high_ankle' => $shoe_filter_high_ankle,
            'va_shoe_filter_season' => $shoe_season,
            'va_shoe_filter_season_winter' => $shoe_filter_winter,
            'va_shoe_filter_terrain' => $shoe_terrain,
            'va_vadcamera_camera_type' => $vadcamera_camera_type,
            'va_vadcamera_condition' => $vadcamera_condition,
            'va_vadcamera_warranty' => $vadcamera_warranty,
            'va_vadcamera_manufacture_year' => $vadcamera_manufacture_year,
            'va_vadcamera_photo_resolution' => $vadcamera_photo_resolution,
            'va_vadcamera_video_resolution' => $vadcamera_video_resolution,
            'va_vadcamera_video_fps' => $vadcamera_video_fps,
            'va_vadcamera_video_audio' => $vadcamera_video_audio,
            'va_vadcamera_ir_type' => $vadcamera_ir_type,
            'va_vadcamera_night_range_m' => $vadcamera_night_range_m,
            'va_vadcamera_ir_led_count' => $vadcamera_ir_led_count,
            'va_vadcamera_trigger_speed' => $vadcamera_trigger_speed,
            'va_vadcamera_pir_distance_m' => $vadcamera_pir_distance_m,
            'va_vadcamera_view_angle_deg' => $vadcamera_view_angle_deg,
            'va_vadcamera_connectivity' => $vadcamera_connectivity,
            'va_vadcamera_sim_slot' => $vadcamera_sim_slot,
            'va_vadcamera_mobile_app' => $vadcamera_mobile_app,
            'va_vadcamera_sd_max_gb' => $vadcamera_sd_max_gb,
            'va_vadcamera_internal_memory' => $vadcamera_internal_memory,
            'va_vadcamera_power_types' => $vadcamera_power_types,
            'va_vadcamera_runtime' => $vadcamera_runtime,
            'va_vadcamera_ip_rating' => $vadcamera_ip_rating,
            'va_vadcamera_temp_range' => $vadcamera_temp_range,
            'va_vadcamera_accessories' => $vadcamera_accessories,
            'va_vadcamera_use_cases' => $vadcamera_use_cases,
            'va_vadcamera_audio_record' => $vadcamera_audio_record,
            'va_vadcamera_public_area_use' => $vadcamera_public_area_use,
            'va_vadcamera_mounting' => $vadcamera_mounting,
            'va_vadcamera_shipping_methods' => $vadcamera_shipping_methods,
            'va_vadcamera_media_required' => $vadcamera_media_required,
            'va_vadcamera_sample_media' => $vadcamera_sample_media,
            'va_vadcamera_4g_carrier' => $vadcamera_4g_carrier,
            'va_vadcamera_4g_app_support' => $vadcamera_4g_app_support,
            'va_vadcamera_solar_panel_power' => $vadcamera_solar_panel_power,
            'va_vadcamera_notes' => $vadcamera_notes,
            'va_vadcamera_filter_connectivity_4g' => $vadcamera_filter_connectivity_4g,
            'va_vadcamera_filter_connectivity_wifi' => $vadcamera_filter_connectivity_wifi,
            'va_vadcamera_filter_trigger_speed' => $vadcamera_trigger_speed,
            'va_vadcamera_filter_ir_type' => $vadcamera_ir_type,
            'va_vadcamera_filter_night_range_m' => $vadcamera_night_range_m,
            'va_vadcamera_filter_video_resolution' => $vadcamera_video_resolution,
            'va_vadcamera_filter_ip_rating' => $vadcamera_ip_rating,
            'va_vadcamera_filter_is_solar' => $vadcamera_filter_is_solar,
            'va_vadcamera_filter_is_sim' => $vadcamera_filter_is_sim,
            'va_vadcamera_needs_review' => ! empty( $vadcamera_moderation_hits ) ? '1' : '0',
            'va_vadcamera_review_hits' => implode( ',', $vadcamera_moderation_hits ),
            'va_shoe_needs_review' => ! empty( $shoe_moderation_hits ) ? '1' : '0',
            'va_shoe_review_hits' => implode( ',', $shoe_moderation_hits ),
            'va_estate_main_type' => $estate_main_type,
            'va_estate_sale_mode' => $estate_sale_mode,
            'va_estate_price_total' => $estate_price_total,
            'va_estate_price_guide' => $estate_price_guide,
            'va_estate_price_per_item' => $estate_price_per_item,
            'va_estate_estimated_value' => $estate_estimated_value,
            'va_estate_negotiable' => $estate_negotiable,
            'va_estate_firearms_count' => $estate_firearms_count,
            'va_estate_firearms_type' => $estate_firearms_type,
            'va_estate_firearms_deactivated' => $estate_firearms_deactivated,
            'va_estate_firearms_licensed' => $estate_firearms_licensed,
            'va_estate_optics_count' => $estate_optics_count,
            'va_estate_clothing_count' => $estate_clothing_count,
            'va_estate_trophy_count' => $estate_trophy_count,
            'va_estate_docs_count' => $estate_docs_count,
            'va_estate_equipment_count' => $estate_equipment_count,
            'va_estate_condition' => $estate_condition,
            'va_estate_origin' => $estate_origin,
            'va_estate_legal_has_licensed_items' => $estate_legal_has_licensed_items,
            'va_estate_legal_deactivated' => $estate_legal_deactivated,
            'va_estate_legal_has_papers' => $estate_legal_has_papers,
            'va_estate_legal_bundle_only' => $estate_legal_bundle_only,
            'va_estate_transfer_methods' => $estate_transfer_methods,
            'va_estate_optic_items' => implode( ',', $estate_optic_items ),
            'va_estate_clothing_items' => implode( ',', $estate_clothing_items ),
            'va_estate_trophy_items' => implode( ',', $estate_trophy_items ),
            'va_estate_docs_items' => implode( ',', $estate_docs_items ),
            'va_estate_equipment_items' => implode( ',', $estate_equipment_items ),
            'va_estate_media_items' => implode( ',', $estate_media_items ),
            'va_estate_media_full' => $estate_media_full,
            'va_estate_media_details' => $estate_media_details,
            'va_estate_media_documents' => $estate_media_documents,
            'va_estate_media_firearms' => $estate_media_firearms,
            'va_estate_optic_celtavcso' => $estate_optic_celtavcso,
            'va_estate_optic_binokular' => $estate_optic_binokular,
            'va_estate_optic_spektiv' => $estate_optic_spektiv,
            'va_estate_optic_reddot' => $estate_optic_reddot,
            'va_estate_clothing_kabat' => $estate_clothing_kabat,
            'va_estate_clothing_nadrag' => $estate_clothing_nadrag,
            'va_estate_clothing_csizma' => $estate_clothing_csizma,
            'va_estate_clothing_esoruha' => $estate_clothing_esoruha,
            'va_estate_trophy_agancs' => $estate_trophy_agancs,
            'va_estate_trophy_koponya' => $estate_trophy_koponya,
            'va_estate_trophy_preparatum' => $estate_trophy_preparatum,
            'va_estate_docs_engedelyek' => $estate_docs_engedelyek,
            'va_estate_docs_vadasznaplo' => $estate_docs_vadasznaplo,
            'va_estate_docs_konyvek' => $estate_docs_konyvek,
            'va_estate_docs_tanusitvanyok' => $estate_docs_tanusitvanyok,
            'va_estate_equipment_kes' => $estate_equipment_kes,
            'va_estate_equipment_hatizsak' => $estate_equipment_hatizsak,
            'va_estate_equipment_lampa' => $estate_equipment_lampa,
            'va_estate_equipment_tavcso_tartozek' => $estate_equipment_tavcso_tartozek,
            'va_estate_equipment_fegyverszef' => $estate_equipment_fegyverszef,
            'va_estate_notes' => $estate_notes,
            'va_estate_filter_has_firearms' => $estate_filter_has_firearms,
            'va_estate_filter_has_optics' => $estate_filter_has_optics,
            'va_estate_filter_scope' => $estate_filter_scope,
            'va_estate_needs_review' => ! empty( $estate_moderation_hits ) ? '1' : '0',
            'va_estate_review_hits' => implode( ',', $estate_moderation_hits ),
            'va_loszer_category' => $loszer_category,
            'va_loszer_caliber_preset' => $loszer_caliber_preset,
            'va_loszer_caliber_custom' => $loszer_caliber_custom,
            'va_loszer_manufacturer_preset' => $loszer_manufacturer_preset,
            'va_loszer_manufacturer_custom' => $loszer_manufacturer_custom,
            'va_loszer_projectile_type' => $loszer_projectile_type,
            'va_loszer_projectile_weight' => $loszer_projectile_weight,
            'va_loszer_projectile_weight_unit' => $loszer_projectile_weight_unit,
            'va_loszer_piece_count' => $loszer_piece_count,
            'va_loszer_piece_unit' => $loszer_piece_unit,
            'va_loszer_lot_year' => $loszer_lot_year,
            'va_loszer_condition' => $loszer_condition,
            'va_loszer_component_case_condition' => $loszer_component_case_condition,
            'va_loszer_powder_type' => $loszer_powder_type,
            'va_loszer_powder_sealed' => $loszer_powder_sealed,
            'va_loszer_powder_weight' => $loszer_powder_weight,
            'va_loszer_primer_type' => $loszer_primer_type,
            'va_bow_type' => $bow_type,
            'va_bow_handedness' => $bow_handedness,
            'va_bow_draw_weight' => $bow_draw_weight,
            'va_bow_draw_length' => $bow_draw_length,
            'va_bow_axle_length' => $bow_axle_length,
            'va_bow_item_condition' => $bow_item_condition,
            'va_bow_condition_notes' => $bow_condition_notes,
            'va_bow_string_replaced' => $bow_string_replaced,
            'va_bow_owned_since' => $bow_owned_since,
            'va_bow_usage' => $bow_usage,
            'va_bow_accessories' => $bow_accessories,
            'va_bow_cams_cables' => $bow_cams_cables,
            'va_bow_speed_fps' => $bow_speed_fps,
            'va_bow_serial' => $bow_serial,
            'va_bow_material' => $bow_material,
            'va_other_weapon_kind' => $other_weapon_kind,
            'va_other_weapon_general_notes' => $other_weapon_general_notes,
            'va_other_weapon_accessories' => $other_weapon_accessories,
            'va_other_weapon_accessories_other' => $other_weapon_accessories_other,
            'va_other_weapon_18plus' => $other_weapon_18plus,
            'va_other_weapon_permit_required' => $other_weapon_permit_required,
            'va_other_weapon_personal_pickup' => $other_weapon_personal_pickup,
            'va_other_weapon_origin_papers' => $other_weapon_origin_papers,
            'va_other_weapon_serial_number' => $other_weapon_serial_number,
            'va_other_weapon_bow_draw_weight_lbs' => $other_weapon_bow_draw_weight_lbs,
            'va_other_weapon_bow_axle_to_axle' => $other_weapon_bow_axle_to_axle,
            'va_other_weapon_bow_handness' => $other_weapon_bow_handness,
            'va_other_weapon_bow_style' => $other_weapon_bow_style,
            'va_other_weapon_bow_arrow_rest' => $other_weapon_bow_arrow_rest,
            'va_other_weapon_bow_sight' => $other_weapon_bow_sight,
            'va_other_weapon_crossbow_draw_weight_lbs' => $other_weapon_crossbow_draw_weight_lbs,
            'va_other_weapon_crossbow_fps' => $other_weapon_crossbow_fps,
            'va_other_weapon_crossbow_accessories' => $other_weapon_crossbow_accessories,
            'va_other_weapon_airsoft_operation' => $other_weapon_airsoft_operation,
            'va_other_weapon_airsoft_energy_joule' => $other_weapon_airsoft_energy_joule,
            'va_other_weapon_airsoft_fps' => $other_weapon_airsoft_fps,
            'va_other_weapon_airsoft_magazine_type' => $other_weapon_airsoft_magazine_type,
            'va_other_weapon_airgun_caliber' => $other_weapon_airgun_caliber,
            'va_other_weapon_airgun_muzzle_energy' => $other_weapon_airgun_muzzle_energy,
            'va_other_weapon_airgun_power_source' => $other_weapon_airgun_power_source,
            'va_other_weapon_gas_alarm_caliber' => $other_weapon_gas_alarm_caliber,
            'va_other_weapon_gas_alarm_self_defense_license' => $other_weapon_gas_alarm_self_defense_license,
            'va_other_weapon_gas_alarm_carry_license' => $other_weapon_gas_alarm_carry_license,
            'va_other_weapon_knife_blade_length' => $other_weapon_knife_blade_length,
            'va_other_weapon_knife_steel_type' => $other_weapon_knife_steel_type,
            'va_other_weapon_knife_handle_material' => $other_weapon_knife_handle_material,
            'va_other_weapon_knife_sheath' => $other_weapon_knife_sheath,
            'va_other_weapon_knife_accessories' => $other_weapon_knife_accessories,
            'va_hatastalanitott_weapon_type' => $hatastalanitott_weapon_type,
            'va_hatastalanitott_origin_country' => $hatastalanitott_origin_country,
            'va_hatastalanitott_is_deactivated' => $hatastalanitott_is_deactivated,
            'va_hatastalanitott_deactivation_type' => $hatastalanitott_deactivation_type,
            'va_hatastalanitott_certificate' => $hatastalanitott_certificate,
            'va_hatastalanitott_mkh_cip' => $hatastalanitott_mkh_cip,
            'va_hatastalanitott_deactivation_year' => $hatastalanitott_deactivation_year,
            'va_hatastalanitott_deactivation_org' => $hatastalanitott_deactivation_org,
            'va_hatastalanitott_condition' => $hatastalanitott_condition,
            'va_hatastalanitott_serial_admin' => $hatastalanitott_serial_admin,
            'va_hatastalanitott_working_parts' => $hatastalanitott_working_parts,
            'va_hatastalanitott_original_finish' => $hatastalanitott_original_finish,
            'va_hatastalanitott_original_parts_ratio' => $hatastalanitott_original_parts_ratio,
            'va_hatastalanitott_accessories' => $hatastalanitott_accessories,
            'va_bow_acc_sight' => $bow_acc_sight,
            'va_bow_acc_release' => $bow_acc_release,
            'va_bow_acc_stabilizer' => $bow_acc_stabilizer,
            'va_bow_acc_arrows' => $bow_acc_arrows,
            'va_bow_acc_case' => $bow_acc_case,
            'va_bow_acc_quiver' => $bow_acc_quiver,
            'va_bow_acc_target' => $bow_acc_target,
            'va_feed_type' => $feed_type,
            'va_feed_recommended_game' => $feed_recommended_game,
            'va_feed_game_boar' => $feed_game_boar,
            'va_feed_game_roe' => $feed_game_roe,
            'va_feed_game_red_deer' => $feed_game_red_deer,
            'va_feed_game_pheasant' => $feed_game_pheasant,
            'va_feed_game_other' => $feed_game_other,
            'va_feed_game_other_text' => $feed_game_other_text,
            'va_feed_ingredients' => $feed_ingredients,
            'va_feed_moisture' => $feed_moisture,
            'va_feed_has_additives' => $feed_has_additives,
            'va_feed_packaging_type' => $feed_packaging_type,
            'va_feed_bag_weight' => $feed_bag_weight,
            'va_feed_total_quantity' => $feed_total_quantity,
            'va_feed_pallet_quantity' => $feed_pallet_quantity,
            'va_feed_harvest_freshness' => $feed_harvest_freshness,
            'va_feed_stored_dry' => $feed_stored_dry,
            'va_feed_mold_free' => $feed_mold_free,
            'va_feed_bags_undamaged' => $feed_bags_undamaged,
            'va_feed_price_per_unit' => $feed_price_per_unit,
            'va_feed_min_order' => $feed_min_order,
            'va_feed_delivery_available' => $feed_delivery_available,
            'va_feed_loading_available' => $feed_loading_available,
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

        self::learn_listing_terms( $category, [
            'title'    => $title,
            'brand'    => $brand,
            'model'    => $model,
            'caliber'  => $caliber,
            'location' => $location,
            'street'   => $street,
        ] );

        // Megtartandó meglévő képek
        $keep_raw = sanitize_text_field( wp_unslash( $_POST['keep_images'] ?? '' ) );
        $keep_ids = array_filter( array_map( 'absint', explode( ',', $keep_raw ) ) );

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
        $postal_code = sanitize_text_field( wp_unslash( $_POST['postal_code'] ?? '' ) );
        $street      = sanitize_text_field( wp_unslash( $_POST['street'] ?? '' ) );
        $postal_code = preg_replace( '/\D+/', '', $postal_code );
        $other_category = sanitize_text_field( wp_unslash( $_POST['other_category'] ?? '' ) );
        $brand       = sanitize_text_field( wp_unslash( $_POST['brand']  ?? '' ) );
        $model       = sanitize_text_field( wp_unslash( $_POST['model']  ?? '' ) );
        $caliber     = sanitize_text_field( wp_unslash( $_POST['caliber'] ?? '' ) );
        $year        = intval( $_POST['year'] ?? 0 );
        $license_req = ! empty( $_POST['license_req'] ) ? '1' : '0';
        $optic_type  = sanitize_key( wp_unslash( $_POST['optic_type'] ?? '' ) );
        $optic_zoom  = sanitize_text_field( wp_unslash( $_POST['optic_zoom'] ?? '' ) );
        $optic_objective = sanitize_text_field( wp_unslash( $_POST['optic_objective'] ?? '' ) );
        $thermal_device_type = sanitize_key( wp_unslash( $_POST['thermal_device_type'] ?? '' ) );
        $thermal_condition = sanitize_key( wp_unslash( $_POST['thermal_condition'] ?? '' ) );
        $thermal_warranty = sanitize_key( wp_unslash( $_POST['thermal_warranty'] ?? '' ) );
        $thermal_manufacture_year = sanitize_text_field( wp_unslash( $_POST['thermal_manufacture_year'] ?? '' ) );
        $thermal_sensor_resolution = sanitize_key( wp_unslash( $_POST['thermal_sensor_resolution'] ?? '' ) );
        $thermal_pixel_pitch = sanitize_key( wp_unslash( $_POST['thermal_pixel_pitch'] ?? '' ) );
        $thermal_netd = sanitize_key( wp_unslash( $_POST['thermal_netd'] ?? '' ) );
        $thermal_refresh_hz = sanitize_key( wp_unslash( $_POST['thermal_refresh_hz'] ?? '' ) );
        $thermal_base_magnification = sanitize_text_field( wp_unslash( $_POST['thermal_base_magnification'] ?? '' ) );
        $thermal_digital_zoom = sanitize_key( wp_unslash( $_POST['thermal_digital_zoom'] ?? '' ) );
        $thermal_lens_size = sanitize_key( wp_unslash( $_POST['thermal_lens_size'] ?? '' ) );
        $thermal_fov_degree = sanitize_text_field( wp_unslash( $_POST['thermal_fov_degree'] ?? '' ) );
        $thermal_fov_m100 = sanitize_text_field( wp_unslash( $_POST['thermal_fov_m100'] ?? '' ) );
        $thermal_detect_human_m = sanitize_text_field( wp_unslash( $_POST['thermal_detect_human_m'] ?? '' ) );
        $thermal_detect_game_m = sanitize_text_field( wp_unslash( $_POST['thermal_detect_game_m'] ?? '' ) );
        $thermal_detect_vehicle_m = sanitize_text_field( wp_unslash( $_POST['thermal_detect_vehicle_m'] ?? '' ) );
        $thermal_display_type = sanitize_key( wp_unslash( $_POST['thermal_display_type'] ?? '' ) );
        $thermal_display_resolution = sanitize_text_field( wp_unslash( $_POST['thermal_display_resolution'] ?? '' ) );
        $thermal_palettes = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_palettes'] ?? [] ) ) ) );
        $thermal_photo = sanitize_key( wp_unslash( $_POST['thermal_photo'] ?? '' ) );
        $thermal_video = sanitize_key( wp_unslash( $_POST['thermal_video'] ?? '' ) );
        $thermal_audio = sanitize_key( wp_unslash( $_POST['thermal_audio'] ?? '' ) );
        $thermal_internal_memory_gb = sanitize_text_field( wp_unslash( $_POST['thermal_internal_memory_gb'] ?? '' ) );
        $thermal_sd_support = sanitize_key( wp_unslash( $_POST['thermal_sd_support'] ?? '' ) );
        $thermal_connectivity = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_connectivity'] ?? [] ) ) ) );
        $thermal_mobile_app = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_mobile_app'] ?? [] ) ) ) );
        $thermal_extra_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_extra_features'] ?? [] ) ) ) );
        $thermal_battery_type = sanitize_key( wp_unslash( $_POST['thermal_battery_type'] ?? '' ) );
        $thermal_runtime_hours = sanitize_text_field( wp_unslash( $_POST['thermal_runtime_hours'] ?? '' ) );
        $thermal_powerbank_compatible = sanitize_key( wp_unslash( $_POST['thermal_powerbank_compatible'] ?? '' ) );
        $thermal_ip_rating = sanitize_key( wp_unslash( $_POST['thermal_ip_rating'] ?? '' ) );
        $thermal_recoil_resistance = sanitize_key( wp_unslash( $_POST['thermal_recoil_resistance'] ?? '' ) );
        $thermal_weapon_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_weapon_compatibility'] ?? [] ) ) ) );
        $thermal_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['thermal_accessories'] ?? [] ) ) ) );
        $dog_age_months = sanitize_text_field( wp_unslash( $_POST['dog_age_months'] ?? '' ) );
        $dog_working_type = sanitize_key( wp_unslash( $_POST['dog_working_type'] ?? '' ) );
        $dog_breed  = sanitize_text_field( wp_unslash( $_POST['dog_breed'] ?? '' ) );
        $dog_name = sanitize_text_field( wp_unslash( $_POST['dog_name'] ?? '' ) );
        $dog_gender = sanitize_key( wp_unslash( $_POST['dog_gender'] ?? '' ) );
        $dog_breeder_name = sanitize_text_field( wp_unslash( $_POST['dog_breeder_name'] ?? '' ) );
        $dog_kennel_name = sanitize_text_field( wp_unslash( $_POST['dog_kennel_name'] ?? '' ) );
        $dog_pedigree_status = sanitize_key( wp_unslash( $_POST['dog_pedigree_status'] ?? '' ) );
        $dog_origin_country = sanitize_text_field( wp_unslash( $_POST['dog_origin_country'] ?? '' ) );
        $dog_bloodline_type = sanitize_key( wp_unslash( $_POST['dog_bloodline_type'] ?? '' ) );
        $dog_documents = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_documents'] ?? [] ) ) ) );
        $dog_birth_date = sanitize_text_field( wp_unslash( $_POST['dog_birth_date'] ?? '' ) );
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dog_birth_date ) ) {
            $dog_birth_date = '';
        }
        $dog_color  = sanitize_text_field( wp_unslash( $_POST['dog_color'] ?? '' ) );
        $dog_appearance_type = sanitize_key( wp_unslash( $_POST['dog_appearance_type'] ?? '' ) );
        $dog_purebred = sanitize_key( wp_unslash( $_POST['dog_purebred'] ?? '' ) );
        $dog_training_skills = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_training_skills'] ?? [] ) ) ) );
        $dog_training_level = sanitize_key( wp_unslash( $_POST['dog_training_level'] ?? '' ) );
        $dog_exam_level = sanitize_key( wp_unslash( $_POST['dog_exam_level'] ?? '' ) );
        $dog_work_exam_type = sanitize_key( wp_unslash( $_POST['dog_work_exam_type'] ?? '' ) );
        $dog_hunting_specialization = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_hunting_specialization'] ?? [] ) ) ) );
        $dog_work_style = sanitize_key( wp_unslash( $_POST['dog_work_style'] ?? '' ) );
        $dog_search_radius_m = sanitize_text_field( wp_unslash( $_POST['dog_search_radius_m'] ?? '' ) );
        $dog_endurance_level = sanitize_key( wp_unslash( $_POST['dog_endurance_level'] ?? '' ) );
        $dog_aggression_level = sanitize_key( wp_unslash( $_POST['dog_aggression_level'] ?? '' ) );
        $dog_apport_stability = sanitize_key( wp_unslash( $_POST['dog_apport_stability'] ?? '' ) );
        $dog_water_work_ability = sanitize_key( wp_unslash( $_POST['dog_water_work_ability'] ?? '' ) );
        $dog_water_work = sanitize_key( wp_unslash( $_POST['dog_water_work'] ?? '' ) );
        $dog_water_work_level = sanitize_key( wp_unslash( $_POST['dog_water_work_level'] ?? '' ) );
        $dog_cold_water_tolerance = sanitize_key( wp_unslash( $_POST['dog_cold_water_tolerance'] ?? '' ) );
        $dog_temperament = sanitize_key( wp_unslash( $_POST['dog_temperament'] ?? '' ) );
        $dog_environment = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_environment'] ?? [] ) ) ) );
        $dog_allergic = sanitize_key( wp_unslash( $_POST['dog_allergic'] ?? '' ) );
        $dog_special_diet = sanitize_text_field( wp_unslash( $_POST['dog_special_diet'] ?? '' ) );
        $dog_known_diseases = sanitize_textarea_field( wp_unslash( $_POST['dog_known_diseases'] ?? '' ) );
        $dog_hip_dysplasia_screening = sanitize_key( wp_unslash( $_POST['dog_hip_dysplasia_screening'] ?? '' ) );
        $dog_height_cm = sanitize_text_field( wp_unslash( $_POST['dog_height_cm'] ?? '' ) );
        $dog_weight_kg = sanitize_text_field( wp_unslash( $_POST['dog_weight_kg'] ?? '' ) );
        $dog_country = sanitize_text_field( wp_unslash( $_POST['dog_country'] ?? '' ) );
        $dog_county = sanitize_text_field( wp_unslash( $_POST['dog_county'] ?? '' ) );
        $dog_city = sanitize_text_field( wp_unslash( $_POST['dog_city'] ?? '' ) );
        $dog_origin_qualification = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_origin_qualification'] ?? [] ) ) ) );
        $dog_litter_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['dog_litter_count'] ?? '' ) );
        $dog_available_from = sanitize_text_field( wp_unslash( $_POST['dog_available_from'] ?? '' ) );
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dog_available_from ) ) {
            $dog_available_from = '';
        }
        $dog_tracking_distance_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['dog_tracking_distance_m'] ?? '' ) );
        $dog_tracking_experience = sanitize_text_field( wp_unslash( $_POST['dog_tracking_experience'] ?? '' ) );
        $dog_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_moderation_flags'] ?? [] ) ) ) );
        $dog_work_video_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['dog_work_video_types'] ?? [] ) ) ) );
        $dog_work_video_url = esc_url_raw( wp_unslash( $_POST['dog_work_video_url'] ?? '' ) );
        if ( $dog_birth_date !== '' ) {
            try {
                $dog_birth = new DateTimeImmutable( $dog_birth_date );
                $dog_today = new DateTimeImmutable( current_time( 'Y-m-d' ) );
                if ( $dog_birth <= $dog_today ) {
                    $dog_diff = $dog_birth->diff( $dog_today );
                    $dog_age_months = (string) ( ( $dog_diff->y * 12 ) + $dog_diff->m );
                }
            } catch ( Exception $e ) {
            }
        }
        $dog_review_hits = array_values( array_filter( array_map( 'trim', explode( ',', $dog_moderation_flags ) ) ) );
        $dog_filter_pedigree = $dog_pedigree_status === 'igen' ? '1' : '0';
        $dog_filter_has_work_exam = ( $dog_work_exam_type !== '' || strpos( ',' . $dog_documents . ',', ',munkavizsga-papir,' ) !== false ) ? '1' : '0';
        $dog_filter_water_work = ( $dog_water_work === 'igen' || in_array( $dog_water_work_level, [ 'eros', 'kozepes' ], true ) ) ? '1' : '0';
        $job_location = sanitize_text_field( wp_unslash( $_POST['job_location'] ?? '' ) );
        $job_type   = sanitize_text_field( wp_unslash( $_POST['job_type'] ?? '' ) );
        $service_type = sanitize_key( wp_unslash( $_POST['service_type'] ?? '' ) );
        $service_provider_type = sanitize_key( wp_unslash( $_POST['service_provider_type'] ?? '' ) );
        $service_provider_name = sanitize_text_field( wp_unslash( $_POST['service_provider_name'] ?? '' ) );
        $service_country = sanitize_text_field( wp_unslash( $_POST['service_country'] ?? '' ) );
        $service_county = absint( wp_unslash( $_POST['service_county'] ?? 0 ) );
        $service_town = sanitize_text_field( wp_unslash( $_POST['service_town'] ?? '' ) );
        $service_area = sanitize_key( wp_unslash( $_POST['service_area'] ?? '' ) );
        $service_gps_lat = sanitize_text_field( wp_unslash( $_POST['service_gps_lat'] ?? '' ) );
        $service_gps_lng = sanitize_text_field( wp_unslash( $_POST['service_gps_lng'] ?? '' ) );
        $service_pricing_type = sanitize_key( wp_unslash( $_POST['service_pricing_type'] ?? '' ) );
        $service_min_price = sanitize_text_field( wp_unslash( $_POST['service_min_price'] ?? '' ) );
        $service_travel_fee = sanitize_text_field( wp_unslash( $_POST['service_travel_fee'] ?? '' ) );
        $service_schedule = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_schedule'] ?? [] ) ) ) );
        $service_available_weekend = sanitize_key( wp_unslash( $_POST['service_available_weekend'] ?? '' ) );
        $service_available_24_7 = sanitize_key( wp_unslash( $_POST['service_available_24_7'] ?? '' ) );
        $service_seasonal = sanitize_key( wp_unslash( $_POST['service_seasonal'] ?? '' ) );
        $service_hunting_specialization = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_hunting_specialization'] ?? [] ) ) ) );
        $service_hunting_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_hunting_species'] ?? [] ) ) ) );
        $service_weapon_categories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_weapon_categories'] ?? [] ) ) ) );
        $service_equipment = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_equipment'] ?? [] ) ) ) );
        $service_permits = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_permits'] ?? [] ) ) ) );
        $service_reference_rating = sanitize_text_field( wp_unslash( $_POST['service_reference_rating'] ?? '' ) );
        $service_completed_jobs = sanitize_text_field( wp_unslash( $_POST['service_completed_jobs'] ?? '' ) );
        $service_reference_notes = sanitize_textarea_field( wp_unslash( $_POST['service_reference_notes'] ?? '' ) );
        $service_media_required = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_media_required'] ?? [] ) ) ) );
        $service_media_video = sanitize_key( wp_unslash( $_POST['service_media_video'] ?? '' ) );
        $service_verified_provider = sanitize_key( wp_unslash( $_POST['service_verified_provider'] ?? '' ) );
        $service_document_verified = sanitize_key( wp_unslash( $_POST['service_document_verified'] ?? '' ) );
        $service_phone_verified = sanitize_key( wp_unslash( $_POST['service_phone_verified'] ?? '' ) );
        $service_business_verified = sanitize_key( wp_unslash( $_POST['service_business_verified'] ?? '' ) );
        $service_sos_service = sanitize_key( wp_unslash( $_POST['service_sos_service'] ?? '' ) );
        $service_urgent_repair = sanitize_key( wp_unslash( $_POST['service_urgent_repair'] ?? '' ) );
        $service_full_prep = sanitize_key( wp_unslash( $_POST['service_full_prep'] ?? '' ) );
        $service_night_work = sanitize_key( wp_unslash( $_POST['service_night_work'] ?? '' ) );
        $service_hound_breed = sanitize_text_field( wp_unslash( $_POST['service_hound_breed'] ?? '' ) );
        $service_caliber_support = sanitize_text_field( wp_unslash( $_POST['service_caliber_support'] ?? '' ) );
        $service_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['service_moderation_flags'] ?? [] ) ) ) );
        $clothing_type = sanitize_key( wp_unslash( $_POST['clothing_type'] ?? '' ) );
        $clothing_condition = sanitize_key( wp_unslash( $_POST['clothing_condition'] ?? '' ) );
        $clothing_gender = sanitize_key( wp_unslash( $_POST['clothing_gender'] ?? '' ) );
        $clothing_size_system = sanitize_key( wp_unslash( $_POST['clothing_size_system'] ?? '' ) );
        $clothing_size = sanitize_text_field( wp_unslash( $_POST['clothing_size'] ?? '' ) );
        $clothing_accessory_size = sanitize_text_field( wp_unslash( $_POST['clothing_accessory_size'] ?? '' ) );
        $clothing_glove_size = sanitize_text_field( wp_unslash( $_POST['clothing_glove_size'] ?? '' ) );
        $clothing_hat_size = sanitize_key( wp_unslash( $_POST['clothing_hat_size'] ?? '' ) );
        $clothing_hat_cm = sanitize_text_field( wp_unslash( $_POST['clothing_hat_cm'] ?? '' ) );
        $clothing_belt_length_cm = sanitize_text_field( wp_unslash( $_POST['clothing_belt_length_cm'] ?? '' ) );
        $clothing_colors = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_colors'] ?? [] ) ) ) );
        $clothing_materials = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_materials'] ?? [] ) ) ) );
        $clothing_protections = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_protections'] ?? [] ) ) ) );
        $clothing_season = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_season'] ?? [] ) ) ) );
        $clothing_use_cases = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_use_cases'] ?? [] ) ) ) );
        $clothing_special_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_special_features'] ?? [] ) ) ) );
        $clothing_glove_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_glove_features'] ?? [] ) ) ) );
        $clothing_headwear_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_headwear_features'] ?? [] ) ) ) );
        $clothing_poncho_water_column = sanitize_text_field( wp_unslash( $_POST['clothing_poncho_water_column'] ?? '' ) );
        $clothing_poncho_ultralight = sanitize_key( wp_unslash( $_POST['clothing_poncho_ultralight'] ?? '' ) );
        $clothing_poncho_backpack_compatible = sanitize_key( wp_unslash( $_POST['clothing_poncho_backpack_compatible'] ?? '' ) );
        $clothing_ghillie_type = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_ghillie_type'] ?? [] ) ) ) );
        $clothing_ghillie_pattern = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_ghillie_pattern'] ?? [] ) ) ) );
        $clothing_filter_thermo = ( strpos( ',' . $clothing_protections . ',', ',thermo,' ) !== false ) ? '1' : '0';
        $clothing_filter_waterproof = ( strpos( ',' . $clothing_protections . ',', ',vizallo,' ) !== false ) ? '1' : '0';
        $clothing_filter_camo = ( strpos( ',' . $clothing_colors . ',', ',terepmintas,' ) !== false || strpos( ',' . $clothing_colors . ',', ',realtree,' ) !== false || strpos( ',' . $clothing_colors . ',', ',multicam,' ) !== false ) ? '1' : '0';
        $clothing_filter_silent = ( strpos( ',' . $clothing_special_features . ',', ',hangtalan,' ) !== false ) ? '1' : '0';
        $season = sanitize_key( wp_unslash( $_POST['season'] ?? '' ) );
        $hunting_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunting_usage'] ?? [] ) ) ) );
        $noise_level = sanitize_key( wp_unslash( $_POST['noise_level'] ?? '' ) );
        $material_tech = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['material_tech'] ?? [] ) ) ) );
        $waterproof_level = sanitize_key( wp_unslash( $_POST['waterproof_level'] ?? '' ) );
        $camo_pattern = sanitize_key( wp_unslash( $_POST['camo_pattern'] ?? '' ) );
        $silence_index = sanitize_key( wp_unslash( $_POST['silence_index'] ?? '' ) );
        $clothing_weather_protection = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_weather_protection'] ?? [] ) ) ) );
        $clothing_functional_details = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_functional_details'] ?? [] ) ) ) );
        $clothing_fit = sanitize_key( wp_unslash( $_POST['clothing_fit'] ?? '' ) );
        $clothing_size_fit = sanitize_key( wp_unslash( $_POST['clothing_size_fit'] ?? '' ) );
        $clothing_hunter_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_hunter_features'] ?? [] ) ) ) );
        $clothing_gear_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['clothing_gear_compatibility'] ?? [] ) ) ) );
        $clothing_durability = sanitize_key( wp_unslash( $_POST['clothing_durability'] ?? '' ) );
        $clothing_layering = sanitize_key( wp_unslash( $_POST['clothing_layering'] ?? '' ) );
        $clothing_membrane_type = sanitize_key( wp_unslash( $_POST['clothing_membrane_type'] ?? '' ) );
        $clothing_ir_reduction = sanitize_key( wp_unslash( $_POST['clothing_ir_reduction'] ?? '' ) );
        $clothing_filter_hunting_usage = $hunting_usage;
        $clothing_filter_noise_level = $noise_level;
        $clothing_filter_material_tech = $material_tech;
        $clothing_filter_camo_pattern = $camo_pattern;
        $clothing_filter_season_mode = $season;
        $shoe_size   = sanitize_text_field( wp_unslash( $_POST['shoe_size'] ?? '' ) );
        $shoe_main_type = sanitize_key( wp_unslash( $_POST['shoe_main_type'] ?? '' ) );
        $shoe_condition = sanitize_key( wp_unslash( $_POST['shoe_condition'] ?? '' ) );
        $shoe_gender = sanitize_key( wp_unslash( $_POST['shoe_gender'] ?? '' ) );
        $shoe_eu_size = sanitize_text_field( wp_unslash( $_POST['shoe_eu_size'] ?? '' ) );
        $shoe_uk_size = sanitize_text_field( wp_unslash( $_POST['shoe_uk_size'] ?? '' ) );
        $shoe_us_size = sanitize_text_field( wp_unslash( $_POST['shoe_us_size'] ?? '' ) );
        $shoe_boot_height = sanitize_key( wp_unslash( $_POST['shoe_boot_height'] ?? '' ) );
        $shoe_outer_material = sanitize_key( wp_unslash( $_POST['shoe_outer_material'] ?? '' ) );
        $shoe_lining = sanitize_key( wp_unslash( $_POST['shoe_lining'] ?? '' ) );
        $shoe_sole_material = sanitize_key( wp_unslash( $_POST['shoe_sole_material'] ?? '' ) );
        $shoe_protections = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_protections'] ?? [] ) ) ) );
        $shoe_season = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_season'] ?? [] ) ) ) );
        $shoe_terrain = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_terrain'] ?? [] ) ) ) );
        $shoe_use_cases = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_use_cases'] ?? [] ) ) ) );
        $shoe_sole_pattern = sanitize_key( wp_unslash( $_POST['shoe_sole_pattern'] ?? '' ) );
        $shoe_stiffness = sanitize_key( wp_unslash( $_POST['shoe_stiffness'] ?? '' ) );
        $shoe_weight_single_g = sanitize_text_field( wp_unslash( $_POST['shoe_weight_single_g'] ?? '' ) );
        $shoe_weight_pair_g = sanitize_text_field( wp_unslash( $_POST['shoe_weight_pair_g'] ?? '' ) );
        $shoe_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_accessories'] ?? [] ) ) ) );
        $shoe_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_shipping_methods'] ?? [] ) ) ) );
        $shoe_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['shoe_required_media'] ?? [] ) ) ) );
        $shoe_rubber_shaft_cm = sanitize_text_field( wp_unslash( $_POST['shoe_rubber_shaft_cm'] ?? '' ) );
        $shoe_rubber_neoprene_lining = sanitize_key( wp_unslash( $_POST['shoe_rubber_neoprene_lining'] ?? '' ) );
        $shoe_tactical_steel_toe = sanitize_key( wp_unslash( $_POST['shoe_tactical_steel_toe'] ?? '' ) );
        $shoe_tactical_quick_lace = sanitize_key( wp_unslash( $_POST['shoe_tactical_quick_lace'] ?? '' ) );
        $shoe_winter_comfort_temp_c = sanitize_text_field( wp_unslash( $_POST['shoe_winter_comfort_temp_c'] ?? '' ) );
        $shoe_foot_type = sanitize_key( wp_unslash( $_POST['shoe_foot_type'] ?? '' ) );
        $shoe_sole_wear_percent = sanitize_text_field( wp_unslash( $_POST['shoe_sole_wear_percent'] ?? '' ) );
        $shoe_inner_wear = sanitize_text_field( wp_unslash( $_POST['shoe_inner_wear'] ?? '' ) );
        $shoe_waterproof_retained = sanitize_key( wp_unslash( $_POST['shoe_waterproof_retained'] ?? '' ) );
        $shoe_resoled = sanitize_key( wp_unslash( $_POST['shoe_resoled'] ?? '' ) );
        $shoe_notes = sanitize_textarea_field( wp_unslash( $_POST['shoe_notes'] ?? '' ) );
        if ( $shoe_eu_size === '' ) {
            $shoe_eu_size = $shoe_size;
        }
        if ( $shoe_size === '' ) {
            $shoe_size = $shoe_eu_size;
        }
        $shoe_filter_waterproof = ( strpos( ',' . $shoe_protections . ',', ',vizallo,' ) !== false ) ? '1' : '0';
        $shoe_filter_goretex = ( $shoe_lining === 'gore-tex' ) ? '1' : '0';
        $shoe_filter_winter = ( strpos( ',' . $shoe_season . ',', ',teli,' ) !== false || $shoe_main_type === 'teli-csizma' || $shoe_main_type === 'hotaposo' ) ? '1' : '0';
        $shoe_filter_high_ankle = ( $shoe_boot_height === 'magas' ) ? '1' : '0';
        $knife_type  = sanitize_key( wp_unslash( $_POST['knife_type'] ?? '' ) );
        $knife_blade_length = sanitize_text_field( wp_unslash( $_POST['knife_blade_length'] ?? '' ) );
        $knife_condition = sanitize_key( wp_unslash( $_POST['knife_condition'] ?? '' ) );
        $knife_manufacture_year = sanitize_text_field( wp_unslash( $_POST['knife_manufacture_year'] ?? '' ) );
        $knife_total_length_cm = sanitize_text_field( wp_unslash( $_POST['knife_total_length_cm'] ?? '' ) );
        $knife_blade_length_mm = sanitize_text_field( wp_unslash( $_POST['knife_blade_length_mm'] ?? '' ) );
        $knife_blade_thickness_mm = sanitize_text_field( wp_unslash( $_POST['knife_blade_thickness_mm'] ?? '' ) );
        $knife_weight_g = sanitize_text_field( wp_unslash( $_POST['knife_weight_g'] ?? '' ) );
        $knife_steel_type = sanitize_key( wp_unslash( $_POST['knife_steel_type'] ?? '' ) );
        $knife_blade_shape = sanitize_key( wp_unslash( $_POST['knife_blade_shape'] ?? '' ) );
        $knife_blade_edge = sanitize_key( wp_unslash( $_POST['knife_blade_edge'] ?? '' ) );
        $knife_finish = sanitize_key( wp_unslash( $_POST['knife_finish'] ?? '' ) );
        $knife_construction = sanitize_key( wp_unslash( $_POST['knife_construction'] ?? '' ) );
        $knife_locking_type = sanitize_key( wp_unslash( $_POST['knife_locking_type'] ?? '' ) );
        $knife_one_hand_opening = sanitize_key( wp_unslash( $_POST['knife_one_hand_opening'] ?? '' ) );
        $knife_handle_material = sanitize_key( wp_unslash( $_POST['knife_handle_material'] ?? '' ) );
        $knife_handle_color = sanitize_text_field( wp_unslash( $_POST['knife_handle_color'] ?? '' ) );
        $knife_has_sheath = sanitize_key( wp_unslash( $_POST['knife_has_sheath'] ?? '' ) );
        $knife_sheath_type = sanitize_key( wp_unslash( $_POST['knife_sheath_type'] ?? '' ) );
        $knife_belt_carry = sanitize_key( wp_unslash( $_POST['knife_belt_carry'] ?? '' ) );
        $knife_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_usage'] ?? [] ) ) ) );
        $knife_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_features'] ?? [] ) ) ) );
        $knife_age_restriction = sanitize_key( wp_unslash( $_POST['knife_age_restriction'] ?? '' ) );
        $knife_restricted = sanitize_key( wp_unslash( $_POST['knife_restricted'] ?? '' ) );
        $knife_public_carry = sanitize_key( wp_unslash( $_POST['knife_public_carry'] ?? '' ) );
        $knife_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_accessories'] ?? [] ) ) ) );
        $knife_price_deal = sanitize_key( wp_unslash( $_POST['knife_price_deal'] ?? '' ) );
        $knife_price_exchange = sanitize_key( wp_unslash( $_POST['knife_price_exchange'] ?? '' ) );
        $knife_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_shipping_methods'] ?? [] ) ) ) );
        $knife_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_required_media'] ?? [] ) ) ) );
        $knife_sharpening_state = sanitize_key( wp_unslash( $_POST['knife_sharpening_state'] ?? '' ) );
        $knife_collectible_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_collectible_flags'] ?? [] ) ) ) );
        $knife_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['knife_moderation_flags'] ?? [] ) ) ) );
        $knife_axe_head_weight_g = sanitize_text_field( wp_unslash( $_POST['knife_axe_head_weight_g'] ?? '' ) );
        $knife_axe_handle_material = sanitize_text_field( wp_unslash( $_POST['knife_axe_handle_material'] ?? '' ) );
        $knife_multitool_function_count = sanitize_text_field( wp_unslash( $_POST['knife_multitool_function_count'] ?? '' ) );
        if ( $knife_blade_length_mm === '' && $knife_blade_length !== '' && is_numeric( $knife_blade_length ) ) {
            $knife_blade_length_mm = (string) ( (int) round( (float) $knife_blade_length * 10 ) );
        }
        if ( $knife_blade_length === '' && $knife_blade_length_mm !== '' && is_numeric( $knife_blade_length_mm ) ) {
            $knife_blade_length = (string) round( (float) $knife_blade_length_mm / 10, 1 );
        }
        $knife_filter_type = in_array( $knife_type, [ 'bicska', 'zsebkes' ], true ) ? 'bicska' : 'fix';
        $knife_filter_full_tang = ( $knife_construction === 'full-tang' ) ? '1' : '0';
        $knife_filter_bushcraft = ( $knife_type === 'bushcraft-kes' || strpos( ',' . $knife_usage . ',', ',bushcraft,' ) !== false ) ? '1' : '0';
        $knife_filter_vadasz = ( $knife_type === 'vadaszkes' || strpos( ',' . $knife_usage . ',', ',vadaszat,' ) !== false ) ? '1' : '0';
        $mixed_type = sanitize_key( wp_unslash( $_POST['mixed_type'] ?? '' ) );
        $mixed_barrel_count = sanitize_text_field( wp_unslash( $_POST['mixed_barrel_count'] ?? '' ) );
        $mixed_has_rifle = sanitize_key( wp_unslash( $_POST['mixed_has_rifle'] ?? '' ) );
        $mixed_rifle_caliber = sanitize_text_field( wp_unslash( $_POST['mixed_rifle_caliber'] ?? '' ) );
        $mixed_has_shotgun = sanitize_key( wp_unslash( $_POST['mixed_has_shotgun'] ?? '' ) );
        $mixed_shotgun_caliber = sanitize_text_field( wp_unslash( $_POST['mixed_shotgun_caliber'] ?? '' ) );
        $mixed_trigger_type = sanitize_key( wp_unslash( $_POST['mixed_trigger_type'] ?? '' ) );
        $mixed_action_system = sanitize_key( wp_unslash( $_POST['mixed_action_system'] ?? '' ) );
        $mixed_lock_type = sanitize_key( wp_unslash( $_POST['mixed_lock_type'] ?? '' ) );
        $mixed_sight_type = sanitize_key( wp_unslash( $_POST['mixed_sight_type'] ?? '' ) );
        $mixed_optic_rail = sanitize_key( wp_unslash( $_POST['mixed_optic_rail'] ?? '' ) );
        $mixed_optic_compatible = sanitize_key( wp_unslash( $_POST['mixed_optic_compatible'] ?? '' ) );
        $mixed_barrel_length_cm = sanitize_text_field( wp_unslash( $_POST['mixed_barrel_length_cm'] ?? '' ) );
        $mixed_barrel_material = sanitize_key( wp_unslash( $_POST['mixed_barrel_material'] ?? '' ) );
        $mixed_barrel_regulated = sanitize_key( wp_unslash( $_POST['mixed_barrel_regulated'] ?? '' ) );
        $mixed_material = sanitize_key( wp_unslash( $_POST['mixed_material'] ?? '' ) );
        $mixed_stock_material = sanitize_key( wp_unslash( $_POST['mixed_stock_material'] ?? '' ) );
        $mixed_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['mixed_usage'] ?? [] ) ) ) );
        $mixed_weight_kg = sanitize_text_field( wp_unslash( $_POST['mixed_weight_kg'] ?? '' ) );
        $mixed_total_length_cm = sanitize_text_field( wp_unslash( $_POST['mixed_total_length_cm'] ?? '' ) );
        $mixed_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['mixed_accessories'] ?? [] ) ) ) );
        $mixed_rifle_moa = sanitize_text_field( wp_unslash( $_POST['mixed_rifle_moa'] ?? '' ) );
        $mixed_shot_pattern = sanitize_text_field( wp_unslash( $_POST['mixed_shot_pattern'] ?? '' ) );
        $mixed_zero_distance_m = sanitize_text_field( wp_unslash( $_POST['mixed_zero_distance_m'] ?? '' ) );
        $mixed_safety_type = sanitize_key( wp_unslash( $_POST['mixed_safety_type'] ?? '' ) );
        $mixed_barrel_layout = sanitize_key( wp_unslash( $_POST['mixed_barrel_layout'] ?? '' ) );
        $mixed_barrel_internal = sanitize_text_field( wp_unslash( $_POST['mixed_barrel_internal'] ?? '' ) );
        $mixed_engraving = sanitize_text_field( wp_unslash( $_POST['mixed_engraving'] ?? '' ) );
        $mixed_factory_marking = sanitize_text_field( wp_unslash( $_POST['mixed_factory_marking'] ?? '' ) );
        $mixed_drilling_barrel1_caliber = sanitize_text_field( wp_unslash( $_POST['mixed_drilling_barrel1_caliber'] ?? '' ) );
        $mixed_drilling_top_barrel_role = sanitize_text_field( wp_unslash( $_POST['mixed_drilling_top_barrel_role'] ?? '' ) );
        $mixed_optic_mount_type = sanitize_text_field( wp_unslash( $_POST['mixed_optic_mount_type'] ?? '' ) );
        $mixed_mountain_weight_optimized = sanitize_key( wp_unslash( $_POST['mixed_mountain_weight_optimized'] ?? '' ) );
        $mixed_universal_label = sanitize_key( wp_unslash( $_POST['mixed_universal_label'] ?? '' ) );
        $mixed_condition = sanitize_key( wp_unslash( $_POST['mixed_condition'] ?? '' ) );
        $mixed_filter_optic = $mixed_optic_compatible === 'igen' ? '1' : '0';
        $mixed_filter_universal = $mixed_universal_label === 'igen' ? '1' : '0';
        $rifle_type = sanitize_key( wp_unslash( $_POST['rifle_type'] ?? '' ) );
        $rifle_caliber = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_caliber'] ?? [] ) ) ) );
        $rifle_barrel_length_cm = sanitize_text_field( wp_unslash( $_POST['rifle_barrel_length_cm'] ?? '' ) );
        $rifle_barrel_material = sanitize_key( wp_unslash( $_POST['rifle_barrel_material'] ?? '' ) );
        $rifle_rifling_type = sanitize_key( wp_unslash( $_POST['rifle_rifling_type'] ?? '' ) );
        $rifle_barrel_condition = sanitize_key( wp_unslash( $_POST['rifle_barrel_condition'] ?? '' ) );
        $rifle_accuracy_moa = sanitize_text_field( wp_unslash( $_POST['rifle_accuracy_moa'] ?? '' ) );
        $rifle_zero_distance_m = sanitize_text_field( wp_unslash( $_POST['rifle_zero_distance_m'] ?? '' ) );
        $rifle_factory_group = sanitize_text_field( wp_unslash( $_POST['rifle_factory_group'] ?? '' ) );
        $rifle_action_type = sanitize_key( wp_unslash( $_POST['rifle_action_type'] ?? '' ) );
        $rifle_trigger_type = sanitize_key( wp_unslash( $_POST['rifle_trigger_type'] ?? '' ) );
        $rifle_safety_type = sanitize_key( wp_unslash( $_POST['rifle_safety_type'] ?? '' ) );
        $rifle_stock_material = sanitize_key( wp_unslash( $_POST['rifle_stock_material'] ?? '' ) );
        $rifle_optic_rail_type = sanitize_key( wp_unslash( $_POST['rifle_optic_rail_type'] ?? '' ) );
        $rifle_optic_compatibility = sanitize_key( wp_unslash( $_POST['rifle_optic_compatibility'] ?? '' ) );
        $rifle_zero_return = sanitize_key( wp_unslash( $_POST['rifle_zero_return'] ?? '' ) );
        $rifle_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_accessories'] ?? [] ) ) ) );
        $rifle_weight_kg = sanitize_text_field( wp_unslash( $_POST['rifle_weight_kg'] ?? '' ) );
        $rifle_total_length_cm = sanitize_text_field( wp_unslash( $_POST['rifle_total_length_cm'] ?? '' ) );
        $rifle_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_usage'] ?? [] ) ) ) );
        $rifle_special_build = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_special_build'] ?? [] ) ) ) );
        $rifle_barrel_mount_type = sanitize_key( wp_unslash( $_POST['rifle_barrel_mount_type'] ?? '' ) );
        $rifle_impact_safe = sanitize_key( wp_unslash( $_POST['rifle_impact_safe'] ?? '' ) );
        $rifle_closed_system = sanitize_key( wp_unslash( $_POST['rifle_closed_system'] ?? '' ) );
        $rifle_effective_range_m = sanitize_text_field( wp_unslash( $_POST['rifle_effective_range_m'] ?? '' ) );
        $rifle_stability_level = sanitize_key( wp_unslash( $_POST['rifle_stability_level'] ?? '' ) );
        $rifle_recoil_level = sanitize_key( wp_unslash( $_POST['rifle_recoil_level'] ?? '' ) );
        $rifle_temperament = sanitize_key( wp_unslash( $_POST['rifle_temperament'] ?? '' ) );
        $rifle_condition = sanitize_key( wp_unslash( $_POST['rifle_condition'] ?? '' ) );
        $rifle_mountain_weight_optimized = sanitize_key( wp_unslash( $_POST['rifle_mountain_weight_optimized'] ?? '' ) );
        $rifle_magazine_capacity = sanitize_text_field( wp_unslash( $_POST['rifle_magazine_capacity'] ?? '' ) );
        $rifle_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['rifle_moderation_flags'] ?? [] ) ) ) );
        $rifle_hunter_profile_game = sanitize_text_field( wp_unslash( $_POST['rifle_hunter_profile_game'] ?? '' ) );
        $rifle_hunter_profile_optic = sanitize_text_field( wp_unslash( $_POST['rifle_hunter_profile_optic'] ?? '' ) );
        $rifle_hunter_profile_distance = sanitize_text_field( wp_unslash( $_POST['rifle_hunter_profile_distance'] ?? '' ) );
        $rifle_caliber_primary = '';
        if ( $rifle_caliber !== '' ) {
            $rifle_calibers = array_filter( array_map( 'trim', explode( ',', $rifle_caliber ) ) );
            $rifle_caliber_primary = (string) ( $rifle_calibers[0] ?? '' );
        }
        $rifle_filter_optic = $rifle_optic_compatibility === 'igen' ? '1' : '0';
        if ( $caliber === '' && $rifle_caliber_primary !== '' ) {
            $caliber = $rifle_caliber_primary;
        }
        $marok_type = sanitize_key( wp_unslash( $_POST['marok_type'] ?? '' ) );
        $marok_condition = sanitize_key( wp_unslash( $_POST['marok_condition'] ?? '' ) );
        $marok_action_type = sanitize_key( wp_unslash( $_POST['marok_action_type'] ?? '' ) );
        $marok_manufacture_year = sanitize_text_field( wp_unslash( $_POST['marok_manufacture_year'] ?? '' ) );
        $marok_origin_country = sanitize_text_field( wp_unslash( $_POST['marok_origin_country'] ?? '' ) );
        $marok_magazine_capacity = sanitize_text_field( wp_unslash( $_POST['marok_magazine_capacity'] ?? '' ) );
        $marok_cylinder_capacity = sanitize_text_field( wp_unslash( $_POST['marok_cylinder_capacity'] ?? '' ) );
        $marok_revolver_action = sanitize_key( wp_unslash( $_POST['marok_revolver_action'] ?? '' ) );
        $marok_barrel_length_mm = sanitize_text_field( wp_unslash( $_POST['marok_barrel_length_mm'] ?? '' ) );
        $marok_total_length_mm = sanitize_text_field( wp_unslash( $_POST['marok_total_length_mm'] ?? '' ) );
        $marok_weight_g = sanitize_text_field( wp_unslash( $_POST['marok_weight_g'] ?? '' ) );
        $marok_frame_material = sanitize_key( wp_unslash( $_POST['marok_frame_material'] ?? '' ) );
        $marok_slide_material = sanitize_key( wp_unslash( $_POST['marok_slide_material'] ?? '' ) );
        $marok_sight_type = sanitize_key( wp_unslash( $_POST['marok_sight_type'] ?? '' ) );
        $marok_optic_ready = sanitize_key( wp_unslash( $_POST['marok_optic_ready'] ?? '' ) );
        $marok_optic_mount = sanitize_key( wp_unslash( $_POST['marok_optic_mount'] ?? '' ) );
        $marok_red_dot_compatibility = sanitize_text_field( wp_unslash( $_POST['marok_red_dot_compatibility'] ?? '' ) );
        $marok_threaded_barrel = sanitize_key( wp_unslash( $_POST['marok_threaded_barrel'] ?? '' ) );
        $marok_rail_type = sanitize_key( wp_unslash( $_POST['marok_rail_type'] ?? '' ) );
        $marok_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_accessories'] ?? [] ) ) ) );
        $marok_license_required = sanitize_key( wp_unslash( $_POST['marok_license_required'] ?? '' ) );
        $marok_legal_category = sanitize_key( wp_unslash( $_POST['marok_legal_category'] ?? '' ) );
        $marok_technical_exam_valid = sanitize_key( wp_unslash( $_POST['marok_technical_exam_valid'] ?? '' ) );
        $marok_cip_marking = sanitize_key( wp_unslash( $_POST['marok_cip_marking'] ?? '' ) );
        $marok_transfer_license_only = sanitize_key( wp_unslash( $_POST['marok_transfer_license_only'] ?? '' ) );
        $marok_personal_pickup = sanitize_key( wp_unslash( $_POST['marok_personal_pickup'] ?? '' ) );
        $marok_serial_masked = sanitize_text_field( wp_unslash( $_POST['marok_serial_masked'] ?? '' ) );
        $marok_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_usage'] ?? [] ) ) ) );
        $marok_price_deal = sanitize_key( wp_unslash( $_POST['marok_price_deal'] ?? '' ) );
        $marok_price_exchange = sanitize_key( wp_unslash( $_POST['marok_price_exchange'] ?? '' ) );
        $marok_transfer_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_transfer_methods'] ?? [] ) ) ) );
        $marok_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_required_media'] ?? [] ) ) ) );
        $marok_recommended_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_recommended_media'] ?? [] ) ) ) );
        $marok_round_count = sanitize_key( wp_unslash( $_POST['marok_round_count'] ?? '' ) );
        $marok_trigger_tuning = sanitize_key( wp_unslash( $_POST['marok_trigger_tuning'] ?? '' ) );
        $marok_sport_categories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_sport_categories'] ?? [] ) ) ) );
        $marok_ipsc_ready = sanitize_key( wp_unslash( $_POST['marok_ipsc_ready'] ?? '' ) );
        $marok_race_trigger = sanitize_key( wp_unslash( $_POST['marok_race_trigger'] ?? '' ) );
        $marok_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['marok_moderation_flags'] ?? [] ) ) ) );
        $marok_filter_optic_ready = ( $marok_optic_ready !== '' && $marok_optic_ready !== 'nincs' ) ? '1' : '0';
        $marok_filter_threaded_barrel = ( $marok_threaded_barrel === 'igen' ) ? '1' : '0';
        $marok_filter_magazine_capacity = $marok_magazine_capacity !== '' ? $marok_magazine_capacity : $marok_cylinder_capacity;
        $trophy_species = sanitize_key( wp_unslash( $_POST['trophy_species'] ?? '' ) );
        $trophy_type = sanitize_key( wp_unslash( $_POST['trophy_type'] ?? '' ) );
        $trophy_maker = sanitize_text_field( wp_unslash( $_POST['trophy_maker'] ?? '' ) );
        $trophy_custom_made = sanitize_key( wp_unslash( $_POST['trophy_custom_made'] ?? '' ) );
        $trophy_condition = sanitize_key( wp_unslash( $_POST['trophy_condition'] ?? '' ) );
        $trophy_manufacture_year = sanitize_text_field( wp_unslash( $_POST['trophy_manufacture_year'] ?? '' ) );
        $trophy_height_cm = sanitize_text_field( wp_unslash( $_POST['trophy_height_cm'] ?? '' ) );
        $trophy_width_cm = sanitize_text_field( wp_unslash( $_POST['trophy_width_cm'] ?? '' ) );
        $trophy_thickness_cm = sanitize_text_field( wp_unslash( $_POST['trophy_thickness_cm'] ?? '' ) );
        $trophy_compatible_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_compatible_species'] ?? [] ) ) ) );
        $trophy_mount_type = sanitize_key( wp_unslash( $_POST['trophy_mount_type'] ?? '' ) );
        if ( $trophy_type !== '' ) {
            $trophy_mount_type = $trophy_type;
        }
        $trophy_mounting_type = sanitize_key( wp_unslash( $_POST['trophy_mounting_type'] ?? '' ) );
        $trophy_attachment_type = sanitize_key( wp_unslash( $_POST['trophy_attachment_type'] ?? '' ) );
        $trophy_style = sanitize_key( wp_unslash( $_POST['trophy_style'] ?? '' ) );
        $trophy_fang_mount = sanitize_key( wp_unslash( $_POST['trophy_fang_mount'] ?? '' ) );
        $trophy_material_type = sanitize_key( wp_unslash( $_POST['trophy_material_type'] ?? '' ) );
        $trophy_material = sanitize_key( wp_unslash( $_POST['trophy_material'] ?? '' ) );
        if ( $trophy_material_type !== '' ) {
            $trophy_material = $trophy_material_type;
        }
        $trophy_surface_finish = sanitize_key( wp_unslash( $_POST['trophy_surface_finish'] ?? '' ) );
        $trophy_finish = sanitize_key( wp_unslash( $_POST['trophy_finish'] ?? '' ) );
        if ( $trophy_surface_finish !== '' ) {
            $trophy_finish = $trophy_surface_finish;
        }
        $trophy_decoration_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_decoration_flags'] ?? [] ) ) ) );
        $trophy_weight_kg = sanitize_text_field( wp_unslash( $_POST['trophy_weight_kg'] ?? '' ) );
        $trophy_usage_context = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_usage_context'] ?? [] ) ) ) );
        $trophy_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_accessories'] ?? [] ) ) ) );
        $trophy_price_negotiable = sanitize_key( wp_unslash( $_POST['trophy_price_negotiable'] ?? '' ) );
        $trophy_custom_order = sanitize_key( wp_unslash( $_POST['trophy_custom_order'] ?? '' ) );
        $trophy_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_shipping_methods'] ?? [] ) ) ) );
        $trophy_fragile_handling = sanitize_key( wp_unslash( $_POST['trophy_fragile_handling'] ?? '' ) );
        $trophy_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_required_media'] ?? [] ) ) ) );
        $trophy_lead_time = sanitize_text_field( wp_unslash( $_POST['trophy_lead_time'] ?? '' ) );
        $trophy_orderable_sizes = sanitize_text_field( wp_unslash( $_POST['trophy_orderable_sizes'] ?? '' ) );
        $trophy_custom_engraving = sanitize_key( wp_unslash( $_POST['trophy_custom_engraving'] ?? '' ) );
        $trophy_house_style = sanitize_key( wp_unslash( $_POST['trophy_house_style'] ?? '' ) );
        $trophy_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['trophy_moderation_flags'] ?? [] ) ) ) );
        if ( $trophy_species === '' && $trophy_compatible_species !== '' ) {
            $trophy_species_list = array_filter( array_map( 'trim', explode( ',', $trophy_compatible_species ) ) );
            $trophy_species = $trophy_species_list[0] ?? '';
        }
        $accommodation_type = sanitize_key( wp_unslash( $_POST['accommodation_type'] ?? '' ) );
        $accommodation_capacity = sanitize_text_field( wp_unslash( $_POST['accommodation_capacity'] ?? '' ) );
        $accommodation_slot_mode = sanitize_key( wp_unslash( $_POST['accommodation_slot_mode'] ?? '' ) );
        $accommodation_from_hour = sanitize_text_field( wp_unslash( $_POST['accommodation_from_hour'] ?? '' ) );
        $accommodation_to_hour = sanitize_text_field( wp_unslash( $_POST['accommodation_to_hour'] ?? '' ) );
        $accommodation_checkin_from = sanitize_text_field( wp_unslash( $_POST['accommodation_checkin_from'] ?? '' ) );
        $accommodation_checkin_to = sanitize_text_field( wp_unslash( $_POST['accommodation_checkin_to'] ?? '' ) );
        $accommodation_checkout_from = sanitize_text_field( wp_unslash( $_POST['accommodation_checkout_from'] ?? '' ) );
        $accommodation_checkout_to = sanitize_text_field( wp_unslash( $_POST['accommodation_checkout_to'] ?? '' ) );
        $accommodation_meal_type = sanitize_key( wp_unslash( $_POST['accommodation_meal_type'] ?? '' ) );
        $accommodation_parking = sanitize_key( wp_unslash( $_POST['accommodation_parking'] ?? '' ) );
        $accommodation_settlement = sanitize_text_field( wp_unslash( $_POST['accommodation_settlement'] ?? '' ) );
        $accommodation_county_text = sanitize_text_field( wp_unslash( $_POST['accommodation_county_text'] ?? '' ) );
        $accommodation_country = sanitize_text_field( wp_unslash( $_POST['accommodation_country'] ?? '' ) );
        $accommodation_exact_address = sanitize_text_field( wp_unslash( $_POST['accommodation_exact_address'] ?? '' ) );
        $accommodation_adult_capacity = sanitize_text_field( wp_unslash( $_POST['accommodation_adult_capacity'] ?? '' ) );
        $accommodation_room_count = sanitize_text_field( wp_unslash( $_POST['accommodation_room_count'] ?? '' ) );
        $accommodation_bed_count = sanitize_text_field( wp_unslash( $_POST['accommodation_bed_count'] ?? '' ) );
        $accommodation_extra_bed = sanitize_key( wp_unslash( $_POST['accommodation_extra_bed'] ?? '' ) );
        $accommodation_hunting_nearby = sanitize_key( wp_unslash( $_POST['accommodation_hunting_nearby'] ?? '' ) );
        $accommodation_hunt_available = sanitize_key( wp_unslash( $_POST['accommodation_hunt_available'] ?? '' ) );
        $accommodation_trophy_handling = sanitize_key( wp_unslash( $_POST['accommodation_trophy_handling'] ?? '' ) );
        $accommodation_cold_room = sanitize_key( wp_unslash( $_POST['accommodation_cold_room'] ?? '' ) );
        $accommodation_gun_storage = sanitize_key( wp_unslash( $_POST['accommodation_gun_storage'] ?? '' ) );
        $accommodation_game_processing = sanitize_key( wp_unslash( $_POST['accommodation_game_processing'] ?? '' ) );
        $accommodation_offroad_parking = sanitize_key( wp_unslash( $_POST['accommodation_offroad_parking'] ?? '' ) );
        $accommodation_dog_allowed = sanitize_key( wp_unslash( $_POST['accommodation_dog_allowed'] ?? '' ) );
        $accommodation_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_features'] ?? [] ) ) ) );
        $accommodation_min_nights = sanitize_text_field( wp_unslash( $_POST['accommodation_min_nights'] ?? '' ) );
        $accommodation_bookable_period = sanitize_text_field( wp_unslash( $_POST['accommodation_bookable_period'] ?? '' ) );
        $accommodation_instant_book = sanitize_key( wp_unslash( $_POST['accommodation_instant_book'] ?? '' ) );
        $accommodation_nearby_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_nearby_species'] ?? [] ) ) ) );
        $accommodation_programs = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_programs'] ?? [] ) ) ) );
        $accommodation_environment = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_environment'] ?? [] ) ) ) );
        $accommodation_land_size_sqm = sanitize_text_field( wp_unslash( $_POST['accommodation_land_size_sqm'] ?? '' ) );
        $accommodation_land_size_hectare = sanitize_text_field( wp_unslash( $_POST['accommodation_land_size_hectare'] ?? '' ) );
        $accommodation_building_size_sqm = sanitize_text_field( wp_unslash( $_POST['accommodation_building_size_sqm'] ?? '' ) );
        $accommodation_levels_count = sanitize_text_field( wp_unslash( $_POST['accommodation_levels_count'] ?? '' ) );
        $accommodation_build_year = sanitize_text_field( wp_unslash( $_POST['accommodation_build_year'] ?? '' ) );
        $accommodation_property_condition = sanitize_key( wp_unslash( $_POST['accommodation_property_condition'] ?? '' ) );
        $accommodation_construction_type = sanitize_key( wp_unslash( $_POST['accommodation_construction_type'] ?? '' ) );
        $accommodation_heating_type = sanitize_key( wp_unslash( $_POST['accommodation_heating_type'] ?? '' ) );
        $accommodation_utilities = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_utilities'] ?? [] ) ) ) );
        $accommodation_accessibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_accessibility'] ?? [] ) ) ) );
        $accommodation_hunting_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_hunting_features'] ?? [] ) ) ) );
        $accommodation_extras = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_extras'] ?? [] ) ) ) );
        $accommodation_network_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_network_features'] ?? [] ) ) ) );
        $accommodation_use_type = sanitize_key( wp_unslash( $_POST['accommodation_use_type'] ?? '' ) );
        $accommodation_terrain_type = sanitize_key( wp_unslash( $_POST['accommodation_terrain_type'] ?? '' ) );
        $accommodation_animal_housing = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['accommodation_animal_housing'] ?? [] ) ) ) );
        $accommodation_fafaj = sanitize_text_field( wp_unslash( $_POST['accommodation_fafaj'] ?? '' ) );
        $accommodation_erdo_kora = sanitize_text_field( wp_unslash( $_POST['accommodation_erdo_kora'] ?? '' ) );
        $accommodation_vadaszhaz_trofeaszoba = sanitize_key( wp_unslash( $_POST['accommodation_vadaszhaz_trofeaszoba'] ?? '' ) );
        $accommodation_vadaszhaz_fegyvertarolo = sanitize_key( wp_unslash( $_POST['accommodation_vadaszhaz_fegyvertarolo'] ?? '' ) );
        $accommodation_tanya_gazdasagi_epuletek = sanitize_text_field( wp_unslash( $_POST['accommodation_tanya_gazdasagi_epuletek'] ?? '' ) );
        $accommodation_hunting_potential_wild_movement = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_wild_movement'] ?? '' ) );
        $accommodation_hunting_potential_isolation = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_isolation'] ?? '' ) );
        $accommodation_hunting_potential_forest_proximity = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_forest_proximity'] ?? '' ) );
        $accommodation_hunting_potential_infrastructure = sanitize_text_field( wp_unslash( $_POST['accommodation_hunting_potential_infrastructure'] ?? '' ) );
        $accommodation_filter_property_type = $accommodation_type;
        $accommodation_filter_county = sanitize_title( $accommodation_county_text );
        $accommodation_filter_land_size_sqm = $accommodation_land_size_sqm;
        $accommodation_filter_building_size_sqm = $accommodation_building_size_sqm;
        $accommodation_filter_utilities = $accommodation_utilities;
        $accommodation_filter_accessibility = $accommodation_accessibility;
        $accommodation_filter_hunting_features = $accommodation_hunting_features;
        $accommodation_filter_condition = $accommodation_property_condition;
        $hunt_slot_from_hour = sanitize_text_field( wp_unslash( $_POST['hunt_slot_from_hour'] ?? '' ) );
        $hunt_slot_to_hour = sanitize_text_field( wp_unslash( $_POST['hunt_slot_to_hour'] ?? '' ) );
        $hunt_capacity = sanitize_text_field( wp_unslash( $_POST['hunt_capacity'] ?? '' ) );
        $hunt_species_list = sanitize_text_field( wp_unslash( $_POST['hunt_species_list'] ?? '' ) );
        $hunt_lease_type = sanitize_key( wp_unslash( $_POST['hunt_lease_type'] ?? '' ) );
        $hunt_has_guide = sanitize_key( wp_unslash( $_POST['hunt_has_guide'] ?? '' ) );
        $hunt_has_tracking = sanitize_key( wp_unslash( $_POST['hunt_has_tracking'] ?? '' ) );
        $hunt_has_trophy_prep = sanitize_key( wp_unslash( $_POST['hunt_has_trophy_prep'] ?? '' ) );
        $hunt_has_trophy_judging = sanitize_key( wp_unslash( $_POST['hunt_has_trophy_judging'] ?? '' ) );
        $hunt_has_offroad = sanitize_key( wp_unslash( $_POST['hunt_has_offroad'] ?? '' ) );
        $hunt_has_accommodation = sanitize_key( wp_unslash( $_POST['hunt_has_accommodation'] ?? '' ) );
        $hunt_can_buy_game = sanitize_key( wp_unslash( $_POST['hunt_can_buy_game'] ?? '' ) );
        $hunt_type = sanitize_key( wp_unslash( $_POST['hunt_type'] ?? '' ) );
        $hunt_game_species = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_game_species'] ?? [] ) ) ) );
        $hunt_country = sanitize_text_field( wp_unslash( $_POST['hunt_country'] ?? '' ) );
        $hunt_county = sanitize_text_field( wp_unslash( $_POST['hunt_county'] ?? '' ) );
        $hunt_area_name = sanitize_text_field( wp_unslash( $_POST['hunt_area_name'] ?? '' ) );
        $hunt_exact_area = sanitize_text_field( wp_unslash( $_POST['hunt_exact_area'] ?? '' ) );
        $hunt_gps_lat = sanitize_text_field( wp_unslash( $_POST['hunt_gps_lat'] ?? '' ) );
        $hunt_gps_lng = sanitize_text_field( wp_unslash( $_POST['hunt_gps_lng'] ?? '' ) );
        $hunt_terrain_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_terrain_types'] ?? [] ) ) ) );
        $hunt_season_start = sanitize_text_field( wp_unslash( $_POST['hunt_season_start'] ?? '' ) );
        $hunt_season_end = sanitize_text_field( wp_unslash( $_POST['hunt_season_end'] ?? '' ) );
        $hunt_time_type = sanitize_key( wp_unslash( $_POST['hunt_time_type'] ?? '' ) );
        $hunt_price_type = sanitize_key( wp_unslash( $_POST['hunt_price_type'] ?? '' ) );
        $hunt_price_min = sanitize_text_field( wp_unslash( $_POST['hunt_price_min'] ?? '' ) );
        $hunt_price_max = sanitize_text_field( wp_unslash( $_POST['hunt_price_max'] ?? '' ) );
        $hunt_price_includes = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_price_includes'] ?? [] ) ) ) );
        $hunt_guide_type = sanitize_key( wp_unslash( $_POST['hunt_guide_type'] ?? '' ) );
        $hunt_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_methods'] ?? [] ) ) ) );
        $hunt_onsite_services = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_onsite_services'] ?? [] ) ) ) );
        $hunt_hunting_license_required = sanitize_key( wp_unslash( $_POST['hunt_hunting_license_required'] ?? '' ) );
        $hunt_permit_required = sanitize_key( wp_unslash( $_POST['hunt_permit_required'] ?? '' ) );
        $hunt_foreigners_allowed = sanitize_key( wp_unslash( $_POST['hunt_foreigners_allowed'] ?? '' ) );
        $hunt_min_age = sanitize_text_field( wp_unslash( $_POST['hunt_min_age'] ?? '' ) );
        $hunt_safety_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_safety_features'] ?? [] ) ) ) );
        $hunt_conditions = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_conditions'] ?? [] ) ) ) );
        $hunt_difficulty_level = sanitize_key( wp_unslash( $_POST['hunt_difficulty_level'] ?? '' ) );
        $hunt_expected_trophy_quality = sanitize_key( wp_unslash( $_POST['hunt_expected_trophy_quality'] ?? '' ) );
        $hunt_trophy_weight_age = sanitize_text_field( wp_unslash( $_POST['hunt_trophy_weight_age'] ?? '' ) );
        $hunt_required_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_required_media'] ?? [] ) ) ) );
        $hunt_video_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_video_types'] ?? [] ) ) ) );
        $hunt_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_moderation_flags'] ?? [] ) ) ) );
        $hunt_trophy_category = sanitize_text_field( wp_unslash( $_POST['hunt_trophy_category'] ?? '' ) );
        $hunt_trophy_weight_limit = sanitize_text_field( wp_unslash( $_POST['hunt_trophy_weight_limit'] ?? '' ) );
        $hunt_travel_assist = sanitize_key( wp_unslash( $_POST['hunt_travel_assist'] ?? '' ) );
        $hunt_interpreter = sanitize_key( wp_unslash( $_POST['hunt_interpreter'] ?? '' ) );
        $hunt_group_size = sanitize_text_field( wp_unslash( $_POST['hunt_group_size'] ?? '' ) );
        $hunt_shot_limit = sanitize_text_field( wp_unslash( $_POST['hunt_shot_limit'] ?? '' ) );
        $hunt_real_time_slots = sanitize_text_field( wp_unslash( $_POST['hunt_real_time_slots'] ?? '' ) );
        $hunt_bookable_dates = sanitize_text_field( wp_unslash( $_POST['hunt_bookable_dates'] ?? '' ) );
        $hunt_damage_main_type = sanitize_key( wp_unslash( $_POST['hunt_damage_main_type'] ?? '' ) );
        $hunt_settlement = sanitize_text_field( wp_unslash( $_POST['hunt_settlement'] ?? '' ) );
        $hunt_land_type = sanitize_key( wp_unslash( $_POST['hunt_land_type'] ?? '' ) );
        $hunt_response_time = sanitize_key( wp_unslash( $_POST['hunt_response_time'] ?? '' ) );
        $hunt_availability_mode = sanitize_key( wp_unslash( $_POST['hunt_availability_mode'] ?? '' ) );
        $hunt_executor_type = sanitize_key( wp_unslash( $_POST['hunt_executor_type'] ?? '' ) );
        $hunt_executor_capacity = sanitize_key( wp_unslash( $_POST['hunt_executor_capacity'] ?? '' ) );
        $hunt_tools = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_tools'] ?? [] ) ) ) );
        $hunt_weapon_use_required = sanitize_key( wp_unslash( $_POST['hunt_weapon_use_required'] ?? '' ) );
        $hunt_land_manager_permit = sanitize_key( wp_unslash( $_POST['hunt_land_manager_permit'] ?? '' ) );
        $hunt_hunting_company_coop = sanitize_key( wp_unslash( $_POST['hunt_hunting_company_coop'] ?? '' ) );
        $hunt_liability_insurance = sanitize_key( wp_unslash( $_POST['hunt_liability_insurance'] ?? '' ) );
        $hunt_damage_nature = sanitize_key( wp_unslash( $_POST['hunt_damage_nature'] ?? '' ) );
        $hunt_pricing_model = sanitize_key( wp_unslash( $_POST['hunt_pricing_model'] ?? '' ) );
        $hunt_travel_fee = sanitize_text_field( wp_unslash( $_POST['hunt_travel_fee'] ?? '' ) );
        $hunt_contract_type = sanitize_key( wp_unslash( $_POST['hunt_contract_type'] ?? '' ) );
        $hunt_site_conditions = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_site_conditions'] ?? [] ) ) ) );
        $hunt_reference_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunt_reference_media'] ?? [] ) ) ) );
        $hunt_agri_hectare_size = sanitize_text_field( wp_unslash( $_POST['hunt_agri_hectare_size'] ?? '' ) );
        $hunt_agri_culture_type = sanitize_text_field( wp_unslash( $_POST['hunt_agri_culture_type'] ?? '' ) );
        $hunt_urgent_immediate_start = sanitize_key( wp_unslash( $_POST['hunt_urgent_immediate_start'] ?? '' ) );
        $hunt_urgent_duty_phone = sanitize_text_field( wp_unslash( $_POST['hunt_urgent_duty_phone'] ?? '' ) );
        $hunt_long_term_yearly_discount = sanitize_text_field( wp_unslash( $_POST['hunt_long_term_yearly_discount'] ?? '' ) );
        $hunt_package_auto_seasonal = sanitize_key( wp_unslash( $_POST['hunt_package_auto_seasonal'] ?? '' ) );
        $hunt_package_monthly_model = sanitize_key( wp_unslash( $_POST['hunt_package_monthly_model'] ?? '' ) );
        $hunt_package_monitoring_intervention = sanitize_key( wp_unslash( $_POST['hunt_package_monitoring_intervention'] ?? '' ) );
        if ( $hunt_species_list === '' && $hunt_game_species !== '' ) {
            $hunt_species_list = $hunt_game_species;
        }
        if ( $hunt_has_guide === '' && $hunt_guide_type !== '' ) {
            $hunt_has_guide = $hunt_guide_type === 'nincs' ? 'nem' : 'igen';
        }
        if ( $hunt_price_type === '' && $hunt_pricing_model !== '' ) {
            $hunt_price_type = $hunt_pricing_model;
        }
        $hunt_response_time_hours = self::map_response_time_to_hours( $hunt_response_time );
        $hunt_availability_24_7 = $hunt_availability_mode === '0-24' ? '1' : '0';
        $hunt_method_types = $hunt_methods;
        $exchange_target = sanitize_text_field( wp_unslash( $_POST['exchange_target'] ?? '' ) );
        $exchange_type = sanitize_key( wp_unslash( $_POST['exchange_type'] ?? '' ) );
        $exchange_offer_category = sanitize_key( wp_unslash( $_POST['exchange_offer_category'] ?? '' ) );
        $exchange_item_name = sanitize_text_field( wp_unslash( $_POST['exchange_item_name'] ?? '' ) );
        $exchange_brand = sanitize_text_field( wp_unslash( $_POST['exchange_brand'] ?? '' ) );
        $exchange_model = sanitize_text_field( wp_unslash( $_POST['exchange_model'] ?? '' ) );
        $exchange_condition = sanitize_key( wp_unslash( $_POST['exchange_condition'] ?? '' ) );
        $exchange_short_desc = sanitize_textarea_field( wp_unslash( $_POST['exchange_short_desc'] ?? '' ) );
        $exchange_description = sanitize_textarea_field( wp_unslash( $_POST['exchange_description'] ?? '' ) );
        $exchange_defects = sanitize_textarea_field( wp_unslash( $_POST['exchange_defects'] ?? '' ) );
        $exchange_wear_signs = sanitize_textarea_field( wp_unslash( $_POST['exchange_wear_signs'] ?? '' ) );
        $exchange_estimated_value = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['exchange_estimated_value'] ?? '' ) );
        $exchange_extra_payment_direction = sanitize_key( wp_unslash( $_POST['exchange_extra_payment_direction'] ?? '' ) );
        $exchange_extra_payment_amount = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['exchange_extra_payment_amount'] ?? '' ) );
        $exchange_wanted_categories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['exchange_wanted_categories'] ?? [] ) ) ) );
        $exchange_country = sanitize_text_field( wp_unslash( $_POST['exchange_country'] ?? '' ) );
        $exchange_county = sanitize_text_field( wp_unslash( $_POST['exchange_county'] ?? '' ) );
        $exchange_city = sanitize_text_field( wp_unslash( $_POST['exchange_city'] ?? '' ) );
        $exchange_method = sanitize_key( wp_unslash( $_POST['exchange_method'] ?? '' ) );
        $exchange_legal_notes = sanitize_textarea_field( wp_unslash( $_POST['exchange_legal_notes'] ?? '' ) );
        $exchange_age_18 = sanitize_key( wp_unslash( $_POST['exchange_age_18'] ?? '' ) );
        $exchange_required_images = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['exchange_required_images'] ?? [] ) ) ) );
        $exchange_video_url = esc_url_raw( wp_unslash( $_POST['exchange_video_url'] ?? '' ) );
        $exchange_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['exchange_moderation_flags'] ?? [] ) ) ) );
        $exchange_partner_requirements = sanitize_textarea_field( wp_unslash( $_POST['exchange_partner_requirements'] ?? '' ) );
        $exchange_firearm_caliber = sanitize_text_field( wp_unslash( $_POST['exchange_firearm_caliber'] ?? '' ) );
        $exchange_firearm_license = sanitize_key( wp_unslash( $_POST['exchange_firearm_license'] ?? '' ) );
        $exchange_optic_zoom = sanitize_text_field( wp_unslash( $_POST['exchange_optic_zoom'] ?? '' ) );
        $exchange_optic_objective = sanitize_text_field( wp_unslash( $_POST['exchange_optic_objective'] ?? '' ) );
        $exchange_knife_steel = sanitize_text_field( wp_unslash( $_POST['exchange_knife_steel'] ?? '' ) );
        $publication_type = sanitize_key( wp_unslash( $_POST['publication_type'] ?? '' ) );
        $publication_topics = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_topics'] ?? [] ) ) ) );
        $publication_title = sanitize_text_field( wp_unslash( $_POST['publication_title'] ?? '' ) );
        $publication_subtitle = sanitize_text_field( wp_unslash( $_POST['publication_subtitle'] ?? '' ) );
        $publication_author = sanitize_text_field( wp_unslash( $_POST['publication_author'] ?? '' ) );
        $publication_publisher = sanitize_text_field( wp_unslash( $_POST['publication_publisher'] ?? '' ) );
        $publication_year = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['publication_year'] ?? '' ) );
        $publication_language = sanitize_key( wp_unslash( $_POST['publication_language'] ?? '' ) );
        $publication_edition_type = sanitize_key( wp_unslash( $_POST['publication_edition_type'] ?? '' ) );
        $publication_periodical_name = sanitize_text_field( wp_unslash( $_POST['publication_periodical_name'] ?? '' ) );
        $publication_volume = sanitize_text_field( wp_unslash( $_POST['publication_volume'] ?? '' ) );
        $publication_issue = sanitize_text_field( wp_unslash( $_POST['publication_issue'] ?? '' ) );
        $publication_complete_volume = sanitize_key( wp_unslash( $_POST['publication_complete_volume'] ?? '' ) );
        $publication_page_count = sanitize_text_field( wp_unslash( $_POST['publication_page_count'] ?? '' ) );
        $publication_cover_type = sanitize_key( wp_unslash( $_POST['publication_cover_type'] ?? '' ) );
        $publication_size = sanitize_text_field( wp_unslash( $_POST['publication_size'] ?? '' ) );
        $publication_condition = sanitize_key( wp_unslash( $_POST['publication_condition'] ?? '' ) );
        $publication_defects = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_defects'] ?? [] ) ) ) );
        $publication_content_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_content_flags'] ?? [] ) ) ) );
        $publication_collector_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_collector_flags'] ?? [] ) ) ) );
        $publication_collector_value = sanitize_key( wp_unslash( $_POST['publication_collector_value'] ?? '' ) );
        $publication_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_usage'] ?? [] ) ) ) );
        $publication_condition_details = sanitize_textarea_field( wp_unslash( $_POST['publication_condition_details'] ?? '' ) );
        $publication_authenticity = sanitize_key( wp_unslash( $_POST['publication_authenticity'] ?? '' ) );
        $publication_signed_copy = sanitize_key( wp_unslash( $_POST['publication_signed_copy'] ?? '' ) );
        $publication_signed_to = sanitize_text_field( wp_unslash( $_POST['publication_signed_to'] ?? '' ) );
        $publication_signature_type = sanitize_key( wp_unslash( $_POST['publication_signature_type'] ?? '' ) );
        $publication_isbn = sanitize_text_field( wp_unslash( $_POST['publication_isbn'] ?? '' ) );
        $publication_digital_format = sanitize_key( wp_unslash( $_POST['publication_digital_format'] ?? '' ) );
        $publication_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['publication_moderation_flags'] ?? [] ) ) ) );
        $publication_notes = sanitize_textarea_field( wp_unslash( $_POST['publication_notes'] ?? '' ) );
        if ( $publication_signed_copy === '' && $publication_edition_type === 'dedikalt' ) {
            $publication_signed_copy = 'igen';
        }
        $publication_filter_collector_item = ( in_array( $publication_collector_value, [ 'magas', 'kiemelt' ], true ) || strpos( ',' . $publication_collector_flags . ',', ',ritka-kiadas,' ) !== false || strpos( ',' . $publication_collector_flags . ',', ',muzealis,' ) !== false || in_array( $publication_edition_type, [ 'gyujtoi', 'limitalt', 'antikvar' ], true ) ) ? '1' : '0';
        $publication_filter_complete_volume = $publication_complete_volume === 'igen' ? '1' : '0';
        $publication_review_hits = array_values( array_filter( array_map( 'trim', explode( ',', $publication_moderation_flags ) ) ) );
        $equipment_type = sanitize_key( wp_unslash( $_POST['equipment_type'] ?? '' ) );
        $equipment_condition = sanitize_key( wp_unslash( $_POST['equipment_condition'] ?? '' ) );
        $equipment_hunting_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_hunting_usage'] ?? [] ) ) ) );
        $equipment_material_type = sanitize_key( wp_unslash( $_POST['equipment_material_type'] ?? '' ) );
        $equipment_color_pattern = sanitize_key( wp_unslash( $_POST['equipment_color_pattern'] ?? '' ) );
        $equipment_length_cm = sanitize_text_field( wp_unslash( $_POST['equipment_length_cm'] ?? '' ) );
        $equipment_width_cm = sanitize_text_field( wp_unslash( $_POST['equipment_width_cm'] ?? '' ) );
        $equipment_height_cm = sanitize_text_field( wp_unslash( $_POST['equipment_height_cm'] ?? '' ) );
        $equipment_weight_g = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_weight_g'] ?? '' ) );
        $equipment_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_features'] ?? [] ) ) ) );
        $equipment_light_lumen = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_light_lumen'] ?? '' ) );
        $equipment_light_range_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_light_range_m'] ?? '' ) );
        $equipment_light_battery_type = sanitize_text_field( wp_unslash( $_POST['equipment_light_battery_type'] ?? '' ) );
        $equipment_light_usb_charge = sanitize_key( wp_unslash( $_POST['equipment_light_usb_charge'] ?? '' ) );
        $equipment_light_color_modes = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_light_color_modes'] ?? [] ) ) ) );
        $equipment_light_color_temperature = sanitize_text_field( wp_unslash( $_POST['equipment_light_color_temperature'] ?? '' ) );
        $equipment_support_height_range_cm = sanitize_text_field( wp_unslash( $_POST['equipment_support_height_range_cm'] ?? '' ) );
        $equipment_support_legs = sanitize_text_field( wp_unslash( $_POST['equipment_support_legs'] ?? '' ) );
        $equipment_support_head_swivel = sanitize_key( wp_unslash( $_POST['equipment_support_head_swivel'] ?? '' ) );
        $equipment_support_material = sanitize_key( wp_unslash( $_POST['equipment_support_material'] ?? '' ) );
        $equipment_call_type = sanitize_key( wp_unslash( $_POST['equipment_call_type'] ?? '' ) );
        $equipment_call_species = sanitize_text_field( wp_unslash( $_POST['equipment_call_species'] ?? '' ) );
        $equipment_call_volume = sanitize_text_field( wp_unslash( $_POST['equipment_call_volume'] ?? '' ) );
        $equipment_trap_type = sanitize_key( wp_unslash( $_POST['equipment_trap_type'] ?? '' ) );
        $equipment_trap_size = sanitize_text_field( wp_unslash( $_POST['equipment_trap_size'] ?? '' ) );
        $equipment_cleaning_caliber_compatibility = sanitize_text_field( wp_unslash( $_POST['equipment_cleaning_caliber_compatibility'] ?? '' ) );
        $equipment_cleaning_full_set = sanitize_key( wp_unslash( $_POST['equipment_cleaning_full_set'] ?? '' ) );
        $equipment_cleaning_oil = sanitize_key( wp_unslash( $_POST['equipment_cleaning_oil'] ?? '' ) );
        $equipment_cleaning_bronze_brush = sanitize_key( wp_unslash( $_POST['equipment_cleaning_bronze_brush'] ?? '' ) );
        $equipment_seat_foldable = sanitize_key( wp_unslash( $_POST['equipment_seat_foldable'] ?? '' ) );
        $equipment_seat_backrest = sanitize_key( wp_unslash( $_POST['equipment_seat_backrest'] ?? '' ) );
        $equipment_seat_swivel = sanitize_key( wp_unslash( $_POST['equipment_seat_swivel'] ?? '' ) );
        $equipment_seat_capacity_kg = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['equipment_seat_capacity_kg'] ?? '' ) );
        $equipment_electronic_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_electronic_features'] ?? [] ) ) ) );
        $equipment_runtime_hours = sanitize_text_field( wp_unslash( $_POST['equipment_runtime_hours'] ?? '' ) );
        $equipment_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_accessories'] ?? [] ) ) ) );
        $equipment_silence_level = sanitize_key( wp_unslash( $_POST['equipment_silence_level'] ?? '' ) );
        $equipment_weapon_compatibility = sanitize_text_field( wp_unslash( $_POST['equipment_weapon_compatibility'] ?? '' ) );
        $equipment_caliber_compatibility = sanitize_text_field( wp_unslash( $_POST['equipment_caliber_compatibility'] ?? '' ) );
        $equipment_moderation_flags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['equipment_moderation_flags'] ?? [] ) ) ) );
        $equipment_notes = sanitize_textarea_field( wp_unslash( $_POST['equipment_notes'] ?? '' ) );
        $other_equipment_type = sanitize_key( wp_unslash( $_POST['other_equipment_type'] ?? '' ) );
        $other_equipment_function = sanitize_text_field( wp_unslash( $_POST['other_equipment_function'] ?? '' ) );
        $other_equipment_material_note = sanitize_text_field( wp_unslash( $_POST['other_equipment_material_note'] ?? '' ) );
        $other_equipment_compatibility = sanitize_text_field( wp_unslash( $_POST['other_equipment_compatibility'] ?? '' ) );
        $other_hunting_relevance = sanitize_key( wp_unslash( $_POST['other_hunting_relevance'] ?? '' ) );
        $other_uncertain_classification = sanitize_key( wp_unslash( $_POST['other_uncertain_classification'] ?? '' ) );
        $other_auto_suggested_category = sanitize_key( wp_unslash( $_POST['other_auto_suggested_category'] ?? '' ) );
        $other_auto_suggested_reason = sanitize_text_field( wp_unslash( $_POST['other_auto_suggested_reason'] ?? '' ) );
        $light_type = sanitize_key( wp_unslash( $_POST['light_type'] ?? '' ) );
        $lumens = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['lumens'] ?? '' ) );
        $light_levels_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['light_levels_count'] ?? '' ) );
        $light_turbo_mode = sanitize_key( wp_unslash( $_POST['light_turbo_mode'] ?? '' ) );
        $light_eco_mode = sanitize_key( wp_unslash( $_POST['light_eco_mode'] ?? '' ) );
        $beam_distance_m = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['beam_distance_m'] ?? '' ) );
        $beam_distance_band = sanitize_key( wp_unslash( $_POST['beam_distance_band'] ?? '' ) );
        $light_color = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_color'] ?? [] ) ) ) );
        $battery_type = sanitize_key( wp_unslash( $_POST['battery_type'] ?? '' ) );
        $light_usb_rechargeable = sanitize_key( wp_unslash( $_POST['light_usb_rechargeable'] ?? '' ) );
        $light_powerbank_compatible = sanitize_key( wp_unslash( $_POST['light_powerbank_compatible'] ?? '' ) );
        $runtime_hours = sanitize_text_field( wp_unslash( $_POST['runtime_hours'] ?? '' ) );
        $runtime_eco_hours = sanitize_text_field( wp_unslash( $_POST['runtime_eco_hours'] ?? '' ) );
        $runtime_turbo_hours = sanitize_text_field( wp_unslash( $_POST['runtime_turbo_hours'] ?? '' ) );
        $light_material = sanitize_key( wp_unslash( $_POST['light_material'] ?? '' ) );
        $light_ip_rating = sanitize_key( wp_unslash( $_POST['light_ip_rating'] ?? '' ) );
        $light_resistance = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_resistance'] ?? [] ) ) ) );
        $mount_type = sanitize_key( wp_unslash( $_POST['mount_type'] ?? '' ) );
        $light_mount_systems = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_mount_systems'] ?? [] ) ) ) );
        $hunting_light_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hunting_light_usage'] ?? [] ) ) ) );
        $light_functions = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_functions'] ?? [] ) ) ) );
        $light_optical_properties = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_optical_properties'] ?? [] ) ) ) );
        $light_weapon_type_compatibility = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['light_weapon_type_compatibility'] ?? [] ) ) ) );
        $light_optics_compatible = sanitize_key( wp_unslash( $_POST['light_optics_compatible'] ?? '' ) );
        $light_ir_compatible = sanitize_key( wp_unslash( $_POST['light_ir_compatible'] ?? '' ) );
        $light_weight_g = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['light_weight_g'] ?? '' ) );
        $light_length_mm = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['light_length_mm'] ?? '' ) );
        $light_size_category = sanitize_key( wp_unslash( $_POST['light_size_category'] ?? '' ) );
        $recoil_resistance = sanitize_key( wp_unslash( $_POST['recoil_resistance'] ?? '' ) );
        $rail_compatibility = sanitize_key( wp_unslash( $_POST['rail_compatibility'] ?? '' ) );
        $ir_wavelength_nm = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['ir_wavelength_nm'] ?? '' ) );
        $head_tiltable = sanitize_key( wp_unslash( $_POST['head_tiltable'] ?? '' ) );
        $headband_system = sanitize_text_field( wp_unslash( $_POST['headband_system'] ?? '' ) );
        $target_identification_index = sanitize_text_field( wp_unslash( $_POST['target_identification_index'] ?? '' ) );
        if ( $equipment_light_lumen === '' && $lumens !== '' ) {
            $equipment_light_lumen = $lumens;
        }
        if ( $equipment_light_range_m === '' && $beam_distance_m !== '' ) {
            $equipment_light_range_m = $beam_distance_m;
        }
        if ( $equipment_light_battery_type === '' && $battery_type !== '' ) {
            $equipment_light_battery_type = $battery_type;
        }
        if ( $equipment_light_usb_charge === '' && $light_usb_rechargeable !== '' ) {
            $equipment_light_usb_charge = $light_usb_rechargeable;
        }
        if ( $equipment_light_color_modes === '' && $light_color !== '' ) {
            $equipment_light_color_modes = $light_color;
        }
        if ( $equipment_runtime_hours === '' && $runtime_hours !== '' ) {
            $equipment_runtime_hours = $runtime_hours;
        }
        $equipment_review_hits = array_values( array_filter( array_map( 'trim', explode( ',', $equipment_moderation_flags ) ) ) );
        $equipment_filter_waterproof = ( strpos( ',' . $equipment_features . ',', ',vizallo,' ) !== false || $light_ip_rating !== '' ) ? '1' : '0';
        $equipment_filter_silent = ( strpos( ',' . $equipment_features . ',', ',hangtalan,' ) !== false || $equipment_silence_level === 'hangtalan' ) ? '1' : '0';
        $equipment_filter_foldable = ( strpos( ',' . $equipment_features . ',', ',osszecsukhato,' ) !== false || $equipment_seat_foldable === 'igen' ) ? '1' : '0';
        $equipment_filter_light_type = $light_type;
        $equipment_filter_light_color = $light_color;
        $equipment_filter_lumens = $lumens;
        $equipment_filter_beam_distance_m = $beam_distance_m;
        $equipment_filter_mount_type = $mount_type;
        $equipment_filter_battery_type = $battery_type;
        $equipment_filter_ip_rating = $light_ip_rating;
        $equipment_filter_recoil_resistance = $recoil_resistance;
        $bag_type = sanitize_key( wp_unslash( $_POST['bag_type'] ?? '' ) );
        $volume_l = sanitize_key( wp_unslash( $_POST['volume_l'] ?? '' ) );
        $bag_size = sanitize_key( wp_unslash( $_POST['bag_size'] ?? '' ) );
        $bag_material = sanitize_key( wp_unslash( $_POST['bag_material'] ?? '' ) );
        $bag_weather_resistance = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_weather_resistance'] ?? [] ) ) ) );
        $bag_color_pattern = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_color_pattern'] ?? [] ) ) ) );
        $bag_camo_pattern = sanitize_key( wp_unslash( $_POST['bag_camo_pattern'] ?? '' ) );
        $bag_modular = sanitize_key( wp_unslash( $_POST['bag_modular'] ?? '' ) );
        $bag_design_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_design_features'] ?? [] ) ) ) );
        $bag_usage = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_usage'] ?? [] ) ) ) );
        $bag_firearm_compatible = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_firearm_compatible'] ?? [] ) ) ) );
        $bag_max_firearm_length_cm = sanitize_text_field( wp_unslash( $_POST['bag_max_firearm_length_cm'] ?? '' ) );
        $bag_inner_padding = sanitize_key( wp_unslash( $_POST['bag_inner_padding'] ?? '' ) );
        $bag_lockable = sanitize_key( wp_unslash( $_POST['bag_lockable'] ?? '' ) );
        $bag_hydration_system = sanitize_key( wp_unslash( $_POST['bag_hydration_system'] ?? '' ) );
        $bag_touring_compatible = sanitize_key( wp_unslash( $_POST['bag_touring_compatible'] ?? '' ) );
        $bag_mag_holder_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_mag_holder_count'] ?? '' ) );
        $bag_ammo_compartment_count = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_ammo_compartment_count'] ?? '' ) );
        $bag_main_compartments = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_main_compartments'] ?? '' ) );
        $bag_outer_pockets = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_outer_pockets'] ?? '' ) );
        $bag_internal_organizer = sanitize_key( wp_unslash( $_POST['bag_internal_organizer'] ?? '' ) );
        $bag_ammo_holder = sanitize_key( wp_unslash( $_POST['bag_ammo_holder'] ?? '' ) );
        $bag_document_compartment = sanitize_key( wp_unslash( $_POST['bag_document_compartment'] ?? '' ) );
        $bag_carrying_system = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_carrying_system'] ?? [] ) ) ) );
        $bag_max_load_kg = sanitize_text_field( wp_unslash( $_POST['bag_max_load_kg'] ?? '' ) );
        $bag_comfort = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['bag_comfort'] ?? [] ) ) ) );
        $bag_closure_type = sanitize_key( wp_unslash( $_POST['bag_closure_type'] ?? '' ) );
        $bag_condition = sanitize_key( wp_unslash( $_POST['bag_condition'] ?? '' ) );
        $bag_packability_index = preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['bag_packability_index'] ?? '' ) );
        $bag_filter_waterproof = ( strpos( ',' . $bag_weather_resistance . ',', ',vizallo,' ) !== false || strpos( ',' . $bag_weather_resistance . ',', ',vizlepergeto,' ) !== false ) ? '1' : '0';
        $bag_filter_molle = strpos( ',' . $bag_design_features . ',', ',molle-kompatibilis,' ) !== false ? '1' : '0';
        $bag_filter_modular = ( $bag_modular === 'igen' || strpos( ',' . $bag_design_features . ',', ',modularis,' ) !== false ) ? '1' : '0';
        $bag_filter_firearm_compatible = $bag_firearm_compatible;
        $bag_filter_color = $bag_color_pattern;
        $bag_filter_material = $bag_material;
        $call_fields = self::collect_call_fields_from_request();
        $decor_type = sanitize_key( wp_unslash( $_POST['decor_type'] ?? '' ) );
        $decor_material = sanitize_key( wp_unslash( $_POST['decor_material'] ?? '' ) );
        $decor_wood_type = sanitize_text_field( wp_unslash( $_POST['decor_wood_type'] ?? '' ) );
        $decor_metal_alloy = sanitize_text_field( wp_unslash( $_POST['decor_metal_alloy'] ?? '' ) );
        $decor_style = sanitize_key( wp_unslash( $_POST['decor_style'] ?? '' ) );
        $decor_motif_tags = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['decor_motif_tags'] ?? [] ) ) ) );
        $decor_finish = sanitize_key( wp_unslash( $_POST['decor_finish'] ?? '' ) );
        $decor_detailing = sanitize_key( wp_unslash( $_POST['decor_detailing'] ?? '' ) );
        $decor_era = sanitize_key( wp_unslash( $_POST['decor_era'] ?? '' ) );
        $decor_production_type = sanitize_key( wp_unslash( $_POST['decor_production_type'] ?? '' ) );
        $decor_function = sanitize_key( wp_unslash( $_POST['decor_function'] ?? '' ) );
        $decor_thematic_group = sanitize_key( wp_unslash( $_POST['decor_thematic_group'] ?? '' ) );
        $decor_special_features = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['decor_special_features'] ?? [] ) ) ) );
        $decor_mounting = sanitize_key( wp_unslash( $_POST['decor_mounting'] ?? '' ) );
        $decor_size_class = sanitize_key( wp_unslash( $_POST['decor_size_class'] ?? '' ) );
        $decor_condition = sanitize_key( wp_unslash( $_POST['decor_condition'] ?? '' ) );
        $decor_replica_type = sanitize_text_field( wp_unslash( $_POST['decor_replica_type'] ?? '' ) );
        $decor_authenticity_level = sanitize_key( wp_unslash( $_POST['decor_authenticity_level'] ?? '' ) );
        $decor_collectible_flag = sanitize_key( wp_unslash( $_POST['decor_collectible_flag'] ?? '' ) );
        $decor_filter_collectible = $decor_collectible_flag === 'igen' ? '1' : '0';
        $vadcamera_camera_type = sanitize_key( wp_unslash( $_POST['vadcamera_camera_type'] ?? '' ) );
        $vadcamera_condition = sanitize_key( wp_unslash( $_POST['vadcamera_condition'] ?? '' ) );
        $vadcamera_warranty = sanitize_key( wp_unslash( $_POST['vadcamera_warranty'] ?? '' ) );
        $vadcamera_manufacture_year = sanitize_text_field( wp_unslash( $_POST['vadcamera_manufacture_year'] ?? '' ) );
        $vadcamera_photo_resolution = sanitize_key( wp_unslash( $_POST['vadcamera_photo_resolution'] ?? '' ) );
        $vadcamera_video_resolution = sanitize_key( wp_unslash( $_POST['vadcamera_video_resolution'] ?? '' ) );
        $vadcamera_video_fps = sanitize_key( wp_unslash( $_POST['vadcamera_video_fps'] ?? '' ) );
        $vadcamera_video_audio = sanitize_key( wp_unslash( $_POST['vadcamera_video_audio'] ?? '' ) );
        $vadcamera_ir_type = sanitize_key( wp_unslash( $_POST['vadcamera_ir_type'] ?? '' ) );
        $vadcamera_night_range_m = sanitize_text_field( wp_unslash( $_POST['vadcamera_night_range_m'] ?? '' ) );
        $vadcamera_ir_led_count = sanitize_text_field( wp_unslash( $_POST['vadcamera_ir_led_count'] ?? '' ) );
        $vadcamera_trigger_speed = sanitize_key( wp_unslash( $_POST['vadcamera_trigger_speed'] ?? '' ) );
        $vadcamera_pir_distance_m = sanitize_text_field( wp_unslash( $_POST['vadcamera_pir_distance_m'] ?? '' ) );
        $vadcamera_view_angle_deg = sanitize_text_field( wp_unslash( $_POST['vadcamera_view_angle_deg'] ?? '' ) );
        $vadcamera_connectivity = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_connectivity'] ?? [] ) ) ) );
        $vadcamera_sim_slot = sanitize_key( wp_unslash( $_POST['vadcamera_sim_slot'] ?? '' ) );
        $vadcamera_mobile_app = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_mobile_app'] ?? [] ) ) ) );
        $vadcamera_sd_max_gb = sanitize_text_field( wp_unslash( $_POST['vadcamera_sd_max_gb'] ?? '' ) );
        $vadcamera_internal_memory = sanitize_key( wp_unslash( $_POST['vadcamera_internal_memory'] ?? '' ) );
        $vadcamera_power_types = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_power_types'] ?? [] ) ) ) );
        $vadcamera_runtime = sanitize_text_field( wp_unslash( $_POST['vadcamera_runtime'] ?? '' ) );
        $vadcamera_ip_rating = sanitize_key( wp_unslash( $_POST['vadcamera_ip_rating'] ?? '' ) );
        $vadcamera_temp_range = sanitize_text_field( wp_unslash( $_POST['vadcamera_temp_range'] ?? '' ) );
        $vadcamera_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_accessories'] ?? [] ) ) ) );
        $vadcamera_use_cases = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_use_cases'] ?? [] ) ) ) );
        $vadcamera_audio_record = sanitize_key( wp_unslash( $_POST['vadcamera_audio_record'] ?? '' ) );
        $vadcamera_public_area_use = sanitize_key( wp_unslash( $_POST['vadcamera_public_area_use'] ?? '' ) );
        $vadcamera_mounting = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_mounting'] ?? [] ) ) ) );
        $vadcamera_shipping_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_shipping_methods'] ?? [] ) ) ) );
        $vadcamera_media_required = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_media_required'] ?? [] ) ) ) );
        $vadcamera_sample_media = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['vadcamera_sample_media'] ?? [] ) ) ) );
        $vadcamera_4g_carrier = sanitize_text_field( wp_unslash( $_POST['vadcamera_4g_carrier'] ?? '' ) );
        $vadcamera_4g_app_support = sanitize_text_field( wp_unslash( $_POST['vadcamera_4g_app_support'] ?? '' ) );
        $vadcamera_solar_panel_power = sanitize_text_field( wp_unslash( $_POST['vadcamera_solar_panel_power'] ?? '' ) );
        $vadcamera_notes = sanitize_textarea_field( wp_unslash( $_POST['vadcamera_notes'] ?? '' ) );
        $vadcamera_filter_connectivity_4g = ( strpos( ',' . $vadcamera_connectivity . ',', ',4g,' ) !== false || strpos( ',' . $vadcamera_connectivity . ',', ',lte,' ) !== false ) ? '1' : '0';
        $vadcamera_filter_connectivity_wifi = ( strpos( ',' . $vadcamera_connectivity . ',', ',wifi,' ) !== false ) ? '1' : '0';
        $vadcamera_filter_is_solar = ( strpos( ',' . $vadcamera_power_types . ',', ',napelem,' ) !== false || $vadcamera_camera_type === 'napelemes' ) ? '1' : '0';
        $vadcamera_filter_is_sim = ( $vadcamera_sim_slot === 'igen' ) ? '1' : '0';
        $estate_main_type = sanitize_key( wp_unslash( $_POST['estate_main_type'] ?? '' ) );
        $estate_sale_mode = sanitize_key( wp_unslash( $_POST['estate_sale_mode'] ?? '' ) );
        $estate_price_total = sanitize_text_field( wp_unslash( $_POST['estate_price_total'] ?? '' ) );
        $estate_price_guide = sanitize_text_field( wp_unslash( $_POST['estate_price_guide'] ?? '' ) );
        $estate_price_per_item = sanitize_text_field( wp_unslash( $_POST['estate_price_per_item'] ?? '' ) );
        $estate_estimated_value = sanitize_text_field( wp_unslash( $_POST['estate_estimated_value'] ?? '' ) );
        $estate_negotiable = sanitize_key( wp_unslash( $_POST['estate_negotiable'] ?? '' ) );
        $estate_firearms_count = sanitize_text_field( wp_unslash( $_POST['estate_firearms_count'] ?? '' ) );
        $estate_firearms_type = sanitize_key( wp_unslash( $_POST['estate_firearms_type'] ?? '' ) );
        $estate_firearms_deactivated = sanitize_key( wp_unslash( $_POST['estate_firearms_deactivated'] ?? '' ) );
        $estate_firearms_licensed = sanitize_key( wp_unslash( $_POST['estate_firearms_licensed'] ?? '' ) );
        $estate_optics_count = sanitize_text_field( wp_unslash( $_POST['estate_optics_count'] ?? '' ) );
        $estate_clothing_count = sanitize_text_field( wp_unslash( $_POST['estate_clothing_count'] ?? '' ) );
        $estate_trophy_count = sanitize_text_field( wp_unslash( $_POST['estate_trophy_count'] ?? '' ) );
        $estate_docs_count = sanitize_text_field( wp_unslash( $_POST['estate_docs_count'] ?? '' ) );
        $estate_equipment_count = sanitize_text_field( wp_unslash( $_POST['estate_equipment_count'] ?? '' ) );
        $estate_condition = sanitize_key( wp_unslash( $_POST['estate_condition'] ?? '' ) );
        $estate_origin = sanitize_key( wp_unslash( $_POST['estate_origin'] ?? '' ) );
        $estate_legal_has_licensed_items = sanitize_key( wp_unslash( $_POST['estate_legal_has_licensed_items'] ?? '' ) );
        $estate_legal_deactivated = sanitize_key( wp_unslash( $_POST['estate_legal_deactivated'] ?? '' ) );
        $estate_legal_has_papers = sanitize_key( wp_unslash( $_POST['estate_legal_has_papers'] ?? '' ) );
        $estate_legal_bundle_only = sanitize_key( wp_unslash( $_POST['estate_legal_bundle_only'] ?? '' ) );
        $estate_transfer_methods = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_transfer_methods'] ?? [] ) ) ) );

        $estate_optic_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_optic_items'] ?? [] ) ),
            array( 'celtavcso', 'binokular', 'spektiv', 'reddot' )
        ) );
        if ( empty( $estate_optic_items ) ) {
            if ( ! empty( $_POST['estate_optic_celtavcso'] ) ) { $estate_optic_items[] = 'celtavcso'; }
            if ( ! empty( $_POST['estate_optic_binokular'] ) ) { $estate_optic_items[] = 'binokular'; }
            if ( ! empty( $_POST['estate_optic_spektiv'] ) ) { $estate_optic_items[] = 'spektiv'; }
            if ( ! empty( $_POST['estate_optic_reddot'] ) ) { $estate_optic_items[] = 'reddot'; }
        }

        $estate_clothing_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_clothing_items'] ?? [] ) ),
            array( 'kabat', 'nadrag', 'csizma', 'esoruha' )
        ) );
        if ( empty( $estate_clothing_items ) ) {
            if ( ! empty( $_POST['estate_clothing_kabat'] ) ) { $estate_clothing_items[] = 'kabat'; }
            if ( ! empty( $_POST['estate_clothing_nadrag'] ) ) { $estate_clothing_items[] = 'nadrag'; }
            if ( ! empty( $_POST['estate_clothing_csizma'] ) ) { $estate_clothing_items[] = 'csizma'; }
            if ( ! empty( $_POST['estate_clothing_esoruha'] ) ) { $estate_clothing_items[] = 'esoruha'; }
        }

        $estate_trophy_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_trophy_items'] ?? [] ) ),
            array( 'agancs', 'koponya', 'preparatum' )
        ) );
        if ( empty( $estate_trophy_items ) ) {
            if ( ! empty( $_POST['estate_trophy_agancs'] ) ) { $estate_trophy_items[] = 'agancs'; }
            if ( ! empty( $_POST['estate_trophy_koponya'] ) ) { $estate_trophy_items[] = 'koponya'; }
            if ( ! empty( $_POST['estate_trophy_preparatum'] ) ) { $estate_trophy_items[] = 'preparatum'; }
        }

        $estate_docs_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_docs_items'] ?? [] ) ),
            array( 'engedelyek', 'vadasznaplo', 'konyvek', 'tanusitvanyok' )
        ) );
        if ( empty( $estate_docs_items ) ) {
            if ( ! empty( $_POST['estate_docs_engedelyek'] ) ) { $estate_docs_items[] = 'engedelyek'; }
            if ( ! empty( $_POST['estate_docs_vadasznaplo'] ) ) { $estate_docs_items[] = 'vadasznaplo'; }
            if ( ! empty( $_POST['estate_docs_konyvek'] ) ) { $estate_docs_items[] = 'konyvek'; }
            if ( ! empty( $_POST['estate_docs_tanusitvanyok'] ) ) { $estate_docs_items[] = 'tanusitvanyok'; }
        }

        $estate_equipment_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_equipment_items'] ?? [] ) ),
            array( 'kes', 'hatizsak', 'lampa', 'tavcso_tartozek', 'fegyverszef' )
        ) );
        if ( empty( $estate_equipment_items ) ) {
            if ( ! empty( $_POST['estate_equipment_kes'] ) ) { $estate_equipment_items[] = 'kes'; }
            if ( ! empty( $_POST['estate_equipment_hatizsak'] ) ) { $estate_equipment_items[] = 'hatizsak'; }
            if ( ! empty( $_POST['estate_equipment_lampa'] ) ) { $estate_equipment_items[] = 'lampa'; }
            if ( ! empty( $_POST['estate_equipment_tavcso_tartozek'] ) ) { $estate_equipment_items[] = 'tavcso_tartozek'; }
            if ( ! empty( $_POST['estate_equipment_fegyverszef'] ) ) { $estate_equipment_items[] = 'fegyverszef'; }
        }

        $estate_media_items = array_values( array_intersect(
            array_map( 'sanitize_key', (array) wp_unslash( $_POST['estate_media_items'] ?? [] ) ),
            array( 'full', 'details', 'documents', 'firearms' )
        ) );
        if ( empty( $estate_media_items ) ) {
            if ( ! empty( $_POST['estate_media_full'] ) ) { $estate_media_items[] = 'full'; }
            if ( ! empty( $_POST['estate_media_details'] ) ) { $estate_media_items[] = 'details'; }
            if ( ! empty( $_POST['estate_media_documents'] ) ) { $estate_media_items[] = 'documents'; }
            if ( ! empty( $_POST['estate_media_firearms'] ) ) { $estate_media_items[] = 'firearms'; }
        }

        $estate_media_full = in_array( 'full', $estate_media_items, true ) ? '1' : '0';
        $estate_media_details = in_array( 'details', $estate_media_items, true ) ? '1' : '0';
        $estate_media_documents = in_array( 'documents', $estate_media_items, true ) ? '1' : '0';
        $estate_media_firearms = in_array( 'firearms', $estate_media_items, true ) ? '1' : '0';
        $estate_optic_celtavcso = in_array( 'celtavcso', $estate_optic_items, true ) ? '1' : '0';
        $estate_optic_binokular = in_array( 'binokular', $estate_optic_items, true ) ? '1' : '0';
        $estate_optic_spektiv = in_array( 'spektiv', $estate_optic_items, true ) ? '1' : '0';
        $estate_optic_reddot = in_array( 'reddot', $estate_optic_items, true ) ? '1' : '0';
        $estate_clothing_kabat = in_array( 'kabat', $estate_clothing_items, true ) ? '1' : '0';
        $estate_clothing_nadrag = in_array( 'nadrag', $estate_clothing_items, true ) ? '1' : '0';
        $estate_clothing_csizma = in_array( 'csizma', $estate_clothing_items, true ) ? '1' : '0';
        $estate_clothing_esoruha = in_array( 'esoruha', $estate_clothing_items, true ) ? '1' : '0';
        $estate_trophy_agancs = in_array( 'agancs', $estate_trophy_items, true ) ? '1' : '0';
        $estate_trophy_koponya = in_array( 'koponya', $estate_trophy_items, true ) ? '1' : '0';
        $estate_trophy_preparatum = in_array( 'preparatum', $estate_trophy_items, true ) ? '1' : '0';
        $estate_docs_engedelyek = in_array( 'engedelyek', $estate_docs_items, true ) ? '1' : '0';
        $estate_docs_vadasznaplo = in_array( 'vadasznaplo', $estate_docs_items, true ) ? '1' : '0';
        $estate_docs_konyvek = in_array( 'konyvek', $estate_docs_items, true ) ? '1' : '0';
        $estate_docs_tanusitvanyok = in_array( 'tanusitvanyok', $estate_docs_items, true ) ? '1' : '0';
        $estate_equipment_kes = in_array( 'kes', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_hatizsak = in_array( 'hatizsak', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_lampa = in_array( 'lampa', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_tavcso_tartozek = in_array( 'tavcso_tartozek', $estate_equipment_items, true ) ? '1' : '0';
        $estate_equipment_fegyverszef = in_array( 'fegyverszef', $estate_equipment_items, true ) ? '1' : '0';
        $estate_notes = sanitize_textarea_field( wp_unslash( $_POST['estate_notes'] ?? '' ) );
        $estate_filter_has_firearms = ( $estate_firearms_count !== '' || $estate_firearms_type !== '' || $estate_firearms_licensed === 'igen' ) ? '1' : '0';
        $estate_filter_has_optics = ( $estate_optics_count !== '' || $estate_optic_celtavcso === '1' || $estate_optic_binokular === '1' || $estate_optic_spektiv === '1' || $estate_optic_reddot === '1' ) ? '1' : '0';
        $estate_filter_scope = in_array( $estate_sale_mode, [ 'egyben', 'darabonkent', 'reszletekben' ], true ) ? $estate_sale_mode : '';
        $bow_type = sanitize_key( wp_unslash( $_POST['bow_type'] ?? '' ) );
        $bow_handedness = sanitize_key( wp_unslash( $_POST['bow_handedness'] ?? '' ) );
        $bow_draw_weight = sanitize_text_field( wp_unslash( $_POST['bow_draw_weight'] ?? '' ) );
        $bow_draw_length = sanitize_text_field( wp_unslash( $_POST['bow_draw_length'] ?? '' ) );
        $bow_axle_length = sanitize_text_field( wp_unslash( $_POST['bow_axle_length'] ?? '' ) );
        $bow_item_condition = sanitize_key( wp_unslash( $_POST['bow_item_condition'] ?? '' ) );
        $bow_condition_notes = sanitize_textarea_field( wp_unslash( $_POST['bow_condition_notes'] ?? '' ) );
        $bow_string_replaced = sanitize_key( wp_unslash( $_POST['bow_string_replaced'] ?? '' ) );
        $bow_owned_since = sanitize_text_field( wp_unslash( $_POST['bow_owned_since'] ?? '' ) );
        $bow_usage = sanitize_textarea_field( wp_unslash( $_POST['bow_usage'] ?? '' ) );
        $bow_accessories = sanitize_textarea_field( wp_unslash( $_POST['bow_accessories'] ?? '' ) );
        $bow_cams_cables = sanitize_textarea_field( wp_unslash( $_POST['bow_cams_cables'] ?? '' ) );
        $bow_speed_fps = sanitize_text_field( wp_unslash( $_POST['bow_speed_fps'] ?? '' ) );
        $bow_serial = sanitize_text_field( wp_unslash( $_POST['bow_serial'] ?? '' ) );
        $bow_material = sanitize_text_field( wp_unslash( $_POST['bow_material'] ?? '' ) );
        $bow_accessories_flags_raw = isset( $_POST['bow_accessories_flags'] ) ? (array) wp_unslash( $_POST['bow_accessories_flags'] ) : array();
        $bow_accessories_flags = array_map( 'sanitize_key', $bow_accessories_flags_raw );
        $bow_accessories_allowed = array( 'bow_acc_sight', 'bow_acc_release', 'bow_acc_stabilizer', 'bow_acc_arrows', 'bow_acc_case', 'bow_acc_quiver', 'bow_acc_target' );
        $bow_accessories_flags = array_values( array_intersect( $bow_accessories_flags, $bow_accessories_allowed ) );
        $bow_acc_sight = ( in_array( 'bow_acc_sight', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_sight'] ) ) ? '1' : '0';
        $bow_acc_release = ( in_array( 'bow_acc_release', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_release'] ) ) ? '1' : '0';
        $bow_acc_stabilizer = ( in_array( 'bow_acc_stabilizer', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_stabilizer'] ) ) ? '1' : '0';
        $bow_acc_arrows = ( in_array( 'bow_acc_arrows', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_arrows'] ) ) ? '1' : '0';
        $bow_acc_case = ( in_array( 'bow_acc_case', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_case'] ) ) ? '1' : '0';
        $bow_acc_quiver = ( in_array( 'bow_acc_quiver', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_quiver'] ) ) ? '1' : '0';
        $bow_acc_target = ( in_array( 'bow_acc_target', $bow_accessories_flags, true ) || ! empty( $_POST['bow_acc_target'] ) ) ? '1' : '0';
        $feed_type = sanitize_key( wp_unslash( $_POST['feed_type'] ?? '' ) );
        $feed_recommended_game = sanitize_textarea_field( wp_unslash( $_POST['feed_recommended_game'] ?? '' ) );
        $feed_recommended_game_flags_raw = isset( $_POST['feed_recommended_game_flags'] ) ? (array) wp_unslash( $_POST['feed_recommended_game_flags'] ) : array();
        $feed_recommended_game_flags = array_map( 'sanitize_key', $feed_recommended_game_flags_raw );
        $feed_recommended_game_allowed = array( 'feed_game_boar', 'feed_game_roe', 'feed_game_red_deer', 'feed_game_pheasant', 'feed_game_other' );
        $feed_recommended_game_flags = array_values( array_intersect( $feed_recommended_game_flags, $feed_recommended_game_allowed ) );
        $feed_game_boar = ( in_array( 'feed_game_boar', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_boar'] ) ) ? '1' : '0';
        $feed_game_roe = ( in_array( 'feed_game_roe', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_roe'] ) ) ? '1' : '0';
        $feed_game_red_deer = ( in_array( 'feed_game_red_deer', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_red_deer'] ) ) ? '1' : '0';
        $feed_game_pheasant = ( in_array( 'feed_game_pheasant', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_pheasant'] ) ) ? '1' : '0';
        $feed_game_other = ( in_array( 'feed_game_other', $feed_recommended_game_flags, true ) || ! empty( $_POST['feed_game_other'] ) ) ? '1' : '0';
        $feed_game_other_text = sanitize_text_field( wp_unslash( $_POST['feed_game_other_text'] ?? '' ) );
        $feed_ingredients = sanitize_textarea_field( wp_unslash( $_POST['feed_ingredients'] ?? '' ) );
        $feed_moisture = sanitize_text_field( wp_unslash( $_POST['feed_moisture'] ?? '' ) );
        $feed_has_additives = sanitize_key( wp_unslash( $_POST['feed_has_additives'] ?? '' ) );
        $feed_packaging_type = sanitize_key( wp_unslash( $_POST['feed_packaging_type'] ?? '' ) );
        $feed_bag_weight = sanitize_text_field( wp_unslash( $_POST['feed_bag_weight'] ?? '' ) );
        $feed_total_quantity = sanitize_text_field( wp_unslash( $_POST['feed_total_quantity'] ?? '' ) );
        $feed_pallet_quantity = sanitize_text_field( wp_unslash( $_POST['feed_pallet_quantity'] ?? '' ) );
        $feed_harvest_freshness = sanitize_key( wp_unslash( $_POST['feed_harvest_freshness'] ?? '' ) );
        $feed_stored_dry = sanitize_key( wp_unslash( $_POST['feed_stored_dry'] ?? '' ) );
        $feed_mold_free = sanitize_key( wp_unslash( $_POST['feed_mold_free'] ?? '' ) );
        $feed_bags_undamaged = sanitize_key( wp_unslash( $_POST['feed_bags_undamaged'] ?? '' ) );
        $feed_price_per_unit = sanitize_text_field( wp_unslash( $_POST['feed_price_per_unit'] ?? '' ) );
        $feed_min_order = sanitize_text_field( wp_unslash( $_POST['feed_min_order'] ?? '' ) );
        $feed_delivery_available = sanitize_key( wp_unslash( $_POST['feed_delivery_available'] ?? '' ) );
        $feed_loading_available = sanitize_key( wp_unslash( $_POST['feed_loading_available'] ?? '' ) );
        $hatastalanitott_weapon_type = sanitize_key( wp_unslash( $_POST['hatastalanitott_weapon_type'] ?? '' ) );
        $hatastalanitott_origin_country = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_origin_country'] ?? '' ) );
        $hatastalanitott_is_deactivated = sanitize_key( wp_unslash( $_POST['hatastalanitott_is_deactivated'] ?? '' ) );
        $hatastalanitott_deactivation_type = sanitize_key( wp_unslash( $_POST['hatastalanitott_deactivation_type'] ?? '' ) );
        $hatastalanitott_certificate = sanitize_key( wp_unslash( $_POST['hatastalanitott_certificate'] ?? '' ) );
        $hatastalanitott_mkh_cip = sanitize_key( wp_unslash( $_POST['hatastalanitott_mkh_cip'] ?? '' ) );
        $hatastalanitott_deactivation_year = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_deactivation_year'] ?? '' ) );
        $hatastalanitott_deactivation_org = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_deactivation_org'] ?? '' ) );
        $hatastalanitott_condition = sanitize_key( wp_unslash( $_POST['hatastalanitott_condition'] ?? '' ) );
        $hatastalanitott_serial_admin = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_serial_admin'] ?? '' ) );
        $hatastalanitott_working_parts = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hatastalanitott_working_parts'] ?? [] ) ) ) );
        $hatastalanitott_original_finish = sanitize_key( wp_unslash( $_POST['hatastalanitott_original_finish'] ?? '' ) );
        $hatastalanitott_original_parts_ratio = sanitize_text_field( wp_unslash( $_POST['hatastalanitott_original_parts_ratio'] ?? '' ) );
        $hatastalanitott_accessories = implode( ',', array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['hatastalanitott_accessories'] ?? [] ) ) ) );
        $loszer_category = sanitize_key( wp_unslash( $_POST['loszer_category'] ?? '' ) );
        $loszer_caliber_preset = sanitize_key( wp_unslash( $_POST['loszer_caliber_preset'] ?? '' ) );
        $loszer_caliber_custom = sanitize_text_field( wp_unslash( $_POST['loszer_caliber_custom'] ?? '' ) );
        $loszer_manufacturer_preset = sanitize_key( wp_unslash( $_POST['loszer_manufacturer_preset'] ?? '' ) );
        $loszer_manufacturer_custom = sanitize_text_field( wp_unslash( $_POST['loszer_manufacturer_custom'] ?? '' ) );
        $loszer_projectile_type = sanitize_key( wp_unslash( $_POST['loszer_projectile_type'] ?? '' ) );
        $loszer_projectile_weight = sanitize_text_field( wp_unslash( $_POST['loszer_projectile_weight'] ?? '' ) );
        $loszer_projectile_weight_unit = sanitize_key( wp_unslash( $_POST['loszer_projectile_weight_unit'] ?? '' ) );
        $loszer_piece_count = sanitize_text_field( wp_unslash( $_POST['loszer_piece_count'] ?? '' ) );
        $loszer_piece_unit = sanitize_key( wp_unslash( $_POST['loszer_piece_unit'] ?? '' ) );
        $loszer_lot_year = sanitize_text_field( wp_unslash( $_POST['loszer_lot_year'] ?? '' ) );
        $loszer_condition = sanitize_key( wp_unslash( $_POST['loszer_condition'] ?? '' ) );
        $loszer_component_case_condition = sanitize_key( wp_unslash( $_POST['loszer_component_case_condition'] ?? '' ) );
        $loszer_powder_type = sanitize_text_field( wp_unslash( $_POST['loszer_powder_type'] ?? '' ) );
        $loszer_powder_sealed = sanitize_key( wp_unslash( $_POST['loszer_powder_sealed'] ?? '' ) );
        $loszer_powder_weight = sanitize_text_field( wp_unslash( $_POST['loszer_powder_weight'] ?? '' ) );
        $loszer_primer_type = sanitize_key( wp_unslash( $_POST['loszer_primer_type'] ?? '' ) );
        $category    = intval( $_POST['category'] ?? 0 );
        $county      = intval( $_POST['county']   ?? 0 );
        $condition   = intval( $_POST['condition'] ?? 0 );

        $category_term = get_term( $category, 'va_category' );
        $category_slug = ( $category_term instanceof \WP_Term && ! is_wp_error( $category_term ) ) ? sanitize_title( (string) $category_term->slug ) : '';
        if ( $category_slug === 'vadasz-felszereles' && $equipment_type === '' ) {
            $equipment_type = 'egyeb';
        }

        if ( $equipment_type === 'egyeb' ) {
            $suggestion_source = strtolower( trim( implode( ' ', [
                (string) $title,
                wp_strip_all_tags( (string) $description ),
                (string) $equipment_notes,
                (string) $other_equipment_function,
                (string) $other_equipment_material_note,
                (string) $other_equipment_compatibility,
                (string) $equipment_weapon_compatibility,
                (string) $light_type,
            ] ) ) );

            if ( $other_uncertain_classification === 'hangtompito-gyanus' || preg_match( '/hangtompito|suppressor|silencer/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'hangtompito';
                $other_auto_suggested_reason = 'Mintafelismeres: hangtompito kulcsszavak';
            } elseif ( preg_match( '/lampa|feny|ir\s|infra|lumen|beam/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'lampa';
                $other_auto_suggested_reason = 'Mintafelismeres: lampa/feny kulcsszavak';
            } elseif ( preg_match( '/taska|tok|hatizsak|molle/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'taska';
                $other_auto_suggested_reason = 'Mintafelismeres: taska/tok kulcsszavak';
            } elseif ( preg_match( '/ruhazat|kabat|nadrag|melleny|bakancs|cipo/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'ruhazat';
                $other_auto_suggested_reason = 'Mintafelismeres: ruhazat kulcsszavak';
            } elseif ( preg_match( '/fegyver|puska|soretes|golyos|marok/', $suggestion_source ) ) {
                $other_auto_suggested_category = 'fegyver';
                $other_auto_suggested_reason = 'Mintafelismeres: fegyver kulcsszavak';
            }
        }

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'A cím kötelező.' ] );
        }

        if ( $location === '' && $postal_code === '' ) {
            wp_send_json_error( [ 'message' => 'Adja meg a várost vagy az irányítószámot.' ] );
        }

        $rule_error = self::validate_category_required_fields( $category, [
            'brand' => $brand,
            'model' => $model,
            'caliber' => $caliber,
            'mixed_type' => $mixed_type,
            'mixed_barrel_count' => $mixed_barrel_count,
            'mixed_rifle_caliber' => $mixed_rifle_caliber,
            'mixed_shotgun_caliber' => $mixed_shotgun_caliber,
            'mixed_condition' => $mixed_condition,
            'rifle_type' => $rifle_type,
            'rifle_caliber' => $rifle_caliber,
            'rifle_barrel_length_cm' => $rifle_barrel_length_cm,
            'rifle_condition' => $rifle_condition,
            'rifle_optic_compatibility' => $rifle_optic_compatibility,
            'other_weapon_kind' => $other_weapon_kind,
            'hatastalanitott_weapon_type' => $hatastalanitott_weapon_type,
            'hatastalanitott_is_deactivated' => $hatastalanitott_is_deactivated,
            'hatastalanitott_deactivation_type' => $hatastalanitott_deactivation_type,
            'hatastalanitott_certificate' => $hatastalanitott_certificate,
            'hatastalanitott_mkh_cip' => $hatastalanitott_mkh_cip,
            'hatastalanitott_condition' => $hatastalanitott_condition,
            'loszer_category' => $loszer_category,
            'loszer_condition' => $loszer_condition,
            'optic_type' => $optic_type,
            'optic_zoom' => $optic_zoom,
            'optic_objective' => $optic_objective,
            'thermal_device_type' => $thermal_device_type,
            'thermal_sensor_resolution' => $thermal_sensor_resolution,
            'thermal_netd' => $thermal_netd,
            'thermal_refresh_hz' => $thermal_refresh_hz,
            'dog_age_months' => $dog_age_months,
            'dog_working_type' => $dog_working_type,
            'dog_breed' => $dog_breed,
            'dog_gender' => $dog_gender,
            'dog_birth_date' => $dog_birth_date,
            'dog_hunting_specialization' => $dog_hunting_specialization,
            'dog_training_level' => $dog_training_level,
            'job_location' => $job_location !== '' ? $job_location : $location,
            'job_type' => $job_type,
            'service_type' => $service_type,
            'service_provider_type' => $service_provider_type,
            'service_country' => $service_country,
            'service_county' => $service_county,
            'service_town' => $service_town,
            'service_area' => $service_area,
            'service_pricing_type' => $service_pricing_type,
            'service_available_weekend' => $service_available_weekend,
            'service_available_24_7' => $service_available_24_7,
            'service_seasonal' => $service_seasonal,
            'service_verified_provider' => $service_verified_provider,
            'service_document_verified' => $service_document_verified,
            'service_phone_verified' => $service_phone_verified,
            'service_business_verified' => $service_business_verified,
            'service_sos_service' => $service_sos_service,
            'service_urgent_repair' => $service_urgent_repair,
            'service_full_prep' => $service_full_prep,
            'service_night_work' => $service_night_work,
            'clothing_type' => $clothing_type,
            'clothing_condition' => $clothing_condition,
            'clothing_gender' => $clothing_gender,
            'clothing_size_system' => $clothing_size_system,
            'clothing_size' => $clothing_size,
            'season' => $season,
            'hunting_usage' => $hunting_usage,
            'waterproof_level' => $waterproof_level,
            'noise_level' => $noise_level,
            'material_tech' => $material_tech,
            'camo_pattern' => $camo_pattern,
            'knife_type' => $knife_type,
            'knife_condition' => $knife_condition,
            'knife_blade_length_mm' => $knife_blade_length_mm,
            'knife_steel_type' => $knife_steel_type,
            'knife_blade_length' => $knife_blade_length,
            'marok_type' => sanitize_key( wp_unslash( $_POST['marok_type'] ?? '' ) ),
            'marok_condition' => sanitize_key( wp_unslash( $_POST['marok_condition'] ?? '' ) ),
            'marok_action_type' => sanitize_key( wp_unslash( $_POST['marok_action_type'] ?? '' ) ),
            'marok_magazine_capacity' => sanitize_text_field( wp_unslash( $_POST['marok_magazine_capacity'] ?? '' ) ),
            'marok_license_required' => sanitize_key( wp_unslash( $_POST['marok_license_required'] ?? '' ) ),
            'marok_legal_category' => sanitize_key( wp_unslash( $_POST['marok_legal_category'] ?? '' ) ),
            'marok_cip_marking' => sanitize_key( wp_unslash( $_POST['marok_cip_marking'] ?? '' ) ),
            'marok_transfer_license_only' => sanitize_key( wp_unslash( $_POST['marok_transfer_license_only'] ?? '' ) ),
            'trophy_species' => $trophy_species,
            'trophy_type' => $trophy_type,
            'trophy_maker' => $trophy_maker,
            'trophy_custom_made' => $trophy_custom_made,
            'trophy_condition' => $trophy_condition,
            'trophy_manufacture_year' => $trophy_manufacture_year,
            'trophy_height_cm' => $trophy_height_cm,
            'trophy_width_cm' => $trophy_width_cm,
            'trophy_thickness_cm' => $trophy_thickness_cm,
            'trophy_compatible_species' => $trophy_compatible_species,
            'trophy_mount_type' => $trophy_mount_type,
            'trophy_mounting_type' => $trophy_mounting_type,
            'trophy_attachment_type' => $trophy_attachment_type,
            'trophy_style' => $trophy_style,
            'trophy_fang_mount' => $trophy_fang_mount,
            'trophy_material_type' => $trophy_material_type,
            'trophy_material' => $trophy_material,
            'trophy_surface_finish' => $trophy_surface_finish,
            'trophy_finish' => $trophy_finish,
            'trophy_weight_kg' => $trophy_weight_kg,
            'trophy_price_negotiable' => $trophy_price_negotiable,
            'trophy_custom_order' => $trophy_custom_order,
            'trophy_fragile_handling' => $trophy_fragile_handling,
            'trophy_house_style' => $trophy_house_style,
            'accommodation_type' => $accommodation_type,
            'accommodation_capacity' => $accommodation_capacity,
            'accommodation_hunting_nearby' => $accommodation_hunting_nearby,
            'accommodation_hunt_available' => $accommodation_hunt_available,
            'accommodation_county_text' => $accommodation_county_text,
            'accommodation_land_size_sqm' => $accommodation_land_size_sqm,
            'accommodation_building_size_sqm' => $accommodation_building_size_sqm,
            'accommodation_utilities' => $accommodation_utilities,
            'accommodation_accessibility' => $accommodation_accessibility,
            'accommodation_hunting_features' => $accommodation_hunting_features,
            'accommodation_property_condition' => $accommodation_property_condition,
            'hunt_slot_from_hour' => $hunt_slot_from_hour,
            'hunt_slot_to_hour' => $hunt_slot_to_hour,
            'hunt_capacity' => $hunt_capacity,
            'hunt_species_list' => $hunt_species_list,
            'hunt_lease_type' => $hunt_lease_type,
            'hunt_type' => $hunt_type,
            'hunt_game_species' => $hunt_game_species,
            'hunt_country' => $hunt_country,
            'hunt_county' => $hunt_county,
            'hunt_price_type' => $hunt_price_type,
            'hunt_price_min' => $hunt_price_min,
            'hunt_methods' => $hunt_methods,
            'hunt_has_accommodation' => $hunt_has_accommodation,
            'hunt_guide_type' => $hunt_guide_type,
            'hunt_difficulty_level' => $hunt_difficulty_level,
            'hunt_season_start' => $hunt_season_start,
            'hunt_season_end' => $hunt_season_end,
            'hunt_damage_main_type' => $hunt_damage_main_type,
            'hunt_settlement' => $hunt_settlement,
            'hunt_land_type' => $hunt_land_type,
            'hunt_response_time' => $hunt_response_time,
            'hunt_availability_mode' => $hunt_availability_mode,
            'hunt_executor_type' => $hunt_executor_type,
            'hunt_executor_capacity' => $hunt_executor_capacity,
            'hunt_tools' => $hunt_tools,
            'hunt_weapon_use_required' => $hunt_weapon_use_required,
            'hunt_land_manager_permit' => $hunt_land_manager_permit,
            'hunt_hunting_company_coop' => $hunt_hunting_company_coop,
            'hunt_liability_insurance' => $hunt_liability_insurance,
            'hunt_damage_nature' => $hunt_damage_nature,
            'hunt_pricing_model' => $hunt_pricing_model,
            'hunt_travel_fee' => $hunt_travel_fee,
            'hunt_contract_type' => $hunt_contract_type,
            'hunt_site_conditions' => $hunt_site_conditions,
            'vadcamera_camera_type' => $vadcamera_camera_type,
            'vadcamera_connectivity' => $vadcamera_connectivity,
            'vadcamera_trigger_speed' => $vadcamera_trigger_speed,
            'vadcamera_ir_type' => $vadcamera_ir_type,
            'vadcamera_night_range_m' => $vadcamera_night_range_m,
            'vadcamera_video_resolution' => $vadcamera_video_resolution,
            'vadcamera_ip_rating' => $vadcamera_ip_rating,
            'vadcamera_sim_slot' => $vadcamera_sim_slot,
            'shoe_main_type' => $shoe_main_type,
            'shoe_eu_size' => $shoe_eu_size,
            'shoe_condition' => $shoe_condition,
            'shoe_boot_height' => $shoe_boot_height,
            'exchange_type' => $exchange_type,
            'exchange_offer_category' => $exchange_offer_category,
            'exchange_brand' => $exchange_brand,
            'exchange_condition' => $exchange_condition,
            'exchange_estimated_value' => $exchange_estimated_value,
            'exchange_extra_payment_direction' => $exchange_extra_payment_direction,
            'exchange_county' => $exchange_county,
            'publication_type' => $publication_type,
            'publication_topics' => $publication_topics,
            'publication_title' => $publication_title,
            'publication_author' => $publication_author,
            'publication_year' => $publication_year,
            'publication_language' => $publication_language,
            'publication_condition' => $publication_condition,
            'publication_collector_value' => $publication_collector_value,
            'publication_complete_volume' => $publication_complete_volume,
            'equipment_type' => $equipment_type,
            'equipment_condition' => $equipment_condition,
            'equipment_hunting_usage' => $equipment_hunting_usage,
            'bag_type' => $bag_type,
            'volume_l' => $volume_l,
            'bag_material' => $bag_material,
            'bag_weather_resistance' => $bag_weather_resistance,
            'bag_condition' => $bag_condition,
            'call_type' => $call_fields['call_type'],
            'call_target_species' => $call_fields['call_target_species'],
            'call_operation_mode' => $call_fields['call_operation_mode'],
            'call_material_type' => $call_fields['call_material_type'],
            'call_volume_level' => $call_fields['call_volume_level'],
            'call_style' => $call_fields['call_style'],
            'decor_type' => $decor_type,
            'decor_material' => $decor_material,
            'decor_style' => $decor_style,
            'decor_motif_tags' => $decor_motif_tags,
            'decor_production_type' => $decor_production_type,
            'decor_size_class' => $decor_size_class,
            'decor_condition' => $decor_condition,
            'estate_main_type' => $estate_main_type,
        ] );
        if ( $rule_error !== '' ) {
            wp_send_json_error( [ 'message' => $rule_error ] );
        }

        $vehicle_error = self::validate_vehicle_required_fields( $category, [
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'mileage' => sanitize_text_field( wp_unslash( $_POST['mileage'] ?? '' ) ),
            'fuel_type' => sanitize_key( wp_unslash( $_POST['fuel_type'] ?? '' ) ),
            'transmission' => sanitize_key( wp_unslash( $_POST['transmission'] ?? '' ) ),
            'drive' => sanitize_key( wp_unslash( $_POST['drive'] ?? '' ) ),
            'vehicle_condition' => sanitize_key( wp_unslash( $_POST['vehicle_condition'] ?? '' ) ),
            'performance_kw' => sanitize_text_field( wp_unslash( $_POST['performance_kw'] ?? '' ) ),
            'engine_size' => sanitize_text_field( wp_unslash( $_POST['engine_size'] ?? '' ) ),
        ] );
        if ( $vehicle_error !== '' ) {
            wp_send_json_error( [ 'message' => $vehicle_error ] );
        }

        $estate_term = $category > 0 ? get_term( $category, 'va_category' ) : null;
        $is_estate_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'vadaszati-hagyatek';
        $estate_moderation_hits = $is_estate_category ? self::detect_estate_moderation_hits( $title, $description, $estate_notes ) : [];
        $is_vadcamera_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'vadkamera';
        $vadcamera_moderation_hits = $is_vadcamera_category ? self::detect_vadcamera_moderation_hits( $title, $description, $vadcamera_notes ) : [];
        $is_shoe_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'cipo-bakancs';
        $shoe_moderation_hits = $is_shoe_category ? self::detect_shoe_moderation_hits( $title, $description, $shoe_notes ) : [];
        $is_knife_category = $estate_term && ! is_wp_error( $estate_term ) && in_array( sanitize_title( (string) $estate_term->slug ), [ 'kesek', 'vadaszkes-vadasztor', 'taktikai-kes-taktikai-tor', 'konyhakes', 'svajci-bicska' ], true );
        $knife_moderation_hits = $is_knife_category ? self::detect_knife_moderation_hits( $title, $description, $knife_moderation_flags ) : [];
        $is_marok_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'maroklofegyver';
        $marok_moderation_hits = $is_marok_category ? self::detect_marok_moderation_hits( $title, $description, $marok_moderation_flags ) : [];
        $is_mixed_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'vegyescsovu-puska';
        $mixed_moderation_hits = $is_mixed_category ? self::detect_mixed_moderation_hits( $title, $description ) : [];
        $is_rifle_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'golyos-puska';
        $rifle_moderation_hits = $is_rifle_category && $rifle_moderation_flags !== '' ? array_filter( array_map( 'trim', explode( ',', $rifle_moderation_flags ) ) ) : [];
        $is_service_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'szolgaltatas';
        $service_moderation_hits = $is_service_category ? self::detect_service_moderation_hits( $title, $description, $service_reference_notes . "\n" . $service_moderation_flags ) : [];
        $is_trophy_category = $estate_term && ! is_wp_error( $estate_term ) && sanitize_title( (string) $estate_term->slug ) === 'trofea-aletet';
        $trophy_moderation_hits = $is_trophy_category ? self::detect_trophy_moderation_hits( $title, $description, $trophy_moderation_flags . "\n" . $trophy_maker ) : [];
        $is_hunt_category = $estate_term && ! is_wp_error( $estate_term ) && in_array( sanitize_title( (string) $estate_term->slug ), [ 'vadaszati-lehetoseg', 'vadkarelharitas' ], true );
        $hunt_moderation_hits = $is_hunt_category ? self::detect_hunt_moderation_hits( $title, $description, $hunt_moderation_flags . "\n" . $hunt_area_name . "\n" . $hunt_game_species ) : [];

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

        if ( class_exists( 'VA_User_Roles' ) && ! empty( $plan_check['can'] ) ) {
            $plan_limit = isset( $plan_check['plan_limit'] ) ? (int) $plan_check['plan_limit'] : 0;
            $used_slots  = isset( $plan_check['used'] ) ? (int) $plan_check['used'] : 0;
            $gift_slots  = isset( $plan_check['credits_total'] ) ? (int) $plan_check['credits_total'] : 0;

            if ( $plan_limit > 0 && $used_slots >= $plan_limit && $gift_slots > 0 ) {
                VA_User_Roles::consume_gift_credit( $user_id );
            }
        }

        // Meta mentés
        $metas = [
            'va_price'       => $price,
            'va_price_type'  => $price_type,
            'va_phone'       => $phone,
            'va_location'    => $location,
            'va_postal_code' => $postal_code,
            'va_street'      => $street,
            'va_other_category' => $other_category,
            'va_email_show'  => '1',
            'va_brand'       => $brand,
            'va_model'       => $model,
            'va_caliber'     => $caliber,
            'va_year'        => $year,
            'va_license_req' => $license_req,
            'va_mixed_type' => $mixed_type,
            'va_mixed_barrel_count' => $mixed_barrel_count,
            'va_mixed_has_rifle' => $mixed_has_rifle,
            'va_mixed_rifle_caliber' => $mixed_rifle_caliber,
            'va_mixed_has_shotgun' => $mixed_has_shotgun,
            'va_mixed_shotgun_caliber' => $mixed_shotgun_caliber,
            'va_mixed_trigger_type' => $mixed_trigger_type,
            'va_mixed_action_system' => $mixed_action_system,
            'va_mixed_lock_type' => $mixed_lock_type,
            'va_mixed_sight_type' => $mixed_sight_type,
            'va_mixed_optic_rail' => $mixed_optic_rail,
            'va_mixed_optic_compatible' => $mixed_optic_compatible,
            'va_mixed_barrel_length_cm' => $mixed_barrel_length_cm,
            'va_mixed_barrel_material' => $mixed_barrel_material,
            'va_mixed_barrel_regulated' => $mixed_barrel_regulated,
            'va_mixed_material' => $mixed_material,
            'va_mixed_stock_material' => $mixed_stock_material,
            'va_mixed_usage' => $mixed_usage,
            'va_mixed_weight_kg' => $mixed_weight_kg,
            'va_mixed_total_length_cm' => $mixed_total_length_cm,
            'va_mixed_accessories' => $mixed_accessories,
            'va_mixed_rifle_moa' => $mixed_rifle_moa,
            'va_mixed_shot_pattern' => $mixed_shot_pattern,
            'va_mixed_zero_distance_m' => $mixed_zero_distance_m,
            'va_mixed_safety_type' => $mixed_safety_type,
            'va_mixed_barrel_layout' => $mixed_barrel_layout,
            'va_mixed_barrel_internal' => $mixed_barrel_internal,
            'va_mixed_engraving' => $mixed_engraving,
            'va_mixed_factory_marking' => $mixed_factory_marking,
            'va_mixed_drilling_barrel1_caliber' => $mixed_drilling_barrel1_caliber,
            'va_mixed_drilling_top_barrel_role' => $mixed_drilling_top_barrel_role,
            'va_mixed_optic_mount_type' => $mixed_optic_mount_type,
            'va_mixed_mountain_weight_optimized' => $mixed_mountain_weight_optimized,
            'va_mixed_universal_label' => $mixed_universal_label,
            'va_mixed_condition' => $mixed_condition,
            'va_mixed_filter_type' => $mixed_type,
            'va_mixed_filter_rifle_caliber' => $mixed_rifle_caliber,
            'va_mixed_filter_shotgun_caliber' => $mixed_shotgun_caliber,
            'va_mixed_filter_barrel_count' => $mixed_barrel_count,
            'va_mixed_filter_brand' => sanitize_title( $brand ),
            'va_mixed_filter_condition' => $mixed_condition,
            'va_mixed_filter_optic' => $mixed_filter_optic,
            'va_mixed_filter_universal' => $mixed_filter_universal,
            'va_mixed_needs_review' => ! empty( $mixed_moderation_hits ) ? '1' : '0',
            'va_mixed_review_hits' => implode( ',', $mixed_moderation_hits ),
            'va_rifle_type' => $rifle_type,
            'va_rifle_caliber' => $rifle_caliber,
            'va_rifle_barrel_length_cm' => $rifle_barrel_length_cm,
            'va_rifle_barrel_material' => $rifle_barrel_material,
            'va_rifle_rifling_type' => $rifle_rifling_type,
            'va_rifle_barrel_condition' => $rifle_barrel_condition,
            'va_rifle_accuracy_moa' => $rifle_accuracy_moa,
            'va_rifle_zero_distance_m' => $rifle_zero_distance_m,
            'va_rifle_factory_group' => $rifle_factory_group,
            'va_rifle_action_type' => $rifle_action_type,
            'va_rifle_trigger_type' => $rifle_trigger_type,
            'va_rifle_safety_type' => $rifle_safety_type,
            'va_rifle_stock_material' => $rifle_stock_material,
            'va_rifle_optic_rail_type' => $rifle_optic_rail_type,
            'va_rifle_optic_compatibility' => $rifle_optic_compatibility,
            'va_rifle_zero_return' => $rifle_zero_return,
            'va_rifle_accessories' => $rifle_accessories,
            'va_rifle_weight_kg' => $rifle_weight_kg,
            'va_rifle_total_length_cm' => $rifle_total_length_cm,
            'va_rifle_usage' => $rifle_usage,
            'va_rifle_special_build' => $rifle_special_build,
            'va_rifle_barrel_mount_type' => $rifle_barrel_mount_type,
            'va_rifle_impact_safe' => $rifle_impact_safe,
            'va_rifle_closed_system' => $rifle_closed_system,
            'va_rifle_effective_range_m' => $rifle_effective_range_m,
            'va_rifle_stability_level' => $rifle_stability_level,
            'va_rifle_recoil_level' => $rifle_recoil_level,
            'va_rifle_temperament' => $rifle_temperament,
            'va_rifle_condition' => $rifle_condition,
            'va_rifle_mountain_weight_optimized' => $rifle_mountain_weight_optimized,
            'va_rifle_magazine_capacity' => $rifle_magazine_capacity,
            'va_rifle_moderation_flags' => $rifle_moderation_flags,
            'va_rifle_hunter_profile_game' => $rifle_hunter_profile_game,
            'va_rifle_hunter_profile_optic' => $rifle_hunter_profile_optic,
            'va_rifle_hunter_profile_distance' => $rifle_hunter_profile_distance,
            'va_rifle_filter_type' => $rifle_type,
            'va_rifle_filter_caliber' => $rifle_caliber_primary,
            'va_rifle_filter_brand' => sanitize_title( $brand ),
            'va_rifle_filter_barrel_length_cm' => $rifle_barrel_length_cm,
            'va_rifle_filter_condition' => $rifle_condition,
            'va_rifle_filter_optic' => $rifle_filter_optic,
            'va_rifle_filter_action_type' => $rifle_action_type,
            'va_rifle_needs_review' => ! empty( $rifle_moderation_hits ) ? '1' : '0',
            'va_rifle_review_hits' => implode( ',', $rifle_moderation_hits ),
            'va_marok_type' => $marok_type,
            'va_marok_condition' => $marok_condition,
            'va_marok_manufacture_year' => $marok_manufacture_year,
            'va_marok_origin_country' => $marok_origin_country,
            'va_marok_action_type' => $marok_action_type,
            'va_marok_magazine_capacity' => $marok_magazine_capacity,
            'va_marok_cylinder_capacity' => $marok_cylinder_capacity,
            'va_marok_revolver_action' => $marok_revolver_action,
            'va_marok_barrel_length_mm' => $marok_barrel_length_mm,
            'va_marok_total_length_mm' => $marok_total_length_mm,
            'va_marok_weight_g' => $marok_weight_g,
            'va_marok_frame_material' => $marok_frame_material,
            'va_marok_slide_material' => $marok_slide_material,
            'va_marok_sight_type' => $marok_sight_type,
            'va_marok_optic_ready' => $marok_optic_ready,
            'va_marok_optic_mount' => $marok_optic_mount,
            'va_marok_red_dot_compatibility' => $marok_red_dot_compatibility,
            'va_marok_threaded_barrel' => $marok_threaded_barrel,
            'va_marok_rail_type' => $marok_rail_type,
            'va_marok_accessories' => $marok_accessories,
            'va_marok_license_required' => $marok_license_required,
            'va_marok_legal_category' => $marok_legal_category,
            'va_marok_technical_exam_valid' => $marok_technical_exam_valid,
            'va_marok_cip_marking' => $marok_cip_marking,
            'va_marok_transfer_license_only' => $marok_transfer_license_only,
            'va_marok_personal_pickup' => $marok_personal_pickup,
            'va_marok_serial_masked' => $marok_serial_masked,
            'va_marok_usage' => $marok_usage,
            'va_marok_price_deal' => $marok_price_deal,
            'va_marok_price_exchange' => $marok_price_exchange,
            'va_marok_transfer_methods' => $marok_transfer_methods,
            'va_marok_required_media' => $marok_required_media,
            'va_marok_recommended_media' => $marok_recommended_media,
            'va_marok_round_count' => $marok_round_count,
            'va_marok_trigger_tuning' => $marok_trigger_tuning,
            'va_marok_sport_categories' => $marok_sport_categories,
            'va_marok_ipsc_ready' => $marok_ipsc_ready,
            'va_marok_race_trigger' => $marok_race_trigger,
            'va_marok_moderation_flags' => $marok_moderation_flags,
            'va_marok_filter_caliber' => $caliber,
            'va_marok_filter_barrel_length_mm' => $marok_barrel_length_mm,
            'va_marok_filter_magazine_capacity' => $marok_filter_magazine_capacity,
            'va_marok_filter_action_type' => $marok_action_type,
            'va_marok_filter_optic_ready' => $marok_filter_optic_ready,
            'va_marok_filter_threaded_barrel' => $marok_filter_threaded_barrel,
            'va_marok_filter_frame_material' => $marok_frame_material,
            'va_marok_needs_review' => ! empty( $marok_moderation_hits ) ? '1' : '0',
            'va_marok_review_hits' => implode( ',', $marok_moderation_hits ),
            'va_optic_type'  => $optic_type,
            'va_optic_zoom'  => $optic_zoom,
            'va_optic_objective' => $optic_objective,
            'va_thermal_device_type' => $thermal_device_type,
            'va_thermal_condition' => $thermal_condition,
            'va_thermal_warranty' => $thermal_warranty,
            'va_thermal_manufacture_year' => $thermal_manufacture_year,
            'va_thermal_sensor_resolution' => $thermal_sensor_resolution,
            'va_thermal_pixel_pitch' => $thermal_pixel_pitch,
            'va_thermal_netd' => $thermal_netd,
            'va_thermal_refresh_hz' => $thermal_refresh_hz,
            'va_thermal_base_magnification' => $thermal_base_magnification,
            'va_thermal_digital_zoom' => $thermal_digital_zoom,
            'va_thermal_lens_size' => $thermal_lens_size,
            'va_thermal_fov_degree' => $thermal_fov_degree,
            'va_thermal_fov_m100' => $thermal_fov_m100,
            'va_thermal_detect_human_m' => $thermal_detect_human_m,
            'va_thermal_detect_game_m' => $thermal_detect_game_m,
            'va_thermal_detect_vehicle_m' => $thermal_detect_vehicle_m,
            'va_thermal_display_type' => $thermal_display_type,
            'va_thermal_display_resolution' => $thermal_display_resolution,
            'va_thermal_palettes' => $thermal_palettes,
            'va_thermal_photo' => $thermal_photo,
            'va_thermal_video' => $thermal_video,
            'va_thermal_audio' => $thermal_audio,
            'va_thermal_internal_memory_gb' => $thermal_internal_memory_gb,
            'va_thermal_sd_support' => $thermal_sd_support,
            'va_thermal_connectivity' => $thermal_connectivity,
            'va_thermal_mobile_app' => $thermal_mobile_app,
            'va_thermal_extra_features' => $thermal_extra_features,
            'va_thermal_battery_type' => $thermal_battery_type,
            'va_thermal_runtime_hours' => $thermal_runtime_hours,
            'va_thermal_powerbank_compatible' => $thermal_powerbank_compatible,
            'va_thermal_ip_rating' => $thermal_ip_rating,
            'va_thermal_recoil_resistance' => $thermal_recoil_resistance,
            'va_thermal_weapon_compatibility' => $thermal_weapon_compatibility,
            'va_thermal_accessories' => $thermal_accessories,
            'va_dog_age_months' => $dog_age_months,
            'va_dog_working_type' => $dog_working_type,
            'va_dog_breed'   => $dog_breed,
            'va_dog_name' => $dog_name,
            'va_dog_gender'  => $dog_gender,
            'va_dog_breeder_name' => $dog_breeder_name,
            'va_dog_kennel_name' => $dog_kennel_name,
            'va_dog_pedigree_status' => $dog_pedigree_status,
            'va_dog_origin_country' => $dog_origin_country,
            'va_dog_bloodline_type' => $dog_bloodline_type,
            'va_dog_documents' => $dog_documents,
            'va_dog_birth_date' => $dog_birth_date,
            'va_dog_color'   => $dog_color,
            'va_dog_appearance_type' => $dog_appearance_type,
            'va_dog_purebred'=> $dog_purebred,
            'va_dog_training_skills' => $dog_training_skills,
            'va_dog_training_level' => $dog_training_level,
            'va_dog_exam_level' => $dog_exam_level,
            'va_dog_work_exam_type' => $dog_work_exam_type,
            'va_dog_hunting_specialization' => $dog_hunting_specialization,
            'va_dog_work_style' => $dog_work_style,
            'va_dog_search_radius_m' => $dog_search_radius_m,
            'va_dog_endurance_level' => $dog_endurance_level,
            'va_dog_aggression_level' => $dog_aggression_level,
            'va_dog_apport_stability' => $dog_apport_stability,
            'va_dog_water_work_ability' => $dog_water_work_ability,
            'va_dog_water_work' => $dog_water_work,
            'va_dog_water_work_level' => $dog_water_work_level,
            'va_dog_cold_water_tolerance' => $dog_cold_water_tolerance,
            'va_dog_temperament' => $dog_temperament,
            'va_dog_environment' => $dog_environment,
            'va_dog_allergic' => $dog_allergic,
            'va_dog_special_diet' => $dog_special_diet,
            'va_dog_known_diseases' => $dog_known_diseases,
            'va_dog_hip_dysplasia_screening' => $dog_hip_dysplasia_screening,
            'va_dog_height_cm' => $dog_height_cm,
            'va_dog_weight_kg' => $dog_weight_kg,
            'va_dog_country' => $dog_country,
            'va_dog_county' => $dog_county,
            'va_dog_city' => $dog_city,
            'va_dog_origin_qualification' => $dog_origin_qualification,
            'va_dog_litter_count' => $dog_litter_count,
            'va_dog_available_from' => $dog_available_from,
            'va_dog_tracking_distance_m' => $dog_tracking_distance_m,
            'va_dog_tracking_experience' => $dog_tracking_experience,
            'va_dog_moderation_flags' => $dog_moderation_flags,
            'va_dog_work_video_types' => $dog_work_video_types,
            'va_dog_work_video_url' => $dog_work_video_url,
            'va_dog_filter_breed' => $dog_breed,
            'va_dog_filter_specialization' => $dog_hunting_specialization,
            'va_dog_filter_training_level' => $dog_training_level,
            'va_dog_filter_working_type' => $dog_working_type,
            'va_dog_filter_pedigree' => $dog_filter_pedigree,
            'va_dog_filter_bloodline' => $dog_bloodline_type,
            'va_dog_filter_age_months' => $dog_age_months,
            'va_dog_filter_has_work_exam' => $dog_filter_has_work_exam,
            'va_dog_filter_water_work' => $dog_filter_water_work,
            'va_dog_review_hits' => implode( ',', $dog_review_hits ),
            'va_job_location' => $job_location,
            'va_job_type'   => $job_type,
            'va_service_type' => $service_type,
            'va_service_provider_type' => $service_provider_type,
            'va_service_provider_name' => $service_provider_name,
            'va_service_country' => $service_country,
            'va_service_county' => $service_county,
            'va_service_town' => $service_town,
            'va_service_area' => $service_area,
            'va_service_gps_lat' => $service_gps_lat,
            'va_service_gps_lng' => $service_gps_lng,
            'va_service_pricing_type' => $service_pricing_type,
            'va_service_min_price' => $service_min_price,
            'va_service_travel_fee' => $service_travel_fee,
            'va_service_schedule' => $service_schedule,
            'va_service_available_weekend' => $service_available_weekend,
            'va_service_available_24_7' => $service_available_24_7,
            'va_service_seasonal' => $service_seasonal,
            'va_service_hunting_specialization' => $service_hunting_specialization,
            'va_service_hunting_species' => $service_hunting_species,
            'va_service_weapon_categories' => $service_weapon_categories,
            'va_service_equipment' => $service_equipment,
            'va_service_permits' => $service_permits,
            'va_service_reference_rating' => $service_reference_rating,
            'va_service_completed_jobs' => $service_completed_jobs,
            'va_service_reference_notes' => $service_reference_notes,
            'va_service_media_required' => $service_media_required,
            'va_service_media_video' => $service_media_video,
            'va_service_verified_provider' => $service_verified_provider,
            'va_service_document_verified' => $service_document_verified,
            'va_service_phone_verified' => $service_phone_verified,
            'va_service_business_verified' => $service_business_verified,
            'va_service_sos_service' => $service_sos_service,
            'va_service_urgent_repair' => $service_urgent_repair,
            'va_service_full_prep' => $service_full_prep,
            'va_service_night_work' => $service_night_work,
            'va_service_hound_breed' => $service_hound_breed,
            'va_service_caliber_support' => $service_caliber_support,
            'va_service_moderation_flags' => $service_moderation_flags,
            'va_service_needs_review' => ! empty( $service_moderation_hits ) ? '1' : '0',
            'va_service_review_hits' => implode( ',', $service_moderation_hits ),
            'va_service_filter_type' => $service_type,
            'va_service_filter_area' => $service_area,
            'va_service_filter_pricing_type' => $service_pricing_type,
            'va_service_filter_available_weekend' => $service_available_weekend,
            'va_service_filter_verified_provider' => $service_verified_provider,
            'va_service_filter_hunting_specialization' => $service_hunting_specialization,
            'va_service_filter_sos_service' => $service_sos_service,
            'va_clothing_type' => $clothing_type,
            'va_clothing_condition' => $clothing_condition,
            'va_clothing_gender' => $clothing_gender,
            'va_clothing_size_system' => $clothing_size_system,
            'va_clothing_size' => $clothing_size,
            'va_clothing_accessory_size' => $clothing_accessory_size,
            'va_clothing_glove_size' => $clothing_glove_size,
            'va_clothing_hat_size' => $clothing_hat_size,
            'va_clothing_hat_cm' => $clothing_hat_cm,
            'va_clothing_belt_length_cm' => $clothing_belt_length_cm,
            'va_clothing_colors' => $clothing_colors,
            'va_clothing_materials' => $clothing_materials,
            'va_clothing_protections' => $clothing_protections,
            'va_clothing_season' => $clothing_season,
            'va_clothing_use_cases' => $clothing_use_cases,
            'va_clothing_special_features' => $clothing_special_features,
            'va_clothing_glove_features' => $clothing_glove_features,
            'va_clothing_headwear_features' => $clothing_headwear_features,
            'va_clothing_poncho_water_column' => $clothing_poncho_water_column,
            'va_clothing_poncho_ultralight' => $clothing_poncho_ultralight,
            'va_clothing_poncho_backpack_compatible' => $clothing_poncho_backpack_compatible,
            'va_clothing_ghillie_type' => $clothing_ghillie_type,
            'va_clothing_ghillie_pattern' => $clothing_ghillie_pattern,
            'va_season' => $season,
            'va_hunting_usage' => $hunting_usage,
            'va_noise_level' => $noise_level,
            'va_material_tech' => $material_tech,
            'va_waterproof_level' => $waterproof_level,
            'va_camo_pattern' => $camo_pattern,
            'va_silence_index' => $silence_index,
            'va_clothing_weather_protection' => $clothing_weather_protection,
            'va_clothing_functional_details' => $clothing_functional_details,
            'va_clothing_fit' => $clothing_fit,
            'va_clothing_size_fit' => $clothing_size_fit,
            'va_clothing_hunter_features' => $clothing_hunter_features,
            'va_clothing_gear_compatibility' => $clothing_gear_compatibility,
            'va_clothing_durability' => $clothing_durability,
            'va_clothing_layering' => $clothing_layering,
            'va_clothing_membrane_type' => $clothing_membrane_type,
            'va_clothing_ir_reduction' => $clothing_ir_reduction,
            'va_clothing_filter_type' => $clothing_type,
            'va_clothing_filter_size' => $clothing_size,
            'va_clothing_filter_thermo' => $clothing_filter_thermo,
            'va_clothing_filter_waterproof' => $clothing_filter_waterproof,
            'va_clothing_filter_camo' => $clothing_filter_camo,
            'va_clothing_filter_silent' => $clothing_filter_silent,
            'va_clothing_filter_season' => $clothing_season,
            'va_clothing_filter_brand' => $brand,
            'va_clothing_filter_hunting_usage' => $clothing_filter_hunting_usage,
            'va_clothing_filter_noise_level' => $clothing_filter_noise_level,
            'va_clothing_filter_material_tech' => $clothing_filter_material_tech,
            'va_clothing_filter_camo_pattern' => $clothing_filter_camo_pattern,
            'va_clothing_filter_season_mode' => $clothing_filter_season_mode,
            'va_knife_type' => $knife_type,
            'va_knife_condition' => $knife_condition,
            'va_knife_manufacture_year' => $knife_manufacture_year,
            'va_knife_total_length_cm' => $knife_total_length_cm,
            'va_knife_blade_length_mm' => $knife_blade_length_mm,
            'va_knife_blade_thickness_mm' => $knife_blade_thickness_mm,
            'va_knife_weight_g' => $knife_weight_g,
            'va_knife_steel_type' => $knife_steel_type,
            'va_knife_blade_shape' => $knife_blade_shape,
            'va_knife_blade_edge' => $knife_blade_edge,
            'va_knife_finish' => $knife_finish,
            'va_knife_construction' => $knife_construction,
            'va_knife_locking_type' => $knife_locking_type,
            'va_knife_one_hand_opening' => $knife_one_hand_opening,
            'va_knife_handle_material' => $knife_handle_material,
            'va_knife_handle_color' => $knife_handle_color,
            'va_knife_has_sheath' => $knife_has_sheath,
            'va_knife_sheath_type' => $knife_sheath_type,
            'va_knife_belt_carry' => $knife_belt_carry,
            'va_knife_usage' => $knife_usage,
            'va_knife_features' => $knife_features,
            'va_knife_age_restriction' => $knife_age_restriction,
            'va_knife_restricted' => $knife_restricted,
            'va_knife_public_carry' => $knife_public_carry,
            'va_knife_accessories' => $knife_accessories,
            'va_knife_price_deal' => $knife_price_deal,
            'va_knife_price_exchange' => $knife_price_exchange,
            'va_knife_shipping_methods' => $knife_shipping_methods,
            'va_knife_required_media' => $knife_required_media,
            'va_knife_sharpening_state' => $knife_sharpening_state,
            'va_knife_collectible_flags' => $knife_collectible_flags,
            'va_knife_moderation_flags' => $knife_moderation_flags,
            'va_knife_axe_head_weight_g' => $knife_axe_head_weight_g,
            'va_knife_axe_handle_material' => $knife_axe_handle_material,
            'va_knife_multitool_function_count' => $knife_multitool_function_count,
            'va_knife_filter_type' => $knife_filter_type,
            'va_knife_filter_blade_length_mm' => $knife_blade_length_mm,
            'va_knife_filter_steel_type' => $knife_steel_type,
            'va_knife_filter_locking_type' => $knife_locking_type,
            'va_knife_filter_handle_material' => $knife_handle_material,
            'va_knife_filter_blade_shape' => $knife_blade_shape,
            'va_knife_filter_full_tang' => $knife_filter_full_tang,
            'va_knife_filter_bushcraft' => $knife_filter_bushcraft,
            'va_knife_filter_vadasz' => $knife_filter_vadasz,
            'va_knife_filter_condition' => $knife_condition,
            'va_knife_needs_review' => ! empty( $knife_moderation_hits ) ? '1' : '0',
            'va_knife_review_hits' => implode( ',', $knife_moderation_hits ),
            'va_knife_blade_length' => $knife_blade_length,
            'va_trophy_species' => $trophy_species,
            'va_trophy_type' => $trophy_type,
            'va_trophy_maker' => $trophy_maker,
            'va_trophy_custom_made' => $trophy_custom_made,
            'va_trophy_condition' => $trophy_condition,
            'va_trophy_manufacture_year' => $trophy_manufacture_year,
            'va_trophy_height_cm' => $trophy_height_cm,
            'va_trophy_width_cm' => $trophy_width_cm,
            'va_trophy_thickness_cm' => $trophy_thickness_cm,
            'va_trophy_compatible_species' => $trophy_compatible_species,
            'va_trophy_mount_type' => $trophy_mount_type,
            'va_trophy_mounting_type' => $trophy_mounting_type,
            'va_trophy_attachment_type' => $trophy_attachment_type,
            'va_trophy_style' => $trophy_style,
            'va_trophy_fang_mount' => $trophy_fang_mount,
            'va_trophy_material_type' => $trophy_material_type,
            'va_trophy_material' => $trophy_material,
            'va_trophy_surface_finish' => $trophy_surface_finish,
            'va_trophy_finish' => $trophy_finish,
            'va_trophy_decoration_flags' => $trophy_decoration_flags,
            'va_trophy_weight_kg' => $trophy_weight_kg,
            'va_trophy_usage_context' => $trophy_usage_context,
            'va_trophy_accessories' => $trophy_accessories,
            'va_trophy_price_negotiable' => $trophy_price_negotiable,
            'va_trophy_custom_order' => $trophy_custom_order,
            'va_trophy_shipping_methods' => $trophy_shipping_methods,
            'va_trophy_fragile_handling' => $trophy_fragile_handling,
            'va_trophy_required_media' => $trophy_required_media,
            'va_trophy_lead_time' => $trophy_lead_time,
            'va_trophy_orderable_sizes' => $trophy_orderable_sizes,
            'va_trophy_custom_engraving' => $trophy_custom_engraving,
            'va_trophy_house_style' => $trophy_house_style,
            'va_trophy_moderation_flags' => $trophy_moderation_flags,
            'va_trophy_needs_review' => ! empty( $trophy_moderation_hits ) ? '1' : '0',
            'va_trophy_review_hits' => implode( ',', $trophy_moderation_hits ),
            'va_trophy_filter_species' => $trophy_compatible_species,
            'va_trophy_filter_material_type' => $trophy_material_type,
            'va_trophy_filter_height_cm' => $trophy_height_cm,
            'va_trophy_filter_width_cm' => $trophy_width_cm,
            'va_trophy_filter_thickness_cm' => $trophy_thickness_cm,
            'va_trophy_filter_style' => $trophy_style,
            'va_trophy_filter_wall_mount' => ( $trophy_mounting_type === 'falra-szerelheto' || $trophy_attachment_type === 'falra-szerelheto' ) ? '1' : '0',
            'va_trophy_filter_handmade' => $trophy_custom_made === 'igen' ? '1' : '0',
            'va_accommodation_type' => $accommodation_type,
            'va_accommodation_capacity' => $accommodation_capacity,
            'va_accommodation_slot_mode' => $accommodation_slot_mode,
            'va_accommodation_from_hour' => $accommodation_from_hour,
            'va_accommodation_to_hour' => $accommodation_to_hour,
            'va_accommodation_checkin_from' => $accommodation_checkin_from,
            'va_accommodation_checkin_to' => $accommodation_checkin_to,
            'va_accommodation_checkout_from' => $accommodation_checkout_from,
            'va_accommodation_checkout_to' => $accommodation_checkout_to,
            'va_accommodation_meal_type' => $accommodation_meal_type,
            'va_accommodation_parking' => $accommodation_parking,
            'va_accommodation_settlement' => $accommodation_settlement,
            'va_accommodation_county_text' => $accommodation_county_text,
            'va_accommodation_country' => $accommodation_country,
            'va_accommodation_exact_address' => $accommodation_exact_address,
            'va_accommodation_adult_capacity' => $accommodation_adult_capacity,
            'va_accommodation_room_count' => $accommodation_room_count,
            'va_accommodation_bed_count' => $accommodation_bed_count,
            'va_accommodation_extra_bed' => $accommodation_extra_bed,
            'va_accommodation_hunting_nearby' => $accommodation_hunting_nearby,
            'va_accommodation_hunt_available' => $accommodation_hunt_available,
            'va_accommodation_trophy_handling' => $accommodation_trophy_handling,
            'va_accommodation_cold_room' => $accommodation_cold_room,
            'va_accommodation_gun_storage' => $accommodation_gun_storage,
            'va_accommodation_game_processing' => $accommodation_game_processing,
            'va_accommodation_offroad_parking' => $accommodation_offroad_parking,
            'va_accommodation_dog_allowed' => $accommodation_dog_allowed,
            'va_accommodation_features' => $accommodation_features,
            'va_accommodation_min_nights' => $accommodation_min_nights,
            'va_accommodation_bookable_period' => $accommodation_bookable_period,
            'va_accommodation_instant_book' => $accommodation_instant_book,
            'va_accommodation_nearby_species' => $accommodation_nearby_species,
            'va_accommodation_programs' => $accommodation_programs,
            'va_accommodation_environment' => $accommodation_environment,
            'va_accommodation_land_size_sqm' => $accommodation_land_size_sqm,
            'va_accommodation_land_size_hectare' => $accommodation_land_size_hectare,
            'va_accommodation_building_size_sqm' => $accommodation_building_size_sqm,
            'va_accommodation_levels_count' => $accommodation_levels_count,
            'va_accommodation_build_year' => $accommodation_build_year,
            'va_accommodation_property_condition' => $accommodation_property_condition,
            'va_accommodation_construction_type' => $accommodation_construction_type,
            'va_accommodation_heating_type' => $accommodation_heating_type,
            'va_accommodation_utilities' => $accommodation_utilities,
            'va_accommodation_accessibility' => $accommodation_accessibility,
            'va_accommodation_hunting_features' => $accommodation_hunting_features,
            'va_accommodation_extras' => $accommodation_extras,
            'va_accommodation_network_features' => $accommodation_network_features,
            'va_accommodation_use_type' => $accommodation_use_type,
            'va_accommodation_terrain_type' => $accommodation_terrain_type,
            'va_accommodation_animal_housing' => $accommodation_animal_housing,
            'va_accommodation_fafaj' => $accommodation_fafaj,
            'va_accommodation_erdo_kora' => $accommodation_erdo_kora,
            'va_accommodation_vadaszhaz_trofeaszoba' => $accommodation_vadaszhaz_trofeaszoba,
            'va_accommodation_vadaszhaz_fegyvertarolo' => $accommodation_vadaszhaz_fegyvertarolo,
            'va_accommodation_tanya_gazdasagi_epuletek' => $accommodation_tanya_gazdasagi_epuletek,
            'va_accommodation_hunting_potential_wild_movement' => $accommodation_hunting_potential_wild_movement,
            'va_accommodation_hunting_potential_isolation' => $accommodation_hunting_potential_isolation,
            'va_accommodation_hunting_potential_forest_proximity' => $accommodation_hunting_potential_forest_proximity,
            'va_accommodation_hunting_potential_infrastructure' => $accommodation_hunting_potential_infrastructure,
            'va_accommodation_filter_property_type' => $accommodation_filter_property_type,
            'va_accommodation_filter_county' => $accommodation_filter_county,
            'va_accommodation_filter_land_size_sqm' => $accommodation_filter_land_size_sqm,
            'va_accommodation_filter_building_size_sqm' => $accommodation_filter_building_size_sqm,
            'va_accommodation_filter_utilities' => $accommodation_filter_utilities,
            'va_accommodation_filter_accessibility' => $accommodation_filter_accessibility,
            'va_accommodation_filter_hunting_features' => $accommodation_filter_hunting_features,
            'va_accommodation_filter_condition' => $accommodation_filter_condition,
            'va_hunt_slot_from_hour' => $hunt_slot_from_hour,
            'va_hunt_slot_to_hour' => $hunt_slot_to_hour,
            'va_hunt_capacity' => $hunt_capacity,
            'va_hunt_species_list' => $hunt_species_list,
            'va_hunt_lease_type' => $hunt_lease_type,
            'va_hunt_has_guide' => $hunt_has_guide,
            'va_hunt_has_tracking' => $hunt_has_tracking,
            'va_hunt_has_trophy_prep' => $hunt_has_trophy_prep,
            'va_hunt_has_trophy_judging' => $hunt_has_trophy_judging,
            'va_hunt_has_offroad' => $hunt_has_offroad,
            'va_hunt_has_accommodation' => $hunt_has_accommodation,
            'va_hunt_can_buy_game' => $hunt_can_buy_game,
            'va_hunt_type' => $hunt_type,
            'va_hunt_game_species' => $hunt_game_species,
            'va_hunt_country' => $hunt_country,
            'va_hunt_county' => $hunt_county,
            'va_hunt_area_name' => $hunt_area_name,
            'va_hunt_exact_area' => $hunt_exact_area,
            'va_hunt_gps_lat' => $hunt_gps_lat,
            'va_hunt_gps_lng' => $hunt_gps_lng,
            'va_hunt_terrain_types' => $hunt_terrain_types,
            'va_hunt_season_start' => $hunt_season_start,
            'va_hunt_season_end' => $hunt_season_end,
            'va_hunt_time_type' => $hunt_time_type,
            'va_hunt_price_type' => $hunt_price_type,
            'va_hunt_price_min' => $hunt_price_min,
            'va_hunt_price_max' => $hunt_price_max,
            'va_hunt_price_includes' => $hunt_price_includes,
            'va_hunt_guide_type' => $hunt_guide_type,
            'va_hunt_methods' => $hunt_methods,
            'va_hunt_onsite_services' => $hunt_onsite_services,
            'va_hunt_hunting_license_required' => $hunt_hunting_license_required,
            'va_hunt_permit_required' => $hunt_permit_required,
            'va_hunt_foreigners_allowed' => $hunt_foreigners_allowed,
            'va_hunt_min_age' => $hunt_min_age,
            'va_hunt_safety_features' => $hunt_safety_features,
            'va_hunt_conditions' => $hunt_conditions,
            'va_hunt_difficulty_level' => $hunt_difficulty_level,
            'va_hunt_expected_trophy_quality' => $hunt_expected_trophy_quality,
            'va_hunt_trophy_weight_age' => $hunt_trophy_weight_age,
            'va_hunt_required_media' => $hunt_required_media,
            'va_hunt_video_types' => $hunt_video_types,
            'va_hunt_moderation_flags' => $hunt_moderation_flags,
            'va_hunt_trophy_category' => $hunt_trophy_category,
            'va_hunt_trophy_weight_limit' => $hunt_trophy_weight_limit,
            'va_hunt_travel_assist' => $hunt_travel_assist,
            'va_hunt_interpreter' => $hunt_interpreter,
            'va_hunt_group_size' => $hunt_group_size,
            'va_hunt_shot_limit' => $hunt_shot_limit,
            'va_hunt_real_time_slots' => $hunt_real_time_slots,
            'va_hunt_bookable_dates' => $hunt_bookable_dates,
            'va_hunt_damage_main_type' => $hunt_damage_main_type,
            'va_hunt_settlement' => $hunt_settlement,
            'va_hunt_land_type' => $hunt_land_type,
            'va_hunt_response_time' => $hunt_response_time,
            'va_hunt_availability_mode' => $hunt_availability_mode,
            'va_hunt_executor_type' => $hunt_executor_type,
            'va_hunt_executor_capacity' => $hunt_executor_capacity,
            'va_hunt_tools' => $hunt_tools,
            'va_hunt_weapon_use_required' => $hunt_weapon_use_required,
            'va_hunt_land_manager_permit' => $hunt_land_manager_permit,
            'va_hunt_hunting_company_coop' => $hunt_hunting_company_coop,
            'va_hunt_liability_insurance' => $hunt_liability_insurance,
            'va_hunt_damage_nature' => $hunt_damage_nature,
            'va_hunt_pricing_model' => $hunt_pricing_model,
            'va_hunt_travel_fee' => $hunt_travel_fee,
            'va_hunt_contract_type' => $hunt_contract_type,
            'va_hunt_site_conditions' => $hunt_site_conditions,
            'va_hunt_reference_media' => $hunt_reference_media,
            'va_hunt_agri_hectare_size' => $hunt_agri_hectare_size,
            'va_hunt_agri_culture_type' => $hunt_agri_culture_type,
            'va_hunt_urgent_immediate_start' => $hunt_urgent_immediate_start,
            'va_hunt_urgent_duty_phone' => $hunt_urgent_duty_phone,
            'va_hunt_long_term_yearly_discount' => $hunt_long_term_yearly_discount,
            'va_hunt_package_auto_seasonal' => $hunt_package_auto_seasonal,
            'va_hunt_package_monthly_model' => $hunt_package_monthly_model,
            'va_hunt_package_monitoring_intervention' => $hunt_package_monitoring_intervention,
            'va_hunt_needs_review' => ! empty( $hunt_moderation_hits ) ? '1' : '0',
            'va_hunt_review_hits' => implode( ',', $hunt_moderation_hits ),
            'va_hunt_filter_game_species' => $hunt_game_species,
            'va_hunt_filter_country' => $hunt_country,
            'va_hunt_filter_county' => $hunt_county,
            'va_hunt_filter_price_min' => $hunt_price_min,
            'va_hunt_filter_price_max' => $hunt_price_max,
            'va_hunt_filter_methods' => $hunt_methods,
            'va_hunt_filter_method_types' => $hunt_method_types,
            'va_hunt_filter_land_type' => $hunt_land_type,
            'va_hunt_filter_response_time_hours' => (string) $hunt_response_time_hours,
            'va_hunt_filter_contract_type' => $hunt_contract_type,
            'va_hunt_filter_pricing_model' => $hunt_pricing_model,
            'va_hunt_filter_availability_24_7' => $hunt_availability_24_7,
            'va_hunt_filter_has_accommodation' => $hunt_has_accommodation === 'igen' ? '1' : '0',
            'va_hunt_filter_has_guide' => $hunt_guide_type === 'nincs' ? '0' : '1',
            'va_hunt_filter_difficulty_level' => $hunt_difficulty_level,
            'va_hunt_filter_season_start' => $hunt_season_start,
            'va_hunt_filter_season_end' => $hunt_season_end,
            'va_exchange_target' => $exchange_target,
            'va_exchange_type' => $exchange_type,
            'va_exchange_offer_category' => $exchange_offer_category,
            'va_exchange_item_name' => $exchange_item_name,
            'va_exchange_brand' => $exchange_brand,
            'va_exchange_model' => $exchange_model,
            'va_exchange_condition' => $exchange_condition,
            'va_exchange_short_desc' => $exchange_short_desc,
            'va_exchange_description' => $exchange_description,
            'va_exchange_defects' => $exchange_defects,
            'va_exchange_wear_signs' => $exchange_wear_signs,
            'va_exchange_estimated_value' => $exchange_estimated_value,
            'va_exchange_extra_payment_direction' => $exchange_extra_payment_direction,
            'va_exchange_extra_payment_amount' => $exchange_extra_payment_amount,
            'va_exchange_wanted_categories' => $exchange_wanted_categories,
            'va_exchange_country' => $exchange_country,
            'va_exchange_county' => $exchange_county,
            'va_exchange_city' => $exchange_city,
            'va_exchange_method' => $exchange_method,
            'va_exchange_legal_notes' => $exchange_legal_notes,
            'va_exchange_age_18' => $exchange_age_18,
            'va_exchange_required_images' => $exchange_required_images,
            'va_exchange_video_url' => $exchange_video_url,
            'va_exchange_moderation_flags' => $exchange_moderation_flags,
            'va_exchange_partner_requirements' => $exchange_partner_requirements,
            'va_exchange_firearm_caliber' => $exchange_firearm_caliber,
            'va_exchange_firearm_license' => $exchange_firearm_license,
            'va_exchange_optic_zoom' => $exchange_optic_zoom,
            'va_exchange_optic_objective' => $exchange_optic_objective,
            'va_exchange_knife_steel' => $exchange_knife_steel,
            'va_exchange_filter_offer_category' => $exchange_offer_category,
            'va_exchange_filter_wanted_categories' => $exchange_wanted_categories,
            'va_exchange_filter_estimated_value' => $exchange_estimated_value,
            'va_exchange_filter_exchange_type' => $exchange_type,
            'va_exchange_filter_extra_payment_direction' => $exchange_extra_payment_direction,
            'va_exchange_filter_extra_payment_amount' => $exchange_extra_payment_amount,
            'va_exchange_filter_condition' => $exchange_condition,
            'va_exchange_filter_county' => $exchange_county,
            'va_exchange_filter_brand' => sanitize_title( $exchange_brand ),
            'va_publication_type' => $publication_type,
            'va_publication_topics' => $publication_topics,
            'va_publication_title' => $publication_title,
            'va_publication_subtitle' => $publication_subtitle,
            'va_publication_author' => $publication_author,
            'va_publication_publisher' => $publication_publisher,
            'va_publication_year' => $publication_year,
            'va_publication_language' => $publication_language,
            'va_publication_edition_type' => $publication_edition_type,
            'va_publication_periodical_name' => $publication_periodical_name,
            'va_publication_volume' => $publication_volume,
            'va_publication_issue' => $publication_issue,
            'va_publication_complete_volume' => $publication_complete_volume,
            'va_publication_page_count' => $publication_page_count,
            'va_publication_cover_type' => $publication_cover_type,
            'va_publication_size' => $publication_size,
            'va_publication_condition' => $publication_condition,
            'va_publication_defects' => $publication_defects,
            'va_publication_content_flags' => $publication_content_flags,
            'va_publication_collector_flags' => $publication_collector_flags,
            'va_publication_collector_value' => $publication_collector_value,
            'va_publication_usage' => $publication_usage,
            'va_publication_condition_details' => $publication_condition_details,
            'va_publication_authenticity' => $publication_authenticity,
            'va_publication_signed_copy' => $publication_signed_copy,
            'va_publication_signed_to' => $publication_signed_to,
            'va_publication_signature_type' => $publication_signature_type,
            'va_publication_isbn' => $publication_isbn,
            'va_publication_digital_format' => $publication_digital_format,
            'va_publication_moderation_flags' => $publication_moderation_flags,
            'va_publication_notes' => $publication_notes,
            'va_publication_filter_type' => $publication_type,
            'va_publication_filter_topics' => $publication_topics,
            'va_publication_filter_author' => sanitize_title( $publication_author ),
            'va_publication_filter_year' => $publication_year,
            'va_publication_filter_language' => $publication_language,
            'va_publication_filter_condition' => $publication_condition,
            'va_publication_filter_collector_item' => $publication_filter_collector_item,
            'va_publication_filter_complete_volume' => $publication_filter_complete_volume,
            'va_publication_needs_review' => ! empty( $publication_review_hits ) ? '1' : '0',
            'va_publication_review_hits' => implode( ',', $publication_review_hits ),
            'va_equipment_type' => $equipment_type,
            'va_equipment_condition' => $equipment_condition,
            'va_equipment_hunting_usage' => $equipment_hunting_usage,
            'va_equipment_material_type' => $equipment_material_type,
            'va_equipment_color_pattern' => $equipment_color_pattern,
            'va_equipment_length_cm' => $equipment_length_cm,
            'va_equipment_width_cm' => $equipment_width_cm,
            'va_equipment_height_cm' => $equipment_height_cm,
            'va_equipment_weight_g' => $equipment_weight_g,
            'va_equipment_features' => $equipment_features,
            'va_equipment_light_lumen' => $equipment_light_lumen,
            'va_equipment_light_range_m' => $equipment_light_range_m,
            'va_equipment_light_battery_type' => $equipment_light_battery_type,
            'va_equipment_light_usb_charge' => $equipment_light_usb_charge,
            'va_equipment_light_color_modes' => $equipment_light_color_modes,
            'va_equipment_light_color_temperature' => $equipment_light_color_temperature,
            'va_light_type' => $light_type,
            'va_lumens' => $lumens,
            'va_light_levels_count' => $light_levels_count,
            'va_light_turbo_mode' => $light_turbo_mode,
            'va_light_eco_mode' => $light_eco_mode,
            'va_beam_distance_m' => $beam_distance_m,
            'va_beam_distance_band' => $beam_distance_band,
            'va_light_color' => $light_color,
            'va_battery_type' => $battery_type,
            'va_light_usb_rechargeable' => $light_usb_rechargeable,
            'va_light_powerbank_compatible' => $light_powerbank_compatible,
            'va_runtime_hours' => $runtime_hours,
            'va_runtime_eco_hours' => $runtime_eco_hours,
            'va_runtime_turbo_hours' => $runtime_turbo_hours,
            'va_light_material' => $light_material,
            'va_light_ip_rating' => $light_ip_rating,
            'va_light_resistance' => $light_resistance,
            'va_mount_type' => $mount_type,
            'va_light_mount_systems' => $light_mount_systems,
            'va_hunting_light_usage' => $hunting_light_usage,
            'va_light_functions' => $light_functions,
            'va_light_optical_properties' => $light_optical_properties,
            'va_light_weapon_type_compatibility' => $light_weapon_type_compatibility,
            'va_light_optics_compatible' => $light_optics_compatible,
            'va_light_ir_compatible' => $light_ir_compatible,
            'va_light_weight_g' => $light_weight_g,
            'va_light_length_mm' => $light_length_mm,
            'va_light_size_category' => $light_size_category,
            'va_recoil_resistance' => $recoil_resistance,
            'va_rail_compatibility' => $rail_compatibility,
            'va_ir_wavelength_nm' => $ir_wavelength_nm,
            'va_head_tiltable' => $head_tiltable,
            'va_headband_system' => $headband_system,
            'va_target_identification_index' => $target_identification_index,
            'va_equipment_support_height_range_cm' => $equipment_support_height_range_cm,
            'va_equipment_support_legs' => $equipment_support_legs,
            'va_equipment_support_head_swivel' => $equipment_support_head_swivel,
            'va_equipment_support_material' => $equipment_support_material,
            'va_equipment_call_type' => $equipment_call_type,
            'va_equipment_call_species' => $equipment_call_species,
            'va_equipment_call_volume' => $equipment_call_volume,
            'va_equipment_trap_type' => $equipment_trap_type,
            'va_equipment_trap_size' => $equipment_trap_size,
            'va_equipment_cleaning_caliber_compatibility' => $equipment_cleaning_caliber_compatibility,
            'va_equipment_cleaning_full_set' => $equipment_cleaning_full_set,
            'va_equipment_cleaning_oil' => $equipment_cleaning_oil,
            'va_equipment_cleaning_bronze_brush' => $equipment_cleaning_bronze_brush,
            'va_equipment_seat_foldable' => $equipment_seat_foldable,
            'va_equipment_seat_backrest' => $equipment_seat_backrest,
            'va_equipment_seat_swivel' => $equipment_seat_swivel,
            'va_equipment_seat_capacity_kg' => $equipment_seat_capacity_kg,
            'va_equipment_electronic_features' => $equipment_electronic_features,
            'va_equipment_runtime_hours' => $equipment_runtime_hours,
            'va_equipment_accessories' => $equipment_accessories,
            'va_equipment_silence_level' => $equipment_silence_level,
            'va_equipment_weapon_compatibility' => $equipment_weapon_compatibility,
            'va_equipment_caliber_compatibility' => $equipment_caliber_compatibility,
            'va_equipment_moderation_flags' => $equipment_moderation_flags,
            'va_equipment_notes' => $equipment_notes,
            'va_other_equipment_type' => $other_equipment_type,
            'va_other_equipment_function' => $other_equipment_function,
            'va_other_equipment_material_note' => $other_equipment_material_note,
            'va_other_equipment_compatibility' => $other_equipment_compatibility,
            'va_other_hunting_relevance' => $other_hunting_relevance,
            'va_other_uncertain_classification' => $other_uncertain_classification,
            'va_other_auto_suggested_category' => $other_auto_suggested_category,
            'va_other_auto_suggested_reason' => $other_auto_suggested_reason,
            'va_equipment_filter_type' => $equipment_type,
            'va_equipment_filter_usage' => $equipment_hunting_usage,
            'va_equipment_filter_condition' => $equipment_condition,
            'va_equipment_filter_brand' => sanitize_title( $brand ),
            'va_equipment_filter_waterproof' => $equipment_filter_waterproof,
            'va_equipment_filter_silent' => $equipment_filter_silent,
            'va_equipment_filter_foldable' => $equipment_filter_foldable,
            'va_equipment_filter_light_type' => $equipment_filter_light_type,
            'va_equipment_filter_light_color' => $equipment_filter_light_color,
            'va_equipment_filter_lumens' => $equipment_filter_lumens,
            'va_equipment_filter_beam_distance_m' => $equipment_filter_beam_distance_m,
            'va_equipment_filter_mount_type' => $equipment_filter_mount_type,
            'va_equipment_filter_battery_type' => $equipment_filter_battery_type,
            'va_equipment_filter_ip_rating' => $equipment_filter_ip_rating,
            'va_equipment_filter_recoil_resistance' => $equipment_filter_recoil_resistance,
            'va_equipment_filter_other_type' => $other_equipment_type,
            'va_equipment_filter_other_function' => sanitize_title( $other_equipment_function ),
            'va_equipment_filter_other_material' => sanitize_title( $other_equipment_material_note ),
            'va_equipment_filter_other_compatibility' => sanitize_title( $other_equipment_compatibility ),
            'va_equipment_filter_other_relevance' => $other_hunting_relevance,
            'va_equipment_filter_other_suggested_category' => $other_auto_suggested_category,
            'va_equipment_needs_review' => ! empty( $equipment_review_hits ) ? '1' : '0',
            'va_equipment_review_hits' => implode( ',', $equipment_review_hits ),
            'va_bag_type' => $bag_type,
            'va_volume_l' => $volume_l,
            'va_bag_size' => $bag_size,
            'va_bag_material' => $bag_material,
            'va_bag_weather_resistance' => $bag_weather_resistance,
            'va_bag_color_pattern' => $bag_color_pattern,
            'va_bag_camo_pattern' => $bag_camo_pattern,
            'va_bag_modular' => $bag_modular,
            'va_bag_design_features' => $bag_design_features,
            'va_bag_usage' => $bag_usage,
            'va_bag_firearm_compatible' => $bag_firearm_compatible,
            'va_bag_max_firearm_length_cm' => $bag_max_firearm_length_cm,
            'va_bag_inner_padding' => $bag_inner_padding,
            'va_bag_lockable' => $bag_lockable,
            'va_bag_hydration_system' => $bag_hydration_system,
            'va_bag_touring_compatible' => $bag_touring_compatible,
            'va_bag_mag_holder_count' => $bag_mag_holder_count,
            'va_bag_ammo_compartment_count' => $bag_ammo_compartment_count,
            'va_bag_main_compartments' => $bag_main_compartments,
            'va_bag_outer_pockets' => $bag_outer_pockets,
            'va_bag_internal_organizer' => $bag_internal_organizer,
            'va_bag_ammo_holder' => $bag_ammo_holder,
            'va_bag_document_compartment' => $bag_document_compartment,
            'va_bag_carrying_system' => $bag_carrying_system,
            'va_bag_max_load_kg' => $bag_max_load_kg,
            'va_bag_comfort' => $bag_comfort,
            'va_bag_closure_type' => $bag_closure_type,
            'va_bag_condition' => $bag_condition,
            'va_bag_packability_index' => $bag_packability_index,
            'va_bag_filter_type' => $bag_type,
            'va_bag_filter_volume_l' => $volume_l,
            'va_bag_filter_waterproof' => $bag_filter_waterproof,
            'va_bag_filter_molle' => $bag_filter_molle,
            'va_bag_filter_firearm_compatible' => $bag_filter_firearm_compatible,
            'va_bag_filter_color_pattern' => $bag_filter_color,
            'va_bag_filter_material' => $bag_filter_material,
            'va_bag_filter_condition' => $bag_condition,
            'va_bag_filter_modular' => $bag_filter_modular,
            'va_call_type' => $call_fields['call_type'],
            'va_call_target_species' => $call_fields['call_target_species'],
            'va_call_sound_type' => $call_fields['call_sound_type'],
            'va_call_material_type' => $call_fields['call_material_type'],
            'va_call_operation_mode' => $call_fields['call_operation_mode'],
            'va_call_electronic_features' => $call_fields['call_electronic_features'],
            'va_call_volume_level' => $call_fields['call_volume_level'],
            'va_call_weather_resistance' => $call_fields['call_weather_resistance'],
            'va_call_size_class' => $call_fields['call_size_class'],
            'va_call_usage' => $call_fields['call_usage'],
            'va_call_sound_generation' => $call_fields['call_sound_generation'],
            'va_call_style' => $call_fields['call_style'],
            'va_call_accessories' => $call_fields['call_accessories'],
            'va_call_condition' => $call_fields['call_condition'],
            'va_call_sample_count' => $call_fields['call_sample_count'],
            'va_call_range_m' => $call_fields['call_range_m'],
            'va_call_runtime_hours' => $call_fields['call_runtime_hours'],
            'va_call_maker_name' => $call_fields['call_maker_name'],
            'va_call_unique_piece' => $call_fields['call_unique_piece'],
            'va_call_competition_standard' => $call_fields['call_competition_standard'],
            'va_call_tuning' => $call_fields['call_tuning'],
            'va_call_sound_sample_url' => $call_fields['call_sound_sample_url'],
            'va_call_filter_type' => $call_fields['call_filter_type'],
            'va_call_filter_target_species' => $call_fields['call_filter_target_species'],
            'va_call_filter_operation_mode' => $call_fields['call_filter_operation_mode'],
            'va_call_filter_material_type' => $call_fields['call_filter_material_type'],
            'va_call_filter_volume_level' => $call_fields['call_filter_volume_level'],
            'va_call_filter_style' => $call_fields['call_filter_style'],
            'va_call_filter_waterproof' => $call_fields['call_filter_waterproof'],
            'va_call_filter_is_electronic' => $call_fields['call_filter_is_electronic'],
            'va_decor_type' => $decor_type,
            'va_decor_material' => $decor_material,
            'va_decor_wood_type' => $decor_wood_type,
            'va_decor_metal_alloy' => $decor_metal_alloy,
            'va_decor_style' => $decor_style,
            'va_decor_motif_tags' => $decor_motif_tags,
            'va_decor_finish' => $decor_finish,
            'va_decor_detailing' => $decor_detailing,
            'va_decor_era' => $decor_era,
            'va_decor_production_type' => $decor_production_type,
            'va_decor_function' => $decor_function,
            'va_decor_thematic_group' => $decor_thematic_group,
            'va_decor_special_features' => $decor_special_features,
            'va_decor_mounting' => $decor_mounting,
            'va_decor_size_class' => $decor_size_class,
            'va_decor_condition' => $decor_condition,
            'va_decor_replica_type' => $decor_replica_type,
            'va_decor_authenticity_level' => $decor_authenticity_level,
            'va_decor_collectible_flag' => $decor_collectible_flag,
            'va_decor_filter_type' => $decor_type,
            'va_decor_filter_material' => $decor_material,
            'va_decor_filter_style' => $decor_style,
            'va_decor_filter_motif' => $decor_motif_tags,
            'va_decor_filter_production' => $decor_production_type,
            'va_decor_filter_size' => $decor_size_class,
            'va_decor_filter_condition' => $decor_condition,
            'va_decor_filter_collectible_flag' => $decor_filter_collectible,
            'va_shoe_size' => $shoe_size,
            'va_shoe_main_type' => $shoe_main_type,
            'va_shoe_condition' => $shoe_condition,
            'va_shoe_gender' => $shoe_gender,
            'va_shoe_eu_size' => $shoe_eu_size,
            'va_shoe_uk_size' => $shoe_uk_size,
            'va_shoe_us_size' => $shoe_us_size,
            'va_shoe_boot_height' => $shoe_boot_height,
            'va_shoe_outer_material' => $shoe_outer_material,
            'va_shoe_lining' => $shoe_lining,
            'va_shoe_sole_material' => $shoe_sole_material,
            'va_shoe_protections' => $shoe_protections,
            'va_shoe_season' => $shoe_season,
            'va_shoe_terrain' => $shoe_terrain,
            'va_shoe_use_cases' => $shoe_use_cases,
            'va_shoe_sole_pattern' => $shoe_sole_pattern,
            'va_shoe_stiffness' => $shoe_stiffness,
            'va_shoe_weight_single_g' => $shoe_weight_single_g,
            'va_shoe_weight_pair_g' => $shoe_weight_pair_g,
            'va_shoe_accessories' => $shoe_accessories,
            'va_shoe_shipping_methods' => $shoe_shipping_methods,
            'va_shoe_required_media' => $shoe_required_media,
            'va_shoe_rubber_shaft_cm' => $shoe_rubber_shaft_cm,
            'va_shoe_rubber_neoprene_lining' => $shoe_rubber_neoprene_lining,
            'va_shoe_tactical_steel_toe' => $shoe_tactical_steel_toe,
            'va_shoe_tactical_quick_lace' => $shoe_tactical_quick_lace,
            'va_shoe_winter_comfort_temp_c' => $shoe_winter_comfort_temp_c,
            'va_shoe_foot_type' => $shoe_foot_type,
            'va_shoe_sole_wear_percent' => $shoe_sole_wear_percent,
            'va_shoe_inner_wear' => $shoe_inner_wear,
            'va_shoe_waterproof_retained' => $shoe_waterproof_retained,
            'va_shoe_resoled' => $shoe_resoled,
            'va_shoe_notes' => $shoe_notes,
            'va_shoe_filter_eu_size' => $shoe_eu_size,
            'va_shoe_filter_waterproof' => $shoe_filter_waterproof,
            'va_shoe_filter_membrane_type' => $shoe_lining,
            'va_shoe_filter_goretex' => $shoe_filter_goretex,
            'va_shoe_filter_boot_height' => $shoe_boot_height,
            'va_shoe_filter_high_ankle' => $shoe_filter_high_ankle,
            'va_shoe_filter_season' => $shoe_season,
            'va_shoe_filter_season_winter' => $shoe_filter_winter,
            'va_shoe_filter_terrain' => $shoe_terrain,
            'va_vadcamera_camera_type' => $vadcamera_camera_type,
            'va_vadcamera_condition' => $vadcamera_condition,
            'va_vadcamera_warranty' => $vadcamera_warranty,
            'va_vadcamera_manufacture_year' => $vadcamera_manufacture_year,
            'va_vadcamera_photo_resolution' => $vadcamera_photo_resolution,
            'va_vadcamera_video_resolution' => $vadcamera_video_resolution,
            'va_vadcamera_video_fps' => $vadcamera_video_fps,
            'va_vadcamera_video_audio' => $vadcamera_video_audio,
            'va_vadcamera_ir_type' => $vadcamera_ir_type,
            'va_vadcamera_night_range_m' => $vadcamera_night_range_m,
            'va_vadcamera_ir_led_count' => $vadcamera_ir_led_count,
            'va_vadcamera_trigger_speed' => $vadcamera_trigger_speed,
            'va_vadcamera_pir_distance_m' => $vadcamera_pir_distance_m,
            'va_vadcamera_view_angle_deg' => $vadcamera_view_angle_deg,
            'va_vadcamera_connectivity' => $vadcamera_connectivity,
            'va_vadcamera_sim_slot' => $vadcamera_sim_slot,
            'va_vadcamera_mobile_app' => $vadcamera_mobile_app,
            'va_vadcamera_sd_max_gb' => $vadcamera_sd_max_gb,
            'va_vadcamera_internal_memory' => $vadcamera_internal_memory,
            'va_vadcamera_power_types' => $vadcamera_power_types,
            'va_vadcamera_runtime' => $vadcamera_runtime,
            'va_vadcamera_ip_rating' => $vadcamera_ip_rating,
            'va_vadcamera_temp_range' => $vadcamera_temp_range,
            'va_vadcamera_accessories' => $vadcamera_accessories,
            'va_vadcamera_use_cases' => $vadcamera_use_cases,
            'va_vadcamera_audio_record' => $vadcamera_audio_record,
            'va_vadcamera_public_area_use' => $vadcamera_public_area_use,
            'va_vadcamera_mounting' => $vadcamera_mounting,
            'va_vadcamera_shipping_methods' => $vadcamera_shipping_methods,
            'va_vadcamera_media_required' => $vadcamera_media_required,
            'va_vadcamera_sample_media' => $vadcamera_sample_media,
            'va_vadcamera_4g_carrier' => $vadcamera_4g_carrier,
            'va_vadcamera_4g_app_support' => $vadcamera_4g_app_support,
            'va_vadcamera_solar_panel_power' => $vadcamera_solar_panel_power,
            'va_vadcamera_notes' => $vadcamera_notes,
            'va_vadcamera_filter_connectivity_4g' => $vadcamera_filter_connectivity_4g,
            'va_vadcamera_filter_connectivity_wifi' => $vadcamera_filter_connectivity_wifi,
            'va_vadcamera_filter_trigger_speed' => $vadcamera_trigger_speed,
            'va_vadcamera_filter_ir_type' => $vadcamera_ir_type,
            'va_vadcamera_filter_night_range_m' => $vadcamera_night_range_m,
            'va_vadcamera_filter_video_resolution' => $vadcamera_video_resolution,
            'va_vadcamera_filter_ip_rating' => $vadcamera_ip_rating,
            'va_vadcamera_filter_is_solar' => $vadcamera_filter_is_solar,
            'va_vadcamera_filter_is_sim' => $vadcamera_filter_is_sim,
            'va_vadcamera_needs_review' => ! empty( $vadcamera_moderation_hits ) ? '1' : '0',
            'va_vadcamera_review_hits' => implode( ',', $vadcamera_moderation_hits ),
            'va_shoe_needs_review' => ! empty( $shoe_moderation_hits ) ? '1' : '0',
            'va_shoe_review_hits' => implode( ',', $shoe_moderation_hits ),
            'va_estate_main_type' => $estate_main_type,
            'va_estate_sale_mode' => $estate_sale_mode,
            'va_estate_price_total' => $estate_price_total,
            'va_estate_price_guide' => $estate_price_guide,
            'va_estate_price_per_item' => $estate_price_per_item,
            'va_estate_estimated_value' => $estate_estimated_value,
            'va_estate_negotiable' => $estate_negotiable,
            'va_estate_firearms_count' => $estate_firearms_count,
            'va_estate_firearms_type' => $estate_firearms_type,
            'va_estate_firearms_deactivated' => $estate_firearms_deactivated,
            'va_estate_firearms_licensed' => $estate_firearms_licensed,
            'va_estate_optics_count' => $estate_optics_count,
            'va_estate_clothing_count' => $estate_clothing_count,
            'va_estate_trophy_count' => $estate_trophy_count,
            'va_estate_docs_count' => $estate_docs_count,
            'va_estate_equipment_count' => $estate_equipment_count,
            'va_estate_condition' => $estate_condition,
            'va_estate_origin' => $estate_origin,
            'va_estate_legal_has_licensed_items' => $estate_legal_has_licensed_items,
            'va_estate_legal_deactivated' => $estate_legal_deactivated,
            'va_estate_legal_has_papers' => $estate_legal_has_papers,
            'va_estate_legal_bundle_only' => $estate_legal_bundle_only,
            'va_estate_transfer_methods' => $estate_transfer_methods,
            'va_estate_optic_items' => implode( ',', $estate_optic_items ),
            'va_estate_clothing_items' => implode( ',', $estate_clothing_items ),
            'va_estate_trophy_items' => implode( ',', $estate_trophy_items ),
            'va_estate_docs_items' => implode( ',', $estate_docs_items ),
            'va_estate_equipment_items' => implode( ',', $estate_equipment_items ),
            'va_estate_media_items' => implode( ',', $estate_media_items ),
            'va_estate_media_full' => $estate_media_full,
            'va_estate_media_details' => $estate_media_details,
            'va_estate_media_documents' => $estate_media_documents,
            'va_estate_media_firearms' => $estate_media_firearms,
            'va_estate_optic_celtavcso' => $estate_optic_celtavcso,
            'va_estate_optic_binokular' => $estate_optic_binokular,
            'va_estate_optic_spektiv' => $estate_optic_spektiv,
            'va_estate_optic_reddot' => $estate_optic_reddot,
            'va_estate_clothing_kabat' => $estate_clothing_kabat,
            'va_estate_clothing_nadrag' => $estate_clothing_nadrag,
            'va_estate_clothing_csizma' => $estate_clothing_csizma,
            'va_estate_clothing_esoruha' => $estate_clothing_esoruha,
            'va_estate_trophy_agancs' => $estate_trophy_agancs,
            'va_estate_trophy_koponya' => $estate_trophy_koponya,
            'va_estate_trophy_preparatum' => $estate_trophy_preparatum,
            'va_estate_docs_engedelyek' => $estate_docs_engedelyek,
            'va_estate_docs_vadasznaplo' => $estate_docs_vadasznaplo,
            'va_estate_docs_konyvek' => $estate_docs_konyvek,
            'va_estate_docs_tanusitvanyok' => $estate_docs_tanusitvanyok,
            'va_estate_equipment_kes' => $estate_equipment_kes,
            'va_estate_equipment_hatizsak' => $estate_equipment_hatizsak,
            'va_estate_equipment_lampa' => $estate_equipment_lampa,
            'va_estate_equipment_tavcso_tartozek' => $estate_equipment_tavcso_tartozek,
            'va_estate_equipment_fegyverszef' => $estate_equipment_fegyverszef,
            'va_estate_notes' => $estate_notes,
            'va_estate_filter_has_firearms' => $estate_filter_has_firearms,
            'va_estate_filter_has_optics' => $estate_filter_has_optics,
            'va_estate_filter_scope' => $estate_filter_scope,
            'va_estate_needs_review' => ! empty( $estate_moderation_hits ) ? '1' : '0',
            'va_estate_review_hits' => implode( ',', $estate_moderation_hits ),
            'va_loszer_category' => $loszer_category,
            'va_loszer_caliber_preset' => $loszer_caliber_preset,
            'va_loszer_caliber_custom' => $loszer_caliber_custom,
            'va_loszer_manufacturer_preset' => $loszer_manufacturer_preset,
            'va_loszer_manufacturer_custom' => $loszer_manufacturer_custom,
            'va_loszer_projectile_type' => $loszer_projectile_type,
            'va_loszer_projectile_weight' => $loszer_projectile_weight,
            'va_loszer_projectile_weight_unit' => $loszer_projectile_weight_unit,
            'va_loszer_piece_count' => $loszer_piece_count,
            'va_loszer_piece_unit' => $loszer_piece_unit,
            'va_loszer_lot_year' => $loszer_lot_year,
            'va_loszer_condition' => $loszer_condition,
            'va_loszer_component_case_condition' => $loszer_component_case_condition,
            'va_loszer_powder_type' => $loszer_powder_type,
            'va_loszer_powder_sealed' => $loszer_powder_sealed,
            'va_loszer_powder_weight' => $loszer_powder_weight,
            'va_loszer_primer_type' => $loszer_primer_type,
            'va_bow_type' => $bow_type,
            'va_bow_handedness' => $bow_handedness,
            'va_bow_draw_weight' => $bow_draw_weight,
            'va_bow_draw_length' => $bow_draw_length,
            'va_bow_axle_length' => $bow_axle_length,
            'va_bow_item_condition' => $bow_item_condition,
            'va_bow_condition_notes' => $bow_condition_notes,
            'va_bow_string_replaced' => $bow_string_replaced,
            'va_bow_owned_since' => $bow_owned_since,
            'va_bow_usage' => $bow_usage,
            'va_bow_accessories' => $bow_accessories,
            'va_bow_cams_cables' => $bow_cams_cables,
            'va_bow_speed_fps' => $bow_speed_fps,
            'va_bow_serial' => $bow_serial,
            'va_bow_material' => $bow_material,
            'va_other_weapon_kind' => $other_weapon_kind,
            'va_other_weapon_general_notes' => $other_weapon_general_notes,
            'va_other_weapon_accessories' => $other_weapon_accessories,
            'va_other_weapon_accessories_other' => $other_weapon_accessories_other,
            'va_other_weapon_18plus' => $other_weapon_18plus,
            'va_other_weapon_permit_required' => $other_weapon_permit_required,
            'va_other_weapon_personal_pickup' => $other_weapon_personal_pickup,
            'va_other_weapon_origin_papers' => $other_weapon_origin_papers,
            'va_other_weapon_serial_number' => $other_weapon_serial_number,
            'va_other_weapon_bow_draw_weight_lbs' => $other_weapon_bow_draw_weight_lbs,
            'va_other_weapon_bow_axle_to_axle' => $other_weapon_bow_axle_to_axle,
            'va_other_weapon_bow_handness' => $other_weapon_bow_handness,
            'va_other_weapon_bow_style' => $other_weapon_bow_style,
            'va_other_weapon_bow_arrow_rest' => $other_weapon_bow_arrow_rest,
            'va_other_weapon_bow_sight' => $other_weapon_bow_sight,
            'va_other_weapon_crossbow_draw_weight_lbs' => $other_weapon_crossbow_draw_weight_lbs,
            'va_other_weapon_crossbow_fps' => $other_weapon_crossbow_fps,
            'va_other_weapon_crossbow_accessories' => $other_weapon_crossbow_accessories,
            'va_other_weapon_airsoft_operation' => $other_weapon_airsoft_operation,
            'va_other_weapon_airsoft_energy_joule' => $other_weapon_airsoft_energy_joule,
            'va_other_weapon_airsoft_fps' => $other_weapon_airsoft_fps,
            'va_other_weapon_airsoft_magazine_type' => $other_weapon_airsoft_magazine_type,
            'va_other_weapon_airgun_caliber' => $other_weapon_airgun_caliber,
            'va_other_weapon_airgun_muzzle_energy' => $other_weapon_airgun_muzzle_energy,
            'va_other_weapon_airgun_power_source' => $other_weapon_airgun_power_source,
            'va_other_weapon_gas_alarm_caliber' => $other_weapon_gas_alarm_caliber,
            'va_other_weapon_gas_alarm_self_defense_license' => $other_weapon_gas_alarm_self_defense_license,
            'va_other_weapon_gas_alarm_carry_license' => $other_weapon_gas_alarm_carry_license,
            'va_other_weapon_knife_blade_length' => $other_weapon_knife_blade_length,
            'va_other_weapon_knife_steel_type' => $other_weapon_knife_steel_type,
            'va_other_weapon_knife_handle_material' => $other_weapon_knife_handle_material,
            'va_other_weapon_knife_sheath' => $other_weapon_knife_sheath,
            'va_other_weapon_knife_accessories' => $other_weapon_knife_accessories,
            'va_hatastalanitott_weapon_type' => $hatastalanitott_weapon_type,
            'va_hatastalanitott_origin_country' => $hatastalanitott_origin_country,
            'va_hatastalanitott_is_deactivated' => $hatastalanitott_is_deactivated,
            'va_hatastalanitott_deactivation_type' => $hatastalanitott_deactivation_type,
            'va_hatastalanitott_certificate' => $hatastalanitott_certificate,
            'va_hatastalanitott_mkh_cip' => $hatastalanitott_mkh_cip,
            'va_hatastalanitott_deactivation_year' => $hatastalanitott_deactivation_year,
            'va_hatastalanitott_deactivation_org' => $hatastalanitott_deactivation_org,
            'va_hatastalanitott_condition' => $hatastalanitott_condition,
            'va_hatastalanitott_serial_admin' => $hatastalanitott_serial_admin,
            'va_hatastalanitott_working_parts' => $hatastalanitott_working_parts,
            'va_hatastalanitott_original_finish' => $hatastalanitott_original_finish,
            'va_hatastalanitott_original_parts_ratio' => $hatastalanitott_original_parts_ratio,
            'va_hatastalanitott_accessories' => $hatastalanitott_accessories,
            'va_bow_acc_sight' => $bow_acc_sight,
            'va_bow_acc_release' => $bow_acc_release,
            'va_bow_acc_stabilizer' => $bow_acc_stabilizer,
            'va_bow_acc_arrows' => $bow_acc_arrows,
            'va_bow_acc_case' => $bow_acc_case,
            'va_bow_acc_quiver' => $bow_acc_quiver,
            'va_bow_acc_target' => $bow_acc_target,
            'va_feed_type' => $feed_type,
            'va_feed_recommended_game' => $feed_recommended_game,
            'va_feed_game_boar' => $feed_game_boar,
            'va_feed_game_roe' => $feed_game_roe,
            'va_feed_game_red_deer' => $feed_game_red_deer,
            'va_feed_game_pheasant' => $feed_game_pheasant,
            'va_feed_game_other' => $feed_game_other,
            'va_feed_game_other_text' => $feed_game_other_text,
            'va_feed_ingredients' => $feed_ingredients,
            'va_feed_moisture' => $feed_moisture,
            'va_feed_has_additives' => $feed_has_additives,
            'va_feed_packaging_type' => $feed_packaging_type,
            'va_feed_bag_weight' => $feed_bag_weight,
            'va_feed_total_quantity' => $feed_total_quantity,
            'va_feed_pallet_quantity' => $feed_pallet_quantity,
            'va_feed_harvest_freshness' => $feed_harvest_freshness,
            'va_feed_stored_dry' => $feed_stored_dry,
            'va_feed_mold_free' => $feed_mold_free,
            'va_feed_bags_undamaged' => $feed_bags_undamaged,
            'va_feed_price_per_unit' => $feed_price_per_unit,
            'va_feed_min_order' => $feed_min_order,
            'va_feed_delivery_available' => $feed_delivery_available,
            'va_feed_loading_available' => $feed_loading_available,
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

        self::learn_listing_terms( $category, [
            'title'    => $title,
            'brand'    => $brand,
            'model'    => $model,
            'caliber'  => $caliber,
            'location' => $location,
            'street'   => $street,
        ] );

        // Képfeltöltés kezelése
        $img_errors = [];
        if ( ! empty( $_FILES['listing_images'] ) ) {
            $featured_idx = isset( $_POST['featured_image_index'] ) ? absint( (string) $_POST['featured_image_index'] ) : 0;
            $img_errors = self::handle_images( $post_id, $_FILES['listing_images'], $featured_idx );
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

    private static function get_target_plan_slug_for_qty( int $qty ): string {
        $default_qtys  = [ 1 => 1, 2 => 3, 3 => 5, 4 => 10 ];
        $default_slugs = [ 1 => 'basic', 2 => 'silver', 3 => 'gold', 4 => 'platinum' ];

        for ( $n = 1; $n <= 4; $n++ ) {
            $enabled = get_option( "va_pc_{$n}_enabled", '1' ) === '1';
            if ( ! $enabled ) {
                continue;
            }
            $cfg_qty = max( 1, (int) get_option( "va_pc_{$n}_qty", $default_qtys[ $n ] ) );
            if ( $cfg_qty === $qty ) {
                return self::normalize_purchase_plan_slug( (string) get_option( "va_pc_{$n}_plan_slug", $default_slugs[ $n ] ) );
            }
        }

        if ( $qty >= 10 ) return 'platinum';
        if ( $qty >= 5 )  return 'gold';
        if ( $qty >= 3 )  return 'silver';
        return 'basic';
    }

    private static function normalize_purchase_plan_slug( string $slug ): string {
        $slug = sanitize_key( $slug );
        if ( in_array( $slug, [ 'company', 'business', 'corporate', 'ceges', 'ceg', 'custom', 'egyedi' ], true ) ) {
            return 'custom';
        }
        return $slug;
    }

    private static function get_plan_base_duration_days( int $user_id, string $plan_slug ): int {
        $plan_slug = self::normalize_purchase_plan_slug( $plan_slug );
        if ( $plan_slug === 'custom' ) {
            $custom_dur = (int) get_user_meta( $user_id, 'va_plan_custom_duration_days', true );
            return $custom_dur > 0 ? $custom_dur : 365;
        }
        return 365;
    }

    private static function get_plan_price_total_for_slug( string $plan_slug ): int {
        $plan_slug = self::normalize_purchase_plan_slug( $plan_slug );
        $packages  = self::get_credit_packages();

        $found_total = 0;
        foreach ( $packages as $pkg ) {
            $qty = absint( $pkg['qty'] ?? 0 );
            if ( $qty <= 0 ) {
                continue;
            }
            $pkg_plan = self::get_target_plan_slug_for_qty( $qty );
            if ( $pkg_plan !== $plan_slug ) {
                continue;
            }
            $total = absint( $pkg['total'] ?? 0 );
            if ( $total > 0 ) {
                $found_total = $total;
                break;
            }
        }

        return $found_total;
    }

    private static function get_plan_label_for_user( int $user_id, string $plan_slug ): string {
        $plan_slug = self::normalize_purchase_plan_slug( $plan_slug );
        $label = ucfirst( $plan_slug );
        if ( class_exists( 'VA_User_Roles' ) ) {
            $cfg = VA_User_Roles::get_plan_config( $plan_slug, $user_id );
            $candidate = trim( (string) ( $cfg['label'] ?? '' ) );
            if ( $candidate !== '' ) {
                $label = $candidate;
            }
        }
        if ( $plan_slug === 'custom' ) {
            $label = 'Céges előfizetés';
        }
        return $label;
    }

    private static function get_card_index_for_purchase( int $qty, string $plan_slug ): int {
        $plan_slug = self::normalize_purchase_plan_slug( $plan_slug );
        $count = max( 1, min( 8, (int) get_option( 'va_pc_count', 4 ) ) );

        // 1) Pontos találat: qty + plan slug.
        for ( $n = 1; $n <= $count; $n++ ) {
            if ( get_option( "va_pc_{$n}_enabled", '1' ) !== '1' ) {
                continue;
            }
            $cfg_qty = max( 1, (int) get_option( "va_pc_{$n}_qty", 0 ) );
            $cfg_slug = self::normalize_purchase_plan_slug( (string) get_option( "va_pc_{$n}_plan_slug", '' ) );
            if ( $cfg_qty === $qty && $cfg_slug !== '' && $cfg_slug === $plan_slug ) {
                return $n;
            }
        }

        // 2) Fallback: qty egyezés (ha slug nincs vagy egyedi).
        for ( $n = 1; $n <= $count; $n++ ) {
            if ( get_option( "va_pc_{$n}_enabled", '1' ) !== '1' ) {
                continue;
            }
            $cfg_qty = max( 1, (int) get_option( "va_pc_{$n}_qty", 0 ) );
            if ( $cfg_qty === $qty ) {
                return $n;
            }
        }

        return 0;
    }

    private static function extract_card_cooldown_days( array $features ): int {
        foreach ( $features as $feature ) {
            $row = trim( (string) $feature );
            if ( $row === '' ) {
                continue;
            }

            // Pl.: "Hirdetés kiemelés: 7 naponta" vagy "Kiemelési újratöltés: 5 nap".
            if ( preg_match( '/kiemel(?:e|é)s[^0-9]{0,24}(\d+)\s*nap(?:onta)?/iu', $row, $m ) ) {
                return max( 1, (int) ( $m[1] ?? 0 ) );
            }
        }
        return 0;
    }

    private static function sync_user_card_cooldown( int $user_id, int $qty, string $plan_slug ): void {
        if ( $user_id <= 0 || $qty <= 0 ) {
            return;
        }

        $card_index = self::get_card_index_for_purchase( $qty, $plan_slug );
        if ( $card_index <= 0 ) {
            return;
        }

        // Elsődleges forrás: dedikált admin mező a kártyán.
        $days = absint( get_option( "va_pc_{$card_index}_boost_cooldown", 0 ) );
        if ( $days <= 0 ) {
            // Visszafelé kompatibilitás: régi feature-szöveg parse-olása.
            $features = get_option( "va_pc_{$card_index}_features", [] );
            if ( ! is_array( $features ) ) {
                return;
            }
            $days = self::extract_card_cooldown_days( $features );
        }

        if ( $days <= 0 ) {
            return;
        }

        // Kártya a mérvadó: vásárláskor a user szintű kiemelési újratöltést ehhez igazítjuk.
        update_user_meta( $user_id, 'va_plan_boost_cooldown', $days );
    }

    private static function enforce_credit_package_purchase_rule( int $user_id, int $qty ) {
        $target_plan = self::get_target_plan_slug_for_qty( $qty );

        $target_rank = class_exists( 'VA_User_Roles' )
            ? VA_User_Roles::get_plan_rank( $target_plan )
            : 0;

        if ( $target_rank <= 0 ) {
            return true;
        }

        $current_plan = class_exists( 'VA_User_Roles' )
            ? VA_User_Roles::get_user_plan( $user_id )
            : sanitize_key( (string) get_user_meta( $user_id, 'va_plan', true ) );
        $current_plan = self::normalize_purchase_plan_slug( $current_plan );

        $current_rank = class_exists( 'VA_User_Roles' )
            ? VA_User_Roles::get_plan_rank( $current_plan )
            : 0;

        $active_until = (int) get_user_meta( $user_id, 'va_plan_expires_at', true );
        $has_active_product = ( $current_rank > 0 && ( $active_until <= 0 || $active_until > time() ) );

        // Aktív csomagnál csak magasabb rang vásárolható.
        if ( $has_active_product && $target_rank <= $current_rank ) {
            $until_txt = ( $active_until > 0 ) ? wp_date( 'Y.m.d H:i', $active_until ) : 'nincs beállítva';
            return new WP_Error(
                'va_plan_upgrade_only',
                'Aktív csomagod van (' . self::get_plan_label_for_user( $user_id, $current_plan ) . ') ' . $until_txt . ' időpontig. Csak magasabb rang vásárolható.'
            );
        }

        return true;
    }

    /* ── Kredit csomag vásárlás ────────────────────────── */
    public static function buy_credits(): void {
        check_ajax_referer( 'va_buy_credits', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $user_id = get_current_user_id();

        $return_to = isset( $_POST['return_to'] ) ? sanitize_key( (string) wp_unslash( $_POST['return_to'] ) ) : 'buy';
        if ( ! in_array( $return_to, [ 'buy', 'submit' ], true ) ) {
            $return_to = 'buy';
        }

        $qty = absint( $_POST['qty'] ?? 0 );
        if ( $qty < 1 ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen mennyiség.' ] );
        }

        $packages = self::get_credit_packages();
        $allowed_qtys = [];
        foreach ( $packages as $p ) {
            $pq = absint( $p['qty'] ?? 0 );
            if ( $pq > 0 ) {
                $allowed_qtys[] = $pq;
            }
        }
        if ( ! in_array( $qty, $allowed_qtys, true ) ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen csomag választás.' ] );
        }

        $purchase_check = self::enforce_credit_package_purchase_rule( $user_id, $qty );
        if ( is_wp_error( $purchase_check ) ) {
            wp_send_json_error( [ 'message' => $purchase_check->get_error_message() ] );
        }

        // Pontosan a választott csomag ára számít, manipulált qty nem fogadható el.
        $unit_price = (int) get_option( 'va_listing_price_after_free', 1990 );
        $total      = $unit_price * $qty;

        $matched = false;
        foreach ( $packages as $pkg ) {
            if ( $qty === (int) ( $pkg['qty'] ?? 0 ) ) {
                $unit_price = $pkg['unit_price'];
                $total      = $pkg['total'];
                $matched    = true;
                break;
            }
        }
        if ( ! $matched ) {
            wp_send_json_error( [ 'message' => 'A választott csomag nem elérhető.' ] );
        }

        $payment_url = trim( (string) get_option( 'va_listing_payment_url', '' ) );
        $provider    = sanitize_key( (string) get_option( 'va_payment_provider', 'none' ) );
        $secret_key  = trim( (string) get_option( 'va_payment_secret_key', '' ) );

        $token  = wp_generate_password( 32, false, false );

        // Token elmentése átmeneti adatban
        set_transient( 'va_credit_token_' . $token, [
            'user_id'    => $user_id,
            'qty'        => $qty,
            'amount'     => $total,
            'provider'   => $provider,
            'return_to'  => $return_to,
            'created_at' => time(),
        ], DAY_IN_SECONDS );

        $return_url = $return_to === 'submit'
            ? self::get_submit_page_url()
            : self::get_buy_credits_page_url();

        $success_url = add_query_arg([
            'va_credit_payment' => 'success',
            'token'             => rawurlencode( $token ),
        ], $return_url );
        $success_url .= ( strpos( $success_url, '?' ) === false ? '?' : '&' ) . 'session_id={CHECKOUT_SESSION_ID}';
        $cancel_url = add_query_arg([
            'va_credit_payment' => 'cancel',
            'token'             => rawurlencode( $token ),
        ], $return_url );

        // Elsődleges: közvetlen Stripe Checkout (WooCommerce nélkül).
        if ( $provider === 'stripe' ) {
            if ( $secret_key === '' ) {
                wp_send_json_error( [ 'message' => 'Stripe titkos kulcs nincs beállítva az admin felületen.' ] );
            }

            $session_url = self::create_stripe_checkout_session( $token, $qty, $total, $success_url, $cancel_url, $user_id );
            if ( is_wp_error( $session_url ) ) {
                wp_send_json_error( [ 'message' => $session_url->get_error_message() ] );
            }

            wp_send_json_success( [
                'checkout_url' => esc_url_raw( (string) $session_url ),
                'total'        => $total,
                'qty'          => $qty,
            ] );
        }

        // Fallback: külső szolgáltató URL (legacy flow).
        if ( $payment_url === '' ) {
            wp_send_json_error( [ 'message' => 'Fizetési szolgáltató nincs beállítva. Kérjük, lépjen kapcsolatba az adminisztrátorral.' ] );
        }

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

    // JS fallback: közvetlen linkről is elindítható a checkout.
    public static function handle_buy_credits_start(): void {
        try {
            $start = isset( $_GET['va_buy_credits_start'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_buy_credits_start'] ) ) : '';
            if ( $start !== '1' ) {
                return;
            }

            if ( ! is_user_logged_in() ) {
                wp_safe_redirect( wp_login_url( self::get_buy_credits_page_url() ) );
                exit;
            }

            $nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['nonce'] ) ) : '';
            if ( ! wp_verify_nonce( $nonce, 'va_buy_credits' ) ) {
                va_set_flash( 'error', 'Érvénytelen vásárlási kérés. Kérjük frissítsd az oldalt.' );
                self::redirect_buy_credits_page();
                return;
            }

            $qty = isset( $_GET['qty'] ) ? absint( (string) wp_unslash( $_GET['qty'] ) ) : 0;
            if ( $qty < 1 ) {
                va_set_flash( 'error', 'Érvénytelen mennyiség.' );
                self::redirect_buy_credits_page();
                return;
            }

            $packages = self::get_credit_packages();
            $allowed_qtys = [];
            foreach ( $packages as $p ) {
                $pq = absint( $p['qty'] ?? 0 );
                if ( $pq > 0 ) {
                    $allowed_qtys[] = $pq;
                }
            }
            if ( ! in_array( $qty, $allowed_qtys, true ) ) {
                va_set_flash( 'error', 'Érvénytelen csomag választás.' );
                self::redirect_buy_credits_page();
                return;
            }

            $user_id = get_current_user_id();
            $purchase_check = self::enforce_credit_package_purchase_rule( $user_id, $qty );
            if ( is_wp_error( $purchase_check ) ) {
                va_set_flash( 'warning', $purchase_check->get_error_message() );
                self::redirect_buy_credits_page();
                return;
            }

            $return_to = isset( $_GET['return_to'] ) ? sanitize_key( (string) wp_unslash( $_GET['return_to'] ) ) : 'buy';
            if ( ! in_array( $return_to, [ 'buy', 'submit' ], true ) ) {
                $return_to = 'buy';
            }

            $unit_price = (int) get_option( 'va_listing_price_after_free', 1990 );
            $total      = $unit_price * $qty;

            $matched = false;
            foreach ( $packages as $pkg ) {
                if ( $qty === (int) ( $pkg['qty'] ?? 0 ) ) {
                    $unit_price = $pkg['unit_price'];
                    $total      = $pkg['total'];
                    $matched    = true;
                    break;
                }
            }
            if ( ! $matched ) {
                va_set_flash( 'error', 'A választott csomag nem elérhető.' );
                self::redirect_buy_credits_page();
                return;
            }

            $payment_url = trim( (string) get_option( 'va_listing_payment_url', '' ) );
            $provider    = sanitize_key( (string) get_option( 'va_payment_provider', 'none' ) );
            $secret_key  = trim( (string) get_option( 'va_payment_secret_key', '' ) );
            $token       = wp_generate_password( 32, false, false );

            set_transient( 'va_credit_token_' . $token, [
                'user_id'    => $user_id,
                'qty'        => $qty,
                'amount'     => $total,
                'provider'   => $provider,
                'return_to'  => $return_to,
                'created_at' => time(),
            ], DAY_IN_SECONDS );

            $return_url = $return_to === 'submit'
                ? self::get_submit_page_url()
                : self::get_buy_credits_page_url();

            $success_url = add_query_arg([
                'va_credit_payment' => 'success',
                'token'             => rawurlencode( $token ),
            ], $return_url );
            $success_url .= ( strpos( $success_url, '?' ) === false ? '?' : '&' ) . 'session_id={CHECKOUT_SESSION_ID}';

            $cancel_url = add_query_arg([
                'va_credit_payment' => 'cancel',
                'token'             => rawurlencode( $token ),
            ], $return_url );

            if ( $provider === 'stripe' ) {
                if ( $secret_key === '' ) {
                    va_set_flash( 'error', 'Stripe titkos kulcs nincs beállítva az admin felületen.' );
                    self::redirect_buy_credits_page();
                    return;
                }

                $session_url = self::create_stripe_checkout_session( $token, $qty, $total, $success_url, $cancel_url, $user_id );
                if ( is_wp_error( $session_url ) ) {
                    va_set_flash( 'error', $session_url->get_error_message() );
                    self::redirect_buy_credits_page();
                    return;
                }

                // Stripe checkout.stripe.com külső domain – wp_safe_redirect blokkolta volna.
                wp_redirect( esc_url_raw( (string) $session_url ), 303 );
                exit;
            }

            if ( $payment_url === '' ) {
                va_set_flash( 'error', 'Fizetési szolgáltató nincs beállítva.' );
                self::redirect_buy_credits_page();
                return;
            }

            $checkout_url = add_query_arg([
                'intent'      => 'credit_purchase',
                'qty'         => $qty,
                'amount'      => $total,
                'token'       => $token,
                'success_url' => rawurlencode( $success_url ),
                'cancel_url'  => rawurlencode( $cancel_url ),
            ], $payment_url );

            wp_safe_redirect( $checkout_url );
            exit;
        } catch ( Throwable $e ) {
            $raw_message = (string) $e->getMessage();
            error_log( 'VA buy credits start fatal guard: ' . $raw_message . ' @ ' . $e->getFile() . ':' . $e->getLine() );
            va_set_flash( 'error', 'Technikai hiba történt a fizetés indításakor. Kérjük próbáld újra.' );
            self::redirect_buy_credits_page();
            return;
        }
    }

    /* ── Kredit fizetés callback ───────────────────────── */
    public static function handle_credit_payment_callback(): void {
        $state = isset( $_GET['va_credit_payment'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_credit_payment'] ) ) : '';
        if ( $state === '' ) return;

        $token = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
        if ( $token === '' ) return;

        // Ha a fizetés visszaérkezik, de a munkamenet lejárt → bejelentkezésre küldjük.
        if ( ! is_user_logged_in() ) {
            $current_url = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            wp_safe_redirect( wp_login_url( $current_url ) );
            exit;
        }

        $data = get_transient( 'va_credit_token_' . $token );
        if ( ! $data ) {
            $paid = get_transient( 'va_credit_paid_' . $token );
            if ( is_array( $paid ) && isset( $paid['user_id'] ) && (int) $paid['user_id'] === get_current_user_id() ) {
                if ( $state === 'success' ) {
                    $provider_expected = sanitize_key( (string) ( $paid['provider'] ?? get_option( 'va_payment_provider', 'none' ) ) );
                    $has_stripe_ids = ! empty( $paid['stripe_session_id'] ) || ! empty( $paid['stripe_payment_intent'] );

                    if ( $provider_expected === 'stripe' && ! $has_stripe_ids ) {
                        $session_id = isset( $_GET['session_id'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['session_id'] ) ) : '';
                        if ( $session_id !== '' ) {
                            $verify = self::verify_stripe_checkout_payment( $session_id, $token );
                            if ( is_array( $verify ) ) {
                                $sid = sanitize_text_field( (string) ( $verify['session_id'] ?? '' ) );
                                $pid = sanitize_text_field( (string) ( $verify['payment_intent'] ?? '' ) );
                                if ( $sid !== '' ) {
                                    $paid['stripe_session_id'] = $sid;
                                }
                                if ( $pid !== '' ) {
                                    $paid['stripe_payment_intent'] = $pid;
                                }
                                set_transient( 'va_credit_paid_' . $token, $paid, WEEK_IN_SECONDS );
                            }
                        }
                    }
                }

                $paid_diag_parts = [];
                if ( ! empty( $paid['stripe_session_id'] ) ) {
                    $paid_diag_parts[] = 'session: ' . sanitize_text_field( (string) $paid['stripe_session_id'] );
                }
                if ( ! empty( $paid['stripe_payment_intent'] ) ) {
                    $paid_diag_parts[] = 'payment: ' . sanitize_text_field( (string) $paid['stripe_payment_intent'] );
                }
                $paid_diag = empty( $paid_diag_parts ) ? '' : ' [' . implode( ' | ', $paid_diag_parts ) . ']';
                va_set_flash( 'info', 'A fizetés már feldolgozásra került.' . $paid_diag );
                $return_to = ( isset( $paid['return_to'] ) && $paid['return_to'] === 'submit' ) ? 'submit' : 'buy';
                if ( $return_to === 'submit' ) {
                    self::redirect_submit_page();
                }
                self::redirect_buy_credits_page();
                return;
            }

            va_set_flash( 'error', 'A fizetési session érvénytelen vagy lejárt.' );
            self::redirect_buy_credits_page();
            return;
        }

        if ( (int) ( $data['user_id'] ?? 0 ) !== get_current_user_id() ) {
            va_set_flash( 'error', 'A fizetési session nem ehhez a felhasználóhoz tartozik.' );
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
            $provider = sanitize_key( (string) get_option( 'va_payment_provider', 'none' ) );
            $provider_expected = sanitize_key( (string) ( $data['provider'] ?? $provider ) );
            $stripe_diag = '';

            if ( $provider_expected === 'stripe' ) {
                $session_id = isset( $_GET['session_id'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['session_id'] ) ) : '';
                if ( $session_id === '' ) {
                    va_set_flash( 'error', 'Stripe visszaigazolás hiányzik (session_id).' );
                    self::redirect_buy_credits_page();
                    return;
                }

                $verify = self::verify_stripe_checkout_payment( $session_id, $token );
                if ( is_wp_error( $verify ) ) {
                    va_set_flash( 'error', $verify->get_error_message() );
                    self::redirect_buy_credits_page();
                    return;
                }

                if ( is_array( $verify ) ) {
                    $sid = sanitize_text_field( (string) ( $verify['session_id'] ?? '' ) );
                    $pid = sanitize_text_field( (string) ( $verify['payment_intent'] ?? '' ) );
                    $rurl = esc_url_raw( (string) ( $verify['receipt_url'] ?? '' ) );
                    if ( $sid !== '' ) {
                        $data['stripe_session_id'] = $sid;
                    }
                    if ( $pid !== '' ) {
                        $data['stripe_payment_intent'] = $pid;
                    }
                    if ( $rurl !== '' ) {
                        $data['stripe_receipt_url'] = $rurl;
                    }
                    $parts = [];
                    if ( $sid !== '' ) $parts[] = 'session: ' . $sid;
                    if ( $pid !== '' ) $parts[] = 'payment: ' . $pid;
                    if ( ! empty( $parts ) ) {
                        $stripe_diag = ' [' . implode( ' | ', $parts ) . ']';
                    }
                }
            }

            $finalize = self::finalize_credit_purchase( $token, $data );
            if ( is_wp_error( $finalize ) ) {
                va_set_flash( 'error', $finalize->get_error_message() );
                self::redirect_buy_credits_page();
                return;
            }

            $qty = absint( $finalize['qty'] ?? 0 );
            $carryover_days = absint( $finalize['carryover_days'] ?? 0 );
            $carryover_value = absint( $finalize['carryover_value_ft'] ?? 0 );
            $carryover_msg = '';
            if ( $carryover_days > 0 ) {
                $carryover_msg = ' A korábbi csomag maradék értéke nem veszett el: +' . $carryover_days . ' nap jóváírva';
                if ( $carryover_value > 0 ) {
                    $carryover_msg .= ' (' . number_format( $carryover_value, 0, ',', ' ' ) . ' Ft érték)';
                }
                $carryover_msg .= '.';
            }
            if ( ! empty( $finalize['already'] ) ) {
                va_set_flash( 'success', 'Köszönjük vásárlást, a fizetés sikeresen megtörtént! A továbbiakban ellenőrizze az új előfizetési rangját. Amennyiben hibát észlel, haladéktalanul jelezze felénk.' . $carryover_msg );
            } else {
                va_set_flash( 'success', 'Köszönjük vásárlást, a fizetés sikeresen megtörtént! A továbbiakban ellenőrizze az új előfizetési rangját. Amennyiben hibát észlel, haladéktalanul jelezze felénk.' . $carryover_msg );
            }
            if ( $return_to === 'submit' ) {
                self::redirect_submit_page();
            }
            self::redirect_buy_credits_page();
        }
    }

    public static function handle_stripe_webhook(): void {
        $flag = isset( $_GET['va_stripe_webhook'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_stripe_webhook'] ) ) : '';
        if ( $flag !== '1' ) {
            return;
        }

        if ( strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) !== 'POST' ) {
            status_header( 405 );
            echo 'Method Not Allowed';
            exit;
        }

        $provider = sanitize_key( (string) get_option( 'va_payment_provider', 'none' ) );
        if ( $provider !== 'stripe' ) {
            status_header( 400 );
            echo 'Stripe provider is not active';
            exit;
        }

        $payload   = file_get_contents( 'php://input' );
        $signature = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? sanitize_text_field( (string) wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) : '';

        $event = self::parse_stripe_webhook_event( (string) $payload, $signature );
        if ( is_wp_error( $event ) ) {
            status_header( 400 );
            echo $event->get_error_message();
            exit;
        }

        $event_id = isset( $event['id'] ) ? sanitize_text_field( (string) $event['id'] ) : '';
        if ( $event_id !== '' ) {
            if ( get_transient( 'va_stripe_evt_' . $event_id ) ) {
                status_header( 200 );
                echo 'ok';
                exit;
            }
            set_transient( 'va_stripe_evt_' . $event_id, 1, WEEK_IN_SECONDS );
        }

        $type   = isset( $event['type'] ) ? (string) $event['type'] : '';
        $object = isset( $event['data']['object'] ) && is_array( $event['data']['object'] ) ? $event['data']['object'] : [];

        if ( $type === 'checkout.session.completed' ) {
            $status         = (string) ( $object['status'] ?? '' );
            $payment_status = (string) ( $object['payment_status'] ?? '' );
            $token          = (string) ( $object['client_reference_id'] ?? '' );
            $metadata       = isset( $object['metadata'] ) && is_array( $object['metadata'] ) ? $object['metadata'] : [];
            if ( $token === '' && isset( $metadata['token'] ) ) {
                $token = (string) $metadata['token'];
            }

            if ( $token !== '' && $status === 'complete' && $payment_status === 'paid' ) {
                $fallback = [
                    'user_id'   => absint( $metadata['user_id'] ?? 0 ),
                    'qty'       => absint( $metadata['qty'] ?? 0 ),
                    'provider'  => 'stripe',
                    'stripe_session_id' => sanitize_text_field( (string) ( $object['id'] ?? '' ) ),
                    'stripe_payment_intent' => sanitize_text_field( (string) ( $object['payment_intent'] ?? '' ) ),
                    'stripe_receipt_url' => '',
                    'return_to' => 'buy',
                ];
                self::finalize_credit_purchase( $token, $fallback );
            }
        }

        status_header( 200 );
        echo 'ok';
        exit;
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

    private static function create_stripe_checkout_session( string $token, int $qty, int $amount, string $success_url, string $cancel_url, int $user_id ) {
        $secret_key = trim( (string) get_option( 'va_payment_secret_key', '' ) );
        if ( $secret_key === '' ) {
            return new WP_Error( 'va_stripe_missing_secret', 'Stripe titkos kulcs hiányzik.' );
        }

        $amount_huf = absint( $amount );
        $qty = max( 1, absint( $qty ) );
        // Stripe ezen a fiókon HUF-ot fillérként kezeli (*100), ezért a minimum 2 Ft (= 175 fillér / 100).
        $huf_min_ft = 2;

        // Ha admin árbeállítás formátumhiba miatt túl kicsi jönne, számoljuk újra.
        if ( $amount_huf < $huf_min_ft ) {
            $base_price = max( 0, absint( get_option( 'va_listing_price_after_free', 1990 ) ) );
            $recalc = $base_price * $qty;
            if ( $recalc >= $huf_min_ft ) {
                $amount_huf = $recalc;
            }
        }

        if ( $amount_huf < $huf_min_ft ) {
            return new WP_Error(
                'va_stripe_amount_too_small_local',
                'A fizetési összeg túl alacsony. Aktuális: ' . $amount_huf . ' Ft. Állítsd a csomagárat legalább ' . $huf_min_ft . ' Ft értékre.'
            );
        }

        // Ezen a Stripe fiókon a HUF 2 tizedes-alapú (fillér szintű) egységként értelmezett,
        // ezért mindig *100-al küldjük: 1500 Ft → 150000 egység → Stripe 1500,00 Ft-ot számol.
        $stripe_unit_amount = $amount_huf * 100;

        $body = [
            'mode'                                            => 'payment',
            'success_url'                                     => $success_url,
            'cancel_url'                                      => $cancel_url,
            'client_reference_id'                             => $token,
            'metadata[token]'                                 => $token,
            'metadata[user_id]'                               => (string) $user_id,
            'metadata[purpose]'                               => 'credit_purchase',
            'metadata[qty]'                                   => (string) $qty,
            'line_items[0][price_data][currency]'             => 'huf',
            'line_items[0][price_data][unit_amount]'          => $stripe_unit_amount,
            'line_items[0][price_data][product_data][name]'   => $qty . ' kredit csomag',
            'line_items[0][quantity]'                         => 1,
        ];

        $response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', [
            'timeout' => 45,
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body,
        ] );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'va_stripe_http_error', 'Stripe kapcsolat sikertelen: ' . $response->get_error_message() );
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        $json = json_decode( (string) wp_remote_retrieve_body( $response ), true );

        if ( $code < 200 || $code >= 300 || ! is_array( $json ) ) {
            $details = '';
            if ( is_array( $json ) && isset( $json['error'] ) && is_array( $json['error'] ) ) {
                $err = $json['error'];
                $parts = [];
                if ( ! empty( $err['type'] ) ) {
                    $parts[] = 'type: ' . sanitize_text_field( (string) $err['type'] );
                }
                if ( ! empty( $err['code'] ) ) {
                    $parts[] = 'code: ' . sanitize_text_field( (string) $err['code'] );
                }
                if ( ! empty( $err['param'] ) ) {
                    $parts[] = 'param: ' . sanitize_text_field( (string) $err['param'] );
                }
                if ( ! empty( $err['message'] ) ) {
                    $parts[] = sanitize_text_field( (string) $err['message'] );
                }
                if ( ! empty( $parts ) ) {
                    $details = ' (' . implode( ' | ', $parts ) . ')';
                }
            }
            $details .= ' [sent_unit_amount=' . $stripe_unit_amount . ' (huf_ft=' . $amount_huf . ')]';
            error_log( 'VA Stripe create session error: code=' . $code . $details );
            return new WP_Error( 'va_stripe_bad_response', 'Stripe session létrehozás sikertelen. Kérjük próbáld újra később.' );
        }

        $url = isset( $json['url'] ) ? esc_url_raw( (string) $json['url'] ) : '';
        if ( $url === '' ) {
            return new WP_Error( 'va_stripe_missing_url', 'Stripe nem adott vissza fizetési URL-t.' );
        }

        return $url;
    }

    private static function verify_stripe_checkout_payment( string $session_id, string $expected_token ) {
        $secret_key = trim( (string) get_option( 'va_payment_secret_key', '' ) );
        if ( $secret_key === '' ) {
            return new WP_Error( 'va_stripe_missing_secret', 'Stripe titkos kulcs hiányzik.' );
        }

        $response = wp_remote_get( 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode( $session_id ), [
            'timeout' => 45,
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
            ],
        ] );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'va_stripe_http_error', 'Stripe ellenőrzés sikertelen: ' . $response->get_error_message() );
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        $json = json_decode( (string) wp_remote_retrieve_body( $response ), true );
        if ( $code < 200 || $code >= 300 || ! is_array( $json ) ) {
            return new WP_Error( 'va_stripe_bad_response', 'Stripe fizetés ellenőrzés sikertelen.' );
        }

        $status         = (string) ( $json['status'] ?? '' );
        $payment_status = (string) ( $json['payment_status'] ?? '' );
        $ref_token      = (string) ( $json['client_reference_id'] ?? '' );
        if ( $ref_token === '' && isset( $json['metadata']['token'] ) ) {
            $ref_token = (string) $json['metadata']['token'];
        }

        if ( $ref_token !== $expected_token ) {
            return new WP_Error( 'va_stripe_token_mismatch', 'Stripe session token eltérés.' );
        }

        if ( $status !== 'complete' || $payment_status !== 'paid' ) {
            return new WP_Error( 'va_stripe_not_paid', 'A Stripe fizetés még nincs sikeresen lezárva.' );
        }

        $receipt_url = '';
        $payment_intent = sanitize_text_field( (string) ( $json['payment_intent'] ?? '' ) );
        if ( $payment_intent !== '' ) {
            $pi_response = wp_remote_get( 'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $payment_intent ), [
                'timeout' => 45,
                'headers' => [
                    'Authorization' => 'Bearer ' . $secret_key,
                ],
            ] );
            if ( ! is_wp_error( $pi_response ) ) {
                $pi_code = (int) wp_remote_retrieve_response_code( $pi_response );
                $pi_json = json_decode( (string) wp_remote_retrieve_body( $pi_response ), true );
                if ( $pi_code >= 200 && $pi_code < 300 && is_array( $pi_json ) ) {
                    $charges = $pi_json['charges']['data'] ?? [];
                    if ( is_array( $charges ) && ! empty( $charges[0]['receipt_url'] ) ) {
                        $receipt_url = esc_url_raw( (string) $charges[0]['receipt_url'] );
                    }
                }
            }
        }

        return [
            'session_id'     => sanitize_text_field( (string) ( $json['id'] ?? $session_id ) ),
            'payment_intent' => $payment_intent,
            'amount_total'   => absint( $json['amount_total'] ?? 0 ),
            'currency'       => sanitize_key( (string) ( $json['currency'] ?? '' ) ),
            'receipt_url'    => $receipt_url,
        ];
    }

    public static function backfill_credit_purchase_history_from_stripe( int $max_pages = 20, int $per_page = 100 ): array {
        $secret_key = trim( (string) get_option( 'va_payment_secret_key', '' ) );
        if ( $secret_key === '' ) {
            return [
                'ok'      => false,
                'message' => 'Stripe titkos kulcs hiányzik.',
                'pages'   => 0,
                'events'  => 0,
                'imported'=> 0,
            ];
        }

        $per_page = max( 1, min( 100, $per_page ) );
        $max_pages = max( 1, min( 50, $max_pages ) );
        $starting_after = '';
        $pages = 0;
        $events_seen = 0;
        $imported = 0;

        while ( $pages < $max_pages ) {
            $query = [
                'type'  => 'checkout.session.completed',
                'limit' => $per_page,
            ];
            if ( $starting_after !== '' ) {
                $query['starting_after'] = $starting_after;
            }

            $response = wp_remote_get( add_query_arg( $query, 'https://api.stripe.com/v1/events' ), [
                'timeout' => 45,
                'headers' => [
                    'Authorization' => 'Bearer ' . $secret_key,
                ],
            ] );

            if ( is_wp_error( $response ) ) {
                return [
                    'ok'      => false,
                    'message' => 'Stripe eseménylista lekérése sikertelen: ' . $response->get_error_message(),
                    'pages'   => $pages,
                    'events'  => $events_seen,
                    'imported'=> $imported,
                ];
            }

            $code = (int) wp_remote_retrieve_response_code( $response );
            $json = json_decode( (string) wp_remote_retrieve_body( $response ), true );
            if ( $code < 200 || $code >= 300 || ! is_array( $json ) ) {
                return [
                    'ok'      => false,
                    'message' => 'Stripe eseménylista érvénytelen választ adott.',
                    'pages'   => $pages,
                    'events'  => $events_seen,
                    'imported'=> $imported,
                ];
            }

            $pages++;
            $events = is_array( $json['data'] ?? null ) ? $json['data'] : [];
            if ( empty( $events ) ) {
                break;
            }

            foreach ( $events as $event ) {
                if ( ! is_array( $event ) ) {
                    continue;
                }

                $events_seen++;
                $starting_after = sanitize_text_field( (string) ( $event['id'] ?? $starting_after ) );
                $object = $event['data']['object'] ?? null;
                if ( ! is_array( $object ) ) {
                    continue;
                }

                $status = (string) ( $object['status'] ?? '' );
                $payment_status = (string) ( $object['payment_status'] ?? '' );
                if ( $status !== 'complete' || $payment_status !== 'paid' ) {
                    continue;
                }

                $metadata = is_array( $object['metadata'] ?? null ) ? $object['metadata'] : [];
                $user_id  = absint( $metadata['user_id'] ?? 0 );
                $qty      = absint( $metadata['qty'] ?? 0 );
                if ( $user_id <= 0 || $qty <= 0 ) {
                    continue;
                }

                $currency = sanitize_key( (string) ( $object['currency'] ?? 'huf' ) );
                $amount_total = absint( $object['amount_total'] ?? 0 );
                $amount_ft = $currency === 'huf' ? (int) round( $amount_total / 100 ) : $amount_total;
                $payment_intent = sanitize_text_field( (string) ( $object['payment_intent'] ?? '' ) );
                $receipt_url = '';
                if ( $payment_intent !== '' ) {
                    $pi_response = wp_remote_get( 'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $payment_intent ), [
                        'timeout' => 45,
                        'headers' => [
                            'Authorization' => 'Bearer ' . $secret_key,
                        ],
                    ] );
                    if ( ! is_wp_error( $pi_response ) ) {
                        $pi_code = (int) wp_remote_retrieve_response_code( $pi_response );
                        $pi_json = json_decode( (string) wp_remote_retrieve_body( $pi_response ), true );
                        if ( $pi_code >= 200 && $pi_code < 300 && is_array( $pi_json ) ) {
                            $charges = $pi_json['charges']['data'] ?? [];
                            if ( is_array( $charges ) && ! empty( $charges[0]['receipt_url'] ) ) {
                                $receipt_url = esc_url_raw( (string) $charges[0]['receipt_url'] );
                            }
                        }
                    }
                }

                $before = count( self::get_credit_purchase_history( $user_id ) );
                self::record_credit_purchase_history( $user_id, [
                    'token'                 => sanitize_text_field( (string) ( $object['client_reference_id'] ?? ( $metadata['token'] ?? '' ) ) ),
                    'user_id'               => $user_id,
                    'qty'                   => $qty,
                    'amount'                => $amount_ft,
                    'provider'              => 'stripe',
                    'return_to'             => 'buy',
                    'plan_slug'             => self::get_target_plan_slug_for_qty( $qty ),
                    'stripe_session_id'     => sanitize_text_field( (string) ( $object['id'] ?? '' ) ),
                    'stripe_payment_intent' => $payment_intent,
                    'stripe_receipt_url'    => $receipt_url,
                    'initiated_at'          => absint( $object['created'] ?? $event['created'] ?? 0 ),
                    'completed_at'          => absint( $event['created'] ?? $object['created'] ?? 0 ),
                ] );
                $after = count( self::get_credit_purchase_history( $user_id ) );
                if ( $after > $before ) {
                    $imported++;
                }
            }

            if ( empty( $json['has_more'] ) ) {
                break;
            }
        }

        update_option( 'va_stripe_purchase_history_last_sync', [
            'ran_at'   => current_time( 'mysql' ),
            'pages'    => $pages,
            'events'   => $events_seen,
            'imported' => $imported,
        ], false );

        return [
            'ok'       => true,
            'message'  => 'Stripe vásárlási előzmények szinkronizálva.',
            'pages'    => $pages,
            'events'   => $events_seen,
            'imported' => $imported,
        ];
    }

    private static function parse_stripe_webhook_event( string $payload, string $signature ) {
        $secret = trim( (string) get_option( 'va_payment_webhook_secret', '' ) );
        if ( $secret === '' ) {
            return new WP_Error( 'va_stripe_missing_webhook_secret', 'Stripe webhook secret hiányzik.' );
        }
        if ( $signature === '' ) {
            return new WP_Error( 'va_stripe_missing_signature', 'Stripe-Signature fejléc hiányzik.' );
        }

        $parts = [];
        foreach ( explode( ',', $signature ) as $chunk ) {
            $pair = explode( '=', trim( $chunk ), 2 );
            if ( count( $pair ) === 2 ) {
                $parts[ $pair[0] ][] = $pair[1];
            }
        }

        $timestamp = isset( $parts['t'][0] ) ? (string) $parts['t'][0] : '';
        $v1_list   = isset( $parts['v1'] ) && is_array( $parts['v1'] ) ? $parts['v1'] : [];
        if ( $timestamp === '' || empty( $v1_list ) ) {
            return new WP_Error( 'va_stripe_bad_signature_header', 'Stripe-Signature fejléc érvénytelen.' );
        }

        if ( ! ctype_digit( $timestamp ) ) {
            return new WP_Error( 'va_stripe_bad_timestamp', 'Stripe-Signature timestamp érvénytelen.' );
        }

        $ts = (int) $timestamp;
        $tolerance = absint( get_option( 'va_stripe_webhook_tolerance_sec', 300 ) );
        if ( $tolerance < 30 ) {
            $tolerance = 30;
        }
        if ( $tolerance > 3600 ) {
            $tolerance = 3600;
        }
        if ( abs( time() - $ts ) > $tolerance ) {
            return new WP_Error( 'va_stripe_sig_too_old', 'Stripe webhook időbélyeg túl régi vagy túl jövőbeli.' );
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected       = hash_hmac( 'sha256', $signed_payload, $secret );
        $valid          = false;
        foreach ( $v1_list as $candidate ) {
            if ( hash_equals( $expected, (string) $candidate ) ) {
                $valid = true;
                break;
            }
        }

        if ( ! $valid ) {
            return new WP_Error( 'va_stripe_sig_mismatch', 'Stripe webhook aláírás ellenőrzés sikertelen.' );
        }

        $event = json_decode( $payload, true );
        if ( ! is_array( $event ) ) {
            return new WP_Error( 'va_stripe_invalid_json', 'Stripe webhook JSON érvénytelen.' );
        }

        return $event;
    }

    private static function finalize_credit_purchase( string $token, array $fallback_data = [] ) {
        $paid_key = 'va_credit_paid_' . $token;
        $paid     = get_transient( $paid_key );
        if ( is_array( $paid ) && ! empty( $paid['user_id'] ) && ! empty( $paid['qty'] ) ) {
            self::record_credit_purchase_history( (int) $paid['user_id'], [
                'token'                => $token,
                'user_id'              => (int) $paid['user_id'],
                'qty'                  => (int) $paid['qty'],
                'amount'               => absint( $paid['amount'] ?? 0 ),
                'provider'             => sanitize_key( (string) ( $paid['provider'] ?? 'stripe' ) ),
                'return_to'            => (string) ( $paid['return_to'] ?? 'buy' ),
                'plan_slug'            => sanitize_key( (string) ( $paid['plan_slug'] ?? '' ) ),
                'stripe_session_id'    => sanitize_text_field( (string) ( $paid['stripe_session_id'] ?? '' ) ),
                'stripe_payment_intent'=> sanitize_text_field( (string) ( $paid['stripe_payment_intent'] ?? '' ) ),
                'stripe_receipt_url'   => esc_url_raw( (string) ( $paid['stripe_receipt_url'] ?? '' ) ),
                'initiated_at'         => absint( $paid['initiated_at'] ?? 0 ),
                'completed_at'         => absint( $paid['completed_at'] ?? current_time( 'timestamp' ) ),
                'upgrade_applied'      => ! empty( $paid['upgrade_applied'] ) ? 1 : 0,
                'carryover_days'       => absint( $paid['carryover_days'] ?? 0 ),
                'carryover_value_ft'   => absint( $paid['carryover_value_ft'] ?? 0 ),
                'prev_plan_slug'       => sanitize_key( (string) ( $paid['prev_plan_slug'] ?? '' ) ),
            ] );
            return [
                'already'   => true,
                'user_id'   => (int) $paid['user_id'],
                'qty'       => (int) $paid['qty'],
                'return_to' => (string) ( $paid['return_to'] ?? 'buy' ),
                'stripe_session_id'     => sanitize_text_field( (string) ( $paid['stripe_session_id'] ?? '' ) ),
                'stripe_payment_intent' => sanitize_text_field( (string) ( $paid['stripe_payment_intent'] ?? '' ) ),
                'stripe_receipt_url'    => esc_url_raw( (string) ( $paid['stripe_receipt_url'] ?? '' ) ),
                'upgrade_applied'       => ! empty( $paid['upgrade_applied'] ),
                'carryover_days'        => absint( $paid['carryover_days'] ?? 0 ),
                'carryover_value_ft'    => absint( $paid['carryover_value_ft'] ?? 0 ),
                'prev_plan_slug'        => sanitize_key( (string) ( $paid['prev_plan_slug'] ?? '' ) ),
                'plan_slug'             => sanitize_key( (string) ( $paid['plan_slug'] ?? '' ) ),
            ];
        }

        $data = get_transient( 'va_credit_token_' . $token );
        if ( ! is_array( $data ) ) {
            $data = $fallback_data;
        }

        $user_id   = absint( $data['user_id'] ?? 0 );
        $qty       = absint( $data['qty'] ?? 0 );
        $amount    = absint( $data['amount'] ?? 0 );
        $return_to = isset( $data['return_to'] ) && $data['return_to'] === 'submit' ? 'submit' : 'buy';
        $provider  = sanitize_key( (string) ( $data['provider'] ?? get_option( 'va_payment_provider', 'none' ) ) );
        $stripe_session_id     = sanitize_text_field( (string) ( $data['stripe_session_id'] ?? '' ) );
        $stripe_payment_intent = sanitize_text_field( (string) ( $data['stripe_payment_intent'] ?? '' ) );
        $stripe_receipt_url    = esc_url_raw( (string) ( $data['stripe_receipt_url'] ?? '' ) );
        $initiated_at          = absint( $data['created_at'] ?? time() );

        if ( $user_id <= 0 || $qty <= 0 ) {
            return new WP_Error( 'va_credit_missing_payload', 'A fizetés adatai hiányosak, kredit nem írható jóvá.' );
        }

        $current = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );
        update_user_meta( $user_id, 'va_listing_credits', $current + $qty );

        // Csomag frissítése a vásárolt csomag rangja alapján, lejárattal.
        $new_plan = self::normalize_purchase_plan_slug( self::get_target_plan_slug_for_qty( $qty ) );
        $cur_plan = class_exists( 'VA_User_Roles' )
            ? VA_User_Roles::get_user_plan( $user_id )
            : sanitize_key( (string) get_user_meta( $user_id, 'va_plan', true ) );
        $cur_plan = self::normalize_purchase_plan_slug( $cur_plan );
        $cur_rank = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_rank( $cur_plan ) : 0;
        $new_rank = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_rank( $new_plan ) : 0;

        $upgrade_applied    = false;
        $carryover_days     = 0;
        $carryover_value_ft = 0;
        $prev_plan_slug     = $cur_plan;

        // Egyszerre csak egy aktív termék: csak magasabb csomag írhatja felül a jelenlegit.
        if ( $new_rank > $cur_rank ) {
            $old_expires_at = (int) get_user_meta( $user_id, 'va_plan_expires_at', true );
            $old_duration_days = self::get_plan_base_duration_days( $user_id, $cur_plan );
            $new_duration_days = self::get_plan_base_duration_days( $user_id, $new_plan );

            $old_total_price = self::get_plan_price_total_for_slug( $cur_plan );
            $new_total_price = $amount > 0 ? $amount : self::get_plan_price_total_for_slug( $new_plan );
            if ( $new_total_price <= 0 ) {
                $new_total_price = 1;
            }

            $remaining_seconds = ( $old_expires_at > time() ) ? ( $old_expires_at - time() ) : 0;
            if ( $cur_rank > 0 && $remaining_seconds > 0 && $old_total_price > 0 ) {
                $old_daily_value = $old_total_price / max( 1, $old_duration_days );
                $remaining_days_float = $remaining_seconds / DAY_IN_SECONDS;
                $carryover_value_ft = (int) round( $old_daily_value * $remaining_days_float );

                if ( $carryover_value_ft > 0 ) {
                    $new_daily_value = $new_total_price / max( 1, $new_duration_days );
                    if ( $new_daily_value > 0 ) {
                        $carryover_days = (int) ceil( $carryover_value_ft / $new_daily_value );
                        if ( $carryover_days > 0 ) {
                            $carryover_days = max( 1, $carryover_days );
                            $carryover_days = min( 365, $carryover_days );
                        }
                    }
                }
            }

            update_user_meta( $user_id, 'va_plan', $new_plan );
            $final_duration_days = max( 1, $new_duration_days + $carryover_days );
            update_user_meta( $user_id, 'va_plan_expires_at', time() + ( $final_duration_days * DAY_IN_SECONDS ) );
            // Lejárati figyelmeztető flagek törlése az új ciklus kezdetekor.
            foreach ( [ 1, 7, 30 ] as $_t ) {
                delete_user_meta( $user_id, 'va_plan_expiry_warned_' . $_t );
            }
            delete_user_meta( $user_id, 'va_plan_expired_admin_notified' );
            delete_user_meta( $user_id, 'va_plan_expired_user_notified' );
            $upgrade_applied = true;

            if ( class_exists( 'VA_Mailer' ) && method_exists( 'VA_Mailer', 'send_plan_upgrade_notice' ) ) {
                VA_Mailer::send_plan_upgrade_notice( $user_id, [
                    'prev_plan_slug'     => $prev_plan_slug,
                    'new_plan_slug'      => $new_plan,
                    'carryover_days'     => $carryover_days,
                    'carryover_value_ft' => $carryover_value_ft,
                    'new_expires_at'     => (int) get_user_meta( $user_id, 'va_plan_expires_at', true ),
                ] );
            }
        }

        // Vásárláskor a kártyán megadott kiemelési gyakoriság legyen a mérvadó.
        self::sync_user_card_cooldown( $user_id, $qty, $new_plan );

        $completed_at = current_time( 'timestamp' );
        self::record_credit_purchase_history( $user_id, [
            'token'                 => $token,
            'user_id'               => $user_id,
            'qty'                   => $qty,
            'amount'                => $amount,
            'provider'              => $provider,
            'return_to'             => $return_to,
            'plan_slug'             => $new_plan,
            'stripe_session_id'     => $stripe_session_id,
            'stripe_payment_intent'  => $stripe_payment_intent,
            'stripe_receipt_url'    => $stripe_receipt_url,
            'initiated_at'          => $initiated_at,
            'completed_at'          => $completed_at,
            'upgrade_applied'       => $upgrade_applied ? 1 : 0,
            'carryover_days'        => $carryover_days,
            'carryover_value_ft'    => $carryover_value_ft,
            'prev_plan_slug'        => $prev_plan_slug,
        ] );

        $paid_data = [
            'user_id'               => $user_id,
            'qty'                   => $qty,
            'amount'                => $amount,
            'provider'              => $provider,
            'return_to'             => $return_to,
            'plan_slug'             => $new_plan,
            'initiated_at'          => $initiated_at,
            'completed_at'          => $completed_at,
            'upgrade_applied'       => $upgrade_applied ? 1 : 0,
            'carryover_days'        => $carryover_days,
            'carryover_value_ft'    => $carryover_value_ft,
            'prev_plan_slug'        => $prev_plan_slug,
        ];
        if ( $stripe_session_id !== '' ) {
            $paid_data['stripe_session_id'] = $stripe_session_id;
        }
        if ( $stripe_payment_intent !== '' ) {
            $paid_data['stripe_payment_intent'] = $stripe_payment_intent;
        }
        if ( $stripe_receipt_url !== '' ) {
            $paid_data['stripe_receipt_url'] = $stripe_receipt_url;
        }

        set_transient( $paid_key, $paid_data, WEEK_IN_SECONDS );
        delete_transient( 'va_credit_token_' . $token );

        if ( class_exists( 'VA_User_Roles' ) ) {
            delete_transient( 'va_enforce_ok_' . $user_id );
            VA_User_Roles::enforce_plan_limits( $user_id );
        }

        return [
            'already'   => false,
            'user_id'   => $user_id,
            'qty'       => $qty,
            'return_to' => $return_to,
            'stripe_session_id'     => $stripe_session_id,
            'stripe_payment_intent' => $stripe_payment_intent,
            'stripe_receipt_url'    => $stripe_receipt_url,
            'upgrade_applied'       => $upgrade_applied,
            'carryover_days'        => $carryover_days,
            'carryover_value_ft'    => $carryover_value_ft,
            'prev_plan_slug'        => $prev_plan_slug,
            'plan_slug'             => $new_plan,
        ];
    }

    public static function get_credit_purchase_history( int $user_id ): array {
        $history = get_user_meta( $user_id, 'va_credit_purchase_history', true );
        if ( ! is_array( $history ) ) {
            return [];
        }

        $normalized = [];
        $did_update = false;
        foreach ( $history as $entry ) {
            if ( ! is_array( $entry ) ) {
                continue;
            }

            $plan_slug = sanitize_key( (string) ( $entry['plan_slug'] ?? '' ) );
            $qty       = absint( $entry['qty'] ?? 0 );
            if ( $plan_slug === '' && $qty > 0 ) {
                $plan_slug = self::get_target_plan_slug_for_qty( $qty );
                if ( $plan_slug !== '' ) {
                    $did_update = true;
                }
            }

            $normalized[] = [
                'token'                => sanitize_text_field( (string) ( $entry['token'] ?? '' ) ),
                'user_id'              => absint( $entry['user_id'] ?? $user_id ),
                'qty'                  => $qty,
                'amount'               => absint( $entry['amount'] ?? 0 ),
                'provider'             => sanitize_key( (string) ( $entry['provider'] ?? 'stripe' ) ),
                'return_to'            => sanitize_key( (string) ( $entry['return_to'] ?? 'buy' ) ),
                'plan_slug'            => $plan_slug,
                'stripe_session_id'    => sanitize_text_field( (string) ( $entry['stripe_session_id'] ?? '' ) ),
                'stripe_payment_intent'=> sanitize_text_field( (string) ( $entry['stripe_payment_intent'] ?? '' ) ),
                'stripe_receipt_url'   => esc_url_raw( (string) ( $entry['stripe_receipt_url'] ?? '' ) ),
                'initiated_at'         => absint( $entry['initiated_at'] ?? 0 ),
                'completed_at'         => absint( $entry['completed_at'] ?? 0 ),
                'upgrade_applied'      => ! empty( $entry['upgrade_applied'] ) ? 1 : 0,
                'carryover_days'       => absint( $entry['carryover_days'] ?? 0 ),
                'carryover_value_ft'   => absint( $entry['carryover_value_ft'] ?? 0 ),
                'prev_plan_slug'       => sanitize_key( (string) ( $entry['prev_plan_slug'] ?? '' ) ),
            ];
        }

        usort( $normalized, static function( array $a, array $b ): int {
            return ( $b['completed_at'] ?? 0 ) <=> ( $a['completed_at'] ?? 0 );
        } );

        if ( $did_update ) {
            update_user_meta( $user_id, 'va_credit_purchase_history', $normalized );
        }

        return $normalized;
    }

    private static function record_credit_purchase_history( int $user_id, array $entry ): void {
        if ( $user_id <= 0 ) {
            return;
        }

        $history = self::get_credit_purchase_history( $user_id );
        $token   = sanitize_text_field( (string) ( $entry['token'] ?? '' ) );
        $session = sanitize_text_field( (string) ( $entry['stripe_session_id'] ?? '' ) );
        $intent  = sanitize_text_field( (string) ( $entry['stripe_payment_intent'] ?? '' ) );

        foreach ( $history as $index => $existing ) {
            if ( $token !== '' && ( $existing['token'] ?? '' ) === $token ) {
                $merged = $existing;
                foreach ( $entry as $key => $value ) {
                    if ( $value !== '' && $value !== null ) {
                        $merged[ $key ] = $value;
                    }
                }
                $history[ $index ] = $merged;
                update_user_meta( $user_id, 'va_credit_purchase_history', $history );
                return;
            }
            if ( $session !== '' && ( $existing['stripe_session_id'] ?? '' ) === $session ) {
                $merged = $existing;
                foreach ( $entry as $key => $value ) {
                    if ( $value !== '' && $value !== null ) {
                        $merged[ $key ] = $value;
                    }
                }
                $history[ $index ] = $merged;
                update_user_meta( $user_id, 'va_credit_purchase_history', $history );
                return;
            }
            if ( $intent !== '' && ( $existing['stripe_payment_intent'] ?? '' ) === $intent ) {
                $merged = $existing;
                foreach ( $entry as $key => $value ) {
                    if ( $value !== '' && $value !== null ) {
                        $merged[ $key ] = $value;
                    }
                }
                $history[ $index ] = $merged;
                update_user_meta( $user_id, 'va_credit_purchase_history', $history );
                return;
            }
        }

        $entry['logged_at'] = current_time( 'mysql' );
        $entry['logged_ts'] = current_time( 'timestamp' );
        $history[] = $entry;

        usort( $history, static function( array $a, array $b ): int {
            return ( $b['completed_at'] ?? $b['logged_ts'] ?? 0 ) <=> ( $a['completed_at'] ?? $a['logged_ts'] ?? 0 );
        } );

        $history = array_slice( $history, 0, 100 );
        update_user_meta( $user_id, 'va_credit_purchase_history', $history );
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
        $optic_zoom        = sanitize_text_field( wp_unslash( $_POST['optic_zoom'] ?? '' ) );
        $optic_objective_min = max( 0, intval( $_POST['optic_objective_min'] ?? 0 ) );
        $optic_objective_max = max( 0, intval( $_POST['optic_objective_max'] ?? 0 ) );
        $dog_age_min       = max( 0, intval( $_POST['dog_age_min'] ?? 0 ) );
        $dog_age_max       = max( 0, intval( $_POST['dog_age_max'] ?? 0 ) );
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
            'brand','model','body_type','fuel_type','optic_zoom','optic_objective_min','optic_objective_max','dog_age_min','dog_age_max','year_min','year_max','mileage_min','mileage_max',
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

        if ( $optic_zoom !== '' ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_oz ON (pm_oz.post_id = p.ID AND pm_oz.meta_key = 'va_optic_zoom')";
            $where[] = 'pm_oz.meta_value LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $optic_zoom ) . '%';
        }

        if ( $optic_objective_min > 0 || $optic_objective_max > 0 ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_oo ON (pm_oo.post_id = p.ID AND pm_oo.meta_key = 'va_optic_objective')";
            if ( $optic_objective_min > 0 ) { $where[] = 'CAST(pm_oo.meta_value AS UNSIGNED) >= %d'; $params[] = $optic_objective_min; }
            if ( $optic_objective_max > 0 ) { $where[] = 'CAST(pm_oo.meta_value AS UNSIGNED) <= %d'; $params[] = $optic_objective_max; }
        }

        if ( $dog_age_min > 0 || $dog_age_max > 0 ) {
            $meta_joins[] = "LEFT JOIN {$wpdb->postmeta} pm_dog ON (pm_dog.post_id = p.ID AND pm_dog.meta_key = 'va_dog_age_months')";
            if ( $dog_age_min > 0 ) { $where[] = 'CAST(pm_dog.meta_value AS UNSIGNED) >= %d'; $params[] = $dog_age_min; }
            if ( $dog_age_max > 0 ) { $where[] = 'CAST(pm_dog.meta_value AS UNSIGNED) <= %d'; $params[] = $dog_age_max; }
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

        if ( $sort === 'price_asc' ) {
            // Ár rendezésnél legyen tiszta ár szerinti sorrend
            $order_sql = $order_prefix . 'lm.price ASC, p.post_date DESC';
        } elseif ( $sort === 'price_desc' ) {
            $order_sql = $order_prefix . 'lm.price DESC, p.post_date DESC';
        } elseif ( $sort === 'views' ) {
            $order_sql = $order_prefix . 'lm.featured DESC, lm.views DESC, p.post_date DESC';
        } else {
            $order_sql = $order_prefix . 'lm.featured DESC, p.post_date DESC';
        }

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

    /* ── Cím autocomplete (város/irányítószám/utca) ───── */
    public static function address_suggest() {
        check_ajax_referer( 'va_address_suggest', 'nonce' );

        if ( self::is_rate_limited( 'address_suggest', 120, 60 ) ) {
            wp_send_json_error( [ 'message' => 'Túl sok kérés. Kérjük várjon egy percet.' ], 429 );
        }

        $q = strtolower( sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) ) );
        $limit = max( 5, min( 20, absint( $_POST['limit'] ?? 10 ) ) );

        $len = function_exists( 'mb_strlen' ) ? mb_strlen( $q, 'UTF-8' ) : strlen( $q );
        if ( $len < 3 ) {
            wp_send_json_success( [] );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'va_hu_address';
        $rows  = [];

        $like = '%' . $wpdb->esc_like( $q ) . '%';
        $prefix = $wpdb->esc_like( $q ) . '%';

        $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
        if ( $exists ) {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT postal_code, city, street
                     FROM {$table}
                     WHERE search_key LIKE %s
                     ORDER BY CASE WHEN search_key LIKE %s THEN 0 ELSE 1 END, city ASC, street ASC
                     LIMIT %d",
                    $like,
                    $prefix,
                    $limit
                ),
                ARRAY_A
            );
        }

        if ( empty( $rows ) ) {
            $json_file = VA_PLUGIN_DIR . 'includes/hu-address-seed.json';
            if ( file_exists( $json_file ) ) {
                $data = json_decode( (string) file_get_contents( $json_file ), true );
                $records = is_array( $data['records'] ?? null ) ? $data['records'] : [];
                foreach ( $records as $r ) {
                    $postal = (string) ( $r['postal_code'] ?? '' );
                    $city   = (string) ( $r['city'] ?? '' );
                    $street = (string) ( $r['street'] ?? '' );
                    $key    = strtolower( trim( $postal . ' ' . $city . ' ' . $street ) );
                    if ( $key !== '' && strpos( $key, $q ) !== false ) {
                        $rows[] = [
                            'postal_code' => $postal,
                            'city'        => $city,
                            'street'      => $street,
                        ];
                    }
                    if ( count( $rows ) >= $limit ) {
                        break;
                    }
                }
            }
        }

        $out = [];
        foreach ( $rows as $r ) {
            $postal = trim( (string) ( $r['postal_code'] ?? '' ) );
            $city   = trim( (string) ( $r['city'] ?? '' ) );
            $street = trim( (string) ( $r['street'] ?? '' ) );
            if ( $city === '' || $street === '' ) {
                continue;
            }
            $out[] = [
                'postal_code' => $postal,
                'city'        => $city,
                'street'      => $street,
                'label'       => trim( $street . ', ' . $city . ( $postal !== '' ? ' ' . $postal : '' ) ),
            ];
        }

        wp_send_json_success( $out );
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

    /* ── Hirdetés frissítése (sorrendváltoztatás nélkül) ────── */
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
            'post_modified'     => $now,
            'post_modified_gmt' => get_gmt_from_date( $now ),
        ] );
        update_post_meta( $post_id, 'va_active_since', current_time( 'timestamp' ) );
        wp_send_json_success( [ 'message' => 'Hirdetés frissítve. A listasorrend nem változik.' ] );
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

        if ( class_exists( 'VA_User_Roles' ) ) {
            $plan = VA_User_Roles::get_user_plan( $user_id );
            if ( VA_User_Roles::get_plan_rank( $plan ) < VA_User_Roles::get_plan_rank( 'gold' ) ) {
                wp_send_json_error( [ 'message' => 'Az akciós ár csak Gold, Platinum vagy Üzleti csomagban állítható. Vásároljon nagyobb előfizetést.' ] );
            }
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
