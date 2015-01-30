<?php
/*
Plugin Name: Stripe Donation for WordPress
Plugin URI: 
Description: Donate using Stripe Checkout without leaving the page.
Version: 1.0
Author: Hal Gatewood
Author URI: http://www.halgatewood.com/
License: GPL
Copyright: Hal Gatewood
Text Domain: hg-stripe-donate

*/


// AJAX POST OF DONATION
require_once( dirname(__FILE__) . "/includes/ajax-post.php" );

// SETTINGS
require_once( dirname(__FILE__) . "/includes/settings.php" );


// ADMIN MEDIA HELPER
if( is_admin() ) require_once( dirname(__FILE__) . "/includes/admin-thickbox.php" );


// SETUP
function hg_stripe_donate_setup()
{
	load_plugin_textdomain( 'hg-stripe-donate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	add_action(	'admin_menu', 'hg_stripe_donation_setting_page_menu' );
}
add_action('plugins_loaded', 'hg_stripe_donate_setup', 99999);


// ENQUEUE CSS
function hg_stripe_donate_wp_head( ) 
{
	wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ) );
	wp_enqueue_style( 'hg-stripe-donate', plugins_url( '/hg-stripe-donate.css', __FILE__ )  );
}
add_action('wp_enqueue_scripts', 'hg_stripe_donate_wp_head');


