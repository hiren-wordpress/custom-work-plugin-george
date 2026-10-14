<?php

/** 
 *
 * @package Ultimate WooCommerce Auction PRO - business stripe add on
 * @author Nitesh Singh 
 * @since 1.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* add option of stripe */

if (isset($_POST['uwa_auto_stripe_autodebit'])){

	/* Capability + CSRF checks */
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to update these settings.', 'woo_ua' ) );
	}
	check_admin_referer( 'uwa_stripe_autodebit_save', 'uwa_sad_nonce' );

	if (isset($_POST['uwa_payment_gateway'])) {
		update_option('uwa_payment_gateway', sanitize_text_field($_POST['uwa_payment_gateway']));
	}
	
	if(isset($_POST['uwa_stripe_card_register_page']) == "1"){							
		update_option('uwa_stripe_card_register_page', "yes");
	}
	else{
		update_option('uwa_stripe_card_register_page', "no");
	}
	if(isset($_POST['uwa_stripe_card_myaccount_page']) == "1"){				
		update_option('uwa_stripe_card_myaccount_page', "yes");
	} 
	else{
		update_option('uwa_stripe_card_myaccount_page', "no");
	}
    

	/* Save data according to payment preference */
	if(isset($_POST['uwa_payment_preference_for_auction'])){							
		update_option('uwa_payment_preference_for_auction', sanitize_text_field($_POST['uwa_payment_preference_for_auction']));

		$uwa_payment_preference = $_POST['uwa_payment_preference_for_auction'];

		if($uwa_payment_preference == "uwa_stripe_hold_payment"){

			if(isset($_POST['uwa_stripe_hold_charge_type'])){							
				update_option('uwa_stripe_hold_charge_type', sanitize_text_field($_POST['uwa_stripe_hold_charge_type']));
			}

			if(isset($_POST['uwa_stripe_hold_fix_amount_charge_amt'])){							
				update_option('uwa_stripe_hold_fix_amount_charge_amt', sanitize_text_field($_POST['uwa_stripe_hold_fix_amount_charge_amt']));
			}

			if(isset($_POST['uwa_stripe_hold_percentage_amount_charge_amt'])){
				update_option('uwa_stripe_hold_percentage_amount_charge_amt', sanitize_text_field( wp_unslash( $_POST['uwa_stripe_hold_percentage_amount_charge_amt'] ) ));
			}

			update_option("uwa_stripe_charge_type", "");
			update_option("uwa_stripe_charge_type_partially_type", "");
			update_option("uwa_stripe_charge_type_partially_amt", "");


			if(isset($_POST['uwa_stripe_hold_debit_winner_amount']) == "1"){
				update_option('uwa_stripe_hold_debit_winner_amount', "yes");
			} 
			else{
				update_option('uwa_stripe_hold_debit_winner_amount', "no");
			}

		}

		if($uwa_payment_preference == "uwa_stripe_auto_debit_payment"){

			if(isset($_POST['uwa_stripe_charge_type'])){							
				update_option('uwa_stripe_charge_type', sanitize_text_field($_POST['uwa_stripe_charge_type']));
			}

			if(isset($_POST['uwa_stripe_charge_type_partially_type'])){
				update_option('uwa_stripe_charge_type_partially_type',
				sanitize_text_field( wp_unslash( $_POST['uwa_stripe_charge_type_partially_type'] ) ));
			}

			if(isset($_POST['uwa_stripe_charge_type_partially_amt'])){
				update_option('uwa_stripe_charge_type_partially_amt',
					sanitize_text_field( wp_unslash( $_POST['uwa_stripe_charge_type_partially_amt'] ) ));
			}

			update_option("uwa_stripe_hold_charge_type", "");
			update_option("uwa_stripe_hold_fix_amount_charge_amt", "");
			update_option("uwa_stripe_hold_percentage_amount_charge_amt", "");

			if(isset($_POST['uwa_stripe_tax_enable']) == "1"){
				update_option('uwa_stripe_tax_enable', "yes");
			} 
			else{
				update_option('uwa_stripe_tax_enable', "no");
			}

		}

	}

		
	if(isset($_POST['uwa_stripe_buyers_premium_enable']) == "1"){
		update_option('uwa_stripe_buyers_premium_enable', "yes");
	} 
	else{
		update_option('uwa_stripe_buyers_premium_enable', "no");
	}	
		
	if(isset($_POST['uwa_auto_stripe_text'])){
		update_option('uwa_auto_stripe_text', sanitize_text_field($_POST['uwa_auto_stripe_text']));
	}
	if(isset($_POST['uwa_auto_stripe_payment_text'])){
		update_option('uwa_auto_stripe_payment_text',
			sanitize_text_field( wp_unslash( $_POST['uwa_auto_stripe_payment_text'] ) ));
	}

	if(isset($_POST['uwa_payment_method_authorization_message_text'])){							
		update_option('uwa_payment_method_authorization_message_text', sanitize_text_field($_POST['uwa_payment_method_authorization_message_text']));
	}
	else{
		update_option('uwa_payment_method_authorization_message_text', "");
	}

	if(isset($_POST['uwa_payment_method_authorization_btn_text'])){
		update_option('uwa_payment_method_authorization_btn_text', sanitize_text_field($_POST['uwa_payment_method_authorization_btn_text']));
	}else{
		update_option('uwa_payment_method_authorization_btn_text', "");
	}

	if(isset($_POST['uwa_payment_method_unauthorization_btn_text'])){							
		update_option('uwa_payment_method_unauthorization_btn_text', sanitize_text_field($_POST['uwa_payment_method_unauthorization_btn_text']));
	}
	else{
		update_option('uwa_payment_method_unauthorization_btn_text', "");
	}
	
	if(isset($_POST['uwa_wc_billing_myaccount_page']) == "1"){							
		update_option('uwa_wc_billing_myaccount_page', "yes");
	}
	else{
		update_option('uwa_wc_billing_myaccount_page', "no");
	}
		
	/* vendor */	
