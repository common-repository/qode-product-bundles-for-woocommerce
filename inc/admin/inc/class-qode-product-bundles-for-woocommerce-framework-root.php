<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Root {
	private static $instance;
	private $admin_options;
	private $meta_options;
	private $attachment_options;
	private $taxonomy_options;
	private $product_attribute_options;
	private $custom_post_types;
	private $shortcodes;
	private $widgets;

	private function __construct() {
		do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_framework_root_init' );

		add_action( 'after_setup_theme', array( $this, 'load_admin_pages' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_options_files' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_cpt_files' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_admin_notice_files' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_shortcode_files' ), 5 );
		add_action( 'after_setup_theme', array( $this, 'load_widget_files' ), 5 );

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_framework_root_init' );
	}

	/**
	 * Instance of module class
	 *
	 * @return Qode_Product_Bundles_For_WooCommerce_Framework_Root
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_admin_pages() {
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/admin-pages/include.php';
	}

	public function load_options_files() {
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/common/include.php';
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/fonts/include.php';

		$this->admin_options   = array();
		$admin_options_classes = apply_filters( 'qode_product_bundles_for_woocommerce_filter_framework_register_admin_options', $this->admin_options );

		if ( ! empty( $admin_options_classes ) ) {
			foreach ( $admin_options_classes as $class ) {
				$this->set_admin_option( $class );
			}
		}

		$this->meta_options              = new Qode_Product_Bundles_For_WooCommerce_Framework_Options_Meta();
		$this->attachment_options        = new Qode_Product_Bundles_For_WooCommerce_Framework_Options_Attachment();
		$this->taxonomy_options          = new Qode_Product_Bundles_For_WooCommerce_Framework_Options_Taxonomy();
		$this->product_attribute_options = new Qode_Product_Bundles_For_WooCommerce_Framework_Options_Attribute();
	}

	public function load_cpt_files() {
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/post-types/include.php';

		$this->custom_post_types = new Qode_Product_Bundles_For_WooCommerce_Framework_Custom_Post_Types();
		$this->taxonomy_options  = new Qode_Product_Bundles_For_WooCommerce_Framework_Options_Taxonomy();
	}

	public function load_admin_notice_files() {
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/admin-notice/include.php';
	}

	public function load_shortcode_files() {
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/shortcodes/include.php';

		$this->shortcodes = new Qode_Product_Bundles_For_WooCommerce_Framework_Shortcodes();
	}

	public function load_widget_files() {
		require_once QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc/widgets/include.php';

		$this->widgets = new Qode_Product_Bundles_For_WooCommerce_Framework_Widgets();
	}

	public function get_admin_options() {
		return $this->admin_options;
	}

	public function set_admin_option( Qode_Product_Bundles_For_WooCommerce_Framework_Options_Admin $options ) {
		$key                         = $options->get_options_name();
		$this->admin_options[ $key ] = $options;

		return $this->admin_options[ $key ];
	}

	public function get_admin_option( $key ) {
		if ( is_array( $key ) ) {
			$key = $key[0];
		}

		return $this->admin_options[ $key ];
	}

	public function get_meta_options() {
		return $this->meta_options;
	}

	public function get_attachment_options() {
		return $this->attachment_options;
	}

	public function get_taxonomy_options() {
		return $this->taxonomy_options;
	}

	public function get_product_attribute_options() {
		return $this->product_attribute_options;
	}

	public function get_custom_post_types() {
		return $this->custom_post_types;
	}

	public function get_custom_post_type_names() {
		$cpt_names = array();

		foreach ( (array) $this->custom_post_types as $items ) {
			foreach ( $items as $item => $value ) {
				$cpt_names[ $item ] = $item;
			}
		}

		return $cpt_names;
	}

	public function get_custom_post_type_taxonomies( $cpt_slug = '' ) {
		$taxonomies = array();

		if ( ! empty( $cpt_slug ) ) {
			$cpt_taxonomies = get_object_taxonomies( $cpt_slug );

			foreach ( $cpt_taxonomies as $cpt_taxonomy ) {
				$taxonomies[ $cpt_taxonomy ] = ucwords( str_replace( array( '-' ), array( ' ' ), $cpt_taxonomy ) );
			}
		} else {
			$cpt_names = qode_product_bundles_for_woocommerce_framework_get_framework_root()->get_custom_post_type_names();

			foreach ( $cpt_names as $cpt_name ) {
				$cpt_taxonomies = get_object_taxonomies( $cpt_name );

				foreach ( $cpt_taxonomies as $cpt_taxonomy ) {
					$taxonomies[ $cpt_taxonomy ] = ucwords( str_replace( array( '-' ), array( ' ' ), $cpt_taxonomy ) );
				}
			}
		}

		return $taxonomies;
	}

	public function add_options_page( $params ) {
		$page = false;
		if ( isset( $params['type'] ) && ! empty( $params['type'] ) ) {
			if ( 'admin' === $params['type'] ) {
				$scope = isset( $params['scope'] ) ? $params['scope'] : '';
				if ( ! empty( $scope ) ) {
					$page = new Qode_Product_Bundles_For_WooCommerce_Framework_Page_Admin( $params );
					$this->get_admin_option( $scope )->add_option_page( $page );
				}
			} elseif ( 'meta' === $params['type'] ) {
				$page = new Qode_Product_Bundles_For_WooCommerce_Framework_Page_Meta( $params );
				$this->get_meta_options()->add_option_page( $page );
			} elseif ( 'attachment' === $params['type'] ) {
				$page = new Qode_Product_Bundles_For_WooCommerce_Framework_Page_Attachment( $params );
				$this->get_attachment_options()->add_option_page( $page );
			} elseif ( 'taxonomy' === $params['type'] ) {
				$page = new Qode_Product_Bundles_For_WooCommerce_Framework_Page_Taxonomy( $params );
				$this->get_taxonomy_options()->add_option_page( $page );
			} elseif ( 'product-attribute' === $params['type'] ) {
				$page = new Qode_Product_Bundles_For_WooCommerce_Framework_Page_Attribute( $params );
				$this->get_product_attribute_options()->add_option_page( $page );
			}
		}

		return $page;
	}

	public function add_custom_post_type( Qode_Product_Bundles_For_WooCommerce_Framework_Custom_Post_Type $cpt ) {
		if ( $cpt ) {
			$this->get_custom_post_types()->add_custom_post_type( $cpt );
		}

		return $cpt;
	}

	public function get_shortcodes() {
		return $this->shortcodes;
	}

	public function get_widgets() {
		return $this->widgets;
	}

	public function add_shortcode( Qode_Product_Bundles_For_WooCommerce_Framework_Shortcode $shortcode ) {
		if ( $shortcode ) {
			$this->get_shortcodes()->add_shortcode( $shortcode );
		}

		return $shortcode;
	}

	public function add_widget( Qode_Product_Bundles_For_WooCommerce_Framework_Widget $widget ) {
		if ( $widget ) {
			$this->get_widgets()->add_widget( $widget );
		}

		return $widget;
	}
}

if ( ! function_exists( 'qode_product_bundles_for_woocommerce_framework_get_framework_root' ) ) {
	/**
	 * Main instance of Framework Root.
	 *
	 * Returns the main instance of Qode_Product_Bundles_For_WooCommerce_Framework_Root to prevent the need to use globals.
	 *
	 * @return Qode_Product_Bundles_For_WooCommerce_Framework_Root
	 * @since  1.0
	 */
	function qode_product_bundles_for_woocommerce_framework_get_framework_root() {
		return Qode_Product_Bundles_For_WooCommerce_Framework_Root::get_instance();
	}
}
