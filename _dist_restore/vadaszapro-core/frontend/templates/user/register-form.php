<?php
/**
 * Template: Regisztrációs form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );

if ( is_user_logged_in() ) {
    $dashboard = get_page_by_path( 'va-fiok' );
    wp_safe_redirect( $dashboard ? get_permalink( $dashboard ) : home_url() );
    exit;
}

$login_enabled = get_option( 'va_enable_login', '1' ) === '1';
$register_enabled = get_option( 'va_enable_register', '1' ) === '1';
?>
<div class="va-wrap va-auth-wrap va-auth-wrap--register">
    <?php va_display_flash(); ?>

    <div class="va-auth-box">
        <h2 class="va-auth-box__title">Regisztráció</h2>

        <?php if ( ! $register_enabled ): ?>
            <div class="va-notice va-notice--warning">A regisztráció jelenleg ki van kapcsolva.</div>
            <?php if ( $login_enabled ): ?>
                <div class="va-auth-links">
                    Van már fiókod? <a href="<?php echo esc_url( wp_login_url() ); ?>">Bejelentkezés</a>
                </div>
            <?php endif; ?>
            <?php return; ?>
        <?php endif; ?>

        <form method="post" class="va-register-form">
            <?php wp_nonce_field( 'va_register', 'va_register_nonce' ); ?>
            <input type="hidden" name="va_action" value="register">
            <input type="hidden" name="reg_account_type" id="va-account-type" value="private">

        <div class="va-register-grid">
            <?php
            $fb_reg    = 'va_register';
            $fb_rfields = VA_Form_Builder::get_fields( $fb_reg );
            usort( $fb_rfields, fn( $a, $b ) => (int)($a['order'] ?? 99) - (int)($b['order'] ?? 99) );

            foreach ( $fb_rfields as $rf ):
                $rkey   = (string)( $rf['key']         ?? '' );
                $rlabel = esc_html( (string)( $rf['label']        ?? $rkey ) );
                $rph    = esc_attr( (string)( $rf['placeholder']  ?? '' ) );
                $rreq   = ! empty( $rf['required'] );
                if ( empty( $rf['enabled'] ) ) continue;
                $rreq_html = $rreq ? ' <span class="required">*</span>' : '';
                $rreq_attr = $rreq ? ' required' : '';
                $company_keys = [ 'reg_company_name', 'reg_company_tax', 'reg_company_seat' ];
            ?>

            <?php if ( $rkey === 'account_type' ): ?>
                <div class="va-register-field va-register-field--full">
                    <div class="va-account-switch" role="group" aria-label="Fiók típus választó">
                        <span class="va-account-switch__label">Magánszemély</span>
                        <label class="va-account-switch__toggle" for="va-account-type-switch">
                            <input type="checkbox" id="va-account-type-switch" class="va-account-switch__input" aria-label="Céges fiók kapcsoló">
                            <span class="va-account-switch__slider" aria-hidden="true"></span>
                        </label>
                        <span class="va-account-switch__label">Cég</span>
                    </div>
                </div>

            <?php elseif ( $rkey === 'reg_company_name' ): ?>
                <?php
                // Céges blokk: összegyűjti a három company mezőt egy csoportba
                $company_fields = [];
                foreach ( $fb_rfields as $crf ) {
                    if ( in_array( $crf['key'] ?? '', $company_keys, true ) && ! empty( $crf['enabled'] ) ) {
                        $company_fields[] = $crf;
                    }
                }
                ?>
                <div class="va-register-company va-register-field va-register-field--full" aria-hidden="true">
                    <div class="va-register-company__grid">
                        <?php foreach ( $company_fields as $cf ): ?>
                            <div class="va-form-group va-register-field">
                                <label><?php echo esc_html( (string)( $cf['label'] ?? '' ) ); ?><?php echo ! empty( $cf['required'] ) ? ' <span class="required">*</span>' : ''; ?></label>
                                <input type="text" name="<?php echo esc_attr( (string)( $cf['key'] ?? '' ) ); ?>"
                                    class="va-input" data-company-field="1" disabled
                                    placeholder="<?php echo esc_attr( (string)( $cf['placeholder'] ?? '' ) ); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ( in_array( $rkey, [ 'reg_company_tax', 'reg_company_seat' ], true ) ): ?>
                <?php // már renderelve a company block-ban, kihagyjuk ?>

            <?php elseif ( $rkey === 'terms_accept' ): ?>
                <div class="va-form-group va-register-field va-register-field--full">
                    <label class="va-check-label">
                        <input type="checkbox" name="terms_accept"<?php echo $rreq_attr; ?>>
                        <?php echo esc_html( $rlabel ); ?> – <a href="<?php echo esc_url( home_url('/aszf') ); ?>" style="color:#ff0000;">ÁSZF</a>
                    </label>
                </div>

            <?php elseif ( in_array( $rf['type'] ?? 'text', [ 'password' ], true ) ): ?>
                <div class="va-form-group va-register-field">
                    <label><?php echo $rlabel . $rreq_html; ?></label>
                    <input type="password" name="<?php echo esc_attr( $rkey ); ?>" class="va-input"<?php echo $rreq_attr; ?> minlength="8"
                        autocomplete="<?php echo $rkey === 'reg_password' ? 'new-password' : 'new-password'; ?>">
                    <?php if ( $rkey === 'reg_password' ): ?><p class="va-register-help">Min. 8 karakter</p><?php endif; ?>
                </div>

            <?php else: ?>
                <div class="va-form-group va-register-field">
                    <label><?php echo $rlabel . $rreq_html; ?></label>
                    <input type="<?php echo esc_attr( (string)( $rf['type'] ?? 'text' ) ); ?>"
                        name="<?php echo esc_attr( $rkey ); ?>"
                        class="va-input"<?php echo $rreq_attr; ?>
                        placeholder="<?php echo $rph; ?>"
                        <?php if ( $rkey === 'reg_username' ) echo 'autocomplete="username"'; ?>
                        <?php if ( $rkey === 'reg_email' ) echo 'autocomplete="email"'; ?>>
                </div>
            <?php endif; ?>

            <?php endforeach; ?>

                <div class="va-register-field va-register-field--full">
                    <button type="submit" class="va-btn va-btn--primary va-btn--block">Regisztráció</button>
                </div>
        </div>
        </form>

        <?php if ( $login_enabled ): ?>
        <div class="va-auth-links">
            Már van fiókod? <a href="<?php echo esc_url( wp_login_url() ); ?>">Bejelentkezés</a>
        </div>
        <?php endif; ?>
    </div>
</div>
