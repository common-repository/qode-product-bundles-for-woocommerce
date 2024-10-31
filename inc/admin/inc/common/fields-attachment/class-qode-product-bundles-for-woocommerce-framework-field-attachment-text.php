<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}
class Qode_Product_Bundles_For_WooCommerce_Framework_Field_Attachment_Text extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_Attachment_Type {

	public function render() {
		$html = '<input type="text" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->params['value'] ) . '">';

		$this->form_fields['html'] = $html;
	}
}
