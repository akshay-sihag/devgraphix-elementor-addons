<?php
/**
 * Marquee Text widget.
 *
 * A large, continuously-scrolling text marquee — the big rotating headline that
 * runs across the foot of homepage.html. One phrase (with an optional
 * separator) loops seamlessly forever; the text can be a solid colour or a
 * gradient fill, and the edges can fade out. The motion is a smooth, never-
 * stopping linear loop (the set is repeated + mirrored in JS for a gap-free
 * `translateX(-50%)` cycle, with a constant pixel speed at any text length).
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Marquee_Text
 */
class Marquee_Text extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-marquee-text';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Marquee Text', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-marquee-text';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'marquee', 'text', 'scrolling', 'ticker', 'rotating', 'banner', 'headline', 'loop', 'gradient' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-marquee-text' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-marquee-text' );
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

		// -------------------------------------------------------------------
		// Content — Text
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_text',
			array(
				'label' => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Your Brand Name', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'show_separator',
			array(
				'label'        => esc_html__( 'Show Separator', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'A small mark shown between each repetition of the text.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'separator_type',
			array(
				'label'     => esc_html__( 'Separator', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'dot',
				'options'   => array(
					'dot'       => esc_html__( 'Dot', 'devgraphix-elementor-addons' ),
					'character' => esc_html__( 'Character', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'show_separator' => 'yes' ),
			)
		);

		$this->add_control(
			'separator_char',
			array(
				'label'       => esc_html__( 'Character', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '•',
				'description' => esc_html__( 'e.g. •  /  ·  —  ×  ★', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_separator' => 'yes', 'separator_type' => 'character' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Motion
		// -------------------------------------------------------------------
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
				'label'       => esc_html__( 'Speed (seconds per loop)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 's' ),
				'range'       => array( 's' => array( 'min' => 5, 'max' => 180, 'step' => 1 ) ),
				'default'     => array( 'unit' => 's', 'size' => 30 ),
				'description' => esc_html__( 'Higher = slower. The pixel speed stays constant however long the text is.', 'devgraphix-elementor-addons' ),
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
				'label'      => esc_html__( 'Spacing', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 64 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-mtext' => '--dgx-mtext-gap: {{SIZE}}{{UNIT}};' ),
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

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Effects
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_effects',
			array(
				'label' => esc_html__( 'Effects', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'edge_fade',
			array(
				'label'        => esc_html__( 'Fade Edges', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Fade the text out softly at the left and right edges.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_responsive_control(
			'fade_width',
			array(
				'label'      => esc_html__( 'Fade Width', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => '%', 'size' => 10 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-mtext' => '--dgx-mtext-fade: {{SIZE}}%;' ),
				'condition'  => array( 'edge_fade' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	/**
	 * Style controls.
	 *
	 * @return void
	 */
	private function register_style_controls() {
		$root = '{{WRAPPER}} .dgx-mtext';

		// -------------------------------------------------------------------
		// Style — Text
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_text',
			array(
				'label' => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'color_type',
			array(
				'label'   => esc_html__( 'Fill Type', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => array(
					'solid'    => esc_html__( 'Solid Colour', 'devgraphix-elementor-addons' ),
					'gradient' => esc_html__( 'Gradient', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Colour', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(14,26,38,0.16)',
				'selectors' => array( '{{WRAPPER}} .dgx-mtext__text' => 'color: {{VALUE}};' ),
				'condition' => array( 'color_type' => 'solid' ),
			)
		);

		$this->add_control(
			'grad_a',
			array(
				'label'     => esc_html__( 'Gradient Start', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#435970',
				'selectors' => array( $root => '--dgx-mtext-grad-a: {{VALUE}};' ),
				'condition' => array( 'color_type' => 'gradient' ),
			)
		);

		$this->add_control(
			'grad_b',
			array(
				'label'     => esc_html__( 'Gradient End', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#bcd3e8',
				'selectors' => array( $root => '--dgx-mtext-grad-b: {{VALUE}};' ),
				'condition' => array( 'color_type' => 'gradient' ),
			)
		);

		$this->add_responsive_control(
			'grad_angle',
			array(
				'label'      => esc_html__( 'Gradient Angle', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'range'      => array( 'deg' => array( 'min' => 0, 'max' => 360 ) ),
				'default'    => array( 'unit' => 'deg', 'size' => 90 ),
				'selectors'  => array( $root => '--dgx-mtext-grad-angle: {{SIZE}}deg;' ),
				'condition'  => array( 'color_type' => 'gradient' ),
			)
		);

		// Typography lives on the item so the separator character can size
		// relative to the text (the inner text span inherits it, then takes the
		// solid colour or the gradient clip on top).
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'text_typography',
				'selector'       => '{{WRAPPER}} .dgx-mtext__item',
				'fields_options' => array(
					'typography'     => array( 'default' => 'custom' ),
					'font_family'    => array( 'default' => 'Fraunces' ),
					'font_size'      => array(
						'default'        => array( 'unit' => 'px', 'size' => 160 ),
						'tablet_default' => array( 'unit' => 'px', 'size' => 100 ),
						'mobile_default' => array( 'unit' => 'px', 'size' => 56 ),
					),
					'font_weight'    => array( 'default' => '400' ),
					'letter_spacing' => array( 'default' => array( 'unit' => 'px', 'size' => -2 ) ),
				),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Separator
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_separator',
			array(
				'label'     => esc_html__( 'Separator', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_separator' => 'yes' ),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Colour', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(14,26,38,0.16)',
				'selectors' => array( $root => '--dgx-mtext-sep-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'dot_size',
			array(
				'label'      => esc_html__( 'Dot Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 4, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-mtext__dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'separator_type' => 'dot' ),
			)
		);

		$this->add_responsive_control(
			'char_size',
			array(
				'label'      => esc_html__( 'Character Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'min' => 8, 'max' => 200 ),
					'em' => array( 'min' => 0.1, 'max' => 2, 'step' => 0.05 ),
				),
				'default'    => array( 'unit' => 'em', 'size' => 0.5 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-mtext__sep-char' => 'font-size: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'separator_type' => 'character' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Box
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_box',
			array(
				'label' => esc_html__( 'Box', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'box_bg',
			array(
				'label'     => esc_html__( 'Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array( '{{WRAPPER}} .dgx-mtext' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array( 'unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'isLinked' => true ),
				'selectors'  => array( '{{WRAPPER}} .dgx-mtext' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	// =======================================================================
	// RENDER
	// =======================================================================

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$text = isset( $s['text'] ) ? $s['text'] : '';

		if ( '' === trim( $text ) ) {
			return;
		}

		$show_sep  = 'yes' === ( isset( $s['show_separator'] ) ? $s['show_separator'] : '' );
		$sep_type  = isset( $s['separator_type'] ) ? $s['separator_type'] : 'dot';
		$sep_char  = isset( $s['separator_char'] ) ? $s['separator_char'] : '•';
		$gradient  = 'gradient' === ( isset( $s['color_type'] ) ? $s['color_type'] : 'solid' );
		$direction = 'right' === ( isset( $s['direction'] ) ? $s['direction'] : 'left' ) ? 'right' : 'left';
		$speed     = isset( $s['speed']['size'] ) && '' !== $s['speed']['size'] ? (float) $s['speed']['size'] : 30;

		$classes = array( 'dgx-mtext' );
		if ( 'right' === $direction ) {
			$classes[] = 'dgx-mtext--reverse';
		}
		if ( 'yes' === ( isset( $s['pause_on_hover'] ) ? $s['pause_on_hover'] : '' ) ) {
			$classes[] = 'dgx-mtext--pause';
		}
		if ( 'yes' === ( isset( $s['edge_fade'] ) ? $s['edge_fade'] : '' ) ) {
			$classes[] = 'dgx-mtext--fade';
		}
		if ( $gradient ) {
			$classes[] = 'dgx-mtext--gradient';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		$this->add_render_attribute( 'wrapper', 'data-speed', (string) $speed );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="dgx-mtext__track">
				<?php
				// One item is rendered; the script repeats it until it fills the
				// screen, then mirrors it for a seamless translateX(-50%) loop.
				$this->render_item( $text, $show_sep, $sep_type, $sep_char );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single marquee item (text + optional separator).
	 *
	 * @param string $text     Text.
	 * @param bool   $show_sep Whether to show the separator.
	 * @param string $sep_type 'dot' | 'character'.
	 * @param string $sep_char Separator character.
	 * @return void
	 */
	private function render_item( $text, $show_sep, $sep_type, $sep_char ) {
		?>
		<span class="dgx-mtext__item">
			<span class="dgx-mtext__text"><?php echo esc_html( $text ); ?></span>
			<?php if ( $show_sep ) : ?>
				<?php if ( 'character' === $sep_type && '' !== $sep_char ) : ?>
					<span class="dgx-mtext__sep dgx-mtext__sep-char" aria-hidden="true"><?php echo esc_html( $sep_char ); ?></span>
				<?php elseif ( 'dot' === $sep_type ) : ?>
					<span class="dgx-mtext__sep dgx-mtext__dot" aria-hidden="true"></span>
				<?php endif; ?>
			<?php endif; ?>
		</span>
		<?php
	}
}
