<?php
/**
 * Template: Aukció lista oldal
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( function_exists( 'va_auctions_enabled' ) && ! va_auctions_enabled() ) {
    echo '<div class="va-wrap"><div class="va-notice va-notice--info">Az aukció funkció jelenleg ki van kapcsolva.</div></div>';
    return;
}

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'va_user_nonce' ),
    'post_id'  => 0,
]);

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => true ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => true ] );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <div class="va-filter-bar">
        <div class="va-filter-bar__title">🔨 Aukciók keresése</div>
        <form id="va-filter-form" data-post-type="va_auction">
            <div class="va-filter-bar__grid">
                <input type="text" id="va-kw" class="va-input" placeholder="Kulcsszó...">

                <select id="va-cat" class="va-select">
                    <option value="">– Kategória –</option>
                    <?php foreach ( $categories as $cat ): ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-county" class="va-select">
                    <option value="">– Megye –</option>
                    <?php foreach ( $counties as $county ): ?>
                        <option value="<?php echo esc_attr( $county->term_id ); ?>"><?php echo esc_html( $county->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-sort" class="va-select">
                    <option value="date">Legújabb</option>
                    <option value="price_asc">Licit: növekvő</option>
                    <option value="price_desc">Licit: csökkenő</option>
                </select>
            </div>
            <div class="va-filter-bar__actions">
                <button type="button" id="va-filter-reset" class="va-btn va-btn--outline va-btn--sm">Szűrők törlése</button>
                <span id="va-results-count" style="font-size:13px;color:rgba(255,255,255,0.5);align-self:center;"></span>
            </div>
        </form>
    </div>

    <div id="va-listing-loader" style="display:none;text-align:center;padding:20px;color:rgba(255,255,255,0.5);">Betöltés...</div>
    <div id="va-listing-results" class="va-grid"></div>
    <div id="va-pagination" class="va-pagination"></div>
</div>
