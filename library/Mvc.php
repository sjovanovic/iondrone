<?php
class Mvc{
	function Mvc($conf){
		$this->www_root = str_replace("index.php", "", strtolower($_SERVER['PHP_SELF']));
		$justpars = str_replace($this->www_root, "", $_SERVER['REDIRECT_URL']);
		if (strpos($_SERVER['REDIRECT_URL'], $this->www_root) !== false){
			$justpars = substr($_SERVER['REDIRECT_URL'], strlen($this->www_root));
        }
		$slsh = substr($justpars, 0, 1);
		if ($slsh == '/'){
			$this->params =	explode('/', substr($justpars, 1));
		}else{
			$this->params =	explode('/', $justpars);
		}
		if (count($this->params) == 1 && $this->params[0] == ''){
			$this->params = array();
		}
		if (isset($_GET['beatle'])){
			print_r($this->params);
		}
		//$this->params =	explode('/', substr($_SERVER['REDIRECT_URL'], 1));
		$this->conf = $conf;
		foreach ($conf['path'] as $name => $path){
			if ($name != "mvc_root"){
				set_include_path($conf['path']['mvc_root'].$path . PATH_SEPARATOR . get_include_path());
			}
		}
		$params = array();
		$params['params'] = $this->params;
		$params['conf'] = $this->conf;
		$params['www_root'] = $this->www_root;
		require_once("Controller.php");
		if (isset($this->conf)){
			$controller_path = $this->conf['path']['mvc_root'].$this->conf['path']['controller']."/".ucfirst(strtolower($this->params[0]))."Controller.php";
			if (is_file($controller_path)){
				require_once($controller_path);
				$cls = ucfirst(strtolower($this->params[0]))."Controller";
				$cinst = new $cls($params);
				$action = @ucfirst(strtolower($this->params[1]))."Action";
				if (isset($this->params[1]) && method_exists($cinst, $action)){
					$cinst->$action();
				}elseif(method_exists($cinst, "IndexAction")){
					$action = "IndexAction";
					$cinst->$action();
				}else{
					require_once($this->conf['path']['mvc_root'].$this->conf['path']['controller']."/IndexController.php");
					$cls = "IndexController";
					$action = "IndexAction";
					$cinst = new $cls($params);
					$cinst->$action();
				}
			}else{
				require_once($this->conf['path']['mvc_root'].$this->conf['path']['controller']."/IndexController.php");
				$cls = "IndexController";
				$cinst = new $cls($params);
				$action = ucfirst(strtolower($this->params[0]))."Action";
				if (isset($this->params[0]) && method_exists($cinst, $action)){
					$cinst->$action();
				}else{
					$action = "IndexAction";
					$cinst->$action();
				}
			}
		}
	}
}

?>
