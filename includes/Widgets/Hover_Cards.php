<?php
/**
 * Hover Cards widget.
 *
 * A tonal card (icon, decorative numeral, heading, description, hairline
 * divider, footer label + arrow) that reveals a background image with a
 * gradient scrim on hover. On hover the card lifts, the image fades in while
 * zooming out, the text turns white and the arrow slides right.
 *
 * Defaults reproduce the reference design pixel-for-pixel (Fraunces serif,
 * Inter Tight body, DM Mono label). Everything is overridable in both Normal
 * and Hover states, with several content layouts.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Hover_Cards
 */
class Hover_Cards extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-hover-cards';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Hover Cards', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-hover';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'card', 'hover', 'image', 'overlay', 'box', 'step', 'reveal' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * Fonts are intentionally not enqueued here — set heading/body/label
	 * typography via the Style tab (Elementor global fonts). The stylesheet
	 * only provides graceful serif/sans/mono fallback stacks.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-hover-card' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	// =======================================================================
	// CONTENT CONTROLS
	// =======================================================================

	/**
	 * Register all Content-tab controls.
	 *
	 * @return void
	 */
	private function register_content_controls() {

		// ---- Layout -------------------------------------------------------
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'content_layout',
			array(
				'label'       => esc_html__( 'Content Layout', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => array(
					'default'  => esc_html__( 'Standard (top-aligned)', 'devgraphix-elementor-addons' ),
					'centered' => esc_html__( 'Centered', 'devgraphix-elementor-addons' ),
					'bottom'   => esc_html__( 'Bottom aligned', 'devgraphix-elementor-addons' ),
					'reveal'   => esc_html__( 'Reveal on hover', 'devgraphix-elementor-addons' ),
				),
				'description' => esc_html__( 'How the content is arranged inside the card.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'hover_zoom',
			array(
				'label'        => esc_html__( 'Zoom Image on Hover', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Slow scale-down of the hover image (1.08 → 1).', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'card_link',
			array(
				'label'       => esc_html__( 'Card Link', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'devgraphix-elementor-addons' ),
				'description' => esc_html__( 'Optional. Makes the whole card clickable.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->end_controls_section();

		// ---- Icon ---------------------------------------------------------
		$this->start_controls_section(
			'section_icon',
			array(
				'label' => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_icon',
			array(
				'label'        => esc_html__( 'Show Icon', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'     => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'far fa-file-alt',
					'library' => 'fa-regular',
				),
				'condition' => array( 'show_icon' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// ---- Number badge -------------------------------------------------
		$this->start_controls_section(
			'section_number',
			array(
				'label' => esc_html__( 'Number', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_number',
			array(
				'label'        => esc_html__( 'Show Number', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'number_text',
			array(
				'label'     => esc_html__( 'Number / Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '01',
				'condition' => array( 'show_number' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// ---- Content (heading + description) ------------------------------
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Content', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'   => esc_html__( 'Heading', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'   => esc_html__( 'Heading HTML Tag', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'   => esc_html__( 'Description', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->end_controls_section();

		// ---- Footer (divider + label + arrow) -----------------------------
		$this->start_controls_section(
			'section_footer',
			array(
				'label' => esc_html__( 'Footer', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_footer',
			array(
				'label'        => esc_html__( 'Show Footer', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_divider',
			array(
				'label'        => esc_html__( 'Show Divider', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'show_footer' => 'yes' ),
			)
		);

		$this->add_control(
			'footer_label',
			array(
				'label'     => esc_html__( 'Footer Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Lorem ipsum', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_footer' => 'yes' ),
			)
		);

		$this->add_control(
			'show_arrow',
			array(
				'label'        => esc_html__( 'Show Arrow', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'show_footer' => 'yes' ),
			)
		);

		$this->add_control(
			'arrow_icon',
			array(
				'label'     => esc_html__( 'Arrow Icon', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				),
				'skin'      => 'inline',
				'condition' => array(
					'show_footer' => 'yes',
					'show_arrow'  => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// ---- Hover image --------------------------------------------------
		$this->start_controls_section(
			'section_hover_image',
			array(
				'label' => esc_html__( 'Hover Image', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'hover_image',
			array(
				'label'       => esc_html__( 'Background Image (on hover)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => esc_html__( 'Revealed behind the content on hover. Style the gradient scrim under Style → Card → Hover.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->end_controls_section();
	}

	// =======================================================================
	// STYLE CONTROLS
	// =======================================================================

	/**
	 * Register all Style-tab controls.
	 *
	 * @return void
	 */
	private function register_style_controls() {
		$this->register_card_style();
		$this->register_icon_style();
		$this->register_number_style();
		$this->register_heading_style();
		$this->register_description_style();
		$this->register_divider_style();
		$this->register_label_style();
		$this->register_arrow_style();
	}

	/**
	 * Card box: size, spacing, border, shadow, lift + Normal/Hover backgrounds.
	 *
	 * @return void
	 */
	private function register_card_style() {
		$this->start_controls_section(
			'style_card',
			array(
				'label' => esc_html__( 'Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'card_min_height',
			array(
				'label'      => esc_html__( 'Min Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'min' => 200, 'max' => 900 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 440 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => 40,
					'right'    => 32,
					'bottom'   => 32,
					'left'     => 32,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => 24,
					'right'    => 24,
					'bottom'   => 24,
					'left'     => 24,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'card_border',
				'selector'       => '{{WRAPPER}} .dgx-hover-card',
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
					'color'  => array( 'default' => 'rgba(14, 26, 38, 0.06)' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .dgx-hover-card',
			)
		);

		$this->add_responsive_control(
			'hover_lift',
			array(
				'label'      => esc_html__( 'Hover Lift', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 4 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover' => 'transform: translateY(-{{SIZE}}{{UNIT}});',
				),
			)
		);

		// Normal / Hover background.
		$this->start_controls_tabs( 'card_bg_tabs' );

		$this->start_controls_tab(
			'card_bg_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'devgraphix-elementor-addons' ) )
		);

		$this->add_control(
			'card_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#EAF1F8',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'card_bg_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'devgraphix-elementor-addons' ) )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'scrim',
				'label'    => esc_html__( 'Image Scrim', 'devgraphix-elementor-addons' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .dgx-hover-card__scrim',
			)
		);

		$this->add_control(
			'scrim_hint',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Defaults to a top-to-bottom dark gradient. Choose Gradient or Classic to customize.', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Icon styling.
	 *
	 * @return void
	 */
	private function register_icon_style() {
		$this->start_controls_section(
			'style_icon',
			array(
				'label'     => esc_html__( 'Icon', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_icon' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 22 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dgx-hover-card__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_color_hover_tabs(
			array(
				'id'               => 'icon',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => '#435970',
				'default_hover'    => 'rgba(255, 255, 255, 0.85)',
				'normal_selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__icon' => 'color: {{VALUE}};',
				),
				'hover_selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Number badge styling.
	 *
	 * @return void
	 */
	private function register_number_style() {
		$this->start_controls_section(
			'style_number',
			array(
				'label'     => esc_html__( 'Number', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_number' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'number_typography',
				'selector' => '{{WRAPPER}} .dgx-hover-card__number',
			)
		);

		$this->add_color_hover_tabs(
			array(
				'id'               => 'number',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => 'rgba(67, 89, 112, 0.13)',
				'default_hover'    => 'rgba(255, 255, 255, 0.22)',
				'normal_selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__number' => 'color: {{VALUE}};',
				),
				'hover_selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__number' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Heading styling.
	 *
	 * @return void
	 */
	private function register_heading_style() {
		$this->start_controls_section(
			'style_heading',
			array(
				'label' => esc_html__( 'Heading', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'selector' => '{{WRAPPER}} .dgx-hover-card__heading',
			)
		);

		$this->add_color_hover_tabs(
			array(
				'id'               => 'heading',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => '#0E1A26',
				'default_hover'    => '#FFFFFF',
				'normal_selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__heading' => 'color: {{VALUE}};',
				),
				'hover_selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__heading' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Description styling.
	 *
	 * @return void
	 */
	private function register_description_style() {
		$this->start_controls_section(
			'style_description',
			array(
				'label' => esc_html__( 'Description', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'desc_spacing',
			array(
				'label'      => esc_html__( 'Spacing Above', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 12 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__desc' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .dgx-hover-card__desc',
			)
		);

		$this->add_color_hover_tabs(
			array(
				'id'               => 'description',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => 'rgba(14, 26, 38, 0.62)',
				'default_hover'    => 'rgba(255, 255, 255, 0.78)',
				'normal_selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__desc' => 'color: {{VALUE}};',
				),
				'hover_selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Divider styling (the footer's hairline top border).
	 *
	 * @return void
	 */
	private function register_divider_style() {
		$this->start_controls_section(
			'style_divider',
			array(
				'label'     => esc_html__( 'Divider', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_footer'  => 'yes',
					'show_divider' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'divider_weight',
			array(
				'label'      => esc_html__( 'Weight', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 12 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__footer' => 'border-top-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'divider_gap',
			array(
				'label'      => esc_html__( 'Spacing (above content)', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 22 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__footer' => 'padding-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_color_hover_tabs(
			array(
				'id'               => 'divider',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => 'rgba(67, 89, 112, 0.2)',
				'default_hover'    => 'rgba(255, 255, 255, 0.22)',
				'normal_selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__footer' => 'border-top-color: {{VALUE}};',
				),
				'hover_selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__footer' => 'border-top-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Footer label styling.
	 *
	 * @return void
	 */
	private function register_label_style() {
		$this->start_controls_section(
			'style_label',
			array(
				'label'     => esc_html__( 'Footer Label', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_footer' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .dgx-hover-card__label',
			)
		);

		$this->add_color_hover_tabs(
			array(
				'id'               => 'label',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => 'rgba(14, 26, 38, 0.62)',
				'default_hover'    => 'rgba(255, 255, 255, 0.78)',
				'normal_selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__label' => 'color: {{VALUE}};',
				),
				'hover_selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Arrow button styling (size + shift + Normal/Hover icon, bg, border).
	 *
	 * @return void
	 */
	private function register_arrow_style() {
		$this->start_controls_section(
			'style_arrow',
			array(
				'label'     => esc_html__( 'Arrow', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_footer' => 'yes',
					'show_arrow'  => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_box_size',
			array(
				'label'      => esc_html__( 'Button Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 18, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 30 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 6, 'max' => 48 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 12 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__arrow i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .dgx-hover-card__arrow svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 8 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__arrow' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'%'  => array( 'min' => 0, 'max' => 50 ),
					'px' => array( 'min' => 0, 'max' => 60 ),
				),
				'default'    => array( 'unit' => '%', 'size' => 50 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card__arrow' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_hover_shift',
			array(
				'label'      => esc_html__( 'Hover Shift (right)', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 6 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__arrow i'   => 'transform: translateX({{SIZE}}{{UNIT}});',
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__arrow svg' => 'transform: translateX({{SIZE}}{{UNIT}});',
				),
			)
		);

		// Normal / Hover.
		$this->start_controls_tabs( 'arrow_color_tabs' );

		$this->start_controls_tab(
			'arrow_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'devgraphix-elementor-addons' ) )
		);

		$this->add_control(
			'arrow_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#435970',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__arrow' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_bg_color',
			array(
				'label'     => esc_html__( 'Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0, 0, 0, 0)',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__arrow' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(67, 89, 112, 0.33)',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card__arrow' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'arrow_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'devgraphix-elementor-addons' ) )
		);

		$this->add_control(
			'arrow_icon_color_hover',
			array(
				'label'     => esc_html__( 'Icon Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0E1A26',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__arrow' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__arrow' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .dgx-hover-card:hover .dgx-hover-card__arrow' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Helper: register a Normal/Hover color tab pair.
	 *
	 * @param array $args id, label, default, default_hover, normal_selectors, hover_selectors.
	 * @return void
	 */
	private function add_color_hover_tabs( array $args ) {
		$args = array_merge(
			array(
				'id'               => '',
				'label'            => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'default'          => '',
				'default_hover'    => '',
				'normal_selectors' => array(),
				'hover_selectors'  => array(),
			),
			$args
		);

		$this->start_controls_tabs( $args['id'] . '_color_tabs' );

		$this->start_controls_tab(
			$args['id'] . '_color_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'devgraphix-elementor-addons' ) )
		);

		$this->add_control(
			$args['id'] . '_color',
			array(
				'label'     => $args['label'],
				'type'      => Controls_Manager::COLOR,
				'default'   => $args['default'],
				'selectors' => $args['normal_selectors'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			$args['id'] . '_color_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'devgraphix-elementor-addons' ) )
		);

		$this->add_control(
			$args['id'] . '_color_hover',
			array(
				'label'     => $args['label'],
				'type'      => Controls_Manager::COLOR,
				'default'   => $args['default_hover'],
				'selectors' => $args['hover_selectors'],
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();
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

		$layout = ! empty( $s['content_layout'] ) ? $s['content_layout'] : 'default';

		$this->add_render_attribute(
			'wrapper',
			'class',
			array(
				'dgx-hover-card',
				'dgx-hover-card--layout-' . sanitize_html_class( $layout ),
			)
		);

		if ( 'yes' !== $s['hover_zoom'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'dgx-hover-card--no-zoom' );
		}

		if ( 'yes' === $s['show_footer'] && 'yes' !== $s['show_divider'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'dgx-hover-card--no-divider' );
		}

		if ( ! empty( $s['hover_image']['url'] ) ) {
			$this->add_render_attribute( 'bg', 'style', 'background-image:url(' . esc_url( $s['hover_image']['url'] ) . ');' );
		}

		$has_link = ! empty( $s['card_link']['url'] );
		if ( $has_link ) {
			$this->add_link_attributes( 'card_link', $s['card_link'] );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="dgx-hover-card__bg" <?php echo $this->get_render_attribute_string( 'bg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
			<div class="dgx-hover-card__scrim"></div>

			<?php if ( 'yes' === $s['show_number'] && '' !== $s['number_text'] ) : ?>
				<span class="dgx-hover-card__number" aria-hidden="true"><?php echo esc_html( $s['number_text'] ); ?></span>
			<?php endif; ?>

			<?php if ( 'yes' === $s['show_icon'] && ! empty( $s['icon']['value'] ) ) : ?>
				<span class="dgx-hover-card__icon">
					<?php Icons_Manager::render_icon( $s['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</span>
			<?php endif; ?>

			<div class="dgx-hover-card__body">
				<?php
				if ( ! empty( $s['heading'] ) ) {
					printf(
						'<%1$s class="dgx-hover-card__heading">%2$s</%1$s>',
						Utils::validate_html_tag( $s['heading_tag'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_html( $s['heading'] )
					);
				}
				?>
				<?php if ( ! empty( $s['description'] ) ) : ?>
					<p class="dgx-hover-card__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( 'yes' === $s['show_footer'] ) : ?>
				<div class="dgx-hover-card__footer">
					<?php if ( '' !== $s['footer_label'] ) : ?>
						<span class="dgx-hover-card__label"><?php echo esc_html( $s['footer_label'] ); ?></span>
					<?php endif; ?>

					<?php if ( 'yes' === $s['show_arrow'] && ! empty( $s['arrow_icon']['value'] ) ) : ?>
						<span class="dgx-hover-card__arrow">
							<?php Icons_Manager::render_icon( $s['arrow_icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_link ) : ?>
				<a class="dgx-hover-card__link" <?php echo $this->get_render_attribute_string( 'card_link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<span class="screen-reader-text"><?php echo esc_html( $s['heading'] ); ?></span>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Editor live-preview template (Underscore.js).
	 *
	 * @return void
	 */
	protected function content_template() {
		?>
		<#
		var layout    = settings.content_layout || 'default';
		var classes   = 'dgx-hover-card dgx-hover-card--layout-' + layout;
		if ( 'yes' !== settings.hover_zoom ) { classes += ' dgx-hover-card--no-zoom'; }
		if ( 'yes' === settings.show_footer && 'yes' !== settings.show_divider ) { classes += ' dgx-hover-card--no-divider'; }
		var bgStyle   = settings.hover_image.url ? 'background-image:url(' + settings.hover_image.url + ');' : '';
		var tag       = settings.heading_tag || 'h3';
		var iconHTML  = elementor.helpers.renderIcon( view, settings.icon, { 'aria-hidden': true }, 'i', 'object' );
		var arrowHTML = elementor.helpers.renderIcon( view, settings.arrow_icon, { 'aria-hidden': true }, 'i', 'object' );
		#>
		<div class="{{ classes }}">
			<div class="dgx-hover-card__bg" style="{{ bgStyle }}"></div>
			<div class="dgx-hover-card__scrim"></div>

			<# if ( 'yes' === settings.show_number && settings.number_text ) { #>
				<span class="dgx-hover-card__number" aria-hidden="true">{{{ settings.number_text }}}</span>
			<# } #>

			<# if ( 'yes' === settings.show_icon && settings.icon.value ) { #>
				<span class="dgx-hover-card__icon">{{{ iconHTML.value }}}</span>
			<# } #>

			<div class="dgx-hover-card__body">
				<# if ( settings.heading ) { #>
					<{{{ tag }}} class="dgx-hover-card__heading">{{{ settings.heading }}}</{{{ tag }}}>
				<# } #>
				<# if ( settings.description ) { #>
					<p class="dgx-hover-card__desc">{{{ settings.description }}}</p>
				<# } #>
			</div>

			<# if ( 'yes' === settings.show_footer ) { #>
				<div class="dgx-hover-card__footer">
					<# if ( settings.footer_label ) { #>
						<span class="dgx-hover-card__label">{{{ settings.footer_label }}}</span>
					<# } #>
					<# if ( 'yes' === settings.show_arrow && settings.arrow_icon.value ) { #>
						<span class="dgx-hover-card__arrow">{{{ arrowHTML.value }}}</span>
					<# } #>
				</div>
			<# } #>
		</div>
		<?php
	}
}
