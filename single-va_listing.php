<?php


/**


 * single-va_listing.php - Hirdetes reszletes oldal (v2 - modern 2026)


 */





// ── Open Graph / Twitter Card meta tagok ────────────────
// Központi SEO réteg mellett ne duplázzuk a meta tageket.
if ( ! class_exists( 'VA_SEO' ) ) {
add_action( 'wp_head', function() {


    if ( ! is_singular( 'va_listing' ) ) return;





    $post_id = get_the_ID();





    // Cím


    $og_title = get_the_title( $post_id );





    // Leírás: post excerpt vagy description meta, 160 karakterre vágva


    $desc = get_post_meta( $post_id, 'va_description', true );


    if ( $desc === '' ) $desc = get_the_excerpt();


    $og_desc = wp_strip_all_tags( $desc );


    $og_desc = preg_replace( '/\s+/', ' ', $og_desc );


    $og_desc = trim( mb_substr( $og_desc, 0, 160 ) );





    // Kép: kiemelt kép (borítókép)


    $og_image     = '';


    $og_img_w     = '';


    $og_img_h     = '';


    $thumb_id = get_post_thumbnail_id( $post_id );


    if ( $thumb_id ) {


        $img_data = wp_get_attachment_image_src( $thumb_id, 'large' );


        if ( $img_data ) {


            $og_image = $img_data[0];


            $og_img_w = $img_data[1];


            $og_img_h = $img_data[2];


        }


    }





    // URL


    $og_url = get_permalink( $post_id );





    // Site neve


    $site_name = get_option( 'va_site_name', get_bloginfo('name') );





    echo "\n<!-- Open Graph / Social Share -->\n";


    echo '<meta property="og:type" content="product">' . "\n";


    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";


    echo '<meta property="og:url" content="' . esc_url( $og_url ) . '">' . "\n";


    echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";


    if ( $og_desc !== '' ) {


        echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";


        echo '<meta name="description" content="' . esc_attr( $og_desc ) . '">' . "\n";


    }


    if ( $og_image !== '' ) {


        echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";


        if ( $og_img_w ) echo '<meta property="og:image:width" content="' . esc_attr( (string) $og_img_w ) . '">' . "\n";


        if ( $og_img_h ) echo '<meta property="og:image:height" content="' . esc_attr( (string) $og_img_h ) . '">' . "\n";


        echo '<meta property="og:image:alt" content="' . esc_attr( $og_title ) . '">' . "\n";


    }


    // Twitter Card


    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";


    echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";


    if ( $og_desc !== '' ) echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";


    if ( $og_image !== '' ) echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";


    echo "<!-- /Open Graph -->\n\n";


}, 1 ); // priority 1 = wp_head legeleje
}





get_header();





if ( ! have_posts() ) { get_footer(); return; }


the_post();





$post_id     = get_the_ID();


$price       = get_post_meta( $post_id, 'va_price',       true );


$price_type  = get_post_meta( $post_id, 'va_price_type',  true ) ?: 'fixed';
$sale_price  = get_post_meta( $post_id, 'va_sale_price',     true );
$sale_end    = get_post_meta( $post_id, 'va_sale_price_end', true );
$has_sale    = $sale_price && floatval( $sale_price ) > 0
               && ( ! $sale_end || strtotime( $sale_end ) >= current_time( 'timestamp' ) );


$brand       = get_post_meta( $post_id, 'va_brand',       true );


$model       = get_post_meta( $post_id, 'va_model',       true );


$caliber     = get_post_meta( $post_id, 'va_caliber',     true );


$year        = get_post_meta( $post_id, 'va_year',        true );


$phone       = get_post_meta( $post_id, 'va_phone',       true );


$location    = get_post_meta( $post_id, 'va_location',    true );


$license_req = get_post_meta( $post_id, 'va_license_req', true );


$email_show  = get_post_meta( $post_id, 'va_email_show',  true );


$views       = va_display_views( $post_id );


$expires     = get_post_meta( $post_id, 'va_expires',     true );


$featured    = get_post_meta( $post_id, 'va_featured',    true ) === '1';


$verified    = get_post_meta( $post_id, 'va_verified',    true ) === '1';


$categories  = get_the_terms( $post_id, 'va_category' );


$county      = get_the_terms( $post_id, 'va_county' );


$condition   = get_the_terms( $post_id, 'va_condition' );


$author      = get_userdata( get_the_author_meta('ID') );





// Típus-specifikus extra metaadatok


$site_type       = class_exists('VA_Meta_Fields') ? VA_Meta_Fields::get_site_type() : 'vadaszat';


$mileage         = get_post_meta( $post_id, 'va_mileage',         true );


$fuel_type       = get_post_meta( $post_id, 'va_fuel_type',       true );


$performance_kw  = get_post_meta( $post_id, 'va_performance_kw',  true );


$engine_size     = get_post_meta( $post_id, 'va_engine_size',     true );


$transmission    = get_post_meta( $post_id, 'va_transmission',    true );


$body_type       = get_post_meta( $post_id, 'va_body_type',       true );


$color_val       = get_post_meta( $post_id, 'va_color',           true );


$doors           = get_post_meta( $post_id, 'va_doors',           true );


$owners          = get_post_meta( $post_id, 'va_owners',          true );


$keys_count      = get_post_meta( $post_id, 'va_keys',            true );


$prev_damage     = get_post_meta( $post_id, 'va_previous_damage', true );


$service_book    = get_post_meta( $post_id, 'va_service_book',    true );


$tech_inspect    = get_post_meta( $post_id, 'va_tech_inspect',    true );


$first_reg       = get_post_meta( $post_id, 'va_first_reg',       true );


$area_m2         = get_post_meta( $post_id, 'va_area_m2',         true );


$rooms           = get_post_meta( $post_id, 'va_rooms',           true );


$floor           = get_post_meta( $post_id, 'va_floor',           true );


$lot_size        = get_post_meta( $post_id, 'va_lot_size',        true );


$building_year   = get_post_meta( $post_id, 'va_building_year',   true );


$parking         = get_post_meta( $post_id, 'va_parking',         true );


$furnished       = get_post_meta( $post_id, 'va_furnished',       true );


$heating         = get_post_meta( $post_id, 'va_heating',         true );


$balcony         = get_post_meta( $post_id, 'va_balcony',         true );

// Új jármű mezők
$drive            = get_post_meta( $post_id, 'va_drive',            true );
$vehicle_cond     = get_post_meta( $post_id, 'va_vehicle_condition',true );
$doc_type         = get_post_meta( $post_id, 'va_doc_type',         true );
$doc_validity     = get_post_meta( $post_id, 'va_doc_validity',     true );
$ac_type          = get_post_meta( $post_id, 'va_ac_type',          true );
$eco_class        = get_post_meta( $post_id, 'va_eco_class',        true );
$cylinder_layout  = get_post_meta( $post_id, 'va_cylinder_layout',  true );
$own_weight       = get_post_meta( $post_id, 'va_own_weight',       true );
$gross_weight     = get_post_meta( $post_id, 'va_gross_weight',     true );
$passengers       = get_post_meta( $post_id, 'va_passengers',       true );
$trunk_liters     = get_post_meta( $post_id, 'va_trunk_liters',     true );
$range_gearbox    = get_post_meta( $post_id, 'va_range_gearbox',    true );
$roof_type        = get_post_meta( $post_id, 'va_roof_type',        true );
$color_metallic   = get_post_meta( $post_id, 'va_color_metallic',   true );
$upholstery_1     = get_post_meta( $post_id, 'va_upholstery_1',     true );
$upholstery_2     = get_post_meta( $post_id, 'va_upholstery_2',     true );
$summer_tire_front= get_post_meta( $post_id, 'va_summer_tire_front',true );
$summer_tire_rear = get_post_meta( $post_id, 'va_summer_tire_rear', true );
$winter_tire_front= get_post_meta( $post_id, 'va_winter_tire_front',true );
$winter_tire_rear = get_post_meta( $post_id, 'va_winter_tire_rear', true );
$vehicle_type     = get_post_meta( $post_id, 'va_vehicle_type',     true );
$extras_raw       = get_post_meta( $post_id, 'va_extras',           true );
$extras_arr       = ( is_string( $extras_raw ) && $extras_raw !== '' ) ? json_decode( $extras_raw, true ) : [];
$extras_arr       = is_array( $extras_arr ) ? $extras_arr : [];

// Kutya mezők
$dog_breed        = get_post_meta( $post_id, 'va_dog_breed',        true );
$dog_gender       = get_post_meta( $post_id, 'va_dog_gender',       true );
$dog_color        = get_post_meta( $post_id, 'va_dog_color',        true );
$dog_purebred     = get_post_meta( $post_id, 'va_dog_purebred',     true );
$dog_age_months   = get_post_meta( $post_id, 'va_dog_age_months',   true );

// Optikai mezők
$optic_type       = get_post_meta( $post_id, 'va_optic_type',       true );
$optic_zoom       = get_post_meta( $post_id, 'va_optic_zoom',       true );
$optic_objective  = get_post_meta( $post_id, 'va_optic_objective',  true );





// Felszerelési lista fordítók


$fuel_labels = [ 'benzin'=>'Benzin','diesel'=>'Dízel','hybrid'=>'Hibrid','electric'=>'Elektromos','lpg'=>'LPG','cng'=>'CNG','egyeb'=>'Egyéb' ];


$trans_labels = [ 'manual'=>'Kéziváltó','automatic'=>'Automata','semi_auto'=>'Félautomata','cvt'=>'CVT' ];


$body_labels = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_body_type_options() : [ 'sedan'=>'sedan','hatchback'=>'ferdehátú','wagon'=>'kombi','cabrio'=>'cabrio','mpv'=>'egyterű','coupe'=>'coupe','crossover'=>'városi terepjáró (crossover)','closed'=>'zárt','double_cab_chassis'=>'duplakabinos alváz','pickup'=>'pickup','minibus'=>'kisbusz','single_cab_chassis'=>'alváz szimpla kabin' ];


$park_labels = [ 'none'=>'Nincs','street'=>'Utcai','private'=>'Saját','garage'=>'Garázs' ];


$furn_labels = [ 'no'=>'Nem','partial'=>'Részben','yes'=>'Igen' ];


$heat_labels = [ 'gas'=>'Gáz','electric'=>'Elektromos','district'=>'Távfűtés','wood'=>'Fa/szilárd','heat_pump'=>'Hőszivattyú' ];

