<?php

/**
 * Send Email to bidder when the bidder won the auction. (Plain)
 * 
 * @package Ultimate WooCommerce Auction PRO
 * @author Nitesh Singh 
 * @since 1.0  
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

$product_id = 0;
$product = null;
$end_time_raw = '';

// ---------- LIVE MODE ----------
if ( isset( $email->productid ) && absint( $email->productid ) > 0 ) {
    $product_id = absint( $email->productid );
    $product    = wc_get_product( $product_id );

    if ( $product && method_exists( $product, 'get_uwa_auctions_end_time' ) ) {
        $end_time_raw = $product->get_uwa_auctions_end_time();
    } else {
        $end_time_raw = get_post_meta( $product_id, '_uwa_auctions_end_time', true );
    }
}

// ---------- PREVIEW MODE ----------
elseif ( is_a( $email->object, 'WC_Order' ) ) {
    $order   = $email->object;
    $item    = current( $order->get_items() );
    $product = $item ? $item->get_product() : null;
    $product_id = $product ? $product->get_id() : 0;

    if ( $product && method_exists( $product, 'get_uwa_auctions_end_time' ) ) {
        $end_time_raw = $product->get_uwa_auctions_end_time();
    } elseif ( $product_id ) {
        $end_time_raw = get_post_meta( $product_id, '_uwa_auctions_end_time', true );
    }
}

echo $email_heading . "\n\n";

printf(
    __( 'Auction <a href="%s">%s</a> is going to be expire at %s.', 'woo_ua' ),
    get_permalink( $product_id ),
    $product ? $product->get_title() : __( 'Auction Product', 'woo_ua' ),
    date_i18n( get_option( 'date_format' ), strtotime( $end_time_raw ) ) . ' ' .
    date_i18n( get_option( 'time_format' ), strtotime( $end_time_raw ) )
);

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
