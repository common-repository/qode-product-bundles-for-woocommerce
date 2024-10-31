<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Field_Number extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_Type {

	public function render_field() {
		?>
		<?php
		if ( ! empty( $this->args['custom_class'] ) ) {
			?>
			<div <?php qode_product_bundles_for_woocommerce_class_attribute( $this->args['custom_class'] ); ?>>
		<?php } ?>
		<?php
		$min = 0;
		$max = 9999;
		if ( ! empty( $this->args['min'] ) ) {
			$min = $this->args['min'];
		}
		if ( ! empty( $this->args['max'] ) ) {
			$max = $this->args['max'];
		}
		if ( $this->params['suffix'] || $this->params['prefix'] ) {
			?>
		<div class="input-group">
			<?php
		}
		if ( $this->params['prefix'] ) {
			?>
			<div class="input-group-addon input-prefix">
				<?php echo esc_html( $this->params['prefix'] ); ?>
			</div>
		<?php } ?>
		<input type="number" <?php qode_product_bundles_for_woocommerce_inline_attrs( $this->data_attrs ); ?> class="qodef-field qodef-input" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( esc_html( $this->params['value'] ) ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
				<?php
				if ( isset( $this->args['readonly'] ) ) {
					echo ' readonly';
				}
				?>
		/>
		<?php
		if ( $this->params['suffix'] ) {
			?>
			<div class="input-group-addon input-suffix">
				<?php echo esc_html( $this->params['suffix'] ); ?>
			</div>
		<?php } ?>
		<?php
		if ( $this->params['suffix'] || $this->params['prefix'] ) {
			?>
		</div>
			<?php
		}
		if ( ! empty( $this->args['custom_class'] ) ) {
			?>
			</div>
			<?php
		}
	}
}
