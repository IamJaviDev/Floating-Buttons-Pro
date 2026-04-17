<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
[data-btn-id=\"{$id}\"] .fbpro-btn__img {
    width: {$icon_size}% !important;
    height: {$icon_size}% !important;
    object-fit: contain !important;
    pointer-events: none !important;
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

/* fbpro_render_icon() → definida en includes/icons.php (FASE 2) */
