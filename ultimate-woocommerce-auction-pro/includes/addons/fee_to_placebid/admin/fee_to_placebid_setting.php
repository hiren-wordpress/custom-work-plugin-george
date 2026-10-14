<?php

/**
 *
 * @package Ultimate WooCommerce Auction ADDON fee to place bid tab
 * @author Nitesh Singh 
 * @since 1.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(isset($_POST['uwa-fee-to-placebid-submit'])){

		/* Capability + CSRF checks */
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to update these settings.', 'woo_ua' ) );
		}
		check_admin_referer( 'uwa_fee_to_placebid_save', 'uwa_ftp_nonce' );

		if(isset($_POST['uwa_bid_fee_enable'])){
			update_option('uwa_bid_fee_enable', "yes");
		} else{
			update_option('uwa_bid_fee_enable', "no");
		}
	
		if (isset($_POST['uwa_bid_fee_amount'])) {
			if($_POST['uwa_bid_fee_amount'] >= 1){
				update_option('uwa_bid_fee_amount', absint($_POST['uwa_bid_fee_amount']));
			}
			else{
				update_option('uwa_bid_fee_amount', 1);
			}			
		}
	
		if(isset($_POST['uwa_bid_fee_button_text'])){
			if($_POST['uwa_bid_fee_button_text'] == ""){
				$uwa_bid_fee_button_text = "Pay Fee";
				update_option('uwa_bid_fee_button_text', $uwa_bid_fee_button_text);
			}
			else{
				update_option('uwa_bid_fee_button_text', sanitize_text_field( wp_unslash( $_POST['uwa_bid_fee_button_text'] ) ));
			}
		}

		if(isset($_POST['uwa_bid_fee_message_text'])){
			$uwa_bid_fee_message_text = "You need to pay fee once before placing your first bid on the website. After successful payment, you can place any number of bids on any product on the website.";

			if($_POST['uwa_bid_fee_message_text'] == ""){
				update_option('uwa_bid_fee_message_text', $uwa_bid_fee_message_text);
			}
			else{
				update_option('uwa_bid_fee_message_text', sanitize_textarea_field( wp_unslash( $_POST['uwa_bid_fee_message_text'] ) ));
			}
		}

		if(isset($_POST['uwa_bid_fee_type'])){
			$fee_type = $_POST['uwa_bid_fee_type'];
			if($fee_type == "onetime"){
				update_option('uwa_bid_fee_type', "onetime");
			}
			else if($fee_type == "eachtime"){
				update_option('uwa_bid_fee_type', "eachtime");
			}
		} else{
			update_option('uwa_bid_fee_type', "onetime");
		}

	
} /* end of if - save changes */


	$uwa_bid_fee_enable = get_option('uwa_bid_fee_enable');
	$uwa_bid_fee_checked_enable = "";
		if($uwa_bid_fee_enable == "yes"){
			$uwa_bid_fee_checked_enable = "checked";
		}

	$uwa_bid_fee_amount = get_option('uwa_bid_fee_amount');
	if($uwa_bid_fee_amount == false ||  $uwa_bid_fee_amount == 0){
		$uwa_bid_fee_amount = 1;
	}

	$get_bid_fee_button_text = get_option('uwa_bid_fee_button_text');

	if ($get_bid_fee_button_text == false || empty($get_bid_fee_button_text)){
		$uwa_bid_fee_button_text = "Pay Fee";
	}	
	else{
		//$uwa_bid_fee_button_text = __($get_bid_fee_button_text, "woo_ua");
		$uwa_bid_fee_button_text = $get_bid_fee_button_text;
	}


	$get_bid_fee_message_text = get_option('uwa_bid_fee_message_text');

	if ($get_bid_fee_message_text == false || empty($get_bid_fee_message_text)){
		$uwa_bid_fee_message_text = "You need to pay fee once before placing your first bid on the website. After successful payment, you can place any number of bids on any product on the website.";
	}	
	else{
		//$uwa_bid_fee_message_text = __($get_bid_fee_message_text, "woo_ua");
		$uwa_bid_fee_message_text = $get_bid_fee_message_text;
	}

	$fee_type_onetime_checked = "";
	$fee_type_eachtime_checked = "";
	$uwa_bid_fee_type = get_option("uwa_bid_fee_type");
	
	if($uwa_bid_fee_type == "onetime" || $uwa_bid_fee_type == "" ){
		$fee_type_onetime_checked = "checked";
	}
	else if($uwa_bid_fee_type == "eachtime" ){ 
		$fee_type_eachtime_checked = "checked";
	}

?>

