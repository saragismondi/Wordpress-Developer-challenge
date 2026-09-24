<?php
/**
 * Front end assets.
 *
 * Every handle declares its dependencies and a version, so the browser cache
 * is invalidated on release and the load order never depends on registration
 * order.
 *
 * @package AgroNews
 */

/**
 * Enqueues the front end styles and scripts.
 *
 * @return void
 */
function agronews_enqueue_assets() {
	// Base typography and resets shipped by the theme stylesheet.
	wp_enqueue_style(
		'agronews-base',
		get_stylesheet_uri(),
		array(),
		AGRONEWS_VERSION
	);

	// Portal layout: grids, cards and the quotes bar. Depends on the base.
	wp_enqueue_style(
		'agronews-portal',
		AGRONEWS_URI . '/css/portal.css',
		array( 'agronews-base' ),
		AGRONEWS_VERSION
	);

	wp_enqueue_script(
		'agronews-navigation',
		AGRONEWS_URI . '/js/navigation.js',
		array(),
		AGRONEWS_VERSION,
		true
	);

	// The quotes ticker reuses the helpers defined by the navigation script.
	if ( is_front_page() ) {
		wp_enqueue_script(
			'agronews-ticker',
			AGRONEWS_URI . '/js/ticker.js',
			array( 'agronews-navigation' ),
			AGRONEWS_VERSION,
			true
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'agronews_enqueue_assets' );