$drive_labels     = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_drive_options() : [];
$vcond_labels     = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_vehicle_condition_options() : [];
$doctype_labels   = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_doc_type_options() : [];
$docval_labels    = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_doc_validity_options() : [];
$ac_labels        = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_ac_type_options() : [];
$eco_labels       = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_eco_class_options() : [];
$cyl_labels       = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_cylinder_layout_options() : [];
$vtype_labels     = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_vehicle_type_options() : [];
$roof_labels      = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_roof_type_options() : [];
$extras_opts      = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_options() : [];
$extras_by_grp    = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_by_group() : [];
// Kepek gyujtese: va_gallery_ids meta (elsődleges) + featured image


$gallery_str    = (string) get_post_meta( $post_id, 'va_gallery_ids', true );


$attachment_ids = $gallery_str


    ? array_filter( array_map( 'absint', explode( ',', $gallery_str ) ) )


    : [];


if ( has_post_thumbnail() ) {


    $thumb_id       = get_post_thumbnail_id( $post_id );


    $attachment_ids = array_values( array_unique( array_merge( [ $thumb_id ], $attachment_ids ) ) );


}

$demo_image_rel = (string) get_post_meta( $post_id, 'va_demo_image', true );
$demo_image_url = $demo_image_rel !== ''
    ? trailingslashit( get_template_directory_uri() ) . ltrim( $demo_image_rel, '/' )
    : '';





wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );


wp_localize_script( 'va-frontend', 'VA_Data', [


    'ajax_url' => admin_url('admin-ajax.php'),


    'nonce'    => wp_create_nonce('va_user_nonce'),


    'post_id'  => $post_id,


]);





$sl_layout_mode = (string) get_option( 'va_single_layout_mode', 'split' );


if ( ! in_array( $sl_layout_mode, [ 'split', 'stacked' ], true ) ) {


    $sl_layout_mode = 'split';


}





$sl_content_max   = max( 960, min( 1800, absint( get_option( 'va_single_content_max', 1320 ) ) ) );


$sl_sidebar_width = max( 280, min( 620, absint( get_option( 'va_single_sidebar_width', 390 ) ) ) );


$sl_layout_gap    = max( 8, min( 60, absint( get_option( 'va_single_layout_gap', 24 ) ) ) );


$sl_thumb_size    = max( 54, min( 140, absint( get_option( 'va_single_thumb_size', 86 ) ) ) );


$sl_radius        = max( 0, min( 40, absint( get_option( 'va_single_card_radius', 14 ) ) ) );


$sl_padding       = max( 10, min( 48, absint( get_option( 'va_single_card_padding', 22 ) ) ) );


$sl_title_size    = max( 24, min( 72, absint( get_option( 'va_single_title_size', 40 ) ) ) );


$sl_price_size    = max( 24, min( 72, absint( get_option( 'va_single_price_size', 42 ) ) ) );


$sl_meta_size     = max( 10, min( 22, absint( get_option( 'va_single_meta_size', 13 ) ) ) );


$sl_btn_height    = max( 34, min( 72, absint( get_option( 'va_single_btn_height', 48 ) ) ) );


$sl_share_size    = max( 28, min( 62, absint( get_option( 'va_single_share_size', 40 ) ) ) );


$sl_mobile_scale  = max( 60, min( 100, absint( get_option( 'va_single_mobile_title_scale', 78 ) ) ) );





$sl_gallery_ratio = (string) get_option( 'va_single_gallery_ratio', '4/3' );


if ( ! in_array( $sl_gallery_ratio, [ '1/1', '4/3', '16/10', '16/9' ], true ) ) {


    $sl_gallery_ratio = '4/3';


}


$sl_gallery_fit = (string) get_option( 'va_single_gallery_fit', 'cover' );


if ( ! in_array( $sl_gallery_fit, [ 'cover', 'contain' ], true ) ) {


    $sl_gallery_fit = 'cover';


}





$sl_viewer_bg = sanitize_text_field( (string) get_option( 'va_single_viewer_bg', 'rgba(4,4,4,.96)' ) );


$sl_accent    = sanitize_hex_color( (string) get_option( 'va_single_accent', '#ff2a2a' ) ) ?: '#ff2a2a';


$sl_glass     = sanitize_text_field( (string) get_option( 'va_single_glass', 'rgba(255,255,255,.07)' ) );


$sl_border    = sanitize_text_field( (string) get_option( 'va_single_border', 'rgba(255,255,255,.12)' ) );

// Extra szin opciok
$sl_card_title_color      = sanitize_text_field( (string) get_option( 'va_sl_card_title_color',      'rgba(255,255,255,.55)' ) );
$sl_title_color           = sanitize_text_field( (string) get_option( 'va_sl_title_color',           '#ffffff' ) );
$sl_meta_color            = sanitize_text_field( (string) get_option( 'va_sl_meta_color',            'rgba(255,255,255,.55)' ) );
$sl_spec_label_color      = sanitize_text_field( (string) get_option( 'va_sl_spec_label_color',      'rgba(255,255,255,.45)' ) );
$sl_spec_val_color        = sanitize_text_field( (string) get_option( 'va_sl_spec_val_color',        '#ffffff' ) );
$sl_desc_color            = sanitize_text_field( (string) get_option( 'va_sl_desc_color',            'rgba(255,255,255,.82)' ) );
$sl_seller_name_color     = sanitize_text_field( (string) get_option( 'va_sl_seller_name_color',     '#ffffff' ) );
$sl_seller_since_color    = sanitize_text_field( (string) get_option( 'va_sl_seller_since_color',    'rgba(255,255,255,.45)' ) );
$sl_demand_bg             = sanitize_text_field( (string) get_option( 'va_sl_demand_bg',             'rgba(255,100,0,.12)' ) );
$sl_demand_border         = sanitize_text_field( (string) get_option( 'va_sl_demand_border',         'rgba(255,100,0,.3)' ) );
$sl_demand_text           = sanitize_text_field( (string) get_option( 'va_sl_demand_text',           '#ff9550' ) );
$sl_featured_pill_bg      = sanitize_text_field( (string) get_option( 'va_sl_featured_pill_bg',      'rgba(255,180,0,.15)' ) );
$sl_featured_pill_border  = sanitize_text_field( (string) get_option( 'va_sl_featured_pill_border',  'rgba(255,180,0,.3)' ) );
$sl_featured_pill_text    = sanitize_text_field( (string) get_option( 'va_sl_featured_pill_text',    '#ffd060' ) );
$sl_views_color           = sanitize_text_field( (string) get_option( 'va_sl_views_color',           'rgba(255,255,255,.45)' ) );
$sl_share_label_color     = sanitize_text_field( (string) get_option( 'va_sl_share_label_color',     'rgba(255,255,255,.45)' ) );
$sl_expired_color         = sanitize_text_field( (string) get_option( 'va_sl_expired_color',         '#ff6060' ) );
$sl_sticky_bg             = sanitize_text_field( (string) get_option( 'va_sl_sticky_bg',             'rgba(10,10,10,.95)' ) );
$sl_sticky_title_color    = sanitize_text_field( (string) get_option( 'va_sl_sticky_title_color',    '#ffffff' ) );
$sl_related_border        = sanitize_text_field( (string) get_option( 'va_sl_related_border',        'rgba(255,255,255,.1)' ) );
$sl_related_title_color   = sanitize_text_field( (string) get_option( 'va_sl_related_title_color',   '#ffffff' ) );
$sl_related_meta_color    = sanitize_text_field( (string) get_option( 'va_sl_related_meta_color',    'rgba(255,255,255,.4)' ) );

// Szoveg label opciok
$sl_lbl_description     = (string) get_option( 'va_sl_lbl_description',     'Le&#237;r&#225;s' );
$sl_lbl_details         = (string) get_option( 'va_sl_lbl_details',         'R&#233;szletek' );
$sl_lbl_seller          = (string) get_option( 'va_sl_lbl_seller',          'Felad&#243;' );
$sl_lbl_more_listings   = (string) get_option( 'va_sl_lbl_more_listings',   'Felad&#243; tov&#225;bbi hirdet&#233;sei' );
$sl_lbl_related         = (string) get_option( 'va_sl_lbl_related',         'Hasonl&#243; hirdet&#233;sek' );
$sl_lbl_phone_btn_text   = (string) get_option( 'va_sl_lbl_phone_btn_label',   'Telefonsz&#225;m megjelen&#237;t&#233;se' );
$sl_phone_icon_size     = max( 12, min( 32, absint( get_option( 'va_sl_phone_icon_size', 16 ) ) ) );
$sl_phone_icon_color    = sanitize_text_field( (string) get_option( 'va_sl_phone_icon_color', '#ffffff' ) );
$sl_watch_btn_color     = sanitize_text_field( (string) get_option( 'va_sl_watch_btn_color', 'rgba(255,255,255,.75)' ) );
$sl_watch_btn_border    = sanitize_text_field( (string) get_option( 'va_sl_watch_btn_border', 'rgba(255,255,255,.2)' ) );
$sl_lbl_email_btn       = (string) get_option( 'va_sl_lbl_email_btn',       '&#9993; E-mail &#252;zenet k&#252;ld&#233;se' );
$sl_lbl_watch_add       = (string) get_option( 'va_sl_lbl_watch_add',       '&#9734; Ment&#233;s kedvencekbe' );
$sl_lbl_watch_remove    = (string) get_option( 'va_sl_lbl_watch_remove',    '&#9733; Kedvencekb&#337;l elt&#225;vol&#237;t&#225;s' );
$sl_lbl_share           = (string) get_option( 'va_sl_lbl_share',           'Megoszt&#225;s:' );
$sl_lbl_zoom            = (string) get_option( 'va_sl_lbl_zoom',            'Nagy&#237;t&#225;s' );
$sl_lbl_no_image        = (string) get_option( 'va_sl_lbl_no_image',        'Nincs k&#233;p' );
$sl_lbl_views_suffix    = (string) get_option( 'va_sl_lbl_views_suffix',    'megtekint&#233;s' );
$sl_lbl_posted          = (string) get_option( 'va_sl_lbl_posted',          'Feladva:' );
$sl_lbl_expires         = (string) get_option( 'va_sl_lbl_expires',         'Lej&#225;r:' );
$sl_lbl_expired_label   = (string) get_option( 'va_sl_lbl_expired_label',   'Lej&#225;rt:' );
$sl_lbl_member_since_pre= (string) get_option( 'va_sl_lbl_member_since_pre', 'Tag' );
$sl_lbl_member_since_suf= (string) get_option( 'va_sl_lbl_member_since_suf', '&#243;ta' );
$sl_lbl_condition_pre   = (string) get_option( 'va_sl_lbl_condition_pre',   '&#193;llapot:' );
$sl_lbl_demand          = (string) get_option( 'va_sl_lbl_demand',          '&#233;rdekl&#337;d&#337; az elm&#250;lt 24 &#243;r&#225;ban figyel&#337;list&#225;j&#225;ra vette' );
$sl_lbl_featured_pill   = (string) get_option( 'va_sl_lbl_featured_pill',   '<?php echo $sl_lbl_featured_pill; ?>' );





