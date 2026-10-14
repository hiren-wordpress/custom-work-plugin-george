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

global $woocommerce, $wpdb;

// Setup defaults
$product               = null;
$product_id            = 0;
$user_type             = '';
$user_name             = '';
$auction_url           = '';
$auction_title         = __( 'Auction Product', 'woo_ua' );
$auction_bid_value     = '';
$product_base_currency = get_woocommerce_currency();
$args                  = array( 'currency' => $product_base_currency );
$uwa_silent            = 'no';
$uwa_proxy             = 'no';
$user_id               = 0;

// LIVE mode
if ( is_array( $email->object ) && isset( $email->object['product'] ) && is_object( $email->object['product'] ) ) {
	$product    = $email->object['product'];
	$product_id = $product->get_id();
	$user_type  = $email->object['user_type'];
	$user_name  = $email->object['user_name'];
	$auction_url = $email->object['url_product'];
	$user_id    = absint( $email->object['placebid_userid'] );

	if ( method_exists( $product, 'uwa_aelia_get_base_currency' ) ) {
		$product_base_currency = $product->uwa_aelia_get_base_currency();
		$args = array( 'currency' => $product_base_currency );
	}

	if ( method_exists( $product, 'get_uwa_current_bid' ) ) {
		$auction_bid_value = wc_price( $product->get_uwa_current_bid(), $args );
	}

	if ( method_exists( $product, 'get_title' ) ) {
		$auction_title = $product->get_title();
	}

	if ( method_exists( $product, 'get_uwa_auction_silent' ) ) {
		$uwa_silent = $product->get_uwa_auction_silent();
	}

	if ( method_exists( $product, 'get_uwa_auction_proxy' ) ) {
		$uwa_proxy = $product->get_uwa_auction_proxy();
	}

// PREVIEW mode
} elseif ( is_a( $email->object, 'WC_Order' ) ) {
	$order     = $email->object;
	$item      = current( $order->get_items() );
	$product   = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;
	$user_type  = 'bidder';
	$user_name  = $order->get_formatted_billing_full_name();
	$auction_url = $product ? get_permalink( $product_id ) : home_url();
	$user_id    = $order->get_user_id();

	if ( $product ) {
		$auction_title = $product->get_title();
		$auction_bid_value = wc_price( $item ? $item->get_total() : 0, $args );
	}
}

// Adjust bid value for silent auctions
if ( $product && $uwa_silent === 'yes' && method_exists( $product, 'get_uwa_last_bid' ) ) {
	$auction_bid_value = wc_price( $product->get_uwa_last_bid(), $args );
}

// Adjust for proxy bidding
if ( $product && $uwa_proxy === 'yes' && method_exists( $product, 'get_uwa_auction_type' ) ) {
	$auction_type = $product->get_uwa_auction_type();

	if ( in_array( $auction_type, array( 'normal', 'reverse' ), true ) ) {
		$last_bid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT bid FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d AND userid = %d ORDER BY id DESC LIMIT 1",
				$product_id,
				$user_id
			)
		);
		$auction_bid_value = wc_price( $last_bid, $args );
	}
}

// Output email content
echo $email_heading . "</br>";

if ( $user_type === "bidder" ) {
	printf( __( "Hi %s,", 'woo_ua' ), esc_html( $user_name ) );
	echo "</br>";

	$cur_userid = get_current_user_id();
	$sentmail = get_user_meta( $cur_userid, "uwa_samemaxbid_sent_mail", true );

	if ( $sentmail === "yes" ) {
		printf(
			__( "An user has placed a bid which matched your maximum bid and due to this, we have placed a new bid on your behalf with an amount same as your 'Maximum Bid' and declined the other user's bid. on <a href='%s'>%s</a>.", "woo_ua" ),
			esc_url( $auction_url ),
			esc_html( $auction_title )
		);
		delete_user_meta( $cur_userid, "uwa_samemaxbid_sent_mail" );
	} else {
		printf(
			__( 'You recently placed a bid on <a href="%s">%s</a>.', 'woo_ua' ),
			esc_url( $auction_url ),
			esc_html( $auction_title )
		);
	}

	echo "</br>";
	printf( __( "Bid Value %s.", 'woo_ua' ), $auction_bid_value );
	echo "</br>";

	if (
		$uwa_proxy === 'yes' &&
		method_exists( $product, 'get_uwa_auction_max_current_bider' ) &&
		method_exists( $product, 'get_uwa_auction_max_bid' ) &&
		$product->get_uwa_auction_max_current_bider() &&
		get_current_user_id() === $product->get_uwa_auction_max_current_bider()
	) {
		$max_bid_price = $product->get_uwa_auction_max_bid();
		$formatted_max_bid_price = $max_bid_price ? wc_price( $max_bid_price, $args ) : '---';
		printf( __( "Your maximum bid is  %s.", 'woo_ua' ), $formatted_max_bid_price );
		echo "</br>";
	}
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );