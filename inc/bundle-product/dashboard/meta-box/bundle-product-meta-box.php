<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_add_bundle_product_meta_boxes' ) ) {
	/**
	 * Function that add general meta box options for this module
	 */
	function qode_product_bundles_for_woocommerce_add_bundle_product_meta_boxes() {
		$qode_framework = qode_product_bundles_for_woocommerce_framework_get_framework_root();

		$page = $qode_framework->add_options_page(
			array(
				'scope' => array( 'product' ),
				'type'  => 'meta',
				'slug'  => 'bundle-product',
				'title' => esc_html__( 'QODE - Bundle Product Options', 'qode-product-bundles-for-woocommerce' ),
			)
		);

		if ( $page ) {

			$general_section = $page->add_section_element(
				array(
					'name'  => 'qode_product_bundles_for_woocommerce_bundle_product_general_section',
					'title' => esc_html__( 'General Options', 'qode-product-bundles-for-woocommerce' ),
				)
			);

			// Hook to include additional options before general section.
			do_action( 'qode_product_bundles_for_woocommerce_action_bundle_product_meta_boxes_before_general', $general_section, $page );

			$general_section->add_field_element(
				array(
					'field_type'  => 'text',
					'name'        => 'qode_product_bundles_for_woocommerce_bundle_product_regular_price',
					// translators: %s is currency symbol.
					'title'       => sprintf( esc_html__( 'Regular Price (%s)', 'qode-product-bundles-for-woocommerce' ), get_woocommerce_currency_symbol() ),
					'description' => esc_html__( 'Set the regular price of this bundle.', 'qode-product-bundles-for-woocommerce' ),
					'dependency'  => array(
						'show' => array(
							'qode_product_bundles_for_woocommerce_bundle_product_price_type' => array(
								'values'        => 'fixed',
								'default_value' => 'fixed',
							),
						),
					),
				)
			);

			$general_section->add_field_element(
				array(
					'field_type'  => 'text',
					'name'        => 'qode_product_bundles_for_woocommerce_bundle_product_sale_price',
					// translators: %s is currency symbol.
					'title'       => sprintf( esc_html__( 'Sale Price (%s)', 'qode-product-bundles-for-woocommerce' ), get_woocommerce_currency_symbol() ),
					'description' => esc_html__( 'Set the optional sale price to show a discount for this bundle.', 'qode-product-bundles-for-woocommerce' ),
					'dependency'  => array(
						'show' => array(
							'qode_product_bundles_for_woocommerce_bundle_product_price_type' => array(
								'values'        => 'fixed',
								'default_value' => 'fixed',
							),
						),
					),
				)
			);

			// Hook to include additional options after general section.
			do_action( 'qode_product_bundles_for_woocommerce_action_bundle_product_meta_boxes_after_general', $general_section, $page );

			$page_repeater = $page->add_repeater_element(
				array(
					'name'        => 'qode_product_bundles_for_woocommerce_bundle_product_items',
					'title'       => esc_html__( 'Bundle Items', 'qode-product-bundles-for-woocommerce' ),
					'description' => esc_html__( 'Add products to the bundle', 'qode-product-bundles-for-woocommerce' ),
					'button_text' => esc_html__( 'Add New Item', 'qode-product-bundles-for-woocommerce' ),
				)
			);

			$cpt_additional_args = array(
				// phpcs:ignore WordPress.DB.SlowDBQuery
				'tax_query' => array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array_keys( qode_product_bundles_for_woocommerce_get_allowed_product_types() ),
					),
				),
			);

			$page_repeater->add_field_element(
				array(
					'field_type'  => 'select',
					'name'        => 'product_id',
					'title'       => esc_html__( 'Product', 'qode-product-bundles-for-woocommerce' ),
					'description' => esc_html__( 'Pick a product to add to this bundle.', 'qode-product-bundles-for-woocommerce' ),
					'options'     => qode_product_bundles_for_woocommerce_get_cpt_items( '', $cpt_additional_args ),
					'args'        => array(
						'custom_class' => 'qodef-full-info',
					),
				)
			);

			$page_repeater->add_field_element(
				array(
					'field_type'  => 'text',
					'name'        => 'quantity',
					'title'       => esc_html__( 'Quantity', 'qode-product-bundles-for-woocommerce' ),
					'description' => esc_html__( 'Set a quantity of the product for this bundle.', 'qode-product-bundles-for-woocommerce' ),
					'args'        => array(
						'custom_class' => 'qodef-full-info',
					),
				)
			);

			// Hook to include additional options as repeater item.
			do_action( 'qode_product_bundles_for_woocommerce_action_bundle_product_meta_boxes_repeater_items', $page_repeater );

			// Hook to include additional options after module options.
			do_action( 'qode_product_bundles_for_woocommerce_action_bundle_product_meta_boxes', $page );
		}
	}

	add_action( 'qode_product_bundles_for_woocommerce_action_default_meta_boxes_init', 'qode_product_bundles_for_woocommerce_add_bundle_product_meta_boxes' );
}
