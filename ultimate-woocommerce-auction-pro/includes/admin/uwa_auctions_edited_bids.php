<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

global $wpdb;
$table_name = '';

class UWA_Edited_Bids_Table extends WP_List_Table {
    function __construct() {
        parent::__construct(array(
            'singular' => 'edited_bid',
            'plural'   => 'edited_bids',
            'ajax'     => false,
        ));
		add_thickbox();
    }

    function get_columns() {
        return array(
            'id'       => __("ID", "woo_ua"),
            'auction_logid' => __("Auction Log ID", "woo_ua"),
            'auction_id'      => __("Product Name", "woo_ua"),
            'userid'      => __("User", "woo_ua"),
            'old_bid' => __("Old Bid", "woo_ua"),
            'new_bid' => __("New Bid", "woo_ua"),            
            'changed_by' => __("Updated By", "woo_ua"),
            'auction_type' => __("Auction Type", "woo_ua"),
            'added_date' => __("Added Date", "woo_ua"),
            'changed_date' => __("Updated Date", "woo_ua"),
        );
    }

    function prepare_items() {
        global $wpdb;

		$table_name = $wpdb->prefix . 'woo_ua_auction_changebids_log';
        $per_page = 20; // Number of items per page

        $current_page = $this->get_pagenum();
		$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE uwa_action='update'");

		$this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $offset = ($current_page - 1) * $per_page;
      	/* $items = $wpdb->get_results("SELECT * FROM $table_name LIMIT $per_page OFFSET $offset", ARRAY_A); */

        $items = $wpdb->get_results("SELECT * FROM $table_name WHERE uwa_action='update' ORDER BY id DESC 
				LIMIT $per_page OFFSET $offset", ARRAY_A);

        $this->items = $items;
    }

    function column_default($item, $column_name) {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

	function column_auction_id($item) {

		$item_pid = $item['auction_id'];
		$product_title = get_the_title($item_pid);

		if ($product_title) {

			// Product found, create a link to its edit page
			$edit_link = get_edit_post_link($item_pid);
			$product_status = get_post_status($item_pid);
			
			if ($product_status != 'trash') {
				$product_title = "<a href='$edit_link'>$product_title</a>";
			}
		}

        return ($product_title);
    }

	function column_userid($item) {

		$user_data = get_userdata($item['userid']);

		if ($user_data) {
			$username = $user_data->user_login;
			return ($username);
			
		} else {
			return ($item['userid']);
		}
	}

	function column_old_bid($item) {

		$bid_price = wc_price($item['old_bid']);
		return $bid_price;
	}

	function column_new_bid($item) {

		$bid_price = wc_price($item['new_bid']);
		return $bid_price;
	}
	
	
}
	$custom_table = new UWA_Edited_Bids_Table();
    $custom_table->prepare_items();

    $base_url = admin_url('admin.php?page=uwa_auction_reports&type=edited_bids');
	$delete_url = wp_nonce_url( $base_url . "&action=uwa_delete_edited_auction_bids&delete=editbids", 'uwa_delete_edited_auction_bids' );

    if (isset($_GET['deleted_edited_bids']) && is_numeric($_GET['deleted_edited_bids'])) {
        $deleted_count = intval($_GET['deleted_edited_bids']);
        if ($deleted_count > 0) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('All edited bid(s) deleted successfully.', 'woo_ua') . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . __('No bids were deleted.', 'woo_ua') . '</p>';
            echo '</div>';
        }
    }

	?>  

    <form id="uwa_frm_delete_edited_bids" class="uwa_delete_edited_auction_bids" method="post">
        <div class="delete">
            <a style="border: 1px solid #2271b1;" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete all edited bids Logs?', 'woo_ua'); ?>');" class="uwa-highlight-btn highlight-btn-disabled"><?php esc_html_e('Delete All Log', 'woo_ua'); ?></a>
        </div>
    </form>
    <form method="post">
        <input type="hidden" name="page" value="custom-table">
        <?php $custom_table->display(); ?>
    </form>
    


