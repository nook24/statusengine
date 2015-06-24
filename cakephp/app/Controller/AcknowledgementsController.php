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
		]
	];
	
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
