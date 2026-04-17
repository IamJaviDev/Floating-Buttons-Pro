<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   MENÚ
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

/* ═══════════════════════════════════════════════════════════════
   ASSETS ADMIN
   ═══════════════════════════════════════════════════════════════ */
add_action( 'admin_enqueue_scripts', 'fbpro_admin_assets' );
function fbpro_admin_assets( $hook ) {
    if ( $hook !== 'settings_page_floating-buttons-pro' ) return;

    wp_enqueue_media();
    wp_enqueue_style( 'fbpro-admin-style', FBPRO_URL . 'assets/admin.css', [], FBPRO_VERSION );
    wp_enqueue_script( 'jquery-ui-sortable' );

    // CodeMirror para el editor de CSS del popup
    $cm_settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

    wp_enqueue_script(
        'fbpro-admin-script',
        FBPRO_URL . 'assets/admin.js',
        [ 'jquery', 'jquery-ui-sortable', 'wp-codemirror' ],
        FBPRO_VERSION,
        true
    );

    // Preparar botones con URL de imagen para el admin JS
    $buttons = fbpro_get_buttons();
    foreach ( $buttons as &$btn ) {
        $btn['icon_image_url'] = '';
        if ( ( $btn['icon_type'] ?? 'svg' ) === 'image' && ! empty( $btn['icon_image_id'] ) ) {
            $btn['icon_image_url'] = wp_get_attachment_image_url( $btn['icon_image_id'], 'thumbnail' ) ?: '';
        }
    }
    unset( $btn );

    wp_localize_script( 'fbpro-admin-script', 'fbproData', [
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'nonce'        => wp_create_nonce( 'fbpro_admin' ),
        'buttons'      => $buttons,
        'global'       => fbpro_get_global(),
        'svgLibrary'   => fbpro_icon_admin_data(),
        'defaults'     => fbpro_button_defaults(),
        'setupPending' => (bool) get_option( 'fbpro_setup_pending' ),
        'cmSettings'   => $cm_settings ?: false,
    ] );
}

/* ═══════════════════════════════════════════════════════════════
   AVISO DE SETUP INICIAL
   ═══════════════════════════════════════════════════════════════ */
