<?php
/**
 * index.php – Főoldal / fallback template
 */
get_header(); ?>

<?php if ( is_front_page() ): ?>
<?php
$va_show_season_widget = get_option( 'va_show_hunting_season_widget', '1' ) === '1';
$va_show_moon_widget   = get_option( 'va_show_moon_widget', '1' ) === '1';
$va_show_weather_widget = get_option( 'va_show_weather_widget', '1' ) === '1';
$va_show_home_hunting_calendar = get_option( 'va_show_home_hunting_calendar', '1' ) === '1';
$va_home_cards_per_row = max( 2, min( 10, (int) get_option( 'va_layout_grid_cols_desktop', 4 ) ) );
$va_home_h1 = trim( wp_strip_all_tags( (string) get_option( 'va_home_hero_title', '' ) ) );
if ( $va_home_h1 === '' ) {
  $va_home_h1 = trim( wp_strip_all_tags( get_bloginfo( 'name' ) ) );
}
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
  <button type="button" class="va-weather__toggle" id="vwToggle" aria-expanded="false" aria-controls="vwPanel">7 napos előrejelzés ▼</button>
  <div class="va-weather__panel" id="vwPanel" hidden>
    <div class="va-weather__days" id="vwDays"></div>
  </div>
  <div class="va-weather__note" id="vwNote">Forrás: Open-Meteo (DWD/ECMWF/NCEP modellek).</div>
</section>
<?php endif; ?>

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
  var toggleEl=document.getElementById('vwToggle');
  var panelEl=document.getElementById('vwPanel');

  function toggleForecast(forceOpen){
    if(!toggleEl||!panelEl)return;
    var open=typeof forceOpen==='boolean' ? forceOpen : toggleEl.getAttribute('aria-expanded')!=='true';
    toggleEl.setAttribute('aria-expanded',open?'true':'false');
    toggleEl.textContent=open?'7 napos előrejelzés ▲':'7 napos előrejelzés ▼';
    panelEl.hidden=!open;
  }
  if(toggleEl){
    toggleEl.addEventListener('click',function(){toggleForecast();});
    toggleForecast(false);
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
  function render(data,label){
    if(!data||!data.current||!data.daily){
      nowEl.textContent='Nincs időjárás adat';
      return;
    }
    var c=data.current;
    var d=data.daily;
    var todayIso=todayIsoBp();
    locEl.textContent=fmtLoc(label);
    nowEl.innerHTML='<strong>'+Math.round(c.temperature_2m)+'°C</strong> • '+wcText(c.weather_code)+' <span class="va-weather__stamp">('+dateTimeHu(new Date())+')</span>';
    metaEl.textContent='Hőérzet '+Math.round(c.apparent_temperature)+'°C • Pára '+Math.round(c.relative_humidity_2m)+'% • Szél '+Math.round(c.wind_speed_10m)+' km/h ('+wdText(c.wind_direction_10m)+') • Csapadék '+(c.precipitation||0)+' mm/h • Dátum: '+dateHu(d.time[0]);

    var html='';
    var idx=[];
    for(var ii=0;ii<d.time.length;ii++){
      if(d.time[ii]!==todayIso)idx.push(ii);
    }
    for(var j=0;j<idx.length&&j<7;j++){
      var i=idx[j];
      var prefix=dayHu(d.time[i]);
      html+='<div class="va-weather__day">'
        +'<div class="va-weather__d1">'+prefix+' · '+dateHu(d.time[i])+'</div>'
        +'<div class="va-weather__d2">'+Math.round(d.temperature_2m_min[i])+' / '+Math.round(d.temperature_2m_max[i])+'°C</div>'
        +'<div class="va-weather__d3">'+wcText(d.weather_code[i])+'</div>'
        +'<div class="va-weather__d4">Csap. '+Math.round(d.precipitation_probability_max[i]||0)+'% · '+(d.precipitation_sum[i]||0).toFixed(1)+' mm · Szél '+Math.round(d.wind_speed_10m_max[i]||0)+' km/h</div>'
        +'</div>';
    }
    daysEl.innerHTML=html;
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
    {label:'MUFLON',color:'#cc44cc',animals:[
      {name:'Muflon – kos',sub:'Ovis musimon ♂',seasons:[[8,1,1,31]]},
      {name:'Muflon – juh',sub:'Ovis musimon ♀',seasons:[[10,1,1,31]]},
      {name:'Muflon – bárány',sub:'Ovis musimon juv.',seasons:[[8,1,1,31]]},
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
.va-hnaptar{--va-hn-name-w:160px;margin-bottom:24px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);border-radius:10px;overflow:hidden;}
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
.va-hnaptar__scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:#171717 #060606;}
.va-hnaptar__scroll::-webkit-scrollbar{height:12px;}
.va-hnaptar__scroll::-webkit-scrollbar-track{background:#060606;}
.va-hnaptar__scroll::-webkit-scrollbar-thumb{background:#171717;border:1px solid #2a2a2a;border-radius:999px;}
.va-hnaptar__scroll::-webkit-scrollbar-thumb:hover{background:#232323;}
.va-hnaptar__chart{min-width:980px;}
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
.va-hn-sbar{position:absolute;top:50%;transform:translateY(-50%);height:14px;border-radius:3px;opacity:.85;cursor:default;transition:opacity .15s,height .15s;}
.va-hn-sbar:hover{opacity:1;height:20px;}
.va-hn-sbar::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 5px);left:50%;transform:translateX(-50%);background:rgba(0,0,0,.95);color:#fff;font-size:.6rem;padding:3px 8px;border-radius:4px;white-space:nowrap;pointer-events:none;opacity:0;transition:opacity .15s;z-index:100;border:1px solid rgba(255,255,255,.1);}
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
  {name:'Muflon – kos',label:'Muflon kos'}
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
  var cutoffToday=new Date(stToday.set.getTime()+3600000); // napnyugta + 1 óra

  if(now<=cutoffToday&&openSpecies.length>0){
    if(wrap)wrap.style.display='inline-flex';
    el.textContent=openSpecies.join(', ')+' — a mai napon lőhető még '+fmtHp(cutoffToday-now);
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
  {label:"MUFLON",color:"#cc44cc",animals:[
    {name:"Muflon \u2013 kos",sub:"Ovis musimon \u2642",seasons:[[8,1,1,31]]},
    {name:"Muflon \u2013 juh",sub:"Ovis musimon \u2640",seasons:[[10,1,1,31]]},
    {name:"Muflon \u2013 b\u00e1r\u00e1ny",sub:"Ovis musimon juv.",seasons:[[8,1,1,31]]},
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
