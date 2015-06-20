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
		'Legacy.Objects'
	];
	public $helpers = ['Status'];
	
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
		
		$options = [
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
		
		$this->Paginator->settings = Hash::merge($options, $this->Paginator->settings);
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
						'table' => 'servicestatus',
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
		
		Configure::load('Interface');
		$commandFileError = false;
		if(!is_writable(Configure::read('Interface.command_file'))){
			$commandFileError = 'External command file '.Configure::read('Interface.command_file').' is not writable';
		}
		if(!file_exists(Configure::read('Interface.command_file'))){
			$commandFileError = 'External command file '.Configure::read('Interface.command_file').' does not exists';
		}
		
		$this->Frontend->setJson('hostObectId', $hostObjectId);
		$this->set(compact([
			'host',
			'hoststatus',
			'object',
			'commandFileError'
		]));
		$this->set('_serialize', [
			'host',
			'hoststatus',
			'object',
			'commandFileError'
		]);
	}
}
