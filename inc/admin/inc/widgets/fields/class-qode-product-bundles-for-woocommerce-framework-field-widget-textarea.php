<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Field_Widget_Textarea extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_Widget_Type {

	public function render() {
		?>
		<textarea class="widefat" rows="16" cols="20" id="<?php echo esc_attr( $this->params['id'] ); ?>" name="<?php echo esc_attr( $this->params['name'] ); ?>" type="text"><?php echo esc_html( $this->params['value'] ); ?></textarea>
		<?php
	}
}
