<?php
/**
 * Template: Felhasználói dashboard (fiókom oldal)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'va_user_nonce' ),
    'post_id'  => 0,
]);

$user     = wp_get_current_user();
$auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;
$listings = va_get_user_listings( $user->ID );
$watchlist= va_get_user_watchlist( $user->ID );
$bids     = $auctions_enabled ? va_get_user_bids( $user->ID ) : [];
$phone    = get_user_meta( $user->ID, 'va_phone', true );
$submit_page = get_page_by_path( 'va-hirdetes-feladas' );

// Plan adatok
$user_plan    = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user->ID ) : 'basic';
$plan_configs = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::PLANS : [];
$plan_cfg     = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_config( $user_plan, $user->ID ) : [];
$plan_check   = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::can_post_listing( $user->ID ) : [ 'used' => 0, 'limit' => 0 ];
$boost_nonce  = wp_create_nonce( 'va_user_nonce' );
$ajax_url     = admin_url( 'admin-ajax.php' );
$seller_label = get_user_meta( $user->ID, 'va_seller_label', true );
$avatar_id    = (int) get_user_meta( $user->ID, 'va_profile_avatar_id', true );
$avatar_url   = $avatar_id ? wp_get_attachment_image_url( $avatar_id, 'thumbnail' ) : '';

$va_today_local       = current_time( 'Y-m-d' );
$va_last_welcome_seen = (string) get_user_meta( $user->ID, 'va_daily_welcome_seen', true );
$va_show_daily_welcome = current_user_can( 'manage_options' ) && $va_last_welcome_seen !== $va_today_local;
if ( $va_show_daily_welcome ) {
    update_user_meta( $user->ID, 'va_daily_welcome_seen', $va_today_local );
}

$va_daily_welcome_message = '';

$va_fixed_welcome_filename = 'Névtelen.png';
$va_fixed_welcome_path = WP_CONTENT_DIR . '/' . $va_fixed_welcome_filename;
$va_fixed_welcome_url = site_url( '/wp-content/' . rawurlencode( $va_fixed_welcome_filename ) );

$va_welcome_svg_fallback = 'data:image/svg+xml;utf8,' . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280 560">'
    . '<defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#2a0909"/><stop offset="100%" stop-color="#070707"/></linearGradient></defs>'
    . '<rect width="1280" height="560" fill="url(#bg)"/>'
    . '<g fill="#f6d9af" stroke="#d8b487" stroke-width="6" opacity=".95">'
    . '<path d="M170 290c70-56 156-62 238 12-70 24-120 72-150 142-58-42-88-88-88-154z"/>'
    . '<path d="M470 210c64-50 144-54 214 14-62 20-108 62-136 124-52-38-78-78-78-138z"/>'
    . '<path d="M760 300c70-56 156-62 238 12-70 24-120 72-150 142-58-42-88-88-88-154z"/>'
    . '<path d="M970 190c58-46 128-50 190 12-54 18-92 52-116 104-44-32-66-66-74-116z"/>'
    . '</g></svg>'
);

$va_welcome_image_url = '';

if ( file_exists( $va_fixed_welcome_path ) ) {
    $va_welcome_image_url = $va_fixed_welcome_url;
}

if ( '' === $va_welcome_image_url ) {
    $va_welcome_image_url = $va_welcome_svg_fallback;
}

$va_welcome_name = trim( (string) $user->display_name );
if ( '' === $va_welcome_name ) {
    $va_welcome_name = 'Vadász';
}
if ( strtolower( (string) $user->user_login ) === 'weingartnerauto' || strtolower( $va_welcome_name ) === 'weingartnerauto' ) {
    // Kifejezetten ehhez a fiokhoz kert nevfeluliras.
    $va_welcome_name = 'Adri';
}

if ( $va_show_daily_welcome ) {
    $va_wish_openers = [
        'Ma tisztán látod az utad',
        'A mai nap neked dolgozik',
        'Csendben erősödsz',
        'A szerencse most figyel rád',
        'Ma könnyebb lesz dönteni',
        'A jó időzítés melletted áll',
        'Ma jól olvasod a jeleket',
        'Most nyitott az út a jó alkalmakra',
        'A kitartásod ma gyorsabban térül meg',
        'Ma tiszta fejjel lépsz előre',
        'A bátorságod ma jutalmat kap',
        'A mai nap egy új ritmust ad',
        'Most minden kicsit a helyére pattan',
        'A figyelmed ma aranyat ér',
        'A mai lépésed holnapot építi',
        'Ma megérkezik egy jó visszajelzés',
        'A higgadtságod ma előny lesz',
        'Most jó helyen vagy jó időben',
        'A mai napban több van, mint látszik',
        'Ma könnyebben kapcsolódnak össze a dolgok',
    ];
    $va_wish_focuses = [
        'a figyelmed minden fontos részletet megtalál',
        'a jó emberek ma könnyebben találnak rád',
        'a jó döntés ma kevesebb kétellyel jön',
        'a nyugalmad ma erős pajzs lesz',
        'a kitartásod ma tiszta eredményt hoz',
        'a lendületed ma végig kitart',
        'az ötleteid ma jól hasznosulnak',
        'egy régi terv ma új erőre kap',
        'a munkád ma látható nyomot hagy',
        'a jó hír ma gyorsabban érkezik',
        'a kapcsolataid ma támogatóbbak lesznek',
        'a megérzésed ma pontos iránytű',
        'a körülmények ma inkább segítenek, mint akadályoznak',
        'a mai beszélgetések hasznos fordulatot adnak',
        'a türelmed ma gyorsabb haladást szül',
        'a figyelmed ma pénzt és időt spórol',
        'a jó ritmus ma végig veled marad',
        'a mai kezdet stabil folytatást hoz',
        'a választásod ma hosszabb távon is nyereséges',
        'a régi bizonytalanság ma elcsendesedik',
    ];
    $va_wish_outcomes = [
        'estére elégedett mosollyal zárod a napot',
        'egy jó hír megerősíti a döntéseidet',
        'több tiszta igen érkezik, mint nemet',
        'pont annyi segítséget kapsz, amennyi kell',
        'a mai eredmény holnap is tartani fog',
        'a kis lépéseid nagy távolságot adnak',
        'a jó lehetőség időben kopogtat',
        'nyugodt erősség marad benned estig',
        'egy váratlan jó fordulat mosolyt hoz',
        'minden lényeges válasz időben megérkezik',
        'a mai munka megmutatja az értelmet',
        'jó emberek állnak melletted a fontos pillanatban',
        'a mai nyereséged később is látszani fog',
        'könnyebb lesz továbblépni, mint gondoltad',
        'a bizonytalanság helyett tiszta terv marad',
        'a nap végére büszke leszel magadra',
        'a jó ritmus holnap is folytatódik',
        'a siker csendben, de biztosan megérkezik',
        'a mai döntésed erős alapot ad',
        'az estéd nyugodt és megelégedett lesz',
    ];

    $va_wishes = [];
    foreach ( $va_wish_openers as $va_open ) {
        foreach ( $va_wish_focuses as $va_focus ) {
            foreach ( $va_wish_outcomes as $va_outcome ) {
                $va_wishes[] = $va_open . ', {nev}! Ma ' . $va_focus . ', és ' . $va_outcome . '.';
                if ( count( $va_wishes ) >= 1000 ) {
                    break 3;
                }
            }
        }
    }

    $va_client_ip = '';
    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $va_forwarded = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
        $va_client_ip = trim( (string) $va_forwarded[0] );
    }
    if ( '' === $va_client_ip && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $va_client_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }
    if ( '' === $va_client_ip ) {
        $va_client_ip = 'unknown';
    }

    $va_ip_hash = md5( $va_client_ip );
    $va_seen_key = 'va_welcome_seen_' . $va_ip_hash;
    $va_seen_ids = get_transient( $va_seen_key );
    if ( ! is_array( $va_seen_ids ) ) {
        $va_seen_ids = [];
    }
    $va_seen_ids = array_values( array_unique( array_filter( array_map( 'intval', $va_seen_ids ), static function( $id ) {
        return $id >= 0 && $id < 1000;
    } ) ) );

    $va_available_ids = array_values( array_diff( range( 0, 999 ), $va_seen_ids ) );
    if ( empty( $va_available_ids ) ) {
        $va_seen_ids = [];
        $va_available_ids = range( 0, 999 );
    }

    $va_pick_id = $va_available_ids[ wp_rand( 0, count( $va_available_ids ) - 1 ) ];
    $va_seen_ids[] = (int) $va_pick_id;
    set_transient( $va_seen_key, $va_seen_ids, YEAR_IN_SECONDS );

    $va_daily_welcome_message = str_replace( '{nev}', $va_welcome_name, $va_wishes[ $va_pick_id ] );
}

// CRM statisztika adatelokeszites (valid, adminnal egyezo nezetseggel)
$crm_now_ts         = current_time( 'timestamp' );
$crm_total_listings = count( $listings );
$crm_total_views    = 0;
$crm_total_price    = 0.0;
$crm_priced_count   = 0;
$crm_active_count   = 0;
$crm_featured_count = 0;
$crm_boosted_count  = 0;
$crm_highlight_ids  = [];
$crm_newpill_count  = 0;
$crm_last7_count    = 0;
$crm_last30_count   = 0;

$crm_status_counts = [
    'Aktiv'                  => 0,
    'Jovahagyasra var'       => 0,
    'Piszkozat'              => 0,
    'Privat/Szunet'          => 0,
    'Limit miatt leallitva'  => 0,
];

$crm_rows = [];
foreach ( $listings as $l ) {
    $valid_views = (int) get_post_meta( $l->ID, 'va_views', true );
    $price_raw   = get_post_meta( $l->ID, 'va_price', true );
    $price_type  = (string) get_post_meta( $l->ID, 'va_price_type', true );
    $is_featured = get_post_meta( $l->ID, 'va_featured', true ) === '1';
    $is_suspended = get_post_meta( $l->ID, 'va_is_suspended', true ) === '1';
    $suspended_by_plan = get_post_meta( $l->ID, 'va_suspended_by_plan', true ) === '1';

    $created_ts = strtotime( $l->post_date );
    if ( $created_ts >= ( $crm_now_ts - ( 7 * DAY_IN_SECONDS ) ) ) {
        $crm_last7_count++;
    }
    if ( $created_ts >= ( $crm_now_ts - ( 30 * DAY_IN_SECONDS ) ) ) {
        $crm_last30_count++;
    }

    $crm_total_views += $valid_views;

    if ( $is_featured ) {
        $crm_featured_count++;
        $crm_highlight_ids[ (int) $l->ID ] = 1;
    }
    $is_boosted = class_exists( 'VA_User_Roles' ) && VA_User_Roles::is_boosted( $l->ID );
    if ( $is_boosted ) {
        $crm_boosted_count++;
        $crm_highlight_ids[ (int) $l->ID ] = 1;
    }
    if ( class_exists( 'VA_User_Roles' ) && VA_User_Roles::is_new_pill( $l->ID ) ) {
        $crm_newpill_count++;
    }

    if ( is_numeric( $price_raw ) && $price_type !== 'ask' ) {
        $crm_total_price += (float) $price_raw;
        $crm_priced_count++;
    }

    if ( $l->post_status === 'publish' ) {
        $crm_active_count++;
        $crm_status_counts['Aktiv']++;
    } elseif ( $l->post_status === 'pending' ) {
        $crm_status_counts['Jovahagyasra var']++;
    } elseif ( $l->post_status === 'draft' ) {
        $crm_status_counts['Piszkozat']++;
    } elseif ( $suspended_by_plan ) {
        $crm_status_counts['Limit miatt leallitva']++;
    } else {
        $crm_status_counts['Privat/Szunet']++;
    }

    $crm_rows[] = [
        'id'      => (int) $l->ID,
        'title'   => (string) $l->post_title,
        'url'     => get_permalink( $l->ID ),
        'views'   => $valid_views,
        'date_ts' => $created_ts,
        'status'  => (string) $l->post_status,
    ];
}

usort( $crm_rows, static function( array $a, array $b ): int {
    return $b['views'] <=> $a['views'];
} );

$crm_top_rows      = array_slice( $crm_rows, 0, 5 );
$crm_highlight_count = count( $crm_highlight_ids );
$crm_avg_views     = $crm_total_listings > 0 ? ( $crm_total_views / $crm_total_listings ) : 0;
$crm_avg_price     = $crm_priced_count > 0 ? ( $crm_total_price / $crm_priced_count ) : 0;
$crm_watch_count   = count( $watchlist );
$crm_bid_count     = count( $bids );
$crm_plan_limit    = (int) ( $plan_check['limit'] ?? 0 );
$crm_plan_used     = (int) ( $plan_check['used'] ?? 0 );
$crm_plan_usage_pc = $crm_plan_limit > 0 ? min( 100, (int) round( ( $crm_plan_used / $crm_plan_limit ) * 100 ) ) : 0;
$crm_active_rate_pc = $crm_total_listings > 0 ? min( 100, (int) round( ( $crm_active_count / $crm_total_listings ) * 100 ) ) : 0;
$crm_momentum_pc    = $crm_last30_count > 0 ? min( 100, (int) round( ( $crm_last7_count / $crm_last30_count ) * 100 ) ) : 0;
$crm_engage_raw     = $crm_total_listings > 0 ? ( ( $crm_watch_count + $crm_bid_count ) / $crm_total_listings ) * 16 : 0;
$crm_engagement_pc  = min( 100, (int) round( $crm_engage_raw ) );

$crm_flow_raw = [
    max( 1, $crm_last7_count ),
    max( 1, (int) round( $crm_last30_count / 4 ) ),
    max( 1, (int) round( $crm_total_views / max( 1, $crm_total_listings ) ) ),
    max( 1, $crm_active_count ),
    max( 1, $crm_watch_count ),
    max( 1, $crm_bid_count ),
    max( 1, $crm_highlight_count ),
];
$crm_flow_max = max( $crm_flow_raw );
$crm_flow_scaled = array_map(
    static function( $v ) use ( $crm_flow_max ) {
        return (int) max( 18, round( ( $v / max( 1, $crm_flow_max ) ) * 100 ) );
    },
    $crm_flow_raw
);
$crm_chart_labels = [ 'H', 'K', 'Sze', 'Cs', 'P', 'Szo', 'V' ];
$crm_chart_line_points = [];
$crm_chart_area_points = [];
$crm_chart_dots = [];
foreach ( $crm_flow_scaled as $idx => $val ) {
    $x = 18 + ( $idx * 44 );
    $y = 112 - ( ( $val / 100 ) * 76 );
    $crm_chart_line_points[] = round( $x, 1 ) . ',' . round( $y, 1 );
    $crm_chart_area_points[] = round( $x, 1 ) . ' ' . round( $y, 1 );
    $crm_chart_dots[] = [
        'x' => round( $x, 1 ),
        'y' => round( $y, 1 ),
    ];
}
$crm_chart_polyline = implode( ' ', $crm_chart_line_points );
$crm_chart_area     = 'M 18 112 L ' . implode( ' L ', $crm_chart_area_points ) . ' L 282 112 Z';

$crm_top_title = $crm_top_rows ? (string) $crm_top_rows[0]['title'] : 'Nincs adat';
$crm_top_views = $crm_top_rows ? (int) $crm_top_rows[0]['views'] : 0;

// Rendezés
$sort_by  = sanitize_key( $_GET['sort_by'] ?? 'date' );
$sort_dir = sanitize_key( $_GET['sort_dir'] ?? 'desc' );
if ( ! in_array( $sort_by, [ 'date', 'views', 'price' ], true ) ) $sort_by = 'date';
if ( ! in_array( $sort_dir, [ 'asc', 'desc' ], true ) ) $sort_dir = 'desc';

// Képszámok (batch)
$listing_ids_all = array_column( (array) $listings, 'ID' );
$gallery_meta_map = [];
foreach ( $listing_ids_all as $lid ) {
    $g = get_post_meta( $lid, 'va_gallery_ids', true );
    $gallery_meta_map[ $lid ] = count( array_filter( array_map( 'intval', explode( ',', (string) $g ) ) ) );
}

// Akciós árak + lejáratok per listing
$sale_prices = [];
$sale_ends   = [];
$active_sinces = [];
foreach ( $listing_ids_all as $lid ) {
    $sp = get_post_meta( $lid, 'va_sale_price', true );
    $sale_prices[ $lid ] = $sp ? floatval( $sp ) : 0.0;
    $se = get_post_meta( $lid, 'va_sale_price_end', true );
    $sale_ends[ $lid ] = $se ?: '';
    $as = (int) get_post_meta( $lid, 'va_active_since', true );
    $active_sinces[ $lid ] = $as ?: 0;
}

// Sort listings
usort( $listings, function( $a, $b ) use ( $sort_by, $sort_dir, $sale_prices ) {
    if ( $sort_by === 'views' ) {
        $av = (int) get_post_meta( $a->ID, 'va_views', true );
        $bv = (int) get_post_meta( $b->ID, 'va_views', true );
        $cmp = $av <=> $bv;
    } elseif ( $sort_by === 'price' ) {
        $ap = floatval( get_post_meta( $a->ID, 'va_price', true ) );
        $bp = floatval( get_post_meta( $b->ID, 'va_price', true ) );
        $cmp = $ap <=> $bp;
    } else {
        $cmp = strtotime( $a->post_date ) <=> strtotime( $b->post_date );
    }
    return $sort_dir === 'asc' ? $cmp : -$cmp;
} );

// Profil-teljességi %
$completeness_items = [
    'Megjelenítési név' => ! empty( $user->display_name ),
    'E-mail'            => ! empty( $user->user_email ),
    'Telefonszám'       => ! empty( $phone ),
    'Bemutatkozás'      => ! empty( $user->description ),
    'Profilkép'         => ! empty( $avatar_url ),
    'Aktív hirdetés'    => $crm_active_count > 0,
];
$completeness_done  = count( array_filter( $completeness_items ) );
$completeness_pct   = (int) round( $completeness_done / count( $completeness_items ) * 100 );

// Tagság kora
$membership_days = (int) floor( ( time() - strtotime( $user->user_registered ) ) / 86400 );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <div class="va-dashboard">

        <!-- Bal navigáció -->
        <nav class="va-dashboard__nav">
            <div class="va-dash-user-head">
                <div class="va-dash-user-head__avatar">
                    <?php if ( $avatar_url ): ?>
                        <img src="<?php echo esc_url( $avatar_url ); ?>" alt="Profilkép">
                    <?php else: ?>
                        <?php echo esc_html( strtoupper( mb_substr( $user->display_name ?: $user->user_login, 0, 1 ) ) ); ?>
                    <?php endif; ?>
                </div>
                <div class="va-dash-user-head__name"><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></div>
            </div>
            <form method="post" enctype="multipart/form-data" class="va-dash-avatar-form" id="va-avatar-form">
                <?php wp_nonce_field( 'va_profile_avatar', 'va_profile_avatar_nonce' ); ?>
                <input type="hidden" name="va_action" value="profile_avatar">
                <input type="file" name="profile_avatar" id="va-avatar-input" class="va-dash-avatar-form__file" accept="image/jpeg,image/png,image/webp">
                <label for="va-avatar-input" class="va-dash-avatar-drop" id="va-avatar-drop">
                    <span class="va-dash-avatar-drop__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.5"/><path d="M20.8 13a9 9 0 1 1-1.4-4.8"/><path d="M17 3l3 3-3 3"/><line x1="20" y1="6" x2="14" y2="6"/></svg>
                    </span>
                    <span class="va-dash-avatar-drop__text" id="va-avatar-label">Kép kiválasztása</span>
                    <span class="va-dash-avatar-drop__hint">JPG, PNG, WEBP</span>
                </label>
                <?php if ( $avatar_url ): ?>
                <label class="va-dash-avatar-form__remove">
                    <input type="checkbox" name="profile_avatar_remove" value="1"> Profilkép törlése
                </label>
                <?php endif; ?>
                <button type="submit" class="va-dash-avatar-form__btn">Mentés</button>
            </form>
            <span class="va-dashboard__nav-item active" data-tab="listings"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg class="va-ico va-ico--list" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 8h8M8 12h8M8 16h6"/></svg></span><span class="va-dashboard__nav-label">Hirdetéseim (<?php echo count( $listings ); ?>)</span></span>
            <?php if ( $auctions_enabled ): ?>
            <span class="va-dashboard__nav-item" data-tab="bids"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg class="va-ico va-ico--hammer" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M14 5l5 5"/><path d="M11 8l5 5"/><path d="M3 21l8-8"/><path d="M10 4l6 6"/></svg></span><span class="va-dashboard__nav-label">Licitjeim (<?php echo count( $bids ); ?>)</span></span>
            <?php endif; ?>
            <span class="va-dashboard__nav-item" data-tab="watchlist"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg class="va-ico va-ico--heart" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20s-7-4.6-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.4-7 10-7 10z"/></svg></span><span class="va-dashboard__nav-label">Kedvenceim (<?php echo count( $watchlist ); ?>)</span></span>
            <span class="va-dashboard__nav-item" data-tab="profile"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg class="va-ico va-ico--user" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.6 3-6 7-6s7 2.4 7 6"/></svg></span><span class="va-dashboard__nav-label">Profilom</span></span>
            <span class="va-dashboard__nav-item va-dashboard__nav-item--danger" data-tab="deleteaccount"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg></span><span class="va-dashboard__nav-label">Fiók törlése</span></span>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="va-dashboard__nav-item va-dashboard__nav-item--logout"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg class="va-ico va-ico--exit" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg></span><span class="va-dashboard__nav-label">Kijelentkezés</span></a>

            <?php if ( $plan_cfg ): ?>
            <div class="va-dash-plan-badge" style="--pc:<?php echo esc_attr( $plan_cfg['color'] ); ?>;--pb:<?php echo esc_attr( $plan_cfg['bg'] ); ?>">
                <span class="va-dash-plan-icon"><?php echo esc_html( $plan_cfg['icon'] ); ?></span>
                <span class="va-dash-plan-label"><?php echo esc_html( $plan_cfg['label'] ); ?> Csomag</span>
                <?php if ( $plan_check['limit'] > 0 ): ?>
                <div class="va-dash-plan-usage">
                    <?php
                    $pu = $plan_check['used'];
                    $pl = $plan_check['limit'];
                    $pp = min( 100, (int) round( $pu / $pl * 100 ) );
                    $pc = $pp >= 100 ? '#ff4444' : ( $pp >= 80 ? '#ffb400' : '#00c850' );
                    ?>
                    <span><?php echo esc_html( $pu . '/' . $pl ); ?> hird.</span>
                    <div class="va-dash-plan-bar"><div style="width:<?php echo esc_attr( $pp ); ?>%;background:<?php echo esc_attr( $pc ); ?>"></div></div>
                </div>
                <?php endif; ?>
                <small><svg class="va-ico va-ico--up" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 19V5"/><path d="M6 11l6-6 6 6"/></svg> <?php echo esc_html( $plan_cfg['boost_cooldown'] ); ?> naponként emelhető</small>
                <?php if ( $user_plan === 'platinum' ): ?>
                <form method="post" class="va-dash-plan-label-form">
                    <?php wp_nonce_field( 'va_profile_label', 'va_profile_label_nonce' ); ?>
                    <input type="hidden" name="va_action" value="profile_label">
                    <label class="va-dash-plan-label-form__label">Saját rang címke</label>
                    <input type="text"
                           name="profile_seller_label"
                           class="va-dash-plan-label-form__input"
                           value="<?php echo esc_attr( $seller_label ); ?>"
                           placeholder="pl. Kereskedő, Viszonteladó"
                           maxlength="40">
                    <button type="submit" class="va-dash-plan-label-form__btn">Mentés</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </nav>

        <!-- Tartalom -->
        <div class="va-dashboard__content">

            <!-- Tab: Hirdetéseim -->
            <div id="va-tab-listings" class="va-dashboard__section active">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                    <h2 class="va-dashboard__title">Hirdetéseim</h2>
                    <div class="va-dashboard__title-actions">
                        <?php if ( $submit_page ): ?>
                            <a href="<?php echo esc_url( get_permalink( $submit_page ) ); ?>" class="va-btn va-btn--primary va-btn--sm">+ Új hirdetés</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $can_see_stats = current_user_can( 'manage_options' ) || in_array( $user_plan, [ 'platinum', 'gold' ], true );
                if ( $can_see_stats ) :
                ?>
                <section class="va-crm" aria-label="Hirdetés statisztika">
                    <div class="va-crm__head">
                        <div>
                            <h3>Részletes bontás</h3>
                            <p>Státuszok, toplista, kiemelések</p>
                        </div>
                    </div>

                    <div class="va-crm__panels va-crm__panels--triple">
                        <article class="va-crm__panel">
                            <h4>Státusz megoszlás</h4>
                            <div class="va-crm__status-list">
                                <?php foreach ( $crm_status_counts as $label => $count ):
                                    $pc = $crm_total_listings > 0 ? ( $count / $crm_total_listings ) * 100 : 0;
                                ?>
                                <div class="va-crm__status-row">
                                    <div class="va-crm__status-meta">
                                        <span><?php echo esc_html( $label ); ?></span>
                                        <strong><?php echo esc_html( number_format( $count, 0, ',', ' ' ) ); ?></strong>
                                    </div>
                                    <div class="va-crm__status-bar"><span style="width:<?php echo esc_attr( max( 4, round( $pc, 1 ) ) ); ?>%"></span></div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ( $crm_plan_limit > 0 ): ?>
                            <div class="va-crm__plan-use">
                                <div class="va-crm__plan-use-meta">
                                    <span>Csomagkihasznaltsag</span>
                                    <strong><?php echo esc_html( $crm_plan_used . '/' . $crm_plan_limit ); ?></strong>
                                </div>
                                <div class="va-crm__status-bar"><span style="width:<?php echo esc_attr( max( 4, $crm_plan_usage_pc ) ); ?>%"></span></div>
                            </div>
                            <?php endif; ?>
                        </article>

                        <article class="va-crm__panel">
                            <h4>Top 5 hirdetés</h4>
                            <?php if ( $crm_top_rows ): ?>
                            <ol class="va-crm__top-list">
                                <?php foreach ( $crm_top_rows as $row ): ?>
                                <li>
                                    <a href="<?php echo esc_url( (string) $row['url'] ); ?>"><?php echo esc_html( (string) $row['title'] ); ?></a>
                                    <span><?php echo esc_html( number_format( (int) $row['views'], 0, ',', ' ' ) ); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ol>
                            <?php else: ?>
                            <p class="va-crm__empty">Még nincs elég adat a toplistához.</p>
                            <?php endif; ?>

                            <div class="va-crm__panel-note">A lista a valós megtekintések alapján rendezett.</div>
                        </article>

                        <article class="va-crm__panel">
                            <h4>Jelzők és kiemelések</h4>
                            <div class="va-crm__metric-list">
                                <div class="va-crm__metric-row"><span>Kiemelt hirdetések</span><strong><?php echo esc_html( number_format( $crm_highlight_count, 0, ',', ' ' ) ); ?></strong></div>
                                <div class="va-crm__metric-row"><span>Új címke aktív</span><strong><?php echo esc_html( number_format( $crm_newpill_count, 0, ',', ' ' ) ); ?></strong></div>
                                <div class="va-crm__metric-row"><span>Kedvencek</span><strong><?php echo esc_html( number_format( $crm_watch_count, 0, ',', ' ' ) ); ?></strong></div>
                                <div class="va-crm__metric-row"><span>Licit aktivitás</span><strong><?php echo esc_html( number_format( $crm_bid_count, 0, ',', ' ' ) ); ?></strong></div>
                            </div>

                            <div class="va-crm__badges">
                                <span>Kiemelt: <strong><?php echo esc_html( number_format( $crm_highlight_count, 0, ',', ' ' ) ); ?></strong></span>
                                <span>Új: <strong><?php echo esc_html( number_format( $crm_newpill_count, 0, ',', ' ' ) ); ?></strong></span>
                            </div>
                        </article>
                    </div>

                    <details class="va-crm__fold">
                        <summary class="va-crm__fold-summary">
                            <span>További statisztikák</span>
                            <small>Piaci mozgás, jelzések, alapadatok</small>
                        </summary>
                        <div class="va-crm__fold-content">
                            <div class="va-crm__hero">
                                <article class="va-crm__screen">
                                    <div class="va-crm__screen-top">
                                        <div>
                                            <div class="va-crm__eyebrow">Előrejelző nézet</div>
                                            <h4>Piaci mozgás</h4>
                                        </div>
                                        <div class="va-crm__switches" aria-hidden="true">
                                            <span class="is-active">7 nap</span>
                                            <span>30 nap</span>
                                            <span>Élő</span>
                                        </div>
                                    </div>

                                    <div class="va-crm__screen-stats">
                                        <div class="va-crm__screen-stat">
                                            <span>Megtekintés</span>
                                            <strong><?php echo esc_html( number_format( $crm_total_views, 0, ',', ' ' ) ); ?></strong>
                                        </div>
                                        <div class="va-crm__screen-stat">
                                            <span>Átlagár</span>
                                            <strong><?php echo esc_html( number_format( $crm_avg_price, 0, ',', ' ' ) ); ?> Ft</strong>
                                        </div>
                                        <div class="va-crm__screen-stat">
                                            <span>Top címke</span>
                                            <strong><?php echo esc_html( number_format( $crm_highlight_count, 0, ',', ' ' ) ); ?></strong>
                                        </div>
                                    </div>

                                    <div class="va-crm__chart-shell">
                                        <svg viewBox="0 0 320 130" class="va-crm__linechart" role="img" aria-label="Aktivitási trend grafikon">
                                            <defs>
                                                <linearGradient id="vaChartAreaGradient2" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#77dbff" stop-opacity="0.34"/>
                                                    <stop offset="100%" stop-color="#77dbff" stop-opacity="0"/>
                                                </linearGradient>
                                                <linearGradient id="vaChartLineGradient2" x1="0" y1="0" x2="1" y2="0">
                                                    <stop offset="0%" stop-color="#6edbff"/>
                                                    <stop offset="55%" stop-color="#70f1cf"/>
                                                    <stop offset="100%" stop-color="#ffd47a"/>
                                                </linearGradient>
                                            </defs>
                                            <g class="va-crm__gridlines" aria-hidden="true">
                                                <line x1="18" y1="24" x2="282" y2="24" />
                                                <line x1="18" y1="52" x2="282" y2="52" />
                                                <line x1="18" y1="80" x2="282" y2="80" />
                                                <line x1="18" y1="108" x2="282" y2="108" />
                                            </g>
                                            <path class="va-crm__line-area" d="<?php echo esc_attr( $crm_chart_area ); ?>" fill="url(#vaChartAreaGradient2)"></path>
                                            <polyline class="va-crm__line-stroke" points="<?php echo esc_attr( $crm_chart_polyline ); ?>" fill="none" style="stroke:url(#vaChartLineGradient2);"></polyline>
                                            <g class="va-crm__line-points" fill="#fff">
                                                <?php foreach ( $crm_chart_dots as $dot ) : ?>
                                                <circle cx="<?php echo esc_attr( (string) $dot['x'] ); ?>" cy="<?php echo esc_attr( (string) $dot['y'] ); ?>" r="3"></circle>
                                                <?php endforeach; ?>
                                            </g>
                                        </svg>
                                    </div>

                                    <div class="va-crm__chart-foot">
                                        <div class="va-crm__flow-bars" aria-hidden="true">
                                        <?php foreach ( $crm_flow_scaled as $idx => $h ): ?>
                                            <span style="height:<?php echo esc_attr( $h ); ?>%" data-label="<?php echo esc_attr( $crm_chart_labels[ $idx ] ?? '' ); ?>"></span>
                                        <?php endforeach; ?>
                                        </div>
                                        <div class="va-crm__chart-labels">
                                            <?php foreach ( $crm_chart_labels as $label ) : ?>
                                            <span><?php echo esc_html( $label ); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <div class="va-crm__signal-stack va-crm__signal-stack--fold">
                                <article class="va-crm__signal-card va-crm__signal-card--cyan">
                                    <div class="va-crm__signal-head">
                                        <span>Aktív arány</span>
                                        <strong><?php echo esc_html( $crm_active_rate_pc ); ?>%</strong>
                                    </div>
                                    <div class="va-crm__signal-bar"><span style="width:<?php echo esc_attr( max( 6, $crm_active_rate_pc ) ); ?>%"></span></div>
                                    <small><?php echo esc_html( number_format( $crm_active_count, 0, ',', ' ' ) ); ?> aktív a <?php echo esc_html( number_format( $crm_total_listings, 0, ',', ' ' ) ); ?> hirdetésből</small>
                                </article>

                                <article class="va-crm__signal-card va-crm__signal-card--mint">
                                    <div class="va-crm__signal-head">
                                        <span>Heti tempó</span>
                                        <strong><?php echo esc_html( $crm_momentum_pc ); ?>%</strong>
                                    </div>
                                    <div class="va-crm__signal-bar"><span style="width:<?php echo esc_attr( max( 6, $crm_momentum_pc ) ); ?>%"></span></div>
                                    <small><?php echo esc_html( number_format( $crm_last7_count, 0, ',', ' ' ) ); ?> új aktivitás az utóbbi 7 napban</small>
                                </article>

                                <article class="va-crm__signal-card va-crm__signal-card--amber">
                                    <div class="va-crm__signal-head">
                                        <span>Érdeklődés</span>
                                        <strong><?php echo esc_html( $crm_engagement_pc ); ?>%</strong>
                                    </div>
                                    <div class="va-crm__signal-bar"><span style="width:<?php echo esc_attr( max( 6, $crm_engagement_pc ) ); ?>%"></span></div>
                                    <small><?php echo esc_html( number_format( $crm_watch_count + $crm_bid_count, 0, ',', ' ' ) ); ?> kedvenc és licit összesen</small>
                                </article>

                                <article class="va-crm__signal-card va-crm__signal-card--violet">
                                    <div class="va-crm__signal-head">
                                        <span><?php echo esc_html( $crm_plan_limit > 0 ? 'Csomagkihasználtság' : 'Kiemelt aktivitás' ); ?></span>
                                        <strong><?php echo esc_html( $crm_plan_limit > 0 ? $crm_plan_usage_pc . '%' : number_format( $crm_highlight_count + $crm_newpill_count, 0, ',', ' ' ) ); ?></strong>
                                    </div>
                                    <div class="va-crm__signal-bar"><span style="width:<?php echo esc_attr( max( 6, $crm_plan_limit > 0 ? $crm_plan_usage_pc : min( 100, 20 + ( $crm_highlight_count + $crm_newpill_count ) * 12 ) ) ); ?>%"></span></div>
                                    <small><?php echo esc_html( $crm_plan_limit > 0 ? ( $crm_plan_used . ' / ' . $crm_plan_limit . ' hely felhasználva' ) : ( 'Kiemelt: ' . $crm_highlight_count ) ); ?></small>
                                </article>
                            </div>

                            <div class="va-crm__kpis">
                                <article class="va-crm__kpi">
                                    <span class="va-crm__kpi-label">Összes hirdetés</span>
                                    <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_total_listings, 0, ',', ' ' ) ); ?></strong>
                                    <small class="va-crm__kpi-sub">Aktív: <?php echo esc_html( number_format( $crm_active_count, 0, ',', ' ' ) ); ?></small>
                                    <div class="va-crm__kpi-spark" aria-hidden="true"><span style="height:30%"></span><span style="height:48%"></span><span style="height:36%"></span><span style="height:62%"></span><span style="height:58%"></span><span style="height:74%"></span><span style="height:66%"></span></div>
                                </article>
                                <article class="va-crm__kpi">
                                    <span class="va-crm__kpi-label">Összes megtekintés</span>
                                    <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_total_views, 0, ',', ' ' ) ); ?></strong>
                                    <small class="va-crm__kpi-sub">Átlag / hirdetés: <?php echo esc_html( number_format( $crm_avg_views, 1, ',', ' ' ) ); ?></small>
                                    <div class="va-crm__kpi-spark" aria-hidden="true"><span style="height:24%"></span><span style="height:39%"></span><span style="height:44%"></span><span style="height:52%"></span><span style="height:70%"></span><span style="height:64%"></span><span style="height:80%"></span></div>
                                </article>
                                <article class="va-crm__kpi">
                                    <span class="va-crm__kpi-label">Átlagár</span>
                                    <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_avg_price, 0, ',', ' ' ) ); ?> Ft</strong>
                                    <small class="va-crm__kpi-sub">Számolt hirdetés: <?php echo esc_html( number_format( $crm_priced_count, 0, ',', ' ' ) ); ?></small>
                                    <div class="va-crm__kpi-spark" aria-hidden="true"><span style="height:55%"></span><span style="height:62%"></span><span style="height:58%"></span><span style="height:50%"></span><span style="height:68%"></span><span style="height:61%"></span><span style="height:72%"></span></div>
                                </article>
                                <article class="va-crm__kpi">
                                    <span class="va-crm__kpi-label">Top hirdetés</span>
                                    <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_top_views, 0, ',', ' ' ) ); ?></strong>
                                    <small class="va-crm__kpi-sub"><?php echo esc_html( wp_trim_words( $crm_top_title, 6, '...' ) ); ?></small>
                                    <div class="va-crm__kpi-spark" aria-hidden="true"><span style="height:16%"></span><span style="height:32%"></span><span style="height:49%"></span><span style="height:74%"></span><span style="height:63%"></span><span style="height:77%"></span><span style="height:88%"></span></div>
                                </article>
                                <article class="va-crm__kpi">
                                    <span class="va-crm__kpi-label">Friss aktivitás</span>
                                    <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_last7_count, 0, ',', ' ' ) ); ?> / 7 nap</strong>
                                    <small class="va-crm__kpi-sub"><?php echo esc_html( number_format( $crm_last30_count, 0, ',', ' ' ) ); ?> / 30 nap</small>
                                    <div class="va-crm__kpi-spark" aria-hidden="true"><span style="height:26%"></span><span style="height:36%"></span><span style="height:41%"></span><span style="height:57%"></span><span style="height:48%"></span><span style="height:69%"></span><span style="height:75%"></span></div>
                                </article>
                                <article class="va-crm__kpi">
                                    <span class="va-crm__kpi-label">Figyelők és licitek</span>
                                    <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_watch_count, 0, ',', ' ' ) ); ?> kedvenc</strong>
                                    <small class="va-crm__kpi-sub"><?php echo esc_html( number_format( $crm_bid_count, 0, ',', ' ' ) ); ?> licit | <?php echo esc_html( $crm_highlight_count ); ?> kiemelt</small>
                                    <div class="va-crm__kpi-spark" aria-hidden="true"><span style="height:22%"></span><span style="height:35%"></span><span style="height:58%"></span><span style="height:46%"></span><span style="height:64%"></span><span style="height:56%"></span><span style="height:71%"></span></div>
                                </article>
                            </div>
                        </div>
                    </details>
                </section>
                <?php endif; // can_see_stats ?>

                <?php if ( $listings ): ?>
                <!-- Sort controls -->
                <?php
                $cur_url = strtok( (string) $_SERVER['REQUEST_URI'], '?' );
                $make_sort_url = function( string $by ) use ( $cur_url, $sort_by, $sort_dir ): string {
                    $dir = ( $sort_by === $by && $sort_dir === 'desc' ) ? 'asc' : 'desc';
                    return esc_url( add_query_arg( [ 'sort_by' => $by, 'sort_dir' => $dir ], $cur_url ) );
                };
                $sort_arrow = function( string $by ) use ( $sort_by, $sort_dir ): string {
                    if ( $sort_by !== $by ) {
                        return '<svg class="va-ico va-sort-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7l4-4 4 4"/><path d="M16 17l-4 4-4-4"/><path d="M12 4v16"/></svg>';
                    }
                    return $sort_dir === 'desc'
                        ? '<svg class="va-ico va-sort-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 4v16"/><path d="M7 15l5 5 5-5"/></svg>'
                        : '<svg class="va-ico va-sort-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20V4"/><path d="M7 9l5-5 5 5"/></svg>';
                };
                ?>
                <div class="va-sort-bar">
                    <span style="font-size:11px;color:rgba(255,255,255,.4);margin-right:6px;">Rendezés:</span>
                    <a href="<?php echo $make_sort_url('date'); ?>" class="va-sort-btn <?php echo $sort_by==='date'?'active':''; ?>">Dátum <?php echo $sort_arrow('date'); ?></a>
                    <a href="<?php echo $make_sort_url('views'); ?>" class="va-sort-btn <?php echo $sort_by==='views'?'active':''; ?>">Nézettség <?php echo $sort_arrow('views'); ?></a>
                    <a href="<?php echo $make_sort_url('price'); ?>" class="va-sort-btn <?php echo $sort_by==='price'?'active':''; ?>">Ár <?php echo $sort_arrow('price'); ?></a>
                </div>

                <!-- Bulk toolbar -->
                <div class="va-bulk-toolbar" id="va-bulk-toolbar">
                    <label class="va-bulk-select-all-label">
                        <input type="checkbox" id="va-select-all"> Összes
                    </label>
                    <div class="va-bulk-dropdown" id="va-bulk-dropdown">
                        <button type="button" class="va-bulk-dropdown__toggle" id="va-bulk-dropdown-toggle">
                            <span id="va-bulk-dropdown-label">— Tömeges művelet —</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="va-bulk-dropdown__menu" id="va-bulk-dropdown-menu" style="display:none;">
                            <button type="button" class="va-bulk-dropdown__item" data-value="" data-label="— Tömeges művelet —">— Tömeges művelet —</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="activate" data-label="Aktiválás"><svg class="va-ico" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Aktiválás</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="suspend" data-label="Szüneteltetés"><svg class="va-ico" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg> Szüneteltetés</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="delete" data-label="Törlés"><svg class="va-ico" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg> Törlés</button>
                        </div>
                        <input type="hidden" id="va-bulk-action" value="">
                    </div>
                    <button class="va-btn va-btn--sm va-bulk-exec" id="va-bulk-exec" style="background:rgba(255,0,0,.18);border:1px solid rgba(255,0,0,.4);color:#fff;">Végrehajtás</button>
                    <span class="va-bulk-count" id="va-bulk-count" style="font-size:11px;color:rgba(255,255,255,.45);margin-left:6px;"></span>
                </div>

                <div class="va-table-scroll">
                <table class="va-user-listings-table" style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="padding:8px 4px;width:28px;"></th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Cím</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);white-space:nowrap;min-width:110px;">Ár</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Státusz</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Dátum</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Megtekintés</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Kiemelés</th>
                            <th style="padding:8px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $listings as $l ):
                        $price    = get_post_meta( $l->ID, 'va_price', true );
                        $p_type   = get_post_meta( $l->ID, 'va_price_type', true ) ?: 'fixed';
                        $valid_views = (int) get_post_meta( $l->ID, 'va_views', true );
                        $is_suspended    = get_post_meta( $l->ID, 'va_is_suspended', true ) === '1';
                        $suspended_by_plan = get_post_meta( $l->ID, 'va_suspended_by_plan', true ) === '1';
                        $suspended_at    = (int) get_post_meta( $l->ID, 'va_suspended_at', true );
                        $active_since_ts = isset( $active_sinces[ $l->ID ] ) ? $active_sinces[ $l->ID ] : (int) get_post_meta( $l->ID, 'va_active_since', true );
                        if ( ! $active_since_ts ) $active_since_ts = strtotime( $l->post_date );
                        $now             = current_time( 'timestamp' );
                        $can_suspend     = in_array( $user_plan, [ 'gold', 'platinum' ], true );
                        $buy_url         = home_url( '/va-kredit-vasarlas/' );
                        $days_running    = (int) max( 1, ceil( ( $now - $active_since_ts ) / 86400 ) );
                        $days_expiry     = max( 0, 30 - $days_running );
                        $img_count       = isset( $gallery_meta_map[ $l->ID ] ) ? (int) $gallery_meta_map[ $l->ID ] : 0;
                        $l_sale_price    = $sale_prices[ $l->ID ] ?? 0.0;
                        $l_sale_end      = $sale_ends[ $l->ID ] ?? '';
                        $statuses = [
                            'publish' => '<span style="color:#00c850">● Aktív</span>',
                            'pending' => '<span style="color:#ffb400">● Jóváhagyásra vár</span>',
                            'draft'   => '<span style="color:#888">● Piszkozat</span>',
                            'private' => $suspended_by_plan
                                ? '<span style="color:#ff4444;font-weight:600;">● Leállítva</span>'
                                : ( $is_suspended
                                    ? '<span style="color:#ff9900">● Szüneteltetve</span>'
                                    : '<span style="color:#888">● Privát</span>' ),
                        ];
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);" class="va-listing-row" data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>">
                        <td style="padding:10px 4px;text-align:center;vertical-align:middle;">
                            <input type="checkbox" class="va-row-check" value="<?php echo esc_attr( (string) $l->ID ); ?>" style="cursor:pointer;width:15px;height:15px;accent-color:#ff2a2a;">
                        </td>
                        <td style="padding:10px 8px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php
                                $thumb_id  = get_post_thumbnail_id( $l->ID );
                                if ( ! $thumb_id ) {
                                    $gallery = get_post_meta( $l->ID, 'va_gallery_ids', true );
                                    $ids     = array_filter( array_map( 'intval', explode( ',', (string)$gallery ) ) );
                                    $thumb_id = $ids[0] ?? 0;
                                }
                                if ( $thumb_id ) :
                                    $thumb_url = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
                                ?>
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:6px;flex-shrink:0;border:1px solid rgba(255,255,255,.1);">
                                <?php endif; ?>
                                <div style="min-width:0;">
                                    <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">
                                        <a href="<?php echo esc_url( get_permalink( $l->ID ) ); ?>" style="color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;"><?php echo esc_html( $l->post_title ); ?></a>
                                        <?php if ( $img_count > 0 ): ?>
                                        <span class="va-img-count-badge"><svg class="va-ico" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><?php echo esc_html( $img_count ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( get_post_meta( $l->ID, 'va_featured', true ) === '1' ): ?>
                                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;background:linear-gradient(135deg,#3a2800,#1e1400);color:#ffc840;border:1px solid rgba(255,180,0,.5);box-shadow:0 0 8px rgba(255,160,0,.2);padding:2px 7px;border-radius:20px;"><svg width="9" height="9" viewBox="0 0 24 24" fill="#ffc840" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Kiemelt</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top:3px;font-size:11px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <?php if ( $suspended_by_plan ): ?>
                                        <span style="color:#ff4444;">Limit felett – kredit szükséges</span>
                                    <?php elseif ( $is_suspended && $suspended_at ): ?>
                                        <span style="color:#ff9900;">● Szüneteltetve: <?php echo esc_html( date_i18n( 'Y.m.d', $suspended_at ) ); ?></span>
                                    <?php else: ?>
                                        <span style="color:rgba(255,255,255,.35);">Fut: <?php echo esc_html( $days_running . ' napja' ); ?></span>
                                        <?php if ( $l->post_status === 'publish' && $days_expiry <= 7 ): ?>
                                        <span class="va-expiry-badge <?php echo $days_expiry <= 3 ? 'va-expiry-badge--critical' : 'va-expiry-badge--warn'; ?>"><svg class="va-ico" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> <?php echo $days_expiry > 0 ? esc_html( $days_expiry . ' nap múlva lejár' ) : 'Lejárt'; ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:10px 8px;white-space:nowrap;min-width:110px;">
                            <?php if ( $l_sale_price > 0 ): ?>
                                <del style="color:rgba(255,255,255,.4);font-size:12px;"><?php echo esc_html( va_format_price( $price, $p_type ) ); ?></del><br>
                                <span style="color:#ff3030;font-weight:700;"><?php echo esc_html( number_format( $l_sale_price, 0, ',', ' ' ) . ' Ft' ); ?> <span style="font-size:10px;background:rgba(255,0,0,.2);border:1px solid rgba(255,0,0,.4);border-radius:4px;padding:1px 5px;">AKCIÓ</span></span>
                                <?php if ( $l_sale_end ): ?><br><span style="font-size:10px;color:rgba(255,255,255,.35);">–<?php echo esc_html( $l_sale_end ); ?></span><?php endif; ?>
                                <button class="va-sale-edit-btn va-sale-edit-btn--active" data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>" data-normal-price="<?php echo esc_attr( (string) floatval( $price ) ); ?>" data-sale-price="<?php echo esc_attr( (string) $l_sale_price ); ?>" data-sale-end="<?php echo esc_attr( $l_sale_end ); ?>" title="Ár szerkesztése"><svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
                            <?php else: ?>
                                <?php echo esc_html( va_format_price( $price, $p_type ) ); ?>
                                <button class="va-sale-edit-btn va-sale-edit-btn--idle" data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>" data-normal-price="<?php echo esc_attr( (string) floatval( $price ) ); ?>" data-sale-price="" data-sale-end="" title="Ár szerkesztése"><svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 8px;"><?php echo $statuses[ $l->post_status ] ?? esc_html( $l->post_status ); ?></td>
                        <td style="padding:10px 8px;color:rgba(255,255,255,0.5);"><?php echo esc_html( get_the_date( 'Y.m.d', $l ) ); ?></td>
                        <td style="padding:10px 8px;">
                            <div style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
                            <?php if ( current_user_can( 'manage_options' ) || $user_plan === 'platinum' ) : ?>
                            <span style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;letter-spacing:.02em;background:linear-gradient(135deg,rgba(255,0,0,.20),rgba(90,0,0,.20));color:#fff;border:1px solid rgba(255,0,0,.55);box-shadow:0 0 8px rgba(255,0,0,.18);padding:3px 9px;border-radius:999px;line-height:1;">
                                <svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <?php echo esc_html( number_format( $valid_views, 0, ',', ' ' ) ); ?>
                            </span>
                            <?php if ( function_exists( 'va_user_can_open_geo_report' ) && va_user_can_open_geo_report( (int) $user->ID, (int) $l->ID ) ) : ?>
                                <button type="button"
                                        class="va-geo-report-trigger"
                                        data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                        data-geo-nonce="<?php echo esc_attr( wp_create_nonce( 'va_view_geo_report_' . $l->ID ) ); ?>"
                                        title="Megtekintési helyek - geo riport"
                                        style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;letter-spacing:.02em;background:linear-gradient(135deg,rgba(255,0,0,.20),rgba(90,0,0,.20));color:#fff;border:1px solid rgba(255,0,0,.55);box-shadow:0 0 8px rgba(255,0,0,.18);padding:3px 9px;border-radius:999px;line-height:1;white-space:nowrap;cursor:pointer;">
                                    <svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18"></path><path d="M12 3a14 14 0 0 1 0 18"></path><path d="M12 3a14 14 0 0 0 0 18"></path></svg>
                                    Geo
                                </button>
                            <?php endif; ?>
                            <?php endif; // admin + platinum: szem + geo ?>
                            </div>
                        </td>
                        <td style="padding:10px 8px;">
                            <?php
                            // Boost gomb
                            if ( class_exists( 'VA_User_Roles' ) ) :
                                echo '<div class="va-pill-toggles">';
                                $boost_info = VA_User_Roles::can_boost( $user->ID, $l->ID );
                                $is_boosted_now = VA_User_Roles::is_boosted( $l->ID );
                                $is_new_pill_now = VA_User_Roles::is_new_pill( $l->ID );
                                if ( $is_boosted_now ):
                            ?>
                            <button class="va-boost-btn va-boost-btn--on"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="remove"
                                    aria-pressed="true">
                                <span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelt: BE
                            </button>
                            <?php elseif ( $boost_info['can'] ):
                            ?>
                            <button class="va-boost-btn va-boost-btn--off"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="boost"
                                    aria-pressed="false">
                                <span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelés: KI
                            </button>
                            <?php else:
                                $hrs = (int) ceil( $boost_info['seconds_remaining'] / 3600 );
                                $days_left = (int) ceil( $boost_info['seconds_remaining'] / 86400 );
                                $wait_label = $boost_info['seconds_remaining'] >= 86400 ? ( $days_left . ' nap' ) : ( $hrs . ' óra' );
                            ?>
                            <button class="va-boost-btn va-boost-btn--off"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="boost"
                                    aria-pressed="false"
                                    aria-disabled="true"
                                    disabled
                                    title="<?php echo esc_attr( $boost_info['cooldown_days'] . ' naponként emelhető' ); ?>">
                                <span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelés: várakozás (<?php echo esc_html( $wait_label ); ?>)
                            </button>
                            <?php
                                endif;

                                if ( $is_new_pill_now ):
                            ?>
                            <button class="va-newpill-btn va-newpill-btn--on"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="off"
                                    aria-pressed="true">
                                <span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: BE
                            </button>
                            <?php else: ?>
                            <button class="va-newpill-btn va-newpill-btn--off"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="on"
                                    aria-pressed="false">
                                <span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: KI
                            </button>
                            <?php endif;
                                echo '</div>';
                            endif;
                            ?>
                        </td>
                        <td style="padding:10px 8px;text-align:right;white-space:nowrap;">
                            <?php
                            $edit_page = get_page_by_path('va-hirdetes-feladas');
                            $edit_url  = $edit_page
                                ? add_query_arg( 'edit', $l->ID, get_permalink( $edit_page ) )
                                : get_edit_post_link( $l->ID );
                            ?>
                            <?php if ( $suspended_by_plan ): ?>
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;">
                                <a href="<?php echo esc_url( $buy_url ); ?>" class="va-btn va-btn--sm" style="background:linear-gradient(135deg,rgba(255,60,60,.25),rgba(200,0,0,.25));border:1px solid rgba(255,60,60,.5);color:#ff6060;white-space:nowrap;font-weight:600;">Kredit vásárlás →</a>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Biztosan törlöd ezt a hirdetést?');">
                                    <?php wp_nonce_field( 'va_delete_listing', 'va_delete_listing_nonce' ); ?>
                                    <input type="hidden" name="va_action" value="delete_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                    <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.4);color:#ff6060;white-space:nowrap;">Törlés</button>
                                </form>
                            </div>
                            <?php else: ?>
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;">
                                <a href="<?php echo esc_url( $edit_url ); ?>" class="va-btn va-btn--sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.18);color:#fff;white-space:nowrap;">Szerkesztés</a>
                                <?php if ( $l->post_status === 'publish' ): ?>
                                <button class="va-refresh-btn va-btn va-btn--sm"
                                        data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                        data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                        data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                        title="Hirdetés frissítése (lista tetejére tol)"
                                        style="background:rgba(0,180,255,.1);border:1px solid rgba(0,180,255,.35);color:#60d0ff;white-space:nowrap;"><svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.5 14a8.5 8.5 0 1 1 2-5.5"/></svg> Frissítés</button>
                                <?php endif; ?>
                                <?php if ( $can_suspend && in_array( $l->post_status, [ 'publish', 'private' ], true ) ): ?>
                                <form method="post" style="margin:0;">
                                    <?php wp_nonce_field( 'va_suspend_listing', 'va_suspend_listing_nonce' ); ?>
                                    <input type="hidden" name="va_action" value="suspend_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                    <?php if ( $is_suspended ): ?>
                                        <button type="submit" class="va-btn va-btn--sm" style="background:rgba(0,200,80,.12);border:1px solid rgba(0,200,80,.4);color:#00c850;white-space:nowrap;"><svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="8,5 19,12 8,19"/></svg> Indítás</button>
                                    <?php else: ?>
                                        <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,153,0,.12);border:1px solid rgba(255,153,0,.4);color:#ff9900;white-space:nowrap;"><svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg> Szünet</button>
                                    <?php endif; ?>
                                </form>
                                <?php endif; ?>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Biztosan törlöd ezt a hirdetést? A képekkel együtt végleg eltűnik!');">
                                    <?php wp_nonce_field( 'va_delete_listing', 'va_delete_listing_nonce' ); ?>
                                    <input type="hidden" name="va_action" value="delete_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                    <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.4);color:#ff6060;white-space:nowrap;">Törlés</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div><!-- .va-table-scroll -->
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Még nincs feladott hirdetésed.</p>
                    <?php if ( $submit_page ): ?>
                        <a href="<?php echo esc_url( get_permalink( $submit_page ) ); ?>" class="va-btn va-btn--primary">+ Hirdetés feladása</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ( $auctions_enabled ): ?>
            <!-- Tab: Licitjeim -->
            <div id="va-tab-bids" class="va-dashboard__section">
                <h2 class="va-dashboard__title">Licitjeim</h2>
                <?php if ( $bids ): ?>
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Aukció</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Licit</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Dátum</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $bids as $bid ): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:10px 8px;">
                            <a href="<?php echo esc_url( get_permalink( $bid->auction_id ) ); ?>" style="color:#fff;font-weight:600;">
                                <?php echo esc_html( $bid->post_title ); ?>
                            </a>
                        </td>
                        <td style="padding:10px 8px;font-weight:700;color:#ff0000;">
                            <?php echo esc_html( number_format( $bid->amount, 0, ',', ' ' ) . ' Ft' ); ?>
                        </td>
                        <td style="padding:10px 8px;color:rgba(255,255,255,0.5);">
                            <?php echo esc_html( date_i18n( 'Y.m.d H:i', strtotime( $bid->created_at ) ) ); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Még nem adtál le licitet.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Tab: Kedvenceim -->
            <div id="va-tab-watchlist" class="va-dashboard__section">
                <h2 class="va-dashboard__title">Kedvenceim</h2>
                <?php if ( $watchlist ): ?>
                <div class="va-grid">
                    <?php foreach ( $watchlist as $p ):
                        $post = $p;
                        va_template( 'listing/card', [ 'post' => $post ] );
                    endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Még nincs mentett hirdetés.</p>
                <?php endif; ?>
            </div>

            <!-- Tab: Profilom -->
            <div id="va-tab-profile" class="va-dashboard__section">
                <h2 class="va-dashboard__title">Profilom szerkesztése</h2>

                <!-- Profil teljességi sáv -->
                <div class="va-profile-completeness">
                    <div class="va-profile-completeness__head">
                        <span class="va-profile-completeness__label">Profil teljessége</span>
                        <span class="va-profile-completeness__pct"><?php echo esc_html( $completeness_pct ); ?>%</span>
                    </div>
                    <div class="va-profile-completeness__bar">
                        <div style="width:<?php echo esc_attr( $completeness_pct ); ?>%;background:<?php echo $completeness_pct >= 80 ? '#00c850' : ( $completeness_pct >= 50 ? '#ffb400' : '#ff4444' ); ?>"></div>
                    </div>
                    <div class="va-profile-completeness__items">
                    <?php foreach ( $completeness_items as $item_label => $item_done ): ?>
                        <span class="va-profile-completeness__item <?php echo $item_done ? 'done' : ''; ?>"><?php echo $item_done ? '✓' : '○'; ?> <?php echo esc_html( $item_label ); ?></span>
                    <?php endforeach; ?>
                    </div>
                </div>

                <!-- Trust badge-ek -->
                <div class="va-trust-badges">
                    <span class="va-trust-badge <?php echo $user->user_email ? 'active' : ''; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        E-mail megerősítve
                    </span>
                    <span class="va-trust-badge <?php echo $phone ? 'active' : ''; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Telefonszám megadva
                    </span>
                    <span class="va-trust-badge <?php echo $crm_active_count > 0 ? 'active' : ''; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Aktív hirdető
                    </span>
                    <span class="va-trust-badge active" title="Regisztráció óta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Tagság: <?php echo esc_html( $membership_days < 365 ? $membership_days . ' nap' : round( $membership_days / 365, 1 ) . ' év' ); ?>
                    </span>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'va_profile', 'va_profile_nonce' ); ?>
                    <input type="hidden" name="va_action" value="profile">

                    <div class="va-form-row">
                        <div class="va-form-group">
                            <label>Keresztnév</label>
                            <input type="text" name="profile_firstname" class="va-input" value="<?php echo esc_attr( $user->first_name ); ?>">
                        </div>
                        <div class="va-form-group">
                            <label>Vezetéknév</label>
                            <input type="text" name="profile_lastname" class="va-input" value="<?php echo esc_attr( $user->last_name ); ?>">
                        </div>
                    </div>

                    <div class="va-form-group">
                        <label>E-mail cím</label>
                        <input type="text" class="va-input" value="<?php echo esc_attr( $user->user_email ); ?>" disabled style="opacity:0.5;">
                        <p style="font-size:11px;color:rgba(255,255,255,0.4);">Az e-mail cím megváltoztatásához lépj kapcsolatba az adminisztrátorokkal.</p>
                    </div>

                    <div class="va-form-group">
                        <label>Telefonszám</label>
                        <input type="tel" name="profile_phone" class="va-input" value="<?php echo esc_attr( $phone ); ?>">
                    </div>

                    <div class="va-form-group">
                        <label>Profilkép</label>
                        <div class="va-profile-avatar-editor">
                            <div class="va-profile-avatar-editor__preview">
                                <?php if ( $avatar_url ): ?>
                                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="Profilkép előnézet">
                                <?php else: ?>
                                    <?php echo esc_html( strtoupper( mb_substr( $user->display_name ?: $user->user_login, 0, 1 ) ) ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="va-profile-avatar-editor__fields">
                                <input type="file" name="profile_avatar" class="va-input" accept="image/jpeg,image/png,image/webp">
                                <?php if ( $avatar_url ): ?>
                                    <label class="va-profile-avatar-editor__remove">
                                        <input type="checkbox" name="profile_avatar_remove" value="1"> Profilkép törlése
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="va-form-group">
                        <label>Bemutatkozás</label>
                        <textarea name="profile_bio" class="va-textarea"><?php echo esc_textarea( $user->description ); ?></textarea>
                    </div>

                    <?php if ( $user_plan === 'platinum' ): ?>
                    <div class="va-form-group">
                        <label>Rang / címke a hirdetésoldalakon
                            <span style="font-size:11px;font-weight:400;color:rgba(255,255,255,.45);margin-left:6px;">Platinum jogosultság</span>
                        </label>
                        <input type="text" name="profile_seller_label" class="va-input" value="<?php echo esc_attr( $seller_label ); ?>" placeholder="pl. Kereskedő, Viszonteladó – hagyd üresen ha nem kell" maxlength="40">
                        <p style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:4px;">Ez jelenik meg a feladó blokkban a hirdetéseidnél. Üresen hagyva az alapértelmezett csomagcímke látszik.</p>
                    </div>
                    <?php endif; ?>

                    <hr style="border-color:rgba(255,255,255,0.1);margin:20px 0;">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">Jelszócsere (hagyd üresen ha nem változtatod)</h3>

                    <div class="va-form-row">
                        <div class="va-form-group">
                            <label>Új jelszó</label>
                            <input type="password" name="profile_newpass" class="va-input" autocomplete="new-password" minlength="8">
                        </div>
                        <div class="va-form-group">
                            <label>Új jelszó ismét</label>
                            <input type="password" name="profile_newpass2" class="va-input" autocomplete="new-password" minlength="8">
                        </div>
                    </div>

                    <button type="submit" class="va-btn va-btn--primary">Mentés</button>
                </form>

            </div>

            <!-- Tab: Fiók törlése -->
            <div id="va-tab-deleteaccount" class="va-dashboard__section">
                <h2 class="va-dashboard__title" style="color:#ff6060;">Fiók törlése</h2>
                <div class="va-danger-zone">
                    <p class="va-danger-zone__desc">A fiók törlése <strong>visszafordíthatatlan</strong>! Összes hirdetésed, képeid és adataid végleg törlődnek.</p>
                    <p class="va-danger-zone__desc">A megerősítéshez írd be az alábbi mezőbe: <strong style="color:#ff6060;">TORLESEM</strong></p>
                    <form method="post">
                        <?php wp_nonce_field( 'va_delete_profile', 'va_delete_profile_nonce' ); ?>
                        <input type="hidden" name="va_action" value="delete_profile">
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:12px;">
                            <input id="va-confirm-del-input" type="text" name="confirm_delete" class="va-input va-danger-zone__input" placeholder="TORLESEM" autocomplete="off">
                            <button type="submit" class="va-btn va-danger-zone__submit" onclick="return this.form['confirm_delete'].value==='TORLESEM'||(alert('Írd be pontosan: TORLESEM'),false);">Fiók végleges törlése</button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- .va-dashboard__content -->
    </div><!-- .va-dashboard -->
</div>

<div class="va-sale-modal-overlay" id="va-sale-modal-overlay" role="dialog" aria-modal="true" aria-label="Akciós ár beállítása">
    <div class="va-sale-modal">
        <h3>Árak szerkesztése</h3>
        <input type="hidden" id="va-sale-modal-post-id">
        <label>Normál ár (Ft)</label>
        <input type="number" id="va-sale-modal-normal-price" min="0" placeholder="pl. 2500000">
        <label>Akciós ár (Ft) — 0 = törlés</label>
        <input type="number" id="va-sale-modal-price" min="0" placeholder="pl. 1200000">
        <label>Akció vége (opcionális)</label>
        <div class="va-sale-modal__date-wrap">
            <input type="text" id="va-sale-modal-end" class="va-sale-modal__date-input" placeholder="YYYY-MM-DD" inputmode="numeric" autocomplete="off" readonly>
            <button type="button" id="va-sale-modal-cal-btn" class="va-sale-modal__date-btn" aria-label="Naptár megnyitása" title="Naptár megnyitása">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </button>
            <div id="va-sale-calendar" class="va-sale-calendar" style="display:none;">
                <div class="va-sale-calendar__head">
                    <button type="button" id="va-sale-cal-prev" class="va-sale-calendar__nav" aria-label="Előző hónap">‹</button>
                    <div id="va-sale-cal-title" class="va-sale-calendar__title"></div>
                    <button type="button" id="va-sale-cal-next" class="va-sale-calendar__nav" aria-label="Következő hónap">›</button>
                </div>
                <div class="va-sale-calendar__dow">
                    <span>H</span><span>K</span><span>Sze</span><span>Cs</span><span>P</span><span>Szo</span><span>V</span>
                </div>
                <div id="va-sale-cal-grid" class="va-sale-calendar__grid"></div>
            </div>
        </div>
        <div class="va-sale-preview" id="va-sale-preview">
            <div class="va-sale-preview__label">Előnézet</div>
            <div class="va-sale-preview__prices">
                <span class="va-sale-preview__old" id="va-sale-preview-old"></span>
                <span class="va-sale-preview__new" id="va-sale-preview-new"></span>
                <span class="va-sale-preview__badge" id="va-sale-preview-badge" style="display:none;">AKCIÓ</span>
            </div>
            <div class="va-sale-preview__meta" id="va-sale-preview-meta"></div>
        </div>
        <div class="va-sale-modal__actions">
            <button class="va-sale-modal__remove" id="va-sale-modal-remove">Akció törlése</button>
            <button class="va-sale-modal__cancel" id="va-sale-modal-cancel">Mégse</button>
            <button class="va-sale-modal__save" id="va-sale-modal-save">Mentés</button>
        </div>
    </div>
</div>

<?php if ( $va_show_daily_welcome ) : ?>
<div class="va-welcome-overlay" id="va-welcome-overlay" role="dialog" aria-modal="true" aria-labelledby="va-welcome-title">
    <div class="va-welcome-modal" id="va-welcome-modal">
        <div class="va-welcome-modal__hero" aria-hidden="true">
            <img src="<?php echo esc_url( $va_welcome_image_url ); ?>" data-fallback="<?php echo esc_attr( $va_welcome_svg_fallback ); ?>" alt="Szerencsesüti kép" id="va-welcome-hero-img">
        </div>
        <div class="va-welcome-modal__quote">
            <h3 class="va-welcome-modal__title" id="va-welcome-title"></h3>
        </div>
        <button type="button" class="va-welcome-modal__cta" id="va-welcome-close">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            Napi kívánság bezárása
        </button>
    </div>
</div>
<?php endif; ?>

<style>
.va-welcome-overlay {
    position: fixed;
    inset: 0;
    z-index: 30000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 22px;
    background:
        radial-gradient(circle at 22% 20%, rgba(255, 18, 18, 0.24), transparent 55%),
        radial-gradient(circle at 82% 78%, rgba(255, 70, 70, 0.2), transparent 60%),
        rgba(3, 3, 3, 0.86);
    backdrop-filter: blur(10px) saturate(1.15);
    -webkit-backdrop-filter: blur(10px) saturate(1.15);
}
.va-welcome-overlay.open { display: flex; animation: vaWelcomeFadeIn .28s ease-out; }

.va-welcome-modal {
    position: relative;
    width: min(560px, 94vw);
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    background:
        linear-gradient(140deg, rgba(255, 24, 24, 0.28), rgba(8, 8, 8, 0.88) 38%, rgba(8, 8, 8, 0.95)),
        repeating-linear-gradient(0deg, rgba(255,255,255,.02) 0 1px, transparent 1px 24px),
        repeating-linear-gradient(90deg, rgba(255,255,255,.018) 0 1px, transparent 1px 24px);
    box-shadow: 0 28px 90px rgba(0,0,0,.62), inset 0 1px 0 rgba(255,255,255,.16);
    color: #fff;
    padding: 28px 24px 22px;
    overflow: hidden;
    transform: translateY(18px) scale(.97);
    opacity: 0;
    animation: vaWelcomePopIn .46s cubic-bezier(.22,.78,.2,1) forwards;
    text-align: center;
}
.va-welcome-modal,
.va-welcome-modal * {
    color: #fff !important;
}
.va-welcome-modal__hero {
    display:flex;
    align-items:center;
    justify-content:center;
    width:100%;
    aspect-ratio: 16 / 7;
    margin-bottom: 14px;
    border-radius: 14px;
    border:1px solid rgba(255,255,255,.16);
    background-color: rgba(255,255,255,.04);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.15);
}
.va-welcome-modal__hero img {
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
    border-radius: 14px;
}
.va-welcome-modal__quote {
    position: relative;
    margin: 6px 0 2px;
    padding: 6px 14px;
}
.va-welcome-modal__quote::before,
.va-welcome-modal__quote::after {
    position:absolute;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 34px;
    color: rgba(255,255,255,.58);
    line-height: 1;
}
.va-welcome-modal__quote::before { content: '“'; left: 0; top: -2px; }
.va-welcome-modal__quote::after { content: '”'; right: 0; bottom: -4px; }
.va-welcome-modal__title {
    margin: 4px 0 0;
    font-family: 'Trebuchet MS', 'Segoe UI', Arial, sans-serif;
    font-size: clamp(17px, 2.3vw, 24px);
    line-height: 1.38;
    font-weight: 700;
    letter-spacing: .005em;
    text-shadow: 0 6px 14px rgba(0,0,0,.44);
}
.va-welcome-modal__cta {
    margin-top: 18px;
    height: 46px;
    min-width: 230px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    border-radius: 999px;
    border: 1px solid rgba(255,80,80,.8);
    background: linear-gradient(90deg, #b30000 0%, #ff0000 100%);
    color: #fff !important;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .02em;
    cursor: pointer;
    box-shadow: 0 8px 26px rgba(255,140,35,.32);
    transition: transform .18s ease, box-shadow .18s ease;
}
.va-welcome-modal__cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 30px rgba(255,40,40,.42);
}

@keyframes vaWelcomeFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes vaWelcomePopIn {
    from { transform: translateY(18px) scale(.97); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}
@keyframes vaWelcomeFloatA {
    0%,100% { transform: translate3d(0,0,0); }
    50% { transform: translate3d(-8px, 8px, 0); }
}
@keyframes vaWelcomeFloatB {
    0%,100% { transform: translate3d(0,0,0); }
    50% { transform: translate3d(8px, -6px, 0); }
}

@media (max-width: 640px) {
    .va-welcome-overlay { padding: 14px; }
    .va-welcome-modal { padding: 24px 18px 18px; border-radius: 16px; }
    .va-welcome-modal__hero { aspect-ratio: 4 / 3; }
    .va-welcome-modal__title { font-size: 18px; }
    .va-welcome-modal__cta { width: 100%; }
}

.va-dash-user-head {
    padding:14px;
    border-bottom:1px solid rgba(255,255,255,.08);
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
    overflow:hidden;
}
.va-dash-user-head__avatar {
    width:52px;
    height:52px;
    min-width:52px;
    border-radius:50%;
    overflow:hidden;
    background:rgba(255,255,255,.08);
    border:2px solid rgba(255,255,255,.18);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:18px;
    font-weight:800;
    flex-shrink:0;
}
.va-dash-user-head__avatar img { width:100% !important;height:100% !important;object-fit:cover;display:block;border-radius:50%;max-width:none !important;max-height:none !important; }
.va-dash-user-head__name { color:#fff;font-size:12px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0; }

.va-ico {
    display:inline-block;
    vertical-align:middle;
    flex-shrink:0;
    width:20px;
    height:20px;
    transition:transform .2s ease, filter .2s ease, opacity .2s ease;
}
.va-dashboard__nav-ico {
    width:34px;
    height:34px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:9px;
    background:linear-gradient(160deg, rgba(255,255,255,.12), rgba(255,255,255,.04));
    border:1px solid rgba(255,255,255,.16);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 8px 18px rgba(0,0,0,.32);
}
.va-dashboard__nav-item:hover .va-dashboard__nav-ico,
.va-dashboard__nav-item.active .va-dashboard__nav-ico {
    background:linear-gradient(160deg, rgba(255,44,44,.3), rgba(255,120,0,.18));
    border-color:rgba(255,80,80,.5);
}
.va-dashboard__nav-item:hover .va-ico,
.va-dashboard__nav-item.active .va-ico {
    transform:translateY(-1px) scale(1.08);
    filter:drop-shadow(0 0 10px rgba(255,42,42,.45));
}
.va-ico--heart { animation:vaIcoPulse 2.2s ease-in-out infinite; }
.va-ico--hammer { animation:vaIcoTilt 2.8s ease-in-out infinite; transform-origin:70% 30%; }
.va-ico--list { animation:vaIcoFloat 3.2s ease-in-out infinite; }

@keyframes vaIcoPulse {
    0%,100% { transform:scale(1); }
    50% { transform:scale(1.12); }
}
@keyframes vaIcoTilt {
    0%,100% { transform:rotate(0deg); }
    50% { transform:rotate(-10deg); }
}
@keyframes vaIcoFloat {
    0%,100% { transform:translateY(0); }
    50% { transform:translateY(-2px); }
}

.va-dash-avatar-form {
    padding:10px 14px 12px;
    border-bottom:1px solid rgba(255,255,255,.08);
    display:flex;
    flex-direction:column;
    gap:8px;
}
.va-dash-avatar-form__file { display:none; }
.va-dash-avatar-drop {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:4px;
    padding:12px 8px;
    border:1px dashed rgba(255,255,255,.2);
    border-radius:10px;
    background:rgba(255,255,255,.04);
    cursor:pointer;
    transition:.2s ease;
    text-align:center;
}
.va-dash-avatar-drop:hover,
.va-dash-avatar-drop.drag-over {
    border-color:#ff0000;
    background:rgba(255,0,0,.07);
}
.va-dash-avatar-drop__icon { color:rgba(255,255,255,.45);line-height:1; }
.va-dash-avatar-drop:hover .va-dash-avatar-drop__icon,
.va-dash-avatar-drop.drag-over .va-dash-avatar-drop__icon { color:#ff4444; }
.va-dash-avatar-drop__text { font-size:11px;font-weight:700;color:#fff; }
.va-dash-avatar-drop__hint { font-size:10px;color:rgba(255,255,255,.35); }
.va-dash-avatar-form__remove { font-size:11px;color:rgba(255,255,255,.66);display:flex;gap:6px;align-items:center; }
.va-dash-avatar-form__btn {
    width:100%;
    height:32px;
    border-radius:999px;
    border:1px solid rgba(255,42,42,.45);
    background:rgba(255,42,42,.14);
    color:#fff;
    font-weight:700;
    font-size:12px;
    cursor:pointer;
    transition:.2s ease;
}
.va-dash-avatar-form__btn:hover { background:rgba(255,42,42,.24); }

/* ── Plan badge a navban ── */
.va-dash-plan-badge {
    margin-top:auto;padding:12px 14px;
    background:rgba(255,255,255,.05);
    border-top:1px solid rgba(255,255,255,.08);
    border-radius:0 0 0 10px;
}
.va-dash-plan-icon  { font-size:20px;display:block;margin-bottom:4px; }
.va-dash-plan-label { font-size:12px;font-weight:700;color:var(--pc,#fff);display:block; }
.va-dash-plan-usage { margin-top:6px; }
.va-dash-plan-usage > span { font-size:11px;color:rgba(255,255,255,.5); }
.va-dash-plan-bar {
    height:3px;background:rgba(255,255,255,.1);border-radius:2px;margin-top:3px;
}
.va-dash-plan-bar > div { height:3px;border-radius:2px;transition:width .3s; }
.va-dash-plan-badge small { font-size:10px;color:rgba(255,255,255,.3);display:block;margin-top:6px; }
.va-dash-plan-badge small .va-ico { margin-right:3px; opacity:.85; }
.va-dash-plan-badge small .va-ico { width:13px;height:13px; }

.va-btn .va-ico {
    width:14px !important;
    height:14px !important;
    filter:drop-shadow(0 0 8px rgba(255,255,255,.15));
}
.va-sort-arrow {
    width:13px !important;
    height:13px !important;
    opacity:.95;
}
.va-dash-plan-label-form {
    margin-top:10px;
    padding-top:10px;
    border-top:1px solid rgba(255,255,255,.08);
    display:flex;
    flex-direction:column;
    gap:8px;
}
.va-dash-plan-label-form__label { font-size:11px;font-weight:700;color:#fff; }
.va-dash-plan-label-form__input {
    width:100%;
    height:34px;
    border-radius:9px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06);
    color:#fff;
    padding:0 10px;
    font-size:12px;
}
.va-dash-plan-label-form__input:focus {
    outline:none;
    border-color:#ff2a2a;
    box-shadow:0 0 0 3px rgba(255,42,42,.16);
}
.va-dash-plan-label-form__btn {
    height:32px;
    border-radius:999px;
    border:1px solid rgba(255,42,42,.45);
    background:rgba(255,42,42,.14);
    color:#fff;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    transition:.2s ease;
}
.va-dash-plan-label-form__btn:hover { background:rgba(255,42,42,.24); }

.va-profile-avatar-editor { display:flex;align-items:center;gap:16px; }
.va-profile-avatar-editor__preview {
    width:88px;height:88px;border-radius:50%;overflow:hidden;
    border:2px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:28px;font-weight:800;flex-shrink:0;
}
.va-profile-avatar-editor__preview img { width:100% !important;height:100% !important;object-fit:cover;display:block;max-width:none !important;max-height:none !important; }
.va-profile-avatar-editor__fields { flex:1;display:flex;flex-direction:column;gap:8px; }
.va-profile-avatar-editor__remove { font-size:12px;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:7px; }

.va-dashboard__title-actions {
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    justify-content:flex-end;
}

/* ── CRM stat blokk ── */
.va-crm {
    margin:0 0 20px;
    padding:18px;
    border:1px solid rgba(255,255,255,.1);
    border-radius:24px;
    background:
        radial-gradient(circle at 0% 0%, rgba(112,193,255,.16), transparent 30%),
        radial-gradient(circle at 100% 0%, rgba(173,123,255,.14), transparent 28%),
        radial-gradient(circle at 100% 100%, rgba(79,232,200,.12), transparent 30%),
        linear-gradient(180deg, rgba(15,16,24,.98), rgba(7,8,14,.98));
    box-shadow: 0 24px 80px rgba(0,0,0,.42), inset 0 1px 0 rgba(255,255,255,.08);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    font-family: "Manrope", "Space Grotesk", "Segoe UI", sans-serif;
}
.va-crm__head {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
    padding-bottom:14px;
    border-bottom:1px solid rgba(255,255,255,.08);
}
.va-crm__head h3 {
    margin:0;
    color:#fff;
    font-size:24px;
    font-weight:700;
    letter-spacing:-.02em;
}
.va-crm__head p {
    margin:4px 0 0;
    color:rgba(255,255,255,.66);
    font-size:13px;
    max-width:640px;
}
.va-crm__head-pills {
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
}
.va-crm__head-pill {
    display:inline-flex;
    align-items:center;
    height:30px;
    padding:0 12px;
    border-radius:999px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);
    color:rgba(255,255,255,.84);
    font-size:11px;
    font-weight:600;
}
.va-crm__hero {
    margin-top:16px;
    display:grid;
    grid-template-columns:minmax(0,1fr);
    gap:14px;
}
.va-crm__screen,
.va-crm__signal-card,
.va-crm__kpi,
.va-crm__panel {
    background:linear-gradient(180deg, rgba(255,255,255,.055), rgba(255,255,255,.022));
    border:1px solid rgba(255,255,255,.09);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
}
.va-crm__screen {
    border-radius:24px;
    padding:16px;
    min-height:340px;
    position:relative;
    overflow:hidden;
}
.va-crm__screen::before {
    content:"";
    position:absolute;
    inset:auto -40px -40px auto;
    width:180px;
    height:180px;
    background:radial-gradient(circle, rgba(123,148,255,.16), transparent 68%);
    pointer-events:none;
}
.va-crm__screen-top {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
}
.va-crm__eyebrow {
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.12em;
    color:#8ed8ff;
    margin-bottom:8px;
}
.va-crm__screen-top h4 {
    margin:0;
    color:#fff;
    font-size:22px;
    font-weight:700;
    letter-spacing:-.02em;
}
.va-crm__switches {
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}
.va-crm__switches span {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    height:30px;
    padding:0 12px;
    border-radius:999px;
    color:rgba(255,255,255,.62);
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.06);
    font-size:11px;
}
.va-crm__switches span.is-active {
    color:#081019;
    background:linear-gradient(90deg, #82dbff, #7af1cd);
    border-color:transparent;
    font-weight:700;
}
.va-crm__screen-stats {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
    margin-top:14px;
}
.va-crm__screen-stat {
    padding:12px 12px 10px;
    border-radius:16px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.08);
}
.va-crm__screen-stat span {
    display:block;
    color:rgba(255,255,255,.58);
    font-size:11px;
    margin-bottom:6px;
}
.va-crm__screen-stat strong {
    display:block;
    color:#fff;
    font-size:20px;
    font-weight:700;
}
.va-crm__chart-shell {
    margin-top:16px;
    border-radius:20px;
    padding:14px 12px 10px;
    background:linear-gradient(180deg, rgba(10,14,24,.96), rgba(12,12,22,.92));
    border:1px solid rgba(255,255,255,.08);
}
.va-crm__linechart {
    width:100%;
    height:150px;
    display:block;
}
.va-crm__gridlines line {
    stroke:rgba(255,255,255,.09);
    stroke-width:1;
    stroke-dasharray:3 5;
}
.va-crm__line-stroke {
    stroke:url(#vaChartLineGradient);
    stroke-width:2.3;
    stroke-linecap:round;
    stroke-linejoin:round;
    filter: drop-shadow(0 0 10px rgba(102,209,255,.18));
    stroke-dasharray: 600;
    stroke-dashoffset: 600;
    animation: vaChartDraw 1.2s ease forwards;
}
.va-crm__line-area {
    opacity:.9;
    animation: vaAreaFade .7s ease forwards;
}
.va-crm__line-points circle {
    fill:#fff;
    stroke:#7ee1ff;
    stroke-width:1.6;
}
.va-crm__chart-foot {
    margin-top:8px;
}
.va-crm__flow-bars {
    display:grid;
    grid-template-columns: repeat(7, minmax(0,1fr));
    gap:6px;
    align-items:end;
    height:44px;
}
.va-crm__flow-bars span {
    border-radius:8px 8px 3px 3px;
    background:linear-gradient(180deg, rgba(122,220,255,.95), rgba(90,137,255,.86));
    box-shadow: 0 0 12px rgba(98,188,255,.14);
    animation: vaBarPop .55s ease both;
}
.va-crm__flow-bars span:nth-child(3n+2) {
    background:linear-gradient(180deg, rgba(126,246,214,.95), rgba(68,204,170,.84));
    box-shadow: 0 0 12px rgba(64,220,176,.14);
}
.va-crm__flow-bars span:nth-child(3n) {
    background:linear-gradient(180deg, rgba(255,216,126,.95), rgba(255,171,95,.84));
    box-shadow: 0 0 12px rgba(255,176,94,.12);
}
.va-crm__chart-labels {
    display:grid;
    grid-template-columns:repeat(7,minmax(0,1fr));
    gap:6px;
    margin-top:8px;
}
.va-crm__chart-labels span {
    text-align:center;
    font-size:10px;
    color:rgba(255,255,255,.46);
}
.va-crm__signal-stack {
    display:grid;
    grid-template-columns:1fr;
    gap:12px;
}
.va-crm__signal-stack--fold {
    margin-top:2px;
}
.va-crm__fold {
    margin-top:14px;
    border:1px solid rgba(255,255,255,.08);
    border-radius:18px;
    background:rgba(255,255,255,.025);
    overflow:hidden;
}
.va-crm__fold-summary {
    list-style:none;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:14px 16px;
    cursor:pointer;
}
.va-crm__fold-summary::-webkit-details-marker {
    display:none;
}
.va-crm__fold-summary span {
    color:#fff;
    font-size:14px;
    font-weight:700;
}
.va-crm__fold-summary small {
    color:rgba(255,255,255,.54);
    font-size:11px;
    padding-right:24px;
    position:relative;
}
.va-crm__fold-summary small::after {
    content:'+';
    position:absolute;
    right:0;
    top:50%;
    transform:translateY(-50%);
    width:18px;
    height:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:999px;
    color:#8ee1ff;
    background:rgba(255,255,255,.06);
    font-size:14px;
    line-height:1;
}
.va-crm__fold[open] .va-crm__fold-summary small::after {
    content:'-';
}
.va-crm__fold-content {
    padding:0 16px 16px;
    border-top:1px solid rgba(255,255,255,.06);
}
.va-crm__signal-card {
    padding:14px;
    border-radius:20px;
}
.va-crm__signal-head {
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:10px;
}
.va-crm__signal-head span {
    color:rgba(255,255,255,.66);
    font-size:12px;
}
.va-crm__signal-head strong {
    color:#fff;
    font-size:24px;
    font-weight:700;
    letter-spacing:-.02em;
}
.va-crm__signal-bar {
    margin-top:12px;
    height:7px;
    border-radius:999px;
    background:rgba(255,255,255,.08);
    overflow:hidden;
}
.va-crm__signal-bar span {
    display:block;
    height:100%;
    border-radius:inherit;
    background:linear-gradient(90deg, #7fe4ff, #7ff1c7);
}
.va-crm__signal-card small {
    display:block;
    margin-top:10px;
    color:rgba(255,255,255,.62);
    font-size:11px;
    line-height:1.45;
}
.va-crm__signal-card--mint .va-crm__signal-bar span {
    background:linear-gradient(90deg, #81ffd8, #6fe2ff);
}
.va-crm__signal-card--amber .va-crm__signal-bar span {
    background:linear-gradient(90deg, #ffd586, #ffb174);
}
.va-crm__signal-card--violet .va-crm__signal-bar span {
    background:linear-gradient(90deg, #b99cff, #74cfff);
}
.va-crm__kpis {
    margin-top:14px;
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
}
.va-crm__kpi {
    position:relative;
    border-radius:20px;
    padding:16px 16px 14px;
    overflow:hidden;
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
}
.va-crm__kpi::before {
    content:"";
    position:absolute;
    inset:0 0 auto 0;
    height:1px;
    background:linear-gradient(90deg, rgba(126,226,255,.8), rgba(117,244,210,.7), rgba(255,210,126,.7));
    opacity:.8;
}
.va-crm__kpi::after {
    content:"";
    position:absolute;
    right:10px;
    bottom:10px;
    width:64px;
    height:26px;
    border-radius:10px;
    background:
        repeating-linear-gradient(90deg,
            rgba(255,255,255,.06) 0 5px,
            transparent 5px 10px),
        linear-gradient(180deg, rgba(130,206,255,.12), rgba(130,206,255,0));
    opacity:.65;
    pointer-events:none;
}
.va-crm__kpi:hover {
    transform: translateY(-1px);
    border-color: rgba(143,220,255,.36);
    box-shadow: 0 8px 18px rgba(0,0,0,.25);
}
.va-crm__kpi-label {
    display:block;
    font-size:11px;
    color:rgba(255,255,255,.58);
    margin-bottom:10px;
    letter-spacing:.06em;
    text-transform:uppercase;
}
.va-crm__kpi-value {
    display:block;
    color:#fff;
    font-size:24px;
    font-weight:700;
    line-height:1.08;
    letter-spacing:-.02em;
    text-shadow: 0 4px 16px rgba(0,0,0,.18);
}
.va-crm__kpi-sub {
    display:block;
    margin-top:7px;
    color:rgba(255,255,255,.7);
    font-size:12px;
}
.va-crm__kpi-spark {
    margin-top:12px;
    height:32px;
    display:grid;
    grid-template-columns: repeat(7, minmax(0,1fr));
    gap:5px;
    align-items:end;
}
.va-crm__kpi-spark span {
    border-radius:8px 8px 3px 3px;
    background: linear-gradient(180deg, rgba(115,220,255,.88), rgba(84,156,255,.84));
    opacity:.92;
    transform-origin: bottom;
    animation: vaBarPop .55s ease both;
}
.va-crm__kpi-spark span:nth-child(3n+2) {
    background: linear-gradient(180deg, rgba(116,241,208,.88), rgba(70,200,164,.84));
}
.va-crm__kpi-spark span:nth-child(3n) {
    background: linear-gradient(180deg, rgba(255,216,126,.88), rgba(255,173,94,.84));
}
.va-crm__kpi-spark span:nth-child(1) { animation-delay:.02s; }
.va-crm__kpi-spark span:nth-child(2) { animation-delay:.05s; }
.va-crm__kpi-spark span:nth-child(3) { animation-delay:.08s; }
.va-crm__kpi-spark span:nth-child(4) { animation-delay:.11s; }
.va-crm__kpi-spark span:nth-child(5) { animation-delay:.14s; }
.va-crm__kpi-spark span:nth-child(6) { animation-delay:.17s; }
.va-crm__kpi-spark span:nth-child(7) { animation-delay:.2s; }
.va-crm__panels {
    margin-top:14px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}
.va-crm__panels--triple {
    grid-template-columns:repeat(3,minmax(0,1fr));
}
.va-crm__panel {
    border-radius:20px;
    padding:16px;
}
.va-crm__panel h4 {
    margin:0 0 10px;
    font-size:13px;
    font-weight:700;
    color:#fff;
    letter-spacing:.02em;
    text-transform:uppercase;
}
.va-crm__status-list {
    display:flex;
    flex-direction:column;
    gap:10px;
}
.va-crm__status-meta {
    display:flex;
    align-items:center;
    justify-content:space-between;
    font-size:12px;
    color:rgba(255,255,255,.82);
}
.va-crm__status-meta strong { color:#fff; }
.va-crm__status-bar {
    width:100%;
    height:8px;
    border-radius:999px;
    background:rgba(255,255,255,.08);
    overflow:hidden;
    box-shadow: inset 0 1px 2px rgba(0,0,0,.35);
}
.va-crm__status-bar > span {
    display:block;
    height:100%;
    border-radius:inherit;
    background:linear-gradient(90deg,#7de1ff,#76efcd,#ffd27f);
}
.va-crm__plan-use { margin-top:12px; }
.va-crm__plan-use-meta {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:6px;
    font-size:12px;
    color:rgba(255,255,255,.82);
}
.va-crm__plan-use-meta strong { color:#fff; }
.va-crm__top-list {
    margin:0;
    padding-left:18px;
    display:flex;
    flex-direction:column;
    gap:10px;
}
.va-crm__top-list li {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
.va-crm__top-list a {
    color:#fff;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    max-width:74%;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.va-crm__top-list a:hover { color:#8de4ff; }
.va-crm__top-list span {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:60px;
    height:26px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid rgba(122,220,255,.26);
    background:linear-gradient(135deg, rgba(111,223,255,.12), rgba(119,241,209,.12));
    color:#fff;
    font-size:12px;
    font-weight:700;
}
.va-crm__panel-note {
    margin-top:12px;
    color:rgba(255,255,255,.52);
    font-size:11px;
}
.va-crm__metric-list {
    display:flex;
    flex-direction:column;
    gap:10px;
}
.va-crm__metric-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding-bottom:9px;
    border-bottom:1px solid rgba(255,255,255,.06);
    color:rgba(255,255,255,.68);
    font-size:12px;
}
.va-crm__metric-row:last-child {
    padding-bottom:0;
    border-bottom:0;
}
.va-crm__metric-row strong {
    color:#fff;
    font-size:16px;
    font-weight:700;
}
.va-crm__badges {
    margin-top:12px;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.va-crm__badges span {
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:6px 10px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.82);
    font-size:11px;
}
.va-crm__badges strong { color:#fff; }
.va-crm__empty { margin:0;color:rgba(255,255,255,.68);font-size:12px; }
@media (max-width: 980px) {
    .va-crm__head { display:block; }
    .va-crm__head-pills { justify-content:flex-start; margin-top:12px; }
    .va-crm__hero { grid-template-columns:1fr; }
    .va-crm__panels--triple { grid-template-columns:1fr; }
    .va-crm__kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .va-crm__panels { grid-template-columns:1fr; }
}
@media (max-width: 640px) {
    .va-dashboard__title-actions { justify-content:flex-start; }
    .va-crm { padding:14px; border-radius:18px; }
    .va-crm__head h3 { font-size:20px; }
    .va-crm__screen { padding:14px; min-height:auto; }
    .va-crm__screen-top { display:block; }
    .va-crm__switches { margin-top:10px; }
    .va-crm__screen-stats { grid-template-columns:1fr; }
    .va-crm__kpis { grid-template-columns:1fr; }
}

@keyframes vaChartDraw {
    to { stroke-dashoffset: 0; }
}
@keyframes vaAreaFade {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: .9; transform: translateY(0); }
}
@keyframes vaBarPop {
    from { transform: scaleY(.18); opacity:.35; }
    to { transform: scaleY(1); opacity:.95; }
}

/* ── Boost toggle ── */
.va-pill-toggles {
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:nowrap;
}
.va-table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:thin; scrollbar-color:rgba(255,0,0,.2) transparent; }
.va-table-scroll::-webkit-scrollbar { height:4px; }
.va-table-scroll::-webkit-scrollbar-track { background:transparent; }
.va-table-scroll::-webkit-scrollbar-thumb { background:rgba(255,0,0,.2); border-radius:2px; }
.va-table-scroll::-webkit-scrollbar-thumb:hover { background:#ff0000; }
.va-boost-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    min-width:106px;
    height:30px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid transparent;
    font-size:11px;
    font-weight:700;
    line-height:1;
    letter-spacing:.02em;
    white-space:nowrap;
    cursor:pointer;
    transition:all .2s ease;
}
.va-boost-btn__dot {
    width:7px;
    height:7px;
    border-radius:50%;
    background:currentColor;
    box-shadow:0 0 8px currentColor;
}
.va-boost-btn--on {
    color:#00d46a;
    border-color:#00b85c;
    background:linear-gradient(135deg,rgba(0,212,106,.18),rgba(0,120,64,.14));
}
.va-boost-btn--off {
    color:#ffcc00;
    border-color:#ffcc00;
    background:linear-gradient(135deg,rgba(255,204,0,.18),rgba(160,110,0,.14));
}
.va-boost-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }
.va-boost-btn:disabled { opacity:.65;cursor:not-allowed;transform:none; }

.va-newpill-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    min-width:106px;
    height:30px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid transparent;
    font-size:11px;
    font-weight:700;
    line-height:1;
    letter-spacing:.02em;
    white-space:nowrap;
    cursor:pointer;
    transition:all .2s ease;
}
.va-newpill-btn__dot {
    width:7px;
    height:7px;
    border-radius:50%;
    background:currentColor;
    box-shadow:0 0 8px currentColor;
}
.va-newpill-btn--on {
    color:#ffffff;
    border-color:#ff2a2a;
    background:linear-gradient(135deg,rgba(255,42,42,.9),rgba(180,0,0,.9));
}
.va-newpill-btn--off {
    color:#ff8a8a;
    border-color:#ff3a3a;
    background:linear-gradient(135deg,rgba(255,42,42,.18),rgba(110,0,0,.16));
}
.va-newpill-btn:hover { filter:brightness(1.06); transform:translateY(-1px); }
.va-newpill-btn:disabled { opacity:.65;cursor:not-allowed;transform:none; }

/* ── Veszélyes zóna ── */
.va-danger-zone {
    padding:20px;
    border:1px solid rgba(255,42,42,.25);
    border-radius:10px;
    background:rgba(255,42,42,.05);
}
.va-danger-zone__desc  { font-size:13px;color:rgba(255,255,255,.65);margin:0 0 8px; }
.va-danger-zone__input { max-width:200px;border-color:rgba(255,42,42,.4) !important; }
.va-danger-zone__submit {
    background:rgba(255,42,42,.18);
    border:1px solid rgba(255,42,42,.5);
    color:#ff6060;
    white-space:nowrap;
    padding:0 16px;
    height:40px;
    border-radius:8px;
    font-weight:700;
    cursor:pointer;
    transition:.2s ease;
}
.va-danger-zone__submit:hover { background:rgba(255,42,42,.32); }
.va-dashboard__nav-item--danger { color:#ff6060 !important; }
.va-dashboard__nav-item--danger .va-dashboard__nav-ico svg { stroke:#ff6060; }
.va-dashboard__nav-item--danger:hover,
.va-dashboard__nav-item--danger.active { background:rgba(255,42,42,.1) !important; }

/* ── Sort bar ── */
.va-sort-bar {
    display:flex;align-items:center;gap:6px;margin-bottom:10px;flex-wrap:wrap;
}
.va-sort-btn {
    display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:999px;
    border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);
    font-size:11px;font-weight:600;text-decoration:none;transition:.15s ease;
}
.va-sort-btn:hover { border-color:rgba(255,0,0,.4);color:#fff; }
.va-sort-btn.active { border-color:rgba(255,0,0,.5);background:rgba(255,0,0,.12);color:#fff; }
.va-sort-btn .va-ico { width:13px !important;height:13px !important; }

/* ── Bulk toolbar ── */
.va-bulk-toolbar {
    display:flex;align-items:center;gap:8px;flex-wrap:wrap;
    margin-bottom:10px;padding:8px 12px;
    border:1px solid rgba(255,255,255,.08);border-radius:10px;
    background:rgba(255,255,255,.03);
}
.va-bulk-select-all-label { font-size:12px;color:#fff;font-weight:600;display:flex;align-items:center;gap:6px;cursor:pointer; }

/* ── Custom bulk dropdown ── */
.va-bulk-dropdown { position:relative; }
.va-bulk-dropdown__toggle {
    display:flex;align-items:center;gap:8px;
    height:30px;padding:0 10px;border-radius:8px;
    border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.07);
    color:#fff;font-size:12px;cursor:pointer;min-width:180px;white-space:nowrap;
    transition:border-color .15s,background .15s;
}
.va-bulk-dropdown__toggle:hover,
.va-bulk-dropdown.open .va-bulk-dropdown__toggle {
    border-color:rgba(255,42,42,.5);background:rgba(255,42,42,.1);
}
.va-bulk-dropdown__toggle span { flex:1;text-align:left; }
.va-bulk-dropdown__toggle svg { flex-shrink:0;transition:transform .2s; }
.va-bulk-dropdown.open .va-bulk-dropdown__toggle svg { transform:rotate(180deg); }
.va-bulk-dropdown__menu {
    position:absolute;top:calc(100% + 4px);left:0;z-index:900;
    min-width:100%;border-radius:10px;
    border:1px solid rgba(255,255,255,.12);
    background:rgb(14,14,14);
    box-shadow:0 12px 40px rgba(0,0,0,.7);
    overflow:hidden;
}
.va-bulk-dropdown__item {
    display:flex;align-items:center;gap:8px;width:100%;text-align:left;
    padding:9px 14px;font-size:13px;color:#fff;
    background:none;border:none;cursor:pointer;
    transition:background .12s;white-space:nowrap;
}
.va-bulk-dropdown__item:hover { background:rgba(255,42,42,.15);color:#fff; }
.va-bulk-dropdown__item.selected { color:#ff4444;background:rgba(255,0,0,.08); }
.va-bulk-dropdown__item .va-ico { width:14px !important;height:14px !important;opacity:.95; }

/* ── Bulk price panel ── */
.va-bulk-price-panel {
    margin-bottom:12px;padding:14px;border:1px solid rgba(255,200,0,.2);border-radius:10px;
    background:rgba(255,200,0,.04);
}
.va-bulk-price-label { display:block;font-size:11px;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:4px; }
.va-bulk-price-input {
    height:32px;padding:0 10px;border-radius:8px;border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.07);color:#fff;font-size:13px;min-width:140px;
}
.va-bulk-price-input:focus { outline:none;border-color:#ff2a2a; }

/* ── Akciós ár inline edit gomb ── */
.va-sale-edit-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:30px;height:30px;margin-left:10px;margin-top:4px;
    border-radius:6px;border:1px solid rgba(255,255,255,.25);
    background:rgba(255,255,255,.06);color:#fff;
    cursor:pointer;font-size:14px;line-height:1;vertical-align:middle;
    transition:.15s;border-color:.15s,background:.15s,transform:.15s;
}
.va-sale-edit-btn:hover { transform:translateY(-1px); }
.va-sale-edit-btn .va-ico { width:14px !important;height:14px !important; }
.va-user-listings-table td .va-sale-edit-btn { flex-shrink:0; }
.va-sale-edit-btn--active {
    border-color:rgba(255,0,0,.55);
    background:rgba(255,0,0,.18);
    color:#ff7777;
}
.va-sale-edit-btn--idle {
    border-color:rgba(255,255,255,.35);
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.95);
}

/* ── Akciós ár középre popup (blur háttér) ── */
.va-sale-modal-overlay {
    display:none;
    position:fixed;
    inset:0;
    z-index:9999;
    align-items:center;
    justify-content:center;
    background:rgba(0,0,0,.45);
    backdrop-filter:blur(8px);
}
.va-sale-modal-overlay.open { display:flex; }
.va-sale-modal {
    width:min(92vw, 430px);
    border:1px solid rgba(255,255,255,.14);
    border-radius:16px;
    padding:18px;
    background:linear-gradient(180deg, rgba(18,18,18,.98), rgba(8,8,8,.98));
    box-shadow:0 22px 60px rgba(0,0,0,.65), 0 0 0 1px rgba(255,0,0,.12) inset;
    color-scheme:dark;
}
.va-sale-modal h3 { margin:0 0 12px; font-size:16px; color:#fff; }
.va-sale-modal label { display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,.55); margin:10px 0 4px; }
.va-sale-modal input {
    width:100%;
    height:36px;
    padding:0 12px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.16);
    background:rgba(255,255,255,.06);
    color:#fff;
    font-size:13px;
    box-sizing:border-box;
}
.va-sale-modal input[type="number"] {
    -moz-appearance:textfield;
}
.va-sale-modal input[type="number"]::-webkit-outer-spin-button,
.va-sale-modal input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance:none;
    margin:0;
}
.va-sale-modal input:focus {
    outline:none;
    border-color:rgba(255,0,0,.55);
    box-shadow:0 0 0 3px rgba(255,0,0,.14);
}
.va-sale-modal__date-input {
    padding-right:42px !important;
}
.va-sale-modal__date-wrap {
    position:relative;
}
.va-sale-modal__date-btn {
    position:absolute;
    right:6px;
    top:50%;
    transform:translateY(-50%);
    width:30px;
    height:30px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.2);
    background:rgba(255,255,255,.05);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
}
.va-sale-modal__date-btn:hover {
    border-color:rgba(255,0,0,.45);
    background:rgba(255,0,0,.16);
}
.va-sale-calendar {
    position:absolute;
    top:calc(100% + 8px);
    left:0;
    width:100%;
    border:1px solid rgba(255,255,255,.14);
    border-radius:12px;
    background:linear-gradient(180deg,#111,#080808);
    box-shadow:0 18px 40px rgba(0,0,0,.7);
    z-index:10020;
    padding:10px;
}
.va-sale-calendar__head {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:8px;
}
.va-sale-calendar__nav {
    width:28px;
    height:28px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.18);
    background:rgba(255,255,255,.04);
    color:#fff;
    cursor:pointer;
    font-size:18px;
    line-height:1;
}
.va-sale-calendar__title {
    font-size:13px;
    font-weight:700;
    color:#fff;
}
.va-sale-calendar__dow,
.va-sale-calendar__grid {
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:4px;
}
.va-sale-calendar__dow span {
    text-align:center;
    font-size:10px;
    color:rgba(255,255,255,.45);
    padding:2px 0;
}
.va-sale-calendar__day {
    height:28px;
    border-radius:7px;
    border:1px solid transparent;
    background:transparent;
    color:#fff;
    font-size:12px;
    cursor:pointer;
}
.va-sale-calendar__day:hover {
    border-color:rgba(255,0,0,.35);
    background:rgba(255,0,0,.14);
}
.va-sale-calendar__day--muted {
    color:rgba(255,255,255,.28);
}
.va-sale-calendar__day--selected {
    border-color:rgba(255,0,0,.55);
    background:rgba(255,0,0,.22);
}
.va-sale-calendar__day--today {
    box-shadow:0 0 0 1px rgba(255,255,255,.35) inset;
}
.va-sale-preview {
    margin-top:10px;
    padding:10px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.03);
}
.va-sale-preview__label {
    font-size:10px;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
    color:rgba(255,255,255,.55);
    margin-bottom:4px;
}
.va-sale-preview__prices {
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:wrap;
}
.va-sale-preview__old {
    color:rgba(255,255,255,.45);
    text-decoration:line-through;
    font-size:12px;
}
.va-sale-preview__new {
    color:#fff;
    font-size:14px;
    font-weight:800;
}
.va-sale-preview__badge {
    font-size:10px;
    font-weight:800;
    letter-spacing:.05em;
    padding:1px 5px;
    border-radius:4px;
    border:1px solid rgba(255,0,0,.45);
    background:rgba(255,0,0,.2);
    color:#ff8e8e;
}
.va-sale-preview__meta {
    margin-top:4px;
    font-size:11px;
    color:rgba(255,255,255,.45);
}
.va-sale-modal__actions {
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:14px;
}
.va-sale-modal__save {
    margin-left:auto;
    height:36px;
    padding:0 16px;
    border-radius:8px;
    border:1px solid rgba(255,0,0,.55);
    background:linear-gradient(180deg, rgba(255,34,34,.35), rgba(170,0,0,.35));
    color:#fff;
    font-weight:700;
    cursor:pointer;
}
.va-sale-modal__remove {
    height:36px;
    padding:0 14px;
    border-radius:8px;
    border:1px solid rgba(255,42,42,.35);
    background:rgba(255,42,42,.08);
    color:#ff8b8b;
    cursor:pointer;
}
.va-sale-modal__cancel {
    height:36px;
    padding:0 14px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.16);
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.75);
    cursor:pointer;
}
.va-sale-modal__save:hover,
.va-sale-modal__remove:hover,
.va-sale-modal__cancel:hover {
    filter:brightness(1.15);
}

/* ── Lejárat badge ── */
.va-expiry-badge {
    display:inline-flex;align-items:center;gap:3px;padding:1px 6px;border-radius:999px;
    font-size:10px;font-weight:700;
}
.va-expiry-badge--warn { background:rgba(255,153,0,.15);border:1px solid rgba(255,153,0,.4);color:#ffb400; }
.va-expiry-badge--critical { background:rgba(255,42,42,.18);border:1px solid rgba(255,42,42,.5);color:#ff5555; }

/* ── Képszám badge ── */
.va-img-count-badge {
    display:inline-flex;align-items:center;gap:3px;padding:1px 6px;border-radius:999px;
    font-size:10px;font-weight:700;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.55);
}
.va-img-count-badge .va-ico { width:11px !important;height:11px !important; }

/* ── Profil completeness ── */
.va-profile-completeness {
    margin-bottom:20px;padding:14px;border:1px solid rgba(255,255,255,.08);
    border-radius:12px;background:rgba(255,255,255,.03);
}
.va-profile-completeness__head { display:flex;justify-content:space-between;margin-bottom:8px; }
.va-profile-completeness__label { font-size:12px;font-weight:700;color:#fff; }
.va-profile-completeness__pct { font-size:12px;font-weight:800;color:#fff; }
.va-profile-completeness__bar {
    height:5px;border-radius:999px;background:rgba(255,255,255,.1);overflow:hidden;margin-bottom:10px;
}
.va-profile-completeness__bar > div { height:100%;border-radius:inherit;transition:width .4s; }
.va-profile-completeness__items { display:flex;flex-wrap:wrap;gap:6px; }
.va-profile-completeness__item {
    font-size:11px;color:rgba(255,255,255,.4);padding:2px 8px;border-radius:999px;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
}
.va-profile-completeness__item.done { color:#00c850;border-color:rgba(0,200,80,.3);background:rgba(0,200,80,.07); }

/* ── Trust badge-ek ── */
.va-trust-badges { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px; }
.va-trust-badge {
    display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.35);font-size:11px;font-weight:600;
}
.va-trust-badge svg { width:14px;height:14px; }
.va-trust-badge.active { color:#fff;border-color:rgba(0,200,80,.4);background:rgba(0,200,80,.09); }
.va-trust-badge.active svg { stroke:#00c850; }

/* Emergency nav-scroll fix (cache- and load-order safe) */
.va-dashboard__nav {
    scrollbar-width: thin !important;
    scrollbar-color: rgba(255,0,0,.92) rgba(255,255,255,.16) !important;
}
.va-dashboard__nav::-webkit-scrollbar { width: 5px !important; }
.va-dashboard__nav::-webkit-scrollbar-track {
    background: rgba(255,255,255,.16) !important;
    border-radius: 999px !important;
}
.va-dashboard__nav::-webkit-scrollbar-thumb {
    background: rgba(255,0,0,.92) !important;
    border-radius: 999px !important;
    border: 1px solid rgba(255,255,255,.16) !important;
}
.va-dashboard__nav::-webkit-scrollbar-thumb:hover,
.va-dashboard__nav::-webkit-scrollbar-thumb:active { background: rgba(255,0,0,1) !important; }

.va-dashboard__nav.va-nav-inline-os {
    scrollbar-width: none !important;
    scrollbar-color: transparent transparent !important;
    --os-size: 8px;
    --os-padding-perpendicular: 2px;
    --os-padding-axis: 1px;
    --os-track-bg: rgba(255,255,255,.14);
    --os-track-bg-hover: rgba(255,255,255,.18);
    --os-handle-bg: rgba(255,0,0,.92);
    --os-handle-bg-hover: rgba(255,0,0,.96);
    --os-handle-bg-active: rgba(255,0,0,1);
    --os-handle-border-radius: 999px;
    --os-handle-perpendicular-size: 5px;
    --os-handle-perpendicular-size-hover: 5px;
    --os-handle-perpendicular-size-active: 5px;
    --os-handle-min-size: 36px;
}
.va-dashboard__nav.va-nav-inline-os .os-scrollbar-vertical {
    width: 8px !important;
    opacity: 1 !important;
    visibility: visible !important;
}
.va-dashboard__nav.va-nav-inline-os .os-scrollbar-vertical .os-scrollbar-handle {
    width: 5px !important;
    background: rgba(255,0,0,.92) !important;
    border-radius: 999px !important;
}
</style>

<script>
(function(){
    document.querySelectorAll('.va-boost-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId   = this.dataset.postId;
            var nonce    = this.dataset.nonce;
            var ajaxUrl  = this.dataset.ajaxUrl;
            var mode     = this.dataset.mode || 'toggle';
            var self     = this;
            var originalText = self.textContent;

            self.disabled = true;
            self.textContent = 'Mentés...';

            var data = new URLSearchParams({
                action  : 'va_boost_listing',
                nonce   : nonce,
                post_id : postId,
                mode    : mode
            });

            fetch(ajaxUrl, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : data.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if(res.success){
                    self.disabled = false;
                    if (res.data && res.data.removed) {
                        self.dataset.mode = 'boost';
                        self.setAttribute('aria-pressed', 'false');
                        self.classList.remove('va-boost-btn--on');
                        self.classList.add('va-boost-btn--off');
                        self.innerHTML = '<span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelés: KI';
                    } else {
                        self.dataset.mode = 'remove';
                        self.setAttribute('aria-pressed', 'true');
                        self.classList.remove('va-boost-btn--off');
                        self.classList.add('va-boost-btn--on');
                        self.innerHTML = '<span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelt: BE';
                    }
                } else {
                    self.disabled    = false;
                    self.textContent = originalText;
                    alert(res.data && res.data.message ? res.data.message : 'Hiba történt.');
                }
            })
            .catch(function(){
                self.disabled    = false;
                self.textContent = originalText;
            });
        });
    });

    document.querySelectorAll('.va-newpill-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId   = this.dataset.postId;
            var nonce    = this.dataset.nonce;
            var ajaxUrl  = this.dataset.ajaxUrl;
            var mode     = this.dataset.mode || 'toggle';
            var self     = this;
            var originalText = self.textContent;

            self.disabled = true;
            self.textContent = 'Mentés...';

            var data = new URLSearchParams({
                action  : 'va_toggle_new_pill',
                nonce   : nonce,
                post_id : postId,
                mode    : mode
            });

            fetch(ajaxUrl, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : data.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if(res.success){
                    self.disabled = false;
                    if (res.data && res.data.active) {
                        self.dataset.mode = 'off';
                        self.setAttribute('aria-pressed', 'true');
                        self.classList.remove('va-newpill-btn--off');
                        self.classList.add('va-newpill-btn--on');
                        self.innerHTML = '<span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: BE';
                    } else {
                        self.dataset.mode = 'on';
                        self.setAttribute('aria-pressed', 'false');
                        self.classList.remove('va-newpill-btn--on');
                        self.classList.add('va-newpill-btn--off');
                        self.innerHTML = '<span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: KI';
                    }
                } else {
                    self.disabled = false;
                    self.textContent = originalText;
                    alert(res.data && res.data.message ? res.data.message : 'Hiba történt.');
                }
            })
            .catch(function(){
                self.disabled = false;
                self.textContent = originalText;
            });
        });
    });

    // Avatar upload zone
    var avatarInput = document.getElementById('va-avatar-input');
    var avatarDrop  = document.getElementById('va-avatar-drop');
    var avatarLabel = document.getElementById('va-avatar-label');
    if (avatarInput && avatarDrop) {
        avatarInput.addEventListener('change', function(){
            var name = this.files && this.files[0] ? this.files[0].name : 'Kép kiválasztása';
            avatarLabel.textContent = name.length > 20 ? name.substring(0,18)+'…' : name;
        });
        avatarDrop.addEventListener('dragover', function(e){ e.preventDefault(); this.classList.add('drag-over'); });
        avatarDrop.addEventListener('dragleave', function(){ this.classList.remove('drag-over'); });
        avatarDrop.addEventListener('drop', function(e){
            e.preventDefault(); this.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                avatarInput.files = e.dataTransfer.files;
                var name = e.dataTransfer.files[0].name;
                avatarLabel.textContent = name.length > 20 ? name.substring(0,18)+'…' : name;
            }
        });
    }

    var _nonce  = '<?php echo esc_js( $boost_nonce ); ?>';
    var _ajaxUrl= '<?php echo esc_js( $ajax_url ); ?>';

    <?php if ( $va_show_daily_welcome ) : ?>
    (function(){
        var overlay = document.getElementById('va-welcome-overlay');
        var closeBtn = document.getElementById('va-welcome-close');
        var titleEl = document.getElementById('va-welcome-title');
        var heroImg = document.getElementById('va-welcome-hero-img');
        if (!overlay || !closeBtn || !titleEl) return;

        if (heroImg) {
            heroImg.addEventListener('error', function(){
                var fb = heroImg.getAttribute('data-fallback');
                if (fb && heroImg.src !== fb) {
                    heroImg.src = fb;
                }
            });
        }

        var welcomeMessage = <?php echo wp_json_encode( (string) $va_daily_welcome_message ); ?>;
        titleEl.textContent = welcomeMessage || 'Ma is jo, hogy itt vagy.';

        function closeWelcome() { overlay.classList.remove('open'); }
        overlay.classList.add('open');
        closeBtn.addEventListener('click', closeWelcome);
        overlay.addEventListener('click', function(e){ if (e.target === overlay) closeWelcome(); });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeWelcome(); });
    })();
    <?php endif; ?>

    function normalizeIsoDateText(raw) {
        var v = (raw || '').trim();
        if (!v) return '';
        v = v.replace(/\./g, '-').replace(/\//g, '-');
        if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return null;
        return v;
    }

    /* ── Select all checkbox ── */
    var selectAll = document.getElementById('va-select-all');
    var bulkCount = document.getElementById('va-bulk-count');
    function updateBulkCount() {
        var checked = document.querySelectorAll('.va-row-check:checked').length;
        if (bulkCount) bulkCount.textContent = checked > 0 ? checked + ' kijelölve' : '';
    }
    if (selectAll) {
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.va-row-check').forEach(function(cb){ cb.checked = selectAll.checked; });
            updateBulkCount();
        });
    }
    document.querySelectorAll('.va-row-check').forEach(function(cb){
        cb.addEventListener('change', updateBulkCount);
    });

    /* ── Custom bulk dropdown ── */
    var bulkDropdown     = document.getElementById('va-bulk-dropdown');
    var bulkDropdownMenu = document.getElementById('va-bulk-dropdown-menu');
    var bulkDropdownToggle = document.getElementById('va-bulk-dropdown-toggle');
    var bulkDropdownLabel= document.getElementById('va-bulk-dropdown-label');
    var bulkActionInput  = document.getElementById('va-bulk-action');

    function closeBulkDropdown() {
        if (bulkDropdown) bulkDropdown.classList.remove('open');
        if (bulkDropdownMenu) bulkDropdownMenu.style.display = 'none';
    }

    if (bulkDropdownToggle) {
        bulkDropdownToggle.addEventListener('click', function(e){
            e.stopPropagation();
            var isOpen = bulkDropdown && bulkDropdown.classList.contains('open');
            if (isOpen) {
                closeBulkDropdown();
            } else {
                if (bulkDropdown) bulkDropdown.classList.add('open');
                if (bulkDropdownMenu) bulkDropdownMenu.style.display = 'block';
            }
        });
    }

    document.addEventListener('click', function(e){
        if (bulkDropdown && !bulkDropdown.contains(e.target)) closeBulkDropdown();
    });

    if (bulkDropdownMenu) {
        bulkDropdownMenu.querySelectorAll('.va-bulk-dropdown__item').forEach(function(item){
            item.addEventListener('click', function(){
                var val   = this.dataset.value || '';
                var label = this.dataset.label || this.textContent;
                if (bulkActionInput) bulkActionInput.value = val;
                if (bulkDropdownLabel) bulkDropdownLabel.textContent = label;
                bulkDropdownMenu.querySelectorAll('.va-bulk-dropdown__item').forEach(function(i){ i.classList.remove('selected'); });
                this.classList.add('selected');
                closeBulkDropdown();
            });
        });
    }

    /* ── Bulk exec ── */
    var bulkExecBtn = document.getElementById('va-bulk-exec');
    if (bulkExecBtn) {
        bulkExecBtn.addEventListener('click', function(){
            var action = bulkActionInput ? bulkActionInput.value : '';
            if (!action) { alert('Válassz műveletet!'); return; }
            var ids = Array.from(document.querySelectorAll('.va-row-check:checked')).map(function(cb){ return cb.value; });
            if (!ids.length) { alert('Jelölj ki legalább egy hirdetést!'); return; }
            if (action === 'delete' && !confirm('Biztosan törlöd a kijelölt ' + ids.length + ' hirdetést?')) return;

            var params = new URLSearchParams({
                action: 'va_bulk_listings',
                nonce: _nonce,
                bulk_action: action
            });
            ids.forEach(function(id){ params.append('listing_ids[]', id); });

            bulkExecBtn.disabled = true;
            bulkExecBtn.textContent = 'Folyamatban...';
            fetch(_ajaxUrl, {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body: params.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    alert(res.data.updated + ' hirdetés módosítva.');
                    location.reload();
                } else {
                    alert((res.data && res.data.message) || 'Hiba történt.');
                    bulkExecBtn.disabled = false;
                    bulkExecBtn.textContent = 'Végrehajtás';
                }
            })
            .catch(function(){
                bulkExecBtn.disabled = false;
                bulkExecBtn.textContent = 'Végrehajtás';
            });
        });
    }

    /* ── Refresh listing ── */
    document.querySelectorAll('.va-refresh-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId = this.dataset.postId;
            var self = this;
            self.disabled = true;
            self.textContent = '…';
            var params = new URLSearchParams({ action:'va_refresh_listing', nonce:_nonce, post_id:postId });
            fetch(_ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:params.toString() })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    self.textContent = 'Frissítve';
                    setTimeout(function(){ self.innerHTML = '<svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.5 14a8.5 8.5 0 1 1 2-5.5"/></svg> Frissítés'; self.disabled = false; }, 2000);
                } else {
                    alert((res.data && res.data.message) || 'Hiba.');
                    self.disabled = false; self.innerHTML = '<svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.5 14a8.5 8.5 0 1 1 2-5.5"/></svg> Frissítés';
                }
            })
            .catch(function(){ self.disabled = false; self.innerHTML = '<svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.5 14a8.5 8.5 0 1 1 2-5.5"/></svg> Frissítés'; });
        });
    });

    /* ── Sale price modal edit ── */
    var saleOverlay = document.getElementById('va-sale-modal-overlay');
    var salePostId  = document.getElementById('va-sale-modal-post-id');
    var saleNormal  = document.getElementById('va-sale-modal-normal-price');
    var salePrice   = document.getElementById('va-sale-modal-price');
    var saleEnd     = document.getElementById('va-sale-modal-end');
    var saleCalBtn  = document.getElementById('va-sale-modal-cal-btn');
    var saleCalBox  = document.getElementById('va-sale-calendar');
    var saleCalGrid = document.getElementById('va-sale-cal-grid');
    var saleCalTitle= document.getElementById('va-sale-cal-title');
    var saleCalPrev = document.getElementById('va-sale-cal-prev');
    var saleCalNext = document.getElementById('va-sale-cal-next');
    var salePreviewOld   = document.getElementById('va-sale-preview-old');
    var salePreviewNew   = document.getElementById('va-sale-preview-new');
    var salePreviewBadge = document.getElementById('va-sale-preview-badge');
    var salePreviewMeta  = document.getElementById('va-sale-preview-meta');
    var saleSaveBtn = document.getElementById('va-sale-modal-save');
    var saleRemBtn  = document.getElementById('va-sale-modal-remove');
    var saleCancel  = document.getElementById('va-sale-modal-cancel');
    var saleCalViewDate = null;

    function parseIsoDate(iso) {
        if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return null;
        var p = iso.split('-').map(function(x){ return parseInt(x, 10); });
        var d = new Date(p[0], p[1]-1, p[2]);
        if (d.getFullYear() !== p[0] || d.getMonth() !== (p[1]-1) || d.getDate() !== p[2]) return null;
        return d;
    }

    function toIsoDate(d) {
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function openSaleCalendar() {
        if (!saleCalBox) return;
        saleCalBox.style.display = 'block';
    }

    function closeSaleCalendar() {
        if (!saleCalBox) return;
        saleCalBox.style.display = 'none';
    }

    function renderSaleCalendar(viewDate) {
        if (!saleCalGrid || !saleCalTitle) return;
        var months = ['január','február','március','április','május','június','július','augusztus','szeptember','október','november','december'];
        var year = viewDate.getFullYear();
        var month = viewDate.getMonth();
        saleCalTitle.textContent = year + '. ' + months[month];

        var first = new Date(year, month, 1);
        var offset = (first.getDay() + 6) % 7; // Hétfő = 0
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var prevMonthDays = new Date(year, month, 0).getDate();
        var selected = parseIsoDate(saleEnd ? saleEnd.value : '');
        var today = new Date();
        today.setHours(0,0,0,0);

        saleCalGrid.innerHTML = '';
        for (var i = 0; i < 42; i++) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'va-sale-calendar__day';
            var dateObj;
            if (i < offset) {
                var d1 = prevMonthDays - offset + i + 1;
                dateObj = new Date(year, month - 1, d1);
                btn.classList.add('va-sale-calendar__day--muted');
                btn.textContent = d1;
            } else if (i >= offset + daysInMonth) {
                var d2 = i - (offset + daysInMonth) + 1;
                dateObj = new Date(year, month + 1, d2);
                btn.classList.add('va-sale-calendar__day--muted');
                btn.textContent = d2;
            } else {
                var d = i - offset + 1;
                dateObj = new Date(year, month, d);
                btn.textContent = d;
            }
            var iso = toIsoDate(dateObj);
            btn.dataset.iso = iso;
            if (selected && toIsoDate(selected) === iso) btn.classList.add('va-sale-calendar__day--selected');
            if (toIsoDate(today) === iso) btn.classList.add('va-sale-calendar__day--today');
            btn.addEventListener('click', function(){
                if (!saleEnd) return;
                saleEnd.value = this.dataset.iso || '';
                refreshSalePreview();
                closeSaleCalendar();
            });
            saleCalGrid.appendChild(btn);
        }
    }

    function fmtFt(value) {
        var n = parseFloat(value || 0);
        if (isNaN(n) || n <= 0) return '-';
        return n.toLocaleString('hu-HU') + ' Ft';
    }

    function refreshSalePreview() {
        if (!salePreviewNew) return;
        var normal = parseFloat(saleNormal && saleNormal.value ? saleNormal.value : 0);
        var sale   = parseFloat(salePrice && salePrice.value ? salePrice.value : 0);
        var endVal = saleEnd ? saleEnd.value : '';
        var hasSale = !isNaN(sale) && sale > 0;

        if (hasSale) {
            if (salePreviewOld) salePreviewOld.textContent = fmtFt(normal);
            if (salePreviewNew) salePreviewNew.textContent = fmtFt(sale);
            if (salePreviewBadge) salePreviewBadge.style.display = 'inline-flex';
            if (salePreviewMeta) salePreviewMeta.textContent = endVal ? ('Akció vége: ' + endVal) : 'Akció vége nincs beállítva';
        } else {
            if (salePreviewOld) salePreviewOld.textContent = '';
            if (salePreviewNew) salePreviewNew.textContent = fmtFt(normal);
            if (salePreviewBadge) salePreviewBadge.style.display = 'none';
            if (salePreviewMeta) salePreviewMeta.textContent = 'Nincs aktív akciós ár';
        }
    }

    function openSaleModal(postId, curNormal, curSale, curEnd) {
        if (!saleOverlay) return;
        salePostId.value = postId || '';
        saleNormal.value = curNormal || '';
        salePrice.value  = curSale || '';
        saleEnd.value    = curEnd || '';
        saleCalViewDate  = parseIsoDate(saleEnd.value) || new Date();
        renderSaleCalendar(saleCalViewDate);
        closeSaleCalendar();
        refreshSalePreview();
        saleOverlay.classList.add('open');
        if (saleNormal) saleNormal.focus();
    }
    function closeSaleModal() {
        if (saleOverlay) saleOverlay.classList.remove('open');
        closeSaleCalendar();
        if (saleSaveBtn) { saleSaveBtn.disabled = false; saleSaveBtn.textContent = 'Mentés'; }
    }

    function saveSalePrice(postId, normalPrice, salePriceValue, endDate) {
        if (!postId) return;
        var normalPriceNum = parseFloat(normalPrice || 0);
        if (isNaN(normalPriceNum) || normalPriceNum < 0) {
            alert('Normál ár nem lehet negatív.');
            return;
        }
        var saleEndNorm = normalizeIsoDateText(endDate);
        if (saleEndNorm === null) { alert('Akció vége dátum formátum: YYYY-MM-DD'); return; }
        var params = new URLSearchParams({
            action: 'va_set_sale_price', nonce: _nonce,
            post_id: postId,
            normal_price: normalPriceNum,
            sale_price: salePriceValue,
            sale_end: saleEndNorm || ''
        });
        if (saleSaveBtn) { saleSaveBtn.disabled = true; saleSaveBtn.textContent = 'Mentés...'; }
        fetch(_ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:params.toString() })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) {
                closeSaleModal();
                location.reload();
            } else {
                alert((res.data && res.data.message) || 'Hiba.');
                if (saleSaveBtn) { saleSaveBtn.disabled = false; saleSaveBtn.textContent = 'Mentés'; }
            }
        })
        .catch(function(){
            if (saleSaveBtn) { saleSaveBtn.disabled = false; saleSaveBtn.textContent = 'Mentés'; }
        });
    }

    document.querySelectorAll('.va-sale-edit-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            openSaleModal(this.dataset.postId, this.dataset.normalPrice, this.dataset.salePrice, this.dataset.saleEnd);
        });
    });

    if (saleCancel) saleCancel.addEventListener('click', closeSaleModal);
    if (saleOverlay) {
        saleOverlay.addEventListener('click', function(e){
            if (e.target === saleOverlay) closeSaleModal();
        });
    }
    if (saleCalBtn) {
        saleCalBtn.addEventListener('click', function(e){
            e.stopPropagation();
            if (!saleCalViewDate) saleCalViewDate = parseIsoDate(saleEnd ? saleEnd.value : '') || new Date();
            renderSaleCalendar(saleCalViewDate);
            if (saleCalBox && saleCalBox.style.display === 'none') openSaleCalendar(); else closeSaleCalendar();
        });
    }
    if (saleEnd) {
        saleEnd.addEventListener('click', function(e){
            e.stopPropagation();
            if (!saleCalViewDate) saleCalViewDate = parseIsoDate(saleEnd.value) || new Date();
            renderSaleCalendar(saleCalViewDate);
            openSaleCalendar();
        });
    }
    if (saleCalPrev) {
        saleCalPrev.addEventListener('click', function(e){
            e.stopPropagation();
            if (!saleCalViewDate) saleCalViewDate = new Date();
            saleCalViewDate = new Date(saleCalViewDate.getFullYear(), saleCalViewDate.getMonth() - 1, 1);
            renderSaleCalendar(saleCalViewDate);
            openSaleCalendar();
        });
    }
    if (saleCalNext) {
        saleCalNext.addEventListener('click', function(e){
            e.stopPropagation();
            if (!saleCalViewDate) saleCalViewDate = new Date();
            saleCalViewDate = new Date(saleCalViewDate.getFullYear(), saleCalViewDate.getMonth() + 1, 1);
            renderSaleCalendar(saleCalViewDate);
            openSaleCalendar();
        });
    }
    document.addEventListener('click', function(e){
        if (!saleOverlay || !saleOverlay.classList.contains('open')) return;
        if (!saleCalBox || saleCalBox.style.display === 'none') return;
        if (saleCalBox.contains(e.target)) return;
        if (saleCalBtn && saleCalBtn.contains(e.target)) return;
        if (saleEnd && saleEnd.contains(e.target)) return;
        closeSaleCalendar();
    });
    if (saleSaveBtn) {
        saleSaveBtn.addEventListener('click', function(){
            saveSalePrice(
                salePostId ? salePostId.value : '',
                saleNormal ? saleNormal.value : 0,
                salePrice ? salePrice.value : 0,
                saleEnd ? saleEnd.value : ''
            );
        });
    }
    if (saleRemBtn) {
        saleRemBtn.addEventListener('click', function(){
            if (!confirm('Törlöd az akciós árat?')) return;
            saveSalePrice(
                salePostId ? salePostId.value : '',
                saleNormal ? saleNormal.value : 0,
                0,
                ''
            );
        });
    }
    if (saleNormal) saleNormal.addEventListener('input', refreshSalePreview);
    if (salePrice) salePrice.addEventListener('input', refreshSalePreview);
    if (saleEnd) saleEnd.addEventListener('change', refreshSalePreview);

    function vaForceDashboardNavScrollbar() {
        var nav = document.querySelector('.va-dashboard__nav');
        if (!nav) return;

        if (typeof OverlayScrollbars !== 'undefined') {
            var existing = OverlayScrollbars(nav);
            if (existing && typeof existing.destroy === 'function') {
                existing.destroy();
            }
            OverlayScrollbars(nav, {
                scrollbars: { autoHide: 'never', clickScroll: false },
                overflow: { x: 'hidden', y: 'scroll' }
            });
            nav.classList.add('va-nav-inline-os');
        } else {
            nav.classList.remove('va-nav-inline-os');
        }
    }

    vaForceDashboardNavScrollbar();
    window.addEventListener('load', vaForceDashboardNavScrollbar);

    var vaNavForceRuns = 0;
    var vaNavForceTimer = setInterval(function() {
        vaForceDashboardNavScrollbar();
        vaNavForceRuns += 1;
        if (vaNavForceRuns >= 20) {
            clearInterval(vaNavForceTimer);
        }
    }, 250);

})();
</script>

<script>
(function(){
    function vaHardForceNavScrollbar() {
        var nav = document.querySelector('.va-dashboard__nav');
        if (!nav) return;

        if (typeof OverlayScrollbars !== 'undefined') {
            try {
                var existing = OverlayScrollbars(nav);
                if (existing && typeof existing.destroy === 'function') {
                    existing.destroy();
                }
            } catch (e) {}

            try {
                OverlayScrollbars(nav, {
                    scrollbars: { autoHide: 'never', clickScroll: false },
                    overflow: { x: 'hidden', y: 'scroll' }
                });
                nav.classList.add('va-nav-inline-os');
            } catch (e) {}
        }
    }

    vaHardForceNavScrollbar();
    document.addEventListener('DOMContentLoaded', vaHardForceNavScrollbar);
    window.addEventListener('load', vaHardForceNavScrollbar);

    var runs = 0;
    var t = setInterval(function(){
        vaHardForceNavScrollbar();
        runs++;
        if (runs >= 24) clearInterval(t);
    }, 250);
})();
</script>
