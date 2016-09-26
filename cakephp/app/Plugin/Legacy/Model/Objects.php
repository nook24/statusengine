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
class Objects extends LegacyAppModel{
	public $useDbConfig = 'legacy';
	public $primaryKey = 'object_id';

	public function findList($objecttype_id = 1, $key = 'name1', $conditions = []){
		$_conditions = [
			'Objects.objecttype_id' => $objecttype_id,
			'Objects.is_active' => 1
		];

		$conditions = Hash::merge($_conditions, $conditions);

		$results = $this->find('all', [
			'conditions' => $conditions,
			'fields' => [
				'Objects.object_id',
				'Objects.name1',
				'Objects.name2'
			],
			'order' => [
				'Objects.'.$key => 'asc'
			]
		]);

		$list = [];
		foreach($results as $result){
			$list[$result['Objects']['object_id']] = $result['Objects'][$key];
		}
		return $list;
	}

	public function replace($data){
		$db = $this->getDataSource();

		//Insert new record
		$result = $db->fetchAll(
			'INSERT INTO
			'.$this->tablePrefix.$this->table.' (
				object_id, instance_id, objecttype_id, name1, name2, is_active
			)VALUES(
				:object_id, :instance_id, :objecttype_id, :name1, :name2, :is_active
			)
			ON DUPLICATE KEY UPDATE instance_id = :instance_id, objecttype_id = :objecttype_id, name1 = :name1, name2 = :name2, is_active = :is_active
			',
			[
				'object_id' => $data['Objects']['object_id'],
				'instance_id' => $data['Objects']['instance_id'],
				'objecttype_id' => $data['Objects']['objecttype_id'],
				'name1' => $data['Objects']['name1'],
				'name2' => $data['Objects']['name2'],
				'is_active' => $data['Objects']['is_active']
			]
		);

		if(isset($data['Objects']['object_id']) && is_numeric($data['Objects']['object_id'])){
			//record was updated
			return $data;
		}

		//new record
		$data['Objects']['object_id'] = $db->lastInsertId();
		return $data;

	}
}
