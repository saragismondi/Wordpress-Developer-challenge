<?php
/**
 * PHPUnit bootstrap for the AgroNews Home test suite.
 *
 * Loads the WordPress test library, then the plugin, then points the theme
 * resolution at wp-content/themes so the home blocks can be rendered.
 *
 * @package AgroNews_Home
 */

$agronews_repo_root  = dirname( __DIR__, 4 );
$agronews_tests_dir  = getenv( 'WP_TESTS_DIR' );
$agronews_theme_root = $agronews_repo_root . '/wp-content/themes';

if ( ! $agronews_tests_dir ) {
	$agronews_tests_dir = '/tmp/wp-tests/wordpress-tests-lib';
}

if ( ! file_exists( $agronews_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find the WordPress test suite in {$agronews_tests_dir}." . PHP_EOL;
	echo 'Run bin/test.sh, which installs it before running PHPUnit.' . PHP_EOL;

	exit( 1 );
}

// Yoast polyfills, required by the WordPress test suite on PHPUnit 9.
if ( ! getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- The WordPress test suite reads this path from the environment.
	putenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH=' . $agronews_repo_root . '/vendor/yoast/phpunit-polyfills' );
}

require_once $agronews_tests_dir . '/includes/functions.php';

/**
 * Loads the plugin under test.
 *
 * @return void
 */
function agronews_home_manually_load_plugin() {
	require dirname( __DIR__ ) . '/agronews-home.php';
}
tests_add_filter( 'muplugins_loaded', 'agronews_home_manually_load_plugin' );

/**
 * Registers the repository theme directory.
 *
 * The setup_theme hook runs before WordPress resolves TEMPLATEPATH, which is
 * the last moment a theme directory can be added.
 *
 * @return void
 */
function agronews_home_register_theme_directory() {
	register_theme_directory( $GLOBALS['agronews_theme_root'] );
}
$GLOBALS['agronews_theme_root'] = $agronews_theme_root;
tests_add_filter( 'setup_theme', 'agronews_home_register_theme_directory' );

/**
 * Forces the AgroNews theme, whatever the test install has stored.
 *
 * @return string Theme slug.
 */
function agronews_home_force_theme() {
	return 'agronews';
}
tests_add_filter( 'pre_option_template', 'agronews_home_force_theme' );
tests_add_filter( 'pre_option_stylesheet', 'agronews_home_force_theme' );

require $agronews_tests_dir . '/includes/bootstrap.php';
