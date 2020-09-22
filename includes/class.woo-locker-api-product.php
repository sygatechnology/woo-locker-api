<?php

class WooLockerProduct{

    public function registerCustomFields(){
        woocommerce_wp_select( 
            array( 
                'id' => '_woo_recursive_freq', 
                'label' => __( 'Fréquence', 'woolockerapi' ), 
                'options' => array(
                    '1:7' => __( 'Une fois par semaine', 'woolockerapi' ),
                    '2:7' => __( 'Deux fois par semaine', 'woolockerapi' ),
                    '1:15' => __( 'Une fois pour 15 jours', 'woolockerapi' )
                )
            )
        );
    }

    public function saveCustomMetaFields( $post_id ) {
        $freq = $_POST['_woo_recursive_freq'];
        if( ! empty( $freq ) ) {
            update_post_meta( $post_id, '_woo_recursive_freq', esc_attr( $freq ) );
        }
    }

    public function minAndMaxQuantityValidationCart($bool, $product_id, $quantity){
        $total = WC()->cart->get_cart_contents_count() + $quantity;
        $min = WOO_LOCKER_API_MIN_ITEMS_IN_CART;
        $max = WOO_LOCKER_API_MAX_ITEMS_IN_CART;

        if ($quantity >= $min && $total <= $max) {
            return true;
        }

        // If selected quantity is to large, display message
        if ($total > $max) {
            wc_add_notice('Le plafond d\'achat maximum est de ' . $max . '. Veuillez mettre à jour votre panier pour échanger vos sélections.', 'error');
            return false;
        }

        // If selected quantity is to small, show message
        wc_add_notice('Le plafond d\'achat minimum est de ' . $min . '.', 'error');
        return false;
        
    }

}