<?php

/**
 * Extra Functions file
 *
 * @package Ultimate WooCommerce Auction PRO
 * @author Nitesh Singh 
 * @since 1.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get Auction List By User Id
 *
 */
 
function get_uwa_wp_timezone() {	
	$uwa_time_zone = wp_timezone();
	return $uwa_time_zone;
	
} 

function get_uwa_now_date() {	
	$uwa_now_date = wp_date('Y-m-d H:i:s',time(),get_uwa_wp_timezone());
	return $uwa_now_date;
	
}  
  
function get_uwa_auction_by_user( $user_id  ) {
	
	global $wpdb;
	$table = $wpdb->prefix."woo_ua_auction_log";	 
	$query   = $wpdb->prepare("SELECT auction_id,MAX(bid) FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);
	$results = $wpdb->get_results( $query ); 
	foreach ($results as &$var) {			
	    $query   = $wpdb->prepare( "SELECT bid FROM $table WHERE auction_id = %d AND userid = %d ORDER by CAST(bid AS decimal(50,5)) DESC, date ASC LIMIT 1", $var->auction_id, $user_id );
	    $result = $wpdb->get_var( $query );
	    $var->max_bid = $result;
	}

	return $results;
}
 

/**
 * Get Auction WatchList By User Id
 *
 */
function get_uwa_auction_watchlist_by_user( $user_id  ) {
	
	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	$results = get_user_meta( $user_id, "woo_ua_auction_watch"); 

	$new_watchlist = array();
	if(count($results) > 0){
		foreach($results as $key => $value) {
			$product = wc_get_product( $value );
			if (is_object($product) && method_exists( $product, 'get_type') && $product->get_type() == 'auction'){
				$product_status = $product->get_status();
				if ($product_status == 'publish') {
					$new_watchlist[] = $value;
				}
			}
			
		}

	}

	return $new_watchlist;
} 

/**
 * Get Url For checkout
 *
 */
function uwa_auction_get_checkout_url() {
	
	$checkout_page_id = wc_get_page_id('checkout');	
	$checkout_url     = '';
	
	if ( $checkout_page_id ) {

		if ( is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes' )			
			$checkout_url = str_replace( 'http:', 'https:', get_permalink( $checkout_page_id ) );
			
		else
			$checkout_url = get_permalink( $checkout_page_id );
	}
	return apply_filters( 'woocommerce_get_checkout_url', $checkout_url );
}

/**
 * Bid Placed Message
 *
 */
function uwa_bid_place_message( $product_id ) {
	
	global $woocommerce; 
	$product_data = wc_get_product($product_id);
	
		/* --aelia-- */
	$product_base_currency = $product_data->uwa_aelia_get_base_currency();
  	$args = array("currency" => $product_base_currency);

	$current_user = wp_get_current_user();
	$is_slient_auction = $product_data->get_uwa_auction_silent();
	
	if($is_slient_auction == "yes"){	
	
		$display_bid_value = wc_price($product_data->get_uwa_last_bid(), $args);
	}
	else{
	
		$display_bid_value = wc_price($product_data->get_uwa_current_bid(), $args);
	}
	

	/* if($current_user->ID == $product_data->get_uwa_auction_current_bider()){
		
		if(!$product_data->is_uwa_reserve_met()){			
			$message = sprintf(__('Your bid of %s has been placed successfully.', 'woo_ua'),wc_price($product_data ->get_uwa_current_bid())); 
		} 
		else {
			if($product_data->get_uwa_auction_max_bid()){
				$message = sprintf( 
					__('Your bid of %s has been placed successfully! Your max bid is %s.', 'woo_ua'),
					wc_price($product_data->get_uwa_current_bid()), 
					wc_price($product_data->get_uwa_auction_max_bid()));
				
			}
			else{				
				$message = sprintf(__('Your bid of %s has been placed successfully.', 'woo_ua'), wc_price($product_data->get_uwa_current_bid()));
			}
		}	
		
	} 
	else {
		
		if($product_data->get_uwa_auction_proxy() =="yes"){
			$message = sprintf(__( "Your bid has been placed successfully.", 'woo_ua'));
		}
		else {
			
			$message = sprintf( 
			__( "Your bid of %s has been placed successfully.", 'woo_ua'),
			wc_price($product_data ->get_uwa_current_bid()) );	
		}
			
	} */

	if($current_user->ID == $product_data->get_uwa_auction_current_bider()){
		
		if(!$product_data->is_uwa_reserve_met()){			
			$message = sprintf(
				__('Your bid of %s has been placed successfully.', 'woo_ua'),
				$display_bid_value);
		} 
		else {
			if($product_data->get_uwa_auction_max_bid()){
				$message = sprintf( 
					__('Your bid of %s has been placed successfully! Your max bid is %s.', 
						'woo_ua'),
					$display_bid_value, 
					wc_price($product_data->get_uwa_auction_max_bid(), $args));
				
			}
			else{				
				$message = sprintf(__('Your bid of %s has been placed successfully.', 
					'woo_ua'), $display_bid_value);
			}
		}	
		
	} 
	else {
		
		if($product_data->get_uwa_auction_proxy() =="yes"){
			$message = sprintf(__( "Your bid has been placed successfully.", 'woo_ua'));
		}
		else {
			
			$message = sprintf( 
			__( "Your bid of %s has been placed successfully.", 'woo_ua'),
				$display_bid_value);
		}
			
	} 


	wc_add_notice ( apply_filters('ultimate_woocommerce_auction_bid_place_message', $message, $product_id ) );
}


/**
 * Bid Placed Message for second bid layout
 *
 */
function uwa_bid_place_message_second_layout( $product_id ) {
	
	global $woocommerce; 
	$product_data = wc_get_product($product_id);
	
		/* --aelia-- */
	$product_base_currency = $product_data->uwa_aelia_get_base_currency();
  	$args = array("currency" => $product_base_currency);
  	$uwa_auction_click_button = $_REQUEST['uwa_auction_button'];

	$current_user = wp_get_current_user();
	$is_slient_auction = $product_data->get_uwa_auction_silent();
	
	if($is_slient_auction == "yes"){	
	
		$display_bid_value = wc_price($product_data->get_uwa_last_bid(), $args);
	}
	else{
	
		$display_bid_value = wc_price($product_data->get_uwa_current_bid(), $args);
	}
	

	/* if($current_user->ID == $product_data->get_uwa_auction_current_bider()){
		
		if(!$product_data->is_uwa_reserve_met()){			
			$message = sprintf(__('Your bid of %s has been placed successfully.', 'woo_ua'),wc_price($product_data ->get_uwa_current_bid())); 
		} 
		else {
			if($product_data->get_uwa_auction_max_bid()){
				$message = sprintf( 
					__('Your bid of %s has been placed successfully! Your max bid is %s.', 'woo_ua'),
					wc_price($product_data->get_uwa_current_bid()), 
					wc_price($product_data->get_uwa_auction_max_bid()));
				
			}
			else{				
				$message = sprintf(__('Your bid of %s has been placed successfully.', 'woo_ua'), wc_price($product_data->get_uwa_current_bid()));
			}
		}	
		
	} 
	else {
		
		if($product_data->get_uwa_auction_proxy() =="yes"){
			$message = sprintf(__( "Your bid has been placed successfully.", 'woo_ua'));
		}
		else {
			
			$message = sprintf( 
			__( "Your bid of %s has been placed successfully.", 'woo_ua'),
			wc_price($product_data ->get_uwa_current_bid()) );	
		}
			
	} */

	if($current_user->ID == $product_data->get_uwa_auction_current_bider()){
		
		if(!$product_data->is_uwa_reserve_met()){			
			$message = sprintf(
				__('Your bid of %s has been placed successfully.', 'woo_ua'),
				$display_bid_value);
		} 
		else {
			if($product_data->get_uwa_auction_max_bid()){
				$message = sprintf( 
					__('Your bid of %s has been placed successfully!', 
						'woo_ua'),
					$display_bid_value);
				
			}
			else{				
				$message = sprintf(__('Your bid of %s has been placed successfully.', 
					'woo_ua'), $display_bid_value);
			}
		}	
		
	} 
	else {
		
		if($product_data->get_uwa_auction_proxy() =="yes"){
			$message = sprintf(__( "Your bid has been placed successfully.", 'woo_ua'));
		}
		else {
			
			$message = sprintf( 
			__( "Your bid of %s has been placed successfully.", 'woo_ua'),
				$display_bid_value);
		}
			
	} 


	wc_add_notice ( apply_filters('ultimate_woocommerce_auction_bid_place_message', $message, $product_id ) );
		
}


if (!function_exists('wc_get_price_decimals')) {
	function wc_get_price_decimals() {
		return absint( get_option( 'wc_price_num_decimals', 2 ) );
	}
}

if (!function_exists('uwa_get_expired_auctions_id')) {

    /**
     * Return Expired auctions ids
     *     
     */
    function uwa_get_expired_auctions_id() {
		$args = array(
				'post_type' => 'product',
				'posts_per_page' => '-1',
				'show_expired_auctions' => TRUE,
				'tax_query' => array(array('taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction')),
				'meta_query' => array(
					array(
						'key' => 'woo_ua_auction_closed',
						//'compare' => 'NOT EXISTS',
						'compare' => 'IN',
						'value' => array('1','2','3','4'),
					)
				),
				'auction_arhive' => TRUE,
				'show_expired_auctions' => TRUE,
				'fields' => 'ids',
			);
    	$query = new WP_Query( $args );
    	$uwa_get_expired_auctions_id = $query->posts;

		return $uwa_get_expired_auctions_id;
	}
}

if (!function_exists( 'uwa_get_scheduled_auctions_id')) {

    /**
     * Return scheduled auctions ids
     *    
     */
    function uwa_get_scheduled_auctions_id() {
		$args = array(
				'post_type' => 'product',
				'posts_per_page' => '-1',
				'show_expired_auctions' => TRUE,
				'tax_query' => array(array('taxonomy' => 'product_type', 'field' => 'slug', 
					'terms' => 'auction')),
				'meta_query' => array(
					array(
						'key'     => 'woo_ua_auction_closed',
						'compare' => 'NOT EXISTS',
				),
				array(
						'key'     => 'woo_ua_auction_started',
						'value' => '0',
				)
				),
				'auction_arhive' => TRUE,
				'show_schedule_auctions' => TRUE,
				'fields' => 'ids',
			);
    	$query = new WP_Query( $args );
    	$uwa_get_scheduled_auctions_id = $query->posts;

		return $uwa_get_scheduled_auctions_id;
	}
}

function uwa_woocommerce_auctions_ordering() {
    	
        global $wp_query;

        if ( 1 === $wp_query->found_posts ) {
                return;
        }

        $orderby                 = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : get_option( 'uwa_default_orderby');
        $show_default_orderby    = 'menu_order' === get_option( 'uwa_default_orderby' );
        $catalog_orderby_options = array(
                                'menu_order'       => __( 'Default sorting', 'woocommerce' ),
                                'date'             => __( 'Sort by latest', 'woocommerce' ),
                                'price'            => __( 'Sort by buynow price: low to high', 'woo_ua' ),
                                'price-desc'       => __( 'Sort by buynow price: high to low', 'woo_ua' ),
                               /* 'uwa_bid_asc'          => __( 'Sort by current bid: Low to high', 'woo_ua' ),
                                'uwa_bid_desc'         => __( 'Sort by current bid: High to low', 'woo_ua' ),*/
                                'uwa_ending'      => __( 'Sort auction by Ending Soon', 'woo_ua' ),
                                'uwa_started'  => __( 'Sort auction by Just started', 'woo_ua' ),
                                'uwa_active' => __( 'Sort auction by Most Active', 'woo_ua' ),
        );

        if ( ! $show_default_orderby ) {
                unset( $catalog_orderby_options['menu_order'] );
        }
        
        wc_get_template( 'loop/orderby.php', array( 'catalog_orderby_options' => $catalog_orderby_options, 'orderby' => $orderby, 'show_default_orderby' => $show_default_orderby ) );
}

//user display name
//user display name
function uwa_user_display_name($user_id) {
		
	global $wpdb;	

	$uwa_simple_maskusername_enable = get_option('uwa_simple_maskusername_enable');
	$c_user_id = get_current_user_id();
	$user_name = "";	

	$bidder = get_userdata($user_id);	

	$name_to_display = $bidder->display_name;	
	if (empty($name_to_display)) {
		$name_to_display = $bidder->user_login;
	}	
		
	if (current_user_can('administrator') || current_user_can('manage_options') ||  
		current_user_can('manage_woocommerce') || $c_user_id == $user_id) {

		$user_name = $name_to_display;

	} elseif ($uwa_simple_maskusername_enable == "yes") {

		$uwa_mask_username_type = get_option( 'uwa_mask_username_type', 'full' );

		if ($uwa_mask_username_type == 'full') {

			$user_name = str_repeat("*", 7);

		} else {

			$no_user_name = get_userdata($user_id)->display_name;
			
			$user_strlen = strlen($no_user_name);
			$user_firstchar = strtolower($no_user_name[0]);
			$user_lastchar = strtolower($no_user_name[$user_strlen-1]);
			$user_middlechars = str_repeat("*", $user_strlen - 2); 
					
			$user_name = str_repeat("*", strlen($no_user_name)); 
			$user_name = $user_firstchar . $user_middlechars . $user_lastchar;
		}
		
			
			/* $no_user_name = get_userdata($user_id)->display_name;			
				
			$user_strlen = strlen($no_user_name);
			$user_firstchar = strtolower($no_user_name[0]);
			$user_lastchar = strtolower($no_user_name[$user_strlen-1]);
			$user_middlechars = str_repeat("*", $user_strlen - 2); */
					
			// $user_name = str_repeat("*", strlen($no_user_name)); /* prev */
			// $user_name = $user_firstchar. $user_middlechars . $user_lastchar;

			//$user_name = str_repeat("*", 7);
	
	} else {

		$user_name = $name_to_display;
	}

	return esc_html( $user_name );
}

function uwa_proxy_mask_user_display_name($user_id) {
	
	global $wpdb;
	
	$uwa_proxy_maskusername_enable = get_option('uwa_proxy_maskusername_enable');		
	$c_user_id = get_current_user_id();	
	$user_name = "";

	$bidder = get_userdata($user_id);	
	$name_to_display = $bidder->display_name;

	if (empty($name_to_display)) {
		$name_to_display = $bidder->user_login;
	}

	if (current_user_can('administrator') || current_user_can('manage_options') ||  
		current_user_can('manage_woocommerce') || $c_user_id == $user_id) {

		$user_name = $name_to_display;

	} elseif ($uwa_proxy_maskusername_enable == "yes") {
		
		$uwa_mask_username_type = get_option('uwa_proxy_mask_username_type', 'full');

		if ($uwa_mask_username_type == 'full') {

			$user_name = str_repeat("*", 7);

		} else {

			$no_user_name = get_userdata($user_id)->display_name;
			
			$user_strlen = strlen($no_user_name);
			$user_firstchar = strtolower($no_user_name[0]);
			$user_lastchar = strtolower($no_user_name[$user_strlen-1]);
			$user_middlechars = str_repeat("*", $user_strlen - 2); 
					
			$user_name = str_repeat("*", strlen($no_user_name)); 
			$user_name = $user_firstchar. $user_middlechars . $user_lastchar;
		}

			/* $no_user_name = get_userdata($user_id)->display_name;			
				
			$user_strlen = strlen($no_user_name);
			$user_firstchar = strtolower($no_user_name[0]);
			$user_lastchar = strtolower($no_user_name[$user_strlen-1]);
			$user_middlechars = str_repeat("*", $user_strlen - 2); */
					
			// $user_name = str_repeat("*", strlen($no_user_name)); /* prev */
			// $user_name = $user_firstchar. $user_middlechars . $user_lastchar;
			//$user_name = str_repeat("*", 7);
			
	} else {
		
		$user_name = $name_to_display;
	}

	return esc_html( $user_name );
}

function uwa_silent_mask_user_display_name($user_id) {
		
	global $wpdb;
	
	$uwa_silent_maskusername_enable = get_option('uwa_silent_maskusername_enable');		
	$c_user_id = get_current_user_id();
	$user_name = "";

	$bidder = get_userdata($user_id);

	$name_to_display = $bidder->display_name;
	if (empty($name_to_display)) {
		$name_to_display = $bidder->user_login;
	}	

	if (current_user_can('administrator') || current_user_can('manage_options') ||  
		current_user_can('manage_woocommerce') || $c_user_id == $user_id) {

		$user_name = $name_to_display;	

	} elseif ($uwa_silent_maskusername_enable == "yes") {		
		
		$uwa_mask_username_type = get_option( 'uwa_silent_mask_username_type', 'full' );

		if ($uwa_mask_username_type == 'full') {

			$user_name = str_repeat("*", 7);

		} else {

			$no_user_name = get_userdata($user_id)->display_name;			
			
			$user_strlen = strlen($no_user_name);
			$user_firstchar = strtolower($no_user_name[0]);
			$user_lastchar = strtolower($no_user_name[$user_strlen-1]);
			$user_middlechars = str_repeat("*", $user_strlen - 2); 
					
			$user_name = str_repeat("*", strlen($no_user_name)); 
			$user_name = $user_firstchar. $user_middlechars . $user_lastchar;

		}
	
	
			/* $no_user_name = get_userdata($user_id)->display_name;			
					
				$user_strlen = strlen($no_user_name);
				$user_firstchar = strtolower($no_user_name[0]);
				$user_lastchar = strtolower($no_user_name[$user_strlen-1]);
				$user_middlechars = str_repeat("*", $user_strlen - 2); */
						
				// $user_name = str_repeat("*", strlen($no_user_name)); /* prev */
				// $user_name = $user_firstchar. $user_middlechars . $user_lastchar;
				//$user_name = str_repeat("*", 7);		
	
	} else {

		$user_name = $name_to_display;
	}

	return esc_html( $user_name );
}

function uwa_proxy_mask_bid_amt($bid_value) {
		global $wpdb;
		$uwa_proxy_maskbid_enable = get_option('uwa_proxy_maskbid_enable');			
		if($uwa_proxy_maskbid_enable == "yes"){						
			$bid_value_amt = str_repeat("*", strlen($bid_value));
			
		} else {
			$bid_value_amt = wc_price($bid_value);
		}
		if (current_user_can('administrator') || current_user_can('manage_options') ) {		 
			$bid_value_amt = wc_price($bid_value);	
		}	

		return $bid_value_amt;
}
	
function uwa_silent_mask_bid_amt($bid_value) {
		global $wpdb;
		$uwa_silent_bid_enable = get_option('uwa_silent_bid_enable');			
		if($uwa_silent_bid_enable == "yes"){						
			$bid_value_amt = str_repeat("*", strlen($bid_value));
			
		} else {
			$bid_value_amt = wc_price($bid_value);
		}
		if (current_user_can('administrator') || current_user_can('manage_options') ) {		 
			$bid_value_amt = wc_price($bid_value);	
		}

		return $bid_value_amt;
}

/**
 * list bidders Ajax callback
 *
 * @see 'See More' link on 'Your Auctions/User Auctions' pages
 */
add_action('wp_ajax_uwa_see_more_bids_ajax', 'uwa_see_more_bids_ajax_callback');

function uwa_see_more_bids_ajax_callback() {

   global $wpdb;

	if ( !isset( $_POST['ua_nonce'] ) || !wp_verify_nonce(sanitize_text_field(
		$_POST['ua_nonce']), 'UtAajax-nonce' )) {

		echo json_encode(esc_html__('Nonce verification failed', 'woo_ua'));
		exit;
	}

   if (!isset($_POST['auction_id'])) {

   	echo json_encode(esc_html__('Auction id does not found', 'woo_ua'));
   	exit;
   }

   if (isset($_POST['auction_id'])) {

   	$pro_auction_id = absint($_POST['auction_id']);

   	if ($pro_auction_id > 0) {

			if (isset($_POST['show_rows']) && $_POST['show_rows'] == -1) {

				 /* $query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$_POST['auction_id'].' ORDER BY date DESC'; */

				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . 
					$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY date DESC', 
					$pro_auction_id ));

				$response['uwa_label_text'] = __('See less.', 'woo_ua');

			} else {

				/* $query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$_POST['auction_id'].' ORDER BY date DESC LIMIT 2'; */

				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . 
					$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY date DESC LIMIT 2', 
					$pro_auction_id ));

				$response['uwa_label_text'] = __('See more', 'woo_ua');
			}

			$row_bidders = '';

			if (!empty($results)) {

				foreach ($results as $result) {

						$userid	= $result->userid;
						$userdata = get_userdata( $userid );
						$bidder_name = $userdata->display_name;				

	               if ($bidder_name) {

							$bidder_name = "<a href='".esc_url( get_edit_user_link( $result->userid ) )."' target='_blank'>" .
								esc_html( $bidder_name ) . "</a>";

						} else {

							$bidder_name = "<a href='".esc_url( get_edit_user_link( $result->userid ) )."' target='_blank'>" .
								esc_html( $obj_user->user_login ) . "</a>";
	               }

						$maxbid_metakey = "woo_ua_auction_user_max_bid_" . $pro_auction_id;

						$max_bid =  wc_price(get_user_meta($userid, $maxbid_metakey, true));
						$auction_status = sanitize_text_field($_POST['auction_status']);
						$datetimeformat = get_option('date_format') . ' ' . get_option('time_format');

						$bid_amt = wc_price($result->bid);
						$bid_time = mysql2date($datetimeformat, $result->date);

						$row_bidders .= "<tr>";
						$row_bidders .= "<td>" . $bidder_name . " </td>";
						$row_bidders .= "<td>" . $bid_amt . "</td>";
						$row_bidders .= "<td>" . $max_bid . "</td>";
						$row_bidders .= "<td>" . $bid_time . "</td>";

						if ($auction_status == 'live') {

								$bid_ID = $result->id;
								$bid_user_ID = $result->userid;
								$bid_amount = $result->bid;

								$row_bidders .= "<td><a href='#' class='button uwa_force_choose_winner'
									data-bid_id=" . $bid_ID . "
									data-bid_user_id=" . $bid_user_ID . "
									data-bid_amount=" . $bid_amount . "
									data-auction_id=" . $pro_auction_id . " >" . __('Choose Winner', 'woo_ua') . "</a></td>";
						}

						$row_bidders .= "</tr>";

				} /* end of foreach */

				$row_bidders_final = $row_bidders;
				$response['bids_list'] = $row_bidders_final;

			} /* end of if */

			echo json_encode( $response );
			exit;

   	} else {

   		echo json_encode(esc_html__('Auction id is wrong', 'woo_ua'));
   		exit;
   	}

   } /* end of if - isset auction id */
  
}



