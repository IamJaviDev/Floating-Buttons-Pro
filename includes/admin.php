<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   MENÚ Y REGISTRO
   ═══════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', 'fbpro_admin_menu' );
function fbpro_admin_menu() {
    add_options_page(
        'Floating Buttons Pro',
        'Floating Buttons Pro',
        'manage_options',
        'floating-buttons-pro',
        'fbpro_admin_page'
    );
}

add_action( 'admin_init', 'fbpro_register_settings' );
function fbpro_register_settings() {
    $keys = array_keys( fbpro_defaults() );
    foreach ( $keys as $key ) {
        register_setting( 'fbpro_group', 'fbpro_' . $key, [ 'sanitize_callback' => 'fbpro_sanitize_option' ] );
    }
}

function fbpro_sanitize_option( $val ) {
    return wp_kses_post( $val );
}

/* ═══════════════════════════════════════════════════════════════
   ASSETS ADMIN
   ═══════════════════════════════════════════════════════════════ */
add_action( 'admin_enqueue_scripts', 'fbpro_admin_assets' );
function fbpro_admin_assets( $hook ) {
    if ( $hook !== 'settings_page_floating-buttons-pro' ) return;
    wp_enqueue_style(  'fbpro-admin-style',  FBPRO_URL . 'assets/admin.css',  [], FBPRO_VERSION );
    wp_enqueue_script( 'fbpro-admin-script', FBPRO_URL . 'assets/admin.js',  [], FBPRO_VERSION, true );
    // Color picker nativo de WordPress
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
}

/* ═══════════════════════════════════════════════════════════════
   PÁGINA DE AJUSTES
   ═══════════════════════════════════════════════════════════════ */
