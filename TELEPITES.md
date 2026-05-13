# VadászApró – Telepítési és fejlesztési útmutató

## Kanonikus források

Fejlesztésnél kizárólag ez a két mappa számít hivatalos forrásnak:

- `wp-plugin/vadaszapro-core`
- `wp-theme/vadaszapro-theme`

A deploy kizárólag éles módban történik: `Deploy All` → git push → GitHub Actions FTP a vadkarvadasz.hu oldalra.
A korábbi gyökérszintű duplikált theme/plugin másolatok el lettek távolítva, hogy ne lehessen rossz fájlt szerkeszteni.

## Architektúra

```
wp-plugin/
└── vadaszapro-core/           ← Plugin (telepítsd WP admin → Bővítmények)
    ├── vadaszapro-core.php    ← Főfájl, aktiváláskor adatbázis táblák + alap oldalak
    ├── includes/
    │   ├── class-post-types.php   ← va_listing + va_auction CPT
    │   ├── class-taxonomy.php     ← Kategória, megye, állapot taxonómiák (előre feltöltve)
    │   ├── class-meta-fields.php  ← Admin metabox-ok (hirdetés + aukció paraméterei)
    │   ├── class-user-system.php  ← Regisztráció, login, profil
    │   ├── class-auctions.php     ← Licit AJAX, visszaszámlálás, nyertes, e-mail
    │   ├── class-ad-zones.php     ← 6 reklámzóna (HTML beállítható backendben)
    │   ├── class-ajax.php         ← Hirdetés feladás, watchlist, szűrő, megtekintés
    │   ├── class-shortcodes.php   ← Shortcode-ok minden frontend blokkhoz
    │   └── helpers.php            ← Segédfüggvények
    ├── admin/
    │   ├── class-admin.php        ← Admin menük
    │   ├── class-settings-page.php ← Minden beállítás (6 fül: Általános, Reklámok, stb.)
    │   └── class-listing-columns.php ← Admin lista oszlopok + szűrők
    └── frontend/
        ├── css/frontend.css       ← SDH design CSS
        ├── js/frontend.js         ← Szűrő AJAX, watchlist, galéria, scroll animáció
        ├── js/auction.js          ← Aukció: visszaszámlálás, licit AJAX, real-time
        └── templates/
            ├── listing/card.php   ← Hirdetés kártya
            ├── listing/search.php ← Szűrő + AJAX lista
            ├── listing/submit-form.php ← Hirdetés feladás form
            ├── auction/list.php   ← Aukció lista
            └── user/
                ├── login-form.php
                ├── register-form.php
                └── dashboard.php  ← Fiókom (hirdetések, licitek, kedvencek, profil)

wp-theme/
└── vadaszapro-theme/          ← Téma (telepítsd WP admin → Megjelenés → Témák)
    ├── style.css              ← Téma info + SDH CSS (sötét, #ff0000, dot grid)
    ├── functions.php          ← Setup, widgetek, enqueue
    ├── header.php             ← Fejléc (logo, nav, reklám pozíciók)
    ├── footer.php             ← Lábléc (reklám + footer grid)
    ├── index.php              ← Főoldal hero + legújabb/kiemelt/aukciók
    ├── page.php               ← Normál oldalak template
    ├── single-va_listing.php  ← Hirdetés részletes oldal
    └── single-va_auction.php  ← Aukció részletes oldal + licit box
```

---

## Telepítési lépések

### 1. WordPress telepítés
- Töltsd le a WordPresst: https://hu.wordpress.org/
- Állítsd be adatbázis, domain, stb.

### 2. Plugin telepítés
```
Másold a wp-plugin/vadaszapro-core/ mappát a WordPress:
→ wp-content/plugins/vadaszapro-core/

WP Admin → Bővítmények → VadászApró Core → Aktiválás
```

Az aktiválás automatikusan:
- Létrehozza az adatbázis táblákat (`va_bids`, `va_watchlist`)
- Létrehozza az alap oldalakat (bejelentkezés, regisztráció, fiókom, hirdetés feladás, aukciók, keresés)
- Feltölti a kategóriákat, állapotokat, megyéket

### 3. Téma telepítés
```
Másold a wp-theme/vadaszapro-theme/ mappát a WordPress:
→ wp-content/themes/vadaszapro-theme/

WP Admin → Megjelenés → Témák → VadászApró Theme → Aktiválás
```

