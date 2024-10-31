<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Field_Font extends Qode_Product_Bundles_For_WooCommerce_Framework_Field_Type {

	public function __construct( $params ) {
		$select_class           = 'qodef-select2';
		$params['select_class'] = $select_class;

		$include_fonts = '';
		if ( isset( $params['args'] ) && isset( $params['args']['include_only'] ) && '' !== $params['args']['include_only'] ) {
			$include_fonts = $params['args']['include_only'];
		}

		$params['include_only'] = $include_fonts;
		parent::__construct( $params );
	}

	public function render_field() {
		?>
		<select class="<?php echo esc_attr( $this->params['select_class'] ); ?> qodef-field qodef-font-field" name="<?php echo esc_attr( $this->name ); ?>" data-option-name="<?php echo esc_attr( $this->name ); ?>" data-option-type="selectbox">
			<?php
			$fonts = qode_product_bundles_for_woocommerce_framework_get_complete_fonts_array();
			foreach ( $fonts as $key => $label ) {
				if ( '-1' === $key ) {
					$key = '';
				}
				?>
				<option
					<?php
					if ( $this->params['value'] === $key ) {
						echo " selected='selected'";
					}
					?>
						value="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( $label ); ?>
				</option>
			<?php } ?>
		</select>
		<?php
	}
}
