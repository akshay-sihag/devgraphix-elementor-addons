<?php
/**
 * Section Heading widget.
 *
 * Combines three pieces into one heading block (pixel-perfect from
 * homepage.html):
 *   1. an eyebrow row — counter pill + divider rule + mono label,
 *   2. a serif headline with an italic, tinted accent span,
 *   3. a sans subheading paragraph.
 *
 * Every part is optional and fully styleable, with defaults that match the
 * rendered design.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Section_Heading
 */
class Section_Heading extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-section-heading';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Swiss Heading', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-heading';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'heading', 'title', 'headline', 'eyebrow', 'subheading', 'serif', 'swiss' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-section-heading' );
	}

	// =======================================================================
	// HELPERS
	// =======================================================================

	/**
	 * Register a simple colour control.
	 *
	 * @param string              $id        Control id.
	 * @param string              $label     Label.
	 * @param string              $selector  Full CSS selector.
	 * @param string              $default   Default colour.
	 * @param string              $prop      CSS property.
	 * @param array<string,mixed> $condition Condition.
	 * @return void
	 */
	private function add_color( $id, $label, $selector, $default, $prop = 'color', array $condition = array() ) {
		$this->add_control(
			$id,
			array(
				'label'     => $label,
				'type'      => Controls_Manager::COLOR,
				'default'   => $default,
				'selectors' => array( $selector => $prop . ': {{VALUE}};' ),
				'condition' => $condition,
			)
		);
	}

	/**
	 * Register a typography group control with default fields_options.
	 *
	 * @param string              $id             Group name.
	 * @param string              $selector       Full CSS selector.
	 * @param array<string,mixed> $fields_options Field defaults.
	 * @return void
	 */
	private function add_typo( $id, $selector, array $fields_options ) {
		$fields_options = array_merge( array( 'typography' => array( 'default' => 'custom' ) ), $fields_options );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => $id,
				'selector'       => $selector,
				'fields_options' => $fields_options,
			)
		);
	}

	/**
	 * Shorthand for a px font-size fields_options entry.
	 *
	 * @param int $size Font size.
	 * @return array<string,mixed>
	 */
	private function fs( $size ) {
		return array( 'default' => array( 'unit' => 'px', 'size' => $size ) );
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
		// Content — Eyebrow
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_eyebrow',
			array(
				'label' => esc_html__( 'Eyebrow', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_eyebrow',
			array(
				'label'        => esc_html__( 'Show Eyebrow Row', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'eyebrow_pill',
			array(
				'label'        => esc_html__( 'Wrap Eyebrow in a Pill', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Wrap the whole eyebrow row in one rounded pill (icon + text inside).', 'devgraphix-elementor-addons' ),
				'condition'    => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_control(
			'eyebrow_icon',
			array(
				'label'       => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::ICONS,
				'description' => esc_html__( 'Optional icon shown at the start of the eyebrow.', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => esc_html__( 'Show Counter Pill', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_control(
			'count_text',
			array(
				'label'     => esc_html__( 'Counter Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '01 / 06',
				'condition' => array( 'show_eyebrow' => 'yes', 'show_count' => 'yes' ),
			)
		);

		$this->add_control(
			'show_divider',
			array(
				'label'        => esc_html__( 'Show Divider', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_control(
			'divider_type',
			array(
				'label'     => esc_html__( 'Divider Style', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'line',
				'options'   => array(
					'line' => esc_html__( 'Line', 'devgraphix-elementor-addons' ),
					'dot'  => esc_html__( 'Dot', 'devgraphix-elementor-addons' ),
					'text' => esc_html__( 'Character', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'show_eyebrow' => 'yes', 'show_divider' => 'yes' ),
			)
		);

		$this->add_control(
			'divider_char',
			array(
				'label'       => esc_html__( 'Character', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '·',
				'description' => esc_html__( 'e.g. ·  /  —  •  ×', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_eyebrow' => 'yes', 'show_divider' => 'yes', 'divider_type' => 'text' ),
			)
		);

		$this->add_control(
			'label_text',
			array(
				'label'       => esc_html__( 'Label', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Headline
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_headline',
			array(
				'label' => esc_html__( 'Headline', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title_text',
			array(
				'label'       => esc_html__( 'Title', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'accent_text',
			array(
				'label'       => esc_html__( 'Accent (italic)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'sit amet.', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'accent_break',
			array(
				'label'        => esc_html__( 'Accent on New Line', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'p'    => 'p',
					'span' => 'span',
				),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Subheading
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_subheading',
			array(
				'label' => esc_html__( 'Subheading', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_sub',
			array(
				'label'        => esc_html__( 'Show Subheading', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'sub_text',
			array(
				'label'     => esc_html__( 'Subheading', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore.', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_sub' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Background Heading (watermark)
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_watermark',
			array(
				'label' => esc_html__( 'Background Heading', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_watermark',
			array(
				'label'        => esc_html__( 'Enable Background Heading', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'A large faint heading sitting behind the main one. Position it anywhere from the Style tab.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'watermark_text',
			array(
				'label'       => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_watermark' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — General
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_general',
			array(
				'label' => esc_html__( 'General', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'align',
			array(
				'label'        => esc_html__( 'Alignment', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::CHOOSE,
				'default'      => 'left',
				'options'      => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'devgraphix-elementor-addons' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'devgraphix-elementor-addons' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'devgraphix-elementor-addons' ), 'icon' => 'eicon-text-align-right' ),
				),
				'prefix_class' => 'dgx-halign-',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Eyebrow
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_eyebrow',
			array(
				'label'     => esc_html__( 'Eyebrow', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_gap',
			array(
				'label'      => esc_html__( 'Items Gap', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 14 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__eyebrow' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_spacing',
			array(
				'label'      => esc_html__( 'Spacing Below', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 18 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		// ---- Pill container (when "Wrap in a Pill" is on) ----
		$this->add_control(
			'heading_eyebrow_pill',
			array(
				'label'     => esc_html__( 'Pill Container', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'eyebrow_pill' => 'yes' ),
			)
		);

		$this->add_color( 'eyebrow_pill_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__eyebrow--pill', 'rgba(67,89,112,0.1)', 'background-color', array( 'eyebrow_pill' => 'yes' ) );

		$this->add_responsive_control(
			'eyebrow_pill_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__eyebrow--pill' => 'border-radius: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'eyebrow_pill' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_pill_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 9, 'right' => 16, 'bottom' => 9, 'left' => 16, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__eyebrow--pill' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'condition'  => array( 'eyebrow_pill' => 'yes' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'      => 'eyebrow_pill_border',
				'selector'  => '{{WRAPPER}} .dgx-heading__eyebrow--pill',
				'condition' => array( 'eyebrow_pill' => 'yes' ),
			)
		);

		// ---- Icon ----
		$this->add_control(
			'heading_eyebrow_icon',
			array(
				'label'     => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'eyebrow_icon[value]!' => '' ),
			)
		);

		$this->add_color( 'eyebrow_icon_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__icon', '#0e1a26', 'color', array( 'eyebrow_icon[value]!' => '' ) );
		$this->add_control(
			'eyebrow_icon_bg',
			array(
				'label'     => esc_html__( 'Icon Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array( '{{WRAPPER}} .dgx-heading__icon' => 'background-color: {{VALUE}};' ),
				'condition' => array( 'eyebrow_icon[value]!' => '' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_icon_size',
			array(
				'label'      => esc_html__( 'Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 6, 'max' => 48 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 11 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-heading__icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dgx-heading__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'eyebrow_icon[value]!' => '' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_icon_pad',
			array(
				'label'      => esc_html__( 'Icon Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 24 ) ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__icon' => 'padding: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'eyebrow_icon[value]!' => '' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_icon_radius',
			array(
				'label'      => esc_html__( 'Icon Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__icon' => 'border-radius: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'eyebrow_icon[value]!' => '' ),
			)
		);

		$this->add_control(
			'heading_count',
			array(
				'label'     => esc_html__( 'Counter Pill', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_count' => 'yes' ),
			)
		);

		$this->add_color( 'count_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__count', '#435970', 'color', array( 'show_count' => 'yes' ) );
		$this->add_color( 'count_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__count', 'rgba(67,89,112,0.1)', 'background-color', array( 'show_count' => 'yes' ) );
		$this->add_typo( 'count_typo', '{{WRAPPER}} .dgx-heading__count', array( 'font_size' => $this->fs( 11 ) ) );

		$this->add_responsive_control(
			'count_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__count' => 'border-radius: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'show_count' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'count_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 5, 'right' => 11, 'bottom' => 5, 'left' => 11, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__count' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'condition'  => array( 'show_count' => 'yes' ),
			)
		);

		$this->add_control(
			'heading_divider',
			array(
				'label'     => esc_html__( 'Divider', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_divider' => 'yes' ),
			)
		);

		$this->add_color( 'divider_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading', 'rgba(67,89,112,0.35)', '--dgx-div-color', array( 'show_divider' => 'yes' ) );

		$this->add_responsive_control(
			'divider_width',
			array(
				'label'      => esc_html__( 'Width', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 40 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__divider--line' => 'width: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'show_divider' => 'yes', 'divider_type' => 'line' ),
			)
		);

		$this->add_responsive_control(
			'divider_thickness',
			array(
				'label'      => esc_html__( 'Thickness', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 10 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__divider--line' => 'height: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'show_divider' => 'yes', 'divider_type' => 'line' ),
			)
		);

		$this->add_responsive_control(
			'divider_dot_size',
			array(
				'label'      => esc_html__( 'Dot Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 2, 'max' => 24 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 5 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__divider--dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'show_divider' => 'yes', 'divider_type' => 'dot' ),
			)
		);

		$this->add_responsive_control(
			'divider_char_size',
			array(
				'label'      => esc_html__( 'Character Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 48 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 16 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__divider--text' => 'font-size: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'show_divider' => 'yes', 'divider_type' => 'text' ),
			)
		);

		$this->add_control(
			'heading_label',
			array(
				'label'     => esc_html__( 'Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'label_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__label', 'rgba(14,26,38,0.62)' );
		$this->add_typo( 'label_typo', '{{WRAPPER}} .dgx-heading__label', array( 'font_size' => $this->fs( 10 ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Headline
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_headline',
			array(
				'label' => esc_html__( 'Headline', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'title_color', esc_html__( 'Title Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__title', '#0e1a26' );
		$this->add_typo(
			'title_typo',
			'{{WRAPPER}} .dgx-heading__title',
			array(
				'font_size'   => $this->fs( 88 ),
				'font_weight' => array( 'default' => '400' ),
				'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 0.92 ) ),
			)
		);

		$this->add_control(
			'heading_accent',
			array(
				'label'     => esc_html__( 'Accent', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'accent_color', esc_html__( 'Accent Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__accent', '#435970' );
		$this->add_typo(
			'accent_typo',
			'{{WRAPPER}} .dgx-heading__accent',
			array(
				'font_style'  => array( 'default' => 'italic' ),
				'font_weight' => array( 'default' => '300' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Subheading
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_subheading',
			array(
				'label'     => esc_html__( 'Subheading', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_sub' => 'yes' ),
			)
		);

		$this->add_color( 'sub_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__sub', 'rgba(14,26,38,0.62)' );
		$this->add_typo(
			'sub_typo',
			'{{WRAPPER}} .dgx-heading__sub',
			array(
				'font_size'   => $this->fs( 17 ),
				'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 1.55 ) ),
			)
		);

		$this->add_responsive_control(
			'sub_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 200, 'max' => 1000 ),
					'%'  => array( 'min' => 10, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 540 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__sub' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'sub_spacing',
			array(
				'label'      => esc_html__( 'Spacing Above', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 18 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__sub' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Background Heading (watermark)
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_watermark',
			array(
				'label'     => esc_html__( 'Background Heading', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_watermark' => 'yes' ),
			)
		);

		$this->add_color( 'watermark_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-heading__watermark', 'rgba(67,89,112,0.07)' );

		$this->add_typo(
			'watermark_typo',
			'{{WRAPPER}} .dgx-heading__watermark',
			array(
				'font_size'   => $this->fs( 320 ),
				'font_style'  => array( 'default' => 'italic' ),
				'font_weight' => array( 'default' => '300' ),
			)
		);

		$this->add_control(
			'watermark_nowrap',
			array(
				'label'        => esc_html__( 'Keep on One Line', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'selectors'    => array( '{{WRAPPER}} .dgx-heading__watermark' => 'white-space: nowrap;' ),
			)
		);

		$this->add_control(
			'heading_wm_position',
			array(
				'label'     => esc_html__( 'Position', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'watermark_x',
			array(
				'label'      => esc_html__( 'Horizontal', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => -1000, 'max' => 1000 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
				),
				'default'    => array( 'unit' => '%', 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__watermark' => 'left: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'watermark_y',
			array(
				'label'      => esc_html__( 'Vertical', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => -1000, 'max' => 1000 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
				),
				'default'    => array( 'unit' => '%', 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__watermark' => 'top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'watermark_rotation',
			array(
				'label'      => esc_html__( 'Rotation', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'range'      => array( 'deg' => array( 'min' => -45, 'max' => 45 ) ),
				'default'    => array( 'unit' => 'deg', 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-heading__watermark' => '--dgx-wm-rot: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'watermark_z',
			array(
				'label'        => esc_html__( 'Place In Front', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Off: behind the heading (default). On: in front (sits over the text).', 'devgraphix-elementor-addons' ),
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
		$s = $this->get_settings_for_display();

		$show_eyebrow = 'yes' === ( isset( $s['show_eyebrow'] ) ? $s['show_eyebrow'] : '' );
		$show_count   = 'yes' === ( isset( $s['show_count'] ) ? $s['show_count'] : '' );
		$show_divider = 'yes' === ( isset( $s['show_divider'] ) ? $s['show_divider'] : '' );
		$show_sub     = 'yes' === ( isset( $s['show_sub'] ) ? $s['show_sub'] : '' );

		$eyebrow_pill = 'yes' === ( isset( $s['eyebrow_pill'] ) ? $s['eyebrow_pill'] : '' );
		$eyebrow_icon = isset( $s['eyebrow_icon'] ) ? $s['eyebrow_icon'] : array();
		$has_icon     = ! empty( $eyebrow_icon['value'] );
		$divider_type = isset( $s['divider_type'] ) ? $s['divider_type'] : 'line';
		$divider_char = isset( $s['divider_char'] ) ? $s['divider_char'] : '·';

		$show_watermark = 'yes' === ( isset( $s['show_watermark'] ) ? $s['show_watermark'] : '' );
		$watermark_text = isset( $s['watermark_text'] ) ? $s['watermark_text'] : '';
		$watermark_front = 'yes' === ( isset( $s['watermark_z'] ) ? $s['watermark_z'] : '' );

		$count_text = isset( $s['count_text'] ) ? $s['count_text'] : '';
		$label_text = isset( $s['label_text'] ) ? $s['label_text'] : '';
		$title_text = isset( $s['title_text'] ) ? $s['title_text'] : '';
		$accent     = isset( $s['accent_text'] ) ? $s['accent_text'] : '';
		$sub_text   = isset( $s['sub_text'] ) ? $s['sub_text'] : '';

		$tag = isset( $s['title_tag'] ) ? $s['title_tag'] : 'h2';
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span' );
		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'h2';
		}

		$has_eyebrow = $show_eyebrow && ( $has_icon || ( $show_count && '' !== $count_text ) || $show_divider || '' !== $label_text );
		$has_title   = ( '' !== $title_text || '' !== $accent );
		$has_wm      = $show_watermark && '' !== $watermark_text;

		if ( ! $has_eyebrow && ! $has_title && ! ( $show_sub && '' !== $sub_text ) && ! $has_wm ) {
			return;
		}

		$eyebrow_class = 'dgx-heading__eyebrow' . ( $eyebrow_pill ? ' dgx-heading__eyebrow--pill' : '' );
		$root_class    = 'dgx-heading' . ( $has_wm && $watermark_front ? ' dgx-heading--wm-front' : '' );
		?>
		<div class="<?php echo esc_attr( $root_class ); ?>">
			<?php if ( $has_wm ) : ?>
				<span class="dgx-heading__watermark" aria-hidden="true"><?php echo esc_html( $watermark_text ); ?></span>
			<?php endif; ?>

			<?php if ( $has_eyebrow ) : ?>
				<div class="<?php echo esc_attr( $eyebrow_class ); ?>">
					<?php if ( $has_icon ) : ?>
						<span class="dgx-heading__icon"><?php Icons_Manager::render_icon( $eyebrow_icon, array( 'aria-hidden' => 'true' ) ); ?></span>
					<?php endif; ?>
					<?php if ( $show_count && '' !== $count_text ) : ?>
						<span class="dgx-heading__count"><?php echo esc_html( $count_text ); ?></span>
					<?php endif; ?>
					<?php if ( $show_divider ) : ?>
						<?php if ( 'text' === $divider_type ) : ?>
							<span class="dgx-heading__divider dgx-heading__divider--text" aria-hidden="true"><?php echo esc_html( $divider_char ); ?></span>
						<?php elseif ( 'dot' === $divider_type ) : ?>
							<span class="dgx-heading__divider dgx-heading__divider--dot" aria-hidden="true"></span>
						<?php else : ?>
							<span class="dgx-heading__divider dgx-heading__divider--line" aria-hidden="true"></span>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( '' !== $label_text ) : ?>
						<span class="dgx-heading__label"><?php echo esc_html( $label_text ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_title ) : ?>
				<<?php echo esc_attr( $tag ); ?> class="dgx-heading__title">
					<?php
					echo esc_html( $title_text );
					if ( '' !== $accent ) {
						echo 'yes' === ( isset( $s['accent_break'] ) ? $s['accent_break'] : '' ) ? '<br>' : ' ';
						echo '<span class="dgx-heading__accent">' . esc_html( $accent ) . '</span>';
					}
					?>
				</<?php echo esc_attr( $tag ); ?>>
			<?php endif; ?>

			<?php if ( $show_sub && '' !== $sub_text ) : ?>
				<p class="dgx-heading__sub"><?php echo nl2br( esc_html( $sub_text ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
