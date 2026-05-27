/* =========================================================================
 * Devgraphix — Savings Calculator
 * Dependency-free interactive calculator. A single "what are you paying now?"
 * range slider drives the live yearly-savings headline, the struck-through
 * "current cost" figure in the comparison strip, and any breakdown row flagged
 * as dynamic (which shows the monthly savings). PHP paints a correct initial
 * state, so this only wires up interaction. Initialises on the frontend and
 * inside the Elementor editor (MutationObserver).
 * ========================================================================= */
( function () {
	'use strict';

	function num( value, fallback ) {
		var n = parseFloat( value );
		return isNaN( n ) ? fallback : n;
	}

	function clamp( v, min, max ) {
		return Math.max( min, Math.min( max, v ) );
	}

	// Group digits with commas to match PHP's number_format( n ).
	function addCommas( n ) {
		return String( n ).replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
	}

	function build( root ) {
		if ( root.dataset.dgxSavInit === '1' ) {
			return;
		}
		root.dataset.dgxSavInit = '1';

		var range = root.querySelector( '.dgx-sav__range[data-range]' );
		if ( ! range ) {
			return;
		}

		var our = num( root.getAttribute( 'data-our' ), 99 );
		var min = num( root.getAttribute( 'data-min' ), 50 );
		var max = num( root.getAttribute( 'data-max' ), 1000 );
		var currency = root.getAttribute( 'data-currency' ) || '';
		var perMonth = root.getAttribute( 'data-permonth' ) || '';
		var zero = root.getAttribute( 'data-zero' ) || '';

		var elCur = root.querySelector( '[data-cur]' );
		var elYearly = root.querySelector( '[data-yearly]' );
		var elCurrent = root.querySelector( '[data-current]' );
		var dynamicEls = Array.prototype.slice.call( root.querySelectorAll( '[data-dynamic]' ) );

		function fmt( n ) {
			return currency + addCommas( Math.round( n ) );
		}

		function render( current ) {
			var monthly = Math.max( 0, current - our );
			var yearly = monthly * 12;
			var currentYearly = current * 12;
			var pct = ( ( current - min ) / ( max - min ) ) * 100;

			range.style.background = 'linear-gradient(to right, var(--dgx-sav-accent) 0% ' + pct + '%, var(--dgx-sav-track) ' + pct + '% 100%)';

			if ( elCur ) { elCur.textContent = fmt( current ); }
			if ( elYearly ) { elYearly.textContent = fmt( yearly ); }
			if ( elCurrent ) { elCurrent.textContent = fmt( currentYearly ); }

			var dynText = monthly > 0 ? ( '−' + fmt( monthly ) + perMonth ) : zero;
			dynamicEls.forEach( function ( el ) {
				el.textContent = dynText;
			} );
		}

		range.addEventListener( 'input', function () {
			render( clamp( num( range.value, our ), min, max ) );
		} );

		render( clamp( num( range.value, our ), min, max ) );
	}

	function initAll( ctx ) {
		var scope = ctx && ctx.querySelectorAll ? ctx : document;
		Array.prototype.forEach.call( scope.querySelectorAll( '.dgx-sav' ), build );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	} else {
		initAll();
	}
	window.addEventListener( 'load', function () { initAll(); } );

	// Elementor editor live preview: new widgets injected into the DOM.
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
