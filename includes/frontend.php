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
    $left_class = ( strpos( $corner, 'left' ) !== false ) ? ' fbpro-wrapper--left'   : '';
    $vert_class = ( strpos( $corner, 'top'  ) !== false ) ? ' fbpro-wrapper--top'    : ' fbpro-wrapper--bottom';

    $circle_btns = array_filter( $visible, function( $btn ) {
        $is_bar = in_array( $btn['popup_display'] ?? 'modal', [ 'bar-bottom', 'bar-top' ], true );
        return ! ( $is_bar && ! empty( $btn['hide_floating_button'] ) );
    } );

    if ( ! empty( $circle_btns ) ) {
        echo '<div class="fbpro-wrapper' . $left_class . $vert_class . '" role="complementary" aria-label="Botones de contacto">';
        foreach ( $circle_btns as $btn ) {
            fbpro_render_button( $btn );
        }
        echo '</div>';
    }

    // Renderizar popups
    foreach ( $visible as $btn ) {
        if ( ( $btn['action_type'] ?? 'link' ) !== 'popup' ) continue;
        // Con trigger_slug: renderizar siempre (el botón ya pasó fbpro_button_visible)
        // Sin trigger_slug: solo si popup_pages incluye la página actual
        if ( ! empty( $btn['trigger_slug'] ) || fbpro_popup_active_here( $btn ) ) {
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

    $schedule_attrs = '';
    $wrapper_style  = '';
    if ( ! empty( $btn['schedule_enabled'] ) ) {
        $days = implode( ',', array_map( 'intval', (array)( $btn['schedule_days'] ?? [] ) ) );
        $schedule_attrs = sprintf(
            ' data-schedule="1" data-schedule-days="%s" data-schedule-from="%s" data-schedule-to="%s"',
            esc_attr( $days ),
            esc_attr( $btn['schedule_from'] ?? '00:00' ),
            esc_attr( $btn['schedule_to']   ?? '23:59' )
        );
        $wrapper_style = ' style="display:none"';
    }

    $bubble     = $btn['bubble'] ?? [];
    $has_bubble = ! empty( $bubble['enabled'] ) && ! empty( $bubble['message'] );

    echo '<div class="fbpro-btn-wrapper" data-wrapper-id="' . $id . '"' . $wrapper_style . '>';
    echo '<a class="fbpro-btn' . $custom_class . '" data-btn-id="' . $id . '" href="' . $href . '" aria-label="' . $tooltip . '" title="' . $tooltip . '"' . ( $extra ? ' ' . $extra : '' ) . $schedule_attrs . '>';
    echo $icon_html;
    if ( $tooltip ) {
        echo '<span class="fbpro-tooltip">' . esc_html( $btn['tooltip'] ) . '</span>';
    }
    echo '</a>';
    if ( $has_bubble ) {
        fbpro_render_bubble( $id, $bubble );
    }
    echo '</div>';
}

function fbpro_render_bubble( $btn_id, $bubble ) {
    $pos  = in_array( $bubble['position'] ?? 'left', ['left','right','top','bottom'], true ) ? $bubble['position'] : 'left';
    $anim = in_array( $bubble['animation'] ?? 'fade', ['fade','slide','bounce','none'], true ) ? $bubble['animation'] : 'fade';
    $delay = absint( $bubble['delay'] ?? 3 );
    $ac    = absint( $bubble['auto_close'] ?? 0 );
    $rem   = ! empty( $bubble['remember_close'] ) ? '1' : '0';
    $out   = ! empty( $bubble['close_on_outside'] ) ? '1' : '0';
    ?>
    <div class="fbpro-bubble fbpro-bubble--<?php echo esc_attr( $pos ); ?> fbpro-bubble--anim-<?php echo esc_attr( $anim ); ?>"
         data-bubble-id="<?php echo esc_attr( $btn_id ); ?>"
         data-delay="<?php echo $delay; ?>"
         data-auto-close="<?php echo $ac; ?>"
         data-remember-close="<?php echo $rem; ?>"
         data-close-on-outside="<?php echo $out; ?>"
         style="display:none">
        <?php if ( ! empty( $bubble['show_arrow'] ) ) : ?>
        <span class="fbpro-bubble__arrow"></span>
        <?php endif; ?>
        <?php if ( ! empty( $bubble['title'] ) ) : ?>
        <div class="fbpro-bubble__title"><?php echo esc_html( $bubble['title'] ); ?></div>
        <?php endif; ?>
        <div class="fbpro-bubble__message"><?php echo wp_kses( $bubble['message'], [
            'strong' => [], 'em' => [], 'br' => [], 'b' => [], 'i' => [],
        ] ); ?></div>
        <?php if ( ! empty( $bubble['closable'] ) ) : ?>
        <button class="fbpro-bubble__close" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" width="14" height="14">
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none"/>
            </svg>
        </button>
        <?php endif; ?>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════════════════════
   RENDER POPUP
   ═══════════════════════════════════════════════════════════════ */
function fbpro_render_popup( $btn ) {
    $id         = esc_attr( $btn['id'] );
    $mode       = $btn['popup_mode']      ?? 'shortcode';
    $content    = $btn['popup_content']   ?? '';
    $display    = $btn['popup_display']   ?? 'modal';
    $show_close = isset( $btn['popup_show_close'] ) ? (bool) $btn['popup_show_close'] : true;

    $popup_css_scoped = '';
    if ( ! empty( $btn['popup_css'] ) ) {
        $popup_css_scoped = fbpro_scope_popup_css( $btn['popup_css'], $id );
    }

    $trigger       = $btn['popup_trigger'] ?? [];
    $trigger_attrs = '';
    if ( ! empty( $trigger['enabled'] ) && ( ! empty( $trigger['on_time'] ) || ! empty( $trigger['on_scroll'] ) || ! empty( $trigger['on_close'] ) ) ) {
        $trigger_attrs = sprintf(
            ' data-trigger-on-time="%s" data-trigger-time-delay="%d" data-trigger-on-scroll="%s" data-trigger-scroll-percent="%d" data-trigger-once="%s"',
            ! empty( $trigger['on_time'] )          ? '1' : '0',
            max( 1, min( 120, absint( $trigger['time_delay']       ?? 10 ) ) ),
            ! empty( $trigger['on_scroll'] )        ? '1' : '0',
            max( 5, min( 95,  absint( $trigger['scroll_percent']   ?? 50 ) ) ),
            ! empty( $trigger['once_per_session'] ) ? '1' : '0'
        );
        if ( ! empty( $trigger['on_close'] ) && ! empty( $trigger['close_target'] ) ) {
            $trigger_attrs .= ' data-trigger-on-close="1" data-trigger-close-target="' . esc_attr( $trigger['close_target'] ) . '"';
        }
    }

    $slug_attr = ! empty( $btn['trigger_slug'] ) ? ' data-fbpro-slug="' . esc_attr( $btn['trigger_slug'] ) . '"' : '';

    $close_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">'
               . '<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>'
               . '</svg>';

    if ( $display === 'modal' ) :
    ?>
    <div class="fbpro-overlay" id="fbpro-overlay-<?php echo $id; ?>"<?php echo $slug_attr; ?> inert<?php echo $trigger_attrs; ?>>
        <div class="fbpro-popup" id="fbpro-popup-<?php echo $id; ?>" role="dialog" aria-modal="true">
            <?php if ( $popup_css_scoped ) : ?>
            <style><?php echo $popup_css_scoped; ?></style>
            <?php endif; ?>
            <?php if ( $show_close ) : ?>
            <button class="fbpro-popup__close" aria-label="Cerrar"><?php echo $close_svg; ?></button>
            <?php endif; ?>
            <div class="fbpro-popup__body">
                <?php
                if ( $mode === 'shortcode' && ! empty( $content ) ) {
                    echo do_shortcode( wp_kses_post( $content ) );
                } elseif ( $mode === 'html' && ! empty( $content ) ) {
                    // Intencional: no se filtra el HTML en modo 'html'. La seguridad la garantiza
                    // la verificación de manage_options en fbpro_verify_nonce() al guardar el botón.
                    echo $content;
                } else {
                    echo '<p style="color:#999;text-align:center;">Configura el contenido del popup en <strong>Ajustes → Floating Buttons Pro</strong>.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    else :
        $bar_mod = ( $display === 'bar-top' ) ? 'top' : 'bottom';
    ?>
    <div class="fbpro-bar fbpro-bar--<?php echo $bar_mod; ?>" id="fbpro-overlay-<?php echo $id; ?>"<?php echo $slug_attr; ?> inert<?php echo $trigger_attrs; ?>>
        <div class="fbpro-bar__body" id="fbpro-popup-<?php echo $id; ?>">
            <?php if ( $popup_css_scoped ) : ?>
            <style><?php echo $popup_css_scoped; ?></style>
            <?php endif; ?>
            <?php
            if ( $mode === 'shortcode' && ! empty( $content ) ) {
                echo do_shortcode( wp_kses_post( $content ) );
            } elseif ( $mode === 'html' && ! empty( $content ) ) {
                echo $content;
            } else {
                echo '<p style="color:#999;text-align:center;">Configura el contenido del popup en <strong>Ajustes → Floating Buttons Pro</strong>.</p>';
            }
            ?>
        </div>
        <?php if ( $show_close ) : ?>
        <button class="fbpro-bar__close fbpro-popup__close" aria-label="Cerrar"><?php echo $close_svg; ?></button>
        <?php endif; ?>
    </div>
    <?php
    endif;
}
