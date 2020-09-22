<?php
/**
 * @package WooLockerPickupApi
 */
/*
Plugin Name: WooLocker Pickup API
Plugin URI: https://github.com/sygatechnology/woo-locker-api
Description: Un système de livraison personnalisé pour WooCommerce lié à des APIs de gestion des casiers
Version: 1.0.0
Author: SYGA
Author URI: https://github.com/sygatechnology/
License: MIT License
Text Domain: woolockerapi
*/

namespace SYGA;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    define( 'WOO_LOCKER_API_VERSION', '1.0.0' );
	define( 'WOO_LOCKER_API__MINIMUM_WP_VERSION', '4.0' );
	define( 'SHIPPING_METHOD_ID', 'woo_locker_pickup');
	define( 'WOO_LOCKER_API__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WOO_LOCKER_API__PLUGIN_URL', plugins_url( '/', __FILE__ ) );
	define( 'WOO_LOCKER_API__PLUGIN_PATH', plugin_basename( __FILE__ ) );
	define( 'WOO_LOCKER_API_INTERFACES_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'interfaces/' );
	define( 'WOO_LOCKER_API_CLASSES_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'includes/' );
	define( 'WOO_LOCKER_API_MODELS_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'models/' );
	define( 'WOO_LOCKER_API_VIEWS_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'views/' );
	define( 'WOO_LOCKER_API_ASSETS_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'assets/' );
	define( 'WOO_LOCKER_API_DELETE_LIMIT', 100000 );
	define( 'WOO_LOCKER_API_MIN_ITEMS_IN_CART', 1 );
	define( 'WOO_LOCKER_API_MAX_ITEMS_IN_CART', 1 );
	define( 'WOO_LOCKER_API_POST_TYPE', 'locker' );
	define( 'WOO_LOCKER_API_POST_TYPE_TAXONOMIES', array( 'locker_providers' => 'Prestataires' ) );
	define( 'WOO_LOCKER_SCHEDULE_OPTIONS', array(
		"morning" => __('dès 8h jusqu\'à 12h', 'woolockerapi'),
		"evening" => __('dès 14h jusqu\'à 19h', 'woolockerapi')
	));
	define( 'WOO_LOCKER_API_SUBSCRIPTION_POST_TYPE', 'sumosubscriptions' );
	define( 'WOO_LOCKER_API_SUBSCRIPTION_ORDER_META', 'sumo_get_parent_order_id' );
	define( 'WOO_LOCKER_API_SUBSCRIPTION_NEXT_PAYEMENT_META', 'next_payment_date' );
	
	//require_once( WOO_LOCKER_API__PLUGIN_DIR . 'woo-locker-api-functions.php' );

	//add_action( 'init', 'update_woo_locker_api_po_file' );

	require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-post-type.php' );
	add_action( 'init', array( 'WooLockerApiPostType', 'createPostType' ) );

	require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api.php' );

	register_activation_hook( __FILE__, array( 'WooLockerApi', 'pluginActivation' ) );
	register_deactivation_hook( __FILE__, array( 'WooLockerApi', 'pluginDeactivation' ) );

	add_action( 'init', array( 'WooLockerApi', 'init' ) );
	
}