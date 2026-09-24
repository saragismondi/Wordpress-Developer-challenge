<?php
/**
 * Home block: Latest.
 *
 * The ten most recent stories of the portal, no category filter.
 *
 * @package AgroNews
 */

$agronews_query = agronews_get_block_query( 'latest' );

if ( ! $agronews_query->have_posts() ) {
	return;
}
?>
<section class="an-block an-block--latest" aria-labelledby="an-block-latest">
	<span id="an-block-latest"></span>
	<?php agronews_render_section_heading( __( 'Latest', 'agronews' ), (string) get_post_type_archive_link( 'post' ) ); ?>

	<div class="an-list">
		<?php
		while ( $agronews_query->have_posts() ) :
			$agronews_query->the_post();

			agronews_render_card(
				array(
					'size'  => 'agronews-card',
					'class' => 'an-card--row',
				)
			);
		endwhile;

		wp_reset_postdata();
		?>
	</div>
</section>