function uwa_auction_ajax_add_bid_callback(){

	/* CSRF protection + require an authenticated user to place a bid. */
	if ( ! check_ajax_referer( 'UtAajax-nonce', 'ua_nonce', false ) ) {
		echo json_encode( array( 'status' => 0, 'msg_error' => __( 'Security check failed. Please refresh the page and try again.', 'woo_ua' ) ) );
		die();
	}

	if ( ! is_user_logged_in() ) {
		echo json_encode( array( 'status' => 0, 'msg_error' => __( 'Please sign in to place your bid or buy the product', 'woo_ua' ) ) );
		die();
	}

	if (empty($_REQUEST['bid_value']) || !is_numeric($_REQUEST['bid_value'])) {
		$response['status'] = 0;
	}
	
	global $wpdb,$woocommerce, $product, $post;

	$auction_id = absint($_POST['product_id']);	
	$bid = abs(round((float)str_replace(',', '.', $_REQUEST['bid_value']), wc_get_price_decimals()));
	$proxy_engine = false;		
	$history_bid_id = false;
	$product_data = wc_get_product( $auction_id );
	$response['status'] = 1;

	if (!is_user_logged_in()) {		
		$response['msg_error'] = __('Please sign in to place your bid or buy the product','woo_ua');
		$response['status'] = 0;	
	}

	if ($bid <= 0) {
		$response['msg_error'] = __('Please enter a value greater than 0!','woo_ua');
		$response['status'] = 0;		
	}
		
	/* Check if auction product expired */
	if ($product_data -> is_uwa_expired()) {		
		$response['msg_error'] = __('This auction  has expired', 'woo_ua' );
		$response['status'] = 0;		
	}

	/* Check if auction product Live or schedule */
	if (!$product_data -> is_uwa_live()) {		
		$response['msg_error'] = __('Sorry, the auction has not started yet', 'woo_ua' );
		$response['status'] = 0;	
	}
	
	/* Check Stock */
	if (!$product_data -> is_in_stock()) {
		$response['msg_error'] = __('You cannot place a bid because the product is out of stock.', 
			'woo_ua');
		$response['status'] = 0;		
	}
	
	if ('auction' === $product_type) {
		
		$current_user = wp_get_current_user();
		$auction_type = $product_data->get_uwa_auction_type();
		$auction_bid_value = $product_data->uwa_bid_value();
		$auction_bid_increment = $product_data->get_uwa_auction_bid_increment();
		$auction_current_bid = $product_data->get_uwa_auction_current_bid();
		$auction_current_bider = $product_data->get_uwa_auction_current_bider();
		$auction_high_bid = $product_data->get_uwa_auction_max_bid();
		$auction_high_current_bider = $product_data->get_uwa_auction_max_current_bider();
		$auction_reserved_price = $product_data->get_uwa_auction_reserved_price();
		$auction_bid_count = $product_data->get_uwa_auction_bid_count();
		
		if ($auction_type == 'normal') {
			if ( $product_data->uwa_bid_value() <= ($bid )) {
				    
					$curent_bid = $product_data -> get_uwa_current_bid();
					update_post_meta($product_id, 'woo_ua_auction_current_bid', $bid);
					update_post_meta($product_id, 'woo_ua_auction_current_bider', $current_user->ID);
					update_post_meta($product_id, 'woo_ua_auction_bid_count', absint($product_data->get_uwa_auction_bid_count() + 1));					
					$history_bid_id = true;					
					
			} else {
				
				$response['msg_error'] = __('Please enter a bid value  greater than the current bid', 
					'woo_ua');
				$response['status'] = 0;
			}
		}  
		
		/* do_action('ultimate_woocommerce_auction_place_bid', array( 'product_id' => $product_id ,'log_id' => $history_bid_id )); */
		
		
		if ($history_bid_id){
			
			$woo_ua_auction_log = $wpdb->prefix."woo_ua_auction_log";
			$sql = $wpdb->prepare(
				"INSERT INTO {$woo_ua_auction_log} (userid, auction_id, bid, proxy, date) VALUES (%d, %d, %f, %d, %s)",
				$current_user->ID, $product_id, $bid, $proxy, current_time('mysql')
			);

			if($wpdb->query($sql)) {
				$response['msg_success'] = __('Your Bid Placed Successfully', 'woo_ua');				 
			}
		}
		   
	}

	/* exit;	 */
   	echo json_encode( $response );
	die();

}
add_action('wp_ajax_uwa_auction_ajax_add_bid', 'uwa_auction_ajax_add_bid_callback');

	
function count_user_posts_by_type( $userid, $post_type = 'product' ) {
    global $wpdb;
    $where = get_posts_by_author_sql( $post_type, true, $userid );
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

    return apply_filters( 'get_usernumposts', $count, $userid );
}



/**
 * Anti Snipping
 *	
 */ 
function uwa_extend_auction_time_antisnipping($aucid){	

	global $product_data, $wpdb;	
	global $sitepress;

	if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 
		'get_default_language')) {
			
	    $aucid = icl_object_id($aucid, 'product', false, 
    	$sitepress->get_default_language());

	}		
	
    $ext_tm = get_option('uwa_auto_extend_time');
	$ext_tm = (double)$ext_tm;
    
    $ext_tmm = get_option('uwa_auto_extend_time_m');
    $ext_tmm = (double)$ext_tmm;

	$ext_tmm_s = get_option('uwa_auto_extend_time_s');
    $ext_tmm_s = (double)$ext_tmm_s;

	if($ext_tm > 0 || $ext_tmm > 0 || $ext_tmm_s > 0 ){	/* any of them must be > 0 */
	
			$ext_whn = get_option('uwa_auto_extend_when');
			$ext_whn = (double)$ext_whn;
				
			$ext_whnm = get_option('uwa_auto_extend_when_m');
   		 	$ext_whnm = (double)$ext_whnm;
								   
			$ext_whnm_s = get_option('uwa_auto_extend_when_s');
   		 	$ext_whnm_s = (double)$ext_whnm_s;
			/* $eng = get_post_meta($aucid, 'wdm_bidding_engine', true); */
			
			if(($ext_whn > 0 || $ext_whnm > 0 || $ext_whnm_s > 0) && ($ext_tm > 0 || $ext_tmm > 0 || $ext_tmm_s > 0) ){
				$le = get_post_meta($aucid, 'woo_ua_auction_end_date', true);	

				if(strtotime(get_uwa_now_date()) >= (strtotime($le) - (($ext_whn*3600) + ($ext_whnm*60)  + ($ext_whnm_s)))){
					
					$uwa_aviod_snipping_type_main  = get_option('uwa_aviod_snipping_type_main');
					if($uwa_aviod_snipping_type_main == "sniping_type_extend_checked"){
						$dt = strtotime($le)+(($ext_tm*3600) + ($ext_tmm*60) + ($ext_tmm_s));
					}else if($uwa_aviod_snipping_type_main == "sniping_type_reset_checked"){
						$dt = strtotime(get_uwa_now_date())+(($ext_tm*3600) + ($ext_tmm*60) + ($ext_tmm_s));
					}else{
						$dt = strtotime($le)+(($ext_tm*3600) + ($ext_tmm*60) + ($ext_tmm_s));
					}

					//$new_end_date = wp_date('Y-m-d H:i:s', $dt,get_uwa_wp_timezone());
					update_post_meta($aucid, 'woo_ua_from_anti_snipping', 'yes');
					$type_of_antisnipping  = get_option('uwa_aviod_snipping_type');
					
						$new_date = date("Y-m-d H:i:s", $dt);
						$cookie_time = strtotime($new_date)  -  (get_option( 'gmt_offset' )*3600);
					if($type_of_antisnipping == "snipping_recursive"){
						
						
						
						
						$is_done = update_post_meta($aucid, 'woo_ua_auction_end_date', date("Y-m-d H:i:s", $dt));
					
						/* Set that recursive antisipping is done */
						if($is_done != false){
						
								 
							setcookie('acution_end_time_php_'.$aucid, $cookie_time, time() + (86400 * 30 * 7), "/");
							update_post_meta($aucid, 'woo_ua_auction_extend_time_antisnipping_recursive', 'yes');
							
							/* Send Ending Soon Auction Mail again */
							//do_action( 'woo_ua_auctions_ending_soon_email_bidders', $aucid);
						}
					}					
					else if($type_of_antisnipping == "snipping_only_once" || $type_of_antisnipping == "" ){ 
					
					
					
					
						
						$is_updated = update_post_meta($aucid, 'woo_ua_auction_end_date', date("Y-m-d H:i:s", $dt));
					
						/* Set that only once antisipping is done */
						if($is_updated != false){
							
							setcookie('acution_end_time_php_'.$aucid, $cookie_time, time() + (86400 * 30 * 7), "/");
							update_post_meta($aucid, 'woo_ua_auction_extend_time_antisnipping', 'yes');
							
							/* Send Ending Soon Auction Mail again */
							//do_action( 'woo_ua_auctions_ending_soon_email_bidders', $aucid);
						}
					}
				}
				
			} /* end of if */
			
	} /* end of if  -- extend minutes and hours  > 0  */
}
add_action('uwa_extend_auction_time', 'uwa_extend_auction_time_antisnipping', 10, 1);


