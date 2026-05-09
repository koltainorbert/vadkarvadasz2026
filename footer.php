            </div><!-- .va-main-content -->
<?php if ( ! is_page( 'va-fiok' ) ) : ?>
            </div><!-- .va-main-content -->

            <!-- Jobb oldalsáv -->
            <aside class="va-sidebar va-sidebar--right">
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('sidebar_right'); ?>
            </aside>

        </div><!-- .va-content-layout -->
    </main><!-- .va-container -->

    <!-- ═══ Footer reklám ════════════════════════════════ -->
    <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('footer_top'); ?>

    <?php
    $f_brand_title    = trim( (string) get_option( 'va_hf_footer_brand_title', 'weingartnerauto.hu' ) );
    $f_cat_title      = trim( (string) get_option( 'va_hf_footer_col_categories_title', 'Kategóriák' ) );
    $f_account_title  = trim( (string) get_option( 'va_hf_footer_col_account_title', 'Fiók' ) );
    $f_legal_title    = trim( (string) get_option( 'va_hf_footer_col_legal_title', 'Jogi információk' ) );
    $f_link_aszf      = trim( (string) get_option( 'va_hf_footer_link_aszf', 'ÁSZF' ) );
    $f_link_privacy   = trim( (string) get_option( 'va_hf_footer_link_privacy', 'Adatvédelmi nyilatkozat' ) );
    $f_link_contact   = trim( (string) get_option( 'va_hf_footer_link_contact', 'Kapcsolat' ) );
    $f_link_help      = trim( (string) get_option( 'va_hf_footer_link_help', 'Súgó' ) );
    $f_copy_text      = trim( (string) get_option( 'va_hf_footer_copy_text', 'weingartnerauto.hu - Minden jog fenntartva.' ) );
    $f_privacy_bottom = trim( (string) get_option( 'va_hf_footer_privacy_text', 'Adatvédelem' ) );
    $f_logo_url       = trim( (string) get_option( 'va_hf_footer_logo_url', '' ) );
    $f_logo_height    = max( 20, min( 180, absint( get_option( 'va_hf_footer_logo_height', 48 ) ) ) );
    $f_contact_email  = trim( (string) get_option( 'va_contact_email', 'weingartnertrans@gmail.com' ) );
    $f_contact_phone  = trim( (string) get_option( 'va_billing_phone', '+36 20 943 8636' ) );
    $f_contact_addr   = trim( (string) get_option( 'va_billing_company_address', '8412 Veszprém, Alsó-Újsor utca 31.' ) );
    if ( in_array( $f_brand_title, [ 'VadászApró', 'Vadaszapro', 'Weingartner Auto' ], true ) ) {
        $f_brand_title = 'weingartnerauto.hu';
    }
    if ( in_array( $f_copy_text, [ 'VadászApró – Minden jog fenntartva.', 'Vadaszapro - Minden jog fenntartva.', 'Weingartner Auto - Minden jog fenntartva.' ], true ) ) {
        $f_copy_text = 'weingartnerauto.hu - Minden jog fenntartva.';
    }
    $f_contact_phone_href = preg_replace( '/[^0-9\+]/', '', $f_contact_phone );
    $legacy_legal_map = [
        '/adatvedelmi-nyilatkozat' => '/adatkezeles',
        '/aszf'                    => '/etika',
    ];
    $map_legacy_legal_url = static function( string $url ) use ( $legacy_legal_map ): string {
        $clean = trim( $url );
        if ( isset( $legacy_legal_map[ $clean ] ) ) {
            return $legacy_legal_map[ $clean ];
        }
        return $clean;
    };
    $f_legal_items = [
        [ 'label' => 'Adatvédelem',                         'url' => $map_legacy_legal_url( trim( (string) get_option( 'va_legal_url_adatvedelem', '/adatkezeles' ) ) ) ],
        [ 'label' => 'ÁSZF',                                'url' => $map_legacy_legal_url( trim( (string) get_option( 'va_legal_url_aszf', '/etika' ) ) ) ],
        [ 'label' => 'Impresszum',                          'url' => trim( (string) get_option( 'va_legal_url_impresszum', '' ) ) ],
        [ 'label' => 'Etika és Üzleti Magatartási Kódex',   'url' => trim( (string) get_option( 'va_legal_url_etika', '' ) ) ],
        [ 'label' => 'Sütik',                               'url' => trim( (string) get_option( 'va_legal_url_sutik', '' ) ) ],
        [ 'label' => 'GDPR Adatkezelési Tájékoztató',       'url' => trim( (string) get_option( 'va_legal_url_gdpr', '' ) ) ],
        [ 'label' => 'Fenntartható Fejlődés Irányelve',     'url' => trim( (string) get_option( 'va_legal_url_fenntarthato', '' ) ) ],
    ];

    if ( $f_brand_title === '' )    $f_brand_title = 'weingartnerauto.hu';
    if ( $f_cat_title === '' )      $f_cat_title = 'Kategóriák';
    if ( $f_account_title === '' )  $f_account_title = 'Fiók';
    if ( $f_legal_title === '' )    $f_legal_title = 'Jogi információk';
    if ( $f_link_aszf === '' )      $f_link_aszf = 'ÁSZF';
    if ( $f_link_privacy === '' )   $f_link_privacy = 'Adatvédelmi nyilatkozat';
    if ( $f_link_contact === '' )   $f_link_contact = 'Kapcsolat';
    if ( $f_link_help === '' )      $f_link_help = 'Súgó';
    if ( $f_copy_text === '' )      $f_copy_text = 'weingartnerauto.hu - Minden jog fenntartva.';
    if ( $f_privacy_bottom === '' ) $f_privacy_bottom = 'Adatvédelem';
    if ( $f_contact_email === '' )  $f_contact_email = 'weingartnertrans@gmail.com';
    if ( $f_contact_phone === '' )  $f_contact_phone = '+36 20 943 8636';
    if ( $f_contact_addr === '' )   $f_contact_addr = '8412 Veszprém, Alsó-Újsor utca 31.';
    ?>

    <!-- ═══ Footer ═══════════════════════════════════════ -->
    <footer class="va-footer">
        <div class="va-footer__grid">
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_brand_title ); ?></div>
                <?php if ( $f_logo_url !== '' ): ?>
                    <img src="<?php echo esc_url( $f_logo_url ); ?>" class="va-footer__brand-logo" style="height:<?php echo esc_attr( $f_logo_height ); ?>px;" alt="<?php echo esc_attr( $f_brand_title ); ?>" loading="lazy" decoding="async">
                <?php endif; ?>
                <p style="font-size:12px;color:#fff;line-height:1.6;"><?php echo esc_html(get_option('va_site_description', 'Magyarország vadászati apróhirdetési oldala')); ?></p>
                <div class="va-footer__col-title" style="margin-top:12px;">Kapcsolat</div>
                <div class="va-footer__link" style="line-height:1.55;">Cím: <?php echo esc_html( $f_contact_addr ); ?></div>
                <a href="tel:<?php echo esc_attr( $f_contact_phone_href ); ?>" class="va-footer__link">Telefon: <?php echo esc_html( $f_contact_phone ); ?></a>
                <a href="mailto:<?php echo esc_attr( $f_contact_email ); ?>" class="va-footer__link">Email: <?php echo esc_html( $f_contact_email ); ?></a>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_cat_title ); ?></div>
                <?php
                if ( class_exists('VA_Settings_Page') ) :
                    $all_langs    = VA_Settings_Page::get_languages();
                    $active_langs = (array) json_decode( (string) get_option('va_active_langs','["hu"]'), true );
                    $curr_code    = 'hu';
                    if ( isset( $_COOKIE['googtrans'] ) && preg_match('#^/hu/([a-z]{2})$#', sanitize_text_field( wp_unslash( $_COOKIE['googtrans'] ) ), $cm ) ) {
                        $curr_code = $cm[1];
                    }
                    if ( ! isset( $all_langs[ $curr_code ] ) ) $curr_code = 'hu';
                    $va_flag_map_f = ['hu'=>'hu','en'=>'gb','de'=>'de','ro'=>'ro','sk'=>'sk','cs'=>'cz','pl'=>'pl','fr'=>'fr','it'=>'it','es'=>'es','uk'=>'ua','sr'=>'rs','hr'=>'hr','sl'=>'si'];
                    if ( count($active_langs) > 1 ) :
                        $fc_curr = isset($va_flag_map_f[$curr_code]) ? $va_flag_map_f[$curr_code] : $curr_code;
                ?>
                    <div class="va-lang-sw va-lang-sw--footer notranslate" translate="no">
                        <button type="button" class="va-lang-sw__toggle notranslate" id="va-lang-toggle-footer" aria-haspopup="true" aria-expanded="false" translate="no">
                            <img src="https://flagcdn.com/<?php echo esc_attr($fc_curr); ?>.svg" width="22" height="16" alt="<?php echo esc_attr(strtoupper($curr_code)); ?>" style="border-radius:2px;vertical-align:middle;display:inline-block;">
                            <span class="va-lang-code"><?php echo esc_html( strtoupper($curr_code) ); ?></span>
                            <svg class="va-lang-sw__arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </button>
                        <div class="va-lang-sw__dropdown va-lang-sw__dropdown--up notranslate" id="va-lang-dropdown-footer" hidden translate="no">
                            <?php foreach ( $active_langs as $lcode ) :
                                if ( ! isset( $all_langs[ $lcode ] ) ) continue;
                                $lname = $all_langs[ $lcode ]['name'];
                                $fc2   = isset($va_flag_map_f[$lcode]) ? $va_flag_map_f[$lcode] : $lcode;
                            ?>
                                <button type="button" class="va-lang-sw__item<?php echo ( $lcode === $curr_code ) ? ' active' : ''; ?> notranslate"
                                        onclick="vaSetLang('<?php echo esc_js($lcode); ?>')" translate="no">
                                    <img src="https://flagcdn.com/<?php echo esc_attr($fc2); ?>.svg" width="22" height="16" alt="<?php echo esc_attr(strtoupper($lcode)); ?>" style="border-radius:2px;vertical-align:middle;display:inline-block;">
                                    <span><?php echo esc_html( $lname ); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <script>
                    (function(){
                        var t = document.getElementById('va-lang-toggle-footer');
                        var d = document.getElementById('va-lang-dropdown-footer');
                        if (!t || !d) return;
                        t.addEventListener('click', function(e){
                            e.stopPropagation();
                            var open = !d.hidden;
                            d.hidden = open;
                            t.setAttribute('aria-expanded', String(!open));
                        });
                        document.addEventListener('click', function(){
                            d.hidden = true;
                            t.setAttribute('aria-expanded','false');
                        });
                    })();
                    </script>
                <?php endif;
                endif; ?>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_account_title ); ?></div>
                <?php
                $fp = [
                    'va-hirdetes-kereses' => 'Hirdetések böngészése',
                ];
                foreach ($fp as $slug => $label) {
                    $p = get_page_by_path($slug);
                    if ($p) echo '<a href="' . esc_url(get_permalink($p)) . '" class="va-footer__link">' . esc_html($label) . '</a>';
                }
                ?>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_legal_title ); ?></div>
                <?php foreach ( $f_legal_items as $legal_item ):
                    $legal_url = trim( wp_strip_all_tags( (string) ( $legal_item['url'] ?? '' ) ) );
                    if ( $legal_url === '' ) continue;
                    if ( preg_match( '#^https?://#i', $legal_url ) ) {
                        $legal_url = esc_url_raw( $legal_url, [ 'http', 'https' ] );
                    } else {
                        $legal_url = home_url( '/' . ltrim( $legal_url, '/' ) );
                    }
                    if ( $legal_url === '' ) continue;
                ?>
                    <a href="<?php echo esc_url( $legal_url ); ?>" class="va-footer__link"><?php echo esc_html( (string) $legal_item['label'] ); ?></a>
                <?php endforeach; ?>
                <?php $help_url = home_url( '/sugo/' ); ?>
                <a href="<?php echo esc_url( home_url( '/etika/' ) ); ?>" class="va-footer__link">Etika és Üzleti Kódex</a>
                <a href="<?php echo esc_url( home_url( '/impresszum/' ) ); ?>" class="va-footer__link">Impresszum</a>
                <a href="<?php echo esc_url( home_url( '/adatkezeles/' ) ); ?>" class="va-footer__link">Adatkezelési tájékoztató</a>
                <a href="<?php echo esc_url( $help_url ); ?>" class="va-footer__link"><?php echo esc_html( $f_link_help ); ?></a>
            </div>
        </div>
        <div class="va-footer__bottom">
            <?php if ( get_option('va_social_footer_show','1') === '1' && function_exists('va_social_bar') ):
                $ftr_style = get_option('va_social_footer_style','icons');
                $ftr_size  = max(14, min(28, absint( get_option('va_social_icon_size', 20) )));
                echo va_social_bar( $ftr_style, $ftr_size );
            endif; ?>
            &copy; <?php echo date('Y'); ?> <?php echo esc_html( $f_copy_text ); ?> |
            <a href="<?php echo esc_url(home_url('/adatkezeles/')); ?>"><?php echo esc_html( $f_privacy_bottom ); ?></a>
        </div>
    </footer>
