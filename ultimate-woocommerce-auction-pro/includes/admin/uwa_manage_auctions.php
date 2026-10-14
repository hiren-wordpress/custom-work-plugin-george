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

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ;
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
 
/* $this->auction_status = (isset($_GET['auction_status']) && !empty($_GET['auction_status'])) ? $_GET['auction_status'] : 'live'; */
 

class Uwa_Manage_Auctions_List_Table extends WP_List_Table {
	
	public $allData;
    public $auction_status;
	
	/**
     * Get auction data
     *
     * @param int $per_page, $page_number
     * @return array
     *
     */
	public function uwa_auction_get_data( $per_page, $page_number ) {  
		global $wpdb;
		global $sitepress;	

		$datetimeformat = get_option('date_format') . ' ' . get_option('time_format');
		$curr_user_id = get_current_user_id();	

		/* $this->auction_status = ( isset($_GET['auction_status']) && !empty($_GET['auction_status']) ) ? 
			sanitize_text_field($_GET['auction_status']) : 'live'; 	*/

		$this->auction_status = ( isset( $_GET['auction_status'] ) && ! empty( $_GET['auction_status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['auction_status'] ) ) : 'live';

		
		$pagination = ( (int)$page_number - 1 ) * (int)$per_page;

		$tax_query = array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' );

		if (isset($_REQUEST['category_list']) && !empty($_REQUEST['category_list'])) {

			if ('all' != $_REQUEST['category_list'] && $_REQUEST['category_list'] > 0) {

				$tax_query = array(
					'relation' => 'AND', // Use 'AND' or 'OR' 
					array(
						'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
						'field'    => 'term_id',        // Use 'slug', 'name', or 'term_id'
						'terms'    => sanitize_text_field($_REQUEST['category_list']), // Replace with your category slug
					),
					array(
						'taxonomy' => 'product_type', // WooCommerce product type taxonomy
						'field'    => 'slug',
						'terms'    => 'auction', // Replace with your product type term
					),
				);
			}
		}
		

		$meta_query = array(
			'relation' => 'AND',
			array(               
				'key'  => 'woo_ua_auction_closed',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'woo_ua_auction_has_started',
				'value' => '1',
			),
		);		
		
	
		if ('expired' == $this->auction_status) {

			$meta_query = array(
				'relation' => 'AND',
				array(               
					'key' => 'woo_ua_auction_closed',
					'value' => array( '1', '2', '3', '4' ),
					'compare' => 'IN',
				),                          
			);
		}
		
		if ('scheduled' == $this->auction_status) {                     
				
			$today = get_uwa_now_date();    
			$meta_query = array(                     
				array(               
					'key'     => 'woo_ua_auction_closed',
					'compare' => 'NOT EXISTS',
				),  
				array(
					'key'     => 'woo_ua_auction_started',
					'value' => '0',
				),   
			);                      
		}
		
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page' => $per_page,
			'offset' => $pagination,
			'author' => $curr_user_id,  
			//'s'=> $search,                    
			'meta_query' => array( $meta_query ),
			'tax_query' => $tax_query,
			'auction_arhive' => true,
		);

		$filter_id = isset( $_REQUEST['uwa_auction_id'] ) ? absint( $_REQUEST['uwa_auction_id'] ) : 0;

		if ( $filter_id > 0 ) {
			$args['p'] = $filter_id;
		}
		
		if (function_exists('icl_object_id') && is_object($sitepress) && 
			method_exists($sitepress, 'get_current_language')) {
		   
			$args['suppress_filters'] = 0;
		}

		/*$search = (isset($_POST['uwa_auction_search'])) ? $_POST['uwa_auction_search'] : '';*/
		if (isset($_POST['uwa_auction_search'])) {			
			$new_search = sanitize_text_field($_POST['uwa_auction_search']);
			$args['s'] = $new_search;
		}
		
		$auction_item_array = get_posts($args);	
		$data_array = array();
		foreach ($auction_item_array as $single_auction) { 
					
			$row = array();
			$auction_ID = $single_auction->ID;			
			$auction_types = get_post_meta($auction_ID, 'woo_ua_auction_type', true);
			$auction_proxy = get_post_meta($auction_ID, 'uwa_auction_proxy', true);
			$auction_silent = get_post_meta($auction_ID, 'uwa_auction_silent', true);

			$row['auction_type'] = '';	
			if ('yes' == $auction_proxy) {
				$auction_type = __('Proxy', 'woo_ua');
			} elseif ('yes' == $auction_silent) {
				$auction_type = __('Silent', 'woo_ua');
			} else {
				$auction_type = __('Simple', 'woo_ua');
			}

			/* Auction Type column */			
			$row['auction_type'] = $auction_type;  
			
			/* Product Title column */
			$row['title'] = '';	
	        $row['title'] = '<a href="' . esc_url( get_permalink( $auction_ID ) ) . '">' . esc_html( get_the_title( $auction_ID ) ) . '</a>';
			
			/* Product Catgory column */
			$category_text = '';
			$categories = get_the_terms( $auction_ID, 'product_cat' );
			if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
				$category_text .= '<ul class="product-categories">';

				// Loop through each category and display its name with a link
				foreach ( $categories as $category ) {
					$category_link = get_term_link( $category->term_id, 'product_cat' );
					$category_text .= '<li><a href="' . esc_url( $category_link ) . '">' . esc_html( $category->name ) . '</a></li>';
				}

				$category_text .= '</ul>';
			}

			$row['pro_category'] = $category_text;


			/* Start Date column */
			$row['create_date'] = '';
			$starting_on_date = get_post_meta($auction_ID, 'woo_ua_auction_start_date', true);
			$uwa_start_date = mysql2date($datetimeformat, $starting_on_date);
			$row['create_date'] = $uwa_start_date;

			/* End Date column */
			$row['end_date'] = '';
			$ending_date = get_post_meta($auction_ID, 'woo_ua_auction_end_date', true);
			$uwa_end_date = mysql2date($datetimeformat, $ending_date);			
			$row['end_date'] = $uwa_end_date;
		    
			/* Opening Price column */
			$row['opening_price'] = '';
			$opening_price = get_post_meta($auction_ID, 'woo_ua_opening_price', true);
			$current_bid_price = get_post_meta($auction_ID, 'woo_ua_auction_current_bid', true);
			$row['opening_price'] = wc_price($opening_price);
			if(!empty($current_bid_price)){
				$row['opening_price'] = wc_price($opening_price) . ' / ' . wc_price($current_bid_price);
			}

			/* Reserve Price column */
			$row['reserve_price'] = wc_price(get_post_meta($auction_ID, 'woo_ua_lowest_price', true)); 

			
			/* Bidder/ Bid / Max Bid / Time column */
			$row['bidders'] = '';
			$results = array();
			$row_bidders = '';

			/* $query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$single_auction->ID.' ORDER BY id DESC LIMIT 2';
            $results = $wpdb->get_results($query_bidders); */

			$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . 
				$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY id DESC LIMIT 2', $single_auction->ID));

			if (!empty($results)) {
               				
                foreach ($results as $result) {
					
					/*$bidder_name = get_userdata($result->userid)->display_name;*/

					$obj_user = get_userdata($result->userid);	
					$bidder_name = '';
					if($obj_user){					
						$bidder_name = $obj_user->display_name;	
					}

	                if ($bidder_name) {

						$bidder_name = "<a href='" . esc_url( get_edit_user_link( $result->userid ) ) . "' target='_blank'>" .
							esc_html( $bidder_name ) . "</a>";

					} else {

						// $bidder_name = 'User id:'.$result->userid;
						$bidder_name = "<a href='" . esc_url( get_edit_user_link( $result->userid ) ) . "' target='_blank'>" .
							esc_html( $obj_user->user_login ) . "</a>";
	                } 


	                $userid = $result->userid;
					$maxbid_metakey = 'woo_ua_auction_user_max_bid_' . $auction_ID;
	                $max_bid =  wc_price(get_user_meta($userid, $maxbid_metakey, true));

					$bid_amt = wc_price($result->bid);
					$bid_time = mysql2date($datetimeformat, $result->date);

					$row_bidders .= "<tr>";			

					$row_bidders .= "<td class='create_name column_create_name'>".$bidder_name." </td>";
					$row_bidders .= "<td class='create_bid_amt column_create_bid_amt'>".$bid_amt."</td>";
					$row_bidders .= "<td class='create_max_bid column_create_max_bid'>".$max_bid."</td>";
					$row_bidders .= "<td class='create_bid_time column_create_bid_time'>".$bid_time."</td>";

					if ('live' == $this->auction_status) {
						$bid_ID = $result->id;
						$bid_user_ID = $result->userid;
						$bid_amount = $result->bid;
						$row_bidders .= "<td class='choose_winner column_choose_winner'><a href='#' class='button uwa_force_choose_winner'
							data-bid_id='" . absint( $bid_ID ) . "'
							data-bid_user_id='" . absint( $bid_user_ID ) . "'
							data-bid_amount='" . esc_attr( $bid_amount ) . "'
							data-auction_id='" . absint( $auction_ID ) . "' >" . esc_html__( 'Choose Winner', 'woo_ua' ) . "</a></td>";
					}

					$row_bidders .= "</tr>";
					
                }

				/*  $row['bidders'] = "<div class='uwa-bidder-list-".$single_auction->ID.">"; */
				$row['bidders'] = "<table class='uwa-bidslist uwa-bidder-list-".$auction_ID."'>";
				$row['bidders'] .= $row_bidders;				
				$row['bidders'] .= "</table>";
				
				/* $query_bidders_count = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$single_auction->ID.' ORDER BY id DESC';
        		$results_count = $wpdb->get_results($query_bidders_count); */

				$results_count = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . 
					$wpdb->prefix . 'woo_ua_auction_log WHERE auction_id = %d ORDER BY id DESC', $single_auction->ID));         		

				if (count($results_count) > 2) {						
                        $row['bidders'] .= "
                            <a href='#' class='uwa-see-more show-all'  rel='".$auction_ID."' >".__('See more', 'woo_ua').'</a>';
                }

			} else {				
				$row['bidders'] = __('No bids placed', 'woo_ua');	
			}

			if ('expired' == $this->auction_status) {
				 
				$row['expiry_reason'] = '';
				$user_name = '';
				$fail_reason = get_post_meta($auction_ID, 'woo_ua_auction_fail_reason', true); 
				$reason_closed = get_post_meta($auction_ID, 'woo_ua_auction_closed', true); 
				$current_bidder = get_post_meta($auction_ID, 'woo_ua_auction_current_bider', true);
				$order_id = get_post_meta($auction_ID, 'woo_ua_order_id', true);	

				if ($current_bidder) {		

					/*$user_name = get_userdata($current_bidder)->display_name;*/

					$obj_user = get_userdata($current_bidder);	
					
					$user_name = '';
					if ($obj_user) {
						$user_name = !empty($obj_user->display_name) ? $obj_user->display_name : $obj_user->user_login;
					}

				}

				if (1 == $fail_reason) { 	
				
					$row['expiry_reason'] ='<span style="color:red;font-size:13px">'.__('No Bid', 'woo_ua').'</span>';
					
				} elseif (2 == $fail_reason) {
					
					$row['expiry_reason'] = '<span style="color:red;font-size:13px">'.__('Reserve Not Met', 'woo_ua').'</span>';

				} elseif (3 == $reason_closed) {

					$row['expiry_reason'] = '<span style="color:#7ad03a;font-size:13px">'.__('Sold', 'woo_ua').'</span>';									
					if ( $order_id ) {
						$row['expiry_reason'] .= '<br>';
						$row['expiry_reason'] .= '<span style="font-size:13px"><a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . absint( $order_id ) ) ) . '">' . esc_html__( 'Order ID: ', 'woo_ua' ) . absint( $order_id ) . '</a></span>';
					}
				} else {

					$row['expiry_reason'] = '<span style="color:#7ad03a;font-size:13px">' . esc_html__( 'Won', 'woo_ua' ) . '</span><br>';
					$row['expiry_reason'] .= '<span style="font-size:13px">' . esc_html__( 'Highest bidder was', 'woo_ua' ) . '</span><br>';
					$row['expiry_reason'] .= '<span style="font-size:13px"><a href="' . esc_url( get_edit_user_link( $current_bidder ) ) . '">' . esc_html( $user_name ) . '</a></span><br>';
					if ( $order_id ) {
						$row['expiry_reason'] .= '<span style="font-size:13px"><a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . absint( $order_id ) ) ) . '">' . esc_html__( 'Order ID: ', 'woo_ua' ) . absint( $order_id ) . '</a></span><br>';
					}
				}
			    
			}

			$row_action = '';
			if ('live' == $this->auction_status) {
				$auction_edit_url = get_edit_post_link( $auction_ID );
				$row_action = '<a href="' . esc_url( $auction_edit_url ) . '" class="button">' . esc_html__( 'Edit', 'woo_ua' ) . '</a> <br /><br />';
				$row_action .= "<a href='#' class='button uwa_force_end_now' data-auction_id='" . absint( $auction_ID ) . "'>" . esc_html__( 'End Now', 'woo_ua' ) . '</a>';
			}

			if ('expired' == $this->auction_status) {

				$woo_ua_auction_payed = get_post_meta($auction_ID, 'woo_ua_auction_payed', true);
				$reason_closed = get_post_meta($auction_ID, 'woo_ua_auction_closed', true);
				if (empty($woo_ua_auction_payed) && '1' != $woo_ua_auction_payed) {

					$auction_edit_url = get_edit_post_link( $auction_ID );
					$auction_relist_url = $auction_edit_url . '&relist=true';
					$row_action = '<a href="' . esc_url( $auction_relist_url ) . '" class="button">' . esc_html__( 'Relist', 'woo_ua' ) . '</a><br />';
					if (2 == $reason_closed) {
						$row_action .= "<a href='#' data-postid='" . absint( $auction_ID ) . "' class='button uwa_force_remind_to_pay button'>" . esc_html__( 'Remind to Pay', 'woo_ua' ) . '</a>';
					}
				}
			}


			if ('scheduled' == $this->auction_status) {

				$row_action = "<a href='#' class='button uwa_force_make_live' data-auction_id='" . absint( $auction_ID ) . "'>" . esc_html__( 'Make It Live', 'woo_ua' ) . "</a>";
			}

			$row['uwa_action'] = $row_action;			
			$data_array[] = $row;
	    } 
	   	   
