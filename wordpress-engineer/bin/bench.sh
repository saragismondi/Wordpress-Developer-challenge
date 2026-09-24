#!/usr/bin/env bash
#
# Measures the home page: time to first byte over 10 runs (median and p95),
# plus the query count and the peak memory of the last run, read from the
# X-AN-* headers the an-bench mu-plugin emits.
#
# Usage: bin/bench.sh [url] [runs]

# shellcheck source=bin/lib.sh
. "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

TARGET_URL="${1:-$WP_URL/}"
RUNS="${2:-10}"
WARMUPS=2

HEADER_FILE="$(mktemp)"
trap 'rm -f "$HEADER_FILE"' EXIT

if ! curl -fsS -o /dev/null "$TARGET_URL"; then
	echo "Cannot reach ${TARGET_URL}. Is the stack up? Run bin/setup.sh." >&2
	exit 1
fi

say "Benchmarking ${TARGET_URL}"

# The first hits populate the OPcache and the MySQL buffer pool. Measuring
# them would report the cost of a cold container, not of the page.
for _ in $(seq 1 "$WARMUPS"); do
	curl -fsS -o /dev/null "$TARGET_URL" >/dev/null
done

times=()

for run in $(seq 1 "$RUNS"); do
	ttfb="$(curl -fsS -o /dev/null -D "$HEADER_FILE" \
		-w '%{time_starttransfer}' \
		-H 'Cache-Control: no-cache' \
		"$TARGET_URL")"

	times+=("$ttfb")
	printf '  run %2d: %6.1f ms\n' "$run" "$(echo "$ttfb * 1000" | bc -l)"
done

header_value() {
	grep -i "^$1:" "$HEADER_FILE" | tail -1 | cut -d' ' -f2- | tr -d '\r'
}

queries="$(header_value 'X-AN-Queries')"
query_time="$(header_value 'X-AN-Query-Time')"
memory="$(header_value 'X-AN-Memory')"
savequeries="$(header_value 'X-AN-Savequeries')"
template="$(header_value 'X-AN-Template')"
size="$(curl -fsS -o /dev/null -w '%{size_download}' "$TARGET_URL")"

stats="$(printf '%s\n' "${times[@]}" | sort -g | awk '
	{ values[NR] = $1 }
	END {
		n = NR
		if (n == 0) { exit 1 }

		# Median: average of the two middle values on an even sample.
		if (n % 2) {
			median = values[(n + 1) / 2]
		} else {
			median = (values[n / 2] + values[n / 2 + 1]) / 2
		}

		# p95 by nearest rank.
		rank = int(0.95 * n)
		if (0.95 * n > rank) { rank++ }
		if (rank < 1) { rank = 1 }

		printf "%.1f %.1f %.1f %.1f", median * 1000, values[rank] * 1000, values[1] * 1000, values[n] * 1000
	}
')"

read -r median p95 fastest slowest <<<"$stats"

if [ -z "$queries" ]; then
	warn "No X-AN-* headers in the response: the bench mu-plugin is off."
	warn "Set AN_BENCH=1 in .env and recreate the stack: docker compose up -d"
fi

cat <<EOF

  AgroNews bench — $(date '+%Y-%m-%d %H:%M:%S')
  ------------------------------------------------------------
  URL                ${TARGET_URL}
  Template           ${template:-n/a}
  Runs               ${RUNS} (after ${WARMUPS} warm-up requests)

  TTFB median        ${median} ms
  TTFB p95           ${p95} ms
  TTFB fastest       ${fastest} ms
  TTFB slowest       ${slowest} ms

  Queries            ${queries:-n/a}
  Query time         ${query_time:-n/a} ms
  Peak memory        ${memory:-n/a} MB
  Response size      $(( size / 1024 )) KB
  SAVEQUERIES        ${savequeries:-0}
  ------------------------------------------------------------

EOF
