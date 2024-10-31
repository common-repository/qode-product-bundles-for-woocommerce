<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

class Qode_Product_Bundles_For_WooCommerce_Framework_Options_Admin extends Qode_Product_Bundles_For_WooCommerce_Framework_Options {
	private $menu_name;
	private $options_name;
	private $menu_label;
	private $icon_path;

	public function __construct( $menu_name = 'qode_product_bundles_for_woocommerce_framework_menu', $options_name = 'qode_product_bundles_for_woocommerce_framework_options', $params = array() ) {
		parent::__construct();

		$this->menu_name    = $menu_name;
		$this->options_name = $options_name;

		$this->menu_label = ! empty( $params['label'] ) ? $params['label'] : esc_html__( 'Qode Options', 'qode-product-bundles-for-woocommerce' );
		$this->icon_path  = ! empty( $params['icon_path'] ) ? $params['icon_path'] : QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_URL_PATH . '/inc/common/modules/admin/assets/img/admin-logo-icon.png';

		add_action( 'init', array( $this, 'init_options' ), 11 );

		add_action( 'admin_menu', array( $this, 'framework_menu' ) );

		// 999 is set to be at the last place in admin bar.
		add_action( 'admin_bar_menu', array( $this, 'framework_admin_bar_menu' ), 999 );

		add_action( 'wp_ajax_qode_product_bundles_for_woocommerce_action_framework_save_options_' . $this->get_options_name(), array( $this, 'save_options' ) );

		add_action( 'wp_ajax_qode_product_bundles_for_woocommerce_action_framework_reset_options_' . $this->get_options_name(), array( $this, 'reset_options' ) );

		// 5 is set to be same permission as Gutenberg plugin have.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_framework_options_scripts' ), 5 );

		add_filter( 'admin_body_class', array( $this, 'add_admin_body_classes' ) );

		add_action( 'all_admin_notices', array( $this, 'init_cpt_header' ) );
		add_filter( 'post_row_actions', array( $this, 'modify_post_row_actions' ), 10, 1 );
	}

	public function get_menu_name() {
		return $this->menu_name;
	}

	public function get_options_name() {
		return $this->options_name;
	}

	public function get_menu_label() {
		return $this->menu_label;
	}

	public function get_icon_path() {
		return $this->icon_path;
	}

	public function framework_menu() {

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_options_map', $this->get_menu_name() );

		$child_elements = $this->get_child_elements();

		if ( ! empty( $child_elements ) ) {
			add_submenu_page(
				QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_GENERAL_MENU_NAME,
				$this->get_menu_label(),
				$this->get_menu_label(),
				'edit_theme_options',
				$this->get_menu_name(),
				array(
					$this,
					'render_options',
				),
				20
			);
		}
		remove_submenu_page( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_GENERAL_MENU_NAME, QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_GENERAL_MENU_NAME );
		do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_options_map', $this->get_menu_name() );
	}

	public function framework_admin_bar_menu( $wp_admin_bar ) {
		$menu_name = $this->get_menu_name();

		if ( current_user_can( 'edit_theme_options' ) && ! empty( $menu_name ) && ! is_admin() ) {
			$menu_id = esc_attr( str_replace( '_', '-', $menu_name ) . '-admin-bar-options' );

			$args = array(
				'id'    => $menu_id,
				'title' => sprintf( '<span class="ab-icon dashicons-before dashicons-admin-generic"></span> %s', esc_html( $this->get_menu_label() ) ),
				'href'  => esc_url( admin_url( 'admin.php?page=' . esc_attr( $menu_name ) ) ),
			);

			$wp_admin_bar->add_node( $args );
		}
	}

	public function init_options() {

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_options_init_' . $this->get_options_name(), $this->get_options_name() );

		if ( ! get_option( $this->get_options_name() ) ) {
			add_option( $this->get_options_name(), $this->get_options() );
		}

		$this->populate_options();

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_options_init_' . $this->get_options_name(), $this->get_options_name() );
	}

	public function populate_options() {

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_options_populate', $this->get_options_name() );

		$db_options = get_option( $this->get_options_name() );

		if ( is_array( $db_options ) && ! empty( $db_options ) ) {
			$this->set_options( array_merge( $this->get_options(), get_option( $this->get_options_name() ) ) );
		}

		$this->register_options();

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_options_populate', $this->get_options_name() );
	}

