<?php
/* Main configuration file of Statusengine's web interface
 * This is a PHP file, please check for syntax errors!
 * Example command to check for any syntax erros:
 *   php --syntax-check /opt/statusengine/cakephp/app/Config/Interface.php
 */
$config = [
	'Interface' => [
		// Path to naemonstats binary
		'naemonstats' => '/opt/openitc/nagios/bin/naemonstats',
		
		// common web server user groups
		'webserver_usergroups' => [
			'www-data',
			'www',
			'httpd',
			'apache',
			'nginx'
		],
	]
];