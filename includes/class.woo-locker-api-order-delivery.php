<?php
class WooLockerApiOrderDelivery {

	public function customShippingFields( $fields = array() ){

		foreach($fields as $key => $value){
			unset($fields[$key]);
		}
		/*unset($fields['shipping_first_name']);
		unset($fields['shipping_last_name']);
		unset($fields['shipping_company']);
		unset($fields['shipping_address_1']);
		unset($fields['shipping_address_2']);
		unset($fields['shipping_state']);
		unset($fields['shipping_city']);
		unset($fields['shipping_phone']);
		unset($fields['shipping_postcode']);
		unset($fields['shipping_country']);*/

		return $fields;

	}

	public function orderCustomAdminColumns( $columns ) {
		$new_columns = (is_array($columns)) ? $columns : array();
		unset( $new_columns['order_actions'] );

		$new_columns['woo_locker_delivery_date'] = __('Date de distribution', 'woolockerapi');
		$new_columns['woo_locker_delivery_schedule'] = __('Horaire', 'woolockerapi');
		$new_columns['woo_locker_delivery_locker_name'] = __('Destination de la livraison', 'woolockerapi');
		$new_columns['woo_locker_delivery_locker_id'] = __('ID du casier', 'woolockerapi');

		$new_columns['order_actions'] = $columns['order_actions'];
		return $new_columns;
	}

	public function orderDeliveryEmailNotication( $keys ) {
		$label_name = __('Date de distribution', 'woolockerapi');
		$keys[$label_name] = __('Date de distribution', 'woolockerapi');
		$hour = __('Horaire', 'woolockerapi');
		$keys[$hour] = __('Horaire', 'woolockerapi');
		$locker_name = __('Destination de la livraison', 'woolockerapi');
		$keys[$locker_name] = __('Destination de la livraison', 'woolockerapi');
		$locker_id = __('ID du casier', 'woolockerapi');
		$keys[$locker_id] = __('ID du casier', 'woolockerapi');
		return $keys;
	}

	public function checkoutFieldUpdateOrderMeta( $order_id ) {    
		if (isset($_POST['woo_locker_delivery_date'])) {
			update_post_meta( $order_id, '_woo_locker_delivery_date', esc_attr($_POST['woo_locker_delivery_date']));
		}
		if(isset($_POST['schedule_woo_locker'])){
			update_post_meta( $order_id, '_woo_locker_delivery_schedule', esc_attr($_POST['schedule_woo_locker']));
		}
		if (isset($_POST['woo_locker_delivery_locker_id'])) {
			update_post_meta( $order_id, '_woo_locker_delivery_locker_id', esc_attr($_POST['woo_locker_delivery_locker_id']));
		}
		/*if (isset($_POST['woo_locker_delivery_locker_name'])) {
			update_post_meta( $order_id, '_woo_locker_delivery_locker_name', esc_attr($_POST['woo_locker_delivery_locker_name']));
		}*/
	}

	public function customColumnValue( $column ) {
		global $post;
		$data = get_post_meta( $post->ID );
		if ( $column == 'woo_locker_delivery_date' ) {    
			echo (isset($data['_woo_locker_delivery_date'][0]) ? $data['_woo_locker_delivery_date'][0] : '');
		}
		if ( $column == 'woo_locker_delivery_schedule' ) {   
			echo (isset($data['_woo_locker_delivery_schedule'][0]) ? WOO_LOCKER_SCHEDULE_OPTIONS[$data['_woo_locker_delivery_schedule'][0]] : '');
		}
		if ( $column == 'woo_locker_delivery_locker_id' ) {    
			echo (isset($data['_woo_locker_delivery_locker_id'][0]) ? $data['_woo_locker_delivery_locker_id'][0] : '');
		}
		if ( $column == 'woo_locker_delivery_locker_name' ) {    
			if(isset($data['_woo_locker_delivery_locker_id'][0])){
				require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-service.php' );
				$servive = new WooLockerApiService();
				$lockers = $servive->getLocker($data['_woo_locker_delivery_locker_id'][0]);
			echo (count($lockers) > 0) ? $lockers[0]->getName() : '';
			} else {
				echo '';
			}
		}
	}

	public function hideShipToDifferentAdressCheckbox(){
		?><script type="text/javascript">
			var diffAddress = document.querySelector('#ship-to-different-address');
			diffAddress.outerHTML = '<h3><?php echo __('Détails de livraison', 'woolockerapi'); ?></h3><input type="checkbox" checked="checked" value="1" name="ship_to_different_address" class="woo-locker-hidden" style="display: none;" />';
		</script><?php
		
		/**
		 * Force shipping address on checkout validation
		 */
		function woo_locker_force_posted_data_ship_to_different_address( $posted_data ) {
			$posted_data['ship_to_different_address'] = true;

			return $posted_data;
		}
		add_filter( 'woocommerce_checkout_posted_data', 'woo_locker_force_posted_data_ship_to_different_address' );

		// Note that this overrides the 'Shipping destination' option in the Woo settings
		add_filter( 'woocommerce_ship_to_different_address_checked',  '__return_true' );

		// If you have the possibility of virtual only orders you may want to comment this out
		add_filter( 'woocommerce_cart_needs_shipping_address',  '__return_true' );

		// Order always has shipping (even with local pickup for example)
		add_filter( 'woocommerce_order_needs_shipping_address',  '__return_true' );
	}

