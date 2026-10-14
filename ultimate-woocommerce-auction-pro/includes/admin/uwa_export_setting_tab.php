<?php

/**
 * Ultimate WooCommerce Auction Pro Export Setting Tab
 *
 * @package Ultimate WooCommerce Auction Pro
 * @since 1.0
 *
 */

if (isset($_POST['uwa-settings-submit']) == 'Save Changes') {

    if ( ! isset( $_POST['uwa_nonce_export_settings_tab'] ) || ! wp_verify_nonce( 
        sanitize_text_field($_POST['uwa_nonce_export_settings_tab']), 'uwa_nonce_form_export_settings_tab' )) {
        wp_die( esc_html__( 'Nonce verification failed', 'woo_ua' ) );        
    }

    /*  ---------------------- Admin export auctions ------------------------  */

    if (isset($_POST['uwa_exp_csv_disable_first_name']) && 1 == $_POST['uwa_exp_csv_disable_first_name']) {
        update_option('uwa_exp_csv_disable_first_name', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_first_name', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_company']) && 1 == $_POST['uwa_exp_csv_disable_company']) {
        update_option('uwa_exp_csv_disable_company', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_company', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_second_highest_bidder_company']) &&
        1 == $_POST['uwa_exp_csv_disable_second_highest_bidder_company']) {
        update_option('uwa_exp_csv_disable_second_highest_bidder_company', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_second_highest_bidder_company', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_third_highest_bidder_company']) &&
        1 == $_POST['uwa_exp_csv_disable_third_highest_bidder_company']
    ) {
        update_option('uwa_exp_csv_disable_third_highest_bidder_company', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_third_highest_bidder_company', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_highest_bidder']) && 
        1 == $_POST['uwa_exp_csv_disable_highest_bidder']) {
        update_option('uwa_exp_csv_disable_highest_bidder', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_highest_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_third_highest_bidder']) &&
        1 == $_POST['uwa_exp_csv_disable_third_highest_bidder']) {
        update_option('uwa_exp_csv_disable_third_highest_bidder', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_third_highest_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_last_name']) && 1 == $_POST['uwa_exp_csv_disable_last_name']) {
        update_option('uwa_exp_csv_disable_last_name', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_last_name', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_highest_bid']) && 1 == $_POST['uwa_exp_csv_disable_highest_bid']) {
        update_option('uwa_exp_csv_disable_highest_bid', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_highest_bid', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_third_highest_bid']) && 
        1 == $_POST['uwa_exp_csv_disable_third_highest_bid']) {
        update_option('uwa_exp_csv_disable_third_highest_bid', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_third_highest_bid', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_address1']) && 1 == $_POST['uwa_exp_csv_disable_address1']) {
        update_option('uwa_exp_csv_disable_address1', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_address1', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_highest_bidder_mail']) && 
        1 == $_POST['uwa_exp_csv_disable_highest_bidder_mail']) {
        update_option('uwa_exp_csv_disable_highest_bidder_mail', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_highest_bidder_mail', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_third_highest_bidder_mail']) && 
        1 == $_POST['uwa_exp_csv_disable_third_highest_bidder_mail']) {
        update_option('uwa_exp_csv_disable_third_highest_bidder_mail', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_third_highest_bidder_mail', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_address2']) && 1 == $_POST['uwa_exp_csv_disable_address2']) {
        update_option('uwa_exp_csv_disable_address2', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_address2', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_second_highest_bidder']) && 
        1 == $_POST['uwa_exp_csv_disable_second_highest_bidder']) {
        update_option('uwa_exp_csv_disable_second_highest_bidder', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_second_highest_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_end_date']) && 1 == $_POST['uwa_exp_csv_disable_end_date']) {
        update_option('uwa_exp_csv_disable_end_date', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_end_date', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_city']) && 1 == $_POST['uwa_exp_csv_disable_city']) {
        update_option('uwa_exp_csv_disable_city', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_city', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_second_highest_bid']) && 
        1 == $_POST['uwa_exp_csv_disable_second_highest_bid']) {
        update_option('uwa_exp_csv_disable_second_highest_bid', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_second_highest_bid', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_expiry_reason']) && 
        1 == $_POST['uwa_exp_csv_disable_expiry_reason']) {
        update_option('uwa_exp_csv_disable_expiry_reason', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_expiry_reason', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_country']) && 1 == $_POST['uwa_exp_csv_disable_country']) {
        update_option('uwa_exp_csv_disable_country', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_country', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_second_highest_bidder_mail']) && 
        1 == $_POST['uwa_exp_csv_disable_second_highest_bidder_mail'] ) {
        update_option('uwa_exp_csv_disable_second_highest_bidder_mail', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_second_highest_bidder_mail', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_product_category']) && 
        1 == $_POST['uwa_exp_csv_disable_product_category']) {
        update_option('uwa_exp_csv_disable_product_category', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_product_category', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_phone']) && 
        1 == $_POST['uwa_exp_csv_disable_phone']) {
        update_option('uwa_exp_csv_disable_phone', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_phone', 'no');
    }

    if (isset($_POST['uwa_exp_csv_disable_postal_code']) && 
        1 == $_POST['uwa_exp_csv_disable_postal_code']) {
        update_option('uwa_exp_csv_disable_postal_code', 'yes');
    } else {
        update_option('uwa_exp_csv_disable_postal_code', 'no');
    }

    if (isset($_POST['uwa_exp_csv_enable_category_listing']) && 
        1 == $_POST['uwa_exp_csv_enable_category_listing']) {
        update_option('uwa_exp_csv_enable_category_listing', 'yes');
    } else {
        update_option('uwa_exp_csv_enable_category_listing', 'no');
    }

    if (isset($_POST['uwa_exp_csv_display_bids'])) {
        update_option('uwa_exp_csv_display_bids', 
            sanitize_text_field($_POST['uwa_exp_csv_display_bids']));         
    }

    if (isset($_POST['uwa_exp_csv_enable_pro_attr']) && 
        1 == $_POST['uwa_exp_csv_enable_pro_attr']) {
        update_option('uwa_exp_csv_enable_pro_attr', 'yes');
    } else {
        update_option('uwa_exp_csv_enable_pro_attr', 'no');
    }    

    if (isset($_POST['uwa_exp_csv_display_all_bids_col_bid']) && 
        1 == $_POST['uwa_exp_csv_display_all_bids_col_bid']) {
        update_option('uwa_exp_csv_display_all_bids_col_bid', 'yes');
    } else {
        update_option('uwa_exp_csv_display_all_bids_col_bid', 'no');
    }

    if (isset($_POST['uwa_exp_csv_display_all_bids_col_bidder']) && 
        1 == $_POST['uwa_exp_csv_display_all_bids_col_bidder']) {
        update_option('uwa_exp_csv_display_all_bids_col_bidder', 'yes');
    } else {
        update_option('uwa_exp_csv_display_all_bids_col_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_csv_display_all_bids_col_company']) && 
        1 == $_POST['uwa_exp_csv_display_all_bids_col_company']) {
        update_option('uwa_exp_csv_display_all_bids_col_company', 'yes');
    } else {
        update_option('uwa_exp_csv_display_all_bids_col_company', 'no');
    }

    if (isset($_POST['uwa_exp_csv_display_all_bids_col_email']) && 
        1 == $_POST['uwa_exp_csv_display_all_bids_col_email']) {
        update_option('uwa_exp_csv_display_all_bids_col_email', 'yes');
    } else {
        update_option('uwa_exp_csv_display_all_bids_col_email', 'no');
    }


    /*  ---------------------- Frontend users export auctions ------------------------  */

    if (isset($_POST['uwa_bidsactive_user_csv_enable_pro_attr']) && 
        1 == $_POST['uwa_bidsactive_user_csv_enable_pro_attr']) {
        update_option('uwa_bidsactive_user_csv_enable_pro_attr', 'yes');
    } else {
        update_option('uwa_bidsactive_user_csv_enable_pro_attr', 'no');
    }

    if (isset($_POST['uwa_bidswon_user_csv_enable_pro_attr']) && 
        1 == $_POST['uwa_bidswon_user_csv_enable_pro_attr']) {
        update_option('uwa_bidswon_user_csv_enable_pro_attr', 'yes');
    } else {
        update_option('uwa_bidswon_user_csv_enable_pro_attr', 'no');
    }

    if (isset($_POST['uwa_bidswon_user_csv_enable_sec_highbid']) && 
        1 == $_POST['uwa_bidswon_user_csv_enable_sec_highbid']) {
        update_option('uwa_bidswon_user_csv_enable_sec_highbid', 'yes');
    } else {
        update_option('uwa_bidswon_user_csv_enable_sec_highbid', 'no');
    }

    if (isset($_POST['uwa_bidswon_user_csv_enable_third_highbid']) && 
        1 == $_POST['uwa_bidswon_user_csv_enable_third_highbid']) {
        update_option('uwa_bidswon_user_csv_enable_third_highbid', 'yes');
    } else {
        update_option('uwa_bidswon_user_csv_enable_third_highbid', 'no');
    }

    if (isset($_POST['uwa_bidslost_user_csv_enable_pro_attr']) && 
        1 == $_POST['uwa_bidslost_user_csv_enable_pro_attr']) {
        update_option('uwa_bidslost_user_csv_enable_pro_attr', 'yes');
    } else {
        update_option('uwa_bidslost_user_csv_enable_pro_attr', 'no');
    }

    if (isset($_POST['uwa_bidslost_user_csv_enable_highbid']) && 
        1 == $_POST['uwa_bidslost_user_csv_enable_highbid']) {
        update_option('uwa_bidslost_user_csv_enable_highbid', 'yes');
    } else {
        update_option('uwa_bidslost_user_csv_enable_highbid', 'no');
    }

    if (isset($_POST['uwa_bidslost_user_csv_enable_sec_highbid']) && 
        1 == $_POST['uwa_bidslost_user_csv_enable_sec_highbid']) {
        update_option('uwa_bidslost_user_csv_enable_sec_highbid', 'yes');
    } else {
        update_option('uwa_bidslost_user_csv_enable_sec_highbid', 'no');
    }

    if (isset($_POST['uwa_bidslost_user_csv_enable_third_highbid']) && 
        1 == $_POST['uwa_bidslost_user_csv_enable_third_highbid']) {
        update_option('uwa_bidslost_user_csv_enable_third_highbid', 'yes');
    } else {
        update_option('uwa_bidslost_user_csv_enable_third_highbid', 'no');
    }


    if (isset($_POST['uwa_exp_user_csv_dataformat_csv']) && 
        1 == $_POST['uwa_exp_user_csv_dataformat_csv']) {
        update_option('uwa_exp_user_csv_dataformat_csv', 'yes');
    } else {
        update_option('uwa_exp_user_csv_dataformat_csv', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_dataformat_xls']) && 
        1 == $_POST['uwa_exp_user_csv_dataformat_xls']) {
        update_option('uwa_exp_user_csv_dataformat_xls', 'yes');
    } else {
        update_option('uwa_exp_user_csv_dataformat_xls', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_dataformat_pdf']) && 
        1 == $_POST['uwa_exp_user_csv_dataformat_pdf']) {
        update_option('uwa_exp_user_csv_dataformat_pdf', 'yes');
    } else {
        update_option('uwa_exp_user_csv_dataformat_pdf', 'no');
    }


    if (isset($_POST['uwa_exp_user_csv_enable_pro_attr']) && 
        1 == $_POST['uwa_exp_user_csv_enable_pro_attr']) {
        update_option('uwa_exp_user_csv_enable_pro_attr', 'yes');
    } else {
        update_option('uwa_exp_user_csv_enable_pro_attr', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_enable_category_listing']) && 
        1 == $_POST['uwa_exp_user_csv_enable_category_listing']) {
        update_option('uwa_exp_user_csv_enable_category_listing', 'yes');
    } else {
        update_option('uwa_exp_user_csv_enable_category_listing', 'no');
    }

    if (isset($_POST['uwa_expired_auctions_download_user']) &&
        1 == $_POST['uwa_expired_auctions_download_user']) {
        update_option('uwa_expired_auctions_download_user', 'yes');
    } else {
        update_option('uwa_expired_auctions_download_user', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_first_name']) && 
        1 == $_POST['uwa_exp_user_csv_disable_first_name']) {
        update_option('uwa_exp_user_csv_disable_first_name', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_first_name', 'no');
    }
    
    if (isset($_POST['uwa_exp_user_csv_disable_company']) && 
        1 == $_POST['uwa_exp_user_csv_disable_company']) {
        update_option('uwa_exp_user_csv_disable_company', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_company', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_second_highest_bidder_company']) &&
        1 == $_POST['uwa_exp_user_csv_disable_second_highest_bidder_company']) {
        update_option('uwa_exp_user_csv_disable_second_highest_bidder_company', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_second_highest_bidder_company', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_third_highest_bidder_company']) &&
        1 == $_POST['uwa_exp_user_csv_disable_third_highest_bidder_company']) {
        update_option('uwa_exp_user_csv_disable_third_highest_bidder_company', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_third_highest_bidder_company', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_highest_bidder']) && 
        1 == $_POST['uwa_exp_user_csv_disable_highest_bidder']) {
        update_option('uwa_exp_user_csv_disable_highest_bidder', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_highest_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_third_highest_bidder']) && 
        1 == $_POST['uwa_exp_user_csv_disable_third_highest_bidder']) {
        update_option('uwa_exp_user_csv_disable_third_highest_bidder', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_third_highest_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_last_name']) && 
        1 == $_POST['uwa_exp_user_csv_disable_last_name']) {
        update_option('uwa_exp_user_csv_disable_last_name', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_last_name', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_highest_bid']) && 
        1 == $_POST['uwa_exp_user_csv_disable_highest_bid']) {
        update_option('uwa_exp_user_csv_disable_highest_bid', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_highest_bid', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_third_highest_bid']) && 
        1 == $_POST['uwa_exp_user_csv_disable_third_highest_bid']) {
        update_option('uwa_exp_user_csv_disable_third_highest_bid', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_third_highest_bid', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_address1']) && 
        1 == $_POST['uwa_exp_user_csv_disable_address1']) {
        update_option('uwa_exp_user_csv_disable_address1', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_address1', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_highest_bidder_mail']) &&
        1 == $_POST['uwa_exp_user_csv_disable_highest_bidder_mail']) {
        update_option('uwa_exp_user_csv_disable_highest_bidder_mail', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_highest_bidder_mail', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_third_highest_bidder_mail']) &&
        1 == $_POST['uwa_exp_user_csv_disable_third_highest_bidder_mail']) {
        update_option('uwa_exp_user_csv_disable_third_highest_bidder_mail', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_third_highest_bidder_mail', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_address2']) && 
        1 == $_POST['uwa_exp_user_csv_disable_address2']) {
        update_option('uwa_exp_user_csv_disable_address2', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_address2', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_second_highest_bidder']) &&
        1 == $_POST['uwa_exp_user_csv_disable_second_highest_bidder']) {
        update_option('uwa_exp_user_csv_disable_second_highest_bidder', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_second_highest_bidder', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_end_date']) && 
        1 == $_POST['uwa_exp_user_csv_disable_end_date']) {
        update_option('uwa_exp_user_csv_disable_end_date', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_end_date', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_city']) && 
        1 == $_POST['uwa_exp_user_csv_disable_city']) {
        update_option('uwa_exp_user_csv_disable_city', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_city', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_second_highest_bid']) &&
        1 == $_POST['uwa_exp_user_csv_disable_second_highest_bid']) {
        update_option('uwa_exp_user_csv_disable_second_highest_bid', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_second_highest_bid', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_expiry_reason']) && 
        1 == $_POST['uwa_exp_user_csv_disable_expiry_reason']) {
        update_option('uwa_exp_user_csv_disable_expiry_reason', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_expiry_reason', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_country']) && 
        1 == $_POST['uwa_exp_user_csv_disable_country']) {
        update_option('uwa_exp_user_csv_disable_country', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_country', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_second_highest_bidder_mail']) &&
        1 == $_POST['uwa_exp_user_csv_disable_second_highest_bidder_mail']) {
        update_option('uwa_exp_user_csv_disable_second_highest_bidder_mail', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_second_highest_bidder_mail', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_product_category']) && 
        1 == $_POST['uwa_exp_user_csv_disable_product_category']) {
        update_option('uwa_exp_user_csv_disable_product_category', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_product_category', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_phone']) && 
        1 == $_POST['uwa_exp_user_csv_disable_phone']) {
        update_option('uwa_exp_user_csv_disable_phone', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_phone', 'no');
    }

    if (isset($_POST['uwa_exp_user_csv_disable_postal_code']) && 
        1 == $_POST['uwa_exp_user_csv_disable_postal_code']) {
        update_option('uwa_exp_user_csv_disable_postal_code', 'yes');
    } else {
        update_option('uwa_exp_user_csv_disable_postal_code', 'no');
    }

    if (isset($_POST['uwa_slient_live_auctions_export_user']) &&
        1 == $_POST['uwa_slient_live_auctions_export_user']) {
        update_option('uwa_slient_live_auctions_export_user', 'yes');
    } else {
        update_option('uwa_slient_live_auctions_export_user', 'no');
    }

    if (isset($_POST['uwa_importbids_user_xlsx_enable_pro_attr']) && 
        1 == $_POST['uwa_importbids_user_xlsx_enable_pro_attr']) {
        update_option('uwa_importbids_user_xlsx_enable_pro_attr', 'yes');
    } else {
        update_option('uwa_importbids_user_xlsx_enable_pro_attr', 'no');
    }

    if (isset($_POST['uwa_bid_offline_disable_category_filter']) &&
        1 == $_POST['uwa_bid_offline_disable_category_filter']) {
        update_option('uwa_bid_offline_disable_category_filter', 'yes');
    } else {
        update_option('uwa_bid_offline_disable_category_filter', 'no');
    }

    if (isset($_POST['uwa_bid_offline_opening_price']) &&
        1 == $_POST['uwa_bid_offline_opening_price']) {
        update_option('uwa_bid_offline_opening_price', 'yes');
    } else {
        update_option('uwa_bid_offline_opening_price', 'no');
    }

    if (isset($_POST['uwa_bid_offline_sort_pro_title_asc']) &&
        1 == $_POST['uwa_bid_offline_sort_pro_title_asc']) {
        update_option('uwa_bid_offline_sort_pro_title_asc', 'yes');
    } else {
        update_option('uwa_bid_offline_sort_pro_title_asc', 'no');
    }

    if (isset($_POST['uwa_csv_delimiter']) && in_array($_POST['uwa_csv_delimiter'], [',', ';'])) {
        update_option('uwa_csv_delimiter', sanitize_text_field($_POST['uwa_csv_delimiter']));
    } else {
        update_option('uwa_csv_delimiter', ','); // Default to comma if not set
    }
    
}

    /**
     * Get All Details From DB.
     */

