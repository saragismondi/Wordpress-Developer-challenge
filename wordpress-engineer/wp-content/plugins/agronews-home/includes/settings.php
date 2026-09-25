<?php
/**
 * Settings page: home block categories and market quotes.
 *
 * Built on the Settings API, so the nonce and the capability check on save
 * are handled by options.php, and every value goes through one sanitize
 * callback before it reaches the database.
 *
 * @package AgroNews_Home
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the default settings.
 *
 * @return array<string,mixed> Default settings.
 */
function agronews_home_default_settings() {
	$quotes = array();

	foreach ( agronews_home_get_quote_keys() as $key ) {
		$quotes[ $key ] = '';
	}

	return array(
		'featured_category'  => 0,
		'section_categories' => array( 0, 0, 0 ),
		'quotes'             => $quotes,
	);
}

/**
 * Returns the stored settings, normalized against the defaults.
 *
 * Callers can rely on every key being present and on the shape of the
 * section_categories and quotes sub-arrays.
 *
 * @return array<string,mixed> Settings.
 */
function agronews_home_get_settings() {
	$defaults = agronews_home_default_settings();
	$stored   = get_option( AGRONEWS_HOME_OPTION, array() );

	if ( ! is_array( $stored ) ) {
		return $defaults;
	}

	$settings = wp_parse_args( $stored, $defaults );

	$sections = array();

	for ( $index = 0; $index < 3; $index++ ) {
		$sections[ $index ] = isset( $settings['section_categories'][ $index ] )
			? (int) $settings['section_categories'][ $index ]
			: 0;
	}

	$settings['section_categories'] = $sections;
	$settings['featured_category']  = (int) $settings['featured_category'];

	$quotes = array();

	foreach ( agronews_home_get_quote_keys() as $key ) {
		$quotes[ $key ] = isset( $settings['quotes'][ $key ] ) ? $settings['quotes'][ $key ] : '';
	}

	$settings['quotes'] = $quotes;

	return $settings;
}

/**
 * Reads one setting.
 *
 * @param string $key           Setting key.
 * @param mixed  $default_value Value returned when the key is unknown.
 * @return mixed Setting value.
 */
function agronews_home_get_setting( $key, $default_value = '' ) {
	$settings = agronews_home_get_settings();

	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default_value;
}

/**
 * Registers the option, its sections and its fields.
 *
 * @return void
 */
function agronews_home_register_settings() {
	register_setting(
		AGRONEWS_HOME_GROUP,
		AGRONEWS_HOME_OPTION,
		array(
			'type'              => 'array',
			'description'       => __( 'AgroNews home configuration.', 'agronews-home' ),
			'sanitize_callback' => 'agronews_home_sanitize_settings',
			'default'           => agronews_home_default_settings(),
			'show_in_rest'      => false,
		)
	);

	add_settings_section(
		'agronews_home_blocks',
		__( 'Home blocks', 'agronews-home' ),
		'agronews_home_render_blocks_section',
		AGRONEWS_HOME_PAGE
	);

	add_settings_field(
		'agronews_home_featured_category',
		__( 'Featured category', 'agronews-home' ),
		'agronews_home_render_featured_field',
		AGRONEWS_HOME_PAGE,
		'agronews_home_blocks',
		array( 'label_for' => 'agronews-home-featured-category' )
	);

	add_settings_field(
		'agronews_home_section_categories',
		__( 'Section categories', 'agronews-home' ),
		'agronews_home_render_sections_field',
		AGRONEWS_HOME_PAGE,
		'agronews_home_blocks'
	);

	add_settings_section(
		'agronews_home_quotes',
		__( 'Market quotes', 'agronews-home' ),
		'agronews_home_render_quotes_section',
		AGRONEWS_HOME_PAGE
	);

	foreach ( agronews_home_get_quote_definitions() as $key => $definition ) {
		add_settings_field(
			'agronews_home_quote_' . $key,
			$definition['label'],
			'agronews_home_render_quote_field',
			AGRONEWS_HOME_PAGE,
			'agronews_home_quotes',
			array(
				'key'       => $key,
				'currency'  => $definition['currency'],
				'label_for' => 'agronews-home-quote-' . $key,
			)
		);
	}
}
add_action( 'admin_init', 'agronews_home_register_settings' );

/**
 * Declares the capability required to save this option group.
 *
 * The options.php screen enforces it before running the sanitize callback.
 *
 * @return string Capability name.
 */
function agronews_home_option_capability() {
	return 'manage_options';
}
add_filter( 'option_page_capability_' . AGRONEWS_HOME_GROUP, 'agronews_home_option_capability' );

/**
 * Adds the settings page under the Settings menu.
 *
 * @return void
 */
