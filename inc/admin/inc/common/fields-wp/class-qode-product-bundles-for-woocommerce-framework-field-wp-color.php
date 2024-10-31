<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}
class Qode_Product_Bundles_For_WooCommerce_Framework_Field_WP_Color extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_WP_Type {

	public function load_assets() {
		parent::load_assets();

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-alpha' );
	}

	public function render_field() {
		?>
		<input type="text" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->params['id'] ); ?>" value="<?php echo esc_attr( $this->params['value'] ); ?>" data-alpha="true" class="qodef-color-field"/>
		<?php
	}
}