/* Admin expired auctions - disable different columns */
$uwa_exp_csv_disable_first_name = get_option('uwa_exp_csv_disable_first_name', 'no');
$uwa_exp_csv_disable_first_name_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_first_name) {
    $uwa_exp_csv_disable_first_name_checked_val = 'checked';
}

$uwa_exp_csv_disable_company = get_option('uwa_exp_csv_disable_company', 'no');
$uwa_exp_csv_disable_company_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_company) {
    $uwa_exp_csv_disable_company_checked_val = 'checked';
}

$uwa_exp_csv_disable_second_highest_bidder_company = get_option('uwa_exp_csv_disable_second_highest_bidder_company', 
    'no');
$uwa_exp_csv_disable_sec_highbidder_company_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_second_highest_bidder_company) {
    $uwa_exp_csv_disable_sec_highbidder_company_checked_val = 'checked';
}

$uwa_exp_csv_disable_third_highest_bidder_company = get_option('uwa_exp_csv_disable_third_highest_bidder_company', 
    'no');
$uwa_exp_csv_disable_thrd_highbidder_company_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_third_highest_bidder_company) {
    $uwa_exp_csv_disable_thrd_highbidder_company_checked_val = 'checked';
}

$uwa_exp_csv_disable_highest_bidder = get_option('uwa_exp_csv_disable_highest_bidder', 'no');
$uwa_exp_csv_disable_highest_bidder_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_highest_bidder) {
    $uwa_exp_csv_disable_highest_bidder_checked_val = 'checked';
}