function agronews_home_add_menu() {
	add_options_page(
		__( 'AgroNews Home', 'agronews-home' ),
		__( 'AgroNews Home', 'agronews-home' ),
		'manage_options',
		AGRONEWS_HOME_PAGE,
		'agronews_home_render_page'
	);
}
add_action( 'admin_menu', 'agronews_home_add_menu' );

/**
 * Adds a Settings shortcut to the plugin row.
 *
 * @param string[] $links Action links.
 * @return string[] Action links.
 */
function agronews_home_action_links( $links ) {
	$url = add_query_arg( 'page', AGRONEWS_HOME_PAGE, admin_url( 'options-general.php' ) );

	array_unshift(
		$links,
		sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html__( 'Settings', 'agronews-home' ) )
	);

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( AGRONEWS_HOME_FILE ), 'agronews_home_action_links' );

/**
 * Renders the settings page.
 *
 * Validation notices are printed by core (options-head.php) for pages hanging
 * off the Settings menu, so this callback does not call settings_errors().
 *
 * @return void
 */
function agronews_home_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the AgroNews home.', 'agronews-home' ) );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form action="options.php" method="post">
			<?php
			// Prints the nonce, the option group and the referer fields.
			settings_fields( AGRONEWS_HOME_GROUP );
			do_settings_sections( AGRONEWS_HOME_PAGE );
			submit_button( __( 'Save home settings', 'agronews-home' ) );
			?>
		</form>
	</div>
	<?php
}

/**
 * Intro text of the blocks section.
 *
 * @return void
 */
function agronews_home_render_blocks_section() {
	echo '<p>' . esc_html__( 'Categories behind the front page blocks. Leave a category empty to fall back to the whole site.', 'agronews-home' ) . '</p>';
}

/**
 * Intro text of the quotes section.
 *
 * @return void
 */
function agronews_home_render_quotes_section() {
	echo '<p>' . esc_html__( 'Values shown in the quotes bar, as published by the market desk.', 'agronews-home' ) . '</p>';
}

/**
 * Renders a category dropdown.
 *
 * @param string $id       Field HTML id.
 * @param string $name     Field HTML name.
 * @param int    $selected Selected term ID.
 * @return void
 */