<?php endif; // ! is_page('va-fiok') ?>

</div><!-- .va-site-wrap -->

<script>
(function(){
    // Hamburger + scroll-aware header
    var hdr  = document.querySelector('.va-header');
    var hbtn = document.getElementById('va-hamburger');
    var nav  = document.getElementById('va-main-nav');

    // Scroll: header glass-effect bekapcsol
    function onScroll(){
        if( window.scrollY > 40 ) hdr.classList.add('scrolled');
        else hdr.classList.remove('scrolled');
    }
    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('resize', onScroll);
    onScroll();

    // Hamburger toggle
    if(hbtn && nav){
        hbtn.addEventListener('click', function(){
            var open = nav.classList.toggle('open');
            hbtn.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
            document.body.classList.toggle('nav-open', open);
        });
        // Kattintás nav-on kívül zárja
        document.addEventListener('click', function(e){
            if(nav.classList.contains('open') && !nav.contains(e.target) && e.target !== hbtn && !hbtn.contains(e.target)){
                nav.classList.remove('open');
                hbtn.classList.remove('open');
                document.body.style.overflow = '';
                document.body.classList.remove('nav-open');
            }
        });
    }

    // Aktiv nav item
    var cur = location.pathname;
    document.querySelectorAll('.va-nav__item').forEach(function(a){
        if(a.getAttribute('href') && cur.indexOf(a.getAttribute('href')) === 0 && a.getAttribute('href') !== '/'){
            a.classList.add('active');
        }
    });
})();
</script>

