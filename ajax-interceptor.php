<?php

define( 'WP_DEBUG', true );

define( 'DOING_AJAX', true );

/** Load WordPress Bootstrap */
require_once dirname(dirname(dirname( __DIR__ ))). '/wp-load.php';

/** Allow for cross-domain requests (from the front end). */
send_origin_headers();

header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
header( 'X-Robots-Tag: noindex' );

// Require an action parameter.
if ( empty( $_REQUEST['action'] ) ) {
	wp_die( '0', 400 );
}

if(!defined('WOO_LOCKER_API_CLASSES_DIR')){
    define( 'WOO_LOCKER_API__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WOO_LOCKER_API_INTERFACES_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'interfaces/' );
	define( 'WOO_LOCKER_API_CLASSES_DIR', WOO_LOCKER_API__PLUGIN_DIR . 'includes/' );
}

if(!empty($_GET) OR !empty($_POST)){

    require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-service.php' );

    if($_GET['action'] == 'get_availabilties') {
        $service = new WooLockerApiService();
        echo json_encode( $service->getAvailabilities($_GET['date']) );
    }

    if($_POST['action'] == 'set_locker') {
        $service = new WooLockerApiService();
        echo json_encode( $service->setLockerSession($_POST['locker']) );
    }

    if($_POST['action'] == 'save_order') {
        
    }

    if($_POST['action'] == 'update_order') {
        
    }

    if($_POST['action'] == 'cancel_order') {
        
    }

}