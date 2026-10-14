<?php
/* WooCommerce Square payment gatway suport class file */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('UWA_Square')) {
    class UWA_Square
    {
        public $square_settings = null;
        public $square_enabled = false;
        public $is_sandbox = null;
        public $access_token = null;
        public $location_id = null;
        public $square_api_url = null;
        private static $instance;

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         *
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function __construct()
        {
            // Get the Square settings
            $this->square_settings = get_option('wc_square_settings', array());
            if (!empty($this->square_settings)) {
                $this->square_settings = array_map('sanitize_text_field', $this->square_settings);
                // Determine if we're in sandbox mode
                $this->is_sandbox = isset($this->square_settings['enable_sandbox']) && $this->square_settings['enable_sandbox'] === 'yes';
                $this->square_enabled = true;
                $this->access_token = $this->is_sandbox ? $this->square_settings['sandbox_token'] : $this->square_settings['access_token'];
                $this->location_id = $this->is_sandbox ? $this->square_settings['sandbox_location_id'] : $this->square_settings['production_location_id'];
                if ($this->is_sandbox) {
                    $this->square_api_url = 'https://connect.squareupsandbox.com';
                } else {
                    $this->square_api_url = 'https://connect.squareup.com';
                }
            }
            $payment_gateway = get_option("uwa_payment_gateway", "uwa_payment_stripe");

            if ($payment_gateway == "uwa_payment_square" && $this->square_enabled) {
                // Add action for auto debit payment
                add_action('ultimate_woocommerce_auction_autodabit_payment', [$this, 'uwa_square_addon_get_winner_info_cut_payment'], 10, 1);
            }
        }
        public function uwa_square_hold_payment($user_id, $product_id, $bid)
        {

            $message = "";
            $hold_amount = 0;
            $ajax_placebid = get_option('woo_ua_auctions_placebid_ajax_enable');

            if ($user_id > 0) {

                /* Customer ID from square */
                $wc_square_customer_id = get_user_meta($user_id, 'wc_square_customer_id', true);

                if (!empty($wc_square_customer_id)) {

                    $uwa_stripe_hold_charge_type = get_option('uwa_stripe_hold_charge_type');

                    if (!empty($uwa_stripe_hold_charge_type) && $uwa_stripe_hold_charge_type == 'uwa_stripe_hold_exact_bid_charge') {

                        /* HOLD Full BID AMOUNT */
                        $hold_amount = $bid;
                    } elseif (!empty($uwa_stripe_hold_charge_type) && $uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_amount_charge') {
                        /* HOLD FIX AMOUNT */
                        $hold_amount = (float) get_option('uwa_stripe_hold_fix_amount_charge_amt');
                    } else {
                        /* HOLD Percentage OF BID AMOUNT */
                        $uwa_stripe_hold_percentage_amount_charge_amt = (float) get_option('uwa_stripe_hold_percentage_amount_charge_amt');
                        $hold_amount = ($bid * $uwa_stripe_hold_percentage_amount_charge_amt) / 100;
                    }
                    $currency = get_woocommerce_currency();
                    $total_amt = wc_format_decimal($hold_amount, 2);
                    $total_amt_api = $total_amt * 100; // Convert to cents
                    try {
                        // Get user's saved payment tokens
                        $tokens = WC_Payment_Tokens::get_customer_tokens($user_id, 'square_credit_card');
                        $default_card_id = null;
                        foreach ($tokens as $token) {
                            if ($token->is_default()) {
                                $default_card_id = $token->get_token();
                                break;
                            }
                        }
                        if (empty($default_card_id)) {
                            throw new Exception('No default card found for user ID: ' . $user_id);
                        }
                        $response = wp_remote_post($this->square_api_url . '/v2/payments', [
                            'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                            'body' => json_encode([
                                'idempotency_key' => uniqid('uat_square_hold_id_', true),
                                'amount_money' => [
                                    'amount' => $total_amt_api,
                                    'currency' => $currency,
                                ],
                                'source_id' => $default_card_id,
                                'autocomplete' => false,
                                'acceptPartialAuthorization' => true,
                                'note' => 'Hold Bid Amount for Auction ID: ' . $product_id,
                                'customer_id' => $wc_square_customer_id,
                            ]),
                        ]);

                        $Jdata = json_decode(wp_remote_retrieve_body($response), true);

                        if ($Jdata['payment'] && $Jdata['payment']['status'] === 'APPROVED') {

                            //$message = "Payment HOLD success!";
                            $Jdata = $Jdata['payment'];
                            $stripe_hold = new Ultimate_WooCommerce_Auction_Pro_Addons_Hold_Stripe();
                            $status = $stripe_hold->uwa_payment_hold_add($user_id, $product_id, $total_amt, $Jdata, $bid);

                            $uwa_metakey = "uwa_stripe_payment_hold_chk_" . $product_id;
                            update_user_meta($user_id, $uwa_metakey, "yes_amount_held");

                            $uwa_metakey_amt = "uwa_stripe_payment_hold_amount_" . $product_id;
                            update_user_meta($user_id, $uwa_metakey_amt, $total_amt);


                            $uwa_hold_msg = sprintf(__("The amount %s is held successfully on your square's credit card.", "woo_ua"), wc_price($total_amt));

                            if ($ajax_placebid == "yes") {
                                $wc_msg['success'] = $uwa_hold_msg;
                                $wc_msg['status'] = true;
                                return $wc_msg;
                            } else {
                                wc_add_notice($uwa_hold_msg);
                            }
                        }
                    } catch (Exception $e) {

                        uwa_create_log("Payment hold square error: " . $e->getMessage() . " Auction ID = " . $product_id);
                        /* $message = __("Another problem occurred, maybe unrelated to square : ".$e->getMessage(), 
                                "woo_ua");*/
                        $message = __("Website requires to hold a payment on your credit card to place your bid. The payment was not held due to an unknown error and bid was not placed, please check with your site admin", "woo_ua");
                        if ($ajax_placebid == "yes") {
                            $wc_msg['error'] = $message;
                            $wc_msg['status'] = false;
                            return $wc_msg;
                        } else {
                            wc_add_notice($message, 'error');
                            return false;
                        }
                    }
                } else {
                    $message = __("Website requires to hold a payment on your credit card to place your bid but your square's credit card does not found", "woo_ua");
                    if ($ajax_placebid == "yes") {
                        $wc_msg['error'] = $message;
                        $wc_msg['status'] = false;
                        return $wc_msg;
                    } else {
                        wc_add_notice($message, 'error');
                        return false;
                    }
                }
            } /* end of if - user_id */
            /* in rare case */
            if ($ajax_placebid == "yes") {
                return true;
            } else {
                return $wc_msg['status'] = true;
            }
        }
        public function uwa_square_retrieve_fix_hold_payment($product_id = "", $winner_id = '')
        {

            $message = "";
            global $wpdb;

            if ($product_id > 0) {

                try {
                    $product = wc_get_product($product_id);

                    /* -- aelia -- */
                    $product_base_currency = $product->uwa_aelia_get_base_currency();
                    //$currency_symbol = $product->uwa_aelia_get_base_currency_symbol();

                    //$currency = get_woocommerce_currency();
                    $currency = $product_base_currency;
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
                            $time = current_time('mysql', 1);
                            $is_debit_held_winner_amount = get_option('uwa_stripe_hold_debit_winner_amount', "yes");

                            if ($is_debit_held_winner_amount == "yes") {


                                foreach ($refunresult as $key => $transaction) {

                                    $onetransaction_id = $transaction->transaction_id;
                                    $one_uid = $transaction->uid;
                                    $one_transaction_amount = $transaction->transaction_amount;

                                    /* retrive hold payment from square */
                                    $response = wp_remote_get($this->square_api_url . '/v2/payments/' . $onetransaction_id, [
                                        'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                                    ]);

                                    $Re_data = json_decode(wp_remote_retrieve_body($response), true);
                                    $Re_data_encode = json_encode($Re_data);
                                    if ($Re_data['payment'] && $Re_data['payment']['status'] === 'CANCELED') {
                                        $updated_timestamp = $Re_data['canceled_at'];
                                        $time = date("Y-m-d h:i:s", $updated_timestamp);

                                        $dataup = ['status' => 'already refunded', 'response_json' => $Re_data_encode, 'updated_at' => $time];
                                        $whereup = ['transaction_id' => $onetransaction_id];
                                        $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                    } else {

                                        if ($one_uid != $winner_id) {

                                            /* refund hold payment from square */
                                            $response = wp_remote_post($this->square_api_url . '/v2/payments/' . $onetransaction_id . '/cancel', [
                                                'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                                            ]);
                                            $Jdata = json_decode(wp_remote_retrieve_body($response), true);
                                            $Jdata_encode = json_encode($Jdata);
                                            if ($Jdata['payment'] && $Jdata['payment']['status'] === 'CANCELED') {
                                                $dataup = ['status' => 'refund', 'response_json' => $Jdata_encode, 'updated_at' => $time];
                                                $whereup = ['transaction_id' => $onetransaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            } else {
                                                $dataup = ['status' => 'fail to refund', 'response_json' => $Jdata_encode, 'updated_at' => $time];
                                                $whereup = ['transaction_id' => $onetransaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }
                                        } else {

                                            $transaction_id = $transaction->transaction_id;
                                            $transaction_amount = $transaction->transaction_amount;
                                            // if hold amount is fixed then first check for wining bid amount
                                            $uwa_stripe_hold_charge_type = get_option('uwa_stripe_hold_charge_type');

                                            if (
                                                !empty($uwa_stripe_hold_charge_type) &&
                                                $uwa_stripe_hold_charge_type == 'uwa_stripe_hold_fix_amount_charge'
                                            ) {

                                                $product = wc_get_product($product_id);
                                                if (is_object($product)) {
                                                    $auction_winning_bid = $product->get_uwa_auction_current_bid();
                                                    if ($transaction_amount > $auction_winning_bid) {
                                                        $transaction_amount = $auction_winning_bid;
                                                    }
                                                }
                                            }
                                            $total_amt_api = $transaction_amount * 100; // Convert to cents 
                                            $response = wp_remote_request($this->square_api_url . '/v2/payments/' . $onetransaction_id, [
                                                'method'    => 'PUT',
                                                'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                                                'body'      => json_encode([
                                                    'idempotency_key' => uniqid('uat_square_hold_', true),
                                                    'payment' => [
                                                        'total_money' => [
                                                            'amount'   => (int) $total_amt_api, // Amount in cents, e.g. $50.00
                                                            'currency' => $currency,
                                                        ],
                                                        'approved_money' => [
                                                            'amount'   => (int) $total_amt_api, // Amount in cents, e.g. $50.00
                                                            'currency' => $currency,
                                                        ],
                                                        'amount_money' => [
                                                            'amount'   => (int) $total_amt_api, // Amount in cents, e.g. $50.00
                                                            'currency' => $currency,
                                                        ]
                                                    ],
                                                ])
                                            ]);

                                            $response = wp_remote_post($this->square_api_url . '/v2/payments/' . $onetransaction_id . '/complete', [
                                                'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                                            ]);
                                            $Jdata = json_decode(wp_remote_retrieve_body($response), true);
                                            $Jdata_encode = json_encode($Jdata);

                                            if ($Jdata['payment'] && $Jdata['payment']['status'] === 'COMPLETED') {

                                                //update_post_meta($product_id, $this->_uwa_stripe_hold_total_amt,$transaction_amount);
                                                $dataup = [
                                                    'status' => 'capture',
                                                    'response_json' => $Jdata_encode,
                                                    'updated_at' => $time
                                                ];
                                                $whereup = ['transaction_id' => $transaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                                update_post_meta(
                                                    $product_id,
                                                    '_uwa_stripe_hold_total_amt',
                                                    $transaction_amount
                                                );
                                            } else {
                                                //update_post_meta($product_id, $this->_uwa_stripe_hold_total_amt,$transaction_amount);
                                                $dataup = ['status' => 'fail to capture', 'response_json' => $Jdata, 'updated_at' => $time];
                                                $whereup = ['transaction_id' => $transaction_id];
                                                $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                            }
                                        } /* end of else */
                                    } /* end of else - status - cancelled */
                                } /* end of foreach */
                            } /* end of if - debit winner amount = yes */



                            if ($is_debit_held_winner_amount == "no") {

                                foreach ($refunresult as $key => $transaction) {


                                    $onetransaction_id = $transaction->transaction_id;
                                    $one_uid = $transaction->uid;
                                    $one_transaction_amount = $transaction->transaction_amount;

                                    $response = wp_remote_get($this->square_api_url . '/v2/payments/' . $onetransaction_id, [
                                        'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                                    ]);

                                    $Re_data = json_decode(wp_remote_retrieve_body($response), true);
                                    $Re_data_encode = json_encode($Re_data);
                                    if ($Re_data['payment'] && $Re_data['payment']['status'] === 'CANCELED') {

                                        $updated_timestamp = $Re_data['canceled_at'];
                                        $time = date("Y-m-d h:i:s", $updated_timestamp);

                                        $dataup = ['status' => 'already refunded', 'response_json' => $Re_data_encode, 'updated_at' => $time];
                                        $whereup = ['transaction_id' => $onetransaction_id];
                                        $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                    } else {


                                        /* refund hold payment from square */
                                        $response = wp_remote_post($this->square_api_url . '/v2/payments/' . $onetransaction_id . '/cancel', [
                                            'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                                        ]);
                                        $Jdata = json_decode(wp_remote_retrieve_body($response), true);
                                        $Jdata_encode = json_encode($Jdata);
                                        if ($Jdata['payment'] && $Jdata['payment']['status'] === 'CANCELED') {
                                            $dataup = ['status' => 'refund', 'response_json' => $Jdata_encode, 'updated_at' => $time];
                                            $whereup = ['transaction_id' => $onetransaction_id];
                                            $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                        } else {
                                            $dataup = ['status' => 'fail to refund', 'response_json' => $Jdata_encode, 'updated_at' => $time];
                                            $whereup = ['transaction_id' => $onetransaction_id];
                                            $wpdb->update($wpdb->prefix . 'auction_hold_payment', $dataup, $whereup);
                                        }


                                        //}

                                    } /* end of else - status - cancelled */
                                } /* end of foreach */
                            } /* end of if - debit winner amount = no */
                        } /* end of if - count */
                    } /* end of if - empty */
                } catch (Exception $e) {
                    $message = $e->getMessage();
                }

                return $message;
            } /* end of if - product id */
        } /* end of function */
        public function uwa_square_addon_get_winner_info_cut_payment($auction_id)
        {
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

                    // Get user's saved payment tokens
                    $tokens = WC_Payment_Tokens::get_customer_tokens($cuserid, 'square_credit_card');
                    if (!empty($tokens)) {
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
                            $uwa_stripe_product_price = uwa_get_stripe_auto_debit_value($winning_bid_amt);
                            $w_product_price = uwa_get_stripe_auto_debit_value($winning_bid_amt);
                            $uwa_stripe_buyers_premium_enable = get_option('uwa_stripe_buyers_premium_enable');
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

                                $get_charged_for_winner = get_option("_uwa_w_s_charge_" . $product_id . "_" . $cuserid,false);

                                //if($get_charged_for_winner == false){
                                if ($get_charged_for_winner == "added") {
                                    // if payment is pending.. make payment

                                    if ($total_amt > 0) {
                                        $this->uwa_winner_make_square_payment(
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

        public function uwa_payment_chk_square($cuserid, $product_id, $total_amt)
        {
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

        public function uwa_winner_make_square_payment($cuserid, $total_amt, $product_id, $uwa_stripe_product_price, $uwa_stripe_buyer_price)
        {

            $status = $this->uwa_payment_chk_square($cuserid, $product_id, $total_amt);
            if ($status == 'inprograsss') {
                return;
            }

            global $wpdb;

            $product = wc_get_product($product_id);

            /* -- aelia -- */
            $product_base_currency = $product->uwa_aelia_get_base_currency();
            //$currency_symbol = $product->uwa_aelia_get_base_currency_symbol();

            //$currency = get_woocommerce_currency();
            $currency = $product_base_currency;
            $access_token = $this->access_token; // Get from plugin settings
            if (!empty($access_token)) {


                //e.g. winner_strip_charge_409_10
                $charged = get_option("_uwa_w_s_charge_" . $product_id . "_" . $cuserid, false);

                if ($charged == "added") {

                    try {
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
                        // Get user's saved payment tokens
                        $tokens = WC_Payment_Tokens::get_customer_tokens($cuserid, 'square_credit_card');
                        $default_card_id = null;
                        foreach ($tokens as $token) {
                            if ($token->is_default()) {
                                $default_card_id = $token->get_token();
                                break;
                            }
                        }
                        if (empty($default_card_id)) {
                            throw new Exception('No default card found for user ID: ' . $cuserid);
                        }
                        $wc_square_customer_id = get_user_meta($cuserid, 'wc_square_customer_id', true);
                        $total_amt_api = $total_amt * 100; // Convert to cents
                        $response = wp_remote_post($this->square_api_url . '/v2/payments', [
                            'headers' => ['Authorization' => 'Bearer ' . $this->access_token, 'Content-Type' => 'application/json', 'Square-Version' => '2025-04-16'],
                            'body' => json_encode([
                                'idempotency_key' => uniqid('uat_square_pay_id_', true),
                                'amount_money' => [
                                    'amount' => $total_amt_api,
                                    'currency' => $currency,
                                ],
                                'source_id' => $default_card_id,
                                'autocomplete' => true,
                                'note' => 'Auto Debit Payment for Auction ID: ' . $product_id,
                                'customer_id' => $wc_square_customer_id,
                            ]),
                        ]);

                        $body = json_decode(wp_remote_retrieve_body($response), true);
                        if ($body['payment'] && $body['payment']['status'] === 'COMPLETED') {
                            $dataup = ['status' => 'succeeded', 'response_json' => json_encode($body['payment'])];

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

                        $dataup = ['status' => 'fail', 'response_json' => json_encode($body)];

                        $whereup = ['pid' => $product_id];
                        $wpdb->update($wpdb->prefix . 'auction_direct_payment', $dataup, $whereup);
                        uwa_create_log("Admin Square Auto Debit  Charge Error: " . $e->getMessage() . " Auction ID=" . $product_id);
                    }
                } /* end of if - charged */



                /* -----------stripe payment------------ */
            } /* end of if - is_enabled = yes */
        }
        public function __destruct()
        {
            // Destructor code here
        }
    }
    // End class UWA_Square
    UWA_Square::get_instance();
}
