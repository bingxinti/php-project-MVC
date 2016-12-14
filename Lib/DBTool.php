<?php 


// 单例设计模式，只连接一次数据库
class DBConnect 
{
    private static $_instance = null;
    //私有构造函数，防止外界实例化对象
    // private function __construct() {}
    //私有克隆函数，防止外办克隆对象
    // private function __clone() {}


    //静态方法，单例统一访问入口
    public static function getInstance() 
    {
       if(is_null(self::$_instance))
		{
			// D('tableName');
			self::$_instance = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE, DB_PORT );
		}
		return self::$_instance;
    }
}


class DBTool
{
	// 成员属性
	private $sqlCache;
	private $tableName;
	private $connect;
	private $tableColumn;

	// 初始化对象
	public function __construct( $tableName = '')
	{
		// 连接数据库
		$this->tableName = $tableName;
		$this->connect = DBConnect::getInstance();
		mysqli_set_charset($this->connect, CHARSET); 
		$this->tableColumn = $this->getTableColumn($this->tableName);
		$this->sqlCache = [];
	}

	// 获取总数
	public function getCountTotal($params = [])
	{
		$countTotal = 0;
		$params['fields'] = 'count(*) as countTotal';
		$params['no_limit'] = true;
		$detail = $this->getDetail($params);
		if(!empty($detail['countTotal']))
		{
			$countTotal = $detail['countTotal'];
		}
		return $countTotal;
	}


	// 获取列表
	public function getList($params = [])
	{
		$listInfo = [];

		$fields = empty($params['fields']) ? '*' : $params['fields'];
		// 默认查询列表
		$sql = " SELECT {$fields} FROM {$this->tableName}  ";
		$sql .= $this->getWhere($params);
		
		$order = empty($params['order']) ? ' id desc ' : $params['order'];
		$sql .= " ORDER BY {$order} ";


		if(empty($params['no_limit']))
		{
			// 分页参数
			$page = empty($params['page']) ? 1 : $params['page'];
			$size = empty($params['size']) ? 10 : $params['size'];

			// 当前页码 - 1  乘以 显示的条数  例：limit  0,5
			$page = ($page - 1) * $size;
			$sql .= " limit {$page},$size ";
		}


		if(isset($this->sqlCache[$sql]))
		{
			$listInfo = $this->sqlCache[$sql];
		}
		else
		{
			$result = $this->mysqliQuery($sql);
			// 查看贴子列表
			$listInfo = array();
			while($rows = mysqli_fetch_assoc($result))
			{
				$listInfo[] = $rows;
			}
			$this->sqlCache[$sql] = $listInfo;
		}
		// D($this->sqlCache);
		return $listInfo;
	}

	// 获取详情
	public function getDetail($params = [])
	{
		$getDetail = [];
		$list = $this->getList($params);
		if(!empty($list[0]))
		{
			$getDetail = $list[0];
		}
		return $getDetail;
	}


	// 修改数据 params 必须包含ID
	public function update($params = [])
	{
		$detail = [];
		$column = $this->getColumn($params);
		if(!empty($column['id']))
		{
			// UPDATE `demo`.`user` SET `name`='33', `age`='22' WHERE `id`='6';
			$sql = " UPDATE  {$this->tableName}  SET ";

			$field = '';
			foreach ($column as $key => $value) 
			{
				$field .= " `{$key}` = '{$value}',";
			}

			$field = trim($field, ',');
			$sql .= $field;
			$sql .= " WHERE id = {$column['id']} ";
			$result = $this->mysqliQuery($sql);
			$rows  = mysqli_affected_rows($this->connect);
			// var_dump($rows);
			if(!empty($rows))
			{
				$detail = $this->getDetail(['id' => $column['id'] ]);
			}
		}
		return $detail;
	}