if (!function_exists( 'get_auction_product_search_form')) {

    /**
     * Display Auction product search form.
     *
     * Will first attempt to locate the uwa-auction-searchform.php file in either the child or.
     * the parent, then load it. If it doesn't exist, then the default search form.
     * will be displayed.
     *
     * The default searchform uses html5.
     *
     * @param bool $echo (default: true).
     * @return string
     *
     */
    function uwa_get_auction_product_search_form( $echo = true ) {
        global $product_search_form_index;

        ob_start();

        if ( empty( $product_search_form_index ) ) {
        	$product_search_form_index = 0;
        }

        do_action( 'pre_get_auction_product_search_form' );

        wc_get_template( 'uwa-auction-searchform.php' );

        $form = apply_filters( 'get_uwa_auction_search_form', ob_get_clean() );

        if ( $echo ) {
            echo $form; // WPCS: XSS ok.
        } else {
            return $form;
        }
    } /* end of fuction */
}
	

function uwa_get_total_admin_expireauctions_count() {
		global $wpdb; 
		global $sitepress;		
						
		$meta_query= array(
			array(			     
				'key' => 'woo_ua_auction_closed',
				'value' => array('1','2','3','4'),
				'compare' => 'IN',
			),							
		);
		
		$curr_user_id = get_current_user_id();		
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',			
			'posts_per_page' => -1,	
			'author' => $curr_user_id,
			'meta_query' => array($meta_query),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')),
			'auction_arhive' => TRUE,
			'fields' => 'ids'
		);		
		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
		   
			$args['suppress_filters']=0;	
		}

		$auction_item_array = new WP_Query( $args );
		$auction_item_array_id = $auction_item_array->posts;

		$total_sum = 0;
		if(count($auction_item_array_id) > 0){
			foreach($auction_item_array_id as $key => $value) {
				$product = wc_get_product( $value );
				if (is_object($product)) {
					$id = $product->get_id();

					$logs = $wpdb->get_results( $wpdb->prepare( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d', $id ) );
					
					$productcount = count($logs);
					
					$total_sum += $productcount;
				}
			}
		}
		return $total_sum;
}	

function uwa_get_total_users_expireauctions_count($user_id) {      
		global $wpdb; 
		global $sitepress;					
							
		$meta_query= array(
			'relation' => 'AND',
			array(			     
				'key' => 'woo_ua_auction_closed',
				'value' => array('1','2','3','4'),
				'compare' => 'IN',
			),							
		);
	
		$curr_user_id = get_current_user_id();

		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',			
			'posts_per_page' => -1,				
			'meta_query' => array($meta_query),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')),
			'auction_arhive' => TRUE,
			'fields' => 'ids'
		);	

		if (!empty($user_id)) {          
        	$args['author__in'] = $user_id;
        } else {
            $args[ 'author__not_in'] = $curr_user_id;
        }
		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
		   
			$args['suppress_filters']=0;	
		}

		$auction_item_array = new WP_Query( $args );
		$auction_item_array_id = $auction_item_array->posts;

		$total_sum = 0;
		if(count($auction_item_array_id) > 0){
			foreach($auction_item_array_id as $key => $value) {
				$product = wc_get_product( $value );
				if (is_object($product)) {

					$id = $product->get_id();

					$logs = $wpdb->get_results( $wpdb->prepare( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d', $id ) );
					
					$productcount = count($logs);
					
					$total_sum += $productcount;
				}
			}
		}
		return $total_sum;		
}

function uwa_get_auctions_count($auction_type) {
		global $wpdb; 
		global $sitepress;		
		if ($auction_type == 'live') {
			$meta_query = array(
						'relation' => 'AND',
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							),
							array(
							'key'     => 'woo_ua_auction_has_started',
							'value' => '1',
							)							
						);
		} elseif ($auction_type == 'expired') {						
			$meta_query= array(
						'relation' => 'AND',
							array(			     
								'key' => 'woo_ua_auction_closed',
								'value' => array('1','2','3','4'),
								'compare' => 'IN',
							),							
						);
		} elseif ($auction_type == 'scheduled') {		
			$meta_query= array(						
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
								),	
							array(
							'key'     => 'woo_ua_auction_started',
							'value' => '0',
							)	
						);						
		}

		$curr_user_id = get_current_user_id();		
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',			
			'posts_per_page' => -1,	
			'author' => $curr_user_id,
			'meta_query' => array($meta_query),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')),
			'auction_arhive' => TRUE
		);		
		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
		   
			$args['suppress_filters']=0;	
		}
	    $auction_item_array = get_posts($args);		
		$total_items = count($auction_item_array); 

		return $total_items;		
}
	
function uwa_get_users_auctions_count($auction_type, $user_id) {      
		global $wpdb; 
		global $sitepress;					
		if ($auction_type == 'live') {
			$meta_query = array(
						'relation' => 'AND',
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key'     => 'woo_ua_auction_has_started',
								'value' => '1',
							)							
						);
		} elseif ($auction_type == 'expired') {						
			$meta_query= array(
						'relation' => 'AND',
							array(			     
								'key' => 'woo_ua_auction_closed',
								'value' => array('1','2','3','4'),
								'compare' => 'IN',
							),							
						);
		} elseif ($auction_type == 'scheduled') {			
			$meta_query= array(						
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
								),	
							array(
								'key'     => 'woo_ua_auction_started',
								'value' => '0',
							)	
						);						
		}

		$curr_user_id = get_current_user_id();

		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',			
			'posts_per_page' => -1,				
			'meta_query' => array($meta_query),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')),
			'auction_arhive' => TRUE
		);	

		if (!empty($user_id)) {          
        	$args['author__in'] = $user_id;
        } else {
            $args[ 'author__not_in'] = $curr_user_id;
        }
		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
		   
			$args['suppress_filters']=0;	
		}
	    $auction_item_array = get_posts($args);		
		$total_items = count($auction_item_array); 

		return $total_items;		
}

	
function uwa_front_user_bid_list( $user_id , $bid_status ) {

	if ('won' == $bid_status) {

		uwa_front_user_bids_won_list( $user_id, $bid_status );

	} else if ('active' == $bid_status) {

		uwa_front_user_bids_active_list( $user_id, $bid_status );

	} else if ('lost' == $bid_status) {

		uwa_front_user_bids_lost_list( $user_id, $bid_status );

	}	

}


function uwa_front_user_bid_list_OLD( $user_id , $bid_status ) {	

	/* THIS FUNCTION IS NOT NEEDED */

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;


	   $table = $wpdb->prefix."woo_ua_auction_log";	 
	   $query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);	  
	   $my_auctions = $wpdb->get_results( $query ); 

    $active_bids_count = 0;
    $lost_bids_count = 0;
    $won_bids_count = 0;
    $won_bids_products_ids = array();

	if ( count($my_auctions ) > 0 ) {
			$aelia_addon = "";
			$addons = uwa_enabled_addons();
			if(is_array($addons) && in_array('uwa_currency_switcher', $addons)){			
					$aelia_addon = true;			
			}

		?> 
		<table class="shop_table shop_table_responsive tbl_bidauc_list">
			<tr class="bidauc_heading">
			    <th class="toptable"><?php echo __( 'Image', 'woo_ua' ); ?></th>
			    <th class="toptable"><?php echo __( 'Product', 'woo_ua' ); ?></th>
			    <th class="toptable"><?php echo __( 'Your bid', 'woo_ua' ); ?></th>
			    <th class="toptable"><?php echo __( 'Current bid', 'woo_ua' ); ?></th>
			    <th class="toptable"><?php echo __( 'Maximum bid', 'woo_ua' ); ?></th>
			    <th class="toptable"><?php echo __( 'End date', 'woo_ua' ); ?></th>
			    <th class="toptable"><?php echo __( 'Status', 'woo_ua' ); ?></th>
			</tr>
			<?php	
			foreach ( $my_auctions as $my_auction ) {		  
			   
			   $product_id =  $my_auction->auction_id;	
				
				if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id	,'product', false, $sitepress->get_current_language());
				}
			   
			   
			   $product = wc_get_product( $product_id );

			   if(is_object($product)){

				  
				   
					if ( method_exists( $product, 'get_type') && $product->get_type() == 'auction' ) {

						if($aelia_addon == true){
							if($product->uwa_aelia_is_configure() == TRUE){							
								$my_auction->max_userbid = $product->uwa_aelia_base_to_active($my_auction->max_userbid);
							}						
						}	

				        $product_name = get_the_title( $product_id );
				        $product_url  = get_the_permalink( $product_id );
				        $a            = $product->get_image( 'thumbnail' );
				        
				        $uwa_proxy_maxbid_price = "---";
				        $uwa_proxy_maxbid_user = $product->get_uwa_auction_max_current_bider();
				        
				        if($uwa_proxy_maxbid_user > 0){				        
				        	if($user_id == $uwa_proxy_maxbid_user){				        
				        		$uwa_proxy_maxbid_price = wc_price($product->get_uwa_auction_max_bid());
				        	}
				        }

				    	if ($bid_status == "won" && $user_id == $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2' ){ 	        			
									$won_bids_count++;
				    		?>			
							<tr class="bidauc_won">            
				            	<td class="bidauc_img"><?php echo $a;?></td>
				            	<td class="bidauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
				            	<td class="bidauc_bid"><?php echo wc_price($my_auction->max_userbid); ?></td>
				            	<td class="bidauc_curbid"><?php echo $product->get_price_html(); ?></td>
						<td class="bidauc_maxbid" style="text-align: center;"><?php echo 
				            	  $uwa_proxy_maxbid_price; ?></td>				            	
				            	<td class="bidauc_enddate"><?php echo $product->get_uwa_auction_end_dates(); ?></td>	

								<?php

				            	/* -----  Pay now button for winner ----- */
								if (($user_id == $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2' && !$product->get_uwa_auction_payed() )) { 

									$won_bids_products_ids[]= $product->get_id();
									$checkout_url = esc_attr(add_query_arg("pay-uwa-auction",$product->get_id(), uwa_auction_get_checkout_url()));

									/* change payment url for auto order */
										$uwa_auto_order_enable = get_option('uwa_auto_order_enable');
										if($uwa_auto_order_enable == "yes"){								

											$productid = $product->get_id();
										    $uwa_order_id = get_post_meta( $productid, 'woo_ua_order_id', true);
										    if ($uwa_order_id > 0){
										    	$order = wc_get_order($uwa_order_id);

										    	if(is_object($order)){
												$checkout_url = $order->get_checkout_payment_url();
											}
											}				
										}

					            	?>
									<td class="bidauc_status"> 

										<?php 
										
										/* --- when offline_addon is active --- */

										$addons = uwa_enabled_addons();
										if(is_array($addons) && in_array(
											'uwa_offline_dealing_addon', $addons)){
										
												// buyers and stripe both deactive
											if(!in_array('uwa_buyers_premium_addon', 
												$addons) && 
												!in_array('uwa_stripe_auto_debit_addon', $addons)){
												//echo "in 1";

											}	// buyers active only
											elseif(in_array('uwa_buyers_premium_addon', 
												$addons) && 
												!in_array('uwa_stripe_auto_debit_addon', $addons)){
												//echo "in 2";	

												?>
													<a href="<?php echo $checkout_url; ?>" class="button alt">
													<?php echo apply_filters('ultimate_woocommerce_auction_pay_now_button_text', __( "Pay Buyer's Premium", 
														'woo_ua' ), 
													$product); ?>
													</a>	
												<?php 			

											}  // buyers and stripe both active
											elseif(in_array('uwa_buyers_premium_addon', 
												$addons) && 
												in_array('uwa_stripe_auto_debit_addon', $addons)){
												//echo "in 3";				
											}

										}
										else{
											
											?>
											<a href="<?php echo $checkout_url; ?>" 
												class="button alt">
											<?php echo apply_filters('ultimate_woocommerce_auction_pay_now_button_text', __( 'Pay Now', 'woo_ua' ), $product); ?>
											</a>	
											<?php 

										} /* end of else */

										?>									
										
									</td>
								
				            		<?php 
				            	} 
				            	else { ?>			            		
				            		<td class="bidauc_status"><?php echo __( 'Closed', 'woo_ua' ); ?></td>
				            		<?php
				        		}  ?>

								</tr> 	
				     	<?php } /* end of if of won  */
				    	
				    	/* ------------------------ For Lost bids  ---------------------- */


				    	elseif ($bid_status == "lost" && $user_id != $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2' ){
									$lost_bids_count++;
				    	 ?>			
							<tr class="bidauc_lost">            
				            	<td class="bidauc_img"><?php echo $a ;?></td>
				            	<td class="bidauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
				            	<td class="bidauc_bid"><?php echo wc_price($my_auction->max_userbid); ?></td>
				            	<td class="bidauc_curbid"><?php echo $product->get_price_html(); ?></td>
				            	<td class="bidauc_maxbid" style="text-align: center;"><?php echo 
				            	  $uwa_proxy_maxbid_price; ?></td>
				            	<td class="bidauc_enddate"><?php echo $product->get_uwa_auction_end_dates(); ?></td>
				            	<td class="bidauc_status"><?php echo __( 'Closed', 'woo_ua' ); ?></td>	                	
								</tr> 	
				     	<?php } /* end of if of lost */

				     	/* ------------------------ For active bids  ---------------------- */

				     	elseif($bid_status == "active" && $product->get_uwa_auction_expired() == false){ 
				     			$active_bids_count++;
				     		?>
				     		<tr class="bidauc_active">            
				            	<td class="bidauc_img"><?php echo $a ;?></td>
				            	<td class="bidauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
				            	<td class="bidauc_bid"><?php echo wc_price($my_auction->max_userbid); ?></td>
				            	<td class="bidauc_curbid"><?php echo $product->get_price_html(); ?></td>
				            	<td class="bidauc_maxbid" style="text-align: center;"><?php echo 
				            	  $uwa_proxy_maxbid_price; ?></td>		            	
				            	<td class="bidauc_enddate"><?php echo $product->get_uwa_auction_end_dates(); ?></td>
				            	<td class="bidauc_status"><?php echo __( 'Started', 'woo_ua' ); ?></td>	                	
								</tr> 	
								<?php
				     	}

					}  /* end of if method exists  */

				}
				
			} /* end of foreach */ 

			if($bid_status == "won" && count($won_bids_products_ids) > 0){ ?>

				<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>		
				<?php 
					/*	echo '<a href="'.apply_filters( 'ultimate_woocommerce_auction_all_pay_now_button_text',esc_attr(add_query_arg("pay-uwa-auction",implode(",", $won_bids_products_ids), uwa_auction_get_checkout_url()))).'" class="button">'.__( 'Check Out All', 'woo_ua' ).'</a>';  */


				$main_url = esc_attr(add_query_arg("pay-uwa-auction", implode(",", 
					$won_bids_products_ids), uwa_auction_get_checkout_url()));
				$main_text = __( "Check Out All", "woo_ua" );

				$uwa_url = apply_filters( 'uwa_checkout_all_url', $main_url );
				$uwa_text = apply_filters( 'uwa_checkout_all_text', $main_text );

				echo '<a href="'.$uwa_url.'" class="button uwa_checkout_all">'.$uwa_text.'</a>'; 				

				?>
				</td>
				</tr>
			 <?php	
			}
			elseif($bid_status == "won" && $won_bids_count == 0){ ?>

				<tr class="bidauc_msg"><td colspan="7"><div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">		
				  <?php _e( 'No bids available yet.' , 'woo_ua' ) ?>
				</div></td></tr>
			 <?php	
			}elseif($bid_status == "lost" && $lost_bids_count == 0){ ?>

				<tr class="bidauc_msg"><td colspan="7"><div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">		
				  <?php _e( 'No bids available yet.' , 'woo_ua' ) ?>
				</div></td></tr>
				
			 <?php
			}elseif($bid_status == "active" && $active_bids_count == 0){ ?>

				<tr class="bidauc_msg"><td colspan="7"><div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">		
				  <?php _e( 'No bids available yet.' , 'woo_ua' ) ?>
				</div></td></tr>

				 <?php	
			}
			?>
		</table> 

	<?php
	} /* end of if - count */
	else {
		$shop_page_id = wc_get_page_id( 'shop' );   
		$shop_page_url = $shop_page_id ? get_permalink( $shop_page_id ) : '';
		?>  
		<div class="woocommerce-message woocommerce-message--info woocommerce-Message 	
			woocommerce-Message--info woocommerce-info">		
			  <a class="woocommerce-Button button" href="<?php echo $shop_page_url;?>">
				<?php _e( 'Go shop' , 'woocommerce' ) ?>		</a> <?php _e( 'No bids available yet.' , 'woo_ua' ) ?>
		</div>
	                 
	<?php } /* end of else */

}


