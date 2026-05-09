<?php
/**
 * Aukció rendszer: licit kezelés, idő lejárat, nyertes meghatározás
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Auctions {

    public static function init() {
        if ( function_exists( 'va_auctions_enabled' ) && ! va_auctions_enabled() ) {
            $next = wp_next_scheduled( 'va_close_expired_auctions' );
            if ( $next ) {
                wp_unschedule_event( $next, 'va_close_expired_auctions' );
            }
            return;
        }

        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
        add_action( 'wp_ajax_va_place_bid',             [ __CLASS__, 'ajax_place_bid' ] );
        add_action( 'wp_ajax_nopriv_va_place_bid',      [ __CLASS__, 'ajax_place_bid_nopriv' ] );
        add_action( 'wp_ajax_va_get_bid_status',        [ __CLASS__, 'ajax_get_bid_status' ] );
        add_action( 'wp_ajax_nopriv_va_get_bid_status', [ __CLASS__, 'ajax_get_bid_status' ] );

        // Egyedi cron intervallum: 5 perc
        add_filter( 'cron_schedules', function( $schedules ) {
            $schedules['va_every_5min'] = [
                'interval' => 300,
                'display'  => 'Minden 5 percben (VadászApró)',
            ];
            return $schedules;
        });

        // Lejárt aukciók lezárása – 5 percenként
        if ( ! wp_next_scheduled( 'va_close_expired_auctions' ) ) {
            wp_schedule_event( time(), 'va_every_5min', 'va_close_expired_auctions' );
        }
        add_action( 'va_close_expired_auctions', [ __CLASS__, 'close_expired_auctions' ] );
    }

    public static function enqueue() {
        if ( function_exists( 'va_auctions_enabled' ) && ! va_auctions_enabled() ) {
            return;
        }

        if ( is_singular( 'va_auction' ) || va_is_page( 'va-aukciok' ) ) {
            wp_enqueue_script( 'va-auction', VA_PLUGIN_URL . 'frontend/js/auction.js', [ 'jquery' ], VA_VERSION, true );
            wp_localize_script( 'va-auction', 'VA_Auction', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'va_bid_nonce' ),
                'strings'  => [
                    'confirm_bid'  => 'Biztosan licitel ezzel az összeggel?',
                    'bid_success'  => 'Licit sikeresen leadva!',
                    'not_logged'   => 'Licitáláshoz be kell jelentkezni.',
                    'bid_too_low'  => 'A licit összege túl alacsony.',
                    'auction_over' => 'Az aukció lejárt.',
                ],
            ]);
        }
    }

    /* ── Licit leadása (AJAX) ──────────────────────────── */
    public static function ajax_place_bid_nopriv() {
        wp_send_json_error( [ 'message' => 'Licitáláshoz be kell jelentkezni.' ] );
    }

    public static function ajax_place_bid() {
        check_ajax_referer( 'va_bid_nonce', 'nonce' );

        $auction_id = intval( $_POST['auction_id'] ?? 0 );
        $amount     = floatval( $_POST['amount']     ?? 0 );
        $user_id    = get_current_user_id();

        if ( ! $auction_id || $amount <= 0 ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen adat.' ] );
        }

        $post = get_post( $auction_id );
        if ( ! $post || $post->post_type !== 'va_auction' ) {
            wp_send_json_error( [ 'message' => 'Nem létező aukció.' ] );
        }

        // Lejárat ellenőrzés
        $end = get_post_meta( $auction_id, 'va_auction_end', true );
        if ( $end && strtotime( $end ) < time() ) {
            wp_send_json_error( [ 'message' => 'Az aukció lejárt.' ] );
        }

        $current_bid  = floatval( get_post_meta( $auction_id, 'va_current_bid',  true ) ?: 0 );
        $start_price  = floatval( get_post_meta( $auction_id, 'va_start_price',  true ) ?: 0 );
        $min_step     = floatval( get_post_meta( $auction_id, 'va_min_bid_step', true ) ?: 500 );
        $buyout       = floatval( get_post_meta( $auction_id, 'va_buyout_price', true ) ?: 0 );

        $min_required = max( $start_price, $current_bid + $min_step );
        if ( $amount < $min_required ) {
            wp_send_json_error( [ 'message' => sprintf( 'Minimum licit: %s Ft', number_format( $min_required, 0, ',', ' ' ) ) ] );
        }

        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'va_bids', [
            'auction_id' => $auction_id,
            'user_id'    => $user_id,
            'amount'     => $amount,
        ], [ '%d', '%d', '%f' ] );

        if ( $wpdb->last_error ) {
            wp_send_json_error( [ 'message' => 'Adatbázis hiba.' ] );
        }

        update_post_meta( $auction_id, 'va_current_bid', $amount );
        $count = intval( get_post_meta( $auction_id, 'va_bid_count', true ) ?: 0 );
        update_post_meta( $auction_id, 'va_bid_count', $count + 1 );

        // Azonnali vásárlás logika
        $buyout_triggered = false;
        if ( $buyout > 0 && $amount >= $buyout ) {
            update_post_meta( $auction_id, 'va_auction_winner', $user_id );
            update_post_meta( $auction_id, 'va_auction_end', current_time( 'mysql' ) );
            $buyout_triggered = true;
        }

        // E-mail értesítés az előző legmagasabb licitálónak
        self::notify_outbid( $auction_id, $user_id, $amount );

        wp_send_json_success([
            'message'         => $buyout_triggered ? 'Gratulálunk! Megvette az azonnali vásárlással!' : 'Licit sikeresen leadva!',
            'current_bid'     => $amount,
            'current_bid_fmt' => number_format( $amount, 0, ',', ' ' ) . ' Ft',
            'bid_count'       => $count + 1,
            'buyout'          => $buyout_triggered,
        ]);
    }

    /* ── Licit státusz lekérés (real-time frissítéshez) ── */
    public static function ajax_get_bid_status() {
        $auction_id = intval( $_GET['auction_id'] ?? 0 );
        if ( ! $auction_id ) wp_send_json_error();

        $end         = get_post_meta( $auction_id, 'va_auction_end',  true );
        $current_bid = floatval( get_post_meta( $auction_id, 'va_current_bid', true ) ?: 0 );
        $start_price = floatval( get_post_meta( $auction_id, 'va_start_price', true ) ?: 0 );
        $min_step    = floatval( get_post_meta( $auction_id, 'va_min_bid_step', true ) ?: 500 );
        $bid_count   = intval( get_post_meta( $auction_id, 'va_bid_count', true ) ?: 0 );

        $is_over = $end && strtotime( $end ) < time();
        // Ha lejárt, azonnal írjuk ki a closed flag-et (nem kell várni a cronra)
        if ( $is_over && ! get_post_meta( $auction_id, 'va_auction_closed', true ) ) {
            update_post_meta( $auction_id, 'va_auction_closed', '1' );
        }

        wp_send_json_success([
            'current_bid'     => $current_bid,
            'current_bid_fmt' => number_format( $current_bid ?: $start_price, 0, ',', ' ' ) . ' Ft',
            'min_next_bid'    => max( $start_price, $current_bid + $min_step ),
            'bid_count'       => $bid_count,
            'is_over'         => $is_over,
            'ends_in'         => $end ? max( 0, strtotime( $end ) - time() ) : 0,
        ]);
    }

    /* ── Lejárt aukciók lezárása ───────────────────────── */
    public static function close_expired_auctions() {
        if ( function_exists( 'va_auctions_enabled' ) && ! va_auctions_enabled() ) {
            return;
        }

        global $wpdb;

        $now = current_time( 'mysql' );

        // Batch 100-asával – 2-3M hirdetésnél sem tölt mindent memóriába
        $page = 0;
        do {
            $auctions = get_posts([
                'post_type'      => 'va_auction',
                'post_status'    => 'publish',
                'meta_query'     => [
                    [
                        'key'     => 'va_auction_end',
                        'value'   => $now,
                        'compare' => '<',
                        'type'    => 'DATETIME',
                    ],
                    [
                        'key'     => 'va_auction_closed',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
                'posts_per_page' => 100,
                'offset'         => $page * 100,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]);

            foreach ( $auctions as $auction_id ) {
                $winner = $wpdb->get_row( $wpdb->prepare(
                    "SELECT user_id, amount FROM {$wpdb->prefix}va_bids WHERE auction_id = %d ORDER BY amount DESC LIMIT 1",
                    $auction_id
                ));

                update_post_meta( $auction_id, 'va_auction_closed', '1' );

                if ( $winner ) {
                    update_post_meta( $auction_id, 'va_auction_winner', $winner->user_id );
                    self::notify_winner( $auction_id, $winner->user_id, $winner->amount );
                }
            }

            $page++;
        } while ( count( $auctions ) === 100 );
    }

    /* ── E-mail értesítők ──────────────────────────────── */
    private static function notify_outbid( $auction_id, $new_bidder_id, $new_amount ) {
        global $wpdb;
        $prev = $wpdb->get_row( $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}va_bids
             WHERE auction_id = %d AND user_id != %d
             ORDER BY amount DESC LIMIT 1",
            $auction_id, $new_bidder_id
        ));
        if ( ! $prev ) return;

        $user    = get_userdata( $prev->user_id );
        $amount_fmt = number_format( $new_amount, 0, ',', '\u00a0' );
        $title   = get_the_title( $auction_id );
        $vars    = [
            '{name}'   => esc_html( $user->display_name ),
            '{title}'  => esc_html( $title ),
            '{amount}' => esc_html( $amount_fmt ),
        ];
        VA_Mailer::send(
            $user->user_email,
            strtr( get_option( 'va_email_outbid_subject', 'T\u00falllicit\u00e1ltak \u2013 {title}' ), $vars ),
            strtr( get_option( 'va_email_outbid_heading', 'T\u00falllicit\u00e1ltak t\u00e9ged!' ), $vars ),
            strtr( get_option( 'va_email_outbid_body',    '' ), $vars ),
            [ 'label' => strtr( get_option( 'va_email_outbid_btn', 'Licit\u00e1ljon \u00fajra' ), $vars ), 'url' => get_permalink( $auction_id ) ]
        );
    }

    private static function notify_winner( $auction_id, $user_id, $amount ) {
        $user       = get_userdata( $user_id );
        $amount_fmt = number_format( $amount, 0, ',', '\u00a0' );
        $title      = get_the_title( $auction_id );
        $vars_w = [
            '{name}'   => esc_html( $user->display_name ),
            '{title}'  => esc_html( $title ),
            '{amount}' => esc_html( $amount_fmt ),
        ];

        // Nyertesnek
        VA_Mailer::send(
            $user->user_email,
            strtr( get_option( 'va_email_winner_subject', 'Nyertél az aukción! – {title}' ), $vars_w ),
            strtr( get_option( 'va_email_winner_heading', '🏆 Nyertél az aukción!' ), $vars_w ),
            strtr( get_option( 'va_email_winner_body', '' ), $vars_w ),
            [ 'label' => strtr( get_option( 'va_email_winner_btn', 'Aukció megtekintése' ), $vars_w ), 'url' => get_permalink( $auction_id ) ]
        );

        // Eladónak is értesítés
        $post   = get_post( $auction_id );
        $seller = get_userdata( $post->post_author );
        $vars_s = [
            '{seller_name}'  => esc_html( $seller->display_name ),
            '{title}'        => esc_html( $title ),
            '{amount}'       => esc_html( $amount_fmt ),
            '{winner_name}'  => esc_html( $user->display_name ),
            '{winner_email}' => esc_attr( $user->user_email ),
        ];
        VA_Mailer::send(
            $seller->user_email,
            strtr( get_option( 'va_email_seller_subject', 'Aukciód lezárult – {title}' ), $vars_s ),
            strtr( get_option( 'va_email_seller_heading', 'Aukciód lezárult!' ), $vars_s ),
            strtr( get_option( 'va_email_seller_body', '' ), $vars_s ),
            [ 'label' => strtr( get_option( 'va_email_seller_btn', 'Aukció megtekintése' ), $vars_s ), 'url' => get_permalink( $auction_id ) ]
        );
    }
}
