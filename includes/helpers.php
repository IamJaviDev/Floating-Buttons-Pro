<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   DEFAULTS
   ═══════════════════════════════════════════════════════════════ */
function fbpro_defaults() {
    return [
        /* ── Contenido ── */
        'phone_number'        => '627564896',
        'wa_url'              => 'https://api.whatsapp.com/send?phone=34627564896&text=%C2%A1Hola!%20Quiero%20informaci%C3%B3n%20sobre%20vuestros%20tratamientos',
        'phone_tooltip'       => 'Llamar ahora',
        'wa_tooltip'          => 'Escribir por WhatsApp',

        /* ── Acción de cada botón: direct | popup ── */
        'phone_action'        => 'direct',
        'wa_action'           => 'direct',

        /* ── Popup teléfono ── */
        'phone_popup_mode'    => 'shortcode',   // shortcode | html
        'phone_popup_content' => '',

        /* ── Popup WhatsApp ── */
        'wa_popup_mode'       => 'shortcode',
        'wa_popup_content'    => '',

        /* ── Páginas donde activar popup (vacío = todas) ── */
        'phone_popup_pages'   => '',
        'wa_popup_pages'      => '',

        /* ── Estilo teléfono ── */
        'phone_color'         => '#2A90A0',
        'phone_size'          => '56',
        'phone_radius'        => '16',
        'phone_shadow'        => '2',           // 0-3

        /* ── Estilo WhatsApp ── */
        'wa_color'            => '#25D366',
        'wa_size'             => '56',
        'wa_radius'           => '16',
        'wa_shadow'           => '2',

        /* ── Hover ── */
        'hover_effect'        => 'scale',       // scale | pulse | brightness | none

        /* ── Comportamiento ── */
        'position_corner'     => 'bottom-right',
        'offset_x'            => '22',
        'offset_y'            => '24',
        'entrance_anim'       => '1',
        'pulse_ring'          => '1',

        /* ── Visibilidad global ── */
        'hide_mobile'         => '0',
        'hide_desktop'        => '0',

        /* ── Ocultar por URL ── */
        // Formato: una URL/slug por línea. Prefijo con "phone:" o "wa:" para ocultar solo ese botón.
        // Sin prefijo = oculta ambos.
        // Ejemplos:
        //   /contacto/          → oculta ambos en /contacto/
        //   phone:/about/       → oculta solo teléfono en /about/
        //   wa:/servicios/      → oculta solo WA en /servicios/
        'hide_by_url'         => '',
    ];
}

function fbpro_get( $key ) {
    $defaults = fbpro_defaults();
    return get_option( 'fbpro_' . $key, $defaults[ $key ] ?? '' );
}

/* ═══════════════════════════════════════════════════════════════
   UTILIDADES
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
        case 'bottom-left':  return "bottom:{$oy}px; left:{$ox}px; right:auto; top:auto;";
        case 'top-right':    return "top:{$oy}px; right:{$ox}px; bottom:auto; left:auto;";
        case 'top-left':     return "top:{$oy}px; left:{$ox}px; bottom:auto; right:auto;";
        default:             return "bottom:{$oy}px; right:{$ox}px; top:auto; left:auto;";
    }
}

/* ═══════════════════════════════════════════════════════════════
   VISIBILIDAD POR URL / POST TYPE
   Devuelve: 'both' | 'phone' | 'wa' | false
   Sintaxis soportada:
     /contacto/               → oculta ambos en esa URL
     phone:/about/            → oculta solo teléfono en esa URL
     wa:/aviso-legal/         → oculta solo WhatsApp en esa URL
     posttype:ciudades        → oculta ambos en todo ese CPT (singular + archive)
     phone:posttype:ciudades  → oculta solo teléfono en ese CPT
     wa:posttype:ciudades     → oculta solo WhatsApp en ese CPT
   ═══════════════════════════════════════════════════════════════ */
function fbpro_url_visibility() {
    $rules = fbpro_get('hide_by_url');
    if ( empty( trim( $rules ) ) ) return false;

    $current = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    foreach ( array_filter( array_map( 'trim', explode( "\n", $rules ) ) ) as $rule ) {
        $rule   = rtrim( $rule, '/' );
        $button = 'both';

        // Extraer prefijo de botón (phone: / wa:)
        if ( stripos( $rule, 'phone:' ) === 0 ) {
            $button = 'phone';
            $rule   = substr( $rule, 6 );
        } elseif ( stripos( $rule, 'wa:' ) === 0 ) {
            $button = 'wa';
            $rule   = substr( $rule, 3 );
        }

        $rule = rtrim( $rule, '/' );

        // ── Regla por post type ───────────────────────────────────
        if ( stripos( $rule, 'posttype:' ) === 0 ) {
            $pt = trim( substr( $rule, 9 ) );
            if ( ! empty( $pt ) && ( is_singular( $pt ) || is_post_type_archive( $pt ) ) ) {
                return $button;
            }
            continue;
        }

        // ── Regla por URL ─────────────────────────────────────────
        if ( $current === $rule || '/' . ltrim( $current, '/' ) === '/' . ltrim( $rule, '/' ) ) {
            return $button;
        }
    }
    return false;
}

