<?php

/**
 * My auctions tab list
 * 
 * @package Ultimate WooCommerce Auction PRO 
 * @since 1.0  
 *
 */

if (!defined('ABSPATH')) {
	exit;
}

	global $wpdb, $woocommerce;

	$user_id  = get_current_user_id();  
if (isset( $_GET[ 'bid_status' ] )) {
	$active_tab = sanitize_text_field($_GET[ 'bid_status' ]);

} elseif ( isset( $_GET[ 'display' ] )) {
	$active_tab = sanitize_text_field($_GET[ 'display' ]);

} else {
	$active_tab = 'active';
} 
	
	$my_auction_page_url = wc_get_endpoint_url('uwa-auctions');
	$active_bid_url = esc_attr(add_query_arg(array( 'bid_status' =>'active' ), $my_auction_page_url ));
	$active_won_url = esc_attr(add_query_arg(array( 'bid_status' =>'won' ), $my_auction_page_url ));
	$active_lost_url = esc_attr(add_query_arg(array( 'bid_status' =>'lost' ), $my_auction_page_url ));
	$active_watchlist_url = esc_attr(add_query_arg(array( 'display' =>'watchlist' ), $my_auction_page_url ));
	$active_export_expired_url = esc_attr(add_query_arg(array( 'display' =>'export_expired' ), $my_auction_page_url ));
	$active_export_slient_live_url = esc_attr(add_query_arg(array( 'display' =>'import-bids-silent-auction' ), $my_auction_page_url ));

	$uwa_change_bid_url = esc_attr(add_query_arg(array( 'display' =>'change_bid' ), 
		$my_auction_page_url ));
	

	/* get auctions */

	$uwa_all_auctions = array();
	$sort_by_title = get_option('uwa_bidsactive_sort_pro_title_asc', 'no');

	if ('yes' != $sort_by_title) {

		$my_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT auction_id, MAX(bid) as max_userbid FROM ' . 
			$wpdb->prefix . 'woo_ua_auction_log WHERE userid = %d GROUP by auction_id ORDER by date DESC', 
			$user_id));
	} else {

		$my_auctions = $wpdb->get_results( $wpdb->prepare(
			'SELECT lg.auction_id, MAX(lg.bid) as max_userbid, wposts.post_title FROM ' .
			$wpdb->prefix . 'woo_ua_auction_log lg JOIN ' .
			$wpdb->prefix . "posts wposts 
			ON lg.auction_id = wposts.ID 
			WHERE lg.userid = %d 
			AND wposts.post_type = 'product' 
			GROUP BY lg.auction_id 
			ORDER BY wposts.post_title ASC", 
			$user_id
		));
	}

	if (count($my_auctions ) > 0) {

		foreach ($my_auctions as $my_auction) {
			   
		   $product_id =  $my_auction->auction_id;
				
			if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 
				'get_current_language')) {
				
				$product_id = icl_object_id($my_auction->auction_id , 'product', false, 
					$sitepress->get_current_language());
			}
			   
			$product = wc_get_product( $product_id );

			if (is_object($product)) {          

				if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {

					$uwa_all_auctions[] = $product_id;

				}  /* end of if method exists  */
			}
				
		} /* end of foreach */ 
	}

	
	?>

	<ul class="uwa-user-bid-counts subsubsub">
		
		<li class="<?php echo 'active' == $active_tab ? 'active' : ''; ?>">
			
			<a href="<?php echo esc_url($active_bid_url); ?>"> 
					<?php echo esc_attr__( 'Bids Active', 'woo_ua' ); ?></a> (<?php echo esc_html(uwa_front_user_bids_count($user_id, 'active')); ?>) |
		</li>
		<?php

			/* display link if slient bid edit delete settings enabled */		

			$is_enable_slientbid_edit = get_option('uwa_bidsactive_enable_slientbid_edit', 'no');	
			$is_enable_slientbid_delete = get_option('uwa_bidsactive_enable_slientbid_delete', 'no');

			
			if ('yes' == $is_enable_slientbid_edit || 'yes' == $is_enable_slientbid_delete) {
				?>

				<li class="<?php echo 'change_bid' == $active_tab ? 'active' : ''; ?>"> 
				   <a href="<?php echo esc_url($uwa_change_bid_url); ?>">
					<?php echo esc_attr__( 'Change Active Bids', 'woo_ua' ); ?></a> |
				</li>

				<?php
			}

			$is_enable_slient_live_auctions_export = get_option('uwa_slient_live_auctions_export_user', 'no');
			if ('yes' == $is_enable_slient_live_auctions_export) {
				?>
				<li class="<?php echo 'import-bids-silent-auction' == $active_tab ? 'active' : ''; ?>"> 
				   <a href="<?php echo esc_url($active_export_slient_live_url); ?>">
					<?php echo esc_attr__( 'Import your bids in silent auction', 'woo_ua' ); ?></a> |	
				</li>
				<?php
			}

		?>

		<li class="<?php echo 'won' == $active_tab ? 'active' : ''; ?>">
			<a href="<?php echo esc_url($active_won_url); ?>"> 
				<?php echo esc_attr__( 'Bids Won', 'woo_ua' ); ?></a> (<?php echo esc_html(uwa_front_user_bids_count($user_id, 'won')); ?>) |
		</li>
		<li class="<?php echo 'lost' == $active_tab ? 'active' : ''; ?>">
		   <a href="<?php echo esc_url($active_lost_url); ?>"> 
				<?php echo esc_attr__( 'Bids Lost', 'woo_ua' ); ?></a> (<?php echo esc_html(uwa_front_user_bids_count($user_id, 'lost')); ?>) |
		</li>


		<?php

		/* display link if both watchlist settings enabled */

		$live_auctions_watchlist = get_option('woo_ua_auctions_watchlists', 'yes');
		$future_auctions_watchlist = get_option('woo_ua_auctions_watchlists_future', 'no');
		if ('yes' == $live_auctions_watchlist || 'yes' == $future_auctions_watchlist) { ?>

			<li class="<?php echo 'watchlist' == $active_tab ? 'active' : ''; ?>">
		   		<a href="<?php echo esc_url($active_watchlist_url); ?>"> 
					<?php echo esc_attr__( 'Watchlist', 'woo_ua' ); ?></a> 
					(<?php echo esc_html(uwa_front_user_watchlist_count($user_id)); ?>) |
			</li>
			<?php

		}	

		/* display link if expied auctions settings enabled */

		$is_enable_expired_auctions_download = get_option('uwa_expired_auctions_download_user', 'no');
		if ('yes' == $is_enable_expired_auctions_download) {
			
			?>
			<li class="<?php echo 'export_expired' == $active_tab ? 'active' : ''; ?>"> 
			   <a href="<?php echo esc_url($active_export_expired_url); ?>">
				<?php echo esc_attr__( 'Export Expired Auctions', 'woo_ua' ); ?></a> 
			</li> 
			<?php
			
		}

		/* <li class="<?php echo $active_tab == 'settings' ? 'active' : '';?>">
		   <a href="<?php echo $my_account_page_url?>uwa-auctions?display=settings"> 
			 <?php echo __( 'Settings', 'woo_ua' ); ?></a> 
		</li> */
		?>
	</ul>
		
