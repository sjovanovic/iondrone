<?php
$conf = array(
	'dev'=>array(
      'github_client_id' => '', // add your github client id here
      'github_secret'=>'', // add your github secret here
		'path' => array(
			'mvc_root'=> '../',
			'model' => 'application/model',
			'view' => 'application/view',
			'data' => 'application/data',
			'controller'=> 'application/controller',
			'library' => 'library',
			'configuration' => 'application/configuration'
		),
		'site'=>isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https://'.$_SERVER['SERVER_NAME']:'http://'.$_SERVER['SERVER_NAME']
	),
	'beta'=>array(
		'github_client_id' => '',
		'github_secret'=>'',
		'path' => array(
			'mvc_root'=> '../',
			'model' => 'application/model',
			'view' => 'application/view',
			'data' => 'application/data',
			'controller'=> 'application/controller',
			'library' => 'library',
			'configuration' => 'application/configuration'
		),
		'site'=>isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https://'.$_SERVER['SERVER_NAME']:'http://'.$_SERVER['SERVER_NAME']
	),
	'production'=>array(
		'github_client_id' => '',
		'github_secret'=>'',
		'path' => array(
			'mvc_root'=> '../',
			'model' => 'application/model',
			'view' => 'application/view',
			'data' => 'application/data',
			'controller'=> 'application/controller',
			'library' => 'library',
			'configuration' => 'application/configuration'
		),
		'site'=>isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https://'.$_SERVER['SERVER_NAME']:'http://'.$_SERVER['SERVER_NAME']
	)
);
if (is_file('/var/iondrone_production')){
	$conf = $conf['production'];
}elseif(is_file('/var/iondrone_beta')){
	$conf = $conf['beta'];
}else{
	$conf = $conf['dev'];
}
?>
