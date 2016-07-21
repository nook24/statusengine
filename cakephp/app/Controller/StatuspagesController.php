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
class StatuspagesController extends AppController{

	public $uses = [
		'Statuspage',
		'Legacy.Host',
		'Legacy.Service',
		'Legacy.Objects',
		'Rrdtool'
	];

	public function index(){
		return;

		$statuspages = $this->Paginator->paginate();
		$this->set(compact(['statuspages']));
	}

	public function chooseHost(){
		return;

		$this->Host->bindModel([
			'belongsTo' => [
				'Objects' => [
					'className' => 'Legacy.Objects',
					'foreignKey' => 'host_object_id',
				]
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
			],
			'order' => [
				'Objects.name1' => 'asc'
			],
			'conditions' => [
				'Objects.is_active' => 1
			]
		];

		$result = $this->Host->find('all', $query);
		$hosts = [];
		foreach($result as $host){
			$hosts[$host['Objects']['object_id']] = $host['Objects']['name1'];
		}
		$this->set('hosts', $hosts);
	}

	public function chooseServices(){
		return;

		$hostObjectId = $this->request->data('Statuspages.host_object_id');
		$host = $this->Host->findByhostObjectId($hostObjectId);
		$hostObject = $this->Objects->find('first', [
			'conditions' => [
				'object_id' => $hostObjectId
			]
		]);
		if(empty($host)){
			$this->setFlash(__('Host does not exists'), false);
			$this->redirect(['action' => 'index']);
		}

		if($this->request->is('post') || $this->request->is('put')){
			if($this->request->data('Statuspages.save_services') == 1){

				$data = [
					'uuid' => CakeText::uuid(),
					'name' => $this->request->data('Statuspages.name'),
					'host_object_id' => $this->request->data('Statuspages.host_object_id')
				];
				$config = [];
				$enabledServices = array_filter($this->request->data('enabled_services'));
				$selectedServices = $this->request->data('services');
				foreach($enabledServices as $serviceObjectId => $value){
					if(isset($selectedServices[$serviceObjectId]['graph'])){
						foreach($selectedServices[$serviceObjectId]['graph'] as $ds => $graph){
							if($graph['enabled'] == 1){
								$config[$serviceObjectId][] = [
									'datasource' => $ds,
									'name' => $graph['name'],
									'unit' => $graph['name']
								];
							}
						}
					}
				}

				$data['configuration'] = json_encode($config);
				$this->Statuspage->create();
				$this->Statuspage->save($data);
				$this->setFlash(__('Status page created successfully'));
				$this->redirect(['action' => 'index']);
			}
		}

		$this->Service->primaryKey = 'service_object_id';
		$this->Objects->useDbConfig = 'legacy';
		$this->Service->bindModel([
			'belongsTo' => [
				'Objects' => [
					'foreignKey' => 'service_object_id'
				]
			]
		]);
		$_services = $this->Service->find('all', [
			'conditions' => [
				'Service.host_object_id' => $hostObjectId
			],
			'fields' => [
				'Objects.name2',
				'Service.service_object_id',
				'Service.display_name',
				'Service.notes'
			],
			'order' => [
				'Objects.name2' => 'asc',
			]
		]);

		$services = [];
		foreach($_services as $service){
			$datasources = [];
			$xmlError = true;
			if($this->Rrdtool->hasGraph($hostObject['Objects']['name1'], $service['Objects']['name2'])){
				$xmlError = $this->Rrdtool->isXmlParsable($hostObject['Objects']['name1'], $service['Objects']['name2']);
				if($xmlError === true){
					// true means no error ;)
					$datasources = $this->Rrdtool->parseXml($hostObject['Objects']['name1'], $service['Objects']['name2']);
				}
			}
			$services[] = Hash::merge($service, ['Graph' => $datasources]);
		}

		$this->set(compact([
			'services',
			'hostObjectId',
			'host'
		]));
	}

	public function query($uuid = null){
		return;
		
		$data = $this->Statuspage->findByUuid($uuid);
		if(empty($data)){
			throw new NotFoundException('Status page not found');
		}

		if($this->request->ext !== 'json'){
			//throw new MethodNotAllowedException();
		}

		$data['Statuspage']['configuration'] = json_decode($data['Statuspage']['configuration'], true);

		$metrics = [];
		$services = [];
		foreach($data['Statuspage']['configuration'] as $serviceObjectId => $graphs){
			$this->Service->primaryKey = 'service_object_id';
			$this->Objects->useDbConfig = 'legacy';
			$this->Service->bindModel([
				'belongsTo' => [
					'Objects' => [
						'foreignKey' => 'service_object_id'
					]
				]
			]);
			$service = $this->Service->find('first', [
				'conditions' => [
					'Service.service_object_id' => $serviceObjectId
				],
				'fields' => [
					'Objects.name1',
					'Objects.name2',
					'Service.service_object_id',
					'Service.display_name',
					'Service.notes'
				]
			]);

			$services[$service['Service']['service_object_id']] = [
				'name' => $service['Service']['display_name'],
				'description' => ($service['Service']['notes'] == '0')?'':$service['Service']['notes'],
			];

			$xmlError = true;
			if($this->Rrdtool->hasGraph($service['Objects']['name1'], $service['Objects']['name2'])){
				$xmlError = $this->Rrdtool->isXmlParsable($service['Objects']['name1'], $service['Objects']['name2']);
				if($xmlError === true){
					// true means no error ;)
					$rrdData = $this->Rrdtool->fetch($service['Objects']['name1'], $service['Objects']['name2']);
					$rrdData = $rrdData['data'];
				}
			}

			foreach($graphs as $graph){
				if(isset($rrdData[$graph['datasource']])){

					$_data = [];
					foreach($rrdData[$graph['datasource']] as $timestamp => $value){
						if(is_nan($value)){
							$value = null;
						}
						$_data[$timestamp] = $value;
					}

					$metrics[$serviceObjectId] = [
						'name' => $graph['name'],
						'unit' => $graph['unit'],
						'data' => $_data
					];
				}
			}
		}


		$response = [
			'Statuspage' => $data['Statuspage']
		];
		unset($response['Statuspage']['configuration']);
		$response['Services'] = $services;
		$response['Metrics'] = $metrics;

		$this->set('response', $response);
		$this->set('_serialize', ['response']);
	}

}
