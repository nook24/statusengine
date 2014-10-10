<?php
/**********************************************************************************
*
*    #####
*   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######
*   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #
*    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####
*         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #
*   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #
*    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######
*
*                            the missing event broker
*
* --------------------------------------------------------------------------------
*
* Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation in version 2
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* --------------------------------------------------------------------------------
*
* This is the CakePHP based StatusengineShell. This shell can read the data
* out of the Gearman Job Server and push it to an MySQL database.
*
* THIS IS NOT FINAL IMPLEMENTED YET!!! :-(
*
**********************************************************************************/

class StatusengineShell extends AppShell{
	
	public $uses = ['Objects', 'Command', 'Contact', 'Contactgroup', 'Timeperiod', 'Timerange', 'Host', 'Service'];
	
	public function main(){
		Configure::load('Statusengine');
		$this->out('Starting Statusengine version: '.Configure::read('version').'...');
		$this->_constants();
		
		$this->disableAll();
		
		//Delete all timeranges, because we cant update this :(
		$this->Timerange->deleteAll(true);
		
		$this->clearObjectsCache();
		$this->buildObjectsCache();
		$this->worker = null;
		$this->gearmanConnect();
		while ($this->worker->work());
	}
	
	public function disableAll(){
		//Disable every object in objects, because nagios was restared
		$this->Objects->updateAll(['Objects.is_active' => 0]);
	}
	
	public function dumpObjects($job){
		$payload = json_decode($job->workload());
		$this->Objects->create();
		switch($payload->object_type){
			//Command object
			case OBJECT_COMMAND:
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->command_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'id' => $this->objectIdFromCache($payload->object_type, $payload->command_name),
					],
					'Command' => [
						'command_line' => $payload->command_line
					]
				];
				
