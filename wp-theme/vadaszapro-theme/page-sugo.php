<?php
/**
 * page-sugo.php
 * Teljes, publikus hasznalati utmutato ugyfeleknek.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page" aria-labelledby="va-help-title">
    <style>
        .va-help-page {
            padding: calc(var(--nav) + 26px) 18px 56px;
            color: #fff;
        }
        .va-help-shell {
            max-width: 1160px;
            margin: 0 auto;
            border: 1px solid rgba(255, 0, 0, .24);
            border-radius: 26px;
            background:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0) 0 0 / 15px 15px,
                linear-gradient(180deg, rgba(12,12,12,.98), rgba(6,6,6,.99));
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
            overflow: hidden;
        }
        .va-help-hero {
            position: relative;
            padding: 34px 26px 24px;
            border-bottom: 1px solid rgba(255,255,255,.09);
            background:
                linear-gradient(120deg, rgba(255,0,0,.18), rgba(255,0,0,.03) 42%, transparent 80%),
                linear-gradient(180deg, rgba(16,16,16,.95), rgba(10,10,10,.92));
        }
        .va-help-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            border: 1px solid rgba(255,0,0,.42);
            border-radius: 999px;
            padding: 8px 14px;
            color: #fff;
            background: rgba(255,0,0,.16);
            margin-bottom: 14px;
        }
        .va-help-hero h1 {
            margin: 0;
            font-size: clamp(1.7rem, 4vw, 2.8rem);
            line-height: 1.12;
            letter-spacing: .01em;
            text-wrap: balance;
            color: #fff;
        }
        .va-help-lead {
            margin-top: 12px;
            max-width: 980px;
            color: rgba(255,255,255,.86);
            line-height: 1.75;
            font-size: 1.01rem;
        }
        .va-help-quick {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        .va-help-quick a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 13px;
            background: rgba(255,255,255,.05);
            color: #fff;
            text-decoration: none;
            transition: .18s ease;
        }
        .va-help-quick a:hover {
            border-color: rgba(255,0,0,.5);
            background: rgba(255,0,0,.2);
            transform: translateY(-1px);
        }
        .va-help-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 18px 18px 0;
        }
        .va-help-section {
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.015));
            padding: 18px;
        }
        .va-help-section h2 {
            margin: 0 0 10px;
            font-size: 1.04rem;
            color: #fff;
            line-height: 1.35;
        }
        .va-help-section p,
        .va-help-section li {
            color: rgba(255,255,255,.88);
            line-height: 1.68;
            margin: 0;
        }
        .va-help-section ul,
        .va-help-section ol {
            margin: 0;
            padding-left: 20px;
        }
        .va-help-section li + li {
            margin-top: 6px;
        }
        .va-help-callout {
            margin: 18px;
            border: 1px solid rgba(255,0,0,.34);
            border-radius: 14px;
            padding: 14px 16px;
            background: rgba(255,0,0,.12);
            color: #fff;
            line-height: 1.68;
        }
        .va-help-link-row {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 12px;
        }
        .va-help-link-row a {
            display: inline-block;
            color: #fff;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255,255,255,.06);
            text-decoration: none;
            transition: .18s ease;
        }
        .va-help-link-row a:hover {
            border-color: rgba(255,0,0,.45);
            background: rgba(255,0,0,.18);
        }
        .va-help-divider {
            margin: 18px;
            border: 0;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .va-help-mini {
            margin: 0 18px 18px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .va-help-mini-box {
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            padding: 12px;
            background: rgba(255,255,255,.02);
            font-size: 13px;
            color: rgba(255,255,255,.84);
            line-height: 1.6;
        }
        .va-help-mini-box strong {
            display: block;
            color: #fff;
            margin-bottom: 3px;
        }
        @media (max-width: 900px) {
            .va-help-grid {
                grid-template-columns: 1fr;
            }
            .va-help-mini {
                grid-template-columns: 1fr;
            }
            .va-help-hero {
                padding: 24px 16px 18px;
            }
        }
    </style>

    <div class="va-help-shell">
        <header class="va-help-hero">
            <div class="va-help-kicker">Felhasználói útmutató</div>
            <h1 id="va-help-title">Teljes használati útasítás a weingartnerauto.hu oldalához</h1>
            <p class="va-help-lead">
                Ezen az oldalon összegyűjtöttük a látogatók számára látható funkciókat: hogyan keress, hogyan
                böngéssz, és hogyan kérj kapcsolatfelvételt. Csak olyan információ szerepel itt,
                amit a felhasználók is láthatnak az oldalon.
            </p>
            <div class="va-help-quick">
                <a href="<?php echo esc_url( home_url( '/va-hirdetes-kereses' ) ); ?>">Hirdetés keresése</a>
                <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolat</a>
            </div>
        </header>

        <div class="va-help-grid">
            <article class="va-help-section">
                <h2>1. Első lépések új látogatóknak</h2>
                <ol>
                    <li>Nyisd meg a Hirdetés keresése oldalt, ahol az összes találatot listában vagy rács nézetben látod.</li>
                    <li>Ez egy magán oldal: fiók- és feltöltési funkciók nem érhetőek el látogatóknak.</li>
                    <li>Ha kérdésed van, a Kapcsolat oldalon tudsz írni.</li>
                </ol>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolatfelvétel</a>
                </div>
            </article>

            <article class="va-help-section">
                <h2>2. Keresés és szűrés lépésről lépésre</h2>
                <ul>
                    <li>Kulcsszóra, kategóriára, megyére és állapotra tudsz szűrni.</li>
                    <li>Árszűrővel minimum–maximum tartományt állíthatsz.</li>
                    <li>Rendezés: legújabb, ár növekvő, ár csökkenő, legtöbb megtekintés.</li>
                    <li>Átválthatsz rács és lista nézet között, a neked kényelmesebb böngészéshez.</li>
                    <li>A Részletes kereső panelben több jármű-specifikus szűrő is elérhető (pl. márka, modell, évjárat, üzemanyag).</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>3. Mit látsz egy hirdetés adatlapján?</h2>
                <ul>
                    <li>Képek, cím, ár, helyszín, kategória és egyéb adatok.</li>
                    <li>Leírás blokk, ahol a hirdető részletes információt adhatott meg.</li>
                    <li>Megtekintés szám és a hirdetés állapotára vonatkozó jelzések.</li>
                    <li>A tartalom publikus olvasásra van optimalizálva.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>4. Fiók funkciók</h2>
                <ul>
                    <li>A nyilvános látogatók számára a fiókba lépés ki van kapcsolva.</li>
                    <li>Új fiók létrehozása és fiókkezelés jelenleg nem érhető el.</li>
                    <li>Az oldal tartalma fiók használata nélkül böngészhető.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>5. Tartalom feltöltés</h2>
                <ul>
                    <li>Ez a funkció látogatóknak nem érhető el.</li>
                    <li>A weboldal jelenleg olvasási módban üzemel.</li>
                    <li>A publikus felületen csak a már feltöltött hirdetések böngészése történik.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>6. Fiókom: mit lehet kezelni?</h2>
                <ul>
                    <li>A fiókfunkciók a publikus nézetben ki vannak kapcsolva.</li>
                    <li>A látogatók csak olvashatják és böngészhetik a hirdetéseket.</li>
                    <li>További információért használd a Kapcsolat oldalt.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>7. Biztonságos használat röviden</h2>
                <ul>
                    <li>Csak valós, ellenőrizhető adatot adj meg hirdetésben és kapcsolatfelvételkor.</li>
                    <li>Személyes találkozót lehetőség szerint nyilvános helyen szervezz.</li>
                    <li>Gyanús ajánlatnál vagy átverés-gyanú esetén használd a Kapcsolat oldalt.</li>
                </ul>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolatfelvétel</a>
                </div>
            </article>
        </div>

        <hr class="va-help-divider">

        <div class="va-help-mini">
            <div class="va-help-mini-box">
                <strong>Ha nem találsz menüpontot</strong>
                Bizonyos funkciók időszakosan vagy beállítás szerint ki lehetnek kapcsolva.
            </div>
            <div class="va-help-mini-box">
                <strong>Hibabejelentés</strong>
                A pontos oldal URL-jével és rövid leírással gyorsabban tudunk segíteni.
            </div>
            <div class="va-help-mini-box">
                <strong>Jogi oldalak</strong>
                Használat előtt érdemes elolvasni az ÁSZF-et és az adatvédelmi tájékoztatót.
            </div>
        </div>

        <div class="va-help-callout">
            Nem találod, amit keresel? Írj a Kapcsolat oldalon. Minél pontosabban írod le,
            hogy melyik oldalon és milyen lépés után akadtál el, annál gyorsabban tudunk segíteni.
            <div class="va-help-link-row">
                <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Üzenet küldése</a>
                <a href="<?php echo esc_url( home_url( '/etika' ) ); ?>">ÁSZF</a>
                <a href="<?php echo esc_url( home_url( '/adatkezeles' ) ); ?>">Adatvédelmi tájékoztató</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
