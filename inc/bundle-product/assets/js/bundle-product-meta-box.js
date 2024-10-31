(function ( $ ) {
	'use strict';

	$( document ).on(
		'ready',
		function () {
			qiWooProductBundlesDashboardInit.setWooCommerceMetaOptionsVisibility();
		}
	);

	$( window ).on(
		'load',
		function () {
			qiWooProductBundlesDashboardInit.init();
		}
	);

	/**
	 * Function object that represents product meta boxes
	 *
	 * @returns {{init: Function}}
	 */
	var qiWooProductBundlesDashboardInit = {
		init: function () {
			this.setMetaBoxOptionsVisibility();
		},
		setWooCommerceMetaOptionsVisibility: function () {
			var showClass              = 'show_if_qode_bundle_product';
			var hideClass              = 'hide_if_qode_bundle_product';
			var $soldIndividuallyField = $( '._sold_individually_field' );
			var $stockField            = $( '._manage_stock_field' );
			var $priceField            = $( '.pricing' );
			var $taxStatusField        = $( '._tax_status_field' );

			if ( $soldIndividuallyField.length ) {
				$soldIndividuallyField.addClass( showClass ).closest( 'div' ).addClass( showClass );
			}

			if ( $stockField.length ) {
				$stockField.addClass( showClass );
			}

			if ( $priceField.length ) {
				$priceField.addClass( hideClass );
			}

			if ( $taxStatusField.length ) {
				$taxStatusField.closest( 'div' ).addClass( showClass );
			}
		},
		setMetaBoxOptionsVisibility: function () {
			var $listener = $( '#woocommerce-product-data .hndle #product-type' ),
				$trigger  = $( '#qode-framework-woo-meta-box-bundle-product' );

			if ( $trigger.length && $listener.length ) {
				// Check initial value.
				qiWooProductBundlesDashboardInit.checkVisibility( $trigger, $listener.val() );

				// Check value on DropDown change.
				$listener.on(
					'change',
					function () {
						qiWooProductBundlesDashboardInit.checkVisibility( $trigger, $( this ).val() );
					}
				);
			}
		},
		checkVisibility: function ( $trigger, $value ) {
			if ( 'qode_bundle_product' === $value ) {
				$trigger.show();
			} else {
				$trigger.hide();
			}
		}
	};

})( jQuery );
