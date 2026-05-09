<?php
/**
 * VA_Page_Builder – drag-and-drop oldalszerkesztő admin felülete
 * URL: admin.php?page=vadaszapro-oldalak
 * Szerkesztő: admin.php?page=vadaszapro-oldalak&va_action=edit&post_id=X
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Page_Builder {

    public static function init(): void {
        add_action( 'wp_ajax_va_pb_save',       [ __CLASS__, 'ajax_save'        ] );
        add_action( 'wp_ajax_va_pb_get',        [ __CLASS__, 'ajax_get'         ] );
        add_action( 'wp_ajax_va_pb_new_page',   [ __CLASS__, 'ajax_new_page'    ] );
        add_action( 'wp_ajax_va_pb_delete_page',[ __CLASS__, 'ajax_delete_page' ] );
    }

    /* ── AJAX: mentés ──────────────────────────────────────── */
    public static function ajax_save(): void {
        check_ajax_referer( 'va_pb_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( [ 'msg' => 'Nincs jogosultság' ] );

        $post_id    = (int) ( $_POST['post_id']    ?? 0 );
        $blocks_raw = (string) wp_unslash( $_POST['blocks'] ?? '' );
        $page_title = sanitize_text_field( wp_unslash( $_POST['page_title'] ?? '' ) );
        $page_status= in_array( $_POST['page_status'] ?? 'publish', [ 'publish', 'draft' ], true )
                        ? sanitize_key( $_POST['page_status'] ) : 'publish';

        if ( ! $post_id || get_post_type( $post_id ) !== 'page' ) {
            wp_send_json_error( [ 'msg' => 'Érvénytelen oldal' ] );
        }

        // JSON validálás
        $decoded = json_decode( $blocks_raw, true );
        if ( ! is_array( $decoded ) ) wp_send_json_error( [ 'msg' => 'Érvénytelen blokk adat' ] );

        // Blokkok sanitálása – csak engedélyezett mezők
        $clean_blocks = self::sanitize_blocks( $decoded );
        update_post_meta( $post_id, 'va_page_blocks', wp_json_encode( $clean_blocks ) );

        if ( $page_title ) {
            wp_update_post( [ 'ID' => $post_id, 'post_title' => $page_title, 'post_status' => $page_status ] );
        }

        wp_send_json_success( [ 'msg' => 'Mentve', 'url' => get_permalink( $post_id ) ] );
    }

    /* ── AJAX: blokkok lekérése ────────────────────────────── */
    public static function ajax_get(): void {
        check_ajax_referer( 'va_pb_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $post_id = (int) ( $_GET['post_id'] ?? 0 );
        $json    = get_post_meta( $post_id, 'va_page_blocks', true );
        $blocks  = $json ? json_decode( $json, true ) : [];

        wp_send_json_success( [ 'blocks' => is_array( $blocks ) ? $blocks : [] ] );
    }

    /* ── AJAX: új oldal ────────────────────────────────────── */
    public static function ajax_new_page(): void {
        check_ajax_referer( 'va_pb_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $title  = sanitize_text_field( wp_unslash( $_POST['title'] ?? 'Névtelen oldal' ) );
        $pid    = wp_insert_post( [
            'post_type'   => 'page',
            'post_title'  => $title,
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        ] );

        if ( is_wp_error( $pid ) ) wp_send_json_error( [ 'msg' => $pid->get_error_message() ] );

        wp_send_json_success( [
            'post_id' => $pid,
            'title'   => $title,
            'url'     => get_permalink( $pid ),
            'edit_url'=> admin_url( 'admin.php?page=vadaszapro-oldalak&va_action=edit&post_id=' . $pid ),
        ] );
    }

    /* ── AJAX: oldal törlése ───────────────────────────────── */
    public static function ajax_delete_page(): void {
        check_ajax_referer( 'va_pb_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $post_id = (int) ( $_POST['post_id'] ?? 0 );
        if ( ! $post_id || get_post_type( $post_id ) !== 'page' ) wp_send_json_error();

        wp_trash_post( $post_id );
        wp_send_json_success();
    }

    /* ── Blokkok sanitálása ────────────────────────────────── */
    private static function sanitize_blocks( array $blocks ): array {
        $allowed_types = [ 'hero', 'text', 'img_text', 'cta', 'cards', 'divider' ];
        $clean = [];
        foreach ( $blocks as $block ) {
            if ( ! is_array( $block ) ) continue;
            $type = sanitize_key( (string) ( $block['type'] ?? '' ) );
            if ( ! in_array( $type, $allowed_types, true ) ) continue;
            $s = (array) ( $block['settings'] ?? [] );
            // Mélységkorlátozás – max 3 szint (cards tömb)
            $clean[] = [
                'id'       => sanitize_text_field( (string) ( $block['id'] ?? uniqid( 'b_', true ) ) ),
                'type'     => $type,
                'settings' => self::sanitize_settings( $type, $s ),
            ];
        }
        return $clean;
    }

    private static function sanitize_settings( string $type, array $s ): array {
        $c = [];
        // szöveg mezők
        $text_keys = [ 'eyebrow','title','subtitle','btn1_text','btn2_text','btn_text','section_title','section_subtitle','image_side','text_align','btn1_style','btn2_style','bg_type','layout' ];
        foreach ( $text_keys as $k ) {
            if ( isset( $s[ $k ] ) ) $c[ $k ] = sanitize_text_field( (string) $s[ $k ] );
        }
        // wp_kses_post mezők
        foreach ( [ 'content' ] as $k ) {
            if ( isset( $s[ $k ] ) ) $c[ $k ] = wp_kses_post( (string) $s[ $k ] );
        }
        // URL mezők
        foreach ( [ 'btn1_url','btn2_url','btn_url','image_url','bg_image_url' ] as $k ) {
            if ( isset( $s[ $k ] ) ) $c[ $k ] = esc_url_raw( (string) $s[ $k ] );
        }
        // Szín mezők
        $color_keys = [ 'bg_color','bg_gradient_start','bg_gradient_end','text_color','accent_color','line_color','card_bg','card_border','icon_color','title_color' ];
        foreach ( $color_keys as $k ) {
            if ( isset( $s[ $k ] ) ) {
                $v = trim( (string) $s[ $k ] );
                if ( preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9,.\s%]+\)|hsl[a]?\([0-9,.\s%deg]+\))$/', $v ) ) {
                    $c[ $k ] = $v;
                }
            }
        }
        // Szám mezők
        $int_keys = [ 'min_height','padding_y','font_size','max_width','bg_overlay','bg_gradient_angle','height','dot_count','columns','image_border_radius' ];
        foreach ( $int_keys as $k ) {
            if ( isset( $s[ $k ] ) ) $c[ $k ] = (int) $s[ $k ];
        }
        // Kártyák tömb
        if ( $type === 'cards' && isset( $s['cards'] ) && is_array( $s['cards'] ) ) {
            $c['cards'] = [];
            foreach ( $s['cards'] as $card ) {
                if ( ! is_array( $card ) ) continue;
                $c['cards'][] = [
                    'icon'      => sanitize_text_field( (string) ( $card['icon']      ?? '' ) ),
                    'title'     => sanitize_text_field( (string) ( $card['title']     ?? '' ) ),
                    'text'      => sanitize_text_field( (string) ( $card['text']      ?? '' ) ),
                    'link_url'  => esc_url_raw( (string) ( $card['link_url']  ?? '' ) ),
                    'link_text' => sanitize_text_field( (string) ( $card['link_text'] ?? '' ) ),
                ];
            }
        }
        return $c;
    }

    /* ══════════════════════════════════════════════════════════
       ADMIN UI – RENDER
    ══════════════════════════════════════════════════════════ */
    public static function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $action  = sanitize_key( $_GET['va_action'] ?? '' );
        $post_id = (int) ( $_GET['post_id'] ?? 0 );

        if ( $action === 'edit' && $post_id && get_post_type( $post_id ) === 'page' ) {
            self::render_editor( $post_id );
        } else {
            self::render_list();
        }
    }

    /* ── Lista nézet ───────────────────────────────────────── */
    private static function render_list(): void {
        $nonce = wp_create_nonce( 'va_pb_nonce' );

        $pages = get_posts( [
            'post_type'      => 'page',
            'post_status'    => [ 'publish', 'draft' ],
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ] );

        ?>
        <style>
        .va-pb-list-wrap{max-width:1100px;}
        .va-pb-list-hero{background:linear-gradient(135deg,rgba(204,0,0,.07),transparent),rgba(14,14,18,.95);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:24px 32px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;}
        .va-pb-list-hero h1{margin:0;font-size:20px;color:#fff;font-weight:700;}
        .va-pb-list-hero p{margin:4px 0 0;font-size:13px;color:rgba(255,255,255,.45);}
        .va-pb-new-btn{background:#cc0000;color:#fff!important;border:none;padding:10px 22px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:background .15s;}
        .va-pb-new-btn:hover{background:#aa0000;}
        .va-pb-table{width:100%;border-collapse:collapse;}
        .va-pb-table th{background:rgba(255,255,255,.03);padding:10px 14px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.35);border-bottom:1px solid rgba(255,255,255,.07);}
        .va-pb-table td{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.05);color:#e8e8f0;font-size:14px;vertical-align:middle;}
        .va-pb-table tr:hover td{background:rgba(255,255,255,.02);}
        .va-pb-status{font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:3px 8px;border-radius:20px;}
        .va-pb-status--publish{background:rgba(74,222,128,.12);color:#4ade80;}
        .va-pb-status--draft{background:rgba(255,255,255,.07);color:rgba(255,255,255,.4);}
        .va-pb-has-blocks{font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;background:rgba(204,0,0,.15);color:#ff6060;}
        .va-pb-action-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;cursor:pointer;border:none;transition:all .15s;}
        .va-pb-action-btn--edit{background:#cc0000;color:#fff!important;}
        .va-pb-action-btn--edit:hover{background:#aa0000;}
        .va-pb-action-btn--view{background:rgba(255,255,255,.07);color:rgba(255,255,255,.7)!important;}
        .va-pb-action-btn--view:hover{background:rgba(255,255,255,.12);}
        .va-pb-action-btn--del{background:rgba(255,60,60,.1);color:#ff6060!important;}
        .va-pb-action-btn--del:hover{background:rgba(255,60,60,.2);}
        .va-pb-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;}
        .va-pb-modal-overlay.open{display:flex;}
        .va-pb-modal{background:#111118;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:28px;width:400px;max-width:90vw;}
        .va-pb-modal h2{margin:0 0 16px;font-size:18px;color:#fff;}
        .va-pb-modal input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:10px 14px;color:#e8e8f0;font-size:14px;box-sizing:border-box;}
        .va-pb-modal-btns{display:flex;gap:10px;margin-top:18px;justify-content:flex-end;}
        .va-pb-modal-cancel{background:rgba(255,255,255,.06);color:rgba(255,255,255,.6);border:none;padding:8px 18px;border-radius:7px;cursor:pointer;font-size:13px;}
        </style>

        <div class="wrap va-admin-wrap va-pb-list-wrap">
            <div class="va-pb-list-hero">
                <div>
                    <h1>📄 Oldalszerkesztő</h1>
                    <p>WordPress oldalak szerkesztése blokk alapú drag-and-drop szerkesztővel.</p>
                </div>
                <button class="va-pb-new-btn" onclick="document.getElementById('va-pb-modal').classList.add('open')">
                    + Új oldal
                </button>
            </div>

            <table class="va-pb-table">
                <thead>
                    <tr>
                        <th>Cím</th>
                        <th>Slug</th>
                        <th>Státusz</th>
                        <th>Blokkok</th>
                        <th>Dátum</th>
                        <th>Műveletek</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $pages as $p ):
                    $has_blocks = (bool) get_post_meta( $p->ID, 'va_page_blocks', true );
                    $edit_url   = admin_url( 'admin.php?page=vadaszapro-oldalak&va_action=edit&post_id=' . $p->ID );
                    $view_url   = get_permalink( $p->ID );
                ?>
                <tr>
                    <td><strong><?php echo esc_html( $p->post_title ); ?></strong></td>
                    <td><code style="font-size:12px;color:rgba(255,255,255,.4);">/<?php echo esc_html( $p->post_name ); ?>/</code></td>
                    <td><span class="va-pb-status va-pb-status--<?php echo esc_attr( $p->post_status ); ?>"><?php echo $p->post_status === 'publish' ? 'Közzétett' : 'Piszkozat'; ?></span></td>
                    <td><?php if ( $has_blocks ): ?><span class="va-pb-has-blocks">✓ Van blokk</span><?php else: ?><span style="color:rgba(255,255,255,.25);font-size:12px;">–</span><?php endif; ?></td>
                    <td style="font-size:12px;color:rgba(255,255,255,.35);"><?php echo esc_html( wp_date( 'Y.m.d', strtotime( $p->post_date ) ) ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $edit_url ); ?>" class="va-pb-action-btn va-pb-action-btn--edit">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            Szerkesztés
                        </a>
                        <a href="<?php echo esc_url( $view_url ); ?>" target="_blank" class="va-pb-action-btn va-pb-action-btn--view">↗ Nézet</a>
                        <button class="va-pb-action-btn va-pb-action-btn--del" onclick="deletePage(<?php echo esc_attr( (string) $p->ID ); ?>, '<?php echo esc_js( $p->post_title ); ?>')">Törlés</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ( empty( $pages ) ): ?>
                <tr><td colspan="6" style="text-align:center;padding:40px;color:rgba(255,255,255,.3);">Még nincsenek oldalak. Hozz létre egyet!</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Új oldal modal -->
        <div class="va-pb-modal-overlay" id="va-pb-modal">
            <div class="va-pb-modal">
                <h2>Új oldal létrehozása</h2>
                <input type="text" id="va-pb-new-title" placeholder="Oldal neve (pl. Rólunk)" oninput="this.value=this.value" onkeydown="if(event.key==='Enter')createPage()">
                <div class="va-pb-modal-btns">
                    <button class="va-pb-modal-cancel" onclick="document.getElementById('va-pb-modal').classList.remove('open')">Mégse</button>
                    <button class="va-pb-new-btn" onclick="createPage()" id="va-pb-create-btn">Létrehozás →</button>
                </div>
            </div>
        </div>

        <script>
        var VA_PB_NONCE = '<?php echo esc_js( $nonce ); ?>';
        var VA_PB_AJAX  = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

        function createPage() {
            var title = document.getElementById('va-pb-new-title').value.trim();
            if (!title) { document.getElementById('va-pb-new-title').focus(); return; }
            var btn = document.getElementById('va-pb-create-btn');
            btn.textContent = 'Létrehozás...';
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action','va_pb_new_page');
            fd.append('nonce', VA_PB_NONCE);
            fd.append('title', title);
            fetch(VA_PB_AJAX, { method:'POST', body:fd })
                .then(r=>r.json())
                .then(d=>{ if(d.success) window.location.href = d.data.edit_url; else alert(d.data.msg||'Hiba'); });
        }

        function deletePage(pid, title) {
            if (!confirm('Biztosan kukába dobod: "' + title + '"?')) return;
            var fd = new FormData();
            fd.append('action','va_pb_delete_page');
            fd.append('nonce', VA_PB_NONCE);
            fd.append('post_id', pid);
            fetch(VA_PB_AJAX, { method:'POST', body:fd })
                .then(r=>r.json())
                .then(d=>{ if(d.success) location.reload(); else alert('Hiba!'); });
        }
        </script>
        <?php
    }

    /* ════════════════════════════════════════════════════════════
       SZERKESZTŐ
    ════════════════════════════════════════════════════════════ */
    private static function render_editor( int $post_id ): void {
        $post  = get_post( $post_id );
        if ( ! $post ) { echo '<p>Az oldal nem található.</p>'; return; }

        $nonce   = wp_create_nonce( 'va_pb_nonce' );
        $blocks  = get_post_meta( $post_id, 'va_page_blocks', true );
        $blocks  = $blocks ? $blocks : '[]';
        $view_url= get_permalink( $post_id );
        $list_url= admin_url( 'admin.php?page=vadaszapro-oldalak' );
        ?>
        <style>
        /* Szerkesztő teljes layout */
        #wpbody-content .wrap { max-width:none; }
        .va-pbe { display:flex; height:calc(100vh - var(--va-topbar-h,60px)); overflow:hidden; background:var(--va-bg,#070709); gap:0; }
        /* Bal panel – blokk paletta */
        .va-pbe__palette { width:200px; flex-shrink:0; background:var(--va-bg2,#0d0d11); border-right:1px solid rgba(255,255,255,.07); display:flex; flex-direction:column; overflow:hidden; }
        .va-pbe__palette-head { padding:14px 14px 10px; font-size:11px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:rgba(255,255,255,.35); border-bottom:1px solid rgba(255,255,255,.06); }
        .va-pbe__palette-list { flex:1; overflow-y:auto; padding:8px; display:flex; flex-direction:column; gap:5px; }
        .va-pbe__block-type { display:flex; align-items:center; gap:10px; padding:10px 10px; border-radius:8px; cursor:pointer; transition:background .15s; user-select:none; border:1px solid transparent; }
        .va-pbe__block-type:hover { background:rgba(255,255,255,.05); border-color:rgba(255,255,255,.08); }
        .va-pbe__block-type__dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
        .va-pbe__block-type__label { font-size:13px; color:rgba(255,255,255,.7); font-weight:600; }
        /* Közép – vászon */
        .va-pbe__canvas-wrap { flex:1; display:flex; flex-direction:column; overflow:hidden; }
        .va-pbe__topbar { height:52px; background:rgba(255,255,255,.02); border-bottom:1px solid rgba(255,255,255,.07); display:flex; align-items:center; gap:10px; padding:0 16px; flex-shrink:0; }
        .va-pbe__topbar input { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:7px; padding:7px 12px; color:#e8e8f0; font-size:14px; font-weight:600; flex:1; max-width:320px; }
        .va-pbe__topbar input:focus { border-color:rgba(204,0,0,.5); outline:none; }
        .va-pbe__topbar-right { display:flex; align-items:center; gap:8px; margin-left:auto; }
        .va-pbe__btn { padding:7px 16px; border-radius:7px; font-size:13px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
        .va-pbe__btn--primary { background:#cc0000; color:#fff; }
        .va-pbe__btn--primary:hover { background:#aa0000; }
        .va-pbe__btn--secondary { background:rgba(255,255,255,.07); color:rgba(255,255,255,.7); }
        .va-pbe__btn--secondary:hover { background:rgba(255,255,255,.12); }
        .va-pbe__btn--back { background:transparent; color:rgba(255,255,255,.45); font-size:12px; }
        .va-pbe__btn--back:hover { color:rgba(255,255,255,.8); }
        .va-pbe__status-select { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:7px; padding:7px 10px; color:#e8e8f0; font-size:12px; cursor:pointer; }
        .va-pbe__canvas { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:8px; }
        .va-pbe__empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px; color:rgba(255,255,255,.2); }
        .va-pbe__empty svg { opacity:.3; }
        .va-pbe__empty p { margin:0; font-size:14px; }
        /* Blokk kártya */
        .va-pbe-block { background:rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.07); border-radius:10px; display:flex; align-items:stretch; cursor:pointer; transition:all .15s; overflow:hidden; }
        .va-pbe-block:hover { border-color:rgba(255,255,255,.15); background:rgba(255,255,255,.06); }
        .va-pbe-block.selected { border-color:#cc0000; box-shadow:0 0 0 3px rgba(204,0,0,.15); }
        .va-pbe-block__accent { width:4px; flex-shrink:0; border-radius:2px 0 0 2px; }
        .va-pbe-block__drag { width:32px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.2); cursor:grab; font-size:16px; flex-shrink:0; }
        .va-pbe-block__drag:active { cursor:grabbing; }
        .va-pbe-block__info { flex:1; padding:11px 12px; min-width:0; }
        .va-pbe-block__type { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:rgba(255,255,255,.3); margin-bottom:2px; }
        .va-pbe-block__preview { font-size:13px; color:#e8e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .va-pbe-block__actions { display:flex; align-items:center; gap:4px; padding:0 8px; }
        .va-pbe-block__btn { width:28px; height:28px; border:none; background:transparent; color:rgba(255,255,255,.35); cursor:pointer; border-radius:5px; font-size:14px; display:flex; align-items:center; justify-content:center; transition:all .15s; }
        .va-pbe-block__btn:hover { background:rgba(255,255,255,.1); color:#fff; }
        .va-pbe-block__btn--del:hover { background:rgba(255,60,60,.15); color:#ff6060; }
        /* Jobb panel – beállítások */
        .va-pbe__settings { width:300px; flex-shrink:0; background:var(--va-bg2,#0d0d11); border-left:1px solid rgba(255,255,255,.07); display:flex; flex-direction:column; overflow:hidden; }
        .va-pbe__settings-head { padding:14px 16px 12px; font-size:12px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:rgba(255,255,255,.35); border-bottom:1px solid rgba(255,255,255,.06); flex-shrink:0; }
        .va-pbe__settings-body { flex:1; overflow-y:auto; padding:10px 12px 20px; }
        .va-pbe__no-selection { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:rgba(255,255,255,.2); font-size:13px; gap:8px; }
        /* Settings mezők */
        .pb-section { margin-bottom:6px; border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,.06); }
        .pb-section__header { padding:9px 12px; background:rgba(255,255,255,.03); font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:rgba(255,255,255,.4); cursor:pointer; display:flex; align-items:center; justify-content:space-between; user-select:none; }
        .pb-section__header:hover { background:rgba(255,255,255,.05); }
        .pb-section__arr { transition:transform .2s; }
        .pb-section.collapsed .pb-section__arr { transform:rotate(-90deg); }
        .pb-section__body { padding:10px 10px 12px; display:flex; flex-direction:column; gap:8px; }
        .pb-section.collapsed .pb-section__body { display:none; }
        .pb-field { display:flex; flex-direction:column; gap:4px; }
        .pb-field__label { font-size:10px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:rgba(255,255,255,.4); }
        .pb-input { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:7px; padding:7px 10px; color:#e8e8f0; font-size:13px; width:100%; box-sizing:border-box; transition:border-color .15s; font-family:inherit; }
        .pb-input:focus { border-color:rgba(204,0,0,.5); outline:none; }
        .pb-textarea { min-height:80px; resize:vertical; }
        .pb-select { cursor:pointer; }
        .pb-number { }
        .pb-color-wrap { display:flex; gap:7px; align-items:center; }
        .pb-color-wrap input[type=color] { width:38px; height:34px; border:1px solid rgba(255,255,255,.12); border-radius:7px; padding:2px; cursor:pointer; background:rgba(255,255,255,.05); flex-shrink:0; }
        .pb-color-text { flex:1; }
        .pb-media-wrap { display:flex; gap:6px; }
        .pb-media-url { flex:1; }
        .pb-btn { padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:background .15s; }
        .pb-btn--sm { background:rgba(255,255,255,.08); color:rgba(255,255,255,.7); }
        .pb-btn--sm:hover { background:rgba(255,255,255,.15); }
        .pb-toggle { position:relative; width:36px; height:20px; flex-shrink:0; }
        .pb-toggle input { opacity:0; width:0; height:0; position:absolute; }
        .pb-toggle__track { position:absolute; inset:0; border-radius:20px; background:rgba(255,255,255,.1); cursor:pointer; transition:background .15s; }
        .pb-toggle input:checked + .pb-toggle__track { background:#cc0000; }
        .pb-toggle__track::after { content:''; position:absolute; left:3px; top:3px; width:14px; height:14px; border-radius:50%; background:#fff; transition:transform .15s; }
        .pb-toggle input:checked + .pb-toggle__track::after { transform:translateX(16px); }
        .pb-toggle-row { display:flex; align-items:center; gap:8px; }
        .pb-toggle-label { font-size:12px; color:rgba(255,255,255,.55); }
        /* Repeater kártyák */
        .pb-rep-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; padding:10px; display:flex; flex-direction:column; gap:6px; }
        .pb-rep-card__head { display:flex; align-items:center; justify-content:space-between; margin-bottom:2px; }
        .pb-rep-card__title { font-size:11px; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.05em; }
        .pb-rep-card__del { background:transparent; border:none; color:rgba(255,60,60,.5); cursor:pointer; font-size:15px; line-height:1; padding:0; }
        .pb-rep-card__del:hover { color:#ff6060; }
        .pb-add-card-btn { background:rgba(255,255,255,.05); border:1px dashed rgba(255,255,255,.15); color:rgba(255,255,255,.4); border-radius:8px; padding:8px; font-size:12px; font-weight:600; cursor:pointer; text-align:center; transition:all .15s; }
        .pb-add-card-btn:hover { background:rgba(255,255,255,.08); color:rgba(255,255,255,.7); }
        /* Toast */
        .va-pb-toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#111118; border:1px solid rgba(255,255,255,.12); border-radius:10px; padding:12px 22px; font-size:14px; font-weight:600; color:#fff; z-index:99999; transition:opacity .3s; display:none; }
        .va-pb-toast.va-pb-toast--success { border-color:rgba(74,222,128,.3); color:#4ade80; }
        .va-pb-toast.va-pb-toast--error { border-color:rgba(255,60,60,.3); color:#ff6060; }
        </style>

        <div class="wrap va-admin-wrap" style="padding:0;margin:0;max-width:none;">
        <div class="va-pbe" id="va-pbe">

            <!-- ─── Bal: Paletta ─── -->
            <div class="va-pbe__palette">
                <div class="va-pbe__palette-head">Blokkok</div>
                <div class="va-pbe__palette-list" id="va-palette">
                    <!-- JS tölti -->
                </div>
            </div>

            <!-- ─── Közép: Vászon ─── -->
            <div class="va-pbe__canvas-wrap">
                <div class="va-pbe__topbar">
                    <button class="va-pbe__btn va-pbe__btn--back" onclick="window.location.href='<?php echo esc_js( $list_url ); ?>'">
                        ← Oldalak
                    </button>
                    <input type="text" id="va-page-title" value="<?php echo esc_attr( $post->post_title ); ?>" placeholder="Oldal neve">
                    <select id="va-page-status" class="va-pbe__status-select">
                        <option value="publish"<?php selected( $post->post_status, 'publish' ); ?>>Közzétett</option>
                        <option value="draft"<?php selected( $post->post_status, 'draft' ); ?>>Piszkozat</option>
                    </select>
                    <div class="va-pbe__topbar-right">
                        <a href="<?php echo esc_url( $view_url ); ?>" target="_blank" class="va-pbe__btn va-pbe__btn--secondary">↗ Előnézet</a>
                        <button class="va-pbe__btn va-pbe__btn--primary" onclick="saveAll()" id="va-save-btn">💾 Mentés</button>
                    </div>
                </div>
                <div class="va-pbe__canvas" id="va-canvas">
                    <div class="va-pbe__empty" id="va-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
                        <p>Húzz ide blokkokat a bal oldali listából</p>
                    </div>
                </div>
            </div>

            <!-- ─── Jobb: Beállítások ─── -->
            <div class="va-pbe__settings">
                <div class="va-pbe__settings-head" id="va-settings-head">Beállítások</div>
                <div class="va-pbe__settings-body" id="va-settings-body">
                    <div class="va-pbe__no-selection">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span>Válassz ki egy blokkot a szerkesztéshez</span>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="va-pb-toast" id="va-toast"></div>

        <!-- SortableJS CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

        <script>
        (function() {
        'use strict';

        /* ── Konfig ───────────────────────────────────────────── */
        var AJAX_URL = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
        var NONCE    = '<?php echo esc_js( $nonce ); ?>';
        var POST_ID  = <?php echo (int) $post_id; ?>;

        /* ── Állapot ─────────────────────────────────────────── */
        var state = {
            blocks: [],
            selected: null,
        };

        /* ── Blokk definíciók ────────────────────────────────── */
        var BLOCKS = {
            hero: {
                label: 'Hero szekció', color: '#cc0000',
                defaults: { eyebrow:'', title:'Főcím', subtitle:'Alcím szöveg...', btn1_text:'Gomb', btn1_url:'#', btn1_style:'primary', btn2_text:'', btn2_url:'#', btn2_style:'outline', bg_type:'gradient', bg_color:'#0a0a0a', bg_gradient_start:'#0a0a0a', bg_gradient_end:'#1a0505', bg_gradient_angle:135, bg_image_url:'', bg_overlay:50, text_color:'#ffffff', accent_color:'#cc0000', min_height:500, text_align:'center', padding_y:80 },
                sections: [
                    { title:'Tartalom', fields:[
                        { key:'eyebrow',  label:'Eyebrow (kis felirat)', type:'text' },
                        { key:'title',    label:'Főcím', type:'text' },
                        { key:'subtitle', label:'Alcím', type:'textarea' },
                    ]},
                    { title:'Gombok', fields:[
                        { key:'btn1_text',  label:'1. gomb felirat', type:'text' },
                        { key:'btn1_url',   label:'1. gomb URL',     type:'text' },
                        { key:'btn1_style', label:'1. gomb stílus',  type:'select', opts:['primary','outline','ghost'], labels:['Elsődleges','Körvonal','Átlátszó'] },
                        { key:'btn2_text',  label:'2. gomb felirat', type:'text' },
                        { key:'btn2_url',   label:'2. gomb URL',     type:'text' },
                        { key:'btn2_style', label:'2. gomb stílus',  type:'select', opts:['primary','outline','ghost'], labels:['Elsődleges','Körvonal','Átlátszó'] },
                    ]},
                    { title:'Háttér', fields:[
                        { key:'bg_type',           label:'Háttér típus',        type:'select', opts:['gradient','color','image'], labels:['Gradiens','Szín','Kép'] },
                        { key:'bg_color',           label:'Háttérszín',          type:'color' },
                        { key:'bg_gradient_start',  label:'Gradiens kezdő',      type:'color' },
                        { key:'bg_gradient_end',    label:'Gradiens vég',        type:'color' },
                        { key:'bg_gradient_angle',  label:'Gradiens szög (°)',   type:'number', min:0, max:360 },
                        { key:'bg_image_url',       label:'Háttérkép URL',       type:'media' },
                        { key:'bg_overlay',         label:'Sötétítő (%)',        type:'number', min:0, max:100 },
                    ]},
                    { title:'Szín & Layout', fields:[
                        { key:'text_color',   label:'Szöveg színe',    type:'color' },
                        { key:'accent_color', label:'Accent szín',     type:'color' },
                        { key:'text_align',   label:'Igazítás',        type:'select', opts:['left','center','right'], labels:['Balra','Középre','Jobbra'] },
                        { key:'min_height',   label:'Min. magasság px',type:'number', min:100, max:1200 },
                        { key:'padding_y',    label:'Padding (px)',    type:'number', min:0, max:300 },
                    ]},
                ],
            },

            text: {
                label: 'Szöveg blokk', color: '#3b82f6',
                defaults: { content:'<p>Szöveg...</p>', text_color:'#e8e8f0', bg_color:'#060606', text_align:'left', font_size:16, max_width:800, padding_y:60 },
                sections: [
                    { title:'Tartalom', fields:[
                        { key:'content', label:'Szöveg (HTML)', type:'textarea' },
                    ]},
                    { title:'Stílus', fields:[
                        { key:'text_color', label:'Szöveg színe',   type:'color' },
                        { key:'bg_color',   label:'Háttérszín',     type:'color' },
                        { key:'text_align', label:'Igazítás',       type:'select', opts:['left','center','right'], labels:['Balra','Középre','Jobbra'] },
                        { key:'font_size',  label:'Betűméret (px)', type:'number', min:12, max:48 },
                        { key:'max_width',  label:'Max. szélesség', type:'number', min:400, max:1400 },
                        { key:'padding_y',  label:'Padding (px)',   type:'number', min:0, max:300 },
                    ]},
                ],
            },

            img_text: {
                label: 'Kép + szöveg', color: '#10b981',
                defaults: { image_url:'', image_side:'left', eyebrow:'', title:'Cím', content:'Szöveg...', btn_text:'', btn_url:'#', bg_color:'#060606', text_color:'#ffffff', accent_color:'#cc0000', padding_y:80, image_border_radius:12 },
                sections: [
                    { title:'Tartalom', fields:[
                        { key:'eyebrow',  label:'Eyebrow',      type:'text' },
                        { key:'title',    label:'Cím',          type:'text' },
                        { key:'content',  label:'Szöveg',       type:'textarea' },
                        { key:'btn_text', label:'Gomb felirat', type:'text' },
                        { key:'btn_url',  label:'Gomb URL',     type:'text' },
                    ]},
                    { title:'Kép', fields:[
                        { key:'image_url',           label:'Kép URL',            type:'media' },
                        { key:'image_side',          label:'Kép oldala',         type:'select', opts:['left','right'], labels:['Bal','Jobb'] },
                        { key:'image_border_radius', label:'Kép lekerekítés px', type:'number', min:0, max:50 },
                    ]},
                    { title:'Szín & Layout', fields:[
                        { key:'bg_color',     label:'Háttérszín',  type:'color' },
                        { key:'text_color',   label:'Szöveg szín', type:'color' },
                        { key:'accent_color', label:'Accent szín', type:'color' },
                        { key:'padding_y',    label:'Padding px',  type:'number', min:0, max:300 },
                    ]},
                ],
            },

            cta: {
                label: 'CTA sáv', color: '#f59e0b',
                defaults: { eyebrow:'', title:'Felhívás', subtitle:'', btn1_text:'Fő gomb', btn1_url:'#', btn2_text:'', btn2_url:'#', bg_type:'gradient', bg_color:'#0a0a0a', bg_gradient_start:'#1a0505', bg_gradient_end:'#0a0a0a', text_color:'#ffffff', accent_color:'#cc0000', padding_y:80, layout:'centered' },
                sections: [
                    { title:'Tartalom', fields:[
                        { key:'eyebrow',  label:'Eyebrow',  type:'text' },
                        { key:'title',    label:'Cím',      type:'text' },
                        { key:'subtitle', label:'Alcím',    type:'text' },
                    ]},
                    { title:'Gombok', fields:[
                        { key:'btn1_text', label:'1. gomb felirat', type:'text' },
                        { key:'btn1_url',  label:'1. gomb URL',     type:'text' },
                        { key:'btn2_text', label:'2. gomb felirat', type:'text' },
                        { key:'btn2_url',  label:'2. gomb URL',     type:'text' },
                    ]},
                    { title:'Háttér & Szín', fields:[
                        { key:'bg_type',          label:'Háttér típus',   type:'select', opts:['gradient','color'], labels:['Gradiens','Szín'] },
                        { key:'bg_color',          label:'Háttérszín',     type:'color' },
                        { key:'bg_gradient_start', label:'Gradiens kezdő', type:'color' },
                        { key:'bg_gradient_end',   label:'Gradiens vég',   type:'color' },
                        { key:'text_color',         label:'Szöveg szín',   type:'color' },
                        { key:'accent_color',       label:'Accent szín',   type:'color' },
                        { key:'layout',             label:'Elrendezés',    type:'select', opts:['centered','split'], labels:['Középre','Két oszlop'] },
                        { key:'padding_y',          label:'Padding px',    type:'number', min:0, max:300 },
                    ]},
                ],
            },

            cards: {
                label: 'Kártya rács', color: '#8b5cf6',
                defaults: { section_title:'', section_subtitle:'', columns:3, bg_color:'#060606', card_bg:'rgba(255,255,255,0.04)', card_border:'rgba(255,255,255,0.08)', icon_color:'#cc0000', title_color:'#ffffff', text_color:'rgba(255,255,255,0.6)', padding_y:80, cards:[{icon:'🎯',title:'Kártya 1',text:'Leírás szövege itt.',link_url:'',link_text:''}] },
                sections: [
                    { title:'Fejléc', fields:[
                        { key:'section_title',    label:'Szekció cím',  type:'text' },
                        { key:'section_subtitle', label:'Szekció alcím',type:'text' },
                    ]},
                    { title:'Kártyák', fields:[
                        { key:'cards', label:'', type:'repeater', fields:[
                            { key:'icon',      label:'Ikon / Emoji', type:'text' },
                            { key:'title',     label:'Cím',          type:'text' },
                            { key:'text',      label:'Szöveg',       type:'textarea' },
                            { key:'link_url',  label:'Link URL',     type:'text' },
                            { key:'link_text', label:'Link felirat', type:'text' },
                        ]},
                    ]},
                    { title:'Stílus', fields:[
                        { key:'columns',      label:'Oszlopok',      type:'select', opts:['2','3','4'], labels:['2 oszlop','3 oszlop','4 oszlop'] },
                        { key:'bg_color',     label:'Háttérszín',    type:'color' },
                        { key:'card_bg',      label:'Kártya háttér', type:'color' },
                        { key:'card_border',  label:'Kártya keret',  type:'color' },
                        { key:'icon_color',   label:'Ikon szín',     type:'color' },
                        { key:'title_color',  label:'Cím szín',      type:'color' },
                        { key:'text_color',   label:'Szöveg szín',   type:'color' },
                        { key:'padding_y',    label:'Padding px',    type:'number', min:0, max:300 },
                    ]},
                ],
            },

            divider: {
                label: 'Elválasztó', color: '#6b7280',
                defaults: { type:'spacer', height:60, bg_color:'#060606', line_color:'rgba(255,255,255,0.08)', line_style:'solid', dot_count:5 },
                sections: [
                    { title:'Beállítások', fields:[
                        { key:'type',       label:'Típus',           type:'select', opts:['spacer','line','dots'], labels:['Üres hely','Vonal','Pontok'] },
                        { key:'height',     label:'Magasság (px)',   type:'number', min:8, max:400 },
                        { key:'bg_color',   label:'Háttérszín',      type:'color' },
                        { key:'line_color', label:'Vonal/pont szín', type:'color' },
                        { key:'line_style', label:'Vonal stílus',    type:'select', opts:['solid','dashed','dotted'], labels:['Egybefüggő','Szaggatott','Pontozott'] },
                        { key:'dot_count',  label:'Pontok száma',    type:'number', min:2, max:20 },
                    ]},
                ],
            },
        };

        /* ── ID generátor ────────────────────────────────────── */
        function uid() { return 'b_' + Date.now() + '_' + Math.random().toString(36).slice(2,7); }

        /* ── Adat init ───────────────────────────────────────── */
        var rawBlocks = <?php echo wp_json_encode( json_decode( $blocks, true ) ?: [] ); ?>;
        state.blocks = rawBlocks.length ? rawBlocks : [];

        /* ── Paletta render ──────────────────────────────────── */
        function renderPalette() {
            var el = document.getElementById('va-palette');
            el.innerHTML = '';
            Object.keys(BLOCKS).forEach(function(type) {
                var def = BLOCKS[type];
                var div = document.createElement('div');
                div.className = 'va-pbe__block-type';
                div.setAttribute('data-type', type);
                div.innerHTML = '<span class="va-pbe__block-type__dot" style="background:' + def.color + '"></span><span class="va-pbe__block-type__label">' + def.label + '</span>';
                div.addEventListener('click', function() { addBlock(type); });
                el.appendChild(div);
            });
        }

        /* ── Canvas render ───────────────────────────────────── */
        function renderCanvas() {
            var canvas = document.getElementById('va-canvas');
            var empty  = document.getElementById('va-empty');
            // Meglévő blokkokat töröljük (de az empty-t ne)
            Array.from(canvas.querySelectorAll('.va-pbe-block')).forEach(function(el){ el.remove(); });
            if (state.blocks.length === 0) {
                empty.style.display = 'flex';
                return;
            }
            empty.style.display = 'none';
            state.blocks.forEach(function(block) {
                var def = BLOCKS[block.type] || {};
                var el  = document.createElement('div');
                el.className = 'va-pbe-block' + (state.selected === block.id ? ' selected' : '');
                el.setAttribute('data-id', block.id);
                var preview = getPreviewText(block);
                el.innerHTML =
                    '<div class="va-pbe-block__drag" title="Húzás">⠿</div>' +
                    '<div class="va-pbe-block__accent" style="background:' + (def.color||'#666') + ';"></div>' +
                    '<div class="va-pbe-block__info">' +
                        '<div class="va-pbe-block__type">' + (def.label||block.type) + '</div>' +
                        '<div class="va-pbe-block__preview">' + escHtml(preview) + '</div>' +
                    '</div>' +
                    '<div class="va-pbe-block__actions">' +
                        '<button class="va-pbe-block__btn" title="Másolat" onclick="dupBlock(\'' + block.id + '\',event)">⊕</button>' +
                        '<button class="va-pbe-block__btn va-pbe-block__btn--del" title="Törlés" onclick="removeBlock(\'' + block.id + '\',event)">✕</button>' +
                    '</div>';
                el.addEventListener('click', function(e) {
                    if (e.target.closest('.va-pbe-block__btn')) return;
                    selectBlock(block.id);
                });
                canvas.appendChild(el);
            });
        }

        function getPreviewText(block) {
            var s = block.settings || {};
            switch(block.type) {
                case 'hero':     return (s.title || 'Hero') + (s.subtitle ? ' – ' + s.subtitle.slice(0,40) : '');
                case 'text':     return (s.content || '').replace(/<[^>]+>/g,'').slice(0,60) || 'Szöveg blokk';
                case 'img_text': return (s.title || 'Kép + Szöveg') + (s.image_side ? ' (' + s.image_side + ')' : '');
                case 'cta':      return (s.title || 'CTA') + (s.btn1_text ? ' → ' + s.btn1_text : '');
                case 'cards':    return (s.section_title || 'Kártyák') + (s.cards ? ' (' + s.cards.length + ' db)' : '');
                case 'divider':  return (s.type === 'line' ? 'Vonal' : s.type === 'dots' ? 'Pontok' : 'Üres hely') + ' – ' + (s.height||60) + 'px';
                default: return block.type;
            }
        }

        /* ── Blokk műveletek ─────────────────────────────────── */
        function addBlock(type) {
            var def = BLOCKS[type];
            if (!def) return;
            var block = { id: uid(), type: type, settings: Object.assign({}, def.defaults) };
            // Kártyák deep copy
            if (block.settings.cards) block.settings.cards = JSON.parse(JSON.stringify(block.settings.cards));
            state.blocks.push(block);
            renderCanvas();
            selectBlock(block.id);
            initSortable();
        }

        function removeBlock(id, e) {
            if (e) e.stopPropagation();
            if (!confirm('Biztosan törlöd ezt a blokkot?')) return;
            state.blocks = state.blocks.filter(function(b){ return b.id !== id; });
            if (state.selected === id) { state.selected = null; renderSettings(); }
            renderCanvas();
            initSortable();
        }

        function dupBlock(id, e) {
            if (e) e.stopPropagation();
            var idx = state.blocks.findIndex(function(b){ return b.id === id; });
            if (idx < 0) return;
            var clone = JSON.parse(JSON.stringify(state.blocks[idx]));
            clone.id = uid();
            state.blocks.splice(idx+1, 0, clone);
            renderCanvas();
            selectBlock(clone.id);
            initSortable();
        }

        function selectBlock(id) {
            state.selected = id;
            // DOM frissítés
            document.querySelectorAll('.va-pbe-block').forEach(function(el){
                el.classList.toggle('selected', el.getAttribute('data-id') === id);
            });
            renderSettings();
        }

        /* ── SortableJS ──────────────────────────────────────── */
        var sortable = null;
        function initSortable() {
            if (sortable) sortable.destroy();
            var canvas = document.getElementById('va-canvas');
            sortable = Sortable.create(canvas, {
                handle: '.va-pbe-block__drag',
                animation: 150,
                filter: '.va-pbe__empty',
                draggable: '.va-pbe-block',
                onEnd: function(evt) {
                    var moved = state.blocks.splice(evt.oldIndex, 1)[0];
                    state.blocks.splice(evt.newIndex, 0, moved);
                    renderCanvas();
                    initSortable();
                    if (state.selected) selectBlock(state.selected);
                }
            });
        }

        /* ── Settings panel ──────────────────────────────────── */
        function renderSettings() {
            var head = document.getElementById('va-settings-head');
            var body = document.getElementById('va-settings-body');

            if (!state.selected) {
                head.textContent = 'Beállítások';
                body.innerHTML = '<div class="va-pbe__no-selection"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>Válassz ki egy blokkot</span></div>';
                return;
            }

            var block = state.blocks.find(function(b){ return b.id === state.selected; });
            if (!block) return;
            var def = BLOCKS[block.type];
            head.textContent = (def ? def.label : block.type) + ' beállítások';
            body.innerHTML = buildSettingsForm(block, def);

            // VA Color Picker inicializálás
            if (window.vaInitColorPickers) { window.vaInitColorPickers($(body)); }

            // Input change events
            body.querySelectorAll('.pb-input[data-key]').forEach(function(input) {
                input.addEventListener('input', function() {
                    onFieldChange(this.id, input.type === 'checkbox' ? (this.checked ? '1':'0') : this.value, this.getAttribute('data-card-idx'));
                });
                input.addEventListener('change', function() {
                    onFieldChange(this.id, input.type === 'checkbox' ? (this.checked ? '1':'0') : this.value, this.getAttribute('data-card-idx'));
                });
            });

            // Repeater events
            body.querySelectorAll('.pb-rep-add').forEach(function(btn) {
                btn.addEventListener('click', addCard);
            });
            body.querySelectorAll('.pb-rep-del').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var idx = parseInt(this.getAttribute('data-idx'));
                    delCard(idx);
                });
            });

            // Médiaválasztó
            body.querySelectorAll('.pb-media-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var inputId = this.getAttribute('data-for');
                    openMediaPicker(inputId);
                });
            });
        }

        function buildSettingsForm(block, def) {
            if (!def) return '<p style="color:rgba(255,255,255,.3);font-size:13px;padding:12px;">Ismeretlen blokk típus.</p>';
            var html = '';
            def.sections.forEach(function(section) {
                html += '<div class="pb-section"><div class="pb-section__header" onclick="toggleSection(this)">' + section.title + '<span class="pb-section__arr">▼</span></div><div class="pb-section__body">';
                section.fields.forEach(function(field) {
                    html += buildField(field, block.settings[field.key], null);
                });
                html += '</div></div>';
            });
            return html || '<p style="color:rgba(255,255,255,.3);font-size:13px;padding:12px;">Nincsenek beállítások.</p>';
        }

        function buildField(field, value, cardIdx) {
            if (value === undefined || value === null) {
                var block = state.blocks.find(function(b){ return b.id === state.selected; });
                var def   = block ? BLOCKS[block.type] : null;
                value = def ? (def.defaults[field.key] !== undefined ? def.defaults[field.key] : '') : '';
            }
            var id  = cardIdx !== null && cardIdx !== undefined ? 'pbf_c' + cardIdx + '_' + field.key : 'pbf_' + field.key;
            var ci  = cardIdx !== null && cardIdx !== undefined ? cardIdx : '';
            var da  = ci !== '' ? ' data-card-idx="' + ci + '"' : '';
            var input = '';
            switch(field.type) {
                case 'text':
                case 'url':
                    input = '<input type="text" id="' + id + '" class="pb-input" data-key="' + field.key + '"' + da + ' value="' + escAttr(String(value)) + '">';
                    break;
                case 'textarea':
                    input = '<textarea id="' + id + '" class="pb-input pb-textarea" data-key="' + field.key + '"' + da + '>' + escHtml(String(value)) + '</textarea>';
                    break;
                case 'color': {
                    var hexVal = String(value||'#000000');
                    input = '<input type="text" id="' + id + '" class="pb-input pb-color-text va-color-input" data-key="' + field.key + '"' + da + ' value="' + escAttr(hexVal) + '" placeholder="#000000">';
                    break;
                }
                case 'number':
                    input = '<input type="number" id="' + id + '" class="pb-input pb-number" data-key="' + field.key + '"' + da + ' value="' + escAttr(String(value)) + '" min="' + (field.min||0) + '" max="' + (field.max||9999) + '">';
                    break;
                case 'select': {
                    var opts = (field.opts||[]).map(function(o,i){ return '<option value="' + escAttr(o) + '"' + (String(value)===o?' selected':'') + '>' + escHtml((field.labels&&field.labels[i])||o) + '</option>'; }).join('');
                    input = '<select id="' + id + '" class="pb-input pb-select" data-key="' + field.key + '"' + da + '>' + opts + '</select>';
                    break;
                }
                case 'media':
                    input = '<div class="pb-media-wrap"><input type="text" id="' + id + '" class="pb-input pb-media-url" data-key="' + field.key + '"' + da + ' value="' + escAttr(String(value)) + '" placeholder="URL..."><button type="button" class="pb-btn pb-btn--sm pb-media-btn" data-for="' + id + '">Böngész</button></div>';
                    break;
                case 'repeater':
                    input = buildRepeater(field, Array.isArray(value) ? value : []);
                    break;
            }
            if (field.type === 'repeater') {
                return '<div class="pb-field"><div class="pb-field__label" style="margin-bottom:8px;">' + (field.label||'') + '</div>' + input + '</div>';
            }
            return '<div class="pb-field"><label class="pb-field__label" for="' + id + '">' + field.label + '</label>' + input + '</div>';
        }

        function buildRepeater(field, cards) {
            var html = '<div class="pb-repeater">';
            cards.forEach(function(card, i) {
                html += '<div class="pb-rep-card">';
                html += '<div class="pb-rep-card__head"><span class="pb-rep-card__title">Kártya ' + (i+1) + '</span><button type="button" class="pb-rep-del" data-idx="' + i + '">✕</button></div>';
                field.fields.forEach(function(f) {
                    html += buildField(f, card[f.key], i);
                });
                html += '</div>';
            });
            html += '<button type="button" class="pb-add-card-btn pb-rep-add">+ Kártya hozzáadása</button>';
            html += '</div>';
            return html;
        }

        /* ── Field change ────────────────────────────────────── */
        function onFieldChange(inputId, value, cardIdx) {
            if (!state.selected) return;
            var block = state.blocks.find(function(b){ return b.id === state.selected; });
            if (!block) return;
            var el = document.getElementById(inputId);
            if (!el) return;
            var key = el.getAttribute('data-key');
            if (!key) return;

            if (cardIdx !== null && cardIdx !== undefined && cardIdx !== '') {
                var idx = parseInt(cardIdx);
                if (!block.settings.cards) block.settings.cards = [];
                if (!block.settings.cards[idx]) block.settings.cards[idx] = {};
                block.settings.cards[idx][key] = value;
            } else {
                block.settings[key] = el.type === 'number' ? parseInt(value)||0 : value;
            }
            // Canvas preview frissítés (csak az érintett blokk)
            var canvasBlock = document.querySelector('.va-pbe-block[data-id="' + state.selected + '"] .va-pbe-block__preview');
            if (canvasBlock) canvasBlock.textContent = getPreviewText(block);
        }

        /* ── Kártyák kezelése ────────────────────────────────── */
        function addCard() {
            if (!state.selected) return;
            var block = state.blocks.find(function(b){ return b.id === state.selected; });
            if (!block || !block.settings) return;
            if (!Array.isArray(block.settings.cards)) block.settings.cards = [];
            block.settings.cards.push({ icon:'⭐', title:'Új kártya', text:'Leírás...', link_url:'', link_text:'' });
            renderSettings();
        }

        function delCard(idx) {
            if (!state.selected) return;
            var block = state.blocks.find(function(b){ return b.id === state.selected; });
            if (!block || !Array.isArray(block.settings.cards)) return;
            block.settings.cards.splice(idx, 1);
            renderSettings();
        }

        /* ── Médiaválasztó ───────────────────────────────────── */
        function openMediaPicker(inputId) {
            if (typeof wp !== 'undefined' && wp.media) {
                var frame = wp.media({ title:'Kép kiválasztása', button:{ text:'Kiválaszt' }, multiple:false });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var input = document.getElementById(inputId);
                    if (input) {
                        input.value = attachment.url;
                        input.dispatchEvent(new Event('input'));
                    }
                });
                frame.open();
            } else {
                var url = prompt('Kép URL:');
                if (url) {
                    var input = document.getElementById(inputId);
                    if (input) { input.value = url; input.dispatchEvent(new Event('input')); }
                }
            }
        }

        /* ── Szekció összecsuk/kinyit ────────────────────────── */
        window.toggleSection = function(headerEl) {
            headerEl.closest('.pb-section').classList.toggle('collapsed');
        };

        /* ── Mentés ──────────────────────────────────────────── */
        window.saveAll = function() {
            var btn = document.getElementById('va-save-btn');
            btn.disabled = true;
            btn.textContent = 'Mentés...';

            var fd = new FormData();
            fd.append('action',      'va_pb_save');
            fd.append('nonce',       NONCE);
            fd.append('post_id',     POST_ID);
            fd.append('blocks',      JSON.stringify(state.blocks));
            fd.append('page_title',  document.getElementById('va-page-title').value);
            fd.append('page_status', document.getElementById('va-page-status').value);

            fetch(AJAX_URL, { method:'POST', body:fd })
                .then(function(r){ return r.json(); })
                .then(function(d) {
                    btn.disabled = false;
                    btn.textContent = '💾 Mentés';
                    showToast(d.success ? '✓ Mentve!' : '✕ Hiba: ' + (d.data&&d.data.msg||'ismeretlen'), d.success ? 'success' : 'error');
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.textContent = '💾 Mentés';
                    showToast('✕ Hálózati hiba', 'error');
                });
        };

        /* ── Toast ───────────────────────────────────────────── */
        function showToast(msg, type) {
            var t = document.getElementById('va-toast');
            t.textContent = msg;
            t.className = 'va-pb-toast va-pb-toast--' + (type||'success');
            t.style.display = 'block';
            setTimeout(function(){ t.style.opacity = '0'; setTimeout(function(){ t.style.display='none'; t.style.opacity='1'; }, 300); }, 2500);
        }

        /* ── HTML escape segédek ─────────────────────────────── */
        function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function escAttr(s) { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

        /* ── Init ────────────────────────────────────────────── */
        renderPalette();
        renderCanvas();
        renderSettings();
        initSortable();

        // Ctrl+S mentés
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveAll(); }
        });

        })();
        </script>
        <?php
    }
}
