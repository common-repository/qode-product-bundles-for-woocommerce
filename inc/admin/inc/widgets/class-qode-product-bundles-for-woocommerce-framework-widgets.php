<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Widgets {
	private $widgets;

	public function __construct() {
		$this->widgets = array();

		add_action( 'widgets_init', array( $this, 'register' ) );

		// 5 is set to be same permission as Gutenberg plugin have.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_framework_scripts' ), 5 );
	}

	public function get_widgets() {
		return $this->widgets;
	}

	public function get_widget( $base ) {
		return $this->widgets[ $base ];
	}

	private function set_widget( Qode_Product_Bundles_For_WooCommerce_Framework_Widget $widget ) {
		$key                   = $widget->get_base();
		$this->widgets[ $key ] = $widget;
	}

	public function widget_exists( $base ) {
		return array_key_exists( $base, $this->widgets );
	}

	public function add_widget( Qode_Product_Bundles_For_WooCommerce_Framework_Widget $widget ) {
		if ( '' !== $widget->get_base() ) {
			$this->set_widget( $widget );

			return $widget;
		}

		return false;
	}

	public function register() {
		do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_widgets_register' );

		foreach ( $this->widgets as $widget ) {
			$widget->register();
		}

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_widgets_register' );
	}

	public function enqueue_dashboard_framework_scripts( $hook ) {
		if ( 'widgets.php' === $hook ) {
			// Widgets css scripts.
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters
			wp_enqueue_style( 'qode-product-bundles-for-woocommerce-framework-widgets', QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_URL_PATH . '/inc/widgets/assets/css/widgets.css' );
			wp_enqueue_style( 'wp-color-picker' );

			// Widgets js scripts.
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker-alpha' );

			// Main dashboard js scripts.
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters
			wp_enqueue_script( 'qode-product-bundles-for-woocommerce-framework-script', QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_URL_PATH . '/inc/common/assets/js/common.min.js', array( 'jquery' ), false, true );
		}
	}
}
