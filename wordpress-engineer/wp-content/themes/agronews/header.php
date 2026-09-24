<?php
/**
 * The header of the portal.
 *
 * Everything up to the opening of #content. Assets are not printed here: they
 * are enqueued in inc/enqueue.php and rendered by wp_head().
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package AgroNews
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'agronews' ); ?></a>

	<header id="masthead" class="site-header an-header">
		<div class="an-header__inner">
			<div class="site-branding an-header__branding">
				<?php
				the_custom_logo();

				if ( is_front_page() ) :
					?>
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php
				else :
					?>
					<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
					<?php
				endif;

				$agronews_description = get_bloginfo( 'description', 'display' );

				if ( $agronews_description || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $agronews_description ); ?></p>
				<?php endif; ?>
			</div><!-- .site-branding -->

			<nav id="site-navigation" class="main-navigation an-nav" aria-label="<?php esc_attr_e( 'Sections', 'agronews' ); ?>">
				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Sections', 'agronews' ); ?></button>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav><!-- #site-navigation -->
		</div>
	</header><!-- #masthead -->
