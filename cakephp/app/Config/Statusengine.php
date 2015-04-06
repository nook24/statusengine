<?php
$config = [
	'version' => '1.2.0', //program version
	'logfile' => '/var/log/statusengine.log',
	'servicestatus_freshness' => 30, //max age of service status recors in gearman
	'server' => '127.0.0.1', //address of gearman-job-server
	'port' => 4730, //port of gearman-job-server
	'coreconfig' => '/etc/naemon/naemon.cfg', //path to your naemon.cfg or nagios.cfg
	
	'memcached' => [
		'use_memcached' => false, //use memcached or not
		'processing_type' => 0, //1 = save only in memcached, 0 = save in db and memcached
		'drop_on_start' => false, //clear all memcacehd entries on start up
		'server' => '127.0.0.1', //address of memcached server
		'port' => 11211 // port of memcached server
	]
];
