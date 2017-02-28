<?php

/** 
 * HTML Helper 
 * (This helper class is incomplete as i build it as i use it) 
 * THe layout is based on twitter bootstrap's layout
 */
 

class HTMLHelper 
{
	public static $hasErrors = array();
	public static $data = array();
	public static $message = array();
	 
	
	public static function formInput($name, $label = "", $inputHtml = "", $horizontal = false)
	{
		if(!$label) $label = ucfirst($name);
		
		$hasErrors = self::$hasErrors;
		$data = self::$data;
		 
	 	$html = "<div id=\"form-group-$name\" for=\"$name\"  class=\"form-group";
	 	if(isset($hasErrors[$name]))
		{
	 		$html .= " has-error has-feedback";
		}
				
		$html .= "\">";
		$html .= "<label class=\"control-label ";
		
		if($horizontal) $html .= " col-sm-3 ";
		
		$html .= "\" for=\"".$name."Input\">$label ";
		
		if(isset($hasErrors[$name])) 
		{
			$html .= "- ".$hasErrors[$name] ;
		}
		
		$html .= "</label>";
		
		if($horizontal) $html .="<div class=\"col-sm-9\">";
		$html .= $inputHtml;
		if($horizontal) $html .="</div>";
		
		if(isset($hasErrors[$name]))
		{
			$html .= "<span class=\"glyphicon glyphicon-remove form-control-feedback\" aria-hidden=\"true\"> </span>";
	    	$html .= "<span id=\"".$name."Error\" class=\"sr-only\">(error)</span>";
		}
		 
		$html .= "</div>"; 
		
		return $html;
	}
	
	public static function input($name, $label = "", $type = "text", $attrs = array(), $horizontal = false)
	{ 
		
		$html = "<input type=\"$type\" name=\"$name\"class=\"form-control\" id=\"".$name."Input\" placeholder=\"$label\"";
		
		if(isset($data[$name]))
		{
			$html .=  " value=\"". $data[$name] . "\" ";
		}
		
		if(isset($hasErrors[$name]))
		{
			$html .= "aria-describedby=\"".$name."Error\"";
		}
		
		if(sizeof($attrs))
		{ 
			foreach($attrs as $key => $value)
			{
				$html .= " ";
				$html .= "$key=\"$value\"";
			}
		}
		
		$html .= "/>"; 
		
		return self::formInput($name, $label, $html, $horizontal);
	}
	public static function textarea($name, $label = "",  $attrs = array(), $horizontal = false)
	{ 
		
		$html = "<textarea name=\"$name\"class=\"form-control\" id=\"".$name."Input\" placeholder=\"$label\"";
		
		if(isset($data[$name]))
		{
			$html .=  " value=\"". $data[$name] . "\" ";
		}
		
		if(isset($hasErrors[$name]))
		{
			$html .= "aria-describedby=\"".$name."Error\"";
		}
		
		if(sizeof($attrs))
		{ 
			foreach($attrs as $key => $value)
			{
				$html .= " ";
				$html .= "$key=\"$value\"";
			}
		}
		
		$html .= "></textarea>"; 
		
		return self::formInput($name, $label, $html, $horizontal);
	}
	
	public static function checkbox($name, $label = "", $checked = false)
	{
		if(!$label) $label = ucfirst($name);
		$html = "<div class=\"checkbox\">
		    <label>
		      <input type=\"checkbox\" ";
		if($checked) $html .= "checked=\"checked\"" ;     
		$html .=" name=\"$name\"> $label
		    </label>
		  </div>";
		 return $html;
		
	  
	}
	
	public static function message()
	{
		$html = ''; 
		$message = self::$message; 
		if(is_array($message) && isset($message['message']))
		{
			$html = "<div class=\"alert ";
			if(isset($message['error']) && $message['error'] === true)
			{
				$html .= "alert-danger";
			}
			else 
			{
				$html .= "alert-success";	
			}
			$html .= "\">";
			
			if(isset($message['title']))
			{
				$html .= "<h4>" . $message['title'] . "</h4>";
			} 
			
			$html .= $message['message'] . '</div>';
			
		} 
		return $html;
	}
	
	public static function bootgridTable($id, $columns)
	{
		$html = "<table id=\"$id\" class=\"table table-condensed table-hover table-striped\">";
		 
		if(sizeof($columns) > 0)
		{
			$html .= "<thead><tr>";
			foreach($columns as $key=>$value)
			{ 
				$html .= "<th data-column-id=\"".$value["fieldname"]."\"";
				 
				foreach($value as $k => $v)
				{ 
					if($v != "header")
					{ 
						if($v === false) $v = "false";
						else if($v === true) $v = 'true'; 
						$html .= " data-".$k. "=\"" . $v ."\" ";
					}
				} 
				
				$html .= ">" . $value['header']."</th>";
			}


			$html .= "</tr></thead>";
		}
		
		$html .= "</table>";  
		
		return $html;
	}
	
	public static function dropdown($id, $label, $options)
	{
		$html = "<div class=\"dropdown input-group\">";
		$html .= "<button class=\"btn btn-default dropdown-toggle\" type=\"button\" id=\"$id\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\" >";
		$html .= $label . " <span class=\"caret\"></span></button>";
		$html .= "<ul class=\"dropdown-menu\" aria-labelledby=\"$id\">";
		
		foreach($options as $key=>$value)
		{
			$html .= "<li><a href=\"#\">$value</a></li>";
		}
		
		$html .= "</ul>";
		$html .="</div>"; 
		
		return $html;
	}
	
	public static function select($name,  $options, $attrs= array())
	{  
		$html  = "<select class=\"form-control\" name=\"$name\"";
		$disabled = false;
		if(sizeof($attrs))
		{ 
			foreach($attrs as $key => $value)
			{
				if($key !='disabled')
				{
					$html .= " ";
					$html .= "$key=\"$value\"";
				}
				else 
				{
					$html .= " data-disabled=\"true\" ";
					$disabled = true;
				}
			}
		}
		$html .= ">";
		
		foreach($options as $key=>$value)
		{
			$html .= "<option value=\"$key\" ";
			if($disabled) $html .= " disabled ";
			$html .= ">$value</option>";
		}
		
		$html .= "</select>"; 
		
		return $html;
	}
	
	public static function layout($title)
	{
		if(file_exists(ROOT.DS."Layouts".DS.$title.".html"))
		{  
			include_once(ROOT.DS."Layouts".DS.$title.".html");
		}
	}
	
}

// END OF FILE
