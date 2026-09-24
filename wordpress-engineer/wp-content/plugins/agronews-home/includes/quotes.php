<?php
/**
 * Market quotes: definitions and public read API.
 *
 * @package AgroNews_Home
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the quotes the home bar can display.
 *
 * The list is fixed: adding a quote is a code change, not a setting, so the
 * front end never renders a label it does not know how to format.
 *
 * @return array<string,array<string,string>> Quote key => label and currency.
 */
function agronews_home_get_quote_definitions() {
	return array(
		'soy'      => array(
			'label'    => __( 'Soybean', 'agronews-home' ),
			'currency' => __( 'USD/t', 'agronews-home' ),
		),
		'wheat'    => array(
			'label'    => __( 'Wheat', 'agronews-home' ),
			'currency' => __( 'USD/t', 'agronews-home' ),
		),
		'corn'     => array(
			'label'    => __( 'Corn', 'agronews-home' ),
			'currency' => __( 'USD/t', 'agronews-home' ),
		),
		'cattle'   => array(
			'label'    => __( 'Cattle', 'agronews-home' ),
			'currency' => __( 'ARS/kg', 'agronews-home' ),
		),
		'usd'      => array(
			'label'    => __( 'USD official', 'agronews-home' ),
			'currency' => __( 'ARS', 'agronews-home' ),
		),
		'usd_mep'  => array(
			'label'    => __( 'USD MEP', 'agronews-home' ),
			'currency' => __( 'ARS', 'agronews-home' ),
		),
		'usd_blue' => array(
			'label'    => __( 'USD blue', 'agronews-home' ),
			'currency' => __( 'ARS', 'agronews-home' ),
		),
	);
}

/**
 * Returns the quote keys, in display order.
 *
 * @return string[] Quote keys.
 */
function agronews_home_get_quote_keys() {
	return array_keys( agronews_home_get_quote_definitions() );
}

/**
 * Returns the quotes ready to be rendered by the theme.
 *
 * Quotes left empty in the settings are skipped, so the bar never shows a
 * blank slot.
 *
 * @return array<int,array<string,mixed>> List of key, label, currency and value.
 */
function agronews_home_get_quotes() {
	$settings = agronews_home_get_settings();
	$quotes   = array();

	foreach ( agronews_home_get_quote_definitions() as $key => $definition ) {
		$value = isset( $settings['quotes'][ $key ] ) ? $settings['quotes'][ $key ] : '';

		if ( '' === $value ) {
			continue;
		}

		$quotes[] = array(
			'key'      => $key,
			'label'    => $definition['label'],
			'currency' => $definition['currency'],
			'value'    => $value,
		);
	}

	return $quotes;
}
