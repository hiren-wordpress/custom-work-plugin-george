<?php

/**
 * Admin deleted user bid notification (plain)
 * 
 * @package Ultimate WooCommerce Auction PRO
 * @author Nitesh Singh 
 * @since 1.0  
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ---------- LIVE & PREVIEW MODE SUPPORT ---------- */
$product            = null;
$product_id         = 0;
$args               = array( 'currency' => get_woocommerce_currency() );
$auction_title      = '';
$auction_url        = '';
$deleted_bid_value  = '';
$thumb_image        = '';
$user_name          = '';
$deleted_by_value   = 'by_admin';


if ( is_array( $email->object ) && isset( $email->object['product'] ) ) {
	/* ---------- LIVE EMAIL DATA ---------- */
	$product    = $email->object['product'];
	$product_id = $product->get_id();

	/* Currency */
	$product_base_currency = method_exists( $product, 'uwa_aelia_get_base_currency' )
		? $product->uwa_aelia_get_base_currency()
		: get_woocommerce_currency();
	$args = array( 'currency' => $product_base_currency );

	/* Core values */
	$auction_title      = $email->object['product_name'];
	$auction_url        = esc_url( $email->object['url_product'] );

	// $deleted_bid_value  = wc_price( $email->object['deleted_bid'], $args );
	$deleted_bid_value  = $email->object['deleted_bid'];

	$deleted_by_value = $email->object['deleted_by'];

	$thumb_image        = $product->get_image( 'thumbnail' );
	$user_name          = esc_html( $email->object['user_name'] );

} else {
	/* ---------- PREVIEW MODE ---------- */
	$order  = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item   = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	/* Core values with fallbacks */
	$auction_title = $product ? $product->get_title() : __( 'Auction Product', 'woo_ua' );
	$auction_url   = $product ? get_permalink( $product_id ) : home_url();
	$thumb_image   = $product ? $product->get_image( 'thumbnail' ) : '';
	$user_name     = $order ? $order->get_formatted_billing_full_name() : '';

	/* Use line‑item total (like WC preview) before falling back to meta */
	$deleted_raw = $item ? $item->get_total() : get_post_meta( $product_id, '_auction_current_bid', true );

	// $deleted_bid_value = wc_price( $deleted_raw, $args );
	$deleted_bid_value = $deleted_raw;

}

/* ---------- you can now echo / use these variables below ---------- */
	
	echo "\t\t\t";
	echo $email_heading; /*  email header */
	echo "\n\n\n";
	printf( __( "Hi %s,", "woo_ua" ), $user_name);
	echo "\n\t";


	if ($deleted_by_value == "by_user") {

		printf(__( "We wanted to let you know that your bid has been successfully deleted from our system.", "woo_ua" ));
		printf(__( "If you did not intend to delete your bid or if you need any assistance, kindly contact admin/owner.", 
			"woo_ua" ));

	} else if ($deleted_by_value == "by_admin") { 
		printf( __( "You are receiving this email as auction owner has deleted your bid. Kindly contact admin/owner for further discussion.", "woo_ua" ));
	}
	
	echo "\n\n";

	printf( __( "Here are the details : ", "woo_ua" ));
	echo "\n";

	/*echo __( 'Image :', 'woo_ua' );
	echo "\n";
	echo $thumb_image;*/

	printf( __( "Product : %s", "woo_ua" ), $auction_title);
	echo "\n";	
	printf( __( "Deleted Bid : %s", "woo_ua" ), wc_price($deleted_bid_value, $args));
	echo "\n\n\n\t\t\t";
	
	echo apply_filters( 'woocommerce_email_footer_text', 
		get_option( 'woocommerce_email_footer_text' ) );  /*  email footer */