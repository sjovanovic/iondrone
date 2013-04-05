<?php 
/**
 * Convert p12 to PEM
 * openssl pkcs12 -clcerts -nokeys -in mojcert.p12 -out usercert.pem
 * openssl pkcs12 -nocerts -in mojcert.p12 -out userkey.pem
 * $cert = array(
		'cert'=>'../application/data/usercert.pem',
		'key'=>'../application/data/userkey.pem',
		'pass'=>'keyphrase'
	)
 */
$curl_info = false;
function cget($url, $cert=false, $header=false, $cookies=false){
	$cu = curl_init();
	curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($cu, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($cu, CURLOPT_TIMEOUT, 20);
	curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($cu, CURLOPT_SSL_VERIFYHOST,  0);
	curl_setopt($cu, CURLOPT_URL, $url);
	if($header && is_array($header)){
		curl_setopt($cu, CURLOPT_HTTPHEADER, $header); 
	}
	if($cookies){
		curl_setopt($cu, CURLOPT_COOKIE, $cookies); // name=val; name2=val2
	}
	if($cert){
		curl_setopt($cu, CURLOPT_SSLKEYTYPE, 'PEM');
		if(isset($cert['cert'])){curl_setopt($cu, CURLOPT_SSLCERT, $cert['cert']);}
		if(isset($cert['key'])){curl_setopt($cu, CURLOPT_SSLKEY, $cert['key']);}
		if(isset($cert['info'])){curl_setopt($cu, CURLOPT_CAINFO, $cert['info']);} 
		if(isset($cert['pass'])){curl_setopt ($cu, CURLOPT_SSLCERTPASSWD, $cert['pass']);}
	}
	$contents = curl_exec($cu);
	$curl_info = curl_getinfo($cu);
	if(isset($_GET['beatle'])){
		print_r(curl_getinfo($cu)); 
		echo '<br/>';
		echo curl_error($cu).'<br/>';
	}
	curl_close($cu);
	return $contents;
}
function cput($url, $fields, $headers=false){
   $fields = (is_array($fields)) ? http_build_query($fields) : $fields;
   if($ch = curl_init($url)){
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  if($headers){
	  	$headers[] = 'Content-Length: ' . strlen($fields);
	  }else{
	  	$headers = array('Content-Length: ' . strlen($fields));
	  }
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      $res = curl_exec($ch);
      //$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      //return (int) $status;
      return $res;
   }else{
      return false;
   }
}
function cpost($url, $fields){
	$ch = curl_init($url);
	if(is_array($fields)){
		$fields_string = '';
		foreach($fields as $key=>$value) { $fields_string  .= $key.'='.$value.'&'; } 
		rtrim($fields_string,'&');
	}else{
		$fields_string = $fields;
	}
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
	curl_setopt($ch, CURLOPT_HEADER ,0);  // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
	$res = curl_exec($ch);
	$curl_info = curl_getinfo($ch);
	if(isset($_GET['beatle'])){
		print_r($fields);
		print_r(curl_getinfo($ch)); 
		echo '<br/>';
		echo curl_error($ch).'<br/>';
	}
	curl_close($ch);
	return $res;
}
?>