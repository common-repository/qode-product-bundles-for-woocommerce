<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_is_installed' ) ) {
	/**
	 * Function check is some plugin is installed
	 *
	 * @param string $plugin name
	 *
	 * @return bool
	 */
	function qode_product_bundles_for_woocommerce_is_installed( $plugin ) {
		switch ( $plugin ) :
			case 'product-bundles-premium':
				return defined( 'QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_PREMIUM_VERSION' );
			case 'wishlist':
				return defined( 'QODE_WISHLIST_FOR_WOOCOMMERCE_VERSION' );
			case 'quick-view':
				return defined( 'QODE_QUICK_VIEW_FOR_WOOCOMMERCE_VERSION' );
			case 'wpbakery':
				return class_exists( 'WPBakeryVisualComposerAbstract' );
			case 'elementor':
				return defined( 'ELEMENTOR_VERSION' );
			case 'woocommerce':
				return class_exists( 'WooCommerce' );
			case 'wpml':
				return defined( 'ICL_SITEPRESS_VERSION' );
			default:
				return apply_filters( 'qode_product_bundles_for_woocommerce_filter_is_plugin_installed', false, $plugin );

		endswitch;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_sanitize_module_template_part' ) ) {
	/**
	 * Sanitize module template part.
	 *
	 * @param string $template temp path to file that is being loaded
	 *
	 * @return string - string with template path
	 */
	function qode_product_bundles_for_woocommerce_sanitize_module_template_part( $template ) {
		$available_characters = '/[^A-Za-z0-9\_\-\/]/';

		if ( ! empty( $template ) && is_scalar( $template ) ) {
			$template = preg_replace( $available_characters, '', $template );
		} else {
			$template = '';
		}

		return $template;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_template_with_slug' ) ) {
	/**
	 * Loads module template part.
	 *
	 * @param string $temp temp path to file that is being loaded
	 * @param string $slug slug that should be checked if exists
	 *
	 * @return string - string with template path
	 */
	function qode_product_bundles_for_woocommerce_get_template_with_slug( $temp, $slug ) {
		$template = '';

		if ( ! empty( $temp ) ) {
			$slug = qode_product_bundles_for_woocommerce_sanitize_module_template_part( $slug );

			if ( ! empty( $slug ) ) {
				$template = "$temp-$slug.php";

				if ( ! file_exists( $template ) ) {
					$template = $temp . '.php';
				}
			} else {
				$template = $temp . '.php';
			}
		}

		return $template;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_template_part' ) ) {
	/**
	 * Loads module template part.
	 *
	 * @param string $module name of the module from inc folder
	 * @param string $template full path of the template to load
	 * @param string $slug
	 * @param array $params array of parameters to pass to template
	 *
	 * @return string - string containing html of template
	 */
	function qode_product_bundles_for_woocommerce_get_template_part( $module, $template, $slug = '', $params = array() ) {
		$module   = qode_product_bundles_for_woocommerce_sanitize_module_template_part( $module );
		$template = qode_product_bundles_for_woocommerce_sanitize_module_template_part( $template );

		$temp = QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_INC_PATH . '/' . $module . '/' . $template;

		$template = qode_product_bundles_for_woocommerce_get_template_with_slug( $temp, $slug );

		if ( ! empty( $template ) && file_exists( $template ) ) {
			// Extract params so they could be used in template.
			if ( is_array( $params ) && count( $params ) ) {
				// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				extract( $params, EXTR_SKIP ); // @codingStandardsIgnoreLine
			}

			ob_start();

			// nosemgrep audit.php.lang.security.file.inclusion-arg.
			include qode_product_bundles_for_woocommerce_get_template_with_slug( $temp, $slug );

			$html = ob_get_clean();

			return $html;
		} else {
			return '';
		}
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_template_part' ) ) {
	/**
	 * Echo module template part.
	 *
	 * @param string $module name of the module from inc folder
	 * @param string $template full path of the template to load
	 * @param string $slug
	 * @param array $params array of parameters to pass to template
	 */
	function qode_product_bundles_for_woocommerce_template_part( $module, $template, $slug = '', $params = array() ) {
		$module_template_part = qode_product_bundles_for_woocommerce_get_template_part( $module, $template, $slug, $params );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo qode_product_bundles_for_woocommerce_framework_wp_kses_html( 'html', $module_template_part );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_option_value' ) ) {
	/**
	 * Function that returns option value using framework function but providing its own scope
	 *
	 * @param string $type option type
	 * @param string $name name of option
	 * @param string $default_value option default value
	 * @param int $post_id id of
	 *
	 * @return string value of option
	 */
	function qode_product_bundles_for_woocommerce_get_option_value( $type, $name, $default_value = '', $post_id = null ) {
		$scope = QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_OPTIONS_NAME;

		return qode_product_bundles_for_woocommerce_framework_get_option_value( $scope, $type, $name, $default_value, $post_id );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_post_value_through_levels' ) ) {
	/**
	 * Function that returns meta value if exists, otherwise global value using framework function but providing its own scope
	 *
	 * @param string $name name of option
	 * @param int $post_id id of
	 *
	 * @return string|array value of option
	 */
	function qode_product_bundles_for_woocommerce_get_post_value_through_levels( $name, $post_id = null ) {
		$scope = QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_OPTIONS_NAME;

		return qode_product_bundles_for_woocommerce_framework_get_post_value_through_levels( $scope, $name, $post_id );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_svg_icon' ) ) {
	/**
	 * Function that print svg html icon
	 *
	 * @param string $name - icon name
	 * @param string $class_name - custom html tag class name
	 */
	function qode_product_bundles_for_woocommerce_svg_icon( $name, $class_name = '' ) {
		$svg_template_part = qode_product_bundles_for_woocommerce_get_svg_icon( $name, $class_name );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo qode_product_bundles_for_woocommerce_framework_wp_kses_html( 'html', $svg_template_part );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_svg_icon' ) ) {
	/**
	 * Returns svg html
	 *
	 * @param string $name - icon name
	 * @param string $class_name - custom html tag class name
	 *
	 * @return string
	 */
	function qode_product_bundles_for_woocommerce_get_svg_icon( $name, $class_name = '' ) {
		$html  = '';
		$class = isset( $class_name ) && ! empty( $class_name ) ? 'class="' . esc_attr( $class_name ) . '"' : '';

		switch ( $name ) {
			case 'expand':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="92px" height="92px" viewBox="0 0 92 92" xml:space="preserve"><path d="M90,6l0,20c0,2.2-1.8,4-4,4l0,0c-2.2,0-4-1.8-4-4V15.7L58.8,38.9c-0.8,0.8-1.8,1.2-2.8,1.2c-1,0-2-0.4-2.8-1.2c-1.6-1.6-1.6-4.1,0-5.7L76.3,10H66c-2.2,0-4-1.8-4-4c0-2.2,1.8-4,4-4h20c1.1,0,2.1,0.4,2.8,1.2C89.6,3.9,90,4.9,90,6z M86,62c-2.2,0-4,1.8-4,4v10.3L59.2,53.7c-1.6-1.6-4.2-1.6-5.8,0c-1.6,1.6-1.6,4.1-0.1,5.7L75.9,82H65.6c0,0,0,0,0,0c-2.2,0-4,1.8-4,4s1.8,4,4,4l20,0l0,0c1.1,0,2.3-0.4,3-1.2c0.8-0.8,1.4-1.8,1.4-2.8V66C90,63.8,88.2,62,86,62zM32.8,53.5L10,76.3V66c0-2.2-1.8-4-4-4h0c-2.2,0-4,1.8-4,4l0,20c0,1.1,0.4,2.1,1.2,2.8C4,89.6,5,90,6.1,90h20c2.2,0,4-1.8,4-4c0-2.2-1.8-4-4-4H15.7l22.8-22.8c1.6-1.6,1.5-4.1,0-5.7C37,51.9,34.4,51.9,32.8,53.5z M15.7,10.4l10.3,0h0c2.2,0,4-1.8,4-4s-1.8-4-4-4l-20,0h0c-1.1,0-2.1,0.4-2.8,1.2C2.4,4.3,2,5.3,2,6.4l0,20c0,2.2,1.8,4,4,4c2.2,0,4-1.8,4-4V16l23.1,23.1c0.8,0.8,1.8,1.2,2.8,1.2c1,0,2-0.4,2.8-1.2c1.6-1.6,1.6-4.1,0-5.7L15.7,10.4z"/></svg>';
				break;
			case 'delete':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="12.25" height="14" viewBox="0 0 12.25 14"><g><path d="M12.25 2.625v.438a.437.437 0 0 1-.438.438h-.437v9.188A1.313 1.313 0 0 1 10.063 14H2.188a1.313 1.313 0 0 1-1.313-1.312V3.5H.438A.437.437 0 0 1 0 3.062v-.437a.437.437 0 0 1 .438-.437h2.253l.93-1.55A1.456 1.456 0 0 1 4.747 0H7.5a1.456 1.456 0 0 1 1.129.637l.93 1.55h2.253a.437.437 0 0 1 .438.438Zm-2.187.875H2.188v9.188h7.875ZM4.222 2.188h3.806l-.477-.8a.182.182 0 0 0-.141-.08H4.839a.182.182 0 0 0-.141.08Z" /><path d="M8.166 10.96 6.125 8.919l-2.04 2.041a.65.65 0 0 1-.919-.919L5.207 8 3.166 5.96a.65.65 0 0 1 .919-.919l2.04 2.041 2.041-2.041a.65.65 0 0 1 .919.919L7.044 8l2.041 2.041a.65.65 0 0 1-.919.919Z"/></g></svg>';
				break;
			case 'edit':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path d="M1.556 12.444h1.108l7.6-7.6-1.108-1.108-7.6 7.6ZM0 14v-3.306L10.267.447a1.747 1.747 0 0 1 .515-.331 1.536 1.536 0 0 1 .593-.117 1.609 1.609 0 0 1 .6.117 1.366 1.366 0 0 1 .506.35l1.069 1.089a1.271 1.271 0 0 1 .34.506 1.684 1.684 0 0 1 .107.583 1.658 1.658 0 0 1-.107.593 1.457 1.457 0 0 1-.34.515L3.306 14ZM12.444 2.644l-1.089-1.089ZM9.7 4.3l-.544-.564 1.108 1.108Z"/></svg>';
				break;
			case 'quick-edit':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path d="M1.556 12.444h1.108l7.6-7.6-1.108-1.108-7.6 7.6ZM0 14v-3.306L10.267.447a1.747 1.747 0 0 1 .515-.331 1.536 1.536 0 0 1 .593-.117 1.609 1.609 0 0 1 .6.117 1.366 1.366 0 0 1 .506.35l1.069 1.089a1.271 1.271 0 0 1 .34.506 1.684 1.684 0 0 1 .107.583 1.658 1.658 0 0 1-.107.593 1.457 1.457 0 0 1-.34.515L3.306 14ZM12.444 2.644l-1.089-1.089ZM9.7 4.3l-.544-.564 1.108 1.108Z"/></svg>';
				break;
			case 'trash':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="92px" height="92px" viewBox="0 0 92 92" xml:space="preserve"><path d="M78.4,30.4l-3.1,57.8c-0.1,2.1-1.9,3.8-4,3.8H20.7c-2.1,0-3.9-1.7-4-3.8l-3.1-57.8c-0.1-2.2,1.6-4.1,3.8-4.2c2.2-0.1,4.1,1.6,4.2,3.8l2.9,54h43.1l2.9-54c0.1-2.2,2-3.9,4.2-3.8C76.8,26.3,78.5,28.2,78.4,30.4zM89,17c0,2.2-1.8,4-4,4H7c-2.2,0-4-1.8-4-4s1.8-4,4-4h22V4c0-1.9,1.3-3,3.2-3h27.6C61.7,1,63,2.1,63,4v9h22C87.2,13,89,14.8,89,17zM36,13h20V8H36V13z M37.7,78C37.7,78,37.7,78,37.7,78c2,0,3.5-1.9,3.5-3.8l-1-43.2c0-1.9-1.6-3.5-3.6-3.5c-1.9,0-3.5,1.6-3.4,3.6l1,43.3C34.2,76.3,35.8,78,37.7,78z M54.2,78c1.9,0,3.5-1.6,3.5-3.5l1-43.2c0-1.9-1.5-3.6-3.4-3.6c-2,0-3.5,1.5-3.6,3.4l-1,43.2C50.6,76.3,52.2,78,54.2,78C54.1,78,54.1,78,54.2,78z"/></svg>';
				break;
			case 'search':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M18.869 19.162l-5.943-6.484c1.339-1.401 2.075-3.233 2.075-5.178 0-2.003-0.78-3.887-2.197-5.303s-3.3-2.197-5.303-2.197-3.887 0.78-5.303 2.197-2.197 3.3-2.197 5.303 0.78 3.887 2.197 5.303 3.3 2.197 5.303 2.197c1.726 0 3.362-0.579 4.688-1.645l5.943 6.483c0.099 0.108 0.233 0.162 0.369 0.162 0.121 0 0.242-0.043 0.338-0.131 0.204-0.187 0.217-0.503 0.031-0.706zM1 7.5c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5-2.916 6.5-6.5 6.5-6.5-2.916-6.5-6.5z"></path></svg>';
				break;
			case 'untrash':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="12.25" height="14" viewBox="0 0 12.25 14"><g><path d="M12.25 2.625v.438a.437.437 0 0 1-.438.438h-.437v9.188A1.313 1.313 0 0 1 10.063 14H2.188a1.313 1.313 0 0 1-1.313-1.312V3.5H.438A.437.437 0 0 1 0 3.062v-.437a.437.437 0 0 1 .438-.437h2.253l.93-1.55A1.456 1.456 0 0 1 4.747 0H7.5a1.456 1.456 0 0 1 1.129.637l.93 1.55h2.253a.437.437 0 0 1 .438.438Zm-2.187.875H2.188v9.188h7.875ZM4.222 2.188h3.806l-.477-.8a.182.182 0 0 0-.141-.08H4.839a.182.182 0 0 0-.141.08Z"/><path d="M5.475 10.8V7.067L4.412 8.13a.64912441.64912441 0 0 1-.917-.919l2.17-2.17a.645.645 0 0 1 .46-.191.648.648 0 0 1 .494.228l2.136 2.133a.65.65 0 1 1-.918.92L6.775 7.07v3.73a.65.65 0 0 1-1.3 0Z"/></g></svg>';
				break;
			case 'remove':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="12.25" height="14" viewBox="0 0 12.25 14"><path d="M12.25,2.625v.4375a.4374.4374,0,0,1-.4375.4375H11.375v9.1875A1.3128,1.3128,0,0,1,10.0625,14H2.1875A1.3128,1.3128,0,0,1,.875,12.6875V3.5H.4375A.4374.4374,0,0,1,0,3.0625V2.625a.4374.4374,0,0,1,.4375-.4375H2.6909l.93-1.55A1.4556,1.4556,0,0,1,4.7466,0H7.5039A1.4556,1.4556,0,0,1,8.6294.6372l.93,1.55h2.2534A.4374.4374,0,0,1,12.25,2.625ZM10.0625,3.5H2.1875v9.1875h7.875Zm-6.125,7.5469V5.1406a.3282.3282,0,0,1,.3281-.3281h.6563a.3282.3282,0,0,1,.3281.3281v5.9063a.3282.3282,0,0,1-.3281.3281H4.2656A.3282.3282,0,0,1,3.9375,11.0469Zm.2842-8.8594H8.0283l-.4775-.7954a.1818.1818,0,0,0-.1406-.08H4.8394a.1818.1818,0,0,0-.1406.08ZM7,11.0469V5.1406a.3282.3282,0,0,1,.3281-.3281h.6563a.3282.3282,0,0,1,.3281.3281v5.9063a.3282.3282,0,0,1-.3281.3281H7.3281A.3282.3282,0,0,1,7,11.0469Z"/></svg>';
				break;
			case 'close':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="18.1213" height="18.1213" viewBox="0 0 18.1213 18.1213" stroke-miterlimit="10" stroke-width="2"><line x1="1.0607" y1="1.0607" x2="17.0607" y2="17.0607"/><line x1="17.0607" y1="1.0607" x2="1.0607" y2="17.0607"/></svg>';
				break;
			case 'add':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><rect y="6" width="14" height="2"/><rect y="6" width="14" height="2" transform="translate(0 14) rotate(-90)"/></svg>';
				break;
			case 'check':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" width="16.818" height="12.783" viewBox="0 0 16.818 12.783"><path d="M1,7l4.987,4L15,1" transform="translate(0.406 0.412)" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/></svg>';
				break;
			case 'shopping-cart':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="128" height="128"><path d="M 5 7 C 4.449219 7 4 7.449219 4 8 C 4 8.550781 4.449219 9 5 9 L 7.21875 9 L 9.84375 19.5 C 10.066406 20.390625 10.863281 21 11.78125 21 L 23.25 21 C 24.152344 21 24.917969 20.402344 25.15625 19.53125 L 27.75 10 L 11 10 L 11.5 12 L 25.15625 12 L 23.25 19 L 11.78125 19 L 9.15625 8.5 C 8.933594 7.609375 8.136719 7 7.21875 7 Z M 22 21 C 20.355469 21 19 22.355469 19 24 C 19 25.644531 20.355469 27 22 27 C 23.644531 27 25 25.644531 25 24 C 25 22.355469 23.644531 21 22 21 Z M 13 21 C 11.355469 21 10 22.355469 10 24 C 10 25.644531 11.355469 27 13 27 C 14.644531 27 16 25.644531 16 24 C 16 22.355469 14.644531 21 13 21 Z M 13 23 C 13.5625 23 14 23.4375 14 24 C 14 24.5625 13.5625 25 13 25 C 12.4375 25 12 24.5625 12 24 C 12 23.4375 12.4375 23 13 23 Z M 22 23 C 22.5625 23 23 23.4375 23 24 C 23 24.5625 22.5625 25 22 25 C 21.4375 25 21 24.5625 21 24 C 21 23.4375 21.4375 23 22 23 Z"/></svg>';
				break;
			case 'spinner':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"></path></svg>';
				break;
			case 'share':
				$html = '<svg ' . $class . ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>';
				break;
		}

		return apply_filters( 'qode_product_bundles_for_woocommerce_filter_svg_icon', $html, $name, $class_name );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_icon_html' ) ) {
	/**
	 * Function that return icon html content
	 *
	 * @param string|int $custom_icon - icon value
	 *
	 * @return string - SVG icon or Image
	 */
	function qode_product_bundles_for_woocommerce_get_icon_html( $custom_icon ) {
		$check_image_url = wp_get_attachment_url( $custom_icon );

		if ( strpos( $check_image_url, '.svg' ) !== false ) {
			// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$get_svg_content = @file_get_contents( $check_image_url );

			if ( ! empty( $get_svg_content ) ) {
				$icon_html = qode_product_bundles_for_woocommerce_framework_wp_kses_html( 'svg', $get_svg_content );
			} else {
				$icon_html = esc_html__( 'Please upload a valid SVG icon', 'qode-product-bundles-for-woocommerce' );
			}
		} else {
			$icon_html = wp_get_attachment_image( $custom_icon, 'full' );
		}

		return $icon_html;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_class_attribute' ) ) {
	/**
	 * Function that echoes class attribute
	 *
	 * @param string|array $value - value of class attribute
	 *
	 * @see qode_product_bundles_for_woocommerce_get_class_attribute()
	 */
	function qode_product_bundles_for_woocommerce_class_attribute( $value ) {
		echo wp_kses_post( qode_product_bundles_for_woocommerce_get_class_attribute( $value ) );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_class_attribute' ) ) {
	/**
	 * Function that returns generated class attribute
	 *
	 * @param string|array $value - value of class attribute
	 *
	 * @return string generated class attribute
	 *
	 * @see qode_product_bundles_for_woocommerce_get_inline_attr()
	 */
	function qode_product_bundles_for_woocommerce_get_class_attribute( $value ) {
		return qode_product_bundles_for_woocommerce_get_inline_attr( $value, 'class', ' ' );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_id_attribute' ) ) {
	/**
	 * Function that echoes id attribute
	 *
	 * @param string|array $value - value of id attribute
	 *
	 * @see qode_product_bundles_for_woocommerce_get_id_attribute()
	 */
	function qode_product_bundles_for_woocommerce_id_attribute( $value ) {
		echo wp_kses_post( qode_product_bundles_for_woocommerce_get_id_attribute( $value ) );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_id_attribute' ) ) {
	/**
	 * Function that returns generated id attribute
	 *
	 * @param string|array $value - value of id attribute
	 *
	 * @return string generated id attribute
	 *
	 * @see qode_product_bundles_for_woocommerce_get_inline_attr()
	 */
	function qode_product_bundles_for_woocommerce_get_id_attribute( $value ) {
		return qode_product_bundles_for_woocommerce_get_inline_attr( $value, 'id', ' ' );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_inline_style' ) ) {
	/**
	 * Function that echoes generated style attribute
	 *
	 * @param string|array $value - attribute value
	 *
	 * @see qode_product_bundles_for_woocommerce_get_inline_style()
	 */
	function qode_product_bundles_for_woocommerce_inline_style( $value ) {
		$inline_style_part = qode_product_bundles_for_woocommerce_get_inline_style( $value );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo qode_product_bundles_for_woocommerce_framework_wp_kses_html( 'attributes', $inline_style_part );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_inline_style' ) ) {
	/**
	 * Function that generates style attribute and returns generated string
	 *
	 * @param string|array $value - value of style attribute
	 *
	 * @return string generated style attribute
	 *
	 * @see qode_product_bundles_for_woocommerce_get_inline_style()
	 */
	function qode_product_bundles_for_woocommerce_get_inline_style( $value ) {
		return qode_product_bundles_for_woocommerce_get_inline_attr( $value, 'style', ';' );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_inline_attrs' ) ) {
	/**
	 * Echo multiple inline attributes
	 *
	 * @param array $attrs
	 * @param bool $allow_zero_values
	 */
	function qode_product_bundles_for_woocommerce_inline_attrs( $attrs, $allow_zero_values = false ) {
		$inline_attrs_part = qode_product_bundles_for_woocommerce_get_inline_attrs( $attrs, $allow_zero_values );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo qode_product_bundles_for_woocommerce_framework_wp_kses_html( 'attributes', $inline_attrs_part );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_inline_attrs' ) ) {
	/**
	 * Generate multiple inline attributes
	 *
	 * @param array $attrs
	 * @param bool $allow_zero_values
	 *
	 * @return string
	 */
	function qode_product_bundles_for_woocommerce_get_inline_attrs( $attrs, $allow_zero_values = false ) {
		$output = '';
		if ( is_array( $attrs ) && count( $attrs ) ) {
			if ( $allow_zero_values ) {
				foreach ( $attrs as $attr => $value ) {
					$output .= ' ' . qode_product_bundles_for_woocommerce_get_inline_attr( $value, $attr, '', true );
				}
			} else {
				foreach ( $attrs as $attr => $value ) {
					$output .= ' ' . qode_product_bundles_for_woocommerce_get_inline_attr( $value, $attr );
				}
			}
		}

		$output = ltrim( $output );

		return $output;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_inline_attr' ) ) {
	/**
	 * Function that generates html attribute
	 *
	 * @param string|array $value value of html attribute
	 * @param string $attr - name of html attribute to generate
	 * @param string $glue - glue with which to implode $attr. Used only when $attr is arrayed
	 * @param bool $allow_zero_values - allow data to have zero value
	 *
	 * @return string generated html attribute
	 */
	function qode_product_bundles_for_woocommerce_get_inline_attr( $value, $attr, $glue = '', $allow_zero_values = false ) {
		if ( $allow_zero_values ) {
			if ( '' !== $value ) {

				if ( is_array( $value ) && count( $value ) ) {
					$properties = implode( $glue, $value );
				} else {
					$properties = $value;
				}

				return $attr . '="' . esc_attr( $properties ) . '"';
			}
		} else {
			if ( ! empty( $value ) ) {

				if ( is_array( $value ) && count( $value ) ) {
					$properties = implode( $glue, $value );
				} elseif ( '' !== $value ) {
					$properties = $value;
				} else {
					return '';
				}

				return $attr . '="' . esc_attr( $properties ) . '"';
			}
		}

		return '';
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_string_ends_with' ) ) {
	/**
	 * Checks if $haystack ends with $needle and returns proper bool value
	 *
	 * @param string $haystack - to check
	 * @param string $needle - on end to match
	 *
	 * @return bool
	 */
	function qode_product_bundles_for_woocommerce_string_ends_with( $haystack, $needle ) {
		if ( '' !== $haystack && '' !== $needle ) {
			return ( substr( $haystack, - strlen( $needle ), strlen( $needle ) ) === $needle );
		}

		return false;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_string_ends_with_allowed_units' ) ) {
	/**
	 * Checks if $haystack ends with predefined needles and returns proper bool value
	 *
	 * @param string $haystack - to check
	 *
	 * @return bool
	 */
	function qode_product_bundles_for_woocommerce_string_ends_with_allowed_units( $haystack ) {
		$result  = false;
		$needles = array( 'px', '%', 'em', 'rem', 'vh', 'vw', ')' );

		if ( '' !== $haystack ) {
			foreach ( $needles as $needle ) {
				if ( qode_product_bundles_for_woocommerce_string_ends_with( $haystack, $needle ) ) {
					$result = true;
				}
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_dynamic_style' ) ) {
	/**
	 * Outputs css based on passed selectors and properties
	 *
	 * @param array|string $selector
	 * @param array $properties
	 *
	 * @return string
	 */
	function qode_product_bundles_for_woocommerce_dynamic_style( $selector, $properties ) {
		$output = '';
		// check if selector and rules are valid data.
		if ( ! empty( $selector ) && ( is_array( $properties ) && count( $properties ) ) ) {

			if ( is_array( $selector ) && count( $selector ) ) {
				$output .= implode( ', ', $selector );
			} else {
				$output .= $selector;
			}

			$output .= ' { ';
			foreach ( $properties as $prop => $value ) {
				if ( '' !== $prop ) {

					if ( 'font-family' === $prop ) {
						$output .= $prop . ': "' . esc_attr( $value ) . '";';
					} else {
						$output .= $prop . ': ' . esc_attr( $value ) . ';';
					}
				}
			}

			$output .= '}';
		}

		return $output;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_dynamic_style_responsive' ) ) {
	/**
	 * Outputs css based on passed selectors and properties
	 *
	 * @param array|string $selector
	 * @param array $properties
	 * @param string $min_width
	 * @param string $max_width
	 *
	 * @return string
	 */
	function qode_product_bundles_for_woocommerce_dynamic_style_responsive( $selector, $properties, $min_width = '', $max_width = '' ) {
		$output = '';
		// check if min width or max width is set.
		if ( ! empty( $min_width ) || ! empty( $max_width ) ) {
			$output .= '@media only screen';

			if ( ! empty( $min_width ) ) {
				$output .= ' and (min-width: ' . $min_width . 'px)';
			}

			if ( ! empty( $max_width ) ) {
				$output .= ' and (max-width: ' . $max_width . 'px)';
			}

			$output .= ' { ';

			$output .= qode_product_bundles_for_woocommerce_dynamic_style( $selector, $properties );

			$output .= '}';
		}

		return $output;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_pages' ) ) {
	/**
	 * Returns array of pages item
	 *
	 * @param bool $enable_default - add first element empty for default value
	 *
	 * @return array
	 */
	function qode_product_bundles_for_woocommerce_get_pages( $enable_default = false ) {
		$options = array();

		$pages = get_all_page_ids();
		if ( ! empty( $pages ) ) {

			if ( $enable_default ) {
				$options[''] = esc_html__( 'Default', 'qode-product-bundles-for-woocommerce' );
			}

			foreach ( $pages as $page_id ) {
				$options[ $page_id ] = get_the_title( $page_id );
			}
		}

		return $options;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_users' ) ) {
	/**
	 * Returns array of users
	 *
	 * @param bool $enable_default - add first element empty for default value
	 *
	 * @return array
	 */
	function qode_product_bundles_for_woocommerce_get_users( $enable_default = false ) {
		$options = array();

		$users_args = array(
			'orderby' => 'display_name',
		);
		$users      = get_users( $users_args );

		if ( ! empty( $users ) ) {

			if ( $enable_default ) {
				$options[''] = esc_html__( 'Default', 'qode-product-bundles-for-woocommerce' );
			}

			foreach ( $users as $user ) {
				$options[ $user->ID ] = $user->display_name;
			}
		}

		return $options;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_cpt_items' ) ) {
	/**
	 * Returns array of custom post items
	 *
	 * @param string $cpt_slug
	 * @param array $args
	 * @param bool $enable_default - add first element empty for default value
	 *
	 * @return array
	 */
	function qode_product_bundles_for_woocommerce_get_cpt_items( $cpt_slug = 'product', $args = array(), $enable_default = true ) {
		$options    = array();
		$query_args = array(
			'post_status'    => 'publish',
			'post_type'      => $cpt_slug,
			'posts_per_page' => '-1',
			'fields'         => 'ids',
		);

		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $value ) {
				if ( ! empty( $value ) ) {
					$query_args[ $key ] = $value;
				}
			}
		}

		$cpt_items = new WP_Query( $query_args );

		if ( $cpt_items->have_posts() ) {

			if ( $enable_default ) {
				$options[''] = esc_html__( 'Default', 'qode-product-bundles-for-woocommerce' );
			}

			foreach ( $cpt_items->posts as $id ) :
				$options[ $id ] = get_the_title( $id );
			endforeach;
		}

		wp_reset_postdata();

		return $options;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_select_type_options_pool' ) ) {
	/**
	 * Function that returns array with pool of options for select fields in framework
	 *
	 * @param string $type - type of select field
	 * @param bool $enable_default - add first element empty for default value
	 * @param array $exclude_options - array of items to exclude
	 * @param array $include_options - array of items to include
	 *
	 * @return array escaped output
	 */
	function qode_product_bundles_for_woocommerce_get_select_type_options_pool( $type, $enable_default = true, $exclude_options = array(), $include_options = array() ) {
		$options = array();

		if ( $enable_default ) {
			$options[''] = esc_html__( 'Default', 'qode-product-bundles-for-woocommerce' );
		}

		switch ( $type ) {
			case 'title_tag':
				$options['h1'] = esc_html__( 'H1', 'qode-product-bundles-for-woocommerce' );
				$options['h2'] = esc_html__( 'H2', 'qode-product-bundles-for-woocommerce' );
				$options['h3'] = esc_html__( 'H3', 'qode-product-bundles-for-woocommerce' );
				$options['h4'] = esc_html__( 'H4', 'qode-product-bundles-for-woocommerce' );
				$options['h5'] = esc_html__( 'H5', 'qode-product-bundles-for-woocommerce' );
				$options['h6'] = esc_html__( 'H6', 'qode-product-bundles-for-woocommerce' );
				$options['p']  = esc_html__( 'P', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'link_target':
				$options['_self']  = esc_html__( 'Same Window', 'qode-product-bundles-for-woocommerce' );
				$options['_blank'] = esc_html__( 'New Window', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'border_style':
				$options['solid']  = esc_html__( 'Solid', 'qode-product-bundles-for-woocommerce' );
				$options['dashed'] = esc_html__( 'Dashed', 'qode-product-bundles-for-woocommerce' );
				$options['dotted'] = esc_html__( 'Dotted', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'font_weight':
				$options['100'] = esc_html__( 'Thin (100)', 'qode-product-bundles-for-woocommerce' );
				$options['200'] = esc_html__( 'Extra Light (200)', 'qode-product-bundles-for-woocommerce' );
				$options['300'] = esc_html__( 'Light (300)', 'qode-product-bundles-for-woocommerce' );
				$options['400'] = esc_html__( 'Normal (400)', 'qode-product-bundles-for-woocommerce' );
				$options['500'] = esc_html__( 'Medium (500)', 'qode-product-bundles-for-woocommerce' );
				$options['600'] = esc_html__( 'Semi Bold (600)', 'qode-product-bundles-for-woocommerce' );
				$options['700'] = esc_html__( 'Bold (700)', 'qode-product-bundles-for-woocommerce' );
				$options['800'] = esc_html__( 'Extra Bold (800)', 'qode-product-bundles-for-woocommerce' );
				$options['900'] = esc_html__( 'Black (900)', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'font_style':
				$options['normal']  = esc_html__( 'Normal', 'qode-product-bundles-for-woocommerce' );
				$options['italic']  = esc_html__( 'Italic', 'qode-product-bundles-for-woocommerce' );
				$options['oblique'] = esc_html__( 'Oblique', 'qode-product-bundles-for-woocommerce' );
				$options['initial'] = esc_html__( 'Initial', 'qode-product-bundles-for-woocommerce' );
				$options['inherit'] = esc_html__( 'Inherit', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'text_transform':
				$options['none']       = esc_html__( 'None', 'qode-product-bundles-for-woocommerce' );
				$options['capitalize'] = esc_html__( 'Capitalize', 'qode-product-bundles-for-woocommerce' );
				$options['uppercase']  = esc_html__( 'Uppercase', 'qode-product-bundles-for-woocommerce' );
				$options['lowercase']  = esc_html__( 'Lowercase', 'qode-product-bundles-for-woocommerce' );
				$options['initial']    = esc_html__( 'Initial', 'qode-product-bundles-for-woocommerce' );
				$options['inherit']    = esc_html__( 'Inherit', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'text_decoration':
				$options['none']         = esc_html__( 'None', 'qode-product-bundles-for-woocommerce' );
				$options['underline']    = esc_html__( 'Underline', 'qode-product-bundles-for-woocommerce' );
				$options['overline']     = esc_html__( 'Overline', 'qode-product-bundles-for-woocommerce' );
				$options['line-through'] = esc_html__( 'Line-Through', 'qode-product-bundles-for-woocommerce' );
				$options['initial']      = esc_html__( 'Initial', 'qode-product-bundles-for-woocommerce' );
				$options['inherit']      = esc_html__( 'Inherit', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'columns_number':
				$options['1'] = esc_html__( 'One', 'qode-product-bundles-for-woocommerce' );
				$options['2'] = esc_html__( 'Two', 'qode-product-bundles-for-woocommerce' );
				$options['3'] = esc_html__( 'Three', 'qode-product-bundles-for-woocommerce' );
				$options['4'] = esc_html__( 'Four', 'qode-product-bundles-for-woocommerce' );
				$options['5'] = esc_html__( 'Five', 'qode-product-bundles-for-woocommerce' );
				$options['6'] = esc_html__( 'Six', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'yes_no':
				$options['yes'] = esc_html__( 'Yes', 'qode-product-bundles-for-woocommerce' );
				$options['no']  = esc_html__( 'No', 'qode-product-bundles-for-woocommerce' );
				break;
			case 'no_yes':
				$options['no']  = esc_html__( 'No', 'qode-product-bundles-for-woocommerce' );
				$options['yes'] = esc_html__( 'Yes', 'qode-product-bundles-for-woocommerce' );
				break;
		}

		if ( ! empty( $exclude_options ) ) {
			foreach ( $exclude_options as $exclude_option ) {
				if ( array_key_exists( $exclude_option, $options ) ) {
					unset( $options[ $exclude_option ] );
				}
			}
		}

		if ( ! empty( $include_options ) ) {
			foreach ( $include_options as $key => $value ) {
				if ( ! array_key_exists( $key, $options ) ) {
					$options[ $key ] = $value;
				}
			}
		}

		return apply_filters( 'qode_product_bundles_for_woocommerce_filter_select_type_option', $options, $type, $enable_default, $exclude_options );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_escape_title_tag' ) ) {
	/**
	 * Function that output escape title tag variable for modules
	 *
	 * @param string $title_tag
	 */
	function qode_product_bundles_for_woocommerce_escape_title_tag( $title_tag ) {
		echo esc_html( qode_product_bundles_for_woocommerce_get_escape_title_tag( $title_tag ) );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_escape_title_tag' ) ) {
	/**
	 * Function that return escape title tag variable for modules
	 *
	 * @param string $title_tag
	 *
	 * @return string
	 */
	function qode_product_bundles_for_woocommerce_get_escape_title_tag( $title_tag ) {
		$allowed_tags = array(
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'p',
			'span',
			'ul',
			'ol',
		);

		$escaped_title_tag = '';
		$title_tag         = strtolower( sanitize_key( $title_tag ) );

		if ( in_array( $title_tag, $allowed_tags, true ) ) {
			$escaped_title_tag = $title_tag;
		}

		return $escaped_title_tag;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_call_shortcode' ) ) {
	/**
	 * Function that call/render shortcode
	 *
	 * @param      $base - shortcode base
	 * @param      $params - shortcode parameters
	 * @param null $content - shortcode content
	 *
	 * @return mixed|string
	 */
	function qode_product_bundles_for_woocommerce_call_shortcode( $base, $params = array(), $content = null ) {
		global $shortcode_tags;

		if ( ! isset( $shortcode_tags[ $base ] ) ) {
			return false;
		}

		if ( is_array( $shortcode_tags[ $base ] ) ) {
			$shortcode = $shortcode_tags[ $base ];

			return call_user_func(
				array(
					$shortcode[0],
					$shortcode[1],
				),
				$params,
				$content,
				$base
			);
		}

		return call_user_func( $shortcode_tags[ $base ], $params, $content, $base );
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_ajax_status' ) ) {
	/**
	 * Function that return status from ajax functions
	 *
	 * @param string $status - success or error
	 * @param string $message - ajax message value
	 * @param string|array $data - returned value
	 * @param string $redirect - url address
	 */
	function qode_product_bundles_for_woocommerce_get_ajax_status( $status, $message, $data = null, $redirect = '' ) {
		$response = array(
			'status'   => esc_attr( $status ),
			'message'  => wp_kses_post( $message ),
			'data'     => $data,
			'redirect' => ! empty( $redirect ) ? esc_url( $redirect ) : '',
		);

		$output = wp_json_encode( $response );

		exit( $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_get_button_classes' ) ) {
	/**
	 * Function that return theme and plugin classes for button elements
	 *
	 * @param array $additional_classes
	 *
	 * @return string
	 */
	function qode_product_bundles_for_woocommerce_get_button_classes( $additional_classes = array() ) {
		$classes = array(
			'button',
		);

		if ( function_exists( 'wc_wp_theme_get_element_class_name' ) ) {
			$classes[] = wc_wp_theme_get_element_class_name( 'button' );
		}

		return implode( ' ', array_merge( $classes, $additional_classes ) );
	}
}
