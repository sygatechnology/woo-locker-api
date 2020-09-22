<?php
/**
 *
 * @package         elementor-widgets
 */

 namespace EW;

define( 'EW_VER', '1.0.0' );
define( 'EW_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'elementor-widgets' );
define( 'EW_URL', WOO_LOCKER_API__PLUGIN_URL . 'elementor-widgets' );
define( 'EW_PATH', WOO_LOCKER_API__PLUGIN_PATH . 'elementor-widgets' );

/**
 * Load the class loader.
 */
require_once EW_DIR . '/inc/class.widgets-loader.php';

/**
 * Load the Plugin Class.
 */
function ew_init() {
	Header_Footer_Elementor::instance();
}

add_action( 'plugins_loaded', 'ew_init' );