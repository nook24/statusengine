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
class AcknowledgementsController extends AppController{

	public $uses = [
		'Legacy.Acknowledgement',
		'Legacy.Objects',
	];
	public $helpers = ['Status'];
	public $filter = [
		'index' => [
			'Acknowledgement' => [
				'author_name' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Author', 'submit' => false],
				'comment_data' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Comment', 'submit' => true]
			]
		],
		'service' => [
			'Acknowledgement' => [
				'state' => ['type' => 'checkbox', 'value' => [
					0 => 'Ok',
					1 => 'Warning',
					2 => 'Critical',
					3 => 'Unknown'
				],
				'class' => 'col-xs-12 col-md-3'
				],
				'author_name' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Author', 'submit' => false],
				'comment_data' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Comment', 'submit' => true]
			]
		],
		'host' => [
			'Acknowledgement' => [
				'state' => ['type' => 'checkbox', 'value' => [
					0 => 'Up',
					1 => 'Down',
					2 => 'Unreachable',
				],
				'class' => 'col-xs-12 col-md-4'
				],
				'author_name' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Author', 'submit' => false],
				'comment_data' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Comment', 'submit' => true]
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
					'conditions' => 'Objects.object_id = Acknowledgement.object_id'
				]
			],
			'fields' => [
				'Acknowledgement.entry_time',
				'Acknowledgement.object_id',
				'Acknowledgement.acknowledgement_type',
				'Acknowledgement.state',
				'Acknowledgement.author_name',
				'Acknowledgement.comment_data',
				'Acknowledgement.is_sticky',

				'Objects.object_id',
				'Objects.objecttype_id',
				'Objects.name1',
				'Objects.name2',
			],
			'order' => [
				'Acknowledgement.entry_time' => 'desc',
				'Objects.name1' => 'asc'
			],
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$acknowledgements = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Acknowledgement.entry_time']));
		$this->set(compact([
			'acknowledgements',
			'object'
		]));
		$this->set('_serialize', [
			'acknowledgements',
			'object'
		]);
	}

	public function service($serviceObjectId = null){
		if(!$this->Objects->exists($serviceObjectId)){
			throw new NotFoundException(__('Service not found'));
		}

		$object = $this->Objects->findByObjectId($serviceObjectId);

		$query = [
			'conditions' => [
				'Acknowledgement.acknowledgement_type' => 1,
				'Acknowledgement.object_id' => $serviceObjectId,
			],
			'fields' => [
				'Acknowledgement.entry_time',
				'Acknowledgement.object_id',
				'Acknowledgement.state',
				'Acknowledgement.author_name',
				'Acknowledgement.comment_data',
				'Acknowledgement.is_sticky'
			],
			'order' => [
				'Acknowledgement.entry_time' => 'desc'
			]
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$acknowledgements = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Acknowledgement.entry_time']));
		$this->set(compact([
			'acknowledgements',
			'object'
		]));
		$this->set('_serialize', [
			'acknowledgements',
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
				'Acknowledgement.acknowledgement_type' => 0,
				'Acknowledgement.object_id' => $hostObjectId,
			],
			'fields' => [
				'Acknowledgement.entry_time',
				'Acknowledgement.object_id',
				'Acknowledgement.state',
				'Acknowledgement.author_name',
				'Acknowledgement.comment_data',
				'Acknowledgement.is_sticky'
			],
			'order' => [
				'Acknowledgement.entry_time' => 'desc'
			]
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$acknowledgements = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Acknowledgement.entry_time']));
		$this->set(compact([
			'acknowledgements',
			'object'
		]));
		$this->set('_serialize', [
			'acknowledgements',
			'object'
		]);
	}
}
