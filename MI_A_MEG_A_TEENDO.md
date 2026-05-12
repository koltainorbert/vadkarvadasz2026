# Mi a még a teendő

Ez a projekt "szentgrál" státuszlista. Cél: reggel / induláskor ebből dolgozni.

> **2026.05.13. — Más gépen folytatás: először `git pull`, aztán Deploy All!**

## Kész (pirossal áthúzva)

- <span style="color:#ff0000;"><s>Core plugin + theme architektúra felépítve</s></span>
- <span style="color:#ff0000;"><s>Jármű kategória adatmodell (VA_Vehicle_Catalog) bevezetve</s></span>
- <span style="color:#ff0000;"><s>Kategória szinkron dataset verzióval megoldva</s></span>
- <span style="color:#ff0000;"><s>Kereső alap + részletes szűrők nagyrészt implementálva</s></span>
- <span style="color:#ff0000;"><s>Ár szerinti rendezés viselkedése javítva</s></span>
- <span style="color:#ff0000;"><s>Submit form jelentősen bővítve jármű mezőkkel</s></span>
- <span style="color:#ff0000;"><s>Single listing részletek kártya és scroll UX finomítva</s></span>
- <span style="color:#ff0000;"><s>Deploy flow működik (Deploy Plugin/Theme/All)</s></span>
- <span style="color:#ff0000;"><s>Session naplózás rendszeresen vezetve</s></span>
- <span style="color:#ff0000;"><s>Fresh install fallback ellenőrizve (factory defaults guard)</s></span>
- <span style="color:#ff0000;"><s>Vadász schema rákényszerítve éles site-ra (host-aware migration v6)</s></span>
- <span style="color:#ff0000;"><s>Submit form UI facelift – kártyás design, dot-grid háttér</s></span>
- <span style="color:#ff0000;"><s>Vadász márka/típus adatbázis nagy bővítés (13 kategória, 150+ márka)</s></span>
- <span style="color:#ff0000;"><s>Kaliber autocomplete (~170 kaliber, datalist HTML, hunting-calibers.json)</s></span>

## Következő feladat (holnap reggel innen!)

### 1. LEGFONTOSABB: Admin szerkesztő kaliber autocomplete
- Fájl: `admin/class-form-builder.php` + `admin/class-listing-edit.php` + mirroraik
- A `caliber` mező az admin edit oldalon még sima `<input type="text">`
- Meg kell kapnia a `list="va-caliber-list"` + datalist bekötést ugyanúgy mint a submit formon

### 2. Kaliber szűrő a frontend keresőben
- A kereső oldal szűrői között lehetne kaliber checkbox/select
- Ehhez a `wp_va_listing_meta` táblában már tárolódik a kaliber érték

### 3. Smoke teszt éles oldalon
- Vadász hirdetés feladása végig (minden mező ment, kaliber autocomplete működik?)
- Hirdetés szerkesztése admin oldalon

## Még teendő (100% profi / ThemeForest szint)

4. Teljes clean-install smoke teszt
5. QA mátrix (desktop/mobil/tablet + böngészők)
6. Security hardening audit (nonce/capability, sanitization, escaping)
7. Performance és CWV csomag (CSS/JS minify, LCP mérés)
8. ThemeForest kompatibilitási csomag (plugin/theme szétválasztás, child theme, demo import)
9. Dokumentáció (telepítés, beállítás, hibaelhárítás)
10. Jogi/licenc megfelelés

## Aktuális státusz (2026.05.13)

- Készültség: kb. **75-80%** termékérettség
- Vadász schema: **ÉLES OLDALON AKTÍV** ✓
- Kaliber autocomplete: **MEGVAN** ✓
- Nagy márka/típus DB: **MEGVAN** ✓
- ThemeForest beadási készültség: közepes, még compliance fókusz kell

## Napi használat (induláskori rutin)

1. **Más gépen: `git pull` ELŐSZÖR!**
2. Nyisd meg ezt a fájlt: `MI_A_MEG_A_TEENDO.md`
3. Ellenőrizd az "Következő feladat" pontokból az aznapi fókuszt
4. Amit elkészítettél, tedd át a "Kész" blokkba piros áthúzással
5. Session végén frissítsd a `NAPLO.md` fájlt a konkrét változásokkal
