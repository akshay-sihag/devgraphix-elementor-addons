/* =========================================================================
 * Devgraphix — Testimonials carousel
 * Dependency-free scroll-snap carousel: prev/next arrows, generated dots,
 * optional autoplay + loop, pause on interaction. Initialises itself on the
 * frontend and inside the Elementor editor (MutationObserver).
 * ========================================================================= */
( function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function num( value, fallback ) {
		var n = parseInt( value, 10 );
		return isNaN( n ) ? fallback : n;
	}

	function build( root ) {
		if ( root.dataset.dgxTstInit === '1' ) {
			return;
		}
		root.dataset.dgxTstInit = '1';

		var viewport = root.querySelector( '.dgx-tst__viewport' );
		var track = root.querySelector( '.dgx-tst__track' );
		if ( ! viewport || ! track ) {
			return;
		}

		var cards = Array.prototype.slice.call( track.children );
		if ( ! cards.length ) {
			return;
		}

		var prevBtn = root.querySelector( '.dgx-tst__arrow--prev' );
		var nextBtn = root.querySelector( '.dgx-tst__arrow--next' );
		var dotsWrap = root.querySelector( '.dgx-tst__dots' );

		var autoplay = root.dataset.autoplay === 'yes';
		var interval = num( root.dataset.interval, 6500 );
		var loop = root.dataset.loop === 'yes';
		var pauseHover = root.dataset.pause === 'yes';

		function step() {
			var gap = parseFloat( getComputedStyle( track ).columnGap || getComputedStyle( track ).gap || 0 ) || 0;
			return cards[ 0 ].getBoundingClientRect().width + gap;
		}

		// ----- dots -----
		var dots = [];
		if ( dotsWrap ) {
			dotsWrap.innerHTML = '';
			cards.forEach( function ( card, i ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'dgx-tst__dot';
				dot.setAttribute( 'aria-label', 'Go to slide ' + ( i + 1 ) );
				dot.addEventListener( 'click', function () {
					viewport.scrollTo( { left: card.offsetLeft - track.offsetLeft, behavior: 'smooth' } );
				} );
				dotsWrap.appendChild( dot );
				dots.push( dot );
			} );
		}

		function activeIndex() {
			var pos = viewport.scrollLeft;
			var best = 0;
			var min = Infinity;
			cards.forEach( function ( card, i ) {
				var d = Math.abs( ( card.offsetLeft - track.offsetLeft ) - pos );
				if ( d < min ) {
					min = d;
					best = i;
				}
			} );
			return best;
		}

		function syncUi() {
			var idx = activeIndex();
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'is-active', i === idx );
			} );
			if ( prevBtn && ! loop ) {
				prevBtn.disabled = viewport.scrollLeft <= 2;
			}
			if ( nextBtn && ! loop ) {
				nextBtn.disabled = viewport.scrollLeft >= ( viewport.scrollWidth - viewport.clientWidth - 2 );
			}
		}

		function scrollByStep( dir ) {
			var atEnd = viewport.scrollLeft >= ( viewport.scrollWidth - viewport.clientWidth - 2 );
			var atStart = viewport.scrollLeft <= 2;
			if ( dir > 0 && atEnd && loop ) {
				viewport.scrollTo( { left: 0, behavior: 'smooth' } );
				return;
			}
			if ( dir < 0 && atStart && loop ) {
				viewport.scrollTo( { left: viewport.scrollWidth, behavior: 'smooth' } );
				return;
			}
			viewport.scrollBy( { left: dir * step(), behavior: 'smooth' } );
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () { scrollByStep( -1 ); } );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () { scrollByStep( 1 ); } );
		}

		var scrollRaf;
		viewport.addEventListener( 'scroll', function () {
			window.cancelAnimationFrame( scrollRaf );
			scrollRaf = window.requestAnimationFrame( syncUi );
		}, { passive: true } );

		// ----- autoplay -----
		var timer = null;
		function play() {
			if ( ! autoplay || REDUCED || timer ) {
				return;
			}
			timer = window.setInterval( function () { scrollByStep( 1 ); }, interval );
		}
		function stop() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}
		if ( autoplay && ! REDUCED ) {
			if ( pauseHover ) {
				root.addEventListener( 'mouseenter', stop );
				root.addEventListener( 'mouseleave', play );
			}
			root.addEventListener( 'focusin', stop );
			root.addEventListener( 'touchstart', stop, { passive: true } );
			play();
		}

		syncUi();
		window.addEventListener( 'resize', syncUi );
	}

	function initAll( ctx ) {
		var scope = ctx && ctx.querySelectorAll ? ctx : document;
		var nodes = scope.querySelectorAll( '.dgx-tst--carousel' );
		Array.prototype.forEach.call( nodes, build );
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
