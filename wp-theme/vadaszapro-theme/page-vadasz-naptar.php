<?php
/**
 * page-vadasz-naptar.php – Vadászati idények + Holdnaptár
 * Automatikusan betöltődik a slug = vadasz-naptar oldalra.
 */

if ( get_option( 'va_enable_hunting_calendar_page', '1' ) !== '1' ) {
  get_header();
  ?>
  <main style="max-width:960px;margin:40px auto;padding:24px 18px;color:#fff;background:rgba(6,6,6,.92);border:1px solid rgba(255,255,255,.1);border-radius:12px;">
    <h1 style="margin:0 0 10px;">Vadászati naptár letiltva</h1>
    <p style="margin:0;color:rgba(255,255,255,.78);">Ez az oldal jelenleg admin beállításból ki van kapcsolva.</p>
  </main>
  <?php
  get_footer();
  return;
}

get_header();
?>

<style>
/* ══ VN prefix – vadásznaptár oldal ══════════════════════════════════ */
.vn-wrap{--vn-name-w:200px;max-width:1280px;margin:0 auto;padding:24px 16px 60px;position:relative;isolation:isolate;}
body.page-template-page-vadasz-naptar,
body.page.page-id,
body{
  background: rgb(6,6,6);
}
.vn-wrap::before{
  content:'';
  position:fixed;
  inset:0;
  z-index:-2;
  pointer-events:none;
  background:
    radial-gradient(circle at 12% 8%, rgba(255,0,0,.14), transparent 38%),
    radial-gradient(circle at 88% 16%, rgba(255,0,0,.10), transparent 34%),
    radial-gradient(circle at 50% 92%, rgba(255,120,0,.08), transparent 42%),
    radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px);
  background-size:auto,auto,auto,16px 16px;
  background-color:rgb(6,6,6);
}
.vn-wrap::after{
  content:'';
  position:fixed;
  inset:0;
  z-index:-1;
  pointer-events:none;
  background:linear-gradient(180deg, rgba(6,6,6,.35) 0%, rgba(6,6,6,.86) 100%);
}

/* ── LEGEND ── */
.vn-legend{display:flex;flex-wrap:wrap;justify-content:center;gap:8px 18px;margin-bottom:20px;}
.vn-leg{display:flex;align-items:center;gap:6px;font-size:.75rem;color:rgba(255,255,255,.7)}
.vn-leg-dot{width:16px;height:10px;border-radius:3px;flex-shrink:0}

