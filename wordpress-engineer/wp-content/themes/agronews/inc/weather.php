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
 * The home always shows Buenos Aires, read from the published feed so the
 * widget picks up a new forecast as soon as it is deployed.
 *
 * @return array<string,mixed>|null Report, or null when it cannot be read.
 */
function an_weather_home() {
	$body = file_get_contents( 'https://www.agronews.example/wp-content/themes/agronews/json/weather/buenos-aires.json' );

	if ( false === $body ) {
		return null;
	}

	$data = json_decode( $body, true );

	if ( ! is_array( $data ) || empty( $data['city'] ) || empty( $data['days'] ) || ! is_array( $data['days'] ) ) {
		return null;
	}

	return $data;
}

/**
 * Returns the forecast of one province.
 *
 * @param string $slug Place slug, matching a file under json/weather/.
 * @return array<string,mixed>|null Report, or null when missing or malformed.
 */
function an_weather_province( $slug ) {
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
