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
//use Shuchkin\SimpleXLSX;

function uwa_bid_offline_download_xlsx() {

    global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

    if (ob_get_length() > 0) {
        ob_clean();
    }
    ob_start();

    $selected_category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? intval($_GET['category']) : 'all';

	$auction_args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => '-1',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'auction',
			),
		),
		'orderby'        => 'title',
		'order'          => 'ASC',
		'auction_arhive' => true,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'woo_ua_auction_closed',
				'value'   => array('1', '2', '3', '4'),
				'compare' => 'IN',
			),
		),
	);
	
	$query = new WP_Query($auction_args);
	
	$silent_auctions2 = array();
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$silent_auctions2[] = get_the_ID(); 
		}
		wp_reset_postdata();
	}
			
    // Get silent auctions
    $current_user_id = get_current_user_id(); // Get the current logged-in user ID

    $silent_auctions_results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            pm.post_id AS auction_id,
            MAX(al.bid) AS max_userbid,
            MAX(CASE WHEN al.userid = %d THEN al.bid ELSE NULL END) AS your_bids,
            p.post_title
        FROM {$wpdb->postmeta} AS pm
        JOIN {$wpdb->posts} AS p ON pm.post_id = p.ID
        LEFT JOIN {$wpdb->prefix}woo_ua_auction_log AS al ON pm.post_id = al.auction_id
        WHERE pm.meta_key = 'uwa_auction_silent'
        AND pm.meta_value = 'yes'
        AND p.post_status = 'publish'
        GROUP BY pm.post_id, p.post_title
    ", $current_user_id));
    
    // Filter out auctions that are in $silent_auctions2
    $filtered_silent_auctions = array_filter($silent_auctions_results, function ($auction) use ($silent_auctions2) {
        return !in_array($auction->auction_id, $silent_auctions2);
    });
 
    // Re-index array to maintain structure
    $filtered_silent_auctions = array_values($filtered_silent_auctions);

    // Xlsx fil generate 
    $sitename = sanitize_key(get_bloginfo('name'));
    if (!empty($sitename)) {
        $sitename .= '_';
    }

    $midtext = __('bids-silent-auction', 'woo_ua');
    $filename = $sitename . $midtext . '_' . date('d.m.Y') . '.xlsx';

    $enable_category_list = get_option('uwa_bid_offline_disable_category_filter', 'no');
    $disable_opening_price = get_option('uwa_bid_offline_opening_price', 'no');
    $enable_product_attr = get_option('uwa_importbids_user_xlsx_enable_pro_attr', 'no');
    $data_rows = [];

    $filtered_data = [];
    foreach ($filtered_silent_auctions as $auction) {
        $product = wc_get_product($auction->auction_id);
        if (!$product) {
            continue;
        }

        $category_ids = $product->get_category_ids();
        if ($selected_category_id === 'all' || array_intersect([$selected_category_id], $category_ids)) {
            $filtered_data[] = $auction;
        }
    }
    
    if (empty($filtered_data) && is_array($filtered_data)) { 
        $data_rows[] = [__('No bid users found.', 'woo_ua')];
    } else {

        $attribute_names = [];
        $final_attribute_names = [];

        // Define column headings
        $header_row = [
            __('Auction Id', 'woo_ua'),
            __('Product Title', 'woo_ua'),
            __('Currency Symbol', 'woo_ua'),
            __('Your Bid', 'woo_ua'),
            __('Enter Bid', 'woo_ua'),
        ];

        if ('yes' !== $disable_opening_price) {
            $header_row[] = __('Opening Price', 'woo_ua');
        }

        if ('yes' == $enable_category_list) {
            $header_row[] = __('Category', 'woo_ua');
        }
        

         // First pass: Collect all unique attributes
        foreach ($filtered_data as $auction) {
            $product = wc_get_product($auction->auction_id);
            if (!$product) { //|| 'yes' !== $enable_product_attr) {
                continue;
            }

            $attributes = $product->get_attributes();
            if (!empty($attributes)) {
                foreach ($attributes as $attribute_slug => $attribute) {
                    $attribute_name = wc_attribute_label($attribute_slug);
                    if (!in_array($attribute_name, $attribute_names)) {
                        $attribute_names[] = $attribute_name;
                    }
                }
            }
        }


        /* Add dynamic headers */
        if ('yes' == $enable_product_attr) {
            if (is_array($attribute_names) && count($attribute_names) > 0) {   

                // Add attribute headers dynamically
                $header_row = array_merge($header_row, $attribute_names);         

            } /* end of if */

        } /* end of if */    

        $header_row[] = __('End Date', 'woo_ua');

        // Process auctions
        $data_rows = [];

        foreach ($filtered_data as $auction) {
            $auction_id = $auction->auction_id;
            $product = wc_get_product($auction_id);

            if (!$product) {
                continue;
            }

            $product_id = $product->get_id();
            
            // Fetch category
            $category = wc_get_product_category_list($product_id, ', ');

            /* $user_bid = !empty($auction->your_bids) ? html_entity_decode(strip_tags(wc_price($auction->your_bids))) : ''; 
            $user_bid = preg_replace('/[^\d,\.]/', '', $user_bid); */           

            $user_bid = !empty($auction->your_bids) ? number_format($auction->your_bids, 2, '.', '') : ''; 


            /* $opening_price = html_entity_decode(strip_tags(wc_price($product->get_uwa_auction_start_price())));
            $opening_price = preg_replace('/[^\d,\.]/', '', $opening_price); */

            $opening_price = (float)$product->get_uwa_auction_start_price();
            $opening_price = number_format($opening_price, 2, '.', ''); 

            $end_date = get_post_meta($product_id, 'woo_ua_auction_end_date', true);
            $currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');

            // Initialize row
            $row = [
                $auction_id,
                $product->get_name(),
                $currency_symbol,
                $user_bid,
                "",
            ];

            if ('yes' !== $disable_opening_price) {
                $row[] = $opening_price;
            }

            
            if ('yes' == $enable_category_list) {
                $row[] = $category;
            }

        /* Add dynamic data */
        if ('yes' == $enable_product_attr) {            

            // Ensure each row has all attribute columns
            $attributes = $product->get_attributes();
            foreach ($attribute_names as $attr_name) {
                $final_value = '';

                foreach ($attributes as $attribute_slug => $attribute) {
                    if (wc_attribute_label($attribute_slug) == $attr_name) {
                        $taxonomy_id = wc_attribute_taxonomy_id_by_name($attribute_slug);

                        if ($taxonomy_id) {

                            // Taxonomy-based attribute (global attribute)
                            $terms = wc_get_product_terms($product_id, $attribute_slug, ['fields' => 'names']);

                                if (!empty($terms) && count($terms) == 1) {

                                    foreach ($terms as $key => $value) {

                                        if (uwa_isEuropeanNumber($value)) {

                                            // numbers like -  23,44 and 1.230,45 and 12.336,56
                                            // convert to normal - 23.44 and 1,230.45 and 12,336.56                                                     
                                            $value = uwa_normalizeNumber($value);
                                            $terms[$key] = $value;
                                        }
                                    }
                                }


                                /* check for multiple and single values and change accordingly */
                                if (!empty($terms)) {                                       

                                    if (count($terms) == 1) {
                                        $final_value = implode(', ', $terms);
                                    }                                           

                                    if (count($terms) > 1) {
                                        $final_value = '"' . implode('", "', $terms) . '"';
                                    } 

                                }

                            /* $final_value = !empty($terms) ? implode(', ', $terms) : ''; */

                        } else {

                                // Custom attribute
                                $attr_options = $attribute->get_options();

                                /* check for european number and convert it to normal number */
                                if (!empty($attr_options) && count($attr_options) == 1) {

                                    foreach ($attr_options as $key => $value) {

                                        if (uwa_isEuropeanNumber($value)) {

                                            // numbers like -  23,44 & 1.230,45 & 12.336,56
                                            // convert to normal - 23.44 & 1,230.45 & 12,336.56                                                 
                                            $value = uwa_normalizeNumber($value);
                                            $attr_options[$key] = $value;
                                        }
                                    }
                                }

                                /* check for multiple and single values and change accordingly */

                                if (!empty($attr_options)) {

                                    if (count($attr_options) == 1) {
                                        $final_value = implode(', ', $attr_options);
                                    }

                                    if (count($attr_options) > 1) {
                                        $final_value = '"' . implode('", "', $attr_options) . '"';
                                    } 

                                }


                            // $final_value = !empty($attr_options) ? implode(', ', $attr_options) : '';
                        }
                    break;
                    }
                }

                $row[] = $final_value;
            }


        }

            $row[] = $end_date;
            $data_rows[] = $row; // Add row to data

        }

        // Insert headers at the beginning
        array_unshift($data_rows, $header_row);

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

use Shuchkin\SimpleXLSX;

function uwa_bid_offline_upload_xlsx() {
    global $product_data, $wpdb;
	global $sitepress;

    /* Require an authenticated user - imported bids are stored against the current user. */
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( "message" => __( "Please sign in to import your bids.", "woo_ua" ) ) );
    }

    /* CSRF protection */
    if ( ! check_ajax_referer( 'UtAajax-nonce', 'ua_nonce', false ) ) {
        wp_send_json_error( array( "message" => __( "Security check failed. Please refresh the page and try again.", "woo_ua" ) ) );
    }

    // Step 1: Upload & Parse File
    if (!isset($_POST['step'])) {
        if (empty($_FILES['import_file']['name'])) {
            wp_send_json_error(["message" => __("No file uploaded. Please select a file.", "woo_ua")]);
        }

        if (pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION) !== 'xlsx') {
            wp_send_json_error(["message" => __("Invalid file type. Please upload an XLSX file.", "woo_ua")]);
        }

        require_once UW_AUCTION_PRO_DIR . '/library/simplexlsx/vendor/shuchkin/simplexlsx/src/SimpleXLSX.php';

        $file_path = $_FILES['import_file']['tmp_name'];
        if (!file_exists($file_path)) {
            wp_send_json_error(["message" => __("Uploaded file not found.", "woo_ua")]);
        }

        $xlsx = SimpleXLSX::parse($file_path);
        if (!$xlsx) {
            wp_send_json_error(["message" => __("Failed to parse the XLSX file.", "woo_ua")]);
        }

        $rows = $xlsx->rows();
        $total_records = count($rows) - 1; // Exclude header row

        if ($total_records < 1) {
            wp_send_json_error(["message" => __("No bid data found in the file.", "woo_ua")]);
        }

        array_shift($rows); // Remove header row

        session_start();
        $_SESSION['uwa_bids'] = $rows; // Store bids in session
        wp_send_json_success(["message" => __("File uploaded successfully. Processing bids...", "woo_ua"), "total_bids" => count($rows)]);
    }

    // Step 2: Process One Bid at a Time
    if ($_POST['step'] === 'process') {
        session_start();

        $user_id = get_current_user_id();
        $proxy_engine = false;
		$history_bid_id = false;

        if (!isset($_SESSION['uwa_bids']) || empty($_SESSION['uwa_bids'])) {
            wp_send_json_error(["message" => __("No bids to process.", "woo_ua")]);
        }

        $bid_index = intval($_POST['bid_index']);
        if (!isset($_SESSION['uwa_bids'][$bid_index])) {
            wp_send_json_error(["message" => __("Invalid bid index.", "woo_ua")]);
        }

        $row = $_SESSION['uwa_bids'][$bid_index];
        list($auction_id, $product_title, $currency_symbol, $user_bid, $enter_bid, $end_date) = $row;

        /* For WPML Support */
        if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress,'get_default_language')) {
            $auction_id = icl_object_id($auction_id, 'product', false, $sitepress->get_default_language());
        }

        $product_data = wc_get_product($auction_id);
        if( $product_data == false ){
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __("Sorry, the requested product was not found.", "woo_ua"),
            ]);
            exit;
        }

        if (!$product_data || $product_data->get_type() !== 'auction') {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __("Product is not available for auction.", "woo_ua"),
            ]);
        }

        $product_base_currency = $product_data->uwa_aelia_get_base_currency();
        $args = array("currency" => $product_base_currency);

        $post_obj    = get_post( $auction_id ); // The WP_Post object
        $post_author = $post_obj->post_author; // The post author ID
        /* For WPML Support */

        /* check for block user */
        $current_user = wp_get_current_user();		
        $user_status = get_user_meta($current_user->ID, 'uwa_block_user_status', true);
        if ($user_status == "uwa_block_user_to_bid") {
            $get_block_user_text = get_option('uwa_block_user_text');
            if($get_block_user_text != ""){
                wp_send_json_error(["message" => __($get_block_user_text, "woo_ua")]);
            }
            else{
                wp_send_json_error(["message" => __("You are not allow to place bid please contact to admin.", "woo_ua")]);
            }
        }

        /* check admin allow */
        if (is_user_logged_in()) {
            $uwa_allow_admin_to_bid = get_option('uwa_allow_admin_to_bid', "no");
            $uwa_allow_owner_to_bid = get_option('uwa_allow_owner_to_bid', "no");

            /* for administrator role only */
            if(current_user_can('administrator')){
                if($uwa_allow_admin_to_bid == "no" && $current_user->ID == $post_author){
                    wp_send_json_error(["message" => __("Sorry, you can not bid on your own auction product.", "woo_ua")]);                    
                }
            }
            else{ /* for seller/vendor/other role  */
                if($uwa_allow_owner_to_bid == "no" && $current_user->ID == $post_author){
                    wp_send_json_error(["message" => __("Sorry, you can not bid on your own auction product.", "woo_ua")]);
                }
            }
        }

        /* check for bidder's address in automatic order */
        if (is_user_logged_in()) {
			$uwa_auto_order_enable = get_option('uwa_auto_order_enable');
			if ($uwa_auto_order_enable == "yes") {
				$uwa_shipping_address_enable = get_option('uwa_shipping_address');
				if ($uwa_shipping_address_enable == "yes") {

					//$customer_id = $current_user->ID;
					$customer_id = get_current_user_id();
					$first_name = get_user_meta($customer_id, 'billing_first_name', true);
					$last_name = get_user_meta($customer_id, 'billing_last_name', true);
					$company = get_user_meta($customer_id, 'billing_company', true);
					$billing_email = get_user_meta($customer_id, 'billing_email', true);
					$user_email = get_user_meta($customer_id, 'email', true);
					$add_1 = get_user_meta($customer_id, 'billing_address_1', true);
					$add_2 = get_user_meta($customer_id, 'billing_address_2', true);
					$city = get_user_meta($customer_id, 'billing_city', true);
					$state = get_user_meta($customer_id, 'billing_state', true);
					$postcode = get_user_meta($customer_id, 'billing_postcode', true);
					$phone = get_user_meta($customer_id, 'billing_phone', true);
					$cntry_code = get_user_meta($customer_id, 'billing_country', true);
					$email = '';
					if ($billing_email) {
                        $email = $billing_email;
					} else {
						$email = $user_email;
					}

					if ($first_name == "" || $last_name == "" || $email == "" || $add_1 == "" 
						|| $city == "" || $postcode == "" || $phone == "" || $cntry_code == "" ) {
						$myaccount_link = wc_get_endpoint_url('edit-address', 'billing', get_permalink(wc_get_page_id('myaccount')));
						wp_send_json_error([
                            "message" => sprintf(
                                __('You can not place a bid on the auction, please add billing details. <a href="%s" target="_blank" class="button"> ADD ADDRESS &rarr;</a>', "woo_ua"),
                                $myaccount_link
                            ),
                            "stop" => true
                        ]);
					}
				}
			}
		}


        /* Clean the bid input */
        $enter_bid = str_replace(',', '.', strip_tags($enter_bid));

        /* Get WooCommerce decimal settings */
        $decimal_places = wc_get_price_decimals();

        /* Trim whitespace and check if bid is empty */
        if (trim($enter_bid) === '') {
            wp_send_json_error([
                "title" => "",
                "message" => "",
            ]);
        }

        /* Validate bid format (allow whole numbers or decimals with up to WooCommerce's decimal places) */
        $regex_pattern = '/^\d+(\.\d{1,})?$/';

        if (!preg_match($regex_pattern, $enter_bid)) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __('Please enter a valid bid amount using only numbers.', 'woo_ua'),
            ]);
        }

        /* Convert to float and round according to WooCommerce settings */
        $enter_bid = abs(round((float)$enter_bid, $decimal_places));

        /* Check if the bid is empty or zero */
        if ($enter_bid == 0) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __('Please enter a value greater than 0!', 'woo_ua'),
            ]);
        }

        /* Check if auction product expired */
		if ($product_data -> is_uwa_expired()) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => sprintf(__('This auction &quot;%s&quot; has expired', 'woo_ua'), $product_data->get_title()),
            ]);
		}

        /* Check if auction product Live or schedule */
		if (!$product_data -> is_uwa_live()) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => sprintf(__('Sorry, the auction for &quot;%s&quot; has not started yet', 'woo_ua'), $product_data->get_title()),
            ]);
		}

        /* Check Stock */
        if (!$product_data->is_in_stock()) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => sprintf(__('You cannot place a bid for &quot;%s&quot; because the product is out of stock.', 'woo_ua'), $product_data->get_title()),
            ]);
        }

        /* Check User Can Max Bid */
		$max_bid_amt = get_option( 'uwa_can_maximum_bid_amt');	
		if (empty($max_bid_amt)) {
			$max_bid_amt= 999999999999.99;
		}
        if ($enter_bid >= $max_bid_amt) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => sprintf(__('Bid Value must less than %s !', 'woo_ua'), $product_data->get_title()),
            ]);
		}

        /* Check product data */
        if (!$product_data) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __("Invalid product data.", "woo_ua"),
            ]);
        }

        // Check if auction is silent
        $is_silent = get_post_meta($auction_id, 'uwa_auction_silent', true);
        if ($is_silent != 'yes') {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __("This is not a silent auction.", "woo_ua"),
            ]);
        }

        /* Check auction status */
        $auction_status = get_post_meta($auction_id, 'woo_ua_auction_closed', true);
        if (in_array($auction_status, ['1', '2', '3', '4'])) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => __("This auction has expired.", "woo_ua"),
            ]);
        }

        /* Check opening price */
        $opening_price = $product_data->get_uwa_auction_start_price();
        if ($opening_price > $enter_bid) {
            wp_send_json_error([
                "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => sprintf(__('Your bid is smaller for &quot;%s&quot;, your bid must meet or exceed the opening price %s', 'woo_ua'), $product_title, wc_price($opening_price)),
            ]);
        }

		$product_id = $product_data->get_id();
		$auction_type = $product_data->get_uwa_auction_type();		
		$auction_bid_count = $product_data->get_uwa_auction_bid_count();
		$product_base_currency = $product_data->uwa_aelia_get_base_currency();
  		$args = array("currency" => $product_base_currency);

		if ( ($product_data -> is_uwa_user_biding($current_user->ID) > 0 ) && get_option( 'uwa_restrict_bidder_enable') == 'yes') {	
			wp_send_json_error([
                "title"   => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                "message" => sprintf(__('You already placed bid for this auction. ', 'woo_ua'), $product_data->get_title()),
            ]);
		}

		if ($auction_type == 'normal') {
			if ( !empty($product_data->get_uwa_auction_start_price())) {

				/* check user bid must be greater than his previous max bid */
				$slient_userid = $current_user->ID;
				/* Select max(bid) from wp_woo_ua_auction_log where userid = 2 and auction_id = 198 */
				$user_max_bid = $wpdb->get_var($wpdb->prepare(
					"SELECT MAX(bid) FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d AND userid = %d",
					$product_id,
					$slient_userid
				));
				/* echo $user_max_bid; */
				 
                if (!empty($user_max_bid) && $user_max_bid >= $enter_bid) {						
                    if ($user_max_bid == $enter_bid) {
                        wp_send_json_error([
                            "title"   => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                            "message" => sprintf(__('Your bid is same as your previous bid %s, Please enter higher bid.', 'woo_ua'),
                                wc_price($user_max_bid, $args)),
                        ]);
                        exit;
                    } elseif ($user_max_bid > $enter_bid) {						
                        wp_send_json_error([
                            "title"   => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                            "message" => sprintf(__('Your bid is lower than your previous bid %s, Please enter higher bid.', 'woo_ua'),
                                wc_price($user_max_bid, $args)),
                        ]);
                        exit;
                    }					
                }
                
			}

            /* Bid Place */
            $latest_bid = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(bid) FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d AND userid = %d",
                $auction_id,
                $user_id
            ));

            if (!$latest_bid || $enter_bid > floatval($latest_bid)) {
                if (  $enter_bid > (float)$product_data -> get_uwa_current_bid()) {
                    update_post_meta($product_id, 'woo_ua_auction_current_bid', $enter_bid);
                    update_post_meta($product_id, 'woo_ua_auction_current_bider', $current_user->ID);
                }
    
                update_post_meta($product_id, 'woo_ua_auction_bid_count',(int)$auction_bid_count + 1);
                update_post_meta($product_id, 'woo_ua_auction_last_bid', $enter_bid);
    
                $auction_history = new UWA_Bid(); 
                $history_bid_id = $auction_history->history_bid($product_id, $enter_bid, $current_user);
                
                /* Prnding antisnipping code */
                // $type_of_antisnipping  = get_option('uwa_aviod_snipping_type');
                // /*if($type_of_antisnipping != ""){*/
                    
                //         if($type_of_antisnipping == "snipping_recursive"){
                            
                //             do_action('uwa_extend_auction_time', $product_id);
                            
                //         } /* end of if - snipping recursive */										
                //         else if($type_of_antisnipping == "snipping_only_once" || $type_of_antisnipping == "" ){ 
                
                //             /* Anti snipping hook and it must be called only once  -- start */
                //             $is_antisnipped = get_post_meta($product_id, 
                //                 'woo_ua_auction_extend_time_antisnipping', true);
                //             /* Extend auction end time only if it not extended before */
                //             if($is_antisnipped != 'yes'){
                //                 do_action('uwa_extend_auction_time', $product_id);
                //             }
                //             /* Anti snipping hook and it must be called only once  -- end */
                            
                //         } /* end of if - snipping  only once */
                // /*}*/

                /* Send Notification to Bidder/Admin */
                if($history_bid_id){
                        WC()->mailer();

                        $placing_bid = wc_get_product($product_id);
                        /* bid placed notification to bidder */
                        do_action('uwa_pro_bid_place_email', $current_user->ID, $placing_bid);
                        do_action('uwa_pro_bid_place_email_admin', $current_user->ID, $placing_bid);
                        /* send watchlist mail */
                    
                        $watchlistusers = get_uwa_auction_watchlist_by_auctionid($product_id);
                        if(count($watchlistusers) > 0){
                            do_action('uwa_pro_watchlist_email', $product_data,
                                $watchlistusers);
                        }

                        $outbiddeduser = $placing_bid->get_uwa_auction_current_bider();	
                        $uwa_silent_outbid_email = get_option('uwa_silent_outbid_email',"no");
                        $uwa_silent = $product_data->get_uwa_auction_silent();
                        if($uwa_silent == 'yes'  && $uwa_silent_outbid_email =="yes"){
                            /* Pending outbiddeduser code */
                            if(!empty($outbiddeduser)){
                                /* send mail to outbiddeduser user */
                                do_action( 'uwa_pro_outbid_bid_email', $outbiddeduser,$placing_bid);
                                do_action('ultimate_woocommerce_auction_outbid_bid', $product_id ,$outbiddeduser);		
                            }	
                        }								
                }
                /* Send Notification to Bidder/Admin */
                
                wp_send_json_success([
                    "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                    "message" => __("Your Bid Placed Successfully", "woo_ua"),
                ]);
                
            } else {

                wp_send_json_error([
                    "title" => sprintf(__('Auction ID %s - %s', 'woo_ua'), $auction_id, $product_title),
                    "message" => sprintf(__('Please enter a bid value for &quot;%s&quot; greater than the current bid. Your bid must be at least %s ', 'woo_ua'),
                        $product_data->get_title(),
                        wc_price($product_data->uwa_bid_value(), $args)
                    ),
                ]);

            }
			
		} /*auction_type end*/

    }
}

