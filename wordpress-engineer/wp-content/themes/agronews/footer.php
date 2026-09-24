<?php
/**
 * The footer of the portal.
 *
 * Closes #content and prints the "Topics of the day" block, which is rendered
 * by a single function (see inc/blocks.php).
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package AgroNews
 */

?>

	<footer id="colophon" class="site-footer an-footer">
		<div class="an-footer__inner">
			<?php agronews_render_topics( array( 'number' => 12 ) ); ?>

			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'menu_id'        => 'footer-menu',
						'depth'          => 1,
						'container'      => 'nav',
						'fallback_cb'    => false,
					)
				);
			}
			?>

			<div class="site-info an-footer__info">
				<?php
				printf(
					/* translators: 1: current year, 2: site name. */
					esc_html__( '© %1$s %2$s. Fictional portal used for a technical exercise.', 'agronews' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</div><!-- .site-info -->
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
