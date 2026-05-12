<?php
/**
 * page-kapcsolat.php – Kapcsolat oldal
 */
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'va-body-kapcsolat';
    return $classes;
} );
get_header();

$status = sanitize_text_field( wp_unslash( $_GET['contact_status'] ?? '' ) );
$contact_video = get_option( 'va_contact_hero_video_url', content_url( 'uploads/2026/04/0_Offroad_4x4_1920x1080.mp4' ) );
$contact_badge = get_option( 'va_contact_hero_badge_text', 'Kapcsolat' );
$contact_title = get_option( 'va_contact_hero_title_text', 'Lépj velünk kapcsolatba' );
$contact_lead  = get_option( 'va_contact_hero_lead_text', 'Minden beérkező megkeresésre 24 órán belül válaszolunk. Hívjon minket telefonon, vagy írjon e-mailt – szívesen segítünk!' );
$contact_hero_align = sanitize_key( (string) get_option( 'va_contact_hero_align', 'center' ) );
if ( ! in_array( $contact_hero_align, [ 'left', 'center', 'right' ], true ) ) {
    $contact_hero_align = 'center';
}

// Kártyák
$card1_title  = get_option( 'va_contact_card1_title', 'Elérhetőségeink' );
$card1_text   = get_option( 'va_contact_card1_text', "Cím: 8412 Veszprém, Alsó-Újsor utca 31.\nTelefon: 06209438636\nEmail: weingartnertrans@gmail.com" );
$card2_title  = get_option( 'va_contact_card2_title', 'Mit írj meg?' );
$card2_item1  = get_option( 'va_contact_card2_item1', 'melyik témában keresel minket' );
$card2_item2  = get_option( 'va_contact_card2_item2', 'mi a kérdésed vagy problémád röviden' );
$card2_item3  = get_option( 'va_contact_card2_item3', 'milyen e-mail címre válaszoljunk' );
$card3_title  = get_option( 'va_contact_card3_title', 'Nyitvatartás' );
$card3_text   = get_option( 'va_contact_card3_text', 'H-P 08h00–17h00-ig. SZ 08h00–12h00-ig. V-Zárva. Ünnepnapokon: előre egyeztetett időpontban.' );

// Form
$form_title    = get_option( 'va_contact_form_title', 'Üzenet küldése' );
$form_btn      = get_option( 'va_contact_form_btn_text', 'Üzenet elküldése' );
$label_name    = get_option( 'va_contact_form_label_name', 'Név' );
$label_email   = get_option( 'va_contact_form_label_email', 'E-mail' );
$label_phone   = get_option( 'va_contact_form_label_phone', 'Telefonszám' );
$label_subject = get_option( 'va_contact_form_label_subject', 'Tárgy' );
$label_message = get_option( 'va_contact_form_label_message', 'Üzenet' );
$ph_name       = get_option( 'va_contact_form_ph_name', 'Teljes neved' );
$ph_email      = get_option( 'va_contact_form_ph_email', 'weingartnertrans@gmail.com' );
$ph_phone      = get_option( 'va_contact_form_ph_phone', '+36 20 943 8636' );
$ph_subject    = get_option( 'va_contact_form_ph_subject', 'Miben tudunk segíteni?' );
$ph_message    = get_option( 'va_contact_form_ph_message', 'Írd le röviden a kérdésedet vagy megkeresésed részleteit...' );
$msg_success   = get_option( 'va_contact_form_msg_success', 'Az üzenetedet elküldtük. Hamarosan e-mailben válaszolunk.' );
$msg_invalid   = get_option( 'va_contact_form_msg_invalid', 'Kérlek tölts ki minden kötelező mezőt érvényes adatokkal.' );
$msg_error     = get_option( 'va_contact_form_msg_error', 'Az üzenet küldése nem sikerült. Ellenőrizd az SMTP beállítást, majd próbáld újra.' );
$msg_nonce     = get_option( 'va_contact_form_msg_nonce', 'A kérés érvénytelen volt. Kérlek küldd el újra az űrlapot.' );
?>

