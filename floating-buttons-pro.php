<?php
/**
 * Plugin Name: Floating Buttons Pro
 * Description: Botones flotantes totalmente personalizables. N botones dinámicos. Compatible con Divi y Elementor.
 * Version:     2.0.0
 * Author:      Don Javier
 * License:     GPL-2.0+
 * Text Domain: fbpro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FBPRO_VERSION', '2.0.0' );
define( 'FBPRO_DIR',     plugin_dir_path( __FILE__ ) );
define( 'FBPRO_URL',     plugin_dir_url( __FILE__ ) );

require_once FBPRO_DIR . 'includes/helpers.php';
require_once FBPRO_DIR . 'includes/frontend.php';
require_once FBPRO_DIR . 'includes/admin.php';

/* ── Activation hook ─────────────────────────────────────── */
register_activation_hook( __FILE__, 'fbpro_on_activate' );
function fbpro_on_activate() {
    // Solo marcar "setup pendiente" si no hay datos previos de v2
    if ( get_option( 'fbpro_buttons' ) === false ) {
        update_option( 'fbpro_setup_pending', '1' );
    }
}
