<?php
$config = [
	'version' => '1.0.1',
	'logfile' => '/var/log/statusengine.log',
	'servicestatus_freshness' => 30,
	'server' => '127.0.0.1',
	'port' => 4730,
	'coreconfig' => '/etc/naemon/naemon.cfg',
	
	'memcached' => [
		'use_memcached' => true,
		'drop_in_start' => false,
		'server' => '127.0.0.1',
		'port' => 11211
	]
];
