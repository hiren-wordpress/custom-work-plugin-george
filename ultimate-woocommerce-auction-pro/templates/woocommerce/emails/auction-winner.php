<?php

/**
 * Send Email to bidder when the bidder won the auction. (HTML)
 * 
 * @package Ultimate WooCommerce Auction PRO
 * @author Nitesh Singh 
 * @since 1.0  
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php

/* ---------- LIVE & PREVIEW MODE SUPPORT ---------- */
global $wpdb;

$product          = null;
$product_id       = 0;
$args             = array( 'currency' => get_woocommerce_currency() );
$user_name        = '';
$auction_title    = '';
$auction_bid_value= '';
$thumb_image      = '';
$checkout_url     = '';
$user_type        = '';
$winner_id        = 0;
$auction_owner_name = '';
$auction_owner_id   = 0;

/* ---------- LIVE EMAIL (array data) ---------- */
if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {

	$product_id = absint( $email->object['product_id'] );

	/* Use auction helper if available */
	if ( function_exists( 'Woo_UA' ) && method_exists( Woo_UA(), 'uwa_get_product' ) ) {
		$product = Woo_UA()->uwa_get_product( $product_id );
	} else {
		$product = wc_get_product( $product_id );
	}

	/* Currency */
	$product_base_currency = method_exists( $product, 'uwa_aelia_get_base_currency' )
		? $product->uwa_aelia_get_base_currency()
		: get_woocommerce_currency();
	$args = array( 'currency' => $product_base_currency );

	/* Core values */
	$user_name            = esc_html( $email->object['user_name'] );
	$auction_title        = $product->get_title();
	$auction_bid_value    = wc_price( $product->get_uwa_current_bid(), $args );
	$thumb_image          = $product->get_image( 'thumbnail' );
	$user_type            = $email->object['user_type'];
	$winner_id            = absint( $email->object['user_id'] );
	$auction_owner_name   = esc_html( $email->object['owner_name'] );
	$auction_owner_id     = absint( $email->object['owner_id'] );
	$checkout_url         = add_query_arg( array( 'pay-uwa-auction' => $product_id ), uwa_auction_get_checkout_url() );

	/* Auto‑order payment URL replacement */
	if ( get_option( 'uwa_auto_order_enable' ) === 'yes' ) {
		$uwa_order_id = get_post_meta( $product_id, 'woo_ua_order_id', true );
		if ( $uwa_order_id ) {
			$order = wc_get_order( $uwa_order_id );
			if ( $order ) {
				$checkout_url = $order->get_checkout_payment_url();
			}
		}
	}

/* ---------- PREVIEW MODE (WC_Order) ---------- */
} else {

	$order  = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item   = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	/* Currency remains shop default */
	$args = array( 'currency' => get_woocommerce_currency() );

	/* Core fallback values */
	$user_name         = $order ? $order->get_formatted_billing_full_name() : '';
	$auction_title     = $product ? $product->get_title() : __( 'Auction Product', 'woo_ua' );
	$auction_bid_value = wc_price( $item ? $item->get_total() : 0, $args );
	$thumb_image       = $product ? $product->get_image( 'thumbnail' ) : '';
	$user_type         = 'bidder';
	$checkout_url      = home_url(); // simple preview URL
	$winner_id         = $order ? $order->get_user_id() : 0;
	$auction_owner_name= '';
	$auction_owner_id  = 1;
}
  
?>

