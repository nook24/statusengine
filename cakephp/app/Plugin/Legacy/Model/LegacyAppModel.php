<?php
/**********************************************************************************
*
*    #####
*   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######
*   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #
*    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####
*         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #
*   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #
*    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######
*
*                            the missing event broker
*/

App::uses('AppModel', 'Model');

class LegacyAppModel extends AppModel{

	/**
	*
	* rawSave
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @copyright 2014 - present Marc Ypes, The Netherlands
	* @author Ceeram
	* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	*/
	public function rawSave($data, $returnLastInserId = true){
		$this->saveTemplate = 'SET NAMES utf8; INSERT INTO `%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';
		if(empty($data)) {
			return true;
		}

		$data = Set::extract('{n}.' . $this->alias, $data);
		$duplicate_data = [];

		$schema = $this->schema();
		unset($schema['id']);
		$keyData = '`' . implode('`, `', array_keys($schema)) . '`';

		$db = $this->getDataSource();

		foreach($data as $k => $row) {
			foreach ($row as $field => $value) {
				$row[$field] = $db->value($value, $field);
			}

			//Insert on duplicate key update syntax
			foreach($row as $column => $_value){
				if($column != $this->primaryKey){
					$duplicate_data[] = $column.'='.$_value;
				}
			}

			$data[$k] = "(" . implode(", ", $row) . ")";
		}
		$data = sprintf($this->saveTemplate, $this->tablePrefix.$this->table, $keyData, implode(', ', $data), implode(',',$duplicate_data));
		$this->sqlQuery($data);
		if($returnLastInserId){
			return $db->lastInsertId();
		}

		return true;
	}

	public function saveHoststatus($data, $returnLastInserId = true){
		$schema = array_keys($data['Hoststatus']);
		$data = [$data];
		return $this->rawSaveStatus($data, $returnLastInserId, $schema);
	}

	public function saveServicestatus($data, $returnLastInserId = true){
		$schema = array_keys($data['Servicestatus']);
		$data = [$data];
		return $this->rawSaveStatus($data, $returnLastInserId, $schema);
	}

	/**
	*
	* rawSaveStatus
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @copyright 2014 - present Marc Ypes, The Netherlands
	* @author Ceeram
	* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	*/
	public function rawSaveStatus($data, $returnLastInserId = true, $schema){
		$this->saveTemplate = 'SET NAMES utf8; INSERT INTO `%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';
		if(empty($data)) {
			return true;
		}

		$data = Set::extract('{n}.' . $this->alias, $data);
		$duplicate_data = [];

		if(isset($schema['id'])){
			unset($schema['id']);
		}

		if(isset($schema['servicestatus_id'])){
			unset($schema['servicestatus_id']);
		}

		if(isset($schema['hoststatus_id'])){
			unset($schema['hoststatus_id']);
		}

		$keyData = '`' . implode('`, `', $schema) . '`';

		$db = $this->getDataSource();

		foreach($data as $k => $row) {
			foreach ($row as $field => $value) {
				$row[$field] = $db->value($value, $field);
			}

			//Insert on duplicate key update syntax
			foreach($row as $column => $_value){
				if($column != $this->primaryKey){
					$duplicate_data[] = $column.'='.$_value;
				}
			}

			$data[$k] = "(" . implode(", ", $row) . ")";
		}
		$data = sprintf($this->saveTemplate, $this->tablePrefix.$this->table, $keyData, implode(', ', $data), implode(',',$duplicate_data));
		$this->sqlQuery($data);
		if($returnLastInserId){
			return $db->lastInsertId();
		}

		return true;
	}

	/**
	*
	* rawSaveServicestatus
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @copyright 2014 - present Marc Ypes, The Netherlands
	* @author Ceeram
	* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	*/
	public function rawSaveServicestatus($data, $returnLastInserId = true){
		$this->saveTemplate = 'SET NAMES utf8; INSERT INTO `%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';
		if(empty($data)) {
			return true;
		}

		$data = Set::extract('{n}.' . $this->alias, $data);
		$duplicate_data = [];

		$schema = $this->schema();
		unset($schema['id']);
		unset($schema['servicestatus_id']);
		$keyData = '`' . implode('`, `', array_keys($schema)) . '`';

		$db = $this->getDataSource();

		foreach($data as $k => $row) {
			foreach ($row as $field => $value) {
				$row[$field] = $db->value($value, $field);
			}

			//Insert on duplicate key update syntax
			foreach($row as $column => $_value){
				if($column != $this->primaryKey){
					$duplicate_data[] = $column.'='.$_value;
				}
			}

			$data[$k] = "(" . implode(", ", $row) . ")";
		}
		$data = sprintf($this->saveTemplate, $this->tablePrefix.$this->table, $keyData, implode(', ', $data), implode(',',$duplicate_data));
		$this->sqlQuery($data);
		if($returnLastInserId){
			return $db->lastInsertId();
		}

		return true;
	}

