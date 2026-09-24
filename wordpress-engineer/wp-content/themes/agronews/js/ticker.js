/**
 * File ticker.js.
 *
 * Highlights one quote of the market bar at a time. Enqueued only on the
 * front page and declared as dependent on navigation.js.
 */
( function() {
	const list = document.querySelector( '.an-quotes__list' );

	// Return early if the quotes bar is not rendered (plugin inactive).
	if ( ! list ) {
		return;
	}

	const items = list.querySelectorAll( '.an-quotes__item' );

	if ( items.length < 2 ) {
		return;
	}

	// Respect the reader's motion preference.
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	let current = 0;

	items[ current ].classList.add( 'is-current' );

	window.setInterval( function() {
		items[ current ].classList.remove( 'is-current' );
		current = ( current + 1 ) % items.length;
		items[ current ].classList.add( 'is-current' );
	}, 4000 );
}() );
