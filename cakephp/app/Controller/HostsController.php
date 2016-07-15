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
class HostsController extends AppController{

	public $uses = [
		'Legacy.Host',
		'Legacy.Hoststatus',
		'Legacy.Service',
		'Legacy.Servicestatus',
		'Legacy.Objects',
		'Legacy.Configvariable',
		'Legacy.Downtimehistory',
		'Legacy.Acknowledgement',
	];
	public $helpers = ['Status'];
	public $components = ['Externalcommands'];

	public $filter = [
		'index' => [
			'Hoststatus' => [
				'current_state' => ['type' => 'checkbox', 'value' => [
					0 => 'Up',
					1 => 'Down',
					2 => 'Unreachable'
				],
				'class' => 'col-xs-12 col-md-2'
				]
			],
			'Objects' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Search...', 'submit' => true]
			]
		]
	];

	public function index(){
		//Models are not linked for StatusengineLegacyShell, so we need to to the dirty job now :(
		$this->Hoststatus->primaryKey = 'host_object_id';
		$this->Host->bindModel([
			'belongsTo' => [
				'Objects' => [
					'className' => 'Legacy.Objects',
					'foreignKey' => 'host_object_id',
				],
				'Hoststatus' => [
					'className' => 'Legacy.Hoststatus',
					'foreignKey' => 'host_object_id'
				],
			]
		]);

		$query = [
			'fields' => [
				'Objects.object_id',
				'Objects.name1',

				'Host.host_id',
				'Host.host_object_id',
				'Host.alias',
				'Host.display_name',
				'Host.address',

				'Hoststatus.current_state',
				'Hoststatus.last_check',
				'Hoststatus.last_state_change',
				'Hoststatus.problem_has_been_acknowledged',
				'Hoststatus.scheduled_downtime_depth',
			],
			'order' => [
				'Objects.name1' => 'asc'
			]
		];

		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$hosts = $this->Paginator->paginate();

		//Get services + service status
		$hostObjectIds = Hash::extract($hosts, '{n}.Host.host_object_id');

		$stateTypes = [
			0 => 0,
			1 => 0,
			2 => 0,
			3 => 0
		];

		foreach($hostObjectIds as $hostObjectId){
			$servicestatus[$hostObjectId] = $stateTypes;
			$_servicestatus = $this->Service->find('all', [
				'fields' => [
					//'Service.host_object_id',
					//'Service.service_object_id',
					'Servicestatus.current_state',
					'COUNT(*) AS count'
				],
				'group' => [
					'Servicestatus.current_state'
				],
				'conditions' => [
					'Service.host_object_id' => $hostObjectId
				],
				'joins' => [
					[
						'table' => $this->Servicestatus->tablePrefix.$this->Servicestatus->table,
						'type' => 'INNER',
						'alias' => 'Servicestatus',
						'conditions' => 'Servicestatus.service_object_id = Service.service_object_id'
					]
				]
			]);

			foreach($_servicestatus as $state){
				$servicestatus[$hostObjectId][$state['Servicestatus']['current_state']] = $state[0]['count'];
			}
		}

		$this->set(compact([
			'hosts',
			'servicestatus'
		]));
		$this->set('_serialize', ['hosts']);
	}

	public function details($hostObjectId = null){
		if(!$this->Objects->exists($hostObjectId)){
			throw new NotFoundException(__('Host not found'));
		}

		$hoststatus = $this->Hoststatus->findByHostObjectId($hostObjectId);
		$object = $this->Objects->findByObjectId($hostObjectId);
		$host = $this->Host->find('first', [
			'conditions' => [
				'Host.host_object_id' => $hostObjectId
			],
			'fields' => [
				'Host.address',
				'Host.display_name'
			]
		]);

		$this->Service->primaryKey = 'service_object_id';
		$this->Servicestatus->primaryKey = 'service_object_id';
		$this->Service->bindModel([
			'hasOne' => [
				'Servicestatus' => [
					'foreignKey' => 'service_object_id'
				]
			],
			'belongsTo' => [
				'Objects' => [
					'foreignKey' => 'service_object_id'
				]
			]
		]);
		$services = $this->Service->find('all', [
			'conditions' => [
				'Service.host_object_id' => $hostObjectId
			],
			'fields' => [
				'Objects.name2',
				'Service.service_object_id',
				'Servicestatus.current_state',
				'Servicestatus.current_state',
				'Servicestatus.last_state_change',
				'Servicestatus.last_check',
				'Servicestatus.next_check',
				'Servicestatus.output',
				'Servicestatus.problem_has_been_acknowledged',
				'Servicestatus.scheduled_downtime_depth',
			],
			'order' => [
				'Servicestatus.current_state' => 'DESC',
				'Servicestatus.last_state_change' => 'DESC'
			]
		]);

		$this->Externalcommands->checkCmd();

		$this->Frontend->setJson('url', Router::url(['controller' => 'Externalcommands', 'action' => 'receiver']));
		$this->Frontend->setJson('currentUrl', Router::url(['controller' => 'Hosts', 'action' => 'details', $hostObjectId]));
		$this->Frontend->setJson('hostObjectId', $hostObjectId);

		$downtime = [];
		if(isset($hoststatus['Hoststatus']['scheduled_downtime_depth']) && $hoststatus['Hoststatus']['scheduled_downtime_depth'] > 0){
			$downtime = $this->Downtimehistory->findByObjectId($hostObjectId);
		}
		$acknowledgement = [];
		if(isset($hoststatus['Hoststatus']['problem_has_been_acknowledged']) && $hoststatus['Hoststatus']['problem_has_been_acknowledged'] == 1){
			$acknowledgement = $this->Acknowledgement->findByObjectId($hostObjectId);
		}

		$this->set(compact([
			'host',
			'hoststatus',
			'object',
			'services',
			'downtime',
			'acknowledgement',
		]));
		$this->set('_serialize', [
			'host',
			'hoststatus',
			'object',
			'services',
			'downtime',
			'acknowledgement',
		]);
	}
}
