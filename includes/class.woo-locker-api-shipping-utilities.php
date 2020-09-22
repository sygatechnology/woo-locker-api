<?php

/**
 * 
 */
class WooLockerApiShippingUtilities
{
    public static function registerShippingMethod(){

        require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-shipping-method.php' );

        /**
		 * Redirect users after add to cart.
		 */
		function woo_locker_add_to_cart_redirect( $url ) {
            //die();
            require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-shipping-method.php' );
            woo_locker_pickup_shipping_method_init();
			$wooLockerApiShippingMethod = new WooLockerApiShippingMethod();
			$settings = $wooLockerApiShippingMethod->settings;
			$page_id = (int) $settings['woo_add_to_cart_redirection_page'];
			if($page_id == 0) return $url;
			return get_permalink( $page_id );
		}

		add_filter( 'woocommerce_add_to_cart_redirect', 'woo_locker_add_to_cart_redirect' );
        
    }

    public static function wooLockerApiValidateOrder( $posted ) {
	 
        /*$packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( SHIPPING_METHOD_ID, $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != SHIPPING_METHOD_ID ) {
                    continue;
                }
 
                $wooLockerApiShippingMethod = new WooLockerApiShippingMethod();
                $weightLimit = (int) $wooLockerApiShippingMethod->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + (int) $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                    $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'woocommerce' ), $weight, $weightLimit, $TutsPlus_Shipping_Method->title );
                         
                    $messageType = "error";

                    if( ! wc_has_notice( $message, $messageType ) ) {
                     
                        wc_add_notice( $message, $messageType );
                  
                    }
                }
            }       
        } */
    }

}