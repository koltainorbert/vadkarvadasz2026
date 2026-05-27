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
            <p class="va-fb-group-page__lead">
                Itt a csoportot nem embedben, hanem egy gyors, tiszta oldalon éred el.
                A Facebook csoport külön oldalon nyílik meg, az oldal pedig nem dob hibát vagy üres blokkot.
            </p>
        </header>

        <div class="va-fb-group-page__card">
            <div class="va-fb-group-page__card-top">
                <div class="va-fb-group-page__badge">Közvetlen elérés</div>
                <div class="va-fb-group-page__chip">Nyilvános / privát beállítástól függően a Facebookon nyílik meg</div>
            </div>

            <div class="va-fb-group-page__card-body">
                <div class="va-fb-group-page__icon" aria-hidden="true">f</div>
                <div>
                    <h2>Nyisd meg a csoportot Facebookon</h2>
                    <p>
                        A csoport Facebook-limitáció miatt nem jeleníthető meg megbízhatóan itt belül,
                        ezért a legstabilabb megoldás a közvetlen megnyitás.
                    </p>
                </div>
            </div>

            <div class="va-fb-group-page__actions">
                <a class="va-fb-group-page__open" href="<?php echo esc_url( $group_url ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html( $open_label ); ?>
                </a>
                <span class="va-fb-group-page__hint">Link: <?php echo esc_html( $group_url ); ?></span>
            </div>
        </div>
    </div>
</section>

<style>
    .va-fb-group-page {
        min-height: calc(100vh - 120px);
        background:
            radial-gradient(circle at 15% 12%, rgba(0, 176, 255, .14), transparent 34%),
            radial-gradient(circle at 82% 16%, rgba(255, 255, 255, .05), transparent 26%),
            linear-gradient(180deg, #05070b 0%, #07111d 100%);
        color: #fff;
        padding: clamp(22px, 4vw, 46px) clamp(14px, 3vw, 28px);
    }

    .va-fb-group-page__inner {
        width: min(1280px, 100%);
        margin: 0 auto;
        display: grid;
        gap: 18px;
    }

    .va-fb-group-page__hero h1 {
        margin: 0;
        font-size: clamp(2rem, 5vw, 3.2rem);
        line-height: 1.05;
        letter-spacing: .02em;
        color: #fff;
    }

    .va-fb-group-page__lead {
        max-width: 860px;
        margin: 12px 0 0;
        font-size: 1rem;
        line-height: 1.65;
        color: rgba(255, 255, 255, .82);
    }

    .va-fb-group-page__card {
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 26px;
        background: linear-gradient(160deg, rgba(10, 18, 30, .96), rgba(9, 14, 22, .98));
        box-shadow: 0 28px 60px rgba(0, 0, 0, .42), inset 0 0 26px rgba(0, 176, 255, .08);
        padding: clamp(18px, 2.4vw, 30px);
    }

    .va-fb-group-page__card-top {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .va-fb-group-page__badge,
    .va-fb-group-page__chip {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: .88rem;
    }

    .va-fb-group-page__badge {
        background: rgba(0, 176, 255, .12);
        border: 1px solid rgba(0, 176, 255, .28);
        color: #d7f3ff;
        font-weight: 800;
    }

    .va-fb-group-page__chip {
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(255, 255, 255, .08);
        color: rgba(255, 255, 255, .78);
    }

    .va-fb-group-page__card-body {
        display: grid;
        grid-template-columns: 70px 1fr;
        gap: 18px;
        align-items: center;
        padding: 4px 0 16px;
    }

    .va-fb-group-page__icon {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        background: linear-gradient(145deg, #0b3d78, #0a2244);
        border: 1px solid rgba(110, 178, 255, .35);
        box-shadow: inset 0 0 20px rgba(255, 255, 255, .08);
        font-size: 2.1rem;
        font-weight: 900;
        color: #fff;
        text-transform: lowercase;
    }

    .va-fb-group-page__card-body h2 {
        margin: 0 0 8px;
        font-size: clamp(1.2rem, 2vw, 1.5rem);
        color: #fff;
    }

    .va-fb-group-page__card-body p {
        margin: 0;
        color: rgba(255, 255, 255, .8);
        line-height: 1.65;
    }

    .va-fb-group-page__actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px 18px;
        padding-top: 8px;
    }

    .va-fb-group-page__open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 20px;
        border: 1px solid rgba(126, 190, 255, .45);
        border-radius: 999px;
        color: #fff;
        text-decoration: none;
        background: linear-gradient(145deg, rgba(11, 60, 118, .92), rgba(10, 34, 68, .96));
        box-shadow: 0 14px 30px rgba(0, 0, 0, .24);
        font-weight: 800;
        transition: transform .22s ease, background .22s ease;
    }

    .va-fb-group-page__open:hover {
        transform: translateY(-1px);
        background: linear-gradient(145deg, rgba(14, 74, 146, .96), rgba(11, 45, 87, .98));
    }

    .va-fb-group-page__hint {
        font-size: .88rem;
        color: rgba(255, 255, 255, .68);
        word-break: break-all;
    }

    @media (max-width: 700px) {
        .va-fb-group-page__card-body {
            grid-template-columns: 1fr;
        }

        .va-fb-group-page__icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
        }
    }
</style>

<?php get_footer(); ?>