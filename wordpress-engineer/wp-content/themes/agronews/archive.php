<?php
/**
 * The template for displaying archives that have no more specific template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package AgroNews
 */

get_header();
?>

	<main id="primary" class="site-main an-archive">

		<?php if ( have_posts() ) : ?>

			<header class="page-header an-archive__header">
				<?php
				the_archive_title( '<h1 class="page-title an-archive__title">', '</h1>' );
				the_archive_description( '<div class="archive-description">', '</div>' );
				?>
			</header>

			<div class="an-grid an-grid--archive">
				<?php
				while ( have_posts() ) :
					the_post();

					agronews_render_card( array( 'excerpt' => true ) );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( 'Newer', 'agronews' ),
					'next_text' => esc_html__( 'Older', 'agronews' ),
				)
			);

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

	</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
