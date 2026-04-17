<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   LIBRERÍA DE ICONOS SVG
   ═══════════════════════════════════════════════════════════════ */
function fbpro_svg_library() {
    return [
        'phone'     => [ 'label' => 'Teléfono',   'viewBox' => '0 0 24 24', 'path' => 'M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.47 11.47 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1C9.61 21 3 14.39 3 6a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.58a1 1 0 01-.25 1.01l-2.2 2.2z' ],
        'whatsapp'  => [ 'label' => 'WhatsApp',   'viewBox' => '0 0 32 32', 'path' => 'M16 2C8.28 2 2 8.28 2 16c0 2.46.66 4.84 1.9 6.92L2 30l7.32-1.86A13.93 13.93 0 0016 30c7.72 0 14-6.28 14-14S23.72 2 16 2zm0 25.5a11.47 11.47 0 01-5.85-1.6l-.42-.25-4.34 1.1 1.13-4.22-.27-.44A11.5 11.5 0 1116 27.5zm6.31-8.64c-.35-.17-2.06-1.01-2.38-1.13-.32-.12-.55-.17-.78.17s-.9 1.13-1.1 1.36c-.2.23-.4.26-.75.09-.35-.17-1.47-.54-2.8-1.72a10.47 10.47 0 01-1.94-2.4c-.2-.35-.02-.54.15-.71.15-.15.35-.4.52-.6.17-.2.23-.35.35-.58.12-.23.06-.43-.03-.6-.09-.17-.78-1.88-1.07-2.57-.28-.67-.57-.58-.78-.59h-.67c-.23 0-.6.09-.91.43-.32.35-1.21 1.18-1.21 2.87s1.24 3.33 1.41 3.56c.17.23 2.43 3.71 5.89 5.2.82.36 1.46.57 1.96.73.82.26 1.57.22 2.16.13.66-.1 2.06-.84 2.35-1.65.29-.81.29-1.5.2-1.65-.08-.14-.31-.23-.66-.4z' ],
        'mail'      => [ 'label' => 'Email',       'viewBox' => '0 0 24 24', 'path' => 'M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z' ],
        'instagram' => [ 'label' => 'Instagram',   'viewBox' => '0 0 24 24', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z' ],
        'facebook'  => [ 'label' => 'Facebook',    'viewBox' => '0 0 24 24', 'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z' ],
        'telegram'  => [ 'label' => 'Telegram',    'viewBox' => '0 0 24 24', 'path' => 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-2.008 9.456c-.148.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L6.26 14.84l-2.948-.924c-.64-.203-.654-.64.136-.948l11.527-4.445c.535-.194 1.003.13.587 1.725z' ],
        'location'  => [ 'label' => 'Ubicación',   'viewBox' => '0 0 24 24', 'path' => 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z' ],
        'calendar'  => [ 'label' => 'Cita',        'viewBox' => '0 0 24 24', 'path' => 'M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13z' ],
        'chat'      => [ 'label' => 'Chat',        'viewBox' => '0 0 24 24', 'path' => 'M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z' ],
        'star'      => [ 'label' => 'Valoración',  'viewBox' => '0 0 24 24', 'path' => 'M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z' ],
        'arrowup'   => [ 'label' => 'Subir arriba','viewBox' => '0 0 24 24', 'path' => 'M4 12l1.41 1.41L11 7.83V20h2V7.83l5.58 5.59L20 12l-8-8-8 8z' ],
    ];
}

/* ═══════════════════════════════════════════════════════════════
   DEFAULTS
   ═══════════════════════════════════════════════════════════════ */
function fbpro_button_defaults() {
    return [
        'id'             => '',
        'active'         => true,
        'order'          => 0,
        'label'          => 'Nuevo botón',
        // Contenido
        'action_type'    => 'link',
        'url'            => '',
        'target'         => '_blank',
        'tooltip'        => '',
        // Icono
        'icon_type'      => 'svg',
        'icon_svg'       => 'phone',
        'icon_image_id'  => 0,
        'icon_size'      => 46,
        // Estilo
        'bg_color'       => '#2A90A0',
        'icon_color'     => '#ffffff',
        'size'           => 56,
        'radius'         => 16,
        'shadow'         => 2,
        // Hover
        'hover_effect'   => 'scale',
        // Popup
        'popup_mode'     => 'shortcode',
        'popup_content'  => '',
        'popup_css'      => '',
        'popup_pages'    => '',
        // Visibilidad
        'hide_mobile'    => false,
        'hide_desktop'   => false,
        'hide_on'        => '',
    ];
}

function fbpro_global_defaults() {
    return [
        'position_corner' => 'bottom-right',
        'offset_x'        => 22,
        'offset_y'        => 24,
        'entrance_anim'   => true,
        'pulse_ring'      => true,
    ];
}

/* ═══════════════════════════════════════════════════════════════
   ACCESO A DATOS
   ═══════════════════════════════════════════════════════════════ */
function fbpro_get_buttons() {
    $buttons = get_option( 'fbpro_buttons', null );
    if ( ! is_array( $buttons ) ) return [];
    return $buttons;
}

function fbpro_save_buttons( $buttons ) {
    update_option( 'fbpro_buttons', array_values( $buttons ) );
}

function fbpro_get_global() {
    $global = get_option( 'fbpro_global', null );
    if ( ! is_array( $global ) ) return fbpro_global_defaults();
    return wp_parse_args( $global, fbpro_global_defaults() );
}

function fbpro_save_global( $global ) {
    update_option( 'fbpro_global', $global );
}

function fbpro_generate_uid() {
    return 'btn_' . substr( md5( uniqid( '', true ) ), 0, 8 );
}

/* ═══════════════════════════════════════════════════════════════
   UTILIDADES CSS
   ═══════════════════════════════════════════════════════════════ */
function fbpro_hex_to_rgba( $hex, $alpha = 1 ) {
    $hex = ltrim( $hex, '#' );
    if ( strlen( $hex ) === 3 ) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    return "rgba({$r},{$g},{$b},{$alpha})";
}

function fbpro_shadow( $hex, $level ) {
    $c = fbpro_hex_to_rgba( $hex, 0.4 );
    switch ( (string) $level ) {
        case '0': return 'none';
        case '1': return "0 2px 8px {$c}";
        case '2': return "0 4px 14px {$c}, 0 1px 3px rgba(0,0,0,.12)";
        case '3': return "0 6px 24px {$c}, 0 2px 8px rgba(0,0,0,.18)";
        default:  return "0 4px 14px {$c}";
    }
}

function fbpro_corner_css( $corner, $ox, $oy ) {
    switch ( $corner ) {
        case 'bottom-left': return "bottom:{$oy}px; left:{$ox}px; right:auto; top:auto;";
        case 'top-right':   return "top:{$oy}px; right:{$ox}px; bottom:auto; left:auto;";
        case 'top-left':    return "top:{$oy}px; left:{$ox}px; bottom:auto; right:auto;";
        default:            return "bottom:{$oy}px; right:{$ox}px; top:auto; left:auto;";
    }
}

/* ═══════════════════════════════════════════════════════════════
   VISIBILIDAD (FRONTEND)
   Devuelve true si el botón debe mostrarse en la página actual
   ═══════════════════════════════════════════════════════════════ */
function fbpro_button_visible( $btn ) {
    if ( empty( $btn['active'] ) ) return false;

    $rules = trim( $btn['hide_on'] ?? '' );
    if ( empty( $rules ) ) return true;

    $current = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    foreach ( array_filter( array_map( 'trim', explode( "\n", $rules ) ) ) as $rule ) {
        $rule = rtrim( $rule, '/' );

        if ( stripos( $rule, 'posttype:' ) === 0 ) {
            $pt = trim( substr( $rule, 9 ) );
            if ( ! empty( $pt ) && ( is_singular( $pt ) || is_post_type_archive( $pt ) ) ) {
                return false;
            }
            continue;
        }

        if ( $current === $rule || '/' . ltrim( $current, '/' ) === '/' . ltrim( $rule, '/' ) ) {
            return false;
        }
    }

    return true;
}

/* ═══════════════════════════════════════════════════════════════
   POPUP ACTIVO EN PÁGINA ACTUAL
   ═══════════════════════════════════════════════════════════════ */
function fbpro_popup_active_here( $btn ) {
    if ( ( $btn['action_type'] ?? 'link' ) !== 'popup' ) return false;

    $rules = trim( $btn['popup_pages'] ?? '' );
    if ( empty( $rules ) ) return true;

    $current = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    foreach ( array_filter( array_map( 'trim', explode( "\n", $rules ) ) ) as $rule ) {
        $rule = rtrim( $rule, '/' );

        if ( stripos( $rule, 'posttype:' ) === 0 ) {
            $pt = trim( substr( $rule, 9 ) );
            if ( ! empty( $pt ) && ( is_singular( $pt ) || is_post_type_archive( $pt ) ) ) {
                return true;
            }
            continue;
        }

        if ( $current === $rule || '/' . ltrim( $current, '/' ) === '/' . ltrim( $rule, '/' ) ) {
            return true;
        }
    }

    return false;
}

/* ═══════════════════════════════════════════════════════════════
   GENERAR CSS DINÁMICO (N BOTONES)
   ═══════════════════════════════════════════════════════════════ */
function fbpro_generate_css() {
    $buttons = fbpro_get_buttons();
    $global  = fbpro_get_global();

    $corner = $global['position_corner'];
    $ox     = absint( $global['offset_x'] );
    $oy     = absint( $global['offset_y'] );
    $pos    = fbpro_corner_css( $corner, $ox, $oy );

    $css = ".fbpro-wrapper { {$pos} }\n";

    $active_index = 0;
    foreach ( $buttons as $btn ) {
        if ( empty( $btn['active'] ) ) continue;

        $id         = esc_attr( $btn['id'] );
        $bg         = sanitize_hex_color( $btn['bg_color'] ?? '#2A90A0' ) ?: '#2A90A0';
        $size       = absint( $btn['size'] ?? 56 ) ?: 56;
        $radius     = absint( $btn['radius'] ?? 16 );
        $shadow     = fbpro_shadow( $bg, $btn['shadow'] ?? 2 );
        $icon_color = sanitize_hex_color( $btn['icon_color'] ?? '#ffffff' ) ?: '#ffffff';
        $icon_size  = absint( $btn['icon_size'] ?? 46 );

        // Animación de entrada con stagger
        $delay    = 0.3 + ( $active_index * 0.15 );
        $entrance = $global['entrance_anim']
            ? "animation: fbpro-enter 0.5s cubic-bezier(0.34,1.56,0.64,1) {$delay}s both;"
            : '';

        // Pulse ring
        $pulse = $global['pulse_ring']
            ? "[data-btn-id=\"{$id}\"]::before { display:block; background:{$bg}; border-radius:{$radius}px; width:{$size}px; height:{$size}px; }"
            : "[data-btn-id=\"{$id}\"]::before { display:none !important; }";

        // Hover
        $hover       = $btn['hover_effect'] ?? 'scale';
        $h_transform = $h_filter = '';
        if ( $hover === 'scale' )           $h_transform = 'transform: scale(1.08) !important;';
        elseif ( $hover === 'pulse' )       $h_transform = 'animation: fbpro-hover-pulse 0.35s ease !important;';
        elseif ( $hover === 'brightness' )  $h_filter    = 'filter: brightness(1.12) !important;';

        // Visibilidad por dispositivo
        $hide_mob  = ! empty( $btn['hide_mobile'] );
        $hide_desk = ! empty( $btn['hide_desktop'] );

        $css .= "
[data-btn-id=\"{$id}\"] {
    background-color: {$bg} !important;
    width: {$size}px !important;
    height: {$size}px !important;
    border-radius: {$radius}px !important;
    box-shadow: {$shadow} !important;
    {$entrance}
}
[data-btn-id=\"{$id}\"] svg {
    fill: {$icon_color} !important;
    width: {$icon_size}% !important;
    height: {$icon_size}% !important;
}
[data-btn-id=\"{$id}\"]:hover,
[data-btn-id=\"{$id}\"]:focus-visible {
    background-color: {$bg} !important;
    {$h_transform}
    {$h_filter}
}
{$pulse}
";
        if ( $hide_mob )  $css .= "@media(max-width:768px){ [data-btn-id=\"{$id}\"]{display:none!important;} }\n";
        if ( $hide_desk ) $css .= "@media(min-width:769px){ [data-btn-id=\"{$id}\"]{display:none!important;} }\n";

        $active_index++;
    }

    return $css;
}

/* ═══════════════════════════════════════════════════════════════
   RENDER SVG ICON
   ═══════════════════════════════════════════════════════════════ */
function fbpro_render_icon_html( $btn ) {
    if ( ( $btn['icon_type'] ?? 'svg' ) === 'image' && ! empty( $btn['icon_image_id'] ) ) {
        $src = wp_get_attachment_image_url( $btn['icon_image_id'], 'thumbnail' );
        if ( $src ) {
            $s = absint( $btn['icon_size'] ?? 46 );
            return '<img src="' . esc_url( $src ) . '" alt="" aria-hidden="true" style="width:' . $s . '%;height:' . $s . '%;object-fit:contain;pointer-events:none;">';
        }
    }

    $svgs = fbpro_svg_library();
    $slug = $btn['icon_svg'] ?? 'phone';
    $svg  = $svgs[ $slug ] ?? $svgs['phone'];

    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . esc_attr( $svg['viewBox'] ) . '" fill="currentColor" aria-hidden="true"><path d="' . $svg['path'] . '"/></svg>';
}
