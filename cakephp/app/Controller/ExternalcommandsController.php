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
class ExternalcommandsController extends AppController{
	public $uses = [
		'Legacy.Host',
		'Legacy.Service',
		'Legacy.Objects',
		'Legacy.Configvariable',
	];
	public $components = ['Externalcommands'];
	
	public function receiver(){
		if(!$this->request->is('ajax')){
			throw new MethodNotAllowedException(__('This method is not allowed :('));
		}
		$type = $this->request->data('type');
		$objectId = $this->request->data('objectId');
		if(!$this->Objects->exists($objectId)){
			throw new NotFoundException(__('Object not found'));
		}
		
		$object = $this->Objects->findByObjectId($objectId);
		
		if(!in_array($type, ['host', 'service'])){
			return false;
		}
		switch($this->request->data('commandId')){
			case 1:
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2'],
					time()
				];
				$this->Externalcommands->rescheduleService($options);
				break;
			case 2:
				$options = [
					$object['Objects']['name1'],
					time()
				];
				$this->Externalcommands->rescheduleHost($options);
				break;
			case 3:
				$options = [
					$object['Objects']['name1'],
					time()
				];
				$this->Externalcommands->rescheduleHost($options);
				$this->Externalcommands->rescheduleHostAndServices($options);
				break;
		}
		
		$this->set('result', true);
		$this->set('_serialize', ['result']);
	}
}