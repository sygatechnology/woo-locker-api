<?php 

require_once( WOO_LOCKER_API_INTERFACES_DIR . 'interface.woo-locker-api-service.php' );

/**
 * Cleveron S2 Class Web Service
 */
class WooLockerApiService implements WooLockerApiServiceInterface
{

	const API_HOST = 'rest.cleveron.com/s2';
	const API_PORT = 80;
	const API_KEY = 'api-key';
	const API_USER_TOKEN = 'api-user-token';

	public function get(){
		return 'Yes';
	}

	public function getLockers(){
		require_once( WOO_LOCKER_API_MODELS_DIR . 'class.woo-locker-model.php' );
		$args = array(
			'post_type' => WOO_LOCKER_API_POST_TYPE,
			'posts_per_page' => -1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts' => true,
			'meta_query' => array(
				array(
					'key' => 'woo_locker_id',
					'value' => '',
					'compare' => '!='
				)
			)
		);
		$lockers = [];
		foreach(get_posts($args) as $locker){
			$terms = wp_get_post_terms( $locker->ID, array_keys(WOO_LOCKER_API_POST_TYPE_TAXONOMIES), array( 'fields' => 'all' ) );
			$locker_id = get_post_meta($locker->ID, 'woo_locker_id', true);
			$locker_address = get_post_meta($locker->ID, 'woo_locker_localisation', true);
			$lockerModel = new WooLockerModel(
				[
					'id' => $locker_id,
					'name' => $locker->post_title,
					'address' => $locker_address,
					'terms' => $terms
				]
			);
			$lockers[$locker_id] = $lockerModel;
		}
		return $lockers;
	}

	public function getLocker($locker_id){
		require_once( WOO_LOCKER_API_MODELS_DIR . 'class.woo-locker-model.php' );
		$args = array(
			'post_type' => WOO_LOCKER_API_POST_TYPE,
			'posts_per_page' => -1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts' => true,
			'meta_query' => array(
				array(
					'key' => 'woo_locker_id',
					'value' => $locker_id,
					'compare' => '='
				)
			)
		);
		$lockers = [];
		foreach(get_posts($args) as $locker){
			$terms = wp_get_post_terms( $locker->ID, array_keys(WOO_LOCKER_API_POST_TYPE_TAXONOMIES), array( 'fields' => 'all' ) );
			$locker_id = get_post_meta($locker->ID, 'woo_locker_id', true);
			$locker_address = get_post_meta($locker->ID, 'woo_locker_localisation', true);
			$lockerModel = new WooLockerModel(
				[
					'id' => $locker_id,
					'name' => $locker->post_title,
					'address' => $locker_address,
					'terms' => $terms
				]
			);
			$lockers[] = $lockerModel;
		}
		return $lockers;
	}

	public function getAvailabilities($date)
	{
		setlocale(LC_TIME, "fr_FR");
		$stringDate = strtolower( strftime("%A %d %B %G", strtotime($date)) );
		$args = array(
			'post_type' => WOO_LOCKER_API_SUBSCRIPTION_POST_TYPE,
			'posts_per_page' => -1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts' => true,
			'post_status' => 'publish'
		);
		$subscriptions = [];
		foreach(get_posts($args) as $subscription){
			$order_id = get_post_meta( $subscription->ID, WOO_LOCKER_API_SUBSCRIPTION_ORDER_META, true );
			$subscription->woo_delivery_date = get_post_meta( $order_id, '_woo_locker_delivery_date', true );
			if($date == $subscription->woo_delivery_date) {
				$subscription->{WOO_LOCKER_API_SUBSCRIPTION_NEXT_PAYEMENT_META} = get_post_meta( $subscription->ID, 'sumo_get_next_payment_date', true );
				$subscription->woo_locker_id = get_post_meta( $order_id, '_woo_locker_delivery_locker_id', true );
				$subscription->woo_schedule = get_post_meta( $order_id, '_woo_locker_delivery_schedule', true );
				if(!isset($subscriptions[ $order_id ])) $subscriptions[ $order_id ] = $subscription;
			}
		}

		$availabilities = [];
		$lockers = $this->getLockers();
		foreach($lockers as $locker_id => $locker){
			if(count($subscriptions) > 0){
				foreach($subscriptions as $order_id => $subscription){
					if($subscription->woo_locker_id == $locker_id){
						$lockerShedules = $locker->getShedules();
						$shedules = WOO_LOCKER_SCHEDULE_OPTIONS;
						unset($shedules[$subscription->woo_schedule]);
						if(is_null($lockerShedules)){
							$locker->setShedules(
								[
									$subscription->woo_schedule => false,
									array_keys($shedules)[0] => true
								]
							);
						} else {
							$initial = $lockerShedules;
							foreach($lockerShedules as $shedule => $value){
								if($value && $shedule == $subscription->woo_schedule) {
									$initial[$shedule] = false;
								}
							}
							$locker->setShedules($initial);
						}
					} else {
						$shedules = [];
						foreach(WOO_LOCKER_SCHEDULE_OPTIONS as $shedule => $name){
							$shedules[$shedule] = true;
						}
						$locker->setShedules($shedules);
					}
				}
			} else {
				$shedules = [];
				foreach(WOO_LOCKER_SCHEDULE_OPTIONS as $shedule => $name){
					$shedules[$shedule] = true;
				}
				$locker->setShedules($shedules);
			}
		}
		
		$availabilities = [];
		foreach(WOO_LOCKER_SCHEDULE_OPTIONS as $shedule => $name){
			$availabilities[$shedule] = [
				"available" => false,
				"lockers" => [],
				"date" => $stringDate
			];
		}
		foreach( $lockers as $locker_id => $locker ) {
			foreach($locker->getShedules() as $shedule => $value){
				if($value) {
					$availabilities[$shedule]["available"] = true;
					$availabilities[$shedule]["lockers"][] = $locker;
				}
			}
		}

		return $availabilities;
	}

	public function getOrdersByLockers(){
		$lockerIds = [];
		foreach($this->getLockers() as $locker){
			$lockerIds[] = get_post_meta($locker->ID, 'woo_locker_id', true);
		}
		$args = array(
			'post_type' => 'shop_order',
			'posts_per_page' => -1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts' => true,
			'post_status' => 'wc-on-hold', //wc-completed
			'meta_query' => array(
				array(
					'key' => '_woo_locker_delivery_locker_id',
					'value' => $lockerIds,
					'compare' => 'IN'
				)
			)
		);
		$orders = [];
		/*foreach(get_posts($args) as $order){
			$orders[get_post_meta($order->ID, '_woo_locker_delivery_locker_id', true)] = [
				get_post_meta($order->ID, '_woo_locker_delivery_schedule', true)
			];
		}*/
		return $orders;
	}
	

	public function setLockerSession($locker) {
        $locker_session['woo_locker_choosen_locker_name'] = $locker['name'];
		$locker_session['woo_locker_choosen_locker_id'] = $locker['id'];
		$locker_session['woo_locker_choosen_locker_address'] = $locker['address'];
		WC()->session->woo_locker_choosen_locker = $locker_session;
        return [
			"message" => 'ok',
			"code" => 200,
			"locker" => $locker_session
		];
	}

	public function saveOrder($order)
	{
		# code...
	}

	public function updateOrder($order)
	{
		# code...
	}

	public function cancelOrder($order)
	{
		# code...
	}

}