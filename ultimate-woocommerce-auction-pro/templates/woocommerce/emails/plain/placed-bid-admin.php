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

/* -------------------------------------------------
 * 1)  Resolve product + core vars (live vs preview)
 * ------------------------------------------------*/
$object      = isset( $email->object ) ? $email->object : array();
$product     = null;
$product_id  = 0;
$user_type   = '';
$user_name   = '';
$auction_url = '';
$user_id     = 0;
$args        = array( 'currency' => get_woocommerce_currency() );
$auction_bid_value = wc_price( 0, $args );   // default

# -------- LIVE ----------
if ( is_array( $object ) && isset( $object['product'] ) && is_object( $object['product'] ) ) {
	$product   = $object['product'];
	$product_id = $product->get_id();
	$user_type = $object['user_type'];
	$user_name = $object['user_name'];
	$auction_url = $object['url_product'];
	$user_id   = absint( $object['placebid_userid'] );

	if ( method_exists( $product, 'uwa_aelia_get_base_currency' ) ) {
		$args['currency'] = $product->uwa_aelia_get_base_currency();
	}

# -------- PREVIEW (WC_Order) ----------
} elseif ( is_a( $object, 'WC_Order' ) ) {
	$order  = $object;
	$item   = current( $order->get_items() );
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	$user_type   = 'admin';
	$user_name   = $order->get_formatted_billing_full_name();
	$auction_url = $product ? get_permalink( $product_id ) : home_url();
	$user_id     = $order->get_user_id();
	$auction_bid_value = wc_price( $item ? $item->get_total() : 0, $args );
}

/* -------------------------------------------------
 * 2)  Auction‑specific data (silent / proxy logic)
 * ------------------------------------------------*/
if ( $product ) {

	// Base current bid
	if ( method_exists( $product, 'get_uwa_current_bid' ) ) {
		$auction_bid_value = wc_price( $product->get_uwa_current_bid(), $args );
	}

	$uwa_silent = method_exists( $product, 'get_uwa_auction_silent' ) ? $product->get_uwa_auction_silent() : 'no';
	$uwa_proxy  = method_exists( $product, 'get_uwa_auction_proxy' )  ? $product->get_uwa_auction_proxy()  : 'no';

	// Silent auction: show last bid or price HTML
	if ( $uwa_silent === 'yes' ) {
		if ( get_option( 'uwa_silent_outbid_email_cprice', 'no' ) === 'no' ) {
			$auction_bid_value = $product->get_price_html();
		} elseif ( method_exists( $product, 'get_uwa_last_bid' ) ) {
			$auction_bid_value = wc_price( $product->get_uwa_last_bid(), $args );
		}
	}

	// Proxy auction: override with user’s last bid
	if ( $uwa_proxy === 'yes' && method_exists( $product, 'get_uwa_auction_type' ) ) {
		$auction_type = $product->get_uwa_auction_type();
		if ( in_array( $auction_type, array( 'normal', 'reverse' ), true ) && $product_id && $user_id ) {
			$last_bid = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT bid FROM {$wpdb->prefix}woo_ua_auction_log
					 WHERE auction_id = %d AND userid = %d
					 ORDER BY id DESC LIMIT 1",
					$product_id,
					$user_id
				)
			);
			if ( $last_bid !== null ) {
				$auction_bid_value = wc_price( $last_bid, $args );
			}
		}
	}

	$auction_title = $product->get_title();
} else {
	$auction_title = __( 'Auction Product', 'woo_ua' );
}

/* -------------------------------------------------
 * 3)  Output e‑mail (unchanged layout)
 * ------------------------------------------------*/
echo $email_heading . "</br>";

if ( $user_type === 'admin' ) {
	printf( __( 'Hi,', 'woo_ua' ) );
	echo "</br>";
	printf(
		__( 'A bid was placed on <a href="%s">%s</a>.', 'woo_ua' ),
		esc_url( $auction_url ),
		esc_html( $auction_title )
	);
	echo "</br>";
	printf( __( 'Bid Value %s.', 'woo_ua' ), wp_kses_post( $auction_bid_value ) );
	echo "</br>";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );