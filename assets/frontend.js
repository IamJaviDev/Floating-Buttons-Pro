/* Floating Buttons Pro – Frontend JS v2.0 */
(function () {
  'use strict';

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
