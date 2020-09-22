<?php

function woo_locker_pickup_shipping_method_init(){

	if (!class_exists('WooLockerApiShippingMethod')) {

		class WooLockerApiShippingMethod extends WC_Shipping_Method
		{
			public function __construct()
			{
				$this->id = SHIPPING_METHOD_ID;
				$this->method_title = __('Livraison aux casiers de distribution', 'woolockerapi');
				$this->method_description = __('Méthode de livraison aux casiers de distribution liée à un API', 'woolockerapi');
				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
					'settings'
				);
				// Contreis availability
				//$this->availability = 'including';
				$this->init();
				$this->settings['enabled'] = 'yes';
				$this->enabled = 'yes';
			}
			
			/**
			 * Return the name of the option in the WP DB.
			 *
			 * @since 2.6.0
			 * @return string
			 */
			public function get_option_key() {
				return $this->plugin_id . 'woo_locker_pickup_settings';
			}

			/**
			 * Init function.
			 */
			public function init() {

				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();

				// Define user set variables.
				$this->title        = $this->get_option( 'title' );
				$this->codes        = $this->get_option( 'codes' );
				$this->availability = $this->get_option( 'availability' );
				$this->countries    = $this->get_option( 'countries' );

				// Actions.
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}
			
			/**
			 * Calculate shipping.
			 *
			 * @param array $package Package information.
			 */
			public function calculate_shipping( $package = array() ) {
				$rate = array(
					'id'      => $this->id,
					'label'   => $this->title,
					'package' => $package,
				);
				$this->add_rate( $rate );
			}
			
			/**
			 * Initialize form fields.
			 */
			public function init_form_fields() {
				$this->form_fields = array(
					'title'        => array(
						'title'       => __( 'Title', 'woocommerce' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
						'default'     => __( 'Locker pickup', 'woolockerapi' ),
						'desc_tip'    => true,
					),
					'codes'        => array(
						'title'       => __( 'Allowed ZIP/post codes', 'woocommerce' ),
						'type'        => 'text',
						'desc_tip'    => __( 'What ZIP/post codes are available for local pickup?', 'woocommerce' ),
						'default'     => '',
						'description' => __( 'Separate codes with a comma. Accepts wildcards, e.g. <code>P*</code> will match a postcode of PE30. Also accepts a pattern, e.g. <code>NG1___</code> would match NG1 1AA but not NG10 1AA', 'woocommerce' ),
						'placeholder' => 'e.g. 12345, 56789',
					),
					'availability' => array(
						'title'   => __( 'Method availability', 'woocommerce' ),
						'type'    => 'select',
						'default' => 'all',
						'class'   => 'availability wc-enhanced-select',
						'options' => array(
							'all'      => __( 'All allowed countries', 'woocommerce' ),
							'specific' => __( 'Specific countries', 'woocommerce' ),
						),
					),
					'countries'    => array(
						'title'             => __( 'Specific countries', 'woocommerce' ),
						'type'              => 'multiselect',
						'class'             => 'wc-enhanced-select',
						'css'               => 'width: 400px;',
						'default'           => '',
						'options'           => WC()->countries->get_shipping_countries(),
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select some countries', 'woocommerce' ),
						),
					),
					'woo_locker_api_host'        => array(
						'title'       => __( 'Hôte de l\'API', 'woolockerapi' ),
						'type'        => 'text',
						'description' => __( 'URL distant de l\'API pour intéragir avec les casiers de distribution. e.g. <code>https://api.example.com</code>', 'woolockerapi' ),
						'placeholder' => 'e.g. https://api.example.com/lockers'
					),
					'woo_locker_api_port'        => array(
						'title'       => __( 'Port l\'API', 'woolockerapi' ),
						'type'        => 'number',
						'default'	  => 80,
						'description' => __( 'Numéro de port associé à l\'hôte de l\'API. e.g. <code>8080</code>. S\'il est vide, le port utilé par défaut sera <code>80</code>', 'woolockerapi' ),
						'placeholder' => 'e.g. 8888'
					),
					'woo_locker_api_key'        => array(
						'title'       => __( 'Clé secrète pour l\'API', 'woolockerapi' ),
						'type'        => 'text',
						'description' => __( 'Parfois, il est impératif d\'utiliser une clé pour se connecter à un API. Demandez à votre fournisseur s\'il y en a.', 'woolockerapi' )
					),
					'woo_locker_api_access_token'        => array(
						'title'       => __( 'Jeton d\'accès pour l\'API', 'woolockerapi' ),
						'type'        => 'text',
						'description' => __( 'Parfois, il est impératif d\'utiliser un jeton d\'accès combiné avec la clé secrète pour se connecter à un API. Demandez à votre fournisseur s\'il y en a.', 'woolockerapi' )
					),
					'woo_locker_api_include_weekend' => array(
						'title'   => __( 'Inclure les weekend', 'woolockerapi' ),
						'type'    => 'checkbox',
						'label'   => __( 'Inclure ou non les weekend parmis les jours de distribution.', 'woolockerapi' ),
						'default' => 'no'
					),
					'woo_locker_api_delivery_delay' => array(
						'title'   => __( 'Délai de livraison', 'woolockerapi' ),
						'type'    => 'number',
						'description'   => __( 'Délai en jour pour la livraison de la commande.', 'woolockerapi' ),
						'default' => 0
					),
					'woo_add_to_cart_redirection_page' => array(
						'title'   => __( 'Page de sélection du point de retrait', 'woolockerapi' ),
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'default' => '',
						'options' => $this->getPages(),
						'custom_attributes' => array(
							'data-placeholder' => __( 'Sélectionnez une page', 'woolockerapi' ),
						)
					)
				);
			}

			private function getPages() {
				$pages = [
					'' => ''
				];
				foreach(get_pages() as $page){
					$pages[$page->ID] = $page->post_title;
				}
				return $pages;
			}

			/**
			 * Get postcodes for this method.
			 *
			 * @return array
			 */
			public function get_valid_postcodes() {
				$codes = array();

				if ( '' !== $this->codes ) {
					foreach ( explode( ',', $this->codes ) as $code ) {
						$codes[] = strtoupper( trim( $code ) );
					}
				}

				return $codes;
			}

			/**
			 * See if a given postcode matches valid postcodes.
			 *
			 * @param  string $postcode Postcode to check.
			 * @param  string $country code Code of the country to check postcode against.
			 * @return boolean
			 */
			public function is_valid_postcode( $postcode, $country ) {
				$codes              = $this->get_valid_postcodes();
				$postcode           = $this->clean( $postcode );
				$formatted_postcode = wc_format_postcode( $postcode, $country );

				if ( in_array( $postcode, $codes, true ) || in_array( $formatted_postcode, $codes, true ) ) {
					return true;
				}

				// Pattern matching.
				foreach ( $codes as $c ) {
					$pattern = '/^' . str_replace( '_', '[0-9a-zA-Z]', preg_quote( $c ) ) . '$/i';
					if ( preg_match( $pattern, $postcode ) ) {
						return true;
					}
				}

				// Wildcard search.
				$wildcard_postcode = $formatted_postcode . '*';
				$postcode_length   = strlen( $formatted_postcode );

				for ( $i = 0; $i < $postcode_length; $i++ ) {
					if ( in_array( $wildcard_postcode, $codes, true ) ) {
						return true;
					}
					$wildcard_postcode = substr( $wildcard_postcode, 0, -2 ) . '*';
				}

				return false;
			}
			
			
			public function is_available( $package ) {
				$is_available = 'yes' === $this->enabled;

				if ( $is_available && $this->get_valid_postcodes() ) {
					$is_available = $this->is_valid_postcode( $package['destination']['postcode'], $package['destination']['country'] );
				}

				if ( $is_available ) {
					if ( 'specific' === $this->availability ) {
						$ship_to_countries = $this->countries;
					} else {
						$ship_to_countries = array_keys( WC()->countries->get_shipping_countries() );
					}
					if ( is_array( $ship_to_countries ) && ! in_array( $package['destination']['country'], $ship_to_countries, true ) ) {
						$is_available = false;
					}
				}

				return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
			}
			
			/**
			 * Clean function.
			 *
			 * @access public
			 * @param mixed $code Code.
			 * @return string
			 */
			public function clean( $code ) {
				return str_replace( '-', '', sanitize_title( $code ) ) . ( strstr( $code, '*' ) ? '*' : '' );
			}

		}
	}

}

add_action( 'woocommerce_shipping_init', 'woo_locker_pickup_shipping_method_init', 99 );

function push_woo_locker_pickup_shipping_method( $methods ) {
	$methods[ SHIPPING_METHOD_ID ] = 'WooLockerApiShippingMethod';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'push_woo_locker_pickup_shipping_method' );

