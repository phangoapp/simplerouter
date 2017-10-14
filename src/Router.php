<?php

namespace PhangoApp\SimpleRouter;

class Router {


	/**
	* Php document root
	*/
	
	public $base_path=__DIR__;
    
    public $controller;

    public function __construct($controller_path) 
    {
        
        include($controller_path);
        
        if(is_file($controller_path))
		{
            
            
            
        }
    
    }

}

?>
