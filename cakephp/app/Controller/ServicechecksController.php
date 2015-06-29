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
class ServicechecksController extends AppController{
	
	public $uses = [
		'Legacy.Servicecheck',
		'Legacy.Objects',
	];
	public $helpers = ['Status'];
	public $filter = [
		'index' => [
			'Servicecheck' => [
				'state' => ['type' => 'checkbox', 'value' => [
					0 => 'Ok',
					1 => 'Warning',
					2 => 'Critical',
					3 => 'Unknown'
				],
				'class' => 'col-xs-12 col-md-3'
				],
				'output' => ['type' => 'text', 'class' => 'col-xs-12', 'label' => 'Output', 'submit' => true]
			],
		]
	];
	
	public function index($serviceObjectId = null){
		if(!$this->Objects->exists($serviceObjectId)){
			throw new NotFoundException(__('Service not found'));
		}
		
		$object = $this->Objects->findByObjectId($serviceObjectId);
		
		$query = [
			'conditions' => [
				'Servicecheck.service_object_id' => $serviceObjectId
			],
			'fields' => [
				'current_check_attempt',
				'max_check_attempts',
				'state',
				'state_type',
				'start_time',
				'output',
				'perfdata'
			]
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$servicechecks = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Servicecheck.start_time']));
		$this->set(compact([
			'servicechecks',
			'object'
		]));
		$this->set('_serialize', [
			'servicechecks',
			'object'
		]);
	}
}
