<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}
?>
<div class="qodef-admin-page-v4 qodef-no-header">
	<div class="qodef-admin-content-wrapper">
		<div class="qodef-admin-content">
			<div class="qodef-tabs-content">
				<?php $this_object->get_content(); ?>
				<?php $this_object->get_footer(); ?>
			</div>
		</div>
	</div>
</div>
