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
class StatehistoryController extends AppController{

	public $uses = [
		'Legacy.Statehistory',
		'Legacy.Objects',
	];
	public $helpers = ['Status'];
	public $filter = [
		'service' => [
			'Statehistory' => [
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
		],
		'host' => [
			'Statehistory' => [
				'state' => ['type' => 'checkbox', 'value' => [
					0 => 'Ok',
					1 => 'Down',
					2 => 'Unreachable',
				],
				'class' => 'col-xs-12 col-md-4'
				],
				'output' => ['type' => 'text', 'class' => 'col-xs-12', 'label' => 'Output', 'submit' => true]
			],
		]
	];

	public function service($serviceObjectId = null){
		if(!$this->Objects->exists($serviceObjectId)){
			throw new NotFoundException(__('Service not found'));
		}

		$object = $this->Objects->findByObjectId($serviceObjectId);

		$query = [
			'conditions' => [
				'object_id' => $serviceObjectId,
			],
			'fields' => [
				'state_time',
				'state',
				'state_type',
				'current_check_attempt',
				'max_check_attempts',
				'output'
			],
			'order' => [
				'Statehistory.state_time' => 'desc'
			]
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$statehistory = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Statehistory.state_time']));
		$this->set(compact([
			'statehistory',
			'object'
		]));
		$this->set('_serialize', [
			'statehistory',
			'object'
		]);
	}

	public function host($hostObjectId = null){
		if(!$this->Objects->exists($hostObjectId)){
			throw new NotFoundException(__('Host not found'));
		}

		$object = $this->Objects->findByObjectId($hostObjectId);

		$query = [
			'conditions' => [
				'object_id' => $hostObjectId,
			],
			'fields' => [
				'state_time',
				'state',
				'state_type',
				'current_check_attempt',
				'max_check_attempts',
				'output'
			],
			'order' => [
				'Statehistory.state_time' => 'desc'
			]
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$statehistory = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Statehistory.state_time']));
		$this->set(compact([
			'statehistory',
			'object'
		]));
		$this->set('_serialize', [
			'statehistory',
			'object'
		]);
	}
}
