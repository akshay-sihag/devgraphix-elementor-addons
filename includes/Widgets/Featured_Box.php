<?php
/**
 * Featured Box widget.
 *
 * A single card with two looks:
 *   - product : full-bleed lifestyle image + dark wash + chip/headline/price/
 *               CTA and a recessed cutout holding a product packshot. The
 *               packshot/title/price/link can be pulled from a selected post
 *               or WooCommerce product, or set manually.
 *   - content : tonal background + number/label + heading + description +
 *               optional badge/chips/stats and a positioned image.
 *
 * Place several inside your own Elementor columns/containers to build grids.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Featured_Box
 */
class Featured_Box extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-featured-box';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Featured Box', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-featured';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'featured', 'box', 'product', 'woocommerce', 'card', 'image' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-featured-box' );
	}

	// =======================================================================
	// HELPERS
	// =======================================================================

	/**
	 * Cached Select2 options (built once per request).
	 *
	 * @var array<int|string, string>|null
	 */
	private static $post_options = null;

	/**
	 * Build a Select2 option list of recent posts/pages/products.
	 *
	 * Cached in a static so repeated control registration (editor, element
	 * manager, multiple instances) only runs the query once per request.
	 *
	 * @return array<int|string, string>
	 */
	private function get_post_options() {
		if ( null !== self::$post_options ) {
			return self::$post_options;
		}

		$options = array( '' => esc_html__( '— Select —', 'devgraphix-elementor-addons' ) );

		// WooCommerce products only.
		$posts = get_posts(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => true,
			)
		);

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = $post->post_title;
		}

		self::$post_options = $options;

		return $options;
	}

	/**
	 * Resolve dynamic product data (image, title, price, permalink), falling
	 * back to the manual packshot image when needed.
	 *
	 * @param array $s Settings.
	 * @return array{image:string,title:string,price:string,permalink:string}
	 */
	private function get_product_data( array $s ) {
		$data = array(
			'image'     => '',
			'title'     => '',
			'price'     => '',
			'permalink' => '',
		);

		$source = isset( $s['product_source'] ) ? $s['product_source'] : 'dynamic';

		if ( 'dynamic' === $source && ! empty( $s['product_post'] ) ) {
			$pid = (int) $s['product_post'];

			$thumb = get_the_post_thumbnail_url( $pid, 'large' );
			if ( $thumb ) {
				$data['image'] = $thumb;
			}

			$data['title']     = get_the_title( $pid );
			$data['permalink'] = (string) get_permalink( $pid );

			if ( function_exists( 'wc_get_product' ) && function_exists( 'wc_price' ) ) {
				$product = wc_get_product( $pid );
				if ( $product && is_callable( array( $product, 'get_price' ) ) ) {
					$price = $product->get_price();
					if ( '' !== $price && null !== $price ) {
						$data['price'] = wp_strip_all_tags( html_entity_decode( wc_price( $price ) ) );
					}
				}
			}
		}

		if ( '' === $data['image'] && ! empty( $s['product_cutout_image']['url'] ) ) {
			$data['image'] = $s['product_cutout_image']['url'];
		}

		return $data;
	}

	/**
	 * Register a simple colour control.
	 *
	 * @param string              $id        Control id.
	 * @param string              $label     Label.
	 * @param string              $selector  Full CSS selector.
	 * @param string              $default   Default colour ('' keeps the CSS/per-look default).
	 * @param string              $prop      CSS property.
	 * @param array<string,mixed> $condition Condition.
	 * @return void
	 */
	private function fb_color( $id, $label, $selector, $default = '', $prop = 'color', array $condition = array() ) {
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
	 * Register a typography group control.
	 *
	 * @param string              $id             Group name.
	 * @param string              $label          Label.
	 * @param string              $selector       Full CSS selector.
	 * @param array<string,mixed> $fields_options Field defaults.
	 * @param array<string,mixed> $condition      Condition.
	 * @return void
	 */
	private function fb_typo( $id, $label, $selector, array $fields_options = array(), array $condition = array() ) {
		$args = array(
			'name'     => $id,
			'label'    => $label,
			'selector' => $selector,
		);
		if ( ! empty( $fields_options ) ) {
			$args['fields_options'] = $fields_options;
		}
		if ( ! empty( $condition ) ) {
			$args['condition'] = $condition;
		}
		$this->add_group_control( Group_Control_Typography::get_type(), $args );
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

		// ---- Card ---------------------------------------------------------
		$this->start_controls_section(
			'section_card',
			array(
				'label' => esc_html__( 'Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'card_type',
			array(
				'label'   => esc_html__( 'Type', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'product',
				'options' => array(
					'product' => esc_html__( 'Product (image + cutout)', 'devgraphix-elementor-addons' ),
					'content' => esc_html__( 'Content (text + image)', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'card_size',
			array(
				'label'     => esc_html__( 'Card Size', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'tall',
				'options'   => array(
					'tall'  => esc_html__( 'Tall (large type, big cutout)', 'devgraphix-elementor-addons' ),
					'small' => esc_html__( 'Small (compact)', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'card_type' => 'product' ),
			)
		);

		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => esc_html__( 'Min Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'min' => 200, 'max' => 900 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 440 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'cell_index',
			array(
				'label'       => esc_html__( 'Number / Index', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '03 / 06',
				'description' => esc_html__( 'Shown in the chip (product) or as the corner number (content).', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'cell_category',
			array(
				'label'   => esc_html__( 'Category Label', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'LOREM IPSUM', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'cell_heading',
			array(
				'label'       => esc_html__( 'Heading', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lorem', 'devgraphix-elementor-addons' ),
				'placeholder' => esc_html__( 'Auto from product', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'cell_heading_accent',
			array(
				'label'   => esc_html__( 'Heading Accent', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'ipsum.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'accent_break',
			array(
				'label'        => esc_html__( 'Accent on New Line', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Off: the accent flows inline and wraps with the heading. On: forces it onto its own line.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'cell_description',
			array(
				'label'     => esc_html__( 'Description', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore.', 'devgraphix-elementor-addons' ),
				'condition' => array( 'card_type' => 'content' ),
			)
		);

		$this->add_control(
			'cell_link',
			array(
				'label'       => esc_html__( 'Link', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'description' => esc_html__( 'Optional. Makes the whole card clickable.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->end_controls_section();

		// ---- Product ------------------------------------------------------
		$this->start_controls_section(
			'section_product',
			array(
				'label'     => esc_html__( 'Product', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'card_type' => 'product' ),
			)
		);

		$this->add_control(
			'product_bg_image',
			array(
				'label' => esc_html__( 'Background (lifestyle) Image', 'devgraphix-elementor-addons' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'product_source',
			array(
				'label'   => esc_html__( 'Packshot Source', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'dynamic',
				'options' => array(
					'dynamic' => esc_html__( 'From product', 'devgraphix-elementor-addons' ),
					'manual'  => esc_html__( 'Manual upload', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'product_post',
			array(
				'label'       => esc_html__( 'Product', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_post_options(),
				'description' => esc_html__( 'Uses the product featured image as the packshot. Heading and price auto-fill from the product when left empty (WooCommerce price supported).', 'devgraphix-elementor-addons' ),
				'condition'   => array( 'product_source' => 'dynamic' ),
			)
		);

		$this->add_control(
			'product_link_auto',
			array(
				'label'        => esc_html__( 'Link to Product', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Link the card + CTA to the selected item (unless a manual Link is set above).', 'devgraphix-elementor-addons' ),
				'condition'    => array( 'product_source' => 'dynamic' ),
			)
		);

		$this->add_control(
			'product_cutout_image',
			array(
				'label'     => esc_html__( 'Packshot Image', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'product_source' => 'manual' ),
			)
		);

		$this->add_responsive_control(
			'product_image_height',
			array(
				'label'      => esc_html__( 'Packshot Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 60, 'max' => 320 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__product-img' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'price_prefix',
			array(
				'label'   => esc_html__( 'Price Prefix', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Starting from just', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'price_value',
			array(
				'label'       => esc_html__( 'Price Value', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '$59',
				'placeholder' => esc_html__( 'Auto from product', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'price_suffix',
			array(
				'label'   => esc_html__( 'Price Suffix', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '/mo',
			)
		);

		$this->add_control(
			'show_cta',
			array(
				'label'        => esc_html__( 'Show CTA Button', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'     => esc_html__( 'CTA Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Learn More', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// ---- Content extras ----------------------------------------------
		$this->start_controls_section(
			'section_content',
			array(
				'label'     => esc_html__( 'Content', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'card_type' => 'content' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'content_bg',
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .dgx-fbox.dgx-fbox--content',
			)
		);

		$this->add_control(
			'content_image',
			array(
				'label' => esc_html__( 'Image', 'devgraphix-elementor-addons' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'content_image_position',
			array(
				'label'   => esc_html__( 'Image Position', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right-bottom',
				'options' => array(
					'right-top'    => esc_html__( 'Right top', 'devgraphix-elementor-addons' ),
					'right-center' => esc_html__( 'Right center', 'devgraphix-elementor-addons' ),
					'right-bottom' => esc_html__( 'Right bottom', 'devgraphix-elementor-addons' ),
					'bottom'       => esc_html__( 'Bottom (full width)', 'devgraphix-elementor-addons' ),
					'cover'        => esc_html__( 'Cover (full card)', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_responsive_control(
			'content_image_height',
			array(
				'label'       => esc_html__( 'Image Height', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( '%', 'px', 'vh' ),
				'range'       => array(
					'%'  => array( 'min' => 10, 'max' => 100 ),
					'px' => array( 'min' => 60, 'max' => 700 ),
				),
				'description' => esc_html__( 'Best for Right-bottom / Right-center images (width follows automatically).', 'devgraphix-elementor-addons' ),
				'selectors'   => array(
					'{{WRAPPER}} .dgx-fbox__content-img' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array( 'content_image_position!' => 'bottom' ),
			)
		);

		$this->add_responsive_control(
			'content_image_width',
			array(
				'label'       => esc_html__( 'Image Width', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( '%', 'px' ),
				'range'       => array(
					'%'  => array( 'min' => 10, 'max' => 100 ),
					'px' => array( 'min' => 60, 'max' => 900 ),
				),
				'description' => esc_html__( 'Best for Bottom images. For Right images, leave Height set and width follows the aspect ratio.', 'devgraphix-elementor-addons' ),
				'selectors'   => array(
					'{{WRAPPER}} .dgx-fbox__content-img' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array( 'content_image_position!' => 'cover' ),
			)
		);

		$this->add_control(
			'content_image_fit',
			array(
				'label'     => esc_html__( 'Image Fit', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''        => esc_html__( 'Default', 'devgraphix-elementor-addons' ),
					'contain' => esc_html__( 'Contain', 'devgraphix-elementor-addons' ),
					'cover'   => esc_html__( 'Cover', 'devgraphix-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__content-img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_image_offset',
			array(
				'label'              => esc_html__( 'Image Offset (margin)', 'devgraphix-elementor-addons' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', '%', 'em' ),
				'allowed_dimensions' => array( 'top', 'right', 'bottom', 'left' ),
				'description'        => esc_html__( 'Distance from the card edges (0 = flush into the corner). Positive insets the image; negative bleeds it past the edge.', 'devgraphix-elementor-addons' ),
				'selectors'          => array(
					'{{WRAPPER}} .dgx-fbox__content-img' => '--dgx-cimg-top: {{TOP}}{{UNIT}}; --dgx-cimg-right: {{RIGHT}}{{UNIT}}; --dgx-cimg-bottom: {{BOTTOM}}{{UNIT}}; --dgx-cimg-left: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_text_width',
			array(
				'label'      => esc_html__( 'Text Max Width', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 30, 'max' => 100 ) ),
				'default'    => array( 'unit' => '%', 'size' => 55 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__content' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'show_badge',
			array(
				'label'        => esc_html__( 'Show Badge', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'badge_icon',
			array(
				'label'     => esc_html__( 'Badge Icon', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_text',
			array(
				'label'     => esc_html__( 'Badge Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Lorem ipsum dolor', 'devgraphix-elementor-addons' ),
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'content_chips',
			array(
				'label'       => esc_html__( 'Chips (one per line)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'description' => esc_html__( 'Each line becomes a pill chip.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'content_stats',
			array(
				'label'       => esc_html__( 'Stats (Value | Label per line)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'placeholder' => "100% | Lorem ipsum\n24/7 | Dolor sit amet",
				'description' => esc_html__( 'Each line "Value | Label" becomes a footer stat.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	/**
	 * Style controls. Defaults mirror the rendered design so the panel
	 * reflects the actual values (no hidden CSS-only defaults).
	 *
	 * @return void
	 */
	private function register_style_controls() {
		$this->register_card_style();
		$this->register_cutout_style();
		$this->register_heading_style();
		$this->register_description_style();
		$this->register_meta_style();
		$this->register_button_style();
		$this->register_badge_style();
		$this->register_chips_style();
		$this->register_stats_style();
	}

	/**
	 * Cutout (product packshot panel): background (solid OR gradient), border,
	 * corner radius and padding.
	 *
	 * @return void
	 */
	private function register_cutout_style() {
		$this->start_controls_section(
			'style_cutout',
			array(
				'label'     => esc_html__( 'Cutout (packshot panel)', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'card_type' => 'product' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'cutout_bg',
				'types'          => array( 'classic', 'gradient' ),
				'exclude'        => array( 'image' ),
				'selector'       => '{{WRAPPER}} .dgx-fbox__cutout',
				'fields_options' => array(
					'background'     => array( 'default' => 'gradient' ),
					'color'          => array( 'default' => '#f4f1ea' ),
					'color_b'        => array( 'default' => '#ffffff' ),
					'color_b_stop'   => array( 'default' => array( 'unit' => '%', 'size' => 35 ) ),
					'gradient_angle' => array( 'default' => array( 'unit' => 'deg', 'size' => 135 ) ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'cutout_border',
				'selector' => '{{WRAPPER}} .dgx-fbox__cutout',
			)
		);

		$this->add_responsive_control(
			'cutout_radius',
			array(
				'label'       => esc_html__( 'Corner Radius', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'     => array( 'unit' => 'px', 'size' => 32 ),
				'description' => esc_html__( 'The rounded (cut) corner of the recess.', 'devgraphix-elementor-addons' ),
				'selectors'   => array(
					'{{WRAPPER}} .dgx-fbox__cutout' => 'border-top-left-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cutout_padding',
			array(
				'label'       => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => array( 'px', 'em' ),
				'description' => esc_html__( 'Space around the packshot. Leave empty to keep the size defaults.', 'devgraphix-elementor-addons' ),
				'selectors'   => array(
					'{{WRAPPER}} .dgx-fbox__cutout' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * CTA button (product look): background, colours, typography, padding,
	 * radius, border and shadow.
	 *
	 * @return void
	 */
	private function register_button_style() {
		$this->start_controls_section(
			'style_button',
			array(
				'label'     => esc_html__( 'Button (CTA)', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'card_type' => 'product',
					'show_cta'  => 'yes',
				),
			)
		);

		$this->fb_color( 'cta_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__cta', '#ffffff', 'background-color' );
		$this->fb_color( 'cta_color', esc_html__( 'Text Color', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__cta', '#0e1a26' );
		$this->fb_color( 'cta_icon_color', esc_html__( 'Icon Color', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__cta svg', '', 'stroke' );

		$this->fb_typo(
			'cta_typography',
			esc_html__( 'Typography', 'devgraphix-elementor-addons' ),
			'{{WRAPPER}} .dgx-fbox__cta',
			array(
				'typography'  => array( 'default' => 'custom' ),
				'font_size'   => array( 'default' => array( 'unit' => 'px', 'size' => 12 ) ),
				'font_weight' => array( 'default' => '600' ),
			)
		);

		$this->add_responsive_control(
			'cta_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 10, 'right' => 18, 'bottom' => 10, 'left' => 18, 'isLinked' => false ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cta_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__cta' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'cta_border',
				'selector' => '{{WRAPPER}} .dgx-fbox__cta',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'           => 'cta_shadow',
				'selector'       => '{{WRAPPER}} .dgx-fbox__cta',
				'fields_options' => array(
					'box_shadow_type' => array( 'default' => 'yes' ),
					'box_shadow'      => array(
						'default' => array(
							'horizontal' => 0,
							'vertical'   => 6,
							'blur'       => 16,
							'spread'     => 0,
							'color'      => 'rgba(0, 0, 0, 0.25)',
						),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card box: radius, border, shadow (with real defaults).
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
			'card_radius',
			array(
				'label'       => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'   => array(
					'{{WRAPPER}} .dgx-fbox' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'description' => esc_html__( 'Leave empty to keep the design defaults (tall 32, small 24, content 28).', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'card_border',
				'selector'       => '{{WRAPPER}} .dgx-fbox::before',
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
					'color'  => array( 'default' => 'rgba(14, 26, 38, 0.12)' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'           => 'card_shadow',
				'selector'       => '{{WRAPPER}} .dgx-fbox',
				'fields_options' => array(
					'box_shadow_type' => array( 'default' => 'yes' ),
					'box_shadow'      => array(
						'default' => array(
							'horizontal' => 0,
							'vertical'   => 12,
							'blur'       => 32,
							'spread'     => 0,
							'color'      => 'rgba(14, 26, 38, 0.08)',
						),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Heading typography + colors (main + accent).
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

		$this->fb_typo( 'title_typography', esc_html__( 'Heading Typography', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__title' );

		$this->fb_color(
			'heading_color',
			esc_html__( 'Heading Color', 'devgraphix-elementor-addons' ),
			'{{WRAPPER}} .dgx-fbox__title',
			'',
			'color'
		);

		$this->add_control(
			'heading_accent_heading',
			array(
				'label'     => esc_html__( 'Accent Word', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->fb_typo(
			'accent_typography',
			esc_html__( 'Accent Typography', 'devgraphix-elementor-addons' ),
			'{{WRAPPER}} .dgx-fbox__accent',
			array( 'typography' => array( 'default' => 'custom' ), 'font_style' => array( 'default' => 'italic' ) )
		);

		$this->fb_color(
			'heading_accent_color',
			esc_html__( 'Accent Word Color', 'devgraphix-elementor-addons' ),
			'{{WRAPPER}} .dgx-fbox__accent',
			'',
			'color'
		);

		$this->add_control(
			'heading_color_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Colours are empty by default so each look keeps its design (Product = white on the image, Content = dark). Set a colour to override.', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Description typography + color.
	 *
	 * @return void
	 */
	private function register_description_style() {
		$this->start_controls_section(
			'style_description',
			array(
				'label'     => esc_html__( 'Description', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'card_type' => 'content' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'desc_typography',
				'selector'       => '{{WRAPPER}} .dgx-fbox__desc',
				'fields_options' => array(
					'typography' => array( 'default' => 'custom' ),
					'font_size'  => array( 'default' => array( 'unit' => 'px', 'size' => 15 ) ),
					'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 1.65 ) ),
				),
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => esc_html__( 'Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(14, 26, 38, 0.62)',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox--content .dgx-fbox__desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Category label + number colors.
	 *
	 * @return void
	 */
	private function register_meta_style() {
		// ---- Category label (both looks) ----
		$this->start_controls_section(
			'style_category',
			array(
				'label' => esc_html__( 'Category Label', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->fb_typo( 'category_typography', esc_html__( 'Typography', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__cat' );
		$this->fb_color( 'category_color', esc_html__( 'Color', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__cat', '', 'color' );

		$this->end_controls_section();

		// ---- Number / Index (both looks; product shows it as a pill) ----
		$this->start_controls_section(
			'style_number',
			array(
				'label' => esc_html__( 'Number / Index', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->fb_typo( 'number_typography', esc_html__( 'Typography', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__chip, {{WRAPPER}} .dgx-fbox__num' );
		$this->fb_color( 'number_color', esc_html__( 'Text Color', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__chip, {{WRAPPER}} .dgx-fbox__num', '', 'color' );

		$this->add_control(
			'number_pill_heading',
			array(
				'label'     => esc_html__( 'Pill (product look)', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'card_type' => 'product' ),
			)
		);

		$this->fb_color( 'number_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__chip', 'rgba(255, 255, 255, 0.18)', 'background-color', array( 'card_type' => 'product' ) );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'number_border',
				'selector'       => '{{WRAPPER}} .dgx-fbox__chip',
				'fields_options' => array(
					'border' => array( 'default' => 'solid' ),
					'width'  => array(
						'default' => array( 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'unit' => 'px', 'isLinked' => true ),
					),
					'color'  => array( 'default' => 'rgba(255, 255, 255, 0.25)' ),
				),
				'condition'      => array( 'card_type' => 'product' ),
			)
		);

		$this->add_responsive_control(
			'number_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'unit' => 'px', 'top' => 4, 'right' => 10, 'bottom' => 4, 'left' => 10, 'isLinked' => false ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__chip' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'card_type' => 'product' ),
			)
		);

		$this->add_responsive_control(
			'number_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__chip' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'card_type' => 'product' ),
			)
		);

		$this->end_controls_section();

		// ---- Divider (product rule line) ----
		$this->start_controls_section(
			'style_divider',
			array(
				'label'     => esc_html__( 'Divider', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'card_type' => 'product' ),
			)
		);

		$this->fb_color( 'divider_color', esc_html__( 'Color', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-fbox__rule', 'rgba(255, 255, 255, 0.5)', 'background-color' );

		$this->add_responsive_control(
			'divider_length',
			array(
				'label'      => esc_html__( 'Length', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 22 ),
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__rule' => 'width: {{SIZE}}{{UNIT}};',
				),
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
				'selectors'  => array(
					'{{WRAPPER}} .dgx-fbox__rule' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Badge styling (background, icon, icon background, text + size).
	 *
	 * @return void
	 */
	private function register_badge_style() {
		$this->start_controls_section(
			'style_badge',
			array(
				'label'     => esc_html__( 'Badge', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'card_type'  => 'content',
					'show_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_bg_color',
			array(
				'label'     => esc_html__( 'Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__badge-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .dgx-fbox__badge-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_icon_bg_color',
			array(
				'label'     => esc_html__( 'Icon Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#435970',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__badge-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0E1A26',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__badge-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'badge_text_typography',
				'label'          => esc_html__( 'Text Typography', 'devgraphix-elementor-addons' ),
				'selector'       => '{{WRAPPER}} .dgx-fbox__badge-text',
				'fields_options' => array(
					'typography' => array( 'default' => 'custom' ),
					'font_size'  => array( 'default' => array( 'unit' => 'px', 'size' => 12 ) ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Chips styling.
	 *
	 * @return void
	 */
	private function register_chips_style() {
		$this->start_controls_section(
			'style_chips',
			array(
				'label'     => esc_html__( 'Chips', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'card_type' => 'content' ),
			)
		);

		$this->add_control(
			'chip_bg_color',
			array(
				'label'     => esc_html__( 'Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.7)',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__chip-item' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'chip_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0E1A26',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__chip-item' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'chip_typography',
				'label'    => esc_html__( 'Typography', 'devgraphix-elementor-addons' ),
				'selector' => '{{WRAPPER}} .dgx-fbox__chip-item',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Stats styling (value + label colors and font sizes).
	 *
	 * @return void
	 */
	private function register_stats_style() {
		$this->start_controls_section(
			'style_stats',
			array(
				'label'     => esc_html__( 'Stats', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'card_type' => 'content' ),
			)
		);

		$this->add_control(
			'stat_value_color',
			array(
				'label'     => esc_html__( 'Value Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0E1A26',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__stat-val' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'stat_value_typography',
				'label'          => esc_html__( 'Value Typography', 'devgraphix-elementor-addons' ),
				'selector'       => '{{WRAPPER}} .dgx-fbox__stat-val',
				'fields_options' => array(
					'typography' => array( 'default' => 'custom' ),
					'font_size'  => array( 'default' => array( 'unit' => 'px', 'size' => 28 ) ),
				),
			)
		);

		$this->add_control(
			'stat_label_color',
			array(
				'label'     => esc_html__( 'Label Color', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(14, 26, 38, 0.62)',
				'selectors' => array(
					'{{WRAPPER}} .dgx-fbox__stat-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'stat_label_typography',
				'label'          => esc_html__( 'Label Typography', 'devgraphix-elementor-addons' ),
				'selector'       => '{{WRAPPER}} .dgx-fbox__stat-label',
				'fields_options' => array(
					'typography' => array( 'default' => 'custom' ),
					'font_size'  => array( 'default' => array( 'unit' => 'px', 'size' => 11 ) ),
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
		$s    = $this->get_settings_for_display();
		$type = ! empty( $s['card_type'] ) ? $s['card_type'] : 'product';

		$classes = array( 'dgx-fbox', 'dgx-fbox--' . $type );
		if ( 'product' === $type ) {
			$classes[] = 'dgx-fbox--' . ( ! empty( $s['card_size'] ) ? $s['card_size'] : 'tall' );
		}
		$this->add_render_attribute( 'card', 'class', $classes );

		// Resolve the card link (manual link wins; else product permalink).
		$link = array();
		if ( ! empty( $s['cell_link']['url'] ) ) {
			$link = $s['cell_link'];
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			if ( 'product' === $type ) {
				$this->render_product_look( $s, $link );
			} else {
				$this->render_content_look( $s );
			}

			if ( ! empty( $link['url'] ) ) {
				$this->add_render_attribute( 'cardlink', 'class', 'dgx-fbox__link' );
				$this->add_link_attributes( 'cardlink', $link );
				$label = ! empty( $s['cell_heading'] ) ? $s['cell_heading'] : esc_html__( 'View', 'devgraphix-elementor-addons' );
				echo '<a ' . $this->get_render_attribute_string( 'cardlink' ) . '><span class="screen-reader-text">' . esc_html( $label ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render product look. Resolves dynamic data and may set $link by ref.
	 *
	 * @param array $s    Settings.
	 * @param array $link Card link (passed by reference so a product permalink can fill it).
	 * @return void
	 */
	private function render_product_look( array $s, array &$link ) {
		$data = $this->get_product_data( $s );

		$heading = ! empty( $s['cell_heading'] ) ? $s['cell_heading'] : $data['title'];
		$price   = ! empty( $s['price_value'] ) ? $s['price_value'] : $data['price'];
		$bg_url  = ! empty( $s['product_bg_image']['url'] ) ? $s['product_bg_image']['url'] : '';
		$size    = ! empty( $s['card_size'] ) ? $s['card_size'] : 'tall';
		$show_cta = 'yes' === ( isset( $s['show_cta'] ) ? $s['show_cta'] : '' ) && 'tall' === $size;

		// Auto-link to the product permalink when no manual link is set.
		if ( empty( $link['url'] ) && 'yes' === ( isset( $s['product_link_auto'] ) ? $s['product_link_auto'] : '' ) && '' !== $data['permalink'] ) {
			$link = array(
				'url'         => $data['permalink'],
				'is_external' => '',
				'nofollow'    => '',
			);
		}
		?>
		<?php if ( $bg_url ) : ?>
			<div class="dgx-fbox__bg" style="background-image:url(<?php echo esc_url( $bg_url ); ?>);"></div>
		<?php endif; ?>
		<div class="dgx-fbox__wash"></div>

		<div class="dgx-fbox__content">
			<div class="dgx-fbox__head">
				<?php if ( '' !== $s['cell_index'] ) : ?>
					<span class="dgx-fbox__chip"><?php echo esc_html( $s['cell_index'] ); ?></span>
					<span class="dgx-fbox__rule"></span>
				<?php endif; ?>
				<?php if ( '' !== $s['cell_category'] ) : ?>
					<span class="dgx-fbox__cat"><?php echo esc_html( $s['cell_category'] ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $heading || '' !== $s['cell_heading_accent'] ) : ?>
				<h3 class="dgx-fbox__title">
					<?php
					echo esc_html( $heading );
					if ( '' !== $s['cell_heading_accent'] ) {
						echo 'yes' === ( $s['accent_break'] ?? '' ) ? '<br>' : ' ';
						echo '<span class="dgx-fbox__accent">' . esc_html( $s['cell_heading_accent'] ) . '</span>';
					}
					?>
				</h3>
			<?php endif; ?>

			<?php if ( '' !== $price ) : ?>
				<div class="dgx-fbox__price">
					<?php echo esc_html( $s['price_prefix'] ); ?>
					<span class="dgx-fbox__price-val"><?php echo esc_html( $price ); ?></span><?php if ( '' !== $s['price_suffix'] ) : ?><span class="dgx-fbox__price-suffix"><?php echo esc_html( $s['price_suffix'] ); ?></span><?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_cta && '' !== $s['cta_text'] ) : ?>
				<?php
				$this->add_render_attribute( 'cta', 'class', 'dgx-fbox__cta' );
				if ( ! empty( $link['url'] ) ) {
					$this->add_link_attributes( 'cta', $link );
				}
				?>
				<a <?php echo $this->get_render_attribute_string( 'cta' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( $s['cta_text'] ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( '' !== $data['image'] ) : ?>
			<div class="dgx-fbox__cutout">
				<img class="dgx-fbox__product-img" src="<?php echo esc_url( $data['image'] ); ?>" alt="<?php echo esc_attr( $heading ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render content look.
	 *
	 * @param array $s Settings.
	 * @return void
	 */
	private function render_content_look( array $s ) {
		$img_url = ! empty( $s['content_image']['url'] ) ? $s['content_image']['url'] : '';
		$img_pos = ! empty( $s['content_image_position'] ) ? $s['content_image_position'] : 'right-bottom';

		$chips = array();
		if ( ! empty( $s['content_chips'] ) ) {
			$chips = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $s['content_chips'] ) ) );
		}

		$stats = array();
		if ( ! empty( $s['content_stats'] ) ) {
			foreach ( preg_split( '/\r\n|\r|\n/', $s['content_stats'] ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) {
					continue;
				}
				$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
				$stats[] = array(
					'val'   => $parts[0],
					'label' => isset( $parts[1] ) ? $parts[1] : '',
				);
			}
		}
		?>
		<?php if ( $img_url ) : ?>
			<img class="dgx-fbox__content-img dgx-fbox__content-img--<?php echo esc_attr( $img_pos ); ?>" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $s['cell_heading'] ); ?>" loading="lazy" />
		<?php endif; ?>

		<div class="dgx-fbox__content">
			<div class="dgx-fbox__head">
				<?php if ( '' !== $s['cell_category'] ) : ?>
					<span class="dgx-fbox__cat"><?php echo esc_html( $s['cell_category'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $s['cell_index'] ) : ?>
					<span class="dgx-fbox__num"><?php echo esc_html( $s['cell_index'] ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $s['cell_heading'] || '' !== $s['cell_heading_accent'] ) : ?>
				<h3 class="dgx-fbox__title">
					<?php
					echo esc_html( $s['cell_heading'] );
					if ( '' !== $s['cell_heading_accent'] ) {
						echo 'yes' === ( $s['accent_break'] ?? '' ) ? '<br>' : ' ';
						echo '<span class="dgx-fbox__accent">' . esc_html( $s['cell_heading_accent'] ) . '</span>';
					}
					?>
				</h3>
			<?php endif; ?>

			<?php if ( '' !== $s['cell_description'] ) : ?>
				<p class="dgx-fbox__desc"><?php echo esc_html( $s['cell_description'] ); ?></p>
			<?php endif; ?>

			<?php if ( 'yes' === ( isset( $s['show_badge'] ) ? $s['show_badge'] : '' ) && '' !== $s['badge_text'] ) : ?>
				<div class="dgx-fbox__badge">
					<?php if ( ! empty( $s['badge_icon']['value'] ) ) : ?>
						<span class="dgx-fbox__badge-icon"><?php Icons_Manager::render_icon( $s['badge_icon'], array( 'aria-hidden' => 'true' ) ); ?></span>
					<?php endif; ?>
					<span class="dgx-fbox__badge-text"><?php echo esc_html( $s['badge_text'] ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $chips ) ) : ?>
				<div class="dgx-fbox__chips">
					<?php foreach ( $chips as $chip ) : ?>
						<span class="dgx-fbox__chip-item"><?php echo esc_html( $chip ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $stats ) ) : ?>
				<div class="dgx-fbox__stats">
					<?php foreach ( $stats as $stat ) : ?>
						<div class="dgx-fbox__stat">
							<div class="dgx-fbox__stat-val"><?php echo esc_html( $stat['val'] ); ?></div>
							<?php if ( '' !== $stat['label'] ) : ?>
								<div class="dgx-fbox__stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
