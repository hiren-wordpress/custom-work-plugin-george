<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 
 * Handles generic Backend functionality 
 * 
 * @package Ultimate WooCommerce Auction PRO
 * @since 1.0
 *
 */

use Shuchkin\SimpleXLSXGen;

function uwa_active_bid_user_download_xls() {

    global $wpdb;

    if (ob_get_length() > 0) {
        ob_clean();
    }
    ob_start();

    $selected_category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? intval($_GET['category']) : 'all';
    $uwa_bid_type = isset($_GET['uwa_bid_type']) ? sanitize_text_field($_GET['uwa_bid_type']) : 'active';

    // Get selected category from URL parameter
    $auct_users = get_option('uwa_users_bids_list');
    $curr_user_id = get_current_user_id();
    $table = $wpdb->prefix . "woo_ua_auction_log";
    $table2 = $wpdb->prefix . "posts";
    $table_users = $wpdb->prefix . "users";

    $all_auctions = array();
    $won_bids_array = array();
    $not_won_bids_array = array();
    $active_bids_array = array();
    $data_array = array();

    if (!empty($auct_users)) {
        $query = $wpdb->prepare("
            SELECT DISTINCT(auction_id) 
            FROM {$table} as alg 
            JOIN {$table2} as wposts ON wposts.ID = alg.auction_id 
            WHERE alg.userid = %d  
            AND wposts.post_type = 'product'  
            AND wposts.post_status = 'publish'  
            ORDER BY alg.auction_id DESC
        ", $auct_users);
        $selected = "one_user"; 
    } else {
        $query = $wpdb->prepare("
            SELECT DISTINCT(auction_id) 
            FROM {$table} as alg 
            JOIN {$table2} as wposts ON wposts.ID = alg.auction_id 
            WHERE alg.userid != %d  
            AND wposts.post_type = 'product'  
            AND wposts.post_status = 'publish'  
            GROUP BY alg.auction_id 
            ORDER BY alg.auction_id DESC
        ", $curr_user_id);
        $selected = "all";
    }

    // Get auction IDs
    $auction_ids = $wpdb->get_col($query);

    if (count($auction_ids) > 0) {
        foreach ($auction_ids as $auc_id) {
            $product_data = wc_get_product($auc_id);
            if ($product_data && $product_data->get_type() == 'auction') {  
                if ($uwa_bid_type == "active" && $product_data->get_uwa_auction_expired() == false) {
    
                    // Get the highest bid for this auction
                    $subquery = "SELECT MAX(bid) FROM $table WHERE auction_id = %d";
                    $query_maxbid = $wpdb->prepare("
                        SELECT * FROM $table 
                        WHERE auction_id = %d 
                        AND userid IN (SELECT ID FROM $table_users)    
                        AND bid = ($subquery)
                    ", $auc_id, $auc_id);
    
                    $newdata = $wpdb->get_results($query_maxbid);
    
                    if (count($newdata) > 0) {
                        // Exclude current user’s bid
                        if ($curr_user_id != $newdata[0]->userid) {
                            if ($selected == "one_user") {
                                if ($auct_users == $newdata[0]->userid) {
                                    $active_bids_array[] = $newdata[0];
                                }
                            } else {
                                $active_bids_array[] = $newdata[0];
                            }
                        }
                    }
                }
                elseif ($uwa_bid_type == "not-won" && $product_data->get_uwa_auction_expired() == 2) {

                    // Get the highest bid for this auction
                    $subquery = "SELECT MAX(bid) FROM $table WHERE auction_id = %d";
                    $query_otherbids = $wpdb->prepare("
                        SELECT * FROM $table 
                        WHERE auction_id = %d
                        AND userid IN (SELECT ID FROM $table_users)    
                        AND bid != ($subquery)
                        ORDER BY id DESC
                    ", $auc_id, $auc_id);
                
                    $newdata = $wpdb->get_results($query_otherbids);
                
                    if (count($newdata) > 0) {
                        foreach ($newdata as $single_auction) {
                            // Exclude current user’s bid
                            if ($curr_user_id != $single_auction->userid) {
                                if ($selected == "one_user") {
                                    if ($auct_users == $single_auction->userid) {
                                        $not_won_bids_array[] = $single_auction;
                                    }
                                } else {
                                    $not_won_bids_array[] = $single_auction;
                                }
                            }
                        }
                    }
                } 
                elseif ($uwa_bid_type == "won" && $product_data->get_uwa_auction_expired() == 2) {
    
                    // Get the highest bid for this auction
                    $subquery = "SELECT MAX(bid) FROM $table WHERE auction_id = %d";
                    $query_maxbid = $wpdb->prepare("
                        SELECT * FROM $table 
                        WHERE auction_id = %d     
                        AND userid IN (SELECT ID FROM $table_users)             
                        AND bid = ($subquery)
                    ", $auc_id, $auc_id);
                
                    $newdata = $wpdb->get_results($query_maxbid);
                
                    if (count($newdata) > 0) {
                        // Exclude current user’s bid
                        if ($curr_user_id != $newdata[0]->userid) {
                            if ($selected == "one_user") {
                                if ($auct_users == $newdata[0]->userid) {
                                    $won_bids_array[] = $newdata[0];
                                }
                            } else {
                                $won_bids_array[] = $newdata[0];
                            }                        
                        }
                    }
                } 
                elseif ($uwa_bid_type == "won-lost" && $product_data->get_uwa_auction_expired() == 2) {
    
                    // Get the highest bid for this auction
                    $subquery_lost = "SELECT MAX(bid) FROM $table WHERE auction_id = %d";
                    $query_otherbids = $wpdb->prepare("
                        SELECT * FROM $table 
                        WHERE auction_id = %d
                        AND userid IN (SELECT ID FROM $table_users)    
                        AND bid != ($subquery_lost)
                        ORDER BY id DESC
                    ", $auc_id, $auc_id);
                
                    $newdata = $wpdb->get_results($query_otherbids);
                
                    if (count($newdata) > 0) {
                        foreach ($newdata as $single_auction) {
                            // Exclude current user’s bid
                            if ($curr_user_id != $single_auction->userid) {
                                if ($selected == "one_user") {
                                    if ($auct_users == $single_auction->userid) {
                                        $won_lost_bids_array[] = $single_auction;
                                    }
                                } else {
                                    $won_lost_bids_array[] = $single_auction;
                                }
                            }
                        }
                    }

                    // Get the highest bid for this auction
                    $subquery_won = "SELECT MAX(bid) FROM $table WHERE auction_id = %d";
                    $query_maxbid = $wpdb->prepare("
                        SELECT * FROM $table 
                        WHERE auction_id = %d     
                        AND userid IN (SELECT ID FROM $table_users)             
                        AND bid = ($subquery_won)
                    ", $auc_id, $auc_id);
                
                    $newdata = $wpdb->get_results($query_maxbid);
                
                    if (count($newdata) > 0) {
                        // Exclude current user’s bid
                        if ($curr_user_id != $newdata[0]->userid) {
                            if ($selected == "one_user") {
                                if ($auct_users == $newdata[0]->userid) {
                                    $won_lost_bids_array[] = $newdata[0];
                                }
                            } else {
                                $won_lost_bids_array[] = $newdata[0];
                            }                        
                        }
                    }

                }
            }
        }
    }

    if($uwa_bid_type=="won"){
        $all_auctions = $won_bids_array;
    }			
    elseif($uwa_bid_type=="not-won"){
        $all_auctions = $not_won_bids_array;
    }elseif ($uwa_bid_type == "won-lost") {
        $all_auctions = $won_lost_bids_array;
    }
    else{
        $all_auctions = $active_bids_array;
    }

    foreach ($all_auctions as $auction) {
        $terms = get_the_terms($auction->auction_id, 'product_cat');
        $category_id = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->term_id : null;
        $auctions_with_category[] = [
            "id" => $auction->id,
            "userid" => $auction->userid,
            "auction_id" => $auction->auction_id,
            "category_id" => $category_id, // Dynamically fetched category ID
            "bid" => $auction->bid,
            "date" => $auction->date,
        ];
    }

    $user_ids = array();
    if (!empty($auctions_with_category)) {
        foreach ($auctions_with_category as $auction) {
            if ($selected_category_id === 'all' || $auction['category_id'] == $selected_category_id) {
                if (!in_array($auction['userid'], $user_ids)) {
                    $user_ids[] = $auction['userid'];
                }
            }
        }
    }

    // Xlsx fil generate 
    $sitename = sanitize_key(get_bloginfo('name'));
    if (!empty($sitename)) {
        $sitename .= '_';
    }

    // Define translation mapping for bid types
    $uwa_bid_types = array(
        'active'   => __('activebid-users', 'woo_ua'),
        'won'      => __('wonbid-users', 'woo_ua'),
        'lost'     => __('lostbid-users', 'woo_ua'),
        'won-lost' => __('won-lostbid-users', 'woo_ua'), // Ensure combined types are also mapped
    );

    // Translate bid type if it exists in the mapping
    $translated_bid_type = isset($uwa_bid_types[$uwa_bid_type]) ? $uwa_bid_types[$uwa_bid_type] : $uwa_bid_type;

    $midtext = __('bid-users', 'woo_ua');
    $filename = $sitename . $translated_bid_type . '_' . date('d.m.Y') . '.xlsx';

    $data_rows = [];
    if (empty($user_ids)) {
        $data_rows[] = [__('No bid users found.', 'woo_ua')];
    } else {
        // Define column headings
        $header_row = [
            __('User ID', 'woo_ua'),
            __('Username', 'woo_ua'),
            __('Email', 'woo_ua'),
            __('Company', 'woo_ua'),
            __('Billing First Name', 'woo_ua'),
            __('Billing Last Name', 'woo_ua'),
            __('Billing Street', 'woo_ua'),
            __('Billing Postcode', 'woo_ua'),
            __('Billing City', 'woo_ua'),
            __('Billing Uid', 'woo_ua'),
            __('Billing Country', 'woo_ua'),
            __('Phone', 'woo_ua')
        ];
    
        $data_rows[] = $header_row; // Add headers
    
        foreach ($user_ids as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $row = [
                    $user_id, // User ID
                    $user->user_login, // Username
                    $user->user_email, // Email
                    get_user_meta($user_id, 'billing_company', true),
                    get_user_meta($user_id, 'billing_first_name', true),
                    get_user_meta($user_id, 'billing_last_name', true),
                    get_user_meta($user_id, 'billing_address_1', true),
                    get_user_meta($user_id, 'billing_postcode', true),
                    get_user_meta($user_id, 'billing_city', true),
                    get_user_meta($user_id, 'billing_uid', true),
                    get_user_meta($user_id, 'billing_country', true),
                    get_user_meta($user_id, 'billing_phone', true)
                ];
    
                $data_rows[] = $row;
            }
        }
    }
    
    // Send headers before file content
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename={$filename}");
    header('Cache-Control: max-age=0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $sheet_name = __('Sheet1', 'woo_ua');

    require_once UW_AUCTION_PRO_DIR . '/library/simplexlsx/vendor/autoload.php';

    $xlsx = new SimpleXLSXGen();
    $xlsx->addSheet($data_rows, $sheet_name); // Add sheet with translated name

    // Output the Excel file directly to the browser
    $xlsx->downloadAs($filename);

    ob_end_flush();
    die();
}


