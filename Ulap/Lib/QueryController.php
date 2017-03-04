<?php
/**
 * Parent class of all controllers
 * @author Lorelie Defensor
 */
 namespace Ulap;  
 
 require_once 'Helpers' . DS . 'MyRuntimeException.php';
 require_once 'Helpers' . DS . 'MyRuntimeHelper.php';
 require_once 'Controller.php';
 require_once ROOT . DS . 'Controller' . DS . 'Components' . DS . 'Query.php';
 require_once ROOT . DS . 'Model' . DS . 'AppModel.php';
 
 use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
 use Ulap\Helpers\MyRuntimeHelper as MyRuntimeHelper;
 use Ulap\Controller as Controller;
 use Ulap\Model as Model;
 use App\AppModel as AppModel;
 use App\Query as Query;
 
 class QueryController extends Controller
 { 
	 const defaultMethod = 'f';
	 public $autoRender = false;
	 public $instance;
	 public $method;
	 public $model;
	 
	 public $isJson = true;
	 
	 private static $availableMethods = array(
	 	'f' => 'fetch',
		'l' => 'fetchList',
		's' => 'save',
		'd' => 'delete',
		'm' => 'reorder',
		'dg' => 'getDetails'
	 );
	 
	 //
	 private $args = array();
	 
	 /**
	  * entry point of query controller
	  */
	 public function query($args){ 
		
		$this->args = explode(':', $args); 
		
		return $this->__processQuery();
		
	 }
	 
	 
	 /**
	  * returns the first part of args
	  */
	 private function __getModel(){
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
	 private function __getModelClass($method){
		
		$instance = $this->instance;
		
		if(isset($instance['modelClass']))
		{
			$path = ROOT . DS . 'Model' . DS . $instance['modelClass'] . 'Model.php';
			
			if(!file_exists($path))
				throw MyRuntimeException::MissingFile($path, $instance['modelClass']);
			
			require_once($path);
			
			$modelClass = 'App\\'.$instance['modelClass'];

			if(!class_exists($modelClass))
				throw MyRuntimeException::ClassNotFound($modelClass);
			
			$runtime = new MyRuntimeHelper($path, $modelClass);
			$this->model = $runtime->instantiateClass();

			return $this->model;
		} 
		
		$tableName = '';
		
		if(isset($instance['table_'.$method]))
			$tableName = $instance['table_'.$method];
		else if(isset($instance['table']))
			$tableName = $instance['table'];
			
		if(empty($tableName))
			throw new MyRuntimeException('Invalid Query Model, no table is defined for method '. $method, 1104);
		
		$this->model = new AppModel($tableName); 
		return $this->model;
	 }
	 
	 
	 /**
	  *process request
	  */
	 protected function __processQuery(){
		$model = $this->__getModel();
		
		if($model == null)
			throw new MyRuntimeException('Invalid Request: Null Query Model', 1101);
		
		if(!isset(Query::$$model))
			throw new MyRuntimeException('Invalid Request: Undefined Query Model ' . $model, 1102);
		
		$method = $this->__getMethod();
		
		if(!isset(self::$availableMethods[$method]))
			throw new MyRuntimeException('Invalid Request: Method is not available ' . $model, 1103);
		
		$queryMethod =  '__' . self::$availableMethods[$method];
		
		$this->instance = Query::$$model; 
		
		$this->__getModelClass($queryMethod);
		$result = $this->$queryMethod();


		
		if(!$result)
			throw new MyRuntimeException('Invalid Response for method ' . $method, 1104);
		
		return $result;
		
	 }


	 /*
	 * is called by data grid
	 */
	 private function __fetch(){
	 	$this->model->queryParams = $this->data;
	 	return $this->model->fetch();
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

	 	return $this->model->find($options);
	 }

	/*
	* called by add or edit form
	*/
	private function __save(){
		if(!isset($this->instance['unique']) && !isset($this->model->uniqueFields))
			throw new MyRuntimeException('No unique fields, unable to perform save');

		if(!isset($this->model->uniqueFields))
			$this->model->uniqueFields = $this->instance['unique'];  

		$name = $this->instance['name'] ?? '';

		return $this->model->saveUnique($this->data, $name);
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
			$code = (int) ($this->model->getErrorCode);
			$error = 'Invalid Response';
			 
			if($code == 23000)
				$error['error'] = $i['name'] . " is being referenced by another data.";
			
			return $error;
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
 }
 
 // END OF FILE