function uwa_front_user_bids_count( $user_id , $bid_status ) {
	 
	global $wpdb, $woocommerce;
	   $table = $wpdb->prefix."woo_ua_auction_log";	 
       $query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);	  
       $my_auctions = $wpdb->get_results( $query );


    $active_bids_count = 0;
    $lost_bids_count = 0;
    $won_bids_count = 0;

	if ( count($my_auctions ) > 0 ) {
	   foreach ( $my_auctions as $my_auction ) {
		   global $product;
	      // $product = wc_get_product( $my_auction->auction_id );
			
			 global $sitepress;
			   
			   $product_id =  $my_auction->auction_id;	
				
				if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id	,'product',false, $sitepress->get_current_language());
				}
			   
			   
			   $product = wc_get_product( $product_id );


			if(is_object($product)){

			   
			
				if ( method_exists( $product, 'get_type') && $product->get_type() == 'auction' ) {
					
		        	if ($bid_status == "won" && $user_id == $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2' ){ 
		        		/* echo "in won bids"; */
		        		$won_bids_count++;
						
		         	} /* end of if */
		        	
		        	/* ------------------------ For Lost bids  ---------------------- */


		        	elseif ($bid_status == "lost" && $user_id != $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2' ){
		        		/* echo "in lost bids"; */ 	
		        		$lost_bids_count++;
		         	} /* end of if of lost */

		         	elseif($bid_status == "active" && $product->get_uwa_auction_expired() == false){ 
		         		/* echo "in active bids"; */
		         		$active_bids_count++;
		         	}

				}  /* end of if method exists  */
			}
			
	    } /* end of foreach  */

	} /* end of if - count */

	if($bid_status == "won"){
		return  $won_bids_count;
	}elseif($bid_status == "lost"){
		return  $lost_bids_count;
	}elseif($bid_status == "active"){
		return  $active_bids_count;
	}else{
		return "null";
	}
}


function uwa_front_user_watchlist( $user_id ){

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	$my_auctions_watchlist = get_uwa_auction_watchlist_by_user($user_id);
	$my_auctions_watchlist_count = uwa_front_user_watchlist_count( $user_id );

	if ( $my_auctions_watchlist_count > 0 ) {
	?>
	<table class="shop_table shop_table_responsive tbl_watchauc_list">
	    <tr class="watchauc_heading">
	        <th class="toptable h_watchauc_img"><?php echo __( 'Image', 'woo_ua' ); ?></td>
	        <th class="toptable h_watchauc_name"><?php echo __( 'Product', 'woo_ua' ); ?></td>       
	        <th class="toptable h_watchauc_bid"><?php echo __( 'Current bid', 'woo_ua' ); ?></td>
	        <th class="toptable h_watchauc_status"><?php echo __( 'Status', 'woo_ua' ); ?></td>
	        <th class="toptable"></td>
	    </tr>
	    <?php
	    foreach($my_auctions_watchlist as $key => $value) {
			
	        $product      = wc_get_product( $value );
	        if ( !$product )
	            continue;
	        
	        if ( is_object($product) && method_exists( $product, 'get_type') && $product->get_type() == 'auction' ) {

		        $product_name = get_the_title( $value );
		        $product_url  = get_the_permalink( $value );
		        $a            = $product->get_image( 'thumbnail' );
				$checkout_url = esc_attr(add_query_arg("pay-uwa-auction", $product->get_id(), uwa_auction_get_checkout_url()));

				/* change payment url for auto order */
					$uwa_auto_order_enable = get_option('uwa_auto_order_enable');
					if($uwa_auto_order_enable == "yes"){								

						$productid = $product->get_id();
					    $uwa_order_id = get_post_meta( $productid, 'woo_ua_order_id', true);
					    if ($uwa_order_id > 0){
					    	$order = wc_get_order($uwa_order_id);
					    	
					    		if(is_object($order)){
							$checkout_url = $order->get_checkout_payment_url();
							}
						}				
					}	
					
		        ?>
		        <tr class="watchauc_list">
		            <td class="watchauc_img"><?php echo $a ?></td>
		            <td class="watchauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>           
		            <td class="watchauc_curbid"><?php echo $product->get_price_html(); ?></td>
		            <?php

		            /* -----  Pay now button for winner ----- */

					if (($user_id == $product->get_uwa_auction_current_bider() && 
						$product->get_uwa_auction_expired() == '2' && !$product->get_uwa_auction_payed() )) { 

		              	?>
						<td class="watchauc_status"> 
								<?php 
								
								/* --- when offline_addon is active --- */

								$addons = uwa_enabled_addons();
								if(is_array($addons) && in_array(
									'uwa_offline_dealing_addon', $addons)){
								
										// buyers and stripe both deactive
									if(!in_array('uwa_buyers_premium_addon', 
										$addons) && 
										!in_array('uwa_stripe_auto_debit_addon', $addons)){
										//echo "in 1";

									}	// buyers active only
									elseif(in_array('uwa_buyers_premium_addon', 
										$addons) && 
										!in_array('uwa_stripe_auto_debit_addon', $addons)){
										//echo "in 2";
										?>
											<a href="<?php echo $checkout_url; ?>" 
												class="button alt">
												<?php echo apply_filters('ultimate_woocommerce_auction_pay_now_button_text', __( 
													"Pay Buyer's Premium", 
													'woo_ua' ), $product); ?>
											</a>

									<?php 				

									}  // buyers and stripe both active
									elseif(in_array('uwa_buyers_premium_addon', 
										$addons) && 
										in_array('uwa_stripe_auto_debit_addon', $addons)){
										//echo "in 3";				
									}

								}
								else{
									?>
									<a href="<?php echo $checkout_url; ?>" 
										class="button alt">
										<?php echo apply_filters('ultimate_woocommerce_auction_pay_now_button_text', __( 'Pay Now', 
											'woo_ua' ), $product); ?>
									</a>

									<?php 

								} /* end of else */

								?>
										
						</td>
		            	<?php  
		        	} elseif ( $product->is_uwa_expired() ){ ?> 
					
					<td class="watchauc_status"><?php echo __( 'Closed', 'woo_ua' ); ?></td>

				<?php 
				} elseif( $product->is_uwa_live() ) { ?>		

					<td class="watchauc_status"><?php echo __( 'Started', 'woo_ua' ); ?></td>		   
				<?php } else { ?>

		                	<td class="watchauc_status"><?php echo __( 'Not Started', 'woo_ua' ); ?></td>

		                <?php
		            }
		            ?>
					<td class="product-remove">
						<a href="javascript:void(0)" data-auction-id="<?php echo esc_attr( $product->get_id() ); ?>" 
						class="remove-uwa uwa-watchlist-action remove" aria-label="Remove this item">×</a>
					</td>

		        </tr>
	        <?php
	    	}
		} ?>

	</table>

	  <?php
	} 
	else { ?>
	   
	   	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info watchauc_msg">
			
			   <?php _e( 'No auctions in watchlist' , 'woo_ua' ) ?>
		</div>
	                 
	<?php }
}


function uwa_front_user_watchlist_count( $user_id ){
	global $wpdb, $woocommerce;
	global $product;

	$my_auctions_watchlist = get_uwa_auction_watchlist_by_user($user_id);
	$watchlist_count = count($my_auctions_watchlist);

   	return $watchlist_count;
}

function uwa_front_user_auction_settings( $user_id ){
	do_action( 'woocommerce_before_edit_account_form' );
	/* $user_id = get_current_user_id(); */
 ?>

	<form class="woocommerce-EditAccountForm edit-account" action="" method="post">
		
		 <?php
		 	$uwa_disable_display_user_name = get_user_meta($user_id, 'uwa_disable_display_user_name', true) !== '0' ? '1' : '0';
		 	woocommerce_form_field( 'uwa_disable_display_user_name', array(
	        'type'          => 'checkbox',
	        'class'         => array('input-checkbox'),
	        'label'         => __('Display your name publicly', 'woo_ua'),
	        'required'  => false,
	        'default' => 1
	        ), $uwa_disable_display_user_name );

	       ?>

		<div class="clear"></div>

		<p>
			<?php wp_nonce_field( 'save_uwa_auctions_settings' ); ?>
			<input type="submit" class="woocommerce-Button button" name="save_uwa_auctions_settings" 
				value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>" />
			<input type="hidden" name="action" value="save_uwa_auctions_settings" />
		</p>
		
	</form>

 <?php

}

/**
 *  Ajax Bid Placed Message
 *
 */
function ajax_uwa_bid_place_message( $product_id ) {
	
	global $woocommerce; 
	$product_data = wc_get_product($product_id);
	
		/* --aelia-- */
	$product_base_currency = $product_data->uwa_aelia_get_base_currency();
  	$args = array("currency" => $product_base_currency);

	$current_user = wp_get_current_user();
	$is_slient_auction = $product_data->get_uwa_auction_silent();
	
	if($is_slient_auction == "yes"){	
	
		$display_bid_value = wc_price($product_data->get_uwa_last_bid(), $args);
	}
	else{
	
		$display_bid_value = wc_price($product_data->get_uwa_current_bid(), $args);
	}
	
	if($current_user->ID == $product_data->get_uwa_auction_current_bider()){
		
		if(!$product_data->is_uwa_reserve_met()){			
			$message = sprintf(
				__('Your bid of %s has been placed successfully.', 'woo_ua'),
				$display_bid_value);
		} 
		else {
			if($product_data->get_uwa_auction_max_bid()){
				$message = sprintf( 
					__('Your bid of %s has been placed successfully! Your max bid is %s.', 
						'woo_ua'),
					$display_bid_value, 
					wc_price($product_data->get_uwa_auction_max_bid(), $args));
				
			}
			else{				
				$message = sprintf(__('Your bid of %s has been placed successfully.', 
					'woo_ua'), $display_bid_value);
			}
		}	
		
	} 
	else {
		
		if($product_data->get_uwa_auction_proxy() =="yes"){
			$message = sprintf(__( "Your bid has been placed successfully.", 'woo_ua'));
		}
		else {
			
			$message = sprintf( 
			__( "Your bid of %s has been placed successfully.", 'woo_ua'),
				$display_bid_value);
		}
			
	} 

	
		$newmessage = '<div class="woocommerce-message" role="alert">'.$message.'</div>';
		return $newmessage;
}

/**
 *  Ajax Bid Placed Message for second bid layout
 *
 */
function ajax_uwa_bid_place_message_second_layout( $product_id ) {
	
	global $woocommerce; 
	$product_data = wc_get_product($product_id);
	
		/* --aelia-- */
	$product_base_currency = $product_data->uwa_aelia_get_base_currency();
  	$args = array("currency" => $product_base_currency);
  	$uwa_auction_click_button = $_REQUEST['uwa_auction_button'];
	$current_user = wp_get_current_user();
	$is_slient_auction = $product_data->get_uwa_auction_silent();
	
	if($is_slient_auction == "yes"){	
	
		$display_bid_value = wc_price($product_data->get_uwa_last_bid(), $args);
	}
	else{
	
		$display_bid_value = wc_price($product_data->get_uwa_current_bid(), $args);
	}
	
	if($current_user->ID == $product_data->get_uwa_auction_current_bider()){
		
		if(!$product_data->is_uwa_reserve_met()){			
			$message = sprintf(
				__('Your bid of %s has been placed successfully.', 'woo_ua'),
				$display_bid_value);
		} 
		else {
			if($product_data->get_uwa_auction_max_bid()){
				$message = sprintf( 
					__('Your bid of %s has been placed successfully!', 
						'woo_ua'),
					$display_bid_value);
				
			}
			else{				
				$message = sprintf(__('Your bid of %s has been placed successfully.', 
					'woo_ua'), $display_bid_value);
			}
		}	
		
	} 
	else {
		
		if($product_data->get_uwa_auction_proxy() =="yes"){
			$message = sprintf(__( "Your bid has been placed successfully.", 'woo_ua'));
		}
		else {
			
			$message = sprintf( 
			__( "Your bid of %s has been placed successfully.", 'woo_ua'),
				$display_bid_value);
		}
			
	} 

	
		$newmessage = '<div class="woocommerce-message" role="alert">'.$message.'</div>';
		return $newmessage;
}


function uwa_get_plugin_info_version() {

	?> 
	 <!-- <span class="uwa_info_text"> - Ultimate WooCommerce Auction PRO</span>
	 <span class="uwa_version_text">(Version:<?php echo UW_AUCTION_PRO_VERSION; ?>)</span> -->

	<h2 class="uwa_main_h2">
		<span class="uwa_plugin_info_text">
	    <?php
	    echo esc_html(
	        apply_filters(
	            'uwa_plugin_title_text',
	            __( 'Ultimate WooCommerce Auction PRO', 'woo_ua' )
	        )
	    );
	    ?>
		</span>
	    <span class="uwa_version_text">
	        <?php esc_html_e( 'Version :', 'woo_ua' ); ?>
	        <?php echo esc_html( UW_AUCTION_PRO_VERSION ); ?>
	    </span>
	</h2>

	<?php 
}

