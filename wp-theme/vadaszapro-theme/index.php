<?php
/**
 * index.php – Főoldal / fallback template
 */
get_header(); ?>

<?php if ( is_front_page() ): ?>
<?php
$va_show_season_widget = get_option( 'va_show_hunting_season_widget', '1' ) === '1';
$va_show_agri_widget   = get_option( 'va_show_agri_widget', '1' ) === '1';
$va_show_moon_widget   = get_option( 'va_show_moon_widget', '1' ) === '1';
$va_show_weather_widget = get_option( 'va_show_weather_widget', '1' ) === '1';
$va_show_home_hunting_calendar = get_option( 'va_show_home_hunting_calendar', '1' ) === '1';
$va_home_cards_per_row = max( 2, min( 10, (int) get_option( 'va_layout_grid_cols_desktop', 4 ) ) );
$va_home_h1 = trim( wp_strip_all_tags( (string) get_option( 'va_home_hero_title', '' ) ) );
if ( $va_home_h1 === '' ) {
  $va_home_h1 = trim( wp_strip_all_tags( get_bloginfo( 'name' ) ) );
}
$va_agri_month = (int) current_time( 'n' );
$va_agri_month_labels = [ 'jan', 'febr', 'márc', 'ápr', 'máj', 'jún', 'júl', 'aug', 'szept', 'okt', 'nov', 'dec' ];
$va_agri_is_in = static function( $m, $start, $end ) {
  if ( $start <= $end ) {
    return $m >= $start && $m <= $end;
  }
  return $m >= $start || $m <= $end;
};
$va_agri_crops_widget = [
  [ 'name' => 'Őszi búza',   'sow' => [ 9, 10 ], 'bloom' => [ 5, 6 ], 'ripen' => [ 7, 8 ],  'harvest' => [ 7, 8 ] ],
  [ 'name' => 'Őszi árpa',   'sow' => [ 9, 10 ], 'bloom' => [ 4, 5 ], 'ripen' => [ 6, 7 ],  'harvest' => [ 6, 7 ] ],
  [ 'name' => 'Kukorica',    'sow' => [ 4, 5 ],  'bloom' => [ 7, 8 ], 'ripen' => [ 9, 10 ], 'harvest' => [ 9, 10 ] ],
  [ 'name' => 'Napraforgó',  'sow' => [ 4, 4 ],  'bloom' => [ 7, 7 ], 'ripen' => [ 9, 9 ],  'harvest' => [ 9, 9 ] ],
  [ 'name' => 'Őszi repce',  'sow' => [ 8, 9 ],  'bloom' => [ 4, 5 ], 'ripen' => [ 6, 7 ],  'harvest' => [ 6, 7 ] ],
  [ 'name' => 'Szója',       'sow' => [ 4, 5 ],  'bloom' => [ 6, 7 ], 'ripen' => [ 9, 10 ], 'harvest' => [ 9, 10 ] ],
];
?>
<div class="va-home-layout">
<h1 class="screen-reader-text"><?php echo esc_html( $va_home_h1 ); ?></h1>

<!-- ═══ BAL SIDEBAR ═════════════════════════════════════════════ -->
<aside class="va-home-sidebar">

<!-- ═══ VADÁSZATI IDÉNY WIDGET ════════════════════════════ -->
<?php if ( $va_show_season_widget ): ?>
<section class="va-season" id="ideny-widget">
  <div class="va-season__hd">
    <span class="va-season__title">🏹 Vadászati idény</span>
    <span class="va-season__date" id="sw-date">–</span>
  </div>
  <div class="va-season__cnt" id="sw-open-cnt"></div>
  <div id="sw-open"></div>
  <div class="va-season__soon-lbl" id="sw-soon-lbl"></div>
  <div id="sw-soon"></div>
</section>
<?php endif; ?>

<?php if ( $va_show_agri_widget ): ?>
<section class="va-agri" id="agri-naptar-widget">
  <div class="va-agri__hd">
    <span class="va-agri__title">🌾 Agrár naptár</span>
    <span class="va-agri__sub">Vetés • Virágzás • Érés</span>
  </div>
  <div class="va-agri__list">
    <?php foreach ( $va_agri_crops_widget as $crop ) : ?>
      <?php
      $sow_txt     = $va_agri_month_labels[ $crop['sow'][0] - 1 ] . '–' . $va_agri_month_labels[ $crop['sow'][1] - 1 ];
      $bloom_txt   = $va_agri_month_labels[ $crop['bloom'][0] - 1 ] . '–' . $va_agri_month_labels[ $crop['bloom'][1] - 1 ];
      $ripen_txt   = $va_agri_month_labels[ $crop['ripen'][0] - 1 ] . '–' . $va_agri_month_labels[ $crop['ripen'][1] - 1 ];
      $harvest_txt = $va_agri_month_labels[ $crop['harvest'][0] - 1 ] . '–' . $va_agri_month_labels[ $crop['harvest'][1] - 1 ];
      ?>
      <div class="va-agri__item">
        <div class="va-agri__name"><?php echo esc_html( $crop['name'] ); ?></div>
        <div class="va-agri__row">Vetés: <?php echo esc_html( $sow_txt ); ?> <b class="va-agri__tag <?php echo $va_agri_is_in( $va_agri_month, $crop['sow'][0], $crop['sow'][1] ) ? 'is-on' : 'is-off'; ?>"><?php echo $va_agri_is_in( $va_agri_month, $crop['sow'][0], $crop['sow'][1] ) ? 'MOST' : 'nem'; ?></b></div>
        <div class="va-agri__row">Virágzás: <?php echo esc_html( $bloom_txt ); ?> <b class="va-agri__tag <?php echo $va_agri_is_in( $va_agri_month, $crop['bloom'][0], $crop['bloom'][1] ) ? 'is-on' : 'is-off'; ?>"><?php echo $va_agri_is_in( $va_agri_month, $crop['bloom'][0], $crop['bloom'][1] ) ? 'MOST' : 'nem'; ?></b></div>
        <div class="va-agri__row">Érés: <?php echo esc_html( $ripen_txt ); ?> <b class="va-agri__tag <?php echo $va_agri_is_in( $va_agri_month, $crop['ripen'][0], $crop['ripen'][1] ) ? 'is-on' : 'is-off'; ?>"><?php echo $va_agri_is_in( $va_agri_month, $crop['ripen'][0], $crop['ripen'][1] ) ? 'MOST' : 'nem'; ?></b></div>
        <div class="va-agri__row">Aratás: <?php echo esc_html( $harvest_txt ); ?> <b class="va-agri__tag <?php echo $va_agri_is_in( $va_agri_month, $crop['harvest'][0], $crop['harvest'][1] ) ? 'is-on' : 'is-off'; ?>"><?php echo $va_agri_is_in( $va_agri_month, $crop['harvest'][0], $crop['harvest'][1] ) ? 'MOST' : 'nem'; ?></b></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<style>
