<?php
/* Main configuration file of Statusengine's web interface
 * This is a PHP file, please check for syntax errors!
 * Example command to check for any syntax erros:
 *   php --syntax-check /opt/statusengine/cakephp/app/Config/Interface.php
 */
$config = [
	'Interface' => [
		// Path to naemonstats binary
		'naemonstats' => '/opt/naemon/bin/naemonstats',
		
		// common web server user groups
		'webserver_usergroups' => [
			'www-data',
			'www',
			'httpd',
			'apache',
			'nginx'
		],
		
		//Path to PNP$Nagios index.php
		'pnp4nagios' => '/usr/share/pnp4nagios/html/index.php',
		
		//If true the interface will show all SQL queries, if false not
		'sql_dump' => false,
		
		//Hide the openITCOCKPIT notice in footer
		'hide_oitc' => false,
	]
];
