<?php
/**
 * The template for displaying a single story.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package AgroNews
 */

get_header();
?>

	<main id="primary" class="site-main an-single">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'an-story' ); ?>>
				<header class="entry-header an-story__header">
					<?php the_title( '<h1 class="entry-title an-story__title">', '</h1>' ); ?>

					<div class="entry-meta an-story__meta">
						<?php
						agronews_posted_on();
						agronews_posted_by();
						?>
					</div>
				</header>

				<?php agronews_post_thumbnail(); ?>

				<div class="entry-content an-story__content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'agronews' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>

				<footer class="entry-footer an-story__footer">
					<?php
					agronews_entry_footer();

					agronews_render_share_links(
						array(
							'networks' => array( 'x', 'facebook', 'linkedin', 'whatsapp', 'email' ),
						)
					);

					agronews_render_author_byline();
					?>
				</footer>
			</article>

			<?php
			$agronews_related = agronews_get_related_query( get_the_ID(), 4 );

			if ( $agronews_related->have_posts() ) :
				?>
				<section class="an-block an-block--related" aria-labelledby="an-block-related">
					<span id="an-block-related"></span>
					<?php agronews_render_section_heading( __( 'Related stories', 'agronews' ) ); ?>

					<div class="an-grid an-grid--related">
						<?php
						while ( $agronews_related->have_posts() ) :
							$agronews_related->the_post();

							agronews_render_card( array( 'class' => 'an-card--compact' ) );
						endwhile;

						wp_reset_postdata();
						?>
					</div>
				</section>
				<?php
			endif;

			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'agronews' ) . '</span> <span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'agronews' ) . '</span> <span class="nav-title">%title</span>',
				)
			);

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile;
		?>

	</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
