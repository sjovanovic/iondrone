<?php
$conf = array(
	'dev'=>array(
		'github_client_id' => 'afadb6b38e237828a7ee',
		'github_secret'=>'4d1c6ec603bc62753c4624c230d77c268e52a6a6',
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
		'github_client_id' => 'afadb6b38e237828a7ee',
		'github_secret'=>'4d1c6ec603bc62753c4624c230d77c268e52a6a6',
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
		'github_client_id' => 'afadb6b38e237828a7ee',
		'github_secret'=>'4d1c6ec603bc62753c4624c230d77c268e52a6a6',
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
