<?php
/**
 * Home widget: weather.
 *
 * Shows the Buenos Aires forecast published by the portal.
 *
 * @package AgroNews
 */

$agronews_weather = an_weather_home();
?>
<aside class="an-block an-block--weather" aria-labelledby="an-block-weather">
	<span id="an-block-weather"></span>
	<?php agronews_render_section_heading( __( 'Weather', 'agronews' ) ); ?>

	<?php if ( null === $agronews_weather ) : ?>

		<p class="an-weather__empty">Clima no disponible</p>

	<?php else : ?>

		<p class="an-weather__place">
			<?php echo esc_html( $agronews_weather['city'] ); ?>
			<?php if ( ! empty( $agronews_weather['region'] ) ) : ?>
				<span class="an-weather__region"><?php echo esc_html( $agronews_weather['region'] ); ?></span>
			<?php endif; ?>
		</p>

		<ul class="an-weather__days">
			<?php foreach ( array_slice( (array) $agronews_weather['days'], 0, 4 ) as $agronews_day ) : ?>
				<?php
				if ( ! is_array( $agronews_day ) || empty( $agronews_day['date'] ) ) {
					continue;
				}

				$agronews_timestamp = strtotime( (string) $agronews_day['date'] );
				?>
				<li class="an-weather__day">
					<span class="an-weather__date">
						<?php
						echo esc_html(
							false !== $agronews_timestamp
								? wp_date( 'D j', $agronews_timestamp )
								: (string) $agronews_day['date']
						);
						?>
					</span>
					<span class="an-weather__summary"><?php echo esc_html( isset( $agronews_day['summary'] ) ? $agronews_day['summary'] : '' ); ?></span>
					<span class="an-weather__temp">
						<?php
						printf(
							/* translators: 1: minimum temperature, 2: maximum temperature, both in Celsius. */
							esc_html__( '%1$s° / %2$s°', 'agronews' ),
							esc_html( number_format_i18n( (float) ( isset( $agronews_day['min_c'] ) ? $agronews_day['min_c'] : 0 ) ) ),
							esc_html( number_format_i18n( (float) ( isset( $agronews_day['max_c'] ) ? $agronews_day['max_c'] : 0 ) ) )
						);
						?>
					</span>
					<span class="an-weather__rain">
						<?php
						printf(
							/* translators: %s: expected rainfall in millimetres. */
							esc_html__( '%s mm', 'agronews' ),
							esc_html( number_format_i18n( (float) ( isset( $agronews_day['rain_mm'] ) ? $agronews_day['rain_mm'] : 0 ), 1 ) )
						);
						?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( ! empty( $agronews_weather['updated'] ) ) : ?>
			<p class="an-weather__updated">
				<?php
				printf(
					/* translators: %s: date the forecast was produced. */
					esc_html__( 'Updated %s', 'agronews' ),
					esc_html( (string) $agronews_weather['updated'] )
				);
				?>
			</p>
		<?php endif; ?>

	<?php endif; ?>
</aside>