add_action( 'admin_notices', 'fbpro_setup_notice' );
function fbpro_setup_notice() {
    if ( ! get_option( 'fbpro_setup_pending' ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    $page = admin_url( 'options-general.php?page=floating-buttons-pro' );
    ?>
    <div class="notice notice-info is-dismissible" id="fbpro-global-notice">
        <p>
            <strong>Floating Buttons Pro:</strong>
            El plugin se ha actualizado a la versión 2.0.
            <a href="<?php echo esc_url( $page ); ?>">Configura tus botones</a>
            para empezar.
        </p>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════════════════════
   PÁGINA DE AJUSTES
   ═══════════════════════════════════════════════════════════════ */
function fbpro_admin_page() {
    $global = fbpro_get_global();
    ?>
    <div class="fbpro-admin-wrap">

        <div class="fbpro-admin-header">
            <h1>
                <span class="fbpro-admin-logo">FB</span>
                Floating Buttons Pro
                <span class="fbpro-version">v<?php echo FBPRO_VERSION; ?></span>
            </h1>
            <p class="fbpro-admin-subtitle">Compatible con Divi &amp; Elementor</p>
        </div>

        <!-- Setup notice (solo primera vez) -->
        <?php if ( get_option( 'fbpro_setup_pending' ) ) : ?>
        <div class="fbpro-setup-banner" id="fbpro-setup-banner">
            <div class="fbpro-setup-banner__icon">🚀</div>
            <div class="fbpro-setup-banner__text">
                <strong>¡Bienvenido a Floating Buttons Pro 2.0!</strong><br>
                ¿Quieres empezar con los botones de Teléfono y WhatsApp preconfigurados, o prefieres empezar desde cero?
            </div>
            <div class="fbpro-setup-banner__actions">
                <button class="fbpro-btn-primary" id="fbpro-setup-defaults">Crear botones por defecto</button>
                <button class="fbpro-btn-secondary" id="fbpro-setup-empty">Empezar vacío</button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabs principales -->
        <div class="fbpro-tabs">
            <button type="button" class="fbpro-tab active" data-tab="buttons">Botones</button>
            <button type="button" class="fbpro-tab" data-tab="global">Ajustes globales</button>
        </div>

        <!-- ══ TAB: BOTONES ══ -->
        <div class="fbpro-tab-panel active" id="tab-buttons">

            <div id="fbpro-warning-many" class="fbpro-warning" style="display:none">
                ⚠️ Tienes más de 10 botones. Demasiados botones pueden perjudicar la experiencia en móvil.
            </div>

            <div id="fbpro-buttons-list" class="fbpro-buttons-list">
                <!-- JS renderiza las cards aquí -->
                <div class="fbpro-loading">Cargando botones…</div>
            </div>

            <button type="button" id="fbpro-add-btn" class="fbpro-add-btn">
                <span>+</span> Añadir botón
            </button>

        </div>

        <!-- ══ TAB: AJUSTES GLOBALES ══ -->
        <div class="fbpro-tab-panel" id="tab-global">
            <div class="fbpro-card">
                <h2>Posición</h2>
                <div class="fbpro-grid-2">
                    <div class="fbpro-field">
                        <label>Esquina</label>
                        <select id="fbpro-global-corner">
                            <?php
                            $corners = [
                                'bottom-right' => 'Abajo derecha',
                                'bottom-left'  => 'Abajo izquierda',
                                'top-right'    => 'Arriba derecha',
                                'top-left'     => 'Arriba izquierda',
                            ];
                            foreach ( $corners as $val => $lbl ) {
                                $sel = selected( $global['position_corner'], $val, false );
                                echo "<option value='{$val}'{$sel}>{$lbl}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="fbpro-field"></div>
                    <div class="fbpro-field">
                        <label>Separación horizontal (px)</label>
                        <input type="number" id="fbpro-global-offset-x" min="0" max="200"
                            value="<?php echo esc_attr( $global['offset_x'] ); ?>">
                    </div>
                    <div class="fbpro-field">
                        <label>Separación vertical (px)</label>
                        <input type="number" id="fbpro-global-offset-y" min="0" max="200"
                            value="<?php echo esc_attr( $global['offset_y'] ); ?>">
                    </div>
                </div>
            </div>

            <div class="fbpro-card">
                <h2>Animaciones</h2>
                <div class="fbpro-field">
                    <label class="fbpro-toggle">
                        <input type="checkbox" id="fbpro-global-entrance"
                            <?php checked( $global['entrance_anim'] ); ?>>
                        <span>Animación de entrada al cargar la página</span>
                    </label>
                </div>
                <div class="fbpro-field">
                    <label class="fbpro-toggle">
                        <input type="checkbox" id="fbpro-global-pulse"
                            <?php checked( $global['pulse_ring'] ); ?>>
                        <span>Pulse ring (anillo pulsante alrededor del botón)</span>
                    </label>
                </div>
            </div>

            <div class="fbpro-submit-bar">
                <button type="button" id="fbpro-save-global" class="fbpro-btn-primary">
                    Guardar ajustes globales
                </button>
                <span class="fbpro-save-status" id="fbpro-global-status"></span>
            </div>
        </div>

    </div><!-- /fbpro-admin-wrap -->

    <!-- ══ MODAL EDICIÓN BOTÓN ══ -->
    <div class="fbpro-modal-overlay" id="fbpro-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="fbpro-modal-title">
        <div class="fbpro-modal">
            <div class="fbpro-modal-header">
                <h2 id="fbpro-modal-title">Nuevo botón</h2>
                <button class="fbpro-modal-close" id="fbpro-modal-close" aria-label="Cerrar">✕</button>
            </div>

            <div class="fbpro-modal-tabs">
                <button class="fbpro-mtab active" data-mtab="contenido">Contenido</button>
                <button class="fbpro-mtab" data-mtab="icono">Icono</button>
                <button class="fbpro-mtab" data-mtab="estilo">Estilo</button>
                <button class="fbpro-mtab" data-mtab="popup">Popup</button>
                <button class="fbpro-mtab" data-mtab="visibilidad">Visibilidad</button>
            </div>

            <div class="fbpro-modal-body" id="fbpro-modal-body">
                <!-- JS renderiza los paneles aquí -->
            </div>

            <div class="fbpro-modal-footer">
                <span class="fbpro-save-status" id="fbpro-modal-status"></span>
                <button type="button" class="fbpro-btn-secondary" id="fbpro-modal-cancel">Cancelar</button>
                <button type="button" class="fbpro-btn-primary" id="fbpro-modal-save">Guardar botón</button>
            </div>
        </div>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════════════════════
   HELPERS DE SANITIZACIÓN
   ═══════════════════════════════════════════════════════════════ */
function fbpro_verify_nonce() {
    if ( ! check_ajax_referer( 'fbpro_admin', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => 'Security check failed' ], 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
    }
}

function fbpro_sanitize_button( $raw ) {
    $svgs    = array_keys( fbpro_icon_library() );
    $corners = [ 'bottom-right', 'bottom-left', 'top-right', 'top-left' ];

    return [
        'id'             => sanitize_text_field( $raw['id'] ?? '' ),
        'active'         => ! empty( $raw['active'] ),
        'order'          => absint( $raw['order'] ?? 0 ),
        'label'          => sanitize_text_field( $raw['label'] ?? 'Nuevo botón' ),

        'action_type'    => in_array( $raw['action_type'] ?? '', [ 'link', 'popup' ] ) ? $raw['action_type'] : 'link',
        'url'            => esc_url_raw( $raw['url'] ?? '' ),
        'target'         => in_array( $raw['target'] ?? '', [ '_self', '_blank' ] ) ? $raw['target'] : '_blank',
        'tooltip'        => sanitize_text_field( $raw['tooltip'] ?? '' ),

        'icon_type'      => in_array( $raw['icon_type'] ?? '', [ 'svg', 'image' ] ) ? $raw['icon_type'] : 'svg',
        'icon_svg'       => in_array( $raw['icon_svg'] ?? '', $svgs ) ? $raw['icon_svg'] : 'phone',
        'icon_image_id'  => absint( $raw['icon_image_id'] ?? 0 ),
        'icon_size'      => max( 10, min( 90, absint( $raw['icon_size'] ?? 46 ) ) ),

        'bg_color'       => sanitize_hex_color( $raw['bg_color'] ?? '#2A90A0' ) ?: '#2A90A0',
        'icon_color'     => sanitize_hex_color( $raw['icon_color'] ?? '#ffffff' ) ?: '#ffffff',
        'size'           => max( 32, min( 120, absint( $raw['size'] ?? 56 ) ) ),
        'radius'         => min( 100, absint( $raw['radius'] ?? 16 ) ),
        'shadow'         => min( 3, absint( $raw['shadow'] ?? 2 ) ),

        'hover_effect'   => in_array( $raw['hover_effect'] ?? '', [ 'scale', 'pulse', 'brightness', 'none' ] ) ? $raw['hover_effect'] : 'scale',

        'popup_mode'     => in_array( $raw['popup_mode'] ?? '', [ 'shortcode', 'html' ] ) ? $raw['popup_mode'] : 'shortcode',
        'popup_content'  => wp_kses_post( $raw['popup_content'] ?? '' ),
        'popup_css'      => sanitize_textarea_field( $raw['popup_css'] ?? '' ),
        'popup_pages'    => sanitize_textarea_field( $raw['popup_pages'] ?? '' ),

        'hide_mobile'    => ! empty( $raw['hide_mobile'] ),
        'hide_desktop'   => ! empty( $raw['hide_desktop'] ),
        'hide_on'        => sanitize_textarea_field( $raw['hide_on'] ?? '' ),
    ];
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: GUARDAR BOTÓN
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_save_button', 'fbpro_ajax_save_button' );
function fbpro_ajax_save_button() {
    fbpro_verify_nonce();

    $raw = json_decode( stripslashes( $_POST['button'] ?? '{}' ), true );
    if ( ! is_array( $raw ) ) wp_send_json_error( [ 'message' => 'Datos inválidos' ] );

    $btn     = fbpro_sanitize_button( $raw );
    $buttons = fbpro_get_buttons();

    // ¿Nuevo o actualizar existente?
    $found = false;
    foreach ( $buttons as $i => $existing ) {
        if ( $existing['id'] === $btn['id'] && ! empty( $btn['id'] ) ) {
            $buttons[ $i ] = $btn;
            $found = true;
            break;
        }
    }

    if ( ! $found ) {
        $btn['id']    = fbpro_generate_uid();
        $btn['order'] = count( $buttons );
        $buttons[]    = $btn;
    }

    fbpro_save_buttons( $buttons );

    // Añadir icon_image_url para devolver al JS
    $btn['icon_image_url'] = '';
    if ( $btn['icon_type'] === 'image' && $btn['icon_image_id'] ) {
        $btn['icon_image_url'] = wp_get_attachment_image_url( $btn['icon_image_id'], 'thumbnail' ) ?: '';
    }

    wp_send_json_success( [ 'button' => $btn ] );
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: ELIMINAR BOTÓN
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_delete_button', 'fbpro_ajax_delete_button' );
function fbpro_ajax_delete_button() {
    fbpro_verify_nonce();

    $id = sanitize_text_field( $_POST['id'] ?? '' );
    if ( empty( $id ) ) wp_send_json_error( [ 'message' => 'ID no proporcionado' ] );

    $buttons = fbpro_get_buttons();
    $buttons = array_values( array_filter( $buttons, function( $b ) use ( $id ) {
        return $b['id'] !== $id;
    } ) );

    // Renumerar order
    foreach ( $buttons as $i => &$b ) {
        $b['order'] = $i;
    }
    unset( $b );

    fbpro_save_buttons( $buttons );
    wp_send_json_success( [] );
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: DUPLICAR BOTÓN
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_duplicate_button', 'fbpro_ajax_duplicate_button' );
function fbpro_ajax_duplicate_button() {
    fbpro_verify_nonce();

    $id = sanitize_text_field( $_POST['id'] ?? '' );
    if ( empty( $id ) ) wp_send_json_error( [ 'message' => 'ID no proporcionado' ] );

    $buttons = fbpro_get_buttons();
    $source  = null;

    foreach ( $buttons as $btn ) {
        if ( $btn['id'] === $id ) { $source = $btn; break; }
    }

    if ( ! $source ) wp_send_json_error( [ 'message' => 'Botón no encontrado' ] );

    $new          = $source;
    $new['id']    = fbpro_generate_uid();
    $new['label'] = $source['label'] . ' (copia)';
    $new['order'] = count( $buttons );

    $buttons[] = $new;
    fbpro_save_buttons( $buttons );

    $new['icon_image_url'] = '';
    if ( $new['icon_type'] === 'image' && $new['icon_image_id'] ) {
        $new['icon_image_url'] = wp_get_attachment_image_url( $new['icon_image_id'], 'thumbnail' ) ?: '';
    }

    wp_send_json_success( [ 'button' => $new ] );
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: REORDENAR BOTONES
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_reorder_buttons', 'fbpro_ajax_reorder_buttons' );
function fbpro_ajax_reorder_buttons() {
    fbpro_verify_nonce();

    $order = json_decode( stripslashes( $_POST['order'] ?? '[]' ), true );
    if ( ! is_array( $order ) ) wp_send_json_error( [ 'message' => 'Datos de orden inválidos' ] );

    $buttons = fbpro_get_buttons();
    $map     = [];
    foreach ( $order as $item ) {
        $map[ sanitize_text_field( $item['id'] ) ] = absint( $item['order'] );
    }

    foreach ( $buttons as &$btn ) {
        if ( isset( $map[ $btn['id'] ] ) ) {
            $btn['order'] = $map[ $btn['id'] ];
        }
    }
    unset( $btn );

    usort( $buttons, function( $a, $b ) {
        return ( $a['order'] ?? 0 ) - ( $b['order'] ?? 0 );
    } );

    // Renumerar limpiamente
    foreach ( $buttons as $i => &$b ) {
        $b['order'] = $i;
    }
    unset( $b );

    fbpro_save_buttons( $buttons );
    wp_send_json_success( [] );
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: GUARDAR AJUSTES GLOBALES
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_save_global', 'fbpro_ajax_save_global' );
function fbpro_ajax_save_global() {
    fbpro_verify_nonce();

    $raw = json_decode( stripslashes( $_POST['global'] ?? '{}' ), true );
    if ( ! is_array( $raw ) ) wp_send_json_error( [ 'message' => 'Datos inválidos' ] );

    $global = [
        'position_corner' => in_array( $raw['position_corner'] ?? '', [ 'bottom-right', 'bottom-left', 'top-right', 'top-left' ] )
            ? $raw['position_corner'] : 'bottom-right',
        'offset_x'        => max( 0, min( 200, absint( $raw['offset_x'] ?? 22 ) ) ),
        'offset_y'        => max( 0, min( 200, absint( $raw['offset_y'] ?? 24 ) ) ),
        'entrance_anim'   => ! empty( $raw['entrance_anim'] ),
        'pulse_ring'      => ! empty( $raw['pulse_ring'] ),
    ];

    fbpro_save_global( $global );
    wp_send_json_success( [ 'global' => $global ] );
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: SETUP INICIAL
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_setup', 'fbpro_ajax_setup' );
function fbpro_ajax_setup() {
    fbpro_verify_nonce();

    $choice = sanitize_text_field( $_POST['choice'] ?? 'empty' );

    if ( $choice === 'defaults' ) {
        $uid1 = fbpro_generate_uid();
        $uid2 = fbpro_generate_uid();
        $buttons = [
            array_merge( fbpro_button_defaults(), [
                'id'          => $uid1,
                'label'       => 'Teléfono',
                'action_type' => 'link',
                'url'         => 'tel:+34627564896',
                'target'      => '_self',
                'tooltip'     => 'Llamar ahora',
                'icon_svg'    => 'phone',
                'bg_color'    => '#2A90A0',
                'icon_color'  => '#ffffff',
                'order'       => 0,
                'active'      => true,
            ] ),
            array_merge( fbpro_button_defaults(), [
                'id'          => $uid2,
                'label'       => 'WhatsApp',
                'action_type' => 'link',
                'url'         => 'https://api.whatsapp.com/send?phone=34627564896&text=%C2%A1Hola!%20Quiero%20informaci%C3%B3n',
                'target'      => '_blank',
                'tooltip'     => 'Escribir por WhatsApp',
                'icon_svg'    => 'whatsapp',
                'bg_color'    => '#25D366',
                'icon_color'  => '#ffffff',
                'order'       => 1,
                'active'      => true,
            ] ),
        ];
        fbpro_save_buttons( $buttons );

        // Añadir icon_image_url a cada botón
        foreach ( $buttons as &$btn ) {
            $btn['icon_image_url'] = '';
        }
        unset( $btn );
    } else {
        $buttons = [];
    }

    delete_option( 'fbpro_setup_pending' );
    wp_send_json_success( [ 'buttons' => $buttons ] );
}

/* ═══════════════════════════════════════════════════════════════
   AJAX: TOGGLE ACTIVO/INACTIVO
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_fbpro_toggle_button', 'fbpro_ajax_toggle_button' );
function fbpro_ajax_toggle_button() {
    fbpro_verify_nonce();

    $id     = sanitize_text_field( $_POST['id'] ?? '' );
    $active = ! empty( $_POST['active'] );

    if ( empty( $id ) ) wp_send_json_error( [ 'message' => 'ID no proporcionado' ] );

    $buttons = fbpro_get_buttons();
    foreach ( $buttons as &$btn ) {
        if ( $btn['id'] === $id ) {
            $btn['active'] = $active;
            break;
        }
    }
    unset( $btn );

    fbpro_save_buttons( $buttons );
    wp_send_json_success( [] );
}
