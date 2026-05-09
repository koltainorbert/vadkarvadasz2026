# Fejlesztesi Naplo

---

## 2026. 05. 09. – Session #287 (Globalis Firefox scrollbar erosites)

### Mit csinaltunk [x]
- [x] A globalis frontend scrollbar Firefox szabalyai erosítve lettek a plugin frontend CSS-ben
- [x] A tema globalis `style.css` is kapott Firefox-kompatibilis vastagabb scrollbar beallitast
- [x] A teljes oldalscroll `thin` helyett `auto` lett, erosebb piros thumbbal es lathato trackkel
- [x] WebKit oldalon is vastagabb, kontrasztosabb globalis scrollbar kerult be, hogy minden frontend felulet egyseges legyen

### Eredmeny
- Firefoxban a teljes oldal scrollbarja mar nem hajszalvekony, halvany csikra van allitva
- A modositas nem csak a dashboard / shortcode feluleteket, hanem a theme altal renderelt sima oldalakat is lefedi

---

## 2026. 05. 09. – Session #286 (Firefox scrollbar: mindig lathato allapot)

### Mit csinaltunk [x]
- [x] Az OverlayScrollbars `autoHide` modja `move` helyett `never` lett root + plugin mirror JS-ben
- [x] Az admin sidebar es a dashboard bal nav scrollbar alapallapota erosítve lett
- [x] Vastagabb scrollbar (`12px`), lathato track es erosebb piros thumb beallitva
- [x] Firefox hover nelkuli allapot javitva, hogy ne csak ravitt egernel nezzen ki jol

### Eredmeny
- Firefoxban a scrollbar mar alapallapotban is lathato marad
- A hover csak erositi a megjelenest, nem attol valik hasznalhatova

---

## 2026. 05. 09. – Session #285 (Firefox scrollbar UX fix)

### Mit csinaltunk [x]
- [x] Firefox scrollbar javitas admin oldali bal savban (`#va-sidebar`)
- [x] Firefox scrollbar javitas frontend dashboard bal navban (`.va-dashboard__nav`)
- [x] Alap + hover allapotok kontrasztja emelve, vastagabb/olvashatobb thumb megjelenes
- [x] Mirror szinkron root + plugin mappaban

### Eredmeny
- Firefoxban a scrollbar mar nem vékony, csunya csiknak tunik
- Hover allapot vizualisan kozel azonos lett Chromium es Firefox kozott

### Utolagos finomitas
- Hover nelkuli allapot tovabberositve: magasabb kontraszt, 8px vastagsag, lathato track
- Firefox specifikus felulirasok erosítve (`scrollbar-width/scrollbar-color !important`) a biztos alap megjelenesert

---

## 2026. 05. 09. – Session #284 (SEO UI cleanup: Google kapcsolo eltavolitasa)

### Mit csinaltunk [x]
- [x] A megtévesztő `Google Indexing API` kapcsoló eltávolítva a SEO admin felületről
- [x] A Google teszt gomb ismét egyértelmű, közvetlen működésre visszaállítva
- [x] Kísérő szövegek visszaállítva kapcsoló nélküli, tiszta megfogalmazásra
- [x] Mirror szinkron root + plugin mappában

### Eredmeny
- A SEO panel egyszerűbb és félreértésmentes
- Nincs több megtévesztő kapcsolóállapot a Google blokkban

---

## 2026. 05. 09. – Session #283 (Google Indexing API fallback: opcionális mód)

### Mit csinaltunk [x]
- [x] Uj SEO kapcsolo: `Google Indexing API` bekapcsolas/kikapcsolas
- [x] Alapertelmezett fallback logika: ha ki van kapcsolva, a Google hivasok nem futnak
- [x] `trigger_listing_discovery_boost()` mar csak bekapcsolt Google módnal kuld Indexing API hívast
- [x] Teszt endpoint is figyelembe veszi a kapcsolot, kikapcsolt modban nem jelez piros hibát
- [x] SEO oldalon egyertelmu UX szoveg: Google opcionális, IndexNow+sitemap ettol fuggetlenul megy
- [x] Mirror szinkron root + plugin mappaban

### Eredmeny
- A Search Console service account jogosítási blokkolás mar nem akadályozza az oldal SEO folyamatait
- Az automatikus IndexNow + sitemap alapu indexeles stabilan aktiv marad

---

## 2026. 05. 09. – Session #282 (SEO admin UX hotfix: teszt eredmeny lathatosaga)

### Mit csinaltunk [x]
- [x] A Google Indexing API teszt eredmenye mar nem csak felul notice-kent jelenik meg
- [x] Uj inline visszajelzes kerult a `Google kapcsolat teszt` gomb ala (siker/hiba szinnel)
- [x] Mirror szinkron root + plugin mappaban

### Eredmeny
- A teszt gomb kattintas utan azonnal ugyanott latszik a valasz, nem tunik ugy, mintha csak frissult volna az oldal

---

## 2026. 05. 09. – Session #281 (Google Indexing API admin teszt + diagnosztika)

### Mit csinaltunk [x]
- [x] SEO admin oldalon uj teszt funkcio: `Google kapcsolat teszt` gomb
- [x] Beallithato teszt URL mező (alapertelmezett: nyitolap)
- [x] Azonnali statusz visszajelzes a feluleten (siker / hiba)
- [x] Service Account email megjelenitese a mentett JSON-bol
- [x] `VA_User_System::gsc_indexing_test()` uj metodus: token kerest es URL_UPDATED submitot is blokkoltan tesztel, reszletes hibaokkal
- [x] Mirror szinkron root + plugin mappaban

### Eredmeny
- A Google Indexing API bekotes mar nem "fekete doboz": adminbol 1 kattintasos ellenorzes elerheto
- Hibak es auth problemak konkrét uzenetben latszanak a SEO beallitas oldalon

### Kovetkezo teendo
- Google Cloud oldali szolgaltatasfiok/hozzaferes beallitasa utan a teszt gombbal ellenorizni a kapcsolatot

---

## 2026. 05. 09. – Session #280 (sitemap index 404 hotfix: rewrite sorrend javitas)

### Mit csinaltunk [x]
- [x] Feltaras: a `sitemap-tax-*` es `sitemap-landing-*` URL-ek 404-et adtak, mert a tul altalanos `^sitemap-...` rewrite minta elkapta oket
- [x] Javitas: `register_sitemap_routes()` sorrend cserelve, a specifikus mintak (`sitemap-tax`, `sitemap-landing`) most a generic minta elott regisztralodnak
- [x] `VA_REWRITE_VER` novelve `1.0.9` -> `1.0.10`, hogy mindenkinel automatikus rewrite flush tortenjen
- [x] Mirror szinkron root + plugin mappaban

### Eredmeny
- A `sitemap.xml` tovabbra is valid XML
- A tax es landing sitemap endpointok a rewrite frissules utan mar nem fognak a post-type handlerre esni

### Kovetkezo teendo
- Commit + deploy utan live ellenorzes: minden `loc` URL `200` legyen a sitemap indexben

---

## 2026. 05. 09. – Session #279 (teljes SEO hardening: sitemap fix, IndexNow, GSC API, schema bővítés)

### Mit csinaltunk [x]
- [x] **robots.txt statikus fájl** → `wp-root/robots.txt` FTP-n deployolva a web root-ba, egyetlen `sitemap.xml` sor
- [x] **Cloudflare Page Rule** → `robots.txt` cache bypass beállítva a CF dashboardban
- [x] **CF auto-purge** → deploy workflow bővítve `purge-cloudflare-cache` jobbal (robots.txt + sitemap.xml + sitemap_index.xml)
- [x] **CF credentials** → `CF_API_TOKEN` + `CF_ZONE_ID` GitHub secrets hozzáadva
- [x] **IndexNow API** → `class-user-system.php` új hirdetés publikálásakor elküldi az URL-t az `api.indexnow.org`-nak (Bing + Yandex azonnali indexelés)
- [x] **Google GSC Indexing API** → JWT/OAuth2-alapú implementation, service account JSON-t WP optionból olvassa (`va_gsc_service_account`)
- [x] **SEO admin panel** → `render_seo()` valódi tartalommal: GSC service account JSON textarea + IndexNow státusz kártya
- [x] **Car schema bővítés** → `vehicleEngine` (hengerűrtartalom), `numberOfDoors`, `driveWheelConfiguration`, `vehicleIdentificationNumber`, `itemCondition: UsedCondition`, `seller: AutoDealer` mind a Car és az Offer objectben
- [x] **do_robots action** → priority 1-en átvesszük a kontrollt (Rank Math kiiktatása)
- [x] Mirror szinkron root + plugin mappában minden fájlban

### Eredmény
- robots.txt: egyetlen `Sitemap: /sitemap.xml` sor (live)
- IndexNow: aktív, kulcs live: `b873c945f60ea2d1bc78a3f254901e6d.txt`
- GSC API: kód kész, service account JSON-t az admin SEO fülén lehet beállítani
- Car schema: jelentősen bővebb (motor, ajtók, hajtás, VIN, állapot, eladó)

### Következő teendő
- GSC service account létrehozása Google Cloud-ban → JSON feltöltése az admin SEO fülén

---

### Mit csinaltunk [x]
- [x] `class-seo.php` robots szuroben sitemap canonicalizalas (duplikalt `Sitemap:` sorok kiszurese, egyetlen `/sitemap.xml` sor)
- [x] Szuro prioritas emelve (`robots_txt` priority 999), hogy a vegso kimenetet tisztitsa
- [x] Mirror szinkron root + plugin mappaban
- [x] Deploy + push sikeres

### Megfigyeles
Az eles `robots.txt` tovabbra is 2 sitemap sort mutat (`/sitemap_index.xml` + `/sitemap.xml`), valoszinuleg edge/CDN vagy kulso SEO reteg miatt. A WP kod oldalon a canonicalizalas aktiv, de a vegso kimenetet egy kulso reteg felulirhatja.

---

## 2026. 05. 09. – Session #277 (SEO admin menupont placeholder)

### Mit csinaltunk [x]
- [x] Uj `SEO` admin menupont/almenu hozzaadva a `Vadaszapro` admin ala (`vadaszapro-seo`)
- [x] Uj oldalsav elem hozzaadva: `📈 SEO`
- [x] Uj placeholder oldal letrehozva a settings oldalon: `render_seo()`
- [x] Mirror szinkron frissitve root + plugin mappaban

### Eredmeny
Az adminban mar elerheto a SEO ful: `wp-admin/admin.php?page=vadaszapro-seo`.
Jelenleg ez egy ures/elokeszitett oldal, a reszletes SEO kapcsolok kovetkezo korben adhatok hozza.

---

## 2026. 05. 09. – Session #276 (deploy atallas production-only modra)

### Mit csinaltunk [x]
- [x] A LocalWP-fuggo deploy scriptek atallitva production-first mukodesre
- [x] Erintett fajlok: `.vscode/deploy.ps1`, `.vscode/deploy-plugin.ps1`, `.vscode/deploy-theme.ps1`
- [x] `Deploy All` task tesztelve LocalWP nelkul: nem hibazik, sikeresen kilep es jelzi a push+GitHub Actions FTP deploy flow-t

### Eredmeny
Nincs tobbe LocalWP hard dependency. Az eles deploy folyamat egyertelmu: `Push` a `main` branch-re, majd a GitHub workflow (`.github/workflows/deploy.yml`) intezi az FTP feltoltest.

---

## 2026. 05. 09. – Session #275 (merge utani szeteses audit + mirror szinkron hotfix)

### Mit csinaltunk [x]
- [x] Teljes gyorsaudit: git allapot, VS Code error panel, friss commitok atnezese
- [x] Ellenorzes: Google talalat elonezet fajl megvan (`assets/demo/google-preview.html`)
- [x] Feltaras: root es plugin mirror kozott 5 kritikus elteres volt
- [x] Root oldalon PHP7-kompatibilis visszaszinkron a stabil mirror verziokra
- [x] Eltetesek megszuntetve (`MIRROR_OK`), hibakontroll rendben
- [x] Auto commit rogzitve: `440cdff`

### Eredmeny
A merge utani szeteses egyik fo kockazata (root vs deploy mirror elteres, PHP8-only elemekkel) megszunt. A source es plugin mirror jelenleg ujra szinkronban van.

---

## 2026. 05. 09. – Session #274 (production hotfix: CSS elrejtes plugin hiba mellett is)

### Mit csinaltunk [x]
- [x] A keresooldali SEO landing blokk theme CSS szinten is elrejtve (`.va-search-landing { display:none !important; }`)
- [x] Modositott fajlok: `style.css` + `wp-theme/vadaszapro-theme/style.css`
- [x] Deploy All lefutott
- [x] Push lefutott (`a46998a`)

### Eredmeny
A blokk akkor sem latszik a frontenden, ha a plugin deploy job hibazik, mert a theme oldali CSS felulirja a megjelenitest.

---

## 2026. 05. 09. – Session #273 (keresooldali SEO blokk elrejtese)

### Mit csinaltunk [x]
- [x] A `va-hirdetes-kereses` oldalon a landing/SEO blokk vizualisan elrejtve, hogy a hirdetes lista azonnal latszodjon
- [x] A blokk kodja bent maradt a templateben, de `display:none` + `aria-hidden` allapotban van
- [x] Source + plugin mirror szinkronban
- [x] Hibakontroll rendben, Deploy All sikeres

### Eredmeny
A keresooldal tetejen a latogato mar azonnal a hirdetesekkel es szurokkel talalkozik, a korabbi nagy bevezeto blokk nem tolja le az elso talalatokat.

---

## 2026. 05. 08. – Session #272 (checkpoint holnapi folytatáshoz)

### Hol tartunk most
- Exact query SEO erosites kesz a hirdetesoldalakon (title + meta elejen a pontos hirdetescim)
- Duplikalt OG/meta kimenet megszuntetve a single templatekben (`VA_SEO` aktiv esetben legacy blokk nem fut)
- Landing SEO rendszer aktiv (brand/model landing context, belso linkeles, landing sitemap)
- Source + mirrorok szinkronban, Deploy All lefutott

### Holnap innen folytassuk (prioritas)
1. Elo URL-ellenorzes a konkret hirdetesre: index/noindex, canonical, title, meta, schema
2. Search Console URL Inspection + ujraindexeles (a pontos hirdetes URL-re)
3. Search Console sitemap ujrakuldes + indexelési státusz követés
4. Ha 7-14 napon belul nincs javulas exact queryre, tovabbi erosites: ugyanarra a konkret modellre plusz belso linkek a kapcsolodo oldalakrol

### Fontos megjegyzes
A jelenlegi elso helyes konkurens (hasznaltauto.hu) domain ereje joval nagyobb, ezert a kodszintu SEO javitas utan is kell indexelesi ido es kulso jel (Search Console + linkjel) a poziciohoz.

---

## 2026. 05. 08. – Session #271 (exact query SEO erosites: egyedi hirdetesnev)

### Mit csinaltunk [x]
- [x] A hirdetes oldali browser title elejere az exact hirdetes cim kerul (nem csak normalizalt brand/model)
- [x] A hirdetes meta description elejere az exact hirdetes cim kerul
- [x] A single template legacy OG/meta blokk csak akkor fut, ha a kozponti `VA_SEO` nincs aktivan betoltve (duplikalt meta konfliktus megszuntetve)
- [x] Source + plugin/theme mirror szinkronban
- [x] Hibakontroll rendben, Deploy All sikeres

### Eredmeny
Az egyedi hirdetes URL-ek pontos keresokifejezesre jobban optimalizalt title/meta jelet adnak, es megszunt a duplikalt meta kimenetbol adodo bizonytalansag.

---

## 2026. 05. 08. – Session #270 (célzott márka/modell SEO variációk)

### Mit csinaltunk [x]
- [x] A központi SEO landing context márka- és modellspecifikus variációs réteget kapott
- [x] Kiemelt márkákhoz (BMW, Mercedes-Benz, Audi, Volkswagen, Toyota) egyedi SEO heading + szöveg + bullet készlet került
- [x] Kiemelt modellekhez (BMW X5, VW Golf, Audi A4, Toyota Corolla) egyedi SEO szövegvariáció került
- [x] A főoldali meta description pénzesebb kulcsszavas hangsúlyt kapott (eladó használt autók és motorok)
- [x] Source + plugin mirror szinkronban
- [x] Hibakontroll rendben, Deploy All sikeres

### Eredmeny
A landing oldalak tartalma most már nem általános sablonszöveg, hanem márka/modell intenthez illeszkedő SEO tartalom, ami jobb relevanciát ad a fő keresési szándékokra.

---

## 2026. 05. 08. – Session #269 (fooldali SEO + brand/model landing szovegblokkok)

### Mit csinaltunk [x]
- [x] A fooldal kulon SEO title/social title es meta description logikat kapott a kozponti SEO retegben
- [x] A brand/model landing oldalak szovegezese kozponti helperbe kerult, igy a title/meta/social + lathato oldalblokk ugyanabbol a logikabol epul
- [x] A keresooldal landing resze kapott lathato H2 + SEO szovegblokk + tamogato bulletpontokat
- [x] Source + plugin mirror frissitve
- [x] Hibakontroll rendben, Deploy All sikeres

### Eredmeny
A landing oldalak mar nem csak technikai SEO-s URL-ek, hanem sajat, relevans szoveges tartalmat is kapnak. Ez javitja az indexelhetoseget es a keresesi szandekra adott oldalminoseget.

---

## 2026. 05. 08. – Session #268 (SEO landingek + belso linkeles + landing sitemap)

### Mit csinaltunk [x]
- [x] A keresooldal brand/model query alapon SEO-landingkent is mukodik (`brand`, `model`)
- [x] A keresooldal kapott landing intro blokkot es crawlolhato marka/model linkeket
- [x] A frontend szuro JS automatikusan elotolti a `brand` / `model` landing parametereket
- [x] A hirdetes reszletes oldalak uj belso SEO linkblokkot kaptak (marka / modell / uzemanyag / hely)
- [x] Uj landing sitemap kerult be: brand- es modellalapu landing URL-ek bekerulnek a sitemap indexbe
- [x] Cache-bust + rewrite flush: `VA_VERSION` 1.1.4 -> 1.1.5, `VA_REWRITE_VER` 1.0.7 -> 1.0.8
- [x] Source + plugin/theme mirrorok szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Az SEO reteg mar nem csak meta szinten erosodott, hanem strukturaban is: uj hirdetesek automatikusan erositik a marka/model landingeket, a sitemapet es a belso linkhalot.

---

## 2026. 05. 08. – Session #267 (SEO erosites: autohirdetes title/meta/schema)

### Mit csinaltunk [x]
- [x] A jarmu hirdetesoldalak sajat browser title logikat kaptak (`document_title_parts`)
- [x] Uj, auto-specifikus SEO title sablon: marka + modell + karosszeria + evjarat
- [x] Uj meta description sablon a jarmu metaadatokbol: ar, evjarat, km, uzemanyag, valto, lokacio
- [x] Az archiv es taxonomy leirasok is erositve lettek keszlet-/ajanlatfokuszu szovegekre
- [x] A `Product` schema gazdagitva: `brand`, `model`, `releaseDate`, `mileageFromOdometer`, `fuelType`, `vehicleTransmission`, `bodyType`, `color`
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A SEO reteg mar kozelebb kerult a Hasznaltauto jellegu autoportal mintahoz: erosebb title-ek, jobban strukturalt descriptionok es reszletesebb product schema adatmezo-k kerulnek ki.

---

## 2026. 05. 08. – Session #266 (SEO tisztitas: vadasz kulcsszavak kivezetese)

### Mit csinaltunk [x]
- [x] A `class-seo.php` (source + plugin mirror) SEO leirasai atirva auto/motor fokuszra
- [x] Rank Math hookok bovitve: `rank_math/frontend/title` es `rank_math/frontend/description`
- [x] Beepitett SEO szovegtisztitas (`sanitize_seo_copy`) a vadasz temaju kulcsszavak kivezetesere
- [x] Singular fallback javitas: shortcode-ok (`[va_buy_credits]`) kiszurese a meta description szovegbol
- [x] Deploy All sikeres (LocalWP cel)

### Hol tartunk
Kodszinten a SEO szovegek auto/motor iranyra alltak at, de a publikus domain csak akkor mutatja ezt, ha a termelesi szerverre is kikerulnek a valtozasok es Rank Math cache ujraepul.

---

## 2026. 05. 08. – Session #265 (Pill meretfix cache-bust + osztalyparitas)

### Mit csinaltunk [x]
- [x] A szem/Geotech elemek megkaptak a `va-le-pill` osztalyt is, hogy garantaltan az `Aktiv` pill meretstilusat orokoljek
- [x] A `va-le-geo-pill` line-height `normal`-ra allitva a pontosabb meretparitashoz
- [x] Cache-bust: `VA_VERSION` emelve `1.1.3` -> `1.1.4` (source + plugin mirror)
- [x] Deploy All sikeres

### Hol tartunk
A szem es Geotech pill meret most mar strukturailag es cache-szinten is az Aktiv pillhez van kotve.

---

## 2026. 05. 08. – Session #264 (Szem/Geotech pill meretparitas az Aktivval)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` oldalon a szem + Geotech pill merete az `Aktiv` pill meretere visszaallitva
- [x] Egyeztetett ertekek: `font-size: 10px`, `padding: 3px 9px`, normalizalt `gap`, nagyobb ikonmeret
- [x] Source + plugin mirror (`admin/admin.css`) szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A szem es Geotech pill most mar az Aktiv statusz pilllel azonos vizualis meretben jelenik meg.

---

## 2026. 05. 08. – Session #263 (Admin Geo felirat atnevezes Geotech-re)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` oldalon a `Geo` gombfelirat `Geotech`-re cserelve
- [x] A kapcsolodo tooltip/ARIA szoveg is frissitve (`Geotech riport`)
- [x] Source + plugin mirror (`admin/class-listing-edit.php`) szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Az admin hirdeteslistaban a korabbi `Geo` felirat mostantol `Geotech`-kent jelenik meg.

---

## 2026. 05. 08. – Session #262 (Admin szem/Geo pill ujabb 50% csokkentes)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` admin oldalon a szem/Geo pill meretek ujra 50%-kal csokkentve
- [x] Felezett ertekek: `height: 5px`, `font-size: 4px`, ikon `4px`, kisebb `gap` es kisebb `padding`
- [x] Source + plugin mirror (`admin/admin.css`) szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A szem es Geo pill a listaban most tovabbi kb. 50%-kal kisebb meretben jelenik meg.

---

## 2026. 05. 08. – Session #261 (Teszt szinek visszaallitasa)

### Mit csinaltunk [x]
- [x] A frontend teszt miatt beallitott feher hatter visszaallitva (`background: var(--bg)`) a tema `style.css` fajljaban
- [x] Az admin oldal hattere megerositve eredeti temavaltozora (`background: var(--va-bg)`)
- [x] A teszt kozben letrejott felesleges `frontend/css/style.css` fajl torolve

### Hol tartunk
Az oldal szinvilaga visszaallt az eredeti sotet designra, teszt override nem maradt bent.

---

## 2026. 05. 08. – Session #260 (Adminban szem/Geo pill 50% meret)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` oldalon a szem/Geo pill kb. 50%-kal kisebbre allitva
- [x] Csokkentett ertekek: `height: 10px`, `font-size: 8px`, kisebb ikon es kisebb horizontalis padding
- [x] Soron beluli spacing is felezve (`gap`)
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres
- [x] Push kesz (`main`)

### Hol tartunk
+A szem es Geo pill az admin listaban az admin panelen korekten atmegyeit  50% kisebb meretre allitva.
- **DEPLOYMENT ISSUE:** Prod szerver (weingartnerauto.hu) 0% valtozas - GitHub es LocalWP deploy sikeres, de live URL nem frissul. Deploy infrastruktura tisztazas szukseges.

---

## 2026. 05. 08. – Session #259 (Szem/Geo pill fix magassag az Aktivhoz)

### Mit csinaltunk [x]
- [x] A szem/Geo kapszula fix magassagra allitva (`height: 20px`), hogy az Aktiv badge meretere alljon
- [x] Belső padding atallitva (`0 9px`) a badge-jelleghez
- [x] Kulso glow eltavolitva (csak belso kontur maradt), hogy vizualis meretben se legyen nagyobb
- [x] Source + plugin mirror szinkronizalva

### Hol tartunk
A szem es Geo kapszula most fix magassaggal fut, az Aktiv badge magassagaval egyezo celmeretre allitva.

---

## 2026. 05. 08. – Session #258 (Geo/View pill meret az Aktiv badge-hez)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` oldalon a szem/Geo kapszulak merete az `Aktív` badge-hez igazitva
- [x] `font-size`, `font-weight`, `padding` es ikonmeret visszaallitva badge-meretre
- [x] A korabbi nagy `min-width` ertekek eltavolitva, hogy ne legyen tul nagy a kapszula
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A szem es Geo kapszula most mar magassagban az Aktiv badge-hez illeszkedik, nem nagyobb "gomb" meretben jelenik meg.

---

## 2026. 05. 08. – Session #257 (Prod irany + asset cache bust)

### Mit csinaltunk [x]
- [x] Tisztazva: a `Deploy All` LocalWP celutvonalra masol, nem publikus szerverre
- [x] Asset cache-bust miatt `VA_VERSION` emelve `1.1.2` -> `1.1.3`
- [x] Verzioemeles mind source, mind mirror plugin fajlban megtortent

### Hol tartunk
Az uj CSS/JS csak ott latszik azonnal, ahol a friss plugin kod tenylegesen telepitve van; cache-bust verzio mar felkeszitve.

---

## 2026. 05. 08. – Session #256 (Geo kapszula felülírás fix a listában)

### Mit csinaltunk [x]
- [x] Azonositva: globális `body.va-admin-page a { ... !important }` szabaly felulirta a kapszula link stilust
- [x] Célzott felülírás bekerült: `body.va-admin-page .va-le-geo-wrap a.va-le-geo-pill` (color + text-decoration `!important`)
- [x] Hover/focus/active állapotokra is fixen kapszula stilus marad
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A `vadaszapro-listings` oldalon a szem/Geo elem mar nem tud visszaesni narancs, aláhúzott sima link megjelenésre.

---

## 2026. 05. 08. – Session #255 (Szem + Geo kapszula pixelpontos stilus)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` nezet/Geo kapszulak stilusa a kuldott referenciahoz igazítva
- [x] Eroteljes piros korvonal + sotet belso felulet + finom glow effekt beallitva
- [x] Kapszula meretek, spacing, ikon meret/stroke es tipografia atallitva a referencia jellegre
- [x] Anchor trigger hover/focus allapotok pontositva
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A szem (`305`) es `Geo` kapszula megjelenese most a kuldott mintahoz van igazítva: eros kontur, sotet belso felulet, kozepre igazított ikon+felirat.

---

## 2026. 05. 08. – Session #254 (Admin popup 1:1 Fiók stilus + CSV/Nyomtatas parity)

### Mit csinaltunk [x]
- [x] Az admin geo popup JS markupja/osztalyai 1:1 frontend (Fiók) modal szerkezetre allitva (`va-geo-modal*`)
- [x] A gombok (`Letoltes CSV`, `Nyomtatas`, `Bezar`) ugyanazzal a strukturaval es viselkedessel futnak
- [x] A tabla rendereles `va-geo-modal__table` osztalyra valtva (widefat helyett), mint a Fiók oldalon
- [x] A modal panel kozepre igazitva (`top/left 50% + transform`)
- [x] A kapszula linkek hover/focus stilusa javitva (anchorra is)
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Az admin popup most mar ugyanazt a modal strukturat es stilusnyelvet hasznalja, mint a `va-fiok` oldali Geo riport, beleertve a CSV export es nyomtatas gombok mukodeset.

---

## 2026. 05. 08. – Session #253 (Fallback geo riport oldal teljes redesign)

### Mit csinaltunk [x]
- [x] Az admin fallback geo riport oldal (`admin.php?action=va_view_geo_report`) teljes vizualis ujraepitese
- [x] A korabbi feher/szurke tablazat helyett a popup stilusaval egyezo sotet-piros panel kerult be
- [x] Dot-grid hatter, eros kontraszt, kapszula jellegu vissza gomb, modern tabla tipografia
- [x] Mobilon torodelo fejléc + scrollozhato tabla megtartva
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Ha a JS popup valamiert nem fut es fallback oldal nyilik, az most mar ugyanabban a vizualis nyelvben jelenik meg, mint a modal (nem a regi alap admin kinezetben).

---

## 2026. 05. 08. – Session #252 (Admin popup biztos fallback + Fiók-stilus kapszula)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` egyedi admin tablaban a szem es Geo trigger `button` helyett `a` link lett, nonce-os riport URL fallbackkel
- [x] Ha JS mukodik: ugyanugy popup nyilik (`.va-geo-report-trigger` intercept)
- [x] Ha JS nem fut: a link biztosan megnyitja a riportot (nem marad "kattintok es semmi")
- [x] A szem kapszula emoji helyett SVG szemet kapott, vizualisan kozelebb kerult a Fiók oldalhoz
- [x] `admin.css` finomitas: kapszulaknal `text-decoration: none` a tiszta, gombszeru megjeleneshez
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A popup nyitas most fail-safe: JS mellett modal, JS hiba eseten URL fallback riport. A nezetseg/Geo kapszula megjelenes kozelebb van a Fiók oldali mintahoz.

---

## 2026. 05. 08. – Session #251 (Admin lista popup fix + design tisztitas)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` oldalon a Geo popup nyitas robusztusitva: explicit `admin-ajax.php` endpoint + inline fallback hivas
- [x] A szem badge es Geo gomb uj, letisztultabb kapszula dizajnt kapott (kevesebb glow, tisztabb allapotok)
- [x] A popup hivas globalisan is elerheto (`window.vaOpenAdminGeoModal`) az egyedi listahoz
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Az admin hirdeteslistan a Geo popup nyitas most mar megbizhato, es a nezetseg oszlop vizualisan rendezettebb.

---

## 2026. 05. 08. – Session #250 (Admin listaban a szem is popupot nyit)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` oldalon a szem badge kattinthato lett, ugyanugy geo popupot nyit mint a `Geo` gomb
- [x] A kulon `Geo` gomb megmaradt
- [x] Nem-adminnal csak vizualis nezettség badge marad
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Admin oldalon mar barmelyik badge-re (szem vagy Geo) kattintva nyilik a geo riport popup.

---

## 2026. 05. 08. – Session #249 (Admin hirdeteslista: Fiók-stilu Geo gomb)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` egyedi admin tablaban a nezetseg oszlop ujraepitve Fiók-stilusra
- [x] Kulon szem badge + kulon `Geo` gomb (azonos kapszula dizajn)
- [x] A popupot most mar egyertelmuen a `Geo` gomb nyitja
- [x] Admin-only megjelenites megtartva
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Az admin listaban most mar ugyanaz a vizualis es mukodesi minta van, mint a sajat fiokban: szem badge kulon, kattinthato Geo gomb kulon.

---

## 2026. 05. 08. – Session #248 (GPS koordinatabol helysegnev)

