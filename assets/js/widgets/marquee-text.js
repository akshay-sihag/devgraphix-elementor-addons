/* =========================================================================
 * Devgraphix — Marquee Text
 * Builds a truly seamless, gap-free scrolling text strip. PHP renders one item
 * (text + separator); here we repeat that item until one "half" is at least as
 * wide as the container, then mirror it so the track is two identical halves.
 * CSS animates translateX(0 → -50%) forever, so half 2 lands exactly where
 * half 1 began — the loop never pauses, gaps, or visibly resets. The animation
 * duration scales with the number of copies so the pixel speed stays constant.
 * Self-inits on the frontend and in the Elementor editor (MutationObserver).
 * ========================================================================= */
( function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var MAX_COPIES = 80; // safety cap.

	function cloneHidden( node ) {
		var clone = node.cloneNode( true );
		clone.setAttribute( 'aria-hidden', 'true' );
		return clone;
	}

	function build( root ) {
		var track = root.querySelector( '.dgx-mtext__track' );
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

		var speed = parseFloat( root.getAttribute( 'data-speed' ) ) || 30;

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

		root.classList.add( 'dgx-mtext--ready' );
	}

	function debounce( fn, wait ) {
		var t;
		return function () {
			window.clearTimeout( t );
			t = window.setTimeout( fn, wait );
		};
	}

	function init( root ) {
		if ( root.dataset.dgxMtextInit === '1' ) {
			return;
		}
		root.dataset.dgxMtextInit = '1';
		build( root );
		window.addEventListener( 'resize', debounce( function () { build( root ); }, 200 ) );
	}

	function initAll() {
		var nodes = document.querySelectorAll( '.dgx-mtext:not([data-dgx-mtext-init])' );
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