/* ═══════════════════════════════════════════════════════════════
   GENERAR CSS DINÁMICO
   ═══════════════════════════════════════════════════════════════ */
function fbpro_generate_css() {
    $pc  = sanitize_hex_color( fbpro_get('phone_color') )  ?: '#2A90A0';
    $wc  = sanitize_hex_color( fbpro_get('wa_color') )     ?: '#25D366';
    $ps  = absint( fbpro_get('phone_size') )  ?: 56;
    $ws  = absint( fbpro_get('wa_size') )     ?: 56;
    $pr  = absint( fbpro_get('phone_radius') );
    $wr  = absint( fbpro_get('wa_radius') );
    $ox  = absint( fbpro_get('offset_x') );
    $oy  = absint( fbpro_get('offset_y') );
    $corner       = fbpro_get('position_corner');
    $phone_shadow = fbpro_shadow( $pc, fbpro_get('phone_shadow') );
    $wa_shadow    = fbpro_shadow( $wc, fbpro_get('wa_shadow') );
    $hover        = fbpro_get('hover_effect');
    $pulse_ring   = fbpro_get('pulse_ring');
    $hide_mob     = fbpro_get('hide_mobile');
    $hide_desk    = fbpro_get('hide_desktop');
    $entrance     = fbpro_get('entrance_anim');

    $pos = fbpro_corner_css( $corner, $ox, $oy );

    /* hover rules */
    $h_transform = '';
    $h_filter    = '';
    if ( $hover === 'scale' )      $h_transform = 'transform: scale(1.08) !important;';
    elseif ( $hover === 'pulse' )  $h_transform = 'animation: fbpro-hover-pulse 0.35s ease !important;';
    elseif ( $hover === 'brightness' ) $h_filter = 'filter: brightness(1.12) !important;';

    /* entrada */
    $phone_anim = $entrance === '1' ? 'animation: fbpro-enter 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.3s both;' : '';
    $wa_anim    = $entrance === '1' ? 'animation: fbpro-enter 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.5s both;' : '';

    /* pulse ring */
    $ring_phone = $pulse_ring === '1'
        ? ".fbpro-btn--phone::before { display:block; background:{$pc}; border-radius:{$pr}px; width:{$ps}px; height:{$ps}px; }"
        : ".fbpro-btn--phone::before { display:none !important; }";
    $ring_wa = $pulse_ring === '1'
        ? ".fbpro-btn--wa::before { display:block; background:{$wc}; border-radius:{$wr}px; width:{$ws}px; height:{$ws}px; }"
        : ".fbpro-btn--wa::before { display:none !important; }";

    $css = "
.fbpro-wrapper { {$pos} }

.fbpro-btn--phone {
    background-color: {$pc} !important;
    width: {$ps}px !important;
    height: {$ps}px !important;
    border-radius: {$pr}px !important;
    box-shadow: {$phone_shadow} !important;
    {$phone_anim}
}
.fbpro-btn--wa {
    background-color: {$wc} !important;
    width: {$ws}px !important;
    height: {$ws}px !important;
    border-radius: {$wr}px !important;
    box-shadow: {$wa_shadow} !important;
    {$wa_anim}
}
.fbpro-btn--phone:hover, .fbpro-btn--phone:focus-visible {
    background-color: {$pc} !important;
    {$h_transform}
    {$h_filter}
}
.fbpro-btn--wa:hover, .fbpro-btn--wa:focus-visible {
    background-color: {$wc} !important;
    {$h_transform}
    {$h_filter}
}
{$ring_phone}
{$ring_wa}
";

    if ( $hide_mob === '1' )  $css .= "@media(max-width:768px){ .fbpro-wrapper{ display:none !important; } }";
    if ( $hide_desk === '1' ) $css .= "@media(min-width:769px){ .fbpro-wrapper{ display:none !important; } }";

    return $css;
}

/* ═══════════════════════════════════════════════════════════════
   COMPROBAR SI EL POPUP ESTÁ ACTIVO EN LA PÁGINA ACTUAL
   $btn = 'phone' | 'wa'
   Devuelve true si debe abrir popup, false si debe actuar como enlace directo
   ═══════════════════════════════════════════════════════════════ */
function fbpro_popup_active_here( $btn ) {
    $action = fbpro_get( $btn . '_action' );
    if ( $action !== 'popup' ) return false;

    $rules = trim( fbpro_get( $btn . '_popup_pages' ) );

    // Sin reglas → popup en todas las páginas
    if ( empty( $rules ) ) return true;

    $current = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    foreach ( array_filter( array_map( 'trim', explode( "\n", $rules ) ) ) as $rule ) {
        $rule = rtrim( $rule, '/' );

        // Por post type
        if ( stripos( $rule, 'posttype:' ) === 0 ) {
            $pt = trim( substr( $rule, 9 ) );
            if ( ! empty( $pt ) && ( is_singular( $pt ) || is_post_type_archive( $pt ) ) ) {
                return true;
            }
            continue;
        }

        // Por URL
        if ( $current === $rule || '/' . ltrim( $current, '/' ) === '/' . ltrim( $rule, '/' ) ) {
            return true;
        }
    }

    return false;
}