	// 添加数据
	public function addInfo($params = [])
	{
		$detail = [];

		$column = $this->getColumn($params);
		$sql = " INSERT INTO  {$this->tableName} ";
		// INSERT INTO `demo`.`user` (`name`, `age`) VALUES ('1', '2');

		$keys = '';
		$values = '';
		foreach ($column as $key => $value) 
		{
			$keys .= "`{$key}`,";
			$values .= "'{$value}',";
		}
		$keys = trim($keys, ',');
		$values = trim($values, ',');

		$sql.= " ($keys) ";
		$sql.= ' VALUES ';
		$sql.= " ($values) ";

		// 执行添加
		$result = $this->mysqliQuery($sql);

		// D($column);
		// D($keys);
		// D($values);
		// D($sql);
		// var_dump($result);

		if( $result === true )
		{
			$insertID = mysqli_insert_id($this->connect); 
			$detail = $this->getDetail(['id' => $insertID]);
		}

		return $detail;
	}

	// 删除数据 params 必须有ID
	public function delete($params = [])
	{
		$rows = 0;
		if(!empty($params['id']))
		{
			$sql = " DELETE FROM {$this->tableName}  WHERE `id`='{$params['id']}' ";
			$result = $this->mysqliQuery($sql);
			$rows  = mysqli_affected_rows($this->connect);
		}
		return $rows;
	}

	// 查询数据
	public function mysqliQuery( $sql = '' )
	{
		// D($sql);
		$result =  mysqli_query($this->connect, $sql);
		return $result;
	}

	// 组装有效的where
	public function getWhere( $params =  [] )
	{
		$strWhere = [];
		
		$column = $this->getColumn($params);
		foreach ($column as $key => $value) 
		{
			$strWhere[] = " {$key} =  '{$value}' ";
		}

		$strWhere = implode(' AND ', $strWhere);

		foreach ($params as $key => $value) 
		{
			if(is_int($key))
			{
				$strWhere .= $value;
			}
		}

		if(!empty($strWhere))
		{
			$strWhere = " WHERE {$strWhere} ";
		}
		// D($params);
		// D($column);
		return $strWhere;
	}

	// 获取表的列字段
	public function getTableColumn($tableName = '')
	{
		$listTableColumn = [];
		$sql = " desc {$tableName}";
		
		$result = $this->mysqliQuery($sql);
		// 查看贴子列表
		$tableColumn = array();
		while($rows = mysqli_fetch_assoc($result))
		{

			$tableColumn[$rows['Field']] = $rows;
		}

		return $tableColumn;
	}
	// 获取参数的有效字段
	public function getColumn( $params =  [] )
	{
		$column = [];
		foreach ($params as $key => $value) 
		{
			if(isset($this->tableColumn[$key]))
			{
				$column[$key] = $value;
			}
		}
		return $column;
	}	

}

// 实例化对象
// $userModel = new DBModel('user');

// 参数
// $params = [];
// 等值查询
// $params['id']  = '5';
// 特殊查询
// $params[]  = '  name like "%c%" ';
// 无效字段
// $params['xxxx']  = '213';
// 获取列表
// $userList = $userModel->getList($params);
// D($userList);

// 获取详情
// $detail = $userModel->getDetail($params);
// $detail = $userModel->getDetail($params);
// $detail = $userModel->getDetail($params);
// D($detail);
// D($userModel);

// 添加数据
// $addInfo = [];  // 添加的参数
// $addInfo['name'] = 'opp';
// $addInfo['age'] = '2';
// $addInfo['username'] = 'opp';
// $addInfo['avatar'] = 'opp';
// $addInfo = $userModel->addInfo($addInfo);
// D($addInfo);

// $update = [];
// $update['id'] = 6;
// $update['name'] = '12312';
// $update = $userModel->update($update);
// D($update);

/*$delete = [];
$delete['id'] = 10;
$delete = $userModel->delete($delete);
var_dump($delete);*/



// 单例测试
// $userModel = new DBModel('user');

// $params = [];
// $params['page'] = 2;
// // $params['order'] = ' age desc ';
// $params['size'] = 5;
// $params[] = '    ( id > 6  OR id < 123 ) ';
// $params['fields'] = 'id,name';
// $list = $userModel->getList($params);
// D($list);

