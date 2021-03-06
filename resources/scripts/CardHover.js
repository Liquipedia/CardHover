/* Card Hover */
( function( window, document, Math, console, mw ) {
	var cardhover = {
		init: function() {
			if ( document.readyState === 'loading' ) {
				window.addEventListener( 'DOMContentLoaded', cardhover.run );
			} else {
				cardhover.run();
			}
		},
		run: function() {
			document.querySelectorAll( '.hovercard' ).forEach( function( card ) {
				if ( card.querySelector( '.no-hovercard' ) === null ) {
					var link = card.dataset.img;
					if ( link.indexOf( mw.config.get( 'wgServer' ) + '/' ) === 0 ) {
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
							img.style.width = 'initial';
							img.style.maxWidth = 'initial';
							img.style.minWidth = 'initial';
							img.style.height = 'initial';
							img.style.maxHeight = 'initial';
							img.style.minHeight = 'initial';
							img.src = link;
							var div = document.createElement( 'div' );
							div.classList.add( 'hoverimage' );
							div.appendChild( img );
							div.style.top = Math.round( top ).toString() + 'px';
							div.style.left = Math.round( left ).toString() + 'px';
							card.appendChild( div );
						} );
						card.addEventListener( 'mouseleave', function() {
							document.querySelectorAll( '.hoverimage' ).forEach( function( cardElement ) {
								cardElement.parentNode.removeChild( cardElement );
							} );
						} );
						card.addEventListener( 'wheel', function() {
							document.querySelectorAll( '.hoverimage' ).forEach( function( cardElement ) {
								cardElement.parentNode.removeChild( cardElement );
							} );
						} );
					} else {
						console.error( 'Loading card hover images from remote server is not allowed!' );
					}
				}
			} );
		}
	};
	cardhover.init();
}( window, document, Math, console, mw ) );
