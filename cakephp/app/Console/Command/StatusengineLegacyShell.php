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
	public $tasks = [
		'Memcached',
		'Perfdata',
		'PerfdataBackend',
		'RrdtoolBackend',
		'GraphiteBackend'
	];

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
		'Legacy.Commenthistory',
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
	 * Queues of Status Bulk Operations
	 *
	 * @var array
	 **/
	protected $BulkRepository = [];

	/**
	 * Queues of Objects Bulk Operations
	 *
	 * @var array
	 **/
	protected $ObjectsRepository = [];

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
		$this->out('<info>Welcome Statusengine v'.STATUSENIGNE_VERSION.'</info>');
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
		Configure::load('Statusengine');

		$NebTypes = new NebTypes();
		$NebTypes->defineNebTypesAsGlobals();

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
		$this->dumpStart = null;
		$this->dumpIds = [];

		$this->instance_id = Configure::read('instance_id');
		$this->config_type = Configure::read('config_type');

		$this->fakeObjectId = 1; // maybe instance_id * 1000000 + 1 would be better

		$this->parentPid = getmypid();
		$this->parser = $this->getOptionParser();
		CakeLog::info('Starting Statusengine in legacy mode.');
		$this->servicestatus_freshness = Configure::read('servicestatus_freshness');

		$this->processPerfdata = Configure::read('process_perfdata');

		$this->processPerfdataCache = [];

		$this->useBulkQueries = false;
		$this->useBulkQueries = Configure::read('use_bulk_queries_for_status');

		$this->bulkQueryLimit = 200;
		$this->bulkQueryLimit = Configure::read('bulk_query_limit');

		$this->bulkQueryTime = 10;
		$this->bulkQueryTime = Configure::read('bulk_query_time');

		$this->bulkLastCheck = time();

		$this->lastDatasourcePing = time();

		$emptyMethods = ['truncate', 'delete'];
		$emptyMethod = strtolower(Configure::read('empty_method'));
		if(!in_array($emptyMethod, $emptyMethods)){
			$emptyMethod = 'truncate';
		}
		$this->empty_method = $emptyMethod;

		if($this->processPerfdata === true){
			$this->PerfdataBackend->init(Configure::read());

			if($this->PerfdataBackend->saveToRrd()){
				Configure::load('Perfdata');
				$this->PerfdataConfig = Configure::read('perfdata');
				$this->RrdtoolBackend->init($this->PerfdataConfig);
			}

			if($this->PerfdataBackend->saveToGraphite()){
				Configure::load('Graphite');
				$graphiteConfig = Configure::read('graphite');
				$this->GraphiteBackend->init($graphiteConfig);
			}

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
			$this->Scheduleddowntime->cleanup();
			$this->Dbversion->save([
				'Dbversion' => [
					'name' => 'Statusengine',
					'version' => STATUSENIGNE_VERSION
				]
			]);

			$this->gearmanConnect();

			$this->cacheHostNamesForGraphiteIfRequried();
			$this->cacheServiceNamesForGraphiteIfRequried();
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
	 * Activate Objects (set is_active to 1) for given list of object Ids
	 *
	 * @since 2.0.6
	 * @author Daniel Hoffend <dh@dotlan.net>
	 *
	 * @param array Array with Ids to enable
	 * @return void
	 */
	public function activateObjects($ids){
		if(empty($ids)){
			return;
		}

		$this->Objects->updateAll(['Objects.is_active' => 1], ['Objects.object_id' => $ids]);
	}

	/**
	 * Delete host status records for given list of object Ids
	 *
	 * @since 2.0.6
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param array Array with Ids to remove
	 * @return void
	 */
	public function removeDeprecatedHoststatusRecords($ids){
		if(empty($ids)){
			return;
		}

		$this->Hoststatus->deleteAll(['Hoststatus.host_object_id NOT' => $ids], false);
	}

	/**
	 * Delete service status records for given list of object Ids
	 *
	 * @since 2.0.6
	 * @author Daniel Ziegler <daniel@statusengine.org>
	 *
	 * @param array Array with Ids to remove
	 * @return void
	 */
	public function removeDeprecatedServicestatusRecords($ids){
		if(empty($ids)){
			return;
		}

		$this->Servicestatus->deleteAll(['Servicestatus.service_object_id NOT' => $ids], false);
	}

	/**
	 * Check if object exists in cache, otherwise create it
	 *
	 * @since 2.0.6
	 * @author Daniel Hoffend <dh@dotlan.net>
	 *
	 * @param array $data object attributes
	 * @return integer objectId (cached or new)
	 */
	protected function checkObject(array $data) {

		// get old object id
		$objectId = $this->objectIdFromCache($data['Objects']['objecttype_id'], $data['Objects']['name1'], $data['Objects']['name2']);

		// create new object if object is not there and cache it
		if (!$objectId) {
			$data['Objects']['object_id'] = null;
			$objectResult = $this->Objects->replace($data);
			$objectId = $objectResult['Objects']['object_id'];
			$this->addObjectToCache($data['Objects']['objecttype_id'], $objectId, $data['Objects']['name1'], $data['Objects']['name2']);
		}

		$this->dumpIds[] = $objectId;
		return $objectId;
	}

	/**
	 * check if job payload is valid and log json parsing errors
	 *
	 * @since 2.0.6
	 * @author Daniel Hoffend <dh@dotlan.net>
	 *
	 * @param GearmanJob $job
	 * @return Object|false
	 **/
	protected function getJobPayload(GearmanJob $job)
	{
		$payload = json_decode($job->workload());
		$error = json_last_error();

		// parsing error
		if ($error != JSON_ERROR_NONE) {
			if (function_exists('json_last_error_msg')) {
				 CakeLog::warning('Error while parsing job->workload() - ' . json_last_error_msg());
			} else {
				static $ERRORS = array(
					JSON_ERROR_NONE => 'No error',
					JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
					JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
					JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
					JSON_ERROR_SYNTAX => 'Syntax error',
					JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
				);
				CakeLog::warning('Error while parsing job->workload() - ' . (isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error') );
			}
			CakeLog::debug('Couldn\'t parse: ' . $job->workload());
			return false;

		// parsed object is not an object
		} elseif (!is_object($payload)) {
			CakeLog::warning('Error while parsing job->workload() - Response isn\'t an object');
			CakeLog::debug('Invalid job: ' . $job->workload());
			return false;
		}

		else {
			return $payload;
		}
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

		$this->Objects->getDatasource()->reconnect();

		// check every second if there's something left to push
		if($this->useBulkQueries && $this->bulkLastCheck < time()) {
			foreach ($this->ObjectsRepository AS $repo) {
				$repo->pushIfRequired();
			}
			$this->bulkLastCheck = time();
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}
		$this->Objects->create();
		switch($payload->object_type){
			case START_OBJECT_DUMP:
				if($this->workerMode === true){
					$this->sendSignal(SIGUSR2);
				}

				$this->dumpObjects = true;
				$this->GraphiteBackend->clearCache();
				$this->dumpStart = time();
				$this->dumpIds = [];

				CakeLog::info('Start dumping objects');
				$this->disableAll();
				//Legacy behavior :(
				$truncate = [
					'Command',
					'Comment',
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
					if (strtolower($this->empty_method) == 'delete') {
						CakeLog::debug('Delete from table for '.$Model);
						$this->{$Model}->getDataSource()->rawQuery(sprintf(
							'DELETE FROM %s%s',
							$this->{$Model}->tablePrefix,
							$this->{$Model}->table
						));

						$this->{$Model}->getDataSource()->rawQuery(sprintf(
							'ALTER TABLE %s%s AUTO_INCREMENT = 1',
							$this->{$Model}->tablePrefix,
							$this->{$Model}->table
						));

					} else {
						CakeLog::debug('Truncate table for '.$Model);
						$this->{$Model}->truncate();
					}
				}

				$this->clearObjectsCache();
				$this->buildObjectsCache();
				$this->createParentHosts = [];
				$this->createParentServices = [];
				break;

			case FINISH_OBJECT_DUMP:
				CakeLog::info('Finished dumping objects');

				// flush all objects queues
				if ($this->useBulkQueries) {
					CakeLog::info('Force flushing all bulk queues');
					foreach ($this->ObjectsRepository AS $repo) {
						$repo->push();
					}
				}

				$this->saveParentHosts();
				$this->saveParentServices();

				// activate objects
				CakeLog::info('Enable objects');
				$this->activateObjects($this->dumpIds);

				//Remove deprecated status records
				
				////Removed due to: https://www.percona.com/blog/2011/11/29/avoiding-auto-increment-holes-on-innodb-with-insert-ignore/
				//CakeLog::info('Delete deprecated status records');
				//$this->removeDeprecatedHoststatusRecords($this->dumpIds);
				//$this->removeDeprecatedServicestatusRecords($this->dumpIds);
				
				CakeLog::info('Truncate table hoststatus');
				$this->Hoststatus->truncate();
				CakeLog::info('Truncate table servicestatus');
				$this->Servicestatus->truncate();

				$this->dumpIds = [];

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
				CakeLog::info(sprintf('Objectdump finished in %d seconds', time() - $this->dumpStart));
				break;

			//Command object
			case OBJECT_COMMAND:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->command_name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Save Command
				$data = [
					'Command' => [
						'command_id' => $objectId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'object_id' => $objectId,
						'command_line' => $payload->command_line
					]
				];
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Command']->commit($data['Command']);
				} else {
					$this->Command->rawInsert([$data], false);
				}
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
				$objectResult = $this->Objects->replace($data);

				$data = [
					'Timeperiod' => [
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'timeperiod_object_id' => $objectResult['Objects']['object_id'],
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

				$this->addObjectToCache($payload->object_type, $objectResult['Objects']['object_id'], $payload->name);

				unset($result, $data, $objectResult);
			break;

			//Contact object
			case OBJECT_CONTACT:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Contact
				$data = [
					'Contact' => [
						'contact_id' => $objectId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'contact_object_id' => $objectId,
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
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Contact']->commit($data['Contact']);
				} else {
					$this->Contact->create();
					$this->Contact->save($data);
				}

				$i = 0;
				foreach($payload->address as $address){
					if($address === null){
						continue;
					}
					$data = [
						'Contactaddress' => [
							'contact_address_id' => NULL,
							'instance_id' => $this->instance_id,
							'contact_id' => $objectId,
							'address_number' => $i,
							'address' => $address
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Contactaddress']->commit($data['Contactaddress']);
					} else {
						$this->Contactaddress->create();
						$this->Contactaddress->save($data);
					}
					$i++;
				}

				unset($i);

				//Add Contactnotificationcommand record
				foreach($payload->host_commands as $command){
					$notifyCommand = $this->parseCheckCommand($command->command_name);
					$data = [
						'Contactnotificationcommand' => [
							'contact_notificationcommand_id' => NULL,
							'instance_id' => $this->instance_id,
							'contact_id' => $objectId,
							'notification_type' => 0,
							'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $notifyCommand[0]),
							'command_args' => $notifyCommand[1]
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Contactnotificationcommand']->commit($data['Contactnotificationcommand']);
					} else {
						$this->Contactnotificationcommand->create();
						$this->Contactnotificationcommand->save($data);
					}
				}

				foreach($payload->service_commands as $command){
					$notifyCommand = $this->parseCheckCommand($command->command_name);
					$data = [
						'Contactnotificationcommand' => [
							'contact_notificationcommand_id' => NULL,
							'instance_id' => $this->instance_id,
							'contact_id' => $objectId,
							'notification_type' => 1,
							'command_object_id' => $this->objectIdFromCache(OBJECT_COMMAND, $notifyCommand[0]),
							'command_args' => $notifyCommand[1]
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Contactnotificationcommand']->commit($data['Contactnotificationcommand']);
					} else {
						$this->Contactnotificationcommand->create();
						$this->Contactnotificationcommand->save($data);
					}
				}
				break;

			//Contactgroup object
			case OBJECT_CONTACTGROUP:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Contactgroup
				$data = [
					'Contactgroup' => [
						'contactgroup_id' => $objectId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'contactgroup_object_id' => $objectId,
						'alias' => $payload->alias,
					]
				];
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Contactgroup']->commit($data['Contactgroup']);
				} else {
					$this->Contactgroup->create();
					$this->Contactgroup->save($data);
				}

				//associate contactgroups with contacts
				foreach($payload->contact_members as $ContactName){
					$data = [
						'Contactgroupmember' => [
							'contactgroup_member_id' => NULL,
							'instance_id' => $this->instance_id,
							'contactgroup_id' => $objectId,
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $ContactName),
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Contactgroupmember']->commit($data['Contactgroupmember']);
					} else {
						$this->Contactgroupmember->create();
						$this->Contactgroupmember->rawInsert([$data], false);
					}
				}
				break;

			//Host object
			case OBJECT_HOST:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Host
				$checkCommand = $this->parseCheckCommand($payload->check_command);
				$eventHandlerCommand = $this->parseCheckCommand($payload->event_handler);
				$data = [
					'Host' => [
						'host_id' => $objectId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'host_object_id' => $objectId,
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
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Host']->commit($data['Host']);
				} else {
					$this->Host->rawInsert([$data], false);
				}

				foreach($payload->parent_hosts as $parentHost){
					$this->createParentHosts[$objectId][] = $parentHost;
				}

				foreach($payload->contactgroups as $contactgroupName){
					$data = [
						'Hostcontactgroup' => [
							'host_contentgroup_id' => NULL,
							'instance_id' => $this->instance_id,
							'host_id' => $objectId,
							'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $contactgroupName)
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Hostcontactgroup']->commit($data['Hostcontactgroup']);
					} else {
						$this->Hostcontactgroup->create();
						$this->Hostcontactgroup->save($data);
					}
				}

				foreach($payload->contacts as $contactName){
					$data = [
						'Hostcontact' => [
							'host_contact_id' => NULL,
							'instance_id' => $this->instance_id,
							'host_id' => $objectId,
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName)
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Hostcontact']->commit($data['Hostcontact']);
					} else {
						$this->Hostcontact->create();
						$this->Hostcontact->rawInsert([$data], false);
					}
				}

				foreach($payload->custom_variables as $varName => $varValue){
					$data = [
						'Customvariable' => [
							'customvariable_id' => NULL,
							'instance_id' => $this->instance_id,
							'object_id' => $objectId,
							'config_type' => $this->config_type,
							'has_been_modified' => 0,
							'varname' => $varName,
							'varvalue' => $varValue
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Customvariable']->commit($data['Customvariable']);
					} else {
						$this->Customvariable->create();
						$this->Customvariable->save($data);
					}
				}
				break;

			case OBJECT_HOSTGROUP:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Hostgroup
				$data = [
					'Hostgroup' => [
						'hostgroup_id' => $objectId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'hostgroup_object_id' => $objectId,
						'alias' => $payload->alias
					]
				];
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Hostgroup']->commit($data['Hostgroup']);
				} else {
					$this->Hostgroup->rawInsert([$data], false);
				}

				foreach($payload->members as $hostName){
					$data = [
						'Hostgroupmember' => [
							'hostgroup_member_id' => NULL,
							'instance_id' => $this->instance_id,
							'hostgroup_id' => $objectId,
							'host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $hostName)
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Hostgroupmember']->commit($data['Hostgroupmember']);
					} else {
						//$this->Hostgroupmember->create();
						$this->Hostgroupmember->rawInsert([$data], false);
					}
				}
				break;

			//Service object
			case OBJECT_SERVICE:
				if($this->dumpObjects === false){
					break;
				}

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

				/*******if($objectId === null){
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
				*/

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->description,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Service
				$eventHandlerCommand = $this->parseCheckCommand($payload->event_handler);
				$checkCommand = $this->parseCheckCommand($payload->check_command);
				if($this->objectIdFromCache(OBJECT_COMMAND, $checkCommand[0]) == null){
					debug($checkCommand);
				}
				$data = [
					'Service' => [
						'service_id' => $objectId,
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

				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Service']->commit($data['Service']);
				} else {
					$this->Service->rawInsert([$data], false);
				}
				unset($data);

				//Must run if all services are in the database, or we get in trouble!
				foreach($payload->parent_services as $parentService){
					$this->createParentServices[$objectId][] = [
						'host_name' => $payload->host_name,
						'description' => $payload->description
					];
				}

				if(!empty($payload->contactgroups)){
					foreach($payload->contactgroups as $contactgroupName){
						$data = [
							'Servicecontactgroup' => [
								'service_contactgroup_id' => NULL,
								'instance_id' => $this->instance_id,
								'service_id' => $objectId,
								'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $contactgroupName)
							]
						];
						if ($this->useBulkQueries === true) {
							$this->ObjectsRepository['Servicecontactgroup']->commit($data['Servicecontactgroup']);
						} else {
							$this->Servicecontactgroup->create();
							$this->Servicecontactgroup->save($data);
						}
					}
				}

				foreach($payload->contacts as $contactName){
					$data = [
						'Servicecontact' => [
							'service_content_id' => NULL,
							'instance_id' => $this->instance_id,
							'service_id' => $objectId,
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName)
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Servicecontact']->commit($data['Servicecontact']);
					} else {
						$this->Servicecontact->create();
						$this->Servicecontact->save($data, false);
					}
				}

				foreach($payload->custom_variables as $varName => $varValue){
					$data = [
						'Customvariable' => [
							'customvariable_id' => NULL,
							'instance_id' => $this->instance_id,
							'object_id' => $objectId,
							'config_type' => $this->config_type,
							'has_been_modified' => 0,
							'varname' => $varName,
							'varvalue' => $varValue
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Customvariable']->commit($data['Customvariable']);
					} else {
						$this->Customvariable->create();
						$this->Customvariable->save($data);
					}
				}
				break;

			case OBJECT_SERVICEGROUP:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->group_name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Servicegroup
				$data = [
					'Servicegroup' => [
						'servicegroup_id' => $objectId,
						'instance_id' => $this->instance_id,
						'config_type' => $this->config_type,
						'servicegroup_object_id' => $objectId,
						'alias' => $payload->alias
					]
				];
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Servicegroup']->commit($data['Servicegroup']);
				} else {
					$this->Servicegroup->create();
					$result = $this->Servicegroup->save($data);
				}

				foreach($payload->members as $ServiceArray){
					$data = [
						'Servicegroupmember' => [
							'servicegroup_member_id' => NULL,
							'instance_id' => $this->instance_id,
							'servicegroup_id' => $objectId,
							'service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $ServiceArray->host_name, $ServiceArray->service_description),
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Servicegroupmember']->commit($data['Servicegroupmember']);
					} else {
						$this->Servicegroupmember->create();
						$this->Servicegroupmember->rawInsert([$data], false);
					}
				}
				break;

			case OBJECT_HOSTESCALATION:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Hostescalation
				$data = [
					'Hostescalation' => [
						'hostescalation_id' => $this->fakeObjectId,
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
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Hostescalation']->commit($data['Hostescalation']);
				} else {
					$this->Hostescalation->create();
					$result = $this->Hostescalation->save($data);
				}

				foreach($payload->contacts as $contactName){
					$data = [
						'Hostescalationcontacts' => [
							'hostescalation_contact_id' => NULL,
							'instance_id' => $this->instance_id,
							'hostescalation_id' => $this->fakeObjectId,
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName),
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Hostescalationcontacts']->commit($data['Hostescalationcontacts']);
					} else {
						$this->Hostescalationcontacts->create();
						$this->Hostescalationcontacts->save($data);
					}
				}

				foreach($payload->contactgroups as $groupName){
					$data = [
						'Hostescalationcontactgroup' => [
							'hostescalation_contactgroup_id' => NULL,
							'instance_id' => $this->instance_id,
							'hostescalation_id' => $this->fakeObjectId,
							'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $groupName),
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Hostescalationcontactgroup']->commit($data['Hostescalationcontactgroup']);
					} else {
						$this->Hostescalationcontactgroup->create();
						$this->Hostescalationcontacts->save($data);
					}
				}
				$this->fakeObjectId++;
				break;

			case OBJECT_SERVICEESCALATION:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->description,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Serviceescalation
				$data = [
					'Serviceescalation' => [
						'serviceescalation_id' => $this->fakeObjectId,
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
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Serviceescalation']->commit($data['Serviceescalation']);
				} else {
					$this->Serviceescalation->create();
					$result = $this->Serviceescalation->save($data);
				}

				foreach($payload->contacts as $contactName){
					$data = [
						'Serviceescalationcontact' => [
							'serviceescalation_contact_id' => NULL,
							'instance_id' => $this->instance_id,
							'serviceescalation_id' => $this->fakeObjectId,
							'contact_object_id' => $this->objectIdFromCache(OBJECT_CONTACT, $contactName),
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Serviceescalationcontact']->commit($data['Serviceescalationcontact']);
					} else {
						$this->Serviceescalationcontact->create();
						$this->Serviceescalationcontact->save($data);
					}
				}

				foreach($payload->contactgroups as $groupName){
					$data = [
						'Serviceescalationcontactgroup' => [
							'serviceescalation_contactgroup_id' => NULL,
							'instance_id' => $this->instance_id,
							'serviceescalation_id' => $this->fakeObjectId,
							'contactgroup_object_id' => $this->objectIdFromCache(OBJECT_CONTACTGROUP, $groupName),
						]
					];
					if ($this->useBulkQueries === true) {
						$this->ObjectsRepository['Serviceescalationcontactgroup']->commit($data['Serviceescalationcontactgroup']);
					} else {
						$this->Serviceescalationcontactgroup->create();
						$this->Serviceescalationcontactgroup->save($data);
					}
				}
				$this->fakeObjectId++;
				break;

			case OBJECT_HOSTDEPENDENCY:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => null,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Hostdependency
				$data = [
					'Hostdependency' => [
						'hostdependency_id' => $this->fakeObjectId,
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
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Hostdependency']->commit($data['Hostdependency']);
				} else {
					$this->Hostdependency->create();
					$this->Hostdependency->save($data);
				}
				$this->fakeObjectId++;
				break;

			case OBJECT_SERVICEDEPENDENCY:
				if($this->dumpObjects === false){
					break;
				}

				// Add Object
				$objectId = $this->checkObject([
					'Objects' => [
						'objecttype_id' => $payload->object_type,
						'name1' => $payload->host_name,
						'name2' => $payload->service_description,
						'is_active' => 1,
						'instance_id' => $this->instance_id,
					]
				]);

				// Add Servicedependency
				$data = [
					'Servicedependency' => [
						'servicedependency_id' => $this->fakeObjectId,
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
				if ($this->useBulkQueries === true) {
					$this->ObjectsRepository['Servicedependency']->commit($data['Servicedependency']);
				} else {
					$this->Servicedependency->create();
					$this->Servicedependency->save($data);
				}
				$this->fakeObjectId++;
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$hostObjectId = $this->objectIdFromCache(OBJECT_HOST, $payload->hoststatus->name);
		if($hostObjectId == null){
			//Object has gone
			return;
		}

		if($this->useMemcached === true){
			$this->Memcached->setHoststatus($payload);
			if($this->MemcachedProcessingType === 1){
				return;
			}
		}

		$data = [
			'Hoststatus' => [
				//'hoststatus_id' => $hoststatusId,
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

		if($this->useBulkQueries === true){
			$this->BulkRepository['Hoststatus']->commit($data['Hoststatus']);
			return;
		}
		$this->Hoststatus->saveHoststatus($data, false);
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		//Drop old servicestatus entries
		if($payload->timestamp < (time() - $this->servicestatus_freshness)){
			return;
		}

		$service_object_id = $this->objectIdFromCache(OBJECT_SERVICE, $payload->servicestatus->host_name, $payload->servicestatus->description);
		if($service_object_id === null){
			//Object has gone
			return;
		}

		if($this->useMemcached === true){
			$this->Memcached->setServicestatus($payload);
			if($this->MemcachedProcessingType === 1){
				return;
			}
		}

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

		if($this->useBulkQueries === true){
			$this->BulkRepository['Servicestatus']->commit($data['Servicestatus']);
			return;
		}
		$this->Servicestatus->saveServicestatus($data, false);
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

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

		if($this->useBulkQueries === true){
			$this->BulkRepository['Servicecheck']->commit($data['Servicecheck']);
		}else{
			$data['Servicecheck']['servicecheck_id'] = NULL;
			$this->Servicecheck->rawInsert([$data], false);
		}

		if($this->processPerfdata === true && $payload->servicecheck->perf_data !== null){
			//process_performance_data == 1 ?
			if(isset($this->processPerfdataCache[$service_object_id])){

				$parsedPerfdata = $this->Perfdata->parsePerfdataString($payload->servicecheck->perf_data);

				if($this->PerfdataBackend->saveToRrd()){
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

					$rrdReturn = $this->RrdtoolBackend->writeToRrd($parsedPerfdataString, $parsedPerfdata);
					if($this->PerfdataConfig['XML']['write_xml_files'] === true){
						$this->RrdtoolBackend->updateXml($parsedPerfdataString, $parsedPerfdata, $rrdReturn);
					}
				}

				if($this->PerfdataBackend->saveToGraphite()){
					$_hostname = $payload->servicecheck->host_name;
					$_servicedesc = $payload->servicecheck->service_description;
					if($this->GraphiteBackend->requireHostNameCaching()){
						$_hostname = $this->GraphiteBackend->getHostdisplayNameFromCache($_hostname);
					}

					if($this->GraphiteBackend->requireServiceNameCaching()){
						$_servicedesc = $this->GraphiteBackend->getServicedisplayNameFromCache($payload->servicecheck->host_name, $_servicedesc);
					}

					$this->GraphiteBackend->save(
						$parsedPerfdata,
						$_hostname,
						$_servicedesc,
						$payload->servicecheck->start_time
					);
					unset($_hostname, $_servicedesc);
				}
			}
		}
	}

	public function processHostchecks($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$host_object_id = $this->objectIdFromCache(OBJECT_HOST, $payload->hostcheck->host_name);

		if($host_object_id === null){
			return;
		}

		$checkCommand = $this->parseCheckCommand($payload->hostcheck->command_name);

		//$this->Hostcheck->create();

		$is_raw_check = 0;
		if($payload->type == NEBTYPE_HOSTCHECK_RAW_START || $payload->type == NEBTYPE_HOSTCHECK_RAW_END){
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

		if($this->useBulkQueries === true){
			$this->BulkRepository['Hostcheck']->commit($data['Hostcheck']);
			return;
		}

		$data['Hostcheck']['hostcheck_id'] = NULL;
		$this->Hostcheck->rawInsert([$data], false);
	}

	public function processStatechanges($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$object_id = $this->getObjectIdForPayload($payload, 'statechange');
		if($object_id === null){
			//Object has gone
			return;
		}

		if($this->useMemcached=== true && $payload->statechange->state == 0){
			//Delete ack from memcached if record exists
			$this->Memcached->deleteAcknowledgementIfExists($payload);
		}

		//$this->Statehistory->create();
		$data = [
			'Statehistory' => [
				'statehistory_id' => NULL,
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		//$this->Logentry->create();
		$data = [
			'Logentry' => [
				'logentry_id' => null,
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

		if($this->useBulkQueries === true){
			$this->BulkRepository['Logentry']->commit($data['Logentry']);
			return;
		}
		$this->Logentry->rawInsert([$data], false);
	}

	public function processSystemcommands($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		//$this->Systemcommand->create();
		$data = [
			'Systemcommand' => [
				'systemcommand_id' => NULL,
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$object_id = $this->getObjectIdForPayload($payload, 'comment');

		if($object_id === null){
			//Object has gone
			return;
		}

		//expiration_time was remove!
		//https://github.com/naemon/naemon-core/blob/f730f72bfb8027fbaf21badfce3b53012424fc38/src/naemon/comments.c#L50-L62

		if($payload->type == NEBTYPE_COMMENT_DELETE){
			//Delete comment from DB, if exists;
			$comments = $this->Comment->find('all', [
				'conditions' => [
					'object_id' => $object_id,
					'internal_comment_id' => $payload->comment->comment_id
				]
			]);
			foreach($comments as $comment){
				$this->Comment->delete($comment['Comment']['comment_id']);
			}

			//Update comment history
			$data = [
				'Commenthistory' => [
					'instance_id' => $this->instance_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->comment->entry_time),
					'entry_time_usec' => $payload->comment->entry_time,
					'comment_type' => $payload->comment->comment_type,
					'entry_type' => $payload->comment->entry_type,
					'object_id' => $object_id,
					'comment_time' => date('Y-m-d H:i:s', $payload->timestamp),
					'internal_comment_id' => $payload->comment->comment_id,
					'author_name' => $payload->comment->author_name,
					'comment_data' => $payload->comment->comment_data,
					'is_persistent' => $payload->comment->persistent,
					'comment_source' => $payload->comment->source,
					'expires' => $payload->comment->expires,
					'expiration_time' => '1970-01-01 00:00:00',
					'deletion_time' => date('Y-m-d H:i:s', $payload->timestamp),
					'deletion_time_usec' => $payload->timestamp,
				]
			];
			$this->Commenthistory->saveOnDuplicate($data);
			return true;
		}

		if($payload->type == NEBTYPE_COMMENT_ADD || $payload->type == NEBTYPE_COMMENT_LOAD){
			//Create new comment or update existing comment
			$data = [
				'Comment' => [
					'instance_id' => $this->instance_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->comment->entry_time),
					'entry_time_usec' => $payload->comment->entry_time,
					'comment_type' => $payload->comment->comment_type,
					'entry_type' => $payload->comment->entry_type,
					'object_id' => $object_id,
					'comment_time' => date('Y-m-d H:i:s', $payload->timestamp),
					'internal_comment_id' => $payload->comment->comment_id,
					'author_name' => $payload->comment->author_name,
					'comment_data' => $payload->comment->comment_data,
					'is_persistent' => $payload->comment->persistent,
					'comment_source' => $payload->comment->source,
					'expires' => $payload->comment->expires,
					'expiration_time' => '1970-01-01 00:00:00'
				]
			];

			$this->Comment->saveOnDuplicate($data);

			//Save to comment history
			$data = [
				'Commenthistory' => [
					'instance_id' => $this->instance_id,
					'entry_time' => date('Y-m-d H:i:s', $payload->comment->entry_time),
					'entry_time_usec' => $payload->comment->entry_time,
					'comment_type' => $payload->comment->comment_type,
					'entry_type' => $payload->comment->entry_type,
					'object_id' => $object_id,
					'comment_time' => date('Y-m-d H:i:s', $payload->timestamp),
					'internal_comment_id' => $payload->comment->comment_id,
					'author_name' => $payload->comment->author_name,
					'comment_data' => $payload->comment->comment_data,
					'is_persistent' => $payload->comment->persistent,
					'comment_source' => $payload->comment->source,
					'expires' => $payload->comment->expires,
					'expiration_time' => '1970-01-01 00:00:00',
					'deletion_time' => '1970-01-01 00:00:00',
					'deletion_time_usec' => 0,
				]
			];
			$this->Commenthistory->saveOnDuplicate($data);
		}
	}

	public function processExternalcommands($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		//$this->Externalcommand->create();
		$data = [
			'Externalcommand' => [
				'externalcommand_id' => NULL,
				'instance_id' => $this->instance_id,
				'entry_time' => date('Y-m-d H:i:s', $payload->externalcommand->entry_time),
				'command_type' => $payload->externalcommand->command_type,
				'command_name' => $payload->externalcommand->command_string,
				'command_args' => $payload->externalcommand->command_args
			]
		];

		if($this->useBulkQueries === true){
			$this->BulkRepository['Externalcommand']->commit($data['Externalcommand']);
			return;
		}
		$this->Externalcommand->rawInsert([$data], false);
	}

	public function processAcknowledgements($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$object_id = $this->getObjectIdForPayload($payload, 'acknowledgement');
		if($object_id === null){
			//Object has gone
			return;
		}

		if($this->useMemcached === true){
			//Add a record in memcached
			$this->Memcached->setAcknowledgement($payload);
		}

		//$this->Acknowledgement->create();
		$data = [
			'Acknowledgement' => [
				'acknowledgement_id' => NULL,
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$object_id = $this->getObjectIdForPayload($payload, 'flapping');
		if($object_id === null){
			//Object has gone
			return;
		}

		//$this->Flapping->create();
		$data = [
			'Flapping' => [
				'flappinghistory_id' => NULL,
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		$object_id = $this->getObjectIdForPayload($payload, 'downtime');
		if($object_id === null){
			//Object has gone
			return;
		}

		if($this->useMemcached === true){
			$this->Memcached->setDowntime($payload);
		}

		if($payload->type == NEBTYPE_DOWNTIME_ADD || $payload->type == NEBTYPE_DOWNTIME_LOAD){
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

		if($payload->type == NEBTYPE_DOWNTIME_START){
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

		if($payload->type == NEBTYPE_DOWNTIME_STOP){
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

		//$this->Processdata->create();
		$data = [
			'Processdata' => [
				'processevent_id' => 1,
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
		return true;
		
		//All this code is history Notification is deprecated
		//Please disable this event in your broker options and only use the contactnotificationmethods!
		//$this->processContactnotificationmethod
		//use_notification_data=0
	}

	public function processProgrammstatus($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}

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
		return true;
		
		
		//All this code is history Contactnotificationdata is deprecated
		//Please disable this event in your broker options and only use the contactnotificationmethods!
		//$this->processContactnotificationmethod
		//use_contact_notification_data=0
		
	}

	public function processContactnotificationmethod($job){
		if($this->clearQ){
			return;
		}

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}
		

		$commandObjectId = $this->objectIdFromCache(OBJECT_COMMAND, $payload->contactnotificationmethod->command_name);
		$contactObjectId = $this->objectIdFromCache(OBJECT_CONTACT, $payload->contactnotificationmethod->contact_name);
		$hostOrServiceObjectId = $this->getObjectIdForPayload($payload, 'contactnotificationmethod');
		
		if($contactObjectId === null || $commandObjectId === null || $hostOrServiceObjectId === null){
			//Object has gone
			return;
		}

		if($payload->type !== NEBTYPE_CONTACTNOTIFICATIONMETHOD_END){
			return;
		}
		
		$notificationType = 0;
		if($payload->contactnotificationmethod->service_description !== null){
			$notificationType = 1;
		}
		
		$escalated = 0;
		if(property_exists($payload->contactnotificationmethod, 'escalated')){
			$escalated = $payload->contactnotificationmethod->escalated;
		}
		
		$this->Notification->create();
		$data = [
			'Notification' => [
				'instance_id' => $this->instance_id,
				'notification_type' => $notificationType,
				'notification_reason' => $payload->contactnotificationmethod->reason_type,
				'object_id' => $hostOrServiceObjectId,
				'start_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->start_time),
				'start_time_usec' => $payload->contactnotificationmethod->start_time,
				'end_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->end_time),
				'end_time_usec' => $payload->contactnotificationmethod->end_time,
				'state' => $payload->contactnotificationmethod->state,
				'output' => $payload->contactnotificationmethod->output,
				'long_output' => $payload->contactnotificationmethod->output,
				'escalated' => $escalated,
				'contacts_notified' => 1,
			]
		];
		$result = $this->Notification->save($data);
		
		
		if(isset($result['Notification']['notification_id']) && $result['Notification']['notification_id'] != null){
			$this->Contactnotification->create();
			$data = [
				'Contactnotification' => [
					'contactnotification_id' => NULL,
					'instance_id' => $this->instance_id,
					'notification_id' => $result['Notification']['notification_id'],
					'contact_object_id' => $contactObjectId,
					'start_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->start_time),
					'start_time_usec' => $payload->contactnotificationmethod->start_time,
					'end_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->end_time),
					'end_time_usec' => $payload->contactnotificationmethod->end_time
				]
			];

			$contactnotification_id = $this->Contactnotification->rawInsert([$data], true);
		}
		
		if($contactnotification_id){
			$this->Contactnotificationmethod->create();
			$data = [
				'Contactnotificationmethod' => [
					'instance_id' => $this->instance_id,
					'contactnotification_id' => $contactnotification_id,
					'start_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->start_time),
					'start_time_usec' => $payload->contactnotificationmethod->start_time,
					'end_time' => date('Y-m-d H:i:s', $payload->contactnotificationmethod->end_time),
					'end_time_usec' => $payload->contactnotificationmethod->end_time,
					'command_object_id' => $commandObjectId,
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

		// get job payload and check for parsing errors
		if (($payload = $this->getJobPayload($job)) == false) {
			return;
		}


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

		// Prepare Bulk Repository for Objects Operations
		$this->ObjectsRepository = [];
		$this->ObjectsRepository['Command'] = new BulkRepository($this->Command, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Timerange'] = new BulkRepository($this->Timerange, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Contact'] = new BulkRepository($this->Contact, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Contactaddress'] = new BulkRepository($this->Contactaddress, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Contactnotificationcommand'] = new BulkRepository($this->Contactnotificationcommand, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Contactgroup'] = new BulkRepository($this->Contactgroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Contactgroupmember'] = new BulkRepository($this->Contactgroupmember, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostcontactgroup'] = new BulkRepository($this->Hostcontactgroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostcontact'] = new BulkRepository($this->Hostcontact, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Host'] = new BulkRepository($this->Host, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostgroup'] = new BulkRepository($this->Hostgroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Customvariable'] = new BulkRepository($this->Customvariable, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostgroupmember'] = new BulkRepository($this->Hostgroupmember, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Servicecontactgroup'] = new BulkRepository($this->Servicecontactgroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Servicecontact'] = new BulkRepository($this->Servicecontact, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Service'] = new BulkRepository($this->Service, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Servicegroupmember'] = new BulkRepository($this->Servicegroupmember, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Servicegroup'] = new BulkRepository($this->Servicegroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostescalation'] = new BulkRepository($this->Hostescalation, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Serviceescalation'] = new BulkRepository($this->Serviceescalation, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostescalationcontacts'] = new BulkRepository($this->Hostescalationcontacts, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostescalationcontactgroup'] = new BulkRepository($this->Hostescalationcontactgroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Serviceescalationcontact'] = new BulkRepository($this->Serviceescalationcontact, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Serviceescalationcontactgroup'] = new BulkRepository($this->Serviceescalationcontactgroup, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Hostdependency'] = new BulkRepository($this->Hostdependency, $this->bulkQueryLimit, $this->bulkQueryTime);
		$this->ObjectsRepository['Servicedependency'] = new BulkRepository($this->Servicedependency, $this->bulkQueryLimit, $this->bulkQueryTime);

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
		if ($this->useBulkQueries) {
			$bulk = new BulkRepository($this->Parenthost, $this->bulkQueryLimit, $this->bulkQueryTime);
		}
		foreach($this->createParentHosts as $host_id => $hostNamesAsArray){
			foreach($hostNamesAsArray as $hostName){
				$this->Parenthost->create();
				$data = [
					'Parenthost' => [
						'host_parenthost_id' => NULL,
						'instance_id' => $this->instance_id,
						'host_id' => $host_id,
						'parent_host_object_id' => $this->objectIdFromCache(OBJECT_HOST, $hostName)
					]
				];
				if ($this->useBulkQueries === true) {
					$bulk->commit($data['Parenthost']);
				} else {
					$this->Parenthost->save($data);
				}
			}
		}
		if ($this->useBulkQueries) {
			$bulk->push();
		}
	}

	public function saveParentServices(){
		if ($this->useBulkQueries) {
			$bulk = new BulkRepository($this->Parenthost, $this->bulkQueryLimit, $this->bulkQueryTime);
		}
		//CakeLog::debug(var_export($this->createParentServices, true));
		foreach($this->createParentServices as $service_id => $servicesArray){
			foreach($servicesArray as $serviceArray){
				$this->Parentservice->create();
				$data = [
					'Parentservice' => [
						'service_parentservice_id' => NULL,
						'instance_id' => $this->instance_id,
						'service_id' => $service_id,
						'parent_service_object_id' => $this->objectIdFromCache(OBJECT_SERVICE, $serviceArray['host_name'], $serviceArray['description'])
					]
				];
				if ($this->useBulkQueries === true) {
					$bulk->commit($data['Parentservice']);
				} else {
					$this->Parentservice->save($data);
				}
			}
		}
		if ($this->useBulkQueries) {
			$bulk->push();
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
		$this->Scheduleddowntime->cleanup();
		$this->Dbversion->save([
			'Dbversion' => [
				'name' => 'Statusengine',
				'version' => STATUSENIGNE_VERSION
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
			$this->BulkRepository = [];

			$this->worker = new GearmanWorker();

			/* Avoid that gearman will stuck at GearmanWorker::work() if no jobs are present
			 * which is bad because if GearmanWorker::work() stuck, PHP can not execute the signal handler
			 */
			$this->worker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
			$this->worker->setTimeout(250);

			$this->worker->addServer(Configure::read('server'), Configure::read('port'));
			foreach($this->queues as $queueName => $functionName){
				CakeLog::info(sprintf('Queue "%s" will be handled by function "%s"', $queueName, $functionName));
				$this->worker->addFunction($queueName, [$this, $functionName]);
			}
			$this->bindQueues = false;

			if(isset($this->queues['statusngin_servicechecks']) && $this->useBulkQueries){
				$this->BulkRepository['Servicecheck'] = new BulkRepository($this->Servicecheck, $this->bulkQueryLimit, $this->bulkQueryTime);
			}

			if(isset($this->queues['statusngin_hostchecks']) && $this->useBulkQueries){
				$this->BulkRepository['Hostcheck'] = new BulkRepository($this->Hostcheck, $this->bulkQueryLimit, $this->bulkQueryTime);
			}

			if(isset($this->queues['statusngin_servicestatus']) && $this->useBulkQueries){
				$this->BulkRepository['Servicestatus'] = new BulkRepository($this->Servicestatus, $this->bulkQueryLimit, $this->bulkQueryTime);
			}

			if(isset($this->queues['statusngin_hoststatus']) && $this->useBulkQueries){
				$this->BulkRepository['Hoststatus'] = new BulkRepository($this->Hoststatus, $this->bulkQueryLimit, $this->bulkQueryTime);
			}

			if(isset($this->queues['statusngin_externalcommands']) && $this->useBulkQueries){
				$this->BulkRepository['Externalcommand'] = new BulkRepository($this->Externalcommand, $this->bulkQueryLimit, $this->bulkQueryTime);
			}

			if(isset($this->queues['statusngin_logentries']) && $this->useBulkQueries){
				$this->BulkRepository['Logentry'] = new BulkRepository($this->Logentry, $this->bulkQueryLimit, $this->bulkQueryTime);
			}
		}
		while(true){
			if($this->work === true){
				// Reconnect datasource before we refresh our cache
				CakeLog::info('Reconnect database');
				$this->Objects->getDatasource()->reconnect();

				CakeLog::info('Clear my objects cache');
				$this->clearObjectsCache();

				CakeLog::info('Build up new objects cache');
				$this->buildObjectsCache();
				//CakeLog::debug(var_export($this->objectCache, true));


				if($this->processPerfdata === true){
					if(isset($this->queues['statusngin_servicechecks'])){
						CakeLog::info('Build up new process perfdata cache');
						$this->buildProcessPerfdataCache();

						$this->cacheHostNamesForGraphiteIfRequried();
						$this->cacheServiceNamesForGraphiteIfRequried();
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

				// check every second if there's something left to push
				if($this->useBulkQueries && $this->bulkLastCheck < time()) {
					foreach ($this->BulkRepository as $name => $repo) {
						$repo->pushIfRequired();
					}
					$this->bulkLastCheck = time();
				}

				// ping datasource every now and then to keep the pdo connection alive
				// simulate mysql_ping()
				if($this->lastDatasourcePing + 120 < time()) {
					try {
						$this->Objects->getDatasource()->execute('SELECT 1');
					} catch(PDOException $e) {
						$this->Objects->getDatasource()->reconnect();
					}
					$this->lastDatasourcePing = time();
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

				// flush all bulk queues
				if ($this->useBulkQueries) {
					CakeLog::info('Force flushing all bulk queues');
					foreach ($this->BulkRepository as $name => $repo) {
						$repo->push();
					}
				}
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

	public function cacheHostNamesForGraphiteIfRequried(){
		if($this->processPerfdata === true){
			if($this->PerfdataBackend->isGraphiteEnabled()){
				if($this->GraphiteBackend->requireNameCaching()){
					CakeLog::info('Build up host name cache for Graphite');
					$query = sprintf('
						SELECT
							`%s`.`name1`,
							`%s`.`display_name`
						FROM `%s`
						LEFT JOIN `%s` ON `%s`.object_id = `%s`.host_object_id
						WHERE `%s`.objecttype_id = %s; -- %s',
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Host->tablePrefix.$this->Host->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Host->tablePrefix.$this->Host->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Host->tablePrefix.$this->Host->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						OBJECT_HOST,
						time() //disable caching
					);

					$results = $this->Objects->query($query);

					foreach($results as $result){
						$this->GraphiteBackend->addHostdisplayNameToCache(
							$result[$this->Objects->tablePrefix.$this->Objects->table]['name1'],
							$result[$this->Host->tablePrefix.$this->Host->table]['display_name']
						);
					}
					$this->Objects->clear();
				}
			}
		}
	}

	public function cacheServiceNamesForGraphiteIfRequried(){
		if($this->processPerfdata === true){
			if($this->PerfdataBackend->isGraphiteEnabled()){
				if($this->GraphiteBackend->requireNameCaching()){
					CakeLog::info('Build up service name cache for Graphite');

					$query = sprintf('
						SELECT
							`%s`.`name1`,
							`%s`.`name2`,
							`%s`.`display_name`
						FROM `%s`
						LEFT JOIN `%s` ON `%s`.object_id = `%s`.service_object_id
						WHERE `%s`.objecttype_id = %s; -- %s',
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Service->tablePrefix.$this->Service->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Service->tablePrefix.$this->Service->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						$this->Service->tablePrefix.$this->Service->table,
						$this->Objects->tablePrefix.$this->Objects->table,
						OBJECT_SERVICE,
						time() //disable caching
					);

					$results = $this->Objects->query($query);

					foreach($results as $result){
						$this->GraphiteBackend->addServicedisplayNameToCache(
							$result[$this->Objects->tablePrefix.$this->Objects->table]['name1'],
							$result[$this->Objects->tablePrefix.$this->Objects->table]['name2'],
							$result[$this->Service->tablePrefix.$this->Service->table]['display_name']
						);
					}
					$this->Objects->clear();
				}
			}
		}
	}
}
