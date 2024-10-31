<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}
?>
<div class="qodef-no-tab-wrapper qodef-options-custom qodef-page-v4-product-bundles qodef-exclude-panel-from-saving">
	<div class="row">
		<?php
		do_action( 'qode_product_bundles_for_woocommerce_action_framework_custom_page_content', $page_slug ?? '' );
		?>
	</div>
</div>
