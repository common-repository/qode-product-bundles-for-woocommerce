<?php
/*
Plugin Name: QODE Product Bundles for WooCommerce
Description: Boost conversion rates, create extra value deals and run cross-selling campaigns by combining two or more products in practical product bundles.
Author: Qode Interactive
Author URI: https://qodeinteractive.com/
Plugin URI: https://qodeinteractive.com/qode-product-bundles-for-woocommerce/
Version: 1.0
Requires at least: 6.3
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: qode-product-bundles-for-woocommerce
*/

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! class_exists( 'Qode_Product_Bundles_For_WooCommerce' ) ) {
	class Qode_Product_Bundles_For_WooCommerce {
		private static $instance;

		public function __construct() {
			// Set the main plugins constants.
			define( 'QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_PLUGIN_BASE_FILE', plugin_basename( __FILE__ ) );

			// Include required files.
			require_once __DIR__ . '/constants.php';
			require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ABS_PATH . '/helpers/helper.php';

			// Include framework file.
			require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/class-qode-product-bundles-for-woocommerce-framework.php';

			// Check if WooCommerce is installed.
			if ( function_exists( 'WC' ) ) {

				// Make plugin available for translation (permission 15 is set in order to be after the plugin initialization).
				add_action( 'plugins_loaded', array( $this, 'load_plugin_text_domain' ), 15 );

				// Add plugin's body classes.
				add_filter( 'body_class', array( $this, 'add_body_classes' ) );

				// Enqueue plugin's assets.
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

				// Include plugin's modules.
				$this->include_modules();
			}
		}

		/**
		 * Instance of module class
		 *
		 * @return Qode_Product_Bundles_For_WooCommerce
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function load_plugin_text_domain() {
			// Make plugin available for translation.
			load_plugin_textdomain( 'qode-product-bundles-for-woocommerce', false, QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_REL_PATH . '/languages' );
		}

		public function add_body_classes( $classes ) {
			$classes[] = 'qode-product-bundles-for-woocommerce-' . QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_VERSION;

			if ( wp_is_mobile() ) {
				$classes[] = 'qpbfw--touch';
			} else {
				$classes[] = 'qpbfw--no-touch';
			}

			return $classes;
		}

		public function enqueue_assets() {
			// Enqueue CSS styles.
			wp_enqueue_style( 'qode-product-bundles-for-woocommerce-main', QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ASSETS_URL_PATH . '/css/main.css', array(), QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_VERSION );
		}

		public function include_modules() {
			// Hook to include additional element before modules inclusion.
			do_action( 'qode_product_bundles_for_woocommerce_action_before_include_modules' );

			foreach ( glob( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_PATH . '/*/include.php' ) as $module ) {
				include_once $module;
			}

			// Hook to include additional element after modules inclusion.
			do_action( 'qode_product_bundles_for_woocommerce_action_after_include_modules' );
		}
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_init_plugin' ) ) {
	/**
	 * Function that init plugin activation
	 */
	function qode_product_bundles_for_woocommerce_init_plugin() {
		Qode_Product_Bundles_For_WooCommerce::get_instance();
	}

	add_action( 'plugins_loaded', 'qode_product_bundles_for_woocommerce_init_plugin' );
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_activation_trigger' ) ) {
	/**
	 * Function that trigger hooks on plugin activation
	 */
	function qode_product_bundles_for_woocommerce_activation_trigger() {
		// Hook to add additional code on plugin activation.
		do_action( 'qode_product_bundles_for_woocommerce_action_on_activation' );
	}

	register_activation_hook( __FILE__, 'qode_product_bundles_for_woocommerce_activation_trigger' );
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_deactivation_trigger' ) ) {
	/**
	 * Function that trigger hooks on plugin deactivation
	 */
	function qode_product_bundles_for_woocommerce_deactivation_trigger() {
		// Hook to add additional code on plugin deactivation.
		do_action( 'qode_product_bundles_for_woocommerce_action_on_deactivation' );
	}

	register_deactivation_hook( __FILE__, 'qode_product_bundles_for_woocommerce_deactivation_trigger' );
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_check_requirements' ) ) {
	/**
	 * Function that check plugin requirements
	 */
	function qode_product_bundles_for_woocommerce_check_requirements() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'qode_product_bundles_for_woocommerce_admin_notice_content' );
		}
	}

	add_action( 'plugins_loaded', 'qode_product_bundles_for_woocommerce_check_requirements' );
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_admin_notice_content' ) ) {
	/**
	 * Function that display the error message if the requirements are not met
	 */
	function qode_product_bundles_for_woocommerce_admin_notice_content() {
		printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'WooCommerce plugin is required for QODE Product Bundles for WooCommerce plugin to work properly. Please install/activate it first.', 'qode-product-bundles-for-woocommerce' ) );
	}
}