if(!function_exists('uwa_create_log')) {
	function uwa_create_log( $message, $level = 'debug', $source = 'uwa_auction' ) {
		$logger  = wc_get_logger();
		$context = array( 'source' => $source );

		return $logger->log( $level, $message, $context );
	}
}

/**
 * Get Auction WatchList By Auction Id
 *
 */
function get_uwa_auction_watchlist_by_auctionid( $auction_id  ) {
	
	global $wpdb;	
	$results = get_post_meta( $auction_id, "woo_ua_auction_watch"); 

	return $results;
} 

function uwa_front_user_bids_active_list( $user_id, $bid_status ) {

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	/* $table = $wpdb->prefix . 'woo_ua_auction_log';
	$query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);   
	$my_auctions = $wpdb->get_results( $query ); */

	$sort_by_title = get_option('uwa_bidsactive_sort_pro_title_asc', 'no');

	if ('yes' != $sort_by_title) {

		$my_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT auction_id, MAX(bid) as max_userbid FROM ' . 
			$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d GROUP by auction_id ORDER by date DESC', 
			$user_id));
	} else {

		$my_auctions = $wpdb->get_results( $wpdb->prepare(
			'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
			$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
			$wpdb->prefix . "posts wposts 
			ON lg.auction_id = wposts.ID 
			WHERE lg.userid = %d 
			AND wposts.post_type = 'product' 
			GROUP BY lg.auction_id 
			ORDER BY wposts.post_title ASC", 
			$user_id
		));
	}

	$active_bids_count = 0;
	$final_active_auctions = array();

	$disable_pro_img = get_option('uwa_bidsactive_disable_pro_image', 'no');
	$disable_pro_maxbid = get_option('uwa_bidsactive_disable_pro_maxbid', 'no');
	$enable_category_list = get_option('uwa_bidsactive_enable_category_listing', 'no');

	if (count($my_auctions) > 0) {

		$aelia_addon = '';
		$addons = uwa_enabled_addons();

		if (is_array($addons) && in_array('uwa_currency_switcher', $addons)) {
				$aelia_addon = true;            
		}

		?> 
		<table class="shop_table shop_table_responsive tbl_bidauc_list tbl_bidsactive_list">
			<tr class="bidauc_heading">

				<?php if ('yes' != $disable_pro_img) { ?>
					<th class="toptable h_bidauc_img"><?php esc_html_e( 'Image', 'woo_ua' ); ?></th>
				<?php } ?>
				
					<th class="toptable h_bidauc_name"><?php esc_html_e( 'Product', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_category_list) { ?>
					<th class="toptable h_bidauc_category"><?php esc_html_e( 'Category', 'woo_ua' ); ?></th>
				<?php } ?>

					<th class="toptable h_bidauc_bid"><?php esc_html_e( 'Your bid', 'woo_ua' ); ?></th>
					<th class="toptable h_bidauc_curbid"><?php esc_html_e( 'Current bid', 'woo_ua' ); ?></th>

				<?php if ('yes' != $disable_pro_maxbid) { ?>
					<th class="toptable h_bidauc_maxbid"><?php esc_html_e( 'Maximum bid', 'woo_ua' ); ?></th>
				<?php } ?>
					
					<th class="toptable h_bidauc_enddate"><?php esc_html_e( 'End date', 'woo_ua' ); ?></th>
					<th class="toptable h_bidauc_status"><?php esc_html_e( 'Status', 'woo_ua' ); ?></th>
			</tr>

			<?php	

			/* count for finalize auctions */
			foreach ($my_auctions as $my_auction) {
			   
			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}              
			   
				$product = wc_get_product($product_id); 

				if (is_object($product)) {                 
					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {                        

						if ('active' == $bid_status && $product->get_uwa_auction_expired() == false) { 

								$active_bids_count++;

							if (isset($_REQUEST['category_list']) && 'all' != $_REQUEST['category_list']) {
								if ($_REQUEST['category_list'] > 0) {

									$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

									if (in_array($_REQUEST['category_list'], $categories_ids)) {
										$final_active_auctions[] = $product_id;
									}
								}
							} else {
								$final_active_auctions[] = $product_id;
							}

						} /* end of if - active auctions */

					}  /* end of if method exists  */
				}
				
			} /* end of foreach */ 


			/* display only finalize auctions */
			foreach ($my_auctions as $my_auction) {
			   
			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}              
			   
				$product = wc_get_product( $product_id );

				if (is_object($product)) {                 
					if (method_exists( $product, 'get_type') && $product->get_type() == 'auction') {


						/* ------------------------ For active bids  ---------------------- */

						if ('active' == $bid_status && $product->get_uwa_auction_expired() == false) { 

							if (in_array($product_id, $final_active_auctions)) {    

								if (true == $aelia_addon) {
									if ($product->uwa_aelia_is_configure() == true) {                         
										$my_auction->max_userbid = $product->uwa_aelia_base_to_active($my_auction->max_userbid);
									}                       
								}   

								$auc_name = get_the_title( $product_id );
								$auc_url = get_the_permalink( $product_id );
								$auc_img = $product->get_image( 'thumbnail' );

								$auc_bid = wc_price($my_auction->max_userbid);
								$auc_curbid = $product->get_price_html();
								$auc_old_endate = $product->get_uwa_auction_end_dates();
								$uwa_strtotime = strtotime($auc_old_endate);
								$auc_endate = date('d-m-Y H:i:s', $uwa_strtotime);

								$auc_category = '';
								$category_text = '';

								$categories = get_the_terms( $product_id, 'product_cat' );
								if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
									$category_text .= '<ul class="product-categories">';

									// Loop through each category and display its name with a link
									foreach ( $categories as $category ) {
										$category_link = get_term_link( $category->term_id, 'product_cat' );
										$category_text .= '<li><a href="' . esc_url( $category_link ) . '">' . 
											esc_html( $category->name ) . '</a></li>';
									}
									$category_text .= '</ul>';
								}

								$auc_category = $category_text; 

								$proxy_maxbid_price = '---';
								$proxy_maxbid_user = $product->get_uwa_auction_max_current_bider();
									
								if ($proxy_maxbid_user > 0) {
									if ($user_id == $proxy_maxbid_user) {                     
										$proxy_maxbid_price = wc_price($product->get_uwa_auction_max_bid());
									}
								}

								?>
									<tr class="bidauc_active"> 

										<?php if ('yes' != $disable_pro_img) { ?>           
											<td class="bidauc_img"><?php echo wp_kses_post($auc_img); ?></td>
										<?php } ?>

											<td class="bidauc_name"><a href="<?php echo esc_url($auc_url); ?>">
												<?php echo esc_html($auc_name); ?></a></td>

										<?php if ('yes' == $enable_category_list) { ?>
											<td class="bidauc_category"><?php echo wp_kses_post($auc_category); ?></td>
										<?php } ?>

											<td class="bidauc_bid"><?php echo wp_kses_post($auc_bid); ?></td>
											<td class="bidauc_curbid"><?php echo wp_kses_post($auc_curbid); ?></td>

										<?php if ('yes' != $disable_pro_maxbid) { ?>
											<td class="bidauc_maxbid" style="text-align: center;">
												<?php echo wp_kses_post($proxy_maxbid_price); ?>
											</td>                       
										<?php } ?>

											<td class="bidauc_enddate"><?php echo esc_html($auc_endate); ?></td>
											<td class="bidauc_status"><?php echo esc_html__( 'Started', 'woo_ua' ); ?></td>	                	
									</tr> 	
									  <?php

							} /* end of if - in array */

						}

					}  /* end of if method exists  */

				}
				
			} /* end of foreach */ 

			if ('active' == $bid_status && 0 == $active_bids_count) {
				?>

				<tr class="bidauc_msg"><td colspan="7">
					<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">	<?php esc_html_e( 'No bids available yet.', 'woo_ua' ); ?></div></td>
				</tr>
				
				<?php
			}
			?>
		</table> 

	<?php
	} else {
		$shop_page_id = wc_get_page_id( 'shop' );   
		$shop_page_url = $shop_page_id ? get_permalink( $shop_page_id ) : '';
		?>
			<div class="woocommerce-message woocommerce-message--info woocommerce-Message 
				woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="<?php echo esc_url($shop_page_url); ?>">
					<?php esc_html_e( 'Go shop' , 'woo_ua' ); ?></a> 
				<?php esc_html_e( 'No bids available yet.' , 'woo_ua' ); ?>
			</div>
					 
	<?php
	} /* end of else */
}

function uwa_front_user_bids_won_list( $user_id, $bid_status ) {

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	/* $table = $wpdb->prefix . 'woo_ua_auction_log';
	$query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);   
	$my_auctions = $wpdb->get_results( $query ); */

	$sort_by_title = get_option('uwa_bidswon_sort_pro_title_asc', 'no');

	if ('yes' != $sort_by_title) {

		$my_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT auction_id, MAX(bid) as max_userbid FROM ' . 
			$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d GROUP by auction_id ORDER by date DESC', 
			$user_id));
	} else {

		$my_auctions = $wpdb->get_results( $wpdb->prepare(
			'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
			$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
			$wpdb->prefix . "posts wposts 
			ON lg.auction_id = wposts.ID 
			WHERE lg.userid = %d 
			AND wposts.post_type = 'product' 
			GROUP BY lg.auction_id 
			ORDER BY wposts.post_title ASC", 
			$user_id
		));
	}

	$won_bids_count = 0;
	$won_bids_products_ids = array();
	$final_won_auctions = array();

	$disable_pro_img = get_option('uwa_bidswon_disable_pro_image', 'no');
	$disable_pro_maxbid = get_option('uwa_bidswon_disable_pro_maxbid', 'no');
	$disable_pro_paynow = get_option('uwa_bidswon_disable_pro_paynow', 'no');
	$disable_pro_checkoutall = get_option('uwa_bidswon_disable_pro_checkoutall', 'no');
	$enable_second_bid = get_option('uwa_bidswon_enable_second_bid', 'no');  
	$enable_third_bid = get_option('uwa_bidswon_enable_third_bid', 'no');    
	$enable_category_list = get_option('uwa_bidswon_enable_category_listing', 'no');    

	if (count($my_auctions) > 0) {

		$aelia_addon = '';
		$addons = uwa_enabled_addons();

		if (is_array($addons) && in_array('uwa_currency_switcher', $addons)) {
			$aelia_addon = true;
		}

		?> 
		<table class="shop_table shop_table_responsive tbl_bidauc_list tbl_bidswon_list">
			<tr class="bidauc_heading">

				<?php if ('yes' != $disable_pro_img) { ?>
					<th class="toptable h_bidauc_img"><?php esc_html_e( 'Image', 'woo_ua' ); ?></th>
				<?php } ?>
				
					<th class="toptable h_bidauc_name"><?php esc_html_e( 'Product', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_category_list) { ?>
					<th class="toptable h_bidauc_category"><?php esc_html_e( 'Category', 'woo_ua' ); ?></th>
				<?php } ?>

					<th class="toptable h_bidauc_bid"><?php esc_html_e( 'Your bid', 'woo_ua' ); ?></th>
					<th class="toptable h_bidauc_curbid"><?php esc_html_e( 'Current bid', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_second_bid) { ?>
					<th class="toptable h_bidauc_secbid"><?php esc_html_e( 'Second highest bid', 'woo_ua' ); ?></th>
				<?php } ?>

				<?php if ('yes' == $enable_third_bid) { ?>
					<th class="toptable h_bidauc_thirdbid"><?php esc_html_e( 'Third highest bid', 'woo_ua' ); ?></th>
				<?php } ?>									

				<?php if ('yes' != $disable_pro_maxbid) { ?>
					<th class="toptable h_bidauc_maxbid"><?php esc_html_e( 'Maximum bid', 'woo_ua' ); ?></th>
				<?php } ?>
					
					<th class="toptable h_bidauc_enddate"><?php esc_html_e( 'End date', 'woo_ua' ); ?></th>

				<?php if ('yes' != $disable_pro_paynow) { ?>
					<th class="toptable h_bidauc_status"><?php esc_html_e( 'Status', 'woo_ua' ); ?></th>
				<?php } ?>

			</tr>

			<?php	


			/* count for finalize auctions */
			foreach ($my_auctions as $my_auction) {

			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}

				$product = wc_get_product($product_id);

				if (is_object($product)) {
					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {

						if ('won' == $bid_status && $user_id == $product->get_uwa_auction_current_bider() 
							&& $product->get_uwa_auction_expired() == '2') { 

								$won_bids_count++;

							if (isset($_REQUEST['category_list']) && 'all' != $_REQUEST['category_list']) {
								if ($_REQUEST['category_list'] > 0) {

									$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

									if (in_array($_REQUEST['category_list'], $categories_ids)) {
										$final_won_auctions[] = $product_id;
									}
								}
							} else {
								$final_won_auctions[] = $product_id;
							}

						} /* end of if - won auctions */

					} /* end of if - method exists */

				} /* end of if - is object */

			} /* end of foreach */


			/* display only finalize auctions */
			foreach ($my_auctions as $my_auction) {
			   
			   $product_id =  $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}
			   
				$product = wc_get_product( $product_id );

				if (is_object($product)) {                 
					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {


							/* ------------------------ For won bids  ---------------------- */

						if ('won' == $bid_status && $user_id == $product->get_uwa_auction_current_bider() && 
							$product->get_uwa_auction_expired() == '2') { 
							
							if (in_array($product_id, $final_won_auctions)) {                               
						
								if (true == $aelia_addon) {
									if ($product->uwa_aelia_is_configure() == true) {                         
										$my_auction->max_userbid = $product->uwa_aelia_base_to_active($my_auction->max_userbid);
									}                       
								}   

								$auc_name = get_the_title( $product_id );
								$auc_url = get_the_permalink( $product_id );
								$auc_img = $product->get_image( 'thumbnail' );

								$auc_bid = wc_price($my_auction->max_userbid);
								$auc_curbid = $product->get_price_html();
								$auc_old_endate = $product->get_uwa_auction_end_dates();
								$uwa_strtotime = strtotime($auc_old_endate);
								$auc_endate = date('d-m-Y H:i:s', $uwa_strtotime);

								$auc_category = '';
								$category_text = '';

								$categories = get_the_terms($product_id, 'product_cat');
								if (!empty( $categories ) && !is_wp_error( $categories )) {
									$category_text .= '<ul class="product-categories">';

									// Loop through each category and display its name with a link
									foreach ( $categories as $category ) {
										$category_link = get_term_link( $category->term_id, 'product_cat' );
										$category_text .= '<li><a href="' . esc_url( $category_link ) . '">' . 
											esc_html( $category->name ) . '</a></li>';
									}
									$category_text .= '</ul>';
								}

								$auc_category = $category_text;

								$proxy_maxbid_price = '---';
								$proxy_maxbid_user = $product->get_uwa_auction_max_current_bider();
										
								if ($proxy_maxbid_user > 0) {
									if ($user_id == $proxy_maxbid_user) {
										$proxy_maxbid_price = wc_price($product->get_uwa_auction_max_bid());
									}
								}

								$uwa_current_bid = $product->get_uwa_auction_current_bid();									

								$second_highbid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
									$wpdb->prefix . 'woo_ua_auction_log WHERE bid < %f and auction_id = %d ORDER by bid DESC', $uwa_current_bid, $product_id));		
									
								if ($second_highbid) {

									$third_highbid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' .
										$wpdb->prefix . 'woo_ua_auction_log WHERE bid < %f and auction_id = %d ORDER by bid DESC', $second_highbid, $product_id));

								} else {

									$third_highbid = "";
								}							

								?>
									<tr class="bidauc_won"> 

										<?php if ('yes' != $disable_pro_img) { ?>           
											<td class="bidauc_img"><?php echo wp_kses_post($auc_img); ?></td>
										<?php } ?>

											<td class="bidauc_name"><a href="<?php echo esc_url($auc_url); ?>">
												<?php echo esc_html($auc_name); ?></a></td>

										<?php if ('yes' == $enable_category_list) { ?>
											<td class="bidauc_category"><?php echo wp_kses_post($auc_category); ?></td>
										<?php } ?>

											<td class="bidauc_bid"><?php echo wp_kses_post($auc_bid); ?></td>
											<td class="bidauc_curbid"><?php echo wp_kses_post($auc_curbid); ?></td>

										<?php if ('yes' == $enable_second_bid) { 

											if (!empty($second_highbid)) {
												$sec_highbid_val = wc_price($second_highbid);
											} else {
												$sec_highbid_val = "---";
											}											

											?>
											<td class="bidauc_secbid"><?php echo wp_kses_post($sec_highbid_val); ?></td>
										<?php } ?>

										<?php if ('yes' == $enable_third_bid) { 

											if (!empty($third_highbid)) {
												$third_highbid_val = wc_price($third_highbid);
											} else {
												$third_highbid_val = "---";
											}

											?>
											<td class="bidauc_thirdbid"><?php echo wp_kses_post($third_highbid_val); ?></td>
										<?php } ?>																		

										<?php if ('yes' != $disable_pro_maxbid) { ?>
											<td class="bidauc_maxbid" style="text-align: center;">
												<?php echo wp_kses_post($proxy_maxbid_price); ?>
											 </td>                       
										<?php } ?>

											<td class="bidauc_enddate"><?php echo esc_html($auc_endate); ?></td>

										<?php

											/* calculation of won product ids for checkout all button */
										if ( $user_id == $product->get_uwa_auction_current_bider() && 
											$product->get_uwa_auction_expired() == '2' && 
											!$product->get_uwa_auction_payed() ) { 

											$won_bids_products_ids[]= $product->get_id();
										}

										/* ---- pay now button for winner ---- */
										if ('yes' != $disable_pro_paynow) {

											if (( $user_id == $product->get_uwa_auction_current_bider() && 
												$product->get_uwa_auction_expired() == '2' && 
												!$product->get_uwa_auction_payed() )) { 

												/*$won_bids_products_ids[]= $product->get_id();*/
												$checkout_url = esc_attr(add_query_arg('pay-uwa-auction', $product->get_id(), uwa_auction_get_checkout_url()));

												/* --- change payment url for auto order --- */
												$uwa_auto_order_enable = get_option('uwa_auto_order_enable');
												if ('yes' == $uwa_auto_order_enable) {

													$productid = $product->get_id();
													$uwa_order_id = get_post_meta($productid, 'woo_ua_order_id', true);
													if ($uwa_order_id > 0) {
														$uwa_order = wc_get_order($uwa_order_id);

														if (is_object($uwa_order)) {
															$checkout_url = $uwa_order->get_checkout_payment_url();
														}
													}
												}
													
												$paynow_btn_text =  __( 'Pay Now', 'woo_ua' );

												/* --- when offline_addon is active --- */
												$addons = uwa_enabled_addons();
												if (is_array($addons) && in_array('uwa_offline_dealing_addon', $addons)) {
													
													// buyers and stripe both deactive - nothing to change
													// buyers and stripe both active - nothing to change

													if (in_array('uwa_buyers_premium_addon', $addons) && 
														!in_array('uwa_stripe_auto_debit_addon', $addons)) {

														$paynow_btn_text =  __( "Pay Buyer's Premium", 'woo_ua' );                                          
													}
												}
												?>

												<td class="bidauc_status"> 
													<a href="<?php echo esc_url($checkout_url); ?>" class="button alt">
													<?php echo esc_html(apply_filters('ultimate_woocommerce_auction_pay_now_button_text', $paynow_btn_text, $product)); ?>
													</a>
												</td>
												
												<?php

											} else {
												?>

												<td class="bidauc_status"><?php echo esc_html__( 'Closed', 'woo_ua' ); ?></td> 
												<?php	  
											}

										} /* end of if column paynow disable */                             
										?>

									</tr> 
								  <?php

							} /* end of if - in array */
						}

					}  /* end of if method exists */

				} /* end of if is_object */
				
			} /* end of foreach */ 

			if ('won' == $bid_status && count($won_bids_products_ids) > 0) { 
			
				/* hide checkout all button */
				if ('yes' != $disable_pro_checkoutall) {

					?>

					<tr>					
						<td>		
							<?php

							$main_url = esc_attr(add_query_arg('pay-uwa-auction', implode(',', 
								$won_bids_products_ids), uwa_auction_get_checkout_url()));
							$main_text = __( 'Check Out All', 'woo_ua' );

							$uwa_url = apply_filters( 'uwa_checkout_all_url', $main_url );
							$uwa_text = apply_filters( 'uwa_checkout_all_text', $main_text );

							echo '<a href="' . esc_url($uwa_url) . '" class="button uwa_checkout_all">' . esc_html($uwa_text) . '</a>';

							?>
						</td>
					</tr>
					<?php	
				}
			 
			} else if ('won' == $bid_status && 0 == $won_bids_count) {
				?>

				<tr class="bidauc_msg"><td colspan="7">
					<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">	<?php esc_html_e( 'No bids available yet.', 'woo_ua' ); ?></div></td>
				</tr>
				
				<?php
			}
			?>
		</table> 

	<?php
	} else {

		$shop_page_id = wc_get_page_id( 'shop' );   
		$shop_page_url = $shop_page_id ? get_permalink( $shop_page_id ) : '';
		?>
			<div class="woocommerce-message woocommerce-message--info woocommerce-Message 
				woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="<?php echo esc_url($shop_page_url); ?>">
					<?php esc_html_e( 'Go shop' , 'woo_ua' ); ?></a> 
				<?php esc_html_e( 'No bids available yet.' , 'woo_ua' ); ?>
			</div>
					 
	<?php
	} /* end of else */
}