// Demand indikátor: figyelőlistára adások az elmúlt 24 órában


$demand_count = 0;


global $wpdb;


$wl_table = $wpdb->prefix . 'va_watchlist';


if ( $wpdb->get_var( "SHOW TABLES LIKE '$wl_table'" ) === $wl_table ) {


    $demand_count = (int) $wpdb->get_var( $wpdb->prepare(


        "SELECT COUNT(*) FROM `{$wl_table}` WHERE post_id = %d AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",


        $post_id


    ) );


}


?>





<style>


.sl {


    max-width: <?php echo esc_attr( (string) $sl_content_max ); ?>px;


    --sl-accent: <?php echo esc_attr( $sl_accent ); ?>;


    --sl-glass: <?php echo esc_attr( $sl_glass ); ?>;


    --sl-border: <?php echo esc_attr( $sl_border ); ?>;


}


.sl__layout { gap: <?php echo esc_attr( (string) $sl_layout_gap ); ?>px; }


.sl--layout-split .sl__layout { grid-template-columns: minmax(0, 1fr) <?php echo esc_attr( (string) $sl_sidebar_width ); ?>px; }


.sl--layout-stacked .sl__layout { grid-template-columns: 1fr; }





.sl .sl__card {


    border-radius: <?php echo esc_attr( (string) $sl_radius ); ?>px;


    padding: <?php echo esc_attr( (string) $sl_padding ); ?>px;


    background: var(--sl-glass);


    border-color: var(--sl-border);


}


.sl .sl__main-wrap { aspect-ratio: <?php echo esc_attr( $sl_gallery_ratio ); ?>; }


.sl .sl__main-img { object-fit: <?php echo esc_attr( $sl_gallery_fit ); ?>; }


.sl .sl__thumb {


    width: <?php echo esc_attr( (string) $sl_thumb_size ); ?>px;


    height: <?php echo esc_attr( (string) floor( $sl_thumb_size * 0.74 ) ); ?>px;


}


.sl .sl__title { font-size: <?php echo esc_attr( (string) $sl_title_size ); ?>px; }


.sl .sl__price { font-size: <?php echo esc_attr( (string) $sl_price_size ); ?>px; color: var(--sl-accent); }


.sl .sl__meta-row span { font-size: <?php echo esc_attr( (string) $sl_meta_size ); ?>px; }


.sl .sl__btn { min-height: <?php echo esc_attr( (string) $sl_btn_height ); ?>px; }


.sl .sl__share-btn {


    width: <?php echo esc_attr( (string) $sl_share_size ); ?>px;


    height: <?php echo esc_attr( (string) $sl_share_size ); ?>px;


}


.sl .sl__cat-pill,


.sl .sl__btn--watch.active,


.sl .sl__btn--phone,


.sl .sl__btn--email { border-color: var(--sl-accent); }


.sl .sl__btn--phone,


.sl .sl__btn--watch.active { background: var(--sl-accent); }


.sl-viewer { background: <?php echo esc_attr( $sl_viewer_bg ); ?>; }


/* Demand badge */


