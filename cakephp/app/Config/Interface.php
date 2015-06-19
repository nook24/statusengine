<?php
/* Main configuration file of Statusengine's web interface
 * This is a PHP file, please check for syntax errors!
 * Example command to check for any syntax erros:
 *   php --syntax-check /opt/statusengine/cakephp/app/Config/Interface.php
 */
$config = [
	'Interface' => [
		//Path to Naemons command file, to send external commands
		//like SCHEDULE_FORCED_HOST_CHECK etc..
		'command_file' => '/opt/openitc/nagios/var/rw/naemon.cmd',
	]
];