### Mit csinaltunk [x]
- [x] Uj helper: `va_lookup_geo_by_coords(lat,lng)` reverse geokodolas (Nominatim)
- [x] GPS-es nezet rogzitese mar nem csak koordinatat ment, hanem orszag/regio/helyseg nevet is
- [x] Ha a helyseg nem oldhato fel, fallback marad a koordinata formatum
- [x] GPS pontossag tovabbra is latszik a regio mezoben (`GPS, ±Xm`)
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
GPS engedely mellett a riport mar helysegnevet mutat a koordinata alapjan, nem csak nyers lat/lng erteket.

---

## 2026. 05. 08. – Session #247 (Geo popup a sajat admin hirdeteslistan is)

### Mit csinaltunk [x]
- [x] A `vadaszapro-listings` egyedi admin listaban a nezetseg oszlopba bekerult a Geo popup trigger
- [x] Gomb csak admin szerepkornel jelenik meg, mas szerepkornel sima nezetseg szam marad
- [x] Minden hirdetes soraban mukodik (nem csak sajat hirdeteseknel)
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A geo riport most mar az egyedi admin hirdeteslista oldalon is elerheto, ahol az osszes hirdetes latszik.

---

## 2026. 05. 08. – Session #246 (GPS-pontos hely mentes, IP fallback)

### Mit csinaltunk [x]
- [x] Listing megnyitasnal geolocation engedely eseten GPS koordinata + pontossag bekerul az AJAX keressel
- [x] Szerver oldalon `increment_views` fogadja a `gps_lat/gps_lng/gps_accuracy` mezoket
- [x] `va_record_view_geo` bovitve: GPS adat elsoseget kap, csak ennek hianyaban hasznal IP geolokaciot
- [x] Riport tablaban GPS sorok egyertelmu jelolese (`GP - GPS (eszkoz)`)
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Ha a bongeszo helyhozzaferest kap, a riport mar eszkoz-GPS alapjan ment, vagyis a tenyleges helyedet mutatja (nem szolgaltatoi IP-kilepesi pontot). Hozzaferes tiltasa eseten IP fallback marad.

---

## 2026. 05. 08. – Session #245 (Geo riport idozona + IP pontossag jelzes)

### Mit csinaltunk [x]
- [x] Frontend geo popup idopontjai Europe/Budapest lokal idore formalva (kezdet, frissites, utolso)
- [x] Admin geo popup idopontjai Europe/Budapest lokal idore formalva
- [x] Beepitett figyelmeztetes: IP-alapu geolokacio mobilhalozatnal a szolgaltato kilepesi pontjat mutathatja (pl. Budapest), nem GPS-t
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A riport idok most mar helyi (Budapest) idozonaban jelennek meg, es egyertelmu jelzes van a geolokacio pontossagi korlatrol.

---

## 2026. 05. 08. – Session #244 (Geo jogosultsag: premiumbol csak Platinum)

### Mit csinaltunk [x]
- [x] A premium jogosultsag szigoritva: a geo riportot a fizetett csomagok kozul mar csak a `platinum` lathatja
- [x] Admin jogosultsag valtozatlanul megmaradt (admin tovabbra is lathatja)
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A geo riport jogosultsag most: admin vagy platinum. Silver es Gold fiokok mar nem latjak.

---

## 2026. 05. 08. – Session #243 (Geo jogosultsag: admin + premium)

### Mit csinaltunk [x]
- [x] Uj helper: `va_is_premium_member()` (silver/gold/platinum + admin)
- [x] Uj helper: `va_user_can_open_geo_report()` (admin mindenhez, premium csak sajat hirdeteshez)
- [x] Admin listaban a geo popup trigger kizarolag adminnak jelenik meg
- [x] Admin legacy riport endpoint is admin-only-ra szigoritva
- [x] Fiókom oldalon a geo gomb most admin + premium felhasznaloknak latszik
- [x] AJAX geo riport jogosultsag atallitva a fenti szabalyokra
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A geo riport most mar pontosan szerepkor alapu: admin minden hirdeteshez hozzafer az admin feluleten, a sajat fiokban csak adminok es premium tagok latnak geo riportot, premium csak a sajat hirdeteseire.

---

## 2026. 05. 08. – Session #242 (Geo riport felrakas idopontjatol)

### Mit csinaltunk [x]
- [x] Uj idosoros tabla bevezetve: `wp_va_view_geo_daily` (napi geo aggregatum)
- [x] Migracio automatikusan fut: `va_view_geo_table_ver` -> `1.1.0`
- [x] Geo logolas most mar egyszerre ket helyre megy: osszesitett + napi tabla
- [x] Riport lekerdezes atallitva napi tablara, a hirdetes `post_date` (felrakas ideje) kezdettel
- [x] Popupokban metadatak: kezdo idopont (`from_datetime`) + frissitesi ido
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A geo riport idoszakkezdete most a hirdetes felrakasi ideje, es idosoros adatokbol szamol. A fejlesztes alatti korabbi osszemosott statok nem keverednek az uj modos tartomanyba.

---

## 2026. 05. 08. – Session #241 (Geo badge egyforma + 30 napos IP validitas)

### Mit csinaltunk [x]
- [x] Fiókom > Megtekintes oszlopban a szem es Geo kapszula teljesen egy sorban, azonos merettel/stilussal
- [x] Geo ikon emoji helyett azonos meretu SVG iconra cserelve (vizualisan konzisztens)
- [x] IP deduplikacio szigoritas: ugyanaz az IP 30 napig csak 1x novelheti a geo statot hirdetesenkent
- [x] Frontend/admin popup figyelmezteto szoveg frissitve az uj 30 napos validitas szabalyra
- [x] Source + plugin mirror szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A ket badge most mar tenylegesen egyforma es egy sorban jelenik meg. A lokacios stat validitasa szigoritva: egy IP 30 napig csak egyszer szamit.

---

## 2026. 05. 08. – Session #240 (Geo popup + letoltes/nyomtatas + validitas javitas)

### Mit csinaltunk [x]
- [x] Admin listaban a Megtekintes oszlop szem linkje uj oldal helyett popupot nyit
- [x] Fiókom > Hirdeteseim tablaban a Geo gomb egy sorba es azonos meretre igazítva a kis szem badge-del
- [x] Modern popup bevezetve frontend + admin oldalon: tablazat, osszesites, hiba/ures allapot
- [x] Popupban uj funkciok: CSV letoltes + nyomtatas
- [x] Uj AJAX endpoint: `va_get_view_geo_report` (nonce + admin jogosultsag ellenorzes)
- [x] Validitas javitas: ugyanaz az IP 6 oran belul csak egyszer novel lokacios statisztikat hirdetesenkent
- [x] Modal figyelmeztetes ha tul magas top lokacio arany (koncentracio)
- [x] Source + plugin mirror fajlok szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A geo riport most mar nem uj oldalra dob, hanem modern popupban jelenik meg, letoltheto/nyomtathato formatumban. A torz lokacios szamok ellen bekerult IP-idoszakos deduplikacio.

---

## 2026. 05. 08. – Session #239 (Admin megtekintes lokacio riport, IP alapu)

### Mit csinaltunk [x]
- [x] Uj adatbazis tabla bevezetve: `wp_va_view_geo` (post + orszag/regio/varos aggregalt nezettseg)
- [x] IP-alapu geolokacio lookup bevezetve `ipwho.is` API-val, transient cache-sel (gyorsitas)
- [x] Megtekintes noveleskor (`va_increment_views`) automatikus lokacio logolas
- [x] Admin hirdetes listaban a `Megtekintes` oszlop kattinthatora allitva (`👁 szam`)
- [x] Kattintasra uj admin riport oldal nyilik, ahol latszik orszag/regio/varos bontas + utolso nezes
- [x] Source + plugin mirror fajlok szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
Mostantol hirdetesenkent az admin listaban a szem ikonra kattintva megnezed, hogy orszagosan (orszag/regio/varos bontasban) honnan neztek a hirdetest.

---

## 2026. 05. 08. – Session #238 (404 olvashatosag fix, vilagos szoveg)

### Mit csinaltunk [x]
- [x] A 404 oldalon a fo tipografia szinei vilagosra kenyszeritve (`!important`)
- [x] A `404` felirat fallback feher szint kapott minden bongeszore
- [x] `background-clip` tamogatasnal marad a csikozott hatas, tamogatas nelkul is olvashato
- [x] Cim es leiras eros kontrasztot kapott (vilagos szin + text-shadow)
- [x] Root + theme `404.php` mindket helyen frissitve
- [x] Deploy All sikeres

### Hol tartunk
A feliratok most mar sotet hatteren is egyertelmuen olvashatok, nem tudnak fekete/sotet szinre visszaesni.

---

## 2026. 05. 08. – Session #237 (404 full-screen referencia kozeli verzio)

### Mit csinaltunk [x]
- [x] 404 oldal teljes viewportos (full-screen) kozepponti kompoziciora allitva
- [x] Nagy, csikozott `404` tipografia + 10s-es glitch animacio megtartva
- [x] HUD jellegu sarkok, oldalso status szovegek es finom zajreteg beallitva
- [x] Minimalista egyetlen fo CTA gomb (`VISSZA A FOOLDALRA`)
- [x] Root es theme `404.php` tartalom teljesen szinkronban
- [x] Deploy All sikeres

### Hol tartunk
Az uj 404 oldal most mar teljes kepernyos, referencia-hangulatu, eros vizualis fokusz a kozepso tartalommal.

---

## 2026. 05. 08. – Session #236 (404 brutalis redesign, split hero)

### Mit csinaltunk [x]
- [x] A 404 sablon teljes vizualis ujraepitese split-layout hero szerkezetre
- [x] Bal oldalon nagyméretu 404 tipografia + 10s glitch hatas (cyan/red szetcsuszas)
- [x] Jobb oldalon status panel blokkok modern card stílussal
- [x] Erősebb fenyek, kontrasztos hatter, premium border + glow
- [x] Mobilon egyoszlopos torés, teljes magassaghoz igazított blokk
- [x] Root es theme 404 fajl azonos tartalomra szinkronizalva
- [x] Deploy All sikeres

### Hol tartunk
A 404 oldal most sokkal agresszivebb, karakteresebb vizuált ad, es hangulatban kozel van a kert referenciastilushoz, de sajat implementacio.

---

## 2026. 05. 08. – Session #235 (Vásárlás hero full-screen + 10s glitch)

### Mit csinaltunk [x]
- [x] A rang alapú vásárlás hero blokk teljes képernyős magasságot kapott
- [x] A középső cím rétegezése megerősítve, hogy biztosan látható maradjon
- [x] Erős TikTok-szerű glitch animáció hozzáadva a címhez
- [x] Animáció ciklus: 10 másodperc, végtelen ismétlés
- [x] Mobilon is viewport-közeli magasságra igazítva
- [x] Módosítás átvezetve source + plugin mirror CSS fájlokba
- [x] Deploy All sikeres

### Hol tartunk
A vásárlási hero most full-screen hatású, a cím középen jól olvasható, és a kért erős, 10s-es ismétlődő glitch effekt fut rajta.

---

## 2026. 05. 08. – Session #234 (Vásárlás hero cím fix)

### Mit csinaltunk [x]
- [x] A vásárlási hero főcím fix fehér színt kapott (`.va-credits-title`)
- [x] Az alapértelmezett cím értéke `VÁSÁRLÁS`-ra állítva
- [x] Legacy fallback: ha régi cím (`Rang Alapú Vásárlás`) van mentve, automatikusan `VÁSÁRLÁS` jelenik meg
- [x] Módosítás átvezetve source + plugin mirror fájlokba
- [x] Deploy All sikeres

### Hol tartunk
A középső főcím most minden esetben jól olvasható fehér, és a kért felirat `VÁSÁRLÁS`.

---

## 2026. 05. 08. – Session #233 (404 oldal dögösebb redesign)

### Mit csinaltunk [x]
- [x] A frissen letrehozott 404 oldal teljes vizualis ujratervezese premium hero stilusra
- [x] Nagy tipografia (`404` digits), erosebb fenyek, filmesebb hatter es zajreteg
- [x] Uj CTA hierarchia: hirdetes kereses (primary), fooldal, kapcsolat
- [x] Gyorslink capsule sor + hangsulyos keresosav a hero blokkban
- [x] Root es theme 404 sablon szinkronizalva + Deploy All sikeres

### Hol tartunk
A 404 oldal most mar sokkal karakteresebb, modern es brand-kompatibilis landing jellegu felulet, nem alap hibaablak hatasu.

---

## 2026. 05. 08. – Session #232 (Egyedi 404 hibaoldal)

### Mit csinaltunk [x]
- [x] Uj, teljesen egyedi theme 404 sablon letrehozva (`404.php`)
- [x] Dizajn a projekt arculathoz igazítva (fekete alap, piros accent, dot-grid hatter)
- [x] Beepitett gyors muveletek: fooldal, hirdetes keresese, kapcsolat
- [x] Kereso mező kerult a hibakepernyore, hogy azonnal lehessen tovabb lepni
- [x] Mobilbarat elrendezes + hover allapotok beallitva
- [x] Theme mirror fajlba is atvezetve + Deploy All sikeres

### Hol tartunk
A `/404/` oldal most mar nem alap WordPress hibaoldal, hanem markahu, hasznalhato landing jellegu oldal, ami tovabblepteti a latogatot.

---

## 2026. 05. 07. – Session #231 (CookieYes keret karcsúsítás + belső szöveg beljebb)

### Mit csinaltunk [x]
- [x] A dupla külső keret-hatás megszüntetve (csak a fő consent bar kap border+glow stílust)
- [x] A szöveges blokk belső paddingja növelve (desktop és mobil)
- [x] A consent bar saját paddingja karcsúsítva, kompaktabb vizuál
- [x] Theme deploy sikeresen lefutott

### Eredmény
A sütisáv kevésbé „hatalmas doboz”, a szöveg beljebb került és tisztábban ül a belső tartalmi tengelyen.

---

## 2026. 05. 07. – Session #230 (CookieYes margó- és belső spacing fix)

### Mit csinaltunk [x]
- [x] A sütisáv konténer középre igazítva fix margóval (`min(1280px, calc(100% - 28px))`)
- [x] A külső keretből kilógás megszüntetve, radius vágás elleni beállításokkal
- [x] A belső szövegblokk plusz belső paddinget kapott, mobil clipping javítva
- [x] Mobil szélességre külön konténerlimit: `calc(100% - 16px)`
- [x] Theme deploy sikeresen lefutott

### Hol tartunk
A CookieYes sáv most már csak a belső lekerekített blokkban jelenik meg, fix margón belül marad, és mobilon sem vágja le a szöveget a border/radius.

---

## 2026. 05. 07. – Session #229 (Welcome preview kivezetése)

### Mit csinaltunk [x]
- [x] Az ideiglenes `Napi üdvözlés x10` gomb eltávolítva a dashboard fejlécből
- [x] A preview user meta és POST aktiváló logika kivezetve
- [x] A napi welcome popup logikája admin-only napi 1 megjelenésre állítva vissza
- [x] A 1000 darabos random kívánság és az IP-alapú 365 napos ismétlésvédelem változatlanul megmaradt
- [x] Root dashboard sablon szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
A dashboardból eltűnt az ideiglenes tesztgomb, és a napi jókívánság most már csak adminnál jelenik meg napi egyszer, a korábban beállított randomizálással és ismétlésvédelemmel.

---

## 2026. 05. 07. – Session #228 (CookieYes konténer finomítás + fix jókívánság kép)

### Mit csinaltunk [x]
- [x] A CookieYes sáv külső konténer-hatása kivezetve, hogy ne látszódjon dupla doboz
- [x] Mobilon a szöveg és a gombsor extra belső paddinget kapott, hogy ne vágja a border
- [x] A napi jókívánság popup képe fixre állítva a `wp-content/Névtelen.png` fájlra
- [x] Ha ez a fájl nem található, csak akkor esik vissza SVG fallbackre
- [x] Theme deploy + Plugin deploy sikeresen lefutott

### Hol tartunk
A sütisáv most már egyetlen, tisztább konténerrel jelenik meg és mobilon sem szorul rá a szegélyre, a jókívánság popup pedig mindig a `wp-content` gyökerében lévő fix képet használja.

---

## 2026. 05. 07. – Session #227 (Végső social meta kimenet felülírás)

### Mit csinaltunk [x]
- [x] A listing oldalaknál output buffer alapú végső HTML social meta felülírás került be
- [x] Célzottan cserélve: `og:title`, `twitter:title`, `og:description`, `twitter:description`
- [x] A social leírás most már tartalmazza az árat és aktív akciónál az akciós árat is
- [x] Ezzel a későbbi plugin/meta felülírások (pl. Rank Math) is megkerülhetők a végső kimenetben
- [x] Root SEO fájl szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
A hirdetésoldal végső HTML-je most már kényszerítve tartalmazza az `Eladó | Ár` / `Akciós ár` social meta adatokat, így a Facebook scraper ezt kell hogy lássa.

---

## 2026. 05. 07. – Session #226 (CookieYes vizuális redesign)

### Mit csinaltunk [x]
- [x] CookieYes sütisáv saját design override-ot kapott a theme `wp_head` kimenetében
- [x] Új vizuál: lekerekített, fekete-piros, üvegesebb panel, jobb árnyékkal és finomabb gombokkal
- [x] Gombok átdolgozva: hangsúlyos elfogadás, visszafogott testreszabás/elutasítás
- [x] Mobilon teljes szélességű, egymás alá rendezett gombok
- [x] Theme deploy sikeresen lefutott

### Hol tartunk
A sütisáv már nem nyers plugin kinézetű, hanem jobban illeszkedik a fekete-piros oldaldesignhoz.

---

## 2026. 05. 07. – Session #225 (Rank Math social title kompatibilitás)

### Mit csinaltunk [x]
- [x] Rank Math OG/Twitter title filterekre közvetlen bekötés a listing social cím logikához
- [x] Rank Math OG/Twitter description filterekre bekötés listing oldali leíráshoz
- [x] Cél: Facebook megosztásnál biztosan látszódjon az `Eladó | Ár` és akció esetén az `Akciós ár`
- [x] Root SEO fájl szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
Rank Math használat mellett is a hirdetés megosztási címe most már a testreszabott áras logikából épül.

---

## 2026. 05. 07. – Session #224 (Facebook megosztás cím finomítás)

### Mit csinaltunk [x]
- [x] A `va_listing` oldalak social címe külön logikát kapott (OG + Twitter)
- [x] Megosztási cím formátum: `Főcím | Eladó | Ár: ...`
- [x] Akció esetén: `Főcím | Eladó | Akciós ár: ... (eredeti: ...)`
- [x] Lejárt akció automatikusan nem számít aktív akciónak
- [x] Root SEO fájl szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
Facebook megosztásnál a hirdetés címe most már tartalmazza az `Eladó` jelzést és az árat, aktív akciónál pedig az akciós árat is.

---

## 2026. 05. 07. – Session #223 (Komplett SEO motor beépítés)

### Mit csinaltunk [x]
- [x] Új központi SEO osztály létrehozva: [includes/class-seo.php](includes/class-seo.php)
- [x] Teljes head meta réteg: title/description, canonical, robots, OG, Twitter, hreflang
- [x] Strukturált adatok (JSON-LD): WebSite, Organization, BreadcrumbList, ItemList, Product, Auction
- [x] XML sitemap rendszer beépítve:
  - [sitemap.xml](sitemap.xml) index
  - poszt típus sitemap oldalak
  - taxonómia sitemap oldalak
- [x] robots.txt sitemap sor automatikus hozzáadás
- [x] Search és 404 oldalak noindex, nofollow
- [x] Plugin bootba bekötve a SEO engine, rewrite verzió emelve: [vadaszapro-core.php](vadaszapro-core.php)
- [x] Root -> plugin mirror szinkron + Deploy Plugin sikeres

### Hol tartunk
Az oldal most kapott egy teljes saját technikai SEO alapot, ami külső SEO plugin nélkül is korrekt indexelést, megosztási meta adatokat és sitemap infrastruktúrát ad.

---

## 2026. 05. 07. – Session #222 (Welcome popup tipográfia + UTF-8 + képfallback)

### Mit csinaltunk [x]
- [x] A napi kívánság szöveg idézőjeles (nyitó/záró idézőjel) tördelést kapott
- [x] A címbetű mérete és súlya visszafogottabb, elegánsabb lett
- [x] A kívánság szótár teljesen ékezetes UTF-8 magyar szövegre cserélve
- [x] Hero kép blokk átállítva valódi `img` elemre, hibakezelő fallbackgel
- [x] Képforrás-feloldás bővítve: user legfrissebb képes feltöltés fallback is bekerült
- [x] Root dashboard sablon szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
A popup már nem túl nagy betűs, a szöveg idézőjeles és ékezetes, a kép pedig betöltési hiba esetén is biztosan megjelenik fallbackkel.

---

## 2026. 05. 07. – Session #221 (Welcome popup: kep + fiokspecifikus nev)

### Mit csinaltunk [x]
- [x] A welcome popup kepforrasa robusztusra allitva (letezo fajl ellenorzessel)
- [x] Elsodleges kep: `wp-content/uploads/fortune-cookies.png` ha tenylegesen letezik
- [x] Fallback kep beallitva (beepitett SVG), hogy a hero blokk soha ne legyen ures
- [x] Fiokspecifikus nevfeluliras: `weingartnerauto` login/display nev eseten a popupban a nev `Adri`
- [x] Root dashboard sablon szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
A popupban mar nem marad ures kephely, es ebben a fiokban a napi kivansag szovegben mar nem a regi nev jelenik meg, hanem Adri.

---

## 2026. 05. 07. – Session #220 (Kiemelés stat + gomb javítás)

### Mit csinaltunk [x]
- [x] A stat panelből kiveve a kulon `Boost aktív` sor (duplikacio csokkentese)
- [x] Bevezetve az osszesitett `Kiemelt hirdetesek` szamitas (featured + boost egyedi hirdetes alapjan)
- [x] Kapcsolodo stat mezok ehhez igazitva (`Top cimke`, `Kiemelt` badge, KPI alreszek)
- [x] A hirdetes sorban a `Kiemeles` gomb cooldown alatt is lathato marad (tiltott, varakozasi idovel)
- [x] Root dashboard sablon szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
A statisztika mar nem mutat kulon boost duplikaciot, es a kiemeles kezelese a hirdetes mellett folyamatosan lathato a rang/cooldown logikaval egyutt.

---

## 2026. 05. 07. – Session #219 (Napi kívánság popup újratervezés)

### Mit csinaltunk [x]
- [x] A napi popup vizualis resze teljesen atdolgozva (ikonos "suti" blokk eltavolitva)
- [x] Feher szovegkenyszer beallitva a popup teljes tartalmara
- [x] Gombfelirat cserelve: `Napi kívánság bezárása`
- [x] Szerveroldali 1000 darabos magyar kivansag generator beepitve
- [x] IP-alapu 1 eves ismetlesvedelem beepitve (`transient` alapon)
- [x] Dashboard sablon plugin mirror szinkron + Deploy Plugin sikeres

### Hol tartunk
A napi popup most mar egyetlen, szerveroldalon valasztott magyar kivansagot mutat, es ugyanazon IP-n 1 evig nem adja ujra ugyanazt a kivansagot.

---

## 2026. 05. 07. – Session #218 (Napi üdvözlés x10 ideiglenes előnézet)

### Mit csinaltunk [x]
- [x] A dashboard fejlécébe bekerült egy ideiglenes `Napi üdvözlés x10` gomb
- [x] A gomb 10 darab újratöltésre aktiválja a napi welcome popupot ugyanannak a felhasználónak
- [x] Javítva a logika, hogy maga a gombnyomás ne fogyasszon el egy alkalmat
- [x] Maradék számláló megjelenítve a gomb mellett
- [x] Dashboard plugin mirror szinkron + Deploy Plugin sikeres

### Hol tartunk
A napi üdvözlő popup most ideiglenesen kényelmesen tesztelhető: az aktiválás után még 10 teljes reloadon át újra megjelenik, utána visszaáll a normál napi működés.

---

## 2026. 05. 07. – Session #217 (További statisztikák vissza lenyitható blokkba)

### Mit csinaltunk [x]
- [x] A kompakt hárompaneles fő nézet megmaradt láthatóan
- [x] A korábban kivett extra statok visszakerültek egyetlen lenyitható blokkba
- [x] A lenyitható blokk tartalma: piaci mozgás chart, jelzések, alap KPI adatok
- [x] Dashboard plugin mirror szinkron + Deploy Plugin sikeres

### Hol tartunk
A statisztikai rész most úgy működik, ahogy kérted: a rövid fő nézet látszik, a többi extra adat pedig lenyithatóan elérhető.

---

## 2026. 05. 07. – Session #216 (Stat blokk végső egyszerűsítés)

### Mit csinaltunk [x]
- [x] A stat szekcióból eltávolítva a fő chart, trend és alapadat blokkok
- [x] Meghagyva csak a kompakt hárompaneles nézet: státusz megoszlás, top 5 hirdetés, jelzők és kiemelések
- [x] A szekció címe ehhez igazítva: `Részletes bontás`
- [x] Dashboard plugin mirror szinkron + Deploy Plugin sikeres

### Hol tartunk
A /va-fiok statisztika most már pontosan a kompakt, hárompaneles nézetet mutatja, extra felső blokkok nélkül.

---

## 2026. 05. 07. – Session #215 (Alapból csukott stat blokkok + login redirect fix)

### Mit csinaltunk [x]
- [x] A fő chart alatti `Alapadatok` blokk alapból nyitott állapota megszüntetve
- [x] Eredmény: a fő chart alatt most minden lenyitható blokk csukva indul
- [x] A theme `login_redirect` filterből kivéve az admin oldalra kényszerítő átirányítás
- [x] Eredmény: admin belépés után már nem a custom admin oldal tölt be automatikusan
- [x] Dashboard plugin mirror szinkron + Deploy All sikeresen lefutott

### Hol tartunk
A stat szekció letisztultabb induló állapotot kapott, és az admin login többé nem írja felül automatikusan a WordPress belépési redirectet.

---

## 2026. 05. 07. – Session #214 (Stat panel egyszerűsítés lenyitható blokkokkal)

### Mit csinaltunk [x]
- [x] A stat panel alapnezetet letisztitottuk: csak a fo attekinto resz marad egybol lathato
- [x] A jelzesek/trendek kulon lenyithato blokkba kerultek
- [x] Az alap KPI adatok kulon lenyithato blokkba kerultek, nyitott alapallapottal
- [x] A reszletes bontas (statusz, toplista, kiemelesek) kulon lenyithato blokk lett
- [x] Natív `details/summary` megoldas hasznalva, kulon JS nelkul
- [x] Root sablon szinkron plugin mirrorba + Deploy Plugin sikeresen lefutott

### Hol tartunk
A /va-fiok statisztika most mar sokkal atlathatobb: a fo adatok latszanak azonnal, a reszletesebb blokkok pedig lenyithatok.

---

## 2026. 05. 07. – Session #213 (Future-market stat dashboard redesign)

### Mit csinaltunk [x]
- [x] A CRM stat blokk informacios hierarchiaja teljesen ujra lett tervezve 2026-os dashboard iranyba
- [x] Uj fo resz: nagy chart-screen (grid, gradient line, labels, valos adatokbol szamitott pontok)
- [x] Uj signal stack: kompakt jelzokartyak kulon kulon savokkal es rovid insight szoveggel
- [x] KPI kartyak es also panelek uj uveg/premium stilust kaptak, jobb tipografiaval
- [x] Minden felirat magyarositva es tartalmilag a valos metrikakhoz igazitva
- [x] Root sablon szinkron plugin mirrorba + Deploy Plugin sikeresen lefutott

### Hol tartunk
A /va-fiok stat resz mar nem egyszeru blokkhalmaz, hanem egy modern, future-market jellegu vezerlopult, eros vizualis hierarchiaval.

---

## 2026. 05. 07. – Session #212 (Chart finomhangolas: kecsesebb, szinesebb)

### Mit csinaltunk [x]
- [x] A stat chart design visszafogva: kevesebb tuzpiros, elegansabb szinpaletta (kek-zold-borostyan)
- [x] Karcsubb grafikai stilus: vekonyabb vonal/stroke, kisebb glow, enyhebb shadow
- [x] KPI tipografia finomitva: kevesbe vastag ertekek, diszkretebb hangsulyok
- [x] Hero/chart feliratok teljesen magyarositva (`Piaci mozgas`, `Aktiv arany`, `Heti tempo`, `Erdeklodes`)
- [x] Root sablon szinkron plugin mirrorba + Deploy Plugin sikeresen lefutott

### Hol tartunk
A /va-fiok stat panel mar kecsesebb, letisztultabb, szinesebb es kevésbé agressziv megjelenesu.

---

## 2026. 05. 07. – Session #211 (Tőzsdei chart design a stat panelben)

### Mit csinaltunk [x]
- [x] A CRM blokk kapott egy uj hero chart reszt (vonalgrafikon + area fill + pontok + mini oszlop flow)
- [x] 3 kor/gyuru metrika kerult be (Aktiv rata, 7/30 momentum, Engagement)
- [x] KPI kartyak aljara mini spark-bar grafikonok kerultek animacioval
- [x] Kiegeszito chart animaciok: line draw + bar pop
- [x] Root sablon szinkron plugin mirrorba + Deploy Plugin sikeres

### Hol tartunk
A /va-fiok statisztika mar chart-alapu, terminal jellegu, latvanyos dashboard elemeket mutat, sokkal kozelebb a modern tozsdei/analitikai UI vilaghoz.

---

## 2026. 05. 07. – Session #210 (CRM stat panel vizuális redesign)

### Mit csinaltunk [x]
- [x] A Hirdeteseim statisztikai blokk teljes visual refresh-t kapott modern dashboard stilusban
- [x] KPI kartyak uj stilusa: melyebb panel, eros tipografia, glow/gradient header line, hover emeles
- [x] Panelek/progress barok/toplista badge-ek atdolgozva jobb kontrasztra es premium megjelenesre
- [x] Dot-grid + sotet alap megtartva, feher szoveggel es piros-narancs accenttel
- [x] Root sablon szinkron plugin mirrorba + Deploy Plugin sikeresen lefutott

### Hol tartunk
A /va-fiok statisztika mar nem fapados: modern, kontrasztos dashboard kartyas nezetet ad ugyanazzal az adattartalommal.

---

## 2026. 05. 07. – Session #209 (Napi üdvözlő popup véglegesítés: szerencsesüti)

### Mit csinaltunk [x]
- [x] A napi popup tartalma leegyszerűsítve: csak egy napi üdvözlő szöveg maradt
- [x] Eltávolítva a plusz elemek (`eyebrow`, `subtitle`, extra üzenetsor)
- [x] Új szerencsesüti hangulatú vizuál: halvány süti-motívum + meleg, sütis bezáró gomb
- [x] Popupon belül minden szöveg fehérre állítva
- [x] JS egyszerűsítve: csak egy random napi köszöntés generálódik névre szólóan
- [x] Dashboard template szinkron a plugin mirrorba + LocalWP deploy sikeres

### Hol tartunk
A napi üdvözlő popup már minimalista: egy személyes köszöntés és egy bezárás gomb, szerencsesüti stílusú megjelenéssel.

---

## 2026. 05. 07. – Session #208 (Rollback 1 lépés kérésre)

