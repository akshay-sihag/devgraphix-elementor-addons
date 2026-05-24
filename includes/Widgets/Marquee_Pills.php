<?php
/**
 * Marquee Pills widget.
 *
 * A continuously-scrolling, seamless row of icon + text pills (add as many as
 * you like). The motion is a smooth, never-stopping linear loop — not a
 * stepped carousel.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Marquee_Pills
 */
class Marquee_Pills extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-marquee-pills';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Marquee Pills', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-marquee';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'marquee', 'ticker', 'scroll', 'pills', 'badges', 'loop', 'slider' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-marquee-pills' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-marquee-pills' );
	}

	/**
	 * Default pills (a sample "trust strip" with placeholder copy).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function default_pills() {
		$items = array(
			array( 'Lorem ipsum', 'fas fa-snowflake' ),
			array( 'Dolor sit amet', 'fas fa-flag-usa' ),
			array( 'Consectetur', 'fas fa-shield-alt' ),
			array( 'Adipiscing elit', 'fas fa-lock' ),
			array( 'Sed do eiusmod', 'fas fa-clock' ),
			array( 'Tempor incididunt', 'fas fa-box' ),
			array( 'Ut labore', 'fas fa-truck' ),
			array( 'Magna aliqua', 'fas fa-user-md' ),
		);

		$pills = array();
		foreach ( $items as $item ) {
			$pills[] = array(
				'pill_text' => $item[0],
				'pill_icon' => array(
					'value'   => $item[1],
					'library' => 'fa-solid',
				),
			);
		}

		return $pills;
	}

	// =======================================================================
	// CONTROLS
	// =======================================================================

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {

		// ---- Pills --------------------------------------------------------
		$this->start_controls_section(
			'section_pills',
			array(
				'label' => esc_html__( 'Pills', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$pill = new Repeater();

		$pill->add_control(
			'pill_icon',
			array(
				'label'   => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
			)
		);

		$pill->add_control(
			'pill_text',
			array(
				'label'   => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Lorem ipsum', 'devgraphix-elementor-addons' ),
			)
		);

		$pill->add_control(
			'pill_link',
			array(
				'label'       => esc_html__( 'Link', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
			)
		);

		$this->add_control(
			'pills',
			array(
				'label'       => esc_html__( 'Pills', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $pill->get_controls(),
				'default'     => $this->default_pills(),
				'title_field' => '{{{ pill_text }}}',
			)
		);

		$this->end_controls_section();

		// ---- Motion -------------------------------------------------------
		$this->start_controls_section(
			'section_motion',
			array(
				'label' => esc_html__( 'Motion', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'       => esc_html__( 'Speed (seconds per set)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 's' ),
				'range'       => array( 's' => array( 'min' => 5, 'max' => 120, 'step' => 1 ) ),
				'default'     => array( 'unit' => 's', 'size' => 40 ),
				'description' => esc_html__( 'Higher = slower. Time for one set of pills to scroll past — the speed stays constant however many pills you add.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'   => esc_html__( 'Direction', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => array(
					'left'  => esc_html__( 'Left', 'devgraphix-elementor-addons' ),
					'right' => esc_html__( 'Right', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Gap Between Pills', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 16 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-pills' => '--dgx-pills-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => esc_html__( 'Pause on Hover', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'edge_fade',
			array(
				'label'        => esc_html__( 'Fade Edges', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	/**
	 * Style controls (defaults mirror the rendered design).
	 *
	 * @return void
	 */
	private function register_style_controls() {

		// ---- Pill ---------------------------------------------------------
		$this->start_controls_section(
			'style_pill',
			array(
				'label' => esc_html__( 'Pill', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'pill_bg',
			array(
				'label'     => esc_html__( 'Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.7)',
				'selectors' => array(
					'{{WRAPPER}} .dgx-pills__item' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pill_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0E1A26',
				'selectors' => array(
					'{{WRAPPER}} .dgx-pills__item' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'pill_typography',
				'selector'       => '{{WRAPPER}} .dgx-pills__item',
				'fields_options' => array(
					'typography'  => array( 'default' => 'custom' ),
					'font_size'   => array( 'default' => array( 'unit' => 'px', 'size' => 14 ) ),
					'font_weight' => array( 'default' => '500' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'pill_border',
				'selector'       => '{{WRAPPER}} .dgx-pills__item',
				'fields_options' => array(
					'border' => array( 'default' => 'solid' ),
					'width'  => array(
						'default' => array(
							'top'      => '1',
							'right'    => '1',
							'bottom'   => '1',
							'left'     => '1',
							'unit'     => 'px',
							'isLinked' => true,
						),
					),
					'color'  => array( 'default' => 'rgba(14, 26, 38, 0.08)' ),
				),
			)
		);

		$this->add_responsive_control(
			'pill_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 100 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-pills__item' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pill_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 14,
					'right'    => 22,
					'bottom'   => 14,
					'left'     => 18,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-pills__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_text_gap',
			array(
				'label'      => esc_html__( 'Icon Spacing', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 12 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-pills__item' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ---- Icon ---------------------------------------------------------
		$this->start_controls_section(
			'style_icon',
			array(
				'label' => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'icon_box_size',
			array(
				'label'      => esc_html__( 'Circle Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 16, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 36 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-pills__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 48 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 18 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-pills__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dgx-pills__icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#435970',
				'selectors' => array(
					'{{WRAPPER}} .dgx-pills__icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg',
			array(
				'label'     => esc_html__( 'Circle Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .dgx-pills__icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_border_color',
			array(
				'label'     => esc_html__( 'Circle Border', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#DFEDFB',
				'selectors' => array(
					'{{WRAPPER}} .dgx-pills__icon' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	// =======================================================================
	// RENDER
	// =======================================================================

	/**
	 * Frontend render.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		if ( empty( $s['pills'] ) ) {
			return;
		}

		$classes = array( 'dgx-pills' );
		if ( 'right' === ( $s['direction'] ?? 'left' ) ) {
			$classes[] = 'dgx-pills--reverse';
		}
		if ( 'yes' === ( $s['pause_on_hover'] ?? '' ) ) {
			$classes[] = 'dgx-pills--pause';
		}
		if ( 'yes' === ( $s['edge_fade'] ?? '' ) ) {
			$classes[] = 'dgx-pills--fade';
		}

		$speed = isset( $s['speed']['size'] ) && '' !== $s['speed']['size'] ? (float) $s['speed']['size'] : 40;

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		$this->add_render_attribute( 'wrapper', 'data-speed', (string) $speed );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="dgx-pills__track">
				<?php
				// One set of pills is rendered; the script repeats it until the
				// row is wider than the screen, then mirrors it for a seamless
				// (gap-free, never-resetting) loop.
				foreach ( $s['pills'] as $pill ) {
					$this->render_pill( $pill, false );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single pill.
	 *
	 * @param array $pill      Repeater item.
	 * @param bool  $duplicate Whether this is the hidden duplicate copy.
	 * @return void
	 */
	private function render_pill( array $pill, $duplicate ) {
		$text     = isset( $pill['pill_text'] ) ? $pill['pill_text'] : '';
		$has_icon = ! empty( $pill['pill_icon']['value'] );
		$has_link = ! empty( $pill['pill_link']['url'] );

		$key = 'pill_' . ( isset( $pill['_id'] ) ? $pill['_id'] : wp_rand() ) . ( $duplicate ? '_d' : '' );
		$this->add_render_attribute( $key, 'class', 'dgx-pills__item' );

		if ( $has_link && ! $duplicate ) {
			$this->add_link_attributes( $key, $pill['pill_link'] );
			$tag = 'a';
		} else {
			$tag = 'span';
		}

		if ( $duplicate ) {
			$this->add_render_attribute( $key, 'aria-hidden', 'true' );
		}
		?>
		<<?php echo esc_attr( $tag ); ?> <?php echo $this->get_render_attribute_string( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $has_icon ) : ?>
				<span class="dgx-pills__icon"><?php Icons_Manager::render_icon( $pill['pill_icon'], array( 'aria-hidden' => 'true' ) ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $text ) : ?>
				<span class="dgx-pills__label"><?php echo esc_html( $text ); ?></span>
			<?php endif; ?>
		</<?php echo esc_attr( $tag ); ?>>
		<?php
	}
}
