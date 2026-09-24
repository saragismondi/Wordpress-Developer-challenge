<?php
/**
 * Custom template tags for this theme.
 *
 * @package AgroNews
 */

if ( ! function_exists( 'agronews_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post date/time.
	 *
	 * @return void
	 */
	function agronews_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		printf(
			'<span class="posted-on"><a href="%1$s" rel="bookmark">%2$s</a></span>',
			esc_url( get_permalink() ),
			wp_kses_post( $time_string )
		);
	}
endif;

if ( ! function_exists( 'agronews_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 *
	 * @return void
	 */
	function agronews_posted_by() {
		printf(
			'<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span></span>',
			esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}
endif;

if ( ! function_exists( 'agronews_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 *
	 * @return void
	 */
	function agronews_entry_footer() {
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'agronews' ) );

			if ( $categories_list ) {
				printf(
					'<span class="cat-links">%1$s %2$s</span>',
					esc_html__( 'Posted in', 'agronews' ),
					wp_kses_post( $categories_list )
				);
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'agronews' ) );

			if ( $tags_list && ! is_wp_error( $tags_list ) ) {
				printf(
					'<span class="tags-links">%1$s %2$s</span>',
					esc_html__( 'Tagged', 'agronews' ),
					wp_kses_post( $tags_list )
				);
			}
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'agronews' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'agronews_post_thumbnail' ) ) :
	/**
	 * Displays the post thumbnail, linked on archives and plain on single views.
	 *
	 * @param string $size Image size to render. Default agronews-lead.
	 * @return void
	 */
	function agronews_post_thumbnail( $size = 'large' ) {
		if ( post_password_required() || is_attachment() ) {
			return;
		}

		$post_id   = get_the_ID();
		$thumb_url = get_the_post_thumbnail_url( $post_id, $size );
		$thumb_id  = get_post_thumbnail_id( $post_id );
		$thumb_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );

		if ( ! $thumb_url ) {
			return;
		}

		if ( is_singular() ) :
			?>
			<figure class="post-thumbnail">
				<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" />
			</figure>
			<?php
		else :
			?>
			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" loading="lazy" />
			</a>
			<?php
		endif;
	}
endif;

if ( ! function_exists( 'agronews_render_card' ) ) :
	/**
	 * Renders one news card. Used by every home block, so the markup of a
	 * story is defined in a single place.
	 *
	 * Must be called inside the loop.
	 *
	 * @param array<string,mixed> $args {
	 *     Optional. Card options.
	 *
	 *     @type string $size     Thumbnail size. Default agronews-card.
	 *     @type bool   $excerpt  Whether to print the excerpt. Default false.
	 *     @type bool   $category Whether to print the primary category. Default true.
	 *     @type string $class    Extra CSS class for the article element.
	 * }
	 * @return void
	 */
	function agronews_render_card( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'size'     => 'medium',
				'excerpt'  => false,
				'category' => true,
				'class'    => '',
			)
		);

		$categories = $args['category'] ? get_the_category() : array();
		$primary    = ! empty( $categories ) ? $categories[0] : null;

		$post_id   = get_the_ID();
		$thumb_url = get_the_post_thumbnail_url( $post_id, $args['size'] );
		$thumb_id  = get_post_thumbnail_id( $post_id );
		$thumb_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
		?>
		<article <?php post_class( 'an-card ' . sanitize_html_class( $args['class'] ) ); ?>>
			<?php if ( $thumb_url ) : ?>
				<a class="an-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" loading="lazy" />
				</a>
			<?php endif; ?>

			<div class="an-card__body">
				<?php if ( $primary instanceof WP_Term ) : ?>
					<a class="an-card__kicker" href="<?php echo esc_url( get_category_link( $primary ) ); ?>">
						<?php echo esc_html( $primary->name ); ?>
					</a>
				<?php endif; ?>

				<h3 class="an-card__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>

				<?php if ( $args['excerpt'] ) : ?>
					<p class="an-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
				<?php endif; ?>

				<p class="an-card__meta">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span class="an-card__sep">&middot;</span>
					<span class="an-card__author"><?php echo esc_html( get_the_author() ); ?></span>
				</p>
			</div>
		</article>
		<?php
	}
endif;

if ( ! function_exists( 'agronews_get_author_social_links' ) ) :
	/**
	 * Returns the social links stored in the author user meta.
	 *
	 * @param int $user_id Author ID.
	 * @return array<int,array<string,string>> List of network, label and URL.
	 */
	function agronews_get_author_social_links( $user_id ) {
		$networks = array(
			'an_x'         => __( 'X', 'agronews' ),
			'an_linkedin'  => __( 'LinkedIn', 'agronews' ),
			'an_instagram' => __( 'Instagram', 'agronews' ),
			'an_site'      => __( 'Website', 'agronews' ),
		);

		$links = array();

		foreach ( $networks as $meta_key => $label ) {
			$url = get_the_author_meta( $meta_key, (int) $user_id );

			if ( ! is_string( $url ) || '' === trim( $url ) ) {
				continue;
			}

			$url = esc_url_raw( $url );

			if ( '' === $url ) {
				continue;
			}

			$links[] = array(
				'network' => str_replace( 'an_', '', $meta_key ),
				'label'   => $label,
				'url'     => $url,
			);
		}

		return $links;
	}
