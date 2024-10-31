<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Section_Taxonomy extends Qode_Product_Bundles_For_WooCommerce_Framework_Section {

	public function add_row_element( $params ) {
		return false;
	}

	public function add_section_element( $params ) {

		if ( isset( $params['name'] ) && ! empty( $params['name'] ) ) {
			$params['type'] = 'taxonomy';
			$field          = new Qode_Product_Bundles_For_WooCommerce_Framework_Section_Taxonomy( $params );
			$this->add_child( $field );

			return $field;
		}

		return false;
	}

	public function add_repeater_element( $params ) {
		return false;
	}

	public function add_field_element( $params ) {
		$params['type']          = 'taxonomy';
		$params['default_value'] = isset( $params['default_value'] ) ? $params['default_value'] : '';
		qode_product_bundles_for_woocommerce_framework_get_framework_root()->get_taxonomy_options()->set_option( $params['name'], $params['default_value'], $params['field_type'] );
		parent::add_field_element( $params );
	}
}
