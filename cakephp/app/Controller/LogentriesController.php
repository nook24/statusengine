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
class LogentriesController extends AppController{
	
	public $uses = [
		'Legacy.Logentry',
	];
	public $helpers = ['Status'];
	public $filter = [
		'index' => [
			'Logentry' => [
				'logentry_data' => ['type' => 'text', 'class' => 'col-xs-12', 'label' => 'Data', 'submit' => true],
			],
		]
	];
	
	public function index(){
		$query = [
			'order' => [
				'Logentry.entry_time' => 'desc'
			]
		];
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$logentries = $this->Paginator->paginate();
		$this->set(compact(['logentries']));
		$this->set('_serialize', ['logentries']);
	}
}