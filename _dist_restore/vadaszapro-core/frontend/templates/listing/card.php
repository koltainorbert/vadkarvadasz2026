<?php
/**
 * Template: Hirdetés kártya (lista elem)
 * Változók: $post (WP_Post)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id   = $post->ID;
$price     = get_post_meta( $post_id, 'va_price', true );
$price_type= get_post_meta( $post_id, 'va_price_type', true ) ?: 'fixed';
$sale_price = get_post_meta( $post_id, 'va_sale_price', true );
$sale_end   = get_post_meta( $post_id, 'va_sale_price_end', true );
$has_sale   = $sale_price && floatval( $sale_price ) > 0
              && ( ! $sale_end || strtotime( $sale_end ) >= current_time( 'timestamp' ) );
$location  = get_post_meta( $post_id, 'va_location', true );
$views      = va_display_views( $post_id );
$featured  = get_post_meta( $post_id, 'va_featured', true ) === '1';
$is_boosted        = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::is_boosted( $post_id ) : false;
$is_new_pill       = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::is_new_pill( $post_id ) : false;
$show_boost_badge  = get_option( 'va_card_show_boost_badge', '1' ) === '1';
$boost_badge_text  = 'Kiemelt!';
$is_auction= $post->post_type === 'va_auction';
$categories= get_the_terms( $post_id, 'va_category' );
$county    = get_the_terms( $post_id, 'va_county' );
$watching  = va_user_watches( $post_id );
$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );

$meta_rows = (int) get_option( 'va_card_meta_rows', '2' );
if ( $meta_rows < 1 ) {
    $meta_rows = 1;
}
if ( $meta_rows > 3 ) {
    $meta_rows = 3;
}

$meta_col_gap = max( 0, min( 40, (int) get_option( 'va_card_meta_col_gap', '12' ) ) );
$meta_row_gap = max( 0, min( 20, (int) get_option( 'va_card_meta_row_gap', '2' ) ) );
$meta_stack_gap = max( 0, min( 20, (int) get_option( 'va_card_meta_stack_gap', '4' ) ) );

$show_category = get_option( 'va_card_meta_show_category', '0' ) === '1';
$show_county   = get_option( 'va_card_meta_show_county', '0' ) === '1';
$show_location = get_option( 'va_card_meta_show_location', '1' ) === '1';
$show_views    = get_option( 'va_card_meta_show_views', '0' ) === '1';
$show_author   = get_option( 'va_card_meta_show_author', '0' ) === '1';
$show_date     = get_option( 'va_card_meta_show_date', '1' ) === '1';

$watchlist_button_html = '';
if ( is_user_logged_in() ) {
    $watchlist_nonce = wp_create_nonce( 'va_user_nonce' );
    $watchlist_ajax_url = admin_url( 'admin-ajax.php' );
    $watchlist_button_html = '<button class="va-card__watchlist' . ( $watching ? ' active' : '' ) . '" data-post-id="' . esc_attr( $post_id ) . '" data-nonce="' . esc_attr( $watchlist_nonce ) . '" data-ajax-url="' . esc_url( $watchlist_ajax_url ) . '" title="' . esc_attr( $watching ? 'Eltávolítás kedvencekből' : 'Hozzáadás kedvencekhez' ) . '">'
        . '<svg class="va-card__watchlist-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
        . '</button>';
}

$row_category = max( 1, min( $meta_rows, (int) get_option( 'va_card_meta_row_category', '1' ) ) );
$row_county   = max( 1, min( $meta_rows, (int) get_option( 'va_card_meta_row_county', '1' ) ) );
$row_location = max( 1, min( $meta_rows, (int) get_option( 'va_card_meta_row_location', '1' ) ) );
$row_views    = max( 1, min( $meta_rows, (int) get_option( 'va_card_meta_row_views', '2' ) ) );
$row_author   = max( 1, min( $meta_rows, (int) get_option( 'va_card_meta_row_author', '2' ) ) );
$row_date     = max( 1, min( $meta_rows, (int) get_option( 'va_card_meta_row_date', '2' ) ) );

$meta_items_by_row = [];
for ( $r = 1; $r <= $meta_rows; $r++ ) {
    $meta_items_by_row[ $r ] = [];
}

if ( $show_category && $categories && ! is_wp_error( $categories ) ) {
    $meta_items_by_row[ $row_category ][] = '🏷 ' . esc_html( $categories[0]->name );
}
if ( $show_county && $county && ! is_wp_error( $county ) ) {
    $meta_items_by_row[ $row_county ][] = '📍 ' . esc_html( $county[0]->name );
}
if ( $show_location && $location ) {
    $meta_items_by_row[ $row_location ][] = esc_html( $location );
}
if ( $show_views ) {
    $meta_items_by_row[ $row_views ][] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="vertical-align:-1px;margin-right:2px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>' . esc_html( $views );
}
if ( $show_author && $author_name ) {
    $meta_items_by_row[ $row_author ][] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="vertical-align:-1px;margin-right:2px;flex-shrink:0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span class="va-card__meta-item--author">' . esc_html( $author_name ) . '</span>';
}
if ( $show_date ) {
    $meta_items_by_row[ $row_date ][] = '🗓 ' . esc_html( get_the_date( 'Y.m.d', $post_id ) );
}

$meta_style = '--va-card-meta-col-gap:' . $meta_col_gap . 'px;--va-card-meta-row-gap:' . $meta_row_gap . 'px;--va-card-meta-stack-gap:' . $meta_stack_gap . 'px;';

$card_image_html = '';
if ( has_post_thumbnail( $post_id ) ) {
    static $va_card_count = 0;
    $va_card_count++;
    $is_lcp = $va_card_count <= 4;
    $card_image_html = get_the_post_thumbnail( $post_id, 'va-card', [
        'class'         => 'va-card__thumb',
        'alt'           => esc_attr( get_the_title( $post_id ) ),
        'loading'       => $is_lcp ? 'eager' : 'lazy',
        'fetchpriority' => $is_lcp ? 'high' : 'low',
        'decoding'      => $is_lcp ? 'sync'  : 'async',
    ] );
} else {
    $attachment_ids = get_posts( [
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'post_parent'    => $post_id,
        'post_mime_type' => 'image',
        'fields'         => 'ids',
        'orderby'        => 'menu_order ID',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ] );
    if ( ! empty( $attachment_ids ) ) {
        $card_image_html = wp_get_attachment_image( (int) $attachment_ids[0], 'va-card', false, [
            'class'    => 'va-card__thumb',
            'alt'      => esc_attr( get_the_title( $post_id ) ),
            'loading'  => 'lazy',
            'decoding' => 'async',
        ] );
    }
}

if ( ! $card_image_html ) {
    $demo_image_rel = (string) get_post_meta( $post_id, 'va_demo_image', true );
    if ( $demo_image_rel !== '' ) {
        $demo_image_url  = trailingslashit( get_template_directory_uri() ) . ltrim( $demo_image_rel, '/' );
        $card_image_html = sprintf(
            '<img src="%1$s" class="va-card__thumb" alt="%2$s" loading="lazy" decoding="async">',
            esc_url( $demo_image_url ),
            esc_attr( get_the_title( $post_id ) )
        );
    }
}
?>
<div class="va-card va-animate" data-post-id="<?php echo esc_attr( $post_id ); ?>">

    <?php if ( $featured ): ?>
        <span class="va-card__badge va-card__badge--featured"><svg width="9" height="9" viewBox="0 0 24 24" fill="#ffc840" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Kiemelt</span>
    <?php elseif ( $is_auction ): ?>
        <span class="va-card__badge">🔨 Aukció</span>
    <?php endif; ?>
    <?php if ( $is_boosted && $show_boost_badge ): ?>
        <span class="va-card__badge va-card__badge--boost"><?php echo esc_html( $boost_badge_text ); ?></span>
    <?php endif; ?>
    <?php if ( $is_new_pill ): ?>
        <span class="va-card__badge va-card__badge--new">Új</span>
    <?php endif; ?>

    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="va-card__img-wrap">
        <?php if ( $card_image_html ):
            echo $card_image_html;
        else: ?>
            <div class="va-card__thumb-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="40" height="40" opacity=".25"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
        <?php endif; ?>
    </a>

    <div class="va-card__body">
        <h3 class="va-card__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php the_title(); ?></a></h3>

        <?php if ( $is_auction ): ?>
            <?php $cur_bid = get_post_meta( $post_id, 'va_current_bid', true ); ?>
            <?php $start   = get_post_meta( $post_id, 'va_start_price', true ); ?>
            <div class="va-card__price-row">
                <div class="va-card__price">
                    <?php echo esc_html( $cur_bid
                        ? number_format( (float) $cur_bid, 0, ',', ' ' ) . ' Ft'
                        : number_format( (float) $start, 0, ',', ' ' ) . ' Ft (kikiáltási)' ); ?>
                </div>
                <?php echo $watchlist_button_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="va-card__meta">
                <span class="va-card__meta-item">⏱ <?php echo va_auction_countdown( $post_id ); ?></span>
                <span class="va-card__meta-item">🔨 <?php echo esc_html( get_post_meta( $post_id, 'va_bid_count', true ) ?: 0 ); ?> licit</span>
            </div>
        <?php else: ?>
            <div class="va-card__price-row">
                <div class="va-card__price">
                    <?php if ( $has_sale ): ?>
                        <del style="color:rgba(255,255,255,.35);font-size:11px;font-weight:400;display:block;line-height:1.2;"><?php echo esc_html( va_format_price( $price, $price_type ) ); ?></del>
                        <span style="color:#ff3333;font-weight:800;"><?php echo esc_html( number_format( floatval( $sale_price ), 0, ',', ' ' ) . ' Ft' ); ?></span>
                        <span style="font-size:9px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;background:rgba(255,0,0,.25);border:1px solid rgba(255,0,0,.5);border-radius:4px;padding:1px 5px;color:#ff8888;vertical-align:middle;margin-left:4px;">AKCIÓ</span>
                    <?php else: ?>
                        <?php echo esc_html( va_format_price( $price, $price_type ) ); ?>
                    <?php endif; ?>
                </div>
                <?php echo $watchlist_button_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php endif; ?>

        <?php for ( $row_i = 1; $row_i <= $meta_rows; $row_i++ ): ?>
            <?php if ( empty( $meta_items_by_row[ $row_i ] ) ) { continue; } ?>
            <div class="va-card__meta va-card__meta--row" style="<?php echo esc_attr( $meta_style ); ?>">
                <?php foreach ( $meta_items_by_row[ $row_i ] as $meta_item_html ): ?>
                    <span class="va-card__meta-item"><?php echo wp_kses_post( $meta_item_html ); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
