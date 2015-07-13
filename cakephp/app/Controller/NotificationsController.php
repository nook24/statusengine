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
class NotificationsController extends AppController{

	public $uses = [
		'Legacy.Notification',
		'Legacy.Contactnotification',
		'Legacy.Contactnotificationmethod',
		'Legacy.Service',
		'Legacy.Host',
		'Legacy.Objects',
	];
	public $helpers = ['Status'];
	public $filter = [
		'service' => [
			'Notification' => [
				'state' => ['type' => 'checkbox', 'value' => [
					0 => 'Ok',
					1 => 'Warning',
					2 => 'Critical',
					3 => 'Unknown'
				],
				'class' => 'col-xs-12 col-md-3'
				],
				'output' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Output', 'submit' => false]
			],
			'ContactObject' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Contact', 'submit' => true]
			]
		],
		'host' => [
			'Notification' => [
				'state' => ['type' => 'checkbox', 'value' => [
					0 => 'Ok',
					1 => 'Down',
					2 => 'Unreachable',
				],
				'class' => 'col-xs-12 col-md-4'
				],
				'output' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Output', 'submit' => false]
			],
			'ContactObject' => [
				'name1' => ['type' => 'text', 'class' => 'col-xs-6', 'label' => 'Contact', 'submit' => true]
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
				'Notification.notification_type' => 1, //Service notifications
				'Notification.object_id' => $serviceObjectId,
			],
			'order' => [
				'Notification.start_time' => 'desc'
			]
		];
		$query = Hash::merge($query, $this->__baseQuery());
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$notifications = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Notification.start_time']));
		$this->set(compact([
			'notifications',
			'object'
		]));
		$this->set('_serialize', [
			'notifications',
			'object'
		]);
	}

	public function host($hosteObjectId = null){
		if(!$this->Objects->exists($hosteObjectId)){
			throw new NotFoundException(__('Host not found'));
		}

		$object = $this->Objects->findByObjectId($hosteObjectId);

		$query = [
			'conditions' => [
				'Notification.notification_type' => 0, //Host notifications
				'Notification.object_id' => $hosteObjectId,
			],
			'order' => [
				'Notification.start_time' => 'desc'
			]
		];
		$query = Hash::merge($query, $this->__baseQuery());
		$this->Paginator->settings = Hash::merge($query, $this->Paginator->settings);
		$notifications = $this->Paginator->paginate(null, [], $this->fixPaginatorOrder(['Notification.start_time']));
		$this->set(compact([
			'notifications',
			'object'
		]));
		$this->set('_serialize', [
			'notifications',
			'object'
		]);
	}

	protected function __baseQuery(){
		return [
			'joins' => [
				[
					'table' => $this->Contactnotification->tablePrefix.$this->Contactnotification->table,
					'type'	=> 'INNER',
					'alias'	=> 'Contactnotification',
					'conditions' => 'Contactnotification.notification_id = Notification.notification_id',
				],
				[
					'table' => $this->Contactnotificationmethod->tablePrefix.$this->Contactnotificationmethod->table,
					'type'	=> 'INNER',
					'alias'	=> 'Contactnotificationmethod',
					'conditions' => 'Contactnotificationmethod.contactnotification_id = Contactnotification.contactnotification_id',
				],
				[
					'table' => $this->Objects->tablePrefix.$this->Objects->table,
					'type'	=> 'INNER',
					'alias'	=> 'ContactObject',
					'conditions' => 'ContactObject.object_id = Contactnotification.contact_object_id',
				],
				[
					'table' => $this->Objects->tablePrefix.$this->Objects->table,
					'type'	=> 'INNER',
					'alias'	=> 'CommandObject',
					'conditions' => 'CommandObject.object_id = Contactnotificationmethod.command_object_id',
				],
			],
			'conditions' => [
				'Notification.contacts_notified >' => 0,
			],
			'fields' => [
				'Notification.notification_id',
				'Notification.object_id',
				'Notification.start_time',
				'Notification.state',
				'Notification.output',

				'Contactnotification.contact_object_id',

				'Contactnotificationmethod.command_object_id',

				'ContactObject.object_id',
				'ContactObject.name1',

				'CommandObject.object_id',
				'CommandObject.name1'
			]
		];
	}
}
