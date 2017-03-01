<?php
/**
 * Parent class of all controllers
 * @author Lorelie Defensor
 */
 namespace Ulap;
 
 require_once('Helpers'.DS.'MyRuntimeHelper.php');
 require_once('Model.php');
 
 use Ulap\Helpers\MyRuntimeHelper as MyRuntimeHelper;
 use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
 use Ulap\Model as Model;
 
 class Controller 
 {
 	public $models = array();
	public $autoRender = true;
	public $viewData = array(); 
	
	public $privateMethods = array(
		'invokeMethod',
		'beforeMethodCall',
		'afterMethodCall',
		'importModel',
		'render',
		'beforeRenderView',
		'afterRenderView'
	);
	
	
	public function __construct()
	{ 
		$this->name = get_class($this) == 'Controller' ? get_class($this) : str_replace('Controller', '', __CLASS__); 
	
		$this->__initializeModels();
	}
 	
	private function __initializeModels()
	{
		if(!is_array($this->models) || !sizeof($this->models))
			return;
		try
		{ 	
			foreach($this->models as $model){
				$model = ucfirst($model) ;
				$modelPath = ROOT . DS . 'Model' . DS . $model . 'Model.php';
				$runtime = new MyRuntimeHelper($modelPath, 'App\\'.$model);	
				$this->$model = $runtime->instantiateClass(); 
			}
		}
		catch (\Exception $e)
		{
			var_dump($e->getMessage());
			throw new MyRuntimeException('Model Error: '. $e->getMessage(), $e->getCode());
		}
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
		
		//
		
		
	}
	
	/**
	 * default action
	 */
	public function index(){ 
	}
	 
 }
 
 // END OF FILE
