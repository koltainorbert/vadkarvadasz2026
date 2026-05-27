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
$iframe_src = add_query_arg(
    [
        'href'                   => $group_url,
        'tabs'                   => 'timeline',
        'width'                  => '1200',
        'height'                 => '980',
        'small_header'           => 'false',
        'adapt_container_width'  => 'true',
        'hide_cover'             => 'false',
        'show_facepile'          => 'true',
    ],
    'https://www.facebook.com/plugins/page.php'
);
?>

<section class="va-fb-group-page" aria-label="Facebook csoport oldal">
    <div class="va-fb-group-page__inner">
        <header class="va-fb-group-page__hero">
            <h1>Facebook csoport</h1>
            <a class="va-fb-group-page__open" href="<?php echo esc_url( $group_url ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html( $open_label ); ?>
            </a>
        </header>

        <div class="va-fb-group-page__embed-wrap">
            <iframe
                class="va-fb-group-page__frame"
                src="<?php echo esc_url( $iframe_src ); ?>"
                allowfullscreen="true"
                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                title="Facebook csoport beagyazas">
            </iframe>
        </div>
    </div>
</section>

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

    .va-fb-group-page__embed-wrap {
        margin-top: 16px;
        border: 0;
        border-radius: 0;
        padding: 0;
        overflow: hidden;
        background: transparent;
    }

    .va-fb-group-page__frame {
        width: 100%;
        min-height: 960px;
        border: 0;
        display: block;
        background: transparent;
    }

    @media (max-width: 800px) {
        .va-fb-group-page__frame {
            min-height: 780px;
        }
    }
</style>

<?php get_footer(); ?>