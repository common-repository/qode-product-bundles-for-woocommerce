<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Custom_Post_Types {

	private $custom_post_types;

	public function __construct() {
		$this->custom_post_types = array();

		add_action( 'init', array( $this, 'register' ), 0 );

		add_action( 'admin_init', array( $this, 'handle_permalink_save' ) );
	}

	public function get_custom_post_types() {
		return $this->custom_post_types;
	}

	public function get_custom_post_type( $base ) {
		return $this->custom_post_types[ $base ];
	}

	private function set_custom_post_type( Qode_Product_Bundles_For_WooCommerce_Framework_Custom_Post_Type $custom_post_type ) {
		$key                             = $custom_post_type->get_base();
		$this->custom_post_types[ $key ] = $custom_post_type;
	}

	public function custom_post_type_exists( $base ) {
		return array_key_exists( $base, $this->custom_post_types );
	}

	public function add_custom_post_type( Qode_Product_Bundles_For_WooCommerce_Framework_Custom_Post_Type $custom_post_type ) {
		if ( '' !== $custom_post_type->get_base() ) {
			$this->set_custom_post_type( $custom_post_type );

			return $custom_post_type;
		}

		return false;
	}

	public function register() {
		do_action( 'qode_product_bundles_for_woocommerce_framework_action_before_post_types_register' );

		foreach ( $this->custom_post_types as $custom_post_type ) {
			$custom_post_type->register();
		}

		$this->rewrite_rules();

		do_action( 'qode_product_bundles_for_woocommerce_framework_action_after_post_types_register' );
	}

	public function rewrite_rules( $reinit = false ) {
		$permalinks_updated = (bool) get_option( 'qode_product_bundles_for_woocommerce_framework_permalinks_updated', false );

		if ( $reinit ) {
			flush_rewrite_rules();
		} elseif ( ! $permalinks_updated ) {
			update_option( 'qode_product_bundles_for_woocommerce_framework_permalinks_updated', true );

			flush_rewrite_rules();
		}
	}

	public function handle_permalink_save() {

		if ( isset( $_POST['permalink_structure'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-permalink' ) ) {

			$permalinks      = (array) get_option( 'qode_product_bundles_for_woocommerce_framework_permalinks', array() );
			$mods_to_rewrite = $this->custom_post_types;

			foreach ( $this->custom_post_types as $post_type ) {
				$post_type_taxonomies = $post_type->get_taxonomies();
				if ( ! empty( $post_type_taxonomies ) ) {
					$mods_to_rewrite = array_merge( $mods_to_rewrite, $post_type_taxonomies );
				}
			}

			foreach ( $mods_to_rewrite as $mod ) {
				$name = $mod->get_slug_setting_name();
				if ( isset( $_POST[ $name ] ) ) {
					$permalinks[ $name ] = sanitize_text_field( wp_unslash( $_POST[ $name ] ) );
				}
			}

			update_option( 'qode_product_bundles_for_woocommerce_framework_permalinks', $permalinks );

			$this->rewrite_rules( true );
		}
	}
}