<?php wp_footer(); ?>

<?php
$va_scroll_ring_video_url = trim( (string) get_option( 'va_scroll_ring_video_url', content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' ) ) );
if ( $va_scroll_ring_video_url === '' ) {
    $va_scroll_ring_video_url = content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' );
}
$va_scroll_ring_border_color = trim( (string) get_option( 'va_scroll_ring_border_color', '#00e676' ) );
if ( $va_scroll_ring_border_color === '' ) {
    $va_scroll_ring_border_color = '#00e676';
}
?>

<?php if ( ! is_page( 'va-fiok' ) ) : ?>

<!-- ── Scroll-progress pill videó widget ──────────────────── -->
<div id="va-scroll-ring" role="button" aria-label="Vissza a tetejére" tabindex="0" style="--va-scroll-ring-color: <?php echo esc_attr( $va_scroll_ring_border_color ); ?>;">    <!-- progress border SVG (pill alak) – pathLength=100 → nincs kerület-hiba -->
    <svg id="va-ring-svg" viewBox="0 0 178 66" width="178" height="66" aria-hidden="true" style="position:absolute;top:0;left:0;pointer-events:none;z-index:3;">
        <rect x="2" y="2" width="174" height="62" rx="31" fill="none" stroke="rgba(255,255,255,0.13)" stroke-width="1.5" pathLength="100"/>
        <rect id="va-ring-el" x="2" y="2" width="174" height="62" rx="31" fill="none"
            stroke="var(--va-scroll-ring-color)" stroke-width="1.8" stroke-linecap="round"
            pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"
            transform="rotate(180 89 33)"
            style="transition:stroke-dashoffset .12s linear;"/>
    </svg>
    <!-- videó + bal arrow réteg -->
    <div id="va-ring-inner">
        <video autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $va_scroll_ring_video_url ); ?>" type="video/mp4">
        </video>
        <!-- bal oldali sötét átmenet + nyil -->
        <div id="va-ring-arrow">
            <div class="va-arr">
                <svg viewBox="0 0 32 20" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" width="32" height="20"><polyline points="4 16 16 4 28 16"/></svg>
            </div>
        </div>
    </div>
</div>

<style>
#va-scroll-ring {
    position: fixed;
    right: 18px;
    bottom: 18px;
    width: 178px;
    height: 66px;
    z-index: 9999;
    cursor: pointer;
    opacity: 0;
    transform: translateY(14px);
    pointer-events: none;
    transition: opacity .3s, transform .3s;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
}
#va-scroll-ring.va-ring--visible {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
#va-scroll-ring:hover #va-ring-el { stroke: var(--va-scroll-ring-color); }
#va-scroll-ring:hover #va-ring-inner { transform: scale(1.03); }
@media (max-width: 767px) { #va-scroll-ring { display: none !important; } }

#va-ring-inner {
    position: absolute;
    top: 4px; left: 4px; right: 4px; bottom: 4px;
    border-radius: 28px;
    overflow: hidden;
    transition: transform .2s;
}
#va-ring-inner video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
#va-ring-arrow {
    position: absolute;
    top: 0; left: 0;
    width: 62px;
    height: 100%;
    background: linear-gradient(to right, rgba(0,0,0,.78) 50%, transparent);
    display: flex;
    align-items: center;
    justify-content: center;
}
.va-arr {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
    animation: va-arr-bounce 2.8s ease-in-out infinite;
}
.va-arr svg:first-child { animation: va-arr-fade 2.8s ease-in-out infinite; }
@keyframes va-arr-bounce {
    0%,100% { transform: translateY(2px); }
    50%      { transform: translateY(-4px); }
}
@keyframes va-arr-fade {
    0%,100% { opacity: 1; }
    50%      { opacity: .5; }
}
</style>
<script>
(function(){
    var ring  = document.getElementById('va-scroll-ring');
    var el    = document.getElementById('va-ring-el');
    var perim = 100;
    function update() {
        var doc     = document.documentElement;
        var scrollH = doc.scrollHeight - doc.clientHeight;
        var pct     = scrollH > 0 ? window.scrollY / scrollH : 0;
        el.style.strokeDashoffset = perim * (1 - pct);
        ring.classList.toggle('va-ring--visible', window.scrollY > 80);
    }
    window.addEventListener('scroll', update, {passive:true});
    ring.addEventListener('click', function(){ window.scrollTo({top:0, behavior:'smooth'}); });
    ring.addEventListener('keydown', function(e){
        if(e.key==='Enter'||e.key===' '){ window.scrollTo({top:0,behavior:'smooth'}); }
    });
    update();
})();
</script>
<?php endif; ?>
</body>
</html>
