<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ultimate_WooCommerce_Auction_Pro_Addons_Hold_Stripe {

	private static $instance;
    public $gateway = 'stripe';

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 *
	 */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
        $this->gateway = get_option( "uwa_payment_gateway", "uwa_payment_stripe");
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *			 
	 */
	public function init() {
		global $woocommerce, $wpdb;    
	}

    /* Check payment hold status in place bid */
    public function Payment_Hold_Status($user_id, $product_id, $bid) {

        $hold_status = $this->uwa_payment_hold_chk($user_id, $product_id);
        if ($this->gateway == 'uwa_payment_stripe') {
            if ($hold_status == false) {
                return $this->stripe_hold_payment($user_id, $product_id, $bid);
            }
        }
        if ($this->gateway == 'uwa_payment_square') {
            if ($hold_status == false) {
                if (class_exists('UWA_Square')) {
                    $uwa_square = UWA_Square::get_instance();
                    $uwa_wc_msg = $uwa_square->uwa_square_hold_payment($user_id, $product_id, $bid);
                }
                return $uwa_wc_msg;
            }
        }
        return true; // in rare case    
    }

   /* Check payment hold status in ajax place bid */ 
   public function uwa_Ajax_Payment_Hold_Status($user_id, $product_id, $bid) {

        $uwa_wc_msg = array('status' => true);
        $hold_status = $this->uwa_payment_hold_chk($user_id, $product_id);
        if ($this->gateway == 'uwa_payment_stripe') {
            if ($hold_status == false) {
                return $this->stripe_hold_payment($user_id, $product_id, $bid);
            }
        }
        if ($this->gateway == 'uwa_payment_square') {
            if ($hold_status == false) {
                if (class_exists('UWA_Square')) {
                    $uwa_square = UWA_Square::get_instance();
                    $uwa_wc_msg = $uwa_square->uwa_square_hold_payment($user_id, $product_id, $bid);
                }
                return $uwa_wc_msg;
            }
        }  

        return $uwa_wc_msg; 
   }


   public function uwa_payment_hold_chk($customer_id, $product_id) {
         global $wpdb;

         $hold_tbl_name = $wpdb->prefix . 'auction_hold_payment';

         /* --- OLD CODE (commented for reference) ---
         $refunresult = $wpdb->get_results("SELECT status FROM " . $hold_tbl_name . " WHERE uid ='" .
            $customer_id . "' and pid ='" . $product_id . "' ORDER BY id DESC");
         --- END OLD CODE --- */
         $refunresult = $wpdb->get_results( $wpdb->prepare( "SELECT status FROM {$hold_tbl_name} WHERE uid = %d and pid = %d ORDER BY id DESC", $customer_id, $product_id ) );
    
         if (!empty($refunresult)) {
            return true;
         }else{
            return false;
         }
   }

   public function stripe_hold_payment($user_id, $product_id, $bid) {

        global $wpdb;

        $message = "";
        $hold_amount = 0;
        $ajax_placebid = get_option('woo_ua_auctions_placebid_ajax_enable'); 

        if ($user_id > 0) {

            /* Customer ID from stripe */

            $secret_key = "";
            require_once (UW_AUCTION_PRO_ADDONS .'stripe_auto_debit/lib/uwa-stripe/stripe-main.php');

            $arr_stripe = get_option("woocommerce_stripe_settings");
            $is_enabled = $arr_stripe['enabled'];
                if($is_enabled == 'yes'){
                    $is_testmode = $arr_stripe['testmode'];
                    if($is_testmode == 'yes'){
                        //$publishable_key = $arr_stripe['test_publishable_key'];				
                        $secret_key = $arr_stripe['test_secret_key'];
                    }
                    elseif ($is_testmode == 'no') {				
                        //$publishable_key = $arr_stripe['publishable_key'];				
                        $secret_key = $arr_stripe['secret_key'];				
                    }	
                }

            $customer_id = get_user_meta($user_id, $wpdb->prefix . '_stripe_customer_id', true);            

            if (!empty($customer_id)) {

                    /* uwa_stripe_hold_charge_type = uwa_stripe_hold_fix_amount_charge,
                                                 uwa_stripe_hold_fix_percentage_charge,
                                                 uwa_stripe_hold_exact_bid_charge 
                    */ 

                    $uwa_stripe_hold_charge_type = get_option('uwa_stripe_hold_charge_type');

                    if(!empty($uwa_stripe_hold_charge_type) && $uwa_stripe_hold_charge_type == 'uwa_stripe_hold_exact_bid_charge'){

                        /* HOLD Full BID AMOUNT */ 
                        $hold_amount = $bid; 

                    }elseif(!empty($uwa_stripe_hold_charge_type) && $uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_amount_charge'){

                        /* HOLD FIX AMOUNT */ 
                        $hold_amount = (float) get_option('uwa_stripe_hold_fix_amount_charge_amt');

                        // add here..
                        /*if($bid >= $hold_amount){
                         $hold_amount = $hold_amount;
                        }
                        else{
                         $hold_amount = $bid;
                        }*/

                    }else{
                     
                        /* HOLD Percentage OF BID AMOUNT */ 
                        $uwa_stripe_hold_percentage_amount_charge_amt = (float) get_option('uwa_stripe_hold_percentage_amount_charge_amt');

                        $hold_amount = ($bid * $uwa_stripe_hold_percentage_amount_charge_amt)/100;
                    }


                    $currency = get_woocommerce_currency();                  
                    $total_amt = wc_format_decimal($hold_amount, 2);                  
                    $payment_method_url = wc_get_account_endpoint_url('payment-methods');

                    try {                        

                        \Stripe\Stripe::setApiKey($secret_key);

                        // add here
                        $obj_customer = \Stripe\Customer::retrieve($customer_id);
                        $default_pm_id = $obj_customer->invoice_settings->default_payment_method;

                        $apiData = [
                            'amount' => ($total_amt * 100),
                            'currency' => $currency,
                            'customer' => $customer_id,
                            /*'payment_method_types' => ['card'],*/
                            'payment_method_types' => array('card'),
                            'payment_method' => $default_pm_id,
                            'off_session' => true,
                            'confirm' => true,
                            'capture_method' => 'manual',
                            'description' => 'Held payment For Auction#' . $product_id,
                        ];

                        /*$apiData['payment_method'] = $resultsdata[0]->token;*/

                        $Jdata = \Stripe\PaymentIntent::create($apiData);

                        /*if ($Jdata['status'] == "requires_confirmation") {*/
                        if ($Jdata['status'] == "requires_capture") {                                            

                            //$message = "Payment HOLD success!";

                            $status = $this->uwa_payment_hold_add($user_id, $product_id, $total_amt, 
                              $Jdata, $bid);

                            $uwa_metakey = "uwa_stripe_payment_hold_chk_".$product_id;
                            update_user_meta($user_id, $uwa_metakey, "yes_amount_held");

                            $uwa_metakey_amt = "uwa_stripe_payment_hold_amount_".$product_id;
                            update_user_meta($user_id, $uwa_metakey_amt, $total_amt);
                                               
                            
                            $uwa_hold_msg = sprintf(__("The amount %s is held successfully on your stripe's credit card.", "woo_ua"), wc_price($total_amt));                            

                            if($ajax_placebid == "yes"){                                
                                $wc_msg['success'] = $uwa_hold_msg;
                                $wc_msg['status'] = true;                                
                                return $wc_msg;
                            }
                            else{
                                wc_add_notice($uwa_hold_msg);                                
                            }

                        }else if ($Jdata['status'] == "requires_source" || $Jdata['status'] == "requires_payment_method") {
                  
                            // $message = "Payment Hold fail";

                            /*$status = $this->uwa_payment_hold_add($user_id, $product_id, $total_amt, $Jdata, $bid);*/                           

                            $uwa_hold_msg = __("Your stripe's credit card is missing", "woo_ua");                           

                            if($ajax_placebid == "yes"){                                
                                $wc_msg['error'] = $uwa_hold_msg;
                                $wc_msg['status'] = false;
                                return $wc_msg;                         
                            }
                            else{
                                wc_add_notice($uwa_hold_msg, 'error');
                                return false;
                            }
                        
                        }
                        else{                           

                            //$message = "Payment HOLD Fail!";

                            /*$status = $this->uwa_payment_hold_add($user_id, $product_id, $total_amt, $Jdata, $bid);*/                            
                    
                        }
                

                    }
                    catch (\Stripe\Error\Base $e) {

                        uwa_create_log("Payment hold stripe error: " .$e->getMessage()." Auction ID = ".$product_id);
                        $message = $e->getMessage();

                        if($ajax_placebid == "yes"){                                
                            $wc_msg['error'] = $message;
                            $wc_msg['status'] = false;
                            return $wc_msg;                         
                        }
                        else{
                            wc_add_notice($message, 'error');
                            return false;
                        }

                    }
                    catch (\Stripe\Exception\CardException $e) {                       

                        uwa_create_log("Payment hold stripe error: " .$e->getMessage()." Auction ID = ".$product_id);
                        $message = __("There is some issue occurred with stripe's credit card : ".$e->getMessage(), 
                            "woo_ua"); 

                        if($e->getError()->code == "card_declined"){                           
                            $message = sprintf(__("Website requires to hold a payment on your credit card to place your bid. The payment was not held due to DECLINED CARD, please delete card and add new a valid credit card for your bid to get accepted. <a href='%s' class='button' target='_blank'>Submit here &rarr;</a>", "woo_ua"), $payment_method_url);
                        }

                        //other codes -- expired_card, incorrect_cvc

                        if($ajax_placebid == "yes"){                                
                            $wc_msg['error'] = $message;
                            $wc_msg['status'] = false;
                            return $wc_msg;                         
                        }
                        else{
                            wc_add_notice($message, 'error');
                            return false;
                        }

                    }
                    catch (\Stripe\Exception\ApiErrorException $e) {
                        
                        uwa_create_log("Payment hold stripe error: " .$e->getMessage()." Auction ID = ".$product_id);
                        $message = __("Website requires to hold a payment on your credit card to place your bid. The payment was not held due to an unknown API error from our payment processor and bid was not placed, please check with your site admin", "woo_ua"); 

                        // code = "resource_missing", wrong currency, amount less than 1

                        if($ajax_placebid == "yes"){                                
                            $wc_msg['error'] = $message;
                            $wc_msg['status'] = false;
                            return $wc_msg;                         
                        }
                        else{
                            wc_add_notice($message, 'error');
                            return false;
                        }

                    }
                    catch (Exception $e) {
                        
                        uwa_create_log("Payment hold stripe error: " .$e->getMessage()." Auction ID = ".$product_id);
                       /* $message = __("Another problem occurred, maybe unrelated to Stripe : ".$e->getMessage(), 
                            "woo_ua");*/ 
                            $message = __("Website requires to hold a payment on your credit card to place your bid. The payment was not held due to an unknown error and bid was not placed, please check with your site admin", "woo_ua"); 

                        if($ajax_placebid == "yes"){                                
                            $wc_msg['error'] = $message;
                            $wc_msg['status'] = false;
                            return $wc_msg;                         
                        }
                        else{
                            wc_add_notice($message, 'error');
                            return false;
                        }

                    }                  


            }else{                 
                        
                $message = __("Website requires to hold a payment on your credit card to place your bid but your stripe's credit card does not found", "woo_ua");

                if($ajax_placebid == "yes"){                                
                    $wc_msg['error'] = $message;
                    $wc_msg['status'] = false;
                    return $wc_msg;                         
                }
                else{
                    wc_add_notice($message, 'error');
                    return false;
                }
            }


        } /* end of if - user_id */



        /* in rare case */
        if($ajax_placebid == "yes"){ 
            return true; 
        }
        else{
            return $wc_msg['status'] = true;
        }


   } /* end of function */


    public function uwa_payment_hold_add($customer_id, $product_id, $total_amt, $Jdata, $bid) {
        global $wpdb;

        $uwa_stripe_hold_charge_type = get_option('uwa_stripe_hold_charge_type');
        $uwa_stripe_hold_percentage_amount_charge_amt = get_option('uwa_stripe_hold_percentage_amount_charge_amt');

        if($uwa_stripe_hold_charge_type!='uwa_stripe_hold_fix_percentage_charge'){
            $uwa_stripe_hold_percentage_amount_charge_amt = 0;
        }

        $data = array();
        $data['pid'] = $product_id;
        $data['uid'] = $customer_id; 
        $data['transaction_id'] = $Jdata['id']; 
        $data['hold_type'] =  'Hold Payment';
        $data['hold_amount_type'] = $uwa_stripe_hold_charge_type;
        $data['amount_or_percentage'] = $uwa_stripe_hold_percentage_amount_charge_amt;
        $data['transaction_amount'] = $total_amt;
        $data['main_amount'] = $bid;
        $data['status'] =  'hold';
        $data['response_json'] = json_encode($Jdata);
        $format = array('%s', '%d');
        $wpdb->insert($wpdb->prefix . 'auction_hold_payment', $data, $format);
        
    }


    /**
    *  Retrive payment with direct payment
    */
    public function retrive_debit_payment_check($product_id = "", $userid = "") {
        
        /*  return $this->stripe_retrieve_fix_hold_payment($product_id, $userid);  */

        if ($this->gateway == 'uwa_payment_stripe') {
            /* if ($product_id > 0 && $userid > 0) { */

            if ($product_id > 0) {    
                return $this->stripe_retrieve_fix_hold_payment($product_id, $userid); 
            }
        } else if ($this->gateway == 'uwa_payment_square') {            
            /* if ($product_id > 0 && $userid > 0) { */

            if ($product_id > 0) {        
                if (class_exists('UWA_Square')) {
                    $uwa_square = UWA_Square::get_instance();
                    return $uwa_square->uwa_square_retrieve_fix_hold_payment($product_id, $userid);
                }
            }
        }

    }

    /**
    * stripe Retrive fix hold payment 
    */
    public function stripe_retrieve_fix_hold_payment($product_id = "", $winner_id = ''){
        
        $message = "";
        global $wpdb;
        
        if ($product_id > 0) {

            try{
                
                $transaction_id = "";
				$transaction_amount = "";
				$userid = "";
                $hold_tbl_name = $wpdb->prefix . 'auction_hold_payment';

				/* --- OLD CODE (commented for reference) ---
				$refunresult = $wpdb->get_results("SELECT id, transaction_id, transaction_amount, uid FROM " .
                $hold_tbl_name . "   WHERE pid = '" . $product_id . "' AND  status = 'hold'");
				--- END OLD CODE --- */
				$refunresult = $wpdb->get_results( $wpdb->prepare( "SELECT id, transaction_id, transaction_amount, uid FROM {$hold_tbl_name} WHERE pid = %d AND status = 'hold'", $product_id ) );

                if (!empty($refunresult)) {

					if (count($refunresult) > 0) {
                        
                        /* Customer ID from stripe */
                        require_once (UW_AUCTION_PRO_ADDONS .'stripe_auto_debit/lib/uwa-stripe/stripe-main.php');
                        $arr_stripe = get_option("woocommerce_stripe_settings");
                        $is_enabled = $arr_stripe['enabled'];                     

                        if($is_enabled == 'yes'){
                            $is_testmode = $arr_stripe['testmode'];

                            if($is_testmode == 'yes'){
                                //$publishable_key = $arr_stripe['test_publishable_key'];				
                                $secret_key = $arr_stripe['test_secret_key'];
                            }
                            elseif ($is_testmode == 'no') {				
                                //$publishable_key = $arr_stripe['publishable_key'];				
                                $secret_key = $arr_stripe['secret_key'];				
                            }	
                        }

                        $stripe = new \Stripe\StripeClient($secret_key);

                        $time = current_time('mysql', 1);


                        $is_debit_held_winner_amount = get_option('uwa_stripe_hold_debit_winner_amount', "yes");

                        if ( $is_debit_held_winner_amount == "yes" ) {


                            foreach($refunresult as $key => $transaction){

                                $onetransaction_id = $transaction->transaction_id;
                                $one_uid = $transaction->uid;
                                $one_transaction_amount = $transaction->transaction_amount;

                                $Re_data = $stripe->paymentIntents->retrieve($onetransaction_id, []);
                                $Re_data_encode = json_encode($Re_data);


                                if ($Re_data['status'] == "canceled") {
                                    

                                    $updated_timestamp = $Re_data['canceled_at'];
                                    $time = date("Y-m-d h:i:s", $updated_timestamp);

                                    $dataup = ['status' => 'already refunded','response_json' => $Re_data_encode,'updated_at' => $time];
                                    $whereup = ['transaction_id' => $onetransaction_id];
                                    $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);

                                }
                                else{                                  

                                        if($one_uid != $winner_id) {

                                            //$this->stripe_refunds_payment($one_uid, $one_transaction_amount, $product_id, $onetransaction_id);
                                            $Jdata = $stripe->paymentIntents->cancel($onetransaction_id, []);
                                            $Jdata_encode = json_encode($Jdata);
                                            if ($Jdata['status'] == "canceled") {
                                                $dataup = ['status' => 'refund','response_json' => $Jdata_encode,'updated_at' => $time];
                                                $whereup = ['transaction_id' => $onetransaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }else{
                                                $dataup = ['status' => 'fail to refund','response_json' => $Jdata_encode,'updated_at' => $time];
                                                $whereup = ['transaction_id' => $onetransaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }

                                        
                                        }
                                        else{

                                            $transaction_id = $transaction->transaction_id;
                                            $transaction_amount = $transaction->transaction_amount;
                                            $userid = $transaction->uid;

                                            /*  $stripe1 = $stripe->paymentIntents->confirm(
                                              $transaction_id,
                                              ['payment_method' => 'pm_card_visa']
                                            );*/

                                            
                                            // if hold amount is fixed then first check for wining bid amount
                                            $uwa_stripe_hold_charge_type = get_option('uwa_stripe_hold_charge_type');

                                            if(!empty($uwa_stripe_hold_charge_type) && 
                                                $uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_amount_charge'){

                                                $product = wc_get_product( $product_id );
                                                if(is_object($product)){
                                                    $auction_winning_bid = $product->get_uwa_auction_current_bid();

                                                    if($transaction_amount > $auction_winning_bid){
                                                       $transaction_amount = $auction_winning_bid;
                                                    }

                                                }
                                            }

                                            $amount = ($transaction_amount * 100);

                                            $intent = \Stripe\PaymentIntent::retrieve($transaction_id);
                                            $Jdata = $intent->capture(['amount_to_capture' => $amount]);

                                            $Jdata_encode = json_encode($Jdata);
                                            if($Jdata['status'] == "succeeded") {

                                                //update_post_meta($product_id, $this->_uwa_stripe_hold_total_amt,$transaction_amount);
                                                $dataup = ['status' => 'capture','response_json'=>$Jdata_encode,
                                                    'updated_at' => $time];
                                                $whereup = ['transaction_id' => $transaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                                update_post_meta($product_id, '_uwa_stripe_hold_total_amt', 
                                                $transaction_amount);

                                            }else{
                                                //update_post_meta($product_id, $this->_uwa_stripe_hold_total_amt,$transaction_amount);
                                                $dataup = ['status' => 'fail to capture','response_json'=>$Jdata,'updated_at' => $time];
                                                $whereup = ['transaction_id' => $transaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }


                                        } /* end of else */ 

                                } /* end of else - status - cancelled */

                            } /* end of foreach */

                        } /* end of if - debit winner amount = yes */



                        if( $is_debit_held_winner_amount == "no" ){ 

                            foreach($refunresult as $key => $transaction){                            


                                $onetransaction_id = $transaction->transaction_id;
                                $one_uid = $transaction->uid;
                                $one_transaction_amount = $transaction->transaction_amount;                                                             

                                $Re_data = $stripe->paymentIntents->retrieve($onetransaction_id, []);
                                $Re_data_encode = json_encode($Re_data);


                                if ($Re_data['status'] == "canceled") {
                                    

                                    $updated_timestamp = $Re_data['canceled_at'];
                                    $time = date("Y-m-d h:i:s", $updated_timestamp);

                                    $dataup = ['status' => 'already refunded','response_json' => $Re_data_encode,'updated_at' => $time];
                                    $whereup = ['transaction_id' => $onetransaction_id];
                                    $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);

                                }
                                else{  

                                                               
                                        //if($one_uid != $winner_id) {

                                            //$this->stripe_refunds_payment($one_uid, $one_transaction_amount, $product_id, $onetransaction_id);
                                            $Jdata = $stripe->paymentIntents->cancel($onetransaction_id, []);
                                            $Jdata_encode = json_encode($Jdata);
                                            if ($Jdata['status'] == "canceled") {
                                                $dataup = ['status' => 'refund','response_json' => $Jdata_encode,'updated_at' => $time];
                                                $whereup = ['transaction_id' => $onetransaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }else{
                                                $dataup = ['status' => 'fail to refund','response_json' => $Jdata_encode,'updated_at' => $time];
                                                $whereup = ['transaction_id' => $onetransaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }

                                        
                                        //}

                                } /* end of else - status - cancelled */

                            } /* end of foreach */

                        } /* end of if - debit winner amount = no */

					} /* end of if - count */

				} /* end of if - empty */
           
            }
            catch (\Stripe\Exception\CardException $e) {
				$message = $e->getMessage();                
			}

            return $message;


        } /* end of if - product id */


    } /* end of function */


} /* end of class */

Ultimate_WooCommerce_Auction_Pro_Addons_Hold_Stripe::get_instance();
