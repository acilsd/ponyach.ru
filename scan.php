<?php
$request = array(
'REQUEST' => $_REQUEST,
'GET' => $_GET,
'POST' => $_POST,
'COOKIE' => $_COOKIE
);

function detect_risk($key,$value){
	$risk_r = '/[^\w\s\/@!?\.]+|(?:\.\/)|(?:@@\w+)' . '|(?:\+ADw)|(?:union\s+select)/i';
	if (preg_match($risk_r,$value)) {detect_type($value);} 
	elseif (preg_match($risk_r,$key)) {detect_type($key);}
}
//Recursive interation till array become a scan-ready string
function run_scan($request){
	if(!empty($request)){
		foreach($request as $key => $value){
			interate_array($key,$value);
			}
		}
	}
//Interation function to detect is it scan-ready string
function interate_array($key,$value)  

{
if(!is_array($value) && is_string($value))
	{detect_risk($key,$value);}


else {
	foreach($value as $sKey => $sValue)
	{
	 interate_array($key.'.'.$sKey,$sValue);
	}
  }
}
//scan
function scan($ptrn,$value){
	$ptrn = ("'".$ptrn."'");
	if (preg_match($ptrn,$value)){
		return 1;
	}
else return 0;
}

//detect_type_of_attack
function detect_type($risk){
$xml = simplexml_load_file("/var/www/ponyach/data/www/ponyach.ru/file.xml"); 
  foreach ($xml->filter as $filter) 
  	{$detection = scan($filter->rule,$risk);
     if ($detection === 1) {
     	//On detection attack -- Set type of action
$buff = "Date :".date("l dS of F Y h:i:s A")."\r\n 
IP From Header:".$_SERVER['REMOTE_ADDR']."\r\n
RISK-Query-Detected:".$risk."\r\n
DescriptionOfVuln:".$filter->description."\r\n
Query-string: ".$_SERVER['QUERY_STRING']. "\r\n
IP From HTTP-Client:".$_SERVER['HTTP_CLIENT_IP']."\r\n
From where routed:".$_SERVER['HTTP_X_FORWARDED_FOR']."\r\n
UserAgent:".$_SERVER['HTTP_USER_AGENT']."\r\n
Ref:".$_SERVER['HTTP_REFERER']."\r\n
Lang:".$_SERVER['HTTP_ACCEPT_LANGUAGE']."\r\n
Session:".$_SERVER['PHP_AUTH_USER']."\r\n
Remote client port:".$_SERVER['REMOTE_PORT']."\r\n
Protocol:".$_SERVER['SERVER_PROTOCOL']."\r\n
HOST-header : " .$_SERVER['HTTP_HOST']."\r\n
____________________________|END|____________________________\r\n ";

 

 

 
 $fp = fopen("/var/www/ponyach/data/www/ponyach.ru/pony-logs.txt", "a+");


fwrite($fp,$buff);
fclose($fp);


} 
 }


//detect risks

}
run_scan($request);
?>
