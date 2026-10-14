<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

global $wpdb;
$table_name = '';

class Custom_Admin_Table extends WP_List_Table {
    function __construct() {
        parent::__construct(array(
            'singular' => 'custom_item',
            'plural'   => 'custom_items',
            'ajax'     => false,
        ));
		add_thickbox();
    }

    function get_columns() {
        return array(
            'id'       => __("ID", "woo_ua"),
            'pid'      => __("Product Name", "woo_ua"),
            'uid'      => __("User Name", "woo_ua"),
            'hold_amount_type' => __("Hold Type", "woo_ua"),
			'transaction_amount'=> __("Hold Amount", "woo_ua"),
			'main_amount'=> __("BID Amount", "woo_ua"),
            'status'   => __("Status", "woo_ua"),
            'created_at' => __("Created At", "woo_ua"),
			'updated_at'=> __("Updated At", "woo_ua"),
			'response_json'=> __("View Detail", "woo_ua"),
        );
    }

    function prepare_items() {
        global $wpdb;
		$table_name = $wpdb->prefix . 'auction_hold_payment';
        $per_page = 20; // Number of items per page
        $current_page = $this->get_pagenum();
		$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
		$this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $offset = ($current_page - 1) * $per_page;
/*        $items = $wpdb->get_results("SELECT * FROM $table_name LIMIT $per_page OFFSET $offset", ARRAY_A);
*/        $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT $per_page OFFSET $offset", ARRAY_A);        

        $this->items = $items;
    }

    function column_default($item, $column_name) {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

	

	function column_hold_amount_type($item) {
		/*
		 	uwa_stripe_hold_fix_amount_charge,
			uwa_stripe_hold_fix_percentage_charge,
			uwa_stripe_hold_exact_bid_charge 
		*/
		if($item['hold_amount_type'] == 'uwa_stripe_hold_fix_amount_charge'){			
			return __("Fix Amount", "woo_ua");

		}elseif($item['hold_amount_type'] == 'uwa_stripe_hold_fix_percentage_charge'){			
			return __("Fix Percentage", "woo_ua");

		}else{
			return __("Exact Bid Amount", "woo_ua");
		}
        
    }

	function column_pid($item) {

		$item_pid = $item['pid'];
		$product_title = get_the_title($item_pid);

		if ($product_title) {
			// Product found, create a link to its edit page
			$edit_link = get_edit_post_link($item_pid);
			$product_status = get_post_status($item_pid);

			
			if ($product_status!='trash') {
				$product_title = "<a href='$edit_link'>$product_title</a>";
			}
		}

        return ($product_title);
    }
	function column_uid($item) {
		$user_data = get_userdata($item['uid']);
		if ($user_data) {
			$username = $user_data->user_login;
			return ($username);
			
		} else {
			return ($item['uid']);
		}
	}
	

    function column_status($item) {
        return ($item['status']);
    }
	function column_response_json($item) {
		$p_hold_view_detail = "";

		if(!empty($item['response_json'])){
		$p_hold_view_detail = "<a title='Payment HOLD Response' href='#TB_inline?&width=700&height=400&inlineId=hold-id-".$item['id']."' class='button button-secondary thickbox'>".__('View Detail', "woo_ua").'</a>';
		?>
		<div id="hold-id-<?php echo $item['id'];?>" style="display:none;">
			<?php 
				$response_data = json_decode($item['response_json'], true);

				// Encode the data as JSON with JSON_PRETTY_PRINT option
				$pretty_json = json_encode($response_data, JSON_PRETTY_PRINT);
				
				// Display the formatted JSON
				echo '<pre>' . $pretty_json . '</pre>';
				
			?>
		</div>
		<?php }
		return $p_hold_view_detail;
    }

	
}
	$custom_table = new Custom_Admin_Table();
    $custom_table->prepare_items();
	?>
	
        <form method="post">
            <input type="hidden" name="page" value="custom-table">
            <?php $custom_table->display(); ?>
        </form>