/*	if(isset($_POST['uwa_stripe_charge_type_vendor'])){
		update_option('uwa_stripe_charge_type_vendor', 
			$_POST['uwa_stripe_charge_type_vendor']);
	}

	if(isset($_POST['uwa_stripe_charge_type_partially_type_vendor'])){
		update_option('uwa_stripe_charge_type_partially_type_vendor', 
			$_POST['uwa_stripe_charge_type_partially_type_vendor']);
	}
	
	if(isset($_POST['uwa_stripe_charge_type_partially_amt_vendor'])){
		update_option('uwa_stripe_charge_type_partially_amt_vendor', 
			$_POST['uwa_stripe_charge_type_partially_amt_vendor']);
	}	
		
	if(isset($_POST['uwa_stripe_buyers_premium_vendor_enable']) == "1"){				
		update_option('uwa_stripe_buyers_premium_vendor_enable', "yes");
	} 
	else{
		update_option('uwa_stripe_buyers_premium_vendor_enable', "no");
	}*/	


}


	$stripe_checked = "";
	$braintree_checked = "";
	$paystack_checked = "";
	$square_checked = "";

	$uwa_payment_gateway = get_option('uwa_payment_gateway');

	if ($uwa_payment_gateway == "uwa_payment_stripe" || $uwa_payment_gateway == FALSE){
		$stripe_checked = "checked";
	} else if ($uwa_payment_gateway == "uwa_payment_braintree"){
		$braintree_checked = "checked";
	} else if ($uwa_payment_gateway == "uwa_payment_paystack") { /* check_here  */
		$paystack_checked = "checked";
	} else {
		$square_checked = "checked";
	}

    $uwa_stripe_charge_type_partially_amt = "";
	$uwa_stripe_charge_type_partially_type = "";

	$uwa_stripe_hold_fix_amount_charge_amt = "";
	$uwa_stripe_hold_percentage_amount_charge_amt = "";
	$uwa_stripe_hold_debit_amount_chk = "";

	$is_card_register_page = get_option('uwa_stripe_card_register_page', "no");
	$is_card_myaccount_page = get_option('uwa_stripe_card_myaccount_page', "yes");

	$is_uwa_stripe_tax = get_option('uwa_stripe_tax_enable', "no");
	$is_uwa_stripe_buyers_premium = get_option('uwa_stripe_buyers_premium_enable', "no");
	$uwa_stripe_charge_type = get_option('uwa_stripe_charge_type');

	$uwa_payment_preference_for_auction = get_option('uwa_payment_preference_for_auction', "uwa_stripe_auto_debit_payment");
	$uwa_stripe_hold_charge_type = get_option('uwa_stripe_hold_charge_type');

	$uwa_stripe_charge_type_partially_type = get_option(
		'uwa_stripe_charge_type_partially_type');
	$uwa_stripe_charge_type_partially_amt = get_option(
		'uwa_stripe_charge_type_partially_amt');

	$uwa_stripe_hold_fix_amount_charge_amt = get_option('uwa_stripe_hold_fix_amount_charge_amt');
	$uwa_stripe_hold_percentage_amount_charge_amt = get_option('uwa_stripe_hold_percentage_amount_charge_amt');

	$is_stripe_hold_debit_winner_amount = get_option('uwa_stripe_hold_debit_winner_amount', "yes");
	
	$is_card_register_page == "yes" ? $register_chk = "checked": $register_chk = "";
	$is_card_myaccount_page == "yes" ? $myaccount_chk = "checked": $myaccount_chk = "";

	$is_uwa_stripe_tax == "yes" ? $uwa_stripe_tax_chk = "checked" : 
		$uwa_stripe_tax_chk = "";


	$is_uwa_stripe_buyers_premium == "yes" ? $uwa_stripe_buyers_chk = "checked" : 
		$uwa_stripe_buyers_chk = "";

	$is_stripe_hold_debit_winner_amount == "yes" ? $uwa_stripe_hold_debit_amount_chk = "checked" : 
		$uwa_stripe_hold_debit_amount_chk = "";		
	
	$uwa_auto_stripe_text = get_option('uwa_auto_stripe_text',"Auto Debit Via Stripe");
	$uwa_auto_stripe_payment_text = get_option('uwa_auto_stripe_payment_text',
		"Your payment is already done!! please place the order");	

	$uwa_payment_method_authorization_message_text = get_option('uwa_payment_method_authorization_message_text', "To ensure a secure bidding experience, we require a valid payment method before you can place bids. Your card will be verified with a small, refundable charge. This allows us to hold the bid amount when you participate in auctions.");
	$uwa_payment_method_authorization_btn_text = get_option('uwa_payment_method_authorization_btn_text', "Authorize for Bidding");
	$uwa_payment_method_unauthorization_btn_text = get_option('uwa_payment_method_unauthorization_btn_text', "Unauthorize");
	
	$is_billing_myaccount_page = get_option('uwa_wc_billing_myaccount_page', "no");
	$is_billing_myaccount_page == "yes" ? $my_bill_chk = "checked": $my_bill_chk = "";
	
	//vendor setting 
	/*$uwa_stripe_charge_type_partially_amt_vendor = "";
	$uwa_stripe_charge_type_partially_type_vendor = "";
	$uwa_stripe_charge_type_vendor = get_option('uwa_stripe_charge_type_vendor');
	$uwa_stripe_charge_type_partially_type_vendor = get_option(
		'uwa_stripe_charge_type_partially_type_vendor');
	$uwa_stripe_charge_type_partially_amt_vendor = get_option(
		'uwa_stripe_charge_type_partially_amt_vendor');
	$is_uwa_stripe_buyers_premium_vendor = get_option(
		'uwa_stripe_buyers_premium_vendor_enable', "no");
	$is_uwa_stripe_buyers_premium_vendor == "yes" ? $uwa_stripe_buyers_vendor_chk = "checked": $uwa_stripe_buyers_vendor_chk = "";*/
	
	$uwa_enabled_addons_list = uwa_enabled_addons();
?>

