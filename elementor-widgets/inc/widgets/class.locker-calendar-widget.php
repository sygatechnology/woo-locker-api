<?php
/**
 * Elementor Classes.
 *
 * @package WooLocker API Elementor Widget
 */

namespace EW\inc\widgets;

use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;
use Elementor\Scheme_Color;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Elementor Locker Calendar Widget
 *
 * Elementor widget for locker calendar pickup.
 *
 */
class LockerCalendarWidget extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'locker-calendar';
	}
	/**
	 * Retrieve the widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Calendrier de reservation', 'woolockerapi' );
	}
	/**
	 * Retrieve the widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fa fa-calendar';
	}
	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'ew-widgets' ];
	}
	/**
	 * Register Calendar pickup controls.
	 *
	 * @access protected
	 */
	protected function _register_controls() { //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->register_content_calendar_pickup_controls();
	}
	/**
	 * Register Calendar pickup General Controls.
	 *
	 * @access protected
	 */
	protected function register_content_calendar_pickup_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Calendrier de reservation', 'woolockerapi' )
			]
		);

		$this->add_control(
			'shortcode',
			[
				'label'   => __( 'Copyright Text', 'woolockerapi' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Copyright © [hfe_current_year] [hfe_site_title] | Powered by [hfe_site_title]', 'woolockerapi' ),
			]
		);

		/*$this->add_control(
			'link',
			[
				'label'       => __( 'Link', 'woolockerapi' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'woolockerapi' ),
			]
		);
		*/

		$this->add_responsive_control(
			'align',
			[
				'label'     => __( 'Alignement', 'woolockerapi' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Gauche', 'woolockerapi' ),
						'icon'  => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Centre', 'woolockerapi' ),
						'icon'  => 'fa fa-align-center',
					],
					'right'  => [
						'title' => __( 'Droite', 'woolockerapi' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .hfe-copyright-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Couleur du texte', 'woolockerapi' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
				'selectors' => [
					// Stronger selector to avoid section style from overwriting.
					'{{WRAPPER}} .hfe-copyright-wrapper a, {{WRAPPER}} .hfe-copyright-wrapper' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'caption_typography',
				'selector' => '{{WRAPPER}} .hfe-copyright-wrapper, {{WRAPPER}} .hfe-copyright-wrapper a',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_3,
			]
		);
	}

	/**
	 * Render Calendar Pickup output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();


		/*$link    = isset( $settings['link']['url'] ) ? $settings['link']['url'] : '';

		if ( ! empty( $settings['link']['nofollow'] ) ) {
			$this->add_render_attribute( 'link', 'rel', 'nofollow' );
		}
		if ( ! empty( $settings['link']['is_external'] ) ) {
			$this->add_render_attribute( 'link', 'target', '_blank' );
		}*/

		$copy_right_shortcode = do_shortcode( shortcode_unautop( $settings['shortcode'] ) ); ?>
		<div class="hfe-copyright-wrapper">
			<span><?php echo wp_kses_post( $copy_right_shortcode ); ?></span>
		</div>
		<?php
	}

	/**
	 * Render shortcode widget as plain content.
	 *
	 * Override the default behavior by printing the shortcode instead of rendering it.
	 *
	 * @access public
	 */
	public function render_plain_content() {
		// In plain mode, render without shortcode.
		echo esc_attr( $this->get_settings( 'shortcode' ) );
	}

	/**
	 * Render shortcode widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @access protected
	 */
	protected function content_template() {}

	/**
	 * Render shortcode output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @access protected
	 */
	protected function _content_template() {
		$this->content_template();
	}
}
