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

?>
<?php

/* ---------- LIVE & PREVIEW MODE SUPPORT ---------- */
$product    = null;
$product_id = 0;

if ( isset( $email->productid ) && $email->productid ) {
	/* LIVE EMAIL (legacy) */
	$product_id = absint( $email->productid );
	$product    = wc_get_product( $product_id );

} else {
	/* PREVIEW MODE (WooCommerce passes WC_Order) */
	$order = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item  = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;
}

?>

<p><?php _e( "Hi,", 'woo_ua' ); ?></p>

<p>
<?php

	printf(__('We regret to inform you that you have lost <a href="%s">%s</a>', 'woo_ua'),
		get_permalink($product_id), $product->get_title());

?>
</p>
<?php echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' )); ?>