	public function checkoutDeliveryDate( $checkout ) {
		$frequence = [
			1,
			7
		];
		if(WC()->cart->get_cart_contents_count() > 0){
			$meta = get_post_meta(array_values(WC()->cart->get_cart())[0]['product_id'], '_woo_recursive_freq', true);
			$frequence = explode( ':', $meta );
		}

		unset(WC()->session->woo_locker_choosen_locker);

		require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-service.php' );

		wp_enqueue_style( 'woo-locker-api', plugins_url('assets/css/woo-locker-api.css', WOO_LOCKER_API_ASSETS_DIR ) , '', '', false);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'fontawesome', plugins_url('assets/fontawesome/css/all.min.css', WOO_LOCKER_API_ASSETS_DIR) , '', '', false);
		wp_enqueue_style( 'fullcalendar', plugins_url('assets/calendar/main.css', WOO_LOCKER_API_ASSETS_DIR) , '', '', false);
		wp_enqueue_script( 'woo-locker-scripts', plugins_url('assets/js/woo-locker-scripts.js', WOO_LOCKER_API_ASSETS_DIR) , '', '', false);
		wp_enqueue_script( 'calendar-main.js', plugins_url('assets/calendar/main.js', WOO_LOCKER_API_ASSETS_DIR), '', '', false);
		wp_enqueue_script( 'calendar-fr-local.js', plugins_url('assets/calendar/locales/fr.js', WOO_LOCKER_API_ASSETS_DIR), '', '', false);

		/* Inline script printed out in the footer */
		add_action('wp_footer', 'woo_locker_choose_availability');
		function woo_locker_choose_availability() {
			?>
				<script language="javascript">
					jQuery(document).ready(function(){
						jQuery("#place_order").attr('disabled', true).css("pointer-events", "none");
					});
					function set_woo_locker_checkout(value){
						var locker = WOO_LOCKERS[value]['lockers'][Math.floor(Math.random() * WOO_LOCKERS[value]['lockers'].length)];
						jQuery.ajax({
							type: "POST",
							url: '<?php echo esc_url( plugins_url( 'ajax-interceptor.php', dirname(__FILE__) ) ); ?>',
							dataType: "json",
							data: {
								action: "set_locker",
								locker: locker
							},
							beforeSend: function(){
								jQuery([document.documentElement, document.body]).animate({
									scrollTop: jQuery("#order_review").offset().top - 100
								}, 200);
							},
							success: function(response){
								jQuery('body').trigger('update_checkout');
								jQuery("#woo_locker_delivery_locker_id").val(response['locker']['woo_locker_choosen_locker_id']);
								jQuery("#woo_locker_delivery_locker_name").val(response['locker']['woo_locker_choosen_locker_name']);
								jQuery("#place_order").css("pointer-events", "").removeAttr('disabled');
							}
						});
					}
				</script>
			<?php
		}

		$wooLockerApiShippingMethod = new WooLockerApiShippingMethod();
		$settings = $wooLockerApiShippingMethod->settings;

		$date = new DateTime(date('Y-m-d') . ' + '.$settings['woo_locker_api_delivery_delay'].' day');
		$initialDateCalendar = $date->format('Y-m-d');

		echo '<div id="woo-locker-info-container"><p id="woo-locker-info">'.__("Veuillez sélectionner une date pour la livraison", "woolockerapi").'</p></div>';

		echo '<div id="woo_locker_delivery_calendar"><div class="woo-locker-spinner"><img src="'.esc_url( plugins_url('assets/img/spinner.gif', WOO_LOCKER_API_ASSETS_DIR) ).'" alt="chargement..." /></div></div>';

