<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! class_exists( 'WC_Product_Qode_Bundle_Product' ) ) {
	/**
	 * Class WC_Product_Qode_Bundle_Product
	 */
	class WC_Product_Qode_Bundle_Product extends WC_Product {
		private $bundled_items;

		/**
		 * WC_Product_Qode_Bundle_Product
		 *
		 * @param int|WC_Product|object $product Product to init.
		 */
		public function __construct( $product ) {
			parent::__construct( $product );

			$get_bundle_product_meta = get_post_meta( $this->get_id(), 'qode_product_bundles_for_woocommerce_bundle_product_items', true );

			if ( ! empty( $get_bundle_product_meta ) ) {
				$this->set_bundle_items( $get_bundle_product_meta );
			}
		}

		/**
		 * Function that get bundled product type
		 *
		 * @return string
		 */
		public function get_type() {
			return 'qode_bundle_product';
		}

		/**
		 * Function that get bundle items
		 *
		 * @return array
		 */
		public function get_bundled_items() {
			return ! empty( $this->bundled_items ) ? $this->bundled_items : array();
		}

		/**
		 * Function that set bundle items
		 *
		 * @param array $bundled_items - bundle product items
		 *
		 * @return void
		 */
		private function set_bundle_items( $bundled_items ) {
			$is_virtual = true;

			foreach ( $bundled_items as $bundled_item_value ) {
				$bundled_item_id = qode_product_bundles_for_woocommerce_get_original_product_id( $bundled_item_value['product_id'] ?? 0 );
				$bundled_item    = wc_get_product( $bundled_item_id );

				if ( ! empty( $bundled_item ) ) {

					// Check is Simple product?
					if ( ! in_array( $bundled_item->get_type(), apply_filters( 'qode_product_bundles_for_woocommerce_filter_allowed_product_types_panel', array( 'simple' ) ), true ) ) {
						continue;
					}

					// Set/Cast product quantity value.
					$bundled_item_value['quantity'] = ! empty( $bundled_item_value['quantity'] ) ? absint( $bundled_item_value['quantity'] ) : 1;

					$this->bundled_items[ $bundled_item_id ] = apply_filters(
						'qode_product_bundles_for_woocommerce_filter_bundled_item',
						array(
							'product'     => $bundled_item,
							'product_map' => array_merge(
								$bundled_item_value,
								array(
									'bundle_id' => $this->get_id(),
									'type'      => $bundled_item->get_type(),
								)
							),
						),
						$bundled_item_id
					);

					if ( ! $bundled_item->is_virtual() ) {
						$is_virtual = false;
					}
				}
			}

			$this->set_virtual( $is_virtual );
		}

		/**
		 * Function that checks if all items are purchasable
		 *
		 * @return bool
		 */
		public function is_purchasable() {
			$purchasable = true;

			if ( ! $this->exists() ) {
				$purchasable = false;
			} elseif ( $this->get_price() === '' ) {
				$purchasable = false;

			} elseif ( $this->get_status() !== 'publish' && ! current_user_can( 'edit_post', $this->get_id() ) ) {
				$purchasable = false;
			}

			// Check bundle items are purchasable?
			$bundled_items = $this->get_bundled_items();
			foreach ( $bundled_items as $bundled_item_value ) {
				/**
				 * Bundled product item
				 *
				 * @var WC_Product $bundled_item
				 */
				$bundled_item = $bundled_item_value['product'];

				if ( ! $bundled_item->is_purchasable() ) {
					$purchasable = false;
				}
			}

			return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
		}

		/**
		 * Function that checks if one item at least is variable product
		 *
		 * @return bool
		 */
		public function has_variables() {
			return false;
		}

		/**
		 * Function that checks if all items are in stock
		 *
		 * @return bool
		 */
		public function all_items_in_stock() {
			$response = true;

			$bundled_items = $this->get_bundled_items();
			foreach ( $bundled_items as $bundled_item_value ) {
				/**
				 * Bundled product item
				 *
				 * @var WC_Product $bundled_item
				 */
				$bundled_item = $bundled_item_value['product'];

				if ( ! $bundled_item->is_in_stock() ) {
					$response = false;
					break;
				}
			}

			return $response;
		}

		/**
		 * Function that get the stock status
		 *
		 * @param string $context
		 *
		 * @return string
		 */
		public function get_stock_status( $context = 'view' ) {
			$status = parent::get_stock_status( $context );

			$manage_out_of_stock_option = qode_product_bundles_for_woocommerce_get_option_value( 'admin', 'qode_product_bundles_for_woocommerce_manage_bundled_items_out_of_stock' );

			if ( 'view' === $context && 'instock' === $status && 'out-of-stock' === $manage_out_of_stock_option && ! $this->all_items_in_stock() ) {
				return 'outofstock';
			}

			return $status;
		}

		/**
		 * Function that set out of stock status if items are out of stock and global option is set
		 *
		 * @param string $status
		 */
		public function set_stock_status( $status = 'instock' ) {
			$manage_out_of_stock_option = qode_product_bundles_for_woocommerce_get_option_value( 'admin', 'qode_product_bundles_for_woocommerce_manage_bundled_items_out_of_stock' );

			if ( 'instock' === $status && 'out-of-stock' === $manage_out_of_stock_option && ! $this->all_items_in_stock() ) {
				$status = 'outofstock';
			}

			parent::set_stock_status( $status );
		}

		/**
		 * Function that returns whether the bundled product is visible in the catalog
		 *
		 * @return bool
		 */
		public function is_visible() {
			$visible = parent::is_visible();

			$manage_out_of_stock_option = qode_product_bundles_for_woocommerce_get_option_value( 'admin', 'qode_product_bundles_for_woocommerce_manage_bundled_items_out_of_stock' );

			if ( $visible && 'hide' === $manage_out_of_stock_option && ! $this->all_items_in_stock() ) {
				$visible = false;
			}

			return apply_filters( 'woocommerce_product_is_visible', $visible, $this->get_id() );
		}

		/**
		 * Function that get the add to cart url for products in loop
		 *
		 * @return string
		 */
		public function add_to_cart_url() {
			$url = $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() );

			return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
		}

		/**
		 * Function that get the add to cart button text
		 *
		 * @return string
		 */
		public function add_to_cart_text() {
			$text = $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock() ? esc_html__( 'Add to cart', 'qode-product-bundles-for-woocommerce' ) : esc_html__( 'Read more', 'qode-product-bundles-for-woocommerce' );

			return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
		}

		/**
		 * Function that get the product title
		 *
		 * @return string
		 */
		public function get_title() {
			$title = get_the_title( $this->get_id() );

			if ( $this->get_parent_id() > 0 ) {
				$title = get_the_title( $this->get_parent_id() ) . esc_html__( ' - ', 'qode-product-bundles-for-woocommerce' ) . $title;
			}

			return apply_filters( 'woocommerce_product_title', $title, $this );
		}
	}
}