### Mit csinaltunk [x]
- [x] Visszaállítva az előző állapot: a fix bal menü + külön content scroll módosítás kivezetve
- [x] Visszaállítva a tömeges műveletben az `Ár módosítása` opció
- [x] Visszaállítva a bulk ár/akció panel (HTML + CSS + JS)
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
A dashboard viselkedése egy lépéssel visszaállt a korábbi verzióra a kérésed szerint.

---

## 2026. 05. 07. – Session #207 (Fiók dashboard UX: fix bal menü + bulk egyszerűsítés)

### Mit csinaltunk [x]
- [x] Dashboard layout felülírás: bal menü fixen marad, a középső tartalom saját sávban scrollozik
- [x] Mobilon visszaáll normál folyó layoutra (ne törjön az oldal)
- [x] Tömeges műveletből eltávolítva az `Ár módosítása` opció
- [x] Bulk ár/akció panel teljesen eltávolítva (HTML + CSS)
- [x] Kapcsolódó JS logika tisztítva (`price_change` ág kivéve)
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
Fiók oldalon már nem az egész oldal scrollozik desktopon: a bal nav fix, a fő tartalom külön görgethető; a bulk művelet csak Aktiválás/Szüneteltetés/Törlés.

---

## 2026. 05. 07. – Session #206 (Custom fekete naptár a popupban)

### Mit csinaltunk [x]
- [x] Natív fehér datepicker lecserélve saját (JS + CSS) dark naptár komponensre
- [x] Dátummező most readonly szövegmező + külön naptár gomb
- [x] Sötét naptár popup: hónap léptetés, naprács, mai nap jelölés, kijelölt nap jelölés
- [x] Dátum kiválasztás után azonnali értékbeírás (`YYYY-MM-DD`) + előnézet frissítés
- [x] Külső kattintásra naptár bezárás
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
Az ár/akció modalban a naptár már teljesen fekete, a projekt dizájnjához illeszkedő saját komponens.

---

## 2026. 05. 07. – Session #205 (Popup: élő ár előnézet)

### Mit csinaltunk [x]
- [x] Akció/ár szerkesztő popupba élő "Előnézet" blokk került
- [x] Előnézet mutatja normál ár + akciós ár kombinációt (AKCIÓ badge-del)
- [x] Akció nélküli esetben csak a normál ár jelenik meg
- [x] Akció végdátum szöveg is látszik az előnézetben
- [x] Input közbeni azonnali frissítés (normál ár, akciós ár, dátum)
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
A kis popupban mentés előtt vizuálisan látható, pontosan hogyan fog kinézni az ár megjelenítése.

---

## 2026. 05. 07. – Session #204 (Popup bővítés: normál ár módosítás)

### Mit csinaltunk [x]
- [x] Az akció popup kibővítve új mezővel: `Normál ár (Ft)`
- [x] A ✎ gomb most átadja az aktuális normál árat is a modalnak
- [x] Egy mentéssel frissül a `va_price` (normál ár) és az akciós mezők (`va_sale_price`, `va_sale_price_end`)
- [x] `va_set_sale_price` AJAX handler bővítve `normal_price` mentéssel
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
A kis popup már nem csak akciót kezel: ugyanabban az ablakban és mentésben a normál ár is módosítható.

---

## 2026. 05. 07. – Session #203 (Dupla naptár ikon fix + dark picker)

### Mit csinaltunk [x]
- [x] Akció modalból eltávolítva a második (custom) naptár gomb
- [x] Egyetlen natív naptárválasztó ikon maradt a dátummezőben
- [x] Dátummezőre és modalra `color-scheme: dark` beállítás került
- [x] Naptár ikon kontrasztja javítva (`::-webkit-calendar-picker-indicator` filter)
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
Az akció popupban már csak 1 naptár ikon látszik, és a picker megjelenése a lehető legjobban sötétítve van a böngésző korlátain belül.

---

## 2026. 05. 07. – Session #202 (Akció modal: naptár ikon + gomb polish)

### Mit csinaltunk [x]
- [x] Akció modal dátummező visszaállítva `type=date` mezőre
- [x] Dátummező mellé külön naptár ikon gomb került (`showPicker` támogatással)
- [x] Modal mezők és action gombok vizuális finomítása (jobb kontraszt, hover, tisztább megjelenés)
- [x] Számmező pörgető nyilak elrejtve a modalban (ne legyen "csúnya" spinner)
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
Az akció szerkesztő popupban már nem csak beírni lehet a dátumot: külön ikonról is megnyitható a naptárválasztó, és a modal gombok/mezők egységesebben néznek ki.

---

## 2026. 05. 07. – Session #201 (Akció szerkesztés: popup modal visszaállítás)

### Mit csinaltunk [x]
- [x] Az inline soros akciós ár szerkesztő eltávolítva a Hirdetéseim táblából
- [x] Visszaállítva a középre felugró akció szerkesztő modal
- [x] Blur overlay visszarakva (`backdrop-filter: blur(8px)`)
- [x] Mentés/Mégse/Akció törlése működés visszaállítva modal alapon
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
A módosítás újra középre nyíló, blur hátterű popupban történik, a korábbi "szép" modal UX szerint.

---

## 2026. 05. 07. – Session #200 (Fiókoldal UX polish: dátummező + akció ikon)

### Mit csinaltunk [x]
- [x] A fiókoldali akció dátummezők átállítva natív `type=date` helyett szöveges (`YYYY-MM-DD`) inputra
- [x] Bulk és inline akció mentésnél dátum normalizálás/validáció: `YYYY-MM-DD`, `YYYY.MM.DD`, `YYYY/MM/DD` elfogadott
- [x] Ár melletti akció módosítás piktogram újrastílusozva (mindig jól látható, külön idle/active állapot)
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
A `/va-fiok/` oldalon nincs többé fehér böngésző datepicker popup, és az ár melletti akció szerkesztő ikon kontrasztosan látható.

---

## 2026. 05. 07. – Session #199 (Dashboard tabla/tartalom visszaállítás)

### Mit csinaltunk [x]
- [x] Javítva: hibás `<style>` tagek eltávolítva a tartalomblokk elejéről
- [x] Javítva: egyetlen helyes `<style>` nyitás maradt a CSS blokk előtt
- [x] Eredmény: a jobb oldali dashboard tartalom (CRM + táblázat) újra renderelődik
- [x] Plugin mirror sync + Deploy All lefutott

### Hol tartunk
A `/va-fiok/` oldalon a listings panel és a táblázat visszatöltődik.

---

## 2026. 05. 07. – Session #198 (Dashboard CSS megjelenés végleges hotfix)

### Mit csinaltunk [x]
- [x] Javítva: hiányzó `<style>` nyitó tag visszaállítva a modal blokk után
- [x] Tünet: CSS szövegként jelent meg a dashboard alján
- [x] Plugin mirror sync + Deploy All sikeresen lefutott

### Hol tartunk
A dashboard CSS újra stíluslapként renderelődik, nem szövegként.

---

## 2026. 05. 07. – Session #197 (Dashboard content visszaállítás)

### Mit csinaltunk [x]
- [x] Javítva: téves `<style>` tag eltávolítva a dashboard tartalom blokk elejéről
- [x] Tünet: jobb oldali dashboard tartalom eltűnt (a browser CSS-ként értelmezte a HTML-t)
- [x] Plugin mirror szinkron és Deploy All futtatva

### Hol tartunk
A jobb oldali dashboard tartalom ismét renderelődik.

---

## 2026. 05. 07. – Session #196 (Dashboard render hotfix)

### Mit csinaltunk [x]
- [x] Javítva: a `frontend/templates/user/dashboard.php` fájlban hiányzó `<style>` nyitó tag
- [x] Tünet: teljes CSS szövegként jelent meg a `/va-fiok/` oldalon, layout szétesett
- [x] Plugin mirror szinkron (dashboard.php)
- [x] Deploy All futtatva, sikeres

### Hol tartunk
A dashboard oldal CSS-e újra rendesen értelmeződik, a lap normálisan renderel.

---

## 2026. 05. 07. – Session #195 (Dashboard: bulk árazó, akciós ár, rendezés, profil)

### Mit csinaltunk [x]
- [x] 3 új AJAX endpoint: `va_refresh_listing`, `va_bulk_listings`, `va_set_sale_price` (class-ajax.php)
- [x] Tömeges műveletek (bulk): aktiválás, szüneteltetés, törlés, ár módosítása – per-row checkbox + toolbar
- [x] **Tömeges árváltoztató panel**: új ár + opcionális akciós ár + akció vége dátum
- [x] **Akciós ár** per-hirdetés: quick-edit modal, `va_sale_price` + `va_sale_price_end` meta mentés
- [x] Ár cellában: ha van akciós ár → áthúzott eredeti + piros akciós ár + "AKCIÓ" badge
- [x] Rendezés oszloponként (dátum/nézettség/ár, asc/desc) – sort bar GET paraméterrel
- [x] Képszám badge (galéria képek darabszáma) a cím mellett
- [x] Lejárat-jelző: ha ≤7 nap van hátra (sárga) vagy ≤3 nap (piros)
- [x] ↑ Frissítés gomb aktív hirdetéseknél (lista tetejére tol, AJAX)
- [x] Profil teljességi sáv (completeness % + item check lista)
- [x] Trust badge-ek: e-mail, telefon, aktív hirdető, tagság kora
- [x] Minden CSS + JS beépítve a dashboard template-be
- [x] Plugin tükör szinkron + Deploy All – sikeres

### Hol tartunk
A `/va-fiok/` dashboard teljeskörű CRM eszköz: tömeges műveletek, árkezelés, akciós ár, profil teljességi visszajelzés, bizalmi badge-ek.

---



### Mit csinaltunk [x]
- [x] A `/va-fiok/` Hirdetéseim tab tetejere teljesitmeny iranyitopult kerult
- [x] KPI blokkok: osszes hirdetes, osszes valid megtekintes, atlagos megtekintes/hirdetes, atlagos ar, top hirdetes, 7/30 napos aktivitas, kapcsolodo mutatok
- [x] Statusz-megoszlas savdiagram jellegu vizualizacio (aktiv, jovahagyas, piszkozat, privat/szunet, limit miatti leallitas)
- [x] Csomagkihasznaltsag progress sav megjelenites (ha van limit)
- [x] Top 5 hirdetes lista valid megtekintes szerint
- [x] Kiemelt/Boost/Uj pill aktiv darabszam badge-ek
- [x] Teljes funkcio atvezetve source + plugin mirror dashboard sablonba
- [x] Hibavizsgalat lefuttatva az erintett fajlokon (nincs uj hiba)

### Hol tartunk
A felhasznaloi fiok Hirdetéseim nezet mar CRM-szeru statisztikakat ad, valid (nyers `va_views`) adatokkal, gyors dontestamogatashoz.

---

## 2026. 05. 07. – Session #193 (Fiókoldal: megtekintés szem ikon + pill)

### Mit csinaltunk [x]
- [x] A `/va-fiok/` Hirdetéseim tablaban a valid megtekintes most mar kis pillben jelenik meg
- [x] A pill kapott kis szem ikont es kiemelt (pirosas) megjelenest
- [x] Modositas atvezetve source + plugin mirror dashboard sablonba
- [x] Hibavizsgalat lefuttatva az erintett fajlokon (nincs uj hiba)

### Hol tartunk
A fiok oldalon a hirdetesek melletti megtekintes szam most vizualisan pill badge-ben, szem ikon mellett lathato.

---

## 2026. 05. 07. – Session #192 (Fiókoldal: valid megtekintés oszlop)

### Mit csinaltunk [x]
- [x] A sajat fiok Hirdetéseim tablajaban uj oszlop: `Megtekintés`
- [x] Az oszlop erteke a nyers `va_views` meta (adminnal egyezo, valid szam)
- [x] Modositas atvezetve source + plugin mirror dashboard sablonba
- [x] Hibavizsgalat lefuttatva az erintett fajlokon (nincs uj hiba)

### Hol tartunk
A `/va-fiok/` oldalon a sajat feltoltott hirdetesek mellett most mar a valos, admin oldallal megegyezo megtekintes latszik.

---

## 2026. 05. 07. – Session #191 (Megtekintes szam stabilitas IP alapon)

### Mit csinaltunk [x]
- [x] A frontend megtekintes kijelzes megtartotta a magas (base + valos) logikat
- [x] Uj IP helper kerult be (`va_client_ip`) proxy fejlecek tamogatasaval
- [x] Uj IP+post alapu high-water kulcs es alkalmazas kerult be (`va_views_floor_key`, `va_apply_views_floor`)
- [x] `va_display_views()` most mar sosem ad vissza kisebb erteket ugyanannak az IP-nek ugyanarra a hirdetesre (30 napos transient)
- [x] `va_increment_views` AJAX valasz kibovitve `display_views` mezovel
- [x] Modositasok atvezetve source + plugin mirror fajlokba
- [x] Hibavizsgalat lefuttatva az erintett fajlokon (nincs uj hiba)

### Hol tartunk
Ugyanaz az IP ugyanarra a hirdetesre visszanezeskor nem lat kisebb megtekintes szamot, mint amit korabban mar latott.

---

## 2026. 05. 06. – Session #190 (Hero szoveg mobil/desktop finomitas)

### Mit csinaltunk [x]
- [x] Fooldali hero cim atallitva: `Weingartner Autó` / `Autó-Motor` / `Értékesítés`
- [x] Hero alcim atallitva: `Veszprém`
- [x] A masodik cimsor tobb soros megjelenitesre allitva (`nl2br`)
- [x] Mobilos masodik sor elrejtes visszavonva, hogy a teljes szoveg latszodjon
- [x] Hero cim, kiemelt span es alcim szine fix feherre allitva
- [x] Hero ket CTA gomb elrejtve mobilon
- [x] Mobil hero fuggoleges gap csokkentve (min-height, padding, marginok)
- [x] Mobilon a badge alatti gap a cim-es-alcim gaphez igazitva
- [x] Badge alatti mobil gap vizualisan tovabb szukitve (10px -> 6px)
- [x] Badge alatti mobil gap visszanovelve +5px-re (6px -> 11px)
- [x] Badge alatti mobil gap lathatoan megnovelve (11px -> 18px)

### Hol tartunk
A hero szoveg most minden nezetben a kert tartalommal jelenik meg, a cim 3 sorra torheto, az alcim `Veszprém`.

---

## 2026. 05. 06. – Session #189 ("Új" pill: auto 7 nap + dashboard toggle)

### Mit csinaltunk [x]
- [x] Uj meta bevezetve: `va_new_pill_time` (uj hirdetesnel automatikusan beall)
- [x] Uj logika: `is_new_pill()` 7 napos fix ablakban jelzi az aktiv "Új" pillt
- [x] Uj AJAX vegpont: `va_toggle_new_pill` (KI/BE kapcsolas sajat hirdetesen)
- [x] Kartyanezetben uj badge megjelenites: `Új` (piros hatter, feher szoveg)
- [x] Dashboardban azonos helyen uj toggle gomb: "Új pill: BE/KI"
- [x] Modositasok atvezetve source + plugin mirror fajlokba
- [x] Hibavizsgalat lefuttatva (PHP/template oldalon nincs uj hiba)

### Hol tartunk
Minden ujonnan feltoltott hirdetes alapbol kap "Új" pillt 7 napra, es a `/va-fiok/` oldalon ugyanott kapcsolhato KI/BE, ahol a kiemeles toggle is van.

---

## 2026. 05. 06. – Session #188 (Boost pill: 7 nap + dashboard toggle levetel)

### Mit csinaltunk [x]
- [x] Boost pill ablak default/fallback 14 naprol 7 napra allitva (source + plugin mirror, index + ajax + admin defaults)
- [x] `va_boost_listing` AJAX vegpont toggle-ra bovitve: kiemeles felrakasa es levetele ugyanazzal a gombbal
- [x] Platinum/admin jogosultsaggal barmikor leveheto a pill (`va_boost_time` torles)
- [x] Fiók dashboard boost gomb UX atdolgozva: `Kiemelés` ↔ `Kiemelt! Levétel` allapotok
- [x] Modositasok atvezetve source + plugin mirror sablonokra es classokra
- [x] Hibavizsgalat lefuttatva az erintett fajlokon (nincs uj hiba)

### Hol tartunk
A kiemeles pill alapertelmezett ablaka 7 nap, es platinum/admin felhasznalo a `/va-fiok/` oldalon mar barmikor le is tudja venni a pillt ugyanazzal a gombbal.

---

## 2026. 05. 04. – Session #187 (Publikus olvaso mod: feher szoveg + fiok/feladas linkek kivezetese)

### Mit csinaltunk [x]
- [x] Fejlecbol eltavolitva a bejelentkezes / regisztracio / hirdetesfeladas gombok
- [x] Lablec fiok oszlop egyszerusitve olvaso modra (csak bongeszes link maradt)
- [x] Sugo oldal atirva publikus olvaso uzemre (fiok/feltoltes funkciok nem elerhetoek)
- [x] Frontend CSS-ben bekapcsolva: minden lathato szoveg feher
- [x] Hibavizsgalat lefuttatva az erintett fajlokra (PHP oldalon nincs uj hiba)

### Hol tartunk
A publikus feluleten a latogato mar csak olvas es bongeszik: a fiokhoz/feltolteshez tartozo elemek kivezetve, a szovegek feherre kenyszeritve.

---

## 2026. 05. 04. – Session #186 (Fejlec + kartya szinek visszaallitasa, admin szinallitas helyreallitva)

### Mit csinaltunk [x]
- [x] Eltavolitva a fejlécet es tipografiat `!important`-tel kenyszerito CSS blokk a theme style fajlbol
- [x] Eltavolitva a globalis, admin beallitasokat feluliro vizualis reset (minden elem feher/14px kenyszer)
- [x] Ellenorizve: maradtak csak normal tematikus szabalyok, amelyek mellett az admin szinbeallitasok ujra ervenyesulhetnek

### Hol tartunk
A fejléc es termekkartya szinek visszaalltak a normal tematikus mukodesre, az adminbol torteno szinallitas ujra mukodokepes.

---

## 2026. 05. 04. – Session #185 (Branding fix validalas + allapot rogzites)

### Mit csinaltunk [x]
- [x] Ellenoriztuk a legfrissebb commitot: a domain-branding javitasok benne vannak (`admin/class-admin.php`, `admin/class-settings-page.php`, `footer.php`, plugin mirror + theme mirror)
- [x] Hibavizsgalat lefuttatva az erintett core/theme/admin fajlokra (nincs uj hiba)
- [x] Gyors szovegellenorzes lefuttatva a lathato fejléc/lablec branding fallback pontokra
- [x] Maradek lathato admin felirat csere: `VadászApró – Form szerkesztő` -> `weingartnerauto.hu – Form szerkesztő` (source + plugin mirror)

### Hol tartunk
A szigoru branding korhoz tartozo valtoztatasok commitolva es fenn vannak a tavoli agban, a kod allapot stabil (nincs uj PHP hiba).

---

## 2026. 05. 04. – Session #184 (Vegleges branding + feher szoveg + 14px + Sugo URL fix)

### Mit csinaltunk [x]
- [x] Brand kovetelmeny szerint globalis szovegcsere-szuro hozzaadva: `Vadaszapro`/`VadászApró`/`Weingartner Auto` -> `weingartnerauto.hu`
- [x] Header brand default atallitva domain alakura (`weingartnerauto.hu`) legacy ertekek automatikus felulirassal
- [x] Lablec `Sugo` link fixen a `/sugo/` URL-re allitva (mindket footer fajlban)
- [x] Frontend globális tipografia es szin override: minden szoveg feher + 14px, visszafogottabb vastagsag
- [x] Sugo oldal cimben a maradek `Vadaszapro` csere `weingartnerauto.hu` ertekre
- [x] Modositasok atvezetve source + mirror fajlokba

### Hol tartunk
A lathato feluleten a branding domain alapu (`weingartnerauto.hu`), a Sugo link fix URL-re mutat, a szovegek globálisan feherek es 14px alapra kenyszeritettek.

---

## 2026. 05. 04. – Session #183 (Weingartner rebrand + olvashatosagi tuning)

### Mit csinaltunk [x]
- [x] Admin felulet lathato branding csere: `Vadaszapro` -> `Weingartner Auto` (topbar/panel cimek/default feliratok)
- [x] Beallitas oldalak (`class-settings-page`) default branding atallitasa Weingartner nevre
- [x] Footer branding default csere Weingartnerre + legacy ertek automatikus atiranyitas render idoben
- [x] Frontend vizualis reset: narancs-kek hangsuly, kontrasztosabb header, 14px alap betumeret, visszafogottabb font-weight a fejlec/nav elemeken
- [x] Modositasok atvezetve source + deployolt mirror fajlokba (theme + plugin)
- [x] Hibavizsgalat lefuttatva az erintett PHP fajlokra (nincs uj hiba)

### Hol tartunk
A lathato feluletek arculata Weingartner iranyba fordult: olvashatobb fejléc, kisebb es nyugodtabb tipografia, narancs-kek hangsulyokkal.

---

## 2026. 05. 04. – Session #182 (/sugo 404 fix: garantalt route)

### Mit csinaltunk [x]
- [x] Okazonositas: a `/sugo` URL 404 volt, mert nem mindenhol letezik kulon WordPress oldal ehhez a slughoz
- [x] Javitas: tema szintu route kezelo hozzaadva (`template_redirect`), ami `/sugo` kereskor direkt a `page-sugo.php` template-et rendereli
- [x] Modositas atvezetve source + theme mirror `functions.php` fajlba
- [x] Hibavizsgalat lefuttatva (nincs uj hiba)

### Hol tartunk
A `/sugo` URL most mar oldal letrehozas nelkul is mukodik, es a teljes Sugo tartalom jelenik meg 404 helyett.

---

## 2026. 05. 04. – Session #181 (Sugo link: tenyleges oldal URL automatikus feloldasa)

### Mit csinaltunk [x]
- [x] Okazonositas: a footer Sugo link fix `/sugo` fallbackre mutatott, ami nem minden telepitesen letezo slug
- [x] Javitas: a footer mar eloszor slug alapjan, utana template (`page-sugo.php`) alapjan keresi meg a valos publikus oldalt
- [x] Csak akkor fallbackel gyoker URL-re, ha semmilyen Sugo oldal nem talalhato
- [x] Modositas atvezetve source + theme mirror footer fajlba
- [x] Hibavizsgalat lefuttatva (nincs uj hiba)

### Hol tartunk
A lablec Sugo link most a tenylegesen letezo Sugo oldalra mutat, igy nem ures/hibas URL nyilik meg slug-elteres eseten sem.

---

## 2026. 05. 04. – Session #180 (Footer Sugo link visszaallitas)

### Mit csinaltunk [x]
- [x] Azonositas: a `Sugo` label be volt olvasva footer optionbol, de a jogi oszlopban nem volt kirenderelve
- [x] Javitas: kulon `Sugo` link visszaadasa a footer jogi oszlopaban
- [x] Modositas atvezetve source + theme mirror fajlba
- [x] Hibavizsgalat lefuttatva (nincs uj hiba)

### Hol tartunk
A `Sugo` menupont ujra megjelenik a lablecben, es a `/sugo` oldalra mutat (ha a `sugo` page megtalalhato, annak permalinkjet hasznalja).

---

## 2026. 05. 04. – Session #179 (Sugo oldal: teljes ugyfel-utmutato + design frissites)

### Mit csinaltunk [x]
- [x] A footerbol elerheto `Sugo` oldal teljes tartalmanak ujrairasa ugyfel-szempontu hasznalati utasitasra
- [x] A leiras kibovitese minden publikus, latogatok altal lathato funkcioval (kereses, szures, hirdetes adatlap, aukcio, fiok, hirdetes-feladas, kedvencek)
- [x] Vizuális frissites: ero­sebb hero blokk, reszletes tematikus kartyak, gyorslinkek, callout szekcio
- [x] Modositas atvezetve source + theme mirror fajlba (`page-sugo.php` mindket helyen)
- [x] Hibavizsgalat lefuttatva az erintett ket fajlra (nincs uj hiba)

### Hol tartunk
A Sugo oldal most teljes, atfogo es ugyfelbarat hasznalati utmutato, amely csak olyan funkciokat es adatokat ir le, amiket a felhasznalo a feluleten is lat.

---

## 2026. 05. 03. – Session #178 (ÉLES SZERVER – SITE ÉL + HOLNAPI TERV)

### Mit csinaltunk [x]
- [x] All-in-One WP Migration Pro segítségével exportálva a LocalWP site
- [x] Import sikeresen lefutott az éles szerverre
- [x] **https://www.weingartnerauto.hu/ — ÉL, nyilvánosan elérhető!**
- [x] Minden változás pusholva gitbe

### Hol tartunk
A site éles, nyilvános. Minden funkció (hirdetésfeladás, aukció, admin panel) telepítve.

### ⚡ HOLNAP ELŐSZÖR EZT CSINÁLJUK (FONTOS!)
**GitHub Actions FTP auto-deploy beállítása:**
1. Létrehozzuk a `.github/workflows/deploy.yml` fájlt
2. GitHubon Settings → Secrets → Actions-be felvisszük: FTP_SERVER, FTP_USERNAME, FTP_PASSWORD, és az elérési utakat
3. Ezután: minden `git push` → automatikusan frissül az élő szerver
4. **Többé nem kell All-in-One Migration** — csak push és kész!

### Munkamód váltás
- **LocalWP KUKA** — mostantól csak az élő szerveren dolgozunk
- Workflow: szerkesztés VS Code-ban → deploy script → élő szerverre megy azonnal

---

## 2026. 05. 03. – Session #177 (Teljes git szinkron + hordozhato allapot megerositese)

### Mit csinaltunk [x]
- [x] Teljes branch szinkron rendezve: rebase a tavoli `main`-re
- [x] Rebase konfliktus feloldva a `NAPLO.md` fajlban
- [x] Minden lokalis commit pusholva a tavoli tarhelyre
- [x] Vegallapot ellenorizve: lokalis `main` = `origin/main` (nincs elteres)

### Hol tartunk
A teljes aktualis allapot fent van gitben, es uj WordPress telepitesnel a plugin/theme kod ugyanebben a formaban telepitheto. A plugin gyari beallitasokat is betolt (`factory-defaults`) uj telepitesnel.

---

## 2026. 05. 03. – Session #176 (Email mindig ON + helység mindig kitöltve)

### Mit csinaltunk [x]
- [x] Frontend AJAX `submit` és `update` mentésnél kényszerítve: `va_email_show = '1'`
- [x] Frontend AJAX `submit` és `update` mentésnél üres helység esetén default: `Veszprém Gyulafirátót`
- [x] Admin mentésnél (`handle_save`) kényszerítve: `va_email_show = '1'`
- [x] Admin mentésnél üres `va_location` esetén default: `Veszprém Gyulafirátót`
- [x] Admin UI-ban az email checkbox fixen bepipált (nem kapcsolható ki)
- [x] Admin UI-ban a helység mező alapértéke megjelenik, ha üres volt
- [x] Frontend submit/edit UI-ban az email checkbox fixen bepipált (nem kapcsolható ki)
- [x] Változtatások tükrözve source + mirror fájlokba
- [x] Hibavizsgálat lefuttatva (nincs új hiba)
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
Az e-mail megjelenítés mostantól minden mentésnél kötelezően bekapcsolt, és a helység sem maradhat üres: automatikusan `Veszprém Gyulafirátót` lesz.

---

## 2026. 05. 03. – Session #175 (Admin szerkesztő: frontend-forma igazítás, duplikált blokkok törlése)

### Mit csinaltunk [x]
- [x] Az admin szerkesztőben frontend-szerű vizuális felülírások bekerültek (mezők, címkék, 2 oszlopos rács, piros fókusz, checkbox stílus)
- [x] Új `Alap adatok` blokk felülre: `Járműkategória`, `Állapot`, `Helyszín (város)`
- [x] A `Kapcsolat` blokkból a `Helység` duplikáció kivezetve (helyette felül az `Alap adatok` részben)
- [x] A bent maradt régi, duplikált `Típusfüggő` + régi `Extra felszereltség` blokk teljesen eltávolítva
- [x] A nem-jármű generikus ágban az `array_filter(..., ARRAY_FILTER_USE_KEY)` kiváltva kompatibilis foreach megoldásra
- [x] Módosítás átvezetve source + root mirror fájlokra
- [x] Hibavizsgálat lefuttatva az érintett két fájlra, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
Az admin meglévő hirdetés szerkesztő oldal közelebb került a képeken mutatott frontend feladási formához, és a korábbi duplikált/széteső blokkok megszűntek.

### TODO
- [ ] Frontend/admin vizuális ellenőrzés: a szerkesztő oldalon az elrendezés és a szekció-sorrend pixelpontos finomhangolása a képekhez

---

## 2026. 05. 03. – Session #174 (Hirdetés állítás név + 2–10 desktop rács + fix 4 oszlop feloldás)

### Mit csinaltunk [x]
- [x] A bal oldali admin menüben átnevezve a gyorspont: `Főoldali rács` -> `Hirdetés állítás`
- [x] A `vadaszapro-hirdetes` menüpont címkéi egységesítve `Hirdetés állítás` névre
- [x] Desktop oszlopszám tartomány emelve `2..10`-re (Layout Állító + Hirdetések oldali mezők)
- [x] A theme dinamikus CSS kiterjesztve `.vcp-grid`-re is (`.va-grid,.vcp-grid`), így ugyanaz a beállítás vezérli mindkét rácsot
- [x] A fixen 4 oszlopos `.vcp-grid` szabály eltávolítva, ami felülírta a beállításokat
- [x] Módosítások átvezetve source + mirror fájlokba és deployolva LocalWP-re

### Hol tartunk
A desktop kártyaszám már nem fix 4: a beállított `2..10` érték érvényesül a fő rácsoknál is.

---

## 2026. 05. 03. – Session #173 (Admin új hirdetés = frontend feladási form)

### Mit csinaltunk [x]
- [x] Az admin `Új hirdetés` oldalon (`vadaszapro-listing-edit` új elemnél) a saját admin form helyett ugyanaz a frontend feladási form renderelődik, mint a főoldali CTA által nyitott feladási oldalon
- [x] Beillesztés shortcode-val: `[va_submit_listing]`
- [x] Módosítás átvezetve source + plugin mirror fájlokba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
Az admin új hirdetés felület és a frontend hirdetés-feladás ugyanazt a form template-et használja.

---

## 2026. 05. 03. – Session #172 (Fejléc logó méret: valós gyökérok és javítás)

### Mit csinaltunk [x]
- [x] Ellenőrizve élő DOM-ban a fejléc logó számított mérete (Playwright): az inline `height:100px` beállítás tényleg érvényesült
- [x] Azonosítva a gyökérok: a frontendben ténylegesen használt elem classa `va-logo__img--icon`, nem `va-logo__img--header`
- [x] Javítva a rossz classra célzott méretkorlát: `.va-logo__img--icon` most `max-width: none; height: auto;`
- [x] Módosítás átvezetve mindkét példányba (`style.css` + theme mirror)
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re
- [x] Visszaellenőrzés élő oldalon: számított szélesség már nem 120px, hanem ~320px (100px magasságnál)

### Hol tartunk
A fejléc logó méretezése most már láthatóan reagál a `Logó magasság (px)` beállításra.

---

## 2026. 05. 03. – Session #171 (Fejléc logóméret mentés + viselkedés javítás)

