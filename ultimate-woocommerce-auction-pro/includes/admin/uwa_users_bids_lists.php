<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin, 
 * rename it, and work inside the copy. If you modify this plugin directly and 
 * an update is released, your changes will be lost!
 * ========================================================================== */


/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */

class Uwa_Users_Bids_List_Table extends WP_List_Table {
	
    var $allData;
    var $uwa_bid_type; 

    public function uwa_auction_get_data($per_page, $page_number){ 

    	global $wpdb, $woocommerce;		
		
		$uwa_bid_type = isset( $_GET["uwa_bid_type"] ) ? sanitize_text_field( wp_unslash( $_GET["uwa_bid_type"] ) ) : "active";

		$auct_users = get_option('uwa_users_bids_list');
		$curr_user_id = get_current_user_id();		
		$table = $wpdb->prefix."woo_ua_auction_log";
		$table2 = $wpdb->prefix."posts";
		$table_users = $wpdb->prefix."users";
		
        if(!empty($auct_users)){
        	/* display userwise data */
        	/* get auctions for particular user only */
		$query = $wpdb->prepare("SELECT DISTINCT(auction_id) FROM {$table} as alg JOIN {$table2} as wposts ON wposts.ID = alg.auction_id 
				WHERE alg.userid = %d 				
				AND wposts.post_type = 'product'  
				AND wposts.post_status = 'publish'  
				ORDER by alg.auction_id DESC", $auct_users);				
			$selected = "one_user"; 
		}
		else{
			/* display all data */
			/* get auctions except current logged in user only */
			$query = $wpdb->prepare("SELECT DISTINCT(auction_id) FROM {$table} as alg JOIN {$table2} as wposts ON wposts.ID = alg.auction_id 
				WHERE alg.userid != %d 				
				AND wposts.post_type = 'product' 
				AND wposts.post_status = 'publish'  
				GROUP by alg.auction_id ORDER by alg.auction_id DESC", $curr_user_id);
			$selected = "all";
		}

		$bids_data = $wpdb->get_results( $query );
		
        $all_auctions = array();
        $won_bids_array = array();
        $not_won_bids_array = array();
        $active_bids_array = array();

		$data_array = array();

		if(count($bids_data) > 0){
			foreach($bids_data as $bdata){
				global $product;	
				$product_data = wc_get_product( $bdata->auction_id );
				$auc_id = $bdata->auction_id;
				if( $product_data->get_type() == 'auction' ) {					
					
					if ($uwa_bid_type == "won" && 
						$product_data->get_uwa_auction_expired() == '2') {

						/* get only one last win bid per auction */
						/* auction user must be present in wp_users table */

					   /* $subquery = "SELECT MAX(bid) FROM $table WHERE 
							auction_id = $auc_id";
						$query_maxbid = "SELECT * FROM $table 
							WHERE auction_id = $auc_id 	
							AND userid IN (SELECT ID FROM $table_users) 			
							AND bid = ($subquery)"; */

						$query_maxbid = $wpdb->prepare(
							"SELECT * FROM {$table} WHERE auction_id = %d AND userid IN (SELECT ID FROM {$table_users}) AND bid = (SELECT MAX(bid) FROM {$table} WHERE auction_id = %d)",
							$auc_id,
							$auc_id
						);

						
						$newdata = $wpdb->get_results( $query_maxbid );

						if(count($newdata) > 0){

								// check userid is not login user
								if($curr_user_id != $newdata[0]->userid){
									if($selected == "one_user"){
										if($auct_users == $newdata[0]->userid){
											$won_bids_array[] = $newdata[0];
										}
									}
									else{
										$won_bids_array[] = $newdata[0];
									}							
								}
						} /* end of if count */

					}
					elseif ($uwa_bid_type == "not-won" && 
						$product_data->get_uwa_auction_expired() == '2' ){

						/* get multiple bids per auction */
						/* auction user must be present in wp_users table */
					
						$query_otherbids = $wpdb->prepare(
							"SELECT * FROM {$table}
							WHERE auction_id = %d
							AND userid IN (SELECT ID FROM {$table_users})
							AND bid != (SELECT MAX(bid) FROM {$table} WHERE auction_id = %d)
							ORDER BY id DESC",
							$auc_id,
							$auc_id
						);

						$newdata = $wpdb->get_results( $query_otherbids );

						if(count($newdata) > 0){

								foreach($newdata as $single_auction){
										
									// check userid is not login user
									if($curr_user_id != $single_auction->userid){
										if($selected == "one_user"){
											if($auct_users == $single_auction->userid){
												$not_won_bids_array[] = $single_auction;
											}
										}
										else{
											$not_won_bids_array[] = $single_auction;
										}
									}

								} /* end of foreach */
						} /* end of if count */

					}
					elseif ($uwa_bid_type == "active" && 
							$product_data->get_uwa_auction_expired() == false ){

						/* get only one last bid per auction */
						/* auction user must be present in wp_users table */

						$query_maxbid = $wpdb->prepare(
							"SELECT * FROM {$table}
							WHERE auction_id = %d
							AND userid IN (SELECT ID FROM {$table_users})
							AND bid = (SELECT MAX(bid) FROM {$table} WHERE auction_id = %d)",
							$auc_id,
							$auc_id
						);

						$newdata = $wpdb->get_results( $query_maxbid );

						if(count($newdata) > 0){

								// check userid is not login user
								if($curr_user_id != $newdata[0]->userid){							
									if($selected == "one_user"){
										if($auct_users == $newdata[0]->userid){
											$active_bids_array[] = $newdata[0];
										}
									}
									else{
										$active_bids_array[] = $newdata[0];
									}
								}
						} /* end of if count */

					} /* end of elseif */
				

				} /* end of if - get type */

			} /* end of foreach */

		} /* end of if - count */

		
			if($uwa_bid_type=="won"){
				$all_auctions = $won_bids_array;
			}			
			elseif($uwa_bid_type=="not-won"){
				$all_auctions = $not_won_bids_array;
			}
			else{
				$all_auctions = $active_bids_array;
			}	


		if(count($all_auctions) > 0){
			foreach ( $all_auctions as $my_auction ) {
				
		    	$row = array();
				$product = wc_get_product( $my_auction->auction_id );
				$auc_id = $my_auction->auction_id;			

				$auction_proxy = get_post_meta($auc_id, 'uwa_auction_proxy', true);
				$auction_silent = get_post_meta($auc_id, 'uwa_auction_silent', true);   					
					if($auction_proxy == "yes"){
						$auction_type = __('Proxy', 'woo_ua');
					}elseif($auction_silent == "yes"){
						$auction_type = __('Silent', 'woo_ua');
					}else{
						$auction_type = __('Simple', 'woo_ua');
					}

				/* Auction Type column */
				$row['auction_type'] = $auction_type; 

				/* Product Title column */				
				$row['title'] = '<a href="'.get_permalink( $auc_id ).'">'.get_the_title(  
					$auc_id ).'</a>';
				
				/* Bid column */					
				$row['bid_value'] = wc_price($my_auction->bid);

				/* MaxBid column */
				if($auction_proxy == "yes"){
					$maxbid_metakey = "woo_ua_auction_user_max_bid_".$auc_id;
		        	$max_bid =  get_user_meta($my_auction->userid, $maxbid_metakey, true);	
					$row['max_bid_value'] = wc_price($max_bid);
				}
				else {
					$row['max_bid_value'] = wc_price(0);	
				}

				/* Winning Bid & User column */
				// old.. $bidder_name = uwa_user_display_name($my_auction->userid);
				$userobj = get_userdata( $my_auction->userid );
				
				$bidder_name = $userobj->display_name;
				if (empty($bidder_name)) {
					$bidder_name = $userobj->user_login;
				}				

				$row['winning_bid_bidders'] = wc_price($my_auction->bid)." & ".esc_html( $bidder_name );
				// OR .. $row['winning_bid_bidders'] = $row['bid_value']." & ".esc_html( $bidder_name );

				/* Lost bid Username column */
				$row['lostusername'] = esc_html( $bidder_name );

					/* enddate */
				$row['enddate'] = '';
				$row['enddate'] = $product->get_uwa_auction_end_dates();	
				

				/* Action column */
				$row_action = "";
				if ($uwa_bid_type == "active"){						
					$row_action = "<a href='#' data-id='" . esc_attr( $my_auction->id ) . "' data-postid='" . esc_attr( $auc_id ) . "' class='uwa_force_delete_bid button'>" . esc_html__( 'Delete', 'woo_ua' ) . "</a>";
				}

				if ($uwa_bid_type == 'won') {	
                    $product->get_uwa_auction_current_bider();				
					if (($my_auction->userid == $product->get_uwa_auction_current_bider() && 
						$product->get_uwa_auction_expired() == '2' && 
						!$product->get_uwa_auction_payed() ) ) {
						$row_action = "<a href='#' data-postid='" . esc_attr( $my_auction->auction_id ) . "' class='uwa_force_remind_to_pay button'>" . esc_html__( 'Remind to Pay', 'woo_ua' ) . "</a>";
					}								
				}

				if ($uwa_bid_type == 'not-won') {					
					$row_action = "";
				}

				$row['uwa_action'] = $row_action;
				
			   	$data_array[] = $row;

		    } /* end of foreach */

		} /* end of if - count -  all auctions */

		$this->allData = $data_array;
		return $data_array;

	} /* end of fuction - uwa_auction_get_data */


	function get_columns() {
		
		$uwa_bid_type = isset( $_GET["uwa_bid_type"] ) ? sanitize_text_field( wp_unslash( $_GET["uwa_bid_type"] ) ) : "active";

        $columns = array(           
            'auction_type' => __('Auction Type', 'woo_ua'),
            'title' => __('Product Title', 'woo_ua'),
            'bid_value' => __('Bid', 'woo_ua'),           
            'max_bid_value' => __('Max Bid', 'woo_ua'),          
            'winning_bid_bidders' => __('Winning Bid & User','woo_ua'),                    
			'uwa_action' => __('Actions', 'woo_ua'),			
        );
		
		if ($uwa_bid_type == 'won') {
			$columns = array(
				'auction_type' => __('Auction Type', 'woo_ua'),
				'title' => __('Product Title', 'woo_ua'),
				'bid_value' => __('Bid', 'woo_ua'),				
				'max_bid_value' => __('Max Bid', 'woo_ua'),				
				'winning_bid_bidders' => __('Won Bid & User','woo_ua'),	
				'enddate' => __('End date', 'woo_ua'),
				'uwa_action' => __('Actions', 'woo_ua'),                    			   
			);
		}
		
		if ($uwa_bid_type == 'not-won') {
			 $columns = array(       
				'auction_type' => __('Auction Type', 'woo_ua'),
				'title' => __('Product Title', 'woo_ua'),				
				'bid_value' => __('Bid', 'woo_ua'),								
				'max_bid_value' => __('Max Bid', 'woo_ua'),
				'lostusername' => __('UserName','woo_ua'),
								       
			);
		}		
        return $columns;

    } /* end of fuction - get_columns */


    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     *
     */
    function get_sortable_columns(){
        $sortable_columns = array(			
            'auction_type' => array('auction_type', true),
            'title' => array('title', true),
            'bid_value' => array('bid_value', true),
            'max_bid_value' => array('max_bid_value', true),
            'enddate' => array('enddate', true),
            'winning_bid_bidders' => array('winning_bid_bidders', true),
        );
        return $sortable_columns;
    }


	/**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items() {

    	global $wpdb; 

		$search = isset($_POST['s']) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';

		$uwa_bid_type = isset( $_GET["uwa_bid_type"] ) ? sanitize_text_field( wp_unslash( $_GET["uwa_bid_type"] ) ) : "active";

		$columns = $this->get_columns();
		$hidden = array();		
		$current_page = $this->get_pagenum();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$per_page = 20;

		/*$orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'title';
		if ($orderby === 'title') {
			$this->items = $this->uwa_auction_sort_array($this->uwa_auction_get_data($per_page, 
				$current_page));
		} else {
			$this->items = $this->uwa_auction_get_data($per_page, $current_page);
		}*/

		
		$this->items = $this->uwa_auction_get_data($per_page, $current_page);

		/*$total_items = count($this->items); 

	    if($total_items > 0){
	    	echo "<div><b>Total items: $total_items</b></div>";
	    }*/
    	
	} /* end of fuction - prepare_items */


	public function get_result_e(){
    	return $this->allData;
	}

	public function uwa_auction_sort_array($args){

    	if (!empty($args)) {
        	$orderby = ! empty( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'title';
			$order_raw = ! empty( $_GET['order'] ) ? strtolower( sanitize_key( $_GET['order'] ) ) : 'asc';
			$order_raw = in_array( $order_raw, array( 'asc', 'desc' ), true ) ? $order_raw : 'asc';

			if($orderby === 'auction_type') {
	            $order = $order_raw;
	        }
			else if($orderby === 'bid_value') {
	            $order = $order_raw;
	        }
			else if($orderby === 'max_bid_value') {
	            $order = $order_raw;
	        }
			else if($orderby === 'winning_bid_bidders') {
	            $order = $order_raw;
	        }
			else {
	            $order = 'desc';
	        }		        
		
	        foreach ($args as $array) {
	            $sort_key[] = $array[$orderby];
	        }

	        if ($order == 'asc') {
	            array_multisort($sort_key, SORT_ASC, $args);
	        } else {
	            array_multisort($sort_key, SORT_DESC, $args);
	        }
    	}
    	return $args;

	} /* end of fuction - uwa_auction_sort_array */

	public function column_default($item, $column_name){
	    switch ($column_name) {
	        case 'auction_type':
	        case 'title':
	        case 'bid_value':
	        case 'max_bid_value':
	        case 'winning_bid_bidders':
    	    case 'enddate':
	        case 'uwa_action':
	        case 'lostusername':	        	  
	        	return $item[ $column_name ];
	        default:
	            /* return print_r($item, true); //Show the whole array for troubleshooting purposes */
	            return '';
	    }

	} /* end of fuction - column_default */


} /* end of class */

function uwa_users__bids_list_page_handler_display() {
	global $wpdb; 		
	
	$uwa_bid_type = isset( $_GET["uwa_bid_type"] ) ? sanitize_text_field( wp_unslash( $_GET["uwa_bid_type"] ) ) : "active";

		?>
		<div class="uwa_main_setting wrap">
	 		<br class="clear">	

			<?php
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

			if (!empty($auction_ids)) {
				foreach ($auction_ids as $auc_id) {
					$product_data = wc_get_product($auc_id);

					if ($product_data && $product_data->get_type() == 'auction') {
						$all_auctions[] = $auc_id;
					}
				}
			}

			$active_categories = array(); // Store unique category IDs & names
			if (!empty($all_auctions)) {
				foreach ($all_auctions as $bid) {
					$auc_id = $bid;

					// Get category IDs for the auction product
					$terms = wp_get_post_terms($auc_id, 'product_cat');

					if (!empty($terms) && !is_wp_error($terms)) {
						foreach ($terms as $term) {
							$active_categories[$term->term_id] = $term->name;
						}
					}
				}
			}
			?>

			<form id="uwa_frm_bid_user_export" class="uwa_bid_user_export" method="post">
				<div class="exp-list">
					<div class="cat-filter">
						<label for="category_filter"><?php esc_html_e('Choose Categories: ', 'woo_ua'); ?></label>
						<select name="category" id="category_filter" onchange="updateExportLink()">
							<option value="all" selected><?php esc_html_e('All Categories', 'woo_ua'); ?></option>
							<?php foreach ($active_categories as $cat_id => $cat_name) : ?>
								<option value="<?php echo esc_attr($cat_id); ?>"><?php echo esc_html($cat_name); ?></option>
							<?php endforeach; ?>
						</select>
						<?php
							$uwa_bid_type = isset($_GET['uwa_bid_type']) ? sanitize_text_field($_GET['uwa_bid_type']) : 'active';
							$base_url = admin_url('admin.php?page=uwa_auctions_bids_list&users_bids=true');
							$export_url = $base_url . "&uwa_bid_type=" . $uwa_bid_type . "&user=" . $uwa_bid_type . "bids&action=uwa_active_bid_user_download_xls&category=all";
							$data_baseurl = $base_url . "&uwa_bid_type=" . $uwa_bid_type . "&user=" . $uwa_bid_type . "bids&action=uwa_active_bid_user_download_xls&category=";

							$Wlexport_url = $base_url . "&uwa_bid_type=won-lost&user=" . $uwa_bid_type . "bids&action=uwa_active_bid_user_download_xls&export=both&category=all";
							$Wldata_baseurl = $base_url . "&uwa_bid_type=won-lost&user=" . $uwa_bid_type . "bids&action=uwa_active_bid_user_download_xls&export=both&category=";
						?>
					</div>
					<div class="export-btns">
						<a id="exportLink"
							style="border: 1px solid #2271b1;" 
							href="<?php echo esc_url($export_url); ?>"
							data-baseurl="<?php echo esc_url($data_baseurl); ?>"
							class="uwa-highlight-btn highlight-btn-disabled">
							<?php esc_html_e('Export Bid User List', 'woo_ua'); ?>
						</a>
						<a id="exportWonLostLink"
							style="border: 1px solid #2271b1;" 
							href="<?php echo esc_url($Wlexport_url); ?>"
							data-baseurl="<?php echo esc_url($Wldata_baseurl); ?>"
							class="uwa-highlight-btn highlight-btn-disabled">
							<?php esc_html_e('Export Won and Lost Bid User List', 'woo_ua'); ?>
						</a>
					</div>
				</div>
			</form>

			<?php
			 
			if (isset($uwa_bid_type)) {
				$manage_bid_tab = $uwa_bid_type;
			} else {
				$manage_bid_tab = 'active';
			}		

			/*$user_args = array(
				'blog_id'      => $GLOBALS['blog_id'],
				'orderby'      => 'login',
				'order'        => 'ASC'
			);

			$allUsers = get_users( $user_args );*/

			$table_auc_log = $wpdb->prefix."woo_ua_auction_log";
			$table_users = $wpdb->prefix."users";			

			/* get only one column */
			/*$users_id_list = $wpdb->get_col( "SELECT DISTINCT(userid) FROM 
				$table_auc_log");*/

			$users_id_list = $wpdb->get_col( $wpdb->prepare(
				"SELECT DISTINCT(userid) FROM {$table_auc_log} WHERE userid IN (SELECT ID FROM {$table_users})"
			) );
			
			if ( isset( $_POST['select-auction-users'] ) && isset( $_POST['uwa_users_bids_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uwa_users_bids_nonce_field'] ) ), 'uwa_users_bids_nonce' ) ) {
				update_option( 'uwa_users_bids_list', absint( $_POST['select-auction-users'] ) );
			}
				?>	
				<div class="uwa-action-container" style="float:right;margin-right: 10px;">
					 <form id="users-bids-form" name="users-bids-form" method="post" action="">
						<?php wp_nonce_field( 'uwa_users_bids_nonce', 'uwa_users_bids_nonce_field' ); ?>
	        			<select id="select-auction-users" name="select-auction-users">
	            			<option id='all-au' value=''><?php _e('All', 'woo_ua');?>
	            			</option>
							<?php
							$curr_user_id = get_current_user_id();
							foreach($users_id_list as $userid){

								if($userid != $curr_user_id){
									
									$au = get_userdata($userid);
									
									?>
									<option id='au-<?php echo esc_attr( $au->user_login );?>' value='<?php echo esc_attr( $au->ID );?>' <?php if(isset($_POST['select-auction-users']) && absint( $_POST['select-auction-users'] ) === $au->ID) { echo "selected='selected'"; } elseif(get_option('uwa_users_bids_list') == $au->ID) { echo "selected='selected'"; }?>><?php echo esc_html( $au->user_login );?></option>
									<?php 

								} /* end of if */
							} ?>
        				</select>
						<input type="submit" value="<?php _e('Show Bids', 'woo_ua');?>" class="button-secondary" />
					</form>
        	</div>		

