<?php
/**
 * Taxonómiák: Kategória, Megye, Állapot (Hirdetés + Aukció)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Taxonomy {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_all' ] );
    }

    public static function register_all() {
        $object_types = [ 'va_listing' ];
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            $object_types[] = 'va_auction';
        }

        /* Kategória */
        register_taxonomy( 'va_category', $object_types, [
            'labels'        => [
                'name'          => 'Kategóriák',
                'singular_name' => 'Kategória',
                'menu_name'     => 'Kategóriák',
            ],
            'hierarchical'  => true,
            'public'        => true,
            'rewrite'       => [ 'slug' => 'kategoria' ],
            'show_in_rest'  => true,
        ]);

        /* Megye */
        register_taxonomy( 'va_county', $object_types, [
            'labels'        => [
                'name'          => 'Megyék',
                'singular_name' => 'Megye',
                'menu_name'     => 'Megyék',
            ],
            'hierarchical'  => false,
            'public'        => true,
            'rewrite'       => [ 'slug' => 'megye' ],
            'show_in_rest'  => true,
        ]);

        /* Állapot */
        register_taxonomy( 'va_condition', $object_types, [
            'labels'        => [
                'name'          => 'Állapot',
                'singular_name' => 'Állapot',
                'menu_name'     => 'Állapot',
            ],
            'hierarchical'  => false,
            'public'        => true,
            'rewrite'       => [ 'slug' => 'allapot' ],
            'show_in_rest'  => true,
        ]);

        self::insert_default_terms();
    }

    private static function insert_default_terms() {
        self::sync_vehicle_categories();

        /* Állapot */
        $conditions = [ 'Új', 'Használt – Kiváló', 'Használt – Jó', 'Használt – Közepes', 'Sérült / Alkatrésznek' ];
        foreach ( $conditions as $c ) {
            if ( ! term_exists( $c, 'va_condition' ) ) {
                wp_insert_term( $c, 'va_condition' );
            }
        }

        /* Megyék */
        $counties = [
            'Bács-Kiskun','Baranya','Békés','Borsod-Abaúj-Zemplén','Csongrád-Csanád',
            'Fejér','Győr-Moson-Sopron','Hajdú-Bihar','Heves','Jász-Nagykun-Szolnok',
            'Komárom-Esztergom','Nógrád','Pest','Somogy','Szabolcs-Szatmár-Bereg',
            'Tolna','Vas','Veszprém','Zala','Budapest',
        ];
        foreach ( $counties as $county ) {
            if ( ! term_exists( $county, 'va_county' ) ) {
                wp_insert_term( $county, 'va_county' );
            }
        }
    }

    private static function sync_vehicle_categories(): void {
        if ( ! class_exists( 'VA_Vehicle_Catalog' ) ) {
            return;
        }

        $dataset_version = VA_Vehicle_Catalog::get_dataset_version();
        $categories      = VA_Vehicle_Catalog::get_categories();

        if ( get_option( 'va_category_dataset_ver' ) !== $dataset_version ) {
            $existing_ids = get_terms( [
                'taxonomy'   => 'va_category',
                'hide_empty' => false,
                'fields'     => 'ids',
            ] );

            if ( ! is_wp_error( $existing_ids ) ) {
                foreach ( $existing_ids as $term_id ) {
                    wp_delete_term( (int) $term_id, 'va_category' );
                }
            }

            update_option( 'va_category_dataset_ver', $dataset_version, false );

            if ( get_option( 'va_site_type', 'vadaszat' ) !== 'jarmu' ) {
                update_option( 'va_site_type', 'jarmu' );
            }
        }

        foreach ( $categories as $category ) {
            $existing = get_term_by( 'slug', (string) $category['slug'], 'va_category' );
            if ( ! $existing ) {
                wp_insert_term( (string) $category['name'], 'va_category', [
                    'slug' => (string) $category['slug'],
                ] );
            }
        }
    }
}
