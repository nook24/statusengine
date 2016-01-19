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
* This is the CakePHP based StatusengineLegacyShell. This shell can read the data
* out of the Gearman Job Server and push it to an MySQL database.
* The Legacy shell has nearly exact the same behavior than a very known solution.
*
* Generate a new schema dump:
* Console/cake schema generate --plugin Legacy --file legacy_schema.php --connection legacy
*
**********************************************************************************/

class StatusengineLegacyShell extends AppShell{
	public $tasks = ['Memcached', 'Perfdata'];

	//Load models out of Plugin/Legacy/Model
	public $uses = [
		//Never ever drop/truncate this table!
		'Legacy.Objects',

		//Live data
		'Legacy.Command',
		'Legacy.Timeperiod',
		'Legacy.Timerange',
		'Legacy.Timerange',
		'Legacy.Contact',
		'Legacy.Contactgroup',
		'Legacy.Contactgroupmember',
		'Legacy.Host',
		'Legacy.Parenthost',
		'Legacy.Hostcontactgroup',
		'Legacy.Hostcontact',
		'Legacy.Customvariable',
		'Legacy.Hostgroup',
		'Legacy.Hostgroupmember',
		'Legacy.Service',
		'Legacy.Servicecontactgroup',
		'Legacy.Servicecontact',
		'Legacy.Parentservice',
		'Legacy.Servicegroup',
		'Legacy.Servicegroupmember',
		'Legacy.Programmstatus',
		'Legacy.Contactstatus',
		'Legacy.Contactnotificationcommand',
		'Legacy.Contactaddress',
		'Legacy.Hostescalation',
		'Legacy.Hostescalationcontacts',
		'Legacy.Hostescalationcontactgroup',
		'Legacy.Serviceescalation',
		'Legacy.Serviceescalationcontact',
		'Legacy.Serviceescalationcontactgroup',
		'Legacy.Hostdependency',
		'Legacy.Servicedependency',

		//Archive data
		'Legacy.Hoststatus',
		'Legacy.Servicestatus',
		'Legacy.Servicecheck',
		'Legacy.Hostcheck',
		'Legacy.Statehistory',
		'Legacy.Logentry',
		'Legacy.Comment',
		'Legacy.Externalcommand',
		'Legacy.Acknowledgement',
		'Legacy.Flapping',
		'Legacy.Downtimehistory',
		'Legacy.Scheduleddowntime',
		'Legacy.Notification',
		'Legacy.Contactnotification',
		'Legacy.Contactnotificationmethod',
		'Legacy.Eventhandler',

		//Other tables
		'Legacy.Systemcommand',
		'Legacy.Instance',
		'Legacy.Processdata',
		'Legacy.Dbversion',
		'Legacy.Configfile',
		'Legacy.Configvariable'
	];

	/**
	 * StatusengineLegacyShell's construct
	 *
	 * Will set some needed class variables and constants.
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		Configure::load('Statusengine');
		$this->childPids = [];
		$this->_constants();
		$this->clearQ = false;

		//the Gearman worker
		$this->worker = null;
		$this->createParentHosts = [];
		$this->createParentServices = [];

		//We only start dumping objects to the db if this is true.
		//If you kill the script while it dumps data, you may be have problems on restart statusengine.
		//If you killed it on dump, restart statusengine and restart nagios
		$this->dumpObjects = true;

		$this->fakeLastInsertId = 1;
	}

	/**
	 * CakePHP's option parser
	 *
	 * Parse the parameters, if the user enter some (example: -w or --help)
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 * @link http://book.cakephp.org/2.0/en/console-and-shells.html#configuring-an-option-parser-with-the-fluent-interface
	 *
	 * @return $parser Object
	 */
	public function getOptionParser(){
		$parser = parent::getOptionParser();
		$parser->addOptions([
			'worker' => ['short' => 'w', 'help' => 'worker bases mode'],
		]);
		return $parser;
	}

	/**
	 * Print the welcome massage of Statusengine
	 * Overwrite: parent::_welcome()
	 *
	 * @return void
	 */
	public function _welcome(){
		$this->out();
		$this->out('<info>Welcome Statusengine v'.Configure::read('version').'</info>');
		$this->hr();
		$this->out('Statusengine runs in legacy mode right now...');
		$this->out('Visit https://statusengine.org/documentation.php#What-is-legacy-mode for more information');
		$this->hr();
	}

