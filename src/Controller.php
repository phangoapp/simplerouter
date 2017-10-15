<?php

namespace PhangoApp\SimpleRouter;

/**
* Very simple class for create controllers. 
*
* The __construct method obtain the father route for access to methods how functions for make controller urls.
*/

class Controller {

	public $route;
	public $name="";

	public function __construct($route, $name, $yes_view=1)
	{

		$this->route=$route;
		$this->name=$name;
		//Here define the twig template
		$this->twig='';
		
		if($yes_view==1)
		{
		
            $loader=new \Twig_Loader_Filesystem([$this->route->module_path.'/templates', $this->route->real_path.'/themes']);
 
            $this->twig=new \Twig_Environment($loader, array(
                'cache' => $this->route->real_path.'/tmp/cache',
            ));
 
		}
        
        //Load config

	}

}

