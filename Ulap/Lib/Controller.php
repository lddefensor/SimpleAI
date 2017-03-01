<?php
/**
 * Parent class of all controllers
 * @author Lorelie Defensor
 */
 namespace Ulap;
 
 require_once('Helpers' .DS. 'MyRuntimeHelper.php');
 require_once('Helpers' .DS. 'MySessionHelper.php');
 require_once('Model.php');
	
 use Ulap\Helpers\MyRuntimeHelper as MyRuntimeHelper;
 use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
 use Ulap\Helpers\MySessionHelper as MySessionHelper;
 
 use Ulap\Model as Model;
 
 class Controller 
 {
	public $models = array();
	public $autoRender = true;
	public $viewData = array(); 
	
	public $currentMethod = null;
	
	public $ViewDir = 'View';
	
	public $privateMethods = array(
		'invokeMethod',
		'beforeMethodCall',
		'afterMethodCall',
		'importModel',
		'render',
		'beforeRenderView',
		'afterRenderView',
		'useSession',
		'redirect'
	);
	
	
	public function __construct()
	{ 
		$name = get_class($this); 
		$name = substr($name, strrpos($name, '\\') + 1);
		$this->name = $name == 'Controller' ? $name : str_replace('Controller', '', $name); 
	
		$this->__initializeModels();
	}
 	
	private function __initializeModels()
	{
		if(!is_array($this->models) || !sizeof($this->models))
			return;
		try
		{ 	
			foreach($this->models as $model){
				$this->importModel($model);	
			}
		}
		catch (\Exception $e)
		{ 
			throw new MyRuntimeException('Model Error: '. $e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * allows on the fly creation of model class with name $model
	 */
	function importModel(string $model, bool $reimport = false)
	{
		
		$model = ucfirst($model) ;
		
		if(isset($this->$model) && $reimport === false)
			return $this->$model; 
		
		$modelPath = ROOT . DS . 'Model' . DS . $model . 'Model.php';
		$runtime = new MyRuntimeHelper($modelPath, 'App\\'.$model);	
		$this->$model = $runtime->instantiateClass(); 
		
		return $this->$model;
	}
	
	function useSession(bool $use){
		 if(!defined('SESSION_HANDLER'))
				throw new MyRuntimeException('Undefined Session Handler');
		
			$SessionHandler = SESSION_HANDLER;
			 
			$this->Session = new $SessionHandler(); 
	}
	
	/**
	 * is always called before a method is invoked
	 * throws an error to disable continuing of method
	 */  
	public function beforeMethodCall(){
		
	}
	
	/**
	 * performs something about the results and is called after every method is invoked
	 */
	public function afterMethodCall(&$results){
		
	} 
	
	/**
	 * logic before a view is rendered
	 */
	public function beforeRenderView(){
		
	}
	
	/** 
	 * logic after a view is rendered
	 */
	public function afterRenderView(){
		
	}
	
	
	/**
	 * passess any of the data inside viewData in the template
	 */
	public function render(){
		
		$dir = ROOT.DS.$this->ViewDir.DS.$this->name.DS;

		if(!file_exists($dir) || !file_exists($dir.$this->currentMethod.".html"))
		{
			throw MyRuntimeException::ViewFolderNotFound($this->name, $this->currentMethod);
		}   
		
		$this->beforeRenderView();

		if(sizeof($this->viewData)) extract ($this->viewData); 

		include_once(ROOT.DS."Layouts".DS."top.html");

		include_once($dir.$this->currentMethod.".html");

		include_once(ROOT.DS."Layouts".DS."bottom.html");
		
		$this->afterRenderView();
		
		if(!headers_sent())
		{
			header('Access-Control-Allow-Origin: *');
			http_response_code(200);
		} 
		
	}
	
	/**
	 *redirect allows the controller to forfeit method execution and go to another action
	 *
	 */
	public function redirect($url)
	{  
			header("Location: " . URL . $url);
			exit;
	}
	
	/**
	 * default action
	 */
	public function index(){ 
	}
	 
 }
 
 // END OF FILE
