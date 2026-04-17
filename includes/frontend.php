<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   ENQUEUE FRONTEND
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_enqueue_scripts', 'fbpro_enqueue_frontend' );
function fbpro_enqueue_frontend() {
    wp_enqueue_style( 'fbpro-style', FBPRO_URL . 'assets/frontend.css', [], FBPRO_VERSION );
    wp_add_inline_style( 'fbpro-style', fbpro_generate_css() );
    wp_enqueue_script( 'fbpro-script', FBPRO_URL . 'assets/frontend.js', [], FBPRO_VERSION, true );
}

/* ═══════════════════════════════════════════════════════════════
   RENDER FOOTER
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_footer', 'fbpro_render' );
function fbpro_render() {

    $hide = fbpro_url_visibility(); // 'both' | 'phone' | 'wa' | false
    if ( $hide === 'both' ) return;

    $show_phone = ( $hide !== 'phone' );
    $show_wa    = ( $hide !== 'wa' );

    $phone_number = esc_attr( fbpro_get('phone_number') );
    $wa_url       = esc_url( fbpro_get('wa_url') );
    $phone_tip    = esc_attr( fbpro_get('phone_tooltip') );
    $wa_tip       = esc_attr( fbpro_get('wa_tooltip') );

    $call_url = 'tel:+34' . preg_replace( '/\D/', '', $phone_number );

    // ¿El popup está activo en esta página concreta?
    $phone_popup_here = fbpro_popup_active_here( 'phone' );
    $wa_popup_here    = fbpro_popup_active_here( 'wa' );

    /* atributos del botón teléfono */
    if ( $phone_popup_here ) {
        $phone_href  = '#';
        $phone_extra = 'data-fbpro-popup="phone"';
    } else {
        $phone_href  = esc_url( $call_url );
        $phone_extra = '';
    }

    /* atributos del botón WhatsApp */
    if ( $wa_popup_here ) {
        $wa_href  = '#';
        $wa_extra = 'data-fbpro-popup="wa"';
    } else {
        $wa_href  = $wa_url;
        $wa_extra = 'target="_blank" rel="noopener noreferrer"';
    }

    ?>
    <?php $corner = fbpro_get("position_corner"); $left_class = (strpos($corner, "left") !== false) ? " fbpro-wrapper--left" : ""; ?>
    <div class="fbpro-wrapper<?php echo $left_class; ?>" role="complementary" aria-label="Contacto rápido">

        <?php if ( $show_phone ) : ?>
        <a class="fbpro-btn fbpro-btn--phone"
           href="<?php echo $phone_href; ?>"
           aria-label="<?php echo $phone_tip; ?>"
           title="<?php echo $phone_tip; ?>"
           <?php echo $phone_extra; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.47 11.47 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1C9.61 21 3 14.39 3 6a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.58a1 1 0 01-.25 1.01l-2.2 2.2z"/>
            </svg>
            <?php if ( fbpro_get('phone_tooltip') ) : ?>
            <span class="fbpro-tooltip"><?php echo esc_html( fbpro_get('phone_tooltip') ); ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if ( $show_wa ) : ?>
        <a class="fbpro-btn fbpro-btn--wa"
           href="<?php echo $wa_href; ?>"
           aria-label="<?php echo $wa_tip; ?>"
           title="<?php echo $wa_tip; ?>"
           <?php echo $wa_extra; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                <path d="M16 2C8.28 2 2 8.28 2 16c0 2.46.66 4.84 1.9 6.92L2 30l7.32-1.86A13.93 13.93 0 0016 30c7.72 0 14-6.28 14-14S23.72 2 16 2zm0 25.5a11.47 11.47 0 01-5.85-1.6l-.42-.25-4.34 1.1 1.13-4.22-.27-.44A11.5 11.5 0 1116 27.5zm6.31-8.64c-.35-.17-2.06-1.01-2.38-1.13-.32-.12-.55-.17-.78.17s-.9 1.13-1.1 1.36c-.2.23-.4.26-.75.09-.35-.17-1.47-.54-2.8-1.72a10.47 10.47 0 01-1.94-2.4c-.2-.35-.02-.54.15-.71.15-.15.35-.4.52-.6.17-.2.23-.35.35-.58.12-.23.06-.43-.03-.6-.09-.17-.78-1.88-1.07-2.57-.28-.67-.57-.58-.78-.59h-.67c-.23 0-.6.09-.91.43-.32.35-1.21 1.18-1.21 2.87s1.24 3.33 1.41 3.56c.17.23 2.43 3.71 5.89 5.2.82.36 1.46.57 1.96.73.82.26 1.57.22 2.16.13.66-.1 2.06-.84 2.35-1.65.29-.81.29-1.5.2-1.65-.08-.14-.31-.23-.66-.4z"/>
            </svg>
            <?php if ( fbpro_get('wa_tooltip') ) : ?>
            <span class="fbpro-tooltip"><?php echo esc_html( fbpro_get('wa_tooltip') ); ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

    </div>

    <?php
    /* ── Popups ── */
    if ( $show_phone && $phone_popup_here ) :
        fbpro_render_popup( 'phone' );
    endif;

    if ( $show_wa && $wa_popup_here ) :
        fbpro_render_popup( 'wa' );
    endif;
}

/* ═══════════════════════════════════════════════════════════════
   RENDER POPUP
   ═══════════════════════════════════════════════════════════════ */
function fbpro_render_popup( $btn ) {
    $mode    = fbpro_get( $btn . '_popup_mode' );
    $content = fbpro_get( $btn . '_popup_content' );
    ?>
    <div class="fbpro-overlay" id="fbpro-overlay-<?php echo esc_attr($btn); ?>" aria-hidden="true">
        <div class="fbpro-popup" role="dialog" aria-modal="true">
            <button class="fbpro-popup__close" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
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
