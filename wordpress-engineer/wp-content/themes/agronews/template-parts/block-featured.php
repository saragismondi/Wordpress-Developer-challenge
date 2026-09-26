<?php
/**
 * Home block: Featured.
 *
 * The lead story is rendered large and the other four as regular cards. One
 * query fetches all five; the first post of the loop becomes the lead.
 *
 * @package AgroNews
 */

$agronews_query = agronews_get_block_query( 'featured' );

if ( ! $agronews_query->have_posts() ) {
	return;
}

$agronews_category = agronews_get_featured_category_id();
$agronews_link     = $agronews_category > 0 ? (string) get_category_link( $agronews_category ) : '';
?>
<section class="an-block an-block--featured" aria-labelledby="an-block-featured">
	<span id="an-block-featured"></span>
	<?php agronews_render_section_heading( __( 'Featured', 'agronews' ), $agronews_link ); ?>

	<div class="an-grid an-grid--featured">
		<?php
		while ( $agronews_query->have_posts() ) :
			$agronews_query->the_post();

			if ( 0 === $agronews_query->current_post ) {
				agronews_render_card(
					array(
						'size'    => 'large',
						'excerpt' => true,
						'class'   => 'an-card--lead',
					)
				);
			} else {
				agronews_render_card();
			}
		endwhile;

		wp_reset_postdata();
		?>
	</div>
</section>
