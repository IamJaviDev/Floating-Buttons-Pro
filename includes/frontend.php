<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   ENQUEUE FRONTEND
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_enqueue_scripts', 'fbpro_enqueue_frontend' );
function fbpro_enqueue_frontend() {
    $buttons = fbpro_get_buttons();
    $active  = array_filter( $buttons, function( $b ) { return ! empty( $b['active'] ); } );
    if ( empty( $active ) ) return;

    wp_enqueue_style( 'fbpro-style', FBPRO_URL . 'assets/frontend.css', [], FBPRO_VERSION );
    wp_add_inline_style( 'fbpro-style', fbpro_generate_css() );
    wp_enqueue_script( 'fbpro-script', FBPRO_URL . 'assets/frontend.js', [], FBPRO_VERSION, true );

    wp_localize_script( 'fbpro-script', 'fbproFrontend', [
        'timezoneOffset' => (int) ( wp_timezone()->getOffset( new DateTime( 'now', wp_timezone() ) ) / 60 ),
    ] );
}

/* ═══════════════════════════════════════════════════════════════
   RENDER FOOTER
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_footer', 'fbpro_render' );
function fbpro_render() {
    $buttons = fbpro_get_buttons();
    if ( empty( $buttons ) ) return;

    // Filtrar botones visibles en esta página
    $visible = array_filter( $buttons, 'fbpro_button_visible' );
    if ( empty( $visible ) ) return;

    // Ordenar por campo order
    usort( $visible, function( $a, $b ) {
        return ( $a['order'] ?? 0 ) - ( $b['order'] ?? 0 );
    } );

    $global     = fbpro_get_global();
    $corner     = $global['position_corner'];
    $left_class = ( strpos( $corner, 'left' ) !== false ) ? ' fbpro-wrapper--left' : '';

    echo '<div class="fbpro-wrapper' . $left_class . '" role="complementary" aria-label="Botones de contacto">';

    foreach ( $visible as $btn ) {
        fbpro_render_button( $btn );
    }

    echo '</div>';

    // Renderizar popups
    foreach ( $visible as $btn ) {
        if ( fbpro_popup_active_here( $btn ) ) {
            fbpro_render_popup( $btn );
        }
    }
}

/* ═══════════════════════════════════════════════════════════════
   RENDER BOTÓN INDIVIDUAL
   ═══════════════════════════════════════════════════════════════ */
function fbpro_render_button( $btn ) {
    $id      = esc_attr( $btn['id'] );
    $tooltip = esc_attr( $btn['tooltip'] ?? '' );
    $popup   = fbpro_popup_active_here( $btn );

    if ( $popup ) {
        $href  = '#';
        $extra = 'data-fbpro-popup="' . $id . '"';
    } else {
        $href   = esc_url( $btn['url'] ?? '#' );
        $target = ( ( $btn['target'] ?? '_blank' ) === '_blank' )
            ? ' target="_blank" rel="noopener noreferrer"'
            : '';
        $extra  = ltrim( $target );
    }

    $icon_html    = fbpro_render_icon( $btn );
    $custom_class = ! empty( $btn['custom_class'] ) ? ' ' . esc_attr( $btn['custom_class'] ) : '';

    $schedule_attrs  = '';
    $initial_display = '';
    if ( ! empty( $btn['schedule_enabled'] ) ) {
        $days = implode( ',', array_map( 'intval', (array)( $btn['schedule_days'] ?? [] ) ) );
        $schedule_attrs = sprintf(
            ' data-schedule="1" data-schedule-days="%s" data-schedule-from="%s" data-schedule-to="%s"',
            esc_attr( $days ),
            esc_attr( $btn['schedule_from'] ?? '00:00' ),
            esc_attr( $btn['schedule_to']   ?? '23:59' )
        );
        $initial_display = ' style="display:none"';
    }

    echo '<a class="fbpro-btn' . $custom_class . '" data-btn-id="' . $id . '" href="' . $href . '" aria-label="' . $tooltip . '" title="' . $tooltip . '"' . ( $extra ? ' ' . $extra : '' ) . $schedule_attrs . $initial_display . '>';
    echo $icon_html;
    if ( $tooltip ) {
        echo '<span class="fbpro-tooltip">' . esc_html( $btn['tooltip'] ) . '</span>';
    }
    echo '</a>';
}

/* ═══════════════════════════════════════════════════════════════
   RENDER POPUP
   ═══════════════════════════════════════════════════════════════ */
function fbpro_render_popup( $btn ) {
    $id      = esc_attr( $btn['id'] );
    $mode    = $btn['popup_mode'] ?? 'shortcode';
    $content = $btn['popup_content'] ?? '';
    ?>
    <?php
    $popup_css_scoped = '';
    if ( ! empty( $btn['popup_css'] ) ) {
        $popup_css_scoped = fbpro_scope_popup_css( $btn['popup_css'], $id );
    }
    ?>
    <div class="fbpro-overlay" id="fbpro-overlay-<?php echo $id; ?>" aria-hidden="true">
        <div class="fbpro-popup" id="fbpro-popup-<?php echo $id; ?>" role="dialog" aria-modal="true">
            <?php if ( $popup_css_scoped ) : ?>
            <style><?php echo $popup_css_scoped; ?></style>
            <?php endif; ?>
            <button class="fbpro-popup__close" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="fbpro-popup__body">
                <?php
                if ( $mode === 'shortcode' && ! empty( $content ) ) {
                    echo do_shortcode( wp_kses_post( $content ) );
                } elseif ( $mode === 'html' && ! empty( $content ) ) {
                    echo wp_kses_post( $content );
                } else {
                    echo '<p style="color:#999;text-align:center;">Configura el contenido del popup en <strong>Ajustes → Floating Buttons Pro</strong>.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
