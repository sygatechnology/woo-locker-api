<?php
if( ! function_exists( 'woo_locker_api_view') ) {

    function woo_locker_api_view($view, array $data = []) {

        foreach($data as $key => $value){
            ${$key} = $value;
        }

        ob_start();
        include_once( WOO_LOCKER_API_VIEWS_DIR . $view . '.php' );
        return ob_get_clean();
    }

}

if( ! function_exists( 'update_woo_locker_api_po_file') ) {

    function update_woo_locker_api_po_file(){
        $domain = 'woolockerapi';
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ){
            return $loaded;
        } else {
            load_plugin_textdomain( $domain, FALSE, WOO_LOCKER_API__PLUGIN_DIR . 'languages/' );
        }
    }

}