$uwa_exp_csv_disable_third_highest_bidder = get_option('uwa_exp_csv_disable_third_highest_bidder', 'no');
$uwa_exp_csv_disable_third_highest_bidder_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_third_highest_bidder) {
    $uwa_exp_csv_disable_third_highest_bidder_checked_val = 'checked';
}

$uwa_exp_csv_disable_last_name = get_option('uwa_exp_csv_disable_last_name', 'no');
$uwa_exp_csv_disable_last_name_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_last_name) {
    $uwa_exp_csv_disable_last_name_checked_val = 'checked';
}

$uwa_exp_csv_disable_highest_bid = get_option('uwa_exp_csv_disable_highest_bid', 'no');
$uwa_exp_csv_disable_highest_bid_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_highest_bid) {
    $uwa_exp_csv_disable_highest_bid_checked_val = 'checked';
}

$uwa_exp_csv_disable_third_highest_bid = get_option('uwa_exp_csv_disable_third_highest_bid', 'no');
$uwa_exp_csv_disable_third_highest_bid_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_third_highest_bid) {
    $uwa_exp_csv_disable_third_highest_bid_checked_val = 'checked';
}

$uwa_exp_csv_disable_address1 = get_option('uwa_exp_csv_disable_address1', 'no');
$uwa_exp_csv_disable_address1_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_address1) {
    $uwa_exp_csv_disable_address1_checked_val = 'checked';
}

$uwa_exp_csv_disable_highest_bidder_mail = get_option('uwa_exp_csv_disable_highest_bidder_mail', 'no');
$uwa_exp_csv_disable_highest_bidder_mail_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_highest_bidder_mail) {
    $uwa_exp_csv_disable_highest_bidder_mail_checked_val = 'checked';
}

$uwa_exp_csv_disable_third_highest_bidder_mail = get_option('uwa_exp_csv_disable_third_highest_bidder_mail', 'no');
$uwa_exp_csv_disable_third_highest_bidder_mail_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_third_highest_bidder_mail) {
    $uwa_exp_csv_disable_third_highest_bidder_mail_checked_val = 'checked';
}

$uwa_exp_csv_disable_address2 = get_option('uwa_exp_csv_disable_address2', 'no');
$uwa_exp_csv_disable_address2_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_address2) {
    $uwa_exp_csv_disable_address2_checked_val = 'checked';
}

$uwa_exp_csv_disable_second_highest_bidder = get_option('uwa_exp_csv_disable_second_highest_bidder', 'no');
$uwa_exp_csv_disable_second_highest_bidder_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_second_highest_bidder) {
    $uwa_exp_csv_disable_second_highest_bidder_checked_val = 'checked';
}

$uwa_exp_csv_disable_end_date = get_option('uwa_exp_csv_disable_end_date', 'no');
$uwa_exp_csv_disable_end_date_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_end_date) {
    $uwa_exp_csv_disable_end_date_checked_val = 'checked';
}

$uwa_exp_csv_disable_city = get_option('uwa_exp_csv_disable_city', 'no');
$uwa_exp_csv_disable_city_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_city) {
    $uwa_exp_csv_disable_city_checked_val = 'checked';
}

$uwa_exp_csv_disable_second_highest_bid = get_option('uwa_exp_csv_disable_second_highest_bid', 'no');
$uwa_exp_csv_disable_second_highest_bid_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_second_highest_bid) {
    $uwa_exp_csv_disable_second_highest_bid_checked_val = 'checked';
}

$uwa_exp_csv_disable_expiry_reason = get_option('uwa_exp_csv_disable_expiry_reason', 'no');
$uwa_exp_csv_disable_expiry_reason_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_expiry_reason) {
    $uwa_exp_csv_disable_expiry_reason_checked_val = 'checked';
}

$uwa_exp_csv_disable_country = get_option('uwa_exp_csv_disable_country', 'no');
$uwa_exp_csv_disable_country_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_country) {
    $uwa_exp_csv_disable_country_checked_val = 'checked';
}

$uwa_exp_csv_disable_second_highest_bidder_mail = get_option('uwa_exp_csv_disable_second_highest_bidder_mail', 'no');
$uwa_exp_csv_disable_sec_highbiddermail_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_second_highest_bidder_mail) {
    $uwa_exp_csv_disable_sec_highbiddermail_checked_val = 'checked';
}

$uwa_exp_csv_disable_product_category = get_option('uwa_exp_csv_disable_product_category', 'no');
$uwa_exp_csv_disable_product_category_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_product_category) {
    $uwa_exp_csv_disable_product_category_checked_val = 'checked';
}

$uwa_exp_csv_disable_phone = get_option('uwa_exp_csv_disable_phone', 'no');
$uwa_exp_csv_disable_phone_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_phone) {
    $uwa_exp_csv_disable_phone_checked_val = 'checked';
}

$uwa_exp_csv_disable_postal_code = get_option('uwa_exp_csv_disable_postal_code', 'no');
$uwa_exp_csv_disable_postal_code_checked_val = '';
if ('yes' == $uwa_exp_csv_disable_postal_code) {
    $uwa_exp_csv_disable_postal_code_checked_val = 'checked';
}


/* Admin expired auctions - category wise listing, display all or three bids, bid related columns */
$uwa_exp_csv_enable_category_listing = get_option('uwa_exp_csv_enable_category_listing', 'no');
$uwa_exp_csv_enable_cat_checked_val = '';
if ('yes' == $uwa_exp_csv_enable_category_listing) {
    $uwa_exp_csv_enable_cat_checked_val = 'checked';
}

$uwa_exp_csv_display_all_bids_checked_val = '';
$uwa_exp_csv_display_three_bids_checked_val = '';
$uwa_exp_csv_display_bids = get_option('uwa_exp_csv_display_bids', 'uwa_dis_three_bids');

if ($uwa_exp_csv_display_bids == 'uwa_dis_three_bids') {
    $uwa_exp_csv_display_three_bids_checked_val = 'checked';
}
else if ($uwa_exp_csv_display_bids == 'uwa_dis_all_bids') { 
    $uwa_exp_csv_display_all_bids_checked_val = 'checked';
}

