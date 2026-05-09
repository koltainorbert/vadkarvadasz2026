<?php
/**
 * Hirdetés admin: egyedi lista + szerkesztő oldal (Gutenberg-mentes)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Listing_Edit {

    public static function init(): void {
        // Block editor kikapcsolása va_listing és va_auction esetén
        add_filter( 'use_block_editor_for_post_type', [ __CLASS__, 'disable_block_editor' ], 10, 2 );
        // Átirányítás WP natív szerkesztőkből → egyedi oldalunkra
        add_action( 'load-post-new.php', [ __CLASS__, 'redirect_new' ] );
        add_action( 'load-post.php',     [ __CLASS__, 'redirect_edit' ] );
        // Form mentés + quick actions
        add_action( 'admin_post_va_listing_save',    [ __CLASS__, 'handle_save'    ] );
        add_action( 'admin_post_va_listing_approve', [ __CLASS__, 'handle_approve' ] );
        add_action( 'admin_post_va_listing_delete',  [ __CLASS__, 'handle_delete'  ] );
        // Quill CDN betöltése az admin fejlécbe
        add_action( 'admin_head', [ __CLASS__, 'quill_assets' ] );
    }

    /* ── Quill CDN betöltése ── */
    public static function quill_assets(): void {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'vadaszapro-listing-edit' ) === false ) return;
        ?>
        <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
        <style>
        #va-admin-quill-editor { background:#111; border-radius:0 0 6px 6px; }
        /* ── Specs grid (like frontend) ── */
        .va-specs-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:4px; }
        @media(max-width:900px){ .va-specs-grid { grid-template-columns:1fr; } }
        .va-specs-grid .va-le-field { margin-bottom:0; }
        .va-admin-extras-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:4px; }
        @media(max-width:900px){ .va-admin-extras-grid { grid-template-columns:1fr 1fr; } }
        .va-admin-extra-check { display:flex;align-items:center;gap:6px;font-size:13px;color:#fff;cursor:pointer;padding:6px 8px;border:1px solid rgba(255,255,255,.1);border-radius:6px;transition:border-color .15s,background .15s; }
        .va-admin-extra-check:has(input:checked) { border-color:rgba(255,60,60,.5);background:rgba(255,60,60,.07); }
        .va-admin-extra-check input { accent-color:#ff3030;flex-shrink:0; }
        /* Frontend-like admin form look */
        .va-le-edit-header{margin-bottom:14px!important}
        .va-le-edit-layout{display:block!important}
        .va-le-edit-sidebar{margin-top:16px!important}
        .va-le-edit-main .va-le-card{background:transparent!important;border:0!important;padding:0!important;box-shadow:none!important;margin-bottom:16px!important}
        .va-le-edit-main .va-le-card-hdr{font-size:12px!important;font-weight:700!important;margin:20px 0 14px!important;color:rgba(255,255,255,.6)!important;text-transform:uppercase!important;letter-spacing:1px!important;padding:0 0 6px!important;border-bottom:1px solid rgba(255,255,255,.08)!important}
        .va-le-field-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:16px!important}
        @media(max-width:900px){.va-le-field-grid{grid-template-columns:1fr!important}}
        .va-le-lbl{display:block!important;margin-bottom:6px!important;font-size:13px!important;color:rgba(255,255,255,.65)!important;font-weight:500!important}
        .va-le-input,.va-le-select{width:100%!important;padding:10px 14px!important;border-radius:14px!important;background:#0e0e0e!important;border:1px solid rgba(255,255,255,.12)!important;color:#fff!important;font-size:14px!important;color-scheme:dark}
        .va-le-input:focus,.va-le-select:focus{outline:none!important;border-color:#ff0000!important;box-shadow:none!important}
        .va-le-check-lbl{display:flex!important;align-items:center!important;gap:8px!important}
        .va-le-check-lbl input[type="checkbox"]{width:16px!important;height:16px!important;accent-color:#ff0000!important}
        #va-admin-quill-editor .ql-editor { color:#e8e8e8; min-height:220px; font-size:15px; line-height:1.7; font-family:system-ui,sans-serif; }
        #va-admin-quill-editor .ql-editor.ql-blank::before { color:rgba(255,255,255,.3); font-style:normal; }
        #va-admin-quill-editor a { color:#ff4444; }
        #va-admin-quill-editor img { max-width:100%; border-radius:4px; }
        #va-admin-quill-editor blockquote { border-left:3px solid #ff4444; padding-left:12px; color:#aaa; }
        .ql-toolbar.ql-snow { background:#1e1e1e; border:1px solid rgba(255,255,255,.15)!important; border-bottom:none!important; border-radius:6px 6px 0 0; }
        .ql-container.ql-snow { border:1px solid rgba(255,255,255,.15)!important; border-radius:0 0 6px 6px; }
        .ql-snow .ql-stroke { stroke:#aaa!important; }
        .ql-snow .ql-fill,.ql-snow .ql-stroke.ql-fill { fill:#aaa!important; }
        .ql-snow .ql-picker { color:#bbb!important; }
        .ql-snow .ql-picker-label { border-color:rgba(255,255,255,.15)!important; }
        .ql-snow .ql-picker-options { background:#1e1e1e!important; border-color:rgba(255,255,255,.15)!important; }
        .ql-snow .ql-picker-item { color:#bbb!important; }
        .ql-snow .ql-picker-item:hover,.ql-snow .ql-picker-item.ql-selected { color:#fff!important; }
        .ql-snow.ql-toolbar button:hover .ql-stroke,.ql-snow .ql-toolbar button:hover .ql-stroke { stroke:#ff4444!important; }
        .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke:#ff4444!important; }
        .ql-snow.ql-toolbar button:hover .ql-fill,.ql-snow.ql-toolbar button.ql-active .ql-fill { fill:#ff4444!important; }
        .ql-snow .ql-tooltip { background:#1e1e1e!important; border-color:rgba(255,255,255,.15)!important; color:#e8e8e8!important; box-shadow:0 4px 20px rgba(0,0,0,.5)!important; }
        .ql-snow .ql-tooltip input[type=text] { background:#111!important; border-color:rgba(255,255,255,.2)!important; color:#e8e8e8!important; }
        .ql-snow .ql-tooltip a.ql-action,.ql-snow .ql-tooltip a.ql-remove { color:#ff4444!important; }
        </style>
        <?php
        // Brand/model JS
        if ( class_exists('VA_Vehicle_Catalog') ) {
            $bm = VA_Vehicle_Catalog::get_brand_models();
            echo '<script>var VA_BrandModels=' . wp_json_encode($bm) . ';</script>';
        }
        ?>
        <script>
        document.addEventListener('DOMContentLoaded',function(){
            var brandSel=document.getElementById('va-admin-brand');
            var modelSel=document.getElementById('va-admin-model');
            if(!brandSel||!modelSel) return;
            brandSel.addEventListener('change',function(){
                var brand=this.value;
                var models=(typeof VA_BrandModels!=='undefined'&&VA_BrandModels[brand])||[];
                var cur=modelSel.value;
                modelSel.innerHTML='<option value="">– Válasszon –</option>';
                models.forEach(function(m){
                    var o=document.createElement('option');
                    o.value=m; o.textContent=m;
                    if(m===cur) o.selected=true;
                    modelSel.appendChild(o);
                });
            });
        });
        </script>
        <?php
    }

    /* ── Block editor kikapcs. ───────────────────────────────── */
    public static function disable_block_editor( bool $use, string $post_type ): bool {
        return in_array( $post_type, [ 'va_listing', 'va_auction' ], true ) ? false : $use;
    }

    /* ── Átirányítások ───────────────────────────────────────── */
    public static function redirect_new(): void {
        if ( sanitize_key( $_GET['post_type'] ?? '' ) !== 'va_listing' ) return;
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listing-edit' ) );
        exit;
    }

    public static function redirect_edit(): void {
        if ( sanitize_key( $_GET['action'] ?? '' ) !== 'edit' ) return;
        $post_id = (int)( $_GET['post'] ?? 0 );
        if ( ! $post_id ) return;
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' ) return;
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listing-edit&id=' . $post_id ) );
        exit;
    }

    /* ── Mentés ──────────────────────────────────────────────── */
    public static function handle_save(): void {
        if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Nincs jogosultság.' );
        check_admin_referer( 'va_listing_save', 'va_listing_nonce' );

        $post_id = (int)( $_POST['va_post_id'] ?? 0 );
        $status  = sanitize_key( $_POST['va_post_status'] ?? 'draft' );
        if ( ! in_array( $status, [ 'publish', 'pending', 'draft' ], true ) ) $status = 'draft';

        $post_data = [
            'post_type'    => 'va_listing',
            'post_title'   => sanitize_text_field( wp_unslash( $_POST['va_title'] ?? '' ) ),
            'post_content' => wp_kses_post( wp_unslash( $_POST['va_description'] ?? '' ) ),
            'post_status'  => $status,
            'post_author'  => (int)( $_POST['va_author'] ?? get_current_user_id() ),
        ];

        if ( $status === 'publish' && function_exists( 'va_validate_listing_quality_input' ) ) {
            $raw_gids = sanitize_text_field( wp_unslash( $_POST['va_gallery_ids'] ?? '' ) );
            $gids = array_values( array_unique( array_filter( array_map( 'intval', $raw_gids !== '' ? explode( ',', $raw_gids ) : [] ) ) ) );
            $thumbnail_id = (int)( $_POST['va_thumbnail_id'] ?? 0 );
            if ( $thumbnail_id > 0 && ! in_array( $thumbnail_id, $gids, true ) ) {
                $gids[] = $thumbnail_id;
            }

            $quality = va_validate_listing_quality_input( [
                'title'        => (string) $post_data['post_title'],
                'description'  => (string) $post_data['post_content'],
                'image_count'  => count( $gids ),
                'price'        => sanitize_text_field( wp_unslash( $_POST['va_price'] ?? '0' ) ),
                'price_type'   => sanitize_key( $_POST['va_price_type'] ?? 'fixed' ),
                'year'         => intval( $_POST['va_year'] ?? 0 ),
                'mileage'      => intval( $_POST['va_mileage'] ?? 0 ),
                'brand'        => sanitize_text_field( wp_unslash( $_POST['va_brand'] ?? '' ) ),
                'model'        => sanitize_text_field( wp_unslash( $_POST['va_model'] ?? '' ) ),
                'fuel_type'    => sanitize_key( $_POST['va_fuel_type'] ?? '' ),
                'transmission' => sanitize_key( $_POST['va_transmission'] ?? '' ),
                'location'     => sanitize_text_field( wp_unslash( $_POST['va_location'] ?? '' ) ),
            ] );

            if ( empty( $quality['ok'] ) ) {
                $redirect = add_query_arg(
                    [
                        'page' => 'vadaszapro-listing-edit',
                        'id' => max( 0, $post_id ),
                        'va_error' => 'quality',
                        'va_msg' => rawurlencode( (string) ( $quality['message'] ?? 'A hirdetés nem felel meg a közzétételi minimumoknak.' ) ),
                    ],
                    admin_url( 'admin.php' )
                );
                wp_safe_redirect( $redirect );
                exit;
            }
        }

        if ( $post_id > 0 ) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post( $post_data, true );
        } else {
            $result = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $result ) ) {
            wp_safe_redirect( add_query_arg( 'va_error', '1', admin_url( 'admin.php?page=vadaszapro-listings' ) ) );
            exit;
        }

        $post_id = (int)$result;

        // Meta mezők mentése
        foreach ( VA_Meta_Fields::listing_fields() as $key => $f ) {
            if ( ! empty( $f['readonly'] ) ) continue;
            $ftype = $f['type'] ?? 'text';
            if ( $ftype === 'checkbox' ) {
                update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
            } elseif ( $ftype === 'checkboxes' ) {
                $raw_arr = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] )
                    ? array_map( 'sanitize_key', (array) $_POST[ $key ] )
                    : [];
                update_post_meta( $post_id, $key, wp_json_encode( $raw_arr ) );
            } elseif ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        // Kényszerített üzleti szabályok: email mindig látszódjon, helység ne maradjon üres.
        update_post_meta( $post_id, 'va_email_show', '1' );
        $forced_location = sanitize_text_field( wp_unslash( $_POST['va_location'] ?? '' ) );
        if ( $forced_location === '' ) {
            $forced_location = 'Veszprém Gyulafirátót';
        }
        update_post_meta( $post_id, 'va_location', $forced_location );

        // Galéria képek
        $raw_gids = sanitize_text_field( wp_unslash( $_POST['va_gallery_ids'] ?? '' ) );
        $gids = array_values( array_unique( array_filter( array_map( 'intval', $raw_gids !== '' ? explode( ',', $raw_gids ) : [] ) ) ) );
        update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $gids ) );

        // Kiemelt kép (borítókép) – a galériából
        $thumbnail_id = (int)( $_POST['va_thumbnail_id'] ?? 0 );
        if ( $thumbnail_id > 0 ) {
            set_post_thumbnail( $post_id, $thumbnail_id );
        } else {
            delete_post_thumbnail( $post_id );
        }

        // Taxonómiák
        $cat_id       = (int)( $_POST['va_category']  ?? 0 );
        $county_id    = (int)( $_POST['va_county']    ?? 0 );
        $condition_id = (int)( $_POST['va_condition'] ?? 0 );
        wp_set_object_terms( $post_id, $cat_id       ? [ $cat_id ]       : [], 'va_category'  );
        wp_set_object_terms( $post_id, $county_id    ? [ $county_id ]    : [], 'va_county'    );
        wp_set_object_terms( $post_id, $condition_id ? [ $condition_id ] : [], 'va_condition' );

        // Listing meta szinkron
        if ( function_exists( 'va_sync_listing_meta' ) ) {
            va_sync_listing_meta( $post_id );
        }

        // Egyedi (custom_*) mezők mentése a Form Builder konfigból
        if ( class_exists( 'VA_Form_Builder' ) ) {
            $admin_fields = VA_Form_Builder::get_fields( 'va_admin_listing_edit' );
            foreach ( $admin_fields as $aff ) {
                $akey = (string)( $aff['key'] ?? '' );
                if ( strncmp( $akey, 'custom_', 7 ) !== 0 ) continue;
                if ( empty( $aff['enabled'] ) ) continue;
                $atype = (string)( $aff['type'] ?? 'text' );
                if ( $atype === 'checkbox' ) {
                    update_post_meta( $post_id, $akey, isset( $_POST[ $akey ] ) ? '1' : '0' );
                } elseif ( isset( $_POST[ $akey ] ) ) {
                    update_post_meta( $post_id, $akey, sanitize_text_field( wp_unslash( $_POST[ $akey ] ) ) );
                }
            }
        }

        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listing-edit&id=' . $post_id . '&va_saved=1' ) );
        exit;
    }

    /* ── Jóváhagyás ──────────────────────────────────────────── */
    public static function handle_approve(): void {
        $id = (int)( $_GET['id'] ?? 0 );
        check_admin_referer( 'va_listing_approve_' . $id );
        if ( ! current_user_can( 'edit_post', $id ) ) wp_die( 'Nincs jogosultság.' );

        if ( function_exists( 'va_validate_listing_post_for_publish' ) ) {
            $quality = va_validate_listing_post_for_publish( $id );
            if ( empty( $quality['ok'] ) ) {
                $redirect = add_query_arg(
                    [
                        'page' => 'vadaszapro-listings',
                        'va_error' => 'quality',
                        'va_msg' => rawurlencode( (string) ( $quality['message'] ?? 'A hirdetés nem felel meg a közzétételi minimumoknak.' ) ),
                    ],
                    admin_url( 'admin.php' )
                );
                wp_safe_redirect( $redirect );
                exit;
            }
        }

        wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ] );
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listings&va_approved=1' ) );
        exit;
    }

    /* ── Törlés ──────────────────────────────────────────────── */
    public static function handle_delete(): void {
        $id = (int)( $_GET['id'] ?? 0 );
        check_admin_referer( 'va_listing_delete_' . $id );
        if ( ! current_user_can( 'delete_post', $id ) ) wp_die( 'Nincs jogosultság.' );
        wp_delete_post( $id, true ); // valódi törlés (képek is törlődnek a hookból)
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listings&va_deleted=1' ) );
        exit;
    }

    /* ══════════════════════════════════════════════════════════
       LISTA OLDAL
    ══════════════════════════════════════════════════════════ */
    public static function render_list(): void {
        if ( ! current_user_can( 'edit_posts' ) ) return;

        $per_page   = 20;
        $paged      = max( 1, (int)( $_GET['paged'] ?? 1 ) );
        $search     = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
        $status_tab = sanitize_key( $_GET['va_status'] ?? 'all' );
        $cat_filter = (int)( $_GET['va_cat'] ?? 0 );
        $orderby    = sanitize_key( $_GET['orderby'] ?? 'date' );
        $order      = strtoupper( sanitize_key( $_GET['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';

        $query_status = ( $status_tab === 'all' ) ? [ 'publish', 'pending', 'draft' ] : $status_tab;

        $args = [
            'post_type'      => 'va_listing',
            'post_status'    => $query_status,
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => $orderby,
            'order'          => $order,
        ];
        if ( $search )     $args['s']         = $search;
        if ( $cat_filter ) $args['tax_query']  = [[ 'taxonomy' => 'va_category', 'field' => 'term_id', 'terms' => $cat_filter ]];

        $query = new WP_Query( $args );
        $total = $query->found_posts;
        $pages = (int)ceil( $total / $per_page );

        $counts = wp_count_posts( 'va_listing' );
        $cats   = get_terms([ 'taxonomy' => 'va_category', 'hide_empty' => false, 'number' => 100 ]);
        if ( is_wp_error( $cats ) ) $cats = [];

        // Üzenetek
        $notices = [];
        if ( isset( $_GET['va_approved'] ) ) $notices[] = ['success','✅ Hirdetés közzétéve!'];
        if ( isset( $_GET['va_trashed']  ) ) $notices[] = ['success','🗑️ Hirdetés a kukába került.'];
        if ( isset( $_GET['va_error']    ) ) {
            $msg = isset( $_GET['va_msg'] )
                ? sanitize_text_field( wp_unslash( rawurldecode( (string) $_GET['va_msg'] ) ) )
                : 'Hiba történt a mentés során.';
            $notices[] = [ 'error', '❌ ' . $msg ];
        }

        $tabs = [
            'all'     => [ 'Összes',    (int)($counts->publish??0) + (int)($counts->pending??0) + (int)($counts->draft??0) ],
            'publish' => [ 'Aktív',     (int)($counts->publish??0) ],
            'pending' => [ 'Függő',     (int)($counts->pending??0) ],
            'draft'   => [ 'Vázlat',    (int)($counts->draft??0)   ],
            'trash'   => [ 'Kuka',      (int)($counts->trash??0)   ],
        ];

        ?>
        <div class="va-le-wrap">

            <?php foreach ($notices as [$type, $msg]): ?>
            <div class="va-le-notice va-le-notice--<?php echo esc_attr($type); ?>"><?php echo wp_kses_post($msg); ?></div>
            <?php endforeach; ?>

            <!-- Fejléc -->
            <div class="va-le-list-header">
                <h2>Hirdetések <span class="va-le-count"><?php echo (int)$total; ?></span></h2>
                <div class="va-le-header-actions">
                    <form method="get" class="va-le-search-form">
                        <input type="hidden" name="page" value="vadaszapro-listings">
                        <input type="hidden" name="va_status" value="<?php echo esc_attr($status_tab); ?>">
                        <?php if ($cat_filter): ?><input type="hidden" name="va_cat" value="<?php echo (int)$cat_filter; ?>"><?php endif; ?>
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Keresés…" class="va-le-search">
                        <button type="submit" class="va-le-search-btn">🔍</button>
                    </form>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listing-edit')); ?>" class="va-btn va-btn--primary">+ Új hirdetés</a>
                </div>
            </div>

            <!-- Tabs + kategória szűrő -->
            <div class="va-le-tabs-bar">
                <div class="va-le-tabs">
                    <?php foreach ($tabs as $st => [$label, $cnt]):
                        $url = add_query_arg(['page'=>'vadaszapro-listings','va_status'=>$st,'paged'=>1,'s'=>$search], admin_url('admin.php'));
                    ?>
                    <a href="<?php echo esc_url($url); ?>" class="va-le-tab<?php echo ($status_tab===$st)?' active':''; ?>">
                        <?php echo esc_html($label); ?><span class="va-le-tab-cnt"><?php echo $cnt; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php if ($cats): ?>
                <form method="get" class="va-le-cat-filter">
                    <input type="hidden" name="page" value="vadaszapro-listings">
                    <input type="hidden" name="va_status" value="<?php echo esc_attr($status_tab); ?>">
                    <input type="hidden" name="s" value="<?php echo esc_attr($search); ?>">
                    <select name="va_cat" class="va-le-cat-select" onchange="this.form.submit()">
                        <option value="">Minden kategória</option>
                        <?php foreach ($cats as $cat): ?>
                        <option value="<?php echo (int)$cat->term_id; ?>" <?php selected($cat_filter, $cat->term_id); ?>>
                            <?php echo esc_html($cat->name); ?> (<?php echo (int)$cat->count; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php endif; ?>
            </div>

            <!-- Táblázat -->
            <div class="va-le-table-wrap">
                <?php if ( $query->have_posts() ): ?>
                <table class="va-le-table">
                    <thead>
                    <tr>
                        <th class="va-le-col-img">Kép</th>
                        <th>Cím &amp; ID</th>
                        <th>Hirdető</th>
                        <th>Kategória</th>
                        <th>Ár</th>
                        <th>Státusz</th>
                        <th>👁</th>
                        <th>Feladva</th>
                        <th class="va-le-col-act">Műveletek</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ( $query->have_posts() ) : $query->the_post();
                        $pid    = get_the_ID();
                        $thumb  = get_the_post_thumbnail_url( $pid, [ 56, 56 ] );
                        $author = get_userdata( (int)get_post_field( 'post_author', $pid ) );
                        $pcats  = get_the_terms( $pid, 'va_category' );
                        $cat    = ( ! is_wp_error( $pcats ) && $pcats ) ? $pcats[0]->name : '—';
                        $price  = (float)get_post_meta( $pid, 'va_price', true );
                        $views  = (int)get_post_meta( $pid, 'va_views', true );
                        $st     = get_post_status();

                        $st_cfg = [
                            'publish' => [ 'Aktív',    '#4ade80', '#052e16' ],
                            'pending' => [ 'Függő',    '#fb923c', '#431407' ],
                            'draft'   => [ 'Vázlat',   '#94a3b8', '#1e293b' ],
                            'trash'   => [ 'Kukában',  '#f87171', '#1f0b0b' ],
                        ];
                        [$st_label, $st_color, $st_bg] = $st_cfg[$st] ?? ['—','#666','#111'];

                        $edit_url    = esc_url( admin_url( 'admin.php?page=vadaszapro-listing-edit&id=' . $pid ) );
                        $approve_url = esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=va_listing_approve&id=' . $pid ), 'va_listing_approve_' . $pid ) );
                        $delete_url  = esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=va_listing_delete&id='  . $pid ), 'va_listing_delete_'  . $pid ) );
                        $view_url    = esc_url( get_permalink( $pid ) );
                        $geo_nonce   = wp_create_nonce( 'va_view_geo_report_' . $pid );
                        $geo_url     = esc_url( wp_nonce_url( admin_url( 'admin.php?action=va_view_geo_report&post=' . $pid ), 'va_view_geo_report_' . $pid ) );
                    ?>
                    <tr>
                        <td class="va-le-col-img">
                            <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>" class="va-le-thumb" loading="lazy">
                            <?php else: ?>
                            <div class="va-le-thumb-empty">📷</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $edit_url; ?>" class="va-le-title-link"><?php the_title(); ?></a>
                            <div class="va-le-post-id">#<?php echo $pid; ?></div>
                        </td>
                        <td class="va-le-td-muted"><?php echo $author ? esc_html( $author->display_name ) : '—'; ?></td>
                        <td><span class="va-le-tag"><?php echo esc_html($cat); ?></span></td>
                        <td><?php echo $price > 0 ? '<strong class="va-le-price">' . number_format($price,0,',','&nbsp;') . ' Ft</strong>' : '<span class="va-le-muted">—</span>'; ?></td>
                        <td><span class="va-le-pill" style="color:<?php echo $st_color;?>;background:<?php echo $st_bg;?>"><?php echo esc_html($st_label); ?></span></td>
                        <td class="va-le-td-muted">
                            <div class="va-le-geo-wrap">
                                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                                    <a href="<?php echo $geo_url; ?>"
                                            class="va-geo-report-trigger va-le-pill va-le-geo-pill va-le-geo-pill--views"
                                            data-post-id="<?php echo esc_attr( (string) $pid ); ?>"
                                            data-geo-nonce="<?php echo esc_attr( $geo_nonce ); ?>"
                                            data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
                                            title="Megtekintési helyek - geo riport"
                                            aria-label="Megtekintési helyek megnyitása">
                                        <svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <?php echo number_format( $views, 0, ',', '&nbsp;' ); ?>
                                    </a>
                                    <a href="<?php echo $geo_url; ?>"
                                            class="va-geo-report-trigger va-le-pill va-le-geo-pill va-le-geo-pill--geo"
                                            data-post-id="<?php echo esc_attr( (string) $pid ); ?>"
                                            data-geo-nonce="<?php echo esc_attr( $geo_nonce ); ?>"
                                            data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
                                            title="Megtekintési helyek - Geotech riport"
                                            aria-label="Geotech riport megnyitása">
                                        <svg class="va-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18"></path><path d="M12 3a14 14 0 0 1 0 18"></path><path d="M12 3a14 14 0 0 0 0 18"></path></svg>
                                        Geotech
                                    </a>
                                <?php else : ?>
                                    <span class="va-le-pill va-le-geo-pill va-le-geo-pill--views">
                                        <span aria-hidden="true">👁</span>
                                        <?php echo number_format( $views, 0, ',', '&nbsp;' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="va-le-td-muted"><?php echo esc_html(get_the_date('m.d H:i')); ?></td>
                        <td>
                            <div class="va-le-actions">
                                <a href="<?php echo $edit_url; ?>" class="va-le-act" title="Szerkesztés">✏️</a>
                                <?php if ($st === 'pending'): ?>
                                <a href="<?php echo $approve_url; ?>" class="va-le-act va-le-act--ok" title="Jóváhagyás" onclick="return confirm('Közzéteszed?')">✅</a>
                                <?php endif; ?>
                                <a href="<?php echo $view_url; ?>" target="_blank" class="va-le-act" title="Megtekintés">🔗</a>
                                <a href="<?php echo $delete_url; ?>" class="va-le-act va-le-act--del" title="Törlés" onclick="return confirm('Biztosan törölni akarod ezt a hirdetést?')">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="va-le-empty">
                    <div class="va-le-empty-icon">📭</div>
                    <h3>Nincs találat</h3>
                    <p><?php echo $search ? 'A keresési feltételekre nincs találat.' : 'Még nincsenek hirdetések.'; ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listing-edit')); ?>" class="va-btn va-btn--primary">+ Új hirdetés</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Lapozó -->
            <?php if ($pages > 1): ?>
            <div class="va-le-pagination">
                <?php for ($p = 1; $p <= $pages; $p++):
                    $pu = add_query_arg(['page'=>'vadaszapro-listings','paged'=>$p,'va_status'=>$status_tab,'s'=>$search,'va_cat'=>$cat_filter], admin_url('admin.php'));
                ?>
                <a href="<?php echo esc_url($pu); ?>" class="va-le-page-btn<?php echo $p===$paged?' active':''; ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

        </div><?php
    }

    /* ══════════════════════════════════════════════════════════
       SZERKESZTŐ / ÚJ OLDAL
    ══════════════════════════════════════════════════════════ */
    public static function render_edit(): void {
        if ( ! current_user_can( 'edit_posts' ) ) return;
        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );

        $post_id = (int)( $_GET['id'] ?? 0 );
        $post    = $post_id ? get_post( $post_id ) : null;
        $is_new  = ! $post;

        if ( $post && $post->post_type !== 'va_listing' ) {
            echo '<div class="notice notice-error"><p>Érvénytelen hirdetés azonosítója.</p></div>';
            return;
        }

        // Adminban új hirdetésnél a frontend feladási formot mutatjuk.
        if ( $is_new ) {
            ?>
            <div class="va-le-wrap">
                <div class="va-le-edit-header">
                    <div class="va-le-edit-back">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listings')); ?>" class="va-le-back-btn">← Hirdetések</a>
                        <h2>Új hirdetés</h2>
                    </div>
                </div>

                <div class="va-le-card">
                    <div class="va-wrap">
                        <?php echo do_shortcode('[va_submit_listing]'); ?>
                    </div>
                </div>
            </div>
            <?php
            return;
        }

        $get_meta = static fn($k) => $post ? (string)get_post_meta( $post_id, $k, true ) : '';

        // Galéria + borítókép
        $thumb_id    = $post ? (int)get_post_thumbnail_id( $post_id ) : 0;
        $gallery_raw = $post ? (string)get_post_meta( $post_id, 'va_gallery_ids', true ) : '';
        $gallery_ids = array_values( array_unique( array_filter( array_map( 'intval', $gallery_raw !== '' ? explode( ',', $gallery_raw ) : [] ) ) ) );
        if ( $thumb_id && ! in_array( $thumb_id, $gallery_ids, true ) ) {
            array_unshift( $gallery_ids, $thumb_id );
        }
        if ( ! $thumb_id && $gallery_ids ) {
            $thumb_id = $gallery_ids[0];
        }

        // Taxonómiák
        $categories   = get_terms([ 'taxonomy' => 'va_category', 'hide_empty' => false, 'number' => 200 ]);
        $counties     = get_terms([ 'taxonomy' => 'va_county',   'hide_empty' => false, 'number' => 100 ]);
        $conditions   = get_terms([ 'taxonomy' => 'va_condition','hide_empty' => false, 'number' => 100 ]);
        if ( is_wp_error($categories) ) $categories = [];
        if ( is_wp_error($counties) )   $counties   = [];
        if ( is_wp_error($conditions) ) $conditions = [];

        $cur_cats      = $post ? wp_get_object_terms( $post_id, 'va_category', ['fields'=>'ids'] ) : [];
        $cur_county    = $post ? wp_get_object_terms( $post_id, 'va_county',   ['fields'=>'ids'] ) : [];
        $cur_condition = $post ? wp_get_object_terms( $post_id, 'va_condition',['fields'=>'ids'] ) : [];
        $cur_cat       = ( ! is_wp_error($cur_cats)      && $cur_cats      ) ? (int)$cur_cats[0]      : 0;
        $cur_cty       = ( ! is_wp_error($cur_county)    && $cur_county    ) ? (int)$cur_county[0]    : 0;
        $cur_cond      = ( ! is_wp_error($cur_condition) && $cur_condition ) ? (int)$cur_condition[0] : 0;

        // Felhasználók (max 200)
        $users       = get_users([ 'number' => 200, 'orderby' => 'display_name', 'order' => 'ASC' ]);
        $post_author = $post ? (int)$post->post_author : get_current_user_id();
        $post_status = $post ? $post->post_status : 'draft';

        $price_types = [ 'fixed' => 'Fix ár', 'negotiable' => 'Alkudható', 'free' => 'Ingyenes', 'on_request' => 'Érdeklődjön' ];

        // Form Builder config – admin listing edit
        $fb_raw     = class_exists( 'VA_Form_Builder' ) ? VA_Form_Builder::get_fields( 'va_admin_listing_edit' ) : [];
        usort( $fb_raw, static fn( $a, $b ) => (int)( $a['order'] ?? 99 ) - (int)( $b['order'] ?? 99 ) );
        $fb = [];
        foreach ( $fb_raw as $ff ) { $fb[ $ff['key'] ] = $ff; }
        $fb_on  = static fn( $k )          => ! isset( $fb[ $k ] ) || ! empty( $fb[ $k ]['enabled'] );
        $fb_lbl = static fn( $k, $default ) => ( isset( $fb[ $k ]['label'] ) && $fb[ $k ]['label'] !== '' ) ? esc_html( $fb[ $k ]['label'] ) : $default;
        $fb_ph  = static fn( $k, $default ) => ( isset( $fb[ $k ]['placeholder'] ) && $fb[ $k ]['placeholder'] !== '' ) ? esc_attr( $fb[ $k ]['placeholder'] ) : $default;

        // Egyedi (custom_*) mezők gyűjtése
        $custom_fields = array_filter( $fb_raw, static fn( $ff ) => strncmp( (string)( $ff['key'] ?? '' ), 'custom_', 7 ) === 0 && ! empty( $ff['enabled'] ) );
        $site_type        = sanitize_key( (string) get_option( 'va_site_type', 'vadaszat' ) );
        $brands_list      = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_brands() : [];
        $brand_models_list= class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_brand_models() : [];
        $body_types_list  = class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_body_type_options() : [];
        $cur_brand_val    = $get_meta('va_brand');
        $cur_model_val    = $get_meta('va_model');
        $models_for_brand = $brand_models_list[$cur_brand_val] ?? [];
        $cur_location_val = $get_meta('va_location');
        if ( $cur_location_val === '' ) {
            $cur_location_val = 'Veszprém Gyulafirátót';
        }
        ?>
        <div class="va-le-wrap">

            <?php if (isset($_GET['va_saved'])): ?>
            <div class="va-le-notice va-le-notice--success">✅ Hirdetés sikeresen mentve!</div>
            <?php elseif (isset($_GET['va_error'])): ?>
            <div class="va-le-notice va-le-notice--error">❌ <?php echo esc_html( isset($_GET['va_msg']) ? sanitize_text_field( wp_unslash( rawurldecode( (string) $_GET['va_msg'] ) ) ) : 'Hiba történt a mentés során, próbáld újra.' ); ?></div>
            <?php endif; ?>

            <!-- Szerkesztő fejléc -->
            <div class="va-le-edit-header">
                <div class="va-le-edit-back">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listings')); ?>" class="va-le-back-btn">← Hirdetések</a>
                    <h2><?php echo $is_new ? 'Új hirdetés' : 'Hirdetés szerkesztése <small>#' . $post_id . '</small>'; ?></h2>
                </div>
                <div class="va-le-edit-header-actions">
                    <?php if ($post): ?>
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" class="va-btn va-btn--ghost">🔗 Megtekintés</a>
                    <?php endif; ?>
                    <button form="va-listing-form" type="submit" class="va-btn va-btn--primary">💾 Mentés</button>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="va-listing-form" class="va-le-edit-form">
                <input type="hidden" name="action" value="va_listing_save">
                <input type="hidden" name="va_post_id" value="<?php echo (int)$post_id; ?>">
                <?php wp_nonce_field('va_listing_save', 'va_listing_nonce'); ?>
                <input type="hidden" name="va_post_status" id="va_post_status" value="<?php echo esc_attr($post_status); ?>">

                <div class="va-le-edit-layout">

                    <!-- ━━ Fő tartalom ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
                    <div class="va-le-edit-main">

                        <!-- Cím -->
                        <div class="va-le-card">
                            <input type="text" name="va_title" value="<?php echo esc_attr($post ? $post->post_title : ''); ?>"
                                   placeholder="Hirdetés címe…" class="va-le-title-input" required>
                        </div>

                        <!-- Alap adatok (frontend sorrend) -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">Alap adatok</div>
                            <div class="va-le-field-grid">
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Járműkategória</label>
                                    <select name="va_category" class="va-le-select">
                                        <option value="">— Válasszon —</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo (int)$cat->term_id; ?>" <?php selected($cur_cat, $cat->term_id); ?>><?php echo esc_html($cat->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Állapot</label>
                                    <select name="va_condition" class="va-le-select">
                                        <option value="">— Válasszon —</option>
                                        <?php foreach ($conditions as $cond): ?>
                                        <option value="<?php echo (int)$cond->term_id; ?>" <?php selected($cur_cond, $cond->term_id); ?>><?php echo esc_html($cond->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_location', 'Helyszín (város)'); ?></label>
                                    <input type="text" name="va_location" value="<?php echo esc_attr($cur_location_val); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_location','pl. Veszprém Gyulafirátót'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Leírás -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📝 Leírás</div>
                            <div id="va-admin-quill-editor"></div>
                            <textarea name="va_description" id="va-admin-desc-hidden" style="display:none"><?php echo esc_textarea( $post ? $post->post_content : '' ); ?></textarea>
                        </div>

                        <!-- Képek -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr" style="display:flex;align-items:center;justify-content:space-between;">
                                <span>
                                    <svg style="vertical-align:middle;margin-right:5px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    Képek
                                </span>
                                <span id="va-gal-count" style="font-size:11px;color:var(--va-muted);font-weight:400;"><?php echo $gallery_ids ? count($gallery_ids).' kép' : ''; ?></span>
                            </div>
                            <div class="va-gal-grid" id="va-gal-grid">
                                <?php foreach ( $gallery_ids as $gid ):
                                    $gurl = wp_get_attachment_image_url( $gid, 'thumbnail' );
                                    if ( ! $gurl ) continue;
                                    $is_cover = ( $gid === $thumb_id );
                                ?>
                                <div class="va-gal-item<?php echo $is_cover ? ' va-gal-item--cover' : ''; ?>" data-id="<?php echo (int)$gid; ?>">
                                    <img src="<?php echo esc_url($gurl); ?>" alt="">
                                    <div class="va-gal-over">
                                        <button type="button" class="va-gal-star" title="Borítókép">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </button>
                                        <button type="button" class="va-gal-del" title="Törlés">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                    <?php if ( $is_cover ): ?><div class="va-gal-badge">Borítókép</div><?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <button type="button" class="va-gal-add" id="va-gal-add">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Képek hozzáadása</span>
                                </button>
                            </div>
                            <input type="hidden" name="va_thumbnail_id" id="va_thumbnail_id" value="<?php echo (int)$thumb_id; ?>">
                            <input type="hidden" name="va_gallery_ids"  id="va_gallery_ids"  value="<?php echo esc_attr( implode(',', $gallery_ids) ); ?>">
                            <p style="font-size:11px;color:rgba(255,255,255,.3);margin:4px 0 0;">Húzd a képeket az átrendezéshez &bull; ★ = borítókép beállítása</p>
                        </div>

                        <!-- Árazás -->
                        <?php if ( $fb_on('va_price') || $fb_on('va_price_type') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">💰 Árazás</div>
                            <div class="va-le-field-grid">
                                <?php if ( $fb_on('va_price') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_price', 'Ár (Ft)'); ?></label>
                                    <input type="number" name="va_price" min="0" value="<?php echo esc_attr($get_meta('va_price')); ?>"
                                           placeholder="<?php echo $fb_ph('va_price','0'); ?>" class="va-le-input">
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_price_type') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_price_type', 'Árazás típusa'); ?></label>
                                    <select name="va_price_type" class="va-le-select">
                                        <?php foreach ($price_types as $v => $l): ?>
                                        <option value="<?php echo esc_attr($v); ?>" <?php selected($get_meta('va_price_type'), $v); ?>><?php echo esc_html($l); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Részletek -->
                        <?php if ( $fb_on('va_brand') || $fb_on('va_model') || $fb_on('va_caliber') || $fb_on('va_year') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🔍 Termék részletei</div>
                            <div class="va-le-field-grid">
                                <?php if ( $fb_on('va_brand') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_brand', 'Márka / Gyártó'); ?></label>
                                    <?php if ( $site_type === 'jarmu' ): ?>
                                    <select name="va_brand" id="va-admin-brand" class="va-le-select">
                                        <option value="">– Válasszon –</option>
                                        <?php foreach ( $brands_list as $b ): ?>
                                        <option value="<?php echo esc_attr($b); ?>"<?php selected($cur_brand_val,$b); ?>><?php echo esc_html($b); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php else: ?>
                                    <input type="text" name="va_brand" value="<?php echo esc_attr($cur_brand_val); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_brand','pl. Browning'); ?>">
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_model') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_model', 'Modell / Típus'); ?></label>
                                    <?php if ( $site_type === 'jarmu' ): ?>
                                    <select name="va_model" id="va-admin-model" class="va-le-select">
                                        <option value="">– Válasszon –</option>
                                        <?php if ( $cur_model_val && ! in_array($cur_model_val, $models_for_brand, true) ): ?>
                                        <option value="<?php echo esc_attr($cur_model_val); ?>" selected><?php echo esc_html($cur_model_val); ?></option>
                                        <?php endif; ?>
                                        <?php foreach ( $models_for_brand as $m ): ?>
                                        <option value="<?php echo esc_attr($m); ?>"<?php selected($cur_model_val,$m); ?>><?php echo esc_html($m); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php else: ?>
                                    <input type="text" name="va_model" value="<?php echo esc_attr($cur_model_val); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_model','pl. X-Bolt'); ?>">
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_caliber') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_caliber', 'Kaliber'); ?></label>
                                    <?php if ( $site_type !== 'jarmu' ): ?>
                                    <input type="text" name="va_caliber" value="<?php echo esc_attr($get_meta('va_caliber')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_caliber','pl. .308 Win'); ?>">
                                    <?php else: ?>
                                    <label class="va-le-lbl">Felépítmény</label>
                                    <select name="va_body_type" class="va-le-select">
                                        <option value="">– Válasszon –</option>
                                        <?php foreach ( $body_types_list as $bk => $bl ): ?>
                                        <option value="<?php echo esc_attr($bk); ?>"<?php selected($get_meta('va_body_type'),$bk); ?>><?php echo esc_html($bl); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_year') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_year', 'Gyártási év'); ?></label>
                                    <input type="number" name="va_year" min="1800" max="2099" value="<?php echo esc_attr($get_meta('va_year')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_year','pl. 2020'); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Kapcsolat -->
                        <?php if ( $fb_on('va_phone') || $fb_on('va_email_show') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📞 Kapcsolat</div>
                            <div class="va-le-field-grid">
                                <?php if ( $fb_on('va_phone') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_phone', 'Telefonszám'); ?></label>
                                    <input type="tel" name="va_phone" value="<?php echo esc_attr($get_meta('va_phone')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_phone','+36 20 …'); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if ( $fb_on('va_email_show') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_email_show" value="1" checked onclick="return false;">
                                <span><?php echo $fb_lbl('va_email_show', 'Email cím megjelenítése a hirdetésen'); ?></span>
                            </label>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Egyedi (custom_*) mezők -->
                        <?php if ( $custom_fields ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">➕ Egyéb mezők</div>
                            <div class="va-le-field-grid">
                            <?php foreach ( $custom_fields as $cff ):
                                $ck = esc_attr( (string)( $cff['key'] ?? '' ) );
                                $ct = (string)( $cff['type'] ?? 'text' );
                                $cl = esc_html( (string)( $cff['label'] ?? $ck ) );
                                $cp = esc_attr( (string)( $cff['placeholder'] ?? '' ) );
                            ?>
                            <div class="va-le-field">
                                <label class="va-le-lbl"><?php echo $cl; ?></label>
                                <?php if ( $ct === 'checkbox' ): ?>
                                <label class="va-le-check-lbl">
                                    <input type="checkbox" name="<?php echo $ck; ?>" value="1" <?php checked( (string)get_post_meta($post_id, $ck, true), '1' ); ?>>
                                    <span><?php echo $cl; ?></span>
                                </label>
                                <?php elseif ( $ct === 'textarea' ): ?>
                                <textarea name="<?php echo $ck; ?>" class="va-le-input" rows="3" placeholder="<?php echo $cp; ?>"><?php echo esc_textarea( (string)get_post_meta($post_id, $ck, true) ); ?></textarea>
                                <?php else: ?>
                                <input type="<?php echo esc_attr($ct); ?>" name="<?php echo $ck; ?>" value="<?php echo esc_attr( (string)get_post_meta($post_id, $ck, true) ); ?>" class="va-le-input" placeholder="<?php echo $cp; ?>">
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- .va-le-edit-main -->
                        <?php if ( $site_type === 'jarmu' && class_exists('VA_Vehicle_Catalog') ):
                            $drive_opts    = VA_Vehicle_Catalog::get_drive_options();
                            $vcond_opts    = VA_Vehicle_Catalog::get_vehicle_condition_options();
                            $doc_type_opts = VA_Vehicle_Catalog::get_doc_type_options();
                            $doc_val_opts  = VA_Vehicle_Catalog::get_doc_validity_options();
                            $ac_opts       = VA_Vehicle_Catalog::get_ac_type_options();
                            $eco_opts      = VA_Vehicle_Catalog::get_eco_class_options();
                            $cyl_opts      = VA_Vehicle_Catalog::get_cylinder_layout_options();
                            $roof_opts     = VA_Vehicle_Catalog::get_roof_type_options();
                            $extras_by_grp = VA_Vehicle_Catalog::get_extras_by_group();
                            $extras_raw_v  = $get_meta('va_extras');
                            $extras_val    = json_decode($extras_raw_v, true);
                            if (!is_array($extras_val)) $extras_val = [];
                            $rs = function(string $n, array $opts, string $sv): void {
                                echo '<select name="'.esc_attr($n).'" class="va-le-select"><option value="">– Válasszon –</option>';
                                foreach ($opts as $k=>$l) echo '<option value="'.esc_attr($k).'"'.selected($sv,$k,false).'>'.esc_html($l).'</option>';
                                echo '</select>';
                            };
                        ?>

                        <!-- ⚙️ Motor / Hajtástechnika -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">⚙️ Motor / Hajtástechnika</div>
                            <div class="va-specs-grid">
                                <div class="va-le-field"><label class="va-le-lbl">Futásteljesítmény (km)</label><input type="number" name="va_mileage" value="<?php echo esc_attr($get_meta('va_mileage')); ?>" class="va-le-input" min="0" placeholder="pl. 125000"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Üzemanyag</label><?php $rs('va_fuel_type',['benzin'=>'Benzin','diesel'=>'Dízel','hybrid'=>'Hibrid','electric'=>'Elektromos','lpg'=>'LPG','cng'=>'CNG','egyeb'=>'Egyéb'],$get_meta('va_fuel_type')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Hengerűrtartalom (cm³)</label><input type="number" name="va_engine_size" value="<?php echo esc_attr($get_meta('va_engine_size')); ?>" class="va-le-input" min="0" placeholder="pl. 1598"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Teljesítmény (kW)</label><input type="number" name="va_performance_kw" value="<?php echo esc_attr($get_meta('va_performance_kw')); ?>" class="va-le-input" min="0" placeholder="pl. 85"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Sebességváltó</label><?php $rs('va_transmission',['manual'=>'Kéziváltó','automatic'=>'Automata','semi_auto'=>'Félautomata','cvt'=>'CVT','egyeb'=>'Egyéb'],$get_meta('va_transmission')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Hajtás</label><?php $rs('va_drive',$drive_opts,$get_meta('va_drive')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Henger-elrendezés</label><?php $rs('va_cylinder_layout',$cyl_opts,$get_meta('va_cylinder_layout')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Saját tömeg (kg)</label><input type="number" name="va_own_weight" value="<?php echo esc_attr($get_meta('va_own_weight')); ?>" class="va-le-input" min="0" placeholder="pl. 1450"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Össztömeg (kg)</label><input type="number" name="va_gross_weight" value="<?php echo esc_attr($get_meta('va_gross_weight')); ?>" class="va-le-input" min="0" placeholder="pl. 1900"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Utasok száma</label><input type="number" name="va_passengers" value="<?php echo esc_attr($get_meta('va_passengers')); ?>" class="va-le-input" min="1" max="100" placeholder="pl. 5"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Csomagtartó (liter)</label><input type="number" name="va_trunk_liters" value="<?php echo esc_attr($get_meta('va_trunk_liters')); ?>" class="va-le-input" min="0" placeholder="pl. 350"></div>
                                <div class="va-le-field" style="padding-top:22px;"><label class="va-le-check-lbl"><input type="checkbox" name="va_range_gearbox" value="1"<?php checked($get_meta('va_range_gearbox'),'1'); ?>> <span>Felező váltó</span></label></div>
                            </div>
                        </div>

                        <!-- 🚘 Karosszéria / Állapot -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🚘 Karosszéria / Állapot</div>
                            <div class="va-specs-grid">
                                <div class="va-le-field"><label class="va-le-lbl">Jármű állapota</label><?php $rs('va_vehicle_condition',$vcond_opts,$get_meta('va_vehicle_condition')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Ajtók száma</label><?php $rs('va_doors',['2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6+'],$get_meta('va_doors')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Szín</label><input type="text" name="va_color" value="<?php echo esc_attr($get_meta('va_color')); ?>" class="va-le-input" placeholder="pl. Fehér, Fekete..."></div>
                                <div class="va-le-field" style="padding-top:22px;"><label class="va-le-check-lbl"><input type="checkbox" name="va_color_metallic" value="1"<?php checked($get_meta('va_color_metallic'),'1'); ?>> <span>Metál fényezés</span></label></div>
                                <div class="va-le-field"><label class="va-le-lbl">Tető típusa</label><?php $rs('va_roof_type',$roof_opts,$get_meta('va_roof_type')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Klíma</label><?php $rs('va_ac_type',$ac_opts,$get_meta('va_ac_type')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Környezetvédelmi osztály</label><?php $rs('va_eco_class',$eco_opts,$get_meta('va_eco_class')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Tulajdonosok száma</label><input type="number" name="va_owners" value="<?php echo esc_attr($get_meta('va_owners')); ?>" class="va-le-input" min="1" max="20" placeholder="pl. 2"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Kulcsok száma</label><select name="va_keys" class="va-le-select"><option value="">– Válasszon –</option><?php for($ki=1;$ki<=10;$ki++): ?><option value="<?php echo $ki; ?>"<?php selected($get_meta('va_keys'),(string)$ki); ?>><?php echo $ki; ?> db</option><?php endfor; ?></select></div>
                                <div class="va-le-field"><label class="va-le-lbl">Kárpit (1)</label><input type="text" name="va_upholstery_1" value="<?php echo esc_attr($get_meta('va_upholstery_1')); ?>" class="va-le-input" placeholder="pl. Fekete"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Kárpit (2)</label><input type="text" name="va_upholstery_2" value="<?php echo esc_attr($get_meta('va_upholstery_2')); ?>" class="va-le-input" placeholder="pl. Szürke"></div>
                            </div>
                        </div>

                        <!-- 📄 Okmányok / Műszaki -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📄 Okmányok / Műszaki</div>
                            <div class="va-specs-grid">
                                <div class="va-le-field"><label class="va-le-lbl">Okmányok jellege</label><?php $rs('va_doc_type',$doc_type_opts,$get_meta('va_doc_type')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Okmányok érvényessége</label><?php $rs('va_doc_validity',$doc_val_opts,$get_meta('va_doc_validity')); ?></div>
                                <div class="va-le-field"><label class="va-le-lbl">Műszaki vizsga lejár</label><input type="month" name="va_tech_inspect" value="<?php echo esc_attr($get_meta('va_tech_inspect')); ?>" class="va-le-input" style="color-scheme:dark;background:#0e0e0e;color:#fff;"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Első forgalomba helyezés (év.hó)</label><input type="text" name="va_first_reg" value="<?php echo esc_attr($get_meta('va_first_reg')); ?>" class="va-le-input" placeholder="pl. 2019-03"></div>
                                <div class="va-le-field" style="padding-top:22px;"><label class="va-le-check-lbl"><input type="checkbox" name="va_previous_damage" value="1"<?php checked($get_meta('va_previous_damage'),'1'); ?>> <span>Korábbi kár / baleset</span></label></div>
                                <div class="va-le-field" style="padding-top:22px;"><label class="va-le-check-lbl"><input type="checkbox" name="va_service_book" value="1"<?php checked($get_meta('va_service_book'),'1'); ?>> <span>Szervizkönyv megvan</span></label></div>
                            </div>
                        </div>

                        <!-- 🔧 Gumi méretek / Egyéb -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🔧 Gumi méretek / Egyéb</div>
                            <div class="va-specs-grid">
                                <div class="va-le-field"><label class="va-le-lbl">Nyári gumi (első, pl. 205/55R16)</label><input type="text" name="va_summer_tire_front" value="<?php echo esc_attr($get_meta('va_summer_tire_front')); ?>" class="va-le-input" placeholder="205/55R16"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Nyári gumi (hátsó)</label><input type="text" name="va_summer_tire_rear" value="<?php echo esc_attr($get_meta('va_summer_tire_rear')); ?>" class="va-le-input" placeholder="205/55R16"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Téli gumi (első)</label><input type="text" name="va_winter_tire_front" value="<?php echo esc_attr($get_meta('va_winter_tire_front')); ?>" class="va-le-input" placeholder="205/55R16"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Téli gumi (hátsó)</label><input type="text" name="va_winter_tire_rear" value="<?php echo esc_attr($get_meta('va_winter_tire_rear')); ?>" class="va-le-input" placeholder="205/55R16"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Alvázszám (VIN)</label><input type="text" name="va_vin" value="<?php echo esc_attr($get_meta('va_vin')); ?>" class="va-le-input" placeholder="17 karakteres VIN" maxlength="17"></div>
                                <div class="va-le-field"><label class="va-le-lbl">Belső azonosító</label><input type="text" name="va_internal_id" value="<?php echo esc_attr($get_meta('va_internal_id')); ?>" class="va-le-input" placeholder="Saját belső azonosító"></div>
                                <div class="va-le-field"><label class="va-le-lbl">2. telefonszám</label><input type="text" name="va_second_phone" value="<?php echo esc_attr($get_meta('va_second_phone')); ?>" class="va-le-input" placeholder="+36 30 ..."></div>
                            </div>
                        </div>

                        <!-- ✅ Extra felszereltség -->
                        <?php foreach ($extras_by_grp as $grp_key => $grp):
                            $grp_items = $grp['items'] ?? [];
                        ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">✅ <?php echo esc_html($grp['label']); ?></div>
                            <div class="va-admin-extras-grid">
                                <?php foreach ($grp_items as $ek => $el): $is_chk = in_array($ek, $extras_val, true); ?>
                                <label class="va-admin-extra-check">
                                    <input type="checkbox" name="va_extras[]" value="<?php echo esc_attr($ek); ?>"<?php echo $is_chk ? ' checked' : ''; ?>>
                                    <?php echo esc_html($el); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php else:
                            // Nem jarmu: generikus típus mezők
                            $type_extra_all = class_exists('VA_Meta_Fields') ? VA_Meta_Fields::get_type_extra_fields() : [];
                            $skip_in_generic = ['va_brand','va_model','va_caliber','va_year','va_license_req','va_body_type'];
                            $type_extra_show  = [];
                            foreach ( $type_extra_all as $k => $f ) {
                                if ( ! in_array( $k, $skip_in_generic, true ) ) {
                                    $type_extra_show[ $k ] = $f;
                                }
                            }
                            $type_extra_checkboxes = array_filter($type_extra_show, fn($f) => ($f['type']??'')==='checkboxes');
                            $type_extra_regular    = array_filter($type_extra_show, fn($f) => ($f['type']??'')!=='checkboxes');
                            if ($type_extra_regular):
                        ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🔧 Típusfüggő adatok</div>
                            <div class="va-le-field-grid">
                            <?php foreach ($type_extra_regular as $fk => $ff):
                                $ftype = $ff['type']??'text'; $flabel = esc_html($ff['label']??$fk); $fval = $get_meta($fk); $fname = esc_attr($fk);
                                $fmin = isset($ff['min'])?' min="'.esc_attr((string)$ff['min']).'"':''; $fmax = isset($ff['max'])?' max="'.esc_attr((string)$ff['max']).'"':'';
                            ?>
                            <div class="va-le-field">
                                <?php if ($ftype!=='checkbox'): ?><label class="va-le-lbl"><?php echo $flabel; ?></label><?php endif; ?>
                                <?php if ($ftype==='select'): ?>
                                    <select name="<?php echo $fname; ?>" class="va-le-select"><option value="">— Nincs megadva —</option><?php foreach(($ff['options']??[]) as $ov=>$ol): ?><option value="<?php echo esc_attr((string)$ov); ?>"<?php selected($fval,(string)$ov); ?>><?php echo esc_html((string)$ol); ?></option><?php endforeach; ?></select>
                                <?php elseif ($ftype==='checkbox'): ?>
                                    <label class="va-le-check-lbl"><input type="checkbox" name="<?php echo $fname; ?>" value="1"<?php checked($fval,'1'); ?>><span><?php echo $flabel; ?></span></label>
                                <?php elseif ($ftype==='date'): ?>
                                    <input type="date" name="<?php echo $fname; ?>" value="<?php echo esc_attr($fval); ?>" class="va-le-input">
                                <?php elseif ($ftype==='number'): ?>
                                    <input type="number" name="<?php echo $fname; ?>" value="<?php echo esc_attr($fval); ?>" class="va-le-input"<?php echo $fmin.$fmax; ?>>
                                <?php else: ?>
                                    <input type="text" name="<?php echo $fname; ?>" value="<?php echo esc_attr($fval); ?>" class="va-le-input">
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; endif; ?>

                    </div><!-- .va-le-edit-main -->

                    <!-- ━━ Oldalsáv ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
                    <div class="va-le-edit-sidebar">

                        <!-- Közzététel -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🚀 Közzététel</div>
                            <div class="va-le-field">
                                <label class="va-le-lbl">Státusz</label>
                                <select class="va-le-select" onchange="document.getElementById('va_post_status').value=this.value">
                                    <option value="publish" <?php selected($post_status,'publish'); ?>>✅ Aktív (közzétett)</option>
                                    <option value="pending" <?php selected($post_status,'pending'); ?>>⏳ Jóváhagyásra vár</option>
                                    <option value="draft"   <?php selected($post_status,'draft');   ?>>📝 Vázlat</option>
                                </select>
                            </div>
                            <div class="va-le-field">
                                <label class="va-le-lbl">Hirdető felhasználó</label>
                                <select name="va_author" class="va-le-select">
                                    <?php foreach ($users as $u): ?>
                                    <option value="<?php echo (int)$u->ID; ?>" <?php selected($post_author, $u->ID); ?>><?php echo esc_html($u->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="va-le-publish-row">
                                <button type="button" class="va-btn va-btn--ghost va-btn--sm" onclick="document.getElementById('va_post_status').value='draft'; vaAdminDoSubmit(document.getElementById('va-listing-form'));">Mentés vázlatként</button>
                                <button type="submit" class="va-btn va-btn--primary va-btn--sm">💾 Mentés</button>
                            </div>
                        </div>

                        <!-- Kategória -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🗂️ Kategória</div>
                            <select name="va_category" class="va-le-select">
                                <option value="">— Nincs megadva —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int)$cat->term_id; ?>" <?php selected($cur_cat, $cat->term_id); ?>><?php echo esc_html($cat->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Megye -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📍 Megye</div>
                            <select name="va_county" class="va-le-select">
                                <option value="">— Nincs megadva —</option>
                                <?php foreach ($counties as $county): ?>
                                <option value="<?php echo (int)$county->term_id; ?>" <?php selected($cur_cty, $county->term_id); ?>><?php echo esc_html($county->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Állapot -->
                        <?php if ( $conditions ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🔘 Állapot</div>
                            <select name="va_condition" class="va-le-select">
                                <option value="">— Nincs megadva —</option>
                                <?php foreach ($conditions as $cond): ?>
                                <option value="<?php echo (int)$cond->term_id; ?>" <?php selected($cur_cond, $cond->term_id); ?>><?php echo esc_html($cond->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Jelölések és beállítások -->
                        <?php if ( $fb_on('va_featured') || $fb_on('va_verified') || $fb_on('va_license_req') || $fb_on('va_expires') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🏷️ Jelölések</div>
                            <?php if ( $fb_on('va_featured') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_featured" value="1" <?php checked($get_meta('va_featured'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_featured', '⭐ Kiemelt hirdetés'); ?></span>
                            </label>
                            <?php endif; ?>
                            <?php if ( $fb_on('va_verified') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_verified" value="1" <?php checked($get_meta('va_verified'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_verified', '✅ Ellenőrzött hirdető'); ?></span>
                            </label>
                            <?php endif; ?>
                            <?php if ( $fb_on('va_license_req') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_license_req" value="1" <?php checked($get_meta('va_license_req'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_license_req', '🔒 Fegyverengedély szükséges'); ?></span>
                            </label>
                            <?php endif; ?>
                            <?php if ( $fb_on('va_expires') ): ?>
                            <div class="va-le-field" style="margin-top:12px">
                                <label class="va-le-lbl"><?php echo $fb_lbl('va_expires', '⏰ Lejárat dátuma'); ?></label>
                                <input type="date" name="va_expires" value="<?php echo esc_attr($get_meta('va_expires')); ?>" class="va-le-input">
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Statisztika (meglévő hirdetésnél) -->
                        <?php if (!$is_new): ?>
                        <div class="va-le-card va-le-card--info">
                            <div class="va-le-card-hdr">📊 Statisztika</div>
                            <div class="va-le-stat-row"><span>Megtekintések</span><strong><?php echo number_format((int)$get_meta('va_views'),0,',','&nbsp;'); ?></strong></div>
                            <div class="va-le-stat-row"><span>Feladás dátuma</span><strong><?php echo esc_html(get_the_date('Y.m.d H:i', $post_id)); ?></strong></div>
                            <div class="va-le-stat-row"><span>Utolsó módosítás</span><strong><?php echo esc_html(get_the_modified_date('Y.m.d H:i', $post_id)); ?></strong></div>
                        </div>
                        <?php endif; ?>

                    </div><!-- .va-le-edit-sidebar -->
                </div><!-- .va-le-edit-layout -->
            </form>
        </div><!-- .va-le-wrap -->

        <script>
        /* ══ Quill admin init ══════════════════════════════ */
        (function(){
            var quillAdmin = window.quillAdmin = new Quill('#va-admin-quill-editor', {
                theme: 'snow',
                placeholder: 'Hirdetés leírása...',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote'],
                            [{ align: [] }],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: function() {
                                if (quillAdmin.root.querySelectorAll('img').length >= 2) {
                                    alert('Maximum 2 kép engedélyezett a leírásban.');
                                    return;
                                }
                                var input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/jpeg,image/png,image/webp,image/gif');
                                input.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
                                document.body.appendChild(input);
                                input.addEventListener('change', function() {
                                    var file = input.files[0];
                                    document.body.removeChild(input);
                                    if (!file) return;
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        var range = quillAdmin.getSelection(true);
                                        quillAdmin.insertEmbed(range ? range.index : quillAdmin.getLength(), 'image', e.target.result);
                                        quillAdmin.setSelection((range ? range.index : 0) + 1);
                                    };
                                    reader.readAsDataURL(file);
                                });
                                input.click();
                            }
                        }
                    }
                }
            });
            var existing = document.getElementById('va-admin-desc-hidden');
            if (existing && existing.value.trim()) {
                // root.innerHTML közvetlen írás: megőrzi az img style="width:…" attribútumot
                // (dangerouslyPasteHTML sanitizálja és elveszíti a méretet)
                quillAdmin.root.innerHTML = existing.value;
            }

            /* Kép resize */
            var activeImg = null, rHandle = document.createElement('div'), startX, startW;
            rHandle.style.cssText = 'position:absolute;width:12px;height:12px;background:#ff4444;border:2px solid #fff;border-radius:3px;cursor:se-resize;display:none;z-index:9999;box-shadow:0 0 4px rgba(0,0,0,.6);';
            document.body.appendChild(rHandle);
            function posH() { if (!activeImg) return; var r = activeImg.getBoundingClientRect(); rHandle.style.left=(r.right+window.scrollX-8)+'px'; rHandle.style.top=(r.bottom+window.scrollY-8)+'px'; }
            quillAdmin.root.addEventListener('click', function(e) {
                if (e.target.tagName==='IMG') { activeImg=e.target; if(!activeImg.style.width) activeImg.style.width=activeImg.offsetWidth+'px'; posH(); rHandle.style.display='block'; }
                else { rHandle.style.display='none'; activeImg=null; }
            });
            rHandle.addEventListener('mousedown', function(e) { e.preventDefault(); startX=e.clientX; startW=activeImg?activeImg.offsetWidth:100; document.addEventListener('mousemove',onM); document.addEventListener('mouseup',onU); });
            function onM(e) { if (!activeImg) return; var w=Math.max(40,startW+(e.clientX-startX)); activeImg.style.width=w+'px'; activeImg.style.height='auto'; posH(); }
            function onU() { document.removeEventListener('mousemove',onM); document.removeEventListener('mouseup',onU); }
            window.addEventListener('scroll',posH); window.addEventListener('resize',posH);
            document.addEventListener('click',function(e){ if(e.target!==activeImg&&e.target!==rHandle){rHandle.style.display='none';activeImg=null;} });

            /* Submit előtt: base64 képek feltöltése, majd form küldés */
            var _nonce  = '<?php echo esc_js( wp_create_nonce( 'va_upload_editor_image' ) ); ?>';
            var _ajax   = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
            var _postId = '<?php echo esc_js( (string) ( (int) ( $_GET['id'] ?? 0 ) ) ); ?>';

            window.vaAdminDoSubmit = function(form) {
                var textarea  = document.getElementById('va-admin-desc-hidden');
                var baseHtml  = quillAdmin.root.innerHTML;
                var imgNodes  = quillAdmin.root.querySelectorAll('img[src^="data:"]');

                if (!imgNodes.length) {
                    textarea.value = baseHtml;
                    form.submit();
                    return;
                }

                // A DOM-t NEM módosítjuk – string-cserével dolgozunk,
                // hogy a Quill MutationObserver ne írja vissza a base64-et.
                var promises = Array.from(imgNodes).map(function(img) {
                    var b64 = img.getAttribute('src');
                    return fetch(_ajax, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ action: 'va_upload_editor_image', nonce: _nonce, post_id: _postId, data_url: b64 })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success && res.data && res.data.url) {
                            // Csere a HTML string-ben (nem a DOM-ban)
                            baseHtml = baseHtml.split(b64).join(res.data.url);
                        }
                    })
                    .catch(function() {});
                });

                Promise.all(promises).then(function() {
                    textarea.value = baseHtml;
                    form.submit();
                }).catch(function() {
                    textarea.value = baseHtml;
                    form.submit();
                });
            };

            document.getElementById('va-listing-form').addEventListener('submit', function(e) {
                e.preventDefault();
                vaAdminDoSubmit(this);
            });
        })();

        jQuery(function($) {
            var mf,
                $grid      = $('#va-gal-grid'),
                $thumbIn   = $('#va_thumbnail_id'),
                $galIn     = $('#va_gallery_ids'),
                $count     = $('#va-gal-count');

            /* ── Segédek ─────────────── */
            function ids() {
                return $.map($grid.find('.va-gal-item'), function(el){ return parseInt($(el).data('id'), 10); });
            }
            function sync() {
                var list = ids();
                $galIn.val( list.join(',') );
                $count.text( list.length ? list.length + ' kép' : '' );
                // Ha nincs cover, az első lesz az
                if ( ! $grid.find('.va-gal-item--cover').length && $grid.find('.va-gal-item').length ) {
                    setCover( $grid.find('.va-gal-item').first() );
                }
            }
            function setCover($item) {
                $grid.find('.va-gal-item').removeClass('va-gal-item--cover');
                $grid.find('.va-gal-badge').remove();
                $item.addClass('va-gal-item--cover');
                $item.append('<div class="va-gal-badge">Borítókép</div>');
                $thumbIn.val( $item.data('id') );
            }
            function bindItem($item) {
                $item.find('.va-gal-star').on('click', function(e){
                    e.stopPropagation();
                    setCover($item);
                });
                $item.find('.va-gal-del').on('click', function(e){
                    e.stopPropagation();
                    var wasCover = $item.hasClass('va-gal-item--cover');
                    $item.remove();
                    if (wasCover) {
                        var $first = $grid.find('.va-gal-item').first();
                        if ($first.length) setCover($first);
                        else $thumbIn.val('0');
                    }
                    sync();
                });
            }

            /* ── Init: meglévő elemek ── */
            $grid.find('.va-gal-item').each(function(){ bindItem($(this)); });

            /* ── Sortable ────────────── */
            $grid.sortable({
                items: '.va-gal-item',
                tolerance: 'pointer',
                cursor: 'grabbing',
                placeholder: 'va-gal-ph',
                forcePlaceholderSize: true,
                stop: function(){ sync(); }
            });

            /* ── Médiatár ────────────── */
            $('#va-gal-add').on('click', function(){
                if (!window.wp || !wp.media) return;
                if (mf) { mf.open(); return; }
                mf = wp.media({
                    title: 'Képek kiválasztása',
                    multiple: 'add',
                    library: { type: 'image' },
                    button: { text: 'Hozzáadás a galériához' }
                });
                mf.on('select', function(){
                    var existing = ids();
                    mf.state().get('selection').each(function(att){
                        var d = att.toJSON();
                        if ( existing.indexOf(d.id) !== -1 ) return;
                        var url = (d.sizes && d.sizes.thumbnail) ? d.sizes.thumbnail.url : d.url;
                        var $el = $(
                            '<div class="va-gal-item" data-id="' + d.id + '">' +
                            '<img src="' + url + '" alt="">' +
                            '<div class="va-gal-over">' +
                                '<button type="button" class="va-gal-star" title="Borítókép"><svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></button>' +
                                '<button type="button" class="va-gal-del" title="Törlés"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>' +
                            '</div>' +
                            '</div>'
                        );
                        $grid.find('#va-gal-add').before($el);
                        bindItem($el);
                    });
                    sync();
                });
                mf.open();
            });
        });
        </script>
        <?php
    }
}
