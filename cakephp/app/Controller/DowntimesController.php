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
		$this->redirect(['action' => 'host']);
	}
	
	public function host(){
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
			],
			'conditions' => [
				'Downtimehistory.downtime_type' => 2 //Host
			]
		];
		
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		//Read: https://github.com/cakephp/cakephp/blob/2.7/lib/Cake/Controller/Component/PaginatorComponent.php#L121-L128
		$hostgroups = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Objects.name1']));
		$this->set(compact(['hostgroups']));
		$this->set('_serialize', ['hostgroups']);
	}
}