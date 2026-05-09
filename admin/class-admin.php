<?php
/**
 * Admin főosztály: menük, assets, dark SaaS shell (sidebar + topbar)
 */
if ( ! defined( "ABSPATH" ) ) exit;

class VA_Admin {

    public static function init() {
        add_action( "admin_menu",            [ __CLASS__, "register_menus" ] );
        add_action( "admin_enqueue_scripts", [ __CLASS__, "enqueue" ] );
        add_filter( "admin_body_class",      [ __CLASS__, "body_class" ] );
        add_action( "in_admin_header",       [ __CLASS__, "render_shell" ] );
        add_action( "admin_head",            [ __CLASS__, "inject_admin_css" ] );
        add_action( "admin_bar_menu",        [ __CLASS__, "register_admin_bar" ], 99 );
    }

    /* ── WordPress Admin Bar menü ───────────────────────────── */
    public static function register_admin_bar( WP_Admin_Bar $admin_bar ): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $menu_title = 'LocalBomb';

        $admin_bar->add_node( [
            'id'    => 'va-admin-root',
            'title' => $menu_title,
            'href'  => admin_url( 'admin.php?page=vadaszapro' ),
            'meta'  => [ 'class' => 'va-admin-root' ],
            'order' => 5,
        ] );

        $items = [
            [ 'id' => 'va-ab-dashboard',  'title' => '📊 Irányítópult',    'page' => 'vadaszapro-dashboard' ],
            [ 'id' => 'va-ab-altalanos',  'title' => '⚙️ Általános',        'page' => 'vadaszapro' ],
            [ 'id' => 'va-ab-legal',      'title' => '🛡️ Adatvédelem + ÁSZF','page' => 'vadaszapro-legal' ],
            [ 'id' => 'va-ab-design',     'title' => '🎨 Design',           'page' => 'vadaszapro-design' ],
            [ 'id' => 'va-ab-search',     'title' => '🔎 Kereső',           'page' => 'vadaszapro-search' ],
            [ 'id' => 'va-ab-hirdetes',   'title' => '🧭 Hirdetés állítás', 'page' => 'vadaszapro-hirdetes' ],
            [ 'id' => 'va-ab-felhasznalo','title' => '👥 Felhasználók',     'page' => 'vadaszapro-users' ],
            [ 'id' => 'va-ab-stats',      'title' => '📈 Statisztika',      'page' => 'vadaszapro-stats' ],
            [ 'id' => 'va-ab-layout',     'title' => '📐 Layout Állító',    'page' => 'vadaszapro-layout' ],
            [ 'id' => 'va-ab-customcode', 'title' => '💻 Egyedi kód',       'page' => 'vadaszapro-customcode' ],
        ];

