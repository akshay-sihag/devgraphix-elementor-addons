<?php
/**
 * BMI Calculator widget.
 *
 * The interactive two-card BMI calculator core from homepage.html (lines
 * ~2140-2243): an Inputs card (unit toggle + height/weight range sliders +
 * disclaimer) beside a Result card (big BMI number + category pill + 4-band
 * scale w/ marker + a category recommendation + CTA button). No outer wrapper,
 * header or watermark — drop it into your own Elementor section/background.
 *
 * Every visible string is an Elementor control, all sizing / spacing controls
 * are responsive, and the two cards stack to one column on tablet / mobile. The
 * maths run client-side in bmi-calculator.js; PHP paints a fully correct
 * initial state so it also reads right with JS disabled and inside the editor.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BMI_Calculator
 */
class BMI_Calculator extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-bmi-calculator';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'BMI Calculator', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-bmi';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'bmi', 'calculator', 'body mass', 'health', 'fitness', 'weight', 'height', 'interactive' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-bmi-calculator' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-bmi-calculator' );
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
		// Content — Inputs
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_inputs',
			array(
				'label' => esc_html__( 'Inputs', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'measurements_label',
			array(
				'label'   => esc_html__( 'Card Label', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Your Measurements', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'show_units',
			array(
				'label'        => esc_html__( 'Show Unit Toggle', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'imperial_label',
			array(
				'label'     => esc_html__( 'Imperial Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Imperial', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_units' => 'yes' ),
			)
		);

		$this->add_control(
			'metric_label',
			array(
				'label'     => esc_html__( 'Metric Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Metric', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_units' => 'yes' ),
			)
		);

		$this->add_control(
			'default_unit',
			array(
				'label'   => esc_html__( 'Default Unit', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'imperial',
				'options' => array(
					'imperial' => esc_html__( 'Imperial (ft / lbs)', 'devgraphix-elementor-addons' ),
					'metric'   => esc_html__( 'Metric (cm / kg)', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'height_label',
			array(
				'label'     => esc_html__( 'Height Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Height', 'devgraphix-elementor-addons' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'weight_label',
			array(
				'label'   => esc_html__( 'Weight Label', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Weight', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'disclaimer_text',
			array(
				'label'     => esc_html__( 'Disclaimer', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.', 'devgraphix-elementor-addons' ),
				'separator' => 'before',
			)
		);

		// ---- Ranges (canonical inches / lbs; metric is converted for display) ----
		$this->add_control(
			'ranges_heading',
			array(
				'label'       => esc_html__( 'Slider Ranges', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::HEADING,
				'separator'   => 'before',
				'description' => esc_html__( 'Height in inches, weight in pounds. Metric values are converted automatically for display.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'height_min',
			array(
				'label'   => esc_html__( 'Height Min (in)', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 48,
				'min'     => 12,
				'max'     => 120,
			)
		);

		$this->add_control(
			'height_max',
			array(
				'label'   => esc_html__( 'Height Max (in)', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 84,
				'min'     => 12,
				'max'     => 120,
			)
		);

		$this->add_control(
			'height_default',
			array(
				'label'   => esc_html__( 'Height Default (in)', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 70,
				'min'     => 12,
				'max'     => 120,
			)
		);

		$this->add_control(
			'weight_min',
			array(
				'label'     => esc_html__( 'Weight Min (lb)', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 80,
				'min'       => 20,
				'max'       => 1000,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'weight_max',
			array(
				'label'   => esc_html__( 'Weight Max (lb)', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 400,
				'min'     => 20,
				'max'     => 1000,
			)
		);

		$this->add_control(
			'weight_default',
			array(
				'label'   => esc_html__( 'Weight Default (lb)', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 200,
				'min'     => 20,
				'max'     => 1000,
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Result
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_result',
			array(
				'label' => esc_html__( 'Result', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'result_label',
			array(
				'label'   => esc_html__( 'Result Label', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Your BMI', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'show_scale',
			array(
				'label'        => esc_html__( 'Show Scale Bar', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'scale_ticks',
			array(
				'label'       => esc_html__( 'Scale Ticks', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '<18.5, 18.5, 25, 30, 35+',
				'label_block' => true,
				'description' => esc_html__( 'Comma-separated numbers along the bar (low to high).', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_scale' => 'yes' ),
			)
		);

		$this->add_control(
			'scale_bands',
			array(
				'label'       => esc_html__( 'Band Labels', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Under, Normal, Over, Obese', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'description' => esc_html__( 'Comma-separated labels centred under the four colour bands.', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'show_scale' => 'yes' ),
			)
		);

		$this->add_control(
			'recommendation_heading',
			array(
				'label'     => esc_html__( 'Recommendation', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'rec_heading',
			array(
				'label'   => esc_html__( 'Recommendation Label', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'What This Means', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'cta_heading',
			array(
				'label'     => esc_html__( 'Button', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
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
				'default'   => esc_html__( 'Learn More', 'devgraphix-elementor-addons' ),
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
		// Content — Categories
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_categories',
			array(
				'label' => esc_html__( 'Categories', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'categories_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'The four standard BMI bands. Each band\'s name (shown in the result pill) and recommendation text update live as the sliders move.', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$bands = array(
			array( '1', esc_html__( 'Underweight', 'devgraphix-elementor-addons' ), esc_html__( 'Band 1 · below 18.5', 'devgraphix-elementor-addons' ) ),
			array( '2', esc_html__( 'Normal', 'devgraphix-elementor-addons' ), esc_html__( 'Band 2 · 18.5 to 25', 'devgraphix-elementor-addons' ) ),
			array( '3', esc_html__( 'Overweight', 'devgraphix-elementor-addons' ), esc_html__( 'Band 3 · 25 to 30', 'devgraphix-elementor-addons' ) ),
			array( '4', esc_html__( 'Obese', 'devgraphix-elementor-addons' ), esc_html__( 'Band 4 · 30 and above', 'devgraphix-elementor-addons' ) ),
		);

		foreach ( $bands as $band ) {
			list( $i, $name, $range ) = $band;

			$this->add_control(
				'cat' . $i . '_heading',
				array(
					'label'     => $range,
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'cat' . $i . '_name',
				array(
					'label'   => esc_html__( 'Name', 'devgraphix-elementor-addons' ),
					'type'    => Controls_Manager::TEXT,
					'default' => $name,
				)
			);

			$this->add_control(
				'cat' . $i . '_rec',
				array(
					'label'   => esc_html__( 'Recommendation', 'devgraphix-elementor-addons' ),
					'type'    => Controls_Manager::TEXTAREA,
					'rows'    => 3,
					'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore.', 'devgraphix-elementor-addons' ),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Style tab.
	 *
	 * @return void
	 */
	private function style_controls() {
		$root  = '{{WRAPPER}} .dgx-bmi';
		$white = '#ffffff';

		// -------------------------------------------------------------------
		// Style — Layout
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_layout',
			array(
				'label' => esc_html__( 'Layout', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'      => esc_html__( 'Columns Gap', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-bmi__grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Cards (inputs + result shells)
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_panels',
			array(
				'label' => esc_html__( 'Cards', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'panel_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__panel', $white, 'background-color' );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'panel_border', 'selector' => '{{WRAPPER}} .dgx-bmi__panel', 'fields_options' => array( 'border' => array( 'default' => 'solid' ), 'width' => array( 'default' => array( 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'isLinked' => true ) ), 'color' => array( 'default' => 'rgba(14,26,38,0.12)' ) ) ) );
		$this->add_slider( 'panel_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__panel', 'border-radius: {{SIZE}}{{UNIT}};', 24, 0, 60 );

		$this->add_responsive_control(
			'panel_padding',
			array(
				'label'          => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::DIMENSIONS,
				'size_units'     => array( 'px', 'em' ),
				'default'        => array( 'unit' => 'px', 'top' => 32, 'right' => 32, 'bottom' => 32, 'left' => 32, 'isLinked' => true ),
				'mobile_default' => array( 'unit' => 'px', 'top' => 22, 'right' => 22, 'bottom' => 22, 'left' => 22, 'isLinked' => true ),
				'selectors'      => array( '{{WRAPPER}} .dgx-bmi__panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Unit Toggle + Sliders
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_controls_block',
			array(
				'label' => esc_html__( 'Toggle & Sliders', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control( 'measure_label_heading', array( 'label' => esc_html__( 'Card Label', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING ) );
		$this->add_color( 'measure_label_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__panel-label', 'rgba(14,26,38,0.62)' );
		$this->add_typo( 'measure_label_typo', '{{WRAPPER}} .dgx-bmi__panel-label', array( 'font_size' => $this->fs( 10 ) ) );

		$this->add_control( 'unit_heading', array( 'label' => esc_html__( 'Unit Toggle', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => array( 'show_units' => 'yes' ) ) );
		$this->add_color( 'unit_track_bg', esc_html__( 'Track Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__units', '#efe9de', 'background-color', array( 'show_units' => 'yes' ) );
		$this->add_color( 'unit_active_bg', esc_html__( 'Active Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__unit.is-active', '#0e1a26', 'background-color', array( 'show_units' => 'yes' ) );
		$this->add_color( 'unit_active_color', esc_html__( 'Active Text', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__unit.is-active', $white, 'color', array( 'show_units' => 'yes' ) );
		$this->add_color( 'unit_color', esc_html__( 'Inactive Text', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__unit', 'rgba(14,26,38,0.62)', 'color', array( 'show_units' => 'yes' ) );
		$this->add_typo( 'unit_typo', '{{WRAPPER}} .dgx-bmi__unit', array( 'font_size' => $this->fs( 10 ) ) );

		$this->add_control( 'slider_label_heading', array( 'label' => esc_html__( 'Slider Labels', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'slider_label_color', esc_html__( 'Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__slider-label', '#0e1a26' );
		$this->add_typo( 'slider_label_typo', '{{WRAPPER}} .dgx-bmi__slider-label', array( 'font_size' => $this->fs( 11 ) ) );
		$this->add_color( 'slider_value_color', esc_html__( 'Value Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__slider-value', '#0e1a26' );
		$this->add_typo( 'slider_value_typo', '{{WRAPPER}} .dgx-bmi__slider-value', array( 'font_size' => $this->fs( 36 ), 'font_weight' => array( 'default' => '400' ) ) );
		$this->add_color( 'slider_minmax_color', esc_html__( 'Min / Max Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__slider-minmax', 'rgba(14,26,38,0.62)' );
		$this->add_typo( 'slider_minmax_typo', '{{WRAPPER}} .dgx-bmi__slider-minmax', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_control( 'slider_track_heading', array( 'label' => esc_html__( 'Slider Track', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'slider_fill', esc_html__( 'Fill Colour', 'devgraphix-elementor-addons' ), $root, '#435970', '--dgx-bmi-fill' );
		$this->add_color( 'slider_track', esc_html__( 'Track Colour', 'devgraphix-elementor-addons' ), $root, 'rgba(14,26,38,0.12)', '--dgx-bmi-track' );
		$this->add_color( 'slider_thumb', esc_html__( 'Thumb Colour', 'devgraphix-elementor-addons' ), $root, '#0e1a26', '--dgx-bmi-thumb' );

		$this->add_control( 'disclaimer_heading', array( 'label' => esc_html__( 'Disclaimer', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'disclaimer_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__disclaimer', 'rgba(14,26,38,0.62)' );
		$this->add_typo( 'disclaimer_typo', '{{WRAPPER}} .dgx-bmi__disclaimer', array( 'font_size' => $this->fs( 11 ), 'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 1.55 ) ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Result
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_result',
			array(
				'label' => esc_html__( 'Result', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control( 'result_label_heading', array( 'label' => esc_html__( 'Result Label', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING ) );
		$this->add_color( 'result_label_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__result-label', 'rgba(14,26,38,0.6)' );
		$this->add_typo( 'result_label_typo', '{{WRAPPER}} .dgx-bmi__result-label', array( 'font_size' => $this->fs( 11 ) ) );

		$this->add_control( 'number_heading', array( 'label' => esc_html__( 'BMI Number', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'number_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__number', '#0e1a26' );
		$this->add_typo( 'number_typo', '{{WRAPPER}} .dgx-bmi__number', array( 'font_size' => $this->fsr( 168, 130, 90 ), 'font_style' => array( 'default' => 'italic' ), 'font_weight' => array( 'default' => '300' ) ) );

		$this->add_control( 'cat_pill_heading', array( 'label' => esc_html__( 'Category Pill', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_typo( 'cat_pill_typo', '{{WRAPPER}} .dgx-bmi__cat', array( 'font_size' => $this->fs( 10 ) ) );
		$this->add_slider( 'cat_pill_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__cat', 'border-radius: {{SIZE}}{{UNIT}};', 999, 0, 999 );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Scale
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_scale',
			array(
				'label'     => esc_html__( 'Scale Bar', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_scale' => 'yes' ),
			)
		);

		$this->add_color( 'band1_color', esc_html__( 'Band 1 (Underweight)', 'devgraphix-elementor-addons' ), $root, '#7895b3', '--dgx-bmi-c0' );
		$this->add_color( 'band2_color', esc_html__( 'Band 2 (Normal)', 'devgraphix-elementor-addons' ), $root, '#1f8a5b', '--dgx-bmi-c1' );
		$this->add_color( 'band3_color', esc_html__( 'Band 3 (Overweight)', 'devgraphix-elementor-addons' ), $root, '#d4a017', '--dgx-bmi-c2' );
		$this->add_color( 'band4_color', esc_html__( 'Band 4 (Obese)', 'devgraphix-elementor-addons' ), $root, '#b34548', '--dgx-bmi-c3' );

		$this->add_slider( 'scale_height', esc_html__( 'Bar Height', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__bar', 'height: {{SIZE}}{{UNIT}};', 10, 4, 30 );
		$this->add_slider( 'marker_size', esc_html__( 'Marker Size', 'devgraphix-elementor-addons' ), $root, '--dgx-bmi-marker: {{SIZE}}{{UNIT}};', 22, 10, 40 );

		$this->add_control( 'tick_heading', array( 'label' => esc_html__( 'Tick Numbers', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'tick_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__ticks', 'rgba(14,26,38,0.6)' );
		$this->add_typo( 'tick_typo', '{{WRAPPER}} .dgx-bmi__ticks', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_control( 'band_label_heading', array( 'label' => esc_html__( 'Band Labels', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'band_label_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__bands', 'rgba(14,26,38,0.45)' );
		$this->add_typo( 'band_label_typo', '{{WRAPPER}} .dgx-bmi__bands', array( 'font_size' => $this->fs( 9 ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Recommendation
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'style_rec',
			array(
				'label' => esc_html__( 'Recommendation', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'rec_bg', esc_html__( 'Box Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__rec', 'rgba(255,255,255,0.5)', 'background-color' );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'rec_border', 'selector' => '{{WRAPPER}} .dgx-bmi__rec', 'fields_options' => array( 'border' => array( 'default' => 'solid' ), 'width' => array( 'default' => array( 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'isLinked' => true ) ), 'color' => array( 'default' => 'rgba(14,26,38,0.08)' ) ) ) );
		$this->add_slider( 'rec_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__rec', 'border-radius: {{SIZE}}{{UNIT}};', 16, 0, 40 );
		$this->add_responsive_control(
			'rec_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 18, 'right' => 18, 'bottom' => 18, 'left' => 18, 'isLinked' => true ),
				'selectors'  => array( '{{WRAPPER}} .dgx-bmi__rec' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control( 'rec_label_heading', array( 'label' => esc_html__( 'Label', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_typo( 'rec_label_typo', '{{WRAPPER}} .dgx-bmi__rec-label', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_control( 'rec_text_heading', array( 'label' => esc_html__( 'Text', 'devgraphix-elementor-addons' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'rec_text_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__rec-text', 'rgba(14,26,38,0.78)' );
		$this->add_typo( 'rec_text_typo', '{{WRAPPER}} .dgx-bmi__rec-text', array( 'font_size' => $this->fs( 14 ), 'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 1.55 ) ) ) );

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

		$this->add_color( 'cta_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__cta', '#0e1a26', 'background-color' );
		$this->add_color( 'cta_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__cta', $white );
		$this->add_color( 'cta_icon_color', esc_html__( 'Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__cta svg', $white, 'fill' );
		$this->add_typo( 'cta_typo', '{{WRAPPER}} .dgx-bmi__cta', array( 'font_size' => $this->fs( 14 ), 'font_weight' => array( 'default' => '600' ) ) );
		$this->add_slider( 'cta_radius', esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-bmi__cta', 'border-radius: {{SIZE}}{{UNIT}};', 999, 0, 999 );
		$this->add_responsive_control(
			'cta_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 16, 'right' => 22, 'bottom' => 16, 'left' => 22, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-bmi__cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	// =======================================================================
	// RENDER HELPERS
	// =======================================================================

	/**
	 * Clamp an int into a range.
	 *
	 * @param int $value Value.
	 * @param int $min   Min.
	 * @param int $max   Max.
	 * @return int
	 */
	private function clamp_int( $value, $min, $max ) {
		$value = (int) $value;
		if ( $min > $max ) {
			$tmp = $min;
			$min = $max;
			$max = $tmp;
		}
		return max( $min, min( $max, $value ) );
	}

	/**
	 * Format a height (canonical inches) for display in the chosen unit.
	 *
	 * @param int    $inches Height in inches.
	 * @param string $unit   'imperial' | 'metric'.
	 * @return string
	 */
	private function height_display( $inches, $unit ) {
		if ( 'metric' === $unit ) {
			return round( $inches * 2.54 ) . ' cm';
		}
		$ft = (int) floor( $inches / 12 );
		$in = $inches % 12;
		return $ft . "' " . $in . '"';
	}

	/**
	 * Format a weight (canonical pounds) for display in the chosen unit.
	 *
	 * @param int    $lb   Weight in pounds.
	 * @param string $unit 'imperial' | 'metric'.
	 * @return string
	 */
	private function weight_display( $lb, $unit ) {
		if ( 'metric' === $unit ) {
			return round( $lb / 2.205 ) . ' kg';
		}
		return $lb . ' lbs';
	}

	/**
	 * Resolve the BMI band index (0-3) for a BMI value.
	 *
	 * @param float $bmi BMI value.
	 * @return int
	 */
	private function band_index( $bmi ) {
		if ( $bmi < 18.5 ) {
			return 0;
		}
		if ( $bmi < 25 ) {
			return 1;
		}
		if ( $bmi < 30 ) {
			return 2;
		}
		return 3;
	}

	/**
	 * Marker position (0-1) on the 4-band scale, matching the JS formula.
	 *
	 * @param float $bmi BMI value.
	 * @return float
	 */
	private function marker_pos( $bmi ) {
		if ( $bmi < 18.5 ) {
			$pos = max( 0.02, ( $bmi / 18.5 ) * 0.25 );
		} elseif ( $bmi < 25 ) {
			$pos = 0.25 + ( ( $bmi - 18.5 ) / 6.5 ) * 0.25;
		} elseif ( $bmi < 30 ) {
			$pos = 0.50 + ( ( $bmi - 25 ) / 5 ) * 0.25;
		} else {
			$pos = 0.75 + min( ( $bmi - 30 ) / 15, 1 ) * 0.25;
		}
		return min( max( $pos, 0.01 ), 0.99 );
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

		// ---- ranges + defaults (canonical inches / lbs) ----
		$h_min = (int) ( '' !== $s['height_min'] ? $s['height_min'] : 48 );
		$h_max = (int) ( '' !== $s['height_max'] ? $s['height_max'] : 84 );
		$w_min = (int) ( '' !== $s['weight_min'] ? $s['weight_min'] : 80 );
		$w_max = (int) ( '' !== $s['weight_max'] ? $s['weight_max'] : 400 );
		if ( $h_min >= $h_max ) {
			$h_max = $h_min + 1;
		}
		if ( $w_min >= $w_max ) {
			$w_max = $w_min + 1;
		}

		$h_def = $this->clamp_int( '' !== $s['height_default'] ? $s['height_default'] : 70, $h_min, $h_max );
		$w_def = $this->clamp_int( '' !== $s['weight_default'] ? $s['weight_default'] : 200, $w_min, $w_max );

		$unit = in_array( $s['default_unit'], array( 'imperial', 'metric' ), true ) ? $s['default_unit'] : 'imperial';

		// ---- initial BMI maths ----
		$bmi      = ( $w_def * 703 ) / ( $h_def * $h_def );
		$bmi_disp = number_format( round( $bmi * 10 ) / 10, 1, '.', '' );
		$band     = $this->band_index( $bmi );
		$pos      = $this->marker_pos( $bmi );
		$h_pct    = ( ( $h_def - $h_min ) / ( $h_max - $h_min ) ) * 100;
		$w_pct    = ( ( $w_def - $w_min ) / ( $w_max - $w_min ) ) * 100;

		// ---- categories (names + recommendations) ----
		$cats = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$cats[] = array(
				'n' => isset( $s[ 'cat' . $i . '_name' ] ) ? $s[ 'cat' . $i . '_name' ] : '',
				'r' => isset( $s[ 'cat' . $i . '_rec' ] ) ? $s[ 'cat' . $i . '_rec' ] : '',
			);
		}

		$active_name = $cats[ $band ]['n'];
		$active_rec  = $cats[ $band ]['r'];

		// ---- min/max slider end labels for the initial unit ----
		$h_lo = $this->height_display( $h_min, $unit );
		$h_hi = $this->height_display( $h_max, $unit );
		$w_lo = $this->weight_display( $w_min, $unit );
		$w_hi = $this->weight_display( $w_max, $unit );

		// ---- scale ticks + band labels ----
		$ticks = array_filter( array_map( 'trim', explode( ',', (string) $s['scale_ticks'] ) ), 'strlen' );
		$bands = array_filter( array_map( 'trim', explode( ',', (string) $s['scale_bands'] ) ), 'strlen' );

		// ---- flags ----
		$show_units = 'yes' === $s['show_units'];
		$show_scale = 'yes' === $s['show_scale'];
		$show_cta   = 'yes' === $s['show_cta'];

		$fill_grad   = 'linear-gradient(to right, var(--dgx-bmi-fill) 0% ' . round( $h_pct, 2 ) . '%, var(--dgx-bmi-track) ' . round( $h_pct, 2 ) . '% 100%)';
		$fill_grad_w = 'linear-gradient(to right, var(--dgx-bmi-fill) 0% ' . round( $w_pct, 2 ) . '%, var(--dgx-bmi-track) ' . round( $w_pct, 2 ) . '% 100%)';
		$marker_left = round( $pos * 100, 2 ) . '%';

		// ---- CTA link ----
		$cta_link   = isset( $s['cta_link'] ) ? $s['cta_link'] : array();
		$cta_url    = isset( $cta_link['url'] ) ? $cta_link['url'] : '';
		$cta_target = ! empty( $cta_link['is_external'] ) ? ' target="_blank"' : '';
		$cta_rel    = ! empty( $cta_link['nofollow'] ) ? ' rel="nofollow"' : '';
		$cta_tag    = '' !== $cta_url ? 'a' : 'button';

		$data = array(
			'data-h-min' => $h_min,
			'data-h-max' => $h_max,
			'data-h-val' => $h_def,
			'data-w-min' => $w_min,
			'data-w-max' => $w_max,
			'data-w-val' => $w_def,
			'data-unit'  => $unit,
			'data-cats'  => wp_json_encode( $cats ),
		);
		$data_attr = '';
		foreach ( $data as $k => $v ) {
			$data_attr .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		?>
		<div class="dgx-bmi dgx-bmi--cat-<?php echo (int) $band; ?>"<?php echo $data_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="dgx-bmi__grid">

				<!-- Inputs -->
				<div class="dgx-bmi__panel dgx-bmi__inputs">
					<div class="dgx-bmi__inputs-head">
						<?php if ( '' !== $s['measurements_label'] ) : ?>
							<span class="dgx-bmi__panel-label"><?php echo esc_html( $s['measurements_label'] ); ?></span>
						<?php endif; ?>
						<?php if ( $show_units ) : ?>
							<div class="dgx-bmi__units" role="group">
								<button type="button" class="dgx-bmi__unit<?php echo 'imperial' === $unit ? ' is-active' : ''; ?>" data-unit="imperial"><?php echo esc_html( $s['imperial_label'] ); ?></button>
								<button type="button" class="dgx-bmi__unit<?php echo 'metric' === $unit ? ' is-active' : ''; ?>" data-unit="metric"><?php echo esc_html( $s['metric_label'] ); ?></button>
							</div>
						<?php endif; ?>
					</div>

					<!-- Height slider -->
					<div class="dgx-bmi__slider" data-axis="height">
						<div class="dgx-bmi__slider-head">
							<span class="dgx-bmi__slider-label"><?php echo esc_html( $s['height_label'] ); ?></span>
							<span class="dgx-bmi__slider-value" data-display="height"><?php echo esc_html( $this->height_display( $h_def, $unit ) ); ?></span>
						</div>
						<input type="range" class="dgx-bmi__range" data-range="height" min="<?php echo esc_attr( $h_min ); ?>" max="<?php echo esc_attr( $h_max ); ?>" step="1" value="<?php echo esc_attr( $h_def ); ?>" style="background: <?php echo esc_attr( $fill_grad ); ?>;" aria-label="<?php echo esc_attr( $s['height_label'] ); ?>">
						<div class="dgx-bmi__slider-minmax">
							<span data-minmax="height-min"><?php echo esc_html( $h_lo ); ?></span>
							<span data-minmax="height-max"><?php echo esc_html( $h_hi ); ?></span>
						</div>
					</div>

					<!-- Weight slider -->
					<div class="dgx-bmi__slider" data-axis="weight">
						<div class="dgx-bmi__slider-head">
							<span class="dgx-bmi__slider-label"><?php echo esc_html( $s['weight_label'] ); ?></span>
							<span class="dgx-bmi__slider-value" data-display="weight"><?php echo esc_html( $this->weight_display( $w_def, $unit ) ); ?></span>
						</div>
						<input type="range" class="dgx-bmi__range" data-range="weight" min="<?php echo esc_attr( $w_min ); ?>" max="<?php echo esc_attr( $w_max ); ?>" step="1" value="<?php echo esc_attr( $w_def ); ?>" style="background: <?php echo esc_attr( $fill_grad_w ); ?>;" aria-label="<?php echo esc_attr( $s['weight_label'] ); ?>">
						<div class="dgx-bmi__slider-minmax">
							<span data-minmax="weight-min"><?php echo esc_html( $w_lo ); ?></span>
							<span data-minmax="weight-max"><?php echo esc_html( $w_hi ); ?></span>
						</div>
					</div>

					<?php if ( '' !== $s['disclaimer_text'] ) : ?>
						<p class="dgx-bmi__disclaimer"><?php echo esc_html( $s['disclaimer_text'] ); ?></p>
					<?php endif; ?>
				</div>

				<!-- Result -->
				<div class="dgx-bmi__panel dgx-bmi__result">
					<div class="dgx-bmi__glow" aria-hidden="true"></div>

					<div class="dgx-bmi__result-head">
						<?php if ( '' !== $s['result_label'] ) : ?>
							<span class="dgx-bmi__result-label"><?php echo esc_html( $s['result_label'] ); ?></span>
						<?php endif; ?>
						<span class="dgx-bmi__cat" data-cat-name><?php echo esc_html( $active_name ); ?></span>
					</div>

					<div class="dgx-bmi__number" data-bmi aria-live="polite"><?php echo esc_html( $bmi_disp ); ?></div>

					<?php if ( $show_scale ) : ?>
						<div class="dgx-bmi__scale">
							<div class="dgx-bmi__bar">
								<span class="dgx-bmi__band dgx-bmi__band--0"></span>
								<span class="dgx-bmi__band dgx-bmi__band--1"></span>
								<span class="dgx-bmi__band dgx-bmi__band--2"></span>
								<span class="dgx-bmi__band dgx-bmi__band--3"></span>
								<span class="dgx-bmi__marker" data-marker style="left: <?php echo esc_attr( $marker_left ); ?>;"></span>
							</div>
							<?php if ( ! empty( $ticks ) ) : ?>
								<div class="dgx-bmi__ticks">
									<?php foreach ( $ticks as $tick ) : ?>
										<span><?php echo esc_html( $tick ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $bands ) ) : ?>
								<div class="dgx-bmi__bands">
									<?php foreach ( $bands as $band_label ) : ?>
										<span><?php echo esc_html( $band_label ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="dgx-bmi__rec">
						<?php if ( '' !== $s['rec_heading'] ) : ?>
							<span class="dgx-bmi__rec-label"><?php echo esc_html( $s['rec_heading'] ); ?></span>
						<?php endif; ?>
						<p class="dgx-bmi__rec-text" data-rec><?php echo esc_html( $active_rec ); ?></p>
					</div>

					<?php if ( $show_cta && '' !== $s['cta_text'] ) : ?>
						<<?php echo esc_attr( $cta_tag ); ?> class="dgx-bmi__cta"<?php echo 'a' === $cta_tag ? ' href="' . esc_url( $cta_url ) . '"' . $cta_target . $cta_rel : ' type="button"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<span><?php echo esc_html( $s['cta_text'] ); ?></span>
							<?php if ( ! empty( $s['cta_icon']['value'] ) ) : ?>
								<?php Icons_Manager::render_icon( $s['cta_icon'], array( 'aria-hidden' => 'true' ) ); ?>
							<?php endif; ?>
						</<?php echo esc_attr( $cta_tag ); ?>>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
