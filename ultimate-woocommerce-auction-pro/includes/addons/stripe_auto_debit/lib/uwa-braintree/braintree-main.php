<?php

if ( ! class_exists( 'Braintree' ) ) {
	include_once ('autoload.php');
}

		global $wpdb;

		$arr_braintree = get_option("woocommerce_braintree_api_settings");
		$braintree_cc = get_option("woocommerce_braintree_cc_settings");
	
		if(is_array($arr_braintree) && count($arr_braintree)){


			if(isset($arr_braintree['environment']) && !empty($arr_braintree['environment'])){

				$environment = $arr_braintree['environment'];							

				if($environment == "sandbox"){

					if(isset($arr_braintree['sandbox_public_key']) && !empty($arr_braintree['sandbox_public_key'])){
						$publicKey = $arr_braintree['sandbox_public_key'];
					}
					if(isset($arr_braintree['sandbox_private_key']) && !empty($arr_braintree['sandbox_private_key'])){
						$privateKey = $arr_braintree['sandbox_private_key'];
					}
					if(isset($arr_braintree['sandbox_merchant_id']) && !empty($arr_braintree['sandbox_merchant_id'])){
						$merchantId = $arr_braintree['sandbox_merchant_id'];
					}
					
				}
				else if($environment == "production"){

					if(isset($arr_braintree['production_public_key']) && !empty($arr_braintree['production_public_key'])){
						$publicKey = $arr_braintree['production_public_key'];
					}
					if(isset($arr_braintree['production_private_key']) && !empty($arr_braintree['production_private_key'])){
						$privateKey = $arr_braintree['production_private_key'];
					}
					if(isset($arr_braintree['production_merchant_id']) && !empty($arr_braintree['production_merchant_id'])){
						$merchantId = $arr_braintree['production_merchant_id'];
					}					
				}
	
			   try {
					$braintree_gateway = new Braintree\Gateway([
		                'environment' => $environment,
		                'merchantId' => $merchantId,
		                'publicKey' =>  $publicKey,
		                'privateKey' =>  $privateKey
		            ]);
		            
		        } catch (\Throwable $th) {
		           return "";
		        }

				/* create token */     		       
			    
		        if (!empty($braintree_gateway)) {
		            $clientToken = $braintree_gateway->clientToken()->generate();
		        }
	
				/* create customer */

		       	if (!empty($braintree_gateway)) {

		            if(!empty($token)){

		            	//reg_username
						//reg_email

		                $email = "john@testnew.com";
		                $firstName = "john";
		                $lastName = "test";
   
		              	$email = isset($_POST['email']) ? $_POST['email'] : "";
               		   	$firstName = isset($_POST['billing_first_name']) ? $_POST['billing_first_name'] : "";
                		$lastName = isset($_POST['billing_last_name']) ? $_POST['billing_last_name'] : "";

		                // $uwa_braintree_k_id = $_REQUEST['uwa_braintree_k_id'];
		                $res_customer = $braintree_gateway->customer()->create([
		                    'email' => $email,
		                    'firstName' => $firstName,
		                    'lastName' => $lastName,
		                ]);

		                if ($res_customer->success) {
		                    $customerId = $res_customer->customer->id;
		                    $result_ = $braintree_gateway->paymentMethod()->create([
		                        'customerId' => $customerId,
		                        'paymentMethodNonce' => $token
		                	]);

		                	// save customer id in metadata
		                	/*update_user_meta( $customer_id, '_stripe_customer_id', $customerId);*/

							//$wpdb->prefix
						/*	update_user_meta($customer_id, $wpdb->prefix.'_braintree_customer_id', 
									$customerId);*/

							// Key - wp_braintree_sandbox_vault_id
							update_user_meta($customer_id, $wpdb->prefix.'braintree_sandbox_vault_id', 
									$customerId);

            			}

		   			}
			            
			    }else{
			            // display error 
			    }




			} /* end of if - isset environment */							

		} /* end of if - is array */


function uwa_payment_chk_braintree($cuserid, $product_id, $total_amt) {
	global $wpdb;
	$transaction_status = "";

	/* --- OLD CODE (commented for reference) ---
	$refunresult = $wpdb->get_results("SELECT status FROM " . $wpdb->prefix . 'auction_direct_payment' . "   WHERE pid='" . $product_id . "' ORDER BY `id` DESC ");
	--- END OLD CODE --- */
	$refunresult = $wpdb->get_results( $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}auction_direct_payment WHERE pid = %d ORDER BY `id` DESC", $product_id ) );
	if (!empty($refunresult)) {
		if (!empty($refunresult[0]->status)) {
			$transaction_status = $refunresult[0]->status;
			if ($transaction_status == 'succeeded') {

				$dataup = ['uid' => $cuserid, 'status' => 'inprograsss', 'transaction_amount' => $total_amt, 'main_amount' => $total_amt];

				$whereup = ['pid' => $product_id];
				$wpdb->update($wpdb->prefix . 'auction_direct_payment', $dataup, $whereup);
			}
		}
	} else {
		$data = array();
		$data['pid'] = $product_id;
		$data['uid'] = $cuserid;
		$data['debit_type'] =  'direct payment';
		$data['debit_amount_type'] = '0';
		$data['amount_or_percentage'] = '0';
		$data['transaction_amount'] = $total_amt;
		$data['main_amount'] = $total_amt;
		$data['status'] =  'inprograsss';
		$data['response_json'] = json_encode($data);

		$format = array('%s', '%d');
		$wpdb->insert($wpdb->prefix . 'auction_direct_payment', $data, $format);
		$transaction_status = 'add';
	}
	return $transaction_status;
}



