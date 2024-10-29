<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://coderockz.com
 * @since      1.0.0
 *
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/includes
 * @author     CodeRockz <coderockz1992@gmail.com>
 */
class Coderockz_Wc_Advance_Cod_Free {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Coderockz_Wc_Advance_Cod_Free_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CODEROCKZ_WC_ADVANCE_COD_FREE_VERSION' ) ) {
			$this->version = CODEROCKZ_WC_ADVANCE_COD_FREE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'coderockz-wc-advance-cod-free';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Coderockz_Wc_Advance_Cod_Free_Loader. Orchestrates the hooks of the plugin.
	 * - Coderockz_Wc_Advance_Cod_Free_i18n. Defines internationalization functionality.
	 * - Coderockz_Wc_Advance_Cod_Free_Admin. Defines all hooks for the admin area.
	 * - Coderockz_Wc_Advance_Cod_Free_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-coderockz-wc-advance-cod-free-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-coderockz-wc-advance-cod-free-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-coderockz-wc-advance-cod-free-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-coderockz-wc-advance-cod-free-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-coderockz-wc-advance-cod-free-helper.php';

		$this->loader = new Coderockz_Wc_Advance_Cod_Free_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Coderockz_Wc_Advance_Cod_Free_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Coderockz_Wc_Advance_Cod_Free_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Coderockz_Wc_Advance_Cod_Free_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'coderockz_wc_advance_cod_free_menus_sections' );
		$this->loader->add_action( 'wp_ajax_coderockz_advance_cod_restriction_settings_form', $plugin_admin, 'coderockz_advance_cod_restriction_settings_form' );
		$this->loader->add_action( 'wp_ajax_coderockz_advance_cod_user_restriction_settings_form', $plugin_admin, 'coderockz_advance_cod_user_restriction_settings_form' );
		$this->loader->add_action( 'wp_ajax_coderockz_advance_cod_cat_pro_restriction_settings_form', $plugin_admin, 'coderockz_advance_cod_cat_pro_restriction_settings_form' );
		$this->loader->add_action( 'wp_ajax_coderockz_advance_cod_shipping_restriction_settings_form', $plugin_admin, 'coderockz_advance_cod_shipping_restriction_settings_form' );
		$this->loader->add_action( 'wp_ajax_coderockz_advance_cod_vendor_restriction_settings_form', $plugin_admin, 'coderockz_advance_cod_vendor_restriction_settings_form' );
		$this->loader->add_action( 'wp_ajax_coderockz_advance_cod_localization_settings_form', $plugin_admin, 'coderockz_advance_cod_localization_settings_form' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Coderockz_Wc_Advance_Cod_Free_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_filter( 'woocommerce_available_payment_gateways', $plugin_public, 'coderockz_advance_cod_payment_gateways');
		$this->loader->add_action( 'woocommerce_checkout_update_order_review', $plugin_public, 'action_woocommerce_checkout_update_order_review');
		$this->loader->add_action('wp_ajax_coderockz_advance_cod_get_disable_cod_phone_digit', $plugin_public, 'coderockz_advance_cod_get_disable_cod_phone_digit');
		$this->loader->add_action('wp_ajax_nopriv_coderockz_advance_cod_get_disable_cod_phone_digit', $plugin_public, 'coderockz_advance_cod_get_disable_cod_phone_digit');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Coderockz_Wc_Advance_Cod_Free_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
