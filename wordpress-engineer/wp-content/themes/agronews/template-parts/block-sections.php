<?php
/**
 * Home block: By section.
 *
 * Three categories configured in AgroNews Home, four stories each: the first
 * one opens the column and the rest follow as a list.
 *
 * @package AgroNews
 */

$agronews_categories = agronews_get_section_category_ids();

if ( empty( $agronews_categories ) ) {
	return;
}
?>
<section class="an-block an-block--sections" aria-labelledby="an-block-sections">
	<span id="an-block-sections"></span>
	<?php agronews_render_section_heading( __( 'By section', 'agronews' ) ); ?>

	<div class="an-grid an-grid--sections">
		<?php foreach ( $agronews_categories as $agronews_category_id ) : ?>
			<?php
			$agronews_term = get_term( $agronews_category_id, 'category' );

			if ( ! $agronews_term instanceof WP_Term ) {
				continue;
			}

			$agronews_query = agronews_get_block_query( 'section', $agronews_category_id );

			if ( ! $agronews_query->have_posts() ) {
				continue;
			}
			?>
			<div class="an-section">
				<h3 class="an-section__title">
					<a href="<?php echo esc_url( get_category_link( $agronews_term ) ); ?>">
						<?php echo esc_html( $agronews_term->name ); ?>
					</a>
				</h3>

				<div class="an-section__items">
					<?php
					while ( $agronews_query->have_posts() ) :
						$agronews_query->the_post();

						agronews_render_card(
							array(
								'category' => false,
								'class'    => 'an-card--compact',
							)
						);
					endwhile;

					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
