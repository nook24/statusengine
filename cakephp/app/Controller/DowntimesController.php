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
class DowntimesController extends AppController{
	
	public $uses = [
		'Legacy.Downtimehistory',
		'Legacy.Objects',
		'Legacy.Service',
		'Legacy.Configvariable'
	];
	public $helpers = ['Status'];
	public $components = ['Externalcommands'];
	public $filter = [
		'index' => [
			'Objects' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Host name', 'submit' => false],
				'name2' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Service description', 'submit' => false],
			],
			'Downtimehistory' => [
				'author_name' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Author', 'submit' => false],
				'comment_data' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Comment', 'submit' => true],
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
					'conditions' => 'Objects.object_id = Downtimehistory.object_id'
				],
			],
			'fields' => [
				'Downtimehistory.*',
				'Objects.*'
			],
			'order' => [
				'Objects.name1' => 'asc',
			]
		];
		
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		//Read: https://github.com/cakephp/cakephp/blob/2.7/lib/Cake/Controller/Component/PaginatorComponent.php#L121-L128
		$downtimes = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Objects.name1']));
		$this->set(compact(['downtimes']));
		$this->set('_serialize', ['downtimes']);
	}
	
	public function create($type = 'host'){
		$types = ['host', 'service'];
		if(!in_array($type, $types)){
			$this->redirect(['action' => 'create', 'host']);
		}
		
		if($this->request->is('post') || $this->request->is('put')){
			if(!$this->Objects->exists($this->request->data('Downtimehistory.host'))){
				throw new NotFoundException(__('Host not found'));
			}
			if($type == 'service'){
				$serviceObjectId = $this->request->data('Downtimehistory.service');
				if(!$this->Objects->exists($serviceObjectId)){
					throw new NotFoundException(__('Service not found'));
				}
				$service = $this->Objects->findByObjectId($serviceObjectId);
			}
			$host = $this->Objects->findByObjectId($this->request->data('Downtimehistory.host'));
			$start = strtotime($this->request->data('Downtimehistory.start'));
			$end = strtotime($this->request->data('Downtimehistory.end'));
			$comment = $this->request->data('Downtimehistory.comment');
			if($start > 0 && $end > 0 && strlen($comment) > 0){
				if($type == 'host'){
					$downtimeOptions = [
						'type' => $this->request->data('Downtimehistory.type'),
						'parameters' => [
							$host['Objects']['name1'],
							$start,
							$end,
							1,
							0,
							($end - $start),
							'Daniel',
							$comment
						]
					];
				}else{
					$downtimeOptions = [
						$service['Objects']['name1'],
						$service['Objects']['name2'],
						$start,
						$end,
						1,
						0,
						($end - $start),
						'Daniel',
						$comment
					];
				}

				$this->Externalcommands->createDowntime($type, $downtimeOptions);
				$this->redirect(['action' => 'index']);
			}else{
				$this->setFlash(__('Data validation error'), false);
			}
		}
		
		//Set default values
		$defaults = [
			'Downtimehistory' => [
				'start' => date('H:m d.m.y'),
				'end' => date('H:m d.m.y', strtotime('+3 days'))
			]
		];
		$this->request->data = Hash::merge($defaults, $this->request->data);
		
		$hosts = $this->Objects->findList(1);
		$services = [];
		if($type == 'service' && !empty($hosts)){
			$_hosts = $hosts;
			$services = $this->Objects->findList(2, 'name2', ['Objects.name1' => array_shift($_hosts)]);
		}
		$this->Externalcommands->checkCmd();
		$this->Frontend->setJson('url', Router::url(['controller' => 'Downtimes', 'action' => 'getServices']).'/');
		$this->set(compact([
			'type',
			'hosts',
			'services'
		]));
	}
	
	public function getServices($hostObjectId = null){
		if(!$this->request->is('ajax')){
			throw new MethodNotAllowedException();
		}
		if(!$this->Objects->exists($hostObjectId)){
			throw new NotFoundException(__('Host not found'));
		}
		
		$services = $this->Service->find('all', [
			'conditions' => [
				'Service.host_object_id' => $hostObjectId
			],
			'joins' => [
				[
					'table' => $this->Objects->tablePrefix.$this->Objects->table,
					'type' => 'INNER',
					'alias' => 'Objects',
					'conditions' => 'Objects.object_id = Service.service_object_id'
				]
			],
			'fields' => [
				'Service.service_object_id',
				'Objects.name2'
			],
			'order' => [
				'Objects.name2' => 'asc'
			]
		]);
		$this->set('services', $services);
		$this->set('_serialize', ['services']);
	}
}