### 4. Backend beállítások (VadászApró menü)

#### Általános
- Oldal neve, leírás
- Hirdetések / oldal
- Hirdetés érvényessége (napokban)
- Azonnali megjelenés (jóváhagyás nélkül)
- Telefonszám kötelező-e
- Karbantartási mód

#### Reklámzónák
6 pozíció, mindegyikbe HTML-t (pl. Google AdSense) illeszthetsz:
- **header_top** – fejléc feletti sáv (970×90)
- **header_bottom** – navigáció alatti sáv (970×90)
- **sidebar_left** – bal oldalsáv (300×250)
- **sidebar_right** – jobb oldalsáv (300×250)
- **content_top** – tartalom felett (728×90)
- **footer_top** – lábléc feletti sáv (970×90)

Shortcode-dal is használható: `[va_ad_zone zone="sidebar_left"]`

#### Hirdetések
- Kiemelt hirdetés ára és időtartama
- Ingyenes hirdetések limit / felhasználó

#### Aukciók
- Alapértelmezett min. licitlépés
- Jutalék %

---

## Frontend shortcode-ok

| Shortcode | Leírás |
|---|---|
| `[va_listing_search]` | Hirdetések keresése + AJAX szűrő |
| `[va_submit_listing]` | Hirdetés feladása form |
| `[va_auction_list]` | Aukciók listája |
| `[va_login_form]` | Bejelentkezési form |
| `[va_register_form]` | Regisztrációs form |
| `[va_user_dashboard]` | Fiókom (hirdetések, licitek, kedvencek, profil) |
| `[va_ad_zone zone="header_top"]` | Reklámzóna megjelenítése |

---

## Adatbázis táblák

### `wp_va_bids` – Licitek
| Oszlop | Típus | Leírás |
|---|---|---|
| id | BIGINT | Auto increment |
| auction_id | BIGINT | Aukció post ID |
| user_id | BIGINT | Felhasználó ID |
| amount | DECIMAL | Licit összeg (Ft) |
| created_at | DATETIME | Licit időpontja |

### `wp_va_watchlist` – Kedvencek
| Oszlop | Típus | Leírás |
|---|---|---|
| id | BIGINT | Auto increment |
| user_id | BIGINT | Felhasználó ID |
| post_id | BIGINT | Hirdetés/Aukció ID |
| created_at | DATETIME | Hozzáadás időpontja |

---

## Hirdetés meta mezők

| Meta kulcs | Leírás |
|---|---|
| va_price | Ár (Ft) |
| va_price_type | fixed / negotiable / free / on_request |
| va_brand | Márka |
| va_model | Modell |
| va_caliber | Kaliber |
| va_year | Gyártási év |
| va_phone | Telefonszám |
| va_location | Helyszín (város) |
| va_expires | Lejárat dátuma |
| va_featured | Kiemelt (1/0) |
| va_verified | Ellenőrzött (1/0) |
| va_views | Megtekintések száma |
| va_license_req | Fegyverengedély kell (1/0) |

## Aukció extra meta mezők

| Meta kulcs | Leírás |
|---|---|
| va_start_price | Kikiáltási ár |
| va_min_bid_step | Min. licitlépés |
| va_buyout_price | Azonnali vásárlás ár |
| va_auction_end | Aukció vége (datetime) |
| va_current_bid | Aktuális licit (auto) |
| va_bid_count | Licitek száma (auto) |
| va_auction_winner | Nyertes user ID (auto) |

---

## TODO – Következő fejlesztések

- [ ] Csomag/díjszabás rendszer (ingyenes / prémium hirdetés)
- [ ] WooCommerce integráció (online fizetés kiemelésért)
- [ ] Üzenetküldő rendszer (hirdetők közt)
- [ ] Értesítési rendszer (e-mail + esetleg push)
- [ ] Moderátor workflow (bulk jóváhagyás)
- [ ] Export (CSV) az adminban
- [ ] API endpoint-ok (mobilapp-hoz)
- [ ] Képgaléria lightbox (JS)
- [ ] Auto lejárat értesítő e-mail
- [ ] Google Maps integráció (helyszín térkép)
- [ ] Schema.org structured data (SEO)
