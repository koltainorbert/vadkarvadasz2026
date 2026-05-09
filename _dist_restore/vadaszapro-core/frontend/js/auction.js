/* jshint esversion: 6 */
/**
 * VadászApró – Aukció JavaScript
 * Real-time visszaszámlálás, licit AJAX, bid history auto-refresh
 */
(function($) {
  'use strict';

  var auctionId   = typeof VA_AuctionData !== 'undefined' ? VA_AuctionData.auction_id : 0;
  var refreshInt  = null;

  if (!auctionId) return;

  // ── Visszaszámlálás ──────────────────────────────────────
  function startCountdown() {
    $('.va-countdown').each(function() {
      var $el  = $(this);
      var end  = parseInt($el.data('end'));

      function tick() {
        var diff = end - Math.floor(Date.now() / 1000);
        if (diff <= 0) {
          $el.text('⌛ Lejárt').addClass('urgent');
          clearInterval(timer);
          return;
        }
        var d  = Math.floor(diff / 86400);
        var h  = Math.floor((diff % 86400) / 3600);
        var m  = Math.floor((diff % 3600) / 60);
        var s  = diff % 60;

        var text = '';
        if (d > 0) text += d + 'n ';
        text += pad(h) + ':' + pad(m) + ':' + pad(s);
        $el.text(text);

        if (diff < 3600) $el.addClass('urgent');
      }
      tick();
      var timer = setInterval(tick, 1000);
    });
  }

  function pad(n) { return n < 10 ? '0' + n : n; }
  startCountdown();

  // ── Bid státusz lekérés ──────────────────────────────────
  function refreshBidStatus() {
    $.get(VA_Auction.ajax_url, {
      action:     'va_get_bid_status',
      auction_id: auctionId
    }, function(res) {
      if (!res.success) return;
      var d = res.data;
      $('#va-current-bid').text(d.current_bid_fmt);
      $('#va-bid-count').text(d.bid_count + ' licit');
      $('#va-min-bid').val(d.min_next_bid).attr('min', d.min_next_bid);
      $('#va-min-bid-hint').text('Min. licit: ' + d.min_next_bid.toLocaleString('hu-HU') + ' Ft');

      if (d.is_over) {
        $('#va-bid-form').hide();
        $('#va-auction-over').show();
        clearInterval(refreshInt);
      }
    });
  }

  refreshInt = setInterval(refreshBidStatus, 10000); // 10mp-enként

  // ── Licit leadás ────────────────────────────────────────
  $('#va-bid-submit').on('click', function(e) {
    e.preventDefault();
    var amount = parseFloat($('#va-min-bid').val());

    if (isNaN(amount) || amount <= 0) {
      va_auction_notice('error', 'Kérjük adjon meg összeget.');
      return;
    }

    if (!confirm(VA_Auction.strings.confirm_bid + '\n' + amount.toLocaleString('hu-HU') + ' Ft')) return;

    $(this).prop('disabled', true).text('Küldés...');

    $.post(VA_Auction.ajax_url, {
      action:     'va_place_bid',
      nonce:      VA_Auction.nonce,
      auction_id: auctionId,
      amount:     amount
    }, function(res) {
      $('#va-bid-submit').prop('disabled', false).text('Licitálok');

      if (res.success) {
        va_auction_notice('success', res.data.message);
        refreshBidStatus();
        if (res.data.buyout) {
          $('#va-bid-form').hide();
          $('#va-auction-over').show();
          clearInterval(refreshInt);
        }
      } else {
        va_auction_notice('error', res.data.message);
      }
    }).fail(function() {
      $('#va-bid-submit').prop('disabled', false).text('Licitálok');
      va_auction_notice('error', 'Hálózati hiba. Próbálja újra.');
    });
  });

  function va_auction_notice(type, msg) {
    var $n = $('<div class="va-notice va-notice--' + type + '">' + $('<div>').text(msg).html() + '</div>');
    $('#va-bid-notice').html($n);
    setTimeout(function() { $n.fadeOut(); }, 5000);
  }

})(jQuery);
