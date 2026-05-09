<?php
/**
 * Használtautó.hu alapú jármű-katalógus adatok.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Vehicle_Catalog {

    private static $brand_models = null;

    public static function get_dataset_version(): string {
        return 'hasznaltauto-2026-05-02';
    }

    public static function get_categories(): array {
        return [
            [ 'name' => 'Személyautó',    'slug' => 'szemelyauto' ],
            [ 'name' => 'Kisautó',        'slug' => 'kisauto' ],
            [ 'name' => 'Városi autó',    'slug' => 'varosi-auto' ],
            [ 'name' => 'Családi autó',   'slug' => 'csaladi-auto' ],
            [ 'name' => 'Terepjáró',      'slug' => 'terepjaro' ],
            [ 'name' => 'Kishaszonjármű', 'slug' => 'kishaszonjarmu' ],
            [ 'name' => 'Kisteherautó',   'slug' => 'kisteherauto' ],
            [ 'name' => 'Teherautó',      'slug' => 'teherauto' ],
            [ 'name' => 'Lakóautó / Camper', 'slug' => 'lakoauto' ],
            [ 'name' => 'Busz / Kisbusz', 'slug' => 'busz-kisbusz' ],
            [ 'name' => 'Motor',          'slug' => 'motor' ],
            [ 'name' => 'Egyéb',          'slug' => 'egyeb' ],
        ];
    }

    public static function get_brands(): array {
        return [
            'ABARTH','AIXAM','ALFA ROMEO','ALPINE','ARCTIC CAT','ARO','ASIA','ASTON MARTIN','AUDI','AUSTIN',
            'AVIA','BAJAJ','BARKAS','BEDFORD','BENTLEY','BMC','BMW','BOMBARDIER','BORGWARD','BYD',
            'CADILLAC','CENNTRO','CHERY','CHEVROLET','CHRYSLER','CITROEN','CUPRA','DACIA','DAEWOO','DAF',
            'DAIHATSU','DODGE','DONGFENG (DFSK)','DS','EVEASY','FARIZON','FERRARI','FIAT','FISKER','FORD',
            'FOTON','GAZ','GEELY','GENESIS','GMC','GREAT WALL','HONDA','HUMMER','HYUNDAI','INEOS',
            'INFINITI','ISUZU','IVECO','JAECOO','JAGUAR','JEEP','KAWASAKI','KGM (SSANGYONG)','KIA','KTM',
            'KYMCO','LADA','LANCIA','LAND ROVER','LDV','LEAPMOTOR','LEVC','LEXUS','LIGIER','LINCOLN',
            'LOTUS','MAHINDRA','MAN','MARUTI','MASERATI','MAXUS','MAZDA','MEGA','MERCEDES-AMG','MERCEDES-BENZ',
            'MERCEDES-MAYBACH','MG','MINI','MITSUBISHI','MOSZKVICS','MULTICAR','NIO','NISSAN','OLTCIT','OMODA',
            'OPEL','PEUGEOT','PIAGGIO','POLARIS','POLONEZ','POLSKI FIAT','PORSCHE','PROTON','RENAULT','ROLLS-ROYCE',
            'ROVER','SAAB','SEAT','SHUANGHUAN','SKODA','SKYWELL','SMART','SSANGYONG','STEYR','SUBARU',
            'SUZUKI','TALBOT','TATA','TESLA','TOYOTA','TRABANT','TRIUMPH','UAZ','VAUXHALL','VOLGA',
            'VOLKSWAGEN','VOLVO','VOYAH','WARTBURG','XPENG','YAMAHA','YUGO','ZASTAVA','ZAZ',
        ];
    }

    public static function get_body_type_options(): array {
        return [
            'hatchback'           => 'ferdehátú',
            'sedan'               => 'sedan',
            'wagon'               => 'kombi',
            'cabrio'              => 'cabrio',
            'mpv'                 => 'egyterű',
            'coupe'               => 'coupe',
            'crossover'           => 'városi terepjáró (crossover)',
            'closed'              => 'zárt',
            'double_cab_chassis'  => 'duplakabinos alváz',
            'pickup'              => 'pickup',
            'minibus'             => 'kisbusz',
            'single_cab_chassis'  => 'alváz szimpla kabin',
        ];
    }

    public static function get_vehicle_type_options(): array {
        return [
            'szemelyes'   => 'Személyautó',
            'motor'       => 'Motor',
            'kisteheruto' => 'Kisteherautó',
            'teheruto'    => 'Teherautó',
            'lakoauto'    => 'Lakóautó / Camper',
            'busz'        => 'Busz / Kisbusz',
            'egyeb'       => 'Egyéb',
        ];
    }

    public static function get_drive_options(): array {
        return [
            'elso'                => 'Első kerék meghajtás',
            'hatso'               => 'Hátsó kerék meghajtás',
            'osszkerek_kapc'      => 'Összkerék meghajtás (kapcsolható)',
            'osszkerek_allando'   => 'Összkerék meghajtás (állandó 4×4)',
        ];
    }

    public static function get_vehicle_condition_options(): array {
        return [
            'kituno'    => 'Kiváló',
            'jo'        => 'Jó',
            'kozepes'   => 'Közepes',
            'felujitando' => 'Felújítandó',
            'serult'    => 'Sérült / balesetes',
            'bontott'   => 'Bontott',
            'bemutato'  => 'Bemutatóautó',
        ];
    }

    public static function get_doc_type_options(): array {
        return [
            'magyar'           => 'Magyar',
            'kulfoldi'         => 'Külföldi',
            'import'           => 'Importált',
            'regisztralas_alatt' => 'Regisztrálás alatt',
        ];
    }

    public static function get_doc_validity_options(): array {
        return [
            'ervenyes'             => 'Érvényes okmányokkal',
            'lejart'               => 'Lejárt okmányokkal',
            'ideiglenesen_kivont'  => 'Ideiglenesen forgalomból kivont',
            'nelkul'               => 'Okmányok nélkül',
        ];
    }

    public static function get_ac_type_options(): array {
        return [
            'nincs'       => 'Nincs',
            'manualis'    => 'Manuális',
            'automata'    => 'Automata',
            'digitalis'   => 'Digitális',
            'ketzonas'    => 'Kétzónás',
            'tobbzonas'   => 'Többzónás',
            'hoszivattyus' => 'Hőszivattyús',
        ];
    }

    public static function get_eco_class_options(): array {
        return [
            'euro1'    => 'Euro 1',
            'euro2'    => 'Euro 2',
            'euro3'    => 'Euro 3',
            'euro4'    => 'Euro 4',
            'euro5'    => 'Euro 5',
            'euro6'    => 'Euro 6',
            'euro7'    => 'Euro 7',
            'electric' => 'Elektromos / Nulla emissziós',
            'egyeb'    => 'Egyéb',
        ];
    }

    public static function get_cylinder_layout_options(): array {
        return [
            'soros'  => 'Soros',
            'v'      => 'V-elrendezés',
            'boxer'  => 'Boxer',
            'w'      => 'W-elrendezés',
        ];
    }

    public static function get_roof_type_options(): array {
        return [
            'fix'       => 'Fix tető',
            'panorama'  => 'Panorámatető',
            'napfeny'   => 'Napfénytető (tolható)',
            'vaszon'    => 'Vászon tető (kabrió)',
            'tartaly'   => 'Tartálytető',
        ];
    }

    public static function get_extras_by_group(): array {
        return [
            'muszaki' => [
                'label' => 'Műszaki felszereltség',
                'items' => [
                    '4ws'                      => '4WS – összkerékkormányzás',
                    'adjustable_suspension'    => 'Állítható felfüggesztés',
                    'adjustable_steering'      => 'Állítható kormány',
                    'auto_cyl_shutdown'        => 'Automatikus hengerlekapcsolás',
                    'central_lock'             => 'Centrálzár',
                    'chiptuning'               => 'Chiptuning',
                    'edc'                      => 'EDC (elektr. lengéscsillapítás)',
                    'elec_window_front'        => 'Elektromos ablak (elöl)',
                    'elec_window_rear'         => 'Elektromos ablak (hátul)',
                    'elec_mirror'              => 'Elektromos tükör',
                    'onboard_computer'         => 'Fedélzeti komputer',
                    'heated_mirror'            => 'Fűthető tükör',
                    'hud'                      => 'HUD / Head-Up Display',
                    'hud_ar'                   => 'HUD (kiterjesztett valóság)',
                    'ceramic_brakes'           => 'Kerámia féktárcsák',
                    'dual_sliding_door'        => 'Kétoldali tolóajtó',
                    'alloy_wheels'             => 'Könnyűfém felni',
                    'paddle_shifter'           => 'Kormányváltó (paddle shifter)',
                    'chrome_wheels'            => 'Króm felni',
                    'extra_fuel_tank'          => 'Pót üzemanyagtartály',
                    'particulate_filter'       => 'Részecskeszűrő',
                    'alarm'                    => 'Riasztó',
                    'speed_servo'              => 'Sebességfüggő szervókormány',
                    'sperr_diff'               => 'Sperr differenciálmű',
                    'sport_suspension'         => 'Sportfutómű',
                    'sport_seats'              => 'Sportülések',
                    'start_stop'               => 'Start-stop rendszer',
                    'power_steering'           => 'Szervokormány',
                    'tinted_windows'           => 'Színezett üveg',
                    'sliding_door'             => 'Tolóajtó',
                    'elec_sliding_roof'        => 'Tolótető (elektromos)',
                    'sunroof'                  => 'Napfénytető / tolótető',
                    'towbar'                   => 'Vonóhorog',
                    'elec_towbar'              => 'Vonóhorog (elektromos)',
                    'removable_towbar'         => 'Vonóhorog (levehető fejjel)',
                ],
            ],
            'kenyemi' => [
                'label' => 'Kényelmi felszereltség',
                'items' => [
                    'full_extra'               => 'Full extra',
                    'parking_heater'           => 'Állófűtés',
                    'heated_washer_nozzles'    => 'Fűthető ablakmosó fúvókák',
                    'heated_all_seats'         => 'Fűthető első és hátsó ülések',
                    'heated_front_seats'       => 'Fűthető első ülés',
                    'heated_steering'          => 'Fűthető kormány',
                    'heated_windscreen'        => 'Fűtőszálas szélvédő',
                    'parking_ac'               => 'Álló helyzeti klíma',
                    'cooled_armrest'           => 'Hűthető kartámasz',
                    'cooled_glovebox'          => 'Hűthető kesztyűtartó',
                    'ventilated_seats'         => 'Üléshűtés / szellőztetés',
                    'leather_interior'         => 'Bőr belső',
                    'leatherette'              => 'Műbőr kárpit',
                    'velour'                   => 'Velúr kárpit',
                    'alcantara'                => 'Alcantara kárpit',
                    'plush_interior'           => 'Plüss kárpit',
                    'leather_fabric'           => 'Bőr-szövet huzat',
                    'leather_steering'         => 'Bőrkormány',
                    'socket_230v'              => '230V csatlakozó (hátul)',
                    'cam_360'                  => '360 fokos kamerarendszer',
                    'door_servo'               => 'Ajtószervó',
                    'adjustable_thigh_support' => 'Állítható combtámasz',
                    'adjustable_rear_seats'    => 'Állítható hátsó ülések',
                    'auto_dim_int_mirror'      => 'Automatikusan sötétedő belső tükör',
                    'auto_dim_ext_mirror'      => 'Automatikusan sötétedő külső tükör',
                    'lumbar_support'           => 'Deréktámasz',
                    'digital_dashboard'        => 'Digitális műszeregység',
                    'reclining_pass_seats'     => 'Dönthető utasülések',
                    'elec_tailgate'            => 'Elektromos csomagtérajtó',
                    'elec_seat_passenger'      => 'Elektromos ülésállítás (utas)',
                    'elec_seat_driver'         => 'Elektromos ülésállítás (vezető)',
                    'elec_headrests'           => 'Elektromosan állítható fejtámlák',
                    'elec_folding_mirrors'     => 'Elektromosan behajtható külső tükrök',
                    'elec_suspension_tuning'   => 'Elektronikus futómű hangolás',
                    'front_rear_park_sensor'   => 'Első-hátsó parkolóradar',
                    'wood_trim'                => 'Faberakás',
                    'garage_door_remote'       => 'Garázsajtó távirányító',
                    'gesture_control'          => 'Gesztusvezérlés',
                    'voice_control'            => 'Hangvezérlés',
                    'center_armrest'           => 'Középső kartámasz',
                    'keyless_start'            => 'Kulcsnélküli indítás',
                    'keyless_entry'            => 'Kulcsnélküli nyitórendszer',
                    'massage_seats'            => 'Masszírozós ülés',
                    'memory_passenger_seat'    => 'Memóriás utasülés',
                    'memory_driver_seat'       => 'Memóriás vezetőülés',
                    'multifunction_steering'   => 'Multifunkciós kormánykerék',
                    'remote_fold_rear_seats'   => 'Távirányítással ledönthető hátsó üléstámla',
                    'highbeam_assist'          => 'Távolsági fényszóró asszisztens',
                    'reversing_camera'         => 'Tolatókamera',
                    'rear_parking_sensor'      => 'Tolatóradar',
                    'seat_height_adj'          => 'Ülésmagasság állítás',
                ],
            ],
            'biztonsagi' => [
                'label' => 'Biztonsági felszereltség',
                'items' => [
                    'curtain_airbag'           => 'Függönylégzsák',
                    'pedestrian_airbag'        => 'Gyalogos légzsák',
                    'rear_side_airbag'         => 'Hátsó oldal légzsák',
                    'disableable_airbag'       => 'Kikapcsolható légzsák',
                    'center_airbag_front'      => 'Középső légzsák (elöl)',
                    'side_airbag'              => 'Oldallégzsák',
                    'knee_airbag'              => 'Térdlégzsák',
                    'passenger_airbag'         => 'Utasoldali légzsák',
                    'driver_airbag'            => 'Vezetőoldali légzsák',
                    'auto_headlights'          => 'Automata fényszórókapcsolás',
                    'auto_highbeam'            => 'Automata távfény',
                    'cornering_light'          => 'Bekanyarodási segédfény',
                    'bi_xenon'                 => 'Bi-xenon fényszóró',
                    'drl'                      => 'Bukólámpa (DRL)',
                    'headlight_height_adj'     => 'Fényszóró magasságállítás',
                    'headlight_washer'         => 'Fényszórómosó',
                    'adaptive_headlights'      => 'Kanyarkövető fényszóró',
                    'aux_lights'               => 'Kiegészítő fényszóró',
                    'fog_lights'               => 'Ködlámpa',
                    'led_headlights'           => 'LED fényszóró',
                    'led_matrix_headlights'    => 'LED mátrix fényszóró',
                    'daytime_running_lights'   => 'Menetfény',
                    'xenon_headlights'         => 'Xenon fényszóró',
                    'night_vision'             => 'Éjjellátó asszisztens',
                    'fatigue_detection'        => 'Fáradtságérzékelő',
                    'rear_cross_traffic'       => 'Hátsó kereszt forgalom figyelés',
                    'blind_spot'               => 'Holttér-figyelő rendszer',
                    'collision_prevention'     => 'Koccanásgátló',
                    'hill_descent'             => 'Lejtmenet asszisztens',
                    'parking_assist'           => 'Parkolóasszisztens',
                    'radar_brake_assist'       => 'Radaros fékasszisztens',
                    'lane_keeping'             => 'Sávtartó rendszer',
                    'lane_change_assist'       => 'Sávváltó asszisztens',
                    'adaptive_cruise'          => 'Távolságtartó tempomat',
                    'cruise_control'           => 'Tempomat',
                    'emergency_brake'          => 'Vészfék asszisztens',
                    'hill_start_assist'        => 'Visszagurulás-gátló',
                    'abs'                      => 'ABS (blokkolásgátló)',
                    'ads'                      => 'ADS (adaptív lengéscsillapító)',
                    'ard'                      => 'ARD (automatikus távolságtartó)',
                    'asr'                      => 'ASR (kipörgésgátló)',
                    'emergency_call'           => 'Automatikus segélyhívó',
                    'built_in_child_seat'      => 'Beépített gyerekülés',
                    'roll_bar'                 => 'Bukócső',
                    'load_securing'            => 'Csomag rögzítő',
                    'run_flat_tires'           => 'Defekttűrő abroncsok',
                    'ebd'                      => 'EBD/EBV (elektronikus fékerő-elosztó)',
                    'eds'                      => 'EDS (elektronikus differenciálzár)',
                    'elec_parking_brake'       => 'Elektronikus rögzítőfék',
                    'rain_sensor'              => 'Esőszenzor',
                    'esp'                      => 'ESP (menetstabilizátor)',
                    'brake_assist'             => 'Fékasszisztens',
                    'gps_tracker'              => 'GPS nyomkövető',
                    'tpms'                     => 'Guminyomás-ellenőrző',
                    'rear_headrests'           => 'Hátsó fejtámlák',
                    'immobiliser'              => 'Indításgátló (immobiliser)',
                    'isofix'                   => 'ISOFIX rendszer',
                    'msr'                      => 'MSR (motorféknyomaték szabályzás)',
                    'anti_theft'               => 'Rablásgátló',
                    'gearbox_lock'             => 'Sebességváltó zár',
                    'sign_recognition'         => 'Tábla-felismerő funkció',
                    'pre_collision'            => 'Ütközés veszélyre felkészítő rendszer',
                ],
            ],
            'hifi' => [
                'label' => 'HiFi és multimédia',
                'items' => [
                    'android_auto'             => 'Android Auto',
                    'apple_carplay'            => 'Apple CarPlay',
                    'bluetooth'                => 'Bluetooth kihangosító',
                    'gps_nav'                  => 'GPS / Navigáció',
                    'touchscreen'              => 'Érintőkijelző',
                    'multifunction_display'    => 'Multifunkcionális kijelző',
                    'wireless_charging'        => 'Vezeték nélküli telefontöltés',
                    'wifi_hotspot'             => 'WiFi Hotspot',
                    'steering_hifi'            => 'Kormányról vezérelhető hifi',
                    'steering_remote'          => 'Kormányra szerelhető távirányító',
                    'usb'                      => 'USB csatlakozó',
                    'aux'                      => 'AUX csatlakozó',
                    'hdmi'                     => 'HDMI bemenet',
                    'ipod'                     => 'iPhone/iPod csatlakozó',
                    'amplifier'                => 'Erősítő',
                    'factory_amplifier'        => 'Gyári erősítő',
                    'subwoofer'                => 'Mélynyomó',
                    'hifi_system'              => 'HiFi rendszer',
                    'cd_radio'                 => 'CD-s autórádió',
                    'cd_changer'               => 'CD tár',
                    'dvd'                      => 'DVD lejátszó',
                    'mp3'                      => 'MP3 lejátszás',
                    'mp4'                      => 'MP4 lejátszás',
                    'tv'                       => 'TV',
                    'dvb_t'                    => 'DVB-T tuner',
                    'fm_transmitter'           => 'FM transzmitter',
                    'headrest_monitor'         => 'Fejtámlamonitor',
                    'roof_monitor'             => 'Tetőmonitor',
                    'handsfree'                => 'Kihangosító',
                    'memory_card'              => 'Memóriakártya-olvasó',
                ],
            ],
            'kiegeszito' => [
                'label' => 'Kiegészítő felszereltség',
                'items' => [
                    'spare_wheel'              => 'Pótkerék',
                    'puncture_repair_kit'      => 'Defektjavító készlet',
                    'roof_rack'                => 'Tetőcsomagtartó',
                    'roof_bike_rack'           => 'Tetőre szerelhető kerékpártartó',
                    'towbar_bike_rack'         => 'Vonóhorgos kerékpártartó',
                    'home_charger'             => 'Otthoni hálózati töltő',
                    'type2_cable'              => 'Type2 töltőkábel',
                ],
            ],
            'egyeb_info' => [
                'label' => 'Egyéb információk',
                'items' => [
                    'warranty'                 => 'Garanciális',
                    'us_model'                 => 'Amerikai modell',
                    'immediately_available'    => 'Azonnal elvihető',
                    'demo_vehicle'             => 'Bemutató jármű',
                    'right_hand_drive'         => 'Jobbkormányos',
                    'orderable'                => 'Rendelhető',
                    'vat_reclaimable'          => 'ÁFA visszaigényelhető',
                    'trade_in'                 => 'Autóbeszámítás lehetséges',
                    'first_reg_hungary'        => 'Első forgalomba helyezés Mo.',
                    'first_owner'              => 'Első tulajdonostól',
                    'recently_serviced'        => 'Frissen szervizelt',
                    'guaranteed_mileage'       => 'Garantált km futás',
                    'garage_kept'              => 'Garázsban tartott',
                    'female_owner'             => 'Hölgy tulajdonostól',
                    'low_mileage'              => 'Keveset futott',
                    'second_owner'             => 'Második tulajdonostól',
                    'motorcycle_trade_in'      => 'Motorbeszámítás lehetséges',
                    'disabled_accessible'      => 'Mozgássérült',
                    'non_smoking'              => 'Nem dohányzó',
                    'regularly_maintained'     => 'Rendszeresen karbantartott',
                    'taxi'                     => 'Taxi',
                    'registration_document'    => 'Törzskönyv megvan',
                    'full_service_history'     => 'Végig vezetett szervizkönyv',
                    'service_history'          => 'Vezetett szervizkönyv',
                ],
            ],
        ];
    }

    public static function get_extras_options(): array {
        $all = [];
        foreach ( self::get_extras_by_group() as $group ) {
            $all += $group['items'];
        }
        return $all;
    }

    public static function get_brand_models(): array {
        if ( self::$brand_models !== null ) {
            return self::$brand_models;
        }

        $file = __DIR__ . '/vehicle-brand-models.json';
        if ( ! file_exists( $file ) ) {
            self::$brand_models = [];
            return self::$brand_models;
        }

        $raw = file_get_contents( $file );
        // UTF-8 BOM eltávolítása ha szükséges
        if ( is_string( $raw ) && substr( $raw, 0, 3 ) === "\xEF\xBB\xBF" ) {
            $raw = substr( $raw, 3 );
        }
        if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
            self::$brand_models = [];
            return self::$brand_models;
        }

        $decoded = json_decode( $raw, true );
        if ( ! is_array( $decoded ) ) {
            self::$brand_models = [];
            return self::$brand_models;
        }

        $normalized = [];
        foreach ( $decoded as $brand => $models ) {
            $brand_name = self::normalize_label( (string) $brand );
            if ( $brand_name === '' || ! is_array( $models ) ) {
                continue;
            }

            $clean_models = [];
            foreach ( $models as $model ) {
                $model_name = self::normalize_label( (string) $model );
                if ( $model_name !== '' ) {
                    $clean_models[] = $model_name;
                }
            }

            $normalized[ $brand_name ] = array_values( array_unique( $clean_models ) );
        }

        self::$brand_models = $normalized;
        return self::$brand_models;
    }

    private static function normalize_label( string $value ): string {
        $value = trim( $value );
        if ( $value === '' ) {
            return '';
        }

        $replacements = [
            "â€“Â " => '- ',
            "â€“ "  => '- ',
            'â€“'    => '-',
            'Â '     => ' ',
            'NÂ°'    => 'N°',
            'Ă–'    => 'Ö',
            'Ă'    => 'Á',
            'Ă‰'    => 'É',
            'Ă“'    => 'Ó',
            'Ăś'    => 'ö',
            'Ăź'    => 'ü',
            'Ăš'    => 'Ú',
            'Ăœ'    => 'Ü',
            'NĂś'   => 'NÖ',
            'SĂś'   => 'SÖ',
            'BOGĂR' => 'BOGÁR',
        ];

        $value = str_replace( array_keys( $replacements ), array_values( $replacements ), $value );
        $value = preg_replace( '/\s+/u', ' ', $value );

        return trim( (string) $value );
    }
}
