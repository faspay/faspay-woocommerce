<?php
//FunctionKeyGeneratorBCA	
function genKeyId($clearKey){
	return strtoupper(bin2hex(str2bin($clearKey)));
}

function genSignature($klikPayCode, $transactionDate, $transactionNo, $amount, $currency, $keyId){
	
	//Signature Step 1
	$tempKey1 = $klikPayCode . $transactionNo . $currency . $keyId;
	$hashKey1 = getHash($tempKey1);					 
	 
	// Signature Step 2
	$expDate = explode("/",substr($transactionDate,0,10));
	$strDate = intval32bits($expDate[0] . $expDate[1] . $expDate[2]);
	$amt = intval32bits($amount);
	$tempKey2 = $strDate + $amt;
	$hashKey2 = getHash((string)$tempKey2);
						
	// Generate Key Step 3
	$signature = abs($hashKey1 + $hashKey2);
	
	return $signature; 
}
	
function genAuthKey($klikPayCode, $transactionNo, $currency, $transactionDate, $keyId){

	//Step 1 - Padding
	$klikPayCode = str_pad($klikPayCode, 10, "0");
	$transactionNo = str_pad($transactionNo, 18, "A");
	$currency = str_pad($currency, 5, "1");
   
	//Step 2
	$value_1 = $klikPayCode . $transactionNo . $currency . $transactionDate . $keyId;
	
	//Step 3
	$hash_value_1 = strtoupper(md5($value_1));
	
	//Step 4
	if (strlen($keyId) == 32)
		$key = $keyId . substr($keyId,0,16);
	else if (strlen($keyId) == 48)
		$key = $keyId;
	
	// Hex encode the return value
	return strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_3DES, hex2bin($key), hex2bin($hash_value_1), MCRYPT_MODE_ECB))); 
}

function convertHex2bin($data){
	$len = strlen($data);
	return pack("H" . $len, $data);
}
	
function str2bin($data){
	$len = strlen($data);
	return pack("a" . $len, $data);
}
	
function intval32bits($value){
	if ($value > 2147483647)
		$value = ($value - 4294967296);
	else if ($value < -2147483648)
		$value = ($value + 4294967296);
	
	return $value;
}
	
function getHash($value){
	$h = 0;
	for ($i = 0;$i < strlen($value);$i++){
		$h = intval32bits(add31T($h) + ord($value{$i}));
	}
	return $h;
}
	
function add31T($value){
	$result = 0;
	for($i=1;$i <= 31;$i++){
		$result = intval32bits($result + $value);
	}
	
	return $result;
}
//EndFunctionGeneratorBCA

//Connection
include "../../../wp-config.php";


//GetParameterFromBCA
isset($_GET['trx_id']) ?  $trx_id_get = $_GET['trx_id'] : $trx_id_get = null;
isset($_GET['signature']) ?  $signature_get = $_GET['signature'] : $signature_get =null;
isset($_GET['authkey']) ?  $authkey_get = $_GET['authkey'] : $authkey_get = null;

global $wpdb;

//GetClearKey&ClickPayDataFromDB
$active_plugins_data ="";

$sql = ("SELECT ". $wpdb->prefix ."options.option_value FROM ". $wpdb->prefix ."options WHERE ". $wpdb->prefix ."options.option_name = 'woocommerce_faspay_settings' ");		 
$opt = $wpdb->get_results($sql, ARRAY_A);

foreach($opt as $rows) {
	$active_plugins_data .= $rows["option_value"];
}

$array = unserialize($active_plugins_data);
$klik_pay_code	= $array["klik_pay_code"];
$clear_key		= $array["clear_key"];

//GetTransactionDate&TotalAmountFromDB
$wp_faspay = $wpdb->get_results("SELECT * from ". $wpdb->prefix ."faspay_order WHERE trx_id = '$trx_id_get'", ARRAY_A);
foreach($wp_faspay as $data){
	$id_transaksi	= $data['trx_id'];
	$trx_date		= $data['date_trx'];
	$total_amount	= $data['total_amount']/100.00;
}

//GetArrayVariabel
$clearKey			= $clear_key;
$klikPayCode		= $klik_pay_code;
$transactionNo		= $trx_id_get;
$transactionDate	= date("d/m/Y H:i:s", strtotime($trx_date));
$totalAmount		= $total_amount;
$currency			= "IDR";

//ConditionCheckSiganture&AuthkeyFromBCA
if($signature_get == ''){
															
	$keyId = genKeyId($clearKey);
	$authKey = genAuthKey($klikPayCode,$transactionNo,$currency,$transactionDate,$keyId);
			
	if($authKey == $authkey_get){
		echo "1";
	}else{
		echo "0";
	}	
	
	
}else{
	
	$keyId = genKeyId($clearKey);
	$signature_new = genSignature($klikPayCode, $transactionDate, $transactionNo, $totalAmount, $currency, $keyId);	
	if($signature_new == $signature_get){
		echo "1";
	}else{
		echo "0";
	}	
	

}//EndConditionCheckSignature&AuthkeyFromBCA

?>