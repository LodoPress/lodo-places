<?php

namespace LodoPlaces\Admin;


class Sync {

	public function setup() {
		add_action( 'admin_menu', [ $this, 'sync_feed' ] );
		add_action( 'lodo_places_search_fields', [ $this, 'search_fields' ] );
		add_action( 'wp_ajax_lodo_places_import_location', [ $this, 'import_place' ] );
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

		$field_data = $this->get_field_data();

		$url = add_query_arg( [
			'location' => $field_data['location'],
			'radius' => $field_data['radius'],
			'type' => $field_data['type'],
			'keyword' => $field_data['keyword'],
			//'pagetoken' => 'CqQCGwEAANMUOM3GrezyNU2rEs8Hja_Id_e3AQ4GSt7f0cdb9uVNvkJQrF1snLtjSl6uUMfnNl59tShdegT4vp0qp4Fr1Emkngn-dwPyEuwvFZ8Lts4vidGDi9bOFDYJGldMnOkbe-F9tpIF_4DswkqJd1qeDvZ1vLGccY30G5uKHFgUkf0EEUQ_mGExP5PjpUy9_vsEQj-qrBpEK5YgqUr0lkOqEVC4lGNjEzlSqNwma3vGN4pabp1yfk6DP0JLh5CzncE9UqCH9tPGAFOjHpgmtgQH28o_GMhO-3pw0zBE57FF0Gxx0qklu0mhrByc8av_s5Y49U3XMrfJk5QWaMOrXRUp2vEIM6aC-VvK9VyCAhJocm0g43vAh_jsBqi1HWN1ilmFNRIQLoJoFtq-sXfDDFqL8yAOqRoUBC4Q5RddQy8NDo1eUQICK62G6nY',
			'key' => $this->get_key(),
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

		echo '<pre>';
		print_r( get_option( '_test_place_id' ) );
		echo '</pre>';

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

					echo '<a data-id="' . esc_attr( $element['place_id'] ) . '" class="button button-primary button-large js-location-import-button">Import</a>';

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

	private function get_key() {
		return ( defined( 'LODO_PLACES_API_KEY' ) ) ? LODO_PLACES_API_KEY : '';
	}

	public function import_place() {

		$id = ( isset( $_GET['id'] ) ) ? sanitize_text_field( $_GET['id'] ) : '';

		if ( ! empty( $id ) ) {

			$url = add_query_arg( [
				'key' => $this->get_key(),
				'placeid' => $id,
			], 'https://maps.googleapis.com/maps/api/place/details/json' );

			$request = wp_remote_get( $url );

			if ( is_wp_error( $request ) ) {
				return; // Bail early
			}

			$body = wp_remote_retrieve_body( $request );

			$data = json_decode( $body, true );

			if ( empty( $data['result'] ) ) {
				return;
			}

			$data = $data['result'];
			update_option( '_test_place_id', $data, false );

			/**
			 * @TODO: Check to make sure this place doesn't exist already by checking for the place ID in meta
			 */

			$meta_data = [
				'_job_location' => ( ! empty( $data['formatted_address'] ) ) ? sanitize_text_field( $data['formatted_address'] ) : '',
				'_job_title' => ( ! empty( $data['name'] ) ) ? $data['name'] : '',
				'_job_phone' => ( ! empty( $data['formatted_phone_number'] ) ) ? sanitize_text_field( $data['formatted_phone_number'] ) : '',
				'_job_website' => ( ! empty( $data['website'] ) ) ? sanitize_text_field( $data['website'] ) : '',
				'_case27_listing_type' => apply_filters( 'lodo_places_sync_default_listing_type', 'place', $data, $id ),
				'_work_hours' => $this->get_hour_data( $data['opening_hours']['periods'] ),
				'_google_places_id' => $id,
			];

			if ( ! empty( $data['price_level'] ) ) {
				$meta_data['_price_range'] = str_repeat( '$', absint( $data['price_level'] ) );
			}

			$geo_data = $this->get_geo_data( $data );
			if ( ! empty( $geo_data ) ) {
				$meta_data = array_merge( $meta_data, $geo_data );
			}

			$post_id = wp_insert_post( [
				'post_title' => ( ! empty( $data['name'] ) ) ? sanitize_text_field( $data['name'] ) : '',
				'post_status' => apply_filters( 'lodo_places_sync_default_post_status', 'draft', $data, $id ),
				'post_type' => apply_filters( 'lodo_places_sync_default_post_type', 'job_listing', $data, $id ),
				'meta_input' => $meta_data,
			] );

			update_option( '_test_ajax_import', $post_id, false );

		}

		die();

	}

	private function get_geo_data( $data ) {

		$clean_data = [];

		if ( ! empty( $data['geometry']['location'] ) ) {
			$clean_data['geolocation_lat'] = ( ! empty( $data['geometry']['location']['lat'] ) ) ? sanitize_text_field( $data['geometry']['location']['lat'] ) : '';
			$clean_data['geolocation_long'] = ( ! empty( $data['geometry']['location']['lng'] ) ) ? sanitize_text_field( $data['geometry']['location']['lng'] ) : '';
		}

		if ( ! empty( $data['formatted_address'] ) ) {
			$clean_data['geolocation_formatted_address'] = sanitize_text_field( $data['formatted_address'] );
		}

		if ( ! empty( $data['address_components'] ) && is_array( $data['address_components'] ) ) {
			$address_data = [];
			foreach ( $data['address_components'] as $address_component ) {
				$address_data[ $address_component['types'][0] ] = [
					'long_name' => $address_component['long_name'],
					'short_name' => $address_component['short_name'],
				];
			}
		}

		if ( ! empty( $address_data ) ) {

			if ( ! empty( $address_data['street_number'] ) ) {
				$clean_data['geolocation_street_number'] = sanitize_text_field( $address_data['street_number']['long_name'] );
			}

			if ( ! empty( $address_data['route'] ) ) {
				$clean_data['geolocation_street'] = sanitize_text_field( $address_data['route']['long_name'] );
			}

			if ( ! empty( $address_data['neighborhood'] ) ) {
				$clean_data['geolocation_neighborhood'] = sanitize_text_field( $address_data['neighborhood']['long_name'] );
			}

			if ( ! empty( $address_data['locality'] ) ) {
				$clean_data['geolocation_city'] = sanitize_text_field( $address_data['locality']['long_name'] );
			}

			if ( ! empty( $address_data['administrative_area_level_1'] ) ) {
				$clean_data['geolocation_state_short'] = sanitize_text_field( $address_data['administrative_area_level_1']['short_name'] );
				$clean_data['geolocation_state_long'] = sanitize_text_field( $address_data['administrative_area_level_1']['long_name'] );
			}

			if ( ! empty( $address_data['postal_code'] ) ) {
				$clean_data['geolocation_postcode'] = sanitize_text_field( $address_data['postal_code']['long_name'] );
			}

			if ( ! empty( $address_data['country'] ) ) {
				$clean_data['geolocation_country_short'] = sanitize_text_field( $address_data['country'['short_name'] ] );
				$clean_data['geolocation_country_long'] = sanitize_text_field( $address_data['country']['long_name'] );
			}

			$clean_data['geolocated'] = '1';

		}

		return $clean_data;

	}

	public function get_hour_data( $data ) {

		$clean_data = [];

		if ( empty( $data ) || ! is_array( $data ) ) {
			return $clean_data;
		}

		$key_map = [
			0 => 'Monday',
			1 => 'Tuesday',
			2 => 'Wednesday',
			3 => 'Thursday',
			4 => 'Friday',
			5 => 'Saturday',
			6 => 'Sunday',
		];

		foreach ( $data as $key => $hours ) {
			if ( ! empty( $hours['open']['time'] ) && ! empty( $hours['close']['time'] ) ) {
				$clean_data[ $key_map[ $key ] ] = [
					'status' => 'enter-hours',
					[
						'from' => substr( $hours['open']['time'], 0, 2 ) . ':' . substr( $hours['open']['time'], 2 ),
						'to' => substr( $hours['close']['time'], 0, 2 ) . ':' . substr( $hours['close']['time'], 2 ),
					]
				];
			} else {
				$clean_data[ $key_map[ $key ] ] = [
					'status' => 'closed-all-day',
					[
						'from' => '',
						'to' => '',
					]
				];
			}
		}

		$clean_data['timezone'] = 'America/Denver';
		return $clean_data;

	}

}

