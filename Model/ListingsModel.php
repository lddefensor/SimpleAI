<?php
/**
 *Model interface for table eg-visitors
 * @author Lorelie Defensor
 */

namespace App;

require_once 'AppModel.php';

use App\AppModel as AppModel;

class Listings extends AppModel{

	var $tableName = 'listings';  

	public function getDetails($id)
	{
		return $this->get($id);
	}
}