	public function register_options() {

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_options_registered', $this->get_options_name() );

		register_setting(
			$this->get_menu_label(),
			$this->get_options_name(),
			array(
				'sanitize_callback' => array( $this, 'options_validate' ),
			)
		);

		do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_options_registered', $this->get_options_name() );
	}

	/**
	 * Callback function called by Register Settings function to validate options
	 *
	 * @param array $fields The field to validate.
	 *
	 * @return array validated fields
	 */
	public function options_validate( $fields ) {
		// Get global options from db.
		$saved_global_options = $this->get_options();

		// Helper functions to sanitize whole object recursive.
		return qode_product_bundles_for_woocommerce_framework_clean_global_options( qode_product_bundles_for_woocommerce_framework_map_deep_sanitize( wp_unslash( $fields ) ), $saved_global_options );
	}

	public function save_options() {

		if ( current_user_can( 'edit_theme_options' ) ) {
			// Get global options from db.
			$saved_global_options = $this->get_options();

			// Helper functions to sanitize whole request object and remove unnecessary fields from it.
			$new_global_options = qode_product_bundles_for_woocommerce_framework_clean_global_options( qode_product_bundles_for_woocommerce_framework_map_deep_sanitize( wp_unslash( $_REQUEST ) ), $saved_global_options );

			unset( $new_global_options['action'] );

			check_ajax_referer( 'qode_product_bundles_for_woocommerce_framework_ajax_save_nonce', 'qode_product_bundles_for_woocommerce_framework_ajax_save_nonce' );

			$options_name = isset( $new_global_options['options_name'] ) ? sanitize_text_field( wp_unslash( $new_global_options['options_name'] ) ) : '';
			unset( $new_global_options['options_name'] );

			if ( $options_name === $this->get_options_name() ) {

				do_action( 'qode_product_bundles_for_woocommerce_action_framework_before_framework_option_save', $saved_global_options, $new_global_options );

				$this->set_options( array_merge( $saved_global_options, $new_global_options ) );

				update_option( $this->get_options_name(), $this->get_options() );

				esc_html_e( 'Saved', 'qode-product-bundles-for-woocommerce' );

				do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_framework_option_save' );

			} else {
				esc_html_e( 'Wrong options trigger', 'qode-product-bundles-for-woocommerce' );
			}

			die();
		}
	}

	public function reset_options() {
		if ( current_user_can( 'edit_theme_options' ) ) {

			if ( isset( $_REQUEST['action'] ) ) {
				unset( $_REQUEST['action'] );
			}

			check_ajax_referer( 'qode_product_bundles_for_woocommerce_framework_ajax_save_nonce', 'qode_product_bundles_for_woocommerce_framework_ajax_save_nonce' );

			$options_name = isset( $_REQUEST['options_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['options_name'] ) ) : '';

			if ( $options_name === $this->get_options_name() ) {

				delete_option( $this->get_options_name() );

				esc_html_e( 'Options reset to default', 'qode-product-bundles-for-woocommerce' );

				do_action( 'qode_product_bundles_for_woocommerce_action_framework_after_framework_option_reset' );

			} else {
				esc_html_e( 'Wrong options trigger', 'qode-product-bundles-for-woocommerce' );
			}

			die();
		}
	}

	public function render_options() {
		$params                 = array();
		$params['options']      = $this;
		$params['options_name'] = $this->get_options_name();
		// phpcs:ignore WordPress.Security.NonceVerification
		$custom_holder = isset( $_GET['template'] ) ? 'custom' : '';
		// phpcs:ignore WordPress.Security.NonceVerification
		$params['custom_holder_slug'] = isset( $_GET['template'] ) ? sanitize_text_field( wp_unslash( $_GET['template'] ) ) : '';

		qode_product_bundles_for_woocommerce_framework_template_part( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH, 'inc/common', 'modules/admin/templates/holder', $custom_holder, $params );
	}

	public function render_navigation() {
		$params                 = array();
		$params['pages']        = $this->get_child_elements();
		$params['options_name'] = $this->get_options_name();
		$params['menu_label']   = $this->get_menu_label();
		$params['use_icons']    = false;

		qode_product_bundles_for_woocommerce_framework_template_part( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH, 'inc/common', 'modules/admin/templates/navigation', '', $params );
	}

	public function render_content() {
		$params                 = array();
		$pages                  = $this->get_child_elements();
		$params['pages']        = $pages;
		$params['options_name'] = $this->get_options_name();

		qode_product_bundles_for_woocommerce_framework_template_part( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH, 'inc/common', 'modules/admin/templates/content', '', $params );
	}

	public function render_custom_content() {
		$params                 = array();
		$pages                  = $this->get_child_elements();
		$params['pages']        = $pages;
		$params['options_name'] = $this->get_options_name();

		qode_product_bundles_for_woocommerce_framework_template_part( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_PATH, 'inc/common', 'modules/admin/templates/content', 'custom', $params );
	}

	public function enqueue_framework_options_scripts() {
		if ( $this->allowed_screens() ) {
			$this->enqueue_dashboard_framework_scripts();
		}
	}

	public function add_admin_body_classes( $classes ) {
		if ( $this->allowed_screens() ) {
			$classes = $classes . ' qodef-framework-admin';
		}

		return $classes;
	}

	public function allowed_screens() {
		// check if page is options page.
		// phpcs:ignore WordPress.Security.NonceVerification
		return ( isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), $this->get_menu_name() ) !== false ) || $this->is_allowed_admin_cpt_page();
	}

