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
		$this->query($data);
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
		$this->query($data);
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
		$this->query($data);
		if($returnLastInserId){
			return $db->lastInsertId();
		}
		return true;
	}
	
}
