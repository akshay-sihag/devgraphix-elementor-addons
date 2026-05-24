/**
 * Devgraphix Elementor Addons — frontend scripts.
 *
 * Widgets that need JS should declare 'dgx-ea-frontend' in
 * get_script_depends() and hook their behavior here. Using Elementor's
 * frontend hooks ensures it also works inside the editor preview.
 */
( function ( $ ) {
	'use strict';

	$( window ).on( 'elementor/frontend/init', function () {
		// Example: register a handler per widget when you need JS behavior.
		//
		// elementorFrontend.hooks.addAction(
		//     'frontend/element_ready/dgx-product-cards.default',
		//     function ( $scope ) {
		//         // $scope is the widget wrapper.
		//     }
		// );
	} );
} )( jQuery );
