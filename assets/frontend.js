/* Floating Buttons Pro – Frontend JS v2.0 */
(function () {
  'use strict';

  /* ── Programación horaria ────────────────────────────────────── */
  function parseScheduleTime(str) {
    var parts = (str || '00:00').split(':');
    return (parseInt(parts[0], 10) || 0) * 60 + (parseInt(parts[1], 10) || 0);
  }

  function checkSchedules() {
    var scheduled = document.querySelectorAll('.fbpro-btn[data-schedule="1"]');
    if (!scheduled.length) return;

    var now           = new Date();
    var offsetMin     = (typeof fbproFrontend !== 'undefined' && fbproFrontend.timezoneOffset) ? fbproFrontend.timezoneOffset : 0;
    var localOffsetMin = -now.getTimezoneOffset();
    var wpTime        = new Date(now.getTime() + (offsetMin - localOffsetMin) * 60000);

    var day        = wpTime.getUTCDay();
    day            = day === 0 ? 7 : day; // ISO-8601: 1=L … 7=D
    var currentMin = wpTime.getUTCHours() * 60 + wpTime.getUTCMinutes();

    scheduled.forEach(function (btn) {
      var days = btn.dataset.scheduleDays ? btn.dataset.scheduleDays.split(',').map(Number) : [];
      var from = parseScheduleTime(btn.dataset.scheduleFrom);
      var to   = parseScheduleTime(btn.dataset.scheduleTo);

      var dayOk  = days.indexOf(day) !== -1;
      var timeOk;
      if (from === to) {
        timeOk = false;
      } else if (from < to) {
        timeOk = currentMin >= from && currentMin < to;
      } else {
        // Franja que cruza medianoche (ej: 22:00 → 06:00)
        timeOk = currentMin >= from || currentMin < to;
      }

      btn.style.display = (dayOk && timeOk) ? '' : 'none';
    });
  }

  checkSchedules();
  setInterval(checkSchedules, 60000);

  document.addEventListener('DOMContentLoaded', function () {

    /* ── Popups ──────────────────────────────────────────────── */
    document.querySelectorAll('[data-fbpro-popup]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var id      = btn.dataset.fbproPopup;
        var overlay = document.getElementById('fbpro-overlay-' + id);
        if (overlay) openPopup(overlay);
      });
    });

    document.querySelectorAll('.fbpro-overlay').forEach(function (overlay) {
      var closeBtn = overlay.querySelector('.fbpro-popup__close');
      if (closeBtn) {
        closeBtn.addEventListener('click', function () { closePopup(overlay); });
      }
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closePopup(overlay);
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.fbpro-overlay.is-open').forEach(closePopup);
      }
    });

    function openPopup(overlay) {
      overlay.setAttribute('aria-hidden', 'false');
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      var closeBtn = overlay.querySelector('.fbpro-popup__close');
      if (closeBtn) closeBtn.focus();
    }

    function closePopup(overlay) {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

  });
})();
