<?php

/**
* Simple Email Helper
* @author Lorelie Defensor
*/


namespace Ulap\Helpers;


class EmailHelper {

	var $to;
	var $subject;
	var $message;
	var $headers;
	var $additionalParams;


	public function __construct($to, $subject)
	{
		$this->to = $to;
		$this->subject = $subject;

		$headers = array();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=iso-8859-1';

		// Additional headers
		$headers[] = 'To: ' . $this->to;
		$headers[] = 'From: Ulap - Casting Bee <lddefensor@gmail.com>'; 

		$this->headers = $headers;

	}

	public function template(string $viewFile, array $viewData = array())
	{
		$dir = ROOT . DS . 'Layouts' . DS . 'Email' . DS . $viewFile;

		if(!file_exists($dir))
			return false;

		extract($viewData);

		ob_start();
		include($dir);
		$message = ob_get_clean();
		

		$this->message = $message;

		return true; 
	}

	public function send()
	{ 
		$headers = implode("\r\n", $this->headers);
		return mail ($this->to, $this->subject, $this->message, $headers, $this->additionalParams);
	}
}



/** END OF FILE **/