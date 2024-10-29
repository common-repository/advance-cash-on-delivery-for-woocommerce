<?php

if( !class_exists( 'Coderockz_Wc_Advance_Cod_Helper' ) ) {

	class Coderockz_Wc_Advance_Cod_Helper {

		public function coderockz_advance_cod_array_sanitize($array) {
		    $newArray = array();
		    if (count($array)>0) {
		        foreach ($array as $key => $value) {
		            if (is_array($value)) {
		                foreach ($value as $key2 => $value2) {
		                    if (is_array($value2)) {
		                        foreach ($value2 as $key3 => $value3) {
		                            $newArray[$key][$key2][$key3] = sanitize_text_field($value3);
		                        }
		                    } else {
		                        $newArray[$key][$key2] = sanitize_text_field($value2);
		                    }
		                }
		            } else {
		                $newArray[$key] = sanitize_text_field($value);
		            }
		        }
		    }
		    return $newArray;
		}

		public function objectToArray($d) {
		    foreach($d as $key => $value) {
			    $d[$key] = (array) $value;
			}
			return $d;
		}


		public function currency_exchange_value( $currency_value ) {
	        if ( class_exists('WOOCS') ) {
	            $currency_value = apply_filters('woocs_exchange_value', $currency_value);
	        }
	       
	        return $currency_value;
	    }

	    public function detect_plugin_settings_page() {
			global $wp;  
			$current_url = home_url(add_query_arg(array($_GET), $wp->request));
			if (strpos($current_url, "coderockz-wc-advance-cod-free-settings")!==false){
			    return true;
			}
		}

		// Function to check string starting with given substring 
		public function starts_with ($string, $startString) { 
		    $len = strlen($startString); 
		    return (substr($string, 0, $len) === $startString); 
		}

		public function number_between($varToCheck, $high, $low) {
			if($varToCheck < $low) return false;
			if($varToCheck > $high) return false;
			return true;
		}

		// Function to check whether a string starts with number and take the numeric value until non numeric character
		public function starts_with_starting_numeric($string) {
		  $length = strlen($string);   
		  for ($i = 0, $int = ''; $i < $length; $i++) {
		    if (is_numeric($string[$i]))
		        $int .= $string[$i];
		     else break;
		  }

		  return (int) $int;
		}

		public function cart_total() {
			$restriction_settings = get_option('coderockz_advance_cod_restriction_settings'); 
			$enable_including_tax = (isset($restriction_settings['calculating_include_tax']) && !empty($restriction_settings['calculating_include_tax'])) ? $restriction_settings['calculating_include_tax'] : false;
			$enable_including_discount = (isset($restriction_settings['calculating_include_discount']) && !empty($restriction_settings['calculating_include_discount'])) ? $restriction_settings['calculating_include_discount'] : false;
			$enable_including_shipping_cost = (isset($restriction_settings['calculating_include_shipping_cost']) && !empty($restriction_settings['calculating_include_shipping_cost'])) ? $restriction_settings['calculating_include_shipping_cost'] : false;

			$cart_total_price = WC()->cart->get_cart_contents_total();
			if($enable_including_tax) {
				
				$cart_total_price = WC()->cart->get_cart_contents_total() + (float)WC()->cart->get_cart_contents_tax();
			}

			if($enable_including_discount) {
				$cart_total_price = $cart_total_price + (float)WC()->cart->get_cart_discount_total();
			}

			if($enable_including_shipping_cost) {
				$cart_total_price = $cart_total_price + (float)WC()->cart->get_cart_shipping_total();
			}
			
			return $cart_total_price;
		}
		
		public function check_virtual_downloadable_products() {
			// By default, no virtual or downloadable product
			$has_virtual_downloadable_products = false;
			  
			// Default virtual products number
			$virtual_products = 0;

			// Default downloadable products number
			$downloadable_products = 0;
			  
			// Get all products in cart
			$products = WC()->cart->get_cart();
			$restricted_product_name = [];
			// Loop through cart products
			foreach( $products as $product ) {
				  
				// Get product ID and '_virtual' post meta
				$product_id = $product['product_id'];
				$is_virtual = get_post_meta( $product_id, '_virtual', true );
				  
				// Update $has_virtual_product if product is virtual
				if( $is_virtual == 'yes' ) {
					$virtual_products += 1;
					$product = wc_get_product( $product_id );
					$restricted_product_name [] = $product->get_name();
				}

				$is_downloadable = get_post_meta( $product_id, '_downloadable', true );
				  
				// Update $has_virtual_product if product is virtual
				if( $is_downloadable == 'yes' ) {
					$downloadable_products += 1;
					$product = wc_get_product( $product_id );
					$restricted_product_name [] = $product->get_name();
				}
			  		
			}

			$total_virtual_downloadable_products = $virtual_products + $downloadable_products;


			if( $total_virtual_downloadable_products > 0 ) {
			 	$has_virtual_downloadable_products = true;
			}

			$response = [
				'has_virtual_downloadable_products' => $has_virtual_downloadable_products,
				'restricted_product_name' => array_values(array_unique($restricted_product_name, false))
			];

			return $response;
		}


		public function checkout_product_categories(/*$exclude_checking=false*/) {
			$product_cat = [];
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				if($cart_item['data']->get_parent_id()) {
					$variable_product = $cart_item['data']->get_parent_id();
					$terms = get_the_terms( $variable_product, 'product_cat' );
				} else {
					$terms = get_the_terms( $cart_item['data']->get_id(), 'product_cat' );
				}

				if(!empty($terms)) {
					foreach ($terms as $term) {
						$product_cat[] = htmlspecialchars_decode($term->name);
					}
				}				
			}
			$checkout_product_categories = array_unique(array_values($product_cat));
			/*if($exclude_checking == false) {
				$checkout_product_categories = array_map('strtolower', $checkout_product_categories);
			}*/
			
			return $checkout_product_categories;
		}

		public function checkout_product_shipping_class() {
			$product_shipping_class = [];
			
			foreach ( WC()->cart->get_cart_contents() as $key => $values ) {
	            $product_shipping_class[] = $values[ 'data' ]->get_shipping_class_id();
	        }
			
			return $product_shipping_class;
		}

		public function detect_restriction_product_shipping_class_condition() {
			$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');

			$cod_disabled_shipping_classes = isset($restriction_settings['cod_disabled_shipping_classes']) && !empty($restriction_settings['cod_disabled_shipping_classes']) ? $restriction_settings['cod_disabled_shipping_classes'] : array();

			$cod_reverse_current_condition_shipping_class = (isset($restriction_settings['cod_reverse_current_condition_shipping_class']) && !empty($restriction_settings['cod_reverse_current_condition_shipping_class'])) ? $restriction_settings['cod_reverse_current_condition_shipping_class'] : false;

			$checkout_product_shipping_class = $this->checkout_product_shipping_class();
			$checkout_products = $this->checkout_product_id();

  			$cod_disabled_shipping_classes_condition = (count(array_intersect($checkout_product_shipping_class, $cod_disabled_shipping_classes)) <= count($checkout_product_shipping_class)) && count(array_intersect($checkout_product_shipping_class, $cod_disabled_shipping_classes))>0;

			$restricted_product_name = [];
			if($cod_disabled_shipping_classes_condition) {
				
	  			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {           
				    $product = $cart_item['data'];
				    if($product->get_parent_id()) {
						$product_id = $product->get_parent_id();
					} else {
						$product_id = $product->get_id();
					}

			    	$product = wc_get_product( $product_id );
			    	$shipping_class = $product->get_shipping_class_id();
			    	if(in_array($shipping_class, $cod_disabled_shipping_classes)) {
			    		$restricted_product_name [] = $product->get_name();
			    	}
				}
				$restriction_condition = true;
				if($cod_reverse_current_condition_shipping_class && count($checkout_product_shipping_class) > 1 && count($checkout_products) > 1 && count(array_diff($checkout_product_shipping_class, $cod_disabled_shipping_classes))>0) {
	  				$restriction_condition = !$restriction_condition;
	  			}
			} else {
				$restriction_condition = false;
			}

			$response = [
				'restriction_condition' => $restriction_condition,
				'restricted_product_name' => array_values(array_unique($restricted_product_name, false))
			];

			return $response;
		}

		public function checkout_product_id() {
			$product_id = [];
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id[] = $cart_item['data']->get_id();
			}
			return $product_id;
		}

		public function checkout_product_sku() {
			$product_sku = [];
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				// Get WC_Product by Variation ID, if not avaiable use Product ID
				$product = wc_get_product($cart_item["variation_id"] ? $cart_item["variation_id"] : 
				$cart_item["product_id"]);
				$product_sku[] = $product->get_sku();			
			}
			
			
			return $product_sku;
		}

		public function get_store_product_meta() {
			if(get_option('coderockz-woo-delivery-license-status') == 'valid') {
				return true;
			} else {
				return false;
			}
		}

		public function detect_restriction_cat_pro_sku_condition() {
			$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');

			$restriction_categories_array = (isset($restriction_settings['restrict_cod_categories']) && !empty($restriction_settings['restrict_cod_categories'])) ? $restriction_settings['restrict_cod_categories'] : array();
			$restriction_categories = [];
			foreach ($restriction_categories_array as $restriction_category) {
				$restriction_categories [] = stripslashes($restriction_category);
			}

			$restriction_products = (isset($restriction_settings['restrict_cod_products']) && !empty($restriction_settings['restrict_cod_products'])) ? $restriction_settings['restrict_cod_products'] : array();

			$restriction_products_sku = (isset($restriction_settings['disable_cod_sku']) && !empty($restriction_settings['disable_cod_sku'])) ? $restriction_settings['disable_cod_sku'] : array();

			$cod_reverse_current_condition = (isset($restriction_settings['cod_reverse_current_condition']) && !empty($restriction_settings['cod_reverse_current_condition'])) ? $restriction_settings['cod_reverse_current_condition'] : false;

			$checkout_product_categories = $this->checkout_product_categories(/*true*/);
			$checkout_products = $this->checkout_product_id();
			$checkout_products_sku = $this->checkout_product_sku();

			$restriction_categories_condition = (count(array_intersect($checkout_product_categories, $restriction_categories)) <= count($checkout_product_categories)) && count(array_intersect($checkout_product_categories, $restriction_categories))>0;

  			
  			$restriction_products_condition = (count(array_intersect($checkout_products, $restriction_products)) <= count($checkout_products)) && count(array_intersect($checkout_products, $restriction_products))>0;
  			
  			$restriction_products_sku_condition = (count(array_intersect($checkout_products_sku, $restriction_products_sku)) <= count($checkout_products_sku)) && count(array_intersect($checkout_products_sku, $restriction_products_sku))>0;

  			$restricted_product_name = [];

  			if(empty($restriction_products_condition)) {
	  			foreach(array_intersect($checkout_products, $restriction_products) as $restriction_product) {
	  				$product = wc_get_product( $restriction_product );
	  				$restricted_product_name [] = $product->get_name();
	  			}
  			}

  			if($restriction_products_sku_condition) {
	  			foreach(array_intersect($checkout_products_sku, $restriction_products_sku) as $restriction_product_sku) {
	  				$product = wc_get_product( wc_get_product_id_by_sku($restriction_product_sku) );
	  				$restricted_product_name [] = $product->get_name();
	  			}
  			}

  			if($restriction_categories_condition) {
	  			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {           
				    $product = $cart_item['data'];
				    if($product->get_parent_id()) {
						$product_id = $product->get_parent_id();
					} else {
						$product_id = $product->get_id();
					}
				    if ( has_term( array_intersect($checkout_product_categories, $restriction_categories), 'product_cat', $product_id ) ) {
				    	$product = wc_get_product( $product_id );
	  					$restricted_product_name [] = $product->get_name();
				    }
				}
			}

			if($restriction_categories_condition) {
				$restriction_condition = true;
				if($cod_reverse_current_condition && count($checkout_product_categories) > 1 && count($checkout_products) > 1 && count(array_diff($checkout_product_categories, $restriction_categories))>0) {
	  				$restriction_condition = !$restriction_condition;
	  			}
			} elseif($restriction_products_condition) {
				$restriction_condition = true;
				if($cod_reverse_current_condition && count($checkout_products) > 1 && count(array_diff($checkout_products, $restriction_products))>0) {
	  				$restriction_condition = !$restriction_condition;
	  			}
			} elseif($restriction_products_sku_condition) {
				$restriction_condition = true;
				if($cod_reverse_current_condition && count($checkout_products_sku) > 1 && count(array_diff($checkout_products_sku, $restriction_products_sku))>0) {
	  				$restriction_condition = !$restriction_condition;
	  			}
			} else {
				$restriction_condition = false;
			}

			$response = [
				'restriction_condition' => $restriction_condition,
				'restricted_product_name' => array_values(array_unique($restricted_product_name, false))
			];

			return $response;
		}

		public function detect_disable_cod_discount_coupon_condition() {
			$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');

			$cod_disabled_discount_coupons = (isset($restriction_settings['cod_disabled_discount_coupons']) && !empty($restriction_settings['cod_disabled_discount_coupons'])) ? $restriction_settings['cod_disabled_discount_coupons'] : array();

			$applied_coupons = WC()->cart->get_applied_coupons();

			$disable_discount_coupons_condition = (count(array_intersect($applied_coupons, $cod_disabled_discount_coupons)) <= count($applied_coupons)) && count(array_intersect($applied_coupons, $cod_disabled_discount_coupons))>0;

			if($disable_discount_coupons_condition) {
				$disable_discount_coupons_condition = true;
			} else {
				$disable_discount_coupons_condition = false;
			}

			$response = [
				'disable_discount_coupons_condition' => $disable_discount_coupons_condition,
				'restrict_coupon' => array_intersect($applied_coupons, $cod_disabled_discount_coupons),
			];

			return $response;
		}


		public function detect_disable_user_roles_condition() {
			$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');

			$disable_user_roles = (isset($restriction_settings['disable_user_roles']) && !empty($restriction_settings['disable_user_roles'])) ? $restriction_settings['disable_user_roles'] : array();

			if( is_user_logged_in() ) { // check if there is a logged in user 
	 
				$user = wp_get_current_user(); // getting & setting the current user 
				$roles = ( array ) $user->roles; // obtaining the role 
				 
				$user_roles = $roles; // return the role for the current user 
				 
			
			} else {
					 
				$user_roles = array(); // if there is no logged in user return empty array  
			 
			}

			$disable_user_roles_condition = (count(array_intersect($user_roles, $disable_user_roles)) <= count($user_roles)) && count(array_intersect($user_roles, $disable_user_roles))>0;


			if($disable_user_roles_condition) {
				$disable_user_roles_condition = true;
			} else {
				$disable_user_roles_condition = false;
			}

			return $disable_user_roles_condition;
		}
	
		public function check_greater_less_than($cart_element, $disable_cod_product_based_on, $disable_cod_greater_than, $disable_cod_less_than,$criteria) {
			$disable_cod_condition = false;
			$notice = "";
			$localization_settings = get_option('coderockz_advance_cod_localization_settings');
			$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');
			$disable_cod_product_weight_quantity_consider = (isset($restriction_settings['disable_cod_product_weight_quantity_consider']) && !empty($restriction_settings['disable_cod_product_weight_quantity_consider'])) ? $restriction_settings['disable_cod_product_weight_quantity_consider'] : false;
			$cart_element_sum = array_sum($cart_element);
			if($disable_cod_product_based_on == "all") {
				if(( $disable_cod_greater_than != "" && $cart_element_sum > $disable_cod_greater_than ) && ($disable_cod_less_than != "" && $cart_element_sum < $disable_cod_less_than)) {
					$disable_cod_condition = true;
					$and_text = (isset($localization_settings['and_text']) && $localization_settings['and_text'] != "") ? $localization_settings['and_text'] : __( "and", 'coderockz-advance-cod' );
					if($criteria == "quantity") {
						$quantity_sum_between = (isset($localization_settings['quantity_sum_between']) && $localization_settings['quantity_sum_between'] != "") ? $localization_settings['quantity_sum_between'] : __( "Cash on Delivery is unavailable if sum of all products quantity between", 'coderockz-advance-cod' );
						$notice = $quantity_sum_between." ".$disable_cod_greater_than." ".$and_text." ".$disable_cod_less_than.".";
					} elseif ($criteria == "weight") {
						if($disable_cod_product_weight_quantity_consider) {
							$weight_sum_between = (isset($localization_settings['weight_quantity_sum_between']) && $localization_settings['weight_quantity_sum_between'] != "") ? $localization_settings['weight_quantity_sum_between'] : __( "Cash on Delivery is unavailable if sum of all products (weight x quantity) between", 'coderockz-advance-cod' );
						} else {
							$weight_sum_between = (isset($localization_settings['weight_sum_between']) && $localization_settings['weight_sum_between'] != "") ? $localization_settings['weight_sum_between'] : __( "Cash on Delivery is unavailable if sum of all products weight between", 'coderockz-advance-cod' );
						}
						
						$notice = $weight_sum_between." ".$disable_cod_greater_than." ".$and_text." ".$disable_cod_less_than.".";
					}
				} elseif ( $disable_cod_greater_than != "" && $cart_element_sum > $disable_cod_greater_than ) {
					$disable_cod_condition = true;
					if($criteria == "quantity") {
						$quantity_more_than = (isset($localization_settings['quantity_more_than']) && $localization_settings['quantity_more_than'] != "") ? $localization_settings['quantity_more_than'] : __( "Cash on Delivery is unavailable if sum of all products quantity is more than", 'coderockz-advance-cod' );
						$notice = $quantity_more_than." ".$disable_cod_greater_than.".";
					} elseif ($criteria == "weight") {
						if($disable_cod_product_weight_quantity_consider) {
							$weight_more_than = (isset($localization_settings['weight_quantity_more_than']) && $localization_settings['weight_quantity_more_than'] != "") ? $localization_settings['weight_quantity_more_than'] : __( "Cash on Delivery is unavailable if sum of all products (weight x quantity) is more than", 'coderockz-advance-cod' );
						} else {
							$weight_more_than = (isset($localization_settings['weight_more_than']) && $localization_settings['weight_more_than'] != "") ? $localization_settings['weight_more_than'] : __( "Cash on Delivery is unavailable if sum of all products weight is more than", 'coderockz-advance-cod' );
						}
						
						$notice = $weight_more_than." ".$disable_cod_greater_than.".";
					}
			    } elseif($disable_cod_less_than != "" && $cart_element_sum < $disable_cod_less_than) {
			    	$disable_cod_condition = true;
			    	if($criteria == "quantity") {
			    		$quantity_less_than = (isset($localization_settings['quantity_less_than']) && $localization_settings['quantity_less_than'] != "") ? $localization_settings['quantity_less_than'] : __( "Cash on Delivery is unavailable if sum of all products quantity is less than", 'coderockz-advance-cod' );
			    		$notice = $quantity_less_than." ".$disable_cod_less_than.".";
			    	} elseif ($criteria == "weight") {
			    		if($disable_cod_product_weight_quantity_consider) {
			    			$weight_less_than = (isset($localization_settings['weight_quantity_less_than']) && $localization_settings['weight_quantity_less_than'] != "") ? $localization_settings['weight_quantity_less_than'] : __( "Cash on Delivery is unavailable if sum of all products (weight x quantity) is less than", 'coderockz-advance-cod' );
			    		} else {
			    			$weight_less_than = (isset($localization_settings['weight_less_than']) && $localization_settings['weight_less_than'] != "") ? $localization_settings['weight_less_than'] : __( "Cash on Delivery is unavailable if sum of all products weight is less than", 'coderockz-advance-cod' );
			    		}
			    		
			    		$notice = $weight_less_than." ".$disable_cod_less_than.".";
			    	}
			    }

			} elseif($disable_cod_product_based_on == "any") {
				$greater_than_array = array_filter($cart_element, function($n) use($disable_cod_greater_than) { 
				    return $n > $disable_cod_greater_than;
				});

				$less_than_array = array_filter($cart_element, function($n) use($disable_cod_less_than){
				    return $n < $disable_cod_less_than;
				});

				if( ($disable_cod_greater_than != "" && count($greater_than_array) > 0) && ($disable_cod_less_than != "" && count($less_than_array) > 0) ) {
					$disable_cod_condition = true;
					$and_text = (isset($localization_settings['and_text']) && $localization_settings['and_text'] != "") ? $localization_settings['and_text'] : __( "and", 'coderockz-advance-cod' );
					if($criteria == "quantity") {
						$quantity_any_between = (isset($localization_settings['quantity_any_between']) && $localization_settings['quantity_any_between'] != "") ? $localization_settings['quantity_any_between'] : __( "Cash on Delivery is unavailable if any product quantity between", 'coderockz-advance-cod' );
						$notice = $quantity_any_between." ".$disable_cod_greater_than." ".$and_text." ".$disable_cod_less_than.".";
					} elseif ($criteria == "weight") {
						if($disable_cod_product_weight_quantity_consider) {
							$weight_any_between = (isset($localization_settings['weight_quantity_any_between']) && $localization_settings['weight_quantity_any_between'] != "") ? $localization_settings['weight_quantity_any_between'] : __( "Cash on Delivery is unavailable if any product (weight x quantity) between", 'coderockz-advance-cod' );
						} else {
							$weight_any_between = (isset($localization_settings['weight_any_between']) && $localization_settings['weight_any_between'] != "") ? $localization_settings['weight_any_between'] : __( "Cash on Delivery is unavailable if any product weight between", 'coderockz-advance-cod' );
						}
						
						$notice = $weight_any_between." ".$disable_cod_greater_than." ".$and_text." ".$disable_cod_less_than.".";
					}
				} elseif ($disable_cod_greater_than != "" && $disable_cod_less_than == "" && count($greater_than_array) > 0) {
					$disable_cod_condition = true;
					if($criteria == "quantity") {
						$any_quantity_more_than = (isset($localization_settings['any_quantity_more_than']) && $localization_settings['any_quantity_more_than'] != "") ? $localization_settings['any_quantity_more_than'] : __( "Cash on Delivery is unavailable if any product quantity is more than", 'coderockz-advance-cod' );
						$notice = $any_quantity_more_than." ".$disable_cod_greater_than.".";
					} elseif ($criteria == "weight") {
						if($disable_cod_product_weight_quantity_consider) {
							$any_weight_more_than = (isset($localization_settings['any_weight_quantity_more_than']) && $localization_settings['any_weight_quantity_more_than'] != "") ? $localization_settings['any_weight_quantity_more_than'] : __( "Cash on Delivery is unavailable if any product (weight x quantity) is more than", 'coderockz-advance-cod' );
						} else {
							$any_weight_more_than = (isset($localization_settings['any_weight_more_than']) && $localization_settings['any_weight_more_than'] != "") ? $localization_settings['any_weight_more_than'] : __( "Cash on Delivery is unavailable if any product weight is more than", 'coderockz-advance-cod' );
						}
						
						$notice = $any_weight_more_than." ".$disable_cod_greater_than.".";
					}
			    } elseif ($disable_cod_less_than != "" && $disable_cod_greater_than == "" && count($less_than_array) > 0) {
			    	$disable_cod_condition = true;
			    	if($criteria == "quantity") {
			    		$any_quantity_less_than = (isset($localization_settings['any_quantity_less_than']) && $localization_settings['any_quantity_less_than'] != "") ? $localization_settings['any_quantity_less_than'] : __( "Cash on Delivery is unavailable if any product quantity is less than", 'coderockz-advance-cod' );
			    		$notice = $any_quantity_less_than." ".$disable_cod_less_than.".";
			    	} elseif ($criteria == "weight") {
			    		if($disable_cod_product_weight_quantity_consider) {
			    			$any_weight_less_than = (isset($localization_settings['any_weight_quantity_less_than']) && $localization_settings['any_weight_quantity_less_than'] != "") ? $localization_settings['any_weight_quantity_less_than'] : __( "Cash on Delivery is unavailable if any product (weight x quantity) is less than", 'coderockz-advance-cod' );
			    		} else {
			    			$any_weight_less_than = (isset($localization_settings['any_weight_less_than']) && $localization_settings['any_weight_less_than'] != "") ? $localization_settings['any_weight_less_than'] : __( "Cash on Delivery is unavailable if any product weight is less than", 'coderockz-advance-cod' );
			    		}
			    		
			    		$notice = $any_weight_less_than." ".$disable_cod_less_than.".";
			    	}
			    }

			}

			$response = [
				'disable_cod_condition' => $disable_cod_condition,
				'notice' => $notice,
			];

			return $response;

		}
		

		public function format_price($price, $orderId = null) {
	        return sprintf(get_woocommerce_price_format(), get_woocommerce_currency(wc_get_order($orderId)->get_currency()), $price);
	    }

	    public function postion_currency_symbol($currency_symbol,$price) {
	    	if(get_option( 'woocommerce_currency_pos' ) == 'right') {
				$price = $price.$currency_symbol;
			} elseif(get_option( 'woocommerce_currency_pos' ) == 'left_space') {
				$price = $currency_symbol.' '.$price;
			} elseif(get_option( 'woocommerce_currency_pos' ) == 'right_space') {
				$price = $price.' '.$currency_symbol;
			} if(get_option( 'woocommerce_currency_pos' ) == 'left') {
				$price = $currency_symbol.$price;
			}
	        return $price;
	    }	

	}

}