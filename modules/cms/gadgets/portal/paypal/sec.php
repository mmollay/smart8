<?php
include ('../config.inc.php');
include ('paypal_function.inc');

/**
 * SetExpressCheckout NVP example; last modified 08MAY23.
 *
 * Initiate an Express Checkout transaction.
 */

$_POST ['paymentAmount'] = round ( $_SESSION ['summery'], 2 );
$_POST ['currency'] = 'EUR';

// Set request-specific fields.
$paymentAmount = urlencode ( $_POST ['paymentAmount'] );
$currencyID = urlencode ( $_POST ['currency'] ); // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
$paymentType = urlencode ( 'Sale' ); // 'Authorization' or 'Sale' or 'Order'

$host = $_SERVER ['HTTP_HOST'];
// $host = 'localhost/ssi_center/ssi_constructor';

// '------------------------------------
// ' The returnURL is the location where buyers return to when a
// ' payment has been succesfully authorized.
// '
// ' This is set to the value entered on the Integration Assistant
// '------------------------------------
$returnURL = "http://$host/$relative_path" . "sites/paypal_success.php";

// '------------------------------------
// ' The cancelURL is the location buyers are sent to when they hit the
// ' cancel button during authorization of payment during the PayPal flow
// '
// ' This is set to the value entered on the Integration Assistant
// '------------------------------------
$cancelURL = "http://$host/$relative_path" . "sites/paypal_cancel.php";

/*
 * Vinzenz
 */
// $GLOBALS['API_UserName'] = urlencode('master_1232716757_biz_api1.hotmail.com');
// $GLOBALS['API_Password'] = urlencode('1232716775');
// $GLOBALS['API_Signature'] = urlencode('ARwY3tSdn01m-Wbu5FJTLWwSRNZRARmyoEhX6iaKJ6cIKO0knKrBOUrK');

/*
 * Error - handling
 * https://www.paypalobjects.com/de_DE/html/IntegrationCenter/ic_api-errors.html
 */

/**
 * Send HTTP POST Request
 *
 * @param
 *        	string The API method name
 * @param
 *        	string The POST Message fields in &name=value pair format
 * @return array HTTP Response body
 */

include ('../config.inc.php');
// Call data from Cart
if ($_SESSION ['cart']) {
	// Paypal count starts at one instead of zero
	$count = 0;
	
	foreach ( $_SESSION ['cart'] as $cart_id ) {
		if ($cart_id) {
			$query = $GLOBALS['mysqli']->query ( "SELECT art_title,netto FROM article_temp where temp_id='$cart_id'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
			$array = mysqli_fetch_array ( $query );
			$netto = round ( $array ['netto'], 2 );
			$nvpStr2 .= "&L_PAYMENTREQUEST_0_NAME{$count}={$array['art_title']}" . "&L_PAYMENTREQUEST_0_NUMBER{$count}={$cart_id}" . 
			// "&L_PAYMENTREQUEST_0_DESC0=The+description+of+product+1".
			"&L_PAYMENTREQUEST_0_AMT{$count}={$netto}" . "&L_PAYMENTREQUEST_0_QTY{$count}=1";
			// Increment the counter
			++ $count;
		}
	}
}

// Add request-specific fields to the request string.
$nvpStr = urldecode ( "&ReturnUrl=$returnURL" . "&CANCELURL=$cancelURL" . "&PAYMENTACTION=$paymentType" . "&PAYMENTREQUEST_0_PAYMENTACTION=Sale" . "&PAYMENTREQUEST_0_AMT=$paymentAmount" . 
// "&PAYMENTREQUEST_0_ITEMAMT=15.00".
// "&PAYMENTREQUEST_0_TAXAMT=5.00".
// "&PAYMENTREQUEST_0_SHIPPINGAMT=1.00".
// "&PAYMENTREQUEST_0_HANDLINGAMT=1.00".
// "&PAYMENTREQUEST_0_INSURANCEAMT=1.00".
"&PAYMENTREQUEST_0_CURRENCYCODE=$currencyID" . $nvpStr2 . "&ALLOWNOTE=1" . "&NOSHIPPING=1" );

// Execute the API operation; see the PPHttpPost function above.
$httpParsedResponseAr = PPHttpPost ( 'SetExpressCheckout', $nvpStr );

if ("Success" == $httpParsedResponseAr ["ACK"]) {
	
	// Redirect to paypal.com.
	$token = urldecode ( $httpParsedResponseAr ["TOKEN"] );
	$payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
	if ("sandbox" === $GLOBALS ['environment']) {
		$payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token";
	}
	// echo "<iframe src='$payPalURL' width='100%' height='400' marginheight='0' marginwidth='0' frameborder='0'></iframe>";
	echo $payPalURL;
	// header("Location: $payPalURL");
	exit ();
} else {
	exit ( 'SetExpressCheckout failed: ' . print_r ( $httpParsedResponseAr, true ) );
}
?>