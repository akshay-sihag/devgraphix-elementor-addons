/* =========================================================================
 * Devgraphix — Marquee Pills
 * Builds a truly seamless, gap-free ticker. PHP renders one set of pills;
 * here we repeat that set until one "half" is at least as wide as the
 * container, then mirror it so the track is two identical halves. CSS animates
 * translateX(0 → -50%) forever, so half 2 lands exactly where half 1 began —
 * the loop never pauses, gaps, or visibly resets, even with only a few pills.
 * The animation duration scales with the number of copies so the pixel speed
 * stays constant. Self-inits on the frontend and in the Elementor editor.
 * ========================================================================= */
( function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var MAX_COPIES = 80; // safety cap.

	function cloneHidden( node ) {
		var clone = node.cloneNode( true );
		clone.setAttribute( 'aria-hidden', 'true' );
		if ( 'A' === clone.tagName ) {
			clone.setAttribute( 'tabindex', '-1' );
		}
		if ( clone.querySelectorAll ) {
			Array.prototype.forEach.call( clone.querySelectorAll( 'a' ), function ( a ) {
				a.setAttribute( 'tabindex', '-1' );
			} );
		}
		return clone;
	}

	function build( root ) {
		var track = root.querySelector( '.dgx-pills__track' );
		if ( ! track ) {
			return;
		}

		// Remember the authored set once, so rebuilds (resize) start clean.
		if ( ! root._dgxOriginal ) {
			root._dgxOriginal = Array.prototype.map.call( track.children, function ( n ) {
				return n.cloneNode( true );
			} );
		}

		var originals = root._dgxOriginal;
		if ( ! originals.length ) {
			return;
		}

		var speed = parseFloat( root.getAttribute( 'data-speed' ) ) || 40;

		// Reset to the authored set.
		track.style.animationDuration = '';
		while ( track.firstChild ) {
			track.removeChild( track.firstChild );
		}
		originals.forEach( function ( n ) {
			track.appendChild( n.cloneNode( true ) );
		} );

		var containerWidth = root.clientWidth || ( track.parentElement ? track.parentElement.clientWidth : 0 );

		// Repeat the set until one half fills (and overflows) the container.
		var halfCopies = 1;
		while ( track.scrollWidth < containerWidth && halfCopies < MAX_COPIES ) {
			originals.forEach( function ( n ) {
				track.appendChild( cloneHidden( n ) );
			} );
			halfCopies++;
		}

		// Mirror the whole half → two identical halves for the -50% loop.
		var half = Array.prototype.slice.call( track.children );
		half.forEach( function ( n ) {
			track.appendChild( cloneHidden( n ) );
		} );

		// Constant pixel speed regardless of how many copies it took to fill.
		if ( ! REDUCED ) {
			track.style.animationDuration = ( halfCopies * speed ) + 's';
		}

		root.classList.add( 'dgx-pills--ready' );
	}

	function debounce( fn, wait ) {
		var t;
		return function () {
			window.clearTimeout( t );
			t = window.setTimeout( fn, wait );
		};
	}

	function init( root ) {
		if ( root.dataset.dgxMarqueeInit === '1' ) {
			return;
		}
		root.dataset.dgxMarqueeInit = '1';
		build( root );
		window.addEventListener( 'resize', debounce( function () { build( root ); }, 200 ) );
	}

	function initAll() {
		var nodes = document.querySelectorAll( '.dgx-pills:not([data-dgx-marquee-init])' );
		Array.prototype.forEach.call( nodes, init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
	window.addEventListener( 'load', initAll );

	// Elementor editor: widgets are injected / re-rendered on edit.
	if ( window.MutationObserver ) {
		var observer = new MutationObserver( function ( mutations ) {
			for ( var i = 0; i < mutations.length; i++ ) {
				if ( mutations[ i ].addedNodes.length ) {
					initAll();
					break;
				}
			}
		} );
		observer.observe( document.body, { childList: true, subtree: true } );
	}
}() );
