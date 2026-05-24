/* =========================================================================
 * Devgraphix — Comparison Table
 * Keeps the header and each feature row the same height across all columns so
 * they line up, regardless of differing content (chips wrapping, icon vs text).
 * When the columns stack on small screens, height-matching is removed.
 * Self-inits on the frontend and inside the Elementor editor.
 * ========================================================================= */
( function () {
	'use strict';

	function equalize( root ) {
		var cols = Array.prototype.slice.call( root.querySelectorAll( '.dgx-cmp__col' ) );
		if ( cols.length < 2 ) {
			return;
		}

		// Each column's measurable cells: header first, then rows in order.
		var grids = cols.map( function ( col ) {
			var cells = [];
			var head = col.querySelector( '.dgx-cmp__head' );
			if ( head ) {
				cells.push( head );
			}
			Array.prototype.forEach.call( col.querySelectorAll( '.dgx-cmp__row' ), function ( r ) {
				cells.push( r );
			} );
			return cells;
		} );

		// Reset before measuring.
		grids.forEach( function ( cells ) {
			cells.forEach( function ( el ) { el.style.minHeight = ''; } );
		} );

		// If the columns are stacked (not on the same row), leave them natural.
		if ( cols.length > 1 && cols[0].offsetTop !== cols[1].offsetTop ) {
			return;
		}

		var maxLen = grids.reduce( function ( m, c ) { return Math.max( m, c.length ); }, 0 );
		for ( var i = 0; i < maxLen; i++ ) {
			var max = 0;
			grids.forEach( function ( cells ) {
				if ( cells[ i ] ) {
					max = Math.max( max, cells[ i ].offsetHeight );
				}
			} );
			if ( max > 0 ) {
				grids.forEach( function ( cells ) {
					if ( cells[ i ] ) {
						cells[ i ].style.minHeight = max + 'px';
					}
				} );
			}
		}
	}

	function debounce( fn, wait ) {
		var t;
		return function () {
			window.clearTimeout( t );
			t = window.setTimeout( fn, wait );
		};
	}

	function init( root ) {
		if ( root.dataset.dgxCmpInit === '1' ) {
			return;
		}
		root.dataset.dgxCmpInit = '1';
		equalize( root );
		var run = debounce( function () { equalize( root ); }, 150 );
		window.addEventListener( 'resize', run );
		window.addEventListener( 'load', run );
		// Re-measure once web fonts settle (they change text heights).
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( function () { equalize( root ); } );
		}
	}

	function initAll() {
		Array.prototype.forEach.call( document.querySelectorAll( '.dgx-cmp:not([data-dgx-cmp-init])' ), init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
	window.addEventListener( 'load', initAll );

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
