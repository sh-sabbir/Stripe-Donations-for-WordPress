<?php


// AJAX POST TO STRIPE
add_action( 'init', 'hg_stripe_donate_post', 0 );
function hg_stripe_donate_post()
{
	if( isset($_POST['action']) AND $_POST['action'] == 'hg-stripe-post' )
	{
		if( !class_exists('Stripe') )
		{
			
			// KEYS
			$sec_key = get_option( 'hg-stripe-donation-api-secret-live');
			
			// CHECK FOR TEST MODE
			if( isset($_POST['mode']) AND $_POST['mode'] == "test" )
			{
				$sec_key = get_option( 'hg-stripe-donation-api-secret-test');	
			}
			
		 	// LOAD STRIPE API
		 	require_once( dirname(__FILE__) . '/../api/Stripe.php' );
		 	Stripe::setApiKey( $sec_key );
		 	
		 	$token 		= $_POST['token'];
		 	$amount 	= (int) $_POST['amount'];

		 	// NO TOKEN!! WHAT GIVES
		 	if( !$token ) 
		 	{
			 	if( current_user_can('administrator') )
			 	{
				 	// NICE ERROR MESSAGE FOR ADMINS
				 	hg_stripe_donate_error( __('A Stripe Token could not be created, please check your logs in your Stripe dashboard', 'hg-stripe-donate')  );
			 	}
			 	else
			 	{
				 	hg_stripe_donate_error( __('There has been a processing error and no transaction was able to take place. Please try again. If the problem persists, please contact us.', 'hg-stripe-donate') );	
			 	}
			}
			
			
			// SETTINGS
			$currency_code 			= get_option( 'hg-stripe-donation-currency', 'USD');
			$success_message 		= get_option( 'hg-stripe-donation-success-message', __('Thanks for your donation!', 'hg-stripe-donate' ) );
			$cc_statement 			= get_option( 'hg-stripe-donation-cc-statement' );

			try 
			{
				$charge = Stripe_Charge::create(array(
					"amount" => $amount * 100,
				  	"currency" => $currency_code,
				  	"card" => $token,
				  	"description" => '',
				  	"statement_descriptor" => $cc_statement
				));
				die($success_message);
			} 
			catch (Stripe_ApiConnectionError $e) 
			{
				$e_json 	= $e->getJsonBody();
				$error 		= $e_json['error'];
				if( $error['message'] )
				{
					hg_stripe_donate_error( $error['message'] );
				}
				else
				{
					hg_stripe_donate_error( __('The payment service cannon be reached. Please try again.', 'hg-stripe-donate') );
				}
			}
			catch (Stripe_InvalidRequestError $e) 
			{
				$e_json 	= $e->getJsonBody();
				$error 		= $e_json['error'];
				if( $error['message'] )
				{
					hg_stripe_donate_error( $error['message'] );
				}
				else
				{
					hg_stripe_donate_error( __('The Stripe payment system has not been setup properly. Please contact the website administrator.', hg-stripe-donate) );
				}
			}
			catch (Stripe_ApiError $e) 
			{
				$e_json 	= $e->getJsonBody();
				$error 		= $e_json['error'];
				if( $error['message'] )
				{
					hg_stripe_donate_error( $error['message'] );
				}
				else
				{
					hg_stripe_donate_error( __('The payment service cannon be reached. Please try again.', hg-stripe-donate) );
				}
			}
			catch(Stripe_CardError $e) 
			{
				$e_json 	= $e->getJsonBody();
				$error 		= $e_json['error'];
				
				if( $error['message'] )
				{
					hg_stripe_donate_error( $error['message'] );
				}
				else
				{
					hg_stripe_donate_error( __('Credit Card Error. Please try again or try another card.', hg-stripe-donate) );
				}
			}
			
			hg_stripe_donate_error( __('Donation system is done at this time, please try again soon', hg-stripe-donate) );
		}	
		else
		{
			hg_stripe_donate_error(  __('Donation system is done at this time, please try again soon', hg-stripe-donate) );
		}
	}
}
