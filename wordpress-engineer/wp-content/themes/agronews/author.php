<?php
/**
 * The template for displaying an author archive.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package AgroNews
 */

get_header();

$agronews_author_id = (int) get_queried_object_id();
?>

	<main id="primary" class="site-main an-archive an-archive--author">

		<header class="page-header an-archive__header">
			<h1 class="page-title an-archive__title">
				<?php
				printf(
					/* translators: %s: author display name. */
					esc_html__( 'Stories by %s', 'agronews' ),
					esc_html( get_the_author_meta( 'display_name', $agronews_author_id ) )
				);
				?>
			</h1>

			<?php
			agronews_render_author_byline( array( 'user_id' => $agronews_author_id ) );
			?>
		</header>

		<?php if ( have_posts() ) : ?>

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
