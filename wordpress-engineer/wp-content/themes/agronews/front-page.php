<?php
/**
 * The front page of the portal.
 *
 * Composes the home out of independent blocks. Each block owns its own
 * WP_Query (see inc/blocks.php) and lives in its own template part, so a
 * block can be moved, removed or reordered without touching the others.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package AgroNews
 */

get_header();
?>

	<main id="primary" class="site-main an-home">

		<?php get_template_part( 'template-parts/block', 'quotes' ); ?>

		<?php get_template_part( 'template-parts/block', 'featured' ); ?>

		<div class="an-home__columns">
			<?php get_template_part( 'template-parts/block', 'latest' ); ?>
			<?php get_template_part( 'template-parts/block', 'weather' ); ?>
		</div>

		<?php get_template_part( 'template-parts/block', 'sections' ); ?>

		<?php get_template_part( 'template-parts/block', 'most-read' ); ?>

	</main><!-- #primary -->

<?php
get_footer();
