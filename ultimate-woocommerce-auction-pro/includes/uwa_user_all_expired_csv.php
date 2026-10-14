<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 
 *
 * Handles generic Front functionality
 * 
 * @package Ultimate WooCommerce Auction PRO 
 * @since 1.0
 *
 */

use Dompdf\Dompdf; 
use Dompdf\Options; 

use Shuchkin\SimpleXLSXGen;

function uwa_user_all_expired_auctions_download_csv() {
	
	if (ob_get_length() > 0) {
		ob_clean();
	}
	ob_start();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty( $sitename ) ) {
		$sitename .= '_';
	}

	/* $filename = $sitename . 'all-expired-auctions-' . date( 'Y-m-d-H-i-s' ) . '.csv'; */

	$midtext = __('all-expired-auctions', 'woo_ua');
	$filename = $sitename . $midtext . '_' . date( 'd.m.Y' ) . '.csv';
		
	global $wpdb;

	$data_rows = array();
	$header_row = array();
	$display_row = array();
	$curr_user_id = get_current_user_id();

	$tax_query = array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' );

	if (isset($_REQUEST['category']) && !empty($_REQUEST['category'])) {

		if ('all' != $_REQUEST['category'] && $_REQUEST['category'] > 0) {

			$tax_query = array(
				'relation' => 'AND', // Use 'AND' or 'OR' 
				array(
					'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
					'field'    => 'term_id',        // Use 'slug', 'name', or 'term_id'
					'terms'    => sanitize_text_field($_REQUEST['category']), // Replace with category slug
				),
				array(
					'taxonomy' => 'product_type', // WooCommerce product type taxonomy
					'field'    => 'slug',
					'terms'    => 'auction', // Replace with product type term
				),
			);
		}
	}   

	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => '-1',
		'tax_query' => $tax_query,
		'orderby' => 'title',
		'order' => 'ASC',
		'auction_arhive' => true,
	);

	/*  if (!empty($_REQUEST['users_auctions']) && $_REQUEST['users_auctions'] == 'true') {
		$args['author__not_in'] = $curr_user_id;
	} else {
		$args['author'] = $curr_user_id;
	} */
	
	$args['meta_query'] = array(
				'relation' => 'AND',
					array(
						'key' => 'woo_ua_auction_closed',
						'value' => array( '1', '2', '3', '4' ),
						'compare' => 'IN',
					),
				);

	$query = new WP_Query( $args );

	/* create heading for export files */

	if ( $query->have_posts() ) {

		$attribute_names = array();		
		$final_attribute_names = array();
		$enable_product_attr = get_option('uwa_exp_user_csv_enable_pro_attr', 'no');


		if ('yes' == $enable_product_attr) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$product_id = get_the_ID();
				$product = wc_get_product($product_id);

				/* get product attributes term names */
				$attributes = $product->get_attributes();
				if (is_array($attributes) && count($attributes) > 0) {
					foreach ($attributes as $attribute_slug => $attribute) {

						$attribute_names[] = $attribute_slug;
						// $attribute_names[] = $attribute->get_id();
						// $attribute_names[] = $attribute->get_name();
					}
				}
			} /* end of while */

		} /* end of if - enabled attr */

		
		$disable_pro_category = get_option('uwa_exp_user_csv_disable_product_category', 'no');
		$disable_hbid = get_option('uwa_exp_user_csv_disable_highest_bid', 'no');
		$disable_hbidder = get_option('uwa_exp_user_csv_disable_highest_bidder', 'no');
		$disable_hbidder_company = get_option('uwa_exp_user_csv_disable_company', 'no');
		$disable_first_name = get_option('uwa_exp_user_csv_disable_first_name', 'no');
		$disable_last_name = get_option('uwa_exp_user_csv_disable_last_name', 'no');
		$disable_address1 = get_option('uwa_exp_user_csv_disable_address1', 'no');
		$disable_address2 = get_option('uwa_exp_user_csv_disable_address2', 'no');
		$disable_postal_code = get_option('uwa_exp_user_csv_disable_postal_code', 'no');
		$disable_city = get_option('uwa_exp_user_csv_disable_city', 'no');       
		$disable_country = get_option('uwa_exp_user_csv_disable_country', 'no');     
		$disable_hbidder_mail = get_option('uwa_exp_user_csv_disable_highest_bidder_mail', 'no');
		$disable_phone = get_option('uwa_exp_user_csv_disable_phone', 'no');

		$disable_sec_hbid = get_option('uwa_exp_user_csv_disable_second_highest_bid', 'no');
		$disable_sec_hbidder = get_option('uwa_exp_user_csv_disable_second_highest_bidder', 'no');
		$disable_sec_hbidder_company = get_option('uwa_exp_user_csv_disable_second_highest_bidder_company', 'no');
		$disable_sec_hbidder_mail = get_option('uwa_exp_user_csv_disable_second_highest_bidder_mail', 'no');

		$disable_third_hbid = get_option('uwa_exp_user_csv_disable_third_highest_bid', 'no');
		$disable_third_hbidder = get_option('uwa_exp_user_csv_disable_third_highest_bidder', 'no');
		$disable_third_hbidder_company = get_option('uwa_exp_user_csv_disable_third_highest_bidder_company', 'no');
		$disable_third_hbidder_mail = get_option('uwa_exp_user_csv_disable_third_highest_bidder_mail', 'no');

		$disable_enddate = get_option('uwa_exp_user_csv_disable_end_date', 'no');
		$disable_expiry_reason = get_option('uwa_exp_user_csv_disable_expiry_reason', 'no');    

		$header_row[] = __('Auction Name', 'woo_ua');
			
		if ('yes' != $disable_pro_category) {
			$header_row[] = __('Product Category', 'woo_ua');               
		}
		
		/* Add dynamic headers */
		if ('yes' == $enable_product_attr) {

			if (is_array($attribute_names) && count($attribute_names) > 0) {
				$final_attribute_names = array_values(array_unique($attribute_names));

				foreach ($final_attribute_names as $attr_name) {

					$pro_attribute_label =  wc_attribute_label($attr_name);
					// $header_row[] = __('Attr-', 'woo_ua') . $pro_attribute_label;
					$header_row[] = $pro_attribute_label;

				} /* end of foreach */

			} /* end of if */

		} /* end of if */

		$header_row[] = __('Currency Symbol', 'woo_ua');

		if ('yes' != $disable_hbid) {
			$header_row[] = __('Highest Bid', 'woo_ua');                
		}
		if ('yes' != $disable_hbidder) {
			$header_row[] = __('Highest Bidder', 'woo_ua');             
		}
		if ('yes' != $disable_hbidder_company) {
			$header_row[] = __('Highest Bidder Company', 'woo_ua');             
		}
		if ('yes' != $disable_first_name) {
			$header_row[] = __('First Name', 'woo_ua');             
		}
		if ('yes' != $disable_last_name) {
			$header_row[] = __('Last Name', 'woo_ua');              
		}               
		if ('yes' != $disable_address1) {
			$header_row[] = __('Address1', 'woo_ua');
		}
		if ('yes' != $disable_address2) {
			$header_row[] = __('Address2', 'woo_ua');               
		}
		if ('yes' != $disable_postal_code) {
			$header_row[] = __('Postal Code', 'woo_ua');                
		}
		if ('yes' != $disable_city) {
			$header_row[] = __('City', 'woo_ua');               
		}
		if ('yes' != $disable_country) {
			$header_row[] = __('Country', 'woo_ua');                
		}
		if ('yes' != $disable_hbidder_mail) {
			$header_row[] = __('Highest Bidder Email', 'woo_ua');               
		}
		if ('yes' != $disable_phone) {
			$header_row[] = __('Phone', 'woo_ua');              
		}
		if ('yes' != $disable_sec_hbid) {
			$header_row[] = __('Second Highest Bid', 'woo_ua');             
		}
		if ('yes' != $disable_sec_hbidder) {
			$header_row[] = __('Second Highest Bidder', 'woo_ua');              
		}       
		if ('yes' != $disable_sec_hbidder_company) {
			$header_row[] = __('Second Highest Bidder Company', 'woo_ua');              
		}                   
		if ('yes' != $disable_sec_hbidder_mail) {
			$header_row[] = __('Second Highest Bidder Email', 'woo_ua');                
		}
		if ('yes' != $disable_third_hbid) {
			$header_row[] = __('Third Highest Bid', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder) {
			$header_row[] = __('Third Highest Bidder', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder_company) {
			$header_row[] = __('Third Highest Bidder Company', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder_mail) {
			$header_row[] = __('Third Highest Bidder Email', 'woo_ua');
		}
		if ('yes' != $disable_enddate) {
			$header_row[] = __('Auction End Date', 'woo_ua');
		}
		if ('yes' != $disable_expiry_reason) {
			$header_row[] = __('Expiry Reason', 'woo_ua');
		}
	}


	/* get the data related to auction export fields */
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {

			$query->the_post();

			$auction_ID = get_the_ID();

			$ending_date = get_post_meta(get_the_ID(), 'woo_ua_auction_end_date', true);
			$uwa_strtotime = strtotime($ending_date);
			$ending_date = date('d-m-Y H:i:s', $uwa_strtotime);

			$auction_name = get_the_title();
			$product_cats = wp_get_post_terms($auction_ID, 'product_cat', array( 'fields' => 'names' ));
			$category = !empty($product_cats) ? implode(', ', $product_cats) : '';
		
			/* $highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id    =' . $auction_ID . '  ORDER BY  `bid`  DESC limit 1'); */

			$highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1', $auction_ID));

			/* $currency = get_option('woocommerce_currency');
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol($currency);
			$woocommerce_currency_symbol_value = html_entity_decode($woocommerce_currency_symbol); */
				
			if ($highest_bid) {

				$highest_bid = wc_price($highest_bid);
				$highest_bid = strip_tags($highest_bid);
				$highest_bid_value = html_entity_decode($highest_bid);
				$highest_bid_value = preg_replace('/[^\d,\.]/', '', $highest_bid_value);

			} else {
				$highest_bid_value = '';
			}               

			/* $second_highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 1, 2'); */

			$second_highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1, 2', $auction_ID));

			if ($second_highest_bid) {
					
				$second_highest_bid = wc_price($second_highest_bid);
				$second_highest_bid = strip_tags($second_highest_bid);
				$second_highest_bid_value = html_entity_decode($second_highest_bid);
				$second_highest_bid_value = preg_replace('/[^\d,\.]/', '', $second_highest_bid_value);

			} else {
				$second_highest_bid_value = '';
			}               


			/* $second_highest_bidder = $wpdb->get_var( 'SELECT userid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 1,2'); */

			/* $third_highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 2, 3'); */

			$second_highest_bidder = $wpdb->get_var($wpdb->prepare('SELECT userid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1,2', $auction_ID));

			$third_highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id = %d ORDER BY bid DESC limit 2,3', $auction_ID));

			if ($third_highest_bid) {                   

				$third_highest_bid = wc_price($third_highest_bid);
				$third_highest_bid = strip_tags($third_highest_bid);
				$third_highest_bid_value = html_entity_decode($third_highest_bid);
				$third_highest_bid_value = preg_replace('/[^\d,\.]/', '', $third_highest_bid_value);

			} else {
				$third_highest_bid_value = '';
			}               

			/* $third_highest_bidder = $wpdb->get_var( 'SELECT userid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 2, 3'); */

			$third_highest_bidder = $wpdb->get_var($wpdb->prepare('SELECT userid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 2,3', $auction_ID));

			$row['expiry_reason'] = '';
			$bidder_name = '';
			$bidder_email = '';
			$fail_reason = get_post_meta($auction_ID, 'woo_ua_auction_fail_reason', true);
			$reason_closed = get_post_meta($auction_ID, 'woo_ua_auction_closed', true);
			$current_bidder = get_post_meta($auction_ID, 'woo_ua_auction_current_bider', true);
			$order_id = get_post_meta($auction_ID, 'woo_ua_order_id', true);

			if ($current_bidder) {
				$obj_user = get_userdata($current_bidder);
				if ($obj_user) {
					$bidder_name = $obj_user->display_name;
					$bidder_email = $obj_user->user_email;
				}
			}

			$second_bidder_email = '';
			$second_bidder_name = '';
			$second_highest_bidder_company = '';

			if ($second_highest_bidder) {       
				$obj_user = get_userdata($second_highest_bidder);                           
				if ($obj_user) {
					$second_bidder_name = $obj_user->display_name;
					$second_bidder_email = $obj_user->user_email;
					$second_highest_bidder_company = $obj_user->billing_company;

				}
			}

			$third_bidder_email = '';
			$third_bidder_name = '';
			$third_highest_bidder_company = '';

			if ($third_highest_bidder) {        
				$obj_user = get_userdata($third_highest_bidder);
				if ($obj_user) {
					$third_bidder_name = $obj_user->display_name;
					$third_bidder_email = $obj_user->user_email;
					$third_highest_bidder_company = $obj_user->billing_company;
				}
			}

			if (1 == $fail_reason) {                    
				$row['expiry_reason'] = __('No Bid', 'woo_ua');
					
			} elseif (2 == $fail_reason) {                  
				$row['expiry_reason'] = __('Reserve Not Met', 'woo_ua');

			} elseif (3 == $reason_closed) {
				$row['expiry_reason'] = __('Sold', 'woo_ua');   

				if ( $order_id ) {
					$row['expiry_reason'] .= ', ' . __('Order ID:', 'woo_ua') . ' ' . $order_id;
				}
			} else {

				$row['expiry_reason'] .= __('Won', 'woo_ua') . ' - ';
				$row['expiry_reason'] .= __('Highest bidder was', 'woo_ua') . ' ';
				$row['expiry_reason'] .= $bidder_name;          

				if ( $order_id ) {                      
					$row['expiry_reason'] .= ', ' . __('Order ID:', 'woo_ua') . ' ' . $order_id;
				}
			}

			$first_name = '';
			$last_name = '';
			$billing_address_1 = '';
			$billing_address_2 = '';
			$billing_postcode = '';
			$billing_city = '';
			$billing_country = '';
			$billing_company = '';                  
			$phone = '';

			if ($current_bidder > 0) {
				$current_bidder_data = get_userdata($current_bidder);
				if (is_object($current_bidder_data)) {

					/* $bidder_name = $current_bidder_data->display_name;
					$bidder_email = $current_bidder_data->user_email; */

					$first_name = $current_bidder_data->billing_first_name;
					$last_name = $current_bidder_data->billing_last_name;
					$billing_address_1 = $current_bidder_data->billing_address_1;
					$billing_address_2 = $current_bidder_data->billing_address_2;
					$billing_postcode = $current_bidder_data->billing_postcode;
					$billing_city = $current_bidder_data->billing_city;
					$billing_country = $current_bidder_data->billing_country;
					$billing_company = $current_bidder_data->billing_company;
					$phone = $current_bidder_data->billing_phone;
				}
			}


			// Restore original Post Data
			wp_reset_postdata();

			$display_row = array();
			$display_row[] = $auction_name;

			if ('yes' != $disable_pro_category) {               
				$display_row[] = $category;
			}

			/* Add dynamic data */
			if ('yes' == $enable_product_attr) {

				if (is_array($final_attribute_names) && count($final_attribute_names) > 0) {   


					$product_id = $auction_ID;         

					foreach ($final_attribute_names as $attr_name) {
						
					    $taxonomy_id = wc_attribute_taxonomy_id_by_name($attr_name);
					    if ($taxonomy_id) {		

					    	/* taxonomy based attributes */
							$terms = wc_get_product_terms($product_id, $attr_name, 
								array( 'fields' => 'names' ));
							$display_row[] = !empty($terms) ? implode(', ', $terms) : '';

						} else {

							/* custom attributes */
							$product = wc_get_product($product_id);

							$attributes = $product->get_attributes();											
							if (isset($attributes[$attr_name])) {
								$single_attribute = $attributes[$attr_name];
								$attr_options = $single_attribute->get_options();
								$display_row[] = !empty($attr_options) ? implode(', ', $attr_options) : '';

							} else {
								$display_row[] = ""; /* don't remove it */
							}

						}  /* end of else */        

					} /* end of foreach */

				} /* end of if */

			} /* end of if */  

			$currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
			$display_row[] = $currency_symbol;

			if ('yes' != $disable_hbid) {
				$display_row[] = $highest_bid_value;
			}
			if ('yes' != $disable_hbidder) {
				$display_row[] = $bidder_name;
			}
			if ('yes' != $disable_hbidder_company) {
				$display_row[] = $billing_company;
			}
			if ('yes' != $disable_first_name) {
				$display_row[] = $first_name;
			}
			if ('yes' != $disable_last_name) {
				$display_row[] = $last_name;
			}   
			if ('yes' != $disable_address1) {
				$display_row[] = $billing_address_1;
			}
			if ('yes' != $disable_address2) {
				$display_row[] = $billing_address_2;
			}
			if ('yes' != $disable_postal_code) {
				$display_row[] = $billing_postcode;
			}
			if ('yes' != $disable_city) {
				$display_row[] = $billing_city;
			}
			if ('yes' != $disable_country) {
				$display_row[] = $billing_country;
			}
			if ('yes' != $disable_hbidder_mail) {           
				$display_row[] = $bidder_email;
			}
			if ('yes' != $disable_phone) {
				$display_row[] = $phone;
			}
			if ('yes' != $disable_sec_hbid) {
				$display_row[] = $second_highest_bid_value;
			}
			if ('yes' != $disable_sec_hbidder) {
				$display_row[] = $second_bidder_name;
			}
			if ('yes' != $disable_sec_hbidder_company) {
				$display_row[] = $second_highest_bidder_company;
			}
			if ('yes' != $disable_sec_hbidder_mail) {               
				$display_row[] = $second_bidder_email;
			}
			if ('yes' != $disable_third_hbid) {
				$display_row[] = $third_highest_bid_value;
			}
			if ('yes' != $disable_third_hbidder) {              
				$display_row[] = $third_bidder_name;
			}
			if ('yes' != $disable_third_hbidder_company) {
				$display_row[] = $third_highest_bidder_company;
			}
			if ('yes' != $disable_third_hbidder_mail) {
				$display_row[] = $third_bidder_email;
			}
			if ('yes' != $disable_enddate) {
				$display_row[] = $ending_date;
			}
			if ('yes' != $disable_expiry_reason) {
				$display_row[] = $row['expiry_reason'];
			}

			
			$data_rows[] = $display_row;

		} /* end of while */

	} else {            
		echo esc_html__('No Posts Found', 'woo_ua');
	}
		
	$__csvoutput = @fopen( 'php://output', 'w' );
	fprintf( $__csvoutput, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	
	header('Content-Description: File Transfer');
	header('Content-Encoding: UTF-8');
	header('Content-Type: text/csv; charset=utf-8');
	header("Content-Disposition: attachment; filename={$filename}");
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: public');

	$delimiter = get_option('uwa_csv_delimiter', ',');
	fputcsv($__csvoutput, $header_row, $delimiter); // Use dynamic delimiter
	foreach ($data_rows as $data_row) {
		fputcsv($__csvoutput, $data_row, $delimiter); // Use dynamic delimiter
	}
	fclose( $__csvoutput );

	ob_end_flush();
	die();
}

function uwa_user_all_expired_auctions_download_xls() {
	
	if (ob_get_length() > 0) {
		ob_clean();
	}
	ob_start();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty( $sitename ) ) {
		$sitename .= '_';
	}

	/* $filename = $sitename . 'all-expired-auctions-' . date( 'Y-m-d-H-i-s' ) . '.xls'; */

	$midtext = __('all-expired-auctions', 'woo_ua');
	$filename = $sitename . $midtext . '_' . date( 'd.m.Y' ) . '.xlsx';  
		
	global $wpdb;

	$data_rows = array();
	$header_row = array();
	$display_row = array();
	$curr_user_id = get_current_user_id();

	$tax_query = array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' );

	if (isset($_REQUEST['category']) && !empty($_REQUEST['category'])) {

		if ('all' != $_REQUEST['category'] && $_REQUEST['category'] > 0) {

			$tax_query = array(
				'relation' => 'AND', // Use 'AND' or 'OR' 
				array(
					'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
					'field'    => 'term_id',        // Use 'slug', 'name', or 'term_id'
					'terms'    => sanitize_text_field($_REQUEST['category']), // Replace with category slug
				),
				array(
					'taxonomy' => 'product_type', // WooCommerce product type taxonomy
					'field'    => 'slug',
					'terms'    => 'auction', // Replace with product type term
				),
			);
		}
	}   

	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => '-1',
		'tax_query' => $tax_query,
		'orderby' => 'title',
		'order' => 'ASC',
		'auction_arhive' => true,
	);

	/*  if (!empty($_REQUEST['users_auctions']) && $_REQUEST['users_auctions'] == 'true') {
		$args['author__not_in'] = $curr_user_id;
	} else {
		$args['author'] = $curr_user_id;
	} */
	
	$args['meta_query'] = array(
				'relation' => 'AND',
					array(
						'key' => 'woo_ua_auction_closed',
						'value' => array( '1', '2', '3', '4' ),
						'compare' => 'IN',
					),
				);

	$query = new WP_Query( $args );

	/* create heading for export files */

	if ( $query->have_posts() ) {

		$attribute_names = array();		
		$final_attribute_names = array();
		$enable_product_attr = get_option('uwa_exp_user_csv_enable_pro_attr', 'no');


		if ('yes' == $enable_product_attr) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$product_id = get_the_ID();
				$product = wc_get_product($product_id);

				/* get product attributes term names */
				$attributes = $product->get_attributes();
				if (is_array($attributes) && count($attributes) > 0) {
					foreach ($attributes as $attribute_slug => $attribute) {

						$attribute_names[] = $attribute_slug;
						// $attribute_names[] = $attribute->get_id();
						// $attribute_names[] = $attribute->get_name();
					}
				}
			} /* end of while */

		} /* end of if - enabled attr */

		
		$disable_pro_category = get_option('uwa_exp_user_csv_disable_product_category', 'no');
		$disable_hbid = get_option('uwa_exp_user_csv_disable_highest_bid', 'no');
		$disable_hbidder = get_option('uwa_exp_user_csv_disable_highest_bidder', 'no');
		$disable_hbidder_company = get_option('uwa_exp_user_csv_disable_company', 'no');
		$disable_first_name = get_option('uwa_exp_user_csv_disable_first_name', 'no');
		$disable_last_name = get_option('uwa_exp_user_csv_disable_last_name', 'no');
		$disable_address1 = get_option('uwa_exp_user_csv_disable_address1', 'no');
		$disable_address2 = get_option('uwa_exp_user_csv_disable_address2', 'no');
		$disable_postal_code = get_option('uwa_exp_user_csv_disable_postal_code', 'no');
		$disable_city = get_option('uwa_exp_user_csv_disable_city', 'no');       
		$disable_country = get_option('uwa_exp_user_csv_disable_country', 'no');     
		$disable_hbidder_mail = get_option('uwa_exp_user_csv_disable_highest_bidder_mail', 'no');
		$disable_phone = get_option('uwa_exp_user_csv_disable_phone', 'no');

		$disable_sec_hbid = get_option('uwa_exp_user_csv_disable_second_highest_bid', 'no');
		$disable_sec_hbidder = get_option('uwa_exp_user_csv_disable_second_highest_bidder', 'no');
		$disable_sec_hbidder_company = get_option('uwa_exp_user_csv_disable_second_highest_bidder_company', 'no');
		$disable_sec_hbidder_mail = get_option('uwa_exp_user_csv_disable_second_highest_bidder_mail', 'no');

		$disable_third_hbid = get_option('uwa_exp_user_csv_disable_third_highest_bid', 'no');
		$disable_third_hbidder = get_option('uwa_exp_user_csv_disable_third_highest_bidder', 'no');
		$disable_third_hbidder_company = get_option('uwa_exp_user_csv_disable_third_highest_bidder_company', 'no');
		$disable_third_hbidder_mail = get_option('uwa_exp_user_csv_disable_third_highest_bidder_mail', 'no');

		$disable_enddate = get_option('uwa_exp_user_csv_disable_end_date', 'no');
		$disable_expiry_reason = get_option('uwa_exp_user_csv_disable_expiry_reason', 'no');    

		$header_row[] = __('Auction Name', 'woo_ua');
			
		if ('yes' != $disable_pro_category) {
			$header_row[] = __('Product Category', 'woo_ua');               
		}
		
		/* Add dynamic headers */
		if ('yes' == $enable_product_attr) {

			if (is_array($attribute_names) && count($attribute_names) > 0) {
				$final_attribute_names = array_values(array_unique($attribute_names));

				foreach ($final_attribute_names as $attr_name) {

					$pro_attribute_label =  wc_attribute_label($attr_name);
					// $header_row[] = __('Attr-', 'woo_ua') . $pro_attribute_label;
					$header_row[] = $pro_attribute_label;

				} /* end of foreach */

			} /* end of if */

		} /* end of if */

		$header_row[] = __('Currency Symbol', 'woo_ua');

		if ('yes' != $disable_hbid) {
			$header_row[] = __('Highest Bid', 'woo_ua');                
		}
		if ('yes' != $disable_hbidder) {
			$header_row[] = __('Highest Bidder', 'woo_ua');             
		}
		if ('yes' != $disable_hbidder_company) {
			$header_row[] = __('Highest Bidder Company', 'woo_ua');             
		}
		if ('yes' != $disable_first_name) {
			$header_row[] = __('First Name', 'woo_ua');             
		}
		if ('yes' != $disable_last_name) {
			$header_row[] = __('Last Name', 'woo_ua');              
		}               
		if ('yes' != $disable_address1) {
			$header_row[] = __('Address1', 'woo_ua');
		}
		if ('yes' != $disable_address2) {
			$header_row[] = __('Address2', 'woo_ua');               
		}
		if ('yes' != $disable_postal_code) {
			$header_row[] = __('Postal Code', 'woo_ua');                
		}
		if ('yes' != $disable_city) {
			$header_row[] = __('City', 'woo_ua');               
		}
		if ('yes' != $disable_country) {
			$header_row[] = __('Country', 'woo_ua');                
		}
		if ('yes' != $disable_hbidder_mail) {
			$header_row[] = __('Highest Bidder Email', 'woo_ua');               
		}
		if ('yes' != $disable_phone) {
			$header_row[] = __('Phone', 'woo_ua');              
		}
		if ('yes' != $disable_sec_hbid) {
			$header_row[] = __('Second Highest Bid', 'woo_ua');             
		}
		if ('yes' != $disable_sec_hbidder) {
			$header_row[] = __('Second Highest Bidder', 'woo_ua');              
		}       
		if ('yes' != $disable_sec_hbidder_company) {
			$header_row[] = __('Second Highest Bidder Company', 'woo_ua');              
		}                   
		if ('yes' != $disable_sec_hbidder_mail) {
			$header_row[] = __('Second Highest Bidder Email', 'woo_ua');                
		}
		if ('yes' != $disable_third_hbid) {
			$header_row[] = __('Third Highest Bid', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder) {
			$header_row[] = __('Third Highest Bidder', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder_company) {
			$header_row[] = __('Third Highest Bidder Company', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder_mail) {
			$header_row[] = __('Third Highest Bidder Email', 'woo_ua');
		}
		if ('yes' != $disable_enddate) {
			$header_row[] = __('Auction End Date', 'woo_ua');
		}
		if ('yes' != $disable_expiry_reason) {
			$header_row[] = __('Expiry Reason', 'woo_ua');
		}
	}


	/* get the data related to auction export fields */
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {

			$query->the_post();

			$auction_ID = get_the_ID();
			$ending_date = get_post_meta(get_the_ID(), 'woo_ua_auction_end_date', true);
			$uwa_strtotime = strtotime($ending_date);
			$ending_date = date('d-m-Y H:i:s', $uwa_strtotime);

			$auction_name = get_the_title();
			$product_cats = wp_get_post_terms($auction_ID, 'product_cat', array( 'fields' => 'names' ));
			$category = !empty($product_cats) ? implode(', ', $product_cats) : '';
		
			/* $highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id    =' . $auction_ID . '  ORDER BY  `bid`  DESC limit 1'); */

			$highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1', $auction_ID));

			/* $currency = get_option('woocommerce_currency');
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol($currency);
			$woocommerce_currency_symbol_value = html_entity_decode($woocommerce_currency_symbol); */

			$decimals = wc_get_price_decimals();			
				
			if ($highest_bid) {

				// $highest_bid = wc_price($highest_bid);
				// $highest_bid = strip_tags($highest_bid);
				// $highest_bid_value = html_entity_decode($highest_bid);
				// $highest_bid_value = preg_replace('/^\D+/u', '', $highest_bid_value);

				$highest_bid = number_format($highest_bid, 2, '.', '');
				$highest_bid_value = $highest_bid;

			} else {
				$highest_bid_value = '';
			}

			/* $second_highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 1, 2'); */

			$second_highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1, 2', $auction_ID));

			if ($second_highest_bid) {
					
				// $second_highest_bid = wc_price($second_highest_bid);
				// $second_highest_bid = strip_tags($second_highest_bid);
				// $second_highest_bid_value = html_entity_decode($second_highest_bid);
				// $second_highest_bid_value = preg_replace('/^\D+/u', '', $second_highest_bid_value);

				$second_highest_bid = number_format($second_highest_bid, 2, '.', ''); 
				$second_highest_bid_value = $second_highest_bid;

			} else {
				$second_highest_bid_value = '';
			}               


			/* $second_highest_bidder = $wpdb->get_var( 'SELECT userid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 1,2'); */

			/* $third_highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 2, 3'); */

			$second_highest_bidder = $wpdb->get_var($wpdb->prepare('SELECT userid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1,2', $auction_ID));

			$third_highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id = %d ORDER BY bid DESC limit 2,3', $auction_ID));

			if ($third_highest_bid) {                   

				// $third_highest_bid = wc_price($third_highest_bid);
				// $third_highest_bid = strip_tags($third_highest_bid);
				// $third_highest_bid_value = html_entity_decode($third_highest_bid);
				// $third_highest_bid_value = preg_replace('/^\D+/u', '', $third_highest_bid_value);

				$third_highest_bid = number_format($third_highest_bid, 2, '.', '');
				$third_highest_bid_value = $third_highest_bid;

			} else {
				$third_highest_bid_value = '';
			}               

			/* $third_highest_bidder = $wpdb->get_var( 'SELECT userid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 2, 3'); */

			$third_highest_bidder = $wpdb->get_var($wpdb->prepare('SELECT userid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 2,3', $auction_ID));

			$row['expiry_reason'] = '';
			$bidder_name = '';
			$bidder_email = '';
			$fail_reason = get_post_meta($auction_ID, 'woo_ua_auction_fail_reason', true);
			$reason_closed = get_post_meta($auction_ID, 'woo_ua_auction_closed', true);
			$current_bidder = get_post_meta($auction_ID, 'woo_ua_auction_current_bider', true);
			$order_id = get_post_meta($auction_ID, 'woo_ua_order_id', true);

			if ($current_bidder) {
				$obj_user = get_userdata($current_bidder);
				if ($obj_user) {
					$bidder_name = $obj_user->display_name;
					$bidder_email = $obj_user->user_email;
				}
			}

			$second_bidder_email = '';
			$second_bidder_name = '';
			$second_highest_bidder_company = '';

			if ($second_highest_bidder) {       
				$obj_user = get_userdata($second_highest_bidder);                           
				if ($obj_user) {
					$second_bidder_name = $obj_user->display_name;
					$second_bidder_email = $obj_user->user_email;
					$second_highest_bidder_company = $obj_user->billing_company;

				}
			}

			$third_bidder_email = '';
			$third_bidder_name = '';
			$third_highest_bidder_company = '';

			if ($third_highest_bidder) {        
				$obj_user = get_userdata($third_highest_bidder);
				if ($obj_user) {
					$third_bidder_name = $obj_user->display_name;
					$third_bidder_email = $obj_user->user_email;
					$third_highest_bidder_company = $obj_user->billing_company;
				}
			}

			if (1 == $fail_reason) {                    
				$row['expiry_reason'] = __('No Bid', 'woo_ua');
					
			} elseif (2 == $fail_reason) {                  
				$row['expiry_reason'] = __('Reserve Not Met', 'woo_ua');

			} elseif (3 == $reason_closed) {
				$row['expiry_reason'] = __('Sold', 'woo_ua');   

				if ( $order_id ) {
					$row['expiry_reason'] .= ', ' . __('Order ID:', 'woo_ua') . ' ' . $order_id;
				}
			} else {

				$row['expiry_reason'] .= __('Won', 'woo_ua') . ' - ';
				$row['expiry_reason'] .= __('Highest bidder was', 'woo_ua') . ' ';
				$row['expiry_reason'] .= $bidder_name;          

				if ( $order_id ) {                      
					$row['expiry_reason'] .= ', ' . __('Order ID:', 'woo_ua') . ' ' . $order_id;
				}
			}

			$first_name = '';
			$last_name = '';
			$billing_address_1 = '';
			$billing_address_2 = '';
			$billing_postcode = '';
			$billing_city = '';
			$billing_country = '';
			$billing_company = '';                  
			$phone = '';

			if ($current_bidder > 0) {
				$current_bidder_data = get_userdata($current_bidder);
				if (is_object($current_bidder_data)) {

					/* $bidder_name = $current_bidder_data->display_name;
					$bidder_email = $current_bidder_data->user_email; */

					$first_name = $current_bidder_data->billing_first_name;
					$last_name = $current_bidder_data->billing_last_name;
					$billing_address_1 = $current_bidder_data->billing_address_1;
					$billing_address_2 = $current_bidder_data->billing_address_2;
					$billing_postcode = $current_bidder_data->billing_postcode;
					$billing_city = $current_bidder_data->billing_city;
					$billing_country = $current_bidder_data->billing_country;
					$billing_company = $current_bidder_data->billing_company;
					$phone = $current_bidder_data->billing_phone;
				}
			}


			// Restore original Post Data
			wp_reset_postdata();

				$display_row = array();


			$display_row[] = $auction_name;

			if ('yes' != $disable_pro_category) {               
				$display_row[] = $category;
			}


				$product_id = $auction_ID;			


							/* Add dynamic data */
							if ('yes' == $enable_product_attr) {

								if (is_array($final_attribute_names) && count($final_attribute_names) > 0) {            

									foreach ($final_attribute_names as $attr_name) {
										
									   $taxonomy_id = wc_attribute_taxonomy_id_by_name($attr_name);

									   if ($taxonomy_id) {

									    	/* taxonomy based attributes */
											$terms = wc_get_product_terms($product_id, $attr_name, 
												array( 'fields' => 'names' ));

											/* check for european number and convert it to normal number */			
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
													$display_row[] = implode(', ', $terms);
												}											

												if (count($terms) > 1) {
													$display_row[] = '"' . implode('", "', $terms) . '"';
												} 

											} else {

												$display_row[] = "";
											}

											/*$display_row[] = !empty($terms) ? implode(', ', $terms) : '';*/


										} else {

											/* custom attributes */
											$attributes = $product->get_attributes();											
											if (isset($attributes[$attr_name])) {
												$single_attribute = $attributes[$attr_name];
												$attr_options = $single_attribute->get_options();												
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
														$display_row[] = implode(', ', $attr_options);
													}

													if (count($attr_options) > 1) {
														$display_row[] = '"' . implode('", "', $attr_options) . '"';
													} 


												} else {

													$display_row[] = "";
												}


												/* $display_row[] = !empty($attr_options) ? implode(' & ', $attr_options) : ''; */

											} else {
												$display_row[] = ""; /* don't remove it */
											}
											
										}  /* end of else */        

									} /* end of foreach */

								} /* end of if */

							} /* end of if */  

			
			$currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
			$display_row[] = $currency_symbol;

			if ('yes' != $disable_hbid) {
				$display_row[] = $highest_bid_value;
			}
			if ('yes' != $disable_hbidder) {
				$display_row[] = $bidder_name;
			}
			if ('yes' != $disable_hbidder_company) {
				$display_row[] = $billing_company;
			}
			if ('yes' != $disable_first_name) {
				$display_row[] = $first_name;
			}
			if ('yes' != $disable_last_name) {
				$display_row[] = $last_name;
			}   
			if ('yes' != $disable_address1) {
				$display_row[] = $billing_address_1;
			}
			if ('yes' != $disable_address2) {
				$display_row[] = $billing_address_2;
			}
			if ('yes' != $disable_postal_code) {
				$display_row[] = $billing_postcode;
			}
			if ('yes' != $disable_city) {
				$display_row[] = $billing_city;
			}
			if ('yes' != $disable_country) {
				$display_row[] = $billing_country;
			}
			if ('yes' != $disable_hbidder_mail) {           
				$display_row[] = $bidder_email;
			}
			if ('yes' != $disable_phone) {
				$display_row[] = $phone;
			}
			if ('yes' != $disable_sec_hbid) {
				$display_row[] = $second_highest_bid_value;
			}
			if ('yes' != $disable_sec_hbidder) {
				$display_row[] = $second_bidder_name;
			}
			if ('yes' != $disable_sec_hbidder_company) {
				$display_row[] = $second_highest_bidder_company;
			}
			if ('yes' != $disable_sec_hbidder_mail) {               
				$display_row[] = $second_bidder_email;
			}
			if ('yes' != $disable_third_hbid) {
				$display_row[] = $third_highest_bid_value;
			}
			if ('yes' != $disable_third_hbidder) {              
				$display_row[] = $third_bidder_name;
			}
			if ('yes' != $disable_third_hbidder_company) {
				$display_row[] = $third_highest_bidder_company;
			}
			if ('yes' != $disable_third_hbidder_mail) {
				$display_row[] = $third_bidder_email;
			}
			if ('yes' != $disable_enddate) {
				$display_row[] = $ending_date;
			}
			if ('yes' != $disable_expiry_reason) {
				$display_row[] = $row['expiry_reason'];
			}

			
			$data_rows[] = $display_row;

		} /* end of while */

	} else {            
		echo esc_html__('No Posts Found', 'woo_ua');
	}

	// Send headers before file content
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment; filename={$filename}");
	header('Cache-Control: max-age=0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');

	$sheet_name = __('Sheet1', 'woo_ua');

	require_once UW_AUCTION_PRO_DIR . '/library/simplexlsx/vendor/autoload.php';

	array_unshift($data_rows, $header_row);

	$xlsx = new SimpleXLSXGen();
    $xlsx->addSheet($data_rows, $sheet_name); // Add sheet with translated name

	// Output the Excel file directly to the browser
	$xlsx->downloadAs($filename);

	ob_end_flush();
	die();
}

