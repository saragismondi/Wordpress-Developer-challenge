<?php
/**
 * Home block: Most read.
 *
 * Six stories ranked by the an_views counter, with the counter shown next to
 * each one.
 *
 * @package AgroNews
 */

$agronews_query = agronews_get_block_query( 'most-read' );

if ( ! $agronews_query->have_posts() ) {
	return;
}

$agronews_position = 0;
?>
<section class="an-block an-block--most-read" aria-labelledby="an-block-most-read">
	<span id="an-block-most-read"></span>
	<?php agronews_render_section_heading( __( 'Most read', 'agronews' ) ); ?>

	<ol class="an-ranking">
		<?php
		while ( $agronews_query->have_posts() ) :
			$agronews_query->the_post();

			++$agronews_position;

			$agronews_views = get_post_meta( get_the_ID(), AGRONEWS_VIEWS_META, true );
			?>
			<li class="an-ranking__item">
				<span class="an-ranking__position" aria-hidden="true"><?php echo esc_html( number_format_i18n( $agronews_position ) ); ?></span>
				<?php
				agronews_render_card(
					array(
						'class' => 'an-card--ranked',
					)
				);
				?>
				<span class="an-ranking__views">
					<?php
					printf(
						/* translators: %s: number of readings of the story. */
						esc_html__( '%s readings', 'agronews' ),
						esc_html( number_format_i18n( (int) $agronews_views ) )
					);
					?>
				</span>
			</li>
			<?php
		endwhile;

		wp_reset_postdata();
		?>
	</ol>
</section>
