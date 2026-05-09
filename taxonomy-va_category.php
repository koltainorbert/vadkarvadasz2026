<?php
/**
 * taxonomy-va_category.php – Vadász kategória archívum
 */
get_header();

$term = get_queried_object();
$icon = function_exists('va_category_icon') ? va_category_icon( (int) $term->term_id ) : '🎯';
$categories_video = get_option(
    'va_tax_category_video_url',
    get_option( 'va_category_video_url', content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' ) )
);
$tax_badge        = get_option( 'va_tax_hero_badge_text', 'Kategória ajánló' );
$tax_fallback     = get_option( 'va_tax_hero_fallback_lead', 'A kiválasztott kategóriában böngészel, görgess tovább a friss ajánlatokért.' );
$tax_count_suffix = get_option( 'va_tax_hero_count_suffix', 'hirdetés' );
$tax_hero_align   = sanitize_key( (string) get_option( 'va_tax_hero_align', 'center' ) );
if ( ! in_array( $tax_hero_align, [ 'left', 'center', 'right' ], true ) ) {
    $tax_hero_align = 'center';
}
?>
<div class="va-wrap">
    <div class="vcp-video vcp-video--tax" style="margin-top:0;margin-bottom:24px;">
        <video class="vcp-video__media" autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $categories_video ); ?>" type="video/mp4">
        </video>
        <div class="vcp-video__overlay"></div>
        <div class="vcp-video__content vcp-video__content--<?php echo esc_attr( $tax_hero_align ); ?>">
            <span class="vcp-video__eyebrow"><span class="vcp-hero__badge-dot"></span><?php echo esc_html( $tax_badge ); ?></span>
            <h1 class="vcp-video__title"><?php echo wp_kses_post( $icon ) . ' ' . esc_html( $term->name ); ?></h1>
            <?php if ( $term->description ): ?>
                <p class="vcp-video__lead"><?php echo esc_html($term->description); ?></p>
            <?php else: ?>
                <p class="vcp-video__lead"><?php echo esc_html( $tax_fallback ); ?></p>
            <?php endif; ?>
            <span class="va-archive-header__count"><?php echo esc_html($wp_query->found_posts); ?> <?php echo esc_html( $tax_count_suffix ); ?></span>
        </div>
    </div>

    <?php if ( have_posts() ): ?>
        <div class="va-grid">
            <?php while ( have_posts() ): the_post(); ?>
                <?php va_template( 'listing/card', [ 'post' => get_post() ] ); ?>
            <?php endwhile; ?>
        </div>

        <div class="va-pagination">
            <?php echo paginate_links([
                'prev_text' => '← Előző',
                'next_text' => 'Következő →',
            ]); ?>
        </div>

    <?php else: ?>
        <div class="va-empty">
            <div class="va-empty__icon">🔍</div>
            <div class="va-empty__text">Ebben a kategóriában nincs hirdetés.</div>
        </div>
    <?php endif; ?>

</div>
<?php get_footer(); ?>
