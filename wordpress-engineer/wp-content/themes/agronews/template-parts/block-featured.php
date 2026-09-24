<?php
/**
 * Home block: Featured.
 *
 * The lead story is rendered large and the other four as regular cards, so
 * each list is fetched with the shape it needs.
 *
 * @package AgroNews
 */

$agronews_lead = agronews_get_block_query( 'featured', 0, array( 'posts_per_page' => 1 ) );
$agronews_rest = agronews_get_block_query(
	'featured',
	0,
	array(
		'posts_per_page' => 4,
		'offset'         => 1,
	)
);

if ( ! $agronews_lead->have_posts() ) {
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
		while ( $agronews_lead->have_posts() ) :
			$agronews_lead->the_post();

			agronews_render_card(
				array(
					'size'    => 'large',
					'excerpt' => true,
					'class'   => 'an-card--lead',
				)
			);
		endwhile;

		wp_reset_postdata();

		while ( $agronews_rest->have_posts() ) :
			$agronews_rest->the_post();

			agronews_render_card();
		endwhile;

		wp_reset_postdata();
		?>
	</div>
</section>
