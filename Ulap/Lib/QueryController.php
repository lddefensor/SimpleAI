<?php
/**
 * Parent class of all controllers
 * @author Lorelie Defensor
 */
 namespace Ulap;  
 
 require_once 'Helpers' . DS . 'MyRuntimeException.php';
 require_once 'Helpers' . DS . 'MyRuntimeHelper.php';
 require_once 'Controller.php';
 
 use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
 use Ulap\Helpers\MyRuntimeHelper as MyRuntimeHelper;
 use Ulap\Controller as Controller;
 use Ulap\Model as Model;
 
 class QueryController extends Controller
 { 
	 const defaultMethod = 'f';
	 public $autoRender = false;
	 public $instance;
	 public $method;
	 public $model;
	 public $continue = true;
	 
	 public $isJson = true;
	 
	 private static $availableMethods = array(
	 	'f' => 'fetch',
		'l' => 'fetchList',
		's' => 'save',
		'd' => 'delete',
		'm' => 'reorder',
		'dg' => 'getDetails',
		'dc' => 'downloadCSV'
	 );
	 
	 //
	 private $args = array();

	 public function beforeMethodCall(){
	 	return true;
	 }

	 public function beforeQuery(){
	 	//any logic before method is called
	 }
	 
	 /**
	  * entry point of query controller
	  */
	 public function query($args){ 
		
		$this->args = explode(':', $args);  
 
		$result = $this->__processQuery();

		return $result; 
	 }  
	 
	 /**
	  * returns the first part of args
	  */
	 function __getModel(){
		return isset($this->args[0]) ? $this->args[0] : null;
	 }
	 
	 
	 /**
	  * returns the second part of args
	  */
	 private function __getMethod(){
		return isset($this->args[1]) ? $this->args[1] : self::defaultMethod;
	 }
	 
	 
	 /**
	  * gets the rest of arguments
	  */
	 private function __getParams(){ 
		return array_splice($this->args, 2);
	 }
	 
	 
	 /**
	  *returns and instance of AppModel if modelClass is not specified
	  */
	 function __getModelClass($method){
		 
		$instance = $this->instance;
		
		if(isset($instance['modelClass']))
		{
			$path = $this->basePath . 'Model' . DS . $instance['modelClass'] . 'Model.php';
  
			if(!file_exists($path))
				throw MyRuntimeException::MissingFile($path, $instance['modelClass']);
			
			require_once($path);
			
			$modelClass = 'App\\'.$instance['modelClass']; 

			if(!class_exists($modelClass))
				throw MyRuntimeException::ClassNotFound($modelClass);
			
			$runtime = new MyRuntimeHelper($path, $modelClass);
			$this->model = $runtime->instantiateClass(); 
 

			if(isset($instance['table_'.$method]))
			{
				$tableName = $instance['table_'.$method]; 
				$this->model->tableName = $tableName;
				$this->model->queryBuilder->tableName = $tableName;
			}

			return $this->model;
		} 
		
		$tableName = '';

		if(isset($instance['table_'.$method]))
			$tableName = $instance['table_'.$method];
		else if(isset($instance['table']))
			$tableName = $instance['table'];
			
		if(empty($tableName))
			throw new MyRuntimeException('Invalid Query Model, no table is defined for method '. $method, 1104);
		
		$this->model = new \App\AppModel($tableName); 
		return $this->model;
	 }
	 
	 
	 /**
	  *process request
	  */
	 protected function __processQuery(){
		$model = $this->__getModel();
		
		if($model == null)
			throw new MyRuntimeException('Invalid Request: Null Query Model', 1101);
		
		if(!isset(\App\Query::$$model))
			throw new MyRuntimeException('Invalid Request: Undefined Query Model ' . $model, 1102);
		
		$method = $this->__getMethod();
		
		if(!isset(self::$availableMethods[$method]))
			throw new MyRuntimeException('Invalid Request: Method is not available ' . $model, 1103);
		
		$queryMethod =  self::$availableMethods[$method];
		
		$this->instance = \App\Query::$$model; 
		
		$this->__getModelClass($queryMethod);
		$queryMethod =  '__' . $queryMethod;

		$before = $this->beforeQuery();

		if(!$this->continue) return $before;

		$result = $this->$queryMethod();

 
		if($result === false || $result === null)
			throw new MyRuntimeException('Invalid Response for method ' . $method, 1104);

		if(DEBUG && isset($result['debug']))
		{ 
			$result['debug'] = $_SESSION['debug']; 
		}
		unset($_SESSION['debug']);
		
		return $result;
		
	 } 


	 /*
	 * is called by data grid
	 */
	 private function __fetch(){
  
 		if(!isset($this->data['sort']) && isset($this->instance['order']))
 		{ 
 			$this->data['sort'] = $this->instance['order'];
 		}  

 		if(!isset($this->data['rowCount'])) $this->data['rowCount'] = 10;

	 	$this->model->queryParams = $this->data; 

	 	$result = $this->model->fetch();
	 	$result['queries'] =  ($_SESSION['queries']);
	 	$result['success'] = true;
	 	
	 	return $result;
	 }

	 /*
	 * is called by list (select tag/droplist etc)
	 */
	 private function __fetchList(){
	 	$options = array();
	 	if(isset($this->instance['params_list']))
	 	{
	 		$options = $this->instance['params_list'];
	 	}
	 	$this->model->queryParams = $this->data;

	 	$result = $this->model->find($options);


	 	if(!$result) return array();

	 	return $result;
	 }

	/*
	* called by add or edit form
	*/
	private function __save(){
		if(!sizeof($this->data)) return array('success'=>false, 'error'=>'Empty');

		if(!isset($this->instance['unique']) && !isset($this->model->uniqueFields))
			throw new MyRuntimeException('No unique fields, unable to perform save');

		if(!isset($this->model->uniqueFields))
			$this->model->uniqueFields = $this->instance['unique'];  

		$name = $this->instance['name'] ?? '';

		if(isset($this->data['session_id']))
			unset($this->data['session_id']);

		$result = ($this->model->saveUnique($this->data, $name));

		if($result)
		{
			$id = (int) $result;
			return array('success'=> true, 'id'=>$id);
		}
		
		$error = $this->model->getErrorMessage();
		$errorCode = $this->model->getErrorCode();

		$result = array('success'=>false, 'error'=>$error);

		if($errorCode) $result['code'] = $errorCode;

		return $result; 
	}
	
	/*
	* called on edit form -> delete function
	*/
	private function __delete(){ 
		if(!isset($this->data['x']))
			throw new MyRuntimeException('No defined unique key for delete');


		$this->model->id = $this->data['x']; 
		
		if(!$this->model->delete()) 
		{
			$code = (int) ($this->model->getErrorCode());
			$error = 'Invalid Response';
			 
			if($code == 23000)
				$error = $this->instance['name'] . " is being referenced by another data.";
			
			return array('success'=>$error, 'error'=>$error);
		}
		
		return array('success'=>true);
	}

	/*
	* called by details page
	*/
	private function __getDetails()
	{ 
		$params = $this->__getParams(); 

		if(!sizeof($params))
			throw new MyRuntimeException('Invalid params, undefined key for details');

		$id = $params[0];

		return $this->model->get($id);
	}
	
	 /*
	 * called by draggable / reorder grid
	 */
	 private function __reorder(){

	 	if(!isset($this->data['a']) || !isset($this->data['b']))
	 		throw new MyRuntimeException('Invalid Arguments.'); 
		
		$current = (array) $this->data['a'];
		$replace = (array) $this->data['b'];

		return $this->model->reorder($current, $replace);
	 }

	 /** download csv **/
	 private function __downloadCSV(){ 
	 	$data = $this->__fetch();  
			 
		$data = $this->__fetch(); 
 
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=". $this->instance['name'] .".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		if(!isset($data['rows']))
		{
			return '';
		}
		
		$data = $data['rows'];
		
		$headers = array_keys($data[0]);
		
		$csv = array(implode(',', $headers));
		
		
		foreach($data as $row)
		{
			$line = array();
			foreach($headers as $key)
			{
				$line[] = '"' . $row[$key] . '"';
			}
			
			$csv[] = implode(",", $line);
		}
		
		
		echo implode("\n", $csv);
		exit;
	}
 }
 
 // END OF FILE
