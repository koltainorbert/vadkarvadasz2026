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
        'href'                => $group_url,
        'show_social_context' => 'true',
        'show_metadata'       => 'false',
        'width'               => '1200',
    ],
    'https://www.facebook.com/plugins/group.php'
);
?>

<section class="va-fb-group-page" aria-label="Facebook csoport oldal">
    <div class="va-fb-group-page__bg" aria-hidden="true"></div>

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
                title="Facebook csoport feed">
            </iframe>
        </div>
    </div>
</section>

<style>
    .va-fb-group-page {
        position: relative;
        min-height: calc(100vh - 120px);
        background: linear-gradient(160deg, #050b16 0%, #071225 55%, #08182d 100%);
        color: #fff;
        padding: clamp(20px, 4vw, 44px) clamp(14px, 3vw, 28px);
        overflow: hidden;
    }

    .va-fb-group-page__bg {
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 14% 12%, rgba(0, 178, 255, .16), transparent 42%),
            radial-gradient(circle at 84% 10%, rgba(76, 215, 158, .14), transparent 34%),
            radial-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px);
        background-size: auto, auto, 18px 18px;
        pointer-events: none;
    }

    .va-fb-group-page__inner {
        position: relative;
        z-index: 1;
        width: min(1500px, 100%);
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
        border: 1px solid rgba(120, 190, 255, .45);
        border-radius: 999px;
        color: #fff;
        text-decoration: none;
        background: linear-gradient(145deg, rgba(11, 31, 57, .88), rgba(10, 39, 73, .88));
        box-shadow: 0 10px 28px rgba(0, 130, 255, .16), inset 0 0 22px rgba(71, 152, 255, .12);
        transition: transform .22s ease, background .22s ease;
    }

    .va-fb-group-page__open:hover {
        transform: translateY(-1px);
        background: linear-gradient(145deg, rgba(13, 38, 70, .95), rgba(14, 51, 94, .95));
    }

    .va-fb-group-page__embed-wrap {
        margin-top: clamp(16px, 3vw, 26px);
        min-height: 78vh;
        border: 1px solid rgba(110, 178, 255, .52);
        border-radius: 24px;
        padding: 8px;
        overflow: hidden;
        background: linear-gradient(155deg, rgba(9, 34, 66, .95) 0%, rgba(8, 24, 48, .97) 55%, rgba(6, 17, 36, .99) 100%);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .46), inset 0 0 28px rgba(72, 151, 255, .16);
    }

    .va-fb-group-page__frame {
        width: 100%;
        height: min(78vh, 960px);
        min-height: 720px;
        border: 0;
        border-radius: 16px;
        display: block;
        background: #081427;
    }

    @media (max-width: 800px) {
        .va-fb-group-page__frame {
            min-height: 640px;
            height: 74vh;
        }
    }
</style>

<?php get_footer(); ?>