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
		'Legacy.Hoststatus',
		'Legacy.Servicestatus',
	];
	public $components = ['Externalcommands'];
	const SCHEDULE_FORCED_SVC_CHECK       =  1;
	const SCHEDULE_FORCED_HOST_CHECK      =  2;
	const SCHEDULE_FORCED_HOST_SVC_CHECKS =  3;
	const PROCESS_SERVICE_CHECK_RESULT    =  4;
	const SEND_CUSTOM_SVC_NOTIFICATION    =  5;
	const ACKNOWLEDGE_SVC_PROBLEM         =  6;
	const PROCESS_HOST_CHECK_RESULT       =  7;
	const SEND_CUSTOM_HOST_NOTIFICATION   =  8;
	const ACKNOWLEDGE_HOST_PROBLEM        =  9;

	const DISABLE_SVC_NOTIFICATIONS       = 10;
	const ENABLE_SVC_NOTIFICATIONS        = 11;
	const DISABLE_SVC_FLAP_DETECTION      = 12;
	const ENABLE_SVC_FLAP_DETECTION       = 13;
	const DISABLE_SVC_EVENT_HANDLER       = 14;
	const ENABLE_SVC_EVENT_HANDLER        = 15;
	const DISABLE_SVC_CHECK               = 16;
	const ENABLE_SVC_CHECK                = 17;
	const DISABLE_PASSIVE_SVC_CHECKS      = 18;
	const ENABLE_PASSIVE_SVC_CHECKS       = 19;
	const DISABLE_HOST_CHECK              = 20;
	const ENABLE_HOST_CHECK               = 21;
	const DISABLE_HOST_EVENT_HANDLER      = 22;
	const ENABLE_HOST_EVENT_HANDLER       = 23;
	const DISABLE_HOST_FLAP_DETECTION     = 24;
	const ENABLE_HOST_FLAP_DETECTION      = 25;
	const DISABLE_HOST_NOTIFICATIONS      = 26;
	const ENABLE_HOST_NOTIFICATIONS       = 27;
	const DISABLE_PASSIVE_HOST_CHECKS     = 28;
	const ENABLE_PASSIVE_HOST_CHECKS      = 29;



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

			case self::DISABLE_SVC_NOTIFICATIONS:
			case self::ENABLE_SVC_NOTIFICATIONS:
				$state = $this->__isServiceOptionEnabledNegated($objectId, 'notifications_enabled');
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2']
				];
				$this->Externalcommands->serviceNotifications($options, $state);
				break;

			case self::DISABLE_SVC_FLAP_DETECTION:
			case self::ENABLE_SVC_FLAP_DETECTION:
				$state = $this->__isServiceOptionEnabledNegated($objectId, 'flap_detection_enabled');
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2']
				];
				$this->Externalcommands->serviceFlapdetection($options, $state);
				break;

			case self::DISABLE_SVC_EVENT_HANDLER:
			case self::ENABLE_SVC_EVENT_HANDLER:
				$state = $this->__isServiceOptionEnabledNegated($objectId, 'event_handler_enabled');
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2']
				];
				$this->Externalcommands->serviceEventhandler($options, $state);
				break;

			case self::DISABLE_SVC_CHECK:
			case self::ENABLE_SVC_CHECK:
				$state = $this->__isServiceOptionEnabledNegated($objectId, 'active_checks_enabled');
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2']
				];
				$this->Externalcommands->serviceActiveChecks($options, $state);
				break;

			case self::DISABLE_PASSIVE_SVC_CHECKS:
			case self::ENABLE_PASSIVE_SVC_CHECKS:
				$state = $this->__isServiceOptionEnabledNegated($objectId, 'passive_checks_enabled');
				$options = [
					$object['Objects']['name1'],
					$object['Objects']['name2']
				];
				$this->Externalcommands->servicePassiveChecks($options, $state);
				break;

			case self::DISABLE_HOST_NOTIFICATIONS:
			case self::ENABLE_HOST_NOTIFICATIONS:
				$state = $this->__isHostOptionEnabledNegated($objectId, 'notifications_enabled');
				$options = [
					$object['Objects']['name1']
				];
				$this->Externalcommands->hostNotifications($options, $state);
				break;

			case self::DISABLE_HOST_FLAP_DETECTION:
			case self::ENABLE_HOST_FLAP_DETECTION:
				$state = $this->__isHostOptionEnabledNegated($objectId, 'flap_detection_enabled');
				$options = [
					$object['Objects']['name1']
				];
				$this->Externalcommands->hostFlapdetection($options, $state);
				break;

			case self::DISABLE_HOST_EVENT_HANDLER:
			case self::ENABLE_HOST_EVENT_HANDLER:
				$state = $this->__isHostOptionEnabledNegated($objectId, 'event_handler_enabled');
				$options = [
					$object['Objects']['name1']
				];
				$this->Externalcommands->hostEventhandler($options, $state);
				break;

			case self::DISABLE_HOST_CHECK:
			case self::ENABLE_HOST_CHECK:
				$state = $this->__isHostOptionEnabledNegated($objectId, 'active_checks_enabled');
				$options = [
					$object['Objects']['name1']
				];
				$this->Externalcommands->hostActiveChecks($options, $state);
				break;

			case self::DISABLE_PASSIVE_HOST_CHECKS:
			case self::ENABLE_PASSIVE_HOST_CHECKS:
				$state = $this->__isHostOptionEnabledNegated($objectId, 'passive_checks_enabled');
				$options = [
					$object['Objects']['name1']
				];
				$this->Externalcommands->hostPassiveChecks($options, $state);
				break;
		}

		$this->set('result', true);
		$this->set('_serialize', ['result']);
	}

	protected function __isServiceOptionEnabledNegated($objectId, $field){
		$servicestatus = $this->Servicestatus->find('first', [
			'conditions' => [
				'Servicestatus.service_object_id' => $objectId
			],
			'fields' => [
				$field
			]
		]);
		return !(boolean)$servicestatus['Servicestatus'][$field];
	}

	protected function __isHostOptionEnabledNegated($objectId, $field){
		$hoststatus = $this->Hoststatus->find('first', [
			'conditions' => [
				'Hoststatus.host_object_id' => $objectId
			],
			'fields' => [
				$field
			]
		]);
		return !(boolean)$hoststatus['Hoststatus'][$field];
	}
}
