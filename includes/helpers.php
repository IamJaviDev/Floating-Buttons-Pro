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
        'custom_class'   => '',
        // Icono
        'icon_type'      => 'svg',
        'icon_svg'       => 'phone',
        'icon_image_id'  => 0,
        'icon_size'      => 46,
        'image_fit'      => 'cover',
        // Estilo
        'bg_type'        => 'solid',
        'bg_color'       => '#2A90A0',
        'gradient_from'  => '#2A90A0',
        'gradient_to'    => '#1a6e7e',
        'gradient_angle' => 135,
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
        // Programación horaria
        'schedule_enabled' => false,
        'schedule_days'    => [1,2,3,4,5,6,7],
        'schedule_from'    => '09:00',
        'schedule_to'      => '20:00',
        // Bocadillo
        'bubble' => [
            'enabled'          => false,
            'title'            => '',
            'message'          => '',
            'delay'            => 3,
            'auto_close'       => 0,
            'closable'         => true,
            'close_on_outside' => false,
            'remember_close'   => true,
            'position'         => 'left',
            'show_arrow'       => true,
            'bg_color'         => '#ffffff',
            'title_color'      => '#1a1a2e',
            'text_color'       => '#4b5563',
            'border_color'     => '#e5e7eb',
            'border_width'     => 1,
            'border_radius'    => 12,
            'title_size'       => 15,
            'text_size'        => 13,
            'padding'          => 14,
            'max_width'        => 240,
            'shadow'           => '2',
            'animation'        => 'fade',
        ],
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

