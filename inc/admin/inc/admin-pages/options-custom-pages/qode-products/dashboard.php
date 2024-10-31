<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_premium_add_qode_products_custom_options_page' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function qode_product_bundles_for_woocommerce_premium_add_qode_products_custom_options_page() {
		$custom_page = Qode_Product_Bundles_For_WooCommerce_Admin_Options_Custom_Page_Handler::get_instance();

		$custom_page->add_page(
			array(
				'slug'     => 'qode-products',
				'title'    => esc_html__( 'Qode Products', 'qode-product-bundles-for-woocommerce' ),
				'position' => 1,
				'script'   => false,
				'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" width="17.3" height="16.3" viewBox="0 0 17.3 16.3"><path d="M8,0l2.4,5.036,5.6.694L11.883,9.536,12.944,15,8,12.317,3.056,15,4.117,9.536,0,5.729l5.6-.694Z" transform="translate(0.65 0.65)" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3"/></svg>',
			)
		);
	}

	add_action( 'init', 'qode_product_bundles_for_woocommerce_premium_add_qode_products_custom_options_page', 9 );
}
