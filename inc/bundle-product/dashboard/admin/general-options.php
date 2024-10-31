<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_add_general_options' ) ) {
	/**
	 * Function that add general options for this module
	 *
	 * @param Qode_Product_Bundles_For_WooCommerce_Framework_Page_Admin $page
	 */
	function qode_product_bundles_for_woocommerce_add_general_options( $page ) {

		if ( $page ) {

			$welcome_section = $page->add_section_element(
				array(
					'layout'      => 'welcome',
					'name'        => 'qode_product_bundles_for_woocommerce_global_plugins_options_welcome_section',
					'title'       => esc_html__( 'Welcome to Qode Product Bundles for WooCommerce', 'qode-product-bundles-for-woocommerce' ),
					'description' => esc_html__( 'It\'s time to set up the Product Bundles feature on your website', 'qode-product-bundles-for-woocommerce' ),
					'icon'        => QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ASSETS_URL_PATH . '/img/icon.png',
				)
			);

			$general_section = $page->add_section_element(
				array(
					'name'  => 'qode_product_bundles_for_woocommerce_general_row',
					'title' => esc_html__( 'General', 'qode-product-bundles-for-woocommerce' ),
				)
			);

			$general_section->add_field_element(
				array(
					'field_type'    => 'radio',
					'name'          => 'qode_product_bundles_for_woocommerce_manage_bundled_items_out_of_stock',
					'title'         => esc_html__( 'Manage Bundled Items Out of Stock', 'qode-product-bundles-for-woocommerce' ),
					'description'   => esc_html__( 'Choose how to manage bundle product when an item in the bundle is out of stock.', 'qode-product-bundles-for-woocommerce' ),
					'options'       => array(
						'show'         => esc_html__( 'Show, but users will not be able to buy it', 'qode-product-bundles-for-woocommerce' ),
						'out-of-stock' => esc_html__( 'Set the bundled product as Out of Stock', 'qode-product-bundles-for-woocommerce' ),
						'hide'         => esc_html__( 'Hide the bundled product', 'qode-product-bundles-for-woocommerce' ),
					),
					'default_value' => 'show',
				)
			);

			// Hook to include additional options after module options.
			do_action( 'qode_product_bundles_for_woocommerce_action_after_general_options_map', $page, $general_section );
		}
	}

	add_action( 'qode_product_bundles_for_woocommerce_action_default_options_init', 'qode_product_bundles_for_woocommerce_add_general_options' );
}
