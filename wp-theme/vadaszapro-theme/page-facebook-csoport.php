<?php
/**
 * Facebook Csoport oldal (teljes szelesseg)
 * Aktiv: ha a WordPress oldal slugja `facebook-csoport`.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'va-body-facebook-csoport';
    return $classes;
} );

get_header();

$content_url = trim( wp_strip_all_tags( (string) get_the_content() ) );
$meta_url    = trim( (string) get_post_meta( get_the_ID(), 'va_facebook_group_url', true ) );
$option_url  = trim( (string) get_option( 'va_facebook_group_url', '' ) );

$group_url = $content_url;
if ( ! filter_var( $group_url, FILTER_VALIDATE_URL ) ) {
    $group_url = $meta_url;
}
if ( ! filter_var( $group_url, FILTER_VALIDATE_URL ) ) {
    $group_url = $option_url;
}
if ( ! filter_var( $group_url, FILTER_VALIDATE_URL ) ) {
    $group_url = 'https://www.facebook.com/groups/738296572937664';
}

$open_label = get_option( 'va_facebook_group_button_label', 'Megnyitas Facebookon' );
?>

<section class="va-fb-group-page" aria-label="Facebook csoport oldal">
    <div class="va-fb-group-page__inner">
        <header class="va-fb-group-page__hero">
            <h1>Facebook csoport</h1>
            <a class="va-fb-group-page__open" href="<?php echo esc_url( $group_url ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html( $open_label ); ?>
            </a>
        </header>
    </div>
</section>

<script>
setTimeout(function(){
    window.location.href = <?php echo wp_json_encode( esc_url( $group_url ) ); ?>;
}, 220);
</script>

<style>
    .va-fb-group-page {
        min-height: calc(100vh - 140px);
        background: transparent;
        color: #fff;
        padding: clamp(20px, 4vw, 40px) clamp(14px, 3vw, 24px);
    }

    .va-fb-group-page__inner {
        width: min(1280px, 100%);
        margin: 0 auto;
    }

    .va-fb-group-page__hero h1 {
        margin: 0 0 16px;
        font-size: clamp(2rem, 5vw, 3.2rem);
        line-height: 1.05;
        letter-spacing: .02em;
        color: #fff;
    }

    .va-fb-group-page__open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 999px;
        color: #fff;
        text-decoration: none;
        background: transparent;
        transition: transform .22s ease, background .22s ease;
    }

    .va-fb-group-page__open:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, .06);
    }
</style>

<?php get_footer(); ?>