	    	<ul class="subsubsub">
					<li><a href="?page=uwa_auctions_bids_list&users_bids=true&uwa_bid_type=active" class="<?php echo $manage_bid_tab == 'active' ? 'current' : ''; ?>"><?php _e('Active Bids', 'woo_ua');?></a>|</li>
				<li><a href="?page=uwa_auctions_bids_list&users_bids=true&uwa_bid_type=won" class="<?php echo $manage_bid_tab == 'won' ? 'current' : ''; ?>"><?php _e('Bids Won', 'woo_ua');?></a>|</li>
				<li><a href="?page=uwa_auctions_bids_list&users_bids=true&uwa_bid_type=not-won" class="<?php echo $manage_bid_tab == 'not-won' ? 'current' : ''; ?>"><?php _e('Bids Lost', 'woo_ua');?></a></li>
	    	</ul>
	    	<br class="clear">
		
				<?php 
				global $wpdb;
				$myListTable = new Uwa_Users_Bids_List_Table();
				$myListTable->prepare_items();
				$myListTable->display();?>			
			</div>

		<?php 
}
?>
<script>

function updateExportLink() {
    var category = document.getElementById("category_filter").value; // Correct ID
    var exportLink = document.getElementById("exportLink");
    var baseUrl = exportLink.getAttribute("data-baseurl");
    exportLink.href = baseUrl + category;

	//won-lost
	var exportWonLostLink = document.getElementById("exportWonLostLink");
    var baseUrl = exportWonLostLink.getAttribute("data-baseurl");
	exportWonLostLink.href = baseUrl + category;
}


</script>