$uwa_exp_csv_display_all_bids_col_bid = get_option('uwa_exp_csv_display_all_bids_col_bid', 'yes');
$uwa_exp_csv_allbids_colbid_checked_val = 'checked';
if ('yes' == $uwa_exp_csv_display_all_bids_col_bid) {
    $uwa_exp_csv_allbids_colbid_checked_val = 'checked';
} else {
    $uwa_exp_csv_allbids_colbid_checked_val = '';
}

$uwa_exp_csv_display_all_bids_col_bidder = get_option('uwa_exp_csv_display_all_bids_col_bidder', 'yes');
$uwa_exp_csv_allbids_colbidder_checked_val = 'checked';
if ('yes' == $uwa_exp_csv_display_all_bids_col_bidder) {
    $uwa_exp_csv_allbids_colbidder_checked_val = 'checked';
} else {
    $uwa_exp_csv_allbids_colbidder_checked_val = '';
}

$uwa_exp_csv_display_all_bids_col_company = get_option('uwa_exp_csv_display_all_bids_col_company', 'yes');
$uwa_exp_csv_allbids_colcmpny_checked_val = 'checked';
if ('yes' == $uwa_exp_csv_display_all_bids_col_company) {
    $uwa_exp_csv_allbids_colcmpny_checked_val = 'checked';
} else {
    $uwa_exp_csv_allbids_colcmpny_checked_val = '';
}

$uwa_exp_csv_display_all_bids_col_email = get_option('uwa_exp_csv_display_all_bids_col_email', 'yes');
$uwa_exp_csv_allbids_colemail_checked_val = 'checked';
if ('yes' == $uwa_exp_csv_display_all_bids_col_email) {
    $uwa_exp_csv_allbids_colemail_checked_val = 'checked';
} else {
    $uwa_exp_csv_allbids_colemail_checked_val = '';
}


$uwa_exp_csv_enable_pro_attr = get_option('uwa_exp_csv_enable_pro_attr', 'no');
$uwa_exp_csv_enable_pro_attr_checked_val = '';
if ('yes' == $uwa_exp_csv_enable_pro_attr) {
    $uwa_exp_csv_enable_pro_attr_checked_val = 'checked';
}

/* ======================== Frontend users export auctions============================ */


/* Bids active - product attributes */
$uwa_bidsactive_user_csv_enable_pro_attr = get_option('uwa_bidsactive_user_csv_enable_pro_attr', 'no');
$uwa_bidsactive_user_csv_enbl_proattr_checked_val = '';
if ('yes' == $uwa_bidsactive_user_csv_enable_pro_attr) {
    $uwa_bidsactive_user_csv_enbl_proattr_checked_val = 'checked';
}

/* Bids won - product attributes, second highestbid, third highestbid */
$uwa_bidswon_user_csv_enable_pro_attr = get_option('uwa_bidswon_user_csv_enable_pro_attr', 'no');
$uwa_bidswon_user_csv_enbl_proattr_checked_val = '';
if ('yes' == $uwa_bidswon_user_csv_enable_pro_attr) {
    $uwa_bidswon_user_csv_enbl_proattr_checked_val = 'checked';
}

$uwa_bidswon_user_csv_enable_sec_highbid = get_option('uwa_bidswon_user_csv_enable_sec_highbid', 'no');
$uwa_bidswon_user_csv_enbl_sechighbid_checked_val = '';
if ('yes' == $uwa_bidswon_user_csv_enable_sec_highbid) {
    $uwa_bidswon_user_csv_enbl_sechighbid_checked_val = 'checked';
}

$uwa_bidswon_user_csv_enable_third_highbid = get_option('uwa_bidswon_user_csv_enable_third_highbid', 'no');
$uwa_bidswon_user_csv_enbl_thrdhighbid_checked_val = '';
if ('yes' == $uwa_bidswon_user_csv_enable_third_highbid) {
    $uwa_bidswon_user_csv_enbl_thrdhighbid_checked_val = 'checked';
}

/* Bids lost - product attributes, highestbid, second highestbid, third highestbid */
$uwa_bidslost_user_csv_enable_pro_attr = get_option('uwa_bidslost_user_csv_enable_pro_attr', 'no');
$uwa_bidslost_user_csv_enbl_proattr_checked_val = '';
if ('yes' == $uwa_bidslost_user_csv_enable_pro_attr) {
    $uwa_bidslost_user_csv_enbl_proattr_checked_val = 'checked';
}

$uwa_bidslost_user_csv_enable_highbid = get_option('uwa_bidslost_user_csv_enable_highbid', 'no');
$uwa_bidslost_user_csv_enbl_highbid_checked_val = '';
if ('yes' == $uwa_bidslost_user_csv_enable_highbid) {
    $uwa_bidslost_user_csv_enbl_highbid_checked_val = 'checked';
}

$uwa_bidslost_user_csv_enable_sec_highbid = get_option('uwa_bidslost_user_csv_enable_sec_highbid', 'no');
$uwa_bidslost_user_csv_enbl_sechighbid_checked_val = '';
if ('yes' == $uwa_bidslost_user_csv_enable_sec_highbid) {
    $uwa_bidslost_user_csv_enbl_sechighbid_checked_val = 'checked';
}

$uwa_bidslost_user_csv_enable_third_highbid = get_option('uwa_bidslost_user_csv_enable_third_highbid', 'no');
$uwa_bidslost_user_csv_enbl_thrdhighbid_checked_val = '';
if ('yes' == $uwa_bidslost_user_csv_enable_third_highbid) {
    $uwa_bidslost_user_csv_enbl_thrdhighbid_checked_val = 'checked';
}

/* All expired - product attributes, category wise listing, disable different columns */

$uwa_expired_auctions_download_user = get_option('uwa_expired_auctions_download_user', 'no');
$uwa_expired_auctions_download_user_checked_val = '';
if ('yes' == $uwa_expired_auctions_download_user) {
    $uwa_expired_auctions_download_user_checked_val = 'checked';
}

$uwa_exp_user_csv_dataformat_csv = get_option('uwa_exp_user_csv_dataformat_csv', 'yes');
$uwa_exp_user_csv_format_csv_checked_val = '';
if ('yes' == $uwa_exp_user_csv_dataformat_csv) {
    $uwa_exp_user_csv_format_csv_checked_val = 'checked';
}

$uwa_exp_user_csv_dataformat_xls = get_option('uwa_exp_user_csv_dataformat_xls', 'yes');
$uwa_exp_user_csv_format_xls_checked_val = '';
if ('yes' == $uwa_exp_user_csv_dataformat_xls) {
    $uwa_exp_user_csv_format_xls_checked_val = 'checked';
}

$uwa_exp_user_csv_dataformat_pdf = get_option('uwa_exp_user_csv_dataformat_pdf', 'yes');
$uwa_exp_user_csv_format_pdf_checked_val = '';
if ('yes' == $uwa_exp_user_csv_dataformat_pdf) {
    $uwa_exp_user_csv_format_pdf_checked_val = 'checked';
}

$uwa_exp_user_csv_enable_pro_attr = get_option('uwa_exp_user_csv_enable_pro_attr', 'no');
$uwa_exp_user_csv_enbl_proattr_checked_val = '';
if ('yes' == $uwa_exp_user_csv_enable_pro_attr) {
    $uwa_exp_user_csv_enbl_proattr_checked_val = 'checked';
}

$uwa_exp_user_csv_enable_category_listing = get_option('uwa_exp_user_csv_enable_category_listing', 'no');
$uwa_exp_user_csv_enbl_cat_checked_val = '';
if ('yes' == $uwa_exp_user_csv_enable_category_listing) {
    $uwa_exp_user_csv_enbl_cat_checked_val = 'checked';
}


