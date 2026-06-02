<?php
/**
 * Template: Keresés / szűrő rész + hirdetések AJAX listája
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false, 'parent' => 0 ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );
$category_slug_map = [];
if ( ! is_wp_error( $categories ) && is_array( $categories ) ) {
    foreach ( $categories as $cat_term ) {
        $category_slug_map[ (int) $cat_term->term_id ] = sanitize_title( (string) $cat_term->slug );
    }
}

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
$hunting_brand_models = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_hunting_brand_models_by_category() : [];
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
$active_category_name = '';
$active_category_slug = '';
if ( $url_cat > 0 ) {
    $active_category = get_term( $url_cat, 'va_category' );
    if ( $active_category instanceof WP_Term && ! is_wp_error( $active_category ) ) {
        $active_category_name = (string) $active_category->name;
        $active_category_slug = sanitize_title( (string) $active_category->slug );
    }
}
$active_brand_models = [];
if ( $active_category_slug === 'jarmu' ) {
    $active_brand_models = $vehicle_brand_models;
} elseif ( $active_category_slug !== '' && isset( $hunting_brand_models[ $active_category_slug ] ) && is_array( $hunting_brand_models[ $active_category_slug ] ) ) {
    $active_brand_models = $hunting_brand_models[ $active_category_slug ];
}
$active_brands = array_keys( $active_brand_models );
$active_models = [];
if ( $url_brand !== '' && isset( $active_brand_models[ $url_brand ] ) && is_array( $active_brand_models[ $url_brand ] ) ) {
    $active_models = $active_brand_models[ $url_brand ];
}
$heading_parts = [];
if ( $url_brand !== '' ) {
    $heading_parts[] = $url_brand;
}
if ( $url_model !== '' ) {
    $heading_parts[] = $url_model;
}
if ( $url_s !== '' ) {
    $heading_parts[] = $url_s;
}
if ( empty( $heading_parts ) && $active_category_name !== '' ) {
    $heading_parts[] = $active_category_name;
}
$page_heading = ! empty( $heading_parts ) ? implode( ' ', $heading_parts ) : $landing_title;
$breadcrumb_label = $page_heading !== '' ? $page_heading : 'Hirdetések';
$plain_filter_title = trim( preg_replace( '/^[^\p{L}\p{N}]+/u', '', (string) $lp_filter_title ) );
if ( $plain_filter_title === '' ) {
    $plain_filter_title = 'Hirdetések keresése';
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
    'hunting_brand_models' => $hunting_brand_models,
    'category_slugs'   => $category_slug_map,
]);
wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <section class="va-search-landing" style="display:block !important;margin-bottom:22px;padding:14px 18px 16px;border:1px solid rgba(255,255,255,.08);border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.08);font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:rgba(255,255,255,.7);text-decoration:none;">Főoldal</a>
            <span style="color:rgba(255,255,255,.38);">/</span>
            <span style="color:#fff;"><?php echo esc_html( $breadcrumb_label ); ?></span>
        </div>
        <h1 style="margin:0 0 6px;font-size:clamp(28px,4vw,40px);line-height:1.1;color:#fff;font-weight:900;"><?php echo esc_html( $page_heading ); ?></h1>
        <p style="margin:0;color:rgba(255,255,255,.72);max-width:860px;"><?php echo esc_html( $landing_intro ); ?></p>
    </section>

    <!-- Szűrő sáv -->
    <div class="va-filter-bar">
        <div class="va-filter-bar__title"><?php echo esc_html( $plain_filter_title ); ?></div>
        <style>
            #va-filter-form [data-special-filter] { display: none; }
        </style>
        <form id="va-filter-form" data-post-type="<?php echo esc_attr( $url_post_type ); ?>">
            <div class="va-filter-bar__grid">
                <input type="search" id="va-kw" class="va-input" placeholder="<?php echo esc_attr( $lp_kw_placeholder ); ?>" value="<?php echo esc_attr( $url_s ); ?>">

                <select id="va-county" class="va-select">
                    <option value=""><?php echo esc_html( $lp_co_placeholder ); ?></option>
                    <?php foreach ( $counties as $county_term ): ?>
                        <option value="<?php echo esc_attr( (string) $county_term->term_id ); ?>"><?php echo esc_html( (string) $county_term->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-cond" class="va-select">
                    <option value=""><?php echo esc_html( $lp_cond_placeholder ); ?></option>
                    <?php foreach ( $conditions as $cond_term ): ?>
                        <option value="<?php echo esc_attr( (string) $cond_term->term_id ); ?>"><?php echo esc_html( (string) $cond_term->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-cat" class="va-select">
                    <option value=""><?php echo esc_html( $lp_cat_placeholder ); ?></option>
                    <?php foreach ( $categories as $cat ): ?>
                        <option value="<?php echo esc_attr( (string) $cat->term_id ); ?>"<?php selected( $url_cat, (int) $cat->term_id ); ?>><?php echo esc_html( (string) $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-brand-search" class="va-select" data-special-filter="brand-model">
                    <option value="">Márka: Mindegy</option>
                    <?php foreach ( $active_brands as $brand ): ?>
                        <option value="<?php echo esc_attr( (string) $brand ); ?>"<?php selected( $url_brand, (string) $brand ); ?>><?php echo esc_html( (string) $brand ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-model-search" class="va-select" data-special-filter="brand-model">
                    <option value="">Modell: Mindegy</option>
                    <?php foreach ( $active_models as $model ): ?>
                        <option value="<?php echo esc_attr( (string) $model ); ?>"<?php selected( $url_model, (string) $model ); ?>><?php echo esc_html( (string) $model ); ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" id="va-caliber-search" class="va-input" placeholder="Kaliber (pl. 30-06, 12/70)" data-special-filter="firearm">

                <select id="va-body-type" class="va-select" data-special-filter="vehicle">
                    <option value="">Kivitel: Mindegy</option>
                    <?php foreach ( $vehicle_body_types as $body_key => $body_label ): ?>
                        <option value="<?php echo esc_attr( (string) $body_key ); ?>"><?php echo esc_html( (string) $body_label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-fuel-type" class="va-select" data-special-filter="vehicle">
                    <option value="">Üzemanyag: Mindegy</option>
                    <?php foreach ( $vehicle_fuel_types as $fuel_key => $fuel_label ): ?>
                        <option value="<?php echo esc_attr( (string) $fuel_key ); ?>"><?php echo esc_html( (string) $fuel_label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="number" id="va-year-min" class="va-input" min="1900" max="2099" placeholder="Évjárat -tól" data-special-filter="vehicle">
                <input type="number" id="va-year-max" class="va-input" min="1900" max="2099" placeholder="Évjárat -ig" data-special-filter="vehicle">

                <input type="text" id="va-optic-zoom-search" class="va-input" placeholder="Nagyítás (pl. 3-12x50)" data-special-filter="optic">
                <input type="number" id="va-optic-objective-min" class="va-input" min="1" max="120" placeholder="Objektív -tól (mm)" data-special-filter="optic">
                <input type="number" id="va-optic-objective-max" class="va-input" min="1" max="120" placeholder="Objektív -ig (mm)" data-special-filter="optic">

                <input type="number" id="va-dog-age-min" class="va-input" min="1" max="300" placeholder="Kutya kor -tól (hó)" data-special-filter="dog">
                <input type="number" id="va-dog-age-max" class="va-input" min="1" max="300" placeholder="Kutya kor -ig (hó)" data-special-filter="dog">

                <select id="va-clothing-size" class="va-select" data-special-filter="clothing">
                    <option value="">Ruhaméret: Mindegy</option>
                    <option value="xs">XS</option>
                    <option value="s">S</option>
                    <option value="m">M</option>
                    <option value="l">L</option>
                    <option value="xl">XL</option>
                    <option value="xxl">XXL</option>
                    <option value="xxxl">XXXL</option>
                    <option value="egyedi">Egyedi</option>
                </select>

                <select id="va-clothing-season" class="va-select" data-special-filter="clothing">
                    <option value="">Szezon: Mindegy</option>
                    <option value="nyari">Nyári</option>
                    <option value="atmeneti">Átmeneti</option>
                    <option value="teli">Téli</option>
                    <option value="negyevszakos">4 évszakos</option>
                    <option value="extrem-hideg">Extrém hideg</option>
                    <option value="esos-ido">Esős idő</option>
                    <option value="szelallo">Szélálló</option>
                </select>

                <select id="va-clothing-noise" class="va-select" data-special-filter="clothing">
                    <option value="">Hangtulajdonság: Mindegy</option>
                    <option value="ultra-csendes">Ultra csendes</option>
                    <option value="halk">Halk</option>
                    <option value="normal">Normál</option>
                    <option value="noise-reduction-fabric">Noise reduction</option>
                </select>

                <select id="va-clothing-waterproof" class="va-select" data-special-filter="clothing">
                    <option value="">Vízállóság: Mindegy</option>
                    <option value="vizallo">Vízálló</option>
                    <option value="vizlepergeto">Vízlepergető</option>
                    <option value="szelallo">Szélálló</option>
                    <option value="hoallo">Hóálló</option>
                    <option value="gyorsan-szarado">Gyorsan száradó</option>
                    <option value="legzo">Lélegző</option>
                </select>

                <select id="va-service-type" class="va-select" data-special-filter="service">
                    <option value="">Szolgáltatás típusa: Mindegy</option>
                    <option value="vadaszati-szolgaltatas">Vadászati szolgáltatás</option>
                    <option value="fegyvermusz">Fegyver / optika</option>
                    <option value="preparator">Trófea / preparálás</option>
                    <option value="kutyas-szolgaltatas">Kutyás szolgáltatás</option>
                    <option value="terulet-erdo">Terület / erdő</option>
                    <option value="oktatas">Oktatás</option>
                    <option value="szallitas">Szállítás</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <select id="va-service-area" class="va-select" data-special-filter="service">
                    <option value="">Kiszállási terület: Mindegy</option>
                    <option value="helyi">Helyi</option>
                    <option value="megyei">Megyei</option>
                    <option value="orszagos">Országos</option>
                    <option value="nemzetkozi">Nemzetközi</option>
                </select>

                <select id="va-service-pricing" class="va-select" data-special-filter="service">
                    <option value="">Ártípus: Mindegy</option>
                    <option value="oradij">Óradíj</option>
                    <option value="fix">Fix</option>
                    <option value="km-alapu">Km alapú</option>
                    <option value="projekt">Projekt alapú</option>
                    <option value="egyedi-ajanlat">Egyedi ajánlat</option>
                </select>

                <select id="va-service-verified" class="va-select" data-special-filter="service">
                    <option value="">Ellenőrzött szolgáltató: Mindegy</option>
                    <option value="igen">Igen</option>
                    <option value="nem">Nem</option>
                </select>

                <select id="va-service-weekend" class="va-select" data-special-filter="service">
                    <option value="">Hétvégi elérhetőség: Mindegy</option>
                    <option value="igen">Igen</option>
                    <option value="nem">Nem</option>
                </select>

                <select id="va-service-sos" class="va-select" data-special-filter="service">
                    <option value="">SOS szolgáltatás: Mindegy</option>
                    <option value="igen">Igen</option>
                    <option value="nem">Nem</option>
                </select>

                <select id="va-hunt-species" class="va-select" data-special-filter="hunt">
                    <option value="">Vadfaj: Mindegy</option>
                    <option value="gimszarvas">Gímszarvas</option>
                    <option value="damszarvas">Dámszarvas</option>
                    <option value="oz">Őz</option>
                    <option value="muflon">Muflon</option>
                    <option value="vaddiszno">Vaddisznó</option>
                    <option value="facan">Fácán</option>
                    <option value="roka">Róka</option>
                    <option value="sakal">Sakál</option>
                </select>

                <select id="va-hunt-method" class="va-select" data-special-filter="hunt">
                    <option value="">Vadászati módszer: Mindegy</option>
                    <option value="les">Les</option>
                    <option value="cserkeles">Cserkelés</option>
                    <option value="hajtas">Hajtás</option>
                    <option value="lesvadaszat">Lesvadászat</option>
                    <option value="dronos-megfigyeles">Drónos megfigyelés</option>
                </select>

                <select id="va-hunt-land-type" class="va-select" data-special-filter="hunt">
                    <option value="">Terület típusa: Mindegy</option>
                    <option value="szantofold">Szántóföld</option>
                    <option value="kukorica">Kukorica</option>
                    <option value="napraforgo">Napraforgó</option>
                    <option value="erdo">Erdő</option>
                    <option value="legelo">Legelő</option>
                    <option value="vegyes-mezogazdasagi">Vegyes mezőgazdasági</option>
                </select>

                <select id="va-hunt-contract" class="va-select" data-special-filter="hunt">
                    <option value="">Szerződés típusa: Mindegy</option>
                    <option value="eseti">Eseti</option>
                    <option value="szezonalis">Szezonális</option>
                    <option value="eves">Éves</option>
                    <option value="folyamatos">Folyamatos</option>
                </select>

                <select id="va-hunt-pricing" class="va-select" data-special-filter="hunt">
                    <option value="">Árazás modell: Mindegy</option>
                    <option value="oradij">Óradíj</option>
                    <option value="napidij">Napidíj</option>
                    <option value="hektar-alapu">Hektár alapú</option>
                    <option value="szerzodeses">Szerződéses</option>
                    <option value="sikerdijas">Sikerdíjas</option>
                    <option value="egyedi-ajanlat">Egyedi ajánlat</option>
                </select>

                <select id="va-hunt-availability" class="va-select" data-special-filter="hunt">
                    <option value="">Elérhetőség: Mindegy</option>
                    <option value="0-24">0-24</option>
                    <option value="nappali">Nappali</option>
                    <option value="ejszakai">Éjszakai</option>
                    <option value="elore-egyeztetett">Előre egyeztetett</option>
                </select>

                <select id="va-exchange-offer-category" class="va-select" data-special-filter="exchange">
                    <option value="">Csere fő kategória: Mindegy</option>
                    <option value="fegyver">Fegyver</option>
                    <option value="optika">Optika</option>
                    <option value="hokamera">Hőkamera</option>
                    <option value="kes">Kés</option>
                    <option value="ruhazat">Ruházat</option>
                    <option value="elektronika">Elektronika</option>
                    <option value="vadaszati-felszereles">Vadászati felszerelés</option>
                    <option value="trofea">Trófea</option>
                    <option value="jarmu">Jármű</option>
                    <option value="szolgaltatas">Szolgáltatás</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <select id="va-exchange-type" class="va-select" data-special-filter="exchange">
                    <option value="">Csere típusa: Mindegy</option>
                    <option value="termek-csere">Termék csere</option>
                    <option value="rafizeteses-csere">Ráfizetéses csere</option>
                    <option value="ertekegyezteteses-csere">Értékegyeztetéses csere</option>
                    <option value="szolgaltatas-csere">Szolgáltatás csere</option>
                    <option value="gyujtoi-csere">Gyűjtői csere</option>
                    <option value="tobb-termek-egyre">Több termék egyre</option>
                    <option value="egy-termek-tobbre">Egy termék többre</option>
                </select>

                <select id="va-exchange-condition" class="va-select" data-special-filter="exchange">
                    <option value="">Csere állapot: Mindegy</option>
                    <option value="uj">Új</option>
                    <option value="ujszeru">Újszerű</option>
                    <option value="hasznalt">Használt</option>
                    <option value="hibas">Hibás</option>
                    <option value="gyujtoi">Gyűjtői</option>
                </select>

                <select id="va-exchange-extra-payment" class="va-select" data-special-filter="exchange">
                    <option value="">Ráfizetés iránya: Mindegy</option>
                    <option value="kerek-ra">Kérek rá</option>
                    <option value="adok-ra">Adok rá</option>
                    <option value="nem-szukseges">Nem szükséges</option>
                </select>

                <select id="va-accommodation-type" class="va-select" data-special-filter="accommodation">
                    <option value="">Ingatlan típus: Mindegy</option>
                    <option value="vadaszhaz">Vadászház</option>
                    <option value="szallashely">Szálláshely</option>
                    <option value="tanya">Tanya</option>
                    <option value="telek">Telek</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <input type="text" id="va-accommodation-county" class="va-input" placeholder="Ingatlan megye (szöveg)" data-special-filter="accommodation">

                <select id="va-accommodation-condition" class="va-select" data-special-filter="accommodation">
                    <option value="">Ingatlan állapot: Mindegy</option>
                    <option value="uj">Új</option>
                    <option value="ujszeru">Újszerű</option>
                    <option value="jo">Jó</option>
                    <option value="felujitando">Felújítandó</option>
                </select>

                <select id="va-knife-type" class="va-select" data-special-filter="knife">
                    <option value="">Kés típusa: Mindegy</option>
                    <option value="vadaszkes">Vadászkés</option>
                    <option value="taktikai-kes">Taktikai kés</option>
                    <option value="konyhakes">Konyhakés</option>
                    <option value="bicska">Bicska</option>
                    <option value="svajci-bicska">Svájci bicska</option>
                </select>

                <input type="text" id="va-knife-steel" class="va-input" placeholder="Kés acél típusa" data-special-filter="knife">

                <select id="va-knife-condition" class="va-select" data-special-filter="knife">
                    <option value="">Kés állapot: Mindegy</option>
                    <option value="uj">Új</option>
                    <option value="ujszeru">Újszerű</option>
                    <option value="hasznalt">Használt</option>
                </select>

                <select id="va-bag-type" class="va-select" data-special-filter="bag">
                    <option value="">Táska típusa: Mindegy</option>
                    <option value="hatizsak">Hátizsák</option>
                    <option value="fegyvertok">Fegyvertok</option>
                    <option value="valltaska">Válltáska</option>
                    <option value="range-bag">Range bag</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <input type="text" id="va-bag-material" class="va-input" placeholder="Táska anyaga" data-special-filter="bag">

                <select id="va-bag-condition" class="va-select" data-special-filter="bag">
                    <option value="">Táska állapot: Mindegy</option>
                    <option value="uj">Új</option>
                    <option value="ujszeru">Újszerű</option>
                    <option value="hasznalt">Használt</option>
                </select>

                <select id="va-publication-type" class="va-select" data-special-filter="collectibles">
                    <option value="">Kiadvány típus: Mindegy</option>
                    <option value="konyv">Könyv</option>
                    <option value="folyoirat">Folyóirat</option>
                    <option value="album">Album</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <select id="va-decor-type" class="va-select" data-special-filter="collectibles">
                    <option value="">Dísztárgy típus: Mindegy</option>
                    <option value="trofea">Trófea</option>
                    <option value="faragas">Faragás</option>
                    <option value="szobor">Szobor</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <select id="va-call-type" class="va-select" data-special-filter="collectibles">
                    <option value="">Kürt/síp típusa: Mindegy</option>
                    <option value="kezihivo">Kézi hívó</option>
                    <option value="elektronikus-hivo">Elektronikus hívó</option>
                    <option value="kurt">Kürt</option>
                    <option value="sip">Síp</option>
                </select>

                <input type="text" id="va-trophy-material" class="va-input" placeholder="Trófeaalátét anyaga" data-special-filter="collectibles">

                <select id="va-equipment-type" class="va-select" data-special-filter="equipment">
                    <option value="">Felszerelés típus: Mindegy</option>
                    <option value="vadaszlampa">Vadászlámpa</option>
                    <option value="fegyverlampa">Fegyverlámpa</option>
                    <option value="hatizsak">Hátizsák</option>
                    <option value="egyeb">Egyéb</option>
                </select>

                <input type="number" id="va-mileage-min" class="va-input" min="0" placeholder="Kilométer -tól" data-special-filter="vehicle">
                <input type="number" id="va-mileage-max" class="va-input" min="0" placeholder="Kilométer -ig" data-special-filter="vehicle">

                <input type="number" id="va-engine-min" class="va-input" min="0" placeholder="Hengerűrt. -tól (cm3)" data-special-filter="vehicle">
                <input type="number" id="va-engine-max" class="va-input" min="0" placeholder="Hengerűrt. -ig (cm3)" data-special-filter="vehicle">

                <select id="va-vehicle-condition" class="va-select" data-special-filter="vehicle">
                    <option value="">Állapot: Mindegy</option>
                    <?php foreach ( $vehicle_conditions as $cond_key => $cond_label ): ?>
                        <option value="<?php echo esc_attr( (string) $cond_key ); ?>"><?php echo esc_html( (string) $cond_label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-doors" class="va-select" data-special-filter="vehicle">
                    <option value="">Ajtók száma: Mindegy</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6+</option>
                </select>

                <select id="va-passengers" class="va-select" data-special-filter="vehicle">
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
                <div class="va-car-extra-filters" data-special-filter="vehicle">
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
</div>
