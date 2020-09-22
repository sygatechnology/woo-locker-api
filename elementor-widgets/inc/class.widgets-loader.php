<?php
/**
 * Widgets loader for Woo Locker API.
 *
 * @package     EW
 * @author      EW
 * @copyright   Copyright (c) 2020, sygatechnology
 */

namespace EW\inc;

use Elementor\Plugin;

defined( 'ABSPATH' ) or exit;

/**
 * Set up Widgets Loader class
 */
class Widgets_Loader {

	/**
	 * Instance of Widgets_Loader.
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Get instance of Widgets_Loader
	 *
	 * @return Widgets_Loader
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Setup actions and filters.
	 *
	 */
	private function __construct() {
		// Register category.
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );

		// Register widgets.
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

		// Add svg support.
		add_filter( 'upload_mimes', [ $this, 'syga_svg_mime_types' ] );

		// Refresh the cart fragments.
		if ( class_exists( 'woocommerce' ) ) {
			//add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'init_cart' ], 10, 0 );
			//add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'wc_refresh_mini_cart_count' ] );
		}
	}

	/**
	 * Provide the SVG support for Retina Logo widget.
	 *
	 * @param array $mimes which return mime type.
	 *
	 * @return $mimes.
	 */
	public function syga_svg_mime_types( $mimes ) {
		// New allowed mime types.
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Register Category
	 *
	 * @param object $this_cat class.
	 */
	public function register_widget_category( $this_cat ) {
		$category = __( 'WooLocker API', 'woolockerapi' );

		$this_cat->add_category(
			'woo-locker-widgets',
			[
				'title' => $category,
				'icon'  => 'eicon-font',
			]
		);

		return $this_cat;
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @access public
	 */
	public function register_widgets() {
		// Its is now safe to include Widgets files.
		$this->include_widgets_files();
		// Register Widgets.
		if ( class_exists( 'woocommerce' ) ) {
			Plugin::instance()->widgets_manager->register_widget_type( new Widgets\LockerCalendarWidget() );
		}

	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @access public
	 */
	public function include_widgets_files() {
		$js_files    = $this->get_widget_script();
		$widget_list = $this->get_widget_list();

		if ( ! empty( $widget_list ) ) {
			foreach ( $widget_list as $handle => $data ) {
				require_once EW_DIR . '/inc/widgets/class.' . $data . '-widget.php';
			}
		}

		if ( ! empty( $js_files ) ) {
			foreach ( $js_files as $handle => $data ) {
				wp_register_script( $handle, EW_URL . $data['path'], $data['dep'], EW_VER, $data['in_footer'] );
			}
		}

		// Emqueue the widgets style.
		wp_enqueue_style( 'ew-widgets-style', EW_URL . '/inc/css/frontend.css', [], EW_VER );
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 */
	public static function get_widget_script() {
		$js_files = [
			'ew-frontend-js' => [
				'path'      => '/inc/js/frontend.js',
				'dep'       => [ 'jquery' ],
				'in_footer' => true
			],
		];

		return $js_files;
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 */
	public static function get_widget_list() {
		$widget_list = [
			'locker-calendar'
		];

		return $widget_list;
	}
	
}

/**
 * Initiate the class.
 */
Widgets_Loader::instance();