<?php if($user_type === "bidder"){      /* buyer */


    if ( is_array( $email->object ) && isset( $email->object['url_product'] ) ) {
      $auction_url = $email->object['url_product'];
    } else {
      $product_id  = isset( $email->object ) && method_exists( $email->object, 'get_id' ) ? $email->object->get_id() : 0;
      $auction_url = $product_id ? get_permalink( $product_id ) : home_url();
    }


    ?>

    <p><?php printf( __( "Hi %s,", 'woo_ua' ), $user_name); ?></p>
    <p><?php printf( __( 'Congratulations! You are the winner! of the auction product <a href="%s">%s</a>.', 'woo_ua' ),$auction_url,$auction_title); ?></p>
    <p><?php printf( __( "Here are the details : ", 'woo_ua' )); ?></p>

    <table>
        <tr>
          <td><?php echo __( 'Image', 'woo_ua' ); ?></td>
          <td><?php echo __( 'Product', 'woo_ua' ); ?></td>
          <td><?php echo __( 'Winning bid', 'woo_ua' ); ?></td> 
        </tr>
        <tr>
          <td><?php echo $thumb_image;?></td>
          <td><a href="<?php echo $auction_url;?>"><?php echo $auction_title; ?>
            </a></td>
          <td><?php echo $auction_bid_value; ?></td>
        </tr>
    </table>

    <?php

    /* -----------   when offline_dealing addon is active   ------------- */

      $addons = uwa_enabled_addons();
      if(is_array($addons) && in_array('uwa_offline_dealing_addon', $addons)){

            /* 1. pay now button */

            // buyers and stripe both deactive
            if(!in_array('uwa_buyers_premium_addon', $addons) && 
              !in_array('uwa_stripe_auto_debit_addon', $addons)){
              //echo "in 1"; no need to display button 

            } // buyers active only
            elseif(in_array('uwa_buyers_premium_addon', $addons) && 
              !in_array('uwa_stripe_auto_debit_addon', $addons)){
              //echo "in 2";  

              $button_text = __("Pay Buyer's Premium", 'woo_ua');

              do_action( 'woocommerce_email_before_pay_button', $product); 
              
                ?>

              <div>
                <p><?php _e( 'Please, proceed to checkout', 'woo_ua' ); ?></p>
                <p><a style="padding:6px 28px !important;font-size: 12px !important; background: #ccc !important; color: #333 !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo $checkout_url; ?>" 
                  class="button">
                  <?php echo $button_text; ?></a>
                </p>
              </div>

              <?php

            }  // buyers and stripe both active
            elseif(in_array('uwa_buyers_premium_addon', $addons) && 
              in_array('uwa_stripe_auto_debit_addon', $addons)){
              //echo "in 3";
                ?>
                  <!-- <p><?php _e( "Buyer's premium for auction product is auto debited via stripe", 'woo_ua' ); ?></p> --> 
                <?php
            }


            /* 2.  display SELLER CONTACT details */  
            /*  ------------------------------------------------  */

              $addons = uwa_enabled_addons();
              if(is_array($addons) && in_array('uwa_offline_dealing_addon', $addons)){

                  $display_contact_detail = get_option(
                    "offline_dealing_display_in_mail", "no");

                  if($display_contact_detail == "yes"){
                      echo "<br>";
                      _e('Seller contact details :', 'woo_ua');

                      $user_meta = get_userdata($auction_owner_id);
                      $user_roles = $user_meta->roles;

                      // when seller has store vendor role 
                      if (in_array( 'wcfm_vendor', $user_roles, true )) {

                        // display vendor's store address - seller auction 
                        $vendor_profile = get_user_meta($auction_owner_id, 
                          "wcfmmp_profile_settings");

                        if(is_array($vendor_profile)){

                              if(isset($vendor_profile[0])){

                                  $vdata = $vendor_profile[0];

                                  $store_name = "";
                                  $store_email = "";
                                  $add_1 = "";
                                  $add_2 = "";
                                  $city = "";
                                  $country = "";
                                  $postcode = "";
                                  $phone = "";

                                  if(isset($vdata['store_name']))
                                     $store_name = $vdata['store_name'];

                                  if(isset($vdata['store_email']))
                                     $store_email = $vdata['store_email'];

                                  if(isset($vdata['address']['street_1']))
                                     $add_1 = $vdata['address']['street_1'];

                                  if(isset($vdata['address']['street_2']))
                                     $add_2 = $vdata['address']['street_2'];   

                                  if(isset($vdata['address']['city']))
                                     $city = $vdata['address']['city'];

                                  if(isset($vdata['address']['country']))
                                     $country = $vdata['address']['country']; 

                                  if(isset($vdata['address']['zip']))
                                     $postcode = $vdata['address']['zip'];

                                  if(isset($vdata['phone']))
                                     $phone = $vdata['phone'];                                       
                                   
                               
                                  $display_contact = "<br><p><strong>" .
                                     __('Store Address:', 'woo_ua');

                                  $display_contact .= "</strong><br>". $store_name . "<br>";

                                  if($add_1)
                                     $display_contact .= $add_1 . "<br>";
                                  if($add_2)
                                     $display_contact .= $add_2 . "<br>";
                                  if($city)
                                     $display_contact .= $city . "<br>";
                                  if($postcode)
                                     $display_contact .= $postcode . "<br>";
                                  if($country)
                                     $display_contact .= $country . "<br>";            

                                  echo $display_contact;

                                  if($phone){
                                     _e('Contact : ', 'woo_ua');
                                     echo $phone;
                                  }
                                     echo "<br>";

                                  if($store_email){
                                     _e('Email : ', 'woo_ua');
                                     echo $store_email;
                                  }

                              } /* end of if - isset vendor profile */
                    
                        } /* end of if - isarray */

                      } 
                      else{
                            /* for general */
                            $customer_id = $auction_owner_id;
                            //get_billing_details_html($customer_id);

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
                            if($cntry_code){
                              $country = WC()->countries->countries[$cntry_code].
                              "  ($cntry_code)";
                            }
                            
                            echo "<p>";
                            
                            if($first_name || $last_name){    
                              $display_contact = $first_name ." " .$last_name . "<br>";
                            } 
                            if($add_1)
                              $display_contact .= $add_1 . "<br>";
                            if($add_2)
                              $display_contact .= $add_2 . "<br>";
                            if($city)
                              $display_contact .= $city . "<br>";
                            if($postcode)
                              $display_contact .= $postcode . "<br>";
                            if($country)
                              $display_contact .= $country . "<br><br>";    
                            echo $display_contact;
                            if($phone){
                              _e('Contact : ', 'woo_ua');
                              echo $phone;
                            }
                            echo "<br>";
                            if($email){
                              _e('Email : ', 'woo_ua');
                              echo $email;
                            }
                            
                            echo "</p>";
                            
                      } /* end of else */

                  } /* end of if  - contact detail */

              } /* end of if  - addons */

            /*  ------------------------------------------------  */

      }
      else{

            /* set button text */
           
            $w_current_userid = method_exists( $product, 'get_uwa_auction_current_bider' ) ? $product->get_uwa_auction_current_bider() : 0;
            $w_product_price  = method_exists( $product, 'get_uwa_auction_current_bid' ) ? $product->get_uwa_auction_current_bid() : 0;
            $w_product_id     = $product ? $product->get_id() : 0;

            $get_charged_for_winner = get_option(
                "_uwa_w_s_charge_{$w_product_id}_{$w_current_userid}", false
            );

            if ( $get_charged_for_winner == $w_product_price ) {
                $button_text = __( 'Get Item', 'woo_ua' );
            } else {
                $button_text = __( 'Pay Now', 'woo_ua' );
            }


            do_action( 'woocommerce_email_before_pay_button', $product); 

            ?>

            <div>
              <p><?php _e( 'Please, proceed to checkout', 'woo_ua' ); ?></p>
              <p><a style="padding:6px 28px !important;font-size: 12px !important; background: #ccc !important; color: #333 !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo $checkout_url; ?>" 
                class="button">
                <?php echo $button_text; ?></a>
              </p>
            </div>

          <?php

      } /* end of else - addon */

    ?>

<?php } ?>




<?php do_action( 'woocommerce_email_footer', $email ); ?>