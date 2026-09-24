<?php
/**
 * AgroNews functions and definitions.
 *
 * Everything lives in inc/: this file only defines the theme constants and
 * loads the pieces, so each concern stays in its own file.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package AgroNews
 */

if ( ! defined( 'AGRONEWS_VERSION' ) ) {
	// Bump on every release: it is the cache-busting version for all assets.
	define( 'AGRONEWS_VERSION', '1.0.0' );
}

if ( ! defined( 'AGRONEWS_DIR' ) ) {
	define( 'AGRONEWS_DIR', get_template_directory() );
}

if ( ! defined( 'AGRONEWS_URI' ) ) {
	define( 'AGRONEWS_URI', get_template_directory_uri() );
}

/**
 * Theme supports, menus, image sizes and sidebars.
 */
require AGRONEWS_DIR . '/inc/setup.php';

/**
 * Styles and scripts.
 */
require AGRONEWS_DIR . '/inc/enqueue.php';

/**
 * Presentational helpers used by the templates.
 */
require AGRONEWS_DIR . '/inc/template-tags.php';

/**
 * Queries and data providers for the home page blocks.
 */
require AGRONEWS_DIR . '/inc/blocks.php';

/**
 * Weather reports.
 */
require AGRONEWS_DIR . '/inc/weather.php';

/**
 * Small tweaks that hook into WordPress.
 */
require AGRONEWS_DIR . '/inc/template-functions.php';
