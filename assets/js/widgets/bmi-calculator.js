/* =========================================================================
 * Devgraphix — BMI Calculator
 * Dependency-free interactive calculator. Height/weight sliders + an
 * imperial/metric unit toggle drive a live BMI readout: the big number, the
 * category pill, the scale marker, the recommendation text and (via the root
 * `.dgx-bmi--cat-N` class) every band-tinted colour all update on input.
 *
 * Canonical state is always inches + pounds; metric is a display conversion,
 * so the slider ranges never change. PHP paints a correct initial state, so
 * this only needs to wire up interaction. Initialises on the frontend and
 * inside the Elementor editor (MutationObserver).
 * ========================================================================= */
( function () {
	'use strict';

	// WHO band thresholds (BMI). Four bands → indices 0-3.
	var THRESHOLDS = [ 18.5, 25, 30 ];

	function num( value, fallback ) {
		var n = parseFloat( value );
		return isNaN( n ) ? fallback : n;
	}

	function clamp( v, min, max ) {
		return Math.max( min, Math.min( max, v ) );
	}

	function bandIndex( bmi ) {
		if ( bmi < THRESHOLDS[ 0 ] ) { return 0; }
		if ( bmi < THRESHOLDS[ 1 ] ) { return 1; }
		if ( bmi < THRESHOLDS[ 2 ] ) { return 2; }
		return 3;
	}

	// Position (0-1) on the 4-band scale; each band spans 25% of the bar.
	function markerPos( bmi ) {
		var pos;
		if ( bmi < 18.5 ) {
			pos = Math.max( 0.02, ( bmi / 18.5 ) * 0.25 );
		} else if ( bmi < 25 ) {
			pos = 0.25 + ( ( bmi - 18.5 ) / 6.5 ) * 0.25;
		} else if ( bmi < 30 ) {
			pos = 0.50 + ( ( bmi - 25 ) / 5 ) * 0.25;
		} else {
			pos = 0.75 + Math.min( ( bmi - 30 ) / 15, 1 ) * 0.25;
		}
		return clamp( pos, 0.01, 0.99 );
	}

	function heightDisplay( inches, unit ) {
		if ( unit === 'metric' ) {
			return Math.round( inches * 2.54 ) + ' cm';
		}
		var ft = Math.floor( inches / 12 );
		var inch = inches % 12;
		return ft + "' " + inch + '"';
	}

	function weightDisplay( lb, unit ) {
		if ( unit === 'metric' ) {
			return Math.round( lb / 2.205 ) + ' kg';
		}
		return lb + ' lbs';
	}

	function fillBg( raw, min, max ) {
		var pct = ( ( raw - min ) / ( max - min ) ) * 100;
		return 'linear-gradient(to right, var(--dgx-bmi-fill) 0% ' + pct + '%, var(--dgx-bmi-track) ' + pct + '% 100%)';
	}

	function build( root ) {
		if ( root.dataset.dgxBmiInit === '1' ) {
			return;
		}
		root.dataset.dgxBmiInit = '1';

		var hRange = root.querySelector( '.dgx-bmi__range[data-range="height"]' );
		var wRange = root.querySelector( '.dgx-bmi__range[data-range="weight"]' );
		if ( ! hRange || ! wRange ) {
			return;
		}

		var hMin = num( root.getAttribute( 'data-h-min' ), 48 );
		var hMax = num( root.getAttribute( 'data-h-max' ), 84 );
		var wMin = num( root.getAttribute( 'data-w-min' ), 80 );
		var wMax = num( root.getAttribute( 'data-w-max' ), 400 );

		var cats = [];
		try {
			cats = JSON.parse( root.getAttribute( 'data-cats' ) || '[]' );
		} catch ( e ) {
			cats = [];
		}

		var state = {
			height: clamp( num( root.getAttribute( 'data-h-val' ), 70 ), hMin, hMax ),
			weight: clamp( num( root.getAttribute( 'data-w-val' ), 200 ), wMin, wMax ),
			unit: root.getAttribute( 'data-unit' ) === 'metric' ? 'metric' : 'imperial'
		};

		var els = {
			hValue: root.querySelector( '[data-display="height"]' ),
			wValue: root.querySelector( '[data-display="weight"]' ),
			hLo: root.querySelector( '[data-minmax="height-min"]' ),
			hHi: root.querySelector( '[data-minmax="height-max"]' ),
			wLo: root.querySelector( '[data-minmax="weight-min"]' ),
			wHi: root.querySelector( '[data-minmax="weight-max"]' ),
			number: root.querySelector( '[data-bmi]' ),
			cat: root.querySelector( '[data-cat-name]' ),
			rec: root.querySelector( '[data-rec]' ),
			marker: root.querySelector( '[data-marker]' ),
			units: Array.prototype.slice.call( root.querySelectorAll( '.dgx-bmi__unit' ) )
		};

		function renderUnits() {
			els.units.forEach( function ( btn ) {
				btn.classList.toggle( 'is-active', btn.getAttribute( 'data-unit' ) === state.unit );
			} );
			if ( els.hValue ) { els.hValue.textContent = heightDisplay( state.height, state.unit ); }
			if ( els.wValue ) { els.wValue.textContent = weightDisplay( state.weight, state.unit ); }
			if ( els.hLo ) { els.hLo.textContent = heightDisplay( hMin, state.unit ); }
			if ( els.hHi ) { els.hHi.textContent = heightDisplay( hMax, state.unit ); }
			if ( els.wLo ) { els.wLo.textContent = weightDisplay( wMin, state.unit ); }
			if ( els.wHi ) { els.wHi.textContent = weightDisplay( wMax, state.unit ); }
		}

		function render() {
			var bmi = ( state.weight * 703 ) / ( state.height * state.height );
			var band = bandIndex( bmi );

			if ( els.number ) {
				els.number.textContent = ( Math.round( bmi * 10 ) / 10 ).toFixed( 1 );
			}

			// Recolour everything via the root class → --dgx-bmi-active.
			root.className = root.className.replace( /\bdgx-bmi--cat-\d\b/, '' ).trim();
			root.classList.add( 'dgx-bmi--cat-' + band );

			if ( els.marker ) {
				els.marker.style.left = ( markerPos( bmi ) * 100 ) + '%';
			}

			var cat = cats[ band ] || {};
			if ( els.cat ) { els.cat.textContent = cat.n || ''; }
			if ( els.rec ) { els.rec.textContent = cat.r || ''; }

			hRange.style.background = fillBg( state.height, hMin, hMax );
			wRange.style.background = fillBg( state.weight, wMin, wMax );

			if ( els.hValue ) { els.hValue.textContent = heightDisplay( state.height, state.unit ); }
			if ( els.wValue ) { els.wValue.textContent = weightDisplay( state.weight, state.unit ); }
		}

		hRange.addEventListener( 'input', function () {
			state.height = clamp( num( hRange.value, state.height ), hMin, hMax );
			render();
		} );

		wRange.addEventListener( 'input', function () {
			state.weight = clamp( num( wRange.value, state.weight ), wMin, wMax );
			render();
		} );

		els.units.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var unit = btn.getAttribute( 'data-unit' ) === 'metric' ? 'metric' : 'imperial';
				if ( unit === state.unit ) {
					return;
				}
				state.unit = unit;
				renderUnits();
			} );
		} );

		render();
	}

	function initAll( ctx ) {
		var scope = ctx && ctx.querySelectorAll ? ctx : document;
		Array.prototype.forEach.call( scope.querySelectorAll( '.dgx-bmi' ), build );
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