function agronews_home_render_category_dropdown( $id, $name, $selected ) {
	$dropdown = wp_dropdown_categories(
		array(
			'id'                => $id,
			'name'              => $name,
			'selected'          => (int) $selected,
			'show_option_none'  => __( '— No category —', 'agronews-home' ),
			'option_none_value' => 0,
			'hide_empty'        => false,
			'hierarchical'      => true,
			'orderby'           => 'name',
			'echo'              => false,
		)
	);

	// wp_dropdown_categories() returns markup already escaped by core.
	echo $dropdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the featured category field.
 *
 * @return void
 */
function agronews_home_render_featured_field() {
	agronews_home_render_category_dropdown(
		'agronews-home-featured-category',
		AGRONEWS_HOME_OPTION . '[featured_category]',
		(int) agronews_home_get_setting( 'featured_category', 0 )
	);
	?>
	<p class="description"><?php esc_html_e( 'Feeds the "Featured" block: 5 stories.', 'agronews-home' ); ?></p>
	<?php
}

/**
 * Renders the three section category fields.
 *
 * @return void
 */
function agronews_home_render_sections_field() {
	$sections = (array) agronews_home_get_setting( 'section_categories', array( 0, 0, 0 ) );

	for ( $index = 0; $index < 3; $index++ ) {
		$selected = isset( $sections[ $index ] ) ? (int) $sections[ $index ] : 0;
		?>
		<p>
			<label for="<?php echo esc_attr( 'agronews-home-section-' . $index ); ?>" class="screen-reader-text">
				<?php
				printf(
					/* translators: %d: position of the section in the block. */
					esc_html__( 'Section %d', 'agronews-home' ),
					(int) $index + 1
				);
				?>
			</label>
			<?php
			agronews_home_render_category_dropdown(
				'agronews-home-section-' . $index,
				AGRONEWS_HOME_OPTION . '[section_categories][' . $index . ']',
				$selected
			);
			?>
		</p>
		<?php
	}
	?>
	<p class="description"><?php esc_html_e( 'Feeds the "By section" block: 3 categories, 4 stories each.', 'agronews-home' ); ?></p>
	<?php
}

/**
 * Renders one quote field.
 *
 * @param array<string,string> $args Field arguments: key, currency, label_for.
 * @return void
 */
function agronews_home_render_quote_field( $args ) {
	$key      = isset( $args['key'] ) ? (string) $args['key'] : '';
	$currency = isset( $args['currency'] ) ? (string) $args['currency'] : '';
	$quotes   = (array) agronews_home_get_setting( 'quotes', array() );
	$value    = isset( $quotes[ $key ] ) ? $quotes[ $key ] : '';
	?>
	<input
		type="text"
		class="regular-text"
		id="<?php echo esc_attr( 'agronews-home-quote-' . $key ); ?>"
		name="<?php echo esc_attr( AGRONEWS_HOME_OPTION . '[quotes][' . $key . ']' ); ?>"
		value="<?php echo esc_attr( (string) $value ); ?>"
	/>
	<span class="description">
		<?php echo esc_html( $currency ); ?>
		<?php esc_html_e( 'Example: 1.234,50', 'agronews-home' ); ?>
	</span>
	<?php
}

/**
 * Sanitizes a category ID.
 *
 * Anything that is not an existing category comes back as 0, which the theme
 * reads as "no category filter".
 *
 * @param mixed $value Raw value.
 * @return int Term ID, or 0.
 */
function agronews_home_sanitize_category( $value ) {
	$term_id = is_scalar( $value ) ? absint( $value ) : 0;

	if ( $term_id <= 0 ) {
		return 0;
	}

	return get_term( $term_id, 'category' ) instanceof WP_Term ? $term_id : 0;
}

/**
 * Sanitizes the whole option before it is written.
 *
 * Categories that no longer exist are dropped, and every quote is cleaned with
 * sanitize_text_field() before it reaches the database.
 *
 * @param mixed $input Raw value submitted by the form.
 * @return array<string,mixed> Sanitized settings.
 */
function agronews_home_sanitize_settings( $input ) {
	$current = agronews_home_get_settings();
	$output  = agronews_home_default_settings();

	if ( ! is_array( $input ) ) {
		return $current;
	}

	$output['featured_category'] = agronews_home_sanitize_category(
		isset( $input['featured_category'] ) ? $input['featured_category'] : 0
	);

	$sections = isset( $input['section_categories'] ) && is_array( $input['section_categories'] )
		? array_values( $input['section_categories'] )
		: array();

	for ( $index = 0; $index < 3; $index++ ) {
		$output['section_categories'][ $index ] = agronews_home_sanitize_category(
			isset( $sections[ $index ] ) ? $sections[ $index ] : 0
		);
	}

	$raw_quotes = isset( $input['quotes'] ) && is_array( $input['quotes'] ) ? $input['quotes'] : array();

	$definitions = agronews_home_get_quote_definitions();

	foreach ( agronews_home_get_quote_keys() as $key ) {
		$raw = isset( $raw_quotes[ $key ] ) && is_scalar( $raw_quotes[ $key ] )
			? (string) $raw_quotes[ $key ]
			: '';

		$parsed = agronews_home_parse_quote( $raw );

		if ( null === $parsed ) {
			// Keep the last valid value instead of storing something the bar cannot format.
			$output['quotes'][ $key ] = $current['quotes'][ $key ];

			if ( function_exists( 'add_settings_error' ) ) {
				add_settings_error(
					AGRONEWS_HOME_OPTION,
					'agronews-home-invalid-quote-' . $key,
					sprintf(
						/* translators: 1: quote label, 2: value typed by the editor. */
						__( '%1$s: "%2$s" is not a valid number, the previous value was kept. Use a format like 1.234,50 or 1234.50.', 'agronews-home' ),
						esc_html( $definitions[ $key ]['label'] ),
						esc_html( sanitize_text_field( $raw ) )
					)
				);
			}

			continue;
		}

		$output['quotes'][ $key ] = $parsed;
	}

	return $output;
}

/**
 * Normalizes a quote typed by an editor into a plain decimal string.
 *
 * The market desk writes numbers the Argentine way (1.234,50), while older
 * values and the seeder use a dot as the decimal separator (512.35). Both are
 * accepted; anything ambiguous or non numeric is rejected rather than guessed.
 *
 * - "1.234,50" and "1.234" => "1234.50" and "1234" (dots group thousands).
 * - "1234,5"               => "1234.5" (comma is the decimal separator).
 * - "512.35"               => "512.35" (dot decimal, no grouping).
 *
 * @param string $raw Raw value from the settings form.
 * @return string|null Normalized value, '' for an empty field, null when invalid.
 */
function agronews_home_parse_quote( $raw ) {
	$value = str_replace( array( ' ', "\u{00A0}" ), '', sanitize_text_field( (string) $raw ) );

	if ( '' === $value ) {
		return '';
	}

	// Thousands grouped with dots, optional decimal comma: 1.234 or 1.234,50.
	if ( preg_match( '/^\d{1,3}(?:\.\d{3})+(?:,\d+)?$/', $value ) ) {
		return str_replace( array( '.', ',' ), array( '', '.' ), $value );
	}

	// Decimal comma without grouping: 1234,50.
	if ( preg_match( '/^\d+,\d+$/', $value ) ) {
		return str_replace( ',', '.', $value );
	}

	// Plain number with an optional decimal dot: 1234 or 512.35.
	if ( preg_match( '/^\d+(?:\.\d+)?$/', $value ) ) {
		return $value;
	}

	return null;
}