	public function init_cpt_header() {
		if ( $this->is_allowed_admin_cpt_page() ) {
			?>
			<div class="qodef-admin-page-v4 qodef-cpt-page qodef-admin-content">
				<?php $this->render_navigation(); ?>
				<div class="qodef-admin-content-wrapper">
					<div class="qodef-admin-header">
						<div class="qodef-header-left">
							<div class="qodef-header-left-inner">
								<a href="#" class="qodef-mobile-nav-opener">
									<?php qode_product_bundles_for_woocommerce_framework_svg_icon( 'opener', 'qodef-opener-icon' ); ?>
								</a>
								<div class="qodef-logo-holder">
									<a href="https://qodeinteractive.com/products/plugins/" target="_blank">
										<img src="<?php echo esc_url( QODE_PRODUCT_BUNDLES_FOR_WOOCOMMERCE_ADMIN_URL_PATH . '/inc/common/modules/admin/assets/img/logo-qode-interactive.png' ); ?>" alt="<?php esc_attr_e( 'Admin Qode Interactive image', 'qode-product-bundles-for-woocommerce' ); ?>" height="47" />
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
	/**
	 * Function that modifies post row actions
	 *
	 * @return array Array of table row actions
	 */
	public function modify_post_row_actions( $actions ) {
		if ( $this->is_allowed_admin_cpt_page() ) {
			$new_actions = array();

			// Loop through existing actions.
			foreach ( $actions as $action => $link ) {
				$svg_icon = qode_product_bundles_for_woocommerce_get_svg_icon( $action );

				// If there is a svg with said action, add it before closing tag.
				if ( ! empty( $svg_icon ) ) {
					$pattern                = '/(<\/a\s*>)/i';
					$replacement            = qode_product_bundles_for_woocommerce_get_svg_icon( $action, 'qodef-table-action-btn' ) . '$1';
					$new_actions[ $action ] = preg_replace( $pattern, $replacement, $link );
				} elseif ( 'inline hide-if-no-js' === $action ) {
					$pattern                = '/(<\/button\s*>)/i';
					$replacement            = qode_product_bundles_for_woocommerce_get_svg_icon( 'quick-edit', 'qodef-table-action-btn' ) . '$1';
					$new_actions[ $action ] = preg_replace( $pattern, $replacement, $link );
				} else {
					// Change nothing if there is no corresponding svg.
					$new_actions[ $action ] = $link;
				}
			}

			return $new_actions;
		}

		return $actions;
	}

	public function is_allowed_admin_cpt_page() {
		global $pagenow;

		$pages      = apply_filters( 'qode_product_bundles_for_woocommerce_filter_framework_nav_pages', array( 'post.php', 'post-new.php', 'edit.php', 'edit-tags.php', 'term.php' ) );
		$cpts       = apply_filters( 'qode_product_bundles_for_woocommerce_filter_framework_nav_cpts', array() );
		$taxonomies = apply_filters( 'qode_product_bundles_for_woocommerce_filter_framework_nav_taxonomies', array() );

		// phpcs:ignore WordPress.Security.NonceVerification
		$current_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : get_post_type();

		// phpcs:ignore WordPress.Security.NonceVerification
		$current_taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : get_current_screen()->taxonomy;

		return is_admin() && in_array( $pagenow, $pages, true ) && ( in_array( $current_post_type, $cpts, true ) || in_array( $current_taxonomy, $taxonomies, true ) );
	}
}