<?php 
	
if ('active' == $active_tab) {
	$bid_status = 'active';

	?>
			<div style="float:right; margin-right:10px;">
				<div class="ex-csv-btn">
					<form id="uwa_frm_file_format" class="uwa_cat" method="post">

						<?php
						$enable_category_list = get_option('uwa_bidsactive_enable_category_listing', 'no');
						if ('yes' == $enable_category_list) {


							/* -------- Get Product Categories From Active Auctions ---------- */

							$uwa_categories_ids = array();

							if (is_array($uwa_all_auctions) && count($uwa_all_auctions) > 0) { 

								foreach ($uwa_all_auctions as $auction_ID) {

									$product = wc_get_product( $auction_ID ); 
									if ('active' == $bid_status && $product->get_uwa_auction_expired() == false) { 

										$categories = get_the_terms( $auction_ID, 'product_cat' );
										if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
											foreach ( $categories as $category ) {
												$uwa_categories_ids[] = $category->term_id;                                    
											}                                           
										}

									} /* end of if */

								} /* end of foreach */
										
								$final_categories_ids = array_values(array_unique($uwa_categories_ids));
								$total_categories = count($final_categories_ids);

								if ($total_categories > 0) { 

									foreach ($final_categories_ids as $term_id) {
										$obj_term = get_term($term_id, 'product_cat');
										$term_name = $obj_term->name;

										/* product categories array */
										$uwa_categories[$term_id] = $term_name;
									}                                       

									/* sorts the array values in ascending order and preserve keys */
									/* asort($uwa_categories); */

									/* sorts the array values in ascending order but not case sensitive & preserve keys */
									uasort($uwa_categories, 'strnatcasecmp');

									if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] > 0) {
										$selected_id = sanitize_text_field($_REQUEST['category_list']);
									}
										
									?>
										<div class="cat-ad-list">
											<select name="category_list" id="category_list" onchange="updateExportLink()">
												<option value="all">
													<?php echo esc_attr__( 'All Categories', 'woo_ua' ); ?>					
												</option>

											<?php foreach ($uwa_categories as $term_id => $term_name) { ?>
													<?php if ($selected_id == $term_id) { ?>
														<option value="<?php echo esc_attr($term_id); ?>" selected>
															<?php echo esc_html($term_name); ?>
														</option>
													<?php } else { ?>
														<option value="<?php echo esc_attr($term_id); ?>">
															<?php echo esc_html($term_name); ?>
														</option>	
													<?php } ?>

												<?php } ?>
											</select>

											<input class="uwa-highlight-btn highlight-btn-disabled" 
												style="border: 1px solid #2271b1;" type="submit" 
												value="<?php echo esc_attr__( 'Submit', 'woo_ua' ); ?>" />
										</div>
										<?php	
								}

							} /* end of if - is array */                                    

						} /* end of if category */
						?>

						<div class="exp-list">
							<label for="file_format">
								<?php echo esc_attr__( 'Choose file format:', 'woo_ua' ); ?>
							</label>

							<select style="margin-right:5px;" name="file_format" id="file_format" onchange="updateExportLink()">
								<option value="csv" selected>CSV</option>
								<option value="xls">XLSX</option>
								<option value="pdf">PDF</option>
							</select>

							<a id="exportLink" style="border: 1px solid #2271b1;" href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>uwa-auctions/?bid_status=active&action=uwa_user_active_download_csv&category=all" class="uwa-highlight-btn highlight-btn-disabled">
							<?php esc_html_e('Export Active Auctions', 'woo_ua'); ?></a>
						</div>
					</form>
					<script>
						jQuery("document").ready(function($){

							/* default values on load */
							if(document.getElementById('category_list')){
								selectedCategory = document.getElementById('category_list').value;
							} else {
								selectedCategory = "all";
							}							

							var selectedFormat = document.getElementById('file_format').value;
							var exportLink = document.getElementById('exportLink');
							var baseUrl = exportLink.getAttribute('href').split('&action=')[0];

							// Update the action based on selected format
							if (selectedFormat === 'csv') {
								exportLink.href = baseUrl + '&action=uwa_user_active_download_csv&category=' + selectedCategory;
							} else if (selectedFormat === 'xls') {
								exportLink.href = baseUrl + '&action=uwa_user_active_download_xls&category=' + selectedCategory;
							} else if (selectedFormat === 'pdf') {
								exportLink.href = baseUrl + '&action=uwa_user_active_download_pdf&category=' + selectedCategory;
							}
						});
						
						function updateExportLink() {

							if(document.getElementById('category_list')){
								selectedCategory = document.getElementById('category_list').value;
							} else {
								selectedCategory = "all";
							}							

							var selectedFormat = document.getElementById('file_format').value;
							var exportLink = document.getElementById('exportLink');
							var baseUrl = exportLink.getAttribute('href').split('&action=')[0];

							// Update the action based on selected format
							if (selectedFormat === 'csv') {
								exportLink.href = baseUrl + '&action=uwa_user_active_download_csv&category=' + selectedCategory;
							} else if (selectedFormat === 'xls') {
								exportLink.href = baseUrl + '&action=uwa_user_active_download_xls&category=' + selectedCategory;
							} else if (selectedFormat === 'pdf') {
								exportLink.href = baseUrl + '&action=uwa_user_active_download_pdf&category=' + selectedCategory;
							}					        
						}
					</script>					
				</div>
			</div>

		<?php
		
		/* echo uwa_front_user_bid_list($user_id, "active"); */
		echo uwa_front_user_bids_active_list($user_id, 'active');
}
if ('won' == $active_tab) {
	$bid_status = 'won';

	?>
			<div style="float:right; margin-right:10px;">
				<div class="ex-csv-btn">
					<form id="uwa_frm_file_format" class="uwa_cat" method="post">

						<?php
						$enable_category_list = get_option('uwa_bidswon_enable_category_listing', 'no');
						if ('yes' == $enable_category_list) {


							/* -------- Get Product Categories From Won Auctions ---------- */

							$uwa_categories_ids = array();

							if (is_array($uwa_all_auctions) && count($uwa_all_auctions) > 0) { 

								foreach ($uwa_all_auctions as $auction_ID) {

									$product = wc_get_product( $auction_ID ); 
									if ($user_id == $product->get_uwa_auction_current_bider() && 
										$product->get_uwa_auction_expired() == '2') { 

										$categories = get_the_terms( $auction_ID, 'product_cat' );
										if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
											foreach ( $categories as $category ) {
												$uwa_categories_ids[] = $category->term_id;                                    
											}                                           
										}

									} /* end of if */

								} /* end of foreach */
										
								$final_categories_ids = array_values(array_unique($uwa_categories_ids));
								$total_categories = count($final_categories_ids);

								if ($total_categories > 0) { 

									foreach ($final_categories_ids as $term_id) {
										$obj_term = get_term($term_id, 'product_cat');
										$term_name = $obj_term->name;

										/* product categories array */
										$uwa_categories[$term_id] = $term_name;
									}                                       

									/* sorts the array values in ascending order and preserve keys */
									/* asort($uwa_categories); */

									/* sorts the array values in ascending order but not case sensitive & preserve keys */
									uasort($uwa_categories, 'strnatcasecmp');

									if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] > 0) {
										$selected_id = sanitize_text_field($_REQUEST['category_list']);
									}
										
									?>
										<div class="cat-ad-list">
											<select name="category_list" id="category_list" onchange="updateExportLink()">
												<option value="all">
													<?php echo esc_attr__( 'All Categories', 'woo_ua' ); ?>					
												</option>

												<?php foreach ($uwa_categories as $term_id => $term_name) { ?>
													<?php if ($selected_id == $term_id) { ?>
														<option value="<?php echo esc_attr($term_id); ?>" selected>
															<?php echo esc_html($term_name); ?>
														</option>
													<?php } else { ?>
														<option value="<?php echo esc_attr($term_id); ?>">
															<?php echo esc_html($term_name); ?>
														</option>	
													<?php } ?>

												<?php } ?>
											</select>

											<input class="uwa-highlight-btn highlight-btn-disabled" 
												style="border: 1px solid #2271b1;" type="submit" 
												value="<?php echo esc_attr__( 'Submit', 'woo_ua' ); ?>" />
										</div>
										<?php	
								}

							} /* end of if - is array */                                    

						} /* end of if category */
						?>
						<div class="exp-list">
							<label for="file_format">
								<?php echo esc_attr__( 'Choose file format:', 'woo_ua' ); ?>
							</label>

							<select style="margin-right:5px;" name="file_format" id="file_format" onchange="updateExportLink()">
								<option value="csv" selected>CSV</option>
								<option value="xls">XLSX</option>
								<option value="pdf">PDF</option>
							</select>

							<a id="exportLink" style="border: 1px solid #2271b1;" href="<?php echo esc_url(get_permalink( get_option('woocommerce_myaccount_page_id'))); ?>uwa-auctions/?bid_status=won&action=uwa_user_won_download_csv&category=all" class="uwa-highlight-btn highlight-btn-disabled">
								<?php esc_html_e('Export Won Auctions', 'woo_ua'); ?></a>
						</div>
					</form>
					<script>
						jQuery("document").ready(function($) {

							if (document.getElementById('category_list')) {
								selectedCategory = document.getElementById('category_list').value;
							} else {
								selectedCategory = "all";
							}

							var selectedFormat = document.getElementById('file_format').value;
							var exportLink = document.getElementById('exportLink');
							var baseUrl = exportLink.getAttribute('href').split('&action=')[0];

							// Update the action based on selected format
							if (selectedFormat === 'csv') {
								exportLink.href = baseUrl + '&action=uwa_user_won_download_csv&category=' + selectedCategory;
							} else if (selectedFormat === 'xls') {
								exportLink.href = baseUrl + '&action=uwa_user_won_download_xls&category=' + selectedCategory;
							} else if (selectedFormat === 'pdf') {
								exportLink.href = baseUrl + '&action=uwa_user_won_download_pdf&category=' + selectedCategory;
							}

						});	

						function updateExportLink() {

							if (document.getElementById('category_list')) {
								selectedCategory = document.getElementById('category_list').value;
							} else {
								selectedCategory = "all";
							}							

							var selectedFormat = document.getElementById('file_format').value;
							var exportLink = document.getElementById('exportLink');
							var baseUrl = exportLink.getAttribute('href').split('&action=')[0]; 

							// Update the action based on selected format
							if (selectedFormat === 'csv') {
								exportLink.href = baseUrl + '&action=uwa_user_won_download_csv&category=' + selectedCategory;
							} else if (selectedFormat === 'xls') {
								exportLink.href = baseUrl + '&action=uwa_user_won_download_xls&category=' + selectedCategory;
							} else if (selectedFormat === 'pdf') {
								exportLink.href = baseUrl + '&action=uwa_user_won_download_pdf&category=' + selectedCategory;
							}					
						}
					</script>
				</div>
			</div>

		<?php

		/*echo uwa_front_user_bid_list($user_id, "won");*/
		echo uwa_front_user_bids_won_list($user_id, 'won');

}   
if ('lost' == $active_tab) {
	$bid_status = 'lost';

	?>
			<div style="float:right; margin-right:10px;">
				<div class="ex-csv-btn">
					<form id="uwa_frm_file_format" class="uwa_cat" method="post">

						<?php
						$enable_category_list = get_option('uwa_bidslost_enable_category_listing', 'no');
						if ('yes' == $enable_category_list) {


							/* -------- Get Product Categories From Won Auctions ---------- */

							$uwa_categories_ids = array();

							if (is_array($uwa_all_auctions) && count($uwa_all_auctions) > 0) { 

								foreach ($uwa_all_auctions as $auction_ID) {

									$product = wc_get_product( $auction_ID ); 
									if ($user_id != $product->get_uwa_auction_current_bider() && 
										$product->get_uwa_auction_expired() == '2') { 

										$categories = get_the_terms( $auction_ID, 'product_cat' );
										if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
											foreach ( $categories as $category ) {
												$uwa_categories_ids[] = $category->term_id;                                    
											}                                           
										}

									} /* end of if */

								} /* end of foreach */
										
								$final_categories_ids = array_values(array_unique($uwa_categories_ids));
								$total_categories = count($final_categories_ids);

								if ($total_categories > 0) { 

									foreach ($final_categories_ids as $term_id) {
										$obj_term = get_term($term_id, 'product_cat');
										$term_name = $obj_term->name;

										/* product categories array */
										$uwa_categories[$term_id] = $term_name;
									}                                       

									/* sorts the array values in ascending order and preserve keys */
									/* asort($uwa_categories); */

									/* sorts the array values in ascending order but not case sensitive & preserve keys */
									uasort($uwa_categories, 'strnatcasecmp');

									if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] > 0) {
										$selected_id = sanitize_text_field($_REQUEST['category_list']);
									}
										
									?>
										<div class="cat-ad-list">
											<select name="category_list" id="category_list" onchange="updateExportLink()">
												<option value="all">
													<?php echo esc_attr__( 'All Categories', 'woo_ua' ); ?>
												</option>

												<?php foreach ($uwa_categories as $term_id => $term_name) { ?>
													<?php if ($selected_id == $term_id) { ?>
														<option value="<?php echo esc_attr($term_id); ?>" selected>
															<?php echo esc_html($term_name); ?>
														</option>
													<?php } else { ?>
														<option value="<?php echo esc_attr($term_id); ?>">
															<?php echo esc_html($term_name); ?>
														</option>	
													<?php } ?>

												<?php } ?>
											</select>

											<input class="uwa-highlight-btn highlight-btn-disabled" 
												style="border: 1px solid #2271b1;" type="submit" 
												value="<?php echo esc_attr__( 'Submit', 'woo_ua' ); ?>" />
										</div>
										<?php	
								}

							} /* end of if - is array */                                    

						} /* end of if category */
						?>

						<div class="exp-list">
							<label for="file_format">
							<?php echo esc_attr__( 'Choose file format:', 'woo_ua' ); ?>
							</label>

							<select style="margin-right:5px;" name="file_format" id="file_format" onchange="updateExportLink()">
								<option value="csv" selected>CSV</option>
								<option value="xls">XLSX</option>
								<option value="pdf">PDF</option>
							</select>

							<a id="exportLink" style="border:1px solid #2271b1;" href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>uwa-auctions/?bid_status=lost&action=uwa_user_lost_download_csv&category=all" 
								class="uwa-highlight-btn highlight-btn-disabled">
							<?php esc_html_e('Export Lost Auctions', 'woo_ua'); ?></a>
						</div>
					</form>
					<script>
						jQuery("document").ready(function($) {

							if (document.getElementById('category_list')) {
								selectedCategory = document.getElementById('category_list').value;
							} else {
								selectedCategory = "all";
							}							

							var selectedFormat = document.getElementById('file_format').value;
							var exportLink = document.getElementById('exportLink');
							var baseUrl = exportLink.getAttribute('href').split('&action=')[0];

							// Update the action based on selected format
							if (selectedFormat === 'csv') {
								exportLink.href = baseUrl + '&action=uwa_user_lost_download_csv&category=' + selectedCategory;           
							} else if (selectedFormat === 'xls') {
								exportLink.href = baseUrl + '&action=uwa_user_lost_download_xls&category=' + selectedCategory;            
							} else if (selectedFormat === 'pdf') {
								exportLink.href = baseUrl + '&action=uwa_user_lost_download_pdf&category=' + selectedCategory;	            
							}							


						});
						function updateExportLink() {
							if (document.getElementById('category_list')) {
								selectedCategory = document.getElementById('category_list').value;
							} else {
								selectedCategory = "all";
							}							

							var selectedFormat = document.getElementById('file_format').value;
							var exportLink = document.getElementById('exportLink');
							var baseUrl = exportLink.getAttribute('href').split('&action=')[0]; 

							// Update the action based on selected format
							if (selectedFormat === 'csv') {
								exportLink.href = baseUrl + '&action=uwa_user_lost_download_csv&category=' + selectedCategory;           
							} else if (selectedFormat === 'xls') {
								exportLink.href = baseUrl + '&action=uwa_user_lost_download_xls&category=' + selectedCategory;            
							} else if (selectedFormat === 'pdf') {
								exportLink.href = baseUrl + '&action=uwa_user_lost_download_pdf&category=' + selectedCategory;           
							}
						}
					</script>
				</div>
			</div>

		<?php

		/* echo uwa_front_user_bid_list($user_id, "lost"); */
		echo uwa_front_user_bids_lost_list($user_id, 'lost');
}   