function fbpro_bubble_shadow( $level ) {
    switch ( (string) $level ) {
        case '0': return 'none';
        case '1': return '0 2px 8px rgba(0,0,0,.10)';
        case '2': return '0 4px 14px rgba(0,0,0,.15), 0 1px 3px rgba(0,0,0,.08)';
        case '3': return '0 6px 24px rgba(0,0,0,.22), 0 2px 8px rgba(0,0,0,.14)';
        default:  return '0 4px 14px rgba(0,0,0,.15), 0 1px 3px rgba(0,0,0,.08)';
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
   URL / POSTTYPE RULE MATCHER
   Soporta:
     /ruta/exacta/       → coincidencia exacta (sin trailing slash)
     /ruta/*             → coincide con /ruta y cualquier URL hija
     posttype:nombre     → singular o archivo del post type
   ═══════════════════════════════════════════════════════════════ */
function fbpro_rule_matches_current( $rule, $current ) {
    // posttype:nombre
    if ( stripos( $rule, 'posttype:' ) === 0 ) {
        $pt = trim( substr( $rule, 9 ) );
        return ! empty( $pt ) && ( is_singular( $pt ) || is_post_type_archive( $pt ) );
    }

    // Wildcard: /ruta/* → coincide con /ruta y /ruta/hija/...
    if ( substr( $rule, -2 ) === '/*' ) {
        $prefix = rtrim( substr( $rule, 0, -2 ), '/' );
        // normalizar $current al mismo formato
        $c = '/' . ltrim( $current, '/' );
        $p = '/' . ltrim( $prefix, '/' );
        return $c === $p || strpos( $c, $p . '/' ) === 0;
    }

    // Exacta
    $rule = rtrim( $rule, '/' );
    return $current === $rule
        || '/' . ltrim( $current, '/' ) === '/' . ltrim( $rule, '/' );
}

/* ═══════════════════════════════════════════════════════════════
   VISIBILIDAD (FRONTEND)
   Devuelve true si el botón debe mostrarse en la página actual
   ═══════════════════════════════════════════════════════════════ */
function fbpro_button_visible( $btn ) {
    if ( empty( $btn['active'] ) ) return false;

    $current = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    // Popup buttons: popup_pages controls where the button (and popup) appears
    if ( ( $btn['action_type'] ?? 'link' ) === 'popup' ) {
        $rules = trim( $btn['popup_pages'] ?? '' );
        if ( empty( $rules ) ) return true;
        foreach ( array_filter( array_map( 'trim', explode( "\n", $rules ) ) ) as $rule ) {
            if ( fbpro_rule_matches_current( $rule, $current ) ) return true;
        }
        return false;
    }

    // Link buttons: hide_on exclusion list
    $rules = trim( $btn['hide_on'] ?? '' );
    if ( empty( $rules ) ) return true;

    foreach ( array_filter( array_map( 'trim', explode( "\n", $rules ) ) ) as $rule ) {
        if ( fbpro_rule_matches_current( $rule, $current ) ) return false;
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
        if ( fbpro_rule_matches_current( $rule, $current ) ) {
            return true;
        }
    }

    return false;
}

/* ═══════════════════════════════════════════════════════════════
   SCOPER CSS DE POPUP
   Capa 1 — @scope nativo (Chrome 118+, Safari 17.4+, Firefox 128+)
   Capa 2 — fallback con prefijado de selectores para navegadores
             que no soporten @scope.
   El output es un bloque @scope seguido del fallback prefijado;
   los navegadores modernos usarán @scope y los antiguos el fallback.
   ═══════════════════════════════════════════════════════════════ */
function fbpro_scope_popup_css( $raw_css, $popup_id ) {
    if ( empty( trim( $raw_css ) ) ) return '';

    // — Sanitizar —
    $css = preg_replace( '#</style\s*>#i', '', $raw_css );
    $css = preg_replace( '/@import\b[^;]*;/i', '', $css );
    $css = str_ireplace( [ 'javascript:', 'expression(', 'behavior:' ], [ '', '', '' ], $css );
    $css = preg_replace( '#/\*.*?\*/#s', '', $css ); // strip comments

    $selector = '#fbpro-popup-' . $popup_id;

    // ── Capa 1: @scope ──────────────────────────────────────────
    // Envuelve el CSS sin tocar selectores; el navegador se encarga
    // de aplicarlo solo dentro de $selector.
    $scoped_block = "@scope ({$selector}) {\n{$css}\n}";

    // ── Capa 2: fallback con prefijado de selectores ─────────────
    $prefix = $selector;

    $scope_sel = function ( $raw_sel ) use ( $prefix ) {
        $out = [];
        foreach ( explode( ',', $raw_sel ) as $sel ) {
            $sel = trim( $sel );
            if ( $sel === '' ) continue;
            if ( preg_match( '/^(#fbpro-popup|\.fbpro-popup|html\b|body\b|:root\b)/i', $sel ) ) {
                $out[] = $sel;
            } else {
                $out[] = $prefix . ' ' . $sel;
            }
        }
        return implode( ', ', $out );
    };

    $fallback = '';
    $pos      = 0;
    $len      = strlen( $css );

    while ( $pos < $len ) {
        if ( $css[ $pos ] <= ' ' ) { $pos++; continue; }

        if ( $css[ $pos ] === '@' ) {
            $start = $pos;
            while ( $pos < $len && $css[ $pos ] !== '{' && $css[ $pos ] !== ';' ) { $pos++; }
            if ( $pos >= $len ) break;

            if ( $css[ $pos ] === ';' ) { $pos++; continue; } // @import already stripped

            $header = substr( $css, $start, $pos - $start );
            $pos++; // past {

            $inner = '';
            $depth = 1;
            while ( $pos < $len && $depth > 0 ) {
                $ch = $css[ $pos++ ];
                if ( $ch === '{' )      { $depth++; $inner .= $ch; }
                elseif ( $ch === '}' )  { $depth--; if ( $depth > 0 ) $inner .= $ch; }
                else                    { $inner .= $ch; }
            }

            if ( preg_match( '/@(media|supports|layer|document)\b/i', $header ) ) {
                $scoped_inner = preg_replace_callback(
                    '/([^{@][^{]*)\{([^}]*)\}/s',
                    function ( $m ) use ( $scope_sel ) {
                        $s = $scope_sel( $m[1] );
                        return $s ? $s . '{' . $m[2] . '}' : '';
                    },
                    $inner
                );
                $fallback .= $header . '{' . $scoped_inner . "}\n";
            } else {
                $fallback .= $header . '{' . $inner . "}\n"; // @keyframes, @font-face, etc.
            }
            continue;
        }

        $sel_start = $pos;
        while ( $pos < $len && $css[ $pos ] !== '{' ) { $pos++; }
        $rule_selector = substr( $css, $sel_start, $pos - $sel_start );
        if ( $pos >= $len ) break;
        $pos++;

        $props = '';
        $depth = 1;
        while ( $pos < $len && $depth > 0 ) {
            $ch = $css[ $pos++ ];
            if ( $ch === '{' )      { $depth++; $props .= $ch; }
            elseif ( $ch === '}' )  { $depth--; if ( $depth > 0 ) $props .= $ch; }
            else                    { $props .= $ch; }
        }

        $scoped = $scope_sel( $rule_selector );
        if ( $scoped !== '' ) {
            $fallback .= $scoped . '{' . $props . "}\n";
        }
    }

    // Navegadores modernos ejecutan @scope e ignoran el fallback
    // (la especificidad de @scope gana). Navegadores sin @scope
    // ignoran el bloque @scope desconocido y usan el fallback.
    return $scoped_block . "\n" . $fallback;
}

/* ═══════════════════════════════════════════════════════════════
   SANITIZACIÓN DE BOTONES
   Una única fuente de verdad: todos los AJAX handlers y el importador
   llaman a estas funciones en lugar de sanitizar inline.
   ═══════════════════════════════════════════════════════════════ */

function fbpro_sanitize_bubble( $raw ) {
    if ( ! is_array( $raw ) ) $raw = [];
    return [
        'enabled'          => ! empty( $raw['enabled'] ),
        'title'            => sanitize_text_field( $raw['title'] ?? '' ),
        'message'          => wp_kses( $raw['message'] ?? '', [
            'strong' => [], 'em' => [], 'br' => [], 'b' => [], 'i' => [],
        ]),
        'delay'            => max( 0, min( 60, absint( $raw['delay'] ?? 3 ) ) ),
        'auto_close'       => max( 0, min( 120, absint( $raw['auto_close'] ?? 0 ) ) ),
        'closable'         => ! empty( $raw['closable'] ),
        'close_on_outside' => ! empty( $raw['close_on_outside'] ),
        'remember_close'   => ! empty( $raw['remember_close'] ),
        'position'         => in_array( $raw['position'] ?? 'left', ['left','right','top','bottom'], true )
                              ? $raw['position'] : 'left',
        'show_arrow'       => ! empty( $raw['show_arrow'] ),
        'bg_color'         => sanitize_hex_color( $raw['bg_color'] ?? '#ffffff' ) ?: '#ffffff',
        'title_color'      => sanitize_hex_color( $raw['title_color'] ?? '#1a1a2e' ) ?: '#1a1a2e',
        'text_color'       => sanitize_hex_color( $raw['text_color'] ?? '#4b5563' ) ?: '#4b5563',
        'border_color'     => sanitize_hex_color( $raw['border_color'] ?? '#e5e7eb' ) ?: '#e5e7eb',
        'border_width'     => max( 0, min( 10, absint( $raw['border_width'] ?? 1 ) ) ),
        'border_radius'    => max( 0, min( 50, absint( $raw['border_radius'] ?? 12 ) ) ),
        'title_size'       => max( 10, min( 32, absint( $raw['title_size'] ?? 15 ) ) ),
        'text_size'        => max( 10, min( 24, absint( $raw['text_size'] ?? 13 ) ) ),
        'padding'          => max( 4, min( 40, absint( $raw['padding'] ?? 14 ) ) ),
        'max_width'        => max( 100, min( 500, absint( $raw['max_width'] ?? 240 ) ) ),
        'shadow'           => in_array( (string)( $raw['shadow'] ?? '2' ), ['0','1','2','3'], true )
                              ? (string) $raw['shadow'] : '2',
        'animation'        => in_array( $raw['animation'] ?? 'fade', ['fade','slide','bounce','none'], true )
                              ? $raw['animation'] : 'fade',
    ];
}

function fbpro_sanitize_css_class( $value ) {
    if ( empty( $value ) ) return '';
    $classes = preg_split( '/\s+/', trim( $value ) );
    $clean   = [];
    foreach ( $classes as $class ) {
        $s = sanitize_html_class( $class );
        if ( $s !== '' ) $clean[] = $s;
    }
    return implode( ' ', $clean );
}

function fbpro_sanitize_schedule_days( $days ) {
    if ( ! is_array( $days ) ) return [ 1, 2, 3, 4, 5, 6, 7 ];
    $clean = [];
    foreach ( $days as $d ) {
        $d = intval( $d );
        if ( $d >= 1 && $d <= 7 ) $clean[] = $d;
    }
    return ! empty( $clean ) ? array_values( array_unique( $clean ) ) : [ 1, 2, 3, 4, 5, 6, 7 ];
}

function fbpro_sanitize_time( $time ) {
    if ( ! is_string( $time ) || ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) return '09:00';
    list( $h, $m ) = explode( ':', $time );
    $h = max( 0, min( 23, intval( $h ) ) );
    $m = max( 0, min( 59, intval( $m ) ) );
    return sprintf( '%02d:%02d', $h, $m );
}

function fbpro_sanitize_button( $raw ) {
    if ( ! is_array( $raw ) ) return [];
    $svgs = array_keys( fbpro_icon_library() );

    return [
        'id'             => sanitize_text_field( $raw['id'] ?? '' ),
        'active'         => ! empty( $raw['active'] ),
        'order'          => absint( $raw['order'] ?? 0 ),
        'label'          => sanitize_text_field( $raw['label'] ?? 'Nuevo botón' ),

        'action_type'    => in_array( $raw['action_type'] ?? '', [ 'link', 'popup' ] ) ? $raw['action_type'] : 'link',
        'url'            => esc_url_raw( $raw['url'] ?? '' ),
        'target'         => in_array( $raw['target'] ?? '', [ '_self', '_blank' ] ) ? $raw['target'] : '_blank',
        'tooltip'        => sanitize_text_field( $raw['tooltip'] ?? '' ),
        'custom_class'   => fbpro_sanitize_css_class( $raw['custom_class'] ?? '' ),

        'icon_type'      => in_array( $raw['icon_type'] ?? '', [ 'svg', 'image' ] ) ? $raw['icon_type'] : 'svg',
        'icon_svg'       => in_array( $raw['icon_svg'] ?? '', $svgs ) ? $raw['icon_svg'] : 'phone',
        'icon_image_id'  => absint( $raw['icon_image_id'] ?? 0 ),
        'icon_size'      => max( 10, min( 90, absint( $raw['icon_size'] ?? 46 ) ) ),
        'image_fit'      => in_array( $raw['image_fit'] ?? '', [ 'cover', 'contain', 'fill' ] ) ? $raw['image_fit'] : 'cover',

        'bg_type'        => in_array( $raw['bg_type'] ?? '', [ 'solid', 'gradient' ] ) ? $raw['bg_type'] : 'solid',
        'bg_color'       => sanitize_hex_color( $raw['bg_color'] ?? '#2A90A0' ) ?: '#2A90A0',
        'gradient_from'  => sanitize_hex_color( $raw['gradient_from'] ?? '#2A90A0' ) ?: '#2A90A0',
        'gradient_to'    => sanitize_hex_color( $raw['gradient_to']   ?? '#1a6e7e' ) ?: '#1a6e7e',
        'gradient_angle' => max( 0, min( 360, absint( $raw['gradient_angle'] ?? 135 ) ) ),
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

        'schedule_enabled' => ! empty( $raw['schedule_enabled'] ),
        'schedule_days'    => fbpro_sanitize_schedule_days( $raw['schedule_days'] ?? [] ),
        'schedule_from'    => fbpro_sanitize_time( $raw['schedule_from'] ?? '09:00' ),
        'schedule_to'      => fbpro_sanitize_time( $raw['schedule_to']   ?? '20:00' ),

        'bubble'           => fbpro_sanitize_bubble( $raw['bubble'] ?? [] ),
    ];
}

function fbpro_sanitize_buttons_array( $buttons ) {
    if ( ! is_array( $buttons ) ) return [];
    $clean = [];
    foreach ( $buttons as $btn ) {
        if ( ! is_array( $btn ) ) continue;
        $sanitized = fbpro_sanitize_button( $btn );
        if ( empty( $sanitized['id'] ) ) {
            $sanitized['id'] = fbpro_generate_uid();
        }
        $clean[] = $sanitized;
    }
    return $clean;
}

/* ═══════════════════════════════════════════════════════════════
   GENERAR CSS DINÁMICO (N BOTONES)
   ═══════════════════════════════════════════════════════════════ */
function fbpro_generate_css() {
    $cache_key = 'fbpro_css_v1';
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) {
        return $cached;
    }

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

        $id   = esc_attr( $btn['id'] );
        $size = absint( $btn['size'] ?? 56 ) ?: 56;

        if ( ( $btn['bg_type'] ?? 'solid' ) === 'gradient' ) {
            $from         = sanitize_hex_color( $btn['gradient_from'] ?? '#2A90A0' ) ?: '#2A90A0';
            $to           = sanitize_hex_color( $btn['gradient_to']   ?? '#1a6e7e' ) ?: '#1a6e7e';
            $ang          = max( 0, min( 360, absint( $btn['gradient_angle'] ?? 135 ) ) );
            $bg           = "linear-gradient({$ang}deg, {$from}, {$to})";
            $shadow_color = $from;
        } else {
            $bg           = sanitize_hex_color( $btn['bg_color'] ?? '#2A90A0' ) ?: '#2A90A0';
            $shadow_color = $bg;
        }

        $radius     = absint( $btn['radius'] ?? 16 );
        $shadow     = fbpro_shadow( $shadow_color, $btn['shadow'] ?? 2 );
        $icon_color = sanitize_hex_color( $btn['icon_color'] ?? '#ffffff' ) ?: '#ffffff';
        $icon_size  = absint( $btn['icon_size'] ?? 46 );
        $image_fit  = in_array( $btn['image_fit'] ?? '', [ 'cover', 'contain', 'fill' ] ) ? $btn['image_fit'] : 'cover';

        // Animación de entrada con stagger
        $delay    = 0.3 + ( $active_index * 0.15 );
        $entrance = $global['entrance_anim']
            ? "animation: fbpro-enter 0.5s cubic-bezier(0.34,1.56,0.64,1) {$delay}s both;"
            : '';

        // Pulse ring
        $pulse = $global['pulse_ring']
            ? ".fbpro-btn[data-btn-id=\"{$id}\"]::before { display:block; background:{$bg}; border-radius:{$radius}px; width:{$size}px; height:{$size}px; }"
            : ".fbpro-btn[data-btn-id=\"{$id}\"]::before { display:none !important; }";

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
.fbpro-btn[data-btn-id=\"{$id}\"] {
    background: {$bg} !important;
    width: {$size}px !important;
    height: {$size}px !important;
    border-radius: {$radius}px !important;
    box-shadow: {$shadow} !important;
    {$entrance}
}
.fbpro-btn[data-btn-id=\"{$id}\"] svg {
    fill: {$icon_color} !important;
    width: {$icon_size}% !important;
    height: {$icon_size}% !important;
}
.fbpro-btn[data-btn-id=\"{$id}\"] .fbpro-btn__img {
    width: {$icon_size}% !important;
    height: {$icon_size}% !important;
    object-fit: {$image_fit} !important;
    object-position: center !important;
    border-radius: inherit !important;
    pointer-events: none !important;
}
.fbpro-btn[data-btn-id=\"{$id}\"]:hover,
.fbpro-btn[data-btn-id=\"{$id}\"]:focus-visible {
    background: {$bg} !important;
    {$h_transform}
    {$h_filter}
}
{$pulse}
";
        if ( $hide_mob )  $css .= "@media(max-width:768px){ [data-wrapper-id=\"{$id}\"]{display:none!important;} }\n";
        if ( $hide_desk ) $css .= "@media(min-width:769px){ [data-wrapper-id=\"{$id}\"]{display:none!important;} }\n";

        $active_index++;
    }

    // Bubble CSS per button
    foreach ( $buttons as $btn ) {
        if ( empty( $btn['active'] ) ) continue;
        if ( empty( $btn['bubble']['enabled'] ) ) continue;

        $id = esc_attr( $btn['id'] );
        $b  = $btn['bubble'];

        $bg           = sanitize_hex_color( $b['bg_color']     ?? '#ffffff' ) ?: '#ffffff';
        $text_color   = sanitize_hex_color( $b['text_color']   ?? '#4b5563' ) ?: '#4b5563';
        $border_color = sanitize_hex_color( $b['border_color'] ?? '#e5e7eb' ) ?: '#e5e7eb';
        $title_color  = sanitize_hex_color( $b['title_color']  ?? '#1a1a2e' ) ?: '#1a1a2e';
        $bw           = absint( $b['border_width']  ?? 1 );
        $br           = absint( $b['border_radius'] ?? 12 );
        $padding      = absint( $b['padding']   ?? 14 );
        $max_width    = absint( $b['max_width']  ?? 240 );
        $text_size    = absint( $b['text_size']  ?? 13 );
        $title_size   = absint( $b['title_size'] ?? 15 );
        $shadow       = fbpro_bubble_shadow( $b['shadow'] ?? '2' );

        $css .= "
.fbpro-bubble[data-bubble-id=\"{$id}\"] {
    background: {$bg};
    color: {$text_color};
    border: {$bw}px solid {$border_color};
    border-radius: {$br}px;
    padding: {$padding}px;
    max-width: {$max_width}px;
    font-size: {$text_size}px;
    box-shadow: {$shadow};
}
.fbpro-bubble[data-bubble-id=\"{$id}\"] .fbpro-bubble__title {
    color: {$title_color};
    font-size: {$title_size}px;
}
";
    }

    set_transient( $cache_key, $css, DAY_IN_SECONDS );
    return $css;
}

/* ═══════════════════════════════════════════════════════════════
   CACHÉ E INVALIDACIÓN DE CSS
   ═══════════════════════════════════════════════════════════════ */
function fbpro_invalidate_css_cache() {
    delete_transient( 'fbpro_css_v1' );
}
add_action( 'update_option_fbpro_buttons', 'fbpro_invalidate_css_cache' );
add_action( 'add_option_fbpro_buttons',    'fbpro_invalidate_css_cache' );
add_action( 'update_option_fbpro_global',  'fbpro_invalidate_css_cache' );
add_action( 'add_option_fbpro_global',     'fbpro_invalidate_css_cache' );

/* fbpro_render_icon() → definida en includes/icons.php (FASE 2) */
