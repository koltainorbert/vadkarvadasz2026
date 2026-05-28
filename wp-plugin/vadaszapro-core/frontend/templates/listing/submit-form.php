<?php
/**
 * Template: Hirdetés feladás form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$site_type = sanitize_key( (string) get_option( 'va_site_type', 'vadaszat' ) );

/* ── Helper: egyes mező HTML kimenete ──────────────── */
if ( ! function_exists( 'self_render_listing_field' ) ) {
    function self_render_listing_field( string $key, string $ph, string $req_attr, array $categories, array $counties, array $conditions, array $brands = [], array $body_types = [], array $brand_models = [], string $site_type = 'vadaszat', array $ev = [] ): void {
        $val = $ev[ $key ] ?? '';
        switch ( $key ) {
            case 'title':
                echo '<input type="text" id="va-title" name="title" class="va-input" maxlength="150"' . $req_attr . ' placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'category':
                echo '<select name="category" id="va-category" class="va-select"' . $req_attr . '>';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $categories as $cat ) {
                    $indent   = $cat->parent ? '&nbsp;&nbsp;' : '';
                    $selected = selected( (int) $val, $cat->term_id, false );
                    echo '<option value="' . esc_attr( $cat->term_id ) . '" data-slug="' . esc_attr( (string) $cat->slug ) . '"' . $selected . '>' . $indent . esc_html( $cat->name ) . '</option>';
                }
                echo '</select>';
                break;
            case 'county':
                echo '<select name="county" class="va-select"' . $req_attr . '>';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $counties as $county ) {
                    $selected = selected( (int) $val, $county->term_id, false );
                    echo '<option value="' . esc_attr( $county->term_id ) . '"' . $selected . '>' . esc_html( $county->name ) . '</option>';
                }
                echo '</select>';
                break;
            case 'condition':
                echo '<select name="condition" class="va-select">';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $conditions as $cond ) {
                    $selected = selected( (int) $val, $cond->term_id, false );
                    echo '<option value="' . esc_attr( $cond->term_id ) . '"' . $selected . '>' . esc_html( $cond->name ) . '</option>';
                }
                echo '</select>';
                break;
            case 'location':
                $location_val = (string) ( $ev['location'] ?? '' );
                $postal_val   = (string) ( $ev['postal_code'] ?? '' );
                $street_val   = (string) ( $ev['street'] ?? '' );
                echo '<div class="va-loc-grid">';
                echo '<input type="text" name="location" class="va-input" list="va-street-list" autocomplete="off" placeholder="' . $ph . '" value="' . esc_attr( $location_val ) . '">';
                echo '<input type="text" name="postal_code" class="va-input" placeholder="Irányítószám (pl. 1051)" value="' . esc_attr( $postal_val ) . '" inputmode="numeric" pattern="[0-9]*">';
                echo '<input type="text" name="street" class="va-input" list="va-street-list" autocomplete="off" placeholder="Utca (opcionális)" value="' . esc_attr( $street_val ) . '">';
                echo '<datalist id="va-street-list"></datalist>';
                echo '<small class="va-help">Város vagy irányítószám megadása kötelező.</small>';
                echo '</div>';
                break;
            case 'brand':
                if ( $site_type !== 'jarmu' ) {
                    echo '<select name="brand" id="va-brand" class="va-select">';
                    echo '<option value="">– Válasszon –</option>';
                    if ( $val !== '' ) {
                        echo '<option value="' . esc_attr( (string) $val ) . '" selected>' . esc_html( (string) $val ) . '</option>';
                    }
                    echo '</select>';
                    break;
                }
                echo '<select name="brand" id="va-brand" class="va-select">';
                echo '<option value="">– Válasszon –</option>';
                if ( $val !== '' && ! in_array( (string) $val, $brands, true ) ) {
                    echo '<option value="' . esc_attr( (string) $val ) . '" selected>' . esc_html( (string) $val ) . '</option>';
                }
                foreach ( $brands as $brand ) {
                    echo '<option value="' . esc_attr( $brand ) . '"' . selected( (string) $val, $brand, false ) . '>' . esc_html( $brand ) . '</option>';
                }
                echo '</select>';
                break;
            case 'model':
                if ( $site_type !== 'jarmu' ) {
                    echo '<input type="text" name="model" id="va-model" class="va-input" list="va-model-list" autocomplete="off" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                    echo '<datalist id="va-model-list"></datalist>';
                    break;
                }
                $brand_value = (string) ( $ev['brand'] ?? '' );
                $models_for_brand = isset( $brand_models[ $brand_value ] ) && is_array( $brand_models[ $brand_value ] ) ? $brand_models[ $brand_value ] : [];
                echo '<select name="model" id="va-model" class="va-select" data-placeholder="' . esc_attr( $ph ) . '">';
                echo '<option value="">– Válasszon –</option>';
                if ( $val !== '' && ! in_array( (string) $val, $models_for_brand, true ) ) {
                    echo '<option value="' . esc_attr( (string) $val ) . '" selected>' . esc_html( (string) $val ) . '</option>';
                }
                foreach ( $models_for_brand as $model ) {
                    echo '<option value="' . esc_attr( $model ) . '"' . selected( (string) $val, $model, false ) . '>' . esc_html( $model ) . '</option>';
                }
                echo '</select>';
                break;
            case 'body_type':
                echo '<select name="body_type" class="va-select">';
                echo '<option value="">– Válasszon –</option>';
                if ( $val !== '' && ! array_key_exists( (string) $val, $body_types ) ) {
                    echo '<option value="' . esc_attr( (string) $val ) . '" selected>' . esc_html( (string) $val ) . '</option>';
                }
                foreach ( $body_types as $body_key => $body_label ) {
                    echo '<option value="' . esc_attr( $body_key ) . '"' . selected( (string) $val, $body_key, false ) . '>' . esc_html( $body_label ) . '</option>';
                }
                echo '</select>';
                break;
            case 'caliber':
                echo '<input type="text" name="caliber" class="va-input" list="va-caliber-list" autocomplete="off" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'year':
                echo '<input type="number" name="year" class="va-input" min="1800" max="' . date('Y') . '" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'license_req':
                $checked = $val === '1' ? ' checked' : '';
                echo '<label class="va-check-label"><input type="checkbox" name="license_req" value="1"' . $checked . '> Fegyverengedély szükséges a vásárláshoz</label>';
                break;
            case 'price':
                echo '<input type="number" name="price" class="va-input" min="0" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'price_type':
                $pt = (string) $val;
                echo '<select name="price_type" class="va-select">';
                foreach ( [ 'fixed' => 'Fix ár', 'negotiable' => 'Alkudható', 'free' => 'Ingyenes', 'on_request' => 'Érdeklődjön' ] as $k => $l ) {
                    echo '<option value="' . esc_attr( $k ) . '"' . selected( $pt, $k, false ) . '>' . esc_html( $l ) . '</option>';
                }
                echo '</select>';
                break;
            case 'description':
                $desc_val = wp_kses_post( (string) $val );
                ?>
                <div id="va-quill-editor"></div>
                <textarea name="description" id="va-desc-hidden" style="display:none"><?php echo esc_textarea( $desc_val ); ?></textarea>
                <style>
                .ql-toolbar.ql-snow{background:#1e1e1e;border:1px solid rgba(255,255,255,.15)!important;border-bottom:none!important;border-radius:6px 6px 0 0;}
                .ql-container.ql-snow{background:#111;border:1px solid rgba(255,255,255,.15)!important;border-radius:0 0 6px 6px;font-size:15px;}
                .ql-editor{color:#fff!important;min-height:200px;line-height:1.7;font-family:system-ui,sans-serif;}
                .ql-editor p,.ql-editor span,.ql-editor li,.ql-editor strong,.ql-editor em,.ql-editor u,.ql-editor s{color:#fff!important;}
                .ql-editor.ql-blank::before{color:#9a9a9a!important;font-style:normal;}
                .ql-snow .ql-stroke{stroke:#aaa!important;}
                .ql-snow .ql-fill,.ql-snow .ql-stroke.ql-fill{fill:#aaa!important;}
                .ql-snow .ql-picker{color:#bbb!important;}
                .ql-snow .ql-picker-label{border-color:rgba(255,255,255,.15)!important;}
                .ql-snow .ql-picker-options{background:#1e1e1e!important;border-color:rgba(255,255,255,.15)!important;}
                .ql-snow .ql-picker-item{color:#bbb!important;}
                .ql-snow .ql-picker-item:hover,.ql-snow .ql-picker-item.ql-selected{color:#fff!important;}
                .ql-snow.ql-toolbar button:hover .ql-stroke,.ql-snow .ql-toolbar button:hover .ql-stroke{stroke:#ff4444!important;}
                .ql-snow.ql-toolbar button.ql-active .ql-stroke,.ql-snow .ql-toolbar button.ql-active .ql-stroke{stroke:#ff4444!important;}
                .ql-snow.ql-toolbar button:hover .ql-fill,.ql-snow .ql-toolbar button:hover .ql-fill{fill:#ff4444!important;}
                .ql-snow.ql-toolbar button.ql-active .ql-fill{fill:#ff4444!important;}
                .ql-snow .ql-picker.ql-header .ql-picker-label::before,.ql-snow .ql-picker.ql-header .ql-picker-item::before{color:#bbb!important;}
                .ql-editor a{color:#ff4444;}
                .ql-editor img{max-width:100%;border-radius:6px;}
                .ql-editor blockquote{border-left:3px solid #ff4444;padding-left:12px;color:#aaa;margin:8px 0;}
                .ql-editor h2,.ql-editor h3{color:#e8e8e8;}
                .ql-editor ol,.ql-editor ul{color:#e8e8e8;}
                .ql-snow .ql-tooltip{background:#1e1e1e!important;border-color:rgba(255,255,255,.15)!important;color:#e8e8e8!important;box-shadow:0 4px 20px rgba(0,0,0,.5)!important;}
                .ql-snow .ql-tooltip input[type=text]{background:#111!important;border-color:rgba(255,255,255,.2)!important;color:#e8e8e8!important;}
                .ql-snow .ql-tooltip a.ql-action,.ql-snow .ql-tooltip a.ql-remove{color:#ff4444!important;}
                #va-wizard-overlay.va-wizard-shell .va-wizard-sidebar{padding-top:8px!important;}
                </style>
                <?php
                break;
            case 'images':
                $max_img = absint( get_option( 'va_max_images_per_listing', 10 ) );
                ?>
                <div class="va-img-picker" id="va-img-picker">
                    <div class="va-img-grid" id="va-img-grid">
                        <button type="button" class="va-img-add" id="va-img-add">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Képek<br>hozzáadása</span>
                        </button>
                    </div>
                    <input type="file" id="va-img-file-input" accept="image/jpeg,image/png,image/webp" multiple style="display:none" data-max="<?php echo esc_attr( (string) $max_img ); ?>">
                    <input type="hidden" name="featured_image_index" id="va-featured-index" value="0">
                    <input type="hidden" name="keep_images" id="va-keep-images" value="">
                    <p class="va-img-hint">Húzd a képeket az átrendezéshez &bull; &#9733; = borítókép beállítása</p>
                </div>
                <?php
                break;
            case 'phone':
                echo '<input type="tel" name="phone" class="va-input" placeholder="' . $ph . '"' . $req_attr . ' style="background:#0e0e0e!important;color:#fff!important;color-scheme:dark;" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'email_show':
                echo '<label class="va-check-label" style="align-self:flex-end;">';
                echo '<input type="checkbox" name="email_show" value="1" checked onclick="return false;">';
                echo ' E-mail cím megjelenítése a hirdetésben</label>';
                break;
        }
    }
}

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false ] );
if ( is_array( $categories ) ) {
    $other_category = null;
    $ordered_categories = [];
    $seen_category_names = [];
    foreach ( $categories as $cat ) {
        if ( isset( $cat->slug ) && (string) $cat->slug === 'egyeb' ) {
            $other_category = $cat;
            continue;
        }
        $category_name_key = function_exists( 'mb_strtolower' )
            ? mb_strtolower( trim( (string) ( $cat->name ?? '' ) ), 'UTF-8' )
            : strtolower( trim( (string) ( $cat->name ?? '' ) ) );
        if ( $category_name_key !== '' && isset( $seen_category_names[ $category_name_key ] ) ) {
            continue;
        }
        $seen_category_names[ $category_name_key ] = true;
        $ordered_categories[] = $cat;
    }
    if ( $other_category ) {
        $ordered_categories[] = $other_category;
    }
    $categories = $ordered_categories;
}
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );
$brands     = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_brands() : [];
$brand_models = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_brand_models() : [];
$hunting_brand_models = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_hunting_brand_models_by_category() : [];
$hunting_calibers = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_hunting_calibers() : [];
$address_seed_data = [ 'records' => [] ];
$address_seed_path = dirname( __DIR__, 3 ) . '/includes/hu-address-seed.json';
if ( file_exists( $address_seed_path ) ) {
    $address_seed_raw = (string) file_get_contents( $address_seed_path );
    if ( $address_seed_raw !== '' ) {
        $address_seed_json = json_decode( ltrim( $address_seed_raw, "\xEF\xBB\xBF" ), true );
        if ( is_array( $address_seed_json ) ) {
            $address_seed_data = $address_seed_json;
        }
    }
}
$learned_terms = class_exists( 'VA_Ajax' ) ? [
    'title'    => VA_Ajax::get_learned_terms_map( 'title', 1200 ),
    'brand'    => VA_Ajax::get_learned_terms_map( 'brand', 1200 ),
    'model'    => VA_Ajax::get_learned_terms_map( 'model', 1500 ),
    'caliber'  => VA_Ajax::get_learned_terms_map( 'caliber', 1200 ),
    'location' => VA_Ajax::get_learned_terms_map( 'location', 800 ),
    'street'   => VA_Ajax::get_learned_terms_map( 'street', 1200 ),
] : [];
$body_types = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_body_type_options() : [];
$category_slug_map = [];
if ( is_array( $categories ) ) {
    foreach ( $categories as $cat_term ) {
        $category_slug_map[ (int) $cat_term->term_id ] = sanitize_title( (string) ( $cat_term->slug ?? '' ) );
    }
}
$category_required_rules = [
    'golyos-puska'       => [ 'label' => 'Golyós lőfegyver', 'required' => [ 'brand', 'model', 'rifle_type', 'rifle_caliber', 'rifle_barrel_length_cm', 'rifle_condition', 'rifle_optic_compatibility' ] ],
    'soretes-puska'      => [ 'label' => 'Sörétes lőfegyver', 'required' => [ 'brand', 'caliber' ] ],
    'vegyescsovu-puska'  => [ 'label' => 'Vegyescsövű lőfegyver', 'required' => [ 'brand', 'model', 'mixed_type', 'mixed_barrel_count', 'mixed_rifle_caliber', 'mixed_shotgun_caliber', 'mixed_condition' ] ],
    'maroklofegyver'     => [ 'label' => 'Maroklőfegyver', 'required' => [ 'brand', 'model', 'caliber', 'marok_type', 'marok_condition', 'marok_action_type', 'marok_magazine_capacity', 'marok_license_required', 'marok_legal_category', 'marok_cip_marking', 'marok_transfer_license_only' ] ],
    'hatastalanitott'    => [ 'label' => 'Hatástalanított lőfegyver', 'required' => [ 'brand', 'model', 'hatastalanitott_weapon_type', 'hatastalanitott_is_deactivated', 'hatastalanitott_deactivation_type', 'hatastalanitott_certificate', 'hatastalanitott_mkh_cip', 'hatastalanitott_condition' ] ],
    'egyeb-fegyverek'    => [ 'label' => 'Egyéb fegyverek', 'required' => [ 'brand', 'other_weapon_kind' ] ],
    'loszer-tolteny'     => [ 'label' => 'Lőszer-Töltény', 'required' => [ 'brand', 'caliber', 'loszer_category', 'loszer_condition' ] ],
            'tavcsovek'          => [ 'label' => 'Távcsövek', 'required' => [ 'brand', 'model', 'optic_type', 'optic_objective' ] ],
    'ejjellato-tavcso'   => [ 'label' => 'Éjjellátó távcső', 'required' => [ 'brand', 'model', 'optic_zoom', 'scope_type', 'twilight_value' ] ],
    'hokamerak'          => [ 'label' => 'Hőkamerák', 'required' => [ 'brand', 'model', 'optic_zoom', 'thermal_device_type', 'thermal_sensor_resolution', 'thermal_netd', 'thermal_refresh_hz' ] ],
    'vadkamera'          => [ 'label' => 'Vadkamera', 'required' => [ 'brand', 'model', 'vadcamera_camera_type', 'vadcamera_connectivity', 'vadcamera_trigger_speed', 'vadcamera_ir_type', 'vadcamera_night_range_m', 'vadcamera_video_resolution', 'vadcamera_ip_rating', 'vadcamera_sim_slot' ] ],
    'vadaszlampa'        => [ 'label' => 'Vadászlámpa', 'required' => [ 'brand', 'model' ] ],
    'vadaszkutya'        => [ 'label' => 'Vadászkutya', 'required' => [ 'dog_working_type', 'dog_breed', 'dog_gender', 'dog_birth_date', 'dog_hunting_specialization', 'dog_training_level' ] ],
    'vadasz-ruhazat'     => [ 'label' => 'Vadász ruházat', 'required' => [ 'brand', 'clothing_type', 'clothing_size_system', 'clothing_size' ] ],
    'egyeb-ruhazat'      => [ 'label' => 'Egyéb ruházat', 'required' => [ 'brand', 'clothing_type', 'clothing_condition', 'clothing_gender', 'clothing_size' ] ],
    'cipo-bakancs'       => [ 'label' => 'Cipő, bakancs', 'required' => [ 'brand', 'shoe_main_type', 'shoe_eu_size', 'shoe_condition', 'shoe_boot_height' ] ],
    'csere'              => [ 'label' => 'Csere', 'required' => [ 'exchange_type', 'exchange_offer_category', 'exchange_brand', 'exchange_condition', 'exchange_estimated_value', 'exchange_extra_payment_direction', 'exchange_county' ] ],
    'konyv-folyoirat'    => [ 'label' => 'Könyv és folyóirat', 'required' => [ 'publication_type', 'publication_topics', 'publication_title', 'publication_author', 'publication_year', 'publication_language', 'publication_condition', 'publication_collector_value', 'publication_complete_volume' ] ],
    'disztargyak'        => [ 'label' => 'Dísztárgyak', 'required' => [ 'decor_type', 'decor_material', 'decor_style', 'decor_motif_tags', 'decor_production_type', 'decor_size_class', 'decor_condition' ] ],
    'disztargy'          => [ 'label' => 'Dísztárgyak', 'required' => [ 'decor_type', 'decor_material', 'decor_style', 'decor_motif_tags', 'decor_production_type', 'decor_size_class', 'decor_condition' ] ],
    'allas'              => [ 'label' => 'Állás', 'required' => [ 'job_location', 'job_type' ] ],
    'allas-hirdetes'     => [ 'label' => 'Állás', 'required' => [ 'job_location', 'job_type' ] ],
    'szolgaltatas'       => [ 'label' => 'Szolgáltatás', 'required' => [ 'service_type', 'service_provider_type', 'service_country', 'service_county', 'service_town', 'service_area', 'service_pricing_type' ] ],
    'szallas'            => [ 'label' => 'Szállás / Ingatlan', 'required' => [ 'accommodation_type', 'accommodation_county_text', 'accommodation_land_size_sqm', 'accommodation_building_size_sqm', 'accommodation_utilities', 'accommodation_accessibility', 'accommodation_hunting_features', 'accommodation_property_condition' ] ],
    'ingatlan'           => [ 'label' => 'Szállás / Ingatlan', 'required' => [ 'accommodation_type', 'accommodation_county_text', 'accommodation_land_size_sqm', 'accommodation_building_size_sqm', 'accommodation_utilities', 'accommodation_accessibility', 'accommodation_hunting_features', 'accommodation_property_condition' ] ],
    'ingatlan-szallas'   => [ 'label' => 'Szállás / Ingatlan', 'required' => [ 'accommodation_type', 'accommodation_county_text', 'accommodation_land_size_sqm', 'accommodation_building_size_sqm', 'accommodation_utilities', 'accommodation_accessibility', 'accommodation_hunting_features', 'accommodation_property_condition' ] ],
    'vadaszati-lehetoseg'=> [ 'label' => 'Vadászati lehetőség', 'required' => [ 'hunt_type', 'hunt_game_species', 'hunt_country', 'hunt_county', 'hunt_price_type', 'hunt_price_min', 'hunt_methods', 'hunt_has_accommodation', 'hunt_guide_type', 'hunt_difficulty_level', 'hunt_season_start', 'hunt_season_end' ] ],
    'vadkarelharitas'    => [ 'label' => 'Vadkárelhárítás', 'required' => [ 'hunt_damage_main_type', 'hunt_game_species', 'hunt_country', 'hunt_county', 'hunt_land_type', 'hunt_response_time', 'hunt_availability_mode', 'hunt_pricing_model', 'hunt_contract_type', 'hunt_methods' ] ],
    'vadaszati-hagyatek' => [ 'label' => 'Vadászati hagyaték', 'required' => [ 'estate_main_type' ] ],
    'trofea-aletet'      => [ 'label' => 'Trófeaalátét', 'required' => [ 'trophy_type', 'trophy_material_type', 'trophy_style', 'trophy_mounting_type', 'trophy_attachment_type', 'trophy_height_cm', 'trophy_width_cm', 'trophy_thickness_cm', 'trophy_compatible_species' ] ],
    'kesek'              => [ 'label' => 'Kések', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
    'vadaszkes-vadasztor' => [ 'label' => 'Vadászkés, vadásztőr', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
    'taktikai-kes-taktikai-tor' => [ 'label' => 'Taktikai kés', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
    'konyhakes'          => [ 'label' => 'Konyhakés', 'required' => [ 'knife_type', 'knife_condition', 'knife_blade_length_mm', 'knife_steel_type' ] ],
    'svajci-bicska'      => [ 'label' => 'Svájci bicska', 'required' => [ 'knife_type', 'knife_condition', 'knife_steel_type' ] ],
    'vadasz-felszereles' => [ 'label' => 'Vadász felszerelés', 'required' => [ 'brand', 'equipment_type', 'equipment_condition', 'equipment_hunting_usage' ] ],
];

/* ── Edit mód felismerés ───────────────────────────── */
$edit_post_id = 0;
$edit_post    = null;
$edit_meta    = [];
$edit_mode    = false;
if ( is_user_logged_in() && isset( $_GET['edit'] ) ) {
    $maybe_id = absint( $_GET['edit'] );
    $maybe_post = get_post( $maybe_id );
    if ( $maybe_post && $maybe_post->post_type === 'va_listing' && (int) $maybe_post->post_author === get_current_user_id() ) {
        $edit_post_id = $maybe_id;
        $edit_post    = $maybe_post;
        $edit_mode    = true;
        $edit_meta    = [
            'title'       => $maybe_post->post_title,
            'description' => $maybe_post->post_content,
            'price'       => get_post_meta( $maybe_id, 'va_price',       true ),
            'price_type'  => get_post_meta( $maybe_id, 'va_price_type',  true ),
            'phone'       => get_post_meta( $maybe_id, 'va_phone',       true ),
            'location'    => get_post_meta( $maybe_id, 'va_location',    true ),
            'postal_code' => get_post_meta( $maybe_id, 'va_postal_code', true ),
            'street'      => get_post_meta( $maybe_id, 'va_street',      true ),
            'other_category' => get_post_meta( $maybe_id, 'va_other_category', true ),
            'brand'       => get_post_meta( $maybe_id, 'va_brand',       true ),
            'model'       => get_post_meta( $maybe_id, 'va_model',       true ),
            'body_type'   => get_post_meta( $maybe_id, 'va_body_type',   true ),
            'caliber'     => get_post_meta( $maybe_id, 'va_caliber',     true ),
            'optic_type'  => get_post_meta( $maybe_id, 'va_optic_type',  true ),
            'optic_zoom'  => get_post_meta( $maybe_id, 'va_optic_zoom',  true ),
            'optic_objective' => get_post_meta( $maybe_id, 'va_optic_objective', true ),
            'finder_scope' => get_post_meta( $maybe_id, 'va_finder_scope', true ),
            'spectiv_type' => get_post_meta( $maybe_id, 'va_spectiv_type', true ),
            'scope_type' => get_post_meta( $maybe_id, 'va_scope_type', true ),
            'scope_coatings' => get_post_meta( $maybe_id, 'va_scope_coatings', true ),
            'twilight_value' => get_post_meta( $maybe_id, 'va_twilight_value', true ),
            'scope_accessories' => get_post_meta( $maybe_id, 'va_scope_accessories', true ),
            'optic_magnification_min' => get_post_meta( $maybe_id, 'va_optic_magnification_min', true ),
            'optic_magnification_max' => get_post_meta( $maybe_id, 'va_optic_magnification_max', true ),
            'optic_magnification_fixed' => get_post_meta( $maybe_id, 'va_optic_magnification_fixed', true ),
            'optic_tube_diameter' => get_post_meta( $maybe_id, 'va_optic_tube_diameter', true ),
            'optic_exit_pupil' => get_post_meta( $maybe_id, 'va_optic_exit_pupil', true ),
            'optic_field_of_view' => get_post_meta( $maybe_id, 'va_optic_field_of_view', true ),
            'optic_eye_relief' => get_post_meta( $maybe_id, 'va_optic_eye_relief', true ),
            'optic_reticle_type' => get_post_meta( $maybe_id, 'va_optic_reticle_type', true ),
            'optic_reticle_plane' => get_post_meta( $maybe_id, 'va_optic_reticle_plane', true ),
            'optic_illumination' => get_post_meta( $maybe_id, 'va_optic_illumination', true ),
            'optic_illumination_color' => get_post_meta( $maybe_id, 'va_optic_illumination_color', true ),
            'optic_click_value' => get_post_meta( $maybe_id, 'va_optic_click_value', true ),
            'optic_parallax_adjustment' => get_post_meta( $maybe_id, 'va_optic_parallax_adjustment', true ),
            'optic_turret_type' => get_post_meta( $maybe_id, 'va_optic_turret_type', true ),
            'optic_body_material' => get_post_meta( $maybe_id, 'va_optic_body_material', true ),
            'optic_coating_type' => get_post_meta( $maybe_id, 'va_optic_coating_type', true ),
            'optic_waterproof_flags' => get_post_meta( $maybe_id, 'va_optic_waterproof_flags', true ),
            'optic_prism_system' => get_post_meta( $maybe_id, 'va_optic_prism_system', true ),
            'optic_glass_type' => get_post_meta( $maybe_id, 'va_optic_glass_type', true ),
            'optic_dot_size' => get_post_meta( $maybe_id, 'va_optic_dot_size', true ),
            'optic_rail_compatibility' => get_post_meta( $maybe_id, 'va_optic_rail_compatibility', true ),
            'optic_rangefinder_max_m' => get_post_meta( $maybe_id, 'va_optic_rangefinder_max_m', true ),
            'optic_ballistic_calc' => get_post_meta( $maybe_id, 'va_optic_ballistic_calc', true ),
            'optic_spektiv_zoom_range' => get_post_meta( $maybe_id, 'va_optic_spektiv_zoom_range', true ),
            'optic_eyepiece_type' => get_post_meta( $maybe_id, 'va_optic_eyepiece_type', true ),
            'optic_weapon_compatibility' => get_post_meta( $maybe_id, 'va_optic_weapon_compatibility', true ),
            'scope_accessories_flags' => get_post_meta( $maybe_id, 'va_scope_accessories_flags', true ),
            'optic_mount_type' => get_post_meta( $maybe_id, 'va_optic_mount_type', true ),
            'optic_shipping_methods' => get_post_meta( $maybe_id, 'va_optic_shipping_methods', true ),
            'optic_recommended_use' => get_post_meta( $maybe_id, 'va_optic_recommended_use', true ),
            'optic_moderation_flags' => get_post_meta( $maybe_id, 'va_optic_moderation_flags', true ),
            'thermal_device_type' => get_post_meta( $maybe_id, 'va_thermal_device_type', true ),
            'thermal_condition' => get_post_meta( $maybe_id, 'va_thermal_condition', true ),
            'thermal_warranty' => get_post_meta( $maybe_id, 'va_thermal_warranty', true ),
            'thermal_manufacture_year' => get_post_meta( $maybe_id, 'va_thermal_manufacture_year', true ),
            'thermal_sensor_resolution' => get_post_meta( $maybe_id, 'va_thermal_sensor_resolution', true ),
            'thermal_pixel_pitch' => get_post_meta( $maybe_id, 'va_thermal_pixel_pitch', true ),
            'thermal_netd' => get_post_meta( $maybe_id, 'va_thermal_netd', true ),
            'thermal_refresh_hz' => get_post_meta( $maybe_id, 'va_thermal_refresh_hz', true ),
            'thermal_base_magnification' => get_post_meta( $maybe_id, 'va_thermal_base_magnification', true ),
            'thermal_digital_zoom' => get_post_meta( $maybe_id, 'va_thermal_digital_zoom', true ),
            'thermal_lens_size' => get_post_meta( $maybe_id, 'va_thermal_lens_size', true ),
            'thermal_fov_degree' => get_post_meta( $maybe_id, 'va_thermal_fov_degree', true ),
            'thermal_fov_m100' => get_post_meta( $maybe_id, 'va_thermal_fov_m100', true ),
            'thermal_detect_human_m' => get_post_meta( $maybe_id, 'va_thermal_detect_human_m', true ),
            'thermal_detect_game_m' => get_post_meta( $maybe_id, 'va_thermal_detect_game_m', true ),
            'thermal_detect_vehicle_m' => get_post_meta( $maybe_id, 'va_thermal_detect_vehicle_m', true ),
            'thermal_display_type' => get_post_meta( $maybe_id, 'va_thermal_display_type', true ),
            'thermal_display_resolution' => get_post_meta( $maybe_id, 'va_thermal_display_resolution', true ),
            'thermal_palettes' => get_post_meta( $maybe_id, 'va_thermal_palettes', true ),
            'thermal_photo' => get_post_meta( $maybe_id, 'va_thermal_photo', true ),
            'thermal_video' => get_post_meta( $maybe_id, 'va_thermal_video', true ),
            'thermal_audio' => get_post_meta( $maybe_id, 'va_thermal_audio', true ),
            'thermal_internal_memory_gb' => get_post_meta( $maybe_id, 'va_thermal_internal_memory_gb', true ),
            'thermal_sd_support' => get_post_meta( $maybe_id, 'va_thermal_sd_support', true ),
            'thermal_connectivity' => get_post_meta( $maybe_id, 'va_thermal_connectivity', true ),
            'thermal_mobile_app' => get_post_meta( $maybe_id, 'va_thermal_mobile_app', true ),
            'thermal_extra_features' => get_post_meta( $maybe_id, 'va_thermal_extra_features', true ),
            'thermal_battery_type' => get_post_meta( $maybe_id, 'va_thermal_battery_type', true ),
            'thermal_runtime_hours' => get_post_meta( $maybe_id, 'va_thermal_runtime_hours', true ),
            'thermal_powerbank_compatible' => get_post_meta( $maybe_id, 'va_thermal_powerbank_compatible', true ),
            'thermal_ip_rating' => get_post_meta( $maybe_id, 'va_thermal_ip_rating', true ),
            'thermal_recoil_resistance' => get_post_meta( $maybe_id, 'va_thermal_recoil_resistance', true ),
            'thermal_weapon_compatibility' => get_post_meta( $maybe_id, 'va_thermal_weapon_compatibility', true ),
            'thermal_accessories' => get_post_meta( $maybe_id, 'va_thermal_accessories', true ),
            'dog_age_months' => get_post_meta( $maybe_id, 'va_dog_age_months', true ),
            'dog_breed' => get_post_meta( $maybe_id, 'va_dog_breed', true ),
            'dog_gender' => get_post_meta( $maybe_id, 'va_dog_gender', true ),
            'dog_color' => get_post_meta( $maybe_id, 'va_dog_color', true ),
            'dog_purebred' => get_post_meta( $maybe_id, 'va_dog_purebred', true ),
            'dog_working_type' => get_post_meta( $maybe_id, 'va_dog_working_type', true ),
            'dog_breeder_name' => get_post_meta( $maybe_id, 'va_dog_breeder_name', true ),
            'dog_kennel_name' => get_post_meta( $maybe_id, 'va_dog_kennel_name', true ),
            'dog_pedigree_status' => get_post_meta( $maybe_id, 'va_dog_pedigree_status', true ),
            'dog_origin_country' => get_post_meta( $maybe_id, 'va_dog_origin_country', true ),
            'dog_bloodline_type' => get_post_meta( $maybe_id, 'va_dog_bloodline_type', true ),
            'dog_documents' => get_post_meta( $maybe_id, 'va_dog_documents', true ),
            'dog_name' => get_post_meta( $maybe_id, 'va_dog_name', true ),
            'dog_birth_date' => get_post_meta( $maybe_id, 'va_dog_birth_date', true ),
            'dog_training_skills' => get_post_meta( $maybe_id, 'va_dog_training_skills', true ),
            'dog_hunting_specialization' => get_post_meta( $maybe_id, 'va_dog_hunting_specialization', true ),
            'dog_work_style' => get_post_meta( $maybe_id, 'va_dog_work_style', true ),
            'dog_search_radius_m' => get_post_meta( $maybe_id, 'va_dog_search_radius_m', true ),
            'dog_endurance_level' => get_post_meta( $maybe_id, 'va_dog_endurance_level', true ),
            'dog_aggression_level' => get_post_meta( $maybe_id, 'va_dog_aggression_level', true ),
            'dog_apport_stability' => get_post_meta( $maybe_id, 'va_dog_apport_stability', true ),
            'dog_water_work_ability' => get_post_meta( $maybe_id, 'va_dog_water_work_ability', true ),
            'dog_water_work' => get_post_meta( $maybe_id, 'va_dog_water_work', true ),
            'dog_water_work_level' => get_post_meta( $maybe_id, 'va_dog_water_work_level', true ),
            'dog_cold_water_tolerance' => get_post_meta( $maybe_id, 'va_dog_cold_water_tolerance', true ),
            'dog_temperament' => get_post_meta( $maybe_id, 'va_dog_temperament', true ),
            'dog_environment' => get_post_meta( $maybe_id, 'va_dog_environment', true ),
            'dog_allergic' => get_post_meta( $maybe_id, 'va_dog_allergic', true ),
            'dog_special_diet' => get_post_meta( $maybe_id, 'va_dog_special_diet', true ),
            'dog_known_diseases' => get_post_meta( $maybe_id, 'va_dog_known_diseases', true ),
            'dog_hip_dysplasia_screening' => get_post_meta( $maybe_id, 'va_dog_hip_dysplasia_screening', true ),
            'dog_height_cm' => get_post_meta( $maybe_id, 'va_dog_height_cm', true ),
            'dog_weight_kg' => get_post_meta( $maybe_id, 'va_dog_weight_kg', true ),
            'dog_appearance_type' => get_post_meta( $maybe_id, 'va_dog_appearance_type', true ),
            'dog_country' => get_post_meta( $maybe_id, 'va_dog_country', true ),
            'dog_county' => get_post_meta( $maybe_id, 'va_dog_county', true ),
            'dog_city' => get_post_meta( $maybe_id, 'va_dog_city', true ),
            'dog_training_level' => get_post_meta( $maybe_id, 'va_dog_training_level', true ),
            'dog_exam_level' => get_post_meta( $maybe_id, 'va_dog_exam_level', true ),
            'dog_work_exam_type' => get_post_meta( $maybe_id, 'va_dog_work_exam_type', true ),
            'dog_origin_qualification' => get_post_meta( $maybe_id, 'va_dog_origin_qualification', true ),
            'dog_litter_count' => get_post_meta( $maybe_id, 'va_dog_litter_count', true ),
            'dog_available_from' => get_post_meta( $maybe_id, 'va_dog_available_from', true ),
            'dog_tracking_distance_m' => get_post_meta( $maybe_id, 'va_dog_tracking_distance_m', true ),
            'dog_tracking_experience' => get_post_meta( $maybe_id, 'va_dog_tracking_experience', true ),
            'dog_moderation_flags' => get_post_meta( $maybe_id, 'va_dog_moderation_flags', true ),
            'dog_work_video_types' => get_post_meta( $maybe_id, 'va_dog_work_video_types', true ),
            'dog_work_video_url' => get_post_meta( $maybe_id, 'va_dog_work_video_url', true ),
            'shoe_size'   => get_post_meta( $maybe_id, 'va_shoe_size',   true ),
            'shoe_main_type' => get_post_meta( $maybe_id, 'va_shoe_main_type', true ),
            'shoe_condition' => get_post_meta( $maybe_id, 'va_shoe_condition', true ),
            'shoe_gender' => get_post_meta( $maybe_id, 'va_shoe_gender', true ),
            'shoe_eu_size' => get_post_meta( $maybe_id, 'va_shoe_eu_size', true ),
            'shoe_uk_size' => get_post_meta( $maybe_id, 'va_shoe_uk_size', true ),
            'shoe_us_size' => get_post_meta( $maybe_id, 'va_shoe_us_size', true ),
            'shoe_boot_height' => get_post_meta( $maybe_id, 'va_shoe_boot_height', true ),
            'shoe_outer_material' => get_post_meta( $maybe_id, 'va_shoe_outer_material', true ),
            'shoe_lining' => get_post_meta( $maybe_id, 'va_shoe_lining', true ),
            'shoe_sole_material' => get_post_meta( $maybe_id, 'va_shoe_sole_material', true ),
            'shoe_protections' => get_post_meta( $maybe_id, 'va_shoe_protections', true ),
            'shoe_season' => get_post_meta( $maybe_id, 'va_shoe_season', true ),
            'shoe_terrain' => get_post_meta( $maybe_id, 'va_shoe_terrain', true ),
            'shoe_use_cases' => get_post_meta( $maybe_id, 'va_shoe_use_cases', true ),
            'shoe_sole_pattern' => get_post_meta( $maybe_id, 'va_shoe_sole_pattern', true ),
            'shoe_stiffness' => get_post_meta( $maybe_id, 'va_shoe_stiffness', true ),
            'shoe_weight_single_g' => get_post_meta( $maybe_id, 'va_shoe_weight_single_g', true ),
            'shoe_weight_pair_g' => get_post_meta( $maybe_id, 'va_shoe_weight_pair_g', true ),
            'shoe_accessories' => get_post_meta( $maybe_id, 'va_shoe_accessories', true ),
            'shoe_shipping_methods' => get_post_meta( $maybe_id, 'va_shoe_shipping_methods', true ),
            'shoe_required_media' => get_post_meta( $maybe_id, 'va_shoe_required_media', true ),
            'shoe_rubber_shaft_cm' => get_post_meta( $maybe_id, 'va_shoe_rubber_shaft_cm', true ),
            'shoe_rubber_neoprene_lining' => get_post_meta( $maybe_id, 'va_shoe_rubber_neoprene_lining', true ),
            'shoe_tactical_steel_toe' => get_post_meta( $maybe_id, 'va_shoe_tactical_steel_toe', true ),
            'shoe_tactical_quick_lace' => get_post_meta( $maybe_id, 'va_shoe_tactical_quick_lace', true ),
            'shoe_winter_comfort_temp_c' => get_post_meta( $maybe_id, 'va_shoe_winter_comfort_temp_c', true ),
            'shoe_foot_type' => get_post_meta( $maybe_id, 'va_shoe_foot_type', true ),
            'shoe_sole_wear_percent' => get_post_meta( $maybe_id, 'va_shoe_sole_wear_percent', true ),
            'shoe_inner_wear' => get_post_meta( $maybe_id, 'va_shoe_inner_wear', true ),
            'shoe_waterproof_retained' => get_post_meta( $maybe_id, 'va_shoe_waterproof_retained', true ),
            'shoe_resoled' => get_post_meta( $maybe_id, 'va_shoe_resoled', true ),
            'shoe_notes' => get_post_meta( $maybe_id, 'va_shoe_notes', true ),
            'clothing_type' => get_post_meta( $maybe_id, 'va_clothing_type', true ),
            'clothing_condition' => get_post_meta( $maybe_id, 'va_clothing_condition', true ),
            'clothing_gender' => get_post_meta( $maybe_id, 'va_clothing_gender', true ),
            'clothing_size_system' => get_post_meta( $maybe_id, 'va_clothing_size_system', true ),
            'clothing_size' => get_post_meta( $maybe_id, 'va_clothing_size', true ),
            'clothing_accessory_size' => get_post_meta( $maybe_id, 'va_clothing_accessory_size', true ),
            'clothing_glove_size' => get_post_meta( $maybe_id, 'va_clothing_glove_size', true ),
            'clothing_hat_size' => get_post_meta( $maybe_id, 'va_clothing_hat_size', true ),
            'clothing_hat_cm' => get_post_meta( $maybe_id, 'va_clothing_hat_cm', true ),
            'clothing_belt_length_cm' => get_post_meta( $maybe_id, 'va_clothing_belt_length_cm', true ),
            'clothing_colors' => get_post_meta( $maybe_id, 'va_clothing_colors', true ),
            'clothing_materials' => get_post_meta( $maybe_id, 'va_clothing_materials', true ),
            'clothing_protections' => get_post_meta( $maybe_id, 'va_clothing_protections', true ),
            'clothing_season' => get_post_meta( $maybe_id, 'va_clothing_season', true ),
            'clothing_use_cases' => get_post_meta( $maybe_id, 'va_clothing_use_cases', true ),
            'clothing_special_features' => get_post_meta( $maybe_id, 'va_clothing_special_features', true ),
            'clothing_glove_features' => get_post_meta( $maybe_id, 'va_clothing_glove_features', true ),
            'clothing_headwear_features' => get_post_meta( $maybe_id, 'va_clothing_headwear_features', true ),
            'clothing_poncho_water_column' => get_post_meta( $maybe_id, 'va_clothing_poncho_water_column', true ),
            'clothing_poncho_ultralight' => get_post_meta( $maybe_id, 'va_clothing_poncho_ultralight', true ),
            'clothing_poncho_backpack_compatible' => get_post_meta( $maybe_id, 'va_clothing_poncho_backpack_compatible', true ),
            'clothing_ghillie_type' => get_post_meta( $maybe_id, 'va_clothing_ghillie_type', true ),
            'clothing_ghillie_pattern' => get_post_meta( $maybe_id, 'va_clothing_ghillie_pattern', true ),
            'knife_type' => get_post_meta( $maybe_id, 'va_knife_type', true ),
            'knife_blade_length' => get_post_meta( $maybe_id, 'va_knife_blade_length', true ),
            'knife_condition' => get_post_meta( $maybe_id, 'va_knife_condition', true ),
            'knife_manufacture_year' => get_post_meta( $maybe_id, 'va_knife_manufacture_year', true ),
            'knife_total_length_cm' => get_post_meta( $maybe_id, 'va_knife_total_length_cm', true ),
            'knife_blade_length_mm' => get_post_meta( $maybe_id, 'va_knife_blade_length_mm', true ),
            'knife_blade_thickness_mm' => get_post_meta( $maybe_id, 'va_knife_blade_thickness_mm', true ),
            'knife_weight_g' => get_post_meta( $maybe_id, 'va_knife_weight_g', true ),
            'knife_steel_type' => get_post_meta( $maybe_id, 'va_knife_steel_type', true ),
            'knife_blade_shape' => get_post_meta( $maybe_id, 'va_knife_blade_shape', true ),
            'knife_blade_edge' => get_post_meta( $maybe_id, 'va_knife_blade_edge', true ),
            'knife_finish' => get_post_meta( $maybe_id, 'va_knife_finish', true ),
            'knife_construction' => get_post_meta( $maybe_id, 'va_knife_construction', true ),
            'knife_locking_type' => get_post_meta( $maybe_id, 'va_knife_locking_type', true ),
            'knife_one_hand_opening' => get_post_meta( $maybe_id, 'va_knife_one_hand_opening', true ),
            'knife_handle_material' => get_post_meta( $maybe_id, 'va_knife_handle_material', true ),
            'knife_handle_color' => get_post_meta( $maybe_id, 'va_knife_handle_color', true ),
            'knife_has_sheath' => get_post_meta( $maybe_id, 'va_knife_has_sheath', true ),
            'knife_sheath_type' => get_post_meta( $maybe_id, 'va_knife_sheath_type', true ),
            'knife_belt_carry' => get_post_meta( $maybe_id, 'va_knife_belt_carry', true ),
            'knife_usage' => get_post_meta( $maybe_id, 'va_knife_usage', true ),
            'knife_features' => get_post_meta( $maybe_id, 'va_knife_features', true ),
            'knife_age_restriction' => get_post_meta( $maybe_id, 'va_knife_age_restriction', true ),
            'knife_restricted' => get_post_meta( $maybe_id, 'va_knife_restricted', true ),
            'knife_public_carry' => get_post_meta( $maybe_id, 'va_knife_public_carry', true ),
            'knife_accessories' => get_post_meta( $maybe_id, 'va_knife_accessories', true ),
            'knife_price_deal' => get_post_meta( $maybe_id, 'va_knife_price_deal', true ),
            'knife_price_exchange' => get_post_meta( $maybe_id, 'va_knife_price_exchange', true ),
            'knife_shipping_methods' => get_post_meta( $maybe_id, 'va_knife_shipping_methods', true ),
            'knife_required_media' => get_post_meta( $maybe_id, 'va_knife_required_media', true ),
            'knife_sharpening_state' => get_post_meta( $maybe_id, 'va_knife_sharpening_state', true ),
            'knife_collectible_flags' => get_post_meta( $maybe_id, 'va_knife_collectible_flags', true ),
            'knife_moderation_flags' => get_post_meta( $maybe_id, 'va_knife_moderation_flags', true ),
            'knife_axe_head_weight_g' => get_post_meta( $maybe_id, 'va_knife_axe_head_weight_g', true ),
            'knife_axe_handle_material' => get_post_meta( $maybe_id, 'va_knife_axe_handle_material', true ),
            'knife_multitool_function_count' => get_post_meta( $maybe_id, 'va_knife_multitool_function_count', true ),
            'trophy_species' => get_post_meta( $maybe_id, 'va_trophy_species', true ),
            'trophy_type' => get_post_meta( $maybe_id, 'va_trophy_type', true ),
            'trophy_maker' => get_post_meta( $maybe_id, 'va_trophy_maker', true ),
            'trophy_custom_made' => get_post_meta( $maybe_id, 'va_trophy_custom_made', true ),
            'trophy_condition' => get_post_meta( $maybe_id, 'va_trophy_condition', true ),
            'trophy_manufacture_year' => get_post_meta( $maybe_id, 'va_trophy_manufacture_year', true ),
            'trophy_height_cm' => get_post_meta( $maybe_id, 'va_trophy_height_cm', true ),
            'trophy_width_cm' => get_post_meta( $maybe_id, 'va_trophy_width_cm', true ),
            'trophy_thickness_cm' => get_post_meta( $maybe_id, 'va_trophy_thickness_cm', true ),
            'trophy_compatible_species' => get_post_meta( $maybe_id, 'va_trophy_compatible_species', true ),
            'trophy_mount_type' => get_post_meta( $maybe_id, 'va_trophy_mount_type', true ),
            'trophy_mounting_type' => get_post_meta( $maybe_id, 'va_trophy_mounting_type', true ),
            'trophy_attachment_type' => get_post_meta( $maybe_id, 'va_trophy_attachment_type', true ),
            'trophy_style' => get_post_meta( $maybe_id, 'va_trophy_style', true ),
            'trophy_fang_mount' => get_post_meta( $maybe_id, 'va_trophy_fang_mount', true ),
            'trophy_material' => get_post_meta( $maybe_id, 'va_trophy_material', true ),
            'trophy_material_type' => get_post_meta( $maybe_id, 'va_trophy_material_type', true ),
            'trophy_finish' => get_post_meta( $maybe_id, 'va_trophy_finish', true ),
            'trophy_surface_finish' => get_post_meta( $maybe_id, 'va_trophy_surface_finish', true ),
            'trophy_decoration_flags' => get_post_meta( $maybe_id, 'va_trophy_decoration_flags', true ),
            'trophy_weight_kg' => get_post_meta( $maybe_id, 'va_trophy_weight_kg', true ),
            'trophy_usage_context' => get_post_meta( $maybe_id, 'va_trophy_usage_context', true ),
            'trophy_accessories' => get_post_meta( $maybe_id, 'va_trophy_accessories', true ),
            'trophy_price_negotiable' => get_post_meta( $maybe_id, 'va_trophy_price_negotiable', true ),
            'trophy_custom_order' => get_post_meta( $maybe_id, 'va_trophy_custom_order', true ),
            'trophy_shipping_methods' => get_post_meta( $maybe_id, 'va_trophy_shipping_methods', true ),
            'trophy_fragile_handling' => get_post_meta( $maybe_id, 'va_trophy_fragile_handling', true ),
            'trophy_required_media' => get_post_meta( $maybe_id, 'va_trophy_required_media', true ),
            'trophy_lead_time' => get_post_meta( $maybe_id, 'va_trophy_lead_time', true ),
            'trophy_orderable_sizes' => get_post_meta( $maybe_id, 'va_trophy_orderable_sizes', true ),
            'trophy_custom_engraving' => get_post_meta( $maybe_id, 'va_trophy_custom_engraving', true ),
            'trophy_house_style' => get_post_meta( $maybe_id, 'va_trophy_house_style', true ),
            'trophy_moderation_flags' => get_post_meta( $maybe_id, 'va_trophy_moderation_flags', true ),
            'accommodation_type' => get_post_meta( $maybe_id, 'va_accommodation_type', true ),
            'accommodation_capacity' => get_post_meta( $maybe_id, 'va_accommodation_capacity', true ),
            'accommodation_slot_mode' => get_post_meta( $maybe_id, 'va_accommodation_slot_mode', true ),
            'accommodation_from_hour' => get_post_meta( $maybe_id, 'va_accommodation_from_hour', true ),
            'accommodation_to_hour' => get_post_meta( $maybe_id, 'va_accommodation_to_hour', true ),
            'accommodation_checkin_from' => get_post_meta( $maybe_id, 'va_accommodation_checkin_from', true ),
            'accommodation_checkin_to' => get_post_meta( $maybe_id, 'va_accommodation_checkin_to', true ),
            'accommodation_checkout_from' => get_post_meta( $maybe_id, 'va_accommodation_checkout_from', true ),
            'accommodation_checkout_to' => get_post_meta( $maybe_id, 'va_accommodation_checkout_to', true ),
            'accommodation_meal_type' => get_post_meta( $maybe_id, 'va_accommodation_meal_type', true ),
            'accommodation_parking' => get_post_meta( $maybe_id, 'va_accommodation_parking', true ),
            'accommodation_settlement' => get_post_meta( $maybe_id, 'va_accommodation_settlement', true ),
            'accommodation_county_text' => get_post_meta( $maybe_id, 'va_accommodation_county_text', true ),
            'accommodation_country' => get_post_meta( $maybe_id, 'va_accommodation_country', true ),
            'accommodation_exact_address' => get_post_meta( $maybe_id, 'va_accommodation_exact_address', true ),
            'accommodation_adult_capacity' => get_post_meta( $maybe_id, 'va_accommodation_adult_capacity', true ),
            'accommodation_room_count' => get_post_meta( $maybe_id, 'va_accommodation_room_count', true ),
            'accommodation_bed_count' => get_post_meta( $maybe_id, 'va_accommodation_bed_count', true ),
            'accommodation_extra_bed' => get_post_meta( $maybe_id, 'va_accommodation_extra_bed', true ),
            'accommodation_hunting_nearby' => get_post_meta( $maybe_id, 'va_accommodation_hunting_nearby', true ),
            'accommodation_hunt_available' => get_post_meta( $maybe_id, 'va_accommodation_hunt_available', true ),
            'accommodation_trophy_handling' => get_post_meta( $maybe_id, 'va_accommodation_trophy_handling', true ),
            'accommodation_cold_room' => get_post_meta( $maybe_id, 'va_accommodation_cold_room', true ),
            'accommodation_gun_storage' => get_post_meta( $maybe_id, 'va_accommodation_gun_storage', true ),
            'accommodation_game_processing' => get_post_meta( $maybe_id, 'va_accommodation_game_processing', true ),
            'accommodation_offroad_parking' => get_post_meta( $maybe_id, 'va_accommodation_offroad_parking', true ),
            'accommodation_dog_allowed' => get_post_meta( $maybe_id, 'va_accommodation_dog_allowed', true ),
            'accommodation_features' => get_post_meta( $maybe_id, 'va_accommodation_features', true ),
            'accommodation_min_nights' => get_post_meta( $maybe_id, 'va_accommodation_min_nights', true ),
            'accommodation_bookable_period' => get_post_meta( $maybe_id, 'va_accommodation_bookable_period', true ),
            'accommodation_instant_book' => get_post_meta( $maybe_id, 'va_accommodation_instant_book', true ),
            'accommodation_nearby_species' => get_post_meta( $maybe_id, 'va_accommodation_nearby_species', true ),
            'accommodation_programs' => get_post_meta( $maybe_id, 'va_accommodation_programs', true ),
            'accommodation_environment' => get_post_meta( $maybe_id, 'va_accommodation_environment', true ),
            'accommodation_land_size_sqm' => get_post_meta( $maybe_id, 'va_accommodation_land_size_sqm', true ),
            'accommodation_land_size_hectare' => get_post_meta( $maybe_id, 'va_accommodation_land_size_hectare', true ),
            'accommodation_building_size_sqm' => get_post_meta( $maybe_id, 'va_accommodation_building_size_sqm', true ),
            'accommodation_levels_count' => get_post_meta( $maybe_id, 'va_accommodation_levels_count', true ),
            'accommodation_build_year' => get_post_meta( $maybe_id, 'va_accommodation_build_year', true ),
            'accommodation_property_condition' => get_post_meta( $maybe_id, 'va_accommodation_property_condition', true ),
            'accommodation_construction_type' => get_post_meta( $maybe_id, 'va_accommodation_construction_type', true ),
            'accommodation_heating_type' => get_post_meta( $maybe_id, 'va_accommodation_heating_type', true ),
            'accommodation_utilities' => get_post_meta( $maybe_id, 'va_accommodation_utilities', true ),
            'accommodation_accessibility' => get_post_meta( $maybe_id, 'va_accommodation_accessibility', true ),
            'accommodation_hunting_features' => get_post_meta( $maybe_id, 'va_accommodation_hunting_features', true ),
            'accommodation_extras' => get_post_meta( $maybe_id, 'va_accommodation_extras', true ),
            'accommodation_network_features' => get_post_meta( $maybe_id, 'va_accommodation_network_features', true ),
            'accommodation_use_type' => get_post_meta( $maybe_id, 'va_accommodation_use_type', true ),
            'accommodation_terrain_type' => get_post_meta( $maybe_id, 'va_accommodation_terrain_type', true ),
            'accommodation_animal_housing' => get_post_meta( $maybe_id, 'va_accommodation_animal_housing', true ),
            'accommodation_fafaj' => get_post_meta( $maybe_id, 'va_accommodation_fafaj', true ),
            'accommodation_erdo_kora' => get_post_meta( $maybe_id, 'va_accommodation_erdo_kora', true ),
            'accommodation_vadaszhaz_trofeaszoba' => get_post_meta( $maybe_id, 'va_accommodation_vadaszhaz_trofeaszoba', true ),
            'accommodation_vadaszhaz_fegyvertarolo' => get_post_meta( $maybe_id, 'va_accommodation_vadaszhaz_fegyvertarolo', true ),
            'accommodation_tanya_gazdasagi_epuletek' => get_post_meta( $maybe_id, 'va_accommodation_tanya_gazdasagi_epuletek', true ),
            'accommodation_hunting_potential_wild_movement' => get_post_meta( $maybe_id, 'va_accommodation_hunting_potential_wild_movement', true ),
            'accommodation_hunting_potential_isolation' => get_post_meta( $maybe_id, 'va_accommodation_hunting_potential_isolation', true ),
            'accommodation_hunting_potential_forest_proximity' => get_post_meta( $maybe_id, 'va_accommodation_hunting_potential_forest_proximity', true ),
            'accommodation_hunting_potential_infrastructure' => get_post_meta( $maybe_id, 'va_accommodation_hunting_potential_infrastructure', true ),
            'hunt_slot_from_hour' => get_post_meta( $maybe_id, 'va_hunt_slot_from_hour', true ),
            'hunt_slot_to_hour' => get_post_meta( $maybe_id, 'va_hunt_slot_to_hour', true ),
            'hunt_capacity' => get_post_meta( $maybe_id, 'va_hunt_capacity', true ),
            'hunt_species_list' => get_post_meta( $maybe_id, 'va_hunt_species_list', true ),
            'hunt_lease_type' => get_post_meta( $maybe_id, 'va_hunt_lease_type', true ),
            'hunt_has_guide' => get_post_meta( $maybe_id, 'va_hunt_has_guide', true ),
            'hunt_has_tracking' => get_post_meta( $maybe_id, 'va_hunt_has_tracking', true ),
            'hunt_has_trophy_prep' => get_post_meta( $maybe_id, 'va_hunt_has_trophy_prep', true ),
            'hunt_has_trophy_judging' => get_post_meta( $maybe_id, 'va_hunt_has_trophy_judging', true ),
            'hunt_has_offroad' => get_post_meta( $maybe_id, 'va_hunt_has_offroad', true ),
            'hunt_has_accommodation' => get_post_meta( $maybe_id, 'va_hunt_has_accommodation', true ),
            'hunt_can_buy_game' => get_post_meta( $maybe_id, 'va_hunt_can_buy_game', true ),
            'hunt_damage_main_type' => get_post_meta( $maybe_id, 'va_hunt_damage_main_type', true ),
            'hunt_settlement' => get_post_meta( $maybe_id, 'va_hunt_settlement', true ),
            'hunt_land_type' => get_post_meta( $maybe_id, 'va_hunt_land_type', true ),
            'hunt_response_time' => get_post_meta( $maybe_id, 'va_hunt_response_time', true ),
            'hunt_availability_mode' => get_post_meta( $maybe_id, 'va_hunt_availability_mode', true ),
            'hunt_executor_type' => get_post_meta( $maybe_id, 'va_hunt_executor_type', true ),
            'hunt_executor_capacity' => get_post_meta( $maybe_id, 'va_hunt_executor_capacity', true ),
            'hunt_tools' => get_post_meta( $maybe_id, 'va_hunt_tools', true ),
            'hunt_weapon_use_required' => get_post_meta( $maybe_id, 'va_hunt_weapon_use_required', true ),
            'hunt_land_manager_permit' => get_post_meta( $maybe_id, 'va_hunt_land_manager_permit', true ),
            'hunt_hunting_company_coop' => get_post_meta( $maybe_id, 'va_hunt_hunting_company_coop', true ),
            'hunt_liability_insurance' => get_post_meta( $maybe_id, 'va_hunt_liability_insurance', true ),
            'hunt_damage_nature' => get_post_meta( $maybe_id, 'va_hunt_damage_nature', true ),
            'hunt_pricing_model' => get_post_meta( $maybe_id, 'va_hunt_pricing_model', true ),
            'hunt_travel_fee' => get_post_meta( $maybe_id, 'va_hunt_travel_fee', true ),
            'hunt_contract_type' => get_post_meta( $maybe_id, 'va_hunt_contract_type', true ),
            'hunt_site_conditions' => get_post_meta( $maybe_id, 'va_hunt_site_conditions', true ),
            'hunt_reference_media' => get_post_meta( $maybe_id, 'va_hunt_reference_media', true ),
            'hunt_agri_hectare_size' => get_post_meta( $maybe_id, 'va_hunt_agri_hectare_size', true ),
            'hunt_agri_culture_type' => get_post_meta( $maybe_id, 'va_hunt_agri_culture_type', true ),
            'hunt_urgent_immediate_start' => get_post_meta( $maybe_id, 'va_hunt_urgent_immediate_start', true ),
            'hunt_urgent_duty_phone' => get_post_meta( $maybe_id, 'va_hunt_urgent_duty_phone', true ),
            'hunt_long_term_yearly_discount' => get_post_meta( $maybe_id, 'va_hunt_long_term_yearly_discount', true ),
            'hunt_package_auto_seasonal' => get_post_meta( $maybe_id, 'va_hunt_package_auto_seasonal', true ),
            'hunt_package_monthly_model' => get_post_meta( $maybe_id, 'va_hunt_package_monthly_model', true ),
            'hunt_package_monitoring_intervention' => get_post_meta( $maybe_id, 'va_hunt_package_monitoring_intervention', true ),
            'hunt_type' => get_post_meta( $maybe_id, 'va_hunt_type', true ),
            'hunt_game_species' => get_post_meta( $maybe_id, 'va_hunt_game_species', true ),
            'hunt_country' => get_post_meta( $maybe_id, 'va_hunt_country', true ),
            'hunt_county' => get_post_meta( $maybe_id, 'va_hunt_county', true ),
            'hunt_area_name' => get_post_meta( $maybe_id, 'va_hunt_area_name', true ),
            'hunt_exact_area' => get_post_meta( $maybe_id, 'va_hunt_exact_area', true ),
            'hunt_gps_lat' => get_post_meta( $maybe_id, 'va_hunt_gps_lat', true ),
            'hunt_gps_lng' => get_post_meta( $maybe_id, 'va_hunt_gps_lng', true ),
            'hunt_terrain_types' => get_post_meta( $maybe_id, 'va_hunt_terrain_types', true ),
            'hunt_season_start' => get_post_meta( $maybe_id, 'va_hunt_season_start', true ),
            'hunt_season_end' => get_post_meta( $maybe_id, 'va_hunt_season_end', true ),
            'hunt_time_type' => get_post_meta( $maybe_id, 'va_hunt_time_type', true ),
            'hunt_price_type' => get_post_meta( $maybe_id, 'va_hunt_price_type', true ),
            'hunt_price_min' => get_post_meta( $maybe_id, 'va_hunt_price_min', true ),
            'hunt_price_max' => get_post_meta( $maybe_id, 'va_hunt_price_max', true ),
            'hunt_price_includes' => get_post_meta( $maybe_id, 'va_hunt_price_includes', true ),
            'hunt_guide_type' => get_post_meta( $maybe_id, 'va_hunt_guide_type', true ),
            'hunt_methods' => get_post_meta( $maybe_id, 'va_hunt_methods', true ),
            'hunt_onsite_services' => get_post_meta( $maybe_id, 'va_hunt_onsite_services', true ),
            'hunt_hunting_license_required' => get_post_meta( $maybe_id, 'va_hunt_hunting_license_required', true ),
            'hunt_permit_required' => get_post_meta( $maybe_id, 'va_hunt_permit_required', true ),
            'hunt_foreigners_allowed' => get_post_meta( $maybe_id, 'va_hunt_foreigners_allowed', true ),
            'hunt_min_age' => get_post_meta( $maybe_id, 'va_hunt_min_age', true ),
            'hunt_safety_features' => get_post_meta( $maybe_id, 'va_hunt_safety_features', true ),
            'hunt_conditions' => get_post_meta( $maybe_id, 'va_hunt_conditions', true ),
            'hunt_difficulty_level' => get_post_meta( $maybe_id, 'va_hunt_difficulty_level', true ),
            'hunt_expected_trophy_quality' => get_post_meta( $maybe_id, 'va_hunt_expected_trophy_quality', true ),
            'hunt_trophy_weight_age' => get_post_meta( $maybe_id, 'va_hunt_trophy_weight_age', true ),
            'hunt_required_media' => get_post_meta( $maybe_id, 'va_hunt_required_media', true ),
            'hunt_video_types' => get_post_meta( $maybe_id, 'va_hunt_video_types', true ),
            'hunt_moderation_flags' => get_post_meta( $maybe_id, 'va_hunt_moderation_flags', true ),
            'hunt_trophy_category' => get_post_meta( $maybe_id, 'va_hunt_trophy_category', true ),
            'hunt_trophy_weight_limit' => get_post_meta( $maybe_id, 'va_hunt_trophy_weight_limit', true ),
            'hunt_travel_assist' => get_post_meta( $maybe_id, 'va_hunt_travel_assist', true ),
            'hunt_interpreter' => get_post_meta( $maybe_id, 'va_hunt_interpreter', true ),
            'hunt_group_size' => get_post_meta( $maybe_id, 'va_hunt_group_size', true ),
            'hunt_shot_limit' => get_post_meta( $maybe_id, 'va_hunt_shot_limit', true ),
            'hunt_real_time_slots' => get_post_meta( $maybe_id, 'va_hunt_real_time_slots', true ),
            'hunt_bookable_dates' => get_post_meta( $maybe_id, 'va_hunt_bookable_dates', true ),
            'exchange_target' => get_post_meta( $maybe_id, 'va_exchange_target', true ),
            'exchange_type' => get_post_meta( $maybe_id, 'va_exchange_type', true ),
            'exchange_offer_category' => get_post_meta( $maybe_id, 'va_exchange_offer_category', true ),
            'exchange_item_name' => get_post_meta( $maybe_id, 'va_exchange_item_name', true ),
            'exchange_brand' => get_post_meta( $maybe_id, 'va_exchange_brand', true ),
            'exchange_model' => get_post_meta( $maybe_id, 'va_exchange_model', true ),
            'exchange_condition' => get_post_meta( $maybe_id, 'va_exchange_condition', true ),
            'exchange_short_desc' => get_post_meta( $maybe_id, 'va_exchange_short_desc', true ),
            'exchange_description' => get_post_meta( $maybe_id, 'va_exchange_description', true ),
            'exchange_defects' => get_post_meta( $maybe_id, 'va_exchange_defects', true ),
            'exchange_wear_signs' => get_post_meta( $maybe_id, 'va_exchange_wear_signs', true ),
            'exchange_estimated_value' => get_post_meta( $maybe_id, 'va_exchange_estimated_value', true ),
            'exchange_extra_payment_direction' => get_post_meta( $maybe_id, 'va_exchange_extra_payment_direction', true ),
            'exchange_extra_payment_amount' => get_post_meta( $maybe_id, 'va_exchange_extra_payment_amount', true ),
            'exchange_wanted_categories' => get_post_meta( $maybe_id, 'va_exchange_wanted_categories', true ),
            'exchange_country' => get_post_meta( $maybe_id, 'va_exchange_country', true ),
            'exchange_county' => get_post_meta( $maybe_id, 'va_exchange_county', true ),
            'exchange_city' => get_post_meta( $maybe_id, 'va_exchange_city', true ),
            'exchange_method' => get_post_meta( $maybe_id, 'va_exchange_method', true ),
            'exchange_legal_notes' => get_post_meta( $maybe_id, 'va_exchange_legal_notes', true ),
            'exchange_age_18' => get_post_meta( $maybe_id, 'va_exchange_age_18', true ),
            'exchange_required_images' => get_post_meta( $maybe_id, 'va_exchange_required_images', true ),
            'exchange_video_url' => get_post_meta( $maybe_id, 'va_exchange_video_url', true ),
            'exchange_moderation_flags' => get_post_meta( $maybe_id, 'va_exchange_moderation_flags', true ),
            'exchange_partner_requirements' => get_post_meta( $maybe_id, 'va_exchange_partner_requirements', true ),
            'exchange_firearm_caliber' => get_post_meta( $maybe_id, 'va_exchange_firearm_caliber', true ),
            'exchange_firearm_license' => get_post_meta( $maybe_id, 'va_exchange_firearm_license', true ),
            'exchange_optic_zoom' => get_post_meta( $maybe_id, 'va_exchange_optic_zoom', true ),
            'exchange_optic_objective' => get_post_meta( $maybe_id, 'va_exchange_optic_objective', true ),
            'exchange_knife_steel' => get_post_meta( $maybe_id, 'va_exchange_knife_steel', true ),
            'publication_type' => get_post_meta( $maybe_id, 'va_publication_type', true ),
            'publication_topics' => get_post_meta( $maybe_id, 'va_publication_topics', true ),
            'publication_title' => get_post_meta( $maybe_id, 'va_publication_title', true ),
            'publication_subtitle' => get_post_meta( $maybe_id, 'va_publication_subtitle', true ),
            'publication_author' => get_post_meta( $maybe_id, 'va_publication_author', true ),
            'publication_publisher' => get_post_meta( $maybe_id, 'va_publication_publisher', true ),
            'publication_year' => get_post_meta( $maybe_id, 'va_publication_year', true ),
            'publication_language' => get_post_meta( $maybe_id, 'va_publication_language', true ),
            'publication_edition_type' => get_post_meta( $maybe_id, 'va_publication_edition_type', true ),
            'publication_periodical_name' => get_post_meta( $maybe_id, 'va_publication_periodical_name', true ),
            'publication_volume' => get_post_meta( $maybe_id, 'va_publication_volume', true ),
            'publication_issue' => get_post_meta( $maybe_id, 'va_publication_issue', true ),
            'publication_complete_volume' => get_post_meta( $maybe_id, 'va_publication_complete_volume', true ),
            'publication_page_count' => get_post_meta( $maybe_id, 'va_publication_page_count', true ),
            'publication_cover_type' => get_post_meta( $maybe_id, 'va_publication_cover_type', true ),
            'publication_size' => get_post_meta( $maybe_id, 'va_publication_size', true ),
            'publication_condition' => get_post_meta( $maybe_id, 'va_publication_condition', true ),
            'publication_defects' => get_post_meta( $maybe_id, 'va_publication_defects', true ),
            'publication_content_flags' => get_post_meta( $maybe_id, 'va_publication_content_flags', true ),
            'publication_collector_flags' => get_post_meta( $maybe_id, 'va_publication_collector_flags', true ),
            'publication_collector_value' => get_post_meta( $maybe_id, 'va_publication_collector_value', true ),
            'publication_usage' => get_post_meta( $maybe_id, 'va_publication_usage', true ),
            'publication_condition_details' => get_post_meta( $maybe_id, 'va_publication_condition_details', true ),
            'publication_authenticity' => get_post_meta( $maybe_id, 'va_publication_authenticity', true ),
            'publication_signed_copy' => get_post_meta( $maybe_id, 'va_publication_signed_copy', true ),
            'publication_signed_to' => get_post_meta( $maybe_id, 'va_publication_signed_to', true ),
            'publication_signature_type' => get_post_meta( $maybe_id, 'va_publication_signature_type', true ),
            'publication_isbn' => get_post_meta( $maybe_id, 'va_publication_isbn', true ),
            'publication_digital_format' => get_post_meta( $maybe_id, 'va_publication_digital_format', true ),
            'publication_moderation_flags' => get_post_meta( $maybe_id, 'va_publication_moderation_flags', true ),
            'publication_notes' => get_post_meta( $maybe_id, 'va_publication_notes', true ),
            'equipment_type' => get_post_meta( $maybe_id, 'va_equipment_type', true ),
            'equipment_condition' => get_post_meta( $maybe_id, 'va_equipment_condition', true ),
            'equipment_hunting_usage' => get_post_meta( $maybe_id, 'va_equipment_hunting_usage', true ),
            'equipment_material_type' => get_post_meta( $maybe_id, 'va_equipment_material_type', true ),
            'equipment_color_pattern' => get_post_meta( $maybe_id, 'va_equipment_color_pattern', true ),
            'equipment_length_cm' => get_post_meta( $maybe_id, 'va_equipment_length_cm', true ),
            'equipment_width_cm' => get_post_meta( $maybe_id, 'va_equipment_width_cm', true ),
            'equipment_height_cm' => get_post_meta( $maybe_id, 'va_equipment_height_cm', true ),
            'equipment_weight_g' => get_post_meta( $maybe_id, 'va_equipment_weight_g', true ),
            'equipment_features' => get_post_meta( $maybe_id, 'va_equipment_features', true ),
            'equipment_light_lumen' => get_post_meta( $maybe_id, 'va_equipment_light_lumen', true ),
            'equipment_light_range_m' => get_post_meta( $maybe_id, 'va_equipment_light_range_m', true ),
            'equipment_light_battery_type' => get_post_meta( $maybe_id, 'va_equipment_light_battery_type', true ),
            'equipment_light_usb_charge' => get_post_meta( $maybe_id, 'va_equipment_light_usb_charge', true ),
            'equipment_light_color_modes' => get_post_meta( $maybe_id, 'va_equipment_light_color_modes', true ),
            'equipment_light_color_temperature' => get_post_meta( $maybe_id, 'va_equipment_light_color_temperature', true ),
            'equipment_support_height_range_cm' => get_post_meta( $maybe_id, 'va_equipment_support_height_range_cm', true ),
            'equipment_support_legs' => get_post_meta( $maybe_id, 'va_equipment_support_legs', true ),
            'equipment_support_head_swivel' => get_post_meta( $maybe_id, 'va_equipment_support_head_swivel', true ),
            'equipment_support_material' => get_post_meta( $maybe_id, 'va_equipment_support_material', true ),
            'equipment_call_type' => get_post_meta( $maybe_id, 'va_equipment_call_type', true ),
            'equipment_call_species' => get_post_meta( $maybe_id, 'va_equipment_call_species', true ),
            'equipment_call_volume' => get_post_meta( $maybe_id, 'va_equipment_call_volume', true ),
            'equipment_trap_type' => get_post_meta( $maybe_id, 'va_equipment_trap_type', true ),
            'equipment_trap_size' => get_post_meta( $maybe_id, 'va_equipment_trap_size', true ),
            'equipment_cleaning_caliber_compatibility' => get_post_meta( $maybe_id, 'va_equipment_cleaning_caliber_compatibility', true ),
            'equipment_cleaning_full_set' => get_post_meta( $maybe_id, 'va_equipment_cleaning_full_set', true ),
            'equipment_cleaning_oil' => get_post_meta( $maybe_id, 'va_equipment_cleaning_oil', true ),
            'equipment_cleaning_bronze_brush' => get_post_meta( $maybe_id, 'va_equipment_cleaning_bronze_brush', true ),
            'equipment_seat_foldable' => get_post_meta( $maybe_id, 'va_equipment_seat_foldable', true ),
            'equipment_seat_backrest' => get_post_meta( $maybe_id, 'va_equipment_seat_backrest', true ),
            'equipment_seat_swivel' => get_post_meta( $maybe_id, 'va_equipment_seat_swivel', true ),
            'equipment_seat_capacity_kg' => get_post_meta( $maybe_id, 'va_equipment_seat_capacity_kg', true ),
            'equipment_electronic_features' => get_post_meta( $maybe_id, 'va_equipment_electronic_features', true ),
            'equipment_runtime_hours' => get_post_meta( $maybe_id, 'va_equipment_runtime_hours', true ),
            'equipment_accessories' => get_post_meta( $maybe_id, 'va_equipment_accessories', true ),
            'equipment_silence_level' => get_post_meta( $maybe_id, 'va_equipment_silence_level', true ),
            'equipment_weapon_compatibility' => get_post_meta( $maybe_id, 'va_equipment_weapon_compatibility', true ),
            'equipment_caliber_compatibility' => get_post_meta( $maybe_id, 'va_equipment_caliber_compatibility', true ),
            'equipment_moderation_flags' => get_post_meta( $maybe_id, 'va_equipment_moderation_flags', true ),
            'equipment_notes' => get_post_meta( $maybe_id, 'va_equipment_notes', true ),
            'job_role' => get_post_meta( $maybe_id, 'va_job_role', true ),
            'job_role_other' => get_post_meta( $maybe_id, 'va_job_role_other', true ),
            'job_county' => get_post_meta( $maybe_id, 'va_job_county', true ),
            'job_country' => get_post_meta( $maybe_id, 'va_job_country', true ),
            'job_education' => get_post_meta( $maybe_id, 'va_job_education', true ),
            'job_hunting_license' => get_post_meta( $maybe_id, 'va_job_hunting_license', true ),
            'job_firearm_license' => get_post_meta( $maybe_id, 'va_job_firearm_license', true ),
            'job_drivers_license' => get_post_meta( $maybe_id, 'va_job_drivers_license', true ),
            'job_experience' => get_post_meta( $maybe_id, 'va_job_experience', true ),
            'job_language' => get_post_meta( $maybe_id, 'va_job_language', true ),
            'job_dog_experience' => get_post_meta( $maybe_id, 'va_job_dog_experience', true ),
            'job_trophy_experience' => get_post_meta( $maybe_id, 'va_job_trophy_experience', true ),
            'job_tasks' => get_post_meta( $maybe_id, 'va_job_tasks', true ),
            'job_work_time' => get_post_meta( $maybe_id, 'va_job_work_time', true ),
            'job_accommodation' => get_post_meta( $maybe_id, 'va_job_accommodation', true ),
            'job_meals' => get_post_meta( $maybe_id, 'va_job_meals', true ),
            'job_company_vehicle' => get_post_meta( $maybe_id, 'va_job_company_vehicle', true ),
            'job_company_weapon' => get_post_meta( $maybe_id, 'va_job_company_weapon', true ),
            'job_salary_type' => get_post_meta( $maybe_id, 'va_job_salary_type', true ),
            'job_salary_min' => get_post_meta( $maybe_id, 'va_job_salary_min', true ),
            'job_salary_max' => get_post_meta( $maybe_id, 'va_job_salary_max', true ),
            'job_salary_currency' => get_post_meta( $maybe_id, 'va_job_salary_currency', true ),
            'bow_type' => get_post_meta( $maybe_id, 'va_bow_type', true ),
            'bow_handedness' => get_post_meta( $maybe_id, 'va_bow_handedness', true ),
            'bow_draw_weight' => get_post_meta( $maybe_id, 'va_bow_draw_weight', true ),
            'bow_draw_length' => get_post_meta( $maybe_id, 'va_bow_draw_length', true ),
            'bow_axle_length' => get_post_meta( $maybe_id, 'va_bow_axle_length', true ),
            'bow_item_condition' => get_post_meta( $maybe_id, 'va_bow_item_condition', true ),
            'bow_condition_notes' => get_post_meta( $maybe_id, 'va_bow_condition_notes', true ),
            'bow_string_replaced' => get_post_meta( $maybe_id, 'va_bow_string_replaced', true ),
            'bow_owned_since' => get_post_meta( $maybe_id, 'va_bow_owned_since', true ),
            'bow_usage' => get_post_meta( $maybe_id, 'va_bow_usage', true ),
            'bow_accessories' => get_post_meta( $maybe_id, 'va_bow_accessories', true ),
            'bow_cams_cables' => get_post_meta( $maybe_id, 'va_bow_cams_cables', true ),
            'bow_speed_fps' => get_post_meta( $maybe_id, 'va_bow_speed_fps', true ),
            'bow_serial' => get_post_meta( $maybe_id, 'va_bow_serial', true ),
            'bow_material' => get_post_meta( $maybe_id, 'va_bow_material', true ),
            'other_weapon_kind' => get_post_meta( $maybe_id, 'va_other_weapon_kind', true ),
            'other_weapon_general_notes' => get_post_meta( $maybe_id, 'va_other_weapon_general_notes', true ),
            'other_weapon_accessories' => get_post_meta( $maybe_id, 'va_other_weapon_accessories', true ),
            'other_weapon_accessories_other' => get_post_meta( $maybe_id, 'va_other_weapon_accessories_other', true ),
            'other_weapon_18plus' => get_post_meta( $maybe_id, 'va_other_weapon_18plus', true ),
            'other_weapon_permit_required' => get_post_meta( $maybe_id, 'va_other_weapon_permit_required', true ),
            'other_weapon_personal_pickup' => get_post_meta( $maybe_id, 'va_other_weapon_personal_pickup', true ),
            'other_weapon_origin_papers' => get_post_meta( $maybe_id, 'va_other_weapon_origin_papers', true ),
            'other_weapon_serial_number' => get_post_meta( $maybe_id, 'va_other_weapon_serial_number', true ),
            'other_weapon_bow_draw_weight_lbs' => get_post_meta( $maybe_id, 'va_other_weapon_bow_draw_weight_lbs', true ),
            'other_weapon_bow_axle_to_axle' => get_post_meta( $maybe_id, 'va_other_weapon_bow_axle_to_axle', true ),
            'other_weapon_bow_handness' => get_post_meta( $maybe_id, 'va_other_weapon_bow_handness', true ),
            'other_weapon_bow_style' => get_post_meta( $maybe_id, 'va_other_weapon_bow_style', true ),
            'other_weapon_bow_arrow_rest' => get_post_meta( $maybe_id, 'va_other_weapon_bow_arrow_rest', true ),
            'other_weapon_bow_sight' => get_post_meta( $maybe_id, 'va_other_weapon_bow_sight', true ),
            'other_weapon_crossbow_draw_weight_lbs' => get_post_meta( $maybe_id, 'va_other_weapon_crossbow_draw_weight_lbs', true ),
            'other_weapon_crossbow_fps' => get_post_meta( $maybe_id, 'va_other_weapon_crossbow_fps', true ),
            'other_weapon_crossbow_accessories' => get_post_meta( $maybe_id, 'va_other_weapon_crossbow_accessories', true ),
            'other_weapon_airsoft_operation' => get_post_meta( $maybe_id, 'va_other_weapon_airsoft_operation', true ),
            'other_weapon_airsoft_energy_joule' => get_post_meta( $maybe_id, 'va_other_weapon_airsoft_energy_joule', true ),
            'other_weapon_airsoft_fps' => get_post_meta( $maybe_id, 'va_other_weapon_airsoft_fps', true ),
            'other_weapon_airsoft_magazine_type' => get_post_meta( $maybe_id, 'va_other_weapon_airsoft_magazine_type', true ),
            'other_weapon_airgun_caliber' => get_post_meta( $maybe_id, 'va_other_weapon_airgun_caliber', true ),
            'other_weapon_airgun_muzzle_energy' => get_post_meta( $maybe_id, 'va_other_weapon_airgun_muzzle_energy', true ),
            'other_weapon_airgun_power_source' => get_post_meta( $maybe_id, 'va_other_weapon_airgun_power_source', true ),
            'other_weapon_gas_alarm_caliber' => get_post_meta( $maybe_id, 'va_other_weapon_gas_alarm_caliber', true ),
            'other_weapon_gas_alarm_self_defense_license' => get_post_meta( $maybe_id, 'va_other_weapon_gas_alarm_self_defense_license', true ),
            'other_weapon_gas_alarm_carry_license' => get_post_meta( $maybe_id, 'va_other_weapon_gas_alarm_carry_license', true ),
            'other_weapon_knife_blade_length' => get_post_meta( $maybe_id, 'va_other_weapon_knife_blade_length', true ),
            'other_weapon_knife_steel_type' => get_post_meta( $maybe_id, 'va_other_weapon_knife_steel_type', true ),
            'other_weapon_knife_handle_material' => get_post_meta( $maybe_id, 'va_other_weapon_knife_handle_material', true ),
            'other_weapon_knife_sheath' => get_post_meta( $maybe_id, 'va_other_weapon_knife_sheath', true ),
            'other_weapon_knife_accessories' => get_post_meta( $maybe_id, 'va_other_weapon_knife_accessories', true ),
            'hatastalanitott_weapon_type' => get_post_meta( $maybe_id, 'va_hatastalanitott_weapon_type', true ),
            'hatastalanitott_origin_country' => get_post_meta( $maybe_id, 'va_hatastalanitott_origin_country', true ),
            'hatastalanitott_is_deactivated' => get_post_meta( $maybe_id, 'va_hatastalanitott_is_deactivated', true ),
            'hatastalanitott_deactivation_type' => get_post_meta( $maybe_id, 'va_hatastalanitott_deactivation_type', true ),
            'hatastalanitott_certificate' => get_post_meta( $maybe_id, 'va_hatastalanitott_certificate', true ),
            'hatastalanitott_mkh_cip' => get_post_meta( $maybe_id, 'va_hatastalanitott_mkh_cip', true ),
            'hatastalanitott_deactivation_year' => get_post_meta( $maybe_id, 'va_hatastalanitott_deactivation_year', true ),
            'hatastalanitott_deactivation_org' => get_post_meta( $maybe_id, 'va_hatastalanitott_deactivation_org', true ),
            'hatastalanitott_condition' => get_post_meta( $maybe_id, 'va_hatastalanitott_condition', true ),
            'hatastalanitott_serial_admin' => get_post_meta( $maybe_id, 'va_hatastalanitott_serial_admin', true ),
            'hatastalanitott_working_parts' => get_post_meta( $maybe_id, 'va_hatastalanitott_working_parts', true ),
            'hatastalanitott_original_finish' => get_post_meta( $maybe_id, 'va_hatastalanitott_original_finish', true ),
            'hatastalanitott_original_parts_ratio' => get_post_meta( $maybe_id, 'va_hatastalanitott_original_parts_ratio', true ),
            'hatastalanitott_accessories' => get_post_meta( $maybe_id, 'va_hatastalanitott_accessories', true ),
            'loszer_category' => get_post_meta( $maybe_id, 'va_loszer_category', true ),
            'loszer_caliber_preset' => get_post_meta( $maybe_id, 'va_loszer_caliber_preset', true ),
            'loszer_caliber_custom' => get_post_meta( $maybe_id, 'va_loszer_caliber_custom', true ),
            'loszer_manufacturer_preset' => get_post_meta( $maybe_id, 'va_loszer_manufacturer_preset', true ),
            'loszer_manufacturer_custom' => get_post_meta( $maybe_id, 'va_loszer_manufacturer_custom', true ),
            'loszer_projectile_type' => get_post_meta( $maybe_id, 'va_loszer_projectile_type', true ),
            'loszer_projectile_weight' => get_post_meta( $maybe_id, 'va_loszer_projectile_weight', true ),
            'loszer_projectile_weight_unit' => get_post_meta( $maybe_id, 'va_loszer_projectile_weight_unit', true ),
            'loszer_piece_count' => get_post_meta( $maybe_id, 'va_loszer_piece_count', true ),
            'loszer_piece_unit' => get_post_meta( $maybe_id, 'va_loszer_piece_unit', true ),
            'loszer_lot_year' => get_post_meta( $maybe_id, 'va_loszer_lot_year', true ),
            'loszer_condition' => get_post_meta( $maybe_id, 'va_loszer_condition', true ),
            'loszer_component_case_condition' => get_post_meta( $maybe_id, 'va_loszer_component_case_condition', true ),
            'loszer_powder_type' => get_post_meta( $maybe_id, 'va_loszer_powder_type', true ),
            'loszer_powder_sealed' => get_post_meta( $maybe_id, 'va_loszer_powder_sealed', true ),
            'loszer_powder_weight' => get_post_meta( $maybe_id, 'va_loszer_powder_weight', true ),
            'loszer_primer_type' => get_post_meta( $maybe_id, 'va_loszer_primer_type', true ),
            'bow_acc_sight' => get_post_meta( $maybe_id, 'va_bow_acc_sight', true ),
            'bow_acc_release' => get_post_meta( $maybe_id, 'va_bow_acc_release', true ),
            'bow_acc_stabilizer' => get_post_meta( $maybe_id, 'va_bow_acc_stabilizer', true ),
            'bow_acc_arrows' => get_post_meta( $maybe_id, 'va_bow_acc_arrows', true ),
            'bow_acc_case' => get_post_meta( $maybe_id, 'va_bow_acc_case', true ),
            'bow_acc_quiver' => get_post_meta( $maybe_id, 'va_bow_acc_quiver', true ),
            'bow_acc_target' => get_post_meta( $maybe_id, 'va_bow_acc_target', true ),
            'feed_type' => get_post_meta( $maybe_id, 'va_feed_type', true ),
            'feed_recommended_game' => get_post_meta( $maybe_id, 'va_feed_recommended_game', true ),
            'feed_game_boar' => get_post_meta( $maybe_id, 'va_feed_game_boar', true ),
            'feed_game_roe' => get_post_meta( $maybe_id, 'va_feed_game_roe', true ),
            'feed_game_red_deer' => get_post_meta( $maybe_id, 'va_feed_game_red_deer', true ),
            'feed_game_pheasant' => get_post_meta( $maybe_id, 'va_feed_game_pheasant', true ),
            'feed_game_other' => get_post_meta( $maybe_id, 'va_feed_game_other', true ),
            'feed_game_other_text' => get_post_meta( $maybe_id, 'va_feed_game_other_text', true ),
            'feed_ingredients' => get_post_meta( $maybe_id, 'va_feed_ingredients', true ),
            'feed_moisture' => get_post_meta( $maybe_id, 'va_feed_moisture', true ),
            'feed_has_additives' => get_post_meta( $maybe_id, 'va_feed_has_additives', true ),
            'feed_packaging_type' => get_post_meta( $maybe_id, 'va_feed_packaging_type', true ),
            'feed_bag_weight' => get_post_meta( $maybe_id, 'va_feed_bag_weight', true ),
            'feed_total_quantity' => get_post_meta( $maybe_id, 'va_feed_total_quantity', true ),
            'feed_pallet_quantity' => get_post_meta( $maybe_id, 'va_feed_pallet_quantity', true ),
            'feed_harvest_freshness' => get_post_meta( $maybe_id, 'va_feed_harvest_freshness', true ),
            'feed_stored_dry' => get_post_meta( $maybe_id, 'va_feed_stored_dry', true ),
            'feed_mold_free' => get_post_meta( $maybe_id, 'va_feed_mold_free', true ),
            'feed_bags_undamaged' => get_post_meta( $maybe_id, 'va_feed_bags_undamaged', true ),
            'feed_price_per_unit' => get_post_meta( $maybe_id, 'va_feed_price_per_unit', true ),
            'feed_min_order' => get_post_meta( $maybe_id, 'va_feed_min_order', true ),
            'feed_delivery_available' => get_post_meta( $maybe_id, 'va_feed_delivery_available', true ),
            'feed_loading_available' => get_post_meta( $maybe_id, 'va_feed_loading_available', true ),
            'job_location' => get_post_meta( $maybe_id, 'va_job_location', true ),
            'job_type' => get_post_meta( $maybe_id, 'va_job_type', true ),
            'service_type' => get_post_meta( $maybe_id, 'va_service_type', true ),
            'service_provider_type' => get_post_meta( $maybe_id, 'va_service_provider_type', true ),
            'service_provider_name' => get_post_meta( $maybe_id, 'va_service_provider_name', true ),
            'service_country' => get_post_meta( $maybe_id, 'va_service_country', true ),
            'service_county' => get_post_meta( $maybe_id, 'va_service_county', true ),
            'service_town' => get_post_meta( $maybe_id, 'va_service_town', true ),
            'service_area' => get_post_meta( $maybe_id, 'va_service_area', true ),
            'service_gps_lat' => get_post_meta( $maybe_id, 'va_service_gps_lat', true ),
            'service_gps_lng' => get_post_meta( $maybe_id, 'va_service_gps_lng', true ),
            'service_pricing_type' => get_post_meta( $maybe_id, 'va_service_pricing_type', true ),
            'service_min_price' => get_post_meta( $maybe_id, 'va_service_min_price', true ),
            'service_travel_fee' => get_post_meta( $maybe_id, 'va_service_travel_fee', true ),
            'service_schedule' => get_post_meta( $maybe_id, 'va_service_schedule', true ),
            'service_available_weekend' => get_post_meta( $maybe_id, 'va_service_available_weekend', true ),
            'service_available_24_7' => get_post_meta( $maybe_id, 'va_service_available_24_7', true ),
            'service_seasonal' => get_post_meta( $maybe_id, 'va_service_seasonal', true ),
            'service_hunting_specialization' => get_post_meta( $maybe_id, 'va_service_hunting_specialization', true ),
            'service_hunting_species' => get_post_meta( $maybe_id, 'va_service_hunting_species', true ),
            'service_weapon_categories' => get_post_meta( $maybe_id, 'va_service_weapon_categories', true ),
            'service_equipment' => get_post_meta( $maybe_id, 'va_service_equipment', true ),
            'service_permits' => get_post_meta( $maybe_id, 'va_service_permits', true ),
            'service_reference_rating' => get_post_meta( $maybe_id, 'va_service_reference_rating', true ),
            'service_completed_jobs' => get_post_meta( $maybe_id, 'va_service_completed_jobs', true ),
            'service_reference_notes' => get_post_meta( $maybe_id, 'va_service_reference_notes', true ),
            'service_media_required' => get_post_meta( $maybe_id, 'va_service_media_required', true ),
            'service_media_video' => get_post_meta( $maybe_id, 'va_service_media_video', true ),
            'service_verified_provider' => get_post_meta( $maybe_id, 'va_service_verified_provider', true ),
            'service_document_verified' => get_post_meta( $maybe_id, 'va_service_document_verified', true ),
            'service_phone_verified' => get_post_meta( $maybe_id, 'va_service_phone_verified', true ),
            'service_business_verified' => get_post_meta( $maybe_id, 'va_service_business_verified', true ),
            'service_sos_service' => get_post_meta( $maybe_id, 'va_service_sos_service', true ),
            'service_urgent_repair' => get_post_meta( $maybe_id, 'va_service_urgent_repair', true ),
            'service_full_prep' => get_post_meta( $maybe_id, 'va_service_full_prep', true ),
            'service_night_work' => get_post_meta( $maybe_id, 'va_service_night_work', true ),
            'service_caliber_support' => get_post_meta( $maybe_id, 'va_service_caliber_support', true ),
            'service_moderation_flags' => get_post_meta( $maybe_id, 'va_service_moderation_flags', true ),
            'year'        => get_post_meta( $maybe_id, 'va_year',        true ),
            'license_req' => get_post_meta( $maybe_id, 'va_license_req', true ),
            'rifle_type' => get_post_meta( $maybe_id, 'va_rifle_type', true ),
            'rifle_caliber' => get_post_meta( $maybe_id, 'va_rifle_caliber', true ),
            'rifle_barrel_length_cm' => get_post_meta( $maybe_id, 'va_rifle_barrel_length_cm', true ),
            'rifle_barrel_material' => get_post_meta( $maybe_id, 'va_rifle_barrel_material', true ),
            'rifle_rifling_type' => get_post_meta( $maybe_id, 'va_rifle_rifling_type', true ),
            'rifle_barrel_condition' => get_post_meta( $maybe_id, 'va_rifle_barrel_condition', true ),
            'rifle_accuracy_moa' => get_post_meta( $maybe_id, 'va_rifle_accuracy_moa', true ),
            'rifle_zero_distance_m' => get_post_meta( $maybe_id, 'va_rifle_zero_distance_m', true ),
            'rifle_factory_group' => get_post_meta( $maybe_id, 'va_rifle_factory_group', true ),
            'rifle_action_type' => get_post_meta( $maybe_id, 'va_rifle_action_type', true ),
            'rifle_trigger_type' => get_post_meta( $maybe_id, 'va_rifle_trigger_type', true ),
            'rifle_safety_type' => get_post_meta( $maybe_id, 'va_rifle_safety_type', true ),
            'rifle_stock_material' => get_post_meta( $maybe_id, 'va_rifle_stock_material', true ),
            'rifle_optic_rail_type' => get_post_meta( $maybe_id, 'va_rifle_optic_rail_type', true ),
            'rifle_optic_compatibility' => get_post_meta( $maybe_id, 'va_rifle_optic_compatibility', true ),
            'rifle_zero_return' => get_post_meta( $maybe_id, 'va_rifle_zero_return', true ),
            'rifle_accessories' => get_post_meta( $maybe_id, 'va_rifle_accessories', true ),
            'rifle_weight_kg' => get_post_meta( $maybe_id, 'va_rifle_weight_kg', true ),
            'rifle_total_length_cm' => get_post_meta( $maybe_id, 'va_rifle_total_length_cm', true ),
            'rifle_usage' => get_post_meta( $maybe_id, 'va_rifle_usage', true ),
            'rifle_special_build' => get_post_meta( $maybe_id, 'va_rifle_special_build', true ),
            'rifle_barrel_mount_type' => get_post_meta( $maybe_id, 'va_rifle_barrel_mount_type', true ),
            'rifle_impact_safe' => get_post_meta( $maybe_id, 'va_rifle_impact_safe', true ),
            'rifle_closed_system' => get_post_meta( $maybe_id, 'va_rifle_closed_system', true ),
            'rifle_effective_range_m' => get_post_meta( $maybe_id, 'va_rifle_effective_range_m', true ),
            'rifle_stability_level' => get_post_meta( $maybe_id, 'va_rifle_stability_level', true ),
            'rifle_recoil_level' => get_post_meta( $maybe_id, 'va_rifle_recoil_level', true ),
            'rifle_temperament' => get_post_meta( $maybe_id, 'va_rifle_temperament', true ),
            'rifle_mountain_weight_optimized' => get_post_meta( $maybe_id, 'va_rifle_mountain_weight_optimized', true ),
            'rifle_magazine_capacity' => get_post_meta( $maybe_id, 'va_rifle_magazine_capacity', true ),
            'rifle_moderation_flags' => get_post_meta( $maybe_id, 'va_rifle_moderation_flags', true ),
            'rifle_hunter_profile_game' => get_post_meta( $maybe_id, 'va_rifle_hunter_profile_game', true ),
            'rifle_hunter_profile_optic' => get_post_meta( $maybe_id, 'va_rifle_hunter_profile_optic', true ),
            'rifle_hunter_profile_distance' => get_post_meta( $maybe_id, 'va_rifle_hunter_profile_distance', true ),
            'marok_type' => get_post_meta( $maybe_id, 'va_marok_type', true ),
            'marok_condition' => get_post_meta( $maybe_id, 'va_marok_condition', true ),
            'mixed_type' => get_post_meta( $maybe_id, 'va_mixed_type', true ),
            'mixed_barrel_count' => get_post_meta( $maybe_id, 'va_mixed_barrel_count', true ),
            'mixed_has_rifle' => get_post_meta( $maybe_id, 'va_mixed_has_rifle', true ),
            'mixed_rifle_caliber' => get_post_meta( $maybe_id, 'va_mixed_rifle_caliber', true ),
            'mixed_has_shotgun' => get_post_meta( $maybe_id, 'va_mixed_has_shotgun', true ),
            'mixed_shotgun_caliber' => get_post_meta( $maybe_id, 'va_mixed_shotgun_caliber', true ),
            'mixed_trigger_type' => get_post_meta( $maybe_id, 'va_mixed_trigger_type', true ),
            'mixed_action_system' => get_post_meta( $maybe_id, 'va_mixed_action_system', true ),
            'mixed_lock_type' => get_post_meta( $maybe_id, 'va_mixed_lock_type', true ),
            'mixed_sight_type' => get_post_meta( $maybe_id, 'va_mixed_sight_type', true ),
            'mixed_optic_rail' => get_post_meta( $maybe_id, 'va_mixed_optic_rail', true ),
            'mixed_optic_compatible' => get_post_meta( $maybe_id, 'va_mixed_optic_compatible', true ),
            'mixed_barrel_length_cm' => get_post_meta( $maybe_id, 'va_mixed_barrel_length_cm', true ),
            'mixed_barrel_material' => get_post_meta( $maybe_id, 'va_mixed_barrel_material', true ),
            'mixed_barrel_regulated' => get_post_meta( $maybe_id, 'va_mixed_barrel_regulated', true ),
            'mixed_material' => get_post_meta( $maybe_id, 'va_mixed_material', true ),
            'mixed_stock_material' => get_post_meta( $maybe_id, 'va_mixed_stock_material', true ),
            'mixed_usage' => get_post_meta( $maybe_id, 'va_mixed_usage', true ),
            'mixed_weight_kg' => get_post_meta( $maybe_id, 'va_mixed_weight_kg', true ),
            'mixed_total_length_cm' => get_post_meta( $maybe_id, 'va_mixed_total_length_cm', true ),
            'mixed_accessories' => get_post_meta( $maybe_id, 'va_mixed_accessories', true ),
            'mixed_rifle_moa' => get_post_meta( $maybe_id, 'va_mixed_rifle_moa', true ),
            'mixed_shot_pattern' => get_post_meta( $maybe_id, 'va_mixed_shot_pattern', true ),
            'mixed_zero_distance_m' => get_post_meta( $maybe_id, 'va_mixed_zero_distance_m', true ),
            'mixed_safety_type' => get_post_meta( $maybe_id, 'va_mixed_safety_type', true ),
            'mixed_barrel_layout' => get_post_meta( $maybe_id, 'va_mixed_barrel_layout', true ),
            'mixed_barrel_internal' => get_post_meta( $maybe_id, 'va_mixed_barrel_internal', true ),
            'mixed_engraving' => get_post_meta( $maybe_id, 'va_mixed_engraving', true ),
            'mixed_factory_marking' => get_post_meta( $maybe_id, 'va_mixed_factory_marking', true ),
            'mixed_drilling_barrel1_caliber' => get_post_meta( $maybe_id, 'va_mixed_drilling_barrel1_caliber', true ),
            'mixed_drilling_top_barrel_role' => get_post_meta( $maybe_id, 'va_mixed_drilling_top_barrel_role', true ),
            'mixed_optic_mount_type' => get_post_meta( $maybe_id, 'va_mixed_optic_mount_type', true ),
            'mixed_mountain_weight_optimized' => get_post_meta( $maybe_id, 'va_mixed_mountain_weight_optimized', true ),
            'mixed_universal_label' => get_post_meta( $maybe_id, 'va_mixed_universal_label', true ),
            'mixed_condition' => get_post_meta( $maybe_id, 'va_mixed_condition', true ),
            'decor_type' => get_post_meta( $maybe_id, 'va_decor_type', true ),
            'decor_material' => get_post_meta( $maybe_id, 'va_decor_material', true ),
            'decor_wood_type' => get_post_meta( $maybe_id, 'va_decor_wood_type', true ),
            'decor_metal_alloy' => get_post_meta( $maybe_id, 'va_decor_metal_alloy', true ),
            'decor_style' => get_post_meta( $maybe_id, 'va_decor_style', true ),
            'decor_motif_tags' => get_post_meta( $maybe_id, 'va_decor_motif_tags', true ),
            'decor_finish' => get_post_meta( $maybe_id, 'va_decor_finish', true ),
            'decor_detailing' => get_post_meta( $maybe_id, 'va_decor_detailing', true ),
            'decor_era' => get_post_meta( $maybe_id, 'va_decor_era', true ),
            'decor_production_type' => get_post_meta( $maybe_id, 'va_decor_production_type', true ),
            'decor_function' => get_post_meta( $maybe_id, 'va_decor_function', true ),
            'decor_thematic_group' => get_post_meta( $maybe_id, 'va_decor_thematic_group', true ),
            'decor_special_features' => get_post_meta( $maybe_id, 'va_decor_special_features', true ),
            'decor_mounting' => get_post_meta( $maybe_id, 'va_decor_mounting', true ),
            'decor_size_class' => get_post_meta( $maybe_id, 'va_decor_size_class', true ),
            'decor_condition' => get_post_meta( $maybe_id, 'va_decor_condition', true ),
            'decor_replica_type' => get_post_meta( $maybe_id, 'va_decor_replica_type', true ),
            'decor_authenticity_level' => get_post_meta( $maybe_id, 'va_decor_authenticity_level', true ),
            'decor_collectible_flag' => get_post_meta( $maybe_id, 'va_decor_collectible_flag', true ),
            'marok_manufacture_year' => get_post_meta( $maybe_id, 'va_marok_manufacture_year', true ),
            'marok_origin_country' => get_post_meta( $maybe_id, 'va_marok_origin_country', true ),
            'marok_action_type' => get_post_meta( $maybe_id, 'va_marok_action_type', true ),
            'marok_magazine_capacity' => get_post_meta( $maybe_id, 'va_marok_magazine_capacity', true ),
            'marok_cylinder_capacity' => get_post_meta( $maybe_id, 'va_marok_cylinder_capacity', true ),
            'marok_revolver_action' => get_post_meta( $maybe_id, 'va_marok_revolver_action', true ),
            'marok_barrel_length_mm' => get_post_meta( $maybe_id, 'va_marok_barrel_length_mm', true ),
            'marok_total_length_mm' => get_post_meta( $maybe_id, 'va_marok_total_length_mm', true ),
            'marok_weight_g' => get_post_meta( $maybe_id, 'va_marok_weight_g', true ),
            'marok_frame_material' => get_post_meta( $maybe_id, 'va_marok_frame_material', true ),
            'marok_slide_material' => get_post_meta( $maybe_id, 'va_marok_slide_material', true ),
            'marok_sight_type' => get_post_meta( $maybe_id, 'va_marok_sight_type', true ),
            'marok_optic_ready' => get_post_meta( $maybe_id, 'va_marok_optic_ready', true ),
            'marok_optic_mount' => get_post_meta( $maybe_id, 'va_marok_optic_mount', true ),
            'marok_red_dot_compatibility' => get_post_meta( $maybe_id, 'va_marok_red_dot_compatibility', true ),
            'marok_threaded_barrel' => get_post_meta( $maybe_id, 'va_marok_threaded_barrel', true ),
            'marok_rail_type' => get_post_meta( $maybe_id, 'va_marok_rail_type', true ),
            'marok_accessories' => get_post_meta( $maybe_id, 'va_marok_accessories', true ),
            'marok_license_required' => get_post_meta( $maybe_id, 'va_marok_license_required', true ),
            'marok_legal_category' => get_post_meta( $maybe_id, 'va_marok_legal_category', true ),
            'marok_technical_exam_valid' => get_post_meta( $maybe_id, 'va_marok_technical_exam_valid', true ),
            'marok_cip_marking' => get_post_meta( $maybe_id, 'va_marok_cip_marking', true ),
            'marok_transfer_license_only' => get_post_meta( $maybe_id, 'va_marok_transfer_license_only', true ),
            'marok_personal_pickup' => get_post_meta( $maybe_id, 'va_marok_personal_pickup', true ),
            'marok_serial_masked' => get_post_meta( $maybe_id, 'va_marok_serial_masked', true ),
            'marok_usage' => get_post_meta( $maybe_id, 'va_marok_usage', true ),
            'marok_price_deal' => get_post_meta( $maybe_id, 'va_marok_price_deal', true ),
            'marok_price_exchange' => get_post_meta( $maybe_id, 'va_marok_price_exchange', true ),
            'marok_transfer_methods' => get_post_meta( $maybe_id, 'va_marok_transfer_methods', true ),
            'marok_required_media' => get_post_meta( $maybe_id, 'va_marok_required_media', true ),
            'marok_recommended_media' => get_post_meta( $maybe_id, 'va_marok_recommended_media', true ),
            'marok_round_count' => get_post_meta( $maybe_id, 'va_marok_round_count', true ),
            'marok_trigger_tuning' => get_post_meta( $maybe_id, 'va_marok_trigger_tuning', true ),
            'marok_sport_categories' => get_post_meta( $maybe_id, 'va_marok_sport_categories', true ),
            'marok_ipsc_ready' => get_post_meta( $maybe_id, 'va_marok_ipsc_ready', true ),
            'marok_race_trigger' => get_post_meta( $maybe_id, 'va_marok_race_trigger', true ),
            'marok_moderation_flags' => get_post_meta( $maybe_id, 'va_marok_moderation_flags', true ),
            // Jármű extra mezők
            'mileage'          => get_post_meta( $maybe_id, 'va_mileage',          true ),
            'fuel_type'        => get_post_meta( $maybe_id, 'va_fuel_type',        true ),
            'performance_kw'   => get_post_meta( $maybe_id, 'va_performance_kw',   true ),
            'engine_size'      => get_post_meta( $maybe_id, 'va_engine_size',      true ),
            'transmission'     => get_post_meta( $maybe_id, 'va_transmission',     true ),
            'color'            => get_post_meta( $maybe_id, 'va_color',            true ),
            'doors'            => get_post_meta( $maybe_id, 'va_doors',            true ),
            'drive'            => get_post_meta( $maybe_id, 'va_drive',            true ),
            'vehicle_condition'=> get_post_meta( $maybe_id, 'va_vehicle_condition',true ),
            'doc_type'         => get_post_meta( $maybe_id, 'va_doc_type',         true ),
            'doc_validity'     => get_post_meta( $maybe_id, 'va_doc_validity',     true ),
            'ac_type'          => get_post_meta( $maybe_id, 'va_ac_type',          true ),
            'eco_class'        => get_post_meta( $maybe_id, 'va_eco_class',        true ),
            'cylinder_layout'  => get_post_meta( $maybe_id, 'va_cylinder_layout',  true ),
            'own_weight'       => get_post_meta( $maybe_id, 'va_own_weight',       true ),
            'gross_weight'     => get_post_meta( $maybe_id, 'va_gross_weight',     true ),
            'passengers'       => get_post_meta( $maybe_id, 'va_passengers',       true ),
            'trunk_liters'     => get_post_meta( $maybe_id, 'va_trunk_liters',     true ),
            'range_gearbox'    => get_post_meta( $maybe_id, 'va_range_gearbox',    true ),
            'roof_type'        => get_post_meta( $maybe_id, 'va_roof_type',        true ),
            'color_metallic'   => get_post_meta( $maybe_id, 'va_color_metallic',   true ),
            'upholstery_1'     => get_post_meta( $maybe_id, 'va_upholstery_1',     true ),
            'upholstery_2'     => get_post_meta( $maybe_id, 'va_upholstery_2',     true ),
            'summer_tire_front'=> get_post_meta( $maybe_id, 'va_summer_tire_front',true ),
            'summer_tire_rear' => get_post_meta( $maybe_id, 'va_summer_tire_rear', true ),
            'winter_tire_front'=> get_post_meta( $maybe_id, 'va_winter_tire_front',true ),
            'winter_tire_rear' => get_post_meta( $maybe_id, 'va_winter_tire_rear', true ),
            'vin'              => get_post_meta( $maybe_id, 'va_vin',              true ),
            'internal_id'      => get_post_meta( $maybe_id, 'va_internal_id',      true ),
            'second_phone'     => get_post_meta( $maybe_id, 'va_second_phone',     true ),
            'vehicle_type'     => get_post_meta( $maybe_id, 'va_vehicle_type',     true ),
            'email_show'       => get_post_meta( $maybe_id, 'va_email_show',        true ),
            'tech_inspect'     => get_post_meta( $maybe_id, 'va_tech_inspect',     true ),
            'first_reg'        => get_post_meta( $maybe_id, 'va_first_reg',        true ),
            'owners'           => get_post_meta( $maybe_id, 'va_owners',           true ),
            'keys'             => get_post_meta( $maybe_id, 'va_keys',             true ),
            'previous_damage'  => get_post_meta( $maybe_id, 'va_previous_damage',  true ),
            'service_book'     => get_post_meta( $maybe_id, 'va_service_book',     true ),
            'extras'           => (function( $raw ) {
                                    if ( ! is_string( $raw ) || $raw === '' ) return [];
                                    $d = json_decode( $raw, true );
                                    return is_array( $d ) ? $d : [];
                                })( get_post_meta( $maybe_id, 'va_extras', true ) ),
            'category'    => (int) ( wp_get_post_terms( $maybe_id, 'va_category', ['fields'=>'ids'] )[0] ?? 0 ),
            'county'      => (int) ( wp_get_post_terms( $maybe_id, 'va_county',   ['fields'=>'ids'] )[0] ?? 0 ),
            'condition'   => (int) ( wp_get_post_terms( $maybe_id, 'va_condition',['fields'=>'ids'] )[0] ?? 0 ),
        ];
        // Meglévő képek betöltése (új + legacy meta kompatibilitás)
        $edit_thumb = (int) get_post_thumbnail_id( $maybe_id );

        $raw_gallery = get_post_meta( $maybe_id, 'va_gallery_ids', true );
        $edit_gallery = array_filter( array_map( 'absint', explode( ',', (string) $raw_gallery ) ) );

        // Legacy: régi kulcs lehet tömb vagy vesszős string
        if ( empty( $edit_gallery ) ) {
            $legacy_gallery = get_post_meta( $maybe_id, 'va_gallery', true );
            if ( is_array( $legacy_gallery ) ) {
                $edit_gallery = array_filter( array_map( 'absint', $legacy_gallery ) );
            } elseif ( is_string( $legacy_gallery ) && $legacy_gallery !== '' ) {
                $edit_gallery = array_filter( array_map( 'absint', explode( ',', $legacy_gallery ) ) );
            }
        }

        // Ha nincs gallery meta, de van kiemelt kép, akkor azt is mutassuk a palettában
        if ( empty( $edit_gallery ) && $edit_thumb ) {
            $edit_gallery = [ $edit_thumb ];
        } elseif ( $edit_thumb && ! in_array( $edit_thumb, $edit_gallery, true ) ) {
            array_unshift( $edit_gallery, $edit_thumb );
        }
    }
}

$free_limit = max( 0, absint( get_option( 'va_free_listings_limit', 1 ) ) );
$paid_price = max( 0, absint( get_option( 'va_listing_price_after_free', 1990 ) ) );
$buy_page   = get_page_by_path( 'va-kredit-vasarlas' );
$buy_url    = $buy_page ? get_permalink( $buy_page ) : home_url( '/va-kredit-vasarlas/' );
$buy_url_submit = add_query_arg( 'va_return', 'submit', $buy_url );

$user_listings_count = 0;
$user_credit_balance = 0;
$plan_has_allowance = false;
$plan_remaining = null;
$plan_limit = 0;
$gift_total = 0;
$plan_check = [];
$effective_limit = 0;
if ( is_user_logged_in() ) {
    global $wpdb;
    $user_id = get_current_user_id();
    $user_listings_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = %s
         AND post_author = %d
         AND post_status IN ('publish','pending','draft','future','private')",
        'va_listing',
        $user_id
    ) );

    $user_credit_balance = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );

    if ( class_exists( 'VA_User_Roles' ) ) {
        $plan_check = VA_User_Roles::can_post_listing( $user_id );
        $plan_has_allowance = ! empty( $plan_check['can'] );
        $plan_limit = isset( $plan_check['plan_limit'] ) ? (int) $plan_check['plan_limit'] : (int) ( $plan_check['limit'] ?? 0 );
        $effective_limit = isset( $plan_check['effective_limit'] ) ? (int) $plan_check['effective_limit'] : $plan_limit;
        $gift_total = isset( $plan_check['credits_total'] ) ? (int) $plan_check['credits_total'] : $user_credit_balance;

        if ( isset( $plan_check['effective_limit'], $plan_check['used'] ) && (int) $plan_check['effective_limit'] > 0 ) {
            $plan_remaining = max( 0, (int) $plan_check['effective_limit'] - (int) $plan_check['used'] );
        }
    }
}

$remaining_free = $free_limit === 0 ? 9999 : max( 0, $free_limit - $user_listings_count );

// Ha a user csomagja ismert és el van érve a limit, ne mutassuk az ingyenes keretet
if ( $plan_remaining !== null && ! $plan_has_allowance ) {
    $remaining_free = 0;
}

// ── Azonnali átirányítás ha nincs szabad keret és nem szerkesztés ──
if ( ! $edit_mode && is_user_logged_in() ) {
    $blocked_by_plan_limit = class_exists( 'VA_User_Roles' ) && isset( $plan_check['can'] ) && ! $plan_has_allowance;
    $has_any_allowance = $plan_has_allowance || $user_credit_balance > 0 || $remaining_free > 0;
    if ( $blocked_by_plan_limit || ! $has_any_allowance ) {
        wp_redirect( $buy_url_submit );
        exit;
    }
}

$frontend_css_path = VA_PLUGIN_DIR . 'frontend/css/frontend.css';
$frontend_js_path  = VA_PLUGIN_DIR . 'frontend/js/frontend.js';
$frontend_css_ver  = file_exists( $frontend_css_path ) ? (string) filemtime( $frontend_css_path ) : VA_VERSION;
$frontend_js_ver   = file_exists( $frontend_js_path ) ? (string) filemtime( $frontend_js_path ) : VA_VERSION;

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], $frontend_css_ver );
wp_enqueue_script( 'va-submit',   VA_PLUGIN_URL . 'frontend/js/frontend.js',  [ 'jquery' ], $frontend_js_ver, true );
wp_localize_script( 'va-submit', 'VA_Data', [
    'ajax_url'       => admin_url( 'admin-ajax.php' ),
    'nonce'          => wp_create_nonce( $edit_mode ? 'va_update_listing' : 'va_submit_listing' ),
    'nonce_editor_img' => wp_create_nonce( 'va_upload_editor_image' ),
    'post_id'        => $edit_post_id,
    'edit_mode'      => $edit_mode,
    'edit_images'    => $edit_mode ? array_map( function( $id ) {
        $src = wp_get_attachment_image_url( $id, 'thumbnail' );
        return [ 'id' => $id, 'url' => $src ?: '' ];
    }, $edit_gallery ?? [] ) : [],
    'edit_thumb'     => $edit_mode ? $edit_thumb : 0,
    'nonce_address'  => wp_create_nonce( 'va_address_suggest' ),
    'site_type'      => $site_type,
    'vehicle_brands' => $brands,
    'vehicle_brand_models' => $brand_models,
    'hunting_brand_models' => $hunting_brand_models,
    'hunting_calibers' => $hunting_calibers,
    'learned_terms'  => $learned_terms,
    'category_slugs' => $category_slug_map,
    'category_required_rules' => $category_required_rules,
]);
?>
<?php va_display_flash(); ?>
<div id="va-submit-notice"></div>
<?php
$cat_icons_map = [
    'golyos-puska' => '🎯', 'soretes-puska' => '🦆', 'vegyescsovu-puska' => '⚔️',
    'maroklofegyver' => '🔫', 'hatastalanitott' => '🏛️', 'egyeb-fegyverek' => '⚙️',
    'loszer-tolteny' => '💥', 'tavcsovek' => '🔭', 'ejjellato-tavcso' => '🌙',
    'hokamerak' => '🌡️', 'vadkamera' => '📷', 'vadaszlampa' => '🔦',
    'vadaszkutya' => '🐕', 'vadasz-ruhazat' => '🧥', 'cipo-bakancs' => '👢',
    'kesek' => '🔪',
    'vadasz-felszereles' => '🎒', 'disztargyak' => '🖼️', 'disztargy' => '🖼️', 'egyeb' => '📦',
];
$wiz_steps   = [ 'Kategória', 'Termék', 'Ár & Helyszín', 'Leírás & Képek' ];
$wiz_step_descs = [
    'Mit árulsz pontosan?',
    'Minden fontos adat egy helyen.',
    'Ár, település, telefonszám.',
    'Leírás, képek, végső ellenőrzés.',
];
$max_img     = absint( get_option( 'va_max_images_per_listing', 10 ) );
$desc_val    = wp_kses_post( (string)( $edit_meta['description'] ?? '' ) );
$cond_saved  = (int)( $edit_meta['condition'] ?? 0 );
?>
<style>
.va-submit-preview-shell{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999990;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 10px;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
body.va-page-modal-open{
    overflow: hidden;
}
#va-wizard-overlay.va-wizard-shell{
    overflow-y: auto !important;
    overflow-x: hidden !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-main{
    overflow-y: auto !important;
    overflow-x: hidden !important;
}
.va-submit-page-close{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 14px;
    margin: 0 -34px 0 auto;
    background: #ff8a00 !important;
    border: 1px solid #ff8a00 !important;
    border-radius: 30px;
    color: #111 !important;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 700;
    white-space: nowrap;
    appearance: none;
    -webkit-appearance: none;
    position: static;
    transform: none;
}
.va-submit-page-close::before{
    content: '';
    display: none;
}
.va-submit-page-close:hover{
    background: #ff8a00 !important;
    border-color: #ff8a00 !important;
    filter: brightness(1.1);
}
.va-submit-page-close:active{
    transform: scale(0.95);
}
@media (max-width:1100px){
    .va-submit-page-close{
        margin-right: -20px;
    }
}
@media (max-width:680px){
    .va-submit-page-close{
        margin-right: -22px;
    }
}
.va-submit-preview-shell{padding:0}.va-submit-preview-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:22px;margin-bottom:24px}.va-submit-preview-card,.va-submit-preview-summary,.va-wizard-shell{background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.01));border:1px solid rgba(255,255,255,.08);border-radius:24px;box-shadow:0 28px 70px rgba(0,0,0,.38)}.va-submit-preview-card{position:relative;overflow:hidden;padding:32px}.va-submit-preview-card::after{content:'';position:absolute;right:-60px;bottom:-60px;width:220px;height:220px;background:radial-gradient(circle,rgba(255,138,0,.16),transparent 70%);pointer-events:none}.va-submit-preview-eyebrow{display:inline-flex;align-items:center;padding:8px 12px;margin-bottom:14px;border-radius:999px;border:1px solid rgba(255,138,0,.24);background:rgba(255,138,0,.08);color:#ffd0d0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.16em}.va-submit-preview-card h2{margin:0 0 12px;font-size:clamp(32px,4vw,54px);line-height:.96;text-transform:uppercase;letter-spacing:.03em}.va-submit-preview-card p{margin:0;max-width:680px;color:rgba(255,255,255,.62);font-size:17px;line-height:1.6}.va-submit-preview-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}.va-submit-preview-pills span{padding:10px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:13px;color:#fff}.va-submit-preview-summary{padding:24px;display:flex;flex-direction:column;gap:16px}.va-submit-preview-summary-block{padding:16px;border-radius:18px;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.06)}.va-submit-preview-summary-label{display:block;margin-bottom:8px;color:rgba(255,255,255,.55);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.14em}.va-submit-preview-summary-block strong{display:block;margin-bottom:6px;font-size:18px;color:#fff}.va-submit-preview-summary-block span:last-child{color:rgba(255,255,255,.58);font-size:13px;line-height:1.45}#va-wizard-overlay.va-wizard-shell{display:grid;grid-template-columns:280px minmax(0,1fr);position:static;inset:auto;padding:15px 0 0 0;background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.01))}.va-wizard-sidebar{padding:24px;border-right:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(255,138,0,.10),rgba(255,138,0,.02))}.va-wizard-sidebar-title{margin:0 0 16px;color:rgba(255,255,255,.72);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.16em}.va-wizard-dots--stack{display:flex;flex-direction:column;gap:12px}.va-wdot,.va-cat-card,.va-cond-btn{-webkit-appearance:none;appearance:none}.va-wizard-dots--stack .va-wdot{width:100%;display:flex;align-items:center;gap:14px;padding:14px;border:1px solid rgba(255,255,255,.08);border-radius:18px;background:rgba(255,255,255,.03);opacity:1;text-align:left;cursor:pointer;transition:.2s ease}.va-wizard-dots--stack .va-wdot:hover{border-color:rgba(255,138,0,.4);transform:translateX(4px)}.va-wizard-dots--stack .va-wdot.is-active{background:rgba(255,138,0,.18);border-color:rgba(255,138,0,.55)}.va-wizard-dots--stack .va-wdot.is-done{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.14)}.va-wizard-dots--stack .va-wdot__circle{flex:0 0 40px;width:40px;height:40px}.va-wizard-dots--stack .va-wdot__copy{display:flex;flex-direction:column;gap:4px}.va-wizard-dots--stack .va-wdot__title{font-size:14px;font-weight:700;color:#fff}.va-wizard-dots--stack .va-wdot__desc{font-size:12px;line-height:1.35;color:rgba(255,255,255,.56)}.va-wizard-main{padding:28px;background:rgba(0,0,0,.18)}.va-wizard-header--inline{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding:0;margin-bottom:18px;background:transparent;border-bottom:none}.va-wizard-head-copy{margin:8px 0 0;color:rgba(255,255,255,.58);font-size:15px}.va-wizard-progress-meta{width:min(280px,100%);align-self:flex-start}.va-wizard-progress-meta .va-wiz-foot-label{display:block;margin-bottom:10px;text-align:right}.va-wizard-progress-meta .va-wizard-prog-wrap{margin:0;height:10px;border-radius:999px;overflow:hidden}.va-wizard-progress-meta .va-wizard-prog-fill{border-radius:inherit;background:linear-gradient(90deg,#ff8a00,#ffaa40)}.va-wiz-plan-notice{margin-bottom:18px;padding:14px 16px;border-radius:16px;border-color:rgba(56,211,159,.20);background:rgba(56,211,159,.08)}#va-wizard-overlay.va-wizard-shell .va-wizard-body{padding:0;overflow:visible;flex:none}#va-wizard-overlay.va-wizard-shell .va-wstep-title{font-size:30px;margin-bottom:10px}#va-wizard-overlay.va-wizard-shell .va-wstep-title em{margin-top:8px;font-size:15px;color:rgba(255,255,255,.56)}#va-wizard-overlay.va-wizard-shell .va-wstep{display:none}#va-wizard-overlay.va-wizard-shell .va-wstep.is-active{display:block}#va-wizard-overlay.va-wizard-shell .va-cat-cards{grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}#va-wizard-overlay.va-wizard-shell .va-cat-card{justify-content:space-between;min-height:118px;padding:18px 14px;text-align:left;align-items:flex-start}#va-wizard-overlay.va-wizard-shell .va-cat-card__icon{font-size:28px}#va-wizard-overlay.va-wizard-shell .va-cat-card__label{font-size:13px}#va-wizard-overlay.va-wizard-shell .va-cond-btn{border-radius:999px;padding:11px 14px}#va-wizard-overlay.va-wizard-shell .va-wizard-footer{
    position: relative;
    margin-top: 20px;
    padding: 18px 0 0;
    border-top: 1px solid rgba(255,255,255,.08);
    background: transparent;
    justify-content: flex-end !important;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}#va-wizard-overlay.va-wizard-shell .va-wstep-title,#va-wizard-overlay.va-wizard-shell .va-wstep-title em{color:#fff!important}@media (max-width:980px){.va-submit-preview-hero,#va-wizard-overlay.va-wizard-shell{grid-template-columns:1fr}.va-wizard-sidebar{border-right:none;border-bottom:1px solid rgba(255,255,255,.08)}.va-wizard-dots--stack{overflow-x:auto;flex-direction:row;padding-bottom:4px}.va-wizard-dots--stack .va-wdot{min-width:240px}#va-wizard-overlay.va-wizard-shell .va-cat-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:680px){.va-submit-preview-card,.va-submit-preview-summary,.va-wizard-main,.va-wizard-sidebar{padding:18px}.va-submit-preview-card h2,#va-wizard-overlay.va-wizard-shell .va-wstep-title{font-size:24px}.va-wizard-header--inline{flex-direction:column;align-items:stretch}.va-wizard-progress-meta{width:100%}.va-wizard-progress-meta .va-wiz-foot-label{text-align:left}#va-wizard-overlay.va-wizard-shell .va-cat-cards{grid-template-columns:1fr}#va-wizard-overlay.va-wizard-shell .va-wizard-footer{flex-wrap:wrap}}
</style>
<style>
/* hard override against global button styles */
#va-wizard-overlay.va-wizard-shell .va-cat-cards{display:grid!important;grid-template-columns:repeat(6,minmax(120px,1fr))!important;gap:12px!important}
#va-wizard-overlay.va-wizard-shell .va-cat-card{display:flex!important;flex-direction:column!important;justify-content:space-between!important;align-items:flex-start!important;width:100%!important;min-height:112px!important;padding:14px!important;margin:0!important;background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.16)!important;border-radius:14px!important;color:#fff!important;line-height:1.25!important;white-space:normal!important}
#va-wizard-overlay.va-wizard-shell .va-cat-card:hover{border-color:rgba(255,138,0,.55)!important;background:rgba(255,138,0,.14)!important}
#va-wizard-overlay.va-wizard-shell .va-cat-card__icon{font-size:26px!important;line-height:1!important}
#va-wizard-overlay.va-wizard-shell .va-cat-card__label{display:block!important;font-size:13px!important;font-weight:700!important;color:#fff!important;text-transform:none!important}
#va-wizard-overlay.va-wizard-shell .va-cond-btns{display:flex!important;flex-wrap:wrap!important;gap:10px!important}
#va-wizard-overlay.va-wizard-shell .va-cond-btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;padding:10px 16px!important;margin:0!important;background:rgba(255,255,255,.06)!important;border:1px solid rgba(255,255,255,.20)!important;border-radius:999px!important;color:#fff!important;white-space:nowrap!important}
#va-wizard-overlay.va-wizard-shell{position:relative!important;width:min(1440px,calc(100vw - 20px))!important;max-height:min(92vh,960px)!important;overflow:hidden!important;border:1.5px solid #ff8a00!important;box-shadow:0 30px 80px rgba(0,0,0,.65),inset 0 1px 0 rgba(255,255,255,.06)!important}
#va-wizard-overlay.va-wizard-shell{margin:0 auto!important}
#va-wizard-overlay.va-wizard-shell .va-wizard-main{display:flex;flex-direction:column;overflow:hidden;padding-top:0!important}
#va-wizard-overlay.va-wizard-shell .va-wizard-body{flex:none;overflow:visible!important;padding-right:4px}
#va-wizard-overlay.va-wizard-shell .va-cat-cards{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:12px!important}
@media (max-width:1200px){#va-wizard-overlay.va-wizard-shell .va-cat-cards{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
@media (max-width:760px){#va-wizard-overlay.va-wizard-shell .va-cat-cards{grid-template-columns:1fr!important}}
.va-loc-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:16px!important}
.va-loc-grid .va-input{width:100%!important}
.va-loc-grid small{grid-column:1/-1!important;margin-top:-8px!important;color:rgba(255,255,255,.55)!important;font-size:12px!important}
@media (max-width:1200px){.va-loc-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
@media (max-width:760px){.va-loc-grid{grid-template-columns:1fr!important}}
@media (max-width:760px){.va-submit-preview-shell{padding:8px}#va-wizard-overlay.va-wizard-shell{width:calc(100vw - 16px)!important;max-height:calc(100vh - 16px)!important}}
@media (max-width:1100px){#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar{padding:16px}#va-wizard-overlay.va-wizard-shell .va-wizard-main{padding:16px}}
.va-submit-preview-shell{align-items:center!important;overflow:hidden!important}
#va-wizard-overlay.va-wizard-shell{overflow:hidden!important;overflow-x:hidden!important}
#va-wizard-overlay.va-wizard-shell .va-wizard-main{overflow-y:auto!important;overflow-x:hidden!important;max-height:calc(min(92vh,960px) - 30px)!important}
#va-wizard-overlay.va-wizard-shell .va-wizard-body{overflow:visible!important}
#va-wizard-overlay.va-wizard-shell{position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;margin:0!important}
</style>
<style>
/* Step 1 category list hotfix (current markup: .va-cat-list > li > .va-cat-item) */
#va-wizard-overlay.va-wizard-shell .va-cat-list {
    list-style: none !important;
    margin: 0 0 14px !important;
    padding: 0 !important;
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 8px !important;
}
#va-wizard-overlay.va-wizard-shell .va-cat-list > li {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
#va-wizard-overlay.va-wizard-shell .va-cat-item {
    width: 100% !important;
    min-height: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    text-align: left !important;
    padding: 8px 10px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255,255,255,.16) !important;
    background: rgba(255,255,255,.04) !important;
    color: #fff !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    line-height: 1.15 !important;
    white-space: normal !important;
}
#va-wizard-overlay.va-wizard-shell .va-cat-item:hover {
    border-color: #ff8a00 !important;
    background: #ff8a00 !important;
    color: #111 !important;
}
#va-wizard-overlay.va-wizard-shell .va-cat-item[data-selected="1"] {
    border-color: #ff8a00 !important;
    background: #ff8a00 !important;
    color: #111 !important;
    box-shadow: none !important;
}
@media (max-width:1200px){
    #va-wizard-overlay.va-wizard-shell .va-cat-list { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
}
@media (max-width:760px){
    #va-wizard-overlay.va-wizard-shell .va-cat-list { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar .va-wiz-plan-notice {
    margin-top: 14px;
    margin-bottom: 0;
    border: 1px solid #2b9a0a !important;
    background: #207E01 !important;
    box-shadow: 0 0 10px rgba(32, 126, 1, 0.28), 0 10px 20px rgba(8, 34, 2, 0.38), inset 0 1px 0 rgba(170, 255, 140, 0.18) !important;
    color: #efffe7 !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar .va-wiz-plan-notice,
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar .va-wiz-plan-notice p,
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar .va-wiz-plan-notice span {
    color: #efffe7 !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar .va-wiz-plan-notice strong {
    color: #ffffff !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar .va-wiz-plan-notice a {
    color: #ffffff !important;
    text-decoration-color: rgba(255, 255, 255, 0.55) !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot__circle,
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot__circle span {
    color: #fff !important;
}
#va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-form-group.va-title-group {
    margin-top: 15px !important;
}
/* Step 2: kompakt 4-oszlopos grid minden kategóriában */
#va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-form-row {
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 14px !important;
}
/* Inner grid wrapper — JS sosem toggolja, biztonságos display:grid !important */
.va-step2-4col-inner {
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}
.va-telescope-type-wrap { grid-column: 1 / -1; }
.va-dog-4col .va-dog-row {
    display: contents;
}
#va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-specs-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 14px !important;
}
@media (max-width: 980px) {
    #va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-form-row,
    .va-step2-4col-inner,
    #va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-specs-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}
@media (max-width: 600px) {
    #va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-form-row,
    .va-step2-4col-inner,
    #va-wizard-overlay.va-wizard-shell .va-wstep[data-step="2"] .va-specs-grid {
        grid-template-columns: 1fr !important;
    }
}
#va-wizard-overlay.va-wizard-shell .va-cond-btn.is-selected {
    background: #ff8a00 !important;
    border-color: #ff8a00 !important;
    color: #111 !important;
}
#va-wizard-overlay.va-wizard-shell .va-cond-btn:hover,
#va-wizard-overlay.va-wizard-shell .va-cond-btn:focus,
#va-wizard-overlay.va-wizard-shell .va-cond-btn:active {
    background: #ff8a00 !important;
    border-color: #ff8a00 !important;
    color: #111 !important;
}
#va-wizard-overlay.va-wizard-shell .va-wstep-title,
#va-wizard-overlay.va-wizard-shell .va-wstep-title em {
    font-size: 30px !important;
    font-style: normal !important;
    font-weight: 800 !important;
    color: #fff !important;
    line-height: 1.1 !important;
    margin: 0 0 10px !important;
}
#va-step2-category-label {
    display: inline-block;
}
#va-step2-category-label:not(:empty) {
    margin-left: 10px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255,138,0,.55);
    background: rgba(255,138,0,.14);
    color: #fff !important;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    vertical-align: middle;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-footer {
    display: flex !important;
    justify-content: flex-end !important;
    align-self: stretch !important;
    width: 100% !important;
    flex-wrap: nowrap !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-footer .va-btn {
    background: #ff8a00 !important;
    border-color: #ff8a00 !important;
    color: #111 !important;
    border-radius: 30px;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-footer .va-btn:hover,
#va-wizard-overlay.va-wizard-shell .va-wizard-footer .va-btn:focus,
#va-wizard-overlay.va-wizard-shell .va-wizard-footer .va-btn:active {
    background: #ff8a00 !important;
    border-color: #ff8a00 !important;
    color: #111 !important;
    filter: brightness(1.03);
}

/* Final visual overrides requested */
#va-wizard-overlay.va-wizard-shell::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 280px;
    height: 15px;
    background: #1F1305;
    pointer-events: none;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar {
    background: #1F1305 !important;
    position: relative;
    z-index: 1;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-sidebar::before {
    content: '';
    position: absolute;
    top: -15px;
    right: -1px;
    width: 1px;
    height: 15px;
    background: rgba(255,255,255,.08);
    pointer-events: none;
}
#va-wizard-overlay.va-wizard-shell .va-submit-page-close {
    margin-left: auto !important;
    margin-right: 0 !important;
    flex: 0 0 auto !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-main {
    position: relative;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-close-top {
    display: flex;
    justify-content: flex-end;
    margin: 0 0 12px;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-close-top .va-submit-page-close {
    margin: 0 !important;
}
#va-wizard-overlay.va-wizard-shell .va-cond-group .va-wiz-field-label {
    display: block !important;
    color: rgba(255,255,255,.72) !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .14em !important;
    margin-bottom: 10px !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot.is-active {
    background: #ff8a00 !important;
    border-color: #ff8a00 !important;
    padding: 12px 14px !important;
    box-sizing: border-box !important;
}
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot.is-active .va-wdot__title,
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot.is-active .va-wdot__desc,
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot.is-active .va-wdot__circle,
#va-wizard-overlay.va-wizard-shell .va-wizard-dots--stack .va-wdot.is-active .va-wdot__circle span {
    color: #111 !important;
}

/* Elegant dark datalist replacement */
.va-datalist-panel {
    position: fixed;
    z-index: 2147483646;
    display: none;
    width: 320px;
    max-height: 260px;
    overflow: auto;
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(14,14,14,.98), rgba(8,8,8,.98));
    box-shadow: 0 18px 40px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.05);
}
.va-datalist-panel::-webkit-scrollbar {
    width: 10px;
}
.va-datalist-panel::-webkit-scrollbar-track {
    background: rgba(255,255,255,.05);
    border-radius: 999px;
}
.va-datalist-panel::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(255,138,0,.8), rgba(255,170,0,.8));
    border-radius: 999px;
}
.va-datalist-item {
    display: block;
    width: 100%;
    padding: 10px 12px;
    border: 0;
    background: transparent;
    color: #fff;
    text-align: left;
    cursor: pointer;
    font-size: 14px;
    line-height: 1.35;
}
.va-datalist-item:hover,
.va-datalist-item.is-active {
    background: rgba(255,138,0,.24);
}
.va-submit-preview-shell ::selection {
    background: #ff8a00;
    color: #111;
}
.va-submit-preview-shell ::-moz-selection {
    background: #ff8a00;
    color: #111;
}

.va-year-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.va-year-input-wrap .va-input {
    flex: 1;
}
.va-year-open-btn {
    border: 1px solid rgba(255,138,0,.65);
    background: linear-gradient(180deg,#111,#080808);
    color: #fff;
    border-radius: 12px;
    padding: 10px 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s ease;
    white-space: nowrap;
}
.va-year-open-btn:hover {
    border-color: #ff8a00;
    background: #141414;
    box-shadow: 0 0 16px rgba(255,138,0,.22);
}
.va-year-modal {
    position: fixed;
    inset: 0;
    z-index: 100001;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(0,0,0,.76);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.va-year-modal.is-open {
    display: flex;
}
.va-year-card {
    width: min(560px, calc(100vw - 24px));
    max-width: 100%;
    border-radius: 20px;
    border: none;
    background: linear-gradient(160deg, #0f0f0f, #050505);
    box-shadow: 0 30px 80px rgba(0,0,0,.75), inset 0 1px 0 rgba(255,255,255,.08);
    padding: 18px;
}
.va-year-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
}
.va-year-head h4 {
    margin: 0;
    color: #fff;
    font-size: 20px;
    font-weight: 800;
}
.va-year-nav {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-weight: 700;
}
.va-year-nav-btn {
    border: 1px solid rgba(255,255,255,.2);
    background: #121212;
    color: #fff;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    cursor: pointer;
}
.va-year-nav-btn:hover {
    border-color: #ff8a00;
    background: #1a1a1a;
}
.va-year-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
}
.va-year-item {
    border: 1px solid rgba(255,255,255,.18);
    background: #0f0f0f;
    color: #fff;
    border-radius: 12px;
    padding: 10px 8px;
    font-weight: 700;
    cursor: pointer;
    transition: .18s ease;
}
.va-year-item:hover {
    border-color: #ff8a00;
    transform: translateY(-1px);
}
.va-year-item.is-selected {
    background: #ff8a00;
    border-color: #ff8a00;
    color: #111;
}
.va-year-actions {
    flex-shrink: 0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
}
.va-popup-select-native {
    display: none !important;
}
.va-popup-select-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 48px;
    border: 1px solid rgba(255,255,255,.16);
    background: linear-gradient(180deg, rgba(18,18,18,.98), rgba(8,8,8,.98));
    color: #fff;
    border-radius: 12px;
    padding: 12px 14px;
    cursor: pointer;
    text-align: left;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.va-popup-select-trigger:hover {
    border-color: #ff8a00;
    box-shadow: 0 0 18px rgba(255,138,0,.16);
}
.va-popup-select-trigger:focus-visible {
    outline: none;
    border-color: #ff8a00;
    box-shadow: 0 0 0 3px rgba(255,138,0,.18);
}
.va-popup-select-trigger.is-placeholder .va-popup-select-trigger__value {
    color: rgba(255,255,255,.52);
}
.va-popup-select-trigger[disabled] {
    opacity: .55;
    cursor: not-allowed;
    box-shadow: none;
}
.va-popup-select-trigger__value {
    flex: 1;
    min-width: 0;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
}
.va-popup-select-trigger__icon {
    flex-shrink: 0;
    font-size: 14px;
    opacity: .85;
}
.va-popup-select-card {
    width: min(620px, calc(100vw - 24px));
    max-width: 100%;
    max-height: min(calc(100vh - 32px), 760px);
    display: flex;
    flex-direction: column;
    border-radius: 20px;
    border: 1px solid rgba(255,138,0,.75);
    background: linear-gradient(160deg, #0f0f0f, #050505);
    box-shadow: 0 30px 80px rgba(0,0,0,.75), inset 0 1px 0 rgba(255,255,255,.08);
    padding: 18px;
}
.va-popup-select-head {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}
.va-popup-select-head h4 {
    margin: 0;
    color: #fff;
    font-size: 20px;
    font-weight: 800;
}
.va-popup-select-search {
    flex-shrink: 0;
    width: 100%;
    margin-bottom: 12px;
}
.va-popup-select-list {
    flex: 1;
    min-height: 0;
    overflow: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-right: 4px;
}
    width: 10px;
}
.va-popup-select-list::-webkit-scrollbar-track {
    background: rgba(255,255,255,.05);
    border-radius: 999px;
}
.va-popup-select-list::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(255,138,0,.8), rgba(255,170,0,.8));
    border-radius: 999px;
}
.va-popup-select-item,
.va-popup-select-empty {
    width: 100%;
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    color: #fff;
    padding: 12px 14px;
    text-align: left;
    font-size: 14px;
    line-height: 1.35;
}
.va-popup-select-item {
    cursor: pointer;
    transition: border-color .18s ease, background .18s ease, transform .18s ease;
}
.va-popup-select-item:hover {
    border-color: #ff8a00;
    background: rgba(255,138,0,.12);
    transform: translateY(-1px);
}
.va-popup-select-item.is-selected {
    border-color: #ff8a00;
    background: rgba(255,138,0,.18);
    box-shadow: inset 0 0 0 1px rgba(255,138,0,.2);
}
.va-popup-select-item__label {
    display: block;
}
.va-popup-select-item__meta {
    display: block;
    margin-top: 4px;
    color: rgba(255,255,255,.58);
    font-size: 12px;
}
.va-popup-select-empty {
    color: rgba(255,255,255,.62);
}
.va-shoe-size-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
}
.va-shoe-size-item {
    border: 1px solid rgba(255,255,255,.18);
    background: #0f0f0f;
    color: #fff;
    border-radius: 12px;
    padding: 10px 8px;
    font-weight: 700;
    cursor: pointer;
    transition: .18s ease;
}
.va-shoe-size-item:hover {
    border-color: #ff8a00;
    transform: translateY(-1px);
}
.va-shoe-size-item.is-selected {
    background: #ff8a00;
    border-color: #ff8a00;
    color: #111;
}
@media (max-width:640px) {
    .va-year-input-wrap {
        flex-direction: column;
        align-items: stretch;
    }
    .va-year-modal {
        align-items: flex-start;
        padding: 10px;
    }
    .va-year-card,
    .va-popup-select-card {
        width: 100%;
        max-height: calc(100vh - 20px);
        border-radius: 16px;
        padding: 14px;
    }
    .va-popup-select-head {
        gap: 10px;
        margin-bottom: 10px;
    }
    .va-popup-select-head h4 {
        font-size: 18px;
        line-height: 1.2;
    }
    .va-popup-select-search {
        margin-bottom: 10px;
    }
    .va-popup-select-list {
        gap: 6px;
        padding-right: 2px;
    }
    .va-popup-select-item,
    .va-popup-select-empty {
        padding: 11px 12px;
        font-size: 13px;
    }
    .va-year-actions {
        margin-top: 10px;
    }
    .va-year-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .va-shoe-size-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

/* Egyeb kategoria popup */
.va-other-cat-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: none;
    overflow: hidden;
}
body.va-modal-open {
    overflow: hidden;
}
.va-other-cat-modal.is-open {
    display: flex !important;
    animation: va-modal-fadein 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes va-modal-fadein {
    0% { opacity: 0; transform: scale(0.92); }
    100% { opacity: 1; transform: scale(1); }
}
.va-other-cat-card {
    position: relative;
    z-index: 100000;
    width: 100%;
    max-width: 500px;
    min-width: 280px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,.2);
    background: linear-gradient(135deg, #1a1a1a, #0f0f0f);
    box-shadow: 0 30px 80px rgba(0,0,0,.7), inset 0 1px 0 rgba(255,255,255,.1);
    padding: 28px 24px;
    animation: va-card-slide 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes va-card-slide {
    0% { transform: translateY(-30px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}
.va-other-cat-card h4 {
    margin: 0 0 8px;
    color: #fff;
    font-size: 20px;
    font-weight: 800;
}
.va-other-cat-card p {
    margin: 0 0 12px;
    color: rgba(255,255,255,.7);
}
.va-other-cat-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 12px;
}

/* Submit Notice Modal - Teljes overlay */
#va-submit-notice-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999999;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: none;
    overflow: hidden;
}
#va-submit-notice-modal.is-open {
    display: flex !important;
    animation: va-modal-fadein 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.va-submit-notice-card {
    position: relative;
    z-index: 100000;
    width: 100%;
    max-width: 550px;
    min-width: 280px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,.2);
    background: linear-gradient(135deg, #1a1a1a, #0f0f0f);
    box-shadow: 0 30px 80px rgba(0,0,0,.7), inset 0 1px 0 rgba(255,255,255,.1);
    padding: 28px 24px;
    animation: va-card-slide 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    max-height: 85vh;
    overflow-y: auto;
}
</style>

<div class="va-submit-preview-shell">
    <div class="va-wizard-shell" id="va-wizard-overlay">

        <aside class="va-wizard-sidebar">
            <p class="va-wizard-sidebar-title">Lépések</p>
            <div class="va-wizard-dots va-wizard-dots--stack">
                <?php foreach ( $wiz_steps as $si => $slabel ): $sn = $si + 1; ?>
                <button type="button" class="va-wdot<?php echo $sn === 1 ? ' is-active' : ''; ?>" data-step="<?php echo $sn; ?>">
                    <span class="va-wdot__circle"><span><?php echo $sn; ?></span></span>
                    <span class="va-wdot__copy">
                        <span class="va-wdot__title"><?php echo esc_html( $slabel ); ?></span>
                        <span class="va-wdot__desc"><?php echo esc_html( $wiz_step_descs[ $si ] ); ?></span>
                    </span>
                </button>
                <?php endforeach; ?>
            </div>

            <?php if ( ! $edit_mode && ( $plan_has_allowance || $user_credit_balance > 0 || $remaining_free > 0 ) ): ?>
            <div class="va-notice va-notice--info va-wiz-plan-notice">
                <?php if ( $plan_has_allowance || $user_credit_balance > 0 ): ?>
                    <?php if ( is_int( $plan_remaining ) && $plan_remaining > 0 ): ?>
                        Keretedből még <strong><?php echo esc_html( (string) $plan_remaining ); ?> db</strong> hirdetést adhatsz fel.
                    <?php elseif ( $gift_total > 0 ): ?>
                        Alap: <strong><?php echo esc_html( (string) $plan_limit ); ?> db</strong>, Ajándék: <strong><?php echo esc_html( (string) $gift_total ); ?> db</strong>.
                    <?php else: ?>
                        Az előfizetésed alapján tudsz hirdetést feladni.
                    <?php endif; ?>
                <?php elseif ( $remaining_free === 1 ): ?>
                    Ez az <strong>utolsó ingyenes</strong> hirdetésed. Utána díja: <strong><?php echo esc_html( number_format( $paid_price, 0, ',', ' ' ) ); ?> Ft</strong> — <a href="<?php echo esc_url( $buy_url ); ?>" style="color:#ff6060;font-weight:700;">csomagok</a>
                <?php else: ?>
                    Még <strong><?php echo esc_html( (string) $remaining_free ); ?> db</strong> ingyenes hirdetésed van.
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </aside>

        <div class="va-wizard-main">

            <div class="va-wizard-close-top">
                <button type="button" class="va-submit-page-close" id="va-submit-page-close" aria-label="Bezárás">Bezárás</button>
            </div>

            <div class="va-wizard-body">
    <form id="va-submit-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'va_update_listing' : 'va_submit_listing'; ?>">
        <input type="hidden" name="nonce"  value="<?php echo esc_attr( wp_create_nonce( $edit_mode ? 'va_update_listing' : 'va_submit_listing' ) ); ?>">
        <?php if ( $edit_mode ): ?><input type="hidden" name="post_id" value="<?php echo esc_attr( (string) $edit_post_id ); ?>"><?php endif; ?>

        <?php $fb_form = 'va_listing_submit'; ?>

        <!-- ═══ STEP 1: Kategória + Állapot ═══ -->
        <div class="va-wstep is-active" data-step="1">
            <h3 class="va-wstep-title">Hirdetés feladás, válassz kategóriát</h3>
            <ul class="va-cat-list" id="va-cat-list">
                <?php foreach ( $categories as $cat ): $cat_label = str_ireplace( [ 'Puska', 'puska' ], [ 'Lőfegyver', 'lőfegyver' ], (string) $cat->name ); if ( (string)($cat->slug ?? '') === 'hatastalanitott' ) { $cat_label = 'Hatástalanított lőfegyver'; } ?>
                <li><button type="button" class="va-cat-item" data-term-id="<?php echo esc_attr($cat->term_id); ?>" data-slug="<?php echo esc_attr((string)$cat->slug); ?>" onclick="(function(btn){var items=document.querySelectorAll('.va-cat-item');for(var i=0;i<items.length;i++){items[i].removeAttribute('data-selected');}btn.setAttribute('data-selected','1');var hidden=document.getElementById('va-category');if(hidden){hidden.value=btn.getAttribute('data-term-id')||'';if(window.jQuery){window.jQuery(hidden).trigger('change');}else{hidden.dispatchEvent(new Event('change',{bubbles:true}));}}var slug=(btn.getAttribute('data-slug')||'').toLowerCase();if(slug!=='egyeb'){var other=document.getElementById('va-other-category');if(other){other.value='';}}})(this);"<?php echo ((int)($edit_meta['category']??0) === $cat->term_id) ? ' data-selected="1"' : ''; ?>><?php echo esc_html($cat_label); ?></button></li>
                <?php endforeach; ?>
            </ul>
            <input type="hidden" name="category" id="va-category" value="<?php echo esc_attr((string)($edit_meta['category'] ?? '')); ?>" required>
            <input type="hidden" name="other_category" id="va-other-category" value="<?php echo esc_attr((string)($edit_meta['other_category'] ?? '')); ?>">
            <div class="va-cond-group">
                <label class="va-wiz-field-label">Termék állapotának kiválasztása</label>
                <div class="va-cond-btns" id="va-cond-btns">
                    <?php foreach ( $conditions as $cond ):
                        $is_cs = $cond_saved && $cond_saved === $cond->term_id;
                    ?>
                    <button type="button" class="va-cond-btn<?php echo $is_cs ? ' is-selected' : ''; ?>" data-term-id="<?php echo esc_attr($cond->term_id); ?>"><?php echo esc_html($cond->name); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="condition" id="va-condition-hidden" value="<?php echo esc_attr((string)$cond_saved); ?>">
            </div>
        </div>

        <!-- ═══ STEP 2: Termék adatai ═══ -->
        <div class="va-wstep" data-step="2">
            <h3 class="va-wstep-title">Termék adatai <span id="va-step2-category-label"></span></h3>
            <div class="va-form-group va-title-group">
                <label>Hirdetés címe <span class="required">*</span></label>
                <input type="text" name="title" id="va-title" class="va-input" list="va-title-list" autocomplete="off" maxlength="150" required placeholder="Rövid, figyelemfelkeltő cím..." value="<?php echo esc_attr((string)($edit_meta['title'] ?? '')); ?>">
                <datalist id="va-title-list"></datalist>
            </div>
            <div class="va-form-row va-core-product-fields">
                <div class="va-form-group">
                    <label>Márka / gyártó</label>
                    <?php self_render_listing_field( 'brand', 'pl. Blaser, Swarovski...', '', $categories, $counties, $conditions, $brands, $body_types, $brand_models, $site_type, $edit_meta ); ?>
                </div>
                <div class="va-form-group">
                    <label>Modell / típus</label>
                    <?php self_render_listing_field( 'model', 'pl. R8, Z6...', '', $categories, $counties, $conditions, $brands, $body_types, $brand_models, $site_type, $edit_meta ); ?>
                </div>
            </div>
            <div class="va-cat-rule-field va-exchange-fields-grid" data-categories="csere" style="display:none;">
                <?php
                $exchange_saved = static function ( string $key, string $default = '' ) use ( $edit_meta ): string {
                    return (string) ( $edit_meta[ $key ] ?? $default );
                };
                $exchange_saved_csv = static function ( string $key ) use ( $edit_meta ): array {
                    $raw = (string) ( $edit_meta[ $key ] ?? '' );
                    if ( $raw === '' ) {
                        return [];
                    }
                    return array_filter( array_map( 'trim', explode( ',', $raw ) ) );
                };
                $exchange_wanted_categories_saved = $exchange_saved_csv( 'exchange_wanted_categories' );
                ?>
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Csere típusa</label>
                        <?php $v = $exchange_saved( 'exchange_type' ); ?>
                        <select name="exchange_type" class="va-select" id="va-exchange-type">
                            <option value="">- Válasszon -</option>
                            <option value="termek-csere"<?php selected( $v, 'termek-csere' ); ?>>Termék csere</option>
                            <option value="rafizeteses-csere"<?php selected( $v, 'rafizeteses-csere' ); ?>>Ráfizetéses csere</option>
                            <option value="ertekegyezteteses-csere"<?php selected( $v, 'ertekegyezteteses-csere' ); ?>>Értékegyeztetéses csere</option>
                            <option value="szolgaltatas-csere"<?php selected( $v, 'szolgaltatas-csere' ); ?>>Szolgáltatás csere</option>
                            <option value="gyujtoi-csere"<?php selected( $v, 'gyujtoi-csere' ); ?>>Gyűjtői csere</option>
                            <option value="tobb-termek-egyre"<?php selected( $v, 'tobb-termek-egyre' ); ?>>Több termék egyre</option>
                            <option value="egy-termek-tobbre"<?php selected( $v, 'egy-termek-tobbre' ); ?>>Egy termék többre</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Fő kategória (mit kínál?)</label>
                        <?php $v = $exchange_saved( 'exchange_offer_category' ); ?>
                        <select name="exchange_offer_category" class="va-select" id="va-exchange-offer-category">
                            <option value="">- Válasszon -</option>
                            <option value="fegyver"<?php selected( $v, 'fegyver' ); ?>>Fegyver</option>
                            <option value="optika"<?php selected( $v, 'optika' ); ?>>Optika</option>
                            <option value="hokamera"<?php selected( $v, 'hokamera' ); ?>>Hőkamera</option>
                            <option value="kes"<?php selected( $v, 'kes' ); ?>>Kés</option>
                            <option value="ruhazat"<?php selected( $v, 'ruhazat' ); ?>>Ruházat</option>
                            <option value="elektronika"<?php selected( $v, 'elektronika' ); ?>>Elektronika</option>
                            <option value="vadaszati-felszereles"<?php selected( $v, 'vadaszati-felszereles' ); ?>>Vadászati felszerelés</option>
                            <option value="trofea"<?php selected( $v, 'trofea' ); ?>>Trófea</option>
                            <option value="jarmu"<?php selected( $v, 'jarmu' ); ?>>Jármű</option>
                            <option value="szolgaltatas"<?php selected( $v, 'szolgaltatas' ); ?>>Szolgáltatás</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Termék neve</label>
                        <input type="text" name="exchange_item_name" class="va-input" placeholder="pl. Glock 17 Gen4" value="<?php echo esc_attr( $exchange_saved( 'exchange_item_name' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Márka</label>
                        <input type="text" name="exchange_brand" class="va-input" placeholder="pl. Glock" value="<?php echo esc_attr( $exchange_saved( 'exchange_brand' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Modell</label>
                        <input type="text" name="exchange_model" class="va-input" placeholder="pl. 17" value="<?php echo esc_attr( $exchange_saved( 'exchange_model' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Állapot</label>
                        <?php $v = $exchange_saved( 'exchange_condition' ); ?>
                        <select name="exchange_condition" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option>
                            <option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option>
                            <option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option>
                            <option value="hibas"<?php selected( $v, 'hibas' ); ?>>Hibás</option>
                            <option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Becsült érték (Ft)</label>
                        <input type="text" name="exchange_estimated_value" class="va-input" inputmode="numeric" placeholder="pl. 450000" value="<?php echo esc_attr( $exchange_saved( 'exchange_estimated_value' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Ráfizetés</label>
                        <?php $v = $exchange_saved( 'exchange_extra_payment_direction' ); ?>
                        <select name="exchange_extra_payment_direction" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="kerek-ra"<?php selected( $v, 'kerek-ra' ); ?>>Kérek rá</option>
                            <option value="adok-ra"<?php selected( $v, 'adok-ra' ); ?>>Adok rá</option>
                            <option value="nem-szukseges"<?php selected( $v, 'nem-szukseges' ); ?>>Nem szükséges</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Ráfizetés összege (Ft)</label>
                        <input type="text" name="exchange_extra_payment_amount" class="va-input" inputmode="numeric" placeholder="pl. 100000" value="<?php echo esc_attr( $exchange_saved( 'exchange_extra_payment_amount' ) ); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Keresett kategória</label>
                        <select name="exchange_wanted_categories[]" class="va-select" multiple data-placeholder="Mit keres cserébe?">
                            <option value="fegyver"<?php echo in_array( 'fegyver', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Fegyver</option>
                            <option value="celtavcso"<?php echo in_array( 'celtavcso', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Céltávcső</option>
                            <option value="hokamera"<?php echo in_array( 'hokamera', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Hőkamera</option>
                            <option value="kes"<?php echo in_array( 'kes', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Kés</option>
                            <option value="bakancs"<?php echo in_array( 'bakancs', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Bakancs</option>
                            <option value="ruhazat"<?php echo in_array( 'ruhazat', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Ruházat</option>
                            <option value="trofea"<?php echo in_array( 'trofea', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Trófea</option>
                            <option value="elektronika"<?php echo in_array( 'elektronika', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Elektronika</option>
                            <option value="szolgaltatas"<?php echo in_array( 'szolgaltatas', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Szolgáltatás</option>
                            <option value="barmi-erdekel"<?php echo in_array( 'barmi-erdekel', $exchange_wanted_categories_saved, true ) ? ' selected' : ''; ?>>Bármi érdekel</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Konkrét keresés</label>
                        <input type="text" name="exchange_target" class="va-input" placeholder="pl. Glock 17, Swarovski Z8i, Pulsar" value="<?php echo esc_attr( $exchange_saved( 'exchange_target' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Ország</label>
                        <input type="text" name="exchange_country" class="va-input" placeholder="pl. Magyarország" value="<?php echo esc_attr( $exchange_saved( 'exchange_country' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Megye</label>
                        <input type="text" name="exchange_county" class="va-input" placeholder="pl. Pest" value="<?php echo esc_attr( $exchange_saved( 'exchange_county' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Település</label>
                        <input type="text" name="exchange_city" class="va-input" placeholder="pl. Gödöllő" value="<?php echo esc_attr( $exchange_saved( 'exchange_city' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Csere módja</label>
                        <?php $v = $exchange_saved( 'exchange_method' ); ?>
                        <select name="exchange_method" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="szemelyes"<?php selected( $v, 'szemelyes' ); ?>>Személyes</option>
                            <option value="futar"<?php selected( $v, 'futar' ); ?>>Futár</option>
                            <option value="fegyverbolt-kozvetites"<?php selected( $v, 'fegyverbolt-kozvetites' ); ?>>Fegyverbolt közvetítés</option>
                            <option value="postai"<?php selected( $v, 'postai' ); ?>>Postai</option>
                        </select>
                    </div>

                    <div class="va-form-group va-exchange-dynamic-group" data-exchange-categories="fegyver">
                        <label>Kaliber (ha fegyver)</label>
                        <input type="text" name="exchange_firearm_caliber" class="va-input" placeholder="pl. 9x19" value="<?php echo esc_attr( $exchange_saved( 'exchange_firearm_caliber' ) ); ?>">
                    </div>
                    <div class="va-form-group va-exchange-dynamic-group" data-exchange-categories="fegyver">
                        <label>Engedélyköteles (ha fegyver)</label>
                        <?php $v = $exchange_saved( 'exchange_firearm_license' ); ?>
                        <select name="exchange_firearm_license" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="engedelykoteles"<?php selected( $v, 'engedelykoteles' ); ?>>Engedélyköteles</option>
                            <option value="csak-hivatalos-atirassal"<?php selected( $v, 'csak-hivatalos-atirassal' ); ?>>Csak hivatalos átírással</option>
                            <option value="szemelyes-atvetel"<?php selected( $v, 'szemelyes-atvetel' ); ?>>Személyes átvétel</option>
                        </select>
                    </div>
                    <div class="va-form-group va-exchange-dynamic-group" data-exchange-categories="optika,hokamera">
                        <label>Nagyítás (ha optika/hőkamera)</label>
                        <input type="text" name="exchange_optic_zoom" class="va-input" placeholder="pl. 3-12x" value="<?php echo esc_attr( $exchange_saved( 'exchange_optic_zoom' ) ); ?>">
                    </div>
                    <div class="va-form-group va-exchange-dynamic-group" data-exchange-categories="optika,hokamera">
                        <label>Objektív (ha optika/hőkamera)</label>
                        <input type="text" name="exchange_optic_objective" class="va-input" placeholder="pl. 56 mm" value="<?php echo esc_attr( $exchange_saved( 'exchange_optic_objective' ) ); ?>">
                    </div>
                    <div class="va-form-group va-exchange-dynamic-group" data-exchange-categories="kes">
                        <label>Acél típus (ha kés)</label>
                        <input type="text" name="exchange_knife_steel" class="va-input" placeholder="pl. D2" value="<?php echo esc_attr( $exchange_saved( 'exchange_knife_steel' ) ); ?>">
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Rövid leírás</label>
                        <textarea name="exchange_short_desc" class="va-input" rows="2" placeholder="Rövid összefoglaló"><?php echo esc_textarea( $exchange_saved( 'exchange_short_desc' ) ); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Részletes leírás</label>
                        <textarea name="exchange_description" class="va-input" rows="3" placeholder="Részletes paraméterek, tartozékok"><?php echo esc_textarea( $exchange_saved( 'exchange_description' ) ); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Hibák</label>
                        <textarea name="exchange_defects" class="va-input" rows="2" placeholder="Ismert hibák, hiányok"><?php echo esc_textarea( $exchange_saved( 'exchange_defects' ) ); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Használati nyomok</label>
                        <textarea name="exchange_wear_signs" class="va-input" rows="2" placeholder="Karcok, kopások"><?php echo esc_textarea( $exchange_saved( 'exchange_wear_signs' ) ); ?></textarea>
                    </div>
                </div>
            </div>
            <div class="va-cat-rule-field" data-categories="takarmany">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Takarmány típusa</label>
                        <select name="feed_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="szoro"<?php echo (($edit_meta['feed_type'] ?? '') === 'szoro') ? ' selected' : ''; ?>>Szóró takarmány</option>
                            <option value="abrak"<?php echo (($edit_meta['feed_type'] ?? '') === 'abrak') ? ' selected' : ''; ?>>Abrak</option>
                            <option value="kukorica"<?php echo (($edit_meta['feed_type'] ?? '') === 'kukorica') ? ' selected' : ''; ?>>Kukorica</option>
                            <option value="gabonakeverek"<?php echo (($edit_meta['feed_type'] ?? '') === 'gabonakeverek') ? ' selected' : ''; ?>>Gabonakeverék</option>
                            <option value="pellet"<?php echo (($edit_meta['feed_type'] ?? '') === 'pellet') ? ' selected' : ''; ?>>Pellet</option>
                            <option value="feherje-kiegeszito"<?php echo (($edit_meta['feed_type'] ?? '') === 'feherje-kiegeszito') ? ' selected' : ''; ?>>Fehérje / kiegészítő takarmány</option>
                            <option value="sozo"<?php echo (($edit_meta['feed_type'] ?? '') === 'sozo') ? ' selected' : ''; ?>>Sózó</option>
                            <option value="egyeb"<?php echo (($edit_meta['feed_type'] ?? '') === 'egyeb') ? ' selected' : ''; ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Milyen vadnak ajánlott</label>
                        <select name="feed_recommended_game_flags[]" class="va-select" multiple data-placeholder="Válassz vadfajt">
                            <option value="feed_game_boar"<?php echo (($edit_meta['feed_game_boar'] ?? '') === '1') ? ' selected' : ''; ?>>Vaddisznó</option>
                            <option value="feed_game_roe"<?php echo (($edit_meta['feed_game_roe'] ?? '') === '1') ? ' selected' : ''; ?>>Őz</option>
                            <option value="feed_game_red_deer"<?php echo (($edit_meta['feed_game_red_deer'] ?? '') === '1') ? ' selected' : ''; ?>>Szarvas</option>
                            <option value="feed_game_pheasant"<?php echo (($edit_meta['feed_game_pheasant'] ?? '') === '1') ? ' selected' : ''; ?>>Fácán</option>
                            <option value="feed_game_other"<?php echo (($edit_meta['feed_game_other'] ?? '') === '1') ? ' selected' : ''; ?>>Egyéb</option>
                        </select>
                        <input type="text" name="feed_game_other_text" class="va-input" placeholder="Egyéb vadfaj megnevezése" value="<?php echo esc_attr((string)($edit_meta['feed_game_other_text'] ?? '')); ?>" style="margin-top:8px;">
                        <textarea name="feed_recommended_game" class="va-input" rows="2" placeholder="Részletezés (opcionális)" style="margin-top:8px;"><?php echo esc_textarea((string)($edit_meta['feed_recommended_game'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Összetevők</label>
                        <textarea name="feed_ingredients" class="va-input" rows="3" placeholder="pl. kukorica, búza, árpa, napraforgó, pellet"><?php echo esc_textarea((string)($edit_meta['feed_ingredients'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group">
                        <label>Nedvességtartalom</label>
                        <input type="text" name="feed_moisture" class="va-input" placeholder="pl. 12%" value="<?php echo esc_attr((string)($edit_meta['feed_moisture'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Adalékanyagok vannak benne?</label>
                        <select name="feed_has_additives" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['feed_has_additives'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['feed_has_additives'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Kiszerelés</label>
                        <select name="feed_packaging_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="zsakos"<?php echo (($edit_meta['feed_packaging_type'] ?? '') === 'zsakos') ? ' selected' : ''; ?>>Zsákos</option>
                            <option value="omlesztett"<?php echo (($edit_meta['feed_packaging_type'] ?? '') === 'omlesztett') ? ' selected' : ''; ?>>Ömlesztett</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Egy zsák súlya</label>
                        <input type="text" name="feed_bag_weight" class="va-input" placeholder="pl. 25 kg" value="<?php echo esc_attr((string)($edit_meta['feed_bag_weight'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Elérhető mennyiség összesen</label>
                        <input type="text" name="feed_total_quantity" class="va-input" placeholder="pl. 1200 kg" value="<?php echo esc_attr((string)($edit_meta['feed_total_quantity'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Raklapos mennyiség</label>
                        <input type="text" name="feed_pallet_quantity" class="va-input" placeholder="pl. 1 raklap = 40 zsák" value="<?php echo esc_attr((string)($edit_meta['feed_pallet_quantity'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Friss vagy tavalyi termés</label>
                        <select name="feed_harvest_freshness" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="friss"<?php echo (($edit_meta['feed_harvest_freshness'] ?? '') === 'friss') ? ' selected' : ''; ?>>Friss termés</option>
                            <option value="tavalyi"<?php echo (($edit_meta['feed_harvest_freshness'] ?? '') === 'tavalyi') ? ' selected' : ''; ?>>Tavalyi termés</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Száraz helyen tárolták?</label>
                        <select name="feed_stored_dry" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['feed_stored_dry'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['feed_stored_dry'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Penészmentes?</label>
                        <select name="feed_mold_free" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['feed_mold_free'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['feed_mold_free'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Sérülésmentes zsákok</label>
                        <select name="feed_bags_undamaged" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['feed_bags_undamaged'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['feed_bags_undamaged'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Ár/kg vagy ár/zsák</label>
                        <input type="text" name="feed_price_per_unit" class="va-input" placeholder="pl. 180 Ft/kg vagy 4500 Ft/zsák" value="<?php echo esc_attr((string)($edit_meta['feed_price_per_unit'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Minimum rendelés</label>
                        <input type="text" name="feed_min_order" class="va-input" placeholder="pl. 10 zsák" value="<?php echo esc_attr((string)($edit_meta['feed_min_order'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Szállítás megoldható?</label>
                        <select name="feed_delivery_available" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['feed_delivery_available'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['feed_delivery_available'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Rakodás van?</label>
                        <select name="feed_loading_available" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['feed_loading_available'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['feed_loading_available'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="va-cat-rule-field" data-categories="ij-szamszerij-fuvocso,ij,ijak,szamszerij-nyilpuska,ijvesszo,fuvocso,nyilpisztoly,kiegeszitok-ij,szamszerij">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Íj típusa</label>
                        <select name="bow_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="tradicionalis"<?php echo (($edit_meta['bow_type'] ?? '') === 'tradicionalis') ? ' selected' : ''; ?>>Tradicionális</option>
                            <option value="reflex"<?php echo (($edit_meta['bow_type'] ?? '') === 'reflex') ? ' selected' : ''; ?>>Reflex</option>
                            <option value="longbow"<?php echo (($edit_meta['bow_type'] ?? '') === 'longbow') ? ' selected' : ''; ?>>Longbow</option>
                            <option value="compound"<?php echo (($edit_meta['bow_type'] ?? '') === 'compound') ? ' selected' : ''; ?>>Compound</option>
                            <option value="szamszerij"<?php echo (($edit_meta['bow_type'] ?? '') === 'szamszerij') ? ' selected' : ''; ?>>Számszeríj</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Kezesség</label>
                        <select name="bow_handedness" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="jobbkezes"<?php echo (($edit_meta['bow_handedness'] ?? '') === 'jobbkezes') ? ' selected' : ''; ?>>Jobbkezes</option>
                            <option value="balkezes"<?php echo (($edit_meta['bow_handedness'] ?? '') === 'balkezes') ? ' selected' : ''; ?>>Balkezes</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Húzóerő (lbs)</label>
                        <input type="text" name="bow_draw_weight" class="va-input" placeholder="pl. 55" value="<?php echo esc_attr((string)($edit_meta['bow_draw_weight'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Húzáshossz (inch)</label>
                        <input type="text" name="bow_draw_length" class="va-input" placeholder="pl. 29" value="<?php echo esc_attr((string)($edit_meta['bow_draw_length'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Tengelytáv / teljes hossz</label>
                        <input type="text" name="bow_axle_length" class="va-input" placeholder="pl. 34 inch" value="<?php echo esc_attr((string)($edit_meta['bow_axle_length'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Állapot (új / használt)</label>
                        <select name="bow_item_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="uj"<?php echo (($edit_meta['bow_item_condition'] ?? '') === 'uj') ? ' selected' : ''; ?>>Új</option>
                            <option value="hasznalt"<?php echo (($edit_meta['bow_item_condition'] ?? '') === 'hasznalt') ? ' selected' : ''; ?>>Használt</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Állapot részletei (karc, sérülés, repedés, húrcsere)</label>
                        <textarea name="bow_condition_notes" class="va-input" rows="2" placeholder="pl. kisebb karcok, húrcsere 2025-ben"><?php echo esc_textarea((string)($edit_meta['bow_condition_notes'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group">
                        <label>Húrcsere történt?</label>
                        <select name="bow_string_replaced" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo (($edit_meta['bow_string_replaced'] ?? '') === 'igen') ? ' selected' : ''; ?>>Igen</option>
                            <option value="nem"<?php echo (($edit_meta['bow_string_replaced'] ?? '') === 'nem') ? ' selected' : ''; ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Mióta van nálad</label>
                        <input type="text" name="bow_owned_since" class="va-input" placeholder="pl. 2022 óta" value="<?php echo esc_attr((string)($edit_meta['bow_owned_since'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Mennyit volt használva</label>
                        <textarea name="bow_usage" class="va-input" rows="2" placeholder="pl. heti 1 edzés, versenyen nem volt"><?php echo esc_textarea((string)($edit_meta['bow_usage'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Tartozékok (leírás)</label>
                        <textarea name="bow_accessories" class="va-input" rows="3" placeholder="Írd le pontosan, mi jár hozzá."><?php echo esc_textarea((string)($edit_meta['bow_accessories'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1; margin-bottom:10px;">
                        <label>Íj tartozékok</label>
                        <select name="bow_accessories_flags[]" class="va-select" multiple data-placeholder="Válassz tartozékot">
                            <option value="bow_acc_sight"<?php echo (($edit_meta['bow_acc_sight'] ?? '') === '1') ? ' selected' : ''; ?>>Irányzék</option>
                            <option value="bow_acc_release"<?php echo (($edit_meta['bow_acc_release'] ?? '') === '1') ? ' selected' : ''; ?>>Kioldó</option>
                            <option value="bow_acc_stabilizer"<?php echo (($edit_meta['bow_acc_stabilizer'] ?? '') === '1') ? ' selected' : ''; ?>>Stabilizátor</option>
                            <option value="bow_acc_arrows"<?php echo (($edit_meta['bow_acc_arrows'] ?? '') === '1') ? ' selected' : ''; ?>>Vesszők</option>
                            <option value="bow_acc_case"<?php echo (($edit_meta['bow_acc_case'] ?? '') === '1') ? ' selected' : ''; ?>>Tok</option>
                            <option value="bow_acc_quiver"<?php echo (($edit_meta['bow_acc_quiver'] ?? '') === '1') ? ' selected' : ''; ?>>Tegez</option>
                            <option value="bow_acc_target"<?php echo (($edit_meta['bow_acc_target'] ?? '') === '1') ? ' selected' : ''; ?>>Céltábla</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Csigák / kábelek állapota</label>
                        <textarea name="bow_cams_cables" class="va-input" rows="2" placeholder="pl. megkímélt, időben cserélve"><?php echo esc_textarea((string)($edit_meta['bow_cams_cables'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group">
                        <label>Sebesség (FPS)</label>
                        <input type="text" name="bow_speed_fps" class="va-input" placeholder="pl. 340" value="<?php echo esc_attr((string)($edit_meta['bow_speed_fps'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Sorozatszám (opcionális)</label>
                        <input type="text" name="bow_serial" class="va-input" placeholder="Nem kötelező nyilvánosan" value="<?php echo esc_attr((string)($edit_meta['bow_serial'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Anyag</label>
                        <input type="text" name="bow_material" class="va-input" placeholder="pl. karbon, alumínium" value="<?php echo esc_attr((string)($edit_meta['bow_material'] ?? '')); ?>">
                    </div>
                </div>
            </div>
            <?php
                $other_weapon_kind_val = (string)($edit_meta['other_weapon_kind'] ?? '');
                $other_weapon_accessories_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['other_weapon_accessories'] ?? ''))));
            ?>
            <div class="va-form-group va-other-weapon-fields-grid" data-categories="egyeb-fegyverek" style="display:none">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <h3 class="va-specs-heading">Egyéb fegyverek részletei</h3>
                    </div>
                    <div class="va-form-group">
                        <label>Kategória</label>
                        <select name="other_weapon_kind" id="va-other-weapon-kind" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="legalis-hobbi-sport-eszkoz"<?php echo selected( $other_weapon_kind_val, 'legalis-hobbi-sport-eszkoz', false ); ?>>Legális hobbi / sport / eszköz</option>
                            <option value="gyujtoi"<?php echo selected( $other_weapon_kind_val, 'gyujtoi', false ); ?>>Gyűjtői</option>
                            <option value="dekor"<?php echo selected( $other_weapon_kind_val, 'dekor', false ); ?>>Dekor</option>
                            <option value="airsoft"<?php echo selected( $other_weapon_kind_val, 'airsoft', false ); ?>>Airsoft</option>
                            <option value="ij"<?php echo selected( $other_weapon_kind_val, 'ij', false ); ?>>Íj</option>
                            <option value="szamszerij"<?php echo selected( $other_weapon_kind_val, 'szamszerij', false ); ?>>Számszeríj</option>
                            <option value="legfegyver"<?php echo selected( $other_weapon_kind_val, 'legfegyver', false ); ?>>Légfegyver</option>
                            <option value="gaz-riaszto"<?php echo selected( $other_weapon_kind_val, 'gaz-riaszto', false ); ?>>Gáz-riasztó</option>
                            <option value="muzealis"<?php echo selected( $other_weapon_kind_val, 'muzealis', false ); ?>>Muzeális</option>
                            <option value="diszfegyver"<?php echo selected( $other_weapon_kind_val, 'diszfegyver', false ); ?>>Díszfegyver</option>
                            <option value="vadaszkes"<?php echo selected( $other_weapon_kind_val, 'vadaszkes', false ); ?>>Vadászkés</option>
                            <option value="tulelo-eszkoz"<?php echo selected( $other_weapon_kind_val, 'tulelo-eszkoz', false ); ?>>Túlélő eszköz</option>
                            <option value="taktikai-eszkoz"<?php echo selected( $other_weapon_kind_val, 'taktikai-eszkoz', false ); ?>>Taktikai eszköz</option>
                            <option value="egyeb"<?php echo selected( $other_weapon_kind_val, 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Megjegyzés</label>
                        <textarea name="other_weapon_general_notes" class="va-input" rows="2" placeholder="Rövid leírás a rendeltetésről, eredetről vagy különleges megkötésről."><?php echo esc_textarea((string)($edit_meta['other_weapon_general_notes'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group" style="margin-bottom:10px;">
                        <label>Tartozékok</label>
                        <select name="other_weapon_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot">
                            <option value="eredeti-doboz"<?php echo in_array('eredeti-doboz', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Eredeti doboz</option>
                            <option value="tok"<?php echo in_array('tok', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Tok</option>
                            <option value="optika"<?php echo in_array('optika', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Optika</option>
                            <option value="heveder"<?php echo in_array('heveder', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Heveder</option>
                            <option value="vesszo"<?php echo in_array('vesszo', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Vesszők</option>
                            <option value="tar"<?php echo in_array('tar', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Tárak</option>
                            <option value="gaspatron"<?php echo in_array('gaspatron', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Gázpatron</option>
                            <option value="akkumulator"<?php echo in_array('akkumulator', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Akkumulátor</option>
                            <option value="tolto"<?php echo in_array('tolto', $other_weapon_accessories_saved, true) ? ' selected' : ''; ?>>Töltő</option>
                        </select>
                        <input type="text" name="other_weapon_accessories_other" class="va-input" style="margin-top:10px;" placeholder="Egyéb tartozékok" value="<?php echo esc_attr((string)($edit_meta['other_weapon_accessories_other'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>18+ termék?</label>
                        <select name="other_weapon_18plus" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_18plus'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_18plus'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Csak személyes átvétel?</label>
                        <select name="other_weapon_personal_pickup" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_personal_pickup'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_personal_pickup'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Származási papír van?</label>
                        <select name="other_weapon_origin_papers" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_origin_papers'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_origin_papers'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Sorozatszám</label>
                        <input type="text" name="other_weapon_serial_number" class="va-input" placeholder="Opcionális" value="<?php echo esc_attr((string)($edit_meta['other_weapon_serial_number'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;grid-column:1 / -1;">
                        <strong>Íj</strong>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;">
                        <label>Húzóerő (lbs)</label>
                        <input type="text" name="other_weapon_bow_draw_weight_lbs" class="va-input" placeholder="pl. 45" value="<?php echo esc_attr((string)($edit_meta['other_weapon_bow_draw_weight_lbs'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;">
                        <label>Tengelytáv</label>
                        <input type="text" name="other_weapon_bow_axle_to_axle" class="va-input" placeholder="pl. 30 inch" value="<?php echo esc_attr((string)($edit_meta['other_weapon_bow_axle_to_axle'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;">
                        <label>Jobb / balkezes</label>
                        <select name="other_weapon_bow_handness" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="jobbkezes"<?php echo selected( (string)($edit_meta['other_weapon_bow_handness'] ?? ''), 'jobbkezes', false ); ?>>Jobbkezes</option>
                            <option value="balkezes"<?php echo selected( (string)($edit_meta['other_weapon_bow_handness'] ?? ''), 'balkezes', false ); ?>>Balkezes</option>
                            <option value="univerzalis"<?php echo selected( (string)($edit_meta['other_weapon_bow_handness'] ?? ''), 'univerzalis', false ); ?>>Univerzális</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;">
                        <label>Csigás / tradicionális</label>
                        <select name="other_weapon_bow_style" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="compound"<?php echo selected( (string)($edit_meta['other_weapon_bow_style'] ?? ''), 'compound', false ); ?>>Csigás</option>
                            <option value="tradicionalis"<?php echo selected( (string)($edit_meta['other_weapon_bow_style'] ?? ''), 'tradicionalis', false ); ?>>Tradicionális</option>
                            <option value="recurve"<?php echo selected( (string)($edit_meta['other_weapon_bow_style'] ?? ''), 'recurve', false ); ?>>Recurve</option>
                            <option value="longbow"<?php echo selected( (string)($edit_meta['other_weapon_bow_style'] ?? ''), 'longbow', false ); ?>>Longbow</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;">
                        <label>Vesszőtartó van?</label>
                        <select name="other_weapon_bow_arrow_rest" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_bow_arrow_rest'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_bow_arrow_rest'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="ij" style="display:none;">
                        <label>Irányzék van?</label>
                        <select name="other_weapon_bow_sight" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_bow_sight'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_bow_sight'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="szamszerij" style="display:none;grid-column:1 / -1;">
                        <strong>Számszeríj</strong>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="szamszerij" style="display:none;">
                        <label>Húzóerő (lbs)</label>
                        <input type="text" name="other_weapon_crossbow_draw_weight_lbs" class="va-input" placeholder="pl. 175" value="<?php echo esc_attr((string)($edit_meta['other_weapon_crossbow_draw_weight_lbs'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="szamszerij" style="display:none;">
                        <label>Sebesség (FPS)</label>
                        <input type="text" name="other_weapon_crossbow_fps" class="va-input" placeholder="pl. 390" value="<?php echo esc_attr((string)($edit_meta['other_weapon_crossbow_fps'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="szamszerij" style="display:none;grid-column:1 / -1;">
                        <label>Tartozékok</label>
                        <textarea name="other_weapon_crossbow_accessories" class="va-input" rows="2" placeholder="Puskamarkolat, húr, kormányzó, célzó, stb."><?php echo esc_textarea((string)($edit_meta['other_weapon_crossbow_accessories'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="airsoft" style="display:none;grid-column:1 / -1;">
                        <strong>Airsoft</strong>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="airsoft" style="display:none;">
                        <label>Működés</label>
                        <select name="other_weapon_airsoft_operation" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="spring"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_operation'] ?? ''), 'spring', false ); ?>>Spring</option>
                            <option value="aeg"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_operation'] ?? ''), 'aeg', false ); ?>>AEG</option>
                            <option value="gbb"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_operation'] ?? ''), 'gbb', false ); ?>>GBB</option>
                            <option value="hpa"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_operation'] ?? ''), 'hpa', false ); ?>>HPA</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_operation'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="airsoft" style="display:none;">
                        <label>Energia (Joule)</label>
                        <input type="text" name="other_weapon_airsoft_energy_joule" class="va-input" placeholder="pl. 0.8" value="<?php echo esc_attr((string)($edit_meta['other_weapon_airsoft_energy_joule'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="airsoft" style="display:none;">
                        <label>FPS</label>
                        <input type="text" name="other_weapon_airsoft_fps" class="va-input" placeholder="pl. 320" value="<?php echo esc_attr((string)($edit_meta['other_weapon_airsoft_fps'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="airsoft" style="display:none;">
                        <label>Tár típusa</label>
                        <select name="other_weapon_airsoft_magazine_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="mid-cap"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_magazine_type'] ?? ''), 'mid-cap', false ); ?>>Mid-cap</option>
                            <option value="hi-cap"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_magazine_type'] ?? ''), 'hi-cap', false ); ?>>Hi-cap</option>
                            <option value="low-cap"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_magazine_type'] ?? ''), 'low-cap', false ); ?>>Low-cap</option>
                            <option value="drum"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_magazine_type'] ?? ''), 'drum', false ); ?>>Drum</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['other_weapon_airsoft_magazine_type'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="legfegyver" style="display:none;grid-column:1 / -1;">
                        <strong>Légfegyver</strong>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="legfegyver" style="display:none;">
                        <label>Kaliber</label>
                        <input type="text" name="other_weapon_airgun_caliber" class="va-input" placeholder="pl. 4.5 mm" value="<?php echo esc_attr((string)($edit_meta['other_weapon_airgun_caliber'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="legfegyver" style="display:none;">
                        <label>Torkolati energia</label>
                        <input type="text" name="other_weapon_airgun_muzzle_energy" class="va-input" placeholder="pl. 7.5 J" value="<?php echo esc_attr((string)($edit_meta['other_weapon_airgun_muzzle_energy'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="legfegyver" style="display:none;">
                        <label>Működés</label>
                        <select name="other_weapon_airgun_power_source" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="pcp"<?php echo selected( (string)($edit_meta['other_weapon_airgun_power_source'] ?? ''), 'pcp', false ); ?>>PCP</option>
                            <option value="rugo"<?php echo selected( (string)($edit_meta['other_weapon_airgun_power_source'] ?? ''), 'rugo', false ); ?>>Rugós</option>
                            <option value="co2"<?php echo selected( (string)($edit_meta['other_weapon_airgun_power_source'] ?? ''), 'co2', false ); ?>>CO2</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['other_weapon_airgun_power_source'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="gaz-riaszto" style="display:none;grid-column:1 / -1;">
                        <strong>Gáz-riasztó</strong>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="gaz-riaszto" style="display:none;">
                        <label>Kaliber</label>
                        <input type="text" name="other_weapon_gas_alarm_caliber" class="va-input" placeholder="pl. 9 mm P.A.K." value="<?php echo esc_attr((string)($edit_meta['other_weapon_gas_alarm_caliber'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="gaz-riaszto" style="display:none;">
                        <label>Önvédelmi engedély szükséges?</label>
                        <select name="other_weapon_gas_alarm_self_defense_license" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_gas_alarm_self_defense_license'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_gas_alarm_self_defense_license'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="gaz-riaszto" style="display:none;">
                        <label>Viselési engedély van?</label>
                        <select name="other_weapon_gas_alarm_carry_license" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_gas_alarm_carry_license'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_gas_alarm_carry_license'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="vadaszkes,taktikai-eszkoz,tulelo-eszkoz" style="display:none;grid-column:1 / -1;">
                        <strong>Vadászkés / túlélő eszköz</strong>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="vadaszkes,taktikai-eszkoz,tulelo-eszkoz" style="display:none;">
                        <label>Pengehossz</label>
                        <input type="text" name="other_weapon_knife_blade_length" class="va-input" placeholder="pl. 11 cm" value="<?php echo esc_attr((string)($edit_meta['other_weapon_knife_blade_length'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="vadaszkes,taktikai-eszkoz,tulelo-eszkoz" style="display:none;">
                        <label>Acél típusa</label>
                        <input type="text" name="other_weapon_knife_steel_type" class="va-input" placeholder="pl. D2, 440C" value="<?php echo esc_attr((string)($edit_meta['other_weapon_knife_steel_type'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="vadaszkes,taktikai-eszkoz,tulelo-eszkoz" style="display:none;">
                        <label>Markolat anyaga</label>
                        <input type="text" name="other_weapon_knife_handle_material" class="va-input" placeholder="pl. G10, fa, micarta" value="<?php echo esc_attr((string)($edit_meta['other_weapon_knife_handle_material'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="vadaszkes,taktikai-eszkoz,tulelo-eszkoz" style="display:none;">
                        <label>Tok van?</label>
                        <select name="other_weapon_knife_sheath" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['other_weapon_knife_sheath'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['other_weapon_knife_sheath'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group va-other-weapon-subgroup" data-other-weapon-kinds="vadaszkes,taktikai-eszkoz,tulelo-eszkoz" style="display:none;grid-column:1 / -1;">
                        <label>Tartozékok</label>
                        <textarea name="other_weapon_knife_accessories" class="va-input" rows="2" placeholder="pl. eredeti doboz, élező, övcsipesz"><?php echo esc_textarea((string)($edit_meta['other_weapon_knife_accessories'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>
            <?php
                $hatastalanitott_working_parts_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['hatastalanitott_working_parts'] ?? ''))));
                $hatastalanitott_accessories_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['hatastalanitott_accessories'] ?? ''))));
            ?>
            <div class="va-cat-rule-field" data-categories="hatastalanitott" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <h3 class="va-specs-heading">Hatástalanított fegyverek adatai</h3>
                    </div>
                    <div class="va-form-group">
                        <label>Fegyver típusa</label>
                        <select name="hatastalanitott_weapon_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="pisztoly"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'pisztoly', false ); ?>>Pisztoly</option>
                            <option value="revolver"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'revolver', false ); ?>>Revolver</option>
                            <option value="soretes"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'soretes', false ); ?>>Sörétes</option>
                            <option value="golyos-puska"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'golyos-puska', false ); ?>>Golyós puska</option>
                            <option value="gepkarabely"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'gepkarabely', false ); ?>>Gépkarabély</option>
                            <option value="geppisztoly"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'geppisztoly', false ); ?>>Géppisztoly</option>
                            <option value="tortenelmi"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'tortenelmi', false ); ?>>Történelmi</option>
                            <option value="replika"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'replika', false ); ?>>Replika</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['hatastalanitott_weapon_type'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Származási ország</label>
                        <input type="text" name="hatastalanitott_origin_country" class="va-input" placeholder="pl. Magyarország" value="<?php echo esc_attr((string)($edit_meta['hatastalanitott_origin_country'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Hatástalanított?</label>
                        <select name="hatastalanitott_is_deactivated" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['hatastalanitott_is_deactivated'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['hatastalanitott_is_deactivated'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Hatástalanítás típusa</label>
                        <select name="hatastalanitott_deactivation_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="eu-szabvany"<?php echo selected( (string)($edit_meta['hatastalanitott_deactivation_type'] ?? ''), 'eu-szabvany', false ); ?>>EU szabvány</option>
                            <option value="regi-magyar"<?php echo selected( (string)($edit_meta['hatastalanitott_deactivation_type'] ?? ''), 'regi-magyar', false ); ?>>Régi magyar</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['hatastalanitott_deactivation_type'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Tanúsítvány van?</label>
                        <select name="hatastalanitott_certificate" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['hatastalanitott_certificate'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['hatastalanitott_certificate'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>MKH / CIP jelölés?</label>
                        <select name="hatastalanitott_mkh_cip" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['hatastalanitott_mkh_cip'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['hatastalanitott_mkh_cip'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Hatástalanítás éve</label>
                        <input type="text" name="hatastalanitott_deactivation_year" class="va-input" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="pl. 2018" value="<?php echo esc_attr((string)($edit_meta['hatastalanitott_deactivation_year'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Hatástalanító szervezet (opcionális)</label>
                        <input type="text" name="hatastalanitott_deactivation_org" class="va-input" placeholder="pl. BM tanúsító" value="<?php echo esc_attr((string)($edit_meta['hatastalanitott_deactivation_org'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Állapot</label>
                        <select name="hatastalanitott_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="uj"<?php echo selected( (string)($edit_meta['hatastalanitott_condition'] ?? ''), 'uj', false ); ?>>Új</option>
                            <option value="gyujtoi"<?php echo selected( (string)($edit_meta['hatastalanitott_condition'] ?? ''), 'gyujtoi', false ); ?>>Gyűjtői</option>
                            <option value="jo"<?php echo selected( (string)($edit_meta['hatastalanitott_condition'] ?? ''), 'jo', false ); ?>>Jó</option>
                            <option value="hasznalt"<?php echo selected( (string)($edit_meta['hatastalanitott_condition'] ?? ''), 'hasznalt', false ); ?>>Használt</option>
                            <option value="restauralt"<?php echo selected( (string)($edit_meta['hatastalanitott_condition'] ?? ''), 'restauralt', false ); ?>>Restaurált</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Sorozatszám (csak admin lássa)</label>
                        <input type="text" name="hatastalanitott_serial_admin" class="va-input" placeholder="pl. AB123456" value="<?php echo esc_attr((string)($edit_meta['hatastalanitott_serial_admin'] ?? '')); ?>">
                        <small style="display:block;margin-top:6px;opacity:.8;">Publikus felületen maszkolva jelenjen meg: pl. AB123***</small>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Működő alkatrészek</label>
                        <select name="hatastalanitott_working_parts[]" class="va-select" multiple data-placeholder="Válassz működő alkatrészt">
                            <option value="zar-mozog"<?php echo in_array('zar-mozog', $hatastalanitott_working_parts_saved, true) ? ' selected' : ''; ?>>Zár mozog</option>
                            <option value="elsuto-mukodik"<?php echo in_array('elsuto-mukodik', $hatastalanitott_working_parts_saved, true) ? ' selected' : ''; ?>>Elsütő működik</option>
                            <option value="szetszedheto"<?php echo in_array('szetszedheto', $hatastalanitott_working_parts_saved, true) ? ' selected' : ''; ?>>Szétszedhető</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Eredeti felület</label>
                        <select name="hatastalanitott_original_finish" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="barnitott"<?php echo selected( (string)($edit_meta['hatastalanitott_original_finish'] ?? ''), 'barnitott', false ); ?>>Barnított</option>
                            <option value="kromozott"<?php echo selected( (string)($edit_meta['hatastalanitott_original_finish'] ?? ''), 'kromozott', false ); ?>>Krómozott</option>
                            <option value="festett"<?php echo selected( (string)($edit_meta['hatastalanitott_original_finish'] ?? ''), 'festett', false ); ?>>Festett</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Eredeti alkatrészek aránya (%)</label>
                        <input type="text" name="hatastalanitott_original_parts_ratio" class="va-input" inputmode="numeric" pattern="[0-9]{1,3}" maxlength="3" placeholder="pl. 85" value="<?php echo esc_attr((string)($edit_meta['hatastalanitott_original_parts_ratio'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Tartozékok</label>
                        <select name="hatastalanitott_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot">
                            <option value="eredeti-tok"<?php echo in_array('eredeti-tok', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Eredeti tok</option>
                            <option value="tar"<?php echo in_array('tar', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Tár</option>
                            <option value="heveder"<?php echo in_array('heveder', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Heveder</option>
                            <option value="szurony"<?php echo in_array('szurony', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Szurony</option>
                            <option value="dokumentacio"<?php echo in_array('dokumentacio', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Dokumentáció</option>
                            <option value="tanusitvany"<?php echo in_array('tanusitvany', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Tanúsítvány</option>
                            <option value="gyari-doboz"<?php echo in_array('gyari-doboz', $hatastalanitott_accessories_saved, true) ? ' selected' : ''; ?>>Gyári doboz</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="va-cat-rule-field" data-categories="loszer-tolteny" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <h3 class="va-specs-heading">Lőszer / töltény műszaki adatok</h3>
                    </div>
                    <div class="va-form-group">
                        <label>Kategória</label>
                        <select name="loszer_category" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="golyos-loszer"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'golyos-loszer', false ); ?>>Golyós lőszer</option>
                            <option value="soretes-loszer"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'soretes-loszer', false ); ?>>Sörétes lőszer</option>
                            <option value="peremgyujtasu"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'peremgyujtasu', false ); ?>>Peremgyújtású</option>
                            <option value="vakloszer"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'vakloszer', false ); ?>>Vaktöltény</option>
                            <option value="riaszto-patron"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'riaszto-patron', false ); ?>>Riasztó patron</option>
                            <option value="gyujtoi-tolteny"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'gyujtoi-tolteny', false ); ?>>Gyűjtői töltény</option>
                            <option value="huvely"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'huvely', false ); ?>>Hüvely</option>
                            <option value="lovedek"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'lovedek', false ); ?>>Lövedék</option>
                            <option value="csappantyu"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'csappantyu', false ); ?>>Csappantyú</option>
                            <option value="lopor"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'lopor', false ); ?>>Lőpor</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['loszer_category'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Kaliber (előre definiált)</label>
                        <select name="loszer_caliber_preset" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="22lr"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), '22lr', false ); ?>>.22 LR</option>
                            <option value="223rem"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), '223rem', false ); ?>>.223 Rem</option>
                            <option value="308win"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), '308win', false ); ?>>.308 Win</option>
                            <option value="762x39"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), '762x39', false ); ?>>7.62x39</option>
                            <option value="9mmpak"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), '9mmpak', false ); ?>>9mm P.A.K.</option>
                            <option value="12-70"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), '12-70', false ); ?>>12/70</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['loszer_caliber_preset'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Egyedi kaliber</label>
                        <input type="text" name="loszer_caliber_custom" class="va-input" placeholder="pl. 6.5 Creedmoor" value="<?php echo esc_attr((string)($edit_meta['loszer_caliber_custom'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Gyártó (lista)</label>
                        <select name="loszer_manufacturer_preset" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="hornady"<?php echo selected( (string)($edit_meta['loszer_manufacturer_preset'] ?? ''), 'hornady', false ); ?>>Hornady</option>
                            <option value="sellier-bellot"<?php echo selected( (string)($edit_meta['loszer_manufacturer_preset'] ?? ''), 'sellier-bellot', false ); ?>>Sellier &amp; Bellot</option>
                            <option value="geco"<?php echo selected( (string)($edit_meta['loszer_manufacturer_preset'] ?? ''), 'geco', false ); ?>>Geco</option>
                            <option value="norma"<?php echo selected( (string)($edit_meta['loszer_manufacturer_preset'] ?? ''), 'norma', false ); ?>>Norma</option>
                            <option value="lapua"<?php echo selected( (string)($edit_meta['loszer_manufacturer_preset'] ?? ''), 'lapua', false ); ?>>Lapua</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['loszer_manufacturer_preset'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Gyártó (egyedi)</label>
                        <input type="text" name="loszer_manufacturer_custom" class="va-input" placeholder="pl. Prvi Partizan" value="<?php echo esc_attr((string)($edit_meta['loszer_manufacturer_custom'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Típus</label>
                        <select name="loszer_projectile_type" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="golyos"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'golyos', false ); ?>>Golyós</option>
                            <option value="fmj"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'fmj', false ); ?>>FMJ</option>
                            <option value="sp"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'sp', false ); ?>>SP</option>
                            <option value="hp"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'hp', false ); ?>>HP</option>
                            <option value="match"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'match', false ); ?>>Match</option>
                            <option value="soft-point"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'soft-point', false ); ?>>Soft Point</option>
                            <option value="ballistic-tip"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'ballistic-tip', false ); ?>>Ballistic Tip</option>
                            <option value="slug"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'slug', false ); ?>>Slug</option>
                            <option value="buckshot"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'buckshot', false ); ?>>Buckshot</option>
                            <option value="birdshot"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'birdshot', false ); ?>>Birdshot</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['loszer_projectile_type'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Lövedéksúly</label>
                        <input type="text" name="loszer_projectile_weight" class="va-input" placeholder="pl. 9.7" value="<?php echo esc_attr((string)($edit_meta['loszer_projectile_weight'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Lövedéksúly mértékegység</label>
                        <select name="loszer_projectile_weight_unit" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="grain"<?php echo selected( (string)($edit_meta['loszer_projectile_weight_unit'] ?? ''), 'grain', false ); ?>>grain</option>
                            <option value="gramm"<?php echo selected( (string)($edit_meta['loszer_projectile_weight_unit'] ?? ''), 'gramm', false ); ?>>gramm</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Darabszám</label>
                        <input type="text" name="loszer_piece_count" class="va-input" placeholder="pl. 50" value="<?php echo esc_attr((string)($edit_meta['loszer_piece_count'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Kiszerelés</label>
                        <select name="loszer_piece_unit" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="doboz"<?php echo selected( (string)($edit_meta['loszer_piece_unit'] ?? ''), 'doboz', false ); ?>>doboz</option>
                            <option value="db"<?php echo selected( (string)($edit_meta['loszer_piece_unit'] ?? ''), 'db', false ); ?>>db</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Gyártási év / LOT szám (opcionális)</label>
                        <input type="text" name="loszer_lot_year" class="va-input" placeholder="pl. 2024 / LOT-A123" value="<?php echo esc_attr((string)($edit_meta['loszer_lot_year'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Állapot</label>
                        <select name="loszer_condition" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="uj"<?php echo selected( (string)($edit_meta['loszer_condition'] ?? ''), 'uj', false ); ?>>Új</option>
                            <option value="bontatlan"<?php echo selected( (string)($edit_meta['loszer_condition'] ?? ''), 'bontatlan', false ); ?>>Bontatlan</option>
                            <option value="bontott"<?php echo selected( (string)($edit_meta['loszer_condition'] ?? ''), 'bontott', false ); ?>>Bontott</option>
                            <option value="gyujtoi"<?php echo selected( (string)($edit_meta['loszer_condition'] ?? ''), 'gyujtoi', false ); ?>>Gyűjtői</option>
                            <option value="korrodalt"<?php echo selected( (string)($edit_meta['loszer_condition'] ?? ''), 'korrodalt', false ); ?>>Korrodált</option>
                            <option value="ujratoltott"<?php echo selected( (string)($edit_meta['loszer_condition'] ?? ''), 'ujratoltott', false ); ?>>Újratöltött</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Újratöltés / komponens mezők (ha releváns)</strong></div>
                    <div class="va-form-group">
                        <label>Hüvely állapota</label>
                        <select name="loszer_component_case_condition" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="uj"<?php echo selected( (string)($edit_meta['loszer_component_case_condition'] ?? ''), 'uj', false ); ?>>Új</option>
                            <option value="egyszer-lott"<?php echo selected( (string)($edit_meta['loszer_component_case_condition'] ?? ''), 'egyszer-lott', false ); ?>>Egyszer lőtt</option>
                            <option value="tisztitott"<?php echo selected( (string)($edit_meta['loszer_component_case_condition'] ?? ''), 'tisztitott', false ); ?>>Tisztított</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Lőpor típusa</label>
                        <input type="text" name="loszer_powder_type" class="va-input" placeholder="pl. Vihtavuori N140" value="<?php echo esc_attr((string)($edit_meta['loszer_powder_type'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Lőpor bontatlan?</label>
                        <select name="loszer_powder_sealed" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['loszer_powder_sealed'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['loszer_powder_sealed'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Lőpor tömeg</label>
                        <input type="text" name="loszer_powder_weight" class="va-input" placeholder="pl. 0.5 kg" value="<?php echo esc_attr((string)($edit_meta['loszer_powder_weight'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Csappantyú típusa</label>
                        <select name="loszer_primer_type" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="small-rifle"<?php echo selected( (string)($edit_meta['loszer_primer_type'] ?? ''), 'small-rifle', false ); ?>>Small Rifle</option>
                            <option value="large-rifle"<?php echo selected( (string)($edit_meta['loszer_primer_type'] ?? ''), 'large-rifle', false ); ?>>Large Rifle</option>
                            <option value="small-pistol"<?php echo selected( (string)($edit_meta['loszer_primer_type'] ?? ''), 'small-pistol', false ); ?>>Small Pistol</option>
                            <option value="large-pistol"<?php echo selected( (string)($edit_meta['loszer_primer_type'] ?? ''), 'large-pistol', false ); ?>>Large Pistol</option>
                            <option value="egyeb"<?php echo selected( (string)($edit_meta['loszer_primer_type'] ?? ''), 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php if ( $site_type !== 'jarmu' ): ?>
            <div class="va-form-row">
                <div class="va-form-group va-cat-rule-field" data-categories="golyos-puska,soretes-puska,vegyescsovu-puska,maroklofegyver,hatastalanitott,loszer-tolteny">
                    <label>Kaliber</label>
                    <select name="caliber" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php
                        $caliber_val = (string)($edit_meta['caliber'] ?? '');
                        if ( $caliber_val !== '' && ! in_array( $caliber_val, $hunting_calibers, true ) ):
                        ?><option value="<?php echo esc_attr($caliber_val); ?>" selected><?php echo esc_html($caliber_val); ?></option><?php
                        endif;
                        foreach ( $hunting_calibers as $cal ):
                        ?><option value="<?php echo esc_attr((string)$cal); ?>"<?php echo selected($caliber_val, (string)$cal, false); ?>><?php echo esc_html((string)$cal); ?></option><?php
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="va-form-group va-core-product-year">
                    <label>Gyártási év</label>
                    <div class="va-year-input-wrap">
                        <input type="text" name="year" id="va-year-input" class="va-input" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="pl. 2019" value="<?php echo esc_attr((string)($edit_meta['year'] ?? '')); ?>" readonly>
                        <button type="button" class="va-year-open-btn" id="va-year-open">Év választása</button>
                    </div>
                </div>
                <div class="va-form-group va-cat-rule-field" data-categories="egyeb-fegyverek" style="grid-column:1 / -1; margin-top:10px;">
                    <label>Fegyverengedély szükséges a vásárláshoz</label>
                    <label class="va-check-label"><input type="checkbox" name="other_weapon_permit_required" value="1"<?php echo ((string)($edit_meta['other_weapon_permit_required'] ?? '') === '1') ? ' checked' : ''; ?>> Igen</label>
                </div>
                <div class="va-form-group va-cat-rule-field" data-categories="golyos-puska,soretes-puska,vegyescsovu-puska,maroklofegyver,hatastalanitott,loszer-tolteny,ij-szamszerij-fuvocso,ij,ijak,szamszerij-nyilpuska,ijvesszo,fuvocso,nyilpisztoly,kiegeszitok-ij" style="grid-column:1 / -1; margin-top:10px;">
                    <label class="va-check-label"><input type="checkbox" name="license_req" value="1"<?php echo (($edit_meta['license_req'] ?? '') === '1') ? ' checked' : ''; ?>> Fegyverengedély szükséges a vásárláshoz</label>
                </div>
            </div>
            <?php
            $rifle_saved = static function( string $key ) use ( $edit_meta ) {
                return is_scalar( $edit_meta[ $key ] ?? '' ) ? (string) ( $edit_meta[ $key ] ?? '' ) : '';
            };
            $rifle_caliber_saved = array_filter( array_map( 'trim', explode( ',', $rifle_saved( 'rifle_caliber' ) ) ) );
            $rifle_usage_saved = array_filter( array_map( 'trim', explode( ',', $rifle_saved( 'rifle_usage' ) ) ) );
            $rifle_accessories_saved = array_filter( array_map( 'trim', explode( ',', $rifle_saved( 'rifle_accessories' ) ) ) );
            $rifle_special_saved = array_filter( array_map( 'trim', explode( ',', $rifle_saved( 'rifle_special_build' ) ) ) );
            ?>
            <div class="va-cat-rule-field va-rifle-fields-grid" data-categories="golyos-puska" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group"><label>Típus</label><?php $v = $rifle_saved( 'rifle_type' ); ?><select name="rifle_type" id="va-rifle-type" class="va-select"><option value="">- Válasszon -</option><option value="ismetlo-bolt-action"<?php selected( $v, 'ismetlo-bolt-action' ); ?>>Ismétlő (bolt action)</option><option value="felautomata"<?php selected( $v, 'felautomata' ); ?>>Félautomata</option><option value="egy-lovetu-kipplauf"<?php selected( $v, 'egy-lovetu-kipplauf' ); ?>>Egy lövetű (kipplauf)</option><option value="break-action-golyos"<?php selected( $v, 'break-action-golyos' ); ?>>Break action golyós</option><option value="kombinalt-golyos"<?php selected( $v, 'kombinalt-golyos' ); ?>>Kombinált golyós</option><option value="precizios-puska"<?php selected( $v, 'precizios-puska' ); ?>>Precíziós puska</option><option value="hegyi-puska"<?php selected( $v, 'hegyi-puska' ); ?>>Hegyi puska</option><option value="varmint-puska"<?php selected( $v, 'varmint-puska' ); ?>>Varmint puska</option><option value="taktikai-sport"<?php selected( $v, 'taktikai-sport' ); ?>>Taktikai / sport</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Állapot</label><?php $v = $rifle_saved( 'rifle_condition' ); ?><select name="rifle_condition" class="va-select"><option value="">- Válasszon -</option><option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option><option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option><option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option><option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option><option value="restauralt"<?php selected( $v, 'restauralt' ); ?>>Restaurált</option></select></div>
                    <div class="va-form-group"><label>Csőhossz (cm)</label><input type="text" name="rifle_barrel_length_cm" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_barrel_length_cm' ) ); ?>" placeholder="pl. 61"></div>
                    <div class="va-form-group"><label>Teljes hossz (cm)</label><input type="text" name="rifle_total_length_cm" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_total_length_cm' ) ); ?>" placeholder="pl. 112"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Golyós kaliber</label><select name="rifle_caliber[]" class="va-select" multiple data-placeholder="Válassz kalibert"><option value="17-hmr"<?php echo in_array( '17-hmr', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.17 HMR</option><option value="22-lr"<?php echo in_array( '22-lr', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.22 LR</option><option value="22-wmr"<?php echo in_array( '22-wmr', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.22 WMR</option><option value="222-rem"<?php echo in_array( '222-rem', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.222 Rem</option><option value="223-rem"<?php echo in_array( '223-rem', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.223 Rem</option><option value="243-win"<?php echo in_array( '243-win', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.243 Win</option><option value="6-5-creedmoor"<?php echo in_array( '6-5-creedmoor', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>6.5 Creedmoor</option><option value="6-5x55-se"<?php echo in_array( '6-5x55-se', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>6.5x55 SE</option><option value="7x57"<?php echo in_array( '7x57', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>7x57</option><option value="7x64"<?php echo in_array( '7x64', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>7x64</option><option value="7x65r"<?php echo in_array( '7x65r', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>7x65R</option><option value="308-win"<?php echo in_array( '308-win', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.308 Win</option><option value="30-06-springfield"<?php echo in_array( '30-06-springfield', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.30-06 Springfield</option><option value="300-win-mag"<?php echo in_array( '300-win-mag', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.300 Win Mag</option><option value="338-lapua-magnum"<?php echo in_array( '338-lapua-magnum', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>.338 Lapua Magnum</option><option value="egyeb"<?php echo in_array( 'egyeb', $rifle_caliber_saved, true ) ? ' selected' : ''; ?>>Egyéb</option></select></div>

                    <div class="va-form-group"><label>Cső anyaga</label><?php $v = $rifle_saved( 'rifle_barrel_material' ); ?><select name="rifle_barrel_material" class="va-select"><option value="">- Válasszon -</option><option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option><option value="krom-molibden"<?php selected( $v, 'krom-molibden' ); ?>>Króm-molibdén</option><option value="rozsdamentes"<?php selected( $v, 'rozsdamentes' ); ?>>Rozsdamentes</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Huzagolás típusa</label><?php $v = $rifle_saved( 'rifle_rifling_type' ); ?><select name="rifle_rifling_type" class="va-select"><option value="">- Válasszon -</option><option value="standard"<?php selected( $v, 'standard' ); ?>>Standard</option><option value="gyors"<?php selected( $v, 'gyors' ); ?>>Gyors</option><option value="precizios"<?php selected( $v, 'precizios' ); ?>>Precíziós</option></select></div>
                    <div class="va-form-group"><label>Cső állapota</label><?php $v = $rifle_saved( 'rifle_barrel_condition' ); ?><select name="rifle_barrel_condition" class="va-select"><option value="">- Válasszon -</option><option value="kivalo"<?php selected( $v, 'kivalo' ); ?>>Kiváló</option><option value="jo"<?php selected( $v, 'jo' ); ?>>Jó</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option></select></div>
                    <div class="va-form-group"><label>Zárszerkezet</label><?php $v = $rifle_saved( 'rifle_action_type' ); ?><select name="rifle_action_type" class="va-select"><option value="">- Válasszon -</option><option value="forgo-tolozar"<?php selected( $v, 'forgo-tolozar' ); ?>>Forgó tolózár</option><option value="billenocsoves"<?php selected( $v, 'billenocsoves' ); ?>>Billenőcsöves</option><option value="felautomata-rendszer"<?php selected( $v, 'felautomata-rendszer' ); ?>>Félautomata rendszer</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>

                    <div class="va-form-group"><label>Elsütés</label><?php $v = $rifle_saved( 'rifle_trigger_type' ); ?><select name="rifle_trigger_type" class="va-select"><option value="">- Válasszon -</option><option value="egybillentyus"<?php selected( $v, 'egybillentyus' ); ?>>Egybillentyűs</option><option value="ketbillentyus"<?php selected( $v, 'ketbillentyus' ); ?>>Kétbillentyűs</option><option value="allithato-sutes"<?php selected( $v, 'allithato-sutes' ); ?>>Állítható sütés</option></select></div>
                    <div class="va-form-group"><label>Biztosíték típusa</label><?php $v = $rifle_saved( 'rifle_safety_type' ); ?><select name="rifle_safety_type" class="va-select"><option value="">- Válasszon -</option><option value="kezi"<?php selected( $v, 'kezi' ); ?>>Kézi</option><option value="csuszo"<?php selected( $v, 'csuszo' ); ?>>Csúszó</option><option value="tang"<?php selected( $v, 'tang' ); ?>>Tang</option><option value="kombinalt"<?php selected( $v, 'kombinalt' ); ?>>Kombinált</option></select></div>
                    <div class="va-form-group"><label>Ágyazás</label><?php $v = $rifle_saved( 'rifle_stock_material' ); ?><select name="rifle_stock_material" class="va-select"><option value="">- Válasszon -</option><option value="fa"<?php selected( $v, 'fa' ); ?>>Fa (dió, bükk)</option><option value="laminalt"<?php selected( $v, 'laminalt' ); ?>>Laminált</option><option value="muanyag"<?php selected( $v, 'muanyag' ); ?>>Műanyag</option><option value="szintetikus"<?php selected( $v, 'szintetikus' ); ?>>Szintetikus</option><option value="karbon"<?php selected( $v, 'karbon' ); ?>>Karbon</option><option value="gumirozott"<?php selected( $v, 'gumirozott' ); ?>>Gumírozott</option></select></div>
                    <div class="va-form-group"><label>Súly (kg)</label><input type="text" name="rifle_weight_kg" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_weight_kg' ) ); ?>" placeholder="pl. 3.2"></div>

                    <div class="va-form-group"><label>Sín típusa</label><?php $v = $rifle_saved( 'rifle_optic_rail_type' ); ?><select name="rifle_optic_rail_type" class="va-select"><option value="">- Válasszon -</option><option value="nincs"<?php selected( $v, 'nincs' ); ?>>Nincs</option><option value="11mm"<?php selected( $v, '11mm' ); ?>>11 mm</option><option value="weaver"<?php selected( $v, 'weaver' ); ?>>Weaver</option><option value="picatinny"<?php selected( $v, 'picatinny' ); ?>>Picatinny</option><option value="kombinalt"<?php selected( $v, 'kombinalt' ); ?>>Kombinált</option></select></div>
                    <div class="va-form-group"><label>Optika kompatibilitás</label><?php $v = $rifle_saved( 'rifle_optic_compatibility' ); ?><select name="rifle_optic_compatibility" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Zero visszaállítás</label><?php $v = $rifle_saved( 'rifle_zero_return' ); ?><select name="rifle_zero_return" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Cső / tok kapcsolat</label><?php $v = $rifle_saved( 'rifle_barrel_mount_type' ); ?><select name="rifle_barrel_mount_type" class="va-select"><option value="">- Válasszon -</option><option value="fix"<?php selected( $v, 'fix' ); ?>>Fix</option><option value="cserelheto-cso"<?php selected( $v, 'cserelheto-cso' ); ?>>Cserélhető cső</option><option value="modularis-rendszer"<?php selected( $v, 'modularis-rendszer' ); ?>>Moduláris rendszer</option></select></div>

                    <div class="va-form-group va-rifle-dynamic-group" data-rifle-types="precizios-puska" style="display:none;"><label>MOA érték</label><input type="text" name="rifle_accuracy_moa" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_accuracy_moa' ) ); ?>" placeholder="pl. 0.8"></div>
                    <div class="va-form-group va-rifle-dynamic-group" data-rifle-types="precizios-puska" style="display:none;"><label>Effektív lőtáv (m)</label><input type="text" name="rifle_effective_range_m" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_effective_range_m' ) ); ?>" placeholder="pl. 600"></div>
                    <div class="va-form-group"><label>Belövési távolság (m)</label><input type="text" name="rifle_zero_distance_m" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_zero_distance_m' ) ); ?>" placeholder="pl. 100"></div>
                    <div class="va-form-group"><label>Gyári szóráskép</label><input type="text" name="rifle_factory_group" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_factory_group' ) ); ?>" placeholder="pl. 25 mm / 100 m"></div>

                    <div class="va-form-group va-rifle-dynamic-group" data-rifle-types="hegyi-puska" style="display:none;"><label>Súly optimalizálás</label><?php $v = $rifle_saved( 'rifle_mountain_weight_optimized' ); ?><select name="rifle_mountain_weight_optimized" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-rifle-dynamic-group" data-rifle-types="felautomata" style="display:none;"><label>Tárkapacitás (db)</label><input type="text" name="rifle_magazine_capacity" class="va-input" value="<?php echo esc_attr( $rifle_saved( 'rifle_magazine_capacity' ) ); ?>" placeholder="pl. 5"></div>
                    <div class="va-form-group"><label>Stabilitás</label><?php $v = $rifle_saved( 'rifle_stability_level' ); ?><select name="rifle_stability_level" class="va-select"><option value="">- Válasszon -</option><option value="alacsony"<?php selected( $v, 'alacsony' ); ?>>Alacsony</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="magas"<?php selected( $v, 'magas' ); ?>>Magas</option></select></div>
                    <div class="va-form-group"><label>Visszarúgás szint</label><?php $v = $rifle_saved( 'rifle_recoil_level' ); ?><select name="rifle_recoil_level" class="va-select"><option value="">- Válasszon -</option><option value="alacsony"<?php selected( $v, 'alacsony' ); ?>>Alacsony</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="eros"<?php selected( $v, 'eros' ); ?>>Erős</option></select></div>
                    <div class="va-form-group"><label>Temperamentum</label><?php $v = $rifle_saved( 'rifle_temperament' ); ?><select name="rifle_temperament" class="va-select"><option value="">- Válasszon -</option><option value="lagy-visszarugas"<?php selected( $v, 'lagy-visszarugas' ); ?>>Lágy visszarúgás</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="eros"<?php selected( $v, 'eros' ); ?>>Erős</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="rifle_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot"><option value="tar"<?php echo in_array( 'tar', $rifle_accessories_saved, true ) ? ' selected' : ''; ?>>Tár</option><option value="fegyverszij"<?php echo in_array( 'fegyverszij', $rifle_accessories_saved, true ) ? ' selected' : ''; ?>>Fegyverszíj</option><option value="tok"<?php echo in_array( 'tok', $rifle_accessories_saved, true ) ? ' selected' : ''; ?>>Tok</option><option value="tisztitokeszlet"<?php echo in_array( 'tisztitokeszlet', $rifle_accessories_saved, true ) ? ' selected' : ''; ?>>Tisztítókészlet</option><option value="bipod"<?php echo in_array( 'bipod', $rifle_accessories_saved, true ) ? ' selected' : ''; ?>>Bipod</option><option value="szerulek"<?php echo in_array( 'szerulek', $rifle_accessories_saved, true ) ? ' selected' : ''; ?>>Szerelék</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Felhasználás</label><select name="rifle_usage[]" class="va-select" multiple data-placeholder="Válassz felhasználást"><option value="nagyvad-vadaszat"<?php echo in_array( 'nagyvad-vadaszat', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Nagyvad vadászat</option><option value="aprovad"<?php echo in_array( 'aprovad', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Apróvad</option><option value="hegyi-vadaszat"<?php echo in_array( 'hegyi-vadaszat', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Hegyi vadászat</option><option value="lesvadaszat"<?php echo in_array( 'lesvadaszat', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Lesvadászat</option><option value="cserkeles"<?php echo in_array( 'cserkeles', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Cserkelés</option><option value="sportloveszet"<?php echo in_array( 'sportloveszet', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Sportlövészet</option><option value="precizios-loveszet"<?php echo in_array( 'precizios-loveszet', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Precíziós lövészet</option><option value="varmint"<?php echo in_array( 'varmint', $rifle_usage_saved, true ) ? ' selected' : ''; ?>>Varmint</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Különleges kivitel</label><select name="rifle_special_build[]" class="va-select" multiple data-placeholder="Válassz különleges kivitelt"><option value="konnyitett-hegyi-kivitel"<?php echo in_array( 'konnyitett-hegyi-kivitel', $rifle_special_saved, true ) ? ' selected' : ''; ?>>Könnyített hegyi kivitel</option><option value="long-range-setup"<?php echo in_array( 'long-range-setup', $rifle_special_saved, true ) ? ' selected' : ''; ?>>Long range setup</option><option value="taktikai-platform"<?php echo in_array( 'taktikai-platform', $rifle_special_saved, true ) ? ' selected' : ''; ?>>Taktikai platform</option><option value="klasszikus-vadasz-puska"<?php echo in_array( 'klasszikus-vadasz-puska', $rifle_special_saved, true ) ? ' selected' : ''; ?>>Klasszikus vadász puska</option></select></div>

                    <div class="va-form-group"><label>Ütésbiztos rendszer</label><?php $v = $rifle_saved( 'rifle_impact_safe' ); ?><select name="rifle_impact_safe" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Zárt rendszer</label><?php $v = $rifle_saved( 'rifle_closed_system' ); ?><select name="rifle_closed_system" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                </div>
            </div>

            <?php
            $mixed_saved = static function( string $key ) use ( $edit_meta ) {
                return is_scalar( $edit_meta[ $key ] ?? '' ) ? (string) ( $edit_meta[ $key ] ?? '' ) : '';
            };
            $mixed_usage_saved = array_filter( array_map( 'trim', explode( ',', $mixed_saved( 'mixed_usage' ) ) ) );
            $mixed_accessories_saved = array_filter( array_map( 'trim', explode( ',', $mixed_saved( 'mixed_accessories' ) ) ) );
            ?>
            <div class="va-cat-rule-field va-mixed-fields-grid" data-categories="vegyescsovu-puska" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group"><label>Típus</label><?php $v = $mixed_saved( 'mixed_type' ); ?><select name="mixed_type" id="va-mixed-type" class="va-select"><option value="">- Válasszon -</option><option value="vegyescsovu"<?php selected( $v, 'vegyescsovu' ); ?>>Vegyescsövű (drilling / kombinált)</option><option value="bock"<?php selected( $v, 'bock' ); ?>>Bock (golyó + sörét)</option><option value="over-under"<?php selected( $v, 'over-under' ); ?>>Over-under kombinált</option><option value="side-by-side"<?php selected( $v, 'side-by-side' ); ?>>Side-by-side kombinált</option><option value="drilling"<?php selected( $v, 'drilling' ); ?>>Drilling (3 csöves)</option><option value="kipplauf-kombinalt"<?php selected( $v, 'kipplauf-kombinalt' ); ?>>Kipplauf kombinált</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Állapot</label><?php $v = $mixed_saved( 'mixed_condition' ); ?><select name="mixed_condition" class="va-select"><option value="">- Válasszon -</option><option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option><option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option><option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option><option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option><option value="restauralt"<?php selected( $v, 'restauralt' ); ?>>Restaurált</option></select></div>
                    <div class="va-form-group"><label>Csövek száma</label><?php $v = $mixed_saved( 'mixed_barrel_count' ); ?><select name="mixed_barrel_count" class="va-select"><option value="">- Válasszon -</option><option value="2"<?php selected( $v, '2' ); ?>>2</option><option value="3"<?php selected( $v, '3' ); ?>>3</option></select></div>
                    <div class="va-form-group"><label>Felhúzás / rendszer</label><?php $v = $mixed_saved( 'mixed_action_system' ); ?><select name="mixed_action_system" class="va-select"><option value="">- Válasszon -</option><option value="kezi"<?php selected( $v, 'kezi' ); ?>>Kézi</option><option value="automata"<?php selected( $v, 'automata' ); ?>>Automata</option><option value="kipplauf"<?php selected( $v, 'kipplauf' ); ?>>Kipplauf</option><option value="billenocsoves"<?php selected( $v, 'billenocsoves' ); ?>>Billenőcsöves</option></select></div>

                    <div class="va-form-group"><label>Golyós cső</label><?php $v = $mixed_saved( 'mixed_has_rifle' ); ?><select name="mixed_has_rifle" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Golyós kaliber</label><?php $v = $mixed_saved( 'mixed_rifle_caliber' ); ?><select name="mixed_rifle_caliber" class="va-select"><option value="">- Válasszon -</option><option value=".222-rem"<?php selected( $v, '.222-rem' ); ?>>.222 Rem</option><option value=".223-rem"<?php selected( $v, '.223-rem' ); ?>>.223 Rem</option><option value=".243-win"<?php selected( $v, '.243-win' ); ?>>.243 Win</option><option value="6.5x55"<?php selected( $v, '6.5x55' ); ?>>6.5x55</option><option value="7x57r"<?php selected( $v, '7x57r' ); ?>>7x57R</option><option value="7x65r"<?php selected( $v, '7x65r' ); ?>>7x65R</option><option value=".30-06"<?php selected( $v, '.30-06' ); ?>>.30-06</option><option value=".308-win"<?php selected( $v, '.308-win' ); ?>>.308 Win</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Sörétes cső</label><?php $v = $mixed_saved( 'mixed_has_shotgun' ); ?><select name="mixed_has_shotgun" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Sörétes kaliber</label><?php $v = $mixed_saved( 'mixed_shotgun_caliber' ); ?><select name="mixed_shotgun_caliber" class="va-select"><option value="">- Válasszon -</option><option value="12-70"<?php selected( $v, '12-70' ); ?>>12/70</option><option value="12-76"<?php selected( $v, '12-76' ); ?>>12/76</option><option value="16-70"<?php selected( $v, '16-70' ); ?>>16/70</option><option value="20-76"<?php selected( $v, '20-76' ); ?>>20/76</option><option value="28"<?php selected( $v, '28' ); ?>>28</option><option value=".410"<?php selected( $v, '.410' ); ?>>.410</option></select></div>

                    <div class="va-form-group"><label>Elsütés</label><?php $v = $mixed_saved( 'mixed_trigger_type' ); ?><select name="mixed_trigger_type" class="va-select"><option value="">- Válasszon -</option><option value="egybillentyus"<?php selected( $v, 'egybillentyus' ); ?>>Egybillentyűs</option><option value="ketbillentyus"<?php selected( $v, 'ketbillentyus' ); ?>>Kétbillentyűs</option><option value="kombinalt"<?php selected( $v, 'kombinalt' ); ?>>Kombinált</option></select></div>
                    <div class="va-form-group"><label>Zár</label><?php $v = $mixed_saved( 'mixed_lock_type' ); ?><select name="mixed_lock_type" class="va-select"><option value="">- Válasszon -</option><option value="torocsapos"<?php selected( $v, 'torocsapos' ); ?>>Törőcsapos</option><option value="felso-zarrendszer"<?php selected( $v, 'felso-zarrendszer' ); ?>>Felső zárrendszer</option><option value="also-zar"<?php selected( $v, 'also-zar' ); ?>>Alsó zár</option><option value="kombinalt"<?php selected( $v, 'kombinalt' ); ?>>Kombinált</option></select></div>
                    <div class="va-form-group"><label>Alap irányzék</label><?php $v = $mixed_saved( 'mixed_sight_type' ); ?><select name="mixed_sight_type" class="va-select"><option value="">- Válasszon -</option><option value="nyilt"<?php selected( $v, 'nyilt' ); ?>>Nyílt</option><option value="allithato"<?php selected( $v, 'allithato' ); ?>>Állítható</option><option value="fiber-optic"<?php selected( $v, 'fiber-optic' ); ?>>Fiber optic</option></select></div>
                    <div class="va-form-group"><label>Optika sín</label><?php $v = $mixed_saved( 'mixed_optic_rail' ); ?><select name="mixed_optic_rail" class="va-select"><option value="">- Válasszon -</option><option value="11mm"<?php selected( $v, '11mm' ); ?>>11 mm</option><option value="weaver"<?php selected( $v, 'weaver' ); ?>>Weaver</option><option value="picatinny"<?php selected( $v, 'picatinny' ); ?>>Picatinny</option><option value="nincs"<?php selected( $v, 'nincs' ); ?>>Nincs</option></select></div>
                    <div class="va-form-group"><label>Optika kompatibilitás</label><?php $v = $mixed_saved( 'mixed_optic_compatible' ); ?><select name="mixed_optic_compatible" id="va-mixed-optic-compatible" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-mixed-dynamic-group" data-mixed-optic="yes" style="display:none;"><label>Szerelék típusa</label><input type="text" name="mixed_optic_mount_type" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_optic_mount_type' ) ); ?>" placeholder="pl. gyorsoldású"></div>

                    <div class="va-form-group"><label>Csőhossz (cm)</label><input type="text" name="mixed_barrel_length_cm" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_barrel_length_cm' ) ); ?>" placeholder="pl. 60"></div>
                    <div class="va-form-group"><label>Cső anyaga</label><?php $v = $mixed_saved( 'mixed_barrel_material' ); ?><select name="mixed_barrel_material" class="va-select"><option value="">- Válasszon -</option><option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option><option value="krom-molibden"<?php selected( $v, 'krom-molibden' ); ?>>Króm-molibdén</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Cső összetartás</label><?php $v = $mixed_saved( 'mixed_barrel_regulated' ); ?><select name="mixed_barrel_regulated" class="va-select"><option value="">- Válasszon -</option><option value="regulalt"<?php selected( $v, 'regulalt' ); ?>>Regulált</option><option value="nem-regulalt"<?php selected( $v, 'nem-regulalt' ); ?>>Nem regulált</option></select></div>
                    <div class="va-form-group"><label>Anyag</label><?php $v = $mixed_saved( 'mixed_material' ); ?><select name="mixed_material" class="va-select"><option value="">- Válasszon -</option><option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option><option value="krom-molibden"<?php selected( $v, 'krom-molibden' ); ?>>Króm-molibdén</option><option value="fa-gyazas"<?php selected( $v, 'fa-gyazas' ); ?>>Fa ágyazás</option><option value="muanyag"<?php selected( $v, 'muanyag' ); ?>>Műanyag</option><option value="kompozit"<?php selected( $v, 'kompozit' ); ?>>Kompozit</option></select></div>

                    <div class="va-form-group"><label>Ágyazás</label><?php $v = $mixed_saved( 'mixed_stock_material' ); ?><select name="mixed_stock_material" class="va-select"><option value="">- Válasszon -</option><option value="diofa"<?php selected( $v, 'diofa' ); ?>>Diófa</option><option value="bukk"<?php selected( $v, 'bukk' ); ?>>Bükk</option><option value="laminalt"<?php selected( $v, 'laminalt' ); ?>>Laminált</option><option value="muanyag"<?php selected( $v, 'muanyag' ); ?>>Műanyag</option><option value="szintetikus"<?php selected( $v, 'szintetikus' ); ?>>Szintetikus</option><option value="gumirozott"<?php selected( $v, 'gumirozott' ); ?>>Gumírozott</option></select></div>
                    <div class="va-form-group"><label>Súly (kg)</label><input type="text" name="mixed_weight_kg" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_weight_kg' ) ); ?>" placeholder="pl. 3.4"></div>
                    <div class="va-form-group"><label>Teljes hossz (cm)</label><input type="text" name="mixed_total_length_cm" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_total_length_cm' ) ); ?>" placeholder="pl. 105"></div>
                    <div class="va-form-group"><label>Biztosíték típusa</label><?php $v = $mixed_saved( 'mixed_safety_type' ); ?><select name="mixed_safety_type" class="va-select"><option value="">- Válasszon -</option><option value="kezi"<?php selected( $v, 'kezi' ); ?>>Kézi</option><option value="automata"<?php selected( $v, 'automata' ); ?>>Automata</option><option value="tang"<?php selected( $v, 'tang' ); ?>>Tang biztonság</option></select></div>
                    <div class="va-form-group"><label>Cső elrendezés</label><?php $v = $mixed_saved( 'mixed_barrel_layout' ); ?><select name="mixed_barrel_layout" class="va-select"><option value="">- Válasszon -</option><option value="golyo-felul-soret-alul"<?php selected( $v, 'golyo-felul-soret-alul' ); ?>>Golyó felül / sörét alul</option><option value="soret-felul-golyo-alul"<?php selected( $v, 'soret-felul-golyo-alul' ); ?>>Sörét felül / golyó alul</option><option value="oldalt"<?php selected( $v, 'oldalt' ); ?>>Oldalt</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Használat</label><select name="mixed_usage[]" id="va-mixed-usage" class="va-select" multiple data-placeholder="Válassz felhasználást"><option value="nagyvad"<?php echo in_array( 'nagyvad', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Nagyvad</option><option value="aprovad"<?php echo in_array( 'aprovad', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Apróvad</option><option value="vegyes-vadaszat"<?php echo in_array( 'vegyes-vadaszat', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Vegyes vadászat</option><option value="hegyi-vadaszat"<?php echo in_array( 'hegyi-vadaszat', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Hegyi vadászat</option><option value="erdei-vadaszat"<?php echo in_array( 'erdei-vadaszat', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Erdei vadászat</option><option value="hajtas"<?php echo in_array( 'hajtas', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Hajtás</option><option value="lesvadaszat"<?php echo in_array( 'lesvadaszat', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Lesvadászat</option><option value="univerzalis"<?php echo in_array( 'univerzalis', $mixed_usage_saved, true ) ? ' selected' : ''; ?>>Univerzális</option></select></div>
                    <div class="va-form-group va-mixed-dynamic-group" data-mixed-usage="hegyi-vadaszat" style="display:none;"><label>Súly optimalizálás</label><?php $v = $mixed_saved( 'mixed_mountain_weight_optimized' ); ?><select name="mixed_mountain_weight_optimized" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Univerzális vadászfegyver jelölés</label><?php $v = $mixed_saved( 'mixed_universal_label' ); ?><select name="mixed_universal_label" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>

                    <div class="va-form-group"><label>Golyós szórás (MOA)</label><input type="text" name="mixed_rifle_moa" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_rifle_moa' ) ); ?>" placeholder="pl. 1.2"></div>
                    <div class="va-form-group"><label>Sörétes szóráskép</label><input type="text" name="mixed_shot_pattern" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_shot_pattern' ) ); ?>" placeholder="pl. 75% 35m-en"></div>
                    <div class="va-form-group"><label>Belövési távolság (m)</label><input type="text" name="mixed_zero_distance_m" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_zero_distance_m' ) ); ?>" placeholder="pl. 100"></div>
                    <div class="va-form-group"><label>Cső belső</label><input type="text" name="mixed_barrel_internal" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_barrel_internal' ) ); ?>" placeholder="pl. tükörfényes"></div>
                    <div class="va-form-group"><label>Gravírozás</label><input type="text" name="mixed_engraving" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_engraving' ) ); ?>"></div>
                    <div class="va-form-group"><label>Gyártási jelölés</label><input type="text" name="mixed_factory_marking" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_factory_marking' ) ); ?>"></div>

                    <div class="va-form-group va-mixed-dynamic-group" data-mixed-type="drilling" style="display:none;"><label>1. cső kaliber</label><input type="text" name="mixed_drilling_barrel1_caliber" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_drilling_barrel1_caliber' ) ); ?>"></div>
                    <div class="va-form-group va-mixed-dynamic-group" data-mixed-type="drilling" style="display:none;"><label>Felső cső szerepe</label><input type="text" name="mixed_drilling_top_barrel_role" class="va-input" value="<?php echo esc_attr( $mixed_saved( 'mixed_drilling_top_barrel_role' ) ); ?>" placeholder="pl. golyós"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="mixed_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot"><option value="eredeti-doboz"<?php echo in_array( 'eredeti-doboz', $mixed_accessories_saved, true ) ? ' selected' : ''; ?>>Eredeti doboz</option><option value="puskaszij"<?php echo in_array( 'puskaszij', $mixed_accessories_saved, true ) ? ' selected' : ''; ?>>Puskaszíj</option><option value="tok"<?php echo in_array( 'tok', $mixed_accessories_saved, true ) ? ' selected' : ''; ?>>Tok</option><option value="tisztitokeszlet"<?php echo in_array( 'tisztitokeszlet', $mixed_accessories_saved, true ) ? ' selected' : ''; ?>>Tisztítókészlet</option><option value="csokulcs"<?php echo in_array( 'csokulcs', $mixed_accessories_saved, true ) ? ' selected' : ''; ?>>Csőkulcs</option><option value="iranyzek-tartozek"<?php echo in_array( 'iranyzek-tartozek', $mixed_accessories_saved, true ) ? ' selected' : ''; ?>>Irányzék tartozék</option></select></div>
                </div>
            </div>

            <?php
            $marok_saved = static function( string $key ) use ( $edit_meta ) {
                return is_scalar( $edit_meta[ $key ] ?? '' ) ? (string) ( $edit_meta[ $key ] ?? '' ) : '';
            };
            $marok_accessories_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_accessories' ) ) ) );
            $marok_usage_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_usage' ) ) ) );
            $marok_transfer_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_transfer_methods' ) ) ) );
            $marok_required_media_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_required_media' ) ) ) );
            $marok_recommended_media_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_recommended_media' ) ) ) );
            $marok_sport_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_sport_categories' ) ) ) );
            $marok_moderation_saved = array_filter( array_map( 'trim', explode( ',', $marok_saved( 'marok_moderation_flags' ) ) ) );
            ?>
            <div class="va-cat-rule-field va-handgun-fields-grid" data-categories="maroklofegyver" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group"><label>Típus</label><?php $v = $marok_saved( 'marok_type' ); ?><select name="marok_type" id="va-marok-type" class="va-select"><option value="">- Válasszon -</option><option value="pisztoly"<?php selected( $v, 'pisztoly' ); ?>>Pisztoly</option><option value="revolver"<?php selected( $v, 'revolver' ); ?>>Revolver</option><option value="sportpisztoly"<?php selected( $v, 'sportpisztoly' ); ?>>Sportpisztoly</option><option value="onvedelmi"<?php selected( $v, 'onvedelmi' ); ?>>Önvédelmi</option><option value="szolgalati"<?php selected( $v, 'szolgalati' ); ?>>Szolgálati</option><option value="verseny"<?php selected( $v, 'verseny' ); ?>>Verseny</option><option value="tortenelmi"<?php selected( $v, 'tortenelmi' ); ?>>Történelmi</option><option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option><option value="muzealis"<?php selected( $v, 'muzealis' ); ?>>Muzeális</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Állapot</label><?php $v = $marok_saved( 'marok_condition' ); ?><select name="marok_condition" class="va-select"><option value="">- Válasszon -</option><option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option><option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option><option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option><option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option><option value="restauralt"<?php selected( $v, 'restauralt' ); ?>>Restaurált</option></select></div>
                    <div class="va-form-group"><label>Gyártási év</label><input type="text" name="marok_manufacture_year" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_manufacture_year' ) ); ?>" placeholder="pl. 2018"></div>
                    <div class="va-form-group"><label>Származási ország</label><input type="text" name="marok_origin_country" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_origin_country' ) ); ?>" placeholder="pl. Ausztria"></div>
                    <div class="va-form-group"><label>Működési rendszer</label><?php $v = $marok_saved( 'marok_action_type' ); ?><select name="marok_action_type" class="va-select"><option value="">- Válasszon -</option><option value="dao"<?php selected( $v, 'dao' ); ?>>DAO</option><option value="sa"<?php selected( $v, 'sa' ); ?>>SA</option><option value="da-sa"<?php selected( $v, 'da-sa' ); ?>>DA/SA</option><option value="striker-fired"<?php selected( $v, 'striker-fired' ); ?>>Striker fired</option><option value="single-action"<?php selected( $v, 'single-action' ); ?>>Single action</option><option value="double-action"<?php selected( $v, 'double-action' ); ?>>Double action</option></select></div>
                    <div class="va-form-group"><label>Tárkapacitás (db)</label><input type="text" name="marok_magazine_capacity" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_magazine_capacity' ) ); ?>" placeholder="pl. 17"></div>
                    <div class="va-form-group va-marok-dynamic-group va-marok-revolver-only" style="display:none;"><label>Dob kapacitás (db)</label><input type="text" name="marok_cylinder_capacity" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_cylinder_capacity' ) ); ?>" placeholder="pl. 6"></div>
                    <div class="va-form-group va-marok-dynamic-group va-marok-revolver-only" style="display:none;"><label>Revolver működés</label><?php $v = $marok_saved( 'marok_revolver_action' ); ?><select name="marok_revolver_action" class="va-select"><option value="">- Válasszon -</option><option value="single-action"<?php selected( $v, 'single-action' ); ?>>Single action</option><option value="double-action"<?php selected( $v, 'double-action' ); ?>>Double action</option></select></div>
                    <div class="va-form-group"><label>Csőhossz (mm)</label><input type="text" name="marok_barrel_length_mm" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_barrel_length_mm' ) ); ?>" placeholder="pl. 114"></div>
                    <div class="va-form-group"><label>Teljes hossz (mm)</label><input type="text" name="marok_total_length_mm" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_total_length_mm' ) ); ?>" placeholder="pl. 204"></div>
                    <div class="va-form-group"><label>Súly (g)</label><input type="text" name="marok_weight_g" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_weight_g' ) ); ?>" placeholder="pl. 690"></div>
                    <div class="va-form-group"><label>Keret anyaga</label><?php $v = $marok_saved( 'marok_frame_material' ); ?><select name="marok_frame_material" class="va-select"><option value="">- Válasszon -</option><option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option><option value="aluminium"<?php selected( $v, 'aluminium' ); ?>>Alumínium</option><option value="polimer"<?php selected( $v, 'polimer' ); ?>>Polimer</option><option value="rozsdamentes"<?php selected( $v, 'rozsdamentes' ); ?>>Rozsdamentes</option></select></div>
                    <div class="va-form-group"><label>Szán anyaga</label><?php $v = $marok_saved( 'marok_slide_material' ); ?><select name="marok_slide_material" class="va-select"><option value="">- Válasszon -</option><option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option><option value="inox"<?php selected( $v, 'inox' ); ?>>Inox</option><option value="aluminium"<?php selected( $v, 'aluminium' ); ?>>Alumínium</option></select></div>
                    <div class="va-form-group"><label>Irányzék típusa</label><?php $v = $marok_saved( 'marok_sight_type' ); ?><select name="marok_sight_type" class="va-select"><option value="">- Válasszon -</option><option value="fix"<?php selected( $v, 'fix' ); ?>>Fix</option><option value="allithato"<?php selected( $v, 'allithato' ); ?>>Állítható</option><option value="fiber-optic"<?php selected( $v, 'fiber-optic' ); ?>>Fiber optic</option><option value="night-sight"<?php selected( $v, 'night-sight' ); ?>>Night sight</option><option value="red-dot-ready"<?php selected( $v, 'red-dot-ready' ); ?>>Red dot ready</option></select></div>
                    <div class="va-form-group"><label>Optika előkészítés</label><?php $v = $marok_saved( 'marok_optic_ready' ); ?><select name="marok_optic_ready" id="va-marok-optic-ready" class="va-select"><option value="">- Válasszon -</option><option value="nincs"<?php selected( $v, 'nincs' ); ?>>Nincs</option><option value="mos"<?php selected( $v, 'mos' ); ?>>MOS</option><option value="rmr"<?php selected( $v, 'rmr' ); ?>>RMR</option><option value="docter"<?php selected( $v, 'docter' ); ?>>Docter</option><option value="picatinny"<?php selected( $v, 'picatinny' ); ?>>Picatinny</option></select></div>
                    <div class="va-form-group va-marok-dynamic-group va-marok-optic-only" style="display:none;"><label>Red dot kompatibilitás</label><input type="text" name="marok_red_dot_compatibility" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_red_dot_compatibility' ) ); ?>" placeholder="pl. Trijicon RMR, Holosun 507C"></div>
                    <div class="va-form-group"><label>Menetes cső</label><?php $v = $marok_saved( 'marok_threaded_barrel' ); ?><select name="marok_threaded_barrel" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Sín</label><?php $v = $marok_saved( 'marok_rail_type' ); ?><select name="marok_rail_type" class="va-select"><option value="">- Válasszon -</option><option value="picatinny"<?php selected( $v, 'picatinny' ); ?>>Picatinny</option><option value="weaver"<?php selected( $v, 'weaver' ); ?>>Weaver</option><option value="nincs"<?php selected( $v, 'nincs' ); ?>>Nincs</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="marok_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot"><option value="gyari-doboz"<?php echo in_array( 'gyari-doboz', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Gyári doboz</option><option value="tartalek-tar"<?php echo in_array( 'tartalek-tar', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Tartalék tár</option><option value="tok"<?php echo in_array( 'tok', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Tok</option><option value="tisztitokeszlet"<?php echo in_array( 'tisztitokeszlet', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Tisztítókészlet</option><option value="optika"<?php echo in_array( 'optika', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Optika</option><option value="szerulek"<?php echo in_array( 'szerulek', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Szerelék</option><option value="eredeti-papirok"<?php echo in_array( 'eredeti-papirok', $marok_accessories_saved, true ) ? ' selected' : ''; ?>>Eredeti papírok</option></select></div>
                    <div class="va-form-group"><label>Engedélyköteles</label><?php $v = $marok_saved( 'marok_license_required' ); ?><select name="marok_license_required" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option></select></div>
                    <div class="va-form-group"><label>Kategória</label><?php $v = $marok_saved( 'marok_legal_category' ); ?><select name="marok_legal_category" class="va-select"><option value="">- Válasszon -</option><option value="sport"<?php selected( $v, 'sport' ); ?>>Sport</option><option value="vadasz"<?php selected( $v, 'vadasz' ); ?>>Vadász</option><option value="onvedelmi"<?php selected( $v, 'onvedelmi' ); ?>>Önvédelmi</option><option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option></select></div>
                    <div class="va-form-group"><label>Érvényes műszaki vizsga?</label><?php $v = $marok_saved( 'marok_technical_exam_valid' ); ?><select name="marok_technical_exam_valid" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>MKH / CIP jelölés</label><?php $v = $marok_saved( 'marok_cip_marking' ); ?><select name="marok_cip_marking" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Csak engedéllyel átvehető?</label><?php $v = $marok_saved( 'marok_transfer_license_only' ); ?><select name="marok_transfer_license_only" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option></select></div>
                    <div class="va-form-group"><label>Személyes átvétel kötelező?</label><?php $v = $marok_saved( 'marok_personal_pickup' ); ?><select name="marok_personal_pickup" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Sorozatszám (maszkolt)</label><input type="text" name="marok_serial_masked" class="va-input" value="<?php echo esc_attr( $marok_saved( 'marok_serial_masked' ) ); ?>" placeholder="pl. ABC12***"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Felhasználás</label><select name="marok_usage[]" class="va-select" multiple data-placeholder="Válassz felhasználást"><option value="sportloveszet"<?php echo in_array( 'sportloveszet', $marok_usage_saved, true ) ? ' selected' : ''; ?>>Sportlövészet</option><option value="ipsc"<?php echo in_array( 'ipsc', $marok_usage_saved, true ) ? ' selected' : ''; ?>>IPSC</option><option value="vadaszat"<?php echo in_array( 'vadaszat', $marok_usage_saved, true ) ? ' selected' : ''; ?>>Vadászat</option><option value="onvedelem"<?php echo in_array( 'onvedelem', $marok_usage_saved, true ) ? ' selected' : ''; ?>>Önvédelem</option><option value="gyujtoi"<?php echo in_array( 'gyujtoi', $marok_usage_saved, true ) ? ' selected' : ''; ?>>Gyűjtői</option></select></div>
                    <div class="va-form-group"><label>Ár: alku</label><?php $v = $marok_saved( 'marok_price_deal' ); ?><select name="marok_price_deal" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Ár: csere</label><?php $v = $marok_saved( 'marok_price_exchange' ); ?><select name="marok_price_exchange" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Átvétel</label><select name="marok_transfer_methods[]" class="va-select" multiple data-placeholder="Válassz átvételi módot"><option value="szemelyes"<?php echo in_array( 'szemelyes', $marok_transfer_saved, true ) ? ' selected' : ''; ?>>Személyes</option><option value="fegyverbolt-kozvetites"<?php echo in_array( 'fegyverbolt-kozvetites', $marok_transfer_saved, true ) ? ' selected' : ''; ?>>Fegyverbolt közvetítés</option><option value="hivatalos-atiras"<?php echo in_array( 'hivatalos-atiras', $marok_transfer_saved, true ) ? ' selected' : ''; ?>>Hivatalos átírás</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Kötelező képek</label><select name="marok_required_media[]" class="va-select" multiple data-placeholder="Válassz kötelező képeket"><option value="bal-oldal"<?php echo in_array( 'bal-oldal', $marok_required_media_saved, true ) ? ' selected' : ''; ?>>Bal oldal</option><option value="jobb-oldal"<?php echo in_array( 'jobb-oldal', $marok_required_media_saved, true ) ? ' selected' : ''; ?>>Jobb oldal</option><option value="cso"<?php echo in_array( 'cso', $marok_required_media_saved, true ) ? ' selected' : ''; ?>>Cső</option><option value="szan"<?php echo in_array( 'szan', $marok_required_media_saved, true ) ? ' selected' : ''; ?>>Szán</option><option value="markolat"<?php echo in_array( 'markolat', $marok_required_media_saved, true ) ? ' selected' : ''; ?>>Markolat</option><option value="tartozekok"<?php echo in_array( 'tartozekok', $marok_required_media_saved, true ) ? ' selected' : ''; ?>>Tartozékok</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Erősen ajánlott képek</label><select name="marok_recommended_media[]" class="va-select" multiple data-placeholder="Válassz ajánlott képeket"><option value="tok"<?php echo in_array( 'tok', $marok_recommended_media_saved, true ) ? ' selected' : ''; ?>>Tok</option><option value="iranyzek"<?php echo in_array( 'iranyzek', $marok_recommended_media_saved, true ) ? ' selected' : ''; ?>>Irányzék</option><option value="doboz"<?php echo in_array( 'doboz', $marok_recommended_media_saved, true ) ? ' selected' : ''; ?>>Doboz</option><option value="papirok"<?php echo in_array( 'papirok', $marok_recommended_media_saved, true ) ? ' selected' : ''; ?>>Papírok</option></select></div>
                    <div class="va-form-group"><label>Lőtt darabszám</label><?php $v = $marok_saved( 'marok_round_count' ); ?><select name="marok_round_count" class="va-select"><option value="">- Válasszon -</option><option value="lt500"<?php selected( $v, 'lt500' ); ?>>&lt;500</option><option value="lt2000"<?php selected( $v, 'lt2000' ); ?>>&lt;2000</option><option value="5000plus"<?php selected( $v, '5000plus' ); ?>>5000+</option><option value="ismeretlen"<?php selected( $v, 'ismeretlen' ); ?>>Ismeretlen</option></select></div>
                    <div class="va-form-group"><label>Trigger tuning</label><?php $v = $marok_saved( 'marok_trigger_tuning' ); ?><select name="marok_trigger_tuning" class="va-select"><option value="">- Válasszon -</option><option value="gyari"<?php selected( $v, 'gyari' ); ?>>Gyári</option><option value="tuningolt"<?php selected( $v, 'tuningolt' ); ?>>Tuningolt</option><option value="match"<?php selected( $v, 'match' ); ?>>Match</option></select></div>
                    <div class="va-form-group va-marok-dynamic-group va-marok-sport-only" style="grid-column:1 / -1;display:none;"><label>Sport kategóriák</label><select name="marok_sport_categories[]" class="va-select" multiple data-placeholder="Válassz sportágat"><option value="ipsc"<?php echo in_array( 'ipsc', $marok_sport_saved, true ) ? ' selected' : ''; ?>>IPSC</option><option value="idpa"<?php echo in_array( 'idpa', $marok_sport_saved, true ) ? ' selected' : ''; ?>>IDPA</option><option value="ppc"<?php echo in_array( 'ppc', $marok_sport_saved, true ) ? ' selected' : ''; ?>>PPC</option><option value="bullseye"<?php echo in_array( 'bullseye', $marok_sport_saved, true ) ? ' selected' : ''; ?>>Bullseye</option></select></div>
                    <div class="va-form-group va-marok-dynamic-group va-marok-sport-only" style="display:none;"><label>IPSC ready</label><?php $v = $marok_saved( 'marok_ipsc_ready' ); ?><select name="marok_ipsc_ready" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-marok-dynamic-group va-marok-sport-only" style="display:none;"><label>Race trigger</label><?php $v = $marok_saved( 'marok_race_trigger' ); ?><select name="marok_race_trigger" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Moderációs flag</label><select name="marok_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázatos jelölések"><option value="automata"<?php echo in_array( 'automata', $marok_moderation_saved, true ) ? ' selected' : ''; ?>>Automata</option><option value="sorozatlovo"<?php echo in_array( 'sorozatlovo', $marok_moderation_saved, true ) ? ' selected' : ''; ?>>Sorozatlövő</option><option value="konverzio"<?php echo in_array( 'konverzio', $marok_moderation_saved, true ) ? ' selected' : ''; ?>>Konverzió</option><option value="hangtompito"<?php echo in_array( 'hangtompito', $marok_moderation_saved, true ) ? ' selected' : ''; ?>>Hangtompító</option><option value="illegalis-atalakitas"<?php echo in_array( 'illegalis-atalakitas', $marok_moderation_saved, true ) ? ' selected' : ''; ?>>Illegális átalakítás</option><option value="engedely-nelkul"<?php echo in_array( 'engedely-nelkul', $marok_moderation_saved, true ) ? ' selected' : ''; ?>>Engedély nélkül</option></select></div>
                </div>
            </div>
            <datalist id="va-caliber-list"></datalist>
            <div class="va-form-group va-cat-rule-field" data-categories="cipo-bakancs,bakancs-felcipo,ruhazat-labbeli">
                <label>Cipőméret</label>
                <div class="va-year-input-wrap">
                    <input type="text" name="shoe_size" id="va-shoe-size-input" class="va-input" placeholder="pl. EU 43" readonly value="<?php echo esc_attr((string)($edit_meta['shoe_size'] ?? '')); ?>">
                    <button type="button" class="va-year-open-btn" id="va-shoe-size-open">Mérettáblázat</button>
                </div>
            </div>
            <?php
            $shoe_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $shoe_protections_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_protections' ) ) ) );
            $shoe_season_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_season' ) ) ) );
            $shoe_terrain_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_terrain' ) ) ) );
            $shoe_use_cases_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_use_cases' ) ) ) );
            $shoe_accessories_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_accessories' ) ) ) );
            $shoe_shipping_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_shipping_methods' ) ) ) );
            $shoe_required_media_saved = array_filter( array_map( 'trim', explode( ',', $shoe_meta( 'shoe_required_media' ) ) ) );
            ?>
            <div class="va-cat-rule-field va-shoe-fields-grid" data-categories="cipo-bakancs,bakancs-felcipo,ruhazat-labbeli" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group"><label>Típus</label><?php $v = $shoe_meta( 'shoe_main_type' ); ?><select name="shoe_main_type" id="va-shoe-main-type" class="va-select"><option value="">– Válasszon –</option><option value="vadaszbakancs"<?php selected( $v, 'vadaszbakancs' ); ?>>Vadászbakancs</option><option value="turabakancs"<?php selected( $v, 'turabakancs' ); ?>>Túrabakancs</option><option value="gumicsizma"<?php selected( $v, 'gumicsizma' ); ?>>Gumicsizma</option><option value="felcipo"<?php selected( $v, 'felcipo' ); ?>>Félcipő</option><option value="teli-csizma"<?php selected( $v, 'teli-csizma' ); ?>>Téli csizma</option><option value="nyari-cipo"<?php selected( $v, 'nyari-cipo' ); ?>>Nyári cipő</option><option value="taktikai-bakancs"<?php selected( $v, 'taktikai-bakancs' ); ?>>Taktikai bakancs</option><option value="hotaposo"<?php selected( $v, 'hotaposo' ); ?>>Hótaposó</option><option value="munkabakancs"<?php selected( $v, 'munkabakancs' ); ?>>Munkabakancs</option><option value="wading-csizma"<?php selected( $v, 'wading-csizma' ); ?>>Wading csizma</option><option value="vadasz-gumicsizma"<?php selected( $v, 'vadasz-gumicsizma' ); ?>>Vadász gumicsizma</option><option value="lescsizma"<?php selected( $v, 'lescsizma' ); ?>>Lescsizma</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Állapot</label><?php $v = $shoe_meta( 'shoe_condition' ); ?><select name="shoe_condition" class="va-select"><option value="">– Válasszon –</option><option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option><option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option><option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option><option value="javitott"<?php selected( $v, 'javitott' ); ?>>Javított</option></select></div>
                    <div class="va-form-group"><label>Nem</label><?php $v = $shoe_meta( 'shoe_gender' ); ?><select name="shoe_gender" class="va-select"><option value="">– Válasszon –</option><option value="ferfi"<?php selected( $v, 'ferfi' ); ?>>Férfi</option><option value="noi"<?php selected( $v, 'noi' ); ?>>Női</option><option value="unisex"<?php selected( $v, 'unisex' ); ?>>Unisex</option></select></div>
                    <div class="va-form-group"><label>EU méret</label><input type="text" name="shoe_eu_size" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_eu_size' ) !== '' ? $shoe_meta( 'shoe_eu_size' ) : $shoe_meta( 'shoe_size' ) ); ?>" placeholder="pl. EU 43"></div>
                    <div class="va-form-group"><label>UK méret</label><input type="text" name="shoe_uk_size" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_uk_size' ) ); ?>" placeholder="pl. UK 9"></div>
                    <div class="va-form-group"><label>US méret</label><input type="text" name="shoe_us_size" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_us_size' ) ); ?>" placeholder="pl. US 10"></div>
                    <div class="va-form-group"><label>Szármagasság</label><?php $v = $shoe_meta( 'shoe_boot_height' ); ?><select name="shoe_boot_height" class="va-select"><option value="">– Válasszon –</option><option value="alacsony"<?php selected( $v, 'alacsony' ); ?>>Alacsony</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="magas"<?php selected( $v, 'magas' ); ?>>Magas</option></select></div>
                    <div class="va-form-group"><label>Külső anyag</label><?php $v = $shoe_meta( 'shoe_outer_material' ); ?><select name="shoe_outer_material" class="va-select"><option value="">– Válasszon –</option><option value="bor"<?php selected( $v, 'bor' ); ?>>Bőr</option><option value="nubuk"<?php selected( $v, 'nubuk' ); ?>>Nubuk</option><option value="velur"<?php selected( $v, 'velur' ); ?>>Velúr</option><option value="cordura"<?php selected( $v, 'cordura' ); ?>>Cordura</option><option value="textil"<?php selected( $v, 'textil' ); ?>>Textil</option><option value="gumi"<?php selected( $v, 'gumi' ); ?>>Gumi</option><option value="kevert"<?php selected( $v, 'kevert' ); ?>>Kevert</option></select></div>
                    <div class="va-form-group"><label>Bélés</label><?php $v = $shoe_meta( 'shoe_lining' ); ?><select name="shoe_lining" class="va-select"><option value="">– Válasszon –</option><option value="gore-tex"<?php selected( $v, 'gore-tex' ); ?>>Gore-Tex</option><option value="sympatex"<?php selected( $v, 'sympatex' ); ?>>Sympatex</option><option value="thinsulate"<?php selected( $v, 'thinsulate' ); ?>>Thinsulate</option><option value="polar"<?php selected( $v, 'polar' ); ?>>Polár</option><option value="gyapju"<?php selected( $v, 'gyapju' ); ?>>Gyapjú</option><option value="beleletlen"<?php selected( $v, 'beleletlen' ); ?>>Béleletlen</option></select></div>
                    <div class="va-form-group"><label>Talp anyaga</label><?php $v = $shoe_meta( 'shoe_sole_material' ); ?><select name="shoe_sole_material" class="va-select"><option value="">– Válasszon –</option><option value="vibram"<?php selected( $v, 'vibram' ); ?>>Vibram</option><option value="gumi"<?php selected( $v, 'gumi' ); ?>>Gumi</option><option value="pu"<?php selected( $v, 'pu' ); ?>>PU</option><option value="eva"<?php selected( $v, 'eva' ); ?>>EVA</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Védelem</label><select name="shoe_protections[]" class="va-select" multiple data-placeholder="Válassz védelmeket"><option value="vizallo"<?php echo in_array( 'vizallo', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Vízálló</option><option value="vizlepergeto"<?php echo in_array( 'vizlepergeto', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Vízlepergető</option><option value="legatereszto"<?php echo in_array( 'legatereszto', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Légáteresztő</option><option value="szelallo"<?php echo in_array( 'szelallo', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Szélálló</option><option value="hoszigetelt"<?php echo in_array( 'hoszigetelt', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Hőszigetelt</option><option value="olajallo"<?php echo in_array( 'olajallo', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Olajálló</option><option value="csuszasmentes"<?php echo in_array( 'csuszasmentes', $shoe_protections_saved, true ) ? ' selected' : ''; ?>>Csúszásmentes</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Használati szezon</label><select name="shoe_season[]" class="va-select" multiple data-placeholder="Válassz szezont"><option value="nyari"<?php echo in_array( 'nyari', $shoe_season_saved, true ) ? ' selected' : ''; ?>>Nyári</option><option value="oszi"<?php echo in_array( 'oszi', $shoe_season_saved, true ) ? ' selected' : ''; ?>>Őszi</option><option value="teli"<?php echo in_array( 'teli', $shoe_season_saved, true ) ? ' selected' : ''; ?>>Téli</option><option value="negyevszakos"<?php echo in_array( 'negyevszakos', $shoe_season_saved, true ) ? ' selected' : ''; ?>>Négyévszakos</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Terep típus</label><select name="shoe_terrain[]" class="va-select" multiple data-placeholder="Válassz terepet"><option value="erdo"<?php echo in_array( 'erdo', $shoe_terrain_saved, true ) ? ' selected' : ''; ?>>Erdő</option><option value="hegyvidek"<?php echo in_array( 'hegyvidek', $shoe_terrain_saved, true ) ? ' selected' : ''; ?>>Hegyvidék</option><option value="sar"<?php echo in_array( 'sar', $shoe_terrain_saved, true ) ? ' selected' : ''; ?>>Sár</option><option value="ho"<?php echo in_array( 'ho', $shoe_terrain_saved, true ) ? ' selected' : ''; ?>>Hó</option><option value="sziklas"<?php echo in_array( 'sziklas', $shoe_terrain_saved, true ) ? ' selected' : ''; ?>>Sziklás terep</option><option value="vizes-terulet"<?php echo in_array( 'vizes-terulet', $shoe_terrain_saved, true ) ? ' selected' : ''; ?>>Vizes terület</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Felhasználás</label><select name="shoe_use_cases[]" class="va-select" multiple data-placeholder="Válassz felhasználást"><option value="vadaszat"<?php echo in_array( 'vadaszat', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Vadászat</option><option value="cserkeles"<?php echo in_array( 'cserkeles', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Cserkelés</option><option value="hajtas"<?php echo in_array( 'hajtas', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Hajtás</option><option value="tura"<?php echo in_array( 'tura', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Túra</option><option value="lesvadaszat"<?php echo in_array( 'lesvadaszat', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Lesvadászat</option><option value="horgaszat"<?php echo in_array( 'horgaszat', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Horgászat</option><option value="airsoft"<?php echo in_array( 'airsoft', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Airsoft</option><option value="munkavegzes"<?php echo in_array( 'munkavegzes', $shoe_use_cases_saved, true ) ? ' selected' : ''; ?>>Munkavégzés</option></select></div>
                    <div class="va-form-group"><label>Talpminta</label><?php $v = $shoe_meta( 'shoe_sole_pattern' ); ?><select name="shoe_sole_pattern" class="va-select"><option value="">– Válasszon –</option><option value="mely-mintas"<?php selected( $v, 'mely-mintas' ); ?>>Mély mintás</option><option value="univerzalis"<?php selected( $v, 'univerzalis' ); ?>>Univerzális</option><option value="teli"<?php selected( $v, 'teli' ); ?>>Téli</option><option value="terep"<?php selected( $v, 'terep' ); ?>>Terep</option></select></div>
                    <div class="va-form-group"><label>Merevség</label><?php $v = $shoe_meta( 'shoe_stiffness' ); ?><select name="shoe_stiffness" class="va-select"><option value="">– Válasszon –</option><option value="puha"<?php selected( $v, 'puha' ); ?>>Puha</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="merev"<?php selected( $v, 'merev' ); ?>>Merev</option></select></div>
                    <div class="va-form-group"><label>Egy cipő súlya (g)</label><input type="text" name="shoe_weight_single_g" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_weight_single_g' ) ); ?>" placeholder="pl. 620"></div>
                    <div class="va-form-group"><label>Pár súlya (g)</label><input type="text" name="shoe_weight_pair_g" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_weight_pair_g' ) ); ?>" placeholder="pl. 1240"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="shoe_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékokat"><option value="eredeti-doboz"<?php echo in_array( 'eredeti-doboz', $shoe_accessories_saved, true ) ? ' selected' : ''; ?>>Eredeti doboz</option><option value="plusz-fuzo"<?php echo in_array( 'plusz-fuzo', $shoe_accessories_saved, true ) ? ' selected' : ''; ?>>Plusz fűző</option><option value="impregnalo"<?php echo in_array( 'impregnalo', $shoe_accessories_saved, true ) ? ' selected' : ''; ?>>Impregnáló</option><option value="talpbetet"<?php echo in_array( 'talpbetet', $shoe_accessories_saved, true ) ? ' selected' : ''; ?>>Talpbetét</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Szállítás</label><select name="shoe_shipping_methods[]" class="va-select" multiple data-placeholder="Válassz szállítási módot"><option value="posta"<?php echo in_array( 'posta', $shoe_shipping_saved, true ) ? ' selected' : ''; ?>>Posta</option><option value="futar"<?php echo in_array( 'futar', $shoe_shipping_saved, true ) ? ' selected' : ''; ?>>Futár</option><option value="szemelyes"<?php echo in_array( 'szemelyes', $shoe_shipping_saved, true ) ? ' selected' : ''; ?>>Személyes</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Kötelező képek</label><select name="shoe_required_media[]" class="va-select" multiple data-placeholder="Válassz kötelező fotókat"><option value="oldalnezet"<?php echo in_array( 'oldalnezet', $shoe_required_media_saved, true ) ? ' selected' : ''; ?>>Oldalnézet</option><option value="talp"<?php echo in_array( 'talp', $shoe_required_media_saved, true ) ? ' selected' : ''; ?>>Talp</option><option value="belso-resz"<?php echo in_array( 'belso-resz', $shoe_required_media_saved, true ) ? ' selected' : ''; ?>>Belső rész</option><option value="meret-cimke"<?php echo in_array( 'meret-cimke', $shoe_required_media_saved, true ) ? ' selected' : ''; ?>>Méret címke</option><option value="orr-resz"<?php echo in_array( 'orr-resz', $shoe_required_media_saved, true ) ? ' selected' : ''; ?>>Orr rész</option></select></div>
                    <div class="va-form-group va-shoe-dynamic-group" data-shoe-types="gumicsizma,vadasz-gumicsizma,lescsizma,wading-csizma" style="display:none;"><label>Szármagasság (cm)</label><input type="text" name="shoe_rubber_shaft_cm" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_rubber_shaft_cm' ) ); ?>" placeholder="pl. 38"></div>
                    <div class="va-form-group va-shoe-dynamic-group" data-shoe-types="gumicsizma,vadasz-gumicsizma,lescsizma,wading-csizma" style="display:none;"><label>Neoprene bélés</label><?php $v = $shoe_meta( 'shoe_rubber_neoprene_lining' ); ?><select name="shoe_rubber_neoprene_lining" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-shoe-dynamic-group" data-shoe-types="taktikai-bakancs" style="display:none;"><label>Acélbetét</label><?php $v = $shoe_meta( 'shoe_tactical_steel_toe' ); ?><select name="shoe_tactical_steel_toe" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-shoe-dynamic-group" data-shoe-types="taktikai-bakancs" style="display:none;"><label>Gyorsfűző</label><?php $v = $shoe_meta( 'shoe_tactical_quick_lace' ); ?><select name="shoe_tactical_quick_lace" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-shoe-dynamic-group" data-shoe-types="teli-csizma,hotaposo" style="display:none;"><label>Komfort hőmérséklet (°C)</label><input type="text" name="shoe_winter_comfort_temp_c" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_winter_comfort_temp_c' ) ); ?>" placeholder="pl. -20"></div>
                    <div class="va-form-group"><label>Lábtípus</label><?php $v = $shoe_meta( 'shoe_foot_type' ); ?><select name="shoe_foot_type" class="va-select"><option value="">– Válasszon –</option><option value="normal"<?php selected( $v, 'normal' ); ?>>Normál</option><option value="szeles"<?php selected( $v, 'szeles' ); ?>>Széles</option><option value="keskeny"<?php selected( $v, 'keskeny' ); ?>>Keskeny</option></select></div>
                    <div class="va-form-group"><label>Talp kopása (%)</label><input type="text" name="shoe_sole_wear_percent" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_sole_wear_percent' ) ); ?>" placeholder="pl. 15"></div>
                    <div class="va-form-group"><label>Belső kopás</label><input type="text" name="shoe_inner_wear" class="va-input" value="<?php echo esc_attr( $shoe_meta( 'shoe_inner_wear' ) ); ?>" placeholder="pl. minimális"></div>
                    <div class="va-form-group"><label>Vízállóság megmaradt?</label><?php $v = $shoe_meta( 'shoe_waterproof_retained' ); ?><select name="shoe_waterproof_retained" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option><option value="reszben"<?php selected( $v, 'reszben' ); ?>>Részben</option></select></div>
                    <div class="va-form-group"><label>Újratalpalt?</label><?php $v = $shoe_meta( 'shoe_resoled' ); ?><select name="shoe_resoled" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Megjegyzés</label><textarea name="shoe_notes" class="va-input" rows="3" placeholder="pl. membrán állapota, tereptapasztalat, egyéb részlet"><?php echo esc_textarea( $shoe_meta( 'shoe_notes' ) ); ?></textarea></div>
                </div>
            </div>
            <div class="va-cat-rule-field" data-categories="vadasz-ruhazat,egyeb-ruhazat">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Ruhatípus</label>
                        <select name="clothing_type" id="va-clothing-type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <?php
                            $clothing_type_val = (string)($edit_meta['clothing_type'] ?? '');
                            $clothing_type_options = [
                                'sapka' => 'Sapka',
                                'kalap' => 'Kalap',
                                'maszk' => 'Maszk',
                                'balaclava' => 'Balaclava',
                                'sal' => 'Sál',
                                'nyakmelegito' => 'Nyakmelegítő',
                                'kesztyu' => 'Kesztyű',
                                'zokni' => 'Zokni',
                                'ov' => 'Öv',
                                'alaoltozet' => 'Aláöltözet',
                                'thermo-ruhazat' => 'Thermo ruházat',
                                'esoponcso' => 'Esőponcsó',
                                'ghillie-ruha' => 'Ghillie ruha',
                                'lathatosagi-melleny' => 'Láthatósági mellény',
                                'karmelegito' => 'Karmelegítő',
                                'konyokvedo' => 'Könyökvédő',
                                'terdvedo' => 'Térdvédő',
                                'egyeb' => 'Egyéb'
                            ];
                            if ( $clothing_type_val !== '' && ! array_key_exists( $clothing_type_val, $clothing_type_options ) ) {
                                echo '<option value="' . esc_attr( $clothing_type_val ) . '" selected>' . esc_html( $clothing_type_val ) . '</option>';
                            }
                            foreach ( $clothing_type_options as $ct_key => $ct_label ) {
                                echo '<option value="' . esc_attr( $ct_key ) . '"' . selected( $clothing_type_val, $ct_key, false ) . '>' . esc_html( $ct_label ) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Méretrendszer</label>
                        <select name="clothing_size_system" id="va-clothing-size-system" class="va-select">
                            <?php
                            $clothing_size_system_val = (string)($edit_meta['clothing_size_system'] ?? 'eu');
                            if ( $clothing_size_system_val === '' ) $clothing_size_system_val = 'eu';
                            ?>
                            <option value="eu"<?php echo selected( $clothing_size_system_val, 'eu', false ); ?>>EU</option>
                            <option value="usa"<?php echo selected( $clothing_size_system_val, 'usa', false ); ?>>USA</option>
                            <option value="en"<?php echo selected( $clothing_size_system_val, 'en', false ); ?>>EN</option>
                        </select>
                    </div>
                    <div class="va-form-group va-cat-rule-field" data-categories="egyeb-ruhazat" style="display:none;">
                        <label>Állapot</label>
                        <?php $v = (string) ( $edit_meta['clothing_condition'] ?? '' ); ?>
                        <select name="clothing_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option>
                            <option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option>
                            <option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option>
                            <option value="javitott"<?php selected( $v, 'javitott' ); ?>>Javított</option>
                        </select>
                    </div>
                    <div class="va-form-group va-cat-rule-field" data-categories="egyeb-ruhazat" style="display:none;">
                        <label>Nem</label>
                        <?php $v = (string) ( $edit_meta['clothing_gender'] ?? '' ); ?>
                        <select name="clothing_gender" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="ferfi"<?php selected( $v, 'ferfi' ); ?>>Férfi</option>
                            <option value="noi"<?php selected( $v, 'noi' ); ?>>Női</option>
                            <option value="unisex"<?php selected( $v, 'unisex' ); ?>>Unisex</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Ruhaméret</label>
                        <select name="clothing_size" id="va-clothing-size" class="va-select" data-selected="<?php echo esc_attr((string)($edit_meta['clothing_size'] ?? '')); ?>">
                            <option value="">– Válasszon –</option>
                        </select>
                    </div>
                    <div class="va-form-group va-cat-rule-field" data-categories="egyeb-ruhazat" style="display:none;">
                        <label>Kiegészítő méret</label>
                        <input type="text" name="clothing_accessory_size" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['clothing_accessory_size'] ?? '' ) ); ?>" placeholder="pl. M/L vagy 58 cm">
                    </div>
                </div>
            </div>
            <?php
            $clothing_colors_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_colors'] ?? '' ) ) ) );
            $clothing_materials_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_materials'] ?? '' ) ) ) );
            $clothing_protections_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_protections'] ?? '' ) ) ) );
            $clothing_season_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_season'] ?? '' ) ) ) );
            $clothing_use_cases_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_use_cases'] ?? '' ) ) ) );
            $clothing_special_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_special_features'] ?? '' ) ) ) );
            $clothing_glove_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_glove_features'] ?? '' ) ) ) );
            $clothing_headwear_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_headwear_features'] ?? '' ) ) ) );
            $clothing_ghillie_type_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_ghillie_type'] ?? '' ) ) ) );
            $clothing_ghillie_pattern_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['clothing_ghillie_pattern'] ?? '' ) ) ) );
            ?>
            <div class="va-cat-rule-field" data-categories="egyeb-ruhazat" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="kesztyu" style="display:none;">
                        <label>Kesztyű méret</label>
                        <?php $v = (string) ( $edit_meta['clothing_glove_size'] ?? '' ); ?>
                        <select name="clothing_glove_size" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="6"<?php selected( $v, '6' ); ?>>6</option>
                            <option value="7"<?php selected( $v, '7' ); ?>>7</option>
                            <option value="8"<?php selected( $v, '8' ); ?>>8</option>
                            <option value="9"<?php selected( $v, '9' ); ?>>9</option>
                            <option value="10"<?php selected( $v, '10' ); ?>>10</option>
                            <option value="xl"<?php selected( $v, 'xl' ); ?>>XL</option>
                        </select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="sapka,kalap,maszk,balaclava" style="display:none;">
                        <label>Fejméret</label>
                        <?php $v = (string) ( $edit_meta['clothing_hat_size'] ?? '' ); ?>
                        <select name="clothing_hat_size" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="s-m"<?php selected( $v, 's-m' ); ?>>S/M</option>
                            <option value="l-xl"<?php selected( $v, 'l-xl' ); ?>>L/XL</option>
                            <option value="cm"<?php selected( $v, 'cm' ); ?>>cm alapú</option>
                        </select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="sapka,kalap,maszk,balaclava" style="display:none;">
                        <label>Fejkörfogat (cm)</label>
                        <input type="text" name="clothing_hat_cm" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['clothing_hat_cm'] ?? '' ) ); ?>" placeholder="pl. 58">
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="ov" style="display:none;">
                        <label>Öv hossza (cm)</label>
                        <input type="text" name="clothing_belt_length_cm" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['clothing_belt_length_cm'] ?? '' ) ); ?>" placeholder="pl. 110">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Szín / minta</label>
                        <select name="clothing_colors[]" class="va-select" multiple data-placeholder="Válassz színt / mintát">
                            <option value="zold"<?php echo in_array( 'zold', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Zöld</option>
                            <option value="barna"<?php echo in_array( 'barna', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Barna</option>
                            <option value="fekete"<?php echo in_array( 'fekete', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Fekete</option>
                            <option value="terepmintas"<?php echo in_array( 'terepmintas', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Terepmintás</option>
                            <option value="blaze-orange"<?php echo in_array( 'blaze-orange', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Blaze orange</option>
                            <option value="realtree"<?php echo in_array( 'realtree', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Realtree</option>
                            <option value="multicam"<?php echo in_array( 'multicam', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Multicam</option>
                            <option value="ho-mintas"<?php echo in_array( 'ho-mintas', $clothing_colors_saved, true ) ? ' selected' : ''; ?>>Hó mintás</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Anyag</label>
                        <select name="clothing_materials[]" class="va-select" multiple data-placeholder="Válassz anyagot">
                            <option value="merino"<?php echo in_array( 'merino', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Merino</option>
                            <option value="fleece"<?php echo in_array( 'fleece', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Fleece</option>
                            <option value="gyapju"<?php echo in_array( 'gyapju', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Gyapjú</option>
                            <option value="polyester"<?php echo in_array( 'polyester', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Polyester</option>
                            <option value="softshell"<?php echo in_array( 'softshell', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Softshell</option>
                            <option value="gore-tex"<?php echo in_array( 'gore-tex', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Gore-Tex</option>
                            <option value="neopren"<?php echo in_array( 'neopren', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Neoprén</option>
                            <option value="pamut"<?php echo in_array( 'pamut', $clothing_materials_saved, true ) ? ' selected' : ''; ?>>Pamut</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Védelem</label>
                        <select name="clothing_protections[]" class="va-select" multiple data-placeholder="Válassz védelmet">
                            <option value="vizallo"<?php echo in_array( 'vizallo', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>Vízálló</option>
                            <option value="szelallo"<?php echo in_array( 'szelallo', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>Szélálló</option>
                            <option value="thermo"<?php echo in_array( 'thermo', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>Thermo</option>
                            <option value="legatereszto"<?php echo in_array( 'legatereszto', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>Légáteresztő</option>
                            <option value="uv-vedelem"<?php echo in_array( 'uv-vedelem', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>UV védelem</option>
                            <option value="szunyogallo"<?php echo in_array( 'szunyogallo', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>Szúnyogálló</option>
                            <option value="tuskebiztos"<?php echo in_array( 'tuskebiztos', $clothing_protections_saved, true ) ? ' selected' : ''; ?>>Tüskebiztos</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Szezon</label>
                        <select name="clothing_season[]" class="va-select" multiple data-placeholder="Válassz szezont">
                            <option value="nyari"<?php echo in_array( 'nyari', $clothing_season_saved, true ) ? ' selected' : ''; ?>>Nyári</option>
                            <option value="teli"<?php echo in_array( 'teli', $clothing_season_saved, true ) ? ' selected' : ''; ?>>Téli</option>
                            <option value="atmeneti"<?php echo in_array( 'atmeneti', $clothing_season_saved, true ) ? ' selected' : ''; ?>>Átmeneti</option>
                            <option value="negyevszakos"<?php echo in_array( 'negyevszakos', $clothing_season_saved, true ) ? ' selected' : ''; ?>>Négyévszakos</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Felhasználás</label>
                        <select name="clothing_use_cases[]" class="va-select" multiple data-placeholder="Válassz felhasználást">
                            <option value="vadaszat"<?php echo in_array( 'vadaszat', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Vadászat</option>
                            <option value="lesvadaszat"<?php echo in_array( 'lesvadaszat', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Lesvadászat</option>
                            <option value="cserkeles"<?php echo in_array( 'cserkeles', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Cserkelés</option>
                            <option value="hajtas"<?php echo in_array( 'hajtas', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Hajtás</option>
                            <option value="tura"<?php echo in_array( 'tura', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Túra</option>
                            <option value="horgaszat"<?php echo in_array( 'horgaszat', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Horgászat</option>
                            <option value="airsoft"<?php echo in_array( 'airsoft', $clothing_use_cases_saved, true ) ? ' selected' : ''; ?>>Airsoft</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Speciális tulajdonságok</label>
                        <select name="clothing_special_features[]" class="va-select" multiple data-placeholder="Válassz tulajdonságot">
                            <option value="hangtalan"<?php echo in_array( 'hangtalan', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Hangtalan</option>
                            <option value="erintokepernyo-kompatibilis"<?php echo in_array( 'erintokepernyo-kompatibilis', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Érintőképernyő kompatibilis</option>
                            <option value="gyorsan-szarado"<?php echo in_array( 'gyorsan-szarado', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Gyorsan száradó</option>
                            <option value="stretch"<?php echo in_array( 'stretch', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Stretch</option>
                            <option value="kompresszios"<?php echo in_array( 'kompresszios', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Kompressziós</option>
                            <option value="belelt"<?php echo in_array( 'belelt', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Bélelt</option>
                            <option value="langallo"<?php echo in_array( 'langallo', $clothing_special_saved, true ) ? ' selected' : ''; ?>>Lángálló</option>
                        </select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="kesztyu" style="grid-column:1 / -1;display:none;">
                        <label>Kesztyű specifikus</label>
                        <select name="clothing_glove_features[]" class="va-select" multiple data-placeholder="Válassz kesztyű jellemzőt">
                            <option value="ujj-nelkuli"<?php echo in_array( 'ujj-nelkuli', $clothing_glove_saved, true ) ? ' selected' : ''; ?>>Ujj nélküli</option>
                            <option value="lovesz-kesztyu"<?php echo in_array( 'lovesz-kesztyu', $clothing_glove_saved, true ) ? ' selected' : ''; ?>>Lövész kesztyű</option>
                            <option value="teli"<?php echo in_array( 'teli', $clothing_glove_saved, true ) ? ' selected' : ''; ?>>Téli</option>
                            <option value="vizallo-tenyer"<?php echo in_array( 'vizallo-tenyer', $clothing_glove_saved, true ) ? ' selected' : ''; ?>>Vízálló tenyér</option>
                        </select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="sapka,kalap,maszk,balaclava" style="grid-column:1 / -1;display:none;">
                        <label>Fejfedő specifikus</label>
                        <select name="clothing_headwear_features[]" class="va-select" multiple data-placeholder="Válassz fejfedő jellemzőt">
                            <option value="baseball-sapka"<?php echo in_array( 'baseball-sapka', $clothing_headwear_saved, true ) ? ' selected' : ''; ?>>Baseball sapka</option>
                            <option value="boonie"<?php echo in_array( 'boonie', $clothing_headwear_saved, true ) ? ' selected' : ''; ?>>Boonie</option>
                            <option value="kotott-sapka"<?php echo in_array( 'kotott-sapka', $clothing_headwear_saved, true ) ? ' selected' : ''; ?>>Kötött sapka</option>
                            <option value="premes-sapka"<?php echo in_array( 'premes-sapka', $clothing_headwear_saved, true ) ? ' selected' : ''; ?>>Prémes sapka</option>
                            <option value="halos-sapka"<?php echo in_array( 'halos-sapka', $clothing_headwear_saved, true ) ? ' selected' : ''; ?>>Hálós sapka</option>
                        </select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="esoponcso" style="display:none;">
                        <label>Vízoszlop (mm)</label>
                        <input type="text" name="clothing_poncho_water_column" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['clothing_poncho_water_column'] ?? '' ) ); ?>" placeholder="pl. 10000">
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="esoponcso" style="display:none;">
                        <label>Ultrakönnyű</label>
                        <?php $v = (string) ( $edit_meta['clothing_poncho_ultralight'] ?? '' ); ?>
                        <select name="clothing_poncho_ultralight" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="esoponcso" style="display:none;">
                        <label>Hátizsák kompatibilis</label>
                        <?php $v = (string) ( $edit_meta['clothing_poncho_backpack_compatible'] ?? '' ); ?>
                        <select name="clothing_poncho_backpack_compatible" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="ghillie-ruha" style="grid-column:1 / -1;display:none;">
                        <label>Ghillie típus</label>
                        <select name="clothing_ghillie_type[]" class="va-select" multiple data-placeholder="Válassz típust">
                            <option value="teljes-ruha"<?php echo in_array( 'teljes-ruha', $clothing_ghillie_type_saved, true ) ? ' selected' : ''; ?>>Teljes ruha</option>
                            <option value="felso"<?php echo in_array( 'felso', $clothing_ghillie_type_saved, true ) ? ' selected' : ''; ?>>Felső</option>
                            <option value="poncso"<?php echo in_array( 'poncso', $clothing_ghillie_type_saved, true ) ? ' selected' : ''; ?>>Poncsó</option>
                            <option value="3d-levelzet"<?php echo in_array( '3d-levelzet', $clothing_ghillie_type_saved, true ) ? ' selected' : ''; ?>>3D levélzet</option>
                        </select>
                    </div>
                    <div class="va-form-group va-clothing-dynamic-group" data-clothing-types="ghillie-ruha" style="grid-column:1 / -1;display:none;">
                        <label>Ghillie minta</label>
                        <select name="clothing_ghillie_pattern[]" class="va-select" multiple data-placeholder="Válassz mintát">
                            <option value="woodland"<?php echo in_array( 'woodland', $clothing_ghillie_pattern_saved, true ) ? ' selected' : ''; ?>>Woodland</option>
                            <option value="snow"<?php echo in_array( 'snow', $clothing_ghillie_pattern_saved, true ) ? ' selected' : ''; ?>>Snow</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Távcső bővített grid layout -->
            <div class="va-form-group va-telescope-fields-grid" data-categories="tavcsovek,ejjellato-tavcso,hokamerak">
                <div class="va-step2-4col-inner">
                <?php
                $optic_type_val = (string)($edit_meta['optic_type'] ?? '');
                $optic_zoom_val = (string)($edit_meta['optic_zoom'] ?? '');
                $optic_obj_val = (string)($edit_meta['optic_objective'] ?? '');
                $optic_obj_opts = ['20','21','24','25','30','32','35','40','42','44','50','56','60','62','72','80','100'];
                $optic_magnification_min_val = (string)($edit_meta['optic_magnification_min'] ?? '');
                $optic_magnification_max_val = (string)($edit_meta['optic_magnification_max'] ?? '');
                $optic_magnification_fixed_val = (string)($edit_meta['optic_magnification_fixed'] ?? '');
                $optic_waterproof_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['optic_waterproof_flags'] ?? ''))));
                $optic_rail_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['optic_rail_compatibility'] ?? ''))));
                $optic_weapon_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['optic_weapon_compatibility'] ?? ''))));
                $scope_accessories_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['scope_accessories_flags'] ?? ''))));
                $optic_shipping_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['optic_shipping_methods'] ?? ''))));
                $optic_use_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['optic_recommended_use'] ?? ''))));
                $optic_moderation_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['optic_moderation_flags'] ?? ''))));
                ?>
                <div class="va-telescope-field va-telescope-type-wrap">
                    <label>Távcső típusa</label>
                    <select name="optic_type" id="va-optic-type" class="va-select">
                        <option value="">– Válasszon típust –</option>
                        <option value="celtavcso"<?php selected($optic_type_val, 'celtavcso'); ?>>Céltávcső</option>
                        <option value="binokular"<?php selected($optic_type_val, 'binokular'); ?>>Binokulár</option>
                        <option value="monokular"<?php selected($optic_type_val, 'monokular'); ?>>Monokulár</option>
                        <option value="spektiv"<?php selected($optic_type_val, 'spektiv'); ?>>Spektív</option>
                        <option value="red-dot"<?php selected($optic_type_val, 'red-dot'); ?>>Red dot</option>
                        <option value="holografikus"<?php selected($optic_type_val, 'holografikus'); ?>>Holografikus irányzék</option>
                        <option value="prizmas"<?php selected($optic_type_val, 'prizmas'); ?>>Prizmás optika</option>
                        <option value="reflex"<?php selected($optic_type_val, 'reflex'); ?>>Reflex irányzék</option>
                        <option value="pisztolyoptika"<?php selected($optic_type_val, 'pisztolyoptika'); ?>>Pisztolyoptika</option>
                        <option value="kereso"<?php selected($optic_type_val, 'kereso'); ?>>Keresőtávcső</option>
                        <option value="tavmeros-binokular"<?php selected($optic_type_val, 'tavmeros-binokular'); ?>>Távmérős binokulár</option>
                        <option value="airsoft"<?php selected($optic_type_val, 'airsoft'); ?>>Airsoft optika</option>
                        <option value="egyeb"<?php selected($optic_type_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Nagyítás minimum (x)</label>
                    <input type="text" name="optic_magnification_min" class="va-input" value="<?php echo esc_attr($optic_magnification_min_val); ?>" placeholder="pl. 3">
                </div>
                <div class="va-telescope-field">
                    <label>Nagyítás maximum (x)</label>
                    <input type="text" name="optic_magnification_max" class="va-input" value="<?php echo esc_attr($optic_magnification_max_val); ?>" placeholder="pl. 12">
                </div>
                <div class="va-telescope-field" data-optic-types="red-dot,holografikus,reflex,prizmas,pisztolyoptika,airsoft,egyeb">
                    <label>Fix nagyítás (x)</label>
                    <input type="text" name="optic_magnification_fixed" class="va-input" value="<?php echo esc_attr($optic_magnification_fixed_val); ?>" placeholder="pl. 1 vagy 4">
                </div>
                <div class="va-telescope-field">
                    <label>Nagyítás (kompatibilitási mező)</label>
                    <select name="optic_zoom" id="va-optic-zoom" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php if ($optic_zoom_val !== ''): ?>
                        <option value="<?php echo esc_attr($optic_zoom_val); ?>" selected><?php echo esc_html($optic_zoom_val); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="va-telescope-field" data-visibility="tavcsovek,ejjellato-tavcso,hokamerak">
                    <label>Objektív átmérő (mm)</label>
                    <select name="optic_objective" id="va-optic-objective" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php if ($optic_obj_val !== '' && !in_array($optic_obj_val, $optic_obj_opts, true)): ?>
                        <option value="<?php echo esc_attr($optic_obj_val); ?>" selected><?php echo esc_html($optic_obj_val); ?> mm</option>
                        <?php endif; ?>
                        <?php foreach ($optic_obj_opts as $omm): ?>
                        <option value="<?php echo esc_attr($omm); ?>"<?php selected($optic_obj_val, $omm); ?>><?php echo esc_html($omm); ?> mm</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Objektív rendszer</label>
                    <select name="scope_type" class="va-select">
                        <option value="">-- Válassz típust --</option>
                        <option value="monokuláris"<?php echo ($edit_meta['scope_type'] ?? '') === 'monokuláris' ? ' selected' : ''; ?>>Monokuláris</option>
                        <option value="biokuláris"<?php echo ($edit_meta['scope_type'] ?? '') === 'biokuláris' ? ' selected' : ''; ?>>Biokuláris</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Csőátmérő (mm)</label>
                    <select name="optic_tube_diameter" class="va-select">
                        <?php $v = (string)($edit_meta['optic_tube_diameter'] ?? ''); ?>
                        <option value="">– Válasszon –</option>
                        <option value="25.4"<?php selected($v, '25.4'); ?>>25.4 mm (1 inch)</option>
                        <option value="30"<?php selected($v, '30'); ?>>30 mm</option>
                        <option value="34"<?php selected($v, '34'); ?>>34 mm</option>
                        <option value="35"<?php selected($v, '35'); ?>>35 mm</option>
                        <option value="36"<?php selected($v, '36'); ?>>36 mm</option>
                        <option value="40"<?php selected($v, '40'); ?>>40 mm</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Kilépő pupilla (mm)</label>
                    <input type="text" name="optic_exit_pupil" class="va-input" value="<?php echo esc_attr((string)($edit_meta['optic_exit_pupil'] ?? '')); ?>" placeholder="pl. 4.2">
                </div>
                <div class="va-telescope-field">
                    <label>Látómező 100 m-en (m)</label>
                    <input type="text" name="optic_field_of_view" class="va-input" value="<?php echo esc_attr((string)($edit_meta['optic_field_of_view'] ?? '')); ?>" placeholder="pl. 11.3">
                </div>
                <div class="va-telescope-field">
                    <label>Eye relief (mm)</label>
                    <input type="text" name="optic_eye_relief" class="va-input" value="<?php echo esc_attr((string)($edit_meta['optic_eye_relief'] ?? '')); ?>" placeholder="pl. 90">
                </div>
                <div class="va-telescope-field">
                    <label>Reticle típus</label>
                    <?php $v = (string)($edit_meta['optic_reticle_type'] ?? ''); ?>
                    <select name="optic_reticle_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="duplex"<?php selected($v, 'duplex'); ?>>Duplex</option>
                        <option value="mil-dot"<?php selected($v, 'mil-dot'); ?>>Mil-Dot</option>
                        <option value="moa"<?php selected($v, 'moa'); ?>>MOA</option>
                        <option value="bdc"<?php selected($v, 'bdc'); ?>>BDC</option>
                        <option value="4a"<?php selected($v, '4a'); ?>>4A</option>
                        <option value="german-4"<?php selected($v, 'german-4'); ?>>German #4</option>
                        <option value="horseshoe"<?php selected($v, 'horseshoe'); ?>>Horseshoe</option>
                        <option value="dot"<?php selected($v, 'dot'); ?>>Dot</option>
                        <option value="egyeb"<?php selected($v, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Reticle sík</label>
                    <?php $v = (string)($edit_meta['optic_reticle_plane'] ?? ''); ?>
                    <select name="optic_reticle_plane" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="ffp"<?php selected($v, 'ffp'); ?>>FFP</option>
                        <option value="sfp"<?php selected($v, 'sfp'); ?>>SFP</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Megvilágítás</label>
                    <?php $v = (string)($edit_meta['optic_illumination'] ?? ''); ?>
                    <select name="optic_illumination" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="nincs"<?php selected($v, 'nincs'); ?>>Nincs</option>
                        <option value="piros"<?php selected($v, 'piros'); ?>>Piros</option>
                        <option value="zold"<?php selected($v, 'zold'); ?>>Zöld</option>
                        <option value="piros-zold"<?php selected($v, 'piros-zold'); ?>>Piros + zöld</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Megvilágítás színe</label>
                    <input type="text" name="optic_illumination_color" class="va-input" value="<?php echo esc_attr((string)($edit_meta['optic_illumination_color'] ?? '')); ?>" placeholder="pl. piros/zöld">
                </div>
                <div class="va-telescope-field">
                    <label>Klikk érték</label>
                    <?php $v = (string)($edit_meta['optic_click_value'] ?? ''); ?>
                    <select name="optic_click_value" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="1-4-moa"<?php selected($v, '1-4-moa'); ?>>1/4 MOA</option>
                        <option value="1-8-moa"<?php selected($v, '1-8-moa'); ?>>1/8 MOA</option>
                        <option value="0-1-mrad"<?php selected($v, '0-1-mrad'); ?>>0.1 MRAD</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Parallax állítás</label>
                    <?php $v = (string)($edit_meta['optic_parallax_adjustment'] ?? ''); ?>
                    <select name="optic_parallax_adjustment" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="fix"<?php selected($v, 'fix'); ?>>Fix</option>
                        <option value="ao"<?php selected($v, 'ao'); ?>>AO</option>
                        <option value="oldalso"<?php selected($v, 'oldalso'); ?>>Oldalsó tekerő</option>
                        <option value="nincs"<?php selected($v, 'nincs'); ?>>Nincs</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Torony típus</label>
                    <?php $v = (string)($edit_meta['optic_turret_type'] ?? ''); ?>
                    <select name="optic_turret_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="low-profile"<?php selected($v, 'low-profile'); ?>>Low profile</option>
                        <option value="target"<?php selected($v, 'target'); ?>>Target</option>
                        <option value="locking"<?php selected($v, 'locking'); ?>>Locking</option>
                        <option value="capped"<?php selected($v, 'capped'); ?>>Capped</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Test anyag</label>
                    <?php $v = (string)($edit_meta['optic_body_material'] ?? ''); ?>
                    <select name="optic_body_material" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="aluminium"<?php selected($v, 'aluminium'); ?>>Alumínium</option>
                        <option value="magnzium"<?php selected($v, 'magnzium'); ?>>Magnézium</option>
                        <option value="acel"<?php selected($v, 'acel'); ?>>Acél</option>
                        <option value="polymer"<?php selected($v, 'polymer'); ?>>Polimer</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Bevonat típusa</label>
                    <?php $v = (string)($edit_meta['optic_coating_type'] ?? ''); ?>
                    <select name="optic_coating_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="coated"<?php selected($v, 'coated'); ?>>Coated</option>
                        <option value="fully-coated"<?php selected($v, 'fully-coated'); ?>>Fully coated</option>
                        <option value="multi-coated"<?php selected($v, 'multi-coated'); ?>>Multi-coated</option>
                        <option value="fully-multi-coated"<?php selected($v, 'fully-multi-coated'); ?>>Fully multi-coated</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Vízállóság</label>
                    <select name="optic_waterproof_flags[]" class="va-select" multiple>
                        <option value="vizallo"<?php echo in_array('vizallo', $optic_waterproof_saved, true) ? ' selected' : ''; ?>>Vízálló</option>
                        <option value="paramentes"<?php echo in_array('paramentes', $optic_waterproof_saved, true) ? ' selected' : ''; ?>>Páramentes</option>
                        <option value="nitrogen"<?php echo in_array('nitrogen', $optic_waterproof_saved, true) ? ' selected' : ''; ?>>Nitrogén töltés</option>
                        <option value="argon"<?php echo in_array('argon', $optic_waterproof_saved, true) ? ' selected' : ''; ?>>Argon töltés</option>
                        <option value="shockproof"<?php echo in_array('shockproof', $optic_waterproof_saved, true) ? ' selected' : ''; ?>>Ütésálló</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Prizma rendszer</label>
                    <?php $v = (string)($edit_meta['optic_prism_system'] ?? ''); ?>
                    <select name="optic_prism_system" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="roof"<?php selected($v, 'roof'); ?>>Roof</option>
                        <option value="porro"<?php selected($v, 'porro'); ?>>Porro</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Üvegtípus</label>
                    <?php $v = (string)($edit_meta['optic_glass_type'] ?? ''); ?>
                    <select name="optic_glass_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="ed"<?php selected($v, 'ed'); ?>>ED</option>
                        <option value="hd"<?php selected($v, 'hd'); ?>>HD</option>
                        <option value="apokromat"<?php selected($v, 'apokromat'); ?>>Apokromát</option>
                        <option value="standard"<?php selected($v, 'standard'); ?>>Standard</option>
                    </select>
                </div>
                <div class="va-telescope-field" data-optic-types="red-dot,holografikus,reflex,prizmas,pisztolyoptika,airsoft">
                    <label>Pontméret (MOA)</label>
                    <?php $v = (string)($edit_meta['optic_dot_size'] ?? ''); ?>
                    <select name="optic_dot_size" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="1"<?php selected($v, '1'); ?>>1</option>
                        <option value="2"<?php selected($v, '2'); ?>>2</option>
                        <option value="3"<?php selected($v, '3'); ?>>3</option>
                        <option value="4"<?php selected($v, '4'); ?>>4</option>
                        <option value="5"<?php selected($v, '5'); ?>>5</option>
                        <option value="6"<?php selected($v, '6'); ?>>6</option>
                    </select>
                </div>
                <div class="va-telescope-field" data-optic-types="red-dot,holografikus,reflex,prizmas,pisztolyoptika,airsoft,celtavcso,kereso">
                    <label>Sín kompatibilitás</label>
                    <select name="optic_rail_compatibility[]" class="va-select" multiple>
                        <option value="11mm"<?php echo in_array('11mm', $optic_rail_saved, true) ? ' selected' : ''; ?>>11 mm</option>
                        <option value="weaver"<?php echo in_array('weaver', $optic_rail_saved, true) ? ' selected' : ''; ?>>Weaver</option>
                        <option value="picatinny"<?php echo in_array('picatinny', $optic_rail_saved, true) ? ' selected' : ''; ?>>Picatinny</option>
                        <option value="zeiss"<?php echo in_array('zeiss', $optic_rail_saved, true) ? ' selected' : ''; ?>>Zeiss</option>
                    </select>
                </div>
                <div class="va-telescope-field" data-optic-types="tavmeros-binokular">
                    <label>Távmérő max (m)</label>
                    <input type="text" name="optic_rangefinder_max_m" class="va-input" value="<?php echo esc_attr((string)($edit_meta['optic_rangefinder_max_m'] ?? '')); ?>" placeholder="pl. 1800">
                </div>
                <div class="va-telescope-field" data-optic-types="tavmeros-binokular,celtavcso">
                    <label>Ballisztikai kalkulátor</label>
                    <?php $v = (string)($edit_meta['optic_ballistic_calc'] ?? ''); ?>
                    <select name="optic_ballistic_calc" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="nincs"<?php selected($v, 'nincs'); ?>>Nincs</option>
                        <option value="van"<?php selected($v, 'van'); ?>>Van</option>
                    </select>
                </div>
                <div class="va-telescope-field" data-optic-types="spektiv">
                    <label>Spektív zoom tartomány</label>
                    <?php $v = (string)($edit_meta['optic_spektiv_zoom_range'] ?? ''); ?>
                    <select name="optic_spektiv_zoom_range" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="15-45"<?php selected($v, '15-45'); ?>>15-45x</option>
                        <option value="20-60"<?php selected($v, '20-60'); ?>>20-60x</option>
                        <option value="25-75"<?php selected($v, '25-75'); ?>>25-75x</option>
                        <option value="30-90"<?php selected($v, '30-90'); ?>>30-90x</option>
                    </select>
                </div>
                <div class="va-telescope-field" data-optic-types="spektiv">
                    <label>Okulár típus</label>
                    <?php $v = (string)($edit_meta['optic_eyepiece_type'] ?? ''); ?>
                    <select name="optic_eyepiece_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="45-fok"<?php selected($v, '45-fok'); ?>>45°</option>
                        <option value="egyenes"<?php selected($v, 'egyenes'); ?>>Egyenes</option>
                        <option value="cserelheto"<?php selected($v, 'cserelheto'); ?>>Cserélhető</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Fegyver kompatibilitás</label>
                    <select name="optic_weapon_compatibility[]" class="va-select" multiple>
                        <option value="legpuska"<?php echo in_array('legpuska', $optic_weapon_saved, true) ? ' selected' : ''; ?>>Légpuska</option>
                        <option value="22lr"<?php echo in_array('22lr', $optic_weapon_saved, true) ? ' selected' : ''; ?>>.22 LR</option>
                        <option value="kozepkaliber"<?php echo in_array('kozepkaliber', $optic_weapon_saved, true) ? ' selected' : ''; ?>>Közepes kaliber</option>
                        <option value="nagyvadu"<?php echo in_array('nagyvadu', $optic_weapon_saved, true) ? ' selected' : ''; ?>>Nagyvad</option>
                        <option value="airsoft"<?php echo in_array('airsoft', $optic_weapon_saved, true) ? ' selected' : ''; ?>>Airsoft</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Tartozékok</label>
                    <input type="text" name="scope_accessories" class="va-input" placeholder="pl. Tartó, lencsevédő" value="<?php echo esc_attr((string)($edit_meta['scope_accessories'] ?? '')); ?>">
                </div>
                <div class="va-telescope-field">
                    <label>Tartozék jelölők</label>
                    <select name="scope_accessories_flags[]" class="va-select" multiple>
                        <option value="doboz"<?php echo in_array('doboz', $scope_accessories_saved, true) ? ' selected' : ''; ?>>Doboz</option>
                        <option value="kupak"<?php echo in_array('kupak', $scope_accessories_saved, true) ? ' selected' : ''; ?>>Lencsevédő kupak</option>
                        <option value="napellenzo"<?php echo in_array('napellenzo', $scope_accessories_saved, true) ? ' selected' : ''; ?>>Napellenző</option>
                        <option value="szelelek"<?php echo in_array('szelelek', $scope_accessories_saved, true) ? ' selected' : ''; ?>>Szerelék</option>
                        <option value="garancia"<?php echo in_array('garancia', $scope_accessories_saved, true) ? ' selected' : ''; ?>>Garancia</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Szerelék típusa</label>
                    <?php $v = (string)($edit_meta['optic_mount_type'] ?? ''); ?>
                    <select name="optic_mount_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="fix"<?php selected($v, 'fix'); ?>>Fix</option>
                        <option value="quick-detach"<?php selected($v, 'quick-detach'); ?>>Quick detach</option>
                        <option value="cantilever"<?php selected($v, 'cantilever'); ?>>Cantilever</option>
                        <option value="ring"<?php selected($v, 'ring'); ?>>Gyűrűs</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Szállítás módja</label>
                    <select name="optic_shipping_methods[]" class="va-select" multiple>
                        <option value="szemelyes"<?php echo in_array('szemelyes', $optic_shipping_saved, true) ? ' selected' : ''; ?>>Személyes átvétel</option>
                        <option value="futar"<?php echo in_array('futar', $optic_shipping_saved, true) ? ' selected' : ''; ?>>Futár</option>
                        <option value="foxpost"<?php echo in_array('foxpost', $optic_shipping_saved, true) ? ' selected' : ''; ?>>Foxpost</option>
                        <option value="posta"<?php echo in_array('posta', $optic_shipping_saved, true) ? ' selected' : ''; ?>>Posta</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Ajánlott felhasználás</label>
                    <select name="optic_recommended_use[]" class="va-select" multiple>
                        <option value="hajtas"<?php echo in_array('hajtas', $optic_use_saved, true) ? ' selected' : ''; ?>>Hajtás</option>
                        <option value="lesen"<?php echo in_array('lesen', $optic_use_saved, true) ? ' selected' : ''; ?>>Les</option>
                        <option value="sportloveszet"<?php echo in_array('sportloveszet', $optic_use_saved, true) ? ' selected' : ''; ?>>Sportlövészet</option>
                        <option value="tura"<?php echo in_array('tura', $optic_use_saved, true) ? ' selected' : ''; ?>>Túra</option>
                        <option value="airsoft"<?php echo in_array('airsoft', $optic_use_saved, true) ? ' selected' : ''; ?>>Airsoft</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Moderációs jelzők</label>
                    <select name="optic_moderation_flags[]" class="va-select" multiple>
                        <option value="sorozatszam-lathato"<?php echo in_array('sorozatszam-lathato', $optic_moderation_saved, true) ? ' selected' : ''; ?>>Sorozatszám látható</option>
                        <option value="szereles-nelkul"<?php echo in_array('szereles-nelkul', $optic_moderation_saved, true) ? ' selected' : ''; ?>>Szerelék nélkül</option>
                        <option value="sertesmentes-lencse"<?php echo in_array('sertesmentes-lencse', $optic_moderation_saved, true) ? ' selected' : ''; ?>>Sértésmentes lencse</option>
                        <option value="eredeti-csomagolas"<?php echo in_array('eredeti-csomagolas', $optic_moderation_saved, true) ? ' selected' : ''; ?>>Eredeti csomagolás</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Szürkületi érték</label>
                    <input type="text" name="twilight_value" class="va-input" placeholder="pl. 15" value="<?php echo esc_attr((string)($edit_meta['twilight_value'] ?? '')); ?>">
                </div>
                <div class="va-telescope-field">
                    <label>Bevonatok</label>
                    <input type="text" name="scope_coatings" class="va-input" placeholder="pl. Multi-coated" value="<?php echo esc_attr((string)($edit_meta['scope_coatings'] ?? '')); ?>">
                </div>
                </div>
            </div>

            <?php
            $thermal_palettes_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['thermal_palettes'] ?? '' ) ) ) );
            $thermal_connectivity_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['thermal_connectivity'] ?? '' ) ) ) );
            $thermal_mobile_app_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['thermal_mobile_app'] ?? '' ) ) ) );
            $thermal_extra_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['thermal_extra_features'] ?? '' ) ) ) );
            $thermal_weapon_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['thermal_weapon_compatibility'] ?? '' ) ) ) );
            $thermal_accessories_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['thermal_accessories'] ?? '' ) ) ) );
            ?>
            <div class="va-form-group va-thermal-fields-grid" data-categories="hokamerak" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group"><label>Típus</label><?php $v = (string)($edit_meta['thermal_device_type'] ?? ''); ?><select name="thermal_device_type" class="va-select"><option value="">– Válasszon –</option><option value="kezi-hokamera"<?php selected($v,'kezi-hokamera'); ?>>Kézi hőkamera</option><option value="elotet-clip-on"<?php selected($v,'elotet-clip-on'); ?>>Előtét (clip-on)</option><option value="celtavcso-hokamera"<?php selected($v,'celtavcso-hokamera'); ?>>Céltávcső hőkamera</option><option value="binokular-hokamera"<?php selected($v,'binokular-hokamera'); ?>>Binokulár hőkamera</option><option value="monokular"<?php selected($v,'monokular'); ?>>Monokulár</option><option value="megfigyelo"<?php selected($v,'megfigyelo'); ?>>Megfigyelő</option><option value="kereso"<?php selected($v,'kereso'); ?>>Kereső</option><option value="ipari-hokamera"<?php selected($v,'ipari-hokamera'); ?>>Ipari hőkamera</option><option value="dron-kompatibilis"<?php selected($v,'dron-kompatibilis'); ?>>Drón kompatibilis</option><option value="jarmuves"<?php selected($v,'jarmuves'); ?>>Járműves</option><option value="egyeb"<?php selected($v,'egyeb'); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Állapot</label><?php $v = (string)($edit_meta['thermal_condition'] ?? ''); ?><select name="thermal_condition" class="va-select"><option value="">– Válasszon –</option><option value="uj"<?php selected($v,'uj'); ?>>Új</option><option value="ujszeru"<?php selected($v,'ujszeru'); ?>>Újszerű</option><option value="hasznalt"<?php selected($v,'hasznalt'); ?>>Használt</option><option value="hibas"<?php selected($v,'hibas'); ?>>Hibás</option></select></div>
                    <div class="va-form-group"><label>Garancia</label><?php $v = (string)($edit_meta['thermal_warranty'] ?? ''); ?><select name="thermal_warranty" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v,'igen'); ?>>Igen</option><option value="nem"<?php selected($v,'nem'); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Gyártási év</label><input type="text" name="thermal_manufacture_year" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_manufacture_year'] ?? '')); ?>" placeholder="pl. 2024"></div>

                    <div class="va-form-group"><label>Szenzor felbontás</label><?php $v = (string)($edit_meta['thermal_sensor_resolution'] ?? ''); ?><select name="thermal_sensor_resolution" class="va-select"><option value="">– Válasszon –</option><option value="160x120"<?php selected($v,'160x120'); ?>>160x120</option><option value="256x192"<?php selected($v,'256x192'); ?>>256x192</option><option value="384x288"<?php selected($v,'384x288'); ?>>384x288</option><option value="640x480"<?php selected($v,'640x480'); ?>>640x480</option><option value="1024-plus"<?php selected($v,'1024-plus'); ?>>1024+</option></select></div>
                    <div class="va-form-group"><label>Pixel méret</label><?php $v = (string)($edit_meta['thermal_pixel_pitch'] ?? ''); ?><select name="thermal_pixel_pitch" class="va-select"><option value="">– Válasszon –</option><option value="12um"<?php selected($v,'12um'); ?>>12 um</option><option value="17um"<?php selected($v,'17um'); ?>>17 um</option></select></div>
                    <div class="va-form-group"><label>NETD érzékenység</label><?php $v = (string)($edit_meta['thermal_netd'] ?? ''); ?><select name="thermal_netd" class="va-select"><option value="">– Válasszon –</option><option value="lt20mk"<?php selected($v,'lt20mk'); ?>>&lt;20 mK</option><option value="lt25mk"<?php selected($v,'lt25mk'); ?>>&lt;25 mK</option><option value="lt35mk"<?php selected($v,'lt35mk'); ?>>&lt;35 mK</option><option value="lt40mk"<?php selected($v,'lt40mk'); ?>>&lt;40 mK</option><option value="lt50mk"<?php selected($v,'lt50mk'); ?>>&lt;50 mK</option></select></div>
                    <div class="va-form-group"><label>Frissítési frekvencia</label><?php $v = (string)($edit_meta['thermal_refresh_hz'] ?? ''); ?><select name="thermal_refresh_hz" class="va-select"><option value="">– Válasszon –</option><option value="25hz"<?php selected($v,'25hz'); ?>>25 Hz</option><option value="30hz"<?php selected($v,'30hz'); ?>>30 Hz</option><option value="50hz"<?php selected($v,'50hz'); ?>>50 Hz</option><option value="60hz"<?php selected($v,'60hz'); ?>>60 Hz</option></select></div>

                    <div class="va-form-group"><label>Alap nagyítás</label><input type="text" name="thermal_base_magnification" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_base_magnification'] ?? '')); ?>" placeholder="pl. 2x"></div>
                    <div class="va-form-group"><label>Digitális zoom</label><?php $v = (string)($edit_meta['thermal_digital_zoom'] ?? ''); ?><select name="thermal_digital_zoom" class="va-select"><option value="">– Válasszon –</option><option value="2x"<?php selected($v,'2x'); ?>>2x</option><option value="4x"<?php selected($v,'4x'); ?>>4x</option><option value="8x"<?php selected($v,'8x'); ?>>8x</option><option value="16x"<?php selected($v,'16x'); ?>>16x</option></select></div>
                    <div class="va-form-group"><label>Objektív méret</label><?php $v = (string)($edit_meta['thermal_lens_size'] ?? ''); ?><select name="thermal_lens_size" class="va-select"><option value="">– Válasszon –</option><option value="19"<?php selected($v,'19'); ?>>19 mm</option><option value="25"<?php selected($v,'25'); ?>>25 mm</option><option value="35"<?php selected($v,'35'); ?>>35 mm</option><option value="50"<?php selected($v,'50'); ?>>50 mm</option><option value="75"<?php selected($v,'75'); ?>>75 mm</option></select></div>
                    <div class="va-form-group"><label>Látómező (fok)</label><input type="text" name="thermal_fov_degree" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_fov_degree'] ?? '')); ?>" placeholder="pl. 10.5"></div>
                    <div class="va-form-group"><label>Látómező (m / 100m)</label><input type="text" name="thermal_fov_m100" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_fov_m100'] ?? '')); ?>" placeholder="pl. 18.4"></div>

                    <div class="va-form-group"><label>Felismerési táv (ember, m)</label><input type="text" name="thermal_detect_human_m" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_detect_human_m'] ?? '')); ?>" placeholder="pl. 1800"></div>
                    <div class="va-form-group"><label>Felismerési táv (vad, m)</label><input type="text" name="thermal_detect_game_m" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_detect_game_m'] ?? '')); ?>" placeholder="pl. 1400"></div>
                    <div class="va-form-group"><label>Felismerési táv (jármű, m)</label><input type="text" name="thermal_detect_vehicle_m" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_detect_vehicle_m'] ?? '')); ?>" placeholder="pl. 2500"></div>

                    <div class="va-form-group"><label>Kijelző típusa</label><?php $v = (string)($edit_meta['thermal_display_type'] ?? ''); ?><select name="thermal_display_type" class="va-select"><option value="">– Válasszon –</option><option value="oled"<?php selected($v,'oled'); ?>>OLED</option><option value="amoled"<?php selected($v,'amoled'); ?>>AMOLED</option><option value="lcos"<?php selected($v,'lcos'); ?>>LCOS</option><option value="lcd"<?php selected($v,'lcd'); ?>>LCD</option></select></div>
                    <div class="va-form-group"><label>Kijelző felbontás</label><input type="text" name="thermal_display_resolution" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_display_resolution'] ?? '')); ?>" placeholder="pl. 1024x768"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Színpaletta</label><select name="thermal_palettes[]" class="va-select" multiple data-placeholder="Válassz palettát"><option value="white-hot"<?php echo in_array('white-hot', $thermal_palettes_saved, true) ? ' selected' : ''; ?>>White hot</option><option value="black-hot"<?php echo in_array('black-hot', $thermal_palettes_saved, true) ? ' selected' : ''; ?>>Black hot</option><option value="red-hot"<?php echo in_array('red-hot', $thermal_palettes_saved, true) ? ' selected' : ''; ?>>Red hot</option><option value="rainbow"<?php echo in_array('rainbow', $thermal_palettes_saved, true) ? ' selected' : ''; ?>>Rainbow</option><option value="iron"<?php echo in_array('iron', $thermal_palettes_saved, true) ? ' selected' : ''; ?>>Iron</option></select></div>

                    <div class="va-form-group"><label>Fotó</label><?php $v = (string)($edit_meta['thermal_photo'] ?? ''); ?><select name="thermal_photo" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v,'igen'); ?>>Igen</option><option value="nem"<?php selected($v,'nem'); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Videó</label><?php $v = (string)($edit_meta['thermal_video'] ?? ''); ?><select name="thermal_video" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v,'igen'); ?>>Igen</option><option value="nem"<?php selected($v,'nem'); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Hangrögzítés</label><?php $v = (string)($edit_meta['thermal_audio'] ?? ''); ?><select name="thermal_audio" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v,'igen'); ?>>Igen</option><option value="nem"<?php selected($v,'nem'); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Belső memória (GB)</label><input type="text" name="thermal_internal_memory_gb" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_internal_memory_gb'] ?? '')); ?>" placeholder="pl. 64"></div>
                    <div class="va-form-group"><label>SD kártya támogatás</label><?php $v = (string)($edit_meta['thermal_sd_support'] ?? ''); ?><select name="thermal_sd_support" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v,'igen'); ?>>Igen</option><option value="nem"<?php selected($v,'nem'); ?>>Nem</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Kapcsolat</label><select name="thermal_connectivity[]" class="va-select" multiple data-placeholder="Válassz kapcsolatot"><option value="wifi"<?php echo in_array('wifi', $thermal_connectivity_saved, true) ? ' selected' : ''; ?>>WiFi</option><option value="bluetooth"<?php echo in_array('bluetooth', $thermal_connectivity_saved, true) ? ' selected' : ''; ?>>Bluetooth</option><option value="usb-c"<?php echo in_array('usb-c', $thermal_connectivity_saved, true) ? ' selected' : ''; ?>>USB-C</option><option value="hdmi"<?php echo in_array('hdmi', $thermal_connectivity_saved, true) ? ' selected' : ''; ?>>HDMI</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Mobil app</label><select name="thermal_mobile_app[]" class="va-select" multiple data-placeholder="Válassz app platformot"><option value="android"<?php echo in_array('android', $thermal_mobile_app_saved, true) ? ' selected' : ''; ?>>Android</option><option value="ios"<?php echo in_array('ios', $thermal_mobile_app_saved, true) ? ' selected' : ''; ?>>iOS</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Extra funkciók</label><select name="thermal_extra_features[]" class="va-select" multiple data-placeholder="Válassz extra funkciót"><option value="lrf"<?php echo in_array('lrf', $thermal_extra_saved, true) ? ' selected' : ''; ?>>LRF (lézeres távmérő)</option><option value="ballisztikai-kalkulator"<?php echo in_array('ballisztikai-kalkulator', $thermal_extra_saved, true) ? ' selected' : ''; ?>>Ballisztikai kalkulátor</option><option value="hotspot-tracking"<?php echo in_array('hotspot-tracking', $thermal_extra_saved, true) ? ' selected' : ''; ?>>Hotspot tracking</option><option value="pip"<?php echo in_array('pip', $thermal_extra_saved, true) ? ' selected' : ''; ?>>PIP</option><option value="giroszkop"<?php echo in_array('giroszkop', $thermal_extra_saved, true) ? ' selected' : ''; ?>>Giroszkóp</option><option value="iranytu"<?php echo in_array('iranytu', $thermal_extra_saved, true) ? ' selected' : ''; ?>>Iránytű</option><option value="gps"<?php echo in_array('gps', $thermal_extra_saved, true) ? ' selected' : ''; ?>>GPS</option><option value="video-stream"<?php echo in_array('video-stream', $thermal_extra_saved, true) ? ' selected' : ''; ?>>Videó stream</option></select></div>

                    <div class="va-form-group"><label>Akkumulátor típus</label><?php $v = (string)($edit_meta['thermal_battery_type'] ?? ''); ?><select name="thermal_battery_type" class="va-select"><option value="">– Válasszon –</option><option value="18650"<?php selected($v,'18650'); ?>>18650</option><option value="21700"<?php selected($v,'21700'); ?>>21700</option><option value="beepitett"<?php selected($v,'beepitett'); ?>>Beépített</option><option value="csereheto"<?php selected($v,'csereheto'); ?>>Cserélhető</option></select></div>
                    <div class="va-form-group"><label>Üzemidő (óra)</label><input type="text" name="thermal_runtime_hours" class="va-input" value="<?php echo esc_attr((string)($edit_meta['thermal_runtime_hours'] ?? '')); ?>" placeholder="pl. 8"></div>
                    <div class="va-form-group"><label>Powerbank kompatibilis</label><?php $v = (string)($edit_meta['thermal_powerbank_compatible'] ?? ''); ?><select name="thermal_powerbank_compatible" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v,'igen'); ?>>Igen</option><option value="nem"<?php selected($v,'nem'); ?>>Nem</option></select></div>

                    <div class="va-form-group"><label>IP szabvány</label><?php $v = (string)($edit_meta['thermal_ip_rating'] ?? ''); ?><select name="thermal_ip_rating" class="va-select"><option value="">– Válasszon –</option><option value="ip65"<?php selected($v,'ip65'); ?>>IP65</option><option value="ip66"<?php selected($v,'ip66'); ?>>IP66</option><option value="ip67"<?php selected($v,'ip67'); ?>>IP67</option></select></div>
                    <div class="va-form-group"><label>Ütésállóság</label><?php $v = (string)($edit_meta['thermal_recoil_resistance'] ?? ''); ?><select name="thermal_recoil_resistance" class="va-select"><option value="">– Válasszon –</option><option value="308win"<?php selected($v,'308win'); ?>>.308 Win</option><option value="30-06"<?php selected($v,'30-06'); ?>>.30-06</option><option value="300winmag"<?php selected($v,'300winmag'); ?>>.300 Win Mag</option><option value="12-76"<?php selected($v,'12-76'); ?>>12/76</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Fegyver kompatibilitás</label><select name="thermal_weapon_compatibility[]" class="va-select" multiple data-placeholder="Válassz kompatibilitást"><option value="golyos"<?php echo in_array('golyos', $thermal_weapon_saved, true) ? ' selected' : ''; ?>>Golyós</option><option value="soretes"<?php echo in_array('soretes', $thermal_weapon_saved, true) ? ' selected' : ''; ?>>Sörétes</option><option value="legfegyver"<?php echo in_array('legfegyver', $thermal_weapon_saved, true) ? ' selected' : ''; ?>>Légfegyver</option><option value="airsoft"<?php echo in_array('airsoft', $thermal_weapon_saved, true) ? ' selected' : ''; ?>>Airsoft</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="thermal_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot"><option value="szerulek"<?php echo in_array('szerulek', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Szerelék</option><option value="gyuru"<?php echo in_array('gyuru', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Gyűrű</option><option value="tolto"<?php echo in_array('tolto', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Töltő</option><option value="akkumulator"<?php echo in_array('akkumulator', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Akkumulátor</option><option value="kulso-akku"<?php echo in_array('kulso-akku', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Külső akku</option><option value="tok"<?php echo in_array('tok', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Tok</option><option value="gyari-doboz"<?php echo in_array('gyari-doboz', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Gyári doboz</option><option value="napellenzo"<?php echo in_array('napellenzo', $thermal_accessories_saved, true) ? ' selected' : ''; ?>>Napellenző</option></select></div>
                </div>
            </div>

            <!-- Kés mezők grid -->
            <div class="va-form-group va-knife-fields-grid" data-categories="kesek,bicska-tor-kard,vadaszkes-vadasztor,taktikai-kes-taktikai-tor,konyhakes,svajci-bicska,dobotor,machete-bozotvago,multiszerszam,tok-keslanc,keselezo" style="display:none">
                <div class="va-step2-4col-inner">
                    <?php
                    $knife_type_val = (string) ( $edit_meta['knife_type'] ?? '' );
                    $knife_usage_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_usage'] ?? '' ) ) ) );
                    $knife_features_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_features'] ?? '' ) ) ) );
                    $knife_accessories_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_accessories'] ?? '' ) ) ) );
                    $knife_shipping_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_shipping_methods'] ?? '' ) ) ) );
                    $knife_media_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_required_media'] ?? '' ) ) ) );
                    $knife_collectible_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_collectible_flags'] ?? '' ) ) ) );
                    $knife_moderation_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['knife_moderation_flags'] ?? '' ) ) ) );
                    $knife_blade_mm_val = (string) ( $edit_meta['knife_blade_length_mm'] ?? '' );
                    if ( $knife_blade_mm_val === '' ) {
                        $legacy_cm = (string) ( $edit_meta['knife_blade_length'] ?? '' );
                        if ( is_numeric( $legacy_cm ) ) {
                            $knife_blade_mm_val = (string) ( (int) round( (float) $legacy_cm * 10 ) );
                        }
                    }
                    ?>
                    <div class="va-form-group">
                        <label>Kés típusa</label>
                        <select name="knife_type" id="va-knife-type" class="va-select">
                            <option value="">- Válasszon típust -</option>
                            <option value="vadaszkes"<?php selected( $knife_type_val, 'vadaszkes' ); ?>>Vadászkés</option>
                            <option value="bicska"<?php selected( $knife_type_val, 'bicska' ); ?>>Bicska</option>
                            <option value="fix-penges"<?php selected( $knife_type_val, 'fix-penges' ); ?>>Fix pengés</option>
                            <option value="bushcraft-kes"<?php selected( $knife_type_val, 'bushcraft-kes' ); ?>>Bushcraft kés</option>
                            <option value="tulelo-kes"<?php selected( $knife_type_val, 'tulelo-kes' ); ?>>Túlélőkés</option>
                            <option value="nyuzokes"<?php selected( $knife_type_val, 'nyuzokes' ); ?>>Nyúzókés</option>
                            <option value="filezo-kes"<?php selected( $knife_type_val, 'filezo-kes' ); ?>>Filéző kés</option>
                            <option value="machete"<?php selected( $knife_type_val, 'machete' ); ?>>Machete</option>
                            <option value="balta"<?php selected( $knife_type_val, 'balta' ); ?>>Balta</option>
                            <option value="multitool"<?php selected( $knife_type_val, 'multitool' ); ?>>Multitool</option>
                            <option value="zsebkes"<?php selected( $knife_type_val, 'zsebkes' ); ?>>Zsebkés</option>
                            <option value="gyujtoi-kes"<?php selected( $knife_type_val, 'gyujtoi-kes' ); ?>>Gyűjtői kés</option>
                            <option value="diszkes"<?php selected( $knife_type_val, 'diszkes' ); ?>>Díszkés</option>
                            <option value="taktikai-kes"<?php selected( $knife_type_val, 'taktikai-kes' ); ?>>Taktikai kés</option>
                            <option value="dobokes"<?php selected( $knife_type_val, 'dobokes' ); ?>>Dobókés</option>
                            <option value="egyeb"<?php selected( $knife_type_val, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Állapot</label>
                        <?php $v = (string) ( $edit_meta['knife_condition'] ?? '' ); ?>
                        <select name="knife_condition" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option>
                            <option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option>
                            <option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option>
                            <option value="restauralt"<?php selected( $v, 'restauralt' ); ?>>Restaurált</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Gyártási év (opcionális)</label>
                        <input type="text" name="knife_manufacture_year" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_manufacture_year'] ?? '' ) ); ?>" placeholder="pl. 2022">
                    </div>
                    <div class="va-form-group">
                        <label>Teljes hossz (cm)</label>
                        <input type="text" name="knife_total_length_cm" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_total_length_cm'] ?? '' ) ); ?>" placeholder="pl. 24">
                    </div>
                    <div class="va-form-group">
                        <label>Pengehossz (mm)</label>
                        <input type="text" name="knife_blade_length_mm" class="va-input" value="<?php echo esc_attr( $knife_blade_mm_val ); ?>" placeholder="pl. 110">
                    </div>
                    <div class="va-form-group">
                        <label>Pengevastagság (mm)</label>
                        <input type="text" name="knife_blade_thickness_mm" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_blade_thickness_mm'] ?? '' ) ); ?>" placeholder="pl. 3.8">
                    </div>
                    <div class="va-form-group">
                        <label>Súly (g)</label>
                        <input type="text" name="knife_weight_g" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_weight_g'] ?? '' ) ); ?>" placeholder="pl. 185">
                    </div>
                    <div class="va-form-group">
                        <label>Acél</label>
                        <?php $v = (string) ( $edit_meta['knife_steel_type'] ?? '' ); ?>
                        <select name="knife_steel_type" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="420hc"<?php selected( $v, '420hc' ); ?>>420HC</option>
                            <option value="440a"<?php selected( $v, '440a' ); ?>>440A</option>
                            <option value="440c"<?php selected( $v, '440c' ); ?>>440C</option>
                            <option value="d2"<?php selected( $v, 'd2' ); ?>>D2</option>
                            <option value="n690"<?php selected( $v, 'n690' ); ?>>N690</option>
                            <option value="bohler-m390"<?php selected( $v, 'bohler-m390' ); ?>>Böhler M390</option>
                            <option value="cpm-s30v"<?php selected( $v, 'cpm-s30v' ); ?>>CPM S30V</option>
                            <option value="cpm-s35vn"<?php selected( $v, 'cpm-s35vn' ); ?>>CPM S35VN</option>
                            <option value="elmax"<?php selected( $v, 'elmax' ); ?>>Elmax</option>
                            <option value="aus-8"<?php selected( $v, 'aus-8' ); ?>>AUS-8</option>
                            <option value="vg10"<?php selected( $v, 'vg10' ); ?>>VG10</option>
                            <option value="damaszk"<?php selected( $v, 'damaszk' ); ?>>Damaszk</option>
                            <option value="szenacel"<?php selected( $v, 'szenacel' ); ?>>Szénacél</option>
                            <option value="rozsdamentes"<?php selected( $v, 'rozsdamentes' ); ?>>Rozsdamentes</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Penge forma</label>
                        <?php $v = (string) ( $edit_meta['knife_blade_shape'] ?? '' ); ?>
                        <select name="knife_blade_shape" class="va-select">
                            <option value="">- Válasszon -</option>
                            <option value="drop-point"<?php selected( $v, 'drop-point' ); ?>>Drop point</option>
                            <option value="clip-point"<?php selected( $v, 'clip-point' ); ?>>Clip point</option>
                            <option value="tanto"<?php selected( $v, 'tanto' ); ?>>Tanto</option>
                            <option value="spear-point"<?php selected( $v, 'spear-point' ); ?>>Spear point</option>
                            <option value="trailing-point"<?php selected( $v, 'trailing-point' ); ?>>Trailing point</option>
                            <option value="skinner"<?php selected( $v, 'skinner' ); ?>>Skinner</option>
                            <option value="kukri"<?php selected( $v, 'kukri' ); ?>>Kukri</option>
                            <option value="wharncliffe"<?php selected( $v, 'wharncliffe' ); ?>>Wharncliffe</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Penge él</label>
                        <?php $v = (string) ( $edit_meta['knife_blade_edge'] ?? '' ); ?>
                        <select name="knife_blade_edge" class="va-select"><option value="">- Válasszon -</option><option value="sima"<?php selected( $v, 'sima' ); ?>>Sima</option><option value="reces"<?php selected( $v, 'reces' ); ?>>Recés</option><option value="kombinalt"<?php selected( $v, 'kombinalt' ); ?>>Kombinált</option></select>
                    </div>
                    <div class="va-form-group">
                        <label>Felület</label>
                        <?php $v = (string) ( $edit_meta['knife_finish'] ?? '' ); ?>
                        <select name="knife_finish" class="va-select"><option value="">- Válasszon -</option><option value="satin"<?php selected( $v, 'satin' ); ?>>Satin</option><option value="stonewash"<?php selected( $v, 'stonewash' ); ?>>Stonewash</option><option value="black-coated"<?php selected( $v, 'black-coated' ); ?>>Black coated</option><option value="dlc"<?php selected( $v, 'dlc' ); ?>>DLC</option><option value="bead-blasted"<?php selected( $v, 'bead-blasted' ); ?>>Bead blasted</option></select>
                    </div>
                    <div class="va-form-group">
                        <label>Konstrukció</label>
                        <?php $v = (string) ( $edit_meta['knife_construction'] ?? '' ); ?>
                        <select name="knife_construction" class="va-select"><option value="">- Válasszon -</option><option value="full-tang"<?php selected( $v, 'full-tang' ); ?>>Full tang</option><option value="partial-tang"<?php selected( $v, 'partial-tang' ); ?>>Partial tang</option><option value="folding"<?php selected( $v, 'folding' ); ?>>Folding</option></select>
                    </div>
                    <div class="va-form-group va-knife-folding-only" style="display:none;">
                        <label>Zár típusa</label>
                        <?php $v = (string) ( $edit_meta['knife_locking_type'] ?? '' ); ?>
                        <select name="knife_locking_type" class="va-select"><option value="">- Válasszon -</option><option value="liner-lock"<?php selected( $v, 'liner-lock' ); ?>>Liner lock</option><option value="frame-lock"<?php selected( $v, 'frame-lock' ); ?>>Frame lock</option><option value="back-lock"<?php selected( $v, 'back-lock' ); ?>>Back lock</option><option value="axis-lock"<?php selected( $v, 'axis-lock' ); ?>>Axis lock</option><option value="slipjoint"<?php selected( $v, 'slipjoint' ); ?>>Slipjoint</option></select>
                    </div>
                    <div class="va-form-group va-knife-folding-only" style="display:none;">
                        <label>Egykezes nyitás</label>
                        <?php $v = (string) ( $edit_meta['knife_one_hand_opening'] ?? '' ); ?>
                        <select name="knife_one_hand_opening" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group">
                        <label>Markolat anyag</label>
                        <?php $v = (string) ( $edit_meta['knife_handle_material'] ?? '' ); ?>
                        <select name="knife_handle_material" class="va-select"><option value="">- Válasszon -</option><option value="fa"<?php selected( $v, 'fa' ); ?>>Fa</option><option value="g10"<?php selected( $v, 'g10' ); ?>>G10</option><option value="micarta"<?php selected( $v, 'micarta' ); ?>>Micarta</option><option value="gumi"<?php selected( $v, 'gumi' ); ?>>Gumi</option><option value="csont"<?php selected( $v, 'csont' ); ?>>Csont</option><option value="agancs"<?php selected( $v, 'agancs' ); ?>>Agancs</option><option value="aluminium"<?php selected( $v, 'aluminium' ); ?>>Aluminium</option><option value="titan"<?php selected( $v, 'titan' ); ?>>Titán</option><option value="karbon"<?php selected( $v, 'karbon' ); ?>>Karbon</option></select>
                    </div>
                    <div class="va-form-group"><label>Markolat szín</label><input type="text" name="knife_handle_color" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_handle_color'] ?? '' ) ); ?>" placeholder="pl. fekete"></div>
                    <div class="va-form-group"><label>Tok van?</label><?php $v = (string) ( $edit_meta['knife_has_sheath'] ?? '' ); ?><select name="knife_has_sheath" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Tok típusa</label><?php $v = (string) ( $edit_meta['knife_sheath_type'] ?? '' ); ?><select name="knife_sheath_type" class="va-select"><option value="">- Válasszon -</option><option value="bor"<?php selected( $v, 'bor' ); ?>>Bőr</option><option value="kydex"<?php selected( $v, 'kydex' ); ?>>Kydex</option><option value="cordura"<?php selected( $v, 'cordura' ); ?>>Cordura</option><option value="muanyag"<?php selected( $v, 'muanyag' ); ?>>Műanyag</option></select></div>
                    <div class="va-form-group"><label>Övre fűzhető?</label><?php $v = (string) ( $edit_meta['knife_belt_carry'] ?? '' ); ?><select name="knife_belt_carry" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Felhasználás</label><select name="knife_usage[]" class="va-select" multiple data-placeholder="Válassz felhasználást"><option value="vadaszat"<?php echo in_array( 'vadaszat', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Vadászat</option><option value="bushcraft"<?php echo in_array( 'bushcraft', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Bushcraft</option><option value="tuleles"<?php echo in_array( 'tuleles', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Túlélés</option><option value="horgaszat"<?php echo in_array( 'horgaszat', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Horgászat</option><option value="tura"<?php echo in_array( 'tura', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Túra</option><option value="taktikai"<?php echo in_array( 'taktikai', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Taktikai</option><option value="edc"<?php echo in_array( 'edc', $knife_usage_saved, true ) ? ' selected' : ''; ?>>EDC</option><option value="gyujtoi"<?php echo in_array( 'gyujtoi', $knife_usage_saved, true ) ? ' selected' : ''; ?>>Gyűjtői</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tulajdonságok</label><select name="knife_features[]" class="va-select" multiple data-placeholder="Válassz tulajdonságot"><option value="rozsdamentes"<?php echo in_array( 'rozsdamentes', $knife_features_saved, true ) ? ' selected' : ''; ?>>Rozsdamentes</option><option value="vizallo-markolat"<?php echo in_array( 'vizallo-markolat', $knife_features_saved, true ) ? ' selected' : ''; ?>>Vízálló markolat</option><option value="tuzgyujto-kompatibilis"<?php echo in_array( 'tuzgyujto-kompatibilis', $knife_features_saved, true ) ? ' selected' : ''; ?>>Tűzgyújtó kompatibilis</option><option value="uvegtoro"<?php echo in_array( 'uvegtoro', $knife_features_saved, true ) ? ' selected' : ''; ?>>Üvegtörő</option><option value="zsinorvago"<?php echo in_array( 'zsinorvago', $knife_features_saved, true ) ? ' selected' : ''; ?>>Zsinórvágó</option></select></div>
                    <div class="va-form-group"><label>Korhatár</label><?php $v = (string) ( $edit_meta['knife_age_restriction'] ?? '' ); ?><select name="knife_age_restriction" class="va-select"><option value="">- Válasszon -</option><option value="18plus"<?php selected( $v, '18plus' ); ?>>18+</option></select></div>
                    <div class="va-form-group"><label>Korlátozott?</label><?php $v = (string) ( $edit_meta['knife_restricted'] ?? '' ); ?><select name="knife_restricted" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Közterületen hordható?</label><?php $v = (string) ( $edit_meta['knife_public_carry'] ?? '' ); ?><select name="knife_public_carry" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="knife_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot"><option value="eredeti-doboz"<?php echo in_array( 'eredeti-doboz', $knife_accessories_saved, true ) ? ' selected' : ''; ?>>Eredeti doboz</option><option value="fenoko"<?php echo in_array( 'fenoko', $knife_accessories_saved, true ) ? ' selected' : ''; ?>>Fenőkő</option><option value="firesteel"<?php echo in_array( 'firesteel', $knife_accessories_saved, true ) ? ' selected' : ''; ?>>Firesteel</option><option value="extra-tok"<?php echo in_array( 'extra-tok', $knife_accessories_saved, true ) ? ' selected' : ''; ?>>Extra tok</option><option value="tanusitvany"<?php echo in_array( 'tanusitvany', $knife_accessories_saved, true ) ? ' selected' : ''; ?>>Tanúsítvány</option></select></div>
                    <div class="va-form-group"><label>Ár: alku</label><?php $v = (string) ( $edit_meta['knife_price_deal'] ?? '' ); ?><select name="knife_price_deal" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Ár: csere</label><?php $v = (string) ( $edit_meta['knife_price_exchange'] ?? '' ); ?><select name="knife_price_exchange" class="va-select"><option value="">- Válasszon -</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Szállítás</label><select name="knife_shipping_methods[]" class="va-select" multiple data-placeholder="Válassz szállítást"><option value="posta"<?php echo in_array( 'posta', $knife_shipping_saved, true ) ? ' selected' : ''; ?>>Posta</option><option value="futar"<?php echo in_array( 'futar', $knife_shipping_saved, true ) ? ' selected' : ''; ?>>Futár</option><option value="szemelyes"<?php echo in_array( 'szemelyes', $knife_shipping_saved, true ) ? ' selected' : ''; ?>>Személyes</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Kötelező képek</label><select name="knife_required_media[]" class="va-select" multiple data-placeholder="Válassz képlistát"><option value="teljes-nezet"<?php echo in_array( 'teljes-nezet', $knife_media_saved, true ) ? ' selected' : ''; ?>>Teljes nézet</option><option value="penge"<?php echo in_array( 'penge', $knife_media_saved, true ) ? ' selected' : ''; ?>>Penge</option><option value="markolat"<?php echo in_array( 'markolat', $knife_media_saved, true ) ? ' selected' : ''; ?>>Markolat</option><option value="el-kozelrol"<?php echo in_array( 'el-kozelrol', $knife_media_saved, true ) ? ' selected' : ''; ?>>Él közelről</option><option value="tok"<?php echo in_array( 'tok', $knife_media_saved, true ) ? ' selected' : ''; ?>>Tok</option></select></div>
                    <div class="va-form-group"><label>Élezési állapot</label><?php $v = (string) ( $edit_meta['knife_sharpening_state'] ?? '' ); ?><select name="knife_sharpening_state" class="va-select"><option value="">- Válasszon -</option><option value="gyari-el"<?php selected( $v, 'gyari-el' ); ?>>Gyári él</option><option value="borotvaeles"<?php selected( $v, 'borotvaeles' ); ?>>Borotvaéles</option><option value="hasznalati-el"<?php selected( $v, 'hasznalati-el' ); ?>>Használati él</option><option value="elezendo"<?php selected( $v, 'elezendo' ); ?>>Élezendő</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Gyűjtői extra</label><select name="knife_collectible_flags[]" class="va-select" multiple data-placeholder="Válassz gyűjtői jellemzőt"><option value="limitalt-szeria"<?php echo in_array( 'limitalt-szeria', $knife_collectible_saved, true ) ? ' selected' : ''; ?>>Limitált széria</option><option value="sorszamozott"<?php echo in_array( 'sorszamozott', $knife_collectible_saved, true ) ? ' selected' : ''; ?>>Sorszámozott</option><option value="custom-maker"<?php echo in_array( 'custom-maker', $knife_collectible_saved, true ) ? ' selected' : ''; ?>>Custom maker</option><option value="kezmuves"<?php echo in_array( 'kezmuves', $knife_collectible_saved, true ) ? ' selected' : ''; ?>>Kézműves</option><option value="tanusitvannyal"<?php echo in_array( 'tanusitvannyal', $knife_collectible_saved, true ) ? ' selected' : ''; ?>>Tanúsítvánnyal</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Moderációs jelölők</label><select name="knife_moderation_flags[]" class="va-select" multiple data-placeholder="Automatikus ellenőrzéshez"><option value="automata-kes"<?php echo in_array( 'automata-kes', $knife_moderation_saved, true ) ? ' selected' : ''; ?>>Automata kés</option><option value="rugoskes"<?php echo in_array( 'rugoskes', $knife_moderation_saved, true ) ? ' selected' : ''; ?>>Rugóskés</option><option value="illegalis-penge"<?php echo in_array( 'illegalis-penge', $knife_moderation_saved, true ) ? ' selected' : ''; ?>>Illegális penge</option><option value="rejtett-penge"<?php echo in_array( 'rejtett-penge', $knife_moderation_saved, true ) ? ' selected' : ''; ?>>Rejtett penge</option></select></div>
                    <div class="va-form-group va-knife-axe-only" style="display:none;"><label>Fej súlya (g)</label><input type="text" name="knife_axe_head_weight_g" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_axe_head_weight_g'] ?? '' ) ); ?>" placeholder="pl. 650"></div>
                    <div class="va-form-group va-knife-axe-only" style="display:none;"><label>Nyél anyaga</label><input type="text" name="knife_axe_handle_material" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_axe_handle_material'] ?? '' ) ); ?>" placeholder="pl. hikorifa"></div>
                    <div class="va-form-group va-knife-multitool-only" style="display:none;"><label>Funkciók száma</label><input type="text" name="knife_multitool_function_count" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['knife_multitool_function_count'] ?? '' ) ); ?>" placeholder="pl. 17"></div>
                </div>
            </div>

            <!-- Trófeaalátét mezők grid -->
            <?php
            $trophy_multi = static function( string $key ) use ( $edit_meta ) {
                return array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta[ $key ] ?? '' ) ) ) );
            };
            $trophy_species_saved = $trophy_multi( 'trophy_compatible_species' );
            $trophy_decoration_saved = $trophy_multi( 'trophy_decoration_flags' );
            $trophy_usage_saved = $trophy_multi( 'trophy_usage_context' );
            $trophy_accessories_saved = $trophy_multi( 'trophy_accessories' );
            $trophy_shipping_saved = $trophy_multi( 'trophy_shipping_methods' );
            $trophy_moderation_saved = $trophy_multi( 'trophy_moderation_flags' );
            ?>
            <div class="va-form-group va-trophy-fields-grid" data-categories="trofea-aletet" style="display:none">
                <div class="va-step2-4col-inner">
                <div class="va-form-group">
                    <label>Típusa</label>
                    <?php $v = (string)($edit_meta['trophy_type'] ?? $edit_meta['trophy_mount_type'] ?? ''); ?>
                    <select id="va-trophy-type" name="trophy_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="agancs-alatet"<?php selected($v, 'agancs-alatet'); ?>>Agancs alátét</option>
                        <option value="koponya-alatet"<?php selected($v, 'koponya-alatet'); ?>>Koponya alátét</option>
                        <option value="pajzs-shield"<?php selected($v, 'pajzs-shield'); ?>>Pajzs (shield)</option>
                        <option value="faragott-fa-alatet"<?php selected($v, 'faragott-fa-alatet'); ?>>Faragott fa alátét</option>
                        <option value="modern-design-alatet"<?php selected($v, 'modern-design-alatet'); ?>>Modern design alátét</option>
                        <option value="rusztikus-alatet"<?php selected($v, 'rusztikus-alatet'); ?>>Rusztikus alátét</option>
                        <option value="falra-szerelheto-panel"<?php selected($v, 'falra-szerelheto-panel'); ?>>Falra szerelhető panel</option>
                        <option value="allo-trofea-tarto"<?php selected($v, 'allo-trofea-tarto'); ?>>Álló trófea tartó</option>
                        <option value="kombinalt-koponya-agancs"<?php selected($v, 'kombinalt-koponya-agancs'); ?>>Kombinált (koponya + agancs)</option>
                        <option value="egyedi-keszitesu"<?php selected($v, 'egyedi-keszitesu'); ?>>Egyedi készítésű</option>
                    </select>
                </div>
                <div class="va-form-group"><label>Készítő / márka</label><input type="text" name="trophy_maker" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_maker'] ?? '')); ?>" placeholder="pl. Vadfa Műhely"></div>
                <div class="va-form-group"><label>Kézzel készített?</label><?php $v = (string)($edit_meta['trophy_custom_made'] ?? ''); ?><select id="va-trophy-custom-made" name="trophy_custom_made" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group"><label>Állapot</label><?php $v = (string)($edit_meta['trophy_condition'] ?? ''); ?><select name="trophy_condition" class="va-select"><option value="">– Válasszon –</option><option value="uj"<?php selected($v, 'uj'); ?>>Új</option><option value="ujszeru"<?php selected($v, 'ujszeru'); ?>>Újszerű</option><option value="hasznalt"<?php selected($v, 'hasznalt'); ?>>Használt</option><option value="restauralt"<?php selected($v, 'restauralt'); ?>>Restaurált</option></select></div>
                <div class="va-form-group"><label>Gyártási év (opcionális)</label><input type="text" name="trophy_manufacture_year" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_manufacture_year'] ?? '')); ?>" placeholder="pl. 2024"></div>
                <div class="va-form-group"><label>Magasság (cm)</label><input type="text" name="trophy_height_cm" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_height_cm'] ?? '')); ?>" placeholder="pl. 35"></div>
                <div class="va-form-group"><label>Szélesség (cm)</label><input type="text" name="trophy_width_cm" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_width_cm'] ?? '')); ?>" placeholder="pl. 22"></div>
                <div class="va-form-group"><label>Vastagság (cm)</label><input type="text" name="trophy_thickness_cm" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_thickness_cm'] ?? '')); ?>" placeholder="pl. 2.8"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Trófea kompatibilitás</label><select name="trophy_compatible_species[]" class="va-select" multiple data-placeholder="Válassz vadfajt"><option value="oz"<?php echo in_array( 'oz', $trophy_species_saved, true ) ? ' selected' : ''; ?>>Őz</option><option value="gimszarvas"<?php echo in_array( 'gimszarvas', $trophy_species_saved, true ) ? ' selected' : ''; ?>>Gímszarvas</option><option value="dam"<?php echo in_array( 'dam', $trophy_species_saved, true ) ? ' selected' : ''; ?>>Dám</option><option value="muflon"<?php echo in_array( 'muflon', $trophy_species_saved, true ) ? ' selected' : ''; ?>>Muflon</option><option value="vaddiszno-koponya"<?php echo in_array( 'vaddiszno-koponya', $trophy_species_saved, true ) ? ' selected' : ''; ?>>Vaddisznó koponya</option><option value="egyeb"<?php echo in_array( 'egyeb', $trophy_species_saved, true ) ? ' selected' : ''; ?>>Egyéb</option></select></div>
                <div class="va-form-group"><label>Alapanyag</label><?php $v = (string)($edit_meta['trophy_material_type'] ?? $edit_meta['trophy_material'] ?? ''); ?><select name="trophy_material_type" class="va-select"><option value="">– Válasszon –</option><option value="tolgy"<?php selected($v, 'tolgy'); ?>>Tölgy</option><option value="bukk"<?php selected($v, 'bukk'); ?>>Bükk</option><option value="dio"<?php selected($v, 'dio'); ?>>Dió</option><option value="akac"<?php selected($v, 'akac'); ?>>Akác</option><option value="fenyo"<?php selected($v, 'fenyo'); ?>>Fenyő</option><option value="koris"<?php selected($v, 'koris'); ?>>Kőris</option><option value="mahagoni"<?php selected($v, 'mahagoni'); ?>>Mahagóni</option><option value="mdf"<?php selected($v, 'mdf'); ?>>MDF</option><option value="retegelt-lemez"<?php selected($v, 'retegelt-lemez'); ?>>Rétegelt lemez</option><option value="fem"<?php selected($v, 'fem'); ?>>Fém</option><option value="kombinalt"<?php selected($v, 'kombinalt'); ?>>Kombinált</option></select></div>
                <div class="va-form-group"><label>Felületkezelés</label><?php $v = (string)($edit_meta['trophy_surface_finish'] ?? $edit_meta['trophy_finish'] ?? ''); ?><select name="trophy_surface_finish" class="va-select"><option value="">– Válasszon –</option><option value="natur"<?php selected($v, 'natur'); ?>>Natúr</option><option value="lakkozott"<?php selected($v, 'lakkozott'); ?>>Lakkozott</option><option value="pacolt"<?php selected($v, 'pacolt'); ?>>Pácolt</option><option value="olajozott"<?php selected($v, 'olajozott'); ?>>Olajozott</option><option value="egetett"<?php selected($v, 'egetett'); ?>>Égetett (shou sugi ban)</option><option value="festett"<?php selected($v, 'festett'); ?>>Festett</option></select></div>
                <div class="va-form-group"><label>Stílus</label><?php $v = (string)($edit_meta['trophy_style'] ?? ''); ?><select name="trophy_style" class="va-select"><option value="">– Válasszon –</option><option value="klasszikus"<?php selected($v, 'klasszikus'); ?>>Klasszikus</option><option value="rusztikus"<?php selected($v, 'rusztikus'); ?>>Rusztikus</option><option value="modern"<?php selected($v, 'modern'); ?>>Modern</option><option value="minimalista"<?php selected($v, 'minimalista'); ?>>Minimalista</option><option value="vadasz-lodge"<?php selected($v, 'vadasz-lodge'); ?>>Vadász lodge</option><option value="barokk"<?php selected($v, 'barokk'); ?>>Barokk</option><option value="kezmuves"<?php selected($v, 'kezmuves'); ?>>Kézműves</option><option value="luxus"<?php selected($v, 'luxus'); ?>>Luxus</option></select></div>
                <div class="va-form-group"><label>Rögzítés típusa</label><?php $v = (string)($edit_meta['trophy_mounting_type'] ?? ''); ?><select name="trophy_mounting_type" class="va-select"><option value="">– Válasszon –</option><option value="falra-szerelheto"<?php selected($v, 'falra-szerelheto'); ?>>Falra szerelhető</option><option value="csavaros"<?php selected($v, 'csavaros'); ?>>Csavaros</option><option value="kampos"<?php selected($v, 'kampos'); ?>>Kampós</option><option value="rejtett-rogzites"<?php selected($v, 'rejtett-rogzites'); ?>>Rejtett rögzítés</option><option value="allo"<?php selected($v, 'allo'); ?>>Álló</option></select></div>
                <div class="va-form-group"><label>Trófea rögzítés</label><?php $v = (string)($edit_meta['trophy_attachment_type'] ?? ''); ?><select name="trophy_attachment_type" class="va-select"><option value="">– Válasszon –</option><option value="koponya-rogzito"<?php selected($v, 'koponya-rogzito'); ?>>Koponya rögzítő</option><option value="agancs-bilincs"<?php selected($v, 'agancs-bilincs'); ?>>Agancs bilincs</option><option value="univerzalis"<?php selected($v, 'univerzalis'); ?>>Univerzális</option><option value="egyedi-furas"<?php selected($v, 'egyedi-furas'); ?>>Egyedi fúrás</option><option value="allithato"<?php selected($v, 'allithato'); ?>>Állítható</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Díszítés</label><select name="trophy_decoration_flags[]" class="va-select" multiple data-placeholder="Válassz díszítést"><option value="faragas"<?php echo in_array( 'faragas', $trophy_decoration_saved, true ) ? ' selected' : ''; ?>>Faragás</option><option value="gravirozas"<?php echo in_array( 'gravirozas', $trophy_decoration_saved, true ) ? ' selected' : ''; ?>>Gravírozás</option><option value="nevmezo"<?php echo in_array( 'nevmezo', $trophy_decoration_saved, true ) ? ' selected' : ''; ?>>Névmező</option><option value="evszam"<?php echo in_array( 'evszam', $trophy_decoration_saved, true ) ? ' selected' : ''; ?>>Évszám</option><option value="vadfaj-szimbolum"<?php echo in_array( 'vadfaj-szimbolum', $trophy_decoration_saved, true ) ? ' selected' : ''; ?>>Vadfaj szimbólum</option><option value="pajzs-forma"<?php echo in_array( 'pajzs-forma', $trophy_decoration_saved, true ) ? ' selected' : ''; ?>>Pajzs forma</option></select></div>
                <div class="va-form-group"><label>Súly (kg)</label><input type="text" name="trophy_weight_kg" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_weight_kg'] ?? '')); ?>" placeholder="pl. 1.8"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Felhasználás</label><select name="trophy_usage_context[]" class="va-select" multiple data-placeholder="Válassz felhasználást"><option value="nappali-dekor"<?php echo in_array( 'nappali-dekor', $trophy_usage_saved, true ) ? ' selected' : ''; ?>>Nappali dekor</option><option value="vadaszhaz"<?php echo in_array( 'vadaszhaz', $trophy_usage_saved, true ) ? ' selected' : ''; ?>>Vadászház</option><option value="kiallitas"<?php echo in_array( 'kiallitas', $trophy_usage_saved, true ) ? ' selected' : ''; ?>>Kiállítás</option><option value="ajandek"<?php echo in_array( 'ajandek', $trophy_usage_saved, true ) ? ' selected' : ''; ?>>Ajándék</option><option value="trofea-bemutato"<?php echo in_array( 'trofea-bemutato', $trophy_usage_saved, true ) ? ' selected' : ''; ?>>Trófea bemutató</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="trophy_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot"><option value="rogzito-csavar"<?php echo in_array( 'rogzito-csavar', $trophy_accessories_saved, true ) ? ' selected' : ''; ?>>Rögzítő csavar</option><option value="sablon"<?php echo in_array( 'sablon', $trophy_accessories_saved, true ) ? ' selected' : ''; ?>>Sablon</option><option value="tipli"<?php echo in_array( 'tipli', $trophy_accessories_saved, true ) ? ' selected' : ''; ?>>Tipli</option><option value="szerelesi-utmutato"<?php echo in_array( 'szerelesi-utmutato', $trophy_accessories_saved, true ) ? ' selected' : ''; ?>>Szerelési útmutató</option></select></div>
                <div class="va-form-group"><label>Ár: alku</label><?php $v = (string)($edit_meta['trophy_price_negotiable'] ?? ''); ?><select name="trophy_price_negotiable" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group"><label>Egyedi rendelés</label><?php $v = (string)($edit_meta['trophy_custom_order'] ?? ''); ?><select name="trophy_custom_order" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Szállítás</label><select name="trophy_shipping_methods[]" class="va-select" multiple data-placeholder="Válassz szállítást"><option value="posta"<?php echo in_array( 'posta', $trophy_shipping_saved, true ) ? ' selected' : ''; ?>>Posta</option><option value="futar"<?php echo in_array( 'futar', $trophy_shipping_saved, true ) ? ' selected' : ''; ?>>Futár</option><option value="szemelyes"<?php echo in_array( 'szemelyes', $trophy_shipping_saved, true ) ? ' selected' : ''; ?>>Személyes</option><option value="torekeny-kezeles"<?php echo in_array( 'torekeny-kezeles', $trophy_shipping_saved, true ) ? ' selected' : ''; ?>>Törékeny kezelés</option></select></div>
                <div class="va-form-group"><label>Törékeny kezelés</label><?php $v = (string)($edit_meta['trophy_fragile_handling'] ?? ''); ?><select name="trophy_fragile_handling" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group"><label>Vadászház stílus illesztés</label><?php $v = (string)($edit_meta['trophy_house_style'] ?? ''); ?><select name="trophy_house_style" class="va-select"><option value="">– Válasszon –</option><option value="modern"<?php selected($v, 'modern'); ?>>Modern</option><option value="klasszikus"<?php selected($v, 'klasszikus'); ?>>Klasszikus</option><option value="rusztikus"<?php selected($v, 'rusztikus'); ?>>Rusztikus</option></select></div>
                <div class="va-form-group va-trophy-dynamic-group" data-trophy-custom="igen" style="display:none;"><label>Elkészítési idő</label><input type="text" name="trophy_lead_time" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_lead_time'] ?? '')); ?>" placeholder="pl. 10-14 nap"></div>
                <div class="va-form-group va-trophy-dynamic-group" data-trophy-custom="igen" style="display:none;"><label>Rendelhető méret</label><input type="text" name="trophy_orderable_sizes" class="va-input" value="<?php echo esc_attr((string)($edit_meta['trophy_orderable_sizes'] ?? '')); ?>" placeholder="pl. S / M / L / egyedi"></div>
                <div class="va-form-group va-trophy-dynamic-group" data-trophy-custom="igen" style="display:none;"><label>Egyedi gravírozás</label><?php $v = (string)($edit_meta['trophy_custom_engraving'] ?? ''); ?><select name="trophy_custom_engraving" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Moderációs flag</label><select name="trophy_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázatos jelölések"><option value="vedett-fafaj-illegalis-kereskedelem"<?php echo in_array( 'vedett-fafaj-illegalis-kereskedelem', $trophy_moderation_saved, true ) ? ' selected' : ''; ?>>Védett fafaj illegális kereskedelem</option><option value="hamis-kezmuves-eredet"<?php echo in_array( 'hamis-kezmuves-eredet', $trophy_moderation_saved, true ) ? ' selected' : ''; ?>>Hamis kézműves eredet</option><option value="trofea-manipulacio"<?php echo in_array( 'trofea-manipulacio', $trophy_moderation_saved, true ) ? ' selected' : ''; ?>>Trófea manipuláció</option></select></div>
                </div><!-- /va-step2-4col-inner -->
            </div><!-- /.va-trophy-fields-grid -->

            <!-- Szállás mezők grid -->
            <div class="va-form-group va-accommodation-fields-grid" data-categories="szallas,ingatlan,ingatlan-szallas" style="display:none">
                <div class="va-step2-4col-inner">
                <?php
                $hour_options = [];
                for ( $h = 0; $h < 24; $h++ ) {
                    $hour_options[] = sprintf( '%02d:00', $h );
                }
                $accommodation_type_val = (string)($edit_meta['accommodation_type'] ?? '');
                $accommodation_capacity_val = (string)($edit_meta['accommodation_capacity'] ?? '');
                $accommodation_meal_type_val = (string)($edit_meta['accommodation_meal_type'] ?? '');
                $accommodation_features_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_features'] ?? ''))));
                $accommodation_nearby_species_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_nearby_species'] ?? ''))));
                $accommodation_programs_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_programs'] ?? ''))));
                $accommodation_environment_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_environment'] ?? ''))));
                $accommodation_utilities_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_utilities'] ?? ''))));
                $accommodation_accessibility_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_accessibility'] ?? ''))));
                $accommodation_hunting_features_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_hunting_features'] ?? ''))));
                $accommodation_extras_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_extras'] ?? ''))));
                $accommodation_network_features_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_network_features'] ?? ''))));
                $accommodation_animal_housing_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['accommodation_animal_housing'] ?? ''))));
                ?>
                <div class="va-form-group">
                    <label>Ingatlan típusa</label>
                    <select name="accommodation_type" id="va-accommodation-type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="vadaszhaz"<?php selected($accommodation_type_val, 'vadaszhaz'); ?>>Vadászház</option>
                        <option value="erdei-haz"<?php selected($accommodation_type_val, 'erdei-haz'); ?>>Erdei ház</option>
                        <option value="tanya"<?php selected($accommodation_type_val, 'tanya'); ?>>Tanya</option>
                        <option value="preshaz"<?php selected($accommodation_type_val, 'preshaz'); ?>>Présház</option>
                        <option value="hetvegi-haz"<?php selected($accommodation_type_val, 'hetvegi-haz'); ?>>Hétvégi ház</option>
                        <option value="fahaz"<?php selected($accommodation_type_val, 'fahaz'); ?>>Faház</option>
                        <option value="vadasz-kunyho"<?php selected($accommodation_type_val, 'vadasz-kunyho'); ?>>Vadász kunyhó</option>
                        <option value="erdei-kabin"<?php selected($accommodation_type_val, 'erdei-kabin'); ?>>Erdei kabin</option>
                        <option value="mezogazdasagi-ingatlan"<?php selected($accommodation_type_val, 'mezogazdasagi-ingatlan'); ?>>Mezőgazdasági ingatlan</option>
                        <option value="birtok"<?php selected($accommodation_type_val, 'birtok'); ?>>Birtok</option>
                        <option value="telek"<?php selected($accommodation_type_val, 'telek'); ?>>Telek</option>
                        <option value="erdoterulet"<?php selected($accommodation_type_val, 'erdoterulet'); ?>>Erdőterület</option>
                        <option value="vegyes-ingatlan"<?php selected($accommodation_type_val, 'vegyes-ingatlan'); ?>>Vegyes ingatlan</option>
                        <option value="egyeb"<?php selected($accommodation_type_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Település</label>
                    <input type="text" name="accommodation_settlement" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_settlement'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Megye</label>
                    <input type="text" name="accommodation_county_text" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_county_text'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Ország</label>
                    <input type="text" name="accommodation_country" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_country'] ?? '')); ?>">
                </div>
                <div class="va-form-group" style="grid-column:1 / -1;">
                    <label>Pontos cím (opcionális / rejtett)</label>
                    <input type="text" name="accommodation_exact_address" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_exact_address'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Férőhely</label>
                    <select name="accommodation_capacity" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="1-2"<?php selected($accommodation_capacity_val, '1-2'); ?>>1-2 fő</option>
                        <option value="3-5"<?php selected($accommodation_capacity_val, '3-5'); ?>>3-5 fő</option>
                        <option value="6-10"<?php selected($accommodation_capacity_val, '6-10'); ?>>6-10 fő</option>
                        <option value="11-20"<?php selected($accommodation_capacity_val, '11-20'); ?>>11-20 fő</option>
                        <option value="20-plusz"<?php selected($accommodation_capacity_val, '20-plusz'); ?>>20+ fő</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Felnőtt férőhely</label>
                    <input type="text" name="accommodation_adult_capacity" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_adult_capacity'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Szobák száma</label>
                    <input type="text" name="accommodation_room_count" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_room_count'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Ágyak száma</label>
                    <input type="text" name="accommodation_bed_count" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_bed_count'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Pótágy</label>
                    <select name="accommodation_extra_bed" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_extra_bed'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_extra_bed'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Vadászterület közelében?</label>
                    <select name="accommodation_hunting_nearby" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_hunting_nearby'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_hunting_nearby'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Szervezett vadászat elérhető?</label>
                    <select name="accommodation_hunt_available" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_hunt_available'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_hunt_available'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Trófeakezelés</label>
                    <select name="accommodation_trophy_handling" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_trophy_handling'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_trophy_handling'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Hűtőkamra</label>
                    <select name="accommodation_cold_room" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_cold_room'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_cold_room'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Fegyvertároló</label>
                    <select name="accommodation_gun_storage" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_gun_storage'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_gun_storage'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Vadfeldolgozás</label>
                    <select name="accommodation_game_processing" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_game_processing'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_game_processing'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Terepjáró parkoló</label>
                    <select name="accommodation_offroad_parking" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_offroad_parking'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_offroad_parking'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Vadászkutya hozható?</label>
                    <select name="accommodation_dog_allowed" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_dog_allowed'] ?? ''), 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_dog_allowed'] ?? ''), 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group" style="grid-column:1 / -1;">
                    <label>Felszereltség</label>
                    <select name="accommodation_features[]" class="va-select" multiple data-placeholder="Válassz felszereltséget">
                        <option value="wifi"<?php selected( in_array('wifi', $accommodation_features_saved, true), true ); ?>>Wifi</option>
                        <option value="klima"<?php selected( in_array('klima', $accommodation_features_saved, true), true ); ?>>Klíma</option>
                        <option value="futes"<?php selected( in_array('futes', $accommodation_features_saved, true), true ); ?>>Fűtés</option>
                        <option value="konyha"<?php selected( in_array('konyha', $accommodation_features_saved, true), true ); ?>>Konyha</option>
                        <option value="bogracshely"<?php selected( in_array('bogracshely', $accommodation_features_saved, true), true ); ?>>Bográcshely</option>
                        <option value="grillezo"<?php selected( in_array('grillezo', $accommodation_features_saved, true), true ); ?>>Grillező</option>
                        <option value="szauna"<?php selected( in_array('szauna', $accommodation_features_saved, true), true ); ?>>Szauna</option>
                        <option value="jacuzzi"<?php selected( in_array('jacuzzi', $accommodation_features_saved, true), true ); ?>>Jacuzzi</option>
                        <option value="medence"<?php selected( in_array('medence', $accommodation_features_saved, true), true ); ?>>Medence</option>
                        <option value="parkolo"<?php selected( in_array('parkolo', $accommodation_features_saved, true), true ); ?>>Parkoló</option>
                        <option value="fedett-parkolo"<?php selected( in_array('fedett-parkolo', $accommodation_features_saved, true), true ); ?>>Fedett parkoló</option>
                        <option value="tv"<?php selected( in_array('tv', $accommodation_features_saved, true), true ); ?>>TV</option>
                        <option value="mosogep"<?php selected( in_array('mosogep', $accommodation_features_saved, true), true ); ?>>Mosógép</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Étkezés</label>
                    <select name="accommodation_meal_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="onellato"<?php selected($accommodation_meal_type_val, 'onellato'); ?>>önellátó</option>
                        <option value="reggeli"<?php selected($accommodation_meal_type_val, 'reggeli'); ?>>reggeli</option>
                        <option value="felpanzio"<?php selected($accommodation_meal_type_val, 'felpanzio'); ?>>félpanzió</option>
                        <option value="teljes-ellatas"<?php selected($accommodation_meal_type_val, 'teljes-ellatas'); ?>>teljes ellátás</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Minimum éjszaka</label>
                    <input type="text" name="accommodation_min_nights" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_min_nights'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Foglalható időszak</label>
                    <input type="text" name="accommodation_bookable_period" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_bookable_period'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Azonnal foglalható?</label>
                    <select name="accommodation_instant_book" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected((string)($edit_meta['accommodation_instant_book'] ?? ''), 'igen'); ?>>igen</option>
                        <option value="nem"<?php selected((string)($edit_meta['accommodation_instant_book'] ?? ''), 'nem'); ?>>nem</option>
                    </select>
                </div>
                <div class="va-form-group" style="grid-column:1 / -1;">
                    <label>Vadfajok a közelben</label>
                    <select name="accommodation_nearby_species[]" class="va-select" multiple data-placeholder="Válassz vadfajt">
                        <option value="szarvas"<?php selected( in_array('szarvas', $accommodation_nearby_species_saved, true), true ); ?>>Szarvas</option>
                        <option value="vaddiszno"<?php selected( in_array('vaddiszno', $accommodation_nearby_species_saved, true), true ); ?>>Vaddisznó</option>
                        <option value="oz"<?php selected( in_array('oz', $accommodation_nearby_species_saved, true), true ); ?>>Őz</option>
                        <option value="muflon"<?php selected( in_array('muflon', $accommodation_nearby_species_saved, true), true ); ?>>Muflon</option>
                        <option value="damszarvas"<?php selected( in_array('damszarvas', $accommodation_nearby_species_saved, true), true ); ?>>Dámszarvas</option>
                        <option value="facan"<?php selected( in_array('facan', $accommodation_nearby_species_saved, true), true ); ?>>Fácán</option>
                        <option value="nyul"<?php selected( in_array('nyul', $accommodation_nearby_species_saved, true), true ); ?>>Nyúl</option>
                    </select>
                </div>
                <div class="va-form-group" style="grid-column:1 / -1;">
                    <label>Programok</label>
                    <select name="accommodation_programs[]" class="va-select" multiple data-placeholder="Válassz programot">
                        <option value="hajtas"<?php selected( in_array('hajtas', $accommodation_programs_saved, true), true ); ?>>Hajtás</option>
                        <option value="cserkeles"<?php selected( in_array('cserkeles', $accommodation_programs_saved, true), true ); ?>>Cserkelés</option>
                        <option value="lesvadaszat"<?php selected( in_array('lesvadaszat', $accommodation_programs_saved, true), true ); ?>>Lesvadászat</option>
                        <option value="vadhivas"<?php selected( in_array('vadhivas', $accommodation_programs_saved, true), true ); ?>>Vadhívás</option>
                        <option value="kutyas-vadaszat"<?php selected( in_array('kutyas-vadaszat', $accommodation_programs_saved, true), true ); ?>>Kutyás vadászat</option>
                        <option value="vizi-vadaszat"<?php selected( in_array('vizi-vadaszat', $accommodation_programs_saved, true), true ); ?>>Vízi vadászat</option>
                        <option value="trofeabiralat"<?php selected( in_array('trofeabiralat', $accommodation_programs_saved, true), true ); ?>>Trófeabírálat</option>
                        <option value="fegyverbemutato"<?php selected( in_array('fegyverbemutato', $accommodation_programs_saved, true), true ); ?>>Fegyverbemutató</option>
                        <option value="loiskola"<?php selected( in_array('loiskola', $accommodation_programs_saved, true), true ); ?>>Lőiskola</option>
                    </select>
                </div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Környezet</strong></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Környezet jellemzők</label><select name="accommodation_environment[]" class="va-select" multiple data-placeholder="Válassz környezetet"><option value="erdo-mellett"<?php selected( in_array('erdo-mellett', $accommodation_environment_saved, true), true ); ?>>Erdő mellett</option><option value="vadaszterulet-kozeleben"<?php selected( in_array('vadaszterulet-kozeleben', $accommodation_environment_saved, true), true ); ?>>Vadászterület közelében</option><option value="vizpart"<?php selected( in_array('vizpart', $accommodation_environment_saved, true), true ); ?>>Vízpart</option><option value="hegyvidek"<?php selected( in_array('hegyvidek', $accommodation_environment_saved, true), true ); ?>>Hegyvidék</option><option value="mezogazdasagi-terulet"<?php selected( in_array('mezogazdasagi-terulet', $accommodation_environment_saved, true), true ); ?>>Mezőgazdasági terület</option><option value="elszigetelt"<?php selected( in_array('elszigetelt', $accommodation_environment_saved, true), true ); ?>>Elszigetelt</option><option value="termeszetvedelmi"<?php selected( in_array('termeszetvedelmi', $accommodation_environment_saved, true), true ); ?>>Természetvédelmi környezet</option><option value="kulterulet"<?php selected( in_array('kulterulet', $accommodation_environment_saved, true), true ); ?>>Külterület</option></select></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Terület adatok</strong></div>
                <div class="va-form-group"><label>Telek méret (m²)</label><input type="text" name="accommodation_land_size_sqm" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_land_size_sqm'] ?? '')); ?>" placeholder="pl. 2800"></div>
                <div class="va-form-group"><label>Telek méret (hektár)</label><input type="text" name="accommodation_land_size_hectare" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_land_size_hectare'] ?? '')); ?>" placeholder="pl. 0.28"></div>
                <div class="va-form-group"><label>Lakóterület (m²)</label><input type="text" name="accommodation_building_size_sqm" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_building_size_sqm'] ?? '')); ?>" placeholder="pl. 96"></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Épület paraméterek</strong></div>
                <div class="va-form-group"><label>Szintek száma</label><input type="text" name="accommodation_levels_count" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_levels_count'] ?? '')); ?>" placeholder="pl. 2"></div>
                <div class="va-form-group"><label>Építés éve</label><input type="text" name="accommodation_build_year" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_build_year'] ?? '')); ?>" placeholder="pl. 2004"></div>
                <div class="va-form-group"><label>Állapot</label><?php $v = (string)($edit_meta['accommodation_property_condition'] ?? ''); ?><select name="accommodation_property_condition" class="va-select"><option value="">– Válasszon –</option><option value="uj"<?php selected($v, 'uj'); ?>>Új</option><option value="felujitott"<?php selected($v, 'felujitott'); ?>>Felújított</option><option value="jo"<?php selected($v, 'jo'); ?>>Jó</option><option value="felujitando"<?php selected($v, 'felujitando'); ?>>Felújítandó</option><option value="bontando"<?php selected($v, 'bontando'); ?>>Bontandó</option></select></div>
                <div class="va-form-group"><label>Építési típus</label><?php $v = (string)($edit_meta['accommodation_construction_type'] ?? ''); ?><select name="accommodation_construction_type" class="va-select"><option value="">– Válasszon –</option><option value="tegla"<?php selected($v, 'tegla'); ?>>Tégla</option><option value="fa"<?php selected($v, 'fa'); ?>>Fa</option><option value="valyog"<?php selected($v, 'valyog'); ?>>Vályog</option><option value="konnyuszerkezet"<?php selected($v, 'konnyuszerkezet'); ?>>Könnyűszerkezet</option><option value="ko"<?php selected($v, 'ko'); ?>>Kő</option><option value="vegyes"<?php selected($v, 'vegyes'); ?>>Vegyes</option></select></div>
                <div class="va-form-group"><label>Fűtés</label><?php $v = (string)($edit_meta['accommodation_heating_type'] ?? ''); ?><select name="accommodation_heating_type" class="va-select"><option value="">– Válasszon –</option><option value="kandallo"<?php selected($v, 'kandallo'); ?>>Kandalló</option><option value="fatuzeles"<?php selected($v, 'fatuzeles'); ?>>Fatüzelés</option><option value="gaz"<?php selected($v, 'gaz'); ?>>Gáz</option><option value="elektromos"<?php selected($v, 'elektromos'); ?>>Elektromos</option><option value="vegyes"<?php selected($v, 'vegyes'); ?>>Vegyes</option><option value="nincs"<?php selected($v, 'nincs'); ?>>Nincs</option></select></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Közművek</strong></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Közművek</label><select name="accommodation_utilities[]" class="va-select" multiple data-placeholder="Válassz közműveket"><option value="villany"<?php selected( in_array('villany', $accommodation_utilities_saved, true), true ); ?>>Villany</option><option value="vezetekes-viz"<?php selected( in_array('vezetekes-viz', $accommodation_utilities_saved, true), true ); ?>>Vezetékes víz</option><option value="kut"<?php selected( in_array('kut', $accommodation_utilities_saved, true), true ); ?>>Kút</option><option value="gaz"<?php selected( in_array('gaz', $accommodation_utilities_saved, true), true ); ?>>Gáz</option><option value="internet"<?php selected( in_array('internet', $accommodation_utilities_saved, true), true ); ?>>Internet</option><option value="szennyviz"<?php selected( in_array('szennyviz', $accommodation_utilities_saved, true), true ); ?>>Szennyvíz</option><option value="napelem"<?php selected( in_array('napelem', $accommodation_utilities_saved, true), true ); ?>>Napelem</option></select></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Megközelíthetőség</strong></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Megközelíthetőség</label><select name="accommodation_accessibility[]" class="va-select" multiple data-placeholder="Válassz megközelíthetőséget"><option value="aszfalt-ut"<?php selected( in_array('aszfalt-ut', $accommodation_accessibility_saved, true), true ); ?>>Aszfalt út</option><option value="murvas-ut"<?php selected( in_array('murvas-ut', $accommodation_accessibility_saved, true), true ); ?>>Murvás út</option><option value="foldut"<?php selected( in_array('foldut', $accommodation_accessibility_saved, true), true ); ?>>Földút</option><option value="terepjaro-ajanlott"<?php selected( in_array('terepjaro-ajanlott', $accommodation_accessibility_saved, true), true ); ?>>Terepjáró ajánlott</option><option value="egesz-evben"<?php selected( in_array('egesz-evben', $accommodation_accessibility_saved, true), true ); ?>>Egész évben megközelíthető</option></select></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Vadászati kapcsolódás</strong></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Vadászati kapcsolódás</label><select name="accommodation_hunting_features[]" class="va-select" multiple data-placeholder="Válassz kapcsolódó elemeket"><option value="vadaszterulet-kozeleben"<?php selected( in_array('vadaszterulet-kozeleben', $accommodation_hunting_features_saved, true), true ); ?>>Vadászterület közelében</option><option value="leshelyek-kozeleben"<?php selected( in_array('leshelyek-kozeleben', $accommodation_hunting_features_saved, true), true ); ?>>Leshelyek közelében</option><option value="vadtarolo"<?php selected( in_array('vadtarolo', $accommodation_hunting_features_saved, true), true ); ?>>Vadtároló</option><option value="fegyverszoba"<?php selected( in_array('fegyverszoba', $accommodation_hunting_features_saved, true), true ); ?>>Fegyverszoba</option><option value="trofeaszoba"<?php selected( in_array('trofeaszoba', $accommodation_hunting_features_saved, true), true ); ?>>Trófeaszoba</option><option value="vadfeldolgozo-helyiseg"<?php selected( in_array('vadfeldolgozo-helyiseg', $accommodation_hunting_features_saved, true), true ); ?>>Vadfeldolgozó helyiség</option><option value="vadeteto-kozeleben"<?php selected( in_array('vadeteto-kozeleben', $accommodation_hunting_features_saved, true), true ); ?>>Vadetető közelében</option></select></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Extrák</strong></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Extrák</label><select name="accommodation_extras[]" class="va-select" multiple data-placeholder="Válassz extrákat"><option value="pince"<?php selected( in_array('pince', $accommodation_extras_saved, true), true ); ?>>Pince</option><option value="garazs"<?php selected( in_array('garazs', $accommodation_extras_saved, true), true ); ?>>Garázs</option><option value="muhely"<?php selected( in_array('muhely', $accommodation_extras_saved, true), true ); ?>>Műhely</option><option value="istallo"<?php selected( in_array('istallo', $accommodation_extras_saved, true), true ); ?>>Istálló</option><option value="tarolo"<?php selected( in_array('tarolo', $accommodation_extras_saved, true), true ); ?>>Tároló</option><option value="terasz"<?php selected( in_array('terasz', $accommodation_extras_saved, true), true ); ?>>Terasz</option><option value="kemence"<?php selected( in_array('kemence', $accommodation_extras_saved, true), true ); ?>>Kemence</option><option value="szauna"<?php selected( in_array('szauna', $accommodation_extras_saved, true), true ); ?>>Szauna</option><option value="grillezo"<?php selected( in_array('grillezo', $accommodation_extras_saved, true), true ); ?>>Grillező</option><option value="vadfeldolgozo"<?php selected( in_array('vadfeldolgozo', $accommodation_extras_saved, true), true ); ?>>Vadfeldolgozó</option></select></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Hálózat / kommunikáció</strong></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Hálózat</label><select name="accommodation_network_features[]" class="va-select" multiple data-placeholder="Válassz hálózati opciókat"><option value="mobilhalozat-van"<?php selected( in_array('mobilhalozat-van', $accommodation_network_features_saved, true), true ); ?>>Mobilhálózat van</option><option value="gyenge-terero"<?php selected( in_array('gyenge-terero', $accommodation_network_features_saved, true), true ); ?>>Gyenge térerő</option><option value="starlink-kompatibilis"<?php selected( in_array('starlink-kompatibilis', $accommodation_network_features_saved, true), true ); ?>>Starlink kompatibilis</option><option value="optikai-internet"<?php selected( in_array('optikai-internet', $accommodation_network_features_saved, true), true ); ?>>Optikai internet</option></select></div>

                <div class="va-form-group"><label>Felhasználási típus</label><?php $v = (string)($edit_meta['accommodation_use_type'] ?? ''); ?><select name="accommodation_use_type" class="va-select"><option value="">– Válasszon –</option><option value="vadaszhaz"<?php selected($v, 'vadaszhaz'); ?>>Vadászház</option><option value="nyaralo"<?php selected($v, 'nyaralo'); ?>>Nyaraló</option><option value="allando-lakhatas"<?php selected($v, 'allando-lakhatas'); ?>>Állandó lakhatás</option><option value="gazdasagi-epulet"<?php selected($v, 'gazdasagi-epulet'); ?>>Gazdasági épület</option><option value="befektetes"<?php selected($v, 'befektetes'); ?>>Befektetés</option><option value="vendeghaz"<?php selected($v, 'vendeghaz'); ?>>Vendégház</option><option value="vadaszati-bazis"<?php selected($v, 'vadaszati-bazis'); ?>>Vadászati bázis</option></select></div>
                <div class="va-form-group"><label>Terület jellege</label><?php $v = (string)($edit_meta['accommodation_terrain_type'] ?? ''); ?><select name="accommodation_terrain_type" class="va-select"><option value="">– Válasszon –</option><option value="sik"<?php selected($v, 'sik'); ?>>Sík</option><option value="dombos"<?php selected($v, 'dombos'); ?>>Dombos</option><option value="hegyvideki"<?php selected($v, 'hegyvideki'); ?>>Hegyvidéki</option><option value="erdos"<?php selected($v, 'erdos'); ?>>Erdős</option><option value="vegyes"<?php selected($v, 'vegyes'); ?>>Vegyes</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Állattartás</label><select name="accommodation_animal_housing[]" class="va-select" multiple data-placeholder="Válassz állattartási opciókat"><option value="engedelyezett"<?php selected( in_array('engedelyezett', $accommodation_animal_housing_saved, true), true ); ?>>Engedélyezett</option><option value="kennel-kialakithato"<?php selected( in_array('kennel-kialakithato', $accommodation_animal_housing_saved, true), true ); ?>>Kennel kialakítható</option><option value="nagytestu-kutya"<?php selected( in_array('nagytestu-kutya', $accommodation_animal_housing_saved, true), true ); ?>>Nagytestű kutya tartható</option></select></div>

                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="erdoterulet" style="grid-column:1 / -1; display:none;"><strong>Erdőterület adatok</strong></div>
                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="erdoterulet" style="display:none;"><label>Fafaj</label><input type="text" name="accommodation_fafaj" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_fafaj'] ?? '')); ?>" placeholder="pl. tölgy, bükk"></div>
                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="erdoterulet" style="display:none;"><label>Erdő kora</label><input type="text" name="accommodation_erdo_kora" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_erdo_kora'] ?? '')); ?>" placeholder="pl. 40 év"></div>

                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="vadaszhaz" style="grid-column:1 / -1; display:none;"><strong>Vadászház specifikus</strong></div>
                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="vadaszhaz" style="display:none;"><label>Trófeaszoba</label><?php $v = (string)($edit_meta['accommodation_vadaszhaz_trofeaszoba'] ?? ''); ?><select name="accommodation_vadaszhaz_trofeaszoba" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="vadaszhaz" style="display:none;"><label>Fegyvertároló</label><?php $v = (string)($edit_meta['accommodation_vadaszhaz_fegyvertarolo'] ?? ''); ?><select name="accommodation_vadaszhaz_fegyvertarolo" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>

                <div class="va-form-group va-accommodation-dynamic-group" data-accommodation-types="tanya" style="grid-column:1 / -1; display:none;"><label>Gazdasági épületek</label><input type="text" name="accommodation_tanya_gazdasagi_epuletek" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_tanya_gazdasagi_epuletek'] ?? '')); ?>" placeholder="pl. magtár, pajta, gépszín"></div>

                <div class="va-form-group" style="grid-column:1 / -1;"><strong>Vadászati potenciál (1-10)</strong></div>
                <div class="va-form-group"><label>Vadmozgás</label><input type="text" name="accommodation_hunting_potential_wild_movement" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_hunting_potential_wild_movement'] ?? '')); ?>" placeholder="1-10"></div>
                <div class="va-form-group"><label>Elszigeteltség</label><input type="text" name="accommodation_hunting_potential_isolation" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_hunting_potential_isolation'] ?? '')); ?>" placeholder="1-10"></div>
                <div class="va-form-group"><label>Erdő közelség</label><input type="text" name="accommodation_hunting_potential_forest_proximity" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_hunting_potential_forest_proximity'] ?? '')); ?>" placeholder="1-10"></div>
                <div class="va-form-group"><label>Infrastruktúra</label><input type="text" name="accommodation_hunting_potential_infrastructure" class="va-input" value="<?php echo esc_attr((string)($edit_meta['accommodation_hunting_potential_infrastructure'] ?? '')); ?>" placeholder="1-10"></div>
                </div><!-- /va-step2-4col-inner -->
            </div><!-- /.va-accommodation-fields-grid -->

            <!-- Vadászati lehetőség / Vadkárelhárítás mezők grid -->
            <div class="va-form-group va-hunt-option-fields-grid" data-categories="vadaszati-lehetoseg,vadkarelharitas" style="display:none">
                <div class="va-step2-4col-inner">
                <?php
                $hunt_slot_from_hour_val = (string)($edit_meta['hunt_slot_from_hour'] ?? '');
                $hunt_slot_to_hour_val = (string)($edit_meta['hunt_slot_to_hour'] ?? '');
                $hunt_capacity_val = (string)($edit_meta['hunt_capacity'] ?? '');
                $hunt_species_list_val = (string)($edit_meta['hunt_species_list'] ?? '');
                $hunt_lease_type_val = (string)($edit_meta['hunt_lease_type'] ?? '');
                $hunt_has_guide_val = (string)($edit_meta['hunt_has_guide'] ?? '');
                $hunt_has_tracking_val = (string)($edit_meta['hunt_has_tracking'] ?? '');
                $hunt_has_trophy_prep_val = (string)($edit_meta['hunt_has_trophy_prep'] ?? '');
                $hunt_has_trophy_judging_val = (string)($edit_meta['hunt_has_trophy_judging'] ?? '');
                $hunt_has_offroad_val = (string)($edit_meta['hunt_has_offroad'] ?? '');
                $hunt_has_accommodation_val = (string)($edit_meta['hunt_has_accommodation'] ?? '');
                $hunt_can_buy_game_val = (string)($edit_meta['hunt_can_buy_game'] ?? '');
                $hunt_multi = static function( string $key ) use ( $edit_meta ) {
                    $raw = (string) ( $edit_meta[ $key ] ?? '' );
                    if ( $raw === '' ) {
                        return [];
                    }
                    return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
                };
                $hunt_game_species_saved = $hunt_multi( 'hunt_game_species' );
                $hunt_terrain_saved = $hunt_multi( 'hunt_terrain_types' );
                $hunt_price_includes_saved = $hunt_multi( 'hunt_price_includes' );
                $hunt_methods_saved = $hunt_multi( 'hunt_methods' );
                $hunt_onsite_services_saved = $hunt_multi( 'hunt_onsite_services' );
                $hunt_safety_saved = $hunt_multi( 'hunt_safety_features' );
                $hunt_conditions_saved = $hunt_multi( 'hunt_conditions' );
                $hunt_required_media_saved = $hunt_multi( 'hunt_required_media' );
                $hunt_video_saved = $hunt_multi( 'hunt_video_types' );
                $hunt_moderation_saved = $hunt_multi( 'hunt_moderation_flags' );
                $hunt_tools_saved = $hunt_multi( 'hunt_tools' );
                $hunt_reference_media_saved = $hunt_multi( 'hunt_reference_media' );
                ?>
                <div class="va-form-group va-vadkar-only">
                    <label>Vadkár elhárítás fő típusa</label>
                    <?php $v = (string)($edit_meta['hunt_damage_main_type'] ?? ''); ?>
                    <select id="va-hunt-damage-main-type" name="hunt_damage_main_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="megelozo-vadkar-elharitas"<?php selected($v, 'megelozo-vadkar-elharitas'); ?>>Megelőző vadkár elhárítás</option>
                        <option value="aktiv-vadkar-csokkentes"<?php selected($v, 'aktiv-vadkar-csokkentes'); ?>>Aktív vadkár csökkentés</option>
                        <option value="szezonalis-vedelem"<?php selected($v, 'szezonalis-vedelem'); ?>>Szezonális védelem</option>
                        <option value="folyamatos-teruletvedelem"<?php selected($v, 'folyamatos-teruletvedelem'); ?>>Folyamatos területvédelem</option>
                        <option value="surgossegi-beavatkozas"<?php selected($v, 'surgossegi-beavatkozas'); ?>>Sürgősségi beavatkozás</option>
                        <option value="mezogazdasagi-vedelem"<?php selected($v, 'mezogazdasagi-vedelem'); ?>>Mezőgazdasági védelem</option>
                        <option value="erdeszeti-vedelem"<?php selected($v, 'erdeszeti-vedelem'); ?>>Erdészeti védelem</option>
                        <option value="vegyes-teruletvedelem"<?php selected($v, 'vegyes-teruletvedelem'); ?>>Vegyes területvédelem</option>
                    </select>
                </div>
                <div class="va-form-group va-vadkar-only"><label>Település</label><input type="text" name="hunt_settlement" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_settlement'] ?? '')); ?>"></div>
                <div class="va-form-group va-vadkar-only"><label>Terület típusa</label><?php $v = (string)($edit_meta['hunt_land_type'] ?? ''); ?><select id="va-hunt-land-type" name="hunt_land_type" class="va-select"><option value="">– Válasszon –</option><option value="szantofold"<?php selected($v, 'szantofold'); ?>>Szántóföld</option><option value="kukorica"<?php selected($v, 'kukorica'); ?>>Kukorica</option><option value="napraforgo"<?php selected($v, 'napraforgo'); ?>>Napraforgó</option><option value="buza"<?php selected($v, 'buza'); ?>>Búza</option><option value="szolo"<?php selected($v, 'szolo'); ?>>Szőlő</option><option value="gyumolcsos"<?php selected($v, 'gyumolcsos'); ?>>Gyümölcsös</option><option value="erdo"<?php selected($v, 'erdo'); ?>>Erdő</option><option value="legelo"<?php selected($v, 'legelo'); ?>>Legelő</option><option value="vegyes-mezogazdasagi"<?php selected($v, 'vegyes-mezogazdasagi'); ?>>Vegyes mezőgazdasági terület</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Reakcióidő</label><?php $v = (string)($edit_meta['hunt_response_time'] ?? ''); ?><select id="va-hunt-response-time" name="hunt_response_time" class="va-select"><option value="">– Válasszon –</option><option value="azonnal-0-2"<?php selected($v, 'azonnal-0-2'); ?>>Azonnal (0-2 óra)</option><option value="24-oran-belul"<?php selected($v, '24-oran-belul'); ?>>24 órán belül</option><option value="48-oran-belul"<?php selected($v, '48-oran-belul'); ?>>48 órán belül</option><option value="szezonalis-jelenlet"<?php selected($v, 'szezonalis-jelenlet'); ?>>Szezonális jelenlét</option><option value="folyamatos-jelenlet"<?php selected($v, 'folyamatos-jelenlet'); ?>>Folyamatos jelenlét</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Elérhetőség</label><?php $v = (string)($edit_meta['hunt_availability_mode'] ?? ''); ?><select id="va-hunt-availability-mode" name="hunt_availability_mode" class="va-select"><option value="">– Válasszon –</option><option value="0-24"<?php selected($v, '0-24'); ?>>0-24</option><option value="nappali"<?php selected($v, 'nappali'); ?>>Nappali</option><option value="ejszakai"<?php selected($v, 'ejszakai'); ?>>Éjszakai</option><option value="elore-egyeztetett"<?php selected($v, 'elore-egyeztetett'); ?>>Előre egyeztetett</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Végrehajtó</label><?php $v = (string)($edit_meta['hunt_executor_type'] ?? ''); ?><select name="hunt_executor_type" class="va-select"><option value="">– Válasszon –</option><option value="hivatasos-vadasz"<?php selected($v, 'hivatasos-vadasz'); ?>>Hivatásos vadász</option><option value="vador"<?php selected($v, 'vador'); ?>>Vadőr</option><option value="mezogazdasagi-partner"<?php selected($v, 'mezogazdasagi-partner'); ?>>Mezőgazdasági partner</option><option value="vallalkozas"<?php selected($v, 'vallalkozas'); ?>>Vállalkozás</option><option value="csapat"<?php selected($v, 'csapat'); ?>>Csapat</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Kapacitás</label><?php $v = (string)($edit_meta['hunt_executor_capacity'] ?? ''); ?><select name="hunt_executor_capacity" class="va-select"><option value="">– Válasszon –</option><option value="1-fo"<?php selected($v, '1-fo'); ?>>1 fő</option><option value="2-3-fo"<?php selected($v, '2-3-fo'); ?>>2-3 fő</option><option value="4-6-fo"<?php selected($v, '4-6-fo'); ?>>4-6 fő</option><option value="nagyobb-egyseg"<?php selected($v, 'nagyobb-egyseg'); ?>>Nagyobb egység</option></select></div>
                <div class="va-form-group va-vadkar-only" style="grid-column:1 / -1;"><label>Eszközök</label><select name="hunt_tools[]" class="va-select" multiple data-placeholder="Válassz eszközöket"><option value="vadkamera"<?php echo in_array( 'vadkamera', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Vadkamera</option><option value="hokamera"<?php echo in_array( 'hokamera', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Hőkamera</option><option value="ejjellato"<?php echo in_array( 'ejjellato', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Éjjellátó</option><option value="dron"<?php echo in_array( 'dron', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Drón</option><option value="optika"<?php echo in_array( 'optika', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Optika</option><option value="terepjaro"<?php echo in_array( 'terepjaro', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Terepjáró</option><option value="quad"<?php echo in_array( 'quad', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Quad</option><option value="keritesrendszer"<?php echo in_array( 'keritesrendszer', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Kerítésrendszer</option><option value="csapda"<?php echo in_array( 'csapda', $hunt_tools_saved, true ) ? ' selected' : ''; ?>>Csapda</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Fegyverhasználat szükséges?</label><?php $v = (string)($edit_meta['hunt_weapon_use_required'] ?? ''); ?><select name="hunt_weapon_use_required" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Területgazdálkodói engedély?</label><?php $v = (string)($edit_meta['hunt_land_manager_permit'] ?? ''); ?><select name="hunt_land_manager_permit" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Együttműködés vadásztársasággal?</label><?php $v = (string)($edit_meta['hunt_hunting_company_coop'] ?? ''); ?><select name="hunt_hunting_company_coop" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Felelősségbiztosítás</label><?php $v = (string)($edit_meta['hunt_liability_insurance'] ?? ''); ?><select name="hunt_liability_insurance" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Van</option><option value="nem"<?php selected($v, 'nem'); ?>>Nincs</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Kár jellege</label><?php $v = (string)($edit_meta['hunt_damage_nature'] ?? ''); ?><select name="hunt_damage_nature" class="va-select"><option value="">– Válasszon –</option><option value="termenykar"<?php selected($v, 'termenykar'); ?>>Terménykár</option><option value="erdokar"<?php selected($v, 'erdokar'); ?>>Erdőkár</option><option value="taposasi-kar"<?php selected($v, 'taposasi-kar'); ?>>Taposási kár</option><option value="ragasi-kar"<?php selected($v, 'ragasi-kar'); ?>>Rágáskár</option><option value="infrastruktura-kar"<?php selected($v, 'infrastruktura-kar'); ?>>Infrastruktúra kár</option><option value="vegyes"<?php selected($v, 'vegyes'); ?>>Vegyes</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Árazás modell</label><?php $v = (string)($edit_meta['hunt_pricing_model'] ?? ''); ?><select id="va-hunt-pricing-model" name="hunt_pricing_model" class="va-select"><option value="">– Válasszon –</option><option value="oradij"<?php selected($v, 'oradij'); ?>>Óradíj</option><option value="napidij"<?php selected($v, 'napidij'); ?>>Napidíj</option><option value="hektar-alapu"<?php selected($v, 'hektar-alapu'); ?>>Hektár alapú</option><option value="szerzodeses"<?php selected($v, 'szerzodeses'); ?>>Szerződéses</option><option value="sikerdijas"<?php selected($v, 'sikerdijas'); ?>>Sikerdíjas</option><option value="egyedi-ajanlat"<?php selected($v, 'egyedi-ajanlat'); ?>>Egyedi ajánlat</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Kiszállás díja</label><input type="text" name="hunt_travel_fee" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_travel_fee'] ?? '')); ?>"></div>
                <div class="va-form-group va-vadkar-only"><label>Szerződés típusa</label><?php $v = (string)($edit_meta['hunt_contract_type'] ?? ''); ?><select id="va-hunt-contract-type" name="hunt_contract_type" class="va-select"><option value="">– Válasszon –</option><option value="eseti"<?php selected($v, 'eseti'); ?>>Eseti</option><option value="szezonalis"<?php selected($v, 'szezonalis'); ?>>Szezonális</option><option value="eves"<?php selected($v, 'eves'); ?>>Éves</option><option value="folyamatos"<?php selected($v, 'folyamatos'); ?>>Folyamatos</option></select></div>
                <div class="va-form-group va-vadkar-only" style="grid-column:1 / -1;"><label>Helyszíni feltételek</label><select name="hunt_site_conditions[]" class="va-select" multiple data-placeholder="Válassz feltételeket"><option value="szallas-van"<?php echo in_array( 'szallas-van', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Szállás van</option><option value="terepjaro-biztositott"<?php echo in_array( 'terepjaro-biztositott', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Terepjáró biztosított</option><option value="sajat-eszkoz-kell"<?php echo in_array( 'sajat-eszkoz-kell', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Saját eszköz kell</option><option value="partner-biztositja"<?php echo in_array( 'partner-biztositja', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Partner biztosítja az eszközöket</option></select></div>
                <div class="va-form-group va-vadkar-only" style="grid-column:1 / -1;"><label>Referencia média</label><select name="hunt_reference_media[]" class="va-select" multiple data-placeholder="Válassz referenciát"><option value="korabbi-vadkar-eredmenyek"<?php echo in_array( 'korabbi-vadkar-eredmenyek', $hunt_reference_media_saved, true ) ? ' selected' : ''; ?>>Korábbi vadkár eredmények</option><option value="elotte-utana"<?php echo in_array( 'elotte-utana', $hunt_reference_media_saved, true ) ? ' selected' : ''; ?>>Előtte/utána</option></select></div>
                <div class="va-form-group va-vadkar-dynamic-group" data-vadkar-land="agri" style="display:none;"><label>Hektár méret</label><input type="text" name="hunt_agri_hectare_size" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_agri_hectare_size'] ?? '')); ?>"></div>
                <div class="va-form-group va-vadkar-dynamic-group" data-vadkar-land="agri" style="display:none;"><label>Kultúra típusa</label><input type="text" name="hunt_agri_culture_type" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_agri_culture_type'] ?? '')); ?>"></div>
                <div class="va-form-group va-vadkar-dynamic-group" data-vadkar-response="urgent" style="display:none;"><label>Azonnali indulás</label><?php $v = (string)($edit_meta['hunt_urgent_immediate_start'] ?? ''); ?><select name="hunt_urgent_immediate_start" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-vadkar-dynamic-group" data-vadkar-response="urgent" style="display:none;"><label>Ügyeleti szám</label><input type="text" name="hunt_urgent_duty_phone" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_urgent_duty_phone'] ?? '')); ?>"></div>
                <div class="va-form-group va-vadkar-dynamic-group" data-vadkar-contract="long" style="display:none;"><label>Éves szerződés kedvezmény</label><input type="text" name="hunt_long_term_yearly_discount" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_long_term_yearly_discount'] ?? '')); ?>"></div>
                <div class="va-form-group va-vadkar-only" style="grid-column:1 / -1;"><label>Területvédelmi csomag</label><select name="hunt_package_auto_seasonal" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected((string)($edit_meta['hunt_package_auto_seasonal'] ?? ''), 'igen'); ?>>Automatikus szezonos védelem</option><option value="nem"<?php selected((string)($edit_meta['hunt_package_auto_seasonal'] ?? ''), 'nem'); ?>>Nincs</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Havi díjas modell</label><?php $v = (string)($edit_meta['hunt_package_monthly_model'] ?? ''); ?><select name="hunt_package_monthly_model" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-vadkar-only"><label>Monitoring + beavatkozás</label><?php $v = (string)($edit_meta['hunt_package_monitoring_intervention'] ?? ''); ?><select name="hunt_package_monitoring_intervention" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group">
                    <label>Vadászat típusa</label>
                    <?php $v = (string)($edit_meta['hunt_type'] ?? ''); ?>
                    <select id="va-hunt-type" name="hunt_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="bervadaszat"<?php selected($v, 'bervadaszat'); ?>>Bérvadászat</option>
                        <option value="egyeni-vadaszat"<?php selected($v, 'egyeni-vadaszat'); ?>>Egyéni vadászat</option>
                        <option value="tarsas-vadaszat"<?php selected($v, 'tarsas-vadaszat'); ?>>Társas vadászat</option>
                        <option value="kulfoldi-vadaszat"<?php selected($v, 'kulfoldi-vadaszat'); ?>>Külföldi vadászat</option>
                        <option value="vendegvadaszat"<?php selected($v, 'vendegvadaszat'); ?>>Vendégvadászat</option>
                        <option value="selejtezes"<?php selected($v, 'selejtezes'); ?>>Selejtezés</option>
                        <option value="fotovadaszat"<?php selected($v, 'fotovadaszat'); ?>>Fotóvadászat</option>
                        <option value="szorovadaszat"<?php selected($v, 'szorovadaszat'); ?>>Szóróvadászat</option>
                        <option value="hajtovadaszat"<?php selected($v, 'hajtovadaszat'); ?>>Hajtóvadászat</option>
                        <option value="cserkeles"<?php selected($v, 'cserkeles'); ?>>Cserkelés</option>
                        <option value="lesvadaszat"<?php selected($v, 'lesvadaszat'); ?>>Lesvadászat</option>
                        <option value="egyeb"<?php selected($v, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group" style="grid-column:1 / -1;">
                    <label>Vadfaj</label>
                    <select name="hunt_game_species[]" class="va-select" multiple data-placeholder="Válassz vadfajt">
                        <option value="gimszarvas"<?php echo in_array( 'gimszarvas', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Gímszarvas</option>
                        <option value="damszarvas"<?php echo in_array( 'damszarvas', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Dámszarvas</option>
                        <option value="oz"<?php echo in_array( 'oz', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Őz</option>
                        <option value="muflon"<?php echo in_array( 'muflon', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Muflon</option>
                        <option value="vaddiszno"<?php echo in_array( 'vaddiszno', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Vaddisznó</option>
                        <option value="nyul"<?php echo in_array( 'nyul', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Nyúl</option>
                        <option value="facan"<?php echo in_array( 'facan', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Fácán</option>
                        <option value="fogoly"<?php echo in_array( 'fogoly', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Fogoly</option>
                        <option value="vadkacsa"<?php echo in_array( 'vadkacsa', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Vadkacsa</option>
                        <option value="liba"<?php echo in_array( 'liba', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Liba</option>
                        <option value="sakal"<?php echo in_array( 'sakal', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Sakál</option>
                        <option value="roka"<?php echo in_array( 'roka', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Róka</option>
                        <option value="mezei-nyul"<?php echo in_array( 'mezei-nyul', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Mezei nyúl</option>
                        <option value="galamb-madarkar"<?php echo in_array( 'galamb-madarkar', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Galamb / madárkár</option>
                        <option value="ragadozo"<?php echo in_array( 'ragadozo', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Ragadozó (róka, sakál)</option>
                        <option value="egyeb"<?php echo in_array( 'egyeb', $hunt_game_species_saved, true ) ? ' selected' : ''; ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group"><label>Ország</label><input type="text" name="hunt_country" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_country'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>Megye</label><input type="text" name="hunt_county" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_county'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>Vadászterület neve</label><input type="text" name="hunt_area_name" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_area_name'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>Pontos terület (opcionális)</label><input type="text" name="hunt_exact_area" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_exact_area'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>GPS lat (opcionális)</label><input type="text" name="hunt_gps_lat" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_gps_lat'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>GPS lng (opcionális)</label><input type="text" name="hunt_gps_lng" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_gps_lng'] ?? '')); ?>"></div>
                <div class="va-form-group" style="grid-column:1 / -1;">
                    <label>Terület jellemzők</label>
                    <select name="hunt_terrain_types[]" class="va-select" multiple data-placeholder="Válassz tereptípust">
                        <option value="erdo"<?php echo in_array( 'erdo', $hunt_terrain_saved, true ) ? ' selected' : ''; ?>>Erdő</option>
                        <option value="mezo"<?php echo in_array( 'mezo', $hunt_terrain_saved, true ) ? ' selected' : ''; ?>>Mező</option>
                        <option value="hegyvidek"<?php echo in_array( 'hegyvidek', $hunt_terrain_saved, true ) ? ' selected' : ''; ?>>Hegyvidék</option>
                        <option value="sikvidek"<?php echo in_array( 'sikvidek', $hunt_terrain_saved, true ) ? ' selected' : ''; ?>>Síkvidék</option>
                        <option value="mocsar"<?php echo in_array( 'mocsar', $hunt_terrain_saved, true ) ? ' selected' : ''; ?>>Mocsár</option>
                        <option value="vegyes"<?php echo in_array( 'vegyes', $hunt_terrain_saved, true ) ? ' selected' : ''; ?>>Vegyes</option>
                    </select>
                </div>
                <div class="va-form-group"><label>Kezdő dátum</label><div class="va-dark-date"><input type="hidden" name="hunt_season_start" class="va-date-value" value="<?php echo esc_attr((string)($edit_meta['hunt_season_start'] ?? '')); ?>"><input type="text" class="va-input va-date-display" placeholder="éééé. hh. nn." readonly><button type="button" class="va-date-btn" aria-label="Naptár megnyitása"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></button><div class="va-dark-calendar" style="display:none;"><div class="va-dark-calendar__head"><button type="button" class="va-dark-calendar__nav" data-dir="prev" aria-label="Előző hónap">‹</button><div class="va-dark-calendar__title"></div><button type="button" class="va-dark-calendar__nav" data-dir="next" aria-label="Következő hónap">›</button></div><div class="va-dark-calendar__dow"><span>H</span><span>K</span><span>Sze</span><span>Cs</span><span>P</span><span>Szo</span><span>V</span></div><div class="va-dark-calendar__grid"></div><div class="va-dark-calendar__foot"><button type="button" class="va-dark-calendar__clear">Törlés</button></div></div></div></div>
                <div class="va-form-group"><label>Záró dátum</label><div class="va-dark-date"><input type="hidden" name="hunt_season_end" class="va-date-value" value="<?php echo esc_attr((string)($edit_meta['hunt_season_end'] ?? '')); ?>"><input type="text" class="va-input va-date-display" placeholder="éééé. hh. nn." readonly><button type="button" class="va-date-btn" aria-label="Naptár megnyitása"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></button><div class="va-dark-calendar" style="display:none;"><div class="va-dark-calendar__head"><button type="button" class="va-dark-calendar__nav" data-dir="prev" aria-label="Előző hónap">‹</button><div class="va-dark-calendar__title"></div><button type="button" class="va-dark-calendar__nav" data-dir="next" aria-label="Következő hónap">›</button></div><div class="va-dark-calendar__dow"><span>H</span><span>K</span><span>Sze</span><span>Cs</span><span>P</span><span>Szo</span><span>V</span></div><div class="va-dark-calendar__grid"></div><div class="va-dark-calendar__foot"><button type="button" class="va-dark-calendar__clear">Törlés</button></div></div></div></div>
                <div class="va-form-group"><label>Időpont típus</label><?php $v = (string)($edit_meta['hunt_time_type'] ?? ''); ?><select name="hunt_time_type" class="va-select"><option value="">– Válasszon –</option><option value="fix-datum"<?php selected($v, 'fix-datum'); ?>>Fix dátum</option><option value="szezon"<?php selected($v, 'szezon'); ?>>Szezon</option><option value="egesz-ev"<?php selected($v, 'egesz-ev'); ?>>Egész év</option><option value="hetvegi"<?php selected($v, 'hetvegi'); ?>>Hétvégi</option><option value="egyedi-egyeztetes"<?php selected($v, 'egyedi-egyeztetes'); ?>>Egyedi egyeztetés</option></select></div>
                <div class="va-form-group"><label>Ár típusa</label><?php $v = (string)($edit_meta['hunt_price_type'] ?? ''); ?><select id="va-hunt-price-type" name="hunt_price_type" class="va-select"><option value="">– Válasszon –</option><option value="trofea-alapu"<?php selected($v, 'trofea-alapu'); ?>>Trófea alapú</option><option value="napidij"<?php selected($v, 'napidij'); ?>>Napidíj</option><option value="lovesenkent"<?php selected($v, 'lovesenkent'); ?>>Lövésenként</option><option value="csomag-ar"<?php selected($v, 'csomag-ar'); ?>>Csomag ár</option><option value="egyedi-ajanlat"<?php selected($v, 'egyedi-ajanlat'); ?>>Egyedi ajánlat</option></select></div>
                <div class="va-form-group"><label>Ár minimum</label><input type="text" name="hunt_price_min" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_price_min'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>Ár maximum</label><input type="text" name="hunt_price_max" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_price_max'] ?? '')); ?>"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartalmazza</label><select name="hunt_price_includes[]" class="va-select" multiple data-placeholder="Válassz elemeket"><option value="kiseret"<?php echo in_array( 'kiseret', $hunt_price_includes_saved, true ) ? ' selected' : ''; ?>>Kíséret</option><option value="terulet-hasznalat"<?php echo in_array( 'terulet-hasznalat', $hunt_price_includes_saved, true ) ? ' selected' : ''; ?>>Terület használat</option><option value="trofea-feldolgozas"<?php echo in_array( 'trofea-feldolgozas', $hunt_price_includes_saved, true ) ? ' selected' : ''; ?>>Trófea feldolgozás</option><option value="szallas"<?php echo in_array( 'szallas', $hunt_price_includes_saved, true ) ? ' selected' : ''; ?>>Szállás</option><option value="etkezes"<?php echo in_array( 'etkezes', $hunt_price_includes_saved, true ) ? ' selected' : ''; ?>>Étkezés</option></select></div>
                <div class="va-form-group"><label>Kíséret típusa</label><?php $v = (string)($edit_meta['hunt_guide_type'] ?? ''); ?><select name="hunt_guide_type" class="va-select"><option value="">– Válasszon –</option><option value="hivatasos-vadasz"<?php selected($v, 'hivatasos-vadasz'); ?>>Hivatásos vadász</option><option value="vador"<?php selected($v, 'vador'); ?>>Vadőr</option><option value="nincs"<?php selected($v, 'nincs'); ?>>Nincs kíséret</option><option value="opcionalis"<?php selected($v, 'opcionalis'); ?>>Opcionális</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Vadászati módszer</label><select id="va-hunt-methods" name="hunt_methods[]" class="va-select" multiple data-placeholder="Válassz módszert"><option value="les"<?php echo in_array( 'les', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Les</option><option value="cserkeles"<?php echo in_array( 'cserkeles', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Cserkelés</option><option value="hajtas"<?php echo in_array( 'hajtas', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Hajtás</option><option value="etetes"<?php echo in_array( 'etetes', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Etetés</option><option value="hivas"<?php echo in_array( 'hivas', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Hívás</option><option value="leskunyho"<?php echo in_array( 'leskunyho', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Leskunyhó</option><option value="magasles"<?php echo in_array( 'magasles', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Magasles</option><option value="lesvadaszat"<?php echo in_array( 'lesvadaszat', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Lesvadászat</option><option value="ejszakai-megfigyeles"<?php echo in_array( 'ejszakai-megfigyeles', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Éjszakai megfigyelés</option><option value="szoro-alkalmazas"<?php echo in_array( 'szoro-alkalmazas', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Szóró alkalmazás</option><option value="riasztas-hang-feny"<?php echo in_array( 'riasztas-hang-feny', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Riasztás (hang/fény)</option><option value="kerites-fizikai-vedelem"<?php echo in_array( 'kerites-fizikai-vedelem', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Kerítés / fizikai védelem</option><option value="dronos-megfigyeles"<?php echo in_array( 'dronos-megfigyeles', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Drónos megfigyelés</option><option value="csapdazas"<?php echo in_array( 'csapdazas', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Csapdázás</option><option value="vadtereles"<?php echo in_array( 'vadtereles', $hunt_methods_saved, true ) ? ' selected' : ''; ?>>Vadterelés</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Szolgáltatások a helyszínen</label><select name="hunt_onsite_services[]" class="va-select" multiple data-placeholder="Válassz szolgáltatásokat"><option value="trofea-kezeles"<?php echo in_array( 'trofea-kezeles', $hunt_onsite_services_saved, true ) ? ' selected' : ''; ?>>Trófea kezelés</option><option value="vadhutes"<?php echo in_array( 'vadhutes', $hunt_onsite_services_saved, true ) ? ' selected' : ''; ?>>Vadhűtés</option><option value="zsigereles"<?php echo in_array( 'zsigereles', $hunt_onsite_services_saved, true ) ? ' selected' : ''; ?>>Zsigerelés</option><option value="szallitas"<?php echo in_array( 'szallitas', $hunt_onsite_services_saved, true ) ? ' selected' : ''; ?>>Szállítás</option><option value="fegyverkolcsonzes"<?php echo in_array( 'fegyverkolcsonzes', $hunt_onsite_services_saved, true ) ? ' selected' : ''; ?>>Fegyverkölcsönzés</option><option value="optika-biztositas"<?php echo in_array( 'optika-biztositas', $hunt_onsite_services_saved, true ) ? ' selected' : ''; ?>>Optika biztosítás</option></select></div>
                <div class="va-form-group"><label>Vadászjegy szükséges?</label><?php $v = (string)($edit_meta['hunt_hunting_license_required'] ?? ''); ?><select name="hunt_hunting_license_required" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group"><label>Engedély szükséges?</label><?php $v = (string)($edit_meta['hunt_permit_required'] ?? ''); ?><select name="hunt_permit_required" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group"><label>Külföldiek is vadászhatnak?</label><?php $v = (string)($edit_meta['hunt_foreigners_allowed'] ?? ''); ?><select id="va-hunt-foreigners-allowed" name="hunt_foreigners_allowed" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group"><label>Minimum életkor</label><input type="text" name="hunt_min_age" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_min_age'] ?? '')); ?>" placeholder="pl. 18"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Biztonság</label><select name="hunt_safety_features[]" class="va-select" multiple data-placeholder="Válassz biztonsági elemeket"><option value="biztositott-kiseret"<?php echo in_array( 'biztositott-kiseret', $hunt_safety_saved, true ) ? ' selected' : ''; ?>>Biztosított kíséret</option><option value="felelossegbiztositas"<?php echo in_array( 'felelossegbiztositas', $hunt_safety_saved, true ) ? ' selected' : ''; ?>>Felelősségbiztosítás</option><option value="balesetvedelem"<?php echo in_array( 'balesetvedelem', $hunt_safety_saved, true ) ? ' selected' : ''; ?>>Balesetvédelem</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Körülmények</label><select name="hunt_conditions[]" class="va-select" multiple data-placeholder="Válassz körülményeket"><option value="vadaszhaz-elerheto"<?php echo in_array( 'vadaszhaz-elerheto', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Vadászház elérhető</option><option value="szallas-biztositott"<?php echo in_array( 'szallas-biztositott', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Szállás biztosított</option><option value="etkezes-biztositott"<?php echo in_array( 'etkezes-biztositott', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Étkezés biztosított</option><option value="terepjaro-biztositott"<?php echo in_array( 'terepjaro-biztositott', $hunt_conditions_saved, true ) ? ' selected' : ''; ?>>Terepjáró biztosított</option></select></div>
                <div class="va-form-group"><label>Nehézségi szint</label><?php $v = (string)($edit_meta['hunt_difficulty_level'] ?? ''); ?><select name="hunt_difficulty_level" class="va-select"><option value="">– Válasszon –</option><option value="konnyu"<?php selected($v, 'konnyu'); ?>>Könnyű</option><option value="kozepes"<?php selected($v, 'kozepes'); ?>>Közepes</option><option value="nehez"<?php selected($v, 'nehez'); ?>>Nehéz</option><option value="extrem"<?php selected($v, 'extrem'); ?>>Extrém</option></select></div>
                <div class="va-form-group"><label>Várható trófea minőség</label><?php $v = (string)($edit_meta['hunt_expected_trophy_quality'] ?? ''); ?><select name="hunt_expected_trophy_quality" class="va-select"><option value="">– Válasszon –</option><option value="gyenge"<?php selected($v, 'gyenge'); ?>>Gyenge</option><option value="atlagos"<?php selected($v, 'atlagos'); ?>>Átlagos</option><option value="jo"<?php selected($v, 'jo'); ?>>Jó</option><option value="kivalo"<?php selected($v, 'kivalo'); ?>>Kiváló</option></select></div>
                <div class="va-form-group"><label>Súly / korosztály (ha ismert)</label><input type="text" name="hunt_trophy_weight_age" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_trophy_weight_age'] ?? '')); ?>"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Kötelező képek</label><select name="hunt_required_media[]" class="va-select" multiple data-placeholder="Válassz kötelező képeket"><option value="vadaszterulet"<?php echo in_array( 'vadaszterulet', $hunt_required_media_saved, true ) ? ' selected' : ''; ?>>Vadászterület</option><option value="les"<?php echo in_array( 'les', $hunt_required_media_saved, true ) ? ' selected' : ''; ?>>Les</option><option value="allomany"<?php echo in_array( 'allomany', $hunt_required_media_saved, true ) ? ' selected' : ''; ?>>Állomány</option><option value="szallas"<?php echo in_array( 'szallas', $hunt_required_media_saved, true ) ? ' selected' : ''; ?>>Szállás</option><option value="trofeak"<?php echo in_array( 'trofeak', $hunt_required_media_saved, true ) ? ' selected' : ''; ?>>Trófeák (referencia)</option></select></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Videó</label><select name="hunt_video_types[]" class="va-select" multiple data-placeholder="Válassz videótípust"><option value="vadaszat-kozben"<?php echo in_array( 'vadaszat-kozben', $hunt_video_saved, true ) ? ' selected' : ''; ?>>Vadászat közben</option><option value="terulet-bemutato"<?php echo in_array( 'terulet-bemutato', $hunt_video_saved, true ) ? ' selected' : ''; ?>>Terület bemutató</option></select></div>
                <div class="va-form-group va-hunt-dynamic-group" data-hunt-price="trofea-alapu" style="display:none;"><label>Várható trófea kategória</label><input type="text" name="hunt_trophy_category" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_trophy_category'] ?? '')); ?>"></div>
                <div class="va-form-group va-hunt-dynamic-group" data-hunt-price="trofea-alapu" style="display:none;"><label>Súlyhatár</label><input type="text" name="hunt_trophy_weight_limit" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_trophy_weight_limit'] ?? '')); ?>"></div>
                <div class="va-form-group va-hunt-dynamic-group" data-hunt-type="kulfoldi-vadaszat" style="display:none;"><label>Utazás segítés</label><?php $v = (string)($edit_meta['hunt_travel_assist'] ?? ''); ?><select name="hunt_travel_assist" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-hunt-dynamic-group" data-hunt-type="kulfoldi-vadaszat" style="display:none;"><label>Tolmács</label><?php $v = (string)($edit_meta['hunt_interpreter'] ?? ''); ?><select name="hunt_interpreter" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected($v, 'igen'); ?>>Igen</option><option value="nem"<?php selected($v, 'nem'); ?>>Nem</option></select></div>
                <div class="va-form-group va-hunt-dynamic-group" data-hunt-method="hajtas" style="display:none;"><label>Csoport méret</label><input type="text" name="hunt_group_size" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_group_size'] ?? '')); ?>"></div>
                <div class="va-form-group va-hunt-dynamic-group" data-hunt-method="hajtas" style="display:none;"><label>Lövésszám limit</label><input type="text" name="hunt_shot_limit" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_shot_limit'] ?? '')); ?>"></div>
                <div class="va-form-group"><label>Valós idejű szabad helyek száma</label><input type="text" name="hunt_real_time_slots" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_real_time_slots'] ?? '')); ?>"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Foglalható időpontok (naptár)</label><input type="text" name="hunt_bookable_dates" class="va-input" value="<?php echo esc_attr((string)($edit_meta['hunt_bookable_dates'] ?? '')); ?>" placeholder="pl. 2026-10-02, 2026-10-09"></div>
                <div class="va-form-group" style="grid-column:1 / -1;"><label>Moderációs flag</label><select name="hunt_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázatos jelölések"><option value="engedely-nelkuli-vadaszat"<?php echo in_array( 'engedely-nelkuli-vadaszat', $hunt_moderation_saved, true ) ? ' selected' : ''; ?>>Engedély nélküli vadászat</option><option value="vedett-faj"<?php echo in_array( 'vedett-faj', $hunt_moderation_saved, true ) ? ' selected' : ''; ?>>Védett faj</option><option value="illegalis-terulet"<?php echo in_array( 'illegalis-terulet', $hunt_moderation_saved, true ) ? ' selected' : ''; ?>>Illegális terület</option><option value="hamis-allomany"<?php echo in_array( 'hamis-allomany', $hunt_moderation_saved, true ) ? ' selected' : ''; ?>>Hamis állomány</option></select></div>
                <div class="va-form-group">
                    <label>Idősáv kezdete (óra)</label>
                    <select name="hunt_slot_from_hour" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($hunt_slot_from_hour_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Idősáv vége (óra)</label>
                    <select name="hunt_slot_to_hour" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($hunt_slot_to_hour_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Létszám / fő</label>
                    <select name="hunt_capacity" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( ['1', '2-3', '4-6', '7-10', '10+'] as $cap ): ?>
                        <option value="<?php echo esc_attr($cap); ?>"<?php selected($hunt_capacity_val, $cap); ?>><?php echo esc_html($cap); ?> fő</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Vadászható fajok listája</label>
                    <input type="text" name="hunt_species_list" class="va-input" placeholder="pl. gímszarvas, dámszarvas, vaddisznó" value="<?php echo esc_attr( $hunt_species_list_val ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Lesbérleti / hozzáférési forma</label>
                    <select name="hunt_lease_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="alkalmi"<?php selected($hunt_lease_type_val, 'alkalmi'); ?>>Alkalmi</option>
                        <option value="eves"<?php selected($hunt_lease_type_val, 'eves'); ?>>Éves</option>
                        <option value="berles"<?php selected($hunt_lease_type_val, 'berles'); ?>>Bérlés</option>
                        <option value="tagsag"<?php selected($hunt_lease_type_val, 'tagsag'); ?>>Tagság</option>
                        <option value="tagsag-eladas"<?php selected($hunt_lease_type_val, 'tagsag-eladas'); ?>>Tagság eladása</option>
                        <option value="berbeadas"<?php selected($hunt_lease_type_val, 'berbeadas'); ?>>Bérbeadás</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Kísérő szolgáltatás</label>
                    <select name="hunt_has_guide" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_has_guide_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_has_guide_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Utánkeresés</label>
                    <select name="hunt_has_tracking" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_has_tracking_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_has_tracking_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Trófea kikészítés</label>
                    <select name="hunt_has_trophy_prep" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_has_trophy_prep_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_has_trophy_prep_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Trófea bírálás</label>
                    <select name="hunt_has_trophy_judging" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_has_trophy_judging_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_has_trophy_judging_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Terepjáró biztosított</label>
                    <select name="hunt_has_offroad" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_has_offroad_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_has_offroad_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Szállás lehetőség</label>
                    <select name="hunt_has_accommodation" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_has_accommodation_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_has_accommodation_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Elejtett vad megvásárolható</label>
                    <select name="hunt_can_buy_game" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($hunt_can_buy_game_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($hunt_can_buy_game_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
                </div><!-- /va-step2-4col-inner -->
            </div><!-- /.va-hunt-option-fields-grid -->

            <?php
            $vadcamera_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $vadcamera_connectivity_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_connectivity' ) ) ) );
            $vadcamera_app_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_mobile_app' ) ) ) );
            $vadcamera_power_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_power_types' ) ) ) );
            $vadcamera_accessories_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_accessories' ) ) ) );
            $vadcamera_use_cases_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_use_cases' ) ) ) );
            $vadcamera_mounting_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_mounting' ) ) ) );
            $vadcamera_shipping_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_shipping_methods' ) ) ) );
            $vadcamera_media_required_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_media_required' ) ) ) );
            $vadcamera_sample_media_saved = array_filter( array_map( 'trim', explode( ',', $vadcamera_meta( 'vadcamera_sample_media' ) ) ) );
            ?>
            <div class="va-cat-rule-field va-vadcamera-fields-grid" data-categories="vadkamera" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Kamera típusa</label>
                        <?php $v = $vadcamera_meta( 'vadcamera_camera_type' ); ?>
                        <select name="vadcamera_camera_type" class="va-select" id="va-vadcamera-camera-type">
                            <option value="">– Válasszon –</option>
                            <option value="klasszikus"<?php selected( $v, 'klasszikus' ); ?>>Klasszikus vadkamera</option>
                            <option value="4g-lte"<?php selected( $v, '4g-lte' ); ?>>4G/LTE vadkamera</option>
                            <option value="mms"<?php selected( $v, 'mms' ); ?>>MMS vadkamera</option>
                            <option value="wifi"<?php selected( $v, 'wifi' ); ?>>WiFi vadkamera</option>
                            <option value="bluetooth"<?php selected( $v, 'bluetooth' ); ?>>Bluetooth vadkamera</option>
                            <option value="napelemes"<?php selected( $v, 'napelemes' ); ?>>Napelemes vadkamera</option>
                            <option value="mini"<?php selected( $v, 'mini' ); ?>>Mini vadkamera</option>
                            <option value="biztonsagi"<?php selected( $v, 'biztonsagi' ); ?>>Biztonsági vadkamera</option>
                            <option value="time-lapse"<?php selected( $v, 'time-lapse' ); ?>>Time-lapse kamera</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Állapot</label>
                        <?php $v = $vadcamera_meta( 'vadcamera_condition' ); ?>
                        <select name="vadcamera_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option>
                            <option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option>
                            <option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option>
                            <option value="hibas"<?php selected( $v, 'hibas' ); ?>>Hibás</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Garancia</label>
                        <?php $v = $vadcamera_meta( 'vadcamera_warranty' ); ?>
                        <select name="vadcamera_warranty" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group">
                        <label>Gyártási év (opcionális)</label>
                        <input type="text" name="vadcamera_manufacture_year" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_manufacture_year' ) ); ?>" placeholder="pl. 2024">
                    </div>

                    <div class="va-form-group"><label>Fotó felbontás</label><?php $v = $vadcamera_meta( 'vadcamera_photo_resolution' ); ?><select name="vadcamera_photo_resolution" class="va-select"><option value="">– Válasszon –</option><option value="12mp"<?php selected( $v, '12mp' ); ?>>12 MP</option><option value="16mp"<?php selected( $v, '16mp' ); ?>>16 MP</option><option value="20mp"<?php selected( $v, '20mp' ); ?>>20 MP</option><option value="24mp"<?php selected( $v, '24mp' ); ?>>24 MP</option><option value="30mp"<?php selected( $v, '30mp' ); ?>>30 MP</option><option value="36mp"<?php selected( $v, '36mp' ); ?>>36 MP</option><option value="48mp"<?php selected( $v, '48mp' ); ?>>48 MP</option></select></div>
                    <div class="va-form-group"><label>Videó felbontás</label><?php $v = $vadcamera_meta( 'vadcamera_video_resolution' ); ?><select name="vadcamera_video_resolution" class="va-select"><option value="">– Válasszon –</option><option value="hd"<?php selected( $v, 'hd' ); ?>>HD</option><option value="full-hd"<?php selected( $v, 'full-hd' ); ?>>Full HD</option><option value="2k"<?php selected( $v, '2k' ); ?>>2K</option><option value="4k"<?php selected( $v, '4k' ); ?>>4K</option></select></div>
                    <div class="va-form-group"><label>FPS</label><?php $v = $vadcamera_meta( 'vadcamera_video_fps' ); ?><select name="vadcamera_video_fps" class="va-select"><option value="">– Válasszon –</option><option value="15"<?php selected( $v, '15' ); ?>>15</option><option value="24"<?php selected( $v, '24' ); ?>>24</option><option value="30"<?php selected( $v, '30' ); ?>>30</option><option value="60"<?php selected( $v, '60' ); ?>>60</option></select></div>
                    <div class="va-form-group"><label>Videó hanggal?</label><?php $v = $vadcamera_meta( 'vadcamera_video_audio' ); ?><select name="vadcamera_video_audio" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>

                    <div class="va-form-group"><label>Infra típusa</label><?php $v = $vadcamera_meta( 'vadcamera_ir_type' ); ?><select name="vadcamera_ir_type" class="va-select"><option value="">– Válasszon –</option><option value="no-glow-940"<?php selected( $v, 'no-glow-940' ); ?>>No glow (940nm)</option><option value="low-glow-850"<?php selected( $v, 'low-glow-850' ); ?>>Low glow (850nm)</option><option value="feher-vaku"<?php selected( $v, 'feher-vaku' ); ?>>Fehér vaku</option></select></div>
                    <div class="va-form-group"><label>Éjszakai hatótáv (m)</label><input type="text" name="vadcamera_night_range_m" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_night_range_m' ) ); ?>" placeholder="pl. 25"></div>
                    <div class="va-form-group"><label>Infra LED-ek száma</label><input type="text" name="vadcamera_ir_led_count" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_ir_led_count' ) ); ?>" placeholder="pl. 44"></div>

                    <div class="va-form-group"><label>Trigger idő</label><?php $v = $vadcamera_meta( 'vadcamera_trigger_speed' ); ?><select name="vadcamera_trigger_speed" class="va-select"><option value="">– Válasszon –</option><option value="0.1s"<?php selected( $v, '0.1s' ); ?>>0.1 sec</option><option value="0.2s"<?php selected( $v, '0.2s' ); ?>>0.2 sec</option><option value="0.3s"<?php selected( $v, '0.3s' ); ?>>0.3 sec</option><option value="0.4s"<?php selected( $v, '0.4s' ); ?>>0.4 sec</option><option value="0.5s"<?php selected( $v, '0.5s' ); ?>>0.5 sec</option><option value="1.0s"<?php selected( $v, '1.0s' ); ?>>1.0 sec</option></select></div>
                    <div class="va-form-group"><label>PIR érzékelési távolság (m)</label><input type="text" name="vadcamera_pir_distance_m" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_pir_distance_m' ) ); ?>" placeholder="pl. 20"></div>
                    <div class="va-form-group"><label>Látószög (fok)</label><input type="text" name="vadcamera_view_angle_deg" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_view_angle_deg' ) ); ?>" placeholder="pl. 120"></div>

                    <div class="va-form-group" style="grid-column: span 2;"><label>Adatkapcsolat</label><select name="vadcamera_connectivity[]" class="va-select" multiple data-placeholder="Válassz kapcsolatot"><option value="2g"<?php echo in_array( '2g', $vadcamera_connectivity_saved, true ) ? ' selected' : ''; ?>>2G</option><option value="3g"<?php echo in_array( '3g', $vadcamera_connectivity_saved, true ) ? ' selected' : ''; ?>>3G</option><option value="4g"<?php echo in_array( '4g', $vadcamera_connectivity_saved, true ) ? ' selected' : ''; ?>>4G</option><option value="lte"<?php echo in_array( 'lte', $vadcamera_connectivity_saved, true ) ? ' selected' : ''; ?>>LTE</option><option value="wifi"<?php echo in_array( 'wifi', $vadcamera_connectivity_saved, true ) ? ' selected' : ''; ?>>WiFi</option><option value="bluetooth"<?php echo in_array( 'bluetooth', $vadcamera_connectivity_saved, true ) ? ' selected' : ''; ?>>Bluetooth</option></select></div>
                    <div class="va-form-group"><label>SIM kártyás?</label><?php $v = $vadcamera_meta( 'vadcamera_sim_slot' ); ?><select name="vadcamera_sim_slot" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Mobil app</label><select name="vadcamera_mobile_app[]" class="va-select" multiple data-placeholder="Válassz app támogatást"><option value="android"<?php echo in_array( 'android', $vadcamera_app_saved, true ) ? ' selected' : ''; ?>>Android</option><option value="ios"<?php echo in_array( 'ios', $vadcamera_app_saved, true ) ? ' selected' : ''; ?>>iOS</option></select></div>
                    <div class="va-form-group va-vadcamera-dynamic-group" data-vadcamera-types="4g-lte" style="grid-column: span 2; display:none;"><label>Szolgáltató kompatibilitás</label><input type="text" name="vadcamera_4g_carrier" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_4g_carrier' ) ); ?>" placeholder="pl. Telekom, Yettel, Vodafone"></div>
                    <div class="va-form-group va-vadcamera-dynamic-group" data-vadcamera-types="4g-lte" style="display:none;"><label>App támogatás részlete</label><input type="text" name="vadcamera_4g_app_support" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_4g_app_support' ) ); ?>" placeholder="pl. UCon, TrailCam Go"></div>

                    <div class="va-form-group"><label>SD kártya támogatás (max GB)</label><input type="text" name="vadcamera_sd_max_gb" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_sd_max_gb' ) ); ?>" placeholder="pl. 128"></div>
                    <div class="va-form-group"><label>Belső memória</label><?php $v = $vadcamera_meta( 'vadcamera_internal_memory' ); ?><select name="vadcamera_internal_memory" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column: span 2;"><label>Energiaforrás</label><select name="vadcamera_power_types[]" class="va-select" multiple data-placeholder="Válassz energiaforrást"><option value="aa"<?php echo in_array( 'aa', $vadcamera_power_saved, true ) ? ' selected' : ''; ?>>AA elem</option><option value="li-ion"<?php echo in_array( 'li-ion', $vadcamera_power_saved, true ) ? ' selected' : ''; ?>>Li-ion</option><option value="beepitett-akku"<?php echo in_array( 'beepitett-akku', $vadcamera_power_saved, true ) ? ' selected' : ''; ?>>Beépített akkumulátor</option><option value="kulso-12v"<?php echo in_array( 'kulso-12v', $vadcamera_power_saved, true ) ? ' selected' : ''; ?>>Külső 12V</option><option value="napelem"<?php echo in_array( 'napelem', $vadcamera_power_saved, true ) ? ' selected' : ''; ?>>Napelem</option></select></div>
                    <div class="va-form-group"><label>Üzemidő</label><input type="text" name="vadcamera_runtime" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_runtime' ) ); ?>" placeholder="pl. 3 hónap"></div>
                    <div class="va-form-group va-vadcamera-dynamic-group" data-vadcamera-types="napelemes" style="display:none;"><label>Panel teljesítmény</label><input type="text" name="vadcamera_solar_panel_power" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_solar_panel_power' ) ); ?>" placeholder="pl. 5W"></div>

                    <div class="va-form-group"><label>IP védelem</label><?php $v = $vadcamera_meta( 'vadcamera_ip_rating' ); ?><select name="vadcamera_ip_rating" class="va-select"><option value="">– Válasszon –</option><option value="ip54"<?php selected( $v, 'ip54' ); ?>>IP54</option><option value="ip65"<?php selected( $v, 'ip65' ); ?>>IP65</option><option value="ip66"<?php selected( $v, 'ip66' ); ?>>IP66</option><option value="ip67"<?php selected( $v, 'ip67' ); ?>>IP67</option></select></div>
                    <div class="va-form-group"><label>Hőmérséklet tartomány</label><input type="text" name="vadcamera_temp_range" class="va-input" value="<?php echo esc_attr( $vadcamera_meta( 'vadcamera_temp_range' ) ); ?>" placeholder="pl. -20°C – +60°C"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Tartozékok</label><select name="vadcamera_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékokat"><option value="sd-kartya"<?php echo in_array( 'sd-kartya', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>SD kártya</option><option value="sim-kartya"<?php echo in_array( 'sim-kartya', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>SIM kártya</option><option value="napelem"<?php echo in_array( 'napelem', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>Napelem</option><option value="akkumulator"<?php echo in_array( 'akkumulator', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>Akkumulátor</option><option value="tartokonzol"<?php echo in_array( 'tartokonzol', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>Tartókonzol</option><option value="heveder"<?php echo in_array( 'heveder', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>Heveder</option><option value="doboz"<?php echo in_array( 'doboz', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>Doboz</option><option value="tolto"<?php echo in_array( 'tolto', $vadcamera_accessories_saved, true ) ? ' selected' : ''; ?>>Töltő</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Felhasználási terület</label><select name="vadcamera_use_cases[]" class="va-select" multiple data-placeholder="Válassz felhasználási területet"><option value="vadmegfigyeles"<?php echo in_array( 'vadmegfigyeles', $vadcamera_use_cases_saved, true ) ? ' selected' : ''; ?>>Vadmegfigyelés</option><option value="eteto"<?php echo in_array( 'eteto', $vadcamera_use_cases_saved, true ) ? ' selected' : ''; ?>>Etető megfigyelés</option><option value="biztonsag"<?php echo in_array( 'biztonsag', $vadcamera_use_cases_saved, true ) ? ' selected' : ''; ?>>Biztonságtechnika</option><option value="termeszetfoto"<?php echo in_array( 'termeszetfoto', $vadcamera_use_cases_saved, true ) ? ' selected' : ''; ?>>Természetfotó</option><option value="time-lapse"<?php echo in_array( 'time-lapse', $vadcamera_use_cases_saved, true ) ? ' selected' : ''; ?>>Time-lapse</option><option value="farm"<?php echo in_array( 'farm', $vadcamera_use_cases_saved, true ) ? ' selected' : ''; ?>>Farm megfigyelés</option></select></div>
                    <div class="va-form-group"><label>Hangrögzítés van?</label><?php $v = $vadcamera_meta( 'vadcamera_audio_record' ); ?><select name="vadcamera_audio_record" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Közterület megfigyelésre használható?</label><?php $v = $vadcamera_meta( 'vadcamera_public_area_use' ); ?><select name="vadcamera_public_area_use" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Elhelyezés</label><select name="vadcamera_mounting[]" class="va-select" multiple data-placeholder="Válassz elhelyezési módot"><option value="fara"<?php echo in_array( 'fara', $vadcamera_mounting_saved, true ) ? ' selected' : ''; ?>>Fára szerelhető</option><option value="tripod"<?php echo in_array( 'tripod', $vadcamera_mounting_saved, true ) ? ' selected' : ''; ?>>Tripod kompatibilis</option><option value="magneses"<?php echo in_array( 'magneses', $vadcamera_mounting_saved, true ) ? ' selected' : ''; ?>>Mágneses</option><option value="fix-konzol"<?php echo in_array( 'fix-konzol', $vadcamera_mounting_saved, true ) ? ' selected' : ''; ?>>Fix konzolos</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Szállítás</label><select name="vadcamera_shipping_methods[]" class="va-select" multiple data-placeholder="Válassz szállítási módot"><option value="posta"<?php echo in_array( 'posta', $vadcamera_shipping_saved, true ) ? ' selected' : ''; ?>>Posta</option><option value="futar"<?php echo in_array( 'futar', $vadcamera_shipping_saved, true ) ? ' selected' : ''; ?>>Futár</option><option value="szemelyes"<?php echo in_array( 'szemelyes', $vadcamera_shipping_saved, true ) ? ' selected' : ''; ?>>Személyes</option></select></div>
                    <div class="va-form-group" style="grid-column: span 2;"><label>Kötelező képek</label><select name="vadcamera_media_required[]" class="va-select" multiple data-placeholder="Válassz kötelező képeket"><option value="elol"<?php echo in_array( 'elol', $vadcamera_media_required_saved, true ) ? ' selected' : ''; ?>>Elöl</option><option value="hatul"<?php echo in_array( 'hatul', $vadcamera_media_required_saved, true ) ? ' selected' : ''; ?>>Hátul</option><option value="infra-led"<?php echo in_array( 'infra-led', $vadcamera_media_required_saved, true ) ? ' selected' : ''; ?>>Infra LED</option><option value="kijelzo"<?php echo in_array( 'kijelzo', $vadcamera_media_required_saved, true ) ? ' selected' : ''; ?>>Kijelző</option><option value="tartozekok"<?php echo in_array( 'tartozekok', $vadcamera_media_required_saved, true ) ? ' selected' : ''; ?>>Tartozékok</option></select></div>
                    <div class="va-form-group" style="grid-column: span 2;"><label>Mintaképek / videók</label><select name="vadcamera_sample_media[]" class="va-select" multiple data-placeholder="Válassz mintamédiát"><option value="nappali-kep"<?php echo in_array( 'nappali-kep', $vadcamera_sample_media_saved, true ) ? ' selected' : ''; ?>>Nappali mintakép</option><option value="ejszakai-kep"<?php echo in_array( 'ejszakai-kep', $vadcamera_sample_media_saved, true ) ? ' selected' : ''; ?>>Éjszakai mintakép</option><option value="video"<?php echo in_array( 'video', $vadcamera_sample_media_saved, true ) ? ' selected' : ''; ?>>Videóminta</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Technikai megjegyzés</label><textarea name="vadcamera_notes" class="va-input" rows="3" placeholder="pl. trigger valós mérés, app verzió, SIM/APN tapasztalatok..."><?php echo esc_textarea( $vadcamera_meta( 'vadcamera_notes' ) ); ?></textarea></div>
                </div>
            </div>

            <?php
            $estate_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $estate_transfer_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_transfer_methods' ) ) ) );

            $estate_optic_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_optic_items' ) ) ) );
            if ( empty( $estate_optic_saved ) ) {
                if ( $estate_meta( 'estate_optic_celtavcso' ) === '1' ) { $estate_optic_saved[] = 'celtavcso'; }
                if ( $estate_meta( 'estate_optic_binokular' ) === '1' ) { $estate_optic_saved[] = 'binokular'; }
                if ( $estate_meta( 'estate_optic_spektiv' ) === '1' ) { $estate_optic_saved[] = 'spektiv'; }
                if ( $estate_meta( 'estate_optic_reddot' ) === '1' ) { $estate_optic_saved[] = 'reddot'; }
            }

            $estate_clothing_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_clothing_items' ) ) ) );
            if ( empty( $estate_clothing_saved ) ) {
                if ( $estate_meta( 'estate_clothing_kabat' ) === '1' ) { $estate_clothing_saved[] = 'kabat'; }
                if ( $estate_meta( 'estate_clothing_nadrag' ) === '1' ) { $estate_clothing_saved[] = 'nadrag'; }
                if ( $estate_meta( 'estate_clothing_csizma' ) === '1' ) { $estate_clothing_saved[] = 'csizma'; }
                if ( $estate_meta( 'estate_clothing_esoruha' ) === '1' ) { $estate_clothing_saved[] = 'esoruha'; }
            }

            $estate_trophy_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_trophy_items' ) ) ) );
            if ( empty( $estate_trophy_saved ) ) {
                if ( $estate_meta( 'estate_trophy_agancs' ) === '1' ) { $estate_trophy_saved[] = 'agancs'; }
                if ( $estate_meta( 'estate_trophy_koponya' ) === '1' ) { $estate_trophy_saved[] = 'koponya'; }
                if ( $estate_meta( 'estate_trophy_preparatum' ) === '1' ) { $estate_trophy_saved[] = 'preparatum'; }
            }

            $estate_docs_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_docs_items' ) ) ) );
            if ( empty( $estate_docs_saved ) ) {
                if ( $estate_meta( 'estate_docs_engedelyek' ) === '1' ) { $estate_docs_saved[] = 'engedelyek'; }
                if ( $estate_meta( 'estate_docs_vadasznaplo' ) === '1' ) { $estate_docs_saved[] = 'vadasznaplo'; }
                if ( $estate_meta( 'estate_docs_konyvek' ) === '1' ) { $estate_docs_saved[] = 'konyvek'; }
                if ( $estate_meta( 'estate_docs_tanusitvanyok' ) === '1' ) { $estate_docs_saved[] = 'tanusitvanyok'; }
            }

            $estate_equipment_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_equipment_items' ) ) ) );
            if ( empty( $estate_equipment_saved ) ) {
                if ( $estate_meta( 'estate_equipment_kes' ) === '1' ) { $estate_equipment_saved[] = 'kes'; }
                if ( $estate_meta( 'estate_equipment_hatizsak' ) === '1' ) { $estate_equipment_saved[] = 'hatizsak'; }
                if ( $estate_meta( 'estate_equipment_lampa' ) === '1' ) { $estate_equipment_saved[] = 'lampa'; }
                if ( $estate_meta( 'estate_equipment_tavcso_tartozek' ) === '1' ) { $estate_equipment_saved[] = 'tavcso_tartozek'; }
                if ( $estate_meta( 'estate_equipment_fegyverszef' ) === '1' ) { $estate_equipment_saved[] = 'fegyverszef'; }
            }

            $estate_media_saved = array_filter( array_map( 'trim', explode( ',', $estate_meta( 'estate_media_items' ) ) ) );
            if ( empty( $estate_media_saved ) ) {
                if ( $estate_meta( 'estate_media_full' ) === '1' ) { $estate_media_saved[] = 'full'; }
                if ( $estate_meta( 'estate_media_details' ) === '1' ) { $estate_media_saved[] = 'details'; }
                if ( $estate_meta( 'estate_media_documents' ) === '1' ) { $estate_media_saved[] = 'documents'; }
                if ( $estate_meta( 'estate_media_firearms' ) === '1' ) { $estate_media_saved[] = 'firearms'; }
            }
            ?>
            <div class="va-cat-rule-field va-estate-fields-grid" data-categories="vadaszati-hagyatek" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Fő típus</label>
                        <?php $v = $estate_meta( 'estate_main_type' ); ?>
                        <select name="estate_main_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="komplett-hagyatek"<?php selected( $v, 'komplett-hagyatek' ); ?>>Komplett hagyaték</option>
                            <option value="fegyver-hagyatek"<?php selected( $v, 'fegyver-hagyatek' ); ?>>Fegyver hagyaték</option>
                            <option value="optikai-hagyatek"<?php selected( $v, 'optikai-hagyatek' ); ?>>Optikai hagyaték</option>
                            <option value="ruhazat-felszereles"<?php selected( $v, 'ruhazat-felszereles' ); ?>>Ruházat / felszerelés</option>
                            <option value="trofea-preparatum"<?php selected( $v, 'trofea-preparatum' ); ?>>Trófea / preparátum</option>
                            <option value="konyv-dokumentum"<?php selected( $v, 'konyv-dokumentum' ); ?>>Könyv / dokumentum</option>
                            <option value="vegyes-hagyatek"<?php selected( $v, 'vegyes-hagyatek' ); ?>>Vegyes hagyaték</option>
                            <option value="gyujtoi-kollekcio"<?php selected( $v, 'gyujtoi-kollekcio' ); ?>>Gyűjtői kollekció</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Eladás típusa</label>
                        <?php $v = $estate_meta( 'estate_sale_mode' ); ?>
                        <select name="estate_sale_mode" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="egyben"<?php selected( $v, 'egyben' ); ?>>Egyben</option>
                            <option value="darabonkent"<?php selected( $v, 'darabonkent' ); ?>>Darabonként</option>
                            <option value="reszletekben"<?php selected( $v, 'reszletekben' ); ?>>Részletekben</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Teljes ár</label>
                        <input type="text" name="estate_price_total" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_price_total' ) ); ?>" placeholder="pl. 2 500 000 Ft">
                    </div>
                    <div class="va-form-group">
                        <label>Irányár</label>
                        <input type="text" name="estate_price_guide" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_price_guide' ) ); ?>" placeholder="pl. 2 200 000 Ft">
                    </div>
                    <div class="va-form-group">
                        <label>Darabár (ha bontva)</label>
                        <input type="text" name="estate_price_per_item" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_price_per_item' ) ); ?>" placeholder="pl. 20 000 Ft-tól">
                    </div>
                    <div class="va-form-group">
                        <label>Becsült érték (opcionális)</label>
                        <input type="text" name="estate_estimated_value" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_estimated_value' ) ); ?>" placeholder="pl. 3 100 000 Ft">
                    </div>
                    <div class="va-form-group">
                        <label>Alku</label>
                        <?php $v = $estate_meta( 'estate_negotiable' ); ?>
                        <select name="estate_negotiable" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option>
                            <option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Tartalom összetétele</strong></div>
                    <div class="va-form-group">
                        <label>Fegyverek darabszám</label>
                        <input type="text" name="estate_firearms_count" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_firearms_count' ) ); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Fegyver típus</label>
                        <?php $v = $estate_meta( 'estate_firearms_type' ); ?>
                        <select name="estate_firearms_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="pisztoly"<?php selected( $v, 'pisztoly' ); ?>>Pisztoly</option>
                            <option value="puska"<?php selected( $v, 'puska' ); ?>>Puska</option>
                            <option value="vegyes"<?php selected( $v, 'vegyes' ); ?>>Vegyes</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Hatástalanított?</label>
                        <?php $v = $estate_meta( 'estate_firearms_deactivated' ); ?>
                        <select name="estate_firearms_deactivated" class="va-select"><option value="">–</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group">
                        <label>Engedélyköteles?</label>
                        <?php $v = $estate_meta( 'estate_firearms_licensed' ); ?>
                        <select name="estate_firearms_licensed" class="va-select"><option value="">–</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>

                    <div class="va-form-group">
                        <label>Optika darabszám</label>
                        <input type="text" name="estate_optics_count" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_optics_count' ) ); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column: span 3;">
                        <label>Optika típusok</label>
                        <select name="estate_optic_items[]" class="va-select" multiple data-placeholder="Válassz optika típust">
                            <option value="celtavcso"<?php echo in_array( 'celtavcso', $estate_optic_saved, true ) ? ' selected' : ''; ?>>Céltávcső</option>
                            <option value="binokular"<?php echo in_array( 'binokular', $estate_optic_saved, true ) ? ' selected' : ''; ?>>Binokulár</option>
                            <option value="spektiv"<?php echo in_array( 'spektiv', $estate_optic_saved, true ) ? ' selected' : ''; ?>>Spektív</option>
                            <option value="reddot"<?php echo in_array( 'reddot', $estate_optic_saved, true ) ? ' selected' : ''; ?>>Red dot</option>
                        </select>
                    </div>

                    <div class="va-form-group">
                        <label>Ruházat darabszám</label>
                        <input type="text" name="estate_clothing_count" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_clothing_count' ) ); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column: span 3;">
                        <label>Ruházat típusok</label>
                        <select name="estate_clothing_items[]" class="va-select" multiple data-placeholder="Válassz ruházat típust">
                            <option value="kabat"<?php echo in_array( 'kabat', $estate_clothing_saved, true ) ? ' selected' : ''; ?>>Kabát</option>
                            <option value="nadrag"<?php echo in_array( 'nadrag', $estate_clothing_saved, true ) ? ' selected' : ''; ?>>Nadrág</option>
                            <option value="csizma"<?php echo in_array( 'csizma', $estate_clothing_saved, true ) ? ' selected' : ''; ?>>Csizma</option>
                            <option value="esoruha"<?php echo in_array( 'esoruha', $estate_clothing_saved, true ) ? ' selected' : ''; ?>>Esőruha</option>
                        </select>
                    </div>

                    <div class="va-form-group">
                        <label>Trófea darabszám</label>
                        <input type="text" name="estate_trophy_count" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_trophy_count' ) ); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column: span 3;">
                        <label>Trófea típusok</label>
                        <select name="estate_trophy_items[]" class="va-select" multiple data-placeholder="Válassz trófea típust">
                            <option value="agancs"<?php echo in_array( 'agancs', $estate_trophy_saved, true ) ? ' selected' : ''; ?>>Agancs</option>
                            <option value="koponya"<?php echo in_array( 'koponya', $estate_trophy_saved, true ) ? ' selected' : ''; ?>>Koponya</option>
                            <option value="preparatum"<?php echo in_array( 'preparatum', $estate_trophy_saved, true ) ? ' selected' : ''; ?>>Preparátum</option>
                        </select>
                    </div>

                    <div class="va-form-group">
                        <label>Dokumentumok darabszám</label>
                        <input type="text" name="estate_docs_count" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_docs_count' ) ); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column: span 3;">
                        <label>Dokumentum típusok</label>
                        <select name="estate_docs_items[]" class="va-select" multiple data-placeholder="Válassz dokumentum típust">
                            <option value="engedelyek"<?php echo in_array( 'engedelyek', $estate_docs_saved, true ) ? ' selected' : ''; ?>>Engedélyek</option>
                            <option value="vadasznaplo"<?php echo in_array( 'vadasznaplo', $estate_docs_saved, true ) ? ' selected' : ''; ?>>Vadásznapló</option>
                            <option value="konyvek"<?php echo in_array( 'konyvek', $estate_docs_saved, true ) ? ' selected' : ''; ?>>Könyvek</option>
                            <option value="tanusitvanyok"<?php echo in_array( 'tanusitvanyok', $estate_docs_saved, true ) ? ' selected' : ''; ?>>Tanúsítványok</option>
                        </select>
                    </div>

                    <div class="va-form-group">
                        <label>Egyéb felszerelés darabszám</label>
                        <input type="text" name="estate_equipment_count" class="va-input" value="<?php echo esc_attr( $estate_meta( 'estate_equipment_count' ) ); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column: span 3;">
                        <label>Egyéb felszerelés típusok</label>
                        <select name="estate_equipment_items[]" class="va-select" multiple data-placeholder="Válassz felszerelés típust">
                            <option value="kes"<?php echo in_array( 'kes', $estate_equipment_saved, true ) ? ' selected' : ''; ?>>Kés</option>
                            <option value="hatizsak"<?php echo in_array( 'hatizsak', $estate_equipment_saved, true ) ? ' selected' : ''; ?>>Hátizsák</option>
                            <option value="lampa"<?php echo in_array( 'lampa', $estate_equipment_saved, true ) ? ' selected' : ''; ?>>Lámpa</option>
                            <option value="tavcso_tartozek"<?php echo in_array( 'tavcso_tartozek', $estate_equipment_saved, true ) ? ' selected' : ''; ?>>Távcső tartozék</option>
                            <option value="fegyverszef"<?php echo in_array( 'fegyverszef', $estate_equipment_saved, true ) ? ' selected' : ''; ?>>Fegyverszéf</option>
                        </select>
                    </div>

                    <div class="va-form-group">
                        <label>Állapot</label>
                        <?php $v = $estate_meta( 'estate_condition' ); ?>
                        <select name="estate_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="teljes"<?php selected( $v, 'teljes' ); ?>>Teljes hagyaték</option>
                            <option value="reszben-rendezett"<?php selected( $v, 'reszben-rendezett' ); ?>>Részben rendezett</option>
                            <option value="rendezetlen"<?php selected( $v, 'rendezetlen' ); ?>>Rendezetlen</option>
                            <option value="katalogizalt"<?php selected( $v, 'katalogizalt' ); ?>>Katalogizált</option>
                            <option value="muzealis"<?php selected( $v, 'muzealis' ); ?>>Múzeális jellegű</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Hagyaték jellege</label>
                        <?php $v = $estate_meta( 'estate_origin' ); ?>
                        <select name="estate_origin" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="maganszemely"<?php selected( $v, 'maganszemely' ); ?>>Magánszemély hagyaték</option>
                            <option value="vadasztarsasag"<?php selected( $v, 'vadasztarsasag' ); ?>>Vadásztársaság felszámolás</option>
                            <option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői hagyaték</option>
                            <option value="orokseg"<?php selected( $v, 'orokseg' ); ?>>Örökség</option>
                            <option value="bontas"<?php selected( $v, 'bontas' ); ?>>Bontásból származó</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Jogi státusz</strong></div>
                    <div class="va-form-group"><label>Engedélyköteles tárgyak?</label><?php $v = $estate_meta( 'estate_legal_has_licensed_items' ); ?><select name="estate_legal_has_licensed_items" class="va-select"><option value="">–</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Fegyverek hatástalanítottak?</label><?php $v = $estate_meta( 'estate_legal_deactivated' ); ?><select name="estate_legal_deactivated" class="va-select"><option value="">–</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Papírok rendelkezésre állnak?</label><?php $v = $estate_meta( 'estate_legal_has_papers' ); ?><select name="estate_legal_has_papers" class="va-select"><option value="">–</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Csak egyben átvehető?</label><?php $v = $estate_meta( 'estate_legal_bundle_only' ); ?><select name="estate_legal_bundle_only" class="va-select"><option value="">–</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>

                    <div class="va-form-group">
                        <label>Átadás</label>
                        <select name="estate_transfer_methods[]" class="va-select" multiple>
                            <option value="szemelyes"<?php echo in_array( 'szemelyes', $estate_transfer_saved, true ) ? ' selected' : ''; ?>>Személyes átvétel</option>
                            <option value="helyszini"<?php echo in_array( 'helyszini', $estate_transfer_saved, true ) ? ' selected' : ''; ?>>Helyszíni átadás</option>
                            <option value="szallitas"<?php echo in_array( 'szallitas', $estate_transfer_saved, true ) ? ' selected' : ''; ?>>Szállítás megoldható</option>
                            <option value="rakodassal"<?php echo in_array( 'rakodassal', $estate_transfer_saved, true ) ? ' selected' : ''; ?>>Csak rakodással együtt</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Média ellenőrző lista</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Média elemek</label>
                        <select name="estate_media_items[]" class="va-select" multiple data-placeholder="Válassz média elemet">
                            <option value="full"<?php echo in_array( 'full', $estate_media_saved, true ) ? ' selected' : ''; ?>>Teljes gyűjtemény fotó feltöltve</option>
                            <option value="details"<?php echo in_array( 'details', $estate_media_saved, true ) ? ' selected' : ''; ?>>Részletfotók feltöltve</option>
                            <option value="documents"<?php echo in_array( 'documents', $estate_media_saved, true ) ? ' selected' : ''; ?>>Dokumentumok fotózva</option>
                            <option value="firearms"<?php echo in_array( 'firearms', $estate_media_saved, true ) ? ' selected' : ''; ?>>Fegyverek külön fotózva</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Hagyaték megjegyzés / részletezés</label>
                        <textarea name="estate_notes" class="va-input" rows="4" placeholder="Részletek a kollekcióról, átvételről, csomagolásról, előéletről..."><?php echo esc_textarea( $estate_meta( 'estate_notes' ) ); ?></textarea>
                    </div>
                </div>
            </div>

            <?php
                $dog_breed_options = [];
                $dog_catalog = [];
                if ( isset( $hunting_brand_models['vadaszkutya'] ) && is_array( $hunting_brand_models['vadaszkutya'] ) ) {
                    $dog_catalog = $hunting_brand_models['vadaszkutya'];
                } elseif ( isset( $brand_models['vadaszkutya'] ) && is_array( $brand_models['vadaszkutya'] ) ) {
                    $dog_catalog = $brand_models['vadaszkutya'];
                }
                foreach ( $dog_catalog as $dog_group => $dog_items ) {
                    $dog_group = trim( (string) $dog_group );
                    if ( $dog_group !== '' ) {
                        $group_value = sanitize_title( $dog_group );
                        $group_label = ucwords( str_replace( [ '-', '_' ], ' ', $dog_group ) );
                        $dog_breed_options[ $group_value ] = $group_label;
                    }
                    if ( ! is_array( $dog_items ) ) {
                        continue;
                    }
                    foreach ( $dog_items as $dog_item ) {
                        $dog_item = trim( (string) $dog_item );
                        if ( $dog_item === '' ) {
                            continue;
                        }
                        $opt_value = sanitize_title( $dog_item );
                        $opt_label = ucwords( str_replace( [ '-', '_' ], ' ', $dog_item ) );
                        if ( $dog_group !== '' ) {
                            $opt_label .= ' (' . ucwords( str_replace( [ '-', '_' ], ' ', $dog_group ) ) . ')';
                        }
                        $dog_breed_options[ $opt_value ] = $opt_label;
                    }
                }
                if ( empty( $dog_breed_options ) ) {
                    $dog_breed_options = [
                        'magyar-vizsla-rovidszoru' => 'Magyar vizsla (rövidszőrű)',
                        'magyar-vizsla-drotszoru'  => 'Magyar vizsla (drótszőrű)',
                        'erdelyi-kopo'             => 'Erdélyi kopó',
                        'magyar-agar'              => 'Magyar agár',
                        'angol-pointer'            => 'Angol pointer',
                        'angol-setter'             => 'Angol szetter',
                        'gordon-setter'            => 'Gordon szetter',
                        'labrador-retriever'       => 'Labrador retriever',
                        'golden-retriever'         => 'Golden retriever',
                        'beagle'                   => 'Beagle',
                        'jagdterrier'              => 'Jagdterrier'
                    ];
                }
                asort( $dog_breed_options, SORT_NATURAL | SORT_FLAG_CASE );
                $dog_breed_options['egyeb'] = 'Egyéb';
                $dog_color_options = [
                    'fekete'          => 'Fekete',
                    'feher'           => 'Fehér',
                    'barna'           => 'Barna',
                    'csoki'           => 'Csoki',
                    'voros'           => 'Vörös',
                    'sarga'           => 'Sárga',
                    'arany'           => 'Arany',
                    'szurke'          => 'Szürke',
                    'fekete-feher'    => 'Fekete-fehér',
                    'barna-feher'     => 'Barna-fehér',
                    'voros-feher'     => 'Vörös-fehér',
                    'tricolor'        => 'Tricolor',
                    'cifra-tarkas'    => 'Cifra / tarka',
                    'egyeb'           => 'Egyéb'
                ];
                $dog_documents_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_documents'] ?? '' ) ) ) );
                $dog_training_skills_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_training_skills'] ?? '' ) ) ) );
                $dog_specialization_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_hunting_specialization'] ?? '' ) ) ) );
                $dog_environment_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_environment'] ?? '' ) ) ) );
                $dog_origin_qualification_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_origin_qualification'] ?? '' ) ) ) );
                $dog_moderation_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_moderation_flags'] ?? '' ) ) ) );
                $dog_work_video_saved = array_filter( array_map( 'trim', explode( ',', (string) ( $edit_meta['dog_work_video_types'] ?? '' ) ) ) );
                $saved_dog_breed = (string) ($edit_meta['dog_breed'] ?? '');
                $saved_dog_color = (string) ($edit_meta['dog_color'] ?? '');
            ?>
            <div class="va-cat-rule-field va-dog-fields-wrap" data-categories="vadaszkutya">
                <div class="va-step2-4col-inner va-dog-4col">
                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Vadászkutya típusa</strong></div>
                    <div class="va-form-group">
                        <label>Vadászati feladat szerint</label>
                        <?php $v = (string) ( $edit_meta['dog_working_type'] ?? '' ); ?>
                        <select name="dog_working_type" id="va-dog-working-type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="vizsla"<?php selected( $v, 'vizsla' ); ?>>Vizsla</option>
                            <option value="drotszoru-vizsla"<?php selected( $v, 'drotszoru-vizsla' ); ?>>Drótszőrű vizsla</option>
                            <option value="rovidszoru-vizsla"<?php selected( $v, 'rovidszoru-vizsla' ); ?>>Rövidszőrű vizsla</option>
                            <option value="kopo"<?php selected( $v, 'kopo' ); ?>>Kopó</option>
                            <option value="vereb"<?php selected( $v, 'vereb' ); ?>>Véreb</option>
                            <option value="terrier"<?php selected( $v, 'terrier' ); ?>>Terrier</option>
                            <option value="tacsko"<?php selected( $v, 'tacsko' ); ?>>Tacskó</option>
                            <option value="agar"<?php selected( $v, 'agar' ); ?>>Agar</option>
                            <option value="apportirozo"<?php selected( $v, 'apportirozo' ); ?>>Apportírozó kutya</option>
                            <option value="hajtokutya"<?php selected( $v, 'hajtokutya' ); ?>>Hajtókutya</option>
                            <option value="nyomkoveto"<?php selected( $v, 'nyomkoveto' ); ?>>Nyomkövető</option>
                            <option value="vaddisznos-kutya"<?php selected( $v, 'vaddisznos-kutya' ); ?>>Vaddisznós kutya</option>
                            <option value="univerzalis"<?php selected( $v, 'univerzalis' ); ?>>Univerzális vadászkutya</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Fajta</label>
                        <select name="dog_breed" class="va-select">
                            <option value="">– Válasszon fajtát –</option>
                            <?php if ( $saved_dog_breed !== '' && ! isset( $dog_breed_options[ $saved_dog_breed ] ) ): ?>
                                <option value="<?php echo esc_attr( $saved_dog_breed ); ?>" selected><?php echo esc_html( $saved_dog_breed ); ?> (korábban mentett)</option>
                            <?php endif; ?>
                            <?php foreach ( $dog_breed_options as $opt_value => $opt_label ): ?>
                                <option value="<?php echo esc_attr( $opt_value ); ?>"<?php selected( $saved_dog_breed, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Név</label>
                        <input type="text" name="dog_name" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_name'] ?? '' ) ); ?>" placeholder="pl. Rex">
                    </div>
                    <div class="va-form-group">
                        <label>Neme</label>
                        <select name="dog_gender" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="kan"<?php selected( (string)($edit_meta['dog_gender'] ?? ''), 'kan' ); ?>>Kan</option>
                            <option value="szuka"<?php selected( (string)($edit_meta['dog_gender'] ?? ''), 'szuka' ); ?>>Szuka</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Vérvonal</strong></div>
                    <div class="va-form-group"><label>Tenyésztő neve</label><input type="text" name="dog_breeder_name" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_breeder_name'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group"><label>Kennel neve</label><input type="text" name="dog_kennel_name" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_kennel_name'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group">
                        <label>FCI törzskönyv</label>
                        <?php $v = (string) ( $edit_meta['dog_pedigree_status'] ?? '' ); ?>
                        <select name="dog_pedigree_status" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option>
                            <option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option>
                            <option value="folyamatban"<?php selected( $v, 'folyamatban' ); ?>>Folyamatban</option>
                        </select>
                    </div>
                    <div class="va-form-group"><label>Származási ország</label><input type="text" name="dog_origin_country" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_origin_country'] ?? '' ) ); ?>" placeholder="pl. Magyarország"></div>
                    <div class="va-form-group">
                        <label>Vérvonal</label>
                        <?php $v = (string) ( $edit_meta['dog_bloodline_type'] ?? '' ); ?>
                        <select name="dog_bloodline_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="champion"<?php selected( $v, 'champion' ); ?>>Champion vérvonal</option>
                            <option value="munka"<?php selected( $v, 'munka' ); ?>>Munkavér</option>
                            <option value="vegyes"<?php selected( $v, 'vegyes' ); ?>>Vegyes vérvonal</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Okmányok</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Dokumentumok</label>
                        <select name="dog_documents[]" class="va-select" multiple data-placeholder="Válassz dokumentumot">
                            <option value="torzskonyv"<?php echo in_array( 'torzskonyv', $dog_documents_saved, true ) ? ' selected' : ''; ?>>Törzskönyv</option>
                            <option value="oltasi-konyv"<?php echo in_array( 'oltasi-konyv', $dog_documents_saved, true ) ? ' selected' : ''; ?>>Oltási könyv</option>
                            <option value="mikrochip"<?php echo in_array( 'mikrochip', $dog_documents_saved, true ) ? ' selected' : ''; ?>>Mikrochip</option>
                            <option value="munkavizsga-papir"<?php echo in_array( 'munkavizsga-papir', $dog_documents_saved, true ) ? ' selected' : ''; ?>>Munkavizsga papír</option>
                            <option value="tenyesztesi-engedely"<?php echo in_array( 'tenyesztesi-engedely', $dog_documents_saved, true ) ? ' selected' : ''; ?>>Tenyésztési engedély</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Alapadatok</strong></div>
                    <div class="va-form-group"><label>Születési dátum</label><div class="va-dark-date"><input type="hidden" name="dog_birth_date" id="va-dog-birth-date" class="va-date-value" value="<?php echo esc_attr( (string) ( $edit_meta['dog_birth_date'] ?? '' ) ); ?>"><input type="text" class="va-input va-date-display" placeholder="éééé. hh. nn." readonly><button type="button" class="va-date-btn" aria-label="Naptár megnyitása"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></button><div class="va-dark-calendar" style="display:none;"><div class="va-dark-calendar__head"><button type="button" class="va-dark-calendar__nav" data-dir="prev" aria-label="Előző hónap">‹</button><div class="va-dark-calendar__title"></div><button type="button" class="va-dark-calendar__nav" data-dir="next" aria-label="Következő hónap">›</button></div><div class="va-dark-calendar__dow"><span>H</span><span>K</span><span>Sze</span><span>Cs</span><span>P</span><span>Szo</span><span>V</span></div><div class="va-dark-calendar__grid"></div><div class="va-dark-calendar__foot"><button type="button" class="va-dark-calendar__clear">Törlés</button></div></div></div></div>
                    <div class="va-form-group"><label>Kor (hónap, automatikus)</label><input type="number" name="dog_age_months" id="va-dog-age-months" class="va-input" min="0" max="300" readonly value="<?php echo esc_attr((string)($edit_meta['dog_age_months'] ?? '')); ?>"></div>
                    <div class="va-form-group">
                        <label>Színe</label>
                        <select name="dog_color" class="va-select">
                            <option value="">– Válasszon színt –</option>
                            <?php if ( $saved_dog_color !== '' && ! isset( $dog_color_options[ $saved_dog_color ] ) ): ?>
                                <option value="<?php echo esc_attr( $saved_dog_color ); ?>" selected><?php echo esc_html( $saved_dog_color ); ?> (korábban mentett)</option>
                            <?php endif; ?>
                            <?php foreach ( $dog_color_options as $opt_value => $opt_label ): ?>
                                <option value="<?php echo esc_attr( $opt_value ); ?>"<?php selected( $saved_dog_color, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
                            <?php endforeach; ?>
                            <option value="vad-szin"<?php selected( $saved_dog_color, 'vad-szin' ); ?>>Vad szín</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Küllem</label>
                        <?php $v = (string) ( $edit_meta['dog_appearance_type'] ?? '' ); ?>
                        <select name="dog_appearance_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="egyszinu"<?php selected( $v, 'egyszinu' ); ?>>Egyszínű</option>
                            <option value="foltos"<?php selected( $v, 'foltos' ); ?>>Foltos</option>
                            <option value="tarka"<?php selected( $v, 'tarka' ); ?>>Tarka</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Fajtatisztaság</label>
                        <select name="dog_purebred" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php selected( (string)($edit_meta['dog_purebred'] ?? ''), 'igen' ); ?>>Igen</option>
                            <option value="nem"<?php selected( (string)($edit_meta['dog_purebred'] ?? ''), 'nem' ); ?>>Nem</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Képzettség</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Képzési elemek</label>
                        <select name="dog_training_skills[]" class="va-select" multiple data-placeholder="Válassz képzettséget">
                            <option value="alap-engedelmesseg"<?php echo in_array( 'alap-engedelmesseg', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Alap engedelmesség</option>
                            <option value="behivas-biztos"<?php echo in_array( 'behivas-biztos', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Behívás biztos</option>
                            <option value="poraz-nelkul"<?php echo in_array( 'poraz-nelkul', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Póráz nélkül</option>
                            <option value="apport"<?php echo in_array( 'apport', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Apport</option>
                            <option value="nyomkovetes"<?php echo in_array( 'nyomkovetes', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Nyomkövetés</option>
                            <option value="vadmegallas"<?php echo in_array( 'vadmegallas', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Vadmegállás</option>
                            <option value="vercsapa-munka"<?php echo in_array( 'vercsapa-munka', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Vércsapa munka</option>
                            <option value="vadjelzes"<?php echo in_array( 'vadjelzes', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Vadjelzés</option>
                            <option value="loveshez-szokott"<?php echo in_array( 'loveshez-szokott', $dog_training_skills_saved, true ) ? ' selected' : ''; ?>>Lövéshez szokott</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Kiképzés szintje</label>
                        <?php $v = (string) ( $edit_meta['dog_training_level'] ?? '' ); ?>
                        <select name="dog_training_level" id="va-dog-training-level" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="alap"<?php selected( $v, 'alap' ); ?>>Alap</option>
                            <option value="reszben-kepzett"<?php selected( $v, 'reszben-kepzett' ); ?>>Részben képzett</option>
                            <option value="kikepzett"<?php selected( $v, 'kikepzett' ); ?>>Kiképzett</option>
                            <option value="kikepzes-alatt"<?php selected( $v, 'kikepzes-alatt' ); ?>>Kiképzés alatt</option>
                            <option value="vadaszatra-kesz"<?php selected( $v, 'vadaszatra-kesz' ); ?>>Vadászatra kész</option>
                        </select>
                    </div>
                    <div class="va-form-group va-dog-dynamic-group" data-dog-trained="yes" style="display:none;">
                        <label>Vizsga szint</label>
                        <?php $v = (string) ( $edit_meta['dog_exam_level'] ?? '' ); ?>
                        <select name="dog_exam_level" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="alap"<?php selected( $v, 'alap' ); ?>>Alap</option>
                            <option value="kozep"<?php selected( $v, 'kozep' ); ?>>Közép</option>
                            <option value="halado"<?php selected( $v, 'halado' ); ?>>Haladó</option>
                            <option value="verseny"<?php selected( $v, 'verseny' ); ?>>Verseny</option>
                        </select>
                    </div>
                    <div class="va-form-group va-dog-dynamic-group" data-dog-trained="yes" style="display:none;">
                        <label>Munkavizsga típusa</label>
                        <?php $v = (string) ( $edit_meta['dog_work_exam_type'] ?? '' ); ?>
                        <select name="dog_work_exam_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="oszi-tenyeszvizsga"<?php selected( $v, 'oszi-tenyeszvizsga' ); ?>>Őszi tenyészvizsga</option>
                            <option value="mezei-vizsga"<?php selected( $v, 'mezei-vizsga' ); ?>>Mezei vizsga</option>
                            <option value="vereb-vizsga"<?php selected( $v, 'vereb-vizsga' ); ?>>Véreb vizsga</option>
                            <option value="vizi-vizsga"<?php selected( $v, 'vizi-vizsga' ); ?>>Vízi vizsga</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Vadászati specializáció</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Specializáció</label>
                        <select name="dog_hunting_specialization[]" class="va-select" multiple data-placeholder="Válassz specializációt">
                            <option value="vaddiszno"<?php echo in_array( 'vaddiszno', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Vaddisznó</option>
                            <option value="szarvas"<?php echo in_array( 'szarvas', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Szarvas</option>
                            <option value="oz"<?php echo in_array( 'oz', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Őz</option>
                            <option value="aprovad"<?php echo in_array( 'aprovad', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Apróvad</option>
                            <option value="vizivad"<?php echo in_array( 'vizivad', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Vízivad</option>
                            <option value="roka"<?php echo in_array( 'roka', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Róka</option>
                            <option value="ragadozo"<?php echo in_array( 'ragadozo', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Ragadozó</option>
                            <option value="vegyes"<?php echo in_array( 'vegyes', $dog_specialization_saved, true ) ? ' selected' : ''; ?>>Vegyes</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Munkastílus</label>
                        <?php $v = (string) ( $edit_meta['dog_work_style'] ?? '' ); ?>
                        <select name="dog_work_style" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="onallo"<?php selected( $v, 'onallo' ); ?>>Önálló</option>
                            <option value="vezetett"<?php selected( $v, 'vezetett' ); ?>>Vezetett</option>
                            <option value="hajto"<?php selected( $v, 'hajto' ); ?>>Hajtó</option>
                            <option value="csendes-kereso"<?php selected( $v, 'csendes-kereso' ); ?>>Csendes kereső</option>
                            <option value="intenziv-hajto"<?php selected( $v, 'intenziv-hajto' ); ?>>Intenzív hajtó</option>
                            <option value="kitarto-nyomkoveto"<?php selected( $v, 'kitarto-nyomkoveto' ); ?>>Kitartó nyomkövető</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Teljesítmény adatok</strong></div>
                    <div class="va-form-group"><label>Keresési sugár (m)</label><input type="text" name="dog_search_radius_m" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_search_radius_m'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group"><label>Állóképesség</label><?php $v = (string) ( $edit_meta['dog_endurance_level'] ?? '' ); ?><select name="dog_endurance_level" class="va-select"><option value="">– Válasszon –</option><option value="gyenge"<?php selected( $v, 'gyenge' ); ?>>Gyenge</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="eros"<?php selected( $v, 'eros' ); ?>>Erős</option><option value="kiemelkedo"<?php selected( $v, 'kiemelkedo' ); ?>>Kiemelkedő</option></select></div>
                    <div class="va-form-group"><label>Agresszió szint vad felé</label><?php $v = (string) ( $edit_meta['dog_aggression_level'] ?? '' ); ?><select name="dog_aggression_level" class="va-select"><option value="">– Válasszon –</option><option value="alacsony"<?php selected( $v, 'alacsony' ); ?>>Alacsony</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="magas"<?php selected( $v, 'magas' ); ?>>Magas</option></select></div>
                    <div class="va-form-group"><label>Apport stabilitás</label><?php $v = (string) ( $edit_meta['dog_apport_stability'] ?? '' ); ?><select name="dog_apport_stability" class="va-select"><option value="">– Válasszon –</option><option value="gyenge"<?php selected( $v, 'gyenge' ); ?>>Gyenge</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="eros"<?php selected( $v, 'eros' ); ?>>Erős</option></select></div>
                    <div class="va-form-group"><label>Vízmunka képesség</label><?php $v = (string) ( $edit_meta['dog_water_work_ability'] ?? '' ); ?><select name="dog_water_work_ability" class="va-select"><option value="">– Válasszon –</option><option value="gyenge"<?php selected( $v, 'gyenge' ); ?>>Gyenge</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="eros"<?php selected( $v, 'eros' ); ?>>Erős</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Vízmunka</strong></div>
                    <div class="va-form-group"><label>Vízmunkára alkalmas</label><?php $v = (string) ( $edit_meta['dog_water_work'] ?? '' ); ?><select name="dog_water_work" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Vízmunka szint</label><?php $v = (string) ( $edit_meta['dog_water_work_level'] ?? '' ); ?><select name="dog_water_work_level" class="va-select"><option value="">– Válasszon –</option><option value="eros"<?php selected( $v, 'eros' ); ?>>Erős</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="gyenge"<?php selected( $v, 'gyenge' ); ?>>Gyenge</option></select></div>
                    <div class="va-form-group"><label>Hideg víz tűrés</label><?php $v = (string) ( $edit_meta['dog_cold_water_tolerance'] ?? '' ); ?><select name="dog_cold_water_tolerance" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="korlatozott"<?php selected( $v, 'korlatozott' ); ?>>Korlátozott</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Temperamentum</label><?php $v = (string) ( $edit_meta['dog_temperament'] ?? '' ); ?><select name="dog_temperament" class="va-select"><option value="">– Válasszon –</option><option value="nyugodt"<?php selected( $v, 'nyugodt' ); ?>>Nyugodt</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="aktiv"<?php selected( $v, 'aktiv' ); ?>>Aktív</option><option value="nagyon-aktiv"<?php selected( $v, 'nagyon-aktiv' ); ?>>Nagyon aktív</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Környezet</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Tarthatóság</label>
                        <select name="dog_environment[]" class="va-select" multiple data-placeholder="Válassz környezetet">
                            <option value="lakasban-tarthato"<?php echo in_array( 'lakasban-tarthato', $dog_environment_saved, true ) ? ' selected' : ''; ?>>Lakásban tartható</option>
                            <option value="udvar-szukseges"<?php echo in_array( 'udvar-szukseges', $dog_environment_saved, true ) ? ' selected' : ''; ?>>Udvar szükséges</option>
                            <option value="kennel-tartas"<?php echo in_array( 'kennel-tartas', $dog_environment_saved, true ) ? ' selected' : ''; ?>>Kennel tartás</option>
                        </select>
                    </div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Táplálás / egészség</strong></div>
                    <div class="va-form-group"><label>Allergiás-e</label><?php $v = (string) ( $edit_meta['dog_allergic'] ?? '' ); ?><select name="dog_allergic" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Speciális étrend</label><input type="text" name="dog_special_diet" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_special_diet'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Ismert betegségek</label><textarea name="dog_known_diseases" class="va-input" rows="2"><?php echo esc_textarea( (string) ( $edit_meta['dog_known_diseases'] ?? '' ) ); ?></textarea></div>
                    <div class="va-form-group"><label>Csípő diszplázia szűrés</label><?php $v = (string) ( $edit_meta['dog_hip_dysplasia_screening'] ?? '' ); ?><select name="dog_hip_dysplasia_screening" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option><option value="folyamatban"<?php selected( $v, 'folyamatban' ); ?>>Folyamatban</option></select></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Méretek</strong></div>
                    <div class="va-form-group"><label>Marmagasság (cm)</label><input type="text" name="dog_height_cm" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_height_cm'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group"><label>Súly (kg)</label><input type="text" name="dog_weight_kg" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_weight_kg'] ?? '' ) ); ?>"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Helyszín</strong></div>
                    <div class="va-form-group"><label>Ország</label><input type="text" name="dog_country" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_country'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group"><label>Megye</label><input type="text" name="dog_county" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_county'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group"><label>Település</label><input type="text" name="dog_city" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_city'] ?? '' ) ); ?>"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Származás minősítés</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Minősítés</label>
                        <select name="dog_origin_qualification[]" class="va-select" multiple data-placeholder="Válassz minősítést">
                            <option value="tenyesztesre-alkalmas"<?php echo in_array( 'tenyesztesre-alkalmas', $dog_origin_qualification_saved, true ) ? ' selected' : ''; ?>>Tenyésztésre alkalmas</option>
                            <option value="munkara-alkalmas"<?php echo in_array( 'munkara-alkalmas', $dog_origin_qualification_saved, true ) ? ' selected' : ''; ?>>Munkára alkalmas</option>
                            <option value="hobbi"<?php echo in_array( 'hobbi', $dog_origin_qualification_saved, true ) ? ' selected' : ''; ?>>Hobbi</option>
                            <option value="kiallitasi-minoseg"<?php echo in_array( 'kiallitasi-minoseg', $dog_origin_qualification_saved, true ) ? ' selected' : ''; ?>>Kiállítási minőség</option>
                        </select>
                    </div>

                    <div class="va-form-group va-dog-dynamic-group" data-dog-puppy="yes" style="grid-column:1 / -1; display:none;"><strong>Kölyök adatok</strong></div>
                    <div class="va-form-group va-dog-dynamic-group" data-dog-puppy="yes" style="display:none;"><label>Alom száma</label><input type="text" name="dog_litter_count" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_litter_count'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group va-dog-dynamic-group" data-dog-puppy="yes" style="display:none;"><label>Elvihetőség dátuma</label><div class="va-dark-date"><input type="hidden" name="dog_available_from" class="va-date-value" value="<?php echo esc_attr( (string) ( $edit_meta['dog_available_from'] ?? '' ) ); ?>"><input type="text" class="va-input va-date-display" placeholder="éééé. hh. nn." readonly><button type="button" class="va-date-btn" aria-label="Naptár megnyitása"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></button><div class="va-dark-calendar" style="display:none;"><div class="va-dark-calendar__head"><button type="button" class="va-dark-calendar__nav" data-dir="prev" aria-label="Előző hónap">‹</button><div class="va-dark-calendar__title"></div><button type="button" class="va-dark-calendar__nav" data-dir="next" aria-label="Következő hónap">›</button></div><div class="va-dark-calendar__dow"><span>H</span><span>K</span><span>Sze</span><span>Cs</span><span>P</span><span>Szo</span><span>V</span></div><div class="va-dark-calendar__grid"></div><div class="va-dark-calendar__foot"><button type="button" class="va-dark-calendar__clear">Törlés</button></div></div></div></div>

                    <div class="va-form-group va-dog-dynamic-group" data-dog-working-types="vereb" style="grid-column:1 / -1; display:none;"><strong>Véreb specifikus</strong></div>
                    <div class="va-form-group va-dog-dynamic-group" data-dog-working-types="vereb" style="display:none;"><label>Nyomkövetési táv (m)</label><input type="text" name="dog_tracking_distance_m" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_tracking_distance_m'] ?? '' ) ); ?>"></div>
                    <div class="va-form-group va-dog-dynamic-group" data-dog-working-types="vereb" style="display:none;"><label>Tapasztalat</label><input type="text" name="dog_tracking_experience" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_tracking_experience'] ?? '' ) ); ?>" placeholder="pl. 3 szezon, 25 sikeres utánkeresés"></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Munkateszt videó</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Videó típus</label>
                        <select name="dog_work_video_types[]" class="va-select" multiple data-placeholder="Válassz videótípust">
                            <option value="nyomkovetes"<?php echo in_array( 'nyomkovetes', $dog_work_video_saved, true ) ? ' selected' : ''; ?>>Nyomkövetés</option>
                            <option value="apport"<?php echo in_array( 'apport', $dog_work_video_saved, true ) ? ' selected' : ''; ?>>Apport</option>
                            <option value="loves-reakcio"<?php echo in_array( 'loves-reakcio', $dog_work_video_saved, true ) ? ' selected' : ''; ?>>Lövés reakció</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Videó URL</label><input type="url" name="dog_work_video_url" class="va-input" value="<?php echo esc_attr( (string) ( $edit_meta['dog_work_video_url'] ?? '' ) ); ?>" placeholder="https://..."></div>

                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Moderáció</strong></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Moderációs flag</label>
                        <select name="dog_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázati jelzők">
                            <option value="hamis-torzskonyv"<?php echo in_array( 'hamis-torzskonyv', $dog_moderation_saved, true ) ? ' selected' : ''; ?>>Hamis törzskönyv</option>
                            <option value="illegalis-tenyesztes"<?php echo in_array( 'illegalis-tenyesztes', $dog_moderation_saved, true ) ? ' selected' : ''; ?>>Illegális tenyésztés</option>
                            <option value="egeszsegugyi-adat-hamisitas"<?php echo in_array( 'egeszsegugyi-adat-hamisitas', $dog_moderation_saved, true ) ? ' selected' : ''; ?>>Egészségügyi adat hamisítás</option>
                        </select>
                    </div>
                </div><!-- /va-step2-4col-inner -->
            </div>
            <?php
                $job_location_val = (string)($edit_meta['job_location'] ?? '');
                $job_country_val = (string)($edit_meta['job_country'] ?? '');
                $job_role_val = (string)($edit_meta['job_role'] ?? '');
                $job_county_val = (int)($edit_meta['job_county'] ?? 0);
                $job_drivers_license_saved = array_filter(array_map('trim', explode(',', (string)($edit_meta['job_drivers_license'] ?? ''))));
            ?>
            <div class="va-cat-rule-field" data-categories="allas,allas-hirdetes,munka,munkak,munkalehetoseg,munkalehetosegek,szolgaltatas">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Munkakör</label>
                        <select name="job_role" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="hivatasos-vadasz"<?php echo selected( $job_role_val, 'hivatasos-vadasz', false ); ?>>Hivatásos vadász</option>
                            <option value="vador"<?php echo selected( $job_role_val, 'vador', false ); ?>>Vadőr</option>
                            <option value="vadgazdalkodasi-technikus"<?php echo selected( $job_role_val, 'vadgazdalkodasi-technikus', false ); ?>>Vadászgazdálkodási technikus</option>
                            <option value="trofeabiralo"<?php echo selected( $job_role_val, 'trofeabiralo', false ); ?>>Trófeabíráló</option>
                            <option value="vadaszhaz-gondnok"<?php echo selected( $job_role_val, 'vadaszhaz-gondnok', false ); ?>>Vadászház gondnok</option>
                            <option value="fegyvermester"<?php echo selected( $job_role_val, 'fegyvermester', false ); ?>>Fegyvermester</option>
                            <option value="sofor"<?php echo selected( $job_role_val, 'sofor', false ); ?>>Sofőr</option>
                            <option value="kutyavezeto"<?php echo selected( $job_role_val, 'kutyavezeto', false ); ?>>Kutyavezető</option>
                            <option value="ideny-munkas"<?php echo selected( $job_role_val, 'ideny-munkas', false ); ?>>Idénymunkás</option>
                            <option value="egyeb"<?php echo selected( $job_role_val, 'egyeb', false ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Foglalkoztatás típusa</label>
                        <select name="job_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="teljes-munkaido"<?php echo selected( (string)($edit_meta['job_type'] ?? ''), 'teljes-munkaido', false ); ?>>Teljes munkaidő</option>
                            <option value="reszmunkaido"<?php echo selected( (string)($edit_meta['job_type'] ?? ''), 'reszmunkaido', false ); ?>>Részmunkaidő</option>
                            <option value="szezonalis"<?php echo selected( (string)($edit_meta['job_type'] ?? ''), 'szezonalis', false ); ?>>Szezonális</option>
                            <option value="alkalmi"<?php echo selected( (string)($edit_meta['job_type'] ?? ''), 'alkalmi', false ); ?>>Alkalmi</option>
                            <option value="vallalkozoi"<?php echo selected( (string)($edit_meta['job_type'] ?? ''), 'vallalkozoi', false ); ?>>Vállalkozói</option>
                            <option value="gyakornoki"<?php echo selected( (string)($edit_meta['job_type'] ?? ''), 'gyakornoki', false ); ?>>Gyakornoki</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Település</label>
                        <input type="text" name="job_location" class="va-input" placeholder="pl. Kaposvár" value="<?php echo esc_attr($job_location_val); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Megye</label>
                        <select name="job_county" class="va-select">
                            <option value="">– Válasszon –</option>
                            <?php foreach ( $counties as $county ): ?>
                                <option value="<?php echo esc_attr( $county->term_id ); ?>"<?php echo selected( $job_county_val, (int) $county->term_id, false ); ?>><?php echo esc_html( $county->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Ország</label>
                        <input type="text" name="job_country" class="va-input" placeholder="pl. Magyarország, Ausztria" value="<?php echo esc_attr($job_country_val); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Munkakör pontosítása (opcionális)</label>
                        <input type="text" name="job_role_other" class="va-input" placeholder="pl. vendégvadásztatás, erdészeti asszisztens" value="<?php echo esc_attr((string)($edit_meta['job_role_other'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Szakmai adatok</strong></div>
                    <div class="va-form-group">
                        <label>Szükséges végzettség</label>
                        <select name="job_education" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="nincs"<?php echo selected( (string)($edit_meta['job_education'] ?? ''), 'nincs', false ); ?>>Nincs</option>
                            <option value="vadgazda"<?php echo selected( (string)($edit_meta['job_education'] ?? ''), 'vadgazda', false ); ?>>Vadgazda</option>
                            <option value="erdesz"<?php echo selected( (string)($edit_meta['job_education'] ?? ''), 'erdesz', false ); ?>>Erdész</option>
                            <option value="technikus"<?php echo selected( (string)($edit_meta['job_education'] ?? ''), 'technikus', false ); ?>>Technikus</option>
                            <option value="felsofoku"<?php echo selected( (string)($edit_meta['job_education'] ?? ''), 'felsofoku', false ); ?>>Felsőfokú</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Vadászjegy szükséges?</label>
                        <select name="job_hunting_license" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_hunting_license'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_hunting_license'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Fegyvertartási engedély szükséges?</label>
                        <select name="job_firearm_license" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_firearm_license'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_firearm_license'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Jogosítvány</label>
                        <select name="job_drivers_license[]" class="va-select" multiple data-placeholder="Válassz jogosítványt">
                            <option value="b"<?php echo in_array('b', $job_drivers_license_saved, true) ? ' selected' : ''; ?>>B</option>
                            <option value="be"<?php echo in_array('be', $job_drivers_license_saved, true) ? ' selected' : ''; ?>>BE</option>
                            <option value="c"<?php echo in_array('c', $job_drivers_license_saved, true) ? ' selected' : ''; ?>>C</option>
                            <option value="t"<?php echo in_array('t', $job_drivers_license_saved, true) ? ' selected' : ''; ?>>T</option>
                            <option value="egyeb"<?php echo in_array('egyeb', $job_drivers_license_saved, true) ? ' selected' : ''; ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Tapasztalat</label>
                        <select name="job_experience" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="palyakezdo"<?php echo selected( (string)($edit_meta['job_experience'] ?? ''), 'palyakezdo', false ); ?>>Pályakezdő</option>
                            <option value="1-3ev"<?php echo selected( (string)($edit_meta['job_experience'] ?? ''), '1-3ev', false ); ?>>1–3 év</option>
                            <option value="3-5ev"<?php echo selected( (string)($edit_meta['job_experience'] ?? ''), '3-5ev', false ); ?>>3–5 év</option>
                            <option value="5plusz"<?php echo selected( (string)($edit_meta['job_experience'] ?? ''), '5plusz', false ); ?>>5+ év</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Nyelvtudás</label>
                        <input type="text" name="job_language" class="va-input" placeholder="pl. angol, német" value="<?php echo esc_attr((string)($edit_meta['job_language'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Kutyás tapasztalat</label>
                        <select name="job_dog_experience" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_dog_experience'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_dog_experience'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Trófeabírálati tapasztalat</label>
                        <select name="job_trophy_experience" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_trophy_experience'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_trophy_experience'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Munkaköri feladatok</label>
                        <textarea name="job_tasks" class="va-input" rows="4" placeholder="Részletes feladatleírás..."><?php echo esc_textarea((string)($edit_meta['job_tasks'] ?? '')); ?></textarea>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Munka részletei</strong></div>
                    <div class="va-form-group">
                        <label>Munkaidő</label>
                        <select name="job_work_time" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="nappal"<?php echo selected( (string)($edit_meta['job_work_time'] ?? ''), 'nappal', false ); ?>>Nappal</option>
                            <option value="ejszaka"<?php echo selected( (string)($edit_meta['job_work_time'] ?? ''), 'ejszaka', false ); ?>>Éjszaka</option>
                            <option value="valtott"<?php echo selected( (string)($edit_meta['job_work_time'] ?? ''), 'valtott', false ); ?>>Váltott</option>
                            <option value="rugalmas"<?php echo selected( (string)($edit_meta['job_work_time'] ?? ''), 'rugalmas', false ); ?>>Rugalmas</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Szállás biztosított?</label>
                        <select name="job_accommodation" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_accommodation'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_accommodation'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Étkezés biztosított?</label>
                        <select name="job_meals" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_meals'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_meals'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Szolgálati jármű</label>
                        <select name="job_company_vehicle" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_company_vehicle'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_company_vehicle'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Szolgálati fegyver</label>
                        <select name="job_company_weapon" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php echo selected( (string)($edit_meta['job_company_weapon'] ?? ''), 'igen', false ); ?>>Igen</option>
                            <option value="nem"<?php echo selected( (string)($edit_meta['job_company_weapon'] ?? ''), 'nem', false ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Fizetés</strong></div>
                    <div class="va-form-group">
                        <label>Fizetés típusa</label>
                        <select name="job_salary_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="fix"<?php echo selected( (string)($edit_meta['job_salary_type'] ?? ''), 'fix', false ); ?>>Fix</option>
                            <option value="oraber"<?php echo selected( (string)($edit_meta['job_salary_type'] ?? ''), 'oraber', false ); ?>>Órabér</option>
                            <option value="jutalekos"<?php echo selected( (string)($edit_meta['job_salary_type'] ?? ''), 'jutalekos', false ); ?>>Jutalékos</option>
                            <option value="megegyezes-szerint"<?php echo selected( (string)($edit_meta['job_salary_type'] ?? ''), 'megegyezes-szerint', false ); ?>>Megállapodás szerint</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Fizetési sáv minimum</label>
                        <input type="text" name="job_salary_min" class="va-input" placeholder="pl. 350000" value="<?php echo esc_attr((string)($edit_meta['job_salary_min'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Fizetési sáv maximum</label>
                        <input type="text" name="job_salary_max" class="va-input" placeholder="pl. 600000" value="<?php echo esc_attr((string)($edit_meta['job_salary_max'] ?? '')); ?>">
                    </div>
                    <div class="va-form-group">
                        <label>Pénznem</label>
                        <select name="job_salary_currency" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="HUF"<?php echo selected( (string)($edit_meta['job_salary_currency'] ?? ''), 'HUF', false ); ?>>HUF</option>
                            <option value="EUR"<?php echo selected( (string)($edit_meta['job_salary_currency'] ?? ''), 'EUR', false ); ?>>EUR</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php
            $decor_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $decor_csv = static function( string $key ) use ( $decor_meta ): array {
                return array_filter( array_map( 'trim', explode( ',', (string) $decor_meta( $key ) ) ) );
            };
            $decor_motif_saved = $decor_csv( 'decor_motif_tags' );
            $decor_special_saved = $decor_csv( 'decor_special_features' );
            ?>
            <div class="va-cat-rule-field va-decor-fields-grid" data-categories="disztargyak,disztargy" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group"><label>Dísztárgy típusa</label><?php $v = $decor_meta( 'decor_type' ); ?><select name="decor_type" id="va-decor-type" class="va-select"><option value="">– Válasszon –</option><option value="vadaszati-faldisz"<?php selected( $v, 'vadaszati-faldisz' ); ?>>Vadászati falidísz</option><option value="trofea-dekoracio"<?php selected( $v, 'trofea-dekoracio' ); ?>>Trófea dekoráció</option><option value="fegyver-replika-disz"<?php selected( $v, 'fegyver-replika-disz' ); ?>>Fegyver replika (dísz)</option><option value="kes-tor-disztargy"<?php selected( $v, 'kes-tor-disztargy' ); ?>>Kés / tőr dísztárgy</option><option value="allat-figura"<?php selected( $v, 'allat-figura' ); ?>>Állat figura</option><option value="bronz-fem-szobor"<?php selected( $v, 'bronz-fem-szobor' ); ?>>Bronz / fém szobor</option><option value="fa-faragas"<?php selected( $v, 'fa-faragas' ); ?>>Fa faragás</option><option value="pajzs-cimer"<?php selected( $v, 'pajzs-cimer' ); ?>>Pajzs / címer</option><option value="vadasz-jelenet-relief"<?php selected( $v, 'vadasz-jelenet-relief' ); ?>>Vadász jelenet relief</option><option value="asztali-disz"<?php selected( $v, 'asztali-disz' ); ?>>Asztali dísz</option><option value="falikep-relief"<?php selected( $v, 'falikep-relief' ); ?>>Falikép / relief</option><option value="vintage-retro-dekor"<?php selected( $v, 'vintage-retro-dekor' ); ?>>Vintage / retro dekor</option><option value="antik-jellegu-disztargy"<?php selected( $v, 'antik-jellegu-disztargy' ); ?>>Antik jellegű dísztárgy</option><option value="modern-dekor"<?php selected( $v, 'modern-dekor' ); ?>>Modern dekor</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group"><label>Anyag</label><?php $v = $decor_meta( 'decor_material' ); ?><select name="decor_material" id="va-decor-material" class="va-select"><option value="">– Válasszon –</option><option value="fa"<?php selected( $v, 'fa' ); ?>>Fa</option><option value="tolgy"<?php selected( $v, 'tolgy' ); ?>>Tölgy</option><option value="bukk"<?php selected( $v, 'bukk' ); ?>>Bükk</option><option value="dio"<?php selected( $v, 'dio' ); ?>>Dió</option><option value="fem"<?php selected( $v, 'fem' ); ?>>Fém</option><option value="bronz-hatasu"<?php selected( $v, 'bronz-hatasu' ); ?>>Bronz hatású</option><option value="ontott-fem"<?php selected( $v, 'ontott-fem' ); ?>>Öntött fém (spiáter / ötvözet)</option><option value="keramia"<?php selected( $v, 'keramia' ); ?>>Kerámia</option><option value="uveg"<?php selected( $v, 'uveg' ); ?>>Üveg</option><option value="mugyanta"<?php selected( $v, 'mugyanta' ); ?>>Műgyanta</option><option value="pvc"<?php selected( $v, 'pvc' ); ?>>PVC</option><option value="kombinalt"<?php selected( $v, 'kombinalt' ); ?>>Kombinált</option></select></div>
                    <div class="va-form-group va-decor-dynamic-group" data-decor-material="fa" style="display:none;"><label>Fafaj</label><input type="text" name="decor_wood_type" class="va-input" value="<?php echo esc_attr( $decor_meta( 'decor_wood_type' ) ); ?>" placeholder="pl. tölgy"></div>
                    <div class="va-form-group va-decor-dynamic-group" data-decor-material="fem" style="display:none;"><label>Ötvözet típusa</label><input type="text" name="decor_metal_alloy" class="va-input" value="<?php echo esc_attr( $decor_meta( 'decor_metal_alloy' ) ); ?>" placeholder="pl. bronz-cink"></div>

                    <div class="va-form-group"><label>Stílus</label><?php $v = $decor_meta( 'decor_style' ); ?><select name="decor_style" class="va-select"><option value="">– Válasszon –</option><option value="vadaszati"<?php selected( $v, 'vadaszati' ); ?>>Vadászati</option><option value="klasszikus"<?php selected( $v, 'klasszikus' ); ?>>Klasszikus</option><option value="rusztikus"<?php selected( $v, 'rusztikus' ); ?>>Rusztikus</option><option value="antik"<?php selected( $v, 'antik' ); ?>>Antik</option><option value="modern"<?php selected( $v, 'modern' ); ?>>Modern</option><option value="minimalista"<?php selected( $v, 'minimalista' ); ?>>Minimalista</option><option value="katonai-jellegu"<?php selected( $v, 'katonai-jellegu' ); ?>>Katonai jellegű</option><option value="fantasy-replika"<?php selected( $v, 'fantasy-replika' ); ?>>Fantasy / replika</option><option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option><option value="ipari"<?php selected( $v, 'ipari' ); ?>>Ipari</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Motívum</label><select name="decor_motif_tags[]" class="va-select" multiple data-placeholder="Válassz motívumot"><option value="vadasz-jelenet"<?php echo in_array( 'vadasz-jelenet', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Vadász jelenet</option><option value="trofea"<?php echo in_array( 'trofea', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Trófea</option><option value="vadallatok"<?php echo in_array( 'vadallatok', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Vadállatok</option><option value="fegyver"<?php echo in_array( 'fegyver', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Fegyver</option><option value="erdo-termeszet"<?php echo in_array( 'erdo-termeszet', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Erdő / természet</option><option value="koponya"<?php echo in_array( 'koponya', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Koponya</option><option value="agancs"<?php echo in_array( 'agancs', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Agancs</option><option value="vadaszkutya"<?php echo in_array( 'vadaszkutya', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Vadászkutya</option><option value="madarvadaszat"<?php echo in_array( 'madarvadaszat', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Madárvadászat</option><option value="tortenelmi"<?php echo in_array( 'tortenelmi', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Történelmi</option><option value="cimer-emblema"<?php echo in_array( 'cimer-emblema', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Címer / embléma</option><option value="fantasy"<?php echo in_array( 'fantasy', $decor_motif_saved, true ) ? ' selected' : ''; ?>>Fantasy</option></select></div>

                    <div class="va-form-group"><label>Kivitel</label><?php $v = $decor_meta( 'decor_finish' ); ?><select name="decor_finish" class="va-select"><option value="">– Válasszon –</option><option value="falra-akaszthato"<?php selected( $v, 'falra-akaszthato' ); ?>>Falra akasztható</option><option value="asztali"<?php selected( $v, 'asztali' ); ?>>Asztali</option><option value="allo"<?php selected( $v, 'allo' ); ?>>Álló</option><option value="dombormu-relief"<?php selected( $v, 'dombormu-relief' ); ?>>Dombormű (relief)</option><option value="szobor"<?php selected( $v, 'szobor' ); ?>>Szobor</option><option value="plakett"<?php selected( $v, 'plakett' ); ?>>Plakett</option><option value="panel"<?php selected( $v, 'panel' ); ?>>Panel</option><option value="miniatur"<?php selected( $v, 'miniatur' ); ?>>Miniatűr</option></select></div>
                    <div class="va-form-group"><label>Részletesség</label><?php $v = $decor_meta( 'decor_detailing' ); ?><select name="decor_detailing" class="va-select"><option value="">– Válasszon –</option><option value="egyszeru"<?php selected( $v, 'egyszeru' ); ?>>Egyszerű</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="reszletgazdag"<?php selected( $v, 'reszletgazdag' ); ?>>Részletgazdag</option><option value="muveszi"<?php selected( $v, 'muveszi' ); ?>>Művészi</option><option value="premium-kidolgozas"<?php selected( $v, 'premium-kidolgozas' ); ?>>Prémium kidolgozás</option></select></div>
                    <div class="va-form-group"><label>Kor / korszak</label><?php $v = $decor_meta( 'decor_era' ); ?><select name="decor_era" class="va-select"><option value="">– Válasszon –</option><option value="modern"<?php selected( $v, 'modern' ); ?>>Modern</option><option value="vintage"<?php selected( $v, 'vintage' ); ?>>Vintage</option><option value="retro"<?php selected( $v, 'retro' ); ?>>Retro</option><option value="antik-hatasu"<?php selected( $v, 'antik-hatasu' ); ?>>Antik hatású</option><option value="kortars"<?php selected( $v, 'kortars' ); ?>>Kortárs</option><option value="tortenelmi-ihletesu"<?php selected( $v, 'tortenelmi-ihletesu' ); ?>>Történelmi ihletésű</option></select></div>
                    <div class="va-form-group"><label>Készítés</label><?php $v = $decor_meta( 'decor_production_type' ); ?><select name="decor_production_type" class="va-select"><option value="">– Válasszon –</option><option value="kezzel-keszult"<?php selected( $v, 'kezzel-keszult' ); ?>>Kézzel készült</option><option value="gepi-gyartas"<?php selected( $v, 'gepi-gyartas' ); ?>>Gépi gyártás</option><option value="ontott"<?php selected( $v, 'ontott' ); ?>>Öntött</option><option value="faragott"<?php selected( $v, 'faragott' ); ?>>Faragott</option><option value="gravirozott"<?php selected( $v, 'gravirozott' ); ?>>Gravírozott</option><option value="nyomtatott"<?php selected( $v, 'nyomtatott' ); ?>>Nyomtatott</option><option value="kombinalt-technika"<?php selected( $v, 'kombinalt-technika' ); ?>>Kombinált technika</option></select></div>

                    <div class="va-form-group"><label>Funkció</label><?php $v = $decor_meta( 'decor_function' ); ?><select name="decor_function" class="va-select"><option value="">– Válasszon –</option><option value="dekoracio"<?php selected( $v, 'dekoracio' ); ?>>Dekoráció</option><option value="gyujtoi-darab"<?php selected( $v, 'gyujtoi-darab' ); ?>>Gyűjtői darab</option><option value="ajandek"<?php selected( $v, 'ajandek' ); ?>>Ajándék</option><option value="vadaszhaz-dekor"<?php selected( $v, 'vadaszhaz-dekor' ); ?>>Vadászház dekor</option><option value="tematikus-belso-ter"<?php selected( $v, 'tematikus-belso-ter' ); ?>>Tematikus belső tér</option></select></div>
                    <div class="va-form-group"><label>Tematikus csoport</label><?php $v = $decor_meta( 'decor_thematic_group' ); ?><select name="decor_thematic_group" class="va-select"><option value="">– Válasszon –</option><option value="vadaszat"<?php selected( $v, 'vadaszat' ); ?>>Vadászat</option><option value="katonai"<?php selected( $v, 'katonai' ); ?>>Katonai</option><option value="termeszet"<?php selected( $v, 'termeszet' ); ?>>Természet</option><option value="fantasy"<?php selected( $v, 'fantasy' ); ?>>Fantasy</option><option value="tortenelmi"<?php selected( $v, 'tortenelmi' ); ?>>Történelmi</option><option value="pop-culture-replika"<?php selected( $v, 'pop-culture-replika' ); ?>>Pop culture replika</option><option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Különlegesség</label><select name="decor_special_features[]" class="va-select" multiple data-placeholder="Válassz különlegességet"><option value="limitalt-kiadas"<?php echo in_array( 'limitalt-kiadas', $decor_special_saved, true ) ? ' selected' : ''; ?>>Limitált kiadás</option><option value="ritka-darab"<?php echo in_array( 'ritka-darab', $decor_special_saved, true ) ? ' selected' : ''; ?>>Ritka darab</option><option value="kezmuves"<?php echo in_array( 'kezmuves', $decor_special_saved, true ) ? ' selected' : ''; ?>>Kézműves</option><option value="egyedi-keszites"<?php echo in_array( 'egyedi-keszites', $decor_special_saved, true ) ? ' selected' : ''; ?>>Egyedi készítés</option><option value="sorozat-resze"<?php echo in_array( 'sorozat-resze', $decor_special_saved, true ) ? ' selected' : ''; ?>>Sorozat része</option></select></div>
                    <div class="va-form-group"><label>Felakasztható / rögzítés</label><?php $v = $decor_meta( 'decor_mounting' ); ?><select name="decor_mounting" class="va-select"><option value="">– Válasszon –</option><option value="akaszto-van"<?php selected( $v, 'akaszto-van' ); ?>>Akasztó van</option><option value="nincs-rogzites"<?php selected( $v, 'nincs-rogzites' ); ?>>Nincs rögzítés</option><option value="falra-szerelheto"<?php selected( $v, 'falra-szerelheto' ); ?>>Falra szerelhető</option><option value="allithato"<?php selected( $v, 'allithato' ); ?>>Állítható</option></select></div>
                    <div class="va-form-group"><label>Méret kategória</label><?php $v = $decor_meta( 'decor_size_class' ); ?><select name="decor_size_class" class="va-select"><option value="">– Válasszon –</option><option value="kicsi"<?php selected( $v, 'kicsi' ); ?>>Kicsi</option><option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option><option value="nagy"<?php selected( $v, 'nagy' ); ?>>Nagy</option><option value="extra-nagy"<?php selected( $v, 'extra-nagy' ); ?>>Extra nagy</option></select></div>
                    <div class="va-form-group"><label>Állapot</label><?php $v = $decor_meta( 'decor_condition' ); ?><select name="decor_condition" class="va-select"><option value="">– Válasszon –</option><option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option><option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option><option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option><option value="antik"<?php selected( $v, 'antik' ); ?>>Antik</option><option value="restauralt"<?php selected( $v, 'restauralt' ); ?>>Restaurált</option></select></div>
                    <div class="va-form-group"><label>Gyűjtői jelölés</label><?php $v = $decor_meta( 'decor_collectible_flag' ); ?><select name="decor_collectible_flag" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>

                    <div class="va-form-group va-decor-dynamic-group" data-decor-type="fegyver-replika-disz" style="display:none;"><label>Replika típusa</label><input type="text" name="decor_replica_type" class="va-input" value="<?php echo esc_attr( $decor_meta( 'decor_replica_type' ) ); ?>" placeholder="pl. vadászpuskás replika"></div>
                    <div class="va-form-group va-decor-dynamic-group" data-decor-type="fegyver-replika-disz" style="display:none;"><label>Hitelesség szint</label><?php $v = $decor_meta( 'decor_authenticity_level' ); ?><select name="decor_authenticity_level" class="va-select"><option value="">– Válasszon –</option><option value="alap"<?php selected( $v, 'alap' ); ?>>Alap</option><option value="reszletes"<?php selected( $v, 'reszletes' ); ?>>Részletes</option><option value="muzeumi"<?php selected( $v, 'muzeumi' ); ?>>Múzeumi</option></select></div>
                </div>
            </div>

            <?php
            $publication_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $publication_csv = static function( string $key ) use ( $publication_meta ): array {
                return array_filter( array_map( 'trim', explode( ',', (string) $publication_meta( $key ) ) ) );
            };
            $publication_topics_saved = $publication_csv( 'publication_topics' );
            $publication_defects_saved = $publication_csv( 'publication_defects' );
            $publication_content_saved = $publication_csv( 'publication_content_flags' );
            $publication_collector_flags_saved = $publication_csv( 'publication_collector_flags' );
            $publication_usage_saved = $publication_csv( 'publication_usage' );
            $publication_moderation_saved = $publication_csv( 'publication_moderation_flags' );
            ?>
            <div class="va-cat-rule-field va-publication-fields-grid" data-categories="konyv-folyoirat" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Típus</label>
                        <?php $v = $publication_meta( 'publication_type' ); ?>
                        <select name="publication_type" id="va-publication-type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="konyv"<?php selected( $v, 'konyv' ); ?>>Könyv</option>
                            <option value="folyoirat"<?php selected( $v, 'folyoirat' ); ?>>Folyóirat</option>
                            <option value="magazin"<?php selected( $v, 'magazin' ); ?>>Magazin</option>
                            <option value="evkonyv"<?php selected( $v, 'evkonyv' ); ?>>Évkönyv</option>
                            <option value="katalogus"<?php selected( $v, 'katalogus' ); ?>>Katalógus</option>
                            <option value="kezikonyv"<?php selected( $v, 'kezikonyv' ); ?>>Kézikönyv</option>
                            <option value="atlasz"<?php selected( $v, 'atlasz' ); ?>>Atlasz</option>
                            <option value="oktatoanyag"<?php selected( $v, 'oktatoanyag' ); ?>>Oktatóanyag</option>
                            <option value="gyujtoi-kiadvany"<?php selected( $v, 'gyujtoi-kiadvany' ); ?>>Gyűjtői kiadvány</option>
                            <option value="digitalis-kiadvany"<?php selected( $v, 'digitalis-kiadvany' ); ?>>Digitális kiadvány</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Témakör</label>
                        <select name="publication_topics[]" class="va-select" multiple data-placeholder="Válassz témakört">
                            <option value="vadaszat"<?php echo in_array( 'vadaszat', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Vadászat</option>
                            <option value="fegyverismeret"<?php echo in_array( 'fegyverismeret', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Fegyverismeret</option>
                            <option value="ballisztika"<?php echo in_array( 'ballisztika', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Ballisztika</option>
                            <option value="kutyakikepzes"<?php echo in_array( 'kutyakikepzes', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Kutyakiképzés</option>
                            <option value="vadgazdalkodas"<?php echo in_array( 'vadgazdalkodas', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Vadgazdálkodás</option>
                            <option value="erdeszet"<?php echo in_array( 'erdeszet', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Erdészet</option>
                            <option value="termeszet"<?php echo in_array( 'termeszet', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Természet</option>
                            <option value="tuleles"<?php echo in_array( 'tuleles', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Túlélés</option>
                            <option value="bushcraft"<?php echo in_array( 'bushcraft', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Bushcraft</option>
                            <option value="horgaszat"<?php echo in_array( 'horgaszat', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Horgászat</option>
                            <option value="trofeabiralat"<?php echo in_array( 'trofeabiralat', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Trófeabírálat</option>
                            <option value="preparalas"<?php echo in_array( 'preparalas', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Preparálás</option>
                            <option value="tortenelmi"<?php echo in_array( 'tortenelmi', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Történelmi</option>
                            <option value="katonai"<?php echo in_array( 'katonai', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Katonai</option>
                            <option value="egyeb"<?php echo in_array( 'egyeb', $publication_topics_saved, true ) ? ' selected' : ''; ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group"><label>Cím</label><input type="text" name="publication_title" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_title' ) ); ?>" placeholder="pl. Régi Nimród évfolyam"></div>
                    <div class="va-form-group"><label>Alcím</label><input type="text" name="publication_subtitle" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_subtitle' ) ); ?>" placeholder="opcionális"></div>
                    <div class="va-form-group"><label>Szerző</label><input type="text" name="publication_author" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_author' ) ); ?>" placeholder="pl. Kovács István"></div>
                    <div class="va-form-group"><label>Kiadó</label><input type="text" name="publication_publisher" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_publisher' ) ); ?>" placeholder="pl. Nimród Kiadó"></div>
                    <div class="va-form-group"><label>Kiadás éve</label><input type="text" name="publication_year" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_year' ) ); ?>" placeholder="pl. 1986"></div>
                    <div class="va-form-group">
                        <label>Nyelv</label>
                        <?php $v = $publication_meta( 'publication_language' ); ?>
                        <select name="publication_language" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="magyar"<?php selected( $v, 'magyar' ); ?>>Magyar</option>
                            <option value="angol"<?php selected( $v, 'angol' ); ?>>Angol</option>
                            <option value="nemet"<?php selected( $v, 'nemet' ); ?>>Német</option>
                            <option value="szlovak"<?php selected( $v, 'szlovak' ); ?>>Szlovák</option>
                            <option value="roman"<?php selected( $v, 'roman' ); ?>>Román</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Kiadás típusa</label>
                        <?php $v = $publication_meta( 'publication_edition_type' ); ?>
                        <select name="publication_edition_type" id="va-publication-edition-type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="elso-kiadas"<?php selected( $v, 'elso-kiadas' ); ?>>Első kiadás</option>
                            <option value="utannyomas"<?php selected( $v, 'utannyomas' ); ?>>Utánnyomás</option>
                            <option value="limitalt"<?php selected( $v, 'limitalt' ); ?>>Limitált</option>
                            <option value="dedikalt"<?php selected( $v, 'dedikalt' ); ?>>Dedikált</option>
                            <option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option>
                            <option value="antikvar"<?php selected( $v, 'antikvar' ); ?>>Antikvár</option>
                        </select>
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-types="folyoirat,magazin,evkonyv">
                        <label>Folyóirat neve</label>
                        <input type="text" name="publication_periodical_name" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_periodical_name' ) ); ?>" placeholder="pl. Nimród">
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-types="folyoirat,magazin,evkonyv">
                        <label>Évfolyam</label>
                        <input type="text" name="publication_volume" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_volume' ) ); ?>" placeholder="pl. 1989/12">
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-types="folyoirat,magazin,evkonyv">
                        <label>Lapszám</label>
                        <input type="text" name="publication_issue" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_issue' ) ); ?>" placeholder="pl. 4">
                    </div>
                    <div class="va-form-group">
                        <label>Komplett évfolyam?</label>
                        <?php $v = $publication_meta( 'publication_complete_volume' ); ?>
                        <select name="publication_complete_volume" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option>
                            <option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group"><label>Oldalszám</label><input type="text" name="publication_page_count" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_page_count' ) ); ?>" placeholder="pl. 240"></div>
                    <div class="va-form-group">
                        <label>Borító</label>
                        <?php $v = $publication_meta( 'publication_cover_type' ); ?>
                        <select name="publication_cover_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="kemenyfedeles"<?php selected( $v, 'kemenyfedeles' ); ?>>Keményfedeles</option>
                            <option value="puhafedeles"<?php selected( $v, 'puhafedeles' ); ?>>Puhafedeles</option>
                        </select>
                    </div>
                    <div class="va-form-group"><label>Méret</label><input type="text" name="publication_size" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_size' ) ); ?>" placeholder="pl. A4, 17x24 cm"></div>
                    <div class="va-form-group">
                        <label>Általános állapot</label>
                        <?php $v = $publication_meta( 'publication_condition' ); ?>
                        <select name="publication_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option>
                            <option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option>
                            <option value="jo"<?php selected( $v, 'jo' ); ?>>Jó</option>
                            <option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option>
                            <option value="serult"<?php selected( $v, 'serult' ); ?>>Sérült</option>
                            <option value="gyujtoi"<?php selected( $v, 'gyujtoi' ); ?>>Gyűjtői</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Hibák</label>
                        <select name="publication_defects[]" class="va-select" multiple data-placeholder="Válassz hibákat">
                            <option value="szakadt"<?php echo in_array( 'szakadt', $publication_defects_saved, true ) ? ' selected' : ''; ?>>Szakadt</option>
                            <option value="firkalt"<?php echo in_array( 'firkalt', $publication_defects_saved, true ) ? ' selected' : ''; ?>>Firkált</option>
                            <option value="hianyos"<?php echo in_array( 'hianyos', $publication_defects_saved, true ) ? ' selected' : ''; ?>>Hiányos</option>
                            <option value="vizkaros"<?php echo in_array( 'vizkaros', $publication_defects_saved, true ) ? ' selected' : ''; ?>>Vízkáros</option>
                            <option value="laphiany"<?php echo in_array( 'laphiany', $publication_defects_saved, true ) ? ' selected' : ''; ?>>Laphiány</option>
                            <option value="kopott-borito"<?php echo in_array( 'kopott-borito', $publication_defects_saved, true ) ? ' selected' : ''; ?>>Kopott borító</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Tartalom</label>
                        <select name="publication_content_flags[]" class="va-select" multiple data-placeholder="Válassz tartalom típust">
                            <option value="szines-kepek"<?php echo in_array( 'szines-kepek', $publication_content_saved, true ) ? ' selected' : ''; ?>>Színes képek</option>
                            <option value="fekete-feher"<?php echo in_array( 'fekete-feher', $publication_content_saved, true ) ? ' selected' : ''; ?>>Fekete-fehér</option>
                            <option value="terkepek"<?php echo in_array( 'terkepek', $publication_content_saved, true ) ? ' selected' : ''; ?>>Térképek</option>
                            <option value="ballisztikai-tablazatok"<?php echo in_array( 'ballisztikai-tablazatok', $publication_content_saved, true ) ? ' selected' : ''; ?>>Ballisztikai táblázatok</option>
                            <option value="fotok"<?php echo in_array( 'fotok', $publication_content_saved, true ) ? ' selected' : ''; ?>>Fotók</option>
                            <option value="rajzok"<?php echo in_array( 'rajzok', $publication_content_saved, true ) ? ' selected' : ''; ?>>Rajzok</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Gyűjtői információ</label>
                        <select name="publication_collector_flags[]" class="va-select" multiple data-placeholder="Válassz gyűjtői jelölést">
                            <option value="ritka-kiadas"<?php echo in_array( 'ritka-kiadas', $publication_collector_flags_saved, true ) ? ' selected' : ''; ?>>Ritka kiadás</option>
                            <option value="sorszamozott"<?php echo in_array( 'sorszamozott', $publication_collector_flags_saved, true ) ? ' selected' : ''; ?>>Sorszámozott</option>
                            <option value="dedikalt"<?php echo in_array( 'dedikalt', $publication_collector_flags_saved, true ) ? ' selected' : ''; ?>>Dedikált</option>
                            <option value="megszunt-kiadvany"<?php echo in_array( 'megszunt-kiadvany', $publication_collector_flags_saved, true ) ? ' selected' : ''; ?>>Megszűnt kiadvány</option>
                            <option value="muzealis"<?php echo in_array( 'muzealis', $publication_collector_flags_saved, true ) ? ' selected' : ''; ?>>Muzeális</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Gyűjtői érték</label>
                        <?php $v = $publication_meta( 'publication_collector_value' ); ?>
                        <select name="publication_collector_value" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="alacsony"<?php selected( $v, 'alacsony' ); ?>>Alacsony</option>
                            <option value="kozepes"<?php selected( $v, 'kozepes' ); ?>>Közepes</option>
                            <option value="magas"<?php selected( $v, 'magas' ); ?>>Magas</option>
                            <option value="kiemelt"<?php selected( $v, 'kiemelt' ); ?>>Kiemelt</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Felhasználás</label>
                        <select name="publication_usage[]" class="va-select" multiple data-placeholder="Válassz felhasználást">
                            <option value="oktatas"<?php echo in_array( 'oktatas', $publication_usage_saved, true ) ? ' selected' : ''; ?>>Oktatás</option>
                            <option value="gyujtemeny"<?php echo in_array( 'gyujtemeny', $publication_usage_saved, true ) ? ' selected' : ''; ?>>Gyűjtemény</option>
                            <option value="referencia"<?php echo in_array( 'referencia', $publication_usage_saved, true ) ? ' selected' : ''; ?>>Referencia</option>
                            <option value="ajandek"<?php echo in_array( 'ajandek', $publication_usage_saved, true ) ? ' selected' : ''; ?>>Ajándék</option>
                            <option value="kutatas"<?php echo in_array( 'kutatas', $publication_usage_saved, true ) ? ' selected' : ''; ?>>Kutatás</option>
                        </select>
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-editions="antikvar" style="grid-column:1 / -1;">
                        <label>Állapot részletezés (antikvár)</label>
                        <textarea name="publication_condition_details" class="va-input" rows="2" placeholder="pl. gerinc enyhén kopott, lapok épek"><?php echo esc_textarea( $publication_meta( 'publication_condition_details' ) ); ?></textarea>
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-editions="antikvar">
                        <label>Eredetiség</label>
                        <?php $v = $publication_meta( 'publication_authenticity' ); ?>
                        <select name="publication_authenticity" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="eredeti"<?php selected( $v, 'eredeti' ); ?>>Eredeti</option>
                            <option value="ismeretlen"<?php selected( $v, 'ismeretlen' ); ?>>Ismeretlen</option>
                            <option value="masolat"<?php selected( $v, 'masolat' ); ?>>Másolat</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Dedikált példány?</label>
                        <?php $v = $publication_meta( 'publication_signed_copy' ); ?>
                        <select name="publication_signed_copy" id="va-publication-signed-copy" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option>
                            <option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option>
                        </select>
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-signed="yes">
                        <label>Kinek szól</label>
                        <input type="text" name="publication_signed_to" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_signed_to' ) ); ?>" placeholder="pl. Kovács úrnak">
                    </div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-signed="yes">
                        <label>Aláírás típusa</label>
                        <?php $v = $publication_meta( 'publication_signature_type' ); ?>
                        <select name="publication_signature_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="kezjegy"<?php selected( $v, 'kezjegy' ); ?>>Kézjegy</option>
                            <option value="teljes-nev"<?php selected( $v, 'teljes-nev' ); ?>>Teljes név</option>
                            <option value="uzenettel"<?php selected( $v, 'uzenettel' ); ?>>Üzenettel</option>
                            <option value="ismeretlen"<?php selected( $v, 'ismeretlen' ); ?>>Ismeretlen</option>
                        </select>
                    </div>
                    <div class="va-form-group"><label>ISBN</label><input type="text" name="publication_isbn" class="va-input" value="<?php echo esc_attr( $publication_meta( 'publication_isbn' ) ); ?>" placeholder="pl. 9789630000000"></div>
                    <div class="va-form-group va-publication-dynamic-group" data-publication-types="digitalis-kiadvany">
                        <label>Digitális formátum</label>
                        <?php $v = $publication_meta( 'publication_digital_format' ); ?>
                        <select name="publication_digital_format" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="pdf"<?php selected( $v, 'pdf' ); ?>>PDF</option>
                            <option value="epub"<?php selected( $v, 'epub' ); ?>>EPUB</option>
                            <option value="mobi"<?php selected( $v, 'mobi' ); ?>>MOBI</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Moderáció</label>
                        <select name="publication_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázati jelzők">
                            <option value="szerzoi-jogserto-digitalis-masolat"<?php echo in_array( 'szerzoi-jogserto-digitalis-masolat', $publication_moderation_saved, true ) ? ' selected' : ''; ?>>Szerzői jogsértő digitális másolat</option>
                            <option value="tiltott-tartalom"<?php echo in_array( 'tiltott-tartalom', $publication_moderation_saved, true ) ? ' selected' : ''; ?>>Tiltott tartalom</option>
                            <option value="hamis-dedikalas"<?php echo in_array( 'hamis-dedikalas', $publication_moderation_saved, true ) ? ' selected' : ''; ?>>Hamis dedikálás</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Megjegyzés</label>
                        <textarea name="publication_notes" class="va-input" rows="2" placeholder="pl. régi Nimród évfolyam, hiánytalan sorozat"><?php echo esc_textarea( $publication_meta( 'publication_notes' ) ); ?></textarea>
                    </div>
                </div>
            </div>
            <?php
            $equipment_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $equipment_csv = static function( string $key ) use ( $equipment_meta ): array {
                return array_filter( array_map( 'trim', explode( ',', (string) $equipment_meta( $key ) ) ) );
            };
            $equipment_usage_saved = $equipment_csv( 'equipment_hunting_usage' );
            $equipment_features_saved = $equipment_csv( 'equipment_features' );
            $equipment_light_modes_saved = $equipment_csv( 'equipment_light_color_modes' );
            $equipment_electronic_saved = $equipment_csv( 'equipment_electronic_features' );
            $equipment_accessories_saved = $equipment_csv( 'equipment_accessories' );
            $equipment_moderation_saved = $equipment_csv( 'equipment_moderation_flags' );
            ?>
            <div class="va-cat-rule-field va-equipment-fields-grid" data-categories="vadasz-felszereles" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Felszerelés típusa</label>
                        <?php $v = $equipment_meta( 'equipment_type' ); ?>
                        <select name="equipment_type" id="va-equipment-type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="leszsak"<?php selected( $v, 'leszsak' ); ?>>Leszsák</option>
                            <option value="lobot"<?php selected( $v, 'lobot' ); ?>>Lőbot</option>
                            <option value="vadhivo"<?php selected( $v, 'vadhivo' ); ?>>Vadhívó</option>
                            <option value="csali"<?php selected( $v, 'csali' ); ?>>Csali</option>
                            <option value="eteto"<?php selected( $v, 'eteto' ); ?>>Etető</option>
                            <option value="szoro"<?php selected( $v, 'szoro' ); ?>>Szóró</option>
                            <option value="magasles-tartozek"<?php selected( $v, 'magasles-tartozek' ); ?>>Magasles tartozék</option>
                            <option value="fegyvertok"<?php selected( $v, 'fegyvertok' ); ?>>Fegyvertok</option>
                            <option value="toltenytarto"<?php selected( $v, 'toltenytarto' ); ?>>Tölténytartó</option>
                            <option value="fegyverszij"<?php selected( $v, 'fegyverszij' ); ?>>Fegyverszíj</option>
                            <option value="tisztitokeszlet"<?php selected( $v, 'tisztitokeszlet' ); ?>>Tisztítókészlet</option>
                            <option value="fegyvertamasz"<?php selected( $v, 'fegyvertamasz' ); ?>>Fegyvertámasz</option>
                            <option value="bipod"<?php selected( $v, 'bipod' ); ?>>Bipod</option>
                            <option value="hangtompito-huzat"<?php selected( $v, 'hangtompito-huzat' ); ?>>Hangtompító huzat</option>
                            <option value="uloeke"<?php selected( $v, 'uloeke' ); ?>>Ülőke</option>
                            <option value="vadaszszek"<?php selected( $v, 'vadaszszek' ); ?>>Vadászszék</option>
                            <option value="vadaszhalo"<?php selected( $v, 'vadaszhalo' ); ?>>Vadászháló</option>
                            <option value="alcahalo"<?php selected( $v, 'alcahalo' ); ?>>Álcaháló</option>
                            <option value="tabori-felszereles"<?php selected( $v, 'tabori-felszereles' ); ?>>Tábori felszerelés</option>
                            <option value="kulacs"<?php selected( $v, 'kulacs' ); ?>>Kulacs</option>
                            <option value="termosz"<?php selected( $v, 'termosz' ); ?>>Termosz</option>
                            <option value="elemlampa"<?php selected( $v, 'elemlampa' ); ?>>Elemlámpa</option>
                            <option value="fejlampa"<?php selected( $v, 'fejlampa' ); ?>>Fejlámpa</option>
                            <option value="akkumulator"<?php selected( $v, 'akkumulator' ); ?>>Akkumulátor</option>
                            <option value="vadaszkurt"<?php selected( $v, 'vadaszkurt' ); ?>>Vadászkürt</option>
                            <option value="csapda"<?php selected( $v, 'csapda' ); ?>>Csapda</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Állapot</label>
                        <?php $v = $equipment_meta( 'equipment_condition' ); ?>
                        <select name="equipment_condition" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="uj"<?php selected( $v, 'uj' ); ?>>Új</option>
                            <option value="ujszeru"<?php selected( $v, 'ujszeru' ); ?>>Újszerű</option>
                            <option value="hasznalt"<?php selected( $v, 'hasznalt' ); ?>>Használt</option>
                            <option value="hibas"<?php selected( $v, 'hibas' ); ?>>Hibás</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Felhasználás</label>
                        <select name="equipment_hunting_usage[]" class="va-select" multiple data-placeholder="Válassz felhasználást">
                            <option value="lesvadaszat"<?php echo in_array( 'lesvadaszat', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Lesvadászat</option>
                            <option value="cserkeles"<?php echo in_array( 'cserkeles', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Cserkelés</option>
                            <option value="hajtas"<?php echo in_array( 'hajtas', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Hajtás</option>
                            <option value="aprovad"<?php echo in_array( 'aprovad', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Apróvad</option>
                            <option value="nagyvad"<?php echo in_array( 'nagyvad', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Nagyvad</option>
                            <option value="horgaszat"<?php echo in_array( 'horgaszat', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Horgászat</option>
                            <option value="bushcraft"<?php echo in_array( 'bushcraft', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Bushcraft</option>
                            <option value="tuleles"<?php echo in_array( 'tuleles', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Túlélés</option>
                            <option value="tura"<?php echo in_array( 'tura', $equipment_usage_saved, true ) ? ' selected' : ''; ?>>Túra</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Anyag</label>
                        <?php $v = $equipment_meta( 'equipment_material_type' ); ?>
                        <select name="equipment_material_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="aluminium"<?php selected( $v, 'aluminium' ); ?>>Alumínium</option>
                            <option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option>
                            <option value="muanyag"<?php selected( $v, 'muanyag' ); ?>>Műanyag</option>
                            <option value="cordura"<?php selected( $v, 'cordura' ); ?>>Cordura</option>
                            <option value="bor"<?php selected( $v, 'bor' ); ?>>Bőr</option>
                            <option value="fa"<?php selected( $v, 'fa' ); ?>>Fa</option>
                            <option value="karbon"<?php selected( $v, 'karbon' ); ?>>Karbon</option>
                            <option value="textil"<?php selected( $v, 'textil' ); ?>>Textil</option>
                            <option value="gumi"<?php selected( $v, 'gumi' ); ?>>Gumi</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Szín / minta</label>
                        <?php $v = $equipment_meta( 'equipment_color_pattern' ); ?>
                        <select name="equipment_color_pattern" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="zold"<?php selected( $v, 'zold' ); ?>>Zöld</option>
                            <option value="barna"<?php selected( $v, 'barna' ); ?>>Barna</option>
                            <option value="fekete"<?php selected( $v, 'fekete' ); ?>>Fekete</option>
                            <option value="terepmintas"<?php selected( $v, 'terepmintas' ); ?>>Terepmintás</option>
                            <option value="realtree"<?php selected( $v, 'realtree' ); ?>>Realtree</option>
                            <option value="multicam"<?php selected( $v, 'multicam' ); ?>>Multicam</option>
                            <option value="ho-mintas"<?php selected( $v, 'ho-mintas' ); ?>>Hó mintás</option>
                        </select>
                    </div>
                    <div class="va-form-group"><label>Hossz (cm)</label><input type="text" name="equipment_length_cm" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_length_cm' ) ); ?>" placeholder="pl. 120"></div>
                    <div class="va-form-group"><label>Szélesség (cm)</label><input type="text" name="equipment_width_cm" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_width_cm' ) ); ?>" placeholder="pl. 35"></div>
                    <div class="va-form-group"><label>Magasság (cm)</label><input type="text" name="equipment_height_cm" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_height_cm' ) ); ?>" placeholder="pl. 18"></div>
                    <div class="va-form-group"><label>Súly (g)</label><input type="text" name="equipment_weight_g" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_weight_g' ) ); ?>" placeholder="pl. 850"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Tulajdonságok</label>
                        <select name="equipment_features[]" class="va-select" multiple data-placeholder="Válassz tulajdonságot">
                            <option value="vizallo"<?php echo in_array( 'vizallo', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Vízálló</option>
                            <option value="hangtalan"<?php echo in_array( 'hangtalan', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Hangtalan</option>
                            <option value="osszecsukhato"<?php echo in_array( 'osszecsukhato', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Összecsukható</option>
                            <option value="ultrakonnyu"<?php echo in_array( 'ultrakonnyu', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Ultrakönnyű</option>
                            <option value="allithato"<?php echo in_array( 'allithato', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Állítható</option>
                            <option value="modularis"<?php echo in_array( 'modularis', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Moduláris</option>
                            <option value="gyorskioldos"<?php echo in_array( 'gyorskioldos', $equipment_features_saved, true ) ? ' selected' : ''; ?>>Gyorskioldós</option>
                        </select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="elemlampa,fejlampa">
                        <label>Lumen</label>
                        <input type="text" name="equipment_light_lumen" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_light_lumen' ) ); ?>" placeholder="pl. 1200">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="elemlampa,fejlampa">
                        <label>Hatótáv (m)</label>
                        <input type="text" name="equipment_light_range_m" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_light_range_m' ) ); ?>" placeholder="pl. 180">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="elemlampa,fejlampa,akkumulator">
                        <label>Akkumulátor típus</label>
                        <input type="text" name="equipment_light_battery_type" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_light_battery_type' ) ); ?>" placeholder="pl. 18650 / Li-ion">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="elemlampa,fejlampa,akkumulator">
                        <label>USB tölthető</label>
                        <?php $v = $equipment_meta( 'equipment_light_usb_charge' ); ?>
                        <select name="equipment_light_usb_charge" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="elemlampa,fejlampa" style="grid-column:1 / -1;">
                        <label>Fény módok</label>
                        <select name="equipment_light_color_modes[]" class="va-select" multiple data-placeholder="Válassz fény módot">
                            <option value="feher"<?php echo in_array( 'feher', $equipment_light_modes_saved, true ) ? ' selected' : ''; ?>>Fehér fény</option>
                            <option value="piros"<?php echo in_array( 'piros', $equipment_light_modes_saved, true ) ? ' selected' : ''; ?>>Piros fény</option>
                            <option value="zold"<?php echo in_array( 'zold', $equipment_light_modes_saved, true ) ? ' selected' : ''; ?>>Zöld fény</option>
                        </select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="elemlampa,fejlampa">
                        <label>Színhőmérséklet</label>
                        <input type="text" name="equipment_light_color_temperature" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_light_color_temperature' ) ); ?>" placeholder="pl. 5000K">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="lobot,bipod,fegyvertamasz">
                        <label>Magasság tartomány (cm)</label>
                        <input type="text" name="equipment_support_height_range_cm" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_support_height_range_cm' ) ); ?>" placeholder="pl. 70-180">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="lobot,bipod,fegyvertamasz">
                        <label>Lábak száma</label>
                        <input type="text" name="equipment_support_legs" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_support_legs' ) ); ?>" placeholder="pl. 2 / 3 / 4">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="lobot,bipod,fegyvertamasz">
                        <label>Forgatható fej</label>
                        <?php $v = $equipment_meta( 'equipment_support_head_swivel' ); ?>
                        <select name="equipment_support_head_swivel" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="lobot,bipod,fegyvertamasz">
                        <label>Támrúd anyaga</label>
                        <?php $v = $equipment_meta( 'equipment_support_material' ); ?>
                        <select name="equipment_support_material" class="va-select"><option value="">– Válasszon –</option><option value="karbon"<?php selected( $v, 'karbon' ); ?>>Karbon</option><option value="aluminium"<?php selected( $v, 'aluminium' ); ?>>Alumínium</option><option value="acel"<?php selected( $v, 'acel' ); ?>>Acél</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="vadhivo">
                        <label>Vadhívó típusa</label>
                        <?php $v = $equipment_meta( 'equipment_call_type' ); ?>
                        <select name="equipment_call_type" id="va-equipment-call-type" class="va-select"><option value="">– Válasszon –</option><option value="kezi"<?php selected( $v, 'kezi' ); ?>>Kézi</option><option value="elektronikus"<?php selected( $v, 'elektronikus' ); ?>>Elektronikus</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="vadhivo">
                        <label>Vadfaj</label>
                        <input type="text" name="equipment_call_species" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_call_species' ) ); ?>" placeholder="pl. róka, szarvas, vaddisznó">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="vadhivo">
                        <label>Hangerő</label>
                        <input type="text" name="equipment_call_volume" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_call_volume' ) ); ?>" placeholder="pl. állítható / 90 dB">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="csapda">
                        <label>Csapda típusa</label>
                        <?php $v = $equipment_meta( 'equipment_trap_type' ); ?>
                        <select name="equipment_trap_type" class="va-select"><option value="">– Válasszon –</option><option value="elvefogo"<?php selected( $v, 'elvefogo' ); ?>>Élvefogó</option><option value="ragadozo"<?php selected( $v, 'ragadozo' ); ?>>Ragadozó</option><option value="kisvad"<?php selected( $v, 'kisvad' ); ?>>Kisvad</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="csapda">
                        <label>Méret</label>
                        <input type="text" name="equipment_trap_size" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_trap_size' ) ); ?>" placeholder="pl. 80x25x30 cm">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="tisztitokeszlet" style="grid-column:1 / -1;">
                        <label>Kaliber kompatibilitás</label>
                        <input type="text" name="equipment_cleaning_caliber_compatibility" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_cleaning_caliber_compatibility' ) ); ?>" placeholder="pl. .22, .30, 12/70">
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="tisztitokeszlet">
                        <label>Komplett szett</label>
                        <?php $v = $equipment_meta( 'equipment_cleaning_full_set' ); ?>
                        <select name="equipment_cleaning_full_set" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="tisztitokeszlet">
                        <label>Olaj</label>
                        <?php $v = $equipment_meta( 'equipment_cleaning_oil' ); ?>
                        <select name="equipment_cleaning_oil" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="tisztitokeszlet">
                        <label>Bronzkefe</label>
                        <?php $v = $equipment_meta( 'equipment_cleaning_bronze_brush' ); ?>
                        <select name="equipment_cleaning_bronze_brush" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="uloeke,vadaszszek">
                        <label>Összecsukható</label>
                        <?php $v = $equipment_meta( 'equipment_seat_foldable' ); ?>
                        <select name="equipment_seat_foldable" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="uloeke,vadaszszek">
                        <label>Háttámlás</label>
                        <?php $v = $equipment_meta( 'equipment_seat_backrest' ); ?>
                        <select name="equipment_seat_backrest" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="uloeke,vadaszszek">
                        <label>Forgó ülés</label>
                        <?php $v = $equipment_meta( 'equipment_seat_swivel' ); ?>
                        <select name="equipment_seat_swivel" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-types="uloeke,vadaszszek">
                        <label>Teherbírás (kg)</label>
                        <input type="text" name="equipment_seat_capacity_kg" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_seat_capacity_kg' ) ); ?>" placeholder="pl. 120">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Elektronikus kiegészítők</label>
                        <select name="equipment_electronic_features[]" id="va-equipment-electronic-features" class="va-select" multiple data-placeholder="Válassz elektronikus jellemzőt">
                            <option value="akkumulatoros"<?php echo in_array( 'akkumulatoros', $equipment_electronic_saved, true ) ? ' selected' : ''; ?>>Akkumulátoros</option>
                            <option value="usb-c"<?php echo in_array( 'usb-c', $equipment_electronic_saved, true ) ? ' selected' : ''; ?>>USB-C</option>
                            <option value="powerbank-kompatibilis"<?php echo in_array( 'powerbank-kompatibilis', $equipment_electronic_saved, true ) ? ' selected' : ''; ?>>Powerbank kompatibilis</option>
                            <option value="napelemes"<?php echo in_array( 'napelemes', $equipment_electronic_saved, true ) ? ' selected' : ''; ?>>Napelemes</option>
                        </select>
                    </div>
                    <div class="va-form-group va-equipment-dynamic-group" data-equipment-runtime="yes">
                        <label>Üzemidő</label>
                        <input type="text" name="equipment_runtime_hours" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_runtime_hours' ) ); ?>" placeholder="pl. 8 óra">
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Tartozékok</label>
                        <select name="equipment_accessories[]" class="va-select" multiple data-placeholder="Válassz tartozékot">
                            <option value="tok"<?php echo in_array( 'tok', $equipment_accessories_saved, true ) ? ' selected' : ''; ?>>Tok</option>
                            <option value="pant"<?php echo in_array( 'pant', $equipment_accessories_saved, true ) ? ' selected' : ''; ?>>Pánt</option>
                            <option value="akkumulator"<?php echo in_array( 'akkumulator', $equipment_accessories_saved, true ) ? ' selected' : ''; ?>>Akkumulátor</option>
                            <option value="tolto"<?php echo in_array( 'tolto', $equipment_accessories_saved, true ) ? ' selected' : ''; ?>>Töltő</option>
                            <option value="rogzito"<?php echo in_array( 'rogzito', $equipment_accessories_saved, true ) ? ' selected' : ''; ?>>Rögzítő</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Csendességi szint</label>
                        <?php $v = $equipment_meta( 'equipment_silence_level' ); ?>
                        <select name="equipment_silence_level" class="va-select"><option value="">– Válasszon –</option><option value="hangtalan"<?php selected( $v, 'hangtalan' ); ?>>Hangtalan</option><option value="halk"<?php selected( $v, 'halk' ); ?>>Halk</option><option value="normal"<?php selected( $v, 'normal' ); ?>>Normál</option></select>
                    </div>
                    <div class="va-form-group"><label>Melyik fegyverhez jó</label><input type="text" name="equipment_weapon_compatibility" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_weapon_compatibility' ) ); ?>" placeholder="pl. golyós puska, sörétes"></div>
                    <div class="va-form-group"><label>Melyik kaliberhez jó</label><input type="text" name="equipment_caliber_compatibility" class="va-input" value="<?php echo esc_attr( $equipment_meta( 'equipment_caliber_compatibility' ) ); ?>" placeholder="pl. .308, 12/76"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Moderációs jelzők</label>
                        <select name="equipment_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázati jelzők">
                            <option value="tiltott-csapda"<?php echo in_array( 'tiltott-csapda', $equipment_moderation_saved, true ) ? ' selected' : ''; ?>>Tiltott csapda</option>
                            <option value="illegalis-vadaszati-eszkoz"<?php echo in_array( 'illegalis-vadaszati-eszkoz', $equipment_moderation_saved, true ) ? ' selected' : ''; ?>>Illegális vadászati eszköz</option>
                            <option value="hangtompito"<?php echo in_array( 'hangtompito', $equipment_moderation_saved, true ) ? ' selected' : ''; ?>>Hangtompító</option>
                            <option value="tiltott-elektronikus-hivo"<?php echo in_array( 'tiltott-elektronikus-hivo', $equipment_moderation_saved, true ) ? ' selected' : ''; ?>>Tiltott elektronikus hívó</option>
                        </select>
                    </div>
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Megjegyzés</label>
                        <textarea name="equipment_notes" class="va-input" rows="2" placeholder="pl. Realtree mintás, vízálló tok, .30-as kaliberhez is jó"><?php echo esc_textarea( $equipment_meta( 'equipment_notes' ) ); ?></textarea>
                    </div>
                </div>
            </div>
            <?php
            $service_meta = static function( string $key ) use ( $edit_meta, $edit_post_id ) {
                if ( isset( $edit_meta[ $key ] ) ) {
                    return is_scalar( $edit_meta[ $key ] ) ? (string) $edit_meta[ $key ] : '';
                }
                if ( $edit_post_id > 0 ) {
                    return (string) get_post_meta( $edit_post_id, 'va_' . $key, true );
                }
                return '';
            };
            $service_schedule_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_schedule' ) ) ) );
            $service_hunting_specialization_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_hunting_specialization' ) ) ) );
            $service_hunting_species_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_hunting_species' ) ) ) );
            $service_weapon_categories_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_weapon_categories' ) ) ) );
            $service_equipment_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_equipment' ) ) ) );
            $service_permits_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_permits' ) ) ) );
            $service_media_required_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_media_required' ) ) ) );
            $service_moderation_saved = array_filter( array_map( 'trim', explode( ',', $service_meta( 'service_moderation_flags' ) ) ) );
            ?>
            <div class="va-cat-rule-field va-service-fields-grid" data-categories="szolgaltatas" style="display:none;">
                <div class="va-step2-4col-inner">
                    <div class="va-form-group">
                        <label>Szolgáltatás típusa</label>
                        <?php $v = $service_meta( 'service_type' ); ?>
                        <select name="service_type" id="va-service-type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="vadaszati-szolgaltatas"<?php selected( $v, 'vadaszati-szolgaltatas' ); ?>>Vadászati szolgáltatás</option>
                            <option value="fegyvermusz"<?php selected( $v, 'fegyvermusz' ); ?>>Fegyver / optika</option>
                            <option value="preparator"<?php selected( $v, 'preparator' ); ?>>Trófea / preparálás</option>
                            <option value="kutyas-szolgaltatas"<?php selected( $v, 'kutyas-szolgaltatas' ); ?>>Kutyás szolgáltatás</option>
                            <option value="terulet-erdo"<?php selected( $v, 'terulet-erdo' ); ?>>Terület / erdő</option>
                            <option value="oktatas"<?php selected( $v, 'oktatas' ); ?>>Oktatás</option>
                            <option value="szallitas"<?php selected( $v, 'szallitas' ); ?>>Szállítás</option>
                            <option value="egyeb"<?php selected( $v, 'egyeb' ); ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Szolgáltató típusa</label>
                        <?php $v = $service_meta( 'service_provider_type' ); ?>
                        <select name="service_provider_type" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="ceg"<?php selected( $v, 'ceg' ); ?>>Cég</option>
                            <option value="maganszemely"<?php selected( $v, 'maganszemely' ); ?>>Magánszemély</option>
                            <option value="egyesulet"<?php selected( $v, 'egyesulet' ); ?>>Egyesület</option>
                            <option value="vadtarsasag"<?php selected( $v, 'vadtarsasag' ); ?>>Vadásztársaság</option>
                        </select>
                    </div>
                    <div class="va-form-group">
                        <label>Szolgáltató neve</label>
                        <input type="text" name="service_provider_name" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_provider_name' ) ); ?>" placeholder="pl. Név / műhely / cég">
                    </div>
                    <div class="va-form-group"><label>Ország</label><input type="text" name="service_country" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_country' ) ); ?>" placeholder="pl. Magyarország"></div>
                    <div class="va-form-group">
                        <label>Megye</label>
                        <?php $service_county_val = (int) $service_meta( 'service_county' ); ?>
                        <select name="service_county" class="va-select">
                            <option value="">– Válasszon –</option>
                            <?php foreach ( $counties as $county ): ?><option value="<?php echo esc_attr( $county->term_id ); ?>"<?php echo selected( $service_county_val, (int) $county->term_id, false ); ?>><?php echo esc_html( $county->name ); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="va-form-group"><label>Település</label><input type="text" name="service_town" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_town' ) ); ?>" placeholder="pl. Kaposvár"></div>
                    <div class="va-form-group">
                        <label>Kiszállási terület</label>
                        <?php $v = $service_meta( 'service_area' ); ?>
                        <select name="service_area" class="va-select"><option value="">– Válasszon –</option><option value="helyi"<?php selected( $v, 'helyi' ); ?>>Helyi</option><option value="megyei"<?php selected( $v, 'megyei' ); ?>>Megyei</option><option value="orszagos"<?php selected( $v, 'orszagos' ); ?>>Országos</option><option value="nemzetkozi"<?php selected( $v, 'nemzetkozi' ); ?>>Nemzetközi</option></select>
                    </div>
                    <div class="va-form-group"><label>GPS koordináta</label><div class="va-two-up" style="display:grid;grid-template-columns:1fr;gap:10px;"><input type="text" name="service_gps_lat" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_gps_lat' ) ); ?>" placeholder="lat"><input type="text" name="service_gps_lng" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_gps_lng' ) ); ?>" placeholder="lng"></div></div>
                    <div class="va-form-group"><label>Ár típusa</label><?php $v = $service_meta( 'service_pricing_type' ); ?><select name="service_pricing_type" class="va-select"><option value="">– Válasszon –</option><option value="oradij"<?php selected( $v, 'oradij' ); ?>>Óradíj</option><option value="fix"<?php selected( $v, 'fix' ); ?>>Fix</option><option value="km-alapu"<?php selected( $v, 'km-alapu' ); ?>>Km alapú</option><option value="projekt"<?php selected( $v, 'projekt' ); ?>>Projekt alapú</option><option value="egyedi-ajanlat"<?php selected( $v, 'egyedi-ajanlat' ); ?>>Egyedi ajánlat</option></select></div>
                    <div class="va-form-group"><label>Minimum ár</label><input type="text" name="service_min_price" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_min_price' ) ); ?>" placeholder="pl. 25 000 Ft"></div>
                    <div class="va-form-group"><label>Kiszállási díj</label><input type="text" name="service_travel_fee" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_travel_fee' ) ); ?>" placeholder="pl. 120 Ft/km vagy fix díj"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Elérhetőség</label><select name="service_schedule[]" class="va-select" multiple data-placeholder="Válassz elérhetőséget"><option value="hétköznap"<?php echo in_array( 'hétköznap', $service_schedule_saved, true ) ? ' selected' : ''; ?>>Hétköznap</option><option value="hétvége"<?php echo in_array( 'hétvége', $service_schedule_saved, true ) ? ' selected' : ''; ?>>Hétvége</option><option value="0-24"<?php echo in_array( '0-24', $service_schedule_saved, true ) ? ' selected' : ''; ?>>0-24</option><option value="szezonalis"<?php echo in_array( 'szezonalis', $service_schedule_saved, true ) ? ' selected' : ''; ?>>Szezonális</option></select></div>
                    <div class="va-form-group"><label>Hétvégi elérhetőség</label><?php $v = $service_meta( 'service_available_weekend' ); ?><select name="service_available_weekend" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>0-24 elérhető</label><?php $v = $service_meta( 'service_available_24_7' ); ?><select name="service_available_24_7" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Szezonális szolgáltatás</label><?php $v = $service_meta( 'service_seasonal' ); ?><select name="service_seasonal" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Eszközök</label><select name="service_equipment[]" class="va-select" multiple data-placeholder="Válassz eszközöket"><option value="terepjaro"<?php echo in_array( 'terepjaro', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Terepjáró</option><option value="quad"<?php echo in_array( 'quad', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Quad</option><option value="dron"<?php echo in_array( 'dron', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Drón</option><option value="hokamera"<?php echo in_array( 'hokamera', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Hőkamera</option><option value="vereb"<?php echo in_array( 'vereb', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Véreb</option><option value="muhely"<?php echo in_array( 'muhely', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Műhely</option><option value="mobil-szerviz"<?php echo in_array( 'mobil-szerviz', $service_equipment_saved, true ) ? ' selected' : ''; ?>>Mobil szerviz</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Engedélyek</label><select name="service_permits[]" class="va-select" multiple data-placeholder="Válassz engedélyt"><option value="fegyvermester-engedely"<?php echo in_array( 'fegyvermester-engedely', $service_permits_saved, true ) ? ' selected' : ''; ?>>Fegyvermester engedély</option><option value="vadaszjegy"<?php echo in_array( 'vadaszjegy', $service_permits_saved, true ) ? ' selected' : ''; ?>>Vadászjegy</option><option value="preparatori-engedely"<?php echo in_array( 'preparatori-engedely', $service_permits_saved, true ) ? ' selected' : ''; ?>>Preparátori engedély</option><option value="vallalkozas"<?php echo in_array( 'vallalkozas', $service_permits_saved, true ) ? ' selected' : ''; ?>>Vállalkozás</option><option value="biztositas"<?php echo in_array( 'biztositas', $service_permits_saved, true ) ? ' selected' : ''; ?>>Biztosítás</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Vadászati specializáció</label><select name="service_hunting_specialization[]" class="va-select" multiple data-placeholder="Válassz specializációt"><option value="vadaszatszervezes"<?php echo in_array( 'vadaszatszervezes', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Vadászat szervezés</option><option value="bervadasztatas"<?php echo in_array( 'bervadasztatas', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Bérvadásztatás</option><option value="hivatasos-vadasz"<?php echo in_array( 'hivatasos-vadasz', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Hivatásos vadász szolgáltatás</option><option value="vadkar-elharitas"<?php echo in_array( 'vadkar-elharitas', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Vadkár elhárítás</option><option value="vadbefogas"<?php echo in_array( 'vadbefogas', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Vadbefogás</option><option value="csapdazas"<?php echo in_array( 'csapdazas', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Csapdázás</option><option value="nyomkovetes"<?php echo in_array( 'nyomkovetes', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Nyomkövetés</option><option value="utankereses"<?php echo in_array( 'utankereses', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Utánkeresés</option><option value="vereb-szolgaltatas"<?php echo in_array( 'vereb-szolgaltatas', $service_hunting_specialization_saved, true ) ? ' selected' : ''; ?>>Véreb szolgáltatás</option></select></div>
                    <div class="va-form-group va-service-dynamic-group" data-service-types="fegyvermusz" style="display:none;"><label>Vállalt kaliberek</label><input type="text" name="service_caliber_support" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_caliber_support' ) ); ?>" placeholder="pl. .22 LR, 9x19, .308 Win"></div>
                    <div class="va-form-group va-service-dynamic-group" data-service-types="fegyvermusz" style="display:none;"><label>Sürgős javítás</label><?php $v = $service_meta( 'service_urgent_repair' ); ?><select name="service_urgent_repair" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-service-dynamic-group" data-service-types="preparator" style="display:none;"><label>Vadfajok</label><select name="service_hunting_species[]" class="va-select" multiple data-placeholder="Válassz vadfajt"><option value="szarvas"<?php echo in_array( 'szarvas', $service_hunting_species_saved, true ) ? ' selected' : ''; ?>>Szarvas</option><option value="oez"<?php echo in_array( 'oez', $service_hunting_species_saved, true ) ? ' selected' : ''; ?>>Őz</option><option value="vaddiszno"<?php echo in_array( 'vaddiszno', $service_hunting_species_saved, true ) ? ' selected' : ''; ?>>Vaddisznó</option><option value="muflon"<?php echo in_array( 'muflon', $service_hunting_species_saved, true ) ? ' selected' : ''; ?>>Muflon</option><option value="aprovad"<?php echo in_array( 'aprovad', $service_hunting_species_saved, true ) ? ' selected' : ''; ?>>Apróvad</option></select></div>
                    <div class="va-form-group va-service-dynamic-group" data-service-types="preparator" style="display:none;"><label>Teljes preparálás</label><?php $v = $service_meta( 'service_full_prep' ); ?><select name="service_full_prep" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group va-service-dynamic-group" data-service-types="utankereses,kutyas-szolgaltatas" style="display:none;"><label>Véreb fajta</label><input type="text" name="service_hound_breed" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_hound_breed' ) ); ?>" placeholder="pl. véreb, drótszőrű vizsla"></div>
                    <div class="va-form-group va-service-dynamic-group" data-service-types="utankereses,kutyas-szolgaltatas" style="display:none;"><label>Éjszakai vállalás</label><?php $v = $service_meta( 'service_night_work' ); ?><select name="service_night_work" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><strong>Referenciák</strong></div>
                    <div class="va-form-group"><label>Ügyfél értékelés</label><?php $v = $service_meta( 'service_reference_rating' ); ?><select name="service_reference_rating" class="va-select"><option value="">– Válasszon –</option><option value="1"<?php selected( $v, '1' ); ?>>1</option><option value="2"<?php selected( $v, '2' ); ?>>2</option><option value="3"<?php selected( $v, '3' ); ?>>3</option><option value="4"<?php selected( $v, '4' ); ?>>4</option><option value="5"<?php selected( $v, '5' ); ?>>5</option></select></div>
                    <div class="va-form-group"><label>Elvégzett munkák</label><input type="text" name="service_completed_jobs" class="va-input" value="<?php echo esc_attr( $service_meta( 'service_completed_jobs' ) ); ?>" placeholder="pl. 120+ sikeres megbízás"></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Referencia megjegyzés</label><textarea name="service_reference_notes" class="va-input" rows="3" placeholder="Referencia képek, ügyfél visszajelzés, műhely leírás..."><?php echo esc_textarea( $service_meta( 'service_reference_notes' ) ); ?></textarea></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Média kötelező elemek</label><select name="service_media_required[]" class="va-select" multiple data-placeholder="Válassz kötelező képet"><option value="muhely"<?php echo in_array( 'muhely', $service_media_required_saved, true ) ? ' selected' : ''; ?>>Műhely</option><option value="munka-kozben"<?php echo in_array( 'munka-kozben', $service_media_required_saved, true ) ? ' selected' : ''; ?>>Munka közben</option><option value="referencia-munka"<?php echo in_array( 'referencia-munka', $service_media_required_saved, true ) ? ' selected' : ''; ?>>Referencia munka</option><option value="felszereles"<?php echo in_array( 'felszereles', $service_media_required_saved, true ) ? ' selected' : ''; ?>>Felszerelés</option></select></div>
                    <div class="va-form-group"><label>Videó</label><?php $v = $service_meta( 'service_media_video' ); ?><select name="service_media_video" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Ellenőrzött szolgáltató</label><?php $v = $service_meta( 'service_verified_provider' ); ?><select name="service_verified_provider" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Dokumentum ellenőrzés</label><?php $v = $service_meta( 'service_document_verified' ); ?><select name="service_document_verified" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Telefonszám ellenőrzés</label><?php $v = $service_meta( 'service_phone_verified' ); ?><select name="service_phone_verified" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>Vállalkozás ellenőrzés</label><?php $v = $service_meta( 'service_business_verified' ); ?><select name="service_business_verified" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group"><label>SOS szolgáltatás</label><?php $v = $service_meta( 'service_sos_service' ); ?><select name="service_sos_service" class="va-select"><option value="">– Válasszon –</option><option value="igen"<?php selected( $v, 'igen' ); ?>>Igen</option><option value="nem"<?php selected( $v, 'nem' ); ?>>Nem</option></select></div>
                    <div class="va-form-group" style="grid-column:1 / -1;"><label>Moderációs flag</label><select name="service_moderation_flags[]" class="va-select" multiple data-placeholder="Kockázatos jelölések"><option value="illegalis-fegyverjavitas"<?php echo in_array( 'illegalis-fegyverjavitas', $service_moderation_saved, true ) ? ' selected' : ''; ?>>Illegális fegyverjavítás</option><option value="engedely-nelkuli-szolgaltatas"<?php echo in_array( 'engedely-nelkuli-szolgaltatas', $service_moderation_saved, true ) ? ' selected' : ''; ?>>Engedély nélküli szolgáltatás</option><option value="tiltott-vadaszati-tevekenyseg"<?php echo in_array( 'tiltott-vadaszati-tevekenyseg', $service_moderation_saved, true ) ? ' selected' : ''; ?>>Tiltott vadászati tevékenység</option></select></div>
                </div>
            </div>
            <?php endif; ?>

        <?php
            $ev = $edit_meta;
            $drive_opts     = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_drive_options() : [];
            $vcond_opts     = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_vehicle_condition_options() : [];
            $doc_type_opts  = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_doc_type_options() : [];
            $doc_val_opts   = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_doc_validity_options() : [];
            $ac_opts        = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_ac_type_options() : [];
            $eco_opts       = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_eco_class_options() : [];
            $cyl_opts       = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_cylinder_layout_options() : [];
            $extras_by_grp  = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_by_group() : [];
            $extras_opts    = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_options() : [];
            $roof_opts      = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_roof_type_options() : [];
            $ev_extras      = is_array( $ev['extras'] ?? null ) ? $ev['extras'] : [];

            // Helper: select mező
            $render_select = function( string $name, array $options, string $saved ) {
                echo '<select name="' . esc_attr( $name ) . '" class="va-select">';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $options as $k => $l ) {
                    echo '<option value="' . esc_attr( $k ) . '"' . selected( $saved, $k, false ) . '>' . esc_html( $l ) . '</option>';
                }
                echo '</select>';
            };
        ?>
        <style>
        .va-vehicle-only { display:none; }
        .va-vehicle-specs { margin-top:28px; }
        .va-vehicle-specs h3.va-specs-heading { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.5);margin:22px 0 12px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.08); }
        .va-specs-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
        @media(max-width:600px){ .va-specs-grid { grid-template-columns:1fr; } }
        .va-specs-grid .va-form-group { margin-bottom:0; }
        .va-extras-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:8px; }
        @media(max-width:600px){ .va-extras-grid { grid-template-columns:1fr 1fr; } }
        .va-extra-check { display:flex;align-items:center;gap:6px;font-size:13px;color:#fff;cursor:pointer;padding:6px 8px;border:1px solid rgba(255,255,255,.1);border-radius:6px;transition:border-color .15s,background .15s; }
        .va-extra-check:has(input:checked) { border-color:rgba(255,60,60,.5);background:rgba(255,60,60,.07); }
        .va-extra-check input { accent-color:#ff3030;flex-shrink:0; }
        .va-vehicle-specs + button, .va-vehicle-specs ~ button { margin-top:20px; }
        .va-dog-fields-wrap { margin-bottom: 14px; }
        .va-dog-fields-wrap .va-dog-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
        .va-dog-fields-wrap .va-dog-row--single { grid-template-columns:1fr; margin-bottom:0; }
        @media (max-width:760px){ .va-dog-fields-wrap .va-dog-row { grid-template-columns:1fr; } }
        .va-dark-date { position:relative; }
        .va-dark-date .va-date-display { padding-right:46px; cursor:pointer; }
        .va-date-btn { position:absolute; top:50%; right:10px; transform:translateY(-50%); width:28px; height:28px; border-radius:8px; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.04); color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; }
        .va-date-btn:hover { border-color:rgba(255,0,0,.35); background:rgba(255,0,0,.12); }
        .va-dark-calendar { position:absolute; top:calc(100% + 8px); left:0; width:100%; min-width:280px; border:1px solid rgba(255,255,255,.14); border-radius:12px; background:linear-gradient(180deg,#111,#080808); box-shadow:0 18px 40px rgba(0,0,0,.7); z-index:10020; padding:10px; }
        .va-dark-calendar__head { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
        .va-dark-calendar__nav { width:28px; height:28px; border-radius:8px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.04); color:#fff; cursor:pointer; font-size:18px; line-height:1; }
        .va-dark-calendar__nav:hover { border-color:rgba(255,0,0,.35); background:rgba(255,0,0,.12); }
        .va-dark-calendar__title { font-size:13px; font-weight:700; color:#fff; }
        .va-dark-calendar__dow, .va-dark-calendar__grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
        .va-dark-calendar__dow span { text-align:center; font-size:10px; color:rgba(255,255,255,.45); padding:2px 0; }
        .va-dark-calendar__day { height:28px; border-radius:7px; border:1px solid transparent; background:transparent; color:#fff; font-size:12px; cursor:pointer; }
        .va-dark-calendar__day:hover { border-color:rgba(255,0,0,.35); background:rgba(255,0,0,.14); }
        .va-dark-calendar__day--muted { color:rgba(255,255,255,.28); }
        .va-dark-calendar__day--selected { border-color:rgba(255,0,0,.55); background:rgba(255,0,0,.22); }
        .va-dark-calendar__day--today { box-shadow:0 0 0 1px rgba(255,255,255,.35) inset; }
        .va-dark-calendar__foot { display:flex; justify-content:flex-start; margin-top:8px; }
        .va-dark-calendar__clear { border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.04); color:#fff; border-radius:8px; height:30px; padding:0 12px; cursor:pointer; }
        .va-dark-calendar__clear:hover { border-color:rgba(255,0,0,.35); background:rgba(255,0,0,.12); }
        </style>
        <div class="va-vehicle-only">
        <div class="va-vehicle-specs">
            <h3 class="va-specs-heading">⚙️ Motor / Hajtástechnika</h3>
            <div class="va-specs-grid">
                <div class="va-form-group">
                    <label>Futásteljesítmény (km)</label>
                    <input type="number" name="mileage" class="va-input" min="0" placeholder="pl. 125000" value="<?php echo esc_attr( (string)( $ev['mileage'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Üzemanyag</label>
                    <?php $render_select( 'fuel_type', [ 'benzin'=>'Benzin','diesel'=>'Dízel','hybrid'=>'Hibrid','electric'=>'Elektromos','lpg'=>'LPG','cng'=>'CNG','egyeb'=>'Egyéb' ], (string)( $ev['fuel_type'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Hengerűrtartalom (cm³)</label>
                    <input type="number" name="engine_size" class="va-input" min="0" placeholder="pl. 1598" value="<?php echo esc_attr( (string)( $ev['engine_size'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Teljesítmény (kW)</label>
                    <input type="number" name="performance_kw" class="va-input" min="0" placeholder="pl. 85" value="<?php echo esc_attr( (string)( $ev['performance_kw'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Sebességváltó</label>
                    <?php $render_select( 'transmission', [ 'manual'=>'Kéziváltó','automatic'=>'Automata','semi_auto'=>'Félautomata','cvt'=>'CVT','egyeb'=>'Egyéb' ], (string)( $ev['transmission'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Hajtás</label>
                    <?php $render_select( 'drive', $drive_opts, (string)( $ev['drive'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Henger-elrendezés</label>
                    <?php $render_select( 'cylinder_layout', $cyl_opts, (string)( $ev['cylinder_layout'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Saját tömeg (kg)</label>
                    <input type="number" name="own_weight" class="va-input" min="0" placeholder="pl. 1450" value="<?php echo esc_attr( (string)( $ev['own_weight'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Össztömeg (kg)</label>
                    <input type="number" name="gross_weight" class="va-input" min="0" placeholder="pl. 1900" value="<?php echo esc_attr( (string)( $ev['gross_weight'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Szállítható személyek száma</label>
                    <input type="number" name="passengers" class="va-input" min="1" max="100" placeholder="pl. 5" value="<?php echo esc_attr( (string)( $ev['passengers'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Csomagtartó (liter)</label>
                    <input type="number" name="trunk_liters" class="va-input" min="0" placeholder="pl. 350" value="<?php echo esc_attr( (string)( $ev['trunk_liters'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                    <label class="va-check-label">
                        <input type="checkbox" name="range_gearbox" value="1"<?php echo ( ( $ev['range_gearbox'] ?? '' ) === '1' ) ? ' checked' : ''; ?>>
                        Felező váltó
                    </label>
                </div>
            </div>

            <h3 class="va-specs-heading">🚘 Karosszéria / Állapot</h3>
            <div class="va-specs-grid">
                <div class="va-form-group">
                    <label>Jármű állapota</label>
                    <?php $render_select( 'vehicle_condition', $vcond_opts, (string)( $ev['vehicle_condition'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Ajtók száma</label>
                    <?php $render_select( 'doors', [ '2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6+' ], (string)( $ev['doors'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Szín</label>
                    <input type="text" name="color" class="va-input" placeholder="pl. Fehér, Fekete..." value="<?php echo esc_attr( (string)( $ev['color'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                    <label class="va-check-label">
                        <input type="checkbox" name="color_metallic" value="1"<?php echo ( ( $ev['color_metallic'] ?? '' ) === '1' ) ? ' checked' : ''; ?>>
                        Metál fényezés
                    </label>
                </div>
                <div class="va-form-group">
                    <label>Tető típusa</label>
                    <?php $render_select( 'roof_type', $roof_opts, (string)( $ev['roof_type'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Klíma</label>
                    <?php $render_select( 'ac_type', $ac_opts, (string)( $ev['ac_type'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Környezetvédelmi osztály</label>
                    <?php $render_select( 'eco_class', $eco_opts, (string)( $ev['eco_class'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Tulajdonosok száma</label>
                    <input type="number" name="owners" class="va-input" min="1" max="20" placeholder="pl. 2" value="<?php echo esc_attr( (string)( $ev['owners'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Kulcsok száma</label>
                    <select name="keys" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php for ( $ki = 1; $ki <= 10; $ki++ ): ?>
                        <option value="<?php echo $ki; ?>"<?php selected( (string)( $ev['keys'] ?? '' ), (string) $ki ); ?>><?php echo $ki; ?> db</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Kárpit színe (1)</label>
                    <input type="text" name="upholstery_1" class="va-input" placeholder="pl. Fekete" value="<?php echo esc_attr( (string)( $ev['upholstery_1'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Kárpit színe (2)</label>
                    <input type="text" name="upholstery_2" class="va-input" placeholder="pl. Szürke" value="<?php echo esc_attr( (string)( $ev['upholstery_2'] ?? '' ) ); ?>">
                </div>
            </div>

            <h3 class="va-specs-heading">📄 Okmányok / Műszaki</h3>
            <div class="va-specs-grid">
                <div class="va-form-group">
                    <label>Okmányok jellege</label>
                    <?php $render_select( 'doc_type', $doc_type_opts, (string)( $ev['doc_type'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Okmányok érvényessége</label>
                    <?php $render_select( 'doc_validity', $doc_val_opts, (string)( $ev['doc_validity'] ?? '' ) ); ?>
                </div>
                <div class="va-form-group">
                    <label>Műszaki vizsga lejár</label>
                    <input type="month" name="tech_inspect" class="va-input" style="background:#0e0e0e!important;color:#fff!important;color-scheme:dark;" value="<?php echo esc_attr( (string)( $ev['tech_inspect'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Első forgalomba helyezés (év.hó)</label>
                    <input type="text" name="first_reg" class="va-input" placeholder="pl. 2019-03" value="<?php echo esc_attr( (string)( $ev['first_reg'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                    <label class="va-check-label">
                        <input type="checkbox" name="previous_damage" value="1"<?php echo ( ( $ev['previous_damage'] ?? '' ) === '1' ) ? ' checked' : ''; ?>>
                        Korábbi kár / baleset
                    </label>
                </div>
                <div class="va-form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                    <label class="va-check-label">
                        <input type="checkbox" name="service_book" value="1"<?php echo ( ( $ev['service_book'] ?? '' ) === '1' ) ? ' checked' : ''; ?>>
                        Szervizkönyv megvan
                    </label>
                </div>
            </div>

            <h3 class="va-specs-heading">🔧 Gumi méretek / Egyéb</h3>
            <div class="va-specs-grid">
                <div class="va-form-group">
                    <label>Nyári gumi (első, pl. 205/55R16)</label>
                    <input type="text" name="summer_tire_front" class="va-input" placeholder="205/55R16" value="<?php echo esc_attr( (string)( $ev['summer_tire_front'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Nyári gumi (hátsó)</label>
                    <input type="text" name="summer_tire_rear" class="va-input" placeholder="205/55R16" value="<?php echo esc_attr( (string)( $ev['summer_tire_rear'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Téli gumi (első)</label>
                    <input type="text" name="winter_tire_front" class="va-input" placeholder="205/55R16" value="<?php echo esc_attr( (string)( $ev['winter_tire_front'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Téli gumi (hátsó)</label>
                    <input type="text" name="winter_tire_rear" class="va-input" placeholder="205/55R16" value="<?php echo esc_attr( (string)( $ev['winter_tire_rear'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Alvázszám (VIN)</label>
                    <input type="text" name="vin" class="va-input" placeholder="17 karakteres VIN" maxlength="17" value="<?php echo esc_attr( (string)( $ev['vin'] ?? '' ) ); ?>">
                </div>
                <div class="va-form-group">
                    <label>Belső azonosító</label>
                    <input type="text" name="internal_id" class="va-input" placeholder="Saját belső azonosító" value="<?php echo esc_attr( (string)( $ev['internal_id'] ?? '' ) ); ?>">
                </div>
            </div>

            <?php foreach ( $extras_by_grp as $grp_key => $grp ): ?>
            <h3 class="va-specs-heading">✅ <?php echo esc_html( $grp['label'] ); ?></h3>
            <div class="va-extras-grid">
                <?php foreach ( $grp['items'] as $ekey => $elabel ):
                    $is_checked = in_array( $ekey, $ev_extras, true );
                ?>
                <label class="va-extra-check">
                    <input type="checkbox" name="extras[]" value="<?php echo esc_attr( $ekey ); ?>"<?php echo $is_checked ? ' checked' : ''; ?>>
                    <?php echo esc_html( $elabel ); ?>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        </div>
        </div><!-- /va-wstep step 2 -->

        <!-- ═══ STEP 3: Ár & Helyszín & Elérhetőség ═══ -->
        <div class="va-wstep" data-step="3">
            <h3 class="va-wstep-title">Ár és elérhetőség</h3>
            <div class="va-form-row">
                <div class="va-form-group">
                    <label>Ár (Ft) <span class="required">*</span></label>
                    <input type="number" name="price" class="va-input" min="0" required placeholder="pl. 150000" value="<?php echo esc_attr((string)($edit_meta['price'] ?? '')); ?>">
                </div>
                <div class="va-form-group">
                    <label>Ár jellege</label>
                    <?php $pt = (string)($edit_meta['price_type'] ?? 'fixed');
                    echo '<select name="price_type" class="va-select">';
                    foreach ( ['fixed'=>'Fix ár','negotiable'=>'Alkudható','free'=>'Ingyenes','on_request'=>'Érdeklődjön'] as $k=>$l ) {
                        echo '<option value="'.esc_attr($k).'"'.selected($pt,$k,false).'>'.esc_html($l).'</option>';
                    }
                    echo '</select>'; ?>
                </div>
            </div>
            <div class="va-form-group">
                <label>Helyszín <span class="required">*</span></label>
                <?php
                $location_val = (string)($edit_meta['location'] ?? '');
                $postal_val   = (string)($edit_meta['postal_code'] ?? '');
                $street_val   = (string)($edit_meta['street'] ?? '');
                echo '<div class="va-loc-grid">';
                echo '<input type="text" name="postal_code" class="va-input" placeholder="Irányítószám (pl. 1051)" value="'.esc_attr($postal_val).'" inputmode="numeric" pattern="[0-9]*">';
                echo '<input type="text" name="location" class="va-input" list="va-city-list" autocomplete="off" placeholder="Város / Helység" value="'.esc_attr($location_val).'">';
                echo '<input type="text" name="street" class="va-input" list="va-street-list" autocomplete="off" placeholder="Cím / utca, házszám" value="'.esc_attr($street_val).'">';
                echo '<datalist id="va-city-list"></datalist>';
                echo '<datalist id="va-street-list"></datalist>';
                echo '<small class="va-help">Város vagy irányítószám megadása kötelező.</small>';
                echo '</div>';
                ?>
            </div>
            <?php if ( VA_Form_Builder::is_enabled( $fb_form, 'phone' ) ):
                $phone_field = VA_Form_Builder::get_field( $fb_form, 'phone' );
                $phone_req   = ! empty( $phone_field['required'] );
                $phone_ph    = esc_attr( (string)( $phone_field['placeholder'] ?? '+36 30 000 0000' ) );
            ?>
            <div class="va-form-group">
                <label>Telefonszám<?php echo $phone_req ? ' <span class="required">*</span>' : ''; ?></label>
                <input type="tel" name="phone" class="va-input" placeholder="<?php echo $phone_ph; ?>"<?php echo $phone_req ? ' required' : ''; ?> style="background:#0e0e0e!important;color:#fff!important;color-scheme:dark;" value="<?php echo esc_attr((string)($edit_meta['phone'] ?? '')); ?>">
            </div>
            <?php endif; ?>
            <?php if ( VA_Form_Builder::is_enabled( $fb_form, 'email_show' ) ): ?>
            <div class="va-form-group">
                <label class="va-check-label">
                    <input type="checkbox" name="email_show" value="1" checked onclick="return false;">
                    E-mail cím megjelenítése a hirdetésben
                </label>
            </div>
            <?php endif; ?>
        </div><!-- /va-wstep step 3 -->

        <!-- ═══ STEP 4: Leírás & Képek ═══ -->
        <div class="va-wstep" data-step="4">
            <h3 class="va-wstep-title">Leírás és képek</h3>
            <div class="va-form-group">
                <label>Leírás</label>
                <div id="va-quill-editor"></div>
                <textarea name="description" id="va-desc-hidden" style="display:none"><?php echo esc_textarea( $desc_val ); ?></textarea>
                <style>
                .ql-toolbar.ql-snow{background:#1e1e1e;border:1px solid rgba(255,255,255,.15)!important;border-bottom:none!important;border-radius:6px 6px 0 0;}
                .ql-container.ql-snow{background:#111;border:1px solid rgba(255,255,255,.15)!important;border-radius:0 0 6px 6px;font-size:15px;}
                .ql-editor{color:#fff!important;min-height:160px;line-height:1.7;font-family:system-ui,sans-serif;}
                .ql-editor p,.ql-editor span,.ql-editor li,.ql-editor strong,.ql-editor em,.ql-editor u,.ql-editor s{color:#fff!important;}
                .ql-editor.ql-blank::before{color:#9a9a9a!important;font-style:normal;}
                .ql-snow .ql-stroke{stroke:#aaa!important;}.ql-snow .ql-fill,.ql-snow .ql-stroke.ql-fill{fill:#aaa!important;}
                .ql-snow .ql-picker{color:#bbb!important;}.ql-snow .ql-picker-label{border-color:rgba(255,255,255,.15)!important;}
                .ql-snow .ql-picker-options{background:#1e1e1e!important;border-color:rgba(255,255,255,.15)!important;}
                .ql-snow .ql-picker-item{color:#bbb!important;}.ql-snow .ql-picker-item:hover,.ql-snow .ql-picker-item.ql-selected{color:#fff!important;}
                .ql-snow.ql-toolbar button:hover .ql-stroke,.ql-snow .ql-toolbar button:hover .ql-stroke{stroke:#ff4444!important;}
                .ql-snow.ql-toolbar button.ql-active .ql-stroke,.ql-snow .ql-toolbar button.ql-active .ql-stroke{stroke:#ff4444!important;}
                .ql-snow.ql-toolbar button:hover .ql-fill,.ql-snow .ql-toolbar button:hover .ql-fill{fill:#ff4444!important;}
                .ql-snow.ql-toolbar button.ql-active .ql-fill{fill:#ff4444!important;}
                .ql-snow .ql-picker.ql-header .ql-picker-label::before,.ql-snow .ql-picker.ql-header .ql-picker-item::before{color:#bbb!important;}
                .ql-editor a{color:#ff4444;}.ql-editor img{max-width:100%;border-radius:6px;}
                .ql-editor blockquote{border-left:3px solid #ff4444;padding-left:12px;color:#aaa;margin:8px 0;}
                .ql-editor h2,.ql-editor h3{color:#e8e8e8;}.ql-editor ol,.ql-editor ul{color:#e8e8e8;}
                .ql-snow .ql-tooltip{background:#1e1e1e!important;border-color:rgba(255,255,255,.15)!important;color:#e8e8e8!important;box-shadow:0 4px 20px rgba(0,0,0,.5)!important;}
                .ql-snow .ql-tooltip input[type=text]{background:#111!important;border-color:rgba(255,255,255,.2)!important;color:#e8e8e8!important;}
                .ql-snow .ql-tooltip a.ql-action,.ql-snow .ql-tooltip a.ql-remove{color:#ff4444!important;}
                </style>
            </div>
            <div class="va-form-group">
                <label>Képek (max <?php echo (int)$max_img; ?> db)</label>
                <div class="va-img-picker" id="va-img-picker">
                    <div class="va-img-grid" id="va-img-grid">
                        <button type="button" class="va-img-add" id="va-img-add">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Képek<br>hozzáadása</span>
                        </button>
                    </div>
                    <input type="file" id="va-img-file-input" accept="image/jpeg,image/png,image/webp" multiple style="display:none" data-max="<?php echo esc_attr((string)$max_img); ?>">
                    <input type="hidden" name="featured_image_index" id="va-featured-index" value="0">
                    <input type="hidden" name="keep_images" id="va-keep-images" value="">
                    <p class="va-img-hint">Húzd a képeket az átrendezéshez &bull; &#9733; = borítókép beállítása</p>
                </div>
            </div>
            <div id="va-submit-notice-modal" role="dialog" aria-modal="true" aria-labelledby="va-submit-notice-title">
                <div class="va-submit-notice-card" role="presentation">
                    <h4 id="va-submit-notice-title" style="margin:0 0 12px; color:#fff; font-size:20px; font-weight:800;">Értesítés</h4>
                    <div id="va-submit-notice-content"></div>
                </div>
            </div>
            <p class="va-wiz-publish-note">
                <?php echo get_option('va_auto_publish_listings','0') === '1'
                    ? 'A hirdetés azonnal megjelenik.'
                    : 'A hirdetés moderátor jóváhagyása után jelenik meg.'; ?>
            </p>
        </div><!-- /va-wstep step 4 -->

    </form>
            </div><!-- .va-wizard-body -->
        <!-- Wizard navigációs lábléc -->
        <div class="va-wizard-footer">
            <button type="button" class="va-btn va-btn--ghost" id="va-wizard-prev" style="display:none">← Vissza</button>
            <span class="va-wiz-foot-label" id="va-wiz-label">1 / 4</span>
            <button type="button" class="va-btn va-btn--primary" id="va-wizard-next">Tovább →</button>
            <button type="submit" form="va-submit-form" class="va-btn va-btn--primary" id="va-submit-btn" style="display:none"><?php echo $edit_mode ? '💾 Mentés' : '📤 Feladás'; ?></button>
        </div>
        </div><!-- .va-wizard-main -->
    </div><!-- .va-wizard-shell -->

    <div class="va-other-cat-modal" id="va-other-cat-modal" aria-hidden="true">
        <div class="va-other-cat-card" role="dialog" aria-modal="true" aria-labelledby="va-other-cat-title">
            <h4 id="va-other-cat-title">Egyéb kategória megadása</h4>
            <p>Írd be pontosan, milyen kategóriát szeretnél megadni.</p>
            <input type="text" id="va-other-cat-input" class="va-input" maxlength="80" placeholder="pl. Vadász kiegészítő egyéb">
            <div class="va-other-cat-actions">
                <button type="button" class="va-btn va-btn--ghost" id="va-other-cat-cancel">Mégse</button>
                <button type="button" class="va-btn va-btn--primary" id="va-other-cat-save">Mentés</button>
            </div>
        </div>
    </div>

    <div class="va-year-modal" id="va-year-modal" aria-hidden="true">
        <div class="va-year-card" role="dialog" aria-modal="true" aria-labelledby="va-year-title">
            <div class="va-year-head">
                <h4 id="va-year-title">Gyártási év</h4>
                <div class="va-year-nav">
                    <button type="button" class="va-year-nav-btn" id="va-year-prev" aria-label="Előző">←</button>
                    <span id="va-year-range">-</span>
                    <button type="button" class="va-year-nav-btn" id="va-year-next" aria-label="Következő">→</button>
                </div>
            </div>
            <div class="va-year-grid" id="va-year-grid"></div>
            <div class="va-year-actions">
                <button type="button" class="va-btn va-btn--ghost" id="va-year-cancel">Mégse</button>
                <button type="button" class="va-btn va-btn--primary" id="va-year-now">Mai év</button>
            </div>
        </div>
    </div>

    <div class="va-year-modal" id="va-shoe-size-modal" aria-hidden="true">
        <div class="va-year-card" role="dialog" aria-modal="true" aria-labelledby="va-shoe-size-title">
            <div class="va-year-head">
                <h4 id="va-shoe-size-title">Cipőméret táblázat (EU)</h4>
            </div>
            <div class="va-shoe-size-grid" id="va-shoe-size-grid"></div>
            <div class="va-year-actions">
                <button type="button" class="va-btn va-btn--ghost" id="va-shoe-size-cancel">Mégse</button>
            </div>
        </div>
    </div>

    <div class="va-year-modal" id="va-popup-select-modal" aria-hidden="true">
        <div class="va-year-card va-popup-select-card" role="dialog" aria-modal="true" aria-labelledby="va-popup-select-title">
            <div class="va-popup-select-head">
                <h4 id="va-popup-select-title">Választás</h4>
                <button type="button" class="va-year-nav-btn" id="va-popup-select-close" aria-label="Bezárás">×</button>
            </div>
            <input type="search" id="va-popup-select-search" class="va-input va-popup-select-search" placeholder="Keresés a listában vagy írja be a típust" autocomplete="off">
            <div class="va-popup-select-list" id="va-popup-select-list"></div>
            <div class="va-year-actions">
                <button type="button" class="va-btn va-btn--ghost" id="va-popup-select-add">+ Hozzáadás</button>
                <button type="button" class="va-btn va-btn--ghost" id="va-popup-select-cancel">Mégse</button>
            </div>
        </div>
    </div>

</div><!-- .va-submit-preview-shell -->

<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
(function($){
    $('body').addClass('va-page-modal-open');

    /* ══ Custom dark datalist UI (native fehér lenyíló helyett) ══ */
    (function initCustomDatalistUI(){
        var $panel = $('<div class="va-datalist-panel" id="va-datalist-panel" aria-hidden="true"></div>').appendTo('body');
        var $activeInput = $();
        var activeIndex = -1;

        $('input[list]').each(function(){
            var $inp = $(this);
            var listId = ($inp.attr('list') || '') + '';
            if (!listId) return;
            $inp.data('vaListId', listId).addClass('va-has-custom-list');
            $inp.removeAttr('list');
        });

        function escapeHtml(v) {
            return $('<div>').text(v == null ? '' : String(v)).html();
        }

        function listValues($input) {
            var listId = ($input.data('vaListId') || '') + '';
            if (!listId) return [];
            var out = [];
            var seen = {};
            $('#' + listId + ' option').each(function(){
                var v = (($(this).attr('value') || '') + '').trim();
                if (!v) return;
                var key = v.toLowerCase();
                if (!seen[key]) {
                    seen[key] = true;
                    out.push(v);
                }
            });
            return out;
        }

        function placePanel($input) {
            if (!$input.length) return;
            var rect = $input[0].getBoundingClientRect();
            $panel.css({
                width: rect.width + 'px',
                left: Math.round(rect.left) + 'px',
                top: Math.round(rect.bottom + 6) + 'px'
            });
        }

        function closePanel() {
            $panel.hide().attr('aria-hidden', 'true').empty();
            $activeInput = $();
            activeIndex = -1;
        }

        function renderPanel($input) {
            var query = (($input.val() || '') + '').toLowerCase();
            var listId = (($input.data('vaListId') || '') + '').toLowerCase();
            var maxItems = listId === 'va-brand-list' ? 2000 : 400;
            var items = listValues($input).filter(function(v){
                return !query || v.toLowerCase().indexOf(query) !== -1;
            }).slice(0, maxItems);

            if (!items.length) {
                closePanel();
                return;
            }

            var html = '';
            items.forEach(function(v, i){
                html += '<button type="button" class="va-datalist-item' + (i === 0 ? ' is-active' : '') + '" data-value="' + escapeHtml(v) + '">' + escapeHtml(v) + '</button>';
            });

            $panel.html(html).show().attr('aria-hidden', 'false');
            placePanel($input);
            $activeInput = $input;
            activeIndex = 0;
        }

        function moveActive(delta) {
            var $items = $panel.find('.va-datalist-item');
            if (!$items.length) return;
            activeIndex = Math.max(0, Math.min($items.length - 1, activeIndex + delta));
            $items.removeClass('is-active');
            var $target = $items.eq(activeIndex).addClass('is-active');
            var panel = $panel[0];
            var node = $target[0];
            if (panel && node) {
                var t = node.offsetTop;
                var b = t + node.offsetHeight;
                if (t < panel.scrollTop) panel.scrollTop = t;
                else if (b > panel.scrollTop + panel.clientHeight) panel.scrollTop = b - panel.clientHeight;
            }
        }

        $(document).on('focus input click', 'input.va-has-custom-list, input[list]', function(){
            var $inp = $(this);
            var listId = ($inp.attr('list') || '') + '';
            if (listId && !$inp.data('vaListId')) {
                $inp.data('vaListId', listId);
            }
            $inp.addClass('va-has-custom-list');
            $inp.removeAttr('list');
            renderPanel($inp);
        });

        $(document).on('keydown', 'input.va-has-custom-list', function(e){
            if (!$panel.is(':visible')) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); moveActive(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); moveActive(-1); }
            else if (e.key === 'Enter') {
                var $a = $panel.find('.va-datalist-item.is-active').first();
                if ($a.length) {
                    e.preventDefault();
                    $(this).val($a.data('value')).trigger('change').trigger('input');
                    closePanel();
                }
            } else if (e.key === 'Escape') {
                closePanel();
            }
        });

        $panel.on('mousedown', '.va-datalist-item', function(e){
            e.preventDefault();
            var value = (($(this).data('value') || '') + '').trim();
            if ($activeInput.length) {
                $activeInput.val(value).trigger('change').trigger('input').focus();
            }
            closePanel();
        });

        $(document).on('mousedown', function(e){
            if (!$(e.target).closest('#va-datalist-panel, input.va-has-custom-list, input[list]').length) {
                closePanel();
            }
        });

        $(window).on('resize scroll', function(){
            if ($panel.is(':visible') && $activeInput.length) {
                placePanel($activeInput);
            }
        });
    })();

    /* ══ Gyártási év popup picker ════════════════════════ */
    (function initYearPicker(){
        var $input = $('#va-year-input');
        var $modal = $('#va-year-modal');
        if (!$input.length || !$modal.length) return;

        var minYear = 1800;
        var currentYear = (new Date()).getFullYear();
        var $range = $('#va-year-range');
        var $grid = $('#va-year-grid');
        var viewStart = currentYear - 11;

        function selectedYear() {
            var v = parseInt((($input.val() || '') + '').replace(/\D+/g, ''), 10);
            if (isNaN(v) || v < minYear || v > currentYear) return null;
            return v;
        }

        function renderYears() {
            var end = Math.min(currentYear, viewStart + 11);
            $range.text(viewStart + ' - ' + end);
            var selected = selectedYear();
            var html = '';
            for (var y = viewStart; y <= end; y++) {
                var cls = 'va-year-item' + (selected === y ? ' is-selected' : '');
                html += '<button type="button" class="' + cls + '" data-year="' + y + '">' + y + '</button>';
            }
            $grid.html(html);
        }

        function openYearPicker() {
            var sel = selectedYear();
            if (sel) viewStart = Math.max(minYear, sel - 5);
            $modal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').addClass('va-modal-open');
            renderYears();
        }

        function closeYearPicker() {
            $modal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').removeClass('va-modal-open');
        }

        $('#va-year-open').on('click', openYearPicker);

        $('#va-year-prev').on('click', function(){
            viewStart = Math.max(minYear, viewStart - 12);
            renderYears();
        });

        $('#va-year-next').on('click', function(){
            viewStart = Math.min(currentYear - 11, viewStart + 12);
            renderYears();
        });

        $grid.on('click', '.va-year-item', function(){
            var y = parseInt($(this).data('year'), 10);
            if (!isNaN(y)) {
                $input.val(String(y)).trigger('input').trigger('change');
            }
            closeYearPicker();
        });

        $('#va-year-now').on('click', function(){
            $input.val(String(currentYear)).trigger('input').trigger('change');
            closeYearPicker();
        });

        $('#va-year-cancel').on('click', closeYearPicker);

        $modal.on('click', function(e){
            if (e.target === this) closeYearPicker();
        });

        $(document).on('keydown', function(e){
            if (e.key === 'Escape' && $modal.hasClass('is-open')) closeYearPicker();
        });
    })();

    /* ══ Cipőméret popup picker ════════════════════════ */
    (function initShoeSizePicker(){
        var $input = $('#va-shoe-size-input');
        var $euInput = $('[name="shoe_eu_size"]');
        var $modal = $('#va-shoe-size-modal');
        var $grid = $('#va-shoe-size-grid');
        if (!$input.length || !$modal.length || !$grid.length) return;

        var sizes = ['EU 36','EU 37','EU 38','EU 39','EU 40','EU 41','EU 42','EU 43','EU 44','EU 45','EU 46','EU 47','EU 48','EU 49','EU 50','EU 50+'];

        function renderSizes() {
            var selected = (($input.val() || '') + '').trim();
            var html = '';
            sizes.forEach(function(size){
                var cls = 'va-shoe-size-item' + (selected === size ? ' is-selected' : '');
                html += '<button type="button" class="' + cls + '" data-size="' + size + '">' + size + '</button>';
            });
            $grid.html(html);
        }

        function openShoeModal() {
            renderSizes();
            $modal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').addClass('va-modal-open');
        }

        function closeShoeModal() {
            $modal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').removeClass('va-modal-open');
        }

        $('#va-shoe-size-open').on('click', openShoeModal);
        $('#va-shoe-size-cancel').on('click', closeShoeModal);

        $grid.on('click', '.va-shoe-size-item', function(){
            var size = (($(this).data('size') || '') + '').trim();
            if (size) {
                $input.val(size).trigger('input').trigger('change');
                if ($euInput.length) {
                    $euInput.val(size).trigger('input').trigger('change');
                }
            }
            closeShoeModal();
        });

        $modal.on('click', function(e){
            if (e.target === this) closeShoeModal();
        });

        $(document).on('keydown', function(e){
            if (e.key === 'Escape' && $modal.hasClass('is-open')) closeShoeModal();
        });
    })();

    /* ══ Natív select helyett popup választó ═══════════ */
    var $popupSelectModal = $('#va-popup-select-modal');
    var $popupSelectTitle = $('#va-popup-select-title');
    var $popupSelectSearch = $('#va-popup-select-search');
    var $popupSelectList = $('#va-popup-select-list');
    var $activePopupSelect = $();
    var activePopupIsMultiple = false;
    var activePopupMultiValues = [];

    function normalizePopupSelectText(value) {
        return ((value || '') + '').replace(/\s+/g, ' ').trim();
    }

    function getPopupSelectPlaceholder($select) {
        var attrPlaceholder = normalizePopupSelectText($select.attr('data-placeholder'));
        if (attrPlaceholder) return attrPlaceholder;

        var $firstOption = $select.find('option').first();
        return normalizePopupSelectText($firstOption.text()) || 'Válassz';
    }

    function getPopupSelectButtonLabel($select) {
        if ($select && $select.length && $select.prop('multiple')) {
            var selectedTexts = [];
            $select.find('option:selected').each(function(){
                var txt = normalizePopupSelectText($(this).text());
                if (txt) selectedTexts.push(txt);
            });
            if (!selectedTexts.length) return getPopupSelectPlaceholder($select);
            return selectedTexts.join(', ');
        }

        var $selected = $select.find('option:selected').first();
        var rawValue = normalizePopupSelectText($select.val());
        var selectedValue = normalizePopupSelectText($selected.val());
        var selectedText = normalizePopupSelectText($selected.text());
        if (selectedValue && selectedText) return selectedText;
        if (rawValue) return rawValue;
        return getPopupSelectPlaceholder($select);
    }

    function setPopupSelectValue($select, value) {
        var customValue = normalizePopupSelectText(value);
        if (!$select || !$select.length || !customValue) return;

        var domSelect = $select.get(0);
        var hasOption = false;

        if (domSelect && domSelect.options) {
            for (var i = 0; i < domSelect.options.length; i++) {
                if (normalizePopupSelectText(domSelect.options[i].value) === customValue) {
                    hasOption = true;
                    break;
                }
            }
        }

        if (!hasOption && domSelect) {
            domSelect.add(new Option(customValue, customValue, false, false));
        }

        if ($select.prop('multiple')) {
            var currentValues = $select.val();
            if (!Array.isArray(currentValues)) currentValues = currentValues ? [currentValues] : [];
            var alreadySelected = false;
            for (var j = 0; j < currentValues.length; j++) {
                if (normalizePopupSelectText(currentValues[j]) === customValue) {
                    alreadySelected = true;
                    break;
                }
            }
            if (!alreadySelected) currentValues.push(customValue);
            $select.val(currentValues);
        } else {
            $select.val(customValue);
        }
        $select.trigger('input').trigger('change');
    }

    function getPopupSelectSelectedValues($select) {
        if (!$select || !$select.length) return [];
        var raw = $select.val();
        if (Array.isArray(raw)) {
            return raw.map(function(v){ return normalizePopupSelectText(v); }).filter(function(v){ return v !== ''; });
        }
        var one = normalizePopupSelectText(raw);
        return one ? [one] : [];
    }

    function setPopupSelectSelectedValues($select, values) {
        if (!$select || !$select.length) return;
        var list = Array.isArray(values) ? values : [];
        var cleaned = [];
        var seen = {};
        list.forEach(function(v){
            var n = normalizePopupSelectText(v);
            if (!n || seen[n]) return;
            seen[n] = true;
            cleaned.push(n);
        });

        if ($select.prop('multiple')) {
            $select.val(cleaned);
        } else {
            $select.val(cleaned.length ? cleaned[0] : '');
        }
        $select.trigger('input').trigger('change');
    }

    function getPopupSelectFieldLabel($select) {
        var $label = $select.closest('.va-form-group, .va-telescope-field').find('label').first().clone();
        if ($label.length) {
            $label.find('.required').remove();
            var labelText = normalizePopupSelectText($label.text());
            if (labelText) return labelText;
        }
        return getPopupSelectPlaceholder($select) || 'Választás';
    }

    function teardownPopupSelect($select) {
        if (!$select || !$select.length) return;
        var $button = $select.data('vaPopupButton');
        if ($button && $button.length) {
            $button.remove();
        }
        $select.removeData('vaPopupButton');
        $select.removeData('vaPopupReady');
        $select.off('.vaPopupSelect');
        $select.removeClass('va-popup-select-native');
    }

    function syncPopupSelectButton($select) {
        var $button = $select.data('vaPopupButton');
        if (!$button || !$button.length) return;

        var hasValue = getPopupSelectSelectedValues($select).length > 0;
        $button.find('.va-popup-select-trigger__value').text(getPopupSelectButtonLabel($select));
        $button.toggleClass('is-placeholder', !hasValue);
        $button.prop('disabled', $select.is(':disabled'));
        $button.attr('aria-expanded', 'false');
    }

    function renderPopupSelectOptions(query) {
        if (!$activePopupSelect.length) return;

        var selectedValues = activePopupIsMultiple ? activePopupMultiValues.slice() : getPopupSelectSelectedValues($activePopupSelect);
        var filter = normalizePopupSelectText(query).toLocaleLowerCase('hu-HU');
        var visibleCount = 0;

        $popupSelectList.empty();

        $activePopupSelect.find('option').each(function(){
            var $option = $(this);
            if ($option.is(':disabled')) return;

            var value = (($option.attr('value') || '') + '').trim();
            var text = normalizePopupSelectText($option.text());
            var searchableText = text.toLocaleLowerCase('hu-HU');
            if (filter && searchableText.indexOf(filter) === -1) return;

            visibleCount++;

            var isSelected = selectedValues.indexOf(value) !== -1;

            var $item = $('<button>', {
                type: 'button',
                class: 'va-popup-select-item' + (isSelected ? ' is-selected' : ''),
                'data-value': value,
                'aria-pressed': isSelected ? 'true' : 'false'
            });

            $('<span>', {
                class: 'va-popup-select-item__label',
                text: text || 'Üres érték'
            }).appendTo($item);

            if (value === '') {
                $('<span>', {
                    class: 'va-popup-select-item__meta',
                    text: 'Nincs kiválasztva'
                }).appendTo($item);
            }

            $popupSelectList.append($item);
        });

        if (!visibleCount) {
            $('<div>', {
                class: 'va-popup-select-empty',
                text: 'Nincs találat a megadott szűrésre.'
            }).appendTo($popupSelectList);
        }
    }

    function openPopupSelect($select) {
        if (!$select.length || $select.is(':disabled')) return;

        $activePopupSelect = $select;
        activePopupIsMultiple = !!$select.prop('multiple');
        activePopupMultiValues = getPopupSelectSelectedValues($select);
        $popupSelectTitle.text(getPopupSelectFieldLabel($select));
        $popupSelectSearch.val('');
        renderPopupSelectOptions('');
        $popupSelectModal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('va-modal-open');

        var $button = $select.data('vaPopupButton');
        if ($button && $button.length) {
            $button.attr('aria-expanded', 'true');
        }

        setTimeout(function(){
            $popupSelectSearch.trigger('focus');
        }, 20);
    }

    function closePopupSelect() {
        if ($activePopupSelect.length) {
            var $button = $activePopupSelect.data('vaPopupButton');
            if ($button && $button.length) {
                $button.attr('aria-expanded', 'false').trigger('focus');
            }
        }

        $activePopupSelect = $();
        activePopupIsMultiple = false;
        activePopupMultiValues = [];
        $popupSelectModal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('va-modal-open');
    }

    function enhancePopupSelects(context) {
        var $scope = context ? $(context) : $(document);
        $scope.find('#va-submit-form select, select#va-brand').each(function(){
            var $select = $(this);
            if (!$select.closest('#va-submit-form').length) return;
            if ($select.closest('#va-popup-select-modal').length) return;
            if ($select.data('vaPopupReady')) {
                syncPopupSelectButton($select);
                return;
            }

            var $button = $('<button type="button" class="va-popup-select-trigger" aria-haspopup="dialog" aria-expanded="false"><span class="va-popup-select-trigger__value"></span><span class="va-popup-select-trigger__icon">▾</span></button>');
            $select.addClass('va-popup-select-native');
            $select.data('vaPopupReady', true);
            $select.data('vaPopupButton', $button);
            $select.after($button);

            $button.on('click', function(){
                openPopupSelect($select);
            });

            $select.on('change.vaPopupSelect input.vaPopupSelect', function(){
                syncPopupSelectButton($select);
            });

            syncPopupSelectButton($select);
        });
    }

    $popupSelectSearch.on('input', function(){
        renderPopupSelectOptions($(this).val());
    });

    $popupSelectSearch.on('keypress', function(e){
        if (e.key === 'Enter' && !$activePopupSelect.length) return;
        if (e.key === 'Enter') {
            var customValue = (($popupSelectSearch.val() || '') + '').trim();
            if (customValue) {
                console.log('[VA] Enter key: setting custom value', customValue);
                setPopupSelectValue($activePopupSelect, customValue);
                closePopupSelect();
            }
        }
    });

    $popupSelectList.on('click', '.va-popup-select-item', function(){
        if (!$activePopupSelect.length) return;
        var value = (($(this).data('value') || '') + '').trim();
        if (activePopupIsMultiple) {
            var idx = activePopupMultiValues.indexOf(value);
            if (idx === -1) activePopupMultiValues.push(value);
            else activePopupMultiValues.splice(idx, 1);
            renderPopupSelectOptions($popupSelectSearch.val());
            return;
        }
        $activePopupSelect.val(value).trigger('input').trigger('change');
        closePopupSelect();
    });

    $('#va-popup-select-add').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        if (!$activePopupSelect.length) {
            console.error('[VA] Add button: no active select');
            return;
        }
        var customValue = (($popupSelectSearch.val() || '') + '').trim();
        if (activePopupIsMultiple) {
            if (customValue) {
                setPopupSelectValue($activePopupSelect, customValue);
                activePopupMultiValues = getPopupSelectSelectedValues($activePopupSelect);
            }
            setPopupSelectSelectedValues($activePopupSelect, activePopupMultiValues);
            closePopupSelect();
            return;
        }

        if (!customValue) {
            alert('Kérlek írj be egy értéket!');
            return;
        }
        setPopupSelectValue($activePopupSelect, customValue);
        setTimeout(closePopupSelect, 100);
    });

    $('#va-popup-select-close, #va-popup-select-cancel').on('click', closePopupSelect);

    $popupSelectModal.on('click', function(e){
        if (e.target === this) closePopupSelect();
    });

    $(document).on('keydown', function(e){
        if (e.key === 'Escape' && $popupSelectModal.hasClass('is-open')) {
            closePopupSelect();
        }
    });

    enhancePopupSelects();

    /* ══ Wizard navigáció ════════════════════════════════ */
    var _wStep   = 1;
    var _wTotal  = 4;
    var _wLabels = ['Kategória', 'Termék adatai', 'Ár & Helyszín', 'Leírás & Képek'];

    function wizGoTo(step) {
        if (step > _wStep && !wizValidate(_wStep)) return;
        $('.va-wstep').removeClass('is-active');
        $('.va-wstep[data-step="' + step + '"]').addClass('is-active');
        $('.va-wdot').each(function() {
            var s = parseInt($(this).data('step'), 10);
            $(this).toggleClass('is-active', s === step).toggleClass('is-done', s < step);
        });
        $('#va-wiz-fill').css('width', (step / _wTotal * 100) + '%');
        $('#va-wiz-label').text(step + ' / ' + _wTotal);
        if (step === 1) {
            $('#va-wizard-prev').hide();
        } else {
            $('#va-wizard-prev').css('display', 'inline-flex');
        }
        if (step === _wTotal) { $('#va-wizard-next').hide(); $('#va-submit-btn').show(); }
        else { $('#va-wizard-next').show(); $('#va-submit-btn').hide(); }
        _wStep = step;
        updateStep2CategoryLabel();
        var $body = $('.va-wizard-body');
        if ($body.length) $body.scrollTop(0);
    }

    function wizValidate(step) {
        if (step === 1) {
            if (!($('#va-category').val() || '')) {
                showSubmitError('Válassz kategóriát!');
                return false;
            }
            var slug = getSelectedCategorySlug();
            if (slug === 'egyeb' && !(($('#va-other-category').val() || '') + '').trim()) {
                openOtherCategoryModal();
                showSubmitError('Add meg az egyéb kategóriát.');
                return false;
            }
        } else if (step === 2) {
            if (!(($('[name="title"]').val() || '') + '').trim()) {
                showSubmitError('Add meg a hirdetés címét!');
                return false;
            }
            var vehicleErr = (typeof validateVehicleRequiredFields === 'function') ? validateVehicleRequiredFields() : '';
            if (vehicleErr) { showSubmitError(vehicleErr); return false; }
            var catErr = (typeof validateCategoryRequiredFields === 'function') ? validateCategoryRequiredFields() : '';
            if (catErr) { showSubmitError(catErr); return false; }
        } else if (step === 3) {
            var city  = (($('input[name="location"]').val() || '') + '').trim();
            var zip   = (($('input[name="postal_code"]').val() || '') + '').replace(/\D+/g, '');
            var phone = (($('input[name="phone"]').val() || '') + '').trim();
            if (!city && !zip) { showSubmitError('Add meg a várost vagy irányítószámot!'); return false; }
            if (!phone)        { showSubmitError('Add meg a telefonszámot!'); return false; }
        }
        return true;
    }

    $('#va-wizard-next').on('click', function() { if (_wStep < _wTotal) wizGoTo(_wStep + 1); });
    $('#va-wizard-prev').on('click', function() { if (_wStep > 1)       wizGoTo(_wStep - 1); });
    // Kategória listaelement választás
    function openOtherCategoryModal() {
        var $modal = $('#va-other-cat-modal');
        var $input = $('#va-other-cat-input');
        $input.val((($('#va-other-category').val() || '') + '').trim());
        $('body').addClass('va-modal-open');
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        setTimeout(function(){ $input.trigger('focus'); }, 30);
    }

    function closeOtherCategoryModal() {
        $('body').removeClass('va-modal-open');
        $('#va-other-cat-modal').removeClass('is-open').attr('aria-hidden', 'true');
    }

    /* Submit Notice Modal megnyitás/bezárás */
    function openSubmitNoticeModal() {
        var $modal = $('#va-submit-notice-modal');
        $('body').addClass('va-modal-open');
        $modal.addClass('is-open').attr('aria-hidden', 'false');
    }

    function closeSubmitNoticeModal() {
        $('body').removeClass('va-modal-open');
        $('#va-submit-notice-modal').removeClass('is-open').attr('aria-hidden', 'true');
    }

    function ensureSubmitNoticeModalDom() {
        if ($('#va-submit-notice-modal').length) return;
        var html = ''
            + '<div id="va-submit-notice-modal" role="dialog" aria-modal="true" aria-labelledby="va-submit-notice-title">'
            + '  <div class="va-submit-notice-card" role="presentation">'
            + '    <h4 id="va-submit-notice-title" style="margin:0 0 12px; color:#fff; font-size:20px; font-weight:800;">Értesítés</h4>'
            + '    <div id="va-submit-notice-content"></div>'
            + '  </div>'
            + '</div>';
        $('body').append(html);
    }

    function showSubmitError(message) {
        ensureSubmitNoticeModalDom();
        var safe = $('<div>').text(message || 'Hiba történt.').html();
        $('#va-submit-notice-content').html('<div class="va-notice va-notice--error" style="margin:0;">' + safe + '</div>');
        openSubmitNoticeModal();
        setTimeout(function(){
            var $modal = $('#va-submit-notice-modal');
            var visible = $modal.length && $modal.is(':visible') && $modal.find('.va-submit-notice-card').length;
            if (!visible) {
                alert(message || 'Hiba történt.');
            }
        }, 60);
        window.va_toast && va_toast(message || 'Hiba történt.', 'error');
    }

    // Move modal to body so it is not constrained by wizard container stacking/transform contexts.
    (function ensureSubmitModalOnBody(){
        ensureSubmitNoticeModalDom();
        var $modal = $('#va-submit-notice-modal');
        if ($modal.length && !$modal.parent().is('body')) {
            $('body').append($modal);
        }
    })();

    function closeSubmitPageOverlay() {
        $('body').removeClass('va-page-modal-open');
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/';
        }
    }

    $('#va-submit-page-close').on('click', function(){
        closeSubmitPageOverlay();
    });

    $(document).on('keydown', function(e){
        if (e.keyCode === 27) {
            if ($('#va-other-cat-modal').hasClass('is-open') || $('#va-submit-notice-modal').hasClass('is-open')) {
                return;
            }
            closeSubmitPageOverlay();
        }
    });

    /* Submit notice modal bezárása a háttér kattintásakor vagy ESC billentyűre */
    $('#va-submit-notice-modal').on('click', function(e){
        if(e.target === this) closeSubmitNoticeModal();
    });
    $(document).on('keydown', function(e){
        if(e.keyCode === 27 && $('#va-submit-notice-modal').hasClass('is-open')){
            closeSubmitNoticeModal();
        }
    });

    $(document).on('click', '.va-cat-item', function() {
        $('.va-cat-item').removeAttr('data-selected');
        $(this).attr('data-selected', '1');
        $('#va-category').val($(this).data('term-id')).trigger('change');
        updateStep2CategoryLabel();

        var slug = (($(this).data('slug') || '') + '').toLowerCase();
        if (slug === 'egyeb') {
            openOtherCategoryModal();
        } else {
            $('#va-other-category').val('');
        }
    });

    $('#va-other-cat-cancel').on('click', function(){
        closeOtherCategoryModal();
    });
    $('#va-other-cat-save').on('click', function(){
        var val = (($('#va-other-cat-input').val() || '') + '').trim();
        if (!val) {
            window.va_toast && va_toast('Adj meg egy kategória nevet.', 'error');
            return;
        }
        $('#va-other-category').val(val);
        closeOtherCategoryModal();
    });
    $('#va-other-cat-modal').on('click', function(e){
        if (e.target === this) closeOtherCategoryModal();
    });
    $(document).on('keydown', function(e){
        if (e.key === 'Escape') closeOtherCategoryModal();
    });
    // Állapot gomb választás
    $(document).on('click', '.va-cond-btn', function() {
        $('.va-cond-btn').removeClass('is-selected');
        $(this).addClass('is-selected');
        $('#va-condition-hidden').val($(this).data('term-id'));
    });
    // Step dot kattintás
    $(document).on('click', '.va-wdot', function() {
        var s = parseInt($(this).data('step'), 10);
        if (s !== _wStep) wizGoTo(s);
    });
    wizGoTo(1);

    /* ══ Irányítószám/Város/Utca keresés ═══════════════════ */
    // Irányítószám DB betöltése
    var VA_ADDRESS_DB = <?php echo wp_json_encode( $address_seed_data ); ?>;
    var VA_POSTAL_CITY_MAP = {};
    var VA_POSTALS_BY_CITY = {};
    var VA_CITY_STREETS_MAP = {};
    var VA_CITY_LABELS = {};

    function vaUniqueStrings(values) {
        var out = [];
        var seen = {};
        (values || []).forEach(function(item) {
            var value = ((item || '') + '').trim();
            if (!value) return;
            var key = vaNormalizeAddressValue(value);
            if (seen[key]) return;
            seen[key] = true;
            out.push(value);
        });
        return out;
    }

    function vaGetLearnedTerms(type, categorySlug) {
        var all = (VA_Data && VA_Data.learned_terms && VA_Data.learned_terms[type]) ? VA_Data.learned_terms[type] : {};
        var globalTerms = Array.isArray(all.__global) ? all.__global : [];
        var catTerms = [];
        if (categorySlug && Array.isArray(all[categorySlug])) {
            catTerms = all[categorySlug];
        }
        return vaUniqueStrings(globalTerms.concat(catTerms));
    }

    function vaNormalizeAddressValue(value) {
        var normalized = (value || '').toString().trim();
        try {
            normalized = normalized.toLocaleLowerCase('hu-HU');
        } catch (e) {
            normalized = normalized.toLowerCase();
        }
        if (typeof normalized.normalize === 'function') {
            normalized = normalized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
        return normalized;
    }

    function vaPopulateCityOptions() {
        var learnedCities = vaGetLearnedTerms('location', '');
        var $datalist = $('#va-city-list');
        $datalist.empty();
        var allCities = [];
        Object.keys(VA_CITY_LABELS).sort().forEach(function(cityKey) {
            allCities.push(VA_CITY_LABELS[cityKey]);
        });
        allCities = vaUniqueStrings(allCities.concat(learnedCities)).sort(function(a, b){ return a.localeCompare(b, 'hu'); });
        allCities.forEach(function(cityValue) {
            $('<option>').attr('value', cityValue).appendTo($datalist);
        });
    }

    function vaPopulateStreetOptions(cityValue) {
        var cityKey = vaNormalizeAddressValue(cityValue);
        var streets = (VA_CITY_STREETS_MAP[cityKey] || []).concat(vaGetLearnedTerms('street', ''));
        var $datalist = $('#va-street-list');
        $datalist.empty();
        vaUniqueStrings(streets).forEach(function(street) {
            $('<option>').attr('value', street).appendTo($datalist);
        });
    }

    function vaSyncCityFromPostal() {
        var postal = (($('input[name="postal_code"]').val() || '') + '').replace(/\D+/g, '');
        if (!postal || !VA_POSTAL_CITY_MAP[postal]) return;
        var city = VA_POSTAL_CITY_MAP[postal];
        $('input[name="location"]').val(city);
        vaPopulateStreetOptions(city);
    }

    function vaSyncPostalFromCity() {
        var cityValue = ($('input[name="location"]').val() || '').toString().trim();
        var cityKey = vaNormalizeAddressValue(cityValue);
        var postals = VA_POSTALS_BY_CITY[cityKey] || [];
        vaPopulateStreetOptions(cityValue);
        if (!postals.length) return;
        $('input[name="postal_code"]').val(postals[0]);
        if (VA_CITY_LABELS[cityKey] && cityValue !== VA_CITY_LABELS[cityKey]) {
            $('input[name="location"]').val(VA_CITY_LABELS[cityKey]);
        }
    }

    if (VA_ADDRESS_DB && Array.isArray(VA_ADDRESS_DB.records)) {
        VA_ADDRESS_DB.records.forEach(function(rec) {
            var pc = (((rec && rec.postal_code) || '') + '').replace(/\D+/g, '').slice(0, 4);
            var city = (((rec && rec.city) || '') + '').trim();
            var street = (((rec && rec.street) || '') + '').trim();
            if (!pc || !city) return;
            var cityKey = vaNormalizeAddressValue(city);
            if (!VA_POSTAL_CITY_MAP[pc]) VA_POSTAL_CITY_MAP[pc] = city;
            if (!VA_POSTALS_BY_CITY[cityKey]) VA_POSTALS_BY_CITY[cityKey] = [];
            if (VA_POSTALS_BY_CITY[cityKey].indexOf(pc) === -1) {
                VA_POSTALS_BY_CITY[cityKey].push(pc);
            }
            if (!VA_CITY_STREETS_MAP[cityKey]) VA_CITY_STREETS_MAP[cityKey] = [];
            if (street && VA_CITY_STREETS_MAP[cityKey].indexOf(street) === -1) {
                VA_CITY_STREETS_MAP[cityKey].push(street);
            }
            if (!VA_CITY_LABELS[cityKey]) VA_CITY_LABELS[cityKey] = city;
        });
    }

    vaPopulateCityOptions();

    $(document).on('input change blur', 'input[name="postal_code"]', function(){
        this.value = (this.value || '').replace(/\D+/g, '').slice(0, 4);
        vaSyncCityFromPostal();
    });

    $(document).on('input change blur', 'input[name="location"]', function(){
        vaSyncPostalFromCity();
    });

    if ($('input[name="postal_code"]').val()) {
        vaSyncCityFromPostal();
    } else if ($('input[name="location"]').val()) {
        vaSyncPostalFromCity();
    }

    /* ══ Képkezelő ═══════════════════════════════════════ */
    let _files   = [];   // { file: File|null, id: string, existing_id: int|null, url: string|null }[]
    let _maxImg  = 10;
    let _featured = 0;

    const $picker    = $('#va-img-picker');
    const $grid      = $('#va-img-grid');
    const $input     = $('#va-img-file-input');
    const $featIdx   = $('#va-featured-index');
    const $keepInput = $('#va-keep-images');

    _maxImg = parseInt( $input.data('max') || 10 );

    // Edit mód: meglévő képek betöltése
    var editImages = VA_Data.edit_images || [];
    var editThumb  = parseInt( VA_Data.edit_thumb ) || 0;
    editImages.forEach(function(img, idx) {
        if (!img.url) return;
        _files.push({ file: null, id: 'existing_' + img.id, existing_id: img.id, url: img.url });
        if (img.id === editThumb) _featured = idx;
    });
    if (_files.length) renderGrid();

    /* ── Fájl hozzáadása ─────────────────────────────── */
    function addFiles(newFiles) {
        for (let f of newFiles) {
            if (_files.length >= _maxImg) break;
            if (!['image/jpeg','image/png','image/webp'].includes(f.type)) continue;
            if (f.size > 5 * 1024 * 1024) { alert(f.name + ' – túl nagy (max 5 MB)!'); continue; }
            _files.push({ file: f, id: 'img_' + Date.now() + '_' + Math.random().toString(36).slice(2), existing_id: null, url: null });
        }
        renderGrid();
    }

    /* ── Megtartandó meglévő képek frissítése ─────────── */
    function updateKeepImages() {
        var keep = _files.filter(function(f){ return f.existing_id; }).map(function(f){ return f.existing_id; });
        $keepInput.val(keep.join(','));
    }

    /* ── Grid renderelése ────────────────────────────── */
    function renderGrid() {
        $grid.empty();

        // Biztosítjuk hogy _featured valid
        if (_files.length > 0 && _featured >= _files.length) _featured = 0;
        $featIdx.val(_featured);
        updateKeepImages();

        _files.forEach((item, idx) => {
            const url = item.url ? item.url : URL.createObjectURL(item.file);
            const isFeat = idx === _featured;
            const starSvg = '<svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            const xSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            const $card = $(`
                <div class="va-img-card${isFeat ? ' va-img-card--featured' : ''}" data-id="${item.id}">
                    <img src="${url}" class="va-img-card__thumb" draggable="false" alt="">
                    <div class="va-img-card__overlay">
                        <button type="button" class="va-img-feat-btn" title="Borítókép beállítása">${starSvg}</button>
                        <button type="button" class="va-img-del-btn" title="Törlés">${xSvg}</button>
                    </div>
                    ${isFeat ? '<div class="va-img-card__label">Borítókép</div>' : ''}
                </div>
            `);

            // Törlés
            $card.find('.va-img-del-btn').on('click', function(){
                _files.splice(idx, 1);
                if (_featured >= _files.length) _featured = 0;
                renderGrid();
            });

            // Főkép
            $card.find('.va-img-feat-btn').on('click', function(){
                _featured = idx;
                renderGrid();
            });

            $grid.append($card);
        });

        // "+ Képek hozzáadása" gomb a grid végére
        if (_files.length < _maxImg) {
            const $addBtn = $('<button type="button" class="va-img-add"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>Képek<br>hozzáadása</span></button>');
            $addBtn.on('click', function(){ $input.trigger('click'); });
            $grid.append($addBtn);
        }

        // Sortable – jQuery UI (mint az admin)
        if (_files.length > 1) {
            $grid.sortable({
                items: '.va-img-card',
                tolerance: 'pointer',
                cursor: 'grabbing',
                placeholder: 'va-img-ph',
                forcePlaceholderSize: true,
                stop: function() {
                    // Olvassuk vissza a DOM sorrendet _files-ba
                    const newOrder = [];
                    let newFeaturedId = _files[_featured] ? _files[_featured].id : null;
                    $grid.find('.va-img-card').each(function() {
                        const id = $(this).data('id');
                        const found = _files.find(function(f){ return f.id === id; });
                        if (found) newOrder.push(found);
                    });
                    _files = newOrder;
                    if (newFeaturedId) {
                        _featured = _files.findIndex(function(f){ return f.id === newFeaturedId; });
                        if (_featured < 0) _featured = 0;
                    }
                    $featIdx.val(_featured);
                    renderGrid();
                }
            });
        }
    }

    /* ── Drag & drop a gridre ───────────────────────── */
    $grid.on('dragover', function(e){ e.preventDefault(); $(this).addClass('va-img-grid--hover'); });
    $grid.on('dragleave', function(){ $(this).removeClass('va-img-grid--hover'); });
    $grid.on('drop', function(e){
        e.preventDefault();
        $(this).removeClass('va-img-grid--hover');
        addFiles(e.originalEvent.dataTransfer.files);
    });

    /* ── Statikus "+ gomb" kattintás (első renderelés előtt) ── */
    $grid.on('click', '.va-img-add', function(){ $input.trigger('click'); });

    /* ── Fájl input ──────────────────────────────────── */
    $input.on('change', function(){ addFiles(this.files); this.value = ''; });

    /* ══ Quill editor init ═══════════════════════════════════════ */
    var quillModules = {
        toolbar: {
            container: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote'],
                [{ align: [] }],
                ['link', 'image'],
                ['clean']
            ],
            handlers: {
                image: function() {
                    if (quill.root.querySelectorAll('img').length >= 2) {
                        alert('Maximum 2 kép engedélyezett a leírásban.');
                        return;
                    }
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/jpeg,image/png,image/webp,image/gif');
                    input.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
                    document.body.appendChild(input);
                    input.addEventListener('change', function() {
                        var file = input.files[0];
                        document.body.removeChild(input);
                        if (!file) return;
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var range = quill.getSelection(true);
                            quill.insertEmbed(range ? range.index : quill.getLength(), 'image', e.target.result);
                            quill.setSelection((range ? range.index : 0) + 1);
                        };
                        reader.readAsDataURL(file);
                    });
                    input.click();
                }
            }
        }
    };
    var quill = new Quill('#va-quill-editor', {
        theme: 'snow',
        placeholder: 'Írja le a hirdetett terméket részletesen...',
        modules: quillModules
    });

    // Edit módban meglévő tartalom betöltése
    var $hidden = $('#va-desc-hidden');
    if ($hidden.val().trim()) {
        quill.root.innerHTML = $hidden.val();
    }

    /* ══ Kép átméretezés ════════════════════════════════ */
    (function(){
        var activeImg = null, handle = null, startX, startW;
        handle = document.createElement('div');
        handle.style.cssText = 'position:absolute;width:12px;height:12px;background:#ff4444;border:2px solid #fff;border-radius:3px;cursor:se-resize;display:none;z-index:999;box-shadow:0 0 4px rgba(0,0,0,.6);';
        document.body.appendChild(handle);

        function positionHandle() {
            if (!activeImg) return;
            var r = activeImg.getBoundingClientRect();
            handle.style.left = (r.right + window.scrollX - 8) + 'px';
            handle.style.top  = (r.bottom + window.scrollY - 8) + 'px';
        }

        quill.root.addEventListener('click', function(e) {
            if (e.target.tagName === 'IMG') {
                activeImg = e.target;
                if (!activeImg.style.width) activeImg.style.width = activeImg.offsetWidth + 'px';
                activeImg.style.cursor = 'pointer';
                positionHandle();
                handle.style.display = 'block';
            } else {
                handle.style.display = 'none';
                activeImg = null;
            }
        });

        handle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            startX = e.clientX;
            startW = activeImg ? activeImg.offsetWidth : 100;
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        function onMove(e) {
            if (!activeImg) return;
            var w = Math.max(40, startW + (e.clientX - startX));
            activeImg.style.width = w + 'px';
            activeImg.style.height = 'auto';
            positionHandle();
        }
        function onUp() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        }
        window.addEventListener('scroll', positionHandle);
        window.addEventListener('resize', positionHandle);
        document.addEventListener('click', function(e){
            if (e.target !== activeImg && e.target !== handle) {
                handle.style.display = 'none';
                activeImg = null;
            }
        });
    })();

    function rebuildVehicleModelOptions() {
        if (!isVehicleCategorySelected()) return;

        var $brand = $('#va-brand');
        var $model = $('#va-model');
        if (!$brand.length || !$model.length) return;

        var brand = (($brand.val() || '') + '').trim();
        var catalog = (typeof VA_Data !== 'undefined' && VA_Data.vehicle_brand_models) ? VA_Data.vehicle_brand_models : {};
        var models = catalog[brand] || [];
        if ((!models || !models.length) && brand) {
            var brandLower = brand.toLowerCase();
            Object.keys(catalog).some(function(key){
                if ((key || '').toLowerCase() === brandLower) {
                    models = catalog[key] || [];
                    return true;
                }
                return false;
            });
        }
        if (!models || !models.length) {
            var mergedModels = [];
            Object.keys(catalog).forEach(function(key){
                (catalog[key] || []).forEach(function(modelName){
                    if (mergedModels.indexOf(modelName) === -1) mergedModels.push(modelName);
                });
            });
            models = mergedModels.slice(0, 600);
        }

        if ($model.is('input')) {
            ensureBrandModelDataLists();
            var $modelList = $('#va-model-list');
            var currentInput = (($model.val() || '') + '').trim();
            $modelList.empty();
            models.forEach(function(modelName){
                $('<option>').attr('value', modelName).appendTo($modelList);
            });
            if (currentInput) {
                $model.val(currentInput);
            }
            return;
        }

        var current = $model.data('selected') || $model.val() || '';
        var html = '<option value="">– Válasszon –</option>';
        if (current && models.indexOf(current) === -1) {
            html += '<option value="' + $('<div>').text(current).html() + '">' + $('<div>').text(current).html() + '</option>';
        }
        models.forEach(function(modelName) {
            var safe = $('<div>').text(modelName).html();
            html += '<option value="' + safe + '">' + safe + '</option>';
        });
        $model.html(html);
        if (current) {
            $model.val(current);
        }
    }

    $(document).on('change', '#va-brand', function(){
        if (isVehicleCategorySelected()) {
            rebuildVehicleModelOptions();
            return;
        }
        if (typeof VA_Data !== 'undefined' && VA_Data.site_type !== 'jarmu') {
            rebuildHuntingBrandModelDatalists(true);
        }
    });
    $(document).on('focus input', '#va-model', function(){
        if (isVehicleCategorySelected()) {
            rebuildVehicleModelOptions();
        }
    });
    rebuildVehicleModelOptions();

    function getSelectedCategorySlug() {
        var $cat = $('#va-category');
        if (!$cat.length) return '';
        var opt = $cat.find('option:selected');
        var slug = (opt.data('slug') || '').toString();
        if (slug) return slug;
        var selectedCardSlug = (($('.va-cat-item[data-selected="1"]').data('slug') || '') + '').trim();
        if (selectedCardSlug) return selectedCardSlug;
        var id = parseInt($cat.val() || 0, 10);
        if (!id || typeof VA_Data === 'undefined' || !VA_Data.category_slugs) return '';
        return (VA_Data.category_slugs[String(id)] || VA_Data.category_slugs[id] || '').toString();
    }

    function getSelectedCategoryLabel() {
        var cardLabel = (($('.va-cat-item[data-selected="1"]').text() || '') + '').trim();
        if (cardLabel) return cardLabel;

        var id = parseInt($('#va-category').val() || 0, 10);
        if (!id) return '';
        var $card = $('.va-cat-item[data-term-id="' + id + '"]');
        if ($card.length) {
            return (($card.first().text() || '') + '').trim();
        }
        return '';
    }

    function updateStep2CategoryLabel() {
        var label = getSelectedCategoryLabel();
        $('#va-step2-category-label').text(label ? label : '');
    }

    function isVehicleCategorySelected() {
        if (typeof VA_Data !== 'undefined' && VA_Data.site_type === 'jarmu') {
            return true;
        }
        var slug = (getSelectedCategorySlug() || '').toLowerCase();
        var selectedText = (($('#va-category option:selected').text() || '') + '').toLowerCase();
        var vehicleBySlug = /(jarmu|gepjarmu|auto|autok|kocsi|motor|teherauto|busz|utanfuto|utanfutok|munkagep)/.test(slug);
        var vehicleByText = /(jármű|gepjármű|autó|auto|kocsi|motor|teherautó|busz|utánfutó|utanfuto|munkagép|munkagep)/.test(selectedText);
        return vehicleBySlug || vehicleByText;
    }

    function applyVehicleCategoryVisibility() {
        $('.va-vehicle-only').toggle(isVehicleCategorySelected());
    }

    function ensureBrandModelDataLists() {
        if (!$('#va-brand-list').length) {
            $('<datalist id="va-brand-list"></datalist>').appendTo('#va-submit-form');
        }
        if (!$('#va-model-list').length) {
            $('<datalist id="va-model-list"></datalist>').appendTo('#va-submit-form');
        }
    }

    function switchBrandModelFieldMode() {
        var $brand = $('#va-brand');
        var $model = $('#va-model');
        if (!$brand.length || !$model.length) return;

        var brandValue = (($brand.val() || '') + '').trim();
        var modelValue = (($model.val() || '') + '').trim();

        if (isVehicleCategorySelected()) {
            if (!$brand.is('select')) {
                var brands = (typeof VA_Data !== 'undefined' && Array.isArray(VA_Data.vehicle_brands)) ? VA_Data.vehicle_brands : [];
                var brandHtml = '<option value="">– Válasszon –</option>';
                if (brandValue && brands.indexOf(brandValue) === -1) {
                    var safeCurrentBrand = $('<div>').text(brandValue).html();
                    brandHtml += '<option value="' + safeCurrentBrand + '" selected>' + safeCurrentBrand + '</option>';
                }
                brands.forEach(function(item){
                    var safe = $('<div>').text(item).html();
                    var selected = item === brandValue ? ' selected' : '';
                    brandHtml += '<option value="' + safe + '"' + selected + '>' + safe + '</option>';
                });
                var $brandSelect = $('<select name="brand" id="va-brand" class="va-select"></select>').html(brandHtml);
                $brand.replaceWith($brandSelect);
                $brand = $brandSelect;
                enhancePopupSelects($brand.parent());
            }

            ensureBrandModelDataLists();
            if (!$model.is('input')) {
                teardownPopupSelect($model);
                var $modelInputVehicle = $('<input type="text" name="model" id="va-model" class="va-input" list="va-model-list" autocomplete="off" placeholder="Írd be a modellt vagy válassz a listából...">').val(modelValue);
                $model.replaceWith($modelInputVehicle);
                $model = $modelInputVehicle;
            }
            rebuildVehicleModelOptions();
            return;
        }

        ensureBrandModelDataLists();

        if (!$brand.is('select')) {
            teardownPopupSelect($brand);
            var $brandSelect = $('<select name="brand" id="va-brand" class="va-select"></select>');
            $brandSelect.append('<option value="">– Válasszon –</option>');
            if (brandValue) {
                var safeBV = $('<div>').text(brandValue).html();
                $brandSelect.append('<option value="' + safeBV + '" selected>' + safeBV + '</option>');
            }
            $brand.replaceWith($brandSelect);
            $brand = $brandSelect;
            enhancePopupSelects($brand.parent());
        }
        if (!$model.is('input')) {
            teardownPopupSelect($model);
            var $modelInput = $('<input type="text" name="model" id="va-model" class="va-input" list="va-model-list" autocomplete="off" placeholder="pl. R8, Z6...">').val(modelValue);
            $model.replaceWith($modelInput);
        }

        rebuildHuntingBrandModelDatalists(false);
        if (modelValue) {
            $('#va-model').val(modelValue);
        }
    }

    function rebuildHuntingBrandModelDatalists(clearModel) {
        if (typeof VA_Data === 'undefined' || VA_Data.site_type === 'jarmu') return;

        var $brand = $('#va-brand');
        var $model = $('#va-model');
        var $brandList = $('#va-brand-list');
        var $modelList = $('#va-model-list');
        if (!$brand.length || !$model.length) return;
        if (!$brand.is('select') && !$brandList.length) return;
        if (!$modelList.length) return;

        var categorySlug = getSelectedCategorySlug();
        var categoryData = (VA_Data.hunting_brand_models && VA_Data.hunting_brand_models[categorySlug]) ? VA_Data.hunting_brand_models[categorySlug] : {};
        var catData = categoryData;
        if (!catData || !Object.keys(catData).length) {
            var merged = {};
            var all = VA_Data.hunting_brand_models || {};
            Object.keys(all).forEach(function(slug){
                var byBrand = all[slug] || {};
                Object.keys(byBrand).forEach(function(brand){
                    if (!merged[brand]) merged[brand] = [];
                    (byBrand[brand] || []).forEach(function(model){
                        if (merged[brand].indexOf(model) === -1) merged[brand].push(model);
                    });
                });
            });
            catData = merged;
        }

        var learnedBrands = vaGetLearnedTerms('brand', categorySlug);
        learnedBrands.forEach(function(brand){
            if (!catData[brand]) catData[brand] = [];
        });

        var currentBrand = (($brand.val() || '') + '').trim();
        var currentModel = (($model.val() || '') + '').trim();
        var matchedBrandKey = '';

        if (currentBrand) {
            var hasCurrentBrand = Object.keys(catData).some(function(brandKey){
                return (brandKey || '').toLowerCase() === currentBrand.toLowerCase();
            });
            if (!hasCurrentBrand) {
                catData[currentBrand] = [];
            }
        }

        if ($brand.is('select')) {
            var brandOptHtml = '<option value="">– Válasszon –</option>';
            Object.keys(catData).forEach(function(brand) {
                if (currentBrand.toLowerCase() === brand.toLowerCase()) {
                    matchedBrandKey = brand;
                }
                var safeB = $('<div>').text(brand).html();
                var selB = (currentBrand && currentBrand.toLowerCase() === brand.toLowerCase()) ? ' selected' : '';
                brandOptHtml += '<option value="' + safeB + '"' + selB + '>' + safeB + '</option>';
            });
            $brand.html(brandOptHtml);
            syncPopupSelectButton($brand);
        } else if ($brandList.length) {
            $brandList.empty();
            Object.keys(catData).forEach(function(brand) {
                if (currentBrand.toLowerCase() === brand.toLowerCase()) {
                    matchedBrandKey = brand;
                }
                $('<option>').attr('value', brand).appendTo($brandList);
            });
        }

        var modelSource = matchedBrandKey ? (catData[matchedBrandKey] || []) : [];
        modelSource = vaUniqueStrings(modelSource.concat(vaGetLearnedTerms('model', categorySlug)));
        $modelList.empty();
        modelSource.forEach(function(model) {
            $('<option>').attr('value', model).appendTo($modelList);
        });

        if (clearModel) {
            $model.val('');
        } else if (currentModel && modelSource.length && modelSource.indexOf(currentModel) === -1) {
            $model.val('');
        }
    }

    function applyLearnedCaliberDatalist() {
        if (typeof VA_Data === 'undefined' || VA_Data.site_type === 'jarmu') return;
        var $caliberSel = $('select[name="caliber"]');
        var $caliberInp = $('input[name="caliber"]');
        var $list = $('#va-caliber-list');
        var categorySlug = getSelectedCategorySlug();
        var learnedCalibers = vaGetLearnedTerms('caliber', categorySlug);

        if ($caliberSel.length && learnedCalibers.length) {
            var existing = [];
            $caliberSel.find('option').each(function() { existing.push($(this).val() || ''); });
            learnedCalibers.forEach(function(caliber) {
                if (caliber && existing.indexOf(caliber) === -1) {
                    var safe = $('<div>').text(caliber).html();
                    $caliberSel.append('<option value="' + safe + '">' + safe + '</option>');
                }
            });
            syncPopupSelectButton($caliberSel);
        } else if ($caliberInp.length && $list.length) {
            var seedValues = [];
            $list.find('option').each(function() { seedValues.push($(this).attr('value') || ''); });
            var merged = vaUniqueStrings(seedValues.concat(learnedCalibers));
            $list.empty();
            merged.forEach(function(caliber) { $('<option>').attr('value', caliber).appendTo($list); });
        }
    }

    function rebuildClothingSizeOptions() {
        var $sizeSystem = $('#va-clothing-size-system');
        var $size = $('#va-clothing-size');
        if (!$sizeSystem.length || !$size.length) return;

        var system = (($sizeSystem.val() || 'eu') + '').toLowerCase();
        var selectedSize = (($size.val() || $size.data('selected') || '') + '').trim();
        var sizeMap = {
            eu: ['XS','S','M','L','XL','XXL','3XL+'],
            usa: ['XS','S','M','L','XL','XXL','3XL+'],
            en: ['XS','S','M','L','XL','XXL','3XL+']
        };
        var options = sizeMap[system] || sizeMap.eu;
        var html = '<option value="">– Válasszon –</option>';

        if (selectedSize && options.indexOf(selectedSize) === -1) {
            var safeCurrent = $('<div>').text(selectedSize).html();
            html += '<option value="' + safeCurrent + '" selected>' + safeCurrent + '</option>';
        }

        options.forEach(function(sizeVal){
            var safe = $('<div>').text(sizeVal).html();
            var selected = selectedSize === sizeVal ? ' selected' : '';
            html += '<option value="' + safe + '"' + selected + '>' + safe + '</option>';
        });

        $size.html(html);
        if (selectedSize) {
            $size.val(selectedSize);
        }
        syncPopupSelectButton($size);
    }

    function parseIsoDate(iso) {
        if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return null;
        var parts = iso.split('-').map(function(x){ return parseInt(x, 10); });
        var date = new Date(parts[0], parts[1] - 1, parts[2]);
        if (date.getFullYear() !== parts[0] || date.getMonth() !== (parts[1] - 1) || date.getDate() !== parts[2]) return null;
        return date;
    }

    function toIsoDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function formatDateDisplay(iso) {
        var date = parseIsoDate(iso);
        if (!date) return '';
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '. ' + month + '. ' + day + '.';
    }

    function closeDarkDatePickers() {
        $('.va-dark-date').each(function(){
            var closeFn = $(this).data('darkDateClose');
            if (typeof closeFn === 'function') {
                closeFn();
            }
        });
    }

    function initDarkDatePickers() {
        $('.va-dark-date').each(function(){
            var $field = $(this);
            if ($field.data('darkDateInit')) {
                return;
            }
            $field.data('darkDateInit', true);

            var $value = $field.find('.va-date-value');
            var $display = $field.find('.va-date-display');
            var $popup = $field.find('.va-dark-calendar');
            var $title = $field.find('.va-dark-calendar__title');
            var $grid = $field.find('.va-dark-calendar__grid');
            var $prev = $field.find('.va-dark-calendar__nav[data-dir="prev"]');
            var $next = $field.find('.va-dark-calendar__nav[data-dir="next"]');
            var $clear = $field.find('.va-dark-calendar__clear');
            var viewDate = parseIsoDate($value.val()) || new Date();

            function syncDisplay() {
                $display.val(formatDateDisplay($value.val()));
            }

            function closePopup() {
                $popup.hide();
                $field.removeClass('is-open');
            }

            function openPopup() {
                closeDarkDatePickers();
                renderCalendar();
                $popup.show();
                $field.addClass('is-open');
            }

            function renderCalendar() {
                var months = ['január','február','március','április','május','június','július','augusztus','szeptember','október','november','december'];
                var year = viewDate.getFullYear();
                var month = viewDate.getMonth();
                var selected = parseIsoDate($value.val());
                var first = new Date(year, month, 1);
                var offset = (first.getDay() + 6) % 7;
                var daysInMonth = new Date(year, month + 1, 0).getDate();
                var prevMonthDays = new Date(year, month, 0).getDate();
                var today = new Date();
                today.setHours(0, 0, 0, 0);

                $title.text(year + '. ' + months[month]);
                $grid.empty();

                for (var i = 0; i < 42; i++) {
                    var dateObj;
                    var label;
                    var $day = $('<button type="button" class="va-dark-calendar__day"></button>');
                    if (i < offset) {
                        label = prevMonthDays - offset + i + 1;
                        dateObj = new Date(year, month - 1, label);
                        $day.addClass('va-dark-calendar__day--muted');
                    } else if (i >= offset + daysInMonth) {
                        label = i - (offset + daysInMonth) + 1;
                        dateObj = new Date(year, month + 1, label);
                        $day.addClass('va-dark-calendar__day--muted');
                    } else {
                        label = i - offset + 1;
                        dateObj = new Date(year, month, label);
                    }
                    var iso = toIsoDate(dateObj);
                    $day.text(label).attr('data-iso', iso);
                    if (selected && toIsoDate(selected) === iso) {
                        $day.addClass('va-dark-calendar__day--selected');
                    }
                    if (toIsoDate(today) === iso) {
                        $day.addClass('va-dark-calendar__day--today');
                    }
                    $grid.append($day);
                }
            }

            $field.data('darkDateClose', closePopup);
            syncDisplay();

            $display.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                if ($popup.is(':visible')) {
                    closePopup();
                } else {
                    openPopup();
                }
            });

            $field.find('.va-date-btn').on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                if ($popup.is(':visible')) {
                    closePopup();
                } else {
                    openPopup();
                }
            });

            $prev.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
                renderCalendar();
            });

            $next.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
                renderCalendar();
            });

            $clear.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                $value.val('').trigger('change').trigger('input');
                syncDisplay();
                closePopup();
            });

            $grid.on('click', '.va-dark-calendar__day', function(e){
                e.preventDefault();
                e.stopPropagation();
                var iso = ($(this).attr('data-iso') || '').trim();
                $value.val(iso).trigger('change').trigger('input');
                viewDate = parseIsoDate(iso) || viewDate;
                syncDisplay();
                closePopup();
            });

            $value.on('change input', syncDisplay);
        });
    }

    function applyVadcameraDynamicFields() {
        var type = (($('#va-vadcamera-camera-type').val() || '') + '').trim();
        $('.va-vadcamera-dynamic-group').each(function(){
            var list = (($(this).data('vadcameraTypes') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            $(this).toggle(list.indexOf(type) !== -1);
        });
    }

    function applyShoeDynamicFields() {
        var type = (($('#va-shoe-main-type').val() || '') + '').trim();
        $('.va-shoe-dynamic-group').each(function(){
            var list = (($(this).data('shoeTypes') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            $(this).toggle(list.indexOf(type) !== -1);
        });
    }

    function applyHandgunDynamicFields() {
        var type = (($('#va-marok-type').val() || '') + '').trim();
        var opticReady = (($('#va-marok-optic-ready').val() || '') + '').trim();
        $('.va-marok-dynamic-group').hide();
        if (type === 'revolver') {
            $('.va-marok-revolver-only').show();
        }
        if (type === 'sportpisztoly' || type === 'verseny') {
            $('.va-marok-sport-only').show();
        }
        if (opticReady && opticReady !== 'nincs') {
            $('.va-marok-optic-only').show();
        }
    }

    function applyMixedRifleDynamicFields() {
        var type = (($('#va-mixed-type').val() || '') + '').trim();
        var opticCompatible = (($('#va-mixed-optic-compatible').val() || '') + '').trim();
        var usageValues = $('#va-mixed-usage').val();
        var usageList = Array.isArray(usageValues) ? usageValues : (usageValues ? [usageValues] : []);

        $('.va-mixed-dynamic-group').each(function(){
            var $group = $(this);
            var typeNeed = (($group.attr('data-mixed-type') || '') + '').trim();
            var opticNeed = (($group.attr('data-mixed-optic') || '') + '').trim();
            var usageNeed = (($group.attr('data-mixed-usage') || '') + '').trim();

            var typeOk = !typeNeed || typeNeed === type;
            var opticOk = !opticNeed || (opticNeed === 'yes' && opticCompatible === 'igen');
            var usageOk = !usageNeed || usageList.indexOf(usageNeed) !== -1;
            $group.toggle(typeOk && opticOk && usageOk);
        });
    }

    function applyRifleDynamicFields() {
        var type = (($('#va-rifle-type').val() || '') + '').trim();
        $('.va-rifle-dynamic-group').each(function(){
            var list = (($(this).attr('data-rifle-types') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            $(this).toggle(list.indexOf(type) !== -1);
        });
    }

    function applyAccommodationDynamicFields() {
        var type = (($('#va-accommodation-type').val() || '') + '').trim();
        $('.va-accommodation-dynamic-group').each(function(){
            var list = (($(this).attr('data-accommodation-types') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            $(this).toggle(list.indexOf(type) !== -1);
        });
    }

    function applyTrophyDynamicFields() {
        var custom = (($('#va-trophy-custom-made').val() || '') + '').trim();
        $('.va-trophy-dynamic-group').each(function(){
            var expected = (($(this).data('trophyCustom') || '') + '').trim();
            $(this).toggle(expected !== '' && custom === expected);
        });
    }

    function applyHuntDynamicFields() {
        var priceType = (($('#va-hunt-price-type').val() || '') + '').trim();
        var huntType = (($('#va-hunt-type').val() || '') + '').trim();
        var methodsVal = $('#va-hunt-methods').val();
        var methods = Array.isArray(methodsVal) ? methodsVal : (methodsVal ? [methodsVal] : []);

        $('.va-hunt-dynamic-group').hide();
        $('.va-hunt-dynamic-group[data-hunt-price="' + priceType + '"]').show();
        $('.va-hunt-dynamic-group[data-hunt-type="' + huntType + '"]').show();
        if (methods.indexOf('hajtas') !== -1) {
            $('.va-hunt-dynamic-group[data-hunt-method="hajtas"]').show();
        }
    }

    function applyVadkarDynamicFields() {
        var landType = (($('#va-hunt-land-type').val() || '') + '').trim();
        var response = (($('#va-hunt-response-time').val() || '') + '').trim();
        var contract = (($('#va-hunt-contract-type').val() || '') + '').trim();

        var isAgri = ['szantofold','kukorica','napraforgo','buza','szolo','gyumolcsos','vegyes-mezogazdasagi'].indexOf(landType) !== -1;
        var isUrgent = response === 'azonnal-0-2';
        var isLong = contract === 'eves' || contract === 'folyamatos';

        $('.va-vadkar-dynamic-group').hide();
        if (isAgri) {
            $('.va-vadkar-dynamic-group[data-vadkar-land="agri"]').show();
        }
        if (isUrgent) {
            $('.va-vadkar-dynamic-group[data-vadkar-response="urgent"]').show();
        }
        if (isLong) {
            $('.va-vadkar-dynamic-group[data-vadkar-contract="long"]').show();
        }
    }

    function applyExchangeDynamicFields() {
        var offerCategory = (($('#va-exchange-offer-category').val() || '') + '').trim();
        $('.va-exchange-dynamic-group').each(function(){
            var list = (($(this).data('exchangeCategories') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            $(this).toggle(list.indexOf(offerCategory) !== -1);
        });
    }

    function applyPublicationDynamicFields() {
        var publicationType = (($('#va-publication-type').val() || '') + '').trim();
        var editionType = (($('#va-publication-edition-type').val() || '') + '').trim();
        var signedCopy = (($('#va-publication-signed-copy').val() || '') + '').trim();
        if (!signedCopy && editionType === 'dedikalt') {
            signedCopy = 'igen';
        }

        $('.va-publication-dynamic-group').each(function(){
            var $group = $(this);
            var typeList = (($group.attr('data-publication-types') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            var editionList = (($group.attr('data-publication-editions') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            var needsSigned = (($group.attr('data-publication-signed') || '') + '').trim() === 'yes';
            var typeOk = !typeList.length || typeList.indexOf(publicationType) !== -1;
            var editionOk = !editionList.length || editionList.indexOf(editionType) !== -1;
            var signedOk = !needsSigned || signedCopy === 'igen';
            $group.toggle(typeOk && editionOk && signedOk);
        });
    }

    function applyEquipmentDynamicFields() {
        var equipmentType = (($('#va-equipment-type').val() || '') + '').trim();
        var callType = (($('#va-equipment-call-type').val() || '') + '').trim();
        var electronicValues = $('#va-equipment-electronic-features').val();
        var electronicFeatures = Array.isArray(electronicValues) ? electronicValues : (electronicValues ? [electronicValues] : []);
        var needsRuntime = ['elemlampa', 'fejlampa', 'akkumulator'].indexOf(equipmentType) !== -1 || callType === 'elektronikus' || electronicFeatures.length > 0;

        $('.va-equipment-dynamic-group').each(function(){
            var $group = $(this);
            var typeList = (($group.attr('data-equipment-types') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            var runtimeRequired = (($group.attr('data-equipment-runtime') || '') + '').trim() === 'yes';
            var typeOk = !typeList.length || typeList.indexOf(equipmentType) !== -1;
            var runtimeOk = !runtimeRequired || needsRuntime;
            $group.toggle(typeOk && runtimeOk);
        });
    }

    function applyDecorDynamicFields() {
        var decorType = (($('#va-decor-type').val() || '') + '').trim();
        var decorMaterial = (($('#va-decor-material').val() || '') + '').trim();

        $('.va-decor-dynamic-group').each(function(){
            var $group = $(this);
            var typeNeed = (($group.attr('data-decor-type') || '') + '').trim();
            var materialNeed = (($group.attr('data-decor-material') || '') + '').trim();
            var typeOk = !typeNeed || typeNeed === decorType;
            var materialOk = !materialNeed || materialNeed === decorMaterial;
            $group.toggle(typeOk && materialOk);
        });
    }

    function calculateDogAgeMonths() {
        var birthDate = (($('#va-dog-birth-date').val() || '') + '').trim();
        var $age = $('#va-dog-age-months');
        if (!$age.length) return '';
        if (!birthDate) {
            return (($age.val() || '') + '').trim();
        }

        var birth = new Date(birthDate + 'T00:00:00');
        if (Number.isNaN(birth.getTime())) {
            return (($age.val() || '') + '').trim();
        }

        var today = new Date();
        var months = (today.getFullYear() - birth.getFullYear()) * 12 + (today.getMonth() - birth.getMonth());
        if (today.getDate() < birth.getDate()) {
            months -= 1;
        }
        months = Math.max(0, months);
        $age.val(months);
        return String(months);
    }

    function applyDogDynamicFields() {
        var trainingLevel = (($('#va-dog-training-level').val() || '') + '').trim();
        var workingType = (($('#va-dog-working-type').val() || '') + '').trim();
        var ageMonthsRaw = calculateDogAgeMonths();
        var ageMonths = parseInt(ageMonthsRaw || '0', 10);
        var isPuppy = !Number.isNaN(ageMonths) && ageMonths >= 0 && ageMonths < 12;
        var isTrained = ['reszben-kepzett', 'kikepzett', 'vadaszatra-kesz'].indexOf(trainingLevel) !== -1;

        $('.va-dog-dynamic-group').each(function(){
            var $group = $(this);
            var puppyRequired = (($group.attr('data-dog-puppy') || '') + '').trim() === 'yes';
            var trainedRequired = (($group.attr('data-dog-trained') || '') + '').trim() === 'yes';
            var workingTypes = (($group.attr('data-dog-working-types') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            var puppyOk = !puppyRequired || isPuppy;
            var trainedOk = !trainedRequired || isTrained;
            var workingOk = !workingTypes.length || workingTypes.indexOf(workingType) !== -1;
            $group.toggle(puppyOk && trainedOk && workingOk);
        });
    }

    function applyOtherClothingDynamicFields() {
        var type = (($('#va-clothing-type').val() || '') + '').trim();
        $('.va-clothing-dynamic-group').each(function(){
            var list = (($(this).data('clothingTypes') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            $(this).toggle(list.indexOf(type) !== -1);
        });
    }

    $('#va-category').on('change', function(){
        if (typeof VA_Data !== 'undefined' && VA_Data.site_type !== 'jarmu') {
            $('#va-brand').val('');
            rebuildHuntingBrandModelDatalists(true);
            applyLearnedCaliberDatalist();
            applyCategorySpecificFieldVisibility();
            rebuildClothingSizeOptions();
            applyOtherClothingDynamicFields();
        }
        applyVehicleCategoryVisibility();
        switchBrandModelFieldMode();
    });

    $(document).on('input change blur', '#va-brand', function(){
        if (typeof VA_Data !== 'undefined' && VA_Data.site_type !== 'jarmu') {
            rebuildHuntingBrandModelDatalists(true);
        }
    });

    var opticZoomData = {
        celtavcso: ['1-4x24','1-6x24','1-8x24','1-10x24','1.5-6x44','2-10x50','2-12x50','3-9x40','3-12x56','3-15x50','3-18x50','4-16x44','4-20x50','4-24x50','5-25x56','6-24x50','8-32x','10-40x'],
        binokular: ['7x','8x','8x42','8x56','10x','10x42','10x50','12x','12x50'],
        monokular: ['4x','6x','8x','10x'],
        kereso: ['6x','7x','8x','10x','8x56','10x56','12x','15x','20x','25x'],
        spektiv: ['8x','10x','12x','15-45x','16-48x','20-60x','22-66x','25-75x','30-90x'],
        'red-dot': ['1x'],
        holografikus: ['1x'],
        prizmas: ['1x','2x','3x','4x','5x'],
        reflex: ['1x'],
        pisztolyoptika: ['1x','2x','3x','4x'],
        'tavmeros-binokular': ['7x','8x','10x'],
        airsoft: ['1x','1.5x','2x','3x','4x'],
        egyeb: []
    };

    function updateOpticFieldVisibility(type) {
        var currentType = (type || '').toString();
        $('.va-telescope-fields-grid [data-optic-types]').each(function(){
            var types = (($(this).data('opticTypes') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            if (!types.length) {
                $(this).show();
                return;
            }
            $(this).toggle(types.indexOf(currentType) !== -1);
        });
    }

    function rebuildOpticZoomOptions(type) {
        var $zoom = $('#va-optic-zoom');
        if (!$zoom.length) return;
        var currentVal = ($zoom.val() || '').trim();
        var options = opticZoomData[type] || [];
        var html = '<option value="">\u2013 V\u00e1lasszon \u2013</option>';
        if (currentVal && options.indexOf(currentVal) === -1) {
            var safe = $('<div>').text(currentVal).html();
            html += '<option value="' + safe + '" selected>' + safe + '</option>';
        }
        options.forEach(function(val) {
            var safe = $('<div>').text(val).html();
            var sel = currentVal === val ? ' selected' : '';
            html += '<option value="' + safe + '"' + sel + '>' + safe + '</option>';
        });
        $zoom.html(html);
        if (currentVal) $zoom.val(currentVal);
        syncPopupSelectButton($zoom);
        updateOpticFieldVisibility(type);
    }

    $(document).on('change', '#va-optic-type', function(){
        rebuildOpticZoomOptions($(this).val());
    });

    $(document).on('change', '#va-clothing-size-system', function(){
        rebuildClothingSizeOptions();
    });
    $(document).on('change', '#va-clothing-type', function(){
        applyOtherClothingDynamicFields();
    });

    $(document).on('change', '#va-other-weapon-kind', function(){
        applyCategorySpecificFieldVisibility();
    });
    $(document).on('change', '#va-vadcamera-camera-type', function(){
        applyVadcameraDynamicFields();
    });
    $(document).on('change', '#va-shoe-main-type', function(){
        applyShoeDynamicFields();
    });
    $(document).on('change', '#va-marok-type, #va-marok-optic-ready', function(){
        applyHandgunDynamicFields();
    });
    $(document).on('change', '#va-mixed-type, #va-mixed-optic-compatible, #va-mixed-usage', function(){
        applyMixedRifleDynamicFields();
    });
    $(document).on('change', '#va-rifle-type', function(){
        applyRifleDynamicFields();
    });
    $(document).on('change', '#va-accommodation-type', function(){
        applyAccommodationDynamicFields();
    });
    $(document).on('change', '#va-trophy-custom-made', function(){
        applyTrophyDynamicFields();
    });
    $(document).on('change', '#va-hunt-price-type, #va-hunt-type, #va-hunt-methods', function(){
        applyHuntDynamicFields();
    });
    $(document).on('change', '#va-hunt-land-type, #va-hunt-response-time, #va-hunt-contract-type', function(){
        applyVadkarDynamicFields();
    });
    $(document).on('change', '#va-exchange-offer-category', function(){
        applyExchangeDynamicFields();
    });
    $(document).on('change', '#va-publication-type, #va-publication-edition-type, #va-publication-signed-copy', function(){
        applyPublicationDynamicFields();
    });
    $(document).on('change', '#va-equipment-type, #va-equipment-call-type, #va-equipment-electronic-features', function(){
        applyEquipmentDynamicFields();
    });
    $(document).on('change', '#va-decor-type, #va-decor-material', function(){
        applyDecorDynamicFields();
    });
    $(document).on('change input', '#va-dog-birth-date, #va-dog-training-level, #va-dog-working-type', function(){
        applyDogDynamicFields();
    });
    $(document).on('click', function(e){
        if ($(e.target).closest('.va-dark-date').length) {
            return;
        }
        closeDarkDatePickers();
    });

    rebuildHuntingBrandModelDatalists(false);
    applyLearnedCaliberDatalist();
    initDarkDatePickers();
    rebuildClothingSizeOptions();
    var initialOpticType = $('#va-optic-type').val() || 'celtavcso';
    rebuildOpticZoomOptions(initialOpticType);
    (function(){
        var $list = $('#va-title-list');
        if (!$list.length) return;
        vaUniqueStrings(vaGetLearnedTerms('title', '')).forEach(function(t){
            $('<option>').attr('value', t).appendTo($list);
        });
    })();
    applyCategorySpecificFieldVisibility();
    applyVadcameraDynamicFields();
    applyShoeDynamicFields();
    applyHandgunDynamicFields();
    applyMixedRifleDynamicFields();
    applyRifleDynamicFields();
    applyAccommodationDynamicFields();
    applyTrophyDynamicFields();
    applyHuntDynamicFields();
    applyVadkarDynamicFields();
    applyExchangeDynamicFields();
    applyPublicationDynamicFields();
    applyEquipmentDynamicFields();
    applyDecorDynamicFields();
    applyDogDynamicFields();
    applyOtherClothingDynamicFields();
    updateStep2CategoryLabel();
    applyVehicleCategoryVisibility();
    switchBrandModelFieldMode();

    /* ══ Utca autocomplete (3+ karakter) ═══════════════ */
    var addressTimer = null;
    var streetMetaByLabel = {};

    function getLearnedStreetSuggestionItems(q, limit) {
        var needle = vaNormalizeAddressValue(q || '');
        if (!needle) return [];
        var learned = vaGetLearnedTerms('street', '');
        var out = [];
        learned.forEach(function(street) {
            if (out.length >= limit) return;
            if (vaNormalizeAddressValue(street).indexOf(needle) === -1) return;
            out.push({
                label: street,
                street: street,
                city: '',
                postal_code: ''
            });
        });
        return out;
    }

    function mergeStreetSuggestionItems(primary, secondary) {
        var seen = {};
        var out = [];
        (primary || []).concat(secondary || []).forEach(function(item) {
            if (!item || !item.label) return;
            var key = vaNormalizeAddressValue(item.label);
            if (!key || seen[key]) return;
            seen[key] = true;
            out.push(item);
        });
        return out;
    }

    function renderStreetSuggestions(items) {
        var $dl = $('#va-street-list');
        if (!$dl.length) return;
        $dl.empty();
        streetMetaByLabel = {};

        (items || []).forEach(function(it){
            if (!it || !it.label) return;
            streetMetaByLabel[it.label] = it;
            $('<option>').attr('value', it.label).appendTo($dl);
        });
    }

    function fetchStreetSuggestions(q) {
        var learnedItems = getLearnedStreetSuggestionItems(q, 10);
        if (!VA_Data || !VA_Data.nonce_address) {
            renderStreetSuggestions(learnedItems);
            return;
        }
        $.post(VA_Data.ajax_url, {
            action: 'va_address_suggest',
            nonce: VA_Data.nonce_address,
            q: q,
            limit: 12
        }).done(function(res){
            if (res && res.success && Array.isArray(res.data)) {
                renderStreetSuggestions(mergeStreetSuggestionItems(res.data, learnedItems));
            } else {
                renderStreetSuggestions(learnedItems);
            }
        }).fail(function(){
            renderStreetSuggestions(learnedItems);
        });
    }

    $(document).on('input', 'input[name="street"], input[name="location"]', function(){
        var q = (($(this).val() || '') + '').trim();
        if (q.length < 2) {
            renderStreetSuggestions([]);
            return;
        }
        clearTimeout(addressTimer);
        addressTimer = setTimeout(function(){ fetchStreetSuggestions(q); }, 180);
    });

    $(document).on('change blur', 'input[name="street"], input[name="location"]', function(){
        var v = (($(this).val() || '') + '').trim();
        var selected = streetMetaByLabel[v] || null;
        if (!selected) return;
        $('input[name="location"]').val(selected.city || '');
        if (!$('input[name="postal_code"]').val()) $('input[name="postal_code"]').val(selected.postal_code || '');
        if (selected.street) $('input[name="street"]').val(selected.street);
    });

    function validateCategoryRequiredFields() {
        var $cat = $('#va-category');
        if (!$cat.length || typeof VA_Data === 'undefined') return '';

        var selected = $cat.find('option:selected');
        var slug = (selected.data('slug') || '').toString();
        var selectedCatText = ((selected.text() || '') + '').toLowerCase();
        if (!slug) return '';

        var rules = VA_Data.category_required_rules || {};
        var rule = rules[slug] || null;
        var isJobCategorySlug = ['allas', 'allas-hirdetes', 'munka', 'munkak', 'munkalehetoseg', 'munkalehetosegek'].indexOf(slug) !== -1;
        var isJobCategoryText = /(állás|allas|munka|pozíció|pozicio)/.test(selectedCatText) && !/(szállás|szallas)/.test(selectedCatText);
        var isJobCategory = isJobCategorySlug || isJobCategoryText;
        if (!rule && !isJobCategory) return '';
        if (!rule) {
            rule = { label: 'Állás', required: [] };
        }
        if (!Array.isArray(rule.required)) {
            rule.required = [];
        }
        if (isJobCategory) {
            if (rule.required.indexOf('job_location') === -1) rule.required.push('job_location');
            if (rule.required.indexOf('job_type') === -1) rule.required.push('job_type');
        }
        if (!rule.required.length) return '';

        var labels = {
            brand: 'Márka / gyártó',
            model: 'Modell / típus',
            caliber: 'Kaliber',
            rifle_type: 'Típus',
            rifle_caliber: 'Golyós kaliber',
            rifle_barrel_length_cm: 'Csőhossz (cm)',
            rifle_condition: 'Állapot',
            rifle_optic_compatibility: 'Optika kompatibilitás',
            other_weapon_kind: 'Egyéb fegyverek kategória',
            hatastalanitott_weapon_type: 'Fegyver típusa',
            hatastalanitott_is_deactivated: 'Hatástalanított?',
            hatastalanitott_deactivation_type: 'Hatástalanítás típusa',
            hatastalanitott_certificate: 'Tanúsítvány van?',
            hatastalanitott_mkh_cip: 'MKH / CIP jelölés?',
            hatastalanitott_condition: 'Állapot',
            loszer_category: 'Kategória',
            loszer_condition: 'Állapot',
            optic_type: 'Távcső típusa',
            optic_zoom: 'Nagyítás',
            optic_objective: 'Objektív átmérő (mm)',
            thermal_device_type: 'Hőkamera típus',
            thermal_sensor_resolution: 'Szenzor felbontás',
            thermal_netd: 'NETD érzékenység',
            thermal_refresh_hz: 'Frissítési frekvencia',
            scope_type: 'Típus (Monokuláris/Biokuláris)',
            scope_coatings: 'Bevonatok',
            twilight_value: 'Szürkületi érték',
            scope_accessories: 'Tartozékok',
            dog_age_months: 'Kutya életkor (hónap)',
            dog_breed: 'Kutya fajtája',
            dog_gender: 'Neme',
            dog_color: 'Színe',
            dog_purebred: 'Fajtatisztaság',
            job_location: 'Munkavégzés települése',
            job_type: 'Foglalkoztatás típusa',
            clothing_type: 'Ruhatípus',
            clothing_condition: 'Állapot',
            clothing_gender: 'Nem',
            clothing_size_system: 'Méretrendszer',
            clothing_size: 'Ruhaméret',
            knife_type: 'Kés típusa',
            knife_blade_length: 'Penge hossza (cm)',
            trophy_type: 'Trófeaalátét típusa',
            trophy_maker: 'Készítő / márka',
            trophy_custom_made: 'Kézzel készített',
            trophy_condition: 'Állapot',
            trophy_manufacture_year: 'Gyártási év',
            trophy_height_cm: 'Magasság (cm)',
            trophy_width_cm: 'Szélesség (cm)',
            trophy_thickness_cm: 'Vastagság (cm)',
            trophy_compatible_species: 'Trófea kompatibilitás',
            trophy_species: 'Vadfaj',
            trophy_mount_type: 'Trófeaalátét típus',
            trophy_mounting_type: 'Rögzítés típusa',
            trophy_attachment_type: 'Trófea rögzítés',
            trophy_style: 'Forma / stílus',
            trophy_fang_mount: 'Agyarfoglalat kivitel',
            trophy_material_type: 'Alapanyag',
            trophy_material: 'Anyag',
            trophy_surface_finish: 'Felületkezelés',
            trophy_finish: 'Felületkezelés',
            trophy_decoration_flags: 'Díszítés',
            trophy_weight_kg: 'Súly (kg)',
            trophy_usage_context: 'Felhasználás',
            trophy_accessories: 'Tartozékok',
            trophy_price_negotiable: 'Ár: alku',
            trophy_custom_order: 'Egyedi rendelés',
            trophy_shipping_methods: 'Szállítás',
            trophy_fragile_handling: 'Törékeny kezelés',
            trophy_required_media: 'Kötelező képek',
            trophy_lead_time: 'Elkészítési idő',
            trophy_orderable_sizes: 'Rendelhető méret',
            trophy_custom_engraving: 'Egyedi gravírozás',
            trophy_house_style: 'Vadászház stílus illesztés',
            trophy_moderation_flags: 'Moderációs jelölők',
            accommodation_type: 'Szállás típusa',
            accommodation_capacity: 'Férőhely (fő)',
            accommodation_hunting_nearby: 'Vadászterület közelében?',
            accommodation_hunt_available: 'Szervezett vadászat elérhető?',
            hunt_slot_from_hour: 'Idősáv kezdete (óra)',
            hunt_slot_to_hour: 'Idősáv vége (óra)',
            hunt_capacity: 'Létszám / fő',
            hunt_species_list: 'Vadászható fajok listája',
            hunt_lease_type: 'Lesbérleti / hozzáférési forma',
            hunt_type: 'Vadászat típusa',
            hunt_game_species: 'Vadfaj',
            hunt_country: 'Ország',
            hunt_county: 'Megye',
            hunt_price_type: 'Ár típusa',
            hunt_price_min: 'Ár minimum',
            hunt_methods: 'Vadászati módszer',
            hunt_guide_type: 'Kíséret típusa',
            hunt_difficulty_level: 'Nehézségi szint',
            hunt_season_start: 'Kezdő dátum',
            hunt_season_end: 'Záró dátum',
            hunt_damage_main_type: 'Vadkár elhárítás fő típusa',
            hunt_settlement: 'Település',
            hunt_land_type: 'Terület típusa',
            hunt_response_time: 'Reakcióidő',
            hunt_availability_mode: 'Elérhetőség',
            hunt_executor_type: 'Végrehajtó',
            hunt_executor_capacity: 'Kapacitás',
            hunt_tools: 'Eszközök',
            hunt_weapon_use_required: 'Fegyverhasználat szükséges',
            hunt_land_manager_permit: 'Területgazdálkodói engedély',
            hunt_hunting_company_coop: 'Együttműködés vadásztársasággal',
            hunt_liability_insurance: 'Felelősségbiztosítás',
            hunt_damage_nature: 'Kár jellege',
            hunt_pricing_model: 'Árazás modell',
            hunt_travel_fee: 'Kiszállás díja',
            hunt_contract_type: 'Szerződés típusa',
            hunt_site_conditions: 'Helyszíni feltételek',
            vadcamera_camera_type: 'Kamera típusa',
            vadcamera_connectivity: 'Adatkapcsolat',
            vadcamera_trigger_speed: 'Trigger idő',
            vadcamera_ir_type: 'Infra típusa',
            vadcamera_night_range_m: 'Éjszakai hatótáv (m)',
            vadcamera_video_resolution: 'Videó felbontás',
            vadcamera_ip_rating: 'IP védelem',
            vadcamera_sim_slot: 'SIM kártyás?',
            shoe_main_type: 'Típus',
            shoe_eu_size: 'EU méret',
            shoe_condition: 'Állapot',
            shoe_boot_height: 'Szármagasság',
            estate_main_type: 'Hagyaték fő típusa',
            knife_condition: 'Állapot',
            knife_blade_length_mm: 'Pengehossz (mm)',
            knife_steel_type: 'Acél',
            marok_type: 'Maroklőfegyver típusa',
            marok_condition: 'Állapot',
            marok_action_type: 'Működési rendszer',
            marok_magazine_capacity: 'Tárkapacitás',
            marok_license_required: 'Engedélyköteles',
            marok_legal_category: 'Jogi kategória',
            marok_cip_marking: 'MKH / CIP jelölés',
            marok_transfer_license_only: 'Csak engedéllyel átvehető',
            dog_working_type: 'Vadászkutya típus',
            dog_breed: 'Fajta',
            dog_gender: 'Neme',
            dog_birth_date: 'Születési dátum',
            dog_hunting_specialization: 'Vadászati specializáció',
            dog_training_level: 'Kiképzés szintje',
            exchange_type: 'Csere típusa',
            exchange_offer_category: 'Fő kategória (mit kínál?)',
            exchange_brand: 'Márka',
            exchange_condition: 'Állapot',
            exchange_estimated_value: 'Becsült érték (Ft)',
            exchange_extra_payment_direction: 'Ráfizetés',
            exchange_county: 'Megye',
            publication_type: 'Típus',
            publication_topics: 'Témakör',
            publication_title: 'Cím',
            publication_author: 'Szerző',
            publication_year: 'Kiadás éve',
            publication_language: 'Nyelv',
            publication_condition: 'Állapot',
            publication_collector_value: 'Gyűjtői érték',
            publication_complete_volume: 'Komplett évfolyam',
            equipment_type: 'Felszerelés típusa',
            equipment_condition: 'Állapot',
            equipment_hunting_usage: 'Felhasználás',
            decor_type: 'Dísztárgy típusa',
            decor_material: 'Anyag',
            decor_style: 'Stílus',
            decor_motif_tags: 'Motívum',
            decor_production_type: 'Készítés',
            decor_size_class: 'Méret kategória',
            decor_condition: 'Állapot',
        };
        var missing = [];

        rule.required.forEach(function(field){
            var $el = $( '[name="' + field + '"]' );
            if (!$el.length) {
                $el = $( '[name="' + field + '[]"]' );
            }
            var v = (($el.val() || '') + '').trim();
            if (!v) {
                missing.push(labels[field] || field);
            }
        });

        if (!missing.length) return '';
        return (rule.label || 'A választott kategória') + ' kategóriában kötelező: ' + missing.join(', ') + '.';
    }

    function validateVehicleRequiredFields() {
        if (!isVehicleCategorySelected()) return '';

        var requiredVehicleFields = [
            { name: 'brand', label: 'Márka / gyártó' },
            { name: 'model', label: 'Modell / típus' },
            { name: 'year', label: 'Gyártási év' },
            { name: 'mileage', label: 'Futásteljesítmény (km)' },
            { name: 'fuel_type', label: 'Üzemanyag' },
            { name: 'transmission', label: 'Sebességváltó' },
            { name: 'drive', label: 'Hajtás' },
            { name: 'vehicle_condition', label: 'Jármű állapota' },
            { name: 'performance_kw', label: 'Teljesítmény (kW)' },
            { name: 'engine_size', label: 'Hengerűrtartalom (cm³)' }
        ];

        var missing = [];
        requiredVehicleFields.forEach(function(field){
            var v = (($( '[name="' + field.name + '"]' ).val() || '') + '').trim();
            if (!v) {
                missing.push(field.label);
            }
        });

        if (!missing.length) return '';
        return 'Jármű hirdetéshez kötelező: ' + missing.join(', ') + '.';
    }

    function applyCategorySpecificFieldVisibility() {
        if (typeof VA_Data === 'undefined' || VA_Data.site_type === 'jarmu') return;
        var slug = getSelectedCategorySlug();
        var selectedCatText = (($('.va-cat-item[data-selected="1"]').text() || '') + '').toLowerCase();
        var isBowCategory = /(^|-)ij($|-)|szamszerij|ijvesszo|fuvocso|nyilpisztoly|nyilpuska/.test(slug)
            || /(íj|ij|számszeríj|szamszerij)/.test(selectedCatText);
        var isJobCategorySlug = ['allas', 'allas-hirdetes', 'munka', 'munkak', 'munkalehetoseg', 'munkalehetosegek'].indexOf(slug) !== -1;
        var isJobCategoryText = /(állás|allas|munka|pozíció|pozicio)/.test(selectedCatText) && !/(szállás|szallas)/.test(selectedCatText);
        var isJobCategory = isJobCategorySlug || isJobCategoryText;
        var isServiceCategory = /szolgaltatas|szolg/.test(slug)
            || /(szolgáltatás|szolgaltatas)/.test(selectedCatText);
        var isDogCategory = /vadaszkutya|kutya/.test(slug)
            || /(vadászkutya|vadaszkutya|kutya)/.test(selectedCatText);
        var isTelescopeCategory = /tavcsovek|ejjellato-tavcso|hokamerak/.test(slug)
            || /(távcső|tavcso|éjjellátó|ejjellato|hőkamera|hokamera)/.test(selectedCatText);
        var isNonScopeTelescopeCategory = /ejjellato-tavcso|hokamerak/.test(slug)
            || /(éjjellátó|ejjellato|hőkamera|hokamera)/.test(selectedCatText);
        var isKnifeCategory = /kesek|bicska-tor-kard|vadaszkes-vadasztor|taktikai-kes-taktikai-tor|konyhakes|svajci-bicska|dobotor|machete-bozotvago|multiszerszam|tok-keslanc|keselezo/.test(slug)
            || /(kés|kes|bicska|tőr|tor|machete|bozótvágó|bozotvago|multiszerszám|multiszerszam)/.test(selectedCatText);
        var isOtherWeaponCategory = /egyeb-fegyverek/.test(slug)
            || /(egyéb fegyverek|egyeb fegyverek)/.test(selectedCatText);
        var isAccommodationCategory = /szallas|ingatlan|ingatlan-szallas/.test(slug)
            || /(ingatlan|szállás|szallas|booking|apartman|vendégház|vendeghaz|vadászház|vadaszhaz)/.test(selectedCatText);
        var isHuntOptionCategory = /vadaszati-lehetoseg|vadkarelharitas|vadaszterulet-berlet|vadasz-lehetoseg/.test(slug)
            || /(vadászati lehetőség|vadaszati lehetoseg|vadkárelhárítás|vadkarelharitas|lesbérlet|lesberlet|bérbeadás|berbeadas)/.test(selectedCatText);
        var isDamageControlCategory = /vadkarelharitas/.test(slug)
            || /(vadkárelhárítás|vadkarelharitas|vadkár elhárítás|vadkar elharitas)/.test(selectedCatText);
        var isTrophyPlateCategory = /trofea-aletet|trofea|trofeak/.test(slug)
            || /(trófea|trofea|alátét|alatet|agyar|koponya)/.test(selectedCatText);
        var isShoeCategory = /cipo|bakancs|labbeli/.test(slug)
            || /(cipő|cipo|bakancs|lábbeli|labbeli)/.test(selectedCatText);
        var isClothingCategory = /ruhazat/.test(slug)
            || /(ruházat|ruhazat|nadrág|nadrag|póló|polo|kabát|kabat)/.test(selectedCatText);
        var isHandgunCategory = /maroklofegyver/.test(slug)
            || /(maroklőfegyver|maroklofegyver|pisztoly|revolver)/.test(selectedCatText);
        var isRifleCategory = /golyos-puska/.test(slug)
            || /(golyós lőfegyver|golyos lofegyver|vadpuska|bolt action|semi-auto|precíziós puska|precizios puska)/.test(selectedCatText);
        var isExchangeCategory = /csere/.test(slug)
            || /(csere)/.test(selectedCatText);
        var isPublicationCategory = /konyv-folyoirat/.test(slug)
            || /(könyv|konyv|folyóirat|folyoirat|magazin|évkönyv|evkonyv)/.test(selectedCatText);
        var isDecorCategory = /disztargy/.test(slug)
            || /(dísztárgy|disztargy|dekor|falidísz|falidisz|relief|replika)/.test(selectedCatText);
        var rules = VA_Data.category_required_rules || {};
        var required = (rules[slug] && Array.isArray(rules[slug].required)) ? rules[slug].required : [];
        required = required.slice();
        if (isJobCategory) {
            if (required.indexOf('job_location') === -1) required.push('job_location');
            if (required.indexOf('job_type') === -1) required.push('job_type');
        }
        if (isServiceCategory) {
            ['service_type','service_provider_type','service_country','service_county','service_town','service_area','service_pricing_type'].forEach(function(field){
                if (required.indexOf(field) === -1) required.push(field);
            });
        }

        $('.va-core-product-fields, .va-core-product-year').toggle(!(isJobCategory || isServiceCategory || isDogCategory || isAccommodationCategory || isHuntOptionCategory || isExchangeCategory || isPublicationCategory || isDecorCategory));

        $('.va-exchange-fields-grid').toggle(isExchangeCategory);
        if (isExchangeCategory) {
            applyExchangeDynamicFields();
        }

        $('.va-publication-fields-grid').toggle(isPublicationCategory);
        if (isPublicationCategory) {
            applyPublicationDynamicFields();
        } else {
            $('.va-publication-dynamic-group').hide();
        }

        $('.va-decor-fields-grid').toggle(isDecorCategory);
        if (isDecorCategory) {
            applyDecorDynamicFields();
        } else {
            $('.va-decor-dynamic-group').hide();
        }

        if (isDogCategory) {
            applyDogDynamicFields();
        } else {
            $('.va-dog-dynamic-group').hide();
        }

        $('.va-service-fields-grid').toggle(isServiceCategory);
        if (isServiceCategory) {
            applyServiceDynamicFields();
        } else {
            $('.va-service-dynamic-group').hide();
        }

        $('.va-handgun-fields-grid').toggle(isHandgunCategory);
        if (isHandgunCategory) {
            applyHandgunDynamicFields();
        }

        $('.va-rifle-fields-grid').toggle(isRifleCategory);
        if (isRifleCategory) {
            applyRifleDynamicFields();
        } else {
            $('.va-rifle-dynamic-group').hide();
        }

        // Handle knife fields grid
        $('.va-knife-fields-grid').toggle(isKnifeCategory);
        if (isKnifeCategory) {
            var knifeType = (($( '[name="knife_type"]' ).val() || '') + '').trim();
            var isFoldingType = ['bicska', 'zsebkes'].indexOf(knifeType) !== -1;
            $('.va-knife-folding-only').toggle(isFoldingType);
            $('.va-knife-axe-only').toggle(knifeType === 'balta');
            $('.va-knife-multitool-only').toggle(knifeType === 'multitool');
        } else {
            $('.va-knife-folding-only, .va-knife-axe-only, .va-knife-multitool-only').hide();
        }

        // Handle other weapons fields grid
        $('.va-other-weapon-fields-grid').toggle(isOtherWeaponCategory);

        // Handle trophy mount fields grid
        $('.va-trophy-fields-grid').toggle(isTrophyPlateCategory);
        if (isTrophyPlateCategory) {
            applyTrophyDynamicFields();
        } else {
            $('.va-trophy-dynamic-group').hide();
        }

        // Handle accommodation fields grid
        $('.va-accommodation-fields-grid').toggle(isAccommodationCategory);
        if (isAccommodationCategory) {
            applyAccommodationDynamicFields();
        } else {
            $('.va-accommodation-dynamic-group').hide();
        }

        // Handle hunting-option fields grid
        $('.va-hunt-option-fields-grid').toggle(isHuntOptionCategory);
        $('.va-vadkar-only').toggle(isDamageControlCategory);
        if (isDamageControlCategory) {
            applyVadkarDynamicFields();
        } else {
            $('.va-vadkar-dynamic-group').hide();
        }

        // Handle telescope fields grid
        $('.va-telescope-fields-grid').toggle(isTelescopeCategory);

        if (isOtherWeaponCategory) {
            var otherWeaponKind = ($('#va-other-weapon-kind').val() || '').trim();
            $('.va-other-weapon-subgroup').each(function(){
                var kinds = (($(this).data('otherWeaponKinds') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                $(this).toggle(kinds.indexOf(otherWeaponKind) !== -1);
            });
        } else {
            $('.va-other-weapon-subgroup').hide();
        }

        if (isServiceCategory) {
            var serviceType = ($('#va-service-type').val() || '').trim();
            $('.va-service-dynamic-group').each(function(){
                var types = (($(this).data('serviceTypes') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                $(this).toggle(types.indexOf(serviceType) !== -1);
            });
        }
        
        // Hide spektiv option for non-scope telescope categories (hőkamera, éjjellátó)
        var $opticType = $('#va-optic-type');
        if (isNonScopeTelescopeCategory) {
            $opticType.find('option[value="spektiv"]').hide();
        } else if (isTelescopeCategory) {
            $opticType.find('option[value="spektiv"]').show();
        }
        
        // Hide optic_objective for non-tavcsovek telescope categories
        if (isTelescopeCategory && !/^tavcsovek$/.test(slug)) {
            $('.va-telescope-fields-grid .va-telescope-field[data-visibility="tavcsovek"]').hide();
        } else if (isTelescopeCategory) {
            $('.va-telescope-fields-grid .va-telescope-field[data-visibility="tavcsovek"]').show();
        }
        
        // Apply required attributes to telescope fields
        if (isTelescopeCategory) {
            $('.va-telescope-fields-grid input, .va-telescope-fields-grid select, .va-telescope-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim();
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        if (isServiceCategory) {
            $('.va-service-fields-grid input, .va-service-fields-grid select, .va-service-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        // Apply required attributes to trophy fields
        if (isTrophyPlateCategory) {
            $('.va-trophy-fields-grid input, .va-trophy-fields-grid select, .va-trophy-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        // Apply required attributes to accommodation fields
        if (isAccommodationCategory) {
            $('.va-accommodation-fields-grid input, .va-accommodation-fields-grid select, .va-accommodation-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        // Apply required attributes to hunting-option fields
        if (isHuntOptionCategory) {
            $('.va-hunt-option-fields-grid input, .va-hunt-option-fields-grid select, .va-hunt-option-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        if (isKnifeCategory) {
            $('.va-knife-fields-grid input, .va-knife-fields-grid select, .va-knife-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        if (isHandgunCategory) {
            $('.va-handgun-fields-grid input, .va-handgun-fields-grid select, .va-handgun-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        if (isRifleCategory) {
            $('.va-rifle-fields-grid input, .va-rifle-fields-grid select, .va-rifle-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        if (isPublicationCategory) {
            $('.va-publication-fields-grid input, .va-publication-fields-grid select, .va-publication-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        $('.va-cat-rule-field').each(function(){
            var $wrap = $(this);
            var list = (($wrap.data('categories') || '') + '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            var visible = list.indexOf(slug) !== -1;
            if (!visible && isBowCategory && $wrap.find('input[name="license_req"]').length) {
                visible = true;
            }
            if (!visible && (isJobCategory || isServiceCategory) && $wrap.find('[name="job_location"], [name="job_type"]').length) {
                visible = true;
            }
            if (!visible && isShoeCategory && $wrap.find('[name="shoe_size"]').length) {
                visible = true;
            }
            if (!visible && isClothingCategory && $wrap.find('[name="clothing_type"], [name="clothing_size_system"], [name="clothing_size"]').length) {
                visible = true;
            }
            if (!visible && isDogCategory && $wrap.find('[name="dog_breed"], [name="dog_gender"], [name="dog_color"], [name="dog_purebred"], [name="dog_age_months"]').length) {
                visible = true;
            }
            $wrap.toggle(visible);

            $wrap.find('input,select,textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim().replace(/\[\]$/, '');
                var requiredHere = visible && required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        });
        
        // Apply required attributes to telescope fields
        if (isTelescopeCategory) {
            $('.va-telescope-fields-grid input, .va-telescope-fields-grid select, .va-telescope-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim();
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }
    }

    $(document).on('change', '[name="knife_type"]', function(){
        applyCategorySpecificFieldVisibility();
    });

    /* ══ Form submit ═════════════════════════════════════ */
    $('#va-submit-btn').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        console.log('✓ Submit button clicked');
        var $btn    = $(this);
        var editMode = !! VA_Data.edit_mode;
        console.log('✓ Handler initialized, editMode:', editMode);

        var categoryRuleError = validateCategoryRequiredFields();
        if (categoryRuleError) {
            $('#va-submit-notice-content').html('<div class="va-notice va-notice--error" style="margin:0;">' + $('<div>').text(categoryRuleError).html() + '</div>');
            openSubmitNoticeModal();
            return;
        }

        var vehicleRuleError = validateVehicleRequiredFields();
        if (vehicleRuleError) {
            $('#va-submit-notice-content').html('<div class="va-notice va-notice--error" style="margin:0;">' + $('<div>').text(vehicleRuleError).html() + '</div>');
            openSubmitNoticeModal();
            return;
        }

        var selectedSlug = getSelectedCategorySlug();
        if (selectedSlug === 'egyeb' && !(($('#va-other-category').val() || '') + '').trim()) {
            openOtherCategoryModal();
            return;
        }

        var city = (($('input[name="location"]').val() || '') + '').trim();
        var zip  = (($('input[name="postal_code"]').val() || '') + '').replace(/\D+/g, '');
        if (!city && !zip) {
            $('#va-submit-notice-content').html('<div class="va-notice va-notice--error" style="margin:0;">Adja meg a várost vagy az irányítószámot.</div>');
            openSubmitNoticeModal();
            return;
        }

        $('input[name="postal_code"]').val(zip);
        $btn.prop('disabled', true).text('Feltöltés...');

        // Base64 képek feltöltése médiatárba, majd submit
        var imgs = quill.root.querySelectorAll('img[src^="data:"]');
        var uploads = [];
        imgs.forEach(function(img) {
            uploads.push($.ajax({
                url: VA_Data.ajax_url,
                type: 'POST',
                data: { action: 'va_upload_editor_image', nonce: VA_Data.nonce_editor_img, post_id: VA_Data.post_id || 0, data_url: img.src },
                success: function(res) { if (res.success) img.src = res.data.url; }
            }));
        });

        $.when.apply($, uploads.length ? uploads : [$.Deferred().resolve()]).always(function(){
            // Quill tartalom szinkronizálása a hidden textarea-ba submit előtt
            $('#va-desc-hidden').val(quill.root.innerHTML);

        var $form = $('#va-submit-form');
        var formData = new FormData($form[0]);

        // Csak az új (File objektumos) képek feltöltése
        _files.forEach(function(item){
            if (item.file) {
                formData.append('listing_images[]', item.file, item.file.name);
            }
        });

        // Featured kép: meglévő ID vagy index az új képek között
        var featItem = _files[_featured];
        if (featItem && featItem.existing_id) {
            formData.set('featured_existing_id', featItem.existing_id);
            formData.set('featured_image_index', -1);
        } else {
            // Hány meglévő kép van előtte?
            var newIdx = 0;
            for (var i = 0; i < _featured; i++) {
                if (!_files[i].existing_id) newIdx++;
            }
            formData.set('featured_image_index', newIdx);
        }

        $.ajax({
            url:         VA_Data.ajax_url,
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            beforeSend: function(){ console.log('Ajax request sending to:', VA_Data.ajax_url); },
            success: function(res){
                console.log('Ajax success response:', res);
                $btn.prop('disabled', false).text(editMode ? '💾 Változások mentése' : '📤 Hirdetés feladása');
                if(res.success){
                    $('#va-submit-notice-content').html('<div class="va-notice va-notice--success" style="margin:0;">' + res.data.message + '</div>');
                    openSubmitNoticeModal();
                    if (typeof window.va_toast === 'function') {
                        window.va_toast(res.data.message || 'Mentés sikeres.', 'success');
                    }
                    if(res.data.permalink){
                        setTimeout(function(){
                            closeSubmitNoticeModal();
                            window.location.href = res.data.permalink;
                        }, 2000);
                    }
                } else {
                    if (res.data && res.data.need_credits) {
                        var price = res.data.paid_price ? Number(res.data.paid_price).toLocaleString('hu-HU') + ' Ft' : '';
                        var buyPage = '<?php echo esc_js( $buy_url_submit ); ?>';
                        var html = '<div class="va-notice va-notice--warning" style="margin:0;padding:0;">'
                            + '<strong>Elfogyott az ingyenes hirdetési kereted.</strong><br>'
                            + (price ? 'Egy hirdetés ára: <strong>' + price + '</strong><br>' : '')
                            + '<a href="' + buyPage + '" class="va-btn va-btn--primary" style="margin-top:12px;display:inline-flex;">🛒 Hirdetési csomag vásárlása</a>'
                            + '</div>';
                        $('#va-submit-notice-content').html(html);
                        openSubmitNoticeModal();
                        if (typeof window.va_toast === 'function') {
                            window.va_toast('Elfogyott az ingyenes keret. Csomag vásárlás szükséges.', 'error');
                        }
                    } else if (res.data && res.data.payment_required && res.data.payment_url) {
                        var amount = res.data.amount ? Number(res.data.amount).toLocaleString('hu-HU') + ' Ft' : '';
                        var html2 = '<div class="va-notice va-notice--warning" style="margin:0;padding:0;">'
                            + res.data.message
                            + (amount ? '<br><strong>Fizetendő: ' + amount + '</strong>' : '')
                            + '<br><a href="' + res.data.payment_url + '" class="va-btn va-btn--primary" style="margin-top:10px;display:inline-flex;">Bankkártyás fizetés</a>'
                            + '</div>';
                        $('#va-submit-notice-content').html(html2);
                        openSubmitNoticeModal();
                        if (typeof window.va_toast === 'function') {
                            window.va_toast(res.data.message || 'Fizetés szükséges a folytatáshoz.', 'error');
                        }
                    } else {
                        $('#va-submit-notice-content').html('<div class="va-notice va-notice--error" style="margin:0;">' + res.data.message + '</div>');
                        openSubmitNoticeModal();
                        if (typeof window.va_toast === 'function') {
                            window.va_toast((res.data && res.data.message) ? res.data.message : 'Mentési hiba történt.', 'error');
                        }
                    }
                }
            },
            error: function(xhr, status, error){
                console.error('Ajax error:', {status: xhr.status, statusText: xhr.statusText, error: error, response: xhr.responseText});
                $btn.prop('disabled', false).text('📤 Hirdetés feladása');
                $('#va-submit-notice-content').html('<div class="va-notice va-notice--error" style="margin:0;">Hálózati hiba. Próbálja újra.</div>');
                openSubmitNoticeModal();
            }
        }); // $.ajax end
        }); // $.when end
    }); // button click end
})(jQuery);
}); // DOMContentLoaded
</script>


