<?php

/* WooCommerce Paystack payment gatway suport class file */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('UWA_Paystack')) {

    class UWA_Paystack {

        public $paystack_settings = null;
        public $paystack_enabled = false;
        public $paystack_secret_key = null;
        public $paystack_public_key = null;
        private static $instance;

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
            
            $this->paystack_settings = get_option("woocommerce_paystack_settings");

            if ( isset( $this->paystack_settings['enabled'] ) && $this->paystack_settings['enabled'] == 'yes' ) {

                $this->paystack_enabled = true;

                if ( $this->paystack_settings['testmode'] == 'yes' ) {

                    $this->paystack_public_key = $this->paystack_settings['test_public_key'];
                    $this->paystack_secret_key = $this->paystack_settings['test_secret_key'];
                } else {

                    $this->paystack_public_key = $this->paystack_settings['live_public_key'];
                    $this->paystack_secret_key = $this->paystack_settings['live_secret_key'];
                }
            }

            $payment_gateway = get_option("uwa_payment_gateway", "uwa_payment_stripe");     

                if ($payment_gateway == "uwa_payment_paystack" && $this->paystack_enabled) {


                    // Add action for auto debit payment
                    add_action( 'ultimate_woocommerce_auction_autodabit_payment', [$this, 'uwa_paystack_addon_get_winner_info_cut_payment'], 10, 1 );

                    add_filter('ultimate_woocommerce_auction_before_place_bid_filter_for_ajax', [$this, 'uwa_paystack_front_user_bid_place_message'], 10, 2);
                    add_filter('ultimate_woocommerce_auction_before_place_bid_filter', [$this, 'uwa_paystack_front_user_bid_place_message'], 10, 2);

                    add_filter('woocommerce_after_account_payment_methods', [$this, 'uwa_add_paystack_authorize_links'], 101, 1);

                    add_action('template_redirect', [$this, 'uwa_handle_paystack_auth_actions']);
                    add_action('wp', [$this, 'handle_paystack_callback']);

                    add_action('wp_ajax_uat_paystack_add_payment_method', [$this, 'uat_paystack_add_payment_method_callback'] );

                }
          
        }

        public function uwa_paystack_addon_get_winner_info_cut_payment($auction_id) {
            global $wpdb, $woocommerce, $post, $WCFM;

            /* For one time auto dabit call */
            $auto_debit_one_time_payment = get_post_meta($auction_id, '_done_one_time_payment', true);

            $auto_debit_status = get_post_meta($auction_id, '_uwa_stripe_auto_debit_status', true);

            if ($auto_debit_one_time_payment != 'done_one_time_payment_1') {
                add_post_meta($auction_id, '_done_one_time_payment', 'done_one_time_payment_0');
            }

            if ($auto_debit_status != "paid") {
                if ($auto_debit_one_time_payment != "done_one_time_payment_1") {

                    $product = wc_get_product($auction_id);
                    $cuserid = $product->get_uwa_auction_current_bider();
                    $expired_auct = $product->get_uwa_auction_expired();

                    $paystack_auth_code = get_user_meta($cuserid, 'paystack_auth_code', true);
                    if (!empty($paystack_auth_code)) {
                        $has_methods   = true;
                    }
                    if ($has_methods) {

                        if (!empty($cuserid) &&  $expired_auct == '2') {
                            $product_id =  $product->get_id();
                            $winning_bid_amt = $product->get_uwa_auction_current_bid();
                            $get_charged_for_winner = get_option(
                                "_uwa_w_s_charge_" . $product_id . "_" . $cuserid,
                                false
                            );
                            if (empty($get_charged_for_winner)) {
                                add_option("_uwa_w_s_charge_" . $product_id . "_" . $cuserid, "added");
                            } else {
                                update_option("_uwa_w_s_charge_" . $product_id . "_" . $cuserid, "added");
                            }
                            $w_product_price = 0;
                            $uwa_stripe_product_price = 0;
                            $uwa_stripe_product_price = uwa_get_stripe_auto_debit_value(
                                $winning_bid_amt
                            );
                            $w_product_price = uwa_get_stripe_auto_debit_value($winning_bid_amt);

                            $uwa_stripe_buyers_premium_enable = get_option(
                                'uwa_stripe_buyers_premium_enable'
                            );
                            $uwa_stripe_buyer_price = 0;

                            if ($uwa_stripe_buyers_premium_enable == "yes") {
                                $uwa_stripe_buyer_price  = uwa_get_stripe_buyer_premium_value(
                                    $product_id,
                                    $winning_bid_amt
                                );
                                $w_product_price = $w_product_price + $uwa_stripe_buyer_price;
                            }

                            if (!empty($w_product_price) && $w_product_price > 0) {

                                $total_amt = wc_format_decimal($w_product_price, 2);

                                $get_charged_for_winner = get_option(
                                    "_uwa_w_s_charge_" . $product_id . "_" . $cuserid,
                                    false
                                );

                                //if($get_charged_for_winner == false){
                                if ($get_charged_for_winner == "added") {
                                    // if payment is pending.. make payment

                                    if ($total_amt > 0) {
                                        $this->uwa_winner_make_paystack_payment(
                                            $cuserid,
                                            $total_amt,
                                            $product_id,
                                            $uwa_stripe_product_price,
                                            $uwa_stripe_buyer_price
                                        );
                                    }
                                }
                            } /* end of if - product price > 0 */
                        } /* end of if - empty $cuserid */
                    } /* end of if - has methods */ else {
                        uwa_create_log("Auto Debit this user have no saved payment method : UserID#" . $cuserid);
                    }
                }
            } else {
                uwa_create_log("Auto Debit All ready Done For this Product : ProductID#" . $auction_id);
            } /* end of if - has Paid Status */
        }

        public function handle_paystack_callback() {
            if (isset($_GET['reference'])) {
                $reference = sanitize_text_field($_GET['reference']);
                $paystack_secret_key = $this->paystack_secret_key; // Get from plugin settings
                if ( empty( $paystack_secret_key ) ) {
                    return;
                }
                $response = wp_remote_get("https://api.paystack.co/transaction/verify/$reference", [
                    'headers' => ['Authorization' => 'Bearer ' . $paystack_secret_key],
                ]);
        
                $body = json_decode(wp_remote_retrieve_body($response), true);
                if ($body['status'] && $body['data']['status'] === 'success') {
                    $user_id = get_current_user_id();
                    // Save customer code to user meta
                    $customer_code = $body['data']['customer']['customer_code'];
                    update_user_meta($user_id, 'paystack_customer_code', $customer_code);
        
                    // Save the authorization code to user meta
                    $auth_code = $body['data']['authorization']['authorization_code'];
                    update_user_meta($user_id, 'paystack_auth_code', $auth_code);
                    
                    // Optional: Refund the 100 NGN if you don’t want to charge for verification
                    $transaction_id = $body['data']['id'];
                    wp_remote_post('https://api.paystack.co/refund', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $paystack_secret_key,
                            'Content-Type' => 'application/json',
                        ],
                        'body' => json_encode(['transaction' => $transaction_id]),
                    ]);
                    // Redirect to the payment methods page or show a success message
                    wp_redirect(home_url('/my-account/payment-methods/'));
                    exit;
                } else {
                    // Handle error
                    wc_add_notice(__('Paystack authorization failed. Please try again.', 'woo_ua'), 'error');
                    // Optionally redirect to the payment methods page
                    wp_redirect(home_url('/my-account/payment-methods/'));
                    exit;
                }
            }
        }

        public function uwa_handle_paystack_auth_actions() {
            if (!is_user_logged_in()) return;
        
            $user_id = get_current_user_id();
        
            // Handle Unauthorize
            if (isset($_GET['paystack_unauthorize']) && $_GET['paystack_unauthorize'] === '1') {
                delete_user_meta($user_id, 'paystack_auth_code');
                wc_add_notice(__('Your authorization has been removed.', 'woo_ua'), 'success');
                wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                exit;
            }
            // Handle Authorize Cancel
            if (isset($_GET['paystack_authorize_cancel']) && $_GET['paystack_authorize_cancel'] === '1') {
                wc_add_notice(__('Authorization was canceled.', 'woo_ua'), 'error');
                wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                exit;
            }
        
            // Handle Authorize (this should ideally trigger your $1 charge verification flow)
            if (isset($_GET['paystack_authorize']) && $_GET['paystack_authorize'] === '1') {
                // Check if the user has already authorized
                $auth_code = get_user_meta($user_id, 'paystack_auth_code', true);
                if (!empty($auth_code)) {
                    wc_add_notice(__('You have already authorized.', 'woo_ua'), 'notice');
                    wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                    exit;
                }
                // Get the Paystack authorization URL
                $authorization_url = $this->uwa_get_paystack_auth_url($user_id);
                if (empty($authorization_url)) {
                    wc_add_notice(__('Unable to create authorization.', 'woo_ua'), 'error');
                    wp_redirect(wc_get_account_endpoint_url('payment-methods'));
                    exit;
                }
                // Redirect to the Paystack authorization URL
                wp_redirect($authorization_url);
                exit;
            }
        }

        public function uat_paystack_add_payment_method_callback() {
            // CSRF protection
            if ( ! check_ajax_referer( 'UtAajax-nonce', 'ua_nonce', false ) ) {
                wp_send_json_error( 'Security check failed. Please refresh the page and try again.' );
                return;
            }
            // Check if the request is valid and the user is logged in
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( 'User not logged in' );
                return;
            }
            try {
                $user_id = get_current_user_id();
                $authorization_url = uwa_get_paystack_auth_url($user_id);
                if (empty($authorization_url)) {
                    wp_send_json_error( 'Unable to create authorization.' );
                    return;
                }
                // Send the authorization URL to the front-end
                wp_send_json_success( array( 'url' => $authorization_url ) );
            } catch ( Exception $e ) {
                wp_send_json_error( 'Error: ' . $e->getMessage() );
                return;
            }
        }

        public function uwa_get_paystack_auth_url($user_id) {
            $authorization_url = wc_get_account_endpoint_url('payment-methods');
            // Get the current user ID
            if( ! is_user_logged_in() ) {
                return $authorization_url;
            }
            $user_id = get_current_user_id();
            // Paystack API call to initialize transaction (replace with your logic)
            $paystack_secret_key = $this->paystack_secret_key; // Get from plugin settings
            if ( empty( $paystack_secret_key ) ) {
                return $authorization_url;
            }
            // Get the site URL
            $site_url = get_site_url();
            // This URL will be used to redirect the user if they cancel the authorization
            // For example, redirect to the payment methods page with a cancel flag
            $payment_method_url = wc_get_account_endpoint_url('payment-methods') . '?paystack_authorize_cancel=1';
            // Make the API call to Paystack
            $response = wp_remote_post('https://api.paystack.co/transaction/initialize', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $paystack_secret_key,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'email' => get_userdata($user_id)->user_email,
                    'amount' => 100, // Use 0 for card verification
                    'callback_url' => home_url('/my-account/payment-methods/'),
                    'metadata' => [	
                                        "cancel_action" => $payment_method_url,
                                        "referrer" => $site_url,
                                        "referrer_type" => "website",
                                        "referrer_id" => $user_id,
                                        "referrer_name" => get_userdata($user_id)->display_name,
                                        "referrer_email" => get_userdata($user_id)->user_email,
                                        "referrer_phone" => get_user_meta($user_id, 'billing_phone', true),
                                        
                                      ]
                ]),
            ]);
        
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if ($body['status'] && $body['data']['authorization_url']) {
                $authorization_url = $body['data']['authorization_url'];
            } 
            return $authorization_url;
        }        

        public function uwa_add_paystack_authorize_links($methods) {
            

         /*   if (is_plugin_active('woo-payment-gateway/braintree-payments.php')) {
                return;
            }*/

            // Check if payment methods page
            if (!is_wc_endpoint_url('payment-methods')) {
                return;
            }

            // Optional: Check if Paystack method exists among saved methods (if needed)
            // But you can safely show this section always if you want to handle authorization separately

            // Check if Paystack auth code is saved for this user
            $customer_id = get_current_user_id();
            $auth_code = get_user_meta($customer_id, 'paystack_auth_code', true);

            $uwa_payment_method_authorization_message_text = get_option('uwa_payment_method_authorization_message_text', "To ensure a secure bidding experience, we require a valid payment method before you can place bids. Your card will be verified with a small, refundable charge. This allows us to hold the bid amount when you participate in auctions.");

            $uwa_payment_method_authorization_btn_text = get_option('uwa_payment_method_authorization_btn_text', 
                "Authorize for Bidding");
	        $uwa_payment_method_unauthorization_btn_text = get_option('uwa_payment_method_unauthorization_btn_text', "Unauthorize");

            if (empty($uwa_payment_method_authorization_btn_text)) {
                $uwa_payment_method_authorization_btn_text = "Authorize for Bidding";
            }

            if (empty($uwa_payment_method_unauthorization_btn_text)) {
                $uwa_payment_method_unauthorization_btn_text = "Unauthorize";
            }            

            echo '<div class="uwa-authorization-section">';
            echo '<h3>' . __('Bidding Payment Methods', 'woo_ua') . '</h3>';
            echo '<div class="authorization-message">';
            echo '<strong>' . __('Authorize Your Payment Method to Place Bids', 'woo_ua') . '</strong>';
            echo '<p>' . $uwa_payment_method_authorization_message_text . '</p>';

            if (empty($auth_code)) {
                echo '<a href="' . esc_url(add_query_arg('paystack_authorize', '1')) . '" class="button">' . 
                    $uwa_payment_method_authorization_btn_text . '</a>';
            } else {
                echo '<a href="' . esc_url(add_query_arg('paystack_unauthorize', '1')) . '" class="button">' . 
                    $uwa_payment_method_unauthorization_btn_text . '</a>';
            }
            echo '</div>';
            echo '</div>';
        }

        public function uwa_paystack_front_user_bid_place_message( $product_data, $bid ) {

            // Check if the user is logged in
            if ( ! is_user_logged_in() ) {
                return;
            }
           
            $enabled_gateway = "PAYSTACK";
        
            // Get the current user ID
            $user_id = get_current_user_id();
        
            // Get the payment method URL
            $payment_method_url = wc_get_account_endpoint_url('payment-methods');
            // Check if the user has a Paystack authorization code
            $auth_code = get_user_meta($user_id, 'paystack_auth_code', true);
            $ajax_placebid = get_option('woo_ua_auctions_placebid_ajax_enable');
            
            if ( empty( $auth_code ) ) {

                $uwa_err_msg = sprintf(__('Please authorize your %s payment method to proceed with placing the bid. <a href="%s" class="button" target="_blank">Authorize here &rarr;</a>', 'woo_ua'), 
                    $enabled_gateway, $payment_method_url);

                if($ajax_placebid == "yes"){	
                    $ret_arr89['error'] = $uwa_err_msg;
                    $ret_arr89['status'] = false;
                    return $ret_arr89;
                }else{
                    wc_add_notice($uwa_err_msg, 'error');
                    return false;
                }
            }else{
                return true;
            }
        }

        public function uwa_payment_chk_paystack($cuserid, $product_id, $total_amt) {
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

        /**
        * Check Paystack payment status and update the database
        *
        * @param int $cuserid User ID
        * @param int $product_id Product ID
        * @param float $total_amt Total amount
        * @return string Transaction status
        */
       
        public function uwa_winner_make_paystack_payment( $cuserid, $total_amt, $product_id, 
            $uwa_stripe_product_price, $uwa_stripe_buyer_price ) {
       
           $status = $this->uwa_payment_chk_paystack($cuserid, $product_id, $total_amt);
           if ($status == 'inprograsss') {
               return;
           }
       
           global $woocommerce, $wpdb;
       
           $product = wc_get_product($product_id);
       
           /* -- aelia -- */
           $product_base_currency = $product->uwa_aelia_get_base_currency();
           //$currency_symbol = $product->uwa_aelia_get_base_currency_symbol();
       
           //$currency = get_woocommerce_currency();
           $currency = $product_base_currency;
           $paystack_secret_key = $this->paystack_secret_key; // Get from plugin settings
           if ( !empty($paystack_secret_key) ) {

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

                if (get_option('woocommerce_calc_taxes') === 'yes') {

                        $is_tax_debit_enable = get_option('uwa_stripe_tax_enable', "no");

                        if ($is_tax_debit_enable === 'yes') {

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
       
               $total_autodebit_amt = round($total_amt * 100);
       
               //e.g. winner_strip_charge_409_10
               $charged = get_option("_uwa_w_s_charge_" . $product_id . "_" . $cuserid,false);
       
               if ($charged == "added") {
       
                   try {
                       
                       $auth_code = get_user_meta($cuserid, 'paystack_auth_code', true);
                       $response = wp_remote_post('https://api.paystack.co/transaction/charge_authorization', [
                           'headers' => [
                               'Authorization' => 'Bearer ' . $paystack_secret_key,
                               'Content-Type' => 'application/json',
                           ],
                           'body' => json_encode([
                               'email' => get_userdata($cuserid)->user_email,
                               'amount' => $total_autodebit_amt,
                               'authorization_code' => $auth_code,
                           ]),
                       ]);
       
                       $body = json_decode(wp_remote_retrieve_body($response), true);
                       if ($body['status'] && $body['data']['status'] === 'success') {
                           $dataup = ['status' => 'succeeded', 'response_json' => json_encode($body['data'])];
       
                           $whereup = ['pid' => $product_id];
                           $wpdb->update($wpdb->prefix . 'auction_direct_payment', $dataup, $whereup);
       
                           update_option("_uwa_w_s_charge_" . $product_id . "_" . $cuserid, $total_amt);
                           if ($total_amt) {
                               $stripe_total_amt_metakey = "_uwa_stripe_auto_debit_total_amt";
                               update_post_meta($product_id, $stripe_total_amt_metakey, $total_amt);
                           }
                           if ($uwa_stripe_product_price) {
                               $stripe_metakey = "_uwa_stripe_auto_debit_amt";
                               update_post_meta(
                                   $product_id,
                                   $stripe_metakey,
                                   $uwa_stripe_product_price
                               );
                           }
                           if ($uwa_stripe_buyer_price) {
                               $stripe_buyer_metakey = "_uwa_stripe_auto_debit_bpm_amt";
                               update_post_meta(
                                   $product_id,
                                   $stripe_buyer_metakey,
                                   $uwa_stripe_buyer_price
                               );
                           }
       
                           $stripe_status_metakey = "_uwa_stripe_auto_debit_status";
                           update_post_meta($product_id, $stripe_status_metakey, "paid");
       
                           $uwa_stripe_auto_debit_date_metakey = "_uwa_stripe_auto_debit_date";
                           add_post_meta($product_id, $uwa_stripe_auto_debit_date_metakey, get_uwa_now_date());
                       }
                   } catch (Exception $e) {
                       // Handle error
       
                       $dataup = ['status' => 'fail', 'response_json' => json_encode($e)];
       
                       $whereup = ['pid' => $product_id];
                       $wpdb->update($wpdb->prefix . 'auction_direct_payment', $dataup, $whereup);
                       uwa_create_log("Admin Paystack Auto Debit  Charge Error: " . $e->getMessage() . " Auction ID=" . $product_id);
                   }
               } /* end of if - charged */
       
       
       
               /* -----------stripe payment------------ */
           } /* end of if - is_enabled = yes */
        } 

        public function __destruct() {
            // Destructor code here
        }

    } /* End class UWA_Paystack */

    UWA_Paystack::get_instance();
}