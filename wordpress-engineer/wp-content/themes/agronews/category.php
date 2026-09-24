<?php
/**
 * The template for displaying a category archive: one section of the portal.
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
				<h1 class="page-title an-archive__title"><?php single_cat_title(); ?></h1>
				<?php
				$agronews_description = category_description();

				if ( '' !== $agronews_description ) {
					echo '<div class="archive-description">' . wp_kses_post( $agronews_description ) . '</div>';
				}
				?>
			</header>

			<?php
			/*
			 * The weather section opens with the forecast of the provinces the
			 * portal covers.
			 */
			if ( is_category( 'clima' ) ) :
				?>
				<div class="an-weather-boards">
					<?php foreach ( array( 'buenos-aires', 'cordoba', 'santa-fe' ) as $agronews_place ) : ?>
						<?php
						$agronews_board = an_weather_province( $agronews_place );

						if ( null === $agronews_board ) {
							continue;
						}

						$agronews_today = $agronews_board['days'][0];
						?>
						<div class="an-weather-board">
							<p class="an-weather__place"><?php echo esc_html( $agronews_board['city'] ); ?></p>
							<p class="an-weather__summary"><?php echo esc_html( isset( $agronews_today['summary'] ) ? $agronews_today['summary'] : '' ); ?></p>
							<p class="an-weather__temp">
								<?php
								printf(
									/* translators: 1: minimum temperature, 2: maximum temperature, both in Celsius. */
									esc_html__( '%1$s° / %2$s°', 'agronews' ),
									esc_html( number_format_i18n( (float) ( isset( $agronews_today['min_c'] ) ? $agronews_today['min_c'] : 0 ) ) ),
									esc_html( number_format_i18n( (float) ( isset( $agronews_today['max_c'] ) ? $agronews_today['max_c'] : 0 ) ) )
								);
								?>
							</p>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
			endif;
			?>

			<div class="an-grid an-grid--archive">
				<?php
				while ( have_posts() ) :
					the_post();

					agronews_render_card(
						array(
							'category' => false,
							'excerpt'  => true,
						)
					);
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