endif;

if ( ! function_exists( 'agronews_render_author_byline' ) ) :
	/**
	 * Renders the author byline of a single post: name, bio and social links.
	 *
	 * @param array<string,mixed> $args {
	 *     Optional. Byline options.
	 *
	 *     @type int  $user_id Author ID. Defaults to the current post author.
	 *     @type bool $bio     Whether to print the biography. Default true.
	 *     @type bool $links   Whether to print the social links. Default true.
	 * }
	 * @return void
	 */
	function agronews_render_author_byline( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'user_id' => 0,
				'bio'     => true,
				'links'   => true,
			)
		);

		$user_id = (int) $args['user_id'];

		if ( $user_id <= 0 ) {
			$user_id = (int) get_the_author_meta( 'ID' );
		}

		if ( $user_id <= 0 ) {
			return;
		}

		$description = get_the_author_meta( 'description', $user_id );
		$links       = $args['links'] ? agronews_get_author_social_links( $user_id ) : array();
		?>
		<div class="an-byline">
			<div class="an-byline__avatar"><?php echo get_avatar( $user_id, 64, '', '', array( 'class' => 'an-byline__img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar() returns escaped markup. ?></div>

			<div class="an-byline__body">
				<p class="an-byline__name">
					<a href="<?php echo esc_url( get_author_posts_url( $user_id ) ); ?>">
						<?php echo esc_html( get_the_author_meta( 'display_name', $user_id ) ); ?>
					</a>
				</p>

				<?php if ( $args['bio'] && '' !== trim( (string) $description ) ) : ?>
					<p class="an-byline__bio"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $links ) ) : ?>
					<ul class="an-byline__links">
						<?php foreach ( $links as $link ) : ?>
							<li>
								<a class="an-byline__link an-byline__link--<?php echo esc_attr( $link['network'] ); ?>" href="<?php echo esc_url( $link['url'] ); ?>" rel="noopener noreferrer nofollow" target="_blank">
									<?php echo esc_html( $link['label'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'agronews_render_share_links' ) ) :
	/**
	 * Renders the share bar.
	 *
	 * One parameterized function covers every context: pass the networks you
	 * want and the URL or title to share.
	 *
	 * @param array<string,mixed> $args {
	 *     Optional. Share options.
	 *
	 *     @type string   $url      URL to share. Defaults to the current permalink.
	 *     @type string   $title    Title to share. Defaults to the current title.
	 *     @type string[] $networks Networks to render, in order. Supported:
	 *                              x, facebook, linkedin, whatsapp, email.
	 *     @type string   $label    Heading of the share bar.
	 * }
	 * @return void
	 */
	function agronews_render_share_links( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'url'      => get_permalink(),
				'title'    => get_the_title(),
				'networks' => array( 'x', 'facebook', 'linkedin', 'whatsapp' ),
				'label'    => __( 'Share', 'agronews' ),
			)
		);

		$url   = esc_url_raw( (string) $args['url'] );
		$title = wp_strip_all_tags( (string) $args['title'] );

		if ( '' === $url ) {
			return;
		}

		$templates = array(
			'x'        => array(
				'label' => __( 'X', 'agronews' ),
				'url'   => 'https://x.com/intent/post?text=%1$s&url=%2$s',
			),
			'facebook' => array(
				'label' => __( 'Facebook', 'agronews' ),
				'url'   => 'https://www.facebook.com/sharer/sharer.php?u=%2$s',
			),
			'linkedin' => array(
				'label' => __( 'LinkedIn', 'agronews' ),
				'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=%2$s',
			),
			'whatsapp' => array(
				'label' => __( 'WhatsApp', 'agronews' ),
				'url'   => 'https://api.whatsapp.com/send?text=%1$s%%20%2$s',
			),
			'email'    => array(
				'label' => __( 'Email', 'agronews' ),
				'url'   => 'mailto:?subject=%1$s&body=%2$s',
			),
		);
		?>
		<div class="an-share">
			<span class="an-share__label"><?php echo esc_html( $args['label'] ); ?></span>
			<ul class="an-share__list">
				<?php
				foreach ( (array) $args['networks'] as $network ) :
					$network = sanitize_key( $network );

					if ( ! isset( $templates[ $network ] ) ) {
						continue;
					}

					$share_url = sprintf(
						$templates[ $network ]['url'],
						rawurlencode( $title ),
						rawurlencode( $url )
					);
					?>
					<li>
						<a class="an-share__link an-share__link--<?php echo esc_attr( $network ); ?>"
							href="<?php echo esc_url( $share_url ); ?>"
							rel="noopener noreferrer nofollow"
							target="_blank">
							<?php
							printf(
								/* translators: %s: social network name. */
								esc_html__( 'Share on %s', 'agronews' ),
								esc_html( $templates[ $network ]['label'] )
							);
							?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'agronews_render_section_heading' ) ) :
	/**
	 * Renders the heading of a home block.
	 *
	 * @param string $title Section title.
	 * @param string $link  Optional. URL the heading links to.
	 * @return void
	 */
	function agronews_render_section_heading( $title, $link = '' ) {
		?>
		<h2 class="an-block__title">
			<?php if ( '' !== $link ) : ?>
				<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $title ); ?>
			<?php endif; ?>
		</h2>
		<?php
	}
endif;
