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

$object = isset( $email->object ) ? $email->object : array();

if ( is_array( $object ) && isset( $object['product_id'] ) ) {   // LIVE
	$product_id = absint( $object['product_id'] );
	$product    = wc_get_product( $product_id );
	$product_base_currency = method_exists( $product, 'uwa_aelia_get_base_currency' )
		? $product->uwa_aelia_get_base_currency()
		: get_woocommerce_currency();
	$args = array( 'currency' => $product_base_currency );

	$user_name          = $object['user_name']        ?? '';
	$auction_title      = $product ? $product->get_title() : '';
	$auction_bid_value  = $product ? wc_price( $product->get_uwa_current_bid(), $args ) : '';
	$thumb_image        = $product ? $product->get_image( 'thumbnail' ) : '';
	$checkout_url       = add_query_arg( array( 'pay-uwa-auction' => $product_id ), uwa_auction_get_checkout_url() );
	$user_type          = $object['user_type']        ?? '';
	$winner_id          = absint( $object['user_id']  ?? 0 );
	$auction_owner_name = $object['owner_name']       ?? '';
	$auction_owner_id   = absint( $object['owner_id'] ?? 0 );

	/* auto‑order → override checkout link */
	if ( get_option( 'uwa_auto_order_enable' ) === 'yes' && $product ) {
		$uwa_order_id = get_post_meta( $product_id, 'woo_ua_order_id', true );
		if ( $uwa_order_id ) {
			$order = wc_get_order( $uwa_order_id );
			if ( $order ) {
				$checkout_url = $order->get_checkout_payment_url();
			}
		}
	}

} else {                                                          // PREVIEW
	$order   = is_a( $object, 'WC_Order' ) ? $object : null;
	$item    = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	$args               = array( 'currency' => get_woocommerce_currency() );
	$user_name          = $order ? $order->get_formatted_billing_full_name() : '';
	$auction_title      = $product ? $product->get_title() : __( 'Auction Product', 'woo_ua' );
	$auction_bid_value  = wc_price( $item ? $item->get_total() : 0, $args );
	$thumb_image        = $product ? $product->get_image( 'thumbnail' ) : '';
	$checkout_url       = home_url();
	$user_type          = 'bidder';
	$winner_id          = $order ? $order->get_user_id() : 0;
	$auction_owner_name = '';
	$auction_owner_id   = 1;
}

/* ------------------------------------------------------------------
   AUTO‑GENERATED e‑mail content (unchanged except for safe‑escapes)
-------------------------------------------------------------------*/

echo $email_heading . "</br><br>";

