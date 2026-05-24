<?php
/**
 * Comparison Table widget.
 *
 * Unlimited side-by-side comparison columns. Any column can be highlighted
 * into an elevated card and given a ribbon badge. Each column lists its own
 * rows (one per line) supporting a label, a value, an optional check/cross
 * icon, and an optional chip. Per-column background + accent colours. The
 * widget renders columns only — no wrapper background — so you can drop it on
 * your own section and add your own background + padding.
 *
 * Row syntax (one per line):  Label | Value | Chip
 *   - Start Value with "+ " for a check icon, "- " for a cross icon.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Comparison_Table
 */
class Comparison_Table extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-comparison-table';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Comparison Table', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-compare';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'comparison', 'compare', 'table', 'pricing', 'vs', 'columns' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-comparison-table' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-comparison-table' );
	}

	// =======================================================================
	// HELPERS
	// =======================================================================

	/**
	 * Register a simple colour control.
	 *
	 * @param string $id       Control id.
	 * @param string $label    Label.
	 * @param string $selector Full CSS selector.
	 * @param string $default  Default colour.
	 * @param string $prop     CSS property.
	 * @return void
	 */
	private function add_color( $id, $label, $selector, $default, $prop = 'color' ) {
		$this->add_control(
			$id,
			array(
				'label'     => $label,
				'type'      => Controls_Manager::COLOR,
				'default'   => $default,
				'selectors' => array( $selector => $prop . ': {{VALUE}};' ),
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

	/**
	 * Default columns (generic placeholder content).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function default_columns() {
		return array(
			array(
				'col_name'      => esc_html__( 'Lorem', 'devgraphix-elementor-addons' ),
				'col_highlight' => 'yes',
				'col_ribbon'    => esc_html__( 'Most Chosen', 'devgraphix-elementor-addons' ),
				'col_price'     => '$29',
			),
			array(
				'col_name'         => esc_html__( 'Others', 'devgraphix-elementor-addons' ),
				'col_subtitle'     => esc_html__( 'Lorem ipsum', 'devgraphix-elementor-addons' ),
				'col_price'        => '$99',
				'col_price_strike' => 'yes',
			),
		);
	}

	/**
	 * Default rows for the Rows repeater (generic placeholder content).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function default_rows() {
		$check = array( 'value' => 'fas fa-check', 'library' => 'fa-solid' );
		$cross = array( 'value' => 'fas fa-times', 'library' => 'fa-solid' );

		return array(
			// Column 1.
			array( 'row_column' => 1, 'row_caption' => esc_html__( 'FEATURE ONE', 'devgraphix-elementor-addons' ), 'row_value' => '$0', 'row_chip' => esc_html__( 'LOREM IPSUM', 'devgraphix-elementor-addons' ) ),
			array( 'row_column' => 1, 'row_caption' => esc_html__( 'FEATURE TWO', 'devgraphix-elementor-addons' ), 'row_value' => esc_html__( 'Lorem ipsum', 'devgraphix-elementor-addons' ), 'row_chip' => esc_html__( 'DOLOR SIT', 'devgraphix-elementor-addons' ) ),
			array( 'row_column' => 1, 'row_caption' => esc_html__( 'FEATURE THREE', 'devgraphix-elementor-addons' ), 'row_value' => esc_html__( '3–5 Days', 'devgraphix-elementor-addons' ), 'row_chip' => esc_html__( 'CONSECTETUR', 'devgraphix-elementor-addons' ) ),
			array( 'row_column' => 1, 'row_caption' => esc_html__( 'FEATURE FOUR', 'devgraphix-elementor-addons' ), 'row_value' => esc_html__( 'Included', 'devgraphix-elementor-addons' ), 'row_icon' => $check ),
			array( 'row_column' => 1, 'row_caption' => esc_html__( 'FEATURE FIVE', 'devgraphix-elementor-addons' ), 'row_value' => esc_html__( 'Included', 'devgraphix-elementor-addons' ), 'row_icon' => $check ),
			// Column 2.
			array( 'row_column' => 2, 'row_caption' => 'VS', 'row_value' => esc_html__( 'Up to $00', 'devgraphix-elementor-addons' ) ),
			array( 'row_column' => 2, 'row_caption' => 'VS', 'row_value' => esc_html__( 'Lorem ipsum', 'devgraphix-elementor-addons' ) ),
			array( 'row_column' => 2, 'row_caption' => 'VS', 'row_value' => esc_html__( '10–17 Days', 'devgraphix-elementor-addons' ) ),
			array( 'row_column' => 2, 'row_caption' => 'VS', 'row_value' => esc_html__( 'Not included', 'devgraphix-elementor-addons' ), 'row_icon' => $cross ),
			array( 'row_column' => 2, 'row_caption' => 'VS', 'row_value' => esc_html__( 'Included', 'devgraphix-elementor-addons' ), 'row_icon' => $check ),
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
		// Content — Columns
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_columns',
			array(
				'label' => esc_html__( 'Columns', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$col = new Repeater();

		$col->add_control(
			'col_name',
			array(
				'label'       => esc_html__( 'Name', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$col->add_control(
			'col_logo',
			array(
				'label'       => esc_html__( 'Logo (replaces name)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
			)
		);

		$col->add_control(
			'col_subtitle',
			array(
				'label'       => esc_html__( 'Subtitle', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$col->add_control(
			'col_highlight',
			array(
				'label'        => esc_html__( 'Highlight This Column', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$col->add_control(
			'col_ribbon',
			array(
				'label'       => esc_html__( 'Ribbon Text', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Leave empty for no ribbon.', 'devgraphix-elementor-addons' ),
			)
		);

		$col->add_control(
			'col_ribbon_icon',
			array(
				'label'     => esc_html__( 'Ribbon Icon', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'condition' => array( 'col_ribbon!' => '' ),
			)
		);

		$col->add_control(
			'col_show_price',
			array(
				'label'        => esc_html__( 'Show Price', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$col->add_control(
			'col_price_label',
			array(
				'label'     => esc_html__( 'Price Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'FROM', 'devgraphix-elementor-addons' ),
				'condition' => array( 'col_show_price' => 'yes' ),
			)
		);

		$col->add_control(
			'col_price',
			array(
				'label'     => esc_html__( 'Price', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '$00',
				'condition' => array( 'col_show_price' => 'yes' ),
			)
		);

		$col->add_control(
			'col_price_suffix',
			array(
				'label'     => esc_html__( 'Price Suffix', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '/mo',
				'condition' => array( 'col_show_price' => 'yes' ),
			)
		);

		$col->add_control(
			'col_price_strike',
			array(
				'label'        => esc_html__( 'Strike-through Price', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'col_show_price' => 'yes' ),
			)
		);

		$col->add_control(
			'col_rows',
			array(
				'label'       => esc_html__( 'Rows (legacy text)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => '',
				'description'  => esc_html__( 'Optional fallback, used only if this column has no rows in the "Rows" section below. Prefer that section — add rows one by one with caption, value, chip and a real icon picker. Legacy syntax: Caption | Value | Chip, with "+"/"-"/[name] icon tokens.', 'devgraphix-elementor-addons' ),
			)
		);

		$col->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'        => 'col_bg',
				'label'       => esc_html__( 'Column Background', 'devgraphix-elementor-addons' ),
				'types'       => array( 'classic', 'gradient' ),
				'exclude'     => array( 'image' ),
				'selector'    => '{{WRAPPER}} {{CURRENT_ITEM}} .dgx-cmp__bg',
			)
		);

		$col->add_control(
			'col_accent',
			array(
				'label'     => esc_html__( 'Accent (chips / pill)', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => '--cmp-accent: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'       => esc_html__( 'Columns', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $col->get_controls(),
				'default'     => $this->default_columns(),
				'title_field' => '{{{ col_name }}}',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Rows
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_rows',
			array(
				'label' => esc_html__( 'Rows', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'rows_info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Add rows one at a time. Each row picks the column it belongs to (Column 1 is the first column above, Column 2 the second, etc.).', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$row = new Repeater();

		$row->add_control(
			'row_column',
			array(
				'label'       => esc_html__( 'Column', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 1,
				'step'        => 1,
				'description' => esc_html__( 'Which column this row belongs to (1 = the first column above, 2 = the second, …).', 'devgraphix-elementor-addons' ),
			)
		);

		$row->add_control(
			'row_caption',
			array(
				'label'       => esc_html__( 'Caption', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Small label above the value (optional).', 'devgraphix-elementor-addons' ),
			)
		);

		$row->add_control(
			'row_value',
			array(
				'label'       => esc_html__( 'Value', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$row->add_control(
			'row_chip',
			array(
				'label'       => esc_html__( 'Chip', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Small pill shown after the value (optional).', 'devgraphix-elementor-addons' ),
			)
		);

		$row->add_control(
			'row_icon',
			array(
				'label' => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$row->add_control(
			'row_icon_color',
			array(
				'label'     => esc_html__( 'Icon Colour', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .dgx-cmp__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'rows',
			array(
				'label'       => esc_html__( 'Rows', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $row->get_controls(),
				'default'     => $this->default_rows(),
				'title_field' => 'Col {{{ row_column }}} — {{{ row_value }}}{{{ row_caption }}}',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Layout
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => esc_html__( 'Gap Between Columns', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-cmp' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'chip_arrow',
			array(
				'label'        => esc_html__( 'Chip Up-Arrow', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Header
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_header',
			array(
				'label' => esc_html__( 'Header', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'name_color', esc_html__( 'Name Colour (highlight)', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__col--highlight .dgx-cmp__name', '#0e1a26' );
		$this->add_color( 'name_color_muted', esc_html__( 'Name Colour (others)', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__col:not(.dgx-cmp__col--highlight) .dgx-cmp__name', 'rgba(14,26,38,0.55)' );
		$this->add_typo( 'name_typo', '{{WRAPPER}} .dgx-cmp__name', array( 'font_size' => $this->fs( 34 ), 'font_weight' => array( 'default' => '400' ) ) );

		$this->add_control(
			'heading_price',
			array(
				'label'     => esc_html__( 'Price', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_typo( 'price_typo', '{{WRAPPER}} .dgx-cmp__price-val', array( 'font_size' => $this->fs( 28 ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Rows
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_rows',
			array(
				'label' => esc_html__( 'Rows', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'label_color', esc_html__( 'Row Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__row-label', 'rgba(14,26,38,0.4)' );
		$this->add_typo( 'label_typo', '{{WRAPPER}} .dgx-cmp__row-label', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_control(
			'heading_values',
			array(
				'label'     => esc_html__( 'Values', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'val_color', esc_html__( 'Value Colour (highlight)', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__col--highlight .dgx-cmp__val', '#0e1a26' );
		$this->add_typo( 'val_typo', '{{WRAPPER}} .dgx-cmp__col--highlight .dgx-cmp__val', array( 'font_size' => $this->fs( 28 ), 'font_weight' => array( 'default' => '400' ) ) );

		$this->add_color( 'val_color_muted', esc_html__( 'Value Colour (others)', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__col:not(.dgx-cmp__col--highlight) .dgx-cmp__val', 'rgba(14,26,38,0.55)' );
		$this->add_typo( 'val_typo_muted', '{{WRAPPER}} .dgx-cmp__col:not(.dgx-cmp__col--highlight) .dgx-cmp__val', array( 'font_size' => $this->fs( 18 ) ) );

		$this->add_control(
			'heading_chip',
			array(
				'label'     => esc_html__( 'Chip', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_typo( 'chip_typo', '{{WRAPPER}} .dgx-cmp__chip', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_control(
			'heading_icons',
			array(
				'label'     => esc_html__( 'Row Icons', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'ico_pos', esc_html__( 'Check Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp', 'rgba(14,26,38,0.5)', '--cmp-ico-pos' );
		$this->add_color( 'ico_neg', esc_html__( 'Cross Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp', 'rgba(14,26,38,0.4)', '--cmp-ico-neg' );
		$this->add_color( 'ico_other', esc_html__( 'Other Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp', '', '--cmp-ico-other' );
		$this->add_color( 'ico_bg', esc_html__( 'Icon Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp', 'rgba(14,26,38,0.06)', '--cmp-ico-bg' );

		$this->add_control(
			'heading_icons_hl',
			array(
				'label'     => esc_html__( 'Row Icons (highlight column)', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'ico_hl_color', esc_html__( 'Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp', '#ffffff', '--cmp-ico-hl-color' );
		$this->add_color( 'ico_hl_bg', esc_html__( 'Icon Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp', '#0e1a26', '--cmp-ico-hl-bg' );

		$this->add_responsive_control(
			'row_spacing',
			array(
				'label'      => esc_html__( 'Row Padding (Y)', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 4, 'max' => 48 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 20 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-cmp__row' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Highlight Card
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_highlight',
			array(
				'label' => esc_html__( 'Highlight Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'highlight_bg_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'A cream gradient is applied to the highlighted column by default. To change it (solid colour OR your own gradient), open Columns → that column → Column Background.', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_responsive_control(
			'highlight_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 28 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-cmp__col--highlight' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'highlight_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array( 'unit' => 'px', 'top' => 36, 'right' => 36, 'bottom' => 28, 'left' => 36, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-cmp__col--highlight' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'col_padding',
			array(
				'label'      => esc_html__( 'Column Padding (others)', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array( 'unit' => 'px', 'top' => 36, 'right' => 28, 'bottom' => 28, 'left' => 28, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-cmp__col:not(.dgx-cmp__col--highlight)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'rule_color',
			array(
				'label'     => esc_html__( 'Divider Lines', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(14,26,38,0.12)',
				'selectors' => array( '{{WRAPPER}} .dgx-cmp' => '--cmp-rule: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Ribbon
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_ribbon',
			array(
				'label' => esc_html__( 'Ribbon', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'ribbon_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__ribbon', '#0e1a26', 'background-color' );
		$this->add_color( 'ribbon_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__ribbon', '#ffffff' );
		$this->add_color( 'ribbon_icon_color', esc_html__( 'Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-cmp__ribbon-icon', '#f6c453' );
		$this->add_typo( 'ribbon_typo', '{{WRAPPER}} .dgx-cmp__ribbon', array( 'font_size' => $this->fs( 10 ) ) );

		$this->end_controls_section();
	}

	// =======================================================================
	// RENDER
	// =======================================================================

	/**
	 * Inline SVG for a named row icon. Returns '' for unknown names.
	 *
	 * @param string $name Icon name.
	 * @return string
	 */
	private function row_icon_svg( $name ) {
		$o = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
		$c = '</svg>';
		$f = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">';

		switch ( $name ) {
			case 'check':
				return $o . '<path d="M20 6L9 17l-5-5"/>' . $c;
			case 'cross':
				return $o . '<path d="M18 6L6 18M6 6l12 12"/>' . $c;
			case 'plus':
				return $o . '<path d="M12 5v14M5 12h14"/>' . $c;
			case 'minus':
				return $o . '<path d="M5 12h14"/>' . $c;
			case 'arrow':
				return $o . '<path d="M5 12h14M13 6l6 6-6 6"/>' . $c;
			case 'star':
				return $f . '<path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 21.2l1.4-6.8L2.2 9.7l6.9-.7z"/>' . $c;
			case 'heart':
				return $f . '<path d="M12 21c-6-4-9-7-9-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 4-3 7-9 11Z"/>' . $c;
			case 'shield':
				return $o . '<path d="M12 3 4 6v6c0 4 3 7 8 9 5-2 8-5 8-9V6z"/><path d="m9 12 2.5 2.5L16 10"/>' . $c;
			case 'clock':
				return $o . '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>' . $c;
			case 'bolt':
				return $f . '<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>' . $c;
			case 'lock':
				return $o . '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>' . $c;
			case 'dollar':
				return $o . '<path d="M12 2v20M17 6.5C17 4.6 14.8 3.5 12 3.5S7 4.6 7 6.5 9.2 9.5 12 9.5s5 1.1 5 3-2.2 3-5 3-5-1.1-5-3"/>' . $c;
			case 'gift':
				return $o . '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8h14v-8M12 8v12M12 8S10 3 7.5 4.5 9 8 12 8Zm0 0s2-5 4.5-3.5S15 8 12 8Z"/>' . $c;
			case 'truck':
				return $o . '<rect x="2" y="7" width="13" height="9" rx="1"/><path d="M15 10h4l2 3v3h-6"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>' . $c;
			case 'info':
				return $o . '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>' . $c;
			case 'dot':
				return $f . '<circle cx="12" cy="12" r="5"/>' . $c;
		}

		return '';
	}

	/**
	 * Map an icon name to a colour group ('check' | 'cross' | 'other').
	 *
	 * @param string $name Icon name.
	 * @return string
	 */
	private function icon_group( $name ) {
		if ( 'check' === $name || 'plus' === $name ) {
			return 'check';
		}
		if ( 'cross' === $name || 'minus' === $name ) {
			return 'cross';
		}
		return 'other';
	}

	/**
	 * Parse a column's rows textarea into structured rows.
	 *
	 * Each non-empty line is "Label | Value | Chip" — every part optional. A
	 * leading icon token on the Value sets an icon: "+" check, "-" cross, or a
	 * named "[token]".
	 *
	 * @param string $text Raw textarea.
	 * @return array<int,array<string,string>>
	 */
	private function parse_rows( $text ) {
		$rows = array();

		foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}

			$parts = explode( '|', $line );

			if ( count( $parts ) === 1 ) {
				// A single entry is the row's main value (not a tiny caption),
				// so typing one thing "just works".
				$label = '';
				$value = trim( $parts[0] );
				$chip  = '';
			} else {
				$label = trim( $parts[0] );
				$value = isset( $parts[1] ) ? trim( $parts[1] ) : '';
				$chip  = isset( $parts[2] ) ? trim( $parts[2] ) : '';
			}

			$icon = '';

			if ( 0 === strpos( $value, '+ ' ) || '+' === $value ) {
				$icon  = 'check';
				$value = trim( substr( $value, 1 ) );
			} elseif ( 0 === strpos( $value, '- ' ) || '-' === $value ) {
				$icon  = 'cross';
				$value = trim( substr( $value, 1 ) );
			} elseif ( preg_match( '/^\[([a-z]+)\]\s*(.*)$/i', $value, $m ) ) {
				$candidate = strtolower( $m[1] );
				if ( '' !== $this->row_icon_svg( $candidate ) ) {
					$icon  = $candidate;
					$value = trim( $m[2] );
				}
			}

			$rows[] = array(
				'label' => $label,
				'value' => $value,
				'chip'  => $chip,
				'icon'  => $icon,
			);
		}

		return $rows;
	}

	/**
	 * Normalise rows from the Rows repeater into the render structure.
	 *
	 * @param array<int,array<string,mixed>> $items Repeater items for one column.
	 * @return array<int,array<string,string>>
	 */
	private function normalize_repeater_rows( array $items ) {
		$out = array();

		foreach ( $items as $it ) {
			$icon_html = '';
			if ( ! empty( $it['row_icon']['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $it['row_icon'], array( 'aria-hidden' => 'true' ) );
				$icon_html = ob_get_clean();
			}

			$out[] = array(
				'caption'    => isset( $it['row_caption'] ) ? $it['row_caption'] : '',
				'value'      => isset( $it['row_value'] ) ? $it['row_value'] : '',
				'chip'       => isset( $it['row_chip'] ) ? $it['row_chip'] : '',
				'icon_html'  => $icon_html,
				'icon_group' => 'other',
				'item_id'    => isset( $it['_id'] ) ? $it['_id'] : '',
			);
		}

		return $out;
	}

	/**
	 * Normalise the legacy rows textarea into the render structure.
	 *
	 * @param string $text Raw textarea.
	 * @return array<int,array<string,string>>
	 */
	private function normalize_legacy_rows( $text ) {
		$out = array();

		foreach ( $this->parse_rows( $text ) as $row ) {
			$out[] = array(
				'caption'    => $row['label'],
				'value'      => $row['value'],
				'chip'       => $row['chip'],
				'icon_html'  => '' !== $row['icon'] ? $this->row_icon_svg( $row['icon'] ) : '',
				'icon_group' => '' !== $row['icon'] ? $this->icon_group( $row['icon'] ) : 'other',
				'item_id'    => '',
			);
		}

		return $out;
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$s       = $this->get_settings_for_display();
		$columns = ! empty( $s['columns'] ) ? $s['columns'] : array();

		if ( empty( $columns ) ) {
			return;
		}

		// Group the Rows repeater by the column number each row targets (1-based).
		$rows_by_col = array();
		foreach ( ( ! empty( $s['rows'] ) ? $s['rows'] : array() ) as $r ) {
			$ci = isset( $r['row_column'] ) ? max( 1, (int) $r['row_column'] ) : 1;
			$rows_by_col[ $ci ][] = $r;
		}

		$classes = array( 'dgx-cmp' );
		if ( 'yes' === ( isset( $s['chip_arrow'] ) ? $s['chip_arrow'] : '' ) ) {
			$classes[] = 'dgx-cmp--arrow';
		}

		$col_index = 0;
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php foreach ( $columns as $col ) : ?>
				<?php
				$col_index++;

				$is_highlight = 'yes' === ( isset( $col['col_highlight'] ) ? $col['col_highlight'] : '' );
				$col_classes  = array( 'dgx-cmp__col' );
				if ( $is_highlight ) {
					$col_classes[] = 'dgx-cmp__col--highlight';
				}
				if ( ! empty( $col['_id'] ) ) {
					$col_classes[] = 'elementor-repeater-item-' . $col['_id'];
				}

				$ribbon = isset( $col['col_ribbon'] ) ? trim( (string) $col['col_ribbon'] ) : '';
				$logo   = ! empty( $col['col_logo']['url'] ) ? $col['col_logo']['url'] : '';
				$name   = isset( $col['col_name'] ) ? $col['col_name'] : '';
				$sub    = isset( $col['col_subtitle'] ) ? $col['col_subtitle'] : '';

				// Prefer the Rows repeater for this column; fall back to legacy text.
				if ( ! empty( $rows_by_col[ $col_index ] ) ) {
					$rows = $this->normalize_repeater_rows( $rows_by_col[ $col_index ] );
				} else {
					$rows = $this->normalize_legacy_rows( isset( $col['col_rows'] ) ? $col['col_rows'] : '' );
				}

				$show_price   = 'yes' === ( isset( $col['col_show_price'] ) ? $col['col_show_price'] : '' );
				$price        = isset( $col['col_price'] ) ? $col['col_price'] : '';
				$price_label  = isset( $col['col_price_label'] ) ? $col['col_price_label'] : '';
				$price_suffix = isset( $col['col_price_suffix'] ) ? $col['col_price_suffix'] : '';
				$price_strike = 'yes' === ( isset( $col['col_price_strike'] ) ? $col['col_price_strike'] : '' );
				?>
				<div class="<?php echo esc_attr( implode( ' ', $col_classes ) ); ?>">
					<span class="dgx-cmp__bg" aria-hidden="true"></span>
					<?php if ( '' !== $ribbon ) : ?>
						<span class="dgx-cmp__ribbon">
							<?php if ( ! empty( $col['col_ribbon_icon']['value'] ) ) : ?>
								<span class="dgx-cmp__ribbon-icon"><?php Icons_Manager::render_icon( $col['col_ribbon_icon'], array( 'aria-hidden' => 'true' ) ); ?></span>
							<?php endif; ?>
							<?php echo esc_html( $ribbon ); ?>
						</span>
					<?php endif; ?>

					<div class="dgx-cmp__head">
						<div class="dgx-cmp__brand">
							<?php if ( '' !== $logo ) : ?>
								<img class="dgx-cmp__logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
							<?php elseif ( '' !== $name ) : ?>
								<div class="dgx-cmp__name"><?php echo esc_html( $name ); ?></div>
							<?php endif; ?>
							<?php if ( '' !== $sub ) : ?>
								<div class="dgx-cmp__subtitle"><?php echo esc_html( $sub ); ?></div>
							<?php endif; ?>
						</div>

						<?php if ( $show_price && ( '' !== $price || '' !== $price_label ) ) : ?>
							<div class="dgx-cmp__price<?php echo $price_strike ? ' dgx-cmp__price--strike' : ''; ?>">
								<?php if ( '' !== $price_label ) : ?>
									<span class="dgx-cmp__price-label"><?php echo esc_html( $price_label ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $price ) : ?>
									<span class="dgx-cmp__price-val"><?php echo esc_html( $price ); ?></span><?php if ( '' !== $price_suffix ) : ?><span class="dgx-cmp__price-suffix"><?php echo esc_html( $price_suffix ); ?></span><?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $rows ) ) : ?>
						<div class="dgx-cmp__rows">
							<?php foreach ( $rows as $row ) : ?>
								<?php
								$has_icon  = '' !== $row['icon_html'];
								$has_value = ( $has_icon || '' !== $row['value'] || '' !== $row['chip'] );

								$row_class = array( 'dgx-cmp__row' );
								if ( $has_icon ) {
									$row_class[] = 'dgx-cmp__row--icon';
								}
								if ( '' !== $row['item_id'] ) {
									$row_class[] = 'elementor-repeater-item-' . $row['item_id'];
								}
								?>
								<div class="<?php echo esc_attr( implode( ' ', $row_class ) ); ?>">
									<?php if ( '' !== $row['caption'] ) : ?>
										<div class="dgx-cmp__row-label"><?php echo esc_html( $row['caption'] ); ?></div>
									<?php endif; ?>
									<?php if ( $has_value ) : ?>
										<div class="dgx-cmp__row-value">
											<?php if ( $has_icon ) : ?>
												<span class="dgx-cmp__icon dgx-cmp__icon--<?php echo esc_attr( $row['icon_group'] ); ?>"><?php echo $row['icon_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
											<?php endif; ?>
											<?php if ( '' !== $row['value'] ) : ?>
												<span class="dgx-cmp__val"><?php echo esc_html( $row['value'] ); ?></span>
											<?php endif; ?>
											<?php if ( '' !== $row['chip'] ) : ?>
												<span class="dgx-cmp__chip"><?php echo esc_html( $row['chip'] ); ?></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
