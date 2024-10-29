<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://coderockz.com
 * @since      1.0.0
 *
 * @package    Coderockz_Wc_Advance_Cod_Free
 * @subpackage Coderockz_Wc_Advance_Cod_Free/admin/partials
 */

$delivery_zones = WC_Shipping_Zones::get_zones();
$zone_regions = [];
$zone_post_code = [];
$zone_name = [];
$shipping_methods = [];
$shipping_classes = [];
$discount_coupons = [];
$helper = new Coderockz_Wc_Advance_Cod_Helper();

$shipping = new WC_Shipping();
$shipping_classes = $shipping->get_shipping_classes();
$shipping_classes = $helper->objectToArray($shipping_classes);

$args = array(
    'posts_per_page'   => -1,
    'orderby'          => 'title',
    'order'            => 'asc',
    'post_type'        => 'shop_coupon',
    'post_status'      => 'publish',
);
    
$coupons = get_posts( $args );

$coupon_names = array();
foreach ( $coupons as $coupon ) {
    $coupon_name = $coupon->post_name;
    array_push( $discount_coupons, $coupon_name );
}

foreach ((array) $delivery_zones as $key => $the_zone ) {
	$zone_state_code = [];
	$zone = new WC_Shipping_Zone($the_zone['id']);
	$zone_name[$the_zone['id']] = $zone->get_zone_name();
	$zone_shipping_methods = $zone->get_shipping_methods( true, 'values' );

	foreach ( $zone_shipping_methods as $instance_id => $shipping_method ) 
    {
        if($shipping_method->id != 'flexible_shipping' && $shipping_method->id != 'table_rate') {
        	$shipping_methods[$shipping_method->id.':'.$instance_id] = $zone->get_zone_name().' - '.$shipping_method->get_title();
        }
    }

	$zone_string = $zone->get_formatted_location(50000);
	if(isset($zone_string) && $zone_string != ''){
		$zone_array = explode(", ",$zone_string);
	}
	$zone_locations = $zone->get_zone_locations();
	$zone_locations = $helper->objectToArray($zone_locations);
	foreach($zone_locations as $zone_location) {
		if($zone_location['type'] == "state") {
			$position = strpos($zone_location['code'],':');
			$zone_state_code[] = substr($zone_location['code'],($position+1));
		} else if($zone_location['type'] == "postcode") {
			$zone_post_code[] = $zone_location['code'];
		}
	}

	foreach($zone_state_code as $key => $code) {
		$zone_regions[$code] = $zone_array[$key];
	}

}
/*$shipping_methods = array_unique($shipping_methods, false);
$shipping_methods = array_values($shipping_methods);*/
$store_products = [];
if(get_option('coderockz_advance_cod_large_product_list') == false) {
	$args = array(
	    'post_type' => 'product',
	    'numberposts' => -1,
	);
	$products = get_posts( $args );
	foreach($products as $product) {
		$product_s = wc_get_product( $product->ID );
		if ($product_s->get_type() == 'variable' || $product_s->get_type() == 'pw-gift-card') {
		    $args = array(
		        'post_parent' => $product->ID,
		        'post_type'   => 'product_variation',
		        'numberposts' => -1,
		    );
		    $variations = $product_s->get_available_variations();
		    foreach($variations as $variation) {
		    	
			    $variation_id = $variation['variation_id'];
			    $variation = new WC_Product_Variation($variation_id);
				$store_products[$variation_id] = $variation->get_title()." - ".implode(", ", $variation->get_variation_attributes());
			    
			    /*$variation = wc_get_product($variation_id);
				$store_products[$variation_id] = $variation->get_formatted_name();*/
		    }
		    
		} else {
			$store_products[$product->ID] = $product_s->get_name();
			/*$store_products[$product->ID] = $product_s->get_formatted_name();*/
		}
	}

}

global $wp_roles;

if ( ! isset( $wp_roles ) )
    $wp_roles = new WP_Roles();
$user_roles = $wp_roles->get_names();


$all_categories = get_categories( ['taxonomy' => 'product_cat', 'orderby' => 'name', 'hide_empty' => '0'] );

global $woocommerce;
$countries_obj = new WC_Countries();
$countries = $countries_obj->__get('countries');
$states = $countries_obj->get_states();
$all_states = [];
foreach($states as $key => $value) {
	if(!empty($value)) {
		foreach($value as $code => $state) {
			$all_states[$key.':'.$code] = WC()->countries->countries[ $key ] .' - '.$state;
		}
	}
}


$restriction_settings = get_option('coderockz_advance_cod_restriction_settings');
$localization_settings = get_option('coderockz_advance_cod_localization_settings');


