<?php
/* Configuration file of Statusengine's database cleanup cronjob
 * This is a PHP file, please check for syntax errors!
 * Example command to check for any syntax erros:
 *   php --syntax-check /opt/statusengine/cakephp/app/Config/Cronjob.php
 */
$config = [
	'Cleanup' => [
		// Archiv age in seconds of host check results
		// Default: 604800 -> 7 days (60*60*24*7)
		'hostchecks' => 604800,

		// Archiv age in seconds of service check results
		// Default: 604800 -> 7 days (60*60*24*7)
		'servicechecks' => 604800,

		// Archiv age in seconds of host and service state history
		// Default: 1209600 -> 14 days (60*60*24*14)
		'statehistory' => 1209600,

		// Archiv age in seconds of acknowledgements
		// Default: 7776000 -> ~3 months (60*60*24*30*3)
		'acknowledgements' => 7776000,

		// Archiv age in seconds of logentries
		// Default: 86400 -> 1 day (60*60*24*1)
		'logentries' => 86400,

		// Archiv age in seconds of downtimes
		// Default: 7776000 -> ~3 months (60*60*24*30*3)
		'downtimes' => 7776000,

		// Archiv age in seconds of host and service notifications
		// Default: 1209600 -> 14 days (60*60*24*14)
		'notifications' => 1209600,
	]
];
