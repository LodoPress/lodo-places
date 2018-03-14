<?php

namespace LodoPlaces\Admin;


class Sync {

	public function setup() {
		add_action( 'init', [ $this, 'sync_feed' ] );
	}

	public function sync_feed() {
		if ( ! is_admin() ) {
			return;
		}

		add_menu_page(
			__( 'Sync Places', 'lodo-places' ),
			__( 'Sync Places', 'lodo-places' ),
			apply_filters( 'lodo_places_menu_capability', 'manage_options' ),
			'sync-places',
			[ $this, 'feed_markup' ],
			'dashicons-location',
			85
		);

	}

	public function feed_markup() {
		echo '<h2>Hello World</h2>';
	}

}