<?php
class GithubApi{
	function GithubApi($client_id, $secret, $scope='user,repo,gist'){
		session_start();
		$this->authUri = 'https://github.com/login/oauth/authorize';
		$this->accessTokenUri = 'https://github.com/login/oauth/access_token';
		$this->apiHost = 'https://api.github.com';
		$this->client_id = $client_id;
		$this->secret = $secret;
		if(isset($_GET['code']) && $_GET['code']){
			$this->getAccessToken($_GET['code']);
		}elseif($this->isLogedIn()){
		}else{
			$this->login();
		}
	}
	function login(){
		header('Location: '.$this->authUri.'?client_id='.$this->client_id.'&scope='.$scope);
	}
	function logout(){
		$_SESSION = array();
		session_destroy();
	}
	function getAccessToken($code){
		$res = $this->cpost($this->accessTokenUri, array(
			"client_id"=>$this->client_id,
			"client_secret"=>$this->secret,
			"code"=>$code
		));
		parse_str($res, $out);
		if(isset($out['access_token']) && $out['access_token']){
			$_SESSION['access_token'] = $out['access_token'];
		}else{
			$this->logout();
		}
	}
	function isLogedIn(){
		if(isset($_SESSION['access_token']) && $_SESSION['access_token']){
			return $_SESSION['access_token'];
		}else{
			return false;
		}
	}
	function access_token(){
		return $this->isLogedIn();
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
	function userData(){
		if($this->isLogedIn()){
			if(isset($_SESSION['user_json'])){
				return $_SESSION['user_json'];
			}else{
				$url = $this->apiHost.'/user?access_token='.$this->access_token();
				$json = $this->cget($url);
				$user = json_decode($json, true);
				$_SESSION['user_json'] = $json;
				$_SESSION['user_name'] = $user['login'];
				$_SESSION['user_home'] = $user['html_url'];
				$_SESSION['user_avatar'] = $user['avatar_url'];
				$_SESSION['user_url'] = $user['url'];
				$_SESSION['user_id'] = $user['id'];
				return $json;
			}
		}
	}
	function getCall($method){
		
	}
	function postCall(){
		
	}
}
?>
