<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://coderockz.com
 * @since      1.0.0
 *
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/public
 * @author     CodeRockz <coderockz1992@gmail.com>
 */
class Coderockz_Wc_Advance_Cod_Free_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->helper = new Coderockz_Wc_Advance_Cod_Helper();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		if( is_checkout() && ! ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' )) ){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/coderockz-wc-advance-cod-free-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		if( is_checkout() && ! ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' )) ){

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/coderockz-wc-advance-cod-free-public.js', array( 'jquery' ), $this->version, true );

		}

		$coderockz_advance_cod_nonce = wp_create_nonce('coderockz_advance_cod_nonce');
	    wp_localize_script($this->plugin_name, 'coderockz_advance_cod_ajax_obj', array(
            'coderockz_advance_cod_ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $coderockz_advance_cod_nonce,
        ));

	}

	public function coderockz_advance_cod_payment_gateways( $available_gateways ) {
	    // Not in backend (admin) and Not in order pay page


	    if( is_admin() ||  is_wc_endpoint_url('order-pay') || !is_ajax() ) 
	        return $available_gateways;

	    $restriction_settings = get_option('coderockz_advance_cod_restriction_settings');

	    $enable_cod_greater_than = isset($restriction_settings['enable_cod_greater_than']) && $restriction_settings['enable_cod_greater_than'] != "" ? (float)$restriction_settings['enable_cod_greater_than'] : "";

	    $enable_cod_less_than = isset($restriction_settings['enable_cod_less_than']) && $restriction_settings['enable_cod_less_than'] != "" ? (float)$restriction_settings['enable_cod_less_than'] : "";


	    $cart_total = $this->helper->cart_total();

	    $currency_symbol = get_woocommerce_currency_symbol();

	    $localization_settings = get_option('coderockz_advance_cod_localization_settings');

	    $disable_cod_greater_than = isset($restriction_settings['disable_cod_greater_than']) && $restriction_settings['disable_cod_greater_than'] != "" ? (float)$restriction_settings['disable_cod_greater_than'] : "";

	    $disable_cod_less_than = isset($restriction_settings['disable_cod_less_than']) && $restriction_settings['disable_cod_less_than'] != "" ? (float)$restriction_settings['disable_cod_less_than'] : "";

	    if(( $disable_cod_greater_than != "" && $cart_total > $disable_cod_greater_than ) && ($disable_cod_less_than != "" && $cart_total < $disable_cod_less_than)) {
			unset( $available_gateways['cod'] );
			$total_amount_between_text = (isset($localization_settings['total_amount_between_text']) && $localization_settings['total_amount_between_text'] != "") ? $localization_settings['total_amount_between_text'] : __( "Cash on Delivery is unavailable if cart amount is between", 'coderockz-advance-cod' );
			$and_text = (isset($localization_settings['and_text']) && $localization_settings['and_text'] != "") ? $localization_settings['and_text'] : __( "and", 'coderockz-advance-cod' );
			$greater_price_based_notice = $total_amount_between_text." ".$this->helper->postion_currency_symbol($currency_symbol,$disable_cod_greater_than)." ".$and_text." ".$this->helper->postion_currency_symbol($currency_symbol,$disable_cod_less_than).".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($greater_price_based_notice, 'notice');
		    }	
		} elseif ( $disable_cod_greater_than != "" && $disable_cod_less_than == "" && $cart_total > $disable_cod_greater_than ) {
			unset( $available_gateways['cod'] );
			$total_more_than_text = (isset($localization_settings['total_more_than_text']) && $localization_settings['total_more_than_text'] != "") ? $localization_settings['total_more_than_text'] : __( "Cash on Delivery is unavailable if cart amount is more than", 'coderockz-advance-cod' );
			$greater_price_based_notice = $total_more_than_text." ".$this->helper->postion_currency_symbol($currency_symbol,$disable_cod_greater_than).".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($greater_price_based_notice, 'notice');
		    }
	    } elseif($disable_cod_less_than != "" && $disable_cod_greater_than == "" && $cart_total < $disable_cod_less_than) {
			unset( $available_gateways['cod'] );
			$total_less_than_text = (isset($localization_settings['total_less_than_text']) && $localization_settings['total_less_than_text'] != "") ? $localization_settings['total_less_than_text'] : __( "Cash on Delivery is unavailable if cart amount is less than", 'coderockz-advance-cod' );
	    	$lesser_price_based_notice = $total_less_than_text." ".$this->helper->postion_currency_symbol($currency_symbol,$disable_cod_less_than).".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($lesser_price_based_notice, 'notice');
		    }
	    }

	    $detect_disable_cod_discount_coupon_condition = $this->helper->detect_disable_cod_discount_coupon_condition();

	    if (isset($available_gateways['cod']) && $detect_disable_cod_discount_coupon_condition['disable_discount_coupons_condition'] )
	    {
	        unset( $available_gateways['cod'] );
	        $discount_coupon_text = (isset($localization_settings['discount_coupon_text']) && $localization_settings['discount_coupon_text'] != "") ? $localization_settings['discount_coupon_text'] : __( "Cash on delivery is not available for discount coupon", 'coderockz-advance-cod' );
	    	$discount_notice = $discount_coupon_text." ".implode(', ',$detect_disable_cod_discount_coupon_condition['restrict_coupon']).".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($discount_notice, 'notice');
		    }
	    }

	    $restriction_cat_pro_sku_condition = $this->helper->detect_restriction_cat_pro_sku_condition();
	    
	    if (isset($available_gateways['cod']) && $restriction_cat_pro_sku_condition['restriction_condition'] )
	    {
	        unset( $available_gateways['cod'] );
	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
	    	$category_product_sku_notice = $category_product_sku_text." ".implode(', ',$restriction_cat_pro_sku_condition['restricted_product_name']).".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($category_product_sku_notice, 'notice');
		    }
	    }

	    
	    $restrict_cod_virtual_downloadable_products = (isset($restriction_settings['restrict_cod_virtual_downloadable_products']) && !empty($restriction_settings['restrict_cod_virtual_downloadable_products'])) ? $restriction_settings['restrict_cod_virtual_downloadable_products'] : false;

	    if($restrict_cod_virtual_downloadable_products) {
	    	$has_virtual_downloadable_products = $this->helper->check_virtual_downloadable_products();
		    if (isset($available_gateways['cod']) && $has_virtual_downloadable_products['has_virtual_downloadable_products'])
		    {
		        unset( $available_gateways['cod'] );
		        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
		    	$category_product_sku_notice = $category_product_sku_text." ".implode(', ',$has_virtual_downloadable_products['restricted_product_name']).".";
		        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
		    		wc_add_notice($category_product_sku_notice, 'notice');
			    }
		    }
	    }

	    $restrict_cod_backorder_products = (isset($restriction_settings['restrict_cod_backorder_products']) && !empty($restriction_settings['restrict_cod_backorder_products'])) ? $restriction_settings['restrict_cod_backorder_products'] : false;

	    if($restrict_cod_backorder_products) {

		    $backorder_item = false;
		    $backorder_product = [];
		    $backorder_product_quantity = [];
		    $backorder_product_without_quantity = [];
		    foreach( WC()->cart->get_cart() as $cart_item ) {
		        if( $cart_item['data']->is_on_backorder( $cart_item['quantity'] ) ) {
		            $backorder_item = true;
		            $product = wc_get_product($cart_item["variation_id"] ? $cart_item["variation_id"] : 
					$cart_item["product_id"]);
					if($product->get_stock_quantity() != "") {
						$backorder_product[] = $cart_item['data']->get_name();
		            	$backorder_product_quantity [] = $product->get_stock_quantity();
					} else {
						$backorder_product_without_quantity[] = $cart_item['data']->get_name();
					}
					
		        }
		    }

		    if(isset($available_gateways['cod']) && $backorder_item) {
		    	unset( $available_gateways['cod'] );
		    	$get_the_cod_text = (isset($localization_settings['get_the_cod_text']) && $localization_settings['get_the_cod_text'] != "") ? $localization_settings['get_the_cod_text'] : __( "to get the Cash On Delivery", 'coderockz-advance-cod' );
		    	$quantity_must_be_less_than = (isset($localization_settings['backorder_quantity_less_than']) && $localization_settings['backorder_quantity_less_than'] != "") ? $localization_settings['backorder_quantity_less_than'] : __( "quantity must be less than", 'coderockz-advance-cod' );
		    	$backorder_notice = implode(", ",$backorder_product)." ".$quantity_must_be_less_than." ".implode(", ",$backorder_product_quantity)." ".$get_the_cod_text.".";

		    	if(!empty($backorder_product_without_quantity)) {
		    		$category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
		    		$backorder_product_without_quantity_notice = $category_product_sku_text." ".implode(', ',$backorder_product_without_quantity).".";
		    	}
		    	

		        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
		    		wc_add_notice($backorder_notice, 'notice');
		    		if(!empty($backorder_product_without_quantity)) {
		    			wc_add_notice($backorder_product_without_quantity_notice, 'notice');
		    		}
		    		
			    }
		    }

		}

	    $disable_cod_stock_greater_than = isset($restriction_settings['disable_cod_stock_greater_than']) && $restriction_settings['disable_cod_stock_greater_than'] != "" ? (int)$restriction_settings['disable_cod_stock_greater_than'] : "";

	    $disable_cod_stock_less_than = isset($restriction_settings['disable_cod_stock_less_than']) && $restriction_settings['disable_cod_stock_less_than'] != "" ? (int)$restriction_settings['disable_cod_stock_less_than'] : "";

	    if($disable_cod_stock_greater_than != "" || $disable_cod_stock_less_than != "") {
	    	$disable_cod_stock_condition = false;
	    	$restricted_product_name = [];
			foreach (WC()->cart->get_cart() as $cart_item) {

			    $variation_id = $cart_item['variation_id'];
			    if( 0 != $variation_id) {
			        $product_obj = new WC_Product_variation($variation_id);
			        $stock = $product_obj->get_stock_quantity();
			    } else {
			        $product_id = $cart_item['product_id'];
			        $product_obj = new WC_Product($product_id);
			        $stock = $product_obj->get_stock_quantity();
			        
			    }

			    if ( $stock > $disable_cod_stock_greater_than || $stock < $disable_cod_stock_less_than ) {
			    	$restricted_product_name [] = $product_obj->get_name();
			        $disable_cod_stock_condition = true;
			        /*break; //We break the loop*/
			    }
			}

			if (isset($available_gateways['cod']) && $disable_cod_stock_condition )
		    {
		        unset( $available_gateways['cod'] );
		        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
	    		$stock_notice = $category_product_sku_text." ".implode(', ',$restricted_product_name).".";
	    		wc_add_notice($stock_notice, 'notice');
		    }
	    }

	    $disable_cod_quantity_greater_than = isset($restriction_settings['disable_cod_quantity_greater_than']) && $restriction_settings['disable_cod_quantity_greater_than'] != "" ? (int)$restriction_settings['disable_cod_quantity_greater_than'] : "";

	    $disable_cod_quantity_less_than = isset($restriction_settings['disable_cod_quantity_less_than']) && $restriction_settings['disable_cod_quantity_less_than'] != "" ? (int)$restriction_settings['disable_cod_quantity_less_than'] : "";

	    $disable_cod_product_quantity_based_on = (isset($restriction_settings['disable_cod_product_quantity_based_on']) && !empty($restriction_settings['disable_cod_product_quantity_based_on'])) ? $restriction_settings['disable_cod_product_quantity_based_on'] : false;

	    
	    if($disable_cod_quantity_greater_than != "" || $disable_cod_quantity_less_than != "") {
	    	$cart_quantity = [];
	    	
		    foreach (WC()->cart->get_cart() as $cart_item) {
				$cart_quantity[] = $cart_item['quantity'];
			}

			$disable_cod_quantity_condition = $this->helper->check_greater_less_than($cart_quantity,$disable_cod_product_quantity_based_on,$disable_cod_quantity_greater_than,$disable_cod_quantity_less_than,"quantity");

			if (isset($available_gateways['cod']) && $disable_cod_quantity_condition['disable_cod_condition'] )
		    {
		        unset( $available_gateways['cod'] );
		        wc_add_notice($disable_cod_quantity_condition['notice'], 'notice');
		    }

	    }

	    $disable_cod_weight_greater_than = isset($restriction_settings['disable_cod_weight_greater_than']) && $restriction_settings['disable_cod_weight_greater_than'] != "" ? (int)$restriction_settings['disable_cod_weight_greater_than'] : "";

	    $disable_cod_weight_less_than = isset($restriction_settings['disable_cod_weight_less_than']) && $restriction_settings['disable_cod_weight_less_than'] != "" ? (int)$restriction_settings['disable_cod_weight_less_than'] : "";


	    $disable_cod_product_weight_based_on = (isset($restriction_settings['disable_cod_product_weight_based_on']) && !empty($restriction_settings['disable_cod_product_weight_based_on'])) ? $restriction_settings['disable_cod_product_weight_based_on'] : false;

	    $disable_cod_product_weight_quantity_consider = (isset($restriction_settings['disable_cod_product_weight_quantity_consider']) && !empty($restriction_settings['disable_cod_product_weight_quantity_consider'])) ? $restriction_settings['disable_cod_product_weight_quantity_consider'] : false;

	    
	    if($disable_cod_weight_greater_than != "" || $disable_cod_weight_less_than != "") {
	    	$cart_weight = [];
	    	
		    foreach (WC()->cart->get_cart() as $cart_item) {
		    	if($disable_cod_product_weight_quantity_consider) {
		    		$cart_weight[] = (float)$cart_item['data']->get_weight() * $cart_item['quantity'];
		    	} else {
		    		$cart_weight[] = $cart_item['data']->get_weight();
		    	}
				
			}

			$disable_cod_weight_condition = $this->helper->check_greater_less_than($cart_weight,$disable_cod_product_weight_based_on,$disable_cod_weight_greater_than,$disable_cod_weight_less_than,"weight");


			if (isset($available_gateways['cod']) && $disable_cod_weight_condition['disable_cod_condition'] )
		    {
		        unset( $available_gateways['cod'] );
		        wc_add_notice($disable_cod_weight_condition['notice'], 'notice');
		    }

	    }
	     

	    global $woocommerce;
    	$country = !empty($woocommerce->customer->get_shipping_country()) ? $woocommerce->customer->get_shipping_country() : $woocommerce->customer->get_country();

	    $cod_enabled_country = isset($restriction_settings['cod_enabled_country']) && !empty($restriction_settings['cod_enabled_country']) ? $restriction_settings['cod_enabled_country'] : array();

	    if (isset($available_gateways['cod']) && !empty($cod_enabled_country) && !in_array($country, $cod_enabled_country) )
	    {
	        
	        unset( $available_gateways['cod'] );

	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

	        $selected_country_text = (isset($localization_settings['selected_country_text']) && $localization_settings['selected_country_text'] != "") ? $localization_settings['selected_country_text'] : __( "selected country", 'coderockz-advance-cod' );

	        $country_notice = $category_product_sku_text." ".$selected_country_text.".";

	    	if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($country_notice, 'notice');
		    }

	    }

	    $state = !empty($woocommerce->customer->get_shipping_state()) ? $woocommerce->customer->get_shipping_state() : $woocommerce->customer->get_billing_state();

	    $cod_enabled_states = isset($restriction_settings['cod_enabled_states']) && !empty($restriction_settings['cod_enabled_states']) ? $restriction_settings['cod_enabled_states'] : array();
	    $cod_disabled_zone_states = isset($restriction_settings['cod_disabled_states']) && !empty($restriction_settings['cod_disabled_states']) ? $restriction_settings['cod_disabled_states'] : array();
	    

	    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	    if(isset($chosen_methods)){
			$chosen_shipping_method = $chosen_methods[0];
		}

	    if (isset($available_gateways['cod']) && !empty($cod_enabled_states) && !in_array($country.":".$state, $cod_enabled_states) )
	    {
	        unset( $available_gateways['cod'] );
	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

	        $selected_state_text = (isset($localization_settings['selected_state_text']) && $localization_settings['selected_state_text'] != "") ? $localization_settings['selected_state_text'] : __( "selected state/country", 'coderockz-advance-cod' );

	        $state_notice = $category_product_sku_text." ".$selected_state_text.".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($state_notice, 'notice');
		    }
	    }

	    if (isset($available_gateways['cod']) && !empty($cod_disabled_zone_states) && $chosen_shipping_method!= "" && in_array($state, $cod_disabled_zone_states) )
	    {
	        unset( $available_gateways['cod'] );

	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

	        $selected_state_text = (isset($localization_settings['selected_state_text']) && $localization_settings['selected_state_text'] != "") ? $localization_settings['selected_state_text'] : __( "selected state/country", 'coderockz-advance-cod' );

	        $state_notice = $category_product_sku_text." ".$selected_state_text.".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($state_notice, 'notice');
		    }
	    }

	    $cod_disabled_zone_zip = isset($restriction_settings['cod_disabled_zip']) && !empty($restriction_settings['cod_disabled_zip']) ? $restriction_settings['cod_disabled_zip'] : array();

	    $postcode = !empty($woocommerce->customer->get_shipping_postcode()) ? $woocommerce->customer->get_shipping_postcode() : $woocommerce->customer->get_billing_postcode();


	    if(!empty($cod_disabled_zone_zip)) {
	    	
			foreach($cod_disabled_zone_zip as $zip) {
				$multistep_postal_code = false;
				$between_postal_code = false;
				/*$individual_postcode_range = [];*/
			    if (stripos($zip,'...') !== false) {
			    	$range = explode('...', $zip);
			    	if(stripos($postcode,'-') !== false && stripos($range[0],'-') !== false && stripos($range[1],'-') !== false) {
						
			    		$sub_range_one = (int)str_replace("-", "", $range[0]);
			    		$sub_range_two = (int)str_replace("-", "", $range[1]);

						$postcode_range = (int)str_replace("-", "", $postcode);
						
						if($this->helper->number_between($postcode_range, $sub_range_two, $sub_range_one)) {
							$multistep_postal_code = true;
						}
						
					} elseif(stripos($range[0],'*') !== false && stripos($range[1],'*') !== false) {
						
						$sub_range_one = (int)str_replace("*", "", $range[0]);
						$sub_range_two = (int)str_replace("*", "", $range[1]);
						
						if($this->helper->number_between($this->helper->starts_with_starting_numeric($postcode), $sub_range_two, $sub_range_one)) {
							$multistep_postal_code = true;
						}
						
					} elseif(stripos($postcode,'-') === false && stripos($range[0],'-') === false && stripos($range[1],'-') === false) {
						$alphabet_code = preg_replace("/[^a-zA-Z]+/", "", $range[0]);
						$range[0] = preg_replace("/[^0-9]+/", "", $range[0]);
						$range[1] = preg_replace("/[^0-9]+/", "", $range[1]);
						if($alphabet_code != "" && $this->helper->starts_with(strtolower($postcode), strtolower($alphabet_code)) && $this->helper->number_between(preg_replace("/[^0-9]/", "", $postcode ), $range[1], $range[0])) {
							$between_postal_code = true;
						} elseif($alphabet_code == "" /*&& is_numeric($postcode)*/ && $this->helper->number_between($postcode, $range[1], $range[0])) {
							$between_postal_code = true;
						}
					}
			    }
			    if (substr($zip, -1) == '*') {
			    	if(($this->helper->starts_with($postcode,substr($zip, 0, -1)) || $this->helper->starts_with(strtolower($postcode),substr(strtolower($zip), 0, -1)) || $this->helper->starts_with(strtoupper($postcode),substr(strtoupper($zip), 0, -1))) && $chosen_shipping_method!= "" && isset($available_gateways['cod'])) {
			    		unset( $available_gateways['cod'] );
			    		$category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

				        $this_postcode_text = (isset($localization_settings['this_postcode_text']) && $localization_settings['this_postcode_text'] != "") ? $localization_settings['this_postcode_text'] : __( "this postcode/zip", 'coderockz-advance-cod' );

				        $zip_notice = $category_product_sku_text." ".$this_postcode_text.".";
				        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
				    		wc_add_notice($zip_notice, 'notice');
					    }
			    	}
			    } elseif(($multistep_postal_code || $between_postal_code || ($zip == $postcode || str_replace(" ","",$zip) == $postcode || strtolower($zip) == strtolower($postcode) || str_replace(" ","",strtolower($zip)) == strtolower($postcode) )) && $chosen_shipping_method!= "" && isset($available_gateways['cod'])) {
					unset( $available_gateways['cod'] );
					$category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

			        $this_postcode_text = (isset($localization_settings['this_postcode_text']) && $localization_settings['this_postcode_text'] != "") ? $localization_settings['this_postcode_text'] : __( "this postcode/zip", 'coderockz-advance-cod' );

			        $zip_notice = $category_product_sku_text." ".$this_postcode_text.".";
			        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
			    		wc_add_notice($zip_notice, 'notice');
				    }
			    }
			}			
		}

		$enable_cod_zip = isset($restriction_settings['enable_cod_zip']) && !empty($restriction_settings['enable_cod_zip']) ? $restriction_settings['enable_cod_zip'] : array();

		if(!empty($enable_cod_zip)) {
	    	
			foreach($enable_cod_zip as $zip) {
				$multistep_postal_code = false;
				$between_postal_code = false;
				/*$individual_postcode_range = [];*/
			    if (stripos($zip,'...') !== false) {
			    	$range = explode('...', $zip);
			    	if(stripos($postcode,'-') !== false && stripos($range[0],'-') !== false && stripos($range[1],'-') !== false) {
						
			    		$sub_range_one = (int)str_replace("-", "", $range[0]);
			    		$sub_range_two = (int)str_replace("-", "", $range[1]);

						$postcode_range = (int)str_replace("-", "", $postcode);
						
						if($this->helper->number_between($postcode_range, $sub_range_two, $sub_range_one)) {
							$multistep_postal_code = true;
						}
						
					} elseif(stripos($postcode,'-') === false && stripos($range[0],'-') === false && stripos($range[1],'-') === false) {
						$alphabet_code = preg_replace("/[^a-zA-Z]+/", "", $range[0]);
						$range[0] = preg_replace("/[^0-9]+/", "", $range[0]);
						$range[1] = preg_replace("/[^0-9]+/", "", $range[1]);
						if($alphabet_code != "" && $this->helper->starts_with(strtolower($postcode), strtolower($alphabet_code)) && $this->helper->number_between(preg_replace("/[^0-9]/", "", $postcode ), $range[1], $range[0])) {
							$between_postal_code = true;
						} elseif($alphabet_code == "" /*&& is_numeric($postcode)*/ && $this->helper->number_between($postcode, $range[1], $range[0])) {
							$between_postal_code = true;
						}
					}
			    }
			    if (substr($zip, -1) == '*') {
			    	if(!$this->helper->starts_with($postcode,substr($zip, 0, -1)) && !$this->helper->starts_with(strtolower($postcode),substr(strtolower($zip), 0, -1)) && !$this->helper->starts_with(strtoupper($postcode),substr(strtoupper($zip), 0, -1))) {
			    		unset( $available_gateways['cod'] );
			    		$category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

				        $this_postcode_text = (isset($localization_settings['this_postcode_text']) && $localization_settings['this_postcode_text'] != "") ? $localization_settings['this_postcode_text'] : __( "this postcode/zip", 'coderockz-advance-cod' );

				        $zip_notice = $category_product_sku_text." ".$this_postcode_text.".";
				        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
				    		wc_add_notice($zip_notice, 'notice');
					    }
			    	}
			    } elseif(!$multistep_postal_code && !$between_postal_code && ($zip != $postcode && str_replace(" ","",$zip) != $postcode && strtolower($zip) != strtolower($postcode) && str_replace(" ","",strtolower($zip)) != strtolower($postcode) )) {
					unset( $available_gateways['cod'] );
					$category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

			        $this_postcode_text = (isset($localization_settings['this_postcode_text']) && $localization_settings['this_postcode_text'] != "") ? $localization_settings['this_postcode_text'] : __( "this postcode/zip", 'coderockz-advance-cod' );

			        $zip_notice = $category_product_sku_text." ".$this_postcode_text.".";
			        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
			    		wc_add_notice($zip_notice, 'notice');
				    }
			    }
			}			
		}

		$cod_disabled_shipping_methods = isset($restriction_settings['cod_disabled_shipping_methods']) && !empty($restriction_settings['cod_disabled_shipping_methods']) ? $restriction_settings['cod_disabled_shipping_methods'] : array();
		
		$disable_cod_shipping_method_value = isset($restriction_settings['disable_cod_shipping_method_value']) && !empty($restriction_settings['disable_cod_shipping_method_value']) ? $restriction_settings['disable_cod_shipping_method_value'] : array();

		$cod_disabled_shipping_methods_array = array_filter(array_merge($cod_disabled_shipping_methods, $disable_cod_shipping_method_value), 'strlen');


		if (isset($available_gateways['cod']) && !empty($cod_disabled_shipping_methods_array) && $chosen_shipping_method!= "" && in_array($chosen_shipping_method, $cod_disabled_shipping_methods_array) )
	    {
	        unset( $available_gateways['cod'] );
	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );

	        $selected_shipping_method_text = (isset($localization_settings['selected_shipping_method_text']) && $localization_settings['selected_shipping_method_text'] != "") ? $localization_settings['selected_shipping_method_text'] : __( "selected shipping method", 'coderockz-advance-cod' );

	        $shipping_method_notice = $category_product_sku_text." ".$selected_shipping_method_text.".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($shipping_method_notice, 'notice');
		    }
	    }


	    $detect_restriction_product_shipping_class_condition = $this->helper->detect_restriction_product_shipping_class_condition();

	    if(isset($available_gateways['cod']) && $detect_restriction_product_shipping_class_condition['restriction_condition']) {
	    	unset( $available_gateways['cod'] );
	    	$category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
	    	$shipping_class_notice = $category_product_sku_text." ".implode(', ',$detect_restriction_product_shipping_class_condition['restricted_product_name']).".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($shipping_class_notice, 'notice');
		    }
	    }

	    $disable_cod_user_emails = isset($restriction_settings['disable_cod_user_emails']) && !empty($restriction_settings['disable_cod_user_emails']) ? $restriction_settings['disable_cod_user_emails'] : array();
	    $email = WC()->session->get( 'billing_email');
	    $disable_cod_user_phone = isset($restriction_settings['disable_cod_user_phone']) && !empty($restriction_settings['disable_cod_user_phone']) ? $restriction_settings['disable_cod_user_phone'] : array();
	    $phone = WC()->session->get( 'billing_phone');



	    if (isset($available_gateways['cod']) && !empty($disable_cod_user_emails) && in_array($email, $disable_cod_user_emails) )
	    {
	        unset( $available_gateways['cod'] );
	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
	        $this_email_text = (isset($localization_settings['this_email_text']) && $localization_settings['this_email_text'] != "") ? $localization_settings['this_email_text'] : __( "this email", 'coderockz-advance-cod' );
	    	$email_notice = $category_product_sku_text." ".$this_email_text.".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($email_notice, 'notice');
		    }
	    }

	    if (isset($available_gateways['cod']) && !empty($disable_cod_user_phone) && in_array($phone, $disable_cod_user_phone) )
	    {
	        unset( $available_gateways['cod'] );
	        $category_product_sku_text = (isset($localization_settings['category_product_sku_text']) && $localization_settings['category_product_sku_text'] != "") ? $localization_settings['category_product_sku_text'] : __( "Cash on delivery is not available for", 'coderockz-advance-cod' );
	        $this_phone_text = (isset($localization_settings['this_phone_text']) && $localization_settings['this_phone_text'] != "") ? $localization_settings['this_phone_text'] : __( "this phone", 'coderockz-advance-cod' );
	    	$phone_notice = $category_product_sku_text." ".$this_phone_text.".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($phone_notice, 'notice');
		    }
	    }

	    $disable_user_roles_condition = $this->helper->detect_disable_user_roles_condition();

	    if (isset($available_gateways['cod']) && $disable_user_roles_condition )
	    {
	        unset( $available_gateways['cod'] );
	        $user_role_text = (isset($localization_settings['user_role_text']) && $localization_settings['user_role_text'] != "") ? $localization_settings['user_role_text'] : __( "Cash on delivery is not available for you", 'coderockz-advance-cod' );
	    	$user_role_notice = $user_role_text.".";
	        if(is_checkout() && !isset($available_gateways[ 'cod' ])) {
	    		wc_add_notice($user_role_notice, 'notice');
		    }
	    }

	    return $available_gateways;
	}

	public function action_woocommerce_checkout_update_order_review($posted_data) {
		parse_str( $posted_data, $output );
		if ( isset( $output['billing_email'] ) ){
	        WC()->session->set( 'billing_email', $output['billing_email'] );
	    }

	    if ( isset( $output['billing_phone'] ) ){
	        WC()->session->set( 'billing_phone', $output['billing_phone'] );
	    }
	}

	public function coderockz_advance_cod_get_disable_cod_phone_digit() {
		check_ajax_referer('coderockz_advance_cod_nonce');
		$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');
		$disable_cod_user_phone = isset($restriction_settings['disable_cod_user_phone']) && !empty($restriction_settings['disable_cod_user_phone']) ? $restriction_settings['disable_cod_user_phone'] : array();
		if(!empty($disable_cod_user_phone)) {
			$disable_cod_user_phone_length = array_values(array_unique(array_map('strlen', $disable_cod_user_phone), false));
			$disable_cod_user_phone_length_temp = $disable_cod_user_phone_length;
			foreach($disable_cod_user_phone_length_temp as $length) {
				$disable_cod_user_phone_length[] = $length + 1;
				$disable_cod_user_phone_length[] = $length - 1;
			}
		} else {
			$disable_cod_user_phone_length = [];
		}
		$response = [
			"disable_cod_user_phone_length" => $disable_cod_user_phone_length,
		];
		$response = json_encode($response);
		wp_send_json_success($response);
	}

}