<div class='wrap'>
<div id='icon-tools' class='icon32'></br></div>	
	<form id="uwa_fee_to_placebid_form" class="auction_settings_section_style" action=""
		method="POST">

		<?php wp_nonce_field( 'uwa_fee_to_placebid_save', 'uwa_ftp_nonce' ); ?>

		<table class="form-table">
		
				<!---  ---------   Fee for place bid -------   -->

				<tr class="uwa_heading">
					<th colspan="2"><?php _e( 'Place Bid Fee', 'woo_ua' ); ?></th>
				</tr>

				<tr>
					<th scope="row"><label for="">
						<?php _e('Fee to place bid', 'woo_ua'); ?></label></th>
					<td class="uwaforminp">
						<a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
						<span><?php _e("Enable or disable fee for bidding", 'woo_ua');  ?>
					   	</span></a>

						<label class="switch">						  
						  <input class="coupon_question1" type="checkbox" <?php echo $uwa_bid_fee_checked_enable;?> name="uwa_bid_fee_enable"  id="uwa_bid_fee_enable" value="1">
						  <span class="slider"></span>
						</label>
						
						<span class="description"><?php _e('Do you want users to pay a bidding fee before placing their first bid?', 'woo_ua'); ?></span>
					</td>
				</tr>

				<tr class="answer1">
					<th scope="row"></th>
					<td class="uwaforminp1">											
						<a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
						<span><?php _e('Bidder have to pay fee only once for any product', 'woo_ua');  ?> </span></a>
					
						<input type="radio" name="uwa_bid_fee_type" id="uwa_bid_fee_type_onetime" 
						value="onetime"  <?php echo $fee_type_onetime_checked; ?>> 
						<span class="description"><?php _e('Do you want users to pay a one time fee to bid for any product?', 'woo_ua');  ?></span>
						<span style="margin-right:20px;"></span> 

						<br>
						<span class="uwaforminp">
							<a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
							<span><?php _e('Bidder have to pay fee for every product', 'woo_ua');  ?></span></a>

							<input type="radio" name="uwa_bid_fee_type" id="uwa_bid_fee_type_eachtime" value="eachtime" <?php echo $fee_type_eachtime_checked; ?>> 
							<span class="description"><?php _e('Do you want users to pay a fee each time they bid on a new product?', 'woo_ua');  ?></span>
						</span>
					
					</td>
				</tr>
				<tr class="answer1">
					<th>
						<label for="uwa_bid_fee_amount"><?php _e( 'Fee to pay', 'woo_ua' ); ?></label>
					</th>
					<td class="uwaforminp">	<a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
						<span><?php _e('Fee amount to pay', 'woo_ua');  ?></span></a>					
						<input type="number" name="uwa_bid_fee_amount" style="width: 157px;" class="regular-number" min="1" id="uwa_bid_fee_amount" value="<?php echo $uwa_bid_fee_amount; ?>">
					</td>
				</tr>
				<tr class="answer1">
					<th scope="row">
						<label for="uwa_bid_fee_button_text">
							<?php _e("Button Text to show in alert box", "woo_ua"); ?>
						</label>
					</th>
					<td>
						<input type="text" style="width:38%;" name="uwa_bid_fee_button_text"
						id="uwa_bid_fee_button_text" value="<?php echo esc_attr( $uwa_bid_fee_button_text ); ?>" >
					</td>				
				</tr>
				<tr class="answer1">
					<th scope="row">
						<label for="uwa_bid_fee_message_text">
							<?php _e("Message to show in alert box", "woo_ua"); ?>
						</label>
					</th>
					<td>
						<textarea  name="uwa_bid_fee_message_text" id="uwa_bid_fee_message_text" rows="4" cols="50"><?php echo esc_textarea( trim( $uwa_bid_fee_message_text ) ); ?></textarea>
					</td>				
				</tr>

				<!---  --------------   Fee for place bid END ------------   -->
	
			
			<tr>
				<td colspan="2" valign="top" scope="row">								
					<input type="submit" id="uwa-fee-to-placebid-submit" 
						name="uwa-fee-to-placebid-submit" class="button-primary" 
						value="<?php _e('Save Changes','woo_ua');?>" />
				</td>
			</tr>

		</table>
	</form>
</div>

<style>		 

.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 21px;
}

.switch input {
  display: none;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #dedede;
  border-radius: 40px;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 15px;
  width: 15px;
  background: #fff;
  border-radius: 50%;
  left: 4px;
  bottom: 3px;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked + .slider {
  background: #3582c4;
}

input:checked + .slider:before {
  -webkit-transform: translateX(30px);
  -moz-transform: translateX(30px);
  transform: translateX(17px);
}

input:focus + .slider {
}

</style>


<script type="text/javascript">
	jQuery("document").ready(function($){

	if ($(".coupon_question1").is(':checked')){
		$(".answer1").show();
	}else{
		$(".answer1").hide();
	}
	$(".coupon_question1").click(function() {
		if($(this).is(":checked")) {
			$(".answer1").show();
		} else {
			$(".answer1").hide();
		}
	});

});
</script>