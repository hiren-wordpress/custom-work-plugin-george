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

/*if (get_option('uwa_bid_fee_enable') == "yes") {

	add_filter('ultimate_woocommerce_auction_before_place_bid_filter', 
		array($this, 'uwa_place_bid_fee_alert'), 7, 3);

	add_action('wp_ajax_woocommerce_ajax_add_to_cart', 
		array($this, 'uwa_bid_fee_ajax_add_to_cart'));
	add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 
		array($this, 'uwa_bid_fee_ajax_add_to_cart'));

	add_action('woocommerce_order_status_completed', 
		array($this, 'uwa_bid_fee_order_status_completed'), 10, 1);

	add_filter('woocommerce_thankyou_order_received_text', 
		array($this, 'uwa_change_order_received_text'), 10, 2);
} */


if (get_option('uwa_bid_fee_enable') == "yes") {

	/* add_filter('ultimate_woocommerce_auction_before_place_bid_filter', 
		'uwa_place_bid_fee_alert', 7, 3); */
	
	add_filter('uwa_before_place_bid_fee_for_bidding_addon', 
		'uwa_place_bid_fee_alert', 10, 2);

	/* add_filter('uwa_before_place_bid_AJAX_fee_for_bidding', 
		'uwa_place_bid_fee_alert', 7, 3); */

	add_filter('uwa_before_place_bid_ajax_fee_for_bidding_addon', 
		'uwa_place_bid_fee_alert', 10, 2);


	add_action('wp_ajax_woocommerce_ajax_add_to_cart',
		'uwa_bid_fee_ajax_add_to_cart');

	add_action('woocommerce_order_status_completed', 
		'uwa_bid_fee_order_status_completed', 10, 1);

	add_filter('woocommerce_thankyou_order_received_text', 
		'uwa_change_order_received_text', 10, 2);	
} 

	
function uwa_place_bid_fee_alert($product_data, $bid){

    global $woocommerce, $product_data;
    global $product, $sitepress;

    $uwa_bid_fee_enable = get_option('uwa_bid_fee_enable');	
    $ajax_placebid = get_option('woo_ua_auctions_placebid_ajax_enable');
	if($uwa_bid_fee_enable == "yes"){

		$product_id =  $product_data->get_id();
		$customer_id = get_current_user_id();			
		$user_id = $customer_id;

		$uwa_payfee = "feeorder";
		$product_url = get_permalink($product_id);
		$payfee_btn_url = add_query_arg(array('uwa_pid' =>  $product_id,
			'uwa_payfee' => $uwa_payfee,
			'uwa_fee_nonce' => wp_create_nonce('uwa_pay_fee')), $product_url);

		$uwa_bid_fee_type = get_option("uwa_bid_fee_type");	
		if($uwa_bid_fee_type == "onetime"){

			$is_bid_enable = get_user_meta($user_id, 'able_to_bid', "no");						
			if ($is_bid_enable == 'no' || $is_bid_enable == "") {
				$onetime_fee_order_checkout_url = get_user_meta($user_id, 'onetime_fee_order_checkout_url', true);
				$fee_checkout_url = $onetime_fee_order_checkout_url;				

				$msgtxt = esc_html( get_option("uwa_bid_fee_message_text") );
				$buttontxt = esc_html( get_option("uwa_bid_fee_button_text") );	
				$feetxt = "<b>BID FEE : </b>";	
				$feetxt = "";	

				if($fee_checkout_url == ""){

					/* order not created so add class */

						$imgurl = UW_AUCTION_PRO_ASSETS_URL. "/images/ajax_loader.gif";	

						/*wc_add_notice(sprintf(__('You need to pay fee once before placing your first bid on the website. After successful payment, you can place any number of bids on any product on the website. <a href="" class="button onetimeFeePay" data-auction_id="%d" >Pay Now</a>

						<div style="height:90px;display:none!important;">Hello.. <img src="%s" alt="Loading..."/></div>', 'woo_ua'), 

						$product_id, $imgurl), 'error');*/				

						$uwa_fee_msg = sprintf($feetxt.$msgtxt.'<a href="%s" class="button onetimeFeePay" 
							data-auction_id="%d" >'.$buttontxt.'</a>', $payfee_btn_url, $product_id);

						if($ajax_placebid == "yes"){								
							$wc_msg['error'] = $uwa_fee_msg;
							$wc_msg['status'] = false;
							return $wc_msg;							
						}
						else{
							wc_add_notice($uwa_fee_msg, 'error');
							return false;
						}

				}
				else{
						$imgurl = UW_AUCTION_PRO_ASSETS_URL. "/images/ajax_loader.gif";

						/*wc_add_notice(sprintf(__('in else.... <a href="%s" class="button" target="_blank" data-auction_id="%d">Pay Now here &rarr;</a>

						<div class="blog_loader" id="loader_ajax_for_fee" style="display:none; height:80px; width:80px;"><img src="%s" alt="Loading..." /></div>', 'woo_ua'), 
							$fee_checkout_url, $product_id, $imgurl), 'error');*/

						/*$fee_checkout_url = $old_products_array[$k]['checkout_url'];*/
						$uwa_fee_msg = sprintf($feetxt.$msgtxt.'<a href="%s" class="button" data-auction_id="%d" >'.
							$buttontxt.'</a>', $fee_checkout_url, $product_id);

						if($ajax_placebid == "yes"){
							$wc_msg['error'] = $uwa_fee_msg;
							$wc_msg['status'] = false;
							return $wc_msg;
						}
						else{
							wc_add_notice($uwa_fee_msg, 'error');
							return false;
						}			
				}	

			}
			else{
				return true;
			}
		}
		elseif($uwa_bid_fee_type == "eachtime"){ 

			// check for able to bid or not 
			
			$fee_checkout_url = "";
			$msgtxt = esc_html( get_option("uwa_bid_fee_message_text") );
			$buttontxt = esc_html( get_option("uwa_bid_fee_button_text") );	
			$feetxt = "<b>BID FEE : </b>";
			$feetxt = "";	

			$old_products_array = get_user_meta($user_id, 'onetime_fee_order_product_ids', true);
			$old_products_array = unserialize($old_products_array);
			if (empty($old_products_array)) {	

				/* order not created so add class */

				$uwa_fee_msg = sprintf($feetxt.$msgtxt.'<a href="%s" class="button onetimeFeePay" 
					data-auction_id="%d" >'.$buttontxt.'</a>', $payfee_btn_url, $product_id);

				if($ajax_placebid == "yes"){								
					$wc_msg['error'] = $uwa_fee_msg;
					$wc_msg['status'] = false;
					return $wc_msg;							
				}
				else{
					wc_add_notice($uwa_fee_msg, 'error');
					return false;
				}
			} 
			else {
				$ids = array_column($old_products_array, 'id');				

				if (!empty($ids)) {

					if(in_array($product_id, $ids)){

						foreach ($ids as $k => $pid) {
							if ($product_id == $pid) {
								if ($old_products_array[$k]['able_to_bid'] == 0) {

									$fee_checkout_url = $old_products_array[$k]['checkout_url'];
									$uwa_fee_msg = sprintf($feetxt.$msgtxt.'<a href="%s" class="button" data-auction_id="%d" >'.$buttontxt.'</a>', $fee_checkout_url, $product_id);

									if($ajax_placebid == "yes"){
										$wc_msg['error'] = $uwa_fee_msg;
										$wc_msg['status'] = false;
										return $wc_msg;
									}
									else{
										wc_add_notice($uwa_fee_msg, 'error');
										return false;
									}								
									
								}
								else{
									return true;
								}							
							}						
						}

					}
					else{

							/* order not created so add class */

							$uwa_fee_msg = sprintf($feetxt.$msgtxt.'<a  href="%s" class="button onetimeFeePay" 
								data-auction_id="%d" >'.$buttontxt.'</a>', $payfee_btn_url, $product_id);

							if($ajax_placebid == "yes"){								
								$wc_msg['error'] = $uwa_fee_msg;
								$wc_msg['status'] = false;
								return $wc_msg;							
							}
							else{
								wc_add_notice($uwa_fee_msg, 'error');
								return false;
							}

					}

				}
			}

		}
		else{
			return true;
		}
			
	} /* end of if - fee enabled */

} /* end of function */


function uwa_bid_fee_ajax_add_to_cart(){

	/* CSRF protection */
	if ( ! check_ajax_referer( 'UtAajax-nonce', 'ua_nonce', false ) ) {
		wp_send_json( array( 'order_create' => 0, 'msg' => __( 'Security check failed. Please refresh the page and try again.', 'woo_ua' ) ) );
		die();
	}

	/* Only logged-in users can create a bidding-fee order */
	if ( ! is_user_logged_in() ) {
		wp_send_json( array( 'order_create' => 0, 'msg' => __( 'Please sign in to pay the bidding fee.', 'woo_ua' ) ) );
		die();
	}

	$uat_fee_to_place_bid = get_option('uwa_bid_fee_enable', "no");
	if ($uat_fee_to_place_bid != "yes") {
		return false;
		die();
	}

	/* Validate the product before creating any order */
	$posted_product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $posted_product_id || ! wc_get_product( $posted_product_id ) ) {
		wp_send_json( array( 'order_create' => 0, 'msg' => __( 'Invalid product.', 'woo_ua' ) ) );
		die();
	}
	$_POST['product_id'] = $posted_product_id;

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
				wp_send_json($args);
				die();
			}
		} 
		else {
			$product_id =  $_POST['product_id'];
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
								wp_send_json($args);
								die();
							} else {
								$args = array(
									'order_create' => 1,
									'checkout_url' => $old_products_array[$k]['checkout_url'],
								);
								wp_send_json($args);
								die();
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

	$product = wc_get_product($_POST['product_id']);
	$product_id   = $product->get_id();
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
	update_post_meta($order_id, 'onetime_fee_order_product_id', $_POST['product_id']);*/

	$order->update_meta_data('onetime_fee_order', '1');
	$order->update_meta_data('onetime_fee_order_product_id', $_POST['product_id']);
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
	wp_send_json($args);
	die();

} /* end of function */


function uwa_bid_fee_order_status_completed($order_id) {

	/* change bid fee meta(able_to_bid) when order is completed */

	$order = wc_get_order( $order_id );
	$is_bid_enable = $order->get_meta( 'onetime_fee_order');
	/*$is_bid_enable = get_post_meta($order_id, 'onetime_fee_order', true);*/	
	
	if ($is_bid_enable == '1') {
		/*$order = wc_get_order($order_id);*/
		$user_id = $order->get_user_id();
		$field_options_to_place_bid_fee_type = get_option('uwa_bid_fee_type', 'onetime');
		if ($field_options_to_place_bid_fee_type == 'onetime') {
			$is_bid_enable = update_user_meta($user_id, 'able_to_bid', 'yes');
		} 
		else {

			/*$onetime_fee_order_product_id = get_post_meta($order_id, 'onetime_fee_order_product_id', true);*/

			$onetime_fee_order_product_id = $order->get_meta('onetime_fee_order_product_id');			
			
			$old_products_array = get_user_meta($user_id, 'onetime_fee_order_product_ids', true);
			$old_products_array = unserialize($old_products_array);

			$ids = array_column($old_products_array, 'id');
			if (!empty($ids)) {
				foreach ($ids as $k => $pid) {
					if ($onetime_fee_order_product_id == $pid) {
						if ($old_products_array[$k]['able_to_bid'] == 0) {
							$old_products_array[$k]['able_to_bid'] = 1;
						}
					}
				}
				update_user_meta($user_id, 'onetime_fee_order_product_ids', serialize($old_products_array));
			}
		}
	}

} /* end of function */


function uwa_change_order_received_text( $str, $order ) {

	if(is_object($order)){
		$order_id = $order->get_id();

		/*$product_id = get_post_meta($order_id, 'onetime_fee_order_product_id', true);*/
		$product_id = $order->get_meta('onetime_fee_order_product_id');

		if($product_id > 0){

			$product_url = get_permalink($product_id);
			$product_title = get_the_title( $product_id );
			$auction_txt = __('Back to Product Page', 'woo_ua');
		    
			$new_str = $str. '<span style="float:right;"><strong><i><a href="'.$product_url.'" target="_blank">'.
				$auction_txt.'</a></i></strong></span>';

    		return $new_str;
		}
	}
	return $str;

} /* end of function */