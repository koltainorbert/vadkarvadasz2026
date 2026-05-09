<?php
/**
 * Template: Keresés / szűrő rész + hirdetések AJAX listája
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false, 'parent' => 0 ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );

// URL paraméterek
$url_s           = sanitize_text_field( wp_unslash( $_GET['s']           ?? '' ) );
$url_q           = sanitize_text_field( wp_unslash( $_GET['q']           ?? '' ) ); // user_search módban
$url_cat         = intval( $_GET['cat']         ?? 0 );
$url_author_id   = intval( $_GET['author_id']   ?? 0 );
$url_brand       = sanitize_text_field( wp_unslash( $_GET['brand']       ?? '' ) );
$url_model       = sanitize_text_field( wp_unslash( $_GET['model']       ?? '' ) );
$url_user_search = ! empty( $_GET['user_search'] );
$allowed_post_types = [ 'va_listing' ];
if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
    $allowed_post_types[] = 'va_auction';
}
$url_post_type   = in_array( $_GET['post_type'] ?? '', $allowed_post_types, true ) ? $_GET['post_type'] : 'va_listing';

// ── Keresési oldal szövegek / opciók ────────────────────────────
$lp_filter_title    = (string) get_option( 'va_lp_filter_title_text', '🔍 Hirdetések keresése' );
$lp_kw_placeholder  = (string) get_option( 'va_lp_kw_placeholder', 'Kulcsszó...' );
$lp_cat_placeholder = (string) get_option( 'va_lp_cat_placeholder', '– Kategória –' );
$lp_co_placeholder  = (string) get_option( 'va_lp_county_placeholder', '– Megye –' );
$lp_cond_placeholder= (string) get_option( 'va_lp_cond_placeholder', '– Állapot –' );
$lp_slider_label    = (string) get_option( 'va_lp_slider_label_text', 'Ár szűrő' );
$lp_slider_max      = max( 100000000, (int) get_option( 'va_lp_slider_max', 100000000 ) );
$lp_slider_step     = max( 1, (int) get_option( 'va_lp_slider_step', 500 ) );
$lp_sort_default    = (string) get_option( 'va_lp_sort_default_lbl', 'Legújabb' );
$lp_sort_price_asc  = (string) get_option( 'va_lp_sort_price_asc_lbl', 'Ár: növekvő' );
$lp_sort_price_desc = (string) get_option( 'va_lp_sort_price_desc_lbl', 'Ár: csökkenő' );
$lp_sort_views      = (string) get_option( 'va_lp_sort_views_lbl', 'Legtöbb megtekintés' );
$lp_reset_btn       = (string) get_option( 'va_lp_reset_btn_text', 'Szűrők törlése' );
$lp_loader_text     = (string) get_option( 'va_lp_loader_text', 'Betöltés...' );
$lp_empty_text      = (string) get_option( 'va_lp_empty_text', 'Nincs találat.' );

$vehicle_brand_models = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_brand_models() : [];
$vehicle_brands       = array_keys( $vehicle_brand_models );
$vehicle_body_types   = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_body_type_options() : [];
$vehicle_fuel_types   = [
    'benzin'   => 'Benzin',
    'diesel'   => 'Dízel',
    'hybrid'   => 'Hibrid',
    'electric' => 'Elektromos',
    'lpg'      => 'LPG',
    'cng'      => 'CNG',
    'egyeb'    => 'Egyéb',
];
$vehicle_conditions   = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_vehicle_condition_options() : [];
$search_page         = get_page_by_path( 'va-hirdetes-kereses' );
$search_url          = $search_page ? get_permalink( $search_page ) : home_url( '/va-hirdetes-kereses/' );
$landing_context     = class_exists( 'VA_SEO' ) ? VA_SEO::get_search_landing_context() : [
    'title'       => 'Eladó autók és motorok',
    'intro'       => 'Böngészd a friss autó- és motorhirdetéseket részletes szűrőkkel, aktuális árakkal és járműadatokkal.',
    'seo_heading' => 'Autó- és motorhirdetések országosan',
    'seo_text'    => 'Részletes szűrés márka, modell, ár és futásteljesítmény szerint.',
    'seo_points'  => [],
];
$landing_title       = (string) $landing_context['title'];
$landing_intro       = (string) $landing_context['intro'];
$landing_seo_heading = (string) $landing_context['seo_heading'];
$landing_seo_text    = (string) $landing_context['seo_text'];
$landing_seo_points  = ! empty( $landing_context['seo_points'] ) && is_array( $landing_context['seo_points'] ) ? $landing_context['seo_points'] : [];
$top_brand_links     = array_slice( $vehicle_brands, 0, 24 );
$top_model_links     = [];
if ( $url_brand !== '' && ! empty( $vehicle_brand_models[ $url_brand ] ) && is_array( $vehicle_brand_models[ $url_brand ] ) ) {
    $top_model_links = array_slice( $vehicle_brand_models[ $url_brand ], 0, 24 );
}

// ── Felhasználó-kereső mód ────────────────────────────────────
if ( $url_user_search ) {
    wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
    $search_q  = $url_q ?: $url_s; // q= elsőbbséget élvez, fallback: s=
    $user_args = [
        'number'  => 50,
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ];
    if ( $search_q ) {
        $user_args['search']         = '*' . $search_q . '*';
        $user_args['search_columns'] = [ 'user_login', 'display_name' ];
    }
    $users = get_users( $user_args );
    ?>
    <div class="va-wrap">
        <?php va_display_flash(); ?>
        <div class="va-filter-bar" style="margin-bottom:24px;">
            <div class="va-filter-bar__title">👤 Felhasználók<?php echo ( $search_q ?? '' ) ? ' – <em>' . esc_html( $search_q ) . '</em>' : ''; ?></div>
        </div>
        <div class="va-user-grid">
        <?php if ( empty( $users ) ): ?>
            <p style="color:rgba(255,255,255,0.5);">Nem találtunk felhasználót.</p>
        <?php else: foreach ( $users as $u ):
            $avatar      = get_avatar_url( $u->ID, [ 'size' => 160 ] );
            $listing_url = add_query_arg( 'author_id', $u->ID, $search_url );
            $count       = count_user_posts( $u->ID, 'va_listing' );
        ?>
            <?php
                // Ha display_name email, használjuk a user_login-t
                $show_name = ( strpos( $u->display_name, '@' ) !== false )
                    ? $u->user_login
                    : $u->display_name;
            ?>
            <a class="va-user-card" href="<?php echo esc_url( $listing_url ); ?>">
                <img class="va-user-card__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="" loading="lazy">
                <div class="va-user-card__name"><?php echo esc_html( $show_name ); ?></div>
                <div class="va-user-card__meta"><?php echo intval( $count ); ?> hirdetés</div>
            </a>
        <?php endforeach; endif; ?>
        </div>
    </div>
    <?php
    return; // ne futtassa a hirdetés-szűrő részt
}

wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url'         => admin_url( 'admin-ajax.php' ),
    'nonce'            => wp_create_nonce( 'va_user_nonce' ),
    'post_id'          => 0,
    'initial_s'        => $url_s,
    'initial_cat'      => $url_cat,
    'initial_author_id'=> $url_author_id,
    'initial_post_type'=> $url_post_type,
    'initial_brand'    => $url_brand,
    'initial_model'    => $url_model,
    'slider_max'       => $lp_slider_max,
    'slider_step'      => $lp_slider_step,
    'empty_text'       => $lp_empty_text,
    'vehicle_brand_models' => $vehicle_brand_models,
]);
wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

        <section class="va-search-landing" style="display:none !important;margin-bottom:22px;padding:18px 20px;border:1px solid rgba(255,255,255,.08);border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));" aria-hidden="true">
        <h1 style="margin:0 0 8px;font-size:clamp(24px,4vw,38px);line-height:1.1;color:#fff;"><?php echo esc_html( $landing_title ); ?></h1>
        <p style="margin:0 0 16px;color:rgba(255,255,255,.72);max-width:860px;"><?php echo esc_html( $landing_intro ); ?></p>

        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
            <?php foreach ( $top_brand_links as $brand_link ): ?>
                <a href="<?php echo esc_url( add_query_arg( 'brand', (string) $brand_link, $search_url ) ); ?>" style="display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.12);color:#fff;text-decoration:none;background:rgba(255,255,255,.03);">
                    <?php echo esc_html( (string) $brand_link ); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ( ! empty( $top_model_links ) ) : ?>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php foreach ( $top_model_links as $model_link ): ?>
                <a href="<?php echo esc_url( add_query_arg( [ 'brand' => $url_brand, 'model' => (string) $model_link ], $search_url ) ); ?>" style="display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;border:1px solid rgba(255,0,0,.28);color:#fff;text-decoration:none;background:rgba(255,0,0,.08);">
                    <?php echo esc_html( (string) $model_link ); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top:18px;padding-top:16px;border-top:1px solid rgba(255,255,255,.08);max-width:920px;">
            <h2 style="margin:0 0 10px;font-size:clamp(18px,2.6vw,26px);line-height:1.2;color:#fff;"><?php echo esc_html( $landing_seo_heading ); ?></h2>
            <p style="margin:0;color:rgba(255,255,255,.72);line-height:1.7;"><?php echo esc_html( $landing_seo_text ); ?></p>
            <?php if ( ! empty( $landing_seo_points ) ) : ?>
                <ul style="margin:14px 0 0;padding-left:18px;color:rgba(255,255,255,.82);line-height:1.7;">
                    <?php foreach ( $landing_seo_points as $landing_point ) : ?>
                        <li><?php echo esc_html( (string) $landing_point ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <!-- Szűrő sáv -->
    <div class="va-filter-bar">
        <div class="va-filter-bar__title"><?php echo esc_html( $lp_filter_title ); ?></div>
        <form id="va-filter-form" data-post-type="<?php echo esc_attr( $url_post_type ); ?>">
            <div class="va-filter-bar__grid">
                <select id="va-brand-search" class="va-select">
                    <option value="">Márka: Mindegy</option>
                    <?php foreach ( $vehicle_brands as $brand ): ?>
                        <option value="<?php echo esc_attr( (string) $brand ); ?>"<?php selected( $url_brand, (string) $brand ); ?>><?php echo esc_html( (string) $brand ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-model-search" class="va-select">
                    <option value="">Modell: Mindegy</option>
                </select>

                <select id="va-body-type" class="va-select">
                    <option value="">Kivitel: Mindegy</option>
                    <?php foreach ( $vehicle_body_types as $body_key => $body_label ): ?>
                        <option value="<?php echo esc_attr( (string) $body_key ); ?>"><?php echo esc_html( (string) $body_label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-fuel-type" class="va-select">
                    <option value="">Üzemanyag: Mindegy</option>
                    <?php foreach ( $vehicle_fuel_types as $fuel_key => $fuel_label ): ?>
                        <option value="<?php echo esc_attr( (string) $fuel_key ); ?>"><?php echo esc_html( (string) $fuel_label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="number" id="va-year-min" class="va-input" min="1900" max="2099" placeholder="Évjárat -tól">
                <input type="number" id="va-year-max" class="va-input" min="1900" max="2099" placeholder="Évjárat -ig">

                <input type="number" id="va-mileage-min" class="va-input" min="0" placeholder="Kilométer -tól">
                <input type="number" id="va-mileage-max" class="va-input" min="0" placeholder="Kilométer -ig">

                <input type="number" id="va-engine-min" class="va-input" min="0" placeholder="Hengerűrt. -tól (cm3)">
                <input type="number" id="va-engine-max" class="va-input" min="0" placeholder="Hengerűrt. -ig (cm3)">

                <select id="va-vehicle-condition" class="va-select">
                    <option value="">Állapot: Mindegy</option>
                    <?php foreach ( $vehicle_conditions as $cond_key => $cond_label ): ?>
                        <option value="<?php echo esc_attr( (string) $cond_key ); ?>"><?php echo esc_html( (string) $cond_label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-doors" class="va-select">
                    <option value="">Ajtók száma: Mindegy</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6+</option>
                </select>

                <select id="va-passengers" class="va-select">
                    <option value="">Ülések száma: Mindegy</option>
                    <?php for ( $i = 1; $i <= 9; $i++ ): ?>
                        <option value="<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( (string) $i ); ?></option>
                    <?php endfor; ?>
                </select>

                <div class="va-price-slider-wrap">
                    <div class="va-price-slider-labels">
                        <span><?php echo esc_html( $lp_slider_label ); ?></span>
                        <span class="va-price-slider-display"><span id="va-min-price-display">1</span> – <span id="va-max-price-display"><?php echo esc_html( number_format( $lp_slider_max, 0, ',', ' ' ) ); ?></span> Ft</span>
                    </div>
                    <div class="va-price-slider-track">
                        <input type="range" id="va-min-price" class="va-range" min="1" max="<?php echo esc_attr( $lp_slider_max ); ?>" step="<?php echo esc_attr( $lp_slider_step ); ?>" value="1">
                        <input type="range" id="va-max-price" class="va-range" min="1" max="<?php echo esc_attr( $lp_slider_max ); ?>" step="<?php echo esc_attr( $lp_slider_step ); ?>" value="<?php echo esc_attr( $lp_slider_max ); ?>">
                        <div class="va-range-fill" id="va-range-fill"></div>
                    </div>
                </div>

                <select id="va-sort" class="va-select">
                    <option value="date"><?php echo esc_html( $lp_sort_default ); ?></option>
                    <option value="price_asc"><?php echo esc_html( $lp_sort_price_asc ); ?></option>
                    <option value="price_desc"><?php echo esc_html( $lp_sort_price_desc ); ?></option>
                    <option value="views"><?php echo esc_html( $lp_sort_views ); ?></option>
                </select>
            </div>

            <button type="button" class="va-advanced-toggle" id="va-advanced-toggle" aria-controls="va-advanced-panel" aria-expanded="false">
                Részletes kereső
            </button>

            <div class="va-advanced-panel is-collapsed" id="va-advanced-panel">
                <div class="va-car-extra-filters">
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" id="va-opt-automatic"> automata</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" data-extra="tempomat"> tempomat</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" id="va-opt-awd"> összkerékmeghajtás</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" data-extra="alloy_wheels"> alufelni</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" data-extra="elec_window_front"> elektromos ablak</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" data-extra="towbar"> vonóhorog</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" data-extra="isofix"> ISOFIX rendszer</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" data-extra="esp"> ESP (menetstabilizátor)</label>
                    <label class="va-check-label"><input type="checkbox" class="va-car-filter" id="va-opt-service-book"> szervizkönyv</label>
                </div>

                <div class="va-car-per-page">
                    <label class="va-check-label"><input type="radio" name="va-per-page" value="25" checked> 25 találat oldalanként</label>
                    <label class="va-check-label"><input type="radio" name="va-per-page" value="50"> 50 találat oldalanként</label>
                    <label class="va-check-label"><input type="radio" name="va-per-page" value="100"> 100 találat oldalanként</label>
                </div>
            </div>

            <div class="va-filter-bar__actions">
                <button type="button" id="va-filter-reset" class="va-btn va-btn--outline va-btn--sm"><?php echo esc_html( $lp_reset_btn ); ?></button>
                <span id="va-results-count" style="font-size:13px;color:#fff;align-self:center;"></span>
                <div class="va-view-toggle" style="margin-left:auto;display:flex;gap:10px;">
                    <button type="button" class="va-view-btn va-view-btn--grid" id="va-view-grid" title="Rács nézet" aria-label="Rács nézet">
                        <svg class="va-view-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect class="va-grid-c1" x="3" y="3" width="8" height="8" rx="1.5"/>
                            <rect class="va-grid-c2" x="13" y="3" width="8" height="8" rx="1.5"/>
                            <rect class="va-grid-c3" x="3" y="13" width="8" height="8" rx="1.5"/>
                            <rect class="va-grid-c4" x="13" y="13" width="8" height="8" rx="1.5"/>
                        </svg>
                    </button>
                    <button type="button" class="va-view-btn va-view-btn--list" id="va-view-list" title="Lista nézet" aria-label="Lista nézet">
                        <svg class="va-view-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <line class="va-list-l1" x1="3" y1="6" x2="21" y2="6"/>
                            <line class="va-list-l2" x1="3" y1="12" x2="21" y2="12"/>
                            <line class="va-list-l3" x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Loader -->
    <div id="va-listing-loader" style="display:none;text-align:center;padding:20px;">
        <?php echo esc_html( $lp_loader_text ); ?>
    </div>

    <!-- Eredmények -->
    <div id="va-listing-results" class="va-grid"></div>

    <!-- Pagination -->
    <div id="va-pagination" class="va-pagination"></div>

    <!-- Utolsó hirdetések widget -->
    <?php echo do_shortcode( '[va_recent_listings limit="6" title="Friss ajánlatok" show_title="1"]' ); ?>
</div>
