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
                echo '<select name="category" class="va-select"' . $req_attr . '>';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $categories as $cat ) {
                    $indent   = $cat->parent ? '&nbsp;&nbsp;' : '';
                    $selected = selected( (int) $val, $cat->term_id, false );
                    echo '<option value="' . esc_attr( $cat->term_id ) . '"' . $selected . '>' . $indent . esc_html( $cat->name ) . '</option>';
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
                $location_val = (string) $val !== '' ? (string) $val : 'Veszprém Gyulafirátót';
                echo '<input type="text" name="location" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( $location_val ) . '">';
                break;
            case 'brand':
                if ( $site_type !== 'jarmu' ) {
                    echo '<input type="text" name="brand" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
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
                    echo '<input type="text" name="model" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
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
                echo '<input type="text" name="caliber" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
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
    foreach ( $categories as $cat ) {
        if ( isset( $cat->slug ) && (string) $cat->slug === 'egyeb' ) {
            $other_category = $cat;
            continue;
        }
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
$body_types = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_body_type_options() : [];

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
            'brand'       => get_post_meta( $maybe_id, 'va_brand',       true ),
            'model'       => get_post_meta( $maybe_id, 'va_model',       true ),
            'body_type'   => get_post_meta( $maybe_id, 'va_body_type',   true ),
            'caliber'     => get_post_meta( $maybe_id, 'va_caliber',     true ),
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

        if ( isset( $plan_check['limit'], $plan_check['used'] ) && (int) $plan_check['limit'] > 0 ) {
            $plan_remaining = max( 0, (int) $plan_check['limit'] - (int) $plan_check['used'] );
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
    $has_any_allowance = $plan_has_allowance || $user_credit_balance > 0 || $remaining_free > 0;
    if ( ! $has_any_allowance ) {
        wp_redirect( $buy_url_submit );
        exit;
    }
}

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
wp_enqueue_script( 'va-submit',   VA_PLUGIN_URL . 'frontend/js/frontend.js',  [ 'jquery' ], VA_VERSION, true );
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
    'site_type'      => $site_type,
    'vehicle_brand_models' => $site_type === 'jarmu' ? $brand_models : [],
]);
?>
<?php va_display_flash(); ?>
<div id="va-submit-notice"></div>

    <form id="va-submit-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'va_update_listing' : 'va_submit_listing'; ?>">
        <input type="hidden" name="nonce"  value="<?php echo esc_attr( wp_create_nonce( $edit_mode ? 'va_update_listing' : 'va_submit_listing' ) ); ?>">
        <?php if ( $edit_mode ): ?>
        <input type="hidden" name="post_id" value="<?php echo esc_attr( (string) $edit_post_id ); ?>">
        <?php endif; ?>

        <h2 style="font-size:20px;font-weight:800;margin-bottom:22px;"><?php echo $edit_mode ? '✏️ Hirdetés szerkesztése' : '📋 Hirdetés feladása'; ?></h2>

        <?php if ( ! $edit_mode ): ?>
        <div class="va-notice va-notice--info" style="margin-bottom:16px;">
            <?php if ( $plan_has_allowance || $user_credit_balance > 0 ): ?>
                <?php if ( $user_credit_balance > 0 && is_int( $plan_remaining ) && $plan_remaining > 0 ): ?>
                    Egyenleged: <strong><?php echo esc_html( (string) $plan_remaining ); ?> plan + <?php echo esc_html( (string) $user_credit_balance ); ?> vásárolt kredit</strong>.
                <?php elseif ( $user_credit_balance > 0 ): ?>
                    Rendelkezésre álló kreditjeid: <strong><?php echo esc_html( (string) $user_credit_balance ); ?> db</strong>.
                <?php elseif ( is_int( $plan_remaining ) && $plan_remaining > 0 ): ?>
                    Csomagkeretedből még <strong><?php echo esc_html( (string) $plan_remaining ); ?> db</strong> hirdetést adhatsz fel.
                <?php else: ?>
                    Az előfizetésed alapján jelenleg tudsz hirdetést feladni.
                <?php endif; ?>
            <?php elseif ( $remaining_free > 0 ): ?>
                <?php if ( $remaining_free === 1 ): ?>
                    Ez az <strong>utolsó ingyenes</strong> hirdetésed. Utána minden új hirdetés díja <strong><?php echo esc_html( number_format( $paid_price, 0, ',', ' ' ) ); ?> Ft</strong> –
                    <a href="<?php echo esc_url( $buy_url ); ?>" style="color:#ff6060;font-weight:700;">vásárolj most csomagot!</a>
                <?php else: ?>
                    Még <strong><?php echo esc_html( (string) $remaining_free ); ?> db</strong> ingyenes hirdetésed van.
                    Utána: <strong><?php echo esc_html( number_format( $paid_price, 0, ',', ' ' ) ); ?> Ft / hirdetés</strong> –
                    <a href="<?php echo esc_url( $buy_url ); ?>" style="color:#ff6060;font-weight:700;">csomagok megtekintése</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php
        // Dinamikus form mezők VA_Form_Builder config alapján
        $fb_form    = 'va_listing_submit';
        $fb_fields  = VA_Form_Builder::get_fields( $fb_form );
        usort( $fb_fields, fn( $a, $b ) => (int)( $a['order'] ?? 99 ) - (int)( $b['order'] ?? 99 ) );

        // Csoportok: szekciókat nyit ha szükséges
        $section_groups = [
            'brand'       => 'Termék részletek',
            'price'       => 'Ár',
            'phone'       => 'Kapcsolat',
        ];
        $opened_sections = [];

        // Párba rakandó mezők (2-oszlopos sor)
        $pair_groups = $site_type === 'jarmu'
            ? [
                ['condition','location'],
                ['brand',    'model'],
                ['body_type','year'],
                ['price',    'price_type'],
            ]
            : [
                ['condition','location'],
                ['brand',    'model'],
                ['caliber',  'year'],
                ['price',    'price_type'],
            ];
        $pair_map = [];
        foreach ( $pair_groups as $pair ) {
            $pair_map[ $pair[0] ] = $pair[1];
            $pair_map[ $pair[1] ] = $pair[0]; // partner
        }
        $rendered_keys = [];

        foreach ( $fb_fields as $field ):
            $fkey = (string)( $field['key'] ?? '' );
            if ( in_array( $fkey, $rendered_keys, true ) ) continue;
            if ( $fkey === 'county' ) {
                $rendered_keys[] = $fkey;
                continue;
            }
            if ( $fkey === 'email_show' || $fkey === 'phone' ) {
                $rendered_keys[] = $fkey;
                continue;
            }
            if ( empty( $field['enabled'] ) ) {
                $rendered_keys[] = $fkey;
                continue;
            }

            $label = esc_html( (string)( $field['label'] ?? $fkey ) );
            $ph    = esc_attr( (string)( $field['placeholder'] ?? '' ) );
            $req   = ! empty( $field['required'] );
            $req_html = $req ? ' <span class="required">*</span>' : '';
            $req_attr = $req ? ' required' : '';

            // Szekció fejléc
            if ( isset( $section_groups[ $fkey ] ) && ! isset( $opened_sections[ $fkey ] ) ) {
                $opened_sections[ $fkey ] = true;
                echo '<h3 style="font-size:12px;font-weight:700;margin:20px 0 14px;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px;">'
                    . esc_html( $section_groups[ $fkey ] ) . '</h3>';
            }

            // Pár-sor logika
            $partner_key = $pair_map[ $fkey ] ?? null;
            $partner_field = null;
            if ( $partner_key ) {
                foreach ( $fb_fields as $pf ) {
                    if ( ( $pf['key'] ?? '' ) === $partner_key && ! empty( $pf['enabled'] ) ) {
                        $partner_field = $pf;
                        break;
                    }
                }
            }

            if ( $partner_field && ! in_array( $partner_key, $rendered_keys, true ) ):
                // 2 oszlopos sor
                $rendered_keys[] = $fkey;
                $rendered_keys[] = $partner_key;
                $p2_label   = esc_html( (string)( $partner_field['label'] ?? $partner_key ) );
                $p2_ph      = esc_attr( (string)( $partner_field['placeholder'] ?? '' ) );
                $p2_req     = ! empty( $partner_field['required'] );
                $p2_req_html = $p2_req ? ' <span class="required">*</span>' : '';
                $p2_req_attr = $p2_req ? ' required' : '';
                echo '<div class="va-form-row">';
                // Mező 1
                echo '<div class="va-form-group">';
                echo "<label>{$label}{$req_html}</label>";
                self_render_listing_field( $fkey, $ph, $req_attr, $categories, $counties, $conditions, $brands, $body_types, $brand_models, $site_type, $edit_meta );
                echo '</div>';
                // Mező 2
                echo '<div class="va-form-group">';
                echo "<label>{$p2_label}{$p2_req_html}</label>";
                self_render_listing_field( $partner_key, $p2_ph, $p2_req_attr, $categories, $counties, $conditions, $brands, $body_types, $brand_models, $site_type, $edit_meta );
                echo '</div>';
                echo '</div>';
            else:
                $rendered_keys[] = $fkey;
                // Teljes soros mező
                echo '<div class="va-form-group">';
                echo "<label>{$label}{$req_html}</label>";
                self_render_listing_field( $fkey, $ph, $req_attr, $categories, $counties, $conditions, $brands, $body_types, $brand_models, $site_type, $edit_meta );
                echo '</div>';
            endif;
        endforeach;
        ?>

        <?php if ( $site_type === 'jarmu' ):
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
        </style>
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
        <?php endif; ?>

        <?php
        if ( VA_Form_Builder::is_enabled( $fb_form, 'phone' ) ):
            $phone_field = VA_Form_Builder::get_field( $fb_form, 'phone' );
            $phone_req   = ! empty( $phone_field['required'] );
            $phone_req_html = $phone_req ? ' <span class="required">*</span>' : '';
            $phone_req_attr = $phone_req ? ' required' : '';
            $phone_val  = esc_attr( (string)( $edit_meta['phone'] ?? '' ) );
            $phone_ph   = esc_attr( (string)( $phone_field['placeholder'] ?? '+36 30 000 0000' ) );
        ?>
        <h3 style="font-size:12px;font-weight:700;margin:20px 0 14px;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px;">Kapcsolat</h3>
        <div class="va-form-group">
            <label>Telefonszám<?php echo $phone_req_html; ?></label>
            <input type="tel" name="phone" class="va-input" placeholder="<?php echo $phone_ph; ?>"<?php echo $phone_req_attr; ?> value="<?php echo $phone_val; ?>">
        </div>
        <?php endif; ?>

        <?php if ( VA_Form_Builder::is_enabled( $fb_form, 'email_show' ) ): ?>
        <div class="va-form-group" style="margin-top:18px;">
            <label style="margin-bottom:8px;">E-mail megjelenítése</label>
            <label class="va-check-label">
                <input type="checkbox" name="email_show" value="1" checked onclick="return false;">
                E-mail cím megjelenítése a hirdetésben
            </label>
        </div>
        <?php endif; ?>

        <button type="submit" class="va-btn va-btn--primary va-btn--block" id="va-submit-btn">
            <?php echo $edit_mode ? '💾 Változások mentése' : '📤 Hirdetés feladása'; ?>
        </button>

        <p style="font-size:12px;color:rgba(255,255,255,0.4);margin-top:12px;text-align:center;">
            <?php echo get_option('va_auto_publish_listings','0') === '1'
                ? 'A hirdetés azonnal megjelenik.'
                : 'A hirdetés moderátor jóváhagyása után jelenik meg.'; ?>
        </p>
    </form>

<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
(function($){
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
        if (typeof VA_Data === 'undefined' || VA_Data.site_type !== 'jarmu') return;

        var $brand = $('#va-brand');
        var $model = $('#va-model');
        if (!$brand.length || !$model.length) return;

        var brand = $brand.val() || '';
        var models = (VA_Data.vehicle_brand_models && VA_Data.vehicle_brand_models[brand]) ? VA_Data.vehicle_brand_models[brand] : [];
        var current = $model.data('selected') || $model.val() || '';
        var html = '<option value="">– Válasszon –</option>';

        if (current && models.indexOf(current) === -1) {
            html += '<option value="' + $('<div>').text(current).html() + '">' + $('<div>').text(current).html() + '</option>';
        }

        models.forEach(function(model) {
            var safe = $('<div>').text(model).html();
            html += '<option value="' + safe + '">' + safe + '</option>';
        });

        $model.html(html);
        if (current) {
            $model.val(current);
        }
    }

    $('#va-brand').on('change', function(){
        $('#va-model').data('selected', '');
        rebuildVehicleModelOptions();
    });
    rebuildVehicleModelOptions();

    /* ══ Form submit ═════════════════════════════════════ */
    $('#va-submit-form').on('submit', function(e){
        e.preventDefault();
        var $btn    = $('#va-submit-btn');
        var editMode = !! VA_Data.edit_mode;
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
            success: function(res){
                $btn.prop('disabled', false).text(editMode ? '💾 Változások mentése' : '📤 Hirdetés feladása');
                if(res.success){
                    $('#va-submit-notice').html('<div class="va-notice va-notice--success">' + res.data.message + '</div>');
                    if (typeof window.va_toast === 'function') {
                        window.va_toast(res.data.message || 'Mentés sikeres.', 'success');
                    }
                    if(res.data.permalink){
                        setTimeout(function(){ window.location.href = res.data.permalink; }, 2000);
                    }
                } else {
                    if (res.data && res.data.need_credits) {
                        // Kredit szükséges → csomagvásárló megjelenítése
                        var price = res.data.paid_price ? Number(res.data.paid_price).toLocaleString('hu-HU') + ' Ft' : '';
                        var buyPage = '<?php echo esc_js( $buy_url_submit ); ?>';
                        var html = '<div class="va-notice va-notice--warning" style="padding:18px;">'
                            + '<strong>Elfogyott az ingyenes hirdetési kereted.</strong><br>'
                            + (price ? 'Egy hirdetés ára: <strong>' + price + '</strong><br>' : '')
                            + '<a href="' + buyPage + '" class="va-btn va-btn--primary" style="margin-top:12px;display:inline-flex;">🛒 Hirdetési csomag vásárlása</a>'
                            + '</div>';
                        $('#va-submit-notice').html(html);
                        if (typeof window.va_toast === 'function') {
                            window.va_toast('Elfogyott az ingyenes keret. Csomag vásárlás szükséges.', 'error');
                        }
                    } else if (res.data && res.data.payment_required && res.data.payment_url) {
                        var amount = res.data.amount ? Number(res.data.amount).toLocaleString('hu-HU') + ' Ft' : '';
                        var html2 = '<div class="va-notice va-notice--warning">'
                            + res.data.message
                            + (amount ? '<br><strong>Fizetendő: ' + amount + '</strong>' : '')
                            + '<br><a href="' + res.data.payment_url + '" class="va-btn va-btn--primary" style="margin-top:10px;display:inline-flex;">Bankkártyás fizetés</a>'
                            + '</div>';
                        $('#va-submit-notice').html(html2);
                        if (typeof window.va_toast === 'function') {
                            window.va_toast(res.data.message || 'Fizetés szükséges a folytatáshoz.', 'error');
                        }
                    } else {
                        $('#va-submit-notice').html('<div class="va-notice va-notice--error">' + res.data.message + '</div>');
                        if (typeof window.va_toast === 'function') {
                            window.va_toast((res.data && res.data.message) ? res.data.message : 'Mentési hiba történt.', 'error');
                        }
                    }
                }
            },
            error: function(){
                $btn.prop('disabled', false).text('📤 Hirdetés feladása');
                $('#va-submit-notice').html('<div class="va-notice va-notice--error">Hálózati hiba. Próbálja újra.</div>');
                if (typeof window.va_toast === 'function') {
                    window.va_toast('Hálózati hiba. Próbálja újra.', 'error');
                }
            }
        }); // $.ajax end
        }); // $.when end
    }); // submit end
})(jQuery);
}); // DOMContentLoaded
</script>
