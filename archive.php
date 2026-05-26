<?php
/**
 * archive.php – CPT archívum (va_listing, va_auction)
 */
get_header();

$queried = get_queried_object();
$post_type = get_query_var('post_type');
if ( is_array($post_type) ) $post_type = reset($post_type);
$search_query = trim( (string) get_search_query() );
$archive_base_label = $post_type === 'va_auction' ? 'Aukciók' : 'Hirdetések';
$archive_title = $archive_base_label;
if ( is_tax( 'va_category' ) && $queried instanceof WP_Term ) {
    $archive_title = (string) $queried->name;
} elseif ( $search_query !== '' ) {
    $archive_title = $search_query;
}
?>
<div class="va-wrap">

    <div class="va-archive-header" style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap;margin-bottom:24px;padding:14px 18px 16px;border:1px solid rgba(255,255,255,.08);border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));">
        <div style="flex:1 1 360px;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.08);font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:rgba(255,255,255,.7);text-decoration:none;">Főoldal</a>
                <span style="color:rgba(255,255,255,.38);">/</span>
                <span style="color:#fff;"><?php echo esc_html($archive_title); ?></span>
            </div>
            <h1 class="va-archive-header__title" style="margin:0;color:#fff;font-size:clamp(30px,4vw,42px);line-height:1.05;font-weight:900;"><?php echo esc_html($archive_title); ?></h1>
        </div>
        <span class="va-archive-header__count" style="align-self:flex-start;"><?php echo esc_html($wp_query->found_posts); ?> találat</span>
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
            <div class="va-empty__text">Nincs megjeleníthető hirdetés.</div>
        </div>
    <?php endif; ?>

</div>
<?php get_footer(); ?>
