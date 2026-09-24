.PHONY: setup seed bench test lint fix logs shell down clean

# Bring the stack up and install WordPress with the theme and plugin active.
setup:
	./bin/setup.sh

# Load the demo content: 12 sections, 40 topics, 4 users, 2000 stories.
seed:
	./bin/seed.sh

# Measure the home page: TTFB over 10 runs, query count and peak memory.
bench:
	./bin/bench.sh

# Run the PHPUnit suite inside the container.
test:
	./bin/test.sh

# Run PHP_CodeSniffer (WordPress-Extra + PHPCompatibilityWP).
lint:
	./bin/lint.sh

# Apply the fixes phpcbf can make on its own.
fix:
	./bin/lint.sh --fix

logs:
	docker compose logs -f wordpress

# Shell inside the WordPress container.
shell:
	docker compose exec wordpress bash

down:
	docker compose down

# Wipe everything: containers, database and uploads.
clean:
	docker compose down -v