		$this->allData = $data_array;
		return $data_array;
	}
	
	/**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     *
     */
    function get_columns() {
		

		$this->auction_status = ( isset( $_GET['auction_status'] ) && ! empty( $_GET['auction_status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['auction_status'] ) ) : 'live';


        $columns = array(           
            'auction_type' => __('Type', 'woo_ua'),
            'title' => __('Product Title', 'woo_ua'),
			'pro_category' => __('Product Category', 'woo_ua'),            
            'create_date' => __('Start Date', 'woo_ua'),
            'end_date' => __('End Date', 'woo_ua'),
            'opening_price' => __('Opening / Current Price', 'woo_ua'),
            'reserve_price' => __('Reserve Price', 'woo_ua'),
            'bidders' => __('Bidder &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Max Bid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Time&nbsp;&nbsp;&nbsp;&nbsp;Choose Winner','woo_ua'),                    
			'uwa_action' => __('Actions', 'woo_ua'),
        );
		
		if ('expired' == $this->auction_status) {
			$columns = array(
				'auction_type' => __('Type', 'woo_ua'),
				'title' => __('Product Title', 'woo_ua'),
				'pro_category' => __('Product Category', 'woo_ua'),				
				'create_date' => __('Start Date', 'woo_ua'),
				'end_date' => __('End Date', 'woo_ua'),				
				'opening_price' => __('Opening / Final Price', 'woo_ua'),
				'reserve_price' => __('Reserve Price', 'woo_ua'),
				'bidders' => __('Bidder &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Max Bid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Time','woo_ua'),                   
				'expiry_reason' => __('Expiry Reason', 'woo_ua'),                    
				'uwa_action' => __('Actions', 'woo_ua'),                    			   
			);
		}
		
		if ('scheduled' == $this->auction_status) {
			 $columns = array(       
				'auction_type' => __('Type', 'woo_ua'),
				'title' => __('Product Title', 'woo_ua'),	
				'pro_category' => __('Product Category', 'woo_ua'),
				'create_date' => __('Starting Date', 'woo_ua'),								
				'end_date' => __('End Date', 'woo_ua'),
				'opening_price' => __('Opening / Final Price', 'woo_ua'),
				'reserve_price' => __('Reserve Price', 'woo_ua'),
				'uwa_action' => __('Actions', 'woo_ua'),				       
			);
		}

        //return $columns;
        return apply_filters('uwa_admin_manage_auctions_columns', $columns, $this->auction_status);
    }

	/**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     *
     */
    function get_sortable_columns() {

        $sortable_columns = array(
            'auction_type' => array('auction_type', true),
            'title' => array('title', true),
            'create_date' => array('create_date', true),
            'end_date' => array('end_date', true),
            'opening_price' => array('opening_price', true),
            'reserve_price' => array('reserve_price', true),
            'bidders' => array('bidders', true),
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
		global $sitepress;	
		
		/* $this->auction_status = ( isset($_GET['auction_status']) && !empty($_GET['auction_status']) ) ? 
			sanitize_text_field($_GET['auction_status']) : 'live'; */

		$this->auction_status = ( isset( $_GET['auction_status'] ) && ! empty( $_GET['auction_status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['auction_status'] ) ) : 'live';			

		$columns = $this->get_columns();
		$hidden = array();
		$per_page = '';
		$current_page = '';
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
				
		$orderby = ( !empty( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'title';

		if ('title' === $orderby) {
			$this->items = $this->uwa_auction_sort_array($this->uwa_auction_get_data($per_page, $current_page));
		} else {
			$this->items = $this->uwa_auction_get_data($per_page, $current_page);
		}
		$per_page = 20;
		$current_page = $this->get_pagenum();
		$curr_user_id = get_current_user_id();

		$tax_query = array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' );

		if (isset($_REQUEST['category_list']) && !empty($_REQUEST['category_list'])) {

			if ('all' != $_REQUEST['category_list'] && $_REQUEST['category_list'] > 0) {

				$tax_query = array(
					'relation' => 'AND', // Use 'AND' or 'OR' 
					array(
						'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
						'field'    => 'term_id',        // Use 'slug', 'name', or 'term_id'
						'terms'    => sanitize_text_field($_REQUEST['category_list']), // Replace with your category slug
					),
					array(
						'taxonomy' => 'product_type', // WooCommerce product type taxonomy
						'field'    => 'slug',
						'terms'    => 'auction', // Replace with your product type term
					),
				);
			}
		}

		
		$meta_query = array(
						'relation' => 'AND',
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							),							
						);
		
		if ('expired' == $this->auction_status) { 						
			$meta_query= array(
						'relation' => 'AND',
							array(			     
								'key' => 'woo_ua_auction_closed',
								'value' => array( '1','2','3','4' ),
								'compare' => 'IN',
							),							
						);
		}
		
		if ('scheduled' == $this->auction_status) {
			$meta_query= array(
						'relation' => 'AND',
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							),
							
							array(
							'key'     => 'woo_ua_auction_started',
							'value' => '0',
							),	
						);
		}
				
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'author' => $curr_user_id,	
			//'s'=> $search,
			'meta_key' => 'woo_ua_auction_last_activity',
			'orderby' => 'meta_value_num',
			'order'  => 'DESC',			
			'meta_query' => array($meta_query),
			'tax_query' => $tax_query,
			'auction_arhive' => TRUE
		);

		if ('expired' == $this->auction_status || 'live' == $this->auction_status) {   	

			$args['meta_key'] = 'woo_ua_auction_last_activity';
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'DESC';
			
		} else {

			$args['meta_key'] = 'woo_ua_auction_start_date';
			$args['orderby'] = 'meta_value';
			$args['order'] = 'ASC';

		}
		
		$filter_id = ( isset($_REQUEST['uwa_auction_id']) ) ? absint($_REQUEST['uwa_auction_id']) : '';   
		if ('' != $filter_id) {
			$args['p'] = $filter_id;
		}

		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
		     $args['suppress_filters'] = 1;	
		}

		/*$search = (isset($_POST['s'])) ? $_POST['s'] : '';
		$search = (isset($_POST['uwa_auction_search'])) ? $_POST['uwa_auction_search'] : '';*/

		if(isset($_POST['uwa_auction_search'])){			
			$new_search = sanitize_text_field($_POST['uwa_auction_search']);
			$args['s'] = $new_search;
		}

		$auctions = get_posts($args);
	    $total_items = count($auctions);    

	    /* $this->found_data = array_slice($this->allData, (($current_page - 1) * $per_page), $per_page); */

	    $this->set_pagination_args(array(
	        'total_items' => $total_items,
	        'per_page' => $per_page,
	    ));
	    $this->items = $this->uwa_auction_sort_array($this->uwa_auction_get_data($per_page, 
	    	$current_page));
    	
	}

	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
		$auction_status = isset( $_GET['auction_status'] ) ? sanitize_text_field( wp_unslash( $_GET['auction_status'] ) ) : 'live';

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );
			$style = in_array( $column_key, $hidden ) ? 'display:none;' : '';
			$style_attr = $style ? " style='$style'" : '';
			$id = $with_id ? "id='$column_key'" : '';
	
			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];
				$order = 'asc';
				$class[] = 'sortable';
	
				$current_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
				$current_order   = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : '';

				if ( $current_orderby === $orderby ) {
					$class[] = 'sorted';
					$order = ( 'asc' === $current_order ) ? 'desc' : 'asc';
					$class[] = ( 'asc' === $current_order || 'desc' === $current_order ) ? $current_order : 'asc';
				} else {
					$class[] = $desc_first ? 'desc' : 'asc';
				}
	
				// Dynamic URL to preserve all filters except orderby/order
				$current_url = remove_query_arg( array( 'orderby', 'order' ) );
				$href = add_query_arg( array(
					'orderby' => $orderby,
					'order'   => $order,
				), $current_url );
	
				// Special output for "bidders" column
				if ( $column_key === 'bidders' ) {
					echo "<th scope='col' $id class='" . esc_attr( implode(' ', $class) ) . "'$style_attr>";
					echo "<a href='" . esc_url($href) . "'>";
					echo '<table class="table-th-bidders"><tbody><tr>';
	
					echo '<th class="create_name column_create_name"><a href="' . esc_url($href) . '">' . __('Bidder', 'woo_ua') . '</a></th>';
					echo '<th class="create_bid_amt column_create_bid_amt"><a href="' . esc_url($href) . '">' . __('Bid', 'woo_ua') . '</a></th>';
					echo '<th class="create_max_bid column_create_max_bid"><a href="' . esc_url($href) . '">' . __('Max Bid', 'woo_ua') . '</a></th>';
					echo '<th class="create_bid_time column_create_bid_time"><a href="' . esc_url($href) . '">' . __('Time', 'woo_ua') . '</a></th>';
	
					if ( $auction_status === 'live' ) {
						echo '<th class="choose_winner column_choose_winner"><a href="' . esc_url($href) . '">' . __('Choose Winner', 'woo_ua') . '</a></th>';
					}
	
					echo '</tr></tbody></table>';
					echo '<span class="sorting-indicators">';
					echo '<span class="sorting-indicator asc" aria-hidden="true"></span>';
					echo '<span class="sorting-indicator desc" aria-hidden="true"></span>';
					echo '</span>';
					echo '</a>';
					echo '</th>';
					continue;
				}
	
				// Default sortable header
				echo "<th scope='col' $id class='" . esc_attr( implode( ' ', $class ) ) . "'$style_attr>";
				echo "<a href='" . esc_url( $href ) . "'><span>" . esc_html( $column_display_name ) . "</span>";
				echo "<span class='sorting-indicators'>";
				echo "<span class='sorting-indicator asc' aria-hidden='true'></span>";
				echo "<span class='sorting-indicator desc' aria-hidden='true'></span>";
				echo "</span></a></th>";

			} else {
				// Non-sortable header
				echo "<th scope='col' $id class='" . esc_attr( implode( ' ', $class ) ) . "'$style_attr>" . esc_html( $column_display_name ) . "</th>";
			}
		}
	}

	public function get_result_e(){
    	return $this->allData;
	}

	public function uwa_auction_sort_array( $args ) {

		if (!empty($args)) {        
			$orderby = ( !empty($_GET['orderby']) ) ? sanitize_text_field($_GET['orderby']) : 'title';

			if ('create_date' === $orderby) {                
				$order = ( !empty($_GET['order']) ) ? sanitize_text_field($_GET['order']) : 'asc';
			} else if ('end_date' === $orderby) {              
				$order = ( !empty($_GET['order']) ) ? sanitize_text_field($_GET['order']) : 'asc';
			} else {
				$order = 'desc';
			}               
		
			foreach ($args as $array) {
				$sort_key[] = $array[$orderby];
			}

			if ('asc' == $order) {
				array_multisort($sort_key, SORT_ASC, $args);
			} else {
				array_multisort($sort_key, SORT_DESC, $args);
			}
		}
		return $args;
	}

	public function column_default($item, $column_name){
	    switch ($column_name) {
	        case 'auction_type':
	        case 'title':
	        case 'pro_category':
	        case 'create_date':
	        case 'end_date':	        
	        case 'opening_price':
	        case 'reserve_price':
	        case 'bidders':	  
	        case 'expiry_reason':	  
	        case 'uwa_action':	  
	        	return $item[ $column_name ];
	        default:
	        	return '';

	            /* return print_r($item, true); //Show the whole array for troubleshooting purposes */
	            
	    }
	}
	
} /* end of class */

	
function uwa_manage_auctions_list_page_handler_display() {	

	
	$auction_status = ( isset( $_GET['auction_status'] ) && ! empty( $_GET['auction_status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['auction_status'] ) ) : 'live';
	$auction_class = '';
	if ( 'expired' === $auction_status ) {
		$auction_class = 'admin-auction-expired';
	}
	$user_class = '';
	if ( isset( $_GET['users_auctions'] ) && sanitize_text_field( wp_unslash( $_GET['users_auctions'] ) ) === 'true' ) {
		$user_class = 'user_auction';
	}

?>

	<div class="uwa_main_setting wrap <?php echo $auction_class . ' ' . $user_class; ?>">
	
		<h1 class="uwa_admin_page_title">
			<?php esc_html_e( 'Manage Auctions', 'woo_ua' ); ?>
		</h1>

		<!-- <h2 class="uwa_main_h2"><?php _e( 'Ultimate WooCommerce Auction PRO', 'woo_ua' ); ?>
			<span class="uwa_version_text"><?php _e( 'Version :', 'woo_ua' ); ?> <?php echo UW_AUCTION_PRO_VERSION; ?></span>
		</h2> -->

		<?php uwa_get_plugin_info_version(); ?>
	
		<div id="uwa-auction-banner-text">	
			<?php echo __('If you like <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank"> our plugin working </a> with WooCommerce, please leave us a <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank">★★★★★</a> rating. A huge thanks in advance!', 'woo_ua' ); ?>	 
		</div>

		<br class="clear">
		<?php if (current_user_can('administrator') || current_user_can('shop_manager') ) { ?>

			<div style="float:left;">
				<ul class="subsubsub">
					<li>
						<a class="uwa-highlight-btn <?php echo isset($_GET['users_auctions'])  != 'true' ? 'highlight-btn-active' : 'highlight-btn-disabled'; ?>" 
							href="?page=uwa_manage_auctions" ><?php esc_html_e('Your Auctions', 'woo_ua'); ?></a>
					</li>
					<li>
						<a class="uwa-highlight-btn <?php echo ( isset( $_GET['users_auctions'] ) && sanitize_text_field( wp_unslash( $_GET['users_auctions'] ) ) === 'true' ) ? 'highlight-btn-active' : 'highlight-btn-disabled'; ?>" href="?page=uwa_manage_auctions&users_auctions=true"><?php esc_html_e('User Auctions', 'woo_ua'); ?></a>
					</li>
				</ul>
			</div>


			<?php
			if (isset($_GET['auction_status']) && 'expired' == $_GET['auction_status']
				&& !isset($_GET['users_auctions'])) {
				?>

				<div style="float:right; margin-right:10px;">
					<div class="ex-csv-btn">

						<form id="uwa_frm_file_format" class="uwa_cat" method="post">
												
							<?php
							$enable_category_list = get_option('uwa_exp_csv_enable_category_listing', 'no');
							if ('yes' == $enable_category_list) {


								/* -------- Get Product Categories From Expired Auctions ---------- */

								$curr_user_id = get_current_user_id();
								$auction_meta_query = array(
									'relation' => 'AND',
									array(               
										'key' => 'woo_ua_auction_closed',
										'value' => array( '1', '2', '3', '4' ),
										'compare' => 'IN',
									),
								);

								$auction_tax_query = array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction' );

								$auction_args = array(
									'post_type' => 'product',
									'post_status' => 'publish',
									'ignore_sticky_posts' => 1,
									'posts_per_page' => -1,         
									'author' => $curr_user_id,  
									//'s'=> $search,                    
									'meta_query' => array( $auction_meta_query ),
									'tax_query' => $auction_tax_query,
									'auction_arhive' => true,
								);

								$auctions = get_posts($auction_args);
								$uwa_categories_ids = array();

								if (is_array($auctions) && count($auctions) > 0) { 

									foreach ($auctions as $single_auction) { 

										$auction_ID = $single_auction->ID;

										$categories = get_the_terms( $auction_ID, 'product_cat' );
										if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
											foreach ( $categories as $category ) {
												$uwa_categories_ids[] = $category->term_id;                                         
											}                                           
										} 

									} /* end of foreach */
										
									$final_categories_ids = array_values(array_unique($uwa_categories_ids));
									$total_categories = count($final_categories_ids);

									if ($total_categories > 0) { 

											/*for ($i=0; $i<$total_categories; $i++) { 
												$term_id = $final_categories_ids[$i];*/

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
													<option value="all">All Categories</option>

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
								<label for="file_format"><?php esc_html_e('Choose file format:', 'woo_ua'); ?></label>
								<select name="file_format" id="file_format" onchange="updateExportLink()">
									<option value="csv" selected>CSV</option>
									<option value="xls">XLSX</option>
									<option value="pdf">PDF</option>
								</select>

								<?php 
								$export_type = '';
								if (isset($_REQUEST[ 'users_auctions' ])) {
									$export_type = sanitize_text_field($_REQUEST[ 'users_auctions' ]);
								} else {
									$export_type = 'false';
								}
								if ('true' == $export_type) {
									?>
									<a id="exportLink" style="border: 1px solid #2271b1;" href="<?php echo esc_url(wp_nonce_url(admin_url( 'admin.php?page=uwa_manage_auctions&users_auctions=true&auction_status=expired&user=otherbids' ), 'uwa_export_auctions')); ?>&action=uwa_download_csv&category=all" class="uwa-highlight-btn highlight-btn-disabled">
										<?php esc_html_e('Export Expired Auctions', 'woo_ua'); ?></a>
								<?php } else { ?>
									<a id="exportLink" style="border: 1px solid #2271b1;" href="<?php echo esc_url(wp_nonce_url(admin_url( 'admin.php?page=uwa_manage_auctions&auction_status=expired&user=yourbids' ), 'uwa_export_auctions')); ?>&action=uwa_download_csv&category=all" class="uwa-highlight-btn highlight-btn-disabled">
										<?php esc_html_e('Export Expired Auctions', 'woo_ua'); ?></a>
								<?php } ?>
							</div>				
						</form>
					</div>
				</div>

			<?php } /* end of if status expired */ ?>				

			<script>

				jQuery("document").ready(function($){

					/* default values on load */
					if (document.getElementById('category_list')) {
						selectedCategory = document.getElementById('category_list').value;
					} else {
						selectedCategory = "all";
					}

					var selectedFormat = document.getElementById('file_format').value;
					var exportLink = document.getElementById('exportLink');
					var baseUrl = exportLink.getAttribute('href').split('&action=')[0]; 

					// Update the action and nonce based on selected format
					if (selectedFormat === 'csv') {
						exportLink.href = baseUrl + '&action=uwa_download_csv&category=' + selectedCategory;			            
					} else if (selectedFormat === 'xls') {
						exportLink.href = baseUrl + '&action=uwa_download_xls&category=' + selectedCategory;			            
					} else if (selectedFormat === 'pdf') {
						exportLink.href = baseUrl + '&action=uwa_download_pdf&category=' + selectedCategory;			            
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

					// Update the action and nonce based on selected format
					if (selectedFormat === 'csv') {
						exportLink.href = baseUrl + '&action=uwa_download_csv&category=' + selectedCategory;			            
					} else if (selectedFormat === 'xls') {
						exportLink.href = baseUrl + '&action=uwa_download_xls&category=' + selectedCategory;			            
					} else if (selectedFormat === 'pdf') {
						exportLink.href = baseUrl + '&action=uwa_download_pdf&category=' + selectedCategory;			            
					}     
				}
			</script>			

			<br class="clear">	
		<?php } ?> 

		<?php

		if (isset($_GET['page']) && 'uwa_manage_auctions' == $_GET['page'] && 
			isset($_GET['users_auctions']) && 'true' == $_GET['users_auctions']) {
				include_once UW_AUCTION_PRO_ADMIN . '/uwa_manage_user_auctions.php';
				uwa_manage_users_auctions_list_page_handler_display();
		} else {
	
			if (isset($_REQUEST[ 'auction_status' ])) {
				$manage_auction_tab = sanitize_text_field($_REQUEST[ 'auction_status' ]);
			} else {
				$manage_auction_tab = 'live';
			}       

			?>

			<div class="uwa-action-container" style="float:right;margin-right: 10px;">
				<form action="" method="POST">

					<input type="text" name="uwa_auction_search" value="<?php echo isset( $_POST['uwa_auction_search'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['uwa_auction_search'] ) ) ) : ''; ?>" />

					<input type="submit" class="button-secondary" name="uwa_auction_search_submit" value="Search" />
					<input type="hidden" id="statusofauction" value="<?php echo esc_attr($manage_auction_tab); ?>">
				</form>
			</div>		

			<ul class="subsubsub">
				<li><a href="?page=uwa_manage_auctions&auction_status=live" 
						class="<?php echo 'live' == $manage_auction_tab ? 'current' : ''; ?>">
					<?php esc_html_e( 'Live Auctions', 'woo_ua'); ?></a> 
					(<?php echo esc_html(uwa_get_auctions_count('live')); ?>) |
				</li>

				<li><a href="?page=uwa_manage_auctions&auction_status=expired" 
					class="<?php echo 'expired' == $manage_auction_tab ? 'current' : ''; ?>">
					<?php esc_html_e( 'Expired Auctions', 'woo_ua'); ?></a> 
					(<?php echo esc_html(uwa_get_auctions_count('expired')); ?>) |
				</li>

				<li><a href="?page=uwa_manage_auctions&auction_status=scheduled" 
					class="<?php echo 'scheduled' == $manage_auction_tab ? 'current' : ''; ?>">
					<?php esc_html_e( 'Future Auctions', 'woo_ua'); ?></a> 
					(<?php echo esc_html(uwa_get_auctions_count('scheduled')); ?>)
				</li>
			</ul>
			<br class="clear">

				<?php if ('expired' == $manage_auction_tab) { ?>
					<div class="uwa-auction-expired-bid-count">
						<?php esc_html_e( 'Expired Auctions Total Bid Count :', 'woo_ua'); ?>
						<?php echo esc_html(uwa_get_total_admin_expireauctions_count()); ?>
					</div>
				<?php
				}                   

				global $wpdb;
				$table = new Uwa_Manage_Auctions_List_Table();
				$table->prepare_items();    
				$table->display();
				?>
							
			
		</div>	
			<?php
		} /* end of else  */    
} /* end of fuction - uwa_manage_auctions_list_page_handler_display */ 

?>
