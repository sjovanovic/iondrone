<?php
class Controller
{

    function Controller()
    {
        if (func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            $params = func_get_arg(0);
            if (isset($params['conf']))
                $this->__init($params);
        }
    }

    function __init($params)
    {
        foreach ($params as $param_name => $param_value)
            $this->$param_name = $param_value;
    }

    function render($template = false, $return=false){
    	if($return){
    		$out = '';
    		ob_start();
    	}
        if (!$template){
            require_once($this->conf['path']['mvc_root']."/".$this->conf['path']['view']."/index.php");
        }else{
            require_once($this->conf['path']['mvc_root']."/".$this->conf['path']['view']."/".strtolower($template).".php");
		}
		if($return){
    		$out = ob_get_contents();
			ob_end_clean();
			return $out;
    	}
    }
	function protect(){
		
	}
	function logoutUrl(){
		
	}
}
?>
