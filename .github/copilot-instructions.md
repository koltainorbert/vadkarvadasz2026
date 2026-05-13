# Copilot fejlesztesi szabalyok
## Naplo
- Minden session vegen frissitsd a NAPLO.md fajlt
- Session elejen olvasd el az utolso bejegyzest

## Design
- Szoveg: color:#fff
- Accent: #ff0000
- Hatter: rgb(6,6,6) + dot grid

## KRITIKUS FEJLESZTÉSI SZABÁLYOK — MINDIG TARTSD BE

### 1. CPT / Taxonomy slug változás után → KÖTELEZŐ
Növeld meg a `VA_REWRITE_VER` konstanst a `vadaszapro-core.php`-ban:
```php
define( 'VA_REWRITE_VER', '1.0.3' ); // volt 1.0.2 → növeld eggyel
```
Ez automatikusan leflusholja a rewrite szabályokat mindenkinél. SOHA ne kérd manuálisan az admint!

### 2. posts_per_page => -1 TILOS
2-3 millió hirdetésnél szerver crash. Mindig:
- `posts_per_page` max 100 legyen
- `no_found_rows => true` ha szám nem kell
- Iteráció kell? → do-while batch 100-asával

### 3. meta_query ár/szűrésre TILOS
A `wp_postmeta` EAV tábla 2M hirdetés × 10 meta = 20M sor. Lassú.
Helyette: `wp_va_listing_meta` custom tábla (van index: price, category_id, county_id).
Mentéskor a `va_sync_listing_meta($post_id)` szinkronizálja automatikusan (`save_post_va_listing` hook).

### 4. Aukció lejárat
- Cron 5 percenként fut (`va_every_5min` schedule)
- Az AJAX `va_get_bid_status` is azonnal lezárja ha lejárt (cron nélkül is)
- Lezárt aukció flag: `va_auction_closed` meta = '1'

### 5. Deploy parancs (ELE̋S)
**Ajanlott mod:** VS Code task → `Deploy All`

Ez a projekt kizárólag éles deploy-ban fut:
- `Deploy All` = `git add` + automatikus commit + `git push`
- push után a `.github/workflows/deploy.yml` FTP workflow tölti fel a változásokat a `vadkarvadasz.hu` oldalra

Nincs LocalWP, nincs gépfüggő local path, nincs `local-config.ps1`.

### 6. Adatbázis táblák
| Tábla | Mire való |
|---|---|
| `wp_va_bids` | Aukció licitek |
| `wp_va_watchlist` | Kedvencek (user↔post) |
| `wp_va_listing_meta` | Gyors szűrés (ár, megye, kategória) – indexelt! |

### 7. URL struktúra
| URL | Template |
|---|---|
| `hirdetes/{slug}/` | `single-va_listing.php` |
| `hirdetes/` | `archive.php` |
| `aukcio/{slug}/` | `single-va_auction.php` |
| `kategoria/{slug}/` | `taxonomy-va_category.php` |
