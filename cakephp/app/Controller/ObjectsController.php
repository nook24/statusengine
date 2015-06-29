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
class ObjectsController extends AppController{
	public $uses = ['Legacy.Objects'];
	public $helpers = ['Utils'];
	
	public $filter = [
		'index' => [
			'Objects' => [
				'objecttype_id' => [
					'type' => 'checkbox',
					'value' => [
						1 => 'Host',
						2 => 'Service',
						3 => 'Hostgroup',
						4 => 'Servicegroup',
						5 => 'Hostescalation',
						6 => 'Serviceescalation',
						7 => 'Hostdependency',
						8 => 'Servicedependency',
						9 => 'Timeperiod',
						10 => 'Contact',
						11 => 'Contactgroup',
						12 => 'Command'
					],
					'class' => 'col-xs-12 col-md-3'
				],
				'name1' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Search Name 1', 'submit' => false],
				'name2' => ['type' => 'text', 'class' => 'col-xs-12 col-md-6', 'label' => 'Search Name 2', 'submit' => true]
				
			]
		]
	];
	
	public function index(){
		$options = [
			'order' => [
				'Objects.objecttype_id' => 'asc',
			]
		];
		$this->Paginator->settings = Hash::merge($options, $this->Paginator->settings);
		$objects = $this->Paginator->paginate();
		$this->set(compact(['objects']));
		$this->set('_serialize', ['objects']);
	}
}