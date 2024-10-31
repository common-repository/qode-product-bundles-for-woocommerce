<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

include_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_PATH . '/bundle-product/helper.php';
include_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_PATH . '/bundle-product/class-qode-product-bundles-for-woocommerce-bundle-product.php';

foreach ( glob( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_PATH . '/bundle-product/dashboard/*/*.php' ) as $option ) {
	include_once $option;
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_include_main_bundle_product_class' ) ) {
	/**
	 * Function that include Main WooCommerce product class for Bundled type
	 */
	function qode_product_bundles_for_woocommerce_include_main_bundle_product_class() {
		include_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_PATH . '/bundle-product/woocommerce/class-wc-product-qode-bundle-product.php';
	}

	// permission 15 is set in order to be able to override it inside premium plugin.
	add_action( 'plugins_loaded', 'qode_product_bundles_for_woocommerce_include_main_bundle_product_class', 15 );
}