function uwa_user_all_expired_auctions_download_pdf() {
	
	if (ob_get_length() > 0) {
		ob_clean();
	}
	ob_start();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty( $sitename ) ) {
		$sitename .= '_';
	}

	/* $filename = $sitename . 'all-expired-auctions-' . date( 'Y-m-d-H-i-s' ) . '.pdf'; */

	$midtext = __('all-expired-auctions', 'woo_ua');
	$filename = $sitename . $midtext . '_' . date( 'd.m.Y' ) . '.pdf';
		
	global $wpdb;

	$data_rows = array();
	$header_row = array();
	$display_row = array();
	$curr_user_id = get_current_user_id();

	$tax_query = array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' );

	if (isset($_REQUEST['category']) && !empty($_REQUEST['category'])) {

		if ('all' != $_REQUEST['category'] && $_REQUEST['category'] > 0) {

			$tax_query = array(
				'relation' => 'AND', // Use 'AND' or 'OR' 
				array(
					'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
					'field'    => 'term_id',        // Use 'slug', 'name', or 'term_id'
					'terms'    => sanitize_text_field($_REQUEST['category']), // Replace with category slug
				),
				array(
					'taxonomy' => 'product_type', // WooCommerce product type taxonomy
					'field'    => 'slug',
					'terms'    => 'auction', // Replace with product type term
				),
			);
		}
	}   

	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => '-1',
		'tax_query' => $tax_query,
		'orderby' => 'title',
		'order' => 'ASC',
		'auction_arhive' => true,
	);

	/*  if (!empty($_REQUEST['users_auctions']) && $_REQUEST['users_auctions'] == 'true') {
		$args['author__not_in'] = $curr_user_id;
	} else {
		$args['author'] = $curr_user_id;
	} */
	
	$args['meta_query'] = array(
				'relation' => 'AND',
					array(
						'key' => 'woo_ua_auction_closed',
						'value' => array( '1', '2', '3', '4' ),
						'compare' => 'IN',
					),
				);

	$query = new WP_Query( $args );

	/* create heading for export files */

	if ( $query->have_posts() ) {

		$attribute_names = array();		
		$final_attribute_names = array();
		$enable_product_attr = get_option('uwa_exp_user_csv_enable_pro_attr', 'no');


		if ('yes' == $enable_product_attr) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$product_id = get_the_ID();
				$product = wc_get_product($product_id);

				/* get product attributes term names */
				$attributes = $product->get_attributes();
				if (is_array($attributes) && count($attributes) > 0) {
					foreach ($attributes as $attribute_slug => $attribute) {

						$attribute_names[] = $attribute_slug;
						// $attribute_names[] = $attribute->get_id();
						// $attribute_names[] = $attribute->get_name();
					}
				}
			} /* end of while */

		} /* end of if - enabled attr */

		
		$disable_pro_category = get_option('uwa_exp_user_csv_disable_product_category', 'no');
		$disable_hbid = get_option('uwa_exp_user_csv_disable_highest_bid', 'no');
		$disable_hbidder = get_option('uwa_exp_user_csv_disable_highest_bidder', 'no');
		$disable_hbidder_company = get_option('uwa_exp_user_csv_disable_company', 'no');
		$disable_first_name = get_option('uwa_exp_user_csv_disable_first_name', 'no');
		$disable_last_name = get_option('uwa_exp_user_csv_disable_last_name', 'no');
		$disable_address1 = get_option('uwa_exp_user_csv_disable_address1', 'no');
		$disable_address2 = get_option('uwa_exp_user_csv_disable_address2', 'no');
		$disable_postal_code = get_option('uwa_exp_user_csv_disable_postal_code', 'no');
		$disable_city = get_option('uwa_exp_user_csv_disable_city', 'no');       
		$disable_country = get_option('uwa_exp_user_csv_disable_country', 'no');     
		$disable_hbidder_mail = get_option('uwa_exp_user_csv_disable_highest_bidder_mail', 'no');
		$disable_phone = get_option('uwa_exp_user_csv_disable_phone', 'no');

		$disable_sec_hbid = get_option('uwa_exp_user_csv_disable_second_highest_bid', 'no');
		$disable_sec_hbidder = get_option('uwa_exp_user_csv_disable_second_highest_bidder', 'no');
		$disable_sec_hbidder_company = get_option('uwa_exp_user_csv_disable_second_highest_bidder_company', 'no');
		$disable_sec_hbidder_mail = get_option('uwa_exp_user_csv_disable_second_highest_bidder_mail', 'no');

		$disable_third_hbid = get_option('uwa_exp_user_csv_disable_third_highest_bid', 'no');
		$disable_third_hbidder = get_option('uwa_exp_user_csv_disable_third_highest_bidder', 'no');
		$disable_third_hbidder_company = get_option('uwa_exp_user_csv_disable_third_highest_bidder_company', 'no');
		$disable_third_hbidder_mail = get_option('uwa_exp_user_csv_disable_third_highest_bidder_mail', 'no');

		$disable_enddate = get_option('uwa_exp_user_csv_disable_end_date', 'no');
		$disable_expiry_reason = get_option('uwa_exp_user_csv_disable_expiry_reason', 'no');    

		$header_row[] = __('Auction Name', 'woo_ua');
			
		if ('yes' != $disable_pro_category) {
			$header_row[] = __('Product Category', 'woo_ua');               
		}

		
		/* Add dynamic headers */
		if ('yes' == $enable_product_attr) {

			if (is_array($attribute_names) && count($attribute_names) > 0) {
				$final_attribute_names = array_values(array_unique($attribute_names));

				foreach ($final_attribute_names as $attr_name) {

					$pro_attribute_label =  wc_attribute_label($attr_name);
					// $header_row[] = __('Attr-', 'woo_ua') . $pro_attribute_label;
					$header_row[] = $pro_attribute_label;

				} /* end of foreach */

			} /* end of if */

		} /* end of if */


		if ('yes' != $disable_hbid) {
			$header_row[] = __('Highest Bid', 'woo_ua');                
		}
		if ('yes' != $disable_hbidder) {
			$header_row[] = __('Highest Bidder', 'woo_ua');             
		}
		if ('yes' != $disable_hbidder_company) {
			$header_row[] = __('Highest Bidder Company', 'woo_ua');             
		}
		if ('yes' != $disable_first_name) {
			$header_row[] = __('First Name', 'woo_ua');             
		}
		if ('yes' != $disable_last_name) {
			$header_row[] = __('Last Name', 'woo_ua');              
		}               
		if ('yes' != $disable_address1) {
			$header_row[] = __('Address1', 'woo_ua');
		}
		if ('yes' != $disable_address2) {
			$header_row[] = __('Address2', 'woo_ua');               
		}
		if ('yes' != $disable_postal_code) {
			$header_row[] = __('Postal Code', 'woo_ua');                
		}
		if ('yes' != $disable_city) {
			$header_row[] = __('City', 'woo_ua');               
		}
		if ('yes' != $disable_country) {
			$header_row[] = __('Country', 'woo_ua');                
		}
		if ('yes' != $disable_hbidder_mail) {
			$header_row[] = __('Highest Bidder Email', 'woo_ua');               
		}
		if ('yes' != $disable_phone) {
			$header_row[] = __('Phone', 'woo_ua');              
		}
		if ('yes' != $disable_sec_hbid) {
			$header_row[] = __('Second Highest Bid', 'woo_ua');             
		}
		if ('yes' != $disable_sec_hbidder) {
			$header_row[] = __('Second Highest Bidder', 'woo_ua');              
		}       
		if ('yes' != $disable_sec_hbidder_company) {
			$header_row[] = __('Second Highest Bidder Company', 'woo_ua');              
		}                   
		if ('yes' != $disable_sec_hbidder_mail) {
			$header_row[] = __('Second Highest Bidder Email', 'woo_ua');                
		}
		if ('yes' != $disable_third_hbid) {
			$header_row[] = __('Third Highest Bid', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder) {
			$header_row[] = __('Third Highest Bidder', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder_company) {
			$header_row[] = __('Third Highest Bidder Company', 'woo_ua');
		}
		if ('yes' != $disable_third_hbidder_mail) {
			$header_row[] = __('Third Highest Bidder Email', 'woo_ua');
		}
		if ('yes' != $disable_enddate) {
			$header_row[] = __('Auction End Date', 'woo_ua');
		}
		if ('yes' != $disable_expiry_reason) {
			$header_row[] = __('Expiry Reason', 'woo_ua');
		}
	}


	/* get the data related to auction export fields */
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {

			$query->the_post();

			$auction_ID = get_the_ID();
			$ending_date = get_post_meta(get_the_ID(), 'woo_ua_auction_end_date', true);
			$uwa_strtotime = strtotime($ending_date);
			$ending_date = date('d-m-Y H:i:s', $uwa_strtotime);
						
			$auction_name = get_the_title();
			$product_cats = wp_get_post_terms($auction_ID, 'product_cat', array( 'fields' => 'names' ));
			$category = !empty($product_cats) ? implode(', ', $product_cats) : '';
		
			/* $highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id    =' . $auction_ID . '  ORDER BY  `bid`  DESC limit 1'); */

			$highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1', $auction_ID));

			/* $currency = get_option('woocommerce_currency');
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol($currency);
			$woocommerce_currency_symbol_value = html_entity_decode($woocommerce_currency_symbol); */
				
			if ($highest_bid) {

				$highest_bid = wc_price($highest_bid);
				$highest_bid = strip_tags($highest_bid);
				$highest_bid_value = html_entity_decode($highest_bid);

			} else {
				$highest_bid_value = '';
			}               

			/* $second_highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 1, 2'); */

			$second_highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1, 2', $auction_ID));

			if ($second_highest_bid) {
					
				$second_highest_bid = wc_price($second_highest_bid);
				$second_highest_bid = strip_tags($second_highest_bid);
				$second_highest_bid_value = html_entity_decode($second_highest_bid);

			} else {
				$second_highest_bid_value = '';
			}               


			/* $second_highest_bidder = $wpdb->get_var( 'SELECT userid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 1,2'); */

			/* $third_highest_bid = $wpdb->get_var( 'SELECT bid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 2, 3'); */

			$second_highest_bidder = $wpdb->get_var($wpdb->prepare('SELECT userid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 1,2', $auction_ID));

			$third_highest_bid = $wpdb->get_var($wpdb->prepare('SELECT bid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id = %d ORDER BY bid DESC limit 2,3', $auction_ID));

			if ($third_highest_bid) {                   

				$third_highest_bid = wc_price($third_highest_bid);
				$third_highest_bid = strip_tags($third_highest_bid);
				$third_highest_bid_value = html_entity_decode($third_highest_bid);

			} else {
				$third_highest_bid_value = '';
			}               

			/* $third_highest_bidder = $wpdb->get_var( 'SELECT userid FROM ' . $wpdb->prefix . 'woo_ua_auction_log  WHERE auction_id =' . $auction_ID . '  ORDER BY  `bid` DESC limit 2, 3'); */

			$third_highest_bidder = $wpdb->get_var($wpdb->prepare('SELECT userid FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY bid DESC limit 2,3', $auction_ID));

			$row['expiry_reason'] = '';
			$bidder_name = '';
			$bidder_email = '';
			$fail_reason = get_post_meta($auction_ID, 'woo_ua_auction_fail_reason', true);
			$reason_closed = get_post_meta($auction_ID, 'woo_ua_auction_closed', true);
			$current_bidder = get_post_meta($auction_ID, 'woo_ua_auction_current_bider', true);
			$order_id = get_post_meta($auction_ID, 'woo_ua_order_id', true);

			if ($current_bidder) {
				$obj_user = get_userdata($current_bidder);
				if ($obj_user) {
					$bidder_name = $obj_user->display_name;
					$bidder_email = $obj_user->user_email;
				}
			}

			$second_bidder_email = '';
			$second_bidder_name = '';
			$second_highest_bidder_company = '';

			if ($second_highest_bidder) {       
				$obj_user = get_userdata($second_highest_bidder);                           
				if ($obj_user) {
					$second_bidder_name = $obj_user->display_name;
					$second_bidder_email = $obj_user->user_email;
					$second_highest_bidder_company = $obj_user->billing_company;

				}
			}

			$third_bidder_email = '';
			$third_bidder_name = '';
			$third_highest_bidder_company = '';

			if ($third_highest_bidder) {        
				$obj_user = get_userdata($third_highest_bidder);
				if ($obj_user) {
					$third_bidder_name = $obj_user->display_name;
					$third_bidder_email = $obj_user->user_email;
					$third_highest_bidder_company = $obj_user->billing_company;
				}
			}

			if (1 == $fail_reason) {                    
				$row['expiry_reason'] = __('No Bid', 'woo_ua');
					
			} elseif (2 == $fail_reason) {                  
				$row['expiry_reason'] = __('Reserve Not Met', 'woo_ua');

			} elseif (3 == $reason_closed) {
				$row['expiry_reason'] = __('Sold', 'woo_ua');   

				if ( $order_id ) {
					$row['expiry_reason'] .= ', ' . __('Order ID:', 'woo_ua') . ' ' . $order_id;
				}
			} else {

				$row['expiry_reason'] .= __('Won', 'woo_ua') . ' - ';
				$row['expiry_reason'] .= __('Highest bidder was', 'woo_ua') . ' ';
				$row['expiry_reason'] .= $bidder_name;          

				if ( $order_id ) {                      
					$row['expiry_reason'] .= ', ' . __('Order ID:', 'woo_ua') . ' ' . $order_id;
				}
			}

			$first_name = '';
			$last_name = '';
			$billing_address_1 = '';
			$billing_address_2 = '';
			$billing_postcode = '';
			$billing_city = '';
			$billing_country = '';
			$billing_company = '';                  
			$phone = '';

			if ($current_bidder > 0) {
				$current_bidder_data = get_userdata($current_bidder);
				if (is_object($current_bidder_data)) {

					/* $bidder_name = $current_bidder_data->display_name;
					$bidder_email = $current_bidder_data->user_email; */

					$first_name = $current_bidder_data->billing_first_name;
					$last_name = $current_bidder_data->billing_last_name;
					$billing_address_1 = $current_bidder_data->billing_address_1;
					$billing_address_2 = $current_bidder_data->billing_address_2;
					$billing_postcode = $current_bidder_data->billing_postcode;
					$billing_city = $current_bidder_data->billing_city;
					$billing_country = $current_bidder_data->billing_country;
					$billing_company = $current_bidder_data->billing_company;
					$phone = $current_bidder_data->billing_phone;
				}
			}


			// Restore original Post Data
			wp_reset_postdata();

				$display_row = array();


			$display_row[] = $auction_name;

			if ('yes' != $disable_pro_category) {               
				$display_row[] = $category;
			}


			/* Add dynamic data */
			if ('yes' == $enable_product_attr) {

				if (is_array($final_attribute_names) && count($final_attribute_names) > 0) {   


					$product_id = $auction_ID;         

					foreach ($final_attribute_names as $attr_name) {
						
					    $taxonomy_id = wc_attribute_taxonomy_id_by_name($attr_name);
					    if ($taxonomy_id) {		

					    	/* taxonomy based attributes */
							$terms = wc_get_product_terms($product_id, $attr_name, 
								array( 'fields' => 'names' ));
							$display_row[] = !empty($terms) ? implode(', ', $terms) : '';

						} else {

							/* custom attributes */
							$product = wc_get_product($product_id);

							$attributes = $product->get_attributes();											
							if (isset($attributes[$attr_name])) {
								$single_attribute = $attributes[$attr_name];
								$attr_options = $single_attribute->get_options();
								$display_row[] = !empty($attr_options) ? implode(', ', $attr_options) : '';

							} else {
								$display_row[] = ""; /* don't remove it */
							}

						}  /* end of else */        

					} /* end of foreach */

				} /* end of if */

			} /* end of if */  



			if ('yes' != $disable_hbid) {
				$display_row[] = $highest_bid_value;
			}
			if ('yes' != $disable_hbidder) {
				$display_row[] = $bidder_name;
			}
			if ('yes' != $disable_hbidder_company) {
				$display_row[] = $billing_company;
			}
			if ('yes' != $disable_first_name) {
				$display_row[] = $first_name;
			}
			if ('yes' != $disable_last_name) {
				$display_row[] = $last_name;
			}   
			if ('yes' != $disable_address1) {
				$display_row[] = $billing_address_1;
			}
			if ('yes' != $disable_address2) {
				$display_row[] = $billing_address_2;
			}
			if ('yes' != $disable_postal_code) {
				$display_row[] = $billing_postcode;
			}
			if ('yes' != $disable_city) {
				$display_row[] = $billing_city;
			}
			if ('yes' != $disable_country) {
				$display_row[] = $billing_country;
			}
			if ('yes' != $disable_hbidder_mail) {           
				$display_row[] = $bidder_email;
			}
			if ('yes' != $disable_phone) {
				$display_row[] = $phone;
			}
			if ('yes' != $disable_sec_hbid) {
				$display_row[] = $second_highest_bid_value;
			}
			if ('yes' != $disable_sec_hbidder) {
				$display_row[] = $second_bidder_name;
			}
			if ('yes' != $disable_sec_hbidder_company) {
				$display_row[] = $second_highest_bidder_company;
			}
			if ('yes' != $disable_sec_hbidder_mail) {               
				$display_row[] = $second_bidder_email;
			}
			if ('yes' != $disable_third_hbid) {
				$display_row[] = $third_highest_bid_value;
			}
			if ('yes' != $disable_third_hbidder) {              
				$display_row[] = $third_bidder_name;
			}
			if ('yes' != $disable_third_hbidder_company) {
				$display_row[] = $third_highest_bidder_company;
			}
			if ('yes' != $disable_third_hbidder_mail) {
				$display_row[] = $third_bidder_email;
			}
			if ('yes' != $disable_enddate) {
				$display_row[] = $ending_date;
			}
			if ('yes' != $disable_expiry_reason) {
				$display_row[] = $row['expiry_reason'];
			}

			
			$data_rows[] = $display_row;

		} /* end of while */

	} else {            
		echo esc_html__('No Posts Found', 'woo_ua');
	}
		
	require_once UW_AUCTION_PRO_DIR . '/library/dompdf/autoload.inc.php' ;
 
	// Instantiate and use the dompdf class
	$dompdf = new Dompdf();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );

	$p_uwa_data =  '<style> 
				        table {
				            width: 100%;
				            font-size: 5px;
				            border-collapse: collapse; 
				            font-family: arial;
				        }
				        th, td {
				            border: 1px solid #000;
				            padding: 1em;
				            text-align: left;
				        }
				        th {
				            background-color: #f2f2f2;
				        }
				        h1, h3 {
				        	text-align: center;
				        }
				        h1 {
				        	text-transform: uppercase;
				        }
				    </style>';
						 

	$p_uwa_data .= '<h1> ' . $sitename . '</h1>
					<h3> ' . __('List of All Expired Auctions', 'woo_ua') . '</h3>';

	$p_uwa_data .= "<table class='uwa_tbl_csv'><thead><tr>";

	for ($i = 0; $i < count($header_row); $i++) {
		$p_uwa_data .= '<th>' . $header_row[$i] . '</th>';
	}

	$p_uwa_data .= '</tr></thead>';

	if (count($data_rows) > 0) {
		$p_uwa_data .= '<tbody>';

		foreach ($data_rows as $auction_row) {
			$p_uwa_data .= '<tr>';

			foreach ($auction_row as $key => $value) {
				$p_uwa_data .= '<td>' . $value . '</td>';
			}           
			$p_uwa_data .= '</tr>';
		}

		$p_uwa_data .= '</tbody>';
	}

	$p_uwa_data .= '</table>';

	$dompdf->loadHtml($p_uwa_data);

	// (Optional) Setup the paper size and orientation 
	$dompdf->setPaper('A4', 'landscape'); 
	 
	// Render the HTML as PDF 
	$dompdf->render(); 
	 
	// Output the generated PDF to Browser 
	//$dompdf->stream();
	$dompdf->stream($filename, array( 'Attachment' => 1 ));
	
	ob_end_flush();
	die();
}
