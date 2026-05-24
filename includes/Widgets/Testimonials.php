<?php
/**
 * Testimonials widget.
 *
 * A mixed-media testimonial carousel (sourced from homepage.html's "Join
 * Thousands Who've Reclaimed Their Confidence" section). Each slide is one of
 * four card kinds:
 *   - text          — quote-led card (big quote mark + quote + stars + category)
 *   - video         — dark card with a poster/placeholder + play button + duration
 *   - before_after  — white card with two before/after photos + a metric chip
 *   - photo         — white card with a single photo + a metric chip
 *
 * Shown in a dependency-free scroll-snap carousel (arrows + dots + autoplay).
 * The widget renders cards only — no wrapper background — so it can sit on any
 * section. Every slide is optional and fully styleable.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Testimonials
 */
class Testimonials extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-testimonials';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Testimonials', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-testimonials';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'testimonial', 'review', 'quote', 'carousel', 'slider', 'video', 'before after' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-testimonials' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-testimonials' );
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

	/**
	 * Default slides (generic placeholder content — one of each kind).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function default_slides() {
		return array(
			array(
				'kind'         => 'video',
				'quote'        => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'devgraphix-elementor-addons' ),
				'video_label'  => esc_html__( 'Video Testimonial', 'devgraphix-elementor-addons' ),
				'duration'     => '0:47',
				'name'         => esc_html__( 'Alex M.', 'devgraphix-elementor-addons' ),
				'location'     => esc_html__( 'Lorem City, LC', 'devgraphix-elementor-addons' ),
			),
			array(
				'kind'         => 'before_after',
				'quote'        => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.', 'devgraphix-elementor-addons' ),
				'before_label' => esc_html__( 'Before', 'devgraphix-elementor-addons' ),
				'after_label'  => esc_html__( 'After', 'devgraphix-elementor-addons' ),
				'metric'       => '100+',
				'metric_label' => esc_html__( 'in 6 months', 'devgraphix-elementor-addons' ),
				'name'         => esc_html__( 'Jordan P.', 'devgraphix-elementor-addons' ),
				'location'     => esc_html__( 'Ipsum Town, IT', 'devgraphix-elementor-addons' ),
			),
			array(
				'kind'         => 'text',
				'quote'        => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Worth every penny.', 'devgraphix-elementor-addons' ),
				'rating'       => '5',
				'category'     => esc_html__( 'Lorem Ipsum', 'devgraphix-elementor-addons' ),
				'name'         => esc_html__( 'Sam R.', 'devgraphix-elementor-addons' ),
				'location'     => esc_html__( 'Dolor City, DC', 'devgraphix-elementor-addons' ),
			),
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
		// Content — Testimonials
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_items',
			array(
				'label' => esc_html__( 'Testimonials', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$item = new Repeater();

		$item->add_control(
			'kind',
			array(
				'label'   => esc_html__( 'Card Type', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array(
					'text'         => esc_html__( 'Text only', 'devgraphix-elementor-addons' ),
					'video'        => esc_html__( 'Video', 'devgraphix-elementor-addons' ),
					'before_after' => esc_html__( 'Before & After', 'devgraphix-elementor-addons' ),
					'photo'        => esc_html__( 'Single Photo', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$item->add_control(
			'quote',
			array(
				'label'       => esc_html__( 'Quote', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'default'     => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'devgraphix-elementor-addons' ),
			)
		);

		// --- Text-only ---
		$item->add_control(
			'rating',
			array(
				'label'     => esc_html__( 'Star Rating', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '5',
				'options'   => array(
					'0' => esc_html__( 'No stars', 'devgraphix-elementor-addons' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'condition' => array( 'kind' => 'text' ),
			)
		);

		$item->add_control(
			'category',
			array(
				'label'       => esc_html__( 'Category Tag', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem Ipsum', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'kind' => 'text' ),
			)
		);

		// --- Video ---
		$item->add_control(
			'video_poster',
			array(
				'label'       => esc_html__( 'Video Thumbnail', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => esc_html__( 'Leave empty to show a designed placeholder.', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'kind' => 'video' ),
			)
		);

		$item->add_control(
			'video_link',
			array(
				'label'       => esc_html__( 'Video Link', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'condition'   => array( 'kind' => 'video' ),
			)
		);

		$item->add_control(
			'video_label',
			array(
				'label'       => esc_html__( 'Overlay Label', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Video Testimonial', 'devgraphix-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'kind' => 'video' ),
			)
		);

		$item->add_control(
			'duration',
			array(
				'label'     => esc_html__( 'Duration', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '0:47',
				'condition' => array( 'kind' => 'video' ),
			)
		);

		// --- Before & After ---
		$item->add_control(
			'before_image',
			array(
				'label'     => esc_html__( 'Before Image', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'kind' => 'before_after' ),
			)
		);

		$item->add_control(
			'after_image',
			array(
				'label'     => esc_html__( 'After Image', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'kind' => 'before_after' ),
			)
		);

		$item->add_control(
			'before_label',
			array(
				'label'     => esc_html__( 'Before Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Before', 'devgraphix-elementor-addons' ),
				'condition' => array( 'kind' => 'before_after' ),
			)
		);

		$item->add_control(
			'after_label',
			array(
				'label'     => esc_html__( 'After Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'After', 'devgraphix-elementor-addons' ),
				'condition' => array( 'kind' => 'before_after' ),
			)
		);

		// --- Single photo ---
		$item->add_control(
			'photo',
			array(
				'label'     => esc_html__( 'Photo', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'kind' => 'photo' ),
			)
		);

		// --- Metric (photo + before/after) ---
		$item->add_control(
			'metric',
			array(
				'label'       => esc_html__( 'Metric (chip)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '100+',
				'description' => esc_html__( 'Leave empty for no chip.', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'kind' => array( 'before_after', 'photo' ) ),
			)
		);

		$item->add_control(
			'metric_label',
			array(
				'label'     => esc_html__( 'Metric Caption', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'in 6 months', 'devgraphix-elementor-addons' ),
				'condition' => array( 'kind' => array( 'before_after', 'photo' ) ),
			)
		);

		// --- Person (all kinds) ---
		$item->add_control(
			'person_heading',
			array(
				'label'     => esc_html__( 'Person', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$item->add_control(
			'name',
			array(
				'label'       => esc_html__( 'Name', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Jane D.', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$item->add_control(
			'location',
			array(
				'label'       => esc_html__( 'Location', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem City, LC', 'devgraphix-elementor-addons' ),
				'label_block' => true,
			)
		);

		$item->add_control(
			'avatar',
			array(
				'label'       => esc_html__( 'Avatar (optional)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => esc_html__( 'Leave empty to show the name initial.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Testimonials', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $item->get_controls(),
				'default'     => $this->default_slides(),
				'title_field' => '{{{ name }}} ({{{ kind }}})',
			)
		);

		$this->add_control(
			'footer_label',
			array(
				'label'       => esc_html__( 'Footer Tag', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Customer', 'devgraphix-elementor-addons' ),
				'description' => esc_html__( 'Small label shown on the right of each card footer. Leave empty to hide.', 'devgraphix-elementor-addons' ),
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Content — Carousel
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_carousel',
			array(
				'label' => esc_html__( 'Carousel', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'per_view',
			array(
				'label'          => esc_html__( 'Cards Per View', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => array( 'px' => array( 'min' => 1, 'max' => 4, 'step' => 1 ) ),
				'default'        => array( 'size' => 3 ),
				'tablet_default' => array( 'size' => 2 ),
				'mobile_default' => array( 'size' => 1 ),
				'selectors'      => array( '{{WRAPPER}} .dgx-tst' => '--dgx-tc-per: {{SIZE}};' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Gap', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 20 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-tst' => '--dgx-tc-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'     => esc_html__( 'Autoplay Speed (ms)', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6500,
				'min'       => 1500,
				'step'      => 250,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => esc_html__( 'Pause On Hover', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'        => esc_html__( 'Loop', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'        => esc_html__( 'Show Arrows', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'label'        => esc_html__( 'Show Dots', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Cards (shared)
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_cards',
			array(
				'label' => esc_html__( 'Cards', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'card_min_height',
			array(
				'label'      => esc_html__( 'Min Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 300, 'max' => 800 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 560 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-tcard' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 22 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-tcard' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'heading_quote',
			array(
				'label' => esc_html__( 'Quote', 'devgraphix-elementor-addons' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_typo(
			'quote_typo',
			'{{WRAPPER}} .dgx-tcard__quote',
			array(
				'font_size'   => $this->fs( 17 ),
				'font_weight' => array( 'default' => '300' ),
				'font_style'  => array( 'default' => 'italic' ),
			)
		);

		$this->add_control(
			'heading_person',
			array(
				'label'     => esc_html__( 'Person', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_typo( 'name_typo', '{{WRAPPER}} .dgx-tcard__name', array( 'font_size' => $this->fs( 13 ), 'font_weight' => array( 'default' => '600' ) ) );
		$this->add_typo( 'loc_typo', '{{WRAPPER}} .dgx-tcard__loc', array( 'font_size' => $this->fs( 9 ) ) );

		$this->add_responsive_control(
			'avatar_size',
			array(
				'label'      => esc_html__( 'Avatar Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 64 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 36 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-tcard__avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_typo( 'tag_typo', '{{WRAPPER}} .dgx-tcard__tag', array( 'font_size' => $this->fs( 9 ) ) );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Text Card
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Text Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'text_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--text', '#dfedfb', 'background-color' );
		$this->add_color( 'text_color', esc_html__( 'Quote Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--text .dgx-tcard__quote', '#0e1a26' );
		$this->add_color( 'text_mark_color', esc_html__( 'Quote Mark Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__mark', 'rgba(67,89,112,0.33)' );
		$this->add_color( 'text_stars_color', esc_html__( 'Stars Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__stars', '#435970', 'color' );
		$this->add_color( 'text_cat_bg', esc_html__( 'Category Pill Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__cat', '#ffffff', 'background-color' );
		$this->add_color( 'text_cat_color', esc_html__( 'Category Pill Text', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__cat', '#435970' );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Video Card
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_video',
			array(
				'label' => esc_html__( 'Video Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'video_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--video', '#0e1a26', 'background-color' );
		$this->add_color( 'video_quote_color', esc_html__( 'Quote Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--video .dgx-tcard__quote', 'rgba(255,255,255,0.95)' );
		$this->add_color( 'video_meta_color', esc_html__( 'Name / Tag Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--video .dgx-tcard__name, {{WRAPPER}} .dgx-tcard--video .dgx-tcard__loc, {{WRAPPER}} .dgx-tcard--video .dgx-tcard__tag', '#ffffff' );
		$this->add_color( 'video_play_bg', esc_html__( 'Play Button Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__play', 'rgba(255,255,255,0.95)', 'background-color' );
		$this->add_color( 'video_play_icon', esc_html__( 'Play Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__play svg', '#0e1a26', 'fill' );
		$this->add_color( 'video_label_color', esc_html__( 'Overlay Label Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__vlabel', 'rgba(255,255,255,0.7)' );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Photo / Before & After
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_photo',
			array(
				'label' => esc_html__( 'Photo / Before & After Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'photo_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--photo, {{WRAPPER}} .dgx-tcard--before_after', '#ffffff', 'background-color' );
		$this->add_color( 'photo_quote_color', esc_html__( 'Quote Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard--photo .dgx-tcard__quote, {{WRAPPER}} .dgx-tcard--before_after .dgx-tcard__quote', '#0e1a26' );

		$this->add_control(
			'heading_ba_labels',
			array(
				'label'     => esc_html__( 'Before / After Labels', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'ba_bar_bg', esc_html__( 'Label Bar Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__shot-label', '#7895b3', 'background-color' );
		$this->add_color( 'ba_bar_color', esc_html__( 'Label Bar Text', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__shot-label', '#ffffff' );
		$this->add_typo( 'ba_bar_typo', '{{WRAPPER}} .dgx-tcard__shot-label', array( 'font_size' => $this->fs( 18 ), 'font_weight' => array( 'default' => '600' ) ) );

		$this->add_control(
			'heading_metric',
			array(
				'label'     => esc_html__( 'Metric Chip', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'metric_bg', esc_html__( 'Chip Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__metric', '#ffffff', 'background-color' );
		$this->add_color( 'metric_val_color', esc_html__( 'Value Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__metric-val', '#0e1a26' );
		$this->add_typo( 'metric_val_typo', '{{WRAPPER}} .dgx-tcard__metric-val', array( 'font_size' => $this->fs( 24 ), 'font_weight' => array( 'default' => '300' ), 'font_style' => array( 'default' => 'italic' ) ) );
		$this->add_color( 'metric_label_color', esc_html__( 'Caption Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tcard__metric-label', 'rgba(14,26,38,0.62)' );

		$this->end_controls_section();

		// -------------------------------------------------------------------
		// Style — Arrows & Dots
		// -------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_nav',
			array(
				'label' => esc_html__( 'Arrows & Dots', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'arrow_size',
			array(
				'label'      => esc_html__( 'Arrow Size', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 32, 'max' => 72 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 52 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-tst__arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_color( 'arrow_prev_bg', esc_html__( 'Prev Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tst__arrow--prev', '#ffffff', 'background-color' );
		$this->add_color( 'arrow_prev_color', esc_html__( 'Prev Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tst__arrow--prev', '#0e1a26' );
		$this->add_color( 'arrow_next_bg', esc_html__( 'Next Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tst__arrow--next', '#0e1a26', 'background-color' );
		$this->add_color( 'arrow_next_color', esc_html__( 'Next Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tst__arrow--next', '#ffffff' );

		$this->add_control(
			'heading_dots',
			array(
				'label'     => esc_html__( 'Dots', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'dot_color', esc_html__( 'Dot Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tst__dot', 'rgba(14,26,38,0.2)', 'background-color' );
		$this->add_color( 'dot_active_color', esc_html__( 'Active Dot Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-tst__dot.is-active', '#435970', 'background-color' );

		$this->end_controls_section();
	}

	// =======================================================================
	// RENDER
	// =======================================================================

	/**
	 * SVG play icon.
	 *
	 * @return string
	 */
	private function play_svg() {
		return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';
	}

	/**
	 * SVG arrow icon.
	 *
	 * @param string $dir 'left' or 'right'.
	 * @return string
	 */
	private function arrow_svg( $dir = 'right' ) {
		if ( 'left' === $dir ) {
			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>';
		}
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
	}

	/**
	 * SVG star icon.
	 *
	 * @return string
	 */
	private function star_svg() {
		return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 21.2l1.4-6.8L2.2 9.7l6.9-.7z"/></svg>';
	}

	/**
	 * Render a star row.
	 *
	 * @param int $rating Number of filled stars (0-5).
	 * @return void
	 */
	private function render_stars( $rating ) {
		echo '<span class="dgx-tcard__stars" aria-label="' . esc_attr( sprintf( /* translators: %d: star count. */ __( '%d out of 5 stars', 'devgraphix-elementor-addons' ), $rating ) ) . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$cls = $i <= $rating ? 'dgx-tcard__star is-on' : 'dgx-tcard__star';
			echo '<span class="' . esc_attr( $cls ) . '">' . $this->star_svg() . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</span>';
	}

	/**
	 * Render the shared card footer (avatar + name/location + tag).
	 *
	 * @param array<string,mixed> $it    Repeater item.
	 * @param string               $label Footer tag label.
	 * @return void
	 */
	private function render_footer( array $it, $label ) {
		$name   = isset( $it['name'] ) ? $it['name'] : '';
		$loc    = isset( $it['location'] ) ? $it['location'] : '';
		$avatar = ! empty( $it['avatar']['url'] ) ? $it['avatar']['url'] : '';
		$initial = '' !== $name ? mb_substr( $name, 0, 1 ) : '';
		?>
		<div class="dgx-tcard__foot">
			<div class="dgx-tcard__person">
				<span class="dgx-tcard__avatar">
					<?php if ( '' !== $avatar ) : ?>
						<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
					<?php else : ?>
						<?php echo esc_html( $initial ); ?>
					<?php endif; ?>
				</span>
				<span class="dgx-tcard__id">
					<?php if ( '' !== $name ) : ?>
						<span class="dgx-tcard__name"><?php echo esc_html( $name ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $loc ) : ?>
						<span class="dgx-tcard__loc"><?php echo esc_html( $loc ); ?></span>
					<?php endif; ?>
				</span>
			</div>
			<?php if ( '' !== $label ) : ?>
				<span class="dgx-tcard__tag"><?php echo esc_html( $label ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the metric chip used by photo / before-after cards.
	 *
	 * @param array<string,mixed> $it Repeater item.
	 * @return void
	 */
	private function render_metric( array $it ) {
		$metric = isset( $it['metric'] ) ? trim( (string) $it['metric'] ) : '';
		if ( '' === $metric ) {
			return;
		}
		$caption = isset( $it['metric_label'] ) ? $it['metric_label'] : '';
		?>
		<div class="dgx-tcard__metric">
			<span class="dgx-tcard__metric-val"><?php echo esc_html( $metric ); ?></span>
			<?php if ( '' !== $caption ) : ?>
				<span class="dgx-tcard__metric-label"><?php echo esc_html( $caption ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render one card.
	 *
	 * @param array<string,mixed> $it    Repeater item.
	 * @param string               $label Footer tag label.
	 * @return void
	 */
	private function render_card( array $it, $label ) {
		$kind  = isset( $it['kind'] ) ? $it['kind'] : 'text';
		$quote = isset( $it['quote'] ) ? $it['quote'] : '';
		?>
		<div class="dgx-tcard dgx-tcard--<?php echo esc_attr( $kind ); ?>">
			<?php if ( 'video' === $kind ) : ?>
				<?php
				$poster = ! empty( $it['video_poster']['url'] ) ? $it['video_poster']['url'] : '';
				$vlabel = isset( $it['video_label'] ) ? $it['video_label'] : '';
				$dur    = isset( $it['duration'] ) ? $it['duration'] : '';
				$vurl   = ! empty( $it['video_link']['url'] ) ? $it['video_link']['url'] : '';
				$media_tag = '' !== $vurl ? 'a' : 'div';
				?>
				<<?php echo esc_attr( $media_tag ); ?> class="dgx-tcard__video"
					<?php if ( '' !== $vurl ) : ?>
						href="<?php echo esc_url( $vurl ); ?>"
						<?php echo ! empty( $it['video_link']['is_external'] ) ? ' target="_blank"' : ''; ?>
						<?php echo ! empty( $it['video_link']['nofollow'] ) ? ' rel="nofollow"' : ''; ?>
					<?php endif; ?>
					<?php echo '' !== $poster ? ' style="background-image:url(\'' . esc_url( $poster ) . '\');"' : ''; ?>
				>
					<span class="dgx-tcard__play"><?php echo $this->play_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php if ( '' !== $vlabel ) : ?>
						<span class="dgx-tcard__vlabel"><?php echo esc_html( $vlabel ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $dur ) : ?>
						<span class="dgx-tcard__duration"><span class="dgx-tcard__rec" aria-hidden="true"></span><?php echo esc_html( $dur ); ?></span>
					<?php endif; ?>
				</<?php echo esc_attr( $media_tag ); ?>>
				<div class="dgx-tcard__body">
					<?php if ( '' !== $quote ) : ?>
						<p class="dgx-tcard__quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
					<?php endif; ?>
					<?php $this->render_footer( $it, $label ); ?>
				</div>

			<?php elseif ( 'before_after' === $kind ) : ?>
				<?php
				$before = ! empty( $it['before_image']['url'] ) ? $it['before_image']['url'] : '';
				$after  = ! empty( $it['after_image']['url'] ) ? $it['after_image']['url'] : '';
				$blabel = isset( $it['before_label'] ) ? $it['before_label'] : '';
				$alabel = isset( $it['after_label'] ) ? $it['after_label'] : '';
				?>
				<div class="dgx-tcard__photo">
					<div class="dgx-tcard__split">
						<div class="dgx-tcard__shot">
							<?php if ( '' !== $before ) : ?>
								<span class="dgx-tcard__shot-img" style="background-image:url('<?php echo esc_url( $before ); ?>');"></span>
							<?php else : ?>
								<span class="dgx-tcard__shot-ph" aria-hidden="true"></span>
							<?php endif; ?>
							<?php if ( '' !== $blabel ) : ?>
								<span class="dgx-tcard__shot-label"><?php echo esc_html( $blabel ); ?></span>
							<?php endif; ?>
						</div>
						<div class="dgx-tcard__shot">
							<?php if ( '' !== $after ) : ?>
								<span class="dgx-tcard__shot-img" style="background-image:url('<?php echo esc_url( $after ); ?>');"></span>
							<?php else : ?>
								<span class="dgx-tcard__shot-ph" aria-hidden="true"></span>
							<?php endif; ?>
							<?php if ( '' !== $alabel ) : ?>
								<span class="dgx-tcard__shot-label"><?php echo esc_html( $alabel ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<?php $this->render_metric( $it ); ?>
				</div>
				<div class="dgx-tcard__body">
					<?php if ( '' !== $quote ) : ?>
						<p class="dgx-tcard__quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
					<?php endif; ?>
					<?php $this->render_footer( $it, $label ); ?>
				</div>

			<?php elseif ( 'photo' === $kind ) : ?>
				<?php $photo = ! empty( $it['photo']['url'] ) ? $it['photo']['url'] : ''; ?>
				<div class="dgx-tcard__photo">
					<?php if ( '' !== $photo ) : ?>
						<span class="dgx-tcard__shot-img dgx-tcard__shot-img--single" style="background-image:url('<?php echo esc_url( $photo ); ?>');"></span>
					<?php else : ?>
						<span class="dgx-tcard__shot-ph dgx-tcard__shot-ph--single" aria-hidden="true"></span>
					<?php endif; ?>
					<?php $this->render_metric( $it ); ?>
				</div>
				<div class="dgx-tcard__body">
					<?php if ( '' !== $quote ) : ?>
						<p class="dgx-tcard__quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
					<?php endif; ?>
					<?php $this->render_footer( $it, $label ); ?>
				</div>

			<?php else : ?>
				<?php
				$rating = isset( $it['rating'] ) ? (int) $it['rating'] : 5;
				$cat    = isset( $it['category'] ) ? $it['category'] : '';
				?>
				<span class="dgx-tcard__mark" aria-hidden="true">&ldquo;</span>
				<div class="dgx-tcard__text-body">
					<?php if ( '' !== $quote ) : ?>
						<p class="dgx-tcard__quote dgx-tcard__quote--lg"><?php echo esc_html( $quote ); ?></p>
					<?php endif; ?>
					<div>
						<div class="dgx-tcard__meta">
							<?php if ( $rating > 0 ) : ?>
								<?php $this->render_stars( $rating ); ?>
							<?php else : ?>
								<span></span>
							<?php endif; ?>
							<?php if ( '' !== $cat ) : ?>
								<span class="dgx-tcard__cat"><?php echo esc_html( $cat ); ?></span>
							<?php endif; ?>
						</div>
						<?php $this->render_footer( $it, $label ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = ! empty( $s['items'] ) ? $s['items'] : array();

		if ( empty( $items ) ) {
			return;
		}

		$label       = isset( $s['footer_label'] ) ? $s['footer_label'] : '';
		$autoplay    = 'yes' === ( isset( $s['autoplay'] ) ? $s['autoplay'] : '' );
		$loop        = 'yes' === ( isset( $s['loop'] ) ? $s['loop'] : '' );
		$pause       = 'yes' === ( isset( $s['pause_on_hover'] ) ? $s['pause_on_hover'] : '' );
		$show_arrows = 'yes' === ( isset( $s['show_arrows'] ) ? $s['show_arrows'] : '' );
		$show_dots   = 'yes' === ( isset( $s['show_dots'] ) ? $s['show_dots'] : '' );
		$speed       = isset( $s['autoplay_speed'] ) ? (int) $s['autoplay_speed'] : 6500;
		?>
		<div class="dgx-tst dgx-tst--carousel"
			data-autoplay="<?php echo $autoplay ? 'yes' : 'no'; ?>"
			data-interval="<?php echo esc_attr( $speed ); ?>"
			data-loop="<?php echo $loop ? 'yes' : 'no'; ?>"
			data-pause="<?php echo $pause ? 'yes' : 'no'; ?>">
			<div class="dgx-tst__viewport">
				<div class="dgx-tst__track">
					<?php foreach ( $items as $it ) : ?>
						<?php $this->render_card( $it, $label ); ?>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $show_arrows || $show_dots ) : ?>
				<div class="dgx-tst__nav">
					<?php if ( $show_dots ) : ?>
						<div class="dgx-tst__dots" aria-hidden="true"></div>
					<?php else : ?>
						<span></span>
					<?php endif; ?>
					<?php if ( $show_arrows ) : ?>
						<div class="dgx-tst__arrows">
							<button type="button" class="dgx-tst__arrow dgx-tst__arrow--prev" aria-label="<?php esc_attr_e( 'Previous', 'devgraphix-elementor-addons' ); ?>"><?php echo $this->arrow_svg( 'left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							<button type="button" class="dgx-tst__arrow dgx-tst__arrow--next" aria-label="<?php esc_attr_e( 'Next', 'devgraphix-elementor-addons' ); ?>"><?php echo $this->arrow_svg( 'right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
