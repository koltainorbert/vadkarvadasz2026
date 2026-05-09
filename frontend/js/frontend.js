/* jshint esversion: 6 */
/**
 * VadászApró – Frontend JavaScript
 * Szűrő, watchlist, megtekintés, inline kapcsolat megjelenítés, scroll animáció
 */
(function($) {
  'use strict';

  document.documentElement.classList.add('va-js');

  function va_toast(message, type) {
    var kind = type || 'success';
    var stack = document.querySelector('.va-toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'va-toast-stack';
      document.body.appendChild(stack);
    }

    var toast = document.createElement('div');
    toast.className = 'va-toast va-toast--' + kind;
    toast.innerHTML = '<div class="va-toast__title">' + (kind === 'error' ? 'Sikertelen' : 'Sikeres') + '</div>'
      + '<div class="va-toast__msg"></div>';
    toast.querySelector('.va-toast__msg').textContent = message;
    stack.appendChild(toast);

    requestAnimationFrame(function() {
      toast.classList.add('is-visible');
    });

    setTimeout(function() {
      toast.classList.remove('is-visible');
      setTimeout(function() {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 250);
    }, 5000);
  }

  // Kulso template scriptek is tudjanak push toastot inditani
  window.va_toast = va_toast;

  function vaEscapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function vaBuildGeoCsv(report) {
    var lines = ['Orszagkod;Orszag;Regio;Varos;Megtekintes;Utolso'];
    (report.rows || []).forEach(function(row) {
      var parts = [
        row.country_code || '--',
        row.country || 'Ismeretlen',
        row.region || 'Ismeretlen',
        row.city || 'Ismeretlen',
        String(parseInt(row.views || 0, 10)),
        row.last_seen || ''
      ].map(function(cell) {
        var safe = String(cell).replace(/"/g, '""');
        return '"' + safe + '"';
      });
      lines.push(parts.join(';'));
    });
    return '\ufeff' + lines.join('\n');
  }

  function vaFormatBudapestDateTime(value) {
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

  function vaOpenGeoReportModal(postId, nonce, ajaxUrl) {
    var existing = document.querySelector('.va-geo-modal');
    if (existing) {
      existing.remove();
    }

    var modal = document.createElement('div');
    modal.className = 'va-geo-modal';
    modal.innerHTML = ''
      + '<div class="va-geo-modal__backdrop" data-close="1"></div>'
      + '<div class="va-geo-modal__panel" role="dialog" aria-modal="true" aria-label="Geo riport">'
      + '  <div class="va-geo-modal__head">'
      + '    <div class="va-geo-modal__title">Geo riport betoltese...</div>'
      + '    <div class="va-geo-modal__actions">'
      + '      <button type="button" class="va-geo-modal__btn" data-action="download" disabled>Letoltes CSV</button>'
      + '      <button type="button" class="va-geo-modal__btn" data-action="print" disabled>Nyomtatas</button>'
      + '      <button type="button" class="va-geo-modal__btn va-geo-modal__btn--close" data-close="1">Bezar</button>'
      + '    </div>'
      + '  </div>'
      + '  <div class="va-geo-modal__body"><div class="va-geo-modal__loading">Betoltes...</div></div>'
      + '</div>';

    document.body.appendChild(modal);
    document.body.classList.add('va-geo-modal-open');

    function closeModal() {
      document.body.classList.remove('va-geo-modal-open');
      modal.remove();
    }

    modal.addEventListener('click', function(e) {
      if (e.target && e.target.getAttribute('data-close') === '1') {
        closeModal();
      }
    });

    var bodyEl = modal.querySelector('.va-geo-modal__body');
    var titleEl = modal.querySelector('.va-geo-modal__title');
    var btnDownload = modal.querySelector('[data-action="download"]');
    var btnPrint = modal.querySelector('[data-action="print"]');

    $.post(ajaxUrl, {
      action: 'va_get_view_geo_report',
      post_id: postId,
      nonce: nonce
    }).done(function(res) {
      if (!res || !res.success || !res.data) {
        bodyEl.innerHTML = '<div class="va-geo-modal__error">Nem sikerult a geo riport lekerese.</div>';
        return;
      }

      var report = res.data;
      var rows = report.rows || [];
      var total = parseInt(report.total_views || 0, 10);
      var topViews = rows.length ? parseInt(rows[0].views || 0, 10) : 0;
      var topShare = total > 0 ? Math.round((topViews / total) * 100) : 0;
      var warnHtml = '';

      if (topShare >= 70 && rows.length > 0) {
        warnHtml = '<div class="va-geo-modal__warning">'
          + 'A top lokacio aranya magas (' + topShare + '%). Mostantol ugyanaz az IP 30 napig csak 1x szamit bele.'
          + '</div>';
      }

      titleEl.textContent = 'Geo riport: ' + (report.post_title || ('#' + postId));

      if (!rows.length) {
        bodyEl.innerHTML = warnHtml + '<div class="va-geo-modal__empty">Nincs meg lokacios adat ehhez a hirdeteshez.</div>';
      } else {
        var html = ''
          + warnHtml
          + '<div class="va-geo-modal__meta">Osszes megtekintes: <strong>' + total + '</strong> | Kezdet: ' + vaEscapeHtml(vaFormatBudapestDateTime(report.from_datetime || '-')) + ' | Frissitve: ' + vaEscapeHtml(vaFormatBudapestDateTime(report.generated_at || '-')) + ' (Europe/Budapest)</div>'
          + '<div class="va-geo-modal__meta" style="opacity:.8;">Megjegyzes: IP-alapu becsles. Mobilhalozatnal gyakori, hogy a szolgaltato budapesti kilepesi pontja latszik, nem a tenyleges GPS hely.</div>'
          + '<div class="va-geo-modal__table-wrap">'
          + '<table class="va-geo-modal__table">'
          + '<thead><tr><th>Orszag</th><th>Regio</th><th>Varos</th><th>Megtekintes</th><th>Utolso</th></tr></thead><tbody>';

        rows.forEach(function(row) {
          var countryCell = (row.country_code || '--') + ' - ' + (row.country || 'Ismeretlen');
          if ((row.country_code || '').toUpperCase() === 'GP') {
            countryCell = 'GP - GPS (eszkoz)';
          }
          html += '<tr>'
            + '<td>' + vaEscapeHtml(countryCell) + '</td>'
            + '<td>' + vaEscapeHtml(row.region || 'Ismeretlen') + '</td>'
            + '<td>' + vaEscapeHtml(row.city || 'Ismeretlen') + '</td>'
            + '<td><strong>' + vaEscapeHtml(String(row.views || 0)) + '</strong></td>'
            + '<td>' + vaEscapeHtml(vaFormatBudapestDateTime(row.last_seen || '-')) + '</td>'
            + '</tr>';
        });

        html += '</tbody></table></div>';
        bodyEl.innerHTML = html;
      }

      btnDownload.disabled = false;
      btnPrint.disabled = false;

      btnDownload.addEventListener('click', function() {
        var csv = vaBuildGeoCsv(report);
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'geo-riport-' + postId + '.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
      });

      btnPrint.addEventListener('click', function() {
        window.print();
      });
    }).fail(function() {
      bodyEl.innerHTML = '<div class="va-geo-modal__error">Halozati hiba a geo riport lekeresenel.</div>';
    });
  }

  $(document).on('click', '.va-geo-report-trigger', function(e) {
    e.preventDefault();
    var postId = parseInt($(this).data('post-id') || 0, 10);
    var nonce = ($(this).data('geo-nonce') || '').toString();
    var ajaxUrl = (typeof VA_Data !== 'undefined' && VA_Data.ajax_url) ? VA_Data.ajax_url : '';
    if (!postId || !nonce || !ajaxUrl) {
      va_toast('Geo riport most nem elerheto.', 'error');
      return;
    }
    vaOpenGeoReportModal(postId, nonce, ajaxUrl);
  });

  // Szerver oldali notice-okbol is csinalunk push visszajelzest
  document.querySelectorAll('.va-notice--success, .va-notice--error').forEach(function(el) {
    var msg = (el.textContent || '').trim();
    if (!msg) return;
    va_toast(msg, el.classList.contains('va-notice--error') ? 'error' : 'success');
  });

  // ── Megtekintés számláló (GPS prioritas, ha engedelyezve) ────────
  function va_send_view_increment(extraData) {
    if (typeof VA_Data === 'undefined' || !VA_Data.post_id) return;

    var payload = {
      action: 'va_increment_views',
      post_id: VA_Data.post_id,
      nonce: VA_Data.nonce
    };

    if (extraData && typeof extraData === 'object') {
      Object.keys(extraData).forEach(function(k) {
        payload[k] = extraData[k];
      });
    }

    $.post(VA_Data.ajax_url, payload);
  }

  if (typeof VA_Data !== 'undefined' && VA_Data.post_id) {
    if (navigator.geolocation && typeof navigator.geolocation.getCurrentPosition === 'function') {
      var settled = false;
      var finish = function(extra) {
        if (settled) return;
        settled = true;
        va_send_view_increment(extra || {});
      };

      var fallbackTimer = setTimeout(function() {
        finish({});
      }, 1400);

      navigator.geolocation.getCurrentPosition(
        function(pos) {
          clearTimeout(fallbackTimer);
          finish({
            gps_lat: pos.coords && typeof pos.coords.latitude !== 'undefined' ? pos.coords.latitude : '',
            gps_lng: pos.coords && typeof pos.coords.longitude !== 'undefined' ? pos.coords.longitude : '',
            gps_accuracy: pos.coords && typeof pos.coords.accuracy !== 'undefined' ? pos.coords.accuracy : ''
          });
        },
        function() {
          clearTimeout(fallbackTimer);
          finish({});
        },
        {
          enableHighAccuracy: true,
          timeout: 1200,
          maximumAge: 60000
        }
      );
    } else {
      va_send_view_increment({});
    }
  }

  // ── Ár csúszka (range slider) ────────────────────────────
  function va_format_price(val) {
    return parseInt(val).toLocaleString('hu-HU');
  }
  function va_update_range() {
    var $min  = $('#va-min-price');
    var $max  = $('#va-max-price');
    if (!$min.length) return;
    var minV  = parseInt($min.val());
    var maxV  = parseInt($max.val());
    var total = parseInt($min.attr('max')) || 100000000;
    // csere ha min > max
    if (minV > maxV) {
      var tmp = minV; minV = maxV; maxV = tmp;
      $min.val(minV); $max.val(maxV);
    }
    $('#va-min-price-display').text(va_format_price(minV));
    $('#va-max-price-display').text(va_format_price(maxV));
    // fill sáv
    var left  = (minV / total) * 100;
    var right = 100 - (maxV / total) * 100;
    $('#va-range-fill').css({ left: left + '%', right: right + '%' });
  }

  function va_update_model_options(selectedModel) {
    var $brand = $('#va-brand-search');
    var $model = $('#va-model-search');
    if (!$brand.length || !$model.length) return;

    var map = (typeof VA_Data !== 'undefined' && VA_Data.vehicle_brand_models) ? VA_Data.vehicle_brand_models : {};
    var brand = ($brand.val() || '').trim();
    var models = (brand && map[brand] && Array.isArray(map[brand])) ? map[brand] : [];

    var html = '<option value="">Modell: Mindegy</option>';
    models.forEach(function(m) {
      var safe = $('<div>').text(m).html();
      var sel = (selectedModel && selectedModel === m) ? ' selected' : '';
      html += '<option value="' + safe + '"' + sel + '>' + safe + '</option>';
    });
    $model.html(html);
  }

  $(document).on('input', '#va-min-price, #va-max-price', function() {
    va_update_range();
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() { va_load_listings(1); }, 400);
  });
  va_update_range();

  // ── Watchlist toggle ─────────────────────────────────────
  $(document).on('click', '.va-card__watchlist', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var $btn    = $(this);
    var post_id = $btn.data('post-id');
    var nonce   = $btn.data('nonce') || (typeof VA_Data !== 'undefined' ? VA_Data.nonce : '');
    var ajaxUrl = $btn.data('ajax-url') || (typeof VA_Data !== 'undefined' ? VA_Data.ajax_url : '');

    if (!post_id || !nonce || !ajaxUrl) {
      va_toast('A kedvencek mentése most nem elérhető.', 'error');
      return;
    }

    if ($btn.data('busy')) {
      return;
    }
    $btn.data('busy', true);

    $.post(ajaxUrl, {
      action:  'va_toggle_watchlist',
      nonce:   nonce,
      post_id: post_id
    }, function(res) {
      if (res.success) {
        $btn.toggleClass('active', res.data.action === 'added');
        $btn.attr('title', res.data.message);
        va_toast(res.data.message || 'Kedvencek frissítve.', 'success');
      } else {
        va_toast((res.data && res.data.message) ? res.data.message : 'Nem sikerült menteni a kedvencekbe.', 'error');
      }
    }).fail(function() {
      va_toast('Hálózati hiba. Próbáld újra.', 'error');
    }).always(function() {
      $btn.data('busy', false);
    });
  });

  // ── Teljes kártya kattintható (szív/gomb/link kivételével) ──
  $(document).on('click', '.va-card', function(e) {
    if ($(e.target).closest('a, button, input, select, textarea, label').length) {
      return;
    }

    var href = $(this).find('.va-card__title a').attr('href') || $(this).find('.va-card__img-wrap').attr('href');
    if (!href) {
      return;
    }

    if (e.ctrlKey || e.metaKey) {
      window.open(href, '_blank');
      return;
    }

    window.location.href = href;
  });

  // ── Hirdetések AJAX szűrő ────────────────────────────────
  var filterTimeout;

  function va_load_listings(page) {
    page = page || 1;
    var $form   = $('#va-filter-form');
    var $results = $('#va-listing-results');
    var $loader  = $('#va-listing-loader');

    if (!$form.length) return;

    $loader.show();
    $results.css('opacity', 0.4);

    var $minR     = $('#va-min-price');
    var $maxR     = $('#va-max-price');
    var maxLimit  = parseInt($minR.attr('max') || 100000000);
    var minVal    = $minR.length ? parseInt($minR.val()) : 1;
    var maxVal    = $maxR.length ? parseInt($maxR.val()) : maxLimit;
    var extras = [];
    $('.va-car-filter[data-extra]:checked').each(function() {
      var key = ($(this).data('extra') || '').toString().trim();
      if (key) extras.push(key);
    });

    var data = {
      action:     'va_filter_listings',
      paged:      page,
      keyword:    $('#va-kw').val(),
      category:   $('#va-cat').val(),
      county:     $('#va-county').val(),
      condition:  $('#va-cond').val(),
      min_price:  minVal > 1 ? minVal : 0,
      max_price:  (maxVal > 0 && maxVal < maxLimit) ? maxVal : 0,
      sort:       $('#va-sort').val(),
      post_type:  $form.data('post-type') || 'va_listing',
      author_id:  $form.data('author-id') || 0,
      brand:      $('#va-brand-search').val() || '',
      model:      $('#va-model-search').val() || '',
      body_type:  $('#va-body-type').val() || '',
      fuel_type:  $('#va-fuel-type').val() || '',
      year_min:   parseInt($('#va-year-min').val() || 0),
      year_max:   parseInt($('#va-year-max').val() || 0),
      mileage_min: parseInt($('#va-mileage-min').val() || 0),
      mileage_max: parseInt($('#va-mileage-max').val() || 0),
      engine_min: parseInt($('#va-engine-min').val() || 0),
      engine_max: parseInt($('#va-engine-max').val() || 0),
      vehicle_condition: $('#va-vehicle-condition').val() || '',
      doors:      $('#va-doors').val() || '',
      passengers: $('#va-passengers').val() || '',
      opt_automatic: $('#va-opt-automatic').is(':checked') ? 1 : 0,
      opt_awd:    $('#va-opt-awd').is(':checked') ? 1 : 0,
      opt_service_book: $('#va-opt-service-book').is(':checked') ? 1 : 0,
      extras:     extras,
      per_page:   parseInt($('input[name="va-per-page"]:checked').val() || 25)
    };

    $.post(VA_Data.ajax_url, data, function(res) {
      $loader.hide();
      $results.css('opacity', 1);

      if (res.success) {
        $results.html(res.data.html);
        va_build_pagination(res.data.max_pages, page);
        va_count_update(res.data.total);
        va_init_animate();
      }
    });
  }

  // filter esemény
  $(document).on('change', '#va-cat, #va-county, #va-cond, #va-sort, #va-brand-search, #va-model-search, #va-body-type, #va-fuel-type, #va-vehicle-condition, #va-doors, #va-passengers, input[name="va-per-page"]', function() {
    if (this.id === 'va-brand-search') {
      va_update_model_options('');
    }
    va_load_listings(1);
  });

  $(document).on('input', '#va-kw, #va-min-price, #va-max-price, #va-year-min, #va-year-max, #va-mileage-min, #va-mileage-max, #va-engine-min, #va-engine-max', function() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() { va_load_listings(1); }, 450);
  });

  $(document).on('change', '.va-car-filter, #va-opt-automatic, #va-opt-awd, #va-opt-service-book', function() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() { va_load_listings(1); }, 450);
  });

  $(document).on('click', '#va-filter-reset', function() {
    $('#va-filter-form')[0].reset();
    va_update_model_options('');
    va_update_range();
    va_load_listings(1);
  });

  $(document).on('click', '#va-advanced-toggle', function() {
    var $btn = $(this);
    var $panel = $('#va-advanced-panel');
    if (!$panel.length) return;

    var isOpen = $btn.attr('aria-expanded') === 'true';
    if (isOpen) {
      $panel.stop(true, true).slideUp(180).addClass('is-collapsed');
      $btn.attr('aria-expanded', 'false');
    } else {
      $panel.stop(true, true).slideDown(180).removeClass('is-collapsed');
      $btn.attr('aria-expanded', 'true');
    }
  });

  // ── Pagination build ─────────────────────────────────────
  function va_build_pagination(maxPages, currentPage) {
    var $pag = $('#va-pagination');
    if (!$pag.length || maxPages <= 1) { $pag.empty(); return; }

    var html = '';
    if (currentPage > 1) html += '<button class="va-page-btn" data-page="' + (currentPage - 1) + '">⟨</button>';
    for (var i = 1; i <= maxPages; i++) {
      html += '<button class="va-page-btn' + (i === currentPage ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
    }
    if (currentPage < maxPages) html += '<button class="va-page-btn" data-page="' + (currentPage + 1) + '">⟩</button>';

    $pag.html(html);
  }

  $(document).on('click', '.va-page-btn', function() {
    va_load_listings(parseInt($(this).data('page')));
    $('html,body').animate({ scrollTop: $('#va-listing-results').offset().top - 80 }, 300);
  });

  function va_count_update(total) {
    $('#va-results-count').text(total + ' hirdetés');
  }

  // ── Nézet váltó (rács / lista) ───────────────────────────
  $('#va-view-grid').on('click', function() {
    $('#va-listing-results').removeClass('va-grid--list');
    $('#va-view-grid').addClass('active');
    $('#va-view-list').removeClass('active');
    localStorage.setItem('va_view', 'grid');
  });
  $('#va-view-list').on('click', function() {
    $('#va-listing-results').addClass('va-grid--list');
    $('#va-view-list').addClass('active');
    $('#va-view-grid').removeClass('active');
    localStorage.setItem('va_view', 'list');
  });
  // Mentett nézet visszaállítása
  if (localStorage.getItem('va_view') === 'list') {
    $('#va-listing-results').addClass('va-grid--list');
    $('#va-view-list').addClass('active');
    $('#va-view-grid').removeClass('active');
  } else {
    $('#va-view-grid').addClass('active');
  }

  // ── Galéria kapcsoló (detail oldal) ─────────────────────
  $(document).on('click', '.va-listing-detail__thumb', function() {
    var src = $(this).data('src');
    $('.va-listing-detail__main-img').attr('src', src);
    $('.va-listing-detail__thumb').removeClass('active');
    $(this).addClass('active');
  });

  // ── Kapcsolat megjelenítés (telefonszám) ─────────────────
  $(document).on('click', '.va-contact-box__show-btn', function() {
    var phone = $(this).data('phone');
    $(this).replaceWith('<a href="tel:' + phone + '" class="va-contact-box__phone">📞 ' + phone + '</a>');
  });

  // ── Dashboard tab navigáció ──────────────────────────────
  $(document).on('click', '.va-dashboard__nav-item[data-tab]', function(e) {
    e.preventDefault();
    var tab = $(this).data('tab');
    $('.va-dashboard__nav-item').removeClass('active');
    $(this).addClass('active');
    $('.va-dashboard__section').removeClass('active');
    $('#va-tab-' + tab).addClass('active');
    history.pushState(null, '', '#' + tab);
  });

  // URL hash alapú tab nyitás
  var hash = window.location.hash.replace('#', '');
  if (hash && $('.va-dashboard__nav-item[data-tab="' + hash + '"]').length) {
    $('.va-dashboard__nav-item[data-tab="' + hash + '"]').trigger('click');
  }

  // ── Regisztráció: cég/magánszemély interakció + typing ─────────────────
  function va_init_register_form() {
    var $form = $('.va-register-form');
    if (!$form.length) {
      return;
    }

    var $switch = $('#va-account-type-switch');
    var $hidden = $('#va-account-type');
    var $companyBox = $('.va-register-company');
    var $companyFields = $companyBox.find('[data-company-field="1"]');

    function applyAccountType(isCompany) {
      var type = isCompany ? 'company' : 'private';
      $hidden.val(type);
      $companyBox.toggleClass('is-active', isCompany).attr('aria-hidden', isCompany ? 'false' : 'true');

      $companyFields.each(function() {
        this.disabled = !isCompany;
        this.required = isCompany;
      });
    }

    $switch.on('change', function() {
      applyAccountType(this.checked);
    });

    applyAccountType($switch.is(':checked'));

    var typingTimers = [];
    $form.find('.va-input[data-typing]').each(function(fieldIndex) {
      var input = this;
      var phrases = (input.getAttribute('data-typing') || '')
        .split('|')
        .map(function(txt) { return txt.trim(); })
        .filter(function(txt) { return txt.length > 0; });

      if (!phrases.length) {
        return;
      }

      var phraseIndex = 0;
      var charIndex = 0;
      var deleting = false;

      function tick() {
        if (document.activeElement === input && input.value !== '') {
          typingTimers[fieldIndex] = setTimeout(tick, 150);
          return;
        }

        var phrase = phrases[phraseIndex];
        if (!deleting) {
          charIndex += 1;
          input.setAttribute('placeholder', phrase.slice(0, charIndex));

          if (charIndex >= phrase.length) {
            deleting = true;
            typingTimers[fieldIndex] = setTimeout(tick, 950);
            return;
          }

          typingTimers[fieldIndex] = setTimeout(tick, 55);
          return;
        }

        charIndex = Math.max(0, charIndex - 1);
        input.setAttribute('placeholder', phrase.slice(0, charIndex));

        if (charIndex === 0) {
          deleting = false;
          phraseIndex = (phraseIndex + 1) % phrases.length;
          typingTimers[fieldIndex] = setTimeout(tick, 240);
          return;
        }

        typingTimers[fieldIndex] = setTimeout(tick, 32);
      }

      typingTimers[fieldIndex] = setTimeout(tick, 260 + (fieldIndex * 90));
    });

    $form.on('submit', function() {
      var $btn = $(this).find('button[type="submit"]');
      $btn.addClass('is-loading').prop('disabled', true).text('Regisztráció folyamatban...');
    });
  }
  va_init_register_form();

  // ── Scroll animáció ──────────────────────────────────────
  function va_init_animate() {
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.va-animate').forEach(function(el) {
      observer.observe(el);
    });
  }
  va_init_animate();

  // ── Oldal betöltésekor szűrő init ────────────────────────
  if ($('#va-filter-form').length && $('#va-listing-results').length) {
    if (typeof VA_Data !== 'undefined') {
      if (VA_Data.initial_s)          { $('#va-kw').val(VA_Data.initial_s); }
      if (parseInt(VA_Data.initial_cat) > 0)        { $('#va-cat').val(VA_Data.initial_cat); }
      if (VA_Data.initial_author_id)  { $('#va-filter-form').data('author-id', VA_Data.initial_author_id); }
      if (VA_Data.initial_post_type)  { $('#va-filter-form').data('post-type', VA_Data.initial_post_type); }
      if (VA_Data.initial_brand)      { $('#va-brand-search').val(VA_Data.initial_brand); }
      va_update_model_options(VA_Data.initial_model || '');
      if (VA_Data.initial_model)      { $('#va-model-search').val(VA_Data.initial_model); }
    } else {
      va_update_model_options('');
    }
    va_load_listings(1);
  }

  // ── OverlayScrollbars inicializálás ──────────────────────
  function va_init_overlay_scrollbars() {
    if (typeof OverlayScrollbars === 'undefined') {
      return;
    }

    var scrollbarOptions = {
      scrollbars: {
        autoHide: 'never',
        clickScroll: false
      },
      overflow: {
        x: 'hidden',
        y: 'scroll'
      },
      update: {
        elementEvents: ['cloned', 'added', 'removed'],
        debounce: [0, 33]
      }
    };

    // Teljes oldal scrollja is menjen custom scrollbaron, kulonben Firefox
    // a natív vékony csíkot rajzolja a viewport jobb szélén.
    if (document.body && !document.body.hasAttribute('data-va-body-scrollbars-init')) {
      OverlayScrollbars(document.body, scrollbarOptions);
      document.body.setAttribute('data-va-body-scrollbars-init', '1');
    }

    // #va-sidebar (admin)
    var sidebarEl = document.getElementById('va-sidebar');
    if (sidebarEl) {
      OverlayScrollbars(sidebarEl, scrollbarOptions);
    }

    // .va-dashboard__nav (frontend dashboard) - egyseges custom scrollbar
    // minden bongeszoben, hogy Firefox alatt se essen vissza 1px natívra.
    var dashNavEl = document.querySelector('.va-dashboard__nav');
    if (dashNavEl) {
      OverlayScrollbars(dashNavEl, scrollbarOptions);
      dashNavEl.classList.add('va-dashboard__nav--os');
    }
  }

  // Várakozás OverlayScrollbars betöltésére (ha async kezelés szükséges)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', va_init_overlay_scrollbars);
  } else {
    va_init_overlay_scrollbars();
  }

})(jQuery);
