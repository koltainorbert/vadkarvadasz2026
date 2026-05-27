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

$group_url = trim( (string) get_option( 'va_facebook_group_url', '' ) );
if ( $group_url === '' ) {
    $group_url = 'https://www.facebook.com/groups/';
}

$open_label = get_option( 'va_facebook_group_button_label', 'Megnyitas Facebookon' );
?>

<section class="va-fb-group-page" aria-label="Facebook csoport oldal">
    <div class="va-fb-group-page__bg" aria-hidden="true"></div>

    <div class="va-fb-group-page__inner">
        <header class="va-fb-group-page__hero">
            <h1>Facebook csoport</h1>
            <p>
                A Facebook technikai korlatai miatt a csoport nem jelenik meg 1:1-ben ugyanugy,
                mint facebook.com-on. Az alabbi widget a hivatalos beagyazas,
                teljes szelessegu oldalkornyezetben.
            </p>
            <a class="va-fb-group-page__open" href="<?php echo esc_url( $group_url ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html( $open_label ); ?>
            </a>
        </header>

        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/hu_HU/sdk.js#xfbml=1&version=v20.0"></script>

        <div class="va-fb-group-page__embed-wrap">
            <div
                class="fb-group"
                data-href="<?php echo esc_url( $group_url ); ?>"
                data-show-social-context="true"
                data-show-metadata="false">
            </div>
        </div>
    </div>
</section>

<style>
    .va-fb-group-page {
        position: relative;
        min-height: calc(100vh - 120px);
        background: rgb(6, 6, 6);
        color: #fff;
        padding: clamp(20px, 4vw, 44px) clamp(14px, 3vw, 28px);
        overflow: hidden;
    }

    .va-fb-group-page__bg {
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 18% 16%, rgba(255, 0, 0, .20), transparent 44%),
            radial-gradient(circle at 84% 12%, rgba(255, 0, 0, .14), transparent 32%),
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
        margin: 0 0 10px;
        font-size: clamp(2rem, 5vw, 3.2rem);
        line-height: 1.05;
        letter-spacing: .02em;
    }

    .va-fb-group-page__hero p {
        max-width: 900px;
        margin: 0 0 18px;
        color: rgba(255, 255, 255, .82);
    }

    .va-fb-group-page__open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border: 1px solid rgba(255, 0, 0, .55);
        border-radius: 999px;
        color: #fff;
        text-decoration: none;
        background: rgba(255, 0, 0, .18);
        transition: transform .22s ease, background .22s ease;
    }

    .va-fb-group-page__open:hover {
        transform: translateY(-1px);
        background: rgba(255, 0, 0, .28);
    }

    .va-fb-group-page__embed-wrap {
        margin-top: clamp(16px, 3vw, 26px);
        min-height: 70vh;
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 18px;
        padding: clamp(8px, 1.2vw, 14px);
        background: rgba(0, 0, 0, .35);
    }

    .va-fb-group-page .fb-group {
        width: 100%;
    }
</style>

<?php get_footer(); ?>