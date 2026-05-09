<?php
/**
 * page-impresszum.php
 * Impresszum oldal – /impresszum/ URL-en tölti be a téma.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page va-impresszum-page" aria-labelledby="va-impresszum-title">
<style>
.va-impresszum-page {
    padding: calc(var(--nav) + 26px) 18px 56px;
    color: #fff;
}
.va-impresszum-shell {
    max-width: 780px;
    margin: 0 auto;
    border: 1px solid rgba(255,0,0,.24);
    border-radius: 26px;
    background:
        radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0) 0 0 / 15px 15px,
        linear-gradient(180deg, rgba(12,12,12,.98), rgba(6,6,6,.99));
    box-shadow: 0 26px 70px rgba(0,0,0,.55);
    overflow: hidden;
}
.va-impresszum-hero {
    position: relative;
    padding: 34px 30px 24px;
    border-bottom: 1px solid rgba(255,255,255,.09);
    background:
        linear-gradient(120deg, rgba(255,0,0,.18), rgba(255,0,0,.03) 42%, transparent 80%),
        linear-gradient(180deg, rgba(16,16,16,.95), rgba(10,10,10,.92));
}
.va-impresszum-kicker {
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
.va-impresszum-hero h1 {
    margin: 0;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    line-height: 1.1;
    letter-spacing: .01em;
    color: #fff;
}
.va-impresszum-body {
    padding: 28px 30px 32px;
}
.va-impresszum-block {
    margin-bottom: 28px;
}
.va-impresszum-block:last-of-type {
    margin-bottom: 0;
}
.va-impresszum-section-title {
    font-size: 0.78rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #ff0000;
    font-weight: 700;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,0,0,.25);
}
.va-impresszum-row {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 0;
    margin-bottom: 6px;
    font-size: 0.97rem;
    line-height: 1.65;
    color: rgba(255,255,255,.9);
}
.va-impresszum-row .label {
    min-width: 200px;
    color: rgba(255,255,255,.58);
    font-size: 0.92rem;
}
.va-impresszum-row a {
    color: rgba(255,100,100,.9);
    text-decoration: none;
}
.va-impresszum-row a:hover { text-decoration: underline; }
.va-impresszum-divider {
    border: none;
    border-top: 1px solid rgba(255,255,255,.07);
    margin: 22px 0;
}
.va-impresszum-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 24px;
    padding: 11px 22px;
    background: rgba(255,0,0,.18);
    border: 1px solid rgba(255,0,0,.45);
    border-radius: 10px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition: .18s ease;
    cursor: pointer;
    font-family: inherit;
}
.va-impresszum-pdf-btn:hover {
    background: rgba(255,0,0,.32);
    transform: translateY(-1px);
    color: #fff;
}
@media print {
    .va-header, .va-footer, #va-scroll-ring, .va-impresszum-pdf-btn,
    .va-impresszum-kicker, .va-footer__bottom, .va-sidebar { display: none !important; }
    .va-impresszum-page { padding: 0 !important; }
    .va-impresszum-shell {
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
        border-radius: 0 !important;
        max-width: 100% !important;
    }
    .va-impresszum-hero {
        background: #fff !important;
        border-bottom: 1px solid #ddd !important;
        padding: 20px 0 14px !important;
    }
    .va-impresszum-hero h1 { color: #111 !important; font-size: 22pt !important; }
    .va-impresszum-section-title { color: #111 !important; border-color: #ccc !important; }
    .va-impresszum-row { color: #222 !important; }
    .va-impresszum-row .label { color: #555 !important; }
    .va-impresszum-body { padding: 20px 0 !important; }
    body { background: #fff !important; }
    * { -webkit-print-color-adjust: exact; }
}
@media (max-width: 600px) {
    .va-impresszum-hero { padding: 22px 18px 18px; }
    .va-impresszum-body { padding: 20px 18px 24px; }
    .va-impresszum-row .label { min-width: 100%; font-weight: 600; }
}
</style>

<div class="va-impresszum-shell">

    <div class="va-impresszum-hero">
        <div class="va-impresszum-kicker">&#128196; Jogi információk</div>
        <h1 id="va-impresszum-title" style="color:#fff">IMPRESSZUM</h1>
    </div>

    <div class="va-impresszum-body">

        <div class="va-impresszum-block">
            <div class="va-impresszum-section-title">Weboldal üzemeltetője</div>
            <div class="va-impresszum-row"><span class="label">Név:</span><span>Weingartner György egyéni vállalkozó</span></div>
            <div class="va-impresszum-row"><span class="label">Székhely:</span><span>8412 Veszprém, Alsóújsor 31.</span></div>
            <div class="va-impresszum-row"><span class="label">Nyilvántartási szám:</span><span>5640360</span></div>
            <div class="va-impresszum-row"><span class="label">Statisztikai számjel:</span><span>53988124494123119</span></div>
            <div class="va-impresszum-row"><span class="label">Adószám:</span><span>53988124-2-39</span></div>
            <div class="va-impresszum-row"><span class="label">Telefonszám:</span><span><a href="tel:+36209438636">+36 20 9438636</a></span></div>
            <div class="va-impresszum-row"><span class="label">E-mail:</span><span><a href="mailto:weingartnerauto@gmail.com">weingartnerauto@gmail.com</a></span></div>
            <div class="va-impresszum-row"><span class="label">Weboldal:</span><span><a href="https://www.weingartnerauto.hu" target="_blank" rel="noopener">https://www.weingartnerauto.hu</a></span></div>
        </div>

        <hr class="va-impresszum-divider">

        <div class="va-impresszum-block">
            <div class="va-impresszum-section-title">Tárhelyszolgáltató</div>
            <div class="va-impresszum-row"><span class="label">Név:</span><span>Nethely Kft.</span></div>
            <div class="va-impresszum-row"><span class="label">Székhely:</span><span>1115 Budapest, Halmi utca 29.</span></div>
            <div class="va-impresszum-row"><span class="label">Telefonszám:</span><span><a href="tel:+3614452040">+36 1 445 2040</a></span></div>
            <div class="va-impresszum-row"><span class="label">E-mail:</span><span><a href="mailto:info@nethely.hu">info@nethely.hu</a></span></div>
            <div class="va-impresszum-row"><span class="label">Weboldal:</span><span><a href="https://www.nethely.hu" target="_blank" rel="noopener">https://www.nethely.hu</a></span></div>
        </div>

        <button class="va-impresszum-pdf-btn" onclick="window.print()" type="button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            Impresszum letöltése (PDF)
        </button>

    </div>
</div>
</section>

<?php get_footer(); ?>
