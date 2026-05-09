<?php
/**
 * VA_Page_Renderer – blokk alapú oldalak frontend renderelése
 * A blokkok JSON formátumban tárolódnak a 'va_page_blocks' post_meta-ban.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Page_Renderer {

    public static function init(): void {
        add_filter( 'the_content', [ __CLASS__, 'maybe_render_blocks' ], 1 );
        add_action( 'wp_head',     [ __CLASS__, 'maybe_inject_css' ] );
    }

    /* ── CSS alap ──────────────────────────────────────────── */
    public static function maybe_inject_css(): void {
        if ( ! is_singular( 'page' ) && ! is_front_page() && ! is_home() ) return;
        $post_id = (int) get_the_ID();
        if ( ! get_post_meta( $post_id, 'va_page_blocks', true ) ) return;
        echo '<style id="va-pb-base">
        .va-pb{box-sizing:border-box;}.va-pb *,.va-pb *::before,.va-pb *::after{box-sizing:inherit;}
        .va-pb-wrap{margin:0;padding:0;}
        .va-pb-cnt{max-width:1200px;margin:0 auto;padding:0 clamp(16px,4vw,48px);}
        .va-pb-btn{display:inline-flex;align-items:center;gap:8px;padding:13px 30px;border-radius:10px;font-weight:700;font-size:15px;text-decoration:none;cursor:pointer;border:2px solid transparent;transition:all .2s ease;line-height:1;}
        .va-pb-btn--primary{background:#cc0000;color:#fff!important;border-color:#cc0000;}
        .va-pb-btn--primary:hover{background:#aa0000;border-color:#aa0000;}
        .va-pb-btn--outline{background:transparent;color:inherit;border-color:currentColor;}
        .va-pb-btn--outline:hover{background:rgba(255,255,255,.08);}
        .va-pb-btn--ghost{background:transparent;border-color:transparent;text-decoration:underline;}
        .va-pb-eyebrow{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:12px;}
        @media(max-width:900px){
            .va-pb-wrap{margin-left:15px!important;margin-right:15px!important;overflow-x:hidden;}
        }
        @media(max-width:768px){
            .va-pb-imgtext-grid{grid-template-columns:1fr!important;}
            .va-pb-cards-grid{grid-template-columns:repeat(2,1fr)!important;}
            .va-pb-hero{min-height:0!important;padding-top:40px!important;padding-bottom:40px!important;align-items:flex-start!important;}
            .va-pb-cnt{padding-left:16px!important;padding-right:16px!important;}
        }
        @media(max-width:420px){
            .va-pb-hero{padding-top:28px!important;padding-bottom:28px!important;}
            .va-pb-hero__btns{flex-direction:column!important;align-items:flex-start!important;gap:10px!important;}
            .va-pb-btn{padding:12px 22px!important;font-size:14px!important;width:auto!important;}
        }
        @media(max-width:480px){.va-pb-cards-grid{grid-template-columns:1fr!important;}}
        </style>' . "\n";
    }

    /* ── Tartalom csere ────────────────────────────────────── */
    public static function maybe_render_blocks( string $content ): string {
        if ( ! is_singular( 'page' ) ) return $content;
        $post_id     = (int) get_the_ID();
        $blocks_json = get_post_meta( $post_id, 'va_page_blocks', true );
        if ( empty( $blocks_json ) ) return $content;
        $blocks = json_decode( $blocks_json, true );
        if ( ! is_array( $blocks ) || empty( $blocks ) ) return $content;
        return self::render_blocks( $blocks );
    }

    /* ── Blokk lista render ────────────────────────────────── */
    public static function render_blocks( array $blocks ): string {
        $out = '<div class="va-pb-wrap">';
        foreach ( $blocks as $block ) {
            $type = (string) ( $block['type'] ?? '' );
            $s    = (array) ( $block['settings'] ?? [] );
            switch ( $type ) {
                case 'hero':     $out .= self::render_hero( $s );     break;
                case 'text':     $out .= self::render_text( $s );     break;
                case 'img_text': $out .= self::render_img_text( $s ); break;
                case 'cta':      $out .= self::render_cta( $s );      break;
                case 'cards':    $out .= self::render_cards( $s );    break;
                case 'divider':  $out .= self::render_divider( $s );  break;
            }
        }
        $out .= '</div>';
        return $out;
    }

    /* ── Segéd: biztonságos szín ───────────────────────────── */
    private static function color( array $s, string $k, string $def ): string {
        $v = trim( (string) ( $s[ $k ] ?? '' ) );
        if ( $v && preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9,.\s%]+\)|hsl[a]?\([0-9,.\s%deg]+\))$/', $v ) ) return $v;
        return $def;
    }

    private static function n( array $s, string $k, int $def ): int {
        return (int) ( $s[ $k ] ?? $def );
    }

    private static function t( array $s, string $k, string $def = '' ): string {
        return esc_html( (string) ( $s[ $k ] ?? $def ) );
    }

    private static function u( array $s, string $k, string $def = '#' ): string {
        return esc_url( (string) ( $s[ $k ] ?? $def ) );
    }

    private static function align( array $s, string $def = 'center' ): string {
        $v = $s['text_align'] ?? $def;
        return in_array( $v, [ 'left', 'center', 'right' ], true ) ? $v : $def;
    }

    private static function btn_style( string $v ): string {
        return in_array( $v, [ 'primary', 'outline', 'ghost' ], true ) ? $v : 'primary';
    }

    private static function bg_css( array $s ): string {
        $type = $s['bg_type'] ?? 'gradient';
        if ( $type === 'image' ) {
            $url = esc_url( (string) ( $s['bg_image_url'] ?? '' ) );
            $ov  = min( 100, max( 0, self::n( $s, 'bg_overlay', 50 ) ) );
            $rgba = 'rgba(0,0,0,' . round( $ov / 100, 2 ) . ')';
            return $url ? "background:linear-gradient({$rgba},{$rgba}),url('{$url}') center/cover no-repeat;" : 'background:#0a0a0a;';
        }
        if ( $type === 'color' ) {
            return 'background:' . self::color( $s, 'bg_color', '#0a0a0a' ) . ';';
        }
        // gradient default
        $gs = self::color( $s, 'bg_gradient_start', '#0a0a0a' );
        $ge = self::color( $s, 'bg_gradient_end',   '#1a0505' );
        $ga = self::n( $s, 'bg_gradient_angle', 135 );
        return "background:linear-gradient({$ga}deg,{$gs},{$ge});";
    }

    /* ══ HERO ══════════════════════════════════════════════════ */
    private static function render_hero( array $s ): string {
        $tc  = self::color( $s, 'text_color',   '#ffffff' );
        $acc = self::color( $s, 'accent_color', '#cc0000' );
        $mh  = self::n( $s, 'min_height', 500 );
        $py  = self::n( $s, 'padding_y', 80 );
        $ta  = self::align( $s );
        $bg  = self::bg_css( $s );
        $mw  = $ta === 'center' ? '820px' : '100%';
        $mx  = $ta === 'center' ? 'auto' : '0';

        $eyebrow  = self::t( $s, 'eyebrow' );
        $title    = self::t( $s, 'title', 'Főcím' );
        $subtitle = self::t( $s, 'subtitle' );
        $ey_html  = $eyebrow  ? "<div class=\"va-pb-eyebrow\" style=\"color:{$acc}\">{$eyebrow}</div>" : '';
        $sub_html = $subtitle ? "<p style=\"color:rgba(255,255,255,.65);font-size:clamp(15px,2vw,19px);margin:0 0 32px;line-height:1.6;\">{$subtitle}</p>" : '';

        $btns = '';
        $b1t  = self::t( $s, 'btn1_text' );
        if ( $b1t ) {
            $b1u  = self::u( $s, 'btn1_url' );
            $b1s  = self::btn_style( $s['btn1_style'] ?? 'primary' );
            $btns .= "<a href=\"{$b1u}\" class=\"va-pb-btn va-pb-btn--{$b1s}\">{$b1t}</a> ";
        }
        $b2t  = self::t( $s, 'btn2_text' );
        if ( $b2t ) {
            $b2u  = self::u( $s, 'btn2_url' );
            $b2s  = self::btn_style( $s['btn2_style'] ?? 'outline' );
            $btns .= "<a href=\"{$b2u}\" class=\"va-pb-btn va-pb-btn--{$b2s}\" style=\"color:{$tc};\">{$b2t}</a>";
        }
        $btn_html = $btns ? "<div class=\"va-pb-hero__btns\" style=\"display:flex;gap:12px;flex-wrap:wrap;" . ( $ta === 'center' ? 'justify-content:center;' : '' ) . "\">{$btns}</div>" : '';

        return "<section class=\"va-pb va-pb-hero\" style=\"{$bg}color:{$tc};min-height:{$mh}px;padding:{$py}px 0;display:flex;align-items:center;\">
  <div class=\"va-pb-cnt\" style=\"width:100%;text-align:{$ta};\">
    <div style=\"max-width:{$mw};margin:{$mx};\">
      {$ey_html}
      <h1 style=\"color:{$tc};font-size:clamp(28px,5.5vw,68px);font-weight:800;line-height:1.08;margin:0 0 20px;letter-spacing:-.02em;\">{$title}</h1>
      {$sub_html}
      {$btn_html}
    </div>
  </div>
</section>";
    }

    /* ══ TEXT ══════════════════════════════════════════════════ */
    private static function render_text( array $s ): string {
        $content = wp_kses_post( (string) ( $s['content'] ?? '' ) );
        $tc   = self::color( $s, 'text_color', '#e8e8f0' );
        $bg   = self::color( $s, 'bg_color',   '#060606' );
        $ta   = self::align( $s, 'left' );
        $fs   = self::n( $s, 'font_size', 16 );
        $mw   = self::n( $s, 'max_width', 800 );
        $py   = self::n( $s, 'padding_y', 60 );
        return "<section class=\"va-pb\" style=\"background:{$bg};padding:{$py}px 0;\">
  <div class=\"va-pb-cnt\">
    <div style=\"max-width:{$mw}px;margin:0 auto;color:{$tc};font-size:{$fs}px;text-align:{$ta};line-height:1.75;\">{$content}</div>
  </div>
</section>";
    }

    /* ══ IMAGE + TEXT ══════════════════════════════════════════ */
    private static function render_img_text( array $s ): string {
        $img_side = ( $s['image_side'] ?? 'left' ) === 'right' ? 'right' : 'left';
        $img_url  = self::u( $s, 'image_url', '' );
        $eyebrow  = self::t( $s, 'eyebrow' );
        $title    = self::t( $s, 'title', 'Cím' );
        $content  = wp_kses_post( (string) ( $s['content'] ?? '' ) );
        $btn_t    = self::t( $s, 'btn_text' );
        $btn_u    = self::u( $s, 'btn_url' );
        $bg       = self::color( $s, 'bg_color',     '#060606' );
        $tc       = self::color( $s, 'text_color',   '#ffffff' );
        $acc      = self::color( $s, 'accent_color', '#cc0000' );
        $py       = self::n( $s, 'padding_y', 80 );
        $r        = self::n( $s, 'image_border_radius', 12 );

        $img_order  = $img_side === 'right' ? 2 : 1;
        $text_order = $img_side === 'right' ? 1 : 2;

        $ey_html  = $eyebrow ? "<div class=\"va-pb-eyebrow\" style=\"color:{$acc}\">{$eyebrow}</div>" : '';
        $btn_html = $btn_t   ? "<a href=\"{$btn_u}\" class=\"va-pb-btn va-pb-btn--primary\" style=\"margin-top:24px;\">{$btn_t}</a>" : '';
        $img_html = $img_url
            ? "<img src=\"{$img_url}\" alt=\"\" style=\"width:100%;border-radius:{$r}px;display:block;\">"
            : "<div style=\"width:100%;aspect-ratio:4/3;background:rgba(255,255,255,.05);border-radius:{$r}px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:14px;\">Nincs kép</div>";

        return "<section class=\"va-pb\" style=\"background:{$bg};padding:{$py}px 0;\">
  <div class=\"va-pb-cnt\">
    <div class=\"va-pb-imgtext-grid\" style=\"display:grid;grid-template-columns:1fr 1fr;gap:clamp(32px,5vw,72px);align-items:center;\">
      <div style=\"order:{$img_order};\">{$img_html}</div>
      <div style=\"order:{$text_order};\">
        {$ey_html}
        <h2 style=\"color:{$tc};font-size:clamp(24px,3.5vw,44px);font-weight:800;margin:0 0 16px;line-height:1.15;\">{$title}</h2>
        <div style=\"color:rgba(255,255,255,.62);font-size:16px;line-height:1.72;\">{$content}</div>
        {$btn_html}
      </div>
    </div>
  </div>
</section>";
    }

    /* ══ CTA ═══════════════════════════════════════════════════ */
    private static function render_cta( array $s ): string {
        $eyebrow  = self::t( $s, 'eyebrow' );
        $title    = self::t( $s, 'title', 'CTA Cím' );
        $subtitle = self::t( $s, 'subtitle' );
        $tc       = self::color( $s, 'text_color',   '#ffffff' );
        $acc      = self::color( $s, 'accent_color', '#cc0000' );
        $py       = self::n( $s, 'padding_y', 80 );
        $layout   = ( $s['layout'] ?? 'centered' ) === 'split' ? 'split' : 'centered';

        // bg
        $bgt = $s['bg_type'] ?? 'gradient';
        if ( $bgt === 'color' ) {
            $bg = 'background:' . self::color( $s, 'bg_color', '#0a0a0a' ) . ';';
        } else {
            $gs = self::color( $s, 'bg_gradient_start', '#1a0505' );
            $ge = self::color( $s, 'bg_gradient_end',   '#0a0a0a' );
            $bg = "background:linear-gradient(135deg,{$gs},{$ge});";
        }

        $ey_html  = $eyebrow  ? "<div class=\"va-pb-eyebrow\" style=\"color:{$acc}\">{$eyebrow}</div>" : '';
        $sub_html = $subtitle ? "<p style=\"color:rgba(255,255,255,.6);font-size:16px;margin:10px 0 0;\">{$subtitle}</p>" : '';

        $btns = '';
        $b1t  = self::t( $s, 'btn1_text' );
        if ( $b1t ) $btns .= "<a href=\"" . self::u( $s, 'btn1_url' ) . "\" class=\"va-pb-btn va-pb-btn--primary\">{$b1t}</a>";
        $b2t  = self::t( $s, 'btn2_text' );
        if ( $b2t ) $btns .= " <a href=\"" . self::u( $s, 'btn2_url' ) . "\" class=\"va-pb-btn va-pb-btn--outline\" style=\"color:{$tc};\">{$b2t}</a>";

        if ( $layout === 'split' ) {
            return "<section class=\"va-pb\" style=\"{$bg}padding:{$py}px 0;\">
  <div class=\"va-pb-cnt\">
    <div style=\"display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;\">
      <div>{$ey_html}<h2 style=\"color:{$tc};font-size:clamp(22px,3vw,36px);font-weight:800;margin:0;\">{$title}</h2>{$sub_html}</div>
      <div style=\"display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0;\">{$btns}</div>
    </div>
  </div>
</section>";
        }

        return "<section class=\"va-pb\" style=\"{$bg}padding:{$py}px 0;\">
  <div class=\"va-pb-cnt\" style=\"text-align:center;\">
    {$ey_html}
    <h2 style=\"color:{$tc};font-size:clamp(28px,4.5vw,52px);font-weight:800;margin:0 0 8px;\">{$title}</h2>
    {$sub_html}
    <div style=\"display:flex;gap:12px;justify-content:center;margin-top:32px;flex-wrap:wrap;\">{$btns}</div>
  </div>
</section>";
    }

    /* ══ CARDS ═════════════════════════════════════════════════ */
    private static function render_cards( array $s ): string {
        $sec_title = self::t( $s, 'section_title' );
        $sec_sub   = self::t( $s, 'section_subtitle' );
        $cols      = min( 4, max( 2, self::n( $s, 'columns', 3 ) ) );
        $bg        = self::color( $s, 'bg_color',     '#060606' );
        $card_bg   = self::color( $s, 'card_bg',      'rgba(255,255,255,.04)' );
        $card_bdr  = self::color( $s, 'card_border',  'rgba(255,255,255,.08)' );
        $ic        = self::color( $s, 'icon_color',   '#cc0000' );
        $titc      = self::color( $s, 'title_color',  '#ffffff' );
        $tc        = self::color( $s, 'text_color',   'rgba(255,255,255,.6)' );
        $py        = self::n( $s, 'padding_y', 80 );
        $cards     = (array) ( $s['cards'] ?? [] );

        $header = '';
        if ( $sec_title ) {
            $header  = "<div style=\"text-align:center;margin-bottom:52px;\">";
            $header .= "<h2 style=\"color:#fff;font-size:clamp(24px,3.5vw,42px);font-weight:800;margin:0 0 12px;\">{$sec_title}</h2>";
            if ( $sec_sub ) $header .= "<p style=\"color:rgba(255,255,255,.5);font-size:16px;margin:0;\">{$sec_sub}</p>";
            $header .= "</div>";
        }

        $cards_html = '';
        foreach ( $cards as $card ) {
            $icon    = esc_html( (string) ( $card['icon']      ?? '●' ) );
            $c_tit   = esc_html( (string) ( $card['title']     ?? '' ) );
            $c_txt   = esc_html( (string) ( $card['text']      ?? '' ) );
            $c_link  = esc_url(  (string) ( $card['link_url']  ?? '' ) );
            $c_ltext = esc_html( (string) ( $card['link_text'] ?? '' ) );
            $lnk     = ( $c_link && $c_ltext )
                ? "<a href=\"{$c_link}\" style=\"color:{$ic};font-size:13px;font-weight:600;text-decoration:none;margin-top:14px;display:inline-block;\">{$c_ltext} →</a>"
                : '';
            $cards_html .= "<div style=\"background:{$card_bg};border:1px solid {$card_bdr};border-radius:16px;padding:28px;display:flex;flex-direction:column;\">
    <div style=\"font-size:34px;color:{$ic};margin-bottom:16px;line-height:1;\">{$icon}</div>
    <div style=\"font-size:17px;font-weight:700;color:{$titc};margin-bottom:8px;\">{$c_tit}</div>
    <div style=\"font-size:14px;color:{$tc};line-height:1.65;flex:1;\">{$c_txt}</div>
    {$lnk}
  </div>";
        }

        return "<section class=\"va-pb\" style=\"background:{$bg};padding:{$py}px 0;\">
  <div class=\"va-pb-cnt\">
    {$header}
    <div class=\"va-pb-cards-grid\" style=\"display:grid;grid-template-columns:repeat({$cols},1fr);gap:24px;\">{$cards_html}</div>
  </div>
</section>";
    }

    /* ══ DIVIDER / SPACER ══════════════════════════════════════ */
    private static function render_divider( array $s ): string {
        $type   = in_array( $s['type'] ?? 'spacer', [ 'spacer', 'line', 'dots' ], true ) ? $s['type'] : 'spacer';
        $h      = self::n( $s, 'height', 60 );
        $bg     = self::color( $s, 'bg_color', '#060606' );

        if ( $type === 'spacer' ) {
            return "<div class=\"va-pb\" style=\"background:{$bg};height:{$h}px;\"></div>";
        }

        $lc = self::color( $s, 'line_color', 'rgba(255,255,255,.08)' );

        if ( $type === 'dots' ) {
            $dc   = min( 20, max( 3, self::n( $s, 'dot_count', 5 ) ) );
            $dots = str_repeat( "<span style=\"width:6px;height:6px;border-radius:50%;background:{$lc};display:inline-block;\"></span>", $dc );
            return "<div class=\"va-pb\" style=\"background:{$bg};height:{$h}px;display:flex;align-items:center;justify-content:center;\">
  <div style=\"display:flex;gap:14px;\">{$dots}</div>
</div>";
        }

        $ls = in_array( $s['line_style'] ?? 'solid', [ 'solid', 'dashed', 'dotted' ], true ) ? $s['line_style'] : 'solid';
        return "<div class=\"va-pb\" style=\"background:{$bg};height:{$h}px;display:flex;align-items:center;\">
  <div class=\"va-pb-cnt\" style=\"width:100%;\">
    <hr style=\"border:none;border-top:1px {$ls} {$lc};margin:0;\">
  </div>
</div>";
    }
}
