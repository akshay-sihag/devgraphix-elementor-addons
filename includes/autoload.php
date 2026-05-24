<?php
/**
 * Simple PSR-4 autoloader for the Devgraphix\ElementorAddons namespace.
 *
 * Maps:  Devgraphix\ElementorAddons\Widgets\Product_Cards
 *   to:  includes/Widgets/Product_Cards.php
 *
 * @package Devgraphix\ElementorAddons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	function ( $class ) {
		$prefix   = 'Devgraphix\\ElementorAddons\\';
		$base_dir = DGX_EA_PATH . 'includes/';

		// Bail if the class is outside our namespace.
		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class, $len ) ) {
			return;
		}

		// Strip the namespace prefix, then turn the rest into a path.
		$relative = substr( $class, $len );
		$relative = str_replace( '\\', '/', $relative );

		$file = $base_dir . $relative . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);
