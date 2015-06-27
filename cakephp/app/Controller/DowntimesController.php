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
}