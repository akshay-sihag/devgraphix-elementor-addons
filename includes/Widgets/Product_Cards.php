<?php
/**
 * Product Cards widget.
 *
 * A dynamic product card grid/carousel with two pixel-perfect looks pulled
 * from homepage.html:
 *   - vertical   : dark "glass" card — Rx pill, index counter, large centered
 *                  packshot, family label, serif name, sub line, price + arrow.
 *   - horizontal : light card — packshot left, serif name, description,
 *                  check-list bullets, price + CTA button.
 *
 * The product TITLE and FEATURED IMAGE are always pulled automatically from
 * each product in the query. Every other text slot is mapped to an ACF field
 * chosen from a dropdown (read from the site's ACF field groups), and the
 * price comes straight from WooCommerce. The widget renders cards only — no
 * wrapper background.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Product_Cards
 */
class Product_Cards extends Base_Widget {

	/**
	 * Widget machine name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dgx-product-cards';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Product Cards', 'devgraphix-elementor-addons' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dgx-ico dgx-ico-products';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'product', 'card', 'woocommerce', 'acf', 'grid', 'carousel' ) );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'dgx-ea-product-cards' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'dgx-ea-product-cards' );
	}

	// =======================================================================
	// HELPERS — options
	// =======================================================================

	/**
	 * Cached Select2 post options, keyed by post type.
	 *
	 * @var array<string,array<int,string>>
	 */
	private static $post_options = array();

	/**
	 * Cached ACF field options, keyed by type filter.
	 *
	 * @var array<string,array<string,string>>
	 */
	private static $acf_options = array();

	/**
	 * Public post types as control options (skips attachments).
	 *
	 * @return array<string,string>
	 */
	private function get_post_type_options() {
		$options = array();
		$types   = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $types as $slug => $obj ) {
			if ( 'attachment' === $slug ) {
				continue;
			}
			$options[ $slug ] = isset( $obj->labels->singular_name ) ? $obj->labels->singular_name : $slug;
		}

