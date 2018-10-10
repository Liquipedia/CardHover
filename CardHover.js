/* Card Hover */
( function( window, document, Math ) {
	function cardhover() {
		document.querySelectorAll( '.hovercard' ).forEach( function( card ) {
			var link = card.dataset.img;
			card.addEventListener( 'mouseover', function() {
				var position = card.getBoundingClientRect();
				var top = position.top + 50;
				var left = position.left + 20;
				if ( position.top + 50 > ( window.innerHeight / 2 ) ) {
					top = top - 486;
				}
				if ( ( window.innerWidth - ( left + 480 ) ) < 0 ) {
					left = left - 280;
				}
				var img = document.createElement( 'img' );
				img.src = link;
				var div = document.createElement( 'div' );
				div.classList.add( 'hoverimage' );
				div.appendChild( img );
				div.style.top = Math.round( top ).toString() + 'px';
				div.style.left = Math.round( left ).toString() + 'px';
				card.appendChild( div );
			} );
			card.addEventListener( 'mouseleave', function() {
				document.querySelectorAll( '.hoverimage' ).forEach( function( card ) {
					card.parentNode.removeChild( card );
				} );
			} );
			card.addEventListener( 'wheel', function() {
				document.querySelectorAll( '.hoverimage' ).forEach( function( card ) {
					card.parentNode.removeChild( card );
				} );
			} );
		} );
	}
	if ( document.readyState === 'loading' ) {
		window.addEventListener( 'DOMContentLoaded', cardhover );
	} else {
		cardhover();
	}
}( window, document, Math ) );
