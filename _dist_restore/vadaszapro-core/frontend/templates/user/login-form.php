<?php
/**
 * Template: Bejelentkezési form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url() );
    exit;
}

$redirect = sanitize_url( wp_get_referer() ?: home_url() );
$login_enabled = get_option( 'va_enable_login', '1' ) === '1';
$register_enabled = get_option( 'va_enable_register', '1' ) === '1';
$mode = sanitize_key( (string) ( $_GET['action'] ?? '' ) );
$reset_key = sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) );
$reset_login = sanitize_user( wp_unslash( $_GET['login'] ?? '' ) );
$is_lost_mode = ( $mode === 'lostpassword' );
$is_reset_mode = ( $mode === 'resetpass' );
?>
<div class="va-wrap va-auth-wrap">
    <?php va_display_flash(); ?>

    <div class="va-auth-box">
        <h2 class="va-auth-box__title"><?php echo $is_reset_mode ? 'Új jelszó beállítása' : ( $is_lost_mode ? 'Elfelejtett jelszó' : 'Bejelentkezés' ); ?></h2>

        <?php if ( ! $login_enabled ): ?>
            <div class="va-notice va-notice--warning">A bejelentkezés jelenleg ki van kapcsolva.</div>
            <?php if ( $register_enabled ): ?>
                <div class="va-auth-links">
                    Még nincs fiókod? <a href="<?php echo esc_url( wp_registration_url() ); ?>">Regisztrálj ingyen</a>
                </div>
            <?php endif; ?>
            <?php return; ?>
        <?php endif; ?>

        <?php if ( $is_lost_mode ): ?>
        <form method="post">
            <?php wp_nonce_field( 'va_lostpass', 'va_lostpass_nonce' ); ?>
            <input type="hidden" name="va_action" value="lostpassword">

            <div class="va-form-group">
                <label>Felhasználónév / E-mail <span class="required">*</span></label>
                <input type="text" name="lost_user_login" class="va-input" required autocomplete="username email">
            </div>

            <button type="submit" class="va-btn va-btn--primary va-btn--block">Jelszó-visszaállító e-mail küldése</button>
        </form>

        <div class="va-auth-links">
            <a href="<?php echo esc_url( wp_login_url() ); ?>">← Vissza a bejelentkezéshez</a>
        </div>
        <?php return; ?>
        <?php endif; ?>

        <?php if ( $is_reset_mode ): ?>
        <form method="post">
            <?php wp_nonce_field( 'va_resetpass', 'va_resetpass_nonce' ); ?>
            <input type="hidden" name="va_action" value="resetpass">
            <input type="hidden" name="rp_key" value="<?php echo esc_attr( $reset_key ); ?>">
            <input type="hidden" name="rp_login" value="<?php echo esc_attr( $reset_login ); ?>">

            <div class="va-form-group">
                <label>Új jelszó <span class="required">*</span></label>
                <input type="password" name="rp_pass1" class="va-input" required minlength="8" autocomplete="new-password">
            </div>

            <div class="va-form-group">
                <label>Új jelszó ismét <span class="required">*</span></label>
                <input type="password" name="rp_pass2" class="va-input" required minlength="8" autocomplete="new-password">
            </div>

            <button type="submit" class="va-btn va-btn--primary va-btn--block">Új jelszó mentése</button>
        </form>

        <div class="va-auth-links">
            <a href="<?php echo esc_url( wp_login_url() ); ?>">← Vissza a bejelentkezéshez</a>
        </div>
        <?php return; ?>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'va_login', 'va_login_nonce' ); ?>
            <input type="hidden" name="va_action"    value="login">
            <input type="hidden" name="redirect_to"  value="<?php echo esc_attr( $redirect ); ?>">

            <div class="va-form-group">
                <label>Felhasználónév / E-mail <span class="required">*</span></label>
                <input type="text" name="login_username" class="va-input" required autocomplete="username">
            </div>

            <div class="va-form-group">
                <label>Jelszó <span class="required">*</span></label>
                <input type="password" name="login_password" class="va-input" required autocomplete="current-password">
            </div>

            <div class="va-form-group" style="display:flex;justify-content:space-between;align-items:center;">
                <label class="va-check-label">
                    <input type="checkbox" name="login_remember" value="1">
                    Emlékezz rám
                </label>
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="font-size:13px;color:#ff0000;">Elfelejtett jelszó</a>
            </div>

            <button type="submit" class="va-btn va-btn--primary va-btn--block">Bejelentkezés</button>
        </form>

        <?php if ( $register_enabled ): ?>
        <div class="va-auth-links">
            Még nincs fiókod? <a href="<?php echo esc_url( wp_registration_url() ); ?>">Regisztrálj ingyen</a>
        </div>
        <?php endif; ?>
    </div>
</div>
