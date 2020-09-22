<?php

class WooLockerApi {

	static $initiated = false;

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function pluginActivation() {
		if ( version_compare( $GLOBALS['wp_version'], WOO_LOCKER_API__MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'woolockerapi' );
			
			$message = '<strong>'.sprintf(esc_html__( 'Woo Locker Pickup Api %s exige WordPress %s ou plus.' , 'woolockerapi'), WOO_LOCKER_API_VERSION, WOO_LOCKER_API__MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'woolockerapi'), 'https://codex.wordpress.org/Upgrading_WordPress');

			WooLockerApi::bailOnActivation( $message );
		} elseif ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ) ) {
			self::addOptions();
		}
	}

	private static function addOptions(){
		add_option( 'activated_woo_locker_pickup_plugun', true );
	}

	private static function deleteOptions(){
		delete_option( 'activated_woo_locker_pickup_plugun' );
	}

	private static function bailOnActivation( $message, $deactivate = true ) {
?>
<!doctype html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<style>
* {
	text-align: center;
	margin: 0;
	padding: 0;
	font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
}
p {
	margin-top: 1em;
	font-size: 18px;
}
</style>
</head>
<body>
<p><?php echo esc_html( $message ); ?></p>
</body>
</html>
<?php
		if ( $deactivate ) {
			$plugins = get_option( 'active_plugins' );
			$woolockerapi = plugin_basename( WOO_LOCKER_API__PLUGIN_DIR . 'woo-locker-api.php' );
			$update  = false;
			foreach ( $plugins as $i => $plugin ) {
				if ( $plugin === $woolockerapi ) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ( $update ) {
				update_option( 'active_plugins', array_filter( $plugins ) );
			}
		}
		exit;
	}

	public static function init() {
		if ( ! self::$initiated ) {

			self::initHooks();

			self::initShippingMethodHooks();

			self::initOrderDeliveryHooks();

			require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-short-code.php' );

			WooLockerApiShotCode::addPickupLockerCalendarShortCode();

			WooLockerApiShotCode::addPickupLockerFormShortCode();

			/*if(is_plugin_active( 'sumosubscriptions/sumosubscriptions.php' )){

				require_once( WOO_LOCKER_API__PLUGIN_DIR . 'settings/class.sumo-subscription-admin-product-settings.php' );
				WOOLOKER_SUMOSubscriptions_Product_Settings::initialize();

			} elseif ( is_admin() && current_user_can('manage_options') ) {
				// it's not active. Notify the user, perhaps displaying a notice.
				add_action( 'admin_notices', function(){
					echo '<div id="message" class="error notice is-dismissible"><p>SUMO Subscriptions plugin <b>is not active!</b></p></div>';
				} );
			}*/

			//require_once( WOO_LOCKER_API__PLUGIN_DIR . 'elementor-widgets/widgets-manager.php' );

			self::$initiated = true;
		}
	}

	private static function initHooks() {
		require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-product.php' );
		//add_action( 'woocommerce_product_options_general_product_data', array( 'WooLockerProduct', 'registerCustomFields' ) ); 
		add_action( 'woocommerce_process_product_meta', array( 'WooLockerProduct', 'saveCustomMetaFields' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( 'WooLockerProduct', 'minAndMaxQuantityValidationCart'), 10, 3 );
	}

	private static function initShippingMethodHooks(){
		require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-shipping-utilities.php' );
		WooLockerApiShippingUtilities::registerShippingMethod();
		add_action( 'woocommerce_review_order_before_cart_contents', array( 'WooLockerApiShippingUtilities', 'wooLockerApiValidateOrder' ) , 10 );
		add_action( 'woocommerce_after_checkout_validation', 'woolockerapi_validate_order' , 10 );
	}

	private static function initOrderDeliveryHooks(){

		require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-order-delivery.php' );

		add_action('wp_footer', array( 'WooLockerApiOrderDelivery', 'hideShipToDifferentAdressCheckbox'));
		
		add_filter('woocommerce_shipping_fields', array( 'WooLockerApiOrderDelivery', 'customShippingFields' ));

		//add_action('woocommerce_after_checkout_shipping_form', array( 'WooLockerApiOrderDelivery', 'checkoutDeliveryDate' ));

		add_action( 'woocommerce_review_order_after_shipping', 'woo_locker_shipping_details', 10, 2 );
 
		function woo_locker_shipping_details() {
			$packages = WC()->shipping->get_packages();
			$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
			if( is_array( $chosen_methods ) && in_array( SHIPPING_METHOD_ID, $chosen_methods ) ) {
				foreach ( $packages as $i => $package ) {
					if ( $chosen_methods[ $i ] != SHIPPING_METHOD_ID ) {
						continue;
					}
					if(isset( WC()->session->woo_locker_choosen_locker )) {
						?>
						<tr class="woocommerce-shipping-totals shipping-locker">
							<th><?php echo __('Destination de la livraison', 'woolockerapi'); ?></th>
							<td data-title="casier-number"><?php echo WC()->session->woo_locker_choosen_locker['woo_locker_choosen_locker_name']; ?></td>
						</tr>
					<?php
					}
				}
			}
		}

		add_filter( 'manage_edit-shop_order_columns', array( 'WooLockerApiOrderDelivery', 'orderCustomAdminColumns' ), 20, 1 );

		add_filter('woocommerce_email_order_meta_keys', array( 'WooLockerApiOrderDelivery', 'orderDeliveryEmailNotication' ), 10, 1);

		add_action('woocommerce_checkout_update_order_meta', array( 'WooLockerApiOrderDelivery', 'checkoutFieldUpdateOrderMeta' ));

		add_action( 'manage_shop_order_posts_custom_column', array( 'WooLockerApiOrderDelivery', 'customColumnValue' ), 99 );

	}

	public static function view( $name, array $args = array() ) {
		$args = apply_filters( 'woolockerapi_view_arguments', $args, $name );
		
		foreach ( $args AS $key => $val ) {
			$$key = $val;
		}
		
		load_plugin_textdomain( 'woolockerapi' );

		$file = WOO_LOCKER_API__PLUGIN_DIR . 'views/'. $name . '.php';

		include( $file );
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function pluginDeactivation( ) {
		
		self::deleteOptions();
		
		// Remove any scheduled cron jobs.
		/*$woolockerapi_cron_events = array(
			'woolockerapi_schedule_cron_recheck',
			'woolockerapi_scheduled_delete',
		);
		
		foreach ( $woolockerapi_cron_events as $woolockerapi_cron_event ) {
			$timestamp = wp_next_scheduled( $woolockerapi_cron_event );
			
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $woolockerapi_cron_event );
			}
		}*/
	}
	
	/**
	 * Essentially a copy of WP's build_query but one that doesn't expect pre-urlencoded values.
	 *
	 * @param array $args An array of key => value pairs
	 * @return string A string ready for use as a URL query string.
	 */
	public static function build_query( $args ) {
		return _http_build_query( $args, '', '&' );
	}

	/**
	 * Log debugging info to the error log.
	 *
	 * Enabled when WP_DEBUG_LOG is enabled (and WP_DEBUG, since according to
	 * core, "WP_DEBUG_DISPLAY and WP_DEBUG_LOG perform no function unless
	 * WP_DEBUG is true), but can be disabled via the woolockerapi_debug_log filter.
	 *
	 * @param mixed $woolockerapi_debug The data to log.
	 */
	public static function log( $woolockerapi_debug ) {
		if ( apply_filters( 'woolockerapi_debug_log', defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && defined( 'WOO_LOCKER_API_DEBUG' ) && WOO_LOCKER_API_DEBUG ) ) {
			error_log( print_r( compact( 'woolockerapi_debug' ), true ) );
		}
	}

	public static function cronRecheck() {
		
	}

	public static function _cmp_time( $a, $b ) {
		return $a['time'] > $b['time'] ? -1 : 1;
	}

	public static function _get_microtime() {
		$mtime = explode( ' ', microtime() );
		return $mtime[1] + $mtime[0];
	}

	/**
	 * Make a POST request to the WooLockerApi API.
	 *
	 * @param string $request The body of the request.
	 * @param string $path The path for the request.
	 * @param string $ip The specific IP address to hit.
	 * @return array A two-member array consisting of the headers and the response body, both empty in the case of a failure.
	 */
	public static function http_post( $request, $path, $ip=null ) {

		$woolockerapi_ua = sprintf( 'WordPress/%s | WooLockerApi/%s', $GLOBALS['wp_version'], constant( 'WOO_LOCKER_API_VERSION' ) );
		$woolockerapi_ua = apply_filters( 'woolockerapi_ua', $woolockerapi_ua );

		$content_length = strlen( $request );

		$api_key   = self::get_api_key();
		$host      = self::API_HOST;

		if ( !empty( $api_key ) )
			$host = $api_key.'.'.$host;

		$http_host = $host;
		// use a specific IP if provided
		// needed by WooLockerApi_Admin::check_server_connectivity()
		if ( $ip && long2ip( ip2long( $ip ) ) ) {
			$http_host = $ip;
		}

		$http_args = array(
			'body' => $request,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
				'Host' => $host,
				'User-Agent' => $woolockerapi_ua,
			),
			'httpversion' => '1.0',
			'timeout' => 15
		);

		$woolockerapi_url = $http_woolockerapi_url = "http://{$http_host}/1.1/{$path}";

		/**
		 * Try SSL first; if that fails, try without it and don't try it again for a while.
		 */

		$ssl = $ssl_failed = false;

		// Check if SSL requests were disabled fewer than X hours ago.
		$ssl_disabled = get_option( 'woolockerapi_ssl_disabled' );

		if ( $ssl_disabled && $ssl_disabled < ( time() - 60 * 60 * 24 ) ) { // 24 hours
			$ssl_disabled = false;
			delete_option( 'woolockerapi_ssl_disabled' );
		}
		else if ( $ssl_disabled ) {
			do_action( 'woolockerapi_ssl_disabled' );
		}

		if ( ! $ssl_disabled && ( $ssl = wp_http_supports( array( 'ssl' ) ) ) ) {
			$woolockerapi_url = set_url_scheme( $woolockerapi_url, 'https' );

			do_action( 'woolockerapi_https_request_pre' );
		}

		$response = wp_remote_post( $woolockerapi_url, $http_args );

		WooLockerApi::log( compact( 'woolockerapi_url', 'http_args', 'response' ) );

		if ( $ssl && is_wp_error( $response ) ) {
			do_action( 'woolockerapi_https_request_failure', $response );

			// Intermittent connection problems may cause the first HTTPS
			// request to fail and subsequent HTTP requests to succeed randomly.
			// Retry the HTTPS request once before disabling SSL for a time.
			$response = wp_remote_post( $woolockerapi_url, $http_args );
			
			WooLockerApi::log( compact( 'woolockerapi_url', 'http_args', 'response' ) );

			if ( is_wp_error( $response ) ) {
				$ssl_failed = true;

				do_action( 'woolockerapi_https_request_failure', $response );

				do_action( 'woolockerapi_http_request_pre' );

				// Try the request again without SSL.
				$response = wp_remote_post( $http_woolockerapi_url, $http_args );

				WooLockerApi::log( compact( 'http_woolockerapi_url', 'http_args', 'response' ) );
			}
		}

		if ( is_wp_error( $response ) ) {
			do_action( 'woolockerapi_request_failure', $response );

			return array( '', '' );
		}

		if ( $ssl_failed ) {
			// The request failed when using SSL but succeeded without it. Disable SSL for future requests.
			update_option( 'woolockerapi_ssl_disabled', time() );
			
			do_action( 'woolockerapi_https_disabled' );
		}
		
		$simplified_response = array( $response['headers'], $response['body'] );
		
		self::update_alert( $simplified_response );

		return $simplified_response;
	}

	public static function load_form_js() {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return;
		}

		if ( ! self::get_api_key() ) {
			return;
		}

		wp_register_script( 'woolockerapi-form', plugin_dir_url( __FILE__ ) . '_inc/form.js', array(), WOO_LOCKER_API_VERSION, true );
		wp_enqueue_script( 'woolockerapi-form' );
	}
	
	/**
	 * Mark form.js as async. Because nothing depends on it, it can run at any time
	 * after it's loaded, and the browser won't have to wait for it to load to continue
	 * parsing the rest of the page.
	 */
	public static function set_form_js_async( $tag, $handle, $src ) {
		if ( 'woolockerapi-form' !== $handle ) {
			return $tag;
		}
		
		return preg_replace( '/^<script /i', '<script async="async" ', $tag );
	}
	
}