<div class="uwa_main_setting_content">
	<form  method='post' class='uwa_auction_setting_style'>
		<?php wp_nonce_field( 'uwa_stripe_autodebit_save', 'uwa_sad_nonce' ); ?>
		<table class="form-table">
			<tbody>
			<tr class="uwa_heading">
				<th colspan="2"><?php _e('Payment Gateway', 'woo_ua' ); ?>
				</th>
			</tr>
			<tr>
				<th scope="row"></th>									 
				<td>
					
					<input type="radio"  name="uwa_payment_gateway"  
						value="uwa_payment_stripe" <?php echo $stripe_checked; ?> > 
					<?php _e('Stripe', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>
				     	
					<input type="radio" name="uwa_payment_gateway" 
						value="uwa_payment_braintree"  <?php echo $braintree_checked; ?> >  
					<?php _e('Braintree', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>

					<input type="radio" name="uwa_payment_gateway" 
						value="uwa_payment_paystack"  <?php echo $paystack_checked; ?> >  
					<?php _e('Paystack', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>

					<input type="radio" name="uwa_payment_gateway" 
						value="uwa_payment_square"  <?php echo $square_checked; ?> >  
					<?php _e('Square', 'woo_ua');  /* check_here  */ ?> 
 
				</td>
			</tr>

			<tr class="uwa_heading">
				<th colspan="2"><?php _e('Configuration', 'woo_ua' ); ?>
				</th>
			</tr>
			<tr class="uwa_config_stripe">
				<th scope="row"></th>									 
				<td>
					<?php

						$wc_stripe_plugin_file = 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php';
						if ( file_exists( WP_PLUGIN_DIR . '/' . $wc_stripe_plugin_file ) && !class_exists( 'WC_Stripe' ) ) { 						
							
							echo sprintf( __( 'You just need to activate the <strong>%s</strong> to make it functional.', 'woo_ua' ), 
								'WooCommerce Stripe Gateway' );
							
						} 
						elseif( class_exists( 'WC_Stripe' )){

							$arr_stripe = get_option("woocommerce_stripe_settings");
							if(is_array($arr_stripe) && count($arr_stripe)){

								$is_enabled = $arr_stripe['enabled'];
								if($is_enabled == 'yes'){

									$is_testmode = $arr_stripe['testmode'];
									if($is_testmode == 'yes'){
										echo  __( "Stripe Test Mode Enable", "woo_ua" );

										$mode = "Test";
										$pk_test = $arr_stripe['test_publishable_key'];		
										$sk_test = $arr_stripe['test_secret_key'];
									}
									elseif ($is_testmode == 'no') {
										echo  __( "Stripe Live Mode Enable", "woo_ua" );
									}

									echo "<br>";									
									echo sprintf( __( "We are using WooCommerce Stripe Gateway for auto debit. Please %sgo here and configure.%s", "woo_ua" ), '<a target="_blank"  href="admin.php?page=wc-settings&tab=checkout&section=stripe">', '</a>' );	
												
								} /* end of if - is_enabled = yes */ 
								else {
									echo sprintf( __( "You need to enable the %sEnable Stripe%s and insert stripe detail to make it functional.", 
										"woo_ua" ), '<a target="_blank" 
										href="admin.php?page=wc-settings&tab=checkout&section=stripe">', '</a>' );

								} /* end of else */
									
							} /* end of if - arr_stripe */						
						} 
						else {	
							
							echo sprintf( __( "To use this feature you need to install  %sWooCommerce Stripe Gateway%s. It is free. Once you have installed it, Configure your Stripe Settings in it.", "woo_ua" ),'<a target="_blank" href="https://wordpress.org/plugins/woocommerce-gateway-stripe/">', '</a>' );
							
						} /* end of else */
						
					?>						
						
				</td>
			</tr>

			<tr class="uwa_config_braintree">				
				<th scope="row"></th>
				<td>
					<?php

						$wc_braintree_plugin_file = 'woo-payment-gateway/braintree-payments.php';

						if ( file_exists( WP_PLUGIN_DIR . '/' . $wc_braintree_plugin_file ) && !class_exists( 'WC_Braintree_Manager' ) ) { 	

							/* not activated */
							
							echo sprintf( __( 'You just need to activate the <strong>%s</strong> plugin to make it functional.', 'woo_ua' ), 
								'Payment Plugins Braintree For WooCommerce' );
						} 
						elseif( class_exists( 'WC_Braintree_Manager' )){

							/* not configured */
							
							$arr_braintree = get_option("woocommerce_braintree_api_settings");
							$braintree_cc = get_option("woocommerce_braintree_cc_settings");
						
							if(is_array($arr_braintree) && count($arr_braintree)){


								if(isset($arr_braintree['environment']) && 
									!empty($arr_braintree['environment'])){

									$environment = $arr_braintree['environment'];							

									if($environment == "sandbox"){
										echo  "<strong>".__( "Braintree Sandbox Mode Enable", "woo_ua" ).
										"</strong>";
									}
									else if($environment == "production"){
										echo  __( "Braintree Production Mode Enable", "woo_ua" );
									}

									echo "<br>";
									echo sprintf( __( "We are using Payment Plugins Braintree For WooCommerce for auto debit. Please %sgo here and configure.%s", "woo_ua" ), '<a target="_blank" href="admin.php?page=wc-settings&tab=checkout&section=braintree_api">', '</a>' );	

								} /* end of if - isset environment */							

							} /* end of if - is array */

							if(is_array($braintree_cc) && count($braintree_cc)){
								if($braintree_cc['enabled'] != "yes"){
									
									echo "<br>";
									echo sprintf( __( "You need to enable the %sEnable Braintree CC Gateway%s and configure to make it functional.", 
									"woo_ua" ), '<a target="_blank" 
									href="admin.php?page=wc-settings&tab=checkout&section=braintree_cc">', '</a>' );

								}
							}

						} 
						else {	
							
							/* not installed */
							echo sprintf( __( "To use this feature you need to install  %s Payment Plugins Braintree For WooCommerce%s. It is free. Once you have installed it, Configure your Braintree Settings in it.", 
								"woo_ua" ),'<a target="_blank" href="https://wordpress.org/plugins/woo-payment-gateway/">', '</a>' );
							
						} /* end of else */
						
					?>
				</td>
			</tr>		
			
			<tr class="uwa_config_paystack">				
				<th scope="row"></th>
				<td>
					<?php

						$wc_paystack_plugin_file = 'woo-paystack/woo-paystack.php';

						if ( file_exists( WP_PLUGIN_DIR . '/' . $wc_paystack_plugin_file ) && !class_exists( 'WC_Gateway_Paystack' ) ) { 	

							/* not activated */
							
							echo sprintf( __( 'You just need to activate the <strong>%s</strong> plugin to make it functional.', 'woo_ua' ), 
								'Paystack For WooCommerce' );
						} 
						elseif( class_exists( 'WC_Gateway_Paystack' )){

							/* not configured */
							
							$arr_paystack = get_option("woocommerce_paystack_settings");
						
							if(is_array($arr_paystack) && count($arr_paystack)){

								if(isset($arr_paystack['enabled']) && 
									!empty($arr_paystack['enabled'])){

									$gatway_enabled = $arr_paystack['enabled'];	
									if($gatway_enabled == "yes"){
										$environment = $arr_paystack['testmode'];							

										if($environment == "yes"){
											echo  "<strong>".__( "Paystack Sandbox Mode Enable", "woo_ua" ).
											"</strong>";
										}
										else if($environment == "no"){
											echo  __( "Paystack Production Mode Enable", "woo_ua" );
										}
									}
									else{
										echo sprintf( __( "You need to enable the %sEnable Paystack%s and configure to make it functional.", 
										"woo_ua" ), '<a target="_blank" 
										href="admin.php?page=wc-settings&tab=checkout&section=paystack">', '</a>' );
									} /* end of else */						
								}						

							} /* end of if - is array */

						} 
						else {	
							
							/* not installed */
							echo sprintf( __( "To use this feature you need to install  %s Paystack For WooCommerce%s. It is free. Once you have installed it, Configure your Paystack Settings in it.", 
								"woo_ua" ),'<a target="_blank" href="https://wordpress.org/plugins/woo-paystack/">', '</a>' );
							
						} /* end of else */
						
					?>
				</td>
			</tr>

			<tr class="uwa_config_square">				
				<th scope="row"></th>
				<td>
					<?php

						$wc_square_plugin_file = 'woocommerce-square/woocommerce-square.php';

						if ( file_exists( WP_PLUGIN_DIR . '/' . $wc_square_plugin_file ) && !function_exists( 'wc_square' ) ) { 	

							/* not activated */
							
							echo sprintf( __( 'You just need to activate the <strong>%s</strong> plugin to make it functional.', 'woo_ua' ), 
								'Square for WooCommerce' );
						} 
						elseif( function_exists( 'wc_square' )){
							
							// Get the Square settings
							$square_settings = get_option('wc_square_settings', array());
						
							if(is_array($square_settings) && count($square_settings)){
								// Check if the Square environment is set
								$is_sandbox = isset($square_settings['enable_sandbox']) && $square_settings['enable_sandbox'] === 'yes';
								if($is_sandbox == "yes"){
									echo  __( "Square Sandbox Mode Enable", "woo_ua" );
								}else{
									echo  __( "Square Live Mode Enable", "woo_ua" );
								}
							} else{
								echo "<br>";
									echo sprintf( __( "We are using Square for WooCommerce for auto debit. Please %sgo here and configure.%s", "woo_ua" ), '<a target="_blank" href="admin.php?page=wc-settings&tab=checkout&section=square">', '</a>' );	
							}/* end of if - is array */

						} 
						else {	
							
							/* not installed */
							echo sprintf( __( "To use this feature you need to install  %s Square for WooCommerce%s. It is free. Once you have installed it, Configure your Square Settings in it.", 
								"woo_ua" ),'<a target="_blank" href="https://wordpress.org/plugins/woocommerce-square/">', '</a>' );
							
						} /* end of else */
						
					?>
				</td>
			</tr>
				
			<tr class="uwa_heading">
				<th colspan="2"><?php _e('Display Settings for Credit Card', 'woo_ua' ); ?>
				</th>
			</tr>					
			<tr class="uwa_stripe_register_page">
				<th scope="row"></th>									 
				<td>
					<input <?php echo $register_chk; ?> value="1" 
						name="uwa_stripe_card_register_page" type="checkbox">
						<?php _e( 'Display Credit Card Details on default Wordpress Register form.', 'woo_ua' ); ?>
				</td>
			</tr>
			<tr class="uwa_stripe_register_page"> <!-- check_here -->
				<th scope="row"></th>							 
				<td class="uwaforminp">
					<input <?php echo $myaccount_chk; ?> value="1" 
						name="uwa_stripe_card_myaccount_page" type="checkbox">								
					<?php 
						$uwa_woo_acc_url = admin_url(
							'admin.php?page=wc-settings&tab=account');

						printf( __( 'Display Credit Card Details on WooCommerce My Account Page. Please enable <a href="%s">Registration on WooCommerce My Account</a> page for this setting to work.', 'woo_ua' ), 
							$uwa_woo_acc_url); 
					?>	
				</td>
			</tr> 					
			<tr>
				<th scope="row"></th>
				<td class="uwaforminp">
					<input <?php echo $my_bill_chk; ?> value="1" 
						name="uwa_wc_billing_myaccount_page" type="checkbox">								
					<?php _e( 'Capture Billing Address of Users while registering on WooCommerce My Account Page.', 'woo_ua' ); ?>
				</td>
			</tr> 

			<tr class="uwa_heading">
				<th colspan="2">
					<?php _e("Credit Card - Payment Hold & Automatic Debit", "woo_ua"); ?>
				</th>
			</tr>
			<tr>
				<th colspan="3"><?php  _e("Which option would you choose on the User's Credit Card:", "woo_ua"); ?>		
				</th>
			</tr>

			<?php /* ------- credit card payment hold starts ------- */ ?>

			<tr class="main_auto_hold_section">
			<th scope="row"></th>								 
				<td class="uwaforminp">
					<input type="radio" <?php echo ($uwa_payment_preference_for_auction == 'uwa_stripe_hold_payment') ?  "checked" : "" ;  ?> name="uwa_payment_preference_for_auction" id="uwa_stripe_hold_payment" value="uwa_stripe_hold_payment"> <?php _e("Hold an Amount on User's First Bid", "woo_ua");  ?><span style="margin-right:20px;"></span>
					
					<a href="" class="uwa_fields_tooltip" onclick="return false" style="top:-2px">
						<strong>?</strong>
						<span>
						<?php _e("This option will hold a specific amount on the bidder's credit card. Upon expiration, the hold placed on winner's credit card will be processed and transferred to auctioneer.", 'woo_ua');  ?>
						</span>
					</a>	

				</td>
			</tr>

			<tr class="auto_hold_section">
				<th scope="row"><?php _e('Credit Card Hold Options', 'woo_ua' ); ?></th>					 
				<td class="uwa_left_spacing test">
					<input type="radio" <?php echo ($uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_amount_charge') ?  "checked" : "" ;  ?> name="uwa_stripe_hold_charge_type" id="uwa_stripe_hold_fix_amount_charge" value="uwa_stripe_hold_fix_amount_charge"> <?php _e('Fix Amount', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>
					
					<input type="radio" <?php echo ($uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_percentage_charge') ?  "checked" : "" ;  ?> name="uwa_stripe_hold_charge_type" id="uwa_stripe_hold_fix_percentage_charge" value="uwa_stripe_hold_fix_percentage_charge"> <?php _e('Fix Percentage', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>
					
					<input type="radio" <?php echo ($uwa_stripe_hold_charge_type == 'uwa_stripe_hold_exact_bid_charge') ?  "checked" : "" ;  ?> name="uwa_stripe_hold_charge_type" id="uwa_stripe_hold_exact_bid_charge" value="uwa_stripe_hold_exact_bid_charge"> <?php _e('Exact Bid Amount', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>
					
				</td>
			</tr>

			<tr class="uwa_stripe_hold_fix_amount_charge_rates" >
				<th scope="row"></th>
				<td class="uwaforminp uwa_left_spacing">	
					<?php printf( __( "Enter Fix Amount (in %s)", 'woo_ua' ),get_woocommerce_currency_symbol()); ?> <input name="uwa_stripe_hold_fix_amount_charge_amt"  type="number" id="uwa_stripe_hold_fix_amount_charge_amt" value="<?php echo esc_attr( $uwa_stripe_hold_fix_amount_charge_amt );?>" size="14">
				</td>
			</tr>	
			
			<tr class="uwa_stripe_hold_percentage_amount_charge_rates" >
				<th scope="row"></th>
				<td class="uwaforminp uwa_left_spacing">	
					<?php _e('Enter Percentage', 'woo_ua');  ?> <input name="uwa_stripe_hold_percentage_amount_charge_amt"  type="number" id="uwa_stripe_hold_percentage_amount_charge_amt" value="<?php echo esc_attr( $uwa_stripe_hold_percentage_amount_charge_amt );?>" size="14">
				</td>
			</tr>	

			<tr class="auto_hold_section">			
				<th scope="row"><?php _e('Release or Debit', 'woo_ua' ); ?></th>
				<td class="uwaforminp">
					<input <?php echo $uwa_stripe_hold_debit_amount_chk; ?> value="1" 
						name="uwa_stripe_hold_debit_winner_amount" type="checkbox">								
					<?php 
						printf( __( "Do you want to debit the winner's held amount? By disabling this option,
							the winner's held amount will be released.", 'woo_ua' ));
							
					?>	<span style="margin-right:20px;"></span>

					<a href="" class="uwa_fields_tooltip" onclick="return false" style="top:-2px">
						<strong>?</strong>
						<span>
						<?php _e("By enabling this option, the winner's held amount is debited from his credit card and if you want to release the winner's held amount then just disable this option", 'woo_ua');  ?>
						</span>
					</a>					
				</td>			
			</tr>


			<?php /* ------- credit card payment hold ends ------- */ ?>

			<?php /* ------- automatic debit starts ------- */ ?>
			<tr>
				<th scope="row"></th>					 
				<td class="main_auto_debit_section">
					<input type="radio" <?php echo ($uwa_payment_preference_for_auction == 'uwa_stripe_auto_debit_payment') ?  "checked" : "" ;  ?> name="uwa_payment_preference_for_auction" id="uwa_stripe_auto_debit_payment" value="uwa_stripe_auto_debit_payment"> <?php _e('Automatically Debit the Winning Amount', 'woo_ua');  ?><span style="margin-right:20px;"></span>

					<a href="" class="uwa_fields_tooltip" onclick="return false" style="top:-2px">
						<strong>?</strong>
						<span>
						<?php _e("When an auction expires, then this option will attempt an automatic debit on winner's credit card and transfer the amount to auctioneer.", 'woo_ua');  ?>
						</span>
					</a>

				</td>
			</tr>	


			<tr class="auto_debit_section">
				<th scope="row"><?php _e('Auto Debit Options', 'woo_ua' ); ?></th>					 
				<td class="uwa_left_spacing">
			    	<input type="radio" <?php echo ($uwa_stripe_charge_type == 
			    		'uwa_stripe_charge_type_full') ?  "checked" : "" ;  ?> 
			    		name="uwa_stripe_charge_type" id="uwa_stripe_charge_type_full" 
			    		value="uwa_stripe_charge_type_full"> 
		     		<?php _e('Full Bid Amount', 'woo_ua');  ?>
					<span style="margin-right:20px;"></span>

					<input type="radio" <?php echo ($uwa_stripe_charge_type == 
						'uwa_stripe_charge_type_partially') ?  "checked" : "" ;  ?> 
						name="uwa_stripe_charge_type" id="uwa_stripe_charge_type_partially" value="uwa_stripe_charge_type_partially" > 
				 	<?php _e('Partial Bid Amount', 'woo_ua');  ?>				 
					<span style="margin-right:20px;"></span> 

					<input type="radio" <?php echo ($uwa_stripe_charge_type == 
						'uwa_stripe_charge_type_no') ?  "checked" : "" ;  ?> 
						name="uwa_stripe_charge_type" id="uwa_stripe_charge_type_no" 
						value="uwa_stripe_charge_type_no" > 
					<?php _e('No Auto Debit. Collect Payment on checkout page.', 'woo_ua');?>
				</td>
			</tr>
					
			<?php 
				$uwa_ctm_style = "";
				if($uwa_stripe_charge_type != 'uwa_stripe_charge_type_partially'){
					$uwa_ctm_style = 'style="display: none;"';
				}  
			?>
				
			<tr class="uwa_stripe_charge_type_partially_rates" <?php echo $uwa_ctm_style;?>>
				<th scope="row"></th>									 
				<td class="uwa_left_spacing">
				<?php _e("Partially bid amount type.", "woo_ua"); ?>

					<select name="uwa_stripe_charge_type_partially_type" 
						id="uwa_stripe_charge_type_partially_type">
						<option value="flatrate" <?php selected( 
							$uwa_stripe_charge_type_partially_type, "flatrate" ); ?> >
							<?php _e("Flat Rate", "woo_ua"); ?> </option>
						<option value="percentage" <?php selected( 
							$uwa_stripe_charge_type_partially_type, "percentage" ); ?>>
							<?php _e("Percentage", "woo_ua"); ?></option>
					</select>  
				</td>
			</tr>

			<tr class="uwa_stripe_charge_type_partially_rates "  <?php echo $uwa_ctm_style;?>>
				<th scope="row"></th>
				<td class="uwaforminp uwa_left_spacing">	
					<?php printf( __( "Enter Partially amount (in %s) or percentage.", 'woo_ua' ), get_woocommerce_currency_symbol()); ?>

					<input name="uwa_stripe_charge_type_partially_amt"  type="number" 
						id="uwa_stripe_charge_type_partially_amt" value="<?php echo 
						$uwa_stripe_charge_type_partially_amt;?>" size="14">

					<a href="" class="uwa_fields_tooltip" onclick="return false">
						<strong>?</strong>
						<span>
						<?php _e("If you choose 'Percentage' then the this entered value is treated as percentage of the total bid amount of the product, otherwise as fixed amount.", 'woo_ua');  ?>
						</span>
					</a>					
				
				</td>
			</tr>	
			
			<tr class="uwa_stripe_auto_debit_tax">
				<th scope="row"></th>
					<td class="uwaforminp">	
						<input <?php echo $uwa_stripe_tax_chk; ?> value="1" 
							name="uwa_stripe_tax_enable" type="checkbox">
						<?php _e('Enable automatic debit of tax amount.', 'woo_ua'); ?>

						<span style="margin-left:10px">
							<a href="" class="uwa_fields_tooltip" onclick="return false">
								<strong>  ?</strong>
								<span>
								<?php _e("The tax will be automatically debited for the auction winning amount, based on the enabled settings, which can apply to either the full bid or a partial bid." , 'woo_ua');  ?>
								</span>
							</a>
						</span>	
					</td>
			</tr> 


			<tr class="uwa_vendor_sub_details">			
				<th scope="row"></th>
				<td class="uwa_left_spacing">
					<span>For Vendor :</span>
					<?php
						$wc_stripe_plugin_file = 'wc-multivendor-marketplace/wc-multivendor-marketplace.php';
						if ( file_exists( WP_PLUGIN_DIR . '/' . $wc_stripe_plugin_file ) && !class_exists( 'WCFMmp' ) ) { 
					
							echo sprintf( __( 'You just need to activate the <strong>%s</strong> to make it functional.', 'woo_ua' ), 
								'WCFM - WooCommerce Multivendor Marketplace' );
					
						} 
						elseif( class_exists( 'WCFMmp' ) ){
							$wcfm_withdrawal_options = get_option( 
								'wcfm_withdrawal_options', array());

							$wcfm_withdrawal_options = get_option( 
								'wcfm_withdrawal_options', array());

							if(isset($wcfm_withdrawal_options['payment_methods'])) {

								$withdrawal_payment_methods = $wcfm_withdrawal_options['payment_methods'];
							}
							else{
								$withdrawal_payment_methods = array();
							}

							$pages = get_option("wcfm_page_options");
							$store_url_setting = get_the_permalink($pages['wc_frontend_manager_page_id']).'settings/';
							if(is_array($withdrawal_payment_methods) &&
								in_array("stripe_split", $withdrawal_payment_methods)){									
								$is_enabled = $arr_stripe['enabled'];
								$withdrawal_test_mode = $wcfm_withdrawal_options['test_mode'];
								if($withdrawal_test_mode == 'yes'){								
									if($withdrawal_test_mode == 'yes'){
										echo  __( "Stripe Split Pay Test Mode Enable", 
											"woo_ua" );

										$mode = "Test";
										$pk_test = $arr_stripe['test_publishable_key'];
										$sk_test = $arr_stripe['test_secret_key'];
									}
									elseif ($is_testmode == 'no') {
										echo  __( "Stripe Split Pay Live Mode Enable", 
											"woo_ua" );
												
									} /* end of if - testmode */

									echo "<br>";								
									echo sprintf( __( "We are used  %sWCFM - WooCommerce Multivendor Marketplace%s for auto debit. you can manage here.", "woo_ua" ), '<a target="_blank" href="'.$store_url_setting.'">', '</a>' );	
										
								} /* end of if - withdrawal_test_mode */ 								
							} /* end of if - stripe_split */ 					
							else {
									
								echo sprintf( __( "You need to enable the %sStripe Split Pay%s in WCFM Marketplace and insert stripe detail to make it functional.", "woo_ua" ), '<a target="_blank" href="'.$store_url_setting.'">', 
									'</a>' );
							}
					
						} /* end of elseif */
						else {
					
							echo sprintf( __( "You just need to install the %sWCFM - WooCommerce Multivendor Marketplace%s to make it functional.", "woo_ua" ), '<a target="_blank" href="https://wordpress.org/plugins/wc-multivendor-marketplace/">', '</a>' );
						}
					
					?>
					
				</td>
			</tr>

			<?php /* ------- automatic debit ends ------- */ ?>
				
			<?php 				
				if(in_array("uwa_buyers_premium_addon", $uwa_enabled_addons_list)) { ?>
				
				<br>
				<tr>			
					<th scope="row"><?php _e("Buyer's premium Autodebit", 'woo_ua' ); ?> </th>							 
					<td>
						<input <?php echo $uwa_stripe_buyers_chk; ?> value="1" 
						name="uwa_stripe_buyers_premium_enable" type="checkbox">
						<?php _e( "Enable automatic charge of buyer's premium.", 'woo_ua' ); ?>
					</td>
				</tr>
				<?php } 
			?>			 
	
				 
			<tr class="uwa_heading">
				<th colspan="2"><?php _e('Other Settings', 'woo_ua' ); ?></th>
			</tr>

			<tr>
				<th scope="row"></th>
				<td class="uwaforminp">	
					<input type="text" name="uwa_auto_stripe_text" class="regular-text" 
					value="<?php echo esc_attr( $uwa_auto_stripe_text );?>" id="uwa_auto_stripe_text">
					<?php _e( "This text will shown on check out page in Your order section.", 'woo_ua' ); ?>											
				</td>
			</tr>
				
			<tr>
				<th scope="row"></th>
				<td class="uwaforminp">	
					<input type="text" name="uwa_auto_stripe_payment_text" 
					class="regular-text" value="<?php echo esc_attr( $uwa_auto_stripe_payment_text );?>"
					id="uwa_auto_stripe_payment_text">					
				  	<?php _e( "This text will show on check out page before billing form.", 
				   		'woo_ua' ); ?>											
		   		</td>
			</tr>

			<tr class="uwa_gatway_payment_method_options">
				<th scope="row"></th>
				<td class="uwaforminp">	
					<textarea rows="5" name="uwa_payment_method_authorization_message_text" class="regular-text" id="uwa_payment_method_authorization_message_text"><?php echo $uwa_payment_method_authorization_message_text;?></textarea>					
				  	<?php _e( "This text will appear on the My Account > Payment Methods page for authorization.", 'woo_ua' ); ?>											
		   		</td>
			</tr>

			<tr class="uwa_gatway_payment_method_options">
				<th scope="row"></th>
				<td class="uwaforminp">	
					<input type="text" name="uwa_payment_method_authorization_btn_text" 
					class="regular-text" value="<?php echo esc_attr( $uwa_payment_method_authorization_btn_text );?>"
					id="uwa_payment_method_authorization_btn_text">					
					<?php _e( "Label for Authorize Payment Method Button.", 'woo_ua' ); ?>											
		   		</td>
			</tr>

			<tr class="uwa_gatway_payment_method_options">
				<th scope="row"></th>
				<td class="uwaforminp">	
					<input type="text" name="uwa_payment_method_unauthorization_btn_text" 
					class="regular-text" value="<?php echo esc_attr( $uwa_payment_method_unauthorization_btn_text );?>" 
					id="uwa_payment_method_unauthorization_btn_text">					
					<?php _e( "Label for Un-Authorize Payment Method Button.", 'woo_ua' ); ?>											
		   		</td>
			</tr>
				
			<tr class="submit">
				<th colspan="2">
					<input type="submit" id="uwa_auto_stripe_autodebit"  
						name="uwa_auto_stripe_autodebit" class="button-primary" 
						value="<?php _e('Save Changes','woo_ua');?>" />
				</th>
			</tr>

			</tbody>						
		</table>
	</form>
</div>

<script type="text/javascript">

	jQuery("document").ready(function($){


		/* payment gateway */

		var uwa_gateway = $("input:radio[name='uwa_payment_gateway']:checked").val();
	
			if (uwa_gateway == 'uwa_payment_stripe') {
				$('.uwa_config_stripe').css("display", "table-row");
				$('.uwa_config_braintree').css("display", "none");
				$('.uwa_config_paystack').css("display", "none");
				$('.uwa_config_square').css("display", "none");				
				$('.uwa_vendor_sub_details').css("display", "table-row");
				$('.main_auto_hold_section').css("display", "table-row");
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");				
				$(".uwa_stripe_register_page").show();
				$(".uwa_stripe_myaccount_page").show();
				$('.uwa_gatway_payment_method_options').hide();

			} else if (uwa_gateway == 'uwa_payment_braintree') {
				$('.uwa_config_braintree').css("display", "table-row");
				$('.uwa_config_stripe').css("display", "none");
				$('.uwa_config_paystack').css("display", "none");
				$('.uwa_config_square').css("display", "none");
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");	
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.main_auto_hold_section').css("display", "none");
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
				$(".uwa_stripe_register_page").show();
				$(".uwa_stripe_myaccount_page").show();
				$('.uwa_gatway_payment_method_options').hide();

			} else if (uwa_gateway == 'uwa_payment_paystack') {
				$('.uwa_config_paystack').css("display", "table-row");
				$('.uwa_config_stripe').css("display", "none");
				$('.uwa_config_braintree').css("display", "none");
				$('.uwa_config_square').css("display", "none");
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");
				$('.main_auto_hold_section').css("display", "none");
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");

				$("input[name='uwa_stripe_card_register_page']").prop('checked', false);
				$("input[name='uwa_stripe_card_myaccount_page']").prop('checked', false);
				$(".uwa_stripe_register_page").hide();
				$(".uwa_stripe_myaccount_page").hide();
				$('.uwa_gatway_payment_method_options').show();

			} else if (uwa_gateway == 'uwa_payment_square') {
				$('.uwa_config_square').css("display", "table-row");
				$('.uwa_config_stripe').css("display", "none");
				$('.uwa_config_braintree').css("display", "none");
				$('.uwa_config_paystack').css("display", "none");
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");
				$('.main_auto_hold_section').css("display", "table-row");
				$('.uwa_gatway_payment_method_options').hide();

				$("input[name='uwa_stripe_card_register_page']").prop('checked', false);
				$("input[name='uwa_stripe_card_myaccount_page']").prop('checked', false);
				$(".uwa_stripe_register_page").hide();
				$(".uwa_stripe_myaccount_page").hide();
			}

		/* credit card preference */

		var uwa_payment_type = $("input:radio[name='uwa_payment_preference_for_auction']:checked").val();		

			if(uwa_payment_type == 'uwa_stripe_hold_payment'){			
				$('.auto_debit_section').css("display", "none");
				$('.uwa_stripe_charge_type_partially_rates').css("display", "none");
				$('.uwa_stripe_auto_debit_tax').css("display", "none");				
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.auto_hold_section').css("display", "table-row");

					/* regarding stripe hold */			
					uwa_stripe_show_payment_hold_options();
			
			}else{	
				$('.auto_debit_section').css("display", "table-row");	
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");				
			
				$('.auto_hold_section').css("display", "none");
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
			}


		/* ------------ Change events ----------------- */

	  /* ------- 1. Payment gateway ------ */
	
		$('input:radio[name=uwa_payment_gateway]').change(function() {

			var uwa_payment_type = $("input:radio[name='uwa_payment_preference_for_auction']:checked").val();
			$("input:radio[name='uwa_payment_preference_for_auction']:checked").trigger('click');
		
			if (this.value == 'uwa_payment_stripe') {

				$('.uwa_config_stripe').css("display", "table-row");
				$('.uwa_config_braintree').css("display", "none");	
				$('.uwa_config_paystack').css("display", "none");		
				$('.uwa_config_square').css("display", "none");	
				$('.main_auto_hold_section').css("display", "table-row");	
				$('.uwa_vendor_sub_details').css("display", "table-row");	
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");
				$(".uwa_stripe_register_page").show();
				$(".uwa_stripe_myaccount_page").show();
				$('.uwa_gatway_payment_method_options').hide();				
			

				if(uwa_payment_type == 'uwa_stripe_hold_payment'){

					$('.auto_hold_section').css("display", "table-row");
					$('.uwa_vendor_sub_details').css("display", "none");	
					$('.uwa_stripe_auto_debit_tax').css("display", "none");

					/* uncheck the strip autodebit options */
					
					var uwa_stripe_charge_type = $("input:radio[name='uwa_stripe_charge_type']").is(":checked");
					if(uwa_stripe_charge_type == true){					
						$("input:radio[name='uwa_stripe_charge_type']").prop('checked', false);
					}		

					/* regarding stripe hold */			
					uwa_stripe_show_payment_hold_options();
				}
	
			} else if (this.value == 'uwa_payment_braintree') {
				$('.uwa_config_braintree').css("display", "table-row");
				$('.uwa_config_stripe').css("display", "none");
				$('.uwa_config_paystack').css("display", "none");
				$('.uwa_config_square').css("display", "none");
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.main_auto_hold_section').css("display", "none");
				$('.auto_hold_section').css("display", "none");
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");								
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
				$(".uwa_stripe_register_page").show();
				$(".uwa_stripe_myaccount_page").show();
				$('.uwa_gatway_payment_method_options').hide();

			} else if (this.value == 'uwa_payment_paystack') {	

				$('.uwa_config_paystack').css("display", "table-row");
				$('.uwa_config_stripe').css("display", "none");
				$('.uwa_config_braintree').css("display", "none");
				$('.uwa_config_square').css("display", "none");
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.main_auto_hold_section').css("display", "none");
				$('.auto_hold_section').css("display", "none");				
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");	
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
				$("input[name='uwa_stripe_card_register_page']").prop('checked', false);
				$("input[name='uwa_stripe_card_myaccount_page']").prop('checked', false);
				$(".uwa_stripe_register_page").hide();
				$(".uwa_stripe_myaccount_page").hide();
				$('.uwa_gatway_payment_method_options').show();

			} else if (this.value == 'uwa_payment_square') {	
				$('.uwa_config_square').css("display", "table-row");
				$('.uwa_config_stripe').css("display", "none");
				$('.uwa_config_braintree').css("display", "none");
				$('.uwa_config_paystack').css("display", "none");
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.uwa_gatway_payment_method_options').hide();

				$('.main_auto_hold_section').css("display", "table-row");	
				$('.uwa_stripe_auto_debit_tax').css("display", "table-row");
				$("input[name='uwa_stripe_card_register_page']").prop('checked', false);
				$("input[name='uwa_stripe_card_myaccount_page']").prop('checked', false);
				$(".uwa_stripe_register_page").hide();
				$(".uwa_stripe_myaccount_page").hide();

			}



			if (uwa_payment_type == 'uwa_stripe_auto_debit_payment') {				

				var autodebit_charge_type =  $("input[name='uwa_stripe_charge_type']:checked").val();
				if(autodebit_charge_type == "uwa_stripe_charge_type_partially"){
					$('.uwa_stripe_charge_type_partially_rates').css("display", "table-row");
				}

				/* uncheck the strip payment hold options */
				
				var uwa_stripe_hold_charge_type = $("input:radio[name='uwa_stripe_hold_charge_type']").is(":checked");

				if(uwa_stripe_hold_charge_type == true){					
					$("input:radio[name='uwa_stripe_hold_charge_type']").prop('checked', false);
				}

			}	

		});


		/* ------- 2. Credit card preference ------ */

		$('input:radio[name=uwa_payment_preference_for_auction]').change(function() {

			var uwa_payment_gateway = $("input:radio[name='uwa_payment_gateway']:checked").val();

			if (this.value == 'uwa_stripe_hold_payment') {

				$('.auto_hold_section').css("display", "table-row");
				$('.auto_debit_section').css("display", "none");	
				$('.uwa_stripe_auto_debit_tax').css("display", "none");
				$('.uwa_vendor_sub_details').css("display", "none");
				$('.uwa_stripe_charge_type_partially_rates').css("display", "none");

				/* regarding stripe hold */
				uwa_stripe_show_payment_hold_options();	
	

			}else{		

					if (uwa_payment_gateway == "uwa_payment_braintree") {
						$('.uwa_stripe_auto_debit_tax').css("display", "none");
						$('.uwa_vendor_sub_details').css("display", "none");

					} else {
						$('.uwa_stripe_auto_debit_tax').css("display", "table-row");
						$('.uwa_vendor_sub_details').css("display", "table-row");
					}

				$('.auto_debit_section').css("display", "table-row");
				$('.auto_hold_section').css("display", "none");
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
				

				var autodebit_charge_type =  $("input[name='uwa_stripe_charge_type']:checked").val();
				if (autodebit_charge_type == 'uwa_stripe_charge_type_partially') {
					$('.uwa_stripe_charge_type_partially_rates').css("display", 
						"table-row");
				}

			}

		});

			
		/* ------- 3. Stripe payment hold options ------ */

		$('input:radio[name=uwa_stripe_hold_charge_type]').change(function() {
			
			if (this.value == 'uwa_stripe_hold_fix_amount_charge') {
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "table-row");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
			}else if (this.value == 'uwa_stripe_hold_fix_percentage_charge') {
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "table-row");
			}else{
				$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
				$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
			}

		});

		/* ------- 4. Stripe autodebit charge types ------ */

		$('input:radio[name=uwa_stripe_charge_type]').change(function() {
			if (this.value == 'uwa_stripe_charge_type_partially') {
				$('.uwa_stripe_charge_type_partially_rates').css("display", 
					"table-row");
			}
			else {
				$('.uwa_stripe_charge_type_partially_rates').css("display", 
					"none");
			}
		});


		$("#uwa_auto_stripe_autodebit").click(function(){

			var uwa_payment_mode = $("input:radio[name='uwa_payment_preference_for_auction']:checked").val();

			var p_mode =  $("input[name='uwa_stripe_charge_type']:checked").val();
			var p_mode_hold =  $("input[name='uwa_stripe_hold_charge_type']:checked").val();

			if(uwa_payment_mode == "uwa_stripe_hold_payment"){

				if(p_mode_hold == 'uwa_stripe_hold_fix_percentage_charge'){
					var p__hold = $("#uwa_stripe_hold_percentage_amount_charge_amt").val();

					if(!p__hold){
						alert("Please enter values in Percentage field");
						return false;
					}

					if(isNaN(p__hold)){
						alert("Please enter only numeric values in Percentage field");
						return false;
					}

					if(p__hold <= 0){
						alert("Please enter more than 0 values in Percentage field");
						$("#uwa_stripe_hold_percentage_amount_charge_amt").val("");
						return false;
					}

					if(p__hold > 100){
						alert("In Percentage field amount More than 100 is not allowed");
						$("#uwa_stripe_hold_percentage_amount_charge_amt").val("");
						return false;
					}

				}

				if(p_mode_hold == 'uwa_stripe_hold_fix_amount_charge'){
					var p__hold = $("#uwa_stripe_hold_fix_amount_charge_amt").val();

					if(!p__hold){
						alert("Please enter values in Fix amount field");
						return false;
					}

					if(isNaN(p__hold)){
						alert("Please enter only numeric values in Fix amount field");
						return false;
					}

					if(p__hold <= 0){
						alert("Please enter more than 0 values in Fix amount field");
						$("#uwa_stripe_hold_fix_amount_charge_amt").val("");						
						return false;
					}
				}
			}

			if(uwa_payment_mode == "uwa_stripe_auto_debit_payment"){

				if(p_mode == 'uwa_stripe_charge_type_partially'){			
					var p_rate = $("#uwa_stripe_charge_type_partially_amt").val();
					var premium_type = $('#uwa_stripe_charge_type_partially_type').val();

					if(!p_rate){
						alert("Please Enter Partially amount or percentage.");
						return false;
					}

					if(isNaN(p_rate)){
						alert("Please enter only numeric values in Partially amount");
						return false;
					}

					if(p_rate <= 0){
						alert("Please enter values more than 0 in Partially amount");
						$("#uwa_stripe_charge_type_partially_amt").val("");
						return false;
					}
					
					if(premium_type == "percentage"){
						if(p_rate > 100){
							alert("In Partially rate More than 100 is not allowed");
							$("#uwa_stripe_charge_type_partially_amt").val("");
							return false;
						}
					}				
				}
			}

		});


		function uwa_stripe_show_payment_hold_options(){

			var uwa_stripe_hold_charge_type = $("input:radio[name='uwa_stripe_hold_charge_type']:checked").val();

				if(uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_amount_charge'){

					$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "table-row");
					$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");					
				
				}else if(uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_percentage_charge'){

					$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
					$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "table-row");

				}else{
				
					$('.uwa_stripe_hold_fix_amount_charge_rates').css("display", "none");
					$('.uwa_stripe_hold_percentage_amount_charge_rates').css("display", "none");
				
				}

		}

	});
</script>