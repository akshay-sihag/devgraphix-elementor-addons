<?php
/**
 * Plugin Name: Devgraphix Elementor Addons
 * Description: Custom-built Elementor widgets and elements by Devgraphix.
 * Version:     1.12.0
 * Author:      Devgraphix
 * Author URI:  https://devgraphix.com
 * Text Domain: devgraphix-elementor-addons
 * Domain Path: /languages
 * Requires PHP: 7.4
 *
 * Elementor tested up to: 3.25.0
 * Elementor Pro tested up to: 3.25.0
 *
 * @package Devgraphix\ElementorAddons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------
define( 'DGX_EA_VERSION', '1.12.0' );
define( 'DGX_EA_FILE', __FILE__ );
define( 'DGX_EA_PATH', plugin_dir_path( __FILE__ ) );
define( 'DGX_EA_URL', plugin_dir_url( __FILE__ ) );

// Minimum requirements.
define( 'DGX_EA_MIN_ELEMENTOR_VERSION', '3.5.0' );
define( 'DGX_EA_MIN_PHP_VERSION', '7.4' );

// ---------------------------------------------------------------------------
// Autoloader (PSR-4: Devgraphix\ElementorAddons\ => includes/)
// ---------------------------------------------------------------------------
require_once DGX_EA_PATH . 'includes/autoload.php';

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
/**
 * Initialize the plugin once all plugins are loaded.
 *
 * The main class runs its own requirement checks and prints admin notices
 * if Elementor / PHP versions are not satisfied.
 */
function dgx_ea() {
	return \Devgraphix\ElementorAddons\Plugin::instance();
}

// Kick things off after all plugins (so we can detect Elementor).
add_action( 'plugins_loaded', 'dgx_ea' );
