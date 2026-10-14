<?php

/**
 * Auction product add to cart
 * 
 * @package Ultimate WooCommerce Auction PRO
 * @author Nitesh Singh 
 * @since 1.0  
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

global $woocommerce, $product, $post;

if ( ! $product->is_purchasable() ) {
    return;
}
    
if (  $product->is_uwa_expired() ) {
    return;
}

if (  $product->is_uwa_live() === FALSE )  {
    return;
}

if ( !$product->is_sold_individually() ) {
    return;
}
    
if ( $product->is_in_stock() ) : ?>

<?php do_action('woocommerce_before_add_to_cart_form'); 
$uwa_show_bid_layout = get_option('uwa_show_bid_layout', "uwa_show_first_bid_layout");
$uwa_auction_type = $product->get_uwa_auction_type();
?>

<form class="buy-now cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>">
        
    <?php 
    if($uwa_show_bid_layout == 'uwa_show_second_bid_layout' && $uwa_auction_type == 'normal' ){
        
        ?>
            <div class="group-row uwa_buttons">
            <?php
            
        } else {
            
             ?>
            <div class="uwa_buttons">
            <?php
        }
        
        do_action('woocommerce_before_add_to_cart_button');

        if ( ! $product->is_sold_individually() )
                woocommerce_quantity_input( array(
                        'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
                        'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
                ) );
             ?>
            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
            
                    <?php 
                        $auction_selling_type = $product->get_uwa_auction_selling_type();

                        if($auction_selling_type == "buyitnow" || $auction_selling_type == "both" || 
                            $auction_selling_type == ""){


                                /* --------  when offline_dealing_addon is active ------- */
                                $addons = array();
                                $addons = uwa_enabled_addons();

                             
                                if($addons == false || (is_array($addons) && !in_array('uwa_offline_dealing_addon', 
                                    $addons))){
                                                

                                    $uwa_disable_buy_it_now = get_option('uwa_disable_buy_it_now', "no");
                                    $uwa_disable_buy_it_now__bid_check = get_option('uwa_disable_buy_it_now__bid_check', "no");
                                    $current_bid_value = $product->get_uwa_auction_current_bid();
                                    $buy_now_price = $product->get_regular_price();
                                
                                    if($uwa_disable_buy_it_now == "no" && $uwa_disable_buy_it_now__bid_check == "no" ){
                                        
                                        $buy_now_price = $product->get_regular_price();
                                         if(!empty($buy_now_price) && ($buy_now_price > 0)){                
                                                ?>
                                                <button type="submit" class="single_add_to_cart_button button alt">
                                                    <?php echo apply_filters('single_add_to_cart_text',
                                                        sprintf(__( 'Buy Now %s', 'woo_ua' ), wc_price($product->get_regular_price())), $product); ?>
                                                </button>
                                              <?php
                                            }
                                    }
                                    elseif($uwa_disable_buy_it_now == "yes" && $uwa_disable_buy_it_now__bid_check == "no" ){
                                        
                                         if($product->is_uwa_reserve_met() == FALSE){

                                            $buy_now_price = $product->get_regular_price();
                                            if(!empty($buy_now_price) && ($buy_now_price > 0)){                
                                                ?>

                                                <button type="submit" class="single_add_to_cart_button button alt">
                                                    <?php echo apply_filters('single_add_to_cart_text',
                                                        sprintf(__( 'Buy Now %s', 'woo_ua' ), wc_price($product->get_regular_price())), $product); ?>
                                                </button>
                                                
                                             <?php
                                            }

                                        }
                                    }elseif($uwa_disable_buy_it_now == "yes" && $uwa_disable_buy_it_now__bid_check == "yes" ){
                                        
                                        if($product->is_uwa_reserve_met() == FALSE){
                                            
                                            if ($current_bid_value < $buy_now_price) {
                                                $buy_now_price = $product->get_regular_price();
                                                    if(!empty($buy_now_price) && ($buy_now_price > 0)){ ?>
                                                        <button type="submit" class="single_add_to_cart_button button alt">
                                                            <?php echo apply_filters('single_add_to_cart_text',
                                                                sprintf(__( 'Buy Now %s', 'woo_ua' ), wc_price($product->get_regular_price())), $product); ?>
                                                        </button>
                                                     <?php
                                                    }
                                                }
                                            
                                        }
                                    }elseif($uwa_disable_buy_it_now == "no" && $uwa_disable_buy_it_now__bid_check == "yes" ){
                                        
                                        if ($current_bid_value < $buy_now_price) {
                                            $buy_now_price = $product->get_regular_price();
                                                if(!empty($buy_now_price) && ($buy_now_price > 0)){ ?>
                                                    <button type="submit" class="single_add_to_cart_button button alt">
                                                        <?php echo apply_filters('single_add_to_cart_text',
                                                            sprintf(__( 'Buy Now %s', 'woo_ua' ), wc_price($product->get_regular_price())), $product); ?>
                                                    </button>
                                                 <?php
                                                }
                                            }    
                                    }
                                    
                                    
                    
                                    if($uwa_show_bid_layout == 'uwa_show_second_bid_layout' && $uwa_auction_type == 'normal' ){
                                                 
                                                 
                                        $tooltip_imgurl = UW_AUCTION_PRO_URL . "assets/images/tool-tip.png";

                                        $get_buyitnow_tooltip_text = get_option('uwa_buyitnow_tooltip_text');

                                        if($get_buyitnow_tooltip_text){                                            
                                            $buyitnow_tooltip =   __($get_buyitnow_tooltip_text, 'woo_ua');
                                        }
                                        else{                                           
                                            $buyitnow_tooltip =   __("Bidder can purchase the auction product directly via Buy It Now button.", 'woo_ua');
                                        }
    
                                        ?>

                                        <span class="uwa-auction-tooltip" data-tooltip="<?php echo esc_attr( $buyitnow_tooltip ); ?>">
                                        <svg width="30px" height="30px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_iconCarrier"> <g id="Warning / Info"> <path id="Vector" d="M12 11V16M12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21ZM12.0498 8V8.1L11.9502 8.1002V8H12.0498Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g> </g></svg>
                                        </span>

                                        <?php 
                                             
                                            
                                        } 
                                   

                                } /* end of if - addon */
                                
                        } /* end of auction selling type */
                    ?>

                    <input type="hidden" name="add-to-cart" value="<?php echo $product->get_id(); ?>" />
                    <input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
                
                <?php do_action('woocommerce_after_add_to_cart_button'); ?>

            </div>
</form>
    
    <?php do_action('ultimate_woocommerce_auction_after_bid_form'); ?>
    <?php do_action('woocommerce_after_add_to_cart_form'); ?>
<?php endif; ?>