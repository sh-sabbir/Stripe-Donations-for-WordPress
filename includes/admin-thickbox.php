<?php
	
// MEDIA BUTTON
function hg_stripe_donation_button() 
{
	global $pagenow, $typenow, $wp_version;
	$output = '';
	if ( version_compare( $wp_version, '3.5', '>=' ) AND in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) 
	{
		$img = '<style>#hg-stripe-donation-media-button::before { font: 400 18px/1 dashicons; content: \'\f475\'; }</style><span class="wp-media-buttons-icon" id="hg-stripe-donation-media-button"></span>';
		$output = '<a href="#TB_inline?width=640&inlineId=add-stripe-donation" class="thickbox button stripe-donation-thickbox" title="' .  __( 'Add Donation', 'hg-stripe-donate'  ) . '" style="padding-left: .4em;"> ' . $img . __( 'Add Donation', 'hg-stripe-donate'  ) . '</a>';
	}
	echo $output;
}
add_action( 'media_buttons', 'hg_stripe_donation_button', 11 );


// MEDIA BUTTON FUNCTIONALITY
function hg_stripe_donation_admin_footer_for_thickbox() 
{
	global $pagenow, $typenow, $wp_version;

	if ( version_compare( $wp_version, '3.5', '>=' ) AND in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { ?>
		<script type="text/javascript">
            function insert_hg_stripe_donate() 
            {
	           	var add_to_shortcode = "";
	           	
	           	var test_mode = jQuery('#hg-stripe-donate-test').is(":checked");
	           	if( test_mode )  add_to_shortcode = add_to_shortcode + " mode=\"test\"";
	           	
				var id = jQuery('#hg-stripe-donate-id').val();
				if( id ) add_to_shortcode = add_to_shortcode + " id=\"" + id + "\"";
				
				var image = jQuery('#hg-stripe-donate-image').val();
				if( image ) add_to_shortcode = add_to_shortcode + " image=\"" + image + "\"";		
				
				var amount = jQuery('#hg-stripe-donate-amount').val();
				if( amount ) add_to_shortcode = add_to_shortcode + " amount=\"" + amount + "\"";
				
	           	var zipcode = jQuery('#hg-stripe-donate-zipcode').is(":checked");
	           	if( zipcode )  add_to_shortcode = add_to_shortcode + " zipcode=\"true\"";
	           	
	           	var remember = jQuery('#hg-stripe-donate-remember').is(":checked");
	           	if( remember )  add_to_shortcode = add_to_shortcode + " remember=\"true\"";
	           	
	           	var layout_exact = jQuery('#hg-stripe-donate-layout-exact').is(":checked");
	           	if( layout_exact )  add_to_shortcode = add_to_shortcode + " layout=\"exact\"";
	           	
	           	var layout_custom = jQuery('#hg-stripe-donate-layout-custom').is(":checked");
	           	if( layout_custom )
	           	{
		           var layout_custom_filename = jQuery('#hg-stripe-donate-layout-filename').val();	
			       if( layout_custom_filename )  add_to_shortcode = add_to_shortcode + " layout=\"" + layout_custom_filename + "\"";  	
	           	}
	           	
				var button_label = jQuery('#hg-stripe-donate-button_label').val();
				if( button_label ) add_to_shortcode = add_to_shortcode + " button_label=\"" + button_label + "\"";
	           	
				var checkout_label = jQuery('#hg-stripe-donate-checkout_label').val();
				if( checkout_label ) add_to_shortcode = add_to_shortcode + " checkout_label=\"" + checkout_label + "\"";
				
                window.send_to_editor("[stripe_donate" + add_to_shortcode + "]");
            }
		</script>

		<div id="add-stripe-donation" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">

				<h3><?php _e('Optional Settings', 'hg-stripe-donate'); ?></h3>


				<div style="padding-bottom: 7px;">
					<input type="checkbox" id="hg-stripe-donate-test" name="hg-stripe-donate-test" value="1"> <?php _e('Test Mode', 'hg-stripe-donate'); ?>
				</div>
				
				<div style="padding-bottom: 7px;">
					<input type="checkbox" id="hg-stripe-donate-zipcode" name="hg-stripe-donate-zipcode" value="1"> <?php _e('Validate ZipCode?', 'hg-stripe-donate'); ?>
				</div>
				
				<div style="padding-bottom: 7px;">
					<input type="checkbox" id="hg-stripe-donate-remember" name="hg-stripe-donate-remember" value="1"> <?php _e('Allow Remember Me?', 'hg-stripe-donate'); ?>
				</div>

				<div style="padding-bottom: 7px;">
					<input type="text" id="hg-stripe-donate-id" value="">
					<?php _e('ID', 'hg-stripe-donate'); ?>
				</div>
				<div style="padding-bottom: 7px;">
					<input type="text" id="hg-stripe-donate-image" value="">
					<?php _e('Square Image URL', 'hg-stripe-donate'); ?>
				</div>
				
				<div style="padding-bottom: 7px;">
					<input type="radio" id="hg-stripe-donate-layout-user" name="hg-stripe-donate-layout" value="user-amount" CHECKED onclick="jQuery('#hg-stripe-donate-custom-div').slideUp(); jQuery('#hg-stripe-donate-custom').val('');"> <?php _e('User Specified Amount', 'hg-stripe-donate'); ?>
					<input type="radio" id="hg-stripe-donate-layout-exact" name="hg-stripe-donate-layout" value="exact" onclick="jQuery('#hg-stripe-donate-custom-div').slideUp(); jQuery('#hg-stripe-donate-custom').val('');"> <?php _e('Exact (specify below', 'hg-stripe-donate'); ?>
					<input type="radio"  id="hg-stripe-donate-layout-custom" name="hg-stripe-donate-layout" value="custom" onclick="jQuery('#hg-stripe-donate-custom-div').slideDown();"> <?php _e('Custom', 'hg-stripe-donate'); ?>
				</div>
				
				<div id="hg-stripe-donate-custom-div" style="display:none; padding-bottom: 7px;">
					<input type="text" id="hg-stripe-donate-layout-filename" value="">
					<?php _e('Custom layout filename (without .php)', 'hg-stripe-donate'); ?>
				</div>
				
				<div style="padding-bottom: 7px;">
					<input type="text" id="hg-stripe-donate-amount" value="">
					<?php _e('Preset amount (in pennies)', 'hg-stripe-donate'); ?>
				</div>

				<div style="padding-bottom: 7px;">
					<input type="text" id="hg-stripe-donate-button_label" value="">
					<?php _e('Donate Button Label', 'hg-stripe-donate'); ?>
				</div>
				
				<div style="padding-bottom: 7px;">
					<input type="text" id="hg-stripe-donate-checkout_label" value="">
					<?php _e('Checkout Button Label (in Stripe Popup)', 'hg-stripe-donate'); ?>
				</div>
				
				<p class="submit">
					<input type="button" id="hg-stripe-donation-insert" class="button-primary" value="<?php echo __( 'Insert Stripe Donation Button', 'hg-stripe-donate' ); ?>" onclick="insert_hg_stripe_donate();" />
					<a id="hg-stripe-donation-cancel-add" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Cancel', 'hg-stripe-donate' ); ?>"><?php _e( 'Cancel', 'hg-stripe-donate' ); ?></a>
				</p>
			</div>
		</div>
	<?php
	}
}
add_action( 'admin_footer', 'hg_stripe_donation_admin_footer_for_thickbox' );