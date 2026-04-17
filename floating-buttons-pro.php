<?php
/**
 * Plugin Name: Floating Buttons Pro
 * Description: Botones flotantes de teléfono y WhatsApp totalmente personalizables. Compatible con Divi y Elementor.
 * Version:     1.0.0
 * Author:      Don Javier
 * License:     GPL-2.0+
 * Text Domain: fbpro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FBPRO_VERSION', '1.0.0' );
define( 'FBPRO_DIR',     plugin_dir_path( __FILE__ ) );
define( 'FBPRO_URL',     plugin_dir_url( __FILE__ ) );

require_once FBPRO_DIR . 'includes/helpers.php';
require_once FBPRO_DIR . 'includes/frontend.php';
require_once FBPRO_DIR . 'includes/admin.php';