$uwa_exp_user_csv_disable_first_name = get_option('uwa_exp_user_csv_disable_first_name', 'no');
$uwa_exp_user_csv_disable_first_name_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_first_name) {
    $uwa_exp_user_csv_disable_first_name_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_company = get_option('uwa_exp_user_csv_disable_company', 'no');
$uwa_exp_user_csv_disable_company_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_company) {
    $uwa_exp_user_csv_disable_company_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_second_highest_bidder_company = get_option('uwa_exp_user_csv_disable_second_highest_bidder_company', 'no');
$uwa_exp_user_csv_disable_sec_highbidder_cmpny_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_second_highest_bidder_company) {
    $uwa_exp_user_csv_disable_sec_highbidder_cmpny_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_third_highest_bidder_company = get_option('uwa_exp_user_csv_disable_third_highest_bidder_company', 'no');
$uwa_exp_user_csv_disable_thrd_highbidder_cmpny_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_third_highest_bidder_company) {
    $uwa_exp_user_csv_disable_thrd_highbidder_cmpny_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_highest_bidder = get_option('uwa_exp_user_csv_disable_highest_bidder', 'no');
$uwa_exp_user_csv_disable_highest_bidder_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_highest_bidder) {
    $uwa_exp_user_csv_disable_highest_bidder_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_third_highest_bidder = get_option('uwa_exp_user_csv_disable_third_highest_bidder', 'no');
$uwa_exp_user_csv_disable_third_highest_bidder_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_third_highest_bidder) {
    $uwa_exp_user_csv_disable_third_highest_bidder_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_last_name = get_option('uwa_exp_user_csv_disable_last_name', 'no');
$uwa_exp_user_csv_disable_last_name_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_last_name) {
    $uwa_exp_user_csv_disable_last_name_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_highest_bid = get_option('uwa_exp_user_csv_disable_highest_bid', 'no');
$uwa_exp_user_csv_disable_highest_bid_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_highest_bid) {
    $uwa_exp_user_csv_disable_highest_bid_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_third_highest_bid = get_option('uwa_exp_user_csv_disable_third_highest_bid', 'no');
$uwa_exp_user_csv_disable_third_highest_bid_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_third_highest_bid) {
    $uwa_exp_user_csv_disable_third_highest_bid_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_address1 = get_option('uwa_exp_user_csv_disable_address1', 'no');
$uwa_exp_user_csv_disable_address1_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_address1) {
    $uwa_exp_user_csv_disable_address1_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_highest_bidder_mail = get_option('uwa_exp_user_csv_disable_highest_bidder_mail', 'no');
$uwa_exp_user_csv_disable_highest_bidder_mail_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_highest_bidder_mail) {
    $uwa_exp_user_csv_disable_highest_bidder_mail_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_third_highest_bidder_mail = get_option('uwa_exp_user_csv_disable_third_highest_bidder_mail', 
    'no');
$uwa_exp_user_csv_disable_thrd_highbiddermail_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_third_highest_bidder_mail) {
    $uwa_exp_user_csv_disable_thrd_highbiddermail_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_address2 = get_option('uwa_exp_user_csv_disable_address2', 'no');
$uwa_exp_user_csv_disable_address2_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_address2) {
    $uwa_exp_user_csv_disable_address2_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_second_highest_bidder = get_option('uwa_exp_user_csv_disable_second_highest_bidder', 
    'no');
$uwa_exp_user_csv_disable_second_highest_bidder_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_second_highest_bidder) {
    $uwa_exp_user_csv_disable_second_highest_bidder_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_end_date = get_option('uwa_exp_user_csv_disable_end_date', 'no');
$uwa_exp_user_csv_disable_end_date_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_end_date) {
    $uwa_exp_user_csv_disable_end_date_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_city = get_option('uwa_exp_user_csv_disable_city', 'no');
$uwa_exp_user_csv_disable_city_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_city) {
    $uwa_exp_user_csv_disable_city_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_second_highest_bid = get_option('uwa_exp_user_csv_disable_second_highest_bid', 'no');
$uwa_exp_user_csv_disable_second_highest_bid_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_second_highest_bid) {
    $uwa_exp_user_csv_disable_second_highest_bid_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_expiry_reason = get_option('uwa_exp_user_csv_disable_expiry_reason', 'no');
$uwa_exp_user_csv_disable_expiry_reason_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_expiry_reason) {
    $uwa_exp_user_csv_disable_expiry_reason_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_country = get_option('uwa_exp_user_csv_disable_country', 'no');
$uwa_exp_user_csv_disable_country_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_country) {
    $uwa_exp_user_csv_disable_country_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_second_highest_bidder_mail = get_option('uwa_exp_user_csv_disable_second_highest_bidder_mail', 'no');
$uwa_exp_user_csv_disable_sec_highbiddermail_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_second_highest_bidder_mail) {
    $uwa_exp_user_csv_disable_sec_highbiddermail_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_product_category = get_option('uwa_exp_user_csv_disable_product_category', 'no');
$uwa_exp_user_csv_disable_product_category_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_product_category) {
    $uwa_exp_user_csv_disable_product_category_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_phone = get_option('uwa_exp_user_csv_disable_phone', 'no');
$uwa_exp_user_csv_disable_phone_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_phone) {
    $uwa_exp_user_csv_disable_phone_checked_val = 'checked';
}

$uwa_exp_user_csv_disable_postal_code = get_option('uwa_exp_user_csv_disable_postal_code', 'no');
$uwa_exp_user_csv_disable_postal_code_checked_val = '';
if ('yes' == $uwa_exp_user_csv_disable_postal_code) {
    $uwa_exp_user_csv_disable_postal_code_checked_val = 'checked';
}

/* All Slient live - Export Slient live Auction XLSX */
$uwa_slient_live_auctions_export_user = get_option('uwa_slient_live_auctions_export_user', 'no');
$uwa_slient_live_auctions_export_user_checked_val = '';
if ('yes' == $uwa_slient_live_auctions_export_user) {
    $uwa_slient_live_auctions_export_user_checked_val = 'checked';
}

$uwa_importbids_user_xlsx_enable_pro_attr = get_option('uwa_importbids_user_xlsx_enable_pro_attr', 'no');
$uwa_importbids_user_xlsx_enable_pro_attr_checked_val = '';
if ('yes' == $uwa_importbids_user_xlsx_enable_pro_attr) {
    $uwa_importbids_user_xlsx_enable_pro_attr_checked_val = 'checked';
}

$uwa_bid_offline_disable_category_filter = get_option('uwa_bid_offline_disable_category_filter', 'no');
$uwa_bid_offline_disable_category_filter_checked_val = '';
if ('yes' == $uwa_bid_offline_disable_category_filter) {
    $uwa_bid_offline_disable_category_filter_checked_val = 'checked';
}

$uwa_bid_offline_opening_price = get_option('uwa_bid_offline_opening_price', 'no');
$uwa_bid_offline_opening_price_checked_val = '';
if ('yes' == $uwa_bid_offline_opening_price) {
    $uwa_bid_offline_opening_price_checked_val = 'checked';
}

$uwa_bid_offline_sort_pro_title_asc = get_option('uwa_bid_offline_sort_pro_title_asc', 'no');
$uwa_bid_offline_sort_pro_title_asc_checked_val = '';
if ('yes' == $uwa_bid_offline_sort_pro_title_asc) {
    $uwa_bid_offline_sort_pro_title_asc_checked_val = 'checked';
}

$uwa_csv_delimiter = get_option('uwa_csv_delimiter', ',');
$uwa_csv_delimiter_semicolon_checked = ($uwa_csv_delimiter === ';') ? 'checked' : '';
$uwa_csv_delimiter_comma_checked = ($uwa_csv_delimiter === ',') ? 'checked' : '';

?>

