/**
 * VadászApró – Admin JS
 * Color picker, media picker, sidebar interakció, toast
 */
(function ($) {
    "use strict";

    /* ── Custom VA Color Picker ──────────────────────────────── */
    window.vaInitColorPickers = function($ctx) {
        $ctx = $ctx || $(document);
        // Panel bezárása külső kattintásra (csak egyszer regisztrálva)
        if (!window._vaPickerDocBound) {
            window._vaPickerDocBound = true;
            $(document).on('mousedown.vacpick', function (e) {
                if (!$(e.target).closest('.va-cpick').length) {
                    $('.va-cpick__panel').removeClass('va-cpick__panel--open');
                }
            });
        }

        $ctx.find('.va-color-input:not([data-cpick-init])').each(function () {
            var $hidden = $(this).hide().attr('data-cpick-init', '1');
            var rawVal  = $hidden.val().trim();

            var r = 0, g = 0, b = 0, a = 1;
            var m = rawVal.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
            if (m) {
                r = +m[1]; g = +m[2]; b = +m[3];
                a = m[4] !== undefined ? parseFloat(m[4]) : 1;
            } else if (/^#[0-9a-f]{3,8}$/i.test(rawVal)) {
                var parsed = vaHexToRgb(rawVal);
                r = parsed.r; g = parsed.g; b = parsed.b;
            }

            var VA_PALETTE = [
                '#ff0000','#ff4400','#ff8800','#ffcc00','#ffff00',
                '#88cc00','#00cc00','#00cc88','#00ccff','#0088ff',
                '#0044ff','#4400ff','#8800ff','#cc00ff','#ff00cc',
                '#ff0088','#ffffff','#cccccc','#888888','#444444',
                '#000000','#cc0000','#006600','#003399'
            ];

            var paletteHtml = '<div class="va-cpick__palette">';
            for (var pi = 0; pi < VA_PALETTE.length; pi++) {
                paletteHtml += '<span class="va-cpick__pal-swatch" style="background:' + VA_PALETTE[pi] + '" data-color="' + VA_PALETTE[pi] + '"></span>';
            }
            paletteHtml += '</div>';

            var $wrap = $(
                '<div class="va-cpick">' +
                  '<button type="button" class="va-cpick__trigger"><span class="va-cpick__swatch"></span></button>' +
                  '<div class="va-cpick__panel">' +
                    '<div class="va-cpick__preview-row">' +
                      '<span class="va-cpick__preview"></span>' +
                      '<input type="text" class="va-cpick__hex" maxlength="25" spellcheck="false">' +
                    '</div>' +
                    '<div class="va-cpick__sliders">' +
                      '<div class="va-cpick__slider-row">' +
                        '<span class="va-cpick__slider-label">R</span>' +
                        '<div class="va-cpick__slider-track va-cpick__slider-track--r"><input type="range" class="va-cpick__range" data-ch="r" min="0" max="255" step="1"></div>' +
                        '<span class="va-cpick__slider-num" data-ch="r">0</span>' +
                      '</div>' +
                      '<div class="va-cpick__slider-row">' +
                        '<span class="va-cpick__slider-label">G</span>' +
                        '<div class="va-cpick__slider-track va-cpick__slider-track--g"><input type="range" class="va-cpick__range" data-ch="g" min="0" max="255" step="1"></div>' +
                        '<span class="va-cpick__slider-num" data-ch="g">0</span>' +
                      '</div>' +
                      '<div class="va-cpick__slider-row">' +
                        '<span class="va-cpick__slider-label">B</span>' +
                        '<div class="va-cpick__slider-track va-cpick__slider-track--b"><input type="range" class="va-cpick__range" data-ch="b" min="0" max="255" step="1"></div>' +
                        '<span class="va-cpick__slider-num" data-ch="b">0</span>' +
                      '</div>' +
                    '</div>' +
                    '<div class="va-cpick__opacity-row">' +
                      '<span class="va-cpick__opacity-label">Átlátszóság</span>' +
                      '<div class="va-cpick__slider-track va-cpick__slider-track--a"><input type="range" class="va-cpick__range" data-ch="a" min="0" max="100" step="1"></div>' +
                      '<span class="va-cpick__slider-num va-cpick__opacity-num" data-ch="a">100%</span>' +
                    '</div>' +
                    paletteHtml +
                  '</div>' +
                '</div>'
            );
            $hidden.after($wrap);

            var $trigger = $wrap.find('.va-cpick__trigger');
            var $panel   = $wrap.find('.va-cpick__panel');
            var $swatch  = $wrap.find('.va-cpick__swatch');
            var $preview = $wrap.find('.va-cpick__preview');
            var $hexIn   = $wrap.find('.va-cpick__hex');
            var $ranges  = $wrap.find('.va-cpick__range');

            function syncUI() {
                var hex     = vaRgbToHex(r, g, b);
                var display = a >= 1 ? hex : 'rgba('+r+','+g+','+b+','+Math.round(a*100)/100+')';
                $swatch.css('background', display);
                $preview.css('background', display);
                $hexIn.val(display);
                $hidden.val(display).trigger('change');
                $ranges.each(function () {
                    var ch  = $(this).data('ch');
                    var val = ch === 'r' ? r : ch === 'g' ? g : ch === 'b' ? b : Math.round(a * 100);
                    $(this).val(val);
                    var $num = $wrap.find('.va-cpick__slider-num[data-ch="'+ch+'"]');
                    $num.text(ch === 'a' ? val + '%' : val);
                });
                $wrap.find('.va-cpick__slider-track--r').css('background',
                    'linear-gradient(to right,rgb(0,'+g+','+b+'),rgb(255,'+g+','+b+'))');
                $wrap.find('.va-cpick__slider-track--g').css('background',
                    'linear-gradient(to right,rgb('+r+',0,'+b+'),rgb('+r+',255,'+b+'))');
                $wrap.find('.va-cpick__slider-track--b').css('background',
                    'linear-gradient(to right,rgb('+r+','+g+',0),rgb('+r+','+g+',255))');
                $wrap.find('.va-cpick__slider-track--a').css('background',
                    'linear-gradient(to right,rgba('+r+','+g+','+b+',0),rgba('+r+','+g+','+b+',1))');
            }
            syncUI();

            $trigger.on('click', function (e) {
                e.stopPropagation();
                var isOpen = $panel.hasClass('va-cpick__panel--open');
                $('.va-cpick__panel').removeClass('va-cpick__panel--open');
                if (!isOpen) {
                    var rect      = $trigger[0].getBoundingClientRect();
                    var panelW    = 240;
                    var left      = rect.left;
                    // ne lógjon ki jobbra
                    if (left + panelW > window.innerWidth - 8) {
                        left = window.innerWidth - panelW - 8;
                    }
                    // alulra vagy felülre nyíljon
                    var spaceBelow = window.innerHeight - rect.bottom;
                    var top;
                    if (spaceBelow >= 240 || spaceBelow >= rect.top) {
                        top = rect.bottom + 6;
                    } else {
                        top = rect.top - 6;
                        $panel.css('transform', 'translateY(-100%)');
                    }
                    $panel.css({ top: top, left: left, transform: spaceBelow >= 240 ? '' : 'translateY(-100%)' });
                    $panel.addClass('va-cpick__panel--open');
                }
            });

            $panel.on('mousedown', function (e) { e.stopPropagation(); });

            $panel.on('click', '.va-cpick__pal-swatch', function () {
                var rgb3 = vaHexToRgb($(this).data('color'));
                r = rgb3.r; g = rgb3.g; b = rgb3.b;
                syncUI();
            });

            $ranges.on('input', function () {
                var ch  = $(this).data('ch');
                var val = parseInt($(this).val(), 10);
                if (ch === 'r')      { r = val; }
                else if (ch === 'g') { g = val; }
                else if (ch === 'b') { b = val; }
                else                 { a = val / 100; }
                syncUI();
            });

            $hexIn.on('change blur', function () {
                var v  = $(this).val().trim();
                var pm = v.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
                if (pm) {
                    r = +pm[1]; g = +pm[2]; b = +pm[3];
                    a = pm[4] !== undefined ? parseFloat(pm[4]) : 1;
                } else if (/^#[0-9a-f]{3,8}$/i.test(v)) {
                    var rgb2 = vaHexToRgb(v);
                    r = rgb2.r; g = rgb2.g; b = rgb2.b; a = 1;
                }
                syncUI();
            });
        });  // end each
    };  // end vaInitColorPickers

    $(function () {
        window.vaInitColorPickers($(document));

        /* ── Media picker ─────────────────────────────────────── */
        $(document).on("click", ".va-media-btn", function (e) {
            e.preventDefault();
            var btn     = $(this);
            var target  = btn.data("target");
            var preview = btn.data("preview");

            var frame = wp.media({
                title:    "Kép kiválasztása",
                button:   { text: "Kiválaszt" },
                multiple: false,
                library:  { type: "image" }
            });

            frame.on("select", function () {
                var att = frame.state().get("selection").first().toJSON();
                $("#" + target).val(att.url);
                if (preview) {
                    var $p = $("#" + preview);
                    if ($p.length) {
                        $p.attr("src", att.url).show();
                    } else {
                        btn.closest(".va-media-field").find(".va-media-preview").attr("src", att.url).show();
                    }
                }
            });

            frame.open();
        });

        /* ── Media clear ──────────────────── */
        $(document).on("click", ".va-media-clear", function (e) {
            e.preventDefault();
            var btn     = $(this);
            var target  = btn.data("target");
            var preview = btn.data("preview");
            $("#" + target).val("").trigger("change");
            if (preview) {
                var $p = $("#" + preview);
                if ($p.is("img")) $p.attr("src", "").hide();
                else $p.find("img").attr("src", "").hide();
            } else {
                btn.closest(".va-media-field").find(".va-media-preview").attr("src", "").hide();
            }
        });

        /* ── Video media picker ────────────── */
        $(document).on("click", ".va-media-video-btn", function (e) {
            e.preventDefault();
            var btn    = $(this);
            var target = btn.data("target");
            var frame  = wp.media({
                title:    "Videó kiválasztása",
                button:   { text: "Kiválaszt" },
                multiple: false,
                library:  { type: "video" }
            });
            frame.on("select", function () {
                var att = frame.state().get("selection").first().toJSON();
                $("#" + target).val(att.url).trigger("change");
            });
            frame.open();
        });

        /* ── Video media clear ─────────────── */
        $(document).on("click", ".va-media-video-clear", function (e) {
            e.preventDefault();
            var target = $(this).data("target");
            $("#" + target).val("").trigger("change");
        });

        /* ── Toast stack ──────────────────────────────────────── */
        if (!$(".va-admin-toast-stack").length) {
            $("body").append("<div class=\"va-admin-toast-stack\" id=\"va-toast-stack\"></div>");
        }

        /* ── Mentés után toast ────────────────────────────────── */
        var $notice = $(".updated, .settings-error");
        if ($notice.length) {
            var msg  = $notice.find("p").text().trim() || "Beállítások mentve.";
            var type = ($notice.hasClass("settings-error") && $notice.hasClass("error")) ? "error" : "success";
            vaAdminToast(msg, type);
        }

        /* ── Sidebar aktív elem ───────────────────────────────── */
        var currentPage    = new URLSearchParams(window.location.search).get("page") || "";
        var currentPostType = new URLSearchParams(window.location.search).get("post_type") || "";
        $("#va-sidebar .va-sb-item").each(function () {
            var href      = $(this).attr("href") || "";
            var urlParams = new URLSearchParams(href.split("?")[1] || "");
            var itemPage  = urlParams.get("page") || "";
            var itemPT    = urlParams.get("post_type") || "";
            if ((itemPage && itemPage === currentPage) || (itemPT && itemPT === currentPostType)) {
                $(this).addClass("active");
            }
        });
    });

    /* ── Color math helpers ─────────────────────────────────── */
    function vaHsvToRgb(h, s, v) {
        var i = Math.floor(h / 60) % 6, f = h/60 - Math.floor(h/60);
        var p = v*(1-s), q = v*(1-f*s), t = v*(1-(1-f)*s);
        var r,g,b;
        switch(i){case 0:r=v;g=t;b=p;break;case 1:r=q;g=v;b=p;break;case 2:r=p;g=v;b=t;break;case 3:r=p;g=q;b=v;break;case 4:r=t;g=p;b=v;break;default:r=v;g=p;b=q;}
        return {r:Math.round(r*255),g:Math.round(g*255),b:Math.round(b*255)};
    }
    function vaRgbToHsv(r,g,b) {
        r/=255;g/=255;b/=255;
        var max=Math.max(r,g,b),min=Math.min(r,g,b),d=max-min,h,s=max?d/max:0,v=max;
        if(!d){h=0;}else if(max===r){h=(g-b)/d+(g<b?6:0);}else if(max===g){h=(b-r)/d+2;}else{h=(r-g)/d+4;}
        return {h:h/6*360,s:s,v:v};
    }
    function vaRgbToHex(r, g, b) {
        return '#' + [r,g,b].map(function(v){return ('0'+Math.max(0,Math.min(255,Math.round(v))).toString(16)).slice(-2);}).join('');
    }
    function vaHexToRgb(hex) {
        hex=hex.replace('#','');
        if(hex.length===3)hex=hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
        return{r:parseInt(hex.substr(0,2),16)||0,g:parseInt(hex.substr(2,2),16)||0,b:parseInt(hex.substr(4,2),16)||0};
    }
    function vaCpickParse(val) {
        var m=val.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
        var r,g,b,a=1;
        if(m){r=+m[1];g=+m[2];b=+m[3];a=m[4]!==undefined?parseFloat(m[4]):1;}
        else if(/^#[0-9a-f]{3,8}$/i.test(val)){var rgb=vaHexToRgb(val);r=rgb.r;g=rgb.g;b=rgb.b;}
        else return null;
        var hsv=vaRgbToHsv(r,g,b);
        return{h:hsv.h,s:hsv.s,v:hsv.v,a:a};
    }

    // (legacy stub)
    function vaCommitRgba() {}

    function vaAdminEscapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function vaAdminBuildGeoCsv(report) {
        var lines = ['Orszagkod;Orszag;Regio;Varos;Megtekintes;Utolso'];
        (report.rows || []).forEach(function(row) {
            var cols = [
                row.country_code || '--',
                row.country || 'Ismeretlen',
                row.region || 'Ismeretlen',
                row.city || 'Ismeretlen',
                String(parseInt(row.views || 0, 10)),
                row.last_seen || ''
            ].map(function(cell) {
                var safe = String(cell).replace(/\"/g, '\"\"');
                return '\"' + safe + '\"';
            });
            lines.push(cols.join(';'));
        });
        return '\ufeff' + lines.join('\n');
    }

    function vaAdminFormatBudapestDateTime(value) {
        if (!value) return '-';
        var raw = String(value).trim();
        if (!raw) return '-';
        var date = new Date(raw.replace(' ', 'T') + 'Z');
        if (isNaN(date.getTime())) {
            return raw;
        }
        return new Intl.DateTimeFormat('hu-HU', {
            timeZone: 'Europe/Budapest',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(date);
    }

    window.vaOpenAdminGeoModal = function(postId, nonce, forcedAjaxUrl) {
        var ajaxEndpoint = forcedAjaxUrl || (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
        if (!ajaxEndpoint) {
            window.vaAdminToast('Nincs ajax endpoint.', 'error');
            return;
        }

        $('.va-geo-modal').remove();

        var html = ''
            + '<div class="va-geo-modal">'
            + '  <div class="va-geo-modal__backdrop" data-close="1"></div>'
            + '  <div class="va-geo-modal__panel" role="dialog" aria-modal="true" aria-label="Geo riport">'
            + '    <div class="va-geo-modal__head">'
            + '      <div class="va-geo-modal__title">Geo riport betoltese...</div>'
            + '      <div class="va-geo-modal__actions">'
            + '        <button type="button" class="va-geo-modal__btn" data-action="download" disabled>Letoltes CSV</button>'
            + '        <button type="button" class="va-geo-modal__btn" data-action="print" disabled>Nyomtatas</button>'
            + '        <button type="button" class="va-geo-modal__btn va-geo-modal__btn--close" data-close="1">Bezar</button>'
            + '      </div>'
            + '    </div>'
            + '    <div class="va-geo-modal__body"><div class="va-geo-modal__loading">Betoltes...</div></div>'
            + '  </div>'
            + '</div>';

        $('body').append(html).addClass('va-geo-modal-open');
        var $modal = $('.va-geo-modal');
        var $body = $modal.find('.va-geo-modal__body');
        var $title = $modal.find('.va-geo-modal__title');
        var $download = $modal.find('[data-action="download"]');
        var $print = $modal.find('[data-action="print"]');

        $modal.on('click', '[data-close="1"]', function() {
            $('body').removeClass('va-geo-modal-open');
            $modal.remove();
        });

        $.post(ajaxEndpoint, {
            action: 'va_get_view_geo_report',
            post_id: postId,
            nonce: nonce
        }).done(function(res) {
            if (!res || !res.success || !res.data) {
                $body.html('<div class="va-geo-modal__error">Nem sikerult a geo riport lekerese.</div>');
                return;
            }

            var report = res.data;
            var rows = report.rows || [];
            var total = parseInt(report.total_views || 0, 10);
            var topViews = rows.length ? parseInt(rows[0].views || 0, 10) : 0;
            var topShare = total > 0 ? Math.round((topViews / total) * 100) : 0;
            var warn = '';

            if (topShare >= 70 && rows.length > 0) {
                warn = '<div class="va-geo-modal__warning">A top lokacio aranya magas (' + topShare + '%). Mostantol ugyanaz az IP 30 napig csak 1x szamit bele.</div>';
            }

            $title.text('Geo riport: ' + (report.post_title || ('#' + postId)));

            if (!rows.length) {
                $body.html(warn + '<div class="va-geo-modal__empty">Nincs meg lokacios adat ehhez a hirdeteshez.</div>');
            } else {
                var table = ''
                    + warn
                    + '<div class="va-geo-modal__meta">Osszes megtekintes: <strong>' + total + '</strong> | Kezdet: ' + vaAdminEscapeHtml(vaAdminFormatBudapestDateTime(report.from_datetime || '-')) + ' | Frissitve: ' + vaAdminEscapeHtml(vaAdminFormatBudapestDateTime(report.generated_at || '-')) + ' (Europe/Budapest)</div>'
                    + '<div class="va-geo-modal__meta" style="opacity:.8;">Megjegyzes: IP-alapu becsles. Mobilhalozatnal gyakori, hogy a szolgaltato budapesti kilepesi pontja latszik, nem a tenyleges GPS hely.</div>'
                    + '<div class="va-geo-modal__table-wrap">'
                    + '<table class="va-geo-modal__table">'
                    + '<thead><tr><th>Orszag</th><th>Regio</th><th>Varos</th><th>Megtekintes</th><th>Utolso</th></tr></thead><tbody>';

                rows.forEach(function(row) {
                    var countryCell = (row.country_code || '--') + ' - ' + (row.country || 'Ismeretlen');
                    if ((row.country_code || '').toUpperCase() === 'GP') {
                        countryCell = 'GP - GPS (eszkoz)';
                    }
                    table += '<tr>'
                        + '<td>' + vaAdminEscapeHtml(countryCell) + '</td>'
                        + '<td>' + vaAdminEscapeHtml(row.region || 'Ismeretlen') + '</td>'
                        + '<td>' + vaAdminEscapeHtml(row.city || 'Ismeretlen') + '</td>'
                        + '<td><strong>' + vaAdminEscapeHtml(String(row.views || 0)) + '</strong></td>'
                        + '<td>' + vaAdminEscapeHtml(vaAdminFormatBudapestDateTime(row.last_seen || '-')) + '</td>'
                        + '</tr>';
                });

                table += '</tbody></table></div>';
                $body.html(table);
            }

            $download.prop('disabled', false);
            $print.prop('disabled', false);

            $download.off('click').on('click', function() {
                var csv = vaAdminBuildGeoCsv(report);
                var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'geo-riport-' + postId + '.csv';
                document.body.appendChild(link);
                link.click();
                link.remove();
            });

            $print.off('click').on('click', function() {
                window.print();
            });
        }).fail(function() {
            $body.html('<div class="va-geo-modal__error">Halozati hiba a geo riport lekeresenel.</div>');
        });
    };

    $(document).on('click', '.va-geo-report-trigger', function(e) {
        e.preventDefault();
        var postId = parseInt($(this).data('post-id') || 0, 10);
        var nonce = ($(this).data('geo-nonce') || '').toString();
        var ajaxUrl = ($(this).data('ajax-url') || '').toString();
        if (!postId || !nonce) {
            window.vaAdminToast('Geo riport most nem elerheto.', 'error');
            return;
        }
        window.vaOpenAdminGeoModal(postId, nonce, ajaxUrl);
    });



    /* ── Toast helper ─────────────────────────────────────────── */
    window.vaAdminToast = function (msg, type) {
        type = type || "success";
        var $toast = $("<div class=\"va-admin-toast va-admin-toast--" + type + "\">" + msg + "</div>");
        var $stack = $("#va-toast-stack");
        if (!$stack.length) { $stack = $("<div id=\"va-toast-stack\" class=\"va-admin-toast-stack\"></div>").appendTo("body"); }
        $stack.append($toast);
        requestAnimationFrame(function () { $toast.addClass("show"); });
        setTimeout(function () {
            $toast.removeClass("show");
            setTimeout(function () { $toast.remove(); }, 350);
        }, 3500);
    };

    // ── OverlayScrollbars inicializálás ──────────────────────
    function va_init_admin_overlay_scrollbars() {
        if (typeof OverlayScrollbars === 'undefined') {
            return;
        }

        var scrollbarOptions = {
            scrollbars: {
                autoHide: 'move',
                clickScroll: false
            }
        };

        // #va-sidebar (admin left panel)
        var sidebarEl = document.getElementById('va-sidebar');
        if (sidebarEl) {
            OverlayScrollbars(sidebarEl, scrollbarOptions);
        }
    }

    // Inicializálás betöltéskor
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', va_init_admin_overlay_scrollbars);
    } else {
        va_init_admin_overlay_scrollbars();
    }

}(jQuery));

