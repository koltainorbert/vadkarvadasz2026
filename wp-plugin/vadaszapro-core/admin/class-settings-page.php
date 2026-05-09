<?php
/**
 * Settings oldal – minden beállítás egy helyen
 * Lapok: Általános | Reklámzónák | Hirdetések | Aukciók | Felhasználók | Statisztika
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Settings_Page {

    private static $defaults = [];

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'wp_head',    [ __CLASS__, 'output_pill_css' ], 99 );
        add_action( 'wp_head',    [ __CLASS__, 'output_card_css' ], 99 );
        add_action( 'admin_post_va_save_pill_styles',  [ __CLASS__, 'handle_save_pill_styles'  ] );
        add_action( 'admin_post_va_save_card_styles',  [ __CLASS__, 'handle_save_card_styles'  ] );
        add_action( 'wp_ajax_va_save_card_styles_ajax', [ __CLASS__, 'handle_save_card_styles_ajax' ] );
        add_action( 'admin_post_va_export_settings', [ __CLASS__, 'handle_export_settings' ] );
        add_action( 'admin_post_va_import_settings', [ __CLASS__, 'handle_import_settings' ] );
        add_action( 'admin_post_va_reset_settings',  [ __CLASS__, 'handle_reset_settings' ] );
        add_action( 'admin_post_va_apply_hf_preset', [ __CLASS__, 'handle_apply_hf_preset' ] );
        add_action( 'admin_post_va_apply_ap_preset',  [ __CLASS__, 'handle_apply_ap_preset'  ] );
        add_action( 'admin_post_va_apply_single_preset', [ __CLASS__, 'handle_apply_single_preset' ] );
        add_action( 'admin_post_va_apply_design_preset', [ __CLASS__, 'handle_apply_design_preset' ] );
        add_action( 'admin_post_va_save_nav_items',   [ __CLASS__, 'handle_save_nav_items'   ] );
        add_action( 'wp_head',    [ __CLASS__, 'output_custom_head' ], 1 );
        add_action( 'wp_head',    [ __CLASS__, 'output_custom_css'  ], 100 );
        add_action( 'wp_footer',  [ __CLASS__, 'output_custom_js'   ], 100 );
        add_action( 'admin_post_va_create_snapshot',  [ __CLASS__, 'handle_create_snapshot'  ] );
        add_action( 'admin_post_va_restore_snapshot', [ __CLASS__, 'handle_restore_snapshot' ] );
        add_action( 'admin_post_va_delete_snapshot',  [ __CLASS__, 'handle_delete_snapshot'  ] );
    }

    /* ══ Settings regisztráció ════════════════════════════ */
    public static function register_settings() {

        /* Általános */
        $general = [
            'va_site_name'           => 'weingartnerauto.hu',
            'va_site_description'    => 'Magyarország vadászati apróhirdetési oldala',
            'va_contact_email'       => get_option('admin_email'),
            'va_site_type'           => 'vadaszat',  // oldaltípus: vadaszat | jarmu | ingatlan | altalanos
            'va_enable_auctions'     => '1',
            'va_enable_login'        => '1',
            'va_enable_register'     => '1',
            'va_header_logo_height'  => 36,
            'va_hero_logo_height'    => 72,
            'va_hero_logo_position'  => 'left',
            'va_home_hero_align'     => 'left',
            'va_kategoria_hero_align'=> 'center',
            'va_tax_hero_align'      => 'center',
            'va_contact_hero_align'  => 'center',
            'va_listings_per_page'   => 20,
            'va_auto_publish_listings' => '0',  // 0=jóváhagyás szükséges, 1=azonnal él
            'va_listing_validity_days' => 60,   // hirdetés lejárata (nap) feladáskor
            'va_max_images_per_listing' => 10,
            'va_img_quality'            => 82,
            'va_img_max_width'          => 1920,
            'va_require_phone'       => '1',
            'va_maintenance_mode'    => '0',
            'va_maintenance_msg'     => 'Az oldal karbantartás alatt van.',
            'va_show_hunting_season_widget' => '1',
            'va_show_moon_widget'          => '1',
            'va_show_weather_widget'       => '1',
            'va_enable_hunting_calendar_page' => '1',
            'va_show_home_hunting_calendar' => '1',

            // Hirdetes kartya meta layout (lista/fooldal)
            'va_card_meta_rows'            => '1',
            'va_card_meta_col_gap'         => '12',
            'va_card_meta_row_gap'         => '2',
            'va_card_meta_stack_gap'       => '4',
            'va_card_meta_show_category'   => '0',
            'va_card_meta_show_county'     => '0',
            'va_card_meta_show_location'   => '1',
            'va_card_meta_show_views'      => '0',
            'va_card_meta_show_author'     => '0',
            'va_card_meta_show_date'       => '1',
            'va_card_show_boost_badge'     => '1',
            'va_card_meta_row_category'    => '1',
            'va_card_meta_row_county'      => '1',
            'va_card_meta_row_location'    => '1',
            'va_card_meta_row_views'       => '1',
            'va_card_meta_row_author'      => '1',
            'va_card_meta_row_date'        => '1',

            // Főoldal hero szövegek
            'va_home_hero_badge_text'        => 'Magyarország első vadászati hirdetőoldala',
            'va_home_hero_title_top'         => 'VadászBazár',
            'va_home_hero_title_bottom'      => 'és Apróhirdetés',
            'va_home_hero_sub_text'          => 'Magyarország első vadászati hirdetőoldala',
            'va_home_hero_primary_cta_text'  => '+ Hirdetés feladása',
            'va_home_hero_secondary_cta_text'=> 'Hirdetések böngészése →',
            'va_home_latest_label_text'      => 'ÚJ',
            'va_home_all_link_text'          => 'Összes →',
            'va_home_section_accent_color'   => '#e27019',

            // Kategória oldal hero szövegek
            'va_kategoria_hero_badge_text'   => 'Vadász Apróhirdetések',
            'va_kategoria_hero_title_top'    => 'Válassz',
            'va_kategoria_hero_title_bottom' => 'Kategóriát',
            'va_kategoria_hero_sub_text'     => 'Golyós puskáktól a trófea-alapzatokig – minden vadász felszerelésnél egy helyen',
            'va_kategoria_hero_stat1_label'  => 'Főkategória',
            'va_kategoria_hero_stat2_label'  => 'Aktív hirdetés',

            // Alkategória hero szövegek
            'va_tax_hero_badge_text'         => 'Kategória ajánló',
            'va_tax_hero_fallback_lead'      => 'A kiválasztott kategóriában böngészel, görgess tovább a friss ajánlatokért.',
            'va_tax_hero_count_suffix'       => 'hirdetés',

            // Kapcsolat oldal hero szövegek
            'va_contact_hero_badge_text'     => 'Kapcsolat',
            'va_contact_hero_title_text'     => 'Írj nekünk e-mailt',
            'va_contact_hero_lead_text'      => 'A kapcsolatfelvétel kizárólag e-mailben történik. Az itt elküldött üzenetek WordPress oldalon keresztül, a WP Mail SMTP bővítményen át jutnak el hozzánk.',

            // Kapcsolat oldal kártyák szövegei
            'va_contact_card1_title'         => 'Csak e-mailes megkeresés',
            'va_contact_card1_text'          => 'Telefonszámot és nyilvános közvetlen címet nem jelenítünk meg. Minden megkeresést ezen az űrlapon keresztül fogadunk.',
            'va_contact_card2_title'         => 'Mit írj meg?',
            'va_contact_card2_item1'         => 'melyik témában keresel minket',
            'va_contact_card2_item2'         => 'mi a kérdésed vagy problémád röviden',
            'va_contact_card2_item3'         => 'milyen e-mail címre válaszoljunk',
            'va_contact_card3_title'         => 'Technikai háttér',
            'va_contact_card3_text'          => 'Az üzenetküldés a WordPress wp_mail() rendszerén keresztül történik, így a küldést a WP Mail SMTP plugin kezeli.',

            // Kapcsolat oldal form szövegek
            'va_contact_form_title'          => 'Üzenet küldése',
            'va_contact_form_btn_text'       => 'Üzenet elküldése',
            'va_contact_form_label_name'     => 'Név',
            'va_contact_form_label_email'    => 'E-mail',
            'va_contact_form_label_phone'    => 'Telefonszám',
            'va_contact_form_label_subject'  => 'Tárgy',
            'va_contact_form_label_message'  => 'Üzenet',
            'va_contact_form_ph_name'        => 'Teljes neved',
            'va_contact_form_ph_email'       => 'pelda@email.hu',
            'va_contact_form_ph_phone'       => '+36 30 123 4567',
            'va_contact_form_ph_subject'     => 'Miben tudunk segíteni?',
            'va_contact_form_ph_message'     => 'Írd le röviden a kérdésedet vagy megkeresésed részleteit...',
            'va_contact_form_msg_success'    => 'Az üzenetedet elküldtük. Hamarosan e-mailben válaszolunk.',
            'va_contact_form_msg_invalid'    => 'Kérlek tölts ki minden kötelező mezőt érvényes adatokkal.',
            'va_contact_form_msg_error'      => 'Az üzenet küldése nem sikerült. Ellenőrizd az SMTP beállítást, majd próbáld újra.',
            'va_contact_form_msg_nonce'      => 'A kérés érvénytelen volt. Kérlek küldd el újra az űrlapot.',

            // Termékoldal (single) felirat opciók
            'va_sl_lbl_description'     => 'Leírás',
            'va_sl_lbl_details'         => 'Részletek',
            'va_sl_lbl_seller'          => 'Feladó',
            'va_sl_lbl_more_listings'   => 'Feladó további hirdetései',
            'va_sl_lbl_related'         => 'Hasonló hirdetések',
            'va_sl_lbl_phone_btn'       => '📞 Telefonszám megjelenítése',
            'va_sl_lbl_email_btn'       => '✉ E-mail üzenet küldése',
            'va_sl_lbl_watch_add'       => '☆ Mentés kedvencekbe',
            'va_sl_lbl_watch_remove'    => '★ Kedvencekből eltávolítás',
            'va_sl_lbl_share'           => 'Megosztás:',
            'va_sl_lbl_no_image'        => 'Nincs kép',
            'va_sl_lbl_zoom'            => 'Nagyítás',
            'va_sl_lbl_views_suffix'    => 'megtekintés',
            'va_sl_lbl_posted'          => 'Feladva:',
            'va_sl_lbl_expires'         => 'Lejár:',
            'va_sl_lbl_expired_label'   => 'Lejárt:',
            'va_sl_lbl_member_since_pre'=> 'Tag',
            'va_sl_lbl_member_since_suf'=> 'óta',
            'va_sl_lbl_condition_pre'   => 'Állapot:',
            'va_sl_lbl_demand'          => 'érdeklődő az elmúlt 24 órában figyelőlistájára vette',
            'va_sl_lbl_featured_pill'   => '⭐ Kiemelt',
        ];

        foreach ( $general as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Migráció: ha a korábbi alap 2 soros hely+dátum konfiguráció él, állítsuk 1 sorra.
        $is_old_default_card_layout =
            (string) get_option( 'va_card_meta_rows', '2' ) === '2'
            && (string) get_option( 'va_card_meta_show_category', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_county', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_location', '1' ) === '1'
            && (string) get_option( 'va_card_meta_show_views', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_author', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_date', '1' ) === '1'
            && (string) get_option( 'va_card_meta_row_location', '1' ) === '1'
            && (string) get_option( 'va_card_meta_row_date', '2' ) === '2';

        if ( $is_old_default_card_layout ) {
            update_option( 'va_card_meta_rows', '1' );
            update_option( 'va_card_meta_row_date', '1' );
        }

        /* Design (betűk + színek) */
        $design = [
            // Betűtípusok
            'va_font_global'        => 'system',
            'va_font_headings'      => 'montserrat',
            'va_font_header'        => 'montserrat',
            'va_font_content'       => 'source-sans-3',
            'va_font_footer'        => 'source-sans-3',

            // Globális színek
            'va_color_global_bg'     => '#060606',
            'va_color_global_text'   => '#ffffff',
            'va_color_global_muted'  => 'rgba(255,255,255,.65)',
            'va_color_global_accent' => '#ff0000',

            // Header
            'va_color_header_bg'     => 'rgba(6,4,4,.82)',
            'va_color_header_text'   => '#ffffff',
            'va_color_header_accent' => '#ff0000',

            // Tartalom
            'va_color_content_bg'       => '#060606',
            'va_color_content_text'     => '#ffffff',
            'va_color_content_headings' => '#ffffff',
            'va_color_content_links'    => '#ff4444',

            // Footer
            'va_color_footer_bg'       => '#0a0a0a',
            'va_color_footer_text'     => 'rgba(255,255,255,.72)',
            'va_color_footer_headings' => '#ffffff',
            'va_color_footer_links'    => '#ff4444',

            // Hero méretek (összes oldal)
            'va_size_home_hero_badge'      => 11,
            'va_size_home_hero_title'      => 64,
            'va_size_home_hero_sub'        => 19,
            'va_size_home_hero_btn'        => 15,
            'va_size_kat_hero_badge'       => 10,
            'va_size_kat_hero_title'       => 56,
            'va_size_kat_hero_sub'         => 15,
            'va_size_kat_hero_stat_num'    => 20,
            'va_size_kat_hero_stat_label'  => 10,
            'va_size_tax_hero_badge'       => 11,
            'va_size_tax_hero_title'       => 48,
            'va_size_tax_hero_lead'        => 16,
            'va_size_tax_hero_count'       => 14,
            'va_size_contact_hero_badge'   => 11,
            'va_size_contact_hero_title'   => 62,
            'va_size_contact_hero_lead'    => 16,

            // Hero sorközök
            'va_lh_home_hero_title'        => '1.05',
            'va_lh_home_hero_sub'          => '1.60',
            'va_lh_kat_hero_title'         => '1.06',
            'va_lh_kat_hero_sub'           => '1.70',

            // Hero alcím vastagság
            'va_weight_home_hero_sub'      => '400',
            'va_weight_kat_hero_sub'       => '400',
            'va_lh_tax_hero_title'         => '1.05',
            'va_lh_tax_hero_lead'          => '1.75',
            'va_lh_contact_hero_title'     => '1.02',
            'va_lh_contact_hero_lead'      => '1.80',

            // Fejléc elemek (méret + típus/súly)
            'va_size_header_brand'         => 18,
            'va_size_header_nav'           => 14,
            'va_size_header_search'        => 14,
            'va_size_header_btn'           => 13,
            'va_weight_header_brand'       => '800',
            'va_weight_header_nav'         => '600',

            // Lábléc elemek (méret + típus/súly)
            'va_size_footer_title'         => 13,
            'va_size_footer_link'          => 13,
            'va_size_footer_bottom'        => 12,
            'va_weight_footer_title'       => '700',
            'va_weight_footer_link'        => '500',

            // Hero badge színek
            'va_color_hero_badge_bg'           => 'rgba(6,6,6,.56)',
            'va_color_hero_badge_border'       => 'rgba(255,0,0,.55)',
            'va_color_hero_badge_text'         => '#ffffff',

            // Hero szöveg színek
            'va_color_hero_title'              => '#ffffff',
            'va_color_hero_sub'                => 'rgba(255,255,255,.80)',

            // Hero primary gomb színek
            'va_color_hero_btn_primary_bg'     => '#ff0000',
            'va_color_hero_btn_primary_hover'  => '#cc0000',
            'va_color_hero_btn_primary_text'   => '#ffffff',
            'va_color_hero_btn_primary_glow'   => 'rgba(255,0,0,.45)',

            // Hero ghost gomb színek
            'va_color_hero_btn_ghost_bg'       => 'rgba(255,255,255,.08)',
            'va_color_hero_btn_ghost_border'   => 'rgba(255,255,255,.22)',
            'va_color_hero_btn_ghost_hover'    => 'rgba(255,255,255,.15)',
            'va_color_hero_btn_ghost_text'     => '#ffffff',

            // Kapcsolat oldal színek – hero
            'va_contact_color_hero_title'        => '#ffffff',
            'va_contact_color_hero_lead'         => 'rgba(255,255,255,.72)',
            'va_contact_color_hero_badge_bg'     => 'rgba(6,6,6,.34)',
            'va_contact_color_hero_badge_border' => 'rgba(255,255,255,.14)',
            'va_contact_color_hero_badge_text'   => '#ffffff',
            'va_contact_color_hero_glow'         => '#ff0000',

            // Kapcsolat oldal színek – kártyák
            'va_contact_color_card_border'       => 'rgba(255,255,255,.08)',
            'va_contact_color_card_accent_border'=> 'rgba(255,0,0,.22)',
            'va_contact_color_card_icon'         => '#ff4040',
            'va_contact_color_card_icon_bg'      => 'rgba(255,0,0,.08)',
            'va_contact_color_card_icon_border'  => 'rgba(255,0,0,.18)',
            'va_contact_color_card_title'        => '#ffffff',
            'va_contact_color_card_mini'         => 'rgba(255,255,255,.82)',
            'va_contact_color_card_text'         => 'rgba(255,255,255,.68)',
            'va_contact_color_list_text'         => 'rgba(255,255,255,.74)',
            'va_contact_color_list_dot'          => '#ff0000',

            // Kapcsolat oldal színek – űrlap
            'va_contact_color_form_title'        => '#ffffff',
            'va_contact_color_form_label'        => 'rgba(255,255,255,.7)',
            'va_contact_color_input_bg'          => 'rgba(255,255,255,.04)',
            'va_contact_color_input_border'      => 'rgba(255,255,255,.1)',
            'va_contact_color_input_text'        => '#ffffff',
            'va_contact_color_input_focus_border'=> 'rgba(255,0,0,.45)',
            'va_contact_color_btn_bg'            => '#ff0000',
            'va_contact_color_btn_bg2'           => '#d90000',
            'va_contact_color_btn_text'          => '#ffffff',
            'va_contact_color_btn_glow'          => 'rgba(255,0,0,.26)',
            'va_contact_color_btn_hover_glow'    => 'rgba(255,0,0,.34)',

            // Mobil skála (%)
            'va_mobile_factor_hero'        => 100,
            'va_mobile_factor_header'      => 100,
            'va_mobile_factor_footer'      => 100,

            // Fejléc layout/kinézet (külön menühöz)
            'va_hf_header_height'                  => 66,
            'va_header_logo_height'                => 36,
            'va_hf_header_max_width'               => 1480,
            'va_hf_header_padding_x'               => 32,
            'va_hf_header_padding_top'             => 6,
            'va_hf_header_padding_bottom'          => 10,
            'va_hf_header_gap'                     => 0,
            'va_hf_header_bg_opacity'              => '0.82',
            'va_hf_header_bg_opacity_scrolled'     => '0.88',
            'va_hf_header_blur'                    => 16,
            'va_hf_header_blur_scrolled'           => 20,
            'va_hf_header_shadow_alpha'            => '0.70',
            'va_hf_header_color_base'              => '#050505',
            'va_hf_header_color_alt'               => '#140909',
            'va_hf_header_border_color'            => '#ff2a2a',
            'va_hf_header_shadow_color'            => 'rgba(0,0,0,.72)',
            'va_hf_header_glow_color'              => 'rgba(255,0,0,.24)',

            // Fejléc kereső
            'va_hf_header_search_max_width'        => 460,
            'va_hf_header_search_height'           => 42,
            'va_hf_header_search_radius'           => 30,
            'va_hf_header_search_border_alpha'     => '0.14',
            'va_hf_header_search_bg_alpha'         => '0.02',
            'va_hf_header_search_hover_border_alpha' => '0.38',
            'va_hf_header_search_focus_border_alpha' => '0.52',
            'va_hf_header_search_icon_size'        => 16,
            'va_hf_header_search_icon_bg_alpha'    => '0.14',
            'va_hf_header_search_icon_bg_hover_alpha' => '0.22',
            'va_hf_header_search_glow_color'       => 'rgba(255,0,0,.18)',
            'va_hf_header_search_placeholder'      => 'keresés…',

            // Fejléc gombok
            'va_hf_header_btn_radius'              => 999,
            'va_hf_header_btn_pad_y'               => 8,
            'va_hf_header_btn_pad_x'               => 20,
            'va_hf_header_btn_glow_alpha'          => '0.40',
            'va_hf_header_btn_glow_color'          => 'rgba(255,0,0,.52)',
            'va_color_header_submit_hover_bg'          => '#cc0000',
            'va_color_header_submit_hover_text'        => '#ffffff',
            'va_hf_header_user_border_alpha'       => '0.12',
            'va_hf_header_user_bg_alpha'           => '0.06',
            'va_hf_header_mobile_show_search'      => '0',
            'va_hf_header_mobile_show_submit'      => '0',
            'va_hf_header_show_buy_button'         => '1',
            'va_hf_header_submit_text'             => '+ Hirdetés feladása',
            'va_hf_header_register_text'           => 'Regisztráció',
            'va_hf_header_login_text'              => 'Bejelentkezés',

            // Fejléc nav link és gomb hover színek
            'va_color_header_nav_link'             => '#ffffff',
            'va_color_header_nav_hover'            => '#ff2020',
            'va_color_header_nav_underline'        => '#ff0000',
            'va_color_header_login_hover_bg'       => 'rgba(255,255,255,.08)',
            'va_color_header_login_hover_text'     => '#ffffff',
            'va_color_header_register_hover_bg'    => '#cc0000',
            'va_color_header_register_hover_text'  => '#ffffff',
            'va_color_header_search_border'        => 'rgba(255,255,255,.18)',
            'va_color_header_search_hover_border'  => 'rgba(255,255,255,.28)',
            'va_color_header_search_focus_border'  => 'rgba(255,255,255,.38)',
            'va_color_header_search_text'          => '#ffffff',
            'va_color_header_search_placeholder'   => 'rgba(255,255,255,.40)',
            'va_color_header_search_btn_bg'        => 'rgba(255,255,255,.14)',
            'va_color_header_search_btn_hover_bg'  => 'rgba(255,255,255,.22)',

            // Lábléc layout/kinézet
            'va_hf_footer_top_padding'             => 48,
            'va_hf_footer_bottom_padding'          => 24,
            'va_hf_footer_grid_gap'                => 32,
            'va_hf_footer_col_min_width'           => 160,
            'va_hf_footer_title_gap'               => 12,
            'va_hf_footer_link_pad_y'              => 4,
            'va_hf_footer_bottom_top_padding'      => 20,
            'va_hf_footer_border_alpha'            => '0.07',
            'va_hf_footer_bottom_border_alpha'     => '0.07',
            'va_hf_footer_max_width'               => 1400,
            'va_hf_footer_color_base'              => '#0a0a0a',
            'va_hf_footer_color_alt'               => '#150707',
            'va_hf_footer_border_color'            => '#ff2a2a',
            'va_hf_footer_shadow_color'            => 'rgba(0,0,0,.36)',
            'va_hf_footer_glow_color'              => 'rgba(255,0,0,.14)',
            'va_hf_footer_link_hover_color'        => '#ffffff',
            'va_hf_footer_logo_url'                => '',
            'va_hf_footer_logo_height'             => 48,

            // Lábléc szövegek
            'va_hf_footer_brand_title'             => 'weingartnerauto.hu',
            'va_hf_footer_col_categories_title'    => 'Kategóriák',
            'va_hf_footer_col_account_title'       => 'Fiók',
            'va_hf_footer_col_legal_title'         => 'Jogi információk',
            'va_hf_footer_link_aszf'               => 'ÁSZF',
            'va_hf_footer_link_privacy'            => 'Adatvédelmi nyilatkozat',
            'va_hf_footer_link_contact'            => 'Kapcsolat',
            'va_hf_footer_link_help'               => 'Súgó',
            'va_hf_footer_copy_text'               => 'weingartnerauto.hu - Minden jog fenntartva.',
            'va_hf_footer_privacy_text'            => 'Adatvédelem',
            'va_legal_url_adatvedelem'             => '/adatvedelmi-nyilatkozat',
            'va_legal_url_aszf'                    => '/aszf',
            'va_legal_url_impresszum'              => '',
            'va_legal_url_etika'                   => '',
            'va_legal_url_sutik'                   => '',
            'va_legal_url_gdpr'                    => '',
            'va_legal_url_fenntarthato'            => '',
            'va_legal_content_adatvedelem'       => '',
            'va_legal_content_aszf'              => '',
            'va_legal_content_impresszum'        => '',
            'va_legal_content_etika'             => '',
            'va_legal_content_sutik'             => '',
            'va_legal_content_gdpr'              => '',
            'va_legal_content_fenntarthato'      => '',
        ];

        $header_footer_keys = [
            'va_color_header_bg',
            'va_color_header_text',
            'va_color_header_accent',
            'va_color_footer_bg',
            'va_color_footer_text',
            'va_color_footer_headings',
            'va_color_footer_links',
            'va_size_header_brand',
            'va_size_header_nav',
            'va_size_header_search',
            'va_size_header_btn',
            'va_weight_header_brand',
            'va_weight_header_nav',
            'va_size_footer_title',
            'va_size_footer_link',
            'va_size_footer_bottom',
            'va_weight_footer_title',
            'va_weight_footer_link',
            'va_mobile_factor_header',
            'va_mobile_factor_footer',
            'va_hf_header_height',
            'va_header_logo_height',
            'va_hf_header_max_width',
            'va_hf_header_padding_x',
            'va_hf_header_padding_top',
            'va_hf_header_padding_bottom',
            'va_hf_header_gap',
            'va_hf_header_bg_opacity',
            'va_hf_header_bg_opacity_scrolled',
            'va_hf_header_blur',
            'va_hf_header_blur_scrolled',
            'va_hf_header_shadow_alpha',
            'va_hf_header_color_base',
            'va_hf_header_color_alt',
            'va_hf_header_border_color',
            'va_hf_header_shadow_color',
            'va_hf_header_glow_color',
            'va_hf_header_search_max_width',
            'va_hf_header_search_height',
            'va_hf_header_search_radius',
            'va_hf_header_search_border_alpha',
            'va_hf_header_search_bg_alpha',
            'va_hf_header_search_hover_border_alpha',
            'va_hf_header_search_focus_border_alpha',
            'va_hf_header_search_icon_size',
            'va_hf_header_search_icon_bg_alpha',
            'va_hf_header_search_icon_bg_hover_alpha',
            'va_hf_header_search_glow_color',
            'va_hf_header_search_placeholder',
            'va_hf_header_btn_radius',
            'va_hf_header_btn_pad_y',
            'va_hf_header_btn_pad_x',
            'va_hf_header_btn_glow_alpha',
            'va_hf_header_btn_glow_color',
            'va_color_header_submit_hover_bg',
            'va_color_header_submit_hover_text',
            'va_color_header_nav_link',
            'va_color_header_nav_hover',
            'va_color_header_nav_underline',
            'va_color_header_login_hover_bg',
            'va_color_header_login_hover_text',
            'va_color_header_register_hover_bg',
            'va_color_header_register_hover_text',
            'va_color_header_search_border',
            'va_color_header_search_hover_border',
            'va_color_header_search_focus_border',
            'va_color_header_search_text',
            'va_color_header_search_placeholder',
            'va_color_header_search_btn_bg',
            'va_color_header_search_btn_hover_bg',
            'va_hf_header_user_border_alpha',
            'va_hf_header_user_bg_alpha',
            'va_hf_header_mobile_show_search',
            'va_hf_header_mobile_show_submit',
            'va_hf_header_show_buy_button',
            'va_hf_header_submit_text',
            'va_hf_header_register_text',
            'va_hf_header_login_text',
            'va_hf_footer_top_padding',
            'va_hf_footer_bottom_padding',
            'va_hf_footer_grid_gap',
            'va_hf_footer_col_min_width',
            'va_hf_footer_title_gap',
            'va_hf_footer_link_pad_y',
            'va_hf_footer_bottom_top_padding',
            'va_hf_footer_border_alpha',
            'va_hf_footer_bottom_border_alpha',
            'va_hf_footer_max_width',
            'va_hf_footer_color_base',
            'va_hf_footer_color_alt',
            'va_hf_footer_border_color',
            'va_hf_footer_shadow_color',
            'va_hf_footer_glow_color',
            'va_hf_footer_link_hover_color',
            'va_hf_footer_logo_url',
            'va_hf_footer_logo_height',
            'va_hf_footer_brand_title',
            'va_hf_footer_col_categories_title',
            'va_hf_footer_col_account_title',
            'va_hf_footer_col_legal_title',
            'va_hf_footer_link_aszf',
            'va_hf_footer_link_privacy',
            'va_hf_footer_link_contact',
            'va_hf_footer_link_help',
            'va_hf_footer_copy_text',
            'va_hf_footer_privacy_text',
            'va_legal_url_adatvedelem',
            'va_legal_url_aszf',
            'va_legal_url_impresszum',
            'va_legal_url_etika',
            'va_legal_url_sutik',
            'va_legal_url_gdpr',
            'va_legal_url_fenntarthato',
            'va_legal_content_adatvedelem',
            'va_legal_content_aszf',
            'va_legal_content_impresszum',
            'va_legal_content_etika',
            'va_legal_content_sutik',
            'va_legal_content_gdpr',
            'va_legal_content_fenntarthato',
        ];

        $header_footer = [];
        foreach ( $header_footer_keys as $hf_key ) {
            if ( array_key_exists( $hf_key, $design ) ) {
                $header_footer[ $hf_key ] = $design[ $hf_key ];
                unset( $design[ $hf_key ] );
            }
        }

        foreach ( $design as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_design_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        foreach ( $header_footer as $key => $default ) {
            self::$defaults[ $key ] = $default;
            $sanitize_cb = strpos( $key, 'va_legal_url_' ) === 0
                ? [ __CLASS__, 'sanitize_legal_url' ]
                : ( strpos( $key, 'va_legal_content_' ) === 0
                    ? [ __CLASS__, 'sanitize_legal_html' ]
                    : 'sanitize_text_field' );
            register_setting( 'va_header_footer_settings', $key, [ 'sanitize_callback' => $sanitize_cb ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Layout builder (Divi/Porto jellegu) */
        $layout = [
            'va_layout_preset'                => 'porto',

            // Global container
            'va_layout_page_max_width'        => '1400',
            'va_layout_container_pad_x'       => '20',
            'va_layout_container_pad_x_mobile'=> '12',

            // Main content and sidebars
            'va_layout_main_pad_y'            => '20',
            'va_layout_main_pad_x'            => '24',
            'va_layout_home_main_pad_x'       => '28',
            'va_layout_content_gap'           => '0',
            'va_layout_right_sidebar_width'   => '340',
            'va_layout_right_sidebar_sticky_top' => '48',
            'va_layout_show_right_sidebar'    => '1',

            // Listing grids
            'va_layout_grid_cols_desktop'     => '4',
            'va_layout_grid_cols_tablet'      => '2',
            'va_layout_grid_cols_mobile'      => '1',
            'va_layout_grid_gap'              => '14',
            'va_layout_bp_desktop_tablet'     => '1200',
            'va_layout_bp_tablet_mobile'      => '560',
            'va_layout_bp_sidebar_hide'       => '1100',
            'va_layout_home_sidebar_side'     => 'left',  // left|right|none
            'va_layout_grid_justify'          => 'start', // start|center|end
            'va_layout_grid_justify_tablet'   => 'start',
            'va_layout_grid_justify_mobile'   => 'start',

            // Card appearance
            'va_layout_card_radius'           => '6',
            'va_layout_card_border_alpha'     => '0.08',
            'va_layout_card_padding_y'        => '14',
            'va_layout_card_padding_x'        => '14',
            'va_layout_card_title_size'       => '15',
            'va_layout_card_price_size'       => '17',
            'va_layout_card_meta_size'        => '12',
            'va_layout_card_img_ratio'        => '4/3',

            // Effects
            'va_layout_card_hover_lift'       => '2',
            'va_layout_card_shadow_strength'  => '35',
            'va_layout_card_shadow_red'       => '16',
            'va_layout_widget_radius'         => '10',
            'va_layout_widget_padding'        => '16',
        ];

        foreach ( $layout as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_layout_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Videó URL-ek (hero/oldalblokkok) – adminból szerkeszthető
        $video_urls = [
            'va_home_hero_video_url'      => content_url( 'uploads/2026/04/521380_Gun_Woman_1920x1080.mp4' ),
            'va_contact_hero_video_url'   => content_url( 'uploads/2026/04/0_Offroad_4x4_1920x1080.mp4' ),
            'va_category_video_url'       => content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' ),
            'va_tax_category_video_url'   => content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' ),
        ];
        foreach ( $video_urls as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'esc_url_raw' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Hero háttér típus + statikus kép + carousel beállítások */
        $hero_bg_text = [
            'va_home_hero_bg_type'             => 'video',   // video | image | carousel
            'va_home_hero_carousel_transition' => 'fade',    // fade|slide|slide-right|zoom-in|zoom-out|ken-burns|blinds|push|wipe|dissolve|flip|cube
            'va_home_hero_carousel_speed'      => '800',     // átmenet ms
            'va_home_hero_carousel_interval'   => '5000',    // időköz ms
            'va_home_hero_carousel_arrows'     => '1',
            'va_home_hero_carousel_dots'       => '1',
        ];
        foreach ( $hero_bg_text as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }
        // Statikus hero kép URL
        register_setting( 'va_general_settings', 'va_home_hero_static_image', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_home_hero_static_image'] = '';
        if ( get_option( 'va_home_hero_static_image' ) === false ) update_option( 'va_home_hero_static_image', '' );
        // Carousel képek – JSON array of URLs
        register_setting( 'va_general_settings', 'va_home_hero_carousel_images', [
            'sanitize_callback' => function( $val ) {
                $arr = json_decode( wp_unslash( $val ), true );
                if ( ! is_array( $arr ) ) return '[]';
                $clean = array_values( array_filter( array_map( 'esc_url_raw', $arr ) ) );
                return wp_json_encode( $clean );
            },
        ] );
        self::$defaults['va_home_hero_carousel_images'] = '[]';
        if ( get_option( 'va_home_hero_carousel_images' ) === false ) update_option( 'va_home_hero_carousel_images', '[]' );

        /* Hero szekció – teljes vezérlés */
        $hero = [
            // Szövegek
            'va_home_hero_badge_text'           => 'Magyarország első vadászati hirdetőoldala',
            'va_home_hero_title_top'            => 'VadászBazár',
            'va_home_hero_title_bottom'         => 'és Apróhirdetés',
            'va_home_hero_sub_text'             => 'Magyarország első vadászati hirdetőoldala',
            'va_home_hero_primary_cta_text'     => '+ Hirdetés feladása',
            'va_home_hero_secondary_cta_text'   => 'Hirdetések böngészése →',
            // Igazítás
            'va_home_hero_align'                => 'left',
            // Overlay
            'va_hero_overlay_top'               => '0.72',
            'va_hero_overlay_mid_a'             => '0.18',
            'va_hero_overlay_mid_b'             => '0.25',
            'va_hero_overlay_bottom_a'          => '0.85',
            // Bal oldali piros csík
            'va_hero_stripe_show'               => '1',
            'va_hero_stripe_color'              => '#ff0000',
            'va_hero_stripe_width'              => '3',
            'va_hero_stripe_opacity'            => '0.55',
            // Badge dot
            'va_hero_badge_dot_show'            => '1',
            'va_hero_badge_dot_color'           => '#ff0000',
            // Badge
            'va_color_hero_badge_bg'            => 'rgba(6,6,6,.56)',
            'va_color_hero_badge_border'        => 'rgba(255,0,0,.55)',
            'va_color_hero_badge_text'          => '#ffffff',
            // Cím + alcím
            'va_color_hero_title'               => '#ffffff',
            'va_color_hero_title_span'          => '#ff0000',
            'va_color_hero_sub'                 => 'rgba(255,255,255,.90)',
            // Primary gomb
            'va_color_hero_btn_primary_bg'      => '#ff0000',
            'va_color_hero_btn_primary_hover'   => '#cc0000',
            'va_color_hero_btn_primary_text'    => '#ffffff',
            'va_color_hero_btn_primary_hover_text' => '#ffffff',
            'va_color_hero_btn_primary_glow'    => 'rgba(255,0,0,.45)',
            // Ghost gomb
            'va_color_hero_btn_ghost_bg'        => 'rgba(255,255,255,.08)',
            'va_color_hero_btn_ghost_border'    => 'rgba(255,255,255,.22)',
            'va_color_hero_btn_ghost_hover'     => 'rgba(255,255,255,.15)',
            'va_color_hero_btn_ghost_text'      => '#ffffff',
            'va_color_hero_btn_ghost_hover_text'=> '#ffffff',
            // Scroll jel
            'va_hero_scroll_show'               => '1',
            'va_hero_scroll_line_color'         => '#ff0000',
            'va_hero_scroll_dot_color'          => '#ff0000',
            'va_hero_scroll_opacity'            => '0.50',

            // Scroll-progress gomb (jobb alsó videós pill)
            'va_scroll_ring_video_url'          => content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' ),
            'va_scroll_ring_border_color'       => '#00e676',
        ];
        foreach ( $hero as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_hero_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Márka ikon URL (header ikon + automata favicon)
        register_setting( 'va_general_settings', 'va_brand_icon_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_brand_icon_url'] = '';
        if ( get_option( 'va_brand_icon_url' ) === false ) {
            update_option( 'va_brand_icon_url', '' );
        }

        // Logók (fejléc + hero)
        register_setting( 'va_general_settings', 'va_header_logo_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_header_logo_url'] = '';
        if ( get_option( 'va_header_logo_url' ) === false ) {
            update_option( 'va_header_logo_url', '' );
        }
        register_setting( 'va_general_settings', 'va_hero_logo_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_hero_logo_url'] = '';
        if ( get_option( 'va_hero_logo_url' ) === false ) {
            update_option( 'va_hero_logo_url', '' );
        }

        /* Reklámzónák */
        register_setting( 'va_ad_settings', 'va_ad_show_placeholder', [ 'sanitize_callback' => 'absint' ] );
        foreach ( array_keys( VA_Ad_Zones::ZONES ) as $zone ) {
            register_setting( 'va_ad_settings', 'va_ad_zone_' . $zone, [
                'sanitize_callback' => function( $val ) {
                    $allowed = array_merge( wp_kses_allowed_html('post'), [
                        'a'   => [ 'href'=>[], 'target'=>[], 'rel'=>[], 'style'=>[], 'class'=>[] ],
                        'img' => [ 'src'=>[], 'alt'=>[], 'width'=>[], 'height'=>[], 'style'=>[], 'class'=>[] ],
                        'div' => [ 'style'=>[], 'class'=>[] ],
                        'script' => [], // Google Ads iframe-ek miatt
                        'iframe' => [ 'src'=>[], 'width'=>[], 'height'=>[], 'style'=>[], 'frameborder'=>[], 'scrolling'=>[], 'allowtransparency'=>[] ],
                    ]);
                    return wp_kses( wp_unslash( $val ), $allowed );
                },
            ]);
        }

        /* Hirdetések */
        $listing_opts = [
            'va_featured_price'      => 2990,
            'va_featured_days'       => 30,
            'va_free_listings_limit' => 5,
            'va_listing_price_after_free' => 1990,
        ];
        foreach ( $listing_opts as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'absint' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Főoldali kártyarács gyorsbeállítások (Hirdetés beállítások oldalon is)
        $listing_layout_int = [
            'va_layout_grid_cols_desktop'  => 4,
            'va_layout_grid_cols_tablet'   => 2,
            'va_layout_grid_cols_mobile'   => 1,
        ];
        foreach ( $listing_layout_int as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'absint' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        $listing_layout_select = [
            'va_layout_home_sidebar_side'   => 'left',
            'va_layout_grid_justify'        => 'start',
            'va_layout_grid_justify_tablet' => 'start',
            'va_layout_grid_justify_mobile' => 'start',
        ];
        foreach ( $listing_layout_select as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        self::$defaults['va_listing_payment_url'] = '';
        register_setting( 'va_listing_settings', 'va_listing_payment_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        if ( get_option( 'va_listing_payment_url' ) === false ) {
            update_option( 'va_listing_payment_url', '' );
        }

        $listing_payment = [
            'va_payment_provider'        => 'none',
            'va_payment_mode'            => 'test',
            'va_payment_public_key'      => '',
            'va_payment_secret_key'      => '',
            'va_payment_webhook_secret'  => '',
            'va_payment_success_url'     => '',
            'va_payment_cancel_url'      => '',
        ];
        foreach ( $listing_payment as $key => $default ) {
            self::$defaults[ $key ] = $default;
            $sanitize = in_array( $key, [ 'va_payment_success_url', 'va_payment_cancel_url' ], true )
                ? 'esc_url_raw'
                : 'sanitize_text_field';
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => $sanitize ] );
            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }

        $billing = [
            'va_billing_company_name'    => 'weingartnerauto.hu',
            'va_billing_company_address' => 'Magyarorszag',
            'va_billing_tax_number'      => '',
            'va_billing_email'           => (string) get_option( 'admin_email', '' ),
            'va_billing_phone'           => '',
            'va_invoice_prefix'          => 'VA',
            'va_invoice_next_number'     => 1,
            'va_invoice_footer_note'     => 'Koszonjuk a vasarlast!',
        ];
        foreach ( $billing as $key => $default ) {
            self::$defaults[ $key ] = $default;
            if ( $key === 'va_billing_email' ) {
                register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'sanitize_email' ] );
            } elseif ( $key === 'va_invoice_next_number' ) {
                register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'absint' ] );
            } else {
                register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            }

            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }

        /* Aukciók */
        $auction_opts = [
            'va_default_min_bid_step'  => 500,
            'va_auction_fee_pct'       => 0,
            'va_email_outbid_subject'  => 'Túllicitáltak – {title}',
            'va_email_outbid_heading'  => 'Túllicitáltak téged!',
            'va_email_outbid_body'     => "<p>Kedves <strong>{name}</strong>,</p>\n<p>Túllicitáltak a <strong>{title}</strong> aukción.</p>\n<p>Aktuális licit: <strong>{amount} Ft</strong></p>",
            'va_email_outbid_btn'      => 'Licitáljon újra',
            'va_email_winner_subject'  => 'Nyertél az aukción! – {title}',
            'va_email_winner_heading'  => '🏆 Nyertél az aukción!',
            'va_email_winner_body'     => "<p>Kedves <strong>{name}</strong>,</p>\n<p>Gratulálunk! Nyertél a <strong>{title}</strong> aukción.</p>\n<p>Nyerő licit: <strong>{amount} Ft</strong></p>\n<p>A hirdetés feladójával hamarosan felveszi Önnel a kapcsolatot.</p>",
            'va_email_winner_btn'      => 'Aukció megtekintése',
            'va_email_seller_subject'  => 'Aukciód lezárult – {title}',
            'va_email_seller_heading'  => 'Aukciód lezárult!',
            'va_email_seller_body'     => "<p>Kedves <strong>{seller_name}</strong>,</p>\n<p>Lezárult a <strong>{title}</strong> aukciód.</p>\n<p>Nyerő licit: <strong>{amount} Ft</strong></p>\n<p>Nyertes: <strong>{winner_name}</strong> (<a href=\"mailto:{winner_email}\" style=\"color:#cc0000;\">{winner_email}</a>)</p>\n<p>Kérjük, vegye fel a kapcsolatot a nyertessel.</p>",
            'va_email_seller_btn'      => 'Aukció megtekintése',
        ];
        foreach ( $auction_opts as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_auction_settings', $key, [ 'sanitize_callback' => 'wp_kses_post' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Rendszer emailek */
        $email_opts = [
            'va_email_reg_enabled'        => '1',
            'va_email_reg_subject'        => 'Üdvözlünk a {site_name} oldalon!',
            'va_email_reg_heading'        => 'Sikeres regisztráció!',
            'va_email_reg_body'           => "<p>Kedves <strong>{name}</strong>,</p>\n<p>Sikeresen regisztráltál a <strong>{site_name}</strong> oldalra.</p>\n<p>Felhasználóneved: <strong>{username}</strong></p>\n<p>Jó hirdetezést!</p>",
            'va_email_reg_btn'            => 'Fiókja megtekintése',
            'va_email_listing_enabled'    => '1',
            'va_email_listing_subject'    => 'Hirdetésed megjelent – {title}',
            'va_email_listing_heading'    => 'Hirdetésed él!',
            'va_email_listing_body'       => "<p>Kedves <strong>{name}</strong>,</p>\n<p>A <strong>{title}</strong> hirdetésed jóváhagyásra került és most élőben elérhető.</p>",
            'va_email_listing_btn'        => 'Hirdetés megtekintése',
            'va_email_del_listing_enabled'=> '1',
            'va_email_del_listing_subject'=> 'Hirdetésed törölve – {title}',
            'va_email_del_listing_heading'=> 'Hirdetésed törölve lett',
            'va_email_del_listing_body'   => "<p>Kedves <strong>{name}</strong>,</p>\n<p>A <strong>{title}</strong> hirdetésedet sikeresen törölted.</p>",
            'va_email_del_listing_btn'    => '',
            'va_email_del_account_enabled'=> '1',
            'va_email_del_account_subject'=> 'Fiókod törölve – {site_name}',
            'va_email_del_account_heading'=> 'Fiókod törölve lett',
            'va_email_del_account_body'   => "<p>Kedves <strong>{name}</strong>,</p>\n<p>Fiókod és összes adatod törölve lett a <strong>{site_name}</strong> rendszerből.</p>\n<p>Reméljük, hogy visszatérsz hozzánk!</p>",
            'va_email_del_account_btn'    => '',
        ];
        foreach ( $email_opts as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_email_settings', $key, [ 'sanitize_callback' => 'wp_kses_post' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Árkártyák (kredit vásárlás oldal) */
        $card_int_keys = [];
        for ( $n = 1; $n <= 8; $n++ ) {
            $card_int_keys[] = "va_pc_{$n}_qty";
            $card_int_keys[] = "va_pc_{$n}_price";
        }
        $default_card_qtys  = [ 1 => 1, 2 => 3, 3 => 5, 4 => 10 ];
        $default_card_labels= [ 1 => 'Basic', 2 => 'Silver', 3 => 'Gold', 4 => 'Platinum' ];
        $default_card_slugs = [ 1 => 'basic', 2 => 'silver', 3 => 'gold', 4 => 'platinum' ];
        $default_card_tags  = [ 1 => 'Belépő', 2 => 'Népszerű', 3 => 'Profi', 4 => 'Prémium' ];
        $default_card_descs = [
            1 => 'Ingyenes alap csomag minden regisztrált felhasználónak.',
            2 => '3 hirdetési kredit kedvezményes áron.',
            3 => '5 kredit – legjobb érték a profik számára.',
            4 => '10 kredit – maximális értékcsomag vadász profiknak.',
        ];
        $default_card_prices = [ 1 => 0, 2 => 1791, 3 => 1592, 4 => 1393 ];
        $default_card_badges = [ 1 => '', 2 => '–10%', 3 => '–20%', 4 => '–30%' ];
        $default_card_themes = [ 1 => 'basic', 2 => 'silver', 3 => 'gold', 4 => 'platinum' ];

        $price_card_opts = [
            'va_pc_eyebrow'  => 'Átlátható csomagok',
            'va_pc_title'    => 'Rang Alapú Vásárlás',
            'va_pc_subtitle' => 'Válassz csomagot a rangok szerint, és fizess azonnal bankkártyával.',
            'va_pc_count'    => '4',
        ];
        for ( $n = 1; $n <= 8; $n++ ) {
            $price_card_opts[ "va_pc_{$n}_enabled"   ] = $n <= 4 ? '1' : '0';
            $price_card_opts[ "va_pc_{$n}_label"     ] = $default_card_labels[ $n ]  ?? '';
            $price_card_opts[ "va_pc_{$n}_plan_slug" ] = $default_card_slugs[ $n ]   ?? '';
            $price_card_opts[ "va_pc_{$n}_tag"       ] = $default_card_tags[ $n ]    ?? '';
            $price_card_opts[ "va_pc_{$n}_desc"      ] = $default_card_descs[ $n ]   ?? '';
            $price_card_opts[ "va_pc_{$n}_qty"       ] = (string) ( $default_card_qtys[ $n ]   ?? 1 );
            $price_card_opts[ "va_pc_{$n}_price"     ] = (string) ( $default_card_prices[ $n ] ?? 0 );
            $price_card_opts[ "va_pc_{$n}_badge"     ] = $default_card_badges[ $n ]  ?? '';
            $price_card_opts[ "va_pc_{$n}_featured"  ] = ( $n === 3 ) ? '1' : '0';
            $price_card_opts[ "va_pc_{$n}_free"      ] = ( $n === 1 ) ? '1' : '0';
            $price_card_opts[ "va_pc_{$n}_btn_text"  ] = ( $n === 1 ) ? 'Mindenki számára elérhető' : 'Vásárlás →';
            $price_card_opts[ "va_pc_{$n}_theme"     ] = $default_card_themes[ $n ]  ?? 'basic';
        }
        foreach ( $price_card_opts as $key => $default ) {
            self::$defaults[ $key ] = $default;
            $sanitize = in_array( $key, $card_int_keys, true ) || $key === 'va_pc_count' ? 'absint' : 'sanitize_text_field';
            register_setting( 'va_price_cards_settings', $key, [ 'sanitize_callback' => $sanitize ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Social Media linkek */
        $social_keys = [
            'va_social_facebook'   => '',
            'va_social_instagram'  => '',
            'va_social_youtube'    => '',
            'va_social_tiktok'     => '',
            'va_social_twitter'    => '',
            'va_social_pinterest'  => '',
            'va_social_linkedin'   => '',
            'va_social_whatsapp'   => '',
            'va_social_telegram'   => '',
            'va_social_header_show' => '1',
            'va_social_footer_show' => '1',
            'va_social_header_style' => 'icons',   // icons | pills
            'va_social_footer_style' => 'icons',   // icons | pills | full
            'va_social_icon_size'    => '20',
        ];
        foreach ( $social_keys as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_social_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Admin Panel megjelenés (va_ap_*) */
        $adminpanel = [
            'va_ap_panel_name'    => 'weingartnerauto.hu',
            'va_ap_panel_icon'    => '🎯',
            'va_ap_logo_url'      => '',
            'va_ap_logo_height'   => 32,
            'va_ap_color_bg'      => '#070709',
            'va_ap_color_bg2'     => '#0d0d11',
            'va_ap_color_bg3'     => '#111118',
            'va_ap_color_bg4'     => '#161620',
            'va_ap_color_text'    => '#e8e8f0',
            'va_ap_color_muted'   => 'rgba(255,255,255,.45)',
            'va_ap_color_accent'  => '#ff2020',
            'va_ap_color_accent2' => '#ff5050',
            'va_ap_color_border'  => 'rgba(255,255,255,.07)',
            'va_ap_color_border2' => 'rgba(255,255,255,.12)',
            'va_ap_sidebar_width' => 230,
            'va_ap_topbar_height' => 60,
            'va_ap_radius'        => 12,
            'va_ap_radius_sm'     => 8,
            'va_ap_font'          => 'montserrat',
            'va_ap_font_size'     => 13,
        ];
        foreach ( $adminpanel as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_ap_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Termékoldal Designer (va_single_*) */
        $single_designer = [
            'va_single_preset'             => 'cinematic',
            'va_single_layout_mode'        => 'split',
            'va_single_content_max'        => 1320,
            'va_single_sidebar_width'      => 390,
            'va_single_layout_gap'         => 24,
            'va_single_gallery_ratio'      => '4/3',
            'va_single_gallery_fit'        => 'cover',
            'va_single_thumb_size'         => 86,
            'va_single_card_radius'        => 14,
            'va_single_card_padding'       => 22,
            'va_single_title_size'         => 40,
            'va_single_price_size'         => 42,
            'va_single_meta_size'          => 13,
            'va_single_btn_height'         => 48,
            'va_single_share_size'         => 40,
            'va_single_mobile_title_scale' => 78,
            'va_single_viewer_bg'          => 'rgba(4,4,4,.96)',
            'va_single_accent'             => '#ff2a2a',
            'va_single_glass'              => 'rgba(255,255,255,.07)',
            'va_single_border'             => 'rgba(255,255,255,.12)',
            'va_single_show_plan_badge'    => '1',
            // Termékoldal extra színek
            'va_sl_card_title_color'       => 'rgba(255,255,255,.55)',
            'va_sl_title_color'            => '#ffffff',
            'va_sl_meta_color'             => 'rgba(255,255,255,.55)',
            'va_sl_spec_label_color'       => 'rgba(255,255,255,.45)',
            'va_sl_spec_val_color'         => '#ffffff',
            'va_sl_desc_color'             => 'rgba(255,255,255,.82)',
            'va_sl_seller_name_color'      => '#ffffff',
            'va_sl_seller_since_color'     => 'rgba(255,255,255,.45)',
            'va_sl_demand_bg'              => 'rgba(255,100,0,.12)',
            'va_sl_demand_border'          => 'rgba(255,100,0,.3)',
            'va_sl_demand_text'            => '#ff9550',
            'va_sl_featured_pill_bg'       => 'rgba(255,180,0,.15)',
            'va_sl_featured_pill_border'   => 'rgba(255,180,0,.3)',
            'va_sl_featured_pill_text'     => '#ffd060',
            'va_sl_views_color'            => 'rgba(255,255,255,.45)',
            'va_sl_share_label_color'      => 'rgba(255,255,255,.45)',
            'va_sl_phone_icon_size'        => 16,
            'va_sl_phone_icon_color'       => '#ffffff',
            'va_sl_watch_btn_color'        => 'rgba(255,255,255,.75)',
            'va_sl_watch_btn_border'       => 'rgba(255,255,255,.2)',
            'va_sl_expired_color'          => '#ff6060',
            'va_sl_sticky_bg'              => 'rgba(10,10,10,.95)',
            'va_sl_sticky_title_color'     => '#ffffff',
            'va_sl_related_border'         => 'rgba(255,255,255,.1)',
            'va_sl_related_title_color'    => '#ffffff',
            'va_sl_related_meta_color'     => 'rgba(255,255,255,.4)',
        ];
        foreach ( $single_designer as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_single_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }

        /* Kereső legördülő designer (va_search_dd_*) */
        $search_designer = [
            'va_search_dd_bg'                  => '#111111',
            'va_search_dd_border'              => 'rgba(255,255,255,.10)',
            'va_search_dd_radius'              => 14,
            'va_search_dd_shadow'              => '0 16px 48px rgba(0,0,0,.70)',
            'va_search_dd_item_border'         => 'rgba(255,255,255,.05)',
            'va_search_dd_item_hover_bg'       => 'rgba(255,255,255,.06)',
            'va_search_dd_item_pad_y'          => 10,
            'va_search_dd_item_pad_x'          => 14,
            'va_search_dd_thumb_radius'        => 8,
            'va_search_dd_noimg_bg'            => 'rgba(255,255,255,.06)',
            'va_search_dd_title_color'         => '#ffffff',
            'va_search_dd_title_size'          => 13,
            'va_search_dd_price_color'         => '#ff0000',
            'va_search_dd_price_size'          => 12,
            'va_search_dd_badge_size'          => 10,
            'va_search_dd_badge_radius'        => 20,
            'va_search_dd_badge_listing_bg'    => 'rgba(255,136,0,.22)',
            'va_search_dd_badge_listing_text'  => '#ff8800',
            'va_search_dd_badge_auction_bg'    => 'rgba(255,140,0,.25)',
            'va_search_dd_badge_auction_text'  => '#ff8c00',
            'va_search_dd_badge_category_bg'   => 'rgba(0,200,100,.18)',
            'va_search_dd_badge_category_text' => '#00e070',
            'va_search_dd_badge_user_bg'       => 'rgba(80,140,255,.18)',
            'va_search_dd_badge_user_text'     => '#6fa0ff',
            'va_search_dd_all_color'           => '#ff8800',
            'va_search_dd_all_size'            => 12,
            'va_search_dd_all_hover_bg'        => 'rgba(255,136,0,.10)',
        ];
        foreach ( $search_designer as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_search_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }
        // Finom migracio: korabbi piros kereso defaultok -> narancs (#ff8800)
        if ( (string) get_option( 'va_search_dd_badge_listing_text', '' ) === '#ff4040' ) {
            update_option( 'va_search_dd_badge_listing_text', '#ff8800' );
        }
        if ( (string) get_option( 'va_search_dd_badge_listing_bg', '' ) === 'rgba(255,0,0,.18)' ) {
            update_option( 'va_search_dd_badge_listing_bg', 'rgba(255,136,0,.22)' );
        }
        if ( (string) get_option( 'va_search_dd_all_color', '' ) === '#ff0000' ) {
            update_option( 'va_search_dd_all_color', '#ff8800' );
        }
        if ( (string) get_option( 'va_search_dd_all_hover_bg', '' ) === 'rgba(255,0,0,.06)' ) {
            update_option( 'va_search_dd_all_hover_bg', 'rgba(255,136,0,.10)' );
        }

        /* Hirdetés keresési oldal designer (va_lp_*) */
        $lp_settings = [
            // Szűrő sáv
            'va_lp_filter_bg'             => '#141414',
            'va_lp_filter_border'         => 'rgba(255,255,255,.08)',
            'va_lp_filter_radius'         => '14',
            'va_lp_filter_padding'        => '20',
            'va_lp_filter_title_color'    => 'rgba(255,255,255,.55)',
            'va_lp_filter_title_text'     => '🔍 Hirdetések keresése',
            // Bemenetek
            'va_lp_input_bg'              => '#0e0e0e',
            'va_lp_input_border'          => 'rgba(255,255,255,.12)',
            'va_lp_input_color'           => '#ffffff',
            'va_lp_input_focus_border'    => '#ff0000',
            'va_lp_input_radius'          => '14',
            'va_lp_kw_placeholder'        => 'Kulcsszó...',
            'va_lp_cat_placeholder'       => '– Kategória –',
            'va_lp_county_placeholder'    => '– Megye –',
            'va_lp_cond_placeholder'      => '– Állapot –',
            // Ár csúszka
            'va_lp_slider_fill_color'     => '#ff0000',
            'va_lp_slider_thumb_color'    => '#ff0000',
            'va_lp_slider_track_bg'       => 'rgba(255,255,255,.12)',
            'va_lp_slider_display_color'  => '#ff4040',
            'va_lp_slider_label_text'     => 'Ár szűrő',
            'va_lp_slider_max'            => '5000000',
            'va_lp_slider_step'           => '500',
            // Szortírozás szövegek
            'va_lp_sort_default_lbl'      => 'Legújabb',
            'va_lp_sort_price_asc_lbl'    => 'Ár: növekvő',
            'va_lp_sort_price_desc_lbl'   => 'Ár: csökkenő',
            'va_lp_sort_views_lbl'        => 'Legtöbb megtekintés',
            // Gombok
            'va_lp_reset_btn_text'        => 'Szűrők törlése',
            'va_lp_reset_btn_color'       => '#ffffff',
            'va_lp_reset_btn_border'      => 'rgba(255,255,255,.08)',
            'va_lp_reset_btn_hover_color' => '#ff0000',
            'va_lp_results_count_color'   => 'rgba(255,255,255,.5)',
            // Nézet váltó
            'va_lp_view_active_color'     => '#ff2f2f',
            'va_lp_view_active_glow'      => 'rgba(255,0,0,.5)',
            'va_lp_view_border_active'    => 'rgba(255,0,0,.72)',
            // Betöltő / üres állapot
            'va_lp_loader_text'           => 'Betöltés...',
            'va_lp_loader_color'          => 'rgba(255,255,255,.5)',
            'va_lp_empty_text'            => 'Nincs találat.',
            // Kártyák
            'va_lp_card_bg'               => '#141414',
            'va_lp_card_border'           => 'rgba(255,255,255,.08)',
            'va_lp_card_radius'           => '14',
            'va_lp_card_title_color'      => '#ffffff',
            'va_lp_card_title_size'       => '15',
            'va_lp_card_title_hover'      => '#ff0000',
            'va_lp_card_price_color'      => '#ff0000',
            'va_lp_card_price_size'       => '17',
            'va_lp_card_meta_color'       => 'rgba(255,255,255,.55)',
            'va_lp_card_meta_size'        => '12',
            'va_lp_card_gap'              => '20',
            'va_lp_card_watchlist_color'  => '#ff2a2a',
            'va_lp_card_watchlist_border' => 'rgba(255,0,0,.45)',
            'va_lp_card_watchlist_bg'     => 'rgba(0,0,0,.62)',
            'va_lp_card_featured_color'   => '#ffc840',
            'va_lp_card_featured_border'  => 'rgba(255,180,0,.5)',
            'va_lp_card_boost_color'      => '#ff2a2a',
            'va_lp_card_boost_bg'         => 'rgba(255,42,42,.18)',
            // Lapozó
            'va_lp_pag_bg'                => 'rgb(20,20,20)',
            'va_lp_pag_color'             => 'rgba(255,255,255,.55)',
            'va_lp_pag_border'            => 'rgba(255,255,255,.08)',
            'va_lp_pag_radius'            => '14',
            'va_lp_pag_active_bg'         => '#ff0000',
            'va_lp_pag_active_color'      => '#ffffff',
            'va_lp_pag_size'              => '13',
        ];
        foreach ( $lp_settings as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_lp_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }

        /* Back-to-top gomb (va_btt_*) */
        $btt = [
            'va_btt_enabled'      => '1',
            'va_btt_style'        => 'circle',
            'va_btt_icon'         => 'fa-solid fa-chevron-up',
            'va_btt_color'        => '#ff0000',
            'va_btt_border_color' => '#ff0000',
            'va_btt_text_color'   => '#ffffff',
            'va_btt_size'         => '48',
            'va_btt_position'     => 'right',
            'va_btt_offset_x'     => '28',
            'va_btt_offset_y'     => '28',
            'va_btt_show_after'   => '300',
        ];
        foreach ( $btt as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_btt_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Custom kód (CSS, JS, Head) */
        $custom_code_keys = [
            'va_custom_css'  => '',
            'va_custom_js'   => '',
            'va_custom_head' => '',
        ];
        foreach ( $custom_code_keys as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_custom_code_settings', $key, [ 'sanitize_callback' => [ __CLASS__, 'sanitize_code_field' ] ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* i18n */
        $i18n_keys = [
            'va_default_lang'        => 'hu',
            'va_active_langs'        => '["hu"]',
            'va_lang_show_switcher'  => '1',
            'va_lang_switcher_pos'   => 'header',
        ];
        foreach ( $i18n_keys as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_i18n_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }
    }

    private static function get_display_option( string $key, $fallback = '' ) {
        $val = get_option( $key, null );
        if ( $val === null || $val === false || $val === '' ) {
            if ( array_key_exists( $key, self::$defaults ) ) {
                return self::$defaults[ $key ];
            }
            return $fallback;
        }
        return $val;
    }

    /* ══ Általános beállítások oldal ══════════════════════ */
    public static function render_general() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>⚙️ weingartnerauto.hu - Altalanos beallitasok</h1>
            <?php settings_errors( 'va_general_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_general_settings' ); ?>
                <table class="form-table">
                    <tr><th colspan="2" style="padding-top:0 0 8px;"><h2 style="margin:0 0 4px;">🗂️ Oldaltípus</h2><p class="description">Ez határozza meg a hirdetési form mezőit, a termékoldalon megjelenő adatokat és a kategóriacímkéket.</p></th></tr>
                    <?php self::field_select('va_site_type', '🗂️ Oldaltípus / Termékkategória', [
                        'vadaszat'  => '🦌 Vadászat – fegyver, lőszer, kiegészítők',
                        'jarmu'     => '🚗 Motor & Autó – jármű adatok (km, üzemanyag, stb.)',
                        'ingatlan'  => '🏠 Ingatlan – alapterület, szobák, emelet',
                        'altalanos' => '📦 Általános – márka, modell, gyártási év',
                    ]); ?>
                    <tr><th colspan="2" style="padding-top:18px;"><h2 style="margin:0;">🌐 Oldal adatok</h2></th></tr>
                    <?php self::field_text(  'va_site_name',           'Oldal neve' ); ?>
                    <?php self::field_text(  'va_site_description',     'Oldal alcíme / leírás' ); ?>
                    <?php self::field_email( 'va_contact_email',        'Kapcsolati e-mail' ); ?>
                    <?php self::field_media( 'va_brand_icon_url',       'Ikon (automata favicon, ajánlott: négyzetes PNG)' ); ?>
                    <?php self::field_media( 'va_header_logo_url',      'Fejléc logó' ); ?>
                    <?php self::field_num(   'va_header_logo_height',   'Fejléc logó magasság (px)', 20, 120 ); ?>
                    <?php self::field_media( 'va_hero_logo_url',        'Hero logó (főoldal)' ); ?>
                    <?php self::field_num(   'va_hero_logo_height',     'Hero logó magasság (px)', 30, 260 ); ?>
                    <?php self::field_select('va_hero_logo_position',   'Hero logó pozíció', [ 'left' => 'Bal', 'center' => 'Közép', 'right' => 'Jobb' ] ); ?>
                    <?php self::field_select('va_home_hero_align',      'Főoldal hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_select('va_home_hero_bg_type', 'Főoldal hero háttér típusa', [
                        'video'    => '🎬 Videó',
                        'image'    => '🖼️ Statikus kép',
                        'carousel' => '🎠 Carousel (több kép)',
                    ]); ?>
                    <?php self::field_video( 'va_home_hero_video_url', 'Főoldal hero videó URL' ); ?>
                    <?php self::field_media( 'va_home_hero_static_image', 'Főoldal hero statikus kép' ); ?>
                    <tr class="va-hero-carousel-row">
                        <th colspan="2" style="padding-top:14px;">
                            <strong>🎠 Carousel beállítások</strong>
                        </th>
                    </tr>
                    <tr class="va-hero-carousel-row">
                        <th><label>Carousel képek</label></th>
                        <td>
                            <?php
                            $carousel_raw = get_option('va_home_hero_carousel_images', '[]');
                            $carousel_imgs = json_decode($carousel_raw, true);
                            if (!is_array($carousel_imgs)) $carousel_imgs = [];
                            ?>
                            <div id="va-carousel-repeater" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                                <?php foreach ($carousel_imgs as $idx => $img_url): ?>
                                <div class="va-carousel-item" style="position:relative;width:120px;">
                                    <img src="<?php echo esc_url($img_url); ?>" style="width:120px;height:75px;object-fit:cover;border-radius:4px;border:1px solid #444;">
                                    <input type="hidden" name="va_carousel_img[]" value="<?php echo esc_attr($img_url); ?>">
                                    <button type="button" class="va-carousel-remove" style="position:absolute;top:2px;right:2px;background:#c00;color:#fff;border:none;border-radius:3px;cursor:pointer;padding:1px 5px;font-size:11px;" title="Törlés">✕</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="va_home_hero_carousel_images" name="va_home_hero_carousel_images" value="<?php echo esc_attr($carousel_raw); ?>">
                            <button type="button" id="va-carousel-add-btn" class="button">+ Kép hozzáadása</button>
                            <p class="description" style="margin-top:6px;">Képek sorrendje áthúzással változtatható. Minimum 2 kép ajánlott.</p>
                            <script>
                            (function(){
                                function updateCarouselJson(){
                                    var items = document.querySelectorAll('#va-carousel-repeater .va-carousel-item input[type=hidden]');
                                    var urls = Array.from(items).map(function(i){return i.value;});
                                    document.getElementById('va_home_hero_carousel_images').value = JSON.stringify(urls);
                                }
                                document.getElementById('va-carousel-add-btn').addEventListener('click',function(){
                                    if(typeof wp === 'undefined' || !wp.media) return;
                                    var frame = wp.media({title:'Kép kiválasztása',multiple:true,library:{type:'image'},button:{text:'Kiválaszt'}});
                                    frame.on('select',function(){
                                        var sel = frame.state().get('selection');
                                        sel.each(function(att){
                                            var url = att.get('url');
                                            var wrap = document.getElementById('va-carousel-repeater');
                                            var div = document.createElement('div');
                                            div.className = 'va-carousel-item';
                                            div.style.cssText = 'position:relative;width:120px;';
                                            div.innerHTML = '<img src="'+url+'" style="width:120px;height:75px;object-fit:cover;border-radius:4px;border:1px solid #444;">'
                                                +'<input type="hidden" name="va_carousel_img[]" value="'+url+'">'
                                                +'<button type="button" class="va-carousel-remove" style="position:absolute;top:2px;right:2px;background:#c00;color:#fff;border:none;border-radius:3px;cursor:pointer;padding:1px 5px;font-size:11px;" title="Törlés">✕</button>';
                                            wrap.appendChild(div);
                                            div.querySelector('.va-carousel-remove').addEventListener('click',function(){div.remove();updateCarouselJson();});
                                            updateCarouselJson();
                                        });
                                    });
                                    frame.open();
                                });
                                document.querySelectorAll('.va-carousel-remove').forEach(function(btn){
                                    btn.addEventListener('click',function(){btn.closest('.va-carousel-item').remove();updateCarouselJson();});
                                });
                                // SortableJS ha elérhető
                                if(typeof Sortable !== 'undefined'){
                                    Sortable.create(document.getElementById('va-carousel-repeater'),{animation:150,onEnd:updateCarouselJson});
                                } else {
                                    // fallback: betöltjük
                                    var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                                    s.onload=function(){Sortable.create(document.getElementById('va-carousel-repeater'),{animation:150,onEnd:updateCarouselJson});};
                                    document.head.appendChild(s);
                                }
                            })();
                            </script>
                        </td>
                    </tr>
                    <?php
                    $transitions = [
                        'fade'       => 'Fade (áttűnés)',
                        'slide'      => 'Slide (balra csúszás)',
                        'slide-right'=> 'Slide Right (jobbra csúszás)',
                        'zoom-in'    => 'Zoom In (belé zoomol)',
                        'zoom-out'   => 'Zoom Out (kifelé zoomol)',
                        'ken-burns'  => 'Ken Burns (lassú zoom+pásztázás)',
                        'blinds'     => 'Blinds (függőleges lamellák)',
                        'push'       => 'Push (új kép tolja el)',
                        'wipe'       => 'Wipe (kitörlés balról)',
                        'dissolve'   => 'Dissolve (pixeles feloldás)',
                        'flip'       => 'Flip (vízszintes flip)',
                        'cube'       => 'Cube (3D kocka)',
                    ];
                    echo '<tbody class="va-hero-carousel-rows">';
                    self::field_select('va_home_hero_carousel_transition', 'Átmenettípus', $transitions);
                    self::field_num('va_home_hero_carousel_interval', 'Képváltás időköze (ms)', 1000, 30000);
                    self::field_num('va_home_hero_carousel_speed', 'Átmenet sebessége (ms)', 200, 3000);
                    self::field_toggle('va_home_hero_carousel_arrows', 'Navigációs nyilak megjelenítése');
                    self::field_toggle('va_home_hero_carousel_dots', 'Pontok (slide indikátor) megjelenítése');
                    echo '</tbody>';
                    ?>
                    <script>
                    (function(){
                        function vaToggleCarouselRows(){
                            var sel = document.querySelector('[name="va_home_hero_bg_type"]');
                            if(!sel) return;
                            var type = sel.value;
                            var show = type === 'carousel';
                            document.querySelectorAll('.va-hero-carousel-row, .va-hero-carousel-rows tr').forEach(function(r){
                                r.style.display = show ? '' : 'none';
                            });
                            var carouselTbody = document.querySelector('.va-hero-carousel-rows');
                            if(carouselTbody) carouselTbody.style.display = show ? '' : 'none';
                        }
                        var sel = document.querySelector('[name="va_home_hero_bg_type"]');
                        if(sel){ sel.addEventListener('change', vaToggleCarouselRows); vaToggleCarouselRows(); }
                    })();
                    </script>
                    <?php self::field_video(  'va_contact_hero_video_url', 'Kapcsolat oldal videó URL' ); ?>
                    <?php self::field_video(  'va_category_video_url', 'Kategória főoldal videó URL' ); ?>
                    <?php self::field_video(  'va_tax_category_video_url', 'Alkategória oldal videó URL' ); ?>
                    <?php self::field_select('va_kategoria_hero_align', 'Kategória hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_select('va_tax_hero_align',       'Alkategória hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_select('va_contact_hero_align',   'Kapcsolat hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_toggle('va_enable_auctions',       'Aukció funkció engedélyezése' ); ?>
                    <?php self::field_toggle('va_enable_login',          'Bejelentkezés engedélyezése' ); ?>
                    <?php self::field_toggle('va_enable_register',       'Regisztráció engedélyezése' ); ?>
                    <?php self::field_num(   'va_listings_per_page',    'Hirdetés / oldal', 5, 100 ); ?>
                    <?php self::field_num(   'va_listing_validity_days','Hirdetés érvényessége (nap)', 1, 365 ); ?>
                    <?php self::field_num(   'va_max_images_per_listing','Max. képek száma hirdetésenként', 1, 20 ); ?>
                    <?php self::field_num(   'va_img_quality',           'Kép JPEG minőség (10–100, ajnl.: 82)', 10, 100 ); ?>
                    <?php self::field_num(   'va_img_max_width',         'Kép max szélesség px (ajnl.: 1920)', 400, 4000 ); ?>
                    <?php self::field_toggle('va_auto_publish_listings', 'Hirdetések azonnali megjelenés (jóváhagyás nélkül)' ); ?>
                    <?php self::field_toggle('va_require_phone',         'Telefonszám kötelező' ); ?>
                    <?php self::field_toggle('va_maintenance_mode',      'Karbantartási mód' ); ?>
                    <?php self::field_text(  'va_maintenance_msg',       'Karbantartási üzenet' ); ?>
                    <?php self::field_toggle('va_show_hunting_season_widget', 'Vadászati idény widget megjelenjen' ); ?>
                    <?php self::field_toggle('va_show_moon_widget',          'Hold widget megjelenjen' ); ?>
                    <?php self::field_toggle('va_show_weather_widget',       'Időjárás widget megjelenjen (geolokáció + 7 nap)' ); ?>
                    <?php self::field_toggle('va_enable_hunting_calendar_page', 'Vadászati naptár oldal engedélyezve' ); ?>
                    <?php self::field_toggle('va_show_home_hunting_calendar', 'Főoldali vadászati naptár panel megjelenjen' ); ?>

                    <tr><th colspan="2" style="padding-top:18px;"><h2 style="margin:0;">Hirdetés kártya meta layout</h2><p class="description" style="margin:6px 0 0;">Főoldali/listás kártyák alsó meta sorainak teljes vezérlése.</p></th></tr>
                    <?php self::field_select('va_card_meta_rows', 'Meta sorok száma', [ '1' => '1 sor', '2' => '2 sor', '3' => '3 sor' ] ); ?>
                    <?php self::field_num(   'va_card_meta_col_gap', 'Meta oszlopköz (px)', 0, 40 ); ?>
                    <?php self::field_num(   'va_card_meta_row_gap', 'Meta sorköz egy soron belül (px)', 0, 20 ); ?>
                    <?php self::field_num(   'va_card_meta_stack_gap', 'Meta sorblokkok közti távolság (px)', 0, 20 ); ?>

                    <?php self::field_toggle('va_card_meta_show_category', 'Mutassa: kategória' ); ?>
                    <?php self::field_select('va_card_meta_row_category', 'Kategória sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_county', 'Mutassa: megye' ); ?>
                    <?php self::field_select('va_card_meta_row_county', 'Megye sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_location', 'Mutassa: település/hely' ); ?>
                    <?php self::field_select('va_card_meta_row_location', 'Település/hely sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_views', 'Mutassa: megtekintésszám' ); ?>
                    <?php self::field_select('va_card_meta_row_views', 'Megtekintésszám sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_author', 'Mutassa: feladó neve' ); ?>
                    <?php self::field_select('va_card_meta_row_author', 'Feladó sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_date', 'Mutassa: dátum' ); ?>
                    <?php self::field_select('va_card_meta_row_date', 'Dátum sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_show_boost_badge', 'Kiemelés (előre téve) badge megjelenjen a kártyákon' ); ?>

                    <?php self::field_text(  'va_home_hero_badge_text',         'Főoldal hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_home_hero_title_top',          'Főoldal hero cím 1. sor' ); ?>
                    <?php self::field_text(  'va_home_hero_title_bottom',       'Főoldal hero cím 2. sor' ); ?>
                    <?php self::field_text(  'va_home_hero_sub_text',           'Főoldal hero alcím' ); ?>
                    <?php self::field_text(  'va_home_hero_primary_cta_text',   'Főoldal hero első gomb szöveg' ); ?>
                    <?php self::field_text(  'va_home_hero_secondary_cta_text', 'Főoldal hero második gomb szöveg' ); ?>
                    <?php self::field_text(  'va_home_latest_label_text',       'Főoldal: „ÚJ” címke szöveg (Legújabb hirdetések)' ); ?>
                    <?php self::field_text(  'va_home_all_link_text',           'Főoldal: „Összes” link szöveg (nyíllal)' ); ?>
                    <?php self::field_color( 'va_home_section_accent_color',    'Főoldal: „ÚJ” + „Összes” accent szín' ); ?>

                    <?php self::field_text(  'va_kategoria_hero_badge_text',    'Kategória hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_title_top',     'Kategória hero cím 1. sor' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_title_bottom',  'Kategória hero cím 2. sor' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_sub_text',      'Kategória hero alcím' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_stat1_label',   'Kategória hero stat 1 felirat' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_stat2_label',   'Kategória hero stat 2 felirat' ); ?>

                    <?php self::field_text(  'va_tax_hero_badge_text',          'Alkategória hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_tax_hero_fallback_lead',       'Alkategória hero alapértelmezett leírás' ); ?>
                    <?php self::field_text(  'va_tax_hero_count_suffix',        'Alkategória hero találatszám utótag' ); ?>

                    <?php self::field_text(  'va_contact_hero_badge_text',      'Kapcsolat hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_contact_hero_title_text',      'Kapcsolat hero cím' ); ?>
                    <?php self::field_text(  'va_contact_hero_lead_text',       'Kapcsolat hero alcím' ); ?>

                    <tr><th colspan="2" style="padding-top:18px;"><h2 style="margin:0;">Kapcsolat oldal – Bal oldali kártyák</h2></th></tr>
                    <?php self::field_text( 'va_contact_card1_title',  'Kártya 1 – cím' ); ?>
                    <?php self::field_text( 'va_contact_card1_text',   'Kártya 1 – szöveg' ); ?>
                    <?php self::field_text( 'va_contact_card2_title',  'Kártya 2 – cím' ); ?>
                    <?php self::field_text( 'va_contact_card2_item1',  'Kártya 2 – lista 1. elem' ); ?>
                    <?php self::field_text( 'va_contact_card2_item2',  'Kártya 2 – lista 2. elem' ); ?>
                    <?php self::field_text( 'va_contact_card2_item3',  'Kártya 2 – lista 3. elem' ); ?>
                    <?php self::field_text( 'va_contact_card3_title',  'Kártya 3 – cím' ); ?>
                    <?php self::field_text( 'va_contact_card3_text',   'Kártya 3 – szöveg' ); ?>

                    <tr><th colspan="2" style="padding-top:18px;"><h2 style="margin:0;">Kapcsolat oldal – Űrlap szövegek</h2></th></tr>
                    <?php self::field_text( 'va_contact_form_title',         'Űrlap cím ("Üzenet küldése")' ); ?>
                    <?php self::field_text( 'va_contact_form_btn_text',      'Küldés gomb felirata' ); ?>
                    <?php self::field_text( 'va_contact_form_label_name',    'Mező felirat: Név' ); ?>
                    <?php self::field_text( 'va_contact_form_label_email',   'Mező felirat: E-mail' ); ?>
                    <?php self::field_text( 'va_contact_form_label_phone',   'Mező felirat: Telefonszám' ); ?>
                    <?php self::field_text( 'va_contact_form_label_subject', 'Mező felirat: Tárgy' ); ?>
                    <?php self::field_text( 'va_contact_form_label_message', 'Mező felirat: Üzenet' ); ?>
                    <?php self::field_text( 'va_contact_form_ph_name',       'Placeholder: Név' ); ?>
                    <?php self::field_text( 'va_contact_form_ph_email',      'Placeholder: E-mail' ); ?>
                    <?php self::field_text( 'va_contact_form_ph_phone',      'Placeholder: Telefonszám' ); ?>
                    <?php self::field_text( 'va_contact_form_ph_subject',    'Placeholder: Tárgy' ); ?>
                    <?php self::field_text( 'va_contact_form_ph_message',    'Placeholder: Üzenet (textarea)' ); ?>
                    <?php self::field_text( 'va_contact_form_msg_success',   'Sikeres küldés üzenet' ); ?>
                    <?php self::field_text( 'va_contact_form_msg_invalid',   'Hibás adatok üzenet' ); ?>
                    <?php self::field_text( 'va_contact_form_msg_error',     'SMTP hiba üzenet' ); ?>
                    <?php self::field_text( 'va_contact_form_msg_nonce',     'Érvénytelen nonce üzenet' ); ?>
                </table>
                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Reklámzóna oldal ═════════════════════════════════ */
    public static function render_ad_zones() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📢 weingartnerauto.hu - Reklamzonak</h1>
            <p class="description">Minden zónába beilleszthetsz bármilyen HTML-t: Google AdSense kódot, saját banner-t, iframe-et stb.</p>
            <?php settings_errors( 'va_ad_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_ad_settings' ); ?>
                <div class="va-ad-zone-block" style="background:rgba(255,0,0,.06);border-left:3px solid #c00;padding:12px 16px;margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;">
                        <input type="checkbox" name="va_ad_show_placeholder" value="1" <?php checked( get_option('va_ad_show_placeholder','0'), '1' ); ?>>
                        Üres reklámzónák kijelölése látható legyen az oldalon (piros keretes placeholder)
                    </label>
                    <p class="description" style="margin-top:6px;">Ha ki van pipálva, az üres zónák helyén megjelenik a piros keretes jelző. Kapcsold ki ha az oldalt élesben mutatod.</p>
                </div>
                <?php foreach ( VA_Ad_Zones::get_all() as $zone_key => $zone ): ?>
                <div class="va-ad-zone-block">
                    <h2><?php echo esc_html( $zone['label'] ); ?></h2>
                    <p class="description">Shortcode: <code>[va_ad_zone zone="<?php echo esc_attr( $zone_key ); ?>"]</code></p>
                    <textarea
                        name="va_ad_zone_<?php echo esc_attr( $zone_key ); ?>"
                        rows="6"
                        class="large-text code va-ad-textarea"
                        placeholder="&lt;!-- Google AdSense vagy saját HTML banner kód --&gt;"
                    ><?php echo esc_textarea( $zone['html'] ); ?></textarea>
                </div>
                <hr>
                <?php endforeach; ?>
                <?php submit_button( 'Reklámzónák mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Design oldal (globális + header/content/footer) ════════ */
    public static function render_design() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $fonts = [
            // ─ Rendszer / web-safe ────────────────────────────────────────────
            'system'             => '– System UI (gyors, natív)',
            'arial'              => '– Arial',
            'arial-black'        => '– Arial Black',
            'helvetica'          => '– Helvetica',
            'verdana'            => '– Verdana',
            'tahoma'             => '– Tahoma',
            'trebuchet'          => '– Trebuchet MS',
            'georgia'            => '– Georgia (serif)',
            'times'              => '– Times New Roman (serif)',
            'courier'            => '– Courier New (monospace)',
            // ─ Google – Sans-serif népszerű ───────────────────────────────────
            'open-sans'          => 'Open Sans',
            'poppins'            => 'Poppins',
            'lato'               => 'Lato',
            'inter'              => 'Inter',
            'roboto'             => 'Roboto',
            'nunito'             => 'Nunito',
            'montserrat'         => 'Montserrat',
            'raleway'            => 'Raleway',
            'source-sans-3'      => 'Source Sans 3',
            'pt-sans'            => 'PT Sans',
            'ubuntu'             => 'Ubuntu',
            'rubik'              => 'Rubik',
            'dm-sans'            => 'DM Sans',
            'work-sans'          => 'Work Sans',
            'manrope'            => 'Manrope',
            'cabin'              => 'Cabin',
            'barlow'             => 'Barlow',
            'barlow-condensed'   => 'Barlow Condensed',
            'mulish'             => 'Mulish',
            'quicksand'          => 'Quicksand',
            'josefin-sans'       => 'Josefin Sans',
            'titillium-web'      => 'Titillium Web',
            'exo-2'              => 'Exo 2',
            'exo'                => 'Exo',
            'archivo'            => 'Archivo',
            'outfit'             => 'Outfit',
            'plus-jakarta-sans'  => 'Plus Jakarta Sans',
            'figtree'            => 'Figtree',
            'syne'               => 'Syne',
            'space-grotesk'      => 'Space Grotesk',
            'kanit'              => 'Kanit',
            'jost'               => 'Jost',
            'urbanist'           => 'Urbanist',
            'fira-sans'          => 'Fira Sans',
            'ibm-plex-sans'      => 'IBM Plex Sans',
            'noto-sans'          => 'Noto Sans',
            // ─ Google – Serif ─────────────────────────────────────────────────
            'merriweather'       => 'Merriweather',
            'playfair'           => 'Playfair Display',
            'lora'               => 'Lora',
            'libre-baskerville'  => 'Libre Baskerville',
            'crimson-text'       => 'Crimson Text',
            'eb-garamond'        => 'EB Garamond',
            'cormorant-garamond' => 'Cormorant Garamond',
            'spectral'           => 'Spectral',
            'oswald'             => 'Oswald',
            // ─ Google – Display / dekoratív ───────────────────────────────────
            'abril-fatface'      => 'Abril Fatface',
            'righteous'          => 'Righteous',
            'bebas-neue'         => 'Bebas Neue',
            'comfortaa'          => 'Comfortaa',
            'pacifico'           => 'Pacifico',
            'anton'              => 'Anton',
            'permanent-marker'   => 'Permanent Marker',
            'shadows-into-light' => 'Shadows Into Light',
        ];
        $weights = [
            '300' => '300 – Light',
            '400' => '400 – Normal',
            '500' => '500 – Medium',
            '600' => '600 – SemiBold',
            '700' => '700 – Bold',
            '800' => '800 – ExtraBold',
            '900' => '900 – Black',
        ];
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🎨 weingartnerauto.hu - Design (globalis + fejtol labig)</h1>
            <p class="description">Külön oldalon kezelhető a teljes tipográfia és színvilág: globális, fejléc, tartalom és lábléc szinten.</p>
            <?php settings_errors( 'va_design_settings' ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_design_settings' ); ?>

                <h2>Betűtípusok</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_font_global',   'Globális alap betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_headings', 'Címsorok (H1-H6) betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_header',   'Fejléc/Navigáció betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_content',  'Tartalmi szöveg betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_footer',   'Lábléc betűtípus', $fonts ); ?>
                </table>

                <h2>Globális színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_global_bg',     'Globális háttér' ); ?>
                    <?php self::field_color( 'va_color_global_text',   'Globális fő szöveg' ); ?>
                    <?php self::field_color( 'va_color_global_muted',  'Globális halvány szöveg (pl. rgba...)' ); ?>
                    <?php self::field_color( 'va_color_global_accent', 'Globális accent szín' ); ?>
                </table>

                <h2>Tartalom színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_content_bg',       'Tartalom háttér' ); ?>
                    <?php self::field_color( 'va_color_content_text',     'Tartalom szöveg' ); ?>
                    <?php self::field_color( 'va_color_content_headings', 'Tartalom címsorok' ); ?>
                    <?php self::field_color( 'va_color_content_links',    'Tartalom linkek' ); ?>
                </table>

                <h2>Hero badge és gombok színei</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_hero_title',            'Hero cím szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_sub',             'Hero alcim szoveg szin' ); ?>
                    <?php self::field_color( 'va_color_hero_badge_bg',         'Hero badge hatter' ); ?>
                    <?php self::field_color( 'va_color_hero_badge_border',      'Hero badge keret szin' ); ?>
                    <?php self::field_color( 'va_color_hero_badge_text',         'Hero badge szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_bg',     'Primary gomb háttér' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_hover',  'Primary gomb hover háttér' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_text',   'Primary gomb szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_glow',  'Primary gomb glow szin' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_bg',      'Ghost gomb hatter' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_border',  'Ghost gomb keret szin' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_hover',   'Ghost gomb hover hatter' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_text',     'Ghost gomb szöveg szín' ); ?>
                </table>

                <h2>Hero szöveg méretek (összes oldal)</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_size_home_hero_badge',    'Főoldal hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_home_hero_title',    'Főoldal hero cím méret (px)', 24, 120 ); ?>
                    <?php self::field_num( 'va_size_home_hero_sub',      'Főoldal hero alcím méret (px)', 10, 42 ); ?>
                    <?php self::field_select( 'va_weight_home_hero_sub',  'Főoldal hero alcím vastagság', $weights ); ?>
                    <?php self::field_num( 'va_size_home_hero_btn',      'Főoldal hero gomb szövegméret (px)', 10, 28 ); ?>

                    <?php self::field_num( 'va_size_kat_hero_badge',     'Kategória hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_title',     'Kategória hero cím méret (px)', 20, 110 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_sub',       'Kategória hero alcím méret (px)', 10, 40 ); ?>
                    <?php self::field_select( 'va_weight_kat_hero_sub',   'Kategória hero alcím vastagság', $weights ); ?>
                    <?php self::field_num( 'va_size_kat_hero_stat_num',  'Kategória hero stat szám méret (px)', 10, 44 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_stat_label','Kategória hero stat felirat méret (px)', 8, 24 ); ?>

                    <?php self::field_num( 'va_size_tax_hero_badge',     'Alkategória hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_tax_hero_title',     'Alkategória hero cím méret (px)', 18, 100 ); ?>
                    <?php self::field_num( 'va_size_tax_hero_lead',      'Alkategória hero leírás méret (px)', 10, 40 ); ?>
                    <?php self::field_num( 'va_size_tax_hero_count',     'Alkategória hero találatszám méret (px)', 10, 34 ); ?>

                    <?php self::field_num( 'va_size_contact_hero_badge', 'Kapcsolat hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_contact_hero_title', 'Kapcsolat hero cím méret (px)', 20, 120 ); ?>
                    <?php self::field_num( 'va_size_contact_hero_lead',  'Kapcsolat hero alcím méret (px)', 10, 40 ); ?>
                </table>

                <h2>Kapcsolat oldal színek – Hero szekció</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_contact_color_hero_title',        'Cím színe' ); ?>
                    <?php self::field_color( 'va_contact_color_hero_lead',         'Alcím / bev. szöveg színe' ); ?>
                    <?php self::field_color( 'va_contact_color_hero_badge_bg',     'Badge háttér' ); ?>
                    <?php self::field_color( 'va_contact_color_hero_badge_border', 'Badge keret' ); ?>
                    <?php self::field_color( 'va_contact_color_hero_badge_text',   'Badge szöveg színe' ); ?>
                    <?php self::field_color( 'va_contact_color_hero_glow',         'Háttér glow színe' ); ?>
                </table>

                <h2>Kapcsolat oldal színek – Bal kártyák</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_contact_color_card_border',        'Kártya keret (alap)' ); ?>
                    <?php self::field_color( 'va_contact_color_card_accent_border', 'Kártya keret (accent / 1. kártya)' ); ?>
                    <?php self::field_color( 'va_contact_color_card_icon',          'Ikon színe' ); ?>
                    <?php self::field_color( 'va_contact_color_card_icon_bg',       'Ikon háttér' ); ?>
                    <?php self::field_color( 'va_contact_color_card_icon_border',   'Ikon keret' ); ?>
                    <?php self::field_color( 'va_contact_color_card_title',         'Kártya cím színe' ); ?>
                    <?php self::field_color( 'va_contact_color_card_mini',          'Mini felirat színe (2-3. kártya cím)' ); ?>
                    <?php self::field_color( 'va_contact_color_card_text',          'Kártya szöveg színe' ); ?>
                    <?php self::field_color( 'va_contact_color_list_text',          'Lista elemek színe' ); ?>
                    <?php self::field_color( 'va_contact_color_list_dot',           'Lista bullet pont színe' ); ?>
                </table>

                <h2>Kapcsolat oldal színek – Űrlap</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_contact_color_form_title',         'Űrlap cím színe' ); ?>
                    <?php self::field_color( 'va_contact_color_form_label',         'Mező feliratok színe' ); ?>
                    <?php self::field_color( 'va_contact_color_input_bg',           'Input mező háttér' ); ?>
                    <?php self::field_color( 'va_contact_color_input_border',       'Input mező keret' ); ?>
                    <?php self::field_color( 'va_contact_color_input_text',         'Input mező szöveg színe' ); ?>
                    <?php self::field_color( 'va_contact_color_input_focus_border', 'Input fókusz keret színe' ); ?>
                    <?php self::field_color( 'va_contact_color_btn_bg',             'Küldés gomb háttér (alap)' ); ?>
                    <?php self::field_color( 'va_contact_color_btn_bg2',            'Küldés gomb gradiens vége' ); ?>
                    <?php self::field_color( 'va_contact_color_btn_text',           'Küldés gomb szöveg' ); ?>
                    <?php self::field_color( 'va_contact_color_btn_glow',           'Küldés gomb glow' ); ?>
                    <?php self::field_color( 'va_contact_color_btn_hover_glow',     'Küldés gomb hover glow' ); ?>
                </table>

                <h2>Hero sorköz magasságok</h2>
                <table class="form-table">
                    <?php self::field_decimal( 'va_lh_home_hero_title',    'Főoldal hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_home_hero_sub',      'Főoldal hero alcím sorköz', 0.8, 2.8, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_kat_hero_title',     'Kategória hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_kat_hero_sub',       'Kategória hero alcím sorköz', 0.8, 2.8, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_tax_hero_title',     'Alkategória hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_tax_hero_lead',      'Alkategória hero leírás sorköz', 0.8, 2.8, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_contact_hero_title', 'Kapcsolat hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_contact_hero_lead',  'Kapcsolat hero alcím sorköz', 0.8, 2.8, 0.01 ); ?>
                </table>

                <h2>Mobil skála finomhangolás</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_mobile_factor_hero',   'Hero mobil szorzó (%)', 70, 120 ); ?>
                </table>

                <?php submit_button( 'Design mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Hero szekció – teljes vezérlés ══════════════════════════ */
    public static function render_hero() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🎬 weingartnerauto.hu - Hero szekcio (teljes vezerles)</h1>
            <p class="description">A főoldali video hero összes eleme: szövegek, színek, overlay, bal csík, badge pont, gombok, scroll jelző.</p>
            <?php settings_errors( 'va_hero_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_hero_settings' ); ?>

                <h2>📝 Hero szövegek</h2>
                <table class="form-table">
                    <?php self::field_text( 'va_home_hero_badge_text',         'Badge szöveg' ); ?>
                    <?php self::field_text( 'va_home_hero_title_top',          'Cím 1. sor' ); ?>
                    <?php self::field_text( 'va_home_hero_title_bottom',       'Cím 2. sor' ); ?>
                    <?php self::field_text( 'va_home_hero_sub_text',           'Alcím szöveg' ); ?>
                    <?php self::field_text( 'va_home_hero_primary_cta_text',   'Piros gomb szöveg' ); ?>
                    <?php self::field_text( 'va_home_hero_secondary_cta_text', 'Ghost gomb szöveg' ); ?>
                </table>

                <h2>📐 Elrendezés</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_home_hero_align', 'Tartalom igazítás', [ 'left' => 'Bal', 'center' => 'Közép', 'right' => 'Jobb' ] ); ?>
                </table>

                <h2>🌫️ Overlay sötétítés</h2>
                <p class="description">A videó fölötti fekete gradient réteg erőssége (0 = átlátszó, 1 = teljesen fekete).</p>
                <table class="form-table">
                    <?php self::field_decimal( 'va_hero_overlay_top',      'Teteje opacitás (nav mögötti sötétség)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hero_overlay_mid_a',    'Közép-felső opacitás', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hero_overlay_mid_b',    'Közép-alsó opacitás', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hero_overlay_bottom_a', 'Alsó opacitás (alap tartalomba tűnés)', 0, 1, 0.01 ); ?>
                </table>

                <h2>📍 Bal oldali piros csík</h2>
                <table class="form-table">
                    <?php self::field_toggle( 'va_hero_stripe_show',    'Megjelenítés' ); ?>
                    <?php self::field_color(  'va_hero_stripe_color',   'Csík színe' ); ?>
                    <?php self::field_num(    'va_hero_stripe_width',   'Csík szélessége (px)', 1, 20 ); ?>
                    <?php self::field_decimal( 'va_hero_stripe_opacity','Csík átlátszósága (0-1)', 0, 1, 0.01 ); ?>
                </table>

                <h2>🔴 Badge pulzáló pont</h2>
                <table class="form-table">
                    <?php self::field_toggle( 'va_hero_badge_dot_show',  'Pont megjelenítése' ); ?>
                    <?php self::field_color(  'va_hero_badge_dot_color', 'Pont színe' ); ?>
                </table>

                <h2>🏷️ Badge háttér és keret</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_hero_badge_bg',         'Hatter szin' ); ?>
                    <?php self::field_color( 'va_color_hero_badge_border',      'Keret szin' ); ?>
                    <?php self::field_color( 'va_color_hero_badge_text',   'Szöveg szín' ); ?>
                </table>

                <h2>✍️ Cím és alcím színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_hero_title',      'Cím szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_title_span', 'Cím kiemelt szó szín (span)' ); ?>
                    <?php self::field_color( 'va_color_hero_sub',               'Alcim szin' ); ?>
                </table>

                <h2>🔴 Piros (primary) gomb</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_hero_btn_primary_bg',         'Háttér' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_hover', 'Hover hatter' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_text',       'Szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_hover_text', 'Hover szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_primary_glow',  'Glow szin' ); ?>
                </table>

                <h2>👻 Ghost gomb</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_hero_btn_ghost_bg',      'Hatter' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_border',  'Keret szin' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_hover',   'Hover hatter' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_text',         'Szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_hero_btn_ghost_hover_text',   'Hover szöveg szín' ); ?>
                </table>

                <h2>⬇️ Scroll jelző (jobb oldal lent)</h2>
                <table class="form-table">
                    <?php self::field_toggle( 'va_hero_scroll_show',       'Megjelenítés' ); ?>
                    <?php self::field_color(  'va_hero_scroll_line_color', 'Vonal szín' ); ?>
                    <?php self::field_color(  'va_hero_scroll_dot_color',  'Pont szín' ); ?>
                    <?php self::field_decimal( 'va_hero_scroll_opacity',   'Átlátszóság (0-1)', 0, 1, 0.01 ); ?>
                </table>

                <h2>🎬 Scroll gomb (videós pill)</h2>
                <table class="form-table">
                    <?php self::field_video( 'va_scroll_ring_video_url',    'Háttér videó URL' ); ?>
                    <?php self::field_color( 'va_scroll_ring_border_color', 'Border szín' ); ?>
                </table>

                <?php submit_button( 'Hero beállítások mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Fejléc + Lábléc oldal (maximális paraméterezés) ════════ */
    public static function render_header_footer() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $weights = [
            '300' => '300 – Light',
            '400' => '400 – Normal',
            '500' => '500 – Medium',
            '600' => '600 – SemiBold',
            '700' => '700 – Bold',
            '800' => '800 – ExtraBold',
            '900' => '900 – Black',
        ];

        $preset_msg = isset( $_GET['va_hf_preset'] ) ? sanitize_key( (string) $_GET['va_hf_preset'] ) : '';
        $presets = self::get_header_footer_presets();
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🧩 weingartnerauto.hu - Fejlec + Lablec (100% kontroll)</h1>
            <p class="description">Külön menüből finomhangolhatod a fejléc és a lábléc layoutját, szövegeit, méreteit, mobil viselkedését és vizuális részleteit.</p>
            <?php if ( $preset_msg === 'ok' ): ?>
                <div class="notice notice-success"><p>Preset alkalmazva. Mentés nélkül is azonnal él.</p></div>
            <?php elseif ( $preset_msg === 'invalid' ): ?>
                <div class="notice notice-error"><p>Ismeretlen preset kulcs.</p></div>
            <?php endif; ?>
            <?php settings_errors( 'va_header_footer_settings' ); ?>

            <!-- ──────── PRESETEK ──────── -->
            <div class="va-settings-card" style="margin-bottom:28px;">
                <div class="va-settings-card__head">
                    <span class="va-settings-card__icon">🎛️</span>
                    <span class="va-settings-card__title">Egy kattintásos modern presetek</span>
                    <span class="va-settings-card__desc">10 összehangolt paletta, árnyék és glow kombóval</span>
                </div>
                <div style="padding:20px;">
                    <div class="va-preset-grid">
                        <?php foreach ( $presets as $preset_key => $preset ):
                            $sw_base   = $preset['options']['va_hf_header_color_base']   ?? '#0a0a0a';
                            $sw_alt    = $preset['options']['va_hf_header_color_alt']    ?? '#1a1a1a';
                            $sw_accent = $preset['options']['va_hf_header_border_color'] ?? '#ff2020';
                        ?>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="va-preset-card">
                                <div class="va-preset-card__swatch" style="background:linear-gradient(135deg,<?php echo esc_attr($sw_base); ?> 0%,<?php echo esc_attr($sw_alt); ?> 55%,<?php echo esc_attr($sw_accent); ?> 100%);"></div>
                                <input type="hidden" name="action" value="va_apply_hf_preset">
                                <input type="hidden" name="preset_key" value="<?php echo esc_attr( $preset_key ); ?>">
                                <?php wp_nonce_field( 'va_apply_hf_preset' ); ?>
                                <strong><?php echo esc_html( $preset['label'] ); ?></strong>
                                <p><?php echo esc_html( $preset['desc'] ); ?></p>
                                <button type="submit" class="button button-secondary">Alkalmazás</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ──────── FŐ FORM ──────── -->
            <form method="post" action="options.php">
                <?php settings_fields( 'va_header_footer_settings' ); ?>

                <!-- FEJLÉC ALAPSZÍNEK -->
                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🎨</span>
                            <span class="va-settings-card__title">Fejléc alapszínek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_color_header_bg',     'Hatter' ); ?>
                                <?php self::field_color( 'va_color_header_text',   'Szöveg szín' ); ?>
                                <?php self::field_color( 'va_color_header_accent', 'Accent szín' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔗</span>
                            <span class="va-settings-card__title">Nav link hover</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_color_header_nav_link',  'Nav link alap szín' ); ?>
                                <?php self::field_color( 'va_color_header_nav_hover', 'Nav link hover szín' ); ?>
                                <?php self::field_color( 'va_color_header_nav_underline', 'Nav alsó vonal szín' ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- FEJLÉC LAYOUT -->
                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📐</span>
                            <span class="va-settings-card__title">Fejléc layout</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_num( 'va_hf_header_height',         'Magasság (px)', 50, 120 ); ?>
                                <?php self::field_num( 'va_header_logo_height',       'Logó magasság (px)', 20, 120 ); ?>
                                <?php self::field_num( 'va_hf_header_max_width',       'Belső max szélesség (px)', 960, 2200 ); ?>
                                <?php self::field_num( 'va_hf_header_padding_x',       'Vízszintes padding (px)', 0, 80 ); ?>
                                <?php self::field_num( 'va_hf_header_padding_top',     'Felső padding (px)', 0, 30 ); ?>
                                <?php self::field_num( 'va_hf_header_padding_bottom',  'Alsó padding (px)', 0, 30 ); ?>
                                <?php self::field_num( 'va_hf_header_gap',             'Elemek közti gap (px)', 0, 40 ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🪟</span>
                            <span class="va-settings-card__title">Üveg-hatás &amp; árnyék</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_decimal( 'va_hf_header_bg_opacity',          'Háttér opacitás (0-1)', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_header_bg_opacity_scrolled', 'Opacitás scroll után (0-1)', 0, 1, 0.01 ); ?>
                                <?php self::field_num( 'va_hf_header_blur',                    'Blur (px)', 0, 40 ); ?>
                                <?php self::field_num( 'va_hf_header_blur_scrolled',           'Blur scroll után (px)', 0, 44 ); ?>
                                <?php self::field_decimal( 'va_hf_header_shadow_alpha',        'Árnyék opacitás (0-1)', 0, 1, 0.01 ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- FEJLÉC SZÍNPALETTA -->
                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">✨</span>
                            <span class="va-settings-card__title">Fejléc neon &amp; glow</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_hf_header_color_base',    'Gradient alap szín' ); ?>
                                <?php self::field_color( 'va_hf_header_color_alt',     'Gradient másodlagos szín' ); ?>
                                <?php self::field_color( 'va_hf_header_border_color',  'Alsó border szín' ); ?>
                                <?php self::field_color( 'va_hf_header_shadow_color',      'Fo arnyak szin' ); ?>
                                <?php self::field_color( 'va_hf_header_glow_color',        'Fejlec neon glow' ); ?>
                                <?php self::field_color( 'va_hf_header_search_glow_color', 'Kereso glow szin' ); ?>
                                <?php self::field_color( 'va_hf_header_btn_glow_color',    'CTA gomb glow szin' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔍</span>
                            <span class="va-settings-card__title">Kereső részletes vezérlés</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_num( 'va_hf_header_search_max_width',              'Max szélesség (px)', 220, 760 ); ?>
                                <?php self::field_num( 'va_hf_header_search_height',                 'Magasság (px)', 30, 64 ); ?>
                                <?php self::field_num( 'va_hf_header_search_radius',                 'Lekerekítés (px)', 8, 999 ); ?>
                                <?php self::field_decimal( 'va_hf_header_search_border_alpha',       'Keret opacitás', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_header_search_bg_alpha',           'Háttér opacitás', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_header_search_hover_border_alpha', 'Keret opacitás hover', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_header_search_focus_border_alpha', 'Keret opacitás fókusz', 0, 1, 0.01 ); ?>
                                <?php self::field_num( 'va_hf_header_search_icon_size',              'Nagyító ikon méret (px)', 10, 28 ); ?>
                                <?php self::field_decimal( 'va_hf_header_search_icon_bg_alpha',       'Nagyító háttér opacitás', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_header_search_icon_bg_hover_alpha', 'Nagyító háttér hover opacitás', 0, 1, 0.01 ); ?>
                                <?php self::field_color( 'va_color_header_search_border',             'Kereső keret szín' ); ?>
                                <?php self::field_color( 'va_color_header_search_hover_border',       'Kereső keret hover szín' ); ?>
                                <?php self::field_color( 'va_color_header_search_focus_border',       'Kereső keret fókusz szín' ); ?>
                                <?php self::field_color( 'va_color_header_search_text',               'Kereső betűszín' ); ?>
                                <?php self::field_color( 'va_color_header_search_placeholder',        'Kereső placeholder szín' ); ?>
                                <?php self::field_color( 'va_color_header_search_btn_bg',             'Kereső gomb szín' ); ?>
                                <?php self::field_color( 'va_color_header_search_btn_hover_bg',       'Kereső gomb hover szín' ); ?>
                                <?php self::field_text( 'va_hf_header_search_placeholder',           'Placeholder szöveg' ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- GOMBOK HOVER -->
                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔴</span>
                            <span class="va-settings-card__title">CTA gomb (+ Hirdetés feladása)</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_num( 'va_hf_header_btn_radius',          'Lekerekítés (px)', 8, 999 ); ?>
                                <?php self::field_num( 'va_hf_header_btn_pad_y',           'Függőleges padding (px)', 4, 20 ); ?>
                                <?php self::field_num( 'va_hf_header_btn_pad_x',           'Vízszintes padding (px)', 8, 40 ); ?>
                                <?php self::field_decimal( 'va_hf_header_btn_glow_alpha',  'Glow opacitás (0-1)', 0, 1, 0.01 ); ?>
                                <?php self::field_color( 'va_color_header_submit_hover_bg',   'Hover háttér (hex/rgba)' ); ?>
                                <?php self::field_color( 'va_color_header_submit_hover_text', 'Hover szöveg szín' ); ?>
                                <?php self::field_text( 'va_hf_header_submit_text',           'Felirat (bejelentkezve)' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">👤</span>
                            <span class="va-settings-card__title">Bejelentkezés gomb hover</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_color_header_login_hover_bg',   'Hover háttér (hex/rgba)' ); ?>
                                <?php self::field_color( 'va_color_header_login_hover_text', 'Hover szöveg szín' ); ?>
                                <?php self::field_decimal( 'va_hf_header_user_border_alpha', 'Keret opacitás (0-1)', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_header_user_bg_alpha',     'Háttér opacitás (0-1)', 0, 1, 0.01 ); ?>
                                <?php self::field_text( 'va_hf_header_login_text',            'Felirat (vendégként)' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📝</span>
                            <span class="va-settings-card__title">Regisztráció gomb hover</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_color_header_register_hover_bg',   'Hover háttér (hex/rgba)' ); ?>
                                <?php self::field_color( 'va_color_header_register_hover_text', 'Hover szöveg szín' ); ?>
                                <?php self::field_text( 'va_hf_header_register_text',            'Felirat (vendégként)' ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- TIPOGRÁFIA & MOBIL -->
                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔤</span>
                            <span class="va-settings-card__title">Fejléc tipográfia</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_select( 'va_weight_header_brand', 'Brand név súly', $weights ); ?>
                                <?php self::field_select( 'va_weight_header_nav',   'Navigáció súly', $weights ); ?>
                                <?php self::field_num( 'va_size_header_brand',      'Brand név méret (px)', 10, 44 ); ?>
                                <?php self::field_num( 'va_size_header_nav',        'Navigáció méret (px)', 10, 34 ); ?>
                                <?php self::field_num( 'va_size_header_search',     'Kereső szövegméret (px)', 10, 30 ); ?>
                                <?php self::field_num( 'va_size_header_btn',        'Gomb szövegméret (px)', 10, 30 ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📱</span>
                            <span class="va-settings-card__title">Mobil viselkedés</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_toggle( 'va_hf_header_mobile_show_search', 'Kereső mobilon is' ); ?>
                                <?php self::field_toggle( 'va_hf_header_mobile_show_submit', 'CTA gomb mobilon is' ); ?>
                                <?php self::field_toggle( 'va_hf_header_show_buy_button', 'Vásárlás gomb megjelenítése' ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- LÁBLÉC -->
                <h2 style="margin:36px 0 20px;font-size:15px !important;color:#fff !important;text-transform:none !important;letter-spacing:0 !important;border-bottom:1px solid var(--va-border) !important;padding-bottom:12px !important;">🦶 Lábléc beállítások</h2>

                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📐</span>
                            <span class="va-settings-card__title">Lábléc layout &amp; spacing</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_num( 'va_hf_footer_top_padding',        'Felső padding (px)', 12, 120 ); ?>
                                <?php self::field_num( 'va_hf_footer_bottom_padding',     'Alsó padding (px)', 8, 80 ); ?>
                                <?php self::field_num( 'va_hf_footer_grid_gap',           'Oszlop gap (px)', 8, 80 ); ?>
                                <?php self::field_num( 'va_hf_footer_col_min_width',      'Oszlop min szélesség (px)', 120, 420 ); ?>
                                <?php self::field_num( 'va_hf_footer_title_gap',          'Oszlopcím alsó margó (px)', 4, 36 ); ?>
                                <?php self::field_num( 'va_hf_footer_link_pad_y',         'Link függőleges térköz (px)', 0, 20 ); ?>
                                <?php self::field_num( 'va_hf_footer_bottom_top_padding', 'Alsó sor felső padding (px)', 6, 40 ); ?>
                                <?php self::field_num( 'va_hf_footer_max_width',          'Tartalom max szélesség (px)', 800, 2200 ); ?>
                                <?php self::field_decimal( 'va_hf_footer_border_alpha',        'Felső keret opacitás', 0, 1, 0.01 ); ?>
                                <?php self::field_decimal( 'va_hf_footer_bottom_border_alpha', 'Alsó sor keret opacitás', 0, 1, 0.01 ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🎨</span>
                            <span class="va-settings-card__title">Lábléc színek &amp; glow</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_color_footer_bg',       'Háttér alapszín' ); ?>
                                <?php self::field_color( 'va_color_footer_text',     'Szoveg szin' ); ?>
                                <?php self::field_color( 'va_color_footer_headings', 'Oszlop cím szín' ); ?>
                                <?php self::field_color( 'va_color_footer_links',    'Link alapszín' ); ?>
                                <?php self::field_color( 'va_hf_footer_color_base',  'Gradient alap' ); ?>
                                <?php self::field_color( 'va_hf_footer_color_alt',   'Gradient másodlagos' ); ?>
                                <?php self::field_color( 'va_hf_footer_border_color',     'Border szín' ); ?>
                                <?php self::field_text(  'va_hf_footer_shadow_color',     'Árnyék szín (hex/rgba)' ); ?>
                                <?php self::field_text(  'va_hf_footer_glow_color',       'Glow szín (hex/rgba)' ); ?>
                                <?php self::field_color( 'va_hf_footer_link_hover_color', 'Link hover szín' ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="va-settings-grid">

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔤</span>
                            <span class="va-settings-card__title">Lábléc tipográfia</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_select( 'va_weight_footer_title', 'Oszlopcím súly', $weights ); ?>
                                <?php self::field_select( 'va_weight_footer_link',  'Link súly', $weights ); ?>
                                <?php self::field_num( 'va_size_footer_title',      'Oszlopcím méret (px)', 10, 34 ); ?>
                                <?php self::field_num( 'va_size_footer_link',       'Link méret (px)', 10, 30 ); ?>
                                <?php self::field_num( 'va_size_footer_bottom',     'Alsó sor méret (px)', 10, 28 ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🖼️</span>
                            <span class="va-settings-card__title">Lábléc logó &amp; szövegek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_media( 'va_hf_footer_logo_url',         'Logó (opcionális)' ); ?>
                                <?php self::field_num(   'va_hf_footer_logo_height',       'Logó magasság (px)', 20, 180 ); ?>
                                <?php self::field_text( 'va_hf_footer_brand_title',          'Brand oszlop cím' ); ?>
                                <?php self::field_text( 'va_hf_footer_col_categories_title', 'Kategóriák oszlop cím' ); ?>
                                <?php self::field_text( 'va_hf_footer_col_account_title',    'Fiók oszlop cím' ); ?>
                                <?php self::field_text( 'va_hf_footer_col_legal_title',      'Jogi oszlop cím' ); ?>
                                <?php self::field_text( 'va_hf_footer_link_aszf',            'ÁSZF link felirat' ); ?>
                                <?php self::field_text( 'va_hf_footer_link_privacy',         'Adatvédelem link felirat' ); ?>
                                <?php self::field_text( 'va_hf_footer_link_contact',         'Kapcsolat link felirat' ); ?>
                                <?php self::field_text( 'va_hf_footer_link_help',            'Súgó link felirat' ); ?>
                                <?php self::field_text( 'va_hf_footer_copy_text',            'Copyright szöveg' ); ?>
                                <?php self::field_text( 'va_hf_footer_privacy_text',         'Alsó sor adatvédelem felirat' ); ?>
                            </table>
                        </div>
                    </div>

                </div>

                <?php submit_button( 'Fejléc + Lábléc mentése' ); ?>
            </form>

            <!-- ═══ Nav gombok szerkesztő (külön form, JSON mentés) ═══ -->
            <div class="va-settings-card" style="margin-top:32px;">
                <div class="va-settings-card__head">
                    <span class="va-settings-card__icon">🔗</span>
                    <span class="va-settings-card__title">Navigációs gombok szerkesztése</span>
                    <span class="va-settings-card__desc">Drag &amp; drop sorrendezés</span>
                </div>
                <div style="padding:20px;">
            <?php
            $nav_json = get_option( 'va_nav_items_json', '' );
            $nav_items_saved = [];
            if ( $nav_json ) {
                $decoded = json_decode( $nav_json, true );
                if ( is_array( $decoded ) ) $nav_items_saved = $decoded;
            }
            if ( empty( $nav_items_saved ) ) {
                $nav_items_saved = [
                    [ 'label' => 'Hirdetések', 'url' => '/va-hirdetes-kereses', 'enabled' => true ],
                    [ 'label' => 'Kategóriák', 'url' => '/kategoria',           'enabled' => true ],
                    [ 'label' => 'Kapcsolat',  'url' => '/kapcsolat',           'enabled' => true ],
                ];
            }
            if ( isset( $_GET['va_nav_saved'] ) ): ?>
                <div class="notice notice-success is-dismissible"><p>Navigációs gombok elmentve.</p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" id="va-nav-form">
                <?php wp_nonce_field( 'va_save_nav_items', 'va_nav_nonce' ); ?>
                <input type="hidden" name="action" value="va_save_nav_items">
                <input type="hidden" name="va_nav_json" id="va-nav-json-input" value="<?php echo esc_attr( $nav_json ?: wp_json_encode( $nav_items_saved ) ); ?>">

                <div id="va-nav-list" style="max-width:700px;margin-bottom:16px;">
                    <?php foreach ( $nav_items_saved as $idx => $item ): ?>
                    <div class="va-nav-row va-nav-row-modern" draggable="true">
                        <span class="va-nav-drag">&#8597;</span>
                        <input type="checkbox" class="va-nav-enabled" <?php checked( $item['enabled'] ?? true ); ?> title="Megjelenítés">
                        <input type="text" class="va-nav-label regular-text" value="<?php echo esc_attr( $item['label'] ); ?>" placeholder="Felirat" style="width:200px;">
                        <input type="text" class="va-nav-url regular-text" value="<?php echo esc_attr( $item['url'] ); ?>" placeholder="/url vagy https://..." style="flex:1;">
                        <button type="button" class="button va-nav-del va-nav-del-btn" title="Törlés">&times;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="va-nav-add">+ Új gomb hozzáadása</button>
                &nbsp;
                <?php submit_button( 'Navigáció mentése', 'primary', 'va-nav-submit', false ); ?>
            </form>
            <script>
            (function(){
                var list = document.getElementById('va-nav-list');
                var jsonInput = document.getElementById('va-nav-json-input');

                function collectJSON() {
                    var rows = list.querySelectorAll('.va-nav-row');
                    var items = [];
                    rows.forEach(function(row) {
                        items.push({
                            label:   row.querySelector('.va-nav-label').value.trim(),
                            url:     row.querySelector('.va-nav-url').value.trim(),
                            enabled: row.querySelector('.va-nav-enabled').checked
                        });
                    });
                    jsonInput.value = JSON.stringify(items);
                }

                document.getElementById('va-nav-form').addEventListener('submit', collectJSON);

                document.getElementById('va-nav-add').addEventListener('click', function(){
                    var div = document.createElement('div');
                    div.className = 'va-nav-row va-nav-row-modern';
                    div.draggable = true;
                    div.innerHTML = '<span class="va-nav-drag">&#8597;</span>'
                        + '<input type="checkbox" class="va-nav-enabled" checked title="Megjelenítés">'
                        + '<input type="text" class="va-nav-label regular-text" value="" placeholder="Felirat" style="width:200px;">'
                        + '<input type="text" class="va-nav-url regular-text" value="" placeholder="/url vagy https://..." style="flex:1;">'
                        + '<button type="button" class="button va-nav-del va-nav-del-btn" title="Törlés">&times;</button>';
                    list.appendChild(div);
                    bindDel(div);
                    bindDrag(div);
                });

                function bindDel(row) {
                    row.querySelector('.va-nav-del').addEventListener('click', function(){
                        row.remove();
                    });
                }
                function bindDrag(row) {
                    row.addEventListener('dragstart', function(e) {
                        e.dataTransfer.effectAllowed = 'move';
                        row.classList.add('va-nav-dragging');
                        window._vaDragSrc = row;
                    });
                    row.addEventListener('dragend', function() { row.classList.remove('va-nav-dragging'); });
                    row.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        var src = window._vaDragSrc;
                        if (src && src !== row) {
                            var rect = row.getBoundingClientRect();
                            var mid  = rect.top + rect.height / 2;
                            if (e.clientY < mid) list.insertBefore(src, row);
                            else list.insertBefore(src, row.nextSibling);
                        }
                    });
                }
                list.querySelectorAll('.va-nav-row').forEach(function(r){ bindDel(r); bindDrag(r); });
            })();
            </script>
                </div><!-- end padding div -->
            </div><!-- end va-settings-card -->
        </div><!-- end wrap -->
        <?php
    }

    /* ══ Adatvédelem + ÁSZF (lábléc jogi linkek) ═══════════ */
    public static function render_legal_pages() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        if ( isset( $_GET['settings-updated'] ) ) {
            self::sync_legal_pages_from_options();
            echo '<div class="notice notice-success"><p>Jogi oldalak frissítve. A kitöltött tartalmak automatikusan megjelennek a láblécben.</p></div>';
        }
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🛡️ Adatvédelem + ÁSZF</h1>
            <p class="description">Ide írd a publikus jogi tartalmakat. Amelyik blokkban van tartalom, ahhoz oldal készül/frissül, és megjelenik a lábléc Jogi oszlopában.</p>
            <?php settings_errors( 'va_header_footer_settings' ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_header_footer_settings' ); ?>

                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">⚖️</span>
                            <span class="va-settings-card__title">Lábléc jogi linkek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_textarea( 'va_legal_content_adatvedelem',  'Adatvédelem tartalom', '', 8 ); ?>
                                <?php self::field_textarea( 'va_legal_content_aszf',         'ÁSZF tartalom', '', 8 ); ?>
                                <?php self::field_textarea( 'va_legal_content_impresszum',   'Impresszum tartalom', '', 8 ); ?>
                                <?php self::field_textarea( 'va_legal_content_etika',        'Etika és Üzleti Magatartási Kódex tartalom', '', 8 ); ?>
                                <?php self::field_textarea( 'va_legal_content_sutik',        'Sütik tartalom', '', 8 ); ?>
                                <?php self::field_textarea( 'va_legal_content_gdpr',         'GDPR Adatkezelési Tájékoztató tartalom', '', 8 ); ?>
                                <?php self::field_textarea( 'va_legal_content_fenntarthato', 'Fenntartható Fejlődés Irányelve tartalom', '', 8 ); ?>
                            </table>
                            <p class="description" style="margin-top:8px;">Mentés után az oldalak automatikusan frissülnek, a láblécben pedig csak a kitöltött elemek látszanak.</p>
                        </div>
                    </div>
                </div>

                <?php submit_button( 'Adatvédelem + ÁSZF mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Layout Builder oldal ═════════════════════════════ */
    public static function render_layout_builder() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🧩 weingartnerauto.hu - Layout Allito (Divi / Porto mintara)</h1>
            <p class="description">Nagy léptékű layout finomhangolás: konténer, rácsok, oldalsáv, kártyák, árnyékok, tördelés, mobil viselkedés.</p>
            <?php settings_errors( 'va_layout_settings' ); ?>

            <div class="va-layout-guide">
                <div class="va-layout-guide__card">
                    <h3>Konténer + oldalpárna</h3>
                    <div class="va-diagram va-diagram--container">
                        <div class="va-d-outer">
                            <div class="va-d-gutter"></div>
                            <div class="va-d-inner"></div>
                            <div class="va-d-gutter"></div>
                        </div>
                    </div>
                    <p>Az <strong>Oldal max szélesség</strong> és a <strong>Konténer oldalpárna</strong> itt rajzolódik ki.</p>
                </div>

                <div class="va-layout-guide__card">
                    <h3>Töréspontok (responsive)</h3>
                    <div class="va-diagram va-diagram--breakpoints">
                        <div class="va-d-bp va-d-bp--desktop">Desktop</div>
                        <div class="va-d-arrow">→</div>
                        <div class="va-d-bp va-d-bp--tablet">Tablet</div>
                        <div class="va-d-arrow">→</div>
                        <div class="va-d-bp va-d-bp--mobile">Mobil</div>
                    </div>
                    <p>A <strong>desktop → tablet</strong> és <strong>tablet → mobil</strong> váltás pontos px értékekkel állítható.</p>
                </div>

                <div class="va-layout-guide__card">
                    <h3>Kártya anatómia</h3>
                    <div class="va-diagram va-diagram--card">
                        <div class="va-d-card-img"></div>
                        <div class="va-d-card-body">
                            <div class="va-d-line va-d-line--title"></div>
                            <div class="va-d-line va-d-line--price"></div>
                            <div class="va-d-line va-d-line--meta"></div>
                        </div>
                    </div>
                    <p>Itt állítod a <strong>radius</strong>, <strong>padding</strong>, <strong>árnyék</strong>, <strong>tipográfia</strong> értékeit.</p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_layout_settings' ); ?>

                <h2>Preset</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_layout_preset', 'Layout preset', [
                        'porto' => 'Porto (kompakt, commerce fókusz)',
                        'divi'  => 'Divi (légiesebb, moduláris)',
                        'custom'=> 'Custom (kézi finomhangolás)',
                    ] ); ?>
                </table>

                <h2>Konténer és Tördelés</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_page_max_width', 'Oldal max szélesség (px)', 960, 2200 ); ?>
                    <?php self::field_num( 'va_layout_container_pad_x', 'Konténer oldalpárna desktop (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_layout_container_pad_x_mobile', 'Konténer oldalpárna mobil (px)', 0, 40 ); ?>
                    <?php self::field_num( 'va_layout_main_pad_y', 'Főtartalom függőleges padding (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_layout_main_pad_x', 'Főtartalom vízszintes padding (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_layout_home_main_pad_x', 'Főoldal extra vízszintes padding (px)', 0, 100 ); ?>
                    <?php self::field_num( 'va_layout_content_gap', 'Tartalmi oszlopok közti gap (px)', 0, 64 ); ?>
                </table>

                <h2>Oldalsáv</h2>
                <table class="form-table">
                    <?php self::field_toggle( 'va_layout_show_right_sidebar', 'Jobb oldalsáv megjelenjen desktopon' ); ?>
                    <?php self::field_num( 'va_layout_right_sidebar_width', 'Jobb oldalsáv szélesség (px)', 220, 520 ); ?>
                    <?php self::field_num( 'va_layout_right_sidebar_sticky_top', 'Jobb oldalsáv sticky offset (px)', 0, 180 ); ?>
                </table>

                <h2>Hirdetés Rács</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_layout_home_sidebar_side', 'Főoldal sidebar pozíció', [
                        'left'  => '◀ Bal oldal (termékek jobbra)',
                        'right' => '▶ Jobb oldal (termékek balra)',
                        'none'  => '✕ Rejtve (termékek teljes szélességben)',
                    ] ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_desktop', 'Desktop oszlopok száma (sor/kártya)', 2, 10 ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_tablet', 'Tablet oszlopok száma (sor/kártya)', 1, 4 ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_mobile', 'Mobil oszlopok száma (sor/kártya)', 1, 2 ); ?>
                    <?php self::field_select( 'va_layout_grid_justify', 'Rács igazítás — Desktop', [ 'start' => 'Balra', 'center' => 'Középre', 'end' => 'Jobbra' ] ); ?>
                    <?php self::field_select( 'va_layout_grid_justify_tablet', 'Rács igazítás — Tablet', [ 'start' => 'Balra', 'center' => 'Középre', 'end' => 'Jobbra' ] ); ?>
                    <?php self::field_select( 'va_layout_grid_justify_mobile', 'Rács igazítás — Mobil', [ 'start' => 'Balra', 'center' => 'Középre', 'end' => 'Jobbra' ] ); ?>
                    <?php self::field_num( 'va_layout_grid_gap', 'Kártyák közti gap (px)', 4, 40 ); ?>
                    <?php self::field_num( 'va_layout_bp_desktop_tablet', 'Töréspont: desktop → tablet (px)', 680, 2000 ); ?>
                    <?php self::field_num( 'va_layout_bp_tablet_mobile', 'Töréspont: tablet → mobil (px)', 320, 1200 ); ?>
                    <?php self::field_num( 'va_layout_bp_sidebar_hide', 'Jobb oldalsáv rejtése (px alatt)', 480, 1800 ); ?>
                </table>

                <h2>Mobil Preview (felső admin sáv)</h2>
                <p class="description">Frontend nézetben, bejelentkezett adminként a felső fekete sávban megjelenik egy <strong>VA Breakpoint Preview</strong> menü. Itt 1 kattintással válthatsz preset szélességekre, és <strong>egyedi px</strong> értéket is megadhatsz (Bricks-szerű workflow).</p>

                <h2>Kártya Design</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_card_radius', 'Kártya lekerekítés (px)', 0, 28 ); ?>
                    <?php self::field_decimal( 'va_layout_card_border_alpha', 'Kártya border erősség (0..1)', 0, 1, 0.01 ); ?>
                    <?php self::field_num( 'va_layout_card_padding_y', 'Kártya belső padding Y (px)', 6, 40 ); ?>
                    <?php self::field_num( 'va_layout_card_padding_x', 'Kártya belső padding X (px)', 6, 40 ); ?>
                    <?php self::field_num( 'va_layout_card_title_size', 'Kártya cím méret (px)', 12, 28 ); ?>
                    <?php self::field_num( 'va_layout_card_price_size', 'Kártya ár méret (px)', 12, 36 ); ?>
                    <?php self::field_num( 'va_layout_card_meta_size', 'Kártya meta méret (px)', 10, 20 ); ?>
                    <?php self::field_select( 'va_layout_card_img_ratio', 'Kártya képarány', [
                        '4/3' => '4:3 (klasszikus)',
                        '16/10' => '16:10 (szélesebb)',
                        '1/1' => '1:1 (négyzetes)',
                        '3/2' => '3:2',
                    ] ); ?>
                </table>

                <h2>Interakció és Widgetek</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_card_hover_lift', 'Kártya hover emelés (px)', 0, 16 ); ?>
                    <?php self::field_num( 'va_layout_card_shadow_strength', 'Kártya árnyék erősség (%)', 0, 100 ); ?>
                    <?php self::field_num( 'va_layout_card_shadow_red', 'Kártya vörös glow erősség (%)', 0, 100 ); ?>
                    <?php self::field_num( 'va_layout_widget_radius', 'Widget lekerekítés (px)', 0, 28 ); ?>
                    <?php self::field_num( 'va_layout_widget_padding', 'Widget belső padding (px)', 6, 40 ); ?>
                </table>

                <?php submit_button( 'Layout mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Admin Panel személyre szabás ══════════════════════ */
    public static function render_adminpanel(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        wp_enqueue_media();

        $fonts = [
            'system'             => 'System UI (natív, leggyorsabb)',
            'arial'              => '– Arial',
            'verdana'            => '– Verdana',
            'tahoma'             => '– Tahoma',
            'inter'              => 'Inter',
            'roboto'             => 'Roboto',
            'open-sans'          => 'Open Sans',
            'poppins'            => 'Poppins',
            'lato'               => 'Lato',
            'montserrat'         => 'Montserrat (alapértelmezett)',
            'nunito'             => 'Nunito',
            'raleway'            => 'Raleway',
            'dm-sans'            => 'DM Sans',
            'manrope'            => 'Manrope',
            'work-sans'          => 'Work Sans',
            'rubik'              => 'Rubik',
            'cabin'              => 'Cabin',
            'mulish'             => 'Mulish',
            'figtree'            => 'Figtree',
            'outfit'             => 'Outfit',
            'jost'               => 'Jost',
            'source-sans-3'      => 'Source Sans 3',
            'fira-sans'          => 'Fira Sans',
            'ibm-plex-sans'      => 'IBM Plex Sans',
            'oswald'             => 'Oswald',
        ];

        $presets = self::get_adminpanel_presets();
        $preset_msg = isset( $_GET['va_ap_preset'] ) ? sanitize_key( (string) $_GET['va_ap_preset'] ) : '';
        $g = static fn( string $k, string $d = '' ) => (string) ( get_option( $k, $d ) ?: $d );
        ?>
        <div class="wrap va-admin-wrap va-aps-wrap">
            <h1>🖥️ Admin Panel – Teljes személyre szabás</h1>
            <p class="description" style="margin-bottom:20px;">Állítsd be az admin felület teljes megjelenését: szín, betűtípus, méretek, logó és branding. Minden azonnal él mentés után.</p>

            <?php if ( $preset_msg === 'ok' ): ?>
            <div class="notice notice-success is-dismissible"><p>✅ Preset alkalmazva és elmentve!</p></div>
            <?php elseif ( $preset_msg === 'invalid' ): ?>
            <div class="notice notice-error is-dismissible"><p>❌ Ismeretlen preset kulcs.</p></div>
            <?php endif; ?>
            <?php settings_errors( 'va_ap_settings' ); ?>

            <!-- ══ Presetek ══════════════════════════════════════ -->
            <div class="va-aps-presets-box">
                <div class="va-aps-presets-hdr">
                    <h2>🎨 Egy kattintásos presetek</h2>
                    <p>10 előre összehangolt Admin Panel téma – választ után minden szín és beállítás azonnal frissül.</p>
                </div>
                <div class="va-aps-presets-grid">
                    <?php foreach ( $presets as $pk => $pr ): ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="va-aps-pf">
                        <input type="hidden" name="action" value="va_apply_ap_preset">
                        <input type="hidden" name="preset_key" value="<?php echo esc_attr( $pk ); ?>">
                        <?php wp_nonce_field( 'va_apply_ap_preset' ); ?>
                        <button type="submit" class="va-aps-preset">
                            <span class="va-aps-swatches">
                                <span style="background:<?php echo esc_attr( $pr['bg2'] ); ?>"></span>
                                <span style="background:<?php echo esc_attr( $pr['bg'] ); ?>"></span>
                                <span style="background:<?php echo esc_attr( $pr['accent'] ); ?>"></span>
                                <span style="background:<?php echo esc_attr( $pr['accent2'] ); ?>"></span>
                            </span>
                            <strong><?php echo esc_html( $pr['label'] ); ?></strong>
                            <small><?php echo esc_html( $pr['desc'] ); ?></small>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ══ Fő layout: form + live preview ═══════════════ -->
            <div class="va-aps-main">

                <!-- Form -->
                <div class="va-aps-form-col">
                    <form method="post" action="options.php" id="va-aps-form">
                        <?php settings_fields( 'va_ap_settings' ); ?>

                        <!-- Márka -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">🏷️</div>
                                <div>
                                    <strong>Márka és logó</strong>
                                    <p>Panel neve, ikonja, sidebar logó</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_text(  'va_ap_panel_name',  'Panel neve (sidebar fejléc)' ); ?>
                                <?php self::field_text(  'va_ap_panel_icon',  'Panel ikon (emoji, pl. 🎯)' ); ?>
                                <?php self::field_media( 'va_ap_logo_url',    'Sidebar logó kép (opcionális – ha van, az ikon helyére kerül)' ); ?>
                                <?php self::field_num(   'va_ap_logo_height', 'Logó magasság (px)', 16, 200 ); ?>
                            </table>
                        </div>

                        <!-- Háttér színek -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">🌑</div>
                                <div>
                                    <strong>Háttér rétegek</strong>
                                    <p>Főháttér, sidebar, kártyák, hover állapot</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_color( 'va_ap_color_bg',  'Főháttér (--va-bg)' ); ?>
                                <?php self::field_color( 'va_ap_color_bg2', 'Sidebar / topbar háttér (--va-bg2)' ); ?>
                                <?php self::field_color( 'va_ap_color_bg3', 'Kártya / panel háttér (--va-bg3)' ); ?>
                                <?php self::field_color( 'va_ap_color_bg4', 'Hover / aktív háttér (--va-bg4)' ); ?>
                            </table>
                        </div>

                        <!-- Szöveg és szegélyek -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">✍️</div>
                                <div>
                                    <strong>Szöveg és szegélyek</strong>
                                    <p>Alap szöveg, halvány szöveg, keretek</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_color( 'va_ap_color_text', 'Fő szövegszín (--va-text)' ); ?>
                                <?php self::field_text(  'va_ap_color_muted', 'Halvány szöveg – rgba megengedett (--va-muted)' ); ?>
                                <?php self::field_text(  'va_ap_color_border', 'Keret alap – rgba megengedett (--va-border)' ); ?>
                                <?php self::field_text(  'va_ap_color_border2', 'Keret erős – rgba megengedett (--va-border2)' ); ?>
                            </table>
                        </div>

                        <!-- Accent szín -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">⚡</div>
                                <div>
                                    <strong>Accent (kiemelő) szín</strong>
                                    <p>Az admin panel fő hangsúlyszíne – aktív elemek, gombok, indikátorok</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_color( 'va_ap_color_accent',  'Accent szín (--va-accent)' ); ?>
                                <?php self::field_color( 'va_ap_color_accent2', 'Accent hover / világosabb (--va-accent2)' ); ?>
                            </table>
                        </div>

                        <!-- Layout méretek -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">📐</div>
                                <div>
                                    <strong>Layout méretek</strong>
                                    <p>Sidebar szélesség, topbar magasság, lekerekítések</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_num( 'va_ap_sidebar_width', 'Sidebar szélesség (px)', 180, 340 ); ?>
                                <?php self::field_num( 'va_ap_topbar_height', 'Topbar magasság (px)', 44, 80 ); ?>
                                <?php self::field_num( 'va_ap_radius',     'Kártya / panel lekerekítés (px)', 0, 24 ); ?>
                                <?php self::field_num( 'va_ap_radius_sm',  'Kisebb elem lekerekítés (px)', 0, 16 ); ?>
                            </table>
                        </div>

                        <!-- Betűtípus -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">🔤</div>
                                <div>
                                    <strong>Admin betűtípus</strong>
                                    <p>Az admin panel globális betűcsaládja</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_select( 'va_ap_font', 'Admin betűtípus', $fonts ); ?>
                                <?php self::field_num( 'va_ap_font_size', 'Admin betűméret (px)', 10, 20 ); ?>

                        <div style="margin-top:8px;">
                            <?php submit_button( '💾 Admin Panel beállítások mentése', 'primary', 'submit' ); ?>
                        </div>
                    </form>
                </div><!-- .va-aps-form-col -->

            </div><!-- .va-aps-main -->
        </div><!-- .va-aps-wrap -->

        <style>
        /* ── Admin Panel Settings Page ── */
        .va-aps-wrap { max-width:1400px; }

        /* Presets */
        .va-aps-presets-box { background:var(--va-bg2); border:1px solid var(--va-border); border-radius:var(--va-radius); padding:20px 24px; margin-bottom:24px; }
        .va-aps-presets-hdr h2 { margin:0 0 2px; font-size:15px; color:var(--va-text); }
        .va-aps-presets-hdr p  { margin:0 0 16px; font-size:12px; color:var(--va-muted); }
        .va-aps-presets-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(165px,1fr)); gap:8px; }
        .va-aps-pf { display:contents; }
        .va-aps-preset {
            display:flex; flex-direction:column; gap:5px; align-items:flex-start;
            background:var(--va-bg3); border:1px solid var(--va-border2);
            border-radius:10px; padding:10px 14px; cursor:pointer; color:var(--va-text);
            text-align:left; width:100%; transition:.15s;
        }
        .va-aps-preset:hover { border-color:var(--va-accent); transform:translateY(-2px); box-shadow:0 6px 22px rgba(0,0,0,.45); }
        .va-aps-preset strong { font-size:12px; font-weight:700; }
        .va-aps-preset small  { font-size:11px; color:var(--va-muted); line-height:1.4; }
        .va-aps-swatches { display:flex; gap:4px; margin-bottom:2px; }
        .va-aps-swatches span { width:16px; height:16px; border-radius:50%; border:1px solid rgba(255,255,255,.15); flex-shrink:0; }

        /* Main layout */
        .va-aps-main { display:grid; grid-template-columns:1fr; gap:24px; align-items:start; }

        /* Sections */
        .va-aps-section { background:var(--va-bg2); border:1px solid var(--va-border); border-radius:var(--va-radius); overflow:hidden; margin-bottom:16px; }
        .va-aps-section-hdr { display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid var(--va-border); }
        .va-aps-section-icon { font-size:20px; flex-shrink:0; }
        .va-aps-section-hdr strong { font-size:13px; font-weight:700; color:var(--va-text); display:block; margin-bottom:2px; }
        .va-aps-section-hdr p { margin:0; font-size:11px; color:var(--va-muted); }
        .va-aps-section .form-table { margin:0; padding:8px 20px 14px; background:transparent; }
        .va-aps-section .form-table th { font-size:12px; color:var(--va-muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; padding:10px 18px 10px 0; width:220px; }
        .va-aps-section .form-table td { padding:10px 0; }
        .va-aps-section .form-table input[type=text],
        .va-aps-section .form-table input[type=number],
        .va-aps-section .form-table select { background:var(--va-bg3) !important; border:1px solid var(--va-border2) !important; color:var(--va-text) !important; border-radius:var(--va-radius-sm) !important; padding:7px 10px !important; font-size:13px !important; }
        .va-aps-section .form-table input[type=color] { width:44px; height:34px; border-radius:var(--va-radius-sm); border:1px solid var(--va-border2); background:transparent; cursor:pointer; padding:2px; }

        /* Preview column */
        .va-aps-preview-col { position:sticky; top:72px; align-self:start; }
        .va-aps-preview-sticky { display:flex; flex-direction:column; gap:0; }
        .va-aps-preview-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--va-muted); margin-bottom:6px; }
        .va-aps-preview-note { font-size:10px; color:var(--va-muted); margin-top:8px; text-align:center; }

        /* Topbar preview */
        .va-aps-prev-topbar {
            background:var(--pv-bg2); border:1px solid var(--pv-border,rgba(255,255,255,.07));
            border-radius:8px 8px 0 0; padding:8px 12px;
            display:flex; justify-content:space-between; align-items:center;
            font-size:11px; color:var(--pv-text,#e8e8f0); gap:8px;
            font-family:system-ui,sans-serif;
        }
        .va-aps-topbar-title { font-weight:600; }
        .va-aps-topbar-title small { font-size:9px; color:var(--pv-muted,rgba(255,255,255,.45)); margin-left:4px; }
        .va-aps-topbar-btn { background:var(--pv-accent,#ff2020); color:#fff; border-radius:5px; padding:3px 9px; font-size:10px; font-weight:700; }

        /* Sidebar preview */
        .va-aps-prev-sidebar {
            background:var(--pv-bg2); border-left:1px solid var(--pv-border,rgba(255,255,255,.07));
            border-right:1px solid var(--pv-border,rgba(255,255,255,.07));
            font-family:system-ui,sans-serif; font-size:11px; color:var(--pv-text,#e8e8f0);
            display:flex; flex-direction:column;
        }
        .va-aps-prev-logo { display:flex; align-items:center; gap:9px; padding:12px 12px 10px; border-bottom:1px solid var(--pv-border,rgba(255,255,255,.07)); }
        .va-aps-prev-icon { width:26px; height:26px; background:var(--pv-accent,#ff2020); border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .va-aps-prev-name { font-size:11px; font-weight:700; color:var(--pv-text,#e8e8f0); }
        .va-aps-prev-sub  { font-size:9px; color:var(--pv-muted,rgba(255,255,255,.42)); }
        .va-aps-prev-nav  { padding:6px 7px; display:flex; flex-direction:column; gap:1px; }
        .va-aps-prev-item { display:flex; align-items:center; gap:7px; padding:5px 7px; border-radius:6px; color:var(--pv-text,#e8e8f0); font-size:10px; text-decoration:none; cursor:default; }
        .va-aps-prev-item--active { background:var(--pv-accent,#ff2020); color:#fff !important; }
        .va-aps-prev-item--current { background:var(--pv-bg4,#161620); }
        .va-aps-prev-sep  { font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.7px; color:var(--pv-muted,rgba(255,255,255,.32)); padding:6px 7px 2px; }
        .va-aps-prev-badge { margin-left:auto; background:var(--pv-accent,#ff2020); color:#fff; border-radius:999px; padding:0 5px; font-size:9px; font-weight:700; }

        /* Content preview */
        .va-aps-prev-content {
            background:var(--pv-bg); padding:10px;
            border:1px solid var(--pv-border,rgba(255,255,255,.07)); border-top:none;
            border-radius:0 0 8px 8px; font-family:system-ui,sans-serif;
        }
        .va-aps-prev-kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin-bottom:8px; }
        .va-aps-prev-kpi { background:var(--pv-bg3); border:1px solid var(--pv-border,rgba(255,255,255,.07)); border-radius:var(--pv-radius,8px); padding:6px 8px; display:flex; flex-direction:column; align-items:center; gap:1px; font-size:9px; color:var(--pv-muted,rgba(255,255,255,.45)); }
        .va-aps-prev-kpi span { font-size:13px; }
        @media (max-width:680px) {
            .va-aps-presets-grid { gap:6px; }
            .va-aps-preset { min-width:130px; }
        }
        </style>
        <?php
    }

    /* ══ Hirdetés beállítások ═════════════════════════════ */
    public static function render_listings() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $submit_page = get_page_by_path( 'va-hirdetes-feladas' );
        $submit_url = $submit_page ? get_permalink( $submit_page ) : home_url( '/va-hirdetes-feladas/' );
        $callback_example = add_query_arg(
            [
                'va_payment' => 'success',
                'token'      => '{TOKEN}',
            ],
            $submit_url
        );

        $cancel_example = add_query_arg(
            [
                'va_payment' => 'cancel',
                'token'      => '{TOKEN}',
            ],
            $submit_url
        );
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📋 weingartnerauto.hu - Hirdetes beallitasok</h1>
            <?php settings_errors( 'va_listing_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_listing_settings' ); ?>
                <h2>Alap díjazás</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_featured_price', 'Kiemelt hirdetés ára (Ft)', 0, 99999 ); ?>
                    <?php self::field_num( 'va_featured_days',  'Kiemelt hirdetés időtartama (nap)', 1, 365 ); ?>
                    <?php self::field_num( 'va_free_listings_limit', 'Ingyenes hirdetések száma felhasználónként (0=korlátlan)', 0, 999 ); ?>
                    <?php self::field_num( 'va_listing_price_after_free', 'További hirdetés ára (Ft)', 0, 999999 ); ?>
                    <?php self::field_url( 'va_listing_payment_url', 'Bankkártyás fizetési URL (Stripe/Barion/OTP Simple checkout link)' ); ?>
                </table>

                <h2>Főoldali termék rács (reszponzív)</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_layout_home_sidebar_side', 'Főoldal sidebar pozíció', [
                        'left'  => 'Bal oldali sidebar (termékek jobbra)',
                        'right' => 'Jobb oldali sidebar (termékek balra)',
                        'none'  => 'Sidebar kikapcsolva (teljes szélesség)',
                    ] ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_desktop', 'Desktop: oszlop/sor', 2, 10 ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_tablet', 'Tablet: oszlop/sor', 1, 4 ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_mobile', 'Mobil: oszlop/sor', 1, 2 ); ?>
                    <?php self::field_select( 'va_layout_grid_justify', 'Desktop igazítás', [ 'start' => 'Balra', 'center' => 'Középre', 'end' => 'Jobbra' ] ); ?>
                    <?php self::field_select( 'va_layout_grid_justify_tablet', 'Tablet igazítás', [ 'start' => 'Balra', 'center' => 'Középre', 'end' => 'Jobbra' ] ); ?>
                    <?php self::field_select( 'va_layout_grid_justify_mobile', 'Mobil igazítás', [ 'start' => 'Balra', 'center' => 'Középre', 'end' => 'Jobbra' ] ); ?>
                </table>

                <h2>Fizetési beállítások (szolgáltatóhoz)</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_payment_provider', 'Fizetési szolgáltató', [
                        'none'      => 'Nincs még szolgáltató',
                        'barion'    => 'Barion',
                        'stripe'    => 'Stripe',
                        'simplepay' => 'OTP SimplePay',
                        'custom'    => 'Egyedi szolgáltató',
                    ] ); ?>
                    <?php self::field_select( 'va_payment_mode', 'Fizetési mód', [ 'test' => 'Teszt', 'live' => 'Éles' ] ); ?>
                    <?php self::field_text( 'va_payment_public_key', 'Publikus kulcs / Merchant ID' ); ?>
                    <?php self::field_text( 'va_payment_secret_key', 'Titkos kulcs (API Secret)' ); ?>
                    <?php self::field_text( 'va_payment_webhook_secret', 'Webhook aláírás kulcs' ); ?>
                    <?php self::field_url( 'va_payment_success_url', 'Sikeres fizetés URL (opcionális, üresen automatikus)' ); ?>
                    <?php self::field_url( 'va_payment_cancel_url', 'Megszakított fizetés URL (opcionális, üresen automatikus)' ); ?>
                    <tr>
                        <th>Automatikus callback minta</th>
                        <td><input type="text" readonly class="regular-text code" value="<?php echo esc_attr( $callback_example ); ?>"></td>
                    </tr>
                    <tr>
                        <th>Automatikus cancel minta</th>
                        <td><input type="text" readonly class="regular-text code" value="<?php echo esc_attr( $cancel_example ); ?>"></td>
                    </tr>
                </table>

                <h2>Számlázási beállítások</h2>
                <table class="form-table">
                    <?php self::field_text( 'va_billing_company_name', 'Számlakiállító neve' ); ?>
                    <?php self::field_text( 'va_billing_company_address', 'Számlakiállító címe' ); ?>
                    <?php self::field_text( 'va_billing_tax_number', 'Adószám' ); ?>
                    <?php self::field_email( 'va_billing_email', 'Számlázási e-mail' ); ?>
                    <?php self::field_text( 'va_billing_phone', 'Számlázási telefonszám' ); ?>
                    <?php self::field_text( 'va_invoice_prefix', 'Számlaszám előtag (pl. VA)' ); ?>
                    <?php self::field_num( 'va_invoice_next_number', 'Következő számlasorszám', 1, 99999999 ); ?>
                    <?php self::field_text( 'va_invoice_footer_note', 'Számla lábléc megjegyzés' ); ?>
                </table>
                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Aukció beállítások ═══════════════════════════════ */
    public static function render_auctions() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $vars_outbid  = '{name}, {title}, {amount}';
        $vars_winner  = '{name}, {title}, {amount}';
        $vars_seller  = '{seller_name}, {title}, {amount}, {winner_name}, {winner_email}';
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🔨 weingartnerauto.hu - Aukcio beallitasok</h1>
            <?php settings_errors( 'va_auction_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_auction_settings' ); ?>

                <h2>Általános</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_default_min_bid_step', 'Alapértelmezett minimum licitlépés (Ft)', 1, 999999 ); ?>
                    <?php self::field_num( 'va_auction_fee_pct',       'Aukciós jutalék (%)', 0, 100 ); ?>
                </table>

                <h2 style="margin-top:32px;">📧 Email sablonok</h2>
                <p style="color:#aaa;margin-bottom:24px;">
                    A mezőkben HTML is használható. Az alábbi változókat a rendszer automatikusan behelyettesíti.
                </p>

                <h3>Túllicitálás értesítő (korábbi licitálónak)</h3>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code><?php echo esc_html( $vars_outbid ); ?></code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_outbid_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_outbid_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_outbid_body',    'Email törzs (HTML)', '', 6 ); ?>
                    <?php self::field_text(    'va_email_outbid_btn',     'CTA gomb felirata' ); ?>
                </table>

                <h3 style="margin-top:24px;">Nyertes értesítő (nyertesnek)</h3>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code><?php echo esc_html( $vars_winner ); ?></code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_winner_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_winner_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_winner_body',    'Email törzs (HTML)', '', 7 ); ?>
                    <?php self::field_text(    'va_email_winner_btn',     'CTA gomb felirata' ); ?>
                </table>

                <h3 style="margin-top:24px;">Aukció lezárult értesítő (eladónak)</h3>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code><?php echo esc_html( $vars_seller ); ?></code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_seller_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_seller_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_seller_body',    'Email törzs (HTML)', '', 8 ); ?>
                    <?php self::field_text(    'va_email_seller_btn',     'CTA gomb felirata' ); ?>
                </table>

                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Rendszer email sablonok ════════════════════════════════ */
    public static function render_emails(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📧 weingartnerauto.hu - Email sablonok</h1>
            <?php settings_errors( 'va_email_settings' ); ?>
            <p style="color:#aaa;margin-bottom:4px;">
                HTML használható a törzsben. A <code>{változók}</code> automatikusan behelyettesítésre kerülnek.
                Ha a CTA gomb feliratát üresen hagyod, nem jelenik meg gomb.
            </p>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_email_settings' ); ?>

                <!-- Regisztráció -->
                <h2 style="margin-top:28px; display:flex; align-items:center; gap:10px;">
                    Regisztrációs levél
                    <label style="font-size:13px;font-weight:400;display:flex;align-items:center;gap:5px;">
                        <input type="hidden" name="va_email_reg_enabled" value="0">
                        <input type="checkbox" name="va_email_reg_enabled" value="1" <?php checked( get_option('va_email_reg_enabled','1'), '1' ); ?>>
                        Bekapcsolva
                    </label>
                </h2>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code>{name}, {username}, {site_name}</code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_reg_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_reg_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_reg_body',    'Email törzs (HTML)', '', 6 ); ?>
                    <?php self::field_text(    'va_email_reg_btn',     'CTA gomb felirata (üres = nincs gomb)' ); ?>
                </table>

                <!-- Hirdetés megjelent -->
                <h2 style="margin-top:32px; display:flex; align-items:center; gap:10px;">
                    Hirdetés megjelent értesítő
                    <label style="font-size:13px;font-weight:400;display:flex;align-items:center;gap:5px;">
                        <input type="hidden" name="va_email_listing_enabled" value="0">
                        <input type="checkbox" name="va_email_listing_enabled" value="1" <?php checked( get_option('va_email_listing_enabled','1'), '1' ); ?>>
                        Bekapcsolva
                    </label>
                </h2>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code>{name}, {title}, {site_name}</code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_listing_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_listing_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_listing_body',    'Email törzs (HTML)', '', 6 ); ?>
                    <?php self::field_text(    'va_email_listing_btn',     'CTA gomb felirata (üres = nincs gomb)' ); ?>
                </table>

                <!-- Hirdetés törölve -->
                <h2 style="margin-top:32px; display:flex; align-items:center; gap:10px;">
                    Hirdetés törlése értesítő
                    <label style="font-size:13px;font-weight:400;display:flex;align-items:center;gap:5px;">
                        <input type="hidden" name="va_email_del_listing_enabled" value="0">
                        <input type="checkbox" name="va_email_del_listing_enabled" value="1" <?php checked( get_option('va_email_del_listing_enabled','1'), '1' ); ?>>
                        Bekapcsolva
                    </label>
                </h2>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code>{name}, {title}, {site_name}</code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_del_listing_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_del_listing_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_del_listing_body',    'Email törzs (HTML)', '', 6 ); ?>
                    <?php self::field_text(    'va_email_del_listing_btn',     'CTA gomb felirata (üres = nincs gomb)' ); ?>
                </table>

                <!-- Fiók törölve -->
                <h2 style="margin-top:32px; display:flex; align-items:center; gap:10px;">
                    Fiók törlése értesítő
                    <label style="font-size:13px;font-weight:400;display:flex;align-items:center;gap:5px;">
                        <input type="hidden" name="va_email_del_account_enabled" value="0">
                        <input type="checkbox" name="va_email_del_account_enabled" value="1" <?php checked( get_option('va_email_del_account_enabled','1'), '1' ); ?>>
                        Bekapcsolva
                    </label>
                </h2>
                <p class="description" style="margin-bottom:8px;">Elérhető változók: <code>{name}, {username}, {site_name}</code></p>
                <table class="form-table">
                    <?php self::field_text(    'va_email_del_account_subject', 'Tárgy' ); ?>
                    <?php self::field_text(    'va_email_del_account_heading', 'Email fejléc szöveg' ); ?>
                    <?php self::field_textarea('va_email_del_account_body',    'Email törzs (HTML)', '', 6 ); ?>
                    <?php self::field_text(    'va_email_del_account_btn',     'CTA gomb felirata (üres = nincs gomb)' ); ?>
                </table>

                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Felhasználók + Plan kezelő oldal ═══════════════════════ */
    public static function render_users() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;
        $plans = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::PLANS : [];

        // Keresés + szűrés
        $search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
        $filter_plan = sanitize_key( $_GET['filter_plan'] ?? '' );
        $paged    = max( 1, absint( $_GET['paged'] ?? 1 ) );
        $per_page = 40;
        $offset   = ( $paged - 1 ) * $per_page;

        $user_args = [
            'number'       => $per_page,
            'offset'       => $offset,
            'orderby'      => 'registered',
            'order'        => 'DESC',
        ];
        if ( $search !== '' ) {
            $user_args['search']         = '*' . $search . '*';
            $user_args['search_columns'] = [ 'user_login', 'user_email', 'display_name' ];
        }
        if ( $filter_plan !== '' && isset( $plans[ $filter_plan ] ) ) {
            $user_args['meta_query'] = [[
                'key'     => 'va_plan',
                'value'   => $filter_plan,
                'compare' => '=',
            ]];
        }

        $users       = get_users( $user_args );
        $total_users = (int) ( new WP_User_Query( array_merge( $user_args, [ 'number' => -1, 'offset' => 0, 'count_total' => true ] ) ) )->get_total();
        $total_pages = max( 1, (int) ceil( $total_users / $per_page ) );

        $admin_nonce = wp_create_nonce( 'va_admin_user_plan' );
        ?>
        <div class="wrap va-admin-wrap">
            <h1>👥 weingartnerauto.hu - Felhasznalok &amp; Csomagok</h1>

            <!-- ── Plan összefoglaló kártyák ── -->
            <?php if ( $plans ): ?>
            <div class="va-upm-plan-cards">
                <?php foreach ( $plans as $pk => $pcfg ): ?>
                    <?php
                    $cnt_q = new WP_User_Query([
                        'role__not_in' => [ 'administrator' ],
                        'meta_query'   => [[ 'key' => 'va_plan', 'value' => $pk ]],
                        'count_total'  => true,
                        'number'       => 0,
                    ]);
                    $counts = count_users();
                    $cnt = ( $pk === 'basic' )
                        ? (int) $counts['total_users'] - (int) ( $counts['avail_roles']['administrator'] ?? 0 )
                        : (int) $cnt_q->get_total();
                    ?>
                    <div class="va-upm-plan-card" style="--pc:<?php echo esc_attr( $pcfg['color'] ); ?>">
                        <span class="va-upm-pc-icon"><?php echo esc_html( $pcfg['icon'] ); ?></span>
                        <span class="va-upm-pc-label"><?php echo esc_html( $pcfg['label'] ); ?></span>
                        <span class="va-upm-pc-count"><?php echo esc_html( $cnt ); ?></span>
                        <span class="va-upm-pc-desc"><?php echo esc_html( $pcfg['description'] ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- ── Szűrősáv ── -->
            <div class="va-upm-toolbar">
                <form method="get" action="" class="va-upm-search-form">
                    <input type="hidden" name="page" value="vadaszapro-users">
                    <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Keresés névben, e-mailben…" class="va-upm-search">
                    <select name="filter_plan" class="va-upm-filter-sel">
                        <option value="">– Minden csomag –</option>
                        <?php foreach ( $plans as $pk => $pcfg ): ?>
                            <option value="<?php echo esc_attr( $pk ); ?>" <?php selected( $filter_plan, $pk ); ?>>
                                <?php echo esc_html( $pcfg['icon'] . ' ' . $pcfg['label'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button">Szűrés</button>
                    <?php if ( $search || $filter_plan ): ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vadaszapro-users' ) ); ?>" class="button">✕ Törlés</a>
                    <?php endif; ?>
                </form>
                <span class="va-upm-count"><?php echo esc_html( $total_users ); ?> felhasználó</span>
            </div>

            <!-- ── Felhasználók táblázat ── -->
            <table class="va-upm-table">
                <thead>
                    <tr>
                        <th>Felhasználó</th>
                        <th>E-mail</th>
                        <th>Csomag</th>
                        <th>Kiemelési cooldown</th>
                        <th>Hirdetések</th>
                        <th>Regisztráció</th>
                        <th>Műveletek</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $users as $user ):
                    $phone       = get_user_meta( $user->ID, 'va_phone', true );
                    $listings    = count_user_posts( $user->ID, 'va_listing' );
                    $auctions    = $auctions_enabled ? count_user_posts( $user->ID, 'va_auction' ) : 0;
                    $plan        = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user->ID ) : 'basic';
                    $pcfg        = $plans[ $plan ] ?? $plans['basic'];
                    $plat_limit  = (int) get_user_meta( $user->ID, 'va_plan_listing_limit', true );
                    $plat_cd     = (int) get_user_meta( $user->ID, 'va_plan_boost_cooldown', true );
                    $plan_note   = (string) get_user_meta( $user->ID, 'va_plan_note', true );

                    if ( class_exists( 'VA_User_Roles' ) ) {
                        $eff_cfg = VA_User_Roles::get_plan_config( $plan, $user->ID );
                    } else {
                        $eff_cfg = $pcfg;
                    }
                ?>
                    <tr class="va-upm-row" data-uid="<?php echo esc_attr( (string) $user->ID ); ?>">
                        <td class="va-upm-td-user">
                            <?php echo get_avatar( $user->ID, 28, '', '', [ 'class' => 'va-upm-avatar' ] ); ?>
                            <div>
                                <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                <span class="va-upm-login">@<?php echo esc_html( $user->user_login ); ?></span>
                                <?php if ( $phone ): ?><span class="va-upm-phone">📞 <?php echo esc_html( $phone ); ?></span><?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo esc_html( $user->user_email ); ?></td>
                        <td class="va-upm-td-plan">
                            <!-- Plan badge + inline váltó -->
                            <span class="va-upm-plan-badge va-upm-badge-<?php echo esc_attr( $plan ); ?>"
                                  style="--pc:<?php echo esc_attr( $pcfg['color'] ); ?>;--pb:<?php echo esc_attr( $pcfg['bg'] ); ?>">
                                <?php echo esc_html( $pcfg['icon'] . ' ' . $pcfg['label'] ); ?>
                            </span>

                            <!-- Plan szerkesztő (összezárva, toggle) -->
                            <div class="va-upm-plan-editor" id="va-upm-editor-<?php echo esc_attr( (string) $user->ID ); ?>" style="display:none;">
                                <select class="va-upm-plan-sel" data-uid="<?php echo esc_attr( (string) $user->ID ); ?>">
                                    <?php foreach ( $plans as $pk => $pcfg2 ): ?>
                                        <option value="<?php echo esc_attr( $pk ); ?>" <?php selected( $plan, $pk ); ?>>
                                            <?php echo esc_html( $pcfg2['icon'] . ' ' . $pcfg2['label'] ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Platinum extra mezők -->
                                <div class="va-upm-plat-extra" style="<?php echo $plan === 'platinum' ? '' : 'display:none;'; ?>">
                                    <label>Havi limit:
                                        <input type="number" class="va-upm-plat-limit" min="1" max="9999"
                                               value="<?php echo esc_attr( (string) ( $plat_limit ?: $eff_cfg['monthly_limit'] ) ); ?>" style="width:70px;">
                                    </label>
                                    <label>Cooldown (nap):
                                        <input type="number" class="va-upm-plat-cd" min="1" max="365"
                                               value="<?php echo esc_attr( (string) ( $plat_cd ?: $eff_cfg['boost_cooldown'] ) ); ?>" style="width:60px;">
                                    </label>
                                    <label>Megjegyzés:
                                        <input type="text" class="va-upm-plat-note" maxlength="200"
                                               value="<?php echo esc_attr( $plan_note ); ?>" style="width:200px;">
                                    </label>
                                </div>

                                <button class="button button-primary va-upm-save-btn"
                                        data-uid="<?php echo esc_attr( (string) $user->ID ); ?>"
                                        data-nonce="<?php echo esc_attr( $admin_nonce ); ?>">Mentés</button>
                                <button class="button va-upm-cancel-btn" data-uid="<?php echo esc_attr( (string) $user->ID ); ?>">Mégse</button>
                                <span class="va-upm-save-status"></span>
                            </div>
                        </td>
                        <td class="va-upm-td-cd">
                            <span title="<?php echo esc_attr( (string) $eff_cfg['boost_cooldown'] ); ?> nap cooldown">
                                ⚡ <?php echo esc_html( (string) $eff_cfg['boost_cooldown'] ); ?> nap
                            </span>
                        </td>
                        <td>
                            <?php if ( $auctions_enabled ): ?>
                                <?php echo esc_html( $listings ); ?> hird. / <?php echo esc_html( $auctions ); ?> aukció
                            <?php else: ?>
                                <?php echo esc_html( $listings ); ?> hirdetés
                            <?php endif; ?>
                            <?php
                            // Havi limit használat megjelenítése
                            if ( class_exists( 'VA_User_Roles' ) ) {
                                $plan_check = VA_User_Roles::can_post_listing( $user->ID );
                                $used  = $plan_check['used'];
                                $limit = $plan_check['limit'];
                                if ( $limit > 0 ) {
                                    $pct = min( 100, (int) round( $used / $limit * 100 ) );
                                    $col = $pct >= 100 ? '#ff4444' : ( $pct >= 80 ? '#ffaa00' : '#00c850' );
                                    echo '<div style="margin-top:4px;font-size:11px;color:rgba(255,255,255,.5);">Havi: ' . esc_html( $used ) . '/' . esc_html( $limit ) . '</div>';
                                    echo '<div style="height:3px;background:rgba(255,255,255,.1);border-radius:2px;margin-top:2px;"><div style="height:3px;width:' . esc_attr( $pct ) . '%;background:' . esc_attr( $col ) . ';border-radius:2px;"></div></div>';
                                }
                            }
                            ?>
                        </td>
                        <td style="color:rgba(255,255,255,.5);font-size:12px;">
                            <?php echo esc_html( date_i18n( 'Y.m.d', strtotime( $user->user_registered ) ) ); ?>
                        </td>
                        <td class="va-upm-td-actions">
                            <button class="button button-small va-upm-edit-btn" data-uid="<?php echo esc_attr( (string) $user->ID ); ?>">✏️ Csomag</button>
                            <a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>" class="button button-small">WP profil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Lapozó -->
            <?php if ( $total_pages > 1 ): ?>
            <div class="va-upm-pagination">
                <?php for ( $p = 1; $p <= $total_pages; $p++ ): ?>
                    <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'vadaszapro-users', 'paged' => $p, 's' => $search, 'filter_plan' => $filter_plan ], admin_url( 'admin.php' ) ) ); ?>"
                       class="va-upm-page<?php echo $p === $paged ? ' active' : ''; ?>"><?php echo esc_html( (string) $p ); ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <style>
        /* ── User Plan Manager styles ── */
        .va-upm-plan-cards { display:flex;flex-wrap:wrap;gap:12px;margin:16px 0 20px; }
        .va-upm-plan-card {
            background:var(--va-bg2);border:1px solid var(--pc,#888);border-radius:var(--va-radius);
            padding:14px 18px;min-width:180px;display:flex;flex-direction:column;gap:3px;
        }
        .va-upm-pc-icon  { font-size:22px; }
        .va-upm-pc-label { font-weight:700;font-size:14px;color:var(--pc,#fff); }
        .va-upm-pc-count { font-size:28px;font-weight:800;color:var(--va-text);line-height:1; }
        .va-upm-pc-desc  { font-size:11px;color:var(--va-muted); }

        .va-upm-toolbar { display:flex;align-items:center;gap:10px;margin-bottom:14px; }
        .va-upm-search-form { display:flex;align-items:center;gap:8px;flex:1; }
        .va-upm-search { background:var(--va-bg3) !important;border:1px solid var(--va-border2) !important;color:var(--va-text) !important;border-radius:var(--va-radius-sm);padding:6px 12px;min-width:260px; }
        .va-upm-filter-sel { background:var(--va-bg3);border:1px solid var(--va-border2);color:var(--va-text);border-radius:var(--va-radius-sm);padding:6px 10px; }
        .va-upm-count { margin-left:auto;font-size:12px;color:var(--va-muted); }

        .va-upm-table { width:100%;border-collapse:collapse;background:var(--va-bg2);border-radius:var(--va-radius);overflow:hidden;border:1px solid var(--va-border); }
        .va-upm-table th { background:var(--va-bg3);padding:10px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--va-muted);border-bottom:1px solid var(--va-border); }
        .va-upm-table td { padding:12px 14px;border-bottom:1px solid var(--va-border);vertical-align:middle; }
        .va-upm-row:last-child td { border-bottom:none; }
        .va-upm-row:hover { background:var(--va-bg3); }

        .va-upm-td-user { display:flex;align-items:center;gap:10px; }
        .va-upm-avatar { border-radius:50%;flex-shrink:0; }
        .va-upm-login { font-size:11px;color:var(--va-muted);display:block; }
        .va-upm-phone { font-size:11px;color:var(--va-muted);display:block; }

        .va-upm-plan-badge {
            display:inline-flex;align-items:center;gap:5px;
            background:var(--pb,rgba(136,136,136,.15));color:var(--pc,#888);
            border:1px solid var(--pc,#888);border-radius:999px;
            padding:3px 10px;font-size:12px;font-weight:700;
        }
        .va-upm-plan-editor { margin-top:8px;padding:10px;background:var(--va-bg3);border-radius:var(--va-radius-sm);border:1px solid var(--va-border2); }
        .va-upm-plan-editor select,
        .va-upm-plan-editor input[type=number],
        .va-upm-plan-editor input[type=text] {
            background:var(--va-bg2);border:1px solid var(--va-border2);color:var(--va-text);
            border-radius:var(--va-radius-sm);padding:5px 8px;font-size:12px;
        }
        .va-upm-plat-extra { display:flex;flex-wrap:wrap;gap:8px;margin:8px 0;padding:8px;background:rgba(226,198,255,.06);border-radius:var(--va-radius-sm); }
        .va-upm-plat-extra label { display:flex;align-items:center;gap:5px;font-size:12px; }
        .va-upm-save-status { font-size:12px;margin-left:8px; }

        .va-upm-td-actions { white-space:nowrap; }
        .va-upm-td-actions a, .va-upm-td-actions button { margin-right:4px; }

        .va-upm-pagination { display:flex;gap:6px;margin-top:16px;flex-wrap:wrap; }
        .va-upm-page { background:var(--va-bg2);border:1px solid var(--va-border);border-radius:var(--va-radius-sm);padding:4px 10px;font-size:13px;color:var(--va-text);text-decoration:none; }
        .va-upm-page.active { background:var(--va-accent);border-color:var(--va-accent);color:#fff; }
        .va-upm-page:hover:not(.active) { border-color:var(--va-accent);color:var(--va-accent); }
        </style>

        <script>
        (function(){
            // Toggle szerkesztő
            document.querySelectorAll('.va-upm-edit-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var uid = this.dataset.uid;
                    var ed  = document.getElementById('va-upm-editor-' + uid);
                    if(ed) ed.style.display = ed.style.display === 'none' ? 'block' : 'none';
                });
            });

            document.querySelectorAll('.va-upm-cancel-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var uid = this.dataset.uid;
                    var ed  = document.getElementById('va-upm-editor-' + uid);
                    if(ed) ed.style.display = 'none';
                });
            });

            // Plan dropdown változáskor platinum extra mezők mutatása
            document.querySelectorAll('.va-upm-plan-sel').forEach(function(sel){
                sel.addEventListener('change', function(){
                    var extra = this.closest('.va-upm-plan-editor').querySelector('.va-upm-plat-extra');
                    if(extra) extra.style.display = this.value === 'platinum' ? 'flex' : 'none';
                });
            });

            // Mentés gomb
            document.querySelectorAll('.va-upm-save-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var uid    = this.dataset.uid;
                    var nonce  = this.dataset.nonce;
                    var ed     = document.getElementById('va-upm-editor-' + uid);
                    var status = ed ? ed.querySelector('.va-upm-save-status') : null;
                    var sel    = ed ? ed.querySelector('.va-upm-plan-sel') : null;
                    var plan   = sel ? sel.value : 'basic';

                    var limEl = ed ? ed.querySelector('.va-upm-plat-limit') : null;
                    var cdEl  = ed ? ed.querySelector('.va-upm-plat-cd')    : null;
                    var noteEl= ed ? ed.querySelector('.va-upm-plat-note')  : null;

                    var data = new URLSearchParams({
                        action : 'va_admin_set_user_plan',
                        nonce  : nonce,
                        user_id: uid,
                        plan   : plan,
                        custom_limit         : limEl  ? limEl.value  : 0,
                        custom_boost_cooldown: cdEl   ? cdEl.value   : 0,
                        plan_note            : noteEl ? noteEl.value : ''
                    });

                    if(status) status.textContent = 'Mentés…';
                    btn.disabled = true;

                    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                        method  : 'POST',
                        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body    : data.toString()
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(res){
                        btn.disabled = false;
                        if(res.success){
                            var msg = '✅ Mentve!';
                            if(res.data && res.data.suspended > 0){
                                msg += ' – ' + res.data.suspended + ' hirdetés felfüggesztve (limit felett).';
                            }
                            if(status) { status.textContent = msg; status.style.color = '#00c850'; }
                            // Oldal újratöltés 1.5mp után – azonnal látszik minden változás
                            setTimeout(function(){ window.location.reload(); }, 1500);
                        } else {
                            if(status) { status.textContent = '❌ ' + (res.data ? res.data.message : 'Hiba'); status.style.color = '#ff4444'; }
                        }
                    })
                    .catch(function(){
                        btn.disabled = false;
                        if(status) { status.textContent = '❌ Hálózati hiba'; status.style.color = '#ff4444'; }
                    });
                });
            });
        })();
        </script>
        <?php
    }

    /* ══ Statisztika oldal ════════════════════════════════ */
    public static function render_stats() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        global $wpdb;
        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;

        /* ── Hirdetés KPI ─────────────────────────────────────── */
        $lc        = wp_count_posts( 'va_listing' );
        $published = (int)( $lc->publish  ?? 0 );
        $pending   = (int)( $lc->pending  ?? 0 );
        $draft     = (int)( $lc->draft    ?? 0 );

        $ac              = $auctions_enabled ? wp_count_posts( 'va_auction' ) : null;
        $auctions_active = (int)( $ac->publish ?? 0 );

        $uc          = count_users();
        $total_users = (int)( $uc['total_users'] ?? 0 );

        $total_bids      = (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}va_bids" );
        $total_watchlist = (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}va_watchlist" );

        $total_views = (int)$wpdb->get_var( $wpdb->prepare(
            "SELECT SUM( CAST( meta_value AS UNSIGNED ) ) FROM {$wpdb->postmeta} WHERE meta_key = %s",
            'va_views'
        ) );

        $today = current_time( 'Y-m-d' );
        $today_listings = (int)$wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending') AND DATE(post_date) = %s",
            'va_listing', $today
        ) );

        $this_month_listings = (int)$wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending')
             AND YEAR(post_date) = %d AND MONTH(post_date) = %d",
            'va_listing', (int)date('Y'), (int)date('m')
        ) );

        $this_week_users = count( get_users([
            'number'     => 999,
            'date_query' => [[ 'after' => '7 days ago', 'column' => 'user_registered' ]],
        ]) );

        /* ── Ár statisztikák (wp_va_listing_meta ha létezik) ─── */
        $lmeta_table = $wpdb->prefix . 'va_listing_meta';
        $listing_meta_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $lmeta_table ) ) === $lmeta_table;
        $avg_price = $min_price = $max_price = 0;
        $price_ranges = [];
        if ( $listing_meta_exists ) {
            $ps = $wpdb->get_row( "SELECT AVG(price) as a, MIN(price) as mi, MAX(price) as ma FROM {$lmeta_table} WHERE price > 0" );
            $avg_price = (int)round( (float)( $ps->a  ?? 0 ) );
            $min_price = (int)( $ps->mi ?? 0 );
            $max_price = (int)( $ps->ma ?? 0 );
            $price_ranges = [
                '0–10 e Ft'     => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 0 AND price <= 10000" ),
                '10–50 e Ft'    => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 10000 AND price <= 50000" ),
                '50–200 e Ft'   => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 50000 AND price <= 200000" ),
                '200–500 e Ft'  => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 200000 AND price <= 500000" ),
                '500 e+ Ft'     => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 500000" ),
            ];
        }

        /* ── 30 napos aktivitás ───────────────────────────────── */
        $days30 = [];
        for ( $i = 29; $i >= 0; $i-- ) $days30[ date( 'Y-m-d', strtotime( "-{$i} days" ) ) ] = 0;
        $rows30 = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(post_date) as d, COUNT(*) as cnt FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending')
             AND post_date >= %s GROUP BY DATE(post_date)",
            'va_listing', date( 'Y-m-d', strtotime( '-29 days' ) )
        ), ARRAY_A );
        foreach ( $rows30 as $r ) { if ( isset( $days30[$r['d']] ) ) $days30[$r['d']] = (int)$r['cnt']; }

        /* ── 30 napos regisztráció ────────────────────────────── */
        $reg30 = [];
        for ( $i = 29; $i >= 0; $i-- ) $reg30[ date( 'Y-m-d', strtotime( "-{$i} days" ) ) ] = 0;
        $reg_rows = $wpdb->get_results(
            "SELECT DATE(user_registered) as d, COUNT(*) as cnt FROM {$wpdb->users}
             WHERE user_registered >= '" . esc_sql( date( 'Y-m-d', strtotime( '-29 days' ) ) ) . "'
             GROUP BY DATE(user_registered)",
            ARRAY_A
        );
        foreach ( $reg_rows as $r ) { if ( isset( $reg30[$r['d']] ) ) $reg30[$r['d']] = (int)$r['cnt']; }

        /* ── Top kategóriák ──────────────────────────────────── */
        $top_cats = get_terms(['taxonomy'=>'va_category','orderby'=>'count','order'=>'DESC','number'=>12,'hide_empty'=>false]);
        if ( is_wp_error($top_cats) ) $top_cats = [];

        /* ── Top megyék ──────────────────────────────────────── */
        $top_counties = get_terms(['taxonomy'=>'va_county','orderby'=>'count','order'=>'DESC','number'=>20,'hide_empty'=>false]);
        if ( is_wp_error($top_counties) ) $top_counties = [];

        /* ── Top megtekintett hirdetések ─────────────────────── */
        $top_viewed = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => 'publish',
            'meta_key'       => 'va_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'posts_per_page' => 10,
            'no_found_rows'  => true,
        ]);

        /* ── Top watchlistezett hirdetések ───────────────────── */
        $top_wl_rows = $wpdb->get_results(
            "SELECT post_id, COUNT(*) as cnt FROM {$wpdb->prefix}va_watchlist
             GROUP BY post_id ORDER BY cnt DESC LIMIT 10",
            ARRAY_A
        );
        foreach ( $top_wl_rows as &$wl ) {
            $wl['title'] = get_the_title( $wl['post_id'] );
            $wl['url']   = get_permalink( $wl['post_id'] );
        }
        unset($wl);

        /* ── Top hirdetők ────────────────────────────────────── */
        $top_posters = $wpdb->get_results( $wpdb->prepare(
            "SELECT post_author, COUNT(*) as cnt FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status = 'publish'
             GROUP BY post_author ORDER BY cnt DESC LIMIT 10",
            'va_listing'
        ), ARRAY_A );

        /* ── Legutóbbi regisztrációk ──────────────────────────── */
        $recent_regs = get_users(['number'=>10,'orderby'=>'registered','order'=>'DESC']);

        /* ── Legutóbbi hirdetések ─────────────────────────────── */
        $recent_listings = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => ['publish','pending'],
            'posts_per_page' => 10,
            'no_found_rows'  => true,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        /* ── Aukció extra ────────────────────────────────────── */
        $top_auctions = [];
        $total_bids_value = 0;
        if ( $auctions_enabled ) {
            $total_bids_value = (float)$wpdb->get_var( "SELECT SUM(amount) FROM {$wpdb->prefix}va_bids" );
            $top_auctions = $wpdb->get_results(
                "SELECT post_id, COUNT(*) as bid_cnt, MAX(amount) as max_bid
                 FROM {$wpdb->prefix}va_bids GROUP BY post_id ORDER BY bid_cnt DESC LIMIT 8",
                ARRAY_A
            );
        }

        /* ── JSON adatok Chart.js-hez ────────────────────────── */
        $ch_day_lbl  = wp_json_encode( array_map( fn($d) => date('m.d', strtotime($d)), array_keys($days30) ) );
        $ch_day_data = wp_json_encode( array_values($days30) );
        $ch_reg_data = wp_json_encode( array_values($reg30) );
        $ch_cat_lbl  = wp_json_encode( array_map( fn($t) => $t->name,  $top_cats ) );
        $ch_cat_data = wp_json_encode( array_map( fn($t) => (int)$t->count, $top_cats ) );
        $ch_cty_lbl  = wp_json_encode( array_map( fn($t) => $t->name,  $top_counties ) );
        $ch_cty_data = wp_json_encode( array_map( fn($t) => (int)$t->count, $top_counties ) );
        $ch_pr_lbl   = wp_json_encode( array_keys($price_ranges) );
        $ch_pr_data  = wp_json_encode( array_values($price_ranges) );
        $ch_st_lbl   = wp_json_encode( ['Aktív', 'Függő', 'Vázlat'] );
        $ch_st_data  = wp_json_encode( [$published, $pending, $draft] );
        ?>
        <div class="va-stats-page wrap va-admin-wrap">
            <h1>📈 Statisztika</h1>

            <!-- ══ KPI kártyák ═══════════════════════════════════ -->
            <div class="va-st-kpi-grid">
                <?php
                $kpis = [
                    ['icon'=>'📋','num'=>$published,         'label'=>'Aktív hirdetés',     'sub'=>"+{$today_listings} ma · +{$this_month_listings} idén",                         'color'=>'red'],
                    ['icon'=>'⏳','num'=>$pending,            'label'=>'Jóváhagyásra vár',  'sub'=>$draft . ' vázlat',                                                              'color'=>'orange'],
                    ['icon'=>'👥','num'=>$total_users,        'label'=>'Regisztrált user',   'sub'=>"+{$this_week_users} az elmúlt 7 napban",                                       'color'=>'blue'],
                    ['icon'=>'👁',  'num'=>number_format($total_views,0,',','&nbsp;'), 'label'=>'Összes megtekintés','sub'=>'Összes hirdetésen',                                 'color'=>'purple'],
                    ['icon'=>'❤️', 'num'=>$total_watchlist,  'label'=>'Figyelőlista',       'sub'=>'mentett hirdetés összesen',                                                    'color'=>'pink'],
                ];
                if ($auctions_enabled) {
                    $kpis[] = ['icon'=>'🔨','num'=>$auctions_active,'label'=>'Aktív aukció','sub'=>$total_bids . ' licit · ' . number_format($total_bids_value,0,',','&nbsp;') . ' Ft forgalom','color'=>'green'];
                }
                foreach ($kpis as $k): ?>
                <div class="va-st-kpi va-st-kpi--<?php echo esc_attr($k['color']); ?>">
                    <div class="va-st-kpi__icon"><?php echo $k['icon']; ?></div>
                    <div class="va-st-kpi__num"><?php echo $k['num']; ?></div>
                    <div class="va-st-kpi__label"><?php echo esc_html($k['label']); ?></div>
                    <div class="va-st-kpi__sub"><?php echo wp_kses_post($k['sub']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ══ Ár blokk ══════════════════════════════════════ -->
            <?php if ($avg_price > 0): ?>
            <div class="va-st-price-strip">
                <div class="va-st-price-item">
                    <span class="va-st-price-lbl">Átlagár</span>
                    <span class="va-st-price-val"><?php echo number_format($avg_price,0,',','&nbsp;'); ?> Ft</span>
                </div>
                <div class="va-st-price-sep">|</div>
                <div class="va-st-price-item">
                    <span class="va-st-price-lbl">Legalacsonyabb</span>
                    <span class="va-st-price-val"><?php echo number_format($min_price,0,',','&nbsp;'); ?> Ft</span>
                </div>
                <div class="va-st-price-sep">|</div>
                <div class="va-st-price-item">
                    <span class="va-st-price-lbl">Legmagasabb</span>
                    <span class="va-st-price-val va-st-price-val--hi"><?php echo number_format($max_price,0,',','&nbsp;'); ?> Ft</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- ══ Grafikonok sor 1 ═══════════════════════════════ -->
            <div class="va-st-charts-row">
                <div class="va-st-chart-card va-st-chart-card--wide">
                    <div class="va-st-chart-hdr">
                        <div><h3>Hirdetési aktivitás</h3><p>Utolsó 30 nap – napi új feladások</p></div>
                        <span class="va-st-badge"><?php echo array_sum(array_values($days30)); ?> hirdetés / 30 nap</span>
                    </div>
                    <div class="va-st-chart-body" style="height:200px;">
                        <canvas id="va-st-bar"></canvas>
                    </div>
                </div>
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr">
                        <div><h3>Új regisztrációk</h3><p>Utolsó 30 nap</p></div>
                        <span class="va-st-badge"><?php echo array_sum(array_values($reg30)); ?> / 30 nap</span>
                    </div>
                    <div class="va-st-chart-body" style="height:200px;">
                        <canvas id="va-st-reg"></canvas>
                    </div>
                </div>
            </div>

            <!-- ══ Grafikonok sor 2 ═══════════════════════════════ -->
            <div class="va-st-charts-row">
                <?php if (!empty($top_cats)): ?>
                <div class="va-st-chart-card va-st-chart-card--wide">
                    <div class="va-st-chart-hdr">
                        <div><h3>Top kategóriák</h3><p>Hirdetésszám alapján</p></div>
                    </div>
                    <div class="va-st-chart-body" style="height:220px;">
                        <canvas id="va-st-cat"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($top_counties)): ?>
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr">
                        <div><h3>Top megyék</h3><p>Hirdetésszám alapján</p></div>
                    </div>
                    <div class="va-st-chart-body" style="height:220px;">
                        <canvas id="va-st-county"></canvas>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══ Grafikonok sor 3 – státusz + ár ═══════════════ -->
            <div class="va-st-charts-row va-st-charts-row--sm">
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr"><div><h3>Hirdetés státuszok</h3><p>Jelenlegi eloszlás</p></div></div>
                    <div class="va-st-chart-body va-st-chart-body--donut">
                        <canvas id="va-st-status" width="140" height="140"></canvas>
                        <div class="va-st-donut-legend">
                            <?php foreach (['Aktív'=>['#ff4444',$published],'Függő'=>['#ff8c42',$pending],'Vázlat'=>['#4da6ff',$draft]] as $lbl=>[$col,$val]): ?>
                            <div class="va-st-leg-row">
                                <span class="va-st-leg-dot" style="background:<?php echo $col; ?>"></span>
                                <span class="va-st-leg-name"><?php echo esc_html($lbl); ?></span>
                                <span class="va-st-leg-val"><?php echo $val; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($price_ranges)): ?>
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr"><div><h3>Ár sávok</h3><p>Hirdetések ár szerint csoportosítva</p></div></div>
                    <div class="va-st-chart-body va-st-chart-body--donut">
                        <canvas id="va-st-price" width="140" height="140"></canvas>
                        <div class="va-st-donut-legend" id="va-st-price-legend"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══ Táblázatok ─────────────────────────────────── -->
            <div class="va-st-tables-row">

                <!-- Top megtekintett -->
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>👁 Top 10 megtekintett hirdetés</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>#</th><th>Hirdetés</th><th>Kat.</th><th>Megtekintés</th><th>Feladva</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_viewed as $idx => $p):
                            $views = (int)get_post_meta($p->ID, 'va_views', true);
                            $cats  = get_the_terms($p->ID, 'va_category');
                            $cat   = (!is_wp_error($cats) && $cats) ? $cats[0]->name : '–';
                        ?>
                        <tr>
                            <td class="va-st-rank"><?php echo $idx+1; ?></td>
                            <td><a href="<?php echo esc_url(get_permalink($p->ID)); ?>" target="_blank"><?php echo esc_html(wp_trim_words($p->post_title,6,'…')); ?></a></td>
                            <td><span class="va-st-tag"><?php echo esc_html($cat); ?></span></td>
                            <td><strong class="va-st-accent"><?php echo number_format($views,0,',','&nbsp;'); ?></strong></td>
                            <td class="va-st-muted"><?php echo esc_html(date_i18n('Y.m.d', strtotime($p->post_date))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Top hirdetők -->
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>🏆 Top 10 hirdető</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>#</th><th>Felhasználó</th><th>E-mail</th><th>Hirdetés</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_posters as $idx => $row):
                            $u = get_userdata($row['post_author']);
                            if (!$u) continue;
                        ?>
                        <tr>
                            <td class="va-st-rank"><?php echo $idx+1; ?></td>
                            <td><strong><?php echo esc_html($u->display_name); ?></strong></td>
                            <td class="va-st-muted"><?php echo esc_html($u->user_email); ?></td>
                            <td><strong class="va-st-accent"><?php echo (int)$row['cnt']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="va-st-tables-row">

                <!-- Top watchlist -->
                <?php if (!empty($top_wl_rows)): ?>
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>❤️ Top 10 mentett hirdetés (watchlist)</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>#</th><th>Hirdetés</th><th>Mentések</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_wl_rows as $idx => $wl): ?>
                        <tr>
                            <td class="va-st-rank"><?php echo $idx+1; ?></td>
                            <td><a href="<?php echo esc_url($wl['url']); ?>" target="_blank"><?php echo esc_html(wp_trim_words($wl['title'],8,'…')); ?></a></td>
                            <td><strong class="va-st-accent"><?php echo (int)$wl['cnt']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Legutóbbi regisztrációk -->
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>🆕 Legutóbbi 10 regisztráció</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>Név</th><th>E-mail</th><th>Regisztrált</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent_regs as $u): ?>
                        <tr>
                            <td><strong><?php echo esc_html($u->display_name); ?></strong></td>
                            <td class="va-st-muted"><?php echo esc_html($u->user_email); ?></td>
                            <td class="va-st-muted"><?php echo esc_html(date_i18n('Y.m.d H:i', strtotime($u->user_registered))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Legutóbbi hirdetések -->
            <div class="va-st-panel va-st-panel--full">
                <div class="va-st-panel__hdr"><h3>🕐 Legutóbbi 10 hirdetés</h3></div>
                <table class="va-st-table">
                    <thead><tr><th>Cím</th><th>Hirdető</th><th>Kategória</th><th>Ár</th><th>Státusz</th><th>Dátum</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_listings as $p):
                        $author = get_userdata($p->post_author);
                        $cats   = get_the_terms($p->ID, 'va_category');
                        $cat    = (!is_wp_error($cats) && $cats) ? $cats[0]->name : '–';
                        $price  = (float)get_post_meta($p->ID, 'va_price', true);
                        $status_map = ['publish'=>['Aktív','#4ade80'],'pending'=>['Függő','#fb923c'],'draft'=>['Vázlat','#94a3b8']];
                        [$st_label, $st_color] = $status_map[$p->post_status] ?? ['–','#666'];
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html(wp_trim_words($p->post_title,7,'…')); ?></strong></td>
                        <td class="va-st-muted"><?php echo $author ? esc_html($author->display_name) : '–'; ?></td>
                        <td><span class="va-st-tag"><?php echo esc_html($cat); ?></span></td>
                        <td><?php echo $price > 0 ? '<span class="va-st-price">' . number_format($price,0,',','&nbsp;') . ' Ft</span>' : '<span class="va-st-muted">—</span>'; ?></td>
                        <td><span class="va-st-status-dot" style="color:<?php echo $st_color; ?>"><?php echo esc_html($st_label); ?></span></td>
                        <td class="va-st-muted"><?php echo esc_html(date_i18n('m.d H:i', strtotime($p->post_date))); ?></td>
                        <td><a href="<?php echo esc_url(admin_url("post.php?action=edit&post={$p->ID}")); ?>" class="va-st-edit-btn">Szerk.</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($auctions_enabled && !empty($top_auctions)): ?>
            <!-- Aukció statisztikák -->
            <div class="va-st-panel va-st-panel--full">
                <div class="va-st-panel__hdr"><h3>🔨 Legtöbb licitet kapott aukciók</h3></div>
                <table class="va-st-table">
                    <thead><tr><th>Aukció</th><th>Licitek száma</th><th>Legmagasabb licit</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_auctions as $a):
                        $title = get_the_title($a['post_id']);
                        $url   = get_permalink($a['post_id']);
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html(wp_trim_words($title,8,'…')); ?></a></td>
                        <td><strong class="va-st-accent"><?php echo (int)$a['bid_cnt']; ?></strong></td>
                        <td class="va-st-price"><?php echo number_format((float)$a['max_bid'],0,',','&nbsp;'); ?> Ft</td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div><!-- .va-stats-page -->

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.color = 'rgba(255,255,255,.5)';
            Chart.defaults.font.family = '"Montserrat", system-ui, sans-serif';
            Chart.defaults.font.size = 11;

            const palette = ['#ff2222','#ff6b35','#ffd166','#06d6a0','#118ab2','#9b5de5','#f72585','#4cc9f0','#80b918','#e07a5f','#3d405b','#81b29a'];

            const tooltipDark = {
                backgroundColor: 'rgba(12,12,18,.95)',
                borderColor: 'rgba(255,0,0,.35)',
                borderWidth: 1,
                padding: 10,
                titleColor: '#fff',
                bodyColor: 'rgba(255,255,255,.7)',
            };

            function gradientRed(ctx, chart) {
                const g = ctx.createLinearGradient(0, 0, 0, chart.chartArea?.bottom || 200);
                g.addColorStop(0, 'rgba(255,0,0,.7)');
                g.addColorStop(1, 'rgba(255,0,0,.1)');
                return g;
            }

            /* Bar – hirdetési aktivitás */
            const ctxBar = document.getElementById('va-st-bar');
            if (ctxBar) new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: <?php echo $ch_day_lbl; ?>,
                    datasets: [{
                        label: 'Új hirdetés',
                        data: <?php echo $ch_day_data; ?>,
                        backgroundColor: 'rgba(255,0,0,.55)',
                        borderColor: 'rgba(255,60,60,.9)',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark, callbacks: { label: c => ` ${c.parsed.y} db` } } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { maxTicksLimit: 10 } },
                        y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0, stepSize: 1 }, beginAtZero: true }
                    }
                }
            });

            /* Line – regisztrációk */
            const ctxReg = document.getElementById('va-st-reg');
            if (ctxReg) new Chart(ctxReg, {
                type: 'line',
                data: {
                    labels: <?php echo $ch_day_lbl; ?>,
                    datasets: [{
                        label: 'Regisztráció',
                        data: <?php echo $ch_reg_data; ?>,
                        borderColor: '#4da6ff',
                        backgroundColor: 'rgba(77,166,255,.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#4da6ff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark, callbacks: { label: c => ` ${c.parsed.y} fő` } } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { maxTicksLimit: 10 } },
                        y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0, stepSize: 1 }, beginAtZero: true }
                    }
                }
            });

            /* Horizontal Bar – kategóriák */
            const ctxCat = document.getElementById('va-st-cat');
            if (ctxCat && <?php echo $ch_cat_lbl; ?>.length) new Chart(ctxCat, {
                type: 'bar',
                data: {
                    labels: <?php echo $ch_cat_lbl; ?>,
                    datasets: [{
                        label: 'Hirdetés',
                        data: <?php echo $ch_cat_data; ?>,
                        backgroundColor: palette,
                        borderRadius: 5,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0 }, beginAtZero: true },
                        y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,.6)' } }
                    }
                }
            });

            /* Horizontal Bar – megyék */
            const ctxCty = document.getElementById('va-st-county');
            if (ctxCty && <?php echo $ch_cty_lbl; ?>.length) new Chart(ctxCty, {
                type: 'bar',
                data: {
                    labels: <?php echo $ch_cty_lbl; ?>,
                    datasets: [{
                        label: 'Hirdetés',
                        data: <?php echo $ch_cty_data; ?>,
                        backgroundColor: 'rgba(77,166,255,.65)',
                        borderColor: 'rgba(77,166,255,.9)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0 }, beginAtZero: true },
                        y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,.6)' } }
                    }
                }
            });

            /* Doughnut – státusz */
            const ctxSt = document.getElementById('va-st-status');
            if (ctxSt) new Chart(ctxSt, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $ch_st_lbl; ?>,
                    datasets: [{ data: <?php echo $ch_st_data; ?>, backgroundColor: ['#ff4444','#ff8c42','#4da6ff'], borderColor: '#0d0d11', borderWidth: 3, hoverOffset: 5 }]
                },
                options: { cutout: '68%', plugins: { legend: { display: false }, tooltip: { ...tooltipDark } } }
            });

            /* Doughnut – ár sávok */
            const ctxPr = document.getElementById('va-st-price');
            if (ctxPr) {
                const prLabels = <?php echo $ch_pr_lbl; ?>;
                const prData   = <?php echo $ch_pr_data; ?>;
                const prTotal  = prData.reduce((a,b)=>a+b,0);
                new Chart(ctxPr, {
                    type: 'doughnut',
                    data: {
                        labels: prLabels,
                        datasets: [{ data: prData, backgroundColor: ['#4ade80','#06d6a0','#4da6ff','#9b5de5','#ff4444'], borderColor: '#0d0d11', borderWidth: 3, hoverOffset: 5 }]
                    },
                    options: { cutout: '68%', plugins: { legend: { display: false }, tooltip: { ...tooltipDark, callbacks: { label: c => ` ${c.label}: ${c.parsed} db (${prTotal>0?Math.round(c.parsed/prTotal*100):0}%)` } } } }
                });
                const leg = document.getElementById('va-st-price-legend');
                if (leg) leg.innerHTML = prLabels.map((l,i)=>`<div class="va-st-leg-row"><span class="va-st-leg-dot" style="background:${['#4ade80','#06d6a0','#4da6ff','#9b5de5','#ff4444'][i]}"></span><span class="va-st-leg-name">${l}</span><span class="va-st-leg-val">${prData[i]}</span></div>`).join('');
            }
        });
        </script>
        <?php
    }

    public static function render_search_designer() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🔎 Kereső – legördülő dizájn</h1>
            <p class="description">A header keresőből nyíló találati panel minden fő eleme állítható: háttér, pill badge, színek, szövegméretek, spacing és hover állapotok.</p>
            <?php settings_errors( 'va_search_settings' ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_search_settings' ); ?>

                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🧱</span>
                            <span class="va-settings-card__title">Panel</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_search_dd_bg', 'Háttér szín' ); ?>
                                <?php self::field_color( 'va_search_dd_border', 'Keret szín' ); ?>
                                <?php self::field_num( 'va_search_dd_radius', 'Lekerekítés (px)', 0, 30 ); ?>
                                <?php self::field_text( 'va_search_dd_shadow', 'Árnyék (CSS box-shadow)' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📏</span>
                            <span class="va-settings-card__title">Sorok és bélyegkép</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_search_dd_item_border', 'Elem elválasztó szín' ); ?>
                                <?php self::field_color( 'va_search_dd_item_hover_bg', 'Elem hover háttér' ); ?>
                                <?php self::field_num( 'va_search_dd_item_pad_y', 'Sor padding Y (px)', 4, 30 ); ?>
                                <?php self::field_num( 'va_search_dd_item_pad_x', 'Sor padding X (px)', 6, 40 ); ?>
                                <?php self::field_num( 'va_search_dd_thumb_radius', 'Kép lekerekítés (px)', 0, 20 ); ?>
                                <?php self::field_color( 'va_search_dd_noimg_bg', 'Nincs-kép háttér' ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔤</span>
                            <span class="va-settings-card__title">Szövegek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_search_dd_title_color', 'Találat cím szín' ); ?>
                                <?php self::field_num( 'va_search_dd_title_size', 'Találat cím méret (px)', 10, 24 ); ?>
                                <?php self::field_color( 'va_search_dd_price_color', 'Ár szín' ); ?>
                                <?php self::field_num( 'va_search_dd_price_size', 'Ár méret (px)', 10, 24 ); ?>
                                <?php self::field_color( 'va_search_dd_all_color', 'Összes találat szín' ); ?>
                                <?php self::field_num( 'va_search_dd_all_size', 'Összes találat méret (px)', 10, 24 ); ?>
                                <?php self::field_color( 'va_search_dd_all_hover_bg', 'Összes találat hover háttér' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🏷️</span>
                            <span class="va-settings-card__title">Pill badge (típus címke)</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_num( 'va_search_dd_badge_size', 'Badge betűméret (px)', 9, 18 ); ?>
                                <?php self::field_num( 'va_search_dd_badge_radius', 'Badge lekerekítés (px)', 4, 40 ); ?>
                                <?php self::field_color( 'va_search_dd_badge_listing_bg', 'Hirdetés badge háttér' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_listing_text', 'Hirdetés badge szöveg' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_auction_bg', 'Aukció badge háttér' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_auction_text', 'Aukció badge szöveg' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_category_bg', 'Kategória badge háttér' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_category_text', 'Kategória badge szöveg' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_user_bg', 'Felhasználó badge háttér' ); ?>
                                <?php self::field_color( 'va_search_dd_badge_user_text', 'Felhasználó badge szöveg' ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <?php submit_button( 'Kereső dizájn mentése' ); ?>
            </form>
        </div>
        <?php
    }

    public static function render_seo() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $test_result = null;

        if ( isset( $_POST['va_seo_save'] ) && check_admin_referer( 'va_seo_save' ) ) {
            $json = sanitize_textarea_field( wp_unslash( $_POST['va_gsc_service_account'] ?? '' ) );
            update_option( 'va_gsc_service_account', $json );
            update_option( 'va_gsc_indexing_enabled', isset( $_POST['va_gsc_indexing_enabled'] ) ? '1' : '0' );
            echo '<div class="notice notice-success"><p>SEO beállítások elmentve.</p></div>';
        }

        if ( isset( $_POST['va_seo_test'] ) && check_admin_referer( 'va_seo_test' ) ) {
            $test_url = esc_url_raw( wp_unslash( $_POST['va_gsc_test_url'] ?? '' ) );
            $test_result = VA_User_System::gsc_indexing_test( $test_url );
        }

        $current_json = (string) get_option( 'va_gsc_service_account', '' );
        $is_set = $current_json !== '';
        $gsc_enabled = get_option( 'va_gsc_indexing_enabled', '0' ) === '1';
        $service_email = '';
        if ( $is_set ) {
            $parsed = json_decode( $current_json, true );
            if ( is_array( $parsed ) && ! empty( $parsed['client_email'] ) ) {
                $service_email = (string) $parsed['client_email'];
            }
        }
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📈 SEO beállítások</h1>

            <?php if ( is_array( $test_result ) ) : ?>
                <div class="notice <?php echo ! empty( $test_result['ok'] ) ? 'notice-success' : 'notice-error'; ?>">
                    <p><strong>Google Indexing API teszt:</strong> <?php echo esc_html( (string) ( $test_result['message'] ?? '' ) ); ?></p>
                </div>
            <?php endif; ?>

            <div class="va-settings-grid">

                <div class="va-settings-card">
                    <div class="va-settings-card__head">
                        <span class="va-settings-card__icon">🔍</span>
                        <span class="va-settings-card__title">Google Search Console – Indexing API</span>
                    </div>
                    <div class="va-settings-card__body">
                        <p style="margin:0 0 12px;">Új hirdetés publikálásakor automatikusan beküldi az URL-t a Google Indexing API-nak, hogy azonnali crawlolást kérjen.</p>
                        <p style="margin:0 0 16px;font-size:12px;color:#aaa;">
                            Szükséges: Google Cloud projekt → Indexing API engedélyezve → Service Account JSON kulcs letöltve → Service Account email hozzáadva a Google Search Console-ban <strong>tulajdonosként</strong>.
                        </p>
                        <form method="post">
                            <?php wp_nonce_field( 'va_seo_save' ); ?>
                            <table class="form-table" style="margin:0;">
                                <tr>
                                    <th style="padding:8px 0;width:180px;color:#ccc;">Google Indexing API</th>
                                    <td style="padding:8px 0;">
                                        <label style="display:inline-flex;align-items:center;gap:8px;">
                                            <input type="checkbox" name="va_gsc_indexing_enabled" value="1" <?php checked( $gsc_enabled ); ?>>
                                            <span>Bekapcsolva (ha nem megy a Search Console jogosítás, hagyd kikapcsolva)</span>
                                        </label>
                                        <p style="margin:6px 0 0;font-size:11px;color:#aaa;">Kikapcsolt állapotban a Google API hívások nem futnak, az IndexNow és sitemap működés változatlan marad.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="padding:8px 0;width:180px;color:#ccc;">Service Account JSON</th>
                                    <td style="padding:8px 0;">
                                        <textarea name="va_gsc_service_account" rows="8" style="width:100%;font-family:monospace;font-size:11px;background:#111;color:#eee;border:1px solid #444;padding:8px;"><?php echo esc_textarea( $current_json ); ?></textarea>
                                        <p style="margin:6px 0 0;font-size:11px;color:<?php echo $is_set ? '#4caf50' : '#ff9800'; ?>;">
                                            <?php echo $is_set ? '✅ Service Account be van állítva.' : '⚠️ Nincs beállítva – az Indexing API nem aktív.'; ?>
                                        </p>
                                        <?php if ( $service_email !== '' ) : ?>
                                            <p style="margin:4px 0 0;font-size:11px;color:#9ecbff;">Service Account email: <strong><?php echo esc_html( $service_email ); ?></strong></p>
                                        <?php endif; ?>
                                        <?php if ( ! $gsc_enabled ) : ?>
                                            <p style="margin:4px 0 0;font-size:11px;color:#ffb74d;">ℹ️ A Google Indexing API jelenleg opcionális módban kikapcsolva van.</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            <p><button type="submit" name="va_seo_save" class="button button-primary">Mentés</button></p>
                        </form>

                        <hr style="border-color:#222;margin:16px 0;">

                        <form method="post">
                            <?php wp_nonce_field( 'va_seo_test' ); ?>
                            <table class="form-table" style="margin:0;">
                                <tr>
                                    <th style="padding:8px 0;width:180px;color:#ccc;">Teszt URL</th>
                                    <td style="padding:8px 0;">
                                        <input type="url" name="va_gsc_test_url" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="width:100%;max-width:640px;">
                                        <p style="margin:6px 0 0;font-size:11px;color:#aaa;">A teszt gomb token-t kér, majd URL_UPDATED kérést küld a Google Indexing API felé (ha a funkció be van kapcsolva).</p>
                                    </td>
                                </tr>
                            </table>
                            <p><button type="submit" name="va_seo_test" class="button" <?php disabled( ! $gsc_enabled ); ?>>Google kapcsolat teszt</button></p>
                            <?php if ( is_array( $test_result ) ) : ?>
                                <p style="margin:8px 0 0;font-size:12px;color:<?php echo ! empty( $test_result['ok'] ) ? '#4caf50' : '#ff6b6b'; ?>;">
                                    <strong><?php echo ! empty( $test_result['ok'] ) ? '✅ Teszt sikeres:' : '❌ Teszt hiba:'; ?></strong>
                                    <?php echo esc_html( (string) ( $test_result['message'] ?? '' ) ); ?>
                                </p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="va-settings-card">
                    <div class="va-settings-card__head">
                        <span class="va-settings-card__icon">⚡</span>
                        <span class="va-settings-card__title">IndexNow – Bing / Yandex</span>
                    </div>
                    <div class="va-settings-card__body">
                        <p style="margin:0 0 8px;">✅ <strong>Aktív</strong> – Minden új hirdetés publikálásakor automatikusan beküldésre kerül.</p>
                        <p style="margin:0;font-size:12px;color:#aaa;">Kulcs: <code>b873c945f60ea2d1bc78a3f254901e6d</code></p>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    public static function render_listing_search_designer(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📋 Hirdetés keresési oldal – teljes szerkesztő</h1>
            <p class="description">A <code>/va-hirdetes-kereses</code> oldal minden elemét – szűrő sáv, beviteli mezők, ár csúszka, kártyák, lapozó, szövegek – itt tudod testreszabni.</p>
            <?php settings_errors( 'va_lp_settings' ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_lp_settings' ); ?>

                <!-- ═══ SZŰRŐ SÁV ══════════════════════════════════════════ -->
                <h2 style="margin:28px 0 12px;font-size:16px;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:8px;">🗂 Szűrő sáv</h2>
                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🎨</span>
                            <span class="va-settings-card__title">Szűrő sáv háttér</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_filter_bg', 'Háttér szín' ); ?>
                                <?php self::field_color( 'va_lp_filter_border', 'Keret szín' ); ?>
                                <?php self::field_num( 'va_lp_filter_radius', 'Lekerekítés (px)', 0, 40 ); ?>
                                <?php self::field_num( 'va_lp_filter_padding', 'Belső margó (px)', 6, 60 ); ?>
                                <?php self::field_color( 'va_lp_filter_title_color', 'Cím szöveg szín' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📝</span>
                            <span class="va-settings-card__title">Bemenetek (input, select)</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_input_bg', 'Bemenet háttér' ); ?>
                                <?php self::field_color( 'va_lp_input_border', 'Bemenet keret' ); ?>
                                <?php self::field_color( 'va_lp_input_color', 'Bemenet szöveg' ); ?>
                                <?php self::field_color( 'va_lp_input_focus_border', 'Fókusz keret szín' ); ?>
                                <?php self::field_num( 'va_lp_input_radius', 'Lekerekítés (px)', 0, 40 ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ ÁR CSÚSZKA ══════════════════════════════════════════ -->
                <h2 style="margin:28px 0 12px;font-size:16px;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:8px;">🎚 Ár csúszka</h2>
                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔴</span>
                            <span class="va-settings-card__title">Csúszka színek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_slider_fill_color', 'Kitöltés szín' ); ?>
                                <?php self::field_color( 'va_lp_slider_thumb_color', 'Gomb szín' ); ?>
                                <?php self::field_color( 'va_lp_slider_track_bg', 'Sáv háttér' ); ?>
                                <?php self::field_color( 'va_lp_slider_display_color', 'Ár kijelző szín' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">⚙️</span>
                            <span class="va-settings-card__title">Csúszka tartomány</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_num( 'va_lp_slider_max', 'Maximum érték (Ft)', 1000, 999999999 ); ?>
                                <?php self::field_num( 'va_lp_slider_step', 'Lépésköz (Ft)', 1, 100000 ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ EREDMÉNY SOR + NÉZET VÁLTÓ ══════════════════════════ -->
                <h2 style="margin:28px 0 12px;font-size:16px;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:8px;">🔢 Eredmény sor & nézet váltó</h2>
                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📊</span>
                            <span class="va-settings-card__title">Találat szám & gomb</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_results_count_color', 'Találat szám szín' ); ?>
                                <?php self::field_color( 'va_lp_reset_btn_color', 'Szűrők törlése szöveg' ); ?>
                                <?php self::field_color( 'va_lp_reset_btn_border', 'Szűrők törlése keret' ); ?>
                                <?php self::field_color( 'va_lp_reset_btn_hover_color', 'Szűrők törlése hover szín' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔲</span>
                            <span class="va-settings-card__title">Nézet váltó (rács/lista ikon)</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_view_active_color', 'Aktív ikon szín' ); ?>
                                <?php self::field_color( 'va_lp_view_active_glow', 'Aktív ikon fény (glow)' ); ?>
                                <?php self::field_color( 'va_lp_view_border_active', 'Aktív keret szín' ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ KÁRTYÁK ══════════════════════════════════════════════ -->
                <h2 style="margin:28px 0 12px;font-size:16px;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:8px;">🃏 Hirdetés kártyák</h2>
                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🎨</span>
                            <span class="va-settings-card__title">Kártya alap</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_card_bg', 'Kártya háttér' ); ?>
                                <?php self::field_color( 'va_lp_card_border', 'Kártya keret' ); ?>
                                <?php self::field_num( 'va_lp_card_radius', 'Kártya lekerekítés (px)', 0, 40 ); ?>
                                <?php self::field_num( 'va_lp_card_gap', 'Rács hézag (px)', 4, 60 ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔤</span>
                            <span class="va-settings-card__title">Kártya szövegek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_card_title_color', 'Cím szín' ); ?>
                                <?php self::field_num( 'va_lp_card_title_size', 'Cím méret (px)', 10, 28 ); ?>
                                <?php self::field_color( 'va_lp_card_title_hover', 'Cím hover szín' ); ?>
                                <?php self::field_color( 'va_lp_card_price_color', 'Ár szín' ); ?>
                                <?php self::field_num( 'va_lp_card_price_size', 'Ár méret (px)', 10, 32 ); ?>
                                <?php self::field_color( 'va_lp_card_meta_color', 'Meta szöveg szín' ); ?>
                                <?php self::field_num( 'va_lp_card_meta_size', 'Meta méret (px)', 9, 20 ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">❤️</span>
                            <span class="va-settings-card__title">Kedvencek (watchlist) gomb</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_card_watchlist_color', 'Szív ikon szín' ); ?>
                                <?php self::field_color( 'va_lp_card_watchlist_border', 'Keret szín' ); ?>
                                <?php self::field_color( 'va_lp_card_watchlist_bg', 'Háttér szín' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🏷️</span>
                            <span class="va-settings-card__title">Kártya badge-ek</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_card_featured_color', 'Kiemelt szöveg szín' ); ?>
                                <?php self::field_color( 'va_lp_card_featured_border', 'Kiemelt keret szín' ); ?>
                                <?php self::field_color( 'va_lp_card_boost_color', 'Előre téve szöveg szín' ); ?>
                                <?php self::field_color( 'va_lp_card_boost_bg', 'Előre téve háttér' ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ LAPOZÓ ═══════════════════════════════════════════════ -->
                <h2 style="margin:28px 0 12px;font-size:16px;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:8px;">📄 Lapozó (pagination)</h2>
                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🎨</span>
                            <span class="va-settings-card__title">Lapozó gomb</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_pag_bg', 'Háttér szín' ); ?>
                                <?php self::field_color( 'va_lp_pag_color', 'Szöveg szín' ); ?>
                                <?php self::field_color( 'va_lp_pag_border', 'Keret szín' ); ?>
                                <?php self::field_num( 'va_lp_pag_radius', 'Lekerekítés (px)', 0, 40 ); ?>
                                <?php self::field_num( 'va_lp_pag_size', 'Betűméret (px)', 9, 22 ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">✅</span>
                            <span class="va-settings-card__title">Aktív lap gomb</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_color( 'va_lp_pag_active_bg', 'Aktív háttér' ); ?>
                                <?php self::field_color( 'va_lp_pag_active_color', 'Aktív szöveg szín' ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ SZÖVEGEK ══════════════════════════════════════════════ -->
                <h2 style="margin:28px 0 12px;font-size:16px;color:#fff;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:8px;">💬 Szövegek & feliratok</h2>
                <div class="va-settings-grid">
                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">📋</span>
                            <span class="va-settings-card__title">Szűrő sáv feliratok</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_text( 'va_lp_filter_title_text', 'Szűrő sáv cím' ); ?>
                                <?php self::field_text( 'va_lp_kw_placeholder', 'Kulcsszó placeholder' ); ?>
                                <?php self::field_text( 'va_lp_cat_placeholder', 'Kategória placeholder' ); ?>
                                <?php self::field_text( 'va_lp_county_placeholder', 'Megye placeholder' ); ?>
                                <?php self::field_text( 'va_lp_cond_placeholder', 'Állapot placeholder' ); ?>
                                <?php self::field_text( 'va_lp_slider_label_text', 'Ár csúszka felirat' ); ?>
                                <?php self::field_text( 'va_lp_reset_btn_text', 'Szűrők törlése gomb' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="va-settings-card">
                        <div class="va-settings-card__head">
                            <span class="va-settings-card__icon">🔄</span>
                            <span class="va-settings-card__title">Rendezés & betöltő</span>
                        </div>
                        <div class="va-settings-card__body">
                            <table class="form-table">
                                <?php self::field_text( 'va_lp_sort_default_lbl', 'Legújabb opció' ); ?>
                                <?php self::field_text( 'va_lp_sort_price_asc_lbl', 'Ár: növekvő opció' ); ?>
                                <?php self::field_text( 'va_lp_sort_price_desc_lbl', 'Ár: csökkenő opció' ); ?>
                                <?php self::field_text( 'va_lp_sort_views_lbl', 'Legtöbb megtekintés opció' ); ?>
                                <?php self::field_text( 'va_lp_loader_text', 'Betöltés szöveg' ); ?>
                                <?php self::field_color( 'va_lp_loader_color', 'Betöltés szöveg szín' ); ?>
                                <?php self::field_text( 'va_lp_empty_text', 'Nincs találat szöveg' ); ?>
                            </table>
                        </div>
                    </div>
                </div>

                <?php submit_button( 'Keresési oldal beállítások mentése' ); ?>
            </form>
        </div>
        <?php
    }

    public static function render_tools() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $msg  = isset( $_GET['va_tools_msg'] ) ? sanitize_key( (string) $_GET['va_tools_msg'] ) : '';
        $note = '';
        $cls  = 'notice notice-info';

        if ( $msg === 'import_ok' ) {
            $cnt  = absint( $_GET['count'] ?? 0 );
            $tax  = absint( $_GET['tax'] ?? 0 );
            $pages= absint( $_GET['pages'] ?? 0 );
            $note = '✅ Import sikeres! Frissített opciók: <strong>' . $cnt . '</strong>, taxonómiák: <strong>' . $tax . '</strong>, oldalak: <strong>' . $pages . '</strong>';
            $cls  = 'notice notice-success';
        } elseif ( $msg === 'reset_ok' ) {
            $cnt  = absint( $_GET['count'] ?? 0 );
            $note = '✅ Alaphelyzet visszaállítva. Újra felvett alap opciók: ' . $cnt;
            $cls  = 'notice notice-success';
        } elseif ( $msg === 'import_invalid' ) {
            $note = '❌ Hibás import fájl. Kérlek ellenőrizd a JSON tartalmát (a <code>va_export_*.json</code> fájlt töltsd fel).';
            $cls  = 'notice notice-error';
        } elseif ( $msg === 'import_empty' ) {
            $note = '❌ Nem érkezett import fájl. Válassz ki egy <code>.json</code> fájlt és kattints az "Import indítása" gombra.';
            $cls  = 'notice notice-error';
        } elseif ( $msg === 'import_toolarge' ) {
            $note = '❌ A feltöltött fájl túl nagy (PHP upload limit). A LocalWP PHP settings-ben növeld: <code>upload_max_filesize</code> és <code>post_max_size</code>.';
            $cls  = 'notice notice-error';
        } elseif ( $msg === 'import_partial' ) {
            $note = '❌ A fájl feltöltése megszakadt. Próbáld újra.';
            $cls  = 'notice notice-error';
        }
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📦 weingartnerauto.hu - Export / Import / Reset</h1>
            <p class="description" style="color:rgba(255,255,255,.5)">A teljes <code>va_*</code> beállításkészlet exportálható és importálható (általános, design, fejléc/lábléc, reklámzónák, hirdetés/aukció opciók).</p>
            <style>
            .va-ei-card { background:var(--va-bg2,#161616); border:1px solid var(--va-border,rgba(255,255,255,.1)); border-radius:10px; padding:20px 24px; margin-top:16px; max-width:960px; }
            .va-ei-card h2 { font-size:15px !important; color:#fff !important; text-transform:none !important; letter-spacing:0 !important; border-bottom:1px solid rgba(255,255,255,.1) !important; padding-bottom:10px !important; margin:0 0 14px !important; }
            .va-ei-card p, .va-ei-card label { color:rgba(255,255,255,.65) !important; font-size:13px; }
            .va-ei-card table.widefat { background:transparent !important; border:1px solid rgba(255,255,255,.1) !important; }
            .va-ei-card table.widefat th { background:rgba(255,255,255,.06) !important; color:rgba(255,255,255,.5) !important; font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
            .va-ei-card table.widefat td { background:transparent !important; color:#fff !important; border-bottom:1px solid rgba(255,255,255,.07) !important; }
            .va-ei-card table.widefat tr:last-child td { border-bottom:none !important; }
            .va-ei-card input[type=file] { color:#fff !important; }
            .va-ei-card input[type=checkbox] { accent-color:#ff0000; }
            .va-ei-card--danger { border-left:3px solid #ff4444; }
            </style>

            <?php if ( $note !== '' ): ?>
                <div class="va-notice va-tools-msg" style="display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:8px;margin-top:10px;margin-bottom:4px;border:1px solid <?php echo ( $msg === 'import_ok' || $msg === 'reset_ok' ) ? 'rgba(74,222,128,.35)' : 'rgba(248,113,113,.35)'; ?>;background:<?php echo ( $msg === 'import_ok' || $msg === 'reset_ok' ) ? 'rgba(74,222,128,.08)' : 'rgba(248,113,113,.08)'; ?>;color:#fff;font-size:13px;line-height:1.5;"><?php echo wp_kses( $note, [ 'strong' => [], 'code' => [], 'em' => [] ] ); ?></div>
            <?php endif; ?>

            <div class="va-ei-card">
                <h2>1) Export összes beállítás</h2>
                <p>JSON fájl letöltése, amit friss WordPress telepítésen vissza tudsz importálni — <strong>mindent tartalmaz</strong>.</p>
                <table class="widefat" style="margin-bottom:14px;">
                    <thead><tr><th>Mit exportál</th><th>Mit jelent</th></tr></thead>
                    <tbody>
                        <tr><td>✅ Összes <code>va_*</code> opció</td><td>Plan limitek (Silver=5, Gold=10 stb.), admin panel színek, általános beállítások, hirdetési limitek, árak, minden</td></tr>
                        <tr><td>✅ Taxonómiák (opcionális)</td><td>Kategóriák, megyék, állapot értékek — slug + szülő-gyerek kapcsolatok</td></tr>
                        <tr><td>✅ Fix oldalak (opcionális)</td><td>Hirdetés feladás, bejelentkezés, regisztráció, fiók, kredit vásárlás, ASZF stb. oldalak tartalma</td></tr>
                    </tbody>
                </table>
                <p style="color:#888;font-size:12px;">⚠ Nem exportál: hirdetések, felhasználók, feltöltött képek (ezek az adatbázisban vannak).</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="va_export_settings">
                    <?php wp_nonce_field( 'va_export_settings' ); ?>
                    <p>
                        <label><input type="checkbox" name="va_export_taxonomies" value="1" checked> <strong>Taxonómiák exportálása is</strong> (kategória, megye, állapot)</label><br>
                        <label><input type="checkbox" name="va_export_pages" value="1" checked> <strong>Fix oldalak exportálása is</strong> (slug + shortcode tartalom)</label>
                    </p>
                    <?php submit_button( '⬇ Teljes konfiguráció exportálása (.json)', 'primary', 'submit', false ); ?>
                </form>
            </div>

            <div class="va-ei-card">
                <h2>2) Import beállítás fájlból</h2>
                <p>Csak ezen plugin exportjából származó JSON fájlt tölts fel. Beállíthatod, hogy a taxonómiákat és oldalakat is importálja.</p>
                <?php
                $max_upload = (int) wp_max_upload_size();
                $post_max   = wp_convert_hr_to_bytes( ini_get('post_max_size') );
                $tmp_ok     = is_writable( sys_get_temp_dir() );
                $limit_ok   = $max_upload >= 1024*1024;
                ?>
                <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:rgba(255,255,255,.6);">
                    <strong style="color:rgba(255,255,255,.8);">⚙ PHP feltöltési info:</strong>
                    upload_max_filesize: <code><?php echo esc_html( ini_get('upload_max_filesize') ); ?></code> &nbsp;|&nbsp;
                    post_max_size: <code><?php echo esc_html( ini_get('post_max_size') ); ?></code> &nbsp;|&nbsp;
                    wp_max_upload_size: <code><?php echo esc_html( size_format($max_upload) ); ?></code> &nbsp;|&nbsp;
                    tmp_dir írható: <code><?php echo $tmp_ok ? '<span style="color:#4ade80">igen</span>' : '<span style="color:#f87171">NEM</span>'; ?></code>
                    <?php if ( ! $limit_ok ): ?>
                        <br><span style="color:#f87171;font-weight:600;">⚠ Az upload limit kevesebb mint 1MB! Növeld a LocalWP PHP Settings-ben.</span>
                    <?php endif; ?>
                </div>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="va_import_settings">
                    <?php wp_nonce_field( 'va_import_settings' ); ?>
                    <input type="file" name="va_import_file" accept="application/json,.json" required>
                    <p style="margin-top:10px;">
                        <label><input type="checkbox" name="va_import_taxonomies" value="1" checked> Taxonómiák importálása is</label><br>
                        <label><input type="checkbox" name="va_import_pages" value="1" checked> Fix oldalak importálása is</label>
                    </p>
                    <p style="margin-top:12px;">
                        <?php submit_button( 'Import indítása', 'secondary', 'submit', false ); ?>
                    </p>
                </form>
            </div>

            <div class="va-ei-card va-ei-card--danger">
                <h2>3) Visszaállítás alaphelyzetbe</h2>
                <p>Ez törli a jelenlegi <code>va_*</code> beállításokat, majd visszaállítja az alapértékeket.</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Biztosan alaphelyzetbe állítod az összes beállítást?');">
                    <input type="hidden" name="action" value="va_reset_settings">
                    <?php wp_nonce_field( 'va_reset_settings' ); ?>
                    <input type="hidden" name="va_reset_confirm" value="1">
                    <?php submit_button( 'Összes beállítás alaphelyzetbe', 'delete', 'submit', false ); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public static function handle_export_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_export_settings' );

        $payload = [
            'meta' => [
                'schema'      => 'vadaszapro-settings-v1',
                'exported_at' => current_time( 'mysql' ),
                'site_url'    => home_url( '/' ),
                'va_version'  => defined( 'VA_VERSION' ) ? VA_VERSION : 'unknown',
            ],
            'options' => self::get_all_va_options(),
        ];

        if ( (string) ( $_POST['va_export_taxonomies'] ?? '' ) === '1' ) {
            $payload['taxonomies'] = self::get_export_taxonomies();
        }
        if ( (string) ( $_POST['va_export_pages'] ?? '' ) === '1' ) {
            $payload['pages'] = self::get_export_pages();
        }

        $filename = 'vadaszapro-settings-' . gmdate( 'Ymd-His' ) . '.json';
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        exit;
    }

    public static function handle_import_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_import_settings' );

        $redirect = admin_url( 'admin.php?page=vadaszapro-tools' );

        // Fájl feltöltés hibakód ellenőrzés
        $upload_error = isset( $_FILES['va_import_file']['error'] ) ? (int) $_FILES['va_import_file']['error'] : UPLOAD_ERR_NO_FILE;
        if ( $upload_error !== UPLOAD_ERR_OK ) {
            $err_map = [
                UPLOAD_ERR_NO_FILE   => 'import_empty',
                UPLOAD_ERR_INI_SIZE  => 'import_toolarge',
                UPLOAD_ERR_FORM_SIZE => 'import_toolarge',
                UPLOAD_ERR_PARTIAL   => 'import_partial',
            ];
            $err_key = $err_map[ $upload_error ] ?? 'import_invalid';
            error_log( '[VA Import] Upload error code: ' . $upload_error );
            wp_safe_redirect( add_query_arg( 'va_tools_msg', $err_key, $redirect ) );
            exit;
        }

        $tmp_name = (string) ( $_FILES['va_import_file']['tmp_name'] ?? '' );
        if ( $tmp_name === '' || ! is_uploaded_file( $tmp_name ) ) {
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_empty', $redirect ) );
            exit;
        }

        $content = file_get_contents( $tmp_name );
        if ( $content === false || trim( $content ) === '' ) {
            error_log( '[VA Import] Cannot read tmp file: ' . $tmp_name );
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_invalid', $redirect ) );
            exit;
        }

        // UTF-8 BOM eltávolítás
        $content = ltrim( $content, "\xEF\xBB\xBF" );

        $data = json_decode( $content, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( '[VA Import] JSON decode error: ' . json_last_error_msg() );
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_invalid', $redirect ) );
            exit;
        }

        if ( ! is_array( $data ) || ! isset( $data['options'] ) || ! is_array( $data['options'] ) ) {
            error_log( '[VA Import] Missing options key. Keys: ' . implode( ',', array_keys( (array) $data ) ) );
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_invalid', $redirect ) );
            exit;
        }

        $count = 0;
        foreach ( $data['options'] as $key => $value ) {
            if ( ! is_string( $key ) || strpos( $key, 'va_' ) !== 0 ) {
                continue;
            }
            // Autoload: csak ha már létezik, megtartjuk az autoload értékét; egyébként nem autoload
            $existing = get_option( $key, '__NOT_SET__' );
            if ( $existing === '__NOT_SET__' ) {
                add_option( $key, $value, '', 'no' );
            } else {
                update_option( $key, $value );
            }
            $count++;
        }

        $tax_count  = 0;
        $page_count = 0;

        if ( (string) ( $_POST['va_import_taxonomies'] ?? '' ) === '1' && isset( $data['taxonomies'] ) && is_array( $data['taxonomies'] ) ) {
            $tax_count = self::import_taxonomies( $data['taxonomies'] );
        }

        if ( (string) ( $_POST['va_import_pages'] ?? '' ) === '1' && isset( $data['pages'] ) && is_array( $data['pages'] ) ) {
            $page_count = self::import_pages( $data['pages'] );
        }

        // Rewrite flush ha CPT/taxonomy érintett
        flush_rewrite_rules( false );

        wp_safe_redirect( add_query_arg( [ 'va_tools_msg' => 'import_ok', 'count' => $count, 'tax' => $tax_count, 'pages' => $page_count ], $redirect ) );
        exit;
    }

    public static function handle_reset_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_reset_settings' );

        if ( (string) ( $_POST['va_reset_confirm'] ?? '' ) !== '1' ) {
            wp_die( 'Hiányzó megerősítés.' );
        }

        $keep_keys = [
            'va_pages_created_v4',
        ];

        $all = self::get_all_va_options();
        foreach ( array_keys( $all ) as $key ) {
            if ( in_array( $key, $keep_keys, true ) ) {
                continue;
            }
            delete_option( $key );
        }

        // Alap opciók visszaépítése.
        self::register_settings();

        $all_after = self::get_all_va_options();
        wp_safe_redirect( add_query_arg( [ 'va_tools_msg' => 'reset_ok', 'count' => count( $all_after ) ], admin_url( 'admin.php?page=vadaszapro-tools' ) ) );
        exit;
    }

    public static function handle_save_nav_items(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Nincs jogosultság.' );
        check_admin_referer( 'va_save_nav_items', 'va_nav_nonce' );

        $raw = wp_unslash( $_POST['va_nav_json'] ?? '' );
        $decoded = json_decode( $raw, true );
        if ( ! is_array( $decoded ) ) $decoded = [];

        $clean = [];
        foreach ( $decoded as $item ) {
            if ( ! is_array( $item ) ) continue;
            $label = sanitize_text_field( (string) ( $item['label'] ?? '' ) );
            $url   = sanitize_text_field( (string) ( $item['url']   ?? '' ) );
            if ( $label === '' ) continue;
            $clean[] = [
                'label'   => $label,
                'url'     => $url,
                'enabled' => ! empty( $item['enabled'] ),
            ];
        }

        update_option( 'va_nav_items_json', wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
        wp_safe_redirect( add_query_arg( 'va_nav_saved', '1', admin_url( 'admin.php?page=vadaszapro-header-footer' ) ) );
        exit;
    }

    public static function handle_apply_hf_preset() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_apply_hf_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets = self::get_header_footer_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_hf_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-header-footer' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            if ( strpos( (string) $key, 'va_' ) !== 0 ) {
                continue;
            }
            update_option( (string) $key, $value );
        }

        wp_safe_redirect( add_query_arg( 'va_hf_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-header-footer' ) ) );
        exit;
    }

    private static function get_all_va_options(): array {
        global $wpdb;

        $like = $wpdb->esc_like( 'va_' ) . '%';
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $like
            ),
            ARRAY_A
        );

        $options = [];
        foreach ( (array) $rows as $row ) {
            $name = (string) ( $row['option_name'] ?? '' );
            if ( $name === '' ) {
                continue;
            }
            $options[ $name ] = maybe_unserialize( $row['option_value'] ?? '' );
        }

        ksort( $options );
        return $options;
    }

    /* ── Admin Panel preset alkalmazás ───────────────────── */
    public static function handle_apply_ap_preset(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Nincs jogosultság.' );
        check_admin_referer( 'va_apply_ap_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets = self::get_adminpanel_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_ap_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-adminpanel' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            update_option( (string) $key, $value );
        }

        wp_safe_redirect( add_query_arg( 'va_ap_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-adminpanel' ) ) );
        exit;
    }

    public static function handle_apply_design_preset(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Nincs jogosultság.' );
        check_admin_referer( 'va_apply_design_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets = self::get_design_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_design_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-design' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            update_option( (string) $key, $value );
        }

        wp_safe_redirect( add_query_arg( 'va_design_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-design' ) ) );
        exit;
    }

    private static function get_design_presets(): array {
        return [

            /* ── 1 ── */
            'dark_hunter' => [
                'label' => '🦌 Dark Hunter', 'desc' => 'Vadász – sötét, piros accent',
                'bg' => '#060606', 'accent' => '#ff2020', 'text' => '#e8e8e8',
                'options' => [
                    'va_color_global_bg'             => '#060606',
                    'va_color_global_text'           => '#e8e8e8',
                    'va_color_global_muted'          => 'rgba(232,232,232,.45)',
                    'va_color_global_accent'         => '#ff2020',
                    'va_color_content_bg'            => '#0a0a0a',
                    'va_color_content_text'          => '#e0e0e0',
                    'va_color_content_headings'      => '#ffffff',
                    'va_color_content_links'         => '#ff4444',
                    'va_color_hero_title'            => '#ffffff',
                    'va_color_hero_sub'              => 'rgba(255,255,255,.7)',
                    'va_color_hero_badge_bg'         => 'rgba(255,32,32,.15)',
                    'va_color_hero_badge_border'     => 'rgba(255,32,32,.4)',
                    'va_color_hero_badge_text'       => '#ff6666',
                    'va_color_hero_btn_primary_bg'   => '#ff2020',
                    'va_color_hero_btn_primary_hover'=> '#cc0000',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(255,32,32,.5)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,255,255,.06)',
                    'va_color_hero_btn_ghost_border' => 'rgba(255,255,255,.25)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(255,255,255,.12)',
                    'va_color_hero_btn_ghost_text'   => '#ffffff',
                    'va_font_global'                 => 'inter',
                    'va_font_headings'               => 'oswald',
                ],
            ],

            /* ── 2 ── */
            'arctic_clinic' => [
                'label' => '🏥 Arctic Clinic', 'desc' => 'Orvosi fehér, kék accent, prémium',
                'bg' => '#f8fafc', 'accent' => '#0066ff', 'text' => '#0d1117',
                'options' => [
                    'va_color_global_bg'             => '#f8fafc',
                    'va_color_global_text'           => '#0d1117',
                    'va_color_global_muted'          => 'rgba(13,17,23,.5)',
                    'va_color_global_accent'         => '#0066ff',
                    'va_color_content_bg'            => '#ffffff',
                    'va_color_content_text'          => '#1a1a2e',
                    'va_color_content_headings'      => '#0d1117',
                    'va_color_content_links'         => '#0066ff',
                    'va_color_hero_title'            => '#0d1117',
                    'va_color_hero_sub'              => 'rgba(13,17,23,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(0,102,255,.1)',
                    'va_color_hero_badge_border'     => 'rgba(0,102,255,.3)',
                    'va_color_hero_badge_text'       => '#0055dd',
                    'va_color_hero_btn_primary_bg'   => '#0066ff',
                    'va_color_hero_btn_primary_hover'=> '#0052cc',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(0,102,255,.4)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(0,0,0,.04)',
                    'va_color_hero_btn_ghost_border' => 'rgba(0,0,0,.2)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(0,0,0,.08)',
                    'va_color_hero_btn_ghost_text'   => '#0d1117',
                    'va_font_global'                 => 'inter',
                    'va_font_headings'               => 'plus-jakarta-sans',
                ],
            ],

            /* ── 3 ── */
            'mint_medical' => [
                'label' => '🌿 Mint Medical', 'desc' => 'Letisztult fehér + mentazöld, bizalmi',
                'bg' => '#f5faf8', 'accent' => '#00a878', 'text' => '#0a1e17',
                'options' => [
                    'va_color_global_bg'             => '#f5faf8',
                    'va_color_global_text'           => '#0a1e17',
                    'va_color_global_muted'          => 'rgba(10,30,23,.48)',
                    'va_color_global_accent'         => '#00a878',
                    'va_color_content_bg'            => '#ffffff',
                    'va_color_content_text'          => '#0a1e17',
                    'va_color_content_headings'      => '#062e20',
                    'va_color_content_links'         => '#00a878',
                    'va_color_hero_title'            => '#062e20',
                    'va_color_hero_sub'              => 'rgba(6,46,32,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(0,168,120,.12)',
                    'va_color_hero_badge_border'     => 'rgba(0,168,120,.35)',
                    'va_color_hero_badge_text'       => '#007a58',
                    'va_color_hero_btn_primary_bg'   => '#00a878',
                    'va_color_hero_btn_primary_hover'=> '#008862',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(0,168,120,.4)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(0,0,0,.04)',
                    'va_color_hero_btn_ghost_border' => 'rgba(0,168,120,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(0,168,120,.08)',
                    'va_color_hero_btn_ghost_text'   => '#00a878',
                    'va_font_global'                 => 'poppins',
                    'va_font_headings'               => 'manrope',
                ],
            ],

            /* ── 4 ── */
            'speed_night' => [
                'label' => '🏎 Speed Night', 'desc' => 'Racing fekete + elektromos piros glow',
                'bg' => '#040404', 'accent' => '#ff0022', 'text' => '#f0f0f5',
                'options' => [
                    'va_color_global_bg'             => '#040404',
                    'va_color_global_text'           => '#f0f0f5',
                    'va_color_global_muted'          => 'rgba(240,240,245,.42)',
                    'va_color_global_accent'         => '#ff0022',
                    'va_color_content_bg'            => '#080808',
                    'va_color_content_text'          => '#e8e8e8',
                    'va_color_content_headings'      => '#ffffff',
                    'va_color_content_links'         => '#ff3344',
                    'va_color_hero_title'            => '#ffffff',
                    'va_color_hero_sub'              => 'rgba(255,255,255,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(255,0,34,.12)',
                    'va_color_hero_badge_border'     => 'rgba(255,0,34,.4)',
                    'va_color_hero_badge_text'       => '#ff4455',
                    'va_color_hero_btn_primary_bg'   => '#ff0022',
                    'va_color_hero_btn_primary_hover'=> '#cc0018',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(255,0,34,.6)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,255,255,.05)',
                    'va_color_hero_btn_ghost_border' => 'rgba(255,255,255,.2)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(255,255,255,.1)',
                    'va_color_hero_btn_ghost_text'   => '#ffffff',
                    'va_font_global'                 => 'barlow',
                    'va_font_headings'               => 'barlow-condensed',
                ],
            ],

            /* ── 5 ── */
            'forest_camo' => [
                'label' => '🌲 Forest Camo', 'desc' => 'Vadászos erdei, terepszín + lime zöld',
                'bg' => '#0d110a', 'accent' => '#7aad2f', 'text' => '#e8f2d8',
                'options' => [
                    'va_color_global_bg'             => '#0d110a',
                    'va_color_global_text'           => '#e8f2d8',
                    'va_color_global_muted'          => 'rgba(232,242,216,.44)',
                    'va_color_global_accent'         => '#7aad2f',
                    'va_color_content_bg'            => '#111506',
                    'va_color_content_text'          => '#deecc8',
                    'va_color_content_headings'      => '#f0ffda',
                    'va_color_content_links'         => '#a3d44e',
                    'va_color_hero_title'            => '#f4ffe0',
                    'va_color_hero_sub'              => 'rgba(244,255,224,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(122,173,47,.14)',
                    'va_color_hero_badge_border'     => 'rgba(122,173,47,.4)',
                    'va_color_hero_badge_text'       => '#a3d44e',
                    'va_color_hero_btn_primary_bg'   => '#7aad2f',
                    'va_color_hero_btn_primary_hover'=> '#618c24',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(122,173,47,.5)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,255,255,.05)',
                    'va_color_hero_btn_ghost_border' => 'rgba(122,173,47,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(122,173,47,.1)',
                    'va_color_hero_btn_ghost_text'   => '#c8e896',
                    'va_font_global'                 => 'roboto',
                    'va_font_headings'               => 'oswald',
                ],
            ],

            /* ── 6 ── */
            'obsidian_gold' => [
                'label' => '👑 Obsidian Gold', 'desc' => 'Obszidián fekete + nemes arany, luxury',
                'bg' => '#080807', 'accent' => '#e8b840', 'text' => '#f5f0e0',
                'options' => [
                    'va_color_global_bg'             => '#080807',
                    'va_color_global_text'           => '#f5f0e0',
                    'va_color_global_muted'          => 'rgba(245,240,224,.44)',
                    'va_color_global_accent'         => '#e8b840',
                    'va_color_content_bg'            => '#0d0d0a',
                    'va_color_content_text'          => '#ede8d5',
                    'va_color_content_headings'      => '#fff8e8',
                    'va_color_content_links'         => '#f5cf6a',
                    'va_color_hero_title'            => '#fff8e8',
                    'va_color_hero_sub'              => 'rgba(255,248,232,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(232,184,64,.12)',
                    'va_color_hero_badge_border'     => 'rgba(232,184,64,.4)',
                    'va_color_hero_badge_text'       => '#f5cf6a',
                    'va_color_hero_btn_primary_bg'   => '#e8b840',
                    'va_color_hero_btn_primary_hover'=> '#c99c28',
                    'va_color_hero_btn_primary_text' => '#0a0a06',
                    'va_color_hero_btn_primary_glow' => 'rgba(232,184,64,.5)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,255,255,.05)',
                    'va_color_hero_btn_ghost_border' => 'rgba(232,184,64,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(232,184,64,.1)',
                    'va_color_hero_btn_ghost_text'   => '#f5cf6a',
                    'va_font_global'                 => 'cormorant-garamond',
                    'va_font_headings'               => 'playfair',
                ],
            ],

            /* ── 7 ── */
            'neon_cyber' => [
                'label' => '⚡ Neon Cyber', 'desc' => 'Cyberpunk 2026 – fekete + neon zöld glow',
                'bg' => '#030a06', 'accent' => '#00ff88', 'text' => '#ccffe8',
                'options' => [
                    'va_color_global_bg'             => '#030a06',
                    'va_color_global_text'           => '#ccffe8',
                    'va_color_global_muted'          => 'rgba(204,255,232,.4)',
                    'va_color_global_accent'         => '#00ff88',
                    'va_color_content_bg'            => '#040e08',
                    'va_color_content_text'          => '#c8fce0',
                    'va_color_content_headings'      => '#e0fff0',
                    'va_color_content_links'         => '#33ffaa',
                    'va_color_hero_title'            => '#e0fff0',
                    'va_color_hero_sub'              => 'rgba(224,255,240,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(0,255,136,.1)',
                    'va_color_hero_badge_border'     => 'rgba(0,255,136,.4)',
                    'va_color_hero_badge_text'       => '#00ff88',
                    'va_color_hero_btn_primary_bg'   => '#00ff88',
                    'va_color_hero_btn_primary_hover'=> '#00cc6a',
                    'va_color_hero_btn_primary_text' => '#030a06',
                    'va_color_hero_btn_primary_glow' => 'rgba(0,255,136,.6)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(0,255,136,.05)',
                    'va_color_hero_btn_ghost_border' => 'rgba(0,255,136,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(0,255,136,.12)',
                    'va_color_hero_btn_ghost_text'   => '#00ff88',
                    'va_font_global'                 => 'space-grotesk',
                    'va_font_headings'               => 'syne',
                ],
            ],

            /* ── 8 ── */
            'midnight_navy' => [
                'label' => '🌊 Midnight Navy', 'desc' => 'Mélytenger kék + neon kék, prémium',
                'bg' => '#07090f', 'accent' => '#3b8af7', 'text' => '#dce8ff',
                'options' => [
                    'va_color_global_bg'             => '#07090f',
                    'va_color_global_text'           => '#dce8ff',
                    'va_color_global_muted'          => 'rgba(220,232,255,.44)',
                    'va_color_global_accent'         => '#3b8af7',
                    'va_color_content_bg'            => '#0b0f1a',
                    'va_color_content_text'          => '#d4e0ff',
                    'va_color_content_headings'      => '#eef4ff',
                    'va_color_content_links'         => '#6aadff',
                    'va_color_hero_title'            => '#eef4ff',
                    'va_color_hero_sub'              => 'rgba(238,244,255,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(59,138,247,.12)',
                    'va_color_hero_badge_border'     => 'rgba(59,138,247,.4)',
                    'va_color_hero_badge_text'       => '#6aadff',
                    'va_color_hero_btn_primary_bg'   => '#3b8af7',
                    'va_color_hero_btn_primary_hover'=> '#2270e0',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(59,138,247,.5)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(59,138,247,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(59,138,247,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(59,138,247,.14)',
                    'va_color_hero_btn_ghost_text'   => '#6aadff',
                    'va_font_global'                 => 'manrope',
                    'va_font_headings'               => 'plus-jakarta-sans',
                ],
            ],

            /* ── 9 ── */
            'copper_outdoor' => [
                'label' => '🔶 Copper Outdoor', 'desc' => 'Outdoor barna + réz, prémium vadász',
                'bg' => '#0a0806', 'accent' => '#c97d3e', 'text' => '#f2e8d8',
                'options' => [
                    'va_color_global_bg'             => '#0a0806',
                    'va_color_global_text'           => '#f2e8d8',
                    'va_color_global_muted'          => 'rgba(242,232,216,.44)',
                    'va_color_global_accent'         => '#c97d3e',
                    'va_color_content_bg'            => '#100d09',
                    'va_color_content_text'          => '#eadfc8',
                    'va_color_content_headings'      => '#fff4e8',
                    'va_color_content_links'         => '#e0a265',
                    'va_color_hero_title'            => '#fff4e8',
                    'va_color_hero_sub'              => 'rgba(255,244,232,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(201,125,62,.13)',
                    'va_color_hero_badge_border'     => 'rgba(201,125,62,.4)',
                    'va_color_hero_badge_text'       => '#e0a265',
                    'va_color_hero_btn_primary_bg'   => '#c97d3e',
                    'va_color_hero_btn_primary_hover'=> '#a6622f',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(201,125,62,.5)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(201,125,62,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(201,125,62,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(201,125,62,.13)',
                    'va_color_hero_btn_ghost_text'   => '#e0a265',
                    'va_font_global'                 => 'cabin',
                    'va_font_headings'               => 'oswald',
                ],
            ],

            /* ── 10 ── */
            'sunset_blaze' => [
                'label' => '🌅 Sunset Blaze', 'desc' => 'Napnyugta – korall narancs + arany',
                'bg' => '#0f0a06', 'accent' => '#ff6b35', 'text' => '#fff0e8',
                'options' => [
                    'va_color_global_bg'             => '#0f0a06',
                    'va_color_global_text'           => '#fff0e8',
                    'va_color_global_muted'          => 'rgba(255,240,232,.44)',
                    'va_color_global_accent'         => '#ff6b35',
                    'va_color_content_bg'            => '#170e09',
                    'va_color_content_text'          => '#f5e8dc',
                    'va_color_content_headings'      => '#fffaf4',
                    'va_color_content_links'         => '#ffa040',
                    'va_color_hero_title'            => '#fffaf4',
                    'va_color_hero_sub'              => 'rgba(255,250,244,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(255,107,53,.12)',
                    'va_color_hero_badge_border'     => 'rgba(255,107,53,.4)',
                    'va_color_hero_badge_text'       => '#ffa040',
                    'va_color_hero_btn_primary_bg'   => '#ff6b35',
                    'va_color_hero_btn_primary_hover'=> '#e05520',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(255,107,53,.55)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,107,53,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(255,107,53,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(255,107,53,.13)',
                    'va_color_hero_btn_ghost_text'   => '#ffa040',
                    'va_font_global'                 => 'nunito',
                    'va_font_headings'               => 'raleway',
                ],
            ],

            /* ── 11 ── */
            'ink_paper' => [
                'label' => '📰 Ink & Paper', 'desc' => 'Editorial újság fehér, tinta fekete',
                'bg' => '#fafafa', 'accent' => '#1a1a1a', 'text' => '#111111',
                'options' => [
                    'va_color_global_bg'             => '#fafafa',
                    'va_color_global_text'           => '#111111',
                    'va_color_global_muted'          => 'rgba(17,17,17,.5)',
                    'va_color_global_accent'         => '#1a1a1a',
                    'va_color_content_bg'            => '#ffffff',
                    'va_color_content_text'          => '#222222',
                    'va_color_content_headings'      => '#000000',
                    'va_color_content_links'         => '#333333',
                    'va_color_hero_title'            => '#000000',
                    'va_color_hero_sub'              => 'rgba(0,0,0,.6)',
                    'va_color_hero_badge_bg'         => 'rgba(0,0,0,.06)',
                    'va_color_hero_badge_border'     => 'rgba(0,0,0,.2)',
                    'va_color_hero_badge_text'       => '#333333',
                    'va_color_hero_btn_primary_bg'   => '#111111',
                    'va_color_hero_btn_primary_hover'=> '#000000',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(0,0,0,.3)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(0,0,0,.04)',
                    'va_color_hero_btn_ghost_border' => 'rgba(0,0,0,.2)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(0,0,0,.08)',
                    'va_color_hero_btn_ghost_text'   => '#111111',
                    'va_font_global'                 => 'merriweather',
                    'va_font_headings'               => 'playfair',
                ],
            ],

            /* ── 12 ── */
            'purple_tech' => [
                'label' => '🔮 Purple Tech', 'desc' => 'SaaS lila, tech 2026 vibe',
                'bg' => '#090910', 'accent' => '#8b5cf6', 'text' => '#e4e4f0',
                'options' => [
                    'va_color_global_bg'             => '#090910',
                    'va_color_global_text'           => '#e4e4f0',
                    'va_color_global_muted'          => 'rgba(228,228,240,.42)',
                    'va_color_global_accent'         => '#8b5cf6',
                    'va_color_content_bg'            => '#0f0f18',
                    'va_color_content_text'          => '#dcdcf0',
                    'va_color_content_headings'      => '#f0f0ff',
                    'va_color_content_links'         => '#a78bfa',
                    'va_color_hero_title'            => '#f0f0ff',
                    'va_color_hero_sub'              => 'rgba(240,240,255,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(139,92,246,.12)',
                    'va_color_hero_badge_border'     => 'rgba(139,92,246,.4)',
                    'va_color_hero_badge_text'       => '#a78bfa',
                    'va_color_hero_btn_primary_bg'   => '#8b5cf6',
                    'va_color_hero_btn_primary_hover'=> '#7040e0',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(139,92,246,.55)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(139,92,246,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(139,92,246,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(139,92,246,.14)',
                    'va_color_hero_btn_ghost_text'   => '#a78bfa',
                    'va_font_global'                 => 'outfit',
                    'va_font_headings'               => 'space-grotesk',
                ],
            ],

            /* ── 13 ── */
            'ocean_depth' => [
                'label' => '🐋 Ocean Depth', 'desc' => 'Mélyvíz teal + aqua neon',
                'bg' => '#040e12', 'accent' => '#00c8d4', 'text' => '#c8f4f8',
                'options' => [
                    'va_color_global_bg'             => '#040e12',
                    'va_color_global_text'           => '#c8f4f8',
                    'va_color_global_muted'          => 'rgba(200,244,248,.42)',
                    'va_color_global_accent'         => '#00c8d4',
                    'va_color_content_bg'            => '#081418',
                    'va_color_content_text'          => '#c0ecf0',
                    'va_color_content_headings'      => '#e0f8fc',
                    'va_color_content_links'         => '#33dde6',
                    'va_color_hero_title'            => '#e0f8fc',
                    'va_color_hero_sub'              => 'rgba(224,248,252,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(0,200,212,.11)',
                    'va_color_hero_badge_border'     => 'rgba(0,200,212,.4)',
                    'va_color_hero_badge_text'       => '#33dde6',
                    'va_color_hero_btn_primary_bg'   => '#00c8d4',
                    'va_color_hero_btn_primary_hover'=> '#00a0aa',
                    'va_color_hero_btn_primary_text' => '#030e12',
                    'va_color_hero_btn_primary_glow' => 'rgba(0,200,212,.55)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(0,200,212,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(0,200,212,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(0,200,212,.14)',
                    'va_color_hero_btn_ghost_text'   => '#33dde6',
                    'va_font_global'                 => 'dm-sans',
                    'va_font_headings'               => 'urbanist',
                ],
            ],

            /* ── 14 ── */
            'aurora_nord' => [
                'label' => '🌌 Aurora Nord', 'desc' => 'Északi fény – nordic blue palette',
                'bg' => '#0a0e14', 'accent' => '#88c0d0', 'text' => '#d8dee9',
                'options' => [
                    'va_color_global_bg'             => '#0a0e14',
                    'va_color_global_text'           => '#d8dee9',
                    'va_color_global_muted'          => 'rgba(216,222,233,.44)',
                    'va_color_global_accent'         => '#88c0d0',
                    'va_color_content_bg'            => '#111820',
                    'va_color_content_text'          => '#d0d8e8',
                    'va_color_content_headings'      => '#eceff4',
                    'va_color_content_links'         => '#a3be8c',
                    'va_color_hero_title'            => '#eceff4',
                    'va_color_hero_sub'              => 'rgba(236,239,244,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(136,192,208,.1)',
                    'va_color_hero_badge_border'     => 'rgba(136,192,208,.35)',
                    'va_color_hero_badge_text'       => '#88c0d0',
                    'va_color_hero_btn_primary_bg'   => '#88c0d0',
                    'va_color_hero_btn_primary_hover'=> '#6aaabb',
                    'va_color_hero_btn_primary_text' => '#0a0e14',
                    'va_color_hero_btn_primary_glow' => 'rgba(136,192,208,.45)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(136,192,208,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(136,192,208,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(136,192,208,.14)',
                    'va_color_hero_btn_ghost_text'   => '#88c0d0',
                    'va_font_global'                 => 'ibm-plex-sans',
                    'va_font_headings'               => 'syne',
                ],
            ],

            /* ── 15 ── */
            'sakura_light' => [
                'label' => '🌸 Sakura Light', 'desc' => 'Japán prémium – fehér + rózsa pink',
                'bg' => '#fdf7f9', 'accent' => '#e8558a', 'text' => '#1f0a14',
                'options' => [
                    'va_color_global_bg'             => '#fdf7f9',
                    'va_color_global_text'           => '#1f0a14',
                    'va_color_global_muted'          => 'rgba(31,10,20,.48)',
                    'va_color_global_accent'         => '#e8558a',
                    'va_color_content_bg'            => '#ffffff',
                    'va_color_content_text'          => '#2a1020',
                    'va_color_content_headings'      => '#0f0008',
                    'va_color_content_links'         => '#e8558a',
                    'va_color_hero_title'            => '#0f0008',
                    'va_color_hero_sub'              => 'rgba(15,0,8,.6)',
                    'va_color_hero_badge_bg'         => 'rgba(232,85,138,.1)',
                    'va_color_hero_badge_border'     => 'rgba(232,85,138,.3)',
                    'va_color_hero_badge_text'       => '#cc3070',
                    'va_color_hero_btn_primary_bg'   => '#e8558a',
                    'va_color_hero_btn_primary_hover'=> '#c83870',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(232,85,138,.4)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(232,85,138,.06)',
                    'va_color_hero_btn_ghost_border' => 'rgba(232,85,138,.25)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(232,85,138,.1)',
                    'va_color_hero_btn_ghost_text'   => '#e8558a',
                    'va_font_global'                 => 'quicksand',
                    'va_font_headings'               => 'cormorant-garamond',
                ],
            ],

            /* ── 16 ── */
            'royal_plum' => [
                'label' => '🍷 Royal Plum', 'desc' => 'Sötét lila-bordó, exkluzív premium',
                'bg' => '#12091a', 'accent' => '#c75cff', 'text' => '#f8f0ff',
                'options' => [
                    'va_color_global_bg'             => '#12091a',
                    'va_color_global_text'           => '#f8f0ff',
                    'va_color_global_muted'          => 'rgba(248,240,255,.44)',
                    'va_color_global_accent'         => '#c75cff',
                    'va_color_content_bg'            => '#1a0f24',
                    'va_color_content_text'          => '#f0e8ff',
                    'va_color_content_headings'      => '#fff4ff',
                    'va_color_content_links'         => '#da98ff',
                    'va_color_hero_title'            => '#fff4ff',
                    'va_color_hero_sub'              => 'rgba(255,244,255,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(199,92,255,.12)',
                    'va_color_hero_badge_border'     => 'rgba(199,92,255,.4)',
                    'va_color_hero_badge_text'       => '#da98ff',
                    'va_color_hero_btn_primary_bg'   => '#c75cff',
                    'va_color_hero_btn_primary_hover'=> '#a840e0',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(199,92,255,.55)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(199,92,255,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(199,92,255,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(199,92,255,.14)',
                    'va_color_hero_btn_ghost_text'   => '#da98ff',
                    'va_font_global'                 => 'raleway',
                    'va_font_headings'               => 'abril-fatface',
                ],
            ],

            /* ── 17 ── */
            'steel_ember' => [
                'label' => '🔥 Steel Ember', 'desc' => 'Acélos szürke + izzó narancs',
                'bg' => '#0d0e10', 'accent' => '#ff7a22', 'text' => '#f0f2f5',
                'options' => [
                    'va_color_global_bg'             => '#0d0e10',
                    'va_color_global_text'           => '#f0f2f5',
                    'va_color_global_muted'          => 'rgba(240,242,245,.44)',
                    'va_color_global_accent'         => '#ff7a22',
                    'va_color_content_bg'            => '#111317',
                    'va_color_content_text'          => '#e8eaed',
                    'va_color_content_headings'      => '#ffffff',
                    'va_color_content_links'         => '#ffaa66',
                    'va_color_hero_title'            => '#ffffff',
                    'va_color_hero_sub'              => 'rgba(255,255,255,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(255,122,34,.12)',
                    'va_color_hero_badge_border'     => 'rgba(255,122,34,.4)',
                    'va_color_hero_badge_text'       => '#ffaa66',
                    'va_color_hero_btn_primary_bg'   => '#ff7a22',
                    'va_color_hero_btn_primary_hover'=> '#e06010',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(255,122,34,.55)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,122,34,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(255,122,34,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(255,122,34,.14)',
                    'va_color_hero_btn_ghost_text'   => '#ffaa66',
                    'va_font_global'                 => 'barlow',
                    'va_font_headings'               => 'barlow-condensed',
                ],
            ],

            /* ── 18 ── */
            'matrix_hack' => [
                'label' => '💻 Matrix Hack', 'desc' => 'Hacker – mély fekete + lime zöld',
                'bg' => '#020802', 'accent' => '#39ff14', 'text' => '#ccffcc',
                'options' => [
                    'va_color_global_bg'             => '#020802',
                    'va_color_global_text'           => '#ccffcc',
                    'va_color_global_muted'          => 'rgba(204,255,204,.4)',
                    'va_color_global_accent'         => '#39ff14',
                    'va_color_content_bg'            => '#040e04',
                    'va_color_content_text'          => '#c4fcc4',
                    'va_color_content_headings'      => '#e0ffe0',
                    'va_color_content_links'         => '#66ff44',
                    'va_color_hero_title'            => '#e0ffe0',
                    'va_color_hero_sub'              => 'rgba(224,255,224,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(57,255,20,.1)',
                    'va_color_hero_badge_border'     => 'rgba(57,255,20,.38)',
                    'va_color_hero_badge_text'       => '#39ff14',
                    'va_color_hero_btn_primary_bg'   => '#39ff14',
                    'va_color_hero_btn_primary_hover'=> '#22cc0a',
                    'va_color_hero_btn_primary_text' => '#020802',
                    'va_color_hero_btn_primary_glow' => 'rgba(57,255,20,.6)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(57,255,20,.06)',
                    'va_color_hero_btn_ghost_border' => 'rgba(57,255,20,.28)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(57,255,20,.12)',
                    'va_color_hero_btn_ghost_text'   => '#39ff14',
                    'va_font_global'                 => 'fira-sans',
                    'va_font_headings'               => 'oswald',
                ],
            ],

            /* ── 19 ── */
            'sand_desert' => [
                'label' => '🏜️ Sand Desert', 'desc' => 'Sivatagi homok + rozsda, outdoor',
                'bg' => '#15110c', 'accent' => '#ff9d4d', 'text' => '#fff5eb',
                'options' => [
                    'va_color_global_bg'             => '#15110c',
                    'va_color_global_text'           => '#fff5eb',
                    'va_color_global_muted'          => 'rgba(255,245,235,.44)',
                    'va_color_global_accent'         => '#ff9d4d',
                    'va_color_content_bg'            => '#1c1610',
                    'va_color_content_text'          => '#f5eadb',
                    'va_color_content_headings'      => '#fffaf4',
                    'va_color_content_links'         => '#ffbf8b',
                    'va_color_hero_title'            => '#fffaf4',
                    'va_color_hero_sub'              => 'rgba(255,250,244,.65)',
                    'va_color_hero_badge_bg'         => 'rgba(255,157,77,.12)',
                    'va_color_hero_badge_border'     => 'rgba(255,157,77,.4)',
                    'va_color_hero_badge_text'       => '#ffbf8b',
                    'va_color_hero_btn_primary_bg'   => '#ff9d4d',
                    'va_color_hero_btn_primary_hover'=> '#e07a2c',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(255,157,77,.5)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(255,157,77,.07)',
                    'va_color_hero_btn_ghost_border' => 'rgba(255,157,77,.3)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(255,157,77,.14)',
                    'va_color_hero_btn_ghost_text'   => '#ffbf8b',
                    'va_font_global'                 => 'work-sans',
                    'va_font_headings'               => 'bebas-neue',
                ],
            ],

            /* ── 20 ── */
            'clean_saas' => [
                'label' => '🚀 Clean SaaS', 'desc' => 'Modern fehér SaaS – lila/kék gradiens',
                'bg' => '#f8f9fb', 'accent' => '#6366f1', 'text' => '#111827',
                'options' => [
                    'va_color_global_bg'             => '#f8f9fb',
                    'va_color_global_text'           => '#111827',
                    'va_color_global_muted'          => 'rgba(17,24,39,.48)',
                    'va_color_global_accent'         => '#6366f1',
                    'va_color_content_bg'            => '#ffffff',
                    'va_color_content_text'          => '#1f2937',
                    'va_color_content_headings'      => '#0f172a',
                    'va_color_content_links'         => '#6366f1',
                    'va_color_hero_title'            => '#0f172a',
                    'va_color_hero_sub'              => 'rgba(15,23,42,.6)',
                    'va_color_hero_badge_bg'         => 'rgba(99,102,241,.1)',
                    'va_color_hero_badge_border'     => 'rgba(99,102,241,.3)',
                    'va_color_hero_badge_text'       => '#4f46e5',
                    'va_color_hero_btn_primary_bg'   => '#6366f1',
                    'va_color_hero_btn_primary_hover'=> '#4f46e5',
                    'va_color_hero_btn_primary_text' => '#ffffff',
                    'va_color_hero_btn_primary_glow' => 'rgba(99,102,241,.4)',
                    'va_color_hero_btn_ghost_bg'     => 'rgba(99,102,241,.06)',
                    'va_color_hero_btn_ghost_border' => 'rgba(99,102,241,.25)',
                    'va_color_hero_btn_ghost_hover'  => 'rgba(99,102,241,.1)',
                    'va_color_hero_btn_ghost_text'   => '#6366f1',
                    'va_font_global'                 => 'inter',
                    'va_font_headings'               => 'plus-jakarta-sans',
                ],
            ],

        ];
    }

    public static function handle_apply_single_preset(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_apply_single_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets    = self::get_single_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_single_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-single-designer' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            if ( strpos( (string) $key, 'va_single_' ) !== 0 ) {
                continue;
            }
            update_option( (string) $key, $value );
        }

        update_option( 'va_single_preset', $preset_key );
        wp_safe_redirect( add_query_arg( 'va_single_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-single-designer' ) ) );
        exit;
    }

    private static function get_single_presets(): array {
        return [
            'cinematic' => [
                'label' => 'Cinematic Hero',
                'desc'  => 'Nagy cím, hangsúlyos galéria, erős kontraszt.',
                'options' => [
                    'va_single_layout_mode'        => 'split',
                    'va_single_content_max'        => 1380,
                    'va_single_sidebar_width'      => 410,
                    'va_single_layout_gap'         => 28,
                    'va_single_gallery_ratio'      => '16/10',
                    'va_single_gallery_fit'        => 'cover',
                    'va_single_thumb_size'         => 88,
                    'va_single_card_radius'        => 15,
                    'va_single_card_padding'       => 24,
                    'va_single_title_size'         => 46,
                    'va_single_price_size'         => 46,
                    'va_single_meta_size'          => 13,
                    'va_single_btn_height'         => 50,
                    'va_single_share_size'         => 40,
                    'va_single_mobile_title_scale' => 76,
                    'va_single_viewer_bg'          => 'rgba(4,4,4,.97)',
                    'va_single_accent'             => '#ff2a2a',
                    'va_single_glass'              => 'rgba(255,255,255,.07)',
                    'va_single_border'             => 'rgba(255,255,255,.12)',
                ],
            ],
            'compact_trade' => [
                'label' => 'Compact Trade',
                'desc'  => 'Sűrűbb, mobilon gyorsan áttekinthető.',
                'options' => [
                    'va_single_layout_mode'        => 'split',
                    'va_single_content_max'        => 1260,
                    'va_single_sidebar_width'      => 360,
                    'va_single_layout_gap'         => 18,
                    'va_single_gallery_ratio'      => '4/3',
                    'va_single_gallery_fit'        => 'cover',
                    'va_single_thumb_size'         => 76,
                    'va_single_card_radius'        => 12,
                    'va_single_card_padding'       => 18,
                    'va_single_title_size'         => 34,
                    'va_single_price_size'         => 38,
                    'va_single_meta_size'          => 12,
                    'va_single_btn_height'         => 44,
                    'va_single_share_size'         => 36,
                    'va_single_mobile_title_scale' => 82,
                    'va_single_viewer_bg'          => 'rgba(6,6,6,.96)',
                    'va_single_accent'             => '#ff3b30',
                    'va_single_glass'              => 'rgba(255,255,255,.06)',
                    'va_single_border'             => 'rgba(255,255,255,.10)',
                ],
            ],
            'editorial_stack' => [
                'label' => 'Editorial Stack',
                'desc'  => 'Tartalom-first, egyhasábos fókusz.',
                'options' => [
                    'va_single_layout_mode'        => 'stacked',
                    'va_single_content_max'        => 1120,
                    'va_single_sidebar_width'      => 380,
                    'va_single_layout_gap'         => 20,
                    'va_single_gallery_ratio'      => '16/9',
                    'va_single_gallery_fit'        => 'contain',
                    'va_single_thumb_size'         => 80,
                    'va_single_card_radius'        => 13,
                    'va_single_card_padding'       => 22,
                    'va_single_title_size'         => 42,
                    'va_single_price_size'         => 40,
                    'va_single_meta_size'          => 13,
                    'va_single_btn_height'         => 46,
                    'va_single_share_size'         => 38,
                    'va_single_mobile_title_scale' => 86,
                    'va_single_viewer_bg'          => 'rgba(3,3,3,.95)',
                    'va_single_accent'             => '#ff1f1f',
                    'va_single_glass'              => 'rgba(255,255,255,.08)',
                    'va_single_border'             => 'rgba(255,255,255,.13)',
                ],
            ],
        ];
    }

    private static function get_adminpanel_presets(): array {
        return [

            /* ── 1 ── */
            'dark_crimson' => [
                'label' => '🔴 Dark Crimson', 'desc' => 'Alapértelmezett – sötét + erős piros',
                'bg' => '#070709', 'bg2' => '#0d0d11', 'accent' => '#ff2020', 'accent2' => '#ff5050',
                'options' => [
                    'va_ap_color_bg' => '#070709', 'va_ap_color_bg2' => '#0d0d11', 'va_ap_color_bg3' => '#111118', 'va_ap_color_bg4' => '#161620',
                    'va_ap_color_text' => '#e8e8f0', 'va_ap_color_muted' => 'rgba(255,255,255,.45)',
                    'va_ap_color_accent' => '#ff2020', 'va_ap_color_accent2' => '#ff5050',
                    'va_ap_color_border' => 'rgba(255,255,255,.07)', 'va_ap_color_border2' => 'rgba(255,255,255,.12)',
                ],
            ],

            /* ── 2 ── */
            'arctic_white' => [
                'label' => '⚪ Arctic White', 'desc' => 'Orvosi tiszta fehér, éles kontraszt',
                'bg' => '#f0f4f8', 'bg2' => '#ffffff', 'accent' => '#0066ff', 'accent2' => '#3385ff',
                'options' => [
                    'va_ap_color_bg' => '#f0f4f8', 'va_ap_color_bg2' => '#ffffff', 'va_ap_color_bg3' => '#e8edf4', 'va_ap_color_bg4' => '#dde3ec',
                    'va_ap_color_text' => '#0d1117', 'va_ap_color_muted' => 'rgba(13,17,23,.5)',
                    'va_ap_color_accent' => '#0066ff', 'va_ap_color_accent2' => '#3385ff',
                    'va_ap_color_border' => 'rgba(0,0,0,.09)', 'va_ap_color_border2' => 'rgba(0,0,0,.16)',
                ],
            ],

            /* ── 3 ── */
            'clinical_mint' => [
                'label' => '🏥 Clinical Mint', 'desc' => 'Orvosi letisztult – fehér + mentazöld',
                'bg' => '#f5faf8', 'bg2' => '#ffffff', 'accent' => '#00a878', 'accent2' => '#00cc92',
                'options' => [
                    'va_ap_color_bg' => '#f5faf8', 'va_ap_color_bg2' => '#ffffff', 'va_ap_color_bg3' => '#eaf5f1', 'va_ap_color_bg4' => '#ddeee9',
                    'va_ap_color_text' => '#0a1e17', 'va_ap_color_muted' => 'rgba(10,30,23,.48)',
                    'va_ap_color_accent' => '#00a878', 'va_ap_color_accent2' => '#00cc92',
                    'va_ap_color_border' => 'rgba(0,168,120,.1)', 'va_ap_color_border2' => 'rgba(0,168,120,.2)',
                ],
            ],

            /* ── 4 ── */
            'speed_red' => [
                'label' => '🏎 Speed Red', 'desc' => 'Racing – fekete + elektromos piros glow',
                'bg' => '#060606', 'bg2' => '#0f0000', 'accent' => '#ff0022', 'accent2' => '#ff4455',
                'options' => [
                    'va_ap_color_bg' => '#060606', 'va_ap_color_bg2' => '#0f0000', 'va_ap_color_bg3' => '#180000', 'va_ap_color_bg4' => '#220005',
                    'va_ap_color_text' => '#fff0f2', 'va_ap_color_muted' => 'rgba(255,240,242,.45)',
                    'va_ap_color_accent' => '#ff0022', 'va_ap_color_accent2' => '#ff4455',
                    'va_ap_color_border' => 'rgba(255,0,34,.1)', 'va_ap_color_border2' => 'rgba(255,0,34,.2)',
                ],
            ],

            /* ── 5 ── */
            'hunter_camo' => [
                'label' => '🦌 Hunter Camo', 'desc' => 'Vadászos terepszín – erdei zöld + barna',
                'bg' => '#0d110a', 'bg2' => '#151c10', 'accent' => '#7aad2f', 'accent2' => '#a3d44e',
                'options' => [
                    'va_ap_color_bg' => '#0d110a', 'va_ap_color_bg2' => '#151c10', 'va_ap_color_bg3' => '#1d2815', 'va_ap_color_bg4' => '#25341c',
                    'va_ap_color_text' => '#e8f2d8', 'va_ap_color_muted' => 'rgba(232,242,216,.44)',
                    'va_ap_color_accent' => '#7aad2f', 'va_ap_color_accent2' => '#a3d44e',
                    'va_ap_color_border' => 'rgba(122,173,47,.1)', 'va_ap_color_border2' => 'rgba(122,173,47,.2)',
                ],
            ],

            /* ── 6 ── */
            'forest_command' => [
                'label' => '🌲 Forest Command', 'desc' => 'Mély erdőzöld, vadász parancsnok',
                'bg' => '#060f0b', 'bg2' => '#0b1910', 'accent' => '#25d468', 'accent2' => '#5de891',
                'options' => [
                    'va_ap_color_bg' => '#060f0b', 'va_ap_color_bg2' => '#0b1910', 'va_ap_color_bg3' => '#0f2018', 'va_ap_color_bg4' => '#142a20',
                    'va_ap_color_text' => '#d4f5e3', 'va_ap_color_muted' => 'rgba(212,245,227,.45)',
                    'va_ap_color_accent' => '#25d468', 'va_ap_color_accent2' => '#5de891',
                    'va_ap_color_border' => 'rgba(37,212,104,.07)', 'va_ap_color_border2' => 'rgba(37,212,104,.14)',
                ],
            ],

            /* ── 7 ── */
            'neon_cyber' => [
                'label' => '⚡ Neon Cyber', 'desc' => 'Cyberpunk 2026 – fekete + neon zöld',
                'bg' => '#030a06', 'bg2' => '#060f08', 'accent' => '#00ff88', 'accent2' => '#33ffaa',
                'options' => [
                    'va_ap_color_bg' => '#030a06', 'va_ap_color_bg2' => '#060f08', 'va_ap_color_bg3' => '#0a1a0e', 'va_ap_color_bg4' => '#102214',
                    'va_ap_color_text' => '#ccffe8', 'va_ap_color_muted' => 'rgba(204,255,232,.4)',
                    'va_ap_color_accent' => '#00ff88', 'va_ap_color_accent2' => '#33ffaa',
                    'va_ap_color_border' => 'rgba(0,255,136,.1)', 'va_ap_color_border2' => 'rgba(0,255,136,.22)',
                ],
            ],

            /* ── 8 ── */
            'midnight_navy' => [
                'label' => '🌊 Midnight Navy', 'desc' => 'Mélytenger kék + prémium neon',
                'bg' => '#07090f', 'bg2' => '#0b0f1a', 'accent' => '#3b8af7', 'accent2' => '#6aadff',
                'options' => [
                    'va_ap_color_bg' => '#07090f', 'va_ap_color_bg2' => '#0b0f1a', 'va_ap_color_bg3' => '#101520', 'va_ap_color_bg4' => '#151c2b',
                    'va_ap_color_text' => '#dce8ff', 'va_ap_color_muted' => 'rgba(220,232,255,.45)',
                    'va_ap_color_accent' => '#3b8af7', 'va_ap_color_accent2' => '#6aadff',
                    'va_ap_color_border' => 'rgba(59,138,247,.08)', 'va_ap_color_border2' => 'rgba(59,138,247,.15)',
                ],
            ],

            /* ── 9 ── */
            'ocean_depth' => [
                'label' => '🐋 Ocean Depth', 'desc' => 'Mélyvíz – teal + aqua neon',
                'bg' => '#040e12', 'bg2' => '#08181e', 'accent' => '#00c8d4', 'accent2' => '#33dde6',
                'options' => [
                    'va_ap_color_bg' => '#040e12', 'va_ap_color_bg2' => '#08181e', 'va_ap_color_bg3' => '#0c2028', 'va_ap_color_bg4' => '#122830',
                    'va_ap_color_text' => '#c8f4f8', 'va_ap_color_muted' => 'rgba(200,244,248,.42)',
                    'va_ap_color_accent' => '#00c8d4', 'va_ap_color_accent2' => '#33dde6',
                    'va_ap_color_border' => 'rgba(0,200,212,.09)', 'va_ap_color_border2' => 'rgba(0,200,212,.18)',
                ],
            ],

            /* ── 10 ── */
            'obsidian_gold' => [
                'label' => '👑 Obsidian Gold', 'desc' => 'Obszidián fekete + nemes arany',
                'bg' => '#080807', 'bg2' => '#10100d', 'accent' => '#e8b840', 'accent2' => '#f5cf6a',
                'options' => [
                    'va_ap_color_bg' => '#080807', 'va_ap_color_bg2' => '#10100d', 'va_ap_color_bg3' => '#161612', 'va_ap_color_bg4' => '#1e1d17',
                    'va_ap_color_text' => '#f5f0e0', 'va_ap_color_muted' => 'rgba(245,240,224,.45)',
                    'va_ap_color_accent' => '#e8b840', 'va_ap_color_accent2' => '#f5cf6a',
                    'va_ap_color_border' => 'rgba(232,184,64,.07)', 'va_ap_color_border2' => 'rgba(232,184,64,.14)',
                ],
            ],

            /* ── 11 ── */
            'copper_dark' => [
                'label' => '🔶 Copper Dark', 'desc' => 'Sötétbarna + réz – prémium outdoor',
                'bg' => '#0a0806', 'bg2' => '#140f0a', 'accent' => '#c97d3e', 'accent2' => '#e0a265',
                'options' => [
                    'va_ap_color_bg' => '#0a0806', 'va_ap_color_bg2' => '#140f0a', 'va_ap_color_bg3' => '#1c160e', 'va_ap_color_bg4' => '#241c13',
                    'va_ap_color_text' => '#f2e8d8', 'va_ap_color_muted' => 'rgba(242,232,216,.44)',
                    'va_ap_color_accent' => '#c97d3e', 'va_ap_color_accent2' => '#e0a265',
                    'va_ap_color_border' => 'rgba(201,125,62,.08)', 'va_ap_color_border2' => 'rgba(201,125,62,.14)',
                ],
            ],

            /* ── 12 ── */
            'steel_ember' => [
                'label' => '🔥 Steel Ember', 'desc' => 'Acélos szürke + izzó narancs',
                'bg' => '#0d0e10', 'bg2' => '#111317', 'accent' => '#ff7a22', 'accent2' => '#ffaa66',
                'options' => [
                    'va_ap_color_bg' => '#0d0e10', 'va_ap_color_bg2' => '#111317', 'va_ap_color_bg3' => '#16181e', 'va_ap_color_bg4' => '#1c1e25',
                    'va_ap_color_text' => '#f0f2f5', 'va_ap_color_muted' => 'rgba(240,242,245,.45)',
                    'va_ap_color_accent' => '#ff7a22', 'va_ap_color_accent2' => '#ffaa66',
                    'va_ap_color_border' => 'rgba(255,122,34,.08)', 'va_ap_color_border2' => 'rgba(255,122,34,.15)',
                ],
            ],

            /* ── 13 ── */
            'graphite_purple' => [
                'label' => '🔮 Graphite Purple', 'desc' => 'Grafit + lila, tech-SaaS 2026',
                'bg' => '#090910', 'bg2' => '#0f0f19', 'accent' => '#8b5cf6', 'accent2' => '#a78bfa',
                'options' => [
                    'va_ap_color_bg' => '#090910', 'va_ap_color_bg2' => '#0f0f19', 'va_ap_color_bg3' => '#141420', 'va_ap_color_bg4' => '#1a1a28',
                    'va_ap_color_text' => '#e4e4f0', 'va_ap_color_muted' => 'rgba(228,228,240,.42)',
                    'va_ap_color_accent' => '#8b5cf6', 'va_ap_color_accent2' => '#a78bfa',
                    'va_ap_color_border' => 'rgba(139,92,246,.08)', 'va_ap_color_border2' => 'rgba(139,92,246,.16)',
                ],
            ],

            /* ── 14 ── */
            'royal_plum' => [
                'label' => '🍷 Royal Plum', 'desc' => 'Sötét lila-bordó, exkluzív',
                'bg' => '#12091a', 'bg2' => '#1a0f24', 'accent' => '#c75cff', 'accent2' => '#da98ff',
                'options' => [
                    'va_ap_color_bg' => '#12091a', 'va_ap_color_bg2' => '#1a0f24', 'va_ap_color_bg3' => '#22132e', 'va_ap_color_bg4' => '#2b1a38',
                    'va_ap_color_text' => '#f8f0ff', 'va_ap_color_muted' => 'rgba(248,240,255,.44)',
                    'va_ap_color_accent' => '#c75cff', 'va_ap_color_accent2' => '#da98ff',
                    'va_ap_color_border' => 'rgba(199,92,255,.08)', 'va_ap_color_border2' => 'rgba(199,92,255,.16)',
                ],
            ],

            /* ── 15 ── */
            'aurora_nord' => [
                'label' => '🌌 Aurora Nord', 'desc' => 'Északi fény – nordic kék + zöld',
                'bg' => '#0a0e14', 'bg2' => '#111820', 'accent' => '#88c0d0', 'accent2' => '#a3be8c',
                'options' => [
                    'va_ap_color_bg' => '#0a0e14', 'va_ap_color_bg2' => '#111820', 'va_ap_color_bg3' => '#17222e', 'va_ap_color_bg4' => '#1d2c3c',
                    'va_ap_color_text' => '#d8dee9', 'va_ap_color_muted' => 'rgba(216,222,233,.44)',
                    'va_ap_color_accent' => '#88c0d0', 'va_ap_color_accent2' => '#a3be8c',
                    'va_ap_color_border' => 'rgba(136,192,208,.09)', 'va_ap_color_border2' => 'rgba(136,192,208,.18)',
                ],
            ],

            /* ── 16 ── */
            'carbon_steel' => [
                'label' => '⚙️ Carbon Steel', 'desc' => 'Tiszta acél szürke, minimál 2026',
                'bg' => '#0a0a0a', 'bg2' => '#111111', 'accent' => '#64748b', 'accent2' => '#94a3b8',
                'options' => [
                    'va_ap_color_bg' => '#0a0a0a', 'va_ap_color_bg2' => '#111111', 'va_ap_color_bg3' => '#171717', 'va_ap_color_bg4' => '#1e1e1e',
                    'va_ap_color_text' => '#e0e0e0', 'va_ap_color_muted' => 'rgba(224,224,224,.45)',
                    'va_ap_color_accent' => '#64748b', 'va_ap_color_accent2' => '#94a3b8',
                    'va_ap_color_border' => 'rgba(255,255,255,.06)', 'va_ap_color_border2' => 'rgba(255,255,255,.11)',
                ],
            ],

            /* ── 17 ── */
            'ink_paper' => [
                'label' => '📰 Ink & Paper', 'desc' => 'Editorial újság – fehér + tinta fekete',
                'bg' => '#fafafa', 'bg2' => '#ffffff', 'accent' => '#1a1a1a', 'accent2' => '#333333',
                'options' => [
                    'va_ap_color_bg' => '#fafafa', 'va_ap_color_bg2' => '#ffffff', 'va_ap_color_bg3' => '#f2f2f2', 'va_ap_color_bg4' => '#e8e8e8',
                    'va_ap_color_text' => '#111111', 'va_ap_color_muted' => 'rgba(17,17,17,.5)',
                    'va_ap_color_accent' => '#1a1a1a', 'va_ap_color_accent2' => '#444444',
                    'va_ap_color_border' => 'rgba(0,0,0,.1)', 'va_ap_color_border2' => 'rgba(0,0,0,.18)',
                ],
            ],

            /* ── 18 ── */
            'sunset_blaze' => [
                'label' => '🌅 Sunset Blaze', 'desc' => 'Napnyugta – meleg korall + arany',
                'bg' => '#0f0a06', 'bg2' => '#1a1008', 'accent' => '#ff6b35', 'accent2' => '#ffa040',
                'options' => [
                    'va_ap_color_bg' => '#0f0a06', 'va_ap_color_bg2' => '#1a1008', 'va_ap_color_bg3' => '#24160c', 'va_ap_color_bg4' => '#2e1c12',
                    'va_ap_color_text' => '#fff0e8', 'va_ap_color_muted' => 'rgba(255,240,232,.44)',
                    'va_ap_color_accent' => '#ff6b35', 'va_ap_color_accent2' => '#ffa040',
                    'va_ap_color_border' => 'rgba(255,107,53,.1)', 'va_ap_color_border2' => 'rgba(255,107,53,.2)',
                ],
            ],

            /* ── 19 ── */
            'sakura_pro' => [
                'label' => '🌸 Sakura Pro', 'desc' => 'Japán prémium – halvány rózsa + fehér',
                'bg' => '#fdf7f9', 'bg2' => '#ffffff', 'accent' => '#e8558a', 'accent2' => '#f07aaa',
                'options' => [
                    'va_ap_color_bg' => '#fdf7f9', 'va_ap_color_bg2' => '#ffffff', 'va_ap_color_bg3' => '#f8eef3', 'va_ap_color_bg4' => '#f0e0e8',
                    'va_ap_color_text' => '#1f0a14', 'va_ap_color_muted' => 'rgba(31,10,20,.48)',
                    'va_ap_color_accent' => '#e8558a', 'va_ap_color_accent2' => '#f07aaa',
                    'va_ap_color_border' => 'rgba(232,85,138,.1)', 'va_ap_color_border2' => 'rgba(232,85,138,.2)',
                ],
            ],

            /* ── 20 ── */
            'matrix_dark' => [
                'label' => '💻 Matrix Dark', 'desc' => 'Hacker zöld – mély fekete + lime',
                'bg' => '#020802', 'bg2' => '#050f05', 'accent' => '#39ff14', 'accent2' => '#66ff44',
                'options' => [
                    'va_ap_color_bg' => '#020802', 'va_ap_color_bg2' => '#050f05', 'va_ap_color_bg3' => '#091509', 'va_ap_color_bg4' => '#0f1f0f',
                    'va_ap_color_text' => '#ccffcc', 'va_ap_color_muted' => 'rgba(204,255,204,.4)',
                    'va_ap_color_accent' => '#39ff14', 'va_ap_color_accent2' => '#66ff44',
                    'va_ap_color_border' => 'rgba(57,255,20,.1)', 'va_ap_color_border2' => 'rgba(57,255,20,.2)',
                ],
            ],

        ];
    }

    private static function get_header_footer_presets(): array {
        return [
            'carbon_red' => [
                'label' => 'Carbon Red',
                'desc'  => 'Fekete karbon alap, tiszta piros glow.',
                'options' => [
                    'va_hf_header_color_base' => '#050505',
                    'va_hf_header_color_alt' => '#1a0a0a',
                    'va_hf_header_border_color' => '#ff2a2a',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.74)',
                    'va_hf_header_glow_color' => 'rgba(255,0,0,.24)',
                    'va_hf_header_search_glow_color' => 'rgba(255,0,0,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,0,0,.52)',
                    'va_hf_footer_color_base' => '#090909',
                    'va_hf_footer_color_alt' => '#180808',
                    'va_hf_footer_border_color' => '#ff2a2a',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.38)',
                    'va_hf_footer_glow_color' => 'rgba(255,0,0,.14)',
                    'va_hf_footer_link_hover_color' => '#ffffff',
                    'va_color_header_text' => '#ffffff',
                    'va_color_footer_text' => 'rgba(255,255,255,.76)',
                    'va_color_footer_links' => '#ff4d4d',
                ],
            ],
            'steel_ember' => [
                'label' => 'Steel Ember',
                'desc'  => 'Acélos szürke + izzó narancs hangsúly.',
                'options' => [
                    'va_hf_header_color_base' => '#111317',
                    'va_hf_header_color_alt' => '#25170f',
                    'va_hf_header_border_color' => '#ff7a22',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.70)',
                    'va_hf_header_glow_color' => 'rgba(255,122,34,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(255,122,34,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,122,34,.42)',
                    'va_hf_footer_color_base' => '#0d0f13',
                    'va_hf_footer_color_alt' => '#20140f',
                    'va_hf_footer_border_color' => '#ff7a22',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(255,122,34,.12)',
                    'va_hf_footer_link_hover_color' => '#ffd0b0',
                    'va_color_header_text' => '#f5f7fa',
                    'va_color_footer_text' => 'rgba(245,247,250,.76)',
                    'va_color_footer_links' => '#ff9a57',
                ],
            ],
            'night_copper' => [
                'label' => 'Night Copper',
                'desc'  => 'Sotet barna-femes retegzes, premium hangulat.',
                'options' => [
                    'va_hf_header_color_base' => '#0d0a08',
                    'va_hf_header_color_alt' => '#2a1b12',
                    'va_hf_header_border_color' => '#c67a3d',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(198,122,61,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(198,122,61,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(198,122,61,.40)',
                    'va_hf_footer_color_base' => '#0e0906',
                    'va_hf_footer_color_alt' => '#20140d',
                    'va_hf_footer_border_color' => '#c67a3d',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.37)',
                    'va_hf_footer_glow_color' => 'rgba(198,122,61,.12)',
                    'va_hf_footer_link_hover_color' => '#ffe2cc',
                    'va_color_header_text' => '#fff5eb',
                    'va_color_footer_text' => 'rgba(255,245,235,.74)',
                    'va_color_footer_links' => '#e4a06d',
                ],
            ],
            'midnight_ice' => [
                'label' => 'Midnight Ice',
                'desc'  => 'Hideg kek-szurke modern, minimal premium.',
                'options' => [
                    'va_hf_header_color_base' => '#080d16',
                    'va_hf_header_color_alt' => '#122339',
                    'va_hf_header_border_color' => '#57b0ff',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(87,176,255,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(87,176,255,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(87,176,255,.40)',
                    'va_hf_footer_color_base' => '#070b12',
                    'va_hf_footer_color_alt' => '#102034',
                    'va_hf_footer_border_color' => '#57b0ff',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.38)',
                    'va_hf_footer_glow_color' => 'rgba(87,176,255,.12)',
                    'va_hf_footer_link_hover_color' => '#d8efff',
                    'va_color_header_text' => '#eef7ff',
                    'va_color_footer_text' => 'rgba(238,247,255,.74)',
                    'va_color_footer_links' => '#89c8ff',
                ],
            ],
            'forest_glass' => [
                'label' => 'Forest Glass',
                'desc'  => 'Mely zold uveges panel erzes.',
                'options' => [
                    'va_hf_header_color_base' => '#06100b',
                    'va_hf_header_color_alt' => '#0f2b1f',
                    'va_hf_header_border_color' => '#36d487',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.70)',
                    'va_hf_header_glow_color' => 'rgba(54,212,135,.18)',
                    'va_hf_header_search_glow_color' => 'rgba(54,212,135,.16)',
                    'va_hf_header_btn_glow_color' => 'rgba(54,212,135,.36)',
                    'va_hf_footer_color_base' => '#060f0a',
                    'va_hf_footer_color_alt' => '#10261c',
                    'va_hf_footer_border_color' => '#36d487',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(54,212,135,.11)',
                    'va_hf_footer_link_hover_color' => '#cbffe5',
                    'va_color_header_text' => '#effff7',
                    'va_color_footer_text' => 'rgba(239,255,247,.74)',
                    'va_color_footer_links' => '#7decb7',
                ],
            ],
            'obsidian_gold' => [
                'label' => 'Obsidian Gold',
                'desc'  => 'Fekete + arany, exkluziv karakter.',
                'options' => [
                    'va_hf_header_color_base' => '#090909',
                    'va_hf_header_color_alt' => '#1d1705',
                    'va_hf_header_border_color' => '#e5b843',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.74)',
                    'va_hf_header_glow_color' => 'rgba(229,184,67,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(229,184,67,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(229,184,67,.40)',
                    'va_hf_footer_color_base' => '#080808',
                    'va_hf_footer_color_alt' => '#1b1505',
                    'va_hf_footer_border_color' => '#e5b843',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.38)',
                    'va_hf_footer_glow_color' => 'rgba(229,184,67,.12)',
                    'va_hf_footer_link_hover_color' => '#fff2cb',
                    'va_color_header_text' => '#fff9e8',
                    'va_color_footer_text' => 'rgba(255,249,232,.74)',
                    'va_color_footer_links' => '#f0cf76',
                ],
            ],
            'graphite_rose' => [
                'label' => 'Graphite Rose',
                'desc'  => 'Grafit alap finom rozsas accenttel.',
                'options' => [
                    'va_hf_header_color_base' => '#0f1013',
                    'va_hf_header_color_alt' => '#261521',
                    'va_hf_header_border_color' => '#ff6f91',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(255,111,145,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(255,111,145,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,111,145,.38)',
                    'va_hf_footer_color_base' => '#0d0e10',
                    'va_hf_footer_color_alt' => '#21131b',
                    'va_hf_footer_border_color' => '#ff6f91',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(255,111,145,.11)',
                    'va_hf_footer_link_hover_color' => '#ffdbe5',
                    'va_color_header_text' => '#fff0f5',
                    'va_color_footer_text' => 'rgba(255,240,245,.74)',
                    'va_color_footer_links' => '#ff97b0',
                ],
            ],
            'arctic_mint' => [
                'label' => 'Arctic Mint',
                'desc'  => 'Hideg tiszta menta vilagitas, modern tech vibe.',
                'options' => [
                    'va_hf_header_color_base' => '#071015',
                    'va_hf_header_color_alt' => '#103036',
                    'va_hf_header_border_color' => '#4ce8d2',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.70)',
                    'va_hf_header_glow_color' => 'rgba(76,232,210,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(76,232,210,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(76,232,210,.38)',
                    'va_hf_footer_color_base' => '#060f12',
                    'va_hf_footer_color_alt' => '#0f282d',
                    'va_hf_footer_border_color' => '#4ce8d2',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(76,232,210,.11)',
                    'va_hf_footer_link_hover_color' => '#d8fff8',
                    'va_color_header_text' => '#eefffb',
                    'va_color_footer_text' => 'rgba(238,255,251,.74)',
                    'va_color_footer_links' => '#8bf4e7',
                ],
            ],
            'royal_plum' => [
                'label' => 'Royal Plum',
                'desc'  => 'Kiralyi sotet lila-bordo, eros kontraszttal.',
                'options' => [
                    'va_hf_header_color_base' => '#12091a',
                    'va_hf_header_color_alt' => '#2b1020',
                    'va_hf_header_border_color' => '#c75cff',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.73)',
                    'va_hf_header_glow_color' => 'rgba(199,92,255,.22)',
                    'va_hf_header_search_glow_color' => 'rgba(199,92,255,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(199,92,255,.40)',
                    'va_hf_footer_color_base' => '#100717',
                    'va_hf_footer_color_alt' => '#230d1a',
                    'va_hf_footer_border_color' => '#c75cff',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.37)',
                    'va_hf_footer_glow_color' => 'rgba(199,92,255,.12)',
                    'va_hf_footer_link_hover_color' => '#f1dcff',
                    'va_color_header_text' => '#f8f0ff',
                    'va_color_footer_text' => 'rgba(248,240,255,.74)',
                    'va_color_footer_links' => '#da98ff',
                ],
            ],
            'desert_sand' => [
                'label' => 'Desert Sand',
                'desc'  => 'Meleg homok + rozsda modern outdoor erzet.',
                'options' => [
                    'va_hf_header_color_base' => '#15110c',
                    'va_hf_header_color_alt' => '#3a2312',
                    'va_hf_header_border_color' => '#ff9d4d',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(255,157,77,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(255,157,77,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,157,77,.40)',
                    'va_hf_footer_color_base' => '#120e0a',
                    'va_hf_footer_color_alt' => '#2f1d10',
                    'va_hf_footer_border_color' => '#ff9d4d',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(255,157,77,.12)',
                    'va_hf_footer_link_hover_color' => '#ffe7d2',
                    'va_color_header_text' => '#fff5eb',
                    'va_color_footer_text' => 'rgba(255,245,235,.74)',
                    'va_color_footer_links' => '#ffbf8b',
                ],
            ],
        ];
    }

    private static function get_export_taxonomies(): array {
        $out = [];
        $taxonomies = [ 'va_category', 'va_county', 'va_condition' ];
        foreach ( $taxonomies as $taxonomy ) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);
            if ( is_wp_error( $terms ) ) {
                continue;
            }

            $out[ $taxonomy ] = [];
            foreach ( $terms as $term ) {
                $parent_slug = '';
                if ( $term->parent ) {
                    $parent = get_term( (int) $term->parent, $taxonomy );
                    if ( $parent && ! is_wp_error( $parent ) ) {
                        $parent_slug = (string) $parent->slug;
                    }
                }

                $out[ $taxonomy ][] = [
                    'slug'        => (string) $term->slug,
                    'name'        => (string) $term->name,
                    'description' => (string) $term->description,
                    'parent_slug' => $parent_slug,
                ];
            }
        }

        return $out;
    }

    private static function get_export_pages(): array {
        $slugs = [
            'kategoria',
            'kapcsolat',
            'va-hirdetes-kereses',
            'va-hirdetes-feladas',
            'va-bejelentkezes',
            'va-regisztracio',
            'va-fiok',
            'va-aukciok',
            'va-kredit-vasarlas',
            'aszf',
            'adatvedelmi-nyilatkozat',
            'sugo',
        ];

        $pages = [];
        foreach ( $slugs as $slug ) {
            $page = get_page_by_path( $slug );
            if ( ! $page ) {
                continue;
            }
            $pages[] = [
                'slug'    => (string) $page->post_name,
                'title'   => (string) $page->post_title,
                'status'  => (string) $page->post_status,
                'content' => (string) $page->post_content,
                'excerpt' => (string) $page->post_excerpt,
            ];
        }

        return $pages;
    }

    private static function import_taxonomies( array $tax_data ): int {
        $processed = 0;

        foreach ( $tax_data as $taxonomy => $terms ) {
            if ( ! taxonomy_exists( (string) $taxonomy ) || ! is_array( $terms ) ) {
                continue;
            }

            // 1. kör: létrehozás/frissítés parent nélkül.
            foreach ( $terms as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }
                $slug = sanitize_title( (string) ( $item['slug'] ?? '' ) );
                $name = sanitize_text_field( (string) ( $item['name'] ?? '' ) );
                $desc = sanitize_textarea_field( (string) ( $item['description'] ?? '' ) );
                if ( $slug === '' || $name === '' ) {
                    continue;
                }

                $existing = get_term_by( 'slug', $slug, (string) $taxonomy );
                if ( $existing && ! is_wp_error( $existing ) ) {
                    wp_update_term( (int) $existing->term_id, (string) $taxonomy, [
                        'name'        => $name,
                        'description' => $desc,
                        'slug'        => $slug,
                    ] );
                } else {
                    wp_insert_term( $name, (string) $taxonomy, [
                        'description' => $desc,
                        'slug'        => $slug,
                    ] );
                }
                $processed++;
            }

            // 2. kör: parent kapcsolatok.
            foreach ( $terms as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }
                $slug = sanitize_title( (string) ( $item['slug'] ?? '' ) );
                $parent_slug = sanitize_title( (string) ( $item['parent_slug'] ?? '' ) );
                if ( $slug === '' || $parent_slug === '' ) {
                    continue;
                }

                $term = get_term_by( 'slug', $slug, (string) $taxonomy );
                $parent = get_term_by( 'slug', $parent_slug, (string) $taxonomy );
                if ( ! $term || is_wp_error( $term ) || ! $parent || is_wp_error( $parent ) ) {
                    continue;
                }

                wp_update_term( (int) $term->term_id, (string) $taxonomy, [ 'parent' => (int) $parent->term_id ] );
            }
        }

        return $processed;
    }

    private static function import_pages( array $pages ): int {
        $processed = 0;
        foreach ( $pages as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $slug    = sanitize_title( (string) ( $row['slug'] ?? '' ) );
            $title   = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
            $status  = sanitize_key( (string) ( $row['status'] ?? 'publish' ) );
            $content = wp_kses_post( (string) ( $row['content'] ?? '' ) );
            $excerpt = sanitize_textarea_field( (string) ( $row['excerpt'] ?? '' ) );

            if ( $slug === '' || $title === '' ) {
                continue;
            }
            if ( ! in_array( $status, [ 'publish', 'draft', 'private', 'pending' ], true ) ) {
                $status = 'publish';
            }

            $existing = get_page_by_path( $slug );
            $postarr = [
                'post_type'    => 'page',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => $status,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
            ];

            if ( $existing ) {
                $postarr['ID'] = (int) $existing->ID;
                wp_update_post( $postarr );
            } else {
                wp_insert_post( $postarr );
            }
            $processed++;
        }

        return $processed;
    }

    /* ══ Helper mezők ═════════════════════════════════════ */
    private static function field_text( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"text\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text\"></td></tr>";
    }

    private static function field_email( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"email\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text\"></td></tr>";
    }

    public static function sanitize_legal_url( $value ): string {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( $value === '' ) {
            return '';
        }

        // Absolute URL: only http/https is allowed.
        if ( preg_match( '#^https?://#i', $value ) ) {
            return (string) esc_url_raw( $value, [ 'http', 'https' ] );
        }

        // Relative URL: keep path/query/hash, normalize to leading slash.
        $value = ltrim( $value, '/' );
        return '/' . $value;
    }

    public static function sanitize_legal_html( $value ): string {
        return wp_kses_post( (string) $value );
    }

    private static function get_legal_page_map(): array {
        return [
            [ 'title' => 'Adatvédelem', 'slug' => 'adatvedelmi-nyilatkozat', 'url_opt' => 'va_legal_url_adatvedelem', 'cnt_opt' => 'va_legal_content_adatvedelem' ],
            [ 'title' => 'ÁSZF', 'slug' => 'aszf', 'url_opt' => 'va_legal_url_aszf', 'cnt_opt' => 'va_legal_content_aszf' ],
            [ 'title' => 'Impresszum', 'slug' => 'impresszum', 'url_opt' => 'va_legal_url_impresszum', 'cnt_opt' => 'va_legal_content_impresszum' ],
            [ 'title' => 'Etika és Üzleti Magatartási Kódex', 'slug' => 'etika-es-uzleti-magatartasi-kodex', 'url_opt' => 'va_legal_url_etika', 'cnt_opt' => 'va_legal_content_etika' ],
            [ 'title' => 'Sütik', 'slug' => 'sutik', 'url_opt' => 'va_legal_url_sutik', 'cnt_opt' => 'va_legal_content_sutik' ],
            [ 'title' => 'GDPR Adatkezelési Tájékoztató', 'slug' => 'gdpr-adatkezelesi-tajekoztato', 'url_opt' => 'va_legal_url_gdpr', 'cnt_opt' => 'va_legal_content_gdpr' ],
            [ 'title' => 'Fenntartható Fejlődés Irányelve', 'slug' => 'fenntarthato-fejlodes-iranyelve', 'url_opt' => 'va_legal_url_fenntarthato', 'cnt_opt' => 'va_legal_content_fenntarthato' ],
        ];
    }

    private static function sync_legal_pages_from_options(): void {
        foreach ( self::get_legal_page_map() as $item ) {
            $title       = (string) $item['title'];
            $slug        = sanitize_title( (string) $item['slug'] );
            $url_option  = (string) $item['url_opt'];
            $content_opt = (string) $item['cnt_opt'];
            $content     = wp_kses_post( (string) get_option( $content_opt, '' ) );

            if ( trim( wp_strip_all_tags( $content ) ) === '' ) {
                update_option( $url_option, '' );
                continue;
            }

            $existing = get_page_by_path( $slug );
            $postarr = [
                'post_type'    => 'page',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_content' => $content,
            ];

            if ( $existing ) {
                $postarr['ID'] = (int) $existing->ID;
                wp_update_post( $postarr );
            } else {
                wp_insert_post( $postarr );
            }

            update_option( $url_option, '/' . ltrim( $slug, '/' ) );
        }
    }

    private static function field_url( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"url\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text code";
        echo " placeholder=\"https://.../video.mp4\"></td></tr>";
    }

    private static function field_video( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td>";
        echo "<div class=\"va-media-field\">";
        echo "<input type=\"url\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text code va-media-input\" placeholder=\"https://.../video.mp4\">";
        echo "<button type=\"button\" class=\"button va-media-video-btn\" data-target=\"{$key}\">Tallózás</button>";
        echo "<button type=\"button\" class=\"button va-media-video-clear\" data-target=\"{$key}\">Törlés</button>";
        echo "</div>";
        echo "</td></tr>";
    }

    private static function field_media( string $key, string $label ): void {
        $val     = esc_attr( (string) self::get_display_option( $key, '' ) );
        $img_id  = $key . '_img';
        $img_vis = $val !== '' ? '' : ' style="display:none"';
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td>";
        echo "<div class=\"va-media-field\">";
        echo "<input type=\"url\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text code va-media-input\" placeholder=\"https://.../logo.png\">";
        echo "<button type=\"button\" class=\"button va-media-btn\" data-target=\"{$key}\" data-preview=\"{$img_id}\">Tallózás</button>";
        echo "<button type=\"button\" class=\"button va-media-clear\" data-target=\"{$key}\" data-preview=\"{$img_id}\">Törlés</button>";
        echo "</div>";
        echo "<div class=\"va-media-preview-wrap\"><img id=\"{$img_id}\" src=\"{$val}\" alt=\"\" class=\"va-media-preview\"{$img_vis}></div>";
        echo "</td></tr>";
    }

    private static function spin_wrap( string $key, string $input_html ): string {
        $up   = "var i=this.closest('.va-num-wrap').querySelector('input');var s=parseFloat(i.step)||1;var v=parseFloat(i.value)||0;var mx=i.max!==''?parseFloat(i.max):9999;i.value=Math.min(mx,Math.round((v+s)*10000)/10000);i.dispatchEvent(new Event('change',{bubbles:true}))";
        $down = "var i=this.closest('.va-num-wrap').querySelector('input');var s=parseFloat(i.step)||1;var v=parseFloat(i.value)||0;var mn=i.min!==''?parseFloat(i.min):0;i.value=Math.max(mn,Math.round((v-s)*10000)/10000);i.dispatchEvent(new Event('change',{bubbles:true}))";
        return '<div class="va-num-wrap">'
            . $input_html
            . '<div class="va-num-spin">'
            . '<button type="button" tabindex="-1" onclick="' . esc_attr( $up )   . '">&#9652;</button>'
            . '<button type="button" tabindex="-1" onclick="' . esc_attr( $down ) . '">&#9662;</button>'
            . '</div></div>';
    }

    private static function field_num( string $key, string $label, int $min = 0, int $max = 9999 ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        $input = "<input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" class=\"small-text\">";
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td>" . self::spin_wrap( $key, $input ) . "</td></tr>";
    }

    private static function field_decimal( string $key, string $label, float $min = 0.1, float $max = 5, float $step = 0.01 ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        $input = "<input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" step=\"{$step}\" class=\"small-text\">";
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td>" . self::spin_wrap( $key, $input ) . "</td></tr>";
    }

    private static function field_select( string $key, string $label, array $options ): void {
        $current = (string) self::get_display_option( $key, '' );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><select id=\"{$key}\" name=\"{$key}\">";
        foreach ( $options as $value => $text ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, (string) $value, false ) . '>' . esc_html( (string) $text ) . '</option>';
        }
        echo '</select></td></tr>';
    }

    private static function field_color( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"text\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text va-color-input\" data-default-color=\"{$val}\"></td></tr>";
    }

    private static function field_toggle( string $key, string $label ): void {
        $val = (string) self::get_display_option( $key, '0' );
        echo "<tr><th>{$label}</th><td><input type=\"hidden\" name=\"{$key}\" value=\"0\"><label class=\"va-toggle\"><input type=\"checkbox\" name=\"{$key}\" value=\"1\"" . checked( $val, '1', false ) . "><span class=\"va-toggle-slider\"></span></label></td></tr>";
    }

    private static function field_textarea( string $key, string $label, string $desc = '', int $rows = 6 ): void {
        $val = esc_textarea( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td>";
        echo "<textarea id=\"{$key}\" name=\"{$key}\" rows=\"{$rows}\" class=\"large-text code\" style=\"font-size:13px;line-height:1.6;\">{$val}</textarea>";
        if ( $desc ) echo "<p class=\"description\">{$desc}</p>";
        echo "</td></tr>";
    }

    public static function render_single_designer(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $presets = self::get_single_presets();
        $preset_msg = sanitize_key( (string) ( $_GET['va_single_preset'] ?? '' ) );
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🧱 weingartnerauto.hu - Termekoldal Designer</h1>
            <p class="description">A hirdetés részletes oldal teljes wireframe-je állítható: layout, tipográfia, galéria, kártyák, akciógombok és viewer.</p>

            <?php if ( $preset_msg === 'ok' ): ?>
                <div class="notice notice-success"><p>Preset sikeresen alkalmazva.</p></div>
            <?php elseif ( $preset_msg === 'invalid' ): ?>
                <div class="notice notice-error"><p>Ismeretlen preset.</p></div>
            <?php endif; ?>

            <div class="va-le-card" style="margin-top:14px;max-width:1300px;">
                <div class="va-le-card-hdr">⚡ Wireframe Presetek</div>
                <div class="va-single-presets-grid">
                    <?php foreach ( $presets as $key => $preset ): ?>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="va-single-preset-card">
                            <?php wp_nonce_field( 'va_apply_single_preset' ); ?>
                            <input type="hidden" name="action" value="va_apply_single_preset">
                            <input type="hidden" name="preset_key" value="<?php echo esc_attr( $key ); ?>">
                            <button type="submit" class="va-single-preset-btn">
                                <span class="va-single-preset-title"><?php echo esc_html( $preset['label'] ); ?></span>
                                <span class="va-single-preset-desc"><?php echo esc_html( $preset['desc'] ); ?></span>
                                <span class="va-single-preset-wire">
                                    <i class="w-main"></i><i class="w-side"></i><i class="w-thumbs"></i>
                                </span>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>

            <form id="va-single-designer-form" method="post" action="options.php" style="max-width:1300px;margin-top:14px;">
                <?php settings_fields( 'va_single_settings' ); ?>

                <div class="va-single-main-grid">
                    <div>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🏗️ Layout és szerkezet</div>
                            <table class="form-table">
                                <?php self::field_select( 'va_single_layout_mode', 'Elrendezés', [ 'split' => 'Kétoszlopos (galéria + oldalpanel)', 'stacked' => 'Egyhasábos (stackelt)' ] ); ?>
                                <?php self::field_num( 'va_single_content_max', 'Maximális tartalomszélesség (px)', 960, 1800 ); ?>
                                <?php self::field_num( 'va_single_sidebar_width', 'Oldalpanel szélesség (px)', 280, 620 ); ?>
                                <?php self::field_num( 'va_single_layout_gap', 'Oszlopköz / blokkköz (px)', 8, 60 ); ?>
                                <?php self::field_num( 'va_single_card_radius', 'Kártya lekerekítés (px)', 0, 40 ); ?>
                                <?php self::field_num( 'va_single_card_padding', 'Kártya belső térköz (px)', 10, 48 ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">🖼️ Galéria és média</div>
                            <table class="form-table">
                                <?php self::field_select( 'va_single_gallery_ratio', 'Fő kép arány', [ '1/1' => '1:1', '4/3' => '4:3', '16/10' => '16:10', '16/9' => '16:9' ] ); ?>
                                <?php self::field_select( 'va_single_gallery_fit', 'Képkitöltés', [ 'cover' => 'Kitöltés (cover)', 'contain' => 'Teljes kép (contain)' ] ); ?>
                                <?php self::field_num( 'va_single_thumb_size', 'Bélyegkép méret (px)', 54, 140 ); ?>
                                <?php self::field_text( 'va_single_viewer_bg', 'Viewer háttér (CSS szín / rgba)' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">🔤 Tipográfia és gombok</div>
                            <table class="form-table">
                                <?php self::field_num( 'va_single_title_size', 'Címméret desktopon (px)', 24, 72 ); ?>
                                <?php self::field_num( 'va_single_price_size', 'Ár méret desktopon (px)', 24, 72 ); ?>
                                <?php self::field_num( 'va_single_meta_size', 'Meta címkék mérete (px)', 10, 22 ); ?>
                                <?php self::field_num( 'va_single_btn_height', 'Akciógomb magasság (px)', 34, 72 ); ?>
                                <?php self::field_num( 'va_single_share_size', 'Megosztás gomb méret (px)', 28, 62 ); ?>
                                <?php self::field_num( 'va_single_mobile_title_scale', 'Mobil címskála (%)', 60, 100 ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">🎨 Színek és felületek</div>
                            <table class="form-table">
                                <?php self::field_color( 'va_single_accent', 'Accent szín' ); ?>
                                <?php self::field_text( 'va_single_glass', 'Üveg háttér (rgba)' ); ?>
                                <?php self::field_text( 'va_single_border', 'Üveg keret (rgba)' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">🏅 Feladó panel</div>
                            <table class="form-table">
                                <?php self::field_toggle( 'va_single_show_plan_badge', 'Feladó rang/csomag badge megjelenjen' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">🎨 Szöveg és kártya színek</div>
                            <table class="form-table">
                                <?php self::field_color( 'va_sl_title_color',         'Hirdetés cím színe' ); ?>
                                <?php self::field_color( 'va_sl_card_title_color',    'Kártya szekciócím (Leírás / Részletek / Feladó...)' ); ?>
                                <?php self::field_color( 'va_sl_meta_color',          'Meta sor szöveg (helyszín, dátum, megtekintés...)' ); ?>
                                <?php self::field_color( 'va_sl_views_color',         'Megtekintések szám színe' ); ?>
                                <?php self::field_color( 'va_sl_desc_color',          'Leírás szöveg színe' ); ?>
                                <?php self::field_color( 'va_sl_spec_label_color',    'Műszaki adat cimke (pl. Gyártó, Kaliber...)' ); ?>
                                <?php self::field_color( 'va_sl_spec_val_color',      'Műszaki adat érték színe' ); ?>
                                <?php self::field_color( 'va_sl_expired_color',       'Lejárt hirdetés dátum szín' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">🏷 Pill, badge és demand</div>
                            <table class="form-table">
                                <?php self::field_color( 'va_sl_featured_pill_bg',     '"Kiemelt" pill háttér' ); ?>
                                <?php self::field_color( 'va_sl_featured_pill_border',  '"Kiemelt" pill keret' ); ?>
                                <?php self::field_color( 'va_sl_featured_pill_text',    '"Kiemelt" pill szöveg' ); ?>
                                <?php self::field_color( 'va_sl_demand_bg',            'Kereslet-indikátor háttér' ); ?>
                                <?php self::field_color( 'va_sl_demand_border',        'Kereslet-indikátor keret' ); ?>
                                <?php self::field_color( 'va_sl_demand_text',          'Kereslet-indikátor szöveg' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">👤 Feladó és megosztás</div>
                            <table class="form-table">
                                <?php self::field_color( 'va_sl_seller_name_color',  'Feladó neve szín' ); ?>
                                <?php self::field_color( 'va_sl_seller_since_color', '"Tag X óta" szín' ); ?>
                                <?php self::field_color( 'va_sl_share_label_color',  '"Megosztás:" felirat szín' ); ?>
                                <?php self::field_num( 'va_sl_phone_icon_size', 'Telefon ikon méret (px)', 12, 32 ); ?>
                                <?php self::field_color( 'va_sl_phone_icon_color', 'Telefon ikon szín' ); ?>
                                <?php self::field_color( 'va_sl_watch_btn_color', '"Mentés kedvencekbe" gomb betűszín' ); ?>
                                <?php self::field_color( 'va_sl_watch_btn_border', '"Mentés kedvencekbe" gomb keret szín' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">📌 Sticky sáv + kapcsolódó hirdetések</div>
                            <table class="form-table">
                                <?php self::field_color( 'va_sl_sticky_bg',           'Sticky sáv háttér' ); ?>
                                <?php self::field_color( 'va_sl_sticky_title_color',  'Sticky sáv cím szín' ); ?>
                                <?php self::field_color( 'va_sl_related_border',      'Kapcsolódó kártya keret' ); ?>
                                <?php self::field_color( 'va_sl_related_title_color', 'Kapcsolódó cím szín' ); ?>
                                <?php self::field_color( 'va_sl_related_meta_color',  'Kapcsolódó meta szín' ); ?>
                            </table>
                        </div>

                        <div class="va-le-card" style="margin-top:14px;">
                            <div class="va-le-card-hdr">✏️ Feliratok és szövegek</div>
                            <p class="description" style="padding:0 0 10px 10px;">Minden statikus szöveg megváltoztatható – pl. "Leírás" → "Termékleírás".</p>
                            <table class="form-table">
                                <?php self::field_text( 'va_sl_lbl_description',     '"Leírás" szekció cím' ); ?>
                                <?php self::field_text( 'va_sl_lbl_details',         '"Részletek" szekció cím' ); ?>
                                <?php self::field_text( 'va_sl_lbl_seller',          '"Feladó" szekció cím' ); ?>
                                <?php self::field_text( 'va_sl_lbl_more_listings',   '"Feladó további hirdetései" cím' ); ?>
                                <?php self::field_text( 'va_sl_lbl_related',         '"Hasonló hirdetések" cím' ); ?>
                                <?php self::field_text( 'va_sl_lbl_phone_btn_label', 'Telefonszám gomb szövege' ); ?>
                                <?php self::field_text( 'va_sl_lbl_email_btn',       'E-mail gomb szövege' ); ?>
                                <?php self::field_text( 'va_sl_lbl_watch_add',       '"Mentés kedvencekbe" gomb' ); ?>
                                <?php self::field_text( 'va_sl_lbl_watch_remove',    '"Eltávolítás kedvencekből" gomb' ); ?>
                                <?php self::field_text( 'va_sl_lbl_share',           '"Megosztás:" felirat' ); ?>
                                <?php self::field_text( 'va_sl_lbl_zoom',            '"Nagyítás" gomb szöveg' ); ?>
                                <?php self::field_text( 'va_sl_lbl_no_image',        '"Nincs kép" placeholder szöveg' ); ?>
                                <?php self::field_text( 'va_sl_lbl_views_suffix',    '"megtekintés" utótag' ); ?>
                                <?php self::field_text( 'va_sl_lbl_posted',          '"Feladva:" előtag' ); ?>
                                <?php self::field_text( 'va_sl_lbl_expires',         '"Lejár:" előtag' ); ?>
                                <?php self::field_text( 'va_sl_lbl_expired_label',   '"Lejárt:" előtag' ); ?>
                                <?php self::field_text( 'va_sl_lbl_member_since_pre','Tag X óta – "Tag" szó' ); ?>
                                <?php self::field_text( 'va_sl_lbl_member_since_suf','Tag X óta – "óta" szó' ); ?>
                                <?php self::field_text( 'va_sl_lbl_condition_pre',   '"Állapot:" előtag a meta sorban' ); ?>
                                <?php self::field_text( 'va_sl_lbl_demand',          'Kereslet szöveg (az "%d érdeklődő..." utáni rész)' ); ?>
                                <?php self::field_text( 'va_sl_lbl_featured_pill',   '"Kiemelt" pill szövege' ); ?>
                            </table>
                        </div>

                        <?php submit_button( 'Termékoldal dizájn mentése', 'primary', 'submit', false, [ 'style' => 'margin-top:14px;' ] ); ?>
                    </div>

                    <aside>
                        <div class="va-le-card va-single-preview-card">
                            <div class="va-le-card-hdr">👁️ Élő wireframe előnézet</div>
                            <div id="va-single-preview" class="va-single-preview">
                                <div class="sp-top"></div>
                                <div class="sp-grid">
                                    <div class="sp-main">
                                        <div class="sp-hero"></div>
                                        <div class="sp-thumbs"><i></i><i></i><i></i><i></i></div>
                                        <div class="sp-title"></div>
                                        <div class="sp-meta"><b></b><b></b><b></b></div>
                                        <div class="sp-text"></div>
                                    </div>
                                    <div class="sp-side">
                                        <div class="sp-price"></div>
                                        <div class="sp-btn"></div>
                                        <div class="sp-btn sp-btn--ghost"></div>
                                        <div class="sp-share"><i></i><i></i><i></i></div>
                                    </div>
                                </div>
                            </div>
                            <p class="description" style="margin-top:10px;">A mentés után a beállítások azonnal élesednek a hirdetés részletes oldalon.</p>
                        </div>
                    </aside>
                </div>
            </form>
        </div>

        <style>
        .va-single-presets-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
        .va-single-preset-card { margin:0; }
        .va-single-preset-btn {
            width:100%; border:1px solid var(--va-border2); background:var(--va-bg3); color:var(--va-text);
            border-radius:12px; padding:12px; text-align:left; cursor:pointer; transition:.2s ease;
        }
        .va-single-preset-btn:hover { border-color:var(--va-accent); box-shadow:0 8px 28px rgba(0,0,0,.35); transform:translateY(-2px); }
        .va-single-preset-title { display:block; font-weight:700; font-size:12px; }
        .va-single-preset-desc { display:block; font-size:11px; color:var(--va-muted); margin-top:2px; }
        .va-single-preset-wire { display:grid; grid-template-columns:1fr .62fr; gap:8px; margin-top:10px; }
        .va-single-preset-wire i { display:block; background:rgba(255,255,255,.07); border-radius:7px; }
        .va-single-preset-wire .w-main { height:48px; }
        .va-single-preset-wire .w-side { height:48px; }
        .va-single-preset-wire .w-thumbs { grid-column:1 / span 2; height:10px; }

        .va-single-main-grid { display:grid; grid-template-columns:1fr 380px; gap:18px; align-items:start; }
        .va-single-preview-card { position:sticky; top:74px; }
        @media (max-width:1120px) {
            .va-single-main-grid { grid-template-columns:1fr; }
            .va-single-preview-card { position:static; }
        }

        .va-single-preview {
            --sp-accent:#ff2a2a; --sp-glass:rgba(255,255,255,.07); --sp-border:rgba(255,255,255,.12);
            --sp-gap:20px; --sp-radius:12px; --sp-pad:18px; --sp-side:0.6fr; --sp-ratio:4/3; --sp-thumb:74px;
            background:#080808; border:1px solid var(--sp-border); border-radius:16px; padding:12px;
        }
        .va-single-preview .sp-top { height:8px; width:120px; background:rgba(255,255,255,.15); border-radius:999px; margin-bottom:12px; }
        .va-single-preview .sp-grid { display:grid; grid-template-columns:1fr var(--sp-side); gap:var(--sp-gap); }
        .va-single-preview.sp-stacked .sp-grid { grid-template-columns:1fr; }
        .va-single-preview .sp-main,
        .va-single-preview .sp-side { background:var(--sp-glass); border:1px solid var(--sp-border); border-radius:var(--sp-radius); padding:var(--sp-pad); min-width:0; overflow:hidden; }
        .va-single-preview .sp-hero { aspect-ratio:var(--sp-ratio); border-radius:calc(var(--sp-radius) - 2px); background:linear-gradient(130deg, rgba(255,255,255,.16), rgba(255,255,255,.05)); }
        .va-single-preview .sp-thumbs { display:flex; gap:8px; margin-top:10px; }
        .va-single-preview .sp-thumbs i { width:var(--sp-thumb); height:calc(var(--sp-thumb) * .75); border-radius:8px; background:rgba(255,255,255,.16); }
        .va-single-preview .sp-title { margin-top:14px; height:14px; width:76%; border-radius:5px; background:#fff; opacity:.85; }
        .va-single-preview .sp-meta { display:flex; gap:8px; margin-top:10px; }
        .va-single-preview .sp-meta b { width:66px; height:8px; border-radius:999px; background:rgba(255,255,255,.2); display:block; }
        .va-single-preview .sp-text { margin-top:12px; height:42px; border-radius:10px; background:rgba(255,255,255,.08); }
        .va-single-preview .sp-price { height:14px; width:70%; border-radius:7px; background:var(--sp-accent); margin-bottom:16px; }
        .va-single-preview .sp-btn { height:42px; border-radius:10px; background:var(--sp-accent); margin-bottom:10px; }
        .va-single-preview .sp-btn--ghost { background:transparent; border:1px solid var(--sp-border); }
        .va-single-preview .sp-share { display:flex; gap:8px; margin-top:8px; }
        .va-single-preview .sp-share i { width:38px; height:38px; border-radius:999px; border:1px solid var(--sp-border); background:rgba(255,255,255,.05); display:block; }
        </style>

        <script>
        (function () {
            const form = document.getElementById('va-single-designer-form');
            const preview = document.getElementById('va-single-preview');
            if (!form || !preview) return;

            const ratioMap = {
                '1/1': '1 / 1',
                '4/3': '4 / 3',
                '16/10': '16 / 10',
                '16/9': '16 / 9'
            };

            function val(name, fallback = '') {
                const el = form.querySelector('[name="' + name + '"]');
                return el ? (el.value || fallback) : fallback;
            }

            function syncPreview() {
                const mode = val('va_single_layout_mode', 'split');
                preview.classList.toggle('sp-stacked', mode === 'stacked');
                preview.style.setProperty('--sp-side', (Math.max(0.3, Math.min(0.8, Number(val('va_single_sidebar_width', 380)) / 800))).toFixed(2) + 'fr');
                preview.style.setProperty('--sp-gap', Math.max(8, Math.min(40, Number(val('va_single_layout_gap', 24)))) + 'px');
                preview.style.setProperty('--sp-radius', Math.max(0, Math.min(40, Number(val('va_single_card_radius', 14)))) + 'px');
                preview.style.setProperty('--sp-pad', Math.max(8, Math.min(40, Number(val('va_single_card_padding', 22)))) + 'px');
                preview.style.setProperty('--sp-ratio', ratioMap[val('va_single_gallery_ratio', '4/3')] || '4 / 3');
                preview.style.setProperty('--sp-thumb', Math.max(44, Math.min(110, Number(val('va_single_thumb_size', 86)))) + 'px');
                preview.style.setProperty('--sp-accent', val('va_single_accent', '#ff2a2a'));
                preview.style.setProperty('--sp-glass', val('va_single_glass', 'rgba(255,255,255,.07)'));
                preview.style.setProperty('--sp-border', val('va_single_border', 'rgba(255,255,255,.12)'));
            }

            form.addEventListener('input', syncPreview);
            form.addEventListener('change', syncPreview);
            syncPreview();
        })();
        </script>
        <?php
    }

    /* ══ Social Media beállítások ═════════════════════════ */
    public static function render_social(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $platforms = [
            'facebook'  => [ 'label' => 'Facebook',   'placeholder' => 'https://facebook.com/vadaszapro' ],
            'instagram' => [ 'label' => 'Instagram',  'placeholder' => 'https://instagram.com/vadaszapro' ],
            'youtube'   => [ 'label' => 'YouTube',    'placeholder' => 'https://youtube.com/@vadaszapro' ],
            'tiktok'    => [ 'label' => 'TikTok',     'placeholder' => 'https://tiktok.com/@vadaszapro' ],
            'twitter'   => [ 'label' => 'X (Twitter)','placeholder' => 'https://x.com/vadaszapro' ],
            'pinterest' => [ 'label' => 'Pinterest',  'placeholder' => 'https://pinterest.com/vadaszapro' ],
            'linkedin'  => [ 'label' => 'LinkedIn',   'placeholder' => 'https://linkedin.com/company/vadaszapro' ],
            'whatsapp'  => [ 'label' => 'WhatsApp',   'placeholder' => 'https://wa.me/36301234567' ],
            'telegram'  => [ 'label' => 'Telegram',   'placeholder' => 'https://t.me/vadaszapro' ],
        ];
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🌐 weingartnerauto.hu - Social Media</h1>
            <p class="description">Add meg a közösségi média profilok URL-jeit. Az ikonok automatikusan megjelennek a fejlécben és a láblécben.</p>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_social_settings' ); ?>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">🔗 Platform linkek</div>
                    <table class="form-table">
                        <?php foreach ( $platforms as $key => $cfg ): ?>
                        <tr>
                            <th style="display:flex;align-items:center;gap:10px;">
                                <?php echo va_social_svg( $key, 20 ); ?>
                                <?php echo esc_html( $cfg['label'] ); ?>
                            </th>
                            <td>
                                <input type="url" name="va_social_<?php echo esc_attr( $key ); ?>"
                                       value="<?php echo esc_attr( (string) get_option( 'va_social_' . $key, '' ) ); ?>"
                                       placeholder="<?php echo esc_attr( $cfg['placeholder'] ); ?>"
                                       class="va-le-input regular-text">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">⚙️ Megjelenítés</div>
                    <table class="form-table">
                        <?php self::field_toggle( 'va_social_header_show', 'Fejlécben megjelenjen' ); ?>
                        <?php self::field_toggle( 'va_social_footer_show', 'Láblécben megjelenjen' ); ?>
                        <tr>
                            <th>Fejléc stílus</th>
                            <td><?php self::field_select( 'va_social_header_style', '', [
                                'icons' => 'Csak ikonok (kompakt)',
                                'pills' => 'Pill (ikon + platformnév)',
                            ] ); ?></td>
                        </tr>
                        <tr>
                            <th>Lábléc stílus</th>
                            <td><?php self::field_select( 'va_social_footer_style', '', [
                                'icons' => 'Csak ikonok',
                                'pills' => 'Pill (ikon + platformnév)',
                                'full'  => 'Teljes sor (ikon + link + leírás)',
                            ] ); ?></td>
                        </tr>
                        <?php self::field_num( 'va_social_icon_size', 'Ikon méret (px)', 14, 40 ); ?>
                    </table>
                </div>

                <?php submit_button( 'Social Media mentése' ); ?>
            </form>

            <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                <div class="va-le-card-hdr">👁️ Előnézet</div>
                <div style="padding:12px 0;">
                    <p style="font-size:11px;color:var(--va-muted);margin-bottom:8px;">Ikonos változat:</p>
                    <?php echo va_social_bar( 'icons', 24 ); ?>
                    <p style="font-size:11px;color:var(--va-muted);margin:16px 0 8px;">Pill változat:</p>
                    <?php echo va_social_bar( 'pills', 20 ); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /* ══ Árkártyák szerkesztő ════════════════════════════════════ */
    public static function render_price_cards(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $g = static fn( string $k, string $d = '' ) => (string) ( get_option( $k, $d ) ?: $d );
        $gi = static fn( string $k, int $d = 0 ) => (int) ( get_option( $k, $d ) ?: $d );

        $card_defaults = [
            'labels'     => [ 1 => 'Basic', 2 => 'Silver', 3 => 'Gold', 4 => 'Platinum' ],
            'slugs'      => [ 1 => 'basic', 2 => 'silver', 3 => 'gold', 4 => 'platinum' ],
            'tags'       => [ 1 => 'Belépő', 2 => 'Népszerű', 3 => 'Profi', 4 => 'Prémium' ],
            'descs'      => [ 1 => 'Ingyenes alap csomag minden regisztrált felhasználónak.', 2 => '3 hirdetési kredit kedvezményes áron.', 3 => '5 kredit – legjobb érték a profik számára.', 4 => '10 kredit – maximális értékcsomag vadász profiknak.' ],
            'qtys'       => [ 1 => 1, 2 => 3, 3 => 5, 4 => 10 ],
            'prices'     => [ 1 => 0, 2 => 1791, 3 => 1592, 4 => 1393 ],
            'badges'     => [ 1 => '', 2 => '–10%', 3 => '–20%', 4 => '–30%' ],
            'themes'     => [ 1 => 'basic', 2 => 'silver', 3 => 'gold', 4 => 'platinum' ],
            'btns'       => [ 1 => 'Mindenki számára elérhető', 2 => 'Vásárlás →', 3 => 'Vásárlás →', 4 => 'Vásárlás →' ],
        ];

        $theme_colors = [
            'basic'    => [ 'accent' => '#6b7280', 'glow' => 'rgba(107,114,128,.25)', 'gradient' => 'linear-gradient(135deg,#1a1a1a,#111)' ],
            'silver'   => [ 'accent' => '#94a3b8', 'glow' => 'rgba(148,163,184,.3)',  'gradient' => 'linear-gradient(135deg,#1e2a35,#111827)' ],
            'gold'     => [ 'accent' => '#f59e0b', 'glow' => 'rgba(245,158,11,.35)',  'gradient' => 'linear-gradient(135deg,#2a1f0a,#1a1200)' ],
            'platinum' => [ 'accent' => '#cc0000', 'glow' => 'rgba(204,0,0,.35)',     'gradient' => 'linear-gradient(135deg,#2a0a0a,#1a0000)' ],
        ];

        ?>
        <style>
        .va-pk-wrap { max-width:1400px; }
        .va-pk-hero { background:linear-gradient(135deg,rgba(204,0,0,.08),rgba(0,0,0,0)),rgba(14,14,18,.95); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:28px 32px; margin-bottom:28px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        .va-pk-hero__text h1 { margin:0 0 6px; font-size:22px; color:#fff; font-weight:700; }
        .va-pk-hero__text p  { margin:0; color:rgba(255,255,255,.5); font-size:13px; }
        .va-pk-hero-section { background:rgba(14,14,18,.7); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:20px 24px; margin-bottom:24px; }
        .va-pk-hero-section h2 { margin:0 0 16px; font-size:15px; color:#fff; font-weight:600; display:flex; align-items:center; gap:8px; }
        .va-pk-hero-fields { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
        .va-pk-field { display:flex; flex-direction:column; gap:5px; }
        .va-pk-field label { font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:rgba(255,255,255,.45); }
        .va-pk-field input, .va-pk-field textarea { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:9px 12px; color:#e8e8f0; font-size:13px; width:100%; box-sizing:border-box; transition:border-color .15s; }
        .va-pk-field input:focus, .va-pk-field textarea:focus { border-color:rgba(204,0,0,.6); outline:none; box-shadow:0 0 0 3px rgba(204,0,0,.1); }
        .va-pk-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        @media(max-width:1200px) { .va-pk-grid { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:700px)  { .va-pk-grid { grid-template-columns:1fr; } }
        .va-pk-card--hidden { display:none; }
        .va-pk-add-row { display:flex; justify-content:center; margin-bottom:16px; }
        .va-pk-add-btn { background:rgba(255,255,255,.06); border:1px dashed rgba(255,255,255,.2); border-radius:12px; color:rgba(255,255,255,.6); font-size:13px; font-weight:600; padding:12px 28px; cursor:pointer; transition:.2s; }
        .va-pk-add-btn:hover { background:rgba(204,0,0,.12); border-color:rgba(204,0,0,.4); color:#fff; }
        .va-pk-add-btn:disabled { opacity:.3; cursor:default; }
        .va-pk-card { border-radius:16px; border:1px solid rgba(255,255,255,.1); overflow:hidden; transition:box-shadow .2s; }
        .va-pk-card--featured { border-color:rgba(245,158,11,.4); }
        .va-pk-card__header { padding:16px 16px 12px; position:relative; }
        .va-pk-card__badge-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
        .va-pk-card__badge { font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:3px 8px; border-radius:20px; background:rgba(255,255,255,.08); color:rgba(255,255,255,.5); }
        .va-pk-card__badge--tag { background:rgba(255,255,255,.1); color:#e8e8f0; }
        .va-pk-card__enable-row { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; background:rgba(0,0,0,.2); border-top:1px solid rgba(255,255,255,.06); }
        .va-pk-card__enable-label { font-size:11px; color:rgba(255,255,255,.4); font-weight:600; text-transform:uppercase; letter-spacing:.06em; }
        .va-pk-toggle { position:relative; width:36px; height:20px; flex-shrink:0; }
        .va-pk-toggle input { opacity:0; width:0; height:0; position:absolute; }
        .va-pk-toggle__track { position:absolute; inset:0; border-radius:20px; background:#8b1a1a; cursor:pointer; transition:background .15s; }
        .va-pk-toggle input:checked+.va-pk-toggle__track { background:#1a7a2e; box-shadow:0 0 8px rgba(0,200,60,.3); }
        .va-pk-toggle__track::after { content:''; position:absolute; left:3px; top:3px; width:14px; height:14px; border-radius:50%; background:#fff; transition:transform .15s; }
        .va-pk-toggle input:checked+.va-pk-toggle__track::after { transform:translateX(16px); }
        .va-pk-toggle-row { display:flex; align-items:center; gap:8px; padding:4px 0; }
        .va-pk-toggle-text { font-size:12px; color:rgba(255,255,255,.45); font-weight:500; text-transform:none; letter-spacing:0; }
        .va-pk-card__fields { padding:14px 16px 16px; display:flex; flex-direction:column; gap:10px; }
        .va-pk-card__field-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .va-pk-card__field-row--3 { grid-template-columns:1fr 1fr 1fr; }
        .va-pk-card__price-preview { text-align:center; padding:14px 0; }
        .va-pk-card__price-preview .total { font-size:64px; font-weight:900; color:#fff; line-height:1; display:flex; align-items:baseline; justify-content:center; gap:4px; }
        .va-pk-card__price-preview .total .num { letter-spacing:-2px; }
        .va-pk-card__price-preview .total .currency { font-size:28px; font-weight:700; opacity:.7; margin-left:3px; }
        .va-pk-card__price-preview .unit  { font-size:13px; color:rgba(255,255,255,.4); margin-top:4px; }
        .va-pk-card__price-preview .free-tag { font-size:22px; font-weight:700; color:#4ade80; }
        .va-pk-save-bar { background:rgba(14,14,18,.95); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:16px 24px; display:flex; align-items:center; gap:14px; }
        .va-pk-save-bar .button-primary { background:#cc0000 !important; border-color:#cc0000 !important; color:#fff !important; padding:8px 24px !important; height:auto !important; border-radius:8px !important; font-weight:600 !important; font-size:13px !important; }
        .va-pk-save-bar .button-primary:hover { background:#aa0000 !important; border-color:#aa0000 !important; }
        .va-pk-note { font-size:12px; color:rgba(255,255,255,.35); }
        </style>

        <div class="wrap va-admin-wrap va-pk-wrap">
            <div class="va-pk-hero">
                <div class="va-pk-hero__text">
                    <h1>💳 Árkártyák szerkesztő</h1>
                    <p>Szerkeszd az árkártyák megjelenését, szövegeit és árait. A változtatások azonnal megjelennek az oldalon mentés után.</p>
                </div>
            </div>

            <?php settings_errors( 'va_price_cards_settings' ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_price_cards_settings' ); ?>

                <!-- ─── Hero szövegek ─── -->
                <div class="va-pk-hero-section">
                    <h2>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        Az oldal hero szövegei
                    </h2>
                    <div class="va-pk-hero-fields">
                        <div class="va-pk-field">
                            <label for="va_pc_eyebrow">Eyebrow (kis felirat fölött)</label>
                            <input type="text" id="va_pc_eyebrow" name="va_pc_eyebrow" value="<?php echo esc_attr( $g('va_pc_eyebrow','Átlátható csomagok') ); ?>">
                        </div>
                        <div class="va-pk-field">
                            <label for="va_pc_title">Főcím</label>
                            <input type="text" id="va_pc_title" name="va_pc_title" value="<?php echo esc_attr( $g('va_pc_title','Rang Alapú Vásárlás') ); ?>">
                        </div>
                        <div class="va-pk-field">
                            <label for="va_pc_subtitle">Alcím / leírás</label>
                            <input type="text" id="va_pc_subtitle" name="va_pc_subtitle" value="<?php echo esc_attr( $g('va_pc_subtitle','Válassz csomagot a rangok szerint, és fizess azonnal bankkártyával.') ); ?>">
                        </div>
                    </div>
                </div>

                <!-- ─── Kártya szerkesztők ─── -->
                <?php
                $pc_count = max( 4, min( 8, $gi( 'va_pc_count', 4 ) ) );
                ?>
                <input type="hidden" name="va_pc_count" id="va-pk-count" value="<?php echo $pc_count; ?>">
                <div class="va-pk-grid" id="va-pk-grid">
                <?php for ( $n = 1; $n <= 8; $n++ ):
                    $label    = $g( "va_pc_{$n}_label",    $card_defaults['labels'][$n]  ?? '' );
                    $slug     = $g( "va_pc_{$n}_plan_slug",$card_defaults['slugs'][$n]   ?? '' );
                    $tag      = $g( "va_pc_{$n}_tag",      $card_defaults['tags'][$n]    ?? '' );
                    $desc     = $g( "va_pc_{$n}_desc",     $card_defaults['descs'][$n]   ?? '' );
                    $qty      = $gi( "va_pc_{$n}_qty",     $card_defaults['qtys'][$n]    ?? 1  );
                    $price    = $gi( "va_pc_{$n}_price",   $card_defaults['prices'][$n]  ?? 0  );
                    $badge    = $g( "va_pc_{$n}_badge",    $card_defaults['badges'][$n]  ?? '' );
                    $featured = $g( "va_pc_{$n}_featured", ( $n === 3 ) ? '1' : '0' ) === '1';
                    $free     = $g( "va_pc_{$n}_free",     ( $n === 1 ) ? '1' : '0' ) === '1';
                    $btn_text = $g( "va_pc_{$n}_btn_text", $card_defaults['btns'][$n]    ?? 'Vásárlás →' );
                    $theme    = $g( "va_pc_{$n}_theme",    $card_defaults['themes'][$n]  ?? 'basic' );
                    $enabled  = $g( "va_pc_{$n}_enabled",  $n <= 4 ? '1' : '0' ) === '1';
                    $is_hidden = $n > $pc_count;

                    $tc       = $theme_colors[ $theme ] ?? $theme_colors['basic'];
                    $total    = $qty * $price;
                ?>
                <div class="va-pk-card<?php echo $featured ? ' va-pk-card--featured' : ''; ?><?php echo $is_hidden ? ' va-pk-card--hidden' : ''; ?>" data-card-n="<?php echo $n; ?>"
                     style="background:<?php echo esc_attr( $tc['gradient'] ); ?>; box-shadow:<?php echo $featured ? '0 8px 40px ' . esc_attr( $tc['glow'] ) : 'none'; ?>;">
                    <!-- Header preview -->
                    <div class="va-pk-card__header" style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <div class="va-pk-card__badge-row">
                            <span class="va-pk-card__badge va-pk-card__badge--tag" style="background:<?php echo esc_attr( $tc['glow'] ); ?>; color:<?php echo esc_attr( $tc['accent'] ); ?>;">
                                <?php echo esc_html( $tag ); ?>
                            </span>
                            <?php if ( $badge ): ?>
                            <span class="va-pk-card__badge" style="background:<?php echo esc_attr( $tc['glow'] ); ?>; color:<?php echo esc_attr( $tc['accent'] ); ?>;">
                                <?php echo esc_html( $badge ); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:16px; font-weight:800; color:#fff; letter-spacing:.04em; margin-bottom:4px;">
                            <?php echo esc_html( strtoupper( $label ) ); ?>
                        </div>
                        <div style="font-size:12px; color:rgba(255,255,255,.4);">
                            <?php echo esc_html( $desc ); ?>
                        </div>
                        <!-- Ár preview -->
                        <div class="va-pk-card__price-preview">
                            <?php if ( $free ): ?>
                            <div class="free-tag">Ingyenes</div>
                            <?php else: ?>
                            <div class="total" style="color:<?php echo esc_attr( $tc['accent'] ); ?>;">
                                <span class="num" id="va-pk-total-<?php echo $n; ?>"><?php echo number_format( $total, 0, ',', '&nbsp;' ); ?></span><span class="currency">Ft</span>
                            </div>
                            <div class="unit"><span id="va-pk-unit-<?php echo $n; ?>"><?php echo number_format( $price, 0, ',', ' ' ); ?></span> Ft / kredit · <?php echo esc_html( (string) $qty ); ?> db</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Enable toggle -->
                    <div class="va-pk-card__enable-row">
                        <span class="va-pk-card__enable-label">Kártya aktív</span>
                        <label class="va-pk-toggle">
                            <input type="checkbox" name="va_pc_<?php echo $n; ?>_enabled" value="1"<?php checked( $enabled ); ?>>
                            <span class="va-pk-toggle__track"></span>
                        </label>
                    </div>

                    <!-- Fields -->
                    <div class="va-pk-card__fields">
                        <div class="va-pk-field">
                            <label>Kártya neve (label)</label>
                            <input type="text" name="va_pc_<?php echo $n; ?>_label" value="<?php echo esc_attr( $label ); ?>" placeholder="Basic">
                        </div>

                        <div class="va-pk-card__field-row">
                            <div class="va-pk-field">
                                <label>Tag (badge felirat)</label>
                                <input type="text" name="va_pc_<?php echo $n; ?>_tag" value="<?php echo esc_attr( $tag ); ?>" placeholder="Népszerű">
                            </div>
                            <div class="va-pk-field">
                                <label>Kedvezmény badge</label>
                                <input type="text" name="va_pc_<?php echo $n; ?>_badge" value="<?php echo esc_attr( $badge ); ?>" placeholder="–20%">
                            </div>
                        </div>

                        <div class="va-pk-field">
                            <label>Leírás szöveg</label>
                            <input type="text" name="va_pc_<?php echo $n; ?>_desc" value="<?php echo esc_attr( $desc ); ?>">
                        </div>

                        <div class="va-pk-card__field-row">
                            <div class="va-pk-field">
                                <label>Kredit mennyiség</label>
                                <input type="number" name="va_pc_<?php echo $n; ?>_qty" value="<?php echo esc_attr( (string) $qty ); ?>" min="1" max="9999"
                                       data-card="<?php echo $n; ?>" class="va-pk-qty-input">
                            </div>
                            <div class="va-pk-field">
                                <label>Ár / kredit (Ft)</label>
                                <input type="number" name="va_pc_<?php echo $n; ?>_price" value="<?php echo esc_attr( (string) $price ); ?>" min="0" max="99999"
                                       data-card="<?php echo $n; ?>" class="va-pk-price-input">
                            </div>
                        </div>

                        <div class="va-pk-card__field-row">
                            <div class="va-pk-field">
                                <span class="va-pk-toggle-row">
                                    <label class="va-pk-toggle">
                                        <input type="checkbox" name="va_pc_<?php echo $n; ?>_featured" value="1"<?php checked( $featured ); ?>>
                                        <span class="va-pk-toggle__track"></span>
                                    </label>
                                    <span class="va-pk-toggle-text">Kiemelt kártya</span>
                                </span>
                            </div>
                            <div class="va-pk-field">
                                <span class="va-pk-toggle-row">
                                    <label class="va-pk-toggle">
                                        <input type="checkbox" name="va_pc_<?php echo $n; ?>_free" value="1"<?php checked( $free ); ?>>
                                        <span class="va-pk-toggle__track"></span>
                                    </label>
                                    <span class="va-pk-toggle-text">Ingyenes kártya</span>
                                </span>
                            </div>
                        </div>

                        <div class="va-pk-field">
                            <label>Gomb felirat</label>
                            <input type="text" name="va_pc_<?php echo $n; ?>_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" placeholder="Vásárlás →">
                        </div>

                        <div class="va-pk-field">
                            <label>Plan slug (aktív det.)</label>
                            <input type="text" name="va_pc_<?php echo $n; ?>_plan_slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="gold">
                        </div>
                        <div class="va-pk-field">
                            <label>Téma / szín</label>
                            <select name="va_pc_<?php echo $n; ?>_theme" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:9px 10px;color:#e8e8f0;font-size:13px;width:100%;box-sizing:border-box;">
                                <?php foreach ( $theme_colors as $t_key => $_ ): ?>
                                <option value="<?php echo esc_attr( $t_key ); ?>"<?php selected( $theme, $t_key ); ?>><?php echo esc_html( ucfirst( $t_key ) ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
                </div>

                <!-- ─── Kártya hozzáadása ─── -->
                <div class="va-pk-add-row">
                    <button type="button" id="va-pk-add-card" class="va-pk-add-btn"<?php echo $pc_count >= 8 ? ' disabled' : ''; ?>>+ Üres kártya hozzáadása</button>
                </div>

                <!-- ─── Mentés ─── -->
                <div class="va-pk-save-bar">
                    <?php submit_button( 'Mentés', 'primary', 'submit', false ); ?>
                    <span class="va-pk-note">Mentés után az árkártyák azonnal frissülnek a vásárlás oldalon.</span>
                </div>
            </form>
        </div>

        <script>
        (function(){
            function formatHu(n){
                return n.toLocaleString('hu-HU');
            }
            document.querySelectorAll('.va-pk-qty-input,.va-pk-price-input').forEach(function(el){
                el.addEventListener('input', function(){
                    var card = this.dataset.card;
                    var qtyEl   = document.querySelector('.va-pk-qty-input[data-card="'+card+'"]');
                    var priceEl = document.querySelector('.va-pk-price-input[data-card="'+card+'"]');
                    var totalEl = document.getElementById('va-pk-total-'+card);
                    var unitEl  = document.getElementById('va-pk-unit-'+card);
                    if (!qtyEl||!priceEl||!totalEl) return;
                    var qty   = parseInt(qtyEl.value)||0;
                    var price = parseInt(priceEl.value)||0;
                    totalEl.textContent = formatHu(qty*price);
                    if(unitEl) unitEl.textContent = formatHu(price);
                });
            });

            // + Kártya hozzáadása
            var addBtn   = document.getElementById('va-pk-add-card');
            var countEl  = document.getElementById('va-pk-count');
            if (addBtn && countEl) {
                addBtn.addEventListener('click', function(){
                    var cur = parseInt(countEl.value) || 4;
                    if (cur >= 8) { addBtn.disabled = true; return; }
                    var next = cur + 1;
                    var card = document.querySelector('[data-card-n="'+next+'"]');
                    if (card) {
                        card.classList.remove('va-pk-card--hidden');
                        countEl.value = next;
                        if (next >= 8) addBtn.disabled = true;
                    }
                });
            }
        })();
        </script>
        <?php
    }

    /* ══ Csomag beállítások oldal ═══════════════════════════════ */
    public static function render_plans(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $plans      = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_all_plan_configs() : [];
        $global     = $plans['_global'] ?? [ 'boost_badge_window' => 7, 'boost_badge_text' => '⚡ Előre téve', 'boost_enabled' => true ];
        $defaults   = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::PLANS : [];
        $nonce      = wp_create_nonce( 'va_admin_plan_cfg' );
        $plan_slugs = array_keys( $defaults );
        if ( empty( $plan_slugs ) ) {
            echo '<div class="wrap va-admin-wrap"><div class="notice notice-error"><p>A csomagdefiniciok nem erhetoek el.</p></div></div>';
            return;
        }

        $first_slug = $plan_slugs[0];
        ?>
        <div class="wrap va-admin-wrap va-pc-wrap">
            <div class="va-pc-hero">
                <div class="va-pc-hero__titlewrap">
                    <span class="va-pc-hero__eyebrow">Elofizetesi rendszer</span>
                    <h1 class="va-pc-hero__title">Csomag Beallitasok</h1>
                    <p class="va-pc-hero__lead">Bal oldalt gyorsan tudsz valtani a csomagok kozott, jobb oldalon pedig egyszerre latszik a preview, a limitlogika es a marketing adat. A mentes egy lepes, a valtozas azonnal ervenybe lep.</p>
                </div>
                <div class="va-pc-hero__actions">
                    <span id="va-pc-save-status" class="va-pc-save-status"></span>
                    <button id="va-pc-save-all" class="va-pc-save-btn" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                        Osszes modositas mentese
                    </button>
                </div>
            </div>

            <div class="va-pc-shell">
                <aside class="va-pc-sidebar">
                    <div class="va-pc-sidebar__section">
                        <div class="va-pc-sidebar__label">Csomagok</div>
                        <div class="va-pc-nav" role="tablist" aria-label="Csomag valaszto">
                            <?php foreach ( $plan_slugs as $index => $slug ):
                                $plan = $plans[ $slug ] ?? $defaults[ $slug ];
                                $limit_value = isset( $plan['monthly_limit'] ) ? (int) $plan['monthly_limit'] : 0;
                                $limit_label = $limit_value > 0 ? (string) $limit_value : 'Korlátlan';
                                $basis_label = ( isset( $plan['basis'] ) && $plan['basis'] === 'active' ) ? 'aktív' : 'havi';
                            ?>
                            <button class="va-pc-nav__item<?php echo 0 === $index ? ' is-active' : ''; ?>"
                                    type="button"
                                    data-target="va-pc-panel-<?php echo esc_attr( $slug ); ?>"
                                    data-color="<?php echo esc_attr( $plan['color'] ); ?>"
                                    style="--plan-color:<?php echo esc_attr( $plan['color'] ); ?>;--plan-bg:<?php echo esc_attr( $plan['bg'] ); ?>;">
                                <span class="va-pc-nav__badge">
                                    <span class="va-pc-nav__icon"><?php echo esc_html( $plan['icon'] ); ?></span>
                                    <span class="va-pc-nav__titlewrap">
                                        <span class="va-pc-nav__title"><?php echo esc_html( $plan['label'] ); ?></span>
                                        <span class="va-pc-nav__subtitle"><?php echo esc_html( strtoupper( $slug ) ); ?></span>
                                    </span>
                                </span>
                                <span class="va-pc-nav__meta">
                                    <span><?php echo esc_html( $limit_label ); ?> limit</span>
                                    <span><?php echo esc_html( $basis_label ); ?></span>
                                    <span><?php echo esc_html( (string) (int) $plan['boost_cooldown'] ); ?> nap cooldown</span>
                                </span>
                            </button>
                            <?php endforeach; ?>

                            <button class="va-pc-nav__item va-pc-nav__item--global"
                                    type="button"
                                    data-target="va-pc-panel-global"
                                    data-color="#6ab7ff"
                                    style="--plan-color:#6ab7ff;--plan-bg:rgba(106,183,255,.12);">
                                <span class="va-pc-nav__badge">
                                    <span class="va-pc-nav__icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                                    </span>
                                    <span class="va-pc-nav__titlewrap">
                                        <span class="va-pc-nav__title">Globalis</span>
                                        <span class="va-pc-nav__subtitle">KOZOS LOGIKA</span>
                                    </span>
                                </span>
                                <span class="va-pc-nav__meta">
                                    <span>Boost badge</span>
                                    <span>rendszerszintu</span>
                                    <span>minden csomagra ervenyes</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="va-pc-sidebar__section va-pc-sidebar__section--summary">
                        <div class="va-pc-sidebar__label">Attekintes</div>
                        <div class="va-pc-mini-grid">
                            <div class="va-pc-mini-card">
                                <span class="va-pc-mini-card__value"><?php echo esc_html( (string) count( $plan_slugs ) ); ?></span>
                                <span class="va-pc-mini-card__label">Aktiv csomag</span>
                            </div>
                            <div class="va-pc-mini-card">
                                <span class="va-pc-mini-card__value"><?php echo ! empty( $global['boost_enabled'] ) ? 'ON' : 'OFF'; ?></span>
                                <span class="va-pc-mini-card__label">Boost rendszer</span>
                            </div>
                            <div class="va-pc-mini-card">
                                <span class="va-pc-mini-card__value"><?php echo esc_html( (string) (int) $global['boost_badge_window'] ); ?></span>
                                <span class="va-pc-mini-card__label">Badge nap</span>
                            </div>
                            <div class="va-pc-mini-card">
                                <span class="va-pc-mini-card__value"><?php echo esc_html( strtoupper( (string) $first_slug ) ); ?></span>
                                <span class="va-pc-mini-card__label">Nyito panel</span>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="va-pc-main">
                    <?php foreach ( $plan_slugs as $index => $slug ):
                        $plan = $plans[ $slug ] ?? $defaults[ $slug ];
                        $default = $defaults[ $slug ];
                        $limit_value = isset( $plan['monthly_limit'] ) ? (int) $plan['monthly_limit'] : 0;
                        $limit_display = $limit_value > 0 ? (string) $limit_value : 'Korlátlan';
                        $basis_label = ( isset( $plan['basis'] ) && $plan['basis'] === 'active' ) ? 'Aktiv hirdetesek' : 'Havi feladas';
                    ?>
                    <div class="va-pc-panel<?php echo 0 === $index ? ' is-active' : ''; ?>"
                         id="va-pc-panel-<?php echo esc_attr( $slug ); ?>"
                         data-slug="<?php echo esc_attr( $slug ); ?>"
                         style="--plan-color:<?php echo esc_attr( $plan['color'] ); ?>;--plan-bg:<?php echo esc_attr( $plan['bg'] ); ?>;">

                        <div class="va-pc-panel__hero">
                            <div class="va-pc-planchip">
                                <span class="va-pc-planchip__icon"><?php echo esc_html( $plan['icon'] ); ?></span>
                                <div>
                                    <div class="va-pc-planchip__title" data-preview-label><?php echo esc_html( $plan['label'] ); ?></div>
                                    <div class="va-pc-planchip__desc" data-preview-desc><?php echo esc_html( $plan['description'] ); ?></div>
                                </div>
                            </div>
                            <div class="va-pc-panel__hero-meta">
                                <span class="va-pc-token">Slug: <?php echo esc_html( $slug ); ?></span>
                                <span class="va-pc-token" data-summary-limit><?php echo esc_html( $limit_display ); ?> limit</span>
                                <span class="va-pc-token" data-summary-basis><?php echo esc_html( $basis_label ); ?></span>
                                <span class="va-pc-token" data-summary-cooldown><?php echo esc_html( (string) (int) $plan['boost_cooldown'] ); ?> nap cooldown</span>
                            </div>
                        </div>

                        <div class="va-pc-overview-grid">
                            <div class="va-pc-overview-card">
                                <span class="va-pc-overview-card__label">Használati logika</span>
                                <strong class="va-pc-overview-card__value" data-summary-basis-card><?php echo esc_html( $basis_label ); ?></strong>
                            </div>
                            <div class="va-pc-overview-card">
                                <span class="va-pc-overview-card__label">Limit</span>
                                <strong class="va-pc-overview-card__value" data-summary-limit-card><?php echo esc_html( $limit_display ); ?></strong>
                            </div>
                            <div class="va-pc-overview-card">
                                <span class="va-pc-overview-card__label">Cooldown</span>
                                <strong class="va-pc-overview-card__value" data-summary-cooldown-card><?php echo esc_html( (string) (int) $plan['boost_cooldown'] ); ?> nap</strong>
                            </div>
                            <div class="va-pc-overview-card">
                                <span class="va-pc-overview-card__label">Badge szín</span>
                                <strong class="va-pc-overview-card__value" data-summary-color-card><?php echo esc_html( $plan['color'] ); ?></strong>
                            </div>
                        </div>

                        <div class="va-pc-section-grid">
                            <section class="va-pc-card va-pc-card--appearance">
                                <div class="va-pc-card__head">
                                    <div>
                                        <h2 class="va-pc-card__title">Megjelenés</h2>
                                        <p class="va-pc-card__text">Ez határozza meg a badge vizuális karakterét és az adminban, frontendben látszó rövid szöveget.</p>
                                    </div>
                                    <div class="va-pc-livebadge" data-preview-badge>
                                        <span class="va-pc-livebadge__icon" data-preview-icon><?php echo esc_html( $plan['icon'] ); ?></span>
                                        <span class="va-pc-livebadge__label" data-preview-badge-label><?php echo esc_html( $plan['label'] ); ?></span>
                                    </div>
                                </div>
                                <div class="va-pc-form-grid va-pc-form-grid--appearance">
                                    <label class="va-pc-field va-pc-field--wide">
                                        <span class="va-pc-field__label">Megnevezés</span>
                                        <input type="text" class="va-pc-input" data-key="label" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['label'] ); ?>" placeholder="<?php echo esc_attr( $default['label'] ); ?>">
                                    </label>
                                    <label class="va-pc-field va-pc-field--icon">
                                        <span class="va-pc-field__label">Ikon</span>
                                        <input type="text" class="va-pc-input va-pc-input--icon" data-key="icon" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['icon'] ); ?>" placeholder="<?php echo esc_attr( $default['icon'] ); ?>" maxlength="8">
                                    </label>
                                    <label class="va-pc-field va-pc-field--color">
                                        <span class="va-pc-field__label">Szín</span>
                                        <span class="va-pc-color-field">
                                            <input type="text" class="va-pc-input va-pc-input--color va-color-input" data-key="color" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['color'] ); ?>" placeholder="<?php echo esc_attr( $default['color'] ); ?>">
                                        </span>
                                    </label>
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Háttér RGBA</span>
                                        <input type="text" class="va-pc-input" data-key="bg" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['bg'] ); ?>" placeholder="<?php echo esc_attr( $default['bg'] ); ?>">
                                    </label>
                                    <label class="va-pc-field va-pc-field--full">
                                        <span class="va-pc-field__label">Leírás</span>
                                        <input type="text" class="va-pc-input" data-key="description" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['description'] ); ?>" placeholder="<?php echo esc_attr( $default['description'] ); ?>">
                                    </label>
                                    <?php if ( $slug === 'platinum' ): ?>
                                    <label class="va-pc-field va-pc-field--full">
                                        <span class="va-pc-field__label">Egyedi rang címke (feladó panel)</span>
                                        <span class="va-pc-field__hint">Ha üres, az alap „Platina tag” feliratok jelennek meg. Írd be pl. Kereskedő, Viszonteladó – ez jelenik meg a hirdetés oldalon.</span>
                                        <input type="text" class="va-pc-input" data-key="seller_label" data-slug="platinum" value="<?php echo esc_attr( $plan['seller_label'] ?? '' ); ?>" placeholder="pl. Kereskedő, Viszonteladó">
                                    </label>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <section class="va-pc-card">
                                <div class="va-pc-card__head">
                                    <div>
                                        <h2 class="va-pc-card__title">Hirdetési limit</h2>
                                        <p class="va-pc-card__text">Azt határozza meg, hogy egyszerre aktív darabszámot vagy havi feladást számláljon a rendszer.</p>
                                    </div>
                                </div>
                                <div class="va-pc-form-grid va-pc-form-grid--compact">
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Limit</span>
                                        <span class="va-pc-field__hint">0 = korlátlan</span>
                                        <input type="number" class="va-pc-input va-pc-input--number" data-key="monthly_limit" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( (string) $limit_value ); ?>" min="0" max="9999">
                                    </label>
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Számlálás módja</span>
                                        <span class="va-pc-field__hint">aktív vagy havi</span>
                                        <select class="va-pc-select" data-key="basis" data-slug="<?php echo esc_attr( $slug ); ?>">
                                            <option value="active" <?php selected( $plan['basis'], 'active' ); ?>>Aktív hirdetések egyszerre</option>
                                            <option value="monthly" <?php selected( $plan['basis'], 'monthly' ); ?>>Havi feladott hirdetések</option>
                                        </select>
                                    </label>
                                </div>
                            </section>

                            <section class="va-pc-card">
                                <div class="va-pc-card__head">
                                    <div>
                                        <h2 class="va-pc-card__title">Boost / Kiemelés</h2>
                                        <p class="va-pc-card__text">A cooldown csomag-specifikus. A globális badge és kapcsoló a külön panelen állítható.</p>
                                    </div>
                                </div>
                                <div class="va-pc-form-grid va-pc-form-grid--compact">
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Cooldown</span>
                                        <span class="va-pc-field__hint">napokban mérve</span>
                                        <input type="number" class="va-pc-input va-pc-input--number" data-key="boost_cooldown" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( (string) (int) $plan['boost_cooldown'] ); ?>" min="1" max="365">
                                    </label>
                                </div>
                            </section>

                            <section class="va-pc-card va-pc-card--marketing">
                                <div class="va-pc-card__head">
                                    <div>
                                        <h2 class="va-pc-card__title">Ár és marketing</h2>
                                        <p class="va-pc-card__text">Nem a fizetési logikát kezeli, hanem a kommunikációs szövegeket és badge tartalmakat.</p>
                                    </div>
                                </div>
                                <div class="va-pc-form-grid">
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Havi ár</span>
                                        <input type="text" class="va-pc-input" data-key="price_monthly" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['price_monthly'] ?? '' ); ?>" placeholder="pl. 990 Ft/hó">
                                    </label>
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Éves ár</span>
                                        <input type="text" class="va-pc-input" data-key="price_yearly" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['price_yearly'] ?? '' ); ?>" placeholder="pl. 9900 Ft/év">
                                    </label>
                                    <label class="va-pc-field va-pc-field--full">
                                        <span class="va-pc-field__label">Promo badge szöveg</span>
                                        <input type="text" class="va-pc-input" data-key="badge_text" data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $plan['badge_text'] ?? '' ); ?>" placeholder="opcionális kiemelés, pl. Legjobb ár">
                                    </label>
                                </div>
                            </section>
                        </div>

                        <div class="va-pc-footerbar">
                            <button class="va-pc-reset-btn" type="button"
                                    data-slug="<?php echo esc_attr( $slug ); ?>"
                                    data-defaults="<?php echo esc_attr( wp_json_encode( [
                                        'label'          => $default['label'],
                                        'icon'           => $default['icon'],
                                        'color'          => $default['color'],
                                        'bg'             => $default['bg'],
                                        'monthly_limit'  => $default['monthly_limit'],
                                        'boost_cooldown' => $default['boost_cooldown'],
                                        'basis'          => $default['basis'],
                                        'description'    => $default['description'],
                                        'price_monthly'  => '',
                                        'price_yearly'   => '',
                                        'badge_text'     => '',
                                    ] ) ); ?>">
                                Alapértékek visszaállítása
                            </button>
                            <span class="va-pc-footerbar__note">A visszaállítás csak a jelenlegi panel mezőihez nyúl. Mentés után írjuk felül az adatbázist.</span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="va-pc-panel" id="va-pc-panel-global" style="--plan-color:#6ab7ff;--plan-bg:rgba(106,183,255,.12);">
                        <div class="va-pc-panel__hero">
                            <div class="va-pc-planchip va-pc-planchip--global">
                                <span class="va-pc-planchip__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                                </span>
                                <div>
                                    <div class="va-pc-planchip__title">Globális boost beállítások</div>
                                    <div class="va-pc-planchip__desc">A badge láthatóságát, a rendszer be-/kikapcsolását és a közös feliratot itt vezérelheted.</div>
                                </div>
                            </div>
                            <div class="va-pc-panel__hero-meta">
                                <span class="va-pc-token">Badge: <?php echo esc_html( (string) $global['boost_badge_text'] ); ?></span>
                                <span class="va-pc-token"><?php echo esc_html( (string) (int) $global['boost_badge_window'] ); ?> nap</span>
                                <span class="va-pc-token"><?php echo ! empty( $global['boost_enabled'] ) ? 'Bekapcsolva' : 'Kikapcsolva'; ?></span>
                            </div>
                        </div>

                        <div class="va-pc-section-grid va-pc-section-grid--global">
                            <section class="va-pc-card">
                                <div class="va-pc-card__head">
                                    <div>
                                        <h2 class="va-pc-card__title">Közös boost logika</h2>
                                        <p class="va-pc-card__text">A csomagszintű cooldown mellett ez szabja meg, hogy a badge meddig látszik és egyáltalán aktív-e a szolgáltatás.</p>
                                    </div>
                                </div>
                                <div class="va-pc-form-grid">
                                    <label class="va-pc-field">
                                        <span class="va-pc-field__label">Badge láthatóság</span>
                                        <span class="va-pc-field__hint">napban megadva</span>
                                        <input type="number" id="va-pc-global-window" class="va-pc-input va-pc-input--number" value="<?php echo esc_attr( (string) (int) $global['boost_badge_window'] ); ?>" min="1" max="365">
                                    </label>
                                    <label class="va-pc-field va-pc-field--wide">
                                        <span class="va-pc-field__label">Badge szöveg</span>
                                        <input type="text" id="va-pc-global-badgetext" class="va-pc-input" value="<?php echo esc_attr( $global['boost_badge_text'] ); ?>" placeholder="pl. Előre téve">
                                    </label>
                                    <label class="va-pc-field va-pc-field--switch">
                                        <span class="va-pc-field__label">Boost rendszer</span>
                                        <span class="va-pc-switch">
                                            <input type="checkbox" id="va-pc-global-boost-enabled" <?php checked( ! empty( $global['boost_enabled'] ) ); ?>>
                                            <span class="va-pc-switch__track"></span>
                                            <span class="va-pc-switch__text"><?php echo ! empty( $global['boost_enabled'] ) ? 'Bekapcsolva' : 'Kikapcsolva'; ?></span>
                                        </span>
                                    </label>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <style>
        .va-pc-wrap {
            max-width: 1520px;
        }
        .va-pc-hero {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 28px;
            margin-bottom: 18px;
            border: 1px solid var(--va-border2);
            border-radius: var(--va-radius);
            background:
                radial-gradient(circle at top right, rgba(255,0,0,.12), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01)),
                var(--va-bg2);
        }
        .va-pc-hero__eyebrow {
            display: inline-block;
            font-size: 11px;
            line-height: 1;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255,255,255,.55);
            margin-bottom: 10px;
        }
        .va-pc-hero__title {
            margin: 0 0 10px;
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            color: #fff;
        }
        .va-pc-hero__lead {
            margin: 0;
            max-width: 900px;
            font-size: 14px;
            line-height: 1.65;
            color: rgba(255,255,255,.66);
        }
        .va-pc-hero__actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            gap: 14px;
            min-width: 260px;
        }
        .va-pc-save-status {
            min-height: 18px;
            font-size: 13px;
            color: var(--va-muted);
            text-align: right;
        }
        .va-pc-save-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 20px;
            background: linear-gradient(180deg, #ff3838, #d90000) !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,.08) !important;
            border-radius: 999px !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            letter-spacing: .02em;
            box-shadow: 0 14px 36px rgba(255,0,0,.18);
        }
        .va-pc-save-btn:hover {
            opacity: .92;
            transform: translateY(-1px);
        }
        .va-pc-save-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }
        .va-pc-shell {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }
        .va-pc-sidebar {
            position: sticky;
            top: 72px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .va-pc-sidebar__section {
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.015)),
                var(--va-bg2);
            border: 1px solid var(--va-border);
            border-radius: var(--va-radius);
            padding: 16px;
        }
        .va-pc-sidebar__label {
            margin-bottom: 10px;
            font-size: 11px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: rgba(255,255,255,.48);
        }
        .va-pc-nav {
            display: grid;
            gap: 10px;
        }
        .va-pc-nav__item {
            display: grid;
            gap: 10px;
            width: 100%;
            padding: 14px 14px 13px;
            background: rgba(255,255,255,.02);
            border: 1px solid var(--va-border);
            border-radius: 18px;
            color: #fff;
            text-align: left;
            cursor: pointer;
            transition: border-color .2s ease, transform .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .va-pc-nav__item:hover {
            transform: translateY(-1px);
            border-color: rgba(255,255,255,.2);
            background: rgba(255,255,255,.045);
        }
        .va-pc-nav__item.is-active {
            border-color: color-mix(in srgb, var(--plan-color) 60%, rgba(255,255,255,.2));
            background: linear-gradient(180deg, color-mix(in srgb, var(--plan-bg) 100%, rgba(255,255,255,.03)), rgba(255,255,255,.03));
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.04), 0 16px 34px rgba(0,0,0,.22);
        }
        .va-pc-nav__badge {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .va-pc-nav__icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--plan-bg);
            color: var(--plan-color);
            font-size: 22px;
            flex-shrink: 0;
        }
        .va-pc-nav__item--global .va-pc-nav__icon {
            font-size: 0;
        }
        .va-pc-nav__titlewrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }
        .va-pc-nav__title {
            font-size: 16px;
            font-weight: 800;
        }
        .va-pc-nav__subtitle {
            font-size: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: rgba(255,255,255,.42);
        }
        .va-pc-nav__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .va-pc-nav__meta span {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(255,255,255,.05);
            font-size: 11px;
            color: rgba(255,255,255,.72);
        }
        .va-pc-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .va-pc-mini-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 12px;
            border: 1px solid var(--va-border);
            border-radius: 16px;
            background: rgba(255,255,255,.025);
        }
        .va-pc-mini-card__value {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }
        .va-pc-mini-card__label {
            font-size: 11px;
            color: rgba(255,255,255,.48);
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .va-pc-main {
            min-width: 0;
        }
        .va-pc-panel {
            display: none;
        }
        .va-pc-panel.is-active {
            display: block;
        }
        .va-pc-panel__hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 16px;
            padding: 18px 20px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01)),
                var(--va-bg2);
            border: 1px solid var(--va-border);
            border-radius: 22px;
        }
        .va-pc-planchip {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .va-pc-planchip__icon {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: var(--plan-bg);
            color: var(--plan-color);
            font-size: 28px;
            flex-shrink: 0;
        }
        .va-pc-planchip--global .va-pc-planchip__icon {
            font-size: 0;
        }
        .va-pc-planchip__title {
            font-size: 24px;
            line-height: 1.1;
            font-weight: 800;
            color: #fff;
            margin-bottom: 6px;
        }
        .va-pc-planchip__desc {
            font-size: 13px;
            line-height: 1.6;
            color: rgba(255,255,255,.64);
            max-width: 700px;
        }
        .va-pc-panel__hero-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
            max-width: 420px;
        }
        .va-pc-token {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04);
            color: rgba(255,255,255,.76);
            font-size: 12px;
            font-weight: 600;
        }
        .va-pc-overview-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .va-pc-overview-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px 16px;
            border: 1px solid var(--va-border);
            border-radius: 18px;
            background: rgba(255,255,255,.025);
        }
        .va-pc-overview-card__label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: rgba(255,255,255,.45);
        }
        .va-pc-overview-card__value {
            font-size: 18px;
            color: #fff;
        }
        .va-pc-section-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
            gap: 16px;
        }
        .va-pc-section-grid--global {
            grid-template-columns: minmax(0, 1fr);
        }
        .va-pc-card {
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-width: 0;
            padding: 20px;
            border: 1px solid var(--va-border);
            border-radius: 22px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01)),
                var(--va-bg2);
        }
        .va-pc-card--appearance {
            grid-row: span 2;
        }
        .va-pc-card--marketing {
            grid-column: 1 / -1;
        }
        .va-pc-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }
        .va-pc-card__title {
            margin: 0 0 8px;
            font-size: 19px;
            line-height: 1.2;
            font-weight: 800;
            color: #fff;
        }
        .va-pc-card__text {
            margin: 0;
            max-width: 620px;
            font-size: 13px;
            line-height: 1.65;
            color: rgba(255,255,255,.58);
        }
        .va-pc-livebadge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            background: var(--plan-bg);
            border: 1px solid color-mix(in srgb, var(--plan-color) 36%, rgba(255,255,255,.08));
            color: var(--plan-color);
            font-weight: 800;
            white-space: nowrap;
        }
        .va-pc-livebadge__icon {
            font-size: 18px;
        }
        .va-pc-livebadge__label {
            font-size: 13px;
        }
        .va-pc-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .va-pc-form-grid--appearance {
            grid-template-columns: minmax(0, 1.2fr) 120px minmax(220px, .8fr) minmax(220px, .8fr);
        }
        .va-pc-form-grid--compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .va-pc-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
            min-width: 0;
        }
        .va-pc-field--full {
            grid-column: 1 / -1;
        }
        .va-pc-field--wide {
            grid-column: span 2;
        }
        .va-pc-field--icon {
            max-width: 120px;
        }
        .va-pc-field--color {
            align-items: flex-start;
            justify-content: center;
            padding-left: 18px;
        }
        .va-pc-field__label {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255,255,255,.74);
        }
        .va-pc-field__hint {
            margin-top: -4px;
            font-size: 11px;
            color: rgba(255,255,255,.38);
        }
        .va-pc-input,
        .va-pc-select {
            width: 100%;
            min-height: 46px;
            padding: 0 14px !important;
            border: 1px solid var(--va-border2) !important;
            border-radius: 14px !important;
            background: rgba(255,255,255,.025) !important;
            color: #fff !important;
            font-size: 14px !important;
            font-family: inherit !important;
            box-shadow: none !important;
        }
        .va-pc-input:focus,
        .va-pc-select:focus {
            border-color: var(--va-accent) !important;
            box-shadow: 0 0 0 4px rgba(255,32,32,.12) !important;
        }
        .va-pc-input--number {
            max-width: 160px;
        }
        .va-pc-input--icon {
            text-align: center;
            font-size: 22px !important;
            padding: 0 !important;
        }
        .va-pc-color-field {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .va-pc-colorpicker {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            border: 1px solid var(--va-border2);
            border-radius: 14px;
            background: none;
            padding: 3px;
            cursor: pointer;
        }
        .va-pc-switch {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .va-pc-switch input {
            display: none;
        }
        .va-pc-switch__track {
            position: relative;
            width: 48px;
            height: 28px;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            transition: background .2s ease;
            flex-shrink: 0;
        }
        .va-pc-switch__track::after {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            transition: transform .2s ease;
        }
        .va-pc-switch input:checked + .va-pc-switch__track {
            background: var(--va-accent);
        }
        .va-pc-switch input:checked + .va-pc-switch__track::after {
            transform: translateX(20px);
        }
        .va-pc-switch__text {
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,.76);
        }
        .va-pc-footerbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-top: 16px;
            padding: 16px 20px;
            border: 1px solid var(--va-border);
            border-radius: 18px;
            background: rgba(255,255,255,.02);
        }
        .va-pc-reset-btn {
            min-height: 42px;
            padding: 0 16px;
            border: 1px solid var(--va-border2);
            border-radius: 999px;
            background: transparent;
            color: rgba(255,255,255,.82);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
        }
        .va-pc-reset-btn:hover {
            border-color: var(--va-accent);
            color: #fff;
        }
        .va-pc-footerbar__note {
            font-size: 12px;
            line-height: 1.5;
            color: rgba(255,255,255,.45);
            text-align: right;
        }
        @media (max-width: 1320px) {
            .va-pc-shell {
                grid-template-columns: 280px minmax(0, 1fr);
            }
            .va-pc-form-grid--appearance {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .va-pc-field--wide {
                grid-column: span 1;
            }
        }
        @media (max-width: 1120px) {
            .va-pc-shell {
                grid-template-columns: 1fr;
            }
            .va-pc-sidebar {
                position: static;
            }
            .va-pc-overview-grid,
            .va-pc-section-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .va-pc-card--appearance,
            .va-pc-card--marketing {
                grid-column: auto;
                grid-row: auto;
            }
        }
        @media (max-width: 820px) {
            .va-pc-hero,
            .va-pc-panel__hero,
            .va-pc-footerbar,
            .va-pc-card__head {
                flex-direction: column;
                align-items: stretch;
            }
            .va-pc-hero__actions,
            .va-pc-panel__hero-meta {
                align-items: flex-start;
                justify-content: flex-start;
                max-width: none;
            }
            .va-pc-overview-grid,
            .va-pc-section-grid,
            .va-pc-form-grid,
            .va-pc-form-grid--appearance,
            .va-pc-form-grid--compact {
                grid-template-columns: 1fr;
            }
            .va-pc-mini-grid {
                grid-template-columns: 1fr 1fr;
            }
            .va-pc-field--wide {
                grid-column: auto;
            }
            .va-pc-input--number,
            .va-pc-field--icon {
                max-width: none;
            }
            .va-pc-footerbar__note,
            .va-pc-save-status {
                text-align: left;
            }
        }
        @media (max-width: 560px) {
            .va-pc-wrap {
                margin-right: 10px;
            }
            .va-pc-hero,
            .va-pc-sidebar__section,
            .va-pc-card,
            .va-pc-panel__hero,
            .va-pc-footerbar {
                padding: 16px;
                border-radius: 18px;
            }
            .va-pc-hero__title {
                font-size: 28px;
            }
            .va-pc-mini-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>

        <script>
        (function(){
            var navItems = Array.prototype.slice.call(document.querySelectorAll('.va-pc-nav__item'));
            var panels = Array.prototype.slice.call(document.querySelectorAll('.va-pc-panel'));
            var saveBtn = document.getElementById('va-pc-save-all');
            var saveStatus = document.getElementById('va-pc-save-status');
            if(!saveBtn){
                return;
            }

            function setActivePanel(targetId){
                navItems.forEach(function(item){
                    item.classList.toggle('is-active', item.dataset.target === targetId);
                });
                panels.forEach(function(panel){
                    panel.classList.toggle('is-active', panel.id === targetId);
                });
            }

            navItems.forEach(function(item){
                item.addEventListener('click', function(){
                    setActivePanel(this.dataset.target);
                });
            });

            function updateSidebarSummary(slug, panel){
                var nav = document.querySelector('.va-pc-nav__item[data-target="va-pc-panel-' + slug + '"]');
                if(!nav || !panel){
                    return;
                }
                var label = panel.querySelector('[data-key="label"]');
                var icon = panel.querySelector('[data-key="icon"]');
                var color = panel.querySelector('.va-pc-input[data-key="color"]');
                var limit = panel.querySelector('[data-key="monthly_limit"]');
                var basis = panel.querySelector('[data-key="basis"]');
                var cooldown = panel.querySelector('[data-key="boost_cooldown"]');
                var title = nav.querySelector('.va-pc-nav__title');
                var navIcon = nav.querySelector('.va-pc-nav__icon');
                var meta = nav.querySelectorAll('.va-pc-nav__meta span');
                var basisText = basis && basis.value === 'active' ? 'aktív' : 'havi';
                var limitText = limit && parseInt(limit.value || '0', 10) > 0 ? limit.value : 'Korlátlan';
                var cooldownText = cooldown ? (cooldown.value || '0') : '0';
                if(title && label){ title.textContent = label.value || ''; }
                if(navIcon && icon){ navIcon.textContent = icon.value || ''; }
                if(color){ nav.style.setProperty('--plan-color', color.value || '#ffffff'); }
                if(meta.length >= 3){
                    meta[0].textContent = limitText + ' limit';
                    meta[1].textContent = basisText;
                    meta[2].textContent = cooldownText + ' nap cooldown';
                }
            }

            function updatePlanPreview(slug){
                var panel = document.getElementById('va-pc-panel-' + slug);
                if(!panel){
                    return;
                }
                var iconInput = panel.querySelector('[data-key="icon"]');
                var labelInput = panel.querySelector('[data-key="label"]');
                var descInput = panel.querySelector('[data-key="description"]');
                var colorInput = panel.querySelector('.va-pc-input[data-key="color"]');
                var limitInput = panel.querySelector('[data-key="monthly_limit"]');
                var basisInput = panel.querySelector('[data-key="basis"]');
                var cooldownInput = panel.querySelector('[data-key="boost_cooldown"]');
                var color = colorInput ? colorInput.value : '#ffffff';
                var limitValue = limitInput ? parseInt(limitInput.value || '0', 10) : 0;
                var limitText = limitValue > 0 ? String(limitValue) : 'Korlátlan';
                var basisText = basisInput && basisInput.value === 'active' ? 'Aktív hirdetések' : 'Havi feladás';
                var cooldownText = cooldownInput ? (cooldownInput.value || '0') + ' nap' : '0 nap';

                panel.style.setProperty('--plan-color', color);
                panel.querySelectorAll('[data-preview-icon]').forEach(function(node){ node.textContent = iconInput ? iconInput.value : ''; });
                panel.querySelectorAll('[data-preview-label],[data-preview-badge-label]').forEach(function(node){ node.textContent = labelInput ? labelInput.value : ''; });
                panel.querySelectorAll('[data-preview-desc]').forEach(function(node){ node.textContent = descInput ? descInput.value : ''; });
                var colorCard = panel.querySelector('[data-summary-color-card]');
                if(colorCard){ colorCard.textContent = color; }
                var limitToken = panel.querySelector('[data-summary-limit]');
                var limitCard = panel.querySelector('[data-summary-limit-card]');
                if(limitToken){ limitToken.textContent = limitText + ' limit'; }
                if(limitCard){ limitCard.textContent = limitText; }
                var basisToken = panel.querySelector('[data-summary-basis]');
                var basisCard = panel.querySelector('[data-summary-basis-card]');
                if(basisToken){ basisToken.textContent = basisText; }
                if(basisCard){ basisCard.textContent = basisText; }
                var cooldownToken = panel.querySelector('[data-summary-cooldown]');
                var cooldownCard = panel.querySelector('[data-summary-cooldown-card]');
                if(cooldownToken){ cooldownToken.textContent = cooldownText + ' cooldown'; }
                if(cooldownCard){ cooldownCard.textContent = cooldownText; }
                updateSidebarSummary(slug, panel);
            }

            document.querySelectorAll('.va-pc-input[data-key="color"]').forEach(function(input){
                input.addEventListener('input', function(){
                    updatePlanPreview(input.dataset.slug);
                });
                input.addEventListener('change', function(){
                    updatePlanPreview(input.dataset.slug);
                });
            });

            document.querySelectorAll('.va-pc-panel[data-slug] [data-key]').forEach(function(input){
                if(input.dataset.key === 'color'){
                    return;
                }
                var eventName = input.tagName === 'SELECT' ? 'change' : 'input';
                input.addEventListener(eventName, function(){
                    updatePlanPreview(input.dataset.slug);
                });
            });

            document.querySelectorAll('.va-pc-reset-btn').forEach(function(button){
                button.addEventListener('click', function(){
                    var defaults = JSON.parse(button.dataset.defaults || '{}');
                    var panel = document.getElementById('va-pc-panel-' + button.dataset.slug);
                    if(!panel){
                        return;
                    }
                    Object.keys(defaults).forEach(function(key){
                        var field = panel.querySelector('[data-key="' + key + '"]');
                        if(field){
                            field.value = defaults[key];
                        }
                        if(key === 'color'){
                            updatePlanPreview(button.dataset.slug);
                        }
                    });
                    updatePlanPreview(button.dataset.slug);
                });
            });

            var globalToggle = document.getElementById('va-pc-global-boost-enabled');
            if(globalToggle){
                globalToggle.addEventListener('change', function(){
                    var text = document.querySelector('.va-pc-switch__text');
                    if(text){
                        text.textContent = globalToggle.checked ? 'Bekapcsolva' : 'Kikapcsolva';
                    }
                });
            }

            function buildPayload(){
                var payload = {};
                document.querySelectorAll('.va-pc-panel[data-slug]').forEach(function(panel){
                    var slug = panel.dataset.slug;
                    var item = {};
                    panel.querySelectorAll('[data-key]').forEach(function(field){
                        if(field.classList.contains('va-cpick__swatch')){
                            return;
                        }
                        item[field.dataset.key] = field.value;
                    });
                    payload[slug] = item;
                });
                payload._global = {
                    boost_badge_window: (document.getElementById('va-pc-global-window') || { value: 7 }).value,
                    boost_badge_text: (document.getElementById('va-pc-global-badgetext') || { value: '' }).value,
                    boost_enabled: globalToggle && globalToggle.checked ? 1 : 0
                };
                return payload;
            }

            saveBtn.addEventListener('click', function(){
                saveBtn.disabled = true;
                if(saveStatus){
                    saveStatus.textContent = 'Mentes folyamatban...';
                    saveStatus.style.color = 'rgba(255,255,255,.7)';
                }
                var data = new URLSearchParams({
                    action: 'va_admin_save_plan_cfg',
                    nonce: saveBtn.dataset.nonce,
                    plans: JSON.stringify(buildPayload())
                });
                fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: data.toString()
                })
                .then(function(response){ return response.json(); })
                .then(function(result){
                    saveBtn.disabled = false;
                    if(saveStatus){
                        if(result.success){
                            saveStatus.textContent = 'Valtozasok elmentve.';
                            saveStatus.style.color = '#8cffb6';
                        } else {
                            saveStatus.textContent = result.data && result.data.message ? result.data.message : 'Mentesi hiba tortent.';
                            saveStatus.style.color = '#ff7f7f';
                        }
                    }
                    window.setTimeout(function(){
                        if(saveStatus){
                            saveStatus.textContent = '';
                        }
                    }, 4000);
                })
                .catch(function(){
                    saveBtn.disabled = false;
                    if(saveStatus){
                        saveStatus.textContent = 'Halozati hiba miatt a mentes nem sikerult.';
                        saveStatus.style.color = '#ff7f7f';
                    }
                });
            });

            document.querySelectorAll('.va-pc-panel[data-slug]').forEach(function(panel){
                updatePlanPreview(panel.dataset.slug);
            });
            setActivePanel('va-pc-panel-<?php echo esc_js( $first_slug ); ?>');
            if(typeof $ !== 'undefined') {
                $(function(){
                    if(typeof window.vaInitColorPickers === 'function'){
                        window.vaInitColorPickers(document.querySelector('.va-pc-wrap'));
                    }
                });
            }
        </script>
        <?php
    }

    /* ── Back-to-top gomb beállítások ─────────────────────── */
    public static function render_btt(): void {
        $styles = [
            'circle'   => [ 'label' => 'Circle – teli kör',          'preview' => '⬤' ],
            'rounded'  => [ 'label' => 'Rounded – lekerekített',      'preview' => '▣' ],
            'square'   => [ 'label' => 'Square – szögletes',          'preview' => '■' ],
            'pill'     => [ 'label' => 'Pill – hosszított oval',      'preview' => '💊' ],
            'ghost'    => [ 'label' => 'Ghost – átlátszó keret',      'preview' => '○' ],
            'glass'    => [ 'label' => 'Glass – frosted glass',       'preview' => '🔮' ],
            'neon'     => [ 'label' => 'Neon – izzó glow',            'preview' => '💡' ],
            'minimal'  => [ 'label' => 'Minimal – csak ikon',         'preview' => '↑' ],
            'floating' => [ 'label' => 'Floating – lebegő árnyék',    'preview' => '🎈' ],
            'arrow'    => [ 'label' => 'Arrow – nyíl badge',          'preview' => '▲' ],
        ];

        $cur_style       = self::get_display_option( 'va_btt_style',        'circle' );
        $cur_icon        = self::get_display_option( 'va_btt_icon',          'fa-solid fa-chevron-up' );
        $cur_color       = self::get_display_option( 'va_btt_color',         '#ff0000' );
        $cur_border      = self::get_display_option( 'va_btt_border_color',  '#ff0000' );
        $cur_txtcolor    = self::get_display_option( 'va_btt_text_color',    '#ffffff' );
        $cur_size        = self::get_display_option( 'va_btt_size',          '48' );
        $cur_pos         = self::get_display_option( 'va_btt_position',      'right' );
        $cur_ox          = self::get_display_option( 'va_btt_offset_x',      '28' );
        $cur_oy          = self::get_display_option( 'va_btt_offset_y',      '28' );
        $cur_after       = self::get_display_option( 'va_btt_show_after',    '300' );
        $cur_enabled     = self::get_display_option( 'va_btt_enabled',       '1' );
        ?>
        <div class="wrap va-admin-wrap">
            <h1>⬆ Tetejére gomb</h1>
            <p class="description">Az oldal jobb (vagy bal) sarkában megjelenő „vissza a tetejére" gomb kinézete és viselkedése.</p>
            <?php settings_errors( 'va_btt_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_btt_settings' ); ?>

                <table class="form-table">
                    <tr><th>Bekapcsolva</th><td>
                        <label><input type="checkbox" name="va_btt_enabled" value="1" <?php checked( $cur_enabled, '1' ); ?>> Gomb megjelenítése a frontenden</label>
                    </td></tr>
                    <tr><th>Megjelenési stílus</th><td>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:4px;">
                        <?php foreach ( $styles as $key => $s ) : ?>
                            <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:10px;border:2px solid <?php echo $cur_style === $key ? 'var(--va-accent)' : 'var(--va-border)'; ?>;border-radius:10px;background:var(--va-bg3);min-width:80px;text-align:center;">
                                <input type="radio" name="va_btt_style" value="<?php echo esc_attr($key); ?>" <?php checked($cur_style, $key); ?> style="display:none;">
                                <span style="font-size:22px;"><?php echo $s['preview']; ?></span>
                                <span style="font-size:11px;color:var(--va-muted);"><?php echo esc_html($s['label']); ?></span>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </td></tr>
                    <tr><th>Font Awesome ikon</th><td>
                        <p class="description" style="margin-bottom:8px;">Írj be egy <a href="https://fontawesome.com/icons" target="_blank">Font Awesome 6</a> ikonnevet, pl. <code>fa-solid fa-chevron-up</code> – vagy kattints az alábbi előre beállítottak egyikére:</p>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                            <input type="text" id="va_btt_icon_input" name="va_btt_icon" value="<?php echo esc_attr($cur_icon); ?>" style="width:260px;" placeholder="pl. fa-solid fa-chevron-up">
                            <span style="font-size:22px;"><i id="va_btt_icon_preview" class="<?php echo esc_attr($cur_icon); ?>" style="color:var(--va-accent);"></i></span>
                        </div>
                        <?php
                        $fa_suggestions = [
                            'fa-solid fa-chevron-up'    => 'Chevron',
                            'fa-solid fa-arrow-up'      => 'Nyíl',
                            'fa-solid fa-rocket'        => 'Rakéta',
                            'fa-solid fa-house'         => 'Ház',
                            'fa-solid fa-star'          => 'Csillag',
                            'fa-solid fa-fire'          => 'Láng',
                            'fa-solid fa-paw'           => 'Mancs',
                            'fa-solid fa-leaf'          => 'Levél',
                            'fa-solid fa-circle-up'     => 'Kör nyíl',
                            'fa-solid fa-angles-up'     => 'Dupla nyíl',
                            'fa-solid fa-gun'           => 'Puska',
                            'fa-solid fa-feather'       => 'Toll',
                            'fa-solid fa-tree'          => 'Fa',
                            'fa-solid fa-crow'          => 'Varjú',
                            'fa-solid fa-fish'          => 'Hal',
                            'fa-solid fa-dog'           => 'Kutya',
                            'fa-solid fa-horse'         => 'Ló',
                            'fa-solid fa-bug'           => 'Bogár',
                            'fa-solid fa-khanda'        => 'Khanda',
                            'fa-regular fa-circle-up'   => 'Kör nyíl (körvonal)',
                        ];
                        ?>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ( $fa_suggestions as $cls => $lbl ) : ?>
                            <button type="button" class="va-btt-fa-pick" data-cls="<?php echo esc_attr($cls); ?>"
                                style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:10px 12px;border:2px solid <?php echo $cur_icon === $cls ? 'var(--va-accent)' : 'var(--va-border)'; ?>;border-radius:10px;background:var(--va-bg3);cursor:pointer;min-width:64px;">
                                <i class="<?php echo esc_attr($cls); ?>" style="font-size:20px;color:var(--va-accent);"></i>
                                <span style="font-size:10px;color:var(--va-muted);"><?php echo esc_html($lbl); ?></span>
                            </button>
                        <?php endforeach; ?>
                        </div>
                    </td></tr>
                    <?php self::field_color( 'va_btt_color',        'Gomb háttér színe',   $cur_color ); ?>
                    <?php self::field_color( 'va_btt_border_color', 'Keret (border) színe',$cur_border ); ?>
                    <?php self::field_color( 'va_btt_text_color',   'Ikon színe',          $cur_txtcolor ); ?>
                    <tr><th>Méret (px)</th><td>
                        <input type="number" name="va_btt_size" value="<?php echo esc_attr($cur_size); ?>" min="32" max="80" class="small-text">
                        <p class="description">A gomb átmérője pixelben (32–80).</p>
                    </td></tr>
                    <tr><th>Pozíció</th><td>
                        <select name="va_btt_position">
                            <option value="right" <?php selected($cur_pos,'right'); ?>>Jobb oldal</option>
                            <option value="left"  <?php selected($cur_pos,'left');  ?>>Bal oldal</option>
                        </select>
                    </td></tr>
                    <tr><th>Vízszintes eltolás (px)</th><td>
                        <input type="number" name="va_btt_offset_x" value="<?php echo esc_attr($cur_ox); ?>" min="0" max="120" class="small-text">
                    </td></tr>
                    <tr><th>Függőleges eltolás (px)</th><td>
                        <input type="number" name="va_btt_offset_y" value="<?php echo esc_attr($cur_oy); ?>" min="0" max="120" class="small-text">
                    </td></tr>
                    <tr><th>Megjelenés görgetés után (px)</th><td>
                        <input type="number" name="va_btt_show_after" value="<?php echo esc_attr($cur_after); ?>" min="0" max="2000" class="small-text">
                        <p class="description">Ennyi pixel görgetés után jelenik meg a gomb.</p>
                    </td></tr>
                </table>

                <?php submit_button( '💾 Mentés' ); ?>
            </form>
        </div>
        <script>
        // Stílus radio kártya highlight
        document.querySelectorAll('[name="va_btt_style"]').forEach(function(r){
            r.addEventListener('change',function(){
                document.querySelectorAll('[name="va_btt_style"]').forEach(function(x){
                    x.closest('label').style.borderColor = x.checked ? 'var(--va-accent)' : 'var(--va-border)';
                });
            });
        });
        // FA ikon gyors választó
        var iconInput   = document.getElementById('va_btt_icon_input');
        var iconPreview = document.getElementById('va_btt_icon_preview');
        document.querySelectorAll('.va-btt-fa-pick').forEach(function(btn){
            btn.addEventListener('click', function(){
                var cls = btn.dataset.cls;
                iconInput.value = cls;
                iconPreview.className = cls;
                document.querySelectorAll('.va-btt-fa-pick').forEach(function(b){
                    b.style.borderColor = b.dataset.cls === cls ? 'var(--va-accent)' : 'var(--va-border)';
                });
            });
        });
        iconInput && iconInput.addEventListener('input', function(){
            iconPreview.className = iconInput.value.trim();
            document.querySelectorAll('.va-btt-fa-pick').forEach(function(b){
                b.style.borderColor = b.dataset.cls === iconInput.value.trim() ? 'var(--va-accent)' : 'var(--va-border)';
            });
        });
        if(typeof $ !== 'undefined') { $(function(){ if(typeof window.vaInitColorPickers === 'function') window.vaInitColorPickers(); }); }
        </script>
        <?php
    }


    /* ══════════════════════════════════════════════════════════
     * KÁRTYA STÍLUS SZERKESZTŐ
     * ══════════════════════════════════════════════════════════ */

    public static function get_card_defaults(): array {
        return [
            // Kártya konténer
            'card_bg'               => '#141414',
            'card_border_color'     => 'rgba(255,255,255,.08)',
            'card_border_width'     => 1,
            'card_radius'           => 12,
            // Hover
            'hover_lift'            => 2,
            'hover_border_width'    => 2,
            'hover_border_color'    => 'rgba(255,0,0,.85)',
            // Kép
            'img_aspect'            => '4/3',
            // Kártya törzs
            'body_pad_x'            => 14,
            'body_pad_y'            => 14,
            'body_gap'              => 6,
            // Cím
            'title_color'           => '#ffffff',
            'title_font_size'       => 15,
            'title_font_weight'     => 700,
            'title_font_family'     => 'inherit',
            'title_line_clamp'      => 2,
            // Ár
            'price_color'           => '#ff2a2a',
            'price_font_size'       => 17,
            'price_font_weight'     => 800,
            'price_font_family'     => 'inherit',
            // Meta sor
            'meta_color'            => 'rgba(255,255,255,.38)',
            'meta_font_size'        => 12,
            // Kiemelt badge
            'badge_featured_bg'     => 'linear-gradient(135deg,#3a2800,#1e1400)',
            'badge_featured_color'  => '#ffc840',
            'badge_featured_border' => 'rgba(255,180,0,.5)',
            'badge_featured_radius' => 20,
            'badge_featured_size'   => 11,
            // Boost badge
            'badge_boost_bg'        => 'rgba(255,42,42,.18)',
            'badge_boost_color'     => '#ff2a2a',
            'badge_boost_border'    => 'rgba(255,42,42,.4)',
            'badge_boost_radius'    => 20,
            'badge_boost_size'      => 11,
            // Watchlist gomb
            'watchlist_color'       => '#ff2a2a',
            'watchlist_bg'          => 'rgba(0,0,0,.62)',
            'watchlist_border'      => 'rgba(255,0,0,.45)',
            'watchlist_size'        => 30,
        ];
    }

    public static function output_card_css(): void {
        $saved = get_option( 'va_card_styles', '' );
        $data  = $saved ? json_decode( $saved, true ) : [];
        if ( ! is_array( $data ) ) $data = [];
        $d = array_merge( self::get_card_defaults(), $data );
        
        // DEBUG: ellenőrzés, hogy output_card_css lezajlik-e
        error_log( 'DEBUG: output_card_css() called. Saved option length: ' . strlen( $saved ) . ', merged data count: ' . count( $d ) );

        $ff_title = ( ! empty( $d['title_font_family'] ) && $d['title_font_family'] !== 'inherit' )
            ? "font-family:{$d['title_font_family']};" : '';
        $ff_price = ( ! empty( $d['price_font_family'] ) && $d['price_font_family'] !== 'inherit' )
            ? "font-family:{$d['price_font_family']};" : '';

        $bw = (int) $d['card_border_width'];
        echo "\n<style id=\"va-card-styles\">\n";
        echo ".va-card{background:{$d['card_bg']};border:{$bw}px solid {$d['card_border_color']};border-radius:{$d['card_radius']}px;}\n";
        echo ".va-card:hover,.va-card:focus-within{transform:translateY(-{$d['hover_lift']}px);border-color:{$d['card_border_color']};box-shadow:none;}\n";
        $hbw = (int) $d['hover_border_width'];
        echo ".va-card:hover::after,.va-card:focus-within::after{border-color:{$d['hover_border_color']};border-width:{$hbw}px;box-shadow:none;}\n";
        echo ".va-card__img-wrap{aspect-ratio:{$d['img_aspect']};}\n";
        echo ".va-card__body{padding:{$d['body_pad_y']}px {$d['body_pad_x']}px;gap:{$d['body_gap']}px;}\n";
        echo ".va-card__title{color:{$d['title_color']};font-size:{$d['title_font_size']}px;font-weight:{$d['title_font_weight']};{$ff_title}-webkit-line-clamp:{$d['title_line_clamp']};}\n";
        echo ".va-card__title a{color:{$d['title_color']};}\n";
        echo ".va-card__price{color:{$d['price_color']};font-size:{$d['price_font_size']}px;font-weight:{$d['price_font_weight']};{$ff_price}}\n";
        echo ".va-card__meta{color:{$d['meta_color']};font-size:{$d['meta_font_size']}px;}\n";
        echo ".va-card__badge--featured{background:{$d['badge_featured_bg']};color:{$d['badge_featured_color']};border-color:{$d['badge_featured_border']};border-radius:{$d['badge_featured_radius']}px;font-size:{$d['badge_featured_size']}px;}\n";
        echo ".va-card__badge--boost{background:{$d['badge_boost_bg']};color:{$d['badge_boost_color']};border-color:{$d['badge_boost_border']};border-radius:{$d['badge_boost_radius']}px;font-size:{$d['badge_boost_size']}px;}\n";
        echo ".va-card__watchlist{color:{$d['watchlist_color']};background:{$d['watchlist_bg']};border-color:{$d['watchlist_border']};width:{$d['watchlist_size']}px;height:{$d['watchlist_size']}px;}\n";
        echo "</style>\n";
    }

    public static function handle_save_card_styles_ajax(): void {
        check_ajax_referer( 'va_save_card_styles_ajax' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'msg' => 'Hozzáférés megtagadva.' ] );
        }
        $json = sanitize_textarea_field( wp_unslash( $_POST['va_card_styles_json'] ?? '' ) );
        $data = $json ? json_decode( $json, true ) : null;
        if ( is_array( $data ) && ! empty( $data ) ) {
            update_option( 'va_card_styles', wp_json_encode( $data ) );
            wp_send_json_success( [ 'msg' => 'Mentve!' ] );
        } else {
            wp_send_json_error( [ 'msg' => 'Érvénytelen adat. JSON: ' . substr( $json, 0, 60 ) ] );
        }
    }

    public static function handle_save_card_styles(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        check_admin_referer( 'va_save_card_styles' );
        $json = sanitize_textarea_field( wp_unslash( $_POST['va_card_styles_json'] ?? '' ) );
        error_log( 'DEBUG: handle_save_card_styles() POST json length: ' . strlen( $json ) . ', raw: ' . substr( $json, 0, 100 ) );
        $data = $json ? json_decode( $json, true ) : null;
        if ( is_array( $data ) ) {
            update_option( 'va_card_styles', wp_json_encode( $data ) );
            error_log( 'DEBUG: Saved va_card_styles option. Data keys: ' . implode( ',', array_keys( $data ) ) );
        } else {
            error_log( 'DEBUG: JSON decode failed or not array. Data: ' . var_export( $data, true ) );
        }
        wp_redirect( add_query_arg( [ 'page' => 'vadaszapro-cards', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public static function render_card_designer(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        $defaults = self::get_card_defaults();
        $saved    = get_option( 'va_card_styles', '' );
        $saved_arr = $saved ? json_decode( $saved, true ) : [];
        if ( ! is_array( $saved_arr ) ) $saved_arr = [];
        $d = array_merge( $defaults, $saved_arr );

        $is_saved = isset( $_GET['saved'] );

        // Font lista (újrahasználjuk a pill szerkesztő fontjait)
        $fonts = [
            'inherit' => '– Alapértelmezett (örökölt)',
            'system-ui, sans-serif' => '– System UI',
            'Arial, sans-serif' => '– Arial',
            "'Helvetica Neue', sans-serif" => '– Helvetica Neue',
            "'Open Sans', sans-serif" => 'Open Sans',
            "'Poppins', sans-serif" => 'Poppins',
            "'Lato', sans-serif" => 'Lato',
            "'Inter', sans-serif" => 'Inter',
            "'Roboto', sans-serif" => 'Roboto',
            "'Nunito', sans-serif" => 'Nunito',
            "'Montserrat', sans-serif" => 'Montserrat',
            "'Raleway', sans-serif" => 'Raleway',
            "'Oswald', sans-serif" => 'Oswald',
            "'DM Sans', sans-serif" => 'DM Sans',
            "'Manrope', sans-serif" => 'Manrope',
            "'Work Sans', sans-serif" => 'Work Sans',
            "'Rubik', sans-serif" => 'Rubik',
            "'Source Sans 3', sans-serif" => 'Source Sans 3',
            "'Fira Sans', sans-serif" => 'Fira Sans',
            "'Barlow', sans-serif" => 'Barlow',
            "'Cabin', sans-serif" => 'Cabin',
            "'Exo 2', sans-serif" => 'Exo 2',
            "'Bebas Neue', sans-serif" => 'Bebas Neue',
            'Georgia, serif' => 'Georgia',
            "'Playfair Display', serif" => 'Playfair Display',
            "'Merriweather', serif" => 'Merriweather',
            "'Courier New', monospace" => '– Courier New (mono)',
        ];

        $gf_families = ['Open+Sans:wght@400;700','Poppins:wght@400;700','Lato:wght@400;700','Inter:wght@400;700','Roboto:wght@400;700','Nunito:wght@400;700','Montserrat:wght@400;700','Raleway:wght@400;700','Oswald:wght@400;700','DM+Sans:wght@400;700','Manrope:wght@400;700','Work+Sans:wght@400;700','Rubik:wght@400;700','Source+Sans+3:wght@400;700','Fira+Sans:wght@400;700','Barlow:wght@400;700','Cabin:wght@400;700','Exo+2:wght@400;700','Bebas+Neue:wght@400','Playfair+Display:wght@400;700','Merriweather:wght@400;700'];
        $gf_url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $gf_families ) . '&display=swap';
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="stylesheet" href="' . esc_url( $gf_url ) . '">' . "\n";

        $aspect_options = ['4/3'=>'4:3 (alapért.)','16/9'=>'16:9 (széles)','3/2'=>'3:2','1/1'=>'1:1 (négyzet)','5/4'=>'5:4'];
        ?>
        <style>
        .vacd { display:grid; grid-template-columns:320px 1fr; gap:28px; align-items:start; }
        @media(max-width:1100px){ .vacd { grid-template-columns:1fr; } }
        .vacd__preview-wrap { position:sticky; top:64px; }
        .vacd__preview-card { background:var(--va-bg2,#141414); border:1px solid rgba(255,255,255,.08); border-radius:12px; overflow:hidden; display:flex; flex-direction:column; max-width:300px; position:relative; transition:transform .18s; }
        .vacd__preview-card:hover { transform:translateY(-2px); }
        .vacd__img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:linear-gradient(135deg,#1a1a1a,#0a0a0a); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.15); font-size:42px; overflow:hidden; position:relative; }
        .vacd__img img { width:100%; height:100%; object-fit:cover; }
        .vacd__badge { position:absolute; top:10px; left:10px; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; letter-spacing:.03em; background:linear-gradient(135deg,#3a2800,#1e1400); color:#ffc840; border:1px solid rgba(255,180,0,.5); }
        .vacd__body { padding:14px; display:flex; flex-direction:column; gap:6px; }
        .vacd__title { font-size:15px; font-weight:700; color:#fff; line-height:1.3; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .vacd__price-row { display:flex; align-items:center; justify-content:space-between; gap:8px; }
        .vacd__price { font-size:17px; font-weight:800; color:#ff2a2a; }
        .vacd__watchlist { width:30px; height:30px; border-radius:50%; background:rgba(0,0,0,.62); border:1px solid rgba(255,0,0,.45); display:flex; align-items:center; justify-content:center; color:#ff2a2a; flex-shrink:0; }
        .vacd__meta { font-size:12px; color:rgba(255,255,255,.38); display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        /* Editor */
        .vacd__editor { display:flex; flex-direction:column; gap:0; }
        .vacd__section { background:var(--va-bg2,#111); border:1px solid rgba(255,255,255,.07); border-radius:10px; margin-bottom:10px; overflow:hidden; }
        .vacd__section-head { display:flex; align-items:center; gap:10px; padding:13px 16px; cursor:pointer; user-select:none; }
        .vacd__section-head:hover { background:rgba(255,255,255,.03); }
        .vacd__section-title { font-size:13px; font-weight:600; color:#fff; }
        .vacd__section-arrow { margin-left:auto; font-size:18px; color:rgba(255,255,255,.4); transition:transform .2s; }
        .vacd__section.open .vacd__section-arrow { transform:rotate(90deg); }
        .vacd__section-body { display:none; padding:14px 16px 16px; border-top:1px solid rgba(255,255,255,.06); }
        .vacd__section.open .vacd__section-body { display:block; }
        .vacd__row { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
        .vacd__row:last-child { margin-bottom:0; }
        .vacd__label { font-size:12px; color:rgba(255,255,255,.55); min-width:130px; flex-shrink:0; }
        .vacd__val { font-size:11px; color:rgba(255,255,255,.3); margin-left:4px; }
        .vacd__range { flex:1; min-width:100px; accent-color:#ff0000; }
        .vacd__select { flex:1; background:#1a1a1a; border:1px solid rgba(255,255,255,.12); border-radius:6px; color:#fff; padding:5px 8px; font-size:12px; }
        .vacd__color-wrap { display:flex; align-items:center; gap:8px; flex:1; }
        .vacd__grid2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .vacd__grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }
        </style>
        <?php
        // Helper: szín mező (va-color-input picker)
        function vacd_color_field( string $prop, string $value ): void {
            echo '<div class="vacd__color-wrap">';
            echo '<input type="text" class="va-color-input vacd-field" data-prop="' . esc_attr($prop) . '" value="' . esc_attr($value) . '">';
            echo '</div>';
        }
        ?>

        <div class="va-wrap">
            <div class="va-page-header">
                <div>
                    <h1 class="va-page-title">🃏 Kártyaszerkesztő</h1>
                    <p class="va-page-subtitle">Hirdetés kártyák megjelenésének testreszabása – live preview az oldalon</p>
                </div>
            </div>
            <?php if ( $is_saved ): ?>
                <div class="va-alert va-alert--success" style="margin-bottom:18px;">✅ Beállítások mentve!</div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                <?php wp_nonce_field( 'va_save_card_styles' ); ?>
                <input type="hidden" name="action" value="va_save_card_styles">
                <input type="hidden" name="va_card_styles_json" id="va_card_styles_json" value="<?php echo esc_attr( wp_json_encode( $d ) ); ?>">

                <div class="vacd">
                    <!-- LIVE PREVIEW -->
                    <div class="vacd__preview-wrap">
                        <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:8px;text-transform:uppercase;letter-spacing:.06em;">Élő előnézet</div>
                        <div class="vacd__preview-card" id="prev-card">
                            <div class="vacd__img" id="prev-img">
                                <img src="https://picsum.photos/seed/hunt/400/300" alt="preview" id="prev-img-el">
                                <span class="vacd__badge" id="prev-badge">⭐ Kiemelt</span>
                            </div>
                            <div class="vacd__body" id="prev-body">
                                <h3 class="vacd__title" id="prev-title">SUZUKI SV 650 N – kiváló állapot</h3>
                                <div class="vacd__price-row">
                                    <div class="vacd__price" id="prev-price">2 800 000 Ft</div>
                                    <div class="vacd__watchlist" id="prev-watchlist">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    </div>
                                </div>
                                <div class="vacd__meta" id="prev-meta">
                                    <span>Veszprém</span><span>🗓 2026.04.23</span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="button" id="vacd-reset-all" style="font-size:11px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);border-radius:6px;padding:5px 12px;cursor:pointer;">↺ Minden visszaállítása</button>
                        </div>
                    </div>

                    <!-- EDITOR SECTIONS -->
                    <div class="vacd__editor" id="vacd-editor">

                        <!-- 1. Kártya konténer -->
                        <div class="vacd__section open" id="vacd-s-card">
                            <div class="vacd__section-head" onclick="vacdToggle('card')">
                                <span>🪟</span><span class="vacd__section-title">Kártya konténer</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Háttér szín</span>
                                    <?php vacd_color_field('card_bg', $d['card_bg']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Keret szín</span>
                                    <?php vacd_color_field('card_border_color', $d['card_border_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Keret vastagság <b class="vacd__val" id="lbl-card_border_width"><?php echo esc_html($d['card_border_width']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="card_border_width" min="0" max="4" step="1" value="<?php echo esc_attr($d['card_border_width']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Sarok lekerekítés <b class="vacd__val" id="lbl-card_radius"><?php echo esc_html($d['card_radius']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="card_radius" min="0" max="32" step="1" value="<?php echo esc_attr($d['card_radius']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Hover effekt -->
                        <div class="vacd__section" id="vacd-s-hover">
                            <div class="vacd__section-head" onclick="vacdToggle('hover')">
                                <span>✨</span><span class="vacd__section-title">Hover effekt</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Emelkedés <b class="vacd__val" id="lbl-hover_lift"><?php echo esc_html($d['hover_lift']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="hover_lift" min="0" max="12" step="1" value="<?php echo esc_attr($d['hover_lift']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Hover keret vastagság <b class="vacd__val" id="lbl-hover_border_width"><?php echo esc_html($d['hover_border_width']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="hover_border_width" min="1" max="6" step="1" value="<?php echo esc_attr($d['hover_border_width']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Hover keret szín</span>
                                    <?php vacd_color_field('hover_border_color', $d['hover_border_color']); ?>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Kép -->
                        <div class="vacd__section" id="vacd-s-img">
                            <div class="vacd__section-head" onclick="vacdToggle('img')">
                                <span>🖼️</span><span class="vacd__section-title">Kép arány</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Képarány (aspect-ratio)</span>
                                    <select class="vacd__select vacd-field" data-prop="img_aspect">
                                        <?php foreach ( $aspect_options as $val => $lbl ): ?>
                                            <option value="<?php echo esc_attr($val); ?>" <?php selected($d['img_aspect'],$val); ?>><?php echo esc_html($lbl); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Kártya törzs -->
                        <div class="vacd__section" id="vacd-s-body">
                            <div class="vacd__section-head" onclick="vacdToggle('body')">
                                <span>📦</span><span class="vacd__section-title">Kártya törzs (padding)</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Belső margó X <b class="vacd__val" id="lbl-body_pad_x"><?php echo esc_html($d['body_pad_x']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="body_pad_x" min="0" max="40" step="1" value="<?php echo esc_attr($d['body_pad_x']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Belső margó Y <b class="vacd__val" id="lbl-body_pad_y"><?php echo esc_html($d['body_pad_y']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="body_pad_y" min="0" max="40" step="1" value="<?php echo esc_attr($d['body_pad_y']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Elemek közt rés <b class="vacd__val" id="lbl-body_gap"><?php echo esc_html($d['body_gap']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="body_gap" min="0" max="24" step="1" value="<?php echo esc_attr($d['body_gap']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 5. Cím -->
                        <div class="vacd__section" id="vacd-s-title">
                            <div class="vacd__section-head" onclick="vacdToggle('title')">
                                <span>🔤</span><span class="vacd__section-title">Cím</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Szín</span>
                                    <?php vacd_color_field('title_color', $d['title_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűméret <b class="vacd__val" id="lbl-title_font_size"><?php echo esc_html($d['title_font_size']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="title_font_size" min="10" max="28" step="1" value="<?php echo esc_attr($d['title_font_size']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűvastagság</span>
                                    <select class="vacd__select vacd-field" data-prop="title_font_weight">
                                        <?php foreach ([300=>'300 – Light',400=>'400 – Normal',500=>'500 – Medium',600=>'600 – SemiBold',700=>'700 – Bold',800=>'800 – ExtraBold',900=>'900 – Black'] as $w=>$wl): ?>
                                            <option value="<?php echo $w; ?>" <?php selected((int)$d['title_font_weight'],$w); ?>><?php echo $wl; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűtípus</span>
                                    <select class="vacd__select vacd-field" data-prop="title_font_family">
                                        <?php foreach ($fonts as $fv=>$fn): ?>
                                            <option value="<?php echo esc_attr($fv); ?>" <?php selected($d['title_font_family'],$fv); ?> style="font-family:<?php echo esc_attr($fv); ?>"><?php echo esc_html($fn); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Sorok száma (max) <b class="vacd__val" id="lbl-title_line_clamp"><?php echo esc_html($d['title_line_clamp']); ?></b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="title_line_clamp" min="1" max="4" step="1" value="<?php echo esc_attr($d['title_line_clamp']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 6. Ár -->
                        <div class="vacd__section" id="vacd-s-price">
                            <div class="vacd__section-head" onclick="vacdToggle('price')">
                                <span>💰</span><span class="vacd__section-title">Ár</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Szín</span>
                                    <?php vacd_color_field('price_color', $d['price_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűméret <b class="vacd__val" id="lbl-price_font_size"><?php echo esc_html($d['price_font_size']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="price_font_size" min="12" max="32" step="1" value="<?php echo esc_attr($d['price_font_size']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűvastagság</span>
                                    <select class="vacd__select vacd-field" data-prop="price_font_weight">
                                        <?php foreach ([400=>'400 – Normal',500=>'500 – Medium',600=>'600 – SemiBold',700=>'700 – Bold',800=>'800 – ExtraBold',900=>'900 – Black'] as $w=>$wl): ?>
                                            <option value="<?php echo $w; ?>" <?php selected((int)$d['price_font_weight'],$w); ?>><?php echo $wl; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűtípus</span>
                                    <select class="vacd__select vacd-field" data-prop="price_font_family">
                                        <?php foreach ($fonts as $fv=>$fn): ?>
                                            <option value="<?php echo esc_attr($fv); ?>" <?php selected($d['price_font_family'],$fv); ?> style="font-family:<?php echo esc_attr($fv); ?>"><?php echo esc_html($fn); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 7. Meta sor -->
                        <div class="vacd__section" id="vacd-s-meta">
                            <div class="vacd__section-head" onclick="vacdToggle('meta')">
                                <span>📋</span><span class="vacd__section-title">Meta sor (helyszín, dátum)</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Szöveg szín</span>
                                    <?php vacd_color_field('meta_color', $d['meta_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűméret <b class="vacd__val" id="lbl-meta_font_size"><?php echo esc_html($d['meta_font_size']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="meta_font_size" min="9" max="16" step="1" value="<?php echo esc_attr($d['meta_font_size']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 8. Kiemelt badge -->
                        <div class="vacd__section" id="vacd-s-feat">
                            <div class="vacd__section-head" onclick="vacdToggle('feat')">
                                <span>⭐</span><span class="vacd__section-title">Kiemelt badge</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Háttér szín</span>
                                    <?php vacd_color_field('badge_featured_bg', $d['badge_featured_bg']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Szöveg szín</span>
                                    <?php vacd_color_field('badge_featured_color', $d['badge_featured_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Keret szín</span>
                                    <?php vacd_color_field('badge_featured_border', $d['badge_featured_border']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Sarok lekerekítés <b class="vacd__val" id="lbl-badge_featured_radius"><?php echo esc_html($d['badge_featured_radius']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="badge_featured_radius" min="0" max="30" step="1" value="<?php echo esc_attr($d['badge_featured_radius']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűméret <b class="vacd__val" id="lbl-badge_featured_size"><?php echo esc_html($d['badge_featured_size']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="badge_featured_size" min="8" max="18" step="1" value="<?php echo esc_attr($d['badge_featured_size']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 9. Boost badge -->
                        <div class="vacd__section" id="vacd-s-boost">
                            <div class="vacd__section-head" onclick="vacdToggle('boost')">
                                <span>⚡</span><span class="vacd__section-title">Előre téve (Boost) badge</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Háttér szín</span>
                                    <?php vacd_color_field('badge_boost_bg', $d['badge_boost_bg']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Szöveg szín</span>
                                    <?php vacd_color_field('badge_boost_color', $d['badge_boost_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Keret szín</span>
                                    <?php vacd_color_field('badge_boost_border', $d['badge_boost_border']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Sarok lekerekítés <b class="vacd__val" id="lbl-badge_boost_radius"><?php echo esc_html($d['badge_boost_radius']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="badge_boost_radius" min="0" max="30" step="1" value="<?php echo esc_attr($d['badge_boost_radius']); ?>">
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Betűméret <b class="vacd__val" id="lbl-badge_boost_size"><?php echo esc_html($d['badge_boost_size']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="badge_boost_size" min="8" max="18" step="1" value="<?php echo esc_attr($d['badge_boost_size']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 10. Watchlist -->
                        <div class="vacd__section" id="vacd-s-wl">
                            <div class="vacd__section-head" onclick="vacdToggle('wl')">
                                <span>❤️</span><span class="vacd__section-title">Kedvenc gomb (Watchlist)</span><span class="vacd__section-arrow">›</span>
                            </div>
                            <div class="vacd__section-body">
                                <div class="vacd__row">
                                    <span class="vacd__label">Szívecske szín</span>
                                    <?php vacd_color_field('watchlist_color', $d['watchlist_color']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Háttér szín</span>
                                    <?php vacd_color_field('watchlist_bg', $d['watchlist_bg']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Keret szín</span>
                                    <?php vacd_color_field('watchlist_border', $d['watchlist_border']); ?>
                                </div>
                                <div class="vacd__row">
                                    <span class="vacd__label">Méret <b class="vacd__val" id="lbl-watchlist_size"><?php echo esc_html($d['watchlist_size']); ?>px</b></span>
                                    <input type="range" class="vacd__range vacd-field" data-prop="watchlist_size" min="20" max="50" step="1" value="<?php echo esc_attr($d['watchlist_size']); ?>">
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;align-items:center;">
                            <button type="button" id="vacd-save-btn" class="va-btn va-btn--primary" style="font-size:14px;padding:10px 28px;">💾 Mentés</button>
                        </div>
                    </div><!-- /.vacd__editor -->
                </div><!-- /.vacd -->
            </form>
        </div>

        <script>
        jQuery(function($) {
            var defaults = <?php echo wp_json_encode( $defaults ); ?>;
            var current  = <?php echo wp_json_encode( $d ); ?>;

            // saveJson: közvetlen DOM-olvasás – nem függ az event binding működésétől
            function saveJson() {
                var out = {};
                document.querySelectorAll('#vacd-editor .vacd-field').forEach(function(el) {
                    var prop = el.dataset.prop;
                    if (!prop) return;
                    if (el.type === 'range') {
                        out[prop] = parseFloat(el.value) || parseInt(el.value) || 0;
                    } else if (el.tagName === 'SELECT') {
                        out[prop] = el.value;
                    } else if (el.classList.contains('va-color-input')) {
                        if (el.value) out[prop] = el.value; // hidden input, vaInitColorPickers frissíti
                    } else {
                        out[prop] = el.value;
                    }
                });
                // ami nincs a DOM-ban (pl. nincs megnyitott szekcióban), current-ből töltjük
                Object.keys(current).forEach(function(k) {
                    if (out[k] === undefined) out[k] = current[k];
                });
                current = out;
                document.getElementById('va_card_styles_json').value = JSON.stringify(out);
            }
            saveJson();

            // Frissítsük current-t is amikor picker változik (preview miatt)
            function onPickerChange(el) {
                var prop = el.dataset ? el.dataset.prop : el.getAttribute('data-prop');
                if (prop) current[prop] = el.value;
            }

            // Section toggle
            window.vacdToggle = function(key) {
                var sec = document.getElementById('vacd-s-'+key);
                if(!sec) return;
                sec.classList.toggle('open');
            };

            // Preview updater
            function updatePreview() {
                var c = current;
                var card = document.getElementById('prev-card');
                var img  = document.getElementById('prev-img');
                var body = document.getElementById('prev-body');
                var ttl  = document.getElementById('prev-title');
                var prc  = document.getElementById('prev-price');
                var meta = document.getElementById('prev-meta');
                var bdg  = document.getElementById('prev-badge');
                var wl   = document.getElementById('prev-watchlist');

                if(card) {
                    card.style.background     = c.card_bg;
                    card.style.borderColor    = c.card_border_color;
                    card.style.borderWidth    = (c.card_border_width||1)+'px';
                    card.style.borderStyle    = 'solid';
                    card.style.borderRadius   = c.card_radius+'px';
                }
                if(img) {
                    img.style.aspectRatio = c.img_aspect;
                }
                if(body) {
                    body.style.padding = c.body_pad_y+'px '+c.body_pad_x+'px';
                    body.style.gap     = c.body_gap+'px';
                }
                if(ttl) {
                    ttl.style.color          = c.title_color;
                    ttl.style.fontSize       = c.title_font_size+'px';
                    ttl.style.fontWeight     = c.title_font_weight;
                    ttl.style.fontFamily     = (c.title_font_family && c.title_font_family !== 'inherit') ? c.title_font_family : '';
                    ttl.style.webkitLineClamp= c.title_line_clamp;
                }
                if(prc) {
                    prc.style.color      = c.price_color;
                    prc.style.fontSize   = c.price_font_size+'px';
                    prc.style.fontWeight = c.price_font_weight;
                    prc.style.fontFamily = (c.price_font_family && c.price_font_family !== 'inherit') ? c.price_font_family : '';
                }
                if(meta) {
                    meta.style.color    = c.meta_color;
                    meta.style.fontSize = c.meta_font_size+'px';
                }
                if(bdg) {
                    bdg.style.background  = c.badge_featured_bg;
                    bdg.style.color       = c.badge_featured_color;
                    bdg.style.borderColor = c.badge_featured_border;
                    bdg.style.borderStyle = 'solid';
                    bdg.style.borderRadius= c.badge_featured_radius+'px';
                    bdg.style.fontSize    = c.badge_featured_size+'px';
                }
                if(wl) {
                    wl.style.color       = c.watchlist_color;
                    wl.style.background  = c.watchlist_bg;
                    wl.style.borderColor = c.watchlist_border;
                    wl.style.borderStyle = 'solid';
                    wl.style.width       = c.watchlist_size+'px';
                    wl.style.height      = c.watchlist_size+'px';
                }
            }
            // Hover border preview: inline :hover nem lehetséges, <style> injekcióval
            function updateHoverStyle() {
                var styleId = 'vacd-hover-preview-style';
                var el = document.getElementById(styleId);
                if(!el) { el = document.createElement('style'); el.id = styleId; document.head.appendChild(el); }
                el.textContent = '#prev-card:hover { border-color: ' + current.hover_border_color + ' !important; }';
            }
            // Bővített updatePreview: hover stílust is frissíti
            var _origUpdatePreview = updatePreview;
            updatePreview = function() { _origUpdatePreview(); updateHoverStyle(); };
            updatePreview();

            // Szín picker init: a custom vaInitColorPickers picker syncUI()-ban
            // $hidden.trigger('change')-t hív → change eseményt kell figyelni.
            // DOMReady-n 100ms késleltetéssel bindolunk, hogy admin.js
            // vaInitColorPickers már inicializálta a pickereket.
            function vacdInitPickers() {
                if(!$.fn || !$.fn.wpColorPicker) {
                    // custom picker: 200ms után bindolunk
                    setTimeout(function() {
                        $('#vacd-editor .va-color-input').off('change.vacd').on('change.vacd', function() {
                            onPickerChange(this);
                            updatePreview();
                            saveJson();
                        });
                    }, 200);
                } else {
                    setTimeout(function() {
                        $('#vacd-editor .va-color-input').off('change.vacd').on('change.vacd', function() {
                            onPickerChange(this);
                            updatePreview();
                            saveJson();
                        });
                    }, 200);
                }
            }
            vacdInitPickers();

            // Form submit előtt mindig frissítjük a hidden inputot
            // AJAX mentés – megbízhatóbb, azonnali visszajelzés
            var vacdAjaxNonce = '<?php echo esc_js( wp_create_nonce('va_save_card_styles_ajax') ); ?>';

            document.getElementById('vacd-save-btn').addEventListener('click', function(e) {
                e.preventDefault();
                var btn = this;
                saveJson();
                var jsonVal = document.getElementById('va_card_styles_json').value;
                btn.disabled = true;
                btn.textContent = '⏳ Mentés...';
                jQuery.post(ajaxurl, {
                    action:              'va_save_card_styles_ajax',
                    va_card_styles_json: jsonVal,
                    _ajax_nonce:         vacdAjaxNonce
                })
                .done(function(resp) {
                    if (resp && resp.success) {
                        if (typeof vaAdminToast === 'function') vaAdminToast('✅ Mentés sikeres!', 'success');
                    } else {
                        var msg = (resp && resp.data && resp.data.msg) ? resp.data.msg : 'Ismeretlen hiba';
                        if (typeof vaAdminToast === 'function') vaAdminToast('❌ Hiba: ' + msg, 'error');
                        else alert('Hiba: ' + msg);
                    }
                })
                .fail(function(xhr) {
                    if (typeof vaAdminToast === 'function') vaAdminToast('❌ AJAX hiba: ' + xhr.status, 'error');
                    else alert('AJAX hiba: ' + xhr.status);
                })
                .always(function() {
                    btn.disabled = false;
                    btn.textContent = '💾 Mentés';
                });
            });

            // Field change listeners (range + select)
            document.querySelectorAll('.vacd-field').forEach(function(el){
                el.addEventListener('input', function(){
                    var prop = this.dataset.prop;
                    if(!prop) return;
                    if(this.type === 'range') {
                        current[prop] = parseFloat(this.value)||parseInt(this.value)||0;
                        var lbl = document.getElementById('lbl-'+prop);
                        if(lbl) lbl.textContent = current[prop] + (prop.indexOf('clamp')<0?'px':'');
                        updatePreview(); saveJson();
                    }
                });
                el.addEventListener('change', function(){
                    var prop = this.dataset.prop;
                    if(!prop || this.type === 'range') return;
                    // va-color-input: a jQuery handler kezeli, ide ne kerljön
                    if(this.classList.contains('va-color-input')) return;
                    current[prop] = this.value;
                    updatePreview(); saveJson();
                });
            });

            // Reset all
            document.getElementById('vacd-reset-all').addEventListener('click', function(){
                if(!confirm('Visszaállítod az összes kártya beállítást alapértékre?')) return;
                current = Object.assign({}, defaults);
                // range + select frissítés
                document.querySelectorAll('.vacd-field').forEach(function(el){
                    var prop = el.dataset.prop;
                    if(!prop || current[prop] === undefined) return;
                    if(el.classList.contains('va-color-input')) return; // picker alább kezeli
                    el.value = current[prop];
                    var lbl = document.getElementById('lbl-'+prop);
                    if(lbl) lbl.textContent = current[prop] + (el.type==='range' && prop.indexOf('clamp')<0 ? 'px' : '');
                });
                // color picker-ek frissítése jQuery-vel (trigger change → swatch is frissül)
                if(typeof $ !== 'undefined') {
                    $('.va-color-input.vacd-field').each(function(){
                        var prop = $(this).data('prop');
                        if(prop && current[prop] !== undefined) {
                            $(this).val(current[prop]).trigger('change');
                        }
                    });
                }
                updatePreview();
                saveJson();
            });

        }); // jQuery DOMReady
        </script>
        <?php
    }

    /* ══════════════════════════════════════════════════════════
     * PILL / BADGE STÍLUSOK
     * ══════════════════════════════════════════════════════════ */

    public static function get_pill_defaults(): array {
        return [
            'cat_pill' => [
                'label'    => 'Kategória jelölő',
                'desc'     => 'A hirdetés kategóriájának pill-je (pl. SUZUKI)',
                'selector' => '.sl__cat-pill',
                'example'  => 'Vadászfegyver',
                'text'     => '#ff4444', 'bg' => 'rgba(255,0,0,.12)', 'border' => 'rgba(255,0,0,.25)',
                'radius' => 20, 'font_size' => 11, 'font_weight' => 700, 'pad_x' => 10, 'pad_y' => 3, 'font_family' => 'inherit',
            ],
            'featured_pill' => [
                'label'    => 'Kiemelt hirdetés',
                'desc'     => 'A kiemelt hirdetések jelölője a cím mellett',
                'selector' => '.sl__featured-pill',
                'example'  => '⭐ Kiemelt',
                'text'     => '#ffd060', 'bg' => 'rgba(255,180,0,.15)', 'border' => 'rgba(255,180,0,.3)',
                'radius' => 20, 'font_size' => 11, 'font_weight' => 700, 'pad_x' => 9, 'pad_y' => 0, 'font_family' => 'inherit',
            ],
            'verified_pill' => [
                'label'    => 'Ellenőrzött hirdető',
                'desc'     => 'A megerősített hirdetők jelölője a cím mellett',
                'selector' => '.sl__verified-pill',
                'example'  => '✓ Ellenőrzött',
                'text'     => '#4dffaa', 'bg' => 'rgba(0,210,120,.12)', 'border' => 'rgba(0,210,120,.3)',
                'radius' => 20, 'font_size' => 11, 'font_weight' => 700, 'pad_x' => 9, 'pad_y' => 0, 'font_family' => 'inherit',
            ],
            'badge_damage_no' => [
                'label'    => 'Nincs korábbi kár',
                'desc'     => 'Jármű esetén: nincsen baleseti előélet',
                'selector' => '.sl__badge--damage-no',
                'example'  => '✓ Nincs korábbi kár',
                'text'     => '#4dffaa', 'bg' => 'rgba(0,200,100,.12)', 'border' => 'rgba(0,200,100,.25)',
                'radius' => 20, 'font_size' => 12, 'font_weight' => 700, 'pad_x' => 10, 'pad_y' => 5, 'font_family' => 'inherit',
            ],
            'badge_damage_yes' => [
                'label'    => 'Korábbi kár / baleset',
                'desc'     => 'Jármű esetén: volt baleseti előélete',
                'selector' => '.sl__badge--damage-yes',
                'example'  => '⚠ Korábbi kár / baleset',
                'text'     => '#ff8080', 'bg' => 'rgba(255,60,60,.12)', 'border' => 'rgba(255,60,60,.25)',
                'radius' => 20, 'font_size' => 12, 'font_weight' => 700, 'pad_x' => 10, 'pad_y' => 5, 'font_family' => 'inherit',
            ],
            'badge_service' => [
                'label'    => 'Szervizkönyv megvan',
                'desc'     => 'Jármű esetén: szervizkönyv rendelkezésre áll',
                'selector' => '.sl__badge--service-yes',
                'example'  => '✓ Szervizkönyv megvan',
                'text'     => '#66ccff', 'bg' => 'rgba(0,180,255,.10)', 'border' => 'rgba(0,180,255,.2)',
                'radius' => 20, 'font_size' => 12, 'font_weight' => 700, 'pad_x' => 10, 'pad_y' => 5, 'font_family' => 'inherit',
            ],
            'badge_license' => [
                'label'    => 'Fegyverengedély szükséges',
                'desc'     => 'Vadász termékeknél: engedélyköteles',
                'selector' => '.sl__badge--license',
                'example'  => '⚠ Fegyverengedély szükséges',
                'text'     => '#ffd060', 'bg' => 'rgba(255,180,0,.12)', 'border' => 'rgba(255,180,0,.25)',
                'radius' => 20, 'font_size' => 12, 'font_weight' => 700, 'pad_x' => 10, 'pad_y' => 5, 'font_family' => 'inherit',
            ],
            'badge_verified' => [
                'label'    => 'Ellenőrzött hirdető (badge)',
                'desc'     => 'Részletek kártyában megjelenő ellenőrzött badge',
                'selector' => '.sl__badge--verified',
                'example'  => '✓ Ellenőrzött hirdető',
                'text'     => '#4dffaa', 'bg' => 'rgba(0,210,120,.12)', 'border' => 'rgba(0,210,120,.3)',
                'radius' => 20, 'font_size' => 12, 'font_weight' => 700, 'pad_x' => 10, 'pad_y' => 5, 'font_family' => 'inherit',
            ],
            'plan_basic' => [
                'label'    => 'Alap tag (terv badge)',
                'desc'     => 'Feladó kártyában: Alap csomag jelölője',
                'selector' => '.sl__plan-badge--basic',
                'example'  => 'Alap tag',
                'text'     => 'rgba(255,255,255,.5)', 'bg' => 'rgba(255,255,255,.07)', 'border' => 'rgba(255,255,255,.12)',
                'radius' => 6, 'font_size' => 11, 'font_weight' => 600, 'pad_x' => 8, 'pad_y' => 3, 'font_family' => 'inherit',
            ],
            'plan_silver' => [
                'label'    => 'Ezüst tag (terv badge)',
                'desc'     => 'Feladó kártyában: Ezüst csomag jelölője',
                'selector' => '.sl__plan-badge--silver',
                'example'  => '✦ Ezüst tag',
                'text'     => '#c0c0c0', 'bg' => 'rgba(192,192,192,.12)', 'border' => 'rgba(192,192,192,.3)',
                'radius' => 6, 'font_size' => 11, 'font_weight' => 700, 'pad_x' => 8, 'pad_y' => 3, 'font_family' => 'inherit',
            ],
            'plan_gold' => [
                'label'    => 'Arany tag (terv badge)',
                'desc'     => 'Feladó kártyában: Arany csomag jelölője',
                'selector' => '.sl__plan-badge--gold',
                'example'  => '★ Arany tag',
                'text'     => '#ffd700', 'bg' => 'rgba(255,215,0,.12)', 'border' => 'rgba(255,215,0,.3)',
                'radius' => 6, 'font_size' => 11, 'font_weight' => 700, 'pad_x' => 8, 'pad_y' => 3, 'font_family' => 'inherit',
            ],
            'plan_platinum' => [
                'label'    => 'Platina tag (terv badge)',
                'desc'     => 'Feladó kártyában: Platina csomag jelölője',
                'selector' => '.sl__plan-badge--platinum',
                'example'  => '◆ Platina tag',
                'text'     => '#a0e4ff', 'bg' => 'rgba(100,200,255,.12)', 'border' => 'rgba(100,200,255,.3)',
                'radius' => 6, 'font_size' => 11, 'font_weight' => 700, 'pad_x' => 8, 'pad_y' => 3, 'font_family' => 'inherit',
            ],
        ];
    }

    public static function output_pill_css(): void {
        $defaults = self::get_pill_defaults();
        $saved    = (array) json_decode( (string) get_option( 'va_pill_styles', '{}' ), true );
        $css      = "\n<style id=\"va-pill-styles\">\n";
        foreach ( $defaults as $key => $d ) {
            $s  = isset( $saved[ $key ] ) ? array_merge( $d, (array) $saved[ $key ] ) : $d;
            $py = (int) $s['pad_y'];
            $px = (int) $s['pad_x'];
            $h  = $py * 2 + (int) $s['font_size'] + 4;  // approx height
            $padding = $py > 0 ? "{$py}px {$px}px" : "0 {$px}px";
            $min_h   = $py > 0 ? '' : "height:{$h}px;";
            $ff  = ! empty( $s['font_family'] ) && $s['font_family'] !== 'inherit' ? "font-family:{$s['font_family']};" : '';
            $css .= "{$s['selector']}{display:inline-flex;align-items:center;gap:4px;font-size:{$s['font_size']}px;font-weight:{$s['font_weight']};{$ff}padding:{$padding};{$min_h}border-radius:{$s['radius']}px;background:{$s['bg']};color:{$s['text']};border:1px solid {$s['border']};white-space:nowrap;text-decoration:none;vertical-align:middle;line-height:1;}\n";
        }
        $css .= "</style>\n";
        echo $css; // phpcs:ignore WordPress.Security.EscapeOutput
    }

    public static function handle_save_pill_styles(): void {
        check_admin_referer( 'va_save_pill_styles' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Nincs jogosultság.' );
        $raw = wp_unslash( $_POST['va_pill_styles'] ?? '{}' );
        // validate: must be valid JSON array
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            update_option( 'va_pill_styles', wp_json_encode( $decoded ) );
        }
        wp_safe_redirect( add_query_arg( [ 'page' => 'vadaszapro-pills', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ── Pill / Badge stílusok szerkesztő ─────────────────── */
    public static function render_pill_styles(): void {
        $defaults = self::get_pill_defaults();
        $saved    = (array) json_decode( (string) get_option( 'va_pill_styles', '{}' ), true );
        $pills    = [];
        foreach ( $defaults as $k => $d ) {
            $pills[$k] = isset( $saved[$k] ) ? array_merge( $d, (array) $saved[$k] ) : $d;
        }
        $is_saved = isset( $_GET['saved'] );

        $fonts = [
            // ── Rendszer / web-safe ──────────────────────────────────────────────
            'inherit'                                 => '– Alapértelmezett (örökölt)',
            'system-ui, sans-serif'                   => '– System UI',
            'Arial, sans-serif'                       => '– Arial',
            "'Arial Black', sans-serif"               => '– Arial Black',
            'Helvetica, Arial, sans-serif'            => '– Helvetica',
            'Verdana, sans-serif'                     => '– Verdana',
            'Tahoma, sans-serif'                      => '– Tahoma',
            "'Trebuchet MS', sans-serif"              => '– Trebuchet MS',
            'Georgia, serif'                          => '– Georgia (serif)',
            "'Times New Roman', serif"                => '– Times New Roman (serif)',
            "'Courier New', monospace"                => '– Courier New (mono)',
            // ── Google – Sans-serif ──────────────────────────────────────────────
            "'Open Sans', sans-serif"                 => 'Open Sans',
            "'Poppins', sans-serif"                   => 'Poppins',
            "'Lato', sans-serif"                      => 'Lato',
            "'Inter', sans-serif"                     => 'Inter',
            "'Roboto', sans-serif"                    => 'Roboto',
            "'Nunito', sans-serif"                    => 'Nunito',
            "'Montserrat', sans-serif"                => 'Montserrat',
            "'Raleway', sans-serif"                   => 'Raleway',
            "'Source Sans 3', sans-serif"             => 'Source Sans 3',
            "'PT Sans', sans-serif"                   => 'PT Sans',
            "'Ubuntu', sans-serif"                    => 'Ubuntu',
            "'Rubik', sans-serif"                     => 'Rubik',
            "'DM Sans', sans-serif"                   => 'DM Sans',
            "'Work Sans', sans-serif"                 => 'Work Sans',
            "'Manrope', sans-serif"                   => 'Manrope',
            "'Cabin', sans-serif"                     => 'Cabin',
            "'Barlow', sans-serif"                    => 'Barlow',
            "'Barlow Condensed', sans-serif"          => 'Barlow Condensed',
            "'Mulish', sans-serif"                    => 'Mulish',
            "'Quicksand', sans-serif"                 => 'Quicksand',
            "'Josefin Sans', sans-serif"              => 'Josefin Sans',
            "'Titillium Web', sans-serif"             => 'Titillium Web',
            "'Exo 2', sans-serif"                     => 'Exo 2',
            "'Exo', sans-serif"                       => 'Exo',
            "'Archivo', sans-serif"                   => 'Archivo',
            "'Outfit', sans-serif"                    => 'Outfit',
            "'Plus Jakarta Sans', sans-serif"         => 'Plus Jakarta Sans',
            "'Figtree', sans-serif"                   => 'Figtree',
            "'Syne', sans-serif"                      => 'Syne',
            "'Space Grotesk', sans-serif"             => 'Space Grotesk',
            "'Kanit', sans-serif"                     => 'Kanit',
            "'Jost', sans-serif"                      => 'Jost',
            "'Urbanist', sans-serif"                  => 'Urbanist',
            "'Fira Sans', sans-serif"                 => 'Fira Sans',
            "'IBM Plex Sans', sans-serif"             => 'IBM Plex Sans',
            "'Noto Sans', sans-serif"                 => 'Noto Sans',
            // ── Google – Serif ───────────────────────────────────────────────────
            "'Oswald', sans-serif"                    => 'Oswald',
            "'Merriweather', serif"                   => 'Merriweather',
            "'Playfair Display', serif"               => 'Playfair Display',
            "'Lora', serif"                           => 'Lora',
            "'Libre Baskerville', serif"              => 'Libre Baskerville',
            "'Crimson Text', serif"                   => 'Crimson Text',
            "'EB Garamond', serif"                    => 'EB Garamond',
            "'Cormorant Garamond', serif"             => 'Cormorant Garamond',
            "'Spectral', serif"                       => 'Spectral',
            // ── Google – Display / dekoratív ─────────────────────────────────────
            "'Abril Fatface', cursive"                => 'Abril Fatface',
            "'Righteous', sans-serif"                 => 'Righteous',
            "'Bebas Neue', sans-serif"                => 'Bebas Neue',
            "'Comfortaa', cursive"                    => 'Comfortaa',
            "'Pacifico', cursive"                     => 'Pacifico',
            "'Anton', sans-serif"                     => 'Anton',
            "'Permanent Marker', cursive"             => 'Permanent Marker',
            "'Shadows Into Light', cursive"           => 'Shadows Into Light',
        ];

        // Google Fonts combined URL a pill editor preview-hoz (összes Google font egyszerre)
        $gf_families = [
            'Open+Sans:wght@400;700', 'Poppins:wght@400;700', 'Lato:wght@400;700',
            'Inter:wght@400;700', 'Roboto:wght@400;700', 'Nunito:wght@400;700',
            'Montserrat:wght@400;700', 'Raleway:wght@400;700', 'Source+Sans+3:wght@400;700',
            'PT+Sans:wght@400;700', 'Ubuntu:wght@400;700', 'Rubik:wght@400;700',
            'DM+Sans:wght@400;700', 'Work+Sans:wght@400;700', 'Manrope:wght@400;700',
            'Cabin:wght@400;700', 'Barlow:wght@400;700', 'Barlow+Condensed:wght@400;700',
            'Mulish:wght@400;700', 'Quicksand:wght@400;700', 'Josefin+Sans:wght@400;700',
            'Titillium+Web:wght@400;700', 'Exo+2:wght@400;700', 'Exo:wght@400;700',
            'Archivo:wght@400;700', 'Outfit:wght@400;700', 'Plus+Jakarta+Sans:wght@400;700',
            'Figtree:wght@400;700', 'Syne:wght@400;700', 'Space+Grotesk:wght@400;700',
            'Kanit:wght@400;700', 'Jost:wght@400;700', 'Urbanist:wght@400;700',
            'Fira+Sans:wght@400;700', 'IBM+Plex+Sans:wght@400;700', 'Noto+Sans:wght@400;700',
            'Oswald:wght@400;700', 'Merriweather:wght@400;700', 'Playfair+Display:wght@400;700',
            'Lora:wght@400;700', 'Libre+Baskerville:wght@400;700', 'Crimson+Text:wght@400;700',
            'EB+Garamond:wght@400;700', 'Cormorant+Garamond:wght@400;700', 'Spectral:wght@400;700',
            'Abril+Fatface:wght@400', 'Righteous:wght@400', 'Bebas+Neue:wght@400',
            'Comfortaa:wght@400;700', 'Pacifico:wght@400', 'Anton:wght@400',
            'Permanent+Marker:wght@400', 'Shadows+Into+Light:wght@400',
        ];
        $gf_url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $gf_families ) . '&display=swap';
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="stylesheet" href="' . esc_url( $gf_url ) . '">' . "\n";

        // Szín paletta – gyors választókhoz
        $palette = [
            '#ff0000','#ff4444','#ff8080','#ff6600','#ffaa00','#ffd060','#ffd700',
            '#00cc66','#4dffaa','#00e676','#00bfff','#66ccff','#40c4ff',
            '#bb86fc','#cf6679','#ffffff','rgba(255,255,255,.5)','rgba(255,255,255,.15)',
            'rgba(255,0,0,.12)','rgba(0,200,100,.12)','rgba(0,180,255,.12)','rgba(255,180,0,.12)',
            'rgba(0,0,0,0)','transparent',
        ];
        ?>
        <style>
        .va-pill-card { background:var(--va-bg2);border:1px solid var(--va-border);border-radius:12px;overflow:hidden;margin-bottom:0; }
        .va-pill-card__head { display:flex;align-items:center;gap:10px;padding:14px 16px;cursor:pointer;user-select:none; }
        .va-pill-card__head:hover { background:rgba(255,255,255,.03); }
        .va-pill-card__arrow { margin-left:auto;color:var(--va-muted);font-size:18px;transition:transform .2s; }
        .va-pill-card.open .va-pill-card__arrow { transform:rotate(90deg); }
        .va-pill-card__body { display:none;border-top:1px solid var(--va-border);padding:16px;background:var(--va-bg3); }
        .va-pill-card.open .va-pill-card__body { display:block; }

        .va-pill-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;margin-bottom:12px; }
        @media(max-width:600px){ .va-pill-grid { grid-template-columns:1fr; } }
        .va-pill-grid--full { grid-column:1/-1; }

        .va-pill-label { font-size:11px;color:var(--va-muted);margin-bottom:4px;display:flex;justify-content:space-between; }
        .va-pill-label b { color:var(--va-text); }

        .va-pill-input { width:100%;background:var(--va-bg4,#1e1e1e);border:1px solid var(--va-border);color:var(--va-text);border-radius:6px;padding:6px 8px;font-size:12px; }
        .va-pill-input:focus { border-color:var(--va-accent);outline:none; }

        .va-pill-range { width:100%;accent-color:var(--va-accent); }

        .va-color-row { display:flex;align-items:center;gap:6px; }
        .va-color-row input[type=text] { flex:1; }

        .va-palette { display:flex;flex-wrap:wrap;gap:4px;margin-top:6px; }
        .va-palette__swatch { width:20px;height:20px;border-radius:4px;cursor:pointer;border:1px solid rgba(255,255,255,.15);flex-shrink:0;transition:transform .15s; }
        .va-palette__swatch:hover { transform:scale(1.25);z-index:1; }

        .va-pill-section-title { font-size:11px;font-weight:700;color:var(--va-muted);text-transform:uppercase;letter-spacing:.06em;margin:14px 0 8px; }
        .va-pill-name-row { display:flex;align-items:center;gap:8px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--va-border); }
        .va-pill-name-row span { font-size:11px;color:var(--va-muted);white-space:nowrap; }
        </style>

        <div class="wrap va-admin-wrap">
        <h1>🏷️ Pill & Badge stílusszerkesztő</h1>
        <p class="description">Az összes hirdetésoldalon megjelenő jelölő (pill / badge) kinézete valós időben szerkeszthető.</p>
        <?php if ( $is_saved ): ?><div class="notice notice-success is-dismissible"><p>✅ Stílusok mentve!</p></div><?php endif; ?>

        <!-- Élő előnézet sáv -->
        <div id="va-pill-preview-bar" style="background:var(--va-bg2);border:1px solid var(--va-border);border-radius:12px;padding:14px 18px;margin:16px 0 20px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            <span style="font-size:11px;color:var(--va-muted);margin-right:4px;">Élő előnézet:</span>
            <?php foreach ( $pills as $key => $p ):
                $ff_style = ( ! empty($p['font_family']) && $p['font_family'] !== 'inherit' ) ? "font-family:{$p['font_family']};" : '';
                $py = (int)$p['pad_y']; $px_v = (int)$p['pad_x']; $fs = (int)$p['font_size'];
                $pad = $py > 0 ? "{$py}px {$px_v}px" : "0 {$px_v}px";
                $h_s = $py <= 0 ? "height:".($fs+10)."px;" : '';
            ?>
            <span id="prev-<?php echo esc_attr($key); ?>" style="display:inline-flex;align-items:center;gap:4px;font-size:<?php echo $fs; ?>px;font-weight:<?php echo esc_attr($p['font_weight']); ?>;<?php echo $ff_style; ?>padding:<?php echo $pad; ?>;<?php echo $h_s; ?>border-radius:<?php echo esc_attr($p['radius']); ?>px;background:<?php echo esc_attr($p['bg']); ?>;color:<?php echo esc_attr($p['text']); ?>;border:1px solid <?php echo esc_attr($p['border']); ?>;white-space:nowrap;vertical-align:middle;line-height:1;" title="<?php echo esc_attr($p['label']); ?>">
                <?php echo esc_html($p['example']); ?>
            </span>
            <?php endforeach; ?>
        </div>

        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" id="va-pill-form">
            <input type="hidden" name="action" value="va_save_pill_styles">
            <?php wp_nonce_field( 'va_save_pill_styles' ); ?>
            <input type="hidden" name="va_pill_styles" id="va_pill_styles_json" value="">

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(460px,100%),1fr));gap:16px;">
            <?php foreach ( $pills as $key => $p ):
                $sel = esc_attr($key);
                $ff_cur = $p['font_family'] ?? 'inherit';
                $ff_style = ( $ff_cur !== 'inherit' ) ? "font-family:{$ff_cur};" : '';
                $py = (int)$p['pad_y']; $px_v = (int)$p['pad_x']; $fs = (int)$p['font_size'];
                $pad = $py > 0 ? "{$py}px {$px_v}px" : "0 {$px_v}px";
                $h_s = $py <= 0 ? "height:".($fs+10)."px;" : '';
            ?>
            <div class="va-pill-card" id="card-<?php echo $sel; ?>">
                <div class="va-pill-card__head" onclick="vaPillToggle('<?php echo $sel; ?>')">
                    <span id="prev-card-<?php echo $sel; ?>" style="display:inline-flex;align-items:center;gap:4px;font-size:<?php echo $fs; ?>px;font-weight:<?php echo esc_attr($p['font_weight']); ?>;<?php echo $ff_style; ?>padding:<?php echo $pad; ?>;<?php echo $h_s; ?>border-radius:<?php echo esc_attr($p['radius']); ?>px;background:<?php echo esc_attr($p['bg']); ?>;color:<?php echo esc_attr($p['text']); ?>;border:1px solid <?php echo esc_attr($p['border']); ?>;white-space:nowrap;line-height:1;flex-shrink:0;">
                        <?php echo esc_html($p['example']); ?>
                    </span>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:13px;font-weight:600;color:var(--va-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($p['label']); ?></div>
                        <div style="font-size:11px;color:var(--va-muted);font-family:monospace;"><?php echo esc_html($p['selector']); ?></div>
                    </div>
                    <span class="va-pill-card__arrow">›</span>
                </div>

                <div class="va-pill-card__body">
                    <p style="margin:0 0 14px;font-size:12px;color:var(--va-muted);"><?php echo esc_html($p['desc']); ?></p>

                    <!-- Megjelenítendő szöveg -->
                    <div class="va-pill-name-row">
                        <span>Pill felirat (előnézethez):</span>
                        <input type="text" class="va-pill-input va-pill-field" data-key="<?php echo $sel; ?>" data-prop="example"
                               value="<?php echo esc_attr($p['example']); ?>"
                               style="flex:1;max-width:260px;" placeholder="pl. ⭐ Kiemelt">
                    </div>

                    <!-- Színek -->
                    <div class="va-pill-section-title">🎨 Színek</div>
                    <div class="va-pill-grid">
                        <?php foreach ( ['text'=>'Szöveg szín','bg'=>'Háttér szín','border'=>'Keret szín'] as $prop => $lbl ): ?>
                        <div<?php echo $prop==='border'?' class="va-pill-grid--full"':''; ?>>
                            <div class="va-pill-label"><?php echo $lbl; ?></div>
                            <input type="text" id="cinp-<?php echo $sel.'-'.$prop; ?>" class="va-color-input va-pill-field" data-key="<?php echo $sel; ?>" data-prop="<?php echo $prop; ?>" value="<?php echo esc_attr($p[$prop]); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Betűtípus & méret -->
                    <div class="va-pill-section-title">🔤 Betűtípus & méret</div>
                    <div class="va-pill-grid">
                        <div class="va-pill-grid--full">
                            <div class="va-pill-label">Betűtípus</div>
                            <select class="va-pill-input va-pill-field" data-key="<?php echo $sel; ?>" data-prop="font_family">
                                <?php foreach ( $fonts as $fval => $fname ): ?>
                                <option value="<?php echo esc_attr($fval); ?>" <?php selected($ff_cur, $fval); ?> style="font-family:<?php echo esc_attr($fval); ?>"><?php echo esc_html($fname); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <div class="va-pill-label">Betűméret <b id="lbl-font_size-<?php echo $sel; ?>"><?php echo esc_html($p['font_size']); ?>px</b></div>
                            <input type="range" class="va-pill-range va-pill-field" data-key="<?php echo $sel; ?>" data-prop="font_size" min="9" max="24" value="<?php echo esc_attr($p['font_size']); ?>">
                        </div>
                        <div>
                            <div class="va-pill-label">Betűvastagság</div>
                            <select class="va-pill-input va-pill-field" data-key="<?php echo $sel; ?>" data-prop="font_weight">
                                <?php foreach ( [300=>'300 – Light',400=>'400 – Normal',500=>'500 – Medium',600=>'600 – Semi-bold',700=>'700 – Bold',800=>'800 – Extra bold',900=>'900 – Black'] as $w=>$wl ): ?>
                                <option value="<?php echo $w; ?>" <?php selected((int)$p['font_weight'],$w); ?>><?php echo $wl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Méret & forma -->
                    <div class="va-pill-section-title">📐 Méret & forma</div>
                    <div class="va-pill-grid">
                        <div>
                            <div class="va-pill-label">Lekerekítés <b id="lbl-radius-<?php echo $sel; ?>"><?php echo esc_html($p['radius']); ?>px</b></div>
                            <input type="range" class="va-pill-range va-pill-field" data-key="<?php echo $sel; ?>" data-prop="radius" min="0" max="40" value="<?php echo esc_attr($p['radius']); ?>">
                        </div>
                        <div>
                            <!-- spacer -->
                        </div>
                        <div>
                            <div class="va-pill-label">Vízszintes padding <b id="lbl-pad_x-<?php echo $sel; ?>"><?php echo esc_html($p['pad_x']); ?>px</b></div>
                            <input type="range" class="va-pill-range va-pill-field" data-key="<?php echo $sel; ?>" data-prop="pad_x" min="0" max="32" value="<?php echo esc_attr($p['pad_x']); ?>">
                        </div>
                        <div>
                            <div class="va-pill-label">Függőleges padding <b id="lbl-pad_y-<?php echo $sel; ?>"><?php echo esc_html($p['pad_y']); ?>px</b></div>
                            <input type="range" class="va-pill-range va-pill-field" data-key="<?php echo $sel; ?>" data-prop="pad_y" min="0" max="20" value="<?php echo esc_attr($p['pad_y']); ?>">
                        </div>
                    </div>

                    <button type="button" class="button button-small va-pill-reset" data-key="<?php echo $sel; ?>" style="margin-top:8px;">↺ Alaphelyre</button>
                </div>
            </div>
            <?php endforeach; ?>
            </div>

            <p style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <?php submit_button( '💾 Pill stílusok mentése', 'primary', 'submit', false ); ?>
                <button type="button" class="button" id="va-pills-reset-all">↺ Összes alaphelyzetbe</button>
            </p>
        </form>
        </div>

        <script>
        (function(){
            var defaults = <?php echo wp_json_encode( array_map( function($p){ return array_intersect_key($p, array_flip(['text','bg','border','radius','font_size','font_weight','pad_x','pad_y','example','font_family'])); }, $defaults ) ); ?>;
            var current  = <?php echo wp_json_encode( array_map( function($p){ return array_intersect_key($p, array_flip(['text','bg','border','radius','font_size','font_weight','pad_x','pad_y','example','font_family'])); }, $pills ) ); ?>;

            window.vaPillToggle = function(key) {
                var card = document.getElementById('card-'+key);
                if(!card) return;
                var wasOpen = card.classList.contains('open');
                card.classList.toggle('open');
                // init color pickers when card opens
                if(!wasOpen && typeof $ !== 'undefined' && typeof window.vaInitColorPickers === 'function') {
                    window.vaInitColorPickers($(card));
                }
            };

            window.vaPillSetColor = function(key, prop, val) {
                current[key][prop] = val;
                // update text input
                var inp = document.getElementById('cinp-'+key+'-'+prop);
                if(inp) { inp.value = val; }
                // update color swatch
                var sw = document.getElementById('cswatch-'+key+'-'+prop);
                if(sw) sw.style.background = val;
                updatePreview(key);
            };

            function buildPrevStyle(key) {
                var d = current[key];
                var py = parseInt(d.pad_y)||0;
                var px = parseInt(d.pad_x)||0;
                var fs = parseInt(d.font_size)||11;
                var ff = (d.font_family && d.font_family !== 'inherit') ? 'font-family:'+d.font_family+';' : '';
                return [
                    'display:inline-flex','align-items:center','gap:4px','white-space:nowrap','line-height:1','vertical-align:middle',
                    'font-size:'+fs+'px','font-weight:'+d.font_weight, ff,
                    'border-radius:'+d.radius+'px',
                    'background:'+d.bg,'color:'+d.text,'border:1px solid '+d.border,
                    py>0 ? 'padding:'+py+'px '+px+'px' : 'padding:0 '+px+'px;height:'+(fs+10)+'px',
                ].filter(Boolean).join(';');
            }

            function updatePreview(key) {
                var d = current[key];
                if(!d) return;
                var style = buildPrevStyle(key);
                var ids = ['prev-'+key, 'prev-card-'+key];
                ids.forEach(function(id){
                    var el = document.getElementById(id);
                    if(el){
                        el.setAttribute('style', style + (id.indexOf('prev-card')>=0 ? ';flex-shrink:0' : ''));
                        // update text content if example changed
                        el.textContent = d.example || '';
                    }
                });
                saveJson();
            }

            function saveJson() {
                document.getElementById('va_pill_styles_json').value = JSON.stringify(current);
            }

            // Initial JSON
            saveJson();

            // Field change handler (input = sliders/selects/text, change = color picker update)
            function vaPillHandleChange() {
                var key  = this.dataset.key;
                var prop = this.dataset.prop;
                if(!key || !current[key]) return;
                current[key][prop] = this.type === 'range' ? parseInt(this.value) : this.value;
                var lbl = document.getElementById('lbl-'+prop+'-'+key);
                if(lbl) lbl.textContent = current[key][prop] + (this.type==='range'?'px':'');
                updatePreview(key);
            }
            document.querySelectorAll('.va-pill-field').forEach(function(input){
                input.addEventListener('input', function(){
                    var key  = this.dataset.key;
                    var prop = this.dataset.prop;
                    if(!current[key]) return;
                    current[key][prop] = this.type === 'range' ? parseInt(this.value) : this.value;
                    var lbl = document.getElementById('lbl-'+prop+'-'+key);
                    if(lbl) lbl.textContent = current[key][prop] + (this.type==='range'?'px':'');
                    updatePreview(key);
                });
                // color picker (va-color-input) triggers 'change' after picking
                input.addEventListener('change', function(){
                    var key  = this.dataset.key;
                    var prop = this.dataset.prop;
                    if(!key || !current[key]) return;
                    current[key][prop] = this.value;
                    updatePreview(key);
                });
            });

            // Reset single
            document.querySelectorAll('.va-pill-reset').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var key = this.dataset.key;
                    if(!defaults[key]) return;
                    current[key] = Object.assign({}, defaults[key]);
                    document.querySelectorAll('.va-pill-field[data-key="'+key+'"]').forEach(function(inp){
                        var prop = inp.dataset.prop;
                        var val = current[key][prop] !== undefined ? current[key][prop] : '';
                        inp.value = val;
                        var lbl = document.getElementById('lbl-'+prop+'-'+key);
                        if(lbl) lbl.textContent = val + (inp.type==='range'?'px':'');
                        // swatch
                        if(prop==='text'||prop==='bg'||prop==='border'){
                            var sw = document.getElementById('cswatch-'+key+'-'+prop);
                            if(sw) sw.style.background = val;
                        }
                    });
                    updatePreview(key);
                });
            });

            // Reset all
            document.getElementById('va-pills-reset-all').addEventListener('click', function(){
                if(!confirm('Biztos visszaállítod az összes pill-t?')) return;
                Object.keys(defaults).forEach(function(key){
                    current[key] = Object.assign({}, defaults[key]);
                    document.querySelectorAll('.va-pill-field[data-key="'+key+'"]').forEach(function(inp){
                        var prop = inp.dataset.prop;
                        var val = current[key][prop] !== undefined ? current[key][prop] : '';
                        inp.value = val;
                        var lbl = document.getElementById('lbl-'+prop+'-'+key);
                        if(lbl) lbl.textContent = val + (inp.type==='range'?'px':'');
                        if(prop==='text'||prop==='bg'||prop==='border'){
                            var sw = document.getElementById('cswatch-'+key+'-'+prop);
                            if(sw) sw.style.background = val;
                        }
                    });
                    updatePreview(key);
                });
            });
        })();
        </script>
        <?php
    }

    /* ══════════════════════════════════════════════════════════
     * CUSTOM KÓD SANITIZE
     * ══════════════════════════════════════════════════════════ */
    public static function sanitize_code_field( string $val ): string {
        // Csak </style> és </script> tag-et távolítjuk el hogy ne törjön ki a kontextusból
        return str_replace( [ '</style>', '</script>' ], [ '<\\/style>', '<\\/script>' ], $val );
    }

    /* ══════════════════════════════════════════════════════════
     * CUSTOM KÓD OUTPUT (frontend)
     * ══════════════════════════════════════════════════════════ */
    public static function output_custom_head(): void {
        $head = (string) get_option( 'va_custom_head', '' );
        if ( ! $head ) return;
        // Csak admin állíthatja be (manage_options), direkt output – wp_kses levágja a style/script tartalmát
        echo "\n<!-- VA Custom Head -->\n" . $head . "\n";
    }

    public static function output_custom_css(): void {
        $css = (string) get_option( 'va_custom_css', '' );
        if ( ! $css ) return;
        echo '<style id="va-custom-css">' . "\n" . $css . "\n" . '</style>' . "\n";
    }

    public static function output_custom_js(): void {
        $js = (string) get_option( 'va_custom_js', '' );
        if ( ! $js ) return;
        echo '<script id="va-custom-js">' . "\n" . $js . "\n" . '</script>' . "\n";
    }

    /* ══════════════════════════════════════════════════════════
     * CUSTOM KÓD OLDAL
     * ══════════════════════════════════════════════════════════ */
    public static function render_custom_code(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        $css  = (string) get_option( 'va_custom_css',  '' );
        $js   = (string) get_option( 'va_custom_js',   '' );
        $head = (string) get_option( 'va_custom_head', '' );
        ?>
        <style>
        .va-code-tab-bar { display:flex; gap:6px; border-bottom:1px solid rgba(255,255,255,.1); margin-bottom:0; padding:16px 20px 0; }
        .va-code-tab { padding:8px 18px; border-radius:6px 6px 0 0; cursor:pointer; font-size:13px; font-weight:600; color:rgba(255,255,255,.5); border:1px solid transparent; border-bottom:none; background:transparent; transition:.15s; }
        .va-code-tab.active { color:#fff; background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.12); }
        .va-code-panel { display:none; }
        .va-code-panel.active { display:block; }
        .va-code-editor { width:100%; min-height:360px; font-family:'JetBrains Mono','Fira Code','Cascadia Code',monospace; font-size:13px; line-height:1.6; background:#0a0a0e; color:#e8e8f0; border:1px solid rgba(255,255,255,.1); border-radius:0 0 8px 8px; padding:16px; resize:vertical; box-sizing:border-box; outline:none; tab-size:2; }
        .va-code-editor:focus { border-color:rgba(255,100,100,.4); box-shadow:0 0 0 2px rgba(255,0,0,.08); }
        .va-code-hint { font-size:11px; color:rgba(255,255,255,.35); padding:6px 20px 14px; }
        </style>
        <div class="va-wrap">
            <div class="va-page-header">
                <div>
                    <h1 class="va-page-title">💻 Egyedi kód</h1>
                    <p class="va-page-subtitle">Custom CSS, JavaScript és &lt;head&gt; tartalom – minden oldalon betöltődik</p>
                </div>
            </div>
            <?php if ( isset( $_GET['settings-updated'] ) ): ?>
                <div class="va-alert va-alert--success" style="margin-bottom:16px;">✅ Kód elmentve!</div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_custom_code_settings' ); ?>

                <div class="va-le-card" style="margin-top:20px;padding:0;overflow:hidden;">
                    <div class="va-code-tab-bar">
                        <button type="button" class="va-code-tab active" onclick="vaCodeTab('css',this)">🎨 CSS</button>
                        <button type="button" class="va-code-tab" onclick="vaCodeTab('js',this)">⚡ JavaScript</button>
                        <button type="button" class="va-code-tab" onclick="vaCodeTab('head',this)">📄 &lt;head&gt;</button>
                    </div>

                    <div id="va-code-css" class="va-code-panel active">
                        <textarea name="va_custom_css" class="va-code-editor" spellcheck="false" placeholder="/* Az egyedi CSS ide kerül – minden oldalon a </head> előtt töltődik be */"><?php echo esc_textarea( $css ); ?></textarea>
                        <p class="va-code-hint">💡 Priority: 100 – a téma stílusok UTÁN töltődik be. Nincs szükség !important-ra az egyszerű felülíráshoz.</p>
                    </div>

                    <div id="va-code-js" class="va-code-panel">
                        <textarea name="va_custom_js" class="va-code-editor" spellcheck="false" placeholder="// Az egyedi JavaScript ide kerül – a </body> előtt töltődik be&#10;// document.addEventListener('DOMContentLoaded', function() { ... });"><?php echo esc_textarea( $js ); ?></textarea>
                        <p class="va-code-hint">💡 A kód a &lt;/body&gt; előtt fut le. jQuery elérhető $ névvel.</p>
                    </div>

                    <div id="va-code-head" class="va-code-panel">
                        <textarea name="va_custom_head" class="va-code-editor" spellcheck="false" placeholder="<!-- Meta tagek, külső script/style hivatkozások pl.:&#10;<meta name=&quot;theme-color&quot; content=&quot;#ff0000&quot;>&#10;<link rel=&quot;preconnect&quot; href=&quot;https://...&quot;> -->"><?php echo esc_textarea( $head ); ?></textarea>
                        <p class="va-code-hint">💡 Engedélyezett tagek: &lt;meta&gt; &lt;link&gt; &lt;script&gt; &lt;style&gt;. Inline JavaScript tartalom itt nem fut le.</p>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <?php submit_button( '💾 Kód mentése', 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
        <script>
        function vaCodeTab(id, btn) {
            document.querySelectorAll('.va-code-panel').forEach(function(p){ p.classList.remove('active'); });
            document.querySelectorAll('.va-code-tab').forEach(function(b){ b.classList.remove('active'); });
            document.getElementById('va-code-'+id).classList.add('active');
            btn.classList.add('active');
        }
        // Tab billentyű kezelése a textarea-kban
        document.querySelectorAll('.va-code-editor').forEach(function(el) {
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    var s = this.selectionStart, end = this.selectionEnd;
                    this.value = this.value.substring(0,s) + '  ' + this.value.substring(end);
                    this.selectionStart = this.selectionEnd = s + 2;
                }
            });
        });
        </script>
        <?php
    }

    /* ══════════════════════════════════════════════════════════
     * VERSION CONTROL – SNAPSHOT KEZELŐK
     * ══════════════════════════════════════════════════════════ */
    private static function get_snapshots(): array {
        $raw = (string) get_option( 'va_snapshots', '[]' );
        $arr = json_decode( $raw, true );
        return is_array( $arr ) ? $arr : [];
    }

    private static function save_snapshots( array $arr ): void {
        update_option( 'va_snapshots', wp_json_encode( array_values( $arr ) ) );
    }

    private static function collect_va_options(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'va_%'",
            ARRAY_A
        );
        $data = [];
        foreach ( (array) $rows as $row ) {
            $data[ $row['option_name'] ] = $row['option_value'];
        }
        return $data;
    }

    public static function handle_create_snapshot(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        check_admin_referer( 'va_create_snapshot' );
        $label = sanitize_text_field( wp_unslash( $_POST['snapshot_label'] ?? '' ) );
        if ( ! $label ) $label = 'Mentés ' . wp_date( 'Y.m.d H:i' );
        $snapshots = self::get_snapshots();
        // Max 20 snapshot
        if ( count( $snapshots ) >= 20 ) {
            array_shift( $snapshots );
        }
        $snapshots[] = [
            'id'      => uniqid( 'snap_', true ),
            'label'   => $label,
            'time'    => time(),
            'options' => self::collect_va_options(),
        ];
        self::save_snapshots( $snapshots );
        wp_redirect( add_query_arg( [ 'page' => 'vadaszapro-versions', 'created' => '1' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public static function handle_restore_snapshot(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        check_admin_referer( 'va_restore_snapshot' );
        $snap_id = sanitize_text_field( wp_unslash( $_POST['snapshot_id'] ?? '' ) );
        foreach ( self::get_snapshots() as $snap ) {
            if ( $snap['id'] === $snap_id ) {
                foreach ( (array) $snap['options'] as $key => $val ) {
                    update_option( sanitize_key( $key ), $val );
                }
                wp_redirect( add_query_arg( [ 'page' => 'vadaszapro-versions', 'restored' => '1' ], admin_url( 'admin.php' ) ) );
                exit;
            }
        }
        wp_redirect( add_query_arg( [ 'page' => 'vadaszapro-versions', 'error' => '1' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public static function handle_delete_snapshot(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        check_admin_referer( 'va_delete_snapshot' );
        $snap_id   = sanitize_text_field( wp_unslash( $_POST['snapshot_id'] ?? '' ) );
        $snapshots = array_filter( self::get_snapshots(), fn( $s ) => $s['id'] !== $snap_id );
        self::save_snapshots( array_values( $snapshots ) );
        wp_redirect( add_query_arg( [ 'page' => 'vadaszapro-versions', 'deleted' => '1' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ══════════════════════════════════════════════════════════
     * VERSION CONTROL OLDAL
     * ══════════════════════════════════════════════════════════ */
    public static function render_version_control(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        $snapshots = array_reverse( self::get_snapshots() );
        ?>
        <div class="va-wrap">
            <div class="va-page-header">
                <div>
                    <h1 class="va-page-title">🗂️ Verziókövetés</h1>
                    <p class="va-page-subtitle">Beállítások mentése és visszaállítása – max 20 snapshot</p>
                </div>
            </div>

            <?php if ( isset( $_GET['created'] ) ): ?>
                <div class="va-alert va-alert--success" style="margin-bottom:16px;">✅ Snapshot létrehozva!</div>
            <?php elseif ( isset( $_GET['restored'] ) ): ?>
                <div class="va-alert va-alert--success" style="margin-bottom:16px;">✅ Beállítások visszaállítva!</div>
            <?php elseif ( isset( $_GET['deleted'] ) ): ?>
                <div class="va-alert va-alert--info" style="margin-bottom:16px;">🗑️ Snapshot törölve.</div>
            <?php elseif ( isset( $_GET['error'] ) ): ?>
                <div class="va-alert va-alert--error" style="margin-bottom:16px;">❌ Snapshot nem található.</div>
            <?php endif; ?>

            <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                <div class="va-le-card-hdr">💾 Új snapshot létrehozása</div>
                <div style="padding:16px;">
                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                        <?php wp_nonce_field('va_create_snapshot'); ?>
                        <input type="hidden" name="action" value="va_create_snapshot">
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="text" name="snapshot_label" placeholder="pl. Beállítások v1.0 – launch előtt"
                                   style="flex:1;background:#0e0e0e;border:1px solid rgba(255,255,255,.12);border-radius:6px;padding:9px 12px;color:#fff;font-size:13px;">
                            <?php submit_button( '📸 Snapshot készítése', 'secondary', 'submit', false ); ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                <div class="va-le-card-hdr">📋 Mentett snapshotok (<?php echo count( $snapshots ); ?>/20)</div>
                <?php if ( empty( $snapshots ) ): ?>
                    <div style="padding:24px;text-align:center;color:rgba(255,255,255,.4);font-size:13px;">
                        Még nincs mentett snapshot. Készíts egyet a fenti gombbal!
                    </div>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,.08);">
                                <th style="padding:10px 16px;text-align:left;font-size:12px;color:rgba(255,255,255,.45);">Megnevezés</th>
                                <th style="padding:10px 16px;text-align:left;font-size:12px;color:rgba(255,255,255,.45);">Időpont</th>
                                <th style="padding:10px 16px;text-align:left;font-size:12px;color:rgba(255,255,255,.45);">Opciók száma</th>
                                <th style="padding:10px 16px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $snapshots as $snap ): ?>
                            <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                                <td style="padding:12px 16px;font-size:13px;font-weight:600;"><?php echo esc_html( $snap['label'] ); ?></td>
                                <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.5);"><?php echo esc_html( wp_date( 'Y.m.d H:i', $snap['time'] ) ); ?></td>
                                <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.5);"><?php echo count( (array) $snap['options'] ); ?> beállítás</td>
                                <td style="padding:12px 16px;text-align:right;white-space:nowrap;">
                                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;" onsubmit="return confirm('Biztosan visszaállítod? A jelenlegi beállítások felülíródnak.');">
                                        <?php wp_nonce_field('va_restore_snapshot'); ?>
                                        <input type="hidden" name="action" value="va_restore_snapshot">
                                        <input type="hidden" name="snapshot_id" value="<?php echo esc_attr( $snap['id'] ); ?>">
                                        <button type="submit" class="button button-primary" style="font-size:11px;padding:4px 10px;">↩️ Visszaállítás</button>
                                    </form>
                                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;margin-left:6px;" onsubmit="return confirm('Biztosan törlöd ezt a snapshotot?');">
                                        <?php wp_nonce_field('va_delete_snapshot'); ?>
                                        <input type="hidden" name="action" value="va_delete_snapshot">
                                        <input type="hidden" name="snapshot_id" value="<?php echo esc_attr( $snap['id'] ); ?>">
                                        <button type="submit" class="button" style="font-size:11px;padding:4px 10px;color:#ff4444;border-color:rgba(255,68,68,.3);">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /* ══════════════════════════════════════════════════════════
     * i18n – SEGÉDFÜGGVÉNY
     * ══════════════════════════════════════════════════════════ */
    public static function get_languages(): array {
        return [
            'hu' => [ 'flag' => '🇭🇺', 'name' => 'Magyar',      'locale' => 'hu_HU' ],
            'en' => [ 'flag' => '🇬🇧', 'name' => 'English',     'locale' => 'en_US' ],
            'de' => [ 'flag' => '🇩🇪', 'name' => 'Deutsch',     'locale' => 'de_DE' ],
            'ro' => [ 'flag' => '🇷🇴', 'name' => 'Română',      'locale' => 'ro_RO' ],
            'sk' => [ 'flag' => '🇸🇰', 'name' => 'Slovenčina', 'locale' => 'sk_SK' ],
            'cs' => [ 'flag' => '🇨🇿', 'name' => 'Čeština',    'locale' => 'cs_CZ' ],
            'pl' => [ 'flag' => '🇵🇱', 'name' => 'Polski',      'locale' => 'pl_PL' ],
            'fr' => [ 'flag' => '🇫🇷', 'name' => 'Français',    'locale' => 'fr_FR' ],
            'it' => [ 'flag' => '🇮🇹', 'name' => 'Italiano',    'locale' => 'it_IT' ],
            'es' => [ 'flag' => '🇪🇸', 'name' => 'Español',     'locale' => 'es_ES' ],
            'uk' => [ 'flag' => '🇺🇦', 'name' => 'Українська', 'locale' => 'uk'    ],
            'sr' => [ 'flag' => '🇷🇸', 'name' => 'Srpski',      'locale' => 'sr_RS' ],
            'hr' => [ 'flag' => '🇭🇷', 'name' => 'Hrvatski',    'locale' => 'hr'    ],
            'sl' => [ 'flag' => '🇸🇮', 'name' => 'Slovenščina','locale' => 'sl_SI' ],
        ];
    }

    /* ══════════════════════════════════════════════════════════
     * i18n OLDAL
     * ══════════════════════════════════════════════════════════ */
    public static function render_i18n(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Hozzáférés megtagadva.' );
        $languages    = self::get_languages();
        $active_langs = (array) json_decode( (string) get_option( 'va_active_langs', '["hu"]' ), true );
        $default_lang = (string) get_option( 'va_default_lang', 'hu' );
        $show_sw      = (string) get_option( 'va_lang_show_switcher', '1' );
        $sw_pos       = (string) get_option( 'va_lang_switcher_pos', 'header' );
        ?>
        <div class="va-wrap">
            <div class="va-page-header">
                <div>
                    <h1 class="va-page-title">🌍 Többnyelvűség (i18n)</h1>
                    <p class="va-page-subtitle">Aktív nyelvek, alapértelmezett nyelv és frontend nyelvváltó</p>
                </div>
            </div>
            <?php if ( isset( $_GET['settings-updated'] ) ): ?>
                <div class="va-alert va-alert--success" style="margin-bottom:16px;">✅ Nyelvi beállítások mentve!</div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_i18n_settings' ); ?>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">🏳️ Elérhető nyelvek</div>
                    <div style="padding:16px;">
                        <p style="font-size:12px;color:rgba(255,255,255,.4);margin:0 0 14px;">Jelöld be az aktív nyelveket. Az alapértelmezett mindig aktív.</p>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;">
                        <?php foreach ( $languages as $code => $lang ): ?>
                            <?php $is_active  = in_array( $code, $active_langs, true ); ?>
                            <?php $is_default = ( $code === $default_lang ); ?>
                            <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,<?php echo $is_active ? '.15' : '.06'; ?>);border-radius:8px;padding:10px 14px;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='rgba(255,255,255,.2)'" onmouseout="this.style.borderColor='rgba(255,255,255,<?php echo $is_active ? '.15' : '.06'; ?>)'">
                                <input type="checkbox" name="va_active_langs_cb[]" value="<?php echo esc_attr( $code ); ?>"
                                       <?php checked( $is_active ); ?>
                                       <?php if ( $is_default ) echo 'disabled checked'; ?>
                                       style="accent-color:#ff0000;">
                                <span style="font-size:22px;line-height:1;"><?php echo esc_html( $lang['flag'] ); ?></span>
                                <span style="font-size:13px;font-weight:600;"><?php echo esc_html( $lang['name'] ); ?></span>
                                <?php if ( $is_default ): ?>
                                    <span style="font-size:10px;background:rgba(255,0,0,.15);color:#ff6666;border-radius:4px;padding:2px 6px;margin-left:auto;">alapértelmezett</span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        </div>
                        <!-- Rejtett mező a JSON-hoz – JS frissíti -->
                        <input type="hidden" name="va_active_langs" id="va_active_langs_json" value="<?php echo esc_attr( wp_json_encode( $active_langs ) ); ?>">
                    </div>
                </div>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">⭐ Alapértelmezett nyelv</div>
                    <div style="padding:16px;">
                        <div style="display:flex;flex-wrap:wrap;gap:8px;" id="va-default-lang-radios">
                        <?php foreach ( $languages as $code => $lang ): ?>
                            <?php if ( ! in_array( $code, $active_langs, true ) && $code !== $default_lang ) continue; ?>
                            <label style="display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 14px;cursor:pointer;">
                                <input type="radio" name="va_default_lang" value="<?php echo esc_attr( $code ); ?>"
                                       <?php checked( $default_lang, $code ); ?>
                                       style="accent-color:#ff0000;">
                                <span style="font-size:18px;"><?php echo esc_html( $lang['flag'] ); ?></span>
                                <span style="font-size:13px;"><?php echo esc_html( $lang['name'] ); ?></span>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">🔄 Frontend nyelvváltó</div>
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="padding:14px 16px;">Nyelvváltó megjelenjen</th>
                            <td style="padding:14px 16px;">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                    <input type="checkbox" name="va_lang_show_switcher" value="1" <?php checked( $show_sw, '1' ); ?> style="accent-color:#ff0000;">
                                    <span style="font-size:13px;">Frontend fejlécben megjelenjen a nyelvváltó</span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding:14px 16px;">Pozíció</th>
                            <td style="padding:14px 16px;">
                                <select name="va_lang_switcher_pos" style="background:#0e0e0e;border:1px solid rgba(255,255,255,.12);border-radius:6px;padding:7px 12px;color:#fff;font-size:13px;">
                                    <option value="header" <?php selected( $sw_pos, 'header' ); ?>>Fejléc (jobb oldal)</option>
                                    <option value="footer" <?php selected( $sw_pos, 'footer' ); ?>>Lábléc</option>
                                    <option value="both"   <?php selected( $sw_pos, 'both'   ); ?>>Fejléc + Lábléc</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top:16px;">
                    <?php submit_button( '💾 Nyelvi beállítások mentése', 'primary', 'submit', false ); ?>
                </div>
            </form>

            <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                <div class="va-le-card-hdr">👁️ Előnézet – Nyelvváltó</div>
                <div style="padding:16px;display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ( $active_langs as $code ):
                    if ( ! isset( $languages[$code] ) ) continue;
                    $lang = $languages[$code]; ?>
                    <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:6px 12px;font-size:13px;<?php echo $code === $default_lang ? 'border-color:rgba(255,0,0,.4);' : ''; ?>">
                        <span style="font-size:18px;"><?php echo esc_html( $lang['flag'] ); ?></span>
                        <span><?php echo esc_html( $lang['name'] ); ?></span>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
        (function() {
            // Checkbox változáskor frissíti a JSON hidden inputot
            function syncActiveLangs() {
                var checked = [];
                document.querySelectorAll('input[name="va_active_langs_cb[]"]:checked').forEach(function(cb){
                    checked.push(cb.value);
                });
                document.getElementById('va_active_langs_json').value = JSON.stringify(checked);
            }
            document.querySelectorAll('input[name="va_active_langs_cb[]"]').forEach(function(cb){
                cb.addEventListener('change', syncActiveLangs);
            });
        })();
        </script>
        <?php
    }
}


