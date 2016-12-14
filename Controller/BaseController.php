<?php 

/**
* 所有控制器的父类
*/
class BaseController
{
	/*
	 * 加载视图文件
	 * viewName 视图名称
	 * viewData 视图分配数据
	*/
	public function loadView($viewName ='', $viewData = [])
	{
		$viewPath = BASE_PATH . "/View/{$viewName}.php";
		// D($viewPath);
		// D($viewName);
		// D($data);
		if(file_exists($viewPath))
		{
			extract($viewData);
			include $viewPath;
		}
	}
	
}