				$result = $this->Objects->saveAll($data);
				
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->command_name);
				
				unset($result, $data);
			break;
			
			//Timeperiod object
			case OBJECT_TIMEPERIOD:
				//Fetch timeranges out of raw data
				$timeranges = [];
				foreach($payload->timeranges as $day => $timerangesPerDay){
					foreach($timerangesPerDay as $timerange){
						if(isset($timerange->start) && isset($timerange->end)){
							$timeranges[] = [
								'start_sec' => $timerange->start,
								'end_sec' => $timerange->end,
								'day' => $day
							];
						}else{
							$timeranges[] = [
								'start_sec' => null,
								'end_sec' => null,
								'day' => $day
							];
						}
					}
				}
			
				$timeperiodObjectId = $this->objectIdFromCache($payload->object_type, $payload->name);
				$timeperiodId = $this->idByObjectIdFromDb('Timeperiod', $timeperiodObjectId);
	
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'id' => $timeperiodObjectId,
					],
					'Timeperiod' => [
						'alias' => $payload->alias,
						'id' => $timeperiodId,
					],
				];
				$result = $this->Objects->saveAll($data);
				//associate timeperiods with timeranges (HABTM)
					if(!empty($timeranges)){
						if($timeperiodId === null){
							//The timerang was created right now, so we need to get the new id
							$timeperiodId = $this->Timeperiod->findByObjectId($this->Objects->id)['Timeperiod']['id'];
						}
					$data = [];
					foreach($timeranges as $timerange){
						$data[] = [
							'Timeperiod' => [
								'id' => $timeperiodId,
							],
							'Timerange' => [
								'start_sec' => $timerange['start_sec'],
								'end_sec' => $timerange['end_sec'],
								'day' => $timerange['day'],
							]
						];
					}
					$this->Timerange->saveAll($data);
				}
				
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);
				
				unset($result, $data);
				break;
			
			//Contact object
			case OBJECT_CONTACT:
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'id' => $this->objectIdFromCache($payload->object_type, $payload->name),
					],
					'Contact' => [
						'name' => $payload->name,
						'alias' => $payload->alias,
						'email' => $payload->email,
						'pager' => $payload->pager,
						'host_notification_period' => $payload->host_notification_period,
						'service_notification_period' => $payload->service_notification_period,
						'notify_on_service_downtime' => $payload->notify_on_service_downtime,
						'notify_on_host_downtime' => $payload->notify_on_host_downtime,
						'host_notifications_enabled' => $payload->host_notifications_enabled,
						'service_notifications_enabled' => $payload->service_notifications_enabled,
						'can_submit_commands' => $payload->can_submit_commands,
						'notify_on_service_unknown' => $payload->notify_on_service_unknown,
						'notify_on_service_warning' => $payload->notify_on_service_warning,
						'notify_on_service_critical' => $payload->notify_on_service_critical,
						'notify_on_service_recovery' => $payload->notify_on_service_recovery,
						'notify_on_service_flapping' => $payload->notify_on_service_flapping,
						'notify_on_host_unreachable' => $payload->notify_on_host_unreachable,
						'notify_on_host_down' => $payload->notify_on_host_down,
						'notify_on_host_recovery' => $payload->notify_on_host_recovery,
						'notify_on_host_flapping' => $payload->notify_on_host_flapping,
						'minimum_value' => $payload->minimum_value
					]
				];
				$result = $this->Objects->saveAll($data);
				
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);
				
				unset($result, $data);
			break;
			
			//Contactgroup object
			case OBJECT_CONTACTGROUP:
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'id' => $this->objectIdFromCache($payload->object_type, $payload->group_name),
					],
					'Contactgroup' => [
						'alias' => $payload->alias
					]
				];
				
				$result = $this->Objects->saveAll($data);
				
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->group_name);
				
				//associate contactgroups with contacts (HABTM)
				$_contactgroup = $this->Contactgroup->findByAlias($payload->alias);
				foreach($payload->contact_members as $ContactName){
					$_contact = $this->Objects->find('first', [
						'conditions' => [
							'Objects.name1' => $ContactName,
							'Objects.objecttype_id' => 10
						]
					]);
					$association = [
						'Contact' => [
							'id' => $_contact['Contact']['id'],
						],
						'Contactgroup' => [
							'id' => $_contactgroup['Contactgroup']['id']
						]
					];
					$this->Contact->save($association);
					unset($association);
				}
				
				unset($result, $data);
				break;
			
			//Host object
			case OBJECT_HOST:
				$hostObjectId = $this->objectIdFromCache($payload->object_type, $payload->name);
				$hostId = $this->idByObjectIdFromDb('Host', $hostObjectId);
			
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'id' => $hostObjectId
					],
					'Host' => [
						//Update record, if exists
						'id' => $hostId,

						'alias' => $payload->alias,
						'display_name' => $payload->display_name,
						'address' => $payload->address,
						'check_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $payload->check_command),
						'eventhandler_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $payload->event_handler),
						'notification_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD,$payload->notification_period),
						'check_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->check_period),
						'notes' => $payload->notes,
						'notes_url' => $payload->notes_url,
						'action_url' => $payload->action_url,
						'icon_image' => $payload->icon_image,
						'icon_image_alt' => $payload->icon_image_alt,
						'vrml_image' => $payload->vrml_image,
						'statusmap_image' => $payload->statusmap_image,
						'have_3d_coords' => $payload->have_3d_coords,
						'have_2d_coords' => $payload->have_2d_coords,
						'x_2d' => $payload->x_2d,
						'y_2d' => $payload->y_2d,
						'x_3d' => $payload->x_3d,
						'y_3d' => $payload->y_3d,
						'z_3d' => $payload->z_3d,
						'first_notification_delay' => $payload->first_notification_delay,
						'retry_interval' => $payload->retry_interval,
						'notifications_enabled' => $payload->notifications_enabled,
						'retain_nonstatus_information' => $payload->retain_nonstatus_information,
						'retain_status_information' => $payload->retain_status_information,
						'event_handler_enabled' => $payload->event_handler_enabled,
						'accept_passive_checks' => $payload->accept_passive_checks,
						'checks_enabled' => $payload->checks_enabled,
						'process_performance_data' => $payload->process_performance_data,
						'freshness_threshold' => $payload->freshness_threshold,
						'check_freshness' => $payload->check_freshness,
						'obsess' => $payload->obsess,
						'hourly_value' => $payload->hourly_value,
						'high_flap_threshold' => $payload->high_flap_threshold,
						'low_flap_threshold' => $payload->low_flap_threshold,
						'flap_detection_enabled' => $payload->flap_detection_enabled,
						'notification_interval' => $payload->notification_interval,
						'max_attempts' => $payload->max_attempts,
						'check_interval' => $payload->check_interval,
						'flap_detection_on_up' => $payload->flap_detection_on_up,
						'flap_detection_on_down' => $payload->flap_detection_on_down,
						'flap_detection_on_unreachable' => $payload->flap_detection_on_unreachable,
						'notify_on_down' => $payload->notify_on_down,
						'notify_on_unreachable' => $payload->notify_on_unreachable,
						'notify_on_recovery' => $payload->notify_on_recovery,
						'notify_on_flapping' => $payload->notify_on_flapping,
						'notify_on_downtime' => $payload->notify_on_downtime,
						'stalk_on_up' => $payload->stalk_on_up,
						'stalk_on_down' => $payload->stalk_on_down,
						'stalk_on_unreachable' => $payload->stalk_on_unreachable
					]
				];
				
				$result = $this->Objects->saveAll($data);
				
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);
				unset($data, $result);
				break;
	
			//Service object
			case OBJECT_SERVICE:
				$serviceObjectId = $this->objectIdFromCache($payload->object_type, $payload->name);
				$serviceId = $this->idByObjectIdFromDb('Service', $serviceObjectId);
		
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->description,
						'is_active' => 1,
						//Update record, if exists
						'id' => $hostObjectId
					],
					//lol reverse? :D
					'Service' => [
						'icon_image_alt' => $payload->icon_image_alt,
						'icon_image' => $payload->icon_image,
						'action_url' => $payload->action_url,
						'notes_url' => $payload->notes_url,
						'notes' => $payload->notes,
						'failure_prediction_enabled' => $payload->failure_prediction_enabled,
						'obsess_over_service' => $payload->obsess_over_service,
						'notifications_enabled' => $payload->notifications_enabled,
						'retain_nonstatus_information' => $payload->retain_nonstatus_information,
						'retain_status_information' => $payload->retain_status_information,
						'active_checks_enabled' => $payload->active_checks_enabled,
						'event_handler_enabled' => $payload->event_handler_enabled,
						'passive_checks_enabled' => $payload->passive_checks_enabled,
						'freshness_threshold' => $payload->freshness_threshold,
						'freshness_checks_enabled' => $payload->freshness_checks_enabled,
						'process_performance_data' => $payload->process_performance_data,
						'high_flap_threshold' => $payload->high_flap_threshold,
						'low_flap_threshold' => $payload->low_flap_threshold,
						'flap_detection_on_critical' => $payload->flap_detection_on_critical,
						'flap_detection_on_unknown' => $payload->flap_detection_on_unknown,
						'flap_detection_on_warning' => $payload->flap_detection_on_warning,
						'flap_detection_on_ok' => $payload->flap_detection_on_ok,
						'flap_detection_enabled' => $payload->flap_detection_enabled,
						'is_volatile' => $payload->is_volatile,
						'stalk_on_critical' => $payload->stalk_on_critical,
						'stalk_on_unknown' => $payload->stalk_on_unknown,
						'stalk_on_warning' => $payload->stalk_on_warning,
						'stalk_on_ok' => $payload->stalk_on_ok,
						'notify_on_downtime' => $payload->notify_on_downtime,
						'notify_on_flapping' => $payload->notify_on_flapping,
						'notify_on_recovery' => $payload->notify_on_recovery,
						'notify_on_critical' => $payload->notify_on_critical,
						'notify_on_unknown' => $payload->notify_on_unknown,
						'notify_on_warning' => $payload->notify_on_warning,
						'notification_interval' => $payload->notification_interval,
						'first_notification_delay' => $payload->first_notification_delay,
						'max_check_attempts' => $payload->max_attempts,
						'retry_interval' => $payload->retry_interval,
						'check_interval' => $payload->check_interval,
						'failure_prediction_options' => $payload->failure_prediction_options,
						'check_timeperiod_object_id' => $payload->check_period,
						'notification_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->notification_period),
						'eventhandler_command_args' => $payload->eventhandler_command_args,
						'eventhandler_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $payload->event_handler),
						'check_command_args' => $payload->check_command_args,
						'check_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $payload->check_command),
						'importance' => $payload->importance,
						'display_name' => $payload->display_name,
						'host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $payload->host_name),
						'id' => $serviceId,
					]
				];
				$result = $this->Objects->saveAll($data);
		
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);
				unset($data, $result);
					
				break;
		}
	}
	
	
	public function gearmanConnect(){
		$this->worker= new GearmanWorker();
		$this->worker->addServer();
		
		// These quese are orderd by priority!
		$this->worker->addFunction("objects", array($this, 'dumpObjects'));
		$this->worker->addFunction("hoststatus", array($this, 'my_bob'));
		$this->worker->addFunction("servicestatus", array($this, 'my_bob'));
		$this->worker->addFunction("trash", array($this, 'my_bob'));
		
	}
	
	public function my_bob($job){
		//print_r(json_decode($job->workload()));
		//echo PHP_EOL;
	}
	
	public function clearObjectsCache(){
		$this->objectCache = [
			12 => [],
			11 => [],
			9 =>  [],
			8 =>  [],
			7 =>  [],
			6 =>  [],
			5 =>  [],
			4 =>  [],
			3 =>  [],
			2 =>  [],
			1 =>  []
		];
	}
	
	public function buildObjectsCache(){
		$objects = $this->Objects->find('all', [
			'recursive' => -1 //drops associated data, so we dont get an memory limit error, while processing big data ;)
		]);
		foreach($objects as $object){
			$this->objectCache[$object['Objects']['objecttype_id']][$object['Objects']['name1'].$object['Objects']['name2']] = [
				'name1' => $object['Objects']['name1'],
				'name2' => $object['Objects']['name2'],
				'id' => $object['Objects']['id'],
			];
		}
	}
	
	public function objectIdFromCache($objecttype_id, $name1, $name2 = null){
		if(isset($this->objectCache[$objecttype_id][$name1.$name2]['id'])){
			return $this->objectCache[$objecttype_id][$name1.$name2]['id'];
		}
		
		return null;
	}
	
	public function addObjectToCache($objecttype_id, $id, $name1, $name2 = null){
		if(!isset($this->objectCache[$objecttype_id][$name1.$name2])){
			$this->objectCache[$objecttype_id][$name1.$name2] = [
				'name1' => $name1,
				'name2' => $name2,
				'id' => $id,
			];
			return true;
		}
		return false;
	}
	
	public function idByObjectIdFromDb($Model, $object_id){
		$result = $this->{$Model}->findByObjectId($object_id);
		if(isset($result[$Model]['id']) && $result[$Model]['id'] !== null){
			return $result[$Model]['id'];
		}
		
		return null;
	}
	
	private function _constants(){
		$constants = [
			'OBJECT_COMMAND'        => 12,
			'OBJECT_TIMEPERIOD'     => 9,
			'OBJECT_CONTACT'        => 10,
			'OBJECT_CONTACTGROUP'   => 11,
			'OBJECT_HOST'           =>  1,
			'OBJECT_SERVICE'        =>  2,
			'OBJECT_HOSTGROUP'      =>  3,
			'OBJECT_SERVICEGROUP'   =>  4,
			'OBJECT_HOSTESCALATION' =>  5,
		];
		foreach($constants as $key => $value){
			define($key, $value);
		}
	}
	

}