		return $options;
	}

	/**
	 * Published items of a single post type for the "selected" Select2.
	 *
	 * @param string $post_type Post type slug.
	 * @return array<int,string>
	 */
	private function get_post_options( $post_type ) {
		if ( isset( self::$post_options[ $post_type ] ) ) {
			return self::$post_options[ $post_type ];
		}

		$options = array();

		$posts = get_posts(
			array(
				'post_type'              => $post_type,
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
			$title = ( '' !== $post->post_title ) ? $post->post_title : sprintf( '#%d', $post->ID );
			$options[ $post->ID ] = $title;
		}

		self::$post_options[ $post_type ] = $options;

		return $options;
	}

	/**
	 * Sanitize a post-type slug into a control-id-safe suffix.
	 *
	 * @param string $post_type Post type slug.
	 * @return string
	 */
	private function pt_key( $post_type ) {
		return preg_replace( '/[^a-z0-9_]/', '_', strtolower( (string) $post_type ) );
	}

	/**
	 * Whether ACF is available.
	 *
	 * @return bool
	 */
	private function acf_active() {
		return function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' );
	}

	/**
	 * Build a dropdown of ACF fields (read from the site's field groups).
	 *
	 * @param string[] $types Optional ACF field types to keep (e.g. image).
	 * @return array<string,string>
	 */
	private function get_acf_field_options( array $types = array() ) {
		$cache_key = empty( $types ) ? 'all' : implode( ',', $types );
		if ( isset( self::$acf_options[ $cache_key ] ) ) {
			return self::$acf_options[ $cache_key ];
		}

		$options = array( '' => esc_html__( '— None —', 'devgraphix-elementor-addons' ) );

		if ( $this->acf_active() ) {
			$groups = acf_get_field_groups();
			foreach ( (array) $groups as $group ) {
				$key = isset( $group['key'] ) ? $group['key'] : '';
				if ( '' === $key ) {
					continue;
				}
				$fields = acf_get_fields( $key );
				if ( empty( $fields ) ) {
					continue;
				}
				$group_title = isset( $group['title'] ) ? $group['title'] : esc_html__( 'Fields', 'devgraphix-elementor-addons' );
				$this->collect_acf_fields( $fields, $group_title, $types, $options );
			}
		}

		self::$acf_options[ $cache_key ] = $options;

		return $options;
	}

	/**
	 * Recursively flatten ACF fields into the options array.
	 *
	 * @param array    $fields      ACF fields.
	 * @param string   $group_title Field group title (for the option label).
	 * @param string[] $types       Type filter.
	 * @param array    $options     Options array, by reference.
	 * @return void
	 */
	private function collect_acf_fields( $fields, $group_title, array $types, array &$options ) {
		foreach ( (array) $fields as $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : '';
			$name = isset( $field['name'] ) ? $field['name'] : '';
			$label = isset( $field['label'] ) && '' !== $field['label'] ? $field['label'] : $name;

			// Skip layout-only fields.
			if ( in_array( $type, array( 'tab', 'message', 'accordion' ), true ) ) {
				continue;
			}

			if ( '' !== $name && ( empty( $types ) || in_array( $type, $types, true ) ) ) {
				$options[ $name ] = $group_title . ' › ' . $label;
			}

			// Descend into group sub-fields.
			if ( 'group' === $type && ! empty( $field['sub_fields'] ) ) {
				$this->collect_acf_fields( $field['sub_fields'], $group_title, $types, $options );
			}
		}
	}

	// =======================================================================
	// HELPERS — control builders
	// =======================================================================

	/**
	 * Register an ACF-field picker (Select2 of field names).
	 *
	 * @param string              $id        Control id.
	 * @param string              $label     Label.
	 * @param string[]            $types     ACF type filter.
	 * @param array<string,mixed> $condition Condition.
	 * @return void
	 */
	private function add_acf_select( $id, $label, array $types = array(), array $condition = array() ) {
		$this->add_control(
			$id,
			array(
				'label'       => $label,
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_acf_field_options( $types ),
				'default'     => '',
				'condition'   => $condition,
			)
		);
	}

	/**
	 * Register a simple colour control.
	 *
	 * @param string              $id        Control id.
	 * @param string              $label     Label.
	 * @param string              $selector  Full CSS selector.
	 * @param string              $default   Default colour.
	 * @param array<string,mixed> $condition Condition.
	 * @param string              $prop      CSS property (default color).
	 * @return void
	 */
	private function add_color( $id, $label, $selector, $default, array $condition = array(), $prop = 'color' ) {
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

	// =======================================================================
	// HELPERS — data resolution
	// =======================================================================

	/**
	 * Read an ACF/meta value for a post as a string.
	 *
	 * @param int    $post_id Post id.
	 * @param string $name    Field name.
	 * @return string
	 */
	private function get_meta_value( $post_id, $name ) {
		if ( ! $post_id || '' === $name ) {
			return '';
		}

		$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );

		if ( is_array( $value ) ) {
			$flat = array();
			foreach ( $value as $item ) {
				if ( is_scalar( $item ) ) {
					$flat[] = (string) $item;
				}
			}
			return implode( ', ', array_filter( $flat ) );
		}

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Resolve a WooCommerce product's price amount + recurring period.
	 *
	 * A plain product price is just an amount (e.g. "$99.00"); the "/ month"
	 * tenure only exists on WooCommerce Subscriptions products, where the
	 * billing period/interval are stored separately. We read both.
	 *
	 * @param \WC_Product $product WooCommerce product.
	 * @return array{amount:string,period:string}
	 */
	private function get_woo_price( $product ) {
		$amount = '';
		$period = '';

		$price = $product->get_price();
		if ( '' !== $price && null !== $price && function_exists( 'wc_price' ) ) {
			$amount = wp_strip_all_tags( html_entity_decode( wc_price( $price ) ) );
		}

		// WooCommerce Subscriptions — append the billing period.
		if ( class_exists( 'WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
			$base     = \WC_Subscriptions_Product::get_period( $product );    // day|week|month|year.
			$interval = (int) \WC_Subscriptions_Product::get_interval( $product );

			if ( $base ) {
				if ( function_exists( 'wcs_get_subscription_period_strings' ) ) {
					$phrase = wcs_get_subscription_period_strings( max( 1, $interval ), $base ); // e.g. "month", "3 months".
				} else {
					$phrase = ( $interval > 1 ) ? $interval . ' ' . $base . 's' : $base;
				}
				$period = '/ ' . $phrase;
			}
		}

		return array(
			'amount' => $amount,
			'period' => $period,
		);
	}

	/**
	 * Manual price suffix (used when there's no subscription period).
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	private function price_suffix_fallback( array $s ) {
		return isset( $s['price_suffix'] ) ? trim( (string) $s['price_suffix'] ) : '';
	}

	/**
	 * Resolve the price amount + suffix for a card.
	 *
	 * @param array $s       Settings.
	 * @param int    $post_id Post id.
	 * @return array{amount:string,suffix:string}
	 */
	private function resolve_price( array $s, $post_id ) {
		$source = isset( $s['price_source'] ) ? $s['price_source'] : 'woo';

		if ( 'none' === $source ) {
			return array( 'amount' => '', 'suffix' => '' );
		}

		if ( 'acf' === $source ) {
			return array(
				'amount' => $this->get_meta_value( $post_id, isset( $s['price_field'] ) ? $s['price_field'] : '' ),
				'suffix' => $this->price_suffix_fallback( $s ),
			);
		}

		// WooCommerce.
		if ( $post_id && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post_id );
			if ( $product && is_callable( array( $product, 'get_price' ) ) ) {
				$woo    = $this->get_woo_price( $product );
				$suffix = ( '' !== $woo['period'] ) ? $woo['period'] : $this->price_suffix_fallback( $s );
				return array( 'amount' => $woo['amount'], 'suffix' => $suffix );
			}
		}

		return array( 'amount' => '', 'suffix' => $this->price_suffix_fallback( $s ) );
	}

	/**
	 * Resolve a bullet list from an ACF field.
	 *
	 * @param string $field   ACF field name.
	 * @param int    $post_id Post id.
	 * @return string[]
	 */
	private function resolve_bullets( $field, $post_id ) {
		if ( '' === $field || ! $post_id ) {
			return array();
		}

		$value = function_exists( 'get_field' ) ? get_field( $field, $post_id ) : get_post_meta( $post_id, $field, true );

		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $item ) {
				if ( is_scalar( $item ) ) {
					$out[] = (string) $item;
				} elseif ( is_array( $item ) ) {
					$first = reset( $item );
					$out[]  = is_scalar( $first ) ? (string) $first : '';
				}
			}
			return array_values( array_filter( array_map( 'trim', $out ) ) );
		}

		$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
		return array_values( array_filter( array_map( 'trim', $lines ) ) );
	}

	/**
	 * Convert an ACF image value (id/url/array) into a URL.
	 *
	 * @param mixed $value ACF value.
	 * @return string
	 */
	private function image_value_to_url( $value ) {
		if ( is_numeric( $value ) ) {
			$url = wp_get_attachment_image_url( (int) $value, 'large' );
			return $url ? $url : '';
		}
		if ( is_string( $value ) ) {
			return $value;
		}
		if ( is_array( $value ) ) {
			if ( ! empty( $value['url'] ) ) {
				return $value['url'];
			}
			$id = ! empty( $value['ID'] ) ? $value['ID'] : ( ! empty( $value['id'] ) ? $value['id'] : 0 );
			if ( $id ) {
				$url = wp_get_attachment_image_url( (int) $id, 'large' );
				return $url ? $url : '';
			}
		}
		return '';
	}

	/**
	 * Resolve the product image URL (featured / ACF), with manual fallback.
	 *
	 * @param array $s       Settings.
	 * @param int    $post_id Post id.
	 * @return string
	 */
	private function resolve_image( array $s, $post_id ) {
		$source = isset( $s['image_source'] ) ? $s['image_source'] : 'featured';
		$url    = '';

		if ( 'featured' === $source && $post_id ) {
			$url = (string) get_the_post_thumbnail_url( $post_id, 'large' );
		} elseif ( 'acf' === $source && $post_id ) {
			$field = isset( $s['image_field'] ) ? $s['image_field'] : '';
			if ( '' !== $field ) {
				$value = function_exists( 'get_field' ) ? get_field( $field, $post_id ) : get_post_meta( $post_id, $field, true );
				$url   = $this->image_value_to_url( $value );
			}
		}

		if ( '' === $url && ! empty( $s['fallback_image']['url'] ) ) {
			$url = $s['fallback_image']['url'];
		}

		return $url;
	}

	/**
	 * Resolve a link control into an Elementor-style link array.
	 *
	 * @param array  $s          Settings.
	 * @param string $source_key Settings key holding the source select.
	 * @param string $url_key     Settings key holding the URL control.
	 * @param int    $post_id     Post id.
	 * @return array<string,mixed>
	 */
	private function resolve_link( array $s, $source_key, $url_key, $post_id ) {
		$source = isset( $s[ $source_key ] ) ? $s[ $source_key ] : 'permalink';

		if ( 'custom' === $source ) {
			return ( ! empty( $s[ $url_key ]['url'] ) ) ? $s[ $url_key ] : array();
		}

		if ( 'permalink' === $source && $post_id ) {
			return array( 'url' => (string) get_permalink( $post_id ) );
		}

		return array();
	}

	/**
	 * Run the configured query and return post IDs.
	 *
	 * @param array $s Settings.
	 * @return int[]
	 */
	private function get_query_ids( array $s ) {
		$post_type = ! empty( $s['query_post_type'] ) ? $s['query_post_type'] : 'product';
		$source    = isset( $s['query_source'] ) ? $s['query_source'] : 'latest';

		if ( 'selected' === $source ) {
			$key = 'query_posts_' . $this->pt_key( $post_type );
			$ids = array_filter( array_map( 'intval', (array) ( isset( $s[ $key ] ) ? $s[ $key ] : array() ) ) );
			if ( empty( $ids ) ) {
				return array();
			}
			$posts = get_posts(
				array(
					'post_type'      => 'any',
					'post__in'       => $ids,
					'orderby'        => 'post__in',
					'posts_per_page' => count( $ids ),
					'post_status'    => 'publish',
				)
			);
		} else {
			$posts = get_posts(
				array(
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => max( 1, (int) ( isset( $s['query_count'] ) ? $s['query_count'] : 2 ) ),
					'orderby'        => isset( $s['query_orderby'] ) ? $s['query_orderby'] : 'date',
					'order'          => isset( $s['query_order'] ) ? $s['query_order'] : 'DESC',
				)
			);
		}

		return wp_list_pluck( $posts, 'ID' );
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
		$this->register_layout_section();
		$this->register_query_section();
		$this->register_fields_section();
		$this->register_image_section();
		$this->register_carousel_section();

		$this->register_card_style();
		$this->register_text_style();
		$this->register_price_style();
		$this->register_bullets_style();
		$this->register_button_style();
		$this->register_carousel_style();
	}

	/**
	 * Layout & display section.
	 *
	 * @return void
	 */
	private function register_layout_section() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Card Layout', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => array(
					'vertical'   => esc_html__( 'Vertical (glass / dark)', 'devgraphix-elementor-addons' ),
					'horizontal' => esc_html__( 'Horizontal (light)', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'display',
			array(
				'label'   => esc_html__( 'Display As', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'     => esc_html__( 'Grid', 'devgraphix-elementor-addons' ),
					'carousel' => esc_html__( 'Carousel', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns_v',
			array(
				'label'          => esc_html__( 'Columns', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'default'        => 2,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'selectors'      => array( '{{WRAPPER}} .dgx-pcards' => '--dgx-pc-cols: {{VALUE}};' ),
				'condition'      => array( 'layout' => 'vertical', 'display' => 'grid' ),
			)
		);

		$this->add_responsive_control(
			'columns_h',
			array(
				'label'          => esc_html__( 'Columns', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 4,
				'default'        => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'selectors'      => array( '{{WRAPPER}} .dgx-pcards' => '--dgx-pc-cols: {{VALUE}};' ),
				'condition'      => array( 'layout' => 'horizontal', 'display' => 'grid' ),
			)
		);

		$this->add_responsive_control(
			'per_view_v',
			array(
				'label'          => esc_html__( 'Cards Per View', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'default'        => 2,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'selectors'      => array( '{{WRAPPER}} .dgx-pcards' => '--dgx-pc-per: {{VALUE}};' ),
				'condition'      => array( 'layout' => 'vertical', 'display' => 'carousel' ),
			)
		);

		$this->add_responsive_control(
			'per_view_h',
			array(
				'label'          => esc_html__( 'Cards Per View', 'devgraphix-elementor-addons' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 4,
				'default'        => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'selectors'      => array( '{{WRAPPER}} .dgx-pcards' => '--dgx-pc-per: {{VALUE}};' ),
				'condition'      => array( 'layout' => 'horizontal', 'display' => 'carousel' ),
			)
		);

		$this->add_responsive_control(
			'card_gap',
			array(
				'label'      => esc_html__( 'Gap Between Cards', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 20 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards' => '--dgx-pc-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Query (data source) section.
	 *
	 * @return void
	 */
	private function register_query_section() {
		$this->start_controls_section(
			'section_query',
			array(
				'label' => esc_html__( 'Query (Data Source)', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$post_types = $this->get_post_type_options();

		$this->add_control(
			'query_post_type',
			array(
				'label'       => esc_html__( 'Post Type', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => isset( $post_types['product'] ) ? 'product' : key( $post_types ),
				'options'     => $post_types,
				'description' => esc_html__( 'Drives both the Latest query and the manual selector below.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'query_source',
			array(
				'label'   => esc_html__( 'Get Items', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => array(
					'latest'   => esc_html__( 'Latest (auto)', 'devgraphix-elementor-addons' ),
					'selected' => esc_html__( 'Manually selected', 'devgraphix-elementor-addons' ),
				),
			)
		);

		// One manual selector per post type — only the one matching the chosen
		// Post Type shows, so its list is filtered to that type only.
		foreach ( $post_types as $slug => $label ) {
			$this->add_control(
				'query_posts_' . $this->pt_key( $slug ),
				array(
					/* translators: %s: post type label. */
					'label'       => sprintf( esc_html__( 'Select %s', 'devgraphix-elementor-addons' ), $label ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'options'     => $this->get_post_options( $slug ),
					'condition'   => array(
						'query_source'    => 'selected',
						'query_post_type' => $slug,
					),
				)
			);
		}

		$this->add_control(
			'query_count',
			array(
				'label'     => esc_html__( 'Number of Items', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 2,
				'condition' => array( 'query_source' => 'latest' ),
			)
		);

		$this->add_control(
			'query_orderby',
			array(
				'label'     => esc_html__( 'Order By', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'       => esc_html__( 'Date', 'devgraphix-elementor-addons' ),
					'title'      => esc_html__( 'Title', 'devgraphix-elementor-addons' ),
					'menu_order' => esc_html__( 'Menu Order', 'devgraphix-elementor-addons' ),
					'rand'       => esc_html__( 'Random', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'query_source' => 'latest' ),
			)
		);

		$this->add_control(
			'query_order',
			array(
				'label'     => esc_html__( 'Order', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => esc_html__( 'Descending', 'devgraphix-elementor-addons' ),
					'ASC'  => esc_html__( 'Ascending', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'query_source' => 'latest' ),
			)
		);

		$this->add_control(
			'enable_demo',
			array(
				'label'        => esc_html__( 'Show Demo When Empty', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'description'  => esc_html__( 'Renders sample placeholder cards when the query returns nothing — turn off to hide them.', 'devgraphix-elementor-addons' ),
				'label_on'     => esc_html__( 'Yes', 'devgraphix-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'devgraphix-elementor-addons' ),
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Field-mapping section.
	 *
	 * @return void
	 */
	private function register_fields_section() {
		$this->start_controls_section(
			'section_fields',
			array(
				'label' => esc_html__( 'Fields', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'fields_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'The product title and featured image are pulled automatically. Map the other fields to ACF fields below.', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		if ( ! $this->acf_active() ) {
			$this->add_control(
				'acf_missing_note',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'Advanced Custom Fields (ACF) is not active — install it to populate the field dropdowns.', 'devgraphix-elementor-addons' ),
					'content_classes' => 'elementor-control-field-description',
				)
			);
		}

		// ----- Price -----
		$this->add_control(
			'price_source',
			array(
				'label'   => esc_html__( 'Price', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'woo',
				'options' => array(
					'woo'  => esc_html__( 'WooCommerce price', 'devgraphix-elementor-addons' ),
					'acf'  => esc_html__( 'ACF field', 'devgraphix-elementor-addons' ),
					'none' => esc_html__( 'Hide price', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_acf_select( 'price_field', esc_html__( 'Price Field', 'devgraphix-elementor-addons' ), array(), array( 'price_source' => 'acf' ) );

		$this->add_control(
			'price_suffix',
			array(
				'label'       => esc_html__( 'Price Suffix (fallback)', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => '/mo',
				'description' => esc_html__( 'WooCommerce Subscriptions products show their billing period automatically (e.g. "/ month"). This is only used for non-subscription products.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_control(
			'from_label',
			array(
				'label'   => esc_html__( '"From" Label', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'FROM', 'devgraphix-elementor-addons' ),
			)
		);

		// ----- Vertical -----
		$this->add_control(
			'heading_vertical',
			array(
				'label'     => esc_html__( 'Vertical Fields', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_control(
			'show_rx',
			array(
				'label'        => esc_html__( 'Show Tag Pill', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_control(
			'rx_text',
			array(
				'label'     => esc_html__( 'Tag Pill Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'NEW',
				'condition' => array( 'layout' => 'vertical', 'show_rx' => 'yes' ),
			)
		);

		$this->add_control(
			'show_counter',
			array(
				'label'        => esc_html__( 'Show Index Counter (01 / 02)', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_acf_select( 'family_field', esc_html__( 'Family Label Field', 'devgraphix-elementor-addons' ), array(), array( 'layout' => 'vertical' ) );
		$this->add_acf_select( 'sub_field', esc_html__( 'Sub Line Field', 'devgraphix-elementor-addons' ), array(), array( 'layout' => 'vertical' ) );

		// ----- Horizontal -----
		$this->add_control(
			'heading_horizontal',
			array(
				'label'     => esc_html__( 'Horizontal Fields', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_acf_select( 'desc_field', esc_html__( 'Description Field', 'devgraphix-elementor-addons' ), array(), array( 'layout' => 'horizontal' ) );
		$this->add_acf_select( 'bullets_field', esc_html__( 'Bullets Field', 'devgraphix-elementor-addons' ), array(), array( 'layout' => 'horizontal' ) );

		$this->add_control(
			'bullets_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Bullets accept a textarea (one per line), a checkbox/select, or a repeater (first sub-field used).', 'devgraphix-elementor-addons' ),
				'content_classes' => 'elementor-control-field-description',
				'condition'       => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_control(
			'show_button',
			array(
				'label'        => esc_html__( 'Show CTA Button', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'     => esc_html__( 'Button Text', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Learn More',
				'condition' => array( 'layout' => 'horizontal', 'show_button' => 'yes' ),
			)
		);

		// ----- Links -----
		$this->add_control(
			'heading_links',
			array(
				'label'     => esc_html__( 'Links', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'card_link_source',
			array(
				'label'     => esc_html__( 'Card Link', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'permalink',
				'options'   => array(
					''          => esc_html__( 'None', 'devgraphix-elementor-addons' ),
					'permalink' => esc_html__( 'Product permalink', 'devgraphix-elementor-addons' ),
					'custom'    => esc_html__( 'Custom URL', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_control(
			'card_link',
			array(
				'label'     => esc_html__( 'Custom URL', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'layout' => 'vertical', 'card_link_source' => 'custom' ),
			)
		);

		$this->add_control(
			'button_link_source',
			array(
				'label'     => esc_html__( 'Button Link', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'permalink',
				'options'   => array(
					''          => esc_html__( 'None', 'devgraphix-elementor-addons' ),
					'permalink' => esc_html__( 'Product permalink', 'devgraphix-elementor-addons' ),
					'custom'    => esc_html__( 'Custom URL', 'devgraphix-elementor-addons' ),
				),
				'condition' => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_control(
			'button_link',
			array(
				'label'     => esc_html__( 'Custom URL', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'layout' => 'horizontal', 'button_link_source' => 'custom' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Image section.
	 *
	 * @return void
	 */
	private function register_image_section() {
		$this->start_controls_section(
			'section_image',
			array(
				'label' => esc_html__( 'Image', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'image_source',
			array(
				'label'   => esc_html__( 'Image Source', 'devgraphix-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'featured',
				'options' => array(
					'featured' => esc_html__( 'Featured image', 'devgraphix-elementor-addons' ),
					'acf'      => esc_html__( 'ACF field', 'devgraphix-elementor-addons' ),
					'none'     => esc_html__( 'None', 'devgraphix-elementor-addons' ),
				),
			)
		);

		$this->add_acf_select( 'image_field', esc_html__( 'Image Field', 'devgraphix-elementor-addons' ), array( 'image', 'file', 'gallery' ), array( 'image_source' => 'acf' ) );

		$this->add_control(
			'fallback_image',
			array(
				'label'       => esc_html__( 'Fallback / Demo Image', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => esc_html__( 'Used when a product has no image, and for demo cards.', 'devgraphix-elementor-addons' ),
			)
		);

		$this->add_responsive_control(
			'image_height_v',
			array(
				'label'      => esc_html__( 'Image Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 80, 'max' => 600 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 300 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__img' => 'height: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_responsive_control(
			'image_height_h',
			array(
				'label'      => esc_html__( 'Image Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 80, 'max' => 480 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 198 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__media' => 'height: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_responsive_control(
			'image_media_width_h',
			array(
				'label'      => esc_html__( 'Image Column Width', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 100, 'max' => 320 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 180 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard' => 'grid-template-columns: {{SIZE}}{{UNIT}} 1fr;' ),
				'condition'  => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_control(
			'image_fit',
			array(
				'label'     => esc_html__( 'Image Fit', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'contain',
				'options'   => array(
					'contain' => esc_html__( 'Contain', 'devgraphix-elementor-addons' ),
					'cover'   => esc_html__( 'Cover', 'devgraphix-elementor-addons' ),
				),
				'selectors' => array( '{{WRAPPER}} .dgx-pcard__img' => 'object-fit: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'show_halo',
			array(
				'label'        => esc_html__( 'Show Glow Behind Image', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'vertical' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Carousel options section.
	 *
	 * @return void
	 */
	private function register_carousel_section() {
		$this->start_controls_section(
			'section_carousel',
			array(
				'label'     => esc_html__( 'Carousel', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'display' => 'carousel' ),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'        => esc_html__( 'Arrows', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'label'        => esc_html__( 'Dots', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'carousel_loop',
			array(
				'label'        => esc_html__( 'Loop', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'devgraphix-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay_interval',
			array(
				'label'     => esc_html__( 'Autoplay Interval (ms)', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1000,
				'max'       => 15000,
				'step'      => 500,
				'default'   => 5000,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card style section.
	 *
	 * @return void
	 */
	private function register_card_style() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Card', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// ---- Vertical ----
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'card_bg_v',
				'types'          => array( 'classic', 'gradient' ),
				'selector'       => '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard',
				'fields_options' => array(
					'background'     => array( 'default' => 'gradient' ),
					'color'          => array( 'default' => '#0f1530' ),
					'color_b'        => array( 'default' => '#2b3b75' ),
					'gradient_angle' => array( 'default' => array( 'unit' => 'deg', 'size' => 135 ) ),
				),
				'condition'      => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'card_border_v',
				'selector'       => '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard',
				'fields_options' => array(
					'border' => array( 'default' => 'solid' ),
					'width'  => array( 'default' => array( 'unit' => 'px', 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'isLinked' => true ) ),
					'color'  => array( 'default' => 'rgba(255,255,255,0.14)' ),
				),
				'condition'      => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_responsive_control(
			'card_radius_v',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard' => 'border-radius: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_responsive_control(
			'card_padding_v',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array( 'unit' => 'px', 'top' => 24, 'right' => 22, 'bottom' => 22, 'left' => 22, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'vertical' ),
			)
		);

		$this->add_responsive_control(
			'card_minh_v',
			array(
				'label'      => esc_html__( 'Min Height', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 300, 'max' => 800 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 540 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard' => 'min-height: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'vertical' ),
			)
		);

		// ---- Horizontal ----
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'card_bg_h',
				'types'          => array( 'classic', 'gradient' ),
				'selector'       => '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard',
				'fields_options' => array(
					'background' => array( 'default' => 'classic' ),
					'color'      => array( 'default' => 'rgba(255,255,255,0.9)' ),
				),
				'condition'      => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'card_border_h',
				'selector'       => '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard',
				'fields_options' => array(
					'border' => array( 'default' => 'solid' ),
					'width'  => array( 'default' => array( 'unit' => 'px', 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'isLinked' => true ) ),
					'color'  => array( 'default' => 'rgba(14,26,38,0.12)' ),
				),
				'condition'      => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_responsive_control(
			'card_radius_h',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 28 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard' => 'border-radius: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_responsive_control(
			'card_padding_h',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array( 'unit' => 'px', 'top' => 26, 'right' => 26, 'bottom' => 26, 'left' => 26, 'isLinked' => true ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .dgx-pcard',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Text style section (name, family/desc, sub, tag pill, counter).
	 *
	 * @return void
	 */
	private function register_text_style() {
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Text', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'accent_color',
			array(
				'label'       => esc_html__( 'Accent Colour', 'devgraphix-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#a6c3e4',
				'selectors'   => array( '{{WRAPPER}} .dgx-pcards' => '--dgx-pc-accent: {{VALUE}};' ),
				'condition'   => array( 'layout' => 'vertical' ),
				'description' => esc_html__( 'Family label, glow and arrow.', 'devgraphix-elementor-addons' ),
			)
		);

		// Product name.
		$this->add_control(
			'heading_name',
			array(
				'label'     => esc_html__( 'Product Name', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_color( 'name_color_v', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__name', '#ffffff', array( 'layout' => 'vertical' ) );
		$this->add_typo( 'name_typo_v', '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__name', array( 'font_size' => $this->fs( 26 ), 'font_weight' => array( 'default' => '400' ), 'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 1.05 ) ) ), array( 'layout' => 'vertical' ) );

		$this->add_color( 'name_color_h', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__name', '#0e1a26', array( 'layout' => 'horizontal' ) );
		$this->add_typo( 'name_typo_h', '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__name', array( 'font_size' => $this->fs( 32 ), 'font_weight' => array( 'default' => '400' ), 'line_height' => array( 'default' => array( 'unit' => 'em', 'size' => 1.05 ) ) ), array( 'layout' => 'horizontal' ) );

		// Family label (vertical).
		$this->add_control(
			'heading_family',
			array(
				'label'     => esc_html__( 'Family Label', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'vertical' ),
			)
		);
		$this->add_color( 'family_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__family', '#a6c3e4', array( 'layout' => 'vertical' ) );
		$this->add_typo( 'family_typo', '{{WRAPPER}} .dgx-pcard__family', array( 'font_size' => $this->fs( 9 ) ), array( 'layout' => 'vertical' ) );

		// Description (horizontal).
		$this->add_control(
			'heading_desc',
			array(
				'label'     => esc_html__( 'Description', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'horizontal' ),
			)
		);
		$this->add_color( 'desc_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__desc', 'rgba(14,26,38,0.6)', array( 'layout' => 'horizontal' ) );
		$this->add_typo( 'desc_typo', '{{WRAPPER}} .dgx-pcard__desc', array( 'font_size' => $this->fs( 13 ) ), array( 'layout' => 'horizontal' ) );

		// Sub line (vertical).
		$this->add_control(
			'heading_sub',
			array(
				'label'     => esc_html__( 'Sub Line', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'vertical' ),
			)
		);
		$this->add_color( 'sub_color', esc_html__( 'Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__sub', 'rgba(255,255,255,0.6)', array( 'layout' => 'vertical' ) );
		$this->add_typo( 'sub_typo', '{{WRAPPER}} .dgx-pcard__sub', array( 'font_size' => $this->fs( 11 ) ), array( 'layout' => 'vertical' ) );

		// Tag pill + counter (vertical).
		$this->add_control(
			'heading_rx',
			array(
				'label'     => esc_html__( 'Tag Pill & Counter', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'vertical' ),
			)
		);
		$this->add_color( 'rx_bg', esc_html__( 'Pill Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__rx', '#ffffff', array( 'layout' => 'vertical' ), 'background-color' );
		$this->add_color( 'rx_color', esc_html__( 'Pill Text', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__rx', '#0f1530', array( 'layout' => 'vertical' ) );
		$this->add_color( 'counter_color', esc_html__( 'Counter Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__count', 'rgba(255,255,255,0.65)', array( 'layout' => 'vertical' ) );

		$this->end_controls_section();
	}

	/**
	 * Price style section.
	 *
	 * @return void
	 */
	private function register_price_style() {
		$this->start_controls_section(
			'section_style_price',
			array(
				'label' => esc_html__( 'Price', 'devgraphix-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_color( 'from_color_v', esc_html__( '"From" Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__from', 'rgba(255,255,255,0.5)', array( 'layout' => 'vertical' ) );
		$this->add_color( 'price_color_v', esc_html__( 'Price Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__price', '#ffffff', array( 'layout' => 'vertical' ) );
		$this->add_typo( 'price_typo_v', '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__price', array( 'font_size' => $this->fs( 28 ), 'font_weight' => array( 'default' => '400' ) ), array( 'layout' => 'vertical' ) );
		$this->add_color( 'suffix_color_v', esc_html__( 'Suffix Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--vertical .dgx-pcard__price-suffix', 'rgba(255,255,255,0.6)', array( 'layout' => 'vertical' ) );

		$this->add_color( 'from_color_h', esc_html__( '"From" Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__from', 'rgba(14,26,38,0.6)', array( 'layout' => 'horizontal' ) );
		$this->add_color( 'price_color_h', esc_html__( 'Price Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__price', '#0e1a26', array( 'layout' => 'horizontal' ) );
		$this->add_typo( 'price_typo_h', '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__price', array( 'font_size' => $this->fs( 30 ), 'font_weight' => array( 'default' => '400' ) ), array( 'layout' => 'horizontal' ) );
		$this->add_color( 'suffix_color_h', esc_html__( 'Suffix Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcards--horizontal .dgx-pcard__price-suffix', 'rgba(14,26,38,0.6)', array( 'layout' => 'horizontal' ) );

		$this->end_controls_section();
	}

	/**
	 * Bullets style section (horizontal).
	 *
	 * @return void
	 */
	private function register_bullets_style() {
		$this->start_controls_section(
			'section_style_bullets',
			array(
				'label'     => esc_html__( 'Bullets', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_color( 'bullet_text_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__bullet', '#0e1a26' );
		$this->add_color( 'bullet_icon_color', esc_html__( 'Check Icon Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__bullet-icon', '#435970' );
		$this->add_typo( 'bullet_typo', '{{WRAPPER}} .dgx-pcard__bullet', array( 'font_size' => $this->fs( 12 ) ) );

		$this->add_responsive_control(
			'bullet_gap',
			array(
				'label'      => esc_html__( 'Row Spacing', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 24 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 6 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcard__bullets' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Button style section (horizontal).
	 *
	 * @return void
	 */
	private function register_button_style() {
		$this->start_controls_section(
			'section_style_button',
			array(
				'label'     => esc_html__( 'Button', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'layout' => 'horizontal', 'show_button' => 'yes' ),
			)
		);

		$this->add_color( 'button_bg', esc_html__( 'Background', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__btn', '#0e1a26', array(), 'background-color' );
		$this->add_color( 'button_color', esc_html__( 'Text Colour', 'devgraphix-elementor-addons' ), '{{WRAPPER}} .dgx-pcard__btn', '#ffffff' );
		$this->add_typo( 'button_typo', '{{WRAPPER}} .dgx-pcard__btn', array( 'font_size' => $this->fs( 12 ), 'font_weight' => array( 'default' => '600' ) ) );

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array( 'unit' => 'px', 'top' => 11, 'right' => 18, 'bottom' => 11, 'left' => 18, 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcard__btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'devgraphix-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array( '{{WRAPPER}} .dgx-pcard__btn' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Carousel navigation style section.
	 *
	 * @return void
	 */
	private function register_carousel_style() {
		$this->start_controls_section(
			'section_style_carousel',
			array(
				'label'     => esc_html__( 'Carousel Nav', 'devgraphix-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'display' => 'carousel' ),
			)
		);

		$this->add_control(
			'arrow_icon_color',
			array(
				'label'     => esc_html__( 'Arrow Colour', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .dgx-pcards__arrow' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_bg_color',
			array(
				'label'     => esc_html__( 'Arrow Background', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .dgx-pcards__arrow' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_color',
			array(
				'label'     => esc_html__( 'Dot Colour', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .dgx-pcards__dot' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_active_color',
			array(
				'label'     => esc_html__( 'Active Dot Colour', 'devgraphix-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .dgx-pcards__dot.is-active' => 'background-color: {{VALUE}};' ),
			)
		);

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
	 * SVG left arrow icon (carousel prev).
	 *
	 * @return string
	 */
	private function arrow_left_svg() {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>';
	}

	/**
	 * SVG check icon.
	 *
	 * @return string
	 */
	private function check_svg() {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>';
	}

	/**
	 * Build the normalised card data for a single product.
	 *
	 * @param array $s       Settings.
	 * @param int    $post_id Post id.
	 * @return array<string,mixed>
	 */
	private function build_card_data( array $s, $post_id ) {
		$price = $this->resolve_price( $s, $post_id );

		return array(
			'rx'           => ( 'yes' === ( isset( $s['show_rx'] ) ? $s['show_rx'] : '' ) ) ? trim( (string) ( isset( $s['rx_text'] ) ? $s['rx_text'] : '' ) ) : '',
			'family'       => $this->get_meta_value( $post_id, isset( $s['family_field'] ) ? $s['family_field'] : '' ),
			'desc'         => $this->get_meta_value( $post_id, isset( $s['desc_field'] ) ? $s['desc_field'] : '' ),
			'name'         => (string) get_the_title( $post_id ),
			'sub'          => $this->get_meta_value( $post_id, isset( $s['sub_field'] ) ? $s['sub_field'] : '' ),
			'price'        => $price['amount'],
			'price_suffix' => $price['suffix'],
			'bullets'      => $this->resolve_bullets( isset( $s['bullets_field'] ) ? $s['bullets_field'] : '', $post_id ),
			'image'        => $this->resolve_image( $s, $post_id ),
			'card_link'    => $this->resolve_link( $s, 'card_link_source', 'card_link', $post_id ),
			'button_link'  => $this->resolve_link( $s, 'button_link_source', 'button_link', $post_id ),
		);
	}

	/**
	 * Demo cards (placeholder copy) used when the query is empty.
	 *
	 * @param array  $s      Settings.
	 * @param string $layout Layout.
	 * @return array<int,array<string,mixed>>
	 */
	private function demo_cards( array $s, $layout ) {
		$rx     = ( 'yes' === ( isset( $s['show_rx'] ) ? $s['show_rx'] : '' ) ) ? trim( (string) ( isset( $s['rx_text'] ) ? $s['rx_text'] : '' ) ) : '';
		$image  = ! empty( $s['fallback_image']['url'] ) ? $s['fallback_image']['url'] : '';
		$suffix = $this->price_suffix_fallback( $s );

		if ( 'horizontal' === $layout ) {
			return array(
				array(
					'name'         => 'Lorem Ipsum',
					'desc'         => 'Lorem ipsum dolor sit',
					'bullets'      => array( 'Lorem ipsum dolor sit amet', 'Consectetur adipiscing elit' ),
					'price'        => '$49',
					'price_suffix' => $suffix,
					'image'        => $image,
				),
				array(
					'name'         => 'Dolor Sit',
					'desc'         => 'Consectetur adipiscing elit',
					'bullets'      => array( 'Sed do eiusmod tempor', 'Incididunt ut labore et dolore' ),
					'price'        => '$59',
					'price_suffix' => $suffix,
					'image'        => $image,
				),
			);
		}

		return array(
			array(
				'rx'           => $rx,
				'family'       => 'Lorem ipsum',
				'name'         => 'Lorem Ipsum',
				'sub'          => 'Lorem ipsum dolor sit amet consectetur',
				'price'        => '$49',
				'price_suffix' => $suffix,
				'image'        => $image,
			),
			array(
				'rx'           => $rx,
				'family'       => 'Sit amet elit',
				'name'         => 'Dolor Sit',
				'sub'          => 'Consectetur adipiscing elit sed do',
				'price'        => '$59',
				'price_suffix' => $suffix,
				'image'        => $image,
			),
		);
	}

	/**
	 * Whether we're rendering inside the Elementor editor.
	 *
	 * @return bool
	 */
	private function is_editor() {
		return class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$s       = $this->get_settings_for_display();
		$layout  = ( isset( $s['layout'] ) && 'horizontal' === $s['layout'] ) ? 'horizontal' : 'vertical';
		$display = ( isset( $s['display'] ) && 'carousel' === $s['display'] ) ? 'carousel' : 'grid';

		$ids   = $this->get_query_ids( $s );
		$cards = array();

		if ( ! empty( $ids ) ) {
			foreach ( $ids as $pid ) {
				$cards[] = $this->build_card_data( $s, (int) $pid );
			}
		} elseif ( 'yes' === ( isset( $s['enable_demo'] ) ? $s['enable_demo'] : 'yes' ) ) {
			$cards = $this->demo_cards( $s, $layout );
		}

		if ( empty( $cards ) ) {
			if ( $this->is_editor() ) {
				echo '<div class="dgx-pcards dgx-pcards--' . esc_attr( $layout ) . ' dgx-pcards--' . esc_attr( $display ) . '"><p class="dgx-pcards__hint">' . esc_html__( 'No products found. Configure the Query, or enable “Show Demo When Empty”.', 'devgraphix-elementor-addons' ) . '</p></div>';
			}
			return;
		}

		$total = count( $cards );

		$classes = array( 'dgx-pcards', 'dgx-pcards--' . $layout, 'dgx-pcards--' . $display );
		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if ( 'carousel' === $display ) {
			$this->add_render_attribute( 'wrapper', 'data-autoplay', ( 'yes' === ( isset( $s['autoplay'] ) ? $s['autoplay'] : '' ) ) ? 'yes' : 'no' );
			$this->add_render_attribute( 'wrapper', 'data-interval', (string) max( 1000, (int) ( isset( $s['autoplay_interval'] ) ? $s['autoplay_interval'] : 5000 ) ) );
			$this->add_render_attribute( 'wrapper', 'data-loop', ( 'yes' === ( isset( $s['carousel_loop'] ) ? $s['carousel_loop'] : 'yes' ) ) ? 'yes' : 'no' );
		}

		$is_carousel = ( 'carousel' === $display );
		$show_arrows = 'yes' === ( isset( $s['show_arrows'] ) ? $s['show_arrows'] : 'yes' );
		$show_dots   = 'yes' === ( isset( $s['show_dots'] ) ? $s['show_dots'] : 'yes' );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $is_carousel ) : ?><div class="dgx-pcards__viewport"><?php endif; ?>
			<div class="dgx-pcards__track">
				<?php
				foreach ( $cards as $i => $card ) {
					if ( 'horizontal' === $layout ) {
						$this->render_horizontal_card( $card, $s, $i );
					} else {
						$this->render_vertical_card( $card, $s, $i, $total );
					}
				}
				?>
			</div>
			<?php if ( $is_carousel ) : ?></div><?php endif; ?>

			<?php if ( $is_carousel && ( $show_arrows || $show_dots ) ) : ?>
				<div class="dgx-pcards__nav">
					<?php if ( $show_arrows ) : ?>
						<button type="button" class="dgx-pcards__arrow dgx-pcards__arrow--prev" aria-label="<?php esc_attr_e( 'Previous', 'devgraphix-elementor-addons' ); ?>"><?php echo $this->arrow_left_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<?php endif; ?>
					<?php if ( $show_dots ) : ?>
						<span class="dgx-pcards__dots"></span>
					<?php endif; ?>
					<?php if ( $show_arrows ) : ?>
						<button type="button" class="dgx-pcards__arrow dgx-pcards__arrow--next" aria-label="<?php esc_attr_e( 'Next', 'devgraphix-elementor-addons' ); ?>"><?php echo $this->arrow_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the shared price block (FROM + price).
	 *
	 * @param array $card Card data.
	 * @param array $s    Settings.
	 * @return void
	 */
	private function render_price( array $card, array $s ) {
		$price      = isset( $card['price'] ) ? $card['price'] : '';
		$suffix     = isset( $card['price_suffix'] ) ? $card['price_suffix'] : '';
		$from_label = isset( $s['from_label'] ) ? $s['from_label'] : '';

		if ( '' === $price && '' === $from_label ) {
			return;
		}
		?>
		<div>
			<?php if ( '' !== $from_label && '' !== $price ) : ?>
				<div class="dgx-pcard__from"><?php echo esc_html( $from_label ); ?></div>
			<?php endif; ?>
			<?php if ( '' !== $price ) : ?>
				<div><span class="dgx-pcard__price"><?php echo esc_html( $price ); ?></span><?php if ( '' !== $suffix ) : ?><span class="dgx-pcard__price-suffix"><?php echo esc_html( $suffix ); ?></span><?php endif; ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the media block (image or placeholder).
	 *
	 * @param array  $card   Card data.
	 * @param array  $s      Settings.
	 * @param string $layout Layout.
	 * @return void
	 */
	private function render_media( array $card, array $s, $layout ) {
		$image     = isset( $card['image'] ) ? $card['image'] : '';
		$show_halo = ( 'vertical' === $layout ) && 'yes' === ( isset( $s['show_halo'] ) ? $s['show_halo'] : '' );
		$alt       = isset( $card['name'] ) ? $card['name'] : '';
		?>
		<div class="dgx-pcard__media">
			<?php if ( $show_halo ) : ?>
				<span class="dgx-pcard__halo" aria-hidden="true"></span>
			<?php endif; ?>
			<?php if ( '' !== $image ) : ?>
				<img class="dgx-pcard__img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" />
			<?php else : ?>
				<span class="dgx-pcard__img-ph"><?php esc_html_e( 'Image', 'devgraphix-elementor-addons' ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a vertical (glass) card.
	 *
	 * @param array $card  Card data.
	 * @param array $s     Settings.
	 * @param int    $index Zero-based index.
	 * @param int    $total Total cards.
	 * @return void
	 */
	private function render_vertical_card( array $card, array $s, $index, $total ) {
		$rx           = isset( $card['rx'] ) ? $card['rx'] : '';
		$family       = isset( $card['family'] ) ? $card['family'] : '';
		$name         = isset( $card['name'] ) ? $card['name'] : '';
		$sub          = isset( $card['sub'] ) ? $card['sub'] : '';
		$show_counter = 'yes' === ( isset( $s['show_counter'] ) ? $s['show_counter'] : '' );
		$link         = isset( $card['card_link'] ) ? $card['card_link'] : array();

		$link_key = '';
		if ( ! empty( $link['url'] ) ) {
			$link_key = 'cardlink_' . $index;
			$this->add_render_attribute( $link_key, 'class', 'dgx-pcard__link' );
			$this->add_link_attributes( $link_key, $link );
		}
		?>
		<div class="dgx-pcard">
			<div class="dgx-pcard__head">
				<?php if ( '' !== $rx ) : ?>
					<span class="dgx-pcard__rx"><?php echo esc_html( $rx ); ?></span>
				<?php else : ?>
					<span></span>
				<?php endif; ?>
				<?php if ( $show_counter ) : ?>
					<span class="dgx-pcard__count"><?php echo esc_html( sprintf( '%02d / %02d', $index + 1, $total ) ); ?></span>
				<?php endif; ?>
			</div>

			<?php $this->render_media( $card, $s, 'vertical' ); ?>

			<div class="dgx-pcard__foot">
				<?php if ( '' !== $family ) : ?>
					<div class="dgx-pcard__family"><?php echo esc_html( $family ); ?></div>
				<?php endif; ?>

				<?php if ( '' !== $name ) : ?>
					<h3 class="dgx-pcard__name"><?php echo esc_html( $name ); ?></h3>
				<?php endif; ?>

				<?php if ( '' !== $sub ) : ?>
					<p class="dgx-pcard__sub"><?php echo esc_html( $sub ); ?></p>
				<?php endif; ?>

				<div class="dgx-pcard__price-row">
					<?php $this->render_price( $card, $s ); ?>
					<span class="dgx-pcard__arrow"><?php echo $this->arrow_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>
			</div>

			<?php if ( '' !== $link_key ) : ?>
				<a <?php echo $this->get_render_attribute_string( $link_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $name ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a horizontal (light) card.
	 *
	 * @param array $card  Card data.
	 * @param array $s     Settings.
	 * @param int    $index Zero-based index.
	 * @return void
	 */
	private function render_horizontal_card( array $card, array $s, $index ) {
		$name        = isset( $card['name'] ) ? $card['name'] : '';
		$desc        = isset( $card['desc'] ) ? $card['desc'] : '';
		$bullets     = isset( $card['bullets'] ) && is_array( $card['bullets'] ) ? $card['bullets'] : array();
		$show_button = 'yes' === ( isset( $s['show_button'] ) ? $s['show_button'] : '' );
		$button_text = isset( $s['button_text'] ) ? $s['button_text'] : '';
		$link        = isset( $card['button_link'] ) ? $card['button_link'] : array();

		$btn_key = 'btn_' . $index;
		$this->add_render_attribute( $btn_key, 'class', 'dgx-pcard__btn' );
		$btn_tag = 'span';
		if ( ! empty( $link['url'] ) ) {
			$this->add_link_attributes( $btn_key, $link );
			$btn_tag = 'a';
		}
		?>
		<div class="dgx-pcard">
			<?php $this->render_media( $card, $s, 'horizontal' ); ?>

			<div class="dgx-pcard__body">
				<?php if ( '' !== $name ) : ?>
					<h3 class="dgx-pcard__name"><?php echo esc_html( $name ); ?></h3>
				<?php endif; ?>

				<?php if ( '' !== $desc ) : ?>
					<div class="dgx-pcard__desc"><?php echo esc_html( $desc ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $bullets ) ) : ?>
					<div class="dgx-pcard__bullets">
						<?php foreach ( $bullets as $bullet ) : ?>
							<div class="dgx-pcard__bullet">
								<span class="dgx-pcard__bullet-icon"><?php echo $this->check_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span><?php echo esc_html( $bullet ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="dgx-pcard__price-row">
					<?php $this->render_price( $card, $s ); ?>
					<?php if ( $show_button && '' !== $button_text ) : ?>
						<<?php echo esc_attr( $btn_tag ); ?> <?php echo $this->get_render_attribute_string( $btn_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo esc_html( $button_text ); ?>
							<span class="dgx-pcard__btn-arrow"><?php echo $this->arrow_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</<?php echo esc_attr( $btn_tag ); ?>>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
