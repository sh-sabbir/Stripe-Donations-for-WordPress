<?php
	
// CREATE THE SETTINGS PAGE
function hg_stripe_donation_setting_page_menu()
{
	add_options_page( 'Stripe Donation', 'Stripe Donation', 'manage_options', 'hg-stripe-donate', 'hg_stripe_donation_page' );
}



function hg_stripe_donation_page()
{
?>
<div class="wrap">
    <h2><?php _e('Stripe Donation Settings', 'hg-stripe-donate'); ?></h2>
    <form action="options.php" method="POST">
        <?php settings_fields( 'hg-stripe-donate-settings-group' ); ?>
        <?php do_settings_sections( 'hg-stripe-donate' ); ?>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}


// SET SETTINGS LINK ON PLUGIN PAGE
function hg_stripe_donation_plugin_action_links( $links, $file ) 
{
	$settings_link = '<a href="' . admin_url( 'options-general.php?page=hg-stripe-donate' ) . '">' . esc_html__( 'Settings', 'hg-stripe-donate' ) . '</a>';
	
	if ( $file == 'hg-stripe-donate/hg-stripe-donate.php' )
	{
		array_unshift( $links, $settings_link );
	}
	
	if( !get_option('edd-updater-email-stripe-donation') )
	{
		$register_link = '<a href="' . admin_url( 'options-general.php?page=hg-stripe-donate' ) . '">' . esc_html__( 'Register', 'hg-stripe-donate' ) . '</a>';
	
		if ( $file == 'hg-stripe-donate/hg-stripe-donate.php' )
		{
			array_unshift( $links, $register_link );
		}
	}
	return $links;
}
add_filter( 'plugin_action_links', 'hg_stripe_donation_plugin_action_links', 10, 2 );




define( 'HG_STRIPE_SUCCESS_MESSAGE', 'Your donation has been collected. Thank You!');
define( 'HG_STRIPE_CC_STATEMENT', 'Donation');





add_action( 'admin_init', 'hg_stripe_donation_setting_init' );
function hg_stripe_donation_setting_init()
{

    // KEYS
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-api-secret-test' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-api-pub-test' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-api-secret-live' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-api-pub-live' );
    
    // DEFAULTS
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-checkout-name' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-checkout-desc' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-checkout-image' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-currency' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-success-message' );
    register_setting( 'hg-stripe-donate-settings-group', 'hg-stripe-donation-cc-statement' );
    
	// ---- //

	// KEYS
	add_settings_section( 'hg-stripe-donate-settings-api-group', '', 'hg_stripe_donation_apikeys_description', 'hg-stripe-donate' );
	add_settings_field( 'hg-stripe-donation-api-secret-test', __('Test: Secret Key', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-api-group', array('field_name' => 'hg-stripe-donation-api-secret-test') );
	add_settings_field( 'hg-stripe-donation-api-pub-test', __('Test: Publishable Key', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-api-group', array('field_name' => 'hg-stripe-donation-api-pub-test') );
	add_settings_field( 'hg-stripe-donation-api-secret-live', __('Live: Secret Key', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-api-group', array('field_name' => 'hg-stripe-donation-api-secret-live') );
	add_settings_field( 'hg-stripe-donation-api-pub-live', __('Live: Publishable Key', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-api-group', array('field_name' => 'hg-stripe-donation-api-pub-live') );
	
	// DEFAULTS
	add_settings_section( 'hg-stripe-donate-settings-defaults-group', '', 'hg_stripe_donation_defaults_description', 'hg-stripe-donate' );
	add_settings_field( 'hg-stripe-donation-checkout-name', __('Checkout Name', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-defaults-group', array('field_name' => 'hg-stripe-donation-checkout-name', 'field_desc' => __('This text is displayed in the Stripe Checkout Popup directly under the image if one has been uploaded.', 'hg-stripe-donate') ) );
	add_settings_field( 'hg-stripe-donation-checkout-desc', __('Checkout Description', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-defaults-group', array('field_name' => 'hg-stripe-donation-checkout-desc', 'field_desc' => __('This text is displayed in the Stripe Checkout Popup directly below the name above. It is generally used to explain what the donation is about.', 'hg-stripe-donate') ) );
	add_settings_field( 'hg-stripe-donation-checkout-image', __('Checkout Image', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-defaults-group', array('field_name' => 'hg-stripe-donation-checkout-image', 'field_desc' => __('URL to a square image that will appear in the top of the Stripe Checkout Popup.', 'hg-stripe-donate') ) );
	add_settings_field( 'hg-stripe-donation-currency', __('Currency', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-defaults-group', array('field_name' => 'hg-stripe-donation-currency', 'field_desc' => __('A list of currency codes can be found in the Stripe documentation', 'hg-stripe-donate') ) );
	add_settings_field( 'hg-stripe-donation-success-message', __('Success Message', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-defaults-group', array('field_name' => 'hg-stripe-donation-success-message', 'field_desc' => __('The default success message returned through the ajax post', 'hg-stripe-donate') ) );
	add_settings_field( 'hg-stripe-donation-cc-statement', __('Credit Card Description', 'hg-stripe-donate'), 'hg_stripe_donation_text_field', 'hg-stripe-donate', 'hg-stripe-donate-settings-defaults-group', array('field_name' => 'hg-stripe-donation-cc-statement', 'field_desc' => __('This will prepend a statement description on the end of your users credit card bill. ', 'hg-stripe-donate') ) );
}

function hg_stripe_donation_empty_description() { }
function hg_stripe_donation_apikeys_description()
{ 
	echo "<hr>";
	echo "<h3>" . __('Stripe API Keys', 'hg-stripe-donate') . "</h3>";
	
	echo "<p>";
	echo __("The following four field will sync your donation buttons to your Stripe account.", 'hg-stripe_donate') . "</p>";
	echo "<p>";
	echo " <a href='https://dashboard.stripe.com/account/apikeys' class='button' target='_blank'>";
	echo __('Your Stripe API keys are found here', 'hg-stripe-donate');
	echo " &rarr;</a>";
	echo "</p>";
}

function hg_stripe_donation_defaults_description()
{ 
	echo "<hr>";
	echo "<h3>" . __('Default Settings', 'hg-stripe-donate') . "</h3>";
	echo "<p>" . __('These settings will be used by default. Some of them maybe overwritten by using parameters in your shortcode. ', 'hg-stripe-donate') . "</p>";
	echo "<p>";
	echo " <a href='https://halgatewood.com/docs/plugins/' class='button' target='_blank'>";
	echo __('See the documentation for more details', 'hg-stripe-donate');
	echo " &rarr;</a>";
	echo "</p>";
}

function hg_stripe_donation_text_field( $args )
{
		echo "<input type='text' name='" . $args['field_name'] . "' value='" . esc_attr( get_option( $args['field_name'] ) ) . "' style='width:90%;' />";
		if( isset($args['field_desc']) ) echo "<p>" . $args['field_desc'] . "</p>";
}
