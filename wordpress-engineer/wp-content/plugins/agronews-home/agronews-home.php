<?php
/**
 * Plugin Name:       AgroNews Home
 * Plugin URI:        https://www.agronews.example/plugins/agronews-home
 * Description:       Configures the AgroNews front page: the category behind each home block and the market quotes shown in the top bar.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Braintly
 * Author URI:        https://www.agronews.example
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       agronews-home
 * Domain Path:       /languages
 *
 * @package AgroNews_Home
 */

defined( 'ABSPATH' ) || exit;

define( 'AGRONEWS_HOME_VERSION', '1.0.0' );
define( 'AGRONEWS_HOME_FILE', __FILE__ );
define( 'AGRONEWS_HOME_DIR', plugin_dir_path( __FILE__ ) );

/** Option holding every setting of the plugin. */
define( 'AGRONEWS_HOME_OPTION', 'agronews_home_settings' );

/** Settings group used by register_setting() and settings_fields(). */
define( 'AGRONEWS_HOME_GROUP', 'agronews_home' );

/** Slug of the settings page under Settings. */
define( 'AGRONEWS_HOME_PAGE', 'agronews-home' );

require_once AGRONEWS_HOME_DIR . 'includes/quotes.php';
require_once AGRONEWS_HOME_DIR . 'includes/settings.php';

/**
 * Loads the plugin translations.
 *
 * @return void
 */
function agronews_home_load_textdomain() {
	load_plugin_textdomain( 'agronews-home', false, dirname( plugin_basename( AGRONEWS_HOME_FILE ) ) . '/languages' );
}
add_action( 'init', 'agronews_home_load_textdomain' );