function uwa_winner_make_braintree_payment($cuserid, $total_amt, $product_id, 
	$uwa_stripe_product_price, $uwa_stripe_buyer_price){


	/*$status = uwa_payment_chk_braintree($cuserid, $product_id, $total_amt);
	if ($status == 'inprograsss') {
		return;
	}*/

	global $woocommerce, $wpdb;

	$product = wc_get_product($product_id);	

	/* -- aelia -- */
	$product_base_currency = $product->uwa_aelia_get_base_currency();
	//$currency_symbol = $product->uwa_aelia_get_base_currency_symbol();

	//$currency = get_woocommerce_currency();
	$currency = $product_base_currency;
	
	$auction_title = get_the_title($product_id);

	$config   = braintree()->gateway()->config;
	$gateway = new Braintree\Gateway($config);
	

                /* add text to autodebit amount */
                if (get_option('woocommerce_calc_taxes') === 'yes') {

                    $is_tax_debit_enable = get_option('uwa_stripe_tax_enable', "no");

                    if ($is_tax_debit_enable === 'yes') {

                        $country = get_user_meta($cuserid, 'billing_country', true);
                        $state = get_user_meta($cuserid, 'billing_state', true);
                        $postcode = get_user_meta($cuserid, 'billing_postcode', true);
                        $city = get_user_meta($cuserid, 'billing_city', true);
                        $address = get_user_meta($cuserid, 'billing_address_1', true);
                        $address_2 = get_user_meta($cuserid, 'billing_address_2', true);
                        $tefw_exempt = get_user_meta($cuserid, 'tefw_exempt', true);

                        $state = !empty($state) ? $state : '';

                        // Define the address array
                        $address_array = array(
                            'country'   => $country,
                            'state'     => $state,
                            'postcode'  => $postcode,
                            'city'      => $city,
                            'address'   => $address,
                            'address_2' => $address_2,
                        );

                        $tax_rates = WC_Tax::find_rates($address_array);

                        // Calculate the taxes for the specified amount
                        
                        if ($tefw_exempt != 1) {
                        
                            $taxes = WC_Tax::calc_tax($total_amt, $tax_rates, false);
                            $total_tax = array_sum($taxes);

                            add_post_meta($product_id, '_uwa_stripe_auto_debit_before_tax_total_main', 
                                $total_amt);
                            add_post_meta($product_id, '_uwa_stripe_auto_debit_tax', $total_tax);
                            
                            if (!is_null($total_tax) && $total_tax != 0) {
                                $total_amt = $total_tax + $total_amt;
                            }
                            add_post_meta($product_id, '_uwa_stripe_auto_debit_before_tax_after', $total_amt);
                        }

                    }
                }


	$amount = $total_amt;

	//$customer_id = '157305925';

	//$customer_id = get_user_meta( $cuserid, 'wp_braintree_sandbox_vault_id', true);
	$customer_id = get_user_meta( $cuserid, $wpdb->prefix.'braintree_sandbox_vault_id', true);

	if(!empty($customer_id)){  
			
			$result = $gateway->transaction()->sale([
				'amount' => $amount,
				'customerId' => $customer_id,
				'orderId' => 'Auto Debit For Auction#' . $product_id,		
				'options' => [
				  'submitForSettlement' => True
				]
			  ]);

						update_option("_uwa_w_s_charge_".$product_id."_".$cuserid, $total_amt);

						if($total_amt){
							$stripe_total_amt_metakey = "_uwa_stripe_auto_debit_total_amt";
							update_post_meta($product_id, $stripe_total_amt_metakey, $total_amt);
						}	
						if($uwa_stripe_product_price){
							$stripe_metakey = "_uwa_stripe_auto_debit_amt";
							update_post_meta($product_id, $stripe_metakey, 
								$uwa_stripe_product_price);
						}								
						if($uwa_stripe_buyer_price){
							$stripe_buyer_metakey = "_uwa_stripe_auto_debit_bpm_amt";
							update_post_meta($product_id, $stripe_buyer_metakey, 
								$uwa_stripe_buyer_price);
						}	

						$stripe_status_metakey = "_uwa_stripe_auto_debit_status";
						update_post_meta($product_id, $stripe_status_metakey,"paid");
										
						$uwa_stripe_auto_debit_date_metakey = "_uwa_stripe_auto_debit_date";
						add_post_meta($product_id, $uwa_stripe_auto_debit_date_metakey,get_uwa_now_date());


						/* add new meta */
						add_post_meta($product_id, "autodebit_by_braintree", "yes");


	} /* end of if customer id */

	
}