<?php
/**
 * The main template file.
 *
 * Used for the blog index (the "Blog" page) and as the fallback for anything
 * without a more specific template. The portal home lives in front-page.php.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package AgroNews
 */

get_header();
?>

	<main id="primary" class="site-main an-archive">

		<?php if ( have_posts() ) : ?>

			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header class="page-header an-archive__header">
					<h1 class="page-title an-archive__title"><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

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
