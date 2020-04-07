<?php
/**
* import checksum generation utility
* You can get this utility from https://developer.paytm.com/docs/checksum/
*/
require_once("./lib/encdec_paytm.php");
/* initialize an array with request parameters */
$paytmParams = array(
    
	/* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
	"MID" => "GfQMJs53038044110630",
    
	/* Find your WEBSITE in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
	"WEBSITE" => "DEFAULT",
    
	/* Find your INDUSTRY_TYPE_ID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
	"INDUSTRY_TYPE_ID" => "Retail",
    
	/* WEB for website and WAP for Mobile-websites or App */
	"CHANNEL_ID" => "WAP",
    
	/* Enter your unique order id */
	"ORDER_ID" => "876564",
    
	/* unique id that belongs to your customer */
	"CUST_ID" => "CUST_103",
    
	/* customer's mobile number */
	"MOBILE_NO" => "8828002531",
    
	/* customer's email */
	"EMAIL" => "radhadevitech@gmail.com",
    
	/**
	* Amount in INR that is payble by customer
	* this should be numeric with optionally having two decimal points
	*/
	"TXN_AMOUNT" => "164.00",
    
	/* on completion of transaction, we will send you the response on this URL */
	"CALLBACK_URL" => "https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=876564",
);

/**
* Generate checksum for parameters we have
* Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
*/
$checksum = getChecksumFromArray($paytmParams, "GfQMJs53038044110630");

/* for Staging */
//$url = "https://securegw-stage.paytm.in/order/process";

/* for Production */
$url = "https://securegw.paytm.in/order/process";

/* Prepare HTML Form and Submit to Paytm */
?>
<html>
	<head>
		<title>Merchant Checkout Page</title>
	</head>
	<body>
		<center><h1>Please do not refresh this page...</h1></center>
		<form method='post' action='<?php echo $url; ?>' name='paytm_form'>
				<?php
					foreach($paytmParams as $name => $value) {
						echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
					}
				?>
				<input type="hidden" name="CHECKSUMHASH" value="<?php echo $checksum ?>">
		</form>
		<script type="text/javascript">
			document.paytm_form.submit();
		</script>
	</body>
</html>