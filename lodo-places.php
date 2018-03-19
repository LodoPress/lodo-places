<?php
/**
 * Plugin Name:     LodoPlaces
 * Description:     Google Places API Integration
 * Author:          LodoPress, Ryan Kanner, Taylor Hansen
 * Text Domain:     lodo-places
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Lodo_Places
 */

// ensure the wp environment is loaded properly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LodoPlaces' ) ) {

	class LodoPlaces {

		/**
		 * Stores the instance of the LodoPlaces class
		 *
		 * @var Object $instance
		 * @access private
		 */
		private static $instance;

		/**
		 * Retrieves the instance of the LodoPlaces class
		 *
		 * @access public
		 * @return Object|LodoPlaces
		 */
		public static function instance() {

			/**
			 * Make sure we are only instantiating the class once
			 */
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof LodoPlaces ) ) {
				self::$instance = new LodoPlaces();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->run();
			}

			/**
			 * Action that fires after we are done setting things up in the plugin. Extensions of
			 * this plugin should instantiate themselves on this hook to make sure the framework
			 * is available before they do anything.
			 *
			 * @param object $instance Instance of the current LodoPlaces class
			 */
			do_action( 'lodo_places_init', self::$instance );

			return self::$instance;

		}

		/**
		 * Sets up the constants for the plugin to use
		 *
		 * @access private
		 * @return void
		 */
		public static function setup_constants() {

			// Plugin version.
			if ( ! defined( 'LODO_PLACES_VERSION' ) ) {
				define( 'LODO_PLACES_VERSION', '0.1.0' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'LODO_PLACES_PLUGIN_DIR' ) ) {
				define( 'LODO_PLACES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'LODO_PLACES_PLUGIN_URL' ) ) {
				define( 'LODO_PLACES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'LODO_PLACES_PLUGIN_FILE' ) ) {
				define( 'LODO_PLACES_PLUGIN_FILE', __FILE__ );
			}

		}

		/**
		 * Load the autoloaded files as well as the access functions
		 *
		 * @access private
		 * @return void
		 * @throws Exception
		 */
		private function includes() {

			if ( file_exists( LODO_PLACES_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
				require_once( LODO_PLACES_PLUGIN_DIR . 'vendor/autoload.php' );
			} else {
				throw new Exception( __( 'The autoloader could not be found, send help!', 'lodo-places' ) );
			}

		}

		/**
		 * Instantiate the main classes we need for the plugin
		 *
		 * @access private
		 * @return void
		 */
		private function run() {

			$admin_sync = new \LodoPlaces\Admin\Sync();
			$admin_sync->setup();

			$admin_register = new \LodoPlaces\Admin\Register();
			$admin_register->setup();

		}

	}

}

/**
 * Function to instantiate the LodoPlaces plugin
 *
 * @return object|LodoPlaces Instance of the LodoPlaces object
 * @access public
 */
function lodo_places_init() {

	return LodoPlaces::instance();

}

/**
 * Hook into the after_setup_theme hook to instantiate the plugin
 */
add_action( 'after_setup_theme', 'lodo_places_init' );


