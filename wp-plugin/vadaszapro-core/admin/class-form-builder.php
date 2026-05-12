<?php
/**
 * VA Form Builder – vizuális form szerkesztő admin
 * Formok: va_listing_submit | va_register | va_login
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Form_Builder {

    /* ── Alapértelmezett mezők minden formhoz ──────────── */

    public static function get_default_fields( string $form_id ): array {
        $site_type = sanitize_key( (string) get_option( 'va_site_type', 'vadaszat' ) );
        $listing_submit_defaults = $site_type === 'jarmu'
            ? [
                [ 'key' => 'title',       'label' => 'Hirdetés címe',          'placeholder' => 'pl. Skoda Octavia 1.6 TDI',      'type' => 'text',     'required' => true,  'enabled' => true,  'order' => 1  ],
                [ 'key' => 'category',    'label' => 'Járműkategória',          'placeholder' => '',                                 'type' => 'select',   'required' => true,  'enabled' => true,  'order' => 2  ],
                [ 'key' => 'county',      'label' => 'Megye',                   'placeholder' => '',                                 'type' => 'select',   'required' => true,  'enabled' => true,  'order' => 3  ],
                [ 'key' => 'condition',   'label' => 'Állapot',                 'placeholder' => '',                                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 4  ],
                [ 'key' => 'location',    'label' => 'Helyszín (város)',         'placeholder' => 'pl. Budapest, Győr...',            'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 5  ],
                [ 'key' => 'brand',       'label' => 'Márka',                   'placeholder' => '',                                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 6  ],
                [ 'key' => 'model',       'label' => 'Modell / Típus',          'placeholder' => 'pl. Octavia, A4, Focus',          'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 7  ],
                [ 'key' => 'body_type',   'label' => 'Kivitel',                 'placeholder' => '',                                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 8  ],
                [ 'key' => 'year',        'label' => 'Évjárat',                 'placeholder' => 'pl. 2019',                         'type' => 'number',   'required' => false, 'enabled' => true,  'order' => 9  ],
                [ 'key' => 'price',       'label' => 'Ár (Ft)',                 'placeholder' => '0',                                'type' => 'number',   'required' => false, 'enabled' => true,  'order' => 10 ],
                [ 'key' => 'price_type',  'label' => 'Árazás típusa',           'placeholder' => '',                                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 11 ],
                [ 'key' => 'description', 'label' => 'Leírás',                  'placeholder' => 'Részletes leírás...',              'type' => 'textarea', 'required' => true,  'enabled' => true,  'order' => 12 ],
                [ 'key' => 'images',      'label' => 'Képek',                   'placeholder' => '',                                 'type' => 'file',     'required' => false, 'enabled' => true,  'order' => 13 ],
                [ 'key' => 'phone',       'label' => 'Telefonszám',             'placeholder' => '+36 30 000 0000',                  'type' => 'tel',      'required' => true,  'enabled' => true,  'order' => 14 ],
                [ 'key' => 'email_show',  'label' => 'E-mail megjelenítése',    'placeholder' => '',                                 'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 15 ],
            ]
            : [
                [ 'key' => 'title',       'label' => 'Hirdetés címe',          'placeholder' => 'pl. Beretta A400 sörétes puska',  'type' => 'text',     'required' => true,  'enabled' => true,  'order' => 1  ],
                [ 'key' => 'category',    'label' => 'Kategória',               'placeholder' => '',                                 'type' => 'select',   'required' => true,  'enabled' => true,  'order' => 2  ],
                [ 'key' => 'county',      'label' => 'Megye',                   'placeholder' => '',                                 'type' => 'select',   'required' => true,  'enabled' => true,  'order' => 3  ],
                [ 'key' => 'condition',   'label' => 'Állapot',                 'placeholder' => '',                                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 4  ],
                [ 'key' => 'location',    'label' => 'Helyszín (város)',         'placeholder' => 'pl. Budapest, Győr...',            'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 5  ],
                [ 'key' => 'brand',       'label' => 'Márka / Gyártó',          'placeholder' => 'pl. Beretta, Sauer...',            'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 6  ],
                [ 'key' => 'model',       'label' => 'Modell / Típus',          'placeholder' => 'pl. A400 Xcel',                    'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 7  ],
                [ 'key' => 'caliber',     'label' => 'Kaliber',                 'placeholder' => 'pl. 12/70, .308 Win',              'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 8  ],
                [ 'key' => 'year',        'label' => 'Gyártási év',             'placeholder' => 'pl. 2018',                         'type' => 'number',   'required' => false, 'enabled' => true,  'order' => 9  ],
                [ 'key' => 'license_req', 'label' => 'Fegyverengedély szükséges', 'placeholder' => '',                              'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 10 ],
                [ 'key' => 'price',       'label' => 'Ár (Ft)',                 'placeholder' => '0',                                'type' => 'number',   'required' => false, 'enabled' => true,  'order' => 11 ],
                [ 'key' => 'price_type',  'label' => 'Árazás típusa',           'placeholder' => '',                                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 12 ],
                [ 'key' => 'description', 'label' => 'Leírás',                  'placeholder' => 'Részletes leírás...',              'type' => 'textarea', 'required' => true,  'enabled' => true,  'order' => 13 ],
                [ 'key' => 'images',      'label' => 'Képek',                   'placeholder' => '',                                 'type' => 'file',     'required' => false, 'enabled' => true,  'order' => 14 ],
                [ 'key' => 'phone',       'label' => 'Telefonszám',             'placeholder' => '+36 30 000 0000',                  'type' => 'tel',      'required' => true,  'enabled' => true,  'order' => 15 ],
                [ 'key' => 'email_show',  'label' => 'E-mail megjelenítése',    'placeholder' => '',                                 'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 16 ],
            ];
        $defaults = [
            'va_listing_submit' => $listing_submit_defaults,
            'va_register' => [
                [ 'key' => 'account_type',   'label' => 'Fiók típus (Magánszemély / Cég)', 'placeholder' => '', 'type' => 'toggle',   'required' => false, 'enabled' => true, 'order' => 1  ],
                [ 'key' => 'reg_firstname',  'label' => 'Keresztnév',             'placeholder' => 'pl. András',           'type' => 'text',     'required' => true,  'enabled' => true, 'order' => 2  ],
                [ 'key' => 'reg_lastname',   'label' => 'Vezetéknév',             'placeholder' => 'pl. Nagy',             'type' => 'text',     'required' => false, 'enabled' => true, 'order' => 3  ],
                [ 'key' => 'reg_username',   'label' => 'Felhasználónév',         'placeholder' => 'pl. vadasz1988',       'type' => 'text',     'required' => true,  'enabled' => true, 'order' => 4  ],
                [ 'key' => 'reg_email',      'label' => 'E-mail cím',             'placeholder' => 'pelda@email.hu',       'type' => 'email',    'required' => true,  'enabled' => true, 'order' => 5  ],
                [ 'key' => 'reg_phone',      'label' => 'Telefonszám',            'placeholder' => '+36 30 000 0000',      'type' => 'tel',      'required' => false, 'enabled' => true, 'order' => 6  ],
                [ 'key' => 'reg_company_name','label' => 'Cégnév',                'placeholder' => 'Minta Kft.',           'type' => 'text',     'required' => false, 'enabled' => true, 'order' => 7  ],
                [ 'key' => 'reg_company_tax', 'label' => 'Adószám',               'placeholder' => '12345678-2-42',        'type' => 'text',     'required' => false, 'enabled' => true, 'order' => 8  ],
                [ 'key' => 'reg_company_seat','label' => 'Székhely',              'placeholder' => '1234 Budapest...',     'type' => 'text',     'required' => false, 'enabled' => true, 'order' => 9  ],
                [ 'key' => 'reg_password',   'label' => 'Jelszó',                 'placeholder' => 'Min. 8 karakter',      'type' => 'password', 'required' => true,  'enabled' => true, 'order' => 10 ],
                [ 'key' => 'reg_password2',  'label' => 'Jelszó ismét',           'placeholder' => '',                     'type' => 'password', 'required' => true,  'enabled' => true, 'order' => 11 ],
                [ 'key' => 'terms_accept',   'label' => 'ÁSZF elfogadása',        'placeholder' => '',                     'type' => 'checkbox', 'required' => true,  'enabled' => true, 'order' => 12 ],
            ],
            'va_login' => [
                [ 'key' => 'log',      'label' => 'Felhasználónév / E-mail', 'placeholder' => 'pl. vadasz1988',   'type' => 'text',     'required' => true,  'enabled' => true, 'order' => 1 ],
                [ 'key' => 'pwd',      'label' => 'Jelszó',                   'placeholder' => '',                  'type' => 'password', 'required' => true,  'enabled' => true, 'order' => 2 ],
                [ 'key' => 'rememberme','label' => 'Emlékezz rám',            'placeholder' => '',                  'type' => 'checkbox', 'required' => false, 'enabled' => true, 'order' => 3 ],
            ],
            'va_admin_listing_edit' => [
                [ 'key' => 'va_price',      'label' => 'Ár (Ft)',                    'placeholder' => '0',                'type' => 'number',   'required' => false, 'enabled' => true,  'order' => 1  ],
                [ 'key' => 'va_price_type', 'label' => 'Árazás típusa',              'placeholder' => '',                 'type' => 'select',   'required' => false, 'enabled' => true,  'order' => 2  ],
                [ 'key' => 'va_brand',      'label' => 'Márka / Gyártó',             'placeholder' => 'pl. Browning',     'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 3  ],
                [ 'key' => 'va_model',      'label' => 'Modell / Típus',             'placeholder' => 'pl. X-Bolt',       'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 4  ],
                [ 'key' => 'va_caliber',    'label' => 'Kaliber',                    'placeholder' => 'pl. .308 Win',     'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 5  ],
                [ 'key' => 'va_year',       'label' => 'Gyártási év',                'placeholder' => 'pl. 2020',         'type' => 'number',   'required' => false, 'enabled' => true,  'order' => 6  ],
                [ 'key' => 'va_phone',      'label' => 'Telefonszám',                'placeholder' => '+36 20 …',         'type' => 'tel',      'required' => false, 'enabled' => true,  'order' => 7  ],
                [ 'key' => 'va_location',   'label' => 'Helység',                    'placeholder' => 'pl. Budapest',     'type' => 'text',     'required' => false, 'enabled' => true,  'order' => 8  ],
                [ 'key' => 'va_email_show', 'label' => 'Email cím megjelenítése',    'placeholder' => '',                 'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 9  ],
                [ 'key' => 'va_featured',   'label' => 'Kiemelt hirdetés',           'placeholder' => '',                 'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 10 ],
                [ 'key' => 'va_verified',   'label' => 'Ellenőrzött hirdető',        'placeholder' => '',                 'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 11 ],
                [ 'key' => 'va_license_req','label' => 'Fegyverengedély szükséges',  'placeholder' => '',                 'type' => 'checkbox', 'required' => false, 'enabled' => true,  'order' => 12 ],
                [ 'key' => 'va_expires',    'label' => 'Lejárat dátuma',             'placeholder' => '',                 'type' => 'date',     'required' => false, 'enabled' => true,  'order' => 13 ],
            ],
        ];

        return $defaults[ $form_id ] ?? [];
    }

    /* ── Config lekérés (mentett vagy alap) ───────────── */
    public static function get_fields( string $form_id ): array {
        $saved = get_option( 'va_form_config_' . $form_id );
        if ( $saved && is_array( $saved ) && count( $saved ) > 0 ) {
            return $saved;
        }
        return self::get_default_fields( $form_id );
    }

    /* ── Egyes mező adatai key alapján ────────────────── */
    public static function get_field( string $form_id, string $key ): array {
        foreach ( self::get_fields( $form_id ) as $field ) {
            if ( ( $field['key'] ?? '' ) === $key ) {
                return $field;
            }
        }
        return [];
    }

    public static function is_enabled( string $form_id, string $key ): bool {
        $f = self::get_field( $form_id, $key );
        return ! empty( $f ) && ( (bool) ( $f['enabled'] ?? true ) );
    }

    public static function is_required( string $form_id, string $key ): bool {
        $f = self::get_field( $form_id, $key );
        return ! empty( $f ) && ( (bool) ( $f['required'] ?? false ) );
    }

    public static function get_label( string $form_id, string $key ): string {
        $f = self::get_field( $form_id, $key );
        return esc_html( (string) ( $f['label'] ?? $key ) );
    }

    public static function get_placeholder( string $form_id, string $key ): string {
        $f = self::get_field( $form_id, $key );
        return esc_attr( (string) ( $f['placeholder'] ?? '' ) );
    }

    /* ── Init ─────────────────────────────────────────── */
    public static function init(): void {
        add_action( 'admin_post_va_save_form_config', [ __CLASS__, 'handle_save' ] );
    }

    /* ── Mentés kezelése ──────────────────────────────── */
    public static function handle_save(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }

        $nonce = isset( $_POST['va_form_builder_nonce'] )
            ? sanitize_text_field( wp_unslash( (string) $_POST['va_form_builder_nonce'] ) )
            : '';
        if ( ! wp_verify_nonce( $nonce, 'va_form_builder_save' ) ) {
            wp_die( 'Érvénytelen nonce.' );
        }

        $form_id = sanitize_key( (string) ( $_POST['va_form_id'] ?? '' ) );
        $allowed = [ 'va_listing_submit', 'va_register', 'va_login', 'va_admin_listing_edit' ];
        if ( ! in_array( $form_id, $allowed, true ) ) {
            wp_die( 'Ismeretlen form.' );
        }

        $defaults = self::get_default_fields( $form_id );
        $keys_order  = isset( $_POST['field_order'] ) && is_array( $_POST['field_order'] ) ? (array) $_POST['field_order'] : [];
        $keys_order  = array_map( 'sanitize_key', $keys_order );

        $saved = [];
        $order = 1;
        foreach ( $keys_order as $fkey ) {
            $is_custom = substr( $fkey, 0, 7 ) === 'custom_';

            if ( $is_custom ) {
                $label_raw = isset( $_POST['field_label'][$fkey] )    ? sanitize_text_field( wp_unslash( (string) $_POST['field_label'][$fkey] ) ) : '';
                $ph_raw    = isset( $_POST['field_ph'][$fkey] )       ? sanitize_text_field( wp_unslash( (string) $_POST['field_ph'][$fkey]    ) ) : '';
                $type_raw  = isset( $_POST['field_type'][$fkey] )     ? sanitize_key( wp_unslash( (string) $_POST['field_type'][$fkey] ) ) : 'text';
                $allowed_types = [ 'text','textarea','number','email','tel','checkbox' ];
                if ( ! in_array( $type_raw, $allowed_types, true ) ) $type_raw = 'text';
                if ( $label_raw === '' ) continue;
            } else {
                $def = null;
                foreach ( $defaults as $d ) {
                    if ( $d['key'] === $fkey ) { $def = $d; break; }
                }
                if ( ! $def ) continue;
                $label_raw = isset( $_POST['field_label'][$fkey] ) ? sanitize_text_field( wp_unslash( (string) $_POST['field_label'][$fkey] ) ) : $def['label'];
                $ph_raw    = isset( $_POST['field_ph'][$fkey] )    ? sanitize_text_field( wp_unslash( (string) $_POST['field_ph'][$fkey]   ) ) : $def['placeholder'];
                $type_raw  = $def['type'];
            }

            $enabled_raw  = isset( $_POST['field_enabled'][$fkey] )  ? sanitize_text_field( wp_unslash( (string) $_POST['field_enabled'][$fkey]  ) ) : '0';
            $required_raw = isset( $_POST['field_required'][$fkey] ) ? sanitize_text_field( wp_unslash( (string) $_POST['field_required'][$fkey] ) ) : '0';

            $saved[] = [
                'key'         => $fkey,
                'label'       => $label_raw,
                'placeholder' => $ph_raw,
                'type'        => $type_raw,
                'required'    => $required_raw === '1',
                'enabled'     => $enabled_raw === '1',
                'order'       => $order++,
                'custom'      => $is_custom,
            ];
        }

        update_option( 'va_form_config_' . $form_id, $saved );

        wp_safe_redirect( add_query_arg([
            'page'    => 'va-form-builder',
            'form'    => $form_id,
            'updated' => '1',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ══ Admin render ═════════════════════════════════════ */
    public static function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $forms = [
            'va_listing_submit'    => '📋 Hirdetés feladás (frontend)',
            'va_admin_listing_edit'=> '🛠️ Admin hirdetés szerkesztő',
            'va_register'          => '👤 Regisztráció',
            'va_login'             => '🔐 Bejelentkezés',
        ];

        $active = sanitize_key( (string) ( $_GET['form'] ?? 'va_listing_submit' ) );
        if ( ! array_key_exists( $active, $forms ) ) {
            $active = 'va_listing_submit';
        }

        $fields = self::get_fields( $active );
        usort( $fields, fn( $a, $b ) => (int)( $a['order'] ?? 99 ) - (int)( $b['order'] ?? 99 ) );

        $updated = isset( $_GET['updated'] ) && $_GET['updated'] === '1';

        $type_icons = [
            'text'     => '✏️',
            'email'    => '📧',
            'tel'      => '📱',
            'password' => '🔒',
            'number'   => '🔢',
            'textarea' => '📝',
            'select'   => '🔽',
            'checkbox' => '☑️',
            'toggle'   => '🔀',
            'file'     => '🖼️',
            'date'     => '📅',
        ];
        ?>
        <div class="wrap va-admin-wrap va-fb-wrap">
            <h1>🧩 VadászApró – Form szerkesztő</h1>
            <?php if ( $updated ): ?>
                <div class="notice notice-success is-dismissible"><p>✅ A form konfigurációja mentve!</p></div>
            <?php endif; ?>

            <div class="va-fb-tabs">
                <?php foreach ( $forms as $fid => $fname ): ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=va-form-builder&form=' . $fid ) ); ?>"
                       class="va-fb-tab <?php echo $fid === $active ? 'va-fb-tab--active' : ''; ?>">
                        <?php echo esc_html( $fname ); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="va-fb-body">
                <div class="va-fb-info">
                    <strong>Sorrendhez</strong> húzd a sorokat (⠿ fogópontnál), <strong>ki/bekapcsoláshoz</strong> kattints a kapcsolóra, <strong>Kötelező</strong> pirossal jelzett.
                </div>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="va-fb-form">
                    <?php wp_nonce_field( 'va_form_builder_save', 'va_form_builder_nonce' ); ?>
                    <input type="hidden" name="action" value="va_save_form_config">
                    <input type="hidden" name="va_form_id" value="<?php echo esc_attr( $active ); ?>">

                    <div class="va-fb-list" id="va-fb-sortable">
                        <?php foreach ( $fields as $field ): ?>
                            <?php
                                $fkey      = esc_attr( (string) ( $field['key']         ?? '' ) );
                                $flabel    = esc_attr( (string) ( $field['label']        ?? $fkey ) );
                                $fph       = esc_attr( (string) ( $field['placeholder']  ?? '' ) );
                                $ftype     = (string) ( $field['type'] ?? 'text' );
                                $fenabled  = ! empty( $field['enabled'] );
                                $frequired = ! empty( $field['required'] );
                                $icon      = $type_icons[ $ftype ] ?? '🔲';
                                $row_cls   = 'va-fb-row' . ( ! $fenabled ? ' va-fb-row--disabled' : '' );
                            ?>
                            <div class="<?php echo esc_attr( $row_cls ); ?>" data-key="<?php echo $fkey; ?>">
                                <input type="hidden" name="field_order[]" value="<?php echo $fkey; ?>">

                                <span class="va-fb-handle" title="Húzd a sorrendhez">⠿</span>

                                <span class="va-fb-type-icon" title="<?php echo esc_attr( $ftype ); ?>"><?php echo $icon; ?></span>

                                <div class="va-fb-row-inputs">
                                    <div class="va-fb-input-group">
                                        <label>Felirat</label>
                                        <input type="text" name="field_label[<?php echo $fkey; ?>]" value="<?php echo $flabel; ?>" class="va-fb-label-input">
                                    </div>
                                    <?php if ( ! in_array( $ftype, [ 'checkbox', 'toggle', 'select', 'file' ], true ) ): ?>
                                    <div class="va-fb-input-group">
                                        <label>Placeholder</label>
                                        <input type="text" name="field_ph[<?php echo $fkey; ?>]" value="<?php echo $fph; ?>" class="va-fb-ph-input">
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="va-fb-row-controls">
                                    <label class="va-fb-ctrl-label">
                                        <span>Kötelező</span>
                                        <input type="hidden"   name="field_required[<?php echo $fkey; ?>]" value="0">
                                        <label class="va-toggle va-fb-toggle-req">
                                            <input type="checkbox" name="field_required[<?php echo $fkey; ?>]" value="1" <?php checked( $frequired ); ?>>
                                            <span class="va-toggle-slider va-toggle-slider--red"></span>
                                        </label>
                                    </label>
                                    <label class="va-fb-ctrl-label">
                                        <span>Aktív</span>
                                        <input type="hidden"   name="field_enabled[<?php echo $fkey; ?>]" value="0">
                                        <label class="va-toggle">
                                            <input type="checkbox" name="field_enabled[<?php echo $fkey; ?>]" value="1" <?php checked( $fenabled ); ?> class="va-fb-enable-cb">
                                            <span class="va-toggle-slider"></span>
                                        </label>
                                    </label>
                                </div>                                <?php if ( substr( $fkey, 0, 7 ) === 'custom_' ): ?>
                                <button type="button" class="va-fb-delete-btn" title="Törlés">🗑</button>
                                <?php endif; ?>                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="va-fb-actions">
                        <button type="submit" class="button button-primary va-fb-save-btn">💾 Form mentése</button>
                        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'va-form-builder', 'form' => $active, 'va_reset_form' => $active, '_wpnonce' => wp_create_nonce( 'va_reset_form_' . $active ) ], admin_url( 'admin.php' ) ) ); ?>"
                           class="button va-fb-reset-btn"
                           onclick="return confirm('Visszaállítod az alapértelmezésre?')">↩ Visszaállítás</a>
                    </div>

                    <div class="va-fb-add-panel">
                        <h4>➕ Új egyedi mező hozzáadása</h4>
                        <div class="va-fb-add-row">
                            <input type="text"   id="va-fb-new-label" placeholder="Felirat (pl. Engedélyszám)">
                            <input type="text"   id="va-fb-new-ph"    placeholder="Placeholder (pl. ENG-12345)">
                            <select id="va-fb-new-type">
                                <option value="text">Szöveg</option>
                                <option value="textarea">Hosszú szöveg</option>
                                <option value="number">Szám</option>
                                <option value="email">E-mail</option>
                                <option value="tel">Telefon</option>
                                <option value="checkbox">Jelölőnégyzet</option>
                            </select>
                            <button type="button" id="va-fb-add-btn" class="button va-fb-add-btn">+ Hozzáad</button>
                        </div>
                        <p style="font-size:11px;color:#888;margin:8px 0 0;">Az egyedi mezők a frontend formon megjelennek (a mentett sorrend szerint), és a hirdetés post meta-jaiban tárolódnak.</p>
                    </div>
                </form>
            </div>
        </div>

        <style>
        /* ── Form Builder – dark téma ── */
        .va-fb-wrap * { color:#fff !important; box-sizing:border-box; }
        .va-fb-wrap { max-width:960px; }
        .va-fb-tabs { display:flex; gap:6px; margin-bottom:0; flex-wrap:wrap; }
        .va-fb-tab  { padding:8px 18px; border-radius:6px 6px 0 0; background:rgba(255,255,255,.06); color:rgba(255,255,255,.5) !important; text-decoration:none; font-size:13px; font-weight:600; border:1px solid rgba(255,255,255,.12); border-bottom:none; transition:.15s; }
        .va-fb-tab:hover { background:rgba(255,255,255,.1); color:#fff !important; }
        .va-fb-tab--active { background:#1a1a1a; color:#ff4444 !important; border-color:rgba(255,255,255,.18); font-weight:700; }
        .va-fb-body { background:#1a1a1a; border:1px solid rgba(255,255,255,.12); border-radius:0 6px 6px 6px; padding:20px; }
        .va-fb-info { font-size:12px; color:rgba(255,255,255,.55) !important; margin-bottom:16px; padding:10px 14px; background:rgba(255,255,255,.04); border-left:3px solid #ff0000; border-radius:0 4px 4px 0; }
        .va-fb-list { display:flex; flex-direction:column; gap:7px; }
        .va-fb-row  { display:flex; align-items:center; gap:12px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:6px; padding:9px 13px; cursor:default; transition:.15s; }
        .va-fb-row:hover { border-color:#ff0000; background:rgba(255,0,0,.05); }
        .va-fb-row.sortable-chosen { opacity:.75; border-color:#ff0000; box-shadow:0 2px 8px rgba(255,0,0,.15); }
        .va-fb-row.sortable-ghost  { opacity:.3; }
        .va-fb-row--disabled { opacity:.5; background:rgba(255,255,255,.02); }
        .va-fb-row--custom { border-left:3px solid #4da3ff; }
        .va-fb-handle { font-size:20px; cursor:grab; color:rgba(255,255,255,.3) !important; user-select:none; flex-shrink:0; }
        .va-fb-handle:active { cursor:grabbing; }
        .va-fb-type-icon { font-size:17px; flex-shrink:0; width:22px; text-align:center; }
        .va-fb-row-inputs { display:flex; gap:10px; flex:1; flex-wrap:wrap; }
        .va-fb-input-group { display:flex; flex-direction:column; gap:3px; flex:1; min-width:130px; }
        .va-fb-input-group > label { font-size:10px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,.4) !important; letter-spacing:.6px; }
        .va-fb-label-input, .va-fb-ph-input, .va-fb-type-sel { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.15); color:#fff !important; border-radius:4px; padding:5px 8px; font-size:13px; width:100%; }
        .va-fb-label-input:focus, .va-fb-ph-input:focus, .va-fb-type-sel:focus { border-color:#ff0000; outline:none; box-shadow:0 0 0 1px rgba(255,0,0,.4); }
        .va-fb-row-controls { display:flex; gap:14px; flex-shrink:0; align-items:center; }
        .va-fb-ctrl-label { display:flex; flex-direction:column; align-items:center; gap:4px; }
        .va-fb-ctrl-label > span { font-size:10px; font-weight:700; text-transform:uppercase; color:rgba(255,255,255,.4) !important; letter-spacing:.5px; }
        /* Toggle */
        .va-fb-wrap .va-toggle { position:relative; display:inline-block; width:36px; height:20px; }
        .va-fb-wrap .va-toggle input { opacity:0; width:0; height:0; position:absolute; }
        .va-fb-wrap .va-toggle-slider { position:absolute; inset:0; background:rgba(255,255,255,.2); border-radius:20px; transition:.2s; cursor:pointer; }
        .va-fb-wrap .va-toggle-slider:before { content:''; position:absolute; width:14px; height:14px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
        .va-fb-wrap input:checked + .va-toggle-slider { background:#27ae60; }
        .va-fb-wrap input:checked + .va-toggle-slider--red { background:#c0392b !important; }
        .va-fb-wrap .va-toggle-slider--red { background:rgba(255,255,255,.2) !important; }
        .va-fb-wrap input:checked + .va-toggle-slider:before { transform:translateX(16px); }
        /* Törlés gomb */
        .va-fb-delete-btn { background:none; border:none; cursor:pointer; font-size:16px; color:#c0392b !important; padding:2px 5px; border-radius:4px; flex-shrink:0; line-height:1; }
        .va-fb-delete-btn:hover { background:#fdecea; }
        /* Mező hozzáadás panel */
        .va-fb-add-panel { margin-top:18px; background:rgba(255,255,255,.04); border:1px dashed rgba(255,255,255,.18); border-radius:6px; padding:14px 16px; }
        .va-fb-add-panel h4 { margin:0 0 12px; font-size:13px; font-weight:700; color:#fff !important; }
        .va-fb-add-row { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
        .va-fb-add-row input, .va-fb-add-row select { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.15); color:#fff !important; border-radius:4px; padding:6px 9px; font-size:13px; }
        .va-fb-add-row input { flex:1; min-width:120px; }
        .va-fb-add-btn { background:#2980b9 !important; border-color:#1a5f8a !important; color:#fff !important; font-weight:600 !important; white-space:nowrap; }
        .va-fb-add-btn:hover { background:#1a5f8a !important; }
        .va-fb-actions { margin-top:18px; display:flex; gap:10px; flex-wrap:wrap; }
        .va-fb-save-btn  { background:#c0392b !important; border-color:#a93226 !important; color:#fff !important; font-weight:700 !important; padding:8px 24px !important; }
        .va-fb-save-btn:hover { background:#a93226 !important; }
        .va-fb-reset-btn { border-color:#bbb !important; color:#555 !important; }
        .va-fb-custom-badge { font-size:10px; background:#2980b9; color:#fff !important; border-radius:3px; padding:1px 5px; margin-left:4px; vertical-align:middle; }
        </style>

        <script>
        jQuery(function($){
            const list = document.getElementById('va-fb-sortable');
            if (!list) return;

            // ── Drag & drop ──────────────────────────────
            if (typeof Sortable !== 'undefined') {
                Sortable.create(list, { handle: '.va-fb-handle', animation: 150 });
            } else {
                let dragged = null;
                list.querySelectorAll('.va-fb-row').forEach(row => {
                    row.setAttribute('draggable','true');
                    row.addEventListener('dragstart', e => { dragged = row; row.style.opacity='.4'; });
                    row.addEventListener('dragend',   e => { dragged = null; row.style.opacity=''; });
                    row.addEventListener('dragover',  e => { e.preventDefault(); });
                    row.addEventListener('drop', e => {
                        e.preventDefault();
                        if (dragged && dragged !== row) {
                            const rows = [...list.querySelectorAll('.va-fb-row')];
                            if (rows.indexOf(dragged) < rows.indexOf(row)) list.insertBefore(dragged, row.nextSibling);
                            else list.insertBefore(dragged, row);
                        }
                    });
                });
            }

            // ── Enabled toggle → row dimming ─────────────
            list.addEventListener('change', function(e){
                if ($(e.target).hasClass('va-fb-enable-cb')) {
                    $(e.target).closest('.va-fb-row').toggleClass('va-fb-row--disabled', !e.target.checked);
                }
            });

            // ── Mező törlése ─────────────────────────────
            list.addEventListener('click', function(e){
                const btn = $(e.target).closest('.va-fb-delete-btn');
                if (!btn.length) return;
                if (!confirm('Biztosan törlöd ezt a mezőt?')) return;
                btn.closest('.va-fb-row').remove();
            });

            // ── Új mező hozzáadása ────────────────────────
            $('#va-fb-add-btn').on('click', function(){
                const label = $('#va-fb-new-label').val().trim();
                const ph    = $('#va-fb-new-ph').val().trim();
                const type  = $('#va-fb-new-type').val();
                if (!label) { alert('Add meg a mező feliratát!'); return; }

                const key = 'custom_' + Date.now();
                const typeIcons = {text:'✏️',email:'📧',tel:'📱',number:'🔢',textarea:'📝',select:'🔽',checkbox:'☑️'};
                const icon = typeIcons[type] || '✏️';
                const showPh = !['checkbox','select'].includes(type);

                const row = $('<div class="va-fb-row va-fb-row--custom"></div>');
                row.append('<input type="hidden" name="field_order[]" value="' + key + '">');
                row.append('<span class="va-fb-handle" title="Húzd">⠿</span>');
                row.append('<span class="va-fb-type-icon">' + icon + '</span>');

                let inputs = '<div class="va-fb-row-inputs">';
                inputs += '<div class="va-fb-input-group"><label>Felirat</label>';
                inputs += '<input type="text" name="field_label[' + key + ']" value="' + $('<div>').text(label).html() + '" class="va-fb-label-input"></div>';
                if (showPh) {
                    inputs += '<div class="va-fb-input-group"><label>Placeholder</label>';
                    inputs += '<input type="text" name="field_ph[' + key + ']" value="' + $('<div>').text(ph).html() + '" class="va-fb-ph-input"></div>';
                }
                inputs += '<div class="va-fb-input-group"><label>Típus</label>';
                inputs += '<input type="hidden" name="field_type[' + key + ']" value="' + type + '"></div>';
                inputs += '</div>';
                row.append(inputs);

                let controls = '<div class="va-fb-row-controls">';
                controls += '<label class="va-fb-ctrl-label"><span>Kötelező</span>';
                controls += '<input type="hidden" name="field_required[' + key + ']" value="0">';
                controls += '<label class="va-toggle va-fb-toggle-req"><input type="checkbox" name="field_required[' + key + ']" value="1"><span class="va-toggle-slider va-toggle-slider--red"></span></label></label>';
                controls += '<label class="va-fb-ctrl-label"><span>Aktív</span>';
                controls += '<input type="hidden" name="field_enabled[' + key + ']" value="0">';
                controls += '<label class="va-toggle"><input type="checkbox" name="field_enabled[' + key + ']" value="1" checked class="va-fb-enable-cb"><span class="va-toggle-slider"></span></label></label>';
                controls += '<button type="button" class="va-fb-delete-btn" title="Törlés">🗑</button>';
                controls += '</div>';
                row.append(controls);

                row.append('<span class="va-fb-custom-badge">egyedi</span>');
                list.appendChild(row[0]);

                $('#va-fb-new-label').val('').focus();
                $('#va-fb-new-ph').val('');
            });

            // Enter = hozzáad
            $('#va-fb-new-label, #va-fb-new-ph').on('keydown', function(e){
                if (e.key === 'Enter') { e.preventDefault(); $('#va-fb-add-btn').click(); }
            });
        });
        </script>
        <?php

        // Reset kezelése
        if ( isset( $_GET['va_reset_form'], $_GET['_wpnonce'] ) ) {
            $r_form = sanitize_key( (string) $_GET['va_reset_form'] );
            if ( wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_GET['_wpnonce'] ) ), 'va_reset_form_' . $r_form ) && array_key_exists( $r_form, $forms ) ) {
                delete_option( 'va_form_config_' . $r_form );
                wp_safe_redirect( add_query_arg( [ 'page' => 'va-form-builder', 'form' => $r_form, 'updated' => '1' ], admin_url( 'admin.php' ) ) );
                exit;
            }
        }
    }
}
