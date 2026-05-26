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
    'golyos-puska'       => [ 'label' => 'Golyós lőfegyver', 'required' => [ 'brand', 'caliber' ] ],
    'soretes-puska'      => [ 'label' => 'Sörétes lőfegyver', 'required' => [ 'brand', 'caliber' ] ],
    'vegyescsovu-puska'  => [ 'label' => 'Vegyescsövű lőfegyver', 'required' => [ 'brand', 'caliber' ] ],
    'maroklofegyver'     => [ 'label' => 'Maroklőfegyver', 'required' => [ 'brand', 'caliber' ] ],
    'hatastalanitott'    => [ 'label' => 'Hatástalanított lőfegyver', 'required' => [ 'brand', 'model' ] ],
    'egyeb-fegyverek'    => [ 'label' => 'Egyéb fegyverek', 'required' => [ 'brand' ] ],
    'loszer-tolteny'     => [ 'label' => 'Lőszer-Töltény', 'required' => [ 'brand', 'caliber' ] ],
    'tavcsovek'          => [ 'label' => 'Távcsövek', 'required' => [ 'optic_type', 'brand', 'model', 'optic_zoom', 'optic_objective' ] ],
    'ejjellato-tavcso'   => [ 'label' => 'Éjjellátó távcső', 'required' => [ 'brand', 'model', 'optic_zoom', 'scope_type', 'twilight_value' ] ],
    'hokamerak'          => [ 'label' => 'Hőkamerák', 'required' => [ 'brand', 'model', 'optic_zoom', 'scope_type' ] ],
    'vadkamera'          => [ 'label' => 'Vadkamera', 'required' => [ 'brand', 'model' ] ],
    'vadaszlampa'        => [ 'label' => 'Vadászlámpa', 'required' => [ 'brand', 'model' ] ],
    'vadaszkutya'        => [ 'label' => 'Vadászkutya', 'required' => [ 'dog_breed', 'dog_gender', 'dog_color', 'dog_purebred' ] ],
    'vadasz-ruhazat'     => [ 'label' => 'Vadász ruházat', 'required' => [ 'brand', 'clothing_type', 'clothing_size_system', 'clothing_size' ] ],
    'egyeb-ruhazat'      => [ 'label' => 'Egyéb ruházat', 'required' => [ 'clothing_type', 'clothing_size_system', 'clothing_size' ] ],
    'cipo-bakancs'       => [ 'label' => 'Cipő, bakancs', 'required' => [ 'brand' ] ],
    'allas'              => [ 'label' => 'Állás', 'required' => [ 'job_location', 'job_type' ] ],
    'allas-hirdetes'     => [ 'label' => 'Állás', 'required' => [ 'job_location', 'job_type' ] ],
    'szolgaltatas'       => [ 'label' => 'Szolgáltatás', 'required' => [ 'job_location', 'job_type' ] ],
    'szallas'            => [ 'label' => 'Szállás', 'required' => [ 'accommodation_type', 'accommodation_capacity', 'accommodation_slot_mode', 'accommodation_from_hour', 'accommodation_to_hour' ] ],
    'vadaszati-lehetoseg'=> [ 'label' => 'Vadászati lehetőség', 'required' => [ 'hunt_slot_from_hour', 'hunt_slot_to_hour', 'hunt_capacity', 'hunt_species_list', 'hunt_lease_type' ] ],
    'vadkarelharitas'    => [ 'label' => 'Vadkárelhárítás', 'required' => [ 'hunt_slot_from_hour', 'hunt_slot_to_hour', 'hunt_capacity', 'hunt_species_list', 'hunt_lease_type' ] ],
    'trofea-aletet'      => [ 'label' => 'Trófeaalátét', 'required' => [ 'trophy_species', 'trophy_mount_type', 'trophy_style', 'trophy_material', 'trophy_finish' ] ],
    'vadasz-felszereles' => [ 'label' => 'Vadász felszerelés', 'required' => [ 'brand' ] ],
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
            'dog_age_months' => get_post_meta( $maybe_id, 'va_dog_age_months', true ),
            'dog_breed' => get_post_meta( $maybe_id, 'va_dog_breed', true ),
            'dog_gender' => get_post_meta( $maybe_id, 'va_dog_gender', true ),
            'dog_color' => get_post_meta( $maybe_id, 'va_dog_color', true ),
            'dog_purebred' => get_post_meta( $maybe_id, 'va_dog_purebred', true ),
            'shoe_size'   => get_post_meta( $maybe_id, 'va_shoe_size',   true ),
            'clothing_type' => get_post_meta( $maybe_id, 'va_clothing_type', true ),
            'clothing_size_system' => get_post_meta( $maybe_id, 'va_clothing_size_system', true ),
            'clothing_size' => get_post_meta( $maybe_id, 'va_clothing_size', true ),
            'knife_type' => get_post_meta( $maybe_id, 'va_knife_type', true ),
            'knife_blade_length' => get_post_meta( $maybe_id, 'va_knife_blade_length', true ),
            'trophy_species' => get_post_meta( $maybe_id, 'va_trophy_species', true ),
            'trophy_mount_type' => get_post_meta( $maybe_id, 'va_trophy_mount_type', true ),
            'trophy_style' => get_post_meta( $maybe_id, 'va_trophy_style', true ),
            'trophy_fang_mount' => get_post_meta( $maybe_id, 'va_trophy_fang_mount', true ),
            'trophy_material' => get_post_meta( $maybe_id, 'va_trophy_material', true ),
            'trophy_finish' => get_post_meta( $maybe_id, 'va_trophy_finish', true ),
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
            'exchange_target' => get_post_meta( $maybe_id, 'va_exchange_target', true ),
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
            'year'        => get_post_meta( $maybe_id, 'va_year',        true ),
            'license_req' => get_post_meta( $maybe_id, 'va_license_req', true ),
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
    'vadasz-felszereles' => '🎒', 'egyeb' => '📦',
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
            <div class="va-form-group va-cat-rule-field" data-categories="csere">
                <label>Mire szeretném cserélni</label>
                <input type="text" name="exchange_target" class="va-input" placeholder="pl. távcső, bakancs, jármű" value="<?php echo esc_attr((string)($edit_meta['exchange_target'] ?? '')); ?>">
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
                        <label class="va-check-label"><input type="checkbox" name="feed_game_boar" value="1"<?php echo (($edit_meta['feed_game_boar'] ?? '') === '1') ? ' checked' : ''; ?>> Vaddisznó</label>
                        <label class="va-check-label"><input type="checkbox" name="feed_game_roe" value="1"<?php echo (($edit_meta['feed_game_roe'] ?? '') === '1') ? ' checked' : ''; ?>> Őz</label>
                        <label class="va-check-label"><input type="checkbox" name="feed_game_red_deer" value="1"<?php echo (($edit_meta['feed_game_red_deer'] ?? '') === '1') ? ' checked' : ''; ?>> Szarvas</label>
                        <label class="va-check-label"><input type="checkbox" name="feed_game_pheasant" value="1"<?php echo (($edit_meta['feed_game_pheasant'] ?? '') === '1') ? ' checked' : ''; ?>> Fácán</label>
                        <label class="va-check-label"><input type="checkbox" name="feed_game_other" value="1"<?php echo (($edit_meta['feed_game_other'] ?? '') === '1') ? ' checked' : ''; ?>> Egyéb</label>
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
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_sight" value="1"<?php echo (($edit_meta['bow_acc_sight'] ?? '') === '1') ? ' checked' : ''; ?>> Irányzék</label>
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_release" value="1"<?php echo (($edit_meta['bow_acc_release'] ?? '') === '1') ? ' checked' : ''; ?>> Kioldó</label>
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_stabilizer" value="1"<?php echo (($edit_meta['bow_acc_stabilizer'] ?? '') === '1') ? ' checked' : ''; ?>> Stabilizátor</label>
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_arrows" value="1"<?php echo (($edit_meta['bow_acc_arrows'] ?? '') === '1') ? ' checked' : ''; ?>> Vesszők</label>
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_case" value="1"<?php echo (($edit_meta['bow_acc_case'] ?? '') === '1') ? ' checked' : ''; ?>> Tok</label>
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_quiver" value="1"<?php echo (($edit_meta['bow_acc_quiver'] ?? '') === '1') ? ' checked' : ''; ?>> Tegez</label>
                        <label class="va-check-label"><input type="checkbox" name="bow_acc_target" value="1"<?php echo (($edit_meta['bow_acc_target'] ?? '') === '1') ? ' checked' : ''; ?>> Céltábla</label>
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
            <?php if ( $site_type !== 'jarmu' ): ?>
            <div class="va-form-row">
                <div class="va-form-group va-cat-rule-field" data-categories="golyos-puska,soretes-puska,vegyescsovu-puska,maroklofegyver,hatastalanitott,loszer-tolteny,egyeb-fegyverek">
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
            </div>
            <datalist id="va-caliber-list"></datalist>
            <div class="va-form-group va-cat-rule-field" data-categories="cipo-bakancs,bakancs-felcipo,ruhazat-labbeli">
                <label>Cipőméret</label>
                <div class="va-year-input-wrap">
                    <input type="text" name="shoe_size" id="va-shoe-size-input" class="va-input" placeholder="pl. EU 43" readonly value="<?php echo esc_attr((string)($edit_meta['shoe_size'] ?? '')); ?>">
                    <button type="button" class="va-year-open-btn" id="va-shoe-size-open">Mérettáblázat</button>
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
                                'nadrag' => 'Nadrág',
                                'polo' => 'Póló',
                                'ing' => 'Ing',
                                'pulover' => 'Pulóver',
                                'melleny' => 'Mellény',
                                'kabat' => 'Kabát',
                                'dzseki' => 'Dzseki',
                                'overal' => 'Overál',
                                'kamasli' => 'Kamásli',
                                'esoruha' => 'Esőruházat',
                                'vadasz-kalap' => 'Vadász kalap',
                                'sapka' => 'Sapka',
                                'kesztyu' => 'Kesztyű',
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
                    <div class="va-form-group">
                        <label>Ruhaméret</label>
                        <select name="clothing_size" id="va-clothing-size" class="va-select" data-selected="<?php echo esc_attr((string)($edit_meta['clothing_size'] ?? '')); ?>">
                            <option value="">– Válasszon –</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="va-form-group va-cat-rule-field" data-categories="golyos-puska,soretes-puska,vegyescsovu-puska,maroklofegyver,hatastalanitott,loszer-tolteny,egyeb-fegyverek,ij-szamszerij-fuvocso,ij,ijak,szamszerij-nyilpuska,ijvesszo,fuvocso,nyilpisztoly,kiegeszitok-ij">
                <label class="va-check-label"><input type="checkbox" name="license_req" value="1"<?php echo (($edit_meta['license_req'] ?? '') === '1') ? ' checked' : ''; ?>> Fegyverengedély szükséges a vásárláshoz</label>
            </div>
            <!-- Távcső kompakt grid layout -->
            <div class="va-form-group va-telescope-fields-grid" data-categories="tavcsovek,ejjellato-tavcso,hokamerak">
                <div class="va-step2-4col-inner">
                <!-- ROW 0: Távcső típusa (teljes szélesség) -->
                <div class="va-telescope-field va-telescope-type-wrap">
                    <label>Távcső típusa</label>
                    <?php
                    $optic_type_val = (string)($edit_meta['optic_type'] ?? '');
                    ?>
                    <select name="optic_type" id="va-optic-type" class="va-select">
                        <option value="">– Válasszon típust –</option>
                        <option value="celtavcso"<?php selected($optic_type_val, 'celtavcso'); ?>>Céltávcső</option>
                        <option value="kereso"<?php selected($optic_type_val, 'kereso'); ?>>Kereső</option>
                        <option value="spektiv"<?php selected($optic_type_val, 'spektiv'); ?>>Spektív</option>
                        <option value="egyeb"<?php selected($optic_type_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <!-- ROW 1: Nagyítás | Objektív átmérő -->
                <div class="va-telescope-field">
                    <label>Nagyítás</label>
                    <?php
                    $optic_zoom_val = (string)($edit_meta['optic_zoom'] ?? '');
                    ?>
                    <select name="optic_zoom" id="va-optic-zoom" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php if ($optic_zoom_val !== ''): ?>
                        <option value="<?php echo esc_attr($optic_zoom_val); ?>" selected><?php echo esc_html($optic_zoom_val); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="va-telescope-field" data-visibility="tavcsovek,ejjellato-tavcso,hokamerak">
                    <label>Objektív átmérő (mm)</label>
                    <?php
                    $optic_obj_val = (string)($edit_meta['optic_objective'] ?? '');
                    $optic_obj_opts = ['20','21','24','25','30','32','35','40','42','44','50','56','60','62','72','80','100'];
                    ?>
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
                <!-- ROW 2: Típus | Szürkületi érték -->
                <div class="va-telescope-field">
                    <label>Típus</label>
                    <select name="scope_type" class="va-input">
                        <option value="">-- Válassz típust --</option>
                        <option value="monokuláris"<?php echo ($edit_meta['scope_type'] ?? '') === 'monokuláris' ? ' selected' : ''; ?>>Monokuláris</option>
                        <option value="biokuláris"<?php echo ($edit_meta['scope_type'] ?? '') === 'biokuláris' ? ' selected' : ''; ?>>Biokuláris</option>
                    </select>
                </div>
                <div class="va-telescope-field">
                    <label>Szürkületi érték</label>
                    <input type="text" name="twilight_value" class="va-input" placeholder="pl. 15" value="<?php echo esc_attr((string)($edit_meta['twilight_value'] ?? '')); ?>">
                </div>
                <!-- ROW 4: Bevonatok | Tartozékok -->
                <div class="va-telescope-field">
                    <label>Bevonatok</label>
                    <input type="text" name="scope_coatings" class="va-input" placeholder="pl. Multi-coated" value="<?php echo esc_attr((string)($edit_meta['scope_coatings'] ?? '')); ?>">
                </div>
                <div class="va-telescope-field">
                    <label>Tartozékok</label>
                    <input type="text" name="scope_accessories" class="va-input" placeholder="pl. Tartó, lencsevédő" value="<?php echo esc_attr((string)($edit_meta['scope_accessories'] ?? '')); ?>">
                </div>
                </div><!-- /va-step2-4col-inner -->
            </div>

            <!-- Kés mezők grid -->
            <div class="va-form-group va-knife-fields-grid" data-categories="bicska-tor-kard,vadaszkes-vadasztor,taktikai-kes-taktikai-tor,konyhakes,svajci-bicska,dobotor,machete-bozotvago,multiszerszam,tok-keslanc,keselezo" style="display:none">
                <div class="va-step2-4col-inner">
                <!-- Kés típusa -->
                <div class="va-knife-field va-knife-type-wrap">
                    <label>Kés típusa</label>
                    <?php $knife_type_val = (string)($edit_meta['knife_type'] ?? ''); ?>
                    <select name="knife_type" id="va-knife-type" class="va-select">
                        <option value="">– Válasszon típust –</option>
                        <option value="vadaszkes"<?php selected($knife_type_val,'vadaszkes'); ?>>Vadászkés</option>
                        <option value="vadasztor"<?php selected($knife_type_val,'vadasztor'); ?>>Vadásztőr</option>
                        <option value="vadasz-bicska"<?php selected($knife_type_val,'vadasz-bicska'); ?>>Vadászbicska</option>
                        <option value="nyuzoces"<?php selected($knife_type_val,'nyuzoces'); ?>>Nyúzókés</option>
                        <option value="felezo-kes"<?php selected($knife_type_val,'felezo-kes'); ?>>Filéző kés</option>
                        <option value="zsigereloces"<?php selected($knife_type_val,'zsigereloces'); ?>>Zsigerelő kés</option>
                        <option value="szeleloces"<?php selected($knife_type_val,'szeleloces'); ?>>Szeletelő kés</option>
                        <option value="csontfuresz-kes"<?php selected($knife_type_val,'csontfuresz-kes'); ?>>Csontfűrész kés</option>
                        <option value="csontozo-kes"<?php selected($knife_type_val,'csontozo-kes'); ?>>Csontozó kés</option>
                        <option value="boros-kes"<?php selected($knife_type_val,'boros-kes'); ?>>Bőrös kés</option>
                        <option value="taktikai-kes"<?php selected($knife_type_val,'taktikai-kes'); ?>>Taktikai kés</option>
                        <option value="tura-survival"<?php selected($knife_type_val,'tura-survival'); ?>>Túra / Survival kés</option>
                        <option value="konyhai-kes"<?php selected($knife_type_val,'konyhai-kes'); ?>>Konyhakés</option>
                        <option value="egyedi-kes"<?php selected($knife_type_val,'egyedi-kes'); ?>>Egyedi / Kézzel készített</option>
                        <option value="egyeb"<?php selected($knife_type_val,'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <!-- Penge hossza -->
                <div class="va-knife-field">
                    <label>Penge hossza (cm)</label>
                    <?php
                    $knife_blade_val = (string)($edit_meta['knife_blade_length'] ?? '');
                    $knife_blade_opts = ['5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','22','24','25','28','30'];
                    ?>
                    <select name="knife_blade_length" id="va-knife-blade-length" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php if ($knife_blade_val !== '' && !in_array($knife_blade_val, $knife_blade_opts, true)): ?>
                        <option value="<?php echo esc_attr($knife_blade_val); ?>" selected><?php echo esc_html($knife_blade_val); ?> cm</option>
                        <?php endif; ?>
                        <?php foreach ($knife_blade_opts as $cm): ?>
                        <option value="<?php echo esc_attr($cm); ?>"<?php selected($knife_blade_val,$cm); ?>><?php echo esc_html($cm); ?> cm</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                </div><!-- /va-step2-4col-inner -->
            </div><!-- /.va-knife-fields-grid -->

            <!-- Trófeaalátét mezők grid -->
            <div class="va-form-group va-trophy-fields-grid" data-categories="trofea-aletet" style="display:none">
                <div class="va-step2-4col-inner">
                <div class="va-form-group">
                    <label>Vadfaj</label>
                    <?php $trophy_species_val = (string)($edit_meta['trophy_species'] ?? ''); ?>
                    <select name="trophy_species" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="gimszarvas"<?php selected($trophy_species_val, 'gimszarvas'); ?>>Gímszarvas</option>
                        <option value="damszarvas"<?php selected($trophy_species_val, 'damszarvas'); ?>>Dámszarvas</option>
                        <option value="oz"<?php selected($trophy_species_val, 'oz'); ?>>Őz</option>
                        <option value="muflon"<?php selected($trophy_species_val, 'muflon'); ?>>Muflon</option>
                        <option value="vaddiszno"<?php selected($trophy_species_val, 'vaddiszno'); ?>>Vaddisznó</option>
                        <option value="egyeb"<?php selected($trophy_species_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Trófeaalátét típus</label>
                    <?php $trophy_mount_type_val = (string)($edit_meta['trophy_mount_type'] ?? ''); ?>
                    <select name="trophy_mount_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="agancs-alatet"<?php selected($trophy_mount_type_val, 'agancs-alatet'); ?>>Agancs alátét</option>
                        <option value="szarv-alatet"<?php selected($trophy_mount_type_val, 'szarv-alatet'); ?>>Szarv alátét</option>
                        <option value="agyar-alatet"<?php selected($trophy_mount_type_val, 'agyar-alatet'); ?>>Agyaralátét</option>
                        <option value="koponya-alatet"<?php selected($trophy_mount_type_val, 'koponya-alatet'); ?>>Koponyaalátét</option>
                        <option value="fejpreparatum-alatet"<?php selected($trophy_mount_type_val, 'fejpreparatum-alatet'); ?>>Fejpreparátum alátét</option>
                        <option value="tabla-tipusu"<?php selected($trophy_mount_type_val, 'tabla-tipusu'); ?>>Tábla típusú</option>
                        <option value="agyarfoglalat"<?php selected($trophy_mount_type_val, 'agyarfoglalat'); ?>>Agyarfoglalat</option>
                        <option value="egyeb"<?php selected($trophy_mount_type_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Forma / stílus</label>
                    <?php $trophy_style_val = (string)($edit_meta['trophy_style'] ?? ''); ?>
                    <select name="trophy_style" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="faragott-fa"<?php selected($trophy_style_val, 'faragott-fa'); ?>>Faragott fa</option>
                        <option value="pajzs-alaku"<?php selected($trophy_style_val, 'pajzs-alaku'); ?>>Pajzs alakú</option>
                        <option value="rusztikus"<?php selected($trophy_style_val, 'rusztikus'); ?>>Rusztikus / naturális</option>
                        <option value="fejpreparatum"<?php selected($trophy_style_val, 'fejpreparatum'); ?>>Fejpreparátum alátét</option>
                        <option value="koponya"<?php selected($trophy_style_val, 'koponya'); ?>>Koponya alátét</option>
                        <option value="agyarfoglalat"<?php selected($trophy_style_val, 'agyarfoglalat'); ?>>Agyarfoglalat</option>
                        <option value="tabla"<?php selected($trophy_style_val, 'tabla'); ?>>Tábla típusú</option>
                        <option value="egyeb"<?php selected($trophy_style_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Agyarfoglalat kivitel</label>
                    <?php $trophy_fang_mount_val = (string)($edit_meta['trophy_fang_mount'] ?? ''); ?>
                    <select name="trophy_fang_mount" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="3-leveles"<?php selected($trophy_fang_mount_val, '3-leveles'); ?>>3 leveles</option>
                        <option value="6-leveles-elterulo"<?php selected($trophy_fang_mount_val, '6-leveles-elterulo'); ?>>6 leveles elterülő</option>
                        <option value="tolgyleveles"<?php selected($trophy_fang_mount_val, 'tolgyleveles'); ?>>Tölgyleveles</option>
                        <option value="aluminiumleveles"<?php selected($trophy_fang_mount_val, 'aluminiumleveles'); ?>>Alumíniumleveles</option>
                        <option value="vaddisznofej-kicsi"<?php selected($trophy_fang_mount_val, 'vaddisznofej-kicsi'); ?>>Vaddisznófej alakú (kicsi)</option>
                        <option value="vaddisznofej-nagy"<?php selected($trophy_fang_mount_val, 'vaddisznofej-nagy'); ?>>Vaddisznófej alakú (nagy)</option>
                        <option value="egyeb"<?php selected($trophy_fang_mount_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Anyag</label>
                    <?php $trophy_material_val = (string)($edit_meta['trophy_material'] ?? ''); ?>
                    <select name="trophy_material" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="tolgyfa"<?php selected($trophy_material_val, 'tolgyfa'); ?>>Tölgyfa</option>
                        <option value="harsfa"<?php selected($trophy_material_val, 'harsfa'); ?>>Hársfa</option>
                        <option value="dio"<?php selected($trophy_material_val, 'dio'); ?>>Dió</option>
                        <option value="mahagoni"<?php selected($trophy_material_val, 'mahagoni'); ?>>Mahagóni (pácolt)</option>
                        <option value="muanyag"<?php selected($trophy_material_val, 'muanyag'); ?>>Műanyag (műkoponya)</option>
                        <option value="aluminium"<?php selected($trophy_material_val, 'aluminium'); ?>>Alumínium</option>
                        <option value="egyeb"<?php selected($trophy_material_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Felületkezelés</label>
                    <?php $trophy_finish_val = (string)($edit_meta['trophy_finish'] ?? ''); ?>
                    <select name="trophy_finish" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="nyers"<?php selected($trophy_finish_val, 'nyers'); ?>>Nyers</option>
                        <option value="pacolt-dio"<?php selected($trophy_finish_val, 'pacolt-dio'); ?>>Pácolt (dió)</option>
                        <option value="pacolt-mahagoni"<?php selected($trophy_finish_val, 'pacolt-mahagoni'); ?>>Pácolt (mahagóni)</option>
                        <option value="lakkozott"<?php selected($trophy_finish_val, 'lakkozott'); ?>>Lakkozott</option>
                        <option value="antikolt"<?php selected($trophy_finish_val, 'antikolt'); ?>>Antikolt</option>
                        <option value="egyeb"<?php selected($trophy_finish_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                </div><!-- /va-step2-4col-inner -->
            </div><!-- /.va-trophy-fields-grid -->

            <!-- Szállás mezők grid -->
            <div class="va-form-group va-accommodation-fields-grid" data-categories="szallas" style="display:none">
                <div class="va-step2-4col-inner">
                <?php
                $hour_options = [];
                for ( $h = 0; $h < 24; $h++ ) {
                    $hour_options[] = sprintf( '%02d:00', $h );
                }
                $accommodation_type_val = (string)($edit_meta['accommodation_type'] ?? '');
                $accommodation_capacity_val = (string)($edit_meta['accommodation_capacity'] ?? '');
                $accommodation_slot_mode_val = (string)($edit_meta['accommodation_slot_mode'] ?? '');
                $accommodation_from_hour_val = (string)($edit_meta['accommodation_from_hour'] ?? '');
                $accommodation_to_hour_val = (string)($edit_meta['accommodation_to_hour'] ?? '');
                $accommodation_checkin_from_val = (string)($edit_meta['accommodation_checkin_from'] ?? '');
                $accommodation_checkin_to_val = (string)($edit_meta['accommodation_checkin_to'] ?? '');
                $accommodation_checkout_from_val = (string)($edit_meta['accommodation_checkout_from'] ?? '');
                $accommodation_checkout_to_val = (string)($edit_meta['accommodation_checkout_to'] ?? '');
                $accommodation_meal_type_val = (string)($edit_meta['accommodation_meal_type'] ?? '');
                $accommodation_parking_val = (string)($edit_meta['accommodation_parking'] ?? '');
                ?>
                <div class="va-form-group">
                    <label>Szállás típusa</label>
                    <select name="accommodation_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="vadaszhaz"<?php selected($accommodation_type_val, 'vadaszhaz'); ?>>Vadászház</option>
                        <option value="vendeghaz"<?php selected($accommodation_type_val, 'vendeghaz'); ?>>Vendégház</option>
                        <option value="apartman"<?php selected($accommodation_type_val, 'apartman'); ?>>Apartman</option>
                        <option value="panzio"<?php selected($accommodation_type_val, 'panzio'); ?>>Panzió</option>
                        <option value="hotel"<?php selected($accommodation_type_val, 'hotel'); ?>>Hotel</option>
                        <option value="fahaz"<?php selected($accommodation_type_val, 'fahaz'); ?>>Faház</option>
                        <option value="kemping"<?php selected($accommodation_type_val, 'kemping'); ?>>Kemping</option>
                        <option value="egyeb"<?php selected($accommodation_type_val, 'egyeb'); ?>>Egyéb</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Férőhely (fő)</label>
                    <select name="accommodation_capacity" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( ['1-2','3-4','5-8','9-12','13-20','20+'] as $cap): ?>
                        <option value="<?php echo esc_attr($cap); ?>"<?php selected($accommodation_capacity_val, $cap); ?>><?php echo esc_html($cap); ?> fő</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Foglalási idősáv típusa</label>
                    <select name="accommodation_slot_mode" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="oras"<?php selected($accommodation_slot_mode_val, 'oras'); ?>>Órás</option>
                        <option value="felnapos"<?php selected($accommodation_slot_mode_val, 'felnapos'); ?>>Félnapos</option>
                        <option value="ejszakas"<?php selected($accommodation_slot_mode_val, 'ejszakas'); ?>>Éjszakás</option>
                        <option value="hetvegi"<?php selected($accommodation_slot_mode_val, 'hetvegi'); ?>>Hétvégi</option>
                        <option value="heti"<?php selected($accommodation_slot_mode_val, 'heti'); ?>>Heti</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Foglalható idősáv kezdete</label>
                    <select name="accommodation_from_hour" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($accommodation_from_hour_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Foglalható idősáv vége</label>
                    <select name="accommodation_to_hour" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($accommodation_to_hour_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Check-in sáv (kezdete)</label>
                    <select name="accommodation_checkin_from" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($accommodation_checkin_from_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Check-in sáv (vége)</label>
                    <select name="accommodation_checkin_to" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($accommodation_checkin_to_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Check-out sáv (kezdete)</label>
                    <select name="accommodation_checkout_from" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($accommodation_checkout_from_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Check-out sáv (vége)</label>
                    <select name="accommodation_checkout_to" class="va-select">
                        <option value="">– Válasszon –</option>
                        <?php foreach ( $hour_options as $hour ): ?>
                        <option value="<?php echo esc_attr($hour); ?>"<?php selected($accommodation_checkout_to_val, $hour); ?>><?php echo esc_html($hour); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Ellátás típusa</label>
                    <select name="accommodation_meal_type" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="nincs"<?php selected($accommodation_meal_type_val, 'nincs'); ?>>Nincs</option>
                        <option value="reggeli"<?php selected($accommodation_meal_type_val, 'reggeli'); ?>>Reggeli</option>
                        <option value="felpanzio"<?php selected($accommodation_meal_type_val, 'felpanzio'); ?>>Félpanzió</option>
                        <option value="teljes"<?php selected($accommodation_meal_type_val, 'teljes'); ?>>Teljes ellátás</option>
                    </select>
                </div>
                <div class="va-form-group">
                    <label>Saját parkoló</label>
                    <select name="accommodation_parking" class="va-select">
                        <option value="">– Válasszon –</option>
                        <option value="igen"<?php selected($accommodation_parking_val, 'igen'); ?>>Igen</option>
                        <option value="nem"<?php selected($accommodation_parking_val, 'nem'); ?>>Nem</option>
                    </select>
                </div>
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
                ?>
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
                $saved_dog_breed = (string) ($edit_meta['dog_breed'] ?? '');
                $saved_dog_color = (string) ($edit_meta['dog_color'] ?? '');
            ?>
            <div class="va-cat-rule-field va-dog-fields-wrap" data-categories="vadaszkutya">
                <div class="va-step2-4col-inner va-dog-4col">
                <div class="va-dog-row">
                    <div class="va-form-group">
                        <label>Kutya fajtája</label>
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
                        <label>Neme</label>
                        <select name="dog_gender" class="va-select">
                            <option value="">– Válasszon –</option>
                            <option value="kan"<?php selected( (string)($edit_meta['dog_gender'] ?? ''), 'kan' ); ?>>Kan</option>
                            <option value="szuka"<?php selected( (string)($edit_meta['dog_gender'] ?? ''), 'szuka' ); ?>>Szuka</option>
                        </select>
                    </div>
                </div>
                <div class="va-dog-row">
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
                </div>
                <div class="va-dog-row va-dog-row--single">
                    <div class="va-form-group">
                        <label>Kutya életkor (hónap)</label>
                        <input type="number" name="dog_age_months" class="va-input" min="1" max="300" placeholder="pl. 18" value="<?php echo esc_attr((string)($edit_meta['dog_age_months'] ?? '')); ?>">
                    </div>
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
                    <div class="va-form-group" style="grid-column:1 / -1;">
                        <label>Jogosítvány</label>
                        <label class="va-check-label"><input type="checkbox" name="job_drivers_license[]" value="b"<?php echo in_array('b', $job_drivers_license_saved, true) ? ' checked' : ''; ?>> B</label>
                        <label class="va-check-label"><input type="checkbox" name="job_drivers_license[]" value="be"<?php echo in_array('be', $job_drivers_license_saved, true) ? ' checked' : ''; ?>> BE</label>
                        <label class="va-check-label"><input type="checkbox" name="job_drivers_license[]" value="c"<?php echo in_array('c', $job_drivers_license_saved, true) ? ' checked' : ''; ?>> C</label>
                        <label class="va-check-label"><input type="checkbox" name="job_drivers_license[]" value="t"<?php echo in_array('t', $job_drivers_license_saved, true) ? ' checked' : ''; ?>> T</label>
                        <label class="va-check-label"><input type="checkbox" name="job_drivers_license[]" value="egyeb"<?php echo in_array('egyeb', $job_drivers_license_saved, true) ? ' checked' : ''; ?>> Egyéb</label>
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
        var $modal = $('#va-shoe-size-modal');
        var $grid = $('#va-shoe-size-grid');
        if (!$input.length || !$modal.length || !$grid.length) return;

        var sizes = ['EU 35','EU 36','EU 37','EU 38','EU 39','EU 40','EU 41','EU 42','EU 43','EU 44','EU 45','EU 46','EU 47','EU 48'];

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

        $select.val(customValue);
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

        var hasValue = normalizePopupSelectText($select.val()) !== '';
        $button.find('.va-popup-select-trigger__value').text(getPopupSelectButtonLabel($select));
        $button.toggleClass('is-placeholder', !hasValue);
        $button.prop('disabled', $select.is(':disabled'));
        $button.attr('aria-expanded', 'false');
    }

    function renderPopupSelectOptions(query) {
        if (!$activePopupSelect.length) return;

        var selectedValue = (($activePopupSelect.val() || '') + '').trim();
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

            var $item = $('<button>', {
                type: 'button',
                class: 'va-popup-select-item' + (value === selectedValue ? ' is-selected' : ''),
                'data-value': value,
                'aria-pressed': value === selectedValue ? 'true' : 'false'
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
        $popupSelectModal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('va-modal-open');
    }

    function enhancePopupSelects(context) {
        var $scope = context ? $(context) : $(document);
        $scope.find('#va-submit-form select, select#va-brand').each(function(){
            var $select = $(this);
            if (!$select.closest('#va-submit-form').length) return;
            if ($select.closest('#va-popup-select-modal').length) return;
            if ($select.is('[multiple]')) return;
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
        console.log('[VA] Add button clicked, customValue:', customValue);
        if (!customValue) {
            alert('Kérlek írj be egy értéket!');
            return;
        }
        console.log('[VA] Setting value on', $activePopupSelect.attr('name'));
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
            eu: ['XS','S','M','L','XL','XXL','3XL','4XL','44','46','48','50','52','54','56','58','60'],
            usa: ['XS','S','M','L','XL','XXL','3XL','4XL','30','32','34','36','38','40','42','44','46'],
            en: ['XS','S','M','L','XL','XXL','3XL','4XL','34','36','38','40','42','44','46','48','50']
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

    $('#va-category').on('change', function(){
        if (typeof VA_Data !== 'undefined' && VA_Data.site_type !== 'jarmu') {
            $('#va-brand').val('');
            rebuildHuntingBrandModelDatalists(true);
            applyLearnedCaliberDatalist();
            applyCategorySpecificFieldVisibility();
            rebuildClothingSizeOptions();
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
        celtavcso: [
            // Fix nagyítású modellek
            '1x','2x','4x32','6x42','8x56','10x','15-55x52',
            // Variábel (zoom) nagyítású modellek
            '1-4x24','1-6x24','1-8x24','1-10x24',
            '1.5-6x44',
            '2-10x50','2-12x50',
            '3-9x40','3-12x56','3-15x50','3-18x50',
            '4-16x44','4-20x50','4-24x50',
            '5-25x56',
            '6-24x50',
            '7x50',
            '8-32x','10-40x'
        ],
        kereso: [
            // Kompakt / túra
            '6x','7x','8x',
            // Standard vadász
            '10x',
            // Nagy fényerejű
            '8x56','10x56',
            // Erős (stabil tartás kell)
            '12x','15x',
            // Nehéz/állványos
            '20x','25x'
        ],
        spektiv: [
            // Fix (kompakt, zsebes)
            '8x','10x','12x','30x','32x','40x','60x',
            // Zoom – belépő
            '15-45x','16-48x',
            // Zoom – standard
            '20-60x',
            // Zoom – közepes
            '22-66x',
            // Zoom – prémium
            '25-75x',
            // Zoom – nagy objektív
            '30-90x'
        ],
        egyeb: []
    };

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
    }

    $(document).on('change', '#va-optic-type', function(){
        rebuildOpticZoomOptions($(this).val());
    });

    $(document).on('change', '#va-clothing-size-system', function(){
        rebuildClothingSizeOptions();
    });

    rebuildHuntingBrandModelDatalists(false);
    applyLearnedCaliberDatalist();
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
        var isJobCategory = /allas|munka|pozicio/.test(slug) || /(állás|allas|munka|pozíció|pozicio)/.test(selectedCatText);
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
            optic_type: 'Távcső típusa',
            optic_zoom: 'Nagyítás',
            optic_objective: 'Objektív átmérő (mm)',
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
            clothing_size_system: 'Méretrendszer',
            clothing_size: 'Ruhaméret',
            knife_type: 'Kés típusa',
            knife_blade_length: 'Penge hossza (cm)',
            trophy_species: 'Vadfaj',
            trophy_mount_type: 'Trófeaalátét típus',
            trophy_style: 'Forma / stílus',
            trophy_fang_mount: 'Agyarfoglalat kivitel',
            trophy_material: 'Anyag',
            trophy_finish: 'Felületkezelés',
            accommodation_type: 'Szállás típusa',
            accommodation_capacity: 'Férőhely (fő)',
            accommodation_slot_mode: 'Foglalási idősáv típusa',
            accommodation_from_hour: 'Foglalható idősáv kezdete',
            accommodation_to_hour: 'Foglalható idősáv vége',
            hunt_slot_from_hour: 'Idősáv kezdete (óra)',
            hunt_slot_to_hour: 'Idősáv vége (óra)',
            hunt_capacity: 'Létszám / fő',
            hunt_species_list: 'Vadászható fajok listája',
            hunt_lease_type: 'Lesbérleti / hozzáférési forma'
        };
        var missing = [];

        rule.required.forEach(function(field){
            var v = (($( '[name="' + field + '"]' ).val() || '') + '').trim();
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
        var isJobCategory = /allas|munka|pozicio/.test(slug)
            || /(állás|allas|munka|pozíció|pozicio)/.test(selectedCatText);
        var isServiceCategory = /szolgaltatas|szolg/.test(slug)
            || /(szolgáltatás|szolgaltatas)/.test(selectedCatText);
        var isDogCategory = /vadaszkutya|kutya/.test(slug)
            || /(vadászkutya|vadaszkutya|kutya)/.test(selectedCatText);
        var isTelescopeCategory = /tavcsovek|ejjellato-tavcso|hokamerak/.test(slug)
            || /(távcső|tavcso|éjjellátó|ejjellato|hőkamera|hokamera)/.test(selectedCatText);
        var isNonScopeTelescopeCategory = /ejjellato-tavcso|hokamerak/.test(slug)
            || /(éjjellátó|ejjellato|hőkamera|hokamera)/.test(selectedCatText);
        var isKnifeCategory = /bicska-tor-kard|vadaszkes-vadasztor|taktikai-kes-taktikai-tor|konyhakes|svajci-bicska|dobotor|machete-bozotvago|multiszerszam|tok-keslanc|keselezo/.test(slug)
            || /(kés|kes|bicska|tőr|tor|machete|bozótvágó|bozotvago|multiszerszám|multiszerszam)/.test(selectedCatText);
        var isAccommodationCategory = /szallas|ingatlan-szallas/.test(slug)
            || /(szállás|szallas|booking|apartman|vendégház|vendeghaz|vadászház|vadaszhaz)/.test(selectedCatText);
        var isHuntOptionCategory = /vadaszati-lehetoseg|vadkarelharitas|vadaszterulet-berlet|vadasz-lehetoseg/.test(slug)
            || /(vadászati lehetőség|vadaszati lehetoseg|vadkárelhárítás|vadkarelharitas|lesbérlet|lesberlet|bérbeadás|berbeadas)/.test(selectedCatText);
        var isTrophyPlateCategory = /trofea-aletet|trofea|trofeak/.test(slug)
            || /(trófea|trofea|alátét|alatet|agyar|koponya)/.test(selectedCatText);
        var isShoeCategory = /cipo|bakancs|labbeli/.test(slug)
            || /(cipő|cipo|bakancs|lábbeli|labbeli)/.test(selectedCatText);
        var isClothingCategory = /ruhazat/.test(slug)
            || /(ruházat|ruhazat|nadrág|nadrag|póló|polo|kabát|kabat)/.test(selectedCatText);
        var rules = VA_Data.category_required_rules || {};
        var required = (rules[slug] && Array.isArray(rules[slug].required)) ? rules[slug].required : [];
        required = required.slice();
        if (isJobCategory || isServiceCategory) {
            if (required.indexOf('job_location') === -1) required.push('job_location');
            if (required.indexOf('job_type') === -1) required.push('job_type');
        }

        $('.va-core-product-fields, .va-core-product-year').toggle(!(isJobCategory || isServiceCategory || isDogCategory || isAccommodationCategory || isHuntOptionCategory));

        // Handle knife fields grid
        $('.va-knife-fields-grid').toggle(isKnifeCategory);

        // Handle trophy mount fields grid
        $('.va-trophy-fields-grid').toggle(isTrophyPlateCategory);

        // Handle accommodation fields grid
        $('.va-accommodation-fields-grid').toggle(isAccommodationCategory);

        // Handle hunting-option fields grid
        $('.va-hunt-option-fields-grid').toggle(isHuntOptionCategory);

        // Handle telescope fields grid
        $('.va-telescope-fields-grid').toggle(isTelescopeCategory);
        
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

        // Apply required attributes to trophy fields
        if (isTrophyPlateCategory) {
            $('.va-trophy-fields-grid input, .va-trophy-fields-grid select, .va-trophy-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim();
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        // Apply required attributes to accommodation fields
        if (isAccommodationCategory) {
            $('.va-accommodation-fields-grid input, .va-accommodation-fields-grid select, .va-accommodation-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim();
                var requiredHere = required.indexOf(fieldName) !== -1;
                $(this).prop('required', requiredHere);
            });
        }

        // Apply required attributes to hunting-option fields
        if (isHuntOptionCategory) {
            $('.va-hunt-option-fields-grid input, .va-hunt-option-fields-grid select, .va-hunt-option-fields-grid textarea').each(function(){
                var fieldName = ($(this).attr('name') || '').trim();
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
                var fieldName = ($(this).attr('name') || '').trim();
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


