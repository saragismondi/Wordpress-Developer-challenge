#!/usr/bin/env bash
#
# Loads the demo content: sections, topics, staff, 2000 stories with a
# generated featured image each, pages, menu and plugin settings.
#
# Idempotent: every step checks what is already there, so an interrupted run
# can simply be repeated. Nothing is downloaded.
#
# Override the number of stories with AN_SEED_POSTS=200 bin/seed.sh

# shellcheck source=bin/lib.sh
. "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

if ! wpcli core is-installed >/dev/null 2>&1; then
	echo "WordPress is not installed yet. Run bin/setup.sh first." >&2
	exit 1
fi

say "Seeding sections and topics"
wpcli eval-file /repo/bin/seed/taxonomies.php

say "Seeding the editorial staff"
wpcli eval-file /repo/bin/seed/users.php

say "Seeding stories (this is the slow one: it draws an image per story)"
AN_SEED_POSTS="${AN_SEED_POSTS:-2000}" \
	dc run --rm -T -e AN_SEED_POSTS="${AN_SEED_POSTS:-2000}" wpcli wp eval-file /repo/bin/seed/posts.php

say "Seeding pages, menu and plugin settings"
wpcli eval-file /repo/bin/seed/options.php

say "Flushing permalinks"
wpcli rewrite flush >/dev/null

say "Content summary"
# --format=count prints the number without a trailing newline, so each value
# is captured before being printed.
stories="$(wpcli post list --post_type=post --post_status=publish --format=count)"
images="$(wpcli post list --post_type=attachment --format=count)"
categories="$(wpcli term list category --format=count)"
tags="$(wpcli term list post_tag --format=count)"
users="$(wpcli user list --format=count)"

printf '  %-12s %s\n' \
	'stories:' "$stories" \
	'images:' "$images" \
	'categories:' "$categories" \
	'tags:' "$tags" \
	'users:' "$users"

say "Seed complete: ${WP_URL}"