if ('watchlist' == $active_tab) {
	echo uwa_front_user_watchlist($user_id);
}

if ('yes' == $is_enable_expired_auctions_download) {
	if ('export_expired' == $active_tab) {
		$bid_status = 'export_expired';
		echo uwa_front_user_export_all_expired_auctions($user_id, $bid_status);
	}
}

if ('yes' == $is_enable_slient_live_auctions_export) {
	if ('import-bids-silent-auction' == $active_tab) {
		$bid_status = 'import-bids-silent-auction';
		echo uwa_front_user_export_all_silent_auctions_bid_offline($user_id, $bid_status);
	}
}

if ('yes' == $is_enable_slientbid_edit || 'yes' == $is_enable_slientbid_delete) {
	if ('change_bid' == $active_tab) {
		$bid_status = 'change_bid';

		?>

			<div style="float:right; margin-right:10px;">
				<div class="ex-csv-btn">
					<form id="uwa_frm_file_format" class="uwa_cat" method="post">

						<?php
						$enable_category_list = get_option('uwa_bidsactive_enable_category_listing', 'no');
						if ('yes' == $enable_category_list) {


							/* -------- Get Product Categories From Active Slient Auctions ---------- */

							$uwa_categories_ids = array();

							if (is_array($uwa_all_auctions) && count($uwa_all_auctions) > 0) {

								foreach ($uwa_all_auctions as $auction_ID) {

									$product = wc_get_product( $auction_ID );									

									if ('change_bid' == $bid_status && $product->get_uwa_auction_expired() == false 
										&& 'yes' == $product->get_uwa_auction_silent()) {

										$categories = get_the_terms( $auction_ID, 'product_cat' );
										if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
											foreach ( $categories as $category ) {
												$uwa_categories_ids[] = $category->term_id;                                    
											}                                           
										}

									} /* end of if */

								} /* end of foreach */
										
								$final_categories_ids = array_values(array_unique($uwa_categories_ids));
								$total_categories = count($final_categories_ids);

								if ($total_categories > 0) { 

									foreach ($final_categories_ids as $term_id) {
										$obj_term = get_term($term_id, 'product_cat');
										$term_name = $obj_term->name;

										/* product categories array */
										$uwa_categories[$term_id] = $term_name;
									}                                       

									/* sorts the array values in ascending order and preserve keys */
									/* asort($uwa_categories); */

									/* sorts the array values in ascending order but not case sensitive & preserve keys */
									uasort($uwa_categories, 'strnatcasecmp');

									if (isset($_REQUEST['category_list']) && $_REQUEST['category_list'] > 0) {
										$selected_id = sanitize_text_field($_REQUEST['category_list']);
									}

									/* get selected category id when user updates the slient bid */
									if (isset($_POST['selected_category_id']) && $_POST['selected_category_id'] > 0) {
										$selected_id = sanitize_text_field($_POST['selected_category_id']);
									}
										
									?>
									<div class="cat-ad-list">
											<select name="category_list" id="category_list" onchange="updateExportLink()">
												<option value="all">
													<?php echo esc_attr__( 'All Categories', 'woo_ua' ); ?>					
												</option>

												<?php foreach ($uwa_categories as $term_id => $term_name) { ?>
													<?php if ($selected_id == $term_id) { ?>
														<option value="<?php echo esc_attr($term_id); ?>" selected>
															<?php echo esc_html($term_name); ?>
														</option>
													<?php } else { ?>
														<option value="<?php echo esc_attr($term_id); ?>">
															<?php echo esc_html($term_name); ?>
														</option>	
													<?php } ?>

												<?php } ?>
											</select>

											<input class="uwa-highlight-btn highlight-btn-disabled" 
												style="border: 1px solid #2271b1;" type="submit" 
												value="<?php echo esc_attr__( 'Submit', 'woo_ua' ); ?>" />
									</div>
										<?php	
								}

							} /* end of if - is array */                                    

						} /* end of if category */
						?>

						<div class="exp-list">							
						</div>
					</form>
					<script>						
						function updateExportLink() {									        
						}
					</script>					
				</div>
			</div>

		<?php

		echo uwa_front_user_change_bid_list($user_id, $bid_status);
	}
}   

/*if ('settings' == $active_tab) {
	echo uwa_front_user_auction_settings($user_id);
}*/
?>