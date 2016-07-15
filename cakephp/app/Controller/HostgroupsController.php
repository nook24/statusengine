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
class HostgroupsController extends AppController{

	public $uses = [
		'Legacy.Hostgroup',
		'Legacy.Hostgroupmember',
		//'Legacy.Host',
		'Legacy.Hoststatus',
		'Legacy.Objects',
		'Legacy.Configvariable'
	];
	public $helpers = ['Status'];
	public $filter = [
		'index' => [
			'Hoststatus' => [
				'current_state' => ['type' => 'checkbox', 'value' => [
					0 => 'Up',
					1 => 'Down',
					2 => 'Unreachable',
				],
				'class' => 'col-xs-12 col-md-4'
				]
			],
			'Objects' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Host group name', 'submit' => false],
			],
			'HostObject' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Host name', 'submit' => true]
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
					'conditions' => 'Objects.object_id = Hostgroup.hostgroup_object_id'
				],
				[
					'table' => $this->Hostgroupmember->tablePrefix.$this->Hostgroupmember->table,
					'type' => 'LEFT',
					'alias' => 'Hostgroupmember',
					'conditions' => 'Hostgroupmember.hostgroup_id = Hostgroup.hostgroup_id'
				],
				//[
				//	'table' => $this->Host->tablePrefix.$this->Host->table,
				//	'type' => 'INNER',
				//	'alias' => 'Host',
				//	'conditions' => 'Host.host_object_id = Hostgroupmember.host_object_id'
				//],
				[
					'table' => $this->Hoststatus->tablePrefix.$this->Hoststatus->table,
					'type' => 'INNER',
					'alias' => 'Hoststatus',
					'conditions' => 'Hoststatus.host_object_id = Hostgroupmember.host_object_id'
				],
				[
					'table' => $this->Objects->tablePrefix.$this->Objects->table,
					'type' => 'INNER',
					'alias' => 'HostObject',
					'conditions' => 'HostObject.object_id = Hostgroupmember.host_object_id'
				]
			],
			'fields' => [
				'Hostgroup.hostgroup_id',
				'Hostgroup.hostgroup_object_id',
				'Hostgroup.alias',

				'Hostgroupmember.hostgroup_id',
				'Hostgroupmember.host_object_id',

				//'Host.*',
				'Hoststatus.current_state',
				'Hoststatus.last_check',
				'Hoststatus.last_state_change',
				'Hoststatus.output',
				'Hoststatus.problem_has_been_acknowledged',
				'Hoststatus.scheduled_downtime_depth',

				'Objects.object_id',
				'Objects.name1',

				'HostObject.object_id',
				'HostObject.name1',
			],
			'order' => [
				'Objects.name1' => 'asc',
				'HostObject.name1' => 'asc'
			]
		];

		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		//Read: https://github.com/cakephp/cakephp/blob/2.7/lib/Cake/Controller/Component/PaginatorComponent.php#L121-L128
		$hostgroups = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Objects.name1']));
		$this->set(compact(['hostgroups']));
		$this->set('_serialize', ['hostgroups']);
	}
}
