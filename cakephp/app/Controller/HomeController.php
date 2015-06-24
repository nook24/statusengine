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
class HomeController extends AppController{
	public $uses = [
		'Legacy.Host',
		'Legacy.Hoststatus',
		'Legacy.Service',
		'Legacy.Servicestatus',
		'Legacy.Objects',
		'Legacy.Configvariable'
	];
	public $helpers = ['Status'];
	
	public function index(){
		if(!$this->Configvariable->isRightNaemonUsergroup()){
			$this->setFlash(
				'<p>'.__('Statusengine requres that Naemon runs as the web servers user group (www-data for example)').'</p><p>'
				.__('Please check "naemon_group=" in your naemon.cfg').'</p>', false);
		}
		$hostStatusCount = [
			0 => 0,
			1 => 0,
			2 => 0
		];
		$hoststatus = $this->Host->find('all', [
			'fields' => [
				'Hoststatus.current_state',
				'COUNT(*) AS count'
			],
			'group' => [
				'Hoststatus.current_state'
			],
			'joins' => [
				[
					'table' => $this->Hoststatus->tablePrefix.$this->Hoststatus->table,
					'type' => 'INNER',
					'alias' => 'Hoststatus',
					'conditions' => 'Hoststatus.host_object_id = Host.host_object_id'
				]
			]
		]);
		foreach($hoststatus as $state){
			$hostStatusCount[$state['Hoststatus']['current_state']] = $state[0]['count'];
		}
		
		$serviceStatusCount = [
			0 => 0,
			1 => 0,
			2 => 0,
			3 => 0
		];
		$servicestatus = $this->Service->find('all', [
			'fields' => [
				'Servicestatus.current_state',
				'COUNT(*) AS count'
			],
			'group' => [
				'Servicestatus.current_state'
			],
			'joins' => [
				[
					'table' => $this->Servicestatus->tablePrefix.$this->Servicestatus->table,
					'type' => 'INNER',
					'alias' => 'Servicestatus',
					'conditions' => 'Servicestatus.service_object_id = Service.service_object_id'
				]
			]
		]);
		foreach($servicestatus as $state){
			$serviceStatusCount[$state['Servicestatus']['current_state']] = $state[0]['count'];
		}
		
		$problems = $this->Servicestatus->find('count', [
			'conditions' => [
				'Servicestatus.current_state > ' => 0,
				'Servicestatus.problem_has_been_acknowledged' => 0,
				'Servicestatus.scheduled_downtime_depth' => 0
			]
		]);
		
		$this->set(compact([
			'hostStatusCount',
			'serviceStatusCount',
			'problems'
		]));
	}
}
