<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://coderockz.com
 * @since             1.0.0
 * @package           Coderockz_Wc_Advance_Cod_Free
 *
 * @wordpress-plugin
 * Plugin Name:       Advance Cash On Delivery For WooCommerce
 * Plugin URI:        https://coderockz.com/downloads/woocommerce-advance-cash-on-delivery/
 * Description:       Extends WooCommerce Cash on Delivery(COD) payment gateway and hiding COD, adding extra fee and taking advance fee for certain conditions.
 * Version:           1.0.6
 * Author:            CodeRockz
 * Author URI:        https://coderockz.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       coderockz-wc-advance-cod-free
 * Domain Path:       /languages
 * WC tested up to:   6.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if(!defined("CODEROCKZ_WC_ADVANCE_COD_FREE_DIR"))
    define("CODEROCKZ_WC_ADVANCE_COD_FREE_DIR",plugin_dir_path(__FILE__));
if(!defined("CODEROCKZ_WC_ADVANCE_COD_FREE_URL"))
    define("CODEROCKZ_WC_ADVANCE_COD_FREE_URL",plugin_dir_url(__FILE__));
if(!defined("CODEROCKZ_WC_ADVANCE_COD_FREE"))
    define("CODEROCKZ_WC_ADVANCE_COD_FREE",plugin_basename(__FILE__));

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CODEROCKZ_WC_ADVANCE_COD_FREE_VERSION', '1.0.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-coderockz-wc-advance-cod-free-activator.php
 */
function activate_coderockz_wc_advance_cod_free() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-coderockz-wc-advance-cod-free-activator.php';
	Coderockz_Wc_Advance_Cod_Free_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-coderockz-wc-advance-cod-free-deactivator.php
 */
function deactivate_coderockz_wc_advance_cod_free() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-coderockz-wc-advance-cod-free-deactivator.php';
	Coderockz_Wc_Advance_Cod_Free_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_coderockz_wc_advance_cod_free' );
register_deactivation_hook( __FILE__, 'deactivate_coderockz_wc_advance_cod_free' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-coderockz-wc-advance-cod-free.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_coderockz_wc_advance_cod_free() {

	$plugin = new Coderockz_Wc_Advance_Cod_Free();
	$plugin->run();

}
run_coderockz_wc_advance_cod_free();
