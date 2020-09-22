<?php

require_once( WOO_LOCKER_API__PLUGIN_DIR . 'woo-locker-api-functions.php' );

class WooLockerApiShotCode {

    public static function addPickupLockerCalendarShortCode(){

        add_shortcode('wlpcalendar', 'register_pickup_locker_calendar_shortcode');

        function register_pickup_locker_calendar_shortcode( $atts = [] ){
            return woo_locker_api_view('pickup-calendar', (array)$atts);
        }

    }

    public static function addPickupLockerFormShortCode(){
        
        add_shortcode('wlpform', 'register_pickup_locker_form_shortcode');

        function register_pickup_locker_form_shortcode( $atts = [] ){
            return woo_locker_api_view('pickup-form', (array)$atts);
        }

    }

}