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
class ServicesController extends AppController{

	public $uses = [
		'Legacy.Service',
		'Legacy.Host',
		'Legacy.Servicestatus',
		//'Legacy.Hoststatus',
		'Legacy.Objects',
		'Legacy.Configvariable',
		'Rrdtool',
		'Legacy.Downtimehistory',
		'Legacy.Acknowledgement',
	];
	public $helpers = ['Status'];
	public $components = ['Externalcommands'];

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
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Host name', 'submit' => false],
				'name2' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Service description', 'submit' => true]
			]
		]
	];

	public function index(){
		//Models are not linked for StatusengineLegacyShell, so we need to to the dirty job now :(
		$this->Service->primaryKey = 'service_object_id';
		$this->Servicestatus->primaryKey = 'service_object_id';
		$this->Host->primaryKey = 'host_object_id';

		$query = [
			'bindModels' => true,
			'fields' => [
				'Objects.name1',
				'Objects.name2',

				'Host.host_object_id',

				'Service.service_id',
				'Service.service_object_id',
				'Service.host_object_id',

				'Servicestatus.current_state',
				'Servicestatus.last_check',
				'Servicestatus.last_state_change',
				'Servicestatus.problem_has_been_acknowledged',
				'Servicestatus.scheduled_downtime_depth',
				'Servicestatus.output',

			],
			'order' => [
				'Objects.name1' => 'asc'
			]
		];
		if(isset($this->Paginator->settings['order'])){
			unset($this->Paginator->settings['order']);
		}
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);

		//Read: https://github.com/cakephp/cakephp/blob/2.7/lib/Cake/Controller/Component/PaginatorComponent.php#L121-L128
		$services = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Objects.name1']));
		$this->set(compact([
			'services',
		]));
		$this->set('_serialize', ['services']);
	}

	public function details($serviceObjectId = null){
		if(!$this->Objects->exists($serviceObjectId)){
			throw new NotFoundException(__('Service not found'));
		}

		$servicestatus = $this->Servicestatus->findByServiceObjectId($serviceObjectId);
		$object = $this->Objects->findByObjectId($serviceObjectId);
		$service = $this->Service->find('first', [
			'conditions' => [
				'Service.service_object_id' => $serviceObjectId
			],
			'fields' => [
				'Service.host_object_id',
			]
		]);

		$downtime = [];
		if(isset($servicestatus['Servicestatus']['scheduled_downtime_depth']) && $servicestatus['Servicestatus']['scheduled_downtime_depth'] > 0){
			$downtime = $this->Downtimehistory->findByObjectId($serviceObjectId);
		}

		$acknowledgement = [];
		if(isset($servicestatus['Servicestatus']['problem_has_been_acknowledged']) && $servicestatus['Servicestatus']['problem_has_been_acknowledged'] == 1){
			$acknowledgement = $this->Acknowledgement->find('first', [
				'Acknowledgement.object_id' => $serviceObjectId,
				'order' => [
					'entry_time' => 'desc'
				]
			]);
		}
		
		$this->Externalcommands->checkCmd();

		$datasources = [];
		$xmlError = true;
		if($this->Rrdtool->hasGraph($object['Objects']['name1'], $object['Objects']['name2'])){
			$xmlError = $this->Rrdtool->isXmlParsable($object['Objects']['name1'], $object['Objects']['name2']);
			if($xmlError === true){
				// true means no error ;)
				$datasources = $this->Rrdtool->parseXml($object['Objects']['name1'], $object['Objects']['name2']);
			}
		}

		$this->Frontend->setJson('url', Router::url(['controller' => 'Externalcommands', 'action' => 'receiver']));
		$this->Frontend->setJson('currentUrl', Router::url(['controller' => 'Services', 'action' => 'details', $serviceObjectId]));
		$this->Frontend->setJson('serviceObjectId', $serviceObjectId);
		$this->set(compact([
			'service',
			'servicestatus',
			'object',
			'commandFileError',
			'datasources',
			'downtime',
			'acknowledgement',
			'xmlError'
		]));
		$this->set('_serialize', [
			'service',
			'servicestatus',
			'object',
			'commandFileError',
			'downtime',
			'acknowledgement',
		]);
	}

	public function problem(){
		$this->Service->primaryKey = 'service_object_id';
		$this->Servicestatus->primaryKey = 'service_object_id';
		$this->Host->primaryKey = 'host_object_id';

		$query = [
			'bindModels' => true,
			'fields' => [
				'Objects.name1',
				'Objects.name2',

				'Host.host_object_id',

				'Service.service_id',
				'Service.service_object_id',
				'Service.host_object_id',

				'Servicestatus.current_state',
				'Servicestatus.last_check',
				'Servicestatus.last_state_change',
				'Servicestatus.problem_has_been_acknowledged',
				'Servicestatus.scheduled_downtime_depth',
				'Servicestatus.output',

			],
			'order' => [
				'Objects.name1' => 'asc'
			],
			'conditions' => [
				'Servicestatus.current_state > ' => 0,
				'Servicestatus.problem_has_been_acknowledged' => 0,
				'Servicestatus.scheduled_downtime_depth' => 0
			]
		];
		if(isset($this->Paginator->settings['order'])){
			unset($this->Paginator->settings['order']);
		}
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);

		//Read: https://github.com/cakephp/cakephp/blob/2.7/lib/Cake/Controller/Component/PaginatorComponent.php#L121-L128
		$services = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Objects.name1']));
		$this->set(compact([
			'services',
		]));
		$this->set('_serialize', ['services']);
	}
}
