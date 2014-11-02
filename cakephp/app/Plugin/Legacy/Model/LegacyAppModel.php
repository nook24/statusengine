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
*
* --------------------------------------------------------------------------------
*
* Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation in version 2
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* --------------------------------------------------------------------------------
*/

App::uses('AppModel', 'Model');

class LegacyAppModel extends AppModel{

	// Many thanks to Ceeram from #cakephp :-)
	public $saveTemplate = 'INSERT INTO `%s` (%s) VALUES %s ON DUPLICATE KEY UPDATE %s;';


	public function rawSave($data, $returnLastInserId = true){
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
		if($returnLastInserId){
			return $db->lastInsertId();
		}
		
		return true;
	}
	
	public function rawInsert($data, $returnLastInserId = true){
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
		if($returnLastInserId){
			return $db->lastInsertId();
		}
		return true;
	}
	
}