function fbpro_admin_page() { ?>
<div class="fbpro-admin-wrap">

    <div class="fbpro-admin-header">
        <h1>
            <span class="fbpro-admin-logo">FB</span>
            Floating Buttons Pro
            <span class="fbpro-version">v<?php echo FBPRO_VERSION; ?></span>
        </h1>
        <p class="fbpro-admin-subtitle">Compatible con Divi &amp; Elementor</p>
    </div>

    <?php settings_errors('fbpro_group'); ?>

    <form method="post" action="options.php" class="fbpro-admin-form">
        <?php settings_fields('fbpro_group'); ?>

        <div class="fbpro-tabs">
            <button type="button" class="fbpro-tab active" data-tab="contenido">Contenido</button>
            <button type="button" class="fbpro-tab" data-tab="estilo">Estilo</button>
            <button type="button" class="fbpro-tab" data-tab="comportamiento">Comportamiento</button>
            <button type="button" class="fbpro-tab" data-tab="visibilidad">Visibilidad</button>
            <button type="button" class="fbpro-tab" data-tab="popups">Popups</button>
        </div>

        <!-- ══ TAB: CONTENIDO ══ -->
        <div class="fbpro-tab-panel active" id="tab-contenido">
            <div class="fbpro-card">
                <h2>Botón Teléfono</h2>
                <div class="fbpro-field">
                    <label>Número de teléfono <small>(sin prefijo de país)</small></label>
                    <input type="text" name="fbpro_phone_number"
                        value="<?php echo esc_attr( fbpro_get('phone_number') ); ?>"
                        placeholder="627564896">
                    <p class="fbpro-help">Se usará como <code>tel:+34XXXXXXXXX</code></p>
                </div>
                <div class="fbpro-field">
                    <label>Tooltip</label>
                    <input type="text" name="fbpro_phone_tooltip"
                        value="<?php echo esc_attr( fbpro_get('phone_tooltip') ); ?>"
                        placeholder="Llamar ahora">
                </div>
            </div>

            <div class="fbpro-card">
                <h2>Botón WhatsApp</h2>
                <div class="fbpro-field">
                    <label>URL completa de WhatsApp</label>
                    <input type="url" name="fbpro_wa_url"
                        value="<?php echo esc_attr( fbpro_get('wa_url') ); ?>"
                        placeholder="https://api.whatsapp.com/send?phone=34...&text=...">
                    <p class="fbpro-help">Pega la URL con el mensaje ya codificado.</p>
                </div>
                <div class="fbpro-field">
                    <label>Tooltip</label>
                    <input type="text" name="fbpro_wa_tooltip"
                        value="<?php echo esc_attr( fbpro_get('wa_tooltip') ); ?>"
                        placeholder="Escribir por WhatsApp">
                </div>
            </div>
        </div>

        <!-- ══ TAB: ESTILO ══ -->
        <div class="fbpro-tab-panel" id="tab-estilo">

            <div class="fbpro-card">
                <h2>Estilo – Teléfono</h2>
                <div class="fbpro-grid-2">
                    <div class="fbpro-field">
                        <label>Color de fondo</label>
                        <input type="text" name="fbpro_phone_color" class="fbpro-color-picker"
                            value="<?php echo esc_attr( fbpro_get('phone_color') ); ?>">
                    </div>
                    <div class="fbpro-field">
                        <label>Tamaño (px)</label>
                        <input type="number" name="fbpro_phone_size" min="40" max="100"
                            value="<?php echo esc_attr( fbpro_get('phone_size') ); ?>">
                    </div>
                    <div class="fbpro-field">
                        <label>Border radius (px)</label>
                        <input type="number" name="fbpro_phone_radius" min="0" max="100"
                            value="<?php echo esc_attr( fbpro_get('phone_radius') ); ?>">
                        <p class="fbpro-help">0 = cuadrado · 50+ = círculo</p>
                    </div>
                    <div class="fbpro-field">
                        <label>Sombra</label>
                        <select name="fbpro_phone_shadow">
                            <?php fbpro_shadow_options( fbpro_get('phone_shadow') ); ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="fbpro-card">
                <h2>Estilo – WhatsApp</h2>
                <div class="fbpro-grid-2">
                    <div class="fbpro-field">
                        <label>Color de fondo</label>
                        <input type="text" name="fbpro_wa_color" class="fbpro-color-picker"
                            value="<?php echo esc_attr( fbpro_get('wa_color') ); ?>">
                    </div>
                    <div class="fbpro-field">
                        <label>Tamaño (px)</label>
                        <input type="number" name="fbpro_wa_size" min="40" max="100"
                            value="<?php echo esc_attr( fbpro_get('wa_size') ); ?>">
                    </div>
                    <div class="fbpro-field">
                        <label>Border radius (px)</label>
                        <input type="number" name="fbpro_wa_radius" min="0" max="100"
                            value="<?php echo esc_attr( fbpro_get('wa_radius') ); ?>">
                        <p class="fbpro-help">0 = cuadrado · 50+ = círculo</p>
                    </div>
                    <div class="fbpro-field">
                        <label>Sombra</label>
                        <select name="fbpro_wa_shadow">
                            <?php fbpro_shadow_options( fbpro_get('wa_shadow') ); ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="fbpro-card">
                <h2>Efecto Hover</h2>
                <div class="fbpro-field">
                    <div class="fbpro-radio-group">
                        <?php
                        $hover_opts = [
                            'scale'      => 'Scale (crece levemente)',
                            'pulse'      => 'Pulse (latido rápido)',
                            'brightness' => 'Brightness (se aclara)',
                            'none'       => 'Ninguno',
                        ];
                        $current_hover = fbpro_get('hover_effect');
                        foreach ( $hover_opts as $val => $label ) : ?>
                        <label class="fbpro-radio">
                            <input type="radio" name="fbpro_hover_effect"
                                value="<?php echo $val; ?>"
                                <?php checked( $current_hover, $val ); ?>>
                            <?php echo $label; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ TAB: COMPORTAMIENTO ══ -->
        <div class="fbpro-tab-panel" id="tab-comportamiento">
            <div class="fbpro-card">
                <h2>Posición</h2>
                <div class="fbpro-grid-2">
                    <div class="fbpro-field">
                        <label>Esquina</label>
                        <select name="fbpro_position_corner">
                            <?php
                            $corners = [
                                'bottom-right' => 'Abajo derecha',
                                'bottom-left'  => 'Abajo izquierda',
                                'top-right'    => 'Arriba derecha',
                                'top-left'     => 'Arriba izquierda',
                            ];
                            $cur = fbpro_get('position_corner');
                            foreach ( $corners as $val => $label ) {
                                echo "<option value='{$val}'" . selected($cur, $val, false) . ">{$label}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="fbpro-field"><!-- spacer --></div>
                    <div class="fbpro-field">
                        <label>Separación horizontal (px)</label>
                        <input type="number" name="fbpro_offset_x" min="0" max="200"
                            value="<?php echo esc_attr( fbpro_get('offset_x') ); ?>">
                    </div>
                    <div class="fbpro-field">
                        <label>Separación vertical (px)</label>
                        <input type="number" name="fbpro_offset_y" min="0" max="200"
                            value="<?php echo esc_attr( fbpro_get('offset_y') ); ?>">
                    </div>
                </div>
            </div>

            <div class="fbpro-card">
                <h2>Animaciones</h2>
                <div class="fbpro-field">
                    <label class="fbpro-toggle">
                        <input type="checkbox" name="fbpro_entrance_anim" value="1"
                            <?php checked( fbpro_get('entrance_anim'), '1' ); ?>>
                        <span>Animación de entrada al cargar la página</span>
                    </label>
                </div>
                <div class="fbpro-field">
                    <label class="fbpro-toggle">
                        <input type="checkbox" name="fbpro_pulse_ring" value="1"
                            <?php checked( fbpro_get('pulse_ring'), '1' ); ?>>
                        <span>Pulse ring (anillo pulsante alrededor del botón)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ══ TAB: VISIBILIDAD ══ -->
        <div class="fbpro-tab-panel" id="tab-visibilidad">
            <div class="fbpro-card">
                <h2>Visibilidad por dispositivo</h2>
                <div class="fbpro-field">
                    <label class="fbpro-toggle">
                        <input type="checkbox" name="fbpro_hide_mobile" value="1"
                            <?php checked( fbpro_get('hide_mobile'), '1' ); ?>>
                        <span>Ocultar en móvil <small>(&lt; 768px)</small></span>
                    </label>
                </div>
                <div class="fbpro-field">
                    <label class="fbpro-toggle">
                        <input type="checkbox" name="fbpro_hide_desktop" value="1"
                            <?php checked( fbpro_get('hide_desktop'), '1' ); ?>>
                        <span>Ocultar en escritorio <small>(&gt; 768px)</small></span>
                    </label>
                </div>
            </div>

            <div class="fbpro-card">
                <h2>Ocultar por URL</h2>
                <div class="fbpro-field">
                    <label>URLs donde ocultar botones</label>
                    <textarea name="fbpro_hide_by_url" rows="8"
                        placeholder="/contacto/&#10;phone:/sobre-nosotros/&#10;wa:/aviso-legal/"><?php echo esc_textarea( fbpro_get('hide_by_url') ); ?></textarea>
                    <div class="fbpro-help-block">
                        <strong>Una regla por línea.</strong> Ejemplos:
                        <ul>
                            <li><code>/contacto/</code> &rarr; oculta <strong>ambos</strong> en esa URL</li>
                            <li><code>phone:/sobre-nosotros/</code> &rarr; oculta <strong>solo teléfono</strong> en esa URL</li>
                            <li><code>wa:/aviso-legal/</code> &rarr; oculta <strong>solo WhatsApp</strong> en esa URL</li>
                            <li><code>posttype:ciudades</code> &rarr; oculta <strong>ambos</strong> en todo ese CPT (singular + archivo)</li>
                            <li><code>phone:posttype:ciudades</code> &rarr; oculta <strong>solo teléfono</strong> en ese CPT</li>
                            <li><code>wa:posttype:ciudades</code> &rarr; oculta <strong>solo WhatsApp</strong> en ese CPT</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ TAB: POPUPS ══ -->
        <div class="fbpro-tab-panel" id="tab-popups">

            <div class="fbpro-card">
                <div class="fbpro-info-box">
                    <strong>¿Cómo funcionan los popups?</strong>
                    Por defecto cada botón actúa como enlace directo (llamada o WhatsApp).
                    Si activas el modo popup, al pulsar el botón se abre una ventana modal con el contenido que configures:
                    un shortcode de CF7, HTML libre, o lo que necesites.
                </div>
            </div>

            <!-- Popup teléfono -->
            <div class="fbpro-card">
                <h2>Popup – Botón Teléfono</h2>
                <div class="fbpro-field">
                    <label>Acción al pulsar</label>
                    <div class="fbpro-radio-group">
                        <label class="fbpro-radio">
                            <input type="radio" name="fbpro_phone_action" value="direct"
                                <?php checked( fbpro_get('phone_action'), 'direct' ); ?>>
                            Enlace directo <small>(llamada tel:)</small>
                        </label>
                        <label class="fbpro-radio">
                            <input type="radio" name="fbpro_phone_action" value="popup"
                                <?php checked( fbpro_get('phone_action'), 'popup' ); ?>>
                            Abrir popup
                        </label>
                    </div>
                </div>
                <div class="fbpro-popup-config" id="phone-popup-config"
                    style="<?php echo fbpro_get('phone_action') === 'popup' ? '' : 'display:none'; ?>">
                    <div class="fbpro-field">
                        <label>Modo del popup</label>
                        <select name="fbpro_phone_popup_mode" class="fbpro-popup-mode-select">
                            <option value="shortcode" <?php selected( fbpro_get('phone_popup_mode'), 'shortcode' ); ?>>
                                Shortcode (CF7 u otro)
                            </option>
                            <option value="html" <?php selected( fbpro_get('phone_popup_mode'), 'html' ); ?>>
                                HTML libre
                            </option>
                        </select>
                    </div>
                    <div class="fbpro-field">
                        <label class="fbpro-label-shortcode">Shortcode</label>
                        <label class="fbpro-label-html" style="display:none">HTML</label>
                        <textarea name="fbpro_phone_popup_content" rows="5"
                            placeholder="[contact-form-7 id=&quot;123&quot; title=&quot;Contacto&quot;]"><?php echo esc_textarea( fbpro_get('phone_popup_content') ); ?></textarea>
                        <p class="fbpro-help fbpro-hint-shortcode">Pega el shortcode de CF7 o cualquier otro plugin.</p>
                        <p class="fbpro-help fbpro-hint-html" style="display:none">Pega HTML directamente. Se acepta cualquier etiqueta estándar.</p>
                    </div>
                    <div class="fbpro-field">
                        <label>Mostrar popup solo en estas páginas <small>(vacío = todas)</small></label>
                        <textarea name="fbpro_phone_popup_pages" rows="5"
                            placeholder="/contacto/&#10;posttype:ciudades"><?php echo esc_textarea( fbpro_get('phone_popup_pages') ); ?></textarea>
                        <div class="fbpro-help-block">
                            Una regla por línea. Si está vacío el popup aparece en toda la web.<br>
                            Ejemplos: <code>/landing/</code> &nbsp;·&nbsp; <code>posttype:ciudades</code><br>
                            En páginas que no coincidan el botón actuará como enlace directo.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popup WhatsApp -->
            <div class="fbpro-card">
                <h2>Popup – Botón WhatsApp</h2>
                <div class="fbpro-field">
                    <label>Acción al pulsar</label>
                    <div class="fbpro-radio-group">
                        <label class="fbpro-radio">
                            <input type="radio" name="fbpro_wa_action" value="direct"
                                <?php checked( fbpro_get('wa_action'), 'direct' ); ?>>
                            Enlace directo <small>(abre WhatsApp)</small>
                        </label>
                        <label class="fbpro-radio">
                            <input type="radio" name="fbpro_wa_action" value="popup"
                                <?php checked( fbpro_get('wa_action'), 'popup' ); ?>>
                            Abrir popup
                        </label>
                    </div>
                </div>
                <div class="fbpro-popup-config" id="wa-popup-config"
                    style="<?php echo fbpro_get('wa_action') === 'popup' ? '' : 'display:none'; ?>">
                    <div class="fbpro-field">
                        <label>Modo del popup</label>
                        <select name="fbpro_wa_popup_mode" class="fbpro-popup-mode-select">
                            <option value="shortcode" <?php selected( fbpro_get('wa_popup_mode'), 'shortcode' ); ?>>
                                Shortcode (CF7 u otro)
                            </option>
                            <option value="html" <?php selected( fbpro_get('wa_popup_mode'), 'html' ); ?>>
                                HTML libre
                            </option>
                        </select>
                    </div>
                    <div class="fbpro-field">
                        <label class="fbpro-label-shortcode">Shortcode</label>
                        <label class="fbpro-label-html" style="display:none">HTML</label>
                        <textarea name="fbpro_wa_popup_content" rows="5"
                            placeholder="[contact-form-7 id=&quot;456&quot; title=&quot;WhatsApp Form&quot;]"><?php echo esc_textarea( fbpro_get('wa_popup_content') ); ?></textarea>
                        <p class="fbpro-help fbpro-hint-shortcode">Pega el shortcode de CF7 o cualquier otro plugin.</p>
                        <p class="fbpro-help fbpro-hint-html" style="display:none">Pega HTML directamente.</p>
                    </div>
                    <div class="fbpro-field">
                        <label>Mostrar popup solo en estas páginas <small>(vacío = todas)</small></label>
                        <textarea name="fbpro_wa_popup_pages" rows="5"
                            placeholder="/contacto/&#10;posttype:ciudades"><?php echo esc_textarea( fbpro_get('wa_popup_pages') ); ?></textarea>
                        <div class="fbpro-help-block">
                            Una regla por línea. Si está vacío el popup aparece en toda la web.<br>
                            Ejemplos: <code>/landing/</code> &nbsp;·&nbsp; <code>posttype:ciudades</code><br>
                            En páginas que no coincidan el botón actuará como enlace directo.
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /tab-popups -->

        <div class="fbpro-submit-bar">
            <?php submit_button( 'Guardar cambios', 'primary', 'submit', false ); ?>
        </div>

    </form>
</div>
<?php }

/* ── Helper: opciones de sombra ── */
function fbpro_shadow_options( $current ) {
    $opts = [ '0' => 'Sin sombra', '1' => 'Suave', '2' => 'Media', '3' => 'Intensa' ];
    foreach ( $opts as $val => $label ) {
        echo "<option value='{$val}'" . selected( $current, $val, false ) . ">{$label}</option>";
    }
}
