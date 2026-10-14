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

function uwa_user_active_auctions_download_csv() {

	if (ob_get_length() > 0) {
		ob_clean();
	}
	ob_start();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );

	if ( ! empty( $sitename ) ) {
		$sitename .= '_';
	}

	/* $filename = $sitename . 'active-auctions-' . date( 'Y-m-d-H-i-s' ) . '.csv'; */      

	$midtext = __('active-auctions', 'woo_ua');
	$filename = $sitename . $midtext . '_' . date( 'd.m.Y' ) . '.csv';
	
	global $wpdb, $woocommerce, $product, $sitepress;

	$header_row = array();
	$data_rows = array();
	$curr_user_id = get_current_user_id();
	$table = $wpdb->prefix . 'woo_ua_auction_log';
	$table_posts = $wpdb->prefix . 'posts';

	/* $query = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table WHERE userid = %d GROUP by auction_id ORDER by date DESC", $curr_user_id); */

	/* $query = $wpdb->prepare(
		"SELECT a.auction_id, MAX(a.bid) as max_userbid, p.post_title 
		 FROM $table a
		 JOIN $table_posts p ON a.auction_id = p.ID 
		 WHERE a.userid = %d 
		 AND p.post_type = 'product'
		 GROUP BY a.auction_id 
		 ORDER BY p.post_title ASC", 
		 $curr_user_id
	);

	$my_auctions = $wpdb->get_results( $query ); */

	$my_auctions = $wpdb->get_results( $wpdb->prepare(
		'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
		$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
		$wpdb->prefix . "posts wposts 
		ON lg.auction_id = wposts.ID 
		WHERE lg.userid = %d 
		AND wposts.post_type = 'product' 
		GROUP BY lg.auction_id 
		ORDER BY wposts.post_title ASC", 
		$curr_user_id
	));
	
	if (is_array($my_auctions) && count($my_auctions) > 0) {

		$attribute_names = array();
		$final_active_auctions = array();
		$final_attribute_names = array();
		$enable_product_attr = get_option('uwa_bidsactive_user_csv_enable_pro_attr', 'no'); 

		/* $header_row = array(
				__('Product title', 'woo_ua'),
				__('Product category', 'woo_ua'),
				__('Your bid', 'woo_ua'),
				__('Current bid', 'woo_ua'),
				__('End date', 'woo_ua'),
			); */

		$header_row[] = __('Product title', 'woo_ua');
		$header_row[] = __('Product category', 'woo_ua');
		
		/* count for finalize auctions */
		foreach ($my_auctions as $my_auction) {

			$product_id = $my_auction->auction_id;

			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {

				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}

			$product = wc_get_product($product_id);

			if (is_object($product)) {

				if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {
								
					if ($product->get_uwa_auction_expired() == false) {
						
						if ('yes' == $enable_product_attr) {

							/* get product attributes term names */
							$attributes = $product->get_attributes();
							if (is_array($attributes) && count($attributes) > 0) {
								foreach ($attributes as $attribute_slug => $attribute) {

									$attribute_names[] = $attribute_slug;
									// $attribute_names[] = $attribute->get_id();
									// $attribute_names[] = $attribute->get_name();
								}
							}
						}
						
						if (isset($_REQUEST['category']) && 'all' != $_REQUEST['category']) {
							if ($_REQUEST['category'] > 0) {

								$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

								if (in_array($_REQUEST['category'], $categories_ids)) {
									$final_active_auctions[] = $product_id;
								}
							}
						} else {
							$final_active_auctions[] = $product_id;
						}

					} /* end of if - active auctions */
							
				} /* end of if method exists */

			} /* end of if - is object */
				
		} /* end of foreach */

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
		$header_row[] = __('Your bid', 'woo_ua');
		$header_row[] = __('Current bid', 'woo_ua');
		$header_row[] = __('End date', 'woo_ua');

		/* export only finalize auctions */
		foreach ($my_auctions as $my_auction) {

			$product_id = $my_auction->auction_id;

			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {

				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}

			$product = wc_get_product( $product_id );

			if (is_object($product)) {

				if (method_exists( $product, 'get_type') && $product->get_type() == 'auction') {
								
					if ($product->get_uwa_auction_expired() == false) {

						if (in_array($product_id, $final_active_auctions)) {

							$product_name = get_the_title( $product_id );

							$product_cats = wp_get_post_terms( $product_id, 'product_cat', 
								array( 'fields' => 'names' ) );

							$category = !empty($product_cats) ? implode(', ', $product_cats) : '';


							$maxbid = $my_auction->max_userbid;

							/* $currency = get_option('woocommerce_currency');
							$woocommerce_currency_symbol = get_woocommerce_currency_symbol($currency);
							$woocommerce_currency_symbol_value = html_entity_decode($woocommerce_currency_symbol);*/

							if ($maxbid) {

								$maxbid = wc_price($maxbid);
								$maxbid = strip_tags($maxbid);
								$maxbid_value = html_entity_decode($maxbid);
								$maxbid_value = preg_replace('/[^\d,\.]/', '', $maxbid_value);


							} else {
								$maxbid_value = '';
							}

							$your_bid = $maxbid_value;

							$uwa_current_bid = $product->get_uwa_auction_current_bid();

							if ($uwa_current_bid) {

								$uwa_current_bid = wc_price($uwa_current_bid);
								$uwa_current_bid = strip_tags($uwa_current_bid);
								$uwa_current_bid_value = html_entity_decode($uwa_current_bid);
								$uwa_current_bid_value = preg_replace('/[^\d,\.]/', '', $uwa_current_bid_value);
									
							} else {
								$uwa_current_bid_value = '';
							}

							$current_bid = $uwa_current_bid_value;

							$uwa_slient = $product->get_uwa_auction_silent();

							if ('yes' == $uwa_slient) {
								$current_bid = __('This auction is silent bid', 'woo_ua');
							}

							$end_date = $product->get_uwa_auction_end_dates();
							$uwa_strtotime = strtotime($end_date);
							$end_date = date('d-m-Y H:i:s', $uwa_strtotime);
						
							// Restore original Post Data
							wp_reset_postdata();

							/* $row = array(
								$product_name,
								$category,
								$your_bid,
								$current_bid,
								$end_date,
							);  */

							$row = array();
							$row[] = $product_name;
							$row[] = $category;
							
							/* Add dynamic data */
							if ('yes' == $enable_product_attr) {

								if (is_array($final_attribute_names) && count($final_attribute_names) > 0) {            

									foreach ($final_attribute_names as $attr_name) {
										
									    $taxonomy_id = wc_attribute_taxonomy_id_by_name($attr_name);
									    if ($taxonomy_id) {		

									    	/* taxonomy based attributes */
											$terms = wc_get_product_terms($product_id, $attr_name, 
												array( 'fields' => 'names' ));
											$row[] = !empty($terms) ? implode(', ', $terms) : '';

										} else {

											/* custom attributes */
											$attributes = $product->get_attributes();											
											if (isset($attributes[$attr_name])) {
												$single_attribute = $attributes[$attr_name];
												$attr_options = $single_attribute->get_options();
												$row[] = !empty($attr_options) ? implode(', ', $attr_options) : '';

											} else {
												$row[] = ""; /* don't remove it */
											}
											
										}  /* end of else */        

									} /* end of foreach */

								} /* end of if */

							} /* end of if */  

							$currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
							$row[] = $currency_symbol;
							$row[] = $your_bid;
							$row[] = $current_bid;
							$row[] = $end_date;
												
							$data_rows[] = $row;    

						} /* end of if - inarray */
					}                           
				} /* end of if method exists  */
			} /* end of if is object */
				
		} /* end of foreach */

	} /* end of if - myauctions */
		
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


