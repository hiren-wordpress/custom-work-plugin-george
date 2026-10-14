<?php

/**
 * Ultimate WooCommerce Auction Pro Admin Reports page
 *
 * @package Ultimate WooCommerce Auction Pro
 * @since 1.1.1 
 *
 */ 

?>
    <div class="uwa_main_setting wrap"> 

        <h1 class="uwa_admin_page_title">
            <?php _e( 'Reports', "woo_ua" ); ?>
        </h1>
        
        <!-- <h2 class="uwa_main_h2"><?php _e( 'Ultimate WooCommerce Auction PRO', "woo_ua"); ?>
            <span class="uwa_version_text"><?php _e( 'Version :', "woo_ua" ); ?>  <?php echo UW_AUCTION_PRO_VERSION; ?></span>
        </h2> --> 
                
        <?php uwa_get_plugin_info_version(); ?>
        
        <div class="uwa_help_section_main"> 
            <div class="uat_theme_page_nav">

                <h2 class="nav-tab-wrapper">

                    <?php
                    
                    /*$uat_default_tab = array(array('uat-report-type' => 'products','range' => 'year','slug' => 'uat-report-report', 'label' => __('Report', 'ultimate-auction-theme')), array('uat-report-type' => 'log','range' => '','slug' => 'uat-report-logs', 'label' => __('Logs', 'ultimate-auction-theme')));*/

                    /* display link if slient bid edit delete settings enabled */       

                    $is_enable_slientbid_edit = get_option('uwa_bidsactive_enable_slientbid_edit', 'no');   
                    $is_enable_slientbid_delete = get_option('uwa_bidsactive_enable_slientbid_delete', 'no');


                        $uat_default_tab[] = array(
                            'uwa_auction_reports' => 'log',
                            'range' => '',
                            'type' => 'payment_hold',
                            'slug' => 'uwa_auction_reports', 
                            'label' => __('Payment Hold & Debit', "woo_ua"),
                        );

                    if ('yes' == $is_enable_slientbid_edit) {

                        $uat_default_tab[] = array(    
                            'uwa_auction_edited_bids' => 'log',
                            'range' => '',
                            'slug' => 'uwa_auction_edited_bids', 
                            'type' => 'edited_bids',
                            'label' => __('Edited Auction Bids', "woo_ua"),
                        );
                    }
                    
                    if ('yes' == $is_enable_slientbid_delete) {

                        $uat_default_tab[] = array(    
                            'uwa_auction_deleted_bids' => 'log',
                            'range' => '',
                            'slug' => 'uwa_auction_deleted_bids', 
                            'type' => 'deleted_bids',
                            'label' => __('Deleted Auction Bids', "woo_ua"),
                        );
                    }


                    /*$uat_default_tab = array( 
                        array(    
                            'uwa_auction_edited_bids' => 'log',
                            'range' => '',
                            'slug' => 'uwa_auction_edited_bids', 
                            'type' => 'edited_bids',
                            'label' => __('Edited Auction Bids', "woo_ua"),
                        ),   

                        array(    
                            'uwa_auction_deleted_bids' => 'log',
                            'range' => '',
                            'slug' => 'uwa_auction_deleted_bids', 
                            'type' => 'deleted_bids',
                            'label' => __('Deleted Auction Bids', "woo_ua"),
                        ),                           

                        array(
                            'uwa_auction_reports' => 'log',
                            'range' => '',
                            'type' => 'payment_hold',
                            'slug' => 'uwa_auction_reports', 
                            'label' => __('Payment Hold & Debit', "woo_ua"),
                        ),  

                    ); */             

                    // $active_tab = isset($_GET['uwa_auction_reports']) ? $_GET['uwa_auction_reports'] : 'uwa_auction_reports';

                    // $active_tab = 'uwa_auction_edited_bids';

                    foreach ($uat_default_tab as $tab) { 

                        $active_type = isset($_GET['type']) ? $_GET['type'] : "payment_hold";

                        if ($active_type == $tab['type']) { ?>

                            <a href="?page=uwa_auction_reports&type=<?php echo $tab['type']; ?>" 
                                class="nav-tab nav-tab-active"><?php echo $tab['label']; ?></a>

                                <?php

                        } else { ?>

                            <a href="?page=uwa_auction_reports&type=<?php echo $tab['type']; ?>" 
                                class="nav-tab"> <?php echo $tab['label']; ?></a>

                        <?php } ?>                        
                        

                    <?php } ?>
                    

                </h2>
           

                <?php

                    if ((isset($_GET['page']) && $_GET['page'] = 'uwa_auction_reports') &&
                        (isset($_GET['type']) && $_GET['type'] == 'edited_bids')) {

                        if ('yes' == $is_enable_slientbid_edit) {
                            include_once(UW_AUCTION_PRO_ADMIN . '/uwa_auctions_edited_bids.php');                        
                        }
                    }

                    else if ((isset($_GET['page']) && $_GET['page'] = 'uwa_auction_reports') &&
                        (isset($_GET['type']) && $_GET['type'] == 'deleted_bids')) {
                        
                        if ('yes' == $is_enable_slientbid_delete) {
                            include_once(UW_AUCTION_PRO_ADMIN . '/uwa_auctions_deleted_bids.php'); 
                        }

                    } else if ((isset($_GET['page']) && $_GET['page'] = 'uwa_auction_reports')) {
                        
                        include_once(UW_AUCTION_PRO_ADMIN . '/uwa_auctions-logs-list.php');
                        //uat_logs_list_table_page_handler_display();
                    }


                ?>
            </div>       
       </div>

    </div>
