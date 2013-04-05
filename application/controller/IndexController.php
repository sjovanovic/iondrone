<?php

class IndexController extends Controller {
	function IndexAction(){
		require_once('GithubApi.php');
		$this->api = new GithubApi($this->conf['github_client_id'], $this->conf['github_secret']);
		$this->render('index');
	}
}

?>