// SHORTCODE ACTION
function hg_stripe_donate_shortcode( $atts )
{
	
	// ID
	$button_id = isset($atts['id']) ? $atts['id'] : wp_rand(0, 9999999);
	
	
	// CHECKOUT DEFAULTS
	$checkout_name 			= get_option( 'hg-stripe-donation-checkout-name');
	$checkout_desc 			= get_option( 'hg-stripe-donation-checkout-desc');
	$checkout_image 		= isset($atts['image']) ? $atts['image'] : get_option( 'hg-stripe-donation-checkout-image');
	$checkout_label 		= isset($atts['checkout_label']) ? $atts['checkout_label'] : __('Donate', 'hg-stripe-donate') . " {{amount}}";
	$button_label 			= isset($atts['button_label']) ? $atts['button_label'] : __('Donate', 'hg-stripe-donate');
	$checkout_remember 		= isset($atts['remember']) ? $atts['remember'] : 'false';
	$checkout_zipcode 		= isset($atts['zipcode']) ? $atts['zipcode'] : 'false';
	$currency_code 			= get_option( 'hg-stripe-donation-currency', 'USD');
	$prefilled_amount 		= isset($atts['amount']) ? $atts['amount'] : "";
	$amount_element			= isset($atts['amount_element']) ? $atts['amount_element'] : "#hg-stripe-donate-amount-" . $button_id;
	
	
	// PUBLISHER KEY
	$pub_key = get_option( 'hg-stripe-donation-api-pub-live');
	
	// CHECK FOR TEST MODE
	$test_mode = false;
	$test_mode_var = 'live';
	if( isset($atts['mode']) AND $atts['mode'] == "test" )
	{
		$test_mode = true;
		$test_mode_var = 'test';
		$pub_key = get_option( 'hg-stripe-donation-api-pub-test');
	}
	
	// GET LOGGED IN USER
	$checkout_email = "";
	if( isset($atts['user_check']) AND $atts['user_check'] == "true" AND is_user_logged_in() )
	{
		global $current_user;
		if( isset($current_user->data->user_email) )
		{
			$checkout_email = $current_user->data->user_email;
		}
	}

	
	// TARGETS
	$success_html_id 		= "hg-stripe-donation-success-" . $button_id;
	$error_html_id 			= "hg-stripe-donation-error-" . $button_id;
	
	
	// FORM TO USE
	$layout = isset($atts['layout']) ? $atts['layout'] : "user-amount";
	
	// IF CUSTOM
	if( $layout != "user-amount" AND $layout != "exact")
	{
		$layout_template = locate_template( array( $layout . ".php" ) );
		
		// NO CUSTOM TEMPLATE FOUND
		if( !$layout_template )
		{
			// IF ADMIN, SHOW A MESSAGE
			if( current_user_can( 'manage_options' ) )
			{
				echo "<b style='color:red;'>" . sprintf(__("A donation template could not be found for %s.php", 'hg-stripe-donate'), $layout) . "</b>";
			}
			else
			{
				$layout == "user-amount";	
			}
		}
	}
	
	
	// GET TEMPLATE LOCATION FOR DEFAULT LAYOUTS
	if( $layout == "user-amount" OR $layout == "exact")
	{
		$layout_template = dirname(__FILE__) . "/layouts/" . $layout . ".php";	
	}
	
	
	// POST TO URL
	$post_to_url = trailingslashit(get_site_url());
	if( !$test_mode ) $post_to_url = str_replace( "http://", "https://" , $post_to_url );
	if( isset($atts['ssl']) AND $atts['ssl'] == "off" )  $post_to_url = str_replace( "https://", "http://" , $post_to_url );
	
	
	// SANITIZE
	if( $checkout_image == "false" ) $checkout_image = "";
	if( $checkout_remember != 'true' AND $checkout_remember != 'false' ) $checkout_remember = 'false';
	if( $checkout_zipcode != 'true' AND $checkout_zipcode != 'false' ) $checkout_zipcode = 'false';
	$button_label = str_replace("{{amount}}", $prefilled_amount / 100, $button_label);
	

	ob_start();
?>
	<div class="hg-stripe-donation-wrap hg-stripe-donation-layout-<?php echo $layout; ?>">
		<div id="<?php echo $success_html_id; ?>" class="hg-stripe-donation-message-box hg-stripe-donation-success-message-box"></div>
		<div id="<?php echo $error_html_id; ?>" class="hg-stripe-donation-message-box hg-stripe-donation-error-message-box"></div>
		<?php if( file_exists( $layout_template ) ) include($layout_template); ?>
	</div>

	<script>
	
	var handler_<?php echo $button_id ?> = StripeCheckout.configure({
		key: '<?php echo $pub_key; ?>',
		image: '<?php echo $checkout_image; ?>',
		token: function(token) 
		{
			var hg_ajax_post_amount_<?php echo $button_id ?> = jQuery('<?php echo $amount_element ?>').val().replace(/,/,"");
				
			jQuery.ajax(
			{
				type: "POST",
				url: '<?php echo $post_to_url; ?>',
				data: { action: "hg-stripe-post", token: token.id, mode: '<?php echo $test_mode_var; ?>', amount: hg_ajax_post_amount_<?php echo $button_id ?> },
				success: function( msg ) 
				{
					jQuery('#hg-stripe-donate-form-<?php echo $button_id ?>, #<?php echo $error_html_id; ?>').hide();
					jQuery('#<?php echo $success_html_id; ?>').html(msg).show();	  
				},
				error: function(xhr, status, error)
				{
					jQuery('#<?php echo $success_html_id; ?>').hide();
					jQuery('#<?php echo $error_html_id; ?>').html(xhr.responseText).show();	
				}
			});
		}
	});
	
	document.getElementById('hg-stripe-donate-button-<?php echo $button_id ?>').addEventListener('click', function(e) 
	{
		var hg_ajax_amount_<?php echo $button_id ?> = jQuery('<?php echo $amount_element ?>').val().replace(/,/,"");
		
		if( hg_ajax_amount_<?php echo $button_id ?> && jQuery.isNumeric(hg_ajax_amount_<?php echo $button_id ?>) && hg_ajax_amount_<?php echo $button_id ?> != 0.00 ) 
		{
			jQuery("#<?php echo $error_html_id; ?>").html('').hide();
			
			// Open Checkout with further options
			handler_<?php echo $button_id ?>.open({
				name: '<?php echo $checkout_name; ?>',
				description: '<?php echo $checkout_desc; ?>',
				amount: hg_ajax_amount_<?php echo $button_id ?> * 100,
				panelLabel: "<?php echo $checkout_label; ?>",
				allowRememberMe: <?php echo $checkout_remember; ?>,
				currency: '<?php echo $currency_code; ?>',
				zipCode: <?php echo $checkout_zipcode; ?>,
				email: '<?php echo $checkout_email; ?>'
			});
			e.preventDefault();  
		}
		else
		{
			jQuery("#<?php echo $error_html_id; ?>").html('<?php echo __('Please enter an amount you would like to donate', 'hg-stripe-donate'); ?>').slideDown('fast');
		}
	
	});
	</script>

<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode( 'stripe_donate', 'hg_stripe_donate_shortcode' );


// ERROR HANDELING
function hg_stripe_donate_error( $msg )
{
	status_header(404);
	die( $msg );
}


