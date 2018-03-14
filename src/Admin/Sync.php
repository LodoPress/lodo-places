<?php

namespace LodoPlaces\Admin;


class Sync {

	public function setup() {
		add_action( 'admin_menu', [ $this, 'sync_feed' ] );
	}

	public function sync_feed() {
		if ( ! is_admin() ) {
			return;
		}

		\add_menu_page(
			__( 'Sync Places', 'lodo-places' ),
			__( 'Sync Places', 'lodo-places' ),
			apply_filters( 'lodo_places_menu_capability', 'manage_options' ),
			'sync-places',
			[ $this, 'feed_markup' ],
			'dashicons-location',
			85
		);

	}

	// GET GOOGLE PLACES API RESULTS //

	public function feed_markup() {

		if ( defined( 'LODO_PLACES_API_KEY' ) ) {
			$key = LODO_PLACES_API_KEY;
		} else {
			$key = '';
		}

		$url = add_query_arg( [
			'location' => '-33.8670522,151.1957362',
			'radius' => '24000',
			'type' => 'restaurant',
			'key' => $key,
		], 'https://maps.googleapis.com/maps/api/place/nearbysearch/json' );

		echo '<h1>Add Places from Google</h1>';

		$request = wp_remote_get( $url );

		if( is_wp_error( $request ) ) {
			return false; // Bail early
		}

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body, true );

		foreach($data['results'] as $element) {
			echo '<img src="' . $element['icon'] . '">';
    	echo '<h3>' . $element['name'] . '</h3>';

		}
	}
}