?>
<div class="coderockz-advance-cod-wrap">
<div class="coderockz-advance-cod-container">		
	<div class="coderockz-advance-cod-container-header">
		<img style="max-width: 75px;float: left;display: block;padding-bottom: 2px;" src="<?php echo CODEROCKZ_WC_ADVANCE_COD_FREE_URL; ?>admin/images/cod-logo.png" alt="coderockz-advance-cod">
		<div style="float:left;margin-left:15px;margin-top:10px;">
		<p style="margin: 0!important;text-transform:uppercase;border-bottom:2px solid #048B00;padding-bottom:3px;font-size: 20px;font-weight: 700;color: #048B00;">WooCommerce</p>
		<p style="margin: 0!important;text-transform:uppercase;padding-top:3px;font-size: 11px;color: #048B00;font-weight: 600;">Advance Cash On Delivery</p>
		</div>
		<p style="float: right;margin-top: 30px;color: #bbb">Current Version <?php echo CODEROCKZ_WC_ADVANCE_COD_FREE_VERSION; ?></p>
	</div>
	<div class="coderockz-advance-cod-vertical-tabs">
		<div class="coderockz-advance-cod-tabs">
			<button data-tab="tab1"><i class="dashicons dashicons-admin-generic" style="margin-bottom: 3px;margin-right: 10px;"></i><?php _e('General Settings', 'coderockz-advance-cod'); ?></button>
			<button data-tab="tab2"><i class="dashicons dashicons-translation" style="margin-bottom: 3px;margin-right: 10px;"></i><?php _e('Localization', 'coderockz-advance-cod'); ?></button>	
		</div>
		<div class="coderockz-advance-cod-maincontent">

			<div data-tab="tab1" class="coderockz-advance-cod-tabcontent">
                <div class="coderockz-advance-cod-card">
					<p class="coderockz-advance-cod-card-header"><?php _e('Price Based Restriction', 'coderockz-advance-cod'); ?></p>
					<div class="coderockz-advance-cod-card-body">
						<p class="coderockz-advance-cod-restriction-settings-notice"><span class="dashicons dashicons-yes"></span><?php _e(' Settings Changed Successfully', 'coderockz-advance-cod'); ?></p>
	                    <form action="" method="post" id ="coderockz_advance_cod_restriction_settings_form_submit">
	                        <?php wp_nonce_field('coderockz_advance_cod_nonce'); ?>
	                        <div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_greater_than"><?php _e('Disable COD If Order Total is More Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input style="width:115px;" id="coderockz_advance_cod_disable_cod_greater_than" name="coderockz_advance_cod_disable_cod_greater_than" type="text" onkeyup="if(isNaN(parseFloat(Number(this.value))) || isNaN(parseInt(Number(this.value), 10))) this.value = null;" class="coderockz-advance-cod-input-field" value="<?php echo (isset($restriction_settings['disable_cod_greater_than']) && !empty($restriction_settings['disable_cod_greater_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_greater_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_less_than"><?php _e('Disable COD If Order Total Is Less Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input style="width:115px;" id="coderockz_advance_cod_disable_cod_less_than" name="coderockz_advance_cod_disable_cod_less_than" type="text" onkeyup="if(isNaN(parseFloat(Number(this.value))) || isNaN(parseInt(Number(this.value), 10))) this.value = null;" class="coderockz-advance-cod-input-field" value="<?php echo (isset($restriction_settings['disable_cod_less_than']) && !empty($restriction_settings['disable_cod_less_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Order Total Calculate Including Discount', 'coderockz-advance-cod'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip="Enable it if you want to calculate the cart amount including discount. Default is disable."><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_calculating_include_discount">
							       <input type="checkbox" name="coderockz_advance_cod_calculating_include_discount" id="coderockz_advance_cod_calculating_include_discount" <?php echo (isset($restriction_settings['calculating_include_discount']) && !empty($restriction_settings['calculating_include_discount'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Order Total Calculate Including Tax', 'coderockz-advance-cod'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip="Enable it if you want to calculate the cart amount including tax. Default is disable."><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_calculating_include_tax">
							       <input type="checkbox" name="coderockz_advance_cod_calculating_include_tax" id="coderockz_advance_cod_calculating_include_tax" <?php echo (isset($restriction_settings['calculating_include_tax']) && !empty($restriction_settings['calculating_include_tax'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Order Total Calculate Including Shipping Cost', 'coderockz-advance-cod'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip="Enable it if you want to calculate the cart amount including shipping cost. Default is disable."><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_calculating_include_shipping_cost">
							       <input type="checkbox" name="coderockz_advance_cod_calculating_include_shipping_cost" id="coderockz_advance_cod_calculating_include_shipping_cost" <?php echo (isset($restriction_settings['calculating_include_shipping_cost']) && !empty($restriction_settings['calculating_include_shipping_cost'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD for Discount Coupon', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Select the discount coupons for which you don't want to show the Cash On delivery Method."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_disabled_discount_coupons" name="coderockz_advance_cod_disabled_discount_coupons" class="coderockz_advance_cod_disabled_discount_coupons" multiple>
                                
                                <?php
								
                                foreach ($discount_coupons as $discount_coupon) {

                                	$selected = isset($restriction_settings['cod_disabled_discount_coupons']) && !empty($restriction_settings['cod_disabled_discount_coupons']) && in_array($discount_coupon,$restriction_settings['cod_disabled_discount_coupons']) ? "selected" : "";
                                    echo '<option value="'.esc_attr($discount_coupon).'" '.$selected.'>'.esc_attr($discount_coupon).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>

	                        <input class="coderockz-advance-cod-submit-btn" type="submit" name="coderockz_advance_cod_restriction_settings_form_submit" value="<?php _e('Save Changes', 'coderockz-advance-cod'); ?>" />
	                    </form>
                	</div>

                </div>
                <div class="coderockz-advance-cod-card">
					<p class="coderockz-advance-cod-card-header"><?php _e('User Based Restriction', 'coderockz-advance-cod'); ?></p>
					<div class="coderockz-advance-cod-card-body">
						<p class="coderockz-advance-cod-user-restriction-settings-notice"><span class="dashicons dashicons-yes"></span><?php _e(' Settings Changed Successfully', 'coderockz-advance-cod'); ?></p>
	                    <form action="" method="post" id ="coderockz_advance_cod_user_restriction_settings_form_submit">
	                        <?php wp_nonce_field('coderockz_advance_cod_nonce'); ?>
	                        
	                        <div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_user_email"><?php _e('Disable COD If User Emails', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		<?php 
	                    		
	                    		$disable_cod_user_emails = isset($restriction_settings['disable_cod_user_emails']) && !empty($restriction_settings['disable_cod_user_emails']) ? $restriction_settings['disable_cod_user_emails'] : array();
	                        	$disable_cod_user_emails = implode(",",$disable_cod_user_emails);
	                    		?>
	                    		<textarea id="coderockz_advance_cod_disable_user_email" name="coderockz_advance_cod_disable_user_email" type="text" class="coderockz-advance-cod-textarea-field" placeholder="<?php echo "Comma(,) separated Emails OR&#10;Each email in a new line"; ?>" autocomplete="off"><?php echo $disable_cod_user_emails; ?></textarea>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_user_phone"><?php _e('Disable COD If User Phone', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		<?php 
	                    		$disable_cod_user_phone = isset($restriction_settings['disable_cod_user_phone']) && !empty($restriction_settings['disable_cod_user_phone']) ? $restriction_settings['disable_cod_user_phone'] : array();
	                        	$disable_cod_user_phone = implode(",",$disable_cod_user_phone);
	                    		?> 
	                    		<textarea id="coderockz_advance_cod_disable_user_phone" name="coderockz_advance_cod_disable_user_phone" type="text" class="coderockz-advance-cod-textarea-field" placeholder="<?php echo "Comma(,) separated Phone OR&#10;Each Phone in a new line"; ?>" autocomplete="off"><?php echo $disable_cod_user_phone; ?></textarea>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD for User Role', 'coderockz-advance-cod'); ?><br/><span style="font-size: 11px;font-style: italic;color: lightseagreen;"><?php _e('(User must be Logged in)', 'coderockz-advance-cod'); ?></span></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Select the user role for which you don't want to show the cash on delivery method."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select style="width:400px;" id="coderockz_advance_cod_disable_user_roles" name="coderockz_advance_cod_disable_user_roles" class="coderockz_advance_cod_disable_user_roles" multiple>
                                
                                <?php
                                $disable_user_roles = [];
								if(isset($restriction_settings['disable_user_roles']) && !empty($restriction_settings['disable_user_roles'])) {
									foreach ($restriction_settings['disable_user_roles'] as $disable_role) {
										$disable_user_roles[] = stripslashes($disable_role);
									}
								}
								
                                foreach ($user_roles as $key => $value) {

                                	$selected = isset($restriction_settings['disable_user_roles']) && !empty($restriction_settings['disable_user_roles']) && in_array($key,$disable_user_roles) ? "selected" : "";
                                    echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_attr($value).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>


	                        <input class="coderockz-advance-cod-submit-btn" type="submit" name="coderockz_advance_cod_user_restriction_settings_form_submit" value="<?php _e('Save Changes', 'coderockz-advance-cod'); ?>" />
	                    </form>
                	</div>

                </div>

                <div class="coderockz-advance-cod-card">
					<p class="coderockz-advance-cod-card-header"><?php _e('Category/Product Based Restriction', 'coderockz-advance-cod'); ?></p>
					<div class="coderockz-advance-cod-card-body">
						
						<p class="coderockz-advance-cod-tab-warning"><span class="dashicons dashicons-megaphone"></span><?php _e('For Individual Product option, if product is simple input the product ID and if product is variable input the variation ID', 'coderockz-advance-cod'); ?></p>

						<p class="coderockz-advance-cod-cat-pro-restriction-settings-notice"><span class="dashicons dashicons-yes"></span><?php _e(' Settings Changed Successfully', 'coderockz-advance-cod'); ?></p>
	                    <form action="" method="post" id ="coderockz_advance_cod_cat_pro_restriction_settings_form_submit">
	                        <?php wp_nonce_field('coderockz_advance_cod_nonce'); ?>
	                        <div style="border:1px solid #ddd;border-radius: 4px;margin: 10px 0;">
	                        <div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD For Product Categories', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Select the product categories for which you don't want to give the facility of cash on delivery."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_restrict_cod_categories" name="coderockz_advance_cod_restrict_cod_categories" class="coderockz_advance_cod_restrict_cod_categories" multiple>
                                
                                <?php

                                $restrict_cod_categories = [];

								if(isset($restriction_settings['restrict_cod_categories']) && !empty($restriction_settings['restrict_cod_categories'])) {
									foreach ($restriction_settings['restrict_cod_categories'] as $hide_cat) {
										$restrict_cod_categories[] = stripslashes($hide_cat);
									}
								}
                                foreach ($all_categories as $cat) {

                                	$selected = isset($restriction_settings['restrict_cod_categories']) && !empty($restriction_settings['restrict_cod_categories']) && in_array(htmlspecialchars_decode($cat->name),$restrict_cod_categories) ? "selected" : "";
                                    echo '<option value="'.esc_attr($cat->name).'" '.$selected.'>'.esc_attr($cat->name).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>

                    		<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD For Individual Product', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Select the product for which you don't want to give the facility of cash on delivery."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<?php 
	                        	$restrict_cod_individual_product = isset($restriction_settings['restrict_cod_products']) && !empty($restriction_settings['restrict_cod_products']) ? $restriction_settings['restrict_cod_products'] : array();
	                        	$restrict_cod_individual_product = implode(",",$restrict_cod_individual_product);
	                        	?>
	                    		<input id="coderockz_advance_cod_restrict_cod_individual_product_input" name="coderockz_advance_cod_restrict_cod_individual_product_input" type="text" class="coderockz_advance_cod_restrict_cod_individual_product_input coderockz-advance-cod-input-field" value="<?php echo $restrict_cod_individual_product; ?>" placeholder="<?php _e('Comma(,) separated Product/Variation ID', 'coderockz-advance-cod'); ?>" autocomplete="off"/>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_sku"><?php _e('Disable COD If Product SKU', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		<?php

	                    		$disable_cod_sku = isset($restriction_settings['disable_cod_sku']) && !empty($restriction_settings['disable_cod_sku']) ? $restriction_settings['disable_cod_sku'] : array();
	                        	$disable_cod_sku = implode(",",$disable_cod_sku);

	                    		?>
	                    		
	                    		<textarea id="coderockz_advance_cod_disable_cod_sku" name="coderockz_advance_cod_disable_cod_sku" type="text" class="coderockz-advance-cod-textarea-field" placeholder="<?php echo "Comma(,) separated SKU OR&#10;Each SKU in a new line"; ?>" autocomplete="off"><?php echo $disable_cod_sku; ?></textarea>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Enable COD if Cart Has Regular Product/Category/SKU Along With Restricted Product/Category/SKU'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip="If there is COD restriction category's products or COD restriction products in the cart then whatever there are other category's products or other products, the COD option is hidden. Enable it if you want to reverse it."><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_restrict_cod_reverse_current_condition">
							       <input type="checkbox" name="coderockz_advance_cod_restrict_cod_reverse_current_condition" id="coderockz_advance_cod_restrict_cod_reverse_current_condition" <?php echo (isset($restriction_settings['cod_reverse_current_condition']) && !empty($restriction_settings['cod_reverse_current_condition'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Disable COD if Cart Has Virtual and Downloadable Products'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_restrict_cod_virtual_downloadable_products">
							       <input type="checkbox" name="coderockz_advance_cod_restrict_cod_virtual_downloadable_products" id="coderockz_advance_cod_restrict_cod_virtual_downloadable_products" <?php echo (isset($restriction_settings['restrict_cod_virtual_downloadable_products']) && !empty($restriction_settings['restrict_cod_virtual_downloadable_products'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>
	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Disable COD if Cart Has Backorder Products'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_restrict_cod_backorder_products">
							       <input type="checkbox" name="coderockz_advance_cod_restrict_cod_backorder_products" id="coderockz_advance_cod_restrict_cod_backorder_products" <?php echo (isset($restriction_settings['restrict_cod_backorder_products']) && !empty($restriction_settings['restrict_cod_backorder_products'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>
	                    	</div>
	                    	<div style="border:1px solid #ddd;border-radius: 4px;margin: 10px 0;">
	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_stock_greater_than"><?php _e('Disable COD If Any Product Stock is More Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input onkeyup="if(!Number.isInteger(Number(this.value)) || this.value < 1) this.value = null;" style="width:115px!important;" id="coderockz_advance_cod_disable_cod_stock_greater_than" name="coderockz_advance_cod_disable_cod_stock_greater_than" type="number" class="coderockz-advance-cod-number-field" value="<?php echo (isset($restriction_settings['disable_cod_stock_greater_than']) && !empty($restriction_settings['disable_cod_stock_greater_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_stock_greater_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_stock_less_than"><?php _e('Disable COD If Any Product Stock is Less Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input onkeyup="if(!Number.isInteger(Number(this.value)) || this.value < 1) this.value = null;" style="width:115px!important;" id="coderockz_advance_cod_disable_cod_stock_less_than" name="coderockz_advance_cod_disable_cod_stock_less_than" type="number" class="coderockz-advance-cod-number-field" value="<?php echo (isset($restriction_settings['disable_cod_stock_less_than']) && !empty($restriction_settings['disable_cod_stock_less_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_stock_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>
	                    	</div>
	                    	<div style="border:1px solid #ddd;border-radius: 4px;margin: 10px 0;">
	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_quantity_greater_than"><?php _e('Disable COD If Product Quantity is More Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input onkeyup="if(!Number.isInteger(Number(this.value)) || this.value < 1) this.value = null;" style="width:115px!important;" id="coderockz_advance_cod_disable_cod_quantity_greater_than" name="coderockz_advance_cod_disable_cod_quantity_greater_than" type="number" class="coderockz-advance-cod-number-field" value="<?php echo (isset($restriction_settings['disable_cod_quantity_greater_than']) && !empty($restriction_settings['disable_cod_quantity_greater_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_quantity_greater_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_quantity_less_than"><?php _e('Disable COD If Product Qunatity is Less Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input onkeyup="if(!Number.isInteger(Number(this.value)) || this.value < 1) this.value = null;" style="width:115px!important;" id="coderockz_advance_cod_disable_cod_quantity_less_than" name="coderockz_advance_cod_disable_cod_quantity_less_than" type="number" class="coderockz-advance-cod-number-field" value="<?php echo (isset($restriction_settings['disable_cod_quantity_less_than']) && !empty($restriction_settings['disable_cod_quantity_less_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_quantity_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_product_quantity_based_on"><?php _e('Disable COD for Product Quantity Based On', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Default is All products."><span class="dashicons dashicons-editor-help"></span></p>
	                    		<select style="width:115px!important;" class="coderockz-advance-cod-select-field" name="coderockz_advance_cod_disable_cod_product_quantity_based_on">
	                    			<option value="all" <?php if(isset($restriction_settings['disable_cod_product_quantity_based_on']) && $restriction_settings['disable_cod_product_quantity_based_on'] == "all"){ echo "selected"; } ?>>All Products</option>
									<option value="any" <?php if(isset($restriction_settings['disable_cod_product_quantity_based_on']) && $restriction_settings['disable_cod_product_quantity_based_on'] == "any"){ echo "selected"; } ?>>Any Product</option>			
								</select>
	                    	</div>
	                    	</div>
	                    	<div style="border:1px solid #ddd;border-radius: 4px;margin: 10px 0;">
	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_weight_greater_than"><?php _e('Disable COD If Product Weight is More Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input style="width:115px!important;" id="coderockz_advance_cod_disable_cod_weight_greater_than" name="coderockz_advance_cod_disable_cod_weight_greater_than" type="text" onkeyup="if(isNaN(parseFloat(Number(this.value))) || isNaN(parseInt(Number(this.value), 10))) this.value = null;" class="coderockz-advance-cod-input-field" value="<?php echo (isset($restriction_settings['disable_cod_weight_greater_than']) && !empty($restriction_settings['disable_cod_weight_greater_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_weight_greater_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_weight_less_than"><?php _e('Disable COD If Product Weight is Less Than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input style="width:115px!important;" id="coderockz_advance_cod_disable_cod_weight_less_than" name="coderockz_advance_cod_disable_cod_weight_less_than" type="text" onkeyup="if(isNaN(parseFloat(Number(this.value))) || isNaN(parseInt(Number(this.value), 10))) this.value = null;" class="coderockz-advance-cod-input-field" value="<?php echo (isset($restriction_settings['disable_cod_weight_less_than']) && !empty($restriction_settings['disable_cod_weight_less_than'])) ? stripslashes(esc_attr($restriction_settings['disable_cod_weight_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_product_weight_based_on"><?php _e('Disable COD for Product Weight Based On', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Default is All products."><span class="dashicons dashicons-editor-help"></span></p>
	                    		<select style="width:115px!important;" class="coderockz-advance-cod-select-field" name="coderockz_advance_cod_disable_cod_product_weight_based_on">
	                    			<option value="all" <?php if(isset($restriction_settings['disable_cod_product_weight_based_on']) && $restriction_settings['disable_cod_product_weight_based_on'] == "all"){ echo "selected"; } ?>>All Products</option>
									<option value="any" <?php if(isset($restriction_settings['disable_cod_product_weight_based_on']) && $restriction_settings['disable_cod_product_weight_based_on'] == "any"){ echo "selected"; } ?>>Any Product</option>			
								</select>
	                    	</div>
	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Multiply Cart Product Weight by Quantity'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_disable_cod_product_weight_quantity_consider">
							       <input type="checkbox" name="coderockz_advance_cod_disable_cod_product_weight_quantity_consider" id="coderockz_advance_cod_disable_cod_product_weight_quantity_consider" <?php echo (isset($restriction_settings['disable_cod_product_weight_quantity_consider']) && !empty($restriction_settings['disable_cod_product_weight_quantity_consider'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>
	                    	</div>


	                        <input class="coderockz-advance-cod-submit-btn" type="submit" name="coderockz_advance_cod_cat_pro_restriction_settings_form_submit" value="<?php _e('Save Changes', 'coderockz-advance-cod'); ?>" />
	                    </form>
                	</div>

                </div>
                <div class="coderockz-advance-cod-card">
					<p class="coderockz-advance-cod-card-header"><?php _e('WooCommerce Shipping Based Restriction', 'coderockz-advance-cod'); ?></p>
					<div class="coderockz-advance-cod-card-body">
						<p class="coderockz-advance-cod-shipping-restriction-settings-notice"><span class="dashicons dashicons-yes"></span><?php _e(' Settings Changed Successfully', 'coderockz-advance-cod'); ?></p>
	                    <form action="" method="post" id ="coderockz_advance_cod_shipping_restriction_settings_form_submit">
	                        <?php wp_nonce_field('coderockz_advance_cod_nonce'); ?>
	                        <div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD For Shipping Zone', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Cash on delivery is disabled for the selected shipping zone."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_cod_disabled_zone" name="coderockz_advance_cod_cod_disabled_zone" class="coderockz_advance_cod_cod_disabled_zone" multiple>
                                <?php
                                $cod_disabled_zones = [];
								if(isset($restriction_settings['cod_disabled_zones']) && !empty($restriction_settings['cod_disabled_zones'])) {
									foreach ($restriction_settings['cod_disabled_zones'] as $disabled_zone) {
										$cod_disabled_zones[] = stripslashes($disabled_zone);
									}
								}
                                foreach ($zone_name as $key => $value) {
                                	$selected = isset($restriction_settings['cod_disabled_zones']) && !empty($restriction_settings['cod_disabled_zones']) && in_array($key,$restriction_settings['cod_disabled_zones']) ? "selected" : "";
                                    echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_attr($value).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Enable COD Only For Countries', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Cash on delivery is enabled for the selected countries."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_cod_enabled_country" name="coderockz_advance_cod_cod_enabled_country" class="coderockz_advance_cod_cod_enabled_country" multiple>
                                <?php
                                foreach ($countries as $key => $value) {
                                	$selected = isset($restriction_settings['cod_enabled_country']) && !empty($restriction_settings['cod_enabled_country']) && in_array($key,$restriction_settings['cod_enabled_country']) ? "selected" : "";
                                    echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_attr($value).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Enable COD Only For States', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Cash on delivery is enabled for the selected states."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_cod_enabled_states" name="coderockz_advance_cod_cod_enabled_states" class="coderockz_advance_cod_cod_enabled_states" multiple>
                                <?php
                                foreach ($all_states as $key => $value) {
                                	$selected = isset($restriction_settings['cod_enabled_states']) && !empty($restriction_settings['cod_enabled_states']) && in_array($key,$restriction_settings['cod_enabled_states']) ? "selected" : "";
                                    echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_attr($value).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_enable_cod_zip"><?php _e('Enable COD for Zip/Postal Code', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		<?php

	                    		$enable_cod_zip = isset($restriction_settings['enable_cod_zip']) && !empty($restriction_settings['enable_cod_zip']) ? $restriction_settings['enable_cod_zip'] : array();
	                        	$enable_cod_zip = implode(",",$enable_cod_zip);

	                    		?>
	                    		
	                    		<textarea id="coderockz_advance_cod_enable_cod_zip" name="coderockz_advance_cod_enable_cod_zip" type="text" class="coderockz-advance-cod-textarea-field" placeholder="<?php echo "Comma(,) separated postal code OR&#10;Each postal code in a new line&#10;Postcodes containing wildcards (e.g. CB23*) are supported&#10;Fully numeric ranges (e.g. 90210...99000) are also supported"; ?>" autocomplete="off"><?php echo $enable_cod_zip; ?></textarea>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD for Shipping Method', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Select the shipping methods for which you don't want to show the Cash On delivery Method."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_disabled_shipping_methods" name="coderockz_advance_cod_disabled_shipping_methods" class="coderockz_advance_cod_disabled_shipping_methods" multiple>
                                
                                <?php
								
                                foreach ($shipping_methods as $key => $shipping_method) {

                                	$selected = isset($restriction_settings['cod_disabled_shipping_methods']) && !empty($restriction_settings['cod_disabled_shipping_methods']) && in_array($key,$restriction_settings['cod_disabled_shipping_methods']) ? "selected" : "";
                                    echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_attr($shipping_method).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>
	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_disable_cod_shipping_method_value"><?php _e('Disable COD If Shipping Method Value', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		<?php

	                    		$disable_cod_shipping_method_value = isset($restriction_settings['disable_cod_shipping_method_value']) && !empty($restriction_settings['disable_cod_shipping_method_value']) ? $restriction_settings['disable_cod_shipping_method_value'] : array();
	                        	$disable_cod_shipping_method_value = implode(",",$disable_cod_shipping_method_value);

	                    		?>
	                    		
	                    		<textarea id="coderockz_advance_cod_disable_cod_shipping_method_value" name="coderockz_advance_cod_disable_cod_shipping_method_value" type="text" class="coderockz-advance-cod-textarea-field" placeholder="<?php echo "Comma(,) separated Shipping method value OR&#10;Each Shipping method value in a new line"; ?>" autocomplete="off"><?php echo $disable_cod_shipping_method_value; ?></textarea>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label"><?php _e('Disable COD for Shipping Classes', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip="Select the shipping classes for which you don't want to show the Cash On delivery Method."><span class="dashicons dashicons-editor-help"></span></p>
	                        	<select id="coderockz_advance_cod_disabled_shipping_classes" name="coderockz_advance_cod_disabled_shipping_classes" class="coderockz_advance_cod_disabled_shipping_classes" multiple>
                                
                                <?php
								
                                foreach ($shipping_classes as $key => $shipping_class) {

                                	$selected = isset($restriction_settings['cod_disabled_shipping_classes']) && !empty($restriction_settings['cod_disabled_shipping_classes']) && in_array($shipping_class['term_id'],$restriction_settings['cod_disabled_shipping_classes']) ? "selected" : "";
                                    echo '<option value="'.esc_attr($shipping_class['term_id']).'" '.$selected.'>'.esc_attr($shipping_class['name']).'</option>';
                                }
                                ?>
                                </select>
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                        	<span class="coderockz-advance-cod-form-label"><?php _e('Enable COD if Cart Has Regular Product Along With Restricted Shipping Class Product'); ?></span>
	                        	<p class="coderockz-advance-cod-tooltip" tooltip="If there is COD restriction shipping class products in the cart then whatever there are other products, the COD option is hidden. Enable it if you want to reverse it."><span class="dashicons dashicons-editor-help"></span></p>
							    <label class="coderockz-advance-cod-toogle-switch" for="coderockz_advance_cod_restrict_cod_reverse_current_condition_shipping_class">
							       <input type="checkbox" name="coderockz_advance_cod_restrict_cod_reverse_current_condition_shipping_class" id="coderockz_advance_cod_restrict_cod_reverse_current_condition_shipping_class" <?php echo (isset($restriction_settings['cod_reverse_current_condition_shipping_class']) && !empty($restriction_settings['cod_reverse_current_condition_shipping_class'])) ? "checked" : "" ?>/>
							       <div class="coderockz-advance-cod-toogle-slider coderockz-advance-cod-toogle-round"></div>
							    </label>
	                    	</div>

	                        <input class="coderockz-advance-cod-submit-btn" type="submit" name="coderockz_advance_cod_shipping_restriction_settings_form_submit" value="<?php _e('Save Changes', 'coderockz-advance-cod'); ?>" />
	                    </form>
                	</div>

                </div>
			</div>
			
			<div data-tab="tab2" class="coderockz-advance-cod-tabcontent">
				<div class="coderockz-advance-cod-card">
					<p class="coderockz-advance-cod-card-header"><?php _e('Localization Settings', 'coderockz-advance-cod'); ?></p>
					<div class="coderockz-advance-cod-card-body">
						<p class="coderockz-advance-cod-localization-settings-notice"><span class="dashicons dashicons-yes"></span><?php _e(' Settings Changed Successfully', 'coderockz-advance-cod'); ?></p>
	                    <form action="" method="post" id ="coderockz_advance_cod_localization_settings_form_submit">
	                        <?php wp_nonce_field('coderockz_advance_cod_nonce'); ?>

	                        <div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_total_amount_between_text"><?php _e('Cash on Delivery is unavailable if cart amount is between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_total_amount_between_text" name="coderockz_advance_cod_total_amount_between_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['total_amount_between_text']) && !empty($localization_settings['total_amount_between_text'])) ? stripslashes(esc_attr($localization_settings['total_amount_between_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                        <div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_total_more_than_text"><?php _e('Cash on Delivery is unavailable if cart amount is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_total_more_than_text" name="coderockz_advance_cod_total_more_than_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['total_more_than_text']) && !empty($localization_settings['total_more_than_text'])) ? stripslashes(esc_attr($localization_settings['total_more_than_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_total_less_than_text"><?php _e('Cash on Delivery is unavailable if cart amount is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_total_less_than_text" name="coderockz_advance_cod_total_less_than_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['total_less_than_text']) && !empty($localization_settings['total_less_than_text'])) ? stripslashes(esc_attr($localization_settings['total_less_than_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_get_the_cod_text"><?php _e('to get the Cash On Delivery', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_get_the_cod_text" name="coderockz_advance_cod_get_the_cod_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['get_the_cod_text']) && !empty($localization_settings['get_the_cod_text'])) ? stripslashes(esc_attr($localization_settings['get_the_cod_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_discount_coupon_text"><?php _e('Cash on delivery is not available for discount coupon', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_discount_coupon_text" name="coderockz_advance_cod_discount_coupon_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['discount_coupon_text']) && !empty($localization_settings['discount_coupon_text'])) ? stripslashes(esc_attr($localization_settings['discount_coupon_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_category_product_sku_text"><?php _e('Cash on delivery is not available for', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_category_product_sku_text" name="coderockz_advance_cod_category_product_sku_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['category_product_sku_text']) && !empty($localization_settings['category_product_sku_text'])) ? stripslashes(esc_attr($localization_settings['category_product_sku_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_backorder_quantity_less_than"><?php _e('quantity must be less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_backorder_quantity_less_than" name="coderockz_advance_cod_backorder_quantity_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['backorder_quantity_less_than']) && !empty($localization_settings['backorder_quantity_less_than'])) ? stripslashes(esc_attr($localization_settings['backorder_quantity_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_quantity_sum_between"><?php _e('Cash on Delivery is unavailable if sum of all products quantity between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_quantity_sum_between" name="coderockz_advance_cod_quantity_sum_between" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['quantity_sum_between']) && !empty($localization_settings['quantity_sum_between'])) ? stripslashes(esc_attr($localization_settings['quantity_sum_between'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_sum_between"><?php _e('Cash on Delivery is unavailable if sum of all products weight between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_sum_between" name="coderockz_advance_cod_weight_sum_between" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_sum_between']) && !empty($localization_settings['weight_sum_between'])) ? stripslashes(esc_attr($localization_settings['weight_sum_between'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_quantity_sum_between"><?php _e('Cash on Delivery is unavailable if sum of all products (weight x quantity) between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_quantity_sum_between" name="coderockz_advance_cod_weight_quantity_sum_between" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_quantity_sum_between']) && !empty($localization_settings['weight_quantity_sum_between'])) ? stripslashes(esc_attr($localization_settings['weight_quantity_sum_between'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_and_text"><?php _e('and', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_and_text" name="coderockz_advance_cod_and_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['and_text']) && !empty($localization_settings['and_text'])) ? stripslashes(esc_attr($localization_settings['and_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_quantity_more_than"><?php _e('Cash on Delivery is unavailable if sum of all products quantity is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_quantity_more_than" name="coderockz_advance_cod_quantity_more_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['quantity_more_than']) && !empty($localization_settings['quantity_more_than'])) ? stripslashes(esc_attr($localization_settings['quantity_more_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_more_than"><?php _e('Cash on Delivery is unavailable if sum of all products weight is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_more_than" name="coderockz_advance_cod_weight_more_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_more_than']) && !empty($localization_settings['weight_more_than'])) ? stripslashes(esc_attr($localization_settings['weight_more_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_quantity_more_than"><?php _e('Cash on Delivery is unavailable if sum of all products (weight x quantity) is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_quantity_more_than" name="coderockz_advance_cod_weight_quantity_more_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_quantity_more_than']) && !empty($localization_settings['weight_quantity_more_than'])) ? stripslashes(esc_attr($localization_settings['weight_quantity_more_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_quantity_less_than"><?php _e('Cash on Delivery is unavailable if sum of all products quantity is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_quantity_less_than" name="coderockz_advance_cod_quantity_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['quantity_less_than']) && !empty($localization_settings['quantity_less_than'])) ? stripslashes(esc_attr($localization_settings['quantity_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_less_than"><?php _e('Cash on Delivery is unavailable if sum of all products weight is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_less_than" name="coderockz_advance_cod_weight_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_less_than']) && !empty($localization_settings['weight_less_than'])) ? stripslashes(esc_attr($localization_settings['weight_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_quantity_less_than"><?php _e('Cash on Delivery is unavailable if sum of all products (weight x quantity) is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_quantity_less_than" name="coderockz_advance_cod_weight_quantity_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_quantity_less_than']) && !empty($localization_settings['weight_quantity_less_than'])) ? stripslashes(esc_attr($localization_settings['weight_quantity_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_quantity_any_between"><?php _e('Cash on Delivery is unavailable if any product quantity between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_quantity_any_between" name="coderockz_advance_cod_quantity_any_between" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['quantity_any_between']) && !empty($localization_settings['quantity_any_between'])) ? stripslashes(esc_attr($localization_settings['quantity_any_between'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_any_between"><?php _e('Cash on Delivery is unavailable if any product weight between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_any_between" name="coderockz_advance_cod_weight_any_between" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_any_between']) && !empty($localization_settings['weight_any_between'])) ? stripslashes(esc_attr($localization_settings['weight_any_between'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_weight_quantity_any_between"><?php _e('Cash on Delivery is unavailable if any product (weight x quantity) between', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_weight_quantity_any_between" name="coderockz_advance_cod_weight_quantity_any_between" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['weight_quantity_any_between']) && !empty($localization_settings['weight_quantity_any_between'])) ? stripslashes(esc_attr($localization_settings['weight_quantity_any_between'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_any_quantity_more_than"><?php _e('Cash on Delivery is unavailable if any product quantity is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_any_quantity_more_than" name="coderockz_advance_cod_any_quantity_more_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['any_quantity_more_than']) && !empty($localization_settings['any_quantity_more_than'])) ? stripslashes(esc_attr($localization_settings['any_quantity_more_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_any_weight_more_than"><?php _e('Cash on Delivery is unavailable if any product weight is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_any_weight_more_than" name="coderockz_advance_cod_any_weight_more_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['any_weight_more_than']) && !empty($localization_settings['any_weight_more_than'])) ? stripslashes(esc_attr($localization_settings['any_weight_more_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_any_weight_quantity_more_than"><?php _e('Cash on Delivery is unavailable if any product (weight x quantity) is more than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_any_weight_quantity_more_than" name="coderockz_advance_cod_any_weight_quantity_more_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['any_weight_quantity_more_than']) && !empty($localization_settings['any_weight_quantity_more_than'])) ? stripslashes(esc_attr($localization_settings['any_weight_quantity_more_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_any_quantity_less_than"><?php _e('Cash on Delivery is unavailable if any product quantity is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_any_quantity_less_than" name="coderockz_advance_cod_any_quantity_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['any_quantity_less_than']) && !empty($localization_settings['any_quantity_less_than'])) ? stripslashes(esc_attr($localization_settings['any_quantity_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_any_weight_less_than"><?php _e('Cash on Delivery is unavailable if any product weight is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_any_weight_less_than" name="coderockz_advance_cod_any_weight_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['any_weight_less_than']) && !empty($localization_settings['any_weight_less_than'])) ? stripslashes(esc_attr($localization_settings['any_weight_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_any_weight_quantity_less_than"><?php _e('Cash on Delivery is unavailable if any product (weight x quantity) is less than', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_any_weight_quantity_less_than" name="coderockz_advance_cod_any_weight_quantity_less_than" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['any_weight_quantity_less_than']) && !empty($localization_settings['any_weight_quantity_less_than'])) ? stripslashes(esc_attr($localization_settings['any_weight_quantity_less_than'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_selected_country_text"><?php _e('selected country', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_selected_country_text" name="coderockz_advance_cod_selected_country_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['selected_country_text']) && !empty($localization_settings['selected_country_text'])) ? stripslashes(esc_attr($localization_settings['selected_country_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_selected_state_text"><?php _e('selected state/country', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_selected_state_text" name="coderockz_advance_cod_selected_state_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['selected_state_text']) && !empty($localization_settings['selected_state_text'])) ? stripslashes(esc_attr($localization_settings['selected_state_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_this_postcode_text"><?php _e('this postcode/zip', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_this_postcode_text" name="coderockz_advance_cod_this_postcode_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['this_postcode_text']) && !empty($localization_settings['this_postcode_text'])) ? stripslashes(esc_attr($localization_settings['this_postcode_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_selected_shipping_method_text"><?php _e('selected shipping method', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_selected_shipping_method_text" name="coderockz_advance_cod_selected_shipping_method_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['selected_shipping_method_text']) && !empty($localization_settings['selected_shipping_method_text'])) ? stripslashes(esc_attr($localization_settings['selected_shipping_method_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_this_email_text"><?php _e('this email', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_this_email_text" name="coderockz_advance_cod_this_email_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['this_email_text']) && !empty($localization_settings['this_email_text'])) ? stripslashes(esc_attr($localization_settings['this_email_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_this_phone_text"><?php _e('this phone', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_this_phone_text" name="coderockz_advance_cod_this_phone_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['this_phone_text']) && !empty($localization_settings['this_phone_text'])) ? stripslashes(esc_attr($localization_settings['this_phone_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                    	<div class="coderockz-advance-cod-form-group">
	                    		<label class="coderockz-advance-cod-form-label" for="coderockz_advance_cod_user_role_text"><?php _e('Cash on delivery is not available for you', 'coderockz-advance-cod'); ?></label>
	                    		<p class="coderockz-advance-cod-tooltip" tooltip=""><span class="dashicons dashicons-editor-help"></span></p>
	                    		
	                    		<input id="coderockz_advance_cod_user_role_text" name="coderockz_advance_cod_user_role_text" type="text" class="coderockz-advance-cod-input-field" value="<?php echo (isset($localization_settings['user_role_text']) && !empty($localization_settings['user_role_text'])) ? stripslashes(esc_attr($localization_settings['user_role_text'])) : "" ?>" placeholder="" autocomplete="off"/>	
	                        	
	                    	</div>

	                        <input class="coderockz-advance-cod-submit-btn" type="submit" name="coderockz_advance_cod_localization_settings_form_submit" value="<?php _e('Save Changes', 'coderockz-advance-cod'); ?>" />
	                    </form>
			        </div>
			    </div>
			</div>
		</div>
	</div>

</div>

</div>




