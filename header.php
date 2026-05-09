<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>

    <style>
        /* Lang switcher toggle gomb */
        .va-lang-sw__toggle {
            color: #1e3154 !important;
            background: rgba(255,255,255,.88) !important;
            border-color: rgba(30,49,84,.25) !important;
        }
        .va-lang-sw__toggle .va-lang-code { color: #1e3154 !important; }
        .va-lang-sw__toggle svg path { stroke: #1e3154 !important; }
        /* flag-icons: ne törd felül, csak igazíts */
        .va-lang-sw__toggle .fi,
        .va-lang-sw__item .fi {
            vertical-align: middle;
            border-radius: 2px;
            margin-right: 2px;
        }
        /* Dropdown tételek */
        .va-lang-sw__item, .va-lang-sw__item span { color: #fff !important; }
        .va-lang-sw__dropdown {
            background: #1a1a1a !important;
            border: 1px solid rgba(255,255,255,.12) !important;
        }
        .va-lang-sw__item:hover { background: rgba(255,255,255,.09) !important; }
        .va-lang-sw__item.active { background: rgba(255,200,0,.13) !important; color: #fff !important; }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="va-site-wrap<?php echo ! is_front_page() ? ' va-site-wrap--inner' : ''; ?>">
    <?php $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true; ?>
    <?php
    $brand_name = trim( (string) get_option( 'va_site_name', 'weingartnerauto.hu' ) );
    if ( in_array( $brand_name, [ '', 'VadászApró', 'Vadaszapro', 'Weingartner Auto', 'WEINGARTNER AUTÓ', 'WEINGARTNER AUTO' ], true ) ) {
        $brand_name = 'weingartnerauto.hu';
    }
    $brand_icon  = get_option( 'va_brand_icon_url', '' );
    $header_logo = get_option( 'va_header_logo_url', '' );
    $hero_logo   = get_option( 'va_hero_logo_url', '' );
    $header_logo_h = max( 20, min( 120, absint( get_option( 'va_header_logo_height', 36 ) ) ) );
    $hero_logo_h   = max( 30, min( 260, absint( get_option( 'va_hero_logo_height', 72 ) ) ) );
    $hero_logo_pos = sanitize_key( (string) get_option( 'va_hero_logo_position', 'left' ) );
    if ( ! in_array( $hero_logo_pos, [ 'left', 'center', 'right' ], true ) ) {
        $hero_logo_pos = 'left';
    }
    $home_hero_align = sanitize_key( (string) get_option( 'va_home_hero_align', 'left' ) );
    if ( ! in_array( $home_hero_align, [ 'left', 'center', 'right' ], true ) ) {
        $home_hero_align = 'left';
    }
    $header_search_placeholder = trim( (string) get_option( 'va_hf_header_search_placeholder', 'keresés…' ) );
    if ( $header_search_placeholder === '' ) {
        $header_search_placeholder = 'keresés…';
    }
    $header_register_text = trim( (string) get_option( 'va_hf_header_register_text', 'Regisztráció' ) );
    if ( $header_register_text === '' ) {
        $header_register_text = 'Regisztráció';
    }
    $header_login_text = trim( (string) get_option( 'va_hf_header_login_text', 'Bejelentkezés' ) );
    if ( $header_login_text === '' ) {
        $header_login_text = 'Bejelentkezés';
    }
    $header_show_buy_button = get_option( 'va_hf_header_show_buy_button', '1' ) === '1';
    $login_enabled = get_option( 'va_enable_login', '1' ) === '1';
    $register_enabled = get_option( 'va_enable_register', '1' ) === '1';
    if ( $hero_logo === '' ) {
        $hero_logo = $header_logo;
    }
    ?>

    <!-- ═══ Header ══════════════════════════════════════ -->
    <header class="va-header">
        <div class="va-header__inner">
            <!-- Logo -->
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="va-logo">
                <?php if ( ! empty( $header_logo ) ): ?>
                    <img src="<?php echo esc_url( $header_logo ); ?>" class="va-logo__img va-logo__img--header" style="height:<?php echo esc_attr( $header_logo_h ); ?>px;" alt="<?php echo esc_attr( $brand_name ); ?>" loading="eager" decoding="async">
                <?php elseif ( ! empty( $brand_icon ) ): ?>
                    <img src="<?php echo esc_url( $brand_icon ); ?>" class="va-logo__img va-logo__img--icon" style="height:<?php echo esc_attr( $header_logo_h ); ?>px;" alt="<?php echo esc_attr( $brand_name ); ?>" loading="eager" decoding="async">
                <?php else: ?>
                    <span class="va-logo__icon">🦌</span>
                <?php endif; ?>
            </a>

            <!-- Navigáció -->
            <nav class="va-nav" id="va-main-nav">
                <?php
                $nav_items = apply_filters('va_nav_items', (function() {
                    $default = [
                        ['url' => home_url('/va-hirdetes-kereses'), 'label' => 'Hirdetések',       'class' => '', 'enabled' => true],
                        ['url' => home_url('/kategoria'),           'label' => 'Kategóriák',       'class' => '', 'enabled' => true],
                        ['url' => home_url('/kapcsolat'),           'label' => 'Kapcsolat',        'class' => '', 'enabled' => true],
                    ];
                    $json = get_option('va_nav_items_json', '');
                    if (!$json) return $default;
                    $saved = json_decode($json, true);
                    if (!is_array($saved) || empty($saved)) return $default;
                    $result = [];
                    foreach ($saved as $item) {
                        if (empty($item['enabled'])) continue;
                        $url = $item['url'] ?? '';
                        if ($url !== '' && !preg_match('#^https?://#', $url)) {
                            $url = home_url($url);
                        }
                        $result[] = ['url' => $url, 'label' => $item['label'], 'class' => ''];
                    }
                    return $result ?: $default;
                })());
                foreach ( $nav_items as $item ):
                    $cls = 'va-nav__item' . ( $item['class'] ? ' ' . $item['class'] : '' );
                ?>
                    <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo esc_attr($cls); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
            </nav>

            <!-- Kereső -->
            <form class="va-header__search" id="va-live-search-form" role="search" action="<?php echo esc_url( home_url('/va-hirdetes-kereses') ); ?>" method="get" autocomplete="off">
                <input class="va-header__search-input" id="va-live-search-input" type="text" name="s" placeholder="<?php echo esc_attr( $header_search_placeholder ); ?>" autocomplete="off" value="<?php echo esc_attr( get_search_query() ); ?>">
                <button class="va-header__search-btn" type="submit" aria-label="Keresés"></button>
                <div class="va-search-dropdown" id="va-search-dropdown" hidden></div>
            </form>
            <script>
            (function(){
                var ajaxUrl = '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>';
                var auctionsEnabled = <?php echo $auctions_enabled ? 'true' : 'false'; ?>;
                var input   = document.getElementById('va-live-search-input');
                var dropdown= document.getElementById('va-search-dropdown');
                var timer;

                function render(items) {
                    if (!items.length) { dropdown.hidden = true; return; }
                    dropdown.innerHTML = items.map(function(r){
                        return '<a class="va-sd__item" href="'+r.url+'">'
                            + (r.thumb ? '<img class="va-sd__thumb" src="'+r.thumb+'" alt="" loading="lazy">' : '<span class="va-sd__no-img"></span>')
                            + '<span class="va-sd__info"><span class="va-sd__title">'+r.title+'</span>'
                            + (r.price ? '<span class="va-sd__price">'+r.price+'</span>' : '')
                            + '</span>'
                            + '<span class="va-sd__badge va-sd__badge--'+r.type+'">'+(r.type==='va_auction'?'Aukció':r.type==='category'?'Kategória':r.type==='user'?'Felhasználó':'Hirdetés')+'</span>'
                            + '</a>';
                    }).join('') + '<a class="va-sd__all" href="<?php echo esc_url( home_url('/va-hirdetes-kereses') ); ?>" id="va-sd-all-link">Összes találat →</a>';
                    // "Összes találat" link prioritás: kategória → aukció → user → kulcsszó
                    var catItem     = items.find(function(r){ return r.type === 'category'; });
                    var auctionItem = items.find(function(r){ return r.type === 'va_auction'; });
                    var userItem    = items.find(function(r){ return r.type === 'user'; });
                    var allLink     = dropdown.querySelector('#va-sd-all-link');
                    var baseUrl     = '<?php echo esc_url( home_url('/va-hirdetes-kereses') ); ?>';
                    if (catItem) {
                        allLink.href = catItem.url; // ?cat=ID → kategória összes hirdetése
                    } else if (auctionsEnabled && auctionItem) {
                        allLink.href = baseUrl + '?post_type=va_auction&s=' + encodeURIComponent(input.value); // aukciók listája
                    } else if (userItem) {
                        allLink.href = baseUrl + '?user_search=1&q=' + encodeURIComponent(input.value); // összes felhasználó
                    } else {
                        allLink.href = baseUrl + '?s=' + encodeURIComponent(input.value);
                    }
                    dropdown.hidden = false;
                }

                input.addEventListener('input', function(){
                    clearTimeout(timer);
                    var q = this.value.trim();
                    if (q.length < 2) { dropdown.hidden = true; return; }
                    timer = setTimeout(function(){
                        var fd = new FormData();
                        fd.append('action', 'va_live_search');
                        fd.append('q', q);
                        fetch(ajaxUrl, { method:'POST', body:fd })
                            .then(function(r){ return r.json(); })
                            .then(function(d){ if(d.success) render(d.data); });
                    }, 220);
                });

                document.addEventListener('click', function(e){
                    if (!document.getElementById('va-live-search-form').contains(e.target)) {
                        dropdown.hidden = true;
                    }
                });
            })();
            </script>

            <!-- Jobb oldal -->
            <div class="va-header__right">
                <?php if ( is_user_logged_in() ):
                    $user        = wp_get_current_user();
                    $dashboard   = get_page_by_path('va-fiok');
                    $login_page  = get_page_by_path('va-bejelentkezes');
                    $register_page = get_page_by_path('va-regisztracio');
                    $buy_page    = get_page_by_path('va-kredit-vasarlas');
                    $buy_url     = $buy_page ? get_permalink( $buy_page ) : home_url('/va-kredit-vasarlas/');
                    $login_url   = $login_page ? get_permalink( $login_page ) : wp_login_url();
                    $register_url = $register_page ? get_permalink( $register_page ) : wp_registration_url();
                ?>
                    <?php if ( $header_show_buy_button ): ?>
                        <a href="<?php echo esc_url( $buy_url ); ?>" class="va-header__user-login">Vásárlás</a>
                    <?php endif; ?>
                    <?php if ( $login_enabled ): ?>
                        <a href="<?php echo esc_url( $login_url ); ?>" class="va-header__user-login"><?php echo esc_html( $header_login_text ); ?></a>
                    <?php endif; ?>
                    <?php if ( $register_enabled ): ?>
                        <a href="<?php echo esc_url( $register_url ); ?>" class="va-header__submit-btn"><?php echo esc_html( $header_register_text ); ?></a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $dashboard ? get_permalink($dashboard) : home_url() ); ?>" class="va-header__user">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        <?php echo esc_html( $user->display_name ); ?>
                    </a>
                <?php else:
                    $login_page    = get_page_by_path('va-bejelentkezes');
                    $register_page = get_page_by_path('va-regisztracio');
                    $buy_page      = get_page_by_path('va-kredit-vasarlas');
                    $buy_url       = $buy_page ? get_permalink( $buy_page ) : home_url('/va-kredit-vasarlas/');
                    $login_url     = $login_page ? get_permalink( $login_page ) : wp_login_url();
                    $register_url  = $register_page ? get_permalink( $register_page ) : wp_registration_url();
                ?>
                    <?php if ( $header_show_buy_button ): ?>
                        <a href="<?php echo esc_url( wp_login_url( $buy_url ) ); ?>" class="va-header__user-login">Vásárlás</a>
                    <?php endif; ?>
                    <?php if ( $login_enabled ): ?>
                        <a href="<?php echo esc_url( $login_url ); ?>" class="va-header__user-login"><?php echo esc_html( $header_login_text ); ?></a>
                    <?php endif; ?>
                    <?php if ( $register_enabled ): ?>
                        <a href="<?php echo esc_url( $register_url ); ?>" class="va-header__submit-btn"><?php echo esc_html( $header_register_text ); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ( get_option('va_social_header_show','1') === '1' && function_exists('va_social_bar') ):
                    $hdr_style = get_option('va_social_header_style','icons');
                    $hdr_size  = max(14, min(28, absint( get_option('va_social_icon_size', 20) )));
                    echo '<div style="margin-left:5px">' . va_social_bar( $hdr_style, $hdr_size ) . '</div>';
                endif; ?>
                <?php
                $show_sw  = get_option('va_lang_show_switcher','1') === '1';
                $sw_pos   = (string) get_option('va_lang_switcher_pos','header');
                if ( $show_sw && in_array( $sw_pos, ['header','both'], true ) && class_exists('VA_Settings_Page') ) :
                    $all_langs    = VA_Settings_Page::get_languages();
                    $active_langs = (array) json_decode( (string) get_option('va_active_langs','["hu"]'), true );
                    if ( count($active_langs) > 1 ) :
                        // Jelenlegi nyelv a googtrans cookie-ból vagy hu alapértelmezett
                        $curr_code = 'hu';
                        if ( isset( $_COOKIE['googtrans'] ) && preg_match('#^/hu/([a-z]{2})$#', $_COOKIE['googtrans'], $m ) ) {
                            $curr_code = $m[1];
                        }
                        if ( ! isset( $all_langs[ $curr_code ] ) ) $curr_code = 'hu';
                        $curr_lang = $all_langs[ $curr_code ];
                        // Language code → ISO country code mapping for flag-icons
                        $va_flag_map = ['hu'=>'hu','en'=>'gb','de'=>'de','ro'=>'ro','sk'=>'sk','cs'=>'cz','pl'=>'pl','fr'=>'fr','it'=>'it','es'=>'es','uk'=>'ua','sr'=>'rs','hr'=>'hr','sl'=>'si'];
                ?>
                        <div class="va-lang-sw notranslate" id="va-lang-sw" translate="no">
                            <button type="button" class="va-lang-sw__toggle notranslate" id="va-lang-toggle"
                                    aria-haspopup="true" aria-expanded="false" translate="no" style="color:#1e3154 !important;">
                                <?php $fc = isset($va_flag_map[$curr_code]) ? $va_flag_map[$curr_code] : $curr_code; ?>
                                <img src="https://flagcdn.com/<?php echo esc_attr($fc); ?>.svg" width="24" height="18" alt="<?php echo esc_attr(strtoupper($curr_code)); ?>" style="border-radius:2px;vertical-align:middle;display:inline-block;">
                                <span class="va-lang-code" style="color:#1e3154 !important;"><?php echo esc_html( strtoupper($curr_code) ); ?></span>
                                <svg class="va-lang-sw__arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </button>
                            <div class="va-lang-sw__dropdown notranslate" id="va-lang-dropdown" hidden translate="no">
                                <?php foreach ( $active_langs as $code ) :
                                    if ( ! isset( $all_langs[ $code ] ) ) continue;
                                    $lang = $all_langs[ $code ];
                                ?>
                                    <button type="button" class="va-lang-sw__item<?php echo ( $code === $curr_code ) ? ' active' : ''; ?>"
                                            onclick="vaSetLang('<?php echo esc_js($code); ?>')" style="color:#fff !important;">
                                        <?php $fc2 = isset($va_flag_map[$code]) ? $va_flag_map[$code] : $code; ?>
                                        <img src="https://flagcdn.com/<?php echo esc_attr($fc2); ?>.svg" width="24" height="18" alt="<?php echo esc_attr(strtoupper($code)); ?>" style="border-radius:2px;vertical-align:middle;display:inline-block;">
                                        <span style="color:#fff !important;"><?php echo esc_html( $lang['name'] ); ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Google Translate elem (rejtett) -->
                        <div id="google_translate_element" style="display:none"></div>
                        <script>
                        var VA_ACTIVE_LANGS = <?php echo json_encode( array_values($active_langs) ); ?>;
                        // Ország → nyelv térkép
                        var VA_COUNTRY_LANG = {
                            DE:'de',AT:'de',CH:'de',LI:'de',
                            RO:'ro',MD:'ro',
                            SK:'sk',CZ:'cs',PL:'pl',
                            FR:'fr',BE:'fr',LU:'fr',MC:'fr',
                            IT:'it',SM:'it',VA:'it',
                            ES:'es',MX:'es',AR:'es',CO:'es',CL:'es',PE:'es',VE:'es',
                            UA:'uk',
                            RS:'sr',BA:'sr',ME:'sr',
                            HR:'hr',SI:'sl',
                            GB:'en',US:'en',AU:'en',CA:'en',NZ:'en',IE:'en',
                            HU:'hu'
                        };

                        function googleTranslateElementInit() {
                            new google.translate.TranslateElement({
                                pageLanguage: 'hu',
                                autoDisplay: false
                            }, 'google_translate_element');
                        }

                        function vaEnsureTranslateScript() {
                            if (window.__vaGtScriptLoaded) return;
                            window.__vaGtScriptLoaded = true;
                            var s = document.createElement('script');
                            s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                            s.defer = true;
                            document.head.appendChild(s);
                        }

                        function vaGetCookie(name){
                            var m = document.cookie.match('(?:^|;)\\s*' + name + '=([^;]*)');
                            return m ? decodeURIComponent(m[1]) : '';
                        }

                        function vaSetLang(code) {
                            vaEnsureTranslateScript();
                            if (code === 'hu') {
                                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/';
                                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;domain=.' + location.hostname;
                            } else {
                                document.cookie = 'googtrans=/hu/' + code + ';path=/';
                                document.cookie = 'googtrans=/hu/' + code + ';path=/;domain=.' + location.hostname;
                            }
                            document.cookie = 'va_geo_done=1;path=/;max-age=86400';
                            location.reload();
                        }

                        // IP geolokáció – csak ha még nem volt meghatározva
                        (function(){
                            function getCookie(n){
                                var m = document.cookie.match('(?:^|;)\\s*'+n+'=([^;]*)');
                                return m ? decodeURIComponent(m[1]) : null;
                            }
                            if (getCookie('va_geo_done') || getCookie('googtrans')) return;
                            // Nincs beállítva – lekérjük az IP alapú országot
                            fetch('https://ip-api.com/json/?fields=countryCode', {cache:'no-store'})
                                .then(function(r){ return r.json(); })
                                .then(function(d){
                                    var cc   = (d && d.countryCode) ? d.countryCode.toUpperCase() : 'HU';
                                    var lang = VA_COUNTRY_LANG[cc] || 'hu';
                                    // Beállítjuk hogy ne fusson újra
                                    document.cookie = 'va_geo_done=1;path=/;max-age=86400';
                                    // Ha Magyarország vagy nincs az aktív nyelvek közt → marad
                                    if (lang === 'hu' || VA_ACTIVE_LANGS.indexOf(lang) === -1) return;
                                    document.cookie = 'googtrans=/hu/' + lang + ';path=/';
                                    document.cookie = 'googtrans=/hu/' + lang + ';path=/;domain=.' + location.hostname;
                                    location.reload();
                                })
                                .catch(function(){});
                        })();

                        // Dropdown toggle
                        (function(){
                            var toggle   = document.getElementById('va-lang-toggle');
                            var dropdown = document.getElementById('va-lang-dropdown');
                            if (!toggle) return;
                            toggle.addEventListener('click', function(e){
                                vaEnsureTranslateScript();
                                e.stopPropagation();
                                var open = !dropdown.hidden;
                                dropdown.hidden = open;
                                toggle.setAttribute('aria-expanded', String(!open));
                            });
                            document.addEventListener('click', function(){
                                dropdown.hidden = true;
                                toggle.setAttribute('aria-expanded','false');
                            });
                        })();

                        // Ha korabbi nyelvvaltas mar beallitotta a cookie-t, toltsuk be a fordito scriptet.
                        (function(){
                            var gt = vaGetCookie('googtrans');
                            if (gt && gt !== '/hu/hu') {
                                vaEnsureTranslateScript();
                            }
                        })();
                        </script>
                    <?php endif;
                endif; ?>
            </div>
            <button class="va-hamburger" id="va-hamburger" aria-label="Men&uuml;" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>
    <script>
    (function(){
        var btn = document.getElementById('va-hamburger');
        var nav = document.getElementById('va-main-nav');
        if (!btn || !nav) return;
        function close() {
            nav.classList.remove('open');
            btn.classList.remove('open');
            btn.setAttribute('aria-expanded','false');
            document.body.style.overflow = '';
        }
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var open = nav.classList.toggle('open');
            btn.classList.toggle('open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        });
        document.addEventListener('click', function(e){
            if (nav.classList.contains('open') && !nav.contains(e.target) && e.target !== btn) close();
        });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });
    })();
    </script>

    <!-- ═══ HERO (csak főoldalon) ════════════════════ -->
    <?php if ( is_front_page() ):
        $hero_bg_type      = get_option( 'va_home_hero_bg_type', 'video' );
        $hero_video        = get_option( 'va_home_hero_video_url', content_url( 'uploads/2026/04/521380_Gun_Woman_1920x1080.mp4' ) );
        $hero_static_img   = get_option( 'va_home_hero_static_image', '' );
        $carousel_raw      = get_option( 'va_home_hero_carousel_images', '[]' );
        $carousel_imgs     = json_decode( $carousel_raw, true );
        if ( ! is_array( $carousel_imgs ) ) $carousel_imgs = [];
        $carousel_trans    = get_option( 'va_home_hero_carousel_transition', 'fade' );
        $carousel_speed    = (int) get_option( 'va_home_hero_carousel_speed', 800 );
        $carousel_interval = (int) get_option( 'va_home_hero_carousel_interval', 5000 );
        $carousel_arrows   = get_option( 'va_home_hero_carousel_arrows', '1' ) === '1';
        $carousel_dots     = get_option( 'va_home_hero_carousel_dots', '1' ) === '1';
        $submit_page = get_page_by_path('va-hirdetes-feladas');
        $search_page = get_page_by_path('va-hirdetes-kereses');
        $home_badge  = get_option( 'va_home_hero_badge_text', 'Magyarország első vadászati hirdetőoldala' );
        $home_title1 = 'Weingartner';
        $home_title2 = "Autó-Motor\nÉrtékesítés";
        $home_sub    = 'Veszprém';
        $home_cta_1  = get_option( 'va_home_hero_primary_cta_text', 'Kapcsolatfelvetel' );
        if ( preg_match( '/hirdet[eé]s\s*felad/i', (string) $home_cta_1 ) ) {
            $home_cta_1 = 'Kapcsolatfelvetel';
        }
        $home_cta_2  = get_option( 'va_home_hero_secondary_cta_text', 'Hirdetések böngészése →' );
    ?>
    <div class="vh<?php echo $hero_bg_type === 'carousel' ? ' vh--carousel' : ''; ?>"
         <?php if ( $hero_bg_type === 'carousel' ): ?>
         data-transition="<?php echo esc_attr($carousel_trans); ?>"
         data-speed="<?php echo esc_attr($carousel_speed); ?>"
         data-interval="<?php echo esc_attr($carousel_interval); ?>"
         <?php endif; ?>>

        <?php if ( $hero_bg_type === 'video' && $hero_video ): ?>
        <video class="vh__video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
        </video>

        <?php elseif ( $hero_bg_type === 'image' && $hero_static_img ): ?>
        <div class="vh__static-bg" style="background-image:url('<?php echo esc_url($hero_static_img); ?>')" aria-hidden="true"></div>

        <?php elseif ( $hero_bg_type === 'carousel' && ! empty($carousel_imgs) ): ?>
        <div class="vh__carousel-track" aria-hidden="true">
            <?php foreach ( $carousel_imgs as $idx => $img_url ): ?>
            <div class="vh__slide<?php echo $idx === 0 ? ' vh__slide--active' : ''; ?>"
                 style="background-image:url('<?php echo esc_url($img_url); ?>')"></div>
            <?php endforeach; ?>
        </div>
        <?php if ( $carousel_arrows && count($carousel_imgs) > 1 ): ?>
        <button class="vh__carousel-arrow vh__carousel-arrow--prev" aria-label="Előző">&#10094;</button>
        <button class="vh__carousel-arrow vh__carousel-arrow--next" aria-label="Következő">&#10095;</button>
        <?php endif; ?>
        <?php if ( $carousel_dots && count($carousel_imgs) > 1 ): ?>
        <div class="vh__carousel-dots">
            <?php foreach ( $carousel_imgs as $idx => $_ ): ?>
            <button class="vh__carousel-dot<?php echo $idx === 0 ? ' vh__carousel-dot--active' : ''; ?>"
                    aria-label="<?php echo ($idx+1); ?>. dia"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <div class="vh__overlay"></div>

        <div class="vh__content vh__content--<?php echo esc_attr( $home_hero_align ); ?>">
            <?php if ( ! empty( $hero_logo ) ): ?>
                <img src="<?php echo esc_url( $hero_logo ); ?>" class="vh__logo vh__logo--<?php echo esc_attr( $hero_logo_pos ); ?>" style="height:<?php echo esc_attr( $hero_logo_h ); ?>px;" alt="<?php echo esc_attr( $brand_name ); ?>" loading="eager" decoding="async">
            <?php endif; ?>
            <div class="vh__badge"><span class="vcp-hero__badge-dot"></span><?php echo esc_html( $home_badge ); ?></div>
            <h2 class="vh__title">
                <?php echo esc_html( $home_title1 ); ?><span class="vh__title2"><br><?php echo nl2br( esc_html( $home_title2 ) ); ?></span>
            </h2>
            <p class="vh__sub">
                <?php echo esc_html( $home_sub ); ?>
            </p>
            <div class="vh__actions">
                <?php
                $contact_page = get_page_by_path( 'kapcsolat' );
                $contact_url  = $contact_page ? get_permalink( $contact_page ) : home_url( '/kapcsolat' );
                ?>
                <a href="<?php echo esc_url( $contact_url ); ?>" class="vh__btn vh__btn--primary">
                    <?php echo esc_html( $home_cta_1 ); ?>
                </a>
                <a href="<?php echo esc_url( $search_page ? get_permalink($search_page) : home_url('/hirdetes') ); ?>" class="vh__btn vh__btn--ghost">
                    <?php echo esc_html( $home_cta_2 ); ?>
                </a>
            </div>
        </div>

    </div>
    <?php endif; ?>

    <!-- Kategória gyorsmenü (csak főoldalon + archívumban) -->
    <?php
    $archive_types = [ 'va_listing' ];
    if ( $auctions_enabled ) {
        $archive_types[] = 'va_auction';
    }
    ?>
    <?php if ( is_post_type_archive($archive_types) || is_tax(['va_category','va_county']) ): ?>
    <div class="va-cat-bar">
        <div class="va-cat-bar__inner">
            <?php $top_cats = get_terms(['taxonomy' => 'va_category', 'parent' => 0, 'hide_empty' => false, 'number' => 10]);
            foreach ($top_cats as $cat):
                $icon = va_category_icon($cat->term_id);
            ?>
            <a href="<?php echo esc_url( get_term_link($cat) ); ?>" class="va-cat-item">
                <span class="va-cat-item__icon"><?php echo $icon; ?></span>
                <span><?php echo esc_html($cat->name); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tartalom wrapper -->
    <?php if ( ! is_page( 'va-fiok' ) ) : ?>
    <main class="va-container">
        <div class="va-content-layout">

            <!-- Fő tartalom (ide jön a content) -->
            <div class="va-main-content">
                <?php if ( is_singular() ) va_breadcrumb(); ?>
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('content_top'); ?>
    <?php endif; ?>
