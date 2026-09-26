<?php
/**
 * Settings: storage roundtrip and sanitization.
 *
 * @package AgroNews_Home
 */

/**
 * Covers includes/settings.php and includes/quotes.php.
 */
class Test_AgroNews_Home_Settings extends WP_UnitTestCase {

	/**
	 * Resets the option between tests.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		delete_option( AGRONEWS_HOME_OPTION );
		global $wp_settings_errors;
		$wp_settings_errors = array();
	}

	/**
	 * Saving through the sanitize callback and reading back returns the same
	 * categories and quotes.
	 *
	 * @return void
	 */
	public function test_settings_survive_a_save_and_read_roundtrip() {
		$featured = self::factory()->category->create( array( 'name' => 'Granos' ) );
		$sections = self::factory()->category->create_many( 3 );

		$sanitized = agronews_home_sanitize_settings(
			array(
				'featured_category'  => (string) $featured,
				'section_categories' => array_map( 'strval', $sections ),
				'quotes'             => array(
					'soy'      => '512.35',
					'wheat'    => '238',
					'corn'     => '195.5',
					'cattle'   => '2450',
					'usd'      => '1040.25',
					'usd_mep'  => '1187.9',
					'usd_blue' => '1215',
				),
			)
		);

		update_option( AGRONEWS_HOME_OPTION, $sanitized );

		$stored = agronews_home_get_settings();

		$this->assertSame( $featured, $stored['featured_category'] );
		$this->assertSame( $sections, $stored['section_categories'] );
		$this->assertSame( '512.35', $stored['quotes']['soy'] );
		$this->assertSame( '238', $stored['quotes']['wheat'] );
		$this->assertSame( '1215', $stored['quotes']['usd_blue'] );
		$this->assertSame( $featured, agronews_home_get_setting( 'featured_category' ) );
	}

	/**
	 * Quotes are cleaned before they are written: surrounding whitespace goes
	 * away and no markup survives.
	 *
	 * @return void
	 */
	public function test_quotes_are_cleaned_before_they_are_stored() {
		$sanitized = agronews_home_sanitize_settings(
			array(
				'quotes' => array(
					'soy'   => '  512.35  ',
					'wheat' => '<b>240.50</b>',
					'corn'  => '',
				),
			)
		);

		$this->assertSame( '512.35', $sanitized['quotes']['soy'], 'Surrounding whitespace is dropped.' );
		$this->assertSame( '240.50', $sanitized['quotes']['wheat'], 'Markup is stripped.' );
		$this->assertSame( '', $sanitized['quotes']['corn'], 'An empty quote stays empty.' );

		$this->assertCount( 7, $sanitized['quotes'], 'Every quote key is always present.' );
		$this->assertArrayHasKey( 'usd_blue', $sanitized['quotes'] );
	}

	/**
	 * Categories that do not exist are stored as 0, and the section list
	 * always keeps exactly three slots.
	 *
	 * @return void
	 */
	public function test_unknown_categories_are_reset_to_zero() {
		$real = self::factory()->category->create();

		$sanitized = agronews_home_sanitize_settings(
			array(
				'featured_category'  => 999999,
				'section_categories' => array( $real, 'not-a-number', 999999, 42 ),
				'quotes'             => array(),
			)
		);

		$this->assertSame( 0, $sanitized['featured_category'] );
		$this->assertCount( 3, $sanitized['section_categories'] );
		$this->assertSame( array( $real, 0, 0 ), $sanitized['section_categories'] );
	}

	/**
	 * Quotes typed the Argentine way are normalized before they are stored,
	 * and anything that is not a number keeps the previous value.
	 *
	 * @return void
	 */
	public function test_localized_quotes_are_normalized_and_invalid_ones_rejected() {
		update_option(
			AGRONEWS_HOME_OPTION,
			agronews_home_sanitize_settings( array( 'quotes' => array( 'usd' => '1040.25' ) ) )
		);

		$sanitized = agronews_home_sanitize_settings(
			array(
				'quotes' => array(
					'soy'    => '1.234,50',
					'wheat'  => '238,1',
					'corn'   => '1.234',
					'cattle' => '2450',
					'usd'    => 'N/A',
				),
			)
		);

		$this->assertSame( '1234.50', $sanitized['quotes']['soy'], 'Dots group thousands, the comma is the decimal separator.' );
		$this->assertSame( '238.1', $sanitized['quotes']['wheat'] );
		$this->assertSame( '1234', $sanitized['quotes']['corn'] );
		$this->assertSame( '2450', $sanitized['quotes']['cattle'] );
		$this->assertSame( '1040.25', $sanitized['quotes']['usd'], 'An invalid quote keeps the previous value.' );
	}

	/**
	 * The quotes bar never breaks on, or shows, a value stored before
	 * validation existed: non-numeric and negative values are skipped.
	 *
	 * @return void
	 */
	public function test_invalid_stored_quotes_are_skipped_by_the_read_api() {
		update_option(
			AGRONEWS_HOME_OPTION,
			array(
				'quotes' => array(
					'soy'   => '512.35',
					'wheat' => 'USD 1.234',
					'corn'  => '-5',
				),
			)
		);

		$this->assertSame( array( 'soy' ), wp_list_pluck( agronews_home_get_quotes(), 'key' ) );
	}

	/**
	 * The read API returns the seven quotes in order, skipping the empty ones.
	 *
	 * @return void
	 */
	public function test_get_quotes_returns_the_configured_quotes_in_order() {
		$this->assertSame(
			array( 'soy', 'wheat', 'corn', 'cattle', 'usd', 'usd_mep', 'usd_blue' ),
			agronews_home_get_quote_keys()
		);

		$all = array_fill_keys( agronews_home_get_quote_keys(), '100' );

		update_option( AGRONEWS_HOME_OPTION, agronews_home_sanitize_settings( array( 'quotes' => $all ) ) );

		$quotes = agronews_home_get_quotes();

		$this->assertCount( 7, $quotes );
		$this->assertSame( 'soy', $quotes[0]['key'] );
		$this->assertSame( '100', $quotes[0]['value'] );
		$this->assertNotEmpty( $quotes[0]['label'] );
		$this->assertNotEmpty( $quotes[0]['currency'] );

		$partial          = $all;
		$partial['wheat'] = '';
		$partial['corn']  = '';

		update_option( AGRONEWS_HOME_OPTION, agronews_home_sanitize_settings( array( 'quotes' => $partial ) ) );

		$quotes = agronews_home_get_quotes();

		$this->assertCount( 5, $quotes );
		$this->assertSame( array( 'soy', 'cattle', 'usd', 'usd_mep', 'usd_blue' ), wp_list_pluck( $quotes, 'key' ) );
	}
}
