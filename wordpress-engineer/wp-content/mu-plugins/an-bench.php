<?php
/**
 * Plugin Name: AgroNews Bench Helper
 * Description: Exposes the query count and the peak memory of a front end request as response headers, so bin/bench.sh can measure the site without parsing the HTML.
 * Version:     1.0.0
 * Author:      Braintly
 * License:     GPL-2.0-or-later
 *
 * Only active when AN_BENCH is on, which is a local-only switch: the headers
 * would leak internals on a public site.
 *
 * SAVEQUERIES has to be defined before wpdb runs its first query, so it is set
 * from WORDPRESS_CONFIG_EXTRA in docker-compose.yml. This file only defines it
 * as a fallback for setups that load the mu-plugin some other way.
 *
 * @package AgroNews_Bench
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the bench helper is enabled for this request.
 *
 * @return bool True when the headers should be emitted.
 */
function agronews_bench_is_enabled() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	if ( defined( 'AN_BENCH' ) ) {
		return (bool) AN_BENCH;
	}

	return '1' === getenv( 'AN_BENCH' );
}

if ( ! agronews_bench_is_enabled() ) {
	return;
}

if ( ! defined( 'SAVEQUERIES' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- SAVEQUERIES is a WordPress core constant, not one of ours.
	define( 'SAVEQUERIES', true );
}

/*
 * Headers cannot be sent once the body starts flushing, and the numbers are
 * only final at shutdown. Buffering the whole response is what makes it
 * possible to report them as headers.
 */
if ( ! is_admin() && ! wp_doing_ajax() && ! headers_sent() ) {
	ob_start();
}

/**
 * Sends the measurements as X-AN-* headers.
 *
 * Runs at shutdown priority 0, right before core flushes the output buffers
 * at priority 1.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function agronews_bench_send_headers() {
	global $wpdb;

	if ( headers_sent() ) {
		return;
	}

	$queries    = isset( $wpdb->num_queries ) ? (int) $wpdb->num_queries : 0;
	$query_time = 0.0;

	if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES && is_array( $wpdb->queries ) ) {
		foreach ( $wpdb->queries as $query ) {
			$query_time += isset( $query[1] ) ? (float) $query[1] : 0.0;
		}
	}

	header( 'X-AN-Queries: ' . $queries );
	header( 'X-AN-Query-Time: ' . round( $query_time * 1000, 2 ) );
	header( 'X-AN-Memory: ' . round( memory_get_peak_usage( true ) / 1048576, 2 ) );
	header( 'X-AN-Savequeries: ' . ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ? '1' : '0' ) );
	header( 'X-AN-Template: ' . agronews_bench_template_name() );
}
add_action( 'shutdown', 'agronews_bench_send_headers', 0 );

/**
 * Returns the template file that rendered the request.
 *
 * @return string Template file name, or "unknown".
 */
function agronews_bench_template_name() {
	$template = get_query_var( 'agronews_bench_template' );

	return is_string( $template ) && '' !== $template ? $template : 'unknown';
}

/**
 * Records which template WordPress picked for this request.
 *
 * @param string $template Absolute path of the template about to be included.
 * @return string Unmodified template path.
 */
function agronews_bench_record_template( $template ) {
	set_query_var( 'agronews_bench_template', basename( (string) $template ) );

	return $template;
}
add_filter( 'template_include', 'agronews_bench_record_template', PHP_INT_MAX );
