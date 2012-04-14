<?php

//Debug info
if (isset($_GET['beatle'])) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}
// get the configuration
require_once('../application/configuration/main.php');
require_once($conf['path']['mvc_root'].$conf['path']['library']."/Mvc.php");
$mvc = new Mvc($conf);

?>
