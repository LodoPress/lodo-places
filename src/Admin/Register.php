<?php

namespace LodoPlaces\Admin;


class Register {

	public function setup() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets( $hook ) {

		if ( 'toplevel_page_sync-places' === $hook ) {
			wp_enqueue_style( 'lodo-places', LODO_PLACES_PLUGIN_URL . '/static/css/screen.min.css' );
		}

	}

}