.sl__demand { display:flex;align-items:center;gap:8px;background:rgba(255,100,0,.12);border:1px solid rgba(255,100,0,.3);border-radius:8px;padding:9px 14px;margin-bottom:14px;font-size:13px;color:#ff9550;font-weight:600; }


.sl__demand svg { flex-shrink:0; }


/* Specs table */


.sl__specs-table { display:grid;grid-template-columns:1fr 1fr;gap:0; }


.sl__spec-row { display:flex;flex-direction:column;padding:10px 0;border-bottom:1px solid var(--sl-border); }


.sl__spec-row:nth-child(odd) { padding-right:12px; }


.sl__spec-row:nth-child(even) { padding-left:12px;border-left:1px solid var(--sl-border); }


.sl__spec-label { font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px; }


.sl__spec-val { font-size:14px;font-weight:600;color:#fff; }


.sl__spec-row--full { grid-column:1/-1;padding-right:0 !important;border-left:none !important; }

.sl__params-scroll { position:relative; max-height:min(68vh,720px);overflow:auto;padding-right:6px; }
.sl__params-scroll::-webkit-scrollbar { width:8px; }
.sl__params-scroll::-webkit-scrollbar-thumb {
    border-radius: 8px;
    background: linear-gradient(180deg, rgba(224,119,39,.18) 0%, rgba(224,119,39,.95) 45%, rgba(255,157,72,.9) 70%, rgba(224,119,39,.18) 100%);
    background-size: 100% 220%;
    animation: sl_scrollbar_flow 1.4s linear infinite;
}
.sl__params-scroll::-webkit-scrollbar-track { background:transparent; }
@keyframes sl_scrollbar_flow {
    0% { background-position: 0 0; }
    100% { background-position: 0 220%; }
}

/* Extras pills */
.sl__extras-section { margin-top:18px; }
.sl__extras-heading { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#E07727;margin-bottom:10px; }
.sl__extras-pills + .sl__extras-heading { margin-top:16px; }
.sl__extras-pills { display:flex;flex-wrap:wrap;column-gap:8px;row-gap:10px; }
.sl__extra-pill { font-size:12px;line-height:1.35;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.85); }

/* Highlight badge */


.sl__badge-row { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px; }


.sl__badge { display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;padding:5px 10px;border-radius:20px; }


.sl__badge--damage-no { background:rgba(0,200,100,.12);color:#4dffaa;border:1px solid rgba(0,200,100,.25); }


.sl__badge--damage-yes { background:rgba(255,60,60,.12);color:#ff8080;border:1px solid rgba(255,60,60,.25); }


.sl__badge--service-yes { background:rgba(0,180,255,.1);color:#66ccff;border:1px solid rgba(0,180,255,.2); }


.sl__badge--license { background:rgba(255,180,0,.12);color:#ffd060;border:1px solid rgba(255,180,0,.25); }


.sl__badge--verified { background:rgba(0,210,120,.12);color:#4dffaa;border:1px solid rgba(0,210,120,.3);font-weight:700; }


/* Featured + verified pills – base (dynamic overrides via wp_head) */


.sl__top-pills { display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px; }


.sl__featured-pill { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:0 9px;height:24px;border-radius:20px;background:rgba(255,180,0,.15);color:#ffd060;border:1px solid rgba(255,180,0,.3);white-space:nowrap;flex-shrink:0;line-height:1; }


.sl__verified-pill { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:0 9px;height:24px;border-radius:20px;background:rgba(0,210,120,.12);color:#4dffaa;border:1px solid rgba(0,210,120,.3);white-space:nowrap;flex-shrink:0;line-height:1; }


/* Related listings */


.sl__related-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:6px; }


@media(max-width:700px){ .sl__related-grid { grid-template-columns:repeat(2,1fr); } }


.sl__rel-item { text-decoration:none;color:#fff;border:1px solid var(--sl-border);border-radius:10px;overflow:hidden;display:flex;flex-direction:column;transition:border-color .18s; }


.sl__rel-item:hover { border-color:var(--sl-accent); }


.sl__rel-img { width:100%;aspect-ratio:4/3;object-fit:cover;background:#1a1a1a; }


.sl__rel-img--empty { width:100%;aspect-ratio:4/3;background:#1a1a1a;display:flex;align-items:center;justify-content:center;font-size:28px;color:rgba(255,255,255,.2); }


.sl__rel-info { padding:10px 12px; }


.sl__rel-title { font-size:13px;font-weight:600;margin-bottom:4px;line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical; }


.sl__rel-price { font-size:14px;font-weight:700;color:var(--sl-accent); }


.sl__rel-meta { font-size:11px;color:rgba(255,255,255,.4);margin-top:3px; }


/* Bottom sticky bar */


.sl__sticky-bar { position:fixed;bottom:0;left:0;right:0;z-index:999;background:rgba(10,10,10,.95);backdrop-filter:blur(12px);border-top:1px solid rgba(255,255,255,.1);padding:12px 20px;display:flex;align-items:center;gap:12px;transform:translateY(100%);transition:transform .3s; }


.sl__sticky-bar.visible { transform:translateY(0); }


.sl__sticky-title { font-size:14px;font-weight:700;flex:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis; }


.sl__sticky-price { font-size:16px;font-weight:800;color:var(--sl-accent);white-space:nowrap; }


.sl__sticky-btn { padding:10px 18px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;border:none; }


.sl__sticky-btn--phone { background:var(--sl-accent);color:#fff; }


.sl__sticky-btn--watch { background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2) !important; }


.sl__sticky-btn--watch.active { background:var(--sl-accent);border-color:var(--sl-accent) !important; }


@media(max-width:600px){ .sl__sticky-title { display:none; } }





@media (max-width: 900px) {


    .sl .sl__title { font-size: <?php echo esc_attr( (string) max( 20, (int) floor( $sl_title_size * ( $sl_mobile_scale / 100 ) ) ) ); ?>px; }


    .sl .sl__price { font-size: <?php echo esc_attr( (string) max( 22, (int) floor( $sl_price_size * 0.82 ) ) ); ?>px; }


    .sl--layout-split .sl__layout { grid-template-columns: 1fr; }


    .sl__params-scroll { max-height:none;overflow:visible;padding-right:0; }


    .sl__specs-table { grid-template-columns:1fr; }


    .sl__spec-row:nth-child(even) { border-left:none;padding-left:0; }


}


/* Dinamikus szinek – get_option-bol */
.sl__card-title { color: <?php echo esc_attr( $sl_card_title_color ); ?> !important; }
.sl__title { color: <?php echo esc_attr( $sl_title_color ); ?> !important; }
.sl__meta-row span { color: <?php echo esc_attr( $sl_meta_color ); ?> !important; }
.sl__views { color: <?php echo esc_attr( $sl_views_color ); ?> !important; }
.sl__desc-body,.sl__desc-body p,.sl__desc-body li,.sl__desc-body h1,.sl__desc-body h2,.sl__desc-body h3,.sl__desc-body h4,.sl__desc-body h5,.sl__desc-body h6,.sl__desc-body span,.sl__desc-body strong,.sl__desc-body em,.sl__desc-body a { color: <?php echo esc_attr( $sl_desc_color ); ?> !important; }
.sl__spec-label { color: <?php echo esc_attr( $sl_spec_label_color ); ?> !important; }
.sl__spec-val { color: <?php echo esc_attr( $sl_spec_val_color ); ?> !important; }
.sl__seller-name { color: <?php echo esc_attr( $sl_seller_name_color ); ?> !important; }
.sl__seller-since { color: <?php echo esc_attr( $sl_seller_since_color ); ?> !important; }
.sl__expired { color: <?php echo esc_attr( $sl_expired_color ); ?> !important; }
.sl__share-label { color: <?php echo esc_attr( $sl_share_label_color ); ?> !important; }
.sl__demand { background: <?php echo esc_attr( $sl_demand_bg ); ?> !important; border-color: <?php echo esc_attr( $sl_demand_border ); ?> !important; color: <?php echo esc_attr( $sl_demand_text ); ?> !important; }
.sl__featured-pill { background: <?php echo esc_attr( $sl_featured_pill_bg ); ?> !important; border-color: <?php echo esc_attr( $sl_featured_pill_border ); ?> !important; color: <?php echo esc_attr( $sl_featured_pill_text ); ?> !important; }
.sl__sticky-bar { background: <?php echo esc_attr( $sl_sticky_bg ); ?> !important; }
.sl__sticky-title { color: <?php echo esc_attr( $sl_sticky_title_color ); ?> !important; }
.sl__rel-item { border-color: <?php echo esc_attr( $sl_related_border ); ?> !important; }
.sl__rel-title { color: <?php echo esc_attr( $sl_related_title_color ); ?> !important; }
.sl__rel-meta { color: <?php echo esc_attr( $sl_related_meta_color ); ?> !important; }
.sl__more-item { display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.07);text-decoration:none; }
.sl__more-item:last-child { border-bottom:none; }
.sl__more-img { width:54px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0; }
.sl__more-img--empty { display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.05);font-size:20px; }
.sl__more-title { font-size:13px;font-weight:600;color:#fff;line-height:1.3; }
.sl__more-price { font-size:13px;font-weight:700;color:#e27019;margin-top:2px; }
.sl__more-list { max-height:190px;overflow:auto;padding-right:4px; }
.sl__more-list::-webkit-scrollbar { width:8px; }
.sl__more-list::-webkit-scrollbar-thumb { background:rgba(255,255,255,.2);border-radius:8px; }
.sl__more-list::-webkit-scrollbar-track { background:transparent; }
.sl__phone-icon { font-size: <?php echo esc_attr( (string) $sl_phone_icon_size ); ?>px !important; color: <?php echo esc_attr( $sl_phone_icon_color ); ?> !important; line-height:1; vertical-align:middle; margin-right:5px; }
.sl__btn--watch { color: <?php echo esc_attr( $sl_watch_btn_color ); ?> !important; border-color: <?php echo esc_attr( $sl_watch_btn_border ); ?> !important; }
</style>





<div class="sl sl--layout-<?php echo esc_attr( $sl_layout_mode ); ?>">





    <div class="sl__layout">





        <!-- BAL: galeria + leiras -->


        <div class="sl__left">





            <div class="sl__gallery">


                <div class="sl__main-wrap">


                    <?php if ( ! empty($attachment_ids) ):


                        $main_url = wp_get_attachment_image_url( $attachment_ids[0], 'large' );


                    ?>


                        <img src="<?php echo esc_url($main_url); ?>"


                             id="sl-main-img" class="sl__main-img"


                             alt="<?php the_title_attribute(); ?>">


                        <button type="button" class="sl__zoom-trigger" id="sl-zoom-trigger" aria-label="Kép nagyítása">


                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="17" height="17" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>


                            <span>Nagyítás</span>


                        </button>


                    <?php elseif ( $demo_image_url !== '' ): ?>


                        <img src="<?php echo esc_url( $demo_image_url ); ?>"


                             id="sl-main-img" class="sl__main-img"


                             alt="<?php the_title_attribute(); ?>">


                    <?php else: ?>


                        <div class="sl__main-img sl__main-empty"><?php echo $sl_lbl_no_image; ?></div>


                    <?php endif; ?>


                </div>





                <?php if ( count($attachment_ids) > 1 ): ?>


                <div class="sl__thumbs">


                    <?php foreach ( $attachment_ids as $i => $att_id ):


                        $t = wp_get_attachment_image_url( $att_id, 'thumbnail' );


                        $l = wp_get_attachment_image_url( $att_id, 'large' );


                    ?>


                        <img src="<?php echo esc_url($t); ?>"


                             class="sl__thumb<?php echo $i===0?' sl__thumb--active':''; ?>"


                             data-src="<?php echo esc_url($l); ?>" alt="">


                    <?php endforeach; ?>


                </div>


                <?php endif; ?>


            </div>





            <!-- Leiras -->


            <?php if ( get_the_content() ): ?>


            <div class="sl__card sl__desc">


                <div class="sl__card-title"><?php echo $sl_lbl_description; ?></div>


                <div class="sl__desc-body"><?php the_content(); ?></div>


            </div>


            <?php endif; ?>





        </div><!-- .sl__left -->





        <!-- JOBB: fejlec + adatok + kontakt -->


        <div class="sl__right">





            <!-- Fejlec: kategoria + cim + ar -->


            <div class="sl__card sl__head">


                <div class="sl__top-pills">


                    <?php if ( $categories && !is_wp_error($categories) ): ?>


                        <a href="<?php echo esc_url(get_term_link($categories[0])); ?>" class="sl__cat-pill">


                            <?php echo esc_html($categories[0]->name); ?>


                        </a>


                    <?php endif; ?>


                    <?php if ($featured): ?><span class="sl__featured-pill"><?php echo $sl_lbl_featured_pill; ?></span><?php endif; ?>


                </div>


                <h1 class="sl__title">


                    <?php the_title(); ?>


                </h1>


                <?php if ( $has_sale ): ?>
                <del style="color:rgba(255,255,255,.35);font-size:18px;font-weight:400;display:block;line-height:1.3;margin-bottom:2px;"><?php echo esc_html( va_format_price( $price, $price_type ) ); ?></del>
                <div class="sl__price" style="color:#ff3333;"><?php echo esc_html( number_format( floatval( $sale_price ), 0, ',', ' ' ) . ' Ft' ); ?> <span style="font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;background:rgba(255,0,0,.2);border:1px solid rgba(255,0,0,.5);border-radius:6px;padding:2px 8px;color:#ff8888;vertical-align:middle;">AKCIÓ</span></div>
                <?php else: ?>
                <div class="sl__price"><?php echo esc_html( va_format_price($price, $price_type) ); ?></div>
                <?php endif; ?>





                <?php if ( $demand_count >= 2 ): ?>


                <div class="sl__demand">


                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>


                    <?php echo esc_html( sprintf( 'A(z) %d érdeklődő az elmúlt 24 órában figyelőlistájára vette', $demand_count ) ); ?>


                </div>


                <?php endif; ?>





                <div class="sl__meta-row">


                    <?php if ( $county && !is_wp_error($county) ): ?>


                        <span>&#128205; <?php echo esc_html($county[0]->name); ?></span>


                    <?php endif; ?>


                    <?php if ( $location ): ?>


                        <span><?php echo esc_html($location); ?></span>


                    <?php endif; ?>


                    <?php if ( $condition && !is_wp_error($condition) ): ?>


                        <span><?php echo $sl_lbl_condition_pre; ?> <?php echo esc_html($condition[0]->name); ?></span>


                    <?php endif; ?>


                    <span class="sl__views">


                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15" style="vertical-align:-2px;margin-right:4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?php echo esc_html( number_format($views, 0, ',', ' ') ); ?> megtekint&#233;s


                    </span>


                    <span><?php echo $sl_lbl_posted; ?> <?php echo esc_html(get_the_date('Y. m. d.')); ?></span>


                    <?php if ( $expires && strtotime($expires) > time() ): ?>


                        <span>Lej&#225;r: <?php echo esc_html($expires); ?></span>


                    <?php elseif ( $expires && strtotime($expires) <= time() ): ?>


                        <span class="sl__expired">Lej&#225;rt: <?php echo esc_html($expires); ?></span>


                    <?php endif; ?>


                </div>


            </div>





            <!-- Adatok / Specifications -->


            <?php


            $specs = [];


            if ( $site_type === 'jarmu' ) {


                if ( $vehicle_type )   $specs[] = [ 'J&#225;rm&#369;kateg&#243;ria',       $vtype_labels[$vehicle_type] ?? $vehicle_type, false ];


                if ( $brand )          $specs[] = [ 'Gy&#225;rt&#243;',               $brand,       false ];


                if ( $model )          $specs[] = [ 'Modell',               $model,       false ];


                if ( $year )           $specs[] = [ '&#201;vj&#225;rat',              $year,        false ];


                if ( $first_reg )      $specs[] = [ 'Els&#337; forgalomba hely.',$first_reg,   false ];


                if ( $mileage )        $specs[] = [ 'Kilom&#233;ter&#243;ra',         number_format((int)$mileage,0,',',' ').' km', false ];


                if ( $fuel_type )      $specs[] = [ '&#220;zemanyag',            $fuel_labels[$fuel_type] ?? $fuel_type, false ];


                if ( $performance_kw ) $specs[] = [ 'Teljes&#237;tm&#233;ny',         $performance_kw.' kW / '.round($performance_kw*1.36).' LE', false ];


                if ( $engine_size )    $specs[] = [ 'Henger&#369;rtartalom',     number_format((int)$engine_size,0,',',' ').' cm&#179;', false ];


                if ( $transmission )   $specs[] = [ 'Sebess&#233;gv&#225;lt&#243;',        $trans_labels[$transmission] ?? $transmission, false ];


                if ( $body_type )      $specs[] = [ 'Fel&#233;p&#237;tm&#233;ny',          $body_labels[$body_type] ?? $body_type, false ];


                if ( $color_val )      $specs[] = [ 'Sz&#237;n',                 $color_val,   false ];


                if ( $doors )          $specs[] = [ 'Ajt&#243;k sz&#225;ma',          $doors,       false ];


                if ( $owners )         $specs[] = [ 'Tulajdonosok sz.',     $owners,      false ];


                if ( $keys_count )     $specs[] = [ 'Kulcsok',        $keys_count . ' db kulcs jár a gépjárműhöz',  false ];


                if ( $tech_inspect )   $specs[] = [ 'M&#369;szaki lej&#225;r',        $tech_inspect,false ];
                if ( $drive )          $specs[] = [ 'Hajt&#225;s',                $drive_labels[$drive] ?? $drive, false ];
                if ( $vehicle_cond )   $specs[] = [ 'J&#225;rm&#369; &#225;llapota',      $vcond_labels[$vehicle_cond] ?? $vehicle_cond, false ];
                if ( $doc_type )       $specs[] = [ 'Okiratok jellege',       $doctype_labels[$doc_type] ?? $doc_type, false ];
                if ( $doc_validity )   $specs[] = [ 'Okiratok &#233;rv&#233;nyess&#233;ge', $docval_labels[$doc_validity] ?? $doc_validity, false ];
                if ( $ac_type )        $specs[] = [ 'Kl&#237;ma',                 $ac_labels[$ac_type] ?? $ac_type, false ];
                if ( $eco_class )      $specs[] = [ 'K&#246;rny. oszt&#225;ly',        $eco_labels[$eco_class] ?? $eco_class, false ];
                if ( $cylinder_layout )$specs[] = [ 'Henger-elrendez&#233;s',   $cyl_labels[$cylinder_layout] ?? $cylinder_layout, false ];
                if ( $own_weight )     $specs[] = [ 'Saját tömeg',           number_format((int)$own_weight,0,',',' ').' kg', false ];
                if ( $gross_weight )   $specs[] = [ 'Össztömeg',              number_format((int)$gross_weight,0,',',' ').' kg', false ];
                if ( $passengers )     $specs[] = [ 'Szállítható személyek', (int)$passengers.' fő', false ];
                if ( $trunk_liters )   $specs[] = [ 'Csomagtartó',           (int)$trunk_liters.' l', false ];
                if ( $roof_type )      $specs[] = [ 'Tető',                  $roof_labels[$roof_type] ?? $roof_type, false ];
                if ( $color_metallic === '1' ) $specs[] = [ 'Fényezés', 'Metál', false ];
                if ( $upholstery_1 )   $specs[] = [ 'Kárpit (1)',            esc_html($upholstery_1), false ];
                if ( $upholstery_2 )   $specs[] = [ 'Kárpit (2)',            esc_html($upholstery_2), false ];
                if ( $range_gearbox === '1' ) $specs[] = [ 'Felező váltó', 'Igen', false ];
                if ( $summer_tire_front ) $specs[] = [ 'Nyári gumi (első)',  esc_html($summer_tire_front), false ];
                if ( $summer_tire_rear )  $specs[] = [ 'Nyári gumi (hátsó)', esc_html($summer_tire_rear), false ];
                if ( $winter_tire_front ) $specs[] = [ 'Téli gumi (első)',   esc_html($winter_tire_front), false ];
                if ( $winter_tire_rear )  $specs[] = [ 'Téli gumi (hátsó)',  esc_html($winter_tire_rear), false ];


            } elseif ( $site_type === 'ingatlan' ) {


                if ( $area_m2 )        $specs[] = [ 'Alapter&#252;let',          $area_m2.' m&#178;',       false ];


                if ( $rooms )          $specs[] = [ 'Szob&#225;k',               $rooms,               false ];


                if ( $floor !== '' )   $specs[] = [ 'Emelet',               $floor.'. emelet',    false ];


                if ( $lot_size )       $specs[] = [ 'Telekter&#252;let',         $lot_size.' m&#178;',      false ];


                if ( $building_year )  $specs[] = [ '&#201;p&#237;t&#233;si &#233;v',           $building_year,       false ];


                if ( $parking )        $specs[] = [ 'Parkol&#243;',              $park_labels[$parking] ?? $parking, false ];


                if ( $furnished )      $specs[] = [ 'B&#250;torozott',           $furn_labels[$furnished] ?? $furnished, false ];


                if ( $heating )        $specs[] = [ 'F&#369;t&#233;s',                $heat_labels[$heating] ?? $heating, false ];


            } else {


                if ( $brand )          $specs[] = [ 'M&#225;rka / Gy&#225;rt&#243;',       $brand,  false ];


                if ( $model )          $specs[] = [ 'Modell / T&#237;pus',       $model,  false ];


                if ( $caliber )        $specs[] = [ 'Kaliber',              $caliber,false ];


                if ( $year )           $specs[] = [ 'Gy&#225;rt&#225;si &#233;v',          $year,   false ];


                // Optikai mezők
                if ( $optic_type ) {
                    $optic_type_labels = [ 'celtavcso' => 'Céltávcső', 'kereso' => 'Kereső', 'spektiv' => 'Spektív', 'egyeb' => 'Egyéb' ];
                    $specs[] = [ 'Távcső típusa',        $optic_type_labels[$optic_type] ?? $optic_type, false ];
                }
                if ( $optic_zoom )      $specs[] = [ 'Nagyítás',             $optic_zoom,  false ];
                if ( $optic_objective ) $specs[] = [ 'Objektív átmérő',     $optic_objective . ' mm', false ];


            }





            // Fallback: minden kitoltott, de fent kulon nem listazott va_* mezot jelenitsunk meg.
            $known_fields = [];
            if ( class_exists( 'VA_Meta_Fields' ) && method_exists( 'VA_Meta_Fields', 'listing_fields' ) ) {
                $known_fields = (array) VA_Meta_Fields::listing_fields();
            }

            $single_extra_fields = [
                'va_optic_magnification_min' => [ 'label' => 'Nagyítás minimum', 'type' => 'text' ],
                'va_optic_magnification_max' => [ 'label' => 'Nagyítás maximum', 'type' => 'text' ],
                'va_optic_magnification_fixed' => [ 'label' => 'Fix nagyítás', 'type' => 'text' ],
                'va_optic_tube_diameter' => [ 'label' => 'Csőátmérő (mm)', 'type' => 'select', 'options' => [ '25.4' => '25.4 mm (1 inch)', '30' => '30 mm', '34' => '34 mm', '35' => '35 mm', '36' => '36 mm', '40' => '40 mm' ] ],
                'va_optic_exit_pupil' => [ 'label' => 'Kilépő pupilla (mm)', 'type' => 'text' ],
                'va_optic_field_of_view' => [ 'label' => 'Látómező 100 m-en (m)', 'type' => 'text' ],
                'va_optic_eye_relief' => [ 'label' => 'Eye relief (mm)', 'type' => 'text' ],
                'va_optic_reticle_type' => [ 'label' => 'Reticle típus', 'type' => 'select', 'options' => [ 'duplex' => 'Duplex', 'mil-dot' => 'Mil-Dot', 'moa' => 'MOA', 'bdc' => 'BDC', '4a' => '4A', 'german-4' => 'German #4', 'horseshoe' => 'Horseshoe', 'dot' => 'Dot', 'egyeb' => 'Egyéb' ] ],
                'va_optic_reticle_plane' => [ 'label' => 'Reticle sík', 'type' => 'select', 'options' => [ 'ffp' => 'FFP', 'sfp' => 'SFP' ] ],
                'va_optic_illumination' => [ 'label' => 'Megvilágítás', 'type' => 'select', 'options' => [ 'nincs' => 'Nincs', 'piros' => 'Piros', 'zold' => 'Zöld', 'piros-zold' => 'Piros + zöld' ] ],
                'va_optic_illumination_color' => [ 'label' => 'Megvilágítás színe', 'type' => 'text' ],
                'va_optic_click_value' => [ 'label' => 'Klikk érték', 'type' => 'select', 'options' => [ '1-4-moa' => '1/4 MOA', '1-8-moa' => '1/8 MOA', '0-1-mrad' => '0.1 MRAD' ] ],
                'va_optic_parallax_adjustment' => [ 'label' => 'Parallax állítás', 'type' => 'select', 'options' => [ 'fix' => 'Fix', 'ao' => 'AO', 'oldalso' => 'Oldalsó tekerő', 'nincs' => 'Nincs' ] ],
                'va_optic_turret_type' => [ 'label' => 'Torony típus', 'type' => 'select', 'options' => [ 'low-profile' => 'Low profile', 'target' => 'Target', 'locking' => 'Locking', 'capped' => 'Capped' ] ],
                'va_optic_body_material' => [ 'label' => 'Test anyag', 'type' => 'select', 'options' => [ 'aluminium' => 'Alumínium', 'magnzium' => 'Magnézium', 'acel' => 'Acél', 'polymer' => 'Polimer' ] ],
                'va_optic_coating_type' => [ 'label' => 'Bevonat típusa', 'type' => 'select', 'options' => [ 'coated' => 'Coated', 'fully-coated' => 'Fully coated', 'multi-coated' => 'Multi-coated', 'fully-multi-coated' => 'Fully multi-coated' ] ],
                'va_optic_waterproof_flags' => [ 'label' => 'Vízállóság', 'type' => 'checkboxes', 'options' => [ 'vizallo' => 'Vízálló', 'paramentes' => 'Páramentes', 'nitrogen' => 'Nitrogén töltés', 'argon' => 'Argon töltés', 'shockproof' => 'Ütésálló' ] ],
                'va_optic_prism_system' => [ 'label' => 'Prizma rendszer', 'type' => 'select', 'options' => [ 'roof' => 'Roof', 'porro' => 'Porro' ] ],
                'va_optic_glass_type' => [ 'label' => 'Üvegtípus', 'type' => 'select', 'options' => [ 'ed' => 'ED', 'hd' => 'HD', 'apokromat' => 'Apokromát', 'standard' => 'Standard' ] ],
                'va_optic_dot_size' => [ 'label' => 'Pontméret (MOA)', 'type' => 'text' ],
                'va_optic_rail_compatibility' => [ 'label' => 'Sín kompatibilitás', 'type' => 'checkboxes', 'options' => [ '11mm' => '11 mm', 'weaver' => 'Weaver', 'picatinny' => 'Picatinny', 'zeiss' => 'Zeiss' ] ],
                'va_optic_rangefinder_max_m' => [ 'label' => 'Távmérő max (m)', 'type' => 'text' ],
                'va_optic_ballistic_calc' => [ 'label' => 'Ballisztikai kalkulátor', 'type' => 'select', 'options' => [ 'nincs' => 'Nincs', 'van' => 'Van' ] ],
                'va_optic_spektiv_zoom_range' => [ 'label' => 'Spektív zoom tartomány', 'type' => 'select', 'options' => [ '15-45' => '15-45x', '20-60' => '20-60x', '25-75' => '25-75x', '30-90' => '30-90x' ] ],
                'va_optic_eyepiece_type' => [ 'label' => 'Okulár típus', 'type' => 'select', 'options' => [ '45-fok' => '45°', 'egyenes' => 'Egyenes', 'cserelheto' => 'Cserélhető' ] ],
                'va_optic_weapon_compatibility' => [ 'label' => 'Fegyver kompatibilitás', 'type' => 'checkboxes', 'options' => [ 'legpuska' => 'Légpuska', '22lr' => '.22 LR', 'kozepkaliber' => 'Közepes kaliber', 'nagyvadu' => 'Nagyvad', 'airsoft' => 'Airsoft' ] ],
                'va_scope_type' => [ 'label' => 'Kialakítás', 'type' => 'select', 'options' => [ 'monokuláris' => 'Monokuláris', 'biokuláris' => 'Biokuláris' ] ],
                'va_scope_accessories' => [ 'label' => 'Tartozékok', 'type' => 'text' ],
                'va_scope_accessories_flags' => [ 'label' => 'Tartozék jelölők', 'type' => 'checkboxes', 'options' => [ 'doboz' => 'Doboz', 'kupak' => 'Lencsevédő kupak', 'napellenzo' => 'Napellenző', 'szelelek' => 'Szerelék', 'garancia' => 'Garancia' ] ],
                'va_scope_coatings' => [ 'label' => 'Lencsebevonat', 'type' => 'text' ],
                'va_optic_mount_type' => [ 'label' => 'Szerelék típusa', 'type' => 'select', 'options' => [ 'fix' => 'Fix', 'quick-detach' => 'Quick detach', 'cantilever' => 'Cantilever', 'ring' => 'Gyűrűs' ] ],
                'va_optic_shipping_methods' => [ 'label' => 'Szállítás módja', 'type' => 'checkboxes', 'options' => [ 'szemelyes' => 'Személyes átvétel', 'futar' => 'Futár', 'foxpost' => 'Foxpost', 'posta' => 'Posta' ] ],
                'va_optic_recommended_use' => [ 'label' => 'Ajánlott felhasználás', 'type' => 'checkboxes', 'options' => [ 'hajtas' => 'Hajtás', 'lesen' => 'Les', 'sportloveszet' => 'Sportlövészet', 'tura' => 'Túra', 'airsoft' => 'Airsoft' ] ],
            ];

            $known_fields = array_merge( $known_fields, $single_extra_fields );

            $already_rendered_meta_keys = [
                'va_vehicle_type','va_brand','va_model','va_caliber','va_year','va_first_reg','va_mileage','va_fuel_type',
                'va_performance_kw','va_engine_size','va_transmission','va_body_type','va_color','va_doors','va_owners','va_keys',
                'va_tech_inspect','va_drive','va_vehicle_condition','va_doc_type','va_doc_validity','va_ac_type','va_eco_class',
                'va_cylinder_layout','va_own_weight','va_gross_weight','va_passengers','va_trunk_liters','va_roof_type',
                'va_color_metallic','va_upholstery_1','va_upholstery_2','va_range_gearbox','va_summer_tire_front','va_summer_tire_rear',
                'va_winter_tire_front','va_winter_tire_rear','va_area_m2','va_rooms','va_floor','va_lot_size','va_building_year',
                'va_parking','va_furnished','va_heating','va_balcony','va_optic_type','va_optic_zoom','va_optic_objective',
                'va_dog_breed','va_dog_gender','va_dog_color','va_dog_purebred','va_dog_age_months','va_extras',
                'va_price','va_price_type','va_sale_price','va_sale_price_end','va_phone','va_location','va_views','va_expires',
                'va_featured','va_verified','va_license_req','va_email_show','va_gallery_ids','va_demo_image'
            ];

            $all_meta_keys = get_post_custom_keys( $post_id );
            if ( is_array( $all_meta_keys ) ) {
                foreach ( $all_meta_keys as $meta_key ) {
                    $meta_key = (string) $meta_key;
                    if ( strpos( $meta_key, 'va_' ) !== 0 ) {
                        continue;
                    }
                    if ( in_array( $meta_key, $already_rendered_meta_keys, true ) ) {
                        continue;
                    }
                    if ( preg_match( '/(?:needs_review|review_hits|moderation|_nonce$|_token$|_hash$)/', $meta_key ) ) {
                        continue;
                    }

                    $raw = get_post_meta( $post_id, $meta_key, true );
                    if ( is_array( $raw ) ) {
                        $raw = array_filter( array_map( 'strval', $raw ), static function( $v ) {
                            return $v !== '';
                        } );
                        if ( empty( $raw ) ) {
                            continue;
                        }
                    }

                    $display_value = '';
                    $field_def     = is_array( $known_fields ) ? ( $known_fields[ $meta_key ] ?? null ) : null;

                    if ( ! is_array( $field_def ) || empty( $field_def['label'] ) ) {
                        continue;
                    }

                    if ( is_array( $raw ) ) {
                        if ( is_array( $field_def ) && ( $field_def['type'] ?? '' ) === 'checkboxes' && ! empty( $field_def['options'] ) ) {
                            $mapped = [];
                            foreach ( $raw as $opt_key ) {
                                $mapped[] = (string) ( $field_def['options'][ $opt_key ] ?? $opt_key );
                            }
                            $display_value = implode( ', ', array_filter( $mapped, static function( $v ) { return $v !== ''; } ) );
                        } else {
                            $display_value = implode( ', ', $raw );
                        }
                    } else {
                        $raw_str = is_scalar( $raw ) ? trim( (string) $raw ) : '';
                        if ( $raw_str === '' ) {
                            continue;
                        }

                        if ( ( $field_def['type'] ?? '' ) === 'checkbox' && ! in_array( strtolower( $raw_str ), [ '1', 'true', 'yes', 'on' ], true ) ) {
                            continue;
                        }

                        $json = json_decode( $raw_str, true );
                        if ( is_array( $json ) ) {
                            $vals = array_values( array_filter( array_map( 'strval', $json ), static function( $v ) {
                                return $v !== '';
                            } ) );
                            if ( empty( $vals ) ) {
                                continue;
                            }
                            if ( is_array( $field_def ) && ( $field_def['type'] ?? '' ) === 'checkboxes' && ! empty( $field_def['options'] ) ) {
                                $mapped = [];
                                foreach ( $vals as $opt_key ) {
                                    $mapped[] = (string) ( $field_def['options'][ $opt_key ] ?? $opt_key );
                                }
                                $display_value = implode( ', ', array_filter( $mapped, static function( $v ) { return $v !== ''; } ) );
                            } else {
                                $display_value = implode( ', ', $vals );
                            }
                        } else {
                            if ( is_array( $field_def ) && ( $field_def['type'] ?? '' ) === 'checkbox' ) {
                                $display_value = in_array( strtolower( $raw_str ), [ '1', 'true', 'yes', 'on' ], true ) ? 'Igen' : 'Nem';
                            } elseif ( is_array( $field_def ) && ( $field_def['type'] ?? '' ) === 'select' && ! empty( $field_def['options'] ) ) {
                                $display_value = (string) ( $field_def['options'][ $raw_str ] ?? $raw_str );
                            } else {
                                $display_value = $raw_str;
                            }
                        }
                    }

                    $display_value = trim( $display_value );
                    if ( $display_value === '' ) {
                        continue;
                    }

                    $label = is_array( $field_def ) && ! empty( $field_def['label'] )
                        ? (string) $field_def['label']
                        : ucwords( str_replace( '_', ' ', preg_replace( '/^va_/', '', $meta_key ) ) );

                    $specs[] = [ html_entity_decode( $label, ENT_QUOTES, 'UTF-8' ), $display_value, false ];
                }
            }

            $badges = [];


            if ( $site_type === 'jarmu' ) {


                $badges[] = $prev_damage === '1'


                    ? ['damage-yes','&#9888; Kor&#225;bbi k&#225;r / baleset']


                    : ['damage-no', '&#10003; Nincs kor&#225;bbi k&#225;r'];


                if ( $service_book === '1' ) $badges[] = ['service-yes','&#10003; Szervizk&#246;nyv megvan'];


            }


            if ( $verified )              $badges[] = ['verified',    '&#10003; Ellen&#337;rz&#246;tt hirdeto'];


            if ( $license_req === '1' )  $badges[] = ['license',     '&#9888; Fegyverenged&#233;ly sz&#252;ks&#233;ges'];


            if ( $balcony === '1' )       $badges[] = ['service-yes', '&#10003; Erk&#233;ly / terasz'];





            if ( ! empty($specs) || ! empty($badges) ):


            ?>


            <div class="sl__card sl__params">


                <div class="sl__card-title">R&#233;szletek</div>


                <div class="sl__params-scroll">


                <?php if ( ! empty($badges) ): ?>


                <div class="sl__badge-row">


                    <?php foreach ( $badges as $b ): ?>


                        <span class="sl__badge sl__badge--<?php echo esc_attr($b[0]); ?>"><?php echo esc_html($b[1]); ?></span>


                    <?php endforeach; ?>


                </div>


                <?php endif; ?>


                <?php if ( ! empty($specs) ): ?>


                <div class="sl__specs-table">


                    <?php foreach ( $specs as $spec ):


                        $full_cls = ! empty($spec[2]) ? ' sl__spec-row--full' : '';


                    ?>


                    <div class="sl__spec-row<?php echo esc_attr($full_cls); ?>">


                        <span class="sl__spec-label"><?php echo esc_html($spec[0]); ?></span>


                        <span class="sl__spec-val"><?php echo esc_html($spec[1]); ?></span>


                    </div>


                    <?php endforeach; ?>


                </div>


                <?php endif; ?>

                <?php if ( $site_type === 'jarmu' && ! empty( $extras_arr ) ): ?>
                <div class="sl__extras-section">
                    <?php foreach ( $extras_by_grp as $grp_key => $grp ):
                        $grp_items = array_filter( $grp['items'], function( $ekey ) use ( $extras_arr ) {
                            return in_array( $ekey, $extras_arr, true );
                        }, ARRAY_FILTER_USE_KEY );
                        if ( empty( $grp_items ) ) continue;
                    ?>
                    <div class="sl__extras-heading sl__extras-heading--<?php echo esc_attr( $grp_key ); ?>"><?php echo esc_html( $grp['label'] ); ?></div>
                    <div class="sl__extras-pills">
                        <?php foreach ( $grp_items as $ekey => $elabel ): ?>
                        <span class="sl__extra-pill"><?php echo esc_html( $elabel ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>


                </div>

            </div>
            <?php endif; ?>





            <!-- Felado + kapcsolat -->


            <div class="sl__card sl__contact">


                <div class="sl__card-title">Felad&#243;</div>


                <?php


                $author_avatar_id  = $author ? (int) get_user_meta( $author->ID, 'va_profile_avatar_id', true ) : 0;


                $author_avatar_url = $author_avatar_id ? wp_get_attachment_image_url( $author_avatar_id, 'thumbnail' ) : '';


                ?>


                <div class="sl__seller">


                    <div class="sl__seller-av">


                        <?php if ( $author_avatar_url ): ?>


                            <img src="<?php echo esc_url( $author_avatar_url ); ?>" alt="Felad&#243; profijk&#233;pe">


                        <?php else: ?>


                            <?php echo strtoupper( substr( $author ? $author->display_name : 'X', 0, 1 ) ); ?>


                        <?php endif; ?>


                    </div>


                    <div>


                        <div class="sl__seller-name"><?php echo esc_html($author ? $author->display_name : 'Ismeretlen'); ?></div>


                        <?php if ($author): ?>


                        <div class="sl__seller-since"><?php echo $sl_lbl_member_since_pre; ?> <?php echo esc_html(date('Y', strtotime($author->user_registered))); ?> &#243;ta</div>


                        <?php


                        if ( get_option( 'va_single_show_plan_badge', '1' ) === '1' && class_exists( 'VA_User_Roles' ) ):


                            $author_plan  = VA_User_Roles::get_user_plan( $author->ID );


                            $all_plan_cfg = VA_User_Roles::get_all_plan_configs();


                            $plan_labels  = [ 'basic'=>'Alap','silver'=>'Ez&#252;st','gold'=>'Arany','platinum'=>'Platina','custom'=>'Egyedi' ];


                            if ( in_array( $author_plan, [ 'platinum', 'custom' ], true ) ) {


                                $user_seller_label = get_user_meta( $author->ID, 'va_seller_label', true );


                                $plan_label = ! empty($user_seller_label)


                                    ? sanitize_text_field($user_seller_label)


                                    : ( ! empty($all_plan_cfg[$author_plan]['seller_label'])


                                        ? sanitize_text_field($all_plan_cfg[$author_plan]['seller_label'])


                                        : ( $plan_labels[$author_plan] ?? ucfirst($author_plan) ) );


                            } else {


                                $plan_label = $plan_labels[$author_plan] ?? ucfirst($author_plan);


                            }


                            $plan_icons = ['basic'=>'','silver'=>'&#10086;','gold'=>'&#9733;','platinum'=>'&#9670;','custom'=>'&#9873;'];


                            $plan_icon  = $plan_icons[$author_plan] ?? '';


                        ?>


                        <div class="sl__plan-badge sl__plan-badge--<?php echo esc_attr($author_plan); ?>">


                            <?php if ($plan_icon) echo esc_html($plan_icon).' '; ?><?php echo esc_html($plan_label); ?> tag


                        </div>


                        <?php endif; ?>


                        <?php endif; ?>


                    </div>


                </div>





                <?php


                $author_phone = $author ? get_user_meta($author->ID,'va_phone',true) : '';


                $show_phone   = $phone ?: $author_phone;


                if ( $show_phone ): ?>


                    <button class="sl__btn sl__btn--phone" data-phone="<?php echo esc_attr($show_phone); ?>">


                        <?php echo $sl_lbl_phone_btn_text; ?>


                    </button>


                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$show_phone)); ?>"


                       class="sl__phone-reveal" id="sl-phone" style="display:none;">


                        <?php echo esc_html($show_phone); ?>


                    </a>


                <?php endif; ?>





                <?php if ( $email_show === '1' && $author ): ?>


                    <a href="<?php echo esc_url( function_exists( 'va_get_contact_page_url' ) ? va_get_contact_page_url() : home_url( '/kapcsolat/' ) ); ?>" class="sl__btn sl__btn--email">


                        &#9993; E-mail &#252;zenet k&#252;ld&#233;se


                    </a>


                <?php endif; ?>





                <?php if ( is_user_logged_in() ):


                    $watching = va_user_watches($post_id); ?>


                    <button class="sl__btn sl__btn--watch<?php echo $watching?' active':''; ?>"


                            data-post-id="<?php echo esc_attr($post_id); ?>">


                        <?php echo $watching ? '&#9733; Kedvencekb&#337;l elt&#225;vol&#237;t&#225;s' : '&#9734; Ment&#233;s kedvencekbe'; ?>


                    </button>


                <?php endif; ?>





                <!-- Megosztas -->


                <?php


                $share_url   = rawurlencode( get_permalink($post_id) );


                $share_title = rawurlencode( get_the_title($post_id) );


                ?>


                <div class="sl__share">


                    <span class="sl__share-label">Megoszt&#225;s:</span>


                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--fb" aria-label="Facebook">


                        <?php echo function_exists('va_social_svg') ? va_social_svg('facebook',18) : ''; ?>


                    </a>


                    <a href="https://wa.me/?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--wa" aria-label="WhatsApp">


                        <?php echo function_exists('va_social_svg') ? va_social_svg('whatsapp',18) : ''; ?>


                    </a>


                    <a href="https://t.me/share/url?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tg" aria-label="Telegram">


                        <?php echo function_exists('va_social_svg') ? va_social_svg('telegram',18) : ''; ?>


                    </a>


                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tw" aria-label="X/Twitter">


                        <?php echo function_exists('va_social_svg') ? va_social_svg('twitter',18) : ''; ?>


                    </a>


                    <button class="sl__share-btn sl__share-btn--copy" id="sl-copy-link" aria-label="Link m&#225;sol&#225;sa" data-url="<?php echo esc_attr(get_permalink($post_id)); ?>">


                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>


                    </button>


                </div>


            </div>





            <!-- Feladó további hirdetései -->


            <?php if ( $author ):


                $other = new WP_Query([


                    'post_type'      => 'va_listing',


                    'post_status'    => 'publish',


                    'author'         => $author->ID,


                    'posts_per_page' => 100,


                    'post__not_in'   => [$post_id],


                    'no_found_rows'  => true,


                ]);


                if ( $other->have_posts() ):


            ?>


            <div class="sl__card sl__more">


                <div class="sl__card-title">Tov&#225;bbi hirdet&#233;seink</div>


                <div class="sl__more-list">


                <?php while ( $other->have_posts() ): $other->the_post();


                    $p_id    = get_the_ID();


                    $p_price = get_post_meta($p_id,'va_price',true);


                    $p_type  = get_post_meta($p_id,'va_price_type',true);


                ?>


                    <a href="<?php the_permalink(); ?>" class="sl__more-item">


                        <?php if ( has_post_thumbnail() ):


                            echo get_the_post_thumbnail(null,[54,54],['class'=>'sl__more-img']);


                        else: ?>


                            <div class="sl__more-img sl__more-img--empty">&#128247;</div>


                        <?php endif; ?>


                        <div class="sl__more-info">


                            <div class="sl__more-title"><?php the_title(); ?></div>


                            <div class="sl__more-price"><?php echo esc_html(va_format_price($p_price,$p_type)); ?></div>


                        </div>


                    </a>


                <?php endwhile; wp_reset_postdata(); ?>


                </div>


            </div>


            <?php endif; endif; ?>





        </div><!-- .sl__right -->


    </div><!-- .sl__layout -->


</div><!-- .sl -->





<!-- Hasonló hirdetések -->


<?php


$related = new WP_Query([


    'post_type'      => 'va_listing',


    'post_status'    => 'publish',


    'posts_per_page' => 6,


    'post__not_in'   => [$post_id],


    'no_found_rows'  => true,


    'tax_query'      => $categories && !is_wp_error($categories) ? [[


        'taxonomy' => 'va_category',


        'field'    => 'term_id',


        'terms'    => wp_list_pluck($categories,'term_id'),


    ]] : [],


]);


if ( $related->have_posts() ):
?>
<div class="sl__related-wrap" style="max-width:<?php echo esc_attr((string)$sl_content_max); ?>px;margin:28px auto 0;padding:0 16px;">
    <div class="sl__card" style="margin-bottom:0;">
        <div class="sl__card-title" style="margin-bottom:14px;">Hasonl&#243; hirdet&#233;sek</div>
        <div class="va-grid">
            <?php while ( $related->have_posts() ): $related->the_post();
                va_template( 'listing/card', [ 'post' => get_post() ] );
            endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$seo_search_page = get_page_by_path( 'va-hirdetes-kereses' );
$seo_search_url  = $seo_search_page ? get_permalink( $seo_search_page ) : home_url( '/va-hirdetes-kereses/' );
$seo_internal_links = [];

if ( ! empty( $brand ) ) {
    $seo_internal_links[] = [
        'label' => $brand . ' ajánlatok',
        'url'   => add_query_arg( 'brand', $brand, $seo_search_url ),
    ];
}

if ( ! empty( $brand ) && ! empty( $model ) ) {
    $seo_internal_links[] = [
        'label' => $brand . ' ' . $model . ' ajánlatok',
        'url'   => add_query_arg( [ 'brand' => $brand, 'model' => $model ], $seo_search_url ),
    ];
}

if ( ! empty( $fuel_type ) ) {
    $seo_internal_links[] = [
        'label' => $fuel_type . ' járművek',
        'url'   => add_query_arg( 'fuel_type', $fuel_type, $seo_search_url ),
    ];
}

if ( ! empty( $location ) ) {
    $seo_internal_links[] = [
        'label' => $location . ' környéki ajánlatok',
        'url'   => add_query_arg( 's', $location, $seo_search_url ),
    ];
}

if ( ! empty( $seo_internal_links ) ):
?>
<div class="sl__related-wrap" style="max-width:<?php echo esc_attr((string)$sl_content_max); ?>px;margin:18px auto 0;padding:0 16px;">
    <div class="sl__card" style="margin-bottom:0;">
        <div class="sl__card-title" style="margin-bottom:14px;">Kapcsolódó keresések</div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php foreach ( $seo_internal_links as $seo_link ): ?>
                <a href="<?php echo esc_url( (string) $seo_link['url'] ); ?>" style="display:inline-flex;align-items:center;padding:9px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:#fff;text-decoration:none;">
                    <?php echo esc_html( (string) $seo_link['label'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>





<!-- Sticky bottom bar -->


<?php


$show_phone_sticky = $phone ?: ($author ? get_user_meta($author->ID,'va_phone',true) : '');


$watching_sticky   = is_user_logged_in() ? va_user_watches($post_id) : false;


?>


<div class="sl__sticky-bar" id="sl-sticky-bar">


    <div class="sl__sticky-title"><?php the_title(); ?></div>


    <div class="sl__sticky-price"><?php echo esc_html(va_format_price($price,$price_type)); ?></div>


    <?php if ($show_phone_sticky): ?>


    <button class="sl__sticky-btn sl__sticky-btn--phone" id="sl-sticky-phone" data-phone="<?php echo esc_attr($show_phone_sticky); ?>">


        Telefon


    </button>


    <?php endif; ?>


    <?php if (is_user_logged_in()): ?>


    <button class="sl__sticky-btn sl__sticky-btn--watch<?php echo $watching_sticky?' active':''; ?>"


            id="sl-sticky-watch" data-post-id="<?php echo esc_attr($post_id); ?>">


        <?php echo $watching_sticky ? '&#9733; Elmentve' : '&#9734; Kedvencekbe'; ?>


    </button>


    <?php endif; ?>


</div>





<?php if ( ! empty( $attachment_ids ) ): ?>


<div class="sl-viewer" id="sl-viewer" hidden>


    <button type="button" class="sl-viewer__close" id="sl-viewer-close" aria-label="Bezárás">


        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>


    </button>


    <div class="sl-viewer__toolbar">


        <button type="button" class="sl-viewer__btn" id="sl-zoom-out" aria-label="Kicsinyítés">-</button>


        <button type="button" class="sl-viewer__btn" id="sl-zoom-reset" aria-label="Méret visszaállítása">100%</button>


        <button type="button" class="sl-viewer__btn" id="sl-zoom-in" aria-label="Nagyítás">+</button>


    </div>


    <button type="button" class="sl-viewer__nav sl-viewer__nav--prev" id="sl-viewer-prev" aria-label="Előző kép">


        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>


    </button>


    <button type="button" class="sl-viewer__nav sl-viewer__nav--next" id="sl-viewer-next" aria-label="Következő kép">


        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>


    </button>


    <div class="sl-viewer__stage" id="sl-viewer-stage">


        <img src="<?php echo esc_url( $main_url ); ?>" alt="<?php the_title_attribute(); ?>" class="sl-viewer__img" id="sl-viewer-img">


    </div>


</div>


<?php endif; ?>





<script>


(function(){


    var thumbs = Array.prototype.slice.call(document.querySelectorAll('.sl__thumb'));


    var mainImg = document.getElementById('sl-main-img');





    function syncMainImage(src) {


        if (!mainImg || !src) return;


        mainImg.src = src;


    }






    // Galeria


    thumbs.forEach(function(t){


        t.addEventListener('click',function(){


            syncMainImage(this.dataset.src);


            thumbs.forEach(function(x){ x.classList.remove('sl__thumb--active'); });


            this.classList.add('sl__thumb--active');


        });


    });





    // Profi kepnezegeto (zoom + drag)


    var viewer = document.getElementById('sl-viewer');


    var viewerImg = document.getElementById('sl-viewer-img');


    var stage = document.getElementById('sl-viewer-stage');


    var prevBtn = document.getElementById('sl-viewer-prev');


    var nextBtn = document.getElementById('sl-viewer-next');


    var zoomTrigger = document.getElementById('sl-zoom-trigger');


    var zoomIn = document.getElementById('sl-zoom-in');


    var zoomOut = document.getElementById('sl-zoom-out');


    var zoomReset = document.getElementById('sl-zoom-reset');


    var closeBtn = document.getElementById('sl-viewer-close');


    var imageSources = thumbs.map(function(t){ return t.dataset.src; }).filter(Boolean);


    if (!imageSources.length && mainImg && mainImg.src) {


        imageSources = [mainImg.src];


    }


    var currentIndex = 0;


    var scale = 1;


    var tx = 0;


    var ty = 0;


    var dragging = false;


    var sx = 0;


    var sy = 0;


    var touchStartX = 0;


    var touchStartY = 0;


    var touchMoved = false;





    function applyTransform() {


        if (!viewerImg) return;


        viewerImg.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + scale + ')';


    }





    function setScale(nextScale) {


        scale = Math.max(1, Math.min(4, nextScale));


        if (scale === 1) { tx = 0; ty = 0; }


        applyTransform();


        if (zoomReset) zoomReset.textContent = Math.round(scale * 100) + '%';


    }





    function setNavState() {


        var many = imageSources.length > 1;


        if (prevBtn) prevBtn.style.display = many ? 'inline-flex' : 'none';


        if (nextBtn) nextBtn.style.display = many ? 'inline-flex' : 'none';


    }





    function setActiveThumb(index) {


        if (!thumbs.length) return;


        thumbs.forEach(function(x){ x.classList.remove('sl__thumb--active'); });


        if (thumbs[index]) {


            thumbs[index].classList.add('sl__thumb--active');


        }


    }





    function showImage(index, syncMain) {


        if (!imageSources.length || !viewerImg) return;


        currentIndex = (index + imageSources.length) % imageSources.length;


        viewerImg.src = imageSources[currentIndex];


        setScale(1);


        if (syncMain !== false) {


            syncMainImage(imageSources[currentIndex]);


            setActiveThumb(currentIndex);


        }


    }





    function openViewer() {


        if (!viewer || !viewerImg || !mainImg || !imageSources.length) return;


        var activeThumb = document.querySelector('.sl__thumb.sl__thumb--active');


        if (activeThumb) {


            currentIndex = Math.max(0, imageSources.indexOf(activeThumb.dataset.src));


        } else {


            currentIndex = Math.max(0, imageSources.indexOf(mainImg.src));


        }


        showImage(currentIndex, false);


        setNavState();


        viewer.hidden = false;


        document.body.classList.add('sl-viewer-open');


    }





    function closeViewer() {


        if (!viewer) return;


        viewer.hidden = true;


        document.body.classList.remove('sl-viewer-open');


    }





    if (zoomTrigger) {


        zoomTrigger.addEventListener('click', openViewer);


    }


    if (mainImg) {


        mainImg.addEventListener('click', openViewer);


    }





    if (closeBtn) closeBtn.addEventListener('click', closeViewer);


    if (viewer) {


        viewer.addEventListener('click', function(e){


            if (e.target === viewer) closeViewer();


        });


    }


    document.addEventListener('keydown', function(e){


        if (!viewer || viewer.hidden) return;


        if (e.key === 'Escape') closeViewer();


        if (e.key === 'ArrowLeft') showImage(currentIndex - 1, true);


        if (e.key === 'ArrowRight') showImage(currentIndex + 1, true);


    });





    if (prevBtn) prevBtn.addEventListener('click', function(){ showImage(currentIndex - 1, true); });


    if (nextBtn) nextBtn.addEventListener('click', function(){ showImage(currentIndex + 1, true); });





    if (zoomIn) zoomIn.addEventListener('click', function(){ setScale(scale + 0.25); });


    if (zoomOut) zoomOut.addEventListener('click', function(){ setScale(scale - 0.25); });


    if (zoomReset) zoomReset.addEventListener('click', function(){ setScale(1); });





    if (stage) {


        stage.addEventListener('wheel', function(e){


            e.preventDefault();


            setScale(scale + (e.deltaY < 0 ? 0.2 : -0.2));


        }, { passive: false });





        stage.addEventListener('mousedown', function(e){


            if (scale <= 1) return;


            dragging = true;


            sx = e.clientX - tx;


            sy = e.clientY - ty;


            stage.classList.add('is-dragging');


        });





        document.addEventListener('mousemove', function(e){


            if (!dragging) return;


            tx = e.clientX - sx;


            ty = e.clientY - sy;


            applyTransform();


        });





        document.addEventListener('mouseup', function(){


            dragging = false;


            stage.classList.remove('is-dragging');


        });





        stage.addEventListener('touchstart', function(e){


            if (!e.touches || e.touches.length !== 1) return;


            var t = e.touches[0];


            if (scale > 1) {


                dragging = true;


                sx = t.clientX - tx;


                sy = t.clientY - ty;


                stage.classList.add('is-dragging');


                return;


            }


            touchStartX = t.clientX;


            touchStartY = t.clientY;


            touchMoved = false;


        }, { passive: true });





        stage.addEventListener('touchmove', function(e){


            if (!e.touches || e.touches.length !== 1) return;


            var t = e.touches[0];


            if (scale > 1 && dragging) {


                tx = t.clientX - sx;


                ty = t.clientY - sy;


                applyTransform();


                e.preventDefault();


                return;


            }


            if (!touchStartX && !touchStartY) return;


            if (Math.abs(t.clientX - touchStartX) > 10 || Math.abs(t.clientY - touchStartY) > 10) {


                touchMoved = true;


            }


        }, { passive: false });





        stage.addEventListener('touchend', function(e){


            if (scale > 1) {


                dragging = false;


                stage.classList.remove('is-dragging');


                return;


            }


            if (!touchMoved) {


                touchStartX = 0;


                touchStartY = 0;


                return;


            }


            var t = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0] : null;


            if (!t) return;


            var dx = t.clientX - touchStartX;


            var dy = t.clientY - touchStartY;


            if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) {


                if (dx < 0) {


                    showImage(currentIndex + 1, true);


                } else {


                    showImage(currentIndex - 1, true);


                }


            }


            touchStartX = 0;


            touchStartY = 0;


            touchMoved = false;


        }, { passive: true });


    }





    // Telefonszam


    var pb = document.querySelector('.sl__btn--phone');


    if(pb) pb.addEventListener('click',function(){


        var el = document.getElementById('sl-phone');


        if(el){ el.style.display='flex'; this.style.display='none'; }


    });


    // Watchlist


    document.querySelectorAll('.sl__btn--watch').forEach(function(btn){


        btn.addEventListener('click',function(){


            var b = this;


            fetch(VA_Data.ajax_url,{method:'POST',


                headers:{'Content-Type':'application/x-www-form-urlencoded'},


                body:'action=va_toggle_watchlist&nonce='+VA_Data.nonce+'&post_id='+VA_Data.post_id


            }).then(function(r){return r.json();}).then(function(d){


                if(d.success){


                    b.classList.toggle('active');


                    b.textContent = b.classList.contains('active')


                        ? 'Kedvencekb\u0151l elt\u00e1vol\u00edt\u00e1s'


                        : 'Ment\u00e9s kedvencekbe';


                }


            });


        });


    });


    // Link másolása


    var copyBtn = document.getElementById('sl-copy-link');


    if (copyBtn) {


        copyBtn.addEventListener('click', function(e){


            e.preventDefault();


            var url = this.dataset.url;


            function markCopied() {


                copyBtn.classList.add('sl__share-btn--copied');


                setTimeout(function(){ copyBtn.classList.remove('sl__share-btn--copied'); }, 2000);


                if (typeof window.va_toast === 'function') {


                    window.va_toast('Link kimásolva.', 'success');


                }


            }





            function markFailed() {


                if (typeof window.va_toast === 'function') {


                    window.va_toast('A másolás nem sikerült.', 'error');


                }


            }





            if (navigator.clipboard && window.isSecureContext) {


                navigator.clipboard.writeText(url).then(markCopied).catch(markFailed);


                return;


            }





            try {


                var temp = document.createElement('textarea');


                temp.value = url;


                temp.setAttribute('readonly', 'readonly');


                temp.style.position = 'fixed';


                temp.style.opacity = '0';


                document.body.appendChild(temp);


                temp.select();


                temp.setSelectionRange(0, temp.value.length);


                var ok = document.execCommand('copy');


                document.body.removeChild(temp);


                if (ok) { markCopied(); } else { markFailed(); }


            } catch (err) {


                markFailed();


            }


        });


    }


})();


</script>





<?php get_footer(); ?>