	/**
	*
	* rawInsert
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @copyright 2014 - present Marc Ypes, The Netherlands
	* @author Ceeram
	* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	*/
	public function rawInsert($data, $returnLastInserId = true){
		$this->saveTemplate = 'SET NAMES utf8; INSERT INTO `%s` (%s) VALUES %s ;';
		if(empty($data)) {
			return true;
		}

		$data = Set::extract('{n}.' . $this->alias, $data);
		$duplicate_data = [];
		$schema = $this->schema();
		unset($schema[$this->primaryKey]);
		$keyData = '`' . implode('`, `', array_keys($schema)) . '`';

		$db = $this->getDataSource();
		foreach($data as $k => $row) {
			foreach ($row as $field => $value) {
				$row[$field] = $db->value($value, $field);
			}

			$data[$k] = "(" . implode(", ", $row) . ")";
		}
		$data = sprintf($this->saveTemplate, $this->tablePrefix.$this->table, $keyData, implode(', ', $data));
		$this->sqlQuery($data);
		if($returnLastInserId){
			return $db->lastInsertId();
		}
		return true;
	}

	/**
	*
	* sqlSave
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @copyright 2014 - present Daniel Ziegler
	* @author Ceeram
	* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	*/
	public function sqlSave($data, $recursive = false){
		try{
			$this->save($data);
		}catch(Exception $e){
			$error = $e->getMessage();
			if($error == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'){
				if($recursive === false){
					$this->getDatasource()->reconnect();
					sleep(1);
					$this->getDatasource()->reconnect();
					return $this->sqlSave($data, true);
				}
			}
			CakeLog::error($e->getMessage());
		}
	}

	/**
	*
	* sqlQuery
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @copyright 2014 - present Daniel Ziegler
	* @author Ceeram
	* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	*/
	public function sqlQuery($data, $recursive = false){
		try{
			$this->query($data);
		}catch(Exception $e){
			$error = $e->getMessage();
			print_r($error.PHP_EOL);
			if($error == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'){
				if($recursive === false){
					$this->getDatasource()->reconnect();
					sleep(1);
					$this->getDatasource()->reconnect();
					return $this->sqlQuery($data, true);
				}
			}
			CakeLog::error($e->getMessage());
		}
	}

	public function save($data = null, $validate = true, $fieldList = [], $recursive = false){
		try{
			return parent::save($data, $validate, $fieldList);
		}catch(Exception $e){
			$error = $e->getMessage();
			if($error == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'){
				if($recursive === false){
					$this->getDatasource()->reconnect();
					sleep(1);
					$this->getDatasource()->reconnect();
					return $this->save($data, $validate, $fieldList, true);
				}
			}
			CakeLog::error($e->getMessage());
		}
	}

	public function find($type = 'first', $query = [], $recursive = false){
		try{
			return parent::find($type, $query);
		}catch(Exception $e){
			$error = $e->getMessage();
			if($error == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'){
				if($recursive === false){
					$this->getDatasource()->reconnect();
					sleep(1);
					$this->getDatasource()->reconnect();
					return $this->find($type, $query, true);
				}
			}
			CakeLog::error($e->getMessage());
		}
	}

	public function truncate($recursive = false){
		$db = $this->getDataSource();

		$dbName = $db->config['database'];
		$tableName = $this->tablePrefix.$this->table;
		$query = sprintf('TRUNCATE `%s`.`%s`', $dbName, $tableName);

		try{
			$this->sqlQuery($query);
		}catch(Exception $e){
			$error = $e->getMessage();
			if($error == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'){
				if($recursive === false){
					$this->getDatasource()->reconnect();
					sleep(1);
					$this->getDatasource()->reconnect();
					return $this->truncate(true);
				}
			}
			CakeLog::error($e->getMessage());
		}
	}
	
	/**
	 * @param array $data => default cakephp array to save data
	 */
	public function saveOnDuplicate($data){
		$db = $this->getDataSource();

		//Insert new record
		$schema = array_keys($data[$this->name]);
		
		$queryTemplate = 'INSERT INTO %s (%s)VALUES(%s) ON DUPLICATE KEY UPDATE %s';
		$table = $this->tablePrefix.$this->table;
		//object_id, instance_id, objecttype_id, name1, name2, is_active
		$columns = implode(', ', $schema);
		
		//:object_id, :instance_id, :objecttype_id, :name1, :name2, :is_active
		$_schema = [];
		foreach($schema as $_key){
			$_schema[] = sprintf(':%s', $_key);
		}
		$values = implode(', ', $_schema);
		
		//instance_id = :instance_id, objecttype_id = :objecttype_id, name1 = :name1, name2 = :name2, is_active = :is_active
		$_schema = [];
		foreach($schema as $_key){
			$_schema[] = sprintf('%s = :%s', $_key, $_key);
		}
		$onDumplicate = implode(', ', $_schema);
		
		$query = sprintf($queryTemplate, $table, $columns, $values, $onDumplicate);
	
		$result = $db->fetchAll(
			$query,
			$data[$this->name]
			//[
			//	'object_id' => $data[$this->name]['object_id'],
			//	'instance_id' => $data[$this->name]['instance_id'],
			//	'objecttype_id' => $data[$this->name]['objecttype_id'],
			//	'name1' => $data[$this->name]['name1'],
			//	'name2' => $data[$this->name]['name2'],
			//	'is_active' => $data[$this->name]['is_active']
			//]
		);

	}
}
