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
	const SCHEDULE_FORCED_SVC_CHECK       = 1;
	const SCHEDULE_FORCED_HOST_CHECK      = 2;
	const SCHEDULE_FORCED_HOST_SVC_CHECKS = 3;
	const PROCESS_SERVICE_CHECK_RESULT    = 4;
	const SEND_CUSTOM_SVC_NOTIFICATION    = 5;
	const ACKNOWLEDGE_SVC_PROBLEM         = 6;
	const PROCESS_HOST_CHECK_RESULT       = 7;
	const SEND_CUSTOM_HOST_NOTIFICATION   = 8;
	const ACKNOWLEDGE_HOST_PROBLEM        = 9;
	
	
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
			case self::SCHEDULE_FORCED_SVC_CHECK:
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2'],
					time()
				];
				$this->Externalcommands->rescheduleService($options);
				break;
			case self::SCHEDULE_FORCED_HOST_CHECK:
				$options = [
					$object['Objects']['name1'],
					time()
				];
				$this->Externalcommands->rescheduleHost($options);
				break;
			case self::SCHEDULE_FORCED_HOST_SVC_CHECKS:
				$options = [
					$object['Objects']['name1'],
					time()
				];
				$this->Externalcommands->rescheduleHost($options);
				$this->Externalcommands->rescheduleHostAndServices($options);
				break;
				
			case self::PROCESS_SERVICE_CHECK_RESULT:
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2'],
					$this->request->data('state'),
					$this->request->data('output')
				];
				$this->Externalcommands->serviceCheckResult($options);
				break;
				
			case self::SEND_CUSTOM_SVC_NOTIFICATION:
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2'],
					$this->request->data('options'),
					$this->Auth->user('username'),
					$this->request->data('comment')
				];
				$this->Externalcommands->sendCustomServiceNotification($options);
				break;
				
			case self::ACKNOWLEDGE_SVC_PROBLEM:
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2'],
					(int)$this->request->data('sticky'),
					1,
					1,
					$this->Auth->user('username'),
					$this->request->data('comment')
				];
				$this->Externalcommands->sendServiceAck($options);
				break;
				
			case self::PROCESS_HOST_CHECK_RESULT:
				$options = [
					$object['Objects']['name1'],
					$this->request->data('state'),
					$this->request->data('output')
				];
				$this->Externalcommands->hostCheckResult($options);
				break;
			
			case self::SEND_CUSTOM_HOST_NOTIFICATION:
				$options = [
					$object['Objects']['name1'],
					$this->request->data('options'),
					$this->Auth->user('username'),
					$this->request->data('comment')
				];
				$this->Externalcommands->sendCustomHostNotification($options);
				break;
			
			case self::ACKNOWLEDGE_HOST_PROBLEM:
				$options = [
					$object['Objects']['name1'],
					(int)$this->request->data('sticky'),
					1,
					1,
					$this->Auth->user('username'),
					$this->request->data('comment')
				];
				$this->Externalcommands->sendHostAck($options);
				break;
		}
		
		$this->set('result', true);
		$this->set('_serialize', ['result']);
	}
}