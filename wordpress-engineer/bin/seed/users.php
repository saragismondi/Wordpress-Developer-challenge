<?php
/**
 * Seed step 2: the editorial staff.
 *
 * Run with: wp eval-file bin/seed/users.php
 * Idempotent: an existing login is updated, never duplicated.
 *
 * @package AgroNews_Seed
 */

require_once __DIR__ . '/data.php';

$created = 0;
$updated = 0;

foreach ( agronews_seed_users() as $definition ) {
	$user = get_user_by( 'login', $definition['user_login'] );

	$fields = array(
		'display_name' => $definition['display_name'],
		'first_name'   => $definition['first_name'],
		'last_name'    => $definition['last_name'],
		'description'  => $definition['description'],
		'role'         => $definition['role'],
	);

	if ( $user instanceof WP_User ) {
		$fields['ID'] = $user->ID;

		// Keep the administrator role of the account setup.sh created.
		if ( user_can( $user, 'manage_options' ) ) {
			unset( $fields['role'] );
		}

		$user_id = wp_update_user( $fields );
		++$updated;
	} else {
		$fields['user_login'] = $definition['user_login'];
		$fields['user_email'] = $definition['user_login'] . '@agronews.example';
		$fields['user_pass']  = 'agronews';
		$fields['user_url']   = 'https://www.agronews.example';

		$user_id = wp_insert_user( $fields );
		++$created;
	}

	if ( is_wp_error( $user_id ) ) {
		WP_CLI::warning( sprintf( 'User %s: %s', $definition['user_login'], $user_id->get_error_message() ) );

		continue;
	}

	foreach ( $definition['social'] as $meta_key => $url ) {
		update_user_meta( $user_id, $meta_key, $url );
	}
}

WP_CLI::success( sprintf( 'Users ready: %d created, %d updated.', $created, $updated ) );
