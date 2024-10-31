<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Field_Hidden extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_Type {

	public function render_field() {
		?>
		<?php if ( ! empty( $this->args['custom_class'] ) ) : ?>
			<div <?php qode_product_bundles_for_woocommerce_class_attribute( $this->args['custom_class'] ); ?>>
		<?php endif; ?>
		<input
				type="hidden" <?php qode_product_bundles_for_woocommerce_inline_attrs( $this->data_attrs ); ?>
				class="qodef-field qodef-input" name="<?php echo esc_attr( $this->name ); ?>"
				value="<?php echo esc_attr( esc_html( $this->params['value'] ) ); ?>"
			<?php
			if ( isset( $this->args['readonly'] ) ) {
				echo ' readonly';
			}
			?>
		/>
		<?php if ( ! empty( $this->args['custom_class'] ) ) : ?>
			</div>
			<?php
		endif;
	}
}