		echo '<script language="javascript">jQuery(document).ready(function(){
			var calendarEl = document.getElementById("woo_locker_delivery_calendar");
			var initialDateCalendar = new Date("'.$initialDateCalendar.'");
			var calendar = new FullCalendar.Calendar(calendarEl, {
				initialDate: "' . $initialDateCalendar . '",
				initialView: "dayGridMonth",
				themeSystem: "bootstrap",
				locale: "fr",
				weekends: '.($settings['woo_locker_api_include_weekend'] == 'yes' ? "true" : "false").',
				headerToolbar: {
					start: "title",
					center: "",
					end: "prev,next"
				},
				displayEventTime : false,
				dayCellDidMount: function(cell) {
					if(wooLockerFormatDate(cell.date) < wooLockerFormatDate(initialDateCalendar)){
						cell.el.childNodes[0].childNodes[0].style.opacity = "0.3";
					} else {
						cell.el.style.cursor = "pointer";
					}
				},
				dateClick: function(info) {
					var selectedDate = new Date(info.dateStr);
					if(wooLockerFormatDate(selectedDate) >= wooLockerFormatDate(initialDateCalendar) && !jQuery(info.dayEl).hasClass("woo-locker-selected"))
					{
						jQuery(".fc-day").each(function(index, element) {
							jQuery(element).find(".woo-locker-icon-container").remove();
							jQuery(element).removeClass("woo-locker-selected");
							jQuery(element).find(".fc-daygrid-day-frame").css("top", "");
							jQuery(element).find(".fc-daygrid-day-events").css("min-height", "");
						});
						jQuery(info.dayEl).addClass("woo-locker-selected").prepend("<div class=\'woo-locker-icon-container\'><i class=\'fa fa-check-square\ aria-hidden=\'true\'></i></div>");
						jQuery(info.dayEl).find(".fc-daygrid-day-frame").css("top", "-24px");
						jQuery(info.dayEl).find(".fc-daygrid-day-events").css("min-height", "0.4rem");

						jQuery.ajax({
							type: "GET",
							dataType: "json",
							url: "'.esc_url( plugins_url( 'ajax-interceptor.php', dirname(__FILE__) ) ).'",
							data: {
								action: "get_availabilties",
								date: info.dateStr
							},
							beforeSend: function() {
								jQuery([document.documentElement, document.body]).animate({
									scrollTop: jQuery("#woo-schedule-checker-fieldset").offset().top - 200
								}, 200);
								jQuery("#woo_locker_delivery_calendar").css("pointer-events", "none").css("opacity", "0.5");
								jQuery("#woo-locker-schedule-checker-container").addClass("woo-locker-hidden");
								jQuery("#woo-locker-spinner").removeClass("woo-locker-hidden");
							},
							success: function (response) {
								WOO_LOCKERS = response;
								jQuery("#woo_locker_delivery_date").val(info.dateStr);
								jQuery("#woo-morning-reserved-label").remove();
								jQuery("#woo-evening-reserved-label").remove();
								jQuery("#schedule_woo_locker_evening").removeAttr("disabled").attr("checked", false);
								jQuery("#schedule_woo_locker_morning").removeAttr("disabled").attr("checked", false);
								var disableOrderButton = true;
								if(response.morning.available == false){
									jQuery("#schedule_woo_locker_morning").attr("disabled", true);
									jQuery(".woocommerce-input-wrapper label[for=\'schedule_woo_locker_morning\']").css("cursor", "default").append(\'<span id="woo-morning-reserved-label" class="woo-booked-label"> ( '.__('horaire déjà reservé', 'woolockerapi').' )</span>\');
								} else {
									jQuery(".woocommerce-input-wrapper label[for=\'schedule_woo_locker_morning\']").css("cursor", "");
									disableOrderButton = false;
								}
								if(response.evening.available == false){
									jQuery("#schedule_woo_locker_evening").attr("disabled", true);
									jQuery(".woocommerce-input-wrapper label[for=\'schedule_woo_locker_evening\']").css("cursor", "default").append(\'<span id="woo-evening-reserved-label" class="woo-booked-label"> ( '.__('horaire déjà reservé', 'woolockerapi').' )</span>\');
								} else {
									jQuery(".woocommerce-input-wrapper label[for=\'schedule_woo_locker_evening\']").css("cursor", "")
									disableOrderButton = false;
								}
								if(disableOrderButton = true){
									jQuery("#place_order").attr("disabled", true).css("pointer-events", "none");
								}
								jQuery("#woo_locker_delivery_calendar").css("pointer-events", "").css("opacity", "");
								jQuery("#woo-locker-spinner").addClass("woo-locker-hidden");
								jQuery("#woo-locker-schedule-checker-container").removeClass("woo-locker-hidden");
							}
						});
					}
				}
			});
			calendar.render();
		});</script>';

		woocommerce_form_field( 'woo_locker_delivery_date', array(        
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
		);       
		
		echo '<fieldset id="woo-schedule-checker-fieldset"><div id="woo-locker-spinner" class="woo-locker-spinner woo-locker-hidden"><img src="'.esc_url( plugins_url('assets/img/spinner.gif', WOO_LOCKER_API_ASSETS_DIR) ).'" alt="chargement..." /></div><div id="woo-locker-schedule-checker-container" class="woo-locker-hidden">';
		woocommerce_form_field( 'schedule_woo_locker', array(        
				'type'          => 'radio',
				'class'         => array('input-radio'),       
				'label'         => __('Horaire de livraison', 'woolockerapi'),
				'required'  	=> true,
				'options'		=> WOO_LOCKER_SCHEDULE_OPTIONS,
				'custom_attributes' => [
					'onClick' => 'set_woo_locker_checkout(this.value)'
				]
			), 
			$checkout->get_value( 'schedule_woo_locker' )
		);       
		echo '</div></fieldset>';
	}

}