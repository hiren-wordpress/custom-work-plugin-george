<?php

/**
 * 
 * @package Ultimate WooCommerce Auction PRO ADDON fee to place bid
 * @author Nitesh Singh 
 * @since 1.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ultimate_WooCommerce_Auction_Pro_Addons_Fee_To_Placebid { 

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
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'init', array(  $this, 'uwa_bid_fee_pay_button'), 10);
	}	

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *			 
	 */
	public function init() {
		global $woocommerce;
		require_once ( UW_AUCTION_PRO_ADDONS .'fee_to_placebid/uwa-core-functions.php' );
	}


function uwa_bid_fee_pay_button(){
	
	$uat_fee_to_place_bid = get_option('uwa_bid_fee_enable', "no");
	if ($uat_fee_to_place_bid != "yes") {
		return;		
	}

	if(isset($_GET['uwa_payfee']) && $_GET['uwa_payfee'] == "feeorder"){

		/* CSRF: verify the nonce embedded in the pay-fee link */
		if ( ! isset( $_GET['uwa_fee_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['uwa_fee_nonce'] ) ), 'uwa_pay_fee' ) ) {
			return;
		}

		/* Only logged-in users can create a bidding-fee order */
		if ( ! is_user_logged_in() ) {
			return;
		}

		/* Validate the product */
		$product_id = isset( $_GET['uwa_pid'] ) ? absint( $_GET['uwa_pid'] ) : 0;
		if ( ! $product_id || ! wc_get_product( $product_id ) ) {
			return;
		}

			// check if old order pending for payment then show old
			$user_id = get_current_user_id();
			if ($user_id) {
				$field_options_to_place_bid_fee_type = get_option('uwa_bid_fee_type', 'onetime');
				if ($field_options_to_place_bid_fee_type == 'onetime') {
					$is_bid_enable = get_user_meta($user_id, 'able_to_bid', true);
					//$is_bid_enable = "no";
					if ($is_bid_enable == 'no') {
						$onetime_fee_order_checkout_url = get_user_meta($user_id, 'onetime_fee_order_checkout_url', true);
						$args = array(
							'order_create' => 1,
							'checkout_url' => $onetime_fee_order_checkout_url,
						);				

						wp_redirect($args['checkout_url']);
						exit;
						//wp_send_json($args);
						//die();						
					}
				} 
				else {
					
					$old_products_array = get_user_meta($user_id, 'onetime_fee_order_product_ids', true);
					$old_products_array = unserialize($old_products_array);
					if (!empty($old_products_array)) {
						$ids = array_column($old_products_array, 'id');
						if (!empty($ids)) {
							foreach ($ids as $k => $pid) {
								if ($product_id == $pid) {
									if ($old_products_array[$k]['able_to_bid'] == 1) {
										$args = array(
											'order_create' => 3,
											'msg' => 'your order is completed please refresh page',
										);

										wp_redirect();
										exit;

										//wp_send_json($args);
										//die();

									} else {
										$args = array(
											'order_create' => 1,
											'checkout_url' => $old_products_array[$k]['checkout_url'],
										);

										wp_redirect($args['checkout_url']);
										exit;

										//wp_send_json($args);
										//die();
									}
								}
							}
						}
					}
				}
			}


			/* create auto order */
			$user_id = get_current_user_id();
			$order = wc_create_order();
			$order_id = trim(str_replace('#', '', $order->get_order_number()));
			$order->set_customer_id($user_id);

			$product = wc_get_product($product_id);
			//$product_id   = $product->get_id();
			$product_title   = $product->get_title();
			$fees = get_option('uwa_bid_fee_amount', "1");
			$field_options_to_place_bid_fee_type = get_option('uwa_bid_fee_type', 'onetime');
			$order_text_for_fee = __("Bidding Fee for bidding on an auction product", 'woo_ua');
			/*if ($field_options_to_place_bid_fee_type != 'onetime') {*/
			if ($field_options_to_place_bid_fee_type == 'eachtime') {
				//$order_text_for_fee = sprintf(__("First time bidding fee for bidding on %s", 'woo_ua'), $product_title);
				$order_text_for_fee = __("First time bidding fee for bidding on", 'woo_ua')." ".$product_title;
			}
			$fee_data   = array(
				'name'       => $order_text_for_fee,
				'amount'     => wc_format_decimal($fees),
				'tax_status' => 'none',
				'tax_class'  => ''
			);

			$fee_items  = $order->get_fees(); // Get fees

			// Add fee
			if (empty($fee_items)) {
				$item = new WC_Order_Item_Fee(); // Get an empty instance object

				$item->set_name($fee_data['name']);
				$item->set_amount($fee_data['amount']);
				$item->set_tax_class($fee_data['tax_class']);
				$item->set_tax_status($fee_data['tax_status']);
				$item->set_total($fee_data['amount']);

				$order->add_item($item);
				$item->save(); // (optional) to be sure
			}
			$order->calculate_totals();

			/*update_post_meta($order_id, 'onetime_fee_order', '1');
			update_post_meta($order_id, 'onetime_fee_order_product_id', $product_id);*/

			$order->update_meta_data('onetime_fee_order', '1');
			$order->update_meta_data('onetime_fee_order_product_id', $product_id);
			$order->save();

			if ($field_options_to_place_bid_fee_type != 'onetime') {
				$product_array = array();
				$product_array['id'] = $product_id;
				$product_array['order_id'] = $order_id;
				$product_array['able_to_bid'] = 0;
				$product_array['checkout_url'] = $order->get_checkout_payment_url();
				$old_products_array = get_user_meta($user_id, 'onetime_fee_order_product_ids', true);
				$old_products_array = unserialize($old_products_array);
				if (empty($old_products_array)) {
					$old_products_array = array();
					$old_products_array[] = $product_array;
				} else {
					$old_products_array[] = $product_array;
				}
				$old_products_array = update_user_meta($user_id, 'onetime_fee_order_product_ids', serialize($old_products_array));
			} else {
				update_user_meta($user_id, 'onetime_fee_order_checkout_url', $order->get_checkout_payment_url());
				update_user_meta($user_id, 'able_to_bid', 'no');
			}
			$args = array(
				'order_create' => 1,
				'checkout_url' => $order->get_checkout_payment_url(),
			);

			wp_redirect($args['checkout_url']);
			exit;

			//wp_send_json($args);
			//die();			


	} /* end of if - isset-uwa_payfee */

} /* end of function */

} /* end of class */

Ultimate_WooCommerce_Auction_Pro_Addons_Fee_To_Placebid::get_instance();