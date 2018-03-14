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
		echo '<h1>Add Places from Google</h1>'; 
		$request = wp_remote_get( 'https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJux94CcN4bIcRcH7lFkSAUfo&key=AIzaSyDvo1ivHfHM2yrtInb2NrqcAKiRcsZhUkg' );

		if( is_wp_error( $request ) ) {
			return false; // Bail early
		}

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body );

		echo '<pre>';
		print_r( $data );
		echo '</pre>';
	}

}

