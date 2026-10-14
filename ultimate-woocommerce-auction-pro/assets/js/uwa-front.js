jQuery(document).ready(function($){

	Uwa_ajax_url = WooUa.ajaxurl;
	Uwa_last_activity = WooUa.last_timestamp;

	/* Ajax query String */
	Ajax_qry_str = UWA_Ajax_Qry.ajaqry;
	running = false;

	/* Interval refresh page */	
	var refresh_time_interval = '';
	if(uwa_data.refresh_interval){
		refresh_time_interval =  setInterval(function(){
	    	getLiveStatusAuction();
    	}, uwa_data.refresh_interval*1000);
	}
	

function getKeyByValue(object, value) {
 
   for (var prop in object) { 
		if (object.hasOwnProperty(prop)) { 
			if (object[prop] === value) {
				 
			return prop; 
			}
		} 
	} 
}

 	$('form.cart').submit(function() {
		clearInterval(refresh_time_interval);
	});


	$( "input[name=wua_bid_value]" ).on('changein', function( event ) {
	 	$(this).addClass('changein');
	});

	$( ".uwa_more_details a" ).on('click', function(e){
		e.preventDefault();
		$('.uwa_more_details_display').slideToggle('fast');
	});

	/* --------------------------------------------------------
	 Add / Remove Watchlist
	----------------------------------------------------------- */

	$( ".uwa-watchlist-action" ).on('click', watchlist);

	function watchlist( event ) {

		var auction_id = jQuery(this).data('auction-id');
		var currentelement  =  $(this);

			jQuery.ajax({
				type : "get",
				url : Uwa_ajax_url,
				data : { post_id : auction_id, 'uwa-ajax' : "watchlist", ua_nonce : WooUa.ua_nonce },
				success: function(response) {
					currentelement.parent().replaceWith(response);
				    $( ".uwa-watchlist-action" ).on('click', watchlist);
				    jQuery( document.body).trigger('uwa-watchlist-action',[response,auction_id] );
				}
	      	});
	}

	/* --------------------------------------------------------
	Send Private Message
	----------------------------------------------------------- */

	$( document ).on( 'click', 'button#uwa_private_send', function() {
		var error = 0;
		var thisObj = $(this);
		var private_msg_form = $('#uwa_private_msg_form');
		
		/* collect the data */           
		var firstnameObj	= private_msg_form.find( '.uwa_pri_name' );
		var firstname 		= firstnameObj.val();
		var emailObj 		= private_msg_form.find( '.uwa_pri_email' );
		var email 			= emailObj.val();
		var messageObj 		= private_msg_form.find( '.uwa_pri_message' );
		var message 		= messageObj.val();
		var product_idObj 		= private_msg_form.find( '.uwa_pri_product_id' );
		var product_id 		= product_idObj.val();

		if( error == 0 ) {

			/* Hide / show for ajax loader */
			thisObj.hide();
			private_msg_form.find( 'img.uwa_private_msg_ajax_loader' ).show();	

			var data = {
						action: 	'send_private_message_process',
						ua_nonce: 		WooUa.ua_nonce,
						firstname: 		firstname,
						email: 		    email,
						message: 		message,
						product_id: 	product_id,
					}

			$.post( Uwa_ajax_url, data, function(response) {

				var data = $.parseJSON( response );                       

				if( data.status == 0 ) {
					if (data.error_name) {
						private_msg_form.find( '#error_fname' ).html( data.error_name );
					}else {
						private_msg_form.find( '#error_fname' ).html( "" );
					}

					if (data.error_email) {
						private_msg_form.find( '#error_email' ).html( data.error_email );
					}else {
						private_msg_form.find( '#error_email' ).html( "" );
					}

					if (data.error_message) {
						private_msg_form.find( '#error_message' ).html( data.error_message );
					}else {
						private_msg_form.find( '#error_message' ).html( "" );
					}
				} else {
					private_msg_form.find( '#error_message' ).html( "" );
					private_msg_form.find( '#error_fname' ).html( "" );
					private_msg_form.find( '#error_email' ).html( "" );
					private_msg_form.find( '#uwa_private_msg_success' ).html( data.success_message );
				}

				/* Hide / show for ajax loader */

					thisObj.show();
					private_msg_form.find( 'img.uwa_private_msg_ajax_loader' ).hide();
			});
		}

		return false;
	});

	/* --------------------------------------------------------
	 Add the css for auction menu in WCFM dashboard
	----------------------------------------------------------- */
	if ($('#wwcfm_uwa_auctions_listing_expander').length) {
	    $('.wcfm_menu_wcfm-uwa-auctionslist').css('background', '#17a2b8');
	}
	
	/* --------------------------------------------------------
	 Bidding on shop
	----------------------------------------------------------- */

	$( document ).on( 'click', 'button.uwa_quickbid', function() {
     	var auctionid = $(this).attr("dataauctionid");
		$(".uwa_bidding_on_shop_"+auctionid).toggle();
	});

	$( document ).on( 'click', 'button.shopbid', function() {
		var auctionid = $(this).attr("dataauctionid");
		var bid_value = $(".shopbidvalue_"+auctionid).val();
		
		var data = {
					action: 	'uwa_auction_ajax_add_bid',
					ua_nonce: 	WooUa.ua_nonce,
					product_id: auctionid,
					bid_value: 	bid_value,
				}

		$.post( Uwa_ajax_url, data, function(response) {	
			var data = $.parseJSON( response );
			if( data.status == 0 ) { 
				alert(data.msg_error);
			}
			else {
				alert(data.msg_success);
			}
		});	
	
    	return false;
	});
	
	// CheckExpired(); /* no need for this */

   /* ============================================= */

   /* ============================================= */

   /* Edit slient bid for frontend users */
     
	$(".bidauc_edit span.uwa_second").css("display", "none");
	$(".bidauc_bid span.uwa_second").css("display", "none");

   $( document ).on( 'click', 'a.action_front_edit', function(event) {

   	var pid = $(this).data('postid');

   	$(".bidauc_edit span.uwa_first[data-postid='"+pid+"']").css("display", "none");
		// $(".bidauc_bid span.uwa_first[data-postid='"+pid+"']").css("display", "none");

		$(".bidauc_edit span.uwa_second[data-postid='"+pid+"']").css("display", "inline-block");
		$(".bidauc_bid span.uwa_second[data-postid='"+pid+"']").css("display", "inline-block");

		var bid = $("input[name='placed_bid'][data-postid='"+pid+"']").val();
		$("input[name='edit_bid_value'][data-postid='"+pid+"']").val(bid);
		
   });

    /* update slient bid for frontend users */
	$(document).on('click', 'button.update_edit_bid', function(event) {
		event.preventDefault(); // Prevent default form submission
	
		var button = $(this);
		var pid = button.data('postid');

		// added here
		var formContainer = button.closest('.uwa_second');
	
		// Get input values
		var auctionbid = $("input[name='edit_bid_value'][data-postid='" + pid + "']").val();
		var placedbid = $("input[name='placed_bid'][data-postid='" + pid + "']").val();

		// changed here
		// var logid = $("input[name='logid'][data-postid='" + pid + "']").val();

		var logid = formContainer.find("input[name='logid']").val();
	
		auctionbid = parseFloat(auctionbid);
	
		if (auctionbid >= 1) {
			var process_for_edit = 0;
			var display_confirmbox = WooMsg.edit_confirmbox_enable;
	
			if (display_confirmbox == 'yes') {
				var output = confirm(WooMsg.edit_confirm_msg);
				if (!output) {
					return false; // Stop if user cancels
				} else {
					process_for_edit = 1;
				}
			} else {
				process_for_edit = 1;
			}
	
			if (process_for_edit == 1) {
				$.ajax({
					type: "post",
					url: Uwa_ajax_url,
					data: {
						action: "uwa_front_edit_bid_process",
						ua_nonce: WooUa.ua_nonce,
						auctionid: pid,
						edit_bid_value: auctionbid,
						placed_bid: placedbid,
						logid: logid
					},
					beforeSend: function() {
						button.prop("disabled", true).text("Updating..."); // Disable button while updating
					},
					success: function(response) {
						if (response.success) { // Check if AJAX response was successful
							$("input[name='edit_bid_value'][data-postid='" + pid + "']").val(response.data.new_bid);
							$("input[name='placed_bid'][data-postid='" + pid + "']").val(response.data.new_bid);
							$(".bid_acution_"+ pid).show();
							$(".bid_acution_"+ pid + " .bid-message").html(response.data.message).css("color", "green").fadeIn().delay(5000).fadeOut();

							$(".bid_acution_data_" + pid + " .bidauc_bid .woocommerce-Price-amount").html(response.data.new_bid_without_wc);
							
							$(".bidauc_edit span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
							$(".bidauc_bid span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
							$(".bidauc_edit span.uwa_second[data-postid='"+pid+"']").css("display", "none");
							$(".bidauc_bid span.uwa_second[data-postid='"+pid+"']").css("display", "none");
							
						} else {
							$(".bid_acution_"+ pid).show();
							$(".bid_acution_"+ pid + " .bid-message").html(response.data.message).css("color", "red").fadeIn().delay(5000).fadeOut(); 

							$(".bidauc_edit span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
							$(".bidauc_bid span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
							$(".bidauc_edit span.uwa_second[data-postid='"+pid+"']").css("display", "none");
							$(".bidauc_bid span.uwa_second[data-postid='"+pid+"']").css("display", "none");
						}
					},
					complete: function() {
						button.prop("disabled", false).text("Update Bid"); // Re-enable button

						$(".bidauc_edit span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
						$(".bidauc_bid span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
						$(".bidauc_edit span.uwa_second[data-postid='"+pid+"']").css("display", "none");
						$(".bidauc_bid span.uwa_second[data-postid='"+pid+"']").css("display", "none");
					}
				});
			}
		} else {
			$(".bid_acution_"+ pid + " .bid-message").text(WooMsg.edit_enter_bid_msg).css("color", "red").fadeIn().delay(5000).fadeOut();
			return false;
		}
	});
	/* update slient bid for frontend users */

   $( document ).on( 'click', 'button.cancel_edit_bid', function(event) {

   		var pid = $(this).data('postid');
		$(".bidauc_edit span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");
		$(".bidauc_bid span.uwa_first[data-postid='"+pid+"']").css("display", "inline-block");

		$(".bidauc_edit span.uwa_second[data-postid='"+pid+"']").css("display", "none");
		$(".bidauc_bid span.uwa_second[data-postid='"+pid+"']").css("display", "none");

   }); 

	/* ------------------------------------------ */

	/* Delete slient bid for frontend users */
	jQuery(document).on('click', '.tbl_changebid_list .bid_action a', function(event) {		
		event.preventDefault(); // Prevent default link behavior
	
		var process_for_del = 0;
		var display_confirmbox = WooMsg.del_confirmbox_enable;
	
		if (display_confirmbox == 'yes') {
			var output = confirm(WooMsg.del_confirm_msg);
			if (!output) {
				return false; // Do nothing if user cancels
			} else {
				process_for_del = 1;				  		
			}
		} else {
			process_for_del = 1;
		}
	
		if (process_for_del == 1) {
			var logid = $(this).data('id');
			var postid = $(this).data('postid');
			var row = $(this).closest('tr'); // Select the row to remove
	
			jQuery.ajax({
				type: "post",
				url: Uwa_ajax_url,
				data: {
					action: "uwa_front_delete_bid",
					ua_nonce: WooUa.ua_nonce,
					logid: logid,
					postid: postid
				},
				beforeSend: function() {											    
					$(".uwa_loaderimg").css("visibility", "visible");
					$(".tbl_changebid_list").css("opacity", "0.3");
				},		
				success: function(response) {
					var data = $.parseJSON(response); 
	
					if (data.status == 1) {
						row.fadeOut(300, function() { $(this).remove(); }); // Smoothly remove row
					}
				},
				complete: function() {						
					$(".uwa_loaderimg").css("visibility", "hidden");
					$(".tbl_changebid_list").css("opacity", "1");	
				}
			});
		}
	});
	/* Delete slient bid for frontend users */

	/* Delete All slient bid for frontend users */
	jQuery(document).on('click', '.uwa_bid_delete_checkbox_all', function(event) {
		var isChecked = jQuery(this).is(':checked');
		jQuery('.tbl_changebid_list .uwa_bid_delete_checkbox').prop('checked', isChecked);
	});
	
	jQuery(document).on('click', '.tbl_changebid_list .delete_all_bids a', function(event) {
		event.preventDefault();
	
		var logid = [];
		var postid = [];
	
		jQuery('.tbl_changebid_list .uwa_bid_delete_checkbox:checked').each(function() {
			logid.push(jQuery(this).data('id'));
			postid.push(jQuery(this).data('postid'));
		});
	
		if (logid.length === 0) {
			alert(WooMsg.select_one_bid_msg);
			return;
		}
	
		var process_for_del = 0;
		var display_confirmbox = WooMsg.del_confirmbox_enable;
	
		if (display_confirmbox == 'yes') {
			var output = confirm(WooMsg.del_confirm_msg);
			if (!output) return false;
			process_for_del = 1;
		} else {
			process_for_del = 1;
		}
	
		if (process_for_del == 1) {
			jQuery.ajax({
				type: "post",
				url: Uwa_ajax_url,
				data: {
					action: "uwa_front_delete_all_bid",
					ua_nonce: WooUa.ua_nonce,
					logid: logid,
					postid: postid
				},
				beforeSend: function() {
					jQuery(".uwa_loaderimg").css("visibility", "visible");
					jQuery(".tbl_changebid_list").css("opacity", "0.3");
				},
				success: function(response) {
					var data = jQuery.parseJSON(response);
					if (data.status === 1) {
						// Remove all checked rows
						jQuery('.tbl_changebid_list .uwa_bid_delete_checkbox:checked').closest('tr').fadeOut(300, function() {
							jQuery(this).remove();
						});
					} else {
						alert(data.error_message || WooMsg.error_deleting_bids);
					}
				},
				complete: function() {
					jQuery(".uwa_loaderimg").css("visibility", "hidden");
					jQuery(".tbl_changebid_list").css("opacity", "1");
				}
			});
		}
	});	
	/* Delete All slient bid for frontend users */
		
	/* ------------------------------------------ */
	/* Import your bids in silent auction for frontend users start */

	$('#uwa-bid-offline-upload-form').on('submit', function (e) {
		e.preventDefault();

		var formData = new FormData(this);
		formData.append('action', 'uwa_bid_offline_upload_xlsx');
		formData.append('ua_nonce', WooUa.ua_nonce);

		$('#upload-response .status-message').html(WooBidMsg.uploading);
		$('#upload-response').show();

		// Step 1: Upload File and Get Bids
		$.ajax({
			type: 'POST',
			url: Uwa_ajax_url,
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.success) {
					$('#upload-response .status-message').html('<p class="success">' + WooBidMsg.file_uploaded + '</p>');
					processNextBid(0, response.data.total_bids); // Start processing bids one by one
				} else {
					if (response.data && response.data.title && response.data.message){
						let errorTitle = response.data.title ? `<strong>${response.data.title}</strong><br>` : "";
						$('#upload-response .status-message').html('<p class="error">' + errorTitle + response.data.message + '</p>');
					}
				}
			},
			error: function () {
				$('#upload-response .status-message').html('<p class="error">' + WooBidMsg.upload_failed + '</p>');
			}
		});
	});

	function processNextBid(currentIndex, totalBids) {
		if (currentIndex >= totalBids) {
			$('#upload-response .status-message').append('<p class="success">' + WooBidMsg.all_processed + '</p>');
			return;
		}

		$.ajax({
			type: 'POST',
			url: Uwa_ajax_url,
			data: {
				action: 'uwa_bid_offline_upload_xlsx',
				ua_nonce: WooUa.ua_nonce,
				step: 'process',
				bid_index: currentIndex
			},
			success: function (response) {
				console.log("response", response);
				if (response.success) {
					let auctionTitle = response.data.title || WooBidMsg.unknown_auction; // Extract auction title properly
					let auctionMessage = response.data.message || WooBidMsg.bid_success;

					$('#upload-response .status-message').append(
						`<p class="success"><strong>${auctionTitle}</strong><br>${auctionMessage}</p>`
					);
				} else {
					if (response.data && response.data.title && response.data.message){
						let errorTitle = response.data.title ? `<strong>${response.data.title}</strong><br>` : "";
						$('#upload-response .status-message').append('<p class="error">' + errorTitle + response.data.message + '</p>');
					}
				}

				// Process next bid
				processNextBid(currentIndex + 1, totalBids);
			},
			error: function () {
				$('#upload-response .status-message').append('<p class="error">' + WooBidMsg.processing_error  + (currentIndex + 1) + '</p>');
				processNextBid(currentIndex + 1, totalBids);
			}
		});
	}
	
	/* Import your bids in silent auction for frontend users end */
	/* ------------------------------------------ */


}); /* end of document ready */

function getLiveStatusAuction () {
	
	if(jQuery('.woo-ua-auction-price').length<1){
        return;
    }

	if (running == true){
    	return;
    }

	running = true;
	var ajaxurl = Ajax_qry_str+'=get_live_stutus_auction'; 
	jQuery.ajax({
		type : "post",
		encoding:"UTF-8",
		url : ajaxurl,
		dataType: 'json',
		/*data : {action: "get_live_stutus_auction", "last_timestamp" : Uwa_last_activity,"curentpageenddate" : curentpageenddate},*/
		data : {action: "get_live_stutus_auction", "last_timestamp" : Uwa_last_activity},
		success: function(response)	{			
			
			/*-if(typeof response.last_timestamp == 'undefined' && response.clock_update!="" && response.clock_update=="yes"){
				 
				 
				jQuery('#uwa_auction_countdown').after().html('<a href="javascript:;" onclick="location.reload();" class="btn_refresh">Refresh</a>');
			}	*/ 
				
			if(response != null ) {

					//alert(JSON.stringify(response));

				if (typeof response.last_timestamp != 'undefined') {
					Uwa_last_activity = response.last_timestamp;
					
					
				}

				jQuery.each( response, function( key, value ) {
					  
					if( key  >  0 ){	/* loop is for auctions only so here key must be auctionid */
						
					if(jQuery('.btn_refresh').length != 1) {	
						
					 
					 if(uwa_data.antisniping_check=='yes'){
						
						if(value.wua_current_bider!= value.wua_loggedin_userid && value.wua_loggedin_userid!=0){
							
							 
							if(uwa_data.anti_sniping_timer_update_noti=="auto_page_refresh" || uwa_data.anti_sniping_timer_update_noti==""){
								jQuery('#uwa_auction_countdown').html('<div class="refresh_msg"> <span class="refresh-msg-text">'+uwa_data.anti_sniping_timer_update_noti_msg+'</span></div>');
								
								setTimeout(function(){ 
									location.reload();
								},5000);
							}
							
							
							if(uwa_data.anti_sniping_timer_update_noti=="manual_page_refresh"){
								jQuery('#uwa_auction_countdown').html('<div class="refresh_msg"> <span class="refresh-msg-text">'+uwa_data.anti_sniping_timer_update_noti_msg+'</span><a href="javascript:;" onclick="location.reload();" class="btn_refresh">Refresh</a></div>');
							}
							
						}
						
					 }
					}				
											
						var testclass = jQuery("body").hasClass( "postid-"+key );

							/* auction price */
							var price_len = jQuery("body").find(".woo-ua-auction-price[data-auction-id='" + key + "']").length;
								
							if(price_len > 0){
								if (typeof value.wua_curent_bid != 'undefined') {
									auction = jQuery("body").find(".woo-ua-auction-price[data-auction-id='" + key + "']");
									auction.replaceWith(value.wua_curent_bid);
								}
							}

							/* wining and losing text -- all pages */	
							/* text over image  and text above timer */

							if (typeof value.wua_uwa_imgtext != 'undefined' && typeof value.wua_uwa_detailtext != 'undefined') {
								if (value.wua_loggedin_userid > 0 ) {	

									var login_userid  = jQuery("span.uwa_imgtext").attr("data-user_id");
									if(value.wua_loggedin_userid == login_userid){
							
										jQuery("span.uwa_imgtext[data-auction_id='"+key+"']" ).html(value.wua_uwa_imgtext);
										jQuery("p.uwa_detailtext[data-auction_id='"+key+"']" ).html(value.wua_uwa_detailtext);
									}
								}
							}

							/* Ajax functionality for winning info */
							if (typeof value.wua_winuser != 'undefined' ) {					

								jQuery("div.winner-name[data-auction_id='"+key+"']" ).html(value.wua_winuser);

							}


							/* countdown timer */									
									
							if (typeof value.wua_timer != 'undefined') {
								var curenttimer = jQuery("div.uwa_auction_product_countdown[data-auction-id='" + key + "']");
																				
									var auction_oldtime = curenttimer.attr('data-time');
									var auction_newtime = value.wua_timer;
																							
									
							}	

							var auction_listing_class = jQuery("ul").hasClass( "products" );
							if (auction_listing_class == true) {

								/* display reserve price on auction listing page */
								if (typeof value.wua_uwa_listing_reserveprice_text != 'undefined' ) {	

									jQuery( "div.uwa_list_checkreserve[data-auction_id='"+key+"']" ).html(value.wua_uwa_listing_reserveprice_text);
								}
							}								

						if (testclass == true) {						
			

									if (typeof value.wua_current_bider != 'undefined' ) {

										var uwa_auction_layout = jQuery("input[name=uwa_auction_layout]").val();

										if(uwa_auction_layout == 'uwa_auction_second_layout'){

											var mainauction = jQuery("input[name=uwa-auction-place-bid]").val();

										} else {
											
											var mainauction = jQuery("input[name=uwa-place-bid]").val();

										}

										var currentuser = jQuery("input[name=user_id]");
										//var mainauction = jQuery("input[name=uwa-place-bid]").val();
										if (currentuser.length){
											if(value.wua_current_bider != currentuser.val() && mainauction == key ) {
												if(value.wua_auctionproxy == 'yes'){
	
												 	if (jQuery('.woocommerce-error').length > 0) {
														jQuery('.woocommerce-message').hide();
														jQuery('.woocommerce-error').replaceWith(uwa_data.outbid_message );
													} else{
														jQuery('.woocommerce-message').replaceWith(uwa_data.outbid_message);
													}

												} else {
													
													jQuery('.woocommerce-message').replaceWith(uwa_data.outbid_message);
												}
											}
										}
										if(jQuery( "span.uwa_winning[data-auction_id='"+key+"']" ).attr('data-user_id') != value.wua_current_bider){
											jQuery( "span.uwa_winning[data-auction_id='"+key+"']" ).remove()
										}
									}
									
									if (typeof value.wua_bid_value != 'undefined' ) {
										if(!jQuery( "input[name=uwa_bid_value][data-auction-id='"+key+"']" ).hasClass('wuachangedin')){
											
										}

										
										 

										/* set direct bid value in textbox */
										if(value.wua_auctiontype == 'reverse'){
											jQuery("input[id=uwa_bid_value_direct][data-auction-id='"+key+"']" ).val(value.wua_bid_value);
										}					
									}

									if (typeof value.wua_bid_value_inc != 'undefined' ) {

										jQuery( ".uwa_inc_price_ajax_"+key ).html(value.wua_bid_value_inc + " )");										
									}

									if (typeof value.wua_next_bids != 'undefined' ) {

										/* next bids drop down */
										jQuery("select[id=uwa_auction_lists][data-auction-id='"+key+"']" ).html(value.wua_next_bids);
										jQuery("select[id=uwa_bid_value_direct][data-auction-id='"+key+"']" ).html(value.wua_next_bids);
									}

									
									if (typeof value.wua_reserve != 'undefined' ) {					

										jQuery( ".checkreserve" ).html("<p>" + value.wua_reserve + "</p>");
									}		
									

									if (typeof value.add_to_cart_text != 'undefined' ) {

										jQuery( "a.button.product_type_auction[data-product_id='"+key+"']" ).text(value.add_to_cart_text);

									}
									
									if (typeof value.wua_activity != 'undefined' ) {

										jQuery("div[class=uwa_bids_history_data][data-auction-id='"+key+"']").html("");
										
										jQuery("div[class=uwa_bids_history_data][data-auction-id='"+key+"']").html(value.wua_activity);
										
									}

									if (typeof value.wua_purchaseproduct != 'undefined' ) {
								
										if(value.wua_purchaseproduct == "not_to_display"){
											
											jQuery("form#uwa_purchase_product_form[data-product_id='"+key+"'] .uwa_force_purchase_product").css("display", 
										"none"); 	

										}
										else{
											
											jQuery("form#uwa_purchase_product_form[data-product_id='"+key+"'] .uwa_force_purchase_product").css("display", 
										
										"inline-block"); 

										}
									
									}
						
						
							
							
						} /* end of if - string to check */
						
					} /* end of if - key > 0  */
					
				});	
			}
	        
			running = false;
		},

		error: function() {
			running = false;
		}

	});	 



} /* end of function - getLiveStatusAuction */