        foreach ( $items as $item ) {
            $admin_bar->add_node( [
                'id'     => $item['id'],
                'parent' => 'va-admin-root',
                'title'  => $item['title'],
                'href'   => admin_url( 'admin.php?page=' . $item['page'] ),
            ] );
        }
    }

    /* ── Dinamikus CSS változók injektálása ─────────────────── */
    public static function inject_admin_css(): void {
        if ( ! self::is_va_page() ) return;

        $font_stacks = [
            'system'        => 'system-ui,-apple-system,sans-serif',
            'inter'         => '"Inter",system-ui,sans-serif',
            'roboto'        => '"Roboto",system-ui,sans-serif',
            'montserrat'    => '"Montserrat",system-ui,sans-serif',
            'nunito'        => '"Nunito",system-ui,sans-serif',
            'poppins'       => '"Poppins",system-ui,sans-serif',
            'raleway'       => '"Raleway",system-ui,sans-serif',
            'dm-sans'       => '"DM Sans",system-ui,sans-serif',
            'manrope'       => '"Manrope",system-ui,sans-serif',
            'work-sans'     => '"Work Sans",system-ui,sans-serif',
            'rubik'         => '"Rubik",system-ui,sans-serif',
            'source-sans-3' => '"Source Sans 3",system-ui,sans-serif',
            'fira-sans'     => '"Fira Sans",system-ui,sans-serif',
            'oswald'        => '"Oswald",system-ui,sans-serif',
        ];

        $g = static fn( string $k, string $d ) => (string) ( get_option( $k, $d ) ?: $d );
        $font_slug  = $g( 'va_ap_font', 'montserrat' );
        $font_stack = $font_stacks[ $font_slug ] ?? $font_stacks['montserrat'];

        $vars  = ':root{';
        $vars .= '--va-bg:'        . esc_attr( $g( 'va_ap_color_bg',      '#070709' ) ) . ';';
        $vars .= '--va-bg2:'       . esc_attr( $g( 'va_ap_color_bg2',     '#0d0d11' ) ) . ';';
        $vars .= '--va-bg3:'       . esc_attr( $g( 'va_ap_color_bg3',     '#111118' ) ) . ';';
        $vars .= '--va-bg4:'       . esc_attr( $g( 'va_ap_color_bg4',     '#161620' ) ) . ';';
        $vars .= '--va-text:'      . esc_attr( $g( 'va_ap_color_text',    '#e8e8f0' ) ) . ';';
        $vars .= '--va-muted:'     . $g( 'va_ap_color_muted',  'rgba(255,255,255,.45)' ) . ';';
        $vars .= '--va-accent:'    . esc_attr( $g( 'va_ap_color_accent',  '#ff2020' ) ) . ';';
        $vars .= '--va-accent2:'   . esc_attr( $g( 'va_ap_color_accent2', '#ff5050' ) ) . ';';
        $vars .= '--va-border:'    . $g( 'va_ap_color_border',  'rgba(255,255,255,.07)' ) . ';';
        $vars .= '--va-border2:'   . $g( 'va_ap_color_border2', 'rgba(255,255,255,.12)' ) . ';';
        $vars .= '--va-sidebar-w:' . (int) $g( 'va_ap_sidebar_width', '230' ) . 'px;';
        $vars .= '--va-topbar-h:'  . (int) $g( 'va_ap_topbar_height', '60' )  . 'px;';
        $vars .= '--va-radius:'    . (int) $g( 'va_ap_radius',    '12' ) . 'px;';
        $vars .= '--va-radius-sm:' . (int) $g( 'va_ap_radius_sm', '8' )  . 'px;';
        $font_size  = max( 10, min( 20, (int) $g( 'va_ap_font_size', '13' ) ) );
        $vars .= '--va-font:'      . $font_stack . ';';
        $vars .= '--va-font-size:' . $font_size . 'px;';
        $vars .= '}';

        echo '<style id="va-admin-theme-vars">' . $vars . '</style>' . "\n";

        // Google Font betöltése közvetlen <link> taggal (megbízhatóbb mint wp_enqueue_style)
        $gf_map = self::get_google_font_map();
        if ( isset( $gf_map[ $font_slug ] ) ) {
            $gf_url = esc_url( 'https://fonts.googleapis.com/css2?family=' . $gf_map[ $font_slug ] . '&display=swap' );
            echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
            echo '<link rel="stylesheet" href="' . $gf_url . '">' . "\n";
        }

        // TinyMCE editor sötétítés az admin hirdetés szerkesztőben
        echo '<style id="va-tinymce-dark">
/* Wrapper és tab gombok */
.wp-editor-wrap { border-color: rgba(255,255,255,.12) !important; border-radius: 6px; overflow: hidden; }
.wp-editor-tabs .wp-switch-editor { background: #1a1a1a !important; color: #aaa !important; border-color: rgba(255,255,255,.12) !important; }
.wp-editor-tabs .wp-switch-editor:hover { background: #2a2a2a !important; color: #e8e8e8 !important; }

/* Média gomb sor */
.wp-media-buttons { background: #111 !important; padding: 4px 8px; border-bottom: 1px solid rgba(255,255,255,.1) !important; }
.wp-media-buttons .button { background: #222 !important; color: #ddd !important; border-color: rgba(255,255,255,.2) !important; }
.wp-media-buttons .button:hover { background: #2a2a2a !important; color: #fff !important; }

/* TinyMCE 5+ (tox) */
.tox-tinymce { border-color: rgba(255,255,255,.12) !important; border-radius: 0 !important; }
.tox .tox-toolbar-overlord,
.tox .tox-toolbar__primary,
.tox .tox-toolbar,
.tox .tox-toolbar-overlord .tox-toolbar:not(.tox-toolbar--scrolling):first-child { background: #1a1a1a !important; border-bottom: 1px solid rgba(255,255,255,.1) !important; }
.tox .tox-toolbar__group { border-color: rgba(255,255,255,.1) !important; }
.tox .tox-tbtn svg { fill: #bbb !important; }
.tox .tox-tbtn:hover { background: #2a2a2a !important; }
.tox .tox-tbtn--active { background: #333 !important; }
.tox .tox-tbtn__select-label, .tox .tox-tbtn { color: #ccc !important; }
.tox .tox-statusbar { background: #1a1a1a !important; border-top: 1px solid rgba(255,255,255,.1) !important; color: #666 !important; }
.tox .tox-statusbar__path-item, .tox .tox-statusbar__wordcount { color: #666 !important; }
.tox-edit-area { background: #111 !important; }
.tox-edit-area__iframe { background: #111 !important; }

/* Quicktags */
.quicktags-toolbar { background: #1a1a1a !important; border-color: rgba(255,255,255,.1) !important; }
.quicktags-toolbar input.ed_button { background: #252525 !important; color: #ccc !important; border-color: rgba(255,255,255,.15) !important; border-radius: 3px; }
.quicktags-toolbar input.ed_button:hover { background: #333 !important; color: #fff !important; }
</style>' . "\n";
    }

    /* ── Google Font map ────────────────────────────────────── */
    private static function get_google_font_map(): array {
        return [
            'inter'         => 'Inter:wght@400;500;600;700;800;900',
            'roboto'        => 'Roboto:wght@400;500;700;900',
            'montserrat'    => 'Montserrat:wght@400;500;600;700;800;900',
            'nunito'        => 'Nunito:wght@400;500;600;700;800;900',
            'poppins'       => 'Poppins:wght@400;500;600;700;800;900',
            'raleway'       => 'Raleway:wght@400;500;600;700;800;900',
            'dm-sans'       => 'DM+Sans:wght@400;500;700;900',
            'manrope'       => 'Manrope:wght@400;500;600;700;800',
            'work-sans'     => 'Work+Sans:wght@400;500;600;700;800;900',
            'rubik'         => 'Rubik:wght@400;500;700;900',
            'source-sans-3' => 'Source+Sans+3:wght@400;500;600;700;800;900',
            'fira-sans'     => 'Fira+Sans:wght@400;500;600;700;800;900',
            'oswald'        => 'Oswald:wght@400;500;600;700',
        ];
    }

    /* ── Body class ─────────────────────────────────────────── */
    public static function body_class( string $classes ): string {
        if ( self::is_va_page() ) {
            $classes .= " va-admin-page";
        }
        return $classes;
    }

    /* ── Oldal detekció ─────────────────────────────────────── */
    private static function is_va_page(): bool {
        $screen = function_exists( "get_current_screen" ) ? get_current_screen() : null;
        if ( ! $screen ) return false;
        $id = $screen->id ?? "";
        return (
            strpos( $id, "vadaszapro" ) !== false ||
            strpos( $id, "vadaszapro-listings" ) !== false ||
            strpos( $id, "vadaszapro-listing-edit" ) !== false ||
            strpos( $id, "va-form-builder" ) !== false ||
            strpos( $id, "va_listing" ) !== false ||
            strpos( $id, "va_auction" ) !== false
        );
    }

    /* ── Sidebar + Topbar HTML ──────────────────────────────── */
    public static function render_shell(): void {
        if ( ! self::is_va_page() ) return;

        $current_user   = wp_get_current_user();
        $avatar         = get_avatar( $current_user->ID, 32 );
        $logout_url     = wp_logout_url( admin_url() );
        $ap_panel_name  = (string) ( get_option( 'va_ap_panel_name', 'weingartnerauto.hu' ) ?: 'weingartnerauto.hu' );
        if ( in_array( trim( $ap_panel_name ), [ 'VadászApró', 'Vadaszapro', 'Weingartner Auto' ], true ) ) {
            $ap_panel_name = 'weingartnerauto.hu';
        }
        $ap_panel_icon  = (string) ( get_option( 'va_ap_panel_icon', '🎯' ) ?: '🎯' );
        $ap_logo_url    = (string) get_option( 'va_ap_logo_url', '' );
        $ap_logo_height = (int) ( get_option( 'va_ap_logo_height', 32 ) ?: 32 );

        $page    = sanitize_key( $_GET["page"] ?? "" );
        $pt      = sanitize_key( $_GET["post_type"] ?? "" );
        $screen  = function_exists( "get_current_screen" ) ? get_current_screen() : null;
        $scr_id  = $screen ? ( $screen->id ?? "" ) : "";

        $pending = wp_count_posts( "va_listing" )->pending ?? 0;
        $auctions_enabled = function_exists( "va_auctions_enabled" ) && va_auctions_enabled();

        /* Topbar cím térkép */
        $titles = [
            "vadaszapro-dashboard"    => "Irányítópult",
            "vadaszapro"              => "Általános beállítások",
            "vadaszapro-legal"        => "Adatvédelem + ÁSZF",
            "vadaszapro-design"       => "Design beállítások",
            "vadaszapro-search"       => "Kereső beállítások",
            "vadaszapro-layout"       => "Layout Állító",
            "vadaszapro-header-footer"=> "Fejléc & Lábléc",
            "vadaszapro-hero"         => "Hero szekció",
            "vadaszapro-tools"        => "Export / Import",
            "vadaszapro-reklam"       => "Reklámzónák",
            "vadaszapro-hirdetes"     => "Hirdetés állítás",
            "vadaszapro-aukcio"       => "Aukció beállítások",
            "vadaszapro-emails"       => "Email sablonok",
            "vadaszapro-users"        => "Felhasználók",
            "va-form-builder"         => "Form Szerkesztő",
            "vadaszapro-adminpanel"   => "Admin Panel beállítások",
            "vadaszapro-single-designer" => "Termékoldal Designer",
            "vadaszapro-social"        => "Social Media beállítások",
            "vadaszapro-stats"        => "Statisztika",
            "vadaszapro-plans"        => "Csomag beállítások",
            "vadaszapro-arkartyak"    => "Árkártyák szerkesztő",
            "vadaszapro-oldalak"      => "Oldalszerkesztő",
        ];
        $titles["vadaszapro-listings"]     = "Hirdetések";
        $titles["vadaszapro-listing-edit"] = "Hirdetés szerkesztése";
        if ( $page === "vadaszapro-listings" || $page === "vadaszapro-listing-edit" ) {
            $topbar_title = ( $page === "vadaszapro-listing-edit" ) ? ( isset( $_GET['id'] ) ? 'Hirdetés szerkesztése' : 'Új hirdetés' ) : 'Hirdetések';
        } elseif ( $pt === "va_listing" ) {
            $topbar_title = "Hirdetések";
        } elseif ( $pt === "va_auction" ) {
            $topbar_title = "Aukciók";
        } else {
            $topbar_title = $titles[ $page ] ?? "weingartnerauto.hu Admin";
        }

        ?>
        <div id="va-sidebar">
            <a href="<?php echo esc_url( admin_url( "admin.php?page=vadaszapro-dashboard" ) ); ?>" class="va-sb-logo">
                <div class="va-sb-logo__icon"><?php if ( $ap_logo_url ): ?><img src="<?php echo esc_url( $ap_logo_url ); ?>" style="height:<?php echo $ap_logo_height; ?>px;max-width:100%;object-fit:contain;display:block;" alt=""><?php else: echo esc_html( $ap_panel_icon ); ?><?php endif; ?></div>
                <div class="va-sb-logo__name">
                    <?php echo esc_html( $ap_panel_name ); ?>
                    <small>Admin Panel</small>
                </div>
            </a>

            <nav class="va-sb-nav">

                <?php self::sb_item( "📊", "Irányítópult", admin_url( "admin.php?page=vadaszapro-dashboard" ), $page === "vadaszapro-dashboard" ); ?>

                <?php self::sb_item( "📋", "Hirdetések", admin_url( "admin.php?page=vadaszapro-listings" ), $page === "vadaszapro-listings" || $page === "vadaszapro-listing-edit" || $pt === "va_listing" || str_contains( $scr_id, "va_listing" ), $pending > 0 ? (int)$pending : 0 ); ?>

                <?php if ( $auctions_enabled ): ?>
                <?php self::sb_item( "🔨", "Aukciók", admin_url( "edit.php?post_type=va_auction" ), $pt === "va_auction" || str_contains( $scr_id, "va_auction" ) ); ?>
                <?php endif; ?>

                <?php self::sb_item( "👥", "Felhasználók", admin_url( "admin.php?page=vadaszapro-users" ), $page === "vadaszapro-users" ); ?>

                <span class="va-sb-sep">Beállítások</span>

                <?php self::sb_item( "⚙️", "Általános", admin_url( "admin.php?page=vadaszapro" ), $page === "vadaszapro" ); ?>
                <?php self::sb_item( "🛡️", "Adatvédelem + ÁSZF", admin_url( "admin.php?page=vadaszapro-legal" ), $page === "vadaszapro-legal" ); ?>
                <?php self::sb_item( "🎨", "Design", admin_url( "admin.php?page=vadaszapro-design" ), $page === "vadaszapro-design" ); ?>
                <?php self::sb_item( "🔎", "Kereső", admin_url( "admin.php?page=vadaszapro-search" ), $page === "vadaszapro-search" ); ?>
                <?php self::sb_item( "📋", "Keresési oldal", admin_url( "admin.php?page=vadaszapro-listing-search" ), $page === "vadaszapro-listing-search" ); ?>
                <?php self::sb_item( "🎬", "Hero szekció", admin_url( "admin.php?page=vadaszapro-hero" ), $page === "vadaszapro-hero" ); ?>
                <?php self::sb_item( "📐", "Layout Állító", admin_url( "admin.php?page=vadaszapro-layout" ), $page === "vadaszapro-layout" ); ?>
                <?php self::sb_item( "🧭", "Hirdetés állítás", admin_url( "admin.php?page=vadaszapro-hirdetes" ), $page === "vadaszapro-hirdetes" ); ?>
                <?php self::sb_item( "🗂️", "Fejléc & Lábléc", admin_url( "admin.php?page=vadaszapro-header-footer" ), $page === "vadaszapro-header-footer" ); ?>
                <?php self::sb_item( "🧩", "Form szerkesztő", admin_url( "admin.php?page=va-form-builder" ), $page === "va-form-builder" ); ?>
                <?php self::sb_item( "🖥️", "Admin Panel", admin_url( "admin.php?page=vadaszapro-adminpanel" ), $page === "vadaszapro-adminpanel" ); ?>
                <?php self::sb_item( "🧱", "Termékoldal", admin_url( "admin.php?page=vadaszapro-single-designer" ), $page === "vadaszapro-single-designer" ); ?>
                <?php self::sb_item( "📱", "Social Media", admin_url( "admin.php?page=vadaszapro-social" ), $page === "vadaszapro-social" ); ?>
                <?php self::sb_item( "💼", "Csomagok", admin_url( "admin.php?page=vadaszapro-plans" ), $page === "vadaszapro-plans" ); ?>
                <?php self::sb_item( "💳", "Árkártyák", admin_url( "admin.php?page=vadaszapro-arkartyak" ), $page === "vadaszapro-arkartyak" ); ?>
                <?php self::sb_item( "🏷️", "Pill & Badge", admin_url( "admin.php?page=vadaszapro-pills" ), $page === "vadaszapro-pills" ); ?>
                <?php self::sb_item( "🃏", "Kártyaszerkesztő", admin_url( "admin.php?page=vadaszapro-cards" ), $page === "vadaszapro-cards" ); ?>

                <?php if ( $auctions_enabled ): ?>
                <?php self::sb_item( "🔨", "Aukció beállítások", admin_url( "admin.php?page=vadaszapro-aukcio" ), $page === "vadaszapro-aukcio" ); ?>
                <?php endif; ?>
                <?php self::sb_item( "📧", "Email sablonok", admin_url( "admin.php?page=vadaszapro-emails" ), $page === "vadaszapro-emails" ); ?>

                <?php self::sb_item( "📄", "Oldalak", admin_url( "admin.php?page=vadaszapro-oldalak" ), $page === "vadaszapro-oldalak" ); ?>
                <?php
                    $i18n_langs   = VA_Settings_Page::get_languages();
                    $i18n_default = (string) get_option( 'va_default_lang', 'hu' );
                    $i18n_flag    = $i18n_langs[ $i18n_default ]['flag'] ?? '🌍';
                ?>
                <?php self::sb_item( $i18n_flag, "Fordítás", admin_url( "admin.php?page=vadaszapro-i18n" ), $page === "vadaszapro-i18n" ); ?>
                <?php self::sb_item( "💻", "Egyedi kód", admin_url( "admin.php?page=vadaszapro-customcode" ), $page === "vadaszapro-customcode" ); ?>
                <?php self::sb_item( "🗂️", "Verziókövetés", admin_url( "admin.php?page=vadaszapro-versions" ), $page === "vadaszapro-versions" ); ?>

                <span class="va-sb-sep">Tartalom</span>

                <?php self::sb_item( "📢", "Reklámzónák", admin_url( "admin.php?page=vadaszapro-reklam" ), $page === "vadaszapro-reklam" ); ?>
                <?php self::sb_item( "📈", "Statisztika", admin_url( "admin.php?page=vadaszapro-stats" ), $page === "vadaszapro-stats" ); ?>
                <?php self::sb_item( "🔧", "Export / Import", admin_url( "admin.php?page=vadaszapro-tools" ), $page === "vadaszapro-tools" ); ?>

                <span class="va-sb-sep">Külső</span>

                <a href="<?php echo esc_url( home_url( "/" ) ); ?>" target="_blank" class="va-sb-item">
                    <span class="va-sb-item__icon">🌐</span>
                    <span class="va-sb-item__label">Weboldal ↗</span>
                </a>

            </nav>

            <div class="va-sb-footer">
                <div class="va-sb-footer__avatar"><?php echo $avatar; ?></div>
                <div class="va-sb-footer__user">
                    <span class="va-sb-footer__name"><?php echo esc_html( $current_user->display_name ); ?></span>
                    <span class="va-sb-footer__role">Adminisztrátor</span>
                </div>
                <a href="<?php echo esc_url( $logout_url ); ?>" class="va-sb-logout" title="Kijelentkezés">⏻</a>
            </div>
        </div>

        <div id="va-topbar">
            <div class="va-topbar__title">
                <?php echo esc_html( $topbar_title ); ?>
                <small>weingartnerauto.hu</small>
            </div>
            <div class="va-topbar__actions">
                <a href="<?php echo esc_url( admin_url( "admin.php?page=vadaszapro-listing-edit" ) ); ?>" class="va-topbar__btn va-topbar__btn--primary">
                    + Új hirdetés
                </a>
                <a href="<?php echo esc_url( home_url( "/" ) ); ?>" target="_blank" class="va-topbar__btn">
                    🌐 Weboldal
                </a>
            </div>
        </div>
        <?php
    }

    private static function sb_item( string $icon, string $label, string $href, bool $active = false, int $badge = 0 ): void {
        $cls = "va-sb-item" . ( $active ? " active" : "" );
        echo "<a href=\"" . esc_url( $href ) . "\" class=\"{$cls}\">";
        echo "<span class=\"va-sb-item__icon\">{$icon}</span>";
        echo "<span class=\"va-sb-item__label\">" . esc_html( $label ) . "</span>";
        if ( $badge > 0 ) echo "<span class=\"va-sb-item__badge\">{$badge}</span>";
        echo "</a>";
    }

    /* ── Menük ──────────────────────────────────────────────── */
    public static function register_menus() {
        $auctions_enabled = function_exists( "va_auctions_enabled" ) ? va_auctions_enabled() : true;
        $menu_title = (string) get_option( 'va_site_name', 'InfoStudio Second Hand Theme' );
        if ( $menu_title === '' ) $menu_title = 'InfoStudio Second Hand Theme';

        add_menu_page(
            $menu_title,
            $menu_title,
            "manage_options",
            "vadaszapro",
            [ VA_Settings_Page::class, "render_general" ],
            "dashicons-store",
            4
        );

        // Dashboard – első almenü
        add_submenu_page( "vadaszapro", "Irányítópult",  "📊 Irányítópult",  "manage_options", "vadaszapro-dashboard",       [ VA_Dashboard::class,     "render"                  ] );
        add_submenu_page( "vadaszapro", "Általános",     "⚙️ Általános",     "manage_options", "vadaszapro",                 [ VA_Settings_Page::class, "render_general"          ] );
        add_submenu_page( "vadaszapro", "Adatvédelem + ÁSZF", "🛡️ Adatvédelem + ÁSZF", "manage_options", "vadaszapro-legal", [ VA_Settings_Page::class, "render_legal_pages" ] );
        add_submenu_page( "vadaszapro", "Design",        "🎨 Design",        "manage_options", "vadaszapro-design",          [ VA_Settings_Page::class, "render_design"            ] );
        add_submenu_page( "vadaszapro", "Kereső",        "🔎 Kereső",        "manage_options", "vadaszapro-search",          [ VA_Settings_Page::class, "render_search_designer"   ] );
        add_submenu_page( "vadaszapro", "Keresési oldal", "📋 Keresési oldal", "manage_options", "vadaszapro-listing-search", [ VA_Settings_Page::class, "render_listing_search_designer" ] );
        add_submenu_page( "vadaszapro", "Hero szekció",  "🖼️ Hero szekció",  "manage_options", "vadaszapro-hero",            [ VA_Settings_Page::class, "render_hero"              ] );
        add_submenu_page( "vadaszapro", "Layout Állító", "📐 Layout Állító", "manage_options", "vadaszapro-layout",          [ VA_Settings_Page::class, "render_layout_builder"   ] );
        add_submenu_page( "vadaszapro", "Fejléc + Lábléc", "📑 Fejléc + Lábléc", "manage_options", "vadaszapro-header-footer",[ VA_Settings_Page::class, "render_header_footer"    ] );
        add_submenu_page( "vadaszapro", "Export / Import", "🔄 Export / Import", "manage_options", "vadaszapro-tools",        [ VA_Settings_Page::class, "render_tools"             ] );
        add_submenu_page( "vadaszapro", "Reklámzónák",   "📢 Reklámzónák",   "manage_options", "vadaszapro-reklam",          [ VA_Settings_Page::class, "render_ad_zones"          ] );
        add_submenu_page( "vadaszapro", "Hirdetés állítás", "🧭 Hirdetés állítás", "manage_options", "vadaszapro-hirdetes", [ VA_Settings_Page::class, "render_listings" ] );
        if ( $auctions_enabled ) {
            add_submenu_page( "vadaszapro", "Aukciók",   "🔨 Aukciók",       "manage_options", "vadaszapro-aukcio",          [ VA_Settings_Page::class, "render_auctions"          ] );
        }
        add_submenu_page( "vadaszapro", "Email sablonok",   "📧 Email sablonok",   "manage_options", "vadaszapro-emails",      [ VA_Settings_Page::class, "render_emails"            ] );
        add_submenu_page( "vadaszapro", "Felhasználók",     "👥 Felhasználók",     "manage_options", "vadaszapro-users",       [ VA_Settings_Page::class, "render_users"             ] );
        add_submenu_page( "vadaszapro", "Form szerkesztő",  "🧩 Form szerkesztő",  "manage_options", "va-form-builder",        [ VA_Form_Builder::class,  "render"                   ] );
        add_submenu_page( "vadaszapro", "Admin Panel",      "🖥️ Admin Panel",      "manage_options", "vadaszapro-adminpanel",  [ VA_Settings_Page::class, "render_adminpanel"        ] );
        add_submenu_page( "vadaszapro", "Termékoldal",      "📦 Termékoldal",      "manage_options", "vadaszapro-single-designer", [ VA_Settings_Page::class, "render_single_designer" ] );
        add_submenu_page( "vadaszapro", "Social Media",     "🌐 Social Media",     "manage_options", "vadaszapro-social",      [ VA_Settings_Page::class, "render_social"            ] );
        add_submenu_page( "vadaszapro", "Csomagok",         "💼 Csomagok",         "manage_options", "vadaszapro-plans",       [ VA_Settings_Page::class, "render_plans"             ] );
        add_submenu_page( "vadaszapro", "Árkártyák",        "💳 Árkártyák",        "manage_options", "vadaszapro-arkartyak",   [ VA_Settings_Page::class, "render_price_cards"       ] );
        add_submenu_page( "vadaszapro", "Statisztika",      "📈 Statisztika",      "manage_options", "vadaszapro-stats",       [ VA_Settings_Page::class, "render_stats"             ] );
        add_submenu_page( "vadaszapro", "Pill / Badge stílusok", "🏷️ Pill & Badge", "manage_options", "vadaszapro-pills",       [ VA_Settings_Page::class, "render_pill_styles"       ] );
        add_submenu_page( "vadaszapro", "Kártyaszerkesztő",      "🃏 Kártyaszerkesztő", "manage_options", "vadaszapro-cards",    [ VA_Settings_Page::class, "render_card_designer"     ] );
        add_submenu_page( "vadaszapro", "Oldalszerkesztő",        "📄 Oldalak",        "manage_options", "vadaszapro-oldalak",        [ VA_Page_Builder::class,  "render"                  ] );
        // i18n – dinamikus zászló az aktív alapnyelvből
        $i18n_langs   = VA_Settings_Page::get_languages();
        $i18n_default = (string) get_option( 'va_default_lang', 'hu' );
        $i18n_flag    = $i18n_langs[ $i18n_default ]['flag'] ?? '🌍';
        add_submenu_page( "vadaszapro", "Többnyelvűség",     $i18n_flag . " Fordítás",    "manage_options", "vadaszapro-i18n",      [ VA_Settings_Page::class, "render_i18n"              ] );
        add_submenu_page( "vadaszapro", "Egyedi kód",        "💻 Egyedi kód",             "manage_options", "vadaszapro-customcode",[ VA_Settings_Page::class, "render_custom_code"       ] );
        add_submenu_page( "vadaszapro", "Verziókövetés",     "🗂️ Verziókövetés",         "manage_options", "vadaszapro-versions",  [ VA_Settings_Page::class, "render_version_control"   ] );
        // Hirdetés lista + szerkesztő (rejtett almenük – saját oldalaink)
        add_submenu_page( "vadaszapro", "Hirdetések lista",  "", "edit_posts", "vadaszapro-listings",     [ VA_Listing_Edit::class, "render_list" ] );
        add_submenu_page( "vadaszapro", "Hirdetés szerkesztő", "", "edit_posts", "vadaszapro-listing-edit", [ VA_Listing_Edit::class, "render_edit" ] );
    }

    /* ── Assets ─────────────────────────────────────────────── */
    public static function enqueue( $hook ) {
        $post_types = [ "va_listing" ];
        if ( function_exists( "va_auctions_enabled" ) && va_auctions_enabled() ) {
            $post_types[] = "va_auction";
        }

        $is_va_page = strpos( $hook, "vadaszapro" ) !== false
            || strpos( $hook, "va-form-builder" ) !== false
            || in_array( get_post_type(), $post_types, true )
            || ( isset( $_GET["post_type"] ) && in_array( $_GET["post_type"], $post_types, true ) );

        if ( ! $is_va_page ) return;

        // SortableJS a form szerkesztőhöz
        if ( strpos( $hook, "va-form-builder" ) !== false ) {
            wp_enqueue_script( "sortablejs", "https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js", [], "1.15.2", true );
        }

        // Chart.js – dashboard és stats oldalon
        if ( strpos( $hook, "vadaszapro-dashboard" ) !== false || strpos( $hook, "vadaszapro-stats" ) !== false ) {
            wp_enqueue_script( "chartjs", "https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js", [], "4.4.3", true );
        }

        wp_enqueue_media();
        wp_enqueue_style(  "wp-color-picker" );
        wp_enqueue_style(  "va-admin", VA_PLUGIN_URL . "admin/admin.css", [ "wp-color-picker" ], VA_VERSION );
        wp_enqueue_script( "va-admin", VA_PLUGIN_URL . "admin/admin.js", [ "jquery", "wp-color-picker" ], VA_VERSION, true );

        // Google Font – wp_enqueue_style is betölti (dupla fallback)
        $ap_font = sanitize_key( (string) ( get_option( 'va_ap_font', 'montserrat' ) ?: 'montserrat' ) );
        $gf_map  = self::get_google_font_map();
        if ( isset( $gf_map[ $ap_font ] ) ) {
            $gf_url = 'https://fonts.googleapis.com/css2?family=' . $gf_map[ $ap_font ] . '&display=swap';
            wp_enqueue_style( 'va-admin-font', esc_url_raw( $gf_url ), [], null );
        }
    }
}