function uwa_front_user_bids_lost_list( $user_id, $bid_status ) {

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	/* $table = $wpdb->prefix . 'woo_ua_auction_log';
	$query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);   
	$my_auctions = $wpdb->get_results( $query ); */

	$sort_by_title = get_option('uwa_bidslost_sort_pro_title_asc', 'no');

	if ('yes' != $sort_by_title) {

		$my_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT auction_id, MAX(bid) as max_userbid FROM ' . 
			$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d GROUP by auction_id ORDER by date DESC', 
			$user_id));
	} else {

		$my_auctions = $wpdb->get_results( $wpdb->prepare(
			'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
			$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
			$wpdb->prefix . "posts wposts 
			ON lg.auction_id = wposts.ID 
			WHERE lg.userid = %d 
			AND wposts.post_type = 'product' 
			GROUP BY lg.auction_id 
			ORDER BY wposts.post_title ASC", 
			$user_id
		));
	}

	$lost_bids_count = 0;
	$final_lost_auctions = array();

	$disable_pro_img = get_option('uwa_bidslost_disable_pro_image', 'no');
	$disable_pro_maxbid = get_option('uwa_bidslost_disable_pro_maxbid', 'no');
	$enable_second_bid = get_option('uwa_bidslost_enable_second_bid', 'no');
	$enable_third_bid = get_option('uwa_bidslost_enable_third_bid', 'no');
	$enable_category_list = get_option('uwa_bidslost_enable_category_listing', 'no');

	if (count($my_auctions) > 0) {

		$aelia_addon = '';
		$addons = uwa_enabled_addons();

		if (is_array($addons) && in_array('uwa_currency_switcher', $addons)) {
			$aelia_addon = true;
		}

		?> 
		<table class="shop_table shop_table_responsive tbl_bidauc_list tbl_bidslost_list">
			<tr class="bidauc_heading">

				<?php if ('yes' != $disable_pro_img) { ?>
					<th class="toptable h_bidauc_img"><?php esc_html_e( 'Image', 'woo_ua' ); ?></th>
				<?php } ?>
				
					<th class="toptable h_bidauc_name"><?php esc_html_e( 'Product', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_category_list) { ?>
					<th class="toptable h_bidauc_category"><?php esc_html_e( 'Category', 'woo_ua' ); ?></th>
				<?php } ?>					

					<th class="toptable h_bidauc_bid"><?php esc_html_e( 'Your bid', 'woo_ua' ); ?></th>
					<th class="toptable h_bidauc_curbid"><?php esc_html_e( 'Current bid', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_second_bid) { ?>
					<th class="toptable h_bidauc_secbid"><?php esc_html_e( 'Second highest bid', 'woo_ua' ); ?></th>
				<?php } ?>

				<?php if ('yes' == $enable_third_bid) { ?>
					<th class="toptable h_bidauc_thirdbid"><?php esc_html_e( 'Third highest bid', 'woo_ua' ); ?></th>
				<?php } ?>

				<?php if ('yes' != $disable_pro_maxbid) { ?>
					<th class="toptable h_bidauc_maxbid"><?php esc_html_e( 'Maximum bid', 'woo_ua' ); ?></th>
				<?php } ?>
					
					<th class="toptable h_bidauc_enddate"><?php esc_html_e( 'End date', 'woo_ua' ); ?></th>
					<th class="toptable h_bidauc_status"><?php esc_html_e( 'Status', 'woo_ua' ); ?></th>
			</tr>

			<?php	

			/* count for finalize auctions */
			foreach ($my_auctions as $my_auction) {

			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}

				$product = wc_get_product($product_id);

				if (is_object($product)) {
					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {

						if ('lost' == $bid_status && $user_id != $product->get_uwa_auction_current_bider() 
							&& $product->get_uwa_auction_expired() == '2') { 

								$lost_bids_count++;

							if (isset($_REQUEST['category_list']) && 'all' != $_REQUEST['category_list']) {
								if ($_REQUEST['category_list'] > 0) {

									$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

									if (in_array($_REQUEST['category_list'], $categories_ids)) {
										$final_lost_auctions[] = $product_id;
									}
								}
							} else {
								$final_lost_auctions[] = $product_id;
							}

						} /* end of if - won auctions */

					} /* end of if - method exists */

				} /* end of if - is object */

			} /* end of foreach */


			/* display only finalize auctions */
			foreach ($my_auctions as $my_auction) {
			   
			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {    
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, $sitepress->get_current_language());
				}              
			   
				$product = wc_get_product( $product_id );

				if (is_object($product)) {            
				   
					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {


						/* ------------------------ For lost bids  ---------------------- */

						if ( 'lost' == $bid_status && $user_id != $product->get_uwa_auction_current_bider() && 
							$product->get_uwa_auction_expired() == '2') { 

							if (in_array($product_id, $final_lost_auctions)) {

								if (true == $aelia_addon) {
									if ($product->uwa_aelia_is_configure() == true) {                         
										$my_auction->max_userbid = $product->uwa_aelia_base_to_active($my_auction->max_userbid);
									}                       
								}   

								$auc_name = get_the_title( $product_id );
								$auc_url = get_the_permalink( $product_id );
								$auc_img = $product->get_image( 'thumbnail' );

								$auc_bid = wc_price($my_auction->max_userbid);
								$auc_curbid = $product->get_price_html();
								$auc_old_endate = $product->get_uwa_auction_end_dates();
								$uwa_strtotime = strtotime($auc_old_endate);
								$auc_endate = date('d-m-Y H:i:s', $uwa_strtotime);

								$auc_category = '';
								$category_text = '';

								$categories = get_the_terms($product_id, 'product_cat');
								if (!empty( $categories ) && !is_wp_error( $categories )) {
									$category_text .= '<ul class="product-categories">';

									// Loop through each category and display its name with a link
									foreach ( $categories as $category ) {
										$category_link = get_term_link( $category->term_id, 'product_cat' );
										$category_text .= '<li><a href="' . esc_url( $category_link ) . '">' . 
											esc_html( $category->name ) . '</a></li>';
									}
									$category_text .= '</ul>';
								}

								$auc_category = $category_text;

								$proxy_maxbid_price = '---';
								$proxy_maxbid_user = $product->get_uwa_auction_max_current_bider();
									
								if ($proxy_maxbid_user > 0) {
									if ($user_id == $proxy_maxbid_user) {
										$proxy_maxbid_price = wc_price($product->get_uwa_auction_max_bid());
									}
								}

								$uwa_current_bid = $product->get_uwa_auction_current_bid();

								$second_highbid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
									$wpdb->prefix . 'woo_ua_auction_log WHERE bid < %f and auction_id = %d ORDER by bid DESC', $uwa_current_bid, $product_id));		
									
								if ($second_highbid) {

									$third_highbid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' .
										$wpdb->prefix . 'woo_ua_auction_log WHERE bid < %f and auction_id = %d ORDER by bid DESC', $second_highbid, $product_id));

								} else {

									$third_highbid = "";
								}	

								
								?>
									<tr class="bidauc_lost"> 

										<?php if ('yes' != $disable_pro_img) { ?>           
											<td class="bidauc_img"><?php echo wp_kses_post($auc_img); ?></td>
										<?php } ?>

											<td class="bidauc_name"><a href="<?php echo esc_url($auc_url); ?>">
												<?php echo esc_html($auc_name); ?></a></td>

										<?php if ('yes' == $enable_category_list) { ?>
											<td class="bidauc_category"><?php echo wp_kses_post($auc_category); ?></td>
										<?php } ?>										

											<td class="bidauc_bid"><?php echo wp_kses_post($auc_bid); ?></td>
											<td class="bidauc_curbid"><?php echo wp_kses_post($auc_curbid); ?></td>

										<?php if ('yes' == $enable_second_bid) { 

											if (!empty($second_highbid)) {
												$sec_highbid_val = wc_price($second_highbid);
											} else {
												$sec_highbid_val = "---";
											}	

											?>
											<td class="bidauc_secbid"><?php echo wp_kses_post($sec_highbid_val); ?></td>
										<?php } ?>

										<?php if ('yes' == $enable_third_bid) { 

											if (!empty($third_highbid)) {
												$third_highbid_val = wc_price($third_highbid);
											} else {
												$third_highbid_val = "---";
											}	

											?>
											<td class="bidauc_thirdbid"><?php echo wp_kses_post($third_highbid_val); ?></td>
										<?php } ?>																												

										<?php if ('yes' != $disable_pro_maxbid) { ?>
											<td class="bidauc_maxbid" style="text-align: center;">
												<?php echo wp_kses_post($proxy_maxbid_price); ?>
											 </td>                       
										<?php } ?>

											<td class="bidauc_enddate"><?php echo esc_html($auc_endate); ?></td>
											<td class="bidauc_status"><?php echo esc_html__( 'Closed', 'woo_ua' ); ?></td>     	
									</tr>
										<?php

							} /* end of if - in array */
						}

					}  /* end of if method exists  */
				}
				
			} /* end of foreach */ 

			if ('lost' == $bid_status && 0 == $lost_bids_count) {
				?>

				<tr class="bidauc_msg"><td colspan="7">
					<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">	<?php esc_html_e( 'No bids available yet.', 'woo_ua' ); ?></div></td>
				</tr>
				
				<?php
			}
			?>
		</table> 

	<?php
	} else {
		$shop_page_id = wc_get_page_id( 'shop' );   
		$shop_page_url = $shop_page_id ? get_permalink( $shop_page_id ) : '';
		?>
			<div class="woocommerce-message woocommerce-message--info woocommerce-Message 
				woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="<?php echo esc_url($shop_page_url); ?>">
					<?php esc_html_e( 'Go shop' , 'woo_ua' ); ?></a> 
				<?php esc_html_e( 'No bids available yet.' , 'woo_ua' ); ?>
			</div>
					 
	<?php
	} /* end of else */
}

