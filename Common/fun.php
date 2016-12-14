<?php  

// 工具函数


// 调试函数
if(!file_exists('D'))
{
	function D()
	{
		echo '<pre>';
		print_r(func_get_args());
		echo '</pre>';
	}
}

// 注册自动加载
if(!file_exists('user_aotu_load'))
{
	function user_aotu_load($className)
	{
		$classPath = 'Lib';
		// var_dump(strrpos($className, 'Controller') );
		if(strrpos($className, 'Controller') !== FALSE )
		{
			$classPath = 'Controller';
		}
		else if(strrpos($className, 'Model') !== FALSE )
		{
			$classPath = 'Model';
		}
		// D($classPath);
		$classPath = BASE_PATH . "/{$classPath}/{$className}.php";
		// D($className);
		// D($classPath);
		// var_dump(file_exists($classPath));
		if(file_exists($classPath))
		{
			include $classPath;
		}
		// exit;
	}
	spl_autoload_register('user_aotu_load');
}



// D($_REQUEST);
// 路由控制跳转至控制器 
if(!empty($_REQUEST['r']))
{
	// r=user/list
	$r = explode('/', $_REQUEST['r']);
	list($controller,$action) = $r;
	$controller = "{$controller}Controller"; // UserController
	$action = "action{$action}"; //actionList
	
	// D($controller);
	// D($action);

	if(class_exists($controller))
	{
		if(method_exists($controller,$action))
		{
			// 回调这个函数
			$data = call_user_func(array( (new $controller), $action));   
		}
		else
		{
			die("{$controller}类不存在此函数{$action}");
		}
	}
	else
	{
		die("{$controller}类不存在");
	}

	// $obj = new $controller();
	// if(method_exists($obj, $action))
	// {
	// 	$obj->$action();
	// }

	// $controllerObj = new UserController();
	// $data = $controllerObj->actionlist();



}	
