<?php
/**
 * Meta mezők: Hirdetés + Aukció egyéni adatok
 * Admin metabox + mentés + frontend getter függvények
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Meta_Fields {

    /* ── Oldaltípus lekérése ────────────────────────────── */
    public static function get_site_type(): string {
        $t = (string) get_option( 'va_site_type', 'vadaszat' );
        return in_array( $t, [ 'vadaszat', 'jarmu', 'ingatlan', 'altalanos' ], true ) ? $t : 'vadaszat';
    }

    /* ── Típus-specifikus extra mezők (admin metabox + AJAX mentés) ─── */
    public static function get_type_extra_fields(): array {
        switch ( self::get_site_type() ) {
            case 'jarmu':
                return [
                    'va_vehicle_type'    => [ 'label' => 'Járműkategória',           'type' => 'select',
                        'options' => class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_vehicle_type_options() : [] ],
                    'va_brand'           => [ 'label' => 'Gyártó',                  'type' => 'text' ],
                    'va_model'           => [ 'label' => 'Modell',                   'type' => 'text' ],
                    'va_year'            => [ 'label' => 'Évjárat',                  'type' => 'number', 'min' => 1900, 'max' => 2030 ],
                    'va_mileage'         => [ 'label' => 'Kilométeróra (km)',        'type' => 'number', 'min' => 0 ],
                    'va_fuel_type'       => [ 'label' => 'Üzemanyag',               'type' => 'select',
                        'options' => [ 'benzin'=>'Benzin','diesel'=>'Dízel','hybrid'=>'Hibrid','electric'=>'Elektromos','lpg'=>'LPG','cng'=>'CNG','egyeb'=>'Egyéb' ] ],
                    'va_performance_kw'  => [ 'label' => 'Teljesítmény (kW)',        'type' => 'number', 'min' => 0 ],
                    'va_engine_size'     => [ 'label' => 'Hengerűrtartalom (cm³)',   'type' => 'number', 'min' => 0 ],
                    'va_transmission'    => [ 'label' => 'Sebességváltó',            'type' => 'select',
                        'options' => [ 'manual'=>'Kéziváltó','automatic'=>'Automata','semi_auto'=>'Félautomata','cvt'=>'CVT' ] ],
                    'va_body_type'       => [ 'label' => 'Felépítmény',             'type' => 'select',
                        'options' => class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_body_type_options() : [ 'sedan'=>'sedan','hatchback'=>'ferdehátú','wagon'=>'kombi','cabrio'=>'cabrio','mpv'=>'egyterű','coupe'=>'coupe','crossover'=>'városi terepjáró (crossover)','closed'=>'zárt','double_cab_chassis'=>'duplakabinos alváz','pickup'=>'pickup','minibus'=>'kisbusz','single_cab_chassis'=>'alváz szimpla kabin' ] ],
                    'va_color'           => [ 'label' => 'Szín',                     'type' => 'text' ],
                    'va_doors'           => [ 'label' => 'Ajtók száma',              'type' => 'select',
                        'options' => [ '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5' ] ],
                    'va_owners'          => [ 'label' => 'Tulajdonosok száma',       'type' => 'number', 'min' => 1, 'max' => 20 ],
                    'va_keys'            => [ 'label' => 'Kulcsok száma',            'type' => 'number', 'min' => 0, 'max' => 10 ],
                    'va_previous_damage'    => [ 'label' => 'Korábbi kár / baleset',       'type' => 'checkbox' ],
                    'va_service_book'       => [ 'label' => 'Szervizkönyv megvan',          'type' => 'checkbox' ],
                    'va_tech_inspect'       => [ 'label' => 'Műszaki vizsga lejár',         'type' => 'date' ],
                    'va_first_reg'          => [ 'label' => 'Első forgalomba hely. (év.hó)', 'type' => 'text' ],
                    'va_drive'              => [ 'label' => 'Hajtás',                       'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_drive_options() : [] ],
                    'va_vehicle_condition'  => [ 'label' => 'Jármű állapota',               'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_vehicle_condition_options() : [] ],
                    'va_doc_type'           => [ 'label' => 'Okmányok jellege',             'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_doc_type_options() : [] ],
                    'va_doc_validity'       => [ 'label' => 'Okmányok érvényessége',        'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_doc_validity_options() : [] ],
                    'va_ac_type'            => [ 'label' => 'Klíma',                        'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_ac_type_options() : [] ],
                    'va_eco_class'          => [ 'label' => 'Környezetvédelmi osztály',     'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_eco_class_options() : [] ],
                    'va_cylinder_layout'    => [ 'label' => 'Henger-elrendezés',            'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_cylinder_layout_options() : [] ],
                    'va_own_weight'         => [ 'label' => 'Saját tömeg (kg)',             'type' => 'number', 'min' => 0 ],
                    'va_gross_weight'       => [ 'label' => 'Össztömeg (kg)',                'type' => 'number', 'min' => 0 ],
                    'va_passengers'         => [ 'label' => 'Szállítható személyek száma',  'type' => 'number', 'min' => 1, 'max' => 100 ],
                    'va_trunk_liters'       => [ 'label' => 'Csomagtartó (liter)',          'type' => 'number', 'min' => 0 ],
                    'va_range_gearbox'      => [ 'label' => 'Felező váltó',                 'type' => 'checkbox' ],
                    'va_roof_type'          => [ 'label' => 'Tető',                         'type' => 'select',
                        'options' => class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_roof_type_options() : [] ],
                    'va_color_metallic'     => [ 'label' => 'Metál fényezés',               'type' => 'checkbox' ],
                    'va_upholstery_1'       => [ 'label' => 'Kárpit színe (1)',             'type' => 'text' ],
                    'va_upholstery_2'       => [ 'label' => 'Kárpit színe (2)',             'type' => 'text' ],
                    'va_summer_tire_front'  => [ 'label' => 'Nyári gumi (első, pl. 205/55R16)', 'type' => 'text' ],
                    'va_summer_tire_rear'   => [ 'label' => 'Nyári gumi (hátsó)',           'type' => 'text' ],
                    'va_winter_tire_front'  => [ 'label' => 'Téli gumi (első)',             'type' => 'text' ],
                    'va_winter_tire_rear'   => [ 'label' => 'Téli gumi (hátsó)',            'type' => 'text' ],
                    'va_vin'                => [ 'label' => 'Alvázszám (VIN)',              'type' => 'text' ],
                    'va_internal_id'        => [ 'label' => 'Belső azonosító',              'type' => 'text' ],
                    'va_second_phone'       => [ 'label' => '2. telefonszám',               'type' => 'text' ],
                    'va_extras'             => [ 'label' => 'Extra felszereltség',          'type' => 'checkboxes' ],
                ];
            case 'ingatlan':
                return [
                    'va_area_m2'         => [ 'label' => 'Alapterület (m²)',        'type' => 'number', 'min' => 1 ],
                    'va_rooms'           => [ 'label' => 'Szobák száma',             'type' => 'number', 'min' => 0 ],
                    'va_floor'           => [ 'label' => 'Emelet',                  'type' => 'number', 'min' => -2, 'max' => 100 ],
                    'va_total_floors'    => [ 'label' => 'Összes szint',            'type' => 'number', 'min' => 1 ],
                    'va_lot_size'        => [ 'label' => 'Telek (m²)',              'type' => 'number', 'min' => 0 ],
                    'va_building_year'   => [ 'label' => 'Építési év',              'type' => 'number', 'min' => 1800, 'max' => 2030 ],
                    'va_parking'         => [ 'label' => 'Parkoló',                 'type' => 'select',
                        'options' => [ 'none'=>'Nincs','street'=>'Utcai','private'=>'Saját','garage'=>'Garázs' ] ],
                    'va_furnished'       => [ 'label' => 'Bútorozott',              'type' => 'select',
                        'options' => [ 'no'=>'Nem','partial'=>'Részben','yes'=>'Igen' ] ],
                    'va_heating'         => [ 'label' => 'Fűtés',                   'type' => 'select',
                        'options' => [ 'gas'=>'Gáz','electric'=>'Elektromos','district'=>'Távfűtés','wood'=>'Fa/szilárd','heat_pump'=>'Hőszivattyú' ] ],
                    'va_balcony'         => [ 'label' => 'Erkély / terasz',         'type' => 'checkbox' ],
                ];
            case 'altalanos':
                return [
                    'va_brand'           => [ 'label' => 'Márka',                   'type' => 'text' ],
                    'va_model'           => [ 'label' => 'Modell / Típus',          'type' => 'text' ],
                    'va_year'            => [ 'label' => 'Gyártási év',             'type' => 'number', 'min' => 1800, 'max' => 2030 ],
                ];
            default: // vadaszat
                return [
                    'va_brand'           => [ 'label' => 'Márka / Gyártó',          'type' => 'text' ],
                    'va_model'           => [ 'label' => 'Modell / Típus',          'type' => 'text' ],
                    'va_caliber'         => [ 'label' => 'Kaliber',                 'type' => 'text' ],
                    'va_year'            => [ 'label' => 'Gyártási év',             'type' => 'number', 'min' => 1800, 'max' => 2030 ],
                    'va_license_req'     => [ 'label' => 'Fegyverengedély szükséges', 'type' => 'checkbox' ],
                ];
        }
    }

    /* ── Listing mezők definíciója ─────────────────────── */
    public static function listing_fields() {
        $base = [
            'va_price'        => [ 'label' => 'Ár (Ft)', 'type' => 'number', 'min' => 0 ],
            'va_price_type'   => [ 'label' => 'Árazás', 'type' => 'select',
                                   'options' => [ 'fixed' => 'Fix ár', 'negotiable' => 'Alkudható', 'free' => 'Ingyenes', 'on_request' => 'Érdeklődjön' ] ],
            'va_phone'        => [ 'label' => 'Telefonszám', 'type' => 'tel' ],
            'va_email_show'   => [ 'label' => 'Email megjelenítése', 'type' => 'checkbox' ],
            'va_location'     => [ 'label' => 'Helység (város/község)', 'type' => 'text' ],
            'va_expires'      => [ 'label' => 'Lejárat dátuma', 'type' => 'date' ],
            'va_featured'     => [ 'label' => 'Kiemelt hirdetés', 'type' => 'checkbox' ],
            'va_verified'     => [ 'label' => 'Ellenőrzött', 'type' => 'checkbox' ],
            'va_views'        => [ 'label' => 'Megtekintések', 'type' => 'number', 'readonly' => true ],
        ];
        return array_merge( $base, self::get_type_extra_fields() );
    }

    /* ── Auction extra mezők ───────────────────────────── */
    public static function auction_fields() {
        return [
            'va_start_price'  => [ 'label' => 'Kikiáltási ár (Ft)', 'type' => 'number', 'min' => 0 ],
            'va_min_bid_step' => [ 'label' => 'Min. licitlépés (Ft)', 'type' => 'number', 'min' => 1 ],
            'va_buyout_price' => [ 'label' => 'Azonnal vásárlás ár (Ft, 0 = nincs)', 'type' => 'number', 'min' => 0 ],
            'va_auction_end'  => [ 'label' => 'Aukció vége (dátum + idő)', 'type' => 'datetime-local' ],
            'va_current_bid'  => [ 'label' => 'Aktuális licit (Ft)', 'type' => 'number', 'readonly' => true ],
            'va_bid_count'    => [ 'label' => 'Licitek száma', 'type' => 'number', 'readonly' => true ],
            'va_auction_winner' => [ 'label' => 'Nyertes user ID', 'type' => 'number', 'readonly' => true ],
            // Listing mezon is öröklik
            'va_brand'        => [ 'label' => 'Márka', 'type' => 'text' ],
            'va_model'        => [ 'label' => 'Modell', 'type' => 'text' ],
            'va_caliber'      => [ 'label' => 'Kaliber', 'type' => 'text' ],
            'va_year'         => [ 'label' => 'Gyártási év', 'type' => 'number' ],
            'va_phone'        => [ 'label' => 'Kapcsolat telefon', 'type' => 'tel' ],
            'va_location'     => [ 'label' => 'Helyszín', 'type' => 'text' ],
            'va_license_req'  => [ 'label' => 'Fegyverengedély szükséges', 'type' => 'checkbox' ],
        ];
    }

    /* ── Init ──────────────────────────────────────────── */
    public static function init() {
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_metaboxes' ] );
        add_action( 'save_post',      [ __CLASS__, 'save_meta' ], 10, 2 );
    }

    public static function add_metaboxes() {
        add_meta_box( 'va_listing_meta', 'Hirdetés adatai', [ __CLASS__, 'render_listing_box' ],
            'va_listing', 'normal', 'high' );
        add_meta_box( 'va_auction_meta', 'Aukció adatai', [ __CLASS__, 'render_auction_box' ],
            'va_auction', 'normal', 'high' );
    }

    /* ── Rendering ─────────────────────────────────────── */
    private static function render_fields( $post, array $fields ) {
        wp_nonce_field( 'va_meta_save', 'va_meta_nonce' );
        echo '<table class="form-table">';
        foreach ( $fields as $key => $f ) {
            $val = get_post_meta( $post->ID, $key, true );
            echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';

            if ( $f['type'] === 'select' ) {
                echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
                foreach ( $f['options'] as $optval => $optlabel ) {
                    echo '<option value="' . esc_attr( $optval ) . '"' . selected( $val, $optval, false ) . '>' . esc_html( $optlabel ) . '</option>';
                }
                echo '</select>';
            } elseif ( $f['type'] === 'checkboxes' ) {
                $checked_arr = [];
                if ( is_string( $val ) && $val !== '' ) {
                    $decoded = json_decode( $val, true );
                    if ( is_array( $decoded ) ) $checked_arr = $decoded;
                }
                $opts = class_exists( 'VA_Vehicle_Catalog' ) ? VA_Vehicle_Catalog::get_extras_options() : [];
                echo '<div style="display:flex;flex-wrap:wrap;gap:6px;">';
                foreach ( $opts as $optkey => $optlabel ) {
                    $ch = in_array( $optkey, $checked_arr, true ) ? ' checked' : '';
                    echo '<label style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap;"><input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $optkey ) . '"' . $ch . '> ' . esc_html( $optlabel ) . '</label>';
                }
                echo '</div>';
            } elseif ( $f['type'] === 'checkbox' ) {
                echo '<input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1"' . checked( $val, '1', false ) . '>';
            } else {
                $attrs = '';
                if ( isset( $f['min'] ) )      $attrs .= ' min="' . esc_attr( $f['min'] ) . '"';
                if ( isset( $f['max'] ) )      $attrs .= ' max="' . esc_attr( $f['max'] ) . '"';
                if ( ! empty( $f['readonly'] ) ) $attrs .= ' readonly';
                echo '<input type="' . esc_attr( $f['type'] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text"' . $attrs . '>';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    public static function render_listing_box( $post ) {
        self::render_fields( $post, self::listing_fields() );
    }

    public static function render_auction_box( $post ) {
        self::render_fields( $post, self::auction_fields() );
    }

    /* ── Mentés ────────────────────────────────────────── */
    public static function save_meta( $post_id, $post ) {
        if ( ! isset( $_POST['va_meta_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_meta_nonce'] ) ), 'va_meta_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = $post->post_type === 'va_auction'
            ? self::auction_fields()
            : self::listing_fields();

        foreach ( $fields as $key => $f ) {
            if ( $f['type'] === 'checkboxes' ) {
                $raw = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? (array) $_POST[ $key ] : [];
                $valid = array_keys( class_exists('VA_Vehicle_Catalog') ? VA_Vehicle_Catalog::get_extras_options() : [] );
                $clean = array_values( array_intersect( array_map( 'sanitize_key', $raw ), $valid ) );
                update_post_meta( $post_id, $key, wp_json_encode( $clean ) );
            } elseif ( $f['type'] === 'checkbox' ) {
                update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
            } elseif ( isset( $_POST[ $key ] ) && empty( $f['readonly'] ) ) {
                $val = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
                update_post_meta( $post_id, $key, $val );
            }
        }
    }
}
