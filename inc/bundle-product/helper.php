<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_register_product_for_meta_options' ) ) {
	/**
	 * Function that register product post type for meta box options
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	function qode_product_bundles_for_woocommerce_register_product_for_meta_options( $post_types ) {
		$post_types[] = 'product';

		return $post_types;
	}

	add_filter( 'qode_product_bundles_for_woocommerce_filter_framework_meta_box_save', 'qode_product_bundles_for_woocommerce_register_product_for_meta_options' );
	add_filter( 'qode_product_bundles_for_woocommerce_filter_framework_meta_box_remove', 'qode_product_bundles_for_woocommerce_register_product_for_meta_options' );
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_woocommerce_global_product' ) ) {
	/**
	 * Function that return global WooCommerce object
	 *
	 * @return object
	 */
	function qode_product_bundles_for_woocommerce_get_woocommerce_global_product() {
		global $product;

		return $product;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_allowed_product_types' ) ) {
	/**
	 * Function that returns allowed product types as bundled items
	 *
	 * @return array
	 */
	function qode_product_bundles_for_woocommerce_get_allowed_product_types() {
		$types = array(
			'simple' => esc_html__( 'Simple', 'qode-product-bundles-for-woocommerce' ),
		);

		return (array) apply_filters( 'qode_product_bundles_for_woocommerce_filter_allowed_product_types', $types );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_original_product_id' ) ) {
	/**
	 * Get original product page ID if WPML plugin is installed
	 *
	 * @param int $item_id
	 *
	 * @return int
	 */
	function qode_product_bundles_for_woocommerce_get_original_product_id( $item_id ) {

		if ( ! empty( $item_id ) && qode_product_bundles_for_woocommerce_is_installed( 'wpml' ) ) {
			global $sitepress;

			if ( ! empty( $sitepress ) && ! empty( $sitepress->get_default_language() ) ) {
				$item_id = apply_filters( 'wpml_object_id', $item_id, 'product', true, $sitepress->get_default_language() );
			}
		}

		return (int) $item_id;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_bundled_item_thumbnail' ) ) {
	/**
	 * Function that return bundled product item thumbnail html
	 *
	 * @param int $product_thumbnail_id
	 * @param string $product_permalink
	 *
	 * @see wc_get_gallery_image_html
	 *
	 * @return string - which contains html content
	 */
	function qode_product_bundles_for_woocommerce_get_bundled_item_thumbnail( $product_thumbnail_id, $product_permalink ) {

		if ( $product_thumbnail_id ) {
			$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
			$thumbnail_size    = apply_filters(
				'woocommerce_gallery_thumbnail_size',
				array(
					$gallery_thumbnail['width'],
					$gallery_thumbnail['height'],
				)
			);
			$image_size        = apply_filters( 'woocommerce_gallery_image_size', 'woocommerce_single' );
			$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
			$thumbnail_src     = wp_get_attachment_image_src( $product_thumbnail_id, $thumbnail_size );
			$full_src          = wp_get_attachment_image_src( $product_thumbnail_id, $full_size );
			$alt_text          = trim( wp_strip_all_tags( get_post_meta( $product_thumbnail_id, '_wp_attachment_image_alt', true ) ) );
			$image             = wp_get_attachment_image(
				$product_thumbnail_id,
				$image_size,
				false,
				array(
					'title'                   => _wp_specialchars( get_post_field( 'post_title', $product_thumbnail_id ), ENT_QUOTES, 'UTF-8', true ),
					'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $product_thumbnail_id ), ENT_QUOTES, 'UTF-8', true ),
					'data-src'                => esc_url( $full_src[0] ),
					'data-large_image'        => esc_url( $full_src[0] ),
					'data-large_image_width'  => esc_attr( $full_src[1] ),
					'data-large_image_height' => esc_attr( $full_src[2] ),
					'class'                   => esc_attr( apply_filters( 'qode_product_bundles_for_woocommerce_bundled_item_image_class', implode( ' ', array( 'wp-post-image' ) ) ) ),
				)
			);

			// translators: 1: thumbnail image src; 2: thumbnail image alt text; 3: product link; 4: thumbnail image html.
			$thumbnail_image = sprintf(
				'<div data-thumb="%s" data-thumb-alt="%s" class="woocommerce-product-gallery__image"><a href="%s">%s</a></div>',
				esc_url( $thumbnail_src[0] ),
				esc_attr( $alt_text ),
				esc_url( $product_permalink ),
				$image
			);

		} else {
			// translators: 1: placeholder image; 2: placeholder image alt text.
			$thumbnail_image = sprintf(
				'<div class="woocommerce-product-gallery__image--placeholder"><img src="%1$s" alt="%2$s" class="wp-post-image" /></div>',
				esc_url( wc_placeholder_img_src() ),
				esc_html__( 'Awaiting product image', 'qode-product-bundles-for-woocommerce' )
			);
		}

		return $thumbnail_image;
	}
}
