<?php

App::uses('AppModel', 'Model');

class LegacyAppModel extends AppModel {

	// Many thanks to Ceeram from #cakephp :-)
	public $saveTemplate = 'INSERT INTO `%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';


	public function rawSave($data) {
		$this->saveTemplate = 'INSERT INTO `%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';
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
		return $db->lastInsertId();
		//return true;
	}
	
	public function rawInsert($data) {
		$this->saveTemplate = 'INSERT INTO `%s` (%s) VALUES %s ;';
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
		return $db->lastInsertId();

		//return true;
	}
	
	public function insertObjects($data){
		$db = $this->getDataSource();
		$query = 'INSERT HIGH_PRIORITY INTO nagios_objects (`instance_id`, `objecttype_id`, `name1`, `name2`, `is_active`) VALUES ("'.$data['instance_id'].'","'.$data['objecttype_id'].'","'.$data['name1'].'","'.$data['name2'].'","'.$data['is_active'].'")';
		$this->query($query);
		return $db->lastInsertId();
	}
	
	public function updateObjects(){
		$db = $this->getDataSource();
		$query = 'UPDATE HIGH_PRIORITY nagios_objects SET `instance_id` = "'.$data['instance_id'].'", `objecttype_id` = "'.$data['objecttype_id'].'", `name1` = "'.$data['name1'].'", `name2` = "'.$data['name2'].'", `is_active` = "'.$data['is_active'].'" WHERE `object_id` = '.$data['object_id'];
		$this->query($query);
	}
}
