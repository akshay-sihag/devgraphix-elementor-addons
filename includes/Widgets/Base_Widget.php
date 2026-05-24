<?php
/**
 * Base widget class.
 *
 * Extend this for every Devgraphix widget so they share the custom
 * category and common defaults. Override the abstract-ish methods as needed.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons\Widgets;

use Devgraphix\ElementorAddons\Widgets_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Base_Widget
 */
abstract class Base_Widget extends Widget_Base {

	/**
	 * Place all Devgraphix widgets in the custom category by default.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( Widgets_Manager::CATEGORY_SLUG );
	}

	/**
	 * Default keywords. Override and merge to add widget-specific terms.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'devgraphix', 'dgx' );
	}
}
