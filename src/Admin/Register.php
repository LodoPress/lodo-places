<?php

namespace LodoPlaces\Admin;


class Register {

	public function setup() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets( $hook ) {

		if ( 'toplevel_page_sync-places' === $hook ) {

			wp_enqueue_style( 'lodo-places', LODO_PLACES_PLUGIN_URL . '/static/css/screen.min.css' );
			wp_enqueue_script( 'lodo-places', LODO_PLACES_PLUGIN_URL . '/static/js/app.min.js', [ 'jquery' ], false, true );
			wp_localize_script(
				'lodo-places',
				'LODO_PLACES_SYNC',
				[
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				]
			);

		}

	}

}
