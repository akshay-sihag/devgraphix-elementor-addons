<?php
/**
 * Savings Calculator widget.
 *
 * An interactive cost-comparison / savings calculator, built pixel-for-pixel
 * from the savings-calculator card in homepage.html (lines ~2631-2818):
 *   - LEFT: an eyebrow pill, a serif headline whose dynamic amount = the yearly
 *     savings, a "what are you paying now?" range slider, and a yearly
 *     comparison strip (our cost vs. their struck-through current cost),
 *   - RIGHT: a glass breakdown card (heading + check-mark rows where any row's
 *     value can be the live monthly-savings figure) and a CTA button,
 *   - a centred footnote beneath the card.
 *
 * Every visible string is an Elementor control, all sizing / spacing controls
 * are responsive, and the layout stacks to one column on tablet / mobile. The
 * maths run client-side in savings-calculator.js; PHP paints a correct initial
 * state so it also reads right with JS disabled and inside the editor.
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
 * Class Savings_Calculator
 */
class Savings_Calculator extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-savings-calculator';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Savings Calculator', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-savings';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'savings', 'calculator', 'cost', 'compare', 'comparison', 'price', 'subscription', 'interactive', 'slider' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-savings-calculator' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-savings-calculator' );
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
	 * @param string              $default   Default colour ('' for none).
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
	private function add_typo( $id, $selector, array $fields_options = array() ) {
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
	 * Shorthand for a fixed px font-size fields_options entry.
	 *
	 * @param int $size Font size.
	 * @return array<string,mixed>
	 */
	private function fs( $size ) {
		return array( 'default' => array( 'unit' => 'px', 'size' => $size ) );
	}

	/**
	 * Shorthand for a responsive px font-size entry (desktop / tablet / mobile).
	 *
	 * @param int $d Desktop size.
	 * @param int $t Tablet size.
	 * @param int $m Mobile size.
	 * @return array<string,mixed>
	 */
	private function fsr( $d, $t, $m ) {
		return array(
			'default'        => array( 'unit' => 'px', 'size' => $d ),
			'tablet_default' => array( 'unit' => 'px', 'size' => $t ),
			'mobile_default' => array( 'unit' => 'px', 'size' => $m ),
		);
	}

	/**
	 * Register a responsive slider control.
	 *
	 * @param string              $id        Control id.
	 * @param string              $label     Label.
	 * @param string              $selector  Full CSS selector.
	 * @param string              $prop      CSS declaration template.
	 * @param int                 $default   Default size.
	 * @param int                 $min       Range min.
	 * @param int                 $max       Range max.
	 * @param string[]            $units     Size units.
	 * @param array<string,mixed> $condition Condition.
	 * @return void
	 */
	private function add_slider( $id, $label, $selector, $prop, $default, $min = 0, $max = 200, array $units = array( 'px' ), array $condition = array() ) {
		$this->add_responsive_control(
			$id,
			array(
				'label'      => $label,
				'type'       => Controls_Manager::SLIDER,
				'size_units' => $units,
				'range'      => array(
					'px' => array( 'min' => $min, 'max' => $max ),
					'%'  => array( 'min' => 0, 'max' => 100 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => $default ),
				'selectors'  => array( $selector => $prop ),
				'condition'  => $condition,
			)
		);
	}

	/**
	 * Format a number as a currency string. Mirrors the JS `fmt()`.
	 *
	 * @param float  $n        Number.
	 * @param string $currency Currency symbol/prefix.
	 * @return string
	 */
	private function fmt( $n, $currency ) {
		return $currency . number_format( round( (float) $n ) );
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
		$this->content_controls();
		$this->style_controls();
	}

	/**
	 * Content tab.
	 *
	 * @return void
	 */
	private function content_controls() {

		// -------------------------------------------------------------------
		// Content — Header
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_header',
			array(
				'label' => esc_html__( 'Header', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_eyebrow',
			array(
				'label'        => esc_html__( 'Show Eyebrow', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'eyebrow_icon_char',
			array(
				'label'       => esc_html__( 'Eyebrow Icon (character)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '◐',
				'description' => esc_html__( 'A small symbol shown before the eyebrow. Leave empty to hide.', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_control(
			'eyebrow_text',
			array(
				'label'       => esc_html__( 'Eyebrow', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Savings Calculator', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'show_eyebrow' => 'yes' ),
			)
		);

		$this->add_control(
			'headline_prefix',
			array(
				'label'       => esc_html__( 'Headline — Before Amount', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Save', 'devgraphix-elementor-addons' ),
				'description' => esc_html__( 'The live yearly-savings amount is inserted after this.', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'headline_suffix',
			array(
				'label'   => esc_html__( 'Headline — After Amount', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '/year', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'headline_line2',
			array(
				'label'       => esc_html__( 'Headline — Second Line', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'with us.', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'headline_tag',
			array(
				'label'   => esc_html__( 'Headline HTML Tag', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'div' => 'div',
				),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Slider
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_slider',
			array(
				'label' => esc_html__( 'Slider', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'slider_label',
			array(
				'label'       => esc_html__( 'Slider Label', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'What are you paying now?', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'currency',
			array(
				'label'   => esc_html__( 'Currency Symbol', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '$',
			)
		);

		$this->add_control(
			'per_month_suffix',
			array(
				'label'   => esc_html__( 'Per-Month Suffix', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '/mo', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'our_price',
			array(
				'label'       => esc_html__( 'Our Price (per month)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 129,
				'min'         => 0,
				'description' => esc_html__( 'Your monthly price. Multiplied by 12 for the yearly figure; savings = customer\'s yearly cost − yours.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'slider_min',
			array(
				'label'       => esc_html__( 'Slider Min (per month)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 150,
				'min'         => 0,
				'separator'   => 'before',
				'description' => esc_html__( 'The lowest monthly price a customer can select.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'slider_max',
			array(
				'label'   => esc_html__( 'Slider Max (per month)', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 1200,
				'min'     => 1,
			)
		);

		$this->add_control(
			'slider_step',
			array(
				'label'   => esc_html__( 'Slider Step', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 10,
				'min'     => 1,
			)
		);

		$this->add_control(
			'slider_default',
			array(
				'label'       => esc_html__( 'Slider Default (per month)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 499,
				'min'         => 0,
				'description' => esc_html__( 'The monthly amount the slider starts on.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'show_minmax',
			array(
				'label'        => esc_html__( 'Show Min / Max Labels', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Comparison Strip
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_strip',
			array(
				'label' => esc_html__( 'Comparison Strip', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_strip',
			array(
				'label'        => esc_html__( 'Show Comparison Strip', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'ours_label',
			array(
				'label'     => esc_html__( 'Our Cost Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'You Pay With Us', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_strip' => 'yes' ),
			)
		);

		$this->add_control(
			'per_year_suffix',
			array(
				'label'     => esc_html__( 'Per-Year Suffix', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( '/year', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_strip' => 'yes' ),
			)
		);

		$this->add_control(
			'vs_text',
			array(
				'label'     => esc_html__( 'Versus Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'vs', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_strip' => 'yes' ),
			)
		);

		$this->add_control(
			'current_label',
			array(
				'label'     => esc_html__( 'Current Cost Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Current Cost', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_strip' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Breakdown
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_breakdown',
			array(
				'label' => esc_html__( 'Breakdown', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_breakdown',
			array(
				'label'        => esc_html__( 'Show Breakdown Card', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'breakdown_heading',
			array(
				'label'     => esc_html__( 'Breakdown Heading', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( "Here's How It Breaks Down", 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_breakdown' => 'yes' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'row_label',
			array(
				'label'       => esc_html__( 'Label', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'row_dynamic',
			array(
				'label'        => esc_html__( 'Use Calculated Savings', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Replace the value with the live monthly-savings figure.', 'devgraphix-elementor-addons' ),
			)
		);

		$repeater->add_control(
			'row_value',
			array(
				'label'       => esc_html__( 'Value', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Included', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'row_dynamic!' => 'yes' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'breakdown_rows',
			array(
				'label'       => esc_html__( 'Rows', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'row_label' => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ), 'row_dynamic' => 'yes', 'row_value' => '' ),
					array( 'row_label' => esc_html__( 'Consectetur adipiscing', 'devgraphix-elementor-addons' ), 'row_dynamic' => '', 'row_value' => esc_html__( 'Included', 'devgraphix-elementor-addons' ) ),
					array( 'row_label' => esc_html__( 'Sed do eiusmod tempor', 'devgraphix-elementor-addons' ), 'row_dynamic' => '', 'row_value' => '−$20/mo' ),
					array( 'row_label' => esc_html__( 'Incididunt ut labore', 'devgraphix-elementor-addons' ), 'row_dynamic' => '', 'row_value' => esc_html__( 'Included', 'devgraphix-elementor-addons' ) ),
				),
				'title_field' => '{{{ row_label }}}',
				'condition'   => array( 'show_breakdown' => 'yes' ),
			)
		);

		$this->add_control(
			'zero_text',
			array(
				'label'       => esc_html__( 'Zero-Savings Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Match', 'devgraphix-elementor-addons' ),
				'description' => esc_html__( 'Shown on calculated rows when there are no savings (current price ≤ your price).', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_breakdown' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Button
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_cta',
			array(
				'label' => esc_html__( 'Button', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_cta',
			array(
				'label'        => esc_html__( 'Show Button', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'     => esc_html__( 'Button Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Get Started', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'cta_icon',
			array(
				'label'     => esc_html__( 'Button Icon', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'eicon-arrow-right',
					'library' => 'eicons',
				),
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'cta_link',
			array(
				'label'       => esc_html__( 'Button Link', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'devgraphix-elementor-addons' ),
				'default'     => array( 'url' => '#' ),
				'condition'   => array( 'show_cta' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Footnote
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_footnote',
			array(
				'label' => esc_html__( 'Footnote', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_footnote',
			array(
				'label'        => esc_html__( 'Show Footnote', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'footnote_text',
			array(
				'label'     => esc_html__( 'Footnote', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 2,
				'default'   => esc_html__( 'Lorem ipsum dolor sit amet · consectetur adipiscing elit', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_footnote' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab.
	 *
	 * @return void
	 */
	private function style_controls() {
		$root = '{{WRAPPER}} .dgx-sav';

		// -------------------------------------------------------------------
		// Style — Card
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_card',
			array(
				'label' => esc_html__( 'Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'accent_color', esc_html__( 'Accent Colour', 'devgraphix-elementor-addons' ), $root, '#bcd3e8', '--dgx-sav-accent' );
		$this->add_color( 'card_bg', esc_html__( 'Card Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__card', '#0e1a26', 'background-color' );
		$this->add_slider( 'card_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__card', 'border-radius: {{SIZE}}{{UNIT}};', 28, 0, 60 );

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'          => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::DIMENSIONS,
				'size_units'     => array( 'px', 'em', '%' ),
				'default'        => array( 'unit' => 'px', 'top' => 44, 'right' => 48, 'bottom' => 44, 'left' => 48, 'isLinked' => false ),
				'tablet_default' => array( 'unit' => 'px', 'top' => 36, 'right' => 36, 'bottom' => 36, 'left' => 36, 'isLinked' => true ),
				'mobile_default' => array( 'unit' => 'px', 'top' => 28, 'right' => 24, 'bottom' => 28, 'left' => 24, 'isLinked' => false ),
				'selectors'      => array( '{{WRAPPER}} .dgx-sav__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'      => esc_html__( 'Columns Gap', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 48 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-sav__grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'show_glow',
			array(
				'label'        => esc_html__( 'Show Accent Glows', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Header
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_header',
			array(
				'label' => esc_html__( 'Header', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control( 'eyebrow_style_heading', array( 'label' => esc_html__( 'Eyebrow', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'condition' => array( 'show_eyebrow' => 'yes' ) ) );
		$this->add_color( 'eyebrow_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__eyebrow', '', 'color', array( 'show_eyebrow' => 'yes' ) );
		$this->add_typo( 'eyebrow_typo', '{{WRAPPER}} .dgx-sav__eyebrow-text', array( 'font_size' => $this->fs( 10 ) ) );

		$this->add_control( 'headline_style_heading', array( 'label' => esc_html__( 'Headline', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'headline_color', esc_html__( 'Headline Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__headline', '#ffffff' );
		$this->add_color( 'amount_color', esc_html__( 'Amount Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__amount', '', 'color' );
		$this->add_color( 'headline_suffix_color', esc_html__( 'Suffix Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__h-suffix', 'rgba(255,255,255,0.85)' );
		$this->add_color( 'headline_line2_color', esc_html__( 'Second Line Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__h-line2', 'rgba(255,255,255,0.78)' );
		$this->add_typo( 'headline_typo', '{{WRAPPER}} .dgx-sav__headline', array( 'font_size' => $this->fsr( 60, 46, 34 ), 'font_weight' => array( 'default' => '300' ), 'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 0.98 ) ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Slider
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_slider',
			array(
				'label' => esc_html__( 'Slider', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'slider_label_color', esc_html__( 'Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__slider-label', 'rgba(255,255,255,0.65)' );
		$this->add_typo( 'slider_label_typo', '{{WRAPPER}} .dgx-sav__slider-label', array( 'font_size' => $this->fs( 10 ) ) );
		$this->add_color( 'slider_value_color', esc_html__( 'Value Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__slider-value', '#ffffff' );
		$this->add_typo( 'slider_value_typo', '{{WRAPPER}} .dgx-sav__slider-value', array( 'font_size' => $this->fs( 22 ), 'font_style' => array( 'default' => 'italic' ) ) );

		$this->add_control( 'track_style_heading', array( 'label' => esc_html__( 'Track', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'track_color', esc_html__( 'Track Colour', 'devgraphix-elementor-addons' ), $root, 'rgba(255,255,255,0.12)', '--dgx-sav-track' );
		$this->add_color( 'minmax_color', esc_html__( 'Min / Max Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__minmax', 'rgba(255,255,255,0.4)', 'color', array( 'show_minmax' => 'yes' ) );
		$this->add_typo( 'minmax_typo', '{{WRAPPER}} .dgx-sav__minmax', array( 'font_size' => $this->fs( 9 ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Comparison Strip
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_strip',
			array(
				'label'     => esc_html__( 'Comparison Strip', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_strip' => 'yes' ),
			)
		);

		$this->add_color( 'strip_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__strip', 'rgba(255,255,255,0.06)', 'background-color' );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'strip_border', 'selector' => '{{WRAPPER}} .dgx-sav__strip', 'fields_options' => array( 'border' => array( 'default' => 'solid' ), 'width' => array( 'default' => array( 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'isLinked' => true ) ), 'color' => array( 'default' => 'rgba(255,255,255,0.12)' ) ) ) );
		$this->add_slider( 'strip_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__strip', 'border-radius: {{SIZE}}{{UNIT}};', 14, 0, 40 );
		$this->add_color( 'strip_label_color', esc_html__( 'Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__strip-label--muted', 'rgba(255,255,255,0.45)' );
		$this->add_color( 'strip_value_color', esc_html__( 'Our Value Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__strip-val--ours', '#ffffff' );
		$this->add_color( 'strip_current_color', esc_html__( 'Current Cost Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__strip-val--current', 'rgba(255,255,255,0.5)' );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Breakdown
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_breakdown',
			array(
				'label'     => esc_html__( 'Breakdown', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_breakdown' => 'yes' ),
			)
		);

		$this->add_color( 'breakdown_bg', esc_html__( 'Card Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__breakdown', 'rgba(255,255,255,0.05)', 'background-color' );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'breakdown_border', 'selector' => '{{WRAPPER}} .dgx-sav__breakdown', 'fields_options' => array( 'border' => array( 'default' => 'solid' ), 'width' => array( 'default' => array( 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'isLinked' => true ) ), 'color' => array( 'default' => 'rgba(255,255,255,0.1)' ) ) ) );
		$this->add_slider( 'breakdown_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__breakdown', 'border-radius: {{SIZE}}{{UNIT}};', 18, 0, 40 );

		$this->add_control( 'breakdown_head_heading', array( 'label' => esc_html__( 'Heading', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'breakdown_head_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__breakdown-head', 'rgba(255,255,255,0.55)' );
		$this->add_typo( 'breakdown_head_typo', '{{WRAPPER}} .dgx-sav__breakdown-head', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_control( 'breakdown_row_heading', array( 'label' => esc_html__( 'Rows', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'row_label_color', esc_html__( 'Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__row-label', 'rgba(255,255,255,0.88)' );
		$this->add_color( 'row_value_color', esc_html__( 'Value Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__row-val', '' );
		$this->add_color( 'row_divider_color', esc_html__( 'Divider Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__row', 'rgba(255,255,255,0.08)', 'border-bottom-color' );
		$this->add_typo( 'row_label_typo', '{{WRAPPER}} .dgx-sav__row-label', array( 'font_size' => $this->fs( 14 ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Button
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_cta',
			array(
				'label'     => esc_html__( 'Button', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_color( 'cta_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__cta', '#ffffff', 'background-color' );
		$this->add_color( 'cta_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__cta', '#0e1a26' );
		$this->add_color( 'cta_icon_color', esc_html__( 'Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__cta svg', '#0e1a26', 'fill' );
		$this->add_typo( 'cta_typo', '{{WRAPPER}} .dgx-sav__cta', array( 'font_size' => $this->fs( 14 ), 'font_weight' => array( 'default' => '600' ) ) );
		$this->add_slider( 'cta_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__cta', 'border-radius: {{SIZE}}{{UNIT}};', 999, 0, 999 );
		$this->add_responsive_control(
			'cta_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 14, 'right' => 22, 'bottom' => 14, 'left' => 22, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-sav__cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Footnote
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_footnote',
			array(
				'label'     => esc_html__( 'Footnote', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_footnote' => 'yes' ),
			)
		);

		$this->add_color( 'footnote_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-sav__footnote', 'rgba(14,26,38,0.62)' );
		$this->add_typo( 'footnote_typo', '{{WRAPPER}} .dgx-sav__footnote', array( 'font_size' => $this->fs( 10 ) ) );

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

		$currency  = isset( $s['currency'] ) ? $s['currency'] : '$';
		$per_month = isset( $s['per_month_suffix'] ) ? $s['per_month_suffix'] : '';
		$per_year  = isset( $s['per_year_suffix'] ) ? $s['per_year_suffix'] : '';
		$zero_text = isset( $s['zero_text'] ) ? $s['zero_text'] : '';

		// ---- numeric config ----
		$min  = (int) ( '' !== $s['slider_min'] ? $s['slider_min'] : 50 );
		$max  = (int) ( '' !== $s['slider_max'] ? $s['slider_max'] : 1000 );
		$step = (int) ( '' !== $s['slider_step'] ? $s['slider_step'] : 10 );
		$our  = (int) ( '' !== $s['our_price'] ? $s['our_price'] : 99 );
		if ( $min >= $max ) {
			$max = $min + 1;
		}
		if ( $step < 1 ) {
			$step = 1;
		}
		$cur = (int) ( '' !== $s['slider_default'] ? $s['slider_default'] : 399 );
		$cur = max( $min, min( $max, $cur ) );

		// ---- maths ----
		$monthly_savings = max( 0, $cur - $our );
		$yearly_savings  = $monthly_savings * 12;
		$ours_yearly     = $our * 12;
		$current_yearly  = $cur * 12;
		$fill_pct        = round( ( ( $cur - $min ) / ( $max - $min ) ) * 100, 2 );
		$fill_grad       = 'linear-gradient(to right, var(--dgx-sav-accent) 0% ' . $fill_pct . '%, var(--dgx-sav-track) ' . $fill_pct . '% 100%)';

		// ---- dynamic breakdown value (matches JS) ----
		$dynamic_value = $monthly_savings > 0 ? '−' . $this->fmt( $monthly_savings, $currency ) . $per_month : $zero_text;

		// ---- flags ----
		$show_eyebrow   = 'yes' === $s['show_eyebrow'];
		$show_minmax    = 'yes' === $s['show_minmax'];
		$show_strip     = 'yes' === $s['show_strip'];
		$show_breakdown = 'yes' === $s['show_breakdown'];
		$show_cta       = 'yes' === $s['show_cta'];
		$show_footnote  = 'yes' === $s['show_footnote'];
		$show_glow      = 'yes' === $s['show_glow'];

		$icon_char = isset( $s['eyebrow_icon_char'] ) ? $s['eyebrow_icon_char'] : '';
		$tag       = in_array( $s['headline_tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['headline_tag'] : 'h3';

		// ---- CTA link ----
		$cta_link   = isset( $s['cta_link'] ) ? $s['cta_link'] : array();
		$cta_url    = isset( $cta_link['url'] ) ? $cta_link['url'] : '';
		$cta_target = ! empty( $cta_link['is_external'] ) ? ' target="_blank"' : '';
		$cta_rel    = ! empty( $cta_link['nofollow'] ) ? ' rel="nofollow"' : '';
		$cta_tag    = '' !== $cta_url ? 'a' : 'button';

		// ---- root data attributes ----
		$data = array(
			'data-our'       => $our,
			'data-min'       => $min,
			'data-max'       => $max,
			'data-step'      => $step,
			'data-val'       => $cur,
			'data-currency'  => $currency,
			'data-permonth'  => $per_month,
			'data-zero'      => $zero_text,
		);
		$data_attr = '';
		foreach ( $data as $k => $v ) {
			$data_attr .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		?>
		<div class="dgx-sav"<?php echo $data_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="dgx-sav__card">

				<?php if ( $show_glow ) : ?>
					<span class="dgx-sav__glow dgx-sav__glow--a" aria-hidden="true"></span>
					<span class="dgx-sav__glow dgx-sav__glow--b" aria-hidden="true"></span>
				<?php endif; ?>

				<div class="dgx-sav__grid">

					<!-- LEFT -->
					<div class="dgx-sav__main">
						<?php if ( $show_eyebrow && ( '' !== $s['eyebrow_text'] || '' !== $icon_char ) ) : ?>
							<span class="dgx-sav__eyebrow">
								<?php if ( '' !== $icon_char ) : ?>
									<span class="dgx-sav__eyebrow-icon" aria-hidden="true"><?php echo esc_html( $icon_char ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $s['eyebrow_text'] ) : ?>
									<span class="dgx-sav__eyebrow-text"><?php echo esc_html( $s['eyebrow_text'] ); ?></span>
								<?php endif; ?>
							</span>
						<?php endif; ?>

						<<?php echo esc_attr( $tag ); ?> class="dgx-sav__headline">
							<?php echo esc_html( $s['headline_prefix'] ); ?>
							<em class="dgx-sav__amount" data-yearly><?php echo esc_html( $this->fmt( $yearly_savings, $currency ) ); ?></em><span class="dgx-sav__h-suffix"><?php echo esc_html( $s['headline_suffix'] ); ?></span>
							<?php if ( '' !== $s['headline_line2'] ) : ?>
								<br><span class="dgx-sav__h-line2"><?php echo esc_html( $s['headline_line2'] ); ?></span>
							<?php endif; ?>
						</<?php echo esc_attr( $tag ); ?>>

						<!-- Slider -->
						<div class="dgx-sav__slider">
							<div class="dgx-sav__slider-head">
								<span class="dgx-sav__slider-label"><?php echo esc_html( $s['slider_label'] ); ?></span>
								<span class="dgx-sav__slider-value"><span data-cur><?php echo esc_html( $this->fmt( $cur, $currency ) ); ?></span><span class="dgx-sav__per"><?php echo esc_html( $per_month ); ?></span></span>
							</div>
							<input type="range" class="dgx-sav__range" data-range min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>" value="<?php echo esc_attr( $cur ); ?>" style="background: <?php echo esc_attr( $fill_grad ); ?>;" aria-label="<?php echo esc_attr( $s['slider_label'] ); ?>">
							<?php if ( $show_minmax ) : ?>
								<div class="dgx-sav__minmax">
									<span><?php echo esc_html( $this->fmt( $min, $currency ) ); ?></span>
									<span><?php echo esc_html( $this->fmt( $max, $currency ) ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Comparison strip -->
						<?php if ( $show_strip ) : ?>
							<div class="dgx-sav__strip">
								<div class="dgx-sav__strip-col">
									<span class="dgx-sav__strip-label dgx-sav__strip-label--ours"><?php echo esc_html( $s['ours_label'] ); ?></span>
									<span class="dgx-sav__strip-val dgx-sav__strip-val--ours"><span><?php echo esc_html( $this->fmt( $ours_yearly, $currency ) ); ?></span><span class="dgx-sav__per2"><?php echo esc_html( $per_year ); ?></span></span>
								</div>
								<span class="dgx-sav__vs"><?php echo esc_html( $s['vs_text'] ); ?></span>
								<div class="dgx-sav__strip-col">
									<span class="dgx-sav__strip-label dgx-sav__strip-label--muted"><?php echo esc_html( $s['current_label'] ); ?></span>
									<span class="dgx-sav__strip-val dgx-sav__strip-val--current"><span data-current><?php echo esc_html( $this->fmt( $current_yearly, $currency ) ); ?></span><span class="dgx-sav__per2"><?php echo esc_html( $per_year ); ?></span></span>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<!-- RIGHT -->
					<?php if ( $show_breakdown ) : ?>
						<div class="dgx-sav__breakdown">
							<?php if ( '' !== $s['breakdown_heading'] ) : ?>
								<div class="dgx-sav__breakdown-head"><?php echo esc_html( $s['breakdown_heading'] ); ?></div>
							<?php endif; ?>

							<?php
							$rows = isset( $s['breakdown_rows'] ) && is_array( $s['breakdown_rows'] ) ? $s['breakdown_rows'] : array();
							if ( ! empty( $rows ) ) :
								?>
								<div class="dgx-sav__rows">
									<?php
									foreach ( $rows as $row ) :
										$is_dynamic = isset( $row['row_dynamic'] ) && 'yes' === $row['row_dynamic'];
										$value      = $is_dynamic ? $dynamic_value : ( isset( $row['row_value'] ) ? $row['row_value'] : '' );
										?>
										<div class="dgx-sav__row">
											<span class="dgx-sav__row-main">
												<span class="dgx-sav__row-check" aria-hidden="true">
													<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</span>
												<span class="dgx-sav__row-label"><?php echo esc_html( isset( $row['row_label'] ) ? $row['row_label'] : '' ); ?></span>
											</span>
											<span class="dgx-sav__row-val"<?php echo $is_dynamic ? ' data-dynamic' : ''; ?>><?php echo esc_html( $value ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( $show_cta && '' !== $s['cta_text'] ) : ?>
								<<?php echo esc_attr( $cta_tag ); ?> class="dgx-sav__cta"<?php echo 'a' === $cta_tag ? ' href="' . esc_url( $cta_url ) . '"' . $cta_target . $cta_rel : ' type="button"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									<span><?php echo esc_html( $s['cta_text'] ); ?></span>
									<?php if ( ! empty( $s['cta_icon']['value'] ) ) : ?>
										<?php Icons_Manager::render_icon( $s['cta_icon'], array( 'aria-hidden' => 'true' ) ); ?>
									<?php endif; ?>
								</<?php echo esc_attr( $cta_tag ); ?>>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div>
			</div>

			<?php if ( $show_footnote && '' !== $s['footnote_text'] ) : ?>
				<p class="dgx-sav__footnote"><?php echo esc_html( $s['footnote_text'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