function uwa_front_user_export_all_expired_auctions( $user_id, $bid_status ) {

	global $wpdb, $sitepress;

	$uwa_all_auctions = array();

	$uwa_dataformat_csv = get_option('uwa_exp_user_csv_dataformat_csv', 'yes');
	$uwa_dataformat_xls = get_option('uwa_exp_user_csv_dataformat_xls', 'yes');
	$uwa_dataformat_pdf = get_option('uwa_exp_user_csv_dataformat_pdf', 'yes');

	if ($uwa_dataformat_csv == "no" && $uwa_dataformat_xls == "no" && $uwa_dataformat_pdf == "no") { 
		?>

			<div class="woocommerce-message woocommerce-message--info woocommerce-Message 
				woocommerce-Message--info woocommerce-info">		
				<?php esc_html_e( 'Exporting expired auctions is currently not available.' , 'woo_ua' ); ?>
			</div>

			<?php					
			return;
	}

	$auction_args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => '-1',
		'tax_query' => array( array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' ) ),
		'orderby' => 'title',
		'order' => 'ASC',
		'auction_arhive' => true,
	);
	
	$auction_args['meta_query'] = array(
				'relation' => 'AND',
					array(
						'key' => 'woo_ua_auction_closed',
						'value' => array( '1', '2', '3', '4' ),
						'compare' => 'IN',
					),
				);

	$query = new WP_Query($auction_args);

	if ($query->have_posts()) {
		while ($query->have_posts()) {

			$query->the_post();
			$product_id = get_the_ID();
				
			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
				$product_id = icl_object_id($product_id, 'product', false, 
					$sitepress->get_current_language());
			}
			   
			$product = wc_get_product($product_id);

			if (is_object($product)) {

				if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {

					$uwa_all_auctions[] = $product_id;

				}  /* end of if method exists  */
			}

		} /* end of while */
	} /* end of if query */

	?>
	<div style="float:right; margin-right:10px;">
		<div class="ex-csv-btn">
			<form id="uwa_frm_file_format" class="uwa_cat" method="post">

					<?php
					$enable_category_list = get_option('uwa_exp_user_csv_enable_category_listing', 'no');
					if ('yes' == $enable_category_list) {


						/* -------- Get Product Categories From Won Auctions ---------- */

						$uwa_categories_ids = array();

						if (is_array($uwa_all_auctions) && count($uwa_all_auctions) > 0) { 

							foreach ($uwa_all_auctions as $auction_ID) {

								$product = wc_get_product( $auction_ID ); 
								if ('export_expired' == $bid_status) { 

									$categories = get_the_terms( $auction_ID, 'product_cat' );
									if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
										foreach ( $categories as $category ) {
											$uwa_categories_ids[] = $category->term_id;
										}                                           
									}

								} /* end of if */

							} /* end of foreach */
									
							$final_categories_ids = array_values(array_unique($uwa_categories_ids));
							$total_categories = count($final_categories_ids);

							if ($total_categories > 0) { 

								foreach ($final_categories_ids as $term_id) {
									$obj_term = get_term($term_id, 'product_cat');
									$term_name = $obj_term->name;

									/* product categories array */
									$uwa_categories[$term_id] = $term_name;
								}                                       

								/* sorts the array values in ascending order and preserve keys */
								/* asort($uwa_categories); */

								/* sorts the array values in ascending order but not case sensitive & preserve keys */
								uasort($uwa_categories, 'strnatcasecmp');

								if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] > 0) {
									$selected_id = sanitize_text_field($_REQUEST['category_list']);
								}
									
								?>
									<div class="cat-ad-list">
										<select name="category_list" id="category_list" onchange="updateExportLink()">
											<option value="all">
												<?php echo esc_attr__( 'All Categories', 'woo_ua' ); ?>
											</option>

										<?php foreach ($uwa_categories as $term_id => $term_name) { ?>
												<?php if ($selected_id == $term_id) { ?>
													<option value="<?php echo esc_attr($term_id); ?>" selected>
														<?php echo esc_html($term_name); ?>
													</option>
												<?php } else { ?>
													<option value="<?php echo esc_attr($term_id); ?>">
														<?php echo esc_html($term_name); ?>
													</option>	
												<?php } ?>

											<?php } ?>
										</select>

										<input class="uwa-highlight-btn highlight-btn-disabled" 
											style="border: 1px solid #2271b1;" type="submit" 
											value="<?php echo esc_attr__( 'Submit', 'woo_ua' ); ?>" />
									</div>
									<?php	
							}

						} /* end of if - is array */ 

					} /* end of if category */
					?>
					
				<div class="exp-list">
					<label for="file_format">
						<?php echo esc_attr__( 'Choose file format:', 'woo_ua' ); ?>
					</label>
					<select style="margin-right:5px;" name="file_format" id="file_format" onchange="updateExportLink()">

						<?php if ($uwa_dataformat_csv != "no") { ?>
							<option value="csv" selected>CSV</option> 
						<?php } ?>

						<?php if ($uwa_dataformat_xls != "no") { ?>
							<option value="xls">XLSX</option>
						<?php } ?>

						<?php if ($uwa_dataformat_pdf != "no") { ?>
							<option value="pdf">PDF</option>
						<?php } ?>

					</select>

					<a id="exportLink" style="border:1px solid #2271b1;" href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>uwa-auctions/?display=export_expired&action=uwa_user_all_expired_download_csv&category=all" class="uwa-highlight-btn highlight-btn-disabled">
						<?php esc_html_e('Export All Expired Auctions', 'woo_ua'); ?></a>
				</div>
			</form>

			<script>

				jQuery("document").ready(function($) {

					if (document.getElementById('category_list')) {
						selectedCategory = document.getElementById('category_list').value;
					} else {
						selectedCategory = "all";
					}					

					var selectedFormat = document.getElementById('file_format').value;
					var exportLink = document.getElementById('exportLink');
					var baseUrl = exportLink.getAttribute('href').split('&action=')[0]; 

					// Update the action based on selected format
					if (selectedFormat === 'csv') {
						exportLink.href = baseUrl + '&action=uwa_user_all_expired_download_csv&category=' + selectedCategory;
					} else if (selectedFormat === 'xls') {
						exportLink.href = baseUrl + '&action=uwa_user_all_expired_download_xls&category=' + selectedCategory;
					} else if (selectedFormat === 'pdf') {
						exportLink.href = baseUrl + '&action=uwa_user_all_expired_download_pdf&category=' + selectedCategory;
					}					

				});

				function updateExportLink() {
					if (document.getElementById('category_list')) {
						selectedCategory = document.getElementById('category_list').value;
					} else {
						selectedCategory = "all";
					}					

					var selectedFormat = document.getElementById('file_format').value;
					var exportLink = document.getElementById('exportLink');
					var baseUrl = exportLink.getAttribute('href').split('&action=')[0]; 

					// Update the action based on selected format
					if (selectedFormat === 'csv') {
						exportLink.href = baseUrl + '&action=uwa_user_all_expired_download_csv&category=' + selectedCategory;
					} else if (selectedFormat === 'xls') {
						exportLink.href = baseUrl + '&action=uwa_user_all_expired_download_xls&category=' + selectedCategory;
					} else if (selectedFormat === 'pdf') {
						exportLink.href = baseUrl + '&action=uwa_user_all_expired_download_pdf&category=' + selectedCategory;
					}
				}
			</script>	

		</div>
	</div>

	<?php
}

function uwa_display_checkoutall_button_NEW( $user_id ) {

	global $wpdb, $woocommerce, $product;
	global $sitepress;

	/* $table = $wpdb->prefix . 'woo_ua_auction_log';
	$query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);   
	$my_auctions = $wpdb->get_results( $query ); */

	$my_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT auction_id, MAX(bid) as max_userbid FROM ' . 
		$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d GROUP by auction_id ORDER by date DESC', 
		$user_id));

	$won_bids_products_ids = array();

	if (count($my_auctions ) > 0) {

		foreach ( $my_auctions as $my_auction ) {
		   
		   $product_id =  $my_auction->auction_id;
			
			if (function_exists('icl_object_id') && is_object($sitepress) && 
				method_exists($sitepress, 'get_current_language')) {
			
				$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
					$sitepress->get_current_language());
			}              
		   
			$product = wc_get_product( $product_id );

			if (is_object($product)) {            
			   
				if (method_exists( $product, 'get_type') && $product->get_type() == 'auction') {
				
					/* calculation of won product ids for checkout all button */
					if ( $user_id == $product->get_uwa_auction_current_bider() && 
						$product->get_uwa_auction_expired() == '2' && !$product->get_uwa_auction_payed() ) { 

						$won_bids_products_ids[]= $product->get_id();
					}   

				}  /* end of if method exists  */

			}
			
		} /* end of foreach */ 

		if (count($won_bids_products_ids) > 0) {
			
			$main_url = esc_attr(add_query_arg('pay-uwa-auction', implode(',', 
				$won_bids_products_ids), uwa_auction_get_checkout_url()));
			$main_text = __( 'Check Out All', 'woo_ua' );

			$uwa_url = apply_filters( 'uwa_checkout_all_url', $main_url );
			$uwa_text = apply_filters( 'uwa_checkout_all_text', $main_text );

			return '<a href="' . esc_url($uwa_url) . '" class="button uwa_checkout_all">' . esc_html($uwa_text) . '</a>';
					
		} else {
			return '';
		}
	}
}

