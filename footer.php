            </div><!-- .va-main-content -->
<?php if ( ! is_page( 'va-fiok' ) ) : ?>
            </div><!-- .va-main-content -->

            <!-- Jobb oldalsáv -->
            <aside class="va-sidebar va-sidebar--right">
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('sidebar_right'); ?>
                <section class="va-sidebar__widget va-vadkar-radar" id="va-vadkar-radar">
                    <div class="va-vadkar__hd">
                        <div>
                            <div class="va-vadkar__eyebrow">Decision Support</div>
                            <h3 class="va-vadkar__title">Vadkár Radar</h3>
                        </div>
                        <div class="va-vadkar__status" id="vkrStatus">Betöltés...</div>
                    </div>
                    <p class="va-vadkar__lead">Fajspecifikus becslés időjárás, holdfény, fenológia, erdőtávolság és kárelőzmény alapján.</p>

                    <div class="va-vadkar__controls">
                        <label class="va-vadkar__field">
                            <span>Kultúra</span>
                            <select id="vkrCrop">
                                <option value="maize">Kukorica</option>
                                <option value="rapeseed">Repce</option>
                                <option value="sunflower">Napraforgó</option>
                                <option value="cereal">Kalászos</option>
                                <option value="alfalfa">Lucerna</option>
                                <option value="orchard">Gyümölcsös</option>
                                <option value="vineyard">Szőlő</option>
                            </select>
                        </label>
                        <label class="va-vadkar__field">
                            <span>Fenológia</span>
                            <select id="vkrPhenology"></select>
                        </label>
                        <label class="va-vadkar__field">
                            <span>Erdőtávolság</span>
                            <select id="vkrForest">
                                <option value="under_100">0-100 m</option>
                                <option value="100_300">100-300 m</option>
                                <option value="300_700">300-700 m</option>
                                <option value="700_1500">700-1500 m</option>
                                <option value="over_1500">1500 m felett</option>
                            </select>
                        </label>
                        <label class="va-vadkar__field">
                            <span>Korábbi kár</span>
                            <select id="vkrDamage">
                                <option value="7">7 napon belül</option>
                                <option value="30">30 napon belül</option>
                                <option value="90">90 napon belül</option>
                                <option value="none">Nincs ismert kár</option>
                            </select>
                        </label>
                        <label class="va-vadkar__field">
                            <span>Vadásznyomás</span>
                            <select id="vkrHunting">
                                <option value="low">Alacsony</option>
                                <option value="medium">Közepes</option>
                                <option value="high">Magas</option>
                            </select>
                        </label>
                        <label class="va-vadkar__field">
                            <span>Villanypásztor</span>
                            <select id="vkrFence">
                                <option value="0">Nincs</option>
                                <option value="1">Van, jó állapotú</option>
                            </select>
                        </label>
                    </div>

                    <div class="va-vadkar__hero">
                        <div class="va-vadkar__scorewrap">
                            <div class="va-vadkar__score" id="vkrScore">--</div>
                            <div class="va-vadkar__level" id="vkrLevel">Előrejelzés készül...</div>
                        </div>
                        <div class="va-vadkar__meta">
                            <div class="va-vadkar__metaitem">
                                <span class="va-vadkar__metak">Fő faj</span>
                                <strong id="vkrSpecies">--</strong>
                            </div>
                            <div class="va-vadkar__metaitem">
                                <span class="va-vadkar__metak">Kritikus idő</span>
                                <strong id="vkrWindow">--</strong>
                            </div>
                            <div class="va-vadkar__metaitem">
                                <span class="va-vadkar__metak">Konfidencia</span>
                                <strong id="vkrConfidence">--</strong>
                            </div>
                        </div>
                    </div>

                    <div class="va-vadkar__block">
                        <div class="va-vadkar__blocktitle">Fő kockázati okok</div>
                        <ul class="va-vadkar__factors" id="vkrFactors">
                            <li>Időjárási és szezonális adatok betöltése...</li>
                        </ul>
                    </div>

                    <div class="va-vadkar__block">
                        <div class="va-vadkar__blocktitle">Heti trend</div>
                        <div class="va-vadkar__week" id="vkrWeek"></div>
                    </div>

                    <div class="va-vadkar__note" id="vkrNote">Valid előrejelzéshez lokális táblaadat, kárnapló, kamera és vadlétszám kell. Ez az MVP ezek hiányában konzervatív becslés.</div>
                </section>
                <style>
                .va-vadkar-radar{
                    margin-top:12px;
                    border:1px solid rgba(255,255,255,.14);
                    background:
                        radial-gradient(130% 120% at 0% 0%,rgba(255,0,0,.14),transparent 52%),
                        radial-gradient(120% 120% at 100% 100%,rgba(255,160,0,.10),transparent 46%),
                        linear-gradient(160deg,rgba(10,10,10,.96),rgba(20,12,12,.96));
                    box-shadow:0 18px 34px rgba(0,0,0,.38), inset 0 0 22px rgba(255,255,255,.025);
                    color:#fff;
                }
                .va-vadkar__eyebrow{font-size:.58rem;font-weight:900;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,186,186,.76);margin-bottom:4px;}
                .va-vadkar__hd{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;margin-bottom:8px;}
                .va-vadkar__title{margin:0;font-size:1.05rem;font-weight:900;line-height:1.1;color:#fff;}
                .va-vadkar__status{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#ffb3b3;background:rgba(255,0,0,.12);border:1px solid rgba(255,0,0,.25);padding:5px 8px;border-radius:999px;white-space:nowrap;}
                .va-vadkar__lead{margin:0 0 12px;font-size:.68rem;line-height:1.55;color:rgba(255,255,255,.76);}
                .va-vadkar__controls{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;}
                .va-vadkar__field{display:flex;flex-direction:column;gap:4px;}
                .va-vadkar__field span{font-size:.56rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,214,214,.78);}
                .va-vadkar__field select{width:100%;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:#fff;padding:8px 10px;font-size:.7rem;outline:none;}
                .va-vadkar__field select option{color:#111;}
                .va-vadkar__hero{display:grid;grid-template-columns:96px 1fr;gap:10px;align-items:center;margin-bottom:12px;}
                .va-vadkar__scorewrap{display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:10px 8px;background:rgba(255,255,255,.04);}
                .va-vadkar__score{font-size:2rem;line-height:1;font-weight:900;color:#fff;}
                .va-vadkar__level{margin-top:5px;font-size:.58rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;text-align:center;color:#ffd7d7;}
                .va-vadkar__meta{display:grid;grid-template-columns:1fr;gap:7px;}
                .va-vadkar__metaitem{border:1px solid rgba(255,255,255,.1);border-radius:12px;background:rgba(255,255,255,.03);padding:7px 9px;}
                .va-vadkar__metak{display:block;font-size:.54rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,220,220,.65);margin-bottom:2px;}
                .va-vadkar__metaitem strong{font-size:.72rem;color:#fff;}
                .va-vadkar__block{margin-top:12px;}
                .va-vadkar__blocktitle{font-size:.62rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:#ffc7c7;margin-bottom:8px;}
                .va-vadkar__factors{margin:0;padding-left:16px;display:grid;gap:6px;}
                .va-vadkar__factors li{font-size:.69rem;line-height:1.45;color:rgba(255,255,255,.87);}
                .va-vadkar__week{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:6px;}
                .va-vadkar__weekcell{border-radius:12px;padding:8px 4px 7px;border:1px solid rgba(255,255,255,.1);text-align:center;background:rgba(255,255,255,.04);}
                .va-vadkar__weekday{font-size:.52rem;font-weight:900;letter-spacing:.08em;color:#fff;opacity:.8;}
                .va-vadkar__weekscore{font-size:.82rem;font-weight:900;color:#fff;margin-top:4px;}
                .va-vadkar__weekspecies{font-size:.52rem;color:rgba(255,255,255,.72);margin-top:2px;}
                .va-vadkar__weekcell.is-low{background:rgba(20,110,55,.26);border-color:rgba(50,190,95,.42);}
                .va-vadkar__weekcell.is-moderate{background:rgba(165,145,20,.24);border-color:rgba(225,190,50,.42);}
                .va-vadkar__weekcell.is-high{background:rgba(185,105,20,.24);border-color:rgba(255,160,65,.42);}
                .va-vadkar__weekcell.is-very-high{background:rgba(175,45,30,.24);border-color:rgba(255,95,75,.48);}
                .va-vadkar__weekcell.is-critical{background:rgba(110,18,26,.36);border-color:rgba(170,40,60,.58);}
                .va-vadkar__note{margin-top:12px;font-size:.62rem;line-height:1.5;color:rgba(255,255,255,.58);}
                @media (max-width:1100px){
                    .va-content-layout{display:block;}
                    .va-sidebar.va-sidebar--right{display:block !important;position:static !important;top:auto !important;width:auto !important;height:auto !important;border-left:none !important;padding:12px 0 0 !important;overflow:visible !important;}
                }
                @media (max-width:640px){
                    .va-vadkar__controls{grid-template-columns:1fr;}
                    .va-vadkar__week{grid-template-columns:repeat(4,minmax(0,1fr));}
                    .va-vadkar__hero{grid-template-columns:1fr;}
                }
                </style>
                <script>
                (function(){
                    var root=document.getElementById('va-vadkar-radar');
                    if(!root){return;}

                    var statusEl=document.getElementById('vkrStatus');
                    var cropEl=document.getElementById('vkrCrop');
                    var phenologyEl=document.getElementById('vkrPhenology');
                    var forestEl=document.getElementById('vkrForest');
                    var damageEl=document.getElementById('vkrDamage');
                    var huntingEl=document.getElementById('vkrHunting');
                    var fenceEl=document.getElementById('vkrFence');
                    var scoreEl=document.getElementById('vkrScore');
                    var levelEl=document.getElementById('vkrLevel');
                    var speciesEl=document.getElementById('vkrSpecies');
                    var windowEl=document.getElementById('vkrWindow');
                    var confidenceEl=document.getElementById('vkrConfidence');
                    var factorsEl=document.getElementById('vkrFactors');
                    var weekEl=document.getElementById('vkrWeek');
                    var noteEl=document.getElementById('vkrNote');

                    var phenologyMap={
                        maize:[['sowing','Vetés'],['emergence','Kelés'],['milk_stage','Tejes érés'],['wax_stage','Viaszérés'],['pre_harvest','Betakarítás előtt']],
                        rapeseed:[['autumn_green','Őszi zöld'],['spring_regrowth','Tavaszi újrahajtás'],['flowering','Virágzás'],['ripening','Érés']],
                        sunflower:[['emergence','Kelés'],['head_stage','Tányérképzés'],['ripening','Érés'],['pre_harvest','Betakarítás előtt']],
                        cereal:[['young','Fiatal állomány'],['heading','Kalászolás'],['ripening','Érés']],
                        alfalfa:[['fresh_regrowth','Friss sarjadás'],['post_cut','Kaszálás utáni 3-10 nap'],['mature','Fejlett állomány']],
                        orchard:[['budburst','Rügyfakadás'],['young_shoot','Fiatal hajtás'],['fruiting','Termésképzés']],
                        vineyard:[['budburst','Rügyfakadás'],['young_shoot','Fiatal hajtás'],['fruiting','Termésképzés']]
                    };
                    var speciesMeta={
                        wild_boar:{label:'Vaddisznó',short:'VD',window:'21:00-02:00'},
                        red_deer:{label:'Gímszarvas',short:'GÍM',window:'20:00-23:30'},
                        roe_deer:{label:'Őz',short:'ŐZ',window:'04:00-07:00'},
                        fallow_deer:{label:'Dámvad',short:'DÁM',window:'20:30-23:00'},
                        wild_hare:{label:'Vadnyúl',short:'VNY',window:'19:30-23:30'}
                    };
                    var cropLabels={maize:'kukorica',rapeseed:'repce',sunflower:'napraforgó',cereal:'kalászos',alfalfa:'lucerna',orchard:'gyümölcsös',vineyard:'szőlő'};
                    var speciesBase={wild_boar:9,red_deer:7,roe_deer:5,fallow_deer:6,wild_hare:4};
                    var cropScores={
                        maize:{wild_boar:15,red_deer:10,roe_deer:4,fallow_deer:8,wild_hare:3},
                        sunflower:{wild_boar:11,red_deer:6,roe_deer:7,fallow_deer:5,wild_hare:2},
                        rapeseed:{wild_boar:5,red_deer:13,roe_deer:14,fallow_deer:12,wild_hare:9},
                        cereal:{wild_boar:6,red_deer:9,roe_deer:7,fallow_deer:8,wild_hare:7},
                        alfalfa:{wild_boar:9,red_deer:11,roe_deer:10,fallow_deer:10,wild_hare:8},
                        orchard:{wild_boar:4,red_deer:8,roe_deer:7,fallow_deer:8,wild_hare:4},
                        vineyard:{wild_boar:3,red_deer:9,roe_deer:8,fallow_deer:7,wild_hare:4}
                    };
                    var forestScores={under_100:10,'100_300':8,'300_700':5,'700_1500':2,over_1500:0};
                    var damageScores={7:12,30:8,90:5,none:0};
                    var huntingScores={
                        low:{wild_boar:4,red_deer:4,roe_deer:3,fallow_deer:3,wild_hare:2},
                        medium:{wild_boar:1,red_deer:0,roe_deer:0,fallow_deer:0,wild_hare:0},
                        high:{wild_boar:-4,red_deer:-3,roe_deer:-2,fallow_deer:-2,wild_hare:-2}
                    };
                    var phenologyScores={
                        maize:{sowing:{wild_boar:12},emergence:{wild_boar:8},milk_stage:{wild_boar:15,red_deer:8,fallow_deer:7},wax_stage:{wild_boar:12,red_deer:7},pre_harvest:{wild_boar:10,red_deer:6}},
                        rapeseed:{autumn_green:{red_deer:12,roe_deer:12,fallow_deer:10,wild_hare:8},spring_regrowth:{red_deer:10,roe_deer:10,fallow_deer:8,wild_hare:6},flowering:{red_deer:5,roe_deer:4},ripening:{red_deer:3,roe_deer:2}},
                        sunflower:{emergence:{wild_boar:4,roe_deer:7},head_stage:{wild_boar:8,roe_deer:5},ripening:{wild_boar:10,roe_deer:6},pre_harvest:{wild_boar:8}},
                        cereal:{young:{red_deer:9,roe_deer:7,fallow_deer:8,wild_hare:6},heading:{red_deer:6,roe_deer:4,fallow_deer:5},ripening:{red_deer:3,roe_deer:2,fallow_deer:2}},
                        alfalfa:{fresh_regrowth:{wild_boar:8,red_deer:10,roe_deer:9,fallow_deer:9,wild_hare:6},post_cut:{wild_boar:5,red_deer:6,roe_deer:4,fallow_deer:5},mature:{red_deer:4,roe_deer:4}},
                        orchard:{budburst:{roe_deer:8,red_deer:5},young_shoot:{roe_deer:10,red_deer:7,fallow_deer:5},fruiting:{wild_boar:5,red_deer:5}},
                        vineyard:{budburst:{roe_deer:8,red_deer:5},young_shoot:{roe_deer:9,red_deer:6,fallow_deer:4},fruiting:{red_deer:6,roe_deer:5}}
                    };

                    var weatherData=null;
                    var locationLabel='Veszprém mintahely';

                    function setPhenologyOptions(){
                        var crop=cropEl.value;
                        var list=phenologyMap[crop] || [];
                        var current=phenologyEl.value;
                        phenologyEl.innerHTML='';
                        for(var i=0;i<list.length;i++){
                            var opt=document.createElement('option');
                            opt.value=list[i][0];
                            opt.textContent=list[i][1];
                            phenologyEl.appendChild(opt);
                        }
                        if(list.some(function(item){ return item[0]===current; })){ phenologyEl.value=current; }
                    }

                    function clamp(num,min,max){ return Math.max(min,Math.min(max,num)); }
                    function riskLevel(score){
                        if(score >= 85){ return {label:'Kritikus',cls:'critical'}; }
                        if(score >= 70){ return {label:'Nagyon magas',cls:'very-high'}; }
                        if(score >= 50){ return {label:'Magas',cls:'high'}; }
                        if(score >= 25){ return {label:'Mérsékelt',cls:'moderate'}; }
                        return {label:'Alacsony',cls:'low'};
                    }
                    function confidenceLabel(score){
                        if(score >= 75){ return 'Magas'; }
                        if(score >= 50){ return 'Közepes'; }
                        return 'Alacsony';
                    }
                    function moonIllumination(date){
                        var synodic=29.53058867;
                        var knownNewMoon=Date.UTC(2024,0,11,11,57,0);
                        var days=(date.getTime()-knownNewMoon)/86400000;
                        var phase=((days % synodic)+synodic)%synodic/synodic;
                        return (1-Math.cos(2*Math.PI*phase))/2;
                    }
                    function dayName(index){ return ['V','H','K','Sze','Cs','P','Szo'][index] || ''; }
                    function getCurrentControls(){
                        return {
                            crop:cropEl.value,
                            phenology:phenologyEl.value,
                            forest:forestEl.value,
                            damage:damageEl.value,
                            hunting:huntingEl.value,
                            fence:fenceEl.value === '1'
                        };
                    }
                    function getHourlyRows(data){
                        if(!data || !data.hourly || !data.hourly.time){ return []; }
                        var out=[];
                        for(var i=0;i<data.hourly.time.length;i++){
                            out.push({
                                time:data.hourly.time[i],
                                temp:Number(data.hourly.temperature_2m[i] || 0),
                                humidity:Number(data.hourly.relative_humidity_2m[i] || 0),
                                precip:Number(data.hourly.precipitation[i] || 0),
                                wind:Number(data.hourly.wind_speed_10m[i] || 0),
                                pressure:Number(data.hourly.pressure_msl[i] || 0),
                                cloud:Number(data.hourly.cloud_cover[i] || 0),
                                soil:Number(data.hourly.soil_moisture_0_to_1cm && data.hourly.soil_moisture_0_to_1cm[i] || 0)
                            });
                        }
                        return out;
                    }
                    function findHour(rows,target){
                        for(var i=0;i<rows.length;i++){
                            if(rows[i].time === target){ return rows[i]; }
                        }
                        return null;
                    }
                    function hoursBefore(rows,targetTime,count){
                        var idx=-1;
                        for(var i=0;i<rows.length;i++){
                            if(rows[i].time === targetTime){ idx=i; break; }
                        }
                        if(idx < 0){ return []; }
                        return rows.slice(Math.max(0,idx-count+1),idx+1);
                    }
                    function dayContext(data,dayIndex){
                        var rows=getHourlyRows(data);
                        var dayStr=data.daily.time[dayIndex];
                        var target=dayStr+'T22:00';
                        var night=findHour(rows,target);
                        if(!night && rows.length){
                            for(var i=0;i<rows.length;i++){
                                if(rows[i].time.indexOf(dayStr+'T21:00')===0 || rows[i].time.indexOf(dayStr+'T23:00')===0){ night=rows[i]; break; }
                            }
                        }
                        var current=data.current || {};
                        var recent=night ? hoursBefore(rows,night.time,48) : [];
                        var precip48=recent.reduce(function(sum,row){ return sum + row.precip; },0);
                        var prior24=recent.length >= 24 ? recent[recent.length-24] : null;
                        return {
                            day:dayStr,
                            weatherCode:Number(data.daily.weather_code[dayIndex] || current.weather_code || 0),
                            tempNight:night ? night.temp : Number(current.temperature_2m || 0),
                            humidity:night ? night.humidity : Number(current.relative_humidity_2m || 0),
                            wind:night ? night.wind : Number(current.wind_speed_10m || 0),
                            pressureDrop:night && prior24 ? (night.pressure - prior24.pressure) : 0,
                            cloud:night ? night.cloud : Number(current.cloud_cover || 0),
                            soil:night ? night.soil : 0,
                            precip48:precip48,
                            tempMin:Number(data.daily.temperature_2m_min[dayIndex] || 0),
                            tempMax:Number(data.daily.temperature_2m_max[dayIndex] || 0),
                            rainSum:Number(data.daily.precipitation_sum[dayIndex] || 0),
                            moon:moonIllumination(new Date(dayStr+'T22:00:00'))
                        };
                    }
                    function speciesFactor(scoreBag,points,text){
                        if(points === 0){ return; }
                        scoreBag.score += points;
                        if(points > 0){ scoreBag.factors.push({points:points,text:text}); }
                        else { scoreBag.dampeners.push({points:points,text:text}); }
                    }
                    function scoreSpecies(species,ctx,controls){
                        var bag={score:0,factors:[],dampeners:[]};
                        var speciesLabel=speciesMeta[species].label;
                        var cropLabel=cropLabels[controls.crop] || controls.crop;
                        speciesFactor(bag,speciesBase[species] || 4,speciesLabel+' alapnyomás a térségben');
                        speciesFactor(bag,(cropScores[controls.crop] && cropScores[controls.crop][species]) || 0,cropLabel+' erős táplálék-vonzás');
                        speciesFactor(bag,(((phenologyScores[controls.crop] || {})[controls.phenology] || {})[species]) || 0,'Fenológiai állapot: '+phenologyEl.options[phenologyEl.selectedIndex].text.toLowerCase());
                        speciesFactor(bag,forestScores[controls.forest] || 0,'Erdőközeli megközelítés és takart kijárás');
                        speciesFactor(bag,damageScores[controls.damage] || 0,controls.damage === 'none' ? '' : 'Korábbi káresemény erős prediktor');
                        speciesFactor(bag,(huntingScores[controls.hunting] && huntingScores[controls.hunting][species]) || 0,controls.hunting === 'high' ? 'Magas vadászati nyomás fékezi az aktivitást' : 'Alacsony zavarás növeli a kijárást');
                        if(controls.fence){ speciesFactor(bag,-15,'Villanypásztor jelentős levonás'); }

                        if(species === 'wild_boar' && ctx.precip48 > 5 && ctx.soil > 0.18){ speciesFactor(bag,5,'Eső utáni puha talaj és túrási hajlam'); }
                        if(species === 'wild_boar' && ctx.tempNight > 12 && ctx.humidity > 75){ speciesFactor(bag,4,'Meleg, párás éjszaka kedvez a vaddisznónak'); }
                        if((species === 'red_deer' || species === 'roe_deer' || species === 'fallow_deer' || species === 'wild_hare') && ctx.tempMin <= 2 && (controls.crop === 'rapeseed' || controls.crop === 'cereal' || controls.crop === 'alfalfa')){
                            speciesFactor(bag,4,'Hidegben a zöld kultúra felértékelődik');
                        }
                        if(ctx.wind > 30){ speciesFactor(bag,-5,'Erős szél csökkenti a biztonságos kijárást'); }
                        else if(ctx.wind < 8){ speciesFactor(bag,2,'Szélcsend támogatja az éjszakai mozgást'); }
                        else if(ctx.wind >= 12 && ctx.wind <= 25){ speciesFactor(bag,1,'Mérsékelt szél részben takarja a mozgást'); }
                        if(ctx.pressureDrop <= -5){ speciesFactor(bag,3,'Front előtti nyomásesés aktivitásnövelő'); }
                        if(ctx.rainSum > 6){ speciesFactor(bag,2,'Csapadékos nap után nő az éjszakai mozgás'); }

                        var effMoon=ctx.moon * (1 - (ctx.cloud / 100));
                        if(species === 'wild_boar'){
                            if(effMoon > 0.9 && controls.hunting === 'high'){ speciesFactor(bag,-2,'Világos éj és nagy vadászati nyomás óvatossá teszi'); }
                            else if(effMoon > 0.6 && controls.hunting !== 'low'){ speciesFactor(bag,-1,'Erősebb holdfény óvatosságot hoz'); }
                        } else {
                            if(effMoon > 0.9){ speciesFactor(bag,2,'Erős holdfény javítja a kijárási biztonságot'); }
                            else if(effMoon > 0.6){ speciesFactor(bag,1,'Közepes holdfény segíti a mozgást'); }
                        }

                        speciesFactor(bag,5,'Fajspecifikus esti mozgási ablak aktív');
                        bag.score=clamp(Math.round(bag.score),0,100);
                        bag.factors.sort(function(a,b){ return b.points-a.points; });
                        return bag;
                    }
                    function computeForecast(data){
                        var controls=getCurrentControls();
                        var speciesKeys=Object.keys(speciesMeta);
                        var daily=[];
                        for(var dayIndex=0;dayIndex<data.daily.time.length && dayIndex<7;dayIndex++){
                            var ctx=dayContext(data,dayIndex);
                            var ranked=speciesKeys.map(function(key){
                                return {key:key,result:scoreSpecies(key,ctx,controls)};
                            }).sort(function(a,b){ return b.result.score-a.result.score; });
                            daily.push({day:ctx.day,species:ranked[0].key,score:ranked[0].result.score,result:ranked[0].result,ctx:ctx});
                        }
                        return daily;
                    }
                    function renderForecast(data){
                        var forecast=computeForecast(data);
                        var today=forecast[0];
                        var level=riskLevel(today.score);
                        var confidence=clamp(55 + (damageEl.value !== 'none' ? 12 : 0) + (forestEl.value !== 'over_1500' ? 8 : 0) + 10 + (navigator.geolocation ? 5 : 0),0,85);
                        scoreEl.textContent=String(today.score);
                        levelEl.textContent=level.label;
                        speciesEl.textContent=speciesMeta[today.species].label;
                        windowEl.textContent=speciesMeta[today.species].window;
                        confidenceEl.textContent=confidenceLabel(confidence)+' ('+confidence+'%)';
                        statusEl.textContent=locationLabel;
                        statusEl.style.color='rgba(255,220,220,.92)';
                        statusEl.style.background='rgba(255,255,255,.06)';
                        statusEl.style.borderColor='rgba(255,255,255,.14)';

                        root.classList.remove('is-low','is-moderate','is-high','is-very-high','is-critical');
                        root.classList.add('is-'+level.cls);

                        var factorItems=today.result.factors.slice(0,4);
                        if(!factorItems.length && today.result.dampeners.length){ factorItems=today.result.dampeners.slice(0,3); }
                        factorsEl.innerHTML=factorItems.map(function(item){ return '<li>'+item.text+'</li>'; }).join('');
                        noteEl.textContent='Helyzet: '+locationLabel+' • Kultúra: '+(cropLabels[cropEl.value] || cropEl.value)+' • Ez az MVP a fajspecifikus viselkedést, az időjárást, a holdfényt és a fő térbeli triggert már figyelembe veszi, de a teljes validációhoz valós káresemény és táblaadat kell.';

                        weekEl.innerHTML=forecast.map(function(day){
                            var d=new Date(day.day+'T12:00:00');
                            var l=riskLevel(day.score);
                            return '<div class="va-vadkar__weekcell is-'+l.cls+'">'
                                +'<div class="va-vadkar__weekday">'+dayName(d.getDay())+'</div>'
                                +'<div class="va-vadkar__weekscore">'+day.score+'</div>'
                                +'<div class="va-vadkar__weekspecies">'+speciesMeta[day.species].short+'</div>'
                                +'</div>';
                        }).join('');
                    }
                    function fetchForecast(lat,lon,label){
                        locationLabel=label;
                        statusEl.textContent='Meteorológia...';
                        var url='<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=va_weather_proxy&mode=radar&lat='+encodeURIComponent(lat)
                            +'&lon='+encodeURIComponent(lon);
                        fetch(url).then(function(r){ return r.json(); }).then(function(json){
                            weatherData=json;
                            renderForecast(json);
                        }).catch(function(){
                            if(label === 'Veszprém mintahely'){
                                statusEl.textContent='Offline';
                                levelEl.textContent='Meteorológia nem elérhető';
                                factorsEl.innerHTML='<li>Az Open-Meteo jelenleg nem válaszol.</li><li>A widgethez élő meteorológiai adat kell a validabb becsléshez.</li>';
                                weekEl.innerHTML='';
                                noteEl.textContent='Az élő időjárási adat nem elérhető. A teljes validációhoz ettől függetlenül lokális kárnapló, táblaadat és vadkamera-adat szükséges.';
                                return;
                            }
                            statusEl.textContent='Alap mód';
                            fetchForecast(47.093,17.911,'Veszprém mintahely');
                        });
                    }
                    function fallbackLocation(){
                        fetch('https://ipapi.co/json/').then(function(r){ return r.json(); }).then(function(json){
                            if(json && json.latitude && json.longitude){
                                var label=(json.city ? json.city+', ' : '') + (json.region || json.country_name || 'IP helyzet');
                                fetchForecast(json.latitude,json.longitude,label);
                            } else {
                                fetchForecast(47.093,17.911,'Veszprém mintahely');
                            }
                        }).catch(function(){
                            fetchForecast(47.093,17.911,'Veszprém mintahely');
                        });
                    }
                    function rerender(){ if(weatherData){ renderForecast(weatherData); } }

                    setPhenologyOptions();
                    cropEl.addEventListener('change',function(){ setPhenologyOptions(); rerender(); });
                    phenologyEl.addEventListener('change',rerender);
                    forestEl.addEventListener('change',rerender);
                    damageEl.addEventListener('change',rerender);
                    huntingEl.addEventListener('change',rerender);
                    fenceEl.addEventListener('change',rerender);

                    if(navigator.geolocation){
                        navigator.geolocation.getCurrentPosition(function(pos){
                            fetchForecast(pos.coords.latitude,pos.coords.longitude,'Aktuális hely');
                        }, function(){
                            fallbackLocation();
                        }, {enableHighAccuracy:true,timeout:7000,maximumAge:900000});
                    } else {
                        fallbackLocation();
                    }
                })();
                </script>
            </aside>

        </div><!-- .va-content-layout -->
    </main><!-- .va-container -->

    <!-- ═══ Footer reklám ════════════════════════════════ -->
    <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('footer_top'); ?>

    <?php
    $f_brand_title    = trim( (string) get_option( 'va_hf_footer_brand_title', 'VadászApró' ) );
    $f_cat_title      = trim( (string) get_option( 'va_hf_footer_col_categories_title', 'Kategóriák' ) );
    $f_account_title  = trim( (string) get_option( 'va_hf_footer_col_account_title', 'Fiók' ) );
    $f_legal_title    = trim( (string) get_option( 'va_hf_footer_col_legal_title', 'Jogi információk' ) );
    $f_link_aszf      = trim( (string) get_option( 'va_hf_footer_link_aszf', 'ÁSZF' ) );
    $f_link_privacy   = trim( (string) get_option( 'va_hf_footer_link_privacy', 'Adatvédelmi nyilatkozat' ) );
    $f_link_contact   = trim( (string) get_option( 'va_hf_footer_link_contact', 'Kapcsolat' ) );
    $f_link_help      = trim( (string) get_option( 'va_hf_footer_link_help', 'Súgó' ) );
    $f_copy_text      = trim( (string) get_option( 'va_hf_footer_copy_text', 'VadászApró - Minden jog fenntartva.' ) );
    $f_privacy_bottom = trim( (string) get_option( 'va_hf_footer_privacy_text', 'Adatvédelem' ) );
    $f_logo_url       = trim( (string) get_option( 'va_hf_footer_logo_url', '' ) );
    $f_logo_height    = max( 20, min( 180, absint( get_option( 'va_hf_footer_logo_height', 48 ) ) ) );
    $f_contact_email  = trim( (string) get_option( 'va_contact_email', 'info@vadaszapro.net' ) );
    $f_contact_phone  = trim( (string) get_option( 'va_billing_phone', '+36 20 943 8636' ) );
    $f_contact_addr   = trim( (string) get_option( 'va_contact_addr', '8412 Veszprém, Alsó-Újsor utca 31.' ) );
    if ( in_array( $f_brand_title, [ 'VadászApró', 'Vadaszapro', 'Weingartner Auto' ], true ) ) {
        $f_brand_title = 'VadászApró';
    }
    if ( in_array( $f_copy_text, [ 'VadászApró – Minden jog fenntartva.', 'Vadaszapro - Minden jog fenntartva.', 'Weingartner Auto - Minden jog fenntartva.' ], true ) ) {
        $f_copy_text = 'VadászApró - Minden jog fenntartva.';
    }
    $f_contact_phone_href = preg_replace( '/[^0-9\+]/', '', $f_contact_phone );
    $legacy_legal_map = [
        '/adatvedelmi-nyilatkozat' => '/adatkezeles',
        '/aszf'                    => '/etika',
    ];
    $map_legacy_legal_url = static function( string $url ) use ( $legacy_legal_map ): string {
        $clean = trim( $url );
        if ( isset( $legacy_legal_map[ $clean ] ) ) {
            return $legacy_legal_map[ $clean ];
        }
        return $clean;
    };
    $f_legal_items = [
        [ 'label' => 'Adatvédelem',                         'url' => $map_legacy_legal_url( trim( (string) get_option( 'va_legal_url_adatvedelem', '/adatkezeles' ) ) ) ],
        [ 'label' => 'ÁSZF',                                'url' => $map_legacy_legal_url( trim( (string) get_option( 'va_legal_url_aszf', '/etika' ) ) ) ],
        [ 'label' => 'Impresszum',                          'url' => trim( (string) get_option( 'va_legal_url_impresszum', '' ) ) ],
        [ 'label' => 'Etika és Üzleti Magatartási Kódex',   'url' => trim( (string) get_option( 'va_legal_url_etika', '' ) ) ],
        [ 'label' => 'Sütik',                               'url' => trim( (string) get_option( 'va_legal_url_sutik', '' ) ) ],
        [ 'label' => 'GDPR Adatkezelési Tájékoztató',       'url' => trim( (string) get_option( 'va_legal_url_gdpr', '' ) ) ],
        [ 'label' => 'Fenntartható Fejlődés Irányelve',     'url' => trim( (string) get_option( 'va_legal_url_fenntarthato', '' ) ) ],
    ];

    if ( $f_brand_title === '' )    $f_brand_title = 'VadászApró';
    if ( $f_cat_title === '' )      $f_cat_title = 'Kategóriák';
    if ( $f_account_title === '' )  $f_account_title = 'Fiók';
    if ( $f_legal_title === '' )    $f_legal_title = 'Jogi információk';
    if ( $f_link_aszf === '' )      $f_link_aszf = 'ÁSZF';
    if ( $f_link_privacy === '' )   $f_link_privacy = 'Adatvédelmi nyilatkozat';
    if ( $f_link_contact === '' )   $f_link_contact = 'Kapcsolat';
    if ( $f_link_help === '' )      $f_link_help = 'Súgó';
    if ( $f_copy_text === '' )      $f_copy_text = 'VadászApró - Minden jog fenntartva.';
    if ( $f_privacy_bottom === '' ) $f_privacy_bottom = 'Adatvédelem';
    if ( $f_contact_email === '' )  $f_contact_email = 'info@vadaszapro.net';
    if ( $f_contact_phone === '' )  $f_contact_phone = '+36 20 943 8636';
    if ( $f_contact_addr === '' )   $f_contact_addr = '8412 Veszprém, Alsó-Újsor utca 31.';
    ?>

    <!-- ═══ Footer ═══════════════════════════════════════ -->
    <footer class="va-footer">
        <div class="va-footer__grid">
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_brand_title ); ?></div>
                <?php if ( $f_logo_url !== '' ): ?>
                    <img src="<?php echo esc_url( $f_logo_url ); ?>" class="va-footer__brand-logo" style="height:<?php echo esc_attr( $f_logo_height ); ?>px;" alt="<?php echo esc_attr( $f_brand_title ); ?>" loading="lazy" decoding="async">
                <?php endif; ?>
                <p style="font-size:12px;color:#fff;line-height:1.6;"><?php echo esc_html(get_option('va_site_description', 'Magyarország vadászati apróhirdetési oldala')); ?></p>
                <div class="va-footer__col-title" style="margin-top:12px;">Kapcsolat</div>
                <div class="va-footer__link" style="line-height:1.55;">Cím: <?php echo esc_html( $f_contact_addr ); ?></div>
                <a href="tel:<?php echo esc_attr( $f_contact_phone_href ); ?>" class="va-footer__link">Telefon: <?php echo esc_html( $f_contact_phone ); ?></a>
                <a href="mailto:<?php echo esc_attr( $f_contact_email ); ?>" class="va-footer__link">Email: <?php echo esc_html( $f_contact_email ); ?></a>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_cat_title ); ?></div>
                <?php
                if ( class_exists('VA_Settings_Page') ) :
                    $all_langs    = VA_Settings_Page::get_languages();
                    $active_langs = (array) json_decode( (string) get_option('va_active_langs','["hu"]'), true );
                    $curr_code    = 'hu';
                    if ( isset( $_COOKIE['googtrans'] ) && preg_match('#^/hu/([a-z]{2})$#', sanitize_text_field( wp_unslash( $_COOKIE['googtrans'] ) ), $cm ) ) {
                        $curr_code = $cm[1];
                    }
                    if ( ! isset( $all_langs[ $curr_code ] ) ) $curr_code = 'hu';
                    $va_flag_map_f = ['hu'=>'hu','en'=>'gb','de'=>'de','ro'=>'ro','sk'=>'sk','cs'=>'cz','pl'=>'pl','fr'=>'fr','it'=>'it','es'=>'es','uk'=>'ua','sr'=>'rs','hr'=>'hr','sl'=>'si'];
                    if ( count($active_langs) > 1 ) :
                        $fc_curr = isset($va_flag_map_f[$curr_code]) ? $va_flag_map_f[$curr_code] : $curr_code;
                ?>
                    <div class="va-lang-sw va-lang-sw--footer notranslate" translate="no">
                        <button type="button" class="va-lang-sw__toggle notranslate" id="va-lang-toggle-footer" aria-haspopup="true" aria-expanded="false" translate="no">
                            <img src="https://flagcdn.com/<?php echo esc_attr($fc_curr); ?>.svg" width="22" height="16" alt="<?php echo esc_attr(strtoupper($curr_code)); ?>" style="border-radius:2px;vertical-align:middle;display:inline-block;">
                            <span class="va-lang-code"><?php echo esc_html( strtoupper($curr_code) ); ?></span>
                            <svg class="va-lang-sw__arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </button>
                        <div class="va-lang-sw__dropdown va-lang-sw__dropdown--up notranslate" id="va-lang-dropdown-footer" hidden translate="no">
                            <?php foreach ( $active_langs as $lcode ) :
                                if ( ! isset( $all_langs[ $lcode ] ) ) continue;
                                $lname = $all_langs[ $lcode ]['name'];
                                $fc2   = isset($va_flag_map_f[$lcode]) ? $va_flag_map_f[$lcode] : $lcode;
                            ?>
                                <button type="button" class="va-lang-sw__item<?php echo ( $lcode === $curr_code ) ? ' active' : ''; ?> notranslate"
                                        onclick="vaSetLang('<?php echo esc_js($lcode); ?>')" translate="no">
                                    <img src="https://flagcdn.com/<?php echo esc_attr($fc2); ?>.svg" width="22" height="16" alt="<?php echo esc_attr(strtoupper($lcode)); ?>" style="border-radius:2px;vertical-align:middle;display:inline-block;">
                                    <span><?php echo esc_html( $lname ); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <script>
                    (function(){
                        var t = document.getElementById('va-lang-toggle-footer');
                        var d = document.getElementById('va-lang-dropdown-footer');
                        if (!t || !d) return;
                        t.addEventListener('click', function(e){
                            e.stopPropagation();
                            var open = !d.hidden;
                            d.hidden = open;
                            t.setAttribute('aria-expanded', String(!open));
                        });
                        document.addEventListener('click', function(){
                            d.hidden = true;
                            t.setAttribute('aria-expanded','false');
                        });
                    })();
                    </script>
                <?php endif;
                endif; ?>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_account_title ); ?></div>
                <?php
                $fp = [
                    'va-hirdetes-kereses' => 'Hirdetések böngészése',
                ];
                foreach ($fp as $slug => $label) {
                    $p = get_page_by_path($slug);
                    if ($p) echo '<a href="' . esc_url(get_permalink($p)) . '" class="va-footer__link">' . esc_html($label) . '</a>';
                }
                ?>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_legal_title ); ?></div>
                <?php foreach ( $f_legal_items as $legal_item ):
                    $legal_url = trim( wp_strip_all_tags( (string) ( $legal_item['url'] ?? '' ) ) );
                    if ( $legal_url === '' ) continue;
                    if ( preg_match( '#^https?://#i', $legal_url ) ) {
                        $legal_url = esc_url_raw( $legal_url, [ 'http', 'https' ] );
                    } else {
                        $legal_url = home_url( '/' . ltrim( $legal_url, '/' ) );
                    }
                    if ( $legal_url === '' ) continue;
                ?>
                    <a href="<?php echo esc_url( $legal_url ); ?>" class="va-footer__link"><?php echo esc_html( (string) $legal_item['label'] ); ?></a>
                <?php endforeach; ?>
                <?php $help_url = home_url( '/sugo/' ); ?>
                <a href="<?php echo esc_url( home_url( '/etika/' ) ); ?>" class="va-footer__link">Etika és Üzleti Kódex</a>
                <a href="<?php echo esc_url( home_url( '/impresszum/' ) ); ?>" class="va-footer__link">Impresszum</a>
                <a href="<?php echo esc_url( home_url( '/adatkezeles/' ) ); ?>" class="va-footer__link">Adatkezelési tájékoztató</a>
                <a href="<?php echo esc_url( $help_url ); ?>" class="va-footer__link"><?php echo esc_html( $f_link_help ); ?></a>
            </div>
        </div>
        <div class="va-footer__bottom">
            <?php if ( get_option('va_social_footer_show','1') === '1' && function_exists('va_social_bar') ):
                $ftr_style = get_option('va_social_footer_style','icons');
                $ftr_size  = max(14, min(28, absint( get_option('va_social_icon_size', 20) )));
                echo va_social_bar( $ftr_style, $ftr_size );
            endif; ?>
            &copy; <?php echo date('Y'); ?> <?php echo esc_html( $f_copy_text ); ?> |
            <a href="<?php echo esc_url(home_url('/adatkezeles/')); ?>"><?php echo esc_html( $f_privacy_bottom ); ?></a>
        </div>
    </footer>
<?php endif; // ! is_page('va-fiok') ?>

</div><!-- .va-site-wrap -->

<script>
(function(){
    // Hamburger + scroll-aware header
    var hdr  = document.querySelector('.va-header');
    var hbtn = document.getElementById('va-hamburger');
    var nav  = document.getElementById('va-main-nav');

    // Scroll: header glass-effect bekapcsol
    function onScroll(){
        if( window.scrollY > 40 ) hdr.classList.add('scrolled');
        else hdr.classList.remove('scrolled');
    }
    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('resize', onScroll);
    onScroll();

    // Hamburger toggle
    if(hbtn && nav){
        hbtn.addEventListener('click', function(){
            var open = nav.classList.toggle('open');
            hbtn.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
            document.body.classList.toggle('nav-open', open);
        });
        // Kattintás nav-on kívül zárja
        document.addEventListener('click', function(e){
            if(nav.classList.contains('open') && !nav.contains(e.target) && e.target !== hbtn && !hbtn.contains(e.target)){
                nav.classList.remove('open');
                hbtn.classList.remove('open');
                document.body.style.overflow = '';
                document.body.classList.remove('nav-open');
            }
        });
    }

    // Aktiv nav item
    var cur = location.pathname;
    document.querySelectorAll('.va-nav__item').forEach(function(a){
        if(a.getAttribute('href') && cur.indexOf(a.getAttribute('href')) === 0 && a.getAttribute('href') !== '/'){
            a.classList.add('active');
        }
    });
})();
</script>

<?php wp_footer(); ?>

<?php
$va_scroll_ring_video_url = trim( (string) get_option( 'va_scroll_ring_video_url', content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' ) ) );
if ( $va_scroll_ring_video_url === '' ) {
    $va_scroll_ring_video_url = content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' );
}
$va_scroll_ring_border_color = trim( (string) get_option( 'va_scroll_ring_border_color', '#00e676' ) );
if ( $va_scroll_ring_border_color === '' ) {
    $va_scroll_ring_border_color = '#00e676';
}
?>

<?php if ( ! is_page( 'va-fiok' ) ) : ?>

<!-- ── Scroll-progress pill videó widget ──────────────────── -->
<div id="va-scroll-ring" role="button" aria-label="Vissza a tetejére" tabindex="0" style="--va-scroll-ring-color: <?php echo esc_attr( $va_scroll_ring_border_color ); ?>;">    <!-- progress border SVG (pill alak) – pathLength=100 → nincs kerület-hiba -->
    <svg id="va-ring-svg" viewBox="0 0 178 66" width="178" height="66" aria-hidden="true" style="position:absolute;top:0;left:0;pointer-events:none;z-index:3;">
        <rect x="2" y="2" width="174" height="62" rx="31" fill="none" stroke="rgba(255,255,255,0.13)" stroke-width="1.5" pathLength="100"/>
        <rect id="va-ring-el" x="2" y="2" width="174" height="62" rx="31" fill="none"
            stroke="var(--va-scroll-ring-color)" stroke-width="1.8" stroke-linecap="round"
            pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"
            transform="rotate(180 89 33)"
            style="transition:stroke-dashoffset .12s linear;"/>
    </svg>
    <!-- videó + bal arrow réteg -->
    <div id="va-ring-inner">
        <video autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $va_scroll_ring_video_url ); ?>" type="video/mp4">
        </video>
        <!-- bal oldali sötét átmenet + nyil -->
        <div id="va-ring-arrow">
            <div class="va-arr">
                <svg viewBox="0 0 32 20" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" width="32" height="20"><polyline points="4 16 16 4 28 16"/></svg>
            </div>
        </div>
    </div>
</div>

<style>
#va-scroll-ring {
    position: fixed;
    right: 18px;
    bottom: 18px;
    width: 178px;
    height: 66px;
    z-index: 9999;
    cursor: pointer;
    opacity: 0;
    transform: translateY(14px);
    pointer-events: none;
    transition: opacity .3s, transform .3s;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
}
#va-scroll-ring.va-ring--visible {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
#va-scroll-ring:hover #va-ring-el { stroke: var(--va-scroll-ring-color); }
#va-scroll-ring:hover #va-ring-inner { transform: scale(1.03); }
@media (max-width: 767px) { #va-scroll-ring { display: none !important; } }

#va-ring-inner {
    position: absolute;
    top: 4px; left: 4px; right: 4px; bottom: 4px;
    border-radius: 28px;
    overflow: hidden;
    transition: transform .2s;
}
#va-ring-inner video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
#va-ring-arrow {
    position: absolute;
    top: 0; left: 0;
    width: 62px;
    height: 100%;
    background: linear-gradient(to right, rgba(0,0,0,.78) 50%, transparent);
    display: flex;
    align-items: center;
    justify-content: center;
}
.va-arr {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
    animation: va-arr-bounce 2.8s ease-in-out infinite;
}
.va-arr svg:first-child { animation: va-arr-fade 2.8s ease-in-out infinite; }
@keyframes va-arr-bounce {
    0%,100% { transform: translateY(2px); }
    50%      { transform: translateY(-4px); }
}
@keyframes va-arr-fade {
    0%,100% { opacity: 1; }
    50%      { opacity: .5; }
}
</style>
<script>
(function(){
    var ring  = document.getElementById('va-scroll-ring');
    var el    = document.getElementById('va-ring-el');
    var perim = 100;
    function update() {
        var doc     = document.documentElement;
        var scrollH = doc.scrollHeight - doc.clientHeight;
        var pct     = scrollH > 0 ? window.scrollY / scrollH : 0;
        el.style.strokeDashoffset = perim * (1 - pct);
        ring.classList.toggle('va-ring--visible', window.scrollY > 80);
    }
    window.addEventListener('scroll', update, {passive:true});
    ring.addEventListener('click', function(){ window.scrollTo({top:0, behavior:'smooth'}); });
    ring.addEventListener('keydown', function(e){
        if(e.key==='Enter'||e.key===' '){ window.scrollTo({top:0,behavior:'smooth'}); }
    });
    update();
})();
</script>
<?php endif; ?>
</body>
</html>
