<?php
/**
 * Home bar: commodity and currency quotes.
 *
 * The values come from the AgroNews Home plugin settings page. Without the
 * plugin the bar is simply not rendered.
 *
 * @package AgroNews
 */

if ( ! function_exists( 'agronews_home_get_quotes' ) ) {
	return;
}

$agronews_quotes = agronews_home_get_quotes();

if ( empty( $agronews_quotes ) ) {
	return;
}
?>
<div class="an-quotes" role="region" aria-label="<?php esc_attr_e( 'Market quotes', 'agronews' ); ?>">
	<ul class="an-quotes__list">
		<?php foreach ( $agronews_quotes as $agronews_quote ) : ?>
			<li class="an-quotes__item">
				<span class="an-quotes__label"><?php echo esc_html( $agronews_quote['label'] ); ?></span>
				<span class="an-quotes__value">
					<?php
					printf(
						/* translators: 1: currency symbol, 2: quoted value. */
						esc_html__( '%1$s %2$s', 'agronews' ),
						esc_html( $agronews_quote['currency'] ),
						esc_html( number_format( $agronews_quote['value'], 0, ',', '.' ) )
					);
					?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