### Mit csinaltunk [x]
- [x] `Fejléc + Lábléc` oldalon a `Logó magasság (px)` mező mentése javítva (`va_header_logo_height` bekerült a megfelelő settings csoportba)
- [x] A fejléc logó méretkorlát visszavéve, hogy a mező valósan hasson a logóra
- [x] Javítás átvezetve source + theme mirror fájlokra
- [x] Deploy lefuttatva LocalWP-re

### Hol tartunk
A fejléc logó méret most már ténylegesen változik mentés után.

---

## 2026. 05. 02. – Session #170 (Jogi tartalom-kezelés adminból + automatikus oldal/link szinkron)

### Mit javitottunk [x]
- [x] Az `Adatvédelem + ÁSZF` admin fül URL mezői tartalom-szerkesztő mezőkre váltva (7 jogi blokk)
- [x] Új opciók bevezetve: `va_legal_content_*` kulcsok
- [x] Sanitizer bővítve: jogi tartalom mezők `wp_kses_post` tisztítással mentődnek
- [x] Automatikus jogi oldal szinkron készült mentés után (`sync_legal_pages_from_options`):
  - kitöltött tartalomnál oldal létrehozás/frissítés
  - üres tartalomnál a megfelelő jogi URL opció törlése
- [x] Lábléc viselkedés ezzel teljesíti a kérést: csak azok a jogi tételek jelennek meg, amelyekhez tényleges adat van
- [x] Módosítás átvezetve source + plugin mirror fájlba
- [x] Hibavizsgálat lefutott az érintett fájlokra, nincs új hiba

### Hol tartunk
A jogi oldalak tartalma most közvetlenül az admin felületen szerkeszthető, és a lábléc Jogi oszlop automatikusan csak a kitöltött tételeket mutatja.

### TODO
- [ ] Admin/frontend gyors ellenőrzés: 1-1 jogi tartalom mentése után validáld az oldalgenerálást és a lábléc linkek megjelenését

---

## 2026. 05. 02. – Session #169 (Hotfix: jogi link mentés utáni szétesés)

### Mit javitottunk [x]
- [x] Jogi URL mezőkre külön sanitize callback készült: `sanitize_legal_url`
- [x] Csak `http/https` abszolút link vagy normalizált relatív útvonal kerül mentésre
- [x] Lábléc jogi link renderelés megerősítve (`wp_strip_all_tags`, URL ellenőrzés, üres/hibás URL kihagyás)
- [x] Kategória blokkban `is_wp_error` guard hozzáadva a stabilitásért
- [x] Javítás átvezetve source + plugin/theme mirror fájlokba
- [x] Hibavizsgálat lefutott, nincs új hiba
- [x] Deploy lefutott LocalWP-re (`Deploy All`)

### Hol tartunk
A jogi link mentés most védettebb, hibás vagy félresikerült URL bemenet nem boríthatja szét a footer kimenetet.

---

## 2026. 05. 02. – Session #168 (Adatvédelem + ÁSZF admin fül + dinamikus lábléc jogi linkek)

### Mit csinaltunk [x]
- [x] Új admin menüpont készült: `Adatvédelem + ÁSZF` (`vadaszapro-legal`)
- [x] Új szerkeszthető mezők kerültek be a beállításokba (URL alapon):
  - Adatvédelem
  - ÁSZF
  - Impresszum
  - Etika és Üzleti Magatartási Kódex
  - Sütik
  - GDPR Adatkezelési Tájékoztató
  - Fenntartható Fejlődés Irányelve
- [x] A lábléc jogi oszlopa dinamikus lett: csak azok a linkek jelennek meg, amelyekhez van kitöltött URL
- [x] Módosítás átvezetve a plugin és theme mirror fájlokba is
- [x] Hibavizsgálat lefuttatva, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A jogi oldalak kezelése külön admin menüből szerkeszthető, és a láblécben automatikusan csak a kitöltött tételek jelennek meg.

### TODO
- [ ] Admin ellenőrzés: a `vadaszapro-legal` oldalon töltsd ki a kívánt URL-eket, majd vizuálisan validáld a lábléc listát frontend oldalon

---

## 2026. 05. 02. – Session #167 (Elérhetőségeink kártya: pontos 3 soros kontakt)

### Mit csinaltunk [x]
- [x] Az `Elérhetőségeink` kártya fallback szövege átállítva pontosan erre:
  - `Cím: 8412 Veszprém, Alsó-Újsor utca 31.`
  - `Telefon: 06209438636`
  - `Email: weingartnertrans@gmail.com`
- [x] A kártya szöveg renderelése javítva: a sortörések most ténylegesen megjelennek (`nl2br`)
- [x] Módosítás átvezetve a theme mirror fájlba is
- [x] LocalWP DB opció (`va_contact_card1_text`) frissítve valódi sortörésekkel
- [x] Hibavizsgálat lefuttatva, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
Az `Elérhetőségeink` kártyában a három adat külön sorban jelenik meg a kért sorrendben.

### TODO
- [ ] Frontend gyors vizuális ellenőrzés: kártya tördelés desktop + mobil nézetben

---

## 2026. 05. 02. – Session #166 (Lábléc kontakt formátum + logó pozíció finomítás)

### Mit csinaltunk [x]
- [x] A lábléc kontakt blokk átállítva a kért formára és sorrendre:
  - `Cím: 8412 Veszprém, Alsó-Újsor utca 31.`
  - `Telefon: 06209438636`
  - `Email: weingartnertrans@gmail.com`
- [x] A LocalWP adatbázis opciók frissítve a fenti exact értékekre (`va_billing_company_address`, `va_billing_phone`, `va_contact_email`)
- [x] A lábléc logó balra húzva, hogy vizuálisan egy vonalba kerüljön a mellette/alatta lévő szövegekkel
- [x] Módosítások átvezetve a theme mirror fájlokba is
- [x] Hibavizsgálat lefuttatva az érintett fájlokra, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A láblécben a kapcsolati adatok a kért pontos formában és sorrendben jelennek meg, a logó pedig balra igazítottabb pozíciót kapott.

### TODO
- [ ] Frontend ellenőrzés: a logó balra húzása minden nézetben esztétikus-e (desktop + mobil)

---

## 2026. 05. 02. – Session #165 (Logó teljes balra + leíró szöveg fehér)

### Mit csinaltunk [x]
- [x] A header logó teljesen balra igazítva (desktop + mobil padding bal oldalon lenullázva)
- [x] A logó alatti/leíró szöveg (`va_site_description`), pl. `Auto-Morotor Kereskedelem`, fehér színre állítva
- [x] Módosítás átvezetve a theme mirror fájlokba is
- [x] Hibavizsgálat lefuttatva az érintett fájlokra, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A logó most teljesen balra került, és a kért leíró szöveg fehéren jelenik meg.

### TODO
- [ ] Frontend gyors ellenőrzés: header és footer elrendezés validálása desktop + mobil nézetben

---

## 2026. 05. 02. – Session #164 (Email + Cím külön sorba)

### Mit csinaltunk [x]
- [x] A kapcsolat blokk szövege átállítva külön soros formára:
  - Email: weingartnertrans@gmail.com
  - Cím: 8412 Veszprém, Alsó-Újsor utca 31.
- [x] Módosítás átvezetve a theme mirror oldal sablonba is
- [x] LocalWP adatbázis opció (`va_contact_card1_text`) frissítve valódi sortöréssel
- [x] Hibavizsgálat lefuttatva, nincs új hiba az érintett fájlokban
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A kért Email és Cím tartalom külön sorban jelenik meg a kapcsolat oldalon.

### TODO
- [ ] Frontend gyors ellenőrzés: a sortörés mobil nézetben is szépen törik-e

---

## 2026. 05. 02. – Session #163 (Hiányzó cím javítás + kapcsolati adatok a láblécben)

### Mit csinaltunk [x]
- [x] Javítva a hiányzó cím a kapcsolat szövegben: `8412 Veszprém, Alsó-Újsor utca 31.`
- [x] A láblécbe bekerült egy külön `Kapcsolat` blokk releváns adatokkal:
  - e-mail (`va_contact_email`)
  - telefonszám (`va_billing_phone`)
  - cím (`va_billing_company_address`)
- [x] A footer módosítás átvezetve a theme mirror fájlba is
- [x] LocalWP adatbázis opció frissítve (`va_contact_card1_text`), hogy a teljes cím biztosan megjelenjen
- [x] Hibavizsgálat lefuttatva, nincs új hiba az érintett fájlokban
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A kapcsolat cím biztosan benne van, és a lábléc mostantól tartalmazza a releváns kapcsolati adatokat is.

### TODO
- [ ] Frontend ellenőrzés: lábléc kontakt adatok megjelenésének gyors vizuális validációja desktop + mobil nézetben

---

## 2026. 05. 02. – Session #162 (Kapcsolat oldal: teljes adatfrissítés + szélesség igazítás)

### Mit csinaltunk [x]
- [x] A kapcsolat oldalon a hero alatti blokkok távolsága növelve (+20px)
- [x] A hero alatti teljes blokk szélessége a videó/hero szélességéhez igazítva
- [x] A kontakt fallback szövegek frissítve valós Weingartner Trans adatokra (cím, e-mail, telefon, nyitvatartás)
- [x] A factory defaults frissítve mindkét példányban (forrás + plugin mirror)
- [x] LocalWP adatbázis opciók közvetlenül frissítve (MySQL kliensen keresztül), így azonnal látható az új tartalom
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A kapcsolat oldal most egységes szélességű blokkokkal és a weingartnertrans.hu releváns elérhetőségi adataival fut.

### TODO
- [ ] Frontend ellenőrzés: kapcsolat oldal teljes vizuális és tartalmi QA desktop + mobil nézetben

---

## 2026. 05. 02. – Session #161 (Helyszín alapérték beállítása)

### Mit csinaltunk [x]
- [x] A `Helyszín (város)` mező alapértéke beállítva: `Veszprém Gyulafirátót`
- [x] Logika úgy készült, hogy meglévő mentett értéket nem ír felül (csak üresnél ad defaultot)
- [x] Változtatás átvezetve a plugin mirror template fájlba is
- [x] Hibavizsgálat lefuttatva, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
Új feladásnál a helyszín mező automatikusan kitöltve indul a kért értékkel.

### TODO
- [ ] Frontend ellenőrzés: új hirdetés + szerkesztés módban is validálni a viselkedést

---

## 2026. 05. 02. – Session #160 (2. telefonszám mező eltávolítása)

### Mit csinaltunk [x]
- [x] A jármű adatlap feladó űrlapból a `2. telefonszám` (`second_phone`) mező eltávolítva
- [x] Változtatás átvezetve a plugin mirror template fájlba is
- [x] Hibavizsgálat lefuttatva, nincs új hiba
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A középen megjelenő másodlagos telefonszám mező már nem látszik a feladási űrlapon.

### TODO
- [ ] Frontend gyors ellenőrzés: a rácselrendezés sorvége vizuálisan rendben van-e

---

## 2026. 05. 02. – Session #159 (Leírás placeholder halvány szürke)

### Mit csinaltunk [x]
- [x] A Quill leírás mező placeholder színe halvány szürkére állítva (`#9a9a9a`)
- [x] A szabály `!important`-tal megerősítve, hogy biztosan érvényesüljön
- [x] Változtatás átvezetve a plugin mirror template fájlba is
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A leírás mező placeholder szövege most halvány szürke, jobban elkülönül a fehér beírt szövegtől.

### TODO
- [ ] Frontend gyors ellenőrzés: kontraszt rendben van-e mobilon is

---

## 2026. 05. 02. – Session #158 (Leírás szerkesztő betűszín fehér)

### Mit csinaltunk [x]
- [x] A Quill leírás szerkesztőben a szövegszín fixen fehérre állítva (`#fff`) `!important` szabállyal
- [x] Kiterjesztett selectorok hozzáadva a belső elemekre (`p`, `span`, `li`, `strong`, `em`, `u`, `s`), hogy gépelés közben se sötétedjen vissza
- [x] Változtatás átvezetve a plugin mirror template fájlba is
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A hirdetésfeladó `Leírás` mezőjében a begépelt szöveg most már jól látható fehér.

### TODO
- [ ] Frontend ellenőrzés: cache ürítés után tesztelni normál + félkövér + lista formázásnál is

---

## 2026. 05. 02. – Session #157 (Megye mező eltávolítása feladási űrlapról)

### Mit csinaltunk [x]
- [x] A hirdetésfeladó űrlapon a `Megye` (`county`) mező renderelése kikapcsolva
- [x] A páros mezőlogikából a `Kategória + Megye` sor eltávolítva
- [x] Csak a `Város / Helyszín` mező marad meg kapcsolódó lokációs adatként
- [x] Változtatás átvezetve a plugin mirror template fájlba is
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A feladási oldalon a `Megye` mező már nem jelenik meg, a város mező használható önállóan.

### TODO
- [ ] Rövid UI ellenőrzés: spacing és sorok rendben vannak-e desktop + mobil nézetben

---

## 2026. 05. 02. – Session #156 (Email checkbox legalul + Egyéb mindig utolsó)

### Mit csinaltunk [x]
- [x] A feladó űrlapon az `E-mail megjelenítése` checkbox kivéve a középső Kapcsolat sorból, és külön blokkban a submit gomb elé helyezve
- [x] A `va_category` listában az `egyeb` slugú kategória explicit a lista végére rendezve
- [x] Edit módban az email checkbox állapota most már a mentett `va_email_show` értéket veszi figyelembe
- [x] Módosítások átvezetve a plugin mirror template fájlba is
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A kapcsolati checkbox most a hirdetésfeladás legalján jelenik meg a küldés gomb előtt, és a járműkategória listában az `Egyéb` opció mindig utolsó.

### TODO
- [ ] Frontend ellenőrzés: cache ürítés után vizuális validáció mobil + desktop nézetben

---

## 2026. 05. 02. – Session #155 (Járműkategória összevonás feladó űrlapon)

### Mit csinaltunk [x]
- [x] A felső kategória adatforrása bővítve a korábbi lenti járműkategória elemeivel (Személyautó, Kisteherautó, Teherautó, Lakóautó/Camper, Busz/Kisbusz, Egyéb)
- [x] A jármű kategória dataset verzió emelve (`hasznaltauto-2026-05-02`), hogy a taxonómia automatikusan újraszinkronizálódjon
- [x] A hirdetés feladó űrlapon a külön, lenti `Járműkategória` blokk eltávolítva
- [x] Módosítások szinkronizálva a plugin mirror fájlokba is

### Hol tartunk
A jármű kategória választás most egységes: a felső kategória mező tartalmazza a szükséges opciókat, az alsó duplikált blokk megszűnt.

### TODO
- [ ] LocalWP ellenőrzés: feladás űrlapon vizuálisan validálni, hogy csak egy kategória mező maradt és az új opciók láthatók

---

## 2026. 05. 02. – Session #154 (Teljes mentes: repo + local)

### Mit csinaltunk [x]
- [x] Teljes allapot ellenorizve git oldalon (HEAD, origin/main, status)
- [x] Session allapot naplozva a fejlesztesi naploba
- [x] LocalWP deploy kor lefuttatasa (`Deploy All`)
- [x] Minden valtozas commit + push a GitHub repoba

### Hol tartunk
A kod allapot most szinkronban van helyben, LocalWP-ben es a tavoli repoban is.

### TODO
- [ ] Clean install smoke teszt kulon korben (friss, ures WP peldanyon)

---

## 2026. 05. 02. – Session #153 (3 minta termek kepestol a tema packba)

### Mit csinaltunk [x]
- [x] 3 db demo kep bekerult a csomagba:
  - `assets/demo/demo-auto-1.svg`
  - `assets/demo/demo-auto-2.svg`
  - `assets/demo/demo-auto-3.svg`
  - valamint a theme pack mirrorban is: `wp-theme/vadaszapro-theme/assets/demo/*`
- [x] Uj telepiteshez automatikus demo hirdetes seed keszult:
  - `vadaszapro-core.php` es `wp-plugin/vadaszapro-core/vadaszapro-core.php`
  - 3 db `va_listing` minta hirdetes jon letre, ha meg nincs egyetlen hirdetes sem
  - minta adatok: cim, ar, marka, modell, evjarat, varos, kategoria, megye, allapot
- [x] Kartyas nezetben demo kep fallback bekerult (`va_demo_image` meta alapjan):
  - `frontend/templates/listing/card.php`
  - `wp-plugin/vadaszapro-core/frontend/templates/listing/card.php`
- [x] Single oldalon demo kep fallback bekerult:
  - `single-va_listing.php`
  - `wp-theme/vadaszapro-theme/single-va_listing.php`
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re

### Hol tartunk
A tema pack most tartalmazza a kert 3 minta termeket kepes megjelenitessel egyutt. Friss telepitesen, ures adatbazisnal automatikusan bejon a 3 demo hirdetes.

### TODO
- [ ] Clean install smoke teszt: ellenorizni, hogy ures WP peldanyon aktivalas utan azonnal latszik mindharom minta hirdetes

---

## 2026. 05. 02. – Session #152 (Szentgrál induló teendőlista)

### Mit csináltunk [x]
- [x] Létrehozva az induláskori referencia teendőlista: `MI_A_MEG_A_TEENDO.md`
- [x] A kész elemek piros, áthúzott formátumban kerültek megjelenítésre a fájlban
- [x] A hátralévő elemek külön, számozott "Még teendő" blokkban szerepelnek
- [x] Beállítva induláskori workflow: mindig ebből a fájlból történjen a napi állapotellenőrzés

### Hol tartunk
A projektnek most van egy fix, vizuálisan egyértelmű állapotlistája, ahol azonnal látszik mi kész és mi van még hátra a 100% profi / ThemeForest szinthez.

### TODO
- [ ] Minden session végén a változásokat visszavezetni a `MI_A_MEG_A_TEENDO.md` fájlba
- [ ] Kész pontok folyamatos áthúzása pirossal, hogy a haladás azonnal látható legyen

---

## 2026. 05. 02. – Session #151 (Fresh install garancia + repo zárás)

### Mit csináltunk [x]
- [x] Fresh install bootstrap ellenőrizve `vadaszapro-core.php` és `wp-plugin/vadaszapro-core/vadaszapro-core.php` fájlokban:
  - aktiváláskor fut a `va_load_factory_defaults()`
  - futás közbeni `init` fallback is megvan (`va_factory_defaults_loaded` guarddal)
- [x] Ellenőrizve a kategória seed útvonal:
  - `includes/class-taxonomy.php` továbbra is dataset-verzió alapján szinkronizálja a `va_category` taxonómiát
  - friss installnál a `VA_Vehicle_Catalog` kategóriák kerülnek be, és `va_site_type` => `jarmu`
- [x] Deploy lefuttatva (`Deploy All`) LocalWP-re
- [x] Repo állapot validálás megtörtént (`git status`, branch ellenőrzés)

### Hol tartunk
Új telepítésnél két rétegben biztosított az egységes indulás:
1) Aktiváláskor betöltődnek a gyári opciók és taxonómia adatok.
2) Ha bármilyen okból az aktiválási lépés kimaradna, az első futásnál az `init` fallback automatikusan pótolja.

Ez stabilabbá teszi az "új helyre telepítem, ugyanazt kapjam" elvárt működést.

### TODO
- [ ] LocalWP smoke teszt: teljesen üres WP példányon plugin aktiválás + kategória/design ellenőrzés
- [x] Session napló frissítve

---
## 2026. 05. 02. – Session #150 (Részletek: csak scrollbar animáció)

### Mit csináltunk [x]
- [x] A korábbi "Huzd le" scroll hint overlay teljesen eltávolítva
- [x] A `Részletek` blokkban csak a scrollbar thumb kapott animált narancs „lefolyó” effektet
- [x] Animáció: folyamatos vertikális gradient mozgás (akku/töltés jellegű vizuál)
- [x] A kapcsolódó, már felesleges JS hint logika eltávolítva
- [x] Theme mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A jelzés most kizárólag a görgetősávon történik: diszkrét, modern, és a kért narancs flow effektet adja.

---
## 2026. 05. 02. – Session #149 (Részletek scroll animált jelzés)

### Mit csináltunk [x]
- [x] A termékoldali `Részletek` scroll konténerhez animált "Huzd le" jelzés hozzáadva
- [x] A jelzés csak akkor látszik, ha ténylegesen van még lejjebb tartalom
- [x] A jelzés eltűnik, amikor a felhasználó leér a blokk aljára
- [x] Scroll/resize/ResizeObserver figyeléssel dinamikusan frissül az állapot
- [x] Theme mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A Részletek doboz vizuálisan jelzi, hogy folytatódik lefelé a tartalom, így egyértelműbb a további görgetés lehetősége.

---
## 2026. 05. 02. – Session #148 (Részletes kereső lenyitható blokk)

### Mit csináltunk [x]
- [x] A túl magas extra szűrő részt lenyitható panelbe szerveztük a kereső oldalon
- [x] Új nyitó gomb neve pontosan: `Részletes kereső`
- [x] A panel alapállapotban csukva indul (`is-collapsed`), kattintásra nyit/zár
- [x] `aria-expanded` állapot frissül a gombon (akadálymentesebb működés)
- [x] Plugin mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A kereső teteje alacsonyabb lett, a korábbi magas checkbox/rádió blokk csak igény esetén nyílik le a `Részletes kereső` gombra.

---
## 2026. 05. 02. – Session #147 (Számmező UI modernizálás + kilógó szövegek javítása)

### Mit csináltunk [x]
- [x] A number input mezők natív böngésző spinner (régi fel/le nyilak) megjelenítése kikapcsolva (`.va-input[type="number"]`)
- [x] Input padding finomhangolás: text/number mezők kisebb jobb oldali paddinget kaptak, a select maradt nyíl-kompatibilis
- [x] Kereső filter rács mezőminimum szélessége növelve (`minmax(220px, 1fr)`), hogy ne lógjanak bele a feliratok
- [x] Hosszú placeholder szövegek rövidítve (km és hengerűrtartalom mezők), hogy ne vágódjanak le
- [x] Plugin mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A keresőben és a hirdetésfeladóban a számmezők modernebbek (spinner nélkül), és a mezőszövegek jobban kiférnek, nem lógnak bele.

---
## 2026. 05. 02. – Session #146 (Keresőben minden szöveg fehér)

### Mit csináltunk [x]
- [x] A kereső szűrő blokkban egységesen fehérre állítva a szövegek
- [x] Érintett elemek: szekciócím, slider feliratok/érték, checkbox/radio címkék, segédszövegek, találatszám
- [x] Input placeholder szöveg is világosítva (`rgba(255,255,255,.9)`)
- [x] Plugin mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A kereső felületen a szövegmegjelenítés most egységesen fehér.

---
## 2026. 05. 02. – Session #145 (Használtautó jellegű részletes autós szűrők)

### Mit csináltunk [x]
- [x] A kereső űrlap kibővítve részletes autós mezőkkel:
  - Márka, Modell (függő), Kivitel, Üzemanyag
  - Évjárat tól/ig, Km óra tól/ig, Hengerűrtartalom tól/ig
  - Állapot, Ajtók száma, Ülések száma
  - Checkbox alapú extra szűrők (automata, tempomat, összkerék, alufelni, elektromos ablak, vonóhorog, ISOFIX, ESP, szervizkönyv)
  - Találat/oldal választó (25 / 50 / 100)
- [x] Frontend JS bővítve:
  - Márka -> modell dinamikus opciófrissítés (`vehicle_brand_models` alapján)
  - Új mezők beküldése AJAX payloadban
  - Új mezőkre automatikus újraszűrés (change/input)
  - Reset után modell lista + range vizuális állapot frissítés
- [x] Backend (`va_filter_listings`) bővítve:
  - Új paraméterek fogadása és cache kulcsba emelése
  - SQL `postmeta` joinok + WHERE szűrések a fenti autós mezőkre
  - Extra checkbox szűrés `va_extras` JSON-ben `LIKE` kereséssel
  - `per_page` kezelés: 25/50/100 whitelist
- [x] Plugin mirror + deploy lefuttatva LocalWP-re

### Hol tartunk
- A kereső oldalon már a kért Használtautó-szerű autós feltételek szerint lehet szűrni, backendben tényleges SQL szűréssel.

---
## 2026. 05. 02. – Session #144 (Keresőben hiányzó hirdetés default árszűrő miatt)

### Mit csináltunk [x]
- [x] Beazonosítva: a default `min_price=1` feltétel SQL oldalon kizárhatott olyan rekordokat, ahol a `wp_va_listing_meta` ár mező még nem volt szinkronban
- [x] Frontend javítás: `frontend.js` csak akkor küld `min_price` szűrést, ha a felhasználó 1 Ft fölé állítja a minimumot
- [x] Backend védelem: `class-ajax.php` csak `min_price > 1` esetén tesz `lm.price >= ...` WHERE feltételt
- [x] Plugin mirror + deploy lefuttatva LocalWP-re

### Hol tartunk
- Alap állásban a kereső már nem dob el hirdetést a kötelező `min_price=1` miatt, így a kezdőlapon látható hirdetésnek meg kell jelennie keresőben is.

---
## 2026. 05. 02. – Session #143 (Kereső ár rendezés javítás)

### Mit csináltunk [x]
- [x] Beazonosítva: `Ár szerint` rendezésnél a `featured/boost` előresorolás felülírta a tiszta ár szerinti sorrendet
- [x] Javítva a `va_filter_listings` rendezés: `price_asc` / `price_desc` esetén nincs `boost` prefix és nincs `featured` előresorolás
- [x] Így ár rendezésnél ténylegesen az `lm.price` mező alapján történik a sorrend
- [x] Plugin mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A keresőben az `Ár: csökkenő` most már valódi legdrágább->olcsóbb sorrendet ad.

---
## 2026. 05. 02. – Session #142 (Kereső ár tartomány: 1 Ft – 100M Ft)

### Mit csináltunk [x]
- [x] A hirdetés kereső ár csúszkája 0 helyett 1 Ft minimumról indul
- [x] A kereső ár maximum alapértéke legalább 100 000 000 Ft-ra emelve
- [x] Frissítve a template (`search.php`) és a frontend JS (`frontend.js`) fallback/logika, hogy konzisztensen 1–100M tartományt kezeljen
- [x] Plugin mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A keresőben most 1 Ft-tól 100 millió Ft-ig tudsz szűrni a csúszkával.

---
## 2026. 05. 01. – Session #141 (Feladó további hirdetései: 3 látható + scroll)

### Mit csináltunk [x]
- [x] A termékoldali `Feladó további hirdetései` blokk belső scroll konténert kapott (`.sl__more-list`)
- [x] A lista max magassága úgy lett beállítva, hogy egyszerre kb. 3 elem látszódjon
- [x] A többi elem görgetéssel elérhető ugyanabban a kártyában
- [x] A lekérdezés `posts_per_page` értéke 3-ról 100-ra emelve (a projekt limit szabályon belül)
- [x] Theme mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A feladó többi hirdetése most nem nyújtja meg a kártyát: 3 elem látszik, a többi belső scrollal nézhető.

---
## 2026. 05. 01. – Session #140 (Kényelmi felszereltség cím narancs)

### Mit csináltunk [x]
- [x] A termékoldali Részletek blokk extras csoportcímében csoport-specifikus CSS osztály bevezetve
- [x] A `Kényelmi felszereltség` (`kenyemi`) cím narancs színt kapott: `#ff8800`
- [x] Theme mirror frissítve és deploy lefuttatva LocalWP-re

### Hol tartunk
- A `Kényelmi felszereltség` cím vizuálisan kiemelve jelenik meg a kért narancs színnel.

---
## 2026. 05. 01. – Session #139 (Termékoldal Részletek blokk belső scroll)

### Mit csináltunk [x]
- [x] A termékoldali `Részletek` kártyában a hosszú tartalom (spec + kiegészítők) belső görgethető konténerbe került
- [x] Új wrapper: `sl__params-scroll` a `single-va_listing.php` sablonban
- [x] Desktopon max magasság + `overflow:auto`, így nem nyúlik végtelenre az oldal a kiegészítők miatt
- [x] Mobilon (<= 900px) a belső scroll kikapcsolva, normál tartalomfolyással
- [x] Theme mirror frissítve és deploy lefuttatva LocalWP-re (`deploy-theme.ps1`)

### Hol tartunk
- A hosszú kiegészítőlista már a Részletek kártyán belül görgethető, ezért termékoldalon jelentősen kevesebbet kell teljes oldalt görgetni.

---
## 2026. 05. 01. – Session #138 (Használtautó teljes márka-modell-kivitel bekötés)

### Mit csináltunk [x]
- [x] A Használtautó alapú autós dataset kiegészítve teljes márka -> modell fával (`vehicle-brand-models.json` a root + plugin copy alatt)
- [x] Új helper betöltés: `VA_Vehicle_Catalog::get_brand_models()` JSON-ból olvas, cache-el és normalizálja a külső forrásból érkező hibás karaktereket
- [x] A hirdetésfeladó űrlapon a jármű mód már függő legördülőket használ: `Márka` -> `Modell`, plusz külön `Kivitel`
- [x] A `Modell` mező szerkesztés módban is megtartja a korábban mentett értéket, akkor is ha az nincs az aktuális listában
- [x] A jármű űrlap páros elrendezése frissítve: `Kivitel` + `Évjárat`
- [x] LocalWP deploy lefuttatva (`Deploy All`)
- [x] Nyilvános ellenőrzés: az autós kategóriák már élnek a local oldalon

### Hol tartunk
- A kategóriák, márkák, modellek és kivitelek kódszinten teljesen be vannak kötve.
- A feladási űrlap interaktív ellenőrzéséhez bejelentkezett állapot kell; publikus nézetből ez most nem volt végignyomható.

---
## 2026. 05. 01. – Session #137 (Termékoldali email gomb: mailto -> Kapcsolat oldal)

### Mit csináltunk [x]
- [x] A termékoldali email CTA (`single-va_listing.php`) már nem `mailto:` link
- [x] Új közös URL-feloldó függvény: `va_get_contact_page_url()` (root + theme mirror `functions.php`)
- [x] A függvény feloldási sorrendje: opcionális page ID -> `page-kapcsolat.php` template -> `kapcsolat` slug -> `/kapcsolat/`
- [x] Az email gomb linkje erre a függvényre kötve (root + theme mirror)
- [x] Theme deploy lefuttatva LocalWP-re

### Hol tartunk
- A termékoldali email gomb most webes Kapcsolat oldalra visz, ezért nem jelenik meg többé a Windows `mailto` alkalmazásválasztó.

---
## 2026. 05. 01. – Session #136 (Kapcsolat redirect URL-változásbiztos)

### Mit csináltunk [x]
- [x] A kapcsolat űrlap redirect logika tovább erősítve: már nem slug-függő
- [x] Elsődleges feloldás: `page-kapcsolat.php` template alapján megtalálja a Kapcsolat oldalt
- [x] Fallback-ek: opcionális page ID opció, majd `kapcsolat` slug, végül `/kapcsolat/`
- [x] Módosítva root + theme mirror `functions.php` fájlban
- [x] Theme deploy lefuttatva
- [x] Ellenőrzés: `admin-post.php?action=va_contact_form` -> `302 Location: /kapcsolat/`

### Hol tartunk
- Email küldés után a rendszer a Kapcsolat oldal permalinkjére irányít, akkor is ha a slug később megváltozik.

---

## 2026. 05. 01. – Session #135 (Kapcsolat űrlap redirect fix)