function uwa_user_active_auctions_download_xls() {

	if (ob_get_length() > 0) {
		ob_clean();
	}
	ob_start();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );

	if ( ! empty( $sitename ) ) {
		$sitename .= '_';
	}

	/* $filename = $sitename . 'active-auctions-' . date( 'Y-m-d-H-i-s' ) . '.xls'; */

	$midtext = __('active-auctions', 'woo_ua');
	$filename = $sitename . $midtext . '_' . date( 'd.m.Y' ) . '.xlsx';

	global $wpdb, $woocommerce, $product, $sitepress;

	$header_row = array();
	$data_rows = array();
	$curr_user_id = get_current_user_id();
	$table = $wpdb->prefix . 'woo_ua_auction_log';
	$table_posts = $wpdb->prefix . 'posts';

	/* $query = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table WHERE userid = %d GROUP by auction_id ORDER by date DESC", $curr_user_id); */

	/* $query = $wpdb->prepare(
		"SELECT a.auction_id, MAX(a.bid) as max_userbid, p.post_title 
		 FROM $table a
		 JOIN $table_posts p ON a.auction_id = p.ID 
		 WHERE a.userid = %d 
		 AND p.post_type = 'product'
		 GROUP BY a.auction_id 
		 ORDER BY p.post_title ASC", 
		 $curr_user_id
	);

	$my_auctions = $wpdb->get_results( $query ); */

	$my_auctions = $wpdb->get_results( $wpdb->prepare(
		'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
		$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
		$wpdb->prefix . "posts wposts 
		ON lg.auction_id = wposts.ID 
		WHERE lg.userid = %d 
		AND wposts.post_type = 'product' 
		GROUP BY lg.auction_id 
		ORDER BY wposts.post_title ASC", 
		$curr_user_id
	)); 
	
	if (is_array($my_auctions) && count($my_auctions) > 0) {

		$attribute_names = array();
		$final_active_auctions = array();
		$final_attribute_names = array();
		$enable_product_attr = get_option('uwa_bidsactive_user_csv_enable_pro_attr', 'no');

		/* $header_row = array(
				__('Product title', 'woo_ua'),
				__('Product category', 'woo_ua'),
				__('Your bid', 'woo_ua'),
				__('Current bid', 'woo_ua'),
				__('End date', 'woo_ua'),
			); */

		$header_row[] = __('Product title', 'woo_ua');
		$header_row[] = __('Product category', 'woo_ua');
		
		
		/* count for finalize auctions */
		foreach ($my_auctions as $my_auction) {

			$product_id = $my_auction->auction_id;

			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {

				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}

			$product = wc_get_product($product_id);

			if (is_object($product)) {

				if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {
								
					if ($product->get_uwa_auction_expired() == false) {
						
						if ('yes' == $enable_product_attr) {

							/* get product attributes term names */
							$attributes = $product->get_attributes();
							if (is_array($attributes) && count($attributes) > 0) {
								foreach ($attributes as $attribute_slug => $attribute) {

									$attribute_names[] = $attribute_slug;
									// $attribute_names[] = $attribute->get_id();
									// $attribute_names[] = $attribute->get_name();
								}
							}
						}
						
						if (isset($_REQUEST['category']) && 'all' != $_REQUEST['category']) {
							if ($_REQUEST['category'] > 0) {

								$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

								if (in_array($_REQUEST['category'], $categories_ids)) {
									$final_active_auctions[] = $product_id;
								}
							}
						} else {
							$final_active_auctions[] = $product_id;
						}

					} /* end of if - active auctions */
							
				} /* end of if method exists */

			} /* end of if - is object */
				
		} /* end of foreach */


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
		$header_row[] = __('Your bid', 'woo_ua');
		$header_row[] = __('Current bid', 'woo_ua');
		$header_row[] = __('End date', 'woo_ua');

		/* export only finalize auctions */
		foreach ($my_auctions as $my_auction) {

			$product_id = $my_auction->auction_id;

			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {

				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}

			$product = wc_get_product( $product_id );

			if (is_object($product)) {

				if (method_exists( $product, 'get_type') && $product->get_type() == 'auction') {
								
					if ($product->get_uwa_auction_expired() == false) {

						if (in_array($product_id, $final_active_auctions)) {

							$product_name = get_the_title( $product_id );

							$product_cats = wp_get_post_terms( $product_id, 'product_cat', 
								array( 'fields' => 'names' ) );

							$category = !empty($product_cats) ? implode(', ', $product_cats) : '';

							$decimals = wc_get_price_decimals();

							$maxbid = $my_auction->max_userbid;

							/* $currency = get_option('woocommerce_currency');
							$woocommerce_currency_symbol = get_woocommerce_currency_symbol($currency);
							$woocommerce_currency_symbol_value = html_entity_decode($woocommerce_currency_symbol);*/

							if ($maxbid) {

								/* $maxbid = wc_price($maxbid);
								$maxbid = strip_tags($maxbid);
								$maxbid_value = html_entity_decode($maxbid);
								$maxbid_value = preg_replace('/[^\d,\.]/', '', $maxbid_value); */

								$maxbid = number_format($maxbid, 2, '.', ''); 
								$maxbid_value = $maxbid;

							} else {
								$maxbid_value = '';
							}

							$your_bid = $maxbid_value;

							$uwa_current_bid = $product->get_uwa_auction_current_bid();

							if ($uwa_current_bid) {

								/* $uwa_current_bid = wc_price($uwa_current_bid);
								$uwa_current_bid = strip_tags($uwa_current_bid);
								$uwa_current_bid_value = html_entity_decode($uwa_current_bid);
								$uwa_current_bid_value = preg_replace('/[^\d,\.]/', '', $uwa_current_bid_value); */

								$uwa_current_bid = number_format($uwa_current_bid, 2, '.', ''); 
								$uwa_current_bid_value = $uwa_current_bid;
									
							} else {
								$uwa_current_bid_value = '';
							}

							$current_bid = $uwa_current_bid_value;

							$uwa_slient = $product->get_uwa_auction_silent();

							if ('yes' == $uwa_slient) {
								$current_bid = __('This auction is silent bid', 'woo_ua');
							}

							$end_date = $product->get_uwa_auction_end_dates();
							$uwa_strtotime = strtotime($end_date);
							$end_date = date('d-m-Y H:i:s', $uwa_strtotime);
						
							// Restore original Post Data
							wp_reset_postdata();

							/* $row = array(
								$product_name,
								$category,
								$your_bid,
								$current_bid,
								$end_date,
							);  */

							$row = array();
							$row[] = $product_name;
							$row[] = $category;
							
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
													$row[] = implode(', ', $terms);
												}											

												if (count($terms) > 1) {
													$row[] = '"' . implode('", "', $terms) . '"';
												} 

											} else {

												$row[] = "";
											}

											/*$row[] = !empty($terms) ? implode(', ', $terms) : '';*/


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
														$row[] = implode(', ', $attr_options);
													}

													if (count($attr_options) > 1) {
														$row[] = '"' . implode('", "', $attr_options) . '"';
													} 


												} else {

													$row[] = "";
												}


												/* $row[] = !empty($attr_options) ? implode(' & ', $attr_options) : ''; */

											} else {
												$row[] = ""; /* don't remove it */
											}
											
										}  /* end of else */        

									} /* end of foreach */

								} /* end of if */

							} /* end of if */  

							$currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');

							$row[] = $currency_symbol;
							$row[] = $your_bid;
							$row[] = $current_bid;
							$row[] = $end_date;
												
							$data_rows[] = $row;    

						} /* end of if - inarray */
					}                           
				} /* end of if method exists  */
			} /* end of if is object */
				
		} /* end of foreach */

	} /* end of if - myauctions */

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