if ( $user_type === 'bidder' ) {          /* buyer */

	$auction_url = is_array( $object ) && isset( $object['url_product'] )
		? $object['url_product']
		: ( $product_id ? get_permalink( $product_id ) : home_url() );

	$userlink = add_query_arg( 'user_id', $winner_id, admin_url( 'user-edit.php' ) );

	printf( __( 'Hi %s,', 'woo_ua' ), esc_html( $user_name ) );
	echo "</br>";
	printf(
		__( 'Congratulations! You are the winner! of the auction product <a href="%s">%s</a>.', 'woo_ua' ),
		esc_url( $auction_url ),
		esc_html( $auction_title )
	);
	echo "</br>";
	printf( __( 'Winning bid %s.', 'woo_ua' ), $auction_bid_value );
	echo "</br>";

	/* -------- Offline‑Dealing add‑on logic (unchanged) -------- */
	$addons = function_exists( 'uwa_enabled_addons' ) ? uwa_enabled_addons() : array();

	if ( is_array( $addons ) && in_array( 'uwa_offline_dealing_addon', $addons, true ) ) {

		/* 1. Pay‑Now / Buyer's‑Premium buttons ------------------ */
		if ( in_array( 'uwa_buyers_premium_addon', $addons, true )
		     && ! in_array( 'uwa_stripe_auto_debit_addon', $addons, true ) ) {

			$button_text = __( "Pay Buyer's Premium", 'woo_ua' );
			do_action( 'woocommerce_email_before_pay_button', $product );

			printf(
				__( 'Please, proceed to checkout, <a href="%s">%s</a>.', 'woo_ua' ),
				esc_url( $checkout_url ),
				esc_html( $button_text )
			);
			echo "</br>";
		}

		/* 2. Seller contact block ------------------------------- */
		if ( get_option( 'offline_dealing_display_in_mail', 'no' ) === 'yes' ) {

			echo "<br>";
			_e( 'Seller contact details :', 'woo_ua' );

			$user_meta  = get_userdata( $auction_owner_id );
			$user_roles = is_object( $user_meta ) ? $user_meta->roles : array();

			if ( in_array( 'wcfm_vendor', $user_roles, true ) ) {
				// vendor store address (kept intact)
				$vendor_profile = get_user_meta( $auction_owner_id, 'wcfmmp_profile_settings', true );
				if ( is_array( $vendor_profile ) ) {
					$v = $vendor_profile;
					$store_name  = $v['store_name']  ?? '';
					$store_email = $v['store_email'] ?? '';
					$addr        = $v['address']      ?? array();
					$display_contact  = "<br><p><strong>" . __( 'Store Address:', 'woo_ua' ) . "</strong><br>";
					$display_contact .= $store_name . "<br>";
					$display_contact .= $addr['street_1'] ?? '';  if ( ! empty( $addr['street_1'] ) ) $display_contact .= "<br>";
					$display_contact .= $addr['street_2'] ?? '';  if ( ! empty( $addr['street_2'] ) ) $display_contact .= "<br>";
					$display_contact .= $addr['city']     ?? '';  if ( ! empty( $addr['city']     ) ) $display_contact .= "<br>";
					$display_contact .= $addr['zip']      ?? '';  if ( ! empty( $addr['zip']      ) ) $display_contact .= "<br>";
					$display_contact .= $addr['country']  ?? '';  if ( ! empty( $addr['country']  ) ) $display_contact .= "<br>";
					echo $display_contact;

					if ( ! empty( $v['phone'] ) ) {
						_e( 'Contact : ', 'woo_ua' );
						echo esc_html( $v['phone'] ) . "<br>";
					}
					if ( $store_email ) {
						_e( 'Email : ', 'woo_ua' );
						echo esc_html( $store_email );
					}
					echo "</p>";
				}

			} else {
				// general seller address
				$first  = get_user_meta( $auction_owner_id, 'billing_first_name', true );
				$last   = get_user_meta( $auction_owner_id, 'billing_last_name',  true );
				$email  = get_user_meta( $auction_owner_id, 'billing_email',      true );
				$addr1  = get_user_meta( $auction_owner_id, 'billing_address_1',  true );
				$addr2  = get_user_meta( $auction_owner_id, 'billing_address_2',  true );
				$city   = get_user_meta( $auction_owner_id, 'billing_city',       true );
				$post   = get_user_meta( $auction_owner_id, 'billing_postcode',   true );
				$phone  = get_user_meta( $auction_owner_id, 'billing_phone',      true );
				$code   = get_user_meta( $auction_owner_id, 'billing_country',    true );
				$country= $code && isset( WC()->countries->countries[ $code ] )
					? WC()->countries->countries[ $code ] . " ($code)" : '';

				echo "<p>";
				if ( $first || $last ) echo esc_html( "$first $last" ) . "<br>";
				if ( $addr1 ) echo esc_html( $addr1 ) . "<br>";
				if ( $addr2 ) echo esc_html( $addr2 ) . "<br>";
				if ( $city  ) echo esc_html( $city  ) . "<br>";
				if ( $post  ) echo esc_html( $post  ) . "<br>";
				if ( $country ) echo esc_html( $country ) . "<br><br>";

				if ( $phone ) {
					_e( 'Contact : ', 'woo_ua' );
					echo esc_html( $phone ) . "<br>";
				}
				if ( $email ) {
					_e( 'Email : ', 'woo_ua' );
					echo esc_html( $email );
				}
				echo "</p>";
			}
		}

	} else { /* add‑ons inactive → default pay button */
		$button_text = __( 'Pay Now', 'woo_ua' );
		if ( $product && method_exists( $product, 'get_uwa_auction_current_bider' ) ) {
			$bidder_id = $product->get_uwa_auction_current_bider();
			$bid_price = $product->get_uwa_auction_current_bid();
			if ( get_option( "_uwa_w_s_charge_{$product_id}_{$bidder_id}", false ) == $bid_price ) {
				$button_text = __( 'Get Item', 'woo_ua' );
			}
		}
		do_action( 'woocommerce_email_before_pay_button', $product );
		printf(
			__( 'Please, proceed to checkout, <a href=\"%s\">%s</a>.', 'woo_ua' ),
			esc_url( $checkout_url ),
			esc_html( $button_text )
		);
		echo "</br>";
	}
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );