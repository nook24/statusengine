<?php
$config = [
	'perfdata' => [
		//Version of Statusengine's Perfdata extension
		'version' => '1.0.0',
	
		'RRA' => [
			'step' => 60,
			'average' => '0.5:1:576000',
			'max' => '0.5:1:576000',
			'min' => '0.5:1:576000',
		],
		
		'RRD' => [
			'heartbeat' => 8460
		],
	
		'RRDCACHED' => [
			'use' => false,
			'sock' => 'unix:/var/run/rrdcached.sock'
		],
	
		'PERFDATA' => [
			'dir' => '/opt/openitc/nagios/share/perfdata/'
		],
	
		'MOD_GEARMAN' => [
			'encryption' => true,
			'key' => 'should_be_changed'
		],
	
		'GEARMAN' => [
			//port of gearman-job-server
			'server' => '127.0.0.1',
			//port of gearman-job-server
			'port' => 4730
		]
	]
];
