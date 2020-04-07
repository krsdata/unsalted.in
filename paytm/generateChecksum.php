<?php

//use Redirect;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With, auth-token');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Origin: *");

// following files need to be included
require_once("./lib/config_paytm.php");
require_once("./lib/encdec_paytm.php");
header("Content-Type: application/json");
define('PAYTM_MERCHANT_KEY', 'jWBrK0hmx%Ok7rTd'); 
$_POST = json_decode(file_get_contents('php://input'), true);

$checkSum = "";
//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
// below code snippet is mandatory, so that no one can use your checksumgeneration url for other purpose .
$findme   = 'REFUND';
$findmepipe = '|'; 
$paramList = array();

//$paramList["MID"] = 'qGajnK26062442947748';
//$paramList["INDUSTRY_TYPE_ID"] = 'Retail'; 
//$paramList["CHANNEL_ID"] = 'WAP';  
//$paramList["WEBSITE"] = 'DEFAULT';



$_POST = json_decode(file_get_contents('php://input'), true);

if(isset($_POST['ORDER_ID']) and empty($_POST['ORDER_ID']) OR !isset($_POST['ORDER_ID'])){

	 echo json_encode(array("ORDER_ID" => 'Order is required to generate checksum'));
	 exit();

} 

if($_POST){
          $paramList['MID'] =$_POST['MID'];
 $paramList['INDUSTRY_TYPE_ID'] =$_POST['INDUSTRY_TYPE_ID'];
 $paramList['CHANNEL_ID'] =$_POST['CHANNEL_ID'];
 $paramList['WEBSITE'] =$_POST['WEBSITE'];
	 $paramList['ORDER_ID'] =$_POST['ORDER_ID'];
         $paramList['CALLBACK_URL'] =$_POST['CALLBACK_URL'];
         $paramList['CUST_ID'] =$_POST['CUST_ID'];
         $paramList['EMAIL'] =$_POST['EMAIL'];
         $paramList['MOBILE_NO'] =$_POST['MOBILE_NO'];
          $paramList['TXN_AMOUNT'] =$_POST['TXN_AMOUNT']; 
     
  //  print_r($paramList);
}

  
//Here checksum string will return by getChecksumFromArray() function.
$checkSum = getChecksumFromArray($paramList,"jWBrK0hmx%Ok7rTd");
//print_r($_POST);
 echo json_encode(array("CHECKSUMHASH" => $checkSum,"order_id" => $_POST["ORDER_ID"], "status" => "1"));
 
 
?>
