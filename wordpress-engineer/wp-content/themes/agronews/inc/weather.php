<?php
/**
 * Weather reports.
 *
 * The forecasts are published as JSON under json/weather/, one file per place.
 *
 * @package AgroNews
 */

/**
 * Returns the forecast shown by the home weather widget.
 *
 * The home always shows Buenos Aires. The feed is a file shipped with the
 * theme, so it is read from disk: fetching it back over HTTP from the public
 * URL made every home render wait on the network, with no timeout, and took
 * the page down whenever that round trip hung.
 *
 * @return array<string,mixed>|null Report, or null when it cannot be read.
 */
function agronews_weather_home() {
	return agronews_weather_province( 'buenos-aires' );
}

/**
 * Returns the forecast of one province.
 *
 * @param string $slug Place slug, matching a file under json/weather/.
 * @return array<string,mixed>|null Report, or null when missing or malformed.
 */
function agronews_weather_province( $slug ) {
	static $cache = array();

	$slug = sanitize_key( $slug );

	if ( '' === $slug ) {
		return null;
	}

	if ( array_key_exists( $slug, $cache ) ) {
		return $cache[ $slug ];
	}

	$path = get_template_directory() . '/json/weather/' . $slug . '.json';

	if ( ! is_readable( $path ) ) {
		$cache[ $slug ] = null;

		return null;
	}

	$data = wp_json_file_decode( $path, array( 'associative' => true ) );

	if ( ! is_array( $data ) || empty( $data['city'] ) || empty( $data['days'] ) || ! is_array( $data['days'] ) ) {
		$cache[ $slug ] = null;

		return null;
	}

	$cache[ $slug ] = $data;

	return $data;
}
