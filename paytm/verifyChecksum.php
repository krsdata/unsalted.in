<?php
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

// following files need to be included
require_once("./lib/config_paytm.php");
require_once("./lib/encdec_paytm.php");

$paytmChecksum = "";
$paramList = array();
$isValidChecksum = FALSE;

$paramList = $_POST;// Array having paytm response

$paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg

$isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.

print_r($paramList);
echo "CHECKSUMVERIFED".$isValidChecksum;

 // if checksum is validated Kindly verify the amount and status 
 // if transaction is successful 
 // kindly call Paytm Transaction Status API and verify the transaction amount and status.
 // If everything is fine then mark that transaction as successful into your DB.			

?>