<div class='uwa_main_setting_content'>
    <form method='post' class='uwa_auction_setting_style'>

        <?php $uwa_nonce_export_settings_tab = wp_create_nonce('uwa_nonce_form_export_settings_tab'); ?>

        <input type='hidden' name='uwa_nonce_export_settings_tab' 
            value='<?php echo esc_attr($uwa_nonce_export_settings_tab); ?>'>

        <table class='form-table'>
            <tbody>
                <tr>
                    <h4>
                        <?php esc_html_e('Admin can export expired auctions, while frontend users can export their active, lost, and won auctions, as well as all expired auctions on the site.', 'woo_ua'); ?>
                    </h4>
                    <br>
                </tr>
                <tr class='uwa_heading'>
                    <th colspan='2'><?php esc_html_e('Export Expired Auctions (CSV/XLS/PDF) for admin', 'woo_ua'); ?>
                    </th>
                </tr> 
                <tr>
                    <th colspan='5'>
                    <?php esc_html_e('Admin can export the all expired auctions at Admin Dashboard > Auctions > Expired Auctions. The export includes the following fields by default in CSV, XLS, and PDF formats.', 'woo_ua'); ?> 
                    <br>
                    <?php esc_html_e('Use the settings below to disable any of these fields.', 'woo_ua'); ?> 
                    </th>
                </tr>

                <tr>
                    <th scope='row'><?php esc_html_e('Disable fields for export expired auctions :', 'woo_ua'); ?></th>
                    <td class=''>

                        <table class='tbl_uwa_csv'>
                            <tr class='col_first tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_first_name_checked_val); ?> 
                                        name='uwa_exp_csv_disable_first_name' type='checkbox' value='1'>
                                        <?php esc_html_e('First Name', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_highest_bidder_checked_val); ?> 
                                        name='uwa_exp_csv_disable_highest_bidder' type='checkbox' value='1'> 
                                        <?php esc_html_e('Highest Bidder', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input 
                                        <?php echo esc_attr($uwa_exp_csv_disable_third_highest_bidder_checked_val); ?> 
                                        name='uwa_exp_csv_disable_third_highest_bidder' type='checkbox' value='1'>
                                        <?php esc_html_e('Third Highest Bidder', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_second tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_last_name_checked_val); ?> 
                                        name='uwa_exp_csv_disable_last_name' type='checkbox' value='1'>
                                        <?php esc_html_e('Last Name', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_highest_bid_checked_val); ?> 
                                        name='uwa_exp_csv_disable_highest_bid' type='checkbox' value='1'>
                                        <?php esc_html_e('Highest Bid', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_third_highest_bid_checked_val); ?> 
                                        name='uwa_exp_csv_disable_third_highest_bid' type='checkbox' value='1'>
                                        <?php esc_html_e('Third Highest Bid', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_third tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_postal_code_checked_val); ?> 
                                        name='uwa_exp_csv_disable_postal_code' type='checkbox' value='1'>
                                        <?php esc_html_e('Postal Code', 'woo_ua'); ?> 
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_company_checked_val); ?> 
                                        name='uwa_exp_csv_disable_company' type='checkbox' value='1'>
                                        <?php esc_html_e('Highest Bidder Company', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_thrd_highbidder_company_checked_val); ?>
                                        name='uwa_exp_csv_disable_third_highest_bidder_company' type='checkbox' 
                                        value='1'><?php esc_html_e('Third Highest Bidder Company', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_fourth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_address1_checked_val); ?> 
                                        name='uwa_exp_csv_disable_address1' type='checkbox' value='1'>
                                        <?php esc_html_e('Address1', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_highest_bidder_mail_checked_val); ?>
                                        name='uwa_exp_csv_disable_highest_bidder_mail' type='checkbox' value='1'>
                                        <?php esc_html_e('Highest Bidder Email', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_third_highest_bidder_mail_checked_val); ?> 
                                        name='uwa_exp_csv_disable_third_highest_bidder_mail' type='checkbox' 
                                        value='1'><?php esc_html_e('Third Highest Bidder Email', 'woo_ua'); ?>        
                                </td>
                            </tr>
                            <tr class='col_fifth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_address2_checked_val); ?> 
                                        name='uwa_exp_csv_disable_address2' type='checkbox' value='1'>
                                        <?php esc_html_e('Address2', 'woo_ua'); ?>                                      
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_second_highest_bidder_checked_val); ?> 
                                        name='uwa_exp_csv_disable_second_highest_bidder' type='checkbox' value='1'>
                                        <?php esc_html_e('Second Highest Bidder', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_end_date_checked_val); ?> 
                                        name='uwa_exp_csv_disable_end_date' type='checkbox' value='1'>
                                        <?php esc_html_e('Auction End Date', 'woo_ua'); ?>
                                </td>
                            </tr>
                            <tr class='col_sixth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_city_checked_val); ?> 
                                        name='uwa_exp_csv_disable_city' type='checkbox' value='1'>
                                        <?php esc_html_e('City', 'woo_ua'); ?>                                      
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_second_highest_bid_checked_val); ?> 
                                        name='uwa_exp_csv_disable_second_highest_bid' type='checkbox' value='1'>
                                        <?php esc_html_e('Second Highest Bid', 'woo_ua'); ?>    
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_expiry_reason_checked_val); ?> 
                                        name='uwa_exp_csv_disable_expiry_reason' type='checkbox' value='1'>
                                        <?php esc_html_e('Expiry Reason', 'woo_ua'); ?>
                                </td>
                            </tr>
                            <tr class='col_seventh tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_country_checked_val); ?> 
                                        name='uwa_exp_csv_disable_country' type='checkbox' value='1'>
                                        <?php esc_html_e('Country', 'woo_ua'); ?>                                           
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_sec_highbiddermail_checked_val); ?> 
                                        name='uwa_exp_csv_disable_second_highest_bidder_mail' type='checkbox' 
                                        value='1'><?php esc_html_e('Second Highest Bidder Email', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_product_category_checked_val); ?> 
                                        name='uwa_exp_csv_disable_product_category' type='checkbox' value='1'>
                                        <?php esc_html_e('Product Category', 'woo_ua'); ?>
                                </td>
                            </tr>
                            <tr class='col_eighth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_csv_disable_phone_checked_val); ?> 
                                        name='uwa_exp_csv_disable_phone' type='checkbox' value='1'>
                                        <?php esc_html_e('Phone', 'woo_ua'); ?>                                         
                                </td>
                                <td class='rw2'>
                                    <input 
                                        <?php echo esc_attr($uwa_exp_csv_disable_sec_highbidder_company_checked_val); ?>
                                        name='uwa_exp_csv_disable_second_highest_bidder_company' type='checkbox' 
                                        value='1'><?php esc_html_e('Second Highest Bidder Company', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                </td>
                            </tr>

                        </table>                            

                    </td>
                </tr>

               <tr>
                    <th scope='row'>
                        <label for='uwa_display_wining_losing_text'>
                        <?php esc_html_e('Category option', 'woo_ua'); ?></label>
                    </th>
                    <td>
                        <div>
                            <input <?php echo esc_attr($uwa_exp_csv_enable_cat_checked_val); ?> 
                                name='uwa_exp_csv_enable_category_listing' type='checkbox' value='1'> 
                                <?php esc_html_e('Enable the category option to export the auctions by category', 'woo_ua'); ?>                                
                        </div>                           
                    </td> 
                </tr>                       

                <tr>
                    <th scope='row'>
                        <label for='uwa_display_wining_losing_text'>
                        <?php esc_html_e('How many bids to display in export expired auctions :', 'woo_ua'); ?></label>
                    </th>
                    <td>
                        <a href='' class='uwa_fields_tooltip' onclick="return false"><strong>?</strong>
                        <span><?php esc_html_e('test..', 'woo_ua'); ?> </span></a>
                        
                        <input type='radio' name='uwa_exp_csv_display_bids' id='uwa_exp_csv_display_three_bids' 
                            value='uwa_dis_three_bids'  <?php echo esc_attr($uwa_exp_csv_display_three_bids_checked_val); ?>> 

                        <span class='description'><?php esc_html_e('Hightest 3 bids details', 'woo_ua'); ?></span>
                        <span style='margin-right:20px;'></span> 

                        <input type='radio' name='uwa_exp_csv_display_bids' id='uwa_exp_csv_display_all_bids' 
                        value='uwa_dis_all_bids'  <?php echo esc_attr($uwa_exp_csv_display_all_bids_checked_val); ?>> 

                        <span class='description'><?php esc_html_e('All bids details', 'woo_ua'); ?></span>
                        <br /><br />
                    </td> 
                </tr> 

                <tr>
                    <th scope='row'>
                        <label for=''>
                        <?php esc_html_e('Enable fields for export expired auctions :', 'woo_ua'); ?></label>
                    </th>
                    <td>
                        <div>
                            <input <?php echo esc_attr($uwa_exp_csv_enable_pro_attr_checked_val); ?> 
                                name='uwa_exp_csv_enable_pro_attr' type='checkbox' value='1'> 
                                <?php esc_html_e('Product attributes', 'woo_ua'); ?>
                        </div> 

                        <br /><br />
                    </td> 
                </tr> 

                <tr class='uwa_tr_bid_columns'>
                    <th scope='row'>
                        <?php esc_html_e('Which bid columns to display in export when all bids details selected?',
                            'woo_ua'); ?>
                    </th>
                    <td class='uwa_export_columns'>
                        <div>                                
                            <input <?php echo esc_attr($uwa_exp_csv_allbids_colbid_checked_val); ?> 
                                name='uwa_exp_csv_display_all_bids_col_bid' type='checkbox' 
                                value='1'><?php esc_html_e('Bid', 'woo_ua'); ?>  

                            <input <?php echo esc_attr($uwa_exp_csv_allbids_colbidder_checked_val); ?> 
                                name='uwa_exp_csv_display_all_bids_col_bidder' type='checkbox' 
                                value='1'><?php esc_html_e('Bidder', 'woo_ua'); ?>
                            
                            <input <?php echo esc_attr($uwa_exp_csv_allbids_colcmpny_checked_val); ?> 
                                name='uwa_exp_csv_display_all_bids_col_company' type='checkbox' 
                                value='1'><?php esc_html_e('Bidder Company', 'woo_ua'); ?>

                            <input <?php echo esc_attr($uwa_exp_csv_allbids_colemail_checked_val); ?> 
                                name='uwa_exp_csv_display_all_bids_col_email' type='checkbox' 
                                value='1'><?php esc_html_e('Bidder Email', 'woo_ua'); ?>
                        </div> 
                    </td>
                </tr>                    

                <!-- ==================================================================== -->

                <tr class='uwa_heading'>
                    <th colspan='2'>
                        <?php
                        esc_html_e('Export Auctions (CSV/XLS/PDF) for frontend users',
                            'woo_ua');
                        ?>
                    </th>
                </tr>

                <tr>
                    <th colspan='5'>
                        <?php esc_html_e('Frontend users can also export his auctions at My-account > Auctions. They can export his active, lost and won auctions as well as all the expired auctions of site in CSV, XLS, and PDF formats.', 'woo_ua'); ?> 
                        <br>
                        <?php esc_html_e('Use the settings below to configure it.', 'woo_ua'); ?> 
                    </th>
                </tr>

                <tr>
                    <th scope='row'><?php esc_html_e('Auctions - Bids Active', 'woo_ua'); ?></th>
                    <td class='uwa_export_columns'>
                        <div>
                            <span><?php esc_html_e('Enable the below fields to display in export', 'woo_ua'); ?>
                            </span><br><br>
                            <input <?php echo esc_attr($uwa_bidsactive_user_csv_enbl_proattr_checked_val); ?> 
                                name='uwa_bidsactive_user_csv_enable_pro_attr' type='checkbox' 
                                value='1'><?php esc_html_e('Product attributes', 'woo_ua'); ?>
                        </div> 
                        <br><hr style='border:1px solid lightgrey; margin-left:0px; width:40%'>
                    </td>
                </tr>

                <tr>
                    <th scope='row'><?php esc_html_e('Auctions - Bids Won', 'woo_ua'); ?></th>
                    <td class='uwa_export_columns'>
                        <div>
                            <span>
                                <?php esc_html_e('Enable the below fields to display in export', 'woo_ua'); ?>
                            </span><br><br>

                            <input <?php echo esc_attr($uwa_bidswon_user_csv_enbl_proattr_checked_val); ?> 
                                name='uwa_bidswon_user_csv_enable_pro_attr' type='checkbox' 
                                value='1'><?php esc_html_e('Product attributes', 'woo_ua'); ?>

                            <input <?php echo esc_attr($uwa_bidswon_user_csv_enbl_sechighbid_checked_val); ?> 
                                name='uwa_bidswon_user_csv_enable_sec_highbid' type='checkbox' 
                                value='1'><?php esc_html_e('Second highest bid', 'woo_ua'); ?>

                            <input <?php echo esc_attr($uwa_bidswon_user_csv_enbl_thrdhighbid_checked_val); ?> 
                                name='uwa_bidswon_user_csv_enable_third_highbid' type='checkbox' 
                                value='1'><?php esc_html_e('Third highest bid', 'woo_ua'); ?>
                        </div>                            
                        <br><hr style='border:1px solid lightgrey; margin-left:0px; width:40%'>
                    </td>
                </tr>

                <tr>
                    <th scope='row'><?php esc_html_e('Auctions - Bids Lost', 'woo_ua'); ?></th>
                    <td class='uwa_export_columns'>
                        <div>
                            <span><?php esc_html_e('Enable the below fields to display in export', 'woo_ua'); ?>
                            </span><br><br>

                            <input <?php echo esc_attr($uwa_bidslost_user_csv_enbl_proattr_checked_val); ?> 
                                name='uwa_bidslost_user_csv_enable_pro_attr' type='checkbox' 
                                value='1'><?php esc_html_e('Product attributes', 'woo_ua'); ?>

                            <input <?php echo esc_attr($uwa_bidslost_user_csv_enbl_highbid_checked_val); ?>
                                name='uwa_bidslost_user_csv_enable_highbid' type='checkbox' 
                                value='1'><?php esc_html_e('Highest bid', 'woo_ua'); ?>

                            <input <?php echo esc_attr($uwa_bidslost_user_csv_enbl_sechighbid_checked_val); ?> 
                                name='uwa_bidslost_user_csv_enable_sec_highbid' type='checkbox' 
                                value='1'><?php esc_html_e('Second highest bid', 'woo_ua'); ?>

                            <input <?php echo esc_attr($uwa_bidslost_user_csv_enbl_thrdhighbid_checked_val); ?> 
                                name='uwa_bidslost_user_csv_enable_third_highbid' type='checkbox' 
                                value='1'><?php esc_html_e('Third highest bid', 'woo_ua'); ?>
                        </div>
                        <br><hr style='border:1px solid lightgrey; margin-left:0px; width:40%'>
                    </td>
                </tr>

                <tr>
                    <th scope='row'><?php esc_html_e('Auctions - Import your bids in silent auction', 'woo_ua'); ?></th>
                    <td>
                        <div class="bid-offline-description">
                            <span class="description">
                                <?php esc_html_e('Enable this feature to allow users to place their bids in silent live auctions by uploading an XLSX file with their bid details. Once enabled, users can access this feature in their My Account page under Auctions > Import your bids in silent auction. From there, they can download the Silent Live Auctions list in XLSX format, enter their bids, and re-upload the file to submit their bids. Ensure the uploaded file follows the correct format to avoid errors.', 'woo_ua'); ?>
                            </span>
                        </div><br><br>

                        <input <?php echo esc_attr($uwa_slient_live_auctions_export_user_checked_val); ?> 
                            name='uwa_slient_live_auctions_export_user' type='checkbox'  value='1'>
                            <?php esc_html_e('Enable Import your bids in silent auction feature for Users.', 'woo_ua'); ?><br>
                        <hr class="enable_bid_offline_hr" style='border:1px solid lightgrey; margin-left:0px; width:40%'>
                    </td>
                </tr>

                <tr class='uwa_bid_offline_auctions_user_columns'>
                    <th scope='row'></th>
                    <td class='bid_offline_columns'>
                
                        <span><?php esc_html_e('Enable the below fields to display in export', 'woo_ua'); ?></span><br><br>
                        <input <?php echo esc_attr($uwa_importbids_user_xlsx_enable_pro_attr_checked_val); ?>
                            name='uwa_importbids_user_xlsx_enable_pro_attr' type='checkbox'
                            value='1'><?php esc_html_e('Product attributes', 'woo_ua'); ?><br><br>

                        <hr style='border:1px solid lightgrey; margin-left:0px; width:40%'>
                    </td>
                </tr>

                <tr class='uwa_bid_offline_auctions_user_columns'>
                    <th scope='row'></th>
                    <td class='bid_offline_columns'>
                
                        <span><?php esc_html_e('Disable the below fields to hide in Export your bids in silent auction', 'woo_ua'); ?></span><br><br>
                        <input <?php echo esc_attr($uwa_bid_offline_opening_price_checked_val); ?>
                            name='uwa_bid_offline_opening_price' type='checkbox'
                            value='1'><?php esc_html_e('Opening Price', 'woo_ua'); ?><br><br>

                        <input <?php echo esc_attr($uwa_bid_offline_disable_category_filter_checked_val); ?>
                            name='uwa_bid_offline_disable_category_filter' type='checkbox' 
                            value='1'><?php esc_html_e('Enable the category option to export the auctions by category', 'woo_ua'); ?><br><br>
                        <hr style='border:1px solid lightgrey; margin-left:0px; width:40%'>
                    </td>
                </tr>  

                <tr>
                    <th scope='row'><?php esc_html_e('Auctions -  All Expired Auctions', 'woo_ua'); ?></th>
                    <td>
                        <input <?php echo esc_attr($uwa_expired_auctions_download_user_checked_val); ?> 
                            name='uwa_expired_auctions_download_user' type='checkbox'  value='1'>
                            <?php esc_html_e('Enable All Expired Auctions Download feature for Users.', 'woo_ua'); ?>
                    </td>
                </tr>

                <tr class='uwa_expired_auctions_user_columns'>
                    <th scope='row'></th>
                    <td class='uwa_export_columns'>

                        <span><?php esc_html_e('Which format do you want to use for export data?', 'woo_ua'); ?>
                        </span><br><br>

                        <input <?php echo esc_attr($uwa_exp_user_csv_format_csv_checked_val); ?>
                            name='uwa_exp_user_csv_dataformat_csv' type='checkbox' 
                            value='1'><?php esc_html_e('CSV', 'woo_ua'); ?>
                        <input <?php echo esc_attr($uwa_exp_user_csv_format_xls_checked_val); ?>
                            name='uwa_exp_user_csv_dataformat_xls' type='checkbox' 
                            value='1'><?php esc_html_e('XLSX', 'woo_ua'); ?>                            
                        <input <?php echo esc_attr($uwa_exp_user_csv_format_pdf_checked_val); ?>
                            name='uwa_exp_user_csv_dataformat_pdf' type='checkbox' 
                            value='1'><?php esc_html_e('PDF', 'woo_ua'); ?>
                    </td>
                </tr>                

                <tr class='uwa_expired_auctions_user_columns'>
                    <th scope='row'></th>
                    <td class='uwa_export_columns'>

                        <span><?php esc_html_e('Enable the below fields to display in export', 'woo_ua'); ?>
                        </span><br><br>

                        <input <?php echo esc_attr($uwa_exp_user_csv_enbl_proattr_checked_val); ?>
                            name='uwa_exp_user_csv_enable_pro_attr' type='checkbox' 
                            value='1'><?php esc_html_e('Product attributes', 'woo_ua'); ?>
                    </td>
                </tr>

                <tr class='uwa_expired_auctions_user_columns'>

                    <th scope='row'></th>
                    <td class=''>                        
                        <span><?php esc_html_e('Disable the below fields to hide in export', 'woo_ua'); ?>
                        </span><br><br>                           

                        <table class='tbl_uwa_csv'>
                            <tr class='col_first tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_first_name_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_first_name' type='checkbox' value='1'>
                                        <?php esc_html_e('First Name', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input 
                                        <?php echo esc_attr($uwa_exp_user_csv_disable_highest_bidder_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_highest_bidder' type='checkbox' value='1'> 
                                        <?php esc_html_e('Highest Bidder', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_third_highest_bidder_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_third_highest_bidder' type='checkbox' value='1'>
                                        <?php esc_html_e('Third Highest Bidder', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_second tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_last_name_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_last_name' type='checkbox' value='1'>
                                        <?php esc_html_e('Last Name', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_highest_bid_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_highest_bid' type='checkbox' value='1'>
                                        <?php esc_html_e('Highest Bid', 'woo_ua'); ?>                                     
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_third_highest_bid_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_third_highest_bid' type='checkbox' value='1'>
                                        <?php esc_html_e('Third Highest Bid', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_third tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_postal_code_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_postal_code' type='checkbox' value='1'>
                                        <?php esc_html_e('Postal Code', 'woo_ua'); ?> 
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_company_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_company' type='checkbox' value='1'>
                                        <?php esc_html_e('Highest Bidder Company', 'woo_ua'); ?>                                 
                                </td>
                                <td class='rw3'>
                                    <input 
                                    <?php echo esc_attr($uwa_exp_user_csv_disable_thrd_highbidder_cmpny_checked_val); ?>
                                        name='uwa_exp_user_csv_disable_third_highest_bidder_company' type='checkbox' value='1'><?php esc_html_e('Third Highest Bidder Company', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_fourth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_address1_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_address1' type='checkbox' value='1'>
                                        <?php esc_html_e('Address1', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_highest_bidder_mail_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_highest_bidder_mail' type='checkbox' 
                                        value='1'><?php esc_html_e('Highest Bidder Email', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_thrd_highbiddermail_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_third_highest_bidder_mail' type='checkbox' 
                                        value='1'><?php esc_html_e('Third Highest Bidder Email', 'woo_ua'); ?>      
                                </td>
                            </tr>

                            <tr class='col_fifth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_address2_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_address2' type='checkbox' value='1'>
                                        <?php esc_html_e('Address2', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_second_highest_bidder_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_second_highest_bidder' type='checkbox' 
                                        value='1'><?php esc_html_e('Second Highest Bidder', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_end_date_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_end_date' type='checkbox' 
                                        value='1'><?php esc_html_e('Auction End Date', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_sixth tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_city_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_city' type='checkbox' 
                                        value='1'><?php esc_html_e('City', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_second_highest_bid_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_second_highest_bid' type='checkbox' 
                                        value='1'><?php esc_html_e('Second Highest Bid', 'woo_ua'); ?>  
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_expiry_reason_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_expiry_reason' type='checkbox' 
                                        value='1'><?php esc_html_e('Expiry Reason', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_seventh tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_country_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_country' type='checkbox' 
                                        value='1'><?php esc_html_e('Country', 'woo_ua'); ?>
                                </td>
                                <td class='rw2'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_sec_highbiddermail_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_second_highest_bidder_mail' type='checkbox' 
                                        value='1'><?php esc_html_e('Second Highest Bidder Email', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_product_category_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_product_category' type='checkbox' 
                                        value='1'><?php esc_html_e('Product Category', 'woo_ua'); ?>
                                </td>
                            </tr>

                            <tr class='col_eight tbl_uwa_col'>
                                <td class='rw1'>
                                    <input <?php echo esc_attr($uwa_exp_user_csv_disable_phone_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_phone' type='checkbox' 
                                        value='1'><?php esc_html_e('Phone', 'woo_ua'); ?>    
                                </td>
                                <td class='rw2'>
                                    <input 
                                    <?php echo esc_attr($uwa_exp_user_csv_disable_sec_highbidder_cmpny_checked_val); ?> 
                                        name='uwa_exp_user_csv_disable_second_highest_bidder_company' type='checkbox' value='1'><?php esc_html_e('Second Highest Bidder Company', 'woo_ua'); ?>
                                </td>
                                <td class='rw3'>
                                </td>
                            </tr>

                        </table>                            

                    </td>
                </tr>

                <tr class='uwa_expired_auctions_user_columns'>
                    <th scope='row'></th>
                    <td class='uwa_export_columns'>
                        <div>
                            <input <?php echo esc_attr($uwa_exp_user_csv_enbl_cat_checked_val); ?> 
                                name='uwa_exp_user_csv_enable_category_listing' type='checkbox' value='1'> 
                                <?php esc_html_e('Enable the category option to export the auctions by category', 'woo_ua'); ?>                         
                        </div>
                    </td>
                </tr>

                <tr class='uwa_heading uwa_heading_delimiter'>
                    <th colspan='2'>
                        <?php
                        esc_html_e('Export Auctions delimiter for CSV exports',
                            'woo_ua');
                        ?>
                    </th>
                </tr>

                <tr>
                    <th scope='row'>
                        <label for='uwa_csv_delimiter'>
                            <?php esc_html_e('Delimiter for CSV export:', 'woo_ua'); ?>
                        </label>
                    </th>
                    <td>   
                        <input type='radio' name='uwa_csv_delimiter' id='uwa_csv_delimiter_comma' 
                            value=',' <?php checked(get_option('uwa_csv_delimiter', ','), ','); ?>>
                        <span class='description'><?php esc_html_e('Use Comma ( , ) as delimiter', 'woo_ua'); ?></span>
                        <br /><br />
                        <input type='radio' name='uwa_csv_delimiter' id='uwa_csv_delimiter_semicolon'
                            value=';' <?php checked(get_option('uwa_csv_delimiter', ','), ';'); ?>>
                        <span class='description'><?php esc_html_e('Use Semicolon ( ; ) as delimiter', 'woo_ua'); ?></span>
                    </td>
                </tr>

                <tr class='submit'>
                    <th colspan='2'>
                        <input type="submit" id="uwa-settings-submit" name="uwa-settings-submit" 
                            class="button-primary" value="<?php esc_html_e('Save Changes', 'woo_ua'); ?>" />
                    </th>
                </tr>

            </tbody>                        
        </table>
    </form>
</div>

<style>    
    .uwa_main_setting .uwa_main_setting_content table tr td.uwa_export_columns input:not(:first-child) {
        margin-left: 30px;
    }

    .uwa_layout_left_spacing {
        padding-left: 75px!important;
    }

    .uwa_expired_auctions_user_columns {
        display: none;
    }

    .enable_bid_offline_hr{
        margin-top: 30px;
    }
    .uwa_main_setting .uwa_main_setting_content table input[type="checkbox"][name="uwa_bid_offline_disable_category_filter"] {
        margin-left: 0px !important;
    }

    .uwa_main_setting .uwa_main_setting_content table tr td.bid_offline_columns input:not(:first-child){
        margin-left: 30px;
    }
    .uwa_bid_offline_auctions_user_columns {
        display: none;
    }
</style>

<script type='text/javascript'>
    
    jQuery("document").ready(function($) {


        //Import your bids in silent auction
        var uwa_slient_live_auctions_export_user_button = $("input:checkbox[name='uwa_slient_live_auctions_export_user']:checked").val();

        if (uwa_slient_live_auctions_export_user_button == 1) {
               
            $('.uwa_bid_offline_auctions_user_columns').css("display", "table-row");
            $('.enable_bid_offline_hr').css("display", "none");
        } else {
           
            $('.uwa_bid_offline_auctions_user_columns').css("display", "none");

        }

        $('input:checkbox[name=uwa_slient_live_auctions_export_user]').change(function() {
            if ($(this).prop('checked')) {
               
                $('.uwa_bid_offline_auctions_user_columns').css("display", "table-row");
                $('.enable_bid_offline_hr').css("display", "none");
                

            } else {
               
                $('.uwa_bid_offline_auctions_user_columns').css("display", "none");
                $('.enable_bid_offline_hr').css("display", "block");

            }
        });
        

        var uwa_expired_auctions_download_user_button = $("input:checkbox[name='uwa_expired_auctions_download_user']:checked").val();

        if (uwa_expired_auctions_download_user_button == 1) {
               
            $('.uwa_expired_auctions_user_columns').css("display", "table-row");

        } else {
           
            $('.uwa_expired_auctions_user_columns').css("display", "none");

        }


        $('input:checkbox[name=uwa_expired_auctions_download_user]').change(function() {
            if ($(this).prop('checked')) {
               
                $('.uwa_expired_auctions_user_columns').css("display", "table-row");

            } else {
               
                $('.uwa_expired_auctions_user_columns').css("display", "none");

            }
        });


        var uwa_show_bid_columns_settings = $("input:radio[name='uwa_exp_csv_display_bids']:checked").val();

        if (uwa_show_bid_columns_settings == 'uwa_dis_all_bids') {
            $('.uwa_tr_bid_columns').css('display', 'table-row');                
        } else {
            $('.uwa_tr_bid_columns').css('display', 'none');
        }


        $('input:radio[name=uwa_exp_csv_display_bids]').change(function() {

            if (this.value == 'uwa_dis_all_bids') {
                $('.uwa_tr_bid_columns').css('display', 'table-row');
            } else {
                $('.uwa_tr_bid_columns').css('display', 'none');
            }

        });

    });

</script>