#!/usr/bin/env bash
#
# Runs PHP_CodeSniffer over wp-content with the ruleset in phpcs.xml.
#
#   bin/lint.sh            reports
#   bin/lint.sh --fix      applies what phpcbf can fix on its own
#
# Extra arguments are passed through to phpcs.

# shellcheck source=bin/lib.sh
. "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

BINARY="phpcs"

if [ "${1:-}" = "--fix" ]; then
	BINARY="phpcbf"
	shift
fi

if [ ! -f "$REPO_ROOT/vendor/autoload.php" ]; then
	say "Installing the Composer dependencies"
	docker run --rm \
		-u "$(id -u):$(id -g)" \
		-e COMPOSER_HOME=/tmp/composer \
		-v "$REPO_ROOT":/app \
		-w /app \
		composer:2 install --no-interaction --no-progress
fi

say "Running ${BINARY}"
docker run --rm \
	-u "$(id -u):$(id -g)" \
	-v "$REPO_ROOT":/app \
	-w /app \
	php:8.2-cli \
	php "/app/vendor/bin/${BINARY}" "$@"
