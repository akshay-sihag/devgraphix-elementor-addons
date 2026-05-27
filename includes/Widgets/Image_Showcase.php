<?php
/**
 * Image Showcase widget.
 *
 * A full-bleed photo card with freely-positionable overlay layers (inspired by
 * the reference design in homepage.html):
 *   - a top trust badge (pulsing dot + mono label),
 *   - a serif stat ribbon with a mono caption,
 *   - a "featured" chip (dot + label + arrow, optionally linked).
 *
 * Every layer can be anchored to any of 9 points, nudged on X/Y, and rotated —
 * and may bleed past the photo edge (the photo is clipped in an inner wrapper).
 * Each layer is optional and fully styleable.
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
 * Class Image_Showcase
 */
class Image_Showcase extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-image-showcase';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Photo Spotlight', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-spotlight';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'image', 'photo', 'showcase', 'spotlight', 'stat', 'badge', 'overlay', 'hero' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-image-showcase' );
	}

	// =======================================================================
	// HELPERS
	// =======================================================================

	/**
	 * Nine anchor points.
	 *
	 * @return array<string,string>
	 */
	private function anchor_options() {
		return array(
			'top-left'      => esc_html__( 'Top Left', 'devgraphix-elementor-addons' ),
			'top-center'    => esc_html__( 'Top Center', 'devgraphix-elementor-addons' ),
			'top-right'     => esc_html__( 'Top Right', 'devgraphix-elementor-addons' ),
			'middle-left'   => esc_html__( 'Middle Left', 'devgraphix-elementor-addons' ),
			'middle-center' => esc_html__( 'Middle Center', 'devgraphix-elementor-addons' ),
			'middle-right'  => esc_html__( 'Middle Right', 'devgraphix-elementor-addons' ),
			'bottom-left'   => esc_html__( 'Bottom Left', 'devgraphix-elementor-addons' ),
			'bottom-center' => esc_html__( 'Bottom Center', 'devgraphix-elementor-addons' ),
			'bottom-right'  => esc_html__( 'Bottom Right', 'devgraphix-elementor-addons' ),
		);
	}

	/**
	 * Resolve an anchor setting to its CSS class (validated).
	 *
	 * @param array  $s       Settings.
	 * @param string $key     Setting key.
	 * @param string $default Default anchor.
	 * @return string
	 */
	private function anchor_class( array $s, $key, $default ) {
		$value = isset( $s[ $key ] ) ? $s[ $key ] : $default;
		if ( ! isset( $this->anchor_options()[ $value ] ) ) {
			$value = $default;
		}
		return 'dgx-anchor--' . $value;
	}

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
	 * @param array<string,mixed> $condition      Condition.
	 * @return void
	 */
	private function add_typo( $id, $selector, array $fields_options, array $condition = array() ) {
		$fields_options = array_merge( array( 'typography' => array( 'default' => 'custom' ) ), $fields_options );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => $id,
				'selector'       => $selector,
				'fields_options' => $fields_options,
				'condition'      => $condition,
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

	/**
	 * Register the position controls (anchor + X/Y nudge + rotation) for a layer.
	 *
	 * @param string              $prefix    Control id prefix.
	 * @param string              $selector  Full CSS selector for the layer.
	 * @param string              $anchor    Default anchor.
	 * @param int                 $x         Default X offset (px).
	 * @param int                 $y         Default Y offset (px).
	 * @param int                 $rot       Default rotation (deg).
	 * @param array<string,mixed> $condition Shared condition.
	 * @return void
	 */
	private function add_position_controls( $prefix, $selector, $anchor, $x, $y, $rot, array $condition = array() ) {
		$this->add_control(
			$prefix . '_pos_heading',
			array(
				'label'     => esc_html__( 'Position', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => $condition,
			)
		);

		$this->add_control(
			$prefix . '_anchor',
			array(
				'label'     => esc_html__( 'Anchor', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $anchor,
				'options'   => $this->anchor_options(),
				'condition' => $condition,
			)
		);

		$this->add_responsive_control(
			$prefix . '_x',
			array(
				'label'      => esc_html__( 'Offset X', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => $x ),
				'selectors'  => array( $selector => '--tx: {{SIZE}}{{UNIT}};' ),
				'condition'  => $condition,
			)
		);

		$this->add_responsive_control(
			$prefix . '_y',
			array(
				'label'      => esc_html__( 'Offset Y', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => -500, 'max' => 500 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => $y ),
				'selectors'  => array( $selector => '--ty: {{SIZE}}{{UNIT}};' ),
				'condition'  => $condition,
			)
		);

		$this->add_responsive_control(
			$prefix . '_rot',
			array(
				'label'      => esc_html__( 'Rotation', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'range'      => array( 'deg' => array( 'min' => -45, 'max' => 45 ) ),
				'default'    => array( 'unit' => 'deg', 'size' => $rot ),
				'selectors'  => array( $selector => '--rot: {{SIZE}}{{UNIT}};' ),
				'condition'  => $condition,
			)
		);
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
		// Content — Image
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_image',
			array(
				'label' => esc_html__( 'Image', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'image',
			array(
				'label' => esc_html__( 'Photo', 'devgraphix-elementor-addons' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'image_alt',
			array(
				'label'       => esc_html__( 'Alt Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => esc_html__( 'Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 200, 'max' => 1000 ),
					'vh' => array( 'min' => 20, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 600 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-showcase' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'image_fit',
			array(
				'label'     => esc_html__( 'Image Fit', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'cover'   => esc_html__( 'Cover (fill)', 'devgraphix-elementor-addons' ),
					'contain' => esc_html__( 'Contain (fit)', 'devgraphix-elementor-addons' ),
					'auto'    => esc_html__( 'Original size', 'devgraphix-elementor-addons' ),
				),
				'selectors' => array( '{{WRAPPER}} .dgx-showcase__img' => 'background-size: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'image_position',
			array(
				'label'     => esc_html__( 'Image Position', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center',
				'options'   => array(
					'center'        => esc_html__( 'Center', 'devgraphix-elementor-addons' ),
					'top'           => esc_html__( 'Top', 'devgraphix-elementor-addons' ),
					'bottom'        => esc_html__( 'Bottom', 'devgraphix-elementor-addons' ),
					'left'          => esc_html__( 'Left', 'devgraphix-elementor-addons' ),
					'right'         => esc_html__( 'Right', 'devgraphix-elementor-addons' ),
					'left top'      => esc_html__( 'Top Left', 'devgraphix-elementor-addons' ),
					'right top'     => esc_html__( 'Top Right', 'devgraphix-elementor-addons' ),
					'left bottom'   => esc_html__( 'Bottom Left', 'devgraphix-elementor-addons' ),
					'right bottom'  => esc_html__( 'Bottom Right', 'devgraphix-elementor-addons' ),
				),
				'selectors' => array( '{{WRAPPER}} .dgx-showcase__img' => 'background-position: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 28 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-showcase__media' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'show_overlay',
			array(
				'label'        => esc_html__( 'Dark Gradient Overlay', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Top Badge
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_badge',
			array(
				'label' => esc_html__( 'Top Badge', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_badge',
			array(
				'label'        => esc_html__( 'Show Badge', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'badge_dot',
			array(
				'label'        => esc_html__( 'Show Dot', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_text',
			array(
				'label'       => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_badge' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Stat
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_stat',
			array(
				'label' => esc_html__( 'Stat', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_stat',
			array(
				'label'        => esc_html__( 'Show Stat Ribbon', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'stat_text',
			array(
				'label'     => esc_html__( 'Stat', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '100+',
				'condition' => array( 'show_stat' => 'yes' ),
			)
		);

		$this->add_control(
			'show_stat_label',
			array(
				'label'        => esc_html__( 'Show Caption', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_stat' => 'yes' ),
			)
		);

		$this->add_control(
			'stat_label_text',
			array(
				'label'       => esc_html__( 'Caption', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_stat' => 'yes', 'show_stat_label' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Featured Chip
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_chip',
			array(
				'label' => esc_html__( 'Featured Chip', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_chip',
			array(
				'label'        => esc_html__( 'Show Chip', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'chip_dot',
			array(
				'label'        => esc_html__( 'Show Dot', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_chip' => 'yes' ),
			)
		);

		$this->add_control(
			'chip_text',
			array(
				'label'       => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem · Ipsum', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_chip' => 'yes' ),
			)
		);

		$this->add_control(
			'chip_arrow',
			array(
				'label'        => esc_html__( 'Show Arrow', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_chip' => 'yes' ),
			)
		);

		$this->add_control(
			'chip_link',
			array(
				'label'       => esc_html__( 'Link', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'condition'   => array( 'show_chip' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Stat Card
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_card',
			array(
				'label' => esc_html__( 'Stat Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_card',
			array(
				'label'        => esc_html__( 'Show Stat Card', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'card_label',
			array(
				'label'       => esc_html__( 'Label', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_card' => 'yes' ),
			)
		);

		$this->add_control(
			'card_value',
			array(
				'label'       => esc_html__( 'Value', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( '100+', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_card' => 'yes' ),
			)
		);

		$this->add_control(
			'card_sub',
			array(
				'label'       => esc_html__( 'Sub Line', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum dolor sit', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_card' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Badge
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label'     => esc_html__( 'Top Badge', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_color( 'badge_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__badge', 'rgba(255,255,255,0.95)', 'background-color' );
		$this->add_color( 'badge_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__badge', '#0e1a26' );
		$this->add_color( 'badge_dot_color', esc_html__( 'Dot Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__badge-dot', '#435970', 'background-color', array( 'badge_dot' => 'yes' ) );
		$this->add_typo( 'badge_typo', '{{WRAPPER}} .dgx-showcase__badge', array( 'font_size' => $this->fs( 10 ) ) );
		$this->add_position_controls( 'badge', '{{WRAPPER}} .dgx-showcase__badge', 'top-left', 22, 22, 0 );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Stat
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_stat',
			array(
				'label'     => esc_html__( 'Stat', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_stat' => 'yes' ),
			)
		);

		$this->add_color( 'stat_bg', esc_html__( 'Ribbon Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__stat', '#0e1a26', 'background-color' );
		$this->add_color( 'stat_color', esc_html__( 'Stat Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__stat', '#d4e3f0' );
		$this->add_typo(
			'stat_typo',
			'{{WRAPPER}} .dgx-showcase__stat',
			array(
				'font_size'   => $this->fs( 64 ),
				'font_weight' => array( 'default' => '300' ),
				'font_style'  => array( 'default' => 'italic' ),
			)
		);
		$this->add_responsive_control(
			'stat_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 16, 'right' => 30, 'bottom' => 16, 'left' => 30, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-showcase__stat' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_position_controls( 'stat', '{{WRAPPER}} .dgx-showcase__stat', 'bottom-right', 28, -110, -6 );

		$this->add_control(
			'heading_stat_label',
			array(
				'label'     => esc_html__( 'Caption', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_stat_label' => 'yes' ),
			)
		);

		$this->add_color( 'stat_label_color', esc_html__( 'Caption Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__stat-label', '#ffffff', 'color', array( 'show_stat_label' => 'yes' ) );
		$this->add_typo( 'stat_label_typo', '{{WRAPPER}} .dgx-showcase__stat-label', array( 'font_size' => $this->fs( 10 ) ), array( 'show_stat_label' => 'yes' ) );
		$this->add_position_controls( 'statlabel', '{{WRAPPER}} .dgx-showcase__stat-label', 'bottom-right', -8, -80, 0, array( 'show_stat_label' => 'yes' ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Chip
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_chip',
			array(
				'label'     => esc_html__( 'Featured Chip', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_chip' => 'yes' ),
			)
		);

		$this->add_color( 'chip_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__chip', 'rgba(14,26,38,0.92)', 'background-color' );
		$this->add_color( 'chip_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__chip', '#ffffff' );
		$this->add_color( 'chip_dot_color', esc_html__( 'Dot Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__chip-dot', '#dfedfb', 'background-color', array( 'chip_dot' => 'yes' ) );
		$this->add_typo( 'chip_typo', '{{WRAPPER}} .dgx-showcase__chip', array( 'font_size' => $this->fs( 13 ), 'font_weight' => array( 'default' => '600' ) ) );
		$this->add_position_controls( 'chip', '{{WRAPPER}} .dgx-showcase__chip', 'bottom-left', 18, -18, 0 );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Stat Card
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_card',
			array(
				'label'     => esc_html__( 'Stat Card', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_card' => 'yes' ),
			)
		);

		$this->add_color( 'card_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__card', '#ffffff', 'background-color' );

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 20, 'right' => 26, 'bottom' => 20, 'left' => 26, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-showcase__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 22 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-showcase__card' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'heading_card_label',
			array(
				'label' => esc_html__( 'Label', 'devgraphix-elementor-addons' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_color( 'card_label_color', esc_html__( 'Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__card-label', '#6b7785' );
		$this->add_typo( 'card_label_typo', '{{WRAPPER}} .dgx-showcase__card-label', array( 'font_size' => $this->fs( 11 ) ) );

		$this->add_control(
			'heading_card_value',
			array(
				'label'     => esc_html__( 'Value', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'card_value_color', esc_html__( 'Value Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__card-value', '#0e1a26' );
		$this->add_typo(
			'card_value_typo',
			'{{WRAPPER}} .dgx-showcase__card-value',
			array(
				'font_size'   => $this->fs( 46 ),
				'font_weight' => array( 'default' => '400' ),
			)
		);

		$this->add_control(
			'heading_card_sub',
			array(
				'label'     => esc_html__( 'Sub Line', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'card_sub_color', esc_html__( 'Sub Line Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-showcase__card-sub', '#6b7785' );
		$this->add_typo( 'card_sub_typo', '{{WRAPPER}} .dgx-showcase__card-sub', array( 'font_size' => $this->fs( 13 ) ) );

		$this->add_position_controls( 'card', '{{WRAPPER}} .dgx-showcase__card', 'top-right', -22, 22, 0 );

		$this->end_controls_section();
	}

	// =======================================================================
	// RENDER
	// =======================================================================

	/**
	 * SVG arrow icon.
	 *
	 * @return string
	 */
	private function arrow_svg() {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$image     = ! empty( $s['image']['url'] ) ? $s['image']['url'] : '';
		$alt       = isset( $s['image_alt'] ) ? $s['image_alt'] : '';
		$show_over = 'yes' === ( isset( $s['show_overlay'] ) ? $s['show_overlay'] : '' );

		$show_badge = 'yes' === ( isset( $s['show_badge'] ) ? $s['show_badge'] : '' );
		$badge_dot  = 'yes' === ( isset( $s['badge_dot'] ) ? $s['badge_dot'] : '' );
		$badge_text = isset( $s['badge_text'] ) ? $s['badge_text'] : '';

		$show_stat       = 'yes' === ( isset( $s['show_stat'] ) ? $s['show_stat'] : '' );
		$stat_text       = isset( $s['stat_text'] ) ? $s['stat_text'] : '';
		$show_stat_label = 'yes' === ( isset( $s['show_stat_label'] ) ? $s['show_stat_label'] : '' );
		$stat_label_text = isset( $s['stat_label_text'] ) ? $s['stat_label_text'] : '';

		$show_chip  = 'yes' === ( isset( $s['show_chip'] ) ? $s['show_chip'] : '' );
		$chip_dot   = 'yes' === ( isset( $s['chip_dot'] ) ? $s['chip_dot'] : '' );
		$chip_text  = isset( $s['chip_text'] ) ? $s['chip_text'] : '';
		$chip_arrow = 'yes' === ( isset( $s['chip_arrow'] ) ? $s['chip_arrow'] : '' );
		$chip_link  = ! empty( $s['chip_link']['url'] ) ? $s['chip_link'] : array();

		$show_card  = 'yes' === ( isset( $s['show_card'] ) ? $s['show_card'] : '' );
		$card_label = isset( $s['card_label'] ) ? $s['card_label'] : '';
		$card_value = isset( $s['card_value'] ) ? $s['card_value'] : '';
		$card_sub   = isset( $s['card_sub'] ) ? $s['card_sub'] : '';

		$badge_anchor     = $this->anchor_class( $s, 'badge_anchor', 'top-left' );
		$stat_anchor      = $this->anchor_class( $s, 'stat_anchor', 'bottom-right' );
		$statlabel_anchor = $this->anchor_class( $s, 'statlabel_anchor', 'bottom-right' );
		$chip_anchor      = $this->anchor_class( $s, 'chip_anchor', 'bottom-left' );
		$card_anchor      = $this->anchor_class( $s, 'card_anchor', 'top-right' );
		?>
		<div class="dgx-showcase">
			<div class="dgx-showcase__media">
				<?php if ( '' !== $image ) : ?>
					<div class="dgx-showcase__img" role="img" aria-label="<?php echo esc_attr( $alt ); ?>" style="background-image: url('<?php echo esc_url( $image ); ?>');"></div>
				<?php else : ?>
					<span class="dgx-showcase__ph"><?php esc_html_e( 'Image', 'devgraphix-elementor-addons' ); ?></span>
				<?php endif; ?>
				<?php if ( $show_over ) : ?>
					<span class="dgx-showcase__overlay" aria-hidden="true"></span>
				<?php endif; ?>
			</div>

			<?php if ( $show_badge && ( $badge_dot || '' !== $badge_text ) ) : ?>
				<div class="dgx-showcase__badge <?php echo esc_attr( $badge_anchor ); ?>">
					<?php if ( $badge_dot ) : ?>
						<span class="dgx-showcase__badge-dot" aria-hidden="true"></span>
					<?php endif; ?>
					<?php if ( '' !== $badge_text ) : ?>
						<span class="dgx-showcase__badge-text"><?php echo esc_html( $badge_text ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_stat && '' !== $stat_text ) : ?>
				<div class="dgx-showcase__stat <?php echo esc_attr( $stat_anchor ); ?>"><?php echo esc_html( $stat_text ); ?></div>
				<?php if ( $show_stat_label && '' !== $stat_label_text ) : ?>
					<div class="dgx-showcase__stat-label <?php echo esc_attr( $statlabel_anchor ); ?>"><?php echo esc_html( $stat_label_text ); ?></div>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			if ( $show_chip && ( $chip_dot || '' !== $chip_text ) ) :
				$tag = 'div';
				if ( ! empty( $chip_link['url'] ) ) {
					$this->add_link_attributes( 'chip', $chip_link );
					$tag = 'a';
				}
				$this->add_render_attribute( 'chip', 'class', array( 'dgx-showcase__chip', $chip_anchor ) );
				?>
				<<?php echo esc_attr( $tag ); ?> <?php echo $this->get_render_attribute_string( 'chip' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php if ( $chip_dot ) : ?>
						<span class="dgx-showcase__chip-dot" aria-hidden="true"></span>
					<?php endif; ?>
					<?php if ( '' !== $chip_text ) : ?>
						<span class="dgx-showcase__chip-text"><?php echo esc_html( $chip_text ); ?></span>
					<?php endif; ?>
					<?php if ( $chip_arrow ) : ?>
						<span class="dgx-showcase__chip-arrow"><?php echo $this->arrow_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
				</<?php echo esc_attr( $tag ); ?>>
			<?php endif; ?>

			<?php if ( $show_card && ( '' !== $card_label || '' !== $card_value || '' !== $card_sub ) ) : ?>
				<div class="dgx-showcase__card <?php echo esc_attr( $card_anchor ); ?>">
					<?php if ( '' !== $card_label ) : ?>
						<span class="dgx-showcase__card-label"><?php echo esc_html( $card_label ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $card_value ) : ?>
						<span class="dgx-showcase__card-value"><?php echo esc_html( $card_value ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $card_sub ) : ?>
						<span class="dgx-showcase__card-sub"><?php echo esc_html( $card_sub ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
