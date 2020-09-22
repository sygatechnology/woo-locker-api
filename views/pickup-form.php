<?php
    /*woocommerce_form_field( 'woo_locker_delivery_date', array(        
        'type'          => 'text',    
        'label'         => '',
        'class'  		=> array('woo-locker-hidden'),
        'default'		=> '',
        'custom_attributes' => [
            'style' => 'display: none !important'
        ]
    ), 
    $checkout->get_value( 'woo_locker_deliverydate' )
    );
    woocommerce_form_field( 'woo_locker_delivery_locker_id', array(        
        'type'          => 'text',    
        'label'         => '',
        'class'  		=> array('woo-locker-hidden'),
        'default'		=> '',
        'custom_attributes' => [
            'style' => 'display: none !important'
        ]
    ), 
    $checkout->get_value( 'woo_locker_delivery_locker_id' )
    );

    woocommerce_form_field( 'woo_locker_delivery_locker_name', array(        
        'type'          => 'text',    
        'label'         => '',
        'class'  		=> array('woo-locker-hidden'),
        'default'		=> '',
        'custom_attributes' => [
            'style' => 'display: none !important'
        ]
    ), 
    $checkout->get_value( 'woo_locker_delivery_locker_name' )
    );   */
    ?>

<div id="woo-schedule-checker-fieldset">
    <div id="woo-locker-spinner" class="woo-locker-spinner woo-locker-hidden">
        <img src="<?php echo esc_url( plugins_url('assets/img/spinner.gif', WOO_LOCKER_API_ASSETS_DIR) ); ?>" alt="chargement..." />
    </div>
    <div id="woo-locker-schedule-checker-container" class="woo-locker-hidden">
        <ul class="delivery-list-item-chosen__form__list">
            <?php
            foreach(WOO_LOCKER_SCHEDULE_OPTIONS as $value => $label){
            ?>
                <li class="delivery-list-item-chosen__form__list__item">
                    <div class="delivery-list-item-chosen__form__list__item__radio">
                        <input type="radio" name="schedule_woo_locker" id="d<?php echo $value; ?>" onClick='set_woo_locker_checkout(this.value)' class="woo-locker-radio__input" name="" value="<?php echo $value; ?>"> 
                        <div class="delivery-list-item-chosen__form__list__item__radio__indicator"></div>
                    </div> 
                    <label for="d<?php echo $value; ?>" class="delivery-list-item-chosen__form__list__item__label">
                        <span class="delivery-list-item-chosen__form__list__item__label__text"><?php echo __('Je retire mon premier panier le', 'woolockerapi'); ?> 
                            <b><span class="woo-delivery-date-choice"></span> <?php echo $label; ?></b>
                        </span>
                    </label> 
                    <p class="delivery-list-item-chosen__form__list__item__note"></p>
                </li>
            
            <?php
            }
            ?>
        </ul>
    </div>
    <div id="woo_locker_delivery_locker_info_content-spinner" class="woo-locker-spinner woo-locker-hidden">
        <img src="<?php echo esc_url( plugins_url('assets/img/spinner.gif', WOO_LOCKER_API_ASSETS_DIR) ); ?>" alt="chargement..." />
    </div> 
    <div id="woo_locker_delivery_locker_info_content" class="subscription-tunnel__map__results__confirm__heading woo-locker-hidden">    
        <p id="woo_locker_delivery_locker_name" class="subscription-tunnel__map__results__confirm__name"></p>
        <p id="woo_locker_delivery_locker_address" class="subscription-tunnel__map__results__confirm__address"></p>
    </div>
</div>