<section class="va-contact-page">
    <div class="va-contact-page__hero">
        <video class="va-contact-page__video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $contact_video ); ?>" type="video/mp4">
        </video>
        <div class="va-contact-page__video-overlay"></div>
        <div class="va-contact-page__hero-glow va-contact-page__hero-glow--1"></div>
        <div class="va-contact-page__hero-glow va-contact-page__hero-glow--2"></div>

        <div class="va-contact-page__hero-inner va-contact-page__hero-inner--<?php echo esc_attr( $contact_hero_align ); ?>">
            <div class="va-contact-page__eyebrow"><span class="vcp-hero__badge-dot"></span><?php echo esc_html( $contact_badge ); ?></div>
            <h1 class="va-contact-page__title"><?php echo esc_html( $contact_title ); ?></h1>
            <p class="va-contact-page__lead">
                <?php echo esc_html( $contact_lead ); ?>
            </p>
        </div>
    </div>

    <div class="va-contact-page__wrap">
        <div class="va-contact-page__info">
            <div class="va-contact-card va-contact-card--accent">
                <div class="va-contact-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 6h16v12H4z"/>
                        <path d="m4 7 8 6 8-6"/>
                    </svg>
                </div>
                <h2 class="va-contact-card__title"><?php echo esc_html( $card1_title ); ?></h2>
                <p class="va-contact-card__text"><?php echo wp_kses_post( nl2br( esc_html( $card1_text ) ) ); ?></p>
            </div>

            <div class="va-contact-card">
                <h3 class="va-contact-card__mini"><?php echo esc_html( $card2_title ); ?></h3>
                <ul class="va-contact-list">
                    <li><?php echo esc_html( $card2_item1 ); ?></li>
                    <li><?php echo esc_html( $card2_item2 ); ?></li>
                    <li><?php echo esc_html( $card2_item3 ); ?></li>
                </ul>
            </div>

            <div class="va-contact-card">
                <h3 class="va-contact-card__mini"><?php echo esc_html( $card3_title ); ?></h3>
                <p class="va-contact-card__text"><?php echo esc_html( $card3_text ); ?></p>
            </div>
        </div>

        <div class="va-contact-page__form-col">
            <div class="va-contact-formbox">
                <h2 class="va-contact-formbox__title"><?php echo esc_html( $form_title ); ?></h2>

                <?php if ( $status === 'ok' ) : ?>
                    <div class="va-contact-alert va-contact-alert--success"><?php echo esc_html( $msg_success ); ?></div>
                <?php elseif ( $status === 'invalid' ) : ?>
                    <div class="va-contact-alert va-contact-alert--error"><?php echo esc_html( $msg_invalid ); ?></div>
                <?php elseif ( $status === 'error' ) : ?>
                    <div class="va-contact-alert va-contact-alert--error"><?php echo esc_html( $msg_error ); ?></div>
                <?php elseif ( $status === 'nonce' ) : ?>
                    <div class="va-contact-alert va-contact-alert--error"><?php echo esc_html( $msg_nonce ); ?></div>
                <?php endif; ?>

                <form class="va-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                    <input type="hidden" name="action" value="va_contact_form">
                    <?php wp_nonce_field( 'va_contact_form', 'va_contact_nonce' ); ?>

                    <div class="va-contact-form__hp" aria-hidden="true">
                        <label for="va-company">Cég</label>
                        <input type="text" id="va-company" name="va_company" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="va-contact-form__row">
                        <div class="va-contact-field">
                            <label for="va-name"><?php echo esc_html( $label_name ); ?></label>
                            <input id="va-name" name="va_name" type="text" required data-typed-placeholder="<?php echo esc_attr( $ph_name ); ?>" placeholder="">
                        </div>
                        <div class="va-contact-field">
                            <label for="va-email"><?php echo esc_html( $label_email ); ?></label>
                            <input id="va-email" name="va_email" type="email" required data-typed-placeholder="<?php echo esc_attr( $ph_email ); ?>" placeholder="">
                        </div>
                    </div>

                    <div class="va-contact-field">
                        <label for="va-phone"><?php echo esc_html( $label_phone ); ?></label>
                        <input id="va-phone" name="va_phone" type="tel" inputmode="tel" required data-typed-placeholder="<?php echo esc_attr( $ph_phone ); ?>" placeholder="">
                    </div>

                    <div class="va-contact-field">
                        <label for="va-subject"><?php echo esc_html( $label_subject ); ?></label>
                        <input id="va-subject" name="va_subject" type="text" required data-typed-placeholder="<?php echo esc_attr( $ph_subject ); ?>" placeholder="">
                    </div>

                    <div class="va-contact-field">
                        <label for="va-message"><?php echo esc_html( $label_message ); ?></label>
                        <textarea id="va-message" name="va_message" rows="8" required data-typed-placeholder="<?php echo esc_attr( $ph_message ); ?>" placeholder=""></textarea>
                    </div>

                    <button class="va-contact-form__submit" type="submit"><?php echo esc_html( $form_btn ); ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(function(){
    var fields=document.querySelectorAll('[data-typed-placeholder]');
    if(!fields.length)return;

    function animateField(field,delay){
        var full=field.getAttribute('data-typed-placeholder')||'';
        var index=0;
        var timer=null;

        function clearTyping(){
            if(timer){window.clearTimeout(timer);timer=null;}
        }

        function step(){
            if(document.activeElement===field||field.value!=='')return;
            field.setAttribute('placeholder',full.slice(0,index));
            index++;
            if(index<=full.length){
                timer=window.setTimeout(step,index<4?42:28);
            }
        }

        field.addEventListener('focus',function(){
            clearTyping();
            field.setAttribute('placeholder','');
        });

        field.addEventListener('blur',function(){
            if(field.value!=='')return;
            index=0;
            field.setAttribute('placeholder','');
            timer=window.setTimeout(step,160);
        });

        timer=window.setTimeout(step,delay);
    }

    fields.forEach(function(field,i){
        animateField(field,220+(i*260));
    });
})();
</script>

<?php get_footer(); ?>