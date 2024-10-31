<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! class_exists( 'Qode_Product_Bundles_For_WooCommerce_Bundle_Product' ) ) {
	class Qode_Product_Bundles_For_WooCommerce_Bundle_Product {
		private static $instance;

		public function __construct() {

			// Extend WooCommerce product type selector (wc-metabox).
			add_filter( 'product_type_selector', array( $this, 'extend_product_type_selector' ) );

			// Extend WooCommerce product options with Bundle options (wc-metabox).
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'extend_product_data_tabs' ) );
			add_action( 'woocommerce_admin_process_product_object', array( $this, 'woocommerce_process_product_object' ) );

			// Handle WooCommerce Cart functionalities.
			add_action( 'woocommerce_qode_bundle_product_add_to_cart', array( $this, 'woocommerce_qode_bundle_product_add_to_cart' ) );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woocommerce_add_to_cart_validation' ), 10, 3 );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woocommerce_add_cart_item_data' ), 10, 2 );
			add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 10, 6 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'woocommerce_add_cart_item' ) );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woocommerce_get_cart_item_from_session' ), 10, 2 );

			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'woocommerce_cart_item_remove_link' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woocommerce_cart_item_quantity' ), 10, 2 );
			add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_cart_item_quantity' ), 1, 2 );
			add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_cart_item_quantity' ), 1 );

			add_filter( 'woocommerce_cart_item_price', array( $this, 'woocommerce_cart_item_price' ), 99, 2 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'woocommerce_cart_item_removed' ), 10, 2 );
			add_action( 'woocommerce_cart_item_restored', array( $this, 'woocommerce_cart_item_restored' ), 10, 2 );
			add_filter( 'woocommerce_cart_contents_count', array( $this, 'woocommerce_cart_contents_count' ) );
			add_filter( 'woocommerce_cart_item_class', array( $this, 'add_cart_item_class_for_bundles' ), 10, 3 );

			// Handle WooCommerce Checkout functionalities.
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'woocommerce_order_formatted_line_subtotal' ), 10, 2 );
			add_filter( 'woocommerce_checkout_create_order_line_item', array( $this, 'woocommerce_checkout_create_order_line_item' ), 10, 3 );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woocommerce_hidden_order_itemmeta' ) );
			add_filter( 'woocommerce_order_item_class', array( $this, 'add_order_item_class_for_bundles' ), 10, 3 );

			add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'woocommerce_order_item_needs_processing' ), 10, 2 );
			add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'woocommerce_order_again_cart_item_data' ), 10, 2 );
			add_action( 'woocommerce_ordered_again', array( $this, 'woocommerce_ordered_again' ), 10, 3 );

			// Handle WooCommerce dashboard functionalities.
			add_action( 'wp_ajax_woocommerce_add_order_item', array( $this, 'prevent_adding_bundle_products_in_orders' ), 5 );

			// Handle our meta box functionalities.
			add_filter( 'qode_product_bundles_for_woocommerce_filter_has_meta_box_options', '__return_true' );

			// Enqueue page admin scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_scripts' ) );
		}

		/**
		 * Instance of module class
		 *
		 * @return Qode_Product_Bundles_For_WooCommerce_Bundle_Product
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function extend_product_type_selector( $types ) {
			$types['qode_bundle_product'] = _x( 'Bundle product', 'Admin: Type of product', 'qode-product-bundles-for-woocommerce' );

			return $types;
		}

		public function extend_product_data_tabs( $product_tabs ) {
			$product_tabs['inventory']['class'] = array_merge( $product_tabs['inventory']['class'], array( 'show_if_qode_bundle_product' ) );

			return $product_tabs;
		}

		public function woocommerce_process_product_object( $product ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$regular_price_meta = isset( $_POST['qode_product_bundles_for_woocommerce_bundle_product_regular_price'] ) ? sanitize_text_field( wp_unslash( $_POST['qode_product_bundles_for_woocommerce_bundle_product_regular_price'] ) ) : null;
			// phpcs:ignore WordPress.Security.NonceVerification
			$sale_price_meta = isset( $_POST['qode_product_bundles_for_woocommerce_bundle_product_sale_price'] ) ? sanitize_text_field( wp_unslash( $_POST['qode_product_bundles_for_woocommerce_bundle_product_sale_price'] ) ) : null;

			if ( $product->is_type( 'qode_bundle_product' ) && ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) ) {

				if ( is_numeric( $regular_price_meta ) || '' === $regular_price_meta ) {
					$product->update_meta_data( '_regular_price', wc_format_decimal( $regular_price_meta ) );
				}

				if ( is_numeric( $sale_price_meta ) || '' === $sale_price_meta ) {
					$product->update_meta_data( '_sale_price', wc_format_decimal( $sale_price_meta ) );
				}
			}
		}

		public function woocommerce_qode_bundle_product_add_to_cart() {

			if ( ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) ) {
				/**
				 * Bundle product
				 *
				 * @var WC_Product_Qode_Bundle_Product $bundle_product
				 */
				$bundle_product = qode_product_bundles_for_woocommerce_get_woocommerce_global_product();

				$bundled_items = $bundle_product->get_bundled_items();

				if ( $bundled_items ) {
					wc_get_template( 'single-product/add-to-cart/qode-bundle-product.php', array(), '', QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_REL_TEMPLATES_PATH . '/' );
				}
			}
		}

		public function woocommerce_add_to_cart_validation( $add_flag, $product_id, $product_quantity ) {

			if ( ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) ) {
				/**
				 * Bundle product
				 *
				 * @var WC_Product_Qode_Bundle_Product $bundle_product
				 */
				$bundle_product = wc_get_product( $product_id );

				if ( $bundle_product && $bundle_product->is_type( 'qode_bundle_product' ) ) {
					$bundled_items = $bundle_product->get_bundled_items();
					$check_stock   = 'yes' === get_option( 'woocommerce_manage_stock' );

					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item_value ) {
							/**
							 * Bundled product item
							 *
							 * @var WC_Product $bundled_item
							 */
							$bundled_item          = $bundled_item_value['product'];
							$bundled_item_map      = $bundled_item_value['product_map'];
							$bundled_item_id       = $bundled_item_map['product_id'];
							$bundled_item_quantity = absint( $bundled_item_map['quantity'] );

							if ( ! $bundled_item->is_purchasable() ) {
								wc_add_notice(
									sprintf(
										// translators: 1: bundled product name; 2: bundled item name.
										esc_html__( '&quot;%1$s&quot; cannot be added to the cart because &quot;%2$s&quot; cannot be purchased at the moment.', 'qode-product-bundles-for-woocommerce' ),
										get_the_title( $bundled_item_map['product_id'] ),
										get_the_title( $bundled_item_id )
									),
									'error'
								);

								return false;
							}

							if ( $check_stock && ! $bundled_item->is_in_stock() ) {
								wc_add_notice(
									sprintf(
										// translators: %s - Product name.
										esc_html__( 'Unfortunately you can\'t this item to the cart, because %s is out of stock.', 'qode-product-bundles-for-woocommerce' ),
										get_the_title( $bundled_item_map['product_id'] )
									),
									'error'
								);

								return false;
							}

							if ( $check_stock && ! $bundled_item->has_enough_stock( $bundled_item_quantity * intval( $product_quantity ) ) ) {
								wc_add_notice( esc_html__( 'Unfortunately there are not enough items in stock.', 'qode-product-bundles-for-woocommerce' ), 'error' );

								return false;
							}
						}
					}
				}
			}

			return $add_flag;
		}

		public function woocommerce_add_cart_item_data( $cart_item_data, $product_id ) {

			if ( ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) ) {
				/**
				 * Bundle product
				 *
				 * @var WC_Product_Qode_Bundle_Product $bundle_product
				 */
				$bundle_product = wc_get_product( $product_id );

				if ( ! $bundle_product || ! $bundle_product->is_type( 'qode_bundle_product' ) ) {
					return $cart_item_data;
				}

				if ( isset( $cart_item_data['qode_cart_bundled'] ) && isset( $cart_item_data['qode_bundled_items'] ) ) {
					return $cart_item_data;
				}

				$bundled_items = $bundle_product->get_bundled_items();

				if ( ! ! $bundled_items ) {
					$qode_cart_bundled = array();

					foreach ( $bundled_items as $bundled_item_value ) {
						$bundled_item_id = $bundled_item_value['product_map']['product_id'];

						// Product map contain.
						// 1. Product meta box options (repeater item) - ID, quantity etc.
						// 2. Product type.
						$qode_cart_bundled[ $bundled_item_id ] = $bundled_item_value['product_map'];
					}

					$cart_item_data['qode_cart_bundled']  = $qode_cart_bundled;
					$cart_item_data['qode_bundled_items'] = array();
				}
			}

			return $cart_item_data;
		}

		public function woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

			if ( ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) && isset( $cart_item_data['qode_cart_bundled'] ) && ! isset( $cart_item_data['qode_cart_bundled_by'] ) ) {
				$bundled_items_cart_data = array( 'qode_cart_bundled_by' => $cart_item_key );

				foreach ( $cart_item_data['qode_cart_bundled'] as $bundled_item_stamp ) {
					$bundled_item_cart_data                           = $bundled_items_cart_data;
					$bundled_item_cart_data['qode_bundled_item_data'] = $bundled_item_stamp;

					$bundled_item_quantity = $bundled_item_stamp['quantity'] * $quantity;
					$bundled_item_cart_key = $this->bundled_add_to_cart( $product_id, $bundled_item_stamp['product_id'], $bundled_item_quantity, $variation_id, array(), $bundled_item_cart_data );

					if ( ! isset( WC()->cart->cart_contents[ $cart_item_key ]['qode_bundled_items'] ) || ! is_array( WC()->cart->cart_contents[ $cart_item_key ]['qode_bundled_items'] ) ) {
						WC()->cart->cart_contents[ $cart_item_key ]['qode_bundled_items'] = array();
					}

					if ( $bundled_item_cart_key && ! in_array( $bundled_item_cart_key, WC()->cart->cart_contents[ $cart_item_key ]['qode_bundled_items'], true ) ) {
						WC()->cart->cart_contents[ $cart_item_key ]['qode_bundled_items'][]     = $bundled_item_cart_key;
						WC()->cart->cart_contents[ $cart_item_key ]['qode_bundle_items_parent'] = $cart_item_key;
					}
				}
			}
		}

		public function bundled_add_to_cart( $bundle_id, $product_id, $quantity = 1, $variation_id = '', $variation = array(), $cart_item_data = array() ) {

			if ( $quantity <= 0 ) {
				return false;
			}

			$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );
			$cart_id        = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );
			$cart_item_key  = WC()->cart->find_product_in_cart( $cart_id );

			$bundle_product = wc_get_product( $product_id );

			if ( empty( $cart_item_key ) ) {
				$cart_item_key = $cart_id;

				WC()->cart->cart_contents[ $cart_item_key ] = apply_filters(
					'woocommerce_add_cart_item',
					array_merge(
						$cart_item_data,
						array(
							'key'          => $cart_item_key,
							'product_id'   => $product_id,
							'variation_id' => $variation_id,
							'variation'    => $variation,
							'quantity'     => $quantity,
							'data'         => $bundle_product,
						)
					),
					$cart_item_key
				);
			}

			return $cart_item_key;
		}

		public function woocommerce_add_cart_item( $cart_item ) {
			$cart_contents = WC()->cart->cart_contents ?? array();

			// Loop through bundled items and set price to zero.
			if ( ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) && isset( $cart_item['qode_cart_bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['qode_cart_bundled_by'];

				if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
					/**
					 * The product.
					 *
					 * @var WC_Product $product
					 */
					$product = $cart_item['data'];

					$product->set_price( 0 );
				}
			}

			return $cart_item;
		}

		public function woocommerce_get_cart_item_from_session( $cart_item, $item_session_values ) {

			if ( ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) ) {
				$cart_contents = WC()->cart->cart_contents ?? array();

				if ( isset( $item_session_values['qode_bundled_items'] ) && ! empty( $item_session_values['qode_bundled_items'] ) ) {
					$cart_item['qode_bundled_items'] = $item_session_values['qode_bundled_items'];
				}

				if ( isset( $item_session_values['qode_cart_bundled'] ) ) {
					$cart_item['qode_cart_bundled'] = $item_session_values['qode_cart_bundled'];
				}

				// Loop through bundled items and set price to zero.
				if ( isset( $item_session_values['qode_cart_bundled_by'] ) ) {
					$cart_item['qode_cart_bundled_by']   = $item_session_values['qode_cart_bundled_by'];
					$cart_item['qode_bundled_item_data'] = $item_session_values['qode_bundled_item_data'];
					$bundle_cart_key                     = $cart_item['qode_cart_bundled_by'];

					if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
						/**
						 * The product.
						 *
						 * @var WC_Product $product
						 */
						$product = $cart_item['data'];

						$product->set_price( 0 );
					}
				}
			}

			return $cart_item;
		}

		public function woocommerce_cart_item_remove_link( $link, $cart_item_key ) {
			$cart_contents = WC()->cart->cart_contents ?? array();

			if ( ! empty( $cart_contents ) && isset( $cart_contents[ $cart_item_key ]['qode_cart_bundled_by'] ) ) {
				$bundle_cart_key = $cart_contents[ $cart_item_key ]['qode_cart_bundled_by'];

				if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}

			return $link;
		}

		public function woocommerce_cart_item_quantity( $quantity, $cart_item_key ) {
			$cart_contents = WC()->cart->cart_contents ?? array();

			if ( ! empty( $cart_contents ) && isset( $cart_contents[ $cart_item_key ]['qode_cart_bundled_by'] ) ) {
				return $cart_contents[ $cart_item_key ]['quantity'];
			}

			return $quantity;
		}

		public function update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
			$cart_contents = WC()->cart->cart_contents ?? array();

			if ( ! empty( $cart_contents ) && ! empty( $cart_contents[ $cart_item_key ] ) ) {
				$quantity = $quantity <= 0 ? 0 : $cart_contents[ $cart_item_key ]['quantity'];
				$stamp    = $cart_contents[ $cart_item_key ]['qode_cart_bundled'] ?? array();

				if ( ! empty( $stamp ) && ! isset( $cart_contents[ $cart_item_key ]['qode_cart_bundled_by'] ) ) {
					foreach ( $cart_contents as $key => $value ) {
						if ( isset( $value['qode_cart_bundled_by'] ) && $cart_item_key === $value['qode_cart_bundled_by'] ) {
							$bundle_item_id  = $value['qode_bundled_item_data']['product_id'];
							$bundle_quantity = $stamp[ $bundle_item_id ]['quantity'];

							WC()->cart->set_quantity( $key, $quantity * $bundle_quantity, false );
						}
					}
				}
			}
		}

		public function woocommerce_cart_item_price( $price, $cart_item ) {

			if ( isset( $cart_item['qode_cart_bundled_by'] ) && ! qode_product_bundles_for_woocommerce_is_installed( 'product-bundles-premium' ) ) {

				if ( isset( WC()->cart->cart_contents[ $cart_item['qode_cart_bundled_by'] ] ) ) {
					return '';
				}
			}

			return $price;
		}

		public function woocommerce_cart_item_removed( $cart_item_key, $cart ) {

			if ( ! empty( $cart->removed_cart_contents[ $cart_item_key ]['qode_bundled_items'] ) ) {
				$bundled_item_cart_keys = $cart->removed_cart_contents[ $cart_item_key ]['qode_bundled_items'];

				foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

					if ( ! empty( $cart->cart_contents[ $bundled_item_cart_key ] ) ) {
						$cart->removed_cart_contents[ $bundled_item_cart_key ] = $cart->cart_contents[ $bundled_item_cart_key ];

						unset( $cart->cart_contents[ $bundled_item_cart_key ] );

						do_action( 'woocommerce_cart_item_removed', $bundled_item_cart_key, $cart );
					}
				}
			}
		}

		public function woocommerce_cart_item_restored( $cart_item_key, $cart ) {
			if ( ! empty( $cart->cart_contents[ $cart_item_key ]['qode_bundled_items'] ) ) {
				$bundled_item_cart_keys = $cart->cart_contents[ $cart_item_key ]['qode_bundled_items'];

				foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {
					$cart->restore_cart_item( $bundled_item_cart_key );
				}
			}
		}

		public function woocommerce_cart_contents_count( $count ) {
			$cart_contents = WC()->cart->cart_contents ?? array();

			$bundled_items_count = 0;
			foreach ( $cart_contents as $cart_item ) {
				if ( ! empty( $cart_item['qode_cart_bundled_by'] ) ) {
					$bundled_items_count += $cart_item['quantity'];
				}
			}

			return intval( $count - $bundled_items_count );
		}

		public function add_cart_item_class_for_bundles( $class_name, $cart_item, $cart_item_key = '' ) {
			$cart        = isset( WC()->cart ) ? WC()->cart->get_cart() : array();
			$class_name .= $this->add_item_class_logic( $cart, $cart_item, $cart_item_key );

			return $class_name;
		}

		private function add_item_class_logic( $items, $item, $item_key ) {
			$is_bundled_item = isset( $item['qode_cart_bundled_by'] );
			$is_bundle       = isset( $item['qode_cart_bundled'] );
			$class_name      = '';

			if ( $is_bundled_item ) {
				$class_name .= ' qpbfw-bundle-child-item';
			} elseif ( $is_bundle ) {
				$class_name .= ' qpbfw-bundle-item ';
			}

			$last_bundled_item_flag = false;

			if ( ! empty( $items ) && $item_key && $is_bundled_item ) {
				$keys = array_keys( $items );

				$item_index = array_search( $item_key, $keys, true );
				if ( $item_index && isset( $keys[ $item_index + 1 ] ) ) {
					$next_key       = $keys[ $item_index + 1 ];
					$next_cart_item = $items[ $next_key ];

					if ( ! isset( $next_cart_item['qode_cart_bundled_by'] ) ) {
						$last_bundled_item_flag = true;
					}
				} elseif ( $item_index && ! isset( $keys[ $item_index + 1 ] ) ) {
					$last_bundled_item_flag = true;
				}
			}

			if ( $last_bundled_item_flag ) {
				$class_name .= ' qpbfw--last ';
			}

			return $class_name;
		}

		/**
		 * Function that remove subtotal for bundled product items in order
		 *
		 * @param string        $subtotal
		 * @param WC_Order_Item $item
		 *
		 * @return string
		 */
		public function woocommerce_order_formatted_line_subtotal( $subtotal, $item ) {
			if ( isset( $item['qode_cart_bundled_by'] ) ) {
				return '';
			}

			return $subtotal;
		}

		/**
		 * Function that add bundled product data to order items
		 *
		 * @param WC_Order_Item_Product $item
		 * @param string                $cart_item_key
		 * @param array                 $cart_item
		 */
		public function woocommerce_checkout_create_order_line_item( $item, $cart_item_key, $cart_item ) {
			$meta_to_store = array();

			if ( isset( $cart_item['qode_cart_bundled'] ) ) {
				$meta_to_store = array(
					'_qode_cart_bundled'  => $cart_item['qode_cart_bundled'],
					'_qode_bundled_items' => array(),
				);
			} elseif ( isset( $cart_item['qode_cart_bundled_by'] ) ) {
				$meta_to_store = array(
					'_qode_cart_bundled_by'   => $cart_item['qode_cart_bundled_by'],
					'_qode_bundled_item_data' => $cart_item['qode_bundled_item_data'],
				);
			}

			if ( $meta_to_store ) {
				foreach ( $meta_to_store as $key => $value ) {
					$item->add_meta_data( $key, $value );
				}
			}
		}

		/**
		 * Function that hide our bundled product meta in admin order
		 *
		 * @param array $hidden
		 *
		 * @return array
		 */
		public function woocommerce_hidden_order_itemmeta( $hidden ) {
			return array_merge(
				$hidden,
				array(
					'_qode_cart_bundled_by',
					'_qode_cart_bundled',
					'_qode_bundled_item_data',
				)
			);
		}

		/**
		 * Function that add cart item classes for bundled products and bundled items
		 *
		 * @param string                $class_name
		 * @param WC_Order_Item_Product $item
		 * @param WC_Order              $order
		 *
		 * @return string
		 */
		public function add_order_item_class_for_bundles( $class_name, $item, $order ) {
			$items       = ! empty( $order ) ? $order->get_items() : array();
			$class_name .= $this->add_item_class_logic( $items, $item, $item->get_id() );

			return $class_name;
		}

		/**
		 * Function that set needs_processing to false for Bundled product in orders
		 *
		 * @param bool       $needs_processing
		 * @param WC_Product $product
		 *
		 * @return bool
		 */
		public function woocommerce_order_item_needs_processing( $needs_processing, $product ) {
			if ( $product->is_type( 'qode_bundle_product' ) ) {
				return false;
			}

			return $needs_processing;
		}

		/**
		 * Function that add cart item data for bundled product when "Order Again"
		 *
		 * @param array         $cart_item_data
		 * @param WC_Order_Item $item
		 *
		 * @return array
		 */
		public function woocommerce_order_again_cart_item_data( $cart_item_data, $item ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$product = $item->get_product();

				if ( $product && $product->is_type( 'qode_bundle_product' ) ) {
					$qode_cart_bundled = $item->get_meta( '_qode_cart_bundled' );

					if ( $qode_cart_bundled ) {
						$cart_item_data['qode_cart_bundled']             = $qode_cart_bundled;
						$cart_item_data['qode_bundled_items']            = $item->get_meta( '_qode_bundled_items' );
						$cart_item_data['qode_bundle_items_order_again'] = true;
					}
				} elseif ( $item->get_meta( '_qode_cart_bundled_by' ) ) {
					$cart_item_data['qode_bundle_items_order_again_remove_flag'] = true;
				}
			}

			return $cart_item_data;
		}

		/**
		 * Function that handle order again
		 *
		 * @param int   $order_id
		 * @param array $order_items
		 * @param array $cart
		 */
		public function woocommerce_ordered_again( $order_id, $order_items, &$cart ) {
			$new_cart = array();

			foreach ( $cart as $key => $item ) {

				// If the current item has remove flag, skip iteration.
				if ( isset( $item['qode_bundle_items_order_again_remove_flag'] ) ) {
					continue;
				}

				$new_cart[ $key ] = $item;

				if ( isset( $item['qode_cart_bundled'] ) ) {
					$bundled_items = array();

					foreach ( $item['qode_cart_bundled'] as $id => $item_cart_stamp ) {
						$cart_data = array( 'qode_cart_bundled_by' => $key );

						$item_cart_stamp['quantity'] *= $item['quantity'];

						$bundled_item_data = $this->get_bundled_item_data_from_cart_stamp( $item_cart_stamp, $cart_data );

						if ( $bundled_item_data ) {
							$bundled_item_key  = $bundled_item_data['key'];
							$bundled_item_cart = $bundled_item_data['item'];

							if ( $bundled_item_key && ! in_array( $bundled_item_key, $bundled_items, true ) ) {
								$bundled_items[] = $bundled_item_key;

								$new_cart[ $bundled_item_key ] = $bundled_item_cart;
							}
						}
					}

					$new_cart[ $key ]['qode_bundled_items'] = $bundled_items;
				}
			}

			$cart = $new_cart;
		}

		/**
		 * Function that return bundled product item data from its cart stamp
		 *
		 * @param array $item_cart_stamp
		 * @param array $cart_item_data
		 *
		 * @return array|false
		 */
		protected function get_bundled_item_data_from_cart_stamp( $item_cart_stamp, $cart_item_data = array() ) {
			$quantity     = $item_cart_stamp['quantity'];
			$product_id   = $item_cart_stamp['product_id'];
			$variation_id = $item_cart_stamp['variation_id'] ?? false;
			$variation    = ! ! $variation_id ? wc_get_product( $variation_id ) : false;

			$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );
			$cart_item_key  = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $variation_id );
			}

			$product_data = wc_get_product( ! ! $variation_id ? $variation_id : $product_id );

			$data = false;

			if ( ! ! $cart_item_key ) {
				$data = array(
					'key'  => $cart_item_key,
					'item' => apply_filters(
						'woocommerce_add_cart_item',
						array_merge(
							$cart_item_data,
							array(
								'key'          => $cart_item_key,
								'product_id'   => $product_id,
								'variation_id' => $variation_id,
								'variation'    => $variation,
								'quantity'     => $quantity,
								'data'         => $product_data,
							)
						),
						$cart_item_key
					),
				);
			}

			return $data;
		}

		/**
		 * Function that prevent adding bundled product to orders through "Add Products" in orders
		 */
		public function prevent_adding_bundle_products_in_orders() {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['data'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$items_to_add = array_filter( map_deep( wp_unslash( (array) $_POST['data'] ), 'sanitize_text_field' ) );

				$bundle_titles = array();
				foreach ( $items_to_add as $item ) {
					if ( ! isset( $item['id'], $item['qty'] ) || empty( $item['id'] ) ) {
						continue;
					}
					$product_id = absint( $item['id'] );
					$product    = wc_get_product( $product_id );
					if ( $product && $product->is_type( 'qode_bundle_product' ) ) {
						$bundle_titles[] = $product->get_formatted_name();
					}
				}

				if ( $bundle_titles ) {
					// translators: %s is a comma-separated list of bundle products.
					wp_send_json_error( array( 'error' => sprintf( esc_html__( 'You are trying to add the following Bundle products to the order: %s. You cannot add Bundle products to orders through this box since this type of products needs to follow the normal WooCommerce "Add-to-cart > Cart > Checkout > Order" process.', 'qode-product-bundles-for-woocommerce' ), implode( ', ', $bundle_titles ) ) ) );
				}
			}
		}

		public function enqueue_dashboard_scripts() {
			$screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : '';
			$screen_id = $screen ? $screen->id : '';

			if ( in_array( $screen_id, array( 'product', 'edit-product' ), true ) ) {
				wp_enqueue_script( 'qode-product-bundles-for-woocommerce-bundle-product-meta-box', QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_URL_PATH . '/bundle-product/assets/js/bundle-product-meta-box.js', array( 'jquery' ), QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_VERSION, true );

				// Set additional hook for 3rd party elements.
				do_action( 'qode_product_bundles_for_woocommerce_action_enqueue_product_page_dashboard_scripts' );
			}
		}
	}

	Qode_Product_Bundles_For_WooCommerce_Bundle_Product::get_instance();
}