.va-agri{background:linear-gradient(145deg,rgba(13,26,44,.86),rgba(9,18,33,.92));border:1px solid rgba(255,255,255,.14);border-radius:12px;padding:8px;margin:0 0 10px;box-shadow:0 8px 24px rgba(0,0,0,.3),inset 0 0 18px rgba(0,170,255,.05);}
.va-agri__hd{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:6px;}
.va-agri__title{font-weight:900;font-size:.82rem;color:#fff;line-height:1.15;}
.va-agri__sub{font-size:.56rem;color:rgba(212,235,255,.8);}
.va-agri__list{display:grid;grid-template-columns:1fr;gap:5px;}
.va-agri__item{border:1px solid rgba(255,255,255,.12);border-radius:8px;background:rgba(7,12,22,.56);padding:5px 6px;}
.va-agri__name{font-size:.67rem;font-weight:800;color:#fff;margin-bottom:3px;}
.va-agri__row{font-size:.57rem;color:rgba(231,241,255,.9);display:flex;align-items:center;justify-content:space-between;gap:6px;padding:1px 0;}
.va-agri__tag{font-size:.5rem;font-weight:900;letter-spacing:.04em;border-radius:999px;padding:1px 5px;border:1px solid transparent;text-transform:uppercase;white-space:nowrap;}
.va-agri__tag.is-on{color:#72ffb0;background:rgba(0,190,95,.2);border-color:rgba(0,240,120,.45);}
.va-agri__tag.is-off{color:rgba(255,255,255,.78);background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);}
</style>
<?php endif; ?>

<!-- ═══ HOLDNAPTÁR ════════════════════════════════════════ -->
<?php if ( $va_show_moon_widget ): ?>
<section class="va-moon" id="holdnaptar">
  <div class="va-moon__hd">
    <span class="va-moon__title">🌙 Holdnaptár</span>
    <span class="va-moon__time" id="mTime">Budapest…</span>
  </div>
  <div class="va-moon__bd">
    <div class="va-moon__canvas-wrap">
      <canvas id="mCanvas" width="208" height="208"></canvas>
      <div class="va-moon__illum" id="mIllum">–</div>
    </div>
    <div class="va-moon__meter" aria-label="Hold megvilágítottság">
      <div class="va-moon__meter-fill" id="mIllumBar"></div>
    </div>
  </div>
  <div class="va-moon__info">
    <div class="va-moon__phase" id="mPhase">–</div>
    <div class="va-moon__age"   id="mAge">–</div>
    <div class="va-moon__next"  id="mNext"></div>
  </div>
</section>
  <?php endif; ?>

<?php if ( $va_show_weather_widget ): ?>
<section class="va-weather" id="va-weather">
  <div class="va-weather__hd">
    <span class="va-weather__title">⛅ Időjárás</span>
    <span class="va-weather__loc" id="vwLoc">Helyzet…</span>
  </div>
  <div class="va-weather__now" id="vwNow">Adatok betöltése…</div>
  <div class="va-weather__meta" id="vwMeta">–</div>
  <button type="button" class="va-weather__toggle" id="vwToggle" aria-expanded="false" aria-controls="vwModal">7 napos előrejelzés ✦</button>
  <div class="va-weather__modal" id="vwModal" hidden>
    <div class="va-weather__modal-backdrop" id="vwModalBackdrop"></div>
    <div class="va-weather__modal-card" role="dialog" aria-modal="true" aria-labelledby="vwModalTitle">
      <button type="button" class="va-weather__modal-close" id="vwModalClose" aria-label="Bezárás">✕</button>
      <div class="va-weather__modal-head">
        <div class="va-weather__modal-eyebrow">Részletes Meteo</div>
        <h3 class="va-weather__modal-title" id="vwModalTitle">7 napos előrejelzés</h3>
      </div>
      <div class="va-weather__modal-scroll">
        <div class="va-weather__jump" id="vwJumpNav" role="tablist" aria-label="Időjárás szekciók">
          <button type="button" class="va-weather__jump-btn is-active" id="vwJumpChart" data-target="vwSectionChart">Diagram</button>
          <button type="button" class="va-weather__jump-btn" id="vwJumpDays" data-target="vwSectionDays">7 nap</button>
          <button type="button" class="va-weather__jump-btn" id="vwJumpAgri" data-target="vwSectionAgri">Agrár</button>
        </div>
        <div class="va-weather__section va-weather__section--chart" id="vwSectionChart">
        <div class="va-weather__modal-graph">
          <canvas id="vwChart" class="va-weather__modal-chart" height="210"></canvas>
          <div class="va-weather__modal-legend">
            <span class="va-weather__legend-item"><i class="va-weather__legend-dot va-weather__legend-dot--temp"></i>Max hőmérséklet</span>
            <span class="va-weather__legend-item"><i class="va-weather__legend-dot va-weather__legend-dot--rain"></i>Csapadék (mm / %)</span>
            <span class="va-weather__legend-item"><i class="va-weather__legend-dot va-weather__legend-dot--wind"></i>Szél (km/h)</span>
          </div>
          <div class="va-weather__weekline" id="vwWeekline"></div>
        </div>
        </div>
        <div class="va-weather__section" id="vwSectionDays"><div class="va-weather__days" id="vwDays"></div></div>
        <div class="va-weather__section" id="vwSectionAgri"><div class="va-weather__agri" id="vwAgri"></div></div>
        <div class="va-weather__note" id="vwNote">Forrás: Open-Meteo (DWD/ECMWF/NCEP modellek).</div>
      </div>
      <button type="button" class="va-weather__more" id="vwMoreHint" aria-label="Görgess le">Lejjebb még több adat ⤵</button>
    </div>
  </div>
</section>

<style>
.va-weather__toggle{
  background:linear-gradient(135deg,#0b1f39 0%,#0a2749 35%,#5b102a 100%);
  border:1px solid rgba(120,190,255,.35);
  box-shadow:0 10px 28px rgba(0,130,255,.18), inset 0 0 26px rgba(255,70,70,.08);
}
.va-weather__toggle:hover{filter:brightness(1.1);transform:translateY(-1px);}
.va-weather__modal{position:fixed;inset:0;z-index:12000;display:block;}
.va-weather__modal[hidden]{display:none!important;}
.va-weather__modal-backdrop{
  position:absolute;inset:0;
  background:radial-gradient(circle at 20% 20%,rgba(0,170,255,.25),transparent 45%),radial-gradient(circle at 80% 80%,rgba(255,50,50,.23),transparent 42%),rgba(4,6,10,.8);
  backdrop-filter:blur(8px);
}
.va-weather__modal-card{
  position:relative;
  width:min(960px,calc(100vw - 28px));
  max-height:min(88vh,920px);
  margin:6vh auto;
  overflow:hidden;
  border-radius:22px;
  border:1px solid rgba(255,140,0,.65);
  background:linear-gradient(160deg,rgba(6,16,28,.96) 0%,rgba(10,22,40,.95) 45%,rgba(36,12,24,.94) 100%);
  box-shadow:0 40px 90px rgba(0,0,0,.65),0 0 60px rgba(0,120,255,.2),0 0 70px rgba(255,0,70,.12),0 0 0 1px rgba(255,165,0,.28) inset;
  padding:20px 18px 16px;
}
.va-weather__modal-scroll{
  position:relative;z-index:1;
  max-height:calc(min(88vh,920px) - 104px);
  overflow:auto;
  padding-right:2px;
  margin-right:-3px;
  border-radius:14px;
}
.va-weather__modal-scroll::-webkit-scrollbar{width:10px;}
.va-weather__modal-scroll::-webkit-scrollbar-track{background:rgba(255,255,255,.08);border-radius:999px;}
.va-weather__modal-scroll::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#ff7a00,#ff3d00);border-radius:999px;border:2px solid rgba(7,10,18,.8);}
.va-weather__modal-scroll{scrollbar-width:thin;scrollbar-color:#ff5a00 rgba(255,255,255,.08);}
.va-weather__modal-card::before{
  content:'';
  position:absolute;inset:0;pointer-events:none;border-radius:inherit;
  background:linear-gradient(120deg,rgba(0,160,255,.12),transparent 35%,rgba(255,60,60,.1));
}
.va-weather__modal-head{position:relative;z-index:1;margin-bottom:12px;padding-right:44px;}
.va-weather__modal-eyebrow{font-size:.62rem;letter-spacing:.16em;text-transform:uppercase;color:rgba(190,225,255,.85);font-weight:800;}
.va-weather__modal-title{margin:4px 0 0;font-size:1.35rem;letter-spacing:.03em;color:#fff!important;text-shadow:0 0 18px rgba(80,180,255,.35);}
.va-weather__modal-title,.va-weather__modal-title *{color:#fff!important;}
.va-weather__modal-graph{
  position:relative;z-index:1;margin:0 0 14px;padding:12px 12px 8px;
  border:1px solid rgba(255,255,255,.12);border-radius:14px;
  background:
    radial-gradient(120% 140% at 0% 0%,rgba(70,170,255,.14),transparent 60%),
    radial-gradient(120% 140% at 100% 100%,rgba(255,80,30,.12),transparent 62%),
    linear-gradient(155deg,rgba(5,25,42,.78),rgba(44,10,33,.66));
  box-shadow:inset 0 0 22px rgba(0,180,255,.08),0 0 24px rgba(255,40,90,.07),0 18px 36px rgba(0,0,0,.28);
}
.va-weather__modal-chart{display:block;width:100%;height:300px;}
.va-weather__modal-legend{display:flex;flex-wrap:wrap;gap:10px 18px;margin-top:8px;font-size:.72rem;color:#fff;}
.va-weather__legend-item{display:inline-flex;align-items:center;gap:7px;letter-spacing:.02em;}
.va-weather__legend-dot{width:11px;height:11px;border-radius:50%;display:inline-block;}
.va-weather__legend-dot--temp{background:#ffffff;box-shadow:0 0 10px rgba(255,255,255,.6);}
.va-weather__legend-dot--rain{background:#45d3ff;box-shadow:0 0 10px rgba(69,211,255,.65);}
.va-weather__legend-dot--wind{background:#ff9c2f;box-shadow:0 0 10px rgba(255,156,47,.7);}
.va-weather__weekline{display:grid;grid-template-columns:repeat(7,minmax(74px,1fr));gap:6px;margin-top:8px;}
.va-weather__weekcell{border:1px solid rgba(255,255,255,.12);border-radius:8px;background:rgba(7,13,22,.6);padding:5px 5px 4px;text-align:center;}
.va-weather__weekd{font-size:.58rem;font-weight:800;color:rgba(205,230,255,.92);letter-spacing:.05em;}
.va-weather__weekt{font-size:.7rem;font-weight:900;color:#fff;}
.va-weather__weekr{font-size:.6rem;font-weight:700;color:#7de0ff;}
.va-weather__weekw{font-size:.6rem;font-weight:700;color:#ffbe7a;}
.va-weather__modal-close{
  position:absolute;top:12px;right:12px;z-index:3;
  width:36px;height:36px;border-radius:50%;cursor:pointer;
  border:1px solid rgba(255,255,255,.28);color:#fff;
  background:rgba(255,255,255,.08);
}
.va-weather__modal-close:hover{background:rgba(255,70,70,.2);border-color:rgba(255,90,90,.55);}
.va-weather__modal .va-weather__days{display:grid;grid-template-columns:1fr 1fr;gap:10px;position:relative;z-index:1;}
.va-weather__modal .va-weather__day{
  border-radius:12px;
  border:1px solid rgba(255,255,255,.14);
  background:linear-gradient(145deg,rgba(13,26,44,.86),rgba(9,18,33,.92));
  box-shadow:0 8px 24px rgba(0,0,0,.3),inset 0 0 18px rgba(0,170,255,.05);
}
.va-weather__modal .va-weather__day:nth-child(2n){background:linear-gradient(145deg,rgba(30,16,38,.9),rgba(16,14,31,.92));box-shadow:0 8px 24px rgba(0,0,0,.3),inset 0 0 18px rgba(255,70,110,.05);}
.va-weather__modal .va-weather__d1{font-size:.78rem;letter-spacing:.04em;color:#d4ebff;}
.va-weather__modal .va-weather__d2{font-size:1.45rem;font-weight:900;color:#fff;text-shadow:0 0 16px rgba(100,190,255,.35);}
.va-weather__modal .va-weather__d3{font-size:1rem;color:#f2f8ff;}
.va-weather__modal .va-weather__d4{font-size:.72rem;color:rgba(228,238,255,.9);}
.va-weather__agri{
  margin-top:10px;
  border:1px solid rgba(255,120,0,.28);
  border-radius:12px;
  background:linear-gradient(145deg,rgba(21,28,14,.9),rgba(23,17,10,.92));
  box-shadow:inset 0 0 20px rgba(255,170,0,.07);
  padding:10px;
}
.va-weather__agri-head{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px;}
.va-weather__agri-title{font-size:.78rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#ffd8a6;}
.va-weather__agri-sub{font-size:.62rem;color:rgba(255,232,206,.85);}
.va-weather__agri-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.va-weather__agri-card{border:1px solid rgba(255,255,255,.12);border-radius:10px;background:rgba(7,12,18,.54);padding:8px;}
.va-weather__agri-crop{font-size:.78rem;font-weight:800;color:#fff;margin:0 0 6px;}
.va-weather__agri-row{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:.67rem;color:rgba(236,244,255,.92);padding:2px 0;}
.va-weather__agri-tag{font-size:.58rem;font-weight:800;letter-spacing:.05em;border-radius:999px;padding:2px 7px;white-space:nowrap;}
.va-weather__agri-tag.is-now{background:rgba(0,200,85,.18);border:1px solid rgba(0,240,100,.45);color:#72ffb0;}
.va-weather__agri-tag.is-off{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:rgba(226,238,255,.85);}
.va-weather__section{scroll-margin-top:86px;}
.va-weather__jump{position:sticky;top:0;z-index:4;display:flex;gap:8px;flex-wrap:wrap;margin:0 0 10px;padding:6px;background:linear-gradient(180deg,rgba(6,13,22,.94),rgba(6,13,22,.58));backdrop-filter:blur(3px);border:1px solid rgba(255,255,255,.08);border-radius:12px;}
.va-weather__jump-btn{border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06);color:#e8f3ff;border-radius:999px;padding:6px 10px;font-size:.67rem;font-weight:800;letter-spacing:.03em;cursor:pointer;transition:all .16s ease;}
.va-weather__jump-btn:hover{background:rgba(255,255,255,.13);transform:translateY(-1px);}
.va-weather__jump-btn.is-active{border-color:rgba(255,145,0,.65);background:linear-gradient(135deg,rgba(255,125,0,.28),rgba(255,68,0,.24));box-shadow:0 0 0 1px rgba(255,145,0,.25) inset,0 0 16px rgba(255,120,0,.18);color:#fff3dc;}
.va-weather__more{position:absolute;left:50%;bottom:10px;transform:translate(-50%,8px);opacity:0;pointer-events:none;border:1px solid rgba(255,160,40,.6);background:linear-gradient(180deg,rgba(255,115,0,.25),rgba(255,70,0,.2));color:#fff3e3;border-radius:999px;padding:6px 12px;font-size:.66rem;font-weight:800;letter-spacing:.02em;z-index:5;box-shadow:0 10px 26px rgba(0,0,0,.35);transition:all .22s ease;}
.va-weather__more.is-visible{opacity:1;pointer-events:auto;transform:translate(-50%,0);animation:vaMorePulse 1.35s ease-in-out infinite;}
@keyframes vaMorePulse{0%,100%{box-shadow:0 8px 20px rgba(0,0,0,.35),0 0 0 0 rgba(255,140,0,.34);}50%{box-shadow:0 10px 24px rgba(0,0,0,.4),0 0 0 6px rgba(255,140,0,0);}}
.va-weather__modal .va-weather__note{position:relative;z-index:1;margin-top:12px;font-size:.68rem;opacity:.92;}
body.va-weather-modal-open{overflow:hidden;}
@media (max-width:980px){
  .va-weather__modal-chart{height:320px;}
  .va-weather__modal-legend{gap:8px 12px;font-size:.68rem;}
}
@media (max-width:760px){
  .va-weather__modal-card{margin:3vh auto;padding:16px 12px 12px;border-radius:16px;}
  .va-weather__modal .va-weather__days{grid-template-columns:1fr;gap:8px;}
  .va-weather__modal-title{font-size:1.05rem;}
  .va-weather__modal-graph{padding:10px 8px 8px;}
  .va-weather__modal-chart{height:340px;}
  .va-weather__modal-legend{font-size:.64rem;gap:6px 10px;}
  .va-weather__legend-dot{width:9px;height:9px;}
  .va-weather__agri-grid{grid-template-columns:1fr;}
  .va-weather__jump{gap:6px;padding:5px;}
  .va-weather__jump-btn{font-size:.62rem;padding:6px 9px;}
}
</style>
<?php endif; ?>

<section class="va-home-radar" id="va-home-radar" data-risk="low">
  <div class="va-home-radar__ambient"></div>
  <div class="va-home-radar__scan"></div>
  <div class="va-home-radar__hd">
    <div>
      <div class="va-home-radar__eyebrow">Vadkár előrejelzés</div>
      <div class="va-home-radar__title">Vadkár Radar</div>
      <div class="va-home-radar__subtitle">Fajspecifikus éjszakai mozgás, meteorológia és zavarási nyomok egy képen.</div>
    </div>
    <div class="va-home-radar__status-stack">
      <div class="va-home-radar__status"><span></span>Live field intelligence</div>
      <div class="va-home-radar__loc" id="vhrLoc">Betöltés...</div>
    </div>
  </div>
  <div class="va-home-radar__controls">
    <label class="va-home-radar__field">
      <span>Kultúra</span>
      <select id="vhrCrop">
        <option value="maize">Kukorica</option>
        <option value="rapeseed">Repce</option>
        <option value="sunflower">Napraforgó</option>
        <option value="cereal">Kalászos</option>
        <option value="alfalfa">Lucerna</option>
        <option value="orchard">Gyümölcsös</option>
        <option value="vineyard">Szőlő</option>
      </select>
    </label>
    <label class="va-home-radar__field">
      <span>Erdőközel</span>
      <select id="vhrForest">
        <option value="under_100">0-100 m</option>
        <option value="100_300">100-300 m</option>
        <option value="300_700">300-700 m</option>
        <option value="700_1500">700-1500 m</option>
        <option value="over_1500">1500 m felett</option>
      </select>
    </label>
    <label class="va-home-radar__field">
      <span>Kárelőzmény</span>
      <select id="vhrDamage">
        <option value="7">7 napon belül</option>
        <option value="30">30 napon belül</option>
        <option value="90">90 napon belül</option>
        <option value="none">Nincs ismert kár</option>
      </select>
    </label>
  </div>
  <div class="va-home-radar__hero">
    <div class="va-home-radar__scorebox">
      <div class="va-home-radar__scorelabel">Kockázati index</div>
      <div class="va-home-radar__score" id="vhrScore">--</div>
      <div class="va-home-radar__level" id="vhrLevel">Előrejelzés készül</div>
    </div>
    <div class="va-home-radar__meta">
      <div class="va-home-radar__meta-item"><span>Fő faj</span><strong id="vhrSpecies">--</strong></div>
      <div class="va-home-radar__meta-item"><span>Időablak</span><strong id="vhrWindow">--</strong></div>
      <div class="va-home-radar__meta-item"><span>Konfidencia</span><strong id="vhrConfidence">--</strong></div>
    </div>
  </div>
  <div class="va-home-radar__intel">
    <div class="va-home-radar__intel-label">Taktikai összkép</div>
    <div class="va-home-radar__intel-line" id="vhrIntel">Élő mező- és időjárási mintaillesztés folyamatban...</div>
  </div>
  <div class="va-home-radar__reason" id="vhrReason">Meteorológia és lokáció betöltése...</div>
  <div class="va-home-radar__days" id="vhrDays"></div>
  <button type="button" class="va-home-radar__toggle" id="vhrToggle" aria-expanded="false" aria-controls="vhrModal">Részletes radar ✦</button>
  <div class="va-home-radar__modal" id="vhrModal" hidden>
    <div class="va-home-radar__modal-backdrop" id="vhrModalBackdrop"></div>
    <div class="va-home-radar__modal-card" role="dialog" aria-modal="true" aria-labelledby="vhrModalTitle">
      <button type="button" class="va-home-radar__modal-close" id="vhrModalClose" aria-label="Bezárás">✕</button>
      <div class="va-home-radar__modal-head">
        <div class="va-home-radar__modal-eyebrow">Döntéstámogatás</div>
        <h3 class="va-home-radar__modal-title" id="vhrModalTitle">Vadkár Radar részletes nézet</h3>
      </div>
      <div class="va-home-radar__modal-scroll" id="vhrModalScroll">
        <div class="va-home-radar__cinema">
          <div class="va-home-radar__stage">
            <div class="va-home-radar__threat-ring"></div>
            <div class="va-home-radar__stage-core"></div>
            <div class="va-home-radar__silhouette" id="vhrSilhouette" aria-hidden="true"></div>
          </div>
          <div class="va-home-radar__cinema-meta">
            <div class="va-home-radar__signal-card"><span>Fő célfaj</span><strong id="vhrCinemaSpecies">--</strong></div>
            <div class="va-home-radar__signal-card"><span>Kockázati pulzus</span><strong id="vhrThreatPulse">--</strong></div>
            <div class="va-home-radar__signal-card"><span>Mintázat</span><strong id="vhrPattern">--</strong></div>
          </div>
        </div>
        <div class="va-home-radar__detail-grid">
          <label class="va-home-radar__field">
            <span>Fenológia</span>
            <select id="vhrPhenology"></select>
          </label>
          <label class="va-home-radar__field">
            <span>Vadásznyomás</span>
            <select id="vhrHunting">
              <option value="low">Alacsony</option>
              <option value="medium">Közepes</option>
              <option value="high">Magas</option>
            </select>
          </label>
          <label class="va-home-radar__field">
            <span>Villanypásztor</span>
            <select id="vhrFence">
              <option value="0">Nincs</option>
              <option value="1">Van, jó állapotú</option>
            </select>
          </label>
          <label class="va-home-radar__field">
            <span>Erdei táplálék</span>
            <select id="vhrForestFood">
              <option value="normal">Átlagos</option>
              <option value="abundant">Bő (makk, gyümölcs)</option>
              <option value="scarce">Szegényes</option>
            </select>
          </label>
          <label class="va-home-radar__field">
            <span>Vizes élőhely</span>
            <select id="vhrWater">
              <option value="0">Nincs 500 m-en belül</option>
              <option value="1">Van (tó, patak, mocsár)</option>
            </select>
          </label>
          <label class="va-home-radar__field">
            <span>Csordaméret</span>
            <select id="vhrHerd">
              <option value="small">Kis (1–3 egyed)</option>
              <option value="medium">Közepes (4–10)</option>
              <option value="large">Nagy (10+)</option>
            </select>
          </label>
        </div>
        <div class="va-home-radar__modal-summary">
          <div class="va-home-radar__modal-scorewrap">
            <div class="va-home-radar__modal-score" id="vhrModalScore">--</div>
            <div class="va-home-radar__modal-level" id="vhrModalLevel">Előrejelzés készül</div>
          </div>
          <div class="va-home-radar__modal-meta">
            <div class="va-home-radar__meta-item"><span>Fő faj</span><strong id="vhrModalSpecies">--</strong></div>
            <div class="va-home-radar__meta-item"><span>Kritikus idő</span><strong id="vhrModalWindow">--</strong></div>
            <div class="va-home-radar__meta-item"><span>Konfidencia</span><strong id="vhrModalConfidence">--</strong></div>
          </div>
        </div>
        <div class="va-home-radar__detail-block">
          <div class="va-home-radar__detail-title">Fő kockázati okok</div>
          <ul class="va-home-radar__factors" id="vhrFactors"><li>Adatok betöltése...</li></ul>
        </div>
        <div class="va-home-radar__detail-block">
          <div class="va-home-radar__detail-title">7 napos trend</div>
          <div class="va-home-radar__week" id="vhrWeek"></div>
        </div>
        <div class="va-home-radar__detail-block">
          <div class="va-home-radar__detail-title">Parancsnoki heti heatmap</div>
          <div class="va-home-radar__minihelp">A számok kockázati pontok (0-100), nem százalékok. Minden oszlop konkrét naptári napot jelöl.</div>
          <div class="va-home-radar__heatmap" id="vhrHeatmap"></div>
        </div>
        <div class="va-home-radar__note" id="vhrNote">A modell meteorológiai, holdfény- és viselkedési triggerekből számol. Teljes validációhoz valós káresemény és táblaadat szükséges.</div>
      </div>
    </div>
  </div>
  <div class="va-home-radar__picker" id="vhrPicker" hidden>
    <div class="va-home-radar__picker-backdrop" id="vhrPickerBackdrop"></div>
    <div class="va-home-radar__picker-card" role="dialog" aria-modal="true" aria-labelledby="vhrPickerTitle">
      <button type="button" class="va-home-radar__picker-close" id="vhrPickerClose" aria-label="Bezárás">✕</button>
      <div class="va-home-radar__picker-eyebrow">Választó</div>
      <h4 class="va-home-radar__picker-title" id="vhrPickerTitle">Mező kiválasztása</h4>
      <div class="va-home-radar__picker-options" id="vhrPickerOptions"></div>
    </div>
  </div>
</section>

<style>
.va-home-radar{--radar-accent:#ff3b2f;--radar-accent-soft:rgba(255,59,47,.22);--radar-glow:rgba(255,78,52,.28);position:relative;isolation:isolate;overflow:hidden;margin:0 0 14px;padding:14px;border-radius:24px;border:1px solid rgba(255,70,70,.26);background:linear-gradient(135deg,rgba(6,6,6,.98),rgba(14,10,10,.98) 42%,rgba(28,13,10,.97) 100%);box-shadow:0 30px 80px rgba(0,0,0,.48),inset 0 0 0 1px rgba(255,255,255,.03),inset 0 0 42px rgba(255,70,70,.05);color:#fff;}
.va-home-radar::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.08) 1px,transparent 0);background-size:18px 18px;opacity:.14;pointer-events:none;}
.va-home-radar::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.06),transparent 18%,transparent 82%,rgba(255,74,74,.08));pointer-events:none;}
.va-home-radar[data-risk="moderate"]{--radar-accent:#d0a720;--radar-accent-soft:rgba(208,167,32,.22);--radar-glow:rgba(208,167,32,.24);}
.va-home-radar[data-risk="high"]{--radar-accent:#ff8a2b;--radar-accent-soft:rgba(255,138,43,.22);--radar-glow:rgba(255,138,43,.24);}
.va-home-radar[data-risk="very-high"]{--radar-accent:#ff5b2f;--radar-accent-soft:rgba(255,91,47,.24);--radar-glow:rgba(255,91,47,.28);}
.va-home-radar[data-risk="critical"]{--radar-accent:#ff1f3d;--radar-accent-soft:rgba(255,31,61,.26);--radar-glow:rgba(255,31,61,.34);}
.va-home-radar__ambient{position:absolute;inset:-20% -10% auto auto;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(255,90,60,.22),transparent 68%);filter:blur(6px);pointer-events:none;z-index:0;}
.va-home-radar__scan{position:absolute;inset:0;pointer-events:none;z-index:0;opacity:.32;background:linear-gradient(180deg,transparent 0,rgba(255,255,255,.035) 48%,transparent 100%);animation:vaRadarSweep 5.2s linear infinite;}
@keyframes vaRadarSweep{0%{transform:translateY(-105%);}100%{transform:translateY(105%);}}
.va-home-radar > *{position:relative;z-index:1;}
.va-home-radar__hd{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin-bottom:12px;flex-wrap:wrap;}
.va-home-radar__eyebrow{font-size:.58rem;font-weight:900;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,220,220,.76);}
.va-home-radar__title{font-size:1.28rem;font-weight:900;line-height:1.05;color:#fff;text-shadow:0 0 22px rgba(255,255,255,.06);}
.va-home-radar__subtitle{max-width:420px;margin-top:6px;font-size:.68rem;line-height:1.45;color:rgba(255,240,240,.76);}
.va-home-radar__status-stack{display:grid;gap:8px;justify-items:end;max-width:100%;}
.va-home-radar__status{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);font-size:.58rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:#fff0f0;box-shadow:0 0 0 1px rgba(255,255,255,.03) inset;max-width:100%;white-space:normal;line-height:1.25;}
.va-home-radar__status span{width:8px;height:8px;border-radius:50%;background:var(--radar-accent);box-shadow:0 0 0 5px var(--radar-accent-soft),0 0 18px var(--radar-glow);animation:vaRadarPulse 1.8s ease-in-out infinite;}
@keyframes vaRadarPulse{0%,100%{transform:scale(1);opacity:1;}50%{transform:scale(.78);opacity:.74;}}
.va-home-radar__loc{font-size:.58rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#fff;border-radius:999px;padding:6px 10px;background:linear-gradient(135deg,rgba(255,255,255,.08),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.14);white-space:normal;max-width:280px;overflow:hidden;text-overflow:ellipsis;line-height:1.25;}
.va-home-radar__controls{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:9px;margin-bottom:12px;}
.va-home-radar__field{display:flex;flex-direction:column;gap:5px;}
.va-home-radar__field span{font-size:.56rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase;color:rgba(255,224,224,.82);line-height:1.25;word-break:break-word;}
.va-home-radar__field select{display:none;}
.va-home-radar__picker-btn{width:100%;min-height:48px;border-radius:14px;border:1px solid rgba(255,255,255,.12);background:linear-gradient(180deg,rgba(255,255,255,.09),rgba(255,255,255,.04));color:#fff;padding:12px 34px 12px 12px;font-size:.74rem;font-weight:800;text-align:left;cursor:pointer;position:relative;box-shadow:0 12px 28px rgba(0,0,0,.24),inset 0 0 0 1px rgba(255,255,255,.025);transition:transform .16s ease,border-color .16s ease,background .16s ease,box-shadow .16s ease;}
.va-home-radar__picker-btn::after{content:'+';position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:1.05rem;line-height:1;color:#fff;opacity:.95;}
.va-home-radar__picker-btn:hover{transform:translateY(-1px);border-color:rgba(255,255,255,.22);background:linear-gradient(180deg,rgba(255,255,255,.12),rgba(255,255,255,.05));box-shadow:0 16px 30px rgba(0,0,0,.28),0 0 0 1px rgba(255,255,255,.03) inset;}
.va-home-radar__picker-btn span{display:block;color:#fff;white-space:normal;overflow:hidden;text-overflow:ellipsis;line-height:1.2;word-break:break-word;}
.va-home-radar__hero{display:grid;grid-template-columns:112px 1fr;gap:12px;align-items:stretch;margin-bottom:12px;}
.va-home-radar__scorebox{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.12);border-radius:22px;padding:14px 10px;background:linear-gradient(180deg,rgba(255,255,255,.09),rgba(255,255,255,.03));box-shadow:inset 0 0 0 1px rgba(255,255,255,.025),0 18px 36px rgba(0,0,0,.26);}
.va-home-radar__scorebox::before{content:'';position:absolute;inset:9px;border-radius:18px;border:1px solid var(--radar-accent-soft);pointer-events:none;}
.va-home-radar__scorelabel{font-size:.54rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase;color:rgba(255,228,228,.7);}
.va-home-radar__score{margin-top:5px;font-size:2.45rem;font-weight:900;line-height:1;color:#fff;text-shadow:0 0 24px var(--radar-glow);}
.va-home-radar__level{margin-top:7px;font-size:.58rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;text-align:center;color:#fff;}
.va-home-radar__meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px;}
.va-home-radar__meta-item{border:1px solid rgba(255,255,255,.1);border-radius:15px;background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.03));padding:10px 10px 9px;box-shadow:inset 0 0 0 1px rgba(255,255,255,.02);}
.va-home-radar__meta-item span{display:block;font-size:.52rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,225,225,.68);margin-bottom:5px;}
.va-home-radar__meta-item strong{display:block;font-size:.78rem;color:#fff;line-height:1.35;}
.va-home-radar__meta-item strong,.va-home-radar__signal-card strong,.va-home-radar__dayspecies,.va-home-radar__heatmap-label strong,.va-home-radar__heatmap-label small{overflow-wrap:anywhere;word-break:break-word;}
.va-home-radar__intel{margin-bottom:10px;padding:10px 12px;border-radius:16px;border:1px solid rgba(255,255,255,.09);background:linear-gradient(135deg,var(--radar-accent-soft),rgba(255,255,255,.04) 45%,rgba(255,255,255,.03));box-shadow:0 16px 28px rgba(0,0,0,.22);}
.va-home-radar__intel-label{font-size:.52rem;font-weight:900;letter-spacing:.18em;text-transform:uppercase;color:#ffe3e3;margin-bottom:4px;}
.va-home-radar__intel-line{font-size:.74rem;line-height:1.45;color:#fff;}
.va-home-radar__reason{font-size:.7rem;line-height:1.55;color:rgba(255,255,255,.92);padding:11px 12px;border-radius:16px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);box-shadow:inset 0 0 0 1px rgba(255,255,255,.025);}
.va-home-radar__days{display:grid;grid-template-columns:repeat(auto-fit,minmax(92px,1fr));gap:8px;margin-top:10px;}
.va-home-radar__day{position:relative;overflow:hidden;border-radius:16px;padding:10px 8px 9px;border:1px solid rgba(255,255,255,.1);text-align:center;background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.03));box-shadow:0 12px 24px rgba(0,0,0,.18);}
.va-home-radar__day::before{content:'';position:absolute;inset:auto 0 0 0;height:3px;background:var(--radar-accent);opacity:.9;}
.va-home-radar__dayname{font-size:.54rem;font-weight:900;letter-spacing:.12em;color:#fff;opacity:.84;text-transform:uppercase;}
.va-home-radar__dayscore{font-size:1rem;font-weight:900;color:#fff;margin-top:5px;}
.va-home-radar__dayscore small{font-size:.58rem;opacity:.82;margin-left:2px;}
.va-home-radar__daydate{display:block;margin-top:4px;font-size:.52rem;letter-spacing:.03em;color:rgba(255,255,255,.72);}
.va-home-radar__dayspecies{font-size:.55rem;color:rgba(255,255,255,.76);margin-top:4px;letter-spacing:.08em;}
.va-home-radar__toggle{width:100%;margin-top:12px;border-radius:16px;border:1px solid rgba(255,255,255,.1);background:linear-gradient(135deg,var(--radar-accent-soft),rgba(64,14,14,.92) 38%,rgba(255,60,40,.35) 100%);color:#fff;padding:12px 14px;font-size:.76rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;box-shadow:0 18px 36px rgba(0,0,0,.26),0 0 40px var(--radar-glow) inset;transition:transform .16s ease,filter .16s ease,border-color .16s ease;}
.va-home-radar__toggle:hover{filter:brightness(1.08);transform:translateY(-1px);border-color:rgba(255,255,255,.2);}
.va-home-radar__modal{position:fixed;inset:0;z-index:120200;display:block;padding:calc(var(--nav,66px) + 10px) 0 14px;overflow:auto;}
.admin-bar .va-home-radar__modal{padding-top:calc(var(--nav,66px) + 42px);}
@media screen and (max-width:782px){.admin-bar .va-home-radar__modal{padding-top:calc(var(--nav,66px) + 56px);}}
.va-home-radar__modal[hidden]{display:none !important;}
.va-home-radar__modal-backdrop{position:absolute;inset:0;background:radial-gradient(circle at 20% 16%,var(--radar-accent-soft),transparent 34%),radial-gradient(circle at 80% 84%,rgba(255,205,110,.12),transparent 38%),rgba(4,6,10,.88);backdrop-filter:blur(10px);}
.va-home-radar__modal-card{position:relative;width:min(980px,calc(100vw - 28px));max-height:calc(100dvh - var(--nav,66px) - 28px);margin:0 auto;overflow:hidden;border-radius:28px;border:1px solid rgba(255,255,255,.12);background:linear-gradient(160deg,rgba(5,6,8,.985) 0%,rgba(15,11,12,.98) 45%,rgba(30,12,10,.97) 100%);box-shadow:0 50px 110px rgba(0,0,0,.72),0 0 0 1px rgba(255,255,255,.03) inset,0 0 70px var(--radar-glow);padding:22px 20px 18px;}
.va-home-radar__modal-card::before{content:'';position:absolute;inset:0;border-radius:inherit;pointer-events:none;background:linear-gradient(120deg,rgba(255,255,255,.05),transparent 18%,transparent 78%,rgba(255,80,80,.08));}
.va-home-radar__modal-close,.va-home-radar__picker-close{position:absolute;top:12px;right:12px;z-index:3;width:38px;height:38px;border-radius:50%;cursor:pointer;border:1px solid rgba(255,255,255,.18);color:#fff;background:rgba(255,255,255,.06);box-shadow:0 10px 18px rgba(0,0,0,.22);}
.va-home-radar__modal-close:hover,.va-home-radar__picker-close:hover{background:rgba(255,85,85,.18);border-color:rgba(255,125,125,.42);}
.va-home-radar__modal-head{position:relative;z-index:1;margin-bottom:14px;padding-right:48px;}
.va-home-radar__modal-eyebrow,.va-home-radar__picker-eyebrow{font-size:.62rem;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,226,226,.82);font-weight:900;}
.va-home-radar__modal-title{margin:6px 0 0;font-size:1.5rem;letter-spacing:.02em;color:#fff;}
.va-home-radar__modal-eyebrow{color:#fff !important;}
.va-home-radar__modal-title,.va-home-radar__modal-title *{color:#fff !important;}
.va-home-radar__modal-scroll{position:relative;z-index:1;max-height:calc(100dvh - var(--nav,66px) - 136px);overflow:auto;padding-right:4px;margin-right:-4px;border-radius:18px;}
.va-home-radar__cinema{display:grid;grid-template-columns:240px 1fr;gap:14px;margin-bottom:14px;align-items:stretch;}
.va-home-radar__stage{position:relative;min-height:240px;border-radius:24px;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:radial-gradient(circle at 50% 50%,rgba(255,255,255,.08),rgba(255,255,255,.02) 30%,rgba(0,0,0,.2) 58%,rgba(0,0,0,.65) 100%),linear-gradient(160deg,rgba(18,10,10,.95),rgba(8,8,10,.98));box-shadow:inset 0 0 0 1px rgba(255,255,255,.025),0 24px 40px rgba(0,0,0,.34);}
.va-home-radar__stage::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.07) 1px,transparent 0);background-size:20px 20px;opacity:.16;pointer-events:none;}
.va-home-radar__threat-ring{position:absolute;inset:24px;border-radius:50%;border:1px solid var(--radar-accent-soft);box-shadow:0 0 0 18px rgba(255,255,255,.015),0 0 50px var(--radar-glow),inset 0 0 50px rgba(255,255,255,.025);animation:vaThreatRing 2.8s ease-in-out infinite;}
.va-home-radar__threat-ring::before,.va-home-radar__threat-ring::after{content:'';position:absolute;inset:14%;border-radius:50%;border:1px dashed rgba(255,255,255,.12);}
.va-home-radar__threat-ring::after{inset:28%;border-style:solid;border-color:var(--radar-accent-soft);opacity:.8;}
@keyframes vaThreatRing{0%,100%{transform:scale(1);opacity:.9;}50%{transform:scale(1.03);opacity:1;}}
.va-home-radar__stage-core{position:absolute;inset:50% auto auto 50%;width:18px;height:18px;border-radius:50%;transform:translate(-50%,-50%);background:var(--radar-accent);box-shadow:0 0 0 10px var(--radar-accent-soft),0 0 30px var(--radar-glow);animation:vaStageCore 1.9s ease-in-out infinite;}
@keyframes vaStageCore{0%,100%{box-shadow:0 0 0 10px var(--radar-accent-soft),0 0 30px var(--radar-glow);}50%{box-shadow:0 0 0 18px rgba(255,255,255,.03),0 0 44px var(--radar-glow);}}
.va-home-radar__silhouette{position:absolute;inset:14px;color:#fff;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 18px 24px rgba(0,0,0,.42));}
.va-home-radar__silhouette svg{width:86%;height:86%;display:block;opacity:.96;transform-origin:center;animation:vaSilhouetteFloat 5.6s ease-in-out infinite;}
@keyframes vaSilhouetteFloat{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-4px) scale(1.015);}}
.va-home-radar__cinema-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:10px;align-content:start;}
.va-home-radar__signal-card{padding:14px 14px;border-radius:18px;border:1px solid rgba(255,255,255,.09);background:linear-gradient(180deg,rgba(255,255,255,.065),rgba(255,255,255,.025));box-shadow:inset 0 0 0 1px rgba(255,255,255,.02);min-height:92px;display:flex;flex-direction:column;justify-content:space-between;}
.va-home-radar__signal-card span{font-size:.54rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase;color:rgba(255,225,225,.66);}
.va-home-radar__signal-card strong{font-size:.82rem;line-height:1.42;color:#fff;}
.va-home-radar__detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:10px;margin-bottom:14px;}
.va-home-radar__modal-summary{display:grid;grid-template-columns:140px 1fr;gap:14px;align-items:stretch;margin-bottom:14px;}
.va-home-radar__modal-scorewrap{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:16px 10px;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.03));}
.va-home-radar__modal-scorewrap::before{content:'';position:absolute;inset:10px;border-radius:18px;border:1px solid var(--radar-accent-soft);pointer-events:none;}
.va-home-radar__modal-score{font-size:2.85rem;line-height:1;font-weight:900;color:#fff;text-shadow:0 0 24px var(--radar-glow);}
.va-home-radar__modal-score small{font-size:.52em;opacity:.8;margin-left:4px;}
.va-home-radar__modal-level{margin-top:7px;font-size:.6rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;text-align:center;color:#fff;}
.va-home-radar__modal-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:10px;}
.va-home-radar__detail-block{margin-top:14px;padding:14px;border-radius:20px;border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.025));}
.va-home-radar__detail-title{font-size:.62rem;font-weight:900;letter-spacing:.2em;text-transform:uppercase;color:#ffd7d7;margin-bottom:10px;}
.va-home-radar__minihelp{margin:-2px 0 10px;font-size:.66rem;line-height:1.45;color:rgba(255,235,235,.78);}
.va-home-radar__factors{margin:0;padding-left:18px;display:grid;gap:8px;}
.va-home-radar__factors li{font-size:.73rem;line-height:1.5;color:rgba(255,255,255,.9);}
.va-home-radar__week{display:grid;grid-template-columns:repeat(auto-fit,minmax(72px,1fr));gap:8px;}
.va-home-radar__heatmap{display:grid;gap:8px;}
.va-home-radar__heatmap-head,.va-home-radar__heatmap-row{display:grid;grid-template-columns:112px repeat(7,minmax(0,1fr));gap:6px;align-items:center;}
.va-home-radar__heatmap-corner,.va-home-radar__heatmap-day{font-size:.55rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,224,224,.64);text-align:center;padding:4px 2px;}
.va-home-radar__heatmap-dow{display:block;}
.va-home-radar__heatmap-date{display:block;margin-top:3px;font-size:.5rem;letter-spacing:.02em;color:rgba(255,255,255,.74);text-transform:none;}
.va-home-radar__heatmap-label{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);font-size:.67rem;font-weight:800;color:#fff;}
.va-home-radar__heatmap-label small{font-size:.52rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,224,224,.6);}
.va-home-radar__heatmap-cell{position:relative;min-height:56px;border-radius:14px;border:1px solid rgba(255,255,255,.08);padding:6px 4px;background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02));display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;}
.va-home-radar__heatmap-cell::before{content:'';position:absolute;inset:auto 0 0 0;height:3px;background:var(--heat, rgba(255,255,255,.15));opacity:.95;}
.va-home-radar__heatmap-cell strong{position:relative;z-index:1;font-size:.9rem;line-height:1;font-weight:900;color:#fff;}
.va-home-radar__heatmap-cell span{position:relative;z-index:1;font-size:.48rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.72);margin-top:4px;}
.va-home-radar__day,.va-home-radar__heatmap-label,.va-home-radar__heatmap-cell{cursor:default;}
.va-home-radar__heatmap-cell.is-low{background:linear-gradient(180deg,rgba(18,110,62,.2),rgba(10,42,23,.48));}
.va-home-radar__heatmap-cell.is-moderate{background:linear-gradient(180deg,rgba(150,128,18,.2),rgba(66,52,10,.48));}
.va-home-radar__heatmap-cell.is-high{background:linear-gradient(180deg,rgba(175,95,18,.22),rgba(66,34,10,.52));}
.va-home-radar__heatmap-cell.is-very-high{background:linear-gradient(180deg,rgba(168,45,22,.24),rgba(70,18,10,.56));}
.va-home-radar__heatmap-cell.is-critical{background:linear-gradient(180deg,rgba(120,18,34,.3),rgba(56,8,20,.68));}
.va-home-radar__note{margin-top:14px;padding:12px 14px;border-radius:16px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);font-size:.68rem;line-height:1.6;color:rgba(255,255,255,.72);}
body.va-home-radar-modal-open{overflow:hidden;}
.va-home-radar__picker{position:fixed;inset:0;z-index:120260;display:block;}
.va-home-radar__picker[hidden]{display:none !important;}
.va-home-radar__picker-backdrop{position:absolute;inset:0;background:radial-gradient(circle at 50% 10%,var(--radar-accent-soft),transparent 36%),rgba(4,6,10,.88);backdrop-filter:blur(8px);}
.va-home-radar__picker-card{position:relative;width:min(520px,calc(100vw - 28px));max-height:min(80vh,760px);margin:9vh auto 0;border-radius:26px;border:1px solid rgba(255,255,255,.12);background:linear-gradient(160deg,rgba(8,8,10,.99),rgba(22,12,12,.97));box-shadow:0 36px 90px rgba(0,0,0,.62),0 0 60px var(--radar-glow);padding:18px 16px 16px;overflow:auto;}
.va-home-radar__picker-title{margin:8px 0 14px;font-size:1.08rem;color:#fff;}
.va-home-radar__picker-options{display:grid;gap:10px;max-height:min(62vh,520px);overflow:auto;padding-right:2px;}
.va-home-radar__picker-option{border-radius:14px;border:1px solid rgba(255,255,255,.1);background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.03));color:#fff;padding:14px 14px;font-size:.82rem;font-weight:900;text-align:left;cursor:pointer;transition:transform .16s ease,border-color .16s ease,background .16s ease;}
.va-home-radar__picker-option:hover,.va-home-radar__picker-option.is-active{transform:translateY(-1px);background:linear-gradient(135deg,var(--radar-accent-soft),rgba(255,255,255,.06));border-color:rgba(255,255,255,.18);}
body.va-home-radar-picker-open{overflow:hidden;}
.va-home-radar__day.is-low{background:linear-gradient(180deg,rgba(18,110,62,.28),rgba(10,42,23,.5));border-color:rgba(70,210,115,.35);}
.va-home-radar__day.is-moderate{background:linear-gradient(180deg,rgba(150,128,18,.24),rgba(66,52,10,.52));border-color:rgba(225,190,70,.4);}
.va-home-radar__day.is-high{background:linear-gradient(180deg,rgba(175,95,18,.24),rgba(66,34,10,.56));border-color:rgba(255,160,65,.42);}
.va-home-radar__day.is-very-high{background:linear-gradient(180deg,rgba(168,45,22,.26),rgba(70,18,10,.6));border-color:rgba(255,95,75,.48);}
.va-home-radar__day.is-critical{background:linear-gradient(180deg,rgba(120,18,34,.34),rgba(56,8,20,.72));border-color:rgba(255,70,95,.55);}
/* Modal safety override: keep radar detail content fully inside viewport (top and bottom). */
.va-home-radar__modal{--vhr-modal-top:max(calc(var(--nav,66px) + 16px),82px);--vhr-modal-bottom:14px;padding:var(--vhr-modal-top) 0 var(--vhr-modal-bottom) !important;}
.admin-bar .va-home-radar__modal{--vhr-modal-top:max(calc(var(--nav,66px) + 52px),124px);}
.va-home-radar__modal-card{margin:0 auto !important;max-height:calc(100dvh - var(--vhr-modal-top) - var(--vhr-modal-bottom)) !important;}
.va-home-radar__modal-card{display:flex;flex-direction:column;}
.va-home-radar__modal-scroll{flex:1 1 auto;min-height:0;max-height:none !important;overflow-y:auto !important;padding-bottom:22px;}
@media (max-width:760px){.va-home-radar{padding:12px;}.va-home-radar__hd,.va-home-radar__modal-summary,.va-home-radar__hero,.va-home-radar__cinema{grid-template-columns:1fr;display:grid;}.va-home-radar__status-stack{justify-items:start;}.va-home-radar__modal{--vhr-modal-top:calc(var(--nav,66px) + 8px);--vhr-modal-bottom:10px;}.va-home-radar__modal-card{margin:0 auto;padding:18px 14px 14px;border-radius:20px;}.va-home-radar__modal-title{font-size:1.14rem;}.va-home-radar__heatmap-head,.va-home-radar__heatmap-row{grid-template-columns:86px repeat(7,minmax(0,1fr));}}
@media (max-width:640px){.va-home-radar{padding:11px;border-radius:20px;}.va-home-radar__subtitle{max-width:none;}.va-home-radar__loc{max-width:100%;}.va-home-radar__picker-card{margin:6vh auto 0;}.va-home-radar__heatmap-head,.va-home-radar__heatmap-row{grid-template-columns:1fr;}.va-home-radar__heatmap-corner{display:none;}.va-home-radar__heatmap-day{text-align:left;padding-left:10px;}.va-home-radar__heatmap-label{margin-top:8px;}.va-home-radar__picker-btn{padding:11px 30px 11px 11px;font-size:.72rem;}.va-home-radar__field span{font-size:.54rem;letter-spacing:.07em;}.va-home-radar__status,.va-home-radar__loc{font-size:.55rem;}}
</style>

</aside>

</aside>
<?php if ( $va_show_moon_widget ): ?>
<script>
(function(){
  var PI=Math.PI,sin=Math.sin,cos=Math.cos,tan=Math.tan,
      asin=Math.asin,atan=Math.atan2,acos=Math.acos,rad=PI/180,
      dayMs=86400000,J1970=2440588,J2000=2451545,e=rad*23.4397;
  function toDays(d){return d.valueOf()/dayMs-.5+J1970-J2000;}
  function ra(l,b){return atan(sin(l)*cos(e)-tan(b)*sin(e),cos(l));}
  function dec(l,b){return asin(sin(b)*cos(e)+cos(b)*sin(e)*sin(l));}
  function sunC(d){
    var M=rad*(357.5291+0.98560028*d),
        C=rad*(1.9148*sin(M)+0.02*sin(2*M)+0.0003*sin(3*M)),
        L=M+C+rad*102.9372+PI;
    return{dec:dec(L,0),ra:ra(L,0)};
  }
  function moonC(d){
    var L=rad*(218.316+13.176396*d),M=rad*(134.963+13.064993*d),
        F=rad*(93.272+13.22935*d),
        l=L+rad*6.289*sin(M),b=rad*5.128*sin(F),dt=385001-20905*cos(M);
    return{ra:ra(l,b),dec:dec(l,b),dist:dt};
  }
  function moonIllum(date){
    var d=toDays(date),s=sunC(d),m=moonC(d),sd=149598000,
        phi=acos(Math.max(-1,Math.min(1,sin(s.dec)*sin(m.dec)+cos(s.dec)*cos(m.dec)*cos(s.ra-m.ra)))),
        inc=atan(sd*sin(phi),m.dist-sd*cos(phi)),
        ang=atan(cos(s.dec)*sin(s.ra-m.ra),sin(s.dec)*cos(m.dec)-cos(s.dec)*sin(m.dec)*cos(s.ra-m.ra));
    return{frac:(1+cos(inc))/2,phase:.5+.5*inc*(ang<0?-1:1)/PI};
  }
  var SYN=29.53058867;
  function nextPhase(now,target){
    var p=moonIllum(now).phase,days=((target-p+1)%1)*SYN;
    if(days<0.5)days+=SYN;
    return new Date(now.getTime()+days*dayMs);
  }
  function rndHash(x,y){
    var s=sin(x*127.1+y*311.7)*43758.5453;
    return s-Math.floor(s);
  }
  function smooth(v){return v*v*(3-2*v);}
  function n2(x,y){
    var x0=Math.floor(x),y0=Math.floor(y),x1=x0+1,y1=y0+1;
    var sx=smooth(x-x0),sy=smooth(y-y0);
    var n00=rndHash(x0,y0),n10=rndHash(x1,y0),n01=rndHash(x0,y1),n11=rndHash(x1,y1);
    var nx0=n00+(n10-n00)*sx,nx1=n01+(n11-n01)*sx;
    return nx0+(nx1-nx0)*sy;
  }
  function fbm(x,y){
    var v=0,a=.55,f=1,i;
    for(i=0;i<5;i++){v+=a*n2(x*f,y*f);f*=2;a*=.5;}
    return v;
  }
  var MOON_PHOTO_URL='https://upload.wikimedia.org/wikipedia/commons/e/e1/FullMoon2010.jpg';
  var moonPhoto=new Image(),moonPhotoReady=false;
  moonPhoto.crossOrigin='anonymous';
  moonPhoto.onload=function(){moonPhotoReady=true;update();};
  moonPhoto.onerror=function(){moonPhotoReady=false;};
  moonPhoto.src=MOON_PHOTO_URL;
  function budapestHour(date){
    return Number(new Intl.DateTimeFormat('en-GB',{
      timeZone:'Europe/Budapest',hour:'2-digit',hour12:false
    }).format(date));
  }
  function moonTone(date,frac){
    var hour=budapestHour(date||new Date());
    var tone={
      filter:'contrast(1.08) brightness(1.03) saturate(1.1)',
      overlay:'rgba(245,242,232,.05)',
      glowCore:'rgba(210,215,190,ALPHA)',
      rim:'rgba(255,250,238,'+(0.32+frac*.34)+')',
      rimGlow:'rgba(255,245,225,'+(0.16+frac*.14)+')'
    };
    if(frac>.97){
      if((hour>=5&&hour<6)||(hour>=20&&hour<21)){
        tone.filter='contrast(1.14) brightness(1.02) saturate(1.55) sepia(.32)';
        tone.overlay='rgba(255,110,78,.22)';
        tone.glowCore='rgba(255,116,70,ALPHA)';
        tone.rim='rgba(255,185,155,'+(0.4+frac*.28)+')';
        tone.rimGlow='rgba(255,120,85,'+(0.18+frac*.16)+')';
      }else if((hour>=6&&hour<8)||(hour>=18&&hour<20)){
        tone.filter='contrast(1.12) brightness(1.04) saturate(1.42) sepia(.18)';
        tone.overlay='rgba(255,208,120,.18)';
        tone.glowCore='rgba(255,214,120,ALPHA)';
        tone.rim='rgba(255,232,180,'+(0.38+frac*.26)+')';
        tone.rimGlow='rgba(255,210,135,'+(0.16+frac*.14)+')';
      }else{
        tone.filter='contrast(1.1) brightness(1.04) saturate(1.22)';
        tone.overlay='rgba(255,244,200,.1)';
        tone.glowCore='rgba(255,238,185,ALPHA)';
      }
    }else if(frac>.55){
      tone.filter='contrast(1.09) brightness(1.03) saturate(1.16)';
      tone.overlay='rgba(245,236,208,.06)';
    }
    return tone;
  }
  var texCache={};
  function moonTexture(size){
    if(texCache[size])return texCache[size];
    var t=document.createElement('canvas'),ctx=t.getContext('2d');
    t.width=size;t.height=size;
    var d=ctx.createImageData(size,size),p=d.data;
    var r=size*.5,cx=r,cy=r;
    var mare=[[-.18,-.1,.34,.27,-.2], [.15,-.21,.2,.16,0], [.22,-.01,.21,.16,.12], [.42,-.24,.1,.09,0], [.08,.3,.18,.13,.08]];
    var cr=[[-.31,.13,.1],[-.18,-.31,.06],[.3,-.12,.075],[.2,.3,.08],[-.05,.28,.06],[.04,-.02,.045],[-.24,.33,.05],[.39,.05,.05],[-.38,-.02,.055],[.02,.42,.05],[.27,-.35,.045],[-.06,-.23,.04]];
    var x,y,i,j;
    for(y=0;y<size;y++){
      for(x=0;x<size;x++){
        var dx=(x-cx)/r,dy=(y-cy)/r,rr=dx*dx+dy*dy;
        var idx=(y*size+x)*4;
        if(rr>1){p[idx+3]=0;continue;}
        var limb=1-.38*rr;
        var base=168+26*limb;
        var grain=(fbm((dx+1.2)*5.4,(dy+1.1)*5.4)-.5)*28+(fbm((dx+2.6)*16.2,(dy+2.1)*16.2)-.5)*11;
        var val=base+grain;
        for(i=0;i<mare.length;i++){
          var m=mare[i];
          var rx=(dx-m[0])*cos(m[4])-(dy-m[1])*sin(m[4]);
          var ry=(dx-m[0])*sin(m[4])+(dy-m[1])*cos(m[4]);
          var g=Math.exp(-(rx*rx/(2*m[2]*m[2])+ry*ry/(2*m[3]*m[3])));
          val-=g*30;
        }
        for(j=0;j<cr.length;j++){
          var c=cr[j],cdx=dx-c[0],cdy=dy-c[1],dist=Math.sqrt(cdx*cdx+cdy*cdy),radc=c[2];
          if(dist<radc*1.55){
            var rim=Math.exp(-Math.pow((dist-radc)/(radc*.18),2));
            var pit=Math.exp(-Math.pow(dist/(radc*.72),2));
            val+=rim*16;
            val-=pit*13;
          }
        }
        val=Math.max(56,Math.min(240,val));
        p[idx]=val;p[idx+1]=val;p[idx+2]=val;p[idx+3]=255;
      }
    }
    ctx.putImageData(d,0,0);
    texCache[size]=t;
    return t;
  }
  function drawMoonSkin(ctx,cx,cy,R,tone){
    if(moonPhotoReady&&moonPhoto.naturalWidth>0&&moonPhoto.naturalHeight>0){
      var sw=Math.min(moonPhoto.naturalWidth,moonPhoto.naturalHeight);
      var sx=(moonPhoto.naturalWidth-sw)/2,sy=(moonPhoto.naturalHeight-sw)/2;
      ctx.filter=(tone&&tone.filter)?tone.filter:'contrast(1.08) brightness(1.02) saturate(1)';
      ctx.drawImage(moonPhoto,sx,sy,sw,sw,cx-R,cy-R,R*2,R*2);
      ctx.filter='none';
      return;
    }
    var tex=moonTexture(Math.round(R*2));
    ctx.drawImage(tex,cx-R,cy-R,R*2,R*2);
  }
  /* ── Canvas holdrajz — valósághű felszín ── */
  function draw(cv,phase,frac,now){
    var W=cv.width,H=cv.height,cx=W/2,cy=H/2,R=W/2-7,ctx=cv.getContext('2d');
    var tone=moonTone(now,frac);
    ctx.clearRect(0,0,W,H);

    if(frac>.55){
      var gw=ctx.createRadialGradient(cx,cy,R*.62,cx,cy,R*2.18);
      var ga=(frac-.55)/.45*.2;
      gw.addColorStop(0,tone.glowCore.replace('ALPHA',ga));
      gw.addColorStop(.6,'rgba(170,180,160,'+(ga*.36)+')');
      gw.addColorStop(1,'rgba(0,0,0,0)');
      ctx.fillStyle=gw;ctx.fillRect(0,0,W,H);
    }

    ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);
    var dark=ctx.createRadialGradient(cx-R*.12,cy-R*.18,R*.04,cx,cy,R*1.08);
    dark.addColorStop(0,'#1c1d27');dark.addColorStop(1,'#07070d');
    ctx.fillStyle=dark;ctx.fill();

    var tx=R*Math.abs(cos(phase*2*PI)),wax=phase<=0.5,gibb=phase>=0.25&&phase<=0.75,tcw=!gibb;

    if(frac>0.01){
      ctx.save();
      ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.clip();
      ctx.beginPath();ctx.moveTo(cx,cy-R);
      ctx.arc(cx,cy,R,-PI/2,PI/2,!wax);
      ctx.ellipse(cx,cy,Math.max(1,tx),R,0,PI/2,-PI/2,tcw);
      ctx.closePath();ctx.clip();

      drawMoonSkin(ctx,cx,cy,R,tone);

      ctx.fillStyle=tone.overlay;
      ctx.fillRect(cx-R,cy-R,R*2,R*2);

      var lightDir=wax?1:-1;
      var l1=ctx.createLinearGradient(cx-R*lightDir,cy,cx+R*lightDir,cy);
      l1.addColorStop(0,'rgba(245,246,242,.22)');
      l1.addColorStop(.42,'rgba(245,246,242,.08)');
      l1.addColorStop(.7,'rgba(30,30,35,.08)');
      l1.addColorStop(1,'rgba(0,0,0,.16)');
      ctx.fillStyle=l1;ctx.fillRect(cx-R,cy-R,R*2,R*2);

      ctx.globalCompositeOperation='overlay';
      var term=ctx.createLinearGradient(cx+(wax?tx:-tx),cy-R,cx+(wax?tx+R*.24:-tx-R*.24),cy+R);
      term.addColorStop(0,'rgba(255,255,255,.22)');
      term.addColorStop(1,'rgba(0,0,0,0)');
      ctx.fillStyle=term;ctx.fillRect(cx-R,cy-R,R*2,R*2);
      ctx.globalCompositeOperation='source-over';

      ctx.beginPath();ctx.arc(cx,cy,R-1.1,0,2*PI);
      ctx.strokeStyle=tone.rim;
      ctx.lineWidth=1.8;
      ctx.shadowColor=tone.rimGlow;
      ctx.shadowBlur=8;
      ctx.stroke();
      ctx.shadowBlur=0;

      ctx.restore();
    }

    if(frac<0.08){
      ctx.save();ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.clip();
      ctx.globalAlpha=.095;
      drawMoonSkin(ctx,cx,cy,R,tone);
      ctx.globalAlpha=1;
      ctx.restore();
    }

  }
  /* ── Fázisnevek magyarul ── */
  var PH=[[.0625,'🌑 Újhold'],[.1875,'🌒 Növekvő sarló'],[.3125,'🌓 Első negyed'],
          [.4375,'🌔 Növekvő domború'],[.5625,'🌕 Telihold'],[.6875,'🌖 Fogyó domború'],
          [.8125,'🌗 Utolsó negyed'],[.9375,'🌘 Fogyó sarló'],[1.01,'🌑 Újhold']];
  function phName(p){return(PH.find(function(x){return p<x[0]})||[0,'–'])[1];}
  function until(d,now){
    var h=(d-now)/3600000;
    if(h<1)return 'hamarosan';
    if(h<24)return Math.round(h)+'\u00a0óra múlva';
    return Math.round(h/24)+'\u00a0nap múlva';
  }
  /* ── Frissítés ── */
  function update(){
    var now=new Date(),il=moonIllum(now),ph=il.phase,fr=il.frac;
    var cv=document.getElementById('mCanvas');if(!cv)return;
    draw(cv,ph,fr,now);
    document.getElementById('mPhase').textContent=phName(ph);
    document.getElementById('mAge').textContent=(ph*SYN).toFixed(1)+' napos hold';
    document.getElementById('mIllum').textContent=Math.round(fr*100)+'%';
    document.getElementById('mIllumBar').style.width=Math.max(4,Math.round(fr*100))+'%';
    var nx=[{i:'🌑',l:'Újhold',f:0},{i:'🌓',l:'Első negyed',f:.25},
            {i:'🌕',l:'Telihold',f:.5},{i:'🌗',l:'Utolsó negyed',f:.75}];
    document.getElementById('mTime').textContent=now.toLocaleString('hu-HU',{
      timeZone:'Europe/Budapest',year:'numeric',month:'2-digit',day:'2-digit',
      hour:'2-digit',minute:'2-digit'
    });
    document.getElementById('mNext').innerHTML=nx.map(function(n){
      return '<div class="va-moon__nr"><span>'+n.i+'</span><span>'+n.l+'</span>'
            +'<span class="va-moon__nrd">'+until(nextPhase(now,n.f),now)+'</span></div>';
    }).join('');
  }
  document.addEventListener('DOMContentLoaded',function(){
    update();
    setInterval(update,60000);
  });
})();
</script>
<?php endif; ?>

<?php if ( $va_show_weather_widget ): ?>
<script>
(function(){
  var root=document.getElementById('va-weather');
  if(!root)return;
  var locEl=document.getElementById('vwLoc');
  var nowEl=document.getElementById('vwNow');
  var metaEl=document.getElementById('vwMeta');
  var daysEl=document.getElementById('vwDays');
  var agriEl=document.getElementById('vwAgri');
  var chartEl=document.getElementById('vwChart');
  var weeklineEl=document.getElementById('vwWeekline');
  var jumpNavEl=document.getElementById('vwJumpNav');
  var moreHintEl=document.getElementById('vwMoreHint');
  var toggleEl=document.getElementById('vwToggle');
  var modalEl=document.getElementById('vwModal');
  var modalBackdropEl=document.getElementById('vwModalBackdrop');
  var modalCloseEl=document.getElementById('vwModalClose');
  var scrollEl=modalEl?modalEl.querySelector('.va-weather__modal-scroll'):null;
  var lastDailyData=null;
  var lastIdx=[];
  var lastWindUnitRaw='km/h';

  function setForecastModal(open){
    if(!toggleEl||!modalEl)return;
    toggleEl.setAttribute('aria-expanded',open?'true':'false');
    modalEl.hidden=!open;
    document.body.classList.toggle('va-weather-modal-open',open);
    syncMoreHint();
    if(open&&lastDailyData&&lastIdx.length){
      try{drawForecastChart(lastDailyData,lastIdx,lastWindUnitRaw);}catch(_e){}
    }
  }
  if(toggleEl){
    toggleEl.addEventListener('click',function(){setForecastModal(true);});
    setForecastModal(false);
  }
  if(modalCloseEl){
    modalCloseEl.addEventListener('click',function(){setForecastModal(false);});
  }
  if(modalBackdropEl){
    modalBackdropEl.addEventListener('click',function(){setForecastModal(false);});
  }
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'&&modalEl&&!modalEl.hidden){
      setForecastModal(false);
    }
  });
  if(modalEl){
    modalEl.addEventListener('click',function(e){
      if(e.target===modalEl){
        setForecastModal(false);
      }
    });
  }
  window.addEventListener('resize',function(){
    if(modalEl&&!modalEl.hidden&&lastDailyData&&lastIdx.length){
      try{drawForecastChart(lastDailyData,lastIdx,lastWindUnitRaw);}catch(_e){}
    }
    syncMoreHint();
    syncJumpActive();
  });
  function smoothScrollToSection(id){
    if(!scrollEl)return;
    var el=document.getElementById(id);
    if(!el)return;
    scrollEl.scrollTo({top:Math.max(0,el.offsetTop-58),behavior:'smooth'});
  }
  function syncJumpActive(){
    if(!jumpNavEl||!scrollEl)return;
    var map=['vwSectionChart','vwSectionDays','vwSectionAgri'];
    var best='vwSectionChart',bestDist=999999;
    for(var i=0;i<map.length;i++){
      var sec=document.getElementById(map[i]);
      if(!sec)continue;
      var d=Math.abs(sec.offsetTop-scrollEl.scrollTop-58);
      if(d<bestDist){bestDist=d;best=map[i];}
    }
    var btns=jumpNavEl.querySelectorAll('.va-weather__jump-btn');
    for(var b=0;b<btns.length;b++){
      btns[b].classList.toggle('is-active',btns[b].getAttribute('data-target')===best);
    }
  }
  function syncMoreHint(){
    if(!moreHintEl||!scrollEl||!modalEl||modalEl.hidden){
      if(moreHintEl)moreHintEl.classList.remove('is-visible');
      return;
    }
    var hasOverflow=scrollEl.scrollHeight>scrollEl.clientHeight+24;
    var nearBottom=(scrollEl.scrollTop+scrollEl.clientHeight)>=(scrollEl.scrollHeight-36);
    moreHintEl.classList.toggle('is-visible',hasOverflow&&!nearBottom);
  }
  if(jumpNavEl){
    jumpNavEl.addEventListener('click',function(e){
      var btn=e.target&&e.target.closest?e.target.closest('.va-weather__jump-btn'):null;
      if(!btn)return;
      smoothScrollToSection(btn.getAttribute('data-target'));
    });
  }
  if(scrollEl){
    scrollEl.addEventListener('scroll',function(){
      syncMoreHint();
      syncJumpActive();
    },{passive:true});
  }
  if(moreHintEl){
    moreHintEl.addEventListener('click',function(){
      if(!scrollEl)return;
      scrollEl.scrollBy({top:Math.max(180,Math.floor(scrollEl.clientHeight*0.55)),behavior:'smooth'});
    });
  }

  function wcText(code){
    var map={
      0:'Tiszta',1:'Derült',2:'Gyengén felhős',3:'Borult',
      45:'Köd',48:'Zúzmarás köd',51:'Gyenge szitálás',53:'Szitálás',55:'Erős szitálás',
      61:'Gyenge eső',63:'Eső',65:'Erős eső',66:'Fagyott eső',67:'Erős fagyott eső',
      71:'Gyenge havazás',73:'Havazás',75:'Erős havazás',77:'Hószemcsék',
      80:'Zápor',81:'Erős zápor',82:'Heves zápor',85:'Hózápor',86:'Erős hózápor',
      95:'Zivatar',96:'Zivatar jéggel',99:'Heves zivatar jéggel'
    };
    return map[code]||'Ismeretlen';
  }
  function wdText(deg){
    var dirs=['É','ÉK','K','DK','D','DNY','NY','ÉNY'];
    if(typeof deg!=='number')return '–';
    return dirs[Math.round(deg/45)%8];
  }
  function dayHu(iso){
    var d=new Date(iso+'T12:00:00');
    return new Intl.DateTimeFormat('hu-HU',{weekday:'short'}).format(d);
  }
  function dateHu(iso){
    var d=new Date(iso+'T12:00:00');
    return new Intl.DateTimeFormat('hu-HU',{year:'numeric',month:'2-digit',day:'2-digit'}).format(d);
  }
  function dateTimeHu(dateObj){
    return new Intl.DateTimeFormat('hu-HU',{
      year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hour12:false
    }).format(dateObj||new Date());
  }
  function todayIsoBp(){
    return new Intl.DateTimeFormat('en-CA',{
      timeZone:'Europe/Budapest',year:'numeric',month:'2-digit',day:'2-digit'
    }).format(new Date());
  }
  function fmtLoc(raw){
    if(!raw)return 'Ismeretlen hely';
    return raw;
  }
  function toWindKmh(value,unitRaw){
    var v=Number(value||0);
    if(!isFinite(v))return 0;
    var u=String(unitRaw||'km/h').toLowerCase();
    if(u.indexOf('km/h')!==-1||u.indexOf('kmh')!==-1){return v;}
    if(u.indexOf('m/s')!==-1||u.indexOf('ms')!==-1){return v*3.6;}
    if(u.indexOf('mph')!==-1){return v*1.60934;}
    if(u.indexOf('kn')!==-1){return v*1.852;}
    return v;
  }
  function drawForecastChart(d,idx,windUnitRaw){
    if(!chartEl)return;
    var n=Math.min(7,idx.length);
    var ctx=chartEl.getContext('2d');
    if(!ctx||n<2){return;}
    var dpr=window.devicePixelRatio||1;
    var w=Math.max(320,Math.floor(chartEl.clientWidth||640));
    var compact=w<560;
    var h=compact?340:300;
    chartEl.width=Math.floor(w*dpr);
    chartEl.height=Math.floor(h*dpr);
    ctx.setTransform(dpr,0,0,dpr,0,0);
    ctx.clearRect(0,0,w,h);

    var topPad=compact?16:14,rightPad=16,bottomPad=compact?34:30,leftPad=18;
    var cw=w-leftPad-rightPad,ch=h-topPad-bottomPad;
    var labelEvery=compact?3:2;
    var bandGap=10;
    var bandH=(ch-bandGap*2)/3;
    var b1y=topPad;
    var b2y=topPad+bandH+bandGap;
    var b3y=topPad+(bandH+bandGap)*2;
    var tVals=[],rVals=[],rProbVals=[],wVals=[],labels=[];
    for(var k=0;k<n;k++){
      var i=idx[k];
      tVals.push(Number(d.temperature_2m_max[i]||0));
      rVals.push(Number(d.precipitation_sum[i]||0));
      rProbVals.push(Number(d.precipitation_probability_max[i]||0));
      wVals.push(toWindKmh(d.wind_speed_10m_max[i],windUnitRaw));
      labels.push(dayHu(d.time[i]).toUpperCase().slice(0,2));
    }
    var hasRainAmount=false;
    for(var rv=0;rv<rVals.length;rv++){
      if(rVals[rv]>0.01){hasRainAmount=true;break;}
    }
    var rPlotVals=hasRainAmount?rVals:rProbVals;
    var tMin=Math.min.apply(null,tVals),tMax=Math.max.apply(null,tVals);
    var rMax=hasRainAmount?Math.max(0.5,Math.max.apply(null,rPlotVals)):Math.max(5,Math.max.apply(null,rPlotVals));
    var wMax=Math.max(1,Math.max.apply(null,wVals));
    if(tMax===tMin){tMax=tMin+1;}
    var step=n>1?cw/(n-1):cw;

    function drawBand(y,title,color){
      ctx.fillStyle='rgba(255,255,255,.03)';
      ctx.fillRect(leftPad,y,cw,bandH);
      ctx.strokeStyle='rgba(255,255,255,.12)';
      ctx.lineWidth=1;
      ctx.strokeRect(leftPad,y,cw,bandH);
      ctx.fillStyle=color;
      ctx.font=compact?'700 10px Segoe UI':'700 11px Segoe UI';
      ctx.textAlign='left';
      ctx.fillText(title,leftPad+6,y+12);
      ctx.strokeStyle='rgba(255,255,255,.08)';
      for(var gy=1;gy<=3;gy++){
        var yy=y+(bandH/4)*gy;
        ctx.beginPath();ctx.moveTo(leftPad,yy);ctx.lineTo(w-rightPad,yy);ctx.stroke();
      }
    }
    drawBand(b1y,'Hőmérséklet (°C)','rgba(255,255,255,.95)');
    drawBand(b2y,hasRainAmount?'Csapadék (mm)':'Csapadék valószínűség (%)','rgba(94,220,255,.95)');
    drawBand(b3y,'Szél (km/h)','rgba(255,178,110,.95)');

    function drawSafeText(text,x,y,font,fill,stroke,minX,maxX){
      ctx.font=font;
      ctx.textAlign='center';
      var tw=ctx.measureText(text).width;
      var cx=Math.max(minX+tw/2+2,Math.min(maxX-tw/2-2,x));
      if(stroke){ctx.lineWidth=3;ctx.strokeStyle=stroke;ctx.strokeText(text,cx,y);}
      ctx.fillStyle=fill;
      ctx.fillText(text,cx,y);
    }

    for(var b=0;b<n;b++){
      var barW=Math.max(8,step*0.42);
      var bxRaw=leftPad+b*step;
      var bx=Math.max(leftPad+barW/2+1,Math.min(w-rightPad-barW/2-1,bxRaw));
      var bh=(rPlotVals[b]/rMax)*(bandH*0.72);
      if(!hasRainAmount&&rPlotVals[b]<=0){bh=2;}
      var by=b2y+bandH-bh-4;
      var grad=ctx.createLinearGradient(0,by,0,topPad+ch);
      grad.addColorStop(0,'rgba(69,211,255,.95)');
      grad.addColorStop(1,'rgba(69,211,255,.2)');
      ctx.fillStyle=grad;
      ctx.fillRect(bx-barW/2,by,barW,bh);
      var rainTxt=hasRainAmount?((rPlotVals[b]||0).toFixed(1)+' mm'):(Math.round(rPlotVals[b]||0)+'%');
      var rainY=Math.max(b2y+12,by-4);
      drawSafeText(rainTxt,bx,rainY,compact?'700 8px Segoe UI':'700 9px Segoe UI','rgba(130,228,255,.96)','rgba(8,14,24,.85)',leftPad,w-rightPad);
    }

    ctx.beginPath();
    for(var p=0;p<n;p++){
      var px=leftPad+p*step;
      var py=b1y+4+((tMax-tVals[p])/(tMax-tMin))*(bandH-14);
      if(p===0){ctx.moveTo(px,py);}else{ctx.lineTo(px,py);}
    }
    ctx.strokeStyle='#ffffff';
    ctx.lineWidth=2.5;
    ctx.shadowColor='rgba(255,255,255,.65)';
    ctx.shadowBlur=12;
    ctx.stroke();
    ctx.shadowBlur=0;

    ctx.beginPath();
    for(var wi=0;wi<n;wi++){
      var wx=leftPad+wi*step;
      var wy=b3y+bandH-4-((wVals[wi]/wMax)*(bandH-14));
      if(wi===0){ctx.moveTo(wx,wy);}else{ctx.lineTo(wx,wy);}
    }
    ctx.strokeStyle='rgba(255,156,47,.95)';
    ctx.lineWidth=2;
    ctx.setLineDash([6,4]);
    ctx.shadowColor='rgba(255,156,47,.45)';
    ctx.shadowBlur=10;
    ctx.stroke();
    ctx.setLineDash([]);
    ctx.shadowBlur=0;

    for(var wi2=0;wi2<n;wi2++){
      var wx2=leftPad+wi2*step;
      var wy2=b3y+bandH-4-((wVals[wi2]/wMax)*(bandH-14));
      ctx.fillStyle='rgba(255,156,47,.98)';
      ctx.beginPath();ctx.arc(wx2,wy2,2.7,0,Math.PI*2);ctx.fill();
      var wLabelY=wy2+((wi2%2===0)?-10:12);
      wLabelY=Math.max(b3y+12,Math.min(b3y+bandH-8,wLabelY));
      drawSafeText(Math.round(wVals[wi2])+' km/h',wx2,wLabelY,compact?'700 8px Segoe UI':'700 9px Segoe UI','rgba(255,188,130,.98)','rgba(8,14,24,.9)',leftPad,w-rightPad);
    }

    var tMaxIdx=0,tMinIdx=0;
    for(var ti=0;ti<n;ti++){
      if(tVals[ti]>tVals[tMaxIdx])tMaxIdx=ti;
      if(tVals[ti]<tVals[tMinIdx])tMinIdx=ti;
    }
    var prevTempLabelY=null;
    for(var p2=0;p2<n;p2++){
      var px2=leftPad+p2*step;
      var py2=b1y+4+((tMax-tVals[p2])/(tMax-tMin))*(bandH-14);
      ctx.fillStyle='#fff';
      ctx.beginPath();ctx.arc(px2,py2,3.2,0,Math.PI*2);ctx.fill();
      ctx.textAlign='center';
      var tLabelY=py2-10;
      if(prevTempLabelY!==null&&Math.abs(tLabelY-prevTempLabelY)<11){
        tLabelY=py2+12;
      }
      tLabelY=Math.max(b1y+12,Math.min(b1y+bandH-6,tLabelY));
      drawSafeText(Math.round(tVals[p2])+'°',px2,tLabelY,compact?'700 9px Segoe UI':'700 10px Segoe UI','rgba(255,255,255,.96)','rgba(8,14,24,.9)',leftPad,w-rightPad);
      prevTempLabelY=tLabelY;
      ctx.fillStyle='rgba(205,230,255,.8)';
      ctx.font=compact?'700 9px Segoe UI':'700 10px Segoe UI';
      var dx=px2;
      ctx.textAlign='center';
      if(p2===0){ctx.textAlign='left';dx=leftPad+2;}
      else if(p2===n-1){ctx.textAlign='right';dx=w-rightPad-2;}
      ctx.fillText(labels[p2],dx,h-9);
    }
  }
  function monthInRange(m,start,end){
    if(start<=end){return m>=start&&m<=end;}
    return m>=start||m<=end;
  }
  function renderAgriCalendar(){
    if(!agriEl)return;
    var m=(new Date()).getMonth()+1;
    var mon=['jan','febr','márc','ápr','máj','jún','júl','aug','szept','okt','nov','dec'];
    var crops=[
      {name:'Őszi búza',sow:[9,10],bloom:[5,6],ripen:[7,8],harvest:[7,8]},
      {name:'Őszi árpa',sow:[9,10],bloom:[4,5],ripen:[6,7],harvest:[6,7]},
      {name:'Kukorica',sow:[4,5],bloom:[7,8],ripen:[9,10],harvest:[9,10]},
      {name:'Napraforgó',sow:[4,4],bloom:[7,7],ripen:[9,9],harvest:[9,9]},
      {name:'Őszi repce',sow:[8,9],bloom:[4,5],ripen:[6,7],harvest:[6,7]},
      {name:'Szója',sow:[4,5],bloom:[6,7],ripen:[9,10],harvest:[9,10]},
      {name:'Cirok',sow:[5,5],bloom:[7,8],ripen:[9,10],harvest:[9,10]},
      {name:'Lucerna',sow:[3,4],bloom:[5,9],ripen:[7,9],harvest:[6,9]}
    ];
    function row(label,range,active){
      var t=mon[range[0]-1]+'–'+mon[range[1]-1];
      return '<div class="va-weather__agri-row"><span>'+label+' · '+t+'</span><span class="va-weather__agri-tag '+(active?'is-now':'is-off')+'">'+(active?'MOST':'nem most')+'</span></div>';
    }
    var html='<div class="va-weather__agri-head"><div class="va-weather__agri-title">Agrár naptár vadászatra</div><div class="va-weather__agri-sub">HU országos átlagos időablakok: vetés, virágzás, érés</div></div><div class="va-weather__agri-grid">';
    for(var i=0;i<crops.length;i++){
      var c=crops[i];
      html+='<div class="va-weather__agri-card"><div class="va-weather__agri-crop">'+c.name+'</div>'
        +row('Vetés',c.sow,monthInRange(m,c.sow[0],c.sow[1]))
        +row('Virágzás',c.bloom,monthInRange(m,c.bloom[0],c.bloom[1]))
        +row('Érés',c.ripen,monthInRange(m,c.ripen[0],c.ripen[1]))
        +row('Aratás',c.harvest,monthInRange(m,c.harvest[0],c.harvest[1]))
        +'</div>';
    }
    html+='</div>';
    agriEl.innerHTML=html;
  }
  function render(data,label){
    if(!data||!data.current||!data.daily){
      nowEl.textContent='Nincs időjárás adat';
      return;
    }
    var c=data.current;
    var d=data.daily;
    var windUnitRaw=(data.daily_units&&data.daily_units.wind_speed_10m_max)||(data.current_units&&data.current_units.wind_speed_10m)||'km/h';
    lastWindUnitRaw=windUnitRaw;
    var currentWindKmh=toWindKmh(c.wind_speed_10m,windUnitRaw);
    var todayIso=todayIsoBp();
    locEl.textContent=fmtLoc(label);
    nowEl.innerHTML='<strong>'+Math.round(c.temperature_2m)+'°C</strong> • '+wcText(c.weather_code)+' <span class="va-weather__stamp">('+dateTimeHu(new Date())+')</span>';
    metaEl.textContent='Hőérzet '+Math.round(c.apparent_temperature)+'°C • Pára '+Math.round(c.relative_humidity_2m)+'% • Szél '+Math.round(currentWindKmh)+' km/h ('+wdText(c.wind_direction_10m)+') • Csapadék '+(c.precipitation||0)+' mm/h • Dátum: '+dateHu(d.time[0]);

    var html='';
    var idx=[];
    var startIdx=0;
    for(var ii=0;ii<d.time.length;ii++){
      if(d.time[ii]===todayIso){startIdx=ii;break;}
    }
    for(var ff=startIdx;ff<d.time.length&&idx.length<7;ff++){idx.push(ff);}
    for(var ff2=0;ff2<d.time.length&&idx.length<7;ff2++){
      if(idx.indexOf(ff2)===-1)idx.push(ff2);
    }
    for(var j=0;j<idx.length&&j<7;j++){
      var i=idx[j];
      var prefix=dayHu(d.time[i]);
      html+='<div class="va-weather__day">'
        +'<div class="va-weather__d1">'+prefix+' · '+dateHu(d.time[i])+'</div>'
        +'<div class="va-weather__d2">'+Math.round(d.temperature_2m_min[i])+' / '+Math.round(d.temperature_2m_max[i])+'°C</div>'
        +'<div class="va-weather__d3">'+wcText(d.weather_code[i])+'</div>'
        +'<div class="va-weather__d4">Csap. '+Math.round(d.precipitation_probability_max[i]||0)+'% · '+(d.precipitation_sum[i]||0).toFixed(1)+' mm · Szél '+Math.round(toWindKmh(d.wind_speed_10m_max[i],windUnitRaw))+' km/h</div>'
        +'</div>';
    }
    daysEl.innerHTML=html;
    if(weeklineEl){
      var wh='';
      for(var wj=0;wj<idx.length&&wj<7;wj++){
        var wi=idx[wj];
        var rainV=Number(d.precipitation_sum[wi]||0);
        var rainP=Math.round(d.precipitation_probability_max[wi]||0);
        var rainText=rainV>0.01?(rainV.toFixed(1)+' mm'):(rainP+'%');
        wh+='<div class="va-weather__weekcell">'
          +'<div class="va-weather__weekd">'+dayHu(d.time[wi]).toUpperCase().slice(0,2)+'</div>'
          +'<div class="va-weather__weekt">'+Math.round(d.temperature_2m_max[wi]||0)+'°</div>'
          +'<div class="va-weather__weekr">'+rainText+'</div>'
          +'<div class="va-weather__weekw">'+Math.round(toWindKmh(d.wind_speed_10m_max[wi],windUnitRaw))+' km/h</div>'
          +'</div>';
      }
      weeklineEl.innerHTML=wh;
    }
    renderAgriCalendar();
    lastDailyData=d;
    lastIdx=idx.slice(0,7);
      try{drawForecastChart(d,lastIdx,windUnitRaw);}catch(_err){}
  }
  function weather(lat,lon,label){
    var url='https://api.open-meteo.com/v1/forecast?latitude='+encodeURIComponent(lat)
      +'&longitude='+encodeURIComponent(lon)
      +'&current=temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,weather_code,wind_speed_10m,wind_direction_10m'
      +'&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max'
      +'&forecast_days=8&timezone=auto&wind_speed_unit=kmh';
    fetch(url).then(function(r){return r.json();}).then(function(json){render(json,label);}).catch(function(){
      nowEl.textContent='Időjárás nem elérhető';
    });
  }
  function ipFallback(){
    fetch('https://ipapi.co/json/').then(function(r){return r.json();}).then(function(json){
      if(json&&json.latitude&&json.longitude){
        var lbl=(json.city?json.city+', ':'')+(json.region||json.country_name||'IP helyzet');
        weather(json.latitude,json.longitude,lbl+' (IP)');
      }else{
        nowEl.textContent='Helyadat nem elérhető';
      }
    }).catch(function(){
      nowEl.textContent='Helyadat nem elérhető';
    });
  }
  if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(function(pos){
      weather(pos.coords.latitude,pos.coords.longitude,'Aktuális hely');
    },function(){
      ipFallback();
    },{enableHighAccuracy:true,timeout:7000,maximumAge:900000});
  }else{
    ipFallback();
  }
})();
</script>
<?php endif; ?>

<script>
(function(){
  var root=document.getElementById('va-home-radar');
  if(!root)return;
  var locEl=document.getElementById('vhrLoc');
  var cropEl=document.getElementById('vhrCrop');
  var forestEl=document.getElementById('vhrForest');
  var damageEl=document.getElementById('vhrDamage');
  var scoreEl=document.getElementById('vhrScore');
  var levelEl=document.getElementById('vhrLevel');
  var speciesEl=document.getElementById('vhrSpecies');
  var windowEl=document.getElementById('vhrWindow');
  var confidenceEl=document.getElementById('vhrConfidence');
  var reasonEl=document.getElementById('vhrReason');
  var daysEl=document.getElementById('vhrDays');
  var toggleEl=document.getElementById('vhrToggle');
  var modalEl=document.getElementById('vhrModal');
  var modalBackdropEl=document.getElementById('vhrModalBackdrop');
  var modalCloseEl=document.getElementById('vhrModalClose');
  var pickerEl=document.getElementById('vhrPicker');
  var pickerBackdropEl=document.getElementById('vhrPickerBackdrop');
  var pickerCloseEl=document.getElementById('vhrPickerClose');
  var pickerTitleEl=document.getElementById('vhrPickerTitle');
  var pickerOptionsEl=document.getElementById('vhrPickerOptions');
  var phenologyEl=document.getElementById('vhrPhenology');
  var huntingEl=document.getElementById('vhrHunting');
  var fenceEl=document.getElementById('vhrFence');
  var forestFoodEl=document.getElementById('vhrForestFood');
  var waterEl=document.getElementById('vhrWater');
  var herdEl=document.getElementById('vhrHerd');
  var modalScoreEl=document.getElementById('vhrModalScore');
  var modalLevelEl=document.getElementById('vhrModalLevel');
  var modalSpeciesEl=document.getElementById('vhrModalSpecies');
  var modalWindowEl=document.getElementById('vhrModalWindow');
  var modalConfidenceEl=document.getElementById('vhrModalConfidence');
  var silhouetteEl=document.getElementById('vhrSilhouette');
  var cinemaSpeciesEl=document.getElementById('vhrCinemaSpecies');
  var threatPulseEl=document.getElementById('vhrThreatPulse');
  var patternEl=document.getElementById('vhrPattern');
  var factorsEl=document.getElementById('vhrFactors');
  var weekEl=document.getElementById('vhrWeek');
  var heatmapEl=document.getElementById('vhrHeatmap');
  var noteEl=document.getElementById('vhrNote');
  var intelEl=document.getElementById('vhrIntel');
  var pickerButtons={};
  var activePickerSelect=null;
  var phenologyMap={maize:[['sowing','Vetés'],['emergence','Kelés'],['milk_stage','Tejes érés'],['wax_stage','Viaszérés'],['pre_harvest','Betakarítás előtt']],rapeseed:[['autumn_green','Őszi zöld'],['spring_regrowth','Tavaszi újrahajtás'],['flowering','Virágzás'],['ripening','Érés']],sunflower:[['emergence','Kelés'],['head_stage','Tányérképzés'],['ripening','Érés'],['pre_harvest','Betakarítás előtt']],cereal:[['young','Fiatal állomány'],['heading','Kalászolás'],['ripening','Érés']],alfalfa:[['fresh_regrowth','Friss sarjadás'],['post_cut','Kaszálás utáni 3-10 nap'],['mature','Fejlett állomány']],orchard:[['budburst','Rügyfakadás'],['young_shoot','Fiatal hajtás'],['fruiting','Termésképzés']],vineyard:[['budburst','Rügyfakadás'],['young_shoot','Fiatal hajtás'],['fruiting','Termésképzés']]};
  var speciesMeta={wild_boar:{label:'Vaddisznó',short:'VD',window:'21:00-02:00'},red_deer:{label:'Gímszarvas',short:'GÍM',window:'20:00-23:30'},roe_deer:{label:'Őz',short:'ŐZ',window:'04:00-07:00'},fallow_deer:{label:'Dámvad',short:'DÁM',window:'20:30-23:00'},wild_hare:{label:'Vadnyúl',short:'VNY',window:'19:30-23:30'}};
  var speciesArt={wild_boar:'<svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M46 112c8-23 31-42 65-49l27-24 41 4 33 18 28 5 26 26-7 18-17-6-14 15-22 9-27 0-16 13-27 0-9-12-25-1-21-16-18 0 6-20Zm87-23 23 6 33-2 19-10-18-10-27-3-18 4-12 15Zm98 6 15 1 12 11-17-2-10 11-6-6 6-15Z"/></svg>',red_deer:'<svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M68 123c4-24 20-45 44-55l22-23 39 2 19 13 7-13 8 2-3 17 11 10 6-18 8 2-3 21 15 15-5 16-18-3-17 15-27 6-20 13-24-1-7-12-21 1-17-12-23 2 6-18Zm93-33 22 5 17-5-8-16-19-7-15 2-11 13Zm46-34 12-25 13 6-10 24-15-5Zm-18-2 2-29 15 2-3 30-14-3Z"/></svg>',roe_deer:'<svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M69 125c6-22 21-38 43-48l16-20 34 0 20 12 18 4 20 19-6 16-15-2-15 14-22 6-18 13-24 0-8-11-19 1-18-10-20 2 5-16Zm81-34 22 5 13-8-8-13-20-5-13 2-8 12Zm46-30 7-21 10 4-5 20-12-3Zm-20-1-4-20 10-3 6 19-12 4Z"/></svg>',fallow_deer:'<svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M62 123c5-22 23-42 50-52l18-22 40 2 18 11 8-10 10 2-4 16 13 8 8-14 10 4-5 17 15 15-4 17-18-3-16 16-22 7-22 13-25 0-8-12-18 2-19-11-22 2 5-18Zm84-34 22 7 19-5-9-14-17-6-17 0-10 12Zm53-26 20-13 11 8-19 14-12-9Zm-2-9 5-28 16 6-7 28-14-6Z"/></svg>',mouflon:'<svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M61 123c5-18 18-36 39-47l16-19 35 1 19 10 6-9 18 3c10 3 20 11 27 24l7 14-7 14-17-2-12 14-22 6-19 13-24 0-9-11-17 2-20-10-20 2 5-18Zm83-34 23 5 15-8-8-12-18-5-15 2-9 12Zm38-34c15-17 33-18 45-3 8 10 7 24-2 34l-10-4c6-8 7-18 2-24-5-7-15-6-23 4l-12-7Zm15 2c9-10 22-12 31-3 7 7 6 19-1 28l-9-4c5-6 5-13 1-17-4-4-10-3-16 4l-6-8Z"/></svg>'};
  speciesArt.wild_hare='<svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M72 128c4-20 18-38 38-49l14-17 29 1 15 9 15 3 18 16-5 14-13-2-11 12-17 6-15 10-21 0-7-9-15 1-16-9-16 2 4-14Zm61-34 19 5 14-6-7-10-15-5-13 1-8 10Zm29-28c5-16 14-31 23-32 6-1 8 8 5 17-3 9-9 19-17 26l-11-11Zm14 3c8-19 19-35 30-34 7 1 8 11 4 21-4 9-12 19-24 26l-10-13Z"/></svg>';
  var cropLabels={maize:'kukorica',rapeseed:'repce',sunflower:'napraforgó',cereal:'kalászos',alfalfa:'lucerna',orchard:'gyümölcsös',vineyard:'szőlő'};
  var speciesBase={wild_boar:9,red_deer:7,roe_deer:5,fallow_deer:6,wild_hare:4};
  var forestFoodScores={abundant:{wild_boar:-5,red_deer:-4,roe_deer:-3,fallow_deer:-3,wild_hare:-2},normal:{wild_boar:0,red_deer:0,roe_deer:0,fallow_deer:0,wild_hare:0},scarce:{wild_boar:6,red_deer:5,roe_deer:4,fallow_deer:4,wild_hare:3}};
  var herdScores={small:0,medium:4,large:9};
  var cropScores={maize:{wild_boar:15,red_deer:10,roe_deer:4,fallow_deer:8,wild_hare:3},sunflower:{wild_boar:11,red_deer:6,roe_deer:7,fallow_deer:5,wild_hare:2},rapeseed:{wild_boar:5,red_deer:13,roe_deer:14,fallow_deer:12,wild_hare:9},cereal:{wild_boar:6,red_deer:9,roe_deer:7,fallow_deer:8,wild_hare:7},alfalfa:{wild_boar:9,red_deer:11,roe_deer:10,fallow_deer:10,wild_hare:8},orchard:{wild_boar:4,red_deer:8,roe_deer:7,fallow_deer:8,wild_hare:4},vineyard:{wild_boar:3,red_deer:9,roe_deer:8,fallow_deer:7,wild_hare:4}};
  var forestScores={under_100:10,'100_300':8,'300_700':5,'700_1500':2,over_1500:0};
  var damageScores={7:12,30:8,90:5,none:0};
  var huntingScores={low:{wild_boar:4,red_deer:4,roe_deer:3,fallow_deer:3,wild_hare:2},medium:{wild_boar:1,red_deer:0,roe_deer:0,fallow_deer:0,wild_hare:0},high:{wild_boar:-4,red_deer:-3,roe_deer:-2,fallow_deer:-2,wild_hare:-2}};
  var phenologyScores={maize:{sowing:{wild_boar:12},emergence:{wild_boar:8},milk_stage:{wild_boar:15,red_deer:8,fallow_deer:7},wax_stage:{wild_boar:12,red_deer:7},pre_harvest:{wild_boar:10,red_deer:6}},rapeseed:{autumn_green:{red_deer:12,roe_deer:12,fallow_deer:10,wild_hare:8},spring_regrowth:{red_deer:10,roe_deer:10,fallow_deer:8,wild_hare:6},flowering:{red_deer:5,roe_deer:4},ripening:{red_deer:3,roe_deer:2}},sunflower:{emergence:{wild_boar:4,roe_deer:7},head_stage:{wild_boar:8,roe_deer:5},ripening:{wild_boar:10,roe_deer:6},pre_harvest:{wild_boar:8}},cereal:{young:{red_deer:9,roe_deer:7,fallow_deer:8,wild_hare:6},heading:{red_deer:6,roe_deer:4,fallow_deer:5},ripening:{red_deer:3,roe_deer:2,fallow_deer:2}},alfalfa:{fresh_regrowth:{wild_boar:8,red_deer:10,roe_deer:9,fallow_deer:9,wild_hare:6},post_cut:{wild_boar:5,red_deer:6,roe_deer:4,fallow_deer:5},mature:{red_deer:4,roe_deer:4}},orchard:{budburst:{roe_deer:8,red_deer:5},young_shoot:{roe_deer:10,red_deer:7,fallow_deer:5},fruiting:{wild_boar:5,red_deer:5}},vineyard:{budburst:{roe_deer:8,red_deer:5},young_shoot:{roe_deer:9,red_deer:6,fallow_deer:4},fruiting:{red_deer:6,roe_deer:5}}};
  var weatherData=null;
  function setPhenologyOptions(){var crop=cropEl.value,list=phenologyMap[crop]||[],current=phenologyEl.value;phenologyEl.innerHTML='';for(var i=0;i<list.length;i++){var opt=document.createElement('option');opt.value=list[i][0];opt.textContent=list[i][1];phenologyEl.appendChild(opt);}if(list.some(function(item){return item[0]===current;})){phenologyEl.value=current;}if(pickerButtons.vhrPhenology){syncPickerButton(phenologyEl);}}
  function setRadarModal(open){if(!toggleEl||!modalEl)return;toggleEl.setAttribute('aria-expanded',open?'true':'false');modalEl.hidden=!open;document.body.classList.toggle('va-home-radar-modal-open',open);}
  function setPickerModal(open){if(!pickerEl)return;pickerEl.hidden=!open;document.body.classList.toggle('va-home-radar-picker-open',open);if(!open){activePickerSelect=null;}}
  function syncPickerButton(selectEl){var button=pickerButtons[selectEl.id];if(!button){return;}var option=selectEl.options[selectEl.selectedIndex];button.querySelector('span').textContent=option?option.textContent:'Válassz';}
  function openPicker(selectEl,title){activePickerSelect=selectEl;pickerTitleEl.textContent=title;pickerOptionsEl.innerHTML='';for(var i=0;i<selectEl.options.length;i++){var option=selectEl.options[i],btn=document.createElement('button');btn.type='button';btn.className='va-home-radar__picker-option'+(option.value===selectEl.value?' is-active':'');btn.textContent=option.textContent;btn.dataset.value=option.value;btn.addEventListener('click',function(){if(!activePickerSelect){return;}activePickerSelect.value=this.dataset.value;syncPickerButton(activePickerSelect);activePickerSelect.dispatchEvent(new Event('change',{bubbles:true}));setPickerModal(false);});pickerOptionsEl.appendChild(btn);}setPickerModal(true);}
  function setupPicker(selectEl,title){var button=document.createElement('button');button.type='button';button.className='va-home-radar__picker-btn';button.setAttribute('aria-haspopup','dialog');button.innerHTML='<span></span>';button.addEventListener('click',function(){openPicker(selectEl,title);});selectEl.insertAdjacentElement('afterend',button);pickerButtons[selectEl.id]=button;syncPickerButton(selectEl);}
  function clamp(num,min,max){return Math.max(min,Math.min(max,num));}
  function riskLevel(score){if(score>=85){return {label:'Kritikus',cls:'critical'};}if(score>=70){return {label:'Nagyon magas',cls:'very-high'};}if(score>=50){return {label:'Magas',cls:'high'};}if(score>=25){return {label:'Mérsékelt',cls:'moderate'};}return {label:'Alacsony',cls:'low'};}
  function confidenceLabel(score){if(score>=75){return 'Magas';}if(score>=50){return 'Közepes';}return 'Alacsony';}
  function pulseLabel(score){if(score>=85){return 'Súlyos kockázati ablak';}if(score>=70){return 'Gyors erősödés';}if(score>=50){return 'Aktív nyomás';}if(score>=25){return 'Figyelendő állapot';}return 'Alacsony mozgás';}
  function heatColor(level){if(level.cls==='critical'){return 'rgba(255,31,61,.72)';}if(level.cls==='very-high'){return 'rgba(255,91,47,.7)';}if(level.cls==='high'){return 'rgba(255,138,43,.68)';}if(level.cls==='moderate'){return 'rgba(208,167,32,.64)';}return 'rgba(70,210,115,.6)';}
  function dayName(index){return ['V','H','K','Sze','Cs','P','Szo'][index]||'';}
  function formatDateShort(date){return String(date.getMonth()+1).padStart(2,'0')+'.'+String(date.getDate()).padStart(2,'0')+'.';}
  function moonIllumination(date){var synodic=29.53058867,knownNewMoon=Date.UTC(2024,0,11,11,57,0),days=(date.getTime()-knownNewMoon)/86400000,phase=((days%synodic)+synodic)%synodic/synodic;return (1-Math.cos(2*Math.PI*phase))/2;}
  function controls(){return {crop:cropEl.value,forest:forestEl.value,damage:damageEl.value,phenology:phenologyEl.value,hunting:huntingEl.value,fence:fenceEl.value==='1',forestFood:forestFoodEl.value,water:waterEl.value==='1',herd:herdEl.value};}
  function hourlyRows(data){if(!data||!data.hourly||!data.hourly.time){return [];}var out=[];for(var i=0;i<data.hourly.time.length;i++){out.push({time:data.hourly.time[i],temp:Number(data.hourly.temperature_2m[i]||0),humidity:Number(data.hourly.relative_humidity_2m[i]||0),precip:Number(data.hourly.precipitation[i]||0),wind:Number(data.hourly.wind_speed_10m[i]||0),pressure:Number(data.hourly.pressure_msl[i]||0),cloud:Number(data.hourly.cloud_cover[i]||0),soil:Number(data.hourly.soil_moisture_0_to_1cm&&data.hourly.soil_moisture_0_to_1cm[i]||0)});}return out;}
  function dayContext(data,dayIndex){var rows=hourlyRows(data),dayStr=data.daily.time[dayIndex],target=dayStr+'T22:00',night=null;for(var i=0;i<rows.length;i++){if(rows[i].time===target||rows[i].time.indexOf(dayStr+'T21:00')===0||rows[i].time.indexOf(dayStr+'T23:00')===0){night=rows[i];if(rows[i].time===target){break;}}}var recent=[];if(night){for(var j=0;j<rows.length;j++){if(rows[j].time===night.time){recent=rows.slice(Math.max(0,j-47),j+1);break;}}}var precip48=recent.reduce(function(sum,row){return sum+row.precip;},0);var prior24=recent.length>=24?recent[recent.length-24]:null;return {day:dayStr,tempNight:night?night.temp:0,humidity:night?night.humidity:0,wind:night?night.wind:0,pressureDrop:night&&prior24?(night.pressure-prior24.pressure):0,cloud:night?night.cloud:0,soil:night?night.soil:0,precip48:precip48,tempMin:Number(data.daily.temperature_2m_min[dayIndex]||0),rainSum:Number(data.daily.precipitation_sum[dayIndex]||0),moon:moonIllumination(new Date(dayStr+'T22:00:00'))};}
  function scoreSpecies(species,ctx,ctl){var score=(speciesBase[species]||4)+((cropScores[ctl.crop]&&cropScores[ctl.crop][species])||0)+(forestScores[ctl.forest]||0)+(damageScores[ctl.damage]||0)+((((phenologyScores[ctl.crop]||{})[ctl.phenology]||{})[species])||0)+((huntingScores[ctl.hunting]&&huntingScores[ctl.hunting][species])||0),reasons=[];if((forestScores[ctl.forest]||0)>0){reasons.push('erdőközeli kijárás');}if((damageScores[ctl.damage]||0)>0){reasons.push('friss kárelőzmény');}reasons.push((cropLabels[ctl.crop]||ctl.crop)+' vonzó táplálék');if((((phenologyScores[ctl.crop]||{})[ctl.phenology]||{})[species])>0){reasons.push('aktuális fenológia erős kiváltó ok');}if(ctl.fence){score-=15;}if(forestFoodScores[ctl.forestFood]){var ffs=forestFoodScores[ctl.forestFood][species]||0;score+=ffs;if(ctl.forestFood==='scarce'&&ffs>0){reasons.push('szegényes erdei táplálék, mező felé kényszerül');}else if(ctl.forestFood==='abundant'&&ffs<0){reasons.push('bő erdei makk/gyümölcs, erdőben marad');}}if(ctl.water&&species==='wild_boar'){score+=6;reasons.push('vizes élőhely közel (+6)');}if(ctl.water&&(species==='red_deer'||species==='fallow_deer')){score+=3;reasons.push('vizes élőhely közel (+3)');}score+=herdScores[ctl.herd]||0;if(ctl.herd==='large'){reasons.push('nagy csorda fokozott mezei nyomást fejt ki');}else if(ctl.herd==='medium'){reasons.push('közepes csorda');}if(species==='wild_boar'&&ctx.precip48>5&&ctx.soil>0.18){score+=5;reasons.push('eső utáni puha talaj');}if(species==='wild_boar'&&ctx.tempNight>12&&ctx.humidity>75){score+=4;reasons.push('meleg, párás éjszaka');}if((species==='red_deer'||species==='roe_deer'||species==='fallow_deer'||species==='wild_hare')&&ctx.tempMin<=2&&(ctl.crop==='rapeseed'||ctl.crop==='cereal'||ctl.crop==='alfalfa')){score+=4;reasons.push('hidegben felértékelődő zöld kultúra');}if(ctx.wind>30){score-=5;}else if(ctx.wind<8){score+=2;reasons.push('szélcsend');}if(ctx.pressureDrop<=-5){score+=3;reasons.push('front előtti nyomásesés');}if(ctx.rainSum>6){score+=2;reasons.push('csapadék utáni mozgás');}var effMoon=ctx.moon*(1-(ctx.cloud/100));if(species==='wild_boar'){if(effMoon>0.6&&ctl.hunting!=='low'){score-=1;}if(effMoon>0.9&&ctl.hunting==='high'){score-=1;}}else if(effMoon>0.9){score+=2;reasons.push('erős holdfény');}if(ctl.fence){reasons.push('villanypásztor fékezi a kijárást');}return {score:clamp(Math.round(score),0,100),reasons:reasons};}
  function computeForecast(data){var ctl=controls(),keys=Object.keys(speciesMeta),days=[];for(var dayIndex=0;dayIndex<data.daily.time.length&&dayIndex<7;dayIndex++){var ctx=dayContext(data,dayIndex),best=null;for(var i=0;i<keys.length;i++){var key=keys[i],res=scoreSpecies(key,ctx,ctl);if(!best||res.score>best.result.score){best={species:key,result:res,ctx:ctx};}}days.push({day:ctx.day,species:best.species,score:best.result.score,result:best.result,ctx:ctx});}return days;}
  function computeMatrix(data){var ctl=controls(),keys=Object.keys(speciesMeta),matrix=[];for(var i=0;i<keys.length;i++){var key=keys[i],row=[];for(var dayIndex=0;dayIndex<data.daily.time.length&&dayIndex<7;dayIndex++){var ctx=dayContext(data,dayIndex),res=scoreSpecies(key,ctx,ctl),lvl=riskLevel(res.score);row.push({day:ctx.day,score:res.score,level:lvl});}matrix.push({species:key,row:row});}return matrix;}
  function renderHeatmap(matrix){if(!heatmapEl){return;}var html='<div class="va-home-radar__heatmap-head"><div class="va-home-radar__heatmap-corner">Faj</div>';if(matrix[0]){html+=matrix[0].row.map(function(cell){var d=new Date(cell.day+'T12:00:00');return '<div class="va-home-radar__heatmap-day"><span class="va-home-radar__heatmap-dow">'+dayName(d.getDay())+'</span><span class="va-home-radar__heatmap-date">'+formatDateShort(d)+'</span></div>';}).join('');}html+='</div>';html+=matrix.map(function(entry){return '<div class="va-home-radar__heatmap-row"><div class="va-home-radar__heatmap-label"><strong>'+speciesMeta[entry.species].label+'</strong></div>'+entry.row.map(function(cell){return '<div class="va-home-radar__heatmap-cell is-'+cell.level.cls+'" style="--heat:'+heatColor(cell.level)+'"><strong>'+cell.score+'</strong><span>'+cell.level.label+'</span></div>';}).join('')+'</div>';}).join('');heatmapEl.innerHTML=html;}
  function render(data,label){weatherData=data;var forecast=computeForecast(data);if(!forecast.length){return;}var matrix=computeMatrix(data),top=forecast[0],level=riskLevel(top.score),confidence=clamp(55+(damageEl.value!=='none'?12:0)+(forestEl.value!=='over_1500'?8:0)+(phenologyEl.value?6:0)+(navigator.geolocation?5:0),0,88);root.setAttribute('data-risk',level.cls);if(modalEl){modalEl.setAttribute('data-risk',level.cls);}locEl.textContent=label;scoreEl.innerHTML=String(top.score)+'<small>/100</small>';levelEl.textContent=level.label;speciesEl.textContent=speciesMeta[top.species].label;windowEl.textContent=speciesMeta[top.species].window;confidenceEl.textContent=confidenceLabel(confidence)+' ('+confidence+'%)';if(intelEl){intelEl.textContent=speciesMeta[top.species].label+' fókusz, '+speciesMeta[top.species].window+' kritikus kijárási sáv, '+confidenceLabel(confidence).toLowerCase()+' megbízhatósággal.';}if(silhouetteEl){silhouetteEl.innerHTML=speciesArt[top.species]||'';}if(cinemaSpeciesEl){cinemaSpeciesEl.textContent=speciesMeta[top.species].label;}if(threatPulseEl){threatPulseEl.textContent=pulseLabel(top.score);}if(patternEl){patternEl.textContent=top.result.reasons.slice(0,2).join(' • ');}reasonEl.textContent='Fő kiváltó ok: '+top.result.reasons.slice(0,3).join(' • ')+'.';daysEl.innerHTML=forecast.slice(0,7).map(function(day){var d=new Date(day.day+'T12:00:00'),lvl=riskLevel(day.score);return '<div class="va-home-radar__day is-'+lvl.cls+'"><div class="va-home-radar__dayname">'+dayName(d.getDay())+'</div><div class="va-home-radar__daydate">'+formatDateShort(d)+'</div><div class="va-home-radar__dayscore">'+day.score+'<small>/100</small></div><div class="va-home-radar__dayspecies">'+speciesMeta[day.species].label+'</div></div>';}).join('');modalScoreEl.innerHTML=String(top.score)+'<small>/100</small>';modalLevelEl.textContent=level.label;modalSpeciesEl.textContent=speciesMeta[top.species].label;modalWindowEl.textContent=speciesMeta[top.species].window;modalConfidenceEl.textContent=confidenceLabel(confidence)+' ('+confidence+'%)';factorsEl.innerHTML=top.result.reasons.slice(0,5).map(function(item){return '<li>'+item+'</li>';}).join('');weekEl.innerHTML=forecast.map(function(day){var d=new Date(day.day+'T12:00:00'),lvl=riskLevel(day.score);return '<div class="va-home-radar__day is-'+lvl.cls+'"><div class="va-home-radar__dayname">'+dayName(d.getDay())+'</div><div class="va-home-radar__daydate">'+formatDateShort(d)+'</div><div class="va-home-radar__dayscore">'+day.score+'<small>/100</small></div><div class="va-home-radar__dayspecies">'+speciesMeta[day.species].label+'</div></div>';}).join('');renderHeatmap(matrix);noteEl.textContent='Helyzet: '+label+' • Kultúra: '+(cropLabels[cropEl.value]||cropEl.value)+' • Fenológia: '+(phenologyEl.options[phenologyEl.selectedIndex]?phenologyEl.options[phenologyEl.selectedIndex].text:'-')+' • A számítás 2 múlt nap + 7 nap órás adataiból készül (sok száz mérési pont), a pontszám 0-100 skálájú kockázati index.';}
  function fetchForecast(lat,lon,label){var url='https://api.open-meteo.com/v1/forecast?latitude='+encodeURIComponent(lat)+'&longitude='+encodeURIComponent(lon)+'&current=temperature_2m,relative_humidity_2m,precipitation,weather_code,wind_speed_10m,pressure_msl,cloud_cover&hourly=temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,pressure_msl,cloud_cover,soil_moisture_0_to_1cm&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max&past_days=2&forecast_days=7&timezone=auto&wind_speed_unit=kmh';fetch(url).then(function(r){return r.json();}).then(function(json){render(json,label);}).catch(function(){locEl.textContent='Nincs kapcsolat';reasonEl.textContent='Az élő meteorológia most nem elérhető, ezért nincs friss vadkár-index.';});}
  function rerender(){if(weatherData){render(weatherData,locEl.textContent||'Aktuális hely');}}
  if(toggleEl){toggleEl.addEventListener('click',function(){setRadarModal(true);});setRadarModal(false);}
  if(modalCloseEl){modalCloseEl.addEventListener('click',function(){setRadarModal(false);});}
  if(modalBackdropEl){modalBackdropEl.addEventListener('click',function(){setRadarModal(false);});}
  if(pickerCloseEl){pickerCloseEl.addEventListener('click',function(){setPickerModal(false);});}
  if(pickerBackdropEl){pickerBackdropEl.addEventListener('click',function(){setPickerModal(false);});}
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&pickerEl&&!pickerEl.hidden){setPickerModal(false);return;}if(e.key==='Escape'&&modalEl&&!modalEl.hidden){setRadarModal(false);}});
  if(modalEl){modalEl.addEventListener('click',function(e){if(e.target===modalEl){setRadarModal(false);}});}
  setPhenologyOptions();
  setupPicker(cropEl,'Kultúra');
  setupPicker(forestEl,'Erdőközel');
  setupPicker(damageEl,'Kárelőzmény');
  setupPicker(phenologyEl,'Fenológia');
  setupPicker(huntingEl,'Vadásznyomás');
  setupPicker(fenceEl,'Villanypásztor');
  setupPicker(forestFoodEl,'Erdei táplálék');
  setupPicker(waterEl,'Vizes élőhely');
  setupPicker(herdEl,'Csordaméret');
  forestEl.addEventListener('change',function(){syncPickerButton(forestEl);rerender();});
  damageEl.addEventListener('change',function(){syncPickerButton(damageEl);rerender();});
  cropEl.addEventListener('change',function(){syncPickerButton(cropEl);setPhenologyOptions();rerender();});
  phenologyEl.addEventListener('change',function(){syncPickerButton(phenologyEl);rerender();});
  huntingEl.addEventListener('change',function(){syncPickerButton(huntingEl);rerender();});
  fenceEl.addEventListener('change',function(){syncPickerButton(fenceEl);rerender();});
  forestFoodEl.addEventListener('change',function(){syncPickerButton(forestFoodEl);rerender();});
  waterEl.addEventListener('change',function(){syncPickerButton(waterEl);rerender();});
  herdEl.addEventListener('change',function(){syncPickerButton(herdEl);rerender();});
  if(navigator.geolocation){navigator.geolocation.getCurrentPosition(function(pos){fetchForecast(pos.coords.latitude,pos.coords.longitude,'Aktuális hely');},function(){fetchForecast(47.093,17.911,'Veszprém mintahely');},{enableHighAccuracy:true,timeout:7000,maximumAge:900000});}else{fetchForecast(47.093,17.911,'Veszprém mintahely');}
})();
</script>

<?php if ( $va_show_season_widget ): ?>
<script>
/* ════════════════════════════════════════════════════════════
   VADÁSZATI IDÉNY SIDEBAR WIDGET
   Forrás: 79/2004. (V.4.) FVM rendelet
   ════════════════════════════════════════════════════════════ */
(function(){
  /* Budapest idő */
  function nowBP(){
    var d=new Date(), parts={};
    new Intl.DateTimeFormat('en-US',{
      timeZone:'Europe/Budapest',
      year:'numeric',month:'2-digit',day:'2-digit',
      hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false
    }).formatToParts(d).forEach(function(p){if(p.type!=='literal')parts[p.type]=+p.value;});
    return parts; // month:1-12
  }

  var MFH=['január','február','március','április','május','június',
           'július','augusztus','szeptember','október','november','december'];
  var MF=['Január','Február','Március','Április','Május','Június',
          'Július','Augusztus','Szeptember','Október','November','December'];

  /* Szökőév: 2026 nem szökőév */
  var MD=[31,28,31,30,31,30,31,31,30,31,30,31];
  var TOTAL=365;

  function doy(m,d){var i=0;for(var x=1;x<m;x++)i+=MD[x-1];return i+d-1;}

  function isInSeason(seasons,m,d){
    var t=doy(m,d);
    for(var i=0;i<seasons.length;i++){
      var s=seasons[i],ms=s[0],ds=s[1],me=s[2],de=s[3];
      if(ms<=me){if(t>=doy(ms,ds)&&t<=doy(me,de))return true;}
      else{if(t>=doy(ms,ds)||t<=doy(me,de))return true;}
    }
    return false;
  }

  function nextChangeMs(seasons,m,d){
    var cur=isInSeason(seasons,m,d),nm=m,nd=d;
    for(var i=1;i<=400;i++){
      nd++;if(nd>MD[nm-1]){nd=1;nm++;}if(nm>12)nm=1;
      if(isInSeason(seasons,nm,nd)!==cur)return{m:nm,d:nd};
    }
    return null;
  }

  function msTillDate(tm,td){
    var bp=nowBP();
    var msToMidnight=((23-bp.hour)*3600+(59-bp.minute)*60+(60-bp.second))*1000;
    var nm=bp.month,nd=bp.day,days=0;
    while(!(nm===tm&&nd===td)){
      nd++;if(nd>MD[nm-1]){nd=1;nm++;}if(nm>12)nm=1;
      days++;if(days>400)break;
    }
    return msToMidnight+days*86400000;
  }

  function fmtMs(ms){
    if(ms<=0)return '0s';
    var s=Math.floor(ms/1000),
        dn=Math.floor(s/86400),
        h=Math.floor((s%86400)/3600),
        mn=Math.floor((s%3600)/60),
        sc=s%60;
    if(dn>0)return dn+'n '+h+'ó';
    if(h>0)return h+'ó '+mn+'p';
    return mn+'p '+sc+'s';
  }

  /* Vadászati adatok */
  var groups=[
    {label:'GÍM',color:'#1ec854',animals:[
      {name:'Gímszarvas – bika',sub:'Cervus elaphus ♂',seasons:[[9,1,12,31]]},
      {name:'Gímszarvas – tehén/ünő',sub:'Cervus elaphus ♀',seasons:[[10,1,1,31]]},
      {name:'Gímszarvas – borjú',sub:'Cervus elaphus juv.',seasons:[[8,1,1,31]]},
    ]},
    {label:'DÁM',color:'#f5a623',animals:[
      {name:'Dámszarvas – bak',sub:'Dama dama ♂',seasons:[[9,1,12,31]]},
      {name:'Dámszarvas – suta',sub:'Dama dama ♀',seasons:[[10,1,1,31]]},
      {name:'Dámszarvas – gida',sub:'Dama dama juv.',seasons:[[8,1,1,31]]},
    ]},
    {label:'ŐZ',color:'#3d9ff5',animals:[
      {name:'Őzbak',sub:'Capreolus capreolus ♂',seasons:[[4,15,9,30]]},
      {name:'Őzsuta',sub:'Capreolus capreolus ♀',seasons:[[10,1,1,31]]},
      {name:'Őzgida',sub:'Capreolus capreolus juv.',seasons:[[8,1,1,31]]},
    ]},
    {label:'VADNYÚL',color:'#cc44cc',animals:[
      {name:'Vadnyúl',sub:'Lepus europaeus',seasons:[[10,1,12,31]]},
    ]},
    {label:'VADDISZNÓ',color:'#f55050',animals:[
      {name:'Vaddisznó – kan',sub:'Sus scrofa ♂',seasons:[[1,1,12,31]]},
      {name:'Vaddisznó – koca',sub:'Sus scrofa ♀',seasons:[[7,1,12,31]]},
      {name:'Vaddisznó – malac',sub:'Sus scrofa juv.',seasons:[[7,1,12,31]]},
    ]},
    {label:'APRÓVAD',color:'#f5d020',animals:[
      {name:'Mezei nyúl',sub:'Lepus europaeus',seasons:[[10,1,12,31]]},
      {name:'Fácán – kakas',sub:'Phasianus colchicus ♂',seasons:[[10,1,1,31]]},
      {name:'Fácán – tyúk',sub:'Phasianus colchicus ♀',seasons:[[10,1,11,30]]},
      {name:'Fogoly',sub:'Perdix perdix',seasons:[[10,1,11,30]]},
      {name:'Balkáni gerle',sub:'Streptopelia decaocto',seasons:[[6,1,2,28]]},
      {name:'Vadgalamb',sub:'Columba palumbus',seasons:[[8,15,1,31]]},
      {name:'Erdei szalonka',sub:'Scolopax rusticola',seasons:[[3,1,4,30],[10,1,11,30]]},
    ]},
    {label:'SZÁRNYASVAD',color:'#20d4d4',animals:[
      {name:'Fürj',sub:'Coturnix coturnix',seasons:[[8,15,10,31]]},
      {name:'Seregély',sub:'Sturnus vulgaris',seasons:[[8,1,1,31]]},
    ]},
    {label:'VÍZI',color:'#ff8c00',animals:[
      {name:'Tőkés réce – gácsér',sub:'Anas platyrhynchos ♂',seasons:[[8,15,1,31]]},
      {name:'Tőkés réce – tojó',sub:'Anas platyrhynchos ♀',seasons:[[8,15,1,31]]},
      {name:'Böjti réce',sub:'Anas querquedula',seasons:[[8,15,11,30]]},
      {name:'Nyílfarkú réce',sub:'Anas acuta',seasons:[[8,15,1,31]]},
      {name:'Nagy lilik',sub:'Anser albifrons',seasons:[[10,1,1,31]]},
      {name:'Vetési lúd',sub:'Anser fabalis',seasons:[[10,1,1,31]]},
      {name:'Nyári lúd',sub:'Anser anser',seasons:[[8,15,1,31]]},
    ]},
    {label:'RAGADOZÓ',color:'#ffb300',animals:[
      {name:'Vörösróka',sub:'Vulpes vulpes',seasons:[[1,1,12,31]]},
      {name:'Aranysakál',sub:'Canis aureus',seasons:[[1,1,12,31]]},
      {name:'Borz',sub:'Meles meles',seasons:[[9,1,11,30]]},
      {name:'Nyest / Nyuszt',sub:'Martes foina / M. martes',seasons:[[10,1,2,28]]},
      {name:'Dolmányos varjú',sub:'Corvus cornix',seasons:[[1,1,12,31]]},
      {name:'Szarka',sub:'Pica pica',seasons:[[1,1,12,31]]},
    ]},
  ];

  /* Countdown elemeinek nyilvántartása */
  var _cdList=[];

  function updateCDs(){
    for(var i=0;i<_cdList.length;i++){
      var c=_cdList[i], ms=msTillDate(c.m,c.d);
      c.el.textContent=fmtMs(ms)+(c.open?' van hátra':' múlva nyílik');
    }
  }

  function build(){
    var bp=nowBP(), tm=bp.month, td=bp.day;
    var todayDoy=doy(tm,td);
    var open=[], soon=[], seen={};

    for(var gi=0;gi<groups.length;gi++){
      var g=groups[gi];
      for(var ai=0;ai<g.animals.length;ai++){
        var a=g.animals[ai];
        if(seen[a.name])continue;
        var inSeason=isInSeason(a.seasons,tm,td);
        if(inSeason){
          /* daysLeft: közelebb találjuk meg a zárást */
          var nxt=nextChangeMs(a.seasons,tm,td);
          var dLeft=nxt?(doy(nxt.m,nxt.d)-todayDoy+TOTAL)%TOTAL:9999;
          var neverCloses=!nxt;
          var isNew=(doy(a.seasons[0][0],a.seasons[0][1])===todayDoy);
          open.push({name:a.name,sub:a.sub,color:g.color,
                     daysLeft:dLeft,isNew:isNew,neverCloses:neverCloses,
                     closeM:nxt?nxt.m:12,closeD:nxt?nxt.d:31,
                     closing:nxt?(MF[nxt.m-1]+' '+nxt.d+'.'):'Dec 31.'});
          seen[a.name]=1;
        } else {
          /* hamarosan nyílik (14 napon belül)? */
          for(var si=0;si<a.seasons.length;si++){
            var ss=a.seasons[si];
            var sd=doy(ss[0],ss[1]);
            var diff=sd-todayDoy;
            if(diff>0&&diff<=14&&!seen[a.name]){
              soon.push({name:a.name,sub:a.sub,color:g.color,
                         openIn:diff,openM:ss[0],openD:ss[1],
                         openDate:MF[ss[0]-1]+' '+ss[1]+'.'});
              seen[a.name]=2; break;
            }
          }
        }
      }
    }

    open.sort(function(a,b){
      if(a.isNew&&!b.isNew)return -1;if(!a.isNew&&b.isNew)return 1;
      var aU=a.daysLeft<=7,bU=b.daysLeft<=7;
      if(aU&&!bU)return -1;if(!aU&&bU)return 1;
      return a.daysLeft-b.daysLeft;
    });
    soon.sort(function(a,b){return a.openIn-b.openIn;});

    /* Date display */
    var dateEl=document.getElementById('sw-date');
    if(dateEl)dateEl.textContent=bp.year+'. '+MFH[tm-1]+' '+td+'.';

    var cntEl=document.getElementById('sw-open-cnt');
    if(cntEl)cntEl.textContent=open.length+' faj vadászható ma';

    /* Open list */
    var openEl=document.getElementById('sw-open');
    if(!openEl)return;
    openEl.innerHTML='';
    _cdList=[];

    for(var oi=0;oi<open.length;oi++){
      var a=open[oi];
      var urgency=a.neverCloses?'ok':(a.daysLeft===0?'urgent':a.daysLeft<=7?'urgent':a.daysLeft<=14?'warn':'ok');
      var row=document.createElement('div');
      row.className='sw-row'+(a.isNew?' sw-row--new':'')+(a.daysLeft<=7&&!a.isNew&&!a.neverCloses?' sw-row--closing':'');
      row.style.borderLeftColor=a.color;
      var cdId='sw-cd-'+oi;
      row.innerHTML='<div class="sw-dot sw-dot--on"></div>'
        +'<div class="sw-body">'
        +'<div class="sw-name">'+a.name+(a.daysLeft<=7&&!a.isNew&&!a.neverCloses?' <span class="sw-closing-lbl">&#9888;&#9888; Z&Aacute;RUL</span>':'')+'</div>'
        +'<div class="sw-sub">'+a.sub+'</div>'
        +'<div class="sw-cd sw-cd--'+urgency+'" id="'+cdId+'">'+(a.neverCloses?'Egész évben':'…')+'</div>'
        +'</div>';
      openEl.appendChild(row);
      if(!a.neverCloses)_cdList.push({el:document.getElementById(cdId),m:a.closeM,d:a.closeD,open:true});
    }

    if(!open.length){
      openEl.innerHTML='<div class="sw-empty">Ma nincs nyitott idény.</div>';
    }

    /* Soon section */
    var soonLbl=document.getElementById('sw-soon-lbl');
    var soonEl=document.getElementById('sw-soon');
    if(!soonEl)return;
    soonEl.innerHTML='';

    if(soon.length){
      if(soonLbl)soonLbl.textContent='Hamarosan nyílik';
      for(var si2=0;si2<soon.length;si2++){
        var a2=soon[si2];
        var row2=document.createElement('div');
        row2.className='sw-row sw-row--soon';
        row2.style.borderLeftColor=a2.color;
        var cdId2='sw-cd-soon-'+si2;
        row2.innerHTML='<div class="sw-dot sw-dot--off"></div>'
          +'<div class="sw-body">'
          +'<div class="sw-name">'+a2.name+'</div>'
          +'<div class="sw-cd sw-cd--soon" id="'+cdId2+'">…</div>'
          +'</div>';
        soonEl.appendChild(row2);
        _cdList.push({el:document.getElementById(cdId2),m:a2.openM,d:a2.openD,open:false});
      }
    } else {
      if(soonLbl)soonLbl.textContent='';
    }

    updateCDs();
  }

  /* Azonnali hívás — elemek már a DOM-ban vannak */
  build();
  setInterval(updateCDs,1000);
  /* Napi újraépítés éjfélkor */
  var bp=nowBP();
  var msToMidnight=((23-bp.hour)*3600+(59-bp.minute)*60+(60-bp.second)+1)*1000;
  setTimeout(function(){build();setInterval(build,86400000);},msToMidnight);
})();
</script>
<?php endif; ?>

<!-- FŐ TARTALOM -->
<main class="va-home-main">

<!-- VADÁSZ NAPTÁR GANTT -->
<?php if ( $va_show_home_hunting_calendar ): ?>
<div class="va-hnaptar" id="va-hnaptar-wrap">
<div class="va-hnaptar__hd">
  <span class="va-hnaptar__title">&#127993; Vad&aacute;szati id&eacute;nyek 2026</span>
  <span class="va-hnaptar__clock" id="va-hn-clock">&ndash;</span>
  <span class="va-hnaptar__sub">
    <span class="va-hnaptar__sun" id="va-hn-sun">&ndash;</span>
    <span>Csoportra kattintva &ouml;sszecsukhat&oacute; &middot; <span style="color:#ff0000;font-weight:700;">|</span> = ma</span>
  </span>
</div>
<div class="va-hnaptar__legend" id="va-hn-legend"></div>
<div class="va-hnaptar__meta">
  <span class="va-hnaptar__hint" id="va-hn-hint">Húzd oldalra a naptárat</span>
</div>
<div class="va-hnaptar__scroll">
  <div class="va-hnaptar__chart" id="va-hn-chart"></div>
</div>
</div>

<style>
.va-hnaptar{--va-hn-name-w:160px;margin-bottom:24px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);border-radius:10px;overflow:visible;}
.va-hnaptar__hd{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;padding:12px 20px 10px;border-bottom:1px solid rgba(255,0,0,.12);}
.va-hnaptar__title{font-size:.88rem;font-weight:800;letter-spacing:.06em;color:#fff;}
.va-hnaptar__clock{font-size:.72rem;font-weight:700;color:rgba(255,180,0,.85);letter-spacing:.06em;font-variant-numeric:tabular-nums;white-space:nowrap;}
.va-hnaptar__sub{font-size:.68rem;color:rgba(255,255,255,.35);display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.va-hnaptar__sun{display:inline-flex;align-items:center;gap:8px;font-size:.66rem;color:rgba(255,255,255,.72);font-variant-numeric:tabular-nums;}
.va-hnaptar__sun-item{display:inline-flex;align-items:center;gap:4px;}
.va-hnaptar__sun-ico{width:14px;height:14px;display:inline-block;vertical-align:middle;}
.va-hnaptar__sun-ico--rise{color:#ffb347;}
.va-hnaptar__sun-ico--set{color:#ff6a3d;}
.va-hnaptar__sun-item--trophy{color:rgba(255,255,255,.88);}
.va-hnaptar__legend{display:flex;flex-wrap:wrap;gap:5px 14px;padding:8px 16px;border-bottom:1px solid rgba(255,255,255,.05);}
.va-hn-leg{display:flex;align-items:center;gap:5px;font-size:.65rem;color:rgba(255,255,255,.6);}
.va-hn-leg-dot{width:13px;height:8px;border-radius:2px;flex-shrink:0;}
.va-hnaptar__meta{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 16px;border-bottom:1px solid rgba(255,255,255,.05);}
.va-hnaptar__hint{font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.42);opacity:0;transform:translateX(-6px);transition:all .25s ease;}
.va-hnaptar.has-overflow .va-hnaptar__hint{opacity:1;transform:none;}
.va-hnaptar.is-scrolled .va-hnaptar__hint{opacity:.2;}
.va-hnaptar__scroll{overflow-x:auto;overflow-y:visible;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:#171717 #060606;}
.va-hnaptar__scroll::-webkit-scrollbar{height:12px;}
.va-hnaptar__scroll::-webkit-scrollbar-track{background:#060606;}
.va-hnaptar__scroll::-webkit-scrollbar-thumb{background:#171717;border:1px solid #2a2a2a;border-radius:999px;}
.va-hnaptar__scroll::-webkit-scrollbar-thumb:hover{background:#232323;}
.va-hnaptar__chart{min-width:980px;overflow:visible;}
/* month header */
.va-hn-mh{display:flex;position:sticky;top:0;z-index:10;background:rgb(12,12,12);border-bottom:1px solid rgba(255,255,255,.08);}
.va-hn-mh-name{width:var(--va-hn-name-w);min-width:var(--va-hn-name-w);flex-shrink:0;padding:6px 10px;font-size:.6rem;color:rgba(255,255,255,.35);letter-spacing:.07em;text-transform:uppercase;border-right:1px solid rgba(255,255,255,.07);}
.va-hn-mh-name,.va-hn-gl,.va-hn-name{position:sticky;left:0;z-index:6;}
.va-hn-mh-name{z-index:12;background:rgb(12,12,12);}
.va-hn-gl{z-index:9;background:rgb(20,10,10);}
.va-hn-name{z-index:8;background:rgb(10,10,10);}
.va-hn-mh-name::after,.va-hn-gl::after,.va-hn-name::after{content:'';position:absolute;top:0;right:-12px;width:12px;height:100%;pointer-events:none;background:linear-gradient(90deg,rgba(255,0,0,.18),rgba(255,0,0,0));}
.va-hn-mh-months{flex:1;display:flex;position:relative;}
.va-hn-mh-month{flex:1;text-align:center;font-size:.6rem;font-weight:700;color:rgba(255,255,255,.45);padding:6px 0;border-left:1px solid rgba(255,255,255,.07);}
.va-hn-mh-month.cur{color:#ff0000;}
/* group header */
.va-hn-gh{display:flex;align-items:center;background:rgba(255,0,0,.07);border-top:1px solid rgba(255,0,0,.15);cursor:pointer;user-select:none;transition:background .15s;}
.va-hn-gh:hover{background:rgba(255,0,0,.13);}
.va-hn-gl{width:var(--va-hn-name-w);min-width:var(--va-hn-name-w);flex-shrink:0;padding:7px 10px;font-size:.62rem;font-weight:700;color:#ff0000;letter-spacing:.09em;text-transform:uppercase;border-right:1px solid rgba(255,0,0,.15);display:flex;align-items:center;gap:5px;}
.va-hn-arr{font-size:.95rem;font-weight:900;line-height:1;transition:transform .2s;display:inline-block;}
.va-hn-gl.collapsed .va-hn-arr{transform:rotate(-90deg);}
.va-hn-gl-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;background:#ff3030;box-shadow:0 0 5px rgba(255,48,48,.4);}
.va-hn-gl.has-open{color:#00e060;}
.va-hn-gl.has-open .va-hn-gl-dot{background:#00e060;box-shadow:0 0 7px #00e060;animation:va-hn-blink .9s ease-in-out infinite;}
.va-hn-gh-status{position:absolute;top:50%;right:12px;transform:translateY(-50%);font-size:.6rem;font-weight:700;letter-spacing:.03em;pointer-events:none;}
.va-hn-gba{flex:1;position:relative;height:28px;}
/* animal row */
.va-hn-row{display:flex;align-items:center;border-bottom:1px solid rgba(255,255,255,.035);min-height:32px;transition:background .12s;}
.va-hn-row:hover{background:rgba(255,255,255,.035);}
.va-hn-name{width:var(--va-hn-name-w);min-width:var(--va-hn-name-w);flex-shrink:0;padding:4px 10px;font-size:.68rem;font-weight:600;color:#fff;border-right:1px solid rgba(255,255,255,.05);line-height:1.25;}
.va-hn-name .sub{display:block;font-size:.56rem;font-weight:400;color:rgba(255,255,255,.3);margin-top:1px;font-style:italic;}
.va-hn-acd{display:flex;align-items:center;gap:3px;margin-top:3px;font-size:.52rem;font-weight:700;}
.va-hn-acd-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.va-hn-acd.on .va-hn-acd-dot{background:#00ff66;box-shadow:0 0 5px #00ff66;animation:va-hn-blink .9s ease-in-out infinite;}
.va-hn-acd.off .va-hn-acd-dot{background:#ff3030;}
.va-hn-acd.on .va-hn-acd-txt{color:#00ff66;}
.va-hn-acd.off .va-hn-acd-txt{color:#ff5050;}
.va-hn-acd-lbl{color:rgba(255,255,255,.28);font-weight:400;font-size:.5rem;}
@keyframes va-hn-blink{0%,100%{opacity:1}50%{opacity:.2}}
.va-hn-ba{flex:1;position:relative;height:32px;}
.va-hn-ba,.va-hn-gba{--mp:8.3333%;}
.va-hn-ba::before,.va-hn-gba::before{content:'';position:absolute;inset:0;pointer-events:none;background:repeating-linear-gradient(90deg,transparent 0,transparent calc(var(--mp) - 1px),rgba(255,255,255,.04) calc(var(--mp) - 1px),rgba(255,255,255,.04) var(--mp));}
.va-hn-tl{position:absolute;top:0;bottom:0;width:2px;background:rgba(255,0,0,.85);z-index:5;pointer-events:none;}
.va-hn-tlbl{position:absolute;top:2px;font-size:.5rem;color:#ff0000;font-weight:700;white-space:nowrap;transform:translateX(-50%);}
.va-hn-sbar{position:absolute;top:50%;transform:translateY(-50%);height:14px;border-radius:3px;opacity:.85;cursor:default;transition:opacity .15s,height .15s;z-index:25;}
.va-hn-sbar:hover{opacity:1;height:20px;z-index:120;}
.va-hn-sbar::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 5px);left:0;transform:none;background:rgba(0,0,0,.95);color:#fff;font-size:.6rem;padding:3px 8px;border-radius:4px;white-space:normal;max-width:min(260px,70vw);line-height:1.35;pointer-events:none;opacity:0;transition:opacity .15s;z-index:140;border:1px solid rgba(255,255,255,.1);}
.va-hn-sbar:hover::after{opacity:1;}
.va-hn-body.hidden{display:none;}
/* Closing soon – sidebar */
.sw-row--closing{background:rgba(255,30,30,.07)!important;box-shadow:0 0 10px rgba(255,0,0,.2),inset 0 0 16px rgba(255,0,0,.05);animation:sw-cl-pulse 1.5s ease-in-out infinite;}
@keyframes sw-cl-pulse{0%,100%{box-shadow:0 0 8px rgba(255,0,0,.12);}50%{box-shadow:0 0 20px rgba(255,0,0,.38),inset 0 0 14px rgba(255,0,0,.1);}}
.sw-closing-lbl{font-size:.56rem;font-weight:900;color:#ff3030;letter-spacing:.05em;margin-left:4px;vertical-align:middle;}

@media (max-width: 1024px){
  .va-hnaptar{--va-hn-name-w:136px;}
  .va-hnaptar__chart{min-width:860px;}
}
@media (max-width: 760px){
  .va-hnaptar{--va-hn-name-w:112px;}
  .va-hnaptar__hd{padding:10px 12px 8px;gap:5px;}
  .va-hnaptar__title{font-size:.78rem;letter-spacing:.05em;}
  .va-hnaptar__clock{font-size:.65rem;}
  .va-hnaptar__sub{font-size:.61rem;gap:8px;}
  .va-hnaptar__sun{order:2;width:100%;font-size:.6rem;}
  .va-hnaptar__legend{padding:7px 10px;gap:4px 10px;}
  .va-hnaptar__meta{padding:6px 10px;}
  .va-hn-leg{font-size:.58rem;}
  .va-hnaptar__chart{min-width:760px;}
  .va-hn-mh-month{font-size:.54rem;padding:5px 0;}
  .va-hn-mh-name,.va-hn-gl{font-size:.56rem;padding:6px 7px;}
  .va-hn-name{font-size:.62rem;padding:4px 7px;}
  .va-hn-name .sub{font-size:.52rem;}
}
@media (max-width: 560px){
  .va-hnaptar{--va-hn-name-w:96px;}
  .va-hnaptar__chart{min-width:680px;}
  .va-hn-gh-status{right:8px;font-size:.54rem;}
  .va-hn-sbar{height:12px;}
  .va-hn-sbar:hover{height:16px;}
}
@media (max-width: 420px){
  .va-hnaptar{--va-hn-name-w:82px;}
  .va-hnaptar__chart{min-width:620px;}
  .va-hn-mh-name,.va-hn-gl,.va-hn-name{padding-left:6px;padding-right:6px;}
  .va-hn-gl{letter-spacing:.05em;}
  .va-hn-name{font-size:.58rem;}
  .va-hn-name .sub{display:none;}
  .va-hnaptar__hint{font-size:.56rem;}
}
</style>

<script>
(function(){
'use strict';
var YEAR=2026;
var MD=[31,28,31,30,31,30,31,31,30,31,30,31];
var MN=["Jan","Feb","M\u00e1r","\u00c1pr","M\u00e1j","J\u00fan","J\u00fal","Aug","Szep","Okt","Nov","Dec"];
var MF=["Janu\u00e1r","Febru\u00e1r","M\u00e1rcius","\u00c1prilis","M\u00e1jus","J\u00fanius","J\u00falius","Augusztus","Szeptember","Okt\u00f3ber","November","December"];
var TOTAL=365;
var PI=Math.PI,sin=Math.sin,cos=Math.cos,asin=Math.asin,acos=Math.acos,rad=PI/180,e=rad*23.4397;
var TROPHY_SPECIES=[
  {name:'Gímszarvas – bika',label:'Gímbika'},
  {name:'Dámszarvas – bak',label:'Dámbika'},
  {name:'Őzbak',label:'Őzbak'},
  {name:'Vadnyúl',label:'Vadnyúl'}
];
function doy(m,d){var i=0;for(var x=1;x<m;x++)i+=MD[x-1];return i+d-1;}
function nowBPsimple(){var d=new Date(),parts={};new Intl.DateTimeFormat('en-US',{timeZone:'Europe/Budapest',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).formatToParts(d).forEach(function(p){if(p.type!=='literal')parts[p.type]=+p.value;});return parts;}
function sunDeclination(L){return asin(sin(e)*sin(L));}
function toJulian(date){return date.valueOf()/86400000-.5+2440588;}
function fromJulian(j){return new Date((j+0.5-2440588)*86400000);}
function toDays(date){return toJulian(date)-2451545;}
function solarMeanAnomaly(d){return rad*(357.5291+0.98560028*d);}
function eclipticLongitude(M){var C=rad*(1.9148*sin(M)+0.02*sin(2*M)+0.0003*sin(3*M)),P=rad*102.9372;return M+C+P+PI;}
function julianCycle(d,lw){return Math.round(d-0.0009-lw/(2*PI));}
function approxTransit(Ht,lw,n){return 0.0009+(Ht+lw)/(2*PI)+n;}
function solarTransitJ(ds,M,L){return 2451545+ds+0.0053*sin(M)-0.0069*sin(2*L);}
function hourAngle(h,phi,dec){return acos((sin(h)-sin(phi)*sin(dec))/(cos(phi)*cos(dec)));}
function tzOffsetMinutes(date,tz){
  var p={};
  new Intl.DateTimeFormat('en-US',{timeZone:tz,year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).formatToParts(date).forEach(function(x){if(x.type!=='literal')p[x.type]=+x.value;});
  var asUtc=Date.UTC(p.year,p.month-1,p.day,p.hour,p.minute,p.second);
  return (asUtc-date.getTime())/60000;
}
function getSunTimesBp(year,month,day){
  var lat=47.4979,lng=19.0402,lw=rad*-lng,phi=rad*lat;
  var localNoonUtcGuess=new Date(Date.UTC(year,month-1,day,12,0,0));
  var off=tzOffsetMinutes(localNoonUtcGuess,'Europe/Budapest');
  var localNoonUtc=new Date(localNoonUtcGuess.getTime()-off*60000);
  var d=toDays(localNoonUtc),n=julianCycle(d,lw),ds=approxTransit(0,lw,n);
  var M=solarMeanAnomaly(ds),L=eclipticLongitude(M),decl=sunDeclination(L);
  var Jnoon=solarTransitJ(ds,M,L);
  var h0=-0.833*rad,w=hourAngle(h0,phi,decl);
  var Jset=solarTransitJ(approxTransit(w,lw,n),M,L);
  var Jrise=Jnoon-(Jset-Jnoon);
  return {rise:fromJulian(Jrise),set:fromJulian(Jset)};
}
function fmtBpHm(date){
  return new Intl.DateTimeFormat('hu-HU',{timeZone:'Europe/Budapest',hour:'2-digit',minute:'2-digit',hour12:false}).format(date);
}
function fmtHp(ms){
  var totalMin=Math.max(0,Math.floor(ms/60000));
  var h=Math.floor(totalMin/60);
  var m=totalMin%60;
  return h+' óra '+m+' percig';
}
function sunriseIcon(){
  return '<svg class="va-hnaptar__sun-ico va-hnaptar__sun-ico--rise" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 17h16"/><path d="M7 17a5 5 0 0 1 10 0"/><path d="M12 4v5"/><path d="m9.5 7.5 2.5-2.5 2.5 2.5"/></svg>';
}
function sunsetIcon(){
  return '<svg class="va-hnaptar__sun-ico va-hnaptar__sun-ico--set" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 17h16"/><path d="M7 17a5 5 0 0 1 10 0"/><path d="M12 9v5"/><path d="m9.5 11.5 2.5 2.5 2.5-2.5"/></svg>';
}
function shootIcon(){
  return '<svg class="va-hnaptar__sun-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="1.5"/><path d="M12 3v3"/><path d="M12 18v3"/><path d="M3 12h3"/><path d="M18 12h3"/></svg>';
}
function getOpenTrophySpecies(bp){
  var open=[];
  for(var gi=0;gi<groups.length;gi++){
    for(var ai=0;ai<groups[gi].animals.length;ai++){
      var animal=groups[gi].animals[ai];
      for(var ti=0;ti<TROPHY_SPECIES.length;ti++){
        var trophy=TROPHY_SPECIES[ti];
        if(animal.name===trophy.name&&isInSeason(animal.seasons,bp.month,bp.day)){
          open.push(trophy.label);
        }
      }
    }
  }
  return open;
}
function isLeap(y){return(y%4===0&&y%100!==0)||y%400===0;}
function daysInMonth(y,m){if(m===2)return isLeap(y)?29:28;return [31,0,31,30,31,30,31,31,30,31,30,31][m-1]||31;}
function nextBpDay(y,m,d){d++;if(d>daysInMonth(y,m)){d=1;m++;if(m>12){m=1;y++;}}return{y:y,m:m,d:d};}
var _sunKey='';
function updateSunInfo(bp){
  var sunEl=document.getElementById('va-hn-sun');
  if(!sunEl)return;
  var key=bp.year+'-'+bp.month+'-'+bp.day;
  if(key===_sunKey)return;
  _sunKey=key;
  var st=getSunTimesBp(bp.year,bp.month,bp.day);
  sunEl.innerHTML='<span class="va-hnaptar__sun-item">'+sunriseIcon()+'Napkelte '+fmtBpHm(st.rise)+'</span>'
    +'<span class="va-hnaptar__sun-item">'+sunsetIcon()+'Napnyugta '+fmtBpHm(st.set)+'</span>'
    +'<span class="va-hnaptar__sun-item va-hnaptar__sun-item--trophy">'+shootIcon()+'<span id="va-hn-shoot-cd">&ndash;</span></span>';
}
function updateShootCountdown(bp){
  var el=document.getElementById('va-hn-shoot-cd');
  if(!el)return;
  var wrap=el.closest('.va-hnaptar__sun-item');
  var openSpecies=getOpenTrophySpecies(bp);

  var now=new Date();
  var stToday=getSunTimesBp(bp.year,bp.month,bp.day);
  var startToday=stToday.rise; // Lövési idő kezdete: napkelte
  var cutoffToday=new Date(stToday.set.getTime()+3600000); // napnyugta + 1 óra

  if(now>=startToday&&now<=cutoffToday&&openSpecies.length>0){
    if(wrap)wrap.style.display='inline-flex';
    el.textContent=openSpecies.join(', ')+' — a mai napon lőhető még '+fmtHp(cutoffToday-now);
    return;
  }

  if(now<startToday&&openSpecies.length>0){
    if(wrap)wrap.style.display='inline-flex';
    el.textContent=openSpecies.join(', ')+' — ma napkeltétől ('+fmtBpHm(startToday)+') lőhető';
    return;
  }

  // Ha ma már tilalom van, ne jelenjen meg a trófeás visszaszámláló.
  if(wrap)wrap.style.display='none';
}
var _bp=nowBPsimple();
var TODAY={m:_bp.month,d:_bp.day};
var TODAY_PCT=(doy(TODAY.m,TODAY.d)/TOTAL*100).toFixed(4)+'%';

function isInSeason(seasons,m,d){
  var t=doy(m,d);
  for(var i=0;i<seasons.length;i++){
    var s=seasons[i];
    if(s[0]<=s[2]){if(t>=doy(s[0],s[1])&&t<=doy(s[2],s[3]))return true;}
    else{if(t>=doy(s[0],s[1])||t<=doy(s[2],s[3]))return true;}
  }
  return false;
}
function nextSeasonChange(seasons,m,d){
  var cur=isInSeason(seasons,m,d),nm=m,nd=d;
  for(var i=1;i<=400;i++){nd++;if(nd>MD[nm-1]){nd=1;nm++;}if(nm>12)nm=1;if(isInSeason(seasons,nm,nd)!==cur)return{m:nm,d:nd};}
  return null;
}
function msTillBPDate(tm,td){
  var bp=nowBPsimple();
  var ms=((23-bp.hour)*3600+(59-bp.minute)*60+(60-bp.second))*1000;
  var nm=bp.month,nd=bp.day,days=0;
  while(!(nm===tm&&nd===td)){nd++;if(nd>MD[nm-1]){nd=1;nm++;}if(nm>12)nm=1;days++;if(days>400)break;}
  return ms+days*86400000;
}
function fmtMs(ms){
  if(ms<=0)return '0s';
  var s=Math.floor(ms/1000),dn=Math.floor(s/86400),h=Math.floor((s%86400)/3600),mn=Math.floor((s%3600)/60),sc=s%60;
  if(dn>0)return dn+'n '+h+'\u00f3';
  if(h>0)return h+'\u00f3 '+mn+'p';
  return mn+'p '+sc+'s';
}
function segs(seasons){
  var out=[];
  for(var i=0;i<seasons.length;i++){
    var s=seasons[i],ms=s[0],ds=s[1],me=s[2],de=s[3];
    if(ms<=me){var sv=doy(ms,ds),ev=doy(me,de);out.push({sp:sv/TOTAL*100,wp:(ev-sv+1)/TOTAL*100,lbl:MF[ms-1]+' '+ds+'. \u2013 '+MF[me-1]+' '+de+'.'});}
    else{var e1=doy(me,de);if(e1>=0)out.push({sp:0,wp:(e1+1)/TOTAL*100,lbl:'Jan 1. \u2013 '+MF[me-1]+' '+de+'.'});var s2=doy(ms,ds);out.push({sp:s2/TOTAL*100,wp:(TOTAL-s2)/TOTAL*100,lbl:MF[ms-1]+' '+ds+'. \u2013 Dec 31.'});}
  }
  return out;
}

var groups=[
  {label:"G\u00cdM",color:"#1ec854",animals:[
    {name:"G\u00edmszarvas \u2013 bika",sub:"Cervus elaphus \u2642",seasons:[[9,1,12,31]]},
    {name:"G\u00edmszarvas \u2013 teh\u00e9n/\u00fcn\u0151",sub:"Cervus elaphus \u2640",seasons:[[10,1,1,31]]},
    {name:"G\u00edmszarvas \u2013 borj\u00fa",sub:"Cervus elaphus juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"D\u00c1M",color:"#f5a623",animals:[
    {name:"D\u00e1mszarvas \u2013 bak",sub:"Dama dama \u2642",seasons:[[9,1,12,31]]},
    {name:"D\u00e1mszarvas \u2013 suta",sub:"Dama dama \u2640",seasons:[[10,1,1,31]]},
    {name:"D\u00e1mszarvas \u2013 gida",sub:"Dama dama juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"\u0150Z",color:"#3d9ff5",animals:[
    {name:"\u0150zbak",sub:"Capreolus capreolus \u2642",seasons:[[4,15,9,30]]},
    {name:"\u0150zsuta",sub:"Capreolus capreolus \u2640",seasons:[[10,1,1,31]]},
    {name:"\u0150zgida",sub:"Capreolus capreolus juv.",seasons:[[8,1,1,31]]},
  ]},
  {label:"VADNYÚL",color:"#cc44cc",animals:[
    {name:"Vadnyúl",sub:"Lepus europaeus",seasons:[[10,1,12,31]]},
  ]},
  {label:"VADDISZN\u00d3",color:"#f55050",animals:[
    {name:"Vaddiszn\u00f3 \u2013 kan",sub:"Sus scrofa \u2642",seasons:[[1,1,12,31]]},
    {name:"Vaddiszn\u00f3 \u2013 koca",sub:"Sus scrofa \u2640",seasons:[[7,1,12,31]]},
    {name:"Vaddiszn\u00f3 \u2013 malac",sub:"Sus scrofa juv.",seasons:[[7,1,12,31]]},
  ]},
  {label:"APR\u00d3VAD",color:"#f5d020",animals:[
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
  {label:"V\u00cdZI",color:"#ff8c00",animals:[
    {name:"T\u0151k\u00e9s r\u00e9ce \u2013 g\u00e1cs\u00e9r",sub:"Anas platyrhynchos \u2642",seasons:[[8,15,1,31]]},
    {name:"T\u0151k\u00e9s r\u00e9ce \u2013 toj\u00f3",sub:"Anas platyrhynchos \u2640",seasons:[[8,15,1,31]]},
    {name:"B\u00f6jti r\u00e9ce",sub:"Anas querquedula",seasons:[[8,15,11,30]]},
    {name:"Ny\u00edlfark\u00fa r\u00e9ce",sub:"Anas acuta",seasons:[[8,15,1,31]]},
    {name:"Nagy lilik",sub:"Anser albifrons",seasons:[[10,1,1,31]]},
    {name:"Vet\u00e9si l\u00fad",sub:"Anser fabalis",seasons:[[10,1,1,31]]},
    {name:"Ny\u00e1ri l\u00fad",sub:"Anser anser",seasons:[[8,15,1,31]]},
  ]},
  {label:"RAGADOZ\u00d3",color:"#ffb300",animals:[
    {name:"V\u00f6r\u00f6sr\u00f3ka",sub:"Vulpes vulpes",seasons:[[1,1,12,31]]},
    {name:"Aranysakal",sub:"Canis aureus",seasons:[[1,1,12,31]]},
    {name:"Borz",sub:"Meles meles",seasons:[[9,1,11,30]]},
    {name:"Nyest / Nyuszt",sub:"Martes foina / M. martes",seasons:[[10,1,2,28]]},
    {name:"Dolm\u00e1nyos varj\u00fa",sub:"Corvus cornix",seasons:[[1,1,12,31]]},
    {name:"Szarka",sub:"Pica pica",seasons:[[1,1,12,31]]},
  ]},
];

/* Jelmagyarázat */
var legEl=document.getElementById('va-hn-legend');
if(legEl){groups.forEach(function(g){var d=document.createElement('div');d.className='va-hn-leg';d.innerHTML='<div class="va-hn-leg-dot" style="background:'+g.color+'"></div>'+g.label;legEl.appendChild(d);});}

/* Chart build */
var chart=document.getElementById('va-hn-chart');
if(!chart)return;
var _acdList=[];
var hnWrap=document.getElementById('va-hnaptar-wrap');
var hnScroll=document.querySelector('#va-hnaptar-wrap .va-hnaptar__scroll');

function updateHnOverflowState(){
  if(!hnScroll||!hnWrap)return;
  var overflow=hnScroll.scrollWidth>hnScroll.clientWidth+4;
  hnWrap.classList.toggle('has-overflow',overflow);
}
if(hnScroll){
  hnScroll.addEventListener('scroll',function(){
    if(hnWrap)hnWrap.classList.add('is-scrolled');
  },{passive:true});
  window.addEventListener('resize',function(){
    updateHnOverflowState();
  });
}

function makeTL(){var t=document.createElement('div');t.className='va-hn-tl';t.style.left=TODAY_PCT;return t;}
function makeBA(h){var ba=document.createElement('div');ba.className='va-hn-ba';ba.style.height=h+'px';ba.appendChild(makeTL());return ba;}

/* Month header */
var mh=document.createElement('div');mh.className='va-hn-mh';
var mhn=document.createElement('div');mhn.className='va-hn-mh-name';mhn.textContent='Vadfaj';mh.appendChild(mhn);
var mhm=document.createElement('div');mhm.className='va-hn-mh-months';
for(var mi=0;mi<12;mi++){var md=document.createElement('div');md.className='va-hn-mh-month'+(mi===TODAY.m-1?' cur':'');md.textContent=MN[mi];mhm.appendChild(md);}
var tl0=document.createElement('div');tl0.className='va-hn-tl';tl0.style.left=TODAY_PCT;
var tlbl=document.createElement('div');tlbl.className='va-hn-tlbl';tlbl.textContent='ma';tlbl.style.left=TODAY_PCT;
mhm.appendChild(tl0);mhm.appendChild(tlbl);mh.appendChild(mhm);chart.appendChild(mh);

/* Csoportok rendez\u00e9se: legutols\u00f3bb megny\u00edlt id\u00e9ny el\u00f6l */
function grpDaysSinceOpen(g){
  var best=9999;
  for(var i=0;i<g.animals.length;i++){
    var a=g.animals[i];
    for(var j=0;j<a.seasons.length;j++){
      var s=a.seasons[j];
      if(isInSeason([s],TODAY.m,TODAY.d)){
        var d=(doy(TODAY.m,TODAY.d)-doy(s[0],s[1])+TOTAL)%TOTAL;
        if(d<best)best=d;
      }
    }
  }
  return best;
}
groups.sort(function(a,b){return grpDaysSinceOpen(a)-grpDaysSinceOpen(b);});

var _grpData=[];
var _ghList=[];
groups.forEach(function(g){
  var openCnt=0;
  for(var _oi=0;_oi<g.animals.length;_oi++){if(isInSeason(g.animals[_oi].seasons,TODAY.m,TODAY.d))openCnt++;}
  var hasOpen=openCnt>0;

  var gh=document.createElement('div');gh.className='va-hn-gh';
  var gl=document.createElement('div');gl.className='va-hn-gl collapsed'+(hasOpen?' has-open':'');
  gl.innerHTML='<span class="va-hn-gl-dot"></span><span class="va-hn-arr">\u25bc</span>'+g.label;gh.appendChild(gl);
  var gba=document.createElement('div');gba.className='va-hn-gba';gba.appendChild(makeTL());
  var gst=document.createElement('span');gst.className='va-hn-gh-status';
  gst.style.color=hasOpen?'#00e060':'rgba(255,48,48,.6)';
  gst.textContent=hasOpen?openCnt+' faj vad\u00e1szhat\u00f3':'tilalom';
  gba.appendChild(gst);gh.appendChild(gba);
  chart.appendChild(gh);

  var gbody=document.createElement('div');gbody.className='va-hn-body hidden';
  g.animals.forEach(function(a){
    var row=document.createElement('div');row.className='va-hn-row';
    var nd=document.createElement('div');nd.className='va-hn-name';
    nd.innerHTML=a.name+(a.sub?'<span class="sub">'+a.sub+'</span>':'');
    var acd=document.createElement('div');acd.className='va-hn-acd';
    acd.innerHTML='<div class="va-hn-acd-dot"></div><span class="va-hn-acd-txt"></span><span class="va-hn-acd-lbl"></span>';
    nd.appendChild(acd);_acdList.push({el:acd,seasons:a.seasons});
    row.appendChild(nd);
    var ba=makeBA(32);
    segs(a.seasons).forEach(function(s){
      var bar=document.createElement('div');bar.className='va-hn-sbar';
      bar.style.left=s.sp+'%';bar.style.width=s.wp+'%';bar.style.background=g.color;
      bar.dataset.tip=a.name+': '+s.lbl;ba.appendChild(bar);
    });
    row.appendChild(ba);gbody.appendChild(row);
  });
  chart.appendChild(gbody);
  _grpData.push({gbody:gbody,gl:gl});
  _ghList.push({gl:gl,gst:gst,animals:g.animals});
  gh.addEventListener('click',function(){var h=gbody.classList.toggle('hidden');gl.classList.toggle('collapsed',h);});
});
/* Els\u0151 csoport (legutols\u00f3bb megny\u00edlt) automatikusan kiny\u00edlik */
if(_grpData.length>0&&grpDaysSinceOpen(groups[0])<9999){
  _grpData[0].gbody.classList.remove('hidden');
  _grpData[0].gl.classList.remove('collapsed');
}

function updateCDs(){
  var bp=nowBPsimple();
  updateSunInfo(bp);
  updateShootCountdown(bp);
  /* Élő óra a Gantt fejlécben */
  var clk=document.getElementById('va-hn-clock');
  if(clk){
    var WD=['vasárnap','hétfő','kedd','szerda','csütörtök','péntek','szombat'];
    var MFN=['jan.','febr.','márc.','ápr.','máj.','jún.','júl.','aug.','szept.','okt.','nov.','dec.'];
    var jsDate=new Date();
    var dow=new Intl.DateTimeFormat('hu-HU',{timeZone:'Europe/Budapest',weekday:'short'}).format(jsDate);
    clk.textContent=bp.year+'. '+MFN[bp.month-1]+' '+bp.day+'. '+dow+' — '
      +String(bp.hour).padStart(2,'0')+':'+String(bp.minute).padStart(2,'0')+':'+String(bp.second).padStart(2,'0');
  }
  _acdList.forEach(function(c){
    var on=isInSeason(c.seasons,bp.month,bp.day);
    var nxt=nextSeasonChange(c.seasons,bp.month,bp.day);
    var dot=c.el.querySelector('.va-hn-acd-dot');
    var txt=c.el.querySelector('.va-hn-acd-txt');
    var lbl=c.el.querySelector('.va-hn-acd-lbl');
    c.el.className='va-hn-acd '+(on?'on':'off');
    if(nxt){txt.textContent=fmtMs(msTillBPDate(nxt.m,nxt.d));lbl.textContent=on?' \u203a tilalom':' \u203a ny\u00edt\u00e1s';}
    else{txt.textContent='\u2013';lbl.textContent='';}
  });
  _ghList.forEach(function(c){
    var cnt=0;
    for(var i=0;i<c.animals.length;i++){if(isInSeason(c.animals[i].seasons,bp.month,bp.day))cnt++;}
    var has=cnt>0;
    if(has){c.gl.classList.add('has-open');}else{c.gl.classList.remove('has-open');}
    c.gst.style.color=has?'#00e060':'rgba(255,48,48,.6)';
    c.gst.textContent=has?cnt+' faj vad\u00e1szhat\u00f3':'tilalom';
  });
}
updateCDs();
setInterval(updateCDs,1000);
updateHnOverflowState();
})();
</script>
<?php endif; ?>

<!-- HIRDETÉSEK -->
<div style="margin-bottom:24px;">
  <?php
  $home_latest_label = (string) get_option( 'va_home_latest_label_text', 'ÚJ' );
  $home_all_text     = (string) get_option( 'va_home_all_link_text', 'Összes →' );
  $home_acc_color    = (string) get_option( 'va_home_section_accent_color', '#e27019' );
  if ( ! preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $home_acc_color ) ) {
    $home_acc_color = '#e27019';
  }
  ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <h2 style="font-size:18px;font-weight:800;"><span style="display:inline-block;padding:1px 5px;margin-right:8px;border-radius:4px;background:<?php echo esc_attr( $home_acc_color ); ?>;color:#fff;font-size:10px;letter-spacing:.08em;vertical-align:2px;"><?php echo esc_html( $home_latest_label ); ?></span>Legújabb hirdetések</h2>
        <?php $search = get_page_by_path('va-hirdetes-kereses'); ?>
        <?php if ($search): ?>
      <a href="<?php echo esc_url(get_permalink($search)); ?>" style="color:<?php echo esc_attr( $home_acc_color ); ?>;font-size:13px;text-decoration:none;"><?php echo esc_html( $home_all_text ); ?></a>
        <?php endif; ?>
    </div>

    <?php
    // Legújabb hirdetések – boost-aware rendezés
    global $wpdb;
    $_boost_cfg     = class_exists('VA_User_Roles') ? VA_User_Roles::get_all_plan_configs() : [];
    $_boost_window  = (int) ( $_boost_cfg['_global']['boost_badge_window'] ?? 7 );
    $_boost_cutoff  = time() - $_boost_window * DAY_IN_SECONDS;
    $_latest_ids    = $wpdb->get_col( $wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} bst ON ( bst.post_id = p.ID AND bst.meta_key = 'va_boost_time' )
         WHERE p.post_type = 'va_listing' AND p.post_status = 'publish'
         ORDER BY CASE WHEN CAST(bst.meta_value AS UNSIGNED) > %d THEN 1 ELSE 0 END DESC, p.post_date DESC
         LIMIT 8",
        $_boost_cutoff
    ) );
    $latest = $_latest_ids ? new WP_Query([
        'post_type'      => 'va_listing',
        'post_status'    => 'publish',
        'post__in'       => array_map('intval', $_latest_ids),
        'orderby'        => 'post__in',
        'posts_per_page' => 8,
        'no_found_rows'  => true,
    ]) : null;
    ?>
    <?php if ($latest && $latest->have_posts()): ?>
        <div class="va-grid">
            <?php while ($latest->have_posts()): $latest->the_post();
                if (class_exists('VA_Shortcodes')) va_template('listing/card', ['post' => get_post()]);
            endwhile; wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Kiemelt hirdetések -->
<?php $featured = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'posts_per_page' => $va_home_cards_per_row, 'meta_key' => 'va_featured', 'meta_value' => '1']);
if ($featured->have_posts()): ?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">⭐ Kiemelt hirdetések</h2>
    <div class="va-grid">
        <?php while ($featured->have_posts()): $featured->the_post();
            va_template('listing/card', ['post' => get_post()]);
        endwhile; wp_reset_postdata(); ?>
    </div>
</div>
<?php endif; ?>

<!-- Futó aukciók -->
<?php if ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true ): ?>
<?php $auctions = new WP_Query(['post_type' => 'va_auction', 'post_status' => 'publish', 'posts_per_page' => $va_home_cards_per_row, 'orderby' => 'date', 'order' => 'DESC']);
if ($auctions->have_posts()): ?>
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:800;">🔨 Futó aukciók</h2>
        <?php $ap = get_page_by_path('va-aukciok'); ?>
        <?php if ($ap): ?><a href="<?php echo esc_url(get_permalink($ap)); ?>" style="color:<?php echo esc_attr( $home_acc_color ); ?>;font-size:13px;text-decoration:none;"><?php echo esc_html( $home_all_text ); ?></a><?php endif; ?>
    </div>
    <div class="va-grid">
        <?php while ($auctions->have_posts()): $auctions->the_post();
            va_template('listing/card', ['post' => get_post()]);
        endwhile; wp_reset_postdata(); ?>
    </div>
</div>
<?php endif; ?>
    <?php endif; ?>

  </main><!-- va-home-main -->
</div><!-- va-home-layout -->

<?php else: ?>
  <!-- Archívum / single / page tartalom -->
  <?php
  $req_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );
  $is_search_page_url = strpos( $req_uri, '/va-hirdetes-kereses' ) !== false;
  ?>

  <?php if ( is_page( 'va-hirdetes-kereses' ) || $is_search_page_url ): ?>
    <div class="va-wrap">
      <?php echo do_shortcode( '[va_listing_search]' ); ?>
    </div>
  <?php elseif ( have_posts() ): while ( have_posts() ): the_post(); ?>
    <?php
    $has_pb_blocks = ! empty( get_post_meta( get_the_ID(), 'va_page_blocks', true ) );
    $wrap_class    = 'va-wrap' . ( $has_pb_blocks ? ' va-wrap--pb' : '' );
    ?>
    <div class="<?php echo esc_attr( $wrap_class ); ?>">
      <?php the_content(); ?>
    </div>
  <?php endwhile; endif; ?>
<?php endif; ?>

<?php get_footer(); ?>
