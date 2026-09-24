#!/usr/bin/env bash
#
# Brings the stack up and leaves a working WordPress behind: containers
# running, core installed, theme and plugin active, permalinks flushed.
#
# Safe to run as many times as you want.

# shellcheck source=bin/lib.sh
. "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

say "Starting containers"
dc up -d db wordpress

say "Waiting for the database"
wait_for_db

say "Waiting for WordPress to answer on ${WP_URL}"
wait_for_http

if wpcli core is-installed >/dev/null 2>&1; then
	say "WordPress is already installed, skipping core install"
else
	say "Installing WordPress"
	wpcli core install \
		--url="$WP_URL" \
		--title="$WP_TITLE" \
		--admin_user="$WP_ADMIN_USER" \
		--admin_password="$WP_ADMIN_PASSWORD" \
		--admin_email="$WP_ADMIN_EMAIL" \
		--skip-email
fi

# The sample content of a fresh install would show up in the portal blocks.
# Matched by slug, so a re-run never touches seeded content.
say "Removing the sample content"
for sample in hello-world sample-page privacy-policy; do
	ids="$(wpcli post list --post_type=post,page --post_status=any --name="$sample" --format=ids 2>/dev/null | tr -d '\r')"

	if [ -n "$ids" ]; then
		# shellcheck disable=SC2086
		wpcli post delete $ids --force >/dev/null 2>&1 || true
	fi
done

say "Applying site options"
wpcli option update blogdescription "Noticias del agro" >/dev/null
wpcli option update timezone_string "America/Argentina/Buenos_Aires" >/dev/null
wpcli option update date_format "j F Y" >/dev/null
wpcli option update start_of_week 1 >/dev/null
wpcli option update default_ping_status closed >/dev/null
wpcli option update default_comment_status closed >/dev/null
# The site is local: keep it out of search engines.
wpcli option update blog_public 0 >/dev/null

say "Activating the AgroNews theme"
wpcli theme activate agronews

say "Activating the AgroNews Home plugin"
wpcli plugin activate agronews-home

# Bundled themes and plugins of the official image are dead weight here.
say "Removing the default themes and plugins"
for item in twentytwentythree twentytwentyfour twentytwentyfive; do
	wpcli theme delete "$item" >/dev/null 2>&1 || true
done
for item in akismet hello; do
	wpcli plugin delete "$item" >/dev/null 2>&1 || true
done

say "Flushing permalinks"
# The container image already ships the standard WordPress .htaccess, so the
# rules only need to be written to the database.
wpcli rewrite structure '/%year%/%monthnum%/%postname%/' >/dev/null
wpcli rewrite flush >/dev/null

say "Ready"
cat <<EOF

  Site:   ${WP_URL}
  Admin:  ${WP_URL}/wp-admin  (${WP_ADMIN_USER} / ${WP_ADMIN_PASSWORD})

  Next:   bin/seed.sh    loads the demo content
          bin/bench.sh   measures the home page

EOF