function uwa_user_active_auctions_download_pdf() {  

	if (ob_get_length() > 0) {
		ob_clean();
	}
	ob_start();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );

	if ( ! empty( $sitename ) ) {
		$sitename .= '_';
	}       

	/* $filename = $sitename . 'active-auctions-' . date( 'Y-m-d-H-i-s' ) . '.pdf'; */

	$midtext = __('active-auctions', 'woo_ua');
	$filename = $sitename . $midtext . '_' . date( 'd.m.Y' ) . '.pdf';  

	global $wpdb, $woocommerce, $product, $sitepress;

	$header_row = array();
	$data_rows = array();
	$curr_user_id = get_current_user_id();
	$table = $wpdb->prefix . 'woo_ua_auction_log';
	$table_posts = $wpdb->prefix . 'posts';

	/* $query = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table WHERE userid = %d GROUP by auction_id ORDER by date DESC", $curr_user_id); */

	/* $query = $wpdb->prepare(
		"SELECT a.auction_id, MAX(a.bid) as max_userbid, p.post_title 
		 FROM $table a
		 JOIN $table_posts p ON a.auction_id = p.ID 
		 WHERE a.userid = %d 
		 AND p.post_type = 'product'
		 GROUP BY a.auction_id 
		 ORDER BY p.post_title ASC", 
		 $curr_user_id
	);

	$my_auctions = $wpdb->get_results( $query ); */

	$my_auctions = $wpdb->get_results( $wpdb->prepare(
		'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
		$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
		$wpdb->prefix . "posts wposts 
		ON lg.auction_id = wposts.ID 
		WHERE lg.userid = %d 
		AND wposts.post_type = 'product' 
		GROUP BY lg.auction_id 
		ORDER BY wposts.post_title ASC", 
		$curr_user_id
	)); 
	
	if (is_array($my_auctions) && count($my_auctions) > 0) {

		$attribute_names = array();
		$final_active_auctions = array();
		$final_attribute_names = array();
		$enable_product_attr = get_option('uwa_bidsactive_user_csv_enable_pro_attr', 'no'); 

		/* $header_row = array(
				__('Product title', 'woo_ua'),
				__('Product category', 'woo_ua'),
				__('Your bid', 'woo_ua'),
				__('Current bid', 'woo_ua'),
				__('End date', 'woo_ua'),
			); */

		$header_row[] = __('Product title', 'woo_ua');
		$header_row[] = __('Product category', 'woo_ua');
		
		
		/* count for finalize auctions */
		foreach ($my_auctions as $my_auction) {

			$product_id = $my_auction->auction_id;

			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {

				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}

			$product = wc_get_product($product_id);

			if (is_object($product)) {

				if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {
								
					if ($product->get_uwa_auction_expired() == false) {
						
						if ('yes' == $enable_product_attr) {

							/* get product attributes term names */
							$attributes = $product->get_attributes();
							if (is_array($attributes) && count($attributes) > 0) {
								foreach ($attributes as $attribute_slug => $attribute) {

									$attribute_names[] = $attribute_slug;
									// $attribute_names[] = $attribute->get_id();
									// $attribute_names[] = $attribute->get_name();
								}
							}
						}
						
						if (isset($_REQUEST['category']) && 'all' != $_REQUEST['category']) {
							if ($_REQUEST['category'] > 0) {

								$categories_ids = wp_get_post_terms($product_id, 'product_cat', array( 'fields' => 'ids' ));

								if (in_array($_REQUEST['category'], $categories_ids)) {
									$final_active_auctions[] = $product_id;
								}
							}
						} else {
							$final_active_auctions[] = $product_id;
						}

					} /* end of if - active auctions */
							
				} /* end of if method exists */

			} /* end of if - is object */
				
		} /* end of foreach */


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

		$header_row[] = __('Your bid', 'woo_ua');
		$header_row[] = __('Current bid', 'woo_ua');
		$header_row[] = __('End date', 'woo_ua');

		/* export only finalize auctions */
		foreach ($my_auctions as $my_auction) {

			$product_id = $my_auction->auction_id;

			if (function_exists('icl_object_id') && is_object($sitepress) && 
					method_exists($sitepress, 'get_current_language')) {

				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}

			$product = wc_get_product( $product_id );

			if (is_object($product)) {

				if (method_exists( $product, 'get_type') && $product->get_type() == 'auction') {
								
					if ($product->get_uwa_auction_expired() == false) {

						if (in_array($product_id, $final_active_auctions)) {

							$product_name = get_the_title( $product_id );

							$product_cats = wp_get_post_terms( $product_id, 'product_cat', 
								array( 'fields' => 'names' ) );

							$category = !empty($product_cats) ? implode(', ', $product_cats) : '';

							$maxbid = $my_auction->max_userbid;

							/* $currency = get_option('woocommerce_currency');
							$woocommerce_currency_symbol = get_woocommerce_currency_symbol($currency);
							$woocommerce_currency_symbol_value = html_entity_decode($woocommerce_currency_symbol);*/

							if ($maxbid) {

								$maxbid = wc_price($maxbid);
								$maxbid = strip_tags($maxbid);
								$maxbid_value = html_entity_decode($maxbid);

							} else {
								$maxbid_value = '';
							}

							$your_bid = $maxbid_value;

							$uwa_current_bid = $product->get_uwa_auction_current_bid();

							if ($uwa_current_bid) {

								$uwa_current_bid = wc_price($uwa_current_bid);
								$uwa_current_bid = strip_tags($uwa_current_bid);
								$uwa_current_bid_value = html_entity_decode($uwa_current_bid);
									
							} else {
								$uwa_current_bid_value = '';
							}

							$current_bid = $uwa_current_bid_value;

							$uwa_slient = $product->get_uwa_auction_silent();

							if ('yes' == $uwa_slient) {
								$current_bid = __('This auction is silent bid', 'woo_ua');
							}

							$end_date = $product->get_uwa_auction_end_dates();
							$uwa_strtotime = strtotime($end_date);
							$end_date = date('d-m-Y H:i:s', $uwa_strtotime);
						
							// Restore original Post Data
							wp_reset_postdata();

							/* $row = array(
								$product_name,
								$category,
								$your_bid,
								$current_bid,
								$end_date,
							);  */

							$row = array();
							$row[] = $product_name;
							$row[] = $category;
							
							/* Add dynamic data */
							if ('yes' == $enable_product_attr) {

								if (is_array($final_attribute_names) && count($final_attribute_names) > 0) {            

									foreach ($final_attribute_names as $attr_name) {
										
									    $taxonomy_id = wc_attribute_taxonomy_id_by_name($attr_name);
									    if ($taxonomy_id) {		

									    	/* taxonomy based attributes */
											$terms = wc_get_product_terms($product_id, $attr_name, 
												array( 'fields' => 'names' ));
											$row[] = !empty($terms) ? implode(', ', $terms) : '';

										} else {

											/* custom attributes */
											$attributes = $product->get_attributes();											
											if (isset($attributes[$attr_name])) {
												$single_attribute = $attributes[$attr_name];
												$attr_options = $single_attribute->get_options();
												$row[] = !empty($attr_options) ? implode(', ', $attr_options) : '';

											} else {
												$row[] = ""; /* don't remove it */
											}
											
										}  /* end of else */        

									} /* end of foreach */

								} /* end of if */

							} /* end of if */  

							$row[] = $your_bid;
							$row[] = $current_bid;
							$row[] = $end_date;
												
							$data_rows[] = $row;    

						} /* end of if - inarray */
					}                           
				} /* end of if method exists  */
			} /* end of if is object */
				
		} /* end of foreach */

	} /* end of if - myauctions */
	
	require_once UW_AUCTION_PRO_DIR . '/library/dompdf/autoload.inc.php';
 
	// Instantiate and use the dompdf class 
	$dompdf = new Dompdf();

	$sitename = sanitize_key( get_bloginfo( 'name' ) );

	$p_uwa_data = '<style>
					.uwa_tbl_csv {
					    width: 100%;
					    border-collapse: collapse;
					    font-size: 10px;		
					    font-family: arial;		            
					}
					.uwa_tbl_csv th, .uwa_tbl_csv td {
					    border: 1px solid #bababa;
					    padding: 7px;
					    text-align: left;
					}
					.uwa_tbl_csv th {
					    background-color: #f2f2f2;
					    font-size: 14px;
					}
					h1, h3 {
						text-align: center;
					}
					h1 {
						text-transform: uppercase;
					}				        
				</style>';

  
	$p_uwa_data .= '<h1> ' . $sitename . '</h1>
					<h3> ' . __('List of Your Active Auctions', 'woo_ua') . '</h3>';                    

	$p_uwa_data .= "<table class='uwa_tbl_csv'><thead><tr>";

	for ($i=0; $i<count($header_row); $i++) {
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
