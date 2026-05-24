<?php
/**
 * Registers the custom widget category and all addon widgets.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widgets_Manager
 */
class Widgets_Manager {

	/**
	 * Slug for the custom Elementor category.
	 */
	const CATEGORY_SLUG = 'devgraphix';

	/**
	 * List of widget classes to register.
	 *
	 * Add new widgets here as you build them. Each must extend
	 * \Elementor\Widget_Base (we provide Base_Widget as a convenience).
	 *
	 * @var string[]
	 */
	private $widget_classes = array(
		Widgets\Hover_Cards::class,
		Widgets\Featured_Box::class,
		Widgets\Marquee_Pills::class,
		Widgets\Product_Cards::class,
		Widgets\Section_Heading::class,
		Widgets\Image_Showcase::class,
		Widgets\Comparison_Table::class,
		Widgets\Testimonials::class,
	);

	/**
	 * Constructor. Hooks into Elementor's registration lifecycle.
	 */
	public function __construct() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register the "Devgraphix" category in the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			self::CATEGORY_SLUG,
			array(
				'title' => esc_html__( 'Devgraphix', 'devgraphix-elementor-addons' ),
				'icon'  => 'eicon-apps',
			)
		);
	}

	/**
	 * Instantiate and register every widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		foreach ( $this->widget_classes as $widget_class ) {
			if ( class_exists( $widget_class ) ) {
				$widgets_manager->register( new $widget_class() );
			}
		}
	}
}
