<?php

namespace PhangoApp\SimpleRouter;

use PhangoApp\PhaUtils\Utils;

/**
* A class used for create routes derived from php simple files.
*
* You put a router in a .php file, for example, blog.php file, with a router instance, this route translate the url for send to a controller.
*
* The format is http://localhost/index.php{/method}/get/var1/var2/var3?query=1&query=2 
* For url_rewrite:
* http://localhost/index.php/method http://localhost/method
* http://localhost/router.php/method http://localhost/router/method
* http://localhost/folder/router.php/method http://localhost/folder/router/method
* router variable = php file
* method variable = controller file
*/


class Router {


	/**
	* Php document root
	*/
	
	public $base_path;
    
    public $controller;
    
    public $basename;
    
    public $request_method;
    
    public $controller_path;
    
    public $real_path;
    
    public $on_rewrite=false;

    public function __construct($name, $controller_path) 
    {
        
        //Get php filename
        
        $this->request_method=$_SERVER['REQUEST_METHOD']; 
        
        $arr_path=explode('/public', getcwd());
        
        $this->base_path=$arr_path[0].'/vendor/';
        
        $this->basename=basename($name);
        
        $this->controller_path=$controller_path;

        $this->module_path=$this->base_path.$name;
 
        $this->real_path=$arr_path[0];
        
        $this->extended_path=dirname($_SERVER['SCRIPT_NAME']);
 
    }
    
    public function run()
    {
        
        ob_start();
        
        if(is_file($this->base_path.$this->controller_path.'.php'))
		{
            
            include($this->base_path.$this->controller_path.'.php');
            
            //Get routes from url
			
			$controller_name=ucfirst($this->basename).'Controller';
            
            $controller_class=new $controller_name($this, $this->basename);
            
            //Get method
            
            $method='index';
            
            $parameters=[];
            
            if(isset($_SERVER['PATH_INFO']))
            {
                
                //First is method
                
                //Get is /get/variable1/variable2

                $arr_info=explode('/get/', $_SERVER['PATH_INFO']); 
                
                if($arr_info[0]!=='')
                {
                
                    $method=basename(Utils::slugify($arr_info[0]));
                    
                }

                if(count($arr_info)>1)
                {
                
                    $parameters=explode('/', $arr_info[1]);
                    
                }
                
            }
            
            if(method_exists($controller_class, $method)) 
            {
            
            
                //Check if exists method. 
                
                $p=new \ReflectionMethod($controller_name, $method); 
                            
                $num_parameters=$p->getNumberOfRequiredParameters();
                
                $num_parameters_total=count($p->getParameters());
                
                $object_parameters=$p->getParameters();

                $c_param=count($parameters);
                
                if($c_param<=$num_parameters_total && $c_param>=$num_parameters && $method!=='__construct' && $p->isPublic())
                {
                    
                    for($x=0;$x<$c_param;$x++)
                    {
                        
                        $type=$object_parameters[$x]->getType();
                        
                        settype($parameters[$x], $type);
                        
                    }
                
                    //Check in mod_rewrite
                    
                    if(!call_user_func_array(array($controller_class, $method), $parameters)===false)
                    {
                        
                        throw new \Exception('Not exists method in this controller');

                    }
                    else
                    {
                        
                        //Execute post tasks
                        

                    }

                    
                }
                else
                {
                    
                    $this->response404();

                }

            }
            else
            {
                
                $this->response404();
                
            }
            
        }
        
        ob_end_flush();
        
    }

	/**
	* If response fail, you can use this for response 404 page.
	*
	*/
	
	public function response404()
	{
	
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
		
		/*$url404=$this->make_url($this->default_404['controller'], $this->default_404['method'], $this->default_404['values']);
		
		//Use views for this thing.
		
		if(!$this->response($url404, 0))
		{*/
		
		//use a view
		
        echo 'Error: page not found...';
			
		//}
		
		die;
		
		//$this->response($url404);
	}

    /**
    * Simple method for make an url
    *
    * @param $url_path string The url path of the url
    * @param $controller_query An array from the variables of controller method
    * @param $query An array from create variables of a typical url Example. ?op=1&arg1=string
    * @param $base_file If you are creating a url of other router file you can use this variable
    */
    
    public function make_url($url_path, $controller_query=[], $query=[], $base_file='')
    {
        
        $final_url='';

        if(!$base_file)
        {
            
            $base_file=basename($_SERVER['SCRIPT_NAME']);
            
        }
        
        if(!$this->on_rewrite)
        {
            
            $final_url=$this->extended_path.'/'.$base_file.'/'.$url_path.'/';
            
        }
        else
        {

            $base_file=str_replace('.php', '', $base_file).'/';

            if($base_file=='index/') {
            
                $base_file='';
            }
            
            $final_url=$this->extended_path.'/'.$base_file.$url_path.'/';
            
        }
            
        if(count($controller_query)>0)
        {
        
            $final_url.='get/'.implode('/', $controller_query);
            
        }

        if(count($query)>0)
        {
        
            $final_url.='?'.http_build_query($query);
            
        }
        
        return $final_url;
        
    }

}

?>