/* ── HOLDNAPTÁR ── */
.vn-moon{
  background:linear-gradient(180deg,rgba(20,10,10,.95) 0%,rgba(6,6,6,.98) 100%);
  border:1px solid rgba(255,0,0,.18);border-radius:10px;
  margin:0 0 24px;padding:20px;
  box-shadow:0 0 40px rgba(0,0,0,.6),inset 0 0 60px rgba(255,0,0,.03);
}
.vn-moon,
.vn-chart,
#vn-today-panel,
.vn-op-box,
.vn-cl-box{
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.vn-chart{
  background: linear-gradient(180deg, rgba(14,14,14,.92), rgba(7,7,7,.96));
  border:1px solid rgba(255,255,255,.12);
  box-shadow:0 24px 60px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.06);
}
.vn-moon{
  border-color:rgba(255,0,0,.28);
  box-shadow:0 26px 60px rgba(0,0,0,.62), inset 0 1px 0 rgba(255,255,255,.05), inset 0 0 80px rgba(255,0,0,.06);
}
.vn-moon-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.vn-moon-tools{display:flex;flex-wrap:wrap;gap:10px;align-items:end;justify-content:center;margin:0 0 16px;}
.vn-moon-field{display:flex;flex-direction:column;gap:5px;min-width:120px;}
.vn-moon-field span{font-size:.62rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.42);font-weight:700;}
.vn-moon-select{
  height:38px;padding:0 12px;border-radius:8px;
  border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);
  color:#fff;font-size:.84rem;
}
.vn-moon-jump{
  height:38px;padding:0 14px;border-radius:8px;cursor:pointer;
  border:1px solid rgba(255,0,0,.28);background:rgba(255,0,0,.1);color:#fff;
  font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
}
.vn-moon-jump:hover{background:rgba(255,0,0,.2);border-color:#ff0000;}
.vn-moon-range-note{text-align:center;font-size:.68rem;color:rgba(255,255,255,.42);margin:-2px 0 14px;}
.vn-moon-nav{
  background:rgba(255,0,0,.1);border:1px solid rgba(255,0,0,.35);
  color:#fff;border-radius:6px;width:36px;height:36px;
  font-size:1rem;cursor:pointer;transition:all .15s;flex-shrink:0;
}
.vn-moon-nav:hover{background:rgba(255,0,0,.25);border-color:#ff0000;}
.vn-moon-title{display:flex;align-items:center;gap:14px;flex:1;justify-content:center;}
.vn-moon-icon-big{font-size:2.8rem;line-height:1;filter:drop-shadow(0 0 8px rgba(255,220,100,.5));}
.vn-moon-cur-name{font-size:1.1rem;font-weight:700;color:#fff;letter-spacing:.06em;}
.vn-moon-cur-sub{font-size:.75rem;color:rgba(255,255,255,.5);margin-top:2px;}
.vn-moon-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:14px;}
.vn-moon-dow{text-align:center;font-size:.6rem;font-weight:700;color:rgba(255,255,255,.35);letter-spacing:.08em;padding:4px 0 6px;text-transform:uppercase;}
.vn-moon-day{
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
  border-radius:7px;padding:6px 3px 5px;text-align:center;cursor:default;
  transition:all .15s;position:relative;
}
.vn-moon-day:not(.vn-moon-empty):hover{background:rgba(255,0,0,.1);border-color:rgba(255,0,0,.4);transform:translateY(-1px);}
.vn-moon-day.vn-moon-today{border-color:#ff0000;background:rgba(255,0,0,.12);box-shadow:0 0 10px rgba(255,0,0,.3);}
.vn-moon-day.vn-moon-today .vn-moon-dnum{color:#ff4444;font-weight:700;}
.vn-moon-day.vn-moon-fullnew{border-color:rgba(255,200,50,.4);background:rgba(255,200,50,.07);}
.vn-moon-day.vn-moon-empty{background:none;border-color:transparent;}
.vn-moon-dicon{font-size:1.4rem;display:block;line-height:1;margin-bottom:2px;}
.vn-moon-dnum{font-size:.72rem;color:rgba(255,255,255,.7);line-height:1;}
.vn-moon-dpct{font-size:.55rem;color:rgba(255,255,255,.35);margin-top:1px;}
.vn-moon-legend{display:flex;flex-wrap:wrap;gap:6px 16px;font-size:.65rem;color:rgba(255,255,255,.4);margin-bottom:12px;}
.vn-moon-tip{background:rgba(255,200,50,.07);border:1px solid rgba(255,200,50,.2);border-radius:6px;padding:10px 14px;font-size:.78rem;color:rgba(255,255,255,.8);line-height:1.5;}

/* ── TODAY PANEL ── */
.vn-today{margin:0 0 24px;}
.vn-tp-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;}
.vn-tp-title{font-size:.95rem;font-weight:700;letter-spacing:.06em;color:#fff;}
.vn-tp-title span{color:#ff0000}
.vn-tp-live{font-size:.72rem;color:rgba(255,255,255,.35);font-variant-numeric:tabular-nums;}
.vn-tp-lbl{font-size:.62rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.28);margin:14px 0 7px;}
.vn-tp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:7px;}
.vn-tp-card{
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
  border-left-width:3px;border-radius:8px;padding:9px 12px 10px;
}
.vn-tp-card--new{
  background:rgba(0,255,80,.06);border-color:rgba(0,220,70,.35);
  box-shadow:0 0 16px rgba(0,200,60,.12),inset 0 0 20px rgba(0,200,60,.04);
  animation:vn-new-pulse 2s ease-in-out infinite;
}
@keyframes vn-new-pulse{
  0%,100%{box-shadow:0 0 16px rgba(0,200,60,.12),inset 0 0 20px rgba(0,200,60,.04);}
  50%{box-shadow:0 0 28px rgba(0,220,70,.3),inset 0 0 30px rgba(0,200,60,.09);}
}
.vn-tp-card--new .vn-tp-card-name{color:#4dff88;text-shadow:0 0 10px rgba(0,220,80,.5);}
.vn-tp-card--closing{background:rgba(255,30,30,.08)!important;border-color:rgba(255,60,60,.45)!important;animation:vn-cl-pulse 1.5s ease-in-out infinite;}
@keyframes vn-cl-pulse{0%,100%{box-shadow:0 0 10px rgba(255,0,0,.12);}50%{box-shadow:0 0 22px rgba(255,0,0,.36),inset 0 0 16px rgba(255,0,0,.08);}}
.vn-tp-card--closing .vn-tp-card-name{color:#ff9999;}
.vn-tp-card-name{font-size:.77rem;font-weight:700;color:#fff;line-height:1.3}
.vn-tp-card-sub{font-size:.62rem;color:rgba(255,255,255,.32);font-style:italic;margin-top:1px}
.vn-tp-card-range{font-size:.66rem;color:rgba(255,255,255,.45);margin-top:5px}
.vn-tp-badge{display:inline-block;margin-top:6px;font-size:.63rem;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.03em;}
.vn-b-ok{background:rgba(0,180,60,.15);color:#1ec854}
.vn-b-warn{background:rgba(255,165,0,.18);color:#f5a623}
.vn-b-urgent{background:rgba(255,50,50,.2);color:#ff6060}
.vn-b-soon{background:rgba(255,255,255,.06);color:rgba(255,255,255,.45)}
.vn-b-new{background:rgba(0,210,70,.18);color:#4dff88;border:1px solid rgba(0,210,70,.35);}
.vn-tp-empty{font-size:.78rem;color:rgba(255,255,255,.28);padding:12px 0}
.vn-tp-cd{font-size:.65rem;font-weight:700;color:#ff9900;margin-top:5px;letter-spacing:.04em;text-shadow:0 0 8px rgba(255,150,0,.5);}

/* ── GANTT CHART ── */
.vn-chart-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 8px;padding:0 4px;}
.vn-chart-hint{font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.42);opacity:0;transform:translateX(-6px);transition:all .25s ease;}
.vn-wrap.has-overflow .vn-chart-hint{opacity:1;transform:none;}
.vn-wrap.is-scrolled .vn-chart-hint{opacity:.2;}
.vn-chart-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:#171717 #060606;}
.vn-chart-scroll::-webkit-scrollbar{height:12px;}
.vn-chart-scroll::-webkit-scrollbar-track{background:#060606;}
.vn-chart-scroll::-webkit-scrollbar-thumb{background:#171717;border:1px solid #2a2a2a;border-radius:999px;}
.vn-chart-scroll::-webkit-scrollbar-thumb:hover{background:#232323;}
.vn-chart{
  max-width:1200px;margin:0 auto;
  min-width:980px;
  border:1px solid rgba(255,255,255,.08);border-radius:12px;overflow:hidden;
}
.vn-month-header{
  display:flex;position:sticky;top:80px;z-index:20;
  background:rgb(10,10,10);border-bottom:1px solid rgba(255,255,255,.1);
  box-shadow:0 2px 12px rgba(0,0,0,.7);
}
.vn-mh-name{width:var(--vn-name-w);min-width:var(--vn-name-w);flex-shrink:0;padding:8px 14px;font-size:.68rem;color:rgba(255,255,255,.4);letter-spacing:.08em;text-transform:uppercase;border-right:1px solid rgba(255,255,255,.08);}
.vn-mh-name,.vn-group-label,.vn-animal-name{position:sticky;left:0;z-index:6;}
.vn-mh-name{z-index:20;background:rgb(10,10,10);}
.vn-group-label{z-index:13;background:rgb(20,10,10);}
.vn-animal-name{z-index:12;background:rgb(10,10,10);}
.vn-mh-name::after,.vn-group-label::after,.vn-animal-name::after{content:'';position:absolute;top:0;right:-12px;width:12px;height:100%;pointer-events:none;background:linear-gradient(90deg,rgba(255,0,0,.18),rgba(255,0,0,0));}
.vn-mh-months{flex:1;display:flex;position:relative;}
.vn-mh-month{flex:1;text-align:center;font-size:.7rem;font-weight:700;color:rgba(255,255,255,.55);letter-spacing:.06em;padding:8px 0;border-left:1px solid rgba(255,255,255,.08);}
.vn-mh-month.current{color:#ff0000}

.vn-group-header{
  display:flex;align-items:center;background:rgba(255,0,0,.08);
  border-top:1px solid rgba(255,0,0,.18);border-bottom:1px solid rgba(255,0,0,.12);
  cursor:pointer;user-select:none;transition:background .15s;
}
.vn-group-header:hover{background:rgba(255,0,0,.15)}
.vn-group-label{
  width:var(--vn-name-w);min-width:var(--vn-name-w);flex-shrink:0;padding:9px 14px;
  font-size:.72rem;font-weight:700;color:#ff0000;letter-spacing:.1em;text-transform:uppercase;
  border-right:1px solid rgba(255,0,0,.18);display:flex;align-items:center;gap:7px;
}
.vn-arrow{font-size:.95rem;font-weight:900;line-height:1;transition:transform .2s;display:inline-block}
.vn-group-label.collapsed .vn-arrow{transform:rotate(-90deg)}
.vn-gl-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;background:#ff3030;box-shadow:0 0 5px rgba(255,48,48,.4);}
.vn-group-label.has-open{color:#00e060;}
.vn-group-label.has-open .vn-gl-dot{background:#00e060;box-shadow:0 0 7px #00e060;animation:vn-blink .9s ease-in-out infinite;}
.vn-gh-status{position:absolute;top:50%;right:14px;transform:translateY(-50%);font-size:.64rem;font-weight:700;letter-spacing:.03em;pointer-events:none;}
.vn-group-bar-area{flex:1;position:relative;height:34px}

.vn-animal-row{display:flex;align-items:center;border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;min-height:38px;}
.vn-animal-row:hover{background:rgba(255,255,255,.04)}
.vn-animal-name{width:var(--vn-name-w);min-width:var(--vn-name-w);flex-shrink:0;padding:6px 14px;font-size:.78rem;font-weight:600;color:#fff;border-right:1px solid rgba(255,255,255,.06);line-height:1.3;}
.vn-animal-name .sub{display:block;font-size:.64rem;font-weight:400;color:rgba(255,255,255,.35);margin-top:1px;font-style:italic;}
.vn-acd{display:flex;align-items:center;gap:4px;margin-top:4px;font-size:.6rem;font-weight:700;letter-spacing:.03em;line-height:1.2;}
.vn-acd-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
.vn-acd.on .vn-acd-dot{background:#00ff66;box-shadow:0 0 6px #00ff66;animation:vn-blink .9s ease-in-out infinite;}
.vn-acd.off .vn-acd-dot{background:#ff3030;}
.vn-acd.on .vn-acd-txt{color:#00ff66;}
.vn-acd.off .vn-acd-txt{color:#ff5050;}
.vn-acd-lbl{color:rgba(255,255,255,.3);font-weight:400;font-size:.58rem;}
@keyframes vn-blink{0%,100%{opacity:1}50%{opacity:.2}}
.vn-bar-area{flex:1;position:relative;height:38px;}
.vn-bar-area,.vn-group-bar-area{--mp:8.3333%;}
.vn-bar-area::before,.vn-group-bar-area::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background:repeating-linear-gradient(90deg,transparent 0,transparent calc(var(--mp) - 1px),rgba(255,255,255,.05) calc(var(--mp) - 1px),rgba(255,255,255,.05) var(--mp));
}
.vn-today-line{position:absolute;top:0;bottom:0;width:2px;background:rgba(255,0,0,.9);z-index:10;pointer-events:none;}
.vn-today-label{position:absolute;top:2px;font-size:.58rem;color:#ff0000;font-weight:700;white-space:nowrap;transform:translateX(-50%);}
.vn-sbar{
  position:absolute;top:50%;transform:translateY(-50%);
  height:18px;border-radius:4px;opacity:.85;cursor:default;
  transition:opacity .15s,height .15s;
}
.vn-sbar:hover{opacity:1;height:24px}
.vn-sbar::after{
  content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);
  background:rgba(0,0,0,.95);color:#fff;font-size:.68rem;padding:4px 10px;border-radius:5px;
  white-space:nowrap;pointer-events:none;opacity:0;transition:opacity .15s;z-index:100;
  border:1px solid rgba(255,255,255,.12);
}
.vn-sbar:hover::after{opacity:1}
.vn-group-body.hidden{display:none}

/* ── POPUPS ── */
.vn-popup{
  position:fixed;inset:0;z-index:8000;display:flex;align-items:center;justify-content:center;
  background:rgba(0,0,0,.75);backdrop-filter:blur(6px);
  opacity:0;pointer-events:none;transition:opacity .35s;
}
.vn-popup.show{opacity:1;pointer-events:all;}
.vn-op-box{
  background:linear-gradient(160deg,rgb(8,18,8) 0%,rgb(6,6,6) 100%);
  border:1px solid rgba(0,220,80,.35);border-radius:14px;
  padding:36px 44px 32px;text-align:center;max-width:520px;width:90%;
  box-shadow:0 0 60px rgba(0,200,60,.2),0 0 120px rgba(0,0,0,.8);
  animation:vn-pop-in .4s cubic-bezier(.22,1,.36,1) both;
}
.vn-cl-box{
  background:linear-gradient(160deg,rgb(18,6,6) 0%,rgb(6,6,6) 100%);
  border:1px solid rgba(255,60,60,.35);border-radius:14px;
  padding:36px 44px 32px;text-align:center;max-width:540px;width:90%;
  box-shadow:0 0 60px rgba(255,40,40,.18),0 0 120px rgba(0,0,0,.8);
  animation:vn-pop-in .4s cubic-bezier(.22,1,.36,1) both;
}
@keyframes vn-pop-in{from{transform:scale(.88) translateY(20px);opacity:0}to{transform:none;opacity:1}}
.vn-pop-icon{font-size:3rem;margin-bottom:10px;display:block;}
.vn-pop-title{font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:6px;}
.vn-op-count{font-size:4rem;font-weight:900;color:#4dff88;text-shadow:0 0 30px rgba(0,220,80,.6),0 0 60px rgba(0,200,60,.3);line-height:1.1;margin-bottom:4px;}
.vn-cl-count{font-size:3.4rem;font-weight:900;color:#ff5050;text-shadow:0 0 30px rgba(255,40,40,.6),0 0 60px rgba(255,0,0,.3);line-height:1.1;margin-bottom:4px;}
.vn-pop-sub{font-size:1rem;color:rgba(255,255,255,.7);margin-bottom:22px;}
.vn-op-list{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-bottom:24px;}
.vn-op-item{background:rgba(0,200,60,.12);border:1px solid rgba(0,200,60,.25);border-radius:20px;padding:4px 12px;font-size:.72rem;font-weight:600;color:#4dff88;letter-spacing:.04em;}
.vn-cl-list{display:flex;flex-direction:column;gap:6px;margin-bottom:24px;text-align:left;}
.vn-cl-item{background:rgba(255,40,40,.08);border:1px solid rgba(255,40,40,.22);border-radius:8px;padding:7px 14px;font-size:.74rem;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:10px;}
.vn-cl-item-name{font-weight:700;color:#ff8080;}
.vn-cl-item-days{font-size:.68rem;font-weight:700;background:rgba(255,0,0,.2);color:#ff5050;padding:2px 8px;border-radius:12px;white-space:nowrap;}
.vn-pop-close-btn{font-size:.85rem;font-weight:700;padding:10px 28px;border-radius:8px;cursor:pointer;letter-spacing:.08em;text-transform:uppercase;transition:all .15s;border:none;}
.vn-op-btn{background:rgba(0,200,60,.15);border:1px solid rgba(0,200,60,.4)!important;color:#4dff88;}
.vn-op-btn:hover{background:rgba(0,200,60,.3);box-shadow:0 0 16px rgba(0,200,60,.3);}
.vn-cl-btn{background:rgba(255,40,40,.15);border:1px solid rgba(255,40,40,.4)!important;color:#ff6060;}
.vn-cl-btn:hover{background:rgba(255,40,40,.28);box-shadow:0 0 16px rgba(255,0,0,.25);}

/* ── RESPONSIVE ── */
@media(max-width:1100px){
  .vn-wrap{--vn-name-w:150px;}
  .vn-chart{min-width:860px;}
}
@media(max-width:700px){
  .vn-wrap{--vn-name-w:112px;}
  .vn-moon-grid{gap:2px;}
  .vn-moon-tools{gap:8px;}
  .vn-moon-field{min-width:calc(50% - 4px);}
  .vn-moon-jump{flex:1 1 calc(50% - 4px);}
  .vn-moon-dicon{font-size:1.1rem;}
  .vn-moon-dnum{font-size:.6rem;}
  .vn-moon-dpct{display:none;}
  .vn-moon-legend{font-size:.58rem;gap:4px 10px;}
  .vn-mh-name,.vn-animal-name,.vn-group-label{font-size:.6rem;padding:5px 6px;}
  .vn-mh-month{font-size:.52rem;padding:6px 0;}
  .vn-legend{gap:5px 12px;margin-bottom:14px;}
  .vn-leg{font-size:.66rem;}
  .vn-tp-grid{grid-template-columns:1fr 1fr;gap:6px;}
  .vn-tp-card-name{font-size:.72rem;}
  .vn-today{margin-bottom:14px;}
  .vn-op-box,.vn-cl-box{padding:24px 18px 20px;}
  .vn-chart{min-width:760px;}
}
@media(max-width:400px){
  .vn-tp-grid{grid-template-columns:1fr;}
  .vn-wrap{--vn-name-w:82px;}
  .vn-mh-name,.vn-animal-name,.vn-group-label{font-size:.56rem;}
  .vn-animal-name .sub{display:none;}
  .vn-chart{min-width:620px;}
  .vn-chart-hint{font-size:.56rem;}
}
</style>

<!-- ── CLOSING SOON POPUP ── -->
<div id="vn-close-popup" class="vn-popup">
  <div class="vn-cl-box">
    <span class="vn-pop-icon">&#9888;&#65039;</span>
    <div class="vn-pop-title">Hamarosan lej&aacute;r a vad&aacute;szati id&eacute;ny!</div>
    <div class="vn-cl-count" id="vn-cl-count"></div>
    <div class="vn-pop-sub">vadfajn&aacute;l z&aacute;r a szezon 7 napon bel&uuml;l</div>
    <div class="vn-cl-list" id="vn-cl-list"></div>
    <button class="vn-pop-close-btn vn-cl-btn" onclick="document.getElementById('vn-close-popup').classList.remove('show')">Megn&eacute;zem a napt&aacute;rt</button>
  </div>
</div>

<!-- ── OPENING DAY POPUP ── -->
<div id="vn-open-popup" class="vn-popup">
  <div class="vn-op-box">
    <span class="vn-pop-icon">&#127807;</span>
    <div class="vn-pop-title">Ma megny&iacute;lt &mdash; <span id="vn-op-date"></span></div>
    <div class="vn-op-count" id="vn-op-count"></div>
    <div class="vn-pop-sub">&uacute;j vadfaj vad&aacute;szhat&oacute; ma</div>
    <div class="vn-op-list" id="vn-op-list"></div>
    <button class="vn-pop-close-btn vn-op-btn" onclick="document.getElementById('vn-open-popup').classList.remove('show')">Rendben, hajr&aacute;! &#127993;</button>
  </div>
</div>

<div class="vn-wrap">

  <!-- ── HOLDNAPTÁR ── -->
  <div class="vn-moon">
    <div class="vn-moon-header">
      <button class="vn-moon-nav" id="vn-moon-prev">&#9664;</button>
      <div class="vn-moon-title">
        <span class="vn-moon-icon-big" id="vn-moon-cur-icon"></span>
        <div>
          <div class="vn-moon-cur-name" id="vn-moon-cur-name"></div>
          <div class="vn-moon-cur-sub" id="vn-moon-cur-sub"></div>
        </div>
      </div>
      <button class="vn-moon-nav" id="vn-moon-next">&#9654;</button>
    </div>
    <div class="vn-moon-tools">
      <label class="vn-moon-field">
        <span>Hónap</span>
        <select class="vn-moon-select" id="vn-moon-month"></select>
      </label>
      <label class="vn-moon-field">
        <span>Év</span>
        <select class="vn-moon-select" id="vn-moon-year"></select>
      </label>
      <button class="vn-moon-jump" id="vn-moon-today" type="button">Mai hónap</button>
      <button class="vn-moon-jump" id="vn-moon-plus50" type="button">+50 év</button>
    </div>
    <div class="vn-moon-range-note">Holdnaptár előnézet: az aktuális évtől 50 évre előre.</div>
    <div class="vn-moon-grid" id="vn-moon-grid"></div>
    <div class="vn-moon-legend">
      <span>&#127761; &Uacute;jhold</span><span>&#127762; N&ouml;vekv&#337; sarl&oacute;</span><span>&#127763; Els&#337; negyed</span><span>&#127764; N&ouml;vekv&#337; domboru</span>
      <span>&#127765; Telihold</span><span>&#127766; Fogy&oacute; domboru</span><span>&#127767; Utols&oacute; negyed</span><span>&#127768; Fogy&oacute; sarl&oacute;</span>
    </div>
    <div class="vn-moon-tip" id="vn-moon-tip"></div>
  </div>

  <!-- ── JELMAGYARÁZAT ── -->
  <div class="vn-legend">
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#1ec854"></div>G&iacute;mszarvas</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#f5a623"></div>D&aacute;mszarvas</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#3d9ff5"></div>&#336;z</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#cc44cc"></div>Muflon</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#f55050"></div>Vaddiszn&oacute;</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#f5d020"></div>Apr&oacute;vad</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#20d4d4"></div>Sz&aacute;rnyasvad</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#ff8c00"></div>V&iacute;zi sz&aacute;rnyasvad</div>
    <div class="vn-leg"><div class="vn-leg-dot" style="background:#ffb300"></div>Ragadoz&oacute;k</div>
    <p style="width:100%;text-align:center;font-size:.72rem;color:rgba(255,255,255,.3);margin:4px 0 0;">Csoportokra kattintva &ouml;ssze/kibonthat&oacute;&nbsp;&middot;&nbsp;<span style="color:#ff0000;font-weight:700">|</span>&nbsp;= mai nap</p>
  </div>

  <!-- ── TODAY PANEL ── -->
  <div id="vn-today-panel" class="vn-today"></div>

  <!-- ── GANTT CHART ── -->
  <div class="vn-chart-meta">
    <span class="vn-chart-hint" id="vn-chart-hint">Húzd oldalra a naptárat</span>
  </div>
  <div class="vn-chart-scroll">
    <div class="vn-chart" id="vn-chart"></div>
  </div>

</div><!-- .vn-wrap -->

<script>
(function(){
'use strict';

// ── BUDAPEST TIMEZONE ────────────────────────────────────────────────
function nowBP(){
  const d=new Date();
  const parts={};
  new Intl.DateTimeFormat('en-US',{
    timeZone:'Europe/Budapest',
    year:'numeric',month:'2-digit',day:'2-digit',
    hour:'2-digit',minute:'2-digit',second:'2-digit',
    hour12:false
  }).formatToParts(d).forEach(p=>{if(p.type!=='literal')parts[p.type]=+p.value;});
  parts.weekday=new Intl.DateTimeFormat('hu-HU',{timeZone:'Europe/Budapest',weekday:'long'}).format(d);
  return parts;
}

// ── HELPERS ─────────────────────────────────────────────────────────
const YEAR=2026;
function isLeap(y){return(y%4===0&&y%100!==0)||(y%400===0)}
const MD=[31,isLeap(YEAR)?29:28,31,30,31,30,31,31,30,31,30,31];
const MN=["Jan","Feb","M\u00e1r","\u00c1pr","M\u00e1j","J\u00fan","J\u00fal","Aug","Szep","Okt","Nov","Dec"];
const MF=["Janu\u00e1r","Febru\u00e1r","M\u00e1rcius","\u00c1prilis","M\u00e1jus","J\u00fanius","J\u00falius","Augusztus","Szeptember","Okt\u00f3ber","November","December"];
const TOTAL=MD.reduce((a,b)=>a+b,0);
const _bp=nowBP();
const TODAY={m:_bp.month,d:_bp.day};
function doy(month,day){let i=0;for(let m=1;m<month;m++)i+=MD[m-1];return i+day-1;}
function pct(d){return(d/TOTAL*100).toFixed(4)+'%'}
const TODAY_PCT=(doy(TODAY.m,TODAY.d)/TOTAL*100).toFixed(4)+'%';

function isInSeason(seasons,m,d){
  const t=doy(m,d);
  for(const[ms,ds,me,de]of seasons){
    if(ms<=me){if(t>=doy(ms,ds)&&t<=doy(me,de))return true;}
    else{if(t>=doy(ms,ds)||t<=doy(me,de))return true;}
  }
  return false;
}
function nextSeasonChange(seasons,m,d){
  const cur=isInSeason(seasons,m,d);
  let nm=m,nd=d;
  for(let i=1;i<=400;i++){
    nd++;if(nd>MD[nm-1]){nd=1;nm++;}if(nm>12)nm=1;
    if(isInSeason(seasons,nm,nd)!==cur)return{targetM:nm,targetD:nd};
  }
  return null;
}
function msTillBPDate(targetM,targetD){
  const bp=nowBP();
  const msToMidnight=((23-bp.hour)*3600+(59-bp.minute)*60+(60-bp.second))*1000;
  let nm=bp.month,nd=bp.day,days=0;
  while(!(nm===targetM&&nd===targetD)){
    nd++;if(nd>MD[nm-1]){nd=1;nm++;}if(nm>12)nm=1;
    days++;if(days>400)break;
  }
  return msToMidnight+days*86400000;
}
function fmtMs(ms){
  if(ms<=0)return '0s';
  const s=Math.floor(ms/1000);
  const d=Math.floor(s/86400),h=Math.floor((s%86400)/3600),m=Math.floor((s%3600)/60),sec=s%60;
  if(d>0)return d+'n '+h+'\u00f3 '+m+'p';
  if(h>0)return h+'\u00f3 '+m+'p '+sec+'s';
  return m+'p '+sec+'s';
}

function segs(seasons){
  const out=[];
  for(const[ms,ds,me,de]of seasons){
    if(ms<=me){
      const s=doy(ms,ds),e=doy(me,de);
      out.push({sp:s/TOTAL*100,wp:(e-s+1)/TOTAL*100,lbl:`${MF[ms-1]} ${ds}. \u2013 ${MF[me-1]} ${de}.`});
    }else{
      const e1=doy(me,de);
      if(e1>=0)out.push({sp:0,wp:(e1+1)/TOTAL*100,lbl:`Jan 1. \u2013 ${MF[me-1]} ${de}.`});
      const s2=doy(ms,ds);
      out.push({sp:s2/TOTAL*100,wp:(TOTAL-s2)/TOTAL*100,lbl:`${MF[ms-1]} ${ds}. \u2013 Dec 31.`});
    }
  }
  return out;
}

// ── VADFAJ ADATOK ────────────────────────────────────────────────────
const groups=[
  {label:"SZARVASF\u00c9L\u00c9K \u2013 G\u00cdM",color:"#1ec854",animals:[
    {name:"G\u00edmszarvas \u2013 bika",sub:"Cervus elaphus \u2642",seasons:[[9,1,12,31]]},
    {name:"G\u00edmszarvas \u2013 teh\u00e9n / \u00fcn\u0151",sub:"Cervus elaphus \u2640",seasons:[[10,1,1,31]]},
    {name:"G\u00edmszarvas \u2013 borj\u00fa",sub:"Cervus elaphus juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"SZARVASF\u00c9L\u00c9K \u2013 D\u00c1M",color:"#f5a623",animals:[
    {name:"D\u00e1mszarvas \u2013 bak",sub:"Dama dama \u2642",seasons:[[9,1,12,31]]},
    {name:"D\u00e1mszarvas \u2013 suta",sub:"Dama dama \u2640",seasons:[[10,1,1,31]]},
    {name:"D\u00e1mszarvas \u2013 gida",sub:"Dama dama juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"\u0150Z",color:"#3d9ff5",animals:[
    {name:"\u0150zbak",sub:"Capreolus capreolus \u2642",seasons:[[4,15,9,30]]},
    {name:"\u0150zsuta",sub:"Capreolus capreolus \u2640",seasons:[[10,1,1,31]]},
    {name:"\u0150zgida",sub:"Capreolus capreolus juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"MUFLON",color:"#cc44cc",animals:[
    {name:"Muflon \u2013 kos",sub:"Ovis musimon \u2642",seasons:[[8,1,1,31]]},
    {name:"Muflon \u2013 juh",sub:"Ovis musimon \u2640",seasons:[[10,1,1,31]]},
    {name:"Muflon \u2013 b\u00e1r\u00e1ny",sub:"Ovis musimon juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"VADDISZN\u00d3",color:"#f55050",animals:[
    {name:"Vaddiszn\u00f3 \u2013 kan",sub:"Sus scrofa \u2642",seasons:[[1,1,12,31]]},
    {name:"Vaddiszn\u00f3 \u2013 koca",sub:"Sus scrofa \u2640 (nem szoptat\u00f3s)",seasons:[[7,1,12,31]]},
    {name:"Vaddiszn\u00f3 \u2013 malac",sub:"Sus scrofa juv.",seasons:[[7,1,12,31]]},
  ]},
  {label:"APR\u00d3VAD \u2013 SZ\u00c1RAZF\u00d6LDI",color:"#f5d020",animals:[
    {name:"Mezei ny\u00fal",sub:"Lepus europaeus",seasons:[[10,1,12,31]]},
    {name:"F\u00e1c\u00e1n \u2013 kakas",sub:"Phasianus colchicus \u2642",seasons:[[10,1,1,31]]},
    {name:"F\u00e1c\u00e1n \u2013 ty\u00fak",sub:"Phasianus colchicus \u2640",seasons:[[10,1,11,30]]},
    {name:"Fogoly",sub:"Perdix perdix",seasons:[[10,1,11,30]]},
    {name:"Balk\u00e1ni gerle",sub:"Streptopelia decaocto",seasons:[[6,1,2,28]]},
    {name:"Vadgalamb",sub:"Columba palumbus",seasons:[[8,15,1,31]]},
    {name:"Erdei szalonka",sub:"Scolopax rusticola",seasons:[[3,1,4,30],[10,1,11,30]]},
  ]},
  {label:"SZ\u00c1RNYASVAD",color:"#20d4d4",animals:[
    {name:"F\u00fcrj",sub:"Coturnix coturnix",seasons:[[8,15,10,31]]},
    {name:"Serely",sub:"Sturnus vulgaris",seasons:[[8,1,1,31]]},
  ]},
  {label:"V\u00cdZI SZ\u00c1RNYASVAD",color:"#ff8c00",animals:[
    {name:"T\u0151k\u00e9s r\u00e9ce \u2013 g\u00e1cs\u00e9r",sub:"Anas platyrhynchos \u2642",seasons:[[8,15,1,31]]},
    {name:"T\u0151k\u00e9s r\u00e9ce \u2013 toj\u00f3",sub:"Anas platyrhynchos \u2640",seasons:[[8,15,1,31]]},
    {name:"B\u00f6jti r\u00e9ce",sub:"Anas querquedula",seasons:[[8,15,11,30]]},
    {name:"Ny\u00edlfark\u00fa r\u00e9ce",sub:"Anas acuta",seasons:[[8,15,1,31]]},
    {name:"Nagy lilik",sub:"Anser albifrons",seasons:[[10,1,1,31]]},
    {name:"Vet\u00e9si l\u00fad",sub:"Anser fabalis",seasons:[[10,1,1,31]]},
    {name:"Ny\u00e1ri l\u00fad",sub:"Anser anser",seasons:[[8,15,1,31]]},
  ]},
  {label:"RAGADOZ\u00d3K",color:"#ffb300",animals:[
    {name:"V\u00f6r\u00f6sr\u00f3ka",sub:"Vulpes vulpes",seasons:[[1,1,12,31]]},
    {name:"Aranysakal",sub:"Canis aureus",seasons:[[1,1,12,31]]},
    {name:"Borz",sub:"Meles meles",seasons:[[9,1,11,30]]},
    {name:"Nyest / Nyuszt",sub:"Martes foina / M. martes",seasons:[[10,1,2,28]]},
    {name:"Dolm\u00e1nyos varj\u00fa",sub:"Corvus cornix",seasons:[[1,1,12,31]]},
    {name:"Szarka",sub:"Pica pica",seasons:[[1,1,12,31]]},
  ]},
];

// ── COUNTDOWNS ──────────────────────────────────────────────────────
const _acdList=[];
const _ghList=[];
function updateAllCountdowns(){
  const bp=nowBP();
  for(const {el,seasons} of _acdList){
    const on=isInSeason(seasons,bp.month,bp.day);
    const next=nextSeasonChange(seasons,bp.month,bp.day);
    const dot=el.querySelector('.vn-acd-dot');
    const txt=el.querySelector('.vn-acd-txt');
    const lbl=el.querySelector('.vn-acd-lbl');
    el.className='vn-acd '+(on?'on':'off');
    if(next){
      const ms=msTillBPDate(next.targetM,next.targetD);
      txt.textContent=fmtMs(ms);
      lbl.textContent=on?' \u203a tilalom':' \u203a ny\u00edt\u00e1s';
    }else{txt.textContent='\u2013';lbl.textContent='';}
  }
  for(const {gl,gst,animals} of _ghList){
    const cnt=animals.filter(a=>isInSeason(a.seasons,bp.month,bp.day)).length;
    const has=cnt>0;
    if(has){gl.classList.add('has-open');}else{gl.classList.remove('has-open');}
    gst.style.color=has?'#00e060':'rgba(255,48,48,.6)';
    gst.textContent=has?cnt+' faj vad\u00e1szhat\u00f3':'tilalom';
  }
}
setInterval(updateAllCountdowns,1000);

// ── TODAY PANEL ──────────────────────────────────────────────────────
function buildTodayPanel(){
  const bp=nowBP();
  const tm=bp.month,td=bp.day;
  const todayDoy=doy(tm,td);
  const open=[],soon=[];
  const seen=new Set();

  for(const g of groups){
    for(const a of g.animals){
      if(seen.has(a.name))continue;
      let found=false;
      for(const[ms,ds,me,de]of a.seasons){
        if(ms<=me){
          const s=doy(ms,ds),e=doy(me,de);
          if(todayDoy>=s&&todayDoy<=e){
            const neverCloses=(nextSeasonChange(a.seasons,tm,td)===null);
            open.push({name:a.name,sub:a.sub||'',color:g.color,daysLeft:neverCloses?9999:e-todayDoy,isNew:todayDoy===s,neverCloses:neverCloses,range:`${MF[ms-1]} ${ds}. \u2013 ${MF[me-1]} ${de}.`,closing:`${MF[me-1]} ${de}.`,closeM:me,closeD:de});
            seen.add(a.name);found=true;break;
          }
          if(!found&&s>todayDoy&&s-todayDoy<=14){
            const ex=soon.findIndex(x=>x.name===a.name);
            if(ex===-1)soon.push({name:a.name,sub:a.sub||'',color:g.color,openIn:s-todayDoy,range:`${MF[ms-1]} ${ds}. \u2013 ${MF[me-1]} ${de}.`,openDate:`${MF[ms-1]} ${ds}.`});
          }
        }else{
          const e1=doy(me,de);
          if(todayDoy<=e1){
            open.push({name:a.name,sub:a.sub||'',color:g.color,daysLeft:e1-todayDoy,isNew:false,range:`Jan 1. \u2013 ${MF[me-1]} ${de}.`,closing:`${MF[me-1]} ${de}.`,closeM:me,closeD:de});
            seen.add(a.name);found=true;break;
          }
          const s2=doy(ms,ds);
          if(todayDoy>=s2){
            open.push({name:a.name,sub:a.sub||'',color:g.color,daysLeft:TOTAL-1-todayDoy,isNew:todayDoy===s2,range:`${MF[ms-1]} ${ds}. \u2013 Dec 31.`,closing:'December 31.',closeM:12,closeD:31});
            seen.add(a.name);found=true;break;
          }
          if(!found&&s2>todayDoy&&s2-todayDoy<=14){
            const ex=soon.findIndex(x=>x.name===a.name);
            if(ex===-1)soon.push({name:a.name,sub:a.sub||'',color:g.color,openIn:s2-todayDoy,range:`${MF[ms-1]} ${ds}. \u2013 Dec 31.`,openDate:`${MF[ms-1]} ${ds}.`});
          }
        }
        if(found)break;
      }
    }
  }

  const soonF=soon.filter(x=>!seen.has(x.name));
  open.sort((a,b)=>{
    if(a.isNew&&!b.isNew)return -1;if(!a.isNew&&b.isNew)return 1;
    const aU=a.daysLeft<=7,bU=b.daysLeft<=7;
    if(aU&&!bU)return -1;if(!aU&&bU)return 1;
    return a.daysLeft-b.daysLeft;
  });
  soonF.sort((a,b)=>a.openIn-b.openIn);

  const panel=document.getElementById('vn-today-panel');

  // Header
  const hdr=document.createElement('div');hdr.className='vn-tp-header';
  hdr.innerHTML=`<div class="vn-tp-title">&#127806; Ma vad&aacute;szhat&oacute; <span id="vn-tp-cnt"></span></div><div class="vn-tp-live" id="vn-tp-live"></div>`;
  panel.appendChild(hdr);

  function updateLive(){
    const n=nowBP();
    const MFH=['\u006a\u0061\u006e\u0075\u00e1r','\u0066\u0065\u0062\u0072\u0075\u00e1r','\u006d\u00e1\u0072\u0063\u0069\u0075\u0073','\u00e1\u0070\u0072\u0069\u006c\u0069\u0073','\u006d\u00e1\u006a\u0075\u0073','\u006a\u00fa\u006e\u0069\u0075\u0073','\u006a\u00fa\u006c\u0069\u0075\u0073','\u0061\u0075\u0067\u0075\u0073\u007a\u0074\u0075\u0073','\u0073\u007a\u0065\u0070\u0074\u0065\u006d\u0062\u0065\u0072','\u006f\u006b\u0074\u00f3\u0062\u0065\u0072','\u006e\u006f\u0076\u0065\u006d\u0062\u0065\u0072','\u0064\u0065\u0063\u0065\u006d\u0062\u0065\u0072'];
    const el=document.getElementById('vn-tp-live');
    if(el)el.textContent=`${n.year}. ${MFH[n.month-1]} ${n.day}. \u2013 ${String(n.hour).padStart(2,'0')}:${String(n.minute).padStart(2,'0')}`;
    const cnt=document.getElementById('vn-tp-cnt');
    if(cnt)cnt.textContent=`(${open.length} vadfaj)`;
  }
  updateLive();setInterval(updateLive,30000);

  const _cdList=[];
  function updateCDs(){for(const{el,closeM,closeD}of _cdList){el.textContent='\u23f1 '+fmtMs(msTillBPDate(closeM,closeD))+' van h\u00e1tra';}}
  setInterval(updateCDs,1000);

  const lbl=document.createElement('div');lbl.className='vn-tp-lbl';lbl.textContent='Jelenleg nyitott id\u00e9ny';panel.appendChild(lbl);

  if(open.length){
    const grid=document.createElement('div');grid.className='vn-tp-grid';
    for(const a of open){
      let bc,bt;
      if(a.isNew){bc='vn-b-new';bt='&#127881; Feloldva ma';}
      else if(a.neverCloses){bc='vn-b-ok';bt='Egész évben vadászható';}
      else if(a.daysLeft===0){bc='vn-b-urgent';bt='&#9888; Ma az utols\u00f3 nap!';}
      else if(a.daysLeft<=7){bc='vn-b-urgent';bt=`&#9888;&#9888; Z\u00c1RUL: m\u00e9g ${a.daysLeft} nap! \u2013 ${a.closing}`;}
      else if(a.daysLeft<=14){bc='vn-b-warn';bt=`M\u00e9g ${a.daysLeft} nap \u2013 ${a.closing}`;}
      else{bc='vn-b-ok';bt=`${a.closing} (${a.daysLeft} nap)`;}
      const card=document.createElement('div');
      card.className='vn-tp-card'+(a.isNew?' vn-tp-card--new':'')+(a.daysLeft<=7&&!a.isNew&&!a.neverCloses?' vn-tp-card--closing':'');
      card.style.borderLeftColor=a.color;
      card.innerHTML=`<div class="vn-tp-card-name">${a.name}</div><div class="vn-tp-card-sub">${a.sub}</div><div class="vn-tp-card-range">${a.range}</div><span class="vn-tp-badge ${bc}">${bt}</span><div class="vn-tp-cd"></div>`;
      if(!a.neverCloses)_cdList.push({el:card.querySelector('.vn-tp-cd'),closeM:a.closeM,closeD:a.closeD});
      grid.appendChild(card);
    }
    panel.appendChild(grid);
  }else{
    const em=document.createElement('div');em.className='vn-tp-empty';em.textContent='Ma nincs nyitott vad\u00e1szati id\u00e9ny.';panel.appendChild(em);
  }

  if(soonF.length){
    const lbl2=document.createElement('div');lbl2.className='vn-tp-lbl';lbl2.textContent='Hamarosan ny\u00edlik (14 napon bel\u00fcl)';panel.appendChild(lbl2);
    const grid2=document.createElement('div');grid2.className='vn-tp-grid';
    for(const a of soonF){
      const card=document.createElement('div');
      card.className='vn-tp-card';card.style.borderLeftColor=a.color;
      card.innerHTML=`<div class="vn-tp-card-name">${a.name}</div><div class="vn-tp-card-sub">${a.sub}</div><div class="vn-tp-card-range">${a.range}</div><span class="vn-tp-badge vn-b-soon">Ny\u00edlik: ${a.openDate} (${a.openIn} nap m\u00falva)</span>`;
      grid2.appendChild(card);
    }
    panel.appendChild(grid2);
  }
  updateCDs();

  // Opening popup
  const newToday=open.filter(a=>a.isNew);
  if(newToday.length>0){
    const n=nowBP();
    const MFH2=['\u006a\u0061\u006e\u0075\u00e1r','\u0066\u0065\u0062\u0072\u0075\u00e1r','\u006d\u00e1\u0072\u0063\u0069\u0075\u0073','\u00e1\u0070\u0072\u0069\u006c\u0069\u0073','\u006d\u00e1\u006a\u0075\u0073','\u006a\u00fa\u006e\u0069\u0075\u0073','\u006a\u00fa\u006c\u0069\u0075\u0073','\u0061\u0075\u0067\u0075\u0073\u007a\u0074\u0075\u0073','\u0073\u007a\u0065\u0070\u0074\u0065\u006d\u0062\u0065\u0072','\u006f\u006b\u0074\u00f3\u0062\u0065\u0072','\u006e\u006f\u0076\u0065\u006d\u0062\u0065\u0072','\u0064\u0065\u0063\u0065\u006d\u0062\u0065\u0072'];
    document.getElementById('vn-op-date').textContent=`${n.year}. ${MFH2[n.month-1]} ${n.day}.`;
    document.getElementById('vn-op-count').textContent=newToday.length;
    const list=document.getElementById('vn-op-list');
    newToday.forEach(a=>{const el=document.createElement('div');el.className='vn-op-item';el.textContent=a.name;list.appendChild(el);});
    setTimeout(()=>document.getElementById('vn-open-popup').classList.add('show'),1200);
  }

  // Closing popup
  const closingSoon=open.filter(a=>a.daysLeft>0&&a.daysLeft<=7);
  if(closingSoon.length>0){
    document.getElementById('vn-cl-count').textContent=closingSoon.length;
    const clList=document.getElementById('vn-cl-list');
    closingSoon.sort((a,b)=>a.daysLeft-b.daysLeft);
    closingSoon.forEach(a=>{
      const el=document.createElement('div');el.className='vn-cl-item';
      el.innerHTML=`<span class="vn-cl-item-name">${a.name}</span><span class="vn-cl-item-days">m\u00e9g ${a.daysLeft} nap \u2013 ${a.closing}</span>`;
      clList.appendChild(el);
    });
    setTimeout(()=>document.getElementById('vn-close-popup').classList.add('show'),newToday.length>0?8000:1200);
  }
}
buildTodayPanel();

// ── GANTT CHART ──────────────────────────────────────────────────────
const chart=document.getElementById('vn-chart');
const chartWrap=document.querySelector('.vn-wrap');
const chartScroll=document.querySelector('.vn-chart-scroll');

function updateVnOverflowState(){
  if(!chartWrap||!chartScroll)return;
  const overflow=chartScroll.scrollWidth>chartScroll.clientWidth+4;
  chartWrap.classList.toggle('has-overflow',overflow);
}
if(chartScroll){
  chartScroll.addEventListener('scroll',()=>{
    if(chartWrap)chartWrap.classList.add('is-scrolled');
  },{passive:true});
  window.addEventListener('resize',()=>{
    updateVnOverflowState();
  });
}

function makeTodayLine(){
  const t=document.createElement('div');t.className='vn-today-line';t.style.left=TODAY_PCT;return t;
}
function makeBarArea(h){
  const ba=document.createElement('div');ba.className='vn-bar-area';ba.style.height=h+'px';ba.appendChild(makeTodayLine());return ba;
}

// Month header
const mh=document.createElement('div');mh.className='vn-month-header';
const mhn=document.createElement('div');mhn.className='vn-mh-name';mhn.textContent='Vadfaj';mh.appendChild(mhn);
const mhm=document.createElement('div');mhm.className='vn-mh-months';
for(let m=0;m<12;m++){
  const d=document.createElement('div');
  d.className='vn-mh-month'+(m===TODAY.m-1?' current':'');
  d.textContent=MN[m];mhm.appendChild(d);
}
const tl=document.createElement('div');tl.className='vn-today-line';tl.style.left=TODAY_PCT;
const tlbl=document.createElement('div');tlbl.className='vn-today-label';tlbl.textContent='ma';tlbl.style.left=TODAY_PCT;
mhm.appendChild(tl);mhm.appendChild(tlbl);mh.appendChild(mhm);chart.appendChild(mh);

// Csoportok rendezése: legutolsóbb megnyílt idény elöl
function grpDaysSinceOpen2(g){
  let best=9999;
  for(const a of g.animals){
    for(const s of a.seasons){
      if(isInSeason([s],TODAY.m,TODAY.d)){
        const d=(doy(TODAY.m,TODAY.d)-doy(s[0],s[1])+TOTAL)%TOTAL;
        if(d<best)best=d;
      }
    }
  }
  return best;
}
groups.sort((a,b)=>grpDaysSinceOpen2(a)-grpDaysSinceOpen2(b));

const _grpData2=[];
for(const g of groups){
  const openCnt=g.animals.filter(a=>isInSeason(a.seasons,TODAY.m,TODAY.d)).length;
  const hasOpen=openCnt>0;

  const gh=document.createElement('div');gh.className='vn-group-header';
  const gl=document.createElement('div');gl.className='vn-group-label collapsed'+(hasOpen?' has-open':'');
  gl.innerHTML=`<span class="vn-gl-dot"></span><span class="vn-arrow">\u25bc</span>${g.label}`;
  gh.appendChild(gl);
  const gba=document.createElement('div');gba.className='vn-group-bar-area';gba.appendChild(makeTodayLine());
  const gst=document.createElement('span');gst.className='vn-gh-status';
  gst.style.color=hasOpen?'#00e060':'rgba(255,48,48,.6)';
  gst.textContent=hasOpen?openCnt+' faj vad\u00e1szhat\u00f3':'tilalom';
  gba.appendChild(gst);gh.appendChild(gba);chart.appendChild(gh);

  const gbody=document.createElement('div');gbody.className='vn-group-body hidden';
  for(const a of g.animals){
    const row=document.createElement('div');row.className='vn-animal-row';
    const nd=document.createElement('div');nd.className='vn-animal-name';
    nd.innerHTML=a.name+(a.sub?`<span class="sub">${a.sub}</span>`:'');
    const acd=document.createElement('div');acd.className='vn-acd';
    acd.innerHTML='<div class="vn-acd-dot"></div><span class="vn-acd-txt"></span><span class="vn-acd-lbl"></span>';
    nd.appendChild(acd);_acdList.push({el:acd,seasons:a.seasons});
    row.appendChild(nd);
    const ba=makeBarArea(38);
    for(const s of segs(a.seasons)){
      const bar=document.createElement('div');bar.className='vn-sbar';
      bar.style.left=s.sp+'%';bar.style.width=s.wp+'%';bar.style.background=g.color;
      bar.dataset.tip=`${a.name}: ${s.lbl}`;ba.appendChild(bar);
    }
    row.appendChild(ba);gbody.appendChild(row);
  }
  chart.appendChild(gbody);
  _grpData2.push({gbody,gl});
  _ghList.push({gl,gst,animals:g.animals});
  gh.addEventListener('click',()=>{
    const h=gbody.classList.toggle('hidden');
    gl.classList.toggle('collapsed',h);
  });
}
/* Első csoport (legutolsóbb megnyílt) automatikusan kinyílik */
if(_grpData2.length>0&&grpDaysSinceOpen2(groups[0])<9999){
  _grpData2[0].gbody.classList.remove('hidden');
  _grpData2[0].gl.classList.remove('collapsed');
}
updateAllCountdowns();
updateVnOverflowState();

// ── HOLDNAPTÁR ───────────────────────────────────────────────────────
(function(){
  const KNOWN_NEW=Date.UTC(2000,0,6,18,14,0);
  const SYNODIC=29.53058867*24*3600*1000;

  function moonPhase(date){
    const elapsed=date.getTime()-KNOWN_NEW;
    return((elapsed%SYNODIC)+SYNODIC)%SYNODIC/SYNODIC;
  }
  function phaseIcon(f){return['\ud83c\udf11','\ud83c\udf12','\ud83c\udf13','\ud83c\udf14','\ud83c\udf15','\ud83c\udf16','\ud83c\udf17','\ud83c\udf18'][Math.round(f*8)%8];}
  function phaseName(f){return['&Uacute;jhold','N&ouml;vekv&#337; sarl&oacute;','Els&#337; negyed','N&ouml;vekv&#337; dombor&uacute;','Telihold','Fogy&oacute; dombor&uacute;','Utols&oacute; negyed','Fogy&oacute; sarl&oacute;'][Math.round(f*8)%8];}
  function illumination(f){return Math.round((1-Math.cos(f*2*Math.PI))/2*100);}
  function huntTip(f){
    const p=illumination(f);
    if(f<0.03||f>0.97)return '&#127761; <b>&Uacute;jhold k&ouml;r&uuml;l:</b> Az &aacute;llatok nappal aktívabbak — kit&#369;n&#337; nappali vad&aacute;szat.';
    if(f<0.22)return '&#127762; <b>N&ouml;vekv&#337; sarl&oacute;:</b> Hajnali &eacute;s d&eacute;lel&#337;tti &oacute;r&aacute;kban fokozott aktivit&aacute;s.';
    if(f<0.28)return '&#127763; <b>Els&#337; negyed:</b> Vegyes aktivit&aacute;s — d&eacute;lel&#337;tt &eacute;s kora este egyform&aacute;n eredm&eacute;nyes.';
    if(f<0.47)return '&#127764; <b>N&ouml;vekv&#337; dombor&uacute;:</b> Az esti aktivit&aacute;s n&ouml;vekszik — alkalmas esti lesekhez.';
    if(f<0.53)return `&#127765; <b>Telihold k&ouml;r&uuml;l (${p}%):</b> Az &aacute;llatok &eacute;jjel akt&iacute;vak, nappal pihennek — &eacute;jszakai les ideális.`;
    if(f<0.72)return '&#127766; <b>Fogy&oacute; dombor&uacute;:</b> Hajnali aktivit&aacute;si cs&uacute;cs — legjobb a virradati les.';
    if(f<0.78)return '&#127767; <b>Utols&oacute; negyed:</b> A reggeli &oacute;r&aacute;k adnak t&ouml;bb es&eacute;lyt.';
    return '&#127768; <b>Fogy&oacute; sarl&oacute;:</b> S&ouml;t&eacute;ted&#337; &eacute;jszak&aacute;k — az &aacute;llatok &uacute;jra nappalra tolj&aacute;k aktivit&aacute;sukat.';
  }

  const DOW=['\u0048','\u004b','\u0053\u007a\u0065','\u0043\u0073','\u0050','\u0053\u007a\u006f','\u0056'];
  const MONTHS=['\u004a\u0061\u006e\u0075\u00e1r','\u0046\u0065\u0062\u0072\u0075\u00e1r','\u004d\u00e1\u0072\u0063\u0069\u0075\u0073','\u00c1\u0070\u0072\u0069\u006c\u0069\u0073','\u004d\u00e1\u006a\u0075\u0073','\u004a\u00fa\u006e\u0069\u0075\u0073','\u004a\u00fa\u006c\u0069\u0075\u0073','\u0041\u0075\u0067\u0075\u0073\u007a\u0074\u0075\u0073','\u0053\u007a\u0065\u0070\u0074\u0065\u006d\u0062\u0065\u0072','\u004f\u006b\u0074\u00f3\u0062\u0065\u0072','\u004e\u006f\u0076\u0065\u006d\u0062\u0065\u0072','\u0044\u0065\u0063\u0065\u006d\u0062\u0065\u0072'];
  const bp=nowBP();
  const MIN_YEAR=bp.year;
  const MAX_YEAR=bp.year+50;
  let viewYear=bp.year,viewMonth=bp.month-1;

  function setupMoonControls(){
    const monthSelect=document.getElementById('vn-moon-month');
    const yearSelect=document.getElementById('vn-moon-year');
    const todayBtn=document.getElementById('vn-moon-today');
    const plus50Btn=document.getElementById('vn-moon-plus50');
    if(!monthSelect||!yearSelect)return;
    monthSelect.innerHTML=MONTHS.map((label,index)=>`<option value="${index}">${label}</option>`).join('');
    for(let year=MIN_YEAR;year<=MAX_YEAR;year++){
      const option=document.createElement('option');
      option.value=String(year);
      option.textContent=String(year);
      yearSelect.appendChild(option);
    }
    monthSelect.addEventListener('change',()=>{viewMonth=Number(monthSelect.value);render();});
    yearSelect.addEventListener('change',()=>{viewYear=Number(yearSelect.value);render();});
    if(todayBtn){
      todayBtn.addEventListener('click',()=>{viewYear=bp.year;viewMonth=bp.month-1;render();});
    }
    if(plus50Btn){
      plus50Btn.addEventListener('click',()=>{viewYear=MAX_YEAR;viewMonth=bp.month-1;render();});
    }
  }

  function render(){
    const grid=document.getElementById('vn-moon-grid');
    const tip=document.getElementById('vn-moon-tip');
    const curIcon=document.getElementById('vn-moon-cur-icon');
    const curName=document.getElementById('vn-moon-cur-name');
    const curSub=document.getElementById('vn-moon-cur-sub');
    const monthSelect=document.getElementById('vn-moon-month');
    const yearSelect=document.getElementById('vn-moon-year');
    if(!grid)return;
    const now=new Date();
    const cf=moonPhase(now);
    if(viewYear<MIN_YEAR)viewYear=MIN_YEAR;
    if(viewYear>MAX_YEAR)viewYear=MAX_YEAR;
    curIcon.textContent=phaseIcon(cf);
    curName.innerHTML=phaseName(cf)+' \u2014 '+illumination(cf)+'% megvil\u00e1g\u00edt\u00e1s';
    curSub.textContent=MONTHS[viewMonth]+' '+viewYear+' holdnapt\u00e1r • 50 évre előre';
    tip.innerHTML='&#127993; Vad\u00e1szati tan\u00e1cs: '+huntTip(cf);
    if(monthSelect)monthSelect.value=String(viewMonth);
    if(yearSelect)yearSelect.value=String(viewYear);
    grid.innerHTML='';
    DOW.forEach(d=>{const el=document.createElement('div');el.className='vn-moon-dow';el.textContent=d;grid.appendChild(el);});
    const first=new Date(viewYear,viewMonth,1);
    let startDow=first.getDay();startDow=(startDow+6)%7;
    const daysInMonth=new Date(viewYear,viewMonth+1,0).getDate();
    const todayBP=nowBP();
    for(let i=0;i<startDow;i++){const el=document.createElement('div');el.className='vn-moon-day vn-moon-empty';grid.appendChild(el);}
    for(let d=1;d<=daysInMonth;d++){
      const date=new Date(Date.UTC(viewYear,viewMonth,d,12,0,0));
      const f=moonPhase(date);
      const icon=phaseIcon(f);
      const p=illumination(f);
      const isToday=(d===todayBP.day&&viewMonth===todayBP.month-1&&viewYear===todayBP.year);
      const isFullNew=(f<0.05||f>0.95||(f>0.47&&f<0.53));
      const el=document.createElement('div');
      el.className='vn-moon-day'+(isToday?' vn-moon-today':'')+(isFullNew?' vn-moon-fullnew':'');
      el.title=d+'. \u2014 '+phaseName(f).replace(/&[^;]+;/g,'')+' ('+p+'%)';
      el.innerHTML='<span class="vn-moon-dicon">'+icon+'</span><div class="vn-moon-dnum">'+d+'</div><div class="vn-moon-dpct">'+p+'%</div>';
      grid.appendChild(el);
    }
  }

  document.getElementById('vn-moon-prev').addEventListener('click',()=>{viewMonth--;if(viewMonth<0){viewMonth=11;viewYear--;}render();});
  document.getElementById('vn-moon-next').addEventListener('click',()=>{viewMonth++;if(viewMonth>11){viewMonth=0;viewYear++;}render();});
  setupMoonControls();
  render();
})();

})(); // IIFE end
</script>

<?php get_footer(); ?>
