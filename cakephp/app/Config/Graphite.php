<?php
/* Main configuration file of Statusengine's ModPerfdata Graphite extension
 * This is a PHP file, please check for syntax errors!
 * Example command to check for any syntax erros:
 *   php --syntax-check /opt/statusengine/cakephp/app/Config/Graphite.php
 */
$config = [
	'graphite' => [

		//Statusengine will create a UDP connection to your graphite server
		'host' => 'graphite.example.org',
		'port' => 2003,

		//prefix for every key
		'prefix' => 'statusengine',

		//if false, statusengine will use the host name as key
		//if true, statusengine will use the display_name as key
		'use_host_display_name' => false,

		//if false, statusengine will use the service description as key
		//if true, statusengine will use the display_name as key
		'use_service_display_name' => false,

		//full example for key: statusengine.localhost.Ping.rta
	]
];