function uwa_front_user_change_bid_list( $user_id, $bid_status ) {

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	/* $table = $wpdb->prefix . 'woo_ua_auction_log';
	$query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);   
	$my_auctions = $wpdb->get_results( $query ); */

	$sort_by_title = get_option('uwa_bidsactive_sort_pro_title_asc', 'no');

	if ('yes' != $sort_by_title) {

		$my_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT auction_id, MAX(bid) as max_userbid FROM ' . 
			$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d GROUP by auction_id ORDER by date DESC', 
			$user_id));
	} else {

		$my_auctions = $wpdb->get_results( $wpdb->prepare(
			'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
			$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
			$wpdb->prefix . "posts wposts 
			ON lg.auction_id = wposts.ID 
			WHERE lg.userid = %d 
			AND wposts.post_type = 'product' 
			GROUP BY lg.auction_id 
			ORDER BY wposts.post_title ASC", 
			$user_id
		));
	}

	$active_bids_count = 0;
	$final_active_auctions = array();

	$loader_imgurl = UW_AUCTION_PRO_URL . "assets/images/ajax_loader.gif";

	$disable_pro_img = get_option('uwa_bidsactive_disable_pro_image', 'no');
	$disable_pro_maxbid = get_option('uwa_bidsactive_disable_pro_maxbid', 'no');

	$enable_slientbid_edit = get_option('uwa_bidsactive_enable_slientbid_edit', 'no');		
	$enable_slientbid_delete = get_option('uwa_bidsactive_enable_slientbid_delete', 'no');
	$enable_category_list = get_option('uwa_bidsactive_enable_category_listing', 'no'); 

	if (count($my_auctions) > 0) {

		$aelia_addon = '';
		$addons = uwa_enabled_addons();

		if (is_array($addons) && in_array('uwa_currency_switcher', $addons)) {
				$aelia_addon = true;            
		}

		?> 	

		<span class="uwa_img_ajax_loader" style="">
			<img class='uwa_loaderimg' src="<?php echo $loader_imgurl; ?>" 
				style="visibility: hidden;margin-left:5px;" />
		</span> 


		<table class="shop_table shop_table_responsive tbl_bidauc_list tbl_changebid_list">
			<tr class="bidauc_heading">

				<?php if ('yes' != $disable_pro_img) { ?>
					<th class="toptable h_bidauc_img"><?php esc_html_e( 'Image', 'woo_ua' ); ?></th>
				<?php } ?>
				
					<th class="toptable h_bidauc_name"><?php esc_html_e( 'Product', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_category_list) { ?>
					<th class="toptable h_bidauc_category"><?php esc_html_e( 'Category', 'woo_ua' ); ?></th>
				<?php } ?>

					<th class="toptable h_bidauc_bid"><?php esc_html_e( 'Your bid', 'woo_ua' ); ?></th>
					<th class="toptable h_bidauc_enddate"><?php esc_html_e( 'End date', 'woo_ua' ); ?></th>

				<?php if ('yes' == $enable_slientbid_edit) { ?>					
					<th class="toptable h_bidauc_editbid"></th>
				<?php } ?>

				<?php if ('yes' == $enable_slientbid_delete) { ?>		
					<th class="toptable h_bidauc_delbid"></th>
					<th class="toptable delete_all_bids">
						<a href="javascript:void(0);"><?php esc_html_e( 'Delete All', 'woo_ua' ); ?></a>
						<input type="checkbox" class="uwa_bid_delete_checkbox_all">
					</th>
				<?php } ?>

			</tr>

			<?php	

			/* get selected category and pass values during bid edit */
			if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] > 0) {										
				$selected_category_id = sanitize_text_field($_REQUEST['category_list']);
			} else {
				$selected_category_id = "all";
			}


			/* count for finalize auctions */
			foreach ($my_auctions as $my_auction) {
			   
			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}              
			   
				$product = wc_get_product($product_id); 

				if (is_object($product)) {                 
					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {                        

						if ('change_bid' == $bid_status && $product->get_uwa_auction_expired() == false &&
							'yes' == $product->get_uwa_auction_silent()) { 

								$active_bids_count++;

								if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] != 'all' &&
									$_REQUEST['category_list'] > 0) {		

										$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

										if (in_array($_REQUEST['category_list'], $categories_ids)) {
											$final_active_auctions[] = $product_id;
										}
									
								} else if (isset($_POST['selected_category_id']) && $_POST['selected_category_id'] != 'all' &&
										$_POST['selected_category_id'] > 0) {		

										$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

										if (in_array($_POST['selected_category_id'], $categories_ids)) {
											$final_active_auctions[] = $product_id;
										}
									
								} else {

										$final_active_auctions[] = $product_id;
								}


						} /* end of if - active auctions */

					}  /* end of if method exists  */
				}
				
			} /* end of foreach */ 

			/* display only finalize auctions */
			foreach ($my_auctions as $my_auction) {
			   
			   $product_id = $my_auction->auction_id;
				
				if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {
				
					$product_id = icl_object_id($my_auction->auction_id, 'product', false, 
						$sitepress->get_current_language());
				}              
			   
				$product = wc_get_product( $product_id );

				if (is_object($product)) {                 
					if (method_exists( $product, 'get_type') && $product->get_type() == 'auction') {


						/* ------------------------ For slient active bids  ---------------------- */

						if ('change_bid' == $bid_status && $product->get_uwa_auction_expired() == false &&
							'yes' == $product->get_uwa_auction_silent()) { 

							if (in_array($product_id, $final_active_auctions)) {  

								if (true == $aelia_addon) {
									if ($product->uwa_aelia_is_configure() == true) {                         
										$my_auction->max_userbid = $product->uwa_aelia_base_to_active($my_auction->max_userbid);
									}                       
								}   

								$auc_name = get_the_title( $product_id );
								$auc_url = get_the_permalink( $product_id );
								$auc_img = $product->get_image( 'thumbnail' );

								$auc_bid = wc_price($my_auction->max_userbid);
								$auc_curbid = $product->get_price_html();

								$auc_old_endate = $product->get_uwa_auction_end_dates();
								$uwa_strtotime = strtotime($auc_old_endate);
								$auc_endate = date('d-m-Y H:i:s', $uwa_strtotime);

								$auc_category = '';
								$category_text = '';

								$categories = get_the_terms( $product_id, 'product_cat' );
								if (!empty( $categories ) && !is_wp_error( $categories )) {
									$category_text .= '<ul class="product-categories">';

									// Loop through each category and display its name with a link
									foreach ( $categories as $category ) {
										$category_link = get_term_link( $category->term_id, 'product_cat' );
										$category_text .= '<li><a href="' . esc_url( $category_link ) . '">' . 
											esc_html( $category->name ) . '</a></li>';
									}
									$category_text .= '</ul>';
								}

								$auc_category = $category_text;

								$log_id = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . 
									$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d and auction_id = %d and bid = %f', 
									$user_id, $product_id, $my_auction->max_userbid));
								

								/* ------------------- */

								$bid_placed = $my_auction->max_userbid;
								$bid_placed = round($bid_placed, wc_get_price_decimals());
								$bid_placed = number_format($bid_placed, wc_get_price_decimals(), '.', '');

								$i_edit_value = "edit_bid_value_". $product_id;	
								$h_placebid = "placed_bid_". $product_id;


								?>
									<tr class="bidauc_active_message bid_acution_<?php echo $product_id; ?>">
										<th class="bid-message" colspan="30" style="text-align: center;"></th>
									</tr>
									<tr class="bidauc_active bid_acution_data_<?php echo $product_id; ?>"> 
										
										<?php if ('yes' != $disable_pro_img) { ?>
											<td class="bidauc_img"><?php echo wp_kses_post($auc_img); ?></td>
										<?php } ?>

											<td class="bidauc_name"><a href="<?php echo esc_url($auc_url); ?>">
												<?php echo esc_html($auc_name); ?></a></td>

										<?php if ('yes' == $enable_category_list) { ?>
											<td class="bidauc_category"><?php echo wp_kses_post($auc_category); ?></td>
										<?php } ?>												

											<td class="bidauc_bid">

												<span class="uwa_first" data-postid="<?php echo $product_id; ?>">
													<?php echo wp_kses_post($auc_bid); ?>													
												</span>

												<!-- <span class="uwa_second" data-postid="<?php echo $product_id; ?>">
																									
																												
												</span>	-->

											</td>

											<td class="bidauc_enddate"><?php echo esc_html($auc_endate); ?></td>

											<?php if ('yes' == $enable_slientbid_edit) {

													$btn_update_id = "uwa_update_edit_bid_". $product_id;
													$btn_cancel_id = "uwa_cancel_edit_bid_". $product_id;
													$a_edit_id = "uwa_action_front_edit_bid_". $product_id;

											?>
											<td class="bidauc_edit">

												<form id="" class="bid-edit-form" method="post" enctype='multipart/form-data' data-product_id="">

													<span class="uwa_first" data-postid="<?php echo $product_id; ?>">
															<a class="action_front_edit" id="<?php echo $a_edit_id; ?>" href="javascript:void(0)"
																data-postid="<?php echo $product_id; ?>">
																<?php echo esc_html__( 'Edit', 'woo_ua' ); ?></a>
													</span>

													<span class="uwa_second" data-postid="<?php echo $product_id; ?>">
															<input type="number" 
																name="edit_bid_value" 
																id="<?php echo $i_edit_value; ?>"
																data-postid="<?php echo esc_attr( $product_id ); ?>"
																value="<?php echo $bid_placed; ?>"
																min="1" 
																step="any" size="<?php echo strlen($bid_placed)+2 ?>" 
																title="<?php echo esc_html__( 'bid', 'woo_ua' ); ?>"  
																class="input-text qty bid text left">	

															<input type="hidden" 
																name="placed_bid" 
																id="<?php echo $h_placebid; ?>" 
																value="<?php echo esc_attr( $bid_placed ); ?>" 
																data-postid="<?php echo $product_id; ?>"/>

															<input type="hidden" 
																name="auctionid" 														
																value="<?php echo $product_id; ?>" />

															<input type="hidden" 
																name="logid" 														
																value="<?php echo $log_id; ?>" />	

															<input type="hidden" 
																name="selected_category_id" 														
																value="<?php echo $selected_category_id; ?>" />	

															<button type="submit" class="update_edit_bid" 
																id="<?php echo $btn_update_id; ?>"
																data-postid="<?php echo $product_id; ?>">
																<?php echo esc_html__( 'Update Bid', 'woo_ua' ); ?>
															</button>

															<button type="button" class="cancel_edit_bid" 
																id="<?php echo $btn_cancel_id; ?>"
																data-postid="<?php echo $product_id; ?>">
																<?php echo esc_html__( 'Cancel', 'woo_ua' ); ?>
															</button>	
													</span>

												</form>	
											</td>
										<?php } ?>

										<?php if ('yes' == $enable_slientbid_delete) { ?>	
											<td class="bid_action">				
												<span class="uwa_link_del"> 	
													<a class="action_front_delete" id='' href='javascript:void(0)' 
													data-id='<?php echo $log_id;?>' 
													data-postid='<?php echo $product_id;?>' ><?php echo __('Delete', 'woo_ua');?></a> 
												</span>
											</td>
											<td class="bid_action">
										    	<input type="checkbox" class="uwa_bid_delete_checkbox" 
										           data-id="<?php echo esc_attr($log_id); ?>" 
										           data-postid="<?php echo esc_attr($product_id); ?>">
											</td>
										<?php } ?>

									</tr> 
								
									  <?php		

							} /* end of if - in array */				

						}

					}  /* end of if method exists  */

				}
				
			} /* end of foreach */ 

			if ('change_bid' == $bid_status && 0 == $active_bids_count) {
				?>

				<tr class="bidauc_msg"><td colspan="8">
					<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">	<?php esc_html_e( 'No bids available yet.', 'woo_ua' ); ?></div></td>
				</tr>
				
				<?php
			}
			?>
		</table> 

	<?php


	} else {
		$shop_page_id = wc_get_page_id( 'shop' );   
		$shop_page_url = $shop_page_id ? get_permalink( $shop_page_id ) : '';
		?>
			<div class="woocommerce-message woocommerce-message--info woocommerce-Message 
				woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="<?php echo esc_url($shop_page_url); ?>">
					<?php esc_html_e( 'Go shop' , 'woo_ua' ); ?></a> 
				<?php esc_html_e( 'No bids available yet.' , 'woo_ua' ); ?>
			</div>
					 
	<?php
	} /* end of else */	

}

function uwa_front_user_export_all_silent_auctions_bid_offline( $user_id, $bid_status ) {

	global $wpdb, $woocommerce;
	$auction_args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => '-1',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'auction',
			),
		),
		'orderby'        => 'title',
		'order'          => 'ASC',
		'auction_arhive' => true,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'woo_ua_auction_closed',
				'value'   => array('1', '2', '3', '4'),
				'compare' => 'IN',
			),
		),
	);
	
	$query = new WP_Query($auction_args);
	
	$silent_auctions2 = array();
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$silent_auctions2[] = get_the_ID(); 
		}
		wp_reset_postdata();
	}
			
    // Get silent auctions
    $current_user_id = get_current_user_id(); // Get the current logged-in user ID

    $silent_auctions_results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            pm.post_id AS auction_id,
            MAX(al.bid) AS max_userbid,
            MAX(CASE WHEN al.userid = %d THEN al.bid ELSE NULL END) AS your_bids,
            p.post_title
        FROM {$wpdb->postmeta} AS pm
        JOIN {$wpdb->posts} AS p ON pm.post_id = p.ID
        LEFT JOIN {$wpdb->prefix}woo_ua_auction_log AS al ON pm.post_id = al.auction_id
        WHERE pm.meta_key = 'uwa_auction_silent'
        AND pm.meta_value = 'yes'
        GROUP BY pm.post_id, p.post_title
    ", $current_user_id));

    // Filter out auctions that are in $silent_auctions2
    $filtered_silent_auctions = array_filter($silent_auctions_results, function ($auction) use ($silent_auctions2) {
        return !in_array($auction->auction_id, $silent_auctions2);
    });
 
    // Re-index array to maintain structure
    $filtered_silent_auctions = array_values($filtered_silent_auctions);

	$auction_offline_categories = []; // Store unique categories
	foreach ($filtered_silent_auctions as $auction) {
		$product = wc_get_product($auction->auction_id);

		if ($product) {
			$categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'id=>name']); // Get ID=>Name pairs

			foreach ($categories as $cat_id => $cat_name) {
				// Store unique categories directly
				if (!array_key_exists($cat_id, $auction_offline_categories)) {
					$auction_offline_categories[$cat_id] = [
						'category_id' => $cat_id,
						'category_name' => $cat_name
					];
				}
			}
		}
	}

	// // Print the final unique category array
	// echo "<pre>";
	// print_r(array_values($auction_offline_categories)); // Re-indexing for clean output
	// echo "</pre>";
	
	$loader_imgurl = UW_AUCTION_PRO_URL . "assets/images/ajax_loader.gif";
	$disable_pro_img = get_option('uwa_bidsactive_disable_pro_image', 'no');
	$enable_category_list = get_option('uwa_bidsactive_enable_category_listing', 'no'); 
	
		?>
		<style>
		.bid-offline-info {
			background: #f9f9f9;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
			font-size: 14px;
		}
		.bid-offline-info ol {
			padding-left: 20px;
		}
		.bid-offline-info ol li {
			margin-bottom: 5px;
		}
		</style>

		<form id="uwa_frm_file_format" class="uwa_cat uwa_frm_bid_offline" method="post">

			<?php
			$enable_category_list = get_option('uwa_bid_offline_disable_category_filter', 'no');
			if ('yes' == $enable_category_list) {
				?>
				<select name="bidoffline_category_list" class="bid-offline-category" id="bidoffline_category_list" onchange="updateBidofflineExportLink()">
					<option value="all">
						<?php echo esc_attr__( 'All Categories', 'woo_ua' ); ?>
					</option>
					<?php foreach ($auction_offline_categories as $category) { ?>
						<option value="<?php echo esc_attr($category['category_id']); ?>">
							<?php echo esc_html($category['category_name']); ?>
						</option>
					<?php } ?>
				</select>
				<?php	
			} /* end of if category */
			?>

			<div class="bid-exp-list">	
				<a id="exportLink" style="border:1px solid #2271b1;" href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>uwa-auctions/?display=import-bids-silent-auction&action=uwa_bid_offline_download_xlsx&category=all" class="uwa-highlight-btn highlight-btn-disabled">
					<?php esc_html_e('Export Slient Auctions', 'woo_ua'); ?></a>
			</div>
		</form>

		<form id="uwa-bid-offline-upload-form" enctype="multipart/form-data">
			<div class="card">
				<div class="drop_box">
					<h4><?php _e('Select File here', 'woo_ua'); ?></h4>
					<p><?php _e('Files Supported: XLSX', 'woo_ua'); ?></p>
					<input type="file" name="import_file" accept=".xlsx" id="fileInput" style="display: none;">
					<label for="fileInput" class="btn"><?php _e('Choose File', 'woo_ua'); ?></label>
					<span id="selected-file"><?php _e('No file selected', 'woo_ua'); ?></span>
				</div>
				<button type="submit" class="btn upload-btn"><?php _e('Import Silent Auctions', 'woo_ua'); ?></button>
			</div>
		</form>

		<div id="upload-response">
			<div class="status-message">
			</div>
		</div>

		<?php 
		// Fetch messages
		$upload_messages = get_transient('uwa_bid_upload_message');

		if ($upload_messages) {
			if (!empty($upload_messages['errors'])) {
				foreach ($upload_messages['errors'] as $error) {
					echo "<div class='error'><p>{$error}</p></div>";
				}
			}

			if (!empty($upload_messages['messages'])) {
				foreach ($upload_messages['messages'] as $message) {
					echo "<div class='updated'><p>{$message}</p></div>";
				}
			}

			delete_transient('uwa_bid_upload_message'); // Clear message after showing
		}	
		?>

		<script>
			// Update export link
			jQuery(document).ready(function($) {
				window.updateBidofflineExportLink = function() {
					var selectedCategory = document.getElementById('bidoffline_category_list') 
						? document.getElementById('bidoffline_category_list').value 
						: "all";

					var exportLink = document.getElementById('exportLink');
					var baseUrl = exportLink.getAttribute('href').split('&action=')[0];

					// Update export link to XLSX
					exportLink.href = baseUrl + '&action=uwa_bid_offline_download_xlsx&category=' + selectedCategory;
				};

				// Run on page load
				updateBidofflineExportLink();
			});

			// Display select file name in mport your bids in silent auction
			document.addEventListener("DOMContentLoaded", function () {
				const fileInput = document.getElementById("fileInput");
				const selectedFile = document.getElementById("selected-file");

				fileInput.addEventListener("change", function () {
					if (fileInput.files.length > 0) {
						selectedFile.textContent = fileInput.files[0].name;
					} else {
						selectedFile.textContent = "<?php _e('No file selected', 'woo_ua'); ?>";
					}
				});
			});
		</script>

		<div class="bid-offline-info">
			<p><b><?php esc_html_e('Place your bids in silent auction by uploading an XLSX file.', 'woo_ua'); ?></b></p>
			<ol>
				<li><?php esc_html_e('Export the Silent Live Auctions using the Export Silent Auctions button', 'woo_ua'); ?></li>
				<li><?php esc_html_e('Open the exported XLSX file and enter your bid details in the "Enter Bid" column.', 'woo_ua'); ?></li>
				<li><?php esc_html_e('Once you have entered your bids, upload the XLSX file and submit it.', 'woo_ua'); ?></li>
			</ol>
			<p>
				<b><?php esc_html_e('Note: (Ensure the file format is correct before submission to avoid errors.)', 'woo_ua'); ?></b>
			</p>
		</div>
	<?php
		
}

function uwa_isEuropeanNumber($text) {
    return preg_match(
        '/^-?\d+(\.\d{3})*(,\d+)?$/',
        trim($text)
    ) === 1;
}

function uwa_normalizeNumber($value) {

    // keep digits, comma, dot, minus
    $value = preg_replace('/[^0-9,.\-]/', '', $value);

    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        // last separator is decimal
        if (strrpos($value, ',') > strrpos($value, '.')) {
            // European format
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // US format
            $value = str_replace(',', '', $value);
        }
    } elseif (strpos($value, ',') !== false) {
        // only comma → decimal
        $value = str_replace(',', '.', $value);
    }

    return (float) $value;
}