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

      var wrapper = btn.closest ? btn.closest('.fbpro-btn-wrapper') : null;
      var el = wrapper || btn;
      el.style.display = (dayOk && timeOk) ? '' : 'none';
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

    /* ── Bocadillos ──────────────────────────────────────────── */
    function adjustBubblePosition(bubble) {
      var wrapper = bubble.closest('.fbpro-btn-wrapper');
      if (!wrapper) return;

      var wrapperRect = wrapper.getBoundingClientRect();
      var vw     = window.innerWidth;
      var vh     = window.innerHeight;
      var margin = 14;

      var currentPos = null;
      ['left', 'right', 'top', 'bottom'].forEach(function (pos) {
        if (bubble.classList.contains('fbpro-bubble--' + pos)) currentPos = pos;
      });
      if (!currentPos) return;

      // Medir el bubble en posición neutral (fixed en 0,0) para
      // obtener sus dimensiones reales sin que el viewport lo recorte
      bubble.classList.remove('fbpro-bubble--' + currentPos);
      bubble.style.position  = 'fixed';
      bubble.style.top       = '0';
      bubble.style.left      = '0';
      bubble.style.right     = 'auto';
      bubble.style.bottom    = 'auto';
      bubble.style.transform = 'none';
      bubble.offsetHeight; // reflow

      var bubbleW = bubble.offsetWidth;
      var bubbleH = bubble.offsetHeight;

      // Restaurar
      bubble.style.position  = '';
      bubble.style.top       = '';
      bubble.style.left      = '';
      bubble.style.right     = '';
      bubble.style.bottom    = '';
      bubble.style.transform = '';
      bubble.classList.add('fbpro-bubble--' + currentPos);

      var spaceTop    = wrapperRect.top;
      var spaceBottom = vh - wrapperRect.bottom;
      var spaceLeft   = wrapperRect.left;
      var spaceRight  = vw - wrapperRect.right;

      var newPos = currentPos;
      switch (currentPos) {
        case 'top':
          if (spaceTop < bubbleH + margin && spaceBottom >= bubbleH + margin) newPos = 'bottom';
          break;
        case 'bottom':
          if (spaceBottom < bubbleH + margin && spaceTop >= bubbleH + margin) newPos = 'top';
          break;
        case 'left':
          if (spaceLeft < bubbleW + margin && spaceRight >= bubbleW + margin) newPos = 'right';
          break;
        case 'right':
          if (spaceRight < bubbleW + margin && spaceLeft >= bubbleW + margin) newPos = 'left';
          break;
      }

      if (newPos !== currentPos) {
        bubble.classList.remove('fbpro-bubble--' + currentPos);
        bubble.classList.add('fbpro-bubble--' + newPos);
      }
    }

    function closeBubble(bubble, userClosed) {
      bubble.classList.remove('is-visible');
      setTimeout(function () { bubble.style.display = 'none'; }, 300);
      if (userClosed && bubble.dataset.rememberClose === '1') {
        sessionStorage.setItem('fbpro_bubble_closed_' + bubble.dataset.bubbleId, '1');
      }
    }

    document.querySelectorAll('.fbpro-bubble').forEach(function (bubble) {
      var bubbleId     = bubble.dataset.bubbleId;
      var delay        = parseInt(bubble.dataset.delay, 10) * 1000;
      var autoClose    = parseInt(bubble.dataset.autoClose, 10) * 1000;
      var remember     = bubble.dataset.rememberClose === '1';
      var closeOutside = bubble.dataset.closeOnOutside === '1';

      if (remember && sessionStorage.getItem('fbpro_bubble_closed_' + bubbleId)) return;

      setTimeout(function () {
        var wrapper = bubble.closest('.fbpro-btn-wrapper');
        var btn     = wrapper ? wrapper.querySelector('.fbpro-btn') : null;
        if (wrapper && wrapper.style.display === 'none') return;
        if (btn && btn.style.display === 'none') return;

        // Mostrar invisible para medir antes de animar
        bubble.style.visibility = 'hidden';
        bubble.style.display = '';
        bubble.offsetHeight;

        adjustBubblePosition(bubble);

        bubble.style.visibility = '';
        bubble.offsetHeight;
        bubble.classList.add('is-visible');

        if (autoClose > 0) {
          setTimeout(function () { closeBubble(bubble, false); }, autoClose);
        }
      }, delay);

      var closeBtn = bubble.querySelector('.fbpro-bubble__close');
      if (closeBtn) {
        closeBtn.addEventListener('click', function () { closeBubble(bubble, true); });
      }

      if (closeOutside) {
        document.addEventListener('click', function (e) {
          if (!bubble.contains(e.target) && !e.target.closest('.fbpro-btn-wrapper')) {
            closeBubble(bubble, true);
          }
        });
      }
    });

    window.addEventListener('resize', function () {
      document.querySelectorAll('.fbpro-bubble.is-visible').forEach(adjustBubblePosition);
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
