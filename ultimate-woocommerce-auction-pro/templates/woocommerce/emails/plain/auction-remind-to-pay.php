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
$product           = null;
$product_id        = 0;
$args              = array( 'currency' => get_woocommerce_currency() );
$auction_url       = '';
$user_name         = '';
$auction_title     = '';
$auction_bid_value = '';
$thumb_image       = '';
$checkout_url      = '';

if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {
	/* ---------- LIVE EMAIL DATA ---------- */
	$product_id = absint( $email->object['product_id'] );

	/* Use auction helper if available */
	$product = function_exists( 'Woo_UA' ) && method_exists( Woo_UA(), 'uwa_get_product' )
		? Woo_UA()->uwa_get_product( $product_id )
		: wc_get_product( $product_id );

	/* Currency */
	if ( $product && method_exists( $product, 'uwa_aelia_get_base_currency' ) ) {
		$product_base_currency = $product->uwa_aelia_get_base_currency();
		$args = array( 'currency' => $product_base_currency );
	}

	/* Core live values */
	$auction_url       = get_permalink( $product_id );
	$user_name         = $email->object['user_name'];
	$auction_title     = $product->get_title();
	$auction_bid_value = wc_price( $product->get_uwa_current_bid(), $args );
	$thumb_image       = $product->get_image( 'thumbnail' );
	$checkout_url      = add_query_arg( array( 'pay-uwa-auction' => $product_id ), uwa_auction_get_checkout_url() );

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

} else {
	/* ---------- PREVIEW MODE ---------- */
	$order  = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item   = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	/* Core preview values */
	$auction_url       = $product ? get_permalink( $product_id ) : home_url();
	$user_name         = $order ? $order->get_formatted_billing_full_name() : '';
	$auction_title     = $product ? $product->get_title() : __( 'Auction Product', 'woo_ua' );
	$auction_bid_value = wc_price( $item ? $item->get_total() : 0, $args );
	$thumb_image       = $product ? $product->get_image( 'thumbnail' ) : '';
	$checkout_url      = home_url();  // simple placeholder in preview
}

          
?>
<p><?php printf( __( "Hi %s,", 'woo_ua' ),$user_name); ?></p>
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
      <td><a href="<?php echo $auction_url ;?>"><?php echo $auction_title; ?></a></td>
      <td><?php echo $auction_bid_value;  ?></td>
    </tr>
</table>
    
<div>
  <p><?php _e( 'Please, proceed to checkout', 'woo_ua' ); ?></p>
  <p><a style="padding:6px 28px !important;font-size: 12px !important; background: #ccc !important; color: #333 !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo $checkout_url; ?>" class="button"><?php _e('Pay Now', 'woo_ua') ?></a>
  </p>       
</div>

<?php do_action( 'woocommerce_email_footer', $email );?>