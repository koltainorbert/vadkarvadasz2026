<?php
/**
 * Admin listaoszlopok és szűrők a hirdetések/aukciók listájához
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Listing_Columns {

    public static function init() {
        // Hirdetés oszlopok
        add_filter( 'manage_va_listing_posts_columns',       [ __CLASS__, 'listing_columns' ] );
        add_action( 'manage_va_listing_posts_custom_column', [ __CLASS__, 'listing_column_content' ], 10, 2 );
        add_filter( 'manage_edit-va_listing_sortable_columns', [ __CLASS__, 'sortable_columns' ] );

        // Aukció oszlopok
        add_filter( 'manage_va_auction_posts_columns',       [ __CLASS__, 'auction_columns' ] );
        add_action( 'manage_va_auction_posts_custom_column', [ __CLASS__, 'auction_column_content' ], 10, 2 );

        // Gyors szerkesztés és státusz
        add_action( 'restrict_manage_posts', [ __CLASS__, 'add_filters' ] );
        add_filter( 'parse_query',           [ __CLASS__, 'parse_filter_query' ] );

        // Admin duplikálás hirdetésekhez
        add_filter( 'post_row_actions', [ __CLASS__, 'add_duplicate_row_action' ], 10, 2 );
        add_action( 'admin_action_va_duplicate_listing', [ __CLASS__, 'handle_duplicate_action' ] );
        add_action( 'admin_action_va_view_geo_report', [ __CLASS__, 'handle_view_geo_report_action' ] );
        add_action( 'admin_notices', [ __CLASS__, 'duplicate_admin_notice' ] );
    }

    public static function add_duplicate_row_action( array $actions, $post ): array {
        if ( ! $post instanceof WP_Post || $post->post_type !== 'va_listing' ) {
            return $actions;
        }
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url( 'admin.php?action=va_duplicate_listing&post=' . $post->ID ),
            'va_duplicate_listing_' . $post->ID
        );

        $actions['va_duplicate_listing'] = '<a href="' . esc_url( $url ) . '">Duplikálás</a>';
        return $actions;
    }

    public static function handle_duplicate_action(): void {
        $source_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        if ( $source_id <= 0 ) {
            wp_die( 'Érvénytelen hirdetés azonosító.' );
        }

        if ( ! current_user_can( 'edit_post', $source_id ) ) {
            wp_die( 'Nincs jogosultságod a duplikáláshoz.' );
        }

        check_admin_referer( 'va_duplicate_listing_' . $source_id );

        $source = get_post( $source_id );
        if ( ! $source || $source->post_type !== 'va_listing' ) {
            wp_die( 'A kiválasztott bejegyzés nem duplikálható.' );
        }

        $new_post_id = wp_insert_post( [
            'post_type'      => 'va_listing',
            'post_status'    => 'draft',
            'post_title'     => $source->post_title . ' (Másolat)',
            'post_content'   => $source->post_content,
            'post_excerpt'   => $source->post_excerpt,
            'post_author'    => get_current_user_id(),
            'menu_order'     => (int) $source->menu_order,
            'comment_status' => $source->comment_status,
            'ping_status'    => $source->ping_status,
        ], true );

        if ( is_wp_error( $new_post_id ) ) {
            wp_die( 'A duplikálás sikertelen: ' . esc_html( $new_post_id->get_error_message() ) );
        }

        // Taxonómiák másolása
        $taxonomies = get_object_taxonomies( 'va_listing' );
        foreach ( $taxonomies as $taxonomy ) {
            $term_ids = wp_get_object_terms( $source_id, $taxonomy, [ 'fields' => 'ids' ] );
            if ( ! is_wp_error( $term_ids ) ) {
                wp_set_object_terms( $new_post_id, $term_ids, $taxonomy, false );
            }
        }

        // Meta mezők másolása (szerkesztő-lock kivétel)
        $meta = get_post_meta( $source_id );
        foreach ( $meta as $meta_key => $values ) {
            if ( in_array( $meta_key, [ '_edit_lock', '_edit_last' ], true ) ) {
                continue;
            }
            foreach ( $values as $value ) {
                add_post_meta( $new_post_id, $meta_key, maybe_unserialize( $value ) );
            }
        }

        wp_safe_redirect( admin_url( 'post.php?post=' . $new_post_id . '&action=edit&va_duplicated=1' ) );
        exit;
    }

    public static function duplicate_admin_notice(): void {
        if ( ! is_admin() || empty( $_GET['va_duplicated'] ) ) {
            return;
        }
        echo '<div class="notice notice-success is-dismissible"><p>Hirdetés sikeresen duplikálva. A másolat piszkozatként jött létre.</p></div>';
    }

    public static function listing_columns( $cols ) {
        return [
            'cb'           => $cols['cb'],
            'title'        => 'Cím',
            'va_thumb'     => 'Kép',
            'va_price'     => 'Ár',
            'va_category'  => 'Kategória',
            'va_county'    => 'Megye',
            'va_views'     => 'Megtekintés',
            'va_featured'  => 'Kiemelt',
            'va_verified'  => 'Ell.',
            'va_expires'   => 'Lejárat',
            'author'       => 'Feladó',
            'date'         => 'Dátum',
        ];
    }

    public static function listing_column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'va_thumb':
                echo get_the_post_thumbnail( $post_id, [ 60, 60 ] );
                break;
            case 'va_price':
                $type = get_post_meta( $post_id, 'va_price_type', true );
                echo esc_html( va_format_price( get_post_meta( $post_id, 'va_price', true ), $type ) );
                break;
            case 'va_category':
                $terms = get_the_terms( $post_id, 'va_category' );
                echo $terms ? esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) : '–';
                break;
            case 'va_county':
                $terms = get_the_terms( $post_id, 'va_county' );
                echo $terms ? esc_html( $terms[0]->name ) : '–';
                break;
            case 'va_views':
                $views = (int) ( get_post_meta( $post_id, 'va_views', true ) ?: 0 );
                if ( current_user_can( 'manage_options' ) ) {
                    $nonce = wp_create_nonce( 'va_view_geo_report_' . $post_id );
                    echo '<button type="button" class="button-link va-geo-report-trigger"'
                        . ' data-post-id="' . esc_attr( (string) $post_id ) . '"'
                        . ' data-geo-nonce="' . esc_attr( $nonce ) . '"'
                        . ' title="Megtekintési helyek">'
                        . '<span class="va-geo-report-eye">👁</span> '
                        . esc_html( number_format_i18n( $views ) )
                        . '</button>';
                } else {
                    echo '<span><span class="va-geo-report-eye">👁</span> ' . esc_html( number_format_i18n( $views ) ) . '</span>';
                }
                break;
            case 'va_featured':
                echo get_post_meta( $post_id, 'va_featured', true ) === '1' ? '⭐' : '–';
                break;
            case 'va_verified':
                echo get_post_meta( $post_id, 'va_verified', true ) === '1' ? '✅' : '–';
                break;
            case 'va_expires':
                $exp = get_post_meta( $post_id, 'va_expires', true );
                if ( $exp ) {
                    $class = strtotime( $exp ) < time() ? 'color:red' : '';
                    echo '<span style="' . esc_attr( $class ) . '">' . esc_html( $exp ) . '</span>';
                } else {
                    echo '–';
                }
                break;
        }
    }

    public static function auction_columns( $cols ) {
        return [
            'cb'               => $cols['cb'],
            'title'            => 'Cím',
            'va_thumb'         => 'Kép',
            'va_start_price'   => 'Kikiáltási ár',
            'va_current_bid'   => 'Aktuális licit',
            'va_bid_count'     => 'Licitek',
            'va_auction_end'   => 'Aukció vége',
            'va_auction_winner'=> 'Nyertes',
            'author'           => 'Feladó',
            'date'             => 'Dátum',
        ];
    }

    public static function auction_column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'va_thumb':
                echo get_the_post_thumbnail( $post_id, [ 60, 60 ] );
                break;
            case 'va_start_price':
                echo esc_html( number_format( (float) get_post_meta( $post_id, 'va_start_price', true ), 0, ',', ' ' ) . ' Ft' );
                break;
            case 'va_current_bid':
                $bid = get_post_meta( $post_id, 'va_current_bid', true );
                echo $bid ? esc_html( number_format( (float) $bid, 0, ',', ' ' ) . ' Ft' ) : '–';
                break;
            case 'va_bid_count':
                echo esc_html( get_post_meta( $post_id, 'va_bid_count', true ) ?: 0 );
                break;
            case 'va_auction_end':
                $end = get_post_meta( $post_id, 'va_auction_end', true );
                if ( $end ) {
                    $over  = strtotime( $end ) < time();
                    $class = $over ? 'color:red' : 'color:green';
                    echo '<span style="' . esc_attr( $class ) . '">' . esc_html( $end ) . '</span>';
                } else {
                    echo '–';
                }
                break;
            case 'va_auction_winner':
                $winner_id = get_post_meta( $post_id, 'va_auction_winner', true );
                if ( $winner_id ) {
                    $user = get_userdata( (int) $winner_id );
                    echo $user ? esc_html( $user->display_name ) : 'ID: ' . esc_html( $winner_id );
                } else {
                    echo '–';
                }
                break;
        }
    }

    public static function sortable_columns( $cols ) {
        $cols['va_price'] = 'va_price';
        $cols['va_views'] = 'va_views';
        return $cols;
    }

    /* ── Szűrő dropdownok a listában ─────────────────── */
    public static function add_filters( $post_type ) {
        if ( ! in_array( $post_type, [ 'va_listing', 'va_auction' ], true ) ) return;

        $categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false ] );
        echo '<select name="va_filter_category">';
        echo '<option value="">– Kategória –</option>';
        foreach ( $categories as $t ) {
            $sel = isset( $_GET['va_filter_category'] ) && $_GET['va_filter_category'] == $t->term_id ? ' selected' : '';
            echo '<option value="' . esc_attr( $t->term_id ) . '"' . $sel . '>' . esc_html( $t->name ) . '</option>';
        }
        echo '</select>';

        $counties = get_terms( [ 'taxonomy' => 'va_county', 'hide_empty' => false ] );
        echo '<select name="va_filter_county">';
        echo '<option value="">– Megye –</option>';
        foreach ( $counties as $t ) {
            $sel = isset( $_GET['va_filter_county'] ) && $_GET['va_filter_county'] == $t->term_id ? ' selected' : '';
            echo '<option value="' . esc_attr( $t->term_id ) . '"' . $sel . '>' . esc_html( $t->name ) . '</option>';
        }
        echo '</select>';
    }

    public static function parse_filter_query( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) return;

        $tax_query = [];
        if ( ! empty( $_GET['va_filter_category'] ) ) {
            $tax_query[] = [ 'taxonomy' => 'va_category', 'field' => 'term_id', 'terms' => intval( $_GET['va_filter_category'] ) ];
        }
        if ( ! empty( $_GET['va_filter_county'] ) ) {
            $tax_query[] = [ 'taxonomy' => 'va_county', 'field' => 'term_id', 'terms' => intval( $_GET['va_filter_county'] ) ];
        }
        if ( $tax_query ) {
            $query->set( 'tax_query', array_merge( [ 'relation' => 'AND' ], $tax_query ) );
        }
    }

    public static function handle_view_geo_report_action(): void {
        $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        if ( $post_id <= 0 ) {
            wp_die( 'Érvénytelen hirdetés azonosító.' );
        }

        if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( 'Nincs jogosultságod ehhez a jelentéshez.' );
        }

        check_admin_referer( 'va_view_geo_report_' . $post_id );

        $rows      = function_exists( 'va_get_view_geo_breakdown' ) ? va_get_view_geo_breakdown( $post_id, 150 ) : [];
        $post_type = get_post_type( $post_id ) ?: 'va_listing';
        $title     = get_the_title( $post_id );
        $back_url  = admin_url( 'edit.php?post_type=' . $post_type );

        $total = 0;
        foreach ( $rows as $row ) {
            $total += (int) ( $row['views'] ?? 0 );
        }

        ob_start();
        ?>
        <style>
            :root {
                --va-bg: rgb(6, 6, 6);
                --va-bg-soft: #0b0b0b;
                --va-border: rgba(255, 0, 0, .55);
                --va-border-soft: rgba(255, 0, 0, .24);
                --va-accent: #ff0000;
                --va-text: #fff;
                --va-muted: rgba(255, 255, 255, .78);
            }
            html, body {
                margin: 0;
                padding: 0;
                min-height: 100%;
                background:
                    radial-gradient(circle at 1px 1px, rgba(255,255,255,.08) 1px, transparent 1px) 0 0/18px 18px,
                    var(--va-bg);
                color: var(--va-text);
            }
            #wpcontent, #wpbody-content {
                background: transparent;
            }
            .va-geo-report-page {
                max-width: 1120px;
                margin: 28px auto;
                padding: 0 16px;
                box-sizing: border-box;
            }
            .va-geo-report-panel {
                background: linear-gradient(180deg, rgba(10,10,10,.95), rgba(4,4,4,.95));
                border: 1px solid var(--va-border);
                border-radius: 14px;
                box-shadow: 0 18px 40px rgba(0,0,0,.5), 0 0 0 1px rgba(255,0,0,.14) inset;
                overflow: hidden;
            }
            .va-geo-report-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
                padding: 18px 20px;
                border-bottom: 1px solid var(--va-border-soft);
            }
            .va-geo-report-title {
                margin: 0;
                font-size: 34px;
                line-height: 1;
                letter-spacing: .02em;
                font-weight: 800;
                color: var(--va-text);
            }
            .va-geo-report-meta {
                margin: 10px 0 0;
                color: var(--va-muted);
                font-size: 16px;
                line-height: 1.45;
            }
            .va-geo-report-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                border: 1px solid var(--va-border);
                background: rgba(20, 20, 20, .92);
                color: var(--va-text);
                border-radius: 10px;
                text-decoration: none;
                padding: 8px 12px;
                font-weight: 700;
                font-size: 13px;
                line-height: 1;
                transition: background .15s ease, transform .15s ease;
            }
            .va-geo-report-btn:hover {
                background: rgba(255, 0, 0, .2);
                transform: translateY(-1px);
                color: #fff;
            }
            .va-geo-report-body {
                padding: 18px 20px 14px;
            }
            .va-geo-report-empty {
                border: 1px solid var(--va-border-soft);
                border-radius: 10px;
                background: var(--va-bg-soft);
                padding: 14px 16px;
                color: var(--va-muted);
            }
            .va-geo-report-table-wrap {
                border: 1px solid var(--va-border-soft);
                border-radius: 10px;
                overflow: auto;
                background: rgba(0,0,0,.28);
            }
            .va-geo-report-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 760px;
                color: var(--va-text);
            }
            .va-geo-report-table th,
            .va-geo-report-table td {
                text-align: left;
                padding: 10px 12px;
                border-bottom: 1px solid rgba(255,255,255,.11);
            }
            .va-geo-report-table th {
                color: #f2f2f2;
                background: rgba(255,255,255,.05);
                font-size: 13px;
                font-weight: 700;
            }
            .va-geo-report-table td {
                font-size: 17px;
                line-height: 1.35;
                color: #fff;
            }
            .va-geo-report-code {
                font-weight: 800;
                letter-spacing: .02em;
            }
            .va-geo-report-country {
                color: var(--va-muted);
                font-size: 15px;
                display: block;
                margin-top: 2px;
            }
            @media (max-width: 800px) {
                .va-geo-report-head {
                    flex-direction: column;
                    align-items: stretch;
                }
                .va-geo-report-title {
                    font-size: 28px;
                }
                .va-geo-report-meta {
                    font-size: 15px;
                }
            }
        </style>

        <div class="va-geo-report-page">
            <div class="va-geo-report-panel">
                <div class="va-geo-report-head">
                    <div>
                        <h1 class="va-geo-report-title">Megtekintési lokációk</h1>
                        <p class="va-geo-report-meta">Hirdetés: <strong><?php echo esc_html( $title ?: ( 'ID: ' . $post_id ) ); ?></strong></p>
                        <p class="va-geo-report-meta">Összes megtekintés: <strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong></p>
                    </div>
                    <a class="va-geo-report-btn" href="<?php echo esc_url( $back_url ); ?>">Vissza a listához</a>
                </div>

                <div class="va-geo-report-body">
                    <?php if ( empty( $rows ) ) : ?>
                        <div class="va-geo-report-empty">Még nincs lokációs adat ennél a hirdetésnél.</div>
                    <?php else : ?>
                        <div class="va-geo-report-table-wrap">
                            <table class="va-geo-report-table">
                                <thead>
                                    <tr>
                                        <th style="width:180px;">Ország</th>
                                        <th>Régió</th>
                                        <th>Város</th>
                                        <th style="width:150px;">Megtekintés</th>
                                        <th style="width:220px;">Utolsó</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ( $rows as $row ) : ?>
                                    <tr>
                                        <td>
                                            <span class="va-geo-report-code"><?php echo esc_html( (string) ( $row['country_code'] ?? '--' ) ); ?></span>
                                            <span class="va-geo-report-country"><?php echo esc_html( (string) ( $row['country'] ?? '' ) ); ?></span>
                                        </td>
                                        <td><?php echo esc_html( (string) ( $row['region'] ?? 'Ismeretlen' ) ); ?></td>
                                        <td><?php echo esc_html( (string) ( $row['city'] ?? 'Ismeretlen' ) ); ?></td>
                                        <td><strong><?php echo esc_html( number_format_i18n( (int) ( $row['views'] ?? 0 ) ) ); ?></strong></td>
                                        <td><?php echo esc_html( (string) ( $row['last_seen'] ?? '' ) ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        wp_die( (string) ob_get_clean(), 'Megtekintési lokációk', [ 'response' => 200 ] );
    }
}
