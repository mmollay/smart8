<?php

/** GetExpressCheckoutDetails NVP example; last modified 08MAY23.
 *
 *  Get information about an Express Checkout transaction.
 */
include ('../config.inc.php');
include ('paypal_function.inc');

/**
 * Send HTTP POST Request
 *
 * @param
 *        	string The API method name
 * @param
 *        	string The POST Message fields in &name=value pair format
 * @return array HTTP Response body
 */

/**
 * This example assumes that this is the return URL in the SetExpressCheckout API call.
 * The PayPal website redirects the user to this page with a token.
 */

// Obtain the token from PayPal.
if (! array_key_exists ( 'token', $_REQUEST )) {
	exit ( 'Token is not received.' );
}

// Set request-specific fields.
$token = urlencode ( htmlspecialchars ( $GLOBALS ['token'] ) );

// Add request-specific fields to the request string.
$nvpStr = "&TOKEN=$token";

// Execute the API operation; see the PPHttpPost function above.
$httpParsedResponseAr = PPHttpPost ( 'GetExpressCheckoutDetails', $nvpStr );

if ("Success" == $httpParsedResponseAr ["ACK"]) {
	// Extract the response details.
	$payerID = $httpParsedResponseAr ['PAYERID'];
	$street1 = $httpParsedResponseAr ["SHIPTOSTREET"];
	if (array_key_exists ( "SHIPTOSTREET2", $httpParsedResponseAr )) {
		$street2 = $httpParsedResponseAr ["SHIPTOSTREET2"];
	}
	$city_name = $httpParsedResponseAr ["SHIPTOCITY"];
	$state_province = $httpParsedResponseAr ["SHIPTOSTATE"];
	$postal_code = $httpParsedResponseAr ["SHIPTOZIP"];
	$country_code = $httpParsedResponseAr ["SHIPTOCOUNTRYCODE"];
	
	$paymentAmount = urldecode ( $httpParsedResponseAr ["PAYMENTREQUEST_0_AMT"] );
	// $paymentAmount = $httpParsedResponseAr['paymentAmount'];
	// $currencyID = $httpParsedResponseAr['currencyID'];
	
	$GLOBALS ['firstname'] = urldecode ( $httpParsedResponseAr ['FIRSTNAME'] );
	$GLOBALS ['secondname'] = urldecode ( $httpParsedResponseAr ['LASTNAME'] );
	$GLOBALS ['strasse'] = urldecode ( $httpParsedResponseAr ["SHIPTOSTREET"] );
	$GLOBALS ['plz'] = urldecode ( $httpParsedResponseAr ["SHIPTOZIP"] );
	$GLOBALS ['stadt'] = urldecode ( $httpParsedResponseAr ['SHIPTOCITY'] );
	$GLOBALS ['email'] = urldecode ( $httpParsedResponseAr ['EMAIL'] );
	// $GLOBALS['currencyID'] = $currencyID;
	// $GLOBALS['paymentAmount'] = $paymentAmount;
	
	// exit('Get Express Checkout Details Completed Successfully: '.print_r($httpParsedResponseAr, true));
} else {
	exit ( 'GetExpressCheckoutDetails failed: ' . print_r ( $httpParsedResponseAr, true ) );
}
?>