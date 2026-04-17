/* Floating Buttons Pro – Admin JS v2.0 */
(function ($) {
  'use strict';

  var FBPro = {

    buttons: [],
    editingId: null,
    activeMTab: 'contenido',

    /* ── Init ──────────────────────────────────────────────── */
    init: function () {
      this.buttons = (window.fbproData && fbproData.buttons) ? fbproData.buttons : [];
      this.renderList();
      this.bindEvents();
      if (window.fbproData && fbproData.setupPending) {
        $('#fbpro-setup-banner').show();
      }
    },

    /* ══════════════════════════════════════════════════════════
       LISTA DE BOTONES
       ══════════════════════════════════════════════════════════ */
    renderList: function () {
      var self = this;
      var $list = $('#fbpro-buttons-list');
      var sorted = this.buttons.slice().sort(function (a, b) {
        return (a.order || 0) - (b.order || 0);
      });

      if (sorted.length === 0) {
        $list.html('<div class="fbpro-empty-state"><p>No hay botones. Pulsa <strong>+ Añadir botón</strong> para crear el primero.</p></div>');
        $('#fbpro-warning-many').hide();
        return;
      }

      var html = '';
      sorted.forEach(function (btn) {
        html += self.cardHtml(btn);
      });
      $list.html(html);

      $('#fbpro-warning-many').toggle(sorted.length > 10);
      this.initSortable();
    },

    cardHtml: function (btn) {
      var bg     = btn.bg_color || '#2A90A0';
      var radius = btn.radius !== undefined ? btn.radius : 16;
      var size   = btn.size   || 56;
      // Scale radius to preview size
      var previewRadius = Math.round(radius * 38 / size);

      var previewStyle = [
        'background:' + bg,
        'width:38px',
        'height:38px',
        'border-radius:' + previewRadius + 'px',
        'display:inline-flex',
        'align-items:center',
        'justify-content:center',
        'flex-shrink:0',
        'box-shadow:0 2px 8px rgba(0,0,0,.18)',
      ].join(';');

      var iconColor = btn.icon_color || '#ffffff';
      var iconSvgHtml = this.svgPreviewHtml(btn, iconColor, '55%');

      var meta = '';
      if (btn.action_type === 'popup') {
        meta = '<span class="fbpro-badge fbpro-badge--popup">Popup</span>';
      } else {
        meta = '<span class="fbpro-card-url">' + this.escHtml(this.truncate(btn.url || '—', 45)) + '</span>';
      }

      var toggleChecked = btn.active ? 'checked' : '';
      var cardClass = 'fbpro-btn-card' + (btn.active ? '' : ' fbpro-btn-card--inactive');
      var id = this.escAttr(btn.id);

      return '<div class="' + cardClass + '" data-id="' + id + '">' +
        '<div class="fbpro-card-handle" title="Arrastrar para reordenar"><svg viewBox="0 0 20 20" width="16" height="16" fill="#999"><circle cx="7" cy="5" r="1.5"/><circle cx="7" cy="10" r="1.5"/><circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="5" r="1.5"/><circle cx="13" cy="10" r="1.5"/><circle cx="13" cy="15" r="1.5"/></svg></div>' +
        '<div class="fbpro-card-preview" style="' + previewStyle + '">' + iconSvgHtml + '</div>' +
        '<div class="fbpro-card-info">' +
          '<div class="fbpro-card-label">' + this.escHtml(btn.label || 'Sin nombre') + '</div>' +
          '<div class="fbpro-card-meta">' + meta + '</div>' +
        '</div>' +
        '<div class="fbpro-card-actions">' +
          '<label class="fbpro-switch" title="' + (btn.active ? 'Desactivar' : 'Activar') + '">' +
            '<input type="checkbox" class="fbpro-toggle-active" data-id="' + id + '" ' + toggleChecked + '>' +
            '<span class="fbpro-switch-slider"></span>' +
          '</label>' +
          '<button type="button" class="fbpro-card-btn fbpro-card-edit" data-id="' + id + '" title="Editar">✎</button>' +
          '<button type="button" class="fbpro-card-btn fbpro-card-dupe" data-id="' + id + '" title="Duplicar">⧉</button>' +
          '<button type="button" class="fbpro-card-btn fbpro-card-del fbpro-card-del--danger" data-id="' + id + '" title="Eliminar">✕</button>' +
        '</div>' +
      '</div>';
    },

    svgPreviewHtml: function (btn, color, size) {
      size = size || '55%';
      if (btn.icon_type === 'image' && btn.icon_image_url) {
        return '<img src="' + this.escAttr(btn.icon_image_url) + '" style="width:' + size + ';height:' + size + ';object-fit:contain;pointer-events:none;flex-shrink:0;" alt="">';
      }
      var lib  = (window.fbproData && fbproData.svgLibrary) ? fbproData.svgLibrary : {};
      var slug = btn.icon_svg || 'phone';
      var item = lib[slug] || lib['phone'] || {};
      var svgHtml = item.svg || '';
      // SVGs usan fill="currentColor" — el color se hereda via la propiedad CSS color
      return '<span class="fbpro-icon-wrap" style="color:' + this.escAttr(color) + ';width:' + size + ';height:' + size + ';" aria-hidden="true">' + svgHtml + '</span>';
    },

    /* ── Sortable ──────────────────────────────────────────── */
    initSortable: function () {
      var self = this;
      $('#fbpro-buttons-list').sortable({
        handle: '.fbpro-card-handle',
        axis: 'y',
        tolerance: 'pointer',
        cursor: 'grabbing',
        update: function () {
          var order = [];
          $('#fbpro-buttons-list .fbpro-btn-card').each(function (i) {
            order.push({ id: $(this).data('id'), order: i });
          });
          // Update local state
          order.forEach(function (item) {
            var btn = self.getButton(item.id);
            if (btn) btn.order = item.order;
          });
          // AJAX save
          $.post(fbproData.ajaxUrl, {
            action: 'fbpro_reorder_buttons',
            nonce:  fbproData.nonce,
            order:  JSON.stringify(order),
          });
        },
      });
    },

    /* ══════════════════════════════════════════════════════════
       MODAL
       ══════════════════════════════════════════════════════════ */
    openModal: function (btn) {
      var isNew = !btn;
      this.editingId = btn ? btn.id : null;

      if (isNew) {
        btn = $.extend(true, {}, (fbproData.defaults || {}));
        btn.id = '';
      }

      $('#fbpro-modal-title').text(isNew ? 'Nuevo botón' : 'Editar: ' + (btn.label || 'Botón'));
      this.renderModalForm(btn);
      this.switchMTab('contenido');

      $('#fbpro-modal').attr('aria-hidden', 'false').addClass('is-open');
      $('body').addClass('fbpro-modal-open');
      setTimeout(function () { $('#fbpro-field-label').trigger('focus'); }, 120);
    },

    closeModal: function () {
      $('#fbpro-modal').attr('aria-hidden', 'true').removeClass('is-open');
      $('body').removeClass('fbpro-modal-open');
      this.editingId = null;
    },

    switchMTab: function (tab) {
      this.activeMTab = tab;
      $('.fbpro-mtab').removeClass('active').filter('[data-mtab="' + tab + '"]').addClass('active');
      $('.fbpro-mpanel').hide();
      $('#fbpro-mp-' + tab).show();
    },

    renderModalForm: function (btn) {
      $('#fbpro-modal-body').html(this.modalFormHtml(btn));
      this.updateActionFields();
      this.updateIconFields();
    },

    updateActionFields: function () {
      var action = $('input[name="fbpro_action_type"]:checked').val();
      $('#fbpro-url-fields').toggle(action === 'link');
    },

    updateIconFields: function () {
      var type = $('input[name="fbpro_icon_type"]:checked').val();
      $('#fbpro-svg-picker').toggle(type !== 'image');
      $('#fbpro-image-picker').toggle(type === 'image');
    },

    updatePopupPanel: function () {
      var action = $('input[name="fbpro_action_type"]:checked').val();
      $('#fbpro-popup-notice').toggle(action !== 'popup');
      $('#fbpro-popup-fields').toggle(action === 'popup');
    },

    modalFormHtml: function (btn) {
      var self = this;
      var lib  = (window.fbproData && fbproData.svgLibrary) ? fbproData.svgLibrary : {};

      var shadowSel = function (val) {
        return [
          '<option value="0"' + (btn.shadow == 0 ? ' selected' : '') + '>Sin sombra</option>',
          '<option value="1"' + (btn.shadow == 1 ? ' selected' : '') + '>Suave</option>',
          '<option value="2"' + ((btn.shadow == 2 || btn.shadow === undefined) ? ' selected' : '') + '>Media</option>',
          '<option value="3"' + (btn.shadow == 3 ? ' selected' : '') + '>Intensa</option>',
        ].join('');
      };

      var radio = function (name, value, checked, label) {
        return '<label class="fbpro-radio"><input type="radio" name="' + name + '" value="' + value + '" ' + (checked ? 'checked' : '') + '> ' + label + '</label>';
      };

      // SVG grid
      var svgGrid = '<div class="fbpro-svg-grid">';
      Object.keys(lib).forEach(function (slug) {
        var item = lib[slug];
        var sel  = slug === (btn.icon_svg || 'phone');
        svgGrid += '<label class="fbpro-svg-item' + (sel ? ' is-selected' : '') + '" title="' + self.escAttr(item.label) + '">' +
          '<input type="radio" name="fbpro_icon_svg" value="' + slug + '"' + (sel ? ' checked' : '') + ' style="display:none">' +
          item.svg +
          '<span>' + self.escHtml(item.label) + '</span>' +
        '</label>';
      });
      svgGrid += '</div>';

      var isPopup  = btn.action_type === 'popup';
      var isImage  = btn.icon_type === 'image';

      return [
        /* ── Contenido ─────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-contenido">',
          '<div class="fbpro-field">',
            '<label>Nombre interno <small>(solo visible en el admin)</small></label>',
            '<input type="text" id="fbpro-field-label" value="' + this.escAttr(btn.label || '') + '" placeholder="Ej: Botón WhatsApp">',
          '</div>',
          '<div class="fbpro-field">',
            '<label>Acción al pulsar</label>',
            '<div class="fbpro-radio-group">',
              radio('fbpro_action_type', 'link',  !isPopup, 'Enlace directo'),
              radio('fbpro_action_type', 'popup',  isPopup, 'Abrir popup'),
            '</div>',
          '</div>',
          '<div id="fbpro-url-fields"' + (isPopup ? ' style="display:none"' : '') + '>',
            '<div class="fbpro-field">',
              '<label>URL <small>(tel:, https://, mailto:…)</small></label>',
              '<input type="text" id="fbpro-field-url" value="' + this.escAttr(btn.url || '') + '" placeholder="tel:+34627564896">',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Abrir en</label>',
              '<select id="fbpro-field-target">',
                '<option value="_blank"' + (btn.target === '_blank' ? ' selected' : '') + '>Nueva pestaña</option>',
                '<option value="_self"'  + (btn.target === '_self'  ? ' selected' : '') + '>Misma pestaña</option>',
              '</select>',
            '</div>',
          '</div>',
          '<div class="fbpro-field">',
            '<label>Tooltip <small>(texto al pasar el ratón)</small></label>',
            '<input type="text" id="fbpro-field-tooltip" value="' + this.escAttr(btn.tooltip || '') + '" placeholder="Llamar ahora">',
          '</div>',
        '</div>',

        /* ── Icono ──────────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-icono" style="display:none">',
          '<div class="fbpro-field">',
            '<label>Tipo de icono</label>',
            '<div class="fbpro-radio-group">',
              radio('fbpro_icon_type', 'svg',   !isImage, 'Librería SVG'),
              radio('fbpro_icon_type', 'image',  isImage, 'Imagen personalizada'),
            '</div>',
          '</div>',
          '<div id="fbpro-svg-picker"' + (isImage ? ' style="display:none"' : '') + '>',
            svgGrid,
          '</div>',
          '<div id="fbpro-image-picker"' + (!isImage ? ' style="display:none"' : '') + '>',
            '<div class="fbpro-field">',
              '<button type="button" id="fbpro-select-image-btn" class="fbpro-btn-secondary">Seleccionar imagen</button>',
              '<input type="hidden" id="fbpro-field-icon-image-id" value="' + (btn.icon_image_id || 0) + '">',
              '<div id="fbpro-image-preview-wrap" style="margin-top:10px">',
                (btn.icon_image_url ? '<img id="fbpro-icon-image-preview" src="' + this.escAttr(btn.icon_image_url) + '" style="max-width:80px;max-height:80px;border-radius:8px;border:2px solid #e5e7eb;">' : ''),
              '</div>',
            '</div>',
          '</div>',
          '<div class="fbpro-field" style="margin-top:16px">',
            '<label>Tamaño del icono (%)</label>',
            '<input type="number" id="fbpro-field-icon-size" min="10" max="90" value="' + (btn.icon_size !== undefined ? btn.icon_size : 46) + '">',
            '<p class="fbpro-help">Porcentaje respecto al tamaño del botón. Por defecto: 46%.</p>',
          '</div>',
        '</div>',

        /* ── Estilo ─────────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-estilo" style="display:none">',
          '<div class="fbpro-grid-2">',
            '<div class="fbpro-field">',
              '<label>Color de fondo</label>',
              '<input type="color" id="fbpro-field-bg-color" value="' + (btn.bg_color || '#2A90A0') + '">',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Color del icono</label>',
              '<input type="color" id="fbpro-field-icon-color" value="' + (btn.icon_color || '#ffffff') + '">',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Tamaño del botón (px)</label>',
              '<input type="number" id="fbpro-field-size" min="32" max="120" value="' + (btn.size || 56) + '">',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Border radius (px)</label>',
              '<input type="number" id="fbpro-field-radius" min="0" max="100" value="' + (btn.radius !== undefined ? btn.radius : 16) + '">',
              '<p class="fbpro-help">0 = cuadrado · 50+ = círculo</p>',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Sombra</label>',
              '<select id="fbpro-field-shadow">' + shadowSel() + '</select>',
            '</div>',
          '</div>',
          '<div class="fbpro-field" style="margin-top:16px">',
            '<label>Efecto hover</label>',
            '<div class="fbpro-radio-group">',
              radio('fbpro_hover', 'scale',      btn.hover_effect === 'scale'      || !btn.hover_effect, 'Scale (crece levemente)'),
              radio('fbpro_hover', 'pulse',      btn.hover_effect === 'pulse',      'Pulse (latido rápido)'),
              radio('fbpro_hover', 'brightness', btn.hover_effect === 'brightness', 'Brightness (se aclara)'),
              radio('fbpro_hover', 'none',       btn.hover_effect === 'none',       'Ninguno'),
            '</div>',
          '</div>',
        '</div>',

        /* ── Popup ──────────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-popup" style="display:none">',
          '<div class="fbpro-info-box" id="fbpro-popup-notice"' + (isPopup ? ' style="display:none"' : '') + '>',
            'Cambia la acción a <strong>Abrir popup</strong> en la pestaña <em>Contenido</em> para configurar esta sección.',
          '</div>',
          '<div id="fbpro-popup-fields"' + (!isPopup ? ' style="display:none"' : '') + '>',
            '<div class="fbpro-field">',
              '<label>Modo del popup</label>',
              '<select id="fbpro-field-popup-mode">',
                '<option value="shortcode"' + (btn.popup_mode !== 'html' ? ' selected' : '') + '>Shortcode (CF7 u otro)</option>',
                '<option value="html"'      + (btn.popup_mode === 'html' ? ' selected' : '') + '>HTML libre</option>',
              '</select>',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Contenido del popup</label>',
              '<textarea id="fbpro-field-popup-content" rows="6" placeholder="[contact-form-7 id=&quot;123&quot; title=&quot;Contacto&quot;]">' + this.escHtml(btn.popup_content || '') + '</textarea>',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Mostrar popup solo en estas páginas <small>(vacío = todas)</small></label>',
              '<textarea id="fbpro-field-popup-pages" rows="5" placeholder="/landing/&#10;posttype:ciudades">' + this.escHtml(btn.popup_pages || '') + '</textarea>',
              '<p class="fbpro-help">Una regla por línea. Vacío = aparece en toda la web.</p>',
            '</div>',
          '</div>',
        '</div>',

        /* ── Visibilidad ────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-visibilidad" style="display:none">',
          '<div class="fbpro-field">',
            '<label class="fbpro-toggle">',
              '<input type="checkbox" id="fbpro-field-hide-mobile" ' + (btn.hide_mobile ? 'checked' : '') + '>',
              '<span>Ocultar en móvil <small>(&lt;768px)</small></span>',
            '</label>',
          '</div>',
          '<div class="fbpro-field">',
            '<label class="fbpro-toggle">',
              '<input type="checkbox" id="fbpro-field-hide-desktop" ' + (btn.hide_desktop ? 'checked' : '') + '>',
              '<span>Ocultar en escritorio <small>(&gt;768px)</small></span>',
            '</label>',
          '</div>',
          '<div class="fbpro-field" style="margin-top:16px">',
            '<label>Ocultar este botón en estas URLs</label>',
            '<textarea id="fbpro-field-hide-on" rows="6" placeholder="/contacto/&#10;/aviso-legal/&#10;posttype:ciudades">' + this.escHtml(btn.hide_on || '') + '</textarea>',
            '<div class="fbpro-help-block">',
              'Una regla por línea:<br>',
              '<code>/contacto/</code> → oculta en esa URL &nbsp;·&nbsp; <code>posttype:ciudades</code> → oculta en ese CPT',
            '</div>',
          '</div>',
        '</div>',
      ].join('');
    },

    collectModalData: function () {
      return {
        id:             this.editingId || '',
        label:          $('#fbpro-field-label').val(),
        action_type:    $('input[name="fbpro_action_type"]:checked').val() || 'link',
        url:            $('#fbpro-field-url').val(),
        target:         $('#fbpro-field-target').val() || '_blank',
        tooltip:        $('#fbpro-field-tooltip').val(),
        icon_type:      $('input[name="fbpro_icon_type"]:checked').val() || 'svg',
        icon_svg:       $('input[name="fbpro_icon_svg"]:checked').val() || 'phone',
        icon_image_id:  parseInt($('#fbpro-field-icon-image-id').val() || 0, 10),
        icon_size:      parseInt($('#fbpro-field-icon-size').val() || 46, 10),
        bg_color:       $('#fbpro-field-bg-color').val() || '#2A90A0',
        icon_color:     $('#fbpro-field-icon-color').val() || '#ffffff',
        size:           parseInt($('#fbpro-field-size').val() || 56, 10),
        radius:         parseInt($('#fbpro-field-radius').val() || 16, 10),
        shadow:         parseInt($('#fbpro-field-shadow').val() || 2, 10),
        hover_effect:   $('input[name="fbpro_hover"]:checked').val() || 'scale',
        popup_mode:     $('#fbpro-field-popup-mode').val() || 'shortcode',
        popup_content:  $('#fbpro-field-popup-content').val(),
        popup_pages:    $('#fbpro-field-popup-pages').val(),
        hide_mobile:    $('#fbpro-field-hide-mobile').prop('checked'),
        hide_desktop:   $('#fbpro-field-hide-desktop').prop('checked'),
        hide_on:        $('#fbpro-field-hide-on').val(),
        active:         true,
      };
    },

    /* ══════════════════════════════════════════════════════════
       EVENTOS
       ══════════════════════════════════════════════════════════ */
    bindEvents: function () {
      var self = this;

      /* ── Tabs principales ─────────────────────────────────── */
      $('.fbpro-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.fbpro-tab').removeClass('active');
        $(this).addClass('active');
        $('.fbpro-tab-panel').removeClass('active');
        $('#tab-' + tab).addClass('active');
      });

      /* ── Añadir botón ─────────────────────────────────────── */
      $('#fbpro-add-btn').on('click', function () {
        self.openModal(null);
      });

      /* ── Modal: cerrar ────────────────────────────────────── */
      $(document).on('click', '#fbpro-modal-close, #fbpro-modal-cancel', function () {
        self.closeModal();
      });
      $(document).on('click', '#fbpro-modal', function (e) {
        if ($(e.target).is('#fbpro-modal')) self.closeModal();
      });
      $(document).on('keydown', function (e) {
        if (e.key === 'Escape') self.closeModal();
      });

      /* ── Modal: sub-tabs ──────────────────────────────────── */
      $(document).on('click', '.fbpro-mtab', function () {
        self.switchMTab($(this).data('mtab'));
      });

      /* ── Modal: cambio acción ─────────────────────────────── */
      $(document).on('change', 'input[name="fbpro_action_type"]', function () {
        self.updateActionFields();
        self.updatePopupPanel();
      });

      /* ── Modal: cambio tipo icono ─────────────────────────── */
      $(document).on('change', 'input[name="fbpro_icon_type"]', function () {
        self.updateIconFields();
      });

      /* ── Modal: selección SVG ─────────────────────────────── */
      $(document).on('change', 'input[name="fbpro_icon_svg"]', function () {
        $('.fbpro-svg-item').removeClass('is-selected');
        $(this).closest('.fbpro-svg-item').addClass('is-selected');
      });

      /* ── Modal: media picker ──────────────────────────────── */
      $(document).on('click', '#fbpro-select-image-btn', function () {
        if (typeof wp === 'undefined' || !wp.media) return;
        var frame = wp.media({
          title: 'Seleccionar imagen para el botón',
          button: { text: 'Usar esta imagen' },
          multiple: false,
        });
        frame.on('select', function () {
          var att = frame.state().get('selection').first().toJSON();
          $('#fbpro-field-icon-image-id').val(att.id);
          var $wrap = $('#fbpro-image-preview-wrap');
          $wrap.find('img').remove();
          $wrap.append('<img id="fbpro-icon-image-preview" src="' + att.url + '" style="max-width:80px;max-height:80px;border-radius:8px;border:2px solid #e5e7eb;margin-top:8px;">');
        });
        frame.open();
      });

      /* ── Modal: guardar ───────────────────────────────────── */
      $(document).on('click', '#fbpro-modal-save', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Guardando…');
        $('#fbpro-modal-status').text('').removeClass('fbpro-status--ok fbpro-status--err');

        var data = self.collectModalData();

        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_save_button',
          nonce:  fbproData.nonce,
          button: JSON.stringify(data),
        })
        .done(function (r) {
          if (r.success) {
            self.upsertButton(r.data.button);
            self.renderList();
            self.closeModal();
          } else {
            $('#fbpro-modal-status').text('Error: ' + (r.data && r.data.message ? r.data.message : 'desconocido')).addClass('fbpro-status--err');
          }
        })
        .fail(function () {
          $('#fbpro-modal-status').text('Error de red.').addClass('fbpro-status--err');
        })
        .always(function () {
          $btn.prop('disabled', false).text('Guardar botón');
        });
      });

      /* ── Card: editar ─────────────────────────────────────── */
      $(document).on('click', '.fbpro-card-edit', function () {
        var btn = self.getButton($(this).data('id'));
        if (btn) self.openModal(btn);
      });

      /* ── Card: duplicar ───────────────────────────────────── */
      $(document).on('click', '.fbpro-card-dupe', function () {
        var id   = $(this).data('id');
        var $btn = $(this).prop('disabled', true);
        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_duplicate_button',
          nonce:  fbproData.nonce,
          id:     id,
        })
        .done(function (r) {
          if (r.success) {
            self.buttons.push(r.data.button);
            self.renderList();
          }
        })
        .always(function () { $btn.prop('disabled', false); });
      });

      /* ── Card: eliminar ───────────────────────────────────── */
      $(document).on('click', '.fbpro-card-del', function () {
        var id  = $(this).data('id');
        var btn = self.getButton(id);
        var lbl = btn ? btn.label : 'este botón';
        if (!confirm('¿Eliminar "' + lbl + '"? Esta acción no se puede deshacer.')) return;

        var $card = $(this).closest('.fbpro-btn-card');
        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_delete_button',
          nonce:  fbproData.nonce,
          id:     id,
        })
        .done(function (r) {
          if (r.success) {
            self.buttons = self.buttons.filter(function (b) { return b.id !== id; });
            $card.fadeOut(200, function () { self.renderList(); });
          }
        });
      });

      /* ── Card: toggle activo ──────────────────────────────── */
      $(document).on('change', '.fbpro-toggle-active', function () {
        var id     = $(this).data('id');
        var active = $(this).prop('checked');
        var btn    = self.getButton(id);
        if (btn) btn.active = active;

        var $card = $(this).closest('.fbpro-btn-card');
        $card.toggleClass('fbpro-btn-card--inactive', !active);

        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_toggle_button',
          nonce:  fbproData.nonce,
          id:     id,
          active: active ? 1 : 0,
        });
      });

      /* ── Setup banner ─────────────────────────────────────── */
      $('#fbpro-setup-defaults, #fbpro-setup-empty').on('click', function () {
        var choice = $(this).is('#fbpro-setup-defaults') ? 'defaults' : 'empty';
        var $banner = $('#fbpro-setup-banner');
        $banner.find('button').prop('disabled', true);

        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_setup',
          nonce:  fbproData.nonce,
          choice: choice,
        })
        .done(function (r) {
          if (r.success) {
            self.buttons = r.data.buttons || [];
            self.renderList();
            $banner.slideUp(300);
            fbproData.setupPending = false;
          }
        })
        .always(function () { $banner.find('button').prop('disabled', false); });
      });

      /* ── Guardar ajustes globales ─────────────────────────── */
      $('#fbpro-save-global').on('click', function () {
        var $btn    = $(this).prop('disabled', true).text('Guardando…');
        var $status = $('#fbpro-global-status').text('').removeClass('fbpro-status--ok fbpro-status--err');

        var global = {
          position_corner: $('#fbpro-global-corner').val(),
          offset_x:        parseInt($('#fbpro-global-offset-x').val() || 22, 10),
          offset_y:        parseInt($('#fbpro-global-offset-y').val() || 24, 10),
          entrance_anim:   $('#fbpro-global-entrance').prop('checked'),
          pulse_ring:      $('#fbpro-global-pulse').prop('checked'),
        };

        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_save_global',
          nonce:  fbproData.nonce,
          global: JSON.stringify(global),
        })
        .done(function (r) {
          if (r.success) {
            $status.text('✓ Guardado').addClass('fbpro-status--ok');
            setTimeout(function () { $status.text(''); }, 2500);
          } else {
            $status.text('Error al guardar').addClass('fbpro-status--err');
          }
        })
        .fail(function () { $status.text('Error de red').addClass('fbpro-status--err'); })
        .always(function () { $btn.prop('disabled', false).text('Guardar ajustes globales'); });
      });
    },

    /* ══════════════════════════════════════════════════════════
       HELPERS DE DATOS
       ══════════════════════════════════════════════════════════ */
    getButton: function (id) {
      return this.buttons.find(function (b) { return b.id === id; }) || null;
    },

    upsertButton: function (btn) {
      var idx = this.buttons.findIndex(function (b) { return b.id === btn.id; });
      if (idx >= 0) {
        this.buttons[idx] = btn;
      } else {
        this.buttons.push(btn);
      }
    },

    /* ══════════════════════════════════════════════════════════
       HELPERS DE TEXTO
       ══════════════════════════════════════════════════════════ */
    escHtml: function (str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    },
    escAttr: function (str) { return this.escHtml(str); },
    truncate: function (str, len) {
      return str.length > len ? str.slice(0, len) + '…' : str;
    },
  };

  $(document).ready(function () {
    FBPro.init();
  });

})(jQuery);
