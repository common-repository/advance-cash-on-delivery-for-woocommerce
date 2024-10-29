<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://coderockz.com
 * @since      1.0.0
 *
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/admin
 * @author     CodeRockz <coderockz1992@gmail.com>
 */
class Coderockz_Wc_Advance_Cod_Free_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $helper;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->helper = new Coderockz_Wc_Advance_Cod_Helper();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Coderockz_Wc_Advance_Cod_Free_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Coderockz_Wc_Advance_Cod_Free_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'jquery-ui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );

		if($this->helper->detect_plugin_settings_page()) {
			wp_enqueue_style( 'advance_cod_selectize_css',  plugin_dir_url(__FILE__) . 'css/selectize.min.css', array(),$this->version);
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/coderockz-wc-advance-cod-free-admin.css', array(), $this->version, 'all' );
		}

		

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Coderockz_Wc_Advance_Cod_Free_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Coderockz_Wc_Advance_Cod_Free_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-effects-slide' );
		wp_enqueue_script( 'jquery-ui-accordion' );

		if($this->helper->detect_plugin_settings_page()) {
			wp_enqueue_script( "advance_cod_selectize_js", plugin_dir_url(__FILE__) . 'js/selectize.min.js', array( 'jquery' ), $this->version, true);
			wp_enqueue_script( "advance_cod_anime_js", plugin_dir_url( __FILE__ ) . 'js/anime.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name."_js_plugin", plugin_dir_url( __FILE__ ) . 'js/coderockz-advance-cod-admin-js-plugin.js', array( 'jquery', 'advance_cod_selectize_js' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/coderockz-wc-advance-cod-free-admin.js', array( 'jquery', "advance_cod_anime_js" ), $this->version, true );
		}

		

		$coderockz_advance_cod_nonce = wp_create_nonce('coderockz_advance_cod_nonce');
	    wp_localize_script($this->plugin_name, 'coderockz_advance_cod_ajax_obj', array(
            'coderockz_advance_cod_ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $coderockz_advance_cod_nonce,
        ));

	}

	public function coderockz_wc_advance_cod_free_menus_sections() {

        if(current_user_can( 'manage_woocommerce' )) {
        	add_menu_page(
				__('Advance COD', 'coderockz-advance-cod'),
	            __('Advance COD', 'coderockz-advance-cod'),
				'view_woocommerce_reports',
				'coderockz-wc-advance-cod-free-settings',
				array($this, "coderockz_wc_advance_cod_free_main_layout"),
				"dashicons-money-alt",
				null
			);
        }   
    }

    public function coderockz_advance_cod_restriction_settings_form() { 
    	check_ajax_referer('coderockz_advance_cod_nonce');
		
		$restriction_form_settings = [];

		$restriction_form_data = $this->helper->coderockz_advance_cod_array_sanitize($_POST[ 'restrictionSettingsData' ]);

		$disable_cod_greater_than = sanitize_text_field($restriction_form_data['coderockz_advance_cod_disable_cod_greater_than']);
		$disable_cod_less_than = sanitize_text_field($restriction_form_data['coderockz_advance_cod_disable_cod_less_than']);

		$calculating_include_discount = !isset($restriction_form_data['coderockz_advance_cod_calculating_include_discount']) ? false : true;
		$calculating_include_tax = !isset($restriction_form_data['coderockz_advance_cod_calculating_include_tax']) ? false : true;
		$calculating_include_shipping_cost = !isset($restriction_form_data['coderockz_advance_cod_calculating_include_shipping_cost']) ? false : true;

		$cod_disabled_discount_coupons = (isset($restriction_form_data['coderockz_advance_cod_disabled_discount_coupons']) && !empty($restriction_form_data['coderockz_advance_cod_disabled_discount_coupons'])) ? $this->helper->coderockz_advance_cod_array_sanitize($restriction_form_data['coderockz_advance_cod_disabled_discount_coupons']) : array();

		
		$restriction_form_settings['disable_cod_greater_than'] = $disable_cod_greater_than;
		$restriction_form_settings['disable_cod_less_than'] = $disable_cod_less_than;
		$restriction_form_settings['calculating_include_discount'] = $calculating_include_discount;
		$restriction_form_settings['calculating_include_tax'] = $calculating_include_tax;
		$restriction_form_settings['calculating_include_shipping_cost'] = $calculating_include_shipping_cost;
		$restriction_form_settings['cod_disabled_discount_coupons'] = $cod_disabled_discount_coupons;

		
		if(get_option('coderockz_advance_cod_restriction_settings') == false) {
			update_option('coderockz_advance_cod_restriction_settings', $restriction_form_settings);
		} else {
			$restriction_form_settings = array_merge(get_option('coderockz_advance_cod_restriction_settings'),$restriction_form_settings);
			update_option('coderockz_advance_cod_restriction_settings', $restriction_form_settings);
		}

		wp_send_json_success();
		
    }

    public function coderockz_advance_cod_user_restriction_settings_form() { 
    	check_ajax_referer('coderockz_advance_cod_nonce');
		
		$user_restriction_form_settings = [];

		$user_restriction_form_data = $this->helper->coderockz_advance_cod_array_sanitize($_POST[ 'userRestrictionSettingsData' ]);

		$disable_cod_user_emails = isset($user_restriction_form_data['coderockz_advance_cod_disable_user_email']) && $user_restriction_form_data['coderockz_advance_cod_disable_user_email'] != "" ? sanitize_textarea_field($user_restriction_form_data['coderockz_advance_cod_disable_user_email']) : "";
		if($disable_cod_user_emails != "") {
			while (strpos($disable_cod_user_emails, ', ') !== FALSE) {
			    $disable_cod_user_emails = str_replace(', ', ',', $disable_cod_user_emails);
			}
			$disable_cod_user_emails = preg_replace('#\s+#',',',trim($disable_cod_user_emails));
			$disable_cod_user_emails = explode(",",$disable_cod_user_emails);
			$disable_cod_user_emails = $this->helper->coderockz_advance_cod_array_sanitize($disable_cod_user_emails);
		} else {
			$disable_cod_user_emails = [];
		}

		/*$disable_cod_user_names = isset($user_restriction_form_data['coderockz_advance_cod_disable_user_name']) && $user_restriction_form_data['coderockz_advance_cod_disable_user_name'] != "" ? sanitize_textarea_field($user_restriction_form_data['coderockz_advance_cod_disable_user_name']) : "";
		while (strpos($disable_cod_user_names, ', ') !== FALSE) {
		    $disable_cod_user_names = str_replace(', ', ',', $disable_cod_user_names);
		}
		$disable_cod_user_names = explode(",",$disable_cod_user_names);
		$disable_cod_user_names = $this->helper->coderockz_advance_cod_array_sanitize($disable_cod_user_names);*/

		$disable_cod_user_phone = isset($user_restriction_form_data['coderockz_advance_cod_disable_user_phone']) && $user_restriction_form_data['coderockz_advance_cod_disable_user_phone'] != "" ? sanitize_textarea_field($user_restriction_form_data['coderockz_advance_cod_disable_user_phone']) : "";
		if($disable_cod_user_phone != "") {
			while (strpos($disable_cod_user_phone, ', ') !== FALSE) {
			    $disable_cod_user_phone = str_replace(', ', ',', $disable_cod_user_phone);
			}
			$disable_cod_user_phone = preg_replace('#\s+#',',',trim($disable_cod_user_phone));
			$disable_cod_user_phone = explode(",",$disable_cod_user_phone);
			$disable_cod_user_phone = $this->helper->coderockz_advance_cod_array_sanitize($disable_cod_user_phone);
		} else {
			$disable_cod_user_phone = [];
		}
		

		$disable_user_roles = (isset($user_restriction_form_data['coderockz_advance_cod_disable_user_roles']) && !empty($user_restriction_form_data['coderockz_advance_cod_disable_user_roles'])) ? $this->helper->coderockz_advance_cod_array_sanitize($user_restriction_form_data['coderockz_advance_cod_disable_user_roles']) : array();
		
		/*$user_restriction_form_settings['disable_cod_user_names'] = $disable_cod_user_names;*/
		$user_restriction_form_settings['disable_cod_user_emails'] = $disable_cod_user_emails;
		$user_restriction_form_settings['disable_cod_user_phone'] = $disable_cod_user_phone;
		$user_restriction_form_settings['disable_user_roles'] = $disable_user_roles;


		
		if(get_option('coderockz_advance_cod_restriction_settings') == false) {
			update_option('coderockz_advance_cod_restriction_settings', $user_restriction_form_settings);
		} else {
			$user_restriction_form_settings = array_merge(get_option('coderockz_advance_cod_restriction_settings'),$user_restriction_form_settings);
			update_option('coderockz_advance_cod_restriction_settings', $user_restriction_form_settings);
		}

		wp_send_json_success();
		
    }

    public function coderockz_advance_cod_cat_pro_restriction_settings_form() { 
    	check_ajax_referer('coderockz_advance_cod_nonce');
		
		$cat_pro_restriction_form_settings = [];

		$cat_pro_restriction_form_data = $this->helper->coderockz_advance_cod_array_sanitize($_POST[ 'catProRestrictionSettingsData' ]);

		$restrict_cod_categories = (isset($cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_categories']) && !empty($cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_categories'])) ? $cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_categories'] : array();
		$restrict_cod_categories = $this->helper->coderockz_advance_cod_array_sanitize($restrict_cod_categories);

		$cat_pro_restriction_form_settings['restrict_cod_categories'] = $restrict_cod_categories;
		
		$restrict_cod_products = isset($cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_individual_product_input']) && $cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_individual_product_input'] != "" ? $cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_individual_product_input'] : "";
		$restrict_cod_products = explode(",",$restrict_cod_products);
		$restrict_cod_products = $this->helper->coderockz_advance_cod_array_sanitize($restrict_cod_products);

		$cat_pro_restriction_form_settings['restrict_cod_products'] = $restrict_cod_products;

		
		$disable_cod_sku = isset($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_sku']) && $cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_sku'] != "" ? sanitize_textarea_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_sku']) : "";
		if($disable_cod_sku != "") {
			while (strpos($disable_cod_sku, ', ') !== FALSE) {
			    $disable_cod_sku = str_replace(', ', ',', $disable_cod_sku);
			}

			$disable_cod_sku = preg_replace('#\s+#',',',trim($disable_cod_sku));

			$disable_cod_sku = explode(",",$disable_cod_sku);
			$disable_cod_sku = $this->helper->coderockz_advance_cod_array_sanitize($disable_cod_sku);
		} else {
			$disable_cod_sku = [];
		}

		$cat_pro_restriction_form_settings['disable_cod_sku'] = $disable_cod_sku;


		$cod_reverse_current_condition = !isset($cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_reverse_current_condition']) ? false : true;

		$cat_pro_restriction_form_settings['cod_reverse_current_condition'] = $cod_reverse_current_condition;

		$restrict_cod_virtual_downloadable_products = !isset($cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_virtual_downloadable_products']) ? false : true;

		$cat_pro_restriction_form_settings['restrict_cod_virtual_downloadable_products'] = $restrict_cod_virtual_downloadable_products;

		$restrict_cod_backorder_products = !isset($cat_pro_restriction_form_data['coderockz_advance_cod_restrict_cod_backorder_products']) ? false : true;

		$cat_pro_restriction_form_settings['restrict_cod_backorder_products'] = $restrict_cod_backorder_products;

		$disable_cod_stock_greater_than = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_stock_greater_than']);
		$disable_cod_stock_less_than = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_stock_less_than']);

		$cat_pro_restriction_form_settings['disable_cod_stock_greater_than'] = $disable_cod_stock_greater_than;
		$cat_pro_restriction_form_settings['disable_cod_stock_less_than'] = $disable_cod_stock_less_than;

		$disable_cod_quantity_greater_than = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_quantity_greater_than']);
		$disable_cod_quantity_less_than = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_quantity_less_than']);

		$disable_cod_product_quantity_based_on = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_product_quantity_based_on']);

		$cat_pro_restriction_form_settings['disable_cod_quantity_greater_than'] = $disable_cod_quantity_greater_than;
		$cat_pro_restriction_form_settings['disable_cod_quantity_less_than'] = $disable_cod_quantity_less_than;
		$cat_pro_restriction_form_settings['disable_cod_product_quantity_based_on'] = $disable_cod_product_quantity_based_on;

		$disable_cod_weight_greater_than = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_weight_greater_than']);
		$disable_cod_weight_less_than = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_weight_less_than']);
		$disable_cod_product_weight_based_on = sanitize_text_field($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_product_weight_based_on']);
		$disable_cod_product_weight_quantity_consider = !isset($cat_pro_restriction_form_data['coderockz_advance_cod_disable_cod_product_weight_quantity_consider']) ? false : true;

		$cat_pro_restriction_form_settings['disable_cod_weight_greater_than'] = $disable_cod_weight_greater_than;
		$cat_pro_restriction_form_settings['disable_cod_weight_less_than'] = $disable_cod_weight_less_than;
		$cat_pro_restriction_form_settings['disable_cod_product_weight_based_on'] = $disable_cod_product_weight_based_on;
		$cat_pro_restriction_form_settings['disable_cod_product_weight_quantity_consider'] = $disable_cod_product_weight_quantity_consider;

		
		if(get_option('coderockz_advance_cod_restriction_settings') == false) {
			update_option('coderockz_advance_cod_restriction_settings', $cat_pro_restriction_form_settings);
		} else {
			$cat_pro_restriction_form_settings = array_merge(get_option('coderockz_advance_cod_restriction_settings'),$cat_pro_restriction_form_settings);
			update_option('coderockz_advance_cod_restriction_settings', $cat_pro_restriction_form_settings);
		}

		wp_send_json_success();
		
    }

    public function coderockz_advance_cod_shipping_restriction_settings_form() { 
    	check_ajax_referer('coderockz_advance_cod_nonce');
		
		$zone_restriction_form_settings = [];

		$zone_restriction_form_data = $this->helper->coderockz_advance_cod_array_sanitize($_POST[ 'zoneSettingsData' ]);

		$region_zone_code = (isset($zone_restriction_form_data['coderockz_advance_cod_cod_disabled_zone']) && !empty($zone_restriction_form_data['coderockz_advance_cod_cod_disabled_zone'])) ? $zone_restriction_form_data['coderockz_advance_cod_cod_disabled_zone'] : array();
		$zone_state_code = [];
		$zone_post_code = [];
		foreach($region_zone_code as $zone_code) {
			
			$zone = new WC_Shipping_Zone($zone_code);

			$zone_locations = $zone->get_zone_locations();
			$zone_locations = $this->helper->objectToArray($zone_locations);
			foreach($zone_locations as $zone_location) {
				if($zone_location['type'] == "state") {
					$position = strpos($zone_location['code'],':');
					$zone_state_code[] = substr($zone_location['code'],($position+1));
				} else if($zone_location['type'] == "postcode") {
					$zone_post_code[] = $zone_location['code'];
				} else if($zone_location['type'] == "country") {
					$zone_state_code[] = $zone_location['code'];
				}
			}
		}

		$region_state_code = $zone_state_code;
		$region_post_code = $zone_post_code;

		$region_zone_code = $this->helper->coderockz_advance_cod_array_sanitize($region_zone_code);
		$region_state_code = $this->helper->coderockz_advance_cod_array_sanitize($region_state_code);
		$region_post_code = $this->helper->coderockz_advance_cod_array_sanitize($region_post_code);

		$zone_restriction_form_settings['cod_disabled_zones'] = $region_zone_code;
		$zone_restriction_form_settings['cod_disabled_states'] = $region_state_code;
		$zone_restriction_form_settings['cod_disabled_zip'] = $region_post_code;

		$cod_enabled_country = (isset($zone_restriction_form_data['coderockz_advance_cod_cod_enabled_country']) && !empty($zone_restriction_form_data['coderockz_advance_cod_cod_enabled_country'])) ? $this->helper->coderockz_advance_cod_array_sanitize($zone_restriction_form_data['coderockz_advance_cod_cod_enabled_country']) : array();
		$zone_restriction_form_settings['cod_enabled_country'] = $cod_enabled_country;

		$cod_enabled_states = (isset($zone_restriction_form_data['coderockz_advance_cod_cod_enabled_states']) && !empty($zone_restriction_form_data['coderockz_advance_cod_cod_enabled_states'])) ? $this->helper->coderockz_advance_cod_array_sanitize($zone_restriction_form_data['coderockz_advance_cod_cod_enabled_states']) : array();
		$zone_restriction_form_settings['cod_enabled_states'] = $cod_enabled_states;

		$enable_cod_zip = isset($zone_restriction_form_data['coderockz_advance_cod_enable_cod_zip']) && $zone_restriction_form_data['coderockz_advance_cod_enable_cod_zip'] != "" ? sanitize_textarea_field($zone_restriction_form_data['coderockz_advance_cod_enable_cod_zip']) : "";
		if($enable_cod_zip != "") {
			while (strpos($enable_cod_zip, ', ') !== FALSE) {
			    $enable_cod_zip = str_replace(', ', ',', $enable_cod_zip);
			}

			$enable_cod_zip = preg_replace('#\s+#',',',trim($enable_cod_zip));
			
			$enable_cod_zip = explode(",",$enable_cod_zip);
			$enable_cod_zip = $this->helper->coderockz_advance_cod_array_sanitize($enable_cod_zip);
		} else {
			$enable_cod_zip = [];
		}
		
		

		$zone_restriction_form_settings['enable_cod_zip'] = $enable_cod_zip;

		$cod_disabled_shipping_methods = (isset($zone_restriction_form_data['coderockz_advance_cod_disabled_shipping_methods']) && !empty($zone_restriction_form_data['coderockz_advance_cod_disabled_shipping_methods'])) ? $this->helper->coderockz_advance_cod_array_sanitize($zone_restriction_form_data['coderockz_advance_cod_disabled_shipping_methods']) : array();
		$zone_restriction_form_settings['cod_disabled_shipping_methods'] = $cod_disabled_shipping_methods;

		$disable_cod_shipping_method_value = isset($zone_restriction_form_data['coderockz_advance_cod_disable_cod_shipping_method_value']) && $zone_restriction_form_data['coderockz_advance_cod_disable_cod_shipping_method_value'] != "" ? sanitize_textarea_field($zone_restriction_form_data['coderockz_advance_cod_disable_cod_shipping_method_value']) : "";
		if($disable_cod_shipping_method_value != "") {
			while (strpos($disable_cod_shipping_method_value, ', ') !== FALSE) {
			    $disable_cod_shipping_method_value = str_replace(', ', ',', $disable_cod_shipping_method_value);
			}

			$disable_cod_shipping_method_value = preg_replace('#\s+#',',',trim($disable_cod_shipping_method_value));

			$disable_cod_shipping_method_value = explode(",",$disable_cod_shipping_method_value);
			$disable_cod_shipping_method_value = $this->helper->coderockz_advance_cod_array_sanitize($disable_cod_shipping_method_value);
		} else {
			$disable_cod_shipping_method_value = [];
		}

		$zone_restriction_form_settings['disable_cod_shipping_method_value'] = $disable_cod_shipping_method_value;
		

		$cod_disabled_shipping_classes = (isset($zone_restriction_form_data['coderockz_advance_cod_disabled_shipping_classes']) && !empty($zone_restriction_form_data['coderockz_advance_cod_disabled_shipping_classes'])) ? $this->helper->coderockz_advance_cod_array_sanitize($zone_restriction_form_data['coderockz_advance_cod_disabled_shipping_classes']) : array();
		$zone_restriction_form_settings['cod_disabled_shipping_classes'] = $cod_disabled_shipping_classes;

		$cod_reverse_current_condition_shipping_class = !isset($zone_restriction_form_data['coderockz_advance_cod_restrict_cod_reverse_current_condition_shipping_class']) ? false : true;
		$zone_restriction_form_settings['cod_reverse_current_condition_shipping_class'] = $cod_reverse_current_condition_shipping_class;
		
		if(get_option('coderockz_advance_cod_restriction_settings') == false) {
			update_option('coderockz_advance_cod_restriction_settings', $zone_restriction_form_settings);
		} else {
			$zone_restriction_form_settings = array_merge(get_option('coderockz_advance_cod_restriction_settings'),$zone_restriction_form_settings);
			update_option('coderockz_advance_cod_restriction_settings', $zone_restriction_form_settings);
		}

		wp_send_json_success();
		
    }

    public function coderockz_advance_cod_localization_settings_form() { 
    	check_ajax_referer('coderockz_advance_cod_nonce');
		
		$localization_form_settings = [];

		$localization_form_data = $this->helper->coderockz_advance_cod_array_sanitize($_POST[ 'localizationSettingsData' ]);

		$total_amount_between_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_total_amount_between_text']);
		$total_more_than_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_total_more_than_text']);
		$total_less_than_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_total_less_than_text']);
		$get_the_cod_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_get_the_cod_text']);
		$discount_coupon_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_discount_coupon_text']);
		$category_product_sku_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_category_product_sku_text']);
		$backorder_quantity_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_backorder_quantity_less_than']);
		$quantity_sum_between = sanitize_text_field($localization_form_data['coderockz_advance_cod_quantity_sum_between']);
		$weight_sum_between = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_sum_between']);
		$weight_quantity_sum_between = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_quantity_sum_between']);
		$and_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_and_text']);
		$quantity_more_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_quantity_more_than']);
		$weight_more_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_more_than']);
		$weight_quantity_more_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_quantity_more_than']);
		$quantity_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_quantity_less_than']);
		$weight_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_less_than']);
		$weight_quantity_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_quantity_less_than']);

		$quantity_any_between = sanitize_text_field($localization_form_data['coderockz_advance_cod_quantity_any_between']);
		$weight_any_between = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_any_between']);
		$weight_quantity_any_between = sanitize_text_field($localization_form_data['coderockz_advance_cod_weight_quantity_any_between']);
		$any_quantity_more_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_any_quantity_more_than']);
		$any_weight_more_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_any_weight_more_than']);
		$any_weight_quantity_more_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_any_weight_quantity_more_than']);
		$any_quantity_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_any_quantity_less_than']);
		$any_weight_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_any_weight_less_than']);
		$any_weight_quantity_less_than = sanitize_text_field($localization_form_data['coderockz_advance_cod_any_weight_quantity_less_than']);
		$selected_country_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_selected_country_text']);
		$selected_state_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_selected_state_text']);
		$this_postcode_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_this_postcode_text']);
		$selected_shipping_method_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_selected_shipping_method_text']);
		$this_email_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_this_email_text']);
		$this_phone_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_this_phone_text']);
		$user_role_text = sanitize_text_field($localization_form_data['coderockz_advance_cod_user_role_text']);

		$localization_form_settings['total_amount_between_text'] = $total_amount_between_text;
		$localization_form_settings['total_more_than_text'] = $total_more_than_text;
		$localization_form_settings['total_less_than_text'] = $total_less_than_text;
		$localization_form_settings['get_the_cod_text'] = $get_the_cod_text;
		$localization_form_settings['discount_coupon_text'] = $discount_coupon_text;
		$localization_form_settings['category_product_sku_text'] = $category_product_sku_text;
		$localization_form_settings['backorder_quantity_less_than'] = $quantity_less_than;
		$localization_form_settings['quantity_sum_between'] = $quantity_sum_between;
		$localization_form_settings['weight_sum_between'] = $weight_sum_between;
		$localization_form_settings['weight_quantity_sum_between'] = $weight_quantity_sum_between;
		$localization_form_settings['quantity_more_than'] = $quantity_more_than;
		$localization_form_settings['weight_more_than'] = $weight_more_than;
		$localization_form_settings['weight_quantity_more_than'] = $weight_quantity_more_than;
		$localization_form_settings['quantity_less_than'] = $quantity_less_than;
		$localization_form_settings['weight_less_than'] = $weight_less_than;
		$localization_form_settings['weight_quantity_less_than'] = $weight_quantity_less_than;
		$localization_form_settings['and_text'] = $and_text;
		$localization_form_settings['quantity_any_between'] = $quantity_any_between;
		$localization_form_settings['weight_any_between'] = $weight_any_between;
		$localization_form_settings['weight_quantity_any_between'] = $weight_quantity_any_between;
		$localization_form_settings['any_quantity_more_than'] = $any_quantity_more_than;
		$localization_form_settings['any_weight_more_than'] = $any_weight_more_than;
		$localization_form_settings['any_weight_quantity_more_than'] = $any_weight_quantity_more_than;
		$localization_form_settings['any_quantity_less_than'] = $any_quantity_less_than;
		$localization_form_settings['any_weight_less_than'] = $any_weight_less_than;
		$localization_form_settings['any_weight_quantity_less_than'] = $any_weight_quantity_less_than;
		$localization_form_settings['selected_country_text'] = $selected_country_text;
		$localization_form_settings['selected_state_text'] = $selected_state_text;
		$localization_form_settings['this_postcode_text'] = $this_postcode_text;
		$localization_form_settings['selected_shipping_method_text'] = $selected_shipping_method_text;
		$localization_form_settings['this_email_text'] = $this_email_text;
		$localization_form_settings['this_phone_text'] = $this_phone_text;
		$localization_form_settings['user_role_text'] = $user_role_text;

		if(get_option('coderockz_advance_cod_localization_settings') == false) {
			update_option('coderockz_advance_cod_localization_settings', $localization_form_settings);
		} else {
			$localization_form_settings = array_merge(get_option('coderockz_advance_cod_localization_settings'),$localization_form_settings);
			update_option('coderockz_advance_cod_localization_settings', $localization_form_settings);
		}

		wp_send_json_success();
		
    }

    public function coderockz_wc_advance_cod_free_main_layout() {
        include_once CODEROCKZ_WC_ADVANCE_COD_FREE_DIR . '/admin/partials/coderockz-wc-advance-cod-free-admin-display.php';
    }

}
