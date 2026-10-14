<?php

/**
 * Bidder placed a bid email notification (plain)
 * 
 * @package Ultimate WooCommerce Auction PRO
 * @author Nitesh Singh 
 * @since 1.0  
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

global $woocommerce;

$product       = null;
$product_id    = 0;
$product_base_currency = get_woocommerce_currency();
$args          = array("currency" => $product_base_currency);
$auction_bid_value = '';
$thumb_image   = '';
$user_name     = '';
$auction_title = '';
$user_type     = '';
$winner_id     = 0;
$auction_owner_name = '';
$auction_owner_id   = '';

if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {
  $product_id = $email->object['product_id']; 
  $product = wc_get_product($product_id);  

  if ( method_exists( $product, 'uwa_aelia_get_base_currency' ) ) {
    $product_base_currency = $product->uwa_aelia_get_base_currency(); 
    $args = array("currency" => $product_base_currency);
  }

  $user_name = $email->object['user_name'];
  $auction_title = $product->get_title();
  if ( method_exists( $product, 'get_uwa_current_bid' ) ) {
    $auction_bid_value = wc_price($product->get_uwa_current_bid(), $args);
  } else {
    $auction_bid_value = wc_price(0, $args); // fallback
  }
  $thumb_image = $product->get_image( 'thumbnail' );
  /*$checkout_url  = add_query_arg( array( 'pay-uwa-auction' => $product_id  ), uwa_auction_get_checkout_url() );*/
  $user_type = $email->object['user_type'];
  $winner_id = $email->object['user_id'];
  $auction_owner_name = $email->object['owner_name']; 
  $auction_owner_id = $email->object['owner_id'];
}

echo $email_heading . "</br><Br>";	

if($user_type === "admin") {  
  $auction_url = isset( $email->object['edit_url_product'] ) ? esc_url( $email->object['edit_url_product'] ) : admin_url( 'edit.php?post_type=product' ); // 
  $userlink = add_query_arg( 'user_id', $email->object['user_id'], admin_url( 'user-edit.php' ) );

  printf( __( "Hi Admin,", 'woo_ua' ));
  echo "</br>";
  printf( __( "The auction has expired and won by user. Auction url <a href='%s'>%s</a>.", 'woo_ua' ),$auction_url, $auction_title);
  echo "</br>";
  printf( __( "<a href='%s'>%s</a>.", 'woo_ua' ), $userlink, $user_name);
  echo "</br><br>";
  printf( __( "Winning bid %s.", 'woo_ua' ), $auction_bid_value);
  echo "</br>";
}

if($user_type === "auction_owner") { /* seller */
  $auction_url = $email->object['url_product'];
  $userlink = add_query_arg( 'user_id', $email->object['user_id'], admin_url( 'user-edit.php' ) );

  printf( __( "Hi %s,", 'woo_ua' ), $auction_owner_name);
  echo "</br>";
  printf( __( "The auction has expired and won by user. Auction url <a href='%s'>%s</a>.", 'woo_ua' ), $auction_url, $auction_title);
  echo "</br>";
  printf( __( "<a href='%s'>%s</a>.", 'woo_ua' ), $userlink, $user_name);
  echo "</br><br>";
  printf( __( "Winning bid %s.", 'woo_ua' ), $auction_bid_value);
  echo "</br>";

  /* Display Winner Contact Details */
  $addons = function_exists('uwa_enabled_addons') ? uwa_enabled_addons() : array();
  if ( is_array($addons) && in_array('uwa_offline_dealing_addon', $addons) ) {
    $display_contact_detail = get_option("offline_dealing_display_in_mail", "no");
    if ($display_contact_detail === "yes") {
      echo "<br>";
      _e('Winner contact details :', 'woo_ua');

      $customer_id = $winner_id;

      $display_contact = "";
      $country = "";
      $first_name = get_user_meta( $customer_id, 'billing_first_name', true );
      $last_name = get_user_meta( $customer_id, 'billing_last_name', true );
      $email = get_user_meta( $customer_id, 'billing_email', true );
      $add_1 = get_user_meta( $customer_id, 'billing_address_1', true );
      $add_2 = get_user_meta( $customer_id, 'billing_address_2', true );
      $city = get_user_meta( $customer_id, 'billing_city', true );
      $postcode = get_user_meta( $customer_id, 'billing_postcode', true );
      $phone = get_user_meta( $customer_id, 'billing_phone', true );
      $cntry_code = get_user_meta( $customer_id, 'billing_country', true );
      if($cntry_code && isset(WC()->countries->countries[$cntry_code])){
        $country = WC()->countries->countries[$cntry_code]." ($cntry_code)";
      }

      echo "<p>";
      if($first_name || $last_name){    
        $display_contact = $first_name ." " .$last_name . "<br>";
      } 
      if($add_1)      $display_contact .= $add_1 . "<br>";
      if($add_2)      $display_contact .= $add_2 . "<br>";
      if($city)       $display_contact .= $city . "<br>";
      if($postcode)   $display_contact .= $postcode . "<br>";
      if($country)    $display_contact .= $country . "<br><br>";    
      echo $display_contact;

      if($phone){
        _e('Contact : ', 'woo_ua');
        echo $phone . "<br>";
      }
      if($email){
        _e('Email : ', 'woo_ua');
        echo $email;
      }
      echo "</p>";
    }
  }
}

	echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );