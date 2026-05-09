<?php
/**
 * Custom Post Types: va_listing (hirdetés) + va_auction (aukció)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Post_Types {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_listing' ] );
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            add_action( 'init', [ __CLASS__, 'register_auction'  ] );
        }
    }

    /* ── Hirdetés CPT ──────────────────────────────────── */
    public static function register_listing() {
        $labels = [
            'name'               => 'Hirdetések',
            'singular_name'      => 'Hirdetés',
            'add_new_item'       => 'Új hirdetés',
            'edit_item'          => 'Hirdetés szerkesztése',
            'search_items'       => 'Hirdetés keresése',
            'not_found'          => 'Nincs hirdetés',
            'menu_name'          => 'Hirdetések',
        ];

        register_post_type( 'va_listing', [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => [ 'slug' => 'hirdetes' ],
            'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ],
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-megaphone',
            'menu_position'      => 5,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ]);
    }

    /* ── Aukció CPT ────────────────────────────────────── */
    public static function register_auction() {
        if ( function_exists( 'va_auctions_enabled' ) && ! va_auctions_enabled() ) {
            return;
        }

        $labels = [
            'name'               => 'Aukciók',
            'singular_name'      => 'Aukció',
            'add_new_item'       => 'Új aukció',
            'edit_item'          => 'Aukció szerkesztése',
            'search_items'       => 'Aukció keresése',
            'not_found'          => 'Nincs aukció',
            'menu_name'          => 'Aukciók',
        ];

        register_post_type( 'va_auction', [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => [ 'slug' => 'aukcio' ],
            'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ],
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-hammer',
            'menu_position'      => 6,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ]);
    }
}
