/* Floating Buttons Pro – Admin JS v2.0 */
(function ($) {
  'use strict';

  var FBPro = {

    buttons: [],
    editingId: null,
    activeMTab: 'contenido',
    cmEditor: null,

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
      var bg;
      if (btn.bg_type === 'gradient') {
        bg = 'linear-gradient(' + (btn.gradient_angle || 135) + 'deg, ' + (btn.gradient_from || '#2A90A0') + ', ' + (btn.gradient_to || '#1a6e7e') + ')';
      } else {
        bg = btn.bg_color || '#2A90A0';
      }
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
      this.cmEditor = null;
      $('#fbpro-modal').attr('aria-hidden', 'true').removeClass('is-open');
      $('body').removeClass('fbpro-modal-open');
      this.editingId = null;
    },

    switchMTab: function (tab) {
      this.activeMTab = tab;
      $('.fbpro-mtab').removeClass('active').filter('[data-mtab="' + tab + '"]').addClass('active');
      $('.fbpro-mpanel').hide();
      $('#fbpro-mp-' + tab).show();

      // Inicializar CodeMirror al mostrar el panel popup (solo una vez por apertura de modal)
      if (tab === 'popup' && !this.cmEditor) {
        var $ta = $('#fbpro-field-popup-css');
        if ($ta.length && window.wp && wp.codeEditor && fbproData.cmSettings) {
          this.cmEditor = wp.codeEditor.initialize($ta[0], fbproData.cmSettings);
        }
      }
    },

    renderModalForm: function (btn) {
      this.cmEditor = null; // DOM will be replaced; CM instance is abandoned
      $('#fbpro-modal-body').html(this.modalFormHtml(btn));
      this.updateActionFields();
      this.updateIconFields();
      this.updateModalForActionType();
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

    updateModalForActionType: function () {
      var isPopup = $('input[name="fbpro_action_type"]:checked').val() === 'popup';
      $('[data-mtab="visibilidad"]').toggle(!isPopup);
      var $dv = $('#fbpro-device-visibility');
      if (isPopup) {
        $('#fbpro-popup-device-slot').append($dv);
      } else {
        $('#fbpro-mp-visibilidad').prepend($dv);
      }
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

      var isPopup    = btn.action_type === 'popup';
      var isImage    = btn.icon_type === 'image';
      var isGradient = btn.bg_type === 'gradient';
      var predefAngles  = [180, 90, 135, 225, 45, 315];
      var angle         = btn.gradient_angle !== undefined ? btn.gradient_angle : 135;
      var isCustomAngle = predefAngles.indexOf(angle) === -1;
      var angleLabels   = {
        180: 'Vertical (180°) — arriba a abajo',
        90:  'Horizontal (90°) — izquierda a derecha',
        135: 'Diagonal ↘ (135°)',
        225: 'Diagonal ↙ (225°)',
        45:  'Diagonal ↗ (45°)',
        315: 'Diagonal ↖ (315°)'
      };
      var angleOptions = predefAngles.map(function (a) {
        return '<option value="' + a + '"' + (!isCustomAngle && angle === a ? ' selected' : '') + '>' + angleLabels[a] + '</option>';
      }).join('') + '<option value="custom"' + (isCustomAngle ? ' selected' : '') + '>Personalizado</option>';

      var bDef = (window.fbproData && fbproData.defaults && fbproData.defaults.bubble) ? fbproData.defaults.bubble : {};
      var b    = $.extend({}, bDef, btn.bubble || {});
      var bShadow = (b.shadow !== undefined && b.shadow !== null) ? String(b.shadow) : '2';

      var activeDays  = btn.schedule_days && btn.schedule_days.length ? btn.schedule_days : [1,2,3,4,5,6,7];
      var dayLabels   = ['L','M','X','J','V','S','D'];
      var daysHtml    = dayLabels.map(function (lbl, i) {
        var val     = i + 1;
        var checked = activeDays.indexOf(val) !== -1;
        return '<label class="fbpro-day">' +
          '<input type="checkbox" class="fbpro-day-check" value="' + val + '"' + (checked ? ' checked' : '') + '>' +
          '<span>' + lbl + '</span>' +
        '</label>';
      }).join('');

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
          '<div class="fbpro-field">',
            '<label>Clase CSS <small>(opcional)</small></label>',
            '<input type="text" id="fbpro-field-custom-class" value="' + this.escAttr(btn.custom_class || '') + '" placeholder="mi-clase otra-clase">',
            '<p class="fbpro-help">Una o varias clases separadas por espacios. Útil para estilos personalizados o eventos de tracking.</p>',
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
            '<div class="fbpro-field" style="margin-top:10px">',
              '<label>Ajuste de la imagen</label>',
              '<select id="fbpro-field-image-fit">',
                '<option value="cover"'   + ((btn.image_fit || 'cover') === 'cover'   ? ' selected' : '') + '>Llenar (cover) — rellena sin deformar</option>',
                '<option value="contain"' + ((btn.image_fit || 'cover') === 'contain' ? ' selected' : '') + '>Ajustar (contain) — muestra la imagen completa</option>',
                '<option value="fill"'    + ((btn.image_fit || 'cover') === 'fill'    ? ' selected' : '') + '>Estirar (fill) — estira para llenar el botón</option>',
              '</select>',
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
          '<div class="fbpro-field">',
            '<label>Fondo</label>',
            '<label class="fbpro-toggle" style="margin-bottom:8px">',
              '<input type="checkbox" id="fbpro-field-bg-gradient"' + (isGradient ? ' checked' : '') + '>',
              '<span>Usar degradado</span>',
            '</label>',
            '<div id="fbpro-bg-solid"' + (isGradient ? ' style="display:none"' : '') + '>',
              '<div class="fbpro-color-row">',
                '<input type="color" id="fbpro-field-bg-color" value="' + (btn.bg_color || '#2A90A0') + '">',
                '<input type="text" id="fbpro-field-bg-color-hex" class="fbpro-hex-input" value="' + (btn.bg_color || '#2A90A0').toUpperCase() + '" maxlength="7" placeholder="#2A90A0">',
              '</div>',
            '</div>',
            '<div id="fbpro-bg-gradient-fields"' + (!isGradient ? ' style="display:none"' : '') + '>',
              '<div class="fbpro-gradient-colors">',
                '<div class="fbpro-field">',
                  '<label style="font-weight:500;font-size:12px">Color inicial</label>',
                  '<div class="fbpro-color-row">',
                    '<input type="color" id="fbpro-field-gradient-from" value="' + (btn.gradient_from || '#2A90A0') + '">',
                    '<input type="text" id="fbpro-field-gradient-from-hex" class="fbpro-hex-input" value="' + (btn.gradient_from || '#2A90A0').toUpperCase() + '" maxlength="7">',
                  '</div>',
                '</div>',
                '<div class="fbpro-field">',
                  '<label style="font-weight:500;font-size:12px">Color final</label>',
                  '<div class="fbpro-color-row">',
                    '<input type="color" id="fbpro-field-gradient-to" value="' + (btn.gradient_to || '#1a6e7e') + '">',
                    '<input type="text" id="fbpro-field-gradient-to-hex" class="fbpro-hex-input" value="' + (btn.gradient_to || '#1a6e7e').toUpperCase() + '" maxlength="7">',
                  '</div>',
                '</div>',
              '</div>',
              '<div class="fbpro-field">',
                '<label style="font-weight:500;font-size:12px">Ángulo</label>',
                '<select id="fbpro-field-gradient-angle-preset">' + angleOptions + '</select>',
                '<div id="fbpro-gradient-angle-custom" class="fbpro-gradient-angle-row"' + (!isCustomAngle ? ' style="display:none"' : '') + '>',
                  '<input type="number" id="fbpro-field-gradient-angle" min="0" max="360" value="' + angle + '" style="width:72px">',
                  '<span>°</span>',
                '</div>',
              '</div>',
            '</div>',
          '</div>',
          '<div class="fbpro-grid-2" style="margin-top:4px">',
            '<div class="fbpro-field">',
              '<label>Color del icono</label>',
              '<div class="fbpro-color-row">',
                '<input type="color" id="fbpro-field-icon-color" value="' + (btn.icon_color || '#ffffff') + '">',
                '<input type="text" id="fbpro-field-icon-color-hex" class="fbpro-hex-input" value="' + (btn.icon_color || '#ffffff').toUpperCase() + '" maxlength="7">',
              '</div>',
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
              '<label>Mostrar botón solo en estas páginas <small>(vacío = todas)</small></label>',
              '<textarea id="fbpro-field-popup-pages" rows="5" placeholder="/landing/&#10;posttype:ciudades">' + this.escHtml(btn.popup_pages || '') + '</textarea>',
              '<p class="fbpro-help">Una regla por línea. Vacío = aparece en toda la web.</p>',
            '</div>',
            '<div class="fbpro-field">',
              '<label>CSS personalizado <small>(scoped a este popup)</small></label>',
              '<textarea id="fbpro-field-popup-css" rows="8" class="fbpro-code-editor" placeholder=".mi-formulario { color: red; }&#10;input { border-radius: 6px; }">' + this.escHtml(btn.popup_css || '') + '</textarea>',
              '<p class="fbpro-help">Se aplica únicamente dentro de este popup. No afecta al resto de la web.</p>',
            '</div>',
            '<div id="fbpro-popup-device-slot"></div>',
          '</div>',
        '</div>',

        /* ── Bocadillo ──────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-bocadillo" style="display:none">',
          '<div class="fbpro-field">',
            '<label class="fbpro-toggle">',
              '<input type="checkbox" id="fbpro-field-bubble-enabled"' + (b.enabled ? ' checked' : '') + '>',
              '<span>Activar bocadillo junto al botón</span>',
            '</label>',
            '<p class="fbpro-help">Muestra un mensaje flotante junto al botón para captar la atención del usuario.</p>',
          '</div>',
          '<div class="fbpro-bubble-config"' + (!b.enabled ? ' style="display:none"' : '') + '>',
            '<p style="font-weight:600;margin:0 0 10px;font-size:13px;color:#374151">Contenido</p>',
            '<div class="fbpro-field">',
              '<label>Título <small>(opcional)</small></label>',
              '<input type="text" name="bubble_title" value="' + self.escAttr(b.title || '') + '" placeholder="¡Oferta especial!">',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Mensaje</label>',
              '<textarea name="bubble_message" rows="3" placeholder="¡Hola! ¿Necesitas ayuda?">' + self.escHtml(b.message || '') + '</textarea>',
              '<p class="fbpro-help">Permite <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;br&gt;</code>.</p>',
            '</div>',
            '<p style="font-weight:600;margin:16px 0 10px;font-size:13px;color:#374151">Comportamiento</p>',
            '<div class="fbpro-field">',
              '<label>Aparece tras</label>',
              '<div style="display:flex;align-items:center;gap:8px">',
                '<input type="number" name="bubble_delay" min="0" max="60" value="' + (b.delay !== undefined ? b.delay : 3) + '" style="width:64px">',
                '<span style="font-size:13px;color:#6b7280">segundos</span>',
              '</div>',
            '</div>',
            '<div class="fbpro-field">',
              '<label style="font-weight:600;font-size:13px;margin-bottom:10px;display:block">Cómo se cierra</label>',
              '<label class="fbpro-toggle" style="margin-bottom:8px">',
                '<input type="checkbox" name="bubble_closable"' + (b.closable !== false ? ' checked' : '') + '>',
                '<span>Con botón ✕</span>',
              '</label>',
              '<div style="margin-bottom:8px">',
                '<label class="fbpro-toggle">',
                  '<input type="checkbox" class="fbpro-bubble-autoclose-toggle"' + (b.auto_close > 0 ? ' checked' : '') + '>',
                  '<span>Autocerrar</span>',
                '</label>',
                '<div class="fbpro-bubble-autoclose-seconds"' + (!(b.auto_close > 0) ? ' style="display:none"' : '') + '>',
                  '<div style="display:flex;align-items:center;gap:8px;margin-top:6px;padding-left:8px">',
                    '<span style="font-size:13px;color:#6b7280">tras</span>',
                    '<input type="number" name="bubble_auto_close" min="1" max="120" value="' + (b.auto_close > 0 ? b.auto_close : 10) + '" style="width:64px">',
                    '<span style="font-size:13px;color:#6b7280">segundos</span>',
                  '</div>',
                '</div>',
              '</div>',
              '<label class="fbpro-toggle" style="margin-bottom:8px">',
                '<input type="checkbox" name="bubble_close_on_outside"' + (b.close_on_outside ? ' checked' : '') + '>',
                '<span>Al hacer clic fuera</span>',
              '</label>',
              '<label class="fbpro-toggle">',
                '<input type="checkbox" name="bubble_remember_close"' + (b.remember_close !== false ? ' checked' : '') + '>',
                '<span>Recordar cierre <small>(no volver a mostrar esta sesión)</small></span>',
              '</label>',
            '</div>',
            '<p style="font-weight:600;margin:16px 0 10px;font-size:13px;color:#374151">Posición y aspecto</p>',
            '<div class="fbpro-field">',
              '<label>Posición respecto al botón</label>',
              '<div class="fbpro-radio-group">',
                radio('bubble_position', 'left',   (b.position || 'left') === 'left',   'Izquierda'),
                radio('bubble_position', 'right',  (b.position || 'left') === 'right',  'Derecha'),
                radio('bubble_position', 'top',    (b.position || 'left') === 'top',    'Arriba'),
                radio('bubble_position', 'bottom', (b.position || 'left') === 'bottom', 'Abajo'),
              '</div>',
            '</div>',
            '<div class="fbpro-field">',
              '<label class="fbpro-toggle">',
                '<input type="checkbox" name="bubble_show_arrow"' + (b.show_arrow !== false ? ' checked' : '') + '>',
                '<span>Mostrar flechita apuntando al botón</span>',
              '</label>',
            '</div>',
            '<div class="fbpro-field">',
              '<label>Animación de entrada</label>',
              '<select name="bubble_animation">',
                '<option value="fade"'   + ((b.animation || 'fade') === 'fade'   ? ' selected' : '') + '>Fade (desvanecimiento)</option>',
                '<option value="slide"'  + ((b.animation || 'fade') === 'slide'  ? ' selected' : '') + '>Slide (deslizamiento)</option>',
                '<option value="bounce"' + ((b.animation || 'fade') === 'bounce' ? ' selected' : '') + '>Bounce (rebote)</option>',
                '<option value="none"'   + ((b.animation || 'fade') === 'none'   ? ' selected' : '') + '>Sin animación</option>',
              '</select>',
            '</div>',
            '<p style="font-weight:600;margin:16px 0 10px;font-size:13px;color:#374151">Estilo</p>',
            '<div class="fbpro-grid-2">',
              '<div class="fbpro-field">',
                '<label>Color de fondo</label>',
                '<div class="fbpro-color-row">',
                  '<input type="color" id="fbpro-field-bubble-bg-color" value="' + (b.bg_color || '#ffffff') + '">',
                  '<input type="text" class="fbpro-hex-input" value="' + (b.bg_color || '#ffffff').toUpperCase() + '" maxlength="7">',
                '</div>',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Color del título</label>',
                '<div class="fbpro-color-row">',
                  '<input type="color" id="fbpro-field-bubble-title-color" value="' + (b.title_color || '#1a1a2e') + '">',
                  '<input type="text" class="fbpro-hex-input" value="' + (b.title_color || '#1a1a2e').toUpperCase() + '" maxlength="7">',
                '</div>',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Color del texto</label>',
                '<div class="fbpro-color-row">',
                  '<input type="color" id="fbpro-field-bubble-text-color" value="' + (b.text_color || '#4b5563') + '">',
                  '<input type="text" class="fbpro-hex-input" value="' + (b.text_color || '#4b5563').toUpperCase() + '" maxlength="7">',
                '</div>',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Color del borde</label>',
                '<div class="fbpro-color-row">',
                  '<input type="color" id="fbpro-field-bubble-border-color" value="' + (b.border_color || '#e5e7eb') + '">',
                  '<input type="text" class="fbpro-hex-input" value="' + (b.border_color || '#e5e7eb').toUpperCase() + '" maxlength="7">',
                '</div>',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Grosor del borde (px)</label>',
                '<input type="number" name="bubble_border_width" min="0" max="10" value="' + (b.border_width !== undefined ? b.border_width : 1) + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Border radius (px)</label>',
                '<input type="number" name="bubble_border_radius" min="0" max="50" value="' + (b.border_radius !== undefined ? b.border_radius : 12) + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Tamaño título (px)</label>',
                '<input type="number" name="bubble_title_size" min="10" max="32" value="' + (b.title_size !== undefined ? b.title_size : 15) + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Tamaño texto (px)</label>',
                '<input type="number" name="bubble_text_size" min="10" max="24" value="' + (b.text_size !== undefined ? b.text_size : 13) + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Padding (px)</label>',
                '<input type="number" name="bubble_padding" min="4" max="40" value="' + (b.padding !== undefined ? b.padding : 14) + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Ancho máximo (px)</label>',
                '<input type="number" name="bubble_max_width" min="100" max="500" value="' + (b.max_width !== undefined ? b.max_width : 240) + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Sombra</label>',
                '<select name="bubble_shadow">',
                  '<option value="0"' + (bShadow === '0' ? ' selected' : '') + '>Sin sombra</option>',
                  '<option value="1"' + (bShadow === '1' ? ' selected' : '') + '>Suave</option>',
                  '<option value="2"' + (bShadow === '2' ? ' selected' : '') + '>Media</option>',
                  '<option value="3"' + (bShadow === '3' ? ' selected' : '') + '>Intensa</option>',
                '</select>',
              '</div>',
            '</div>',
          '</div>',
        '</div>',

        /* ── Visibilidad ────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-visibilidad" style="display:none">',
          '<div id="fbpro-device-visibility">',
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

        /* ── Horario ─────────────────────────────────────────── */
        '<div class="fbpro-mpanel" id="fbpro-mp-horario" style="display:none">',
          '<div class="fbpro-field">',
            '<label class="fbpro-toggle">',
              '<input type="checkbox" id="fbpro-field-schedule-enabled"' + (btn.schedule_enabled ? ' checked' : '') + '>',
              '<span>Activar programación horaria</span>',
            '</label>',
            '<p class="fbpro-help">Si está desactivado, el botón se muestra siempre.</p>',
          '</div>',
          '<div class="fbpro-schedule-config"' + (!btn.schedule_enabled ? ' style="display:none"' : '') + '>',
            '<div class="fbpro-field">',
              '<label>Días activos</label>',
              '<div class="fbpro-days-group">',
                daysHtml,
              '</div>',
            '</div>',
            '<div class="fbpro-grid-2" style="margin-top:12px">',
              '<div class="fbpro-field">',
                '<label>Hora de inicio</label>',
                '<input type="time" id="fbpro-field-schedule-from" value="' + (btn.schedule_from || '09:00') + '">',
              '</div>',
              '<div class="fbpro-field">',
                '<label>Hora de fin</label>',
                '<input type="time" id="fbpro-field-schedule-to" value="' + (btn.schedule_to || '20:00') + '">',
              '</div>',
            '</div>',
            '<div class="fbpro-info-box" style="margin-top:14px">',
              '<strong>Zona horaria:</strong> se usa la de WordPress',
              ' (<code>' + self.escHtml((window.fbproData && fbproData.timezone) ? fbproData.timezone : 'UTC') + '</code>).',
              ' Cámbiala en Ajustes → General.',
            '</div>',
          '</div>',
        '</div>',
      ].join('');
    },

    collectModalData: function () {
      var existing = this.editingId ? this.getButton(this.editingId) : null;
      return {
        id:             this.editingId || '',
        order:          existing ? (existing.order || 0) : 0,
        label:          $('#fbpro-field-label').val(),
        action_type:    $('input[name="fbpro_action_type"]:checked').val() || 'link',
        url:            $('#fbpro-field-url').val(),
        target:         $('#fbpro-field-target').val() || '_blank',
        tooltip:        $('#fbpro-field-tooltip').val(),
        custom_class:   $('#fbpro-field-custom-class').val(),
        icon_type:      $('input[name="fbpro_icon_type"]:checked').val() || 'svg',
        icon_svg:       $('input[name="fbpro_icon_svg"]:checked').val() || 'phone',
        icon_image_id:  parseInt($('#fbpro-field-icon-image-id').val() || 0, 10),
        icon_size:      parseInt($('#fbpro-field-icon-size').val() || 46, 10),
        image_fit:      $('#fbpro-field-image-fit').val() || 'cover',
        bg_type:        $('#fbpro-field-bg-gradient').prop('checked') ? 'gradient' : 'solid',
        bg_color:       $('#fbpro-field-bg-color').val() || '#2A90A0',
        gradient_from:  $('#fbpro-field-gradient-from').val() || '#2A90A0',
        gradient_to:    $('#fbpro-field-gradient-to').val() || '#1a6e7e',
        gradient_angle: (function () {
          var p = $('#fbpro-field-gradient-angle-preset').val();
          return p === 'custom' ? parseInt($('#fbpro-field-gradient-angle').val() || 135, 10) : parseInt(p || 135, 10);
        }()),
        icon_color:     $('#fbpro-field-icon-color').val() || '#ffffff',
        size:           parseInt($('#fbpro-field-size').val() || 56, 10),
        radius:         parseInt($('#fbpro-field-radius').val() || 16, 10),
        shadow:         parseInt($('#fbpro-field-shadow').val() || 2, 10),
        hover_effect:   $('input[name="fbpro_hover"]:checked').val() || 'scale',
        popup_mode:     $('#fbpro-field-popup-mode').val() || 'shortcode',
        popup_content:  $('#fbpro-field-popup-content').val(),
        popup_css:      this.cmEditor ? this.cmEditor.codemirror.getValue() : ($('#fbpro-field-popup-css').val() || ''),
        popup_pages:    $('#fbpro-field-popup-pages').val(),
        hide_mobile:    $('#fbpro-field-hide-mobile').prop('checked'),
        hide_desktop:   $('#fbpro-field-hide-desktop').prop('checked'),
        hide_on:        $('#fbpro-field-hide-on').val(),
        schedule_enabled: $('#fbpro-field-schedule-enabled').prop('checked'),
        schedule_days:    (function () {
          var days = [];
          $('#fbpro-mp-horario .fbpro-day-check:checked').each(function () {
            days.push(parseInt($(this).val(), 10));
          });
          return days;
        }()),
        schedule_from:    $('#fbpro-field-schedule-from').val() || '09:00',
        schedule_to:      $('#fbpro-field-schedule-to').val() || '20:00',
        active:           true,
        bubble: {
          enabled:          $('#fbpro-field-bubble-enabled').prop('checked'),
          title:            $('[name="bubble_title"]').val(),
          message:          $('[name="bubble_message"]').val(),
          delay:            parseInt($('[name="bubble_delay"]').val() || 3, 10),
          auto_close:       $('.fbpro-bubble-autoclose-toggle').prop('checked')
                              ? parseInt($('[name="bubble_auto_close"]').val() || 10, 10)
                              : 0,
          closable:         $('[name="bubble_closable"]').prop('checked'),
          close_on_outside: $('[name="bubble_close_on_outside"]').prop('checked'),
          remember_close:   $('[name="bubble_remember_close"]').prop('checked'),
          position:         $('[name="bubble_position"]:checked').val() || 'left',
          show_arrow:       $('[name="bubble_show_arrow"]').prop('checked'),
          animation:        $('[name="bubble_animation"]').val() || 'fade',
          bg_color:         $('#fbpro-field-bubble-bg-color').val() || '#ffffff',
          title_color:      $('#fbpro-field-bubble-title-color').val() || '#1a1a2e',
          text_color:       $('#fbpro-field-bubble-text-color').val() || '#4b5563',
          border_color:     $('#fbpro-field-bubble-border-color').val() || '#e5e7eb',
          border_width:     parseInt($('[name="bubble_border_width"]').val() || 1, 10),
          border_radius:    parseInt($('[name="bubble_border_radius"]').val() || 12, 10),
          title_size:       parseInt($('[name="bubble_title_size"]').val() || 15, 10),
          text_size:        parseInt($('[name="bubble_text_size"]').val() || 13, 10),
          padding:          parseInt($('[name="bubble_padding"]').val() || 14, 10),
          max_width:        parseInt($('[name="bubble_max_width"]').val() || 240, 10),
          shadow:           $('[name="bubble_shadow"]').val() || '2',
        },
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

      /* ── Modal: sub-tabs ──────────────────────────────────── */
      $(document).on('click', '.fbpro-mtab', function () {
        self.switchMTab($(this).data('mtab'));
      });

      /* ── Modal: cambio acción ─────────────────────────────── */
      $(document).on('change', 'input[name="fbpro_action_type"]', function () {
        self.updateActionFields();
        self.updatePopupPanel();
        self.updateModalForActionType();
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
          title: 'Selecciona una imagen o GIF',
          button: { text: 'Usar esta imagen' },
          library: { type: ['image'] },
          multiple: false,
        });
        frame.on('select', function () {
          var att = frame.state().get('selection').first().toJSON();
          $('#fbpro-field-icon-image-id').val(att.id).trigger('change');
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

      /* ── Exportar configuración ──────────────────────────────── */
      $('#fbpro-export-btn').on('click', function () {
        var $btn    = $(this).prop('disabled', true).text('Exportando…');
        var $status = $('#fbpro-export-status').text('').removeClass('fbpro-status--ok fbpro-status--err');

        $.post(fbproData.ajaxUrl, {
          action: 'fbpro_export_config',
          nonce:  fbproData.nonce,
        })
        .done(function (r) {
          if (!r.success) {
            $status.text('Error al exportar').addClass('fbpro-status--err');
            return;
          }
          var blob = new Blob([JSON.stringify(r.data, null, 2)], { type: 'application/json' });
          var url  = URL.createObjectURL(blob);
          var a    = document.createElement('a');
          var date = new Date().toISOString().split('T')[0];
          a.href     = url;
          a.download = 'fbpro-config-' + date + '.json';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
          $status.text('✓ Descargado').addClass('fbpro-status--ok');
          setTimeout(function () { $status.text(''); }, 3000);
        })
        .fail(function () { $status.text('Error de red').addClass('fbpro-status--err'); })
        .always(function () { $btn.prop('disabled', false).text('Exportar configuración'); });
      });

      /* ── Importar configuración ───────────────────────────────── */
      $('#fbpro-import-btn').on('click', function () {
        var file = $('#fbpro-import-file')[0].files[0];
        var $status = $('#fbpro-import-status').text('').removeClass('fbpro-status--ok fbpro-status--err');

        if (!file) {
          $status.text('Selecciona un archivo JSON primero.').addClass('fbpro-status--err');
          return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
          var content = e.target.result;
          var parsed;

          try {
            parsed = JSON.parse(content);
          } catch (err) {
            $status.text('El archivo no es un JSON válido.').addClass('fbpro-status--err');
            return;
          }

          if (!parsed || parsed.plugin !== 'floating-buttons-pro') {
            $status.text('Este archivo no pertenece a Floating Buttons Pro.').addClass('fbpro-status--err');
            return;
          }

          var count = (parsed.buttons || []).length;
          if (!confirm('Se importarán ' + count + ' botones. La configuración actual se sobrescribirá completamente. ¿Continuar?')) {
            return;
          }

          var $btn = $('#fbpro-import-btn').prop('disabled', true).text('Importando…');

          $.post(fbproData.ajaxUrl, {
            action: 'fbpro_import_config',
            nonce:  fbproData.nonce,
            config: content,
          })
          .done(function (r) {
            if (r.success) {
              $status.text('✓ ' + r.data.message + ' (' + r.data.buttons_count + ' botones). Recargando…').addClass('fbpro-status--ok');
              setTimeout(function () { location.reload(); }, 1500);
            } else {
              $status.text('Error: ' + (r.data && r.data.message ? r.data.message : 'desconocido')).addClass('fbpro-status--err');
              $btn.prop('disabled', false).text('Importar');
            }
          })
          .fail(function () {
            $status.text('Error de red.').addClass('fbpro-status--err');
            $btn.prop('disabled', false).text('Importar');
          });
        };
        reader.readAsText(file);
      });

      /* ── Modal: bubble enabled toggle ───────────────────────────── */
      $(document).on('change', '#fbpro-field-bubble-enabled', function () {
        $(this).closest('.fbpro-mpanel').find('.fbpro-bubble-config').toggle(this.checked);
      });

      /* ── Modal: bubble autoclose seconds toggle ──────────────────── */
      $(document).on('change', '.fbpro-bubble-autoclose-toggle', function () {
        $(this).closest('div').find('.fbpro-bubble-autoclose-seconds').toggle(this.checked);
      });

      /* ── Modal: schedule toggle ──────────────────────────────── */
      $(document).on('change', '#fbpro-field-schedule-enabled', function () {
        $(this).closest('.fbpro-mpanel').find('.fbpro-schedule-config').toggle(this.checked);
      });

      /* ── Modal: gradient toggle ───────────────────────────────── */
      $(document).on('change', '#fbpro-field-bg-gradient', function () {
        var isGrad = $(this).prop('checked');
        $('#fbpro-bg-solid').toggle(!isGrad);
        $('#fbpro-bg-gradient-fields').toggle(isGrad);
      });

      /* ── Modal: gradient angle preset ────────────────────────── */
      $(document).on('change', '#fbpro-field-gradient-angle-preset', function () {
        $('#fbpro-gradient-angle-custom').toggle($(this).val() === 'custom');
      });

      /* ── Color picker ↔ hex input ─────────────────────────────── */
      $(document).on('input', '.fbpro-color-row input[type="color"]', function () {
        $(this).closest('.fbpro-color-row').find('.fbpro-hex-input').val($(this).val().toUpperCase());
      });
      $(document).on('input', '.fbpro-color-row .fbpro-hex-input', function () {
        var val = $(this).val().trim();
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
          $(this).closest('.fbpro-color-row').find('input[type="color"]').val(val.toLowerCase());
        }
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
