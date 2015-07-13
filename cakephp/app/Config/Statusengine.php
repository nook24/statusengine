<?php
/* Main configuration file of Statusengine
 * This is a PHP file, please check for syntax errors!
 * Example command to check for any syntax erros:
 *   php --syntax-check /opt/statusengine/cakephp/app/Config/Statusengine.php
 */

$config = [
	//Version of Statusengine
	'version' => '1.4.1',
	
	//Logfile, where statusengine will log some information
	'logfile' => '/var/log/statusengine.log',
	
	//max age of service status records in gearman queue
	'servicestatus_freshness' => 300,
	
	//address of gearman-job-server
	'server' => '127.0.0.1',
	
	//port of gearman-job-server
	'port' => 4730,
	
	//path to your naemon.cfg or nagios.cfg
	'coreconfig' => '/opt/naemon/etc/naemon/naemon.cfg',
	
	//Number of your monitoring instance (just an integer value)
	'instance_id' => 1,
	
	//The number of the config type you would to dump to the database (just an integer value)
	'config_type' => 1,
	
	//If you want, Statusengine's servicestatus workers are able to
	//process performacne data for you and save them to RRD files
	//so you don't need to install any additional software to
	//get the job done.
	'process_perfdata' => true,
	
	//Workers Statusengine will fork in worker mode
	'workers' => [
		[
			'queues' => ['statusngin_servicestatus' => 'processServicestatus']
		],
		//You can simple add more workers if you servicechecks queue is growing
		//Remember: MySQL love multithreaded applications :)
		/*
		[
			'queues' => ['statusngin_servicestatus' => 'processServicestatus']
		],
		[
			'queues' => ['statusngin_servicestatus' => 'processServicestatus']
		],
		[
			'queues' => ['statusngin_servicestatus' => 'processServicestatus']
		],
		*/
		
		[
			'queues' => [
				'statusngin_hoststatus' => 'processHoststatus',
				'statusngin_statechanges' => 'processStatechanges'
			]
		],
		[
			'queues' => ['statusngin_servicechecks' => 'processServicechecks']
		],
		//You can simple add more workers if you servicechecks queue is growing
		//Remember: MySQL love multithreaded applications :)
		/*
		[
			'queues' => ['statusngin_servicechecks' => 'processServicechecks']
		],
		[
			'queues' => ['statusngin_servicechecks' => 'processServicechecks']
		],
		[
			'queues' => ['statusngin_servicechecks' => 'processServicechecks']
		],
		*/
		
		[
			'queues' => [
				'statusngin_hostchecks' => 'processHostchecks',
				'statusngin_logentries' => 'processLogentries'
			]
		],
		[
			'queues' => [
				'statusngin_notifications' => 'processNotifications',
				'statusngin_contactstatus' => 'processContactstatus',
				'statusngin_contactnotificationdata' => 'processContactnotificationdata',
				'statusngin_contactnotificationmethod' => 'processContactnotificationmethod',
				'statusngin_acknowledgements' => 'processAcknowledgements',
				'statusngin_comments' => 'processComments',
				'statusngin_flappings' => 'processFlappings',
				'statusngin_downtimes' => 'processDowntimes',
				'statusngin_externalcommands' => 'processExternalcommands',
				'statusngin_systemcommands' => 'processSystemcommands',
				'statusngin_eventhandler' => 'processEventhandler'
			]
		]
	],
	
	
	//Memcached settings
	'memcached' => [
		//use memcached or not
		'use_memcached' => false,
		
		//1 = save only in memcached, 0 = save in db and memcached
		'processing_type' => 0,
		
		//clear all memcacehd entries on start up
		'drop_on_start' => false,
		
		//address of memcached server
		'server' => '127.0.0.1',
		
		//port of memcached server
		'port' => 11211 
	]
];
