<?php


/**
 * Helper File to Build Query
 */

 namespace Ulap\Helpers;
 
 class QueryHelper {
	
	public $tableName = '';
	public $primaryKey = 'id';
	public $fields = array();
	public $tableFields = array();
	public $dbConnection;
	public $isDerived = false;
	public $derivedTable = '';

	const order = array('DESC', 'ASC');
	
	const directQueries = array(
			'=',
			'>',
			'>=',
			'<',
			'<=',
			'!=',
			'LIKE',
			'LIKE %...%',
			'LIKE %',
			'LIKE ...%',
			'NOT LIKE',
			'NOT LIKE %...%',
			'NOT LIKE %',
			'NOT LIKE ...%',
		);
	
	const arrayQueries = array(
			'IN',
			'NOT IN',
			'BETWEEN',
			'NOT BETWEEN'
		);
	
	const nullQueries = array(
			'IS NULL',
			'IS NOT NULL'
		);
	
	const operatorQueries = array(
			'OR',
			'AND'
		); 

	public function __construct($model = null)
	{
		if($model != null)
		{ 
			$this->tableName = $model->tableName;
			$this->dbConnection = $model->dbConnection;
			$this->tableFields = $model->_fields;
			$this->primaryKey = $model->primaryKey;
		}
	} 

	public function buildSelectQuery(array $options = array()) : string{
		$fields = $options['fields'] ?? $this->fields ?? '*'; 

		$query = $this->select($fields);
 

		if(isset($options['conditions']))
		{
			$query .= ' WHERE ' . $this->buildWhere($options['conditions']);
		}

		if(isset($options['group']))
		{
			$query .= $this->buildGroupBy($options['group']);
		}

		if(isset($options['order']))
		{
			$query .= $this->buildOrderBy($options['order']);
		}

		if(isset($options['limit']) && $options['limit'])
		{
			$limit = $options['limit'];
			$page = null;

			if(isset($options['page']) && $options['page']) $page = $options['page'];

			$query .= $this->buildLimit($limit, $page);
		}  

		return $query;
	}

	public function buildInsertQuery(array &$data)
	{
		$info = $this->buildInsertFields($data);
		extract($info);

		$query = ' INSERT INTO `' . $this->tableName . '` (' . implode(', ', $fields) .') VALUES (' . implode(', ', $args) .')';

		return array('query'=>$query, 'args' => $values);
	}

	public function buildUpdateQuery(array &$data)
	{ 
		$info = $this->buildSetFields($data);
		extract($info);

		$query = ' UPDATE `' . $this->tableName . '` SET ' . implode(', ', $fields);

		return array('query' => $query, 'args' => $args);
	}

	public function buildUpdateByIdQuery(array $data, $id)
	{
		$build = $this->buildUpdateQuery($data);
		extract($build);

		$query .= ' WHERE ' . $this->primaryKey . ' = ' . $this->__parseFieldValue($this->primaryKey, $id); 

		return array('query' => $query, 'args' => $args);
	}

	public function buildDeleteQuery()
	{
		return ' DELETE FROM ' . $this->tableName;
	}

	public function buildDeleteByIdQuery($id)
	{
		$query = $this->buildDeleteQuery();

		return $query . ' WHERE ' . $this->primaryKey . ' = ' . $this->__parseFieldValue($this->primaryKey, $id);
	}

	public function select($fields = '*')
	{
		$table = $this->isDerived ? $this->derivedTable : '`' . $this->tableName . '`';

		return ' SELECT ' . $this->buildSelectFields($fields) . ' FROM '.$table.' ';
	}
	 
	public function buildSelectFields($fields='*'){
		if(empty($fields)) $fields = '*';

		if(is_string($fields)) return $fields; 
	 
		if(isset($this->tableFields) && sizeof($this->tableFields))
		{
			$tableFields = $this->tableFields;
			return implode(', ', array_reduce($fields,
											  function($a, $b) use ($tableFields) {
												if(isset($tableFields[$b]))
													$a[] = '`'.$b.'`';
												else $a[] = $b;
												return $a;
											},
									array()
							)
					);
		}
		
		if(is_array($fields))
			return implode(', ', array_map(function($field){return '`'. $field . '`';},  $fields));
	}
	
	public function buildSetFields(&$data)
	{
		$fields = array();
		$values = array();
		$tableFields = $this->tableFields; 
		foreach($data as $field => $value)
		{
			if(isset($this->tableFields[$field]))
			{
				$fields[] = '`' . $field  . '` = :update_' . $field;
				$values[':update_' . $field] = $value;
			}
			else unset($data[$field]);
		}
		return array('fields'	=>$fields, 'args' =>$values);
	}
	
	public function buildInsertFields(&$data)
	{
		$fields = array();
		$args = array();
		$values = array();
		$tableFields = $this->tableFields;
		foreach($data as $field => $value)
		{
			if(isset($this->tableFields[$field]))
			{
				$fields[] = '`' . $field  . '`'; 
				$values[':'.$field] = $value;
				$args[] = ':'.$field;
			}
			else unset($data[$field]);
		}
		return array('fields'	=>$fields, 'args' => $args, 'values' =>$values);
	}
	
	/**
	 * BUILDS WHERE CONDITION
	 */
	public function buildWhere($conditions, bool $and = true) : string{
		
		if(is_string($conditions)) return $conditions;
		
		if(is_array($conditions) && sizeof($conditions) )
		{ 
			$where = array();
			
			foreach($conditions as $a => $b)
			{
				$c = $this->__getClause($a, $b);
				 
				if($c)
					$where[] = $c;
			}
			
			$operator = $and ? ' AND  ' : ' OR ';
			
			return '(' . implode($operator, $where) .')';
		}
		
		return '';
	}
	 
	/*
	 * returns clause from conditions
	 */
	
	private function __getClause($a, $b) : string
	{
		if(is_int($a) && is_string($b)) return '(' .$b .')'; 

		if(is_int($a) && is_array($b))
		{
			$clause = array();
			foreach($b as $c1 => $c2)
			{
				$clause[] = $this->__getClause($c1, $c2); 
			}

			return implode(' AND ', $clause);
		}
		
		if(is_string($a))
		{
			if(in_array($a, self::operatorQueries) && is_array($b))
			{
				return $this->buildWhere($b, $a === 'AND');
			}
			
			
			//remove '`' from $a 
			$clauseType = $this->__getClauseType($b);   

			$a = str_replace('`', '', $a);  
			switch ($clauseType)
			{
				default: 
					$clause = $this->__buildEqualTo($a, $b, $clauseType); 
					break; 
				case 'IN':
				case 'NOT IN':
					
					$in = $clauseType == 'IN';    

					if(!$in)
					{
						$b = $b['NOT IN']; 
					}

					else if(sizeof($b) == 1 && isset($b[0]) && !is_int($b[0]))
					{ 
						$keys = array_keys($b);
						$b = $b[$keys[0]]; 
					} 

					if(!is_array($b)) $b = array($b);
					
					$clause = $this->__buildInList($a, $b, $in);
				break;	
				case 'IS NULL':
				case 'IS NOT NULL':
					$null = $clauseType == 'IS NULL';
					$clause = $this->__buildIsNull($a, $null); 
					break;
				case 'BETWEEN':
				case 'NOT BETWEEN':
					$between = $clauseType == 'BETWEEN';
					$keys = array_keys($b);
					$value = $b[$keys[0]]; 
					$clause = $this->__buildBetween($a, $value, $between); 
					break;
				case 'LIKE':
				case 'NOT LIKE':
					$like = $clauseType == 'LIKE';
					$clause = $this->__buildLike($a, $b, $like);
					break;
			}
			
			return '(' . $clause .')';
		} 
		
		return '';  
	}
	
	/*
	 *
	 */
	private function __getClauseType($clause)
	{
		if(is_array($clause))
		{  

			//check if first item is mixed;
			if(sizeof($clause) == 1)
			{ 
				$keys = array_keys($clause);
				$key = strtoupper($keys[0]); 
				if(in_array($key, self::arrayQueries))
				{  
					return $key;
				}
			} 
			
			return 'IN';
		} 

		if(preg_match("/^(!=|>|>=|<|<=) /", $clause))
		{
			$d = explode(" ", $clause);
			return $d[0];
		}
		
		if(preg_match("/^(LIKE) /i", $clause))
		{
			return 'LIKE';
		}
		
		if(preg_match("/^(NOT LIKE) /i", $clause))
		{
			return 'NOT LIKE';
		} 
		
		if(strtoupper($clause) === 'IS NULL')
			return 'IS NULL';
		
		
		if(strtoupper($clause) === 'IS NOT NULL')
			return 'IS NOT NULL';
		
		return '=';
	}
	
	/**
	 * quotes / parses a string / int / float depedning on type of field from table
	 */
	private function __parseFieldValue(string $field, $value)
	{
		$column = null;
		if(isset($this->tableFields[$field])) 
			$column = strtolower($this->tableFields[$field]); 

		
		if(
			$column == 'string' || 
			$column == 'datetime' || 
			$column == 'date' || 
			$column == 'timestamp' || 
			($column == null && is_string($value))
		)
		{
			if($this->dbConnection) $value = $this->dbConnection->quote($value);
			//warning dangerous
			else $value = "'" . addslashes($value) . "'";
 		}
		else if($column == 'int' || ($column == null && is_int($value)))
		{ 
			if($value === NULL)
				$value = $value;
			else
				$value = (int) $value;
		}
		else if ($column == 'float' || ($column == null && is_float($value)))
		{
			$value = (float) $value;
		} 
		
		return $value;
	}
	
	/*
	 * value is expected to be of size 2
	 */
	private function __buildBetween(string $field, array $value, bool $between)
	{
		$operator = $between ? ' BETWEEN ' :  ' NOT BETWEEN ';
		 
		$value0 = $this->__parseFieldValue($field, $value[0]);
		$value1 = $this->__parseFieldValue($field, $value[1]);
		
		return '`' . $field . '` '. $operator . ' ' . $value0 . ' AND  ' . $value1; 
	}
	
	
	//basic where condition is composed of name value pair
	private function __buildEqualTo($field, $value, $clauseType = '=')
	{ 
		if($clauseType != '=')
		{
			$value = str_replace($clauseType . ' ', '', $value );
		}
		
		$value = $this->__parseFieldValue($field, $value);
 
		if($value === NULL)
			$where = '`' . $field . '` IS NULL';  
		else 
			$where = '`' . $field . '` '. $clauseType . ' ' . $value;  

		return $where;
	}
	 
	
	private function __buildInList(string $field, array $value,  $in = true)
	{ 
		$list = array();
		 
		foreach($value as $item)
		{
			$item = $this->__parseFieldValue($field, $item);
			$list[] = $item;
		} 
		
		$in = $in ? ' IN ' : ' NOT IN ';
		
		return '`' . $field . '` ' . $in . ' (' . implode(', ', $list ) . ')';
 	}
	private function __buildIsNull($field, $null = true)
	{
		return '`' .$field . '` ' . ($null ? ' IS NULL ' : ' IS NOT NULL ') ; 
	}
	
	
	private function __buildLike(string $field, string $value, $like = true)
	{
		//$value is still to be processed
		$operator = $like ? 'LIKE ' : 'NOT LIKE ';
		$value = str_replace($operator, '', $value);
		$value = strtoupper($value);
		 

		$value = '\'' . $value .'\''; 
		
		return 'UCASE(`' . $field . '`) ' . $operator . ' ' . $value; 
	}
	
	/**
	* builds sub query for ordering 
	*/
	public function buildOrderBy($orderBy)
	{
		return ' ORDER BY ' . $this->__buildOrderBy($orderBy);
	}
	private function __buildOrderBy($orderBy)
	{
		if(is_array($orderBy))
		{
			$b = array();
			foreach($orderBy as $a)
			{
				$b[] = $this->buildOrderBy($a);
			}

			return implode(', ', $b);
		}
		else if(is_string($orderBy))
		{    
			if(preg_match("/(ASC|DESC)$/i", $orderBy))
			{ 
				return $orderBy;
			}

			return $orderBy .' DESC';
		} 
	}

	/**
	* buids the subquery for limit
	*/
	public function buildLimit($limit, $page = null)
	{
		if($page == null) return ' LIMIT ' . $limit;

		$limits = array();

		$limits[] = ($page - 1)* $limit; 
		$limits[] = $limit; 
		
		return ' LIMIT '. implode(', ', $limits);
	}

	/**
	*	builds the subquery for group by
	*/
	public function buildGroupBy($fields)
	{
		if(empty($fields)) return '';

		if(is_array($fields)) $fields = implode(', ' , $fields);

		return ' GROUP BY '. $fields;
	}


	public function buildInsertOnUpdateQuery(array &$data){
		$build = $this->buildInsertQuery($data);

		extract($build);

		$updateQuery = array();
		foreach($data as $field => $value)
		{
			$updateQuery[] = $field.="=VALUES(".$field.")"; 
		}

		$query .= " ON DUPLICATE KEY UPDATE ".implode(",",$updateQuery);

		$build['query'] = $query;

		return $build;
	}
 
 }
 
 
 /** END OF FILE **/