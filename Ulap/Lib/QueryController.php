<?php
/**
 * Parent class of all controllers
 * @author Lorelie Defensor
 */
 namespace Ulap;  
 
 require_once 'Helpers' . DS . 'MyRuntimeException.php';
 require_once 'Helpers' . DS . 'MyRuntimeHelper.php';
 require_once 'Controller.php';
 require_once ROOT . DS . 'Query.php';
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
	 
	 public $isJson = true;
	 
	 private static $availableMethods = array(
	 	'f' => 'fetch',
		'l' => 'fetchList',
		's' => 'save',
		'd' => 'delete',
		'm' => 'reorder' 
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
			$path = ROOT . DS . 'Model' . DS . $$instance['modelClass'] . 'Model.php';
			
			if(!file_exists($path))
				throw MyRuntimeException::MissingFile($path, $instance['modelClass']);
			
			require_once($path);
			
			$modelClass = 'App\\'.$instance['modelClass'];
			if(!class_exists($modelClass))
				throw MyRuntimeException::ClassNotFound($modelClass);
			
			$runtime = new MyRuntimeHelper($path, $modelClass);
			return $runtime->instantiateClass();
		} 
		
		$tableName = '';
		
		if(isset($instance['table_'.$method]))
			$tableName = $instance['table_'.$method];
		else if(iset($instance['table']))
			$tableName = $instance['table'];
			
		if(empty($tableName))
			throw new MyRuntimeException('Invalid Query Model, no table is defined for method '. $method, 1104);
		
		return new AppModel($tableName); 
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
		
		$queryMethod = self::$availableMethods[$method];
		
		$this->instance = Query::$$model; 
		
		$model = $this->__getModelClass($queryMethod);

		$model->queryParams = $this->data; 
		
		$result = $model->$queryMethod(); 
		
		if(!$result)
			throw new MyRuntimeException('Invalid Response for method ' . $method, 1104);
		
		return $result;
		
	 }
	 
	  
	 
 }
 
 // END OF FILE
