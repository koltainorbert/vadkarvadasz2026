<?php
/**
 * page-adatkezeles.php
 * Adatkezelesi tajekoztato (GDPR), Sutik es Fenntarthato Fejlodes Iranyelve.
 * Forras: weingartnertrans.hu/adatkezelesi-tajekoztato-gdpr/ – minden adat 1:1 azonos.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page va-gdpr-page" aria-labelledby="va-gdpr-title">
<style>
.va-gdpr-page {
    padding: calc(var(--nav) + 26px) 18px 56px;
    color: #fff;
}
.va-gdpr-shell {
    max-width: 1060px;
    margin: 0 auto;
    border: 1px solid rgba(255,0,0,.24);
    border-radius: 26px;
    background:
        radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0) 0 0 / 15px 15px,
        linear-gradient(180deg, rgba(12,12,12,.98), rgba(6,6,6,.99));
    box-shadow: 0 26px 70px rgba(0,0,0,.55);
    overflow: hidden;
}
.va-gdpr-hero {
    position: relative;
    padding: 34px 26px 24px;
    border-bottom: 1px solid rgba(255,255,255,.09);
    background:
        linear-gradient(120deg, rgba(255,0,0,.18), rgba(255,0,0,.03) 42%, transparent 80%),
        linear-gradient(180deg, rgba(16,16,16,.95), rgba(10,10,10,.92));
}
.va-gdpr-kicker {
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
.va-gdpr-hero h1 {
    margin: 0;
    font-size: clamp(1.7rem, 4vw, 2.6rem);
    line-height: 1.12;
    letter-spacing: .01em;
    color: #fff;
}
.va-gdpr-hero p {
    margin-top: 12px;
    max-width: 800px;
    color: rgba(255,255,255,.82);
    line-height: 1.72;
    font-size: 1rem;
}
.va-gdpr-company {
    margin: 22px 22px 0;
    border: 1px solid rgba(255,0,0,.3);
    border-radius: 14px;
    padding: 16px 20px;
    background: rgba(255,0,0,.09);
    display: flex;
    flex-wrap: wrap;
    gap: 8px 32px;
    align-items: flex-start;
}
.va-gdpr-company-name {
    font-size: 1.08rem;
    font-weight: 700;
    color: #fff;
    width: 100%;
    margin-bottom: 4px;
}
.va-gdpr-company span { font-size: 0.92rem; color: rgba(255,255,255,.82); }
.va-gdpr-company a { color: rgba(255,80,80,.9); text-decoration: none; }
.va-gdpr-company a:hover { text-decoration: underline; }
.va-gdpr-accordion {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.va-gdpr-panel {
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px;
    overflow: hidden;
    background: rgba(255,255,255,.02);
}
.va-gdpr-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 16px 20px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    color: #fff;
    font-size: 1.04rem;
    font-weight: 600;
    letter-spacing: .02em;
    transition: background .18s;
    gap: 14px;
}
.va-gdpr-toggle:hover { background: rgba(255,0,0,.12); }
.va-gdpr-toggle-icon {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border: 1px solid rgba(255,0,0,.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff0000;
    font-size: 16px;
    line-height: 1;
    transition: transform .22s;
}
.va-gdpr-panel.is-open .va-gdpr-toggle-icon { transform: rotate(45deg); }
.va-gdpr-panel.is-open .va-gdpr-toggle { background: rgba(255,0,0,.1); border-bottom: 1px solid rgba(255,0,0,.2); }
.va-gdpr-body {
    display: none;
    padding: 20px 22px 8px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.va-gdpr-panel.is-open .va-gdpr-body { display: block; }
.va-gdpr-body p {
    color: rgba(255,255,255,.86);
    line-height: 1.78;
    font-size: 0.96rem;
    margin: 0 0 12px;
}
.va-gdpr-body p strong, .va-gdpr-body b { color: #fff; }
.va-gdpr-body br { line-height: 2.1; }
.va-gdpr-body table { border-collapse: collapse; width: 100%; font-size: 0.88rem; color: rgba(255,255,255,.85); margin-bottom: 14px; }
.va-gdpr-body th { text-align: left; padding: 8px 10px; color: #fff; border-bottom: 1px solid rgba(255,255,255,.2); }
.va-gdpr-body td { padding: 8px 10px; border-bottom: 1px solid rgba(255,255,255,.08); }
.va-gdpr-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0 20px;
    padding: 11px 22px;
    background: rgba(255,0,0,.18);
    border: 1px solid rgba(255,0,0,.45);
    border-radius: 10px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition: .18s ease;
}
.va-gdpr-pdf-btn:hover { background: rgba(255,0,0,.32); transform: translateY(-1px); color: #fff; text-decoration: none; }
@media (max-width: 640px) {
    .va-gdpr-hero { padding: 22px 16px 18px; }
    .va-gdpr-accordion { padding: 14px; }
    .va-gdpr-body { padding: 16px 14px 6px; }
    .va-gdpr-company { margin: 14px 14px 0; }
}
</style>

<div class="va-gdpr-shell">

    <div class="va-gdpr-hero">
        <div class="va-gdpr-kicker">&#128274; Adatvédelem &amp; GDPR</div>
        <h1 id="va-gdpr-title" style="color:#fff">ADATVÉDELMI SZABÁLYZATAINK</h1>
        <p>Biztonsági Adattár &mdash; Oldalunkon az Ön adatait biztonságban kezeljük. Az alábbi dokumentumokban tájékozódhat adatkezelési gyakorlatunkról, a sütikről és a fenntartható fejlődés irányelvéről.</p>
    </div>

    <div class="va-gdpr-company">
        <div class="va-gdpr-company-name">Weingartner György Egyéni Vállalkozó</div>
        <span>&#128205; 8412 Veszprém, Alsó-Újsor u. 31.</span>
        <span>&#128222; <a href="tel:+36209438636">+36 20 943 8636</a></span>
        <span>&#9993; <a href="mailto:weingartnerauto@gmail.com">weingartnerauto@gmail.com</a></span>
        <span>&#127760; <a href="https://weingartnerauto.hu" target="_blank" rel="noopener">weingartnerauto.hu</a></span>
    </div>

    <div class="va-gdpr-accordion">

        <!-- 1. GDPR panel -->
        <div class="va-gdpr-panel is-open" id="va-gdpr-panel-1">
            <button class="va-gdpr-toggle" aria-expanded="true" aria-controls="va-gdpr-body-1" onclick="vaGdprToggle(this)">
                <span>1 &ndash; GDPR Adatkezelési Tájékoztató</span>
                <span class="va-gdpr-toggle-icon">+</span>
            </button>
            <div class="va-gdpr-body" id="va-gdpr-body-1">

<p><strong>ADATKEZELÉSI TÁJÉKOZTATÓ</strong></p>

<p><strong>1. Jelen adatkezelési tájékoztató célja:</strong></p>
<p>Jelen adatkezelési tájékoztató célja, hogy az Érintettek megfelelő tájékoztatást kaphassanak Weingartner György egyéni vállalkozó (a továbbiakban: Adatkezelő) által a weingartnerauto.hu domain név alatt üzemeltetett weboldalon (a továbbiakban: Weboldal) keresztül megvalósuló-, valamint a weboldalon közölt tevékenységek végzésével és szolgáltatások nyújtásával kapcsolatos adatkezelésről, annak céljáról, jogalapjáról, időtartamáról, továbbá az adattovábbítás címzettjéről.</p>

<p><strong>2. Fogalommeghatározások:</strong></p>
<p>érintett: bármely információ alapján azonosított vagy azonosítható természetes személy,<br />
személyes adat: az érintettre vonatkozó bármely információ,<br />
hozzájárulás: az érintett akaratának önkéntes, határozott és megfelelő tájékoztatáson alapuló egyértelmű kinyilvánítása, amellyel az érintett nyilatkozat vagy az akaratát félreérthetetlenül kifejező más magatartás útján jelzi, hogy beleegyezését adja a rá vonatkozó személyes adatok kezeléséhez,<br />
adatkezelő: az a természetes vagy jogi személy, illetve jogi személyiséggel nem rendelkező szervezet, aki vagy amely &ndash; törvényben vagy az Európai Unió kötelező jogi aktusában meghatározott keretek között &ndash; önállóan vagy másokkal együtt az adat kezelésének célját meghatározza, az adatkezelésre (beleértve a felhasznált eszközt) vonatkozó döntéseket meghozza és végrehajtja, vagy az adatfeldolgozóval végrehajtatja,<br />
adatkezelés: az alkalmazott eljárástól függetlenül az adaton végzett bármely művelet vagy a műveletek összessége, így különösen gyűjtése, felvétele, rögzítése, rendszerezése, tárolása, megváltoztatása, felhasználása, lekérdezése, továbbítása, nyilvánosságra hozatala, összehangolása vagy összekapcsolása, zárolása, törlése és megsemmisítése, valamint az adat további felhasználásának megakadályozása, fénykép-, hang- vagy képfelvétel készítése, valamint a személy azonosítására alkalmas fizikai jellemzők (pl. ujj- vagy tenyérnyomat, DNS-minta, íriszkép) rögzítése,<br />
adattovábbítás: az adat meghatározott harmadik személy számára történő hozzáférhetővé tétele,<br />
harmadik személy: olyan természetes vagy jogi személy, illetve jogi személyiséggel nem rendelkező szervezet, aki vagy amely nem azonos az érintettel, az adatkezelővel, az adatfeldolgozóval vagy azokkal a személyekkel, akik az adatkezelő vagy adatfeldolgozó közvetlen irányítása alatt a személyes adatok kezelésére irányuló műveleteket végeznek,<br />
adatvédelmi incidens: az adatbiztonság olyan sérelme, amely a továbbított, tárolt vagy más módon kezelt személyes adatok véletlen vagy jogellenes megsemmisülését, elvesztését, módosulását, jogosulatlan továbbítását vagy nyilvánosságra hozatalát, vagy az azokhoz való jogosulatlan hozzáférést eredményezi,<br />
munkatárs: az Adatkezelővel megbízási-, munka- vagy egyéb jogviszonyban levő természetes személy, aki az Adatkezelő szolgáltatásainak ellátásnak, teljesítésének feladatával van bízva és adatkezelési vagy adatfeldolgozási feladatai során személyes adatokkal kapcsolatba kerül vagy kerülhet és akinek tevékenységével kapcsolatban az Adatkezelő teljes felelősséget vállal az érintettek személyi köre és harmadik személyek irányában,<br />
weboldal: a https://weingartnerauto.hu/ portál és minden aloldala, amelynek üzemeltetője az Adatkezelő;<br />
facebook oldal: a https://www.facebook.com/WeingartnerAuto portálon található oldal, amelynek gondozását az Adatkezelő végzi.<br />
partner: az Adatkezelő szolgáltatásait szerződés alapján igénybe vevő és/vagy az Adatkezelő szolgáltatásainak teljesítéseit elősegítő jogi személyek, jogi személyiséggel nem rendelkező gazdasági társaságok, amelyek felé az Adatkezelő &ndash; az érintett hozzájárulását követően &ndash; személyes adatot továbbít vagy továbbíthat, vagy amelyek az Adatkezelő számára adattárolási, feldolgozási, kapcsolódó informatikai és egyéb biztonságos adatkezelést elősegítő tevékenységet végeznek vagy végezhetnek;</p>

<p><strong>3. Irányadó jogszabályok:</strong></p>
<p>Az Adatkezelők a tevékenységüket az Európai Unió és a magyar jogszabályok hatálya alatt végzik. Az adatkezelésre elsősorban az Európai Unió általános adatvédelmi rendelete vonatkozik (Európai Parlament és a Tanács (EU) 2016/679 rendelete (2016. április 27.) a természetes személyeknek a személyes adatok kezelése tekintetében történő védelméről és az ilyen adatok szabad áramlásáról, valamint a 95/46/EK rendelet hatályon kívül helyezéséről; továbbiakban: „GDPR"). A GDPR szövege elérhető a http://eur-lex.europa.eu/legal-content/HU/TXT/?uri=CELEX:32016R0679 honlapon.<br />
Ezen túlmenően az irányadó jogszabályok:<br />
Az információs önrendelkezési jogról és az információszabadságról szóló 2011. évi CXII. törvény; a pénzmosás és a terrorizmus finanszírozása megelőzéséről és megakadályozásáról szóló 2017. évi LIII. törvény (a törvény által előírt azonosítási, bejelentési, nyilvántartási adatokat illetően, továbbiakban: „Pmt."); a számvitelről szóló 2000. évi C. törvény 169. § (a bizonylatok megőrzését illetően), a gazdasági reklámtevékenység alapvető feltételeiről és egyes korlátairól szóló 2008. évi XLVIII. törvény.<br />
A vonatkozó jogszabályok elérhetők a http://net.jogtar.hu/ címen.</p>

<p><strong>4. A tájékoztató hatálya:</strong></p>
<p>Jelen tájékoztató kizárólag a GDPR hatálya alá tartozó adatkezelésekre terjed ki.<br />
A GDPR alapján személyes adat az azonosított vagy azonosítható természetes személyre (továbbiakban: „Érintett") vonatkozó bármely információ; azonosítható az a természetes személy, aki közvetlen vagy közvetett módon, különösen valamely azonosító (például név, szám, helymeghatározó adat, online azonosító) vagy a természetes személy testi, fiziológiai, genetikai, szellemi, gazdasági, kulturális vagy szociális azonosságára vonatkozó egy vagy több tényező alapján azonosítható.<br />
Mindezek alapján jelen Tájékoztató hatálya nem terjed ki azon adatokra, amelyek nem természetes személyekre vonatkoznak (pl. cégadatok), vagy amelyek nem hozhatók kapcsolatba természetes személyekkel (pl. statisztikai adatok, azon adatok, amelyek anonimizáltak). Jelen Tájékoztató hatálya kizárólag az Adatkezelők adatkezelésére terjed ki.</p>
<p>A GDPR. 8. cikk (1) bekezdés alapján a közvetlenül gyermekeknek kínált, információs társadalommal összefüggő szolgáltatások vonatkozásában végzett személyes adatok kezelése akkor jogszerű, ha a gyermek a 16. életévet betöltötte. Ennél fiatalabb gyermek személyes adatainak kezelése csak akkor jogszerű, ha annak kezeléséhez a gyermek feletti szülői felügyeleti jogot gyakorló adta meg.</p>

<p><strong>5. Az adatkezelés elvei:</strong></p>
<p>Az Adatkezelő az adatokat jogszerűen, tisztességesen, és az Érintett számára átlátható módon kezeli. Az Adatkezelő törekszik arra, hogy az általa kezelt adatok pontosak és naprakészek legyenek. Az Adatkezelő biztosítja az Érintett jogainak érvényesülését, és megtesz minden szükséges intézkedéseket annak érdekében, hogy az adatkezelés annak minden szakaszában jogszerű legyen. Az Adatkezelő adatokat csak meghatározott, egyértelmű és jogszerű célból gyűjti és azokat nem kezeli a célokkal össze nem egyeztethető módon, a gyűjtött és kezelt személyes adatok az adatkezelés céljai szempontjából megfelelőek és relevánsak, valamint csak a legszükségesebbre korlátozódnak. Az Adatkezelő a személyes adatokat olyan formában tárolja, hogy az Érintett csak a személyes adatok kezelése céljainak eléréséhez szükséges ideig legyen azonosítható, valamint megfelelő technikai és szervezési intézkedések alkalmazásával biztosítja a személyes adatok biztonságát az adatok jogosulatlan vagy jogellenes kezelésével, véletlen elvesztésével, megsemmisítésével vagy károsodásával szemben.</p>

<p><strong>6. Adatkezelő adatai, elérhetőségei:</strong></p>
<p>Név: Weingartner György egyéni vállalkozó<br />
Székhely: 8412 Veszprém, Alsóújsor 31.<br />
Nyilvántartási szám: 5640360<br />
Statisztikai számjel: 53988124494123119<br />
Adószám: 53988124-2-39<br />
Telefonszám: +36 20 9438636<br />
E-mail: weingartnerauto@gmail.com<br />
Weboldal: https://weingartnerauto.hu<br />
További Adatkezelő személye: Pfujdné Weingartner Adrienn<br />
Adatkezelő beosztása: adminisztrátor<br />
Adatkezelő elérhetősége: weingartnerauto@gmail.com<br />
Személyes adatait a jelen tájékoztatóban megjelölt Adatkezelő kezeli, azonban vannak olyan technikai műveletek, amelyekhez külső segítséget, adatfeldolgozó közreműködését veszi igénybe, aki a nevében és utasításainak megfelelően az adatok feldolgozását végzi. Amennyiben a személyes adatok a konkrét adatkezelések egyedi adatkezelési tájékoztatójában megjelölt harmadik személy/ek részére továbbításra kerülnek, úgy azokat önálló adatkezelőként a továbbiakban ők is kezelik.</p>

<p><strong>7. Tárhelyszolgáltató:</strong></p>
<p>Név: Nethely Kft.<br />
Székhely: 1115 Budapest, Halmi utca 29.<br />
Cégjegyzékszám: 01-09-961790<br />
Adószám: 23358005-2-43<br />
Telefonszám: +36 1 445 2040<br />
E-mail: info@nethely.hu<br />
Weboldal: https://www.nethely.hu<br />
Bankszámlaszám: 11711003-20007085</p>

<p><strong>8. Adattovábbítás, adatfeldolgozók megnevezése:</strong></p>
<p>Adatfeldolgozó: az a természetes vagy jogi személy, közhatalmi szerv, ügynökség vagy bármely egyéb szerv, amely az adatkezelő nevében személyes adatokat kezel; (Rendelet 4. cikk 8.) Az adatfeldolgozó igénybevételéhez nem kell az érintett előzetes beleegyezése, de szükséges a tájékoztatása. Ennek megfelelően a következő tájékoztatást adjuk:</p>
<p>IT szolgáltatók:<br />
CÉGNÉV: Google LLC<br />
SZÉKHELY: 1600 Amphitheatre Pkwy, Mountain View, California 94043 (az EU-U.S. PRIVACY SHIELD FRAMEWORK alapján EU-ban honosnak tekinthető)<br />
HONLAP: https://mail.google.com/<br />
CÉGNÉV: Facebook, Inc.<br />
SZÉKHELY: Menlo Park, California, USA (az EU-U.S. PRIVACY SHIELD FRAMEWORK alapján EU-ban honosnak tekinthető)<br />
HONLAP: https://www.facebook.com/<br />
Könyvelési, bérszámfejtési feladatokat ellátó adatfeldolgozó:<br />
CÉGNÉV: Könyvelő Iroda Veszprém Korlátolt Felelősségű Társaság<br />
SZÉKHELY: 8200 Veszprém, Simon István utca 1/A. 1/6.<br />
Számlázással kapcsolatos adatfeldolgozó:<br />
CÉGNÉV: KBOSS.hu Kft.<br />
SZÉKHELY: 1031 Budapest, Záhony utca 7.<br />
HONLAP: https://szamlazz.hu<br />
E-MAIL: info@szamlazz.hu<br />
Hulladékkezelési szolgáltatással kapcsolatos adatfeldolgozók:<br />
CÉGNÉV: VHK Nonprofit Kft. &mdash; SZÉKHELY: 8200 Veszprém, Kistó utca 8. &mdash; HONLAP: https://www.vhkn.hu/Szolgaltatasok/Hulladekgyujto-udvar &mdash; E-MAIL: ugyfelszolgalat@vkszrt.hu<br />
CÉGNÉV: Méh Zrt &mdash; SZÉKHELY: 8200 Veszprém, Kisréti utca 1. &mdash; HONLAP: https://www.mehzrt.hu/ &mdash; E-MAIL: info@mehzrt.hu<br />
CÉGNÉV: Sebestyén és Társa Kft. &mdash; SZÉKHELY: 8200 Veszprém, Ipar utca 1950/15 hrsz. &mdash; HONLAP: https://www.sebestyenestarsa.hu/ &mdash; E-MAIL: veszprem@sebifuvar.hu<br />
CÉGNÉV: Kötés Építőanyagipari és Szolgáltató Kft &mdash; SZÉKHELY: 8200 Veszprém, Tószeg utca 30. &mdash; HONLAP: http://www.koteskft.hu/<br />
CÉGNÉV: Partner-Depónia Hulladékhasznosító Kft &mdash; SZÉKHELY: 8100 Várpalota &mdash; HONLAP: https://www.deponia.hu<br />
CÉGNÉV: Kemenesalja KATHA Törmelék Hasznosító Kft &mdash; SZÉKHELY: 9531 Mersevát, Dózsa György utca 18. &mdash; HONLAP: https://www.deneskft.hu/katha/<br />
Műszaki vizsgáztatással kapcsolatos adatfeldolgozók:<br />
CÉGNÉV: Magyar Autóklub &mdash; SZÉKHELY: 8200 Veszprém, Aradi Vértanúk útja 1. &mdash; HONLAP: https://www.autoklub.hu/muszaki-szolgaltatas/szerviz/ &mdash; E-MAIL: veszprem@autoklub.hu<br />
CÉGNÉV: Honda Autóház Veszprém &mdash; SZÉKHELY: 8200 Veszprém, Budapest út 72. &mdash; HONLAP: https://sommer.honda.hu/ &mdash; E-MAIL: mail@hondasommer.hu<br />
CÉGNÉV: Veszprém Megyei Közlekedési Felügyelet &mdash; SZÉKHELY: 8200 Veszprém, Kistó utca 1. &mdash; HONLAP: https://www.kormanyhivatal.hu/hu/veszprem/szervezeti-egysegek/kozlekedesi-es-muszaki-engedelyezesi-foosztaly &mdash; E-MAIL: kmef@veszprem.gov.hu</p>

<p><strong>9. Egyedi adatkezelési tájékoztató:</strong></p>
<p><strong>Számlázással kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: A jogszabályoknak megfelelő számla kiállítása és a számviteli bizonylat-megőrzési kötelezettség teljesítése. Az Számv.tv. 169. § (1)&ndash;(2) bekezdése alapján a gazdasági társaságoknak a könyvviteli elszámolást közvetlenül és közvetetten alátámasztó számviteli bizonylatot meg kell őrizniük.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, szállítási címét, a megrendeléssel kapcsolatos információkat, számlázási- és fizetési adatait, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: A kiállított számlákat az Számv.tv. 169. § (2) bekezdése alapján a számla kiállításától számított 8 évig meg kell őrizni. Tájékoztatjuk arról, hogy amennyiben a számla kiállításához adott hozzájárulását visszavonja, az Adatkezelő az Infotv. 6. § (5) bekezdés a) pontja alapján jogosult a számla kiállítása során megismert személyes adatait 8 évig megőrizni.<br />
Az adatkezelés jogalapja: Az adatkezelés az adatkezelőre vonatkozó jogi kötelezettség teljesítéséhez szükséges (GDPR. 6. cikk (1) bekezdés c) pont).</p>

<p><strong>Árufuvarozáshoz kapcsolódó adatkezelés:</strong><br />
Az adatkezelés célja: Az áruszállítás esetében az adatkezelés célja az, hogy a megrendelt árut az Ön igényéhez alkalmazkodva kiszállítsuk.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, szállítási címét, a megrendeléssel kapcsolatos információkat, számlázási- és fizetési adatait, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: Az Adatkezelő az adatokat a megrendelt áru kiszállításának időtartamáig kezeli.<br />
Az adatkezelés jogalapja: Az adatkezelés olyan szerződés teljesítéséhez szükséges, amelyben az Érintett az egyik fél (GDPR. 6. cikk (1) bekezdés b) pont).</p>

<p><strong>Gépjármű javításhoz kapcsolódó adatkezelés:</strong><br />
Az adatkezelés célja: A gépjárműjavítás esetében az adatkezelés célja az, hogy a szolgáltatás nyújtáshoz kapcsolódó kapcsolattartást biztosítsuk.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, a megrendeléssel kapcsolatos információkat és a megbízáshoz kapcsolódó gépjármű adatait kezeli.<br />
Az adatkezelés időtartama: Az Adatkezelő az adatokat a szolgáltatás nyújtásának időtartamáig kezeli.<br />
Az adatkezelés jogalapja: Az adatkezelés olyan szerződés teljesítéséhez szükséges, amelyben az Érintett az egyik fél (GDPR. 6. cikk (1) bekezdés b) pont).</p>

<p><strong>Műszaki vizsgáztatáshoz kapcsolódó adatkezelés:</strong><br />
Az adatkezelés célja: A műszaki vizsgáztatás esetében az adatkezelés célja az, hogy a gépjárművet az Ön képviseletében eljárva a hatósági vizsgálatra regisztráljuk.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, személyigazolvány számát, lakcímkártya számát, születési helyét, születési idejét, édesanyja nevét, a megrendeléssel kapcsolatos információkat, különös tekintettel a gépjármű forgalmi engedélyben szereplő adatait, az Ön számlázási- és fizetési adatait, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: Az Adatkezelő az adatokat a szolgáltatás teljesítéséig őrzi.<br />
Az adatkezelés jogalapja: Az adatkezelés olyan szerződés teljesítéséhez szükséges, amelyben az Érintett az egyik fél (GDPR. 6. cikk (1) bekezdés b) pont).</p>

<p><strong>Árajánlat készítéssel kapcsolatos adatkezelés:</strong><br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, szállítási címét, a megrendeléssel kapcsolatos adatokat, egyedi üzenetét, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: Az árajánlat kiküldését követő 30 napig.<br />
Az adatkezelés jogalapja: Az érintett hozzájárulása (GDPR. 6. cikk (1) bekezdés a) pont).</p>

<p><strong>Hulladékszállításhoz kapcsolódó adatkezelés:</strong><br />
Az adatkezelés célja: A hulladékszállítás során az Ön képviseletében végezzük a szolgáltatást, melyhez kapcsolódóan a hulladékudvar személyes adatait igényli.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát valamint a hulladékkal kapcsolatos információkat kezeli.<br />
Az adatkezelés időtartama: Az Adatkezelő az adatokat a szolgáltatás teljesítéséig őrzi.<br />
Az adatkezelés jogalapja: Az adatkezelés olyan szerződés teljesítéséhez szükséges, amelyben az Érintett az egyik fél (GDPR. 6. cikk (1) bekezdés b) pont).</p>

<p><strong>A kapcsolatfelvétellel kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: Az Érintett által kezdeményezett kapcsolatfelvétel kezelése, válaszadás a megkeresésre.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, telefonszámát, üzenetét és e-mail címét kezeli.<br />
Az adatkezelés időtartama: A kapcsolatfelvétel lezárultáig.<br />
Az adatkezelés jogalapja: Az érintett hozzájárulása (GDPR. 6. cikk (1) bekezdés a) pont).</p>

<p><strong>Weboldal használatával kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: A Weboldal rendeltetésszerű működésének biztosítása, látogatottság mérésére, statisztikai célok.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön IP címét, a látogatás időpontját, a meglátogatott aloldalak adatait, az operációs rendszer és böngésző típusát kezeli.<br />
Az adatkezelés időtartama: Maximum a látogatástól számított 2 év.<br />
Az adatkezelés jogalapja: Az adatkezelés az adatkezelő jogos érdekeinek érvényesítéséhez szükséges (GDPR. 6. cikk (1) bekezdés f) pont).</p>

<p><strong>Panaszkezeléssel kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: Panaszokkal kapcsolatos adatok kezelése, panaszok kivizsgálása, jogorvoslása.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, egyéb üzenetét, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: A panaszt követő 3 év.<br />
Az adatkezelés jogalapja: Az adatkezelés az adatkezelőre vonatkozó jogi kötelezettség teljesítéséhez szükséges (GDPR. 6. cikk (1) bekezdés c) pont).</p>

<p><strong>Álláspályázatok kezelésével kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: A meghirdetett álláshelyre beérkező önéletrajzoknak és kapcsolódó dokumentumoknak a nyilvántartásba vétele és kiértékelése a kiválasztási eljárás során, továbbá a jelöltekkel való kapcsolatlétesítés, valamint az elutasító döntéssel érintett vagy az álláshirdetés hiányában jelentkezők személyes adatait tartalmazó dokumentumoknak munkaerő-piaci adatbankba történő felvétele erre irányuló kérelem esetén.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, képmását, iskolai végzettségeit, szakmai tapasztalatokat, nyelvismeretét, érdeklődési körét, korábbi munkahelyeit, önéletrajzban szereplő egyéb személyes adatait, az Ön aláírását, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: Elutasító döntésig, de maximum 6 hónapig.<br />
Az adatkezelés jogalapja: Az érintett hozzájárulása (GDPR. 6. cikk (1) bekezdés a) pont).</p>

<p><strong>Munkavégzésre irányuló egyéb jogviszony létesítésével kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: Az Önnel történő munkavégzésre irányuló egyéb jogviszony létesítése.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét (titulus/családinév/keresztnév), születési helyét, születési idejét, anyja nevét, életkorát, azonosító okmányainak típusát/számát, a munkavégzésre irányuló egyéb jogviszony jellegét, a díjazás összegét, a díj fizetésének esedékességére vonatkozó adatot, az ellátandó tevékenység megnevezését és teljesítésének időpontját, a természetes személy számlavezető pénzintézetének megnevezését és a számlaszámát, az adóazonosító jelét, a társadalombiztosítási azonosító jelét (TAJ szám), valamint az aláírását, mint személyes adatokat kezeli.<br />
Az adatkezelés időtartama: Szerződés teljesítésének napját követő 8 év.<br />
Az adatkezelés jogalapja: Az adatkezelés az adatkezelőre vonatkozó jogi kötelezettség teljesítéséhez szükséges (GDPR. 6. cikk (1) bekezdés c) pont).</p>

<p><strong>Elektronikus vagyonvédelmi megfigyelőrendszer alkalmazásával kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: A személyes adatok kezelésének célja a székhelyen lévő vagyontárgyak és az ott tartózkodó személyek vagyontárgyainak védelme.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő a képmását és az abból levonható következtetéseket, mint személyes adatokat kezeli.<br />
Az adatkezelés időtartama: Az Adatkezelő az érintettekről készült felvételeket a személy- és vagyonvédelmi, valamint a magánnyomozói tevékenység szabályairól szóló 2005. évi CXXXIII. törvény 31. § (2) bekezdés alapján három munkanapig tárolja.<br />
Az adatkezelés jogalapja: Az adatkezelés az adatkezelő vagy egy harmadik fél jogos érdekeinek érvényesítéséhez szükséges. (GDPR. 6. cikk (1) bekezdés f) pont).</p>

<p><strong>Követeléskezeléssel kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: Az esetleges követelés érvényesítése.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, számlázási- és fizetési adatait, követelés jogosságát igazoló dokumentumokat valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: 5 év.<br />
Az adatkezelés jogalapja: Az érintett hozzájárulása (GDPR. 6. cikk (1) bekezdés a) pont).<br />
Adattovábbítás: amennyiben szükséges, az Adatkezelő jogosult a követelés behajtásához szükséges adatokat behajtással foglalkozó vállalkozás, vagy ügyvéd részére továbbítani.</p>

<p><strong>A weboldalon rögzített szolgáltatások igénybevételével kapcsolatos adatkezelés:</strong><br />
Az adatkezelés célja: Szolgáltatás nyújtásának biztosításához szükséges adatok kezelése.<br />
A kezelt adatok köre: Az adatkezelés során az Adatkezelő az Ön nevét, lakcímét, telefonszámát, címét, a szolgáltatással kapcsolatos információkat, számlázási- és fizetési adatait, valamint e-mail címét kezeli.<br />
Az adatkezelés időtartama: Az Adatkezelő az adatokat a szerződés teljesítését követő 8 évig kezeli.<br />
Az adatkezelés jogalapja: Az adatkezelés olyan szerződés teljesítéséhez szükséges, amelyben az Érintett az egyik fél (GDPR. 6. cikk (1) bekezdés b) pont). Az érintett hozzájárulása (GDPR. 6. cikk (1) bekezdés a) pont).</p>

<p><strong>10. Felhasználók jogai:</strong></p>
<p>Az érintett jogosult arra, hogy személyes adatai vonatkozásában az adatkezeléssel összefüggő tényekről az adatkezelés megkezdését megelőzően tájékoztatást kapjon, kérelmére személyes adatait és az azok kezelésével összefüggő információkat az Adatkezelő a rendelkezésére bocsássa, kérelmére személyes adatait az Adatkezelő helyesbítse, illetve kiegészítse, adatai kezelését az Adatkezelő korlátozza, törölje, kérelmére a rá vonatkozó, általa az Adatkezelő rendelkezésére bocsátott személyes adatokat géppel olvasható formában megkapja, valamint ezeket az adatokat egy másik adatkezelőnek továbbítsa, bármikor tiltakozzon személyes adatainak az Adatkezelő jogos érdekén alapuló kezelése ellen. Ebben az esetben az Adatkezelő a személyes adatokat nem kezelheti tovább, kivéve, ha az Adatkezelő bizonyítja, hogy az adatkezelést olyan kényszerítő erejű jogos okok indokolják, amelyek elsőbbséget élveznek az Érintett érdekeivel, jogaival és szabadságaival szemben, vagy amelyek jogi igények előterjesztéséhez, érvényesítéséhez vagy védelméhez kapcsolódnak.</p>

<p><strong>11. Jogérvényesítés:</strong></p>
<p>Az Adatkezelő az érintett jogainak érvényesülését az alábbiak szerint biztosítja: A tájékozódáshoz való jog biztosítása érdekében jelen Adatkezelési tájékoztatót az Adatkezelő a Weboldalon az Érintettek számára hozzáférhetővé teszi, ha az általa kezelt személyes adatok pontatlanok, helytelenek vagy hiányosak, azokat &ndash; különösen az Érintett kérelmére &ndash; haladéktalanul pontosítja, helyesbíti vagy kiegészíti.<br />
Az Adatkezelő korlátozza az adatkezelést:<br />
&ndash; ha az Érintett vitatja az Adatkezelő által kezelt személyes adatok pontosságát, helytállóságát vagy hiánytalanságát, és a kezelt személyes adatok pontossága, helytállósága vagy hiánytalansága kétséget kizáróan nem állapítható meg, a fennálló kétség tisztázásának időtartamára,<br />
&ndash; ha az adatok törlésének lenne helye, de az Érintett írásbeli nyilatkozata vagy az Adatkezelő rendelkezésére álló információk alapján megalapozottan feltételezhető, hogy az adatok törlése sértené az érintett jogos érdekeit, a törlés mellőzését megalapozó jogos érdek fennállásának időtartamára,<br />
&ndash; ha az adatok törlésének lenne helye, de az Adatkezelő vagy más közfeladatot ellátó szerv által vagy részvételével végzett, jogszabályban meghatározott vizsgálatok vagy eljárások &ndash; így különösen büntetőeljárás &ndash; során az adatok bizonyítékként való megőrzése szükséges, ezen vizsgálat vagy eljárás végleges, illetve jogerős lezárásáig.<br />
Az Adatkezelő haladéktalanul törli az Érintett személyes adatait, ha az adatkezelés jogellenes, az Érintett az adatkezeléshez adott hozzájárulását visszavonja vagy személyes adatainak törlését kérelmezi, az adatok törlését jogszabály, az Európai Unió jogi aktusa, a NAIH vagy a bíróság elrendelte.</p>

<p><strong>12. Jogorvoslati lehetőségek:</strong></p>
<p>Kérjük, ha az adatai kezelésével kapcsolatban észrevétele vagy kifogása van, lépjen kapcsolatba az Adatkezelővel a jelen tájékoztatóban megadott weingartnerauto@gmail.com e-mail címen. Ha megítélése szerint az Adatkezelő a személyes adatait a személyes adatok kezelésére vonatkozó jogszabályban vagy az Európai Unió kötelező jogi aktusában meghatározott előírások megsértésével kezeli, joga van a területileg illetékes Törvényszékhez fordulni. Jogainak érvényesítése érdekében panasszal fordulhat a Nemzeti Adatvédelmi és Információszabadság Hatósághoz is:<br />
Nemzeti Adatvédelmi és Információszabadság Hatóság (Cím: 1055 Budapest, Falk Miksa utca 9&ndash;11., Postacím: 1363 Budapest, Pf.: 9., Telefon: (+36-1) 391-1400, E-mail: ugyfelszolgalat@naih.hu)</p>

<p><strong>13. Adattovábbítás:</strong></p>
<p>Az Adatkezelő az Érintett személyes adatait harmadik személy részére csak a feltétlenül szükséges mértékben, kizárólag a jelen tájékoztatóban megjelölt személynek, továbbá egyéb szerződéses kapcsolatban lévő vagy jogszabályi előíráson alapuló félnek továbbítja (pl. jogi képviselő, hatóságok). Az adattovábbítás során az Adatkezelő köteles meggyőződni arról, hogy a harmadik személy az adatokat kizárólag a meghatározott célra és szükséges mértékben használja, a cél megvalósulását követően pedig az adatokat törölje. Személyes adatok kezelése: amennyiben az Érintett nem a saját, hanem más személy személyes adatait adja meg, úgy az Adatkezelő vélelmezi, hogy az Érintett az ehhez szükséges felhatalmazással rendelkezik.</p>

<p><strong>14. Az adatkezelés biztonsága:</strong></p>
<p>Az elektronikusan kezelt adatok védelme érdekében az Adatkezelő a technika mindenkori állása szerint megfelelő szintű biztonságot nyújtó megoldást használ. Az Adatkezelő köteles a számítástechnikai eszközt és az ahhoz alkalmazott jelszavakat, adathordozókat úgy kezelni, tárolni, hogy a védelmet igénylő adatokat illetéktelen személy ne ismerhesse meg. A célhoz nem kötött és olyan adatokat, amelyekre nézve az adatkezelés célja megszűnt haladéktalanul, illetve az előírt megőrzési határidő leteltével meg kell semmisíteni. A számítógépen rögzített adatokat, ha céljukat betöltötték, további felhasználásuk megakadályozása érdekében véglegesen törölni kell. Az Adatkezelő a vonatkozó jogszabályok alkalmazásával védi az érintettek magánszféráját és biztosítja a személyes adatok bizalmasságát, sértetlenségét és rendelkezésre állását. Az Adatkezelő az általa kezelt adatokat elektronikus formában megőrzi székhelyén.</p>

<p><strong>15. Adatvédelmi incidens:</strong></p>
<p>Az Adatkezelő az általa kezelt adatokkal összefüggésben felmerült adatvédelmi incidenst haladéktalanul, de legfeljebb az adatvédelmi incidensről való tudomásszerzését követő 72 órán belül bejelenti a Nemzeti Adatvédelmi és Információszabadság Hatóságnak. Az adatvédelmi incidenst nem kell bejelenteni, ha valószínűsíthető, hogy az nem jár kockázattal az Érintettek jogainak érvényesülésére. Ha az adatvédelmi incidens valószínűsíthetően magas kockázattal jár a természetes személyek jogaira és szabadságaira nézve, az Adatkezelő indokolatlan késedelem nélkül tájékoztatja az Érintettet az adatvédelmi incidensről.<br />
Az Érintettet nem kell tájékoztatni, ha a következő feltételek bármelyike teljesül:<br />
&ndash; az Adatkezelő megfelelő technikai és szervezési védelmi intézkedéseket hajtott végre, és ezeket az intézkedéseket az adatvédelmi incidens által érintett adatok tekintetében alkalmazták, különösen azokat az intézkedéseket &ndash; mint például a titkosítás alkalmazása &ndash;, amelyek a személyes adatokhoz való hozzáférésre fel nem jogosított személyek számára értelmezhetetlenné teszik az adatokat,<br />
&ndash; az Adatkezelő az adatvédelmi incidenst követően olyan további intézkedéseket tett, amelyek biztosítják, hogy az Érintett jogaira és szabadságaira jelentett, a magas kockázat a továbbiakban valószínűsíthetően nem valósul meg,<br />
&ndash; a tájékoztatás aránytalan erőfeszítést tenne szükségessé. Ilyen esetekben az Érintetteket nyilvánosan közzétett információk útján kell tájékoztatni, vagy olyan hasonló intézkedést kell hozni, amely biztosítja az Érintettek hasonlóan hatékony tájékoztatását.</p>

<p><strong>16. Egyéb rendelkezések:</strong></p>
<p>Az Érintett az adatkezeléshez adott hozzájárulását bármikor visszavonhatja a jelen tájékoztatóban az Adatkezelő adatainál megjelölt e-mail címre küldött üzenet útján. Az Adatkezelő jogi kötelezettségének teljesítése vagy jogos érdekei érvényesítése céljából bizonyos adatokat a hozzájárulás visszavonása után is kezelhet.<br />
Megtévesztő személyes adat használata esetén, illetve ha az Érintett bűncselekményt követ el, az Érintett adatai törlésre kerülnek, illetve szükség esetén a polgári jogi felelősség megállapításának vagy büntető eljárás lefolytatásának időtartamára megőrzésre kerülnek. Az Érintett kérésére az Adatkezelő köteles tájékoztatást adni az általa kezelt adatairól, az adatkezelés jogalapjáról, céljáról, időtartamáról. Az Adatkezelő köteles a kérelem benyújtásától számított legrövidebb idő alatt, legkésőbb azonban egy 15 napon belül írásban megadni a tájékoztatást. Szükség esetén, figyelembe véve a kérelem összetettségét és a kérelmek számát, ez a határidő további 30 nappal meghosszabbítható. A határidő meghosszabbításáról az adatkezelő a késedelem okainak megjelölésével a kérelem kézhezvételétől számított egy hónapon belül tájékoztatja az Érintettet. Ha az Érintett elektronikus úton nyújtotta be a kérelmet, a tájékoztatást lehetőség szerint elektronikus úton kell megadni, kivéve, ha az Érintett azt másként kéri. A természetes személy számára a tájékoztatást tömör, átlátható, érthető és könnyen hozzáférhető formában, világosan és közérthetően megfogalmazva kell megadni. Ha az adatkezelő nem tesz intézkedéseket az Érintett kérelme nyomán, késedelem nélkül, de legkésőbb a kérelem beérkezésétől számított egy hónapon belül tájékoztatja az Érintettet az intézkedés elmaradásának okairól, valamint arról, hogy az Érintett panaszt nyújthat be valamely felügyeleti hatóságnál, és élhet bírósági jogorvoslati jogával.<br />
Az Adatkezelő a tájékoztatást ingyenesen biztosítja, kivéve ha az Érintett ismételten, lényegében változatlan tartalomra kér tájékoztatást vagy intézkedést, vagy a kérelem egyértelműen megalapozatlan és túlzó.</p>
<p>Az Adatkezelő fenntartja magának a jogot jelen Adatkezelési tájékoztató módosítására. A hatályos Adatkezelési tájékoztató a weingartnerauto.hu weboldalon mindig elérhető az Érintettek számára.</p>
<p><strong>2024. Január 17.</strong></p>
<p><strong>Adatkezelési tájékoztató tartalomjegyzék:</strong><br />
1. fejezet &ndash; Jelen adatkezelési tájékoztató célja<br />
2. fejezet &ndash; Fogalommeghatározások<br />
3. fejezet &ndash; Irányadó jogszabályok<br />
4. fejezet &ndash; A tájékoztató hatálya<br />
5. fejezet &ndash; Az adatkezelés elvei<br />
6. fejezet &ndash; Adatkezelő adatai, elérhetősége<br />
7. fejezet &ndash; Tárhelyszolgáltató<br />
8. fejezet &ndash; Adattovábbítás, adatfeldolgozók megnevezése<br />
9. fejezet &ndash; Egyedi adatkezelési tájékoztató<br />
10. fejezet &ndash; Felhasználók jogai<br />
11. fejezet &ndash; Jogérvényesítés<br />
12. fejezet &ndash; Jogorvoslati lehetőségek<br />
13. fejezet &ndash; Adattovábbítás<br />
14. fejezet &ndash; Adatkezelés biztonsága<br />
15. fejezet &ndash; Adatvédelmi incidens<br />
16. fejezet &ndash; Egyebek</p>

                <a class="va-gdpr-pdf-btn" href="https://www.weingartnertrans.hu/wp-content/uploads/2024/01/GDPR-Weingartner-Trans-vegleges.pdf" target="_blank" rel="noopener noreferrer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    Adatkezelési tájékoztató letöltés (PDF)
                </a>

            </div>
        </div>

        <!-- 2. Sütik panel -->
        <div class="va-gdpr-panel" id="va-gdpr-panel-2">
            <button class="va-gdpr-toggle" aria-expanded="false" aria-controls="va-gdpr-body-2" onclick="vaGdprToggle(this)">
                <span>2 &ndash; Sütik (Cookie) Tájékoztató</span>
                <span class="va-gdpr-toggle-icon">+</span>
            </button>
            <div class="va-gdpr-body" id="va-gdpr-body-2">

<p><strong>Süti (Cookie) tájékoztató</strong></p>
<p>Az Adatkezelő a weboldal működése során sütiket (cookie-kat) használ. A süti egy rövidebb szöveg típusú fájl, mely a számítógép vagy a mobil eszköz merevlemezén tárolódik és a későbbi látogatásokkor aktiválódik. A sütik megkönnyítik és biztosítják a weblap használatát, elmentenek bizonyos felhasználói beállításokat és közreműködnek abban, hogy néhány releváns, statisztikai jellegű információ kerüljön gyűjtésre a weblap látogatóival kapcsolatban. A sütik egy része nem tartalmaz személyes információkat és nem alkalmas egyéni felhasználó azonosítására, egy részük azonban olyan egyéni azonosítót is tartalmaz, melyeket az érintett eszköze tárol, ezzel a felhasználó azonosíthatóságát biztosítja. A böngészőprogramok többsége alapértelmezés mellett elfogadja a sütiket, de az Érintett olyan beállítást is alkalmazhat, amely elutasítja a sütik fogadását, illetve jelzi azok érkezését. A sütiket böngészője segítségével törölheti. A személyes információkat nem tartalmazó, a weblap működéséhez feltétlenül szükséges sütik használatához nem szükséges hozzájárulni. A statisztikai (adatgyűjtési) sütik csak az erről szóló értesítés Érintett általi elfogadását követően töltődnek be. A sütik elfogadása nem kötelező, de az adatkezelő azonban nem vállal azért felelősséget, ha a sütik engedélyezése hiányában a weboldal nem megfelelően működik.</p>

<p>Oldalunkon használt sütik számos hasznos célt szolgálnak, például:<br />
A sütik lehetővé teszik számunkra annak megállapítását, hogy Szolgáltatásaink mely részei a legnépszerűbbek, ugyanis a segítségükkel láthatjuk, hogy a látogatók mely oldalakhoz és funkciókhoz férnek hozzá, és mennyi időt töltenek az egyes oldalakon. Az ilyen adatok tanulmányozásával könnyebben tudjuk az igényekhez igazítani a Szolgáltatásokat, így jobb élményt nyújthatunk Önnek.<br />
A sütiknek köszönhetően releváns tartalmakat és hirdetéseket tudunk biztosítani az Ön számára, mivel a sütik adatokat gyűjtenek arról, hogy Ön hogyan használja Szolgáltatásainkat, valamint az egyéb webhelyeket és alkalmazásokat.</p>

<p><strong>Harmadik fél sütik:</strong> webhelyünkön harmadik felek webhelyeinek vagy szolgáltatásainak tartalmai is megjelenhetnek, és ily módon harmadik felek sütijei kerülhetnek az Ön merevlemezére vagy a böngészőjébe. Ezeket a harmadik feleket Ön a Sütikezelő Eszközeiben tekintheti meg, ahol ezeket a sütiket le is tilthatja. Az ilyen harmadik féltől származó sütik tárolását és használatát nem ellenőrizzük. További információkért tekintse meg az adott harmadik fél adatvédelmi tájékoztatóit. Ez a típusú süti automatikusan engedélyezett, és az Ön saját igényeinek megfelelően beállítható.</p>

<p><strong>Google AdWords konverziókövetés használata:</strong><br />
A „Google AdWords" nevű online reklámprogramot használja az adatkezelő, továbbá annak keretein belül igénybe veszi a Google konverziókövető szolgáltatását. A Google konverziókövetés a Google Inc. elemző szolgáltatása (1600 Amphitheatre Parkway, Mountain View, CA 94043, USA; „Google").<br />
Amikor Felhasználó egy weboldalt Google-hirdetés által ér el, akkor egy a konverziókövetéshez szükséges cookie kerül a számítógépére. Ezeknek a cookie-knak az érvényessége korlátozott, és nem tartalmaznak semmilyen személyes adatot, így a Felhasználó nem is azonosítható általuk.<br />
Amikor a Felhasználó a weboldal bizonyos oldalait böngészi, és a cookie még nem járt le, akkor a Google és az adatkezelő is láthatja, hogy Felhasználó a hirdetésre kattintott.<br />
Minden Google AdWords ügyfél másik cookie-t kap, így azokat az AdWords ügyfeleinek weboldalain keresztül nem lehet nyomon követni.<br />
Az információk – melyeket a konverziókövető cookie-k segítségével szereztek – azt a célt szolgálják, hogy az AdWords konverziókövetést választó ügyfeleinek számára konverziós statisztikákat készítsenek. Az ügyfelek így tájékozódnak a hirdetésükre kattintó és konverziókövető címkével ellátott oldalra továbbított felhasználók számáról. Azonban olyan információkhoz nem jutnak hozzá, melyekkel bármelyik felhasználót azonosítani lehetne.<br />
Ha nem szeretne részt venni a konverziókövetésben, akkor ezt elutasíthatja azáltal, hogy böngészőjében letiltja a cookie-k telepítésének lehetőségét. Ezután Ön nem fog szerepelni a konverziókövetési statisztikákban.<br />
További információ valamint a Google adatvédelmi nyilatkozata az alábbi oldalon érhető el: www.google.de/policies/privacy/</p>

<p><strong>A Google Analytics használata:</strong><br />
Ez a honlap a Google Analytics alkalmazást használja, amely a Google Inc. („Google") webelemző szolgáltatása. A Google Analytics úgynevezett „cookie-kat", szövegfájlokat használ, amelyeket a számítógépére mentenek, így elősegítik Felhasználó által látogatott weblap használatának elemzését.<br />
A Felhasználó által használt weboldallal kapcsolatos cookie-kkal létrehozott információk rendszerint a Google egyik USA-beli szerverére kerülnek és tárolódnak. Az IP-anonimizálás weboldali aktiválásával a Google a Felhasználó IP-címét az Európai Unió tagállamain belül vagy az Európai Gazdasági Térségről szóló megállapodásban részes más államokban előzőleg megrövidíti.<br />
A teljes IP-címnek a Google USA-ban lévő szerverére történő továbbítására és ottani lerövidítésére csak kivételes esetekben kerül sor. Eme weboldal üzemeltetőjének megbízásából a Google ezeket az információkat arra fogja használni, hogy kiértékelje, hogyan használta a Felhasználó a honlapot, továbbá, hogy a weboldal üzemeltetőjének a honlap aktivitásával összefüggő jelentéseket készítsen, valamint, hogy a weboldal- és az internethasználattal kapcsolatos további szolgáltatásokat teljesítsen.<br />
A Google Analytics keretein belül a Felhasználó böngészője által továbbított IP-címet nem vezeti össze a Google más adataival. A cookie-k tárolását a Felhasználó a böngészőjének megfelelő beállításával megakadályozhatja, azonban felhívjuk figyelmét, hogy ebben az esetben előfordulhat, hogy ennek a honlapnak nem minden funkciója lesz teljes körűen használható. Megakadályozhatja továbbá, hogy a Google gyűjtse és feldolgozza a cookie-k általi, a Felhasználó weboldalhasználattal kapcsolatos adatait (beleértve az IP-címet is), ha letölti és telepíti a következő linken elérhető böngésző plugint: https://tools.google.com/dlpage/gaoptout?hl=hu</p>

<p><strong>Közösségi oldalak:</strong><br />
Az adatgyűjtés ténye, a kezelt adatok köre: Facebook/Google+/Twitter/Pinterest/Youtube/Instagram stb. közösségi oldalakon regisztrált neve, illetve a felhasználó nyilvános profilképe.<br />
Az érintettek köre: Valamennyi érintett, aki regisztrált a Facebook/Google+/Twitter/Pinterest/Youtube/Instagram stb. közösségi oldalakon, és „lájkolta" a weboldalt.<br />
Az adatgyűjtés célja: A közösségi oldalakon, a weboldal egyes tartalmi elemeinek, termékeinek, akcióinak vagy magának a weboldalnak a megosztása, illetve „lájkolása", népszerűsítése.<br />
Az adatkezelés időtartama, az adatok törlésének határideje, az adatok megismerésére jogosult lehetséges adatkezelők személye és az érintettek adatkezeléssel kapcsolatos jogainak ismertetése: Az adatok forrásáról, azok kezeléséről, illetve az átadás módjáról, és jogalapjáról az adott közösségi oldalon tájékozódhat az érintett. Az adatkezelés a közösségi oldalakon valósul meg, így az adatkezelés időtartamára, módjára, illetve az adatok törlési és módosítási lehetőségeire az adott közösségi oldal szabályozása vonatkozik.<br />
Az adatkezelés jogalapja: az érintett önkéntes hozzájárulása személyes adatai kezeléséhez a közösségi oldalakon.</p>

<p><strong>A weboldal által használt Cookie-k pontos megnevezése:</strong></p>
<table>
<thead><tr>
<th>Cookie neve</th><th>Funkciója</th><th>Időtartama</th><th>Adatkezelés célja / Jogalap</th>
</tr></thead>
<tbody>
<tr><td>_ga</td><td>Google Analytics azonosító cookie</td><td>14 hónap</td><td>Forgalmi adatok rögzítése / Jogos érdek</td></tr>
<tr><td>_gid</td><td>Google Analytics azonosító cookie</td><td>24 óra</td><td>Forgalmi adatok rögzítése / Jogos érdek</td></tr>
<tr><td>cookieyes-consent</td><td>A cookie kezelésről szóló tájékoztatás elfogadásának állapota</td><td>1 év</td><td>A cookie kezelés elfogadásának rögzítése / Jogos érdek</td></tr>
</tbody>
</table>

<p><strong>2024. január 12.</strong></p>

                <a class="va-gdpr-pdf-btn" href="https://www.weingartnertrans.hu/wp-content/uploads/2024/01/Suti.pdf" target="_blank" rel="noopener noreferrer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    Cookie tájékoztató letöltése (PDF)
                </a>

            </div>
        </div>

        <!-- 3. Fenntartható Fejlődés panel -->
        <div class="va-gdpr-panel" id="va-gdpr-panel-3">
            <button class="va-gdpr-toggle" aria-expanded="false" aria-controls="va-gdpr-body-3" onclick="vaGdprToggle(this)">
                <span>3 &ndash; Fenntartható Fejlődés Irányelve</span>
                <span class="va-gdpr-toggle-icon">+</span>
            </button>
            <div class="va-gdpr-body" id="va-gdpr-body-3">

<p><strong>WEINGARTNER GYÖRGY EGYÉNI VÁLLALKOZÓ CÉGÉNEK FENNTARTHATÓ FEJLŐDÉS IRÁNYELVE</strong></p>

<p>„A fenntartható fejlődés olyan fejlődési folyamat, amely kielégíti a jelen igényeit anélkül, hogy csökkentené a jövendő generáció képességét, hogy kielégítsék a saját igényeiket. Az egyik tényező, amit le kellene küzdeni, a környezet elhasználódása, de ezt úgy kell véghezvinni, hogy közben ne mondjunk le sem a gazdasági fejlődés, sem a társadalmi egyenlőség és igazságosság igényeiről."</p>

<p><strong>Társaságunk fenntarthatósági eszméi</strong><br />
A fenntarthatóság eszméje egyre inkább áthatja életünket. Egy felelős vállalat ma már nem létezhet fenntarthatósági stratégia nélkül és nem engedheti meg magának, hogy működése során ne vegye figyelembe a fenntarthatósági szempontokat:</p>

<p><strong>1.) Környezet, azaz „odafigyelés" (környezeti felelősség):</strong><br />
&ndash; Környezettudatosság, környezet-etikus magatartás, környezetünk védelme, beleértve a klímaváltozással és a tudatos fogyasztással kapcsolatos szempontokat;<br />
&ndash; Biológiai sokszínűség fenntartása, azaz az állat- és növényvilág védelme;<br />
&ndash; Természeti erőforrásokkal való fenntartható gazdálkodás, takarékos használat, megújuló energiaforrások használata.</p>

<p><strong>2.) Társadalmi dimenzió, azaz „elfogadás" (társadalmi felelősség):</strong><br />
&ndash; Fenntartható társadalom: egészségügyi és élelmezési, valamint a túlnépesedésből és az öregedő társadalmakból fakadó társadalmi problémák kezelése;<br />
&ndash; Szociális igazságosság, esélyegyenlőség, társadalmi különbségek;<br />
&ndash; Társadalmi sokszínűség, szolidaritás, békés egymás mellett élés.</p>

<p><strong>3.) Gazdasági dimenzió, azaz a „felelős vállalat" megközelítés:</strong><br />
&ndash; Tisztességes, átlátható „vállalati" működés, „fair trade";<br />
&ndash; Környezeti terhelés csökkentése pl. CO&#8322; kibocsátás minimalizálása és papírmentes iroda révén;<br />
&ndash; Emberközpontúság mind a kollégák, mind az ügyfelek, vevők tekintetében;<br />
&ndash; Társadalmi szerepvállalás fontossága.</p>

<p><strong>A fenntarthatóság alapelvei:</strong></p>
<p>1. Amit a környezetbe bocsátunk, az nem haladhatja meg a környezet befogadó/feldolgozó képességét.</p>
<p>2. Amit a környezetből kitermelünk, az nem haladhatja meg a környezet újratermelő képességét.</p>
<p>3. A nem-megújuló erőforrások felhasználásának mértéke nem haladhatja meg azt az ütemet, amilyen arányban helyettesíteni tudjuk őket megújuló erőforrásokkal.</p>

<p>Tervezett és rendszeres intézkedések, amelyek szükségesek ahhoz, hogy a cégünk tevékenysége, szolgáltatása a megadott környezetvédelmi követelményeket kielégítse, és ezzel bizalmat keltsen a cégen belül és kívül.<br />
Cégünk egyéni vállalkozása elkötelezett a környezetvédelem iránt. Tevékenységünket igyekszünk úgy végezni, hogy minél kisebb mértékben terheljük a környezetet.<br />
A munkatársaink környezettudatos szemléletének fejlesztése érdekében évente munka-, tűz- és környezetvédelmi oktatásban részesítjük.<br />
A gépjárművek megfelelő műszaki állapotát az ütemezett karbantartással biztosítjuk.<br />
A gépjárművek CO kibocsátását folyamatosan ellenőriztetjük.<br />
Az irodai tevékenység során a papír felhasználást a minimumra csökkentettük. Az adatok, információk elektronikus rögzítése történik.<br />
A műanyag alapanyagú termékek felhasználását minimalizáljuk, így pl. lefűző dossziék papír alapúak stb.<br />
A nyári melegben a vásárolt ásványvíz visszaváltható palackban kerülnek beszerzésre.</p>

<p>Veszprém, 2024. február 07.</p>
<p><strong>Weingartner György</strong><br />Egyéni Vállalkozó</p>

                <a class="va-gdpr-pdf-btn" href="https://www.weingartnertrans.hu/wp-content/uploads/2024/02/Fenntarthato-fejlodes-iranyelv_Weingartner-Gyorgy-E.V.pdf" target="_blank" rel="noopener noreferrer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    Fenntartható Fejlődés Irányelve letöltése (PDF)
                </a>

            </div>
        </div>

    </div>

</div>
</section>

<script>
function vaGdprToggle(btn) {
    var panel = btn.closest('.va-gdpr-panel');
    var isOpen = panel.classList.contains('is-open');
    panel.classList.toggle('is-open', !isOpen);
    btn.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
}
</script>

<?php get_footer(); ?>
