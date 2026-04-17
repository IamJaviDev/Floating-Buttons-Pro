/* Floating Buttons Pro – Admin JS */
(function ($) {
  'use strict';

  $(document).ready(function () {

    /* ── Tabs ── */
    $('.fbpro-tab').on('click', function () {
      var tab = $(this).data('tab');
      $('.fbpro-tab').removeClass('active');
      $(this).addClass('active');
      $('.fbpro-tab-panel').removeClass('active');
      $('#tab-' + tab).addClass('active');
    });

    /* ── Color picker ── */
    $('.fbpro-color-picker').wpColorPicker();

    /* ── Mostrar/ocultar config popup al cambiar acción ── */
    $('input[name="fbpro_phone_action"]').on('change', function () {
      $('#phone-popup-config').toggle($(this).val() === 'popup');
    });
    $('input[name="fbpro_wa_action"]').on('change', function () {
      $('#wa-popup-config').toggle($(this).val() === 'popup');
    });

    /* ── Cambiar labels según modo shortcode/html ── */
    function updateModeLabels($card) {
      var mode = $card.find('.fbpro-popup-mode-select').val();
      var isHtml = mode === 'html';
      $card.find('.fbpro-label-shortcode').toggle(!isHtml);
      $card.find('.fbpro-label-html').toggle(isHtml);
      $card.find('.fbpro-hint-shortcode').toggle(!isHtml);
      $card.find('.fbpro-hint-html').toggle(isHtml);
    }

    $('.fbpro-popup-mode-select').on('change', function () {
      updateModeLabels($(this).closest('.fbpro-card'));
    }).each(function () {
      updateModeLabels($(this).closest('.fbpro-card'));
    });

  });

})(jQuery);
