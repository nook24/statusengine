<?php
/**
* Copyright (C) 2015 Daniel Ziegler <daniel@statusengine.org>
*
* This file is part of Statusengine.
*
* Statusengine is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* (at your option) any later version.
*
* Statusengine is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Statusengine.  If not, see <http://www.gnu.org/licenses/>.
*/
class ServicegroupsController extends AppController{

	public $uses = [
		'Legacy.Servicegroup',
		'Legacy.Servicegroupmember',
		'Legacy.Servicestatus',
		//'Legacy.Service',
		'Legacy.Objects',
		'Legacy.Configvariable'
	];
	public $helpers = ['Status'];
	public $filter = [
		'index' => [
			'Servicestatus' => [
				'current_state' => ['type' => 'checkbox', 'value' => [
					0 => 'Ok',
					1 => 'Warning',
					2 => 'Critical',
					3 => 'Unknown'
				],
				'class' => 'col-xs-12 col-md-3'
				]
			],
			'Objects' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Service group name', 'submit' => false],
			],
			'ServiceObject' => [
				'name2' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Service description', 'submit' => true]
			]
		]
	];

	public function index(){
		$query = [
			'joins' => [
				[
					'table' => $this->Objects->tablePrefix.$this->Objects->table,
					'type' => 'INNER',
					'alias' => 'Objects',
					'conditions' => 'Objects.object_id = Servicegroup.servicegroup_object_id'
				],
				[
					'table' => $this->Servicegroupmember->tablePrefix.$this->Servicegroupmember->table,
					'type' => 'LEFT',
					'alias' => 'Servicegroupmember',
					'conditions' => 'Servicegroupmember.servicegroup_id = Servicegroup.servicegroup_id'
				],
				//[
				//	'table' => $this->Service->tablePrefix.$this->Service->table,
				//	'type' => 'INNER',
				//	'alias' => 'Service',
				//	'conditions' => 'Service.service_object_id = Servicegroupmember.service_object_id'
				//],
				[
					'table' => $this->Servicestatus->tablePrefix.$this->Servicestatus->table,
					'type' => 'INNER',
					'alias' => 'Servicestatus',
					'conditions' => 'Servicestatus.service_object_id = Servicegroupmember.service_object_id'
				],
				[
					'table' => $this->Objects->tablePrefix.$this->Objects->table,
					'type' => 'INNER',
					'alias' => 'ServiceObject',
					'conditions' => 'ServiceObject.object_id = Servicegroupmember.service_object_id'
				]
			],
			'fields' => [
				'Servicegroup.servicegroup_id',
				'Servicegroup.servicegroup_object_id',
				'Servicegroup.alias',

				'Servicegroupmember.servicegroup_id',
				'Servicegroupmember.service_object_id',

				//'Service.*',
				'Servicestatus.current_state',
				'Servicestatus.last_check',
				'Servicestatus.last_state_change',
				'Servicestatus.problem_has_been_acknowledged',
				'Servicestatus.scheduled_downtime_depth',
				'Servicestatus.output',

				'Objects.object_id',
				'Objects.name1',

				'ServiceObject.object_id',
				'ServiceObject.name2',
			],
			'order' => [
				'Objects.name1' => 'asc',
				'ServiceObject.name2' => 'asc'
			]
		];

		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		//Read: https://github.com/cakephp/cakephp/blob/2.7/lib/Cake/Controller/Component/PaginatorComponent.php#L121-L128
		$servicegroups = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Objects.name1']));
		$this->set(compact(['servicegroups']));
		$this->set('_serialize', ['servicegroups']);
	}
}
