<?php
class UniDatabase 
{
	private $db_handle = NULL;
	private $db_type = '';
	
	function __construct($master_slave) 
	{ 
		$this->db_handle =& wfGetDB($master_slave);
	} 
	
	################################################################
	### BASIC FUNCTIONS                                          ###
	################################################################
	
	public function open($host, $user, $pass, $database)
	{
		return true;
	}
	
	public function close()
	{
		return true;
	}
	
	public function query($query)
	{
		return $this->db_handle->query($query);
	}
	
	public function getRow($data)
	{
		return $this->db_handle->fetchRow($data);
	}
	
	public function getObject($data)
	{
		return $this->db_handle->fetchObject($data);
	}
	
	public function getNumRows($data)
	{
		return 0;
	}
	
	public function getHandle()
	{
		return $this->db_handle;
	}
	
	public function fetchObject($data)
	{
		return $this->getObject($data);
	}
	
	public function fetchRow($data)
	{
		return $this->getRow($data);
	}
	 
	################################################################
	### SPECIAL FUNCTIONS		                                 ###
	################################################################
	
	public function tableName($name)
	{
		return $this->db_handle->tableName($name);
	}
	
	################################################################
	### ELEMENTARY QUERY FUNCTIONS                               ###
	################################################################
	public function select_sql($select, $table, $where = NULL, $optional = array())
	{			
		$sql = 'SELECT ' . $select . ' FROM ' . $this->addPrefix($table) . $this->getWhereString($where) . $this->getOptionalString($optional);
		return $sql;
	}
	
	public function select($select, $table, $where = NULL, $optional = array())
	{			
		$sql = $this->select_sql($select, $table, $where, $optional);
		return $this->query($sql);
	}
	
	public function insert($table, $array)
	{
		$fields = array();
		$values = array();
		
		if(is_array($array))
		{
			foreach ($array as $key => $value) 
			{
				array_push($fields,$key);
				array_push($values,$this->prepareValue($value));
			}
			
			$s_fields	= implode(',',$fields);
			$s_values	= implode(',',$values);
			
			$sql = 'INSERT INTO '. $this->addPrefix($table) . ' (' . $s_fields . ') VALUES (' . $s_values . ')';
			return $this->query($sql);
		}
		else return false;
	}
	
	public function delete($table, $where)
	{
		$sql = 'DELETE FROM ' . $this->addPrefix($table) . $this->getWhereString($where);
		return $this->query($sql);
	}
	
	public function update($table, $values, $where = NULL)
	{
		$sql = 'UPDATE ' . $this->addPrefix($table) . ' SET ' . $this->getString($values, ', ') . $this->getWhereString($where);
		return $this->query($sql);
	}
	
	################################################################
	### ADDITIONAL QUERY FUNCTIONS                               ###
	################################################################
	
	public function count($table, $where = NULL)
	{
		$options['LIMIT'] = 1;
		$result = $this->select('COUNT(*) as num', $table, $where, $options);
		$count	= $this->getObject($result);
		return intval($count->num);
	}
	
	public function exists($table, $where = NULL)
	{
		$options['LIMIT'] = 1;
		$result = $this->select('*', $table, $where, $options);
		$exists	= $this->getObject($result);
		if($exists == false) return false;
			else return true;
	}
	
	public function plus($table, $value, $where = NULL)
	{
		$plus = $value . '=' . $value . '+1';
		return $this->update($table, $plus, $where);
	}
	
	public function minus($table, $value, $where = NULL)
	{
		$minus = $value . '=' . $value . '-1';
		return $this->update($table, $minus, $where);
	}
	
	################################################################
	### ADVANCED QUERY FUNCTIONS                                 ###
	################################################################
	

	
	################################################################
	### HELP FUNCTIONS (public)                                  ###
	################################################################
	
	public function limit($query, $limit, $offset = 0)
	{	
		return $this->db_handle->limitResult($query, $limit, $offset);
	}
		
	public function escape($var)
	{
		$var = $this->db_handle->real_escape_string($var);
		return $var;
	}

	public function addPrefix($table)
	{
		$table = $this->db_prefix.$table;
		return '`'.$table.'`';
	}
	
	################################################################
	### HELP FUNCTIONS (private)                                 ###
	################################################################
	
	private function getOptionalString($option)
	{	
		$optional_string = '';
		if(isset($option['ORDER'])) $optional_string .= ' ORDER BY ' . $option['ORDER'];
		if(isset($option['LIMIT'])) 
		{
			$option['LIMIT']	= $this->checkInt($option['LIMIT']);
			$option['OFFSET']	= $this->checkInt($option['OFFSET']);
			$optional_string .= ' LIMIT ' . $option['OFFSET'] . ', ' . $option['LIMIT'];
		}
		
		return $optional_string;
	}
	
	private function getWhereString($where)
	{	
		if($where == NULL) 	return '';
		else				return ' WHERE ' . $this->getString($where, ' AND ');
	}
	
	public function addQuotes($var)
	{	
		return $this->db_handle->addQuotes($var);
	}
	
	private function checkInt($var)
	{	
		if(is_numeric($var)) 
		{
			if(is_int($var))	return $var;
				else			return intval($var);
		}
		return 0;
	}
	
	private function getString($where, $sep)
	{	
		if(is_array($where) == true)
		{
			$fields = array();
			foreach ($where as $key => $value) {
				array_push($fields, $key . '=' . $this->prepareValue($value));
			}
			$s_fields	= implode($sep, $fields);
		}
		else $s_fields = $where;
		return $s_fields;
	}
		
	/**
	 * Function transforms a searchstring in several strings with different priorisation for search.
	 * In general it checks first if quotes are used in searchstring and if yes the string between the
	 * quotes will be stick together and not used with wildchars. In last step all blanks will be
	 * replaced with wildchars.
	 * @param	string 	< string with data to search	
	 * @return	ARRAY	> array with searchstrings with different prios / [0] < highest prio
	 */
	public function searchString($searchstring)
	{	
		$results = array();
		$searchstring = trim($searchstring);
		$searchstring = stripslashes($searchstring);
		
		if(strpos($searchstring, '"') !== false)
		{
			$ex_string = explode('"', $searchstring);
			for($i=0; $i<sizeof($ex_string); $i++)
			{
				if($i % 2 == 0)
				{
					$ex_string[$i] = str_replace(' ', '%', trim($ex_string[$i]));
				}
			}
			$searchstring = $this->searchStringWild(implode('%', $ex_string));
			array_push($results, $searchstring);
		}
		
		$str_search		= $this->searchStringWild(str_replace(' ', '%', $searchstring));
		$num_results 	= sizeof($results);
		if($num_results == 0 || $results[$num_results-1] != $str_search) array_push($results, $str_search);
		
		return $results;
	}
		
	/**
	 * Function checks if wildchars for already set in front and on end of searchstring.
	 * If not, then the wildchars (%) will be set.
	 * @param	string 	< string with data to search		
	 * @return	STRING	> searchstring with wildchars in front and on end
	 */
	private function searchStringWild($searchstring)
	{	
		if($searchstring[0] != '%') $searchstring = '%'.$searchstring;
		if($searchstring[strlen($searchstring)-1] != '%') $searchstring = $searchstring.'%';
		return $searchstring;
	}
	
}
?>