### Mit csináltunk [x]
- [x] A kapcsolat űrlap küldése utáni redirect fixen a `/kapcsolat/` oldalra állítva
- [x] A korábbi referer-alapú visszairányítás eltávolítva
- [x] Módosítva root és theme mirror `functions.php` fájlban
- [x] Theme deploy lefuttatva LocalWP-re
- [x] Ellenőrzés: `admin-post.php?action=va_contact_form` -> `302 Location: /kapcsolat/`

### Hol tartunk
- Email küldésnél a visszairányítás most mindig a kapcsolat oldalra történik.

---

## 2026. 05. 01. – Session #134 (Telefon ikon eltávolítás)

### Mit csináltunk [x]
- [x] A `Telefonszám megjelenítése` gombból eltávolítva a telefon ikon
- [x] A sticky mobil telefon gombból is eltávolítva az ikon
- [x] Módosítva mind root, mind theme mirror sablonban
- [x] Theme deploy lefuttatva LocalWP-re

### Hol tartunk
- A telefon gomb csak szöveget jelenít meg, ikon nélkül.

---

## 2026. 05. 01. – Session #133 (Főoldali „ÚJ” + „Összes →” szerkeszthető)

### Mit csináltunk [x]
- [x] A főoldali `Legújabb hirdetések` blokk `ÚJ` címkéje és `Összes →` linkje adminból szerkeszthető lett
- [x] Új opciók hozzáadva:
  - `va_home_latest_label_text` (alap: `ÚJ`)
  - `va_home_all_link_text` (alap: `Összes →`)
  - `va_home_section_accent_color` (alap: `#e27019`)
- [x] Az accent szín rákötve a `ÚJ` badge háttérre és az összes `Összes →` link színére a főoldalon
- [x] Plugin + Theme deploy lefuttatva
- [x] Élő ellenőrzés: a főoldalon mindkét elem színe `rgb(226, 112, 25)` (`#e27019`)

### Hol tartunk
- A kért feliratok és a nyilas `Összes` linkek most narancsra vannak állítva, és adminból szerkeszthetők.

---

## 2026. 05. 01. – Session #132 (Kártyaszerkesztő színek nem érvényesültek)

### Mit csináltunk [x]
- [x] Beazonosítva: a Keresési oldal (va_lp_*) CSS blokk globálisan felülírta a kártya stílusokat `!important` szabályokkal
- [x] Emiatt a Kártyaszerkesztőben állított ár/meta/kedvenc (és egyéb kártya) színek nem tudtak érvényesülni
- [x] A konfliktusos kártya szabályok kivezetve a theme `functions.php` va_lp CSS blokkjából (root + theme mirror)
- [x] Theme deploy lefuttatva LocalWP-re
- [x] Ellenőrzés: a kereső oldal HTML-ben már nincs `!important` a `.va-card__price`, `.va-card__meta`, `.va-card__watchlist` szabályokon

### Root cause
A `va_lp_*` (Keresési oldal) dizájn szabályok között globális `.va-card*` felülírások voltak `!important`-tel. Ezek felülírták a Kártyaszerkesztő (`va_card_styles`) frontend kimenetét.

### Hol tartunk
- A Kártyaszerkesztő színei újra érvényesülnek a kártyákon.
- A keresőben az ár/meta/kedvenc színek már nem vannak erőből felülírva.

---

## 2026. 05. 01. – Session #131 (Kereső AJAX fix – BOM eltávolítás)

### Mit csináltunk [x]
- [x] Beazonosítva, hogy a `va_filter_listings` AJAX válasz elején UTF-8 BOM karakter jelent meg, ezért a frontend nem tudta JSON-ként feldolgozni a választ
- [x] Szűkítve a hibaforrás a `admin/class-settings-page.php` fájlra
- [x] A settings page fájl BOM-ja eltávolítva a root és plugin másolatból
- [x] A LocalWP-ben deployolt plugin fájl is BOM nélkülire lett normalizálva
- [x] Élő oldalon ellenőrizve: a keresőoldal újra betölti a kártyákat, eltűnik a loader, frissül a találatszám

### Root cause
A `class-settings-page.php` UTF-8 BOM-mal volt mentve. Ez a plugin betöltésekor kimenetet generált az AJAX kérés előtt, ezért a `admin-ajax.php` válasz nem tiszta JSON-nal indult, hanem BOM + JSON formában. A böngészőben emiatt a kereső callback nem tudta feldolgozni a választ.

### Hol tartunk
- A `/va-hirdetes-kereses` oldal keresője újra működik.
- A keresési eredménykártyák megjelennek, a loader normálisan lefut.

---

## 2026. 05. 01. – Session #130 (Kereső dropdown piros -> #ff8800)

### Mit csináltunk [x]
- [x] A kereső dropdown piros elemei (#ff0000 / #ff4040) átállítva #ff8800 árnyalatra
- [x] Érintett elemek: hirdetés badge szöveg/háttér, "Összes találat" szín + hover háttér
- [x] Migráció hozzáadva: ha régi piros érték van mentve opcióban, automatikusan #ff8800-ra vált
- [x] Plugin deploy kész LocalWP-re

### Hol tartunk
- A kereső dropdown piros elemei most narancs (#ff8800) tónusúak.

---

## 2026. 05. 01. – Session #129 (Új Kereső menüpont + header dropdown teljes dizájn vezérlés)

### Mit csináltunk [x]
- [x] Új admin menüpont létrehozva: **Kereső** (`vadaszapro-search`) a sidebarban, submenu-ben és admin bar menüben
- [x] Új beállítási oldal: `render_search_designer()`
  - panel háttér/keret/radius/árnyék
  - találati sor border + hover háttér + padding
  - bélyegkép/no-image megjelenés
  - cím/ár/összes találat színek és betűméretek
  - pill badge (hirdetés/aukció/kategória/felhasználó) háttér + szövegszín + méret + radius
- [x] Új opciókulcsok regisztrálva `va_search_settings` csoportban (`va_search_dd_*`)
- [x] Frontenden dinamikus CSS bekötve (`functions.php` root + theme mirror), hogy a dropdown elemei élőben kövessék az admin értékeket
- [x] Plugin + Theme deploy lefuttatva LocalWP-re

### Hol tartunk
- A header keresőből nyíló dropdown panel minden fontos vizuális eleme paraméterezhető adminból külön **Kereső** oldalon.

---

## 2026. 05. 01. – Session #128 (Kapcsolat oldal dupla render fix)

### Mit csináltunk [x]
- [x] Beazonosítva: a kapcsolat oldal sablonja kétszer szerepelt a fájlban
- [x] A sablon első `get_footer();` hívása után bent maradt egy teljes duplikált `section + script + get_footer` blokk
- [x] A duplikált második blokk törölve mindkét másolatból (root + theme mirror)
- [x] Theme deploy lefuttatva LocalWP-re

### Root cause
- A `page-kapcsolat.php` fájl véletlenül duplikálódott, ezért a lábléc után újra megjelent ugyanaz az oldalrész.

### Hol tartunk
- A kapcsolat oldal most egyszer renderelődik, nem tölt be újra a lábléc alatt.

---

## 2026. 05. 01. – Session #127 (Aloldalak tartalma hiányzott – shortcode fix)

### Mit csináltunk [x]
- [x] **KRITIKUS HIBA JAVÍTVA**: 6 WP oldal üres tartalommal rendelkezett (nem volt shortcode-juk)
- [x] MySQL-en keresztül beírtuk a hiányzó shortcode-okat:
  - `va-fiok` → `[va_user_dashboard]`
  - `va-hirdetes-feladas` → `[va_submit_listing]`
  - `va-hirdetes-kereses` → `[va_listing_search]`
  - `va-bejelentkezes` → `[va_login_form]`
  - `va-regisztracio` → `[va_register_form]`
  - `va-aukciok` → `[va_auction_list]`
- [x] WP transient cache törölve
- [x] WP_DEBUG + WP_DEBUG_LOG engedélyezve a LocalWP wp-config.php-ban
- [x] `va-setup-pages.php` slug-ok javítva (DB-ben lévő valós slug-okra: `va-fiok`, `va-hirdetes-kereses`, `va-aukciok`)

### Root cause
A `va-setup-pages.php` beállító script hibás slug-okkal lett írva:
- `va-fiokom` helyett a WP-ban `va-fiok` volt a slug
- `va-hirdetesek` helyett `va-hirdetes-kereses`
- `va-aukcio` helyett `va-aukciok`
A script sosem frissítette ezeket az oldalakat, ezért maradtak üresek.

### Hol tartunk
- Összes shortcode-os aloldal tartalmát beállítottuk.
- A single listing oldal (`hirdetes/slug/`) az `single-va_listing.php` template-et használja és a listing adatai megvannak a DB-ben.

---

## 2026. 05. 01. – Session #126 (Gombok nem működnek fix)

### Mit csináltunk [x]
- [x] Beazonosítva: a jobb alsó `#va-scroll-ring` rejtett állapotban is kattintást fogott (`opacity:0`, de `pointer-events` nem volt tiltva)
- [x] Javítás `footer.php` (root + theme mirror):
  - `#va-scroll-ring` alapállapot: `pointer-events: none;`
  - `#va-scroll-ring.va-ring--visible`: `pointer-events: auto;`
- [x] Beazonosítva egy további hibaforrás: admin JS fájlok UTF-16 LE kódolásban voltak
- [x] `admin/admin.js` + `wp-plugin/vadaszapro-core/admin/admin.js` UTF-8-ra konvertálva
- [x] LocalWP deploy kész (`Deploy All`)

### Hol tartunk
- A gombok kattinthatóságát blokkoló overlay hiba javítva.
- Admin JS kompatibilitási hiba (UTF-16) megszüntetve.

### Utókövetés [x]
- [x] További kattinthatósági hotfix a fejléc interaktív elemekre (`style.css` root + theme mirror):
  - `isolation: isolate` a `.va-header`-en
  - explicit `z-index` réteg a `.va-header__inner`, `.va-header__right`, `.va-nav`, `.va-header__search` blokkokra
  - explicit `z-index` + `pointer-events:auto` a fő kattintható elemekre (`.va-header__submit-btn`, `.va-header__user`, `.va-header__user-login`, `.va-header__search-btn`, `.va-nav__item`, nyelvváltó gombok)

---

## 2026. 05. 01. – Session #125 (Scroll videós pill gomb: videó + border állítható)

### Mit csináltunk [x]
- [x] Beazonosítva a komponens: `footer.php` `#va-scroll-ring` (jobb alsó videós scroll-progress gomb)
- [x] Új admin opciók a Hero beállításokhoz:
  - `va_scroll_ring_video_url`
  - `va_scroll_ring_border_color`
- [x] Új mezők a Hero admin oldalon:
  - Háttér videó URL
  - Border szín
- [x] `footer.php` (root + theme mirror):
  - videó source most opcióból jön
  - progress border stroke szín most opcióból jön (`--va-scroll-ring-color` CSS változó)
- [x] LocalWP deploy kész

### Hol tartunk
- A képen látható gomb háttérvideója és keretszíne most adminból szabadon állítható.

---

## 2026. 05. 01. – Session #123 (Fejléc/Lábléc szín vezérlés + Vásárlás gomb toggle)

### Mit csináltunk [x]
- [x] `class-settings-page.php` (root + plugin mirror): új opció hozzáadva `va_hf_header_show_buy_button` (alapértelmezett: `1`), regisztrálva a `va_header_footer_settings` csoportban
- [x] Header/Footer admin oldalon új kapcsoló: **"Vásárlás gomb megjelenítése"**
- [x] `header.php` (root + theme mirror): a "Vásárlás" gomb megjelenése most opcióhoz kötött (bejelentkezett és vendég állapotban is)
- [x] `functions.php` (root + theme mirror): fejléc/lábléc háttér CSS javítva úgy, hogy a **Fejléc háttér** (`va_color_header_bg`) és **Lábléc háttér** (`va_color_footer_bg`) ténylegesen látszódjon (gradient első stopjába bekötve)
- [x] Deploy LocalWP-re kész (`weingartner-aut.local`)

### Mi volt a hiba oka
- A háttérszínek beállíthatók voltak adminban, de a renderelt gradient teljesen elfedte őket, ezért úgy tűnt, mintha a háttér színmezők nem működnének.

### Hol tartunk
- A fejléc és lábléc háttérszínei most láthatóan reagálnak az admin beállításokra.
- A fejléc "Vásárlás" gomb külön ki/be kapcsolható.

---

## 2026. 05. 01. – Session #124 (Nav hover/underline + kereső színek bővítése)

### Mit csináltunk [x]
- [x] Header/Footer admin beállítások bővítése új színkulcsokkal:
  - `va_color_header_nav_underline`
  - `va_color_header_search_border`
  - `va_color_header_search_hover_border`
  - `va_color_header_search_focus_border`
  - `va_color_header_search_text`
  - `va_color_header_search_placeholder`
  - `va_color_header_search_btn_bg`
  - `va_color_header_search_btn_hover_bg`
- [x] Új mezők megjelenítése a Fejléc + Lábléc admin oldalon (nav + kereső blokk)
- [x] Dinamikus CSS bekötés (`functions.php` root + theme mirror):
  - nav hover szín működése javítva helyes szelektorral
  - nav alsó vonal (`::after`) színe külön állítható
  - kereső keret (alap/hover/fókusz), input betűszín, placeholder, gomb és gomb-hover szín külön állítható
- [x] LocalWP deploy kész

### Hol tartunk
- A kért header nav és kereső színek most adminból szabadon állíthatók.

---

## 2026. 04. 30. – Session #122 (Hero szín fix, deploy auto-detect, csomag limit fix)

### Mit csináltunk [x]
- [x] **Deploy auto-detect**: `deploy.ps1`, `deploy-plugin.ps1`, `deploy-theme.ps1` átírva — ha nincs `local-config.ps1` vagy rossz útvonalat mutat, automatikusan megkeresi a `vadaszapro-core`/`vadaszapro-theme` mappát a `D:\LocalWP\` alatt. Új gépen/szerveren 0 konfig kell.
- [x] **local-config.ps1 javítva**: `apro/listbomb` → `listbomb/vadaszapro-core` + `listbomb/vadaszapro-theme`
- [x] **Hero szín fix** (`functions.php`): a csík/badge-dot/scroll elemek hardkódolt `$global_accent`-et használtak → most saját opciókból (`va_hero_stripe_color`, `va_hero_badge_dot_color`, `va_hero_scroll_*`) olvassa. Overlay gradient opacitások, span szín, hover text mind saját admin opcióból jön.
- [x] **Csomag limit üzenet fix** (`submit-form.php`): Basic csomag 1/1 hirdetésnél "Még 4 db ingyenes" felirat jelent meg — a `$remaining_free` nem vette figyelembe a csomag limitet. Fix: ha `$plan_remaining !== null && !$plan_has_allowance` → `$remaining_free = 0`
- [x] **LBreakpoint + VA Admin menü** deployolva listbomb-ra (korábban nem jutott el)
- [x] Minden fent van giten

### Tanulságok
- Deploy script csak 1 site esetén auto-detect, több site esetén `local-config.ps1` kell (ez gépenkénti, gitignorált)
- A `va_free_listings_limit` globális opció és a csomag `monthly_limit` két különböző rendszer — össze kell kötni ha csomag van

---

## 2026. 04. 29. – Session #121 (Encoding katasztrófa + visszaállítás)

### Mi történt
- **KRITIKUS HIBA**: A tegnapi átnevezés script (`rename-to-listbomb.ps1`) PowerShell-lel tömegesen cserélte a szövegeket az összes PHP fájlban. A PowerShell alapból CP1252 encodingban olvassa a fájlokat UTF-8 helyett → az összes ékezetes karakter (`é`, `á`, `ő`, stb.) kettősen kódolódott be (`é` → `Ă©`, `ő` → `Ĺ'` stb.) az összes fájlban
- **Tünet**: Admin színpaletta eltűnt, sok elem hiányzott, frontend teljesen tönkrement
- **Megoldás**: `git reset --hard dfb9ae7` + force push → visszaállás a 2026.04.28 10:31-es állapotra (átnevezés ELŐTTI utolsó jó commit)

### Mit csináltunk [x]
- [x] Git history átvizsgálva, az átnevezés commit azonosítva: `3730412 Auto_2026.04.28_16.00` (itt jött létre a `rename-to-listbomb.ps1`)
- [x] Visszaállítás: `git reset --hard dfb9ae7` (2026.04.28 10:31) + `git push -f`
- [x] Encoding ellenőrzés: `check-encoding.py` megírva → 2 garbled fájl találva: `single-va_listing.php` (root + wp-theme)
- [x] `fix-two-files.py` megírva – egymást követő non-ASCII karaktereket EGYÜTT kezel (cp1250 undouble chunk-onként, nem karakterenként)
- [x] Mindkét `single-va_listing.php` javítva, újra ellenőrizve: **OK – nincs karakterhiba**
- [x] Plugin ZIP: `vadaszapro-plugin.zip` (267 429 byte) és theme ZIP: `vadaszapro-theme.zip` (99 853 byte) elkészítve
- [x] Push: a Watcher automatikusan pusholt, repó tiszta

### Tanulságok – SOHA TÖBBÉ
- **PowerShell-lel TILOS PHP fájlt szerkeszteni** `encoding` paraméter (`-Encoding UTF8`) nélkül
- **Tömeges szövegcsere csak Python scripttel** (UTF-8 alapból)
- Ha valaha ismét átnevezés kell: Python script, `open(f, encoding='utf-8')`
- A `rename-to-listbomb.ps1` fájl törölve (revert eltüntette)

### Encoding hiba diagnosztika (jövőre)
- Tünet: `Ă©`, `Ĺ'`, `Ăş`, `Ăł`, `ĂˇZ` megjelenik PHP forrásban
- Ellenőrzés: `python .vscode/check-encoding.py`
- Javítás: `python .vscode/fix-two-files.py` (vagy új fájlt felsorolva benne)
- Mechanizmus: `chunk.encode('cp1250').decode('utf-8')` – egymást követő non-ASCII karakterek együtt

### Hol tartunk
A projekt visszaállt a 2026.04.28 10:31-es tökéletes állapotba. Plugin neve: `vadaszapro-core`, CPT prefix: `va_`, téma: `vadaszapro-theme`. Karakterhiba mentes, ZIP-ek elkészítve, repó naprakész.

### Következő session teendők
- Folytatni amit 04.28 reggel csináltunk (functions.php fejlesztés volt az utolsó commit)
- Frontend oldalak (login/register/submit/fiók) működése ellenőrzése az új szerveren

---

## 2026. 04. 28. – Session #120 (Átnevezési kísérlet – VISSZAVONVA)

### Mi történt
- Rename script futtatva `vadaszapro` → `listbomb` átnevezéshez → karakterhiba az összes PHP fájlban
- Session végén visszaállítva (lásd 04.29 Session #121)

---

## 2026. 04. 24. – Session #119 (Kártyaszerkesztő – live preview + frontend CSS debug)

### Mit csináltunk [x]
- [x] `class-settings-page.php` – `require_once` áthelyezve `is_admin()` blokkon kívülre → `VA_Settings_Page` osztály frontend-en is elérhető (fatal error megszüntetve)
- [x] `vadaszapro-core.php` – `VA_Settings_Page::init()` kiemelve `is_admin()` blokkon kívülre → `wp_head` CSS hookak (pill + kártya) frontend-en is regisztrálódnak
- [x] Szín inputok: `va-color-input` picker visszaállítva (native color combó visszavonva)
- [x] `vacdInitPickers()` – az egész `#vacd-editor` inicializálódik betöltéskor (nem csak nyitott szekciók), retry 100ms-enként amíg `vaInitColorPickers` elérhető
- [x] jQuery event delegation: `#vacd-editor` `.on('change.vacd', '.va-color-input')` → `updatePreview()` + `saveJson()`
- [x] `vacdToggle()` egyszerűsítve – nincs per-szekció init
- [x] Form submit handler hozzáadva az IIFE-ben → `saveJson()` mindig lefut submit előtt
- [x] `handle_save_card_styles()` – debug `error_log` sorok hozzáadva
- [x] Root fájlok szinkronizálva, deploy megtörtént minden módosítás után

### Ismert nyitott problémák
- [ ] **Live preview nem frissül** – a `va-color-input` picker `change` eventje nem érkezik meg a `vacdInitPickers` jQuery handleréhez (valószínűleg timing: az init előbb fut mint admin.js betölt)
- [ ] **Frontend CSS nem változik** – mentés után a `va_card_styles` opció valószínűleg nem íródik (`saveJson()` / form submit sorrend, debug logolás most kerül be)
- [ ] Debug log elérési útja nem volt ismert, WP_DEBUG státusz ismeretlen

### Hol tartunk
A kártyaszerkesztő admin oldal betölt, a form struktúra helyes. A két fő probléma (live preview + frontend CSS) debuggolás alatt – a debug `error_log` sorok most kerültek be, következő lépés az eredmény ellenőrzése.

### Következő session teendők
1. Mentés után ellenőrizni: `wp_options` táblában megjelenik-e `va_card_styles`
2. Ha igen → frontend CSS specificity probléma (frontend.css felülírja)
3. Ha nem → `handle_save_card_styles` debug log eredménye alapján JSON/nonce probléma
4. Live preview: ha `vacdInitPickers` nem kapja el a `change` eventet → direkt `oninput` handler szükséges

---

## 2026. 04. 21. – Session #115 (Color picker visszaállítás)

### Mit csináltunk [x]
- [x] Eltávolítva a színválasztó gomb fekete kényszerítése (JS + inline style)
- [x] Visszaállítva a WordPress natív aktuális-szín megjelenítés
- [x] „Alapértelmezett” visszaállítás gomb működése megtartva
- [x] Color result szöveg-blokk fehér hátterének tiltása (`transparent`), border törlés
- [x] Asset cache bust: `VA_VERSION` emelve `1.0.5`-re
- [x] Plugin deploy LocalWP-be megtörtént

### Hol tartunk
A color picker ismét a kiválasztott aktuális színt mutatja, az alapértelmezett visszaállítás használható.

---

## 2026. 04. 21. – Session #116 (Color picker aktuális szín szinkron)

### Mit csináltunk [x]
- [x] `admin.js`: a `wpColorPicker` change/clear eseményre az aktuális input szín közvetlenül rákerül a `.wp-color-result` gombra
- [x] Dinamikus kontraszt szövegszín (világos háttérnél sötét, sötét háttérnél fehér)
- [x] `admin.css`: eltávolítva a `.wp-color-result` pseudo-elemek tiltása
- [x] Alapértelmezett gomb színjelzése (`--va-sw-def`) megtartva
- [x] Cache bust: `VA_VERSION` `1.0.6`
- [x] Deploy LocalWP-be kész

### Hol tartunk
A color picker gomb most az aktuálisan beállított színt mutatja, és az alapértelmezett szín jelzése is megmarad.

---

## 2026. 04. 21. – Session #117 (Fehér felirat + kör eltávolítás)

### Mit csináltunk [x]
- [x] `admin.css`: a color picker gomb felirata minden állapotban fehér (`#fff`)
- [x] `admin.css`: `::before`, `::after` és `.color-alpha` elemek letiltva a gombon
- [x] `admin.js`: az aktuális szín szinkron maradt, de feliratszín fixen fehér
- [x] Cache bust: `VA_VERSION` `1.0.7`
- [x] Deploy LocalWP-be kész

### Hol tartunk
A gomb az aktuális színt mutatja, a felirat fehér, a kamu kör elemek eltávolítva.

---

## 2026. 04. 21. – Session #118 (Külső aktuális színkör szinkron)

### Mit csináltunk [x]
- [x] `admin.js`: color picker elemek lekérése javítva init UTÁN (valós `.wp-picker-container`)
- [x] `admin.js`: új `.va-current-color-dot` kör hozzáadása a gomb mellé minden `va-color-input` mezőhöz
- [x] `admin.js`: kör és gomb háttér mindig az aktuális kiválasztott színre frissül (`change`/`clear` + init)
- [x] `admin.css`: `.va-current-color-dot` stílus (kerek, kerettel, látható)
- [x] Cache bust: `VA_VERSION` `1.0.8`
- [x] Deploy LocalWP-be kész

### Hol tartunk
A gomb mellett lévő kis kör mostantól mindig az aktuálisan beállított színt mutatja.

---

## 2026. 04. 20. – Session #5 (délután, irodai gép) — BEFEJEZETLEN, OTTHON FOLYTATNI

### Mit csináltunk [x]
- [x] Hero szekció teljes admin oldal létrehozva (`vadaszapro-hero` slug)
- [x] `class-admin.php` — Hero szekció menüpont hozzáadva
- [x] `class-settings-page.php` — `va_hero_settings` group, 50+ beállítás regisztrálva + `render_hero()` metódus
- [x] `functions.php` — IIFE blokk: overlay, stripe, badge dot, badge, title, sub, primary btn, ghost btn, scroll jelző CSS változók
- [x] Header submit gomb hover szín beállítás hozzáadva
- [x] `php.ini.hbs` — `opcache.validate_timestamps=1` + `opcache.revalidate_freq=0` hozzáadva
- [x] Minden fájl deployolva LocalWP-be, push megtörtént
- [ ] **OPcache probléma: a LocalWP PHP-FPM nem vette fel az új php.ini.hbs-t!**

### Probléma (OPcache)
Az irodai gépen a LocalWP PHP-FPM cachelve tartja a régi PHP bytecode-ot.  
`php.ini.hbs`-be írt `opcache.validate_timestamps=1` még nem lépett életbe mert a PHP-FPM nem restarolt.  
A Hero szekció admin menüpont és az összes kód **ott van a fájlokban**, csak az OPcache blokkolja.

### ⚠️ OTTHONI TEENDŐK (OLVASD EL ELŐSZÖR!)

**1. lépés — git pull + deploy:**
```powershell
# VS Code-ban: Push task (vagy terminálban):
git pull
. ".vscode\local-config.ps1" ; Copy-Item "wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force ; Copy-Item "wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force
```

**2. lépés — php.ini.hbs fix az OTTHONI gépen is:**
```
D:\LocalWP\[site-mappa]\conf\php\php.ini.hbs
```
Keresd az `opcache.enable_cli=1` sort, utána add hozzá:
```
opcache.validate_timestamps=1
opcache.revalidate_freq=0
```

**3. lépés — LocalWP teljes újraindítás:**
- LocalWP alkalmazást **teljesen zárd be** (tálcán is jobb klikk → Quit/Kilépés)
- Nyisd meg újra
- Start Site

**4. lépés — Ellenőrzés:**
- `http://[site].local/wp-admin/admin.php?page=vadaszapro-hero` → meg kell jelennie a Hero szekció oldalnak

### Hol tartunk
Minden kód kész és pusholva. Az irodai gépen OPcache blokkol. Otthon a fenti 4 lépéssel rögtön menni fog.

### Nyitott TODO-k (folytatás otthon)
- [ ] Hero szekció beállítások tesztelése (működik-e minden szín/toggle)
- [ ] Főoldal hirdetés grid / category kártyák szekció a hero alá
- [ ] archive.php grid kártya dizájn egységesítés
- [ ] Hirdetés feladás form UX javítás
- [ ] va_hero_video_url beállítás WP adminba

---

## 2026. 04. 20. – Session #114 (Irodai gép szinkronizálás)

### Mit csináltunk [x]
- [x] Irodai gépen beállított LocalWP (apro.local, port 10017) szinkronizálva
- [x] Plugin + téma deployolva a repóból LocalWP-be
- [x] Teljes szinkronizálás: LocalWP zip → repo (minden fájl naprakész)
- [x] Push: minden fájl fent van GitHubon
- [x] WP oldalak shortcode-jai javítva MySQL-en keresztül
- [x] Megállapítva: va_create_default_pages() automatikusan létrehozza az oldalakat aktiváláskor

### Hol tartunk
Az irodai gépen minden működik (apro.local). A vásárlás gomb bejelentkezéshez köti a csomagvásárlást — ez szándékos.

---

## 2026. 04. 20. – Session #4 (délelőtt, irodai gép)

### Mit csináltunk [x]
- [x] Irodai gépen beállított LocalWP (apro.local, port 10017) szinkronizálva
- [x] Plugin + téma deployolva a repóból LocalWP-be
- [x] Felderítve hogy a LocalWP-n lévő zip-es verzió fejlettebb mint a repo (4 plusz includes fájl, admin fájlok, CSS/JS)
- [x] Teljes szinkronizálás: LocalWP → repo (plugin + téma minden fájl)
- [x] Push: minden fájl fent van GitHubon (commit: Mentes_2026.04.20_11.04)
- [x] WP oldalak shortcode-jai javítva MySQL-en keresztül (va-regisztracio, va-bejelentkezes, va-hirdetes-feladas, va-fiok, va-aukciok, va-hirdetes-kereses)
- [x] Megállapítva: va_create_default_pages() már benne van az aktiválási hook-ban → új telepítésnél automatikusan létrejönnek az oldalak

### Hol tartunk
Az irodai gépen minden működik (apro.local). A repo naprakész. A vásárlás gomb bejelentkezéshez köti a csomagvásárlást — ez szándékos viselkedés.

### Plugin extra fájlok (LocalWP-ből jöttek, most már repóban is):
- `includes/class-mailer.php` – email értesítések
- `includes/class-page-renderer.php` – oldal renderelés
- `includes/class-updater.php` – frissítési logika
- `includes/class-user-roles.php` – rang rendszer (Basic/Silver/Gold/Platinum)
- `admin/class-dashboard.php`, `class-form-builder.php`, `class-listing-edit.php`, `class-page-builder.php`
- `frontend/css/user.css`, `frontend/js/user.js`
- `theme/page-kapcsolat.php`, `page-kategoria.php`, `page-vadasz-naptar.php`

### Nyitott TODO-k
- [ ] Főoldal hirdetés grid / category kártyák szekció a hero alá
- [ ] archive.php grid kártya dizájn egységesítés
- [ ] Hirdetés feladás form UX javítás
- [ ] Mobilon hero videó tesztelés (poster kép iOS-re)
- [ ] va_hero_video_url beállítás WP adminba (Settings oldalra)

---

## 2026. 04. 19. – Session #113 (Árkártyák admin, Toggle fix, Oldalszerkesztő)

### Elvégzett feladatok

#### Árkártyák admin oldal (DB-vezérelt díjcsomagok)
- **`admin/class-settings-page.php`**: `render_price_cards()` metódus – prémium dark admin UI
  - Hero szekció: eyebrow/title/subtitle szerkeszthető
  - 4 kártya × 12 mező: név, ár, ár-egység, leírás, badge, label, gomb szöveg, kiemelt/ingyenes toggle, icon slug
  - Options group: `va_price_cards_settings`, prefix: `va_pc_*`
- **`admin/class-admin.php`**: `vadaszapro-arkartyak` sidebar + submenu hozzáadva
- **`includes/class-ajax.php`**: `get_credit_packages()` – hardkódolt fallback helyett DB-ből olvas (`va_pc_{n}_*`)
- **`includes/class-shortcodes.php`**: `render_buy_credits()` – hero + kártyák DB-ből töltve
  - `get_plan_icon()` helper: 4 SVG ikon (basic/silver/gold/platinum)

#### Toggle CSS bug fix
- Nested `<label>` (érvénytelen HTML) helyett: `<span class="pb-toggle-row">` + `.pb-toggle-text` span
- CSS osztályok: `.va-pk-toggle-row`, `.va-pk-toggle-text`

#### Oldalszerkesztő (drag-and-drop blokk alapú)
- **`includes/class-page-renderer.php`** – ÚJ FÁJL
  - Frontend blokk megjelenítő: JSON → HTML a WordPress oldalakhoz
  - Hook: `the_content` (priority 1) + `wp_head` CSS injektálás
  - 6 blokk típus render: hero, text, img_text, cta, cards, divider
  - Biztonság: `esc_html`, `esc_url`, `wp_kses_post`, szín regex validáció
- **`admin/class-page-builder.php`** – ÚJ FÁJL
  - 3 oszlopos admin szerkesztő: paletta | vászon | beállítások
  - SortableJS 1.15.2 (jsDelivr CDN) – drag-and-drop blokkok
  - 6 blokk típus: Hero, Szöveg, Kép+szöveg, CTA, Kártyák, Elválasztó
  - Live settings panel, repeater kártyákhoz, WP media picker
  - AJAX: `va_pb_save`, `va_pb_get`, `va_pb_new_page`, `va_pb_delete_page`
  - Tárolás: `va_page_blocks` post meta (JSON) a WP `page` post type-on
  - Ctrl+S mentés, toast értesítők
- **`vadaszapro-core.php`**: `require_once` + init bekötve (renderer + page builder)
- **`admin/class-admin.php`**: „📄 Oldalak" sidebar item + `vadaszapro-oldalak` submenu

### Admin útvonalak
- Árkártyák: `admin.php?page=vadaszapro-arkartyak`
- Oldalszerkesztő lista: `admin.php?page=vadaszapro-oldalak`
- Szerkesztő: `admin.php?page=vadaszapro-oldalak&va_action=edit&post_id=X`

### Állapot
- Árkártyák: ✅ kész + deployed
- Toggle fix: ✅ kész + deployed
- Oldalszerkesztő: ✅ kész + deployed
- Git push: ✅ kész

---

## 2026. 04. 19. – Session #112 (Auto-update, Child theme, Rate limiting, Email rendszer)

### Elvégzett feladatok

#### Auto-update rendszer (GitHub Releases alapján)
- **`includes/class-updater.php`** – új fájl, független, 3rd party library nélkül
  - GitHub Releases API polling (6 óránkénti transient cache)
  - WP natív `pre_set_site_transient_update_plugins` + `plugins_api` hook
  - Zip letöltés: Release asset `.zip` → fallback GitHub auto-zip
  - `fix_folder_name()`: GitHub archive mappa → `vadaszapro-core/` átnevezés
- **`vadaszapro-core.php`** bővítve:
  - `VA_GITHUB_REPO` és `VA_GITHUB_TOKEN` konstansok (wp-config.php-ban állítható)
  - `require_once class-updater.php` + `VA_Updater::init()` a boot blokkban

#### Child theme
- **`wp-theme/vadaszapro-child/style.css`** – `Template: vadaszapro-theme` fejléccel
- **`wp-theme/vadaszapro-child/functions.php`** – szülő CSS enqueue, placeholder hook-okhoz

#### Rate limiting (publikus AJAX)
- **`class-ajax.php`**: `is_rate_limited()` privát helper – IP-alapú, transient-tel
  - `live_search`: 60 kérés/perc/IP limit
  - `filter_listings`: 60 kérés/perc/IP limit
  - 429-es JSON hibaválasz túllépés esetén

#### Email rendszer (előző session, most lezárva)
- `VA_Mailer` HTML email osztály – branded template, inline CSS
- 4 rendszer email (regisztráció, listing published, listing deleted, account deleted)
- Admin-editable sablonok: `vadaszapro-emails` panel
- Aukció emailek szintén admin-szerkeszthetők
- Sidebar fix: Aukció beállítások + Email sablonok menüpontok látszanak

### Állapot
- Auto-update: ✅ kész + deployed
- Child theme: ✅ kész (telepítés: WP admin → Megjelenés → Témák → Aktiválás)
- Rate limiting: ✅ kész + deployed
- Email rendszer: ✅ kész + deployed

### Beállítandó (deploy után)
- `VA_GITHUB_REPO` konstans: `wp-config.php`-ba → `define('VA_GITHUB_REPO', 'felhasznalonev/vadaszapro-core');`
- Child theme aktiválása a WP adminban

### Következő session lehetséges feladatok
- [ ] Child theme LocalWP-be deploy + aktiválás
- [ ] GitHub repo létrehozása az auto-update teszteléséhez
- [ ] Rate limiting finomhangolás (objektum cache ha elérhető)

---

## 2026. 04. 19. – Session #111 (Quill editor bugfix + képtörlés cleanup)

### Elvégzett feladatok
- **Admin editor mentés/betöltés fix**: `dangerouslyPasteHTML` → `root.innerHTML` (képméret megmarad)
- **Admin "Mentés vázlatként" gomb fix**: `form.submit()` bypass → `vaAdminDoSubmit()` (Quill tartalom most már mentődik)
- **Admin kép upload fix**: DOM-módosítás helyett HTML string-csere (MutationObserver bug kikerülve)
- **Admin törlés fix**: `wp_trash_post` → `wp_delete_post(true)` (valódi törlés, képek is törlődnek)
- **`before_delete_post` hook bővítés**: editor képek + galéria képek + kiemelt kép törlése listing törlésekor
- **Frontend törlés fix**: `delete_listing_with_images` bővítve – `post_content` img URL-ek regex-szel kinyerve, `attachment_url_to_postid()` alapján törölve (post_parent=0 esetén is működik)

### Állapot
- Admin Quill editor: ✅ teljesen kész
- Frontend Quill editor: ✅ teljesen kész
- Képtörlés (listing törléskor): ✅ minden ág lefedve (editor, galéria, borítókép, post_parent=0)

### Következő session lehetséges feladatok
- [ ] Email HTML support (wp_mail HTML headers)
- [ ] Auto-update rendszer GitHub-ról (YahnisElsts/plugin-update-checker)
- [ ] Child theme



### Allapot
- **Git HEAD:** `Auto_2026.04.19_14.50` — ez az utolsó stabil állapot, TinyMCE még nincs benne
- **Következő feladat:** TinyMCE rich editor bevezetése (erről most nem lett kész)
- Előző session (109) bugok javítva: enforce_plan_limits, dashboard UI, auto-visszaállítás
- TinyMCE implementáció közben adatvesztés történt (leírás eltűnt, 5 hirdetés privát lett)
- `va_recover.php` script segítségével az adatok visszaállítva
- Git reset → `Auto_2026.04.19_14.50`-re visszaálltunk, deploy kész

### Következő session teendői
- [ ] TinyMCE editor implementáció ÓVATOSAN:
  - `submit-form.php`: textarea → `wp_editor()` sötét skinnel
  - `class-listing-edit.php`: textarea → `wp_editor()` admin stílusban
  - `class-ajax.php`: `va_submit_listing` handler: `sanitize_textarea_field` → `wp_kses_post`
  - `class-ajax.php`: `va_update_listing` handler: ellenőrzés/javítás ugyanígy
- [ ] Tesztelés: hirdetés feladás + szerkesztés + mentés után leírás megmarad-e

### Tanulság
- TinyMCE bevezetésekor a szerkesztő `post_content`-et írja (WP default mező)
- Az AJAX handler ha `sanitize_textarea_field`-et használ, a HTML formázást levágja → leírás "eltűnik"
- Mindig `wp_kses_post`-ot kell használni leíráshoz ha rich editor van

---

## 2026. 04. 19. – Session #107 (Rang-alapu csomagvasarlas UI + teljes vasarlasi flow)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/includes/class-shortcodes.php` – `render_buy_credits()` teljesen ujraepitve
  - rang-alapu (Basic / Silver / Gold / Platinum) kartyas csomagvalaszto
  - kedvezmeny toggle es egyedi mennyiseg blokk kiveve
  - `va_return` kezeles (`buy` / `submit`) es ennek tovabbitasa checkoutra
- [x] `wp-plugin/vadaszapro-core/frontend/css/frontend.css` – uj premium pricing design rendszer
  - dot-grid + glow hero, modern kartyak, rank temak, responsive racs
- [x] `wp-plugin/vadaszapro-core/includes/class-ajax.php`
  - `buy_credits()` bovites: `return_to` fogadas + tokenben tarolas
  - callback iranyitas: siker/cancel utan megfelelo oldalra vissza (`submit` vagy csomag oldal)
  - uj helper-ek: buy page URL + redirect
- [x] `wp-theme/vadaszapro-theme/header.php` – uj `Vásárlás` header gomb
  - bejelentkezett user: direkt csomagoldal
  - vendeg user: loginra iranyit, majd csomagoldal
- [x] `wp-plugin/vadaszapro-core/frontend/templates/listing/submit-form.php`
  - csomagvasarlas link dinamikussa teve
  - hirdetes-feladasi flow `va_return=submit` paramrel a csomagoldalra visz
- [x] Hibavizsgalat: erintett fajlok hibamentesek
- [x] Deploy All: kesz ✅

### Eredmeny
- Headerbol a `Vásárlás` gomb vegre tenylegesen a csomagvalaszto oldalra visz.
- A csomagoldalon rangok szerinti modern UI-bol indul a bankkartyas fizetes.
- Ha a hirdetes feladas kozben kell csomag, ugyaninnen valaszt/fizet a user, es fizetes utan visszakerul a feladashoz.

---

## 2026. 04. 19. – Session #106 (Csomag beallitasok admin UI teljes ujrarendezes)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/admin/class-settings-page.php` – a `render_plans()` teljesen ujraepitve
- [x] a bent ragadt, duplikalt regi csomagbeallito markup teljes torlese
- [x] uj admin informacios architektura: bal oldali csomag navigacio + jobb oldali reszletes szerkeszto panel
- [x] uj osszefoglalo blokkok: limit, basis, cooldown, badge szin gyors attekintessel
- [x] panelenkenti elo badge preview es azonnali sidebar/meta frissites input valtozasra
- [x] kulon globalis panel a boost badge rendszerkozpontu beallitasaihoz
- [x] hibavizsgalat: `class-settings-page.php` hibamentes
- [x] Deploy Plugin: kesz ✅

### Eredmeny
- A csomagkezelo oldal mar nem szeteso kartyahalom, hanem egy attekintheto, professzionalis admin szerkeszto felulet.
- Gyorsabb lett a csomagok kozotti valtas, jobban elkulonul a megjelenes, a limitlogika, a boost es a marketing adat.
- A korabbi duplikalt markup kikerult, igy a `render_plans()` szerkezete is tiszta maradt.

---

## 2026. 04. 20. – Session #105 (Felhasználói terv rendszer + Boost kiemelés)

### Mit csinaltunk [x]
- [x] `includes/class-user-roles.php` – ÚJ FÁJL: VA_User_Roles class létrehozva
  - 4 csomag (Basic / Silver / Gold / Platinum) PLANS konstanssal
  - `get_user_plan()`, `get_plan_config()`, `can_post_listing()` hirdetési limit logika
  - `can_boost()`, `do_boost()`, `is_boosted()` kiemelés rendszer
  - `filter_posts_clauses()` – boost sort a listákban (wp_postmeta LEFT JOIN + ORDER BY boost_time)
  - `ajax_admin_set_plan()` – admin AJAX plan váltás
  - `ajax_boost_listing()` – felhasználói AJAX boost
- [x] `vadaszapro-core.php` – bekötve: require + VA_User_Roles::init()
- [x] `includes/class-ajax.php` – submit_listing: kredit alapú limit → plan alapú limit csere
- [x] `admin/class-settings-page.php` – render_users() teljes újraírás:
  - Plan összefoglaló kártyák (4 db felhasználószámmal)
  - User tábla: avatar, plan badge, boost cooldown, havi progress bar
  - Inline plan editor (AJAX, platinum extra mezők: custom limit/cooldown/note)
  - Lapozó (40/oldal), keresés + plan szűrő
- [x] `admin/class-admin.php` – Social Media sidebar elem hozzáadva
- [x] `frontend/templates/user/dashboard.php` – Plan badge a navban + boost gomb per-hirdetés
- [x] `frontend/templates/listing/card.php` – boost badge (⚡ Előre téve, 14 napos ablak)
- [x] `frontend/css/frontend.css` – .va-card__badge--boost CSS hozzáadva
- [x] Deploy Plugin: kész ✅

### Eredmeny
- 4 tervszint (Basic/Silver/Gold/Platinum) teljes backend + admin + frontend integráció
- Admin bármely felhasználó csomagját módosíthatja (Platinum: egyedi limit + cooldown + note)
- Felhasználók a dashboardon ⚡ Előre gombbal kiemelhetik saját hirdetéseiket
- Kiemelési cooldown csomagonként: Basic=7n, Silver=5n, Gold/Platinum=3n
- Kiemelt hirdetések a kategória-listában elöl jelennek meg (boost_time ORDER BY)

 (Vadásznaptár desktop: alsó fekete csúszka + felső hónap-indikátor eltávolítás)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/index.php` – nyitooldali vadásznaptárból kiveve a felső, scroll közbeni hónap-indikátor (`va-hn-month-ind`)
- [x] a hozzá tartozó JS logika törölve (`updateHnMonthIndicator` és hívásai)
- [x] vízszintes chart csúszka desktop stílus finomítva: fekete alsó scrollbar track + sötét thumb (`.va-hnaptar__scroll`)
- [x] `wp-theme/vadaszapro-theme/page-vadasz-naptar.php` – külön vadásznaptár oldalon is kiveve a felső hónap-indikátor (`vn-chart-month-ind`)
- [x] a hozzá tartozó JS logika törölve (`updateVnMonthIndicator` és hívásai)
- [x] vízszintes chart csúszka desktop stílus finomítva: fekete alsó scrollbar track + sötét thumb (`.vn-chart-scroll`)
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy Theme: kész ✅

### Eredmeny
- Desktopon a vadásznaptár vízszintes csúszkája sötét/fekete megjelenésű az alsó sávban.
- A naptár tetején oldalra húzáskor már nem jelenik meg hónapnév-kijelzés.

---

## 2026. 04. 19. – Session #103 (Termékoldal Designer admin menü + wireframe presetek + frontend paraméterezés)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/admin/class-admin.php` – új admin menüpont: `Termékoldal` (`vadaszapro-single-designer`)
- [x] topbar cím map frissítve: `Termékoldal Designer`
- [x] sidebarban új elem: `🧱 Termékoldal`
- [x] `wp-plugin/vadaszapro-core/admin/class-settings-page.php` – új settings csoport: `va_single_settings` (layout, galéria, tipó, gombok, színek, viewer)
- [x] új preset action hook: `admin_post_va_apply_single_preset`
- [x] új preset handler: `handle_apply_single_preset()`
- [x] új preset gyűjtemény: `get_single_presets()` (Cinematic Hero, Compact Trade, Editorial Stack)
- [x] új render oldal: `render_single_designer()`
- [x] grafikus wireframe előnézet élő frissítéssel (input/change → preview CSS változók)
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – `va_single_*` opciók beolvasása és validálása
- [x] dinamikus inline CSS: max szélesség, oszlopok, gap, fő kép arány/fit, thumb méret, card radius/padding, cím/ár/meta méret, gomb méret, share méret, accent/glass/border, viewer háttér
- [x] layout osztály kapcsolás: `sl--layout-split` / `sl--layout-stacked`
- [x] Hibavizsgalat: modosított fájlok hibamentesek

### Eredmeny
- A termékoldal teljes megjelenése adminból paraméterezhetővé vált, presetekkel és vizuális wireframe előnézettel.
- A mentett beállítások azonnal érvényesülnek a hirdetés részletes oldalon.

---

## 2026. 04. 19. – Session #102 (Naptar UX Pro: scroll hint + sticky oszlop arnyek + mini honap indikator)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/index.php` – nyitooldali vadasz naptar UX bovites:
- [x] mobil scroll hint: "Húzd oldalra a naptárat"
- [x] mini honap indikator (scroll pozicio alapjan)
- [x] sticky elso oszlop (faj/csoport) vizualis arnyek leválasztassal
- [x] `wp-theme/vadaszapro-theme/page-vadasz-naptar.php` – ugyanazon UX fejlesztesek a kulon naptar oldalon
- [x] JS: overflow allapot figyeles + resize recalculation + scroll allapot markolas
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #101 (Vadasz naptar teljes responsive overhaul: nyitooldal + kulon oldal)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/index.php` – nyitooldali `va-hnaptar` mobil-first atdolgozas
- [x] fix oszlopszelessegek CSS valtozora emelve (`--va-hn-name-w`)
- [x] chart olvashatosag: fixalt minimum szelessegek viewporttol fuggoen (`980/860/760/680/620`)
- [x] tobb torespont (1024, 760, 560, 420) + tipografia/padding finomitas
- [x] extrém keskeny kijelzon sub sorok tomoritese (sub elrejtese 420px alatt)
- [x] `wp-theme/vadaszapro-theme/page-vadasz-naptar.php` – azonos responsive hardening a kulon naptar oldalon
- [x] oszlopszelesseg valtozo (`--vn-name-w`) + chart min-width lepcsok
- [x] Deploy Theme: exit 0 ✅

### Eredmeny
- A naptar elemei nem tornek szet mobilon, olvashatoak maradnak, a chart konzisztensen vizszintesen gorgetheto.

---

## 2026. 04. 19. – Session #100 (Viewer responsive hardening + touch gesztusok)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – erinteses gesztusok a fullscreen viewerben:
- [x] swipe balra/jobbra kepvaltas (zoom=1 allapotban)
- [x] touch drag mozgatás nagyitasnal (zoom>1)
- [x] `wp-theme/vadaszapro-theme/style.css` – safe-area hardening (`env(safe-area-inset-*)`) toolbar/close/nav elemekre
- [x] Extra responsive finomhangolas 420px alatt (kontroll meretek, zoom trigger felirat elrejtese)
- [x] `touch-action: none` a stage-en a stabil mobil interakcioert
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #99 (Viewer lapozas javitas: elozo/kovetkezo kep)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – fullscreen viewerbe elozo/kovetkezo gombok (`sl-viewer-prev`, `sl-viewer-next`)
- [x] Billentyuzet navigacio: nyil balra/jobbra kepvaltas, ESC bezaras
- [x] Main kep + thumb aktiv allapot szinkronizalas lapozas kozben
- [x] `wp-theme/vadaszapro-theme/style.css` – viewer nav gomb stilusok (desktop + mobil)
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #98 (Single product full-frame galeria + profi zoom kepnezegeto)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/style.css` – fo galeria kep frame-kitoltese (`object-fit: cover`) + zoom trigger gomb
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – fullscreen kepnezegeto overlay bevezetese
- [x] Viewer funkciok: megnyitas fokeprol, ESC/overlay bezaras, zoom +/-, reset, egergorgos zoom, drag mozgatas nagyitasnal
- [x] Mobil finomhangolas (1:1 fo kep, toolbar/close pozicio)
- [x] Safari kompatibilitas: `-webkit-backdrop-filter`, `-webkit-user-select`
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #97 (Szerkeszteskor ures keppaletta javitas)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/frontend/templates/listing/submit-form.php` – edit mod kepbetoltes robust fallback logika:
- [x] elso korben `va_gallery_ids` olvasas (uj formatum)
- [x] ha ures, legacy `va_gallery` kezeles (tomb es vesszos string formatum)
- [x] ha csak kiemelt kep van, az is bekerul a palettaba
- [x] ha a kiemelt kep nincs a gallery listaban, automatikusan elore beszurjuk
- [x] Eredmeny: regi es uj hirdeteseknel is megjelennek a mar feltoltott kepek szerkeszteskor
- [x] Deploy Plugin: exit 0 ✅

---

## 2026. 04. 19. – Session #96 (Social ikon minoseg + link masolas javitas + frontend push toast rendszer)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/includes/helpers.php` – social ikonkeszlet finomitas, hivatalosabb brand karakter (kulon Facebook lettermark)
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – megosztas ikonok atallitva kozponti `va_social_svg()` helperre (header/footer/single egyseges)
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – link masolas javitas:
- [x] `navigator.clipboard` secure context esetben
- [x] fallback `document.execCommand('copy')` insecure/local kornyezetre
- [x] siker/hiba push visszajelzes masolas utan
- [x] `wp-plugin/vadaszapro-core/frontend/js/frontend.js` – push toast globalizalas (`window.va_toast`), title javitas (`Sikeres` / `Sikertelen`), szerver oldali `va-notice` automatikus push-ra tukrozese
- [x] `wp-plugin/vadaszapro-core/frontend/templates/listing/submit-form.php` – hirdetes feladas/szerkesztes osszes AJAX kimenetere push notifikacio (siker, hiba, kredit/fizetes kovetelmeny, halozati hiba)
- [x] `wp-plugin/vadaszapro-core/frontend/css/frontend.css` – push szinkodok javitasa: siker = zold, hiba = piros
- [x] Deploy All: exit 0 ✅

### Megjegyzes
- A push visszajelzes immar form menteseknel es masolasnal is konzisztensen mukodik.

---

## 2026. 04. 19. – Session #95 (Admin Panel teljes személyre szabás – presetek + live preview + CSS injektálás)

### Mit csinaltunk [x]
- [x] `class-settings-page.php` – `render_adminpanel()` megírva (~400 sor): teljes UI szerkesztőoldal
  - 6 szekció: Márka/logó, Háttér rétegek, Szöveg+szegély, Accent szín, Layout méretek, Betűtípus
  - 10 db egy kattintásos preset (Dark Crimson, Midnight Navy, Forest Command, Obsidian Gold, Graphite Purple, Carbon Steel, Copper Dark, Steel Ember, Arctic White, Royal Plum)
  - Beágyazott live preview (topbar + sidebar + KPI kártyák + tábla szkeletonnal)
  - JS: form input → preview CSS változók real-time frissítése
- [x] `class-settings-page.php` – `handle_apply_ap_preset()`: nonce-védett preset alkalmazás, `update_option` loop, redirect `?va_ap_preset=ok/invalid`
- [x] `class-settings-page.php` – `get_adminpanel_presets()`: 10 preset tömb, minden preset tartalmaz bg/bg2/accent/accent2 swatchokat + teljes options[] map-et
- [x] `class-settings-page.php` – `register_settings()`: 19 db `va_ap_*` option regisztrálva (`va_ap_settings` csoport)
  - Branding: panel_name, panel_icon, logo_url, logo_height
  - Színek: color_bg, color_bg2, color_bg3, color_bg4, color_text, color_muted, color_accent, color_accent2, color_border, color_border2
  - Méretek: sidebar_width, topbar_height, radius, radius_sm
  - Font: font (14 lehetséges betűtípus slug)
- [x] `class-settings-page.php` – `init()`: `admin_post_va_apply_ap_preset` hook hozzáadva
- [x] `class-admin.php` – `inject_admin_css()`: dinamikus CSS vars injektálás `<style id="va-admin-theme-vars">:root{...}</style>` formában, admin_head hookra, csak VA oldalakon
  - rgba() értékek NEM esc_attr()-rel, hanem natívan kerülnek ki (komma/zárójel nem escape-elendő CSS-ben)
  - Font slug → teljes CSS font-stack mapping (14 font)
- [x] `class-admin.php` – `init()`: `admin_head` → `inject_admin_css` hozzáadva
- [x] `class-admin.php` – `render_shell()`: dinamikus `$ap_panel_name`, `$ap_panel_icon`, `$ap_logo_url`, `$ap_logo_height` változók, sidebar logo feltételes (kép vs emoji)
- [x] `class-admin.php` – `register_menus()`: `vadaszapro-adminpanel` submenu regisztrálva → `VA_Settings_Page::render_adminpanel`
- [x] `class-admin.php` – sidebar nav: "🖥️ Admin Panel" menüpont hozzáadva Beállítások szekció végére
- [x] `class-admin.php` – titles map: `"vadaszapro-adminpanel" => "Admin Panel beállítások"` bejegyezve
- [x] Deploy Plugin: exit 0 ✅

### Korábbi session (Session #94 – Form Builder: va_admin_listing_edit form)
- [x] `class-form-builder.php` – `va_admin_listing_edit` form hozzáadva 13 default mezővel (va_price, va_price_type, va_brand, va_model, va_caliber, va_year, va_phone, va_location, va_email_show, va_featured, va_verified, va_license_req, va_expires)
- [x] `class-form-builder.php` – `date` típus ikon (📅) hozzáadva, `va_admin_listing_edit` az $allowed listában, tab renderelve "🛠️ Admin hirdetés szerkesztő" névvel
- [x] `class-listing-edit.php` – `render_edit()`: Form Builder config betöltés, mezők ki/be kapcsolható, egyéni label/placeholder szerkeszthető, custom_* mezők "Egyéb mezők" kártyán dinamikusan jelennek meg
- [x] `class-listing-edit.php` – `handle_save()`: custom_* mezők DB mentése va_sync_listing_meta() után
- [x] Deploy Plugin: exit 0 ✅

### Fontos technikai döntések
- rgba() értékek CSS-be: NEM esc_attr() (az nem bántja a zárójeleket), de jobb ha direkten adjuk ki → muted, border, border2 fields text inputon jönnek, color picker helyett
- CSS vars override: admin.css :root{} → inject_admin_css() <style> tag felülírja, sorrend garantált (admin_head jön a CSS enqueue után)
- Font Google Fonts: NEM töltjük be külső forrásból az admin panelen (performance), csak a system-ui alapú stacket adjuk meg

---

## 2026. 04. 18. – Session #93 (Form Builder: fekete szöveg CSS + egyedi mező hozzáadás/törlés)

### Mit csinaltunk [x]
- [x] `class-form-builder.php` teljes CSS újraírva WP admin fehér háttérre (minden szöveg fekete, látható)
- [x] Toggle gombok natív CSS-sel (adminban is látható zöld/piros)
- [x] Mező törlés: custom_* kulcsú sorokon 🗑 gomb → JS confirm + DOM remove
- [x] Egyedi mező hozzáadása panel: label, placeholder, típus választó, + Hozzáad gomb (Enter is)
- [x] handle_save() bővítve: custom_ mezők is mentődnek, típus validáció, üres label skip
- [x] Deploy Plugin OK ✅

---

## 2026. 04. 18. – Session #92 (Vizualis Form Builder admin + dinamikus formok)

### Mit csinaltunk [x]
- [x] Uj admin osztaly: `class-form-builder.php` (VA_Form_Builder)
- [x] Admin menube uj pont: "🧩 Form szerkesztő"
- [x] 3 form szerkesztheto grafikusan: Hirdetes feladas, Regisztracio, Bejelentkezes
- [x] Minden formon mezonkent: felhasznaloi felirat, placeholder, ki/bekapcsolas, kotelezo toggle
- [x] Sor sorrend drag-and-drop (SortableJS CDN + natív HTML5 fallback)
- [x] Config WP options-ban tarolva (`va_form_config_*`), alap visszaallitas gomb
- [x] Submit-form.php teljesen dinamikussá teve: VA_Form_Builder config szerint renderel
- [x] Register-form.php teljesen dinamikussá teve: VA_Form_Builder config szerint renderel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #91 (Hirdetes admin: fizetesi szolgaltato + szamlazasi beallitasok)

### Mit csinaltunk [x]
- [x] A Hirdetes beallitasok oldal 3 blokkra bontva: Alap dijazas, Fizetesi beallitasok, Szamlazasi beallitasok
- [x] Uj fizetesi mezok: szolgaltato valaszto (none/barion/stripe/simplepay/custom), test/live mod, public key, secret key, webhook secret
- [x] Uj URL mezok: sikeres/megszakitott fizetes URL (opcionalis feluliras)
- [x] Admin segedmezok: automatikus success/cancel callback URL minta megjelenitese
- [x] Uj szamlazasi mezok: kiallito nev/cim/adoszam/email/telefon, szamla prefix, kovetkezo sorszam, szamla labjegyzet
- [x] PDF szamla generator atkotve az uj szamlazasi mezokre (prefix + folyamatos sorszam + kiallito adatok)
- [x] Szamlasorszam auto inkrementalasa `va_invoice_next_number` alapjan
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #90 (PDF szamla szolgaltato nelkul)

### Mit csinaltunk [x]
- [x] A sikeres fizetes utani szamla TXT helyett mostantol PDF fajlba generalodik
- [x] Kulso library nelkul, belso minimal PDF builder kerult be (`Helvetica`, egy oldalas szamla layout)
- [x] Biztonsag: szovegek tisztitasa + ekezetek ASCII-ra konvertalasa PDF kompatibilitas miatt
- [x] A PDF fajl tovabbra is `uploads/va-invoices/` mappaba kerul, URL mentessel (`va_invoice_url`)
- [x] Celfunkcio: szolgaltato nelkul is valos, letoltheto PDF szamla keszuljon fizetesi siker callbacknel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #89 (Fizetesi fallback + fizetes utani aktivalas + szamla)

### Mit csinaltunk [x]
- [x] Hirdetes feladas form fallback javitas: explicit `method="post"` es `action=admin-ajax.php`, hogy ne GET-es ures oldal legyen
- [x] Fizetos limitnel a hirdetes mar nem vesz el: draftkent mentjuk (`va_listing`) fizetes elott
- [x] Fizetesi allapot meta mezok bevezetve a hirdeteshez:
- [x] `va_payment_required`, `va_payment_status`, `va_payment_amount`, `va_payment_token`
- [x] Fizetesi URL most tokenes callback parameterekkel epul (`va_payment=success|cancel`, `token`)
- [x] Uj callback feldolgozas `template_redirect` alatt:
- [x] sikeres fizetesnel automatikus status valtas `publish/pending` allapotba
- [x] megszakitott fizetesnel hirdetes draftban marad
- [x] Flash uzenetekkel egyertelmu UX visszajelzes a feladas oldalon
- [x] Szamla generalas fizetes utan:
- [x] szamlaszam (`va_invoice_no`) + osszeg + datum meta mentes
- [x] letoltetheto TXT szamla file generalas `uploads/va-invoices/` konyvtarba (`va_invoice_url`)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #88 (Admin mentes push notifikacio)

### Mit csinaltunk [x]
- [x] Uj admin push toast dizajn bevezetve (jobb felso stack, lekerekitett kartya, animacio, glow)
- [x] Menteskor azonnali informacios toast: `Mentes folyamatban...`
- [x] Ujratoltes utan a WP notice uzenetek push toastban is megjelennek (siker/hiba)
- [x] Fallback: `settings-updated=true` esetben automatikus siker toast
- [x] Cel: barmely admin mentes utan azonnali, latvanyos visszajelzes mint a kedvenceknel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #87 (1 ingyenes hirdetes, utana bankkartyas fizetes)

### Mit csinaltunk [x]
- [x] Uj hirdetes beallitasok:
- [x] `va_listing_price_after_free` (ingyenes limit utani hirdetes ara)
- [x] `va_listing_payment_url` (bankkartyas checkout URL)
- [x] Hirdetes feladas backend logika bovites:
- [x] Felhasznalo aktiv/folyamatban levo hirdetesszam ellenorzese
- [x] Ingyenes limit utan a feladas blokkolasa, fizetesi URL visszaadasa
- [x] Frontend submit form frissites:
- [x] Ingyenes maradek / fizetos dij informacios sor
- [x] Fizetes kotelezo esetben hiba helyett bankkartyas fizetes CTA gomb megjelenitese
- [x] Celfunkcio: 1 ingyenes hirdetes utan tovabbi feladas csak fizetesi lepessel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #86 (Nem-admin WP tiltás + szerepkör kezelés)

### Mit csinaltunk [x]
- [x] Nem-admin felhasznaloknal a WordPress admin bar elrejtve (`show_admin_bar` szuro)
- [x] Nem-admin user `wp-admin` hozzaferese tiltva es fooldalra iranyitva (`admin_init`)
- [x] Uj egyedi szerepkorok letrehozva: `Maganszemely` (`va_maganszemely`) es `Ceg` (`va_ceg`)
- [x] Regisztraciokor account type alapjan automatikus role kiosztas
- [x] Admin `Felhasznalok` oldalon szerepkor oszlop + role valaszto + mentes gomb minden userhez
- [x] Szerepkor modositas csak nem-admin roleokra engedett (administrator kizarva)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #85 (Elfelejtett jelszo frontendben marad)

### Mit csinaltunk [x]
- [x] `wp_lostpassword_url()` atiranyitva a sajat frontend login oldalra (`?action=lostpassword`)
- [x] A `wp-login.php?action=lostpassword|retrievepassword` keresei automatikusan a frontend oldalra redirectelnek
- [x] A `wp-login.php?action=rp|resetpass` reset link is frontend oldalra redirectel (`?action=resetpass&key=...&login=...`)
- [x] Bejelentkezes template bovitve ket uj nezetre:
- [x] Elfelejtett jelszo (email kuldes)
- [x] Uj jelszo beallitasa (key/login alapu reset)
- [x] Backend feldolgozas hozzaadva:
- [x] `va_action=lostpassword` -> `retrieve_password()`
- [x] `va_action=resetpass` -> `check_password_reset_key()` + `reset_password()`
- [x] Eredmeny: az ugyfel nem esik ki a WordPress default login feluletre
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #84 (Toggle esztetikai ujrarendezes: 1 sor, 1 magassag)

### Mit csinaltunk [x]
- [x] Account-type sor ujratervezve `inline-flex` elrendezessel (stabil 1 sor)
- [x] Egységes vizualis sor-magassag bevezetve (`--va-account-row-h`)
- [x] Label elemek es kapcsolo kozeppontra igazítva, harmonikusabb aranyokkal
- [x] Toggle meretek ujrakalibralva (42x24, 18px knob), nem dominans de jol olvashato
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #83 (Toggle magassag a szoveg ala)

### Mit csinaltunk [x]
- [x] A regisztracios account-type kapcsolo merete em-alapu skalarol mukodik a labelszoveghez kotve
- [x] A kapcsolo magassaga csokkentve, hogy vizualisan biztosan ne legyen magasabb a feliratnal
- [x] A kapcsolo gomb pozicioja/atmenete ujramertezve az uj kompakt magassaghoz
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #82 (Account type toggle meret/pozicio finomitas)

### Mit csinaltunk [x]
- [x] A regisztracios toggle kapcsolo merete csokkentve (kompaktabb switch)
- [x] A feliratok betumerete csokkentve, aranyosabb a kapcsolohoz
- [x] A teljes toggle szekcio balra igazitva (nem kozepre)
- [x] Kompaktabb container padding/gap beallitasok
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #81 (Ceges mezo tisztitas + toggle szimmetria)

### Mit csinaltunk [x]
- [x] Ceges regisztraciobol a `Szemelynev` mezo eltavolitva (nem kotelezo tobbe)
- [x] Backend validacio frissitve: ceges modban csak `Cegnev + Adoszam + Szekhely` kotelezo
- [x] Kontakt nev meta mentes/torles logika eltavolitva
- [x] Account type toggle ujrarendezve szimmetrikus gridre (kozepre zarva)
- [x] Bal/jobb label igazitas kulon finomitva, hogy vizualisan ne csusszon el
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #80 (Login/Regisztracio kulon ki-be kapcsolhato)

### Mit csinaltunk [x]
- [x] Uj altalanos admin kapcsolok: `va_enable_login`, `va_enable_register`
- [x] Az `Általános` beallitas oldalon kulon toggle-kent kezelhetok
- [x] Headerben a Bejelentkezes/Regisztracio gombok csak akkor jelennek meg, ha engedelyezettek
- [x] Footer `Fiok` oszlopban a megfelelo linkek szinten feltetelesek
- [x] Login/Register frontend oldalak tiltott allapotban urlap helyett figyelmeztetest mutatnak
- [x] Backend oldali vedelem: login/register POST feldolgozas tiltva, ha az adott funkcio ki van kapcsolva
- [x] Aukcio oldali vendeg licit gomb is figyeli a login tiltast (figyelmeztetesre valt)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #79 (Interaktiv regisztracio + ceg/maganszemely toggle)

### Mit csinaltunk [x]
- [x] Regisztracio urlap interaktivva teve: typing placeholder effekt tobb mezone
- [x] Submit allapot javitas: betoltes jelzes (`Regisztracio folyamatban...`) + loading animacio
- [x] Uj account type kapcsolo: Maganszemely / Ceg (toggle switch)
- [x] Ceges adatblokkok dinamikus megjelenitese/elrejtese JS-bol
- [x] Ceges kotelezo mezok: Cegnev, Adoszam, Szekhely, Szemelynev
- [x] Backend validacio es mentes bovites a ceges adatokra
- [x] Uj user meta kulcsok: `va_account_type`, `va_company_name`, `va_company_tax`, `va_company_seat`, `va_contact_name`
- [x] Biztonsag: ASZF checkbox szerveroldali kotelezo ellenorzese
- [x] Mobil fallback megtartva (1 oszlop)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #78 (Regisztracio rovidebb/szelesebb layout)

### Mit csinaltunk [x]
- [x] Regisztracios oldal szelesitese kulon wrapperrel (`va-auth-wrap--register`)
- [x] Desktopon 2 oszlopos regisztracios grid bevezetve, hogy kevesebb gorgetes kelljen
- [x] Checkbox + submit teljes szelessegben maradt (egyertelmu UX)
- [x] Mobil fallback: 1 oszlop 860px alatt
- [x] Inline seged szoveg stilus kiszervezve CSS osztalyba (`va-register-help`)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #77 (Kedvenceim menu sziv piros)

### Mit csinaltunk [x]
- [x] A dashboard `Kedvenceim` menupont sziv ikonja fix piros lett
- [x] Hover es aktiv allapotban is piros marad
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #76 (Dashboard menu teljes egyvonalas igazitas)

### Mit csinaltunk [x]
- [x] A dashboard menupontok icon + label kulon elemekre bontva
- [x] Fix ikon oszlop bevezetve (`grid-template-columns: 20px 1fr`), hogy minden sor tokeletesen egyvonalban legyen
- [x] A kijelentkezes sor is ugyanabba a strukturaba kerult (inline style eltavolitva)
- [x] Label oldal overflow-biztos lett (`text-overflow: ellipsis`)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #75 (Profil dashboard menupont UX fix)

### Mit csinaltunk [x]
- [x] A profil/dashboard bal menu elemei egysegesen 1 sorosak lettek (`white-space: nowrap`)
- [x] Minden menupont kez kurzort kapott (`cursor: pointer`), nem csak a kijelentkezes
- [x] A menupontok ikon + szoveg elrendezese egységesitve (`display:flex`, `align-items:center`, `gap`)
- [x] Tab kezelo JS szukitve csak a `data-tab` elemekre, igy a kijelentkezes linket mar nem fogja meg a tab script
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #74 (Kartya hover border sarok simitas)

### Mit csinaltunk [x]
- [x] A termekkartya hover border nem a fo borderen valtozik mar, hanem kulon radius-oroklo overlayen
- [x] Ez megszunteti a reces/tort sarokhatast hover allapotban
- [x] Hozzaadva `focus-within` allapot is az egyseges kiemeleshez
- [x] Finom GPU simitas: `translateZ(0)` + `backface-visibility: hidden`
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #73 (Kedvencek stabilizalas + 5s push toast)

### Mit csinaltunk [x]
- [x] A `va-frontend` JS globalisan enqueue-olva a theme-bol, hogy minden kartya nezetben biztosan aktiv legyen
- [x] Globalis `VA_Data` lokalizacio hozzaadva (ajax_url, nonce, post_id)
- [x] Kedvencek kattintasnal robosztus hibakezeles bevezetve (missing adat, backend hiba, halozati hiba)
- [x] Dupla kattintas vedelme (`busy` flag)
- [x] Uj jobb felso push ertesites design (lekerekitett kartya, glow, elegans be/ki animacio)
- [x] Toast eletciklus: 5 masodperc
- [x] Cel: azonnali vizualis visszajelzes + megbizhato kedvencek mentes
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #72 (Kedvencek mentes hibajavitas)

### Mit csinaltunk [x]
- [x] A kedvencek gomb mukodese fuggetlenitve a globalis `VA_Data` objektumtol
- [x] A gomb most sajat `data-nonce` es `data-ajax-url` adatokat kap
- [x] A frontend JS watchlist handler ezeket hasznalja, fallbackkel
- [x] `stopPropagation()` hozzaadva, hogy ne zavarja kartya-link interakcio
- [x] Cel: kattintasra biztos kedvencekbe mentes + profilban megjelenes
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #71 (Kedvencek sziv az ar melle helyezve)

### Mit csinaltunk [x]
- [x] A kartyan a kedvencek sziv mar nem a kepre van pozicionalva
- [x] A sziv fizikailag az ar soraba kerult (ar melle)
- [x] A gomb emiatt nem takarja a kepet es stabilan kattinthato marad
- [x] A kartya cim linkes maradt, a kedvencek gomb kulon, tiszta interakcios zonat kapott
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #70 (Kartya kedvencek szivecske redesign)

### Mit csinaltunk [x]
- [x] A kartya kedvencek gomb karakteres `♥` ikonja lecserelve valodi SVG szivre
- [x] A szivecske fix pirosra allitva minden allapotban
- [x] Vizualis finomitas: kor alakú gomb, piros keret, enyhe glow, elegans hover animacio
- [x] Cel: markans, szep, azonnal felismerheto kedvencek ikon
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #69 (Layout Allito grafikus abra blokkok)

### Mit csinaltunk [x]
- [x] A Layout Allito tetejere vizualis magyarazo kartyak kerultek (nem steril tabla)
- [x] Grafikus blokkok: kontener + oldalpárna, responsive toréspont flow, kartya anatomia
- [x] Kulon admin CSS keszult a diagram elemekhez (dark panel, piros kiemeles, responsziv admin elrendezes)
- [x] Cel: gyorsabb ertelmezes, egyertelmu "mi mit csinal" UX
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #68 (Mobil-first responzivitas + Breakpoint Preview)

### Mit csinaltunk [x]
- [x] A Layout Allitoba bekerultek a toréspont vezerlok (desktop→tablet, tablet→mobil, oldalsav rejtese)
- [x] A dinamikus frontend CSS most ezeket a toréspontokat hasznalja
- [x] Felső admin savba uj menu: `VA Breakpoint Preview`
- [x] Preset preview szelessegek: 1440, 1280, 1024, 820, 480, 390, 375, 320 px
- [x] Bricks-szeru egyedi kezi szelesseg megadasa prompttal (`Egyedi szelesseg (px)…`)
- [x] Preview modban a teljes oldal a valasztott szelessegre van constrainelve + jobb also jelzes mutatja az aktualis px-et
- [x] Cel: maximalis mobil-ellenorizhetoseg valos torespontokkal
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #67 (Divi/Porto mintaju Layout Allito)

### Mit csinaltunk [x]
- [x] Uj admin menupont: `Layout Allito`
- [x] Kulon settings csoport letrehozva: `va_layout_settings`
- [x] Sok parameteres layout panel bevezetve (preset, kontener, tartalom, oldalsav, grid, kartya, widget, hover/arnyek)
- [x] Preset modok: Porto / Divi / Custom
- [x] Frontend dinamkus CSS bekotve a layout opciokra (kontener szelesseg, padding, grid oszlopszam, gap, kartya radius, kep arany, meta meret, oldalsav szelesseg/sticky stb.)
- [x] Alapelv: nagylepteku, hirdetes-fokuszu testreszabhatósag egy helyen
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #66 (Favicon bal sarok utanjavitas)

### Mit csinaltunk [x]
- [x] Favicon biztonsagi margoja tovabb novelve (14%)
- [x] Fajlvariant frissitve `safe3`-ra, hogy garantaltan ujrageneraljon
- [x] Celzott javitas: bal sarok lecsapas megszuntetese
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #65 (Favicon bal oldal lecsapas javitas)

### Mit csinaltunk [x]
- [x] Favicon generalas javitva biztonsagi belso margoval (8%)
- [x] A kesz ikon PNG atlatszo canvason keszul, hogy a jel ne erjen a szelere
- [x] Versionalt favicon fajlnev bevezetve cache-bustinghoz (`safe2`)
- [x] Fallback megtartva: GD hianyaban WP image editor
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #64 (Kartya meta egy sor alap + migracio)

### Mit csinaltunk [x]
- [x] A kartya meta alapertelmezett sor-szama 1 sorra allitva
- [x] A hely + datum alapertelmezett sorhoz rendelese 1. sorra allitva
- [x] Biztonsagos migracio bevezetve: ha a korabbi alap 2 soros hely+datum konfiguracio van, automatikusan 1 sorra valt
- [x] Az admin testreszabhatosag teljesen megmaradt
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #63 (Kartya meta teljes admin vezerles)

### Mit csinaltunk [x]
- [x] A kartya meta megjelenites teljesen adminbol allithato lett
- [x] Minden meta parameter kulon kapcsolhato: kategoria, megye, telepules/hely, megtekintes, felado, datum
- [x] Minden parameter kulon sorhoz rendelheto (1-3. sor)
- [x] Layout parameterek is adminbol allithatok: sorok szama, oszlopkoz, soron beluli sorkoz, sorblokkok kozti tavolsag
- [x] Alapertelmezett beallitas a kert igeny szerint: csak hely + datum
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #62 (Kartya meta 2 soros elrendezes)

### Mit csinaltunk [x]
- [x] A kartya meta blokk 3 sorrol 2 sorra atalakitva
- [x] 1. sor: kategoria + megye + telepules
- [x] 2. sor: megtekintes + felado + datum
- [x] Cel: tomorebb, egysegesebb sorstruktura
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #61 (Meta elemek egymas melle igazitas)

### Mit csinaltunk [x]
- [x] Kartya meta sorok igazitasanak modositasa balra zart elrendezesre
- [x] A `space-between` eltavolitva, hogy ne legyenek indokolatlanul nagy kozok
- [x] Soron beluli elemek kozti tavolsag fixalt (column-gap/row-gap)
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #60 (Kartya meta sorkoz tomorites)

### Mit csinaltunk [x]
- [x] A kartya meta sorok sortavja egységesitve es csokkentve
- [x] Sorok kozti tavolsag tomoritve (top/middle/bottom fix spacing)
- [x] Cel: kompaktabb, egysegesebb kartyamegjelenes
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #59 (Kartya meta sorstruktura fix)

### Mit csinaltunk [x]
- [x] A listing kartya meta adatai fix 3 sorra bontva
- [x] Felso sor: kategoria + megye
- [x] Kozepso sor: telepules + megtekintes
- [x] Also sor: felado + datum
- [x] Cel: nyitolapi kartya-elrendezes igazitas a kért 2. referencia kephez
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #58 (Nyitolap kartyakep fallback javitas)

### Mit csinaltunk [x]
- [x] A listing kartya kepforrasa kiegeszitve fallbackkel
- [x] Ha nincs kiemelt kep, az elso feltoltott hirdeteskep jelenik meg
- [x] Igy a nyitolap / archiv / kategoria listak kepmegjelenese egyezik a hirdetes aloldal kepforrasaival
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #57 (Idojaras 7 nap holnaptol indul)

### Mit csinaltunk [x]
- [x] A 7 napos elorejelzesbol a mai nap kiszurve
- [x] A lista mindig holnaptol indul es 7 jovobeli napot mutat
- [x] API keret 8 napra emelve, hogy a mai nap kihagyasa utan is maradjon 7 elem
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #56 (Idojaras: MA jeloles es datum egyeztetes)

### Mit csinaltunk [x]
- [x] A 7 napos sorban a mai nap mar egyertelmuen `MA` jelolest kap
- [x] A mai nap felismerese Budapest idozonara allitott datum alapjan tortenik
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #55 (Idojaras widget mukodesi hiba javitas)

### Mit csinaltunk [x]
- [x] Javitas: render sorrendhiba miatt a napi adatok valtozoja hasznalat elott nem volt inicializalva
- [x] A napi adatok deklaracioja a megfelelo helyre kerult
- [x] Eredmeny: az idojaras feldolgozas nem omlik ossze, adatok megjelennek
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #54 (Idojaras 7 napos panel osszecsukhato + datum mindenhol)

### Mit csinaltunk [x]
- [x] A 7 napos elorejelzes alapbol osszecsukott, gombbal lenyithato
- [x] Nagyobb, kontrasztosabb (feher) betuk az idojaras widgetben
- [x] Datum megjelenites bovites: aktualis idopont + napi sorokban konkret datum
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #53 (Geolokalizalt idojaras widget a Hold ala)

### Mit csinaltunk [x]
- [x] Uj idojaras widget kerult a Hold widget ala a fooldali sidebarban
- [x] Geolokalizacio: eloszor browser helymeghatarozas, fallback IP alapu helyzet
- [x] Aktualis adatok: homerseklet, hoerzet, para, szel, csapadek, allapot
- [x] Elorejelzes: 7 nap (min/max, csapadek valoszinuseg, csapadek osszeg, max szel)
- [x] Uj admin toggle: `va_show_weather_widget` (kulon ki/be kapcsolhato)
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #52 (Fooldali vadaszati naptar panel kulon kapcsolo)

### Mit csinaltunk [x]
- [x] Uj admin toggle: `va_show_home_hunting_calendar`
- [x] A nagy fooldali "Vadaszati idenyek 2026" panel (HTML + CSS + JS) opciohoz kotve
- [x] Igy kulon kikapcsolhato a panel, mikozben a tobbi tartalom marad
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #51 (Hold/Ideny/Naptar kapcsolhatosag adminbol)

### Mit csinaltunk [x]
- [x] Uj admin togglek az Altalanos beallitasokban:
- [x] `va_show_hunting_season_widget` (Vadaszati ideny widget)
- [x] `va_show_moon_widget` (Hold widget)
- [x] `va_enable_hunting_calendar_page` (Vadaszati naptar oldal)
- [x] A fooldali `index.php` mar feltetelhez kotve rendereli a ket widgetet es a kapcsolodo JS-eket
- [x] A `page-vadasz-naptar.php` oldal teljes tartalma adminbol letilthato
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #50 (Hold widget datum stilus vissza az elozore)

### Mit csinaltunk [x]
- [x] A datum/idopont megjelenes visszaallitva az elozo egyszeru stilusra
- [x] Csak a szin maradt allitva: feher (`#ffffff`)
- [x] Hibavizsgalat: modosított fajl ellenorizve

---

## 2026. 04. 18. – Session #49 (Hold widget datum lathatosag javitas)

### Mit csinaltunk [x]
- [x] A Holdnaptar datum/idopont badge kontrasztja jelentosen erosítve
- [x] A datum nagyobb betumeretet, keretet es finom hatteret kapott
- [x] Hibavizsgalat: modosított fajl ellenorizve

---

## 2026. 04. 18. – Session #48 (Ideiglenes hold szimulacio eltavolitasa, elesites)

### Mit csinaltunk [x]
- [x] A fooldali Holdnaptar ideiglenes Valos / Telik / Fogy demo vezerloi eltavolitva
- [x] A kezicsuszka es a szimulacios allapotok kiszedve a frontendbol
- [x] Az eles, valos idon alapulo hold widget maradt meg automatikus frissitessel
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #47 (Hold peremfeny visszaallitasa egy lepessel)

### Mit csinaltunk [x]
- [x] Az elozo lepes visszavonva: a finom belso peremfeny visszakerult
- [x] Csak a legutobbi hold-rajz modositas lett visszaallitva
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #46 (Hold korbefuto gyuru teljes eltavolitasa)

### Mit csinaltunk [x]
- [x] A holdon maradt korbefuto belso peremfeny gyuru eltavolitva
- [x] Igy a hold korul mar nem fut teljes koros fenyes stroke
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #45 (Valos telihold tonus + 50 eves holdnaptar nezet)

### Mit csinaltunk [x]
- [x] A fooldali hold widget melegebb, valosabb szintonusokat kap telihold kozeleben
- [x] Teliholdnal a hold kep mar nem csak szurke: mehet elefantcsont, sargas vagy vorosesebb tone-ba
- [x] A vadasz-naptar oldalon uj honap/ev valaszto kerult be
- [x] Uj gyorsgombok: Mai honap es +50 ev
- [x] Az oldal az aktualis evtol 50 evre elore enged navigalni a holdnaptarban

---

## 2026. 04. 18. – Session #44 (Hold kulso kor eltavolitasa)

### Mit csinaltunk [x]
- [x] A hold canvas vegso kulso korvonala eltavolitva
- [x] A belso megvilagitott perem megmaradt, csak a kulso gyuru tunik el
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #43 (Hold peremfeny + ideiglenes fazis szimulacio)

### Mit csinaltunk [x]
- [x] A megvilagitott holdperem visszakapta a jobban lathato fenyes szegelyt
- [x] Ideiglenes szimulacio vezerlok bekerultek: Valos / Telik / Fogy + kezicsuszka
- [x] A szimulacios mod sajat allapotszoveget kap, hogy egyertelmu legyen a demo nezet
- [x] Hibavizsgalat: modosított JS/PHP fajl hibamentes

---

## 2026. 04. 18. – Session #42 (Hold szazalek sav lathatosag javitas)

### Mit csinaltunk [x]
- [x] A hold megvilagitottsag szazalek badge eros kontrasztot kapott
- [x] Uj vizualis sav kerult a hold ala, ami a megvilagitott resz %-at mutatja
- [x] A sav JS-bol frissul az aktualis holdfazis alapjan
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #41 (Valodi hold skin bekotese)

### Mit csinaltunk [x]
- [x] A Holdnaptar render motor valodi holdfoto texturat kapott (kulso forras)
- [x] Kep betoltesre automatikus redraw kerult be, nem kell varni 1 percet
- [x] Ha a kulso kep nem erheto el, fallback marad a proceduralis textura
- [x] Hibavizsgalat: modosított fajl hibamentes
- [x] Deploy: `Deploy Theme` lefutott

---

## 2026. 04. 18. – Session #40 (Holdnaptar valosaghubb holdfelszin + kraterek)

### Mit csinaltunk [x]
- [x] A Holdnaptar canvas rajzolo logika teljesen frissitve valosaghubb felszinre
- [x] Proceduralis texturagenerator kerult be: tobb retegu zaj, limb darkening, mare foltok
- [x] Krater modellezes javitva: perem + melyedes (pit) a termeszetesebb felszinert
- [x] Terminator feny/arnyek atmenet finomitva a fazis fuggvenyeben
- [x] Ujhold kozeli earthshine visszaverodes finoman megtartva
- [x] Hibavizsgalat: modosított fajl hibamentes
- [x] Deploy: `Deploy Theme` lefutott

---

## 2026. 04. 18. – Session #39 (Minden admin mező érték láthatóság fix)

### Mit csináltunk [x]
- [x] Bevezetve központi `get_display_option()` fallback a settings oldalon
- [x] Ha egy opció üres/hiányzik, a mező most a regisztrált default értéket mutatja
- [x] Kiterjesztve az összes helper mezőre: text/email/url/media/number/decimal/select/color/toggle
- [x] Eredmény: nem maradnak "vak" üres mezők, mindenhol látható aktuális vagy default érték
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #38 (Admin mezők láthatósági fix)

### Mit csináltunk [x]
- [x] Javítva az admin beállítás oldalak input láthatósági hibája
- [x] Minden settings mezőre kontrasztos stílus: fehér háttér + sötét szöveg + fókusz keret
- [x] Kiterjesztve text/email/url/number/select/textarea mezőkre
- [x] Eredmény: a beírt és mentett értékek minden admin oldalon olvashatóak

---

## 2026. 04. 18. – Session #37 (Lábléc logó pozíció finomítás)

### Mit csináltunk [x]
- [x] A lábléc első oszlopában a logó markup áthelyezve a cím alá
- [x] Eredmény: a logó a vonal alá kerül, nem a szöveg/cím fölé

---

## 2026. 04. 18. – Session #36 (Visszanullázódó mentés végleges ok + javítás)

### Mit csináltunk [x]
- [x] Azonosítva: a `render_design()` és `render_header_footer()` mentési groupja fel volt cserélve
- [x] Javítva: Design oldal újra `va_design_settings` csoportot ment
- [x] Javítva: Fejléc + Lábléc oldal újra `va_header_footer_settings` csoportot ment
- [x] A fejléc/lábléc alapszín mezők visszakerültek a Fejléc + Lábléc oldalra
- [x] Következmény: a hero méret mezők mentéskor már nem nullázódnak vissza
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #35 (Footer logó adminból)

### Mit csináltunk [x]
- [x] Új opciók a Fejléc + Lábléc oldalon: lábléc logó URL + lábléc logó magasság
- [x] A logó média pickerrel választható (`field_media`)
- [x] A lábléc első oszlopában megjelenik az adminban beállított logó
- [x] Biztonságos fallback: ha nincs megadva logó, a layout nem törik
- [x] Új CSS osztály a lábléc logóhoz (`.va-footer__brand-logo`)
- [x] Hibavizsgálat: módosított fájlok hibamentesek

---

## 2026. 04. 18. – Session #34 (Footer szétesés vizsgálat + fallback javítás)

### Mit csináltunk [x]
- [x] Footer vizsgálat lefuttatva (markup + dinamikus CSS + hibavizsgálat)
- [x] Azonosított kockázat: üresre mentett opciók esetén a lábléc feliratok eltűnhetnek
- [x] `footer.php` megerősítve: minden opció-vezérelt footer címke/szöveg kötelező fallbacket kap
- [x] Így a lábléc feliratok nem tudnak üresen maradni hibás mentés után sem
- [x] Hibavizsgálat: `footer.php` hibamentes
- [x] Deploy: `Deploy Theme` lefutott

---

## 2026. 04. 18. – Session #33 (Hero vs Header mentési konfliktus javítás)

### Mit csináltunk [x]
- [x] Az ok azonosítva: hero/design és fejléc/lábléc mezők ugyanabban a settings groupban voltak
- [x] Külön settings group létrehozva a fejléc/lábléc oldalhoz: `va_header_footer_settings`
- [x] A fejléc/lábléc opciók átemelve dedikált regisztrációba, így mentéskor nem nullázza a másik oldal mezőit
- [x] A Design oldalról kikerültek a fejléc/lábléc mezők, hogy ne legyen keveredés
- [x] A Fejléc + Lábléc oldal saját `settings_fields` blokkot kapott
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #32 (10 db egykattintásos fejléc/lábléc preset)

### Mit csináltunk [x]
- [x] Fejléc + Lábléc oldalon új preset blokk: 10 db egykattintásos modern paletta
- [x] Új backend action: `va_apply_hf_preset` nonce + jogosultság ellenőrzéssel
- [x] Minden preset egyszerre állítja a header/footer gradient, border, shadow, glow és link hover opciókat
- [x] Preset nevek: Carbon Red, Steel Ember, Night Copper, Midnight Ice, Forest Glass, Obsidian Gold, Graphite Rose, Arctic Mint, Royal Plum, Desert Sand
- [x] Siker/hiba admin visszajelzés beépítve preset alkalmazás után
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #31 (Full export/import + modern fejléc/lábléc paletta)

### Mit csináltunk [x]
- [x] Export/Import oldal bővítve teljes migráció opciókkal
- [x] Export: opcionálisan taxonómiák (`va_category`, `va_county`, `va_condition`) és fix oldalak tartalma is mehet a JSON-ba
- [x] Import: opcionálisan taxonómiák és oldalak visszaállítása/upsertje is lefut
- [x] Import visszajelzés bővítve: opciók + taxonómiák + oldalak darabszáma
- [x] Fejléc + Lábléc admin oldal modern színpaletta mezőkkel bővítve
- [x] Új fejléc vezérlés: gradient alapszínek, border szín, shadow szín, glow szín, kereső glow, CTA glow
- [x] Új lábléc vezérlés: gradient alapszínek, border szín, shadow/glow, link hover szín
- [x] Frontend dinamikus CSS bekötve az új paletta/árnyék opciókra
- [x] Hibavizsgálat: módosított fájlok hibamentesek

---

## 2026. 04. 18. – Session #30 (Admin Export/Import + Alaphelyzet)

### Mit csináltunk [x]
- [x] Új admin almenü: `Export / Import` a VadászApró menü alatt
- [x] Export funkció: teljes `va_*` opciókészlet JSON fájlba mentése
- [x] Import funkció: JSON visszatöltés, összes `va_*` opció frissítése
- [x] Alaphelyzet funkció: összes `va_*` beállítás törlése (kivéve védett kulcsok), majd defaultok újraépítése
- [x] Biztonság: jogosultság-ellenőrzés + nonce minden műveletnél
- [x] Admin visszajelzés: siker/hiba üzenetek import és reset után
- [x] Hibavizsgálat: módosított admin fájlok hibamentesek

---

## 2026. 04. 18. – Session #29 (Külön Fejléc + Lábléc admin menü, teljes paraméterezés)

### Mit csináltunk [x]
- [x] Új admin almenü: `Fejléc + Lábléc` a VadászApró menü alatt
- [x] Új, részletes fejléc opciók: magasság, belső spacing, üveg-hatás opacitás/blur, shadow
- [x] Új, részletes kereső opciók: szélesség, magasság, radius, border/bg alpha, ikonméret, ikon háttér
- [x] Új fejléc gomb opciók: radius, padding, glow, user gomb border/bg alpha
- [x] Új mobil kapcsolók: kereső és piros CTA gomb mobil láthatóság
- [x] Új fejléc szöveg opciók: kereső placeholder, login/register/submit feliratok
- [x] Új lábléc layout opciók: padding, grid gap, min oszlopszélesség, border alpha, max width
- [x] Új lábléc szöveg opciók: oszlopcímek, jogi link feliratok, copyright sor
- [x] Frontend bekötés kész: `functions.php` dinamikus CSS now kezeli az új fejléc/lábléc mezőket
- [x] `header.php` és `footer.php` opció-vezérelt szövegezést kapott
- [x] Hibavizsgálat: módosított fájlok hibamentesek

---

## 2026. 04. 18. – Session #28 (Repo rendrakás + kanonikus források)

### Mit csináltunk [x]
- [x] A keresőgomb aktív theme-ben fehér nagyító ikont kapott
- [x] Az egyértelműen nem használt, gyökérszintű duplikált theme/plugin forrásfájlok eltávolítva
- [x] A kiürült duplikált mappák törölve (`admin`, `frontend`, `includes`, `vadaszapro-theme` a repo gyökerében)
- [x] A repo hivatalos forrásai rögzítve:
  - `wp-plugin/vadaszapro-core`
  - `wp-theme/vadaszapro-theme`
- [x] `TELEPITES.md` frissítve, hogy később se keveredjen vissza a többforrásos állapot

---

## 2026. 04. 18. – Session #27 (Kereső belső keret törlés + hover-only külső neon)

### Mit csináltunk [x]
- [x] A kereső belső input-kontúr/fókuszkeret teljesen nullázva lett
- [x] A külső pontozott piros keret megszüntetve alapállapotban
- [x] Helyette csak hover/focus alatt jelenik meg finom külső neon piros kiemelés
- [x] A fix rásegítő override bekerült mindhárom theme `style.css` példányba
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #26 (Kereső végső override + duplikált theme szinkron)

### Mit csináltunk [x]
- [x] A kereső végső override stílusa ráírva a duplikált theme `style.css` fájlok végére is
- [x] A wp-theme példány is pontozott neon külső keretre lett állítva
- [x] Az input saját fókusz-border/outline/box-shadow teljesen nullázva lett
- [x] Cél: sehol ne maradjon belső piros kijelölés, csak a külső keret maradjon hangsúlyos
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #25 (Kereső nagyító láthatóság + piros belső kiemelés törlése)

### Mit csináltunk [x]
- [x] A keresőmező belső piros kiemelése eltávolítva
- [x] A jobb oldali ikon fekete nagyítóra állítva
- [x] A nagyító jobb láthatóságot kapott világos kör háttérrel
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #24 (Automatikus cache-bust CSS módosításokhoz)

### Mit csináltunk [x]
- [x] A theme `style.css` verziója most már `filemtime()` alapján töltődik
- [x] A plugin `frontend.css` is automatikus cache-bust verziózást kapott
- [x] Cél: a CSS módosítások biztosan azonnal megjelenjenek, ne ragadjon bent régi stílus
- [x] Hibavizsgálat: `functions.php` hibamentes
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #23 (Keresősáv dinamikus felülírás javítás)

### Mit csináltunk [x]
- [x] Javítva a Design rendszerből érkező dinamikus CSS felülírás
- [x] A keresőgomb kikerült a globális header accent háttérszabályból
- [x] Így az egyszerűsített kis piros nagyító stílus ténylegesen érvényesül
- [x] Hibavizsgálat: `functions.php` hibamentes
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #22 (Header keresősáv egyszerűsítés)

### Mit csináltunk [x]
- [x] Header keresősáv leegyszerűsítve, ugyanakkora hossz/magasság megtartásával
- [x] A jobb oldali nagy piros blokk helyett kis piros nagyító ikon került be
- [x] A nagyító enyhe lebegő animációt kapott
- [x] A mező sima lekerekített, letisztult megjelenést kapott
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #21 (Lebegő vissza-a-tetjére gomb körkörös indikátorral)

### Mit csináltunk [x]
- [x] A felső header progress sáv teljesen eltávolítva
- [x] Helyette lebegő gomb került az oldal aljára
- [x] Körkörös piros indikátor fut körbe görgetés alapján
- [x] Középen animált piros felfelé mutató nyíl került be
- [x] Kattintásra sima visszagörgetés az oldal tetejére
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #20 (Header felső/alsó tér növelése logóhoz)

### Mit csináltunk [x]
- [x] Header magasság növelve (`--nav: 66px`)
- [x] Header belső függőleges padding növelve (`.va-header__inner`: felül/alul több hely)
- [x] Cél: logó ne érjen bele az alsó progress csík/border zónába
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #19 (Hero sorköz magasság állítás)

### Mit csináltunk [x]
- [x] Design oldalon új hero sorköz (line-height) mezők:
  - főoldal cím + alcím
  - kategória cím + alcím
  - alkategória cím + leírás
  - kapcsolat cím + alcím
- [x] Frontenden dinamikus CSS-be bekötve (`line-height` felülírás)
- [x] Új helper a theme-ben: `va_design_float_option()`
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #18 (Dinamikus header scroll progress csík)

### Mit csináltunk [x]
- [x] A header alján lévő fix piros csík eltávolítva
- [x] Új dinamikus 2px progress csík került be a header aljára
- [x] A csík görgetés alapján töltődik/leürül (le/fel görgetéskor)
- [x] Markup + CSS + JS bekötés (`header.php`, `style.css`, `footer.php`)
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #17 (Főoldali hero logó feljebb igazítás)

### Mit csináltunk [x]
- [x] A főoldali hero logó 15px-el feljebb került (`transform: translateY(-15px)`)
- [x] Deploy: `Deploy All` lefutott
- [x] Ellenőrzés: új hiba nem keletkezett, csak korábbról ismert CSS kompatibilitási warningok maradtak

---

## 2026. 04. 18. – Session #16 (Admin mobil szorzó vezérlés)

### Mit csináltunk [x]
- [x] Design oldalon új mobil skála mezők:
  - `va_mobile_factor_hero`
  - `va_mobile_factor_header`
  - `va_mobile_factor_footer`
- [x] A fluid `clamp()` képletekbe bekötve a mobil szorzók (70–120%)
- [x] Új helper: `va_design_scaled_ratio()`
- [x] Validáció: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #15 (Reszponzív fluid méretezés a Design vezérléshez)

### Mit csináltunk [x]
- [x] A Design oldalon állítható méretek kimenete fix px helyett fluid `clamp()` alapú lett
- [x] Hero / fejléc / lábléc szövegméretek mobilra és tabletre automatikusan arányosodnak
- [x] Új helper került be a theme-be: `va_design_fluid_px()`
- [x] Validáció: `functions.php` hibamentes
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #14 (Összes hero + fejléc + lábléc méret/típus vezérlés)

### Mit csináltunk [x]
- [x] Design oldalon teljes méretvezérlés az összes hero szövegre:
  - Főoldal hero (badge, cím, alcím, gomb)
  - Kategória hero (badge, cím, alcím, stat szám, stat felirat)
  - Alkategória hero (badge, cím, leírás, találatszám)
  - Kapcsolat hero (badge, cím, alcím)
- [x] Fejléc elemek méret + típus/súly beállítások:
  - brand név méret/súly
  - navigáció méret/súly
  - keresőméret
  - fejléc gombok mérete
- [x] Lábléc elemek méret + típus/súly beállítások:
  - oszlopcím méret/súly
  - link méret/súly
  - alsó sor méret
- [x] Frontend dinamikus CSS kibővítve az új opciók lekövetésére
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #13 (Kiterjedt betűtípus + színrendszer külön Design oldalon)

### Mit csináltunk [x]
- [x] Új admin aloldal: `Design` (külön menüpont az Általánostól)
- [x] Kiterjedt betűtípus választó bevezetése (20 opció, Google Fonts támogatással)
  - Globális alap betűtípus
  - Címsorok betűtípusa
  - Fejléc/Navigáció betűtípusa
  - Tartalmi szöveg betűtípusa
  - Lábléc betűtípusa
- [x] Színrendszer bevezetése 4 szinten:
  - Globális
  - Fejléc
  - Tartalom
  - Lábléc
- [x] Admin oldalon WP Color Picker bekötve a színmezőkhöz
- [x] Frontenden dinamikus CSS kimenet:
  - globális változók (`--a`, `--t`, `--t2`)
  - célzott felülírások header/content/footer részekre
- [x] Kiválasztott betűk automatikus betöltése Google Fonts-ról (`display=swap`)
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #12 (Hero elemek igazítása adminból)

### Mit csináltunk [x]
- [x] Hero elemek igazíthatósága (bal/közép/jobb) adminból bevezetve mind a 4 aktív hero blokkra:
  - Főoldal hero
  - Kategória főoldal hero
  - Alkategória hero
  - Kapcsolat hero
- [x] Új beállítások az Általános oldalon:
  - `va_home_hero_align`
  - `va_kategoria_hero_align`
  - `va_tax_hero_align`
  - `va_contact_hero_align`
- [x] Sablonok osztály-alapú igazításra átvezetve
- [x] CSS: bal/közép/jobb variánsok + gombok/lead pozicionálása igazításhoz kötve
- [x] Ellenőrzés: PHP fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #11 (Minden hero szöveg adminból szerkeszthető)

### Mit csináltunk [x]
- [x] Teljes hero szöveg-admin bevezetés az összes használt hero blokkra:
  - Főoldali hero (`header.php`)
  - Kategória főoldal hero (`page-kategoria.php`)
  - Alkategória hero (`taxonomy-va_category.php`)
  - Kapcsolat hero (`page-kapcsolat.php`)
- [x] Új opciók a `VA_Settings_Page` Általános fülön:
  - badge, cím(ek), alcím, gombszövegek, stat feliratok, találatszám utótag
- [x] Sablonok átvezetve `get_option(...)` használatra fallback alapértékekkel
- [x] Hibavizsgálat: módosított PHP fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #10 (Hero logó pozíció adminból)

### Mit csináltunk [x]
- [x] Új admin opció: `va_hero_logo_position` (Bal / Közép / Jobb)
- [x] Beállítás mező hozzáadva az Általános oldalra
- [x] Header sablon frissítve: a hero logó osztálya opció alapján vált (`vh__logo--left|center|right`)
- [x] Új CSS igazítás osztályok a hero logóhoz
- [x] Deploy futtatva (`Deploy All`), hibamentes PHP ellenőrzés

---

## 2026. 04. 18. – Session #9 (Logó méretezés adminból + favicon torzulás javítás)

### Mit csináltunk [x]
- [x] Új admin méret mezők:
  - `va_header_logo_height` (Fejléc logó magasság px)
  - `va_hero_logo_height` (Hero logó magasság px)
- [x] Header és hero logó magasság beállítás opció alapján renderelve
- [x] Header logó vizuális tisztítás:
  - háttér/keret/saroklekerekítés/árnyék eltávolítva
  - `object-fit: contain` (ne torzuljon)
- [x] Hero logó megtartva + külön méret opció adminból
- [x] Favicon torzulás ellen automata négyzetes generálás az ikon attachmentből:
  - `32x32` és `180x180` PNG favicon fájlok készülnek (`/uploads/va-favicons/`)
  - head linkek ezeket használják
  - `get_site_icon_url` fallback is ezekre mutat
- [x] Deploy futtatva (`Deploy All`), PHP hibák nélkül

---

## 2026. 04. 18. – Session #8 (Médiatáras logó/ikon + favicon torzulás javítás)

### Mit csináltunk [x]
- [x] Adminban új, médiatárból tallózható képes mezők:
  - `Ikon (automata favicon)`
  - `Fejléc logó`
  - `Hero logó (főoldal)`
- [x] Új admin JS: WordPress Media Library picker (`Tallózás` / `Törlés` + preview)
- [x] `class-admin.php`: `wp_enqueue_media()` bekötve
- [x] Header logó kirajzolás logika:
  - elsődleges: `va_header_logo_url`
  - fallback: `va_brand_icon_url`
  - végső fallback: 🦌 ikon
- [x] Hero logó hely hozzáadva a főoldali hero blokkba (`va_hero_logo_url`, fallback: fejléc logó)
- [x] Logó torzulás javítás:
  - `object-fit: contain`
  - külön osztályok: `.va-logo__img--header` és `.va-logo__img--icon`
- [x] Favicon link output javítva: nem erőltetett `type=image/png`, így SVG/WEBP esetén sem torz jellegű fallback
- [x] Deploy futtatva (`Deploy All`)

---

## 2026. 04. 18. – Session #7 (Header név + ikon + automata favicon)

### Mit csináltunk [x]
- [x] Új admin mező: `va_brand_icon_url` (Fejléc ikon URL)
- [x] Header logó átállítva opció alapra:
  - név: `va_site_name`
  - ikon: `va_brand_icon_url`
  - fallback ikon: 🦌 ha URL üres
- [x] Automata favicon: a beállított ikon URL-ből kerül ki a `head`-be
  - `rel="icon"`
  - `rel="shortcut icon"`
  - `rel="apple-touch-icon"`
- [x] `get_site_icon_url` filter: ha WP Site Icon nincs külön beállítva, az admin ikon URL szolgál faviconként
- [x] Header ikon kép stílus (`.va-logo__img`) hozzáadva
- [x] Deploy futtatva (`Deploy All`), hibamentes PHP ellenőrzés

---

## 2026. 04. 18. – Session #6 (Hero badge piros pulzáló pont)

### Mit csináltunk [x]
- [x] Főoldali hero badge-be visszakerült a piros pulzáló pont
- [x] `header.php`: `vh__badge` elé `vcp-hero__badge-dot` elem beszúrva
- [x] `style.css`: `vh__badge` inline-flex + gap, hogy a pont és a szöveg egy sorban jól látszódjon
- [x] `style.css`: hiányzó `@keyframes dotBlink` definíció hozzáadva
- [x] Deploy futtatva (`Deploy All`)

---

## 2026. 04. 18. – Session #5 (Aukció globális kikapcsolás)

### Mit csináltunk [x]
- [x] Új globális kapcsoló: `va_enable_auctions` (adminban: **Aukció funkció engedélyezése**)
- [x] Kikapcsolás esetén az aukció CPT nem regisztrálódik (`va_auction`)
- [x] Taxonómiák csak `va_listing` post type-ra kötődnek, ha az aukció tiltott
- [x] Aukciós cron + AJAX licit rendszer tiltása kikapcsolt állapotban
- [x] Aukció oldalak tiltása: `va-aukciok`, `post_type=va_auction`, archive/single -> átirányítás a hirdetés oldalra
- [x] Frontendből eltüntetve az aukció menüpont és főoldali „Futó aukciók” blokk
- [x] Live search és listázó AJAX csak hirdetésekre keres, ha aukció ki van kapcsolva
- [x] Dashboardból eltüntetve a `Licitjeim` tab
- [x] Admin menüből eltüntetve az `Aukciók` beállítás almenü kikapcsolt módban
- [x] Admin statisztikák/felhasználó lista aukciós számai feltételessé téve
- [x] Toggle mezők javítása: hidden `0` érték hozzáadva, így OFF állapot biztosan menthető
- [x] Deploy futtatva (`Deploy All`), szintaxis hibák ellenőrizve (hibamentes)

### Eredmény
- Egyetlen admin kapcsolóval az aukció funkció teljes frontend/admin jelenléte kikapcsolható.
- A „csak apróhirdetés” üzemmód külön opciós oldalként tisztán használható.

---

## 2026. 04. 18. – Session #4 (kártya egységesítés)

### Mit csináltunk [x]
- [x] Javítva: a `Hirdetések` oldalon széteső kártya layout
- [x] Ok azonosítva: archív oldalon nem töltődött be a plugin egységes `va-frontend` CSS, ezért a téma régi `.va-card` szabályai felülírták a kívánt megjelenést
- [x] `wp-theme/vadaszapro-theme/functions.php` módosítva:
  - a `va-theme` mellé globálisan betöltjük a plugin `frontend/css/frontend.css` fájlját (`va-frontend` handle)
  - dependency: `[ 'va-theme' ]`, verzió: `VA_VERSION`
- [x] Deploy futtatva (`Deploy All`) és hibavizsgálat lefuttatva (`functions.php` hibamentes)

### Eredmény
- A kártyák megjelenése egységes lett az egész oldalon (kereső, archívum, kategória): arányos kép + rendezett cím/ár/meta blokk.

### Hotfix (ugyanebben a sessionben)
- [x] Hiba: `Hirdetések` oldalon a találatszám látszott, de kártyák nem
- [x] Ok: a kártyák `va-animate` class miatt CSS-ben alapból rejtve voltak (`opacity:0`), de ezen az oldalon nem futott mindig a láthatóvá tevő JS
- [x] Javítás:
  - `frontend.css`: `va-animate` alapállapot látható
  - csak JS jelenlét esetén legyen rejtett (`html.va-js .va-animate`)
  - `frontend.js`: `document.documentElement.classList.add('va-js')`
- [x] Deploy: `Deploy All` kész

### Admin funkció bővítés
- [x] Adminból duplikálható hirdetések (`va_listing`) támogatás
- [x] Lista művelet: `Duplikálás` link a hirdetés sorában
- [x] Biztonság: jogosultság + nonce ellenőrzés
- [x] Duplikálás tartalma: cím/tartalom/kivonat + taxonómiák + post meta (lock mezők kihagyva)
- [x] Új bejegyzés státusz: `draft` (`(Másolat)` utótaggal)
- [x] Sikeres duplikálás után automatikus átirányítás az új piszkozat szerkesztőjére

### Kapcsolat oldal
- [x] Új egyedi kapcsolati oldal sablon: `page-kapcsolat.php`
- [x] Kizárólag e-mailes kapcsolatfelvétel támogatás
- [x] Backend küldés `wp_mail()`-lel, WP Mail SMTP kompatibilisen
- [x] Védelem: nonce + honeypot mező + szerveroldali validáció
- [x] Automatikus `kapcsolat` oldal létrehozás hozzáadva a theme oldal-generáláshoz
- [x] Header menü link a `/kapcsolat` oldalra megerősítve
- [x] Deploy: `Deploy All` kész

### Hero videó csere
- [x] Főoldali header hero videó fallback URL cserélve új offroad videóra
- [x] Új videó: `/wp-content/uploads/2026/04/0_Offroad_4x4_1920x1080.mp4`

### Kapcsolat űrlap UX
- [x] Telefonszám mező hozzáadva a kapcsolati űrlaphoz
- [x] Gépelős placeholder effekt a Kapcsolat oldali mezőkben
- [x] A typewriter placeholder nem ír bele a valódi input értékbe, csak a placeholdert animálja

### Videók adminból
- [x] Főoldal hero videó URL admin beállításból vezérelhető
- [x] Kapcsolat oldal videó URL admin beállításból vezérelhető
- [x] Kategóriák alatti videó URL admin beállításból vezérelhető
- [x] Új mezők: Általános beállítások oldalon (`va_home_hero_video_url`, `va_contact_hero_video_url`, `va_category_video_url`)
- [x] Sablonok átvezetve opció alapú URL-re fallbackkel

---

## 2026. 04. 17. – Session #3 (délelőtt + délután)

### Mit csináltunk [x]
- [x] `single-va_listing.php` teljes újraírás — sérült UTF-8 fájl törölve, HTML entitásokkal újraírva (encoding-biztos megoldás)
  - Minden magyar szöveg HTML entitásként: `R&eacute;szletek`, `Felad&oacute;` stb.
  - JS watchlist szöveg: `\u0151` Unicode escape
  - 2-column layout megőrizve (`.sl__` prefix)
- [x] Videó HERO szekció hozzáadva a főoldalra (`header.php`)
  - Teljes viewport (`100vh`) autoplay/muted/loop mp4 háttér
  - Rétegzett overlay: teteje átlátszó, alja belefut `rgb(6,6,6)`-ba
  - Bal oldali piros accent vonal
  - CTA gombok: Hirdetés feladása + böngészés
  - Stats sor: élő adatok (hirdetések, aukciók, felhasználók száma)
  - Animált scroll jelzés
  - Videó URL: `va_hero_video_url` WP opcióból szerkeszthető
- [x] Header / Navbar teljes 2026 modernizálás
  - `position: fixed` + **scroll-aware glass effect** (40px után aktiválódik)
  - Logo: octagon piros ikon + `vadaszapro.net` subtitle
  - Nav link hover: piros alulvonás slide-animáció (nem background highlight)
  - CTA gomb: corner-cut szögletes design + erős red glow
  - Hamburger: 3 vonal → X CSS animáció, body scroll lock mobilon
  - Aktív nav item URL alapú automatikus jelölés
  - Belső oldalak: `padding-top: var(--nav)` (`.va-site-wrap--inner`)
  - Főoldalon: hero videó tölti a 100vh-t
- [x] Reklámzónák eltávolítva fejlécből (`header_top`, `header_bottom` — ki van szedve)
- [x] Minden git-be pusholva (origin/main = HEAD)

### Hol tartunk
A téma vizuálisan teljesen megújult. A főoldalon video hero van, a header modern glass-effect navbarral rendelkezik. Az egész encoding-biztos (HTML entitások mindenhol).

**Deploy path:**
- Plugin: `D:\LocalWP\apr-vadsz\app\public\wp-content\plugins\vadaszapro-core`
- Téma: `D:\LocalWP\apr-vadsz\app\public\wp-content\themes\vadaszapro-theme`

### Nyitott TODO-k
- [ ] Főoldal hirdetés grid / category kártyák szekció a hero alá
- [ ] `archive.php` grid kártya dizájn egységesítés
- [ ] Keresési oldal (`[va_listing_search]`) vizuális refresh
- [ ] Hirdetés feladás form UX javítás
- [ ] Mobilon hero videó tesztelés (poster kép beállítás iOS-re)
- [ ] `va_hero_video_url` beállítás WP adminba bevezetni (Settings oldalra)

---



### Mit csináltunk [x]
- [x] Projekt architektúra megtervezve (WordPress plugin + téma)
- [x] `vadaszapro-core` WordPress plugin teljes struktúra létrehozva
- [x] Custom Post Types: `va_listing` (hirdetés), `va_auction` (aukció)
- [x] Taxonómiák: kategória (fa struktúra, előre feltöltve), megye (20 db), állapot
- [x] Meta mezők admin metabox-szal (ár, márka, modell, kaliber, telefon stb.)
- [x] Felhasználói rendszer: regisztráció, bejelentkezés, logout, profil szerkesztés (custom WP-oldalakon)
- [x] Aukció rendszer: AJAX licit leadás, real-time visszaszámlálás, e-mail értesítők, nyertes meghatározás (hourly cron)
- [x] 6 reklámzóna: header_top/bottom, sidebar_left/right, content_top, footer_top – mind HTML-alapú, backendből szerkeszthető
- [x] Backend Settings oldal (6 fül): Általános, Reklámzónák, Hirdetések, Aukciók, Felhasználók, Statisztika
- [x] Frontend shortcode-ok: `[va_listing_search]`, `[va_submit_listing]`, `[va_auction_list]`, `[va_login_form]`, `[va_register_form]`, `[va_user_dashboard]`
- [x] AJAX hirdetésszűrő: kulcsszó, kategória, megye, állapot, ár range, rendezés
- [x] Dashboard: hirdetések kezelése, licitek, watchlist/kedvencek, profil szerkesztés
- [x] `vadaszapro-theme` WordPress téma: SDH design (sötét, #ff0000, dot grid)
- [x] Header: sticky, logo, navigáció, reklám sáv, kategória gyorsmenü
- [x] Layout: 3 oszlop (bal sáv | tartalom | jobb sáv) – responsive
- [x] Főoldal: hero szekció, stat számok, legújabb/kiemelt hirdetések + aukciók
- [x] `single-va_listing.php`: galéria, paraméterek, telefonszám reveal, watchlist
- [x] `single-va_auction.php`: aukció box, visszaszámlálás, licit form, bid history
- [x] Aktiváláskor: adatbázis táblák, alap WordPress oldalak automatikus létrehozása
- [x] `TELEPITES.md` dokumentáció

### Hol tartunk
A teljes WordPress plugin + téma alap struktúra elkészült. A projekt felépítése:
```
D:\Vadaszat2026\
├── wp-plugin\vadaszapro-core\   ← Plugin (→ wp-content/plugins/)
├── wp-theme\vadaszapro-theme\   ← Téma (→ wp-content/themes/)
├── TELEPITES.md                 ← Részletes telepítési útmutató
└── NAPLO.md
```

### Nyitott TODO-k
- [ ] WordPress szerver beállítás (localhost WAMP/XAMPP vagy tárhely)
- [ ] Plugin + téma átmásolása a WordPress könyvtárba
- [ ] Tesztelés, hibakeresés élesben
- [ ] Képgaléria lightbox JS
- [ ] Adatvédelmi + ÁSZF oldalak szövege
- [ ] WooCommerce integráció (kiemelés fizetős)
- [ ] Üzenetküldő rendszer hirdetők között
- [ ] Schema.org SEO markup
- [ ] Google Maps helyszín térkép

---

## 2026. 04. – Session: Dizájn teljes újraírás (dark theme)

### Mit csináltunk [x]
- [x] Referencia design lekérve: `https://github.com/koltainorbert/tt1/vadasz-apro/public/index.html`
- [x] `style.css` v3.0.0 – teljes újraírás a referencia alapján:
  - Háttér: `rgb(6,6,6)` + pont rács (`radial-gradient`, 28px)
  - Akcentszín: `#ff0000` / `#ff4444`
  - Fehér szöveg (`#fff`) mindenhol
  - Header: sticky, sötét, piros `border-bottom`, `backdrop-filter: blur`
  - Cat-bar: sticky, vízszintes pill-stílusú kategória gombok emoji ikonokkal
  - Kártyák: `rgba(255,255,255,.025)` háttér, `rgba(255,255,255,.07)` keret
  - Hover: `translateY(-3px)` + piros ragyogás
  - Footer: `rgb(12,10,10)` háttér
  - Form inputok: sötét háttér, fehér szöveg, piros focus
  - Scrollbar: piros
- [x] `functions.php` – `va_category_icon()` átírva: SVG ikonok → emoji ikonok
  - slug + név alapú keresés (pl. `golyos-puska` → `🎯`, `trofea` → `🦌`)
  - 34 kategória slug leképezve
- [x] `header.php` – 🦌 emoji hozzáadva a logó mellé; bejelentkezés gomb stílus javítva
- [x] Összes fájl másolva `D:\LocalWP\apr-vadsz\app\public\...`

### Hol tartunk
WordPress theme v3.0 deployed a LocalWP-be. A dizájn sötét (dark), piros akcenttel,
emoji kategória ikonokkal – pontosan a referencia (vadasz-apro/public/index.html) stílusában.

### TODO
- [ ] Böngészőben megnézni az eredményt (localhost), esetleges apró igazítások
- [ ] Plugin card template (`va_template('listing/card')`) osztályok ellenőrzése (`.va-card__img` stb.)
- [ ] Hirdetés felvétel tesztelés
- [ ] Push (Ctrl+Shift+B)

---

## 2026. 05. – Session: Használtautó kategória-migráció

### Mit csináltunk [x]
- [x] Új központi autós adatforrás: `includes/class-vehicle-catalog.php`
  - Használtautó alapú fő kategóriák: Kisautó, Városi autó, Családi autó, Terepjáró, Kishaszonjármű
  - Márkalista bekötve a frontend űrlaphoz
  - Kivitel lista bekötve a jármű meta mezőkhöz és megjelenítéshez
- [x] `class-taxonomy.php` átírva: a régi vadász kategóriákat dataset verzióváltáskor törli és autós kategóriákat seedel
- [x] Migráció során az oldaltípus automatikusan `jarmu` módra vált
- [x] `admin/class-form-builder.php` frontend alapmezők jármű módra igazítva
  - Márka select
  - Modell / Típus szövegmező
  - Kivitel select
- [x] `frontend/templates/listing/submit-form.php` frissítve
  - Márka: használtautós lista
  - Kivitel: használtautós lista
  - Szerkesztéskor meglévő értékek visszatöltése
- [x] `single-va_listing.php` felépítmény címkézés igazítva az új kivitellistához
- [x] Root + `wp-plugin/vadaszapro-core` másolatok szinkronban tartva

### Hol tartunk
A következő WordPress betöltéskor a plugin egyszer lefuttatja a kategória-migrációt: a meglévő `va_category` elemek törlődnek, és helyettük a használtautós fő járműkategóriák jönnek létre. A jármű űrlap márkánál és kivitelnél listás választást használ.

### TODO
- [ ] LocalWP-ben megnyitni és ellenőrizni a kategória-migráció eredményét
- [ ] Hirdetés feladás teszt: márka + modell / típus + kivitel mentés
- [ ] Szükség esetén deploy (`Deploy Plugin` vagy `Deploy All`)

---

## 2026. 05. 07. – Session: /va-fiok UX finomítás

### Mit csináltunk [x]
- [x] `/va-fiok` oldal layout: admin-szeru fix bal menü + külön scrollozó tartalom
- [x] Dashboard konténer szélesség: teljes kijelző + normalis margók/gutter
- [x] Footer és footer közeli elemek kikapcsolása `/va-fiok` oldalon
- [x] "Vissza a tetejére" scroll-ring kikapcsolva `/va-fiok` oldalon
- [x] Tömeges műveletből az ár módosítás opció eltávolítva
- [x] Napi egyszeri (első megnyitás) személyre szabott üdvözlő popup beépítve
  - [x] Felhasználó neve megjelenik a popupban
  - [x] 300+ random üdvözlő variáció (kombinált üzenetkészlet)
  - [x] Innovatív, animált, dark-red vizuális modal design
  - [x] Napi limit user meta alapján: `va_daily_welcome_seen`

### Hol tartunk
A `/va-fiok` oldal most teljes szélességben, fix oldalsávval működik. A napi üdvözlő popup csak napi első belépéskor jelenik meg az adott felhasználónak, névre szóló és randomizált üzenettel.

### TODO
- [ ] Valós oldalon vizuális ellenőrzés desktop + mobil nézetben
- [ ] Igény esetén popup szövegkészlet további bővítése (hangnem / stílus profilok)

---

## 2026. 05. 07. – Session: Dashboard ikonrendszer modernizálás

### Mit csináltunk [x]
- [x] `/va-fiok` dashboard régi emoji/karakter ikonok cseréje modern inline SVG ikonokra
- [x] Bal oldali menü ikonok teljes cseréje (Hirdetések, Licit, Kedvencek, Profil, Kijelentkezés)
- [x] Tömeges művelet dropdown ikonok modernizálása (Aktiválás, Szüneteltetés, Törlés)
- [x] Sor szintű akció ikonok cseréje (ár szerkesztés, frissítés, indítás/szünet)
- [x] Státuszoknál és lejárat badge-nél a problematikus karakter ikonok eltávolítása/cseréje
- [x] Új ikon animációs rendszer (`.va-ico`) bevezetése: pulse/tilt/float effektek

### Hol tartunk
Az admin/dashboard felületen a korábbi "1970-es" karakterikonok helyett konzisztens, animált SVG ikonkészlet fut, a fő vizuális problémák javítva.

### TODO
- [ ] Éles böngészős ellenőrzés a maradék edge-state gombfeliratokra

---

## 2026. 05. 07. – Session: Ikon láthatósági vészjavítás

### Mit csináltunk [x]
- [x] Dashboard ikonok méretének és kontrasztjának jelentős növelése
- [x] Bal oldali menü ikonokra vizuális badge háttér + erősebb glow
- [x] Sort nyilak teljes cseréje SVG-re
- [x] Gombikonok egységes méretre hozása (`.va-btn .va-ico`)
- [x] Inline ár-szerkesztő ikon nagyítása és vizuális erősítése
- [x] Képszám és trust badge ikonok méretnövelése
- [x] Kritikus CSS hiba javítása: lezáratlan komment helyreállítva

### Hol tartunk
Az ikonok most látványosan nagyobbak, erősebb kontrasztúak és következetesek a dashboard felületen.

---

## 2026. 05. 07. – Session: Ikon összeérés spacing fix

### Mit csináltunk [x]
- [x] Bal menü ikon+sor spacing javítva központi CSS-ben (rács, gap, min-height)
- [x] Nav ikon cella szélesítve, sorok ritmusának növelése
- [x] Ár szerkesztő ceruza gomb margó/távolság javítva a táblázatban
- [x] Root + plugin mirror fájlok szinkronban
- [x] Local deploy kész

### Hol tartunk
A dashboardon az ikonok és feliratok már nem érnek össze, a vizuális ritmus szellősebb.

---

