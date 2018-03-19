<?php

namespace LodoPlaces\Admin;


class Sync {

	public function setup() {
		add_action( 'admin_menu', [ $this, 'sync_feed' ] );
		add_action( 'lodo_places_search_fields', [ $this, 'search_fields' ] );
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

		$field_data = $this->get_field_data();

		$url = add_query_arg( [
			'location' => $field_data['location'],
			'radius' => $field_data['radius'],
			'type' => $field_data['type'],
			'keyword' => $field_data['keyword'],
			//'pagetoken' => 'CqQCGwEAANMUOM3GrezyNU2rEs8Hja_Id_e3AQ4GSt7f0cdb9uVNvkJQrF1snLtjSl6uUMfnNl59tShdegT4vp0qp4Fr1Emkngn-dwPyEuwvFZ8Lts4vidGDi9bOFDYJGldMnOkbe-F9tpIF_4DswkqJd1qeDvZ1vLGccY30G5uKHFgUkf0EEUQ_mGExP5PjpUy9_vsEQj-qrBpEK5YgqUr0lkOqEVC4lGNjEzlSqNwma3vGN4pabp1yfk6DP0JLh5CzncE9UqCH9tPGAFOjHpgmtgQH28o_GMhO-3pw0zBE57FF0Gxx0qklu0mhrByc8av_s5Y49U3XMrfJk5QWaMOrXRUp2vEIM6aC-VvK9VyCAhJocm0g43vAh_jsBqi1HWN1ilmFNRIQLoJoFtq-sXfDDFqL8yAOqRoUBC4Q5RddQy8NDo1eUQICK62G6nY',
			'key' => $key,
		], 'https://maps.googleapis.com/maps/api/place/nearbysearch/json' );

		echo '<h1>Add Places from Google</h1>';

		$request = wp_remote_get( $url );

		if( is_wp_error( $request ) ) {
			return; // Bail early
		}

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body, true );

//		echo '<pre>';
//		print_r( $data );
//		echo '</pre>';

		$next_page = $data['next_page_token'];

		do_action( 'lodo_places_search_fields' );

		echo '<div class="lodo-places-holder">';

		foreach( $data['results'] as $element ) {

			echo '<div class="lodo-places-listing">';
				echo '<img src="' . esc_url( $element['icon'] ) . '">';
    			echo '<h3>' . esc_html( $element['name'] ) . '</h3>';
    			echo '<p>' . esc_html( $element['vicinity'] ) . '</p>';
    			echo '<div class="listing-data">';

    				if ( ! empty( $element['price_level'] ) ) {
    					echo '<div class="price-level">';
    					echo '<p>' . esc_html( $element['price_level'] ) . '</p>';
    					echo '</div>';
					}

					if ( ! empty( $element['rating'] ) ) {
    					echo '<div class="rating">';
						echo '<p>' . esc_html( $element['rating'] ) . '</p>';
    					echo '</div>';
					}

					echo '<a data-id="' . esc_attr( $element['id'] ) . '" class="button button-primary button-large">Import</a>';

    			echo '</div>';
			echo '</div>';

		}

		echo '</div>';

	}

	public function search_fields() {

		$field_data = $this->get_field_data();

		echo '<div class="lodo-places-listing-filters">';
			echo '<form method="get">';
				echo '<input type="text" name="latlong" placeholder="Latitude/Longitude" value="' . esc_html( $field_data['location'] ) . '">';
				echo '<input type="text" name="radius" placeholder="Radius" value="' . esc_html( $field_data['radius'] ) . '">';
				echo '<input type="text" name="type" placeholder="Type" value="' . esc_html( $field_data['type'] ) . '">';
				echo '<input type="text" name="keyword" placeholder="Keyword" value="' . esc_html( $field_data['keyword'] ) . '">';
				echo '<input type="hidden" name="page" value="sync-places">';
				echo '<input type="submit" class="button button-primary button-large" value="filter">';
			echo '</form>';
		echo '</div>';

	}

	private function get_field_data() {

		return [
			'location' => ( ! empty( $_GET['latlong'] ) ) ? sanitize_text_field( $_GET['latlong'] ) : '39.742043,-104.991531',
			'radius' => ( ! empty( $_GET['radius'] ) ) ? sanitize_text_field( $_GET['radius'] ) : '2400',
			'type' => ( ! empty( $_GET['type'] ) ) ? sanitize_text_field( $_GET['type'] ) : '',
			'keyword' => ( ! empty( $_GET['keyword'] ) ) ? sanitize_text_field( $_GET['keyword'] ) : '',
		];

	}

}

