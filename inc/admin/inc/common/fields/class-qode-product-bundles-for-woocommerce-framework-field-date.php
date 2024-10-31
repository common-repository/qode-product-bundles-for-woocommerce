<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Field_Date extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_Type {

	public function __construct( $params ) {
		$date_format = 'yy-mm-dd';
		if ( isset( $params['args'] ) && isset( $params['args']['date_format'] ) && ! empty( $params['args']['date_format'] ) ) {
			$date_format = $params['args']['date_format'];
		}
		$params['date_format'] = $date_format;

		parent::__construct( $params );
	}

	public function load_assets() {
		parent::load_assets();

		wp_enqueue_script( 'jquery-ui-datepicker' );
	}

	public function render_field() {
		?>
		<input type="text" data-date-format="<?php echo esc_attr( $this->params['date_format'] ); ?>" <?php qode_product_bundles_for_woocommerce_inline_attrs( $this->data_attrs ); ?> class="qodef-field qodef-input qodef-datepicker" name="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_attr( $this->params['value'] ); ?>" autocomplete="off" readonly/>
		<?php
	}
}