	/**
	 * Gets called if a user run the shell over Console/cake statusengine_legacy
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function main(){
		$this->instance_id = Configure::read('instance_id');
		$this->config_type = Configure::read('config_type');

		$this->parentPid = getmypid();
		$this->parser = $this->getOptionParser();
		CakeLog::info('Starting Statusengine in legacy mode.');
		$this->servicestatus_freshness = Configure::read('servicestatus_freshness');

		$this->processPerfdata = Configure::read('process_perfdata');

		$this->processPerfdataCache = [];
		if($this->processPerfdata === true){
			Configure::load('Perfdata');
			$this->PerfdataConfig = Configure::read('perfdata');
			$this->Perfdata->init($this->PerfdataConfig);
		}


		$this->useMemcached = false;

		$this->MemcachedProcessingType = 0;
		if(Configure::read('memcached.use_memcached') === true){
			if($this->Memcached->init()){
				$this->useMemcached = true;
				$this->MemcachedProcessingType = (int)Configure::read('memcached.processing_type');
				if(Configure::read('memcached.drop_on_start') === true){
					$this->Memcached->deleteAll();
				}
			}

		}

		if(array_key_exists('worker', $this->params)){
			$this->workerMode = true;
			$this->forkWorker();
		}else{
			$this->workerMode = false;
			$this->createInstance();
			$this->clearObjectsCache();
			$this->buildObjectsCache();
			$this->buildHoststatusCache();
			$this->buildServicestatusCache();
			$this->Scheduleddowntime->cleanup();
			$this->Dbversion->save([
				'Dbversion' => [
					'name' => 'Statusengine',
					'version' => Configure::read('version')
				]
			]);

			$this->gearmanConnect();
			CakeLog::info('Lets rock!');
		}

	}

	/**
	 * This disable (set is_atvice to 0) in objects table
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function disableAll(){
		//Disable every object in objects, because nagios was restared
		$this->Objects->updateAll(['Objects.is_active' => 0]);
	}

	/**
	 * Dump all objects to the DB
	 *
	 * If there are entries in gearmands Q objects, this function will process them
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param Gearmans $job object
	 * @return void
	 */
	public function dumpObjects($job){
		if($this->clearQ){
			return;
		}

		$payload = json_decode($job->workload());
		$this->Objects->create();
		switch($payload->object_type){
			case START_OBJECT_DUMP:
				if($this->workerMode === true){
					$this->sendSignal(SIGUSR2);
				}

				$this->dumpObjects = true;
				$this->fakeLastInsertId = 1;
				CakeLog::info('Start dumping objects');
				$this->disableAll();
				//Legacy behavior :(
				$truncate = [
					'Command',
					'Timeperiod',
					'Timerange',
					'Contact',
					'Contactgroup',
					'Contactgroupmember',
					'Host',
					'Parenthost',
					'Hostcontactgroup',
					'Hostcontact',
					'Customvariable',
					'Hostgroup',
					'Hostgroupmember',
					'Service',
					'Servicecontactgroup',
					'Servicecontact',
					'Parentservice',
					'Servicegroup',
					'Servicegroupmember',
					'Contactstatus',
					'Contactnotificationcommand',
					//'Contactnotification',
					'Contactaddress',
					'Hostescalation',
					'Hostescalationcontacts',
					'Hostescalationcontactgroup',
					'Serviceescalation',
					'Serviceescalationcontact',
					'Serviceescalationcontactgroup',
					'Hostdependency',
					'Servicedependency',



					'Systemcommand',
					'Configfile',
					'Configvariable'
				];
				foreach($truncate as $Model){
					$this->{$Model}->deleteAll(true);
				}

				$this->clearObjectsCache();
				$this->buildObjectsCache();
				$this->createParentHosts = [];
				$this->createParentServices = [];
				break;

			case FINISH_OBJECT_DUMP:
				CakeLog::info('Finished dumping objects');
				$this->buildHoststatusCache();
				$this->buildServicestatusCache();
				$this->saveParentHosts();
				$this->saveParentServices();
				//We are done with object dumping and can write parent hosts and services to DB

				CakeLog::info('Start dumping core config '.Configure::read('coreconfig').' to database');
				$this->dumpCoreConfig();
				CakeLog::info('Core config dump finished');

				if($this->workerMode === true){
					$this->sendSignal(SIGUSR1);
				}

				if($this->workerMode === false && $this->processPerfdata === true){
					$this->buildProcessPerfdataCache();
				}

				$this->dumpObjects = false;
				break;

			//Command object
			case OBJECT_COMMAND:
				if($this->dumpObjects === false){
					break;
				}
				$this->Command->create();
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->command_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->command_name),
						'instance_id' => $this->instance_id,
					]
				];

				$result = $this->Objects->save($data);

				$data = [
					'Command' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'object_id' => $result['Objects']['object_id'],
						'command_line' => $payload->command_line
					]
				];

				$this->Command->rawInsert([$data], false);
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->command_name);

				unset($result, $data);
			break;

			//Timeperiod object
			case OBJECT_TIMEPERIOD:
				if($this->dumpObjects === false){
					break;
				}
				$this->Timeperiod->create();
				$timeperiodObjectId = $this->objectIdFromCache($payload->object_type, $payload->name);

				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $timeperiodObjectId,
						'instance_id' => $this->instance_id,
					],
				];
				$result = $this->Objects->save($data);

				$data = [
					'Timeperiod' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'timeperiod_object_id' => $result['Objects']['object_id'],
						'alias' => $payload->alias,
					]
				];

				$result = $this->Timeperiod->save($data);

				foreach($payload->timeranges as $day => $timerangesPerDay){
					foreach($timerangesPerDay as $timerange){
						$this->Timerange->create();
						if(isset($timerange->start) && isset($timerange->end)){
							$data = [
								'Timerange' => [
									'instance_id' => $this->instance_id,
									'timeperiod_id' => $result['Timeperiod']['timeperiod_id'],
									'start_sec' => $timerange->start,
									'end_sec' => $timerange->end,
									'day' => $day
								]
							];
							$this->Timerange->save($data);
						}else{
							$data = [
								'Timerange' => [
									'instance_id' => $this->instance_id,
									'timeperiod_id' => $result['Timeperiod']['timeperiod_id'],
									'start_sec' => 0,
									'end_sec' => 0,
									'day' => $day
								]
							];
							$this->Timerange->save($data);
						}
					}
				}

				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);

				unset($result, $data);
				break;

			//Contact object
			case OBJECT_CONTACT:
				if($this->dumpObjects === false){
					break;
				}
				$this->Contact->create();
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->name),
						'instance_id' => $this->instance_id,
					]
				];
				$result = $this->Objects->save($data);
				$data = [
					'Contact' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'contact_object_id' => $result['Objects']['object_id'],
						'alias' => $payload->alias,
						'email_address' => $payload->email,
						'pager_address' => $payload->pager,
						'host_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->host_notification_period),
						'service_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->service_notification_period),
						'host_notifications_enabled' => $payload->host_notifications_enabled,
						'service_notifications_enabled' => $payload->service_notifications_enabled,
						'can_submit_commands' => $payload->can_submit_commands,
						'notify_service_recovery' => $payload->notify_on_service_recovery,
						'notify_service_warning' => $payload->notify_on_service_warning,
						'notify_service_unknown' => $payload->notify_on_service_unknown,
						'notify_service_critical' => $payload->notify_on_service_critical,
						'notify_service_flapping' => $payload->notify_on_service_flapping,
						'notify_service_downtime' => $payload->notify_on_service_downtime,
						'notify_host_recovery' => $payload->notify_on_host_recovery,
						'notify_host_down' => $payload->notify_on_host_down,
						'notify_host_unreachable' => $payload->notify_on_host_unreachable,
						'notify_host_flapping' => $payload->notify_on_host_flapping,
						'notify_host_downtime' => $payload->notify_on_host_downtime,
						'minimum_importance' => $payload->minimum_value,
					]
				];

				$result = $this->Contact->save($data);

				$i = 0;
				foreach($payload->address as $address){
					if($address === null){
						continue;
					}
					$this->Contactaddress->create();
					$this->Contactaddress->save([
						'Contactaddress' => [
							'instance_id' => $this->instance_id,
							'contact_id' => $result['Contact']['contact_id'],
							'address_number' => $i,
							'address' => $address
						]
					]);
					$i++;
				}

				unset($i);

				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);

				//Add Contactnotificationcommand record
				foreach($payload->host_commands as $command){
					$this->Contactnotificationcommand->create();
					$notifyCommand = $this->parseCheckCommand($command->command_name);
					$data = [
						'Contactnotificationcommand' => [
							'instance_id' => $this->instance_id,
							'contact_id' => $result['Contact']['contact_id'],
							'notification_type' => 0,
							'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $notifyCommand[0]),
							'command_args' => $notifyCommand[1]
						]
					];
					$this->Contactnotificationcommand->save($data);
				}

				foreach($payload->service_commands as $command){
					$this->Contactnotificationcommand->create();
					$notifyCommand = $this->parseCheckCommand($command->command_name);
					$data = [
						'Contactnotificationcommand' => [
							'instance_id' => $this->instance_id,
							'contact_id' => $result['Contact']['contact_id'],
							'notification_type' => 1,
							'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $notifyCommand[0]),
							'command_args' => $notifyCommand[1]
						]
					];
					$this->Contactnotificationcommand->save($data);
				}


				unset($result, $data);
			break;

			//Contactgroup object
			case OBJECT_CONTACTGROUP:
				if($this->dumpObjects === false){
					break;
				}
				$this->Contactgroup->create();
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->group_name),
						'instance_id' => $this->instance_id,
					],
				];

				$result = $this->Objects->save($data);

				$data = [
					'Contactgroup' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'contactgroup_object_id' => $result['Objects']['object_id'],
						'alias' => $payload->alias,
					]
				];

				$result = $this->Contactgroup->save($data);
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->group_name);

				//associate contactgroups with contacts
				foreach($payload->contact_members as $ContactName){
					$this->Contactgroupmember->create();
					$data = [
						'Contactgroupmember' => [
							'instance_id' => $this->instance_id,
							'contactgroup_id' => $result['Contactgroup']['contactgroup_id'],
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $ContactName),
						]
					];
					$this->Contactgroupmember->rawInsert([$data], false);
				}

				unset($result, $data);
				break;

			//Host object
			case OBJECT_HOST:
				if($this->dumpObjects === false){
					break;
				}
				$this->Host->create();
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->name),
						'instance_id' => $this->instance_id,
					]
				];

				$result = $this->Objects->save($data);
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->name);

				$checkCommand = $this->parseCheckCommand($payload->check_command);
				$eventHandlerCommand = $this->parseCheckCommand($payload->event_handler);

				$data = [
					'Host' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'host_object_id' => $result['Objects']['object_id'],
						'alias' => $payload->alias,
						'display_name' => $payload->display_name,
						'address' => $payload->address,
						'check_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $checkCommand[0]),
						'check_command_args' => $checkCommand[1],
						'eventhandler_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $eventHandlerCommand[0], null, 0),
						'eventhandler_command_args' => $eventHandlerCommand[1],
						'notification_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->notification_period),
						'check_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->check_period),
						'failure_prediction_options' => 0,
						'check_interval' => $payload->check_interval,
						'retry_interval' => $payload->retry_interval,
						'max_check_attempts' => $payload->max_attempts,
						'first_notification_delay' => $payload->first_notification_delay,
						'notification_interval' => $payload->notification_interval,
						'notify_on_down' => $payload->notify_on_down,
						'notify_on_unreachable' => $payload->notify_on_unreachable,
						'notify_on_recovery' => $payload->notify_on_recovery,
						'notify_on_flapping' => $payload->notify_on_flapping,
						'notify_on_downtime' => $payload->notify_on_downtime,
						'stalk_on_up' => $payload->stalk_on_up,
						'stalk_on_down' => $payload->stalk_on_down,
						'stalk_on_unreachable' => $payload->stalk_on_unreachable,
						'flap_detection_enabled' => $payload->flap_detection_enabled,
						'flap_detection_on_up' => $payload->flap_detection_on_up,
						'flap_detection_on_down' => $payload->flap_detection_on_down,
						'flap_detection_on_unreachable' => $payload->flap_detection_on_unreachable,
						'low_flap_threshold' => $payload->low_flap_threshold,
						'high_flap_threshold' => $payload->high_flap_threshold,
						'process_performance_data' => $payload->process_performance_data,
						'freshness_checks_enabled' => $payload->check_freshness,
						'freshness_threshold' => $payload->freshness_threshold,
						'passive_checks_enabled' => $payload->accept_passive_checks,
						'event_handler_enabled' => $payload->event_handler_enabled,
						'active_checks_enabled' => $payload->checks_enabled,
						'retain_status_information' => $payload->retain_status_information,
						'retain_nonstatus_information' => $payload->retain_nonstatus_information,
						'notifications_enabled' => $payload->notifications_enabled,
						'obsess_over_host' => $payload->obsess,
						'failure_prediction_enabled' => (isset($payload->failure_prediction_enabled))?$payload->failure_prediction_enabled:0,
						'notes' => $this->notNull($payload->notes),
						'notes_url' => $this->notNull($payload->notes_url),
						'action_url' => $this->notNull($payload->action_url),
						'icon_image' => $this->notNull($payload->icon_image),
						'icon_image_alt' => $this->notNull($payload->icon_image_alt),
						'vrml_image' => $this->notNull($payload->vrml_image),
						'statusmap_image' => $this->notNull($payload->statusmap_image),
						'have_2d_coords' => $this->notNull($payload->have_2d_coords),
						'x_2d' => $this->notNull($payload->x_2d),
						'y_2d' => $this->notNull($payload->y_2d),
						'have_3d_coords' => $this->notNull($payload->have_3d_coords),
						'x_3d' => $this->notNull($payload->x_3d),
						'y_3d' => $this->notNull($payload->y_3d),
						'z_3d' => $this->notNull($payload->z_3d),
						'importance' => $payload->hourly_value
					]
				];

				$result = $this->Host->save($data);
				//$lastInsertId = $this->Host->rawSave([$data]);
				foreach($payload->parent_hosts as $parentHost){
					$this->createParentHosts[$result['Host']['host_id']][] = $parentHost;
				}

				foreach($payload->contactgroups as $contactgroupName){
					$this->Hostcontactgroup->create();
					$data = [
						'Hostcontactgroup' => [
							'instance_id' => $this->instance_id,
							'host_id' => $result['Host']['host_id'],
							'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $contactgroupName)
						]
					];
					$this->Hostcontactgroup->save($data);
				}

				foreach($payload->contacts as $contactName){
					$this->Hostcontact->create();
					$data = [
						'Hostcontact' => [
							'instance_id' => $this->instance_id,
							'host_id' => $result['Host']['host_id'],
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName)
						]
					];
					$this->Hostcontact->rawInsert([$data], false);
				}

				foreach($payload->custom_variables as $varName => $varValue){
					$this->Customvariable->create();
					$data = [
						'Customvariable' => [
							'instance_id' => $this->instance_id,
							'object_id' => $this->objectIdFromCache($payload->object_type, $payload->name),
							'config_type' => $this->config_type,
							'has_been_modified' => 0,
							'varname' => $varName,
							'varvalue' => $varValue
						]
					];
					$this->Customvariable->save($data);
				}

				unset($data, $result);
				break;

			case OBJECT_HOSTGROUP:
				if($this->dumpObjects === false){
					break;
				}
				$this->Hostgroup->create();

				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->group_name),
						'instance_id' => $this->instance_id,
					]
				];

				$result = $this->Objects->save($data);

				$data = [
					'Hostgroup' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'hostgroup_object_id' => $result['Objects']['object_id'],
						'alias' => $payload->alias
					]
				];

				$result = $this->Hostgroup->save($data);

				foreach($payload->members as $hostName){
					$this->Hostgroupmember->create();
					$data = [
						'Hostgroupmember' => [
							'instance_id' => $this->instance_id,
							'hostgroup_id' => $result['Hostgroup']['hostgroup_id'],
							'host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $hostName)
						]
					];
					$this->Hostgroupmember->rawInsert([$data], false);
				}

				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->group_name);

				break;

			//Service object
			case OBJECT_SERVICE:
				if($this->dumpObjects === false){
					break;
				}
				//$this->Service->create();

				$objectId = $this->objectIdFromCache(OBJECT_SERVICE, $payload->host_name, $payload->description);

				/*
				 * NOTICE
				 * !!! THIS IS TESTING CODE AND WILL BE REMOVED SOON OR BE REPLEACED !!!
				 */
				/*if($objectId == null){
					$result = $this->Objects->insertObjects([
						'instance_id' => $this->instance_id,
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->description,
						'is_active' => 1,
					]);
					$objectId = $result;
				}else{
					$result = $this->Objects->insertObjects([
						'object_id' => $objectId,
						'instance_id' => $this->instance_id,
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->description,
						'is_active' => 1,
					]);
				}*/

				//CakePHP default
				//$data = [
				//	'Objects' => [
				//		'objecttype_id' => $payload->object_type,
				//		'name1' => $payload->host_name,
				//		'name2' => $payload->description,
				//		'is_active' => 1,
				//		'object_id' => $objectId,
				//		'instance_id' => $this->instance_id,
				//	]
				//];
				//$result = $this->Objects->save($data);

				if($objectId === null){
					$data = [
						'Objects' => [
							'instance_id' => $this->instance_id,
							'objecttype_id' => $payload->object_type,
							'name1' => $payload->host_name,
							'name2' => $payload->description,
							'is_active' => 1,
						]
					];
					//Insert new record
					$objectId = $this->Objects->rawInsert([$data], true);
				}else{
					$data = [
						'Objects' => [
							'object_id' => $objectId,
							'instance_id' => $this->instance_id,
							'objecttype_id' => $payload->object_type,
							'name1' => $payload->host_name,
							'name2' => $payload->description,
							'is_active' => 1,
						]
					];
					//Update + on duplicate key update
					$this->Objects->rawSave([$data], false);
				}

				//$objectId = $result['Objects']['object_id'];

				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $objectId, $payload->host_name, $payload->description);

				$checkCommand = $this->parseCheckCommand($payload->check_command);
				if($this->objectIdFromCache(OBJECT_COMMAND, $checkCommand[0]) == null){
					debug($checkCommand);
				}

				$eventHandlerCommand = $this->parseCheckCommand($payload->event_handler);


				$data = [
					'Service' => [
						'service_id' => $this->fakeLastInsertId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $payload->host_name),
						'service_object_id' => $objectId,
						'display_name' => $payload->display_name,
						'check_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $checkCommand[0]),
						'check_command_args' => $checkCommand[1],
						'eventhandler_command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $eventHandlerCommand[0], null, 0),
						'eventhandler_command_args' => $eventHandlerCommand[1],
						'notification_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->notification_period),
						'check_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->check_period),
						'failure_prediction_options' => 0,
						'check_interval' => $payload->check_interval,
						'retry_interval' => $payload->retry_interval,
						'max_check_attempts' => $payload->max_attempts,
						'first_notification_delay' => $payload->first_notification_delay,
						'notification_interval' => $payload->notification_interval,
						'notify_on_warning' => $payload->notify_on_warning,
						'notify_on_unknown' => $payload->notify_on_unknown,
						'notify_on_critical' => $payload->notify_on_critical,
						'notify_on_recovery' => $payload->notify_on_recovery,
						'notify_on_flapping' => $payload->notify_on_flapping,
						'notify_on_downtime' => $payload->notify_on_downtime,
						'stalk_on_ok' => $payload->stalk_on_ok,
						'stalk_on_warning' => $payload->stalk_on_warning,
						'stalk_on_unknown' => $payload->stalk_on_unknown,
						'stalk_on_critical' => $payload->stalk_on_critical,
						'is_volatile' => $payload->is_volatile,
						'flap_detection_enabled' => $payload->flap_detection_enabled,
						'flap_detection_on_ok' => $payload->flap_detection_on_ok,
						'flap_detection_on_warning' => $payload->flap_detection_on_warning,
						'flap_detection_on_unknown' => $payload->flap_detection_on_unknown,
						'flap_detection_on_critical' => $payload->flap_detection_on_critical,
						'low_flap_threshold' => $payload->low_flap_threshold,
						'high_flap_threshold' => $payload->high_flap_threshold,
						'process_performance_data' => $payload->process_performance_data,
						'freshness_checks_enabled' => $payload->check_freshness,
						'freshness_threshold' => $payload->freshness_threshold,
						'passive_checks_enabled' => $payload->accept_passive_checks,
						'event_handler_enabled' => $payload->event_handler_enabled,
						'active_checks_enabled' => $payload->checks_enabled,
						'retain_status_information' => $payload->retain_status_information,
						'retain_nonstatus_information' => $payload->retain_nonstatus_information,
						'notifications_enabled' => $payload->notifications_enabled,
						'obsess_over_service' => $payload->obsess,
						'failure_prediction_enabled' => (isset($payload->failure_prediction_enabled))?$payload->failure_prediction_enabled:0,
						'notes' => $this->notNull($payload->notes),
						'notes_url' => $this->notNull($payload->notes_url),
						'action_url' => $this->notNull($payload->action_url),
						'icon_image' => $this->notNull($payload->icon_image),
						'icon_image_alt' => $this->notNull($payload->icon_image_alt),
						'importance' => $payload->hourly_value
					]
				];
				$result = $this->Service->rawSave([$data], false);
				$lastInsertId = null;
				//if(isset($result['Service']['service_id'])){
				//	$lastInsertId = $result['Service']['service_id'];
				//}

				$lastInsertId = $this->fakeLastInsertId;

				if($lastInsertId == null){
					$this->fakeLastInsertId++;
					continue;
				}

				//$lastInsertId = $this->Service->rawInsert([$data]);
				unset($data);

				//Must run if all services are in the database, or we get in trouble!
				foreach($payload->parent_services as $parentService){
					$this->createParentServices[$lastInsertId][] = [
						'host_name' => $payload->host_name,
						'description' => $payload->description

					];
				}

				if(!empty($payload->contactgroups)){
					foreach($payload->contactgroups as $contactgroupName){
						$this->Servicecontactgroup->create();
						$data = [
							'Servicecontactgroup' => [
								'instance_id' => $this->instance_id,
								'service_id' => $lastInsertId,
								'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $contactgroupName)
							]
						];
						$this->Servicecontactgroup->save($data);
					}
				}

				foreach($payload->contacts as $contactName){
					$this->Servicecontact->create();
					$data = [
						'Servicecontact' => [
							'instance_id' => $this->instance_id,
							'service_id' => $lastInsertId,
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName)
						]
					];
					$this->Servicecontact->rawInsert([$data], false);
				}

				foreach($payload->custom_variables as $varName => $varValue){
					$this->Customvariable->create();
					$data = [
						'Customvariable' => [
							'instance_id' => $this->instance_id,
							'object_id' => $objectId,
							'config_type' => $this->config_type,
							'has_been_modified' => 0,
							'varname' => $varName,
							'varvalue' => $varValue
						]
					];
					$this->Customvariable->save($data);
				}

				unset($data, $result, $objectId);
				$this->fakeLastInsertId++;
				break;

			case OBJECT_SERVICEGROUP:
				if($this->dumpObjects === false){
					break;
				}
				$this->Servicegroup->create();
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->group_name),
						'instance_id' => $this->instance_id,
					]
				];

				$result = $this->Objects->save($data);

				$data = [
					'Servicegroup' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'servicegroup_object_id' => $result['Objects']['object_id'],
						'alias' => $payload->alias
					]
				];

				$result = $this->Servicegroup->save($data);

				foreach($payload->members as $ServiceArray){
					$this->Servicegroupmember->create();
					$data = [
						'Servicegroupmember' => [
							'instance_id' => $this->instance_id,
							'servicegroup_id' => $result['Servicegroup']['servicegroup_id'],
							'service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $ServiceArray->host_name, $ServiceArray->service_description)
						]
					];
					$this->Servicegroupmember->rawInsert([$data], false);
				}

				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->group_name);
				unset($data, $result);
				break;

			case OBJECT_HOSTESCALATION:
				if($this->dumpObjects === false){
					break;
				}
				//$this->Hostescalation->create();
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->host_name),
						'instance_id' => $this->instance_id,
					]
				];
				$this->Objects->save($data);
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->host_name);

				$this->Hostescalation->create();
				$data = [
					'Hostescalation' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $payload->host_name),
						'timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->escalation_period),
						'first_notification' => $payload->first_notification,
						'last_notification' => $payload->last_notification,
						'notification_interval' => $payload->notification_interval,
						'escalate_on_recovery' => $payload->escalate_on_recovery,
						'escalate_on_down' => $payload->escalate_on_down,
						'escalate_on_unreachable' => $payload->escalate_on_unreachable,
					]
				];
				$result = $this->Hostescalation->save($data);

				foreach($payload->contacts as $contactName){
					$this->Hostescalationcontacts->create();
					$data = [
						'Hostescalationcontacts' => [
							'instance_id' => $this->instance_id,
							'hostescalation_id' => $result['Hostescalation']['hostescalation_id'],
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName),
						]
					];
					$this->Hostescalationcontacts->save($data);
				}

				foreach($payload->contactgroups as $groupName){
					$this->Hostescalationcontactgroup->create();
					$data = [
						'Hostescalationcontactgroup' => [
							'instance_id' => $this->instance_id,
							'hostescalation_id' => $result['Hostescalation']['hostescalation_id'],
							'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $groupName),
						]
					];
					$this->Hostescalationcontactgroup->save($data);
				}
				unset($data, $result);
				break;

			case OBJECT_SERVICEESCALATION:
				if($this->dumpObjects === false){
					break;
				}
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->description,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->host_name, $payload->description),
						'instance_id' => $this->instance_id,
					]
				];
				$this->Objects->save($data);
				//Add the object to objectCache
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->host_name);

				$this->Serviceescalation->create();
				$data = [
					'Serviceescalation' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $payload->host_name, $payload->description),
						'timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->escalation_period),
						'first_notification' => $payload->first_notification,
						'last_notification' => $payload->last_notification,
						'notification_interval' => $payload->notification_interval,
						'escalate_on_recovery' => $payload->escalate_on_recovery,
						'escalate_on_warning' => $payload->escalate_on_warning,
						'escalate_on_unknown' => $payload->escalate_on_unknown,
						'escalate_on_critical' => $payload->escalate_on_critical,
					]
				];

				$result = $this->Serviceescalation->save($data);

				foreach($payload->contacts as $contactName){
					$this->Serviceescalationcontact->create();
					$data = [
						'Serviceescalationcontact' => [
							'instance_id' => $this->instance_id,
							'serviceescalation_id' => $result['Serviceescalation']['serviceescalation_id'],
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName),
						]
					];
					$this->Serviceescalationcontact->save($data);
				}

				foreach($payload->contactgroups as $groupName){
					$this->Serviceescalationcontactgroup->create();
					$data = [
						'Serviceescalationcontactgroup' => [
							'instance_id' => $this->instance_id,
							'serviceescalation_id' => $result['Serviceescalation']['serviceescalation_id'],
							'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $groupName),
						]
					];
					$this->Serviceescalationcontactgroup->save($data);
				}
				unset($data, $result);
				break;

			case OBJECT_HOSTDEPENDENCY:
				if($this->dumpObjects === false){
					break;
				}
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => null,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->host_name),
						'instance_id' => $this->instance_id,
					]
				];

				$result = $this->Objects->save($data);
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->host_name);
				$this->Hostdependency->create();
				$data = [
					'Hostdependency' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $payload->host_name),
						'dependent_host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $payload->dependent_host_name),
						'dependency_type' => $payload->dependency_type,
						'inherits_parent' => $payload->inherits_parent,
						'timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->dependency_period),
						'fail_on_up' => $payload->fail_on_up,
						'fail_on_down' => $payload->fail_on_down,
						'fail_on_unreachable' => $payload->fail_on_unreachable,
					]
				];

				$this->Hostdependency->save($data);

				unset($data, $result);
				break;

			case OBJECT_SERVICEDEPENDENCY:
				if($this->dumpObjects === false){
					break;
				}
				$data = [
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->service_description,
						'is_active' => 1,
						//Update record, if exists
						'object_id' => $this->objectIdFromCache($payload->object_type, $payload->host_name, $payload->service_description),
						'instance_id' => $this->instance_id,
					]
				];

				$result = $this->Objects->save($data);
				$this->addObjectToCache($payload->object_type, $this->Objects->id, $payload->host_name, $payload->service_description);

				$this->Servicedependency->create();
				$data = [
					'Servicedependency' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $payload->host_name, $payload->service_description),
						'dependent_service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $payload->dependent_host_name, $payload->dependent_service_description),
						'dependency_type' => $payload->dependency_type,
						'inherits_parent' => $payload->inherits_parent,
						'timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->dependency_period),
						'fail_on_ok' => $payload->fail_on_ok,
						'fail_on_warning' => $payload->fail_on_warning,
						'fail_on_unknown' => $payload->fail_on_unknown,
						'fail_on_critical' => $payload->fail_on_critical
					]
				];
				$this->Servicedependency->save($data);
				unset($result, $data);
				break;
		}
	}

	/**
	 * This function handle every entry out of gearmans hoststatus Q
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param Gearmans $job object
	 * @return void
	 */
	public function processHoststatus($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		if($this->useMemcached === true){
			$this->Memcached->setHoststatus($payload);
			if($this->MemcachedProcessingType === 1){
				return;
			}
		}

		//$this->Hoststatus->create();

		if($this->objectIdFromCache(OBJECT_HOST, $payload->hoststatus->name) === null){
			return;
		}

		$hoststatusId = $this->hoststatusIdFromCache($this->objectIdFromCache(OBJECT_HOST, $payload->hoststatus->name));
		$hostObjectId = $this->objectIdFromCache(OBJECT_HOST, $payload->hoststatus->name);
		//debug('Hoststatus Id: '.$hoststatusId);
		if($hostObjectId == null){
			//Object has gone
			return;
		}

		$data = [
			'Hoststatus' => [
				'hoststatus_id' => $hoststatusId,
				'instance_id' => $this->instance_id,
				'host_object_id' => $hostObjectId,
				'status_update_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'output' => $this->notNull($payload->hoststatus->plugin_output, ''),
				'long_output' => $this->notNull($payload->hoststatus->long_plugin_output, ''),
				'perfdata' => $this->notNull($payload->hoststatus->perf_data, ''),
				'current_state' => $payload->hoststatus->current_state,
				'has_been_checked' => $payload->hoststatus->has_been_checked,
				'should_be_scheduled' => $payload->hoststatus->should_be_scheduled,
				'current_check_attempt' => $payload->hoststatus->current_attempt,
				'max_check_attempts' => $payload->hoststatus->max_attempts,
				'last_check' => date('Y-m-d H:i:s', $payload->hoststatus->last_check),
				'next_check' => date('Y-m-d H:i:s', $payload->hoststatus->next_check),
				'check_type' => $payload->hoststatus->check_type,
				'last_state_change' => date('Y-m-d H:i:s', $payload->hoststatus->last_state_change),
				'last_hard_state_change' => date('Y-m-d H:i:s', $payload->hoststatus->last_hard_state_change),
				'last_hard_state' => $payload->hoststatus->last_hard_state,
				'last_time_up' => date('Y-m-d H:i:s', $payload->hoststatus->last_time_up),
				'last_time_down' => date('Y-m-d H:i:s', $payload->hoststatus->last_time_down),
				'last_time_unreachable' => date('Y-m-d H:i:s', $payload->hoststatus->last_time_unreachable),
				'state_type' => $payload->hoststatus->state_type,
				'last_notification' => date('Y-m-d H:i:s', $payload->hoststatus->last_notification),
				'next_notification' => date('Y-m-d H:i:s', $payload->hoststatus->next_notification),
				'no_more_notifications' => $payload->hoststatus->no_more_notifications,
				'notifications_enabled' => $payload->hoststatus->notifications_enabled,
				'problem_has_been_acknowledged' => $payload->hoststatus->problem_has_been_acknowledged,
				'acknowledgement_type' => $payload->hoststatus->acknowledgement_type,
				'current_notification_number' => $payload->hoststatus->current_notification_number,
				'passive_checks_enabled' => $payload->hoststatus->accept_passive_checks,
				'active_checks_enabled' => $payload->hoststatus->checks_enabled,
				'event_handler_enabled' => $payload->hoststatus->event_handler_enabled,
				'flap_detection_enabled' => $payload->hoststatus->flap_detection_enabled,
				'is_flapping' => $payload->hoststatus->is_flapping,
				'percent_state_change' => $payload->hoststatus->percent_state_change,
				'latency' => $payload->hoststatus->latency,
				'execution_time' => $payload->hoststatus->execution_time,
				'scheduled_downtime_depth' => $payload->hoststatus->scheduled_downtime_depth,
				'failure_prediction_enabled' => 0,
				'process_performance_data' => $payload->hoststatus->process_performance_data,
				'obsess_over_host' => $payload->hoststatus->obsess,
				'modified_host_attributes' => 0 /*$payload->hoststatus->modified_host_attributes*/,
				'event_handler' => $this->notNull($payload->hoststatus->event_handler, ''),
				'check_command' => $payload->hoststatus->check_command,
				'normal_check_interval' => $payload->hoststatus->check_interval,
				'retry_check_interval' => $payload->hoststatus->retry_interval,
				'check_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->hoststatus->check_period)
			]
		];

		if($hoststatusId == null){
			$result = $this->Hoststatus->save($data);
		}else{
			$result = $this->Hoststatus->rawSave([$data]);
		}

		if($hoststatusId == null){
			$this->addToHoststatusCache($hostObjectId, $result['Hoststatus']['hoststatus_id']);
		}
	}

	/**
	 * This function handle every entry out of gearmans servicestatus Q
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param Gearmans $job object
	 * @return void
	 */
	public function processServicestatus($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		//Drop old servicestatus entries
		if($payload->timestamp < (time() - $this->servicestatus_freshness)){
			return;
		}
		//$this->Servicestatus->create();

		if($this->useMemcached === true){
			$this->Memcached->setServicestatus($payload);
			if($this->MemcachedProcessingType === 1){
				return;
			}
		}

		$service_object_id = $this->objectIdFromCache(OBJECT_SERVICE, $payload->servicestatus->host_name, $payload->servicestatus->description);

		if($service_object_id === null){
			//Object has gone
			return;
		}

		//$servicestatus_id = $this->servicestatusIdFromCache($service_object_id);

		//debug('Servicestatus Id: '.$servicestatus_id);
		//debug($payload);
		$data = [
			'Servicestatus' => [
				//'servicestatus_id' => $servicestatus_id,
				'instance_id' => $this->instance_id,
				'service_object_id' => $service_object_id,
				'status_update_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'output' => $this->notNull($payload->servicestatus->plugin_output, ''),
				'long_output' => $this->notNull($payload->servicestatus->long_plugin_output, ''),
				'perfdata' => $this->notNull($payload->servicestatus->perf_data, ''),
				'current_state' => $payload->servicestatus->current_state,
				'has_been_checked' => $payload->servicestatus->has_been_checked,
				'should_be_scheduled' => $payload->servicestatus->should_be_scheduled,
				'current_check_attempt' => $payload->servicestatus->current_attempt,
				'max_check_attempts' => $payload->servicestatus->max_attempts,
				'last_check' => date('Y-m-d H:i:s', $payload->servicestatus->last_check),
				'next_check' => date('Y-m-d H:i:s', $payload->servicestatus->next_check),
				'check_type' => $payload->servicestatus->check_type,
				'last_state_change' => date('Y-m-d H:i:s', $payload->servicestatus->last_state_change),
				'last_hard_state_change' => date('Y-m-d H:i:s', $payload->servicestatus->last_hard_state_change),
				'last_hard_state' => $payload->servicestatus->last_hard_state,
				'last_time_ok' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_ok),
				'last_time_warning' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_warning),
				'last_time_unknown' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_unknown),
				'last_time_critical' => date('Y-m-d H:i:s', $payload->servicestatus->last_time_critical),
				'state_type' => $payload->servicestatus->state_type,
				'last_notification' => date('Y-m-d H:i:s', $payload->servicestatus->last_notification),
				'next_notification' => date('Y-m-d H:i:s', $payload->servicestatus->next_notification),
				'no_more_notifications' => $payload->servicestatus->no_more_notifications,
				'notifications_enabled' => $payload->servicestatus->notifications_enabled,
				'problem_has_been_acknowledged' => $payload->servicestatus->problem_has_been_acknowledged,
				'acknowledgement_type' => $payload->servicestatus->acknowledgement_type,
				'current_notification_number' => $payload->servicestatus->current_notification_number,
				'passive_checks_enabled' => $payload->servicestatus->accept_passive_checks,
				'active_checks_enabled' => $payload->servicestatus->checks_enabled,
				'event_handler_enabled' => $payload->servicestatus->event_handler_enabled,
				'flap_detection_enabled' => $payload->servicestatus->flap_detection_enabled,
				'is_flapping' => $payload->servicestatus->is_flapping,
				'percent_state_change' => $payload->servicestatus->percent_state_change,
				'latency' => $payload->servicestatus->latency,
				'execution_time' => $payload->servicestatus->execution_time,
				'scheduled_downtime_depth' => $payload->servicestatus->scheduled_downtime_depth,
				'failure_prediction_enabled' => 0,
				'process_performance_data' => $payload->servicestatus->process_performance_data,
				'obsess_over_service' => $payload->servicestatus->obsess,
				'modified_service_attributes' => 0/*$payload->servicestatus->modified_service_attributes*/,
				'event_handler' => $this->notNull($payload->servicestatus->event_handler, ''),
				'check_command' => $payload->servicestatus->check_command,
				'normal_check_interval' => $payload->servicestatus->check_interval,
				'retry_check_interval' => $payload->servicestatus->retry_interval,
				'check_timeperiod_object_id' => $this->objectIdFromCache(OBJECT_TIMEPERIOD, $payload->servicestatus->check_period),
			]
		];

		$result = $this->Servicestatus->rawSaveServicestatus([$data]);

		/*if($servicestatus_id == null){
			$result = $this->Servicestatus->save($data);
		}else{
			$result = $this->Servicestatus->rawSave([$data]);
		}

		if($servicestatus_id == null){
			$this->addToServicestatusCache($service_object_id, $result['Servicestatus']['servicestatus_id']);
		}*/
	}

	/**
	 * This function handle every entry out of gearmans servicechecks Q
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param Gearmans $job object
	 * @return void
	 */
	public function processServicechecks($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		$service_object_id = $this->objectIdFromCache(OBJECT_SERVICE, $payload->servicecheck->host_name, $payload->servicecheck->service_description);
		if($service_object_id === null){
			//CakeLog::debug(var_export($this->objectCache ,true));
			return;
		}

		$checkCommand = $this->parseCheckCommand($payload->servicecheck->command_name);

		//$this->Servicecheck->create();

		$data = [
			'Servicecheck' => [
				'instance_id' => $this->instance_id,
				'service_object_id' => $service_object_id,
				'check_type' => $payload->servicecheck->check_type,
				'current_check_attempt' => $payload->servicecheck->current_attempt,
				'max_check_attempts' => $payload->servicecheck->max_attempts,
				'state' => $payload->servicecheck->state,
				'state_type' => $payload->servicecheck->state_type,
				'start_time' => date('Y-m-d H:i:s', $payload->servicecheck->start_time),
				'start_time_usec' => $this->notNull($payload->servicecheck->start_time, 0),
				'end_time' => date('Y-m-d H:i:s', $payload->servicecheck->end_time),
				'end_time_usec' => $this->notNull($payload->servicecheck->end_time),
				'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $checkCommand[0]),
				'command_args' => $checkCommand[1],
				'command_line' => $payload->servicecheck->command_line,
				'timeout' => $payload->servicecheck->timeout,
				'early_timeout' => $payload->servicecheck->early_timeout,
				'execution_time' => $payload->servicecheck->execution_time,
				'latency' => $payload->servicecheck->latency,
				'return_code' => $payload->servicecheck->return_code,
				'output' => $payload->servicecheck->output,
				'long_output' => $payload->servicecheck->long_output,
				'perfdata' => $payload->servicecheck->perf_data,
			]
		];

		$this->Servicecheck->rawInsert([$data], false);

		if($this->processPerfdata === true && $payload->servicecheck->perf_data !== null){
			//process_performance_data == 1 ?
			if(isset($this->processPerfdataCache[$service_object_id])){
				$parsedPerfdataString = [
					'DATATYPE' => 'SERVICEPERFDATA',
					'TIMET' => $payload->servicecheck->start_time,
					'HOSTNAME' => $payload->servicecheck->host_name,
					'SERVICEDESC' => $payload->servicecheck->service_description,
					'SERVICEPERFDATA' => $payload->servicecheck->perf_data,
					'SERVICECHECKCOMMAND' => $payload->servicecheck->command_name,
					'SERVICESTATE' => $payload->servicecheck->state,
					'SERVICESTATETYPE' => $payload->servicecheck->state_type
				];

				$parsedPerfdata = $this->Perfdata->parsePerfdataString($payload->servicecheck->perf_data);
				$rrdReturn = $this->Perfdata->writeToRrd($parsedPerfdataString, $parsedPerfdata);
				if($this->PerfdataConfig['XML']['write_xml_files'] === true){
					$this->Perfdata->updateXml($parsedPerfdataString, $parsedPerfdata, $rrdReturn);
				}
			}
		}
	}

	public function processHostchecks($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		$host_object_id = $this->objectIdFromCache(OBJECT_HOST, $payload->hostcheck->host_name);

		if($host_object_id === null){
			return;
		}

		$checkCommand = $this->parseCheckCommand($payload->hostcheck->command_name);

		//$this->Hostcheck->create();

		$is_raw_check = 0;
		if($payload->type == 802 || $payload->type == 803){
			$is_raw_check = 1;
		}

		$data = [
			'Hostcheck' => [
				'instance_id' => $this->instance_id,
				'host_object_id' => $host_object_id,
				'check_type' => $payload->hostcheck->check_type,
				'is_raw_check' => $is_raw_check,
				'current_check_attempt' => $payload->hostcheck->current_attempt,
				'max_check_attempts' => $payload->hostcheck->max_attempts,
				'state' => $payload->hostcheck->state,
				'state_type' => $payload->hostcheck->state_type,
				'start_time' => date('Y-m-d H:i:s', $payload->hostcheck->start_time),
				'start_time_usec' => $this->notNull($payload->hostcheck->start_time, 0),
				'end_time' => date('Y-m-d H:i:s', $payload->hostcheck->end_time),
				'end_time_usec' => $this->notNull($payload->hostcheck->end_time),
				'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $checkCommand[0]),
				'command_args' => $checkCommand[1],
				'command_line' => $payload->hostcheck->command_line,
				'timeout' => $payload->hostcheck->timeout,
				'early_timeout' => $payload->hostcheck->early_timeout,
				'execution_time' => $payload->hostcheck->execution_time,
				'latency' => $payload->hostcheck->latency,
				'return_code' => $payload->hostcheck->return_code,
				'output' => $payload->hostcheck->output,
				'long_output' => $payload->hostcheck->long_output,
				'perfdata' => $payload->hostcheck->perf_data,
			]
		];

		$this->Hostcheck->rawInsert([$data], false);
	}

	public function processStatechanges($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		$object_id = $this->getObjectIdForPayload($payload, 'statechange');

		if($this->useMemcached=== true && $payload->statechange->state == 0){
			//Delete ack from memcached if record exists
			$this->Memcached->deleteAcknowledgementIfExists($payload);
		}

		if($object_id === null){
			//Object has gone
			return;
		}

		//$this->Statehistory->create();
		$data = [
			'Statehistory' => [
				'instance_id' => $this->instance_id,
				'state_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'state_time_usec' => $payload->timestamp,
				'object_id' => $object_id,
				'state_change' => $payload->statechange->statechange_type,
				'state' => $payload->statechange->state,
				'state_type' => $payload->statechange->state_type,
				'current_check_attempt' => $payload->statechange->current_attempt,
				'max_check_attempts' => $payload->statechange->max_attempts,
				'last_state' => $payload->statechange->last_state,
				'last_hard_state' => $payload->statechange->last_hard_state,
				'output' => $payload->statechange->output,
				'long_output' => $payload->statechange->long_output,
			]
		];

		$this->Statehistory->rawInsert([$data], false);
	}

	public function processLogentries($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		//$this->Logentry->create();
		$data = [
			'Logentry' => [
				'instance_id' => $this->instance_id,
				'logentry_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'entry_time' => date('Y-m-d H:i:s', $payload->logentry->entry_time),
				'entry_time_usec' => $this->notNull($payload->logentry->entry_time, 0),
				'logentry_type' => $payload->logentry->data_type,
				'logentry_data' => $payload->logentry->data,
				'realtime_data' => 1, //this is hardcoded in ndo?
				'inferred_data_extracted' => 1 //this is hardcoded in ndo?
			]
		];

		$this->Logentry->rawInsert([$data], false);
	}

	public function processSystemcommands($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		//$this->Systemcommand->create();
		$data = [
			'Systemcommand' => [
				'instance_id' => $this->instance_id,
				'start_time' => date('Y-m-d H:i:s', $payload->systemcommand->start_time),
				'start_time_usec' => $payload->systemcommand->start_time,
				'end_time' => date('Y-m-d H:i:s', $payload->systemcommand->end_time),
				'end_time_usec' => $payload->systemcommand->end_time,
				'command_line' => $payload->systemcommand->command_line,
				'timeout' => $payload->systemcommand->timeout,
				'early_timeout' => $payload->systemcommand->early_timeout,
				'execution_time' => $payload->systemcommand->execution_time,
				'return_code' => $payload->systemcommand->return_code,
				'output' => $payload->systemcommand->output,
				'long_output' => $payload->systemcommand->long_output,
			]
		];

		$this->Systemcommand->rawInsert([$data], false);
	}

	public function processComments($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		$object_id = $this->getObjectIdForPayload($payload, 'comment');

		if($object_id === null){
			//Object has gone
			return;
		}

		//$this->Comment->create();
		$data = [
			'Comment' => [
				'instance_id' => $this->instance_id,
				'entry_time' => date('Y-m-d H:i:s', $payload->comment->entry_time),
				'entry_time_usec' => $payload->comment->entry_time,
				'comment_type' => $payload->comment->comment_type,
				'entry_type' => $payload->comment->entry_time,
				'object_id' => $object_id,
				'comment_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'internal_comment_id' => $payload->comment->comment_id,
				'author_name' => $payload->comment->author_name,
				'comment_data' => $payload->comment->comment_data,
				'is_persistent' => $payload->comment->persistent,
				'comment_source' => $payload->comment->source,
				'expires' => $payload->comment->expires,
				'expiration_time' => $payload->comment->expire_time
			]
		];

		$this->Comment->rawInsert([$data], false);
	}

	public function processExternalcommands($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		//$this->Externalcommand->create();
		$data = [
			'Externalcommand' => [
				'instance_id' => $this->instance_id,
				'entry_time' => date('Y-m-d H:i:s', $payload->externalcommand->entry_time),
				'command_type' => $payload->externalcommand->command_type,
				'command_name' => $payload->externalcommand->command_string,
				'command_args' => $payload->externalcommand->command_args
			]
		];

		$this->Externalcommand->rawInsert([$data], false);
	}

	public function processAcknowledgements($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		$object_id = $this->getObjectIdForPayload($payload, 'acknowledgement');

		if($object_id === null){
			//Object has gone
			return;
		}

		if($this->useMemcached=== true){
			//Add a record in memcached
			$this->Memcached->setAcknowledgement($payload);
		}

		//$this->Acknowledgement->create();
		$data = [
			'Acknowledgement' => [
				'instance_id' => $this->instance_id,
				'entry_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'entry_time_usec' => $payload->timestamp,
				'acknowledgement_type' => $payload->acknowledgement->acknowledgement_type,
				'object_id' => $object_id,
				'state' => $payload->acknowledgement->state,
				'author_name' => $payload->acknowledgement->author_name,
				'comment_data' => $payload->acknowledgement->comment_data,
				'is_sticky' => $payload->acknowledgement->is_sticky,
				'persistent_comment' => $payload->acknowledgement->persistent_comment,
				'notify_contacts' => $payload->acknowledgement->notify_contacts,
			]
		];

		$this->Acknowledgement->rawInsert([$data], false);
	}

	public function processFlappings($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		$object_id = $this->getObjectIdForPayload($payload, 'flapping');

		if($object_id === null){
			//Object has gone
			return;
		}

		//$this->Flapping->create();
		$data = [
			'Flapping' => [
				'instance_id' => $this->instance_id,
				'event_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'event_time_usec' => $payload->timestamp,
				'event_type' => $payload->type,
				'reason_type' => $payload->attr,
				'flapping_type' => $payload->flapping->flapping_type,
				'object_id' => $object_id,
				'percent_state_change' => $payload->flapping->percent_change,
				'low_threshold' => $payload->flapping->low_threshold,
				'high_threshold' => $payload->flapping->high_threshold,
				'comment_time' => $this->notNull($payload->flapping->comment_entry_time, 0),
				'internal_comment_id' => $payload->flapping->comment_id
			]
		];

		$this->Flapping->rawInsert([$data], false);
	}

	public function processDowntimes($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		$object_id = $this->getObjectIdForPayload($payload, 'downtime');

		if($this->useMemcached === true){
			$this->Memcached->setDowntime($payload);
		}

		if($object_id === null){
			//Object has gone
			return;
		}

		if($payload->type == 1100 || $payload->type == 1102){
			//Add a new downtime
			$downtime = $this->Downtimehistory->find('first', [
				'conditions' => [
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'internal_downtime_id' => $payload->downtime->downtime_id,
				]
			]);

			if(isset($downtime['Downtimehistory']['downtimehistory_id']) && $downtime['Downtimehistory']['downtimehistory_id'] !== null){
				$downtimehistory_id = $downtime['Downtimehistory']['downtimehistory_id'];
			}else{
				$downtimehistory_id = null;
				$this->Downtimehistory->create();
			}

			$data = [
				'Downtimehistory' => [
					'downtimehistory_id' => $downtimehistory_id,
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'author_name' => $payload->downtime->author_name,
					'comment_data' => $payload->downtime->comment_data,
					'internal_downtime_id' => $payload->downtime->downtime_id,
					'triggered_by_id' => $payload->downtime->triggered_by,
					'is_fixed' => $payload->downtime->fixed,
					'duration' => $payload->downtime->duration,
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'was_started' => 0,
					'actual_start_time' =>  date('Y-m-d H:i:s', 0),
					'actual_start_time_usec' => 0,
					'actual_end_time' => date('Y-m-d H:i:s', 0),
					'actual_end_time_usec' => 0,
					'was_cancelled' => 0,
				]
			];
			$result = $this->Downtimehistory->save($data);


			//Scheduleddowntime table data
			$downtime = $this->Scheduleddowntime->find('first', [
				'conditions' => [
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'internal_downtime_id' => $payload->downtime->downtime_id,
				]
			]);


			if(isset($downtime['Scheduleddowntime']['scheduleddowntime_id']) && $downtime['Scheduleddowntime']['scheduleddowntime_id'] !== null){
				$scheduleddowntime_id = $downtime['Scheduleddowntime']['scheduleddowntime_id'];
			}else{
				$scheduleddowntime_id = null;
				$this->Scheduleddowntime->create();
			}

			$data = [
				'Scheduleddowntime' => [
					'scheduleddowntime_id' => $scheduleddowntime_id,
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'author_name' => $payload->downtime->author_name,
					'comment_data' => $payload->downtime->comment_data,
					'internal_downtime_id' => $payload->downtime->downtime_id,
					'triggered_by_id' => $payload->downtime->triggered_by,
					'is_fixed' => $payload->downtime->fixed,
					'duration' => $payload->downtime->duration,
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'was_started' => 0,
					'actual_start_time' => date('Y-m-d H:i:s', 0),
					'actual_start_time_usec' => 0,
				]
			];
			$this->Scheduleddowntime->save($data);
		}

		if($payload->type == 1103){
			//The downtime exists, and was started now
			$downtime = $this->Downtimehistory->find('first', [
				'conditions' => [
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'internal_downtime_id' => $payload->downtime->downtime_id,
				]
			]);
			if(isset($downtime['Downtimehistory']['downtimehistory_id']) && $downtime['Downtimehistory']['downtimehistory_id'] !== null){
					//The downtime was found in DB so we can update the record
				$data = [
					'Downtimehistory' => [
						'downtimehistory_id' => $downtime['Downtimehistory']['downtimehistory_id'],
						'was_started' => 1,
						'actual_start_time_usec' => $payload->timestamp,
						'actual_start_time' => date('Y-m-d H:i:s', $payload->timestamp),
					]
				];
				$result = $this->Downtimehistory->save($data);
			}

			//Update scheduledowntime table
			$downtime = $this->Scheduleddowntime->find('first', [
				'conditions' => [
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'internal_downtime_id' => $payload->downtime->downtime_id,
				]
			]);

			if(isset($downtime['Scheduleddowntime']['scheduleddowntime_id']) && $downtime['Scheduleddowntime']['scheduleddowntime_id'] !== null){
				$data = [
					'Scheduleddowntime' => [
						'scheduleddowntime_id' => $downtime['Scheduleddowntime']['scheduleddowntime_id'],
						'was_started' => 1,
						'actual_start_time' => date('Y-m-d H:i:s', $payload->timestamp),
						'actual_start_time_usec' => $payload->timestamp,
					]
				];
				$this->Scheduleddowntime->save($data);
			}
		}

		if($payload->type == 1104){
			//The downtime exists, but ends now
			$downtime = $this->Downtimehistory->find('first', [
				'conditions' => [
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'internal_downtime_id' => $payload->downtime->downtime_id,
				]
			]);

			if(isset($downtime['Downtimehistory']['downtimehistory_id']) && $downtime['Downtimehistory']['downtimehistory_id'] !== null){
				//The downtime was found in DB so we can update the record
				$data = [
					'Downtimehistory' => [
						'downtimehistory_id' => $downtime['Downtimehistory']['downtimehistory_id'],
						'actual_end_time' => date('Y-m-d H:i:s', $payload->timestamp),
						'actual_end_time_usec' => $payload->timestamp,
						'was_cancelled' => ($payload->attr == 2)?1:0
					]
				];
				$result = $this->Downtimehistory->save($data);
			}

			//Update scheduledowntime table
			$downtime = $this->Scheduleddowntime->find('first', [
				'conditions' => [
					'instance_id' => $this->instance_id,
					'downtime_type' => $payload->downtime->downtime_type,
					'object_id' => $object_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->downtime->entry_time),
					'scheduled_start_time' => date('Y-m-d H:i:s', $payload->downtime->start_time),
					'scheduled_end_time' => date('Y-m-d H:i:s', $payload->downtime->end_time),
					'internal_downtime_id' => $payload->downtime->downtime_id,
				]
			]);

			if(isset($downtime['Scheduleddowntime']['scheduleddowntime_id']) && $downtime['Scheduleddowntime']['scheduleddowntime_id'] !== null){
				$this->Scheduleddowntime->delete($downtime['Scheduleddowntime']['scheduleddowntime_id']);
			}
		}
	}

	public function processProcessdata($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		//$this->Processdata->create();
		$data = [
			'Processdata' => [
				'instance_id' => $this->instance_id,
				'event_type' => $payload->type,
				'event_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'event_time_usec' => $payload->timestamp,
				'process_id' => $payload->processdata->pid,
				'program_name' => $payload->processdata->programmname,
				'program_version' => $payload->processdata->programmversion,
				'program_date' => $payload->processdata->modification_data,
			]
		];

		$this->Processdata->rawInsert([$data], false);
	}

	public function processNotifications($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		if($payload->type != 601){
			//I guess everything else is trash, contacts_notified = 0 start_time = 0 and stuff like this :/
			return;
		}

		$object_id = $this->getObjectIdForPayload($payload, 'notification_data');

		if($object_id === null){
			//Object has gone
			return;
		}
		$this->Notification->create();
		$data = [
			'Notification' => [
				'instance_id' => $this->instance_id,
				'notification_type' => $payload->notification_data->notification_type,
				'notification_reason' => $payload->notification_data->reason_type,
				'object_id' => $object_id,
				'start_time' => date('Y-m-d H:i:s', $payload->notification_data->start_time),
				'start_time_usec' => $payload->notification_data->start_time,
				'end_time' => date('Y-m-d H:i:s', $payload->notification_data->end_time),
				'end_time_usec' => $payload->notification_data->end_time,
				'state' => $payload->notification_data->state,
				'output' => $payload->notification_data->output,
				'long_output' => $payload->notification_data->long_output,
				'escalated' => $payload->notification_data->escalated,
				'contacts_notified' => $payload->notification_data->contacts_notified,
			]
		];
		$result = $this->Notification->save($data);
	}

	public function processProgrammstatus($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		//$this->Programmstatus->create();
		$data = [
			'Programmstatus' => [
				'programstatus_id' => 1,
				'instance_id' => $this->instance_id,
				'status_update_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'program_start_time' => date('Y-m-d H:i:s', $payload->programmstatus->program_start),
				//nobody knows how to get this time, because it's not possible ;)
				'program_end_time' => date('Y-m-d H:i:s', 0),
				'is_currently_running' => 1,
				'process_id' => $payload->programmstatus->pid,
				'daemon_mode' =>$payload->programmstatus->daemon_mode,
				'last_command_check' =>$payload->programmstatus->last_command_check,
				'last_log_rotation' =>$payload->programmstatus->last_log_rotation,
				'notifications_enabled' =>$payload->programmstatus->notifications_enabled,
				'active_service_checks_enabled' =>$payload->programmstatus->active_service_checks_enabled,
				'passive_service_checks_enabled' =>$payload->programmstatus->passive_service_checks_enabled,
				'active_host_checks_enabled' =>$payload->programmstatus->active_host_checks_enabled,
				'passive_host_checks_enabled' =>$payload->programmstatus->passive_host_checks_enabled,
				'event_handlers_enabled' =>$payload->programmstatus->event_handlers_enabled,
				'flap_detection_enabled' =>$payload->programmstatus->flap_detection_enabled,
				'failure_prediction_enabled' =>$payload->programmstatus->failure_prediction_enabled,
				'process_performance_data' =>$payload->programmstatus->process_performance_data,
				'obsess_over_hosts' =>$payload->programmstatus->obsess_over_hosts,
				'obsess_over_services' =>$payload->programmstatus->obsess_over_services,
				'modified_host_attributes' =>$payload->programmstatus->modified_host_attributes,
				'modified_service_attributes' =>$payload->programmstatus->modified_service_attributes,
				'global_host_event_handler' =>$payload->programmstatus->global_host_event_handler,
				'global_service_event_handler' =>$payload->programmstatus->global_service_event_handler,
			]
		];

		$this->Programmstatus->rawSave([$data], false);
	}

	public function processContactstatus($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());
		$contactObjectId = $this->objectIdFromCache(OBJECT_CONTACT, $payload->contactstatus->contact_name);
		//Update record if exists
		$contactstatus = $this->Contactstatus->findByContactObjectId($contactObjectId);
		//I'm not sure, if this will called by nagios for udate?!
		if(isset($contactstatus['Contactstatus']['contactstatus_id']) && $contactstatus['Contactstatus']['contactstatus_id'] !== null){
			$contactstatus_id = $contactstatus['Contactstatus']['contactstatus_id'];
		}else{
			$this->Contactstatus->create();
			$contactstatus_id = null;
		}

		if($contactObjectId === null){
			//Object has gone
			return;
		}

		$data = [
			'Contactstatus' => [
				'contactstatus_id' => $contactstatus_id,
				'instance_id' => $this->instance_id,
				'contact_object_id' => $contactObjectId,
				'status_update_time' => date('Y-m-d H:i:s', $payload->timestamp),
				'host_notifications_enabled' => $payload->contactstatus->host_notifications_enabled,
				'service_notifications_enabled' => $payload->contactstatus->service_notifications_enabled,
				'last_host_notification' => date('Y-m-d H:i:s', $payload->contactstatus->last_host_notification),
				'last_service_notification' => date('Y-m-d H:i:s', $payload->contactstatus->last_service_notification),
				'modified_attributes' => $payload->contactstatus->modified_attributes,
				'modified_host_attributes' => $payload->contactstatus->modified_host_attributes,
				'modified_service_attributes' => $payload->contactstatus->modified_service_attributes
			]
		];

		$this->Contactstatus->save($data);
	}

	public function processContactnotificationdata($job){
		if($this->clearQ){
			return;
		}

		$payload = json_decode($job->workload());

		if($payload->type != 603){
			//I guess everyting else is trash ?
			return;
		}
		$objectId = $this->getObjectIdForPayload($payload, 'contactnotificationdata');

		//Find notification_id
		$notification = $this->Notification->find('first', [
			'conditions' => [
				'Notification.start_time = FROM_UNIXTIME('.$payload->contactnotificationdata->start_time.')',
				'Notification.end_time = FROM_UNIXTIME('.$payload->contactnotificationdata->end_time.')',
				'Notification.object_id' => $objectId
			]
		]);

		if(isset($notification['Notification']['notification_id']) && $notification['Notification']['notification_id'] != null){
			$this->Contactnotification->create();
			$data = [
				'Contactnotification' => [
					'instance_id' => $this->instance_id,
					'notification_id' => $notification['Notification']['notification_id'],
					'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $payload->contactnotificationdata->contact_name),
					'start_time' => date('Y-m-d H:i:s', $payload->contactnotificationdata->start_time),
					'start_time_usec' => $payload->contactnotificationdata->start_time,
					'end_time' => date('Y-m-d H:i:s', $payload->contactnotificationdata->end_time),
					'end_time_usec' => $payload->contactnotificationdata->end_time
				]
			];

			$this->Contactnotification->rawInsert([$data], false);
		}
	}

	public function processContactnotificationmethod($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		if($payload->type !== 605){
			return;
		}

		$contactObjectId = $this->objectIdFromCache(OBJECT_CONTACT, $payload->contactnotificationmethod->contact_name);

		if($contactObjectId === null){
			//Object has gone
			return;
		}

		//Find last contactnotification
		$cn = $this->Contactnotification->find('first', [
			'conditions' => [
				'Contactnotification.start_time = FROM_UNIXTIME('.$payload->contactnotificationmethod->start_time.')',
				'Contactnotification.end_time = FROM_UNIXTIME('.$payload->contactnotificationmethod->end_time.')',
				'contact_object_id' => $contactObjectId
			]
		]);

		if(isset($cn['Contactnotification']['contactnotification_id']) && $cn['Contactnotification']['contactnotification_id'] != null){
			$this->Contactnotificationmethod->create();
			$data = [
				'Contactnotificationmethod' => [
					'instance_id' => $this->instance_id,
					'contactnotification_id' => $cn['Contactnotification']['contactnotification_id'],
					'start_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->start_time),
					'start_time_usec' => $payload->contactnotificationmethod->start_time,
					'end_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->end_time),
					'end_time_usec' => $payload->contactnotificationmethod->end_time,
					'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $payload->contactnotificationmethod->command_name),
					'command_args' => $payload->contactnotificationmethod->command_args
				]
			];
			$rs = $this->Contactnotificationmethod->save($data);
		}
	}

	//May be a little bit buggy?
	public function processEventhandler($job){
		if($this->clearQ){
			return;
		}
		$payload = json_decode($job->workload());

		if($payload->eventhandler->service_description != NULL){
			$object_id = $this->objectIdFromCache(OBJECT_SERVICE, $payload->eventhandler->host_name, $payload->eventhandler->service_description);
			$eventhanderType = 1;
		}else{
			$object_id = $this->objectIdFromCache(OBJECT_HOST, $payload->eventhandler->host_name);
			$eventhanderType = 0;
		}

		$this->Eventhandler->create();

		$data = [
			'Eventhandler' => [
				'instance_id' => $this->instance_id,
				'eventhandler_type' => $eventhanderType,
				'object_id' => $object_id,
				'state' => $payload->eventhandler->state,
				'state_type' => $payload->eventhandler->state_type,
				'start_time' => date('Y-m-d H:i:s', $payload->eventhandler->start_time),
				'start_time_usec' => $payload->eventhandler->start_time,
				'end_time' => date('Y-m-d H:i:s', $payload->eventhandler->end_time),
				'end_time_usec' => $payload->eventhandler->end_time,
				'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $payload->eventhandler->command_name),
				'command_args' => $payload->eventhandler->command_args,
				'command_line' => $payload->eventhandler->command_line,
				'timeout' => $payload->eventhandler->timeout,
				'early_timeout' => $payload->eventhandler->early_timeout,
				'execution_time' => $payload->eventhandler->execution_time,
				'return_code' => $payload->eventhandler->return_code,
				'output' => $payload->eventhandler->output,
				'long_output' => $payload->eventhandler->long_output
			]
		];

		$this->Eventhandler->save($data);
	}

	/**
	 * Parent process connect to the gearman servicer
	 * If you start the programm this function get called from $this->forkWorker() OR $this->main(),
	 * depends on if you start with -w or not
	 * This function will bind the gearman Qs for the parent process
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function gearmanConnect(){
		$this->worker = new GearmanWorker();

		/* Avoid that gearman will stuck at GearmanWorker::work() if no jobs are present
		 * witch is bad because if GearmanWorker::work() stuck, PHP can not execute the signal handler
		 */
		$this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);

		$this->worker->addServer(Configure::read('server'), Configure::read('port'));

		if($this->workerMode === true){
			$this->worker->addFunction('statusngin_objects',        [$this, 'dumpObjects']);
			$this->worker->addFunction('statusngin_programmstatus', [$this, 'processProgrammstatus']);
			$this->worker->addFunction('statusngin_processdata',    [$this, 'processProcessdata']);
		}else{
			// These quese are (more or less) orderd by priority!
			$this->worker->addFunction('statusngin_objects',                    [$this, 'dumpObjects']);
			$this->worker->addFunction('statusngin_servicestatus',              [$this, 'processServicestatus']);
			$this->worker->addFunction('statusngin_hoststatus',                 [$this, 'processHoststatus']);
			$this->worker->addFunction('statusngin_servicechecks',              [$this, 'processServicechecks']);
			$this->worker->addFunction('statusngin_hostchecks',                 [$this, 'processHostchecks']);
			$this->worker->AddFunction('statusngin_statechanges',               [$this, 'processStatechanges']);
			$this->worker->addFunction('statusngin_logentries',                 [$this, 'processLogentries']);
			$this->worker->addFunction('statusngin_systemcommands',             [$this, 'processSystemcommands']);
			$this->worker->addFunction('statusngin_comments',                   [$this, 'processComments']);
			$this->worker->addFunction('statusngin_externalcommands',           [$this, 'processExternalcommands']);
			$this->worker->addFunction('statusngin_acknowledgements',           [$this, 'processAcknowledgements']);
			$this->worker->addFunction('statusngin_flappings',                  [$this, 'processFlappings']);
			$this->worker->addFunction('statusngin_downtimes',                  [$this, 'processDowntimes']);
			$this->worker->addFunction('statusngin_processdata',                [$this, 'processProcessdata']);
			$this->worker->addFunction('statusngin_notifications',              [$this, 'processNotifications']);
			$this->worker->addFunction('statusngin_programmstatus',             [$this, 'processProgrammstatus']);
			$this->worker->addFunction('statusngin_contactstatus',              [$this, 'processContactstatus']);
			$this->worker->addFunction('statusngin_contactnotificationdata',    [$this, 'processContactnotificationdata']);
			$this->worker->addFunction('statusngin_contactnotificationmethod',  [$this, 'processContactnotificationmethod']);
			$this->worker->addFunction('statusngin_eventhandler',               [$this, 'processEventhandler']);

			while($this->worker->work());
		}
	}

	/**
	 * Create the instance in instances table
	 * Not sure if some one realy needs this, but most
	 * software runs selects like "WHERE instance_id = 1"
	 * or some like this
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function createInstance(){
		$this->Instance->create();
		$data = [
			'Instance' => [
				'instance_id' => $this->instance_id,
				'instance_name' => 'main',
				'instance_description' => ''
			]
		];
		$this->Instance->save($data);
	}

	/**
	 * Every time we recive an object we need the object_id to run CRUD (create, read, update, delete)
	 * So we dont want to lookup the object id every time again, so we store them in an cache array
	 * The sorting is done by the objecttype_id
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
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

	/**
	 * This function fills up the cache array with data out of the DB
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function buildObjectsCache(){
		$objects = $this->Objects->find('all', [
			'recursive' => -1 //drops associated data, so we dont get an memory limit error, while processing big data ;)
		]);
		foreach($objects as $object){
			/*if($object['Objects']['objecttype_id'] == OBJECT_SERVICE){
				debug($object);
			}*/
			$this->objectCache[$object['Objects']['objecttype_id']][$object['Objects']['name1'].$object['Objects']['name2']] = [
				'name1' => $object['Objects']['name1'],
				'name2' => $object['Objects']['name2'],
				'object_id' => $object['Objects']['object_id'],
			];
		}
	}

	/**
	 * If an object is inside of the cache, we return the object_id
	 * The object is sorted by the objecttype_id, i didn't check php's source code
	 * but i guess a numeric array is the fastes way in php to acces an array
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param  int    objecttyoe_id The objecttype_id of the current object we want to lookup
	 * @param  string name1 The first name of the object
	 * @param  string name2 The second name of the object, or empty if the object has no name2 (default: null)
	 * @param  mixed  default If we dont find an entry in our cache we retrun the default value (default: null)
	 * @return int    object_id
	 */
	public function objectIdFromCache($objecttype_id, $name1, $name2 = null, $default = null){
		if(isset($this->objectCache[$objecttype_id][$name1.$name2]['object_id'])){
			return $this->objectCache[$objecttype_id][$name1.$name2]['object_id'];
		}

		return $default;
	}

	public function objectIdFromCacheDebug($objecttype_id, $name1, $name2 = null, $default = null){
		CakeLog::debug('name1: '.$name1);
		CakeLog::debug('name1: '.$name2);
		CakeLog::debug('isset: '.(int)isset($this->objectCache[$objecttype_id][$name1.$name2]['object_id']));
		if(isset($this->objectCache[$objecttype_id][$name1.$name2]['object_id'])){
			return $this->objectCache[$objecttype_id][$name1.$name2]['object_id'];
		}

		return $default;
	}

	/**
	 * This function adds an new created object to the object cache, or replace it
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param  int    objecttype_id The objecttype_id of the object you want to add
	 * @param  string name1 of the object
	 * @param  string name2 of the object (default: null)
	 * @return void
	 */
	public function addObjectToCache($objecttype_id, $id, $name1, $name2 = null){
		if(!isset($this->objectCache[$objecttype_id][$name1.$name2])){
			$this->objectCache[$objecttype_id][$name1.$name2] = [
				'name1' => $name1,
				'name2' => $name2,
				'object_id' => $id,
			];
			return true;
		}
		return false;
	}

	/**
	 * Every time we recive an hoststatus we need to update the last record in DB
	 * Cause of we don't want to lookup the id on every update again, we create us an cache of it
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function buildHoststatusCache(){
		$this->hoststatusCache = [];
		foreach($this->Hoststatus->find('all', ['fields' => ['hoststatus_id', 'host_object_id']]) as $hs){
			$this->hoststatusCache[$hs['Hoststatus']['host_object_id']] = $hs['Hoststatus']['hoststatus_id'];
		}
	}

	public function addToHoststatusCache($hostObjectId, $hoststatus){
		$this->hoststatusCache[$hostObjectId] = $hoststatus;
	}

	public function hoststatusIdFromCache($hostObjectId){
		if(isset($this->hoststatusCache[$hostObjectId])){
			return $this->hoststatusCache[$hostObjectId];
		}

		return null;
	}

	/**
	 * Every time we recive an servucestatus we need to update the last record in DB
	 * Cause of we don't want to lookup the id on every update again, we create us an cache of it
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function buildServicestatusCache(){
		$this->servicestatusCache = [];
		foreach($this->Servicestatus->find('all', ['fields' => ['servicestatus_id', 'service_object_id']]) as $ss){
			$this->servicestatusCache[$ss['Servicestatus']['service_object_id']] = $ss['Servicestatus']['servicestatus_id'];
		}
	}

	public function addToServicestatusCache($serviceObjectId, $servicestatus_id){
		$this->servicestatusCache[$serviceObjectId] = $servicestatus_id;
	}

	public function servicestatusIdFromCache($serviceObjectId){
		if(isset($this->servicestatusCache[$serviceObjectId])){
			return $this->servicestatusCache[$serviceObjectId];
		}

		return null;
	}

	public function buildProcessPerfdataCache(){
		$this->processPerfdataCache = [];
		$result = $this->Service->find('all', [
			'conditions' => [
				'process_performance_data' => 1,
			],
			'fields' => [
				'service_object_id'
			]
		]);
		foreach($result as $service){
			$this->processPerfdataCache[$service['Service']['service_object_id']] = true;
		}
	}

	/**
	 * For each CRUD operation we need the object_id
	 * this function will return us the object id for the given $payload
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param  stdobject $payload (normaly from $job)
	 * @param  string    the name of the current payload (For example 'statechange')
	 * @return int       object_id
	 */
	public function getObjectIdForPayload($payload, $payloadName){
		$object_id = null;
		if($payload->{$payloadName}->service_description == null){
			$object_id = $this->objectIdFromCache(OBJECT_HOST, $payload->{$payloadName}->host_name);
		}else{
			$object_id = $this->objectIdFromCache(OBJECT_SERVICE, $payload->{$payloadName}->host_name, $payload->{$payloadName}->service_description);
		}
		return $object_id;
	}

	public function saveParentHosts(){
		foreach($this->createParentHosts as $host_id => $hostNamesAsArray){
			foreach($hostNamesAsArray as $hostName){
				$this->Parenthost->create();
				$data = [
					'Parenthost' => [
						'instance_id' => $this->instance_id,
						'host_id' => $host_id,
						'parent_host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $hostName)
					]
				];
				$this->Parenthost->save($data);
			}
		}
	}

	public function saveParentServices(){
		//CakeLog::debug(var_export($this->createParentServices, true));
		foreach($this->createParentServices as $service_id => $servicesArray){
			foreach($servicesArray as $serviceArray){
				$this->Parentservice->create();
				$data = [
					'Parentservice' => [
						'instance_id' => $this->instance_id,
						'service_id' => $service_id,
						'parent_service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $serviceArray['host_name'], $serviceArray['description'])
					]
				];
				$this->Parentservice->save($data);
			}
		}
	}

	/**
	 * Define the constants for the objecttype_ids.
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	private function _constants(){
		$constants = [
			'OBJECT_COMMAND'           => 12,
			'OBJECT_TIMEPERIOD'        =>  9,
			'OBJECT_CONTACT'           => 10,
			'OBJECT_CONTACTGROUP'      => 11,
			'OBJECT_HOST'              =>  1,
			'OBJECT_SERVICE'           =>  2,
			'OBJECT_HOSTGROUP'         =>  3,
			'OBJECT_SERVICEGROUP'      =>  4,
			'OBJECT_HOSTESCALATION'    =>  5,
			'OBJECT_SERVICEESCALATION' =>  6,
			'OBJECT_HOSTDEPENDENCY'    =>  7,
			'OBJECT_SERVICEDEPENDENCY' =>  8,

			'START_OBJECT_DUMP'     =>  100,
			'FINISH_OBJECT_DUMP'    =>  101,
		];
		foreach($constants as $key => $value){
			define($key, $value);
		}
	}

	public function notNull($field, $default = 0){
		if($field === null){
			return $default;
		}

		return $field;
	}

	/**
	 * Parse the check_command string into command_name and command_arg
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param  string checkCommand from $payload
	 * @return array  [0] => 'lan_ping', [1] => '!80!80'
	 */
	public function parseCheckCommand($checkCommand){
		$cc = explode('!', $checkCommand, 2);
		$return = [];
		if(isset($cc[0])){
			$return[0] = $cc[0];
		}else{
			$return[0] = '';
		}
		if(isset($cc[1])){
			$return[1] = $cc[1];
		}else{
			$return[1] = '';
		}
		return $return;
	}

	/**
	 * This function will fork the child processes (worker) if you run with -w
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function forkWorker(){
		$workers = Configure::read('workers');
		foreach($workers as $worker){
			declare(ticks = 1);
			CakeLog::info('Forking a new worker child');
			$pid = pcntl_fork();
			if(!$pid){
				//We are the child
				CakeLog::info('Hey, my queues are: '.implode(',', array_keys($worker['queues'])));
				$this->bindQueues = true;
				$this->queues = $worker['queues'];
				$this->work = false;
				$this->bindChildSignalHandler();
				$this->waitForInstructions();
			}else{
				//we are the parrent
				$this->childPids[] = $pid;

			}
		}
		pcntl_signal(SIGTERM, [$this, 'signalHandler']);
		pcntl_signal(SIGINT,  [$this, 'signalHandler']);

		//Every worker is created now, so lets rock!

		$this->createInstance();
		$this->clearObjectsCache();
		$this->buildObjectsCache();
		$this->buildHoststatusCache();
		$this->buildServicestatusCache();
		$this->Scheduleddowntime->cleanup();
		$this->Dbversion->save([
			'Dbversion' => [
				'name' => 'Statusengine',
				'version' => Configure::read('version')
			]
		]);

		$this->gearmanConnect();
		CakeLog::info('Lets rock!');
		$this->sendSignal(SIGUSR1);
		$this->worker->setTimeout(1000);


		while(true){
			pcntl_signal_dispatch();
			$this->worker->work();
			if($this->worker->returnCode() == GEARMAN_SUCCESS){
				continue;
			}


			if(!@$this->worker->wait()){
				if($this->worker->returnCode() == GEARMAN_NO_ACTIVE_FDS){
					//Lost connection - lets wait a bit
					sleep(1);
				}
			}
		}
	}

	/**
	 * This is the child process, and it waits for instuctions from the parent
	 * The communication is done by unix signals
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function waitForInstructions(){
		CakeLog::info('Ok, i will wait for instructions');
		if($this->bindQueues === true){
			$this->worker = new GearmanWorker();

			/* Avoid that gearman will stuck at GearmanWorker::work() if no jobs are present
			 * witch is bad because if GearmanWorker::work() stuck, PHP can not execute the signal handler
			 */
			$this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
			$this->worker->setTimeout(1000);

			$this->worker->addServer(Configure::read('server'), Configure::read('port'));
			foreach($this->queues as $queueName => $functionName){
				CakeLog::info('Queue "'.$queueName.'" will be handled by function "'.$functionName.'"');
				$this->worker->addFunction($queueName, [$this, $functionName]);
			}
			$this->bindQueues = false;
		}
		while(true){
			if($this->work === true){
				CakeLog::info('Clear my objects cache');
				$this->clearObjectsCache();

				CakeLog::info('Build up new objects cache');
				$this->buildObjectsCache();
				//CakeLog::debug(var_export($this->objectCache, true));

				CakeLog::info('Build up new hoststatus cache');
				$this->buildHoststatusCache();

				CakeLog::info('Build up new servicestatus cache');
				$this->buildServicestatusCache();

				if($this->processPerfdata === true){
					if(isset($this->queues['statusngin_servicechecks'])){
						CakeLog::info('Build up new process perfdata cache');
						$this->buildProcessPerfdataCache();
					}
				}

				CakeLog::info('I will continue my work');
				$this->childWork();

			}
			pcntl_signal_dispatch();
                        //Check if the parent process still exists
                        if($this->parentPid != posix_getppid()){
                                CakeLog::error('My parent process is gone I guess I am orphaned and will exit now!');
                                exit(3);
                        }
			usleep(250000);
		}
	}

	public function bindChildSignalHandler(){
		pcntl_signal(SIGTERM, [$this, 'childSignalHandler']);
		pcntl_signal(SIGUSR1, [$this, 'childSignalHandler']);
		pcntl_signal(SIGUSR2, [$this, 'childSignalHandler']);
	}

	public function childWork(){
		while($this->work === true){
			pcntl_signal_dispatch();
			$this->worker->work();
			if($this->worker->returnCode() == GEARMAN_SUCCESS){
				continue;
			}

			if(!@$this->worker->wait()){
				if($this->worker->returnCode() == GEARMAN_NO_ACTIVE_FDS){
					sleep(1);
				}
				
				//Check if the parent process still exists
				if($this->parentPid != posix_getppid()){
					CakeLog::error('My parent process is gone I guess I am orphaned and will exit now!');
					exit(3);
				}
			}
		}
	}

	public function childSignalHandler($signo){
		CakeLog::info('Recived signal: '.$signo);
		switch($signo){
			case SIGTERM:
				CakeLog::info('Will kill myself :-(');
				CakeLog::info('Unregister all my queues');
				exit(0);
				break;

			case SIGUSR1:
				//Tell the worker to start its work
				$this->work = true;
				break;

			case SIGUSR2:
				//Tell the worker to stop its work
				$this->work = false;
				break;
		}
		$this->bindChildSignalHandler();
	}

	/**
	 * This is the parent signal handel, so that we can catch SIGTERM and SIGINT
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function signalHandler($signo){
		switch($signo){
			case SIGINT:
			case SIGTERM:
				CakeLog::info('Will kill my childs :-(');
				$this->sendSignal(SIGTERM);
				CakeLog::info('Bye');
				exit(0);
				break;
		}

	}

	/**
	 * This function sends a singal to every child process
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return void
	 */
	public function sendSignal($signal){
		$gmanClient = new GearmanClient();
		$gmanClient->addServer(Configure::read('server'), Configure::read('port'));
		if($signal !== SIGTERM){
			foreach($this->childPids as $cpid){
				CakeLog::info('Send signal to child pid: '.$cpid);
				posix_kill($cpid, $signal);
			}
		}

		if($signal == SIGTERM){
			foreach($this->childPids as $cpid){
				CakeLog::info('Will kill pid: '.$cpid);
				posix_kill($cpid, SIGTERM);
			}
			foreach($this->childPids as $cpid){
				pcntl_waitpid($cpid, $status);
				CakeLog::info('Child ['.$cpid.'] killed successfully');
			}
		}
	}

	/**
	 * Throw the given job away
	 *
	 * @since 1.0.0
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @return true
	 */
	public function devNull($job){
		return true;
	}

	public function dumpCoreConfig(){
		$configFile = Configure::read('coreconfig');

		if(file_exists($configFile)){
			$this->Configfile->create();
			$data = [
				'Configfile' => [
					'instance_id' => $this->instance_id,
					'configfile_type' => 0, // ???
					'configfile_path' => Configure::read('coreconfig')
				]
			];

			$result = $this->Configfile->save($data);


			$coreconfig = fopen($configFile, "r");

			while(!feof($coreconfig)){
				$line = trim(fgets($coreconfig));
				$strpos = strpos($line, '#');

				if($line != '' && ($strpos === false || $strpos > 0)){
					$parsed = explode('=', $line, 2);
					if(isset($parsed[0]) && isset($parsed[1])){
						$this->Configvariable->create();
						$data = [
							'Configvariable' => [
								'instance_id' => $this->instance_id,
								'configfile_id' => $result['Configfile']['configfile_id'],
								'varname' => $parsed[0],
								'varvalue' =>$parsed[1]
							]
						];
						$this->Configvariable->save($data);
					}
				}
			}
		}else{
			CakeLog::info('ERROR: Core config '.$configFile.' not found!!!');
		}
	}
}
