<?php
/**
*Copyright (C) 2015 Daniel Ziegler <daniel@statusengine.org>
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
class HostsController extends AppController{
	
	public $uses = ['Legacy.Host', 'Legacy.Hoststatus'];
	public $helpers = ['Status'];
	
	public function index(){

		//Models are not linked for StatusengineLegacyShell, so we need to to the dirty job now :(
		$this->Hoststatus->primaryKey = 'host_object_id';
		$this->Host->bindModel([
			'belongsTo' => [
				'Objects' => [
					'className' => 'Legacy.Objects',
					'foreignKey' => 'host_object_id',
				],
				'Hoststatus' => [
					'className' => 'Legacy.Hoststatus',
					'foreignKey' => 'host_object_id'
				]
			]
		]);
		
		$options = [
			'fields' => [
				'Objects.object_id',
				'Objects.name1',
				
				'Host.host_id',
				'Host.host_object_id',
				'Host.alias',
				'Host.display_name',
				'Host.address',
				
				'Hoststatus.current_state',
				'Hoststatus.last_check',
				'Hoststatus.problem_has_been_acknowledged',
				'Hoststatus.scheduled_downtime_depth'
			]
		];
		
		$this->Paginator->settings = Hash::merge($options, $this->Paginator->settings);
		
		$hosts = $this->Paginator->paginate();
		$this->set(compact(['hosts']));
		$this->set('_serialize', ['hosts']);
		
	}
}