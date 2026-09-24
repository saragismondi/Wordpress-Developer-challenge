# What this changes

<!-- One paragraph. What the reviewer is looking at and why. -->

Closes #

## How to verify

<!-- The exact steps a reviewer runs. Include the URL and what they should see. -->

1. `bin/setup.sh && bin/seed.sh`
2. Open
3. Expected

## Checklist

### Security

- [ ] Every form and admin action verifies a nonce (`wp_nonce_field` / `check_admin_referer` / `wp_verify_nonce`).
- [ ] Every admin action checks a capability (`current_user_can`), never just `is_admin()` or the role name.
- [ ] Every input is sanitized on the way in (`sanitize_*`, `absint`, an allow-list).
- [ ] Every output is escaped on the way out (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`), at the point of output.
- [ ] Any SQL is built with `$wpdb->prepare()`. No user input is interpolated into a query.
- [ ] No secrets, tokens or credentials in the diff.

### WordPress

- [ ] Scripts and styles go through `wp_enqueue_*` with explicit dependencies and a version. No inline `<script>` or `<link>` in templates.
- [ ] Queries use `WP_Query` with `no_found_rows` where there is no pagination, and prime the caches they need (`update_post_thumbnail_cache`).
- [ ] No query runs inside a loop over posts.
- [ ] `wp_reset_postdata()` after every secondary loop.
- [ ] Options, meta keys and function names carry the project prefix (`agronews_`, `an_`).
- [ ] Strings are translatable, with the right text domain.

### Quality

- [ ] `bin/lint.sh` passes with zero errors and zero warnings.
- [ ] `bin/test.sh` passes, and the change is covered by a test.
- [ ] `bin/bench.sh` was run: the home page TTFB and query count did not regress.
- [ ] No debugging leftovers (`var_dump`, `error_log`, commented-out code).

## Bench before / after

<!-- Paste the relevant lines of bin/bench.sh when the change can affect the front end. -->

| | TTFB median | Queries | Peak memory |
| --- | --- | --- | --- |
| before | | | |
| after | | | |
