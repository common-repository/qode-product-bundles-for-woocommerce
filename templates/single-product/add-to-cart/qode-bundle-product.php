<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo wc_get_stock_html( $product );

/**
 * Bundle product
 *
 * @var WC_Product_Qode_Bundle_Product $product
 */
if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php
		$bundled_items = $product->get_bundled_items();

		if ( $bundled_items ) {
			// Hook to include additional elements before bundled items.
			do_action( 'qode_product_bundles_for_woocommerce_action_before_bundle_product_items' );
			?>
			<div
				class="qpbfw-bundle-product-items qpbfw-m qpbfw-layout--standard"
				data-bundle_id="<?php echo esc_attr( $product->get_id() ); ?>">
				<?php
				foreach ( $bundled_items as $bundled_item_value ) :
					/**
					 * Bundled product item
					 *
					 * @var WC_Product $bundled_item
					 */
					$bundled_item         = $bundled_item_value['product'];
					$bundled_item_map     = $bundled_item_value['product_map'];
					$bundled_item_id      = (int) $bundled_item_map['product_id'];
					$quantity             = ! empty( $bundled_item_map['quantity'] ) ? absint( $bundled_item_map['quantity'] ) : 1;
					$product_title        = $bundled_item->get_title();
					$product_description  = $bundled_item->get_short_description();
					$product_thumbnail_id = $bundled_item->get_image_id();
					$product_permalink    = apply_filters( 'qode_product_bundles_for_woocommerce_filter_bundle_product_item_link', $bundled_item->get_permalink(), $bundled_item, $bundled_item_map );

					$item_classes   = array( 'qpbfw-e product' );
					$item_classes[] = 'qpbfw--' . esc_attr( $bundled_item->get_type() );

					$item_classes = apply_filters( 'qode_product_bundles_for_woocommerce_filter_bundle_product_item_classes', $item_classes, $bundled_item, $bundled_item_map );
					?>
					<div
							class="qpbfw-m-item <?php echo esc_attr( implode( ' ', $item_classes ) ); ?>"
							data-product_id="<?php echo esc_attr( $bundled_item_id ); ?>"
							data-quantity="<?php echo absint( $quantity ); ?>">
						<div class="qpbfw-e-image" data-default-src="<?php echo esc_url( $product_thumbnail_id ? wp_get_attachment_image_url( $product_thumbnail_id, apply_filters( 'woocommerce_gallery_image_size', 'woocommerce_single' ) ) : wc_placeholder_img_src() ); ?>">
							<?php
							echo wp_kses_post( qode_product_bundles_for_woocommerce_get_bundled_item_thumbnail( $product_thumbnail_id, $product_permalink ) );
							?>
						</div>
						<div class="qpbfw-e-content">
							<h5 class="qpbfw-e-title">
								<a href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo wp_kses_post( $product_title ); ?>
								</a>
							</h5>
							<?php if ( ! empty( $quantity ) ) { ?>
								<p class="qpbfw-e-quantity">
									<?php esc_html_e( 'Qty', 'qode-product-bundles-for-woocommerce' ); ?>
									<?php echo esc_html( $quantity ); ?>
								</p>
							<?php } ?>
							<?php if ( ! empty( $product_description ) ) { ?>
								<p class="qpbfw-e-description">
									<?php echo wp_kses_post( do_shortcode( $product_description ) ); ?>
								</p>
							<?php } ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
			// Hook to include additional elements after bundled items.
			do_action( 'qode_product_bundles_for_woocommerce_action_after_bundle_product_items' );
		}
		?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			array(
				'